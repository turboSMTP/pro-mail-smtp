<?php defined('ABSPATH') || exit; ?>

<div class="wrap">
    <div class="plugin-header">
    <span class="plugin-logo"></span>
    <h1><?php esc_html_e('FREE MAIL', 'free-mail-smtp'); ?> <span><?php esc_html_e('SMTP', 'free-mail-smtp'); ?></span></h1>
    </div>

    <p class="description"><?php esc_html_e('Configure general settings for Free Mail SMTP.', 'free-mail-smtp'); ?></p>

    <nav class="free-mail-smtp-nav-tab-wrapper">
        <a href="<?php echo esc_url(admin_url('admin.php?page=free-mail-smtp-providers')); ?>" class="free-mail-smtp-nav-tab"><?php esc_html_e('Providers', 'free-mail-smtp'); ?></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=free-mail-smtp-logs')); ?>" class="free-mail-smtp-nav-tab"><?php esc_html_e('Email Logs', 'free-mail-smtp'); ?></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=free-mail-smtp-analytics')); ?>" class="free-mail-smtp-nav-tab"><?php esc_html_e('Providers Logs', 'free-mail-smtp'); ?></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=free-mail-smtp-email-router')); ?>" class="free-mail-smtp-nav-tab"><?php esc_html_e('Email Router', 'free-mail-smtp'); ?></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=free-mail-smtp-settings')); ?>" class="free-mail-smtp-nav-tab free-mail-smtp-nav-tab-active"><?php esc_html_e('Settings', 'free-mail-smtp'); ?></a>
    </nav>

    <?php settings_errors('free_mail_smtp_messages'); ?>

    <form method="post" action="">
        <?php 
        wp_nonce_field('free-mail-smtp-settings', 'free_mail_smtp_nonce_settings'); 
        ?>
        <input type="hidden" name="action" value="save_settings">
        
        <div class="settings-section">
            <h2><?php esc_html_e('Force Sender Settings', 'free-mail-smtp'); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="from_email"><?php esc_html_e('From Email', 'free-mail-smtp'); ?></label></th>
                    <td><input name="from_email" type="email" id="from_email" value="<?php echo esc_attr($from_email); ?>" class="regular-text" required></td>
                </tr>
                <tr>
                    <th scope="row"><label for="from_name"><?php esc_html_e('From Name', 'free-mail-smtp'); ?></label></th>
                    <td><input name="from_name" type="text" id="from_name" value="<?php echo esc_attr($from_name); ?>" class="regular-text"></td>
                </tr>
            </table>
        </div>

        <div class="settings-section">
            <h2><?php esc_html_e('Email Summary Settings', 'free-mail-smtp'); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="enable_email_summary"><?php esc_html_e('Enable Email Summary', 'free-mail-smtp'); ?></label></th>
                    <td>
                        <input type="checkbox" name="enable_email_summary" id="enable_email_summary" value="1" <?php checked($enable_summary, 1); ?>>
                        <p class="description"><?php esc_html_e('Enable periodic email summary reports', 'free-mail-smtp'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="summary_email"><?php esc_html_e('Summary Recipient Email', 'free-mail-smtp'); ?></label></th>
                    <td>
                        <input type="email" name="summary_email" id="summary_email" value="<?php echo esc_attr($summary_email); ?>" class="regular-text">
                        <p class="description"><?php esc_html_e('Email address to receive summary reports', 'free-mail-smtp'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="summary_frequency"><?php esc_html_e('Summary Frequency', 'free-mail-smtp'); ?></label></th>
                    <td>
                        <select name="summary_frequency" id="summary_frequency">
                            <option value="weekly" <?php selected($summary_frequency, 'weekly'); ?>><?php esc_html_e('Weekly', 'free-mail-smtp'); ?></option>
                            <option value="monthly" <?php selected($summary_frequency, 'monthly'); ?>><?php esc_html_e('Monthly', 'free-mail-smtp'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('How often to send summary reports', 'free-mail-smtp'); ?></p>
                        <div class="notice notice-warning inline">
                            <p>
                                <strong><?php esc_html_e('Caution:', 'free-mail-smtp'); ?></strong>
                                <?php esc_html_e('Make sure your summary period is shorter than your logs retention period to ensure accurate reporting.', 'free-mail-smtp'); ?>
                            </p>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="settings-section">
            <h2><?php esc_html_e('Fallback Mail Settings', 'free-mail-smtp'); ?></h2>
            <p class="description"><?php esc_html_e('Configure fallback settings for when all providers fail to send email', 'free-mail-smtp'); ?></p>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="enable_fallback"><?php esc_html_e('Enable PHP Mail Fallback', 'free-mail-smtp'); ?></label></th>
                    <td>
                        <input type="checkbox" name="enable_fallback" id="enable_fallback" value="1" <?php checked($enable_fallback, 1); ?>>
                        <p class="description"><?php esc_html_e('Use PHP mail() function as fallback when all providers fail to send email', 'free-mail-smtp'); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <p class="submit">
        <input type="submit" name="save_settings" class="save-settings" value="<?php esc_attr_e('Save Changes', 'free-mail-smtp'); ?>">
        </p>
    </form>
    <div class="settings-section danger-zone">
            <h2><?php esc_html_e('Data Management', 'free-mail-smtp'); ?></h2>
            <div class="notice notice-error inline">
                <p>
                    <strong><?php esc_html_e('Danger Zone:', 'free-mail-smtp'); ?></strong>
                    <?php esc_html_e('Actions in this section cannot be undone. Please proceed with caution.', 'free-mail-smtp'); ?>
                </p>
            </div>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e('Delete All Data', 'free-mail-smtp'); ?></th>
                    <td>
                        <button type="button" id="free-mail-smtp-delete-data" class="button button-danger">
                            <?php esc_html_e('Delete All Plugin Data', 'free-mail-smtp'); ?>
                        </button>
                        <p class="description">
                            <?php esc_html_e('This will delete all connections, conditions, logs, and plugin settings. This action cannot be undone.', 'free-mail-smtp'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
</div>

<!-- Data Deletion Confirmation Modal -->
<div id="data-deletion-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><?php esc_html_e('Confirm Data Deletion', 'free-mail-smtp'); ?></h3>
            <span class="modal-close">&times;</span>
        </div>
        <div class="modal-body">
            <p><?php esc_html_e('This will permanently delete all your plugin data including:', 'free-mail-smtp'); ?></p>
            <ul>
                <li><?php esc_html_e('All email provider connections', 'free-mail-smtp'); ?></li>
                <li><?php esc_html_e('All routing conditions', 'free-mail-smtp'); ?></li>
                <li><?php esc_html_e('All email logs', 'free-mail-smtp'); ?></li>
                <li><?php esc_html_e('All plugin settings', 'free-mail-smtp'); ?></li>
            </ul>
            <p><strong><?php esc_html_e('This action cannot be undone.', 'free-mail-smtp'); ?></strong></p>
            <p><?php esc_html_e('Please type "DELETE" to confirm:', 'free-mail-smtp'); ?></p>
            <input type="text" class="confirmation-input" id="delete-confirmation" placeholder="<?php esc_attr_e('DELETE', 'free-mail-smtp'); ?>">
        </div>
        <div class="modal-actions">
            <button type="button" class="button modal-cancel"><?php esc_html_e('Cancel', 'free-mail-smtp'); ?></button>
            <button type="button" class="button button-danger" id="confirm-delete-data" disabled><?php esc_html_e('Permanently Delete All Data', 'free-mail-smtp'); ?></button>
        </div>
    </div>
</div>
