<?php
require_once(dirname(__FILE__) . '/wp-load.php');

$provider = isset($_GET['state']) ? sanitize_text_field($_GET['state']) : '';
$code = isset($_GET['code']) ? sanitize_text_field($_GET['code']) : '';

wp_redirect(admin_url('admin.php?page=free_mail_smtp-settings&provider=' . $provider . '&code=' . $code));
exit;