<?php
namespace FreeMailSMTP\Admin;

class Menu {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu_items']);
    }

    public function add_menu_items() {
        $parent_slug = 'free_mail_smtp-settings';

        add_menu_page(
            'Free Mail SMTP', 
            'Free Mail SMTP', 
            'manage_options', 
            'free_mail_smtp-settings', 
            [$this, 'render_settings_page'], 
            'dashicons-email',
            30
        );
        //submenu items
        add_submenu_page(
            $parent_slug,
            'Settings',
            'Settings',
            'manage_options',
            $parent_slug,
            [$this, 'render_settings_page']
        );

        add_submenu_page(
            $parent_slug,
            'Email Logs',
            'Email Logs',
            'manage_options',
            'free_mail_smtp-logs',
            [$this, 'render_logs_page']
        );

        add_submenu_page(
            $parent_slug,
            'Providers Logs',
            'Providers Logs',
            'manage_options',
            'free_mail_smtp-analytics',
            [$this, 'render_analytics_page']
        );

    }

    public function render_settings_page() {
        $settings = new Settings();
        $settings->render();
    }

    public function render_analytics_page() {
        $analytics = new Analytics();
        $analytics->render();
    }

    public function render_logs_page() {
        $logs = new Logs();
        $logs->render();
    }
}