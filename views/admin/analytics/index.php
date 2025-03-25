<?php defined('ABSPATH') || exit; ?>

<div class="wrap">
    <div class="plugin-header">
        <img src="<?php echo esc_url(plugins_url('assets/img/icon-svg.svg', dirname(dirname(dirname(__FILE__))))); ?>" alt="<?php esc_attr_e('Free Mail SMTP', 'free-mail-smtp'); ?>" class="plugin-logo">
        <h1><?php esc_html_e('FREE MAIL', 'free-mail-smtp'); ?> <span><?php esc_html_e('SMTP', 'free-mail-smtp'); ?></span></h1>
    </div>
    
    <p class="description"><?php esc_html_e('Setup custom SMTP or popular Providers to improve your WordPress email deliverability.', 'free-mail-smtp'); ?></p>

    <nav class="free-mail-smtp-nav-tab-wrapper">
        <a href="<?php echo esc_url(admin_url('admin.php?page=free_mail_smtp-providers')); ?>" class="free-mail-smtp-nav-tab"><?php esc_html_e('Providers', 'free-mail-smtp'); ?></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=free_mail_smtp-logs')); ?>" class="free-mail-smtp-nav-tab"><?php esc_html_e('Email Logs', 'free-mail-smtp'); ?></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=free_mail_smtp-analytics')); ?>" class="free-mail-smtp-nav-tab free-mail-smtp-nav-tab-active"><?php esc_html_e('Providers Logs', 'free-mail-smtp'); ?></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=free_mail_smtp-email-router')); ?>" class="free-mail-smtp-nav-tab"><?php esc_html_e('Email Router', 'free-mail-smtp'); ?></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=free_mail_smtp-settings')); ?>" class="free-mail-smtp-nav-tab"><?php esc_html_e('Settings', 'free-mail-smtp'); ?></a>
    </nav>

    <?php settings_errors('free_mail_smtp_messages'); ?>
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

</div>

<div id="loading-overlay" style="display: none;">
    <div class="loading-spinner"></div>
</div>