<?php
namespace FreeMailSMTP\DB;

defined('ABSPATH') || exit;

class ConnectionRepository {

    private $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'free_mail_smtp_connections';
    }
    
    public function insert_connection($connection_id, $provider, $connection_data, $priority = 0, $connection_label = '') {
        global $wpdb;
        
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $this->table");
        if ($count >= 5) {
            return new \WP_Error('max_entries', 'Maximum number of connections reached.');
        }
        
        $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$this->table} WHERE priority = %d", $priority));
        if ($exists > 0) {
            return new \WP_Error('duplicate_priority', 'The priority value must be unique.');
        }
        
        $result = $wpdb->insert(
            $this->table,
            [
                'connection_id'      => $connection_id,
                'provider'           => $provider,
                'connection_label'   => $connection_label,
                'priority'           => $priority,
                'connection_data'    => json_encode($connection_data),
            ],
            [
                '%s',
                '%s',
                '%s',
                '%d',
                '%s'
            ]
        );
        return $result ? $wpdb->insert_id : false;
    }
    
    public function update_connection($connection_id, $connection_data, $connection_label = null, $priority = null) {
        global $wpdb;
        
        $current = $this->get_connection($connection_id);
        if (!$current) {
            return new \WP_Error('not_found', 'Connection not found.');
        }
        
        $new_priority = ($priority !== null) ? intval($priority) : intval($current->priority);
        
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE priority = %d AND connection_id != %s",
            $new_priority,
            $connection_id
        ));
        if ($exists > 0) {
            return new \WP_Error('duplicate_priority', 'The priority value must be unique.');
        }
        
        $update_data = [
            'connection_data' => json_encode($connection_data)
        ];
        $format = ['%s'];
        if (!is_null($connection_label)) {
            $update_data['connection_label'] = $connection_label;
            $format[] = '%s';
        }
        if ($priority !== null) {
            $update_data['priority'] = $new_priority;
            $format[] = '%d';
        }
        return $wpdb->update(
            $this->table,
            $update_data,
            ['connection_id' => $connection_id],
            $format,
            ['%s']
        );
    }
    
    public function get_connection($connection_id) {
        global $wpdb;
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table} WHERE connection_id = %s", $connection_id)
        );
        if ($row) {
            $row->connection_data = json_decode($row->connection_data, true);
        }
        return $row;
    }
    
    public function delete_connection($connection_id) {
        global $wpdb;
        return $wpdb->delete($this->table, ['connection_id' => $connection_id], ['%s']);
    }
    
    public function get_all_connections() {
        global $wpdb;
        $results = $wpdb->get_results("SELECT * FROM {$this->table} ORDER BY priority ASC");
        if ($results) {
            foreach ($results as &$row) {
                $row->connection_data = json_decode($row->connection_data, true);
            }
        }
        return $results;
    }

    public function get_available_priority() {
        global $wpdb;
        $table_name_esc = esc_sql($this->table);
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT priority FROM {$table_name_esc} ORDER BY priority ASC"
            )
        );
        $priorities = [];
        if ($results) {
            foreach ($results as $row) {
                $priorities[] = $row->priority;
            }
        }
        $available = [];
        for ($i = 1; $i < 10; $i++) {
            if (!in_array($i, $priorities)) {
                $available[] = $i;
            }
        }
        return $available;
    }

    public function provider_exists($provider) {
        global $wpdb;
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE provider = %s",
            $provider
        ));
        return $count > 0;
    }
}
