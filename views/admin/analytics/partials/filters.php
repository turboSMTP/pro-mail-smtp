<?php
$current_month_start = date('Y-m-01');
$current_month_end = date('Y-m-d');
?>
<div class="tablenav top">
    <div class="alignleft actions filters-group">
    <?php wp_nonce_field('free_mail_smtp_analytics', 'free_mail_smtp_analytics_nonce'); ?>
        <label for="provider-filter">Provider</label>
        <select id="provider-filter">
            <?php foreach ($data['providers'] as $provider): ?>
                <option value="<?php echo esc_attr($provider->connection_id); ?>"
                        <?php selected($data['filters']['selected_provider'], $provider->connection_id); ?>>
                    <?php echo esc_html($provider->connection_label); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="date-from">From Date</label>
        <input type="date" 
               id="date-from" 
               value="<?php echo esc_attr($data['filters']['date_from'] ?: $current_month_start); ?>" 
               placeholder="From Date">
               
        <label for="date-to">To Date</label>
        <input type="date" 
               id="date-to" 
               value="<?php echo esc_attr($data['filters']['date_to'] ?: $current_month_end); ?>" 
               placeholder="To Date">

        <label for="per-page">Rows per page</label>
        <input type="number" 
               id="per-page" 
               value="<?php echo esc_attr($data['filters']['per_page'] ?: 5); ?>" 
               min="1" 
               placeholder="Rows per page">

        <button type="button" class="button action apply-filter" id="apply-filters">
            <?php _e('Apply Filters', 'free_mail_smtp'); ?>
        </button>
    </div>
</div>