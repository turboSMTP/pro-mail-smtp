<?php

namespace FreeMailSMTP\Admin;

class Settings
{
    private $providersList = [];

    private $plugin_path;

    public function __construct()
    {
        add_action('admin_init', [$this, 'handle_form_submissions']);
        add_action('admin_enqueue_scripts', [$this, 'admin_scripts']);
        add_action('wp_ajax_test_provider_connection', [$this, 'test_provider_connection']);
        add_action('wp_ajax_save_provider', [$this, 'save_provider']);
        add_action('wp_ajax_delete_provider', [$this, 'delete_provider']);
        add_action('wp_ajax_load_provider_form', [$this, 'load_provider_form']);
        add_action('wp_ajax_free_mail_smtp_set_gmail_token', [$this, 'free_mail_smtp_set_gmail_token']);

        $this->providersList = include __DIR__ . '/../../config/providers-list.php';

        $this->plugin_path = dirname(dirname(dirname(__FILE__)));
    }

    public function render()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        // Get data for views
        $providers_config = get_option('free_mail_smtp_providers', []);
        $from_email = get_option('free_mail_smtp_from_email', get_option('admin_email'));
        $from_name = get_option('free_mail_smtp_from_name', get_option('blogname'));
        $providers_list = $this->providersList;

        // Include the main view file
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

        $providers = get_option('free_mail_smtp_providers', []);

        if (count($providers) >= 10) {
            wp_send_json_error('Maximum number of providers (10) reached, please delete an existing provider to add a new one.');
            return;
        }

