<?php
namespace TurboSMTP\ProMailSMTP\Admin;

if (!defined('ABSPATH')) {
    exit;
}

use TurboSMTP\ProMailSMTP\DB\EmailLogRepository;
use TurboSMTP\ProMailSMTP\Admin\Helpers\LogsHelper;

/**
 * Email Logs Admin Controller
 */
class Logs
{
    private $per_page = 20;
    private $providers_list = [];
    private $log_repository;

    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        $this->log_repository = new EmailLogRepository();
        $this->providers_list = include __DIR__ . '/../../config/providers-list.php';
    }

    public function enqueue_scripts($hook)
    {
        $expected_hook = 'pro-mail-smtp_page_pro-mail-smtp-logs';
        if ($hook !== $expected_hook) {
            return;
        }

        wp_enqueue_script(
            'pro-mail-smtp-logs',
            plugins_url('assets/js/logs.js', PRO_MAIL_SMTP_FILE),
            ['jquery'],
            PRO_MAIL_SMTP_VERSION,
            true
        );

        wp_enqueue_style(
            'pro-mail-smtp-logs',
            plugins_url('assets/css/logs.css', PRO_MAIL_SMTP_FILE),
            [],
            PRO_MAIL_SMTP_VERSION
        );
    }

    /**
     * Render the logs page
     */
    public function render()
    {
        $this->handle_form_submissions();
        
        $data = $this->prepare_view_data();
        
        $this->render_view('index', $data);
    }

    /**
     * Handle form submissions
     */
    private function handle_form_submissions()
    {
        // Handle retention settings update
        if (isset($_POST['retention_duration_setting']) && 
            isset($_POST['pro_mail_smtp_retention_nonce']) && 
            wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pro_mail_smtp_retention_nonce'])), 'pro_mail_smtp_update_retention')) {
            
            update_option('pro_mail_smtp_retention_duration', sanitize_text_field(wp_unslash($_POST['retention_duration_setting'])));
        }
        
        // Handle filters update
        if (isset($_POST['filter_action']) && 
            isset($_POST['pro_mail_smtp_logs_filter_nonce']) && 
            wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pro_mail_smtp_logs_filter_nonce'])), 'pro_mail_smtp_logs_filter')) {
            
            $filter_data = [
                'provider'  => isset($_POST['provider']) ? sanitize_text_field(wp_unslash($_POST['provider'])) : '',
                'status'    => isset($_POST['status']) ? sanitize_text_field(wp_unslash($_POST['status'])) : '',
                'search'    => isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : '',
                'date_from' => isset($_POST['date_from']) ? sanitize_text_field(wp_unslash($_POST['date_from'])) : '',
                'date_to'   => isset($_POST['date_to']) ? sanitize_text_field(wp_unslash($_POST['date_to'])) : '',
                'orderby'   => isset($_POST['orderby']) ? sanitize_text_field(wp_unslash($_POST['orderby'])) : 'sent_at',
                'order'     => isset($_POST['order']) && in_array(strtolower(wp_unslash($_POST['order'])), ['asc', 'desc'], true) 
                            ? strtolower(sanitize_text_field(wp_unslash($_POST['order']))) 
                            : 'desc',
            ];
            
            update_user_meta(get_current_user_id(), 'pro_mail_smtp_log_filters', $filter_data);
        }
    }

    /**
     * Prepare data for the view
     */
    private function prepare_view_data()
    {
        $current_retention = get_option('pro_mail_smtp_retention_duration', 'forever');
        $filters = $this->get_filters();
        $logs = $this->get_logs($filters);
        $total_items = $this->get_total_logs($filters);
        $total_pages = ceil($total_items / $this->per_page);

        return [
            'current_retention' => $current_retention,
            'filters' => $filters,
            'logs' => $logs,
            'total_items' => $total_items,
            'total_pages' => $total_pages,
            'columns' => LogsHelper::get_columns(),
            'providers' => LogsHelper::get_providers($this->providers_list),
            'statuses' => LogsHelper::get_statuses(),
            'format_date' => [LogsHelper::class, 'format_date'],
            'time_diff' => [LogsHelper::class, 'time_diff'],
            'get_column_sort_class' => [LogsHelper::class, 'get_column_sort_class'],
        ];
    }

    /**
     * Render a view file
     */
    private function render_view($view, $data = [])
    {
        $view_file = __DIR__ . '/../../views/admin/logs/' . $view . '.php';
        
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            wp_die(sprintf('View file not found: %s', esc_html($view_file)));
        }
    }

    /**
     * Get logs from repository
     */
    private function get_logs($filters)
    {
        return $this->log_repository->get_logs($filters);
    }

    /**
     * Get total logs count
     */
    private function get_total_logs($filters)
    {
        return $this->log_repository->get_total_logs();
    }

    private function get_filters()
    {
        $defaults = [
            'paged'     => 1,
            'provider'  => '',
            'status'    => '',
            'search'    => '',
            'date_from' => '',
            'date_to'   => '',
            'orderby'   => 'sent_at',
            'order'     => 'desc',
        ];
        if (isset($_POST['pro_mail_smtp_logs_filter_nonce']) && 
            wp_verify_nonce(sanitize_text_field( wp_unslash ($_POST['pro_mail_smtp_logs_filter_nonce'])), 'pro_mail_smtp_logs_filter')) {
            
            $filter_data = [
                'paged'     => isset($_POST['paged']) ? max(1, absint($_POST['paged'])) : $defaults['paged'],
                'provider'  => isset($_POST['provider']) ? sanitize_text_field(wp_unslash($_POST['provider'])) : $defaults['provider'],
                'status'    => isset($_POST['status']) ? sanitize_text_field(wp_unslash($_POST['status'])) : $defaults['status'],
                'search'    => isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : $defaults['search'],
                'date_from' => isset($_POST['date_from']) ? sanitize_text_field(wp_unslash($_POST['date_from'])) : $defaults['date_from'],
                'date_to'   => isset($_POST['date_to']) ? sanitize_text_field(wp_unslash($_POST['date_to'])) : $defaults['date_to'],
                'orderby'   => isset($_POST['orderby']) ? sanitize_text_field(wp_unslash($_POST['orderby'])) : $defaults['orderby'],
                'order'     => isset($_POST['order']) && in_array(strtolower($_POST['order']), ['asc', 'desc'], true) 
                            ? strtolower(sanitize_text_field(wp_unslash($_POST['order']))) 
                            : $defaults['order'],
            ];
            
            $is_pagination_or_sort_only = isset($_POST['filter_action']) && 
                                          $_POST['filter_action'] === 'filter_logs' &&
                                          isset($_POST['paged']);
                                          
            $is_reset = isset($_POST['filter_action']) && 
                        $_POST['filter_action'] === 'filter_logs' &&
                        empty($_POST['provider']) && 
                        empty($_POST['status']) && 
                        empty($_POST['search']) && 
                        empty($_POST['date_from']) && 
                        empty($_POST['date_to']) &&
                        $_POST['paged'] == 1 &&
                        $_POST['orderby'] === 'sent_at' && 
                        $_POST['order'] === 'desc';
            
            if ($is_reset) {
                delete_user_meta(get_current_user_id(), 'pro_mail_smtp_log_filters');
                return $defaults;
            }
            
            if (!$is_pagination_or_sort_only || isset($_POST['provider']) || isset($_POST['status']) || 
                !empty($_POST['search']) || !empty($_POST['date_from']) || !empty($_POST['date_to'])) {
                
                $filter_save = $filter_data;
                $filter_save['paged'] = 1; 
                update_user_meta(get_current_user_id(), 'pro_mail_smtp_log_filters', $filter_save);
            } else {
                $saved_filters = get_user_meta(get_current_user_id(), 'pro_mail_smtp_log_filters', true);
                if (!empty($saved_filters) && is_array($saved_filters)) {
                    $filter_data = array_merge($saved_filters, ['paged' => $filter_data['paged']]);
                }
            }
            
            return $filter_data;
        }
        
        $saved_filters = get_user_meta(get_current_user_id(), 'pro_mail_smtp_log_filters', true);
        if (!empty($saved_filters) && is_array($saved_filters)) {
            return array_merge($defaults, $saved_filters);
        }
        
        return $defaults;
    }
}
