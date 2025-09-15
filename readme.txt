=== Create a Newsletter with the Block Editor ===
Contributors: flabernardez
Donate link: https://flabernardez.com/donate
Tags: newsletter, email, subscribers, substack, block-editor, gutenberg, email-marketing, smtp, batch-sending
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.4.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create and send professional newsletters using WordPress's native Block Editor. Features batch sending, subscriber management, and WP Mail SMTP integration.

== Description ==

**Create a Newsletter with the Block Editor** transforms your WordPress site into a powerful newsletter platform. Build beautiful newsletters using the familiar WordPress Block Editor and send them to your subscribers with intelligent batch processing.

= 🚀 Key Features =

* **Block Editor Integration**: Use any Gutenberg blocks to create rich newsletter content
* **Batch Email System**: Smart batch sending prevents server overload
* **Subscription Form Block**: Built-in Gutenberg block for easy subscriber collection
* **Form Plugin Integration**: Works with WPForms, Gravity Forms, Contact Form 7, and others
* **WP Mail SMTP Integration**: Works seamlessly with external email services
* **Subscriber Management**: Easy management of subscriber lists with role-based targeting
* **Basic Analytics**: Track sent emails and open rates
* **Responsive Design**: Newsletters look great on all devices
* **Performance Optimized**: Efficient processing with minimal server load
* **Developer Friendly**: Extensive hooks and filters for customization

= 📧 Intelligent Email Delivery =

* **Batch Processing**: Sends emails in small batches to prevent server overload
* **Automatic Retries**: Failed emails are automatically retried
* **Real-time Monitoring**: Track sending progress in the admin interface
* **SMTP Compatibility**: Works with WP Mail SMTP and other email plugins
* **Server Cron Support**: Recommended setup for reliable delivery

= 👥 Subscriber Management =

* **Built-in Subscription Form**: Add subscription forms anywhere with Gutenberg block
* **Role-based Targeting**: Send to specific user role groups
* **Bulk Actions**: Easily manage subscribers in WordPress user interface
* **CSV Export**: Export subscriber lists for backup or migration
* **Secure Unsubscribe**: One-click unsubscribe with secure token system

= 📊 Analytics & Monitoring =

* **Sending Statistics**: Track successful and failed email deliveries
* **Open Rate Tracking**: Monitor email opens via WP Mail SMTP integration
* **Detailed Logging**: Complete logs for troubleshooting and analysis
* **Batch Monitoring**: Real-time progress tracking for email campaigns

= 🔧 Technical Requirements =

**Strongly Recommended:**
* **WP Mail SMTP Plugin**: For reliable email delivery
* **External SMTP Service**: Gmail, SendGrid, Mailgun, Amazon SES, etc.
* **Server Cron Job**: For reliable batch processing (see setup instructions)

**Minimum Requirements:**
* WordPress 5.0 or higher
* PHP 7.4 or higher
* MySQL 5.6 or higher

= 📖 Setup Instructions =

**Essential Setup Steps:**

1. **Install WP Mail SMTP**: Configure with a reliable external email service
2. **Set up Server Cron**: Create a server cron job for reliable email delivery:
   ```
   */5 * * * * wget -q -O - https://yoursite.com/wp-cron.php?doing_wp_cron >/dev/null 2>&1
   ```
3. **Configure Email Settings**: Go to Newsletter → Email Settings to configure batch sizes and delays
4. **Add Subscription Forms**: Use the Newsletter Subscription Form block on your pages

**Email Service Recommendations:**
* Gmail SMTP (free for low volume)
* SendGrid (reliable with good free tier)
* Mailgun (developer-friendly)
* Amazon SES (cost-effective for high volume)

= 🎯 Perfect For =

* **Content Creators** building email lists
* **Small Businesses** sending customer updates
* **Organizations** communicating with members
* **Bloggers** expanding reach beyond RSS
* **Developers** needing a customizable newsletter solution

