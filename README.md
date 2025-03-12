# Free Mail SMTP

![Free Mail SMTP](assets/img/icon-svg.svg)

A powerful WordPress plugin that enhances email deliverability by connecting your site to various email service providers. Configure multiple SMTP providers with automatic failover, track email performance, and ensure reliable email delivery.

## 🚀 Features

- **Multiple Provider Support**:
  - Standard SMTP servers
  - Gmail (with secure OAuth authentication)
  - Brevo (formerly Sendinblue)
  - TurboSMTP
  - SMTP2GO
  - Mailgun
  - And more...

- **Smart Email Routing**:
  - Route emails through specific providers based on custom conditions
  - Automatic failover system using priority levels
  - Set conditions based on email type, recipient, or sending plugin

- **Comprehensive Logging**:
  - Track email status (sent, delivered, failed)
  - View detailed error messages
  - Configurable log retention
  - Email content inspection

- **Analytics Dashboard**:
  - Monitor provider performance
  - View delivery rates
  - Track email engagement
  - Regular summary reports

- **Advanced Settings**:
  - Custom From Email and From Name
  - OAuth authentication for supported providers
  - Fallback to WordPress mail system
  - Easy import from other SMTP plugins

## 📋 Requirements

- WordPress 5.2 or higher
- PHP 7.2 or higher
- Access to your site's server or hosting control panel

## 🔧 Installation

1. Download the plugin from WordPress.org or via your WordPress dashboard
2. Navigate to **Plugins → Add New → Upload Plugin**
3. Select the downloaded zip file and click **Install Now**
4. After installation completes, click **Activate Plugin**

## ⚙️ Configuration

### General Setup

1. Navigate to **Free Mail SMTP → Settings**
2. Configure your default "From Email" and "From Name"
3. Choose whether to enable email summaries and set your preferred frequency

### Adding Email Providers

1. Go to **Free Mail SMTP → Providers**
2. Click **Add Provider**
3. Select your email service provider
4. Enter your credentials:
   - For SMTP: Server, port, username, password, encryption type
   - For API-based services: API key and required settings
   - For OAuth services: Follow the authentication flow
5. Set a priority level for each provider (lower numbers = higher priority)
6. Test the connection before saving

### Email Routing (Optional)

1. Navigate to **Free Mail SMTP → Email Router**
2. Create rules to route specific emails through particular providers
3. Set conditions based on recipient email, source plugin, or other factors

## 📊 Monitoring Your Emails

### Email Logs

Access detailed logs of all emails sent through the plugin:

1. Go to **Free Mail SMTP → Email Logs**
2. View status, recipient, subject, and timestamp
3. Click on any email to see its full details including headers and content

### Provider Analytics

View performance metrics for each provider:

1. Navigate to **Free Mail SMTP → Providers Logs**
2. See delivery rates, bounces, and other provider-specific metrics

## 🧩 Advanced Usage

### Importing from Other SMTP Plugins

The plugin can automatically detect and import settings from:
- WP Mail SMTP
- Easy SMTP

When detected, you'll see an import banner on the Providers page.

### Data Management

For plugin cleanup or troubleshooting:

1. Go to **Free Mail SMTP → Settings**
2. Scroll to the Data Management section
3. Use with caution - deletion actions cannot be undone

## 🤝 Support

Need help with configuration or experiencing issues?

- [Documentation](https://example.com/docs) - Detailed setup guides
- [Support Forum](https://wordpress.org/support/plugin/free-mail-smtp/) - Community help
- [Contact Us](https://example.com/contact) - Direct support

## 📄 License

Free Mail SMTP is licensed under the [GPL-2.0+](http://www.gnu.org/licenses/gpl-2.0.txt)