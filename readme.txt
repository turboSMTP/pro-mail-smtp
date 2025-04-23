=== Pro Mail SMTP ===
Tags: smtp, email, wp mail, gmail, outlook
Requires at least: 5.5
Tested up to: 6.8
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Enhance email deliverability by connecting WordPress to SMTP providers with automatic failover, logging, and advanced routing.

== Description ==

Pro Mail SMTP is a powerful WordPress plugin that enhances email deliverability by connecting your site to various email service providers. Configure multiple SMTP providers with automatic failover, track email performance, and ensure reliable email delivery.

= 🚀 Features =

* **Multiple Provider Support**:
  * Standard SMTP servers
  * Gmail (with secure OAuth authentication)
  * Brevo (formerly Sendinblue)
  * TurboSMTP
  * SMTP2GO
  * Mailgun
  * And more...

* **Smart Email Routing**:
  * Route emails through specific providers based on custom conditions
  * Automatic failover system using priority levels
  * Set conditions based on email type, recipient, or sending plugin

* **Comprehensive Logging**:
  * Track email status (sent, delivered, failed)
  * View detailed error messages
  * Configurable log retention
  * Email content inspection

* **Analytics Dashboard**:
  * Monitor provider performance
  * View delivery rates
  * Track email engagement
  * Regular summary reports

