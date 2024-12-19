<?php defined('ABSPATH') || exit; ?>

<div class="wrap">
    <h1><?php _e('Email Analytics', 'email-api'); ?></h1>

    <!-- Filters Section -->
    <?php 
    $filters_file = dirname(__FILE__) . '/partials/filters.php';
    if (file_exists($filters_file)) {
        include $filters_file;
    }
    ?>

    <!-- Analytics Table -->
    <?php 
    $table_file = dirname(__FILE__) . '/partials/table.php';
    if (file_exists($table_file)) {
        include $table_file;
    }
    ?>

    <!-- Pagination -->
    <?php 
    // $pagination_file = dirname(__FILE__) . '/partials/pagination.php';
    // if (file_exists($pagination_file)) {
    //     include $pagination_file;
    // }
    ?>
</div>

<!-- Loading Overlay -->
<div id="loading-overlay" style="display: none;">
    <div class="loading-spinner"></div>
</div>