<?php defined('ABSPATH') || exit; ?>

<div class="wrap">
    <div class="plugin-header">
        <img src="<?php echo esc_url(plugins_url('assets/img/icon-svg.svg', dirname(dirname(dirname(__FILE__))))); ?>" alt="Free Mail SMTP" class="plugin-logo">
        <h1>FREE MAIL <span>SMTP</span></h1>
    </div>

    <p class="description">Configure general settings for Free Mail SMTP.</p>

    <nav class="free-mail-smtp-nav-tab-wrapper">
        <a href="<?php echo admin_url('admin.php?page=free_mail_smtp-providers'); ?>" class="free-mail-smtp-nav-tab">Providers</a>
        <a href="<?php echo admin_url('admin.php?page=free_mail_smtp-logs'); ?>" class="free-mail-smtp-nav-tab">Email Logs</a>
        <a href="<?php echo admin_url('admin.php?page=free_mail_smtp-analytics'); ?>" class="free-mail-smtp-nav-tab">Providers Logs</a>
        <a href="<?php echo admin_url('admin.php?page=free_mail_smtp-email-router'); ?>" class="free-mail-smtp-nav-tab">Email Router</a>
        <a href="<?php echo admin_url('admin.php?page=free_mail_smtp-settings'); ?>" class="free-mail-smtp-nav-tab free-mail-smtp-nav-tab-active">Settings</a>
    </nav>

    <?php settings_errors('free_mail_smtp_messages'); ?>

    <form method="post" action="">
        <?php wp_nonce_field('free_mail_smtp_settings', 'free_mail_smtp_nonce_settings'); ?>
        <input type="hidden" name="action" value="save_settings">
        
        <div class="settings-section">
            <h2><?php _e('Force Sender Settings', 'free_mail_smtp'); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="from_email"><?php _e('From Email', 'free_mail_smtp'); ?></label></th>
                    <td><input name="from_email" type="email" id="from_email" value="<?php echo esc_attr($from_email); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="from_name"><?php _e('From Name', 'free_mail_smtp'); ?></label></th>
                    <td><input name="from_name" type="text" id="from_name" value="<?php echo esc_attr($from_name); ?>" class="regular-text"></td>
                </tr>
            </table>
        </div>

        <div class="settings-section">
            <h2><?php _e('Email Summary Settings', 'free_mail_smtp'); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="enable_email_summary"><?php _e('Enable Email Summary', 'free_mail_smtp'); ?></label></th>
                    <td>
                        <input type="checkbox" name="enable_email_summary" id="enable_email_summary" value="1" <?php checked($enable_summary, 1); ?>>
                        <p class="description"><?php _e('Enable periodic email summary reports', 'free_mail_smtp'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="summary_email"><?php _e('Summary Recipient Email', 'free_mail_smtp'); ?></label></th>
                    <td>
                        <input type="email" name="summary_email" id="summary_email" value="<?php echo esc_attr($summary_email); ?>" class="regular-text">
                        <p class="description"><?php _e('Email address to receive summary reports', 'free_mail_smtp'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="summary_frequency"><?php _e('Summary Frequency', 'free_mail_smtp'); ?></label></th>
                    <td>
                        <select name="summary_frequency" id="summary_frequency">
                            <option value="weekly" <?php selected($summary_frequency, 'weekly'); ?>><?php _e('Weekly', 'free_mail_smtp'); ?></option>
                            <option value="monthly" <?php selected($summary_frequency, 'monthly'); ?>><?php _e('Monthly', 'free_mail_smtp'); ?></option>
                        </select>
                        <p class="description"><?php _e('How often to send summary reports', 'free_mail_smtp'); ?></p>
                        <div class="notice notice-warning inline">
                            <p>
                                <strong><?php _e('Caution:', 'free_mail_smtp'); ?></strong>
                                <?php _e('Make sure your summary period is shorter than your logs retention period to ensure accurate reporting.', 'free_mail_smtp'); ?>
                            </p>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="settings-section">
            <h2><?php _e('Fallback Mail Settings', 'free_mail_smtp'); ?></h2>
            <p class="description"><?php _e('Configure fallback settings for when all providers fail to send email', 'free_mail_smtp'); ?></p>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="enable_fallback"><?php _e('Enable PHP Mail Fallback', 'free_mail_smtp'); ?></label></th>
                    <td>
                        <input type="checkbox" name="enable_fallback" id="enable_fallback" value="1" <?php checked($enable_fallback, 1); ?>>
                        <p class="description"><?php _e('Use PHP mail() function as fallback when all providers fail to send email', 'free_mail_smtp'); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- New Data Collection Section -->
        <div class="settings-section">
            <h2><?php _e('Data Collection Settings', 'free_mail_smtp'); ?></h2>
            <p class="description"><?php _e('Configure how Free Mail SMTP collects and processes data.', 'free_mail_smtp'); ?></p>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="allow_data_collection"><?php _e('Allow Data Collection', 'free_mail_smtp'); ?></label></th>
                    <td>
                        <input type="checkbox" name="allow_data_collection" id="allow_data_collection" value="1" <?php checked($allow_data_collection, 1); ?>>
                        <p class="description"><?php _e('Allow Free Mail SMTP to collect anonymous usage data to help improve the plugin.', 'free_mail_smtp'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"></th>
                    <td>
                        <div class="notice notice-info inline" style="margin: 10px 0;">
                            <p>
                                <?php _e('Data collected includes:', 'free_mail_smtp'); ?>
                                <ul style="list-style-type: disc; margin-left: 20px;">
                                    <li><?php _e('Plugin settings (not your credentials)', 'free_mail_smtp'); ?></li>
                                    <li><?php _e('Email sending statistics', 'free_mail_smtp'); ?></li>
                                    <li><?php _e('WordPress environment information', 'free_mail_smtp'); ?></li>
                                </ul>
                                <a href="#" target="_blank"><?php _e('View our privacy policy', 'free_mail_smtp'); ?></a>
                            </p>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <p class="submit">
        <input type="submit" name="save_settings"  class="save-settings" value="<?php _e('Save Changes', 'free_mail_smtp'); ?>">
        </p>
    </form>
    <div class="settings-section danger-zone">
            <h2><?php _e('Data Management', 'free_mail_smtp'); ?></h2>
            <div class="notice notice-error inline">
                <p>
                    <strong><?php _e('Danger Zone:', 'free_mail_smtp'); ?></strong>
                    <?php _e('Actions in this section cannot be undone. Please proceed with caution.', 'free_mail_smtp'); ?>
                </p>
            </div>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php _e('Delete All Data', 'free_mail_smtp'); ?></th>
                    <td>
                        <button type="button" id="free-mail-smtp-delete-data" class="button button-danger">
                            <?php _e('Delete All Plugin Data', 'free_mail_smtp'); ?>
                        </button>
                        <p class="description">
                            <?php _e('This will delete all connections, conditions, logs, and plugin settings. This action cannot be undone.', 'free_mail_smtp'); ?>
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
            <h3><?php _e('Confirm Data Deletion', 'free_mail_smtp'); ?></h3>
            <span class="modal-close">&times;</span>
        </div>
        <div class="modal-body">
            <p><?php _e('This will permanently delete all your plugin data including:', 'free_mail_smtp'); ?></p>
            <ul style="list-style: disc; margin-left: 20px;">
                <li><?php _e('All email provider connections', 'free_mail_smtp'); ?></li>
                <li><?php _e('All routing conditions', 'free_mail_smtp'); ?></li>
                <li><?php _e('All email logs', 'free_mail_smtp'); ?></li>
                <li><?php _e('All plugin settings', 'free_mail_smtp'); ?></li>
            </ul>
            <p><strong><?php _e('This action cannot be undone.', 'free_mail_smtp'); ?></strong></p>
            <p><?php _e('Please type "DELETE" to confirm:', 'free_mail_smtp'); ?></p>
            <input type="text" class="confirmation-input" id="delete-confirmation" placeholder="DELETE">
        </div>
        <div class="modal-actions">
            <button type="button" class="button modal-cancel"><?php _e('Cancel', 'free_mail_smtp'); ?></button>
            <button type="button" class="button button-danger" id="confirm-delete-data" disabled><?php _e('Permanently Delete All Data', 'free_mail_smtp'); ?></button>
        </div>
    </div>
</div>

<style>
    .settings-section {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .settings-section h2 {
        margin-top: 0;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    
    .disabled-field {
        opacity: 0.5;
        pointer-events: none;
    }
</style>

<script>
    jQuery(document).ready(function($) {
        const summaryEmail = $('#summary_email');
        const summaryFrequency = $('#summary_frequency');
        const enableSummary = $('#enable_email_summary');
        
        function toggleSummaryFields() {
            const isEnabled = enableSummary.is(':checked');
            summaryEmail.prop('disabled', !isEnabled);
            summaryFrequency.prop('disabled', !isEnabled);
                        summaryEmail.closest('tr').toggleClass('disabled-field', !isEnabled);
            summaryFrequency.closest('tr').toggleClass('disabled-field', !isEnabled);
        }

        toggleSummaryFields();

        enableSummary.on('change', toggleSummaryFields);
    });
</script>
