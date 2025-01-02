<?php

namespace FreeMailSMTP\Admin;

class Logs
{
    private $per_page = 20;
    private $statuses = [
        'sent' => '#3498db',
        'delivered' => '#2ecc71',
        'opened' => '#f1c40f',
        'clicked' => '#9b59b6',
        'failed' => '#e74c3c',
        'bounced' => '#e67e22',
        'spam' => '#c0392b'
    ];

    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function enqueue_scripts($hook)
    {
        if (strpos($hook, 'free_mail_smtp-logs') === false) {
            return;
        }

        wp_enqueue_script(
            'free_mail_smtp-logs',
            plugins_url('assets/js/logs.js', dirname(dirname(__FILE__))),
            ['jquery'],
            '1.0.0',
            true
        );

        wp_enqueue_style(
            'free_mail_smtp-logs',
            plugins_url('assets/css/logs.css', dirname(dirname(__FILE__))),
            [],
            '1.0.0'
        );

        wp_localize_script('free_mail_smtp-logs', 'FreeMailSMTPLogs', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('free_mail_smtp_logs'),
            'i18n' => [
                'confirmDelete' => __('Are you sure you want to delete the selected logs?', 'free_mail_smtp'),
                'noLogsSelected' => __('Please select at least one log to delete.', 'free_mail_smtp'),
                'deleted' => __('Selected logs have been deleted.', 'free_mail_smtp'),
                'error' => __('An error occurred. Please try again.', 'free_mail_smtp')
            ]
        ]);
    }

    public function render()
    {
        $filters = $this->get_filters();
        $logs = $this->get_logs($filters);
        $total_items = $this->get_total_logs($filters);
        $total_pages = ceil($total_items / $this->per_page);

?>
        <div class="wrap free_mail_smtp-wrap">
            <div class="plugin-header">
                <img src="<?php echo esc_url(plugins_url('assets/img/icon-svg.svg', dirname(dirname(__FILE__)))); ?>" alt="Free Mail SMTP" class="plugin-logo">
                <h1>FREE MAIL <span>SMTP</span></h1>
            </div>

            <p class="description">Setup custom SMTP or popular Providers to improve your WordPress email deliverability.</p>

            <nav class="nav-tab-wrapper">
                <a href="<?php echo admin_url('admin.php?page=free_mail_smtp-settings'); ?>" class="nav-tab">Providers</a>
                <a href="<?php echo admin_url('admin.php?page=free_mail_smtp-logs'); ?>" class="nav-tab nav-tab-active">Email Logs</a>
                <a href="<?php echo admin_url('admin.php?page=free_mail_smtp-analytics'); ?>" class="nav-tab">Providers Logs</a>
            </nav>
            <!-- Filters -->
            <div class="tablenav top">
                <form method="get" class="email-filters">
                    <input type="hidden" name="page" value="free_mail_smtp-logs">
                    <div class="alignleft actions filters">
                        <select name="provider" class="provider-filter">
                            <option value=""><?php _e('All Providers', 'free_mail_smtp'); ?></option>
                            <?php foreach ($this->get_providers() as $key => $name): ?>
                                <option value="<?php echo esc_attr($key); ?>"
                                    <?php selected($filters['provider'], $key); ?>>
                                    <?php echo esc_html($name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <select name="status" class="status-filter">
                            <option value=""><?php _e('All Statuses', 'free_mail_smtp'); ?></option>
                            <?php foreach (array_keys($this->statuses) as $status): ?>
                                <option value="<?php echo esc_attr($status); ?>"
                                    <?php selected($filters['status'], $status); ?>>
                                    <?php echo esc_html(ucfirst($status)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <input type="date"
                            name="date_from"
                            value="<?php echo esc_attr($filters['date_from']); ?>"
                            class="date-picker"
                            placeholder="<?php _e('From Date', 'free_mail_smtp'); ?>">

                        <input type="date"
                            name="date_to"
                            value="<?php echo esc_attr($filters['date_to']); ?>"
                            class="date-picker"
                            placeholder="<?php _e('To Date', 'free_mail_smtp'); ?>">

                        <input type="search"
                            name="search"
                            value="<?php echo esc_attr($filters['search']); ?>"
                            class="search-input"
                            placeholder="<?php _e('Search emails...', 'free_mail_smtp'); ?>">

                        <input type="submit"
                            class="button apply-filter"
                            value="<?php _e('Filter', 'free_mail_smtp'); ?>">
                    </div>

                    <div class="alignright">
                        <span class="displaying-num">
                            <?php printf(
                                _n('%s item', '%s items', $total_items, 'free_mail_smtp'),
                                number_format_i18n($total_items)
                            ); ?>
                        </span>
                    </div>
                </form>
            </div>

            <!-- Logs Table -->
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <td class="manage-column column-cb check-column">
                            <input type="checkbox" id="cb-select-all-1">
                        </td>
                        <?php foreach ($this->get_columns() as $key => $label): ?>
                            <th scope="col"
                                class="manage-column column-<?php echo esc_attr($key); ?> <?php echo $this->get_column_sort_class($key, $filters); ?>">
                                <a href="<?php echo esc_url($this->get_sort_url($key)); ?>">
                                    <span><?php echo esc_html($label); ?></span>
                                    <span class="sorting-indicator"></span>
                                </a>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr class="no-items">
                            <td class="colspanchange" colspan="<?php echo count($this->get_columns()) + 1; ?>">
                                <?php _e('No logs found.', 'free_mail_smtp'); ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <th scope="row" class="check-column">
                                    <input type="checkbox"
                                        name="log_ids[]"
                                        value="<?php echo esc_attr($log->id); ?>">
                                </th>
                                <td class="column-date">
                                    <?php echo esc_html($this->format_date($log->sent_at)); ?>
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
                                    <?php if ($log->error_message): ?>
                                        <!-- <span class="error-icon dashicons dashicons-warning" 
                                              title="<?php //echo esc_attr($log->error_message); 
                                                        ?>">
                                        </span> -->
                                    <?php endif; ?>
                                </td>
                                <td class="column-details">
                                    <?php echo esc_html($log->error_message); ?>

                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>

                <tfoot>
                    <tr>
                        <td class="manage-column column-cb check-column">
                            <input type="checkbox" id="cb-select-all-2">
                        </td>
                        <?php foreach ($this->get_columns() as $key => $label): ?>
                            <th scope="col" class="manage-column column-<?php echo esc_attr($key); ?>">
                                <?php echo esc_html($label); ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </tfoot>
            </table>

            <!-- Pagination -->
            <div class="tablenav bottom">
                <?php $this->render_pagination($total_items, $total_pages, $filters['paged']); ?>
            </div>
        </div>

        <!-- Log Details Modal -->
        <?php $this->render_modal_template(); ?>
    <?php
    }

    private function render_modal_template()
    {
    ?>
        <script type="text/template" id="tmpl-log-details">
            <div class="log-details-content">
                <h2><?php _e('Email Log Details', 'free_mail_smtp'); ?></h2>
                <div class="log-details-grid">
                    <div class="log-section">
                        <h3><?php _e('Basic Information', 'free_mail_smtp'); ?></h3>
                        <table class="log-info">
                            <tr>
                                <th><?php _e('Message ID', 'free_mail_smtp'); ?></th>
                                <td>{{ data.message_id }}</td>
                            </tr>
                            <tr>
                                <th><?php _e('Status', 'free_mail_smtp'); ?></th>
                                <td>
                                    <span class="status-badge status-{{ data.status }}">
                                        {{ data.status }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e('Provider', 'free_mail_smtp'); ?></th>
                                <td>{{ data.provider }}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="log-section">
                        <h3><?php _e('Timeline', 'free_mail_smtp'); ?></h3>
                        <div class="log-timeline">
                            <div class="timeline-item">
                                <span class="time">{{ data.sent_at }}</span>
                                <span class="event"><?php _e('Sent', 'free_mail_smtp'); ?></span>
                            </div>
                            <# if (data.error_message) { #>
                                <div class="timeline-item">
                                    <span class="time">{{ data.error_message }}</span>
                                    <span class="event"><?php _e('Details', 'free_mail_smtp'); ?></span>
                                </div>
                            <# } #>
                        </div>
                    </div>
                    
                    <# if (data.error_message) { #>
                        <div class="log-section error-section">
                            <h3><?php _e('Error Details', 'free_mail_smtp'); ?></h3>
                            <div class="error-message">
                                {{ data.error_message }}
                            </div>
                        </div>
                    <# } #>
                    
                    <div class="log-section">
                        <h3><?php _e('Email Content', 'free_mail_smtp'); ?></h3>
                        <table class="log-info">
                            <tr>
                                <th><?php _e('To', 'free_mail_smtp'); ?></th>
                                <td>{{ data.to_email }}</td>
                            </tr>
                            <tr>
                                <th><?php _e('Subject', 'free_mail_smtp'); ?></th>
                                <td>{{ data.subject }}</td>
                            </tr>
                            <# if (data.headers) { #>
                                <tr>
                                    <th><?php _e('Headers', 'free_mail_smtp'); ?></th>
                                    <td><pre>{{ data.headers }}</pre></td>
                                </tr>
                            <# } #>
                            <tr>
                                <th><?php _e('Body', 'free_mail_smtp'); ?></th>
                                <td><div class="email-body">{{ data.message }}</div></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </script>
<?php
    }

    private function get_columns()
    {
        return [
            'sent_at' => __('Date', 'free_mail_smtp'),
            'provider' => __('Provider', 'free_mail_smtp'),
            'to_email' => __('To', 'free_mail_smtp'),
            'subject' => __('Subject', 'free_mail_smtp'),
            'status' => __('Status', 'free_mail_smtp'),
            'details' => __('Details', 'free_mail_smtp')
        ];
    }

    private function get_providers()
    {
        return [
            'sendgrid' => 'SendGrid',
            'mailgun' => 'Mailgun',
            'ses' => 'Amazon SES',
            'postmark' => 'Postmark',
            'turbosmtp' => 'TurboSMTP',
            'brevo' => 'Brevo',
            'gmail' => 'Gmail',
            'smtp2go' => 'SMTP2GO',
        ];
    }

    private function get_filters()
    {
        return [
            'paged' => max(1, intval($_GET['paged'] ?? 1)),
            'provider' => sanitize_text_field($_GET['provider'] ?? ''),
            'status' => sanitize_text_field($_GET['status'] ?? ''),
            'search' => sanitize_text_field($_GET['search'] ?? ''),
            'date_from' => sanitize_text_field($_GET['date_from'] ?? ''),
            'date_to' => sanitize_text_field($_GET['date_to'] ?? ''),
            'orderby' => sanitize_text_field($_GET['orderby'] ?? 'sent_at'),
            'order' => sanitize_text_field($_GET['order'] ?? 'desc')
        ];
    }

    private function get_logs($filters)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'email_log';

        $where = [];
        $values = [];

        if (!empty($filters['provider'])) {
            $where[] = 'provider = %s';
            $values[] = $filters['provider'];
        }

        if (!empty($filters['status'])) {
            $where[] = 'status = %s';
            $values[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(to_email LIKE %s OR subject LIKE %s)';
            $search_term = '%' . $wpdb->esc_like($filters['search']) . '%';
            $values[] = $search_term;
            $values[] = $search_term;
        }

        if (!empty($filters['date_from'])) {
            $where[] = 'sent_at >= %s';
            $values[] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'sent_at <= %s';
            $values[] = $filters['date_to'] . ' 23:59:59';
        }

        $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $orderby = $this->validate_orderby($filters['orderby']);
        $order = $filters['order'] === 'asc' ? 'ASC' : 'DESC';

        $offset = ($filters['paged'] - 1) * $this->per_page;

        $query = $wpdb->prepare(
            "SELECT SQL_CALC_FOUND_ROWS * 
            FROM $table_name 
            $where_clause 
            ORDER BY $orderby $order 
            LIMIT %d OFFSET %d",
            array_merge($values, [$this->per_page, $offset])
        );

        return $wpdb->get_results($query);
    }

    private function get_total_logs($filters)
    {
        global $wpdb;
        return $wpdb->get_var('SELECT FOUND_ROWS()');
    }

    private function validate_orderby($orderby)
    {
        $allowed = array_keys($this->get_columns());
        return in_array($orderby, $allowed) ? $orderby : 'sent_at';
    }

    private function get_sort_url($column)
    {
        $current_orderby = $_GET['orderby'] ?? 'sent_at';
        $current_order = $_GET['order'] ?? 'desc';

        $order = ($current_orderby === $column && $current_order === 'desc') ? 'asc' : 'desc';

        $params = array_merge($_GET, [
            'orderby' => $column,
            'order' => $order
        ]);

        return add_query_arg($params);
    }

    private function get_column_sort_class($column, $filters)
    {
        $classes = ['sortable'];

        if ($filters['orderby'] === $column) {
            $classes[] = $filters['order'] === 'asc' ? 'asc' : 'desc';
            $classes[] = 'sorted';
        }

        return implode(' ', $classes);
    }

    private function format_date($date)
    {
        return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($date));
    }

    private function time_diff($date)
    {
        return human_time_diff(strtotime($date), current_time('timestamp')) . ' ' . __('ago', 'free_mail_smtp');
    }

    private function render_pagination($total_items, $total_pages, $current_page)
    {
        $pagination = paginate_links([
            'base' => add_query_arg('paged', '%#%'),
            'format' => '',
            'prev_text' => '&laquo;',
            'next_text' => '&raquo;',
            'total' => $total_pages,
            'current' => $current_page,
            'type' => 'array'
        ]);

        if ($pagination) {
            echo '<div class="tablenav-pages">';
            echo '<span class="displaying-num">' . sprintf(
                _n('%s item', '%s items', $total_items, 'free_mail_smtp'),
                number_format_i18n($total_items)
            ) . '</span>';
            echo '<span class="pagination-links">' . implode("\n", $pagination) . '</span>';
            echo '</div>';
        }
    }
}
