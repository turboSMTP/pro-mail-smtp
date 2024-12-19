
# Free Mail SMTP Plugin

Free Mail SMTP Plugin allows you to send emails using various email service providers. This plugin is designed to be easy to configure and use, providing a reliable way to send emails from your WordPress site.

## Features

Supports multiple email service providers:

- TurboSMTP
- Brevo
- SMTP2GO

Easy configuration through the WordPress admin interface

Email logging and analytics

Secure connection using OAuth for Gmail

## Installation

1. Download the plugin zip file.
2. Go to your WordPress admin dashboard.
3. Navigate to **Plugins > Add New**.
4. Click on **Upload Plugin** and choose the downloaded zip file.
5. Click **Install Now** and then **Activate**.

## Configuration

1. Go to **Settings > Free Mail SMTP**.
2. Configure the **From Email** and **From Name** fields.
3. Add and configure your email providers:
   - Click on **Add Provider**.
   - Select the provider and fill in the required details.
   - Save the settings.

## Usage

Once configured, the plugin will automatically use the selected email provider to send emails from your WordPress site.

## Priority

Each email connection you configure will be assigned a priority level, establishing a hierarchical backup system. If the primary connection fails, the system automatically attempts to send through the next highest-priority connection, ensuring reliable email delivery.

## Email Logs

You can view the email logs by navigating to **Email Logs** in the WordPress admin menu. The logs provide detailed information about each email sent attempt from your connections, including the status and any errors.

## Providers Logs

The plugin provides your connections email logs fetched by their API. Navigate to **Providers Logs** in the WordPress admin menu to view detailed logs.