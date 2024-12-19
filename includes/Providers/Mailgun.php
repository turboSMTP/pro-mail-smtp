<?php
namespace FreeMailSMTP\Providers;

class Mailgun extends BaseProvider {
    protected function get_api_url() {
        return 'https://api.mailgun.net/v3/';
    }
    
    protected function get_headers() {
        return [
            'user' => 'api:'.$this->config_keys['api_key'],
            'Content-Type' => 'application/json'
        ];
    }

    public function send($data) {
        $domain = $this->config_keys['domain'];
        $endpoint = $domain.'/messages';
            error_log('Data to send: ' . print_r(json_encode($data), true));
        $payload = [
            'from' => $data['from_email'],
            'to' => $data['to'][0],
            'subject' => $data['subject'],
            'html' => $data['message'],
        ];
        
        // Add attachments if any
        // if (!empty($data['attachments'])) {
        //     $payload['attachments'] = array_map(function($attachment) {
        //         return [
        //             'content' => $attachment['content'],
        //             'filename' => $attachment['filename'],
        //             'type' => $attachment['type'],
        //             'disposition' => 'attachment'
        //         ];
        //     }, $data['attachments']);
        // }
        error_log('Data to send: ' . print_r(json_encode($payload), true));

        $response = $this->request($endpoint, $payload, false, 'POST');
        error_log('Response of email send: ' . print_r($response, true));
        return [
            'message_id' => 'Mailgun' . uniqid(),
            'provider_response' => $response
        ];
    }
    
    protected function prepare_request_body($data) {
        return json_encode($data);
    }
    
    protected function get_error_message($body, $code) {
        $data = json_decode($body, true);
        
        if (isset($data['message'])) {
                return "Mailgun API error: {$data['message']}. (HTTP $code)";
            }
        
        return "Mailgun API error (HTTP $code)";
    }

    public function test_connection() {
        $domain = $this->config_keys['domain'];
        $endpoint = $domain.'/messages';
        $payload = [
            'from' => 'osama@travelishtours.com',
            'to' => 'osamadabous@gmail.com',
            'subject' => 'Test Email',
            'html' =>   'This is a test email from Maingun SMTP',
        ];
        
        // Add attachments if any
        // if (!empty($data['attachments'])) {
        //     $payload['attachments'] = array_map(function($attachment) {
        //         return [
        //             'content' => $attachment['content'],
        //             'filename' => $attachment['filename'],
        //             'type' => $attachment['type'],
        //             'disposition' => 'attachment'
        //         ];
        //     }, $data['attachments']);
        // }
        error_log('Data to send: ' . print_r(json_encode($payload), true));

        $response = $this->request($endpoint, $payload, false, 'POST');
        error_log('Response of email send: ' . print_r($response, true));
        return [
            'message_id' => 'Mailgun__' . uniqid(),
            'provider_response' => $response
        ];
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
        error_log('Responseeeeeeeeeee Mailgunooo: ' . print_r($response, true));
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