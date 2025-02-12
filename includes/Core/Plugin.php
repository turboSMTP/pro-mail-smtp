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
            new \FreeMailSMTP\Admin\EmailRouter();
        }

        $email_manager = new \FreeMailSMTP\Email\Manager();
        add_filter('pre_wp_mail', [$email_manager, 'send_mail'], 10, 2);

    }

    private function init_hooks() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }

    public function enqueue_admin_scripts($hook) {
        wp_enqueue_style(
            'free_mail_smtp-admin',
            free_mail_smtp_URL . 'assets/css/admin.css',
            [],
            $this->version
        );

        wp_enqueue_style('dashicons');
    }

}