<?php

namespace TurboSMTP\ProMailSMTP\Providers;
if ( ! defined( 'ABSPATH' ) ) exit;

class AmazonSES extends BaseProvider
{
    /**
     * All AWS regions where Amazon SES is available.
     */
    private const ALLOWED_REGIONS = [
        'us-east-1', 'us-east-2', 'us-west-1', 'us-west-2',
        'eu-west-1', 'eu-west-2', 'eu-west-3', 'eu-central-1', 'eu-south-1', 'eu-north-1',
        'ap-south-1', 'ap-northeast-1', 'ap-northeast-2', 'ap-northeast-3',
        'ap-southeast-1', 'ap-southeast-2',
        'ca-central-1', 'sa-east-1', 'me-south-1', 'af-south-1', 'il-central-1',
    ];

    /**
     * SHA-256 hash of an empty string — used as the payload hash for GET requests.
     */
    private const EMPTY_PAYLOAD_HASH = 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855';

    public function __construct( $config_keys )
    {
        if ( empty( $config_keys['region'] ) || ! in_array( $config_keys['region'], self::ALLOWED_REGIONS, true ) ) {
            $config_keys['region'] = 'us-east-1';
        }
        parent::__construct( $config_keys );
    }

    // -------------------------------------------------------------------------
    // BaseProvider abstract implementations
    // -------------------------------------------------------------------------

    protected function get_api_url()
    {
        return 'https://email.' . $this->config_keys['region'] . '.amazonaws.com/v2/email/';
    }

    /**
     * Returns only the Content-Type header.
     * Full SigV4-signed headers are computed per-request via sign_request().
     */
    protected function get_headers()
    {
        return [ 'Content-Type' => 'application/json' ];
    }

    protected function get_error_message( $body, $code )
    {
        $data    = json_decode( $body, true );
        $type    = $data['__type']  ?? ( $data['Code']    ?? 'Unknown' );
        $message = $data['message'] ?? ( $data['Message'] ?? '' );
        return "Amazon SES error [{$type}]: {$message} (HTTP {$code})";
    }

    // -------------------------------------------------------------------------
    // Send
    // -------------------------------------------------------------------------

    public function send( $data )
    {
        $email_from = ! empty( $this->config_keys['email_from_overwrite'] )
            ? $this->config_keys['email_from_overwrite']
            : $data['from_email'];

        if ( ! empty( $data['attachments'] ) ) {
            $payload = $this->build_raw_payload( $data, $email_from );
        } else {
            $payload = $this->build_simple_payload( $data, $email_from );
        }

        $body    = wp_json_encode( $payload );
        $path    = '/v2/email/outbound-emails';
        $headers = $this->sign_request( 'POST', $path, $body );

        // $is_form_data = true so BaseProvider passes $body as-is without re-encoding
        $result = $this->request( 'outbound-emails', $body, false, 'POST', true, $headers );

        return [
            'message_id'       => $result['MessageId'] ?? ( 'AmazonSES-' . uniqid() ),
            'provider_response' => [ 'MessageId' => $result['MessageId'] ?? null ],
        ];
    }

    // -------------------------------------------------------------------------
    // Test connection
    // -------------------------------------------------------------------------

    public function test_connection()
    {
        $admin_email = get_option( 'admin_email' );
        $site_name   = get_bloginfo( 'name' );
        $site_url    = get_bloginfo( 'url' );

        $from_email = ! empty( $this->config_keys['email_from_overwrite'] )
            ? $this->config_keys['email_from_overwrite']
            : get_option( 'pro_mail_smtp_from_email', $admin_email );

        if ( empty( $from_email ) || ! is_string( $from_email ) ) {
            $from_email = $admin_email;
        }

        $test_data = [
            'to'         => [ $admin_email ],
            'subject'    => 'Pro Mail SMTP: Amazon SES Test Email',
            'message'    => sprintf(
                'This is a test email from %s (%s) to verify your Amazon SES configuration with the Pro Mail SMTP plugin.<br><br>If you\'re reading this, your Amazon SES connection is working properly!<br><br>Sent: %s',
                esc_html( $site_name ),
                esc_url( $site_url ),
                gmdate( 'Y-m-d H:i:s' )
            ),
            'from_email' => $from_email,
            'from_name'  => 'Pro Mail SMTP Test',
        ];

        $result = $this->send( $test_data );

        if ( ! empty( $result['message_id'] ) ) {
            return [
                'success' => true,
                'message' => 'Amazon SES connection verified successfully. Test email sent to ' . $admin_email,
            ];
        }

        throw new \Exception( 'Test email could not be sent.' );
    }

