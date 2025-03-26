<?php
/**
 * Plugin Name: Free Mail SMTP
 * Description: Enhance email deliverability by connecting WordPress to SMTP providers with automatic failover, logging, and advanced routing.
 * Version: 1.0.0
 * Author: turbosmtp
 * textdomain: free-mail-smtp
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('WP_DEBUG')) {
    define('WP_DEBUG', true);
}
if (!defined('WP_DEBUG_LOG')) {
    define('WP_DEBUG_LOG', true);
}

define('free_mail_smtp_VERSION', '1.0.0');
define('free_mail_smtp_FILE', __FILE__);
define('free_mail_smtp_PATH', plugin_dir_path(__FILE__));
define('free_mail_smtp_URL', plugin_dir_url(__FILE__));

if (file_exists(free_mail_smtp_PATH . 'includes/Lib/google/vendor/autoload.php')) {
    require_once free_mail_smtp_PATH . 'includes/Lib/google/vendor/autoload.php';
}
// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'FreeMailSMTP\\';
    $base_dir = plugin_dir_path(__FILE__) . 'includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Initialize plugin
function free_mail_smtp_init() {
    $plugin = new FreeMailSMTP\Core\Plugin();
    $plugin->init();
}
add_action('plugins_loaded', 'free_mail_smtp_init');

// Activation hook
register_activation_hook(__FILE__, function() {
    $installer = new FreeMailSMTP\Core\Installer();
    $installer->install();
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    \FreeMailSMTP\Cron\CronManager::get_instance()->deactivate_crons();
});