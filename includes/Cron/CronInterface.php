<?php

namespace FreeMailSMTP\Cron;

/**
 * Interface for all cron jobs in the Free Mail SMTP plugin.
 *
 * @since 1.0.0
 */
interface CronInterface {
    public function register();
    public function deregister();
    public function is_scheduled();
    public function get_interval();
    public function get_hook();
    public function handle();
}
