# Pro Mail SMTP

![Pro Mail SMTP](assets/img/icon-svg.svg)

A powerful WordPress plugin that enhances email deliverability by connecting your site to various email service providers. Configure multiple SMTP providers with automatic failover, track email performance, and ensure reliable email delivery.

## 🚀 Features

- **Multiple Provider Support**:
  - Standard SMTP servers
  - Gmail (with secure OAuth authentication)
  - Brevo (formerly Sendinblue)
  - TurboSMTP
  - SMTP2GO
  - Mailgun
  - Microsoft Outlook
  - SendGrid
  - Postmark
  - SparkPost
  - And more...

- **Smart Email Routing**:
  - Route emails through specific providers based on custom conditions
  - Automatic failover system using priority levels
  - Set conditions based on email type, recipient, or sending plugin
  - Advanced conditional logic with multiple operators (contains, starts with, regex, etc.)
  - Support for source application detection

- **Proactive Alert System** 🚨:
  - Real-time email failure notifications
  - Multi-channel alert support (Slack, Discord, Microsoft Teams, Custom Webhooks)
  - Smart threshold-based alerts to prevent spam
  - Consolidated failure reports when thresholds are reached
  - Individual alerts for immediate critical failures
  - Test alert functionality to verify configurations

- **Comprehensive Logging**:
  - Track email status (sent, delivered, failed)
  - View detailed error messages
  - Configurable log retention
  - Email content inspection
  - Email resend functionality from logs
  - Full email header and body inspection

- **Advanced Analytics Dashboard**:
  - Monitor provider performance metrics
  - View delivery rates and engagement statistics
  - Track email analytics per provider
  - Regular summary reports
  - Date range filtering and pagination
  - Export capabilities for detailed analysis

- **Enhanced Security & Authentication**:
  - OAuth 2.0 authentication for Gmail and Outlook
  - Secure API key management
  - SSL/TLS encryption support
  - Connection validation and testing

- **Advanced Settings**:
  - Custom From Email and From Name per routing condition
  - Fallback to WordPress mail system
  - Easy import from other SMTP plugins (WP Mail SMTP, Easy SMTP)

## 📋 Requirements

- WordPress 6.3 or higher
- PHP 7.2 or higher
- Access to your site's server or hosting control panel
- For OAuth providers: Ability to configure external applications (Google Console, Azure AD)
- For webhook alerts: External webhook endpoints (Slack, Discord, Teams, etc.)

## 🔧 Installation

1. Download the plugin from WordPress.org or via your WordPress dashboard
2. Navigate to **Plugins → Add New → Upload Plugin**
3. Select the downloaded zip file and click **Install Now**
4. After installation completes, click **Activate Plugin**

## ⚙️ Configuration

### General Setup

1. Navigate to **Pro Mail SMTP → Settings**
2. Configure your default "From Email" and "From Name"
3. Choose whether to enable email summaries and set your preferred frequency

### Adding Email Providers

1. Go to **Pro Mail SMTP → Providers**
2. Click **Add Provider**
3. Select your email service provider
4. Enter your credentials:
   - For SMTP: Server, port, username, password, encryption type
   - For API-based services: API key and required settings
   - For OAuth services: Follow the authentication flow
5. Set a priority level for each provider (lower numbers = higher priority)
6. Test the connection before saving

### Email Routing (Optional)

1. Navigate to **Pro Mail SMTP → Email Router**
2. Create rules to route specific emails through particular providers
3. Set conditions based on recipient email, source plugin, or other factors
4. Use advanced operators like "contains", "starts with", "regex match", etc.
5. Configure custom sender email and name for specific routing conditions

### Proactive Email Alerts Setup

1. Navigate to **Pro Mail SMTP → Alerts**
2. Click **Add New Alert** to create your first alert configuration
3. Choose your notification channel:
   - **Slack**: Enter your Slack webhook URL
   - **Discord**: Enter your Discord webhook URL
   - **Microsoft Teams**: Enter your Teams webhook URL
   - **Custom Webhook**: Enter any custom webhook endpoint
4. Set your **failure threshold**:
   - `0` = Immediate alerts for every failure
   - `1+` = Consolidated alerts when threshold is reached
5. Configure alert settings and test the connection
6. Enable the alert and save your configuration

**Alert Features:**
- **Smart Thresholds**: Prevent notification spam with configurable failure thresholds
- **Consolidated Reports**: When multiple failures occur, get summarized reports instead of individual alerts
- **Rich Formatting**: Alerts include detailed failure information, provider details, and site context
- **Test Functionality**: Verify your alert configuration with test notifications

## 📊 Monitoring Your Emails

### Email Logs

Access detailed logs of all emails sent through the plugin:

1. Go to **Pro Mail SMTP → Email Logs**
2. View status, recipient, subject, and timestamp
3. Click on any email to see its full details including headers and content
4. **Resend failed emails** directly from the log with different providers
5. Filter logs by date range, status, and provider
6. Export logs for external analysis