    // -------------------------------------------------------------------------
    // Analytics
    // -------------------------------------------------------------------------

    public function get_analytics( $filters = [] )
    {
        throw new \Exception(
            'Analytics are not available for Amazon SES. View sending history in the Email Logs tab.'
        );
    }

    // -------------------------------------------------------------------------
    // SigV4 signing
    // -------------------------------------------------------------------------

    /**
     * Signs a request using AWS Signature Version 4.
     *
     * @param string $method  HTTP method (GET | POST)
     * @param string $path    URL path including leading slash, e.g. '/v2/email/outbound-emails'
     * @param string $body    Request body (empty string for GET)
     * @return array          Complete headers array to pass to BaseProvider::request()
     */
    private function sign_request( $method, $path, $body = '' )
    {
        $region         = $this->config_keys['region'];
        $access_key_id  = $this->config_keys['access_key_id'];
        $secret_key     = $this->config_keys['secret_access_key'];
        $host           = 'email.' . $region . '.amazonaws.com';
        $service        = 'ses';

        $datetime       = gmdate( 'Ymd\THis\Z' );
        $date           = gmdate( 'Ymd' );
        $payload_hash   = empty( $body ) ? self::EMPTY_PAYLOAD_HASH : hash( 'sha256', $body );

        // Canonical headers — must be sorted alphabetically (lowercased keys)
        $canonical_headers =
            "content-type:application/json\n" .
            "host:{$host}\n" .
            "x-amz-content-sha256:{$payload_hash}\n" .
            "x-amz-date:{$datetime}\n";

        $signed_headers = 'content-type;host;x-amz-content-sha256;x-amz-date';

        // Canonical request
        $canonical_request = implode( "\n", [
            $method,
            $path,
            '',   // query string (none for our endpoints)
            $canonical_headers,
            $signed_headers,
            $payload_hash,
        ] );

        // Credential scope
        $credential_scope = "{$date}/{$region}/{$service}/aws4_request";

        // String to sign
        $string_to_sign = implode( "\n", [
            'AWS4-HMAC-SHA256',
            $datetime,
            $credential_scope,
            hash( 'sha256', $canonical_request ),
        ] );

        // Derive signing key — use raw binary output (true) for chained HMACs
        $signing_key = hash_hmac( 'sha256', 'aws4_request',
                        hash_hmac( 'sha256', $service,
                        hash_hmac( 'sha256', $region,
                        hash_hmac( 'sha256', $date, 'AWS4' . $secret_key, true ), true ), true ), true );

        // Final hex signature
        $signature = hash_hmac( 'sha256', $string_to_sign, $signing_key );

        $authorization =
            "AWS4-HMAC-SHA256 " .
            "Credential={$access_key_id}/{$credential_scope}, " .
            "SignedHeaders={$signed_headers}, " .
            "Signature={$signature}";

        return [
            'Content-Type'          => 'application/json',
            'x-amz-date'            => $datetime,
            'x-amz-content-sha256'  => $payload_hash,
            'Authorization'         => $authorization,
        ];
    }

    // -------------------------------------------------------------------------
    // Payload builders
    // -------------------------------------------------------------------------

    /**
     * Builds a SES v2 "Simple" content payload (no attachments).
     */
    private function build_simple_payload( $data, $email_from )
    {
        $content_type = ! empty( $data['content_type'] ) ? strtolower( $data['content_type'] ) : '';
        $is_plain_text = ( strpos( $content_type, 'text/plain' ) !== false );

        $body_content = $is_plain_text
            ? [ 'Text' => [ 'Data' => $data['message'], 'Charset' => 'UTF-8' ] ]
            : [ 'Html' => [ 'Data' => $data['message'], 'Charset' => 'UTF-8' ] ];

        $payload = [
            'FromEmailAddress' => $email_from,
            'Destination'      => [
                'ToAddresses'  => $this->normalise_addresses( $data['to'] ?? [] ),
                'CcAddresses'  => $this->normalise_addresses( $data['cc'] ?? [] ),
                'BccAddresses' => $this->normalise_addresses( $data['bcc'] ?? [] ),
            ],
            'Content' => [
                'Simple' => [
                    'Subject' => [ 'Data' => $data['subject'], 'Charset' => 'UTF-8' ],
                    'Body'    => $body_content,
                ],
            ],
        ];

        if ( ! empty( $data['reply_to'] ) ) {
            $reply_address = is_array( $data['reply_to'] ) ? $data['reply_to']['email'] : $data['reply_to'];
            $payload['ReplyToAddresses'] = [ $reply_address ];
        }

        return $payload;
    }

