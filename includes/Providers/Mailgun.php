<?php

namespace FreeMailSMTP\Providers;

class Mailgun extends BaseProvider
{
    private $boundary;
    public function __construct($config_keys)
    {
        parent::__construct($config_keys);
        $this->boundary = md5(uniqid());
    }

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
            'Content-Type' => 'multipart/form-data boundary=' . $this->boundary
        ];
    }

    public function send($data)
    {
        $domain = $this->config_keys['domain'];
        $endpoint = $domain . '/messages';
        $payload = '';

        // Add regular fields
        $fields = [
           'from' => $data['from_email'],
           'to' => implode(",", $data['to']),
           'subject' => $data['subject'],
           'html' => $data['message']
        ];
        
        if (!empty($data['cc'])) $fields['cc'] = implode(",", $data['cc']);
        if (!empty($data['bcc'])) $fields['bcc'] = implode(",", $data['bcc']);
        
        foreach($fields as $name => $value) {
           $payload .= "--{$this->boundary}\r\n";
           $payload .= "Content-Disposition: form-data; name=\"{$name}\"\r\n\r\n";
           $payload .= $value . "\r\n";
        }
        
        // Add attachments
        if (!empty($data['attachments'])) {
           foreach($data['attachments'] as $index => $attachment) {
               $file_content = file_get_contents($attachment['path']);
               $mime_type = mime_content_type($attachment['path']);
               $payload .= "--{$this->boundary}\r\n";
               $payload .= "Content-Disposition: form-data; name=\"attachment[{$index}]\"; filename=\"{$attachment['name']}\"\r\n";
               $payload .= "Content-Type: {$mime_type}\r\n\r\n";
               $payload .= $file_content . "\r\n";
           }
        }
        
        $payload .= "--{$this->boundary}--\r\n";
        error_log('Mailgun payload: ' . print_r($payload, true));
        $response = $this->request($endpoint, $payload, false, 'POST', true);
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
        $endpoint = $domain . '/stats/total?event=delivered';
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

        $endpoint = $domain . '/events';
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
