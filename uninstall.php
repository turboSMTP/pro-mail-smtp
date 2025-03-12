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

// Delete database tables
global $wpdb;

// Drop email logs table
if ($wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}email_log") === false) {
    error_log('Free Mail SMTP: Failed to remove email_log table during uninstall');
}

// Drop connections table
if ($wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}free_mail_smtp_connections") === false) {
    error_log('Free Mail SMTP: Failed to remove free_mail_smtp_connections table during uninstall');
}
// Drop email router conditions table
if ($wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}free_mail_smtp_email_router_conditions") === false) {
    error_log('Free Mail SMTP: Failed to remove free_mail_smtp_email_router_conditions table during uninstall');
}
// Delete all plugin options
$options = [
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
    'free_mail_smtp_analytics_consent',
    'free_mail_smtp_import_easysmtp_notice_dismissed',
    'free_mail_smtp_import_wpmail_notice_dismissed',
    'free_mail_smtp_retention_duration'
];

foreach ($options as $option) {
    delete_option($option);
}

// Clear any scheduled cron events
require_once plugin_dir_path(__FILE__) . 'includes/Cron/CronManager.php';
\FreeMailSMTP\Cron\CronManager::get_instance()->deactivate_crons();