<?php

namespace TurboSMTP\FreeMailSMTP\DB;
if ( ! defined( 'ABSPATH' ) ) exit;

class EmailLogRepository {
    public function get_logs($filters) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'free_mail_smtp_email_log';

        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM {$table_name}";
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
            $search_term = '%' . $wpdb->esc_like($filters['search']) . '%';
            $where[] = '(to_email LIKE %s OR subject LIKE %s)';
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

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $orderby = $this->validate_orderby($filters['orderby']);
        $order = isset($filters['order']) && strtolower($filters['order']) === 'asc' ? 'ASC' : 'DESC';
        $sql .= " ORDER BY " . $orderby . " " . $order;

        $per_page = 20;
        $offset = ($filters['paged'] - 1) * $per_page;
        $sql .= " LIMIT %d OFFSET %d";
        $values[] = $per_page;
        $values[] = $offset;

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql is built dynamically but uses placeholders
        $prepared_sql = $wpdb->prepare($sql, $values);

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
        return $wpdb->get_results($prepared_sql);
    }

    public function get_total_logs() {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        return $wpdb->get_var('SELECT FOUND_ROWS()');
    }

    private function validate_orderby($orderby) {
        $allowed = ['sent_at', 'provider', 'to_email', 'subject', 'status'];
        return in_array($orderby, $allowed, true) ? $orderby : 'sent_at';
    }
}