        // only one Gmail provider can be added
        if ($form_data['provider'] === 'gmail') {
            foreach ($providers as $existing_provider) {
                if ($existing_provider['provider'] === 'gmail') {
                    wp_send_json_error('Only one Gmail provider can be added.');
                    return;
                }
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

        // Create provider data array
        if (isset($form_data['provider']) && !empty($form_data['provider'])) {
            $provider = sanitize_text_field($form_data['provider']);
        } else {
            wp_send_json_error('Provider is required');
            return;
        }

        $priority = isset($form_data['priority']) ? intval($form_data['priority']) : 0;

        // Check if priority already exists
        foreach ($providers as &$existing_provider) {
            if ($existing_provider['priority'] === $priority) {
                // Increment priorities of existing providers starting from the conflicting priority
                foreach ($providers as &$provider_to_adjust) {
                    if ($provider_to_adjust['priority'] >= $priority) {
                        $provider_to_adjust['priority'] += 1;
                    }
                }
                break;
            }
        }
        unset($existing_provider);

        $provider_id = uniqid();
        $connection_label = isset($form_data['connection_label']) ? sanitize_text_field($form_data['connection_label']) : $provider . '-' . $provider_id;

        if ($provider === 'gmail') {
            $gmail = new \FreeMailSMTP\Providers\Gmail([
                'client_id' => $config_keys['client_id'],
                'client_secret' => $config_keys['client_secret']
            ]);
            $config_keys['auth_url'] = $gmail->get_auth_url();
            $config_keys['authenticated'] = false;
        }

        $provider_data = [
            'id' => $provider_id,
            'provider' => $provider,
            'config_keys' => $config_keys,
            'priority' => $priority,
            'connection_label' => $connection_label
        ];

        // Check if we're editing or adding
        if ($form_data['provider_index'] !== '') {
            // Edit existing provider
            $index = intval($form_data['provider_index']);
            if (isset($providers[$index])) {
                $providers[$index] = $provider_data;
                error_log('Updating provider at index: ' . $index);
            }
        } else {
            // Add new provider
            $providers[] = $provider_data;
            error_log('Adding new provider');
            error_log('Providers data: ' . print_r($providers, true));
        }

        // Sort providers by priority
        usort($providers, function ($a, $b) {
            return $a['priority'] - $b['priority'];
        });

        // Save to database
        $updated = update_option('free_mail_smtp_providers', $providers);
        error_log('Update result: ' . ($updated ? 'success' : 'failed'));

        wp_send_json_success([
            'message' => 'Provider saved successfully',
            'providers' => $providers
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

    function free_mail_smtp_set_gmail_token()
    {
        error_log('free_mail_smtp_set_gmail_token');
        check_ajax_referer('free_mail_smtp_set_gmail_token', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }
        $providers = get_option('free_mail_smtp_providers', []);
        $index = null;
            foreach ($providers as $provider_index => $config) {
                if ($config['provider'] === 'gmail') {
                    $index = $provider_index;
                    break;
                }
            }
      
        if (!isset($providers[$index])) {
            wp_send_json_error('Provider not found');
            return;
        }
        error_log('Provider Index: ' . print_r($index, true));

        $provider = $providers[$index];

        $credential = $_POST['code'];
        try {
            // Initialize provider class
            $provider_class = '\\FreeMailSMTP\\Providers\\' . $this->providersList[$provider['provider']];

            if (!class_exists($provider_class)) {
                throw new \Exception('Invalid provider');
            }

            $provider_instance = new $provider_class($provider['config_keys']);
            if (!method_exists($provider_instance, 'handle_oauth_callback')) {
                throw new \Exception('Invalid provider');
            }

           $save = $provider_instance->handle_oauth_callback($credential);
              if(!$save){
                throw new \Exception('Failed to save token');
              }

            $provider['authenticated'] = true;
            $providers[$index] = $provider;
            usort($providers, function ($a, $b) {
               return $a['priority'] - $b['priority'];
           });
   
            update_option('free_mail_smtp_providers', $providers);

            wp_send_json_success('Gmail connected successfully');
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

        $index = intval($_POST['index']);
        $providers = get_option('free_mail_smtp_providers', []);

        if (!isset($providers[$index])) {
            wp_send_json_error('Provider not found');
            return;
        }

        $provider = $providers[$index];

        try {
            // Initialize provider class
            $provider_class = '\\FreeMailSMTP\\Providers\\' . $this->providersList[$provider['provider']];

            if (!class_exists($provider_class)) {
                throw new \Exception('Invalid provider');
            }

            $provider_instance = new $provider_class($provider['config_keys']);
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

        $index = intval($_POST['index']);
        $providers = get_option('free_mail_smtp_providers', []);
            $this->clear_provider_tokens($providers[$index]);
        if (isset($providers[$index])) {
            unset($providers[$index]);
            $providers = array_values($providers); // Reindex array
            update_option('free_mail_smtp_providers', $providers);
            $this->clear_provider_tokens($providers[$index]);
            wp_send_json_success();
        } else {
            wp_send_json_error('Provider not found');
        }
    }

    public function admin_scripts($hook)
    {
        if (strpos($hook, 'free_mail_smtp') === false) {
            return;
        }

        wp_enqueue_style('dashicons');

        wp_enqueue_script(
            'free_mail_smtp-settings',
            plugins_url('/includes/assets/js/admin.js', dirname(dirname(__FILE__))),
            ['jquery'],
            time(), // Use for development to prevent caching
            true
        );

        wp_localize_script('free_mail_smtp-settings', 'FreeMailSMTPAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('free_mail_smtp_save_providers'),
            'debug' => true
        ]);

        wp_localize_script('free_mail_smtp-settings', 'FreeMailSMTPGoogleAuth', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'redirectUrl' => admin_url('admin.php?page=free_mail_smtp-settings'),
            'nonce' => wp_create_nonce('free_mail_smtp_set_gmail_token'),
            'debug' => true
        ]);
    }

    public function load_provider_form()
    {
        check_ajax_referer('free_mail_smtp_save_providers', 'nonce');


        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }
        $provider = sanitize_text_field($_POST['provider']);
        $is_edit = isset($_POST['index']);
        $form_file = $this->plugin_path . "/views/admin/settings/provider-forms/{$provider}.php";

        if (file_exists($form_file)) {
            ob_start();
            include $form_file;
            $form_html = ob_get_clean();
            wp_send_json_success([
                'html' => $form_html,
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
