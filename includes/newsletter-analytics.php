<?php
/**
 * Newsletter Analytics
 *
 * Handles newsletter analytics and metrics integration with WP Mail SMTP
 *
 * @package Create_A_Newsletter_With_The_Block_Editor
 * @since 1.4
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class CANWBE_Newsletter_Analytics {

    /**
     * Initialize analytics system
     */
    public static function init() {
        // Only initialize if WP Mail SMTP is active
        if (!self::is_wp_mail_smtp_active()) {
            return;
        }

        add_action('admin_menu', array(__CLASS__, 'add_analytics_menu'));
        add_action('wp_ajax_canwbe_refresh_analytics', array(__CLASS__, 'ajax_refresh_analytics'));

        // Hook into email sending to track metrics
        add_action('canwbe_email_sent_successfully', array(__CLASS__, 'track_email_sent'), 10, 3);

        // Hook into WP Mail SMTP for open tracking
        self::setup_wp_mail_smtp_integration();
    }

    /**
     * Check if WP Mail SMTP is active and has logging enabled
     */
    public static function is_wp_mail_smtp_active() {
        return function_exists('wp_mail_smtp') &&
            class_exists('WPMailSMTP\Logs\Logs') &&
            self::is_wp_mail_smtp_logging_enabled();
    }

    /**
     * Check if WP Mail SMTP logging is enabled
     */
    public static function is_wp_mail_smtp_logging_enabled() {
        if (!function_exists('wp_mail_smtp')) {
            return false;
        }

        $options = wp_mail_smtp()->get_options();
        return $options->get('logs', 'enabled', false);
    }

    /**
     * Check if WP Mail SMTP open tracking is enabled
     */
    public static function is_wp_mail_smtp_open_tracking_enabled() {
        if (!function_exists('wp_mail_smtp')) {
            return false;
        }

        $options = wp_mail_smtp()->get_options();
        return $options->get('logs', 'email_open_tracking', false);
    }

    /**
     * Add analytics menu to admin
     */
    public static function add_analytics_menu() {
        add_submenu_page(
            'edit.php?post_type=newsletter',
            __('Analytics', 'create-a-newsletter-with-the-block-editor'),
            __('Analytics', 'create-a-newsletter-with-the-block-editor'),
            'manage_options',
            'canwbe-analytics',
            array(__CLASS__, 'analytics_page')
        );
    }

    /**
     * Analytics admin page
     */
    public static function analytics_page() {
        // Check if WP Mail SMTP is properly configured
        if (!self::is_wp_mail_smtp_active()) {
            self::render_setup_notice();
            return;
        }

        $analytics_data = self::get_analytics_data();
        $recent_newsletters = self::get_recent_newsletters_with_stats();

        ?>
        <div class="wrap">
            <h1>
                <?php esc_html_e('Newsletter Analytics', 'create-a-newsletter-with-the-block-editor'); ?>
                <button type="button" class="page-title-action" id="refresh-analytics">
                    🔄 <?php esc_html_e('Refresh Data', 'create-a-newsletter-with-the-block-editor'); ?>
                </button>
            </h1>

            <?php if (!self::is_wp_mail_smtp_open_tracking_enabled()): ?>
                <div class="notice notice-warning">
                    <p>
                        <strong><?php esc_html_e('Open Tracking Disabled', 'create-a-newsletter-with-the-block-editor'); ?></strong><br>
                        <?php esc_html_e('Email open tracking is disabled in WP Mail SMTP. Enable it to see open rates.', 'create-a-newsletter-with-the-block-editor'); ?>
                        <a href="<?php echo admin_url('admin.php?page=wp-mail-smtp-logs'); ?>" class="button button-small" style="margin-left: 10px;">
                            <?php esc_html_e('WP Mail SMTP Settings', 'create-a-newsletter-with-the-block-editor'); ?>
                        </a>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Overview Cards -->
            <div class="canwbe-analytics-overview">
                <div class="canwbe-metric-card">
                    <div class="canwbe-metric-number"><?php echo esc_html(number_format_i18n($analytics_data['total_sent'])); ?></div>
                    <div class="canwbe-metric-label"><?php esc_html_e('Total Emails Sent', 'create-a-newsletter-with-the-block-editor'); ?></div>
                    <div class="canwbe-metric-period"><?php esc_html_e('All time', 'create-a-newsletter-with-the-block-editor'); ?></div>
                </div>

                <div class="canwbe-metric-card">
                    <div class="canwbe-metric-number"><?php echo esc_html(number_format_i18n($analytics_data['total_opens'])); ?></div>
                    <div class="canwbe-metric-label"><?php esc_html_e('Total Email Opens', 'create-a-newsletter-with-the-block-editor'); ?></div>
                    <div class="canwbe-metric-period"><?php esc_html_e('All time', 'create-a-newsletter-with-the-block-editor'); ?></div>
                </div>

                <div class="canwbe-metric-card">
                    <div class="canwbe-metric-number">
                        <?php
                        $open_rate = $analytics_data['total_sent'] > 0
                            ? round(($analytics_data['total_opens'] / $analytics_data['total_sent']) * 100, 1)
                            : 0;
                        echo esc_html($open_rate . '%');
                        ?>
                    </div>
                    <div class="canwbe-metric-label"><?php esc_html_e('Average Open Rate', 'create-a-newsletter-with-the-block-editor'); ?></div>
                    <div class="canwbe-metric-period">
                        <?php
                        if ($open_rate >= 25) {
                            echo '<span style="color: #00a32a;">✓ ' . esc_html__('Excellent', 'create-a-newsletter-with-the-block-editor') . '</span>';
                        } elseif ($open_rate >= 15) {
                            echo '<span style="color: #dba617;">⚠ ' . esc_html__('Good', 'create-a-newsletter-with-the-block-editor') . '</span>';
                        } else {
                            echo '<span style="color: #d63638;">! ' . esc_html__('Needs improvement', 'create-a-newsletter-with-the-block-editor') . '</span>';
                        }
                        ?>
                    </div>
                </div>

                <div class="canwbe-metric-card">
                    <div class="canwbe-metric-number"><?php echo esc_html(number_format_i18n($analytics_data['newsletters_sent'])); ?></div>
                    <div class="canwbe-metric-label"><?php esc_html_e('Newsletters Sent', 'create-a-newsletter-with-the-block-editor'); ?></div>
                    <div class="canwbe-metric-period"><?php esc_html_e('All time', 'create-a-newsletter-with-the-block-editor'); ?></div>
                </div>
            </div>

            <!-- Recent Newsletters Table -->
            <div class="card" style="margin-top: 30px;">
                <h2><?php esc_html_e('Recent Newsletter Performance', 'create-a-newsletter-with-the-block-editor'); ?></h2>

                <?php if (empty($recent_newsletters)): ?>
                    <p><?php esc_html_e('No newsletter data found. Send some newsletters to see analytics here.', 'create-a-newsletter-with-the-block-editor'); ?></p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                        <tr>
                            <th><?php esc_html_e('Newsletter', 'create-a-newsletter-with-the-block-editor'); ?></th>
                            <th><?php esc_html_e('Sent Date', 'create-a-newsletter-with-the-block-editor'); ?></th>
                            <th><?php esc_html_e('Recipients', 'create-a-newsletter-with-the-block-editor'); ?></th>
                            <th><?php esc_html_e('Delivered', 'create-a-newsletter-with-the-block-editor'); ?></th>
                            <th><?php esc_html_e('Opens', 'create-a-newsletter-with-the-block-editor'); ?></th>
                            <th><?php esc_html_e('Open Rate', 'create-a-newsletter-with-the-block-editor'); ?></th>
                            <th><?php esc_html_e('Status', 'create-a-newsletter-with-the-block-editor'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recent_newsletters as $newsletter): ?>
                            <tr>
                                <td>
                                    <strong>
                                        <a href="<?php echo esc_url(get_edit_post_link($newsletter['post_id'])); ?>">
                                            <?php echo esc_html($newsletter['title']); ?>
                                        </a>
                                    </strong>
                                    <?php if ($newsletter['batch_id']): ?>
                                        <br><small>Batch: <code><?php echo esc_html($newsletter['batch_id']); ?></code></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($newsletter['sent_date']); ?></td>
                                <td><?php echo esc_html(number_format_i18n($newsletter['total_recipients'])); ?></td>
                                <td>
                                    <span style="color: #00a32a;"><?php echo esc_html(number_format_i18n($newsletter['delivered'])); ?></span>
                                    <?php if ($newsletter['failed'] > 0): ?>
                                        <br><small style="color: #d63638;"><?php echo esc_html($newsletter['failed']); ?> failed</small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html(number_format_i18n($newsletter['opens'])); ?></td>
                                <td>
                                    <?php
                                    $newsletter_open_rate = $newsletter['delivered'] > 0
                                        ? round(($newsletter['opens'] / $newsletter['delivered']) * 100, 1)
                                        : 0;
                                    $rate_color = $newsletter_open_rate >= 25 ? '#00a32a' : ($newsletter_open_rate >= 15 ? '#dba617' : '#d63638');
                                    ?>
                                    <span style="color: <?php echo $rate_color; ?>; font-weight: bold;">
                                        <?php echo esc_html($newsletter_open_rate . '%'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-<?php echo esc_attr($newsletter['status']); ?>">
                                        <?php echo esc_html(ucfirst($newsletter['status'])); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Integration Info -->
            <div class="card" style="margin-top: 20px;">
                <h3><?php esc_html_e('Integration Status', 'create-a-newsletter-with-the-block-editor'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e('WP Mail SMTP', 'create-a-newsletter-with-the-block-editor'); ?></th>
                        <td>
                            <span style="color: #00a32a;">✅ <?php esc_html_e('Active', 'create-a-newsletter-with-the-block-editor'); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Email Logging', 'create-a-newsletter-with-the-block-editor'); ?></th>
                        <td>
                            <?php if (self::is_wp_mail_smtp_logging_enabled()): ?>
                                <span style="color: #00a32a;">✅ <?php esc_html_e('Enabled', 'create-a-newsletter-with-the-block-editor'); ?></span>
                            <?php else: ?>
                                <span style="color: #d63638;">❌ <?php esc_html_e('Disabled', 'create-a-newsletter-with-the-block-editor'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Open Tracking', 'create-a-newsletter-with-the-block-editor'); ?></th>
                        <td>
                            <?php if (self::is_wp_mail_smtp_open_tracking_enabled()): ?>
                                <span style="color: #00a32a;">✅ <?php esc_html_e('Enabled', 'create-a-newsletter-with-the-block-editor'); ?></span>
                            <?php else: ?>
                                <span style="color: #dba617;">⚠️ <?php esc_html_e('Disabled', 'create-a-newsletter-with-the-block-editor'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Data Source', 'create-a-newsletter-with-the-block-editor'); ?></th>
                        <td>
                            <?php esc_html_e('WP Mail SMTP Logs Database', 'create-a-newsletter-with-the-block-editor'); ?>
                            <br><small><?php esc_html_e('Analytics are generated from WP Mail SMTP log entries', 'create-a-newsletter-with-the-block-editor'); ?></small>
                        </td>
                    </tr>
                </table>

                <p>
                    <a href="<?php echo admin_url('admin.php?page=wp-mail-smtp-logs'); ?>" class="button">
                        <?php esc_html_e('View WP Mail SMTP Logs', 'create-a-newsletter-with-the-block-editor'); ?>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=wp-mail-smtp-settings'); ?>" class="button">
                        <?php esc_html_e('WP Mail SMTP Settings', 'create-a-newsletter-with-the-block-editor'); ?>
                    </a>
                </p>
            </div>
        </div>

        <style>
            .canwbe-analytics-overview {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin: 20px 0;
            }

            .canwbe-metric-card {
                background: white;
                border: 1px solid #c3c4c7;
                border-radius: 4px;
                padding: 20px;
                text-align: center;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
            }

            .canwbe-metric-number {
                font-size: 2.5em;
                font-weight: bold;
                color: #2271b1;
                margin-bottom: 5px;
            }

            .canwbe-metric-label {
                font-size: 1.1em;
                color: #50575e;
                margin-bottom: 5px;
            }

            .canwbe-metric-period {
                font-size: 0.9em;
                color: #646970;
            }

            .card {
                background: white;
                border: 1px solid #c3c4c7;
                border-left: 4px solid #72aee6;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
                padding: 1em 2em;
            }

            .status-completed { color: #00a32a; font-weight: bold; }
            .status-processing { color: #dba617; font-weight: bold; }
            .status-failed { color: #d63638; font-weight: bold; }

            #refresh-analytics {
                background: #2271b1;
                border-color: #2271b1;
                color: white;
            }

            #refresh-analytics:hover {
                background: #135e96;
                border-color: #135e96;
            }
        </style>

        <script>
            jQuery(document).ready(function($) {
                $('#refresh-analytics').on('click', function() {
                    const button = $(this);
                    const originalText = button.text();

                    button.prop('disabled', true).text('🔄 <?php echo esc_js(__('Refreshing...', 'create-a-newsletter-with-the-block-editor')); ?>');

                    $.post(ajaxurl, {
                        action: 'canwbe_refresh_analytics',
                        nonce: '<?php echo wp_create_nonce('canwbe_analytics'); ?>'
                    }, function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('<?php echo esc_js(__('Error refreshing analytics. Please try again.', 'create-a-newsletter-with-the-block-editor')); ?>');
                        }
                    }).always(function() {
                        button.prop('disabled', false).text(originalText);
                    });
                });
            });
        </script>
        <?php
    }

    /**
     * Render setup notice when WP Mail SMTP is not properly configured
     */
    public static function render_setup_notice() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Newsletter Analytics', 'create-a-newsletter-with-the-block-editor'); ?></h1>

            <div class="notice notice-warning">
                <h2><?php esc_html_e('WP Mail SMTP Required', 'create-a-newsletter-with-the-block-editor'); ?></h2>
                <p><?php esc_html_e('Newsletter analytics requires WP Mail SMTP plugin with logging enabled to track email metrics.', 'create-a-newsletter-with-the-block-editor'); ?></p>
            </div>

            <div class="card">
                <h2><?php esc_html_e('Setup Instructions', 'create-a-newsletter-with-the-block-editor'); ?></h2>

                <h3><?php esc_html_e('Step 1: Install WP Mail SMTP', 'create-a-newsletter-with-the-block-editor'); ?></h3>
                <?php if (!function_exists('wp_mail_smtp')): ?>
                    <p>
                        <a href="<?php echo admin_url('plugin-install.php?s=wp-mail-smtp&tab=search&type=term'); ?>" class="button button-primary">
                            <?php esc_html_e('Install WP Mail SMTP Plugin', 'create-a-newsletter-with-the-block-editor'); ?>
                        </a>
                    </p>
                <?php else: ?>
                    <p style="color: #00a32a;">✅ <?php esc_html_e('WP Mail SMTP is installed', 'create-a-newsletter-with-the-block-editor'); ?></p>
                <?php endif; ?>

                <h3><?php esc_html_e('Step 2: Enable Email Logging', 'create-a-newsletter-with-the-block-editor'); ?></h3>
                <p><?php esc_html_e('Go to WP Mail SMTP settings and enable email logging to track sent emails.', 'create-a-newsletter-with-the-block-editor'); ?></p>
                <?php if (function_exists('wp_mail_smtp')): ?>
                    <p>
                        <a href="<?php echo admin_url('admin.php?page=wp-mail-smtp-logs'); ?>" class="button">
                            <?php esc_html_e('WP Mail SMTP Logs Settings', 'create-a-newsletter-with-the-block-editor'); ?>
                        </a>
                    </p>
                <?php endif; ?>

                <h3><?php esc_html_e('Step 3: Enable Open Tracking (Optional)', 'create-a-newsletter-with-the-block-editor'); ?></h3>
                <p><?php esc_html_e('For open rate tracking, enable email open tracking in WP Mail SMTP settings.', 'create-a-newsletter-with-the-block-editor'); ?></p>

                <h3><?php esc_html_e('What You\'ll Get', 'create-a-newsletter-with-the-block-editor'); ?></h3>
                <ul>
                    <li>✅ <?php esc_html_e('Total emails sent across all newsletters', 'create-a-newsletter-with-the-block-editor'); ?></li>
                    <li>✅ <?php esc_html_e('Email open tracking and rates', 'create-a-newsletter-with-the-block-editor'); ?></li>
                    <li>✅ <?php esc_html_e('Individual newsletter performance metrics', 'create-a-newsletter-with-the-block-editor'); ?></li>
                    <li>✅ <?php esc_html_e('Delivery success and failure tracking', 'create-a-newsletter-with-the-block-editor'); ?></li>
                    <li>✅ <?php esc_html_e('Historical analytics across all campaigns', 'create-a-newsletter-with-the-block-editor'); ?></li>
                </ul>
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
        </style>
        <?php
    }

    /**
     * Get analytics data from WP Mail SMTP logs
     */
    public static function get_analytics_data() {
        global $wpdb;

        $data = array(
            'total_sent' => 0,
            'total_opens' => 0,
            'newsletters_sent' => 0
        );

        if (!self::is_wp_mail_smtp_active()) {
            return $data;
        }

        // Get WP Mail SMTP table name
        $logs_table = \WPMailSMTP\Logs\Logs::get_table_name();

        // Get total sent newsletter emails
        $sent_query = $wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$logs_table} 
            WHERE status = %s 
            AND (subject LIKE %s OR headers LIKE %s)
        ", 'sent', '%newsletter%', '%newsletter%');

        $data['total_sent'] = (int) $wpdb->get_var($sent_query);

        // Get total opens if open tracking is enabled
        if (self::is_wp_mail_smtp_open_tracking_enabled()) {
            $opens_query = $wpdb->prepare("
                SELECT COUNT(*) 
                FROM {$logs_table} 
                WHERE opened IS NOT NULL 
                AND (subject LIKE %s OR headers LIKE %s)
            ", '%newsletter%', '%newsletter%');

            $data['total_opens'] = (int) $wpdb->get_var($opens_query);
        }

        // Get count of newsletters sent
        $newsletters_query = "
            SELECT COUNT(DISTINCT post_id) 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_newsletter_batch_id'
        ";

        $data['newsletters_sent'] = (int) $wpdb->get_var($newsletters_query);

        return $data;
    }

    /**
     * Get recent newsletters with statistics
     */
    public static function get_recent_newsletters_with_stats() {
        global $wpdb;

        $newsletters = array();

        if (!self::is_wp_mail_smtp_active()) {
            return $newsletters;
        }

        // Get recent newsletters with batch IDs
        $recent_newsletters = get_posts(array(
            'post_type' => 'newsletter',
            'post_status' => 'publish',
            'numberposts' => 10,
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => array(
                array(
                    'key' => '_newsletter_batch_id',
                    'compare' => 'EXISTS'
                )
            )
        ));

        $logs_table = \WPMailSMTP\Logs\Logs::get_table_name();

        foreach ($recent_newsletters as $post) {
            $batch_id = get_post_meta($post->ID, '_newsletter_batch_id', true);
            $batch_data = get_option('canwbe_batch_' . $batch_id);

            if (!$batch_data) continue;

            // Get WP Mail SMTP stats for this newsletter
            $subject_pattern = '%' . $wpdb->esc_like($post->post_title) . '%';

            // Count sent emails
            $sent_query = $wpdb->prepare("
                SELECT COUNT(*) 
                FROM {$logs_table} 
                WHERE status = %s 
                AND subject LIKE %s
                AND date_sent >= %s
            ", 'sent', $subject_pattern, $post->post_date);

            $delivered = (int) $wpdb->get_var($sent_query);

            // Count opens
            $opens = 0;
            if (self::is_wp_mail_smtp_open_tracking_enabled()) {
                $opens_query = $wpdb->prepare("
                    SELECT COUNT(*) 
                    FROM {$logs_table} 
                    WHERE opened IS NOT NULL 
                    AND subject LIKE %s
                    AND date_sent >= %s
                ", $subject_pattern, $post->post_date);

                $opens = (int) $wpdb->get_var($opens_query);
            }

            $newsletters[] = array(
                'post_id' => $post->ID,
                'title' => $post->post_title,
                'sent_date' => date_i18n(get_option('date_format'), strtotime($post->post_date)),
                'batch_id' => $batch_id,
                'total_recipients' => $batch_data['total_emails'] ?? 0,
                'delivered' => $delivered,
                'failed' => ($batch_data['failed_emails'] ?? 0),
                'opens' => $opens,
                'status' => $batch_data['status'] ?? 'unknown'
            );
        }

        return $newsletters;
    }

    /**
     * Track email sent successfully
     */
    public static function track_email_sent($email, $user_id, $newsletter_id) {
        // This hook can be used by the batch sender to track successful sends
        // Additional tracking can be added here if needed
        canwbe_log('Email sent for analytics tracking', array(
            'email' => $email,
            'user_id' => $user_id,
            'newsletter_id' => $newsletter_id
        ));
    }

    /**
     * Setup WP Mail SMTP integration hooks
     */
    public static function setup_wp_mail_smtp_integration() {
        // Hook into WP Mail SMTP events if available
        if (has_action('wp_mail_smtp_mailcatcher_smtp_pre_send')) {
            add_action('wp_mail_smtp_mailcatcher_smtp_pre_send', array(__CLASS__, 'wp_mail_smtp_pre_send'), 10, 2);
        }
    }

    /**
     * WP Mail SMTP pre-send hook
     */
    public static function wp_mail_smtp_pre_send($phpmailer, $wp_mail_smtp) {
        // Add any newsletter-specific tracking here
        if (strpos($phpmailer->Subject, 'newsletter') !== false) {
            canwbe_log('Newsletter email being sent via WP Mail SMTP', array(
                'subject' => $phpmailer->Subject,
                'to' => $phpmailer->getToAddresses()
            ));
        }
    }

    /**
     * AJAX handler for refreshing analytics
     */
    public static function ajax_refresh_analytics() {
        check_ajax_referer('canwbe_analytics', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        // Clear any cached data and force refresh
        delete_transient('canwbe_analytics_data');

        wp_send_json_success(array(
            'message' => __('Analytics data refreshed successfully', 'create-a-newsletter-with-the-block-editor')
        ));
    }
}

// Initialize analytics system
CANWBE_Newsletter_Analytics::init();
