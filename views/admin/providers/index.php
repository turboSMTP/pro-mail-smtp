<?php defined('ABSPATH') || exit; ?>

<div class="wrap">
    <div class="plugin-header">
        <img src="<?php echo esc_url(plugins_url('assets/img/icon-svg.svg', FREE_MAIL_SMTP_FILE)); ?>" alt="<?php esc_attr_e('Free Mail SMTP', 'free-mail-smtp'); ?>" class="plugin-logo">
        <h1><?php esc_html_e('FREE MAIL', 'free-mail-smtp'); ?> <span><?php esc_html_e('SMTP', 'free-mail-smtp'); ?></span></h1>
    </div>

    <p class="description"><?php esc_html_e('Setup custom SMTP or popular Providers to improve your WordPress email deliverability.', 'free-mail-smtp'); ?></p>

    <nav class="free-mail-smtp-nav-tab-wrapper">
        <a href="<?php echo esc_url(admin_url('admin.php?page=free-mail-smtp-providers')); ?>" class="free-mail-smtp-nav-tab free-mail-smtp-nav-tab-active"><?php esc_html_e('Providers', 'free-mail-smtp'); ?></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=free-mail-smtp-logs')); ?>" class="free-mail-smtp-nav-tab"><?php esc_html_e('Email Logs', 'free-mail-smtp'); ?></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=free-mail-smtp-analytics')); ?>" class="free-mail-smtp-nav-tab"><?php esc_html_e('Providers Logs', 'free-mail-smtp'); ?></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=free-mail-smtp-email-router')); ?>" class="free-mail-smtp-nav-tab"><?php esc_html_e('Email Router', 'free-mail-smtp'); ?></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=free-mail-smtp-settings')); ?>" class="free-mail-smtp-nav-tab"><?php esc_html_e('Settings', 'free-mail-smtp'); ?></a>
    </nav>

    <?php settings_errors('free_mail_smtp_messages'); ?>

    <div class="tabset-content">
        <?php if ($import_available['easySMTP']): ?>
            <div id="import-banner" class="import-banner" style="background-color:#ff6e7914; border: 1px solid #ff6e79; padding: 10px; margin-bottom: 20px; position: relative;">
                <span> <?php esc_html_e('We noticed you have Easy WP SMTP installed. Would you like to import your settings?', 'free-mail-smtp'); ?> </span>
                <button id="import" class="button" style="margin-left: 10px;" data-import-nonce="<?php echo esc_attr(wp_create_nonce('free_mail_smtp_import')); ?>" data-plugin="easySMTP"><?php esc_html_e('Import Providers', 'free-mail-smtp'); ?></button>
                <span class="dismiss-banner" style="position: absolute; top: 5px; right: 10px; cursor: pointer;">&times;</span>
            </div>
        <?php endif; ?>
        <?php if ($import_available['wpMail']): ?>
            <div id="import-banner" class="import-banner" style="background-color:#ff6e7914; border: 1px solid #ff6e79; padding: 10px; margin-bottom: 20px; position: relative;">
                <span> <?php esc_html_e('We noticed you have WP Mail SMTP installed. Would you like to import your settings?', 'free-mail-smtp'); ?></span>
                <button id="import" class="button" style="margin-left: 10px;" data-import-nonce="<?php echo esc_attr(wp_create_nonce('free_mail_smtp_import')); ?>" data-plugin="wpMail"><?php esc_html_e('Import Providers', 'free-mail-smtp'); ?></button>
                <span class="dismiss-banner" style="position: absolute; top: 5px; right: 10px; cursor: pointer;">&times;</span>
            </div>
        <?php endif; ?>
        <div class="table-header">
            <a href="#" class="page-title-action add-provider">
                <span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e('Add Provider', 'free-mail-smtp'); ?>
            </a>
        </div>

        <!-- Providers Table -->
        <div class="providers-table-wrapper">
            <table class="widefat fixed providers-table">
                <thead>
                    <tr>
                        <th class="column-label"><?php esc_html_e('Label', 'free-mail-smtp'); ?></th>
                        <th class="column-priority"><?php esc_html_e('Priority', 'free-mail-smtp'); ?></th>
                        <th class="column-provider"><?php esc_html_e('Provider', 'free-mail-smtp'); ?></th>
                        <th class="column-status"><?php esc_html_e('Required Actions', 'free-mail-smtp'); ?></th>
                        <th class="column-actions"><?php esc_html_e('Actions', 'free-mail-smtp'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($providers_config)): ?>
                        <tr class="no-items">
                            <td colspan="5" class="empty-state">
                                <img src="<?php echo esc_url(plugins_url('assets/img/icon-svg.svg', FREE_MAIL_SMTP_FILE)); ?>" alt="<?php esc_attr_e('No providers', 'free-mail-smtp'); ?>" class="empty-state-icon">
                                <p><?php esc_html_e('It seems you haven\'t added any providers yet.', 'free-mail-smtp'); ?></p>
                                <p>
                                    <?php esc_html_e('Our plugin supports the most important SMTP providers to improve email deliverability.', 'free-mail-smtp'); ?>
                                </p>
                                <p><?php esc_html_e('Get started now.', 'free-mail-smtp'); ?></p>
                                <div class="suggested-providers">
                                    <?php
                                    foreach ($providers_list as $provider): ?>
                                        <?php if ($provider['name'] !== 'other'): ?>
                                            <img src="<?php echo esc_url(plugins_url("assets/img/providers/{$provider['name']}.svg", FREE_MAIL_SMTP_FILE)); ?>"
                                                alt=""
                                                class="provider-icon">
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" class="button button-primary save-provider" id="add-provider-button">
                                    <span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e('Add Provider', 'free-mail-smtp'); ?>
                                </button>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($providers_config as $index => $provider): ?>
                            <tr>
                                <td class="column-label">
                                    <?php
                                    $label = isset($provider->connection_data['connection_label'])
                                        ? $provider->connection_data['connection_label']
                                        : $provider->provider . '-' . $provider->connection_id;
                                    echo esc_html($label);
                                    ?>
                                </td>
                                <td class="column-priority">
                                    <?php echo esc_html($provider->priority); ?>
                                </td>
                                <td class="column-provider">
                                    <img src="<?php echo esc_url(plugins_url("assets/img/providers/{$provider->provider}.svg", FREE_MAIL_SMTP_FILE)); ?>"
                                        alt=""
                                        class="provider-icon">
                                    <strong><?php echo esc_html($providers_list[$provider->provider]['label']); ?></strong>
                                </td>
                                <td>
                                    <?php if ($provider->provider === 'gmail' && empty(get_option('free_mail_smtp_gmail_access_token'))): ?>
                                        <a href="<?php echo esc_url(isset($provider->connection_data['auth_url']) ? $provider->connection_data['auth_url'] : '#'); ?>" class="button button-primary google-sign"><?php esc_html_e('Connect Gmail Account', 'free-mail-smtp'); ?></a>
                                    <?php endif; ?>
                                    <?php if ($provider->provider === 'outlook' && empty(get_option('free_mail_smtp_outlook_access_token'))): ?>
                                        <a href="<?php echo esc_url(isset($provider->connection_data['auth_url']) ? $provider->connection_data['auth_url'] : '#'); ?>" class="button button-primary outlook-sign"><?php esc_html_e('Connect Outlook Account', 'free-mail-smtp'); ?></a>
                                    <?php endif; ?>
                                </td>
                                <td class="column-actions">
                                    <button type="button"
                                        class="button edit-provider"
                                        data-connection_id="<?php echo esc_attr($provider->connection_id); ?>"
                                        data-config='<?php echo esc_attr(json_encode([
                                                            'provider' => $provider->provider,
                                                            'config_keys' => $provider->connection_data,
                                                            'priority' => $provider->priority,
                                                            'connection_label' => $provider->connection_label
                                                        ])); ?>'>
                                        <?php esc_html_e('Edit', 'free-mail-smtp'); ?>
                                    </button>
                                    <button type="button"
                                        class="button test-provider"
                                        data-connection_id="<?php echo esc_attr($provider->connection_id); ?>"
                                        data-provider="<?php echo esc_attr($provider->provider); ?>"
                                        data-api-key='<?php echo esc_attr(json_encode($provider->connection_data)); ?>'>
                                        <?php esc_html_e('Test', 'free-mail-smtp'); ?>
                                    </button>
                                    <button type="button"
                                        class="button delete-provider"
                                        data-connection_id="<?php echo esc_attr($provider->connection_id); ?>">
                                        <?php esc_html_e('Delete', 'free-mail-smtp'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Provider Modal -->
        <div id="provider-modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2><?php esc_html_e('Configure Provider', 'free-mail-smtp'); ?></h2>
                    <button type="button" class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="wizard-step" id="step-provider">
                        <p class="description"><?php esc_html_e('Select a provider to configure your email settings.', 'free-mail-smtp'); ?></p>
                        <div class="provider-grid">
                            <?php foreach ($providers_list as $key => $info): ?>
                                <div class="provider-card <?php echo !empty($info['recommended']) ? 'recommended' : ''; ?>" data-provider="<?php echo esc_attr($key); ?>">
                                    <?php if ($info['recommended']): ?>
                                        <div class="ribbon-recommended"><?php esc_html_e('Recommended', 'free-mail-smtp'); ?></div>
                                    <?php endif; ?>
                                    <img src="<?php echo esc_url(plugins_url("assets/img/providers/{$key}.svg", FREE_MAIL_SMTP_FILE)); ?>"
                                        alt="<?php echo esc_attr($info['label']); ?>"
                                        onerror="this.src='<?php echo esc_url(plugins_url('assets/img/providers/default.svg', FREE_MAIL_SMTP_FILE)); ?>'">
                                    <h4><?php echo esc_html($info['label']); ?></h4>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="wizard-step" id="step-config" style="display: none;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>