    /**
     * Builds a SES v2 "Raw" content payload (with attachments).
     * Constructs a multipart/mixed MIME message then base64-encodes the whole thing.
     */
    private function build_raw_payload( $data, $email_from )
    {
        $boundary  = uniqid( 'ses_', true );
        $charset   = get_bloginfo( 'charset' ) ?: 'UTF-8';
        $parts     = [];

        // — Headers —
        $to_header   = implode( ', ', $this->normalise_addresses( $data['to'] ?? [] ) );
        $from_header = ! empty( $data['from_name'] )
            ? $this->encode_mime_header( $data['from_name'] ) . " <{$email_from}>"
            : $email_from;
        $parts[]     = "To: {$to_header}";
        $parts[]     = "From: {$from_header}";
        $parts[]     = "Subject: " . $this->encode_mime_header( $data['subject'] );
        $parts[]   = 'MIME-Version: 1.0';
        $parts[]   = "Content-Type: multipart/mixed; boundary=\"{$boundary}\"";

        if ( ! empty( $data['reply_to'] ) ) {
            $reply_address = is_array( $data['reply_to'] ) ? $data['reply_to']['email'] : $data['reply_to'];
            $parts[]       = "Reply-To: {$reply_address}";
        }

        foreach ( [ 'cc', 'bcc' ] as $field ) {
            if ( ! empty( $data[ $field ] ) ) {
                $addresses = $this->normalise_addresses( is_array( $data[ $field ] ) ? $data[ $field ] : [ $data[ $field ] ] );
                if ( ! empty( $addresses ) ) {
                    $parts[] = ucfirst( $field ) . ': ' . implode( ', ', $addresses );
                }
            }
        }

        $parts[] = ''; // blank line before body

        // — Body part —
        $content_type = ! empty( $data['content_type'] ) ? strtolower( $data['content_type'] ) : '';
        $is_plain_text = ( strpos( $content_type, 'text/plain' ) !== false );
        $mime_type = $is_plain_text ? 'text/plain' : 'text/html';

        $parts[] = "--{$boundary}";
        $parts[] = "Content-Type: {$mime_type}; charset={$charset}";
        $parts[] = 'Content-Transfer-Encoding: base64';
        $parts[] = '';
        $parts[] = rtrim( base64_encode( $data['message'] ) );
        $parts[] = '';

        // — Attachment parts —
        foreach ( $data['attachments'] as $attachment ) {
            if ( empty( $attachment['content'] ) || empty( $attachment['name'] ) || empty( $attachment['type'] ) ) {
                continue;
            }
            $parts[] = "--{$boundary}";
            $parts[] = "Content-Type: {$attachment['type']}; name=\"{$attachment['name']}\"";
            $parts[] = "Content-Disposition: attachment; filename=\"{$attachment['name']}\"";
            $parts[] = 'Content-Transfer-Encoding: base64';
            $parts[] = '';
            $parts[] = chunk_split( $attachment['content'] );
            $parts[] = '';
        }

        $parts[] = "--{$boundary}--";

        $raw_mime = implode( "\r\n", $parts );

        return [
            'Content' => [
                'Raw' => [ 'Data' => base64_encode( $raw_mime ) ],
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Normalises an address list to plain RFC-5321 strings.
     * Accepts strings or arrays with 'email' / 'name' keys.
     */
    private function normalise_addresses( $addresses )
    {
        if ( empty( $addresses ) ) {
            return [];
        }
        if ( is_string( $addresses ) ) {
            return [ $addresses ];
        }
        $result = [];
        foreach ( $addresses as $addr ) {
            if ( is_string( $addr ) ) {
                $result[] = $addr;
            } elseif ( is_array( $addr ) && ! empty( $addr['email'] ) ) {
                $result[] = ! empty( $addr['name'] )
                    ? "{$addr['name']} <{$addr['email']}>"
                    : $addr['email'];
            }
        }
        return $result;
    }

    /**
     * Override request() to enforce sslverify on all outgoing calls to AWS.
     */
    protected function request( $endpoint, $data = [], $override_base_api_url = false, $method = 'POST', $is_form_data = false, $headers = [] )
    {
        add_filter( 'http_request_args', [ $this, 'enforce_ssl_verify' ] );
        try {
            $result = parent::request( $endpoint, $data, $override_base_api_url, $method, $is_form_data, $headers );
        } finally {
            remove_filter( 'http_request_args', [ $this, 'enforce_ssl_verify' ] );
        }
        return $result;
    }

    /**
     * Callback for the http_request_args filter — forces sslverify = true.
     */
    public function enforce_ssl_verify( $args )
    {
        $args['sslverify'] = true;
        return $args;
    }
}
