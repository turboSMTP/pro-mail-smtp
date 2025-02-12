<?php
namespace FreeMailSMTP\Admin;

class Menu {
    private $plugin_path;

    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu_items']);
        $this->plugin_path = plugin_dir_path(dirname(dirname(__FILE__)));
    }

    private function get_svg_icon() {
        $svg_path = $this->plugin_path . '/assets/img/icon-white-svg.svg';
        if (file_exists($svg_path)) {
            return 'data:image/svg+xml;base64,' . base64_encode(file_get_contents($svg_path));
        }
        return 'dashicons-email';
    }

    public function add_menu_items() {
        $parent_slug = 'free_mail_smtp-settings';

        add_menu_page(
            'Free Mail SMTP',
            'Free Mail SMTP',
            'manage_options',
            $parent_slug,
            [$this, 'render_settings_page'],
            $this->get_svg_icon(),
            30
        );

        $submenu_pages = [
            [
                'title' => 'Settings',
                'menu_title' => 'Settings',
                'capability' => 'manage_options',
                'slug' => $parent_slug,
                'callback' => 'render_settings_page'
            ],
            [
                'title' => 'Email Logs',
                'menu_title' => 'Email Logs',
                'capability' => 'manage_options',
                'slug' => 'free_mail_smtp-logs',
                'callback' => 'render_logs_page'
            ],
            [
                'title' => 'Providers Logs',
                'menu_title' => 'Providers Logs',
                'capability' => 'manage_options',
                'slug' => 'free_mail_smtp-analytics',
                'callback' => 'render_analytics_page'
            ],
            [
                'title' => 'Email Router',
                'menu_title' => 'Email Router',
                'capability' => 'manage_options',
                'slug' => 'free_mail_smtp-email-router',
                'callback' => 'render_email_router_page'
            ]
        ];

        foreach ($submenu_pages as $page) {
            add_submenu_page(
                $parent_slug,
                $page['title'],
                $page['menu_title'],
                $page['capability'],
                $page['slug'],
                [$this, $page['callback']]
            );
        }
    }

    public function render_settings_page() {
        (new Settings())->render();
    }

    public function render_analytics_page() {
        (new Analytics())->render();
    }

    public function render_logs_page() {
        (new Logs())->render();
    }
    public function render_email_router_page() {
        (new EmailRouter())->render();
    }
}