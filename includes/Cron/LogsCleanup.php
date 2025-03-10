<?php

namespace FreeMailSMTP\Cron;

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
        $table_name = $wpdb->prefix . 'email_log';
        $cutoff = '';

        switch ($retention) {
            case '1_week':
                $cutoff = date('Y-m-d H:i:s', strtotime('-1 week'));
                break;
            case '1_month':
                $cutoff = date('Y-m-d H:i:s', strtotime('-1 month'));
                break;
            case '1_year':
                $cutoff = date('Y-m-d H:i:s', strtotime('-1 year'));
                break;
            default:
                return;
        }

        $rows_affected = $wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE sent_at < %s", $cutoff));
        error_log("Free Mail SMTP: Deleted $rows_affected log entries older than $cutoff");
    }
}
