<?php
namespace TurboSMTP\FreeMailSMTP\Admin;
if ( ! defined( 'ABSPATH' ) ) exit;

class EmailRouter {
    private $providersList = [];
    private $plugin_path;

    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_free_mail_smtp_save_email_router', [$this, 'save_email_router']);
        add_action('wp_ajax_free_mail_smtp_update_email_router_status', [$this, 'update_condition_status']); 
        add_action('wp_ajax_free_mail_smtp_get_email_router_condition', [$this, 'get_email_router_condition']); 
        add_action('wp_ajax_free_mail_smtp_delete_email_router_condition', [$this, 'delete_email_router_condition']); 

        $this->providersList = include __DIR__ . '/../../config/providers-list.php';
        $this->plugin_path = FREE_MAIL_SMTP_PATH;
    }

    private function get_active_plugins_list() {
        return get_option('free_mail_smtp_active_plugins_list', []);
    }

    public function enqueue_scripts($hook) {

        if ($hook !== 'free-mail-smtp_page_free-mail-smtp-email-router') {
            return;
        }
    
        wp_enqueue_style(
            'free-mail-smtp-email-router',
            plugins_url('/assets/css/emailrouter.css', FREE_MAIL_SMTP_FILE),
            [],
            FREE_MAIL_SMTP_VERSION
        );
    
        wp_enqueue_script(
            'free-mail-smtp-email-router',
            plugins_url('/assets/js/emailrouter.js', FREE_MAIL_SMTP_FILE),
            ['jquery'],
            FREE_MAIL_SMTP_VERSION,
            true
        );
        
        wp_localize_script('free-mail-smtp-email-router', 'FreeMailSMTPEmailRouter', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('free_mail_smtp_email_router'),
            'debug' => true,
            'pluginsList' => wp_json_encode($this->get_active_plugins_list())
        ]);
    }
    public function render() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'free-mail-smtp'));
        }
        $conditions_repo = new \TurboSMTP\FreeMailSMTP\DB\ConditionRepository();
        $connections_repo = new \TurboSMTP\FreeMailSMTP\DB\ConnectionRepository();

        $conditions_list = $conditions_repo->load_all_conditions();
        $connections_list = $connections_repo->get_all_connections();
        $providers_list = $this->providersList;
        $view_file = $this->plugin_path . '/views/admin/emailrouter/index.php';
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            echo '<div class="wrap">';
            echo '<h1>' . esc_html__('Free Mail SMTP Email Router', 'free-mail-smtp') . '</h1>';
            echo '<div class="notice notice-error"><p>' . esc_html__('Error: View file not found.', 'free-mail-smtp') . '</p></div>';
            echo '</div>';
        }
    }
    
    public function save_email_router() {
        check_ajax_referer('free_mail_smtp_email_router', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }

        $unslashed_data = wp_unslash( $_POST );
        // phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $data = isset($unslashed_data['data']) ? $unslashed_data['data'] : array();
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

        $condition_repo = new \TurboSMTP\FreeMailSMTP\DB\ConditionRepository();
        if (isset($data['id']) && !empty($data['id'])) {
            $condition_id = absint($data['id']);
            $success = $condition_repo->update_condition($condition_id, $prepared_data);
            
            if (!$success) {
                wp_send_json_error(['message' => esc_html__('Failed to update router condition.', 'free-mail-smtp')]);
                return;
            }
            
            wp_send_json_success([
                'message' => esc_html__('Router condition updated successfully!', 'free-mail-smtp'),
                'id' => $condition_id,
                'operation' => 'update'
            ]);
        } 
        else {
            $insert_id = $condition_repo->add_condition($prepared_data);
            
            if (!$insert_id) {
                wp_send_json_error(['message' => esc_html__('Failed to create new router condition.', 'free-mail-smtp')]);
                return;
            }
            
            wp_send_json_success([
                'message' => esc_html__('New router condition created successfully!', 'free-mail-smtp'),
                'id' => $insert_id,
                'operation' => 'insert'
            ]);
        }
    }

    public function update_condition_status() {
        check_ajax_referer('free_mail_smtp_email_router', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }

        $condition_id = isset($_POST['condition_id']) ? absint($_POST['condition_id']) : 0;
        $status = isset($_POST['status']) ? absint($_POST['status']) : 0;

        if (!$condition_id) {
            wp_send_json_error(['message' => esc_html__('Invalid condition ID', 'free-mail-smtp')]);
            return;
        }

        $update_data = ['is_enabled' => $status];
        $condition_repo = new \TurboSMTP\FreeMailSMTP\DB\ConditionRepository();
        $updated = $condition_repo->update_condition($condition_id, $update_data);

        if (!$updated) {
            wp_send_json_error(['message' => esc_html__('Failed to update status.', 'free-mail-smtp')]);
        } else {
            wp_send_json_success(['message' => esc_html__('Status updated successfully', 'free-mail-smtp')]);
        }
    }

    public function get_email_router_condition() {
        check_ajax_referer('free_mail_smtp_email_router', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }
        $condition_id = isset($_POST['condition_id']) ? absint($_POST['condition_id']) : 0;
        if (!$condition_id) {
            wp_send_json_error(['message' => esc_html__('Invalid condition ID', 'free-mail-smtp')]);
            return;
        }
        $condition_repo = new \TurboSMTP\FreeMailSMTP\DB\ConditionRepository();
        $condition = $condition_repo->get_condition($condition_id);
        if (!$condition) {
            wp_send_json_error(['message' => esc_html__('Condition not found', 'free-mail-smtp')]);
            return;
        }
        $condition->condition_data = json_decode($condition->condition_data, true);
        wp_send_json_success($condition);
    }

    public function delete_email_router_condition() {
        check_ajax_referer('free_mail_smtp_email_router', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }

        $condition_id = isset($_POST['condition_id']) ? absint($_POST['condition_id']) : 0;
        if (!$condition_id) {
            wp_send_json_error(['message' => esc_html__('Invalid condition ID', 'free-mail-smtp')]);
            return;
        }

        $condition_repo = new \TurboSMTP\FreeMailSMTP\DB\ConditionRepository();
        $deleted = $condition_repo->delete_condition($condition_id);

        if (!$deleted) {
            wp_send_json_error(['message' => esc_html__('Failed to delete condition.', 'free-mail-smtp')]);
        } else {
            wp_send_json_success(['message' => esc_html__('Condition deleted successfully', 'free-mail-smtp')]);
        }
    }
}