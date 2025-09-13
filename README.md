# Create A Newsletter With The Block Editor (CANWBE)

A powerful WordPress plugin that enables you to create and send professional newsletters using WordPress's native block editor (Gutenberg).

![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple)
![License](https://img.shields.io/badge/License-GPL%20v2%2B-green)
![Version](https://img.shields.io/badge/Version-1.3-orange)

## 🚀 Features

- **📝 Block Editor Integration**: Create newsletters using WordPress Gutenberg blocks
- **📬 Batch Email System**: Smart batch sending to prevent server overload
- **👥 Subscriber Management**: Easy management of subscriber lists
- **📊 Detailed Logging**: Complete tracking of email sending and errors
- **🔧 SMTP Compatibility**: Works seamlessly with WP Mail SMTP and other email plugins
- **📱 Responsive Design**: Newsletters look great on all devices
- **🎯 Customizable**: Extensive hooks and filters for developers
- **⚡ Performance Optimized**: Efficient processing with minimal server load

## 📋 Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher
- Recommended PHP memory: 128MB minimum

## 🛠️ Installation

### Automatic Installation
1. Go to **Plugins → Add New** in your WordPress admin
2. Search for "Create A Newsletter With The Block Editor"
3. Click **Install Now** and then **Activate**

### Manual Installation
1. Download the plugin from this repository
2. Upload the plugin folder to `/wp-content/plugins/`
3. Activate the plugin through the **Plugins** menu in WordPress

## 🎯 Quick Start

1. **Create a Newsletter**: Go to **Newsletter → Add New**
2. **Design Your Content**: Use any Gutenberg blocks to create your newsletter
3. **Preview & Send**: Preview your newsletter and publish to send

## 📖 Documentation

### English Documentation
- [Complete Documentation (English)](./docs/documentation-en.md)
- [Resend Guide (English)](./docs/resend-guide-en.md)

### Spanish Documentation
- [Documentación Completa (Español)](./docs/documentacion-es.md)
- [Guía de Reenvío (Español)](./docs/guia-reenvio-es.md)

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

## 🎨 Hooks & Filters

### Available Filters
- `canwbe_newsletter_subscribers` - Modify subscriber list
- `canwbe_email_content` - Modify email content
- `canwbe_email_subject` - Modify email subject
- `canwbe_batch_size` - Change batch size
- `canwbe_batch_delay` - Change delay between batches
- `canwbe_email_template` - Customize email HTML template

### Available Actions
- `canwbe_before_send_newsletter` - Before sending newsletter
- `canwbe_after_send_newsletter` - After sending newsletter
- `canwbe_batch_completed` - When batch completes
- `canwbe_email_failed` - When email fails
- `canwbe_batch_cancelled` - When batch is cancelled

## 📊 Batch Email System

The plugin includes an intelligent batch email system that:

- **Prevents server overload** by sending emails in small batches
- **Provides real-time progress tracking** through the admin interface
- **Handles failed emails** with automatic retry mechanisms
- **Logs detailed information** for troubleshooting
- **Allows batch cancellation** for better control

### Default Configuration:
- **Emails per batch**: 10
- **Delay between batches**: 30 seconds
- **Maximum retries**: 3
- **Retry delay**: 5 minutes

## 🛠️ Troubleshooting

### Common Issues

#### "Call to undefined method get_batch_size()"
Add these methods to your `batch-email-sender.php`:

```php
public static function get_batch_size() {
    return apply_filters('canwbe_batch_size', self::BATCH_SIZE);
}

public static function get_batch_delay() {
    return apply_filters('canwbe_batch_delay', self::BATCH_DELAY);
}

public static function get_max_retries() {
    return apply_filters('canwbe_max_retries', self::MAX_RETRIES);
}
```

#### Emails Not Sending
1. Install and configure **WP Mail SMTP**
2. Check your hosting email limits
3. Verify WordPress cron is working
4. Check server error logs

#### Emails Going to Spam
1. Set up proper SPF, DKIM, and DMARC records
2. Use a professional SMTP service
3. Include unsubscribe links
4. Avoid spam trigger words in subjects

## 🔍 Monitoring

### Admin Interface
Access **Newsletter → Email Batches** to monitor:

- **Active batches** and their progress
- **Email statistics** (sent/failed/total)
- **Real-time status updates**
- **Batch cancellation options**

### Logging
The plugin logs detailed information about:
- Batch creation and completion
- Individual email sending results
- Error messages and retry attempts
- Performance metrics

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
# Set up your local WordPress development environment
```

## 📝 Changelog

### Version 1.3 (Current)
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

### Feature Requests
We love hearing from users! If you have ideas for new features:
1. Check existing issues to avoid duplicates
2. Create a detailed issue describing your use case
3. Explain how it would benefit other users

## 🙏 Acknowledgments

- Thanks to the WordPress community for feedback and contributions
- Special thanks to contributors who helped improve the codebase
- Inspired by the need for a simple, powerful newsletter solution for WordPress

---

**Made with ❤️ for the WordPress community**

[![Follow on GitHub](https://img.shields.io/github/followers/flabernardez?style=social)](https://github.com/flabernardez)
[![Star this repo](https://img.shields.io/github/stars/flabernardez/create-a-newsletter-with-the-block-editor?style=social)](https://github.com/flabernardez/create-a-newsletter-with-the-block-editor)
