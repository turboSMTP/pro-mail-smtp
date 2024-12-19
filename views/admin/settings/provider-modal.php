<?php defined('ABSPATH') || exit; ?>

<div id="provider-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2><?php _e('Configure Provider', 'free_mail_smtp'); ?></h2>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="provider-form" method="post">
                <?php wp_nonce_field('free_mail_smtp_settings', 'free_mail_smtp_nonce'); ?>
                <input type="hidden" name="provider_index" id="provider_index" value="">
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="provider"><?php _e('Provider', 'free_mail_smtp'); ?></label>
                        </th>
                        <td>
                            <select name="provider" id="provider" required>
                                <option value=""><?php _e('Select Provider', 'free_mail_smtp'); ?></option>
                                <?php foreach ($this->providers as $key => $name): ?>
                                    <option value="<?php echo esc_attr($key); ?>">
                                        <?php echo esc_html($name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="api_key"><?php _e('API Key', 'free_mail_smtp'); ?></label>
                        </th>
                        <td>
                            <input type="password" name="api_key" id="api_key" class="regular-text" required>
                            <button type="button" class="button toggle-password">
                                <span class="dashicons dashicons-visibility"></span>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="priority"><?php _e('Priority', 'free_mail_smtp'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="priority" id="priority" class="small-text" min="1" required>
                            <p class="description">
                                <?php _e('Lower number = higher priority', 'free_mail_smtp'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                <div class="submit-wrapper">
                    <button type="submit" class="button button-primary">
                        <?php _e('Save Provider', 'free_mail_smtp'); ?>
                    </button>
                    <button type="button" class="button modal-close">
                        <?php _e('Cancel', 'free_mail_smtp'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>