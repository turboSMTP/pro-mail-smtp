<?php

namespace TurboSMTP\ProMailSMTP\Core;
if ( ! defined( 'ABSPATH' ) ) exit;

use TurboSMTP\ProMailSMTP\Helpers\PluginListUpdater;

class Plugin
{
    private $version;
    private $wp_mail_caller;
    private $plugin_list_updater;

    public function __construct()
    {
        $this->version = PRO_MAIL_SMTP_VERSION;
        $this->wp_mail_caller = new WPMailCaller();
        $this->plugin_list_updater = new PluginListUpdater();
    }

    public function init()
    {
        $this->load_components();
        $this->init_hooks();
        wp_cron();
    }

    private function load_components()
    {
        \TurboSMTP\ProMailSMTP\Cron\CronManager::get_instance()->init();
        \TurboSMTP\ProMailSMTP\Cron\CronManager::get_instance()->activate_crons();


        add_action('rest_api_init', function () {
            register_rest_route('pro-mail-smtp/v1', '/oauth/outlook/callback', [
                'methods'             => 'GET',
                'callback'            => function (\WP_REST_Request $request) {
                    $code  = sanitize_text_field($request->get_param('code') ?? '');
                    $state = sanitize_text_field($request->get_param('state') ?? '');
                    $error = sanitize_text_field($request->get_param('error') ?? '');

                    $args = [
                        'page'  => 'pro-mail-smtp-providers',
                        'code'  => $code,
                        'state' => $state,
                    ];
                    if ($error !== '') {
                        $args['error'] = $error;
                    }

                    wp_safe_redirect(add_query_arg($args, admin_url('admin.php')));
                    exit;
                },
                'permission_callback' => '__return_true',
            ]);
        });

        if (is_admin()) {
            new \TurboSMTP\ProMailSMTP\Admin\Menu();
            new \TurboSMTP\ProMailSMTP\Admin\Providers();
            new \TurboSMTP\ProMailSMTP\Admin\Logs();
            new \TurboSMTP\ProMailSMTP\Admin\Analytics();
            new \TurboSMTP\ProMailSMTP\Admin\EmailRouter();
            new \TurboSMTP\ProMailSMTP\Admin\Alerts();
            new \TurboSMTP\ProMailSMTP\Admin\Settings();
            new \TurboSMTP\ProMailSMTP\Admin\About();
        }

        // CF7's array_filter in WPCF7_Mail::compose() accidentally strips uploaded file
        // paths (absolute paths get mangled by path_join), so wp_mail receives an empty
        // attachments array. We capture the real paths here, before that filter runs,
        // and inject them back in the pre_wp_mail handler below.
        $cf7_stashed_attachments = [];

        add_filter('wpcf7_mail_components', function ($components) use (&$cf7_stashed_attachments) {
            if (class_exists('WPCF7_Submission')) {
                $submission = \WPCF7_Submission::get_instance();
                if ($submission) {
                    foreach ($submission->uploaded_files() as $paths) {
                        foreach ((array) $paths as $path) {
                            if (is_string($path) && file_exists($path)) {
                                $cf7_stashed_attachments[] = $path;
                            }
                        }
                    }
                }
            }
            return $components;
        }, 10, 1);

        $email_manager = new \TurboSMTP\ProMailSMTP\Email\Manager();
        add_filter('pre_wp_mail', function ($pre, $atts) use ($email_manager, &$cf7_stashed_attachments) {
            $this->wp_mail_caller->getSourcePluginName();

            if (!empty($cf7_stashed_attachments)) {
                $atts['attachments'] = array_merge((array) ($atts['attachments'] ?? []), $cf7_stashed_attachments);
                $cf7_stashed_attachments = [];
            }

            return $email_manager->sendMail($pre, $atts);
        }, 10, 2);
    }

    private function init_hooks()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('admin_bar_menu', [$this, 'pro_mail_smtp_add_admin_bar_menu'], 100);
    }

    public function enqueue_admin_scripts($hook)
    {
        $admin_pages = [
            'pro-mail-smtp_page_pro-mail-smtp-providers',
            'pro-mail-smtp_page_pro-mail-smtp-logs',
            'pro-mail-smtp_page_pro-mail-smtp-analytics',
            'pro-mail-smtp_page_pro-mail-smtp-email-router',
            'pro-mail-smtp_page_pro-mail-smtp-settings',
            'pro-mail-smtp_page_pro-mail-smtp-about',

        ];

        if (in_array($hook, $admin_pages)) {
            $this->plugin_list_updater->updateActivePluginsOption();
        }

        wp_enqueue_style(
            'pro_mail_smtp_admin',
            PRO_MAIL_SMTP_URL . 'assets/css/admin.css',
            [],
            $this->version
        );

        wp_enqueue_style('dashicons');
    }

    function pro_mail_smtp_add_admin_bar_menu($wp_admin_bar)
    {
        $wp_admin_bar->add_node([
            'id'    => 'pro-mail-smtp',
            'title' => 'Pro Mail SMTP',
            'href'  => admin_url('admin.php?page=pro-mail-smtp-providers'),
            'meta'  => [
                'title' => __('Pro Mail SMTP Plugin', 'pro-mail-smtp'),
            ],
        ]);
    }
}
