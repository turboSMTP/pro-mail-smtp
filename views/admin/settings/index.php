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
                                <!-- <p>If you need support, check out our <a href="#">documentation</a>.</p> -->
                                <button type="button" class="button button-primary save-provider">
                                    <span class="dashicons dashicons-plus-alt2"></span> Add Provider
                                </button>
                            </td>                        </tr>
                    <?php else: ?>
                        <?php foreach ($providers_config as $index => $config): ?>
                            <tr>
                                <td class="column-label">
                                    <?php echo esc_html($config['connection_label']); ?>
                                </td>
                                <td class="column-priority">
                                    <?php echo esc_html($config['priority']); ?>
                                </td>
                                <td class="column-provider">
                                    <img src="<?php echo esc_url(plugins_url("assets/img/providers/{$config['provider']}.svg", dirname(dirname(dirname(__FILE__))))); ?>" 
                                         alt="" 
                                         class="provider-icon">
                                    <strong><?php echo esc_html($providers_list[$config['provider']]); ?></strong>
                                </td>
                                <td>
                                    <?php if ($config['provider'] === 'gmail' && !get_option('free_mail_smtp_gmail_access_token')): ?>
                                        <a href="<?php echo esc_url($config['config_keys']['auth_url']); ?>" class="button button-primary google-sign">Connect Gmail Account</a>
                                    <?php endif; ?>
                                </td>
                                <td class="column-actions">
                                    <button type="button"
                                        class="button edit-provider"
                                        data-index="<?php echo esc_attr($index); ?>"
                                        data-config='<?php echo esc_attr(json_encode([
                                            'provider' => $config['provider'],
                                            'config_keys' => $config['config_keys'],
                                            'priority' => $config['priority'],
                                            'connection_label' => $config['connection_label']
                                        ])); ?>'>
                                        Edit
                                    </button>
                                    <button type="button"
                                        class="button test-provider"
                                        data-index="<?php echo esc_attr($index); ?>"
                                        data-provider="<?php echo esc_attr($config['provider']); ?>"
                                        data-api-key="<?php echo esc_attr($config['config_keys']); ?>">
                                        <?php _e('Test', 'free-mail-smtp'); ?>
                                    </button>
                                    <button type="button"
                                        class="button delete-provider"
                                        data-index="<?php echo esc_attr($index); ?>">
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
                    <!-- Step 1: Choose Provider -->
                    <div class="wizard-step" id="step-provider">
                        <p class="description"><?php _e('Select a provider to configure your email settings.', 'free_mail_smtp'); ?></p>
                        <div class="provider-grid">
                            <?php foreach ($providers_list as $key => $name): ?>
                                <div class="provider-card" data-provider="<?php echo esc_attr($key); ?>">
                                    <img src="<?php echo esc_url(plugins_url("assets/img/providers/{$key}.svg", dirname(dirname(dirname(__FILE__))))); ?>"
                                        alt="<?php echo esc_attr($name); ?>"
                                        onerror="this.src='<?php echo esc_url(plugins_url('assets/img/providers/default.svg', dirname(dirname(dirname(__FILE__))))); ?>'">
                                    <h4><?php echo esc_html($name); ?></h4>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <!-- Step 2: Provider Configuration -->
                    <div class="wizard-step" id="step-config" style="display: none;">
                        <!-- Provider form will be loaded here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- General Settings -->
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