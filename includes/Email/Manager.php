<?php
namespace FreeMailSMTP\Email;

class Manager {
    private $providers = [];
    private $providersList = [];

    
    public function __construct() {
        add_action('init', [$this, 'init_providers']);
        $this->providersList = include __DIR__ . '/../../config/providers-list.php';
    }
    
    public function init_providers() {
        $provider_configs = get_option('free_mail_smtp_providers', []);
         
        foreach ($provider_configs as $config) {
            if (!empty($config['provider']) && !empty($config['id']) && !empty($config['priority'])) {
                $provider_class = '\\FreeMailSMTP\\Providers\\' . $this->providersList[$config['provider']];
                if (class_exists($provider_class)) {
                    $conn_repo = new \FreeMailSMTP\Connections\ConnectionRepository();
                    $conn = $conn_repo->get_connection($config['id']);
                    $config_keys = ($conn && isset($conn->connection_data)) ? $conn->connection_data : [];
                    
                    $instance = new $provider_class($config_keys);
                    $this->providers[] = [
                        'instance' => $instance,
                        'priority' => $config['priority'],
                        'name' => $config['provider']
                    ];
                }
            }
        }
        
        usort($this->providers, function($a, $b) {
            return $a['priority'] - $b['priority'];
        });
    }
    
    public function send_mail($null, $args) {
        $error_messages = [];
            $email_data = $this->prepare_email_data($args);
        foreach ($this->providers as $provider) {
            try {
                $result = $provider['instance']->send($email_data);
                
                $this->log_email($email_data, $result, $provider['name'], 'sent');
                return true;
                
            } catch (\Exception $e) {
                $error_messages[] = [
                    'provider' => $provider['name'],
                    'error' => $e->getMessage()
                ];
                error_log("Email sending failed for provider {$provider['name']}: {$e->getMessage()}");
                $this->log_email($email_data ?? [], null, $provider['name'], 'failed', $e->getMessage());
                continue;
            }
        }
        
        $this->log_provider_failures($error_messages);
        return false;
    }
    
    private function prepare_email_data($args) {
        $to = is_array($args['to']) ? $args['to'] : [$args['to']];
        $headers = $this->parse_headers($args['headers']);
        return [
            'to' => $to,
            'subject' => $args['subject'],
            'message' => $args['message'],
            'from_email' => get_option('free_mail_smtp_from_email') ?? $headers['from_email'],
            'from_name' => get_option('free_mail_smtp_from_name') ?? $headers['from_name'],
            'reply_to' => $headers['reply_to'] ?? '',
            'cc' => $headers['cc'] ?? [],
            'bcc' => $headers['bcc'] ?? [],
            'attachments' => $this->prepare_attachments($args['attachments'])
        ];
    }
    
    private function parse_headers($headers) {
        $parsed_headers = [];
        if (empty($headers)) {
            return $parsed_headers;
        }

        if (!is_array($headers)) {
            $headers = explode("\n", str_replace("\r\n", "\n", $headers));
        }
        foreach ($headers as $header) {
            if (strpos($header, ':') === false) {
                continue;
            }
            list($name, $value) = explode(':', trim($header), 2);
            $name = strtolower(trim($name));
            $value = trim($value);
            
            switch ($name) {
                case 'from':
                    $parsed_headers['from_email'] = $this->extract_email($value);
                    $parsed_headers['from_name'] = $this->extract_name($value);
                    break;
                case 'reply-to':
                    $parsed_headers['reply_to'] = $this->extract_email($value);
                    break;
                case 'cc':
                    $parsed_headers['cc'] = $this->extract_addresses($value);
                    break;
                case 'bcc':
                    $parsed_headers['bcc'] = $this->extract_addresses($value);
                    break;
                default:
                    // Ignore other headers
                    break;
            }
        }

        return $parsed_headers;
    }
    
    private function prepare_attachments($attachments) {
        if (empty($attachments)) {
            return [];
        }
    
        if (!is_array($attachments)) {
            $attachments = [$attachments];
        }
    
        $prepared_attachments = [];

        foreach ($attachments as $attachment) {
            if (file_exists($attachment)) {
                $prepared_attachments[] = [
                    'path' => $attachment,
                    'name' => basename($attachment),
                    'size' => filesize($attachment),
                    'type' => mime_content_type($attachment),
                    'content' => base64_encode(file_get_contents($attachment)) // Encode content to base64
                ];
            }
        }
    
        return $prepared_attachments;
    }

    private function log_email($data, $result, $provider, $status, $error = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'email_log';
        
        foreach ($data['to'] as $to) {
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
                ['%s', '%s', '%s', '%s', '%s', '%s', '%s']
            );
        }
    }
    
    private function log_provider_failures($errors) {
        error_log('Email sending failed for all providers:');
        foreach ($errors as $error) {
            error_log("Provider {$error['provider']}: {$error['error']}");
        }
    }

    private function extract_email($string) {
        if (preg_match('/<(.+)>/', $string, $matches)) {
            return $matches[1];
        }
        return $string;
    }

    private function extract_name($string) {
        if (preg_match('/(.+)<.+>/', $string, $matches)) {
            return trim($matches[1]);
        }
        return '';
    }

    private function extract_addresses($string) {
        $addresses = explode(',', $string);
        return array_map('trim', $addresses);
    }
}