<?php
namespace FreeMailSMTP\Core;

class Installer {
    private $db_version = '1.0';
    
    public function install() {
        global $wpdb;
        
        // Create email log table
        $table_name = $wpdb->prefix . 'email_log';
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
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Update version
        update_option('free_mail_smtp_db_version', $this->db_version);
        
        // Create default options
        $this->create_default_options();
    }
    
    private function create_default_options() {
        add_option('free_mail_smtp_from_email', get_option('admin_email'));
        add_option('free_mail_smtp_from_name', get_option('blogname'));
    }
}
