<?php
namespace FreeMailSMTP\Providers;

class Sendgrid extends BaseProvider {
    protected function get_api_url() {
        return 'https://api.sendgrid.com/v3/';
    }
    
    protected function get_headers() {
        return [
            'Authorization' => 'Bearer ' .$this->config_keys['api_key'],
            'Content-Type' => 'application/json'
        ];
    }
    
    public function send($data) {
        $endpoint = 'mail/send';
        
        $payload = [
            'personalizations' => [
                [
                    'to' => array_map(function($email) {
                        return ['email' => $email];
                    }, $data['to'])
                ]
            ],
            'from' => [
                'email' => $data['from_email'],
                'name' => $data['from_name']
            ],
            'subject' => $data['subject'],
            'content' => [
                [
                    'type' => 'text/html',
                    'value' => $data['message']
                ]
            ]
        ];
        
        // Add CC recipients if any
        if (!empty($data['cc'])) {
            $payload['personalizations'][0]['cc'] = array_map(function($email) {
                return ['email' => $email];
            }, $data['cc']);
        }
        
        // Add BCC recipients if any
        if (!empty($data['bcc'])) {
            $payload['personalizations'][0]['bcc'] = array_map(function($email) {
                return ['email' => $email];
            }, $data['bcc']);
        }
        
        // Add reply-to if set
        if (!empty($data['reply_to'])) {
            $payload['reply_to'] = [
                'email' => $data['reply_to']
            ];
        }
        
        // Add attachments if any
        if (!empty($data['attachments'])) {
            $payload['attachments'] = array_map(function($attachment) {
                return [
                    'content' => $attachment['content'],
                    'filename' => $attachment['filename'],
                    'type' => $attachment['type'],
                    'disposition' => 'attachment'
                ];
            }, $data['attachments']);
        }
        
        $response = $this->request($endpoint, $payload);
        
        return [
            'message_id' => 'sendgrid_' . uniqid(),
            'provider_response' => $response
        ];
    }
    
    protected function prepare_request_body($data) {
        return json_encode($data);
    }
    
    protected function get_error_message($body, $code) {
        $data = json_decode($body, true);
        
        if (isset($data['errors']) && is_array($data['errors'])) {
            return implode(', ', array_column($data['errors'], 'message'));
        }
        
        return "SendGrid API error (HTTP $code)";
    }
    
    public function test_connection() {
        $response = $this->request('mail_settings', null, 'GET');
        
        if (isset($response['error'])) {
            throw new \Exception($response['error']['message']);
        }
        
        return true;
    }
     public function get_analytics($filters = []) {
        $response = $this->request('stats', null, 'GET');
        
        if (isset($response['error'])) {
            throw new \Exception($response['error']['message']);
        }
        
        return $response;
     }

}
