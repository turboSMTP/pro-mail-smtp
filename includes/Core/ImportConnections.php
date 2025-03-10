<?php

namespace FreeMailSMTP\Core;


class ImportConnections
{
    private $wpmailOption;
    private $easysmtpOption;
    private $providerManager;

        public function __construct() {
            $this->wpmailOption = get_option('wp_mail_smtp');
            $this->easysmtpOption = get_option('swpsmtp_options');
            $this->providerManager = new ProviderManager();
        }

    public function isImportAvailable()
    {
        return [
            'wpMail' => $this->isWpMailSMTPAvailable(),
            'easySMTP' => $this->isEasySMTPAvailable(),
        ];
    }

    private function isWpMailSMTPAvailable()
    {
        $wpMailSMTP =$this->wpmailOption;
        if (!$wpMailSMTP) {
            $this->dismissNotice('free_mail_smtp_import_wpmail_notice_dismissed');
            return false;
        }
        return true;
    }

    private function isEasySMTPAvailable()
    {
        $easySMTP = $this->easysmtpOption;

        if (!$easySMTP || !is_array($easySMTP)) {
            $this->dismissNotice('free_mail_smtp_import_easysmtp_notice_dismissed');
            return false;
        }
        return true;
    }

    private function dismissNotice($optionName)
    {
        update_option($optionName, true);
    }

    public function importProviders($plugin){
        
        if ($plugin == 'wpMail') {
          return  $this->importProviderData($this->wpmailOption);
        }

        if ($plugin == 'easySMTP') {
         return   $this->importProviderData($this->easysmtpOption);
        }
    }


