<?php
// Create a nonce for pagination
$pagination_nonce = wp_create_nonce('free_mail_smtp_pagination_nonce');

// Verify nonce if paged parameter is present
$current_page = 1;
if (isset($_GET['paged'])) {
    if (isset($_GET['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'free_mail_smtp_pagination_nonce')) {
        $current_page = max(1, intval($_GET['paged']));
    }
} 

$total_items = count($data['analytics_data']);
$per_page = 20;
$total_pages = ceil($total_items / $per_page);

if ($total_pages > 1): ?>
    <div class="tablenav-pages">
        <span class="displaying-num">
            <?php echo esc_html(
                sprintf(
                    /* translators: %s: number of items */
                    _n('%s item', '%s items', $total_items, 'free-mail-smtp'),
                    number_format_i18n($total_items)
                )
            ); ?>
        </span>

        <span class="pagination-links">
            <?php
            if ($current_page > 1): ?>
                <a class="first-page button" href="<?php echo esc_url(add_query_arg(array('paged' => 1, '_wpnonce' => $pagination_nonce))); ?>">
                    <span><?php echo esc_html('«'); ?></span>
                </a>
            <?php else: ?>
                <span class="first-page button disabled"><?php echo esc_html('«'); ?></span>
            <?php endif;

            if ($current_page > 1): ?>
                <a class="prev-page button" href="<?php echo esc_url(add_query_arg(array('paged' => $current_page - 1, '_wpnonce' => $pagination_nonce))); ?>">
                    <span><?php echo esc_html('‹'); ?></span>
                </a>
            <?php else: ?>
                <span class="prev-page button disabled"><?php echo esc_html('‹'); ?></span>
            <?php endif; ?>

            <span class="paging-input">
                <form action="" method="get">
                    <label for="current-page-selector" class="screen-reader-text"><?php echo esc_html__('Current Page', 'free-mail-smtp'); ?></label>
                    <input class="current-page" 
                           id="current-page-selector" 
                           type="text" 
                           name="paged"
                           value="<?php echo esc_attr($current_page); ?>" 
                           size="1" 
                           aria-describedby="table-paging">
                    <?php 
                    // Add hidden nonce field
                    wp_nonce_field('free_mail_smtp_pagination_nonce', '_wpnonce', false); 
                    
                    // Preserve any other existing query parameters
                    foreach ($_GET as $key => $value) {
                        if ($key !== 'paged' && $key !== '_wpnonce') {
                            echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
                        }
                    }
                    ?>
                    <span class="tablenav-paging-text">
                        <?php echo esc_html__('of', 'free-mail-smtp'); ?> <span class="total-pages"><?php echo esc_html(number_format_i18n($total_pages)); ?></span>
                    </span>
                </form>
            </span>

            <?php 
            if ($current_page < $total_pages): ?>
                <a class="next-page button" href="<?php echo esc_url(add_query_arg(array('paged' => $current_page + 1, '_wpnonce' => $pagination_nonce))); ?>">
                    <span><?php echo esc_html('›'); ?></span>
                </a>
            <?php else: ?>
                <span class="next-page button disabled"><?php echo esc_html('›'); ?></span>
            <?php endif;
            if ($current_page < $total_pages): ?>
                <a class="last-page button" href="<?php echo esc_url(add_query_arg(array('paged' => $total_pages, '_wpnonce' => $pagination_nonce))); ?>">
                    <span><?php echo esc_html('»'); ?></span>
                </a>
            <?php else: ?>
                <span class="last-page button disabled"><?php echo esc_html('»'); ?></span>
            <?php endif; ?>
        </span>
    </div>
<?php endif; ?>