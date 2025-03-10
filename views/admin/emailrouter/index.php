<?php defined('ABSPATH') || exit; ?>
<div class="wrap">
    <div class="plugin-header">
        <img src="<?php echo esc_url(plugins_url('assets/img/icon-svg.svg', dirname(dirname(dirname(__FILE__))))); ?>" alt="Free Mail SMTP" class="plugin-logo">
        <h1>FREE MAIL <span>SMTP</span></h1>
    </div>

    <p class="description">Setup custom SMTP or popular Providers to improve your WordPress email deliverability.</p>

    <nav class="free-mail-smtp-nav-tab-wrapper">
        <a href="<?php echo admin_url('admin.php?page=free_mail_smtp-providers'); ?>" class="free-mail-smtp-nav-tab">Providers</a>
        <a href="<?php echo admin_url('admin.php?page=free_mail_smtp-logs'); ?>" class="free-mail-smtp-nav-tab">Email Logs</a>
        <a href="<?php echo admin_url('admin.php?page=free_mail_smtp-analytics'); ?>" class="free-mail-smtp-nav-tab">Providers Logs</a>
        <a href="<?php echo admin_url('admin.php?page=free_mail_smtp-email-router'); ?>" class="free-mail-smtp-nav-tab free-mail-smtp-nav-tab-active">Email Router</a>
        <a href="<?php echo admin_url('admin.php?page=free_mail_smtp-settings'); ?>" class="free-mail-smtp-nav-tab">Settings</a>

    </nav>

    <?php settings_errors('free_mail_smtp_messages'); ?>

    <div class="tabset-content">
        <div class="table-header">
            <a href="#" class="page-title-action add-router-condition">
                <span class="dashicons dashicons-plus-alt2"></span> Add Router Condition
            </a>
        </div>

        <div class="providers-table-wrapper">
            <table class="widefat fixed providers-table">
                <thead>
                    <tr>
                        <th class="column-label"><?php _e('Label', 'free_mail_smtp'); ?></th>
                        <th class="column-provider"><?php _e('Enabled', 'free_mail_smtp'); ?></th>
                        <th class="column-actions"><?php _e('Actions', 'free_mail_smtp'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($conditions_list)): ?>
                        <tr class="no-items">
                            <td colspan="5" class="empty-state">
                                <img src="<?php echo esc_url(plugins_url('assets/img/icon-svg.svg', dirname(dirname(dirname(__FILE__))))); ?>" alt="No providers" class="empty-state-icon">
                                <p>It seems you haven't added any routing condition yet. Get started now.</p>
                                <button type="button" class="button button-primary save-condition" id="add-router-condition-button">
                                    <span class="dashicons dashicons-plus-alt2"></span> Add Router Condition
                                </button>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($conditions_list as $condition): ?>
                            <tr>
                                <td class="column-label">
                                    <strong><?php echo esc_html($condition->condition_label); ?></strong>
                                </td>
                                <td class="column-provider">
                                    <div class="toggle-container">
                                        <label class="toggle-switch">
                                            <input type="checkbox" class="toggle-is-enabled" data-id="<?php echo esc_attr($condition->id); ?>" <?php checked($condition->is_enabled, 1); ?>>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>
                                </td>
                                <td class="column-actions">
                                    <button type="button" class="button button-primary edit-condition" data-id="<?php echo esc_attr($condition->id); ?>">
                                        <span class="dashicons dashicons-edit"></span> Edit
                                    </button>
                                    <button type="button" class="button button-secondary delete-condition" data-id="<?php echo esc_attr($condition->id); ?>">
                                        <span class="dashicons dashicons-trash"></span> Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div id="router-modal" class="modal" style="display:none;">
            <div class="conditions-modal-content">
                <div class="conditions-modal-header">
                    <h2><?php _e('Configure Router Condition', 'free_mail_smtp'); ?></h2>
                    <button type="button" onclick="FreeMailSMTPRouter.closeModal(false)" class="conditions-modal-close">&times;</button>
                </div>
                <div class="conditions-modal-body">
                    <?php
                    $modal = dirname(__FILE__) . '/partials/modal.php';
                    if (file_exists($modal)) {
                        include $modal;
                    }
                    ?>
            </div>
            <div class="conditions-modal-footer">
                <div class="conditions-modal-footer-buttons">
                    <button type="button" class="btn btn-secondary" onclick="FreeMailSMTPRouter.closeModal(false)">Close</button>
                    <button type="button" class="btn btn-primary save-condition" onclick="FreeMailSMTPRouter.saveRouter()">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>