    private function importProviderData($info)
    {
        try{
            foreach ($info as $key => $value) {
                switch($key){
                    case 'smtp':
                        $this->importOtherProvider($value);
                        break;
                    case 'mailgun':
                        $this->importMailgunProvider($value);
                        break;
                    case 'smtp2go':
                        $this->importSMTP2GOProvider($value);
                        break;
                    // case 'sendgrid':
                    //     $this->importSendGridProvider($value);
                    //     break;
                    case 'postmark':
                        $this->importPostmarkProvider($value);
                        break;
                    case 'sparkpost':
                        $this->importSparkpostProvider($value);
                        break;
                    // case 'amazon_ses':
                    //     $this->importAmazonSESProvider($value);
                    //     break;
                    // case 'smtpcom':
                    //     $this->importSMTPcomProvider($value);
                    //     break;
                    // case 'brevo':
                    //     $this->importBrevoProvider($value);
                    //     break;
                    case 'gmail':
                        $this->importGmailProvider($value);
                        break;
                    // case 'outlook':
                    //     $this->importOutlookProvider($value);
                    //     break;
                    // case 'zoho':
                    //     $this->importZohoProvider($value);
                    //     break;
                    default:
                    break;
                }
            }
            return 'Imported Successfully';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }
    

    private function importOtherProvider($data)
    {
        if ($data['host'] == '' || $data['user'] == '' || $data['pass'] == '') {
            return;
        }
        $available_priority = $this->providerManager->get_available_priority();
        $providerData = [
            'provider' => 'other',
            'connection_id' => '',
            'connection_label' => 'Imported Other SMTP',
            'priority' => $available_priority[0],
            'config_keys' => [
                'smtp_host' => $data['host'],
                'smtp_port' => $data['port'],
                'smtp_encryption' => $data['encryption'],
                'smtp_user' => $data['user'],
                'smtp_pw' => $data['pass']
            ]
        ];
        $this->providerManager->save_provider($providerData);
    }

    private function importMailgunProvider($data)
    {
        if ($data['domain'] == '' || $data['api_key']=='') {
            return;
        }
        $available_priority = $this->providerManager->get_available_priority();
        $providerData = [
            'provider' => 'mailgun',
            'connection_id' => '',
            'connection_label' => 'Imported Mailgun',
            'priority' => $available_priority[0],
            'config_keys' => [
                'domain' => $data['domain'],
                'api_key' => $data['api_key'],
                'region' => $data['region'] ?? 'us'
            ]
        ];
        $this->providerManager->save_provider($providerData);
    }

    private function importSMTP2GOProvider($data)
    {
        if ($data['api_key'] == '')  {
            return;
        }
        $available_priority = $this->providerManager->get_available_priority();
        $providerData = [
            'provider' => 'smtp2go',
            'connection_id' => '',
            'connection_label' => 'Imported SMTP2GO',
            'priority' => $available_priority[0],
            'config_keys' => [
                'api_key' => $data['api_key']
            ]
        ];
        $this->providerManager->save_provider($providerData);
    }

    // private function importSendGridProvider($data)
    // {
    //     $available_priority = $this->providerManager->get_available_priority();
    //     $providerData = [
    //         'provider' => 'sendgrid',
    //         'connection_id' => '',
    //         'connection_label' => 'Imported SendGrid',
    //         'priority' => $available_priority[0],
    //         'config_keys' => [
    //             'sendgrid_key' => $data['key']
    //         ]
    //     ];
    //     $this->providerManager->save_provider($providerData);
    // }
    private function importPostmarkProvider($data)
    {
        if ($data['server_api_token'] == '') {
            return;
        }
        $available_priority = $this->providerManager->get_available_priority();
        $providerData = [
            'provider' => 'postmark',
            'connection_id' => '',
            'connection_label' => 'Imported Postmark',
            'priority' => $available_priority[0],
            'config_keys' => [
                'api_key' => $data['server_api_token']
            ]
        ];
        $this->providerManager->save_provider($providerData);
    }
    private function importSparkpostProvider($data)
    {
        if ($data['api_key']=='') {
            return;
        }
        $available_priority = $this->providerManager->get_available_priority();
        $providerData = [
            'provider' => 'sparkpost',
            'connection_id' => '',
            'connection_label' => 'Imported Sparkpost',
            'priority' => $available_priority[0],
            'config_keys' => [
                'api_key' => $data['api_key'],
                'region' => $data['region'] ?? 'us'
            ]
        ];
        $this->providerManager->save_provider($providerData);
    }
    // private function importAmazonSESProvider($data)
    // {
    //     $available_priority = $this->providerManager->get_available_priority();
    //     $providerData = [
    //         'provider' => 'amazon_ses',
    //         'connection_id' => '',
    //         'connection_label' => 'Imported Amazon SES',
    //         'priority' => $available_priority[0],
    //         'config_keys' => [
    //             'access_key' => $data['access_key'],
    //             'secret_key' => $data['secret_key']
    //         ]
    //     ];
    //     $this->providerManager->save_provider($providerData);
    // }

    // private function importSMTPcomProvider($data)
    // {
    //     $available_priority = $this->providerManager->get_available_priority();
    //     $providerData = [
    //         'provider' => 'smtpcom',
    //         'connection_id' => '',
    //         'connection_label' => 'Imported SMTPcom',
    //         'priority' => $available_priority[0],
    //         'config_keys' => [
    //             'api_key' => $data['api_key']
    //         ]
    //     ];
    //     $this->providerManager->save_provider($providerData);
    // }
    // private function importBrevoProvider($data)
    // {
    //     $available_priority = $this->providerManager->get_available_priority();
    //     $providerData = [
    //         'provider' => 'brevo',
    //         'connection_id' => '',
    //         'connection_label' => 'Imported Brevo',
    //         'priority' => $available_priority[0],
    //         'config_keys' => [
    //             'api_key' => $data['key']
    //         ]
    //     ];
    //     $this->providerManager->save_provider($providerData);
    // }
    private function importGmailProvider($data)
    {
        if ($data['client_secret']=='' || $data['client_id']=='') {
            return;
        }
        $available_priority = $this->providerManager->get_available_priority();
        $providerData = [
            'provider' => 'gmail',
            'connection_id' => '',
            'connection_label' => 'Imported Gmail',
            'priority' => $available_priority[0],
            'config_keys' => [
                'client_id' => $data['client_id'],
                'client_secret' => $data['client_secret']
            ]
        ];
        $this->providerManager->save_provider($providerData);
    }

    // private function importOutlookProvider($data)
    // {
    //     $available_priority = $this->providerManager->get_available_priority();
    //     $providerData = [
    //         'provider' => 'outlook',
    //         'connection_id' => '',
    //         'connection_label' => 'Imported Outlook',
    //         'priority' => $available_priority[0],
    //         'config_keys' => [
    //             'client_id' => $data['client_id'],
    //             'client_secret' => $data['client_secret']
    //         ]
    //     ];
    //     $this->providerManager->save_provider($providerData);
    // }
    // private function importZohoProvider($data)
    // {
    //     $available_priority = $this->providerManager->get_available_priority();
    //     $providerData = [
    //         'provider' => 'zoho',
    //         'connection_id' => '',
    //         'connection_label' => 'Imported Zoho',
    //         'priority' => $available_priority[0],
    //         'config_keys' => [
    //             'zoho_key' => $data['key']
    //         ]
    //     ];
    //     $this->providerManager->save_provider($providerData);
    // }
}