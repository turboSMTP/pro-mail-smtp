<?php
/**
 * Uninstall script for Pro Mail SMTP
 *
 * This file runs when the plugin is deleted from the WordPress admin.
 */

// Exit if accessed directly
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// --- Database Table Deletion ---
global $wpdb;
$pro_mail_smtp_tables_to_drop = [
    $wpdb->prefix . 'pro_mail_smtp_email_log',
    $wpdb->prefix . 'pro_mail_smtp_email_router_conditions',
    $wpdb->prefix . 'pro_mail_smtp_connections',
];

foreach ($pro_mail_smtp_tables_to_drop as $pro_mail_smtp_table_name) {
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange
    $wpdb->query($wpdb->prepare("DROP TABLE IF EXISTS %i", $pro_mail_smtp_table_name));
}

$pro_mail_smtp_options_to_delete = [
    'pro_mail_smtp_db_version',
    'pro_mail_smtp_from_email',
    'pro_mail_smtp_from_name',
    'pro_mail_smtp_enable_summary',
    'pro_mail_smtp_summary_email',
    'pro_mail_smtp_summary_frequency',
    'pro_mail_smtp_fallback_to_wp_mail',
    'pro_mail_smtp_gmail_access_token',
    'pro_mail_smtp_gmail_refresh_token',
    'pro_mail_smtp_outlook_refresh_token',
    'pro_mail_smtp_outlook_access_token',
    'pro_mail_smtp_import_easysmtp_notice_dismissed',
    'pro_mail_smtp_import_wpmail_notice_dismissed',
    'pro_mail_smtp_retention_duration'
];

foreach ($pro_mail_smtp_options_to_delete as $pro_mail_smtp_option_name) {
    delete_option($pro_mail_smtp_option_name);
}

$pro_mail_smtp_cron_hooks = [
    'pro_mail_smtp_summary_cron',
    'pro_mail_smtp_log_cleanup_cron',
    
];

foreach ($pro_mail_smtp_cron_hooks as $pro_mail_smtp_hook) {
    wp_clear_scheduled_hook($pro_mail_smtp_hook);
}
