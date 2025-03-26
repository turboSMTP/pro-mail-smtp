=== Free Mail SMTP ===
Tags: smtp, email, wp mail, gmail, outlook
Requires at least: 5.5
Tested up to: 6.7
Stable tag: 1.0.0
Requires PHP: 7.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Enhance email deliverability by connecting WordPress to SMTP providers with automatic failover, logging, and advanced routing.

== Description ==

Free Mail SMTP is a powerful WordPress plugin that enhances email deliverability by connecting your site to various email service providers. Configure multiple SMTP providers with automatic failover, track email performance, and ensure reliable email delivery.

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

1. Upload the plugin files to the `/wp-content/plugins/free-mail-smtp` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to **Free Mail SMTP → Settings** to configure the plugin

== Configuration ==

= General Setup =

1. Navigate to **Free Mail SMTP → Settings**
2. Configure your default "From Email" and "From Name"
3. Choose whether to enable email summaries and set your preferred frequency

= Adding Email Providers =

1. Go to **Free Mail SMTP → Providers**
2. Click **Add Provider**
3. Select your email service provider
4. Enter your credentials:
   * For SMTP: Server, port, username, password, encryption type
   * For API-based services: API key and required settings
   * For OAuth services: Follow the authentication flow
5. Set a priority level for each provider (lower numbers = higher priority)
6. Test the connection before saving

= Email Routing (Optional) =

1. Navigate to **Free Mail SMTP → Email Router**
2. Create rules to route specific emails through particular providers
3. Set conditions based on recipient email, source plugin, or other factors

== Frequently Asked Questions ==

= Which email services does this plugin support? =

Free Mail SMTP supports standard SMTP servers, Gmail (with OAuth), Brevo, TurboSMTP, SMTP2GO, Mailgun, and many other providers.

= Can I use multiple email providers? =

Yes! You can configure multiple providers and set priorities for automatic failover. You can also create rules to route specific emails through particular providers.

= Will this plugin work with contact form plugins? =

Yes, Free Mail SMTP works with all major contact form plugins including Contact Form 7, WPForms, Gravity Forms, and more.

== Screenshots ==

1. Provider configuration screen
2. Email logs view
3. Providers Logs Page
4. Settings page
5. Email Router Configurations
6. Email Router page

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release of Free Mail SMTP
