<?php

namespace FreeMailSMTP\Core;

use FreeMailSMTP\Helpers\PluginListUpdater;

class Plugin
{
    private $version;
    private $wp_mail_caller;
    private $plugin_list_updater;

    public function __construct()
    {
        $this->version = free_mail_smtp_VERSION;
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
        \FreeMailSMTP\Cron\CronManager::get_instance()->init();
        \FreeMailSMTP\Cron\CronManager::get_instance()->activate_crons();


        if (is_admin()) {
            new \FreeMailSMTP\Admin\Menu();
            new \FreeMailSMTP\Admin\Providers();
            new \FreeMailSMTP\Admin\Logs();
            new \FreeMailSMTP\Admin\Analytics();
            new \FreeMailSMTP\Admin\EmailRouter();
            new \FreeMailSMTP\Admin\Settings();
        }

        $email_manager = new \FreeMailSMTP\Email\Manager();
        add_filter('pre_wp_mail', function ($pre, $atts) use ($email_manager) {
            $this->wp_mail_caller->get_source_plugin_name();
            return $email_manager->send_mail($pre, $atts);
        }, 10, 2);
    }

    private function init_hooks()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('admin_bar_menu', [$this, 'my_plugin_add_admin_bar_menu'], 100);
    }

    public function enqueue_admin_scripts($hook)
    {
        $admin_pages = [
            'free-mail-smtp_page_free_mail_smtp-providers',
            'free-mail-smtp_page_free_mail_smtp-logs',
            'free-mail-smtp_page_free_mail_smtp-analytics',
            'free-mail-smtp_page_free_mail_smtp-email-router',
            'free-mail-smtp_page_free_mail_smtp-settings',

        ];

        if (in_array($hook, $admin_pages)) {
            $this->plugin_list_updater->updateActivePluginsOption();
        }

        wp_enqueue_style(
            'free_mail_smtp-admin',
            free_mail_smtp_URL . 'assets/css/admin.css',
            [],
            $this->version
        );

        wp_enqueue_style('dashicons');
    }

    function my_plugin_add_admin_bar_menu($wp_admin_bar)
    {
        $wp_admin_bar->add_node([
            'id'    => 'free-mail-smtp',
            'title' => 'Free Mail SMTP',
            'href'  => admin_url('admin.php?page=free_mail_smtp-providers'),
            'meta'  => [
                'title' => __('Free Mail SMTP Plugin', 'free-mail-smtp'),
            ],
        ]);
    }
}
