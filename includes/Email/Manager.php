<?php
namespace TurboSMTP\FreeMailSMTP\Email;
if ( ! defined( 'ABSPATH' ) ) exit;

use TurboSMTP\FreeMailSMTP\Providers\ProviderFactory;
use TurboSMTP\FreeMailSMTP\Email\EmailFormatterService;
use TurboSMTP\FreeMailSMTP\Email\EmailRoutingService;
use TurboSMTP\FreeMailSMTP\Core\WPMailCaller;
use TurboSMTP\FreeMailSMTP\Providers\PhpMailerProvider;

/**
 * Class Manager
 * 
 * Manages email sending operations through multiple providers with routing capabilities.
 * Handles provider initialization, email routing, and sending attempts with fallback support.
 * 
 * @package TurboSMTP\FreeMailSMTP\Email
 */
class Manager {
    private $connections = [];
    private $providerFactory;
    private $emailFormatterService;
    private $emailRoutingService;
    private $wpMailCaller;

    /**
     * Initialize the Manager with required services and hook into WordPress.
     */
    public function __construct() {
        add_action('init', [$this, 'init_providers']);
        $this->providerFactory = new ProviderFactory();
        $this->emailFormatterService = new EmailFormatterService();
        $this->emailRoutingService = new EmailRoutingService();
        $this->wpMailCaller = new WPMailCaller();
    }
    
    /**
     * Initialize email providers from database connections.
     * Loads and sorts providers based on their priority.
     * 
     * @return void
     */
    public function init_providers() {
        $conn_repo = new \TurboSMTP\FreeMailSMTP\DB\ConnectionRepository();
        $provider_configs = $conn_repo->get_all_connections();
        foreach ($provider_configs as $config) {
            if (!empty($config->provider) && !empty($config->id) && !empty($config->priority)) {
                $instance = $this->providerFactory->get_provider_class($config);
                $this->connections[] = [
                    'instance' => $instance,
                    'priority' => $config->priority,
                    'name' => $config->provider,
                    'connection_id' => $config->connection_id
                ];
            }
        }
        usort($this->connections, function($a, $b) {
            return $a['priority'] - $b['priority'];
        });
    }
    
    /**
     * Handle email sending through configured providers.
     * 
     * @param mixed $null     WordPress hook parameter
     * @param array $args     Email arguments containing to, subject, message, etc.
     * @return bool           True if email was sent successfully, false otherwise
     */
    public function send_mail($null, $args) {
        $error_messages = [];
        $email_data = $this->emailFormatterService->format($args);
        $source_plugin = $this->wpMailCaller->getSourcePluginName();
        $email_data['source_app'] = $source_plugin;

        $matching_conditions = $this->emailRoutingService->getRoutingConditionIfExists($email_data);
        $routing_providers = $this->get_routing_providers($matching_conditions);
        if ($this->trySendMail($routing_providers, $email_data, $error_messages)) {
            return true;
        }

        $remaining_providers = $this->get_remaining_providers($matching_conditions);
        
        if ($this->trySendMail($remaining_providers, $email_data, $error_messages)) {
            return true;
        }

        if(get_option('free_mail_smtp_fallback_to_wp_mail', true)) {
            return $this->fallback_to_wp_mail($args);
        }
        return false;
    }

    private function fallback_to_wp_mail($args) {
        $phpmailer = new PhpMailerProvider();
        $result = $phpmailer->send($args);
        $current_email_data = $this->emailFormatterService->format($args);

        if($result) {
            $this->log_email($current_email_data, $result, 'phpmailer', 'sent');
            return true;
        }


        $this->log_email($current_email_data ?? [], null, 'phpmailer', 'failed', 'All providers failed to send email');
        return false;
    }
    /**
     * Get providers based on matching routing conditions.
     * 
     * @param array $matching_conditions Array of routing conditions
     * @return array Array of providers with their routing configurations
     */
    private function get_routing_providers($matching_conditions) {
        $routing_providers = [];
        if (!empty($matching_conditions)) {
            foreach ($matching_conditions as $condition) {
                $key = array_search($condition->connection_id, array_column($this->connections, 'connection_id'));
                if ($key !== false) {
                    $routing_providers[] = [
                        'provider' => $this->connections[$key],
                        'overwrite_sender' => $condition->overwrite_sender,
                        'overwrite_connection' => $condition->overwrite_connection,
                        'forced_senderemail' => $condition->forced_senderemail ?? '',
                        'forced_sendername' => $condition->forced_sendername ?? ''
                    ];
                }
            }
            
            usort($routing_providers, function($a, $b) {
                return $a['provider']['priority'] - $b['provider']['priority'];
            });
        }
        return $routing_providers;
    }

    /**
     * Get remaining providers that aren't included in routing conditions.
     * 
     * @param array $matching_conditions Array of routing conditions
     * @return array Array of remaining providers
     */
    private function get_remaining_providers($matching_conditions) {
        $condition_ids = array_map(function($condition) {
            return $condition->connection_id;
        }, $matching_conditions);

        return array_filter($this->connections, function($provider) use ($condition_ids) {
            return !in_array($provider['connection_id'], $condition_ids);
        });
    }

    /**
     * Attempt to send email through a list of providers.
     * 
     * @param array $providers      Array of providers to try
     * @param array $email_data     Formatted email data
     * @param array $error_messages Reference to array storing error messages
     * @return bool                 True if email was sent successfully, false otherwise
     */
    private function trySendMail($providers, $email_data, &$error_messages) {
        foreach ($providers as $provider_data) {
            try {
                $current_email_data = $email_data;
                if (isset($provider_data['overwrite_sender'])) {
                    $provider = $provider_data['provider'];
                    
                    if ($provider_data['overwrite_sender']) {
                        if (!empty($provider_data['forced_senderemail'])) {
                            $current_email_data['from_email'] = $provider_data['forced_senderemail'];
                        }
                        if (!empty($provider_data['forced_sendername'])) {
                            $current_email_data['from_name'] = $provider_data['forced_sendername'];
                        }
                    }
                    if (!$provider_data['overwrite_connection']) {
                        try {
                            $result = $provider['instance']->send($current_email_data);
                            $this->log_email($current_email_data, $result, $provider['name'], 'sent');
                            return true;
                        } catch (\Exception $e) {
                            continue;
                        }
                    }
                } else {
                    $provider = $provider_data;
                }

                $result = $provider['instance']->send($current_email_data);
                $this->log_email($current_email_data, $result, $provider['name'], 'sent');
                return true;
            } catch (\Exception $e) {
                $provider_name = isset($provider_data['provider']) ? $provider_data['provider']['name'] : $provider_data['name'];
                $error_messages[] = [
                    'provider' => $provider_name,
                    'error' => $e->getMessage()
                ];
                $this->log_email($current_email_data ?? [], null, $provider_name, 'failed', $e->getMessage());
            }
        }
        return false;
    }
    
    /**
     * Log email sending attempt to database.
     * 
     * @param array  $data     Email data
     * @param array  $result   Provider response
     * @param string $provider Provider name
     * @param string $status   Status of the email (sent/failed)
     * @param string $error    Error message if any
     * @return void
     */
    private function log_email($data, $result, $provider, $status, $error = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'free_mail_smtp_email_log';
        
        foreach ($data['to'] as $to) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
            $wpdb->insert(
                $table_name,
                [
                    'provider' => $provider,
                    'to_email' => $to,
                    'subject' => $data['subject'] ?? '',
                    'status' => $status,
                    'message_id' => $result['message_id'] ?? null,
                    'error_message' => $error,
                    'sent_at' => current_time('mysql')
                ],
                ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
            );
        }
    }
}