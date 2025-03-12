<?php

namespace FreeMailSMTP\Admin;

use FreeMailSMTP\DB\EmailLogRepository;

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
    private $log_repository;

    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        $this->log_repository = new EmailLogRepository();
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
            'ajaxUrl' => esc_url(admin_url('admin-ajax.php')),
            'nonce' => wp_create_nonce('free_mail_smtp_logs'),
            'i18n' => [
                'confirmDelete' => __('Are you sure you want to delete the selected logs?', 'free-mail-smtp'),
                'noLogsSelected' => __('Please select at least one log to delete.', 'free-mail-smtp'),
                'deleted' => __('Selected logs have been deleted.', 'free-mail-smtp'),
                'error' => __('An error occurred. Please try again.', 'free-mail-smtp')
            ]
        ]);
    }

    public function render()
    {
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['retention_duration_setting'])) {
            if (isset($_POST['free_mail_smtp_retention_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['free_mail_smtp_retention_nonce'])), 'free_mail_smtp_update_retention')) {
                update_option('free_mail_smtp_retention_duration', sanitize_text_field(wp_unslash($_POST['retention_duration_setting'])));
            }
        }
        $current_retention = get_option('free_mail_smtp_retention_duration', 'forever');

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

            <nav class="free-mail-smtp-nav-tab-wrapper">
                <a href="<?php echo esc_url(admin_url('admin.php?page=free_mail_smtp-providers')); ?>" class="free-mail-smtp-nav-tab">Providers</a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=free_mail_smtp-logs')); ?>" class="free-mail-smtp-nav-tab free-mail-smtp-nav-tab-active">Email Logs</a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=free_mail_smtp-analytics')); ?>" class="free-mail-smtp-nav-tab">Providers Logs</a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=free_mail_smtp-email-router')); ?>" class="free-mail-smtp-nav-tab">Email Router</a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=free_mail_smtp-settings')); ?>" class="free-mail-smtp-nav-tab">Settings</a>
            </nav>
                <div class="logs-retention-settings">
                <h2><?php esc_html_e('Logs Retention Settings', 'free-mail-smtp'); ?></h2>
                <p class="retention-description">
                    <?php esc_html_e('Select how long you want to keep your email logs in the database. Logs older than the selected duration will be automatically deleted.', 'free-mail-smtp'); ?>
                </p>
                <form method="post">
                    <?php wp_nonce_field('free_mail_smtp_update_retention', 'free_mail_smtp_retention_nonce'); ?>
                    <select name="retention_duration_setting">
                        <option value="forever" <?php selected($current_retention, 'forever'); ?>>
                            <?php esc_html_e('Forever', 'free-mail-smtp'); ?>
                        </option>
                        <option value="1_week" <?php selected($current_retention, '1_week'); ?>>
                            <?php esc_html_e('1 Week', 'free-mail-smtp'); ?>
                        </option>
                        <option value="1_month" <?php selected($current_retention, '1_month'); ?>>
                            <?php esc_html_e('1 Month', 'free-mail-smtp'); ?>
                        </option>
                        <option value="1_year" <?php selected($current_retention, '1_year'); ?>>
                            <?php esc_html_e('1 Year', 'free-mail-smtp'); ?>
                        </option>
                    </select>
                    <input type="submit" class="button" value="<?php esc_html_e('Update Retention Setting', 'free-mail-smtp'); ?>">
                </form>
            </div>
            <!-- Filters -->
            <div class="tablenav top">
                <form method="get" class="email-filters">
                    <input type="hidden" name="page" value="free_mail_smtp-logs">
                    <div class="alignleft actions filters">
                        <select name="provider" class="provider-filter">
                            <option value=""><?php echo esc_html__('All Providers', 'free-mail-smtp'); ?></option>
                            <?php foreach ($this->get_providers() as $key => $name): ?>
                                <option value="<?php echo esc_attr($key); ?>"
                                    <?php selected(esc_attr($filters['provider']), $key); ?>>
                                    <?php echo esc_html($name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <select name="status" class="status-filter">
                            <option value=""><?php echo esc_html__('All Statuses', 'free-mail-smtp'); ?></option>
                            <?php foreach (array_keys($this->statuses) as $status): ?>
                                <option value="<?php echo esc_attr($status); ?>"
                                    <?php selected(esc_attr($filters['status']), $status); ?>>
                                    <?php echo esc_html(ucfirst($status)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <input type="date"
                            name="date_from"
                            value="<?php echo esc_attr($filters['date_from']); ?>"
                            class="date-picker"
                            placeholder="<?php echo esc_attr__('From Date', 'free-mail-smtp'); ?>">

                        <input type="date"
                            name="date_to"
                            value="<?php echo esc_attr($filters['date_to']); ?>"
                            class="date-picker"
                            placeholder="<?php echo esc_attr__('To Date', 'free-mail-smtp'); ?>">

                        <input type="search"
                            name="search"
                            value="<?php echo esc_attr($filters['search']); ?>"
                            class="search-input"
                            placeholder="<?php echo esc_attr__('Search emails...', 'free-mail-smtp'); ?>">

                        <input type="submit"
                            class="button apply-filter"
                            value="<?php echo esc_attr__('Filter', 'free-mail-smtp'); ?>">
                    </div>

                    <div class="alignright">
                        <span class="displaying-num">
                            <?php 
                            echo esc_html(sprintf(
                            /* translators: %s: number of items */
                                _n('%s item', '%s items', $total_items, 'free-mail-smtp'),
                                number_format_i18n($total_items)
                            )); ?>
                        </span>
                    </div>
                </form>
            </div>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <td class="manage-column column-cb check-column">
                            <input type="checkbox" id="cb-select-all-1">
                        </td>
                        <?php foreach ($this->get_columns() as $key => $label): ?>
                            <th scope="col"
                                class="manage-column column-<?php echo esc_attr($key); ?> <?php echo esc_attr($this->get_column_sort_class($key, $filters)); ?>">
                                <a href="<?php echo esc_url($this->get_sort_url($key)); ?>">
                                    <span><?php echo esc_html($label); ?></span>
                                </a>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr class="no-items">
                            <td class="colspanchange" colspan="<?php echo count($this->get_columns()) + 1; ?>">
                                <?php esc_html_e('No logs found.', 'free-mail-smtp'); ?>
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
                                    <?php echo esc_html($this->format_date($log->sent_at)); ?> <br>
                                    <small><?php echo esc_html($this->time_diff($log->sent_at)); ?></small>
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

            <div class="tablenav bottom">
                <?php $this->render_pagination($total_items, $total_pages, $filters['paged']); ?>
            </div>
        </div>

        <?php $this->render_modal_template(); ?>
    <?php
    }

    private function render_modal_template()
    {
    ?>
        <script type="text/template" id="tmpl-log-details">
            <div class="log-details-content">
                <h2><?php esc_html_e('Email Log Details', 'free-mail-smtp'); ?></h2>
                <div class="log-details-grid">
                    <div class="log-section">
                        <h3><?php esc_html_e('Basic Information', 'free-mail-smtp'); ?></h3>
                        <table class="log-info">
                            <tr>
                                <th><?php esc_html_e('Message ID', 'free-mail-smtp'); ?></th>
                                <td>{{ data.message_id }}</td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e('Status', 'free-mail-smtp'); ?></th>
                                <td>
                                    <span class="status-badge status-{{ data.status }}">
                                        {{ data.status }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e('Provider', 'free-mail-smtp'); ?></th>
                                <td>{{ data.provider }}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="log-section">
                        <h3><?php esc_html_e('Timeline', 'free-mail-smtp'); ?></h3>
                        <div class="log-timeline">
                            <div class="timeline-item">
                                <span class="time">{{ data.sent_at }}</span>
                                <span class="event"><?php esc_html_e('Sent', 'free-mail-smtp'); ?></span>
                            </div>
                            <# if (data.error_message) { #>
                                <div class="timeline-item">
                                    <span class="time">{{ data.error_message }}</span>
                                    <span class="event"><?php esc_html_e('Details', 'free-mail-smtp'); ?></span>
                                </div>
                            <# } #>
                        </div>
                    </div>
                    
                    <# if (data.error_message) { #>
                        <div class="log-section error-section">
                            <h3><?php esc_html_e('Error Details', 'free-mail-smtp'); ?></h3>
                            <div class="error-message">
                                {{ data.error_message }}
                            </div>
                        </div>
                    <# } #>
                    
                    <div class="log-section">
                        <h3><?php esc_html_e('Email Content', 'free-mail-smtp'); ?></h3>
                        <table class="log-info">
                            <tr>
                                <th><?php esc_html_e('To', 'free-mail-smtp'); ?></th>
                                <td>{{ data.to_email }}</td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e('Subject', 'free-mail-smtp'); ?></th>
                                <td>{{ data.subject }}</td>
                            </tr>
                            <# if (data.headers) { #>
                                <tr>
                                    <th><?php esc_html_e('Headers', 'free-mail-smtp'); ?></th>
                                    <td><pre>{{ data.headers }}</pre></td>
                                </tr>
                            <# } #>
                            <tr>
                                <th><?php esc_html_e('Body', 'free-mail-smtp'); ?></th>
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
            'sent_at' => __('Date', 'free-mail-smtp'),
            'provider' => __('Provider', 'free-mail-smtp'),
            'to_email' => __('To', 'free-mail-smtp'),
            'subject' => __('Subject', 'free-mail-smtp'),
            'status' => __('Status', 'free-mail-smtp'),
            'details' => __('Details', 'free-mail-smtp')
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
            'paged'     => isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1,
            'provider'  => isset($_GET['provider']) ? sanitize_text_field($_GET['provider']) : '',
            'status'    => isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '',
            'search'    => isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '',
            'date_from' => isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '',
            'date_to'   => isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '',
            'orderby'   => isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'sent_at',
            'order'     => isset($_GET['order']) && in_array(strtolower($_GET['order']), ['asc', 'desc']) ? strtolower($_GET['order']) : 'desc',
        ];
    }

    private function get_logs($filters)
    {
        return $this->log_repository->get_logs($filters);
    }

    private function get_total_logs($filters)
    {
        return $this->log_repository->get_total_logs();
    }

    private function get_sort_url($column)
    {
        $current_orderby = isset($_GET['orderby']) ? sanitize_text_field(wp_unslash($_GET['orderby'])) : 'sent_at';
        $current_order = isset($_GET['order']) ? sanitize_text_field(wp_unslash($_GET['order'])) : 'desc';

        $order = ($current_orderby === $column && $current_order === 'desc') ? 'asc' : 'desc';

        $params = array_merge(isset($_GET) ? $_GET : [], [
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
        return human_time_diff(strtotime($date), current_time('timestamp')) . ' ' . __('ago', 'free-mail-smtp');
    }

    private function render_pagination($total_items, $total_pages, $current_page) 
    {
        $pagination = paginate_links([
            'base' => esc_url(add_query_arg('paged', '%#%')),
            'format' => '',
            'prev_text' => '&laquo;',
            'next_text' => '&raquo;',
            'total' => absint($total_pages),
            'current' => absint($current_page),
            'type' => 'array'
        ]);

        if ($pagination) {
            echo '<div class="tablenav-pages">';
            echo '<span class="displaying-num">' . esc_html(sprintf(
                /* translators: %s: number of items */
                _n('%s item', '%s items', $total_items, 'free-mail-smtp'),
                number_format_i18n($total_items)
            )) . '</span>';
            
            echo '<span class="pagination-links">';
            echo wp_kses_post(implode("\n", array_map('wp_kses_post', $pagination)));
            echo '</span>';
            
            echo '</div>';
        }
    }
}
