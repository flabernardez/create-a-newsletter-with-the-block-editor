# Create A Newsletter With The Block Editor (CANWBE)

A powerful WordPress plugin that enables you to create and send professional newsletters using WordPress's native block editor (Gutenberg).

![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple)
![License](https://img.shields.io/badge/License-GPL%20v2%2B-green)
![Version](https://img.shields.io/badge/Version-1.4-orange)

## 🚀 Features

- **📝 Block Editor Integration**: Create newsletters using WordPress Gutenberg blocks
- **📬 Batch Email System**: Smart batch sending to prevent server overload
- **👥 Subscriber Management**: Easy management of subscriber lists
- **📊 Detailed Logging**: Complete tracking of email sending and errors
- **🔧 SMTP Compatibility**: Works seamlessly with WP Mail SMTP and other email plugins
- **📱 Responsive Design**: Newsletters look great on all devices
- **🎯 Customizable**: Extensive hooks and filters for developers
- **⚡ Performance Optimized**: Efficient processing with minimal server load
- **📝 Subscription Form Block**: Built-in subscription form Gutenberg block
- **📈 Basic Analytics**: Track sent emails and open rates

## 📋 Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher
- Recommended PHP memory: 128MB minimum
- **WP Mail SMTP plugin** (strongly recommended)
- **Server cron job** (recommended for reliable email delivery)

## 🛠️ Installation

### Automatic Installation
1. Go to **Plugins → Add New** in your WordPress admin
2. Search for "Create A Newsletter With The Block Editor"
3. Click **Install Now** and then **Activate**

### Manual Installation
1. Download the plugin from this repository
2. Upload the plugin folder to `/wp-content/plugins/`
3. Activate the plugin through the **Plugins** menu in WordPress

## ⚙️ Essential Setup

### 1. Install WP Mail SMTP (Recommended)
This plugin works best with an external email service:

1. Install the [WP Mail SMTP plugin](https://wordpress.org/plugins/wp-mail-smtp/)
2. Configure it with a reliable email service like:
   - Gmail SMTP
   - SendGrid
   - Mailgun
   - Amazon SES
   - Any other SMTP service

### 2. Server Cron Job (Highly Recommended)
For reliable email delivery, set up a server cron job instead of relying on WordPress cron:

```bash
# Add this to your server's crontab
*/5 * * * * wget -q -O - https://yoursite.com/wp-cron.php?doing_wp_cron >/dev/null 2>&1
```

Or disable WordPress cron and use this instead:
```bash
# In wp-config.php add:
define('DISABLE_WP_CRON', true);

# Then add to crontab:
*/5 * * * * /usr/bin/php /path/to/wordpress/wp-cron.php >/dev/null 2>&1
```

## 🎯 Quick Start

1. **Configure Email Settings**: Go to **Newsletter → Email Settings**
2. **Create a Newsletter**: Go to **Newsletter → Add New**
3. **Design Your Content**: Use any Gutenberg blocks to create your newsletter
4. **Add Subscribers**: Use the built-in subscription form block or manage subscribers manually
5. **Preview & Send**: Preview your newsletter and publish to send

## 📖 Documentation

### English Documentation
- [Complete Documentation (English)](./docs/documentation-en.md)
- [Resend Guide (English)](./docs/resend-guide-en.md)

### Spanish Documentation
- [Documentación Completa (Español)](./docs/documentacion-es.md)
- [Guía de Reenvío (Español)](./docs/guia-reenvio-es.md)

## 📝 Adding Subscribers

### Method 1: Subscription Form Block
1. Edit any page or post
2. Add the "Newsletter Subscription Form" block
3. Customize the form text and styling
4. Users can subscribe directly through the form

### Method 2: Manual Management
1. Go to **Newsletter → Subscribers**
2. Add subscribers manually
3. Import/export subscriber lists

### Method 3: Integration with Registration Forms
The plugin creates a `newsletter_subscriber` role. Any registration form plugin that can assign this role will work:

- **WPForms**: Set user registration to assign "Newsletter Subscriber" role
- **Gravity Forms**: Use User Registration add-on with role assignment
- **Contact Form 7**: Use additional plugins for user registration
- **Ninja Forms**: Use User Management add-on

## 📝 Adding Subscribers

### Method 1: Subscription Form Block
1. Edit any page or post
2. Add the "Newsletter Subscription Form" block
3. Customize the form text and styling
4. Users can subscribe directly through the form

### Method 2: Manual Management
1. Go to **Newsletter → Subscribers**
2. Add subscribers manually
3. Import/export subscriber lists

### Method 3: Integration with Registration Forms
The plugin creates a `newsletter_subscriber` role. Any registration form plugin that can assign this role will work:

- **WPForms**: Set user registration to assign "Newsletter Subscriber" role
- **Gravity Forms**: Use User Registration add-on with role assignment
- **Contact Form 7**: Use additional plugins for user registration
- **Ninja Forms**: Use User Management add-on

## 🔧 Configuration

### Basic Setup
```php
// Configure sender details
add_filter('canwbe_sender_email', function() {
    return 'newsletters@yoursite.com';
});

add_filter('canwbe_sender_name', function() {
    return 'Your Site Name';
});
```

### Batch Settings
```php
// Customize batch settings
add_filter('canwbe_batch_size', function($size) {
    return 25; // 25 emails per batch
});

add_filter('canwbe_batch_delay', function($delay) {
    return 60; // 60 seconds between batches
});
```

### Web View Link Configuration
```php
// Customize the "View on Web" link text
add_filter('canwbe_web_view_text', function($text) {
    return 'Read online version';
});

// Disable the web view link entirely
add_filter('canwbe_show_web_view_link', '__return_false');
```

## 📊 Analytics & Logging

### Built-in Analytics
Access basic analytics at **Newsletter → Analytics**:
- Total newsletters sent
- Delivery statistics
- Open rate tracking (via WP Mail SMTP integration)

### Logging System
Monitor email sending at **Newsletter → Email Logs**:
- Real-time batch processing status
- Individual email success/failure logs
- Integration with WP Mail SMTP logs
- Detailed error reporting

### WP Mail SMTP Integration
When WP Mail SMTP is active, this plugin:
- Logs all newsletter emails in WP Mail SMTP
- Tracks open rates through WP Mail SMTP
- Provides detailed delivery reports
- Integrates with email service analytics

## 🎨 Hooks & Filters

### Available Filters
- `canwbe_newsletter_subscribers` - Modify subscriber list
- `canwbe_email_content` - Modify email content
- `canwbe_email_subject` - Modify email subject
- `canwbe_batch_size` - Change batch size
- `canwbe_batch_delay` - Change delay between batches
- `canwbe_email_template` - Customize email HTML template
- `canwbe_web_view_text` - Customize web view link text
- `canwbe_show_web_view_link` - Show/hide web view link

### Available Actions
- `canwbe_before_send_newsletter` - Before sending newsletter
- `canwbe_after_send_newsletter` - After sending newsletter
- `canwbe_batch_completed` - When batch completes
- `canwbe_email_failed` - When email fails
- `canwbe_batch_cancelled` - When batch is cancelled
- `canwbe_subscriber_added` - When new subscriber is added

## 📊 Batch Email System

The plugin includes an intelligent batch email system that:

- **Prevents server overload** by sending emails in small batches
- **Provides real-time progress tracking** through the admin interface
- **Handles failed emails** with automatic retry mechanisms
- **Logs detailed information** for troubleshooting
- **Allows batch cancellation** for better control
- **Integrates with external SMTP services**

### Default Configuration:
- **Emails per batch**: 10
- **Delay between batches**: 30 seconds
- **Maximum retries**: 3
- **Retry delay**: 5 minutes

## 🛠️ Troubleshooting

### Common Issues

#### Emails Not Sending
1. **Install and configure WP Mail SMTP** (most important step)
2. Set up a server cron job for reliable scheduling
3. Check your hosting email limits
4. Verify WordPress cron is working
5. Check server error logs

#### Emails Going to Spam
1. Set up proper SPF, DKIM, and DMARC records
2. Use a professional SMTP service (Gmail, SendGrid, etc.)
3. Include unsubscribe links (automatically added)
4. Avoid spam trigger words in subjects
5. Maintain a clean subscriber list

#### Performance Issues
1. Reduce batch size in **Newsletter → Email Settings**
2. Increase delay between batches
3. Ensure server cron is set up properly
4. Monitor server resources during sending

### Debug Mode
Add this to wp-config.php for detailed logging:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Access debug tools at **Newsletter → Debug Tools** for:
- Real-time batch system monitoring
- Manual batch inspection and restart
- System compatibility checks
- Scheduled events analysis

## 🔍 Monitoring

### Admin Interface
Access comprehensive monitoring at:

- **Newsletter → Email Batches**: Real-time batch processing
- **Newsletter → Email Logs**: Detailed sending logs
- **Newsletter → Analytics**: Basic metrics and reports
- **Newsletter → Subscribers**: Subscriber management

### WP Mail SMTP Integration
When using WP Mail SMTP, additional monitoring is available:
- Email service delivery reports
- Advanced open rate tracking
- Bounce and complaint handling
- Professional email analytics

## 🔒 Security & Privacy

- All subscriber data is stored securely in WordPress
- Unsubscribe tokens are randomly generated and unique
- No external services required (except SMTP)
- GDPR compliant unsubscribe process
- All inputs are properly sanitized and escaped

## 🤝 Contributing

We welcome contributions! Please:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Setup
```bash
git clone https://github.com/flabernardez/create-a-newsletter-with-the-block-editor.git
cd create-a-newsletter-with-the-block-editor
npm install
npm run build
```

## 📝 Changelog

### Version 1.4 (Current)
- ✅ Added subscription form Gutenberg block
- ✅ Configurable web view link text and visibility
- ✅ Basic analytics and open rate tracking
- ✅ Enhanced WP Mail SMTP integration
- ✅ Improved logging system
- ✅ WordPress.org guidelines compliance
- ✅ Better internationalization

### Version 1.3
- ✅ Added batch email sending system
- ✅ Enhanced WP Mail SMTP compatibility
- ✅ Improved admin interface with real-time monitoring
- ✅ Detailed logging and error handling
- ✅ Performance optimizations

### Version 1.2
- ✅ Full block editor integration
- ✅ Email template improvements
- ✅ Bug fixes and stability improvements

### Version 1.1
- ✅ Initial release
- ✅ Basic newsletter functionality
- ✅ Subscriber management
- ✅ Simple email sending

## 📄 License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.

## 📞 Support

### Getting Help
- **Documentation**: Check our comprehensive docs above
- **Issues**: [Create an issue](https://github.com/flabernardez/create-a-newsletter-with-the-block-editor/issues) for bugs or feature requests
- **WordPress.org**: Visit the [plugin support forum](https://wordpress.org/support/plugin/create-a-newsletter-with-the-block-editor/)

### Before Requesting Support
1. Ensure WP Mail SMTP is installed and configured
2. Check that your server cron job is working
3. Review the troubleshooting section above
4. Enable debug logging and check for errors

### Feature Requests
We love hearing from users! If you have ideas for new features:
1. Check existing issues to avoid duplicates
2. Create a detailed issue describing your use case
3. Explain how it would benefit other users

## 🙏 Acknowledgments

- Thanks to the WordPress community for feedback and contributions
- Special thanks to contributors who helped improve the codebase
- Inspired by the need for a simple, powerful newsletter solution for WordPress
- Built with WordPress coding standards and best practices

---

**Made with ❤️ for the WordPress community**

[![Follow on GitHub](https://img.shields.io/github/followers/flabernardez?style=social)](https://github.com/flabernardez)
[![Star this repo](https://img.shields.io/github/stars/flabernardez/create-a-newsletter-with-the-block-editor?style=social)](https://github.com/flabernardez/create-a-newsletter-with-the-block-editor)
