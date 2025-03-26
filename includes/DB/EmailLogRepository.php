<?php

namespace FreeMailSMTP\DB;

class EmailLogRepository {
    public function get_logs($filters) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'email_log';
        
        $where = [];
        $values = [];
        
        if (!empty($filters['provider'])) {
            $where[] = 'provider = %s';
            $values[] = $filters['provider'];
        }

        if (!empty($filters['status'])) {
            $where[] = 'status = %s';
            $values[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(to_email LIKE %s OR subject LIKE %s)';
            $search_term = '%' . $wpdb->esc_like($filters['search']) . '%';
            $values[] = $search_term;
            $values[] = $search_term;
        }

        if (!empty($filters['date_from'])) {
            $where[] = 'sent_at >= %s';
            $values[] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'sent_at <= %s';
            $values[] = $filters['date_to'] . ' 23:59:59';
        }

        $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $orderby = $this->validate_orderby($filters['orderby']);
        $order = isset($filters['order']) && strtolower($filters['order']) === 'asc' ? 'ASC' : 'DESC';
        
        $per_page = 20;
        $offset = ($filters['paged'] - 1) * $per_page;
        
        $table_name_esc = esc_sql($table_name);
        $orderby_esc = esc_sql($orderby);
        $order_esc = esc_sql($order);
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT SQL_CALC_FOUND_ROWS * 
                FROM {$table_name_esc} 
                {$where_clause} 
                ORDER BY {$orderby_esc} {$order_esc} 
                LIMIT %d OFFSET %d",
                array_merge($values, [$per_page, $offset])
            )
        );
    }
    
    public function get_total_logs() {
        global $wpdb;
        return $wpdb->get_var('SELECT FOUND_ROWS()');
    }
    
    private function validate_orderby($orderby) {
        $allowed = ['sent_at', 'provider', 'toesc_html_email', 'subject', 'status'];
        return in_array($orderby, $allowed) ? $orderby : 'sent_at';
    }
}