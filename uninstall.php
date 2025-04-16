<?php
/**
 * Uninstall script for Free Mail SMTP
 *
 * This file runs when the plugin is deleted from the WordPress admin.
 */

// Exit if accessed directly
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// --- Database Table Deletion ---
global $wpdb;
$tables_to_drop = [
    $wpdb->prefix . 'free_mail_smtp_email_log',
    $wpdb->prefix . 'free_mail_smtp_email_router_conditions',
    $wpdb->prefix . 'free_mail_smtp_connections',
];

foreach ($tables_to_drop as $table_name) {
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
}

$options_to_delete = [
    'free_mail_smtp_db_version',
    'free_mail_smtp_from_email',
    'free_mail_smtp_from_name',
    'free_mail_smtp_enable_summary',
    'free_mail_smtp_summary_email',
    'free_mail_smtp_summary_frequency',
    'free_mail_smtp_fallback_to_wp_mail',
    'free_mail_smtp_gmail_access_token',
    'free_mail_smtp_gmail_refresh_token',
    'free_mail_smtp_outlook_refresh_token',
    'free_mail_smtp_outlook_access_token',
    'free_mail_smtp_import_easysmtp_notice_dismissed',
    'free_mail_smtp_import_wpmail_notice_dismissed',
    'free_mail_smtp_retention_duration'
];

foreach ($options_to_delete as $option_name) {
    delete_option($option_name);
}

$cron_hooks = [
    'free_mail_smtp_summary_cron',
    'free_mail_smtp_log_cleanup_cron',
    
];

foreach ($cron_hooks as $hook) {
    wp_clear_scheduled_hook($hook);
}
