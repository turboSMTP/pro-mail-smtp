<?php

namespace FreeMailSMTP\Core;

use FreeMailSMTP\DB\ConnectionRepository;

class ProviderManager
{

    public function save_provider($data)
    {
        $provider = sanitize_text_field($data['provider']);

        global $wpdb;
        if ($provider === 'gmail' && !$data['connection_id']) {
            $count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}free_mail_smtp_connections WHERE provider = %s",
                'gmail'
            ));
            if ($count > 0) {
                wp_send_json_error('Only one Gmail provider can be added.');
                return;
            }
        }
        if ($provider === 'outlook' && !$data['connection_id'] ) {
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
        if (isset($data['config_keys']) && is_array($data['config_keys'])) {
            foreach ($data['config_keys'] as $key => $value) {
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

        $priority = isset($data['priority']) ? intval($data['priority']) : 1;
        $connection_label = (isset($data['connection_label']) && !empty($data['connection_label']))
            ? sanitize_text_field($data['connection_label'])
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
        if (isset($data['connection_id']) && !empty($data['connection_id'])) {
            $connection_id = sanitize_text_field($data['connection_id']);
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
        return $connection_id;
    }

    public function get_available_priority()
    {
        $conn_repo = new \FreeMailSMTP\DB\ConnectionRepository();
        return $conn_repo->get_available_priority();
    }
}
