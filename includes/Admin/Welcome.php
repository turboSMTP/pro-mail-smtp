<?php

namespace FreeMailSMTP\Admin;

class Welcome {
    public function __construct() {
        add_action('admin_init', [$this, 'maybe_redirect_to_welcome']);
        add_action('admin_menu', [$this, 'register_welcome_page']);
        add_action('admin_init', [$this, 'handle_welcome_form']);
    }
    
    public function maybe_redirect_to_welcome() {
        if (isset($_GET['page']) && $_GET['page'] === 'free_mail_smtp-welcome') {
            return;
        }
        
        if (!isset($_GET['page']) || strpos($_GET['page'], 'free_mail_smtp') === false) {
            return;
        }
        
        $consent_value = get_option('free_mail_smtp_analytics_consent', 'not_set');        
        if (($consent_value === 'not_set' || $consent_value === null) && is_admin()) {
            wp_safe_redirect(admin_url('admin.php?page=free_mail_smtp-welcome'));
            exit;
        }
    }
    
    public function register_welcome_page() {
        add_submenu_page(
            null,
            'Welcome to Free Mail SMTP',
            'Welcome',
            'manage_options',
            'free_mail_smtp-welcome',
            [$this, 'render_welcome_page']
        );
    }
    
    public function render_welcome_page() {
        $view_file = dirname(dirname(dirname(__FILE__))) . '/views/admin/welcome/index.php';
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            echo '<div class="wrap">';
            echo '<h1>Welcome to Free Mail SMTP</h1>';
            echo '<div class="notice notice-error"><p>Error: Welcome view file not found.</p></div>';
            echo '</div>';
        }
    }

    public function handle_welcome_form() {
        if (isset($_POST['save_consent']) && isset($_POST['free_mail_smtp_welcome_nonce'])) {
            if (!wp_verify_nonce($_POST['free_mail_smtp_welcome_nonce'], 'free_mail_smtp_welcome_consent')) {
                wp_die('Security check failed');
            }
            $consent = isset($_POST['data_collection_consent']) && $_POST['data_collection_consent'] === 'yes' ? 1 : 0;
            update_option('free_mail_smtp_analytics_consent', $consent);
            wp_redirect(admin_url('admin.php?page=free_mail_smtp-providers'));
            exit;
        }
    }
}