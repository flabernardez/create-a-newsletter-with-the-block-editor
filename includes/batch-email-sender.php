<?php
/**
 * Batch Email Sender System
 *
 * Sends newsletter emails in batches to prevent server overload
 * Compatible with WP Mail SMTP logging
 *
 * @package Create_A_Newsletter_With_The_Block_Editor
 * @since 1.3
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class CANWBE_Batch_Email_Sender {

    /**
     * Configuration constants
     */
    const BATCH_SIZE = 10;          // Number of emails per batch
    const BATCH_DELAY = 30;         // Seconds between batches
    const MAX_RETRIES = 3;          // Maximum retry attempts per email
    const RETRY_DELAY = 300;        // Seconds before retry (5 minutes)

    /**
     * Initialize the batch email system
     */
    public static function init() {
        // Hook for processing batches
        add_action('canwbe_process_email_batch', array(__CLASS__, 'process_email_batch'));

        // Hook for retrying failed emails
        add_action('canwbe_retry_failed_emails', array(__CLASS__, 'retry_failed_emails'));

        // Admin page hooks
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));

        // AJAX hooks for admin interface
        add_action('wp_ajax_canwbe_cancel_batch', array(__CLASS__, 'ajax_cancel_batch'));
        add_action('wp_ajax_canwbe_get_batch_status', array(__CLASS__, 'ajax_get_batch_status'));

        // Hook to clean old batch data
        add_action('canwbe_cleanup_old_batches', array(__CLASS__, 'cleanup_old_batches'));
        if (!wp_next_scheduled('canwbe_cleanup_old_batches')) {
            wp_schedule_event(time(), 'daily', 'canwbe_cleanup_old_batches');
        }
    }

    /**
     * Queue newsletter for batch sending
     */
    public static function queue_newsletter($post_id, $subscribers, $subject, $message, $headers, $unsubscribe_message) {
        $batch_id = 'newsletter_' . $post_id . '_' . time();

        // Prepare email queue
        $email_queue = array();
        foreach ($subscribers as $subscriber) {
            $token = canwbe_generate_unsubscribe_token($subscriber->ID);
            $login_url = esc_url(home_url('/?canwbe_login=1&user_id=' . $subscriber->ID . '&token=' . $token));
            $final_message = $message . '<br><br><a href="' . $login_url . '">' . esc_html($unsubscribe_message) . '</a>';

            $email_queue[] = array(
                'to' => sanitize_email($subscriber->user_email),
                'user_id' => $subscriber->ID,
                'subject' => $subject,
                'message' => $final_message,
                'headers' => $headers,
                'status' => 'pending',
                'attempts' => 0,
                'last_attempt' => null,
                'error_message' => null
            );
        }

        // Store batch data
        $batch_data = array(
            'post_id' => $post_id,
            'batch_id' => $batch_id,
            'total_emails' => count($email_queue),
            'sent_emails' => 0,
            'failed_emails' => 0,
            'status' => 'queued',
            'created_at' => current_time('mysql'),
            'started_at' => null,
            'completed_at' => null,
            'current_batch' => 0,
            'email_queue' => $email_queue
        );

        update_option('canwbe_batch_' . $batch_id, $batch_data);

        // Schedule first batch
        wp_schedule_single_event(time() + 5, 'canwbe_process_email_batch', array($batch_id));

        // Log batch creation
        self::log_batch_event($batch_id, 'Batch created', array(
            'total_emails' => count($email_queue),
            'post_id' => $post_id
        ));

        return $batch_id;
    }

    /**
     * Process a batch of emails
     */
    public static function process_email_batch($batch_id) {
        $batch_data = get_option('canwbe_batch_' . $batch_id);

        if (!$batch_data || $batch_data['status'] === 'cancelled') {
            return;
        }

        // Update status if first batch
        if ($batch_data['status'] === 'queued') {
            $batch_data['status'] = 'processing';
            $batch_data['started_at'] = current_time('mysql');
        }

        // Get emails to process in this batch
        $emails_to_process = array_slice(
            array_filter($batch_data['email_queue'], function($email) {
                return $email['status'] === 'pending';
            }),
            0,
            self::BATCH_SIZE
        );

        if (empty($emails_to_process)) {
            self::complete_batch($batch_id, $batch_data);
            return;
        }

        self::log_batch_event($batch_id, 'Processing batch', array(
            'emails_in_batch' => count($emails_to_process)
        ));

        // Process each email in the batch
        foreach ($emails_to_process as $index => $email_data) {
            $success = self::send_single_email($email_data);

            // Update email status in queue
            foreach ($batch_data['email_queue'] as &$queue_email) {
                if ($queue_email['to'] === $email_data['to'] && $queue_email['user_id'] === $email_data['user_id']) {
                    $queue_email['attempts']++;
                    $queue_email['last_attempt'] = current_time('mysql');

                    if ($success) {
                        $queue_email['status'] = 'sent';
                        $batch_data['sent_emails']++;

                        self::log_email_success($batch_id, $email_data);
                    } else {
                        if ($queue_email['attempts'] >= self::MAX_RETRIES) {
                            $queue_email['status'] = 'failed';
                            $batch_data['failed_emails']++;

                            self::log_email_failure($batch_id, $email_data, 'Max retries reached');
                        } else {
                            $queue_email['status'] = 'retry';
                            $queue_email['error_message'] = 'Failed to send, will retry';

                            self::log_email_failure($batch_id, $email_data, 'Will retry');
                        }
                    }
                    break;
                }
            }

            // Small delay between individual emails
            if ($index < count($emails_to_process) - 1) {
                sleep(1);
            }
        }

        // Update batch data
        $batch_data['current_batch']++;
        update_option('canwbe_batch_' . $batch_id, $batch_data);

        // Schedule next batch if needed
        $pending_emails = array_filter($batch_data['email_queue'], function($email) {
            return $email['status'] === 'pending';
        });

        if (!empty($pending_emails)) {
            wp_schedule_single_event(time() + self::BATCH_DELAY, 'canwbe_process_email_batch', array($batch_id));
        } else {
            // Schedule retry for failed emails
            $retry_emails = array_filter($batch_data['email_queue'], function($email) {
                return $email['status'] === 'retry';
            });

            if (!empty($retry_emails)) {
                wp_schedule_single_event(time() + self::RETRY_DELAY, 'canwbe_retry_failed_emails', array($batch_id));
            } else {
                self::complete_batch($batch_id, $batch_data);
            }
        }
    }

    /**
     * Send a single email
     */
    private static function send_single_email($email_data) {
        try {
            $success = wp_mail(
                $email_data['to'],
                $email_data['subject'],
                $email_data['message'],
                $email_data['headers']
            );

            // Check if WP Mail SMTP is active and get detailed results
            if (function_exists('wp_mail_smtp')) {
                $wp_mail_smtp = wp_mail_smtp();
                if ($wp_mail_smtp && method_exists($wp_mail_smtp, 'get_processor')) {
                    $processor = $wp_mail_smtp->get_processor();
                    if ($processor && method_exists($processor, 'get_response')) {
                        // Additional logging for WP Mail SMTP compatibility
                        $response = $processor->get_response();
                        if (!empty($response)) {
                            error_log('CANWBE WP Mail SMTP Response: ' . print_r($response, true));
                        }
                    }
                }
            }

            return $success;

        } catch (Exception $e) {
            error_log('CANWBE Email sending exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Retry failed emails
     */
    public static function retry_failed_emails($batch_id) {
        $batch_data = get_option('canwbe_batch_' . $batch_id);

        if (!$batch_data || $batch_data['status'] === 'cancelled') {
            return;
        }

        // Reset retry emails to pending
        foreach ($batch_data['email_queue'] as &$email) {
            if ($email['status'] === 'retry') {
                $email['status'] = 'pending';
            }
        }

        update_option('canwbe_batch_' . $batch_id, $batch_data);

        // Schedule processing
        wp_schedule_single_event(time() + 5, 'canwbe_process_email_batch', array($batch_id));

        self::log_batch_event($batch_id, 'Retrying failed emails');
    }

    /**
     * Complete batch processing
     */
    private static function complete_batch($batch_id, $batch_data) {
        $batch_data['status'] = 'completed';
        $batch_data['completed_at'] = current_time('mysql');

        update_option('canwbe_batch_' . $batch_id, $batch_data);

        self::log_batch_event($batch_id, 'Batch completed', array(
            'sent' => $batch_data['sent_emails'],
            'failed' => $batch_data['failed_emails'],
            'total' => $batch_data['total_emails']
        ));

        // Send admin notification if there were failures
        if ($batch_data['failed_emails'] > 0) {
            self::send_admin_notification($batch_id, $batch_data);
        }
    }

    /**
     * Cancel a batch
     */
    public static function cancel_batch($batch_id) {
        $batch_data = get_option('canwbe_batch_' . $batch_id);

        if ($batch_data) {
            $batch_data['status'] = 'cancelled';
            $batch_data['completed_at'] = current_time('mysql');

            update_option('canwbe_batch_' . $batch_id, $batch_data);

            // Clear scheduled events
            wp_clear_scheduled_hook('canwbe_process_email_batch', array($batch_id));
            wp_clear_scheduled_hook('canwbe_retry_failed_emails', array($batch_id));

            self::log_batch_event($batch_id, 'Batch cancelled by admin');

            return true;
        }

        return false;
    }

    /**
     * Get batch status
     */
    public static function get_batch_status($batch_id) {
        $batch_data = get_option('canwbe_batch_' . $batch_id);

        if (!$batch_data) {
            return false;
        }

        return array(
            'batch_id' => $batch_id,
            'status' => $batch_data['status'],
            'total_emails' => $batch_data['total_emails'],
            'sent_emails' => $batch_data['sent_emails'],
            'failed_emails' => $batch_data['failed_emails'],
            'progress_percentage' => $batch_data['total_emails'] > 0
                ? round(($batch_data['sent_emails'] + $batch_data['failed_emails']) / $batch_data['total_emails'] * 100, 2)
                : 0,
            'created_at' => $batch_data['created_at'],
            'started_at' => $batch_data['started_at'],
            'completed_at' => $batch_data['completed_at']
        );
    }

    /**
     * Get all active batches
     */
    public static function get_active_batches() {
        global $wpdb;

        $batches = array();
        $results = $wpdb->get_results("
            SELECT option_name, option_value 
            FROM {$wpdb->options} 
            WHERE option_name LIKE 'canwbe_batch_%'
            ORDER BY option_id DESC
            LIMIT 20
        ");

        foreach ($results as $result) {
            $batch_data = maybe_unserialize($result->option_value);
            if ($batch_data && is_array($batch_data)) {
                $batch_id = str_replace('canwbe_batch_', '', $result->option_name);
                $batches[] = self::get_batch_status($batch_id);
            }
        }

        return array_filter($batches);
    }

    /**
     * Log batch events
     */
    private static function log_batch_event($batch_id, $message, $data = array()) {
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'batch_id' => $batch_id,
            'message' => $message,
            'data' => $data
        );

        error_log('CANWBE Batch Log: ' . json_encode($log_entry));

        // Store in database for admin interface
        $logs = get_option('canwbe_batch_logs', array());
        array_unshift($logs, $log_entry);

        // Keep only last 1000 log entries
        $logs = array_slice($logs, 0, 1000);

        update_option('canwbe_batch_logs', $logs);
    }

    /**
     * Log email success (compatible with WP Mail SMTP)
     */
    private static function log_email_success($batch_id, $email_data) {
        $log_message = sprintf(
            'Email sent successfully to %s (User ID: %d, Batch: %s)',
            $email_data['to'],
            $email_data['user_id'],
            $batch_id
        );

        error_log('CANWBE Email Success: ' . $log_message);

        // Try to integrate with WP Mail SMTP logs if available
        if (function_exists('wp_mail_smtp') && class_exists('WPMailSMTP\Logs\Logs')) {
            // This creates a log entry compatible with WP Mail SMTP format
            do_action('wp_mail_smtp_mailcatcher_smtp_pre_send_before');
        }
    }

    /**
     * Log email failure
     */
    private static function log_email_failure($batch_id, $email_data, $reason) {
        $log_message = sprintf(
            'Email failed to %s (User ID: %d, Batch: %s) - Reason: %s',
            $email_data['to'],
            $email_data['user_id'],
            $batch_id,
            $reason
        );

        error_log('CANWBE Email Failure: ' . $log_message);
    }

    /**
     * Send admin notification for failed emails
     */
    private static function send_admin_notification($batch_id, $batch_data) {
        $admin_email = get_option('admin_email');
        $post_title = get_the_title($batch_data['post_id']);

        $subject = sprintf(
            __('Newsletter sending completed with errors - %s', 'create-a-newsletter-with-the-block-editor'),
            $post_title
        );

        $message = sprintf(
            __('The newsletter "%s" has finished sending, but some emails failed:

Successfully sent: %d
Failed emails: %d
Total emails: %d

Please check the batch email logs in your WordPress admin for more details.

Batch ID: %s', 'create-a-newsletter-with-the-block-editor'),
            $post_title,
            $batch_data['sent_emails'],
            $batch_data['failed_emails'],
            $batch_data['total_emails'],
            $batch_id
        );

        wp_mail($admin_email, $subject, $message);
    }

    /**
     * Clean up old batch data
     */
    public static function cleanup_old_batches() {
        global $wpdb;

        // Get batches older than 30 days
        $old_batches = $wpdb->get_results("
            SELECT option_name 
            FROM {$wpdb->options} 
            WHERE option_name LIKE 'canwbe_batch_%'
        ");

        $deleted_count = 0;
        foreach ($old_batches as $batch) {
            $batch_data = get_option($batch->option_name);
            if ($batch_data && isset($batch_data['created_at'])) {
                $created_time = strtotime($batch_data['created_at']);
                if ($created_time < strtotime('-30 days')) {
                    delete_option($batch->option_name);
                    $deleted_count++;
                }
            }
        }

        if ($deleted_count > 0) {
            error_log("CANWBE: Cleaned up $deleted_count old batch records");
        }
    }

    /**
     * AJAX handler for canceling batches
     */
    public static function ajax_cancel_batch() {
        check_ajax_referer('canwbe_admin', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $batch_id = sanitize_text_field($_POST['batch_id']);
        $result = self::cancel_batch($batch_id);

        wp_send_json_success(array('cancelled' => $result));
    }

    /**
     * AJAX handler for getting batch status
     */
    public static function ajax_get_batch_status() {
        check_ajax_referer('canwbe_admin', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $batch_id = sanitize_text_field($_POST['batch_id']);
        $status = self::get_batch_status($batch_id);

        wp_send_json_success($status);
    }

    /**
     * Get batch size setting
     */
    public static function get_batch_size() {
        return apply_filters('canwbe_batch_size', self::BATCH_SIZE);
    }

    /**
     * Get batch delay setting
     */
    public static function get_batch_delay() {
        return apply_filters('canwbe_batch_delay', self::BATCH_DELAY);
    }

    /**
     * Get max retries setting
     */
    public static function get_max_retries() {
        return apply_filters('canwbe_max_retries', self::MAX_RETRIES);
    }

    /**
     * Add admin menu
     */
    public static function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=newsletter',
            __('Email Batches', 'create-a-newsletter-with-the-block-editor'),
            __('Email Batches', 'create-a-newsletter-with-the-block-editor'),
            'manage_options',
            'canwbe-email-batches',
            array(__CLASS__, 'admin_page')
        );
    }

    /**
     * Admin page content
     */
    public static function admin_page() {
        $batches = self::get_active_batches();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Email Batches', 'create-a-newsletter-with-the-block-editor'); ?></h1>

            <div class="card">
                <h2><?php esc_html_e('Batch Settings', 'create-a-newsletter-with-the-block-editor'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e('Emails per batch', 'create-a-newsletter-with-the-block-editor'); ?></th>
                        <td><?php echo self::get_batch_size(); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Delay between batches', 'create-a-newsletter-with-the-block-editor'); ?></th>
                        <td><?php echo self::get_batch_delay(); ?> <?php esc_html_e('seconds', 'create-a-newsletter-with-the-block-editor'); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Maximum retries', 'create-a-newsletter-with-the-block-editor'); ?></th>
                        <td><?php echo self::get_max_retries(); ?></td>
                    </tr>
                </table>
            </div>

            <div class="card">
                <h2><?php esc_html_e('Active Batches', 'create-a-newsletter-with-the-block-editor'); ?></h2>

                <?php if (empty($batches)): ?>
                    <p><?php esc_html_e('No active batches found.', 'create-a-newsletter-with-the-block-editor'); ?></p>
                <?php else: ?>
                    <table class="widefat">
                        <thead>
                        <tr>
                            <th><?php esc_html_e('Batch ID', 'create-a-newsletter-with-the-block-editor'); ?></th>
                            <th><?php esc_html_e('Status', 'create-a-newsletter-with-the-block-editor'); ?></th>
                            <th><?php esc_html_e('Progress', 'create-a-newsletter-with-the-block-editor'); ?></th>
                            <th><?php esc_html_e('Emails', 'create-a-newsletter-with-the-block-editor'); ?></th>
                            <th><?php esc_html_e('Created', 'create-a-newsletter-with-the-block-editor'); ?></th>
                            <th><?php esc_html_e('Actions', 'create-a-newsletter-with-the-block-editor'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($batches as $batch): ?>
                            <tr id="batch-<?php echo esc_attr($batch['batch_id']); ?>">
                                <td><code><?php echo esc_html($batch['batch_id']); ?></code></td>
                                <td>
                                        <span class="status-<?php echo esc_attr($batch['status']); ?>">
                                            <?php echo esc_html(ucfirst($batch['status'])); ?>
                                        </span>
                                </td>
                                <td>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $batch['progress_percentage']; ?>%"></div>
                                    </div>
                                    <?php echo $batch['progress_percentage']; ?>%
                                </td>
                                <td>
                                    <?php printf(
                                        '%d / %d (%d failed)',
                                        $batch['sent_emails'],
                                        $batch['total_emails'],
                                        $batch['failed_emails']
                                    ); ?>
                                </td>
                                <td><?php echo esc_html($batch['created_at']); ?></td>
                                <td>
                                    <?php if (in_array($batch['status'], ['queued', 'processing'])): ?>
                                        <button class="button cancel-batch" data-batch-id="<?php echo esc_attr($batch['batch_id']); ?>">
                                            <?php esc_html_e('Cancel', 'create-a-newsletter-with-the-block-editor'); ?>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <style>
            .progress-bar {
                width: 100%;
                height: 20px;
                background-color: #f0f0f0;
                border-radius: 10px;
                overflow: hidden;
            }
            .progress-fill {
                height: 100%;
                background-color: #4CAF50;
                transition: width 0.3s ease;
            }
            .status-completed { color: #4CAF50; font-weight: bold; }
            .status-processing { color: #FF9800; font-weight: bold; }
            .status-queued { color: #2196F3; font-weight: bold; }
            .status-cancelled { color: #f44336; font-weight: bold; }
            .status-failed { color: #f44336; font-weight: bold; }
        </style>

        <script>
            jQuery(document).ready(function($) {
                $('.cancel-batch').on('click', function() {
                    const batchId = $(this).data('batch-id');

                    if (!confirm('<?php esc_html_e('Are you sure you want to cancel this batch?', 'create-a-newsletter-with-the-block-editor'); ?>')) {
                        return;
                    }

                    $.post(ajaxurl, {
                        action: 'canwbe_cancel_batch',
                        batch_id: batchId,
                        nonce: '<?php echo wp_create_nonce('canwbe_admin'); ?>'
                    }, function(response) {
                        if (response.success) {
                            location.reload();
                        }
                    });
                });

                // Auto-refresh every 10 seconds for active batches
                setInterval(function() {
                    location.reload();
                }, 10000);
            });
        </script>
        <?php
    }
}

// Initialize the batch email system
CANWBE_Batch_Email_Sender::init();
