<?php
/**
 * Logs Table partial for Email Logs
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>        <?php foreach ($columns as $key => $label): ?>
            <th scope="col"
                class="manage-column column-<?php echo esc_attr($key); ?> <?php echo esc_attr(call_user_func($data['get_column_sort_class'], $key, $filters)); ?>">
                <a href="#" class="sort-column" data-column="<?php echo esc_attr($key); ?>">
                    <span><?php echo esc_html($label); ?></span>
                </a>
            </th>
        <?php endforeach; ?>
        </tr>
    </thead>

    <tbody>
        <?php if (empty($logs)): ?>
            <tr class="no-items">
                <td class="colspanchange" colspan="<?php echo count($columns); ?>">
                    <?php esc_html_e('No logs found.', 'pro-mail-smtp'); ?>
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td class="column-date">
                        <?php echo esc_html(call_user_func($data['format_date'], $log->sent_at)); ?><br>
                        <small><?php echo esc_html(call_user_func($data['time_diff'], $log->sent_at)); ?></small>
                    </td>
                    <td class="column-provider">
                        <span class="provider-badge provider-<?php echo esc_attr($log->provider); ?>">
                            <?php echo esc_html(ucfirst($log->provider)); ?>
                        </span>
                    </td>
                    <td class="column-to">
                        <?php echo esc_html($log->to_email); ?>
                    </td>
                    <td class="column-subject">
                        <?php echo esc_html($log->subject); ?>
                    </td>
                    <td class="column-status">
                        <span class="status-badge status-<?php echo esc_attr($log->status); ?>">
                            <?php echo esc_html(ucfirst($log->status)); ?>
                        </span>
                    </td>
                    <td class="column-details">
                        <?php echo esc_html($log->error_message); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>

    <tfoot>
        <tr>
            <?php foreach ($columns as $key => $label): ?>
                <th scope="col" class="manage-column column-<?php echo esc_attr($key); ?>">
                    <?php echo esc_html($label); ?>
                </th>
            <?php endforeach; ?>
        </tr>
    </tfoot>
</table>