* **Advanced Settings**:
  * Custom From Email and From Name
  * OAuth authentication for supported providers
  * Fallback to WordPress mail system
  * Easy import from other SMTP plugins

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/pro-mail-smtp` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to **Pro Mail SMTP → Settings** to configure the plugin

== Configuration ==

= General Setup =

1. Navigate to **Pro Mail SMTP → Settings**
2. Configure your default "From Email" and "From Name"
3. Choose whether to enable email summaries and set your preferred frequency

= Adding Email Providers =

1. Go to **Pro Mail SMTP → Providers**
2. Click **Add Provider**
3. Select your email service provider
4. Enter your credentials:
   * For SMTP: Server, port, username, password, encryption type
   * For API-based services: API key and required settings
   * For OAuth services: Follow the authentication flow
5. Set a priority level for each provider (lower numbers = higher priority)
6. Test the connection before saving

= Email Routing (Optional) =

1. Navigate to **Pro Mail SMTP → Email Router**
2. Create rules to route specific emails through particular providers
3. Set conditions based on recipient email, source plugin, or other factors

== Frequently Asked Questions ==

= Which email services does this plugin support? =

Pro Mail SMTP supports standard SMTP servers, Gmail (with OAuth), Brevo, TurboSMTP, SMTP2GO, Mailgun, and many other providers.

= Can I use multiple email providers? =

Yes! You can configure multiple providers and set priorities for automatic failover. You can also create rules to route specific emails through particular providers.

= Will this plugin work with contact form plugins? =

Yes, Pro Mail SMTP works with all major contact form plugins including Contact Form 7, WPForms, Gravity Forms, and more.

== Screenshots ==

1. Provider configuration screen
2. Email logs view
3. Providers Logs Page
4. Settings page
5. Email Router Configurations
6. Email Router page

== Third-Party Services ==

Pro Mail SMTP connects to various third-party email service providers to send your WordPress site's emails. When you configure and use these services, your site will transmit data to these external services. Below is information about each service:

= Gmail =
* **Service Description**: Google's email service used for sending emails from your WordPress site.
* **Data Transmitted**: Email content (subject, body, attachments), recipient email addresses, sender information, and authentication data.
* **When Data is Sent**: When an email is sent through Gmail via the plugin and during authentication.
* **Terms of Service**: [https://policies.google.com/terms](https://policies.google.com/terms)
* **Privacy Policy**: [https://policies.google.com/privacy](https://policies.google.com/privacy)

= Brevo (formerly Sendinblue) =
* **Service Description**: Email marketing and transactional email service.
* **Data Transmitted**: Email content (subject, body, attachments), recipient email addresses, and sender information.
* **When Data is Sent**: When an email is sent through Brevo via the plugin.
* **Terms of Service**: [https://www.brevo.com/legal/termsofuse/](https://www.brevo.com/legal/termsofuse/)
* **Privacy Policy**: [https://www.brevo.com/legal/privacy-policy/](https://www.brevo.com/legal/privacy-policy/)

= Outlook/Microsoft =
* **Service Description**: Microsoft's email service used for sending emails.
* **Data Transmitted**: Email content (subject, body, attachments), recipient email addresses, sender information, and authentication data.
* **When Data is Sent**: When an email is sent through Outlook via the plugin and during authentication.
* **Terms of Service**: [https://www.microsoft.com/licensing/terms/](https://www.microsoft.com/licensing/terms/)
* **Privacy Policy**: [https://privacy.microsoft.com/](https://privacy.microsoft.com/)

= Mailgun =
* **Service Description**: Email API service for sending, receiving, and tracking emails.
* **Data Transmitted**: Email content (subject, body, attachments), recipient email addresses, and sender information.
* **When Data is Sent**: When an email is sent through Mailgun via the plugin.
* **Terms of Service**: [https://www.mailgun.com/terms/](https://www.mailgun.com/terms/)
* **Privacy Policy**: [https://www.mailgun.com/privacy-policy/](https://www.mailgun.com/privacy-policy/)

= Postmark =
* **Service Description**: Transactional email delivery service.
* **Data Transmitted**: Email content (subject, body, attachments), recipient email addresses, and sender information.
* **When Data is Sent**: When an email is sent through Postmark via the plugin.
* **Terms of Service**: [https://postmarkapp.com/terms-of-service](https://postmarkapp.com/terms-of-service)
* **Privacy Policy**: [https://wildbit.com/privacy-policy](https://wildbit.com/privacy-policy)

= Sendgrid =
* **Service Description**: Email delivery platform for transactional and marketing emails.
* **Data Transmitted**: Email content (subject, body, attachments), recipient email addresses, and sender information.
* **When Data is Sent**: When an email is sent through Sendgrid via the plugin.
* **Terms of Service**: [https://sendgrid.com/policies/tos/](https://sendgrid.com/policies/tos/)
* **Privacy Policy**: [https://sendgrid.com/policies/privacy/](https://sendgrid.com/policies/privacy/)

= SMTP2Go =
* **Service Description**: Email delivery service for reliable email sending.
* **Data Transmitted**: Email content (subject, body, attachments), recipient email addresses, and sender information.
* **When Data is Sent**: When an email is sent through SMTP2Go via the plugin.
* **Terms of Service**: [https://www.smtp2go.com/terms-of-service/](https://www.smtp2go.com/terms-of-service/)
* **Privacy Policy**: [https://www.smtp2go.com/privacy-policy/](https://www.smtp2go.com/privacy-policy/)

= Sparkpost =
* **Service Description**: Email delivery service for sending and analyzing emails.
* **Data Transmitted**: Email content (subject, body, attachments), recipient email addresses, and sender information.
* **When Data is Sent**: When an email is sent through Sparkpost via the plugin.
* **Terms of Service**: [https://www.sparkpost.com/policies/tou/](https://www.sparkpost.com/policies/tou/)
* **Privacy Policy**: [https://www.sparkpost.com/policies/privacy/](https://www.sparkpost.com/policies/privacy/)

= TurboSMTP =
* **Service Description**: Professional SMTP relay service for reliable email delivery.
* **Data Transmitted**: Email content (subject, body, attachments), recipient email addresses, and sender information.
* **When Data is Sent**: When an email is sent through TurboSMTP via the plugin.
* **Terms of Service**: [https://www.serversmtp.com/en/terms-of-service](https://www.serversmtp.com/en/terms-of-service)
* **Privacy Policy**: [https://www.serversmtp.com/en/privacy-policy](https://www.serversmtp.com/en/privacy-policy)

= Other SMTP Servers =
When using custom SMTP servers, your email data will be transmitted to the SMTP service you configure. Please refer to your SMTP service provider's terms of service and privacy policy for details on how they handle your data.

**Important Note**: This plugin does not collect or share any data with these services beyond what is necessary to send emails. Your email content and recipient information is only sent to the services you explicitly configure in the plugin settings.

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release of Pro Mail SMTP
