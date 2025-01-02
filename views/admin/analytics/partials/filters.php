<?php
$current_month_start = date('Y-m-01');
$current_month_end = date('Y-m-d');
?>
<div class="tablenav top">
    <div class="alignleft actions filters-group">
    <?php wp_nonce_field('free_mail_smtp_analytics', 'free_mail_smtp_analytics_nonce'); ?>
        <select id="provider-filter">
            <?php foreach ($data['providers'] as $provider): ?>
                <option value="<?php echo esc_attr($provider['id']); ?>"
                        <?php selected($data['filters']['selected_provider'], $provider['id']); ?>>
                    <?php echo esc_html(ucfirst($provider['connection_label'])); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="date" 
               id="date-from" 
               value="<?php echo esc_attr($data['filters']['date_from'] ?: $current_month_start); ?>" 
               placeholder="From Date">
               
        <input type="date" 
               id="date-to" 
               value="<?php echo esc_attr($data['filters']['date_to'] ?: $current_month_end); ?>" 
               placeholder="To Date">

        <button type="button" class="button action apply-filter" id="apply-filters">
            <?php _e('Apply Filters', 'free_mail_smtp'); ?>
        </button>
    </div>
</div>