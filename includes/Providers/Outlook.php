<?php

namespace FreeMailSMTP\Providers;

class Outlook extends BaseProvider
{
    private $token_url = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
    private $auth_url = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize';
    private $api_url = 'https://graph.microsoft.com/v1.0/me/sendMail';

    public function get_api_url()
    {
        return $this->api_url;
    }

    public function get_headers()
    {
        $token = $this->get_access_token();
        return [
            'Authorization' => 'Bearer ' . $token['access_token'],
            'Content-Type' => 'application/json'
        ];
    }

    private function save_access_token($token)
    {
        update_option('free_mail_smtp_outlook_access_token', $token);
        error_log('Access token saved.');
    }

    private function save_refresh_token($token)
    {
        update_option('free_mail_smtp_outlook_refresh_token', $token);
        error_log('Refresh token saved.');
    }

    private function get_access_token()
    {
        return get_option('free_mail_smtp_outlook_access_token');
    }

    private function get_refresh_token()
    {
        return get_option('free_mail_smtp_outlook_refresh_token');
    }

    public function send($data)
    {
        try {
            $token = $this->get_access_token();
            if (empty($token['access_token'])) {
                throw new \Exception('Outlook authentication required');
            }

            if (isset($token['expires_in']) && time() >= $token['expires_in']) {
                $this->refresh_token($token['refresh_token']);
                $token = $this->get_access_token();
            }

            $email_data = [
                'message' => [
                    'subject' => $data['subject'],
                    'body' => [
                        'contentType' => 'HTML',
                        'content' => $data['message']
                    ],
                    'toRecipients' => array_map(function($email) {
                        return [
                            'emailAddress' => [
                                'address' => $email
                            ]
                        ];
                    }, (array)$data['to']),
                    'from' => [
                        'emailAddress' => [
                            'address' => $data['from_email'],
                            'name' => $data['from_name']
                        ]
                    ]
                ]
            ];

            if (!empty($data['attachments'])) {
                $email_data['message']['attachments'] = array_map(function($attachment) {
                    return [
                        '@odata.type' => '#microsoft.graph.fileAttachment',
                        'name' => $attachment['filename'],
                        'contentType' => $attachment['type'],
                        'contentBytes' => base64_encode($attachment['content'])
                    ];
                }, $data['attachments']);
            }

            $response = $this->request(
                $this->api_url,
                $email_data,
                [
                    'Authorization' => 'Bearer ' . $token['access_token'],
                    'Content-Type' => 'application/json'
                ],
                'POST'
            );

            if (isset($response['error'])) {
                throw new \Exception($this->get_error_message(json_encode($response), 400));
            }

            return [
                'message_id' => uniqid('outlook_', true),
                'provider_response' => $response
            ];
        } catch (\Exception $e) {
            error_log('Outlook send error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function test_connection()
    {
        try {
            $token = $this->get_access_token();
            if (empty($token['access_token'])) {
                throw new \Exception('Outlook authentication required');
            }

            $response = $this->request(
                'https://graph.microsoft.com/v1.0/me',
                [],
                [
                    'Authorization' => 'Bearer ' . $token['access_token'],
                    'Content-Type' => 'application/json'
                ],
                'GET'
            );

            if (isset($response['error'])) {
                throw new \Exception('Connection test failed: ' . ($response['error']['message'] ?? 'Unknown error'));
            }

            return [
                'success' => true,
                'message' => 'Outlook connection verified successfully.'
            ];
        } catch (\Exception $e) {
            throw new \Exception('Outlook connection test failed: ' . $e->getMessage());
        }
    }

    public function get_auth_url()
    {
        $params = [
            'client_id' => $this->config_keys['client_id'],
            'response_type' => 'code',
            'redirect_uri' => admin_url('free-mail-smtp-oauth.php'),
            'response_mode' => 'query',
            'scope' => 'offline_access Mail.Send',
            'state' => 'outlook'
        ];
        return $this->auth_url . '?' . http_build_query($params);
    }

    protected function get_error_message($body, $code)
    {
        $data = json_decode($body, true);

        if (isset($data['error']['message'])) {
            return "Outlook API error: {$data['error']['message']}. (HTTP $code)";
        }

        if (isset($data['message'])) {
            return "Outlook API error: {$data['message']}. (HTTP $code)";
        }

        return "Outlook API error (HTTP $code)";
    }

    public function get_analytics($filters = [])
    {
        return [];
    }

    private function get_message_headers($message)
    {
        $headers = [];
        foreach ($message->getPayload()->getHeaders() as $header) {
            $headers[strtolower($header->getName())] = $header->getValue();
        }
        return $headers;
    }

    public function handle_oauth_callback($code)
    {
        try {
            $response = $this->request(
                $this->token_url,
                [
                    'client_id' => $this->config_keys['client_id'],
                    'client_secret' => $this->config_keys['client_secret'],
                    'code' => $code,
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => admin_url('admin.php?page=free_mail_smtp-settings')
                ],
                false,
                'POST'
            );

            if (isset($response['error'])) {
                throw new \Exception('OAuth error: ' . ($response['error_description'] ?? $response['error']));
            }

            $this->save_access_token($response);
            if (!empty($response['refresh_token'])) {
                $this->save_refresh_token($response['refresh_token']);
            }

            return true;
        } catch (\Exception $e) {
            error_log('Outlook OAuth error: ' . $e->getMessage());
            throw new \Exception('Failed to authenticate with Outlook: ' . $e->getMessage());
        }
    }

    private function refresh_token($refresh_token)
    {
        $response = $this->request(
            $this->token_url,
            [
                'client_id' => $this->config_keys['client_id'],
                'client_secret' => $this->config_keys['client_secret'],
                'refresh_token' => $refresh_token,
                'grant_type' => 'refresh_token'
            ],
            false,
            'POST'
        );

        if (isset($response['error'])) {
            throw new \Exception('Failed to refresh token: ' . ($response['error_description'] ?? $response['error']));
        }

        $this->save_access_token($response);
        if (!empty($response['refresh_token'])) {
            $this->save_refresh_token($response['refresh_token']);
        }
    }

    private function validateAccessToken()
    {
        $accessToken = $this->get_access_token();
        if (!empty($accessToken)) {
            $this->client->setAccessToken($accessToken);
            if ($this->client->isAccessTokenExpired()) {
                $this->client->refreshToken($this->get_refresh_token());
                try {
                    $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                    $this->save_access_token($this->client->getAccessToken());
                    $this->save_refresh_token($this->client->getRefreshToken());
                } catch (\Exception $e) {
                    error_log('Token refresh failed: ' . $e->getMessage());
                }
            }
        }
    }
}
