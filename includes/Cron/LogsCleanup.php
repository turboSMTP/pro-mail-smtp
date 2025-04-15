<?php

namespace TurboSMTP\FreeMailSMTP\Cron;
if ( ! defined( 'ABSPATH' ) ) exit;

class LogsCleanup implements CronInterface
{
    private $hook = 'free_mail_smtp_cleanup_logs';
    private $interval = 'daily';

    public function __construct()
    {
        add_action($this->hook, [$this, 'handle']);
    }

    public function register()
    {
        if (!$this->is_scheduled()) {
            wp_clear_scheduled_hook($this->hook);
            wp_schedule_event(time(), $this->interval, $this->hook);
        }
    }

    public function deregister()
    {
        $timestamp = wp_next_scheduled($this->hook);
        if ($timestamp) {
            wp_unschedule_event($timestamp, $this->hook);
        }
    }

    public function is_scheduled()
    {
        return (bool)wp_next_scheduled($this->hook);
    }

    public function get_interval()
    {
        return $this->interval;
    }

    public function get_hook()
    {
        return $this->hook;
    }

    public function handle()
    {
        $current_retention = get_option('free_mail_smtp_retention_duration', 'forever');

        if ($current_retention === 'forever') {
            return;
        }

        $this->auto_delete_logs($current_retention);
    }

    private function auto_delete_logs($retention)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'free_mail_smtp_email_log';
        $cutoff = '';

        switch ($retention) {
            case '1_week':
                $cutoff = gmdate('Y-m-d H:i:s', strtotime('-1 week'));
                break;
            case '1_month':
                $cutoff = gmdate('Y-m-d H:i:s', strtotime('-1 month'));
                break;
            case '1_year':
                $cutoff = gmdate('Y-m-d H:i:s', strtotime('-1 year'));
                break;
            default:
                return;
        }

        $table_name_esc = esc_sql($table_name);
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query($wpdb->prepare("DELETE FROM {$table_name_esc} WHERE sent_at < %s", $cutoff));
    }
}