### Provider Analytics

View comprehensive performance metrics for each provider:

1. Navigate to **Pro Mail SMTP → Providers Logs**
2. See delivery rates, bounces, and provider-specific metrics
3. **Real-time provider data**: Get live analytics from Gmail, Mailgun, SendGrid, and other API-based providers
4. **Advanced filtering**: Filter by date range, provider, and email status
5. **Detailed insights**: View individual email analytics including send time, recipient, and delivery status
6. **Performance comparison**: Compare different providers' performance side by side

### Email Failure Alerts

Stay informed about email delivery issues:

1. Navigate to **Pro Mail SMTP → Alerts**
2. View all configured alert channels and their status
3. **Real-time notifications**: Get instant alerts when emails fail to deliver
4. **Threshold management**: Configure when to receive alerts (immediate or after X failures)
5. **Multi-channel support**: Receive alerts via Slack, Discord, Teams, or custom webhooks
6. **Rich alert content**: Detailed failure information with context and troubleshooting hints

## 🧩 Advanced Usage

### Supported Email Providers

**API-Based Providers (with Analytics Support):**
- **Gmail**: OAuth 2.0 authentication, full analytics, attachment support
- **Microsoft Outlook**: OAuth 2.0 authentication, enterprise-grade security
- **SendGrid**: API-based sending, detailed analytics, high deliverability
- **Mailgun**: Powerful API, real-time analytics, webhook support
- **Postmark**: Transactional email specialist, bounce tracking
- **SparkPost**: High-volume sending, advanced analytics
- **Brevo** (formerly Sendinblue): Marketing + transactional emails
- **TurboSMTP**: Reliable SMTP service with API features
- **SMTP2GO**: Global infrastructure, detailed reporting

**SMTP-Based Providers:**
- **Generic SMTP**: Support for any SMTP server
- **Custom SMTP configurations**: Full control over server settings

### OAuth Authentication Setup

For Gmail and Outlook providers:

1. **Gmail OAuth Setup**:
   - Configure your Google Cloud Console project
   - Enable Gmail API
   - Create OAuth 2.0 credentials
   - Follow the authentication flow in the plugin

2. **Outlook OAuth Setup**:
   - Register your application in Azure AD
   - Configure Microsoft Graph permissions
   - Complete the OAuth flow through the plugin interface

### Email Routing Advanced Features

**Conditional Logic Operators:**
- `Is` / `Is not`: Exact matching
- `Contains` / `Does not contain`: Substring matching
- `Starts with` / `Ends with`: Position-based matching
- `Regex match` / `Regex not match`: Pattern matching
- `Is empty` / `Is not empty`: Field presence checking

**Routing Conditions:**
- **To**: Route based on recipient email addresses
- **Subject**: Route based on email subject content
- **Source App**: Route based on the plugin/application sending the email
- **From Email**: Route based on sender email address
- **From Name**: Route based on sender name
- **CC/BCC**: Route based on carbon copy recipients
- **Reply To**: Route based on reply-to address
- **Message Content**: Route based on email body content

### Importing from Other SMTP Plugins

The plugin can automatically detect and import settings from:
- WP Mail SMTP
- Easy SMTP

When detected, you'll see an import banner on the Providers page.

### Data Management

For plugin cleanup or troubleshooting:

1. Go to **Pro Mail SMTP → Settings**
2. Scroll to the Data Management section
3. Use with caution - deletion actions cannot be undone

## � Troubleshooting

### Common Issues

**Email Delivery Problems:**
- Check the **Email Logs** for detailed error messages
- Verify provider credentials and configuration
- Test individual providers using the built-in test functionality
- Review routing conditions that might be interfering with delivery

**OAuth Authentication Issues:**
- Ensure proper OAuth application configuration
- Check that required scopes/permissions are granted
- Verify redirect URLs match your site's domain
- Clear browser cache and try re-authentication

**Alert Configuration:**
- Use the **Test Alert** feature to verify webhook connectivity
- Check webhook URL format and permissions
- Verify that your notification service (Slack, Discord, etc.) is properly configured
- Review threshold settings if alerts aren't triggering as expected

**Performance Optimization:**
- Configure appropriate log retention periods
- Use email routing to balance load across multiple providers
- Consider using API-based providers for better performance and analytics

### Getting Help

If you encounter issues:

1. **Check Email Logs**: Most issues are visible in the detailed email logs
2. **Test Provider Connections**: Use the built-in test functionality
3. **Review Alert Configurations**: Test your alert setup with the test feature
4. **Check WordPress Debug Log**: Enable WordPress debugging for detailed error information

## �📄 License

Pro Mail SMTP is licensed under the [GPL-2.0+](http://www.gnu.org/licenses/gpl-2.0.txt)