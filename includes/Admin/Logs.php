<?php
namespace TurboSMTP\FreeMailSMTP\Admin;
if ( ! defined( 'ABSPATH' ) ) exit;

use TurboSMTP\FreeMailSMTP\DB\EmailLogRepository;

class Logs
{
    private $per_page = 20;
    private $providersList = [];
    private $statuses = [
        'sent' => '#3498db',
        'failed' => '#e74c3c'
    ];
    private $log_repository;

    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        $this->log_repository = new EmailLogRepository();
        $this->providersList = include __DIR__ . '/../../config/providers-list.php';

    }

    public function enqueue_scripts($hook)
    {
        $expected_hook = 'free-mail-smtp_page_free-mail-smtp-logs';
        if ($hook !== $expected_hook) {
            return;
        }

        wp_enqueue_script(
            'free-mail-smtp-logs',
            plugins_url('assets/js/logs.js', FREE_MAIL_SMTP_FILE),
            ['jquery'],
            FREE_MAIL_SMTP_VERSION,
            true
        );

        wp_enqueue_style(
            'free-mail-smtp-logs',
            plugins_url('assets/css/logs.css', FREE_MAIL_SMTP_FILE),
            [],
            FREE_MAIL_SMTP_VERSION
        );

        wp_localize_script('free-mail-smtp-logs', 'FreeMailSMTPLogs', [
            'ajaxUrl' => esc_url(admin_url('admin-ajax.php')),
            'nonce' => wp_create_nonce('free_mail_smtp_logs'),
            'i18n' => [
                'error' => __('An error occurred. Please try again.', 'free-mail-smtp')
            ]
        ]);
    }

    public function render()
    {
        if (isset($_POST['retention_duration_setting']) && 
            isset($_POST['free_mail_smtp_retention_nonce']) && 
            wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['free_mail_smtp_retention_nonce'])), 'free_mail_smtp_update_retention')) {
            
            update_option('free_mail_smtp_retention_duration', sanitize_text_field(wp_unslash($_POST['retention_duration_setting'])));
        }
        
        if (isset($_POST['filter_action']) && 
            isset($_POST['free_mail_smtp_logs_filter_nonce']) && 
            wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['free_mail_smtp_logs_filter_nonce'])), 'free_mail_smtp_logs_filter')) {
            
            $filter_data = [
                'provider'  => isset($_POST['provider']) ? sanitize_text_field(wp_unslash($_POST['provider'])) : '',
                'status'    => isset($_POST['status']) ? sanitize_text_field(wp_unslash($_POST['status'])) : '',
                'search'    => isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : '',
                'date_from' => isset($_POST['date_from']) ? sanitize_text_field(wp_unslash($_POST['date_from'])) : '',
                'date_to'   => isset($_POST['date_to']) ? sanitize_text_field(wp_unslash($_POST['date_to'])) : '',
                'orderby'   => isset($_POST['orderby']) ? sanitize_text_field(wp_unslash($_POST['orderby'])) : 'sent_at',
                'order'     => isset($_POST['order']) && in_array(strtolower(wp_unslash($_POST['order'])), ['asc', 'desc'], true) 
                            ? strtolower(sanitize_text_field(wp_unslash($_POST['order']))) 
                            : 'desc',
            ];
            
            update_user_meta(get_current_user_id(), 'free_mail_smtp_log_filters', $filter_data);
        }
        
        $current_retention = get_option('free_mail_smtp_retention_duration', 'forever');
        $filters = $this->get_filters();
        $logs = $this->get_logs($filters);
        $total_items = $this->get_total_logs($filters);
        $total_pages = ceil($total_items / $this->per_page);

?>

        <div class="wrap free_mail_smtp-wrap">
            <div class="plugin-header">
                <img src="<?php echo esc_url(plugins_url('assets/img/icon-svg.svg', FREE_MAIL_SMTP_FILE)); ?>" alt="Free Mail SMTP" class="plugin-logo">
                <h1>FREE MAIL <span>SMTP</span></h1>
            </div>

            <p class="description">Setup custom SMTP or popular Providers to improve your WordPress email deliverability.</p>

            <nav class="free-mail-smtp-nav-tab-wrapper">
                <a href="<?php echo esc_url(admin_url('admin.php?page=free-mail-smtp-providers')); ?>" class="free-mail-smtp-nav-tab">Providers</a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=free_mail_smtp_logs')); ?>" class="free-mail-smtp-nav-tab free-mail-smtp-nav-tab-active">Email Logs</a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=free-mail-smtp-analytics')); ?>" class="free-mail-smtp-nav-tab">Providers Logs</a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=free-mail-smtp-email-router')); ?>" class="free-mail-smtp-nav-tab">Email Router</a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=free-mail-smtp-settings')); ?>" class="free-mail-smtp-nav-tab">Settings</a>
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
                <form method="post" class="email-filters">
                    <input type="hidden" name="page" value="free_mail_smtp-logs">
                    <input type="hidden" name="filter_action" value="filter_logs">
                    <input type="hidden" name="paged" value="<?php echo isset($filters['paged']) ? absint($filters['paged']) : 1; ?>">
                    <input type="hidden" name="orderby" value="<?php echo esc_attr($filters['orderby']); ?>">
                    <input type="hidden" name="order" value="<?php echo esc_attr($filters['order']); ?>">
                    <?php wp_nonce_field('free_mail_smtp_logs_filter', 'free_mail_smtp_logs_filter_nonce'); ?>
                    <div class="alignleft actions filters">
                        <select name="provider" class="provider-filter">
                            <option value=""><?php echo esc_html__('All Providers', 'free-mail-smtp'); ?></option>
                            <?php foreach ($this->get_providers() as $key => $provider): ?>
                                <option value="<?php echo esc_attr($key); ?>"
                                    <?php selected(esc_attr($filters['provider']), $key); ?>>
                                    <?php echo esc_html($provider); ?>
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
                            
                        <button type="button" class="button reset-filter">
                            <?php echo esc_html__('Reset Filters', 'free-mail-smtp'); ?>
                        </button>
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
                        <?php foreach ($this->get_columns() as $key => $label): ?>
                            <th scope="col"
                                class="manage-column column-<?php echo esc_attr($key); ?> <?php echo esc_attr($this->get_column_sort_class($key, $filters)); ?>">
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
                            <td class="colspanchange" colspan="<?php echo count($this->get_columns()); ?>">
                                <?php esc_html_e('No logs found.', 'free-mail-smtp'); ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
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
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>

                <tfoot>
                    <tr>
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
        $providersArray = [];
        foreach ($this->providersList as $key => $provider) {
            $providersArray[$key] = $provider['label'];
        }
        $providersArray['phpmailer'] = __('Phpmailer', 'free-mail-smtp');
        return $providersArray;
    }

    private function get_filters()
    {
        $defaults = [
            'paged'     => 1,
            'provider'  => '',
            'status'    => '',
            'search'    => '',
            'date_from' => '',
            'date_to'   => '',
            'orderby'   => 'sent_at',
            'order'     => 'desc',
        ];
        // phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
        if (isset($_POST['free_mail_smtp_logs_filter_nonce']) && 
            wp_verify_nonce($_POST['free_mail_smtp_logs_filter_nonce'], 'free_mail_smtp_logs_filter')) {
            
            $filter_data = [
                'paged'     => isset($_POST['paged']) ? max(1, absint($_POST['paged'])) : $defaults['paged'],
                'provider'  => isset($_POST['provider']) ? sanitize_text_field(wp_unslash($_POST['provider'])) : $defaults['provider'],
                'status'    => isset($_POST['status']) ? sanitize_text_field(wp_unslash($_POST['status'])) : $defaults['status'],
                'search'    => isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : $defaults['search'],
                'date_from' => isset($_POST['date_from']) ? sanitize_text_field(wp_unslash($_POST['date_from'])) : $defaults['date_from'],
                'date_to'   => isset($_POST['date_to']) ? sanitize_text_field(wp_unslash($_POST['date_to'])) : $defaults['date_to'],
                'orderby'   => isset($_POST['orderby']) ? sanitize_text_field(wp_unslash($_POST['orderby'])) : $defaults['orderby'],
                'order'     => isset($_POST['order']) && in_array(strtolower($_POST['order']), ['asc', 'desc'], true) 
                            ? strtolower(sanitize_text_field(wp_unslash($_POST['order']))) 
                            : $defaults['order'],
            ];
            
            $is_pagination_or_sort_only = isset($_POST['filter_action']) && 
                                          $_POST['filter_action'] === 'filter_logs' &&
                                          isset($_POST['paged']);
                                          
            $is_reset = isset($_POST['filter_action']) && 
                        $_POST['filter_action'] === 'filter_logs' &&
                        empty($_POST['provider']) && 
                        empty($_POST['status']) && 
                        empty($_POST['search']) && 
                        empty($_POST['date_from']) && 
                        empty($_POST['date_to']) &&
                        $_POST['paged'] == 1 &&
                        $_POST['orderby'] === 'sent_at' && 
                        $_POST['order'] === 'desc';
            
            if ($is_reset) {
                delete_user_meta(get_current_user_id(), 'free_mail_smtp_log_filters');
                return $defaults;
            }
            
            if (!$is_pagination_or_sort_only || isset($_POST['provider']) || isset($_POST['status']) || 
                !empty($_POST['search']) || !empty($_POST['date_from']) || !empty($_POST['date_to'])) {
                
                $filter_save = $filter_data;
                $filter_save['paged'] = 1; 
                update_user_meta(get_current_user_id(), 'free_mail_smtp_log_filters', $filter_save);
            } else {
                $saved_filters = get_user_meta(get_current_user_id(), 'free_mail_smtp_log_filters', true);
                if (!empty($saved_filters) && is_array($saved_filters)) {
                    $filter_data = array_merge($saved_filters, ['paged' => $filter_data['paged']]);
                }
            }
            
            return $filter_data;
        }
        
        $saved_filters = get_user_meta(get_current_user_id(), 'free_mail_smtp_log_filters', true);
        if (!empty($saved_filters) && is_array($saved_filters)) {
            return array_merge($defaults, $saved_filters);
        }
        
        return $defaults;
    }

    private function get_logs($filters)
    {
        return $this->log_repository->get_logs($filters);
    }

    private function get_total_logs()
    {
        return $this->log_repository->get_total_logs();
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
        if ($total_pages <= 1) {
            return;
        }
        
        echo '<div class="tablenav-pages">';
        
        echo '<span class="displaying-num">' . esc_html(sprintf(
            /* translators: %s: number of items */
            _n('%s item', '%s items', $total_items, 'free-mail-smtp'),
            number_format_i18n($total_items)
        )) . '</span>';
        
        echo '<span class="pagination-links">';
        
        $first_page_disabled = $current_page <= 1 ? 'disabled' : '';
        echo '<button type="button" class="first-page button pagination-button ' . esc_attr($first_page_disabled) . '" data-page="1" aria-label="' . esc_attr__('Go to the first page', 'free-mail-smtp') . '">&laquo;</button>';
        
        $prev_page = max(1, $current_page - 1);
        $prev_page_disabled = $current_page <= 1 ? 'disabled' : '';
        echo '<button type="button" class="prev-page button pagination-button ' . esc_attr($prev_page_disabled) . '" data-page="' . esc_attr($prev_page) . '" aria-label="' . esc_attr__('Go to the previous page', 'free-mail-smtp') . '">&lsaquo;</button>';
        
        echo '<span class="paging-input">';
        echo '<span class="tablenav-paging-text">' . absint($current_page) . ' ' . esc_html__('of', 'free-mail-smtp') . ' <span class="total-pages">' . absint($total_pages) . '</span>';
        echo '</span>';
        
        $next_page = min($total_pages, $current_page + 1);
        $next_page_disabled = $current_page >= $total_pages ? 'disabled' : '';
        echo '<button type="button" class="next-page button pagination-button ' . esc_attr($next_page_disabled) . '" data-page="' . esc_attr($next_page) . '" aria-label="' . esc_attr__('Go to the next page', 'free-mail-smtp') . '">&rsaquo;</button>';
        
        $last_page_disabled = $current_page >= $total_pages ? 'disabled' : '';
        echo '<button type="button" class="last-page button pagination-button ' . esc_attr($last_page_disabled) . '" data-page="' . esc_attr($total_pages) . '" aria-label="' . esc_attr__('Go to the last page', 'free-mail-smtp') . '">&raquo;</button>';
        
        echo '</span>';
        echo '</div>';
    }
}