= 🔍 Logging & Debugging =

The plugin includes comprehensive logging accessible at **Newsletter → Email Logs**:
* Batch processing status and progress
* Individual email success/failure tracking
* Integration with WP Mail SMTP logs
* Detailed error reporting for troubleshooting

= 🌟 Why Choose This Plugin? =

Unlike complex email marketing platforms, this plugin:
* ✅ Integrates seamlessly with WordPress
* ✅ No monthly subscription fees
* ✅ Uses familiar Block Editor interface
* ✅ Maintains full control of your subscriber data
* ✅ Works with your existing WordPress setup
* ✅ Follows WordPress coding standards

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin
2. Navigate to **Plugins → Add New**
3. Search for "Create a Newsletter with the Block Editor"
4. Click **Install Now** and then **Activate**

= Manual Installation =

1. Download the plugin zip file
2. Upload via **Plugins → Add New → Upload Plugin**
3. Activate the plugin through the **Plugins** menu

= Essential Post-Installation Setup =

**Step 1: Install WP Mail SMTP**
1. Install the WP Mail SMTP plugin
2. Configure with an external email service (Gmail, SendGrid, etc.)
3. Test email sending to ensure it works

**Step 2: Set Up Server Cron (Highly Recommended)**
Add this to your server's crontab:
```
*/5 * * * * wget -q -O - https://yoursite.com/wp-cron.php?doing_wp_cron >/dev/null 2>&1
```

Or disable WordPress cron and use this instead:
```
# In wp-config.php:
define('DISABLE_WP_CRON', true);

# In crontab:
*/5 * * * * /usr/bin/php /path/to/wordpress/wp-cron.php >/dev/null 2>&1
```

**Step 3: Configure Plugin Settings**
1. Go to **Newsletter → Email Settings**
2. Configure batch sizes and delays based on your server capacity
3. Enable WP Mail SMTP integration if using that plugin

**Step 4: Add Subscription Forms**
1. Edit any page or post
2. Add the "Newsletter Subscription Form" block
3. Customize the form text and styling

== Frequently Asked Questions ==

= Do I need WP Mail SMTP? =

While not required, **WP Mail SMTP is strongly recommended** for reliable email delivery. Without it, emails may not be delivered or could end up in spam folders.

= Why do I need a server cron job? =

WordPress cron can be unreliable for batch email processing. A server cron job ensures newsletters are sent consistently and reliably.

= Can I use this with my existing email service? =

Yes! The plugin works with any SMTP service supported by WP Mail SMTP, including Gmail, SendGrid, Mailgun, Amazon SES, and others.

= How do I add subscribers? =

You can:
* Use the built-in subscription form block
* Manually add users with the "Newsletter Subscriber" role
* Use any registration form plugin that assigns the newsletter subscriber role
* Import users and assign them the newsletter subscriber role

= What if my emails go to spam? =

To improve deliverability:
* Use WP Mail SMTP with a professional email service
* Set up SPF, DKIM, and DMARC records for your domain
* Avoid spam trigger words in subject lines
* Include unsubscribe links (automatically added by plugin)

= Can I customize the newsletter design? =

The plugin sends clean, responsive HTML emails. You can:
* Customize the "View on Web" link text
* Add intro messages to each newsletter
* Use any Gutenberg blocks for content creation
* The design automatically adapts to be mobile-friendly

= How do I monitor email sending? =

Go to **Newsletter → Email Batches** to monitor:
* Real-time sending progress
* Success and failure statistics
* Batch cancellation and restart options
* Detailed logs for troubleshooting

= Is this GDPR compliant? =

The plugin includes:
* Secure unsubscribe mechanisms
* Minimal data storage
* No external data sharing
* However, you should review local privacy regulations and add appropriate privacy notices

= Can I segment my audience? =

Yes! You can send newsletters to specific user roles, allowing you to segment your audience and send targeted content.

