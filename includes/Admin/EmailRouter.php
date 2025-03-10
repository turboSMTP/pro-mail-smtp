<?php
namespace FreeMailSMTP\Admin;

class EmailRouter {
    private $providersList = [];
    private $plugin_path;

    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_save_email_router', [$this, 'save_email_router']);
        add_action('wp_ajax_update_email_router_status', [$this, 'update_condition_status']); 
        add_action('wp_ajax_get_email_router_condition', [$this, 'get_email_router_condition']); 
        add_action('wp_ajax_delete_email_router_condition', [$this, 'delete_email_router_condition']); 

        $this->providersList = include __DIR__ . '/../../config/providers-list.php';
        $this->plugin_path = dirname(dirname(dirname(__FILE__)));
    }

    private function get_active_plugins_list() {
        return get_option('free_mail_smtp_active_plugins_list', []);
    }

    public function enqueue_scripts($hook) {

        if ($hook !== 'free-mail-smtp_page_free_mail_smtp-email-router') {
            return;
        }
    
        wp_enqueue_style(
            'free_mail_smtp_email-router',
            plugins_url('/assets/css/emailrouter.css', dirname(dirname(__FILE__))),
            [],
            '1.0.0'
        );
    
        wp_enqueue_script(
            'free_mail_smtp_email-router',
            plugins_url('/assets/js/emailrouter.js', dirname(dirname(__FILE__))),
            ['jquery'],
            '1.0.0',
            true
        );
        
        wp_localize_script('free_mail_smtp_email-router', 'FreeMailSMTPEmailRouter', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('free_mail_smtp_email-router'),
            'debug' => true,
            'pluginsList' => wp_json_encode($this->get_active_plugins_list()) // Ensure proper JSON encoding
        ]);
    }
    public function render() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        $conditions_repo = new \FreeMailSMTP\DB\ConditionRepository();
        $connections_repo = new \FreeMailSMTP\DB\ConnectionRepository();

        $conditions_list = $conditions_repo->load_all_conditions();
        $connections_list = $connections_repo->get_all_connections();
        $providers_list = $this->providersList;
        $view_file = $this->plugin_path . '/views/admin/emailrouter/index.php';
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            echo '<div class="wrap">';
            echo '<h1>Free Mail SMTP Email Router</h1>';
            echo '<div class="notice notice-error"><p>Error: View file not found.</p></div>';
            echo '</div>';
        }
    }
    
    public function save_email_router() {
        check_ajax_referer('free_mail_smtp_email-router', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }

        $data = isset($_POST['data']) ? $_POST['data'] : array();
        $prepared_data = [
            'connection_id'        => sanitize_text_field($data['connection']['selected']),
            'condition_data'       => wp_json_encode($data['conditions']),
            'condition_label'      => sanitize_text_field($data['label']),
            'overwrite_connection' => $data['connection']['enabled'] ? 1 : 0,
            'overwrite_sender'     => $data['email']['enabled'] ? 1 : 0,
            'forced_senderemail'   => $data['email']['enabled'] ? sanitize_email($data['email']['email']) : null,
            'forced_sendername'    => $data['email']['enabled'] ? sanitize_text_field($data['email']['name']) : null,
            'is_enabled'           => $data['is_enabled'],
        ];

        $condition_repo = new \FreeMailSMTP\DB\ConditionRepository();
        if (isset($data['id']) && !empty($data['id'])) {
            $condition_id = absint($data['id']);
            $success = $condition_repo->update_condition($condition_id, $prepared_data);
            
            if (!$success) {
                wp_send_json_error(['message' => 'Failed to update router condition.']);
                return;
            }
            
            wp_send_json_success([
                'message' => 'Router condition updated successfully!',
                'id' => $condition_id,
                'operation' => 'update'
            ]);
        } 
        else {
            $insert_id = $condition_repo->add_condition($prepared_data);
            
            if (!$insert_id) {
                wp_send_json_error(['message' => 'Failed to create new router condition.']);
                return;
            }
            
            wp_send_json_success([
                'message' => 'New router condition created successfully!',
                'id' => $insert_id,
                'operation' => 'insert'
            ]);
        }
    }

    public function update_condition_status() {
        check_ajax_referer('free_mail_smtp_email-router', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }

        $condition_id = isset($_POST['condition_id']) ? absint($_POST['condition_id']) : 0;
        $status = isset($_POST['status']) ? absint($_POST['status']) : 0;

        if (!$condition_id) {
            wp_send_json_error(['message' => 'Invalid condition ID']);
            return;
        }

        $update_data = ['is_enabled' => $status];
        $condition_repo = new \FreeMailSMTP\DB\ConditionRepository();
        $updated = $condition_repo->update_condition($condition_id, $update_data);

        if (!$updated) {
            wp_send_json_error(['message' => 'Failed to update status.']);
        } else {
            wp_send_json_success(['message' => 'Status updated successfully']);
        }
    }

    public function get_email_router_condition() {
        check_ajax_referer('free_mail_smtp_email-router', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }
        $condition_id = isset($_POST['condition_id']) ? absint($_POST['condition_id']) : 0;
        if (!$condition_id) {
            wp_send_json_error(['message' => 'Invalid condition ID']);
            return;
        }
        $condition_repo = new \FreeMailSMTP\DB\ConditionRepository();
        $condition = $condition_repo->get_condition($condition_id);
        if (!$condition) {
            wp_send_json_error(['message' => 'Condition not found']);
            return;
        }
        $condition->condition_data = json_decode($condition->condition_data, true);
        wp_send_json_success($condition);
    }

    public function delete_email_router_condition() {
        check_ajax_referer('free_mail_smtp_email-router', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }

        $condition_id = isset($_POST['condition_id']) ? absint($_POST['condition_id']) : 0;
        if (!$condition_id) {
            wp_send_json_error(['message' => 'Invalid condition ID']);
            return;
        }

        $condition_repo = new \FreeMailSMTP\DB\ConditionRepository();
        $deleted = $condition_repo->delete_condition($condition_id);

        if (!$deleted) {
            wp_send_json_error(['message' => 'Failed to delete condition.']);
        } else {
            wp_send_json_success(['message' => 'Condition deleted successfully']);
        }
    }
}