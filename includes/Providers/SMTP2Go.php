<?php
namespace FreeMailSMTP\Providers;

class SMTP2GO extends BaseProvider {
    protected function get_api_url() {
        return 'https://api.smtp2go.com/v3/';
    }
    
    protected function get_headers() {
        return [
            'X-Smtp2go-Api-Key' => $this->config_keys['api_key'],
            'Content-Type' => 'application/json'
        ];
    }

    public function send($data) {
        $endpoint = 'email/send';
        $payload = [
            'sender' => $data['from_email'],
            'to' => $data['to'],
            'subject' => $data['subject'],
            'text_body' => $data['message'],
        ];
        $response = $this->request($endpoint, $payload, false, 'POST');
        
        return [
            'message_id' => 'SMTP2GO__' . uniqid(),
            'provider_response' => $response
        ];
    }
    
    protected function prepare_request_body($data) {
        return json_encode($data);
    }
    
    protected function get_error_message($body, $code) {
        $data = json_decode($body, true);
        
        if (isset($data['message'])) {
                return "SMTP2GO API error: {$data['message']}. (HTTP $code)";
            }
        
        return "SMTP2GO API error (HTTP $code)";
    }

    public function test_connection() {
        $endpoint = 'stats/email_history';
        $response = $this->request($endpoint, [], false,'POST');
        if (isset($response['error'])) {
            throw new \Exception($response['error']['message']);
        }
        
        return $response;
    }
    public function get_analytics($filters = []) {
        $endpoint = 'activity/search';

        $response = $this->request($endpoint, [
            'start_date' => $filters['date_from'],
            'end_date' => date('Y-m-d', strtotime($filters['date_to'] . ' +1 day')),
            'limit' => 100
        ], false ,'POST');
        $data = [];
        $data['data'] = $this->format_analytics_response($response);
        $data['columns'] = $this->analytics_table_columns();
        return $data;
    }

    private function format_analytics_response($response) {
        $formatted_data = [];
        foreach ($response['data']['events'] as $data) {
            $formatted_data[] = [
                'subject' => $data['subject'],
                'sender' => $data['sender'],
                'recipient' => $data['to'],
                'send_time' => $data['date'],
                'status' => $data['event'],
                'provider_message' => $data['smtp_response']
            ];
        }
        
        return $formatted_data;
    }

    private function analytics_table_columns(){
        return [
            'subject', 'sender', 'recipient', 'send_time', 'status', 'provider_message'
        ];
    }
}