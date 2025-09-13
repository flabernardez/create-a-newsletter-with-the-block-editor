# Create a Newsletter with the Block Editor

[![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/create-a-newsletter-with-the-block-editor?style=flat-square)](https://wordpress.org/plugins/create-a-newsletter-with-the-block-editor/)
[![WordPress Plugin Downloads](https://img.shields.io/wordpress/plugin/dt/create-a-newsletter-with-the-block-editor?style=flat-square)](https://wordpress.org/plugins/create-a-newsletter-with-the-block-editor/)
[![WordPress Plugin Rating](https://img.shields.io/wordpress/plugin/stars/create-a-newsletter-with-the-block-editor?style=flat-square)](https://wordpress.org/plugins/create-a-newsletter-with-the-block-editor/)
[![License](https://img.shields.io/badge/license-GPL%20v3%2B-blue?style=flat-square)](https://www.gnu.org/licenses/gpl-3.0.html)

Creates beautiful newsletters in Substack style using WordPress Block Editor. Send newsletters to subscribers with custom roles and unsubscribe management.

## 🌟 Features

- **Substack-style Design**: Clean, minimal newsletter layout
- **Block Editor Integration**: Use Gutenberg blocks for rich content
- **Subscriber Management**: Custom subscriber roles and targeting
- **Automatic Sending**: Newsletters sent automatically when published
- **Secure Unsubscribe**: One-click unsubscribe with secure tokens
- **Responsive Design**: Perfect on desktop and mobile
- **Developer Friendly**: Built with modern React and WordPress standards

## 🚀 Quick Start

1. **Install** the plugin from WordPress.org or upload manually
2. **Activate** the plugin in your WordPress admin
3. **Create** your first newsletter using the Block Editor
4. **Configure** settings in the newsletter sidebar panel
5. **Add subscribers** to the "Newsletter Subscriber" role
6. **Publish** to automatically send to all subscribers

## 📖 Documentation

### Creating Your First Newsletter

1. Navigate to **Newsletters** → **Add New** in your WordPress admin
2. Write your newsletter content using any Gutenberg blocks
3. In the sidebar panel, configure:
    - **Intro Message**: Text that appears before your content
    - **Unsubscribe Message**: Custom unsubscribe link text
    - **Recipient Roles**: Which user roles should receive this newsletter

### Managing Subscribers

Subscribers are WordPress users with the "Newsletter Subscriber" role:

- **Add manually**: Create users with the Newsletter Subscriber role
- **Registration**: Allow visitors to register with this role
- **Import**: Use any WordPress user import tool
- **Segment**: Send to specific user roles (subscribers, customers, etc.)

### Customization

The plugin provides hooks for developers:

```php
// Modify newsletter content before sending
add_filter('canwbe_newsletter_content', 'my_newsletter_filter');

// Custom CSS variables for email styling
add_filter('canwbe_css_variables', 'my_css_variables');

// Modify recipient list
add_filter('canwbe_newsletter_recipients', 'my_recipients_filter');
```

## 🔧 System Requirements

- **WordPress**: 5.8 or higher
- **PHP**: 7.4 or higher
- **MySQL**: 5.6 or higher
- **JavaScript**: Enabled for Block Editor

## 🆘 Support

- **Documentation**: Full documentation available in the plugin
- **Community**: Ask questions in [WordPress.org forums](https://wordpress.org/support/plugin/create-a-newsletter-with-the-block-editor/)
- **Issues**: Report bugs on [GitHub](https://github.com/flabernardez/create-a-newsletter-with-the-block-editor)

## 🤝 Contributing

We welcome contributions! Here's how you can help:

1. **Report bugs**: Use GitHub issues for bug reports
2. **Suggest features**: Share your ideas for new features
3. **Submit code**: Fork the repository and submit pull requests
4. **Translate**: Help translate the plugin into your language
5. **Test**: Test new releases and provide feedback

### Development Setup

```bash
# Clone the repository
git clone https://github.com/flabernardez/create-a-newsletter-with-the-block-editor.git

# Install dependencies
npm install

# Start development mode
npm run start

# Build for production
npm run build
```

## 📄 License

This plugin is licensed under the [GPL v3 or later](https://www.gnu.org/licenses/gpl-3.0.html).

## 🙏 Credits

- **Author**: [Flavia Bernárdez Rodríguez](https://flabernardez.com)
- **Inspiration**: Substack's clean newsletter design
- **Built with**: WordPress Block Editor, React, and modern web standards

## 📈 Changelog

### 1.3
- Updated plugin name and branding
- Refactored code with new prefixes
- Added plugin constants
- Improved translation loading
- Enhanced documentation
- Modernized development workflow

### 1.2
- Initial public release
- Complete newsletter system
- Subscriber management
- Block Editor integration

---

**Made with ❤️ for the WordPress community**
