<?php

namespace FreeMailSMTP\Providers;

class Mailgun extends BaseProvider
{
    protected function get_api_url()
    {
        $region = isset($this->config_keys['region']) ? $this->config_keys['region'] : 'us';
        if ($region === 'eu') {
            return 'https://api.eu.mailgun.net/v3/';
        }
        return 'https://api.mailgun.net/v3/';
    }

    protected function get_headers()
    {
        $credentials = base64_encode('api:' . $this->config_keys['api_key']);
        return [
            'Authorization' => 'Basic ' . $credentials,
            'multipart/form-data'
        ];
    }

    public function send($data)
    {
        $domain = $this->config_keys['domain'];
        $endpoint = $domain . '/messages';
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

    protected function prepare_request_body($data)
    {
        return json_encode($data);
    }

    protected function get_error_message($body, $code)
    {
        $data = json_decode($body, true);

        if (isset($data['message'])) {
            return "Mailgun API error: {$data['message']}. (HTTP $code)";
        }

        return "Mailgun API error (HTTP $code)";
    }

    public function test_connection()
    {
        $domain = $this->config_keys['domain'];
        $endpoint = $domain. '/stats/total?event=delivered';
        $response = $this->request($endpoint, [], false, 'GET');
        error_log('Response of test mailgun: ' . print_r($response, true));
        if (isset($response['error'])) {
            throw new \Exception($response['error']['message']);
        }
        return $response;
    }

    public function get_analytics($filters = [])
    {
        $domain = $this->config_keys['domain'];

        $endpoint = $domain. '/events';
        $begin_date = date('r', strtotime($filters['date_from'])); // Converts to RFC 2822 format
        $end_date = date('r', strtotime($filters['date_to'])); // Converts to RFC 2822 format
        $response = $this->request($endpoint, [
            'begin' => $begin_date,
            'end' => $end_date,
            'limit' => 100
        ], false, 'GET');
        $data = [];
        $data['data'] = $this->format_analytics_response($response);
        $data['columns'] = $this->analytics_table_columns();
        return $data;
    }

    private function format_analytics_response($response)
    {
        $formatted_data = [];
        error_log('Responseeeeeeeeeee Mailgunooo: ' . print_r($response, true));
        foreach ($response['items'] as $data) {
            $formatted_data[] = [
                'id' => $data['id'],
                'subject' => $data['message']['headers']['subject'],
                'sender' => $data['envelope']['sender'],
                'recipient' => $data['recipient'],
                'send_time' => date('Y-m-d H:i:s', $data['timestamp']),
                'status' => $data['event']
            ];
        }

        return $formatted_data;
    }

    private function analytics_table_columns()
    {
        return [
            'id',
            'subject',
            'sender',
            'recipient',
            'send_time',
            'status'
        ];
    }
}
