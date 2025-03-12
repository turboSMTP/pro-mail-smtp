<?php

namespace FreeMailSMTP\Admin;

use FreeMailSMTP\Providers\ProviderFactory;

class Providers
{
    private $providersList = [];
    private $provider_factory;
    private $plugin_path;
    private $import_connections;
    private $provider_manager;

    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_test_provider_connection', [$this, 'test_provider_connection']);
        add_action('wp_ajax_save_provider', [$this, 'save_provider']);
        add_action('wp_ajax_delete_provider', [$this, 'delete_provider']);
        add_action('wp_ajax_load_provider_form', [$this, 'load_provider_form']);
        add_action('wp_ajax_import_connections', [$this, 'import_connections']);
        add_action('wp_ajax_free_mail_smtp_set_oauth_token', [$this, 'free_mail_smtp_set_oauth_token']);

        $this->providersList = include __DIR__ . '/../../config/providers-list.php';
        $this->provider_factory = new ProviderFactory();
        $this->import_connections = new \FreeMailSMTP\Core\ImportConnections();
        $this->plugin_path = dirname(dirname(dirname(__FILE__)));
        $this->provider_manager = new \FreeMailSMTP\Core\ProviderManager();
    }

    public function enqueue_scripts($hook)
    {
        if ($hook !== 'toplevel_page_free_mail_smtp-providers') {
            return;
        }
            wp_enqueue_script(
                'free_mail_smtp-admin',
                plugins_url('/assets/js/admin.js', dirname(dirname(__FILE__))),
                ['jquery'],
                '1.0.0',
                true
            );
            wp_enqueue_script(
                'free_mail_smtp-oauth-handler',
                plugins_url('/assets/js/oauth-handler.js', dirname(dirname(__FILE__))),
                ['jquery'],
                '1.0.0',
                true
            );

            wp_localize_script('free_mail_smtp-admin', 'FreeMailSMTPAdminProviders', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('free_mail_smtp_nonce_providers'),
                'adminUrl' => admin_url('admin.php?page=free_mail_smtp-settings'),
                'debug' => true
            ]);
            wp_localize_script('free_mail_smtp-admin', 'FreeMailSMTPOAuth', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'redirectUrl' => admin_url('admin.php?page=free_mail_smtp-providers'),
                'nonce' => wp_create_nonce('free_mail_smtp_set_oauth_token'),
                'debug' => true
            ]);
    }

    public function render()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'free-mail-smtp'));
        }

        $conn_repo = new \FreeMailSMTP\DB\ConnectionRepository();
        $providers_config = $conn_repo->get_all_connections();
        $providers_list = $this->providersList;
        $import_available = $this->import_connections->isImportAvailable();

        $view_file = $this->plugin_path . '/views/admin/providers/index.php';
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            echo '<div class="wrap">';
            echo '<h1>Free Mail SMTP Providers</h1>';
            echo '<div class="notice notice-error"><p>Error: View file not found.</p></div>';
            echo '</div>';
        }
    }

    public function save_provider()
    {
        check_ajax_referer('free_mail_smtp_nonce_providers', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }

        if (!isset($_POST['formData'])) {
            wp_send_json_error('Form data is required');
            return;
        }

        $sanitized_form_data = sanitize_text_field($_POST['formData']);
        parse_str(urldecode($sanitized_form_data), $form_data);
        
        if (empty($form_data['provider'])) {
            wp_send_json_error('Provider is required');
            return;
        }
        try {
            $connection_id = $this->provider_manager->save_provider($form_data);
        } catch (\Exception $e) {
            wp_send_json_error($e->getMessage());
            return;
        }
        wp_send_json_success([
            'message'       => 'Provider saved successfully',
            'connection_id' => $connection_id
        ]);
    }

    public function free_mail_smtp_set_oauth_token()
    {
        check_ajax_referer('free_mail_smtp_set_oauth_token', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }

        $provider_type = sanitize_text_field($_POST['provider_type']);
        if (empty($provider_type)) {
            wp_send_json_error('Provider type not found');
            return;
        }

        $conn_repo = new \FreeMailSMTP\DB\ConnectionRepository();
        $providers = $conn_repo->get_all_connections();
        $connection = null;
        foreach ($providers as $prov) {
            if ($prov->provider === $provider_type) {
                $connection = $prov;
                break;
            }
        }

        if (!$connection) {
            wp_send_json_error('Provider not found');
            return;
        }

        $credential = sanitize_text_field($_POST['code']);
        try {
            $provider_instance = $this->provider_factory->get_provider_class($connection);
            if (!method_exists($provider_instance, 'handle_oauth_callback')) {
                throw new \Exception('Invalid provider');
            }
            $save = $provider_instance->handle_oauth_callback($credential);

            if (!$save) {
                throw new \Exception('Failed to save token');
            }
            $connection->connection_data['authenticated'] = true;
            $result = $conn_repo->update_connection($connection->connection_id, $connection->connection_data);
            if ($result === false) {
                throw new \Exception('Failed to update connection');
            }
            wp_send_json_success($connection->provider . ' connected successfully');
        } catch (\Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    public function test_provider_connection()
    {
        check_ajax_referer('free_mail_smtp_nonce_providers', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }

        $connection_id = sanitize_text_field($_POST['connection_id']);
        $conn_repo = new \FreeMailSMTP\DB\ConnectionRepository();
        $connection = $conn_repo->get_connection($connection_id);
        if (!$connection) {
            wp_send_json_error('Provider not found');
            return;
        }

        try {
            $provider_instance = $this->provider_factory->get_provider_class($connection);

            $result = $provider_instance->test_connection();
            if ($result) {
                wp_send_json_success($result);
            } else {
                wp_send_json_error($result);
            }
        } catch (\Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    public function delete_provider()
    {
        check_ajax_referer('free_mail_smtp_nonce_providers', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }
        $connection_id = sanitize_text_field($_POST['connection_id']);
        $conn_repo = new \FreeMailSMTP\DB\ConnectionRepository();
        $connection = $conn_repo->get_connection($connection_id);
        if (!$connection) {
            wp_send_json_error('Provider not found');
            return;
        }
        $this->clear_provider_tokens(['provider' => $connection->provider]);
        $conn_repo->delete_connection($connection_id);

        wp_send_json_success('Provider deleted successfully.');
    }

    public function load_provider_form()
    {
        check_ajax_referer('free_mail_smtp_nonce_providers', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }
        $provider = sanitize_text_field($_POST['provider']);
        $is_edit = isset($_POST['connection_id']) ? true : false;
        if ($is_edit) {
            $connection_id = sanitize_text_field($_POST['connection_id']);
        }
        $form_file = $this->plugin_path . "/views/admin/providers/provider-forms/{$provider}.php";

        if (file_exists($form_file)) {
            ob_start();
            include $form_file;
            $form_html = ob_get_clean();
            wp_send_json_success([
                'html'    => $form_html,
                'is_edit' => $is_edit
            ]);
        } else {
            wp_send_json_error('Provider form not found');
        }
    }

    private function clear_provider_tokens($provider)
    {
        if ($provider['provider'] === 'gmail') {
            delete_option('free_mail_smtp_gmail_access_token');
            delete_option('free_mail_smtp_gmail_refresh_token');
        }
    }


    public function import_connections()
    {
        check_ajax_referer('free_mail_smtp_import', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }
        try {
            $result =  $this->import_connections->importProviders($_POST['plugin']);
            return wp_send_json_success([$result]);
        } catch (\Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }
}
