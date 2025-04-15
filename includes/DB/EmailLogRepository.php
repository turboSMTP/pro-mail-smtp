<?php

namespace TurboSMTP\FreeMailSMTP\DB;
if ( ! defined( 'ABSPATH' ) ) exit;

class EmailLogRepository {
    public function get_logs($filters) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'free_mail_smtp_email_log';
        
        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM " . esc_sql($table_name);
        $where = [];
        
        if (!empty($filters['provider'])) {
            $where[] = 'provider = "' . esc_sql($filters['provider']) . '"';
        }

        if (!empty($filters['status'])) {
            $where[] = 'status = "' . esc_sql($filters['status']) . '"';
        }

        if (!empty($filters['search'])) {
            $search_term = '%' . $wpdb->esc_like($filters['search']) . '%';
            $where[] = '(to_email LIKE "' . esc_sql($search_term) . '" OR subject LIKE "' . esc_sql($search_term) . '")';
        }

        if (!empty($filters['date_from'])) {
            $where[] = 'sent_at >= "' . esc_sql($filters['date_from'] . ' 00:00:00') . '"';
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'sent_at <= "' . esc_sql($filters['date_to'] . ' 23:59:59') . '"';
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        
        $orderby = $this->validate_orderby($filters['orderby']);
        $order = isset($filters['order']) && strtolower($filters['order']) === 'asc' ? 'ASC' : 'DESC';
        $sql .= " ORDER BY " . esc_sql($orderby) . " " . esc_sql($order); 
        
        $per_page = 20;
        $offset = ($filters['paged'] - 1) * $per_page;
        $sql .= " LIMIT " . intval($per_page) . " OFFSET " . intval($offset);
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
        return $wpdb->get_results($sql);
    }
    
    public function get_total_logs() {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        return $wpdb->get_var('SELECT FOUND_ROWS()');
    }
    
    private function validate_orderby($orderby) {
        $allowed = ['sent_at', 'provider', 'to_email', 'subject', 'status'];
        return in_array($orderby, $allowed) ? $orderby : 'sent_at';
    }
}