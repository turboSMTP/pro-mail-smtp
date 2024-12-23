<?php
namespace FreeMailSMTP\Admin;

class Analytics {
    private $providers = [];
    private $providersList = [];

    private $plugin_path;

    public function __construct() {
        $this->plugin_path = dirname(dirname(dirname(__FILE__)));
        $this->providers = get_option('free_mail_smtp_providers', []);
        $this->providersList = include __DIR__ . '/../../config/providers-list.php';

        add_action('wp_ajax_fetch_provider_analytics', [$this, 'fetch_provider_analytics']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);

    }

    public function enqueue_scripts($hook) {

        // Only load on analytics page
        if ($hook !== 'free-mail-smtp_page_free_mail_smtp-analytics') {
            return;
        }
    
        // Enqueue analytics specific CSS and JS
        wp_enqueue_style(
            'free_mail_smtp_analytics',
            plugins_url('/assets/css/analytics.css', dirname(dirname(__FILE__))),
            [],
            '1.0.0'
        );
    
        wp_enqueue_script(
            'free_mail_smtp_analytics',
            plugins_url('/assets/js/analytics.js', dirname(dirname(__FILE__))),
            ['jquery'],
            '1.0.0',
            true
        );

        wp_localize_script(
            'free_mail_smtp_analytics',
            'FreeMailSMTPAnalytics',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('free_mail_smtp_analytics')
            ]
        );
    }

    public function render() {
        $data = [
            'providers' => $this->providers,
            'filters' => $this->get_filter_values(),
            'analytics_data' => $this->get_analytics_data()
        ];


        // Include the main view file
        $view_file = $this->plugin_path . '/views/admin/analytics/index.php';
        if (file_exists($view_file)) {
            include $view_file;
        }
    }

    private function get_analytics_data() {
        $filters = $this->get_filter_values();
        error_log('selected provider '.$filters['selected_provider']);

        try {
            if (!empty($filters['selected_provider'])) {

                return $this->get_provider_analytics($filters['selected_provider'], $filters);
            } else {
                $all_data = [];
                foreach ($this->providers as $provider) {
                    $provider_data = $this->get_provider_analytics($provider['provider'], $filters);
                    $all_data = array_merge($all_data, $provider_data);
                }
                return $all_data;
            }
        } catch (\Exception $e) {
            error_log('Analytics Error: ' . $e->getMessage());
            return [];
        }
    }

    private function get_filter_values() {
        return [
            'selected_provider' => isset($_GET['provider']) ? sanitize_text_field($_GET['provider']) : '',
            'selected_status' => isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '',
            'date_from' => isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '',
            'date_to' => isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : ''
        ];
    }

    public function fetch_provider_analytics() {
        check_ajax_referer('free_mail_smtp_analytics', 'nonce');

        $provider_id = sanitize_text_field($_POST['filters']['provider']);
        $status = isset($_POST['filters']['status']) ? sanitize_text_field($_POST['filters']['status']): '';
        $date_from = sanitize_text_field($_POST['filters']['date_from']);
        $date_to = sanitize_text_field($_POST['filters']['date_to']);

        try {
            $provider_data = $this->get_provider_analytics($provider_id,['status' => $status, 'date_from' => $date_from, 'date_to' => $date_to]);
            wp_send_json_success($provider_data);
        } catch (\Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    private function get_provider_analytics($provider_id, $filters) {
        $provider_config = $this->get_provider_config($provider_id);

        if (!$provider_config) {
            throw new \Exception('Provider configuration not found');
        }

        // Initialize provider class
        $provider_class = '\\FreeMailSMTP\\Providers\\' . $this->providersList[$provider_config['provider']];
        if (!class_exists($provider_class)) {
            throw new \Exception('Invalid provider');
        }
    
        $provider = new $provider_class($provider_config['config_keys']);

        return $provider->get_analytics($filters);
    }

private function get_provider_config($provider_id) {

    foreach ($this->providers as $provider) {
        if ($provider['id'] === $provider_id) {
            return $provider;
        }
    }
    return null;
}
}