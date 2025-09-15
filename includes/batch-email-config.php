<?php
/**
 * Batch Email Configuration Page
 *
 * Allows admins to configure batch email settings
 *
 * @package Create_A_Newsletter_With_The_Block_Editor
 * @since 1.3
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class CANWBE_Batch_Config {

    /**
     * Initialize configuration
     */
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_config_menu'));
        add_action('admin_init', array(__CLASS__, 'register_settings'));
    }

    /**
     * Get configuration values with defaults
     */
    public static function get_config() {
        return array(
            'batch_size' => get_option('canwbe_batch_size', 10),
            'batch_delay' => get_option('canwbe_batch_delay', 30),
            'max_retries' => get_option('canwbe_max_retries', 3),
            'retry_delay' => get_option('canwbe_retry_delay', 300),
            'admin_notifications' => get_option('canwbe_admin_notifications', 'failures_only'),
            'wp_mail_smtp_logging' => get_option('canwbe_wp_mail_smtp_logging', 'yes'),
            'web_view_enabled' => get_option('canwbe_web_view_enabled', 'yes'),
            'web_view_text' => get_option('canwbe_web_view_text', __('View on the web with graphics and images', 'create-a-newsletter-with-the-block-editor')),
        );
    }

    /**
     * Add configuration menu
     */
    public static function add_config_menu() {
        add_submenu_page(
            'edit.php?post_type=newsletter',
            __('Email Settings', 'create-a-newsletter-with-the-block-editor'),
            __('Email Settings', 'create-a-newsletter-with-the-block-editor'),
            'manage_options',
            'canwbe-email-settings',
            array(__CLASS__, 'settings_page')
        );
    }

    /**
     * Register settings
     */
    public static function register_settings() {
        register_setting('canwbe_email_settings', 'canwbe_batch_size', array(
            'type' => 'integer',
            'default' => 10,
            'sanitize_callback' => array(__CLASS__, 'sanitize_batch_size')
        ));

        register_setting('canwbe_email_settings', 'canwbe_batch_delay', array(
            'type' => 'integer',
            'default' => 30,
            'sanitize_callback' => array(__CLASS__, 'sanitize_batch_delay')
        ));

        register_setting('canwbe_email_settings', 'canwbe_max_retries', array(
            'type' => 'integer',
            'default' => 3,
            'sanitize_callback' => array(__CLASS__, 'sanitize_max_retries')
        ));

        register_setting('canwbe_email_settings', 'canwbe_retry_delay', array(
            'type' => 'integer',
            'default' => 300,
            'sanitize_callback' => array(__CLASS__, 'sanitize_retry_delay')
        ));

        register_setting('canwbe_email_settings', 'canwbe_admin_notifications');
        register_setting('canwbe_email_settings', 'canwbe_wp_mail_smtp_logging');

        // Web view settings
        register_setting('canwbe_email_settings', 'canwbe_web_view_enabled', array(
            'type' => 'string',
            'default' => 'yes',
            'sanitize_callback' => array(__CLASS__, 'sanitize_checkbox')
        ));

        register_setting('canwbe_email_settings', 'canwbe_web_view_text', array(
            'type' => 'string',
            'default' => __('View on the web with graphics and images', 'create-a-newsletter-with-the-block-editor'),
            'sanitize_callback' => 'sanitize_text_field'
        ));
    }

    /**
     * Sanitization callbacks
     */
    public static function sanitize_batch_size($value) {
        $value = intval($value);
        return ($value >= 1 && $value <= 100) ? $value : 10;
    }

    public static function sanitize_batch_delay($value) {
        $value = intval($value);
        return ($value >= 10 && $value <= 600) ? $value : 30;
    }

    public static function sanitize_max_retries($value) {
        $value = intval($value);
        return ($value >= 0 && $value <= 10) ? $value : 3;
    }

    public static function sanitize_retry_delay($value) {
        $value = intval($value);
        return ($value >= 60 && $value <= 3600) ? $value : 300;
    }

    /**
     * Sanitize checkbox values
     */
    public static function sanitize_checkbox($value) {
        return ($value === 'yes') ? 'yes' : 'no';
    }

    /**
     * Settings page
     */
    public static function settings_page() {
        if (isset($_POST['submit'])) {
            // Handle form submission
            update_option('canwbe_batch_size', self::sanitize_batch_size($_POST['canwbe_batch_size']));
            update_option('canwbe_batch_delay', self::sanitize_batch_delay($_POST['canwbe_batch_delay']));
            update_option('canwbe_max_retries', self::sanitize_max_retries($_POST['canwbe_max_retries']));
            update_option('canwbe_retry_delay', self::sanitize_retry_delay($_POST['canwbe_retry_delay']));
            update_option('canwbe_admin_notifications', sanitize_text_field($_POST['canwbe_admin_notifications']));
            update_option('canwbe_wp_mail_smtp_logging', sanitize_text_field($_POST['canwbe_wp_mail_smtp_logging']));

            // Web view settings
            update_option('canwbe_web_view_enabled', self::sanitize_checkbox($_POST['canwbe_web_view_enabled']));
            update_option('canwbe_web_view_text', sanitize_text_field($_POST['canwbe_web_view_text']));

            echo '<div class="notice notice-success"><p>' . __('Settings saved successfully!', 'create-a-newsletter-with-the-block-editor') . '</p></div>';
        }

        $config = self::get_config();
        ?>
        <div class="wrap">
            <h1>
                <?php esc_html_e('Email Settings', 'create-a-newsletter-with-the-block-editor'); ?>
                <a href="<?php echo admin_url('admin.php?page=canwbe-debug-batch'); ?>"
                   class="page-title-action debug-button"
                   title="<?php esc_attr_e('Open advanced debugging tools for batch email system', 'create-a-newsletter-with-the-block-editor'); ?>">
                    🔧 <?php esc_html_e('Debug Tools', 'create-a-newsletter-with-the-block-editor'); ?>
                </a>
            </h1>

            <form method="post" action="">
                <?php wp_nonce_field('canwbe_email_settings'); ?>

                <div class="card">
                    <h2><?php esc_html_e('Batch Sending Configuration', 'create-a-newsletter-with-the-block-editor'); ?></h2>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="canwbe_batch_size"><?php esc_html_e('Emails per Batch', 'create-a-newsletter-with-the-block-editor'); ?></label>
                            </th>
                            <td>
                                <input type="number" id="canwbe_batch_size" name="canwbe_batch_size"
                                       value="<?php echo esc_attr($config['batch_size']); ?>"
                                       min="1" max="100" class="small-text" />
                                <p class="description">
                                    <?php esc_html_e('Number of emails to send in each batch (1-100). Lower numbers reduce server load but take longer.', 'create-a-newsletter-with-the-block-editor'); ?>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="canwbe_batch_delay"><?php esc_html_e('Delay Between Batches', 'create-a-newsletter-with-the-block-editor'); ?></label>
                            </th>
                            <td>
                                <input type="number" id="canwbe_batch_delay" name="canwbe_batch_delay"
                                       value="<?php echo esc_attr($config['batch_delay']); ?>"
                                       min="10" max="600" class="small-text" />
                                <span><?php esc_html_e('seconds', 'create-a-newsletter-with-the-block-editor'); ?></span>
                                <p class="description">
                                    <?php esc_html_e('Time to wait between batches (10-600 seconds). Longer delays help avoid rate limits.', 'create-a-newsletter-with-the-block-editor'); ?>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="canwbe_max_retries"><?php esc_html_e('Maximum Retries', 'create-a-newsletter-with-the-block-editor'); ?></label>
                            </th>
                            <td>
                                <input type="number" id="canwbe_max_retries" name="canwbe_max_retries"
                                       value="<?php echo esc_attr($config['max_retries']); ?>"
                                       min="0" max="10" class="small-text" />
                                <p class="description">
                                    <?php esc_html_e('Number of times to retry failed emails (0-10). Set to 0 to disable retries.', 'create-a-newsletter-with-the-block-editor'); ?>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="canwbe_retry_delay"><?php esc_html_e('Retry Delay', 'create-a-newsletter-with-the-block-editor'); ?></label>
                            </th>
                            <td>
                                <input type="number" id="canwbe_retry_delay" name="canwbe_retry_delay"
                                       value="<?php echo esc_attr($config['retry_delay']); ?>"
                                       min="60" max="3600" class="small-text" />
                                <span><?php esc_html_e('seconds', 'create-a-newsletter-with-the-block-editor'); ?></span>
                                <p class="description">
                                    <?php esc_html_e('Time to wait before retrying failed emails (60-3600 seconds).', 'create-a-newsletter-with-the-block-editor'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="card">
                    <h2><?php esc_html_e('Web View Settings', 'create-a-newsletter-with-the-block-editor'); ?></h2>
                    <p><?php esc_html_e('Configure the "View on Web" link that appears in newsletter emails.', 'create-a-newsletter-with-the-block-editor'); ?></p>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e('Enable Web View Link', 'create-a-newsletter-with-the-block-editor'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="canwbe_web_view_enabled" value="yes"
                                        <?php checked($config['web_view_enabled'], 'yes'); ?> />
                                    <?php esc_html_e('Show "View on Web" link in newsletter emails', 'create-a-newsletter-with-the-block-editor'); ?>
                                </label>
                                <p class="description">
                                    <?php esc_html_e('When enabled, a link to view the newsletter on your website will be included in emails.', 'create-a-newsletter-with-the-block-editor'); ?>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="canwbe_web_view_text"><?php esc_html_e('Web View Link Text', 'create-a-newsletter-with-the-block-editor'); ?></label>
                            </th>
                            <td>
                                <input type="text"
                                       id="canwbe_web_view_text"
                                       name="canwbe_web_view_text"
                                       value="<?php echo esc_attr($config['web_view_text']); ?>"
                                       class="regular-text" />
                                <p class="description">
                                    <?php esc_html_e('Text for the web view link. Leave empty to use default text.', 'create-a-newsletter-with-the-block-editor'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="card">
                    <h2><?php esc_html_e('Notifications & Logging', 'create-a-newsletter-with-the-block-editor'); ?></h2>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e('Admin Notifications', 'create-a-newsletter-with-the-block-editor'); ?></th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input type="radio" name="canwbe_admin_notifications" value="all"
                                            <?php checked($config['admin_notifications'], 'all'); ?> />
                                        <?php esc_html_e('All batch completions', 'create-a-newsletter-with-the-block-editor'); ?>
                                    </label><br>

                                    <label>
                                        <input type="radio" name="canwbe_admin_notifications" value="failures_only"
                                            <?php checked($config['admin_notifications'], 'failures_only'); ?> />
                                        <?php esc_html_e('Only when there are failures', 'create-a-newsletter-with-the-block-editor'); ?>
                                    </label><br>

                                    <label>
                                        <input type="radio" name="canwbe_admin_notifications" value="none"
                                            <?php checked($config['admin_notifications'], 'none'); ?> />
                                        <?php esc_html_e('No notifications', 'create-a-newsletter-with-the-block-editor'); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><?php esc_html_e('WP Mail SMTP Integration', 'create-a-newsletter-with-the-block-editor'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="canwbe_wp_mail_smtp_logging" value="yes"
                                        <?php checked($config['wp_mail_smtp_logging'], 'yes'); ?> />
                                    <?php esc_html_e('Enable WP Mail SMTP logging integration', 'create-a-newsletter-with-the-block-editor'); ?>
                                </label>
                                <p class="description">
                                    <?php esc_html_e('When enabled, newsletter emails will be logged in WP Mail SMTP logs (if plugin is active).', 'create-a-newsletter-with-the-block-editor'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="card">
                    <h2><?php esc_html_e('Current System Status', 'create-a-newsletter-with-the-block-editor'); ?></h2>

                    <table class="widefat">
                        <tr>
                            <th><?php esc_html_e('WP Cron Status', 'create-a-newsletter-with-the-block-editor'); ?></th>
                            <td>
                                <?php if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON): ?>
                                    <span style="color: #d63638;">❌ <?php esc_html_e('Disabled', 'create-a-newsletter-with-the-block-editor'); ?></span>
                                    <br><em><?php esc_html_e('WP Cron is disabled. Batch emails may not be sent automatically.', 'create-a-newsletter-with-the-block-editor'); ?></em>
                                <?php else: ?>
                                    <span style="color: #00a32a;">✅ <?php esc_html_e('Active', 'create-a-newsletter-with-the-block-editor'); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <tr>
                            <th><?php esc_html_e('WP Mail SMTP', 'create-a-newsletter-with-the-block-editor'); ?></th>
                            <td>
                                <?php if (function_exists('wp_mail_smtp')): ?>
                                    <span style="color: #00a32a;">✅ <?php esc_html_e('Installed', 'create-a-newsletter-with-the-block-editor'); ?></span>
                                <?php else: ?>
                                    <span style="color: #dba617;">⚠️ <?php esc_html_e('Not installed', 'create-a-newsletter-with-the-block-editor'); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <tr>
                            <th><?php esc_html_e('Estimated Sending Time', 'create-a-newsletter-with-the-block-editor'); ?></th>
                            <td>
                                <?php
                                $subscriber_count = count(get_users(array('role' => 'newsletter_subscriber')));
                                $batches = ceil($subscriber_count / $config['batch_size']);
                                $total_time = ($batches - 1) * $config['batch_delay'];

                                if ($subscriber_count > 0) {
                                    printf(
                                        __('Approximately %s for %d subscribers (%d batches)', 'create-a-newsletter-with-the-block-editor'),
                                        human_time_diff(0, $total_time),
                                        $subscriber_count,
                                        $batches
                                    );
                                } else {
                                    esc_html_e('No subscribers found', 'create-a-newsletter-with-the-block-editor');
                                }
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>

                <?php submit_button(__('Save Settings', 'create-a-newsletter-with-the-block-editor')); ?>
            </form>

            <!-- Debug Tools Info Card -->
            <div class="card debug-info-card">
                <h2>🔧 <?php esc_html_e('Need Help with Batch Issues?', 'create-a-newsletter-with-the-block-editor'); ?></h2>
                <p><?php esc_html_e('If you\'re experiencing problems with batch email sending, use our advanced debugging tools to diagnose the issue.', 'create-a-newsletter-with-the-block-editor'); ?></p>
                <p>
                    <a href="<?php echo admin_url('admin.php?page=canwbe-debug-batch'); ?>" class="button button-secondary">
                        🔍 <?php esc_html_e('Open Debug Tools', 'create-a-newsletter-with-the-block-editor'); ?>
                    </a>
                    <span class="description" style="margin-left: 10px;">
                        <?php esc_html_e('Access system status, batch data inspection, and manual restart options.', 'create-a-newsletter-with-the-block-editor'); ?>
                    </span>
                </p>
            </div>
        </div>

        <style>
            .card {
                background: white;
                border: 1px solid #c3c4c7;
                border-left: 4px solid #72aee6;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
                padding: 1em 2em;
                margin: 20px 0;
            }

            .debug-info-card {
                border-left-color: #dba617;
                background-color: #fffbf0;
            }

            .debug-button {
                background: #dba617 !important;
                border-color: #c18a03 !important;
                color: white !important;
                text-decoration: none !important;
                font-size: 13px !important;
                padding: 4px 8px !important;
                border-radius: 3px !important;
                margin-left: 10px !important;
                display: inline-block !important;
            }

            .debug-button:hover {
                background: #c18a03 !important;
                border-color: #a47b02 !important;
                color: white !important;
            }

            .debug-button:focus {
                box-shadow: 0 0 0 1px #fff, 0 0 0 3px #dba617 !important;
            }
        </style>
        <?php
    }
}

// Initialize batch configuration
CANWBE_Batch_Config::init();
