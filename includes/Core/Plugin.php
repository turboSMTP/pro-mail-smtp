<?php
namespace FreeMailSMTP\Core;

class Plugin {
    private $version;
    
    public function __construct() {
        $this->version = free_mail_smtp_VERSION;
    }

    public function init() {
        $this->load_components();
        $this->init_hooks();
    }

    private function load_components() {
        if (is_admin()) {
            new \FreeMailSMTP\Admin\Menu();
            new \FreeMailSMTP\Admin\Settings();
            new \FreeMailSMTP\Admin\Logs();
            new \FreeMailSMTP\Admin\Analytics();
        }

        $email_manager = new \FreeMailSMTP\Email\Manager();
        
        if ($this->is_configured()) {
            add_filter('pre_wp_mail', [$email_manager, 'send_mail'], 10, 2);
        }
    }

    private function init_hooks() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }

    public function enqueue_admin_scripts($hook) {
        // CSS
        wp_enqueue_style(
            'free_mail_smtp-admin',
            free_mail_smtp_URL . 'assets/css/admin.css',
            [],
            $this->version
        );

        // JavaScript
        wp_enqueue_script(
            'free_mail_smtp-admin',
            free_mail_smtp_URL . 'assets/js/admin.js',
            ['jquery'],
            $this->version,
            true
        );

        wp_localize_script('free_mail_smtp-admin', 'FreeMailSMTPAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('free_mail_smtp_save_providers'),
            'debug' => true
        ]);

        // Add dashicons
        wp_enqueue_style('dashicons');

        error_log('Scripts enqueued successfully');
    }

    private function is_configured() {
        $providers = get_option('free_mail_smtp_providers', []);
        return !empty($providers);
    }
}