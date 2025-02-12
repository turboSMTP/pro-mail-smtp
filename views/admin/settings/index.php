<?php defined('ABSPATH') || exit; ?>

<div class="wrap">
    <div class="plugin-header">
        <img src="<?php echo esc_url(plugins_url('assets/img/icon-svg.svg', dirname(dirname(dirname(__FILE__))))); ?>" alt="Free Mail SMTP" class="plugin-logo">
        <h1>FREE MAIL <span>SMTP</span></h1>
    </div>
    
    <p class="description">Setup custom SMTP or popular Providers to improve your WordPress email deliverability.</p>

    <nav class="nav-tab-wrapper">
        <a href="<?php echo admin_url('admin.php?page=free_mail_smtp-settings'); ?>" class="nav-tab nav-tab-active">Providers</a>
        <a href="<?php echo admin_url('admin.php?page=free_mail_smtp-logs'); ?>" class="nav-tab">Email Logs</a>
        <a href="<?php echo admin_url('admin.php?page=free_mail_smtp-analytics'); ?>" class="nav-tab">Providers Logs</a>
        <a href="<?php echo admin_url('admin.php?page=free_mail_smtp-email-router'); ?>" class="nav-tab">Email Router</a>
    </nav>

    <?php settings_errors('free_mail_smtp_messages'); ?>

    <div class="tabset-content">
        <div class="table-header">
            <a href="#" class="page-title-action add-provider">
                <span class="dashicons dashicons-plus-alt2"></span> Add Provider
            </a>
        </div>

        <!-- Providers Table -->
        <div class="providers-table-wrapper">
            <table class="widefat fixed providers-table">
                <thead>
                    <tr>
                        <th class="column-label"><?php _e('Label', 'free_mail_smtp'); ?></th>
                        <th class="column-priority"><?php _e('Priority', 'free_mail_smtp'); ?></th>
                        <th class="column-provider"><?php _e('Provider', 'free_mail_smtp'); ?></th>
                        <th class="column-status"><?php _e('Required Actions', 'free_mail_smtp'); ?></th>
                        <th class="column-actions"><?php _e('Actions', 'free_mail_smtp'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($providers_config)): ?>
                        <tr class="no-items">
                        <td colspan="5" class="empty-state">
                                <img src="<?php echo esc_url(plugins_url('assets/img/icon-svg.svg', dirname(dirname(dirname(__FILE__))))); ?>" alt="No providers" class="empty-state-icon">
                                <p>It seems you haven't added any providers yet. Get started now.</p>
                                <button type="button" class="button button-primary save-provider" id="add-provider-button">
                                    <span class="dashicons dashicons-plus-alt2"></span> Add Provider
                                </button>
                            </td>                        </tr>
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
                                    <img src="<?php echo esc_url(plugins_url("assets/img/providers/{$provider->provider}.svg", dirname(dirname(dirname(__FILE__))))); ?>" 
                                         alt="" 
                                         class="provider-icon">
                                    <strong><?php echo esc_html($providers_list[$provider->provider]['label']); ?></strong>
                                </td>
                                <td>
                                    <?php if ($provider->provider === 'gmail' && empty(get_option('free_mail_smtp_gmail_access_token'))): ?>
                                        <a href="<?php echo esc_url(isset($provider->connection_data['auth_url']) ? $provider->connection_data['auth_url'] : '#'); ?>" class="button button-primary google-sign">Connect Gmail Account</a>
                                    <?php endif; ?>
                                    <?php if ($provider->provider === 'outlook' && empty(get_option('free_mail_smtp_outlook_access_token'))): ?>
                                        <a href="<?php echo esc_url(isset($provider->connection_data['auth_url']) ? $provider->connection_data['auth_url'] : '#'); ?>" class="button button-primary outlook-sign">Connect Outlook Account</a>
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
                                        Edit
                                    </button>
                                    <button type="button"
                                        class="button test-provider"
                                        data-connection_id="<?php echo esc_attr($provider->connection_id); ?>"
                                        data-provider="<?php echo esc_attr($provider->provider); ?>"
                                        data-api-key='<?php echo esc_attr(json_encode($provider->connection_data)); ?>'>
                                        <?php _e('Test', 'free_mail_smtp'); ?>
                                    </button>
                                    <button type="button"
                                        class="button delete-provider"
                                        data-connection_id="<?php echo esc_attr($provider->connection_id); ?>">
                                        Delete
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
                    <h2><?php _e('Configure Provider', 'free_mail_smtp'); ?></h2>
                    <button type="button" class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="wizard-step" id="step-provider">
                        <p class="description"><?php _e('Select a provider to configure your email settings.', 'free_mail_smtp'); ?></p>
                        <div class="provider-grid">
                            <?php foreach ($providers_list as $key => $info): ?>
                                <div class="provider-card" data-provider="<?php echo esc_attr($key); ?>">
                                    <img src="<?php echo esc_url(plugins_url("assets/img/providers/{$key}.svg", dirname(dirname(dirname(__FILE__))))); ?>"
                                        alt="<?php echo esc_attr($info['label']); ?>"
                                        onerror="this.src='<?php echo esc_url(plugins_url('assets/img/providers/default.svg', dirname(dirname(dirname(__FILE__))))); ?>'">
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

        <div class="general-settings-wrapper">
            <h2><?php _e('General Settings', 'free_mail_smtp'); ?></h2>
            <form method="post" action="">
                <?php wp_nonce_field('free_mail_smtp_settings', 'free_mail_smtp_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="from_email"><?php _e('From Email', 'free_mail_smtp'); ?></label>
                        </th>
                        <td>
                            <input type="email" name="from_email" id="from_email" value="<?php echo esc_attr($from_email); ?>" class="regular-text" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="from_name"><?php _e('From Name', 'free_mail_smtp'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="from_name" id="from_name" value="<?php echo esc_attr($from_name); ?>" class="regular-text" required>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="save_settings" class="button button-primary" value="<?php _e('Save Settings', 'free_mail_smtp'); ?>">
                </p>
            </form>
        </div>
    </div>
</div>