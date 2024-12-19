<?php
namespace FreeMailSMTP\Providers;

class Postmark extends BaseProvider {
    protected function get_api_url() {
        return 'https://api.postmarkapp.com/';
    }
    
    protected function get_headers() {
        return [
            'X-Postmark-Server-Token' => $this->config_keys['api_key'],
            'Content-Type' => 'application/json'
        ];
    }

    public function send($data) {
        $endpoint = '/email';
        $payload = [
            'From' => $data['from_email'],
            'To' => $data['to'][0],
            'Subject' => $data['subject'],
            'HtmlBody' => $data['message'],
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
            'message_id' => 'Postmark' . uniqid(),
            'provider_response' => $response
        ];
    }
    
    protected function prepare_request_body($data) {
        return json_encode($data);
    }
    
    protected function get_error_message($body, $code) {
        $data = json_decode($body, true);
        
        if (isset($data['message'])) {
                return "Postmark API error: {$data['message']}. (HTTP $code)";
            }
        
        return "Postmark API error (HTTP $code)";
    }

    public function test_connection() {
        $endpoint = '/email';

        $payload = [
            'From' => 'osama@travelishtours.com',
            'To' => 'osamadabous@gmail.com',
            'Subject' => 'Test Email',
            'HtmlBody' =>   'This is a test email from Postmark SMTP',
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
            'message_id' => 'Postmark__' . uniqid(),
            'provider_response' => $response
        ];
    }
    public function get_analytics($filters = []) {
        $endpoint = '/essages/outbound';

        $response = $this->request($endpoint, [
            'fromdate' => $filters['date_from'],
            'todate' => $filters['date_to'],
            'count' => 100
        ], false ,'GET');
        $data = [];
        $data['data'] = $this->format_analytics_response($response);
        $data['columns'] = $this->analytics_table_columns();
        return $data;
    }

    private function format_analytics_response($response) {
        $formatted_data = [];
        foreach ($response['Messages'] as $data) {
            $formatted_data[] = [
                'id' => $data['MessageID'],
                'subject' => $data['Subject'],
                'sender' => $data['From'],
                'recipient' => json_encode($data['Recipients']),
                'send_time' => $data['ReceivedAt'],
                'status' => $data['Status']
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