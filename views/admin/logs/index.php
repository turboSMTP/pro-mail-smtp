<?php
/**
 * Email Logs Main View
 *
 * @var array $data Data passed from the controller
 */

if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables unpacked from controller $data array
$current_retention = $data['current_retention'];
$filters = $data['filters'];
$logs = $data['logs'];
$total_items = $data['total_items'];
$total_pages = $data['total_pages'];
$columns = $data['columns'];
$providers = $data['providers'];
$statuses = $data['statuses'];
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
?>

<div class="wrap pro_mail_smtp-wrap">
    <?php include __DIR__ . '/partials/header.php'; ?>
    
    <?php include __DIR__ . '/partials/retention-settings.php'; ?>
    
    <?php include __DIR__ . '/partials/filters.php'; ?>
    
    <?php include __DIR__ . '/partials/logs-table.php'; ?>
    
    <?php include __DIR__ . '/partials/pagination.php'; ?>
</div>
