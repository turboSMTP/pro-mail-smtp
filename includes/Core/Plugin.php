<?php

namespace TurboSMTP\FreeMailSMTP\Core;
if ( ! defined( 'ABSPATH' ) ) exit;

use TurboSMTP\FreeMailSMTP\Helpers\PluginListUpdater;

class Plugin
{
    private $version;
    private $wp_mail_caller;
    private $plugin_list_updater;

    public function __construct()
    {
        $this->version = FREE_MAIL_SMTP_VERSION;
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
        \TurboSMTP\FreeMailSMTP\Cron\CronManager::get_instance()->init();
        \TurboSMTP\FreeMailSMTP\Cron\CronManager::get_instance()->activate_crons();


        if (is_admin()) {
            new \TurboSMTP\FreeMailSMTP\Admin\Menu();
            new \TurboSMTP\FreeMailSMTP\Admin\Providers();
            new \TurboSMTP\FreeMailSMTP\Admin\Logs();
            new \TurboSMTP\FreeMailSMTP\Admin\Analytics();
            new \TurboSMTP\FreeMailSMTP\Admin\EmailRouter();
            new \TurboSMTP\FreeMailSMTP\Admin\Settings();
        }

        $email_manager = new \TurboSMTP\FreeMailSMTP\Email\Manager();
        add_filter('pre_wp_mail', function ($pre, $atts) use ($email_manager) {
            $this->wp_mail_caller->getSourcePluginName();
            return $email_manager->send_mail($pre, $atts);
        }, 10, 2);
    }

    private function init_hooks()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('admin_bar_menu', [$this, 'free_mail_smtp_add_admin_bar_menu'], 100);
    }

    public function enqueue_admin_scripts($hook)
    {
        $admin_pages = [
            'free-mail-smtp_page_free-mail-smtp-providers',
            'free-mail-smtp_page_free-mail-smtp-logs',
            'free-mail-smtp_page_free-mail-smtp-analytics',
            'free-mail-smtp_page_free-mail-smtp-email-router',
            'free-mail-smtp_page_free-mail-smtp-settings',

        ];

        if (in_array($hook, $admin_pages)) {
            $this->plugin_list_updater->updateActivePluginsOption();
        }

        wp_enqueue_style(
            'free_mail_smtp_admin',
            FREE_MAIL_SMTP_URL . 'assets/css/admin.css',
            [],
            $this->version
        );

        wp_enqueue_style('dashicons');
    }

    function free_mail_smtp_add_admin_bar_menu($wp_admin_bar)
    {
        $wp_admin_bar->add_node([
            'id'    => 'free-mail-smtp',
            'title' => 'Free Mail SMTP',
            'href'  => admin_url('admin.php?page=free-mail-smtp-providers'),
            'meta'  => [
                'title' => __('Free Mail SMTP Plugin', 'free-mail-smtp'),
            ],
        ]);
    }
}