== Screenshots ==

1. Newsletter editor with Block Editor integration and settings panel
2. Built-in subscription form block with customization options
3. Email batch monitoring with real-time progress tracking
4. Newsletter management interface showing sending status
5. Email settings page with batch configuration options
6. Clean, responsive newsletter email design
7. Subscriber management with role-based targeting

== Changelog ==

= 1.4.0 =
* Added Newsletter Subscription Form Gutenberg block
* Introduced configurable "View on Web" link settings
* Added basic analytics and open rate tracking
* Enhanced WP Mail SMTP integration with detailed logging
* Improved internationalization with proper text domain usage
* Added WordPress.org guidelines compliance
* Updated minimum WordPress version to 5.0
* Better error handling and user feedback
* Comprehensive documentation and setup guides
* Removed default newsletter template placeholders for cleaner editor experience

= 1.3.0 =
* Added intelligent batch email sending system
* Enhanced WP Mail SMTP compatibility and logging
* Improved admin interface with real-time monitoring
* Added detailed logging and error handling
* Performance optimizations for large subscriber lists
* Batch cancellation and restart functionality
* Automatic retry mechanism for failed emails
* Admin notifications for batch completion status

= 1.2.0 =
* Full WordPress Block Editor integration
* Enhanced email template system
* Improved subscriber role management
* Bug fixes and stability improvements
* Better mobile responsiveness

= 1.1.0 =
* Initial public release
* Basic newsletter creation and sending
* Subscriber role system
* Unsubscribe functionality
* Simple email templates

== Upgrade Notice ==

= 1.4.0 =
Major update with new subscription form block, enhanced analytics, and improved SMTP integration. Recommended to install WP Mail SMTP and set up server cron job for best performance. Backup your site before upgrading.

= 1.3.0 =
Important update adding batch email system for better reliability. Backup your site before upgrading. All existing newsletters and subscribers will continue to work normally.

== Additional Information ==

= Technical Support =

For technical support and troubleshooting:
* Use the **Debug Tool** button in Newsletter → Email Batches for system diagnostics
* Access debug tool directly: `yoursite.com/wp-admin/admin.php?page=canwbe-debug-batch`
* Review **Newsletter → Email Batches** for batch monitoring
* Use WordPress.org support forums for general questions
* Report bugs and request features on GitHub
* Enable WP_DEBUG for detailed server logging

= Performance Recommendations =

For optimal performance:
* Use WP Mail SMTP with external SMTP service
* Set up server cron job instead of relying on WordPress cron
* Start with smaller batch sizes (10-25 emails) and increase gradually
* Monitor server resources during initial newsletter sends
* Use email services with good delivery rates (SendGrid, Mailgun, etc.)

= Integration with Other Plugins =

The plugin works well with:
* **WP Mail SMTP** - Essential for reliable email delivery
* **Contact Form 7** - Can direct signups to newsletter subscriber role
* **WPForms** - User registration forms with role assignment
* **Gravity Forms** - Advanced form handling with user creation
* **All caching plugins** - Newsletter sending doesn't interfere with caching

= Developer Information =

* Built with modern React components for the Block Editor
* Follows WordPress coding standards and best practices
* Extensively documented with hooks and filters
* Translation ready with proper text domain
* Open source development on GitHub
* Comprehensive API for custom integrations

= Privacy & Data Handling =

This plugin:
* Stores subscriber data locally in your WordPress database
* Does not share data with external services (except your chosen SMTP service)
* Provides secure unsubscribe mechanisms with unique tokens
* Logs minimal data necessary for functionality
* Does not track user behavior beyond basic email analytics
* Complies with data protection best practices

= Credits =

Developed with ❤️ by Flavia Bernárdez Rodríguez for the WordPress community.

Special thanks to:
* WordPress core team for the Block Editor framework
* WP Mail SMTP team for excellent email delivery tools
* The WordPress community for feedback and contributions
