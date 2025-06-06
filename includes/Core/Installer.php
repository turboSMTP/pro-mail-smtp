<?php
namespace TurboSMTP\ProMailSMTP\Core;
if ( ! defined( 'ABSPATH' ) ) exit;

class Installer {
    private $db_version = '1.0';
    public function install() {
        $installed_version = get_option('pro_mail_smtp_db_version', '0');
        $this->create_default_options();

        if ($installed_version === '0') {
            $this->update_db_1_0();
        }
        // Future updates
        // if (version_compare($installed_version, '1.1', '<')) {
        // }
        
        update_option('pro_mail_smtp_db_version', $this->db_version);

    }
    
    private function create_default_options() {
        add_option('pro_mail_smtp_from_email', get_option('admin_email'));
        add_option('pro_mail_smtp_from_name', get_option('blogname'));
        add_option('pro_mail_smtp_fallback_to_wp_mail', true);
        add_option('pro_mail_smtp_import_easysmtp_notice_dismissed', false);
        add_option('pro_mail_smtp_import_wpmail_notice_dismissed', false);
    }

    private function update_db_1_0(){
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'pro_mail_smtp_email_log';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            provider varchar(50) NOT NULL,
            to_email varchar(100) NOT NULL,
            subject varchar(255) NOT NULL,
            status varchar(20) NOT NULL,
            sent_at datetime DEFAULT CURRENT_TIMESTAMP,
            delivered_at datetime NULL,
            opened_at datetime NULL,
            clicked_at datetime NULL,
            error_message text NULL,
            message_id varchar(255) NULL,
            PRIMARY KEY  (id),
            KEY provider (provider),
            KEY status (status),
            KEY sent_at (sent_at)
        ) $charset_collate;";
        
        if (!function_exists('dbDelta')) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        }
        dbDelta($sql);
        
        $connections_table = $wpdb->prefix . 'pro_mail_smtp_connections';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql_connections = "CREATE TABLE $connections_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            connection_id varchar(50) NOT NULL,
            provider varchar(50) NOT NULL,
            connection_label varchar(255) NOT NULL,
            priority int NOT NULL DEFAULT 0,
            connection_data text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY connection_id (connection_id),
            UNIQUE KEY priority (priority)
        ) $charset_collate;";
        
        dbDelta($sql_connections);
        
        $conditions_table = $wpdb->prefix . 'pro_mail_smtp_email_router_conditions';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql_conditions = "CREATE TABLE IF NOT EXISTS $conditions_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            connection_id varchar(50) NOT NULL,
            condition_data json NOT NULL,
            condition_label varchar(255) NOT NULL,
            overwrite_connection boolean NOT NULL DEFAULT 0,
            overwrite_sender boolean NOT NULL DEFAULT 0,
            forced_senderemail varchar(255) NULL,
            forced_sendername varchar(255) NULL,
            is_enabled boolean NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            KEY connection_id (connection_id),
            CONSTRAINT fk_connection_id FOREIGN KEY (connection_id) REFERENCES $connections_table(connection_id) ON DELETE CASCADE
        ) $charset_collate;";
        
        dbDelta($sql_conditions);
        
    }
}