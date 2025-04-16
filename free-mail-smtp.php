<?php
/**
 * Plugin Name: Free Mail SMTP
 * Plugin URI: https://www.freemailsmtp.com
 * Description: Enhance email deliverability by connecting WordPress to SMTP providers with automatic failover, logging, and advanced routing.
 * Version: 1.0.0
 * Author: turbosmtp
 * Text Domain: free-mail-smtp
 * Domain Path: /assets/languages
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Requires at least: 5.0
 * Requires PHP: 7.2
 */

if (!defined('ABSPATH')) {
    exit;
}

define('FREE_MAIL_SMTP_VERSION', '1.0.0');
define('FREE_MAIL_SMTP_FILE', __FILE__);
define('FREE_MAIL_SMTP_PATH', plugin_dir_path(__FILE__));
define('FREE_MAIL_SMTP_URL', plugin_dir_url(__FILE__));

function free_mail_smtp_load_textdomain() {
    load_plugin_textdomain(
        'free-mail-smtp',
        false,
        dirname(plugin_basename(FREE_MAIL_SMTP_FILE)) . '/languages/'
    );
}
add_action('init', 'free_mail_smtp_load_textdomain');

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'TurboSMTP\FreeMailSMTP\\';
    $base_dir = FREE_MAIL_SMTP_PATH . 'includes/';

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

/**
 * Initialize the main plugin components.
 *
 * @since 1.0.0
 */
function free_mail_smtp_init() {
    if (class_exists('TurboSMTP\FreeMailSMTP\Core\Plugin')) {
        $plugin = new TurboSMTP\FreeMailSMTP\Core\Plugin();
        $plugin->init();
    } else {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>';
            echo esc_html__('Free Mail SMTP Error: Core plugin class not found. Please ensure the plugin files are intact.', 'free-mail-smtp');
            echo '</p></div>';
        });
    }
}
add_action('init', 'free_mail_smtp_init', 11);


register_activation_hook(__FILE__, function() {
    if (!class_exists('TurboSMTP\FreeMailSMTP\Core\Installer')) {
         $installer_file = FREE_MAIL_SMTP_PATH . 'includes/Core/Installer.php';
         if (file_exists($installer_file)) {
             require_once $installer_file;
         }
    }
    if (class_exists('TurboSMTP\FreeMailSMTP\Core\Installer')) {
        $installer = new TurboSMTP\FreeMailSMTP\Core\Installer();
        $installer->install();
    }
});

register_deactivation_hook(__FILE__, function() {
    if (!class_exists('TurboSMTP\FreeMailSMTP\Cron\CronManager')) {
         $cron_manager_file = FREE_MAIL_SMTP_PATH . 'includes/Cron/CronManager.php';
         if (file_exists($cron_manager_file)) {
             require_once $cron_manager_file;
         }
    }
    if (class_exists('TurboSMTP\FreeMailSMTP\Cron\CronManager') && method_exists('TurboSMTP\FreeMailSMTP\Cron\CronManager', 'get_instance')) {
        \TurboSMTP\FreeMailSMTP\Cron\CronManager::get_instance()->deactivate_crons();
    }
});

