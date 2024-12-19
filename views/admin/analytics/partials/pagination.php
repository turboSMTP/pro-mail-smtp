<?php
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$total_items = count($data['analytics_data']);
$per_page = 20;
$total_pages = ceil($total_items / $per_page);

if ($total_pages > 1): ?>
    <div class="tablenav-pages">
        <span class="displaying-num">
            <?php printf(
                _n('%s item', '%s items', $total_items, 'email-api'),
                number_format_i18n($total_items)
            ); ?>
        </span>

        <span class="pagination-links">
            <?php
            // First page link
            if ($current_page > 1): ?>
                <a class="first-page button" href="<?php echo add_query_arg('paged', 1); ?>">
                    <span>«</span>
                </a>
            <?php else: ?>
                <span class="first-page button disabled">«</span>
            <?php endif;

            // Previous page link
            if ($current_page > 1): ?>
                <a class="prev-page button" href="<?php echo add_query_arg('paged', $current_page - 1); ?>">
                    <span>‹</span>
                </a>
            <?php else: ?>
                <span class="prev-page button disabled">‹</span>
            <?php endif; ?>

            <span class="paging-input">
                <label for="current-page-selector" class="screen-reader-text">Current Page</label>
                <input class="current-page" 
                       id="current-page-selector" 
                       type="text" 
                       name="paged"
                       value="<?php echo esc_attr($current_page); ?>" 
                       size="1" 
                       aria-describedby="table-paging">
                <span class="tablenav-paging-text">
                    of <span class="total-pages"><?php echo number_format_i18n($total_pages); ?></span>
                </span>
            </span>

            <?php 
            // Next page link
            if ($current_page < $total_pages): ?>
                <a class="next-page button" href="<?php echo add_query_arg('paged', $current_page + 1); ?>">
                    <span>›</span>
                </a>
            <?php else: ?>
                <span class="next-page button disabled">›</span>
            <?php endif;

            // Last page link
            if ($current_page < $total_pages): ?>
                <a class="last-page button" href="<?php echo add_query_arg('paged', $total_pages); ?>">
                    <span>»</span>
                </a>
            <?php else: ?>
                <span class="last-page button disabled">»</span>
            <?php endif; ?>
        </span>
    </div>
<?php endif; ?>