<?php
namespace FreeMailSMTP\Providers;

class Brevo extends BaseProvider {
    protected function get_api_url() {
        return 'https://api.brevo.com/v3/smtp/';
    }
    
    protected function get_headers() {
        return [
            'api-key' => $this->config_keys['api_key'],
            'Content-Type' => 'application/json'
        ];
    }

    public function send($data) {
        $endpoint = 'email';
        $payload = [
            'sender' => [
                'email' => $data['from_email'],
                'name' => $data['from_name']
            ],
            'to' => [
                [
                'email' => $data['to'][0],
                'name' => $data['to'][0]
                ]
            ],
            'subject' => $data['subject'],
                'htmlContent' => $data['message'],
        ];
        
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

        $response = $this->request($endpoint, $payload, false, 'POST');
        
        return [
            'message_id' => 'Brevo__' . uniqid(),
            'provider_response' => $response
        ];
    }
    
    protected function prepare_request_body($data) {
        return json_encode($data);
    }
    
    protected function get_error_message($body, $code) {
        $data = json_decode($body, true);
        
        if (isset($data['message'])) {
                return "Brevo API error: {$data['message']}. (HTTP $code)";
            }
        
        return "Brevo API error (HTTP $code)";
    }

    public function test_connection() {
        $endpoint = 'https://api.brevo.com/v3/account';
        $response = $this->request($endpoint, [], true,'GET');

        if (isset($response['error'])) {
            throw new \Exception($response['error']['message']);
        }
        
        return $response;
    }
    public function get_analytics($filters = []) {
        $endpoint = 'statistics/events';

        $response = $this->request($endpoint, [
            'startDate' => $filters['date_from'],
            'endDate' => $filters['date_to']
        ], false ,'GET');
        $data = [];
        $data['data'] = $this->format_analytics_response($response);
        $data['columns'] = $this->analytics_table_columns();
        return $data;
    }

    private function format_analytics_response($response) {
        $formatted_data = [];
        foreach ($response['events'] as $data) {
            $formatted_data[] = [
                'id' => $data['messageId'],
                'subject' => $data['subject'],
                'sender' => $data['from'],
                'recipient' => $data['email'],
                'send_time' => $data['date'],
                'status' => $data['event']
            ];
        }
        
        return $formatted_data;
    }

    private function analytics_table_columns(){
        return [
            'id', 'subject', 'sender', 'recipient', 'send_time', 'status'
        ];
    }
}