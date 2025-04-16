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

// Setup array to collect error messages
$uninstall_errors = [];

// Delete database tables
global $wpdb;

// Drop email logs table
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange
if ($wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}free_mail_smtp_email_log") === false) {
    $uninstall_errors[] = 'Failed to remove email log table.';
}

// Drop email router conditions table
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange
if ($wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}free_mail_smtp_email_router_conditions") === false) {
    $uninstall_errors[] = 'Failed to remove email router conditions table.';
}

// Drop connections table
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange
if ($wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}free_mail_smtp_connections") === false) {
    $uninstall_errors[] = 'Failed to remove connections table.';
}

// If we have errors and we're in the admin area, show them to the user
if (!empty($uninstall_errors) && is_admin() && function_exists('add_action')) {
    // This displays the message on the next page load after uninstall
    add_action('admin_notices', function() use ($uninstall_errors) {
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p><strong>Free Mail SMTP uninstall encountered issues:</strong></p>';
        echo '<ul>';
        foreach ($uninstall_errors as $error) {
            echo '<li>' . esc_html($error) . '</li>';
        }
        echo '</ul>';
        echo '</div>';
    });
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
    'free_mail_smtp_import_easysmtp_notice_dismissed',
    'free_mail_smtp_import_wpmail_notice_dismissed',
    'free_mail_smtp_retention_duration'
];

foreach ($options as $option) {
    delete_option($option);
}

// Clear any scheduled cron events
require_once FREE_MAIL_SMTP_FILE; 
require_once dirname(FREE_MAIL_SMTP_FILE) . '/includes/Cron/CronManager.php';
\TurboSMTP\FreeMailSMTP\Cron\CronManager::get_instance()->deactivate_crons();