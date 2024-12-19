<?php defined('ABSPATH') || exit; ?>

<tr>
    <td class="column-priority">
        <?php echo esc_html($config['priority']); ?>
    </td>
    <td class="column-provider">
        <strong><?php echo esc_html($this->providers[$config['provider']]); ?></strong>
    </td>
    <td class="column-status">
        <span class="status-active">Active</span>
    </td>
    <td class="column-actions">
        <button type="button" 
                class="button edit-provider" 
                data-provider="<?php echo esc_attr(json_encode($config)); ?>"
                data-index="<?php echo esc_attr($index); ?>">
            <?php _e('Edit', 'free_mail_smtp'); ?>
        </button>
        <button type="button" 
                class="button test-provider" 
                data-provider="<?php echo esc_attr($config['provider']); ?>"
                data-api-key="<?php echo esc_attr($config['api_key']); ?>">
            <?php _e('Test', 'free_mail_smtp'); ?>
        </button>
        <button type="button" 
                class="button delete-provider" 
                data-index="<?php echo esc_attr($index); ?>">
            <?php _e('Delete', 'free_mail_smtp'); ?>
        </button>
    </td>
</tr>