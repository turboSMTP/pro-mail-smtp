<?php

namespace FreeMailSMTP\Admin;
use FreeMailSMTP\Providers\ProviderFactory;

class Settings
{
    private $providersList = [];
    private $provider_factory;
    private $plugin_path;

    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_init', [$this, 'handle_form_submissions']);
        add_action('wp_ajax_test_provider_connection', [$this, 'test_provider_connection']);
        add_action('wp_ajax_save_provider', [$this, 'save_provider']);
        add_action('wp_ajax_delete_provider', [$this, 'delete_provider']);
        add_action('wp_ajax_load_provider_form', [$this, 'load_provider_form']);
        add_action('wp_ajax_free_mail_smtp_set_oauth_token', [$this, 'free_mail_smtp_set_oauth_token']);

        $this->providersList = include __DIR__ . '/../../config/providers-list.php';
        $this->provider_factory = new ProviderFactory();

        $this->plugin_path = dirname(dirname(dirname(__FILE__)));
    }

    public function enqueue_scripts($hook) {
        if ($hook !== 'toplevel_page_free_mail_smtp-settings') {
            return;
        }
    
        wp_enqueue_script(
            'free_mail_smtp-admin',
            plugins_url('/assets/js/admin.js', dirname(dirname(__FILE__))),
            ['jquery'],
            '1.0.0',
            true
        );

        wp_localize_script('free_mail_smtp-admin', 'FreeMailSMTPAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('free_mail_smtp_save_providers'),
            'debug' => true
        ]);
        wp_localize_script('free_mail_smtp-admin', 'FreeMailSMTPOAuth', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'redirectUrl' => admin_url('admin.php?page=free_mail_smtp-settings'),
            'nonce' => wp_create_nonce('free_mail_smtp_set_oauth_token'),
            'debug' => true
        ]);
    }
    
    public function render()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $conn_repo = new \FreeMailSMTP\DB\ConnectionRepository();
        $providers_config = $conn_repo->get_all_connections();
        
        $from_email = get_option('free_mail_smtp_from_email', get_option('admin_email'));
        $from_name = get_option('free_mail_smtp_from_name', get_option('blogname'));
        $providers_list = $this->providersList;

        $view_file = $this->plugin_path . '/views/admin/settings/index.php';
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            echo '<div class="wrap">';
            echo '<h1>Free Mail SMTP Settings</h1>';
            echo '<div class="notice notice-error"><p>Error: View file not found.</p></div>';
            echo '</div>';
        }
    }

    public function save_provider()
    {
        check_ajax_referer('free_mail_smtp_save_providers', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }

        parse_str(urldecode($_POST['formData']), $form_data);
        if (empty($form_data['provider'])) {
            wp_send_json_error('Provider is required');
            return;
        }
        $provider = sanitize_text_field($form_data['provider']);
        
        global $wpdb;
        if ($provider === 'gmail') {
            $count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}free_mail_smtp_connections WHERE provider = %s",
                'gmail'
            ));
            if ($count > 0) {
                wp_send_json_error('Only one Gmail provider can be added.');
                return;
            }
        }
        if ($provider === 'outlook') {
            $count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}free_mail_smtp_connections WHERE provider = %s",
                'outlook'
            ));
            if ($count > 0) {
                wp_send_json_error('Only one Outlook provider can be added.');
                return;
            }
        }

        $config_keys = [];
        if (isset($form_data['config_keys']) && is_array($form_data['config_keys'])) {
            foreach ($form_data['config_keys'] as $key => $value) {
                if (empty($value)) {
                    wp_send_json_error('API key is required');
                    return;
                }
                $config_keys[$key] = sanitize_text_field($value);
            }
        } else {
            wp_send_json_error('Config keys are required');
            return;
        }
        
        $priority = isset($form_data['priority']) ? intval($form_data['priority']) : 1;
        $connection_label = (isset($form_data['connection_label']) && !empty($form_data['connection_label']))
            ? sanitize_text_field($form_data['connection_label'])
            : $provider . '-' . uniqid();
        $config_keys['connection_label'] = $connection_label;

        if ($provider === 'gmail') {
            $gmail = new \FreeMailSMTP\Providers\Gmail([
                'client_id'     => $config_keys['client_id'],
                'client_secret' => $config_keys['client_secret']
            ]);
            $config_keys['auth_url'] = $gmail->get_auth_url();
            $config_keys['authenticated'] = false;
        }
        if ($provider === 'outlook') {
            $outlook = new \FreeMailSMTP\Providers\Outlook([
                'client_id'     => $config_keys['client_id'],
                'client_secret' => $config_keys['client_secret']
            ]);
            $config_keys['auth_url'] = $outlook->get_auth_url();
            $config_keys['authenticated'] = false;
        }

        $conn_repo = new \FreeMailSMTP\DB\ConnectionRepository();
        if (isset($form_data['connection_id']) && !empty($form_data['connection_id'])) {
            $connection_id = sanitize_text_field($form_data['connection_id']);
            $result = $conn_repo->update_connection($connection_id, $config_keys, $connection_label, $priority);
            if ($result === false) {
                wp_send_json_error('Failed to update provider.');
                return;
            }
        } else {
            $connection_id = uniqid();
            $result = $conn_repo->insert_connection($connection_id, $provider, $config_keys, $priority, $connection_label);
            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
                return;
            } elseif ($result === false) {
                wp_send_json_error('Failed to add provider.');
                return;
            }
        }

        wp_send_json_success([
            'message'       => 'Provider saved successfully',
            'connection_id' => $connection_id
        ]);
    }

    public function handle_form_submissions()
    {
        if (!isset($_POST['free_mail_smtp_nonce'])) {
            return;
        }

        if (isset($_POST['save_settings'])) {
            check_admin_referer('free_mail_smtp_settings', 'free_mail_smtp_nonce');

            update_option('free_mail_smtp_from_email', sanitize_email($_POST['from_email']));
            update_option('free_mail_smtp_from_name', sanitize_text_field($_POST['from_name']));

            add_settings_error(
                'free_mail_smtp_messages',
                'settings_updated',
                __('Settings saved successfully.', 'free_mail_smtp'),
                'updated'
            );
        }
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
        check_ajax_referer('free_mail_smtp_save_providers', 'nonce');
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
        check_ajax_referer('free_mail_smtp_save_providers', 'nonce');
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
        check_ajax_referer('free_mail_smtp_save_providers', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }
        $provider = sanitize_text_field($_POST['provider']);
        $is_edit = isset($_POST['connection_id']) ? true : false;
        if ($is_edit) {
            $connection_id = sanitize_text_field($_POST['connection_id']);
        }
        $form_file = $this->plugin_path . "/views/admin/settings/provider-forms/{$provider}.php";

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
            if($provider['provider'] === 'gmail'){
                delete_option('free_mail_smtp_gmail_access_token');
                delete_option('free_mail_smtp_gmail_refresh_token');
            }
    }
}
