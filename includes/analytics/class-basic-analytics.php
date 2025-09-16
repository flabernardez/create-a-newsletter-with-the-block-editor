<?php
/**
 * Basic Analytics
 *
 * Handles basic newsletter analytics when WP Mail SMTP Pro is not available
 * Uses batch system data and basic WordPress functions
 *
 * @package Create_A_Newsletter_With_The_Block_Editor
 * @since 1.4.1
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class CANWBE_Basic_Analytics {

    /**
     * Cache for analytics data
     */
    private static $cache = array();

    /**
     * Initialize basic analytics
     */
    public static function init() {
        // Schedule cleanup of old analytics data
        if (!wp_next_scheduled('canwbe_cleanup_analytics_data')) {
            wp_schedule_event(time(), 'daily', 'canwbe_cleanup_analytics_data');
        }
        add_action('canwbe_cleanup_analytics_data', array(__CLASS__, 'cleanup_old_data'));
    }

    /**
     * Get basic analytics data
     */
    public static function get_analytics_data() {
        // Check cache first
        $cache_key = 'basic_analytics';
        if (isset(self::$cache[$cache_key])) {
            return self::$cache[$cache_key];
        }

        $data = array(
            'total_newsletters' => 0,
            'total_emails_sent' => 0,
            'total_emails_failed' => 0,
            'total_subscribers' => 0,
            'smtp_logs_count' => 0,
            'published_newsletters' => 0,
            'batch_options_count' => 0,
            'delivery_rate' => 0,
            'active_batches' => 0,
            'completed_batches' => 0
        );

        // Get published newsletters using WordPress function
        $newsletters = get_posts(array(
            'post_type' => 'newsletter',
            'post_status' => 'publish',
            'numberposts' => -1,
            'fields' => 'ids'
        ));
        $data['published_newsletters'] = count($newsletters);

        // Get active subscribers using WordPress function
        $subscribers = get_users(array(
            'role' => 'newsletter_subscriber',
            'fields' => 'ID'
        ));
        $data['total_subscribers'] = count($subscribers);

        // Get batch data using WordPress options API
        $batch_data = self::get_all_batch_data();
        $data['batch_options_count'] = count($batch_data);

        foreach ($batch_data as $batch) {
            if (is_array($batch)) {
                $data['total_newsletters']++;
                $data['total_emails_sent'] += (int) ($batch['sent_emails'] ?? 0);
                $data['total_emails_failed'] += (int) ($batch['failed_emails'] ?? 0);

                // Count batch statuses
                $status = $batch['status'] ?? 'unknown';
                if (in_array($status, ['queued', 'processing'])) {
                    $data['active_batches']++;
                } elseif ($status === 'completed') {
                    $data['completed_batches']++;
                }
            }
        }

        // Calculate delivery rate
        $total_attempted = $data['total_emails_sent'] + $data['total_emails_failed'];
        if ($total_attempted > 0) {
            $data['delivery_rate'] = round(($data['total_emails_sent'] / $total_attempted) * 100, 2);
        }

        // Get SMTP logs count if basic SMTP is available
        if (self::has_basic_smtp_logs()) {
            $data['smtp_logs_count'] = self::get_smtp_logs_count();
        }

        // Cache the result
        self::$cache[$cache_key] = $data;

        return $data;
    }

    /**
     * Get basic newsletter campaigns
     */
    public static function get_newsletter_campaigns($limit = 20) {
        // Check cache first
        $cache_key = 'campaigns_' . $limit;
        if (isset(self::$cache[$cache_key])) {
            return self::$cache[$cache_key];
        }

        $campaigns = array();

        // Get batch data
        $batch_data = self::get_all_batch_data($limit);

        foreach ($batch_data as $option_name => $batch) {
            if (!is_array($batch) || !isset($batch['post_id'])) {
                continue;
            }

            $post = get_post($batch['post_id']);
            if (!$post) {
                continue;
            }

            $batch_id = str_replace('canwbe_batch_', '', $option_name);

            // Get additional data from our tracking
            $additional_stats = self::get_newsletter_basic_stats($post->ID);

            $campaign = array(
                'post_id' => $post->ID,
                'title' => $post->post_title,
                'batch_id' => $batch_id,
                'status' => $batch['status'] ?? 'unknown',
                'total_emails' => (int) ($batch['total_emails'] ?? 0),
                'sent_emails' => (int) ($batch['sent_emails'] ?? 0),
                'failed_emails' => (int) ($batch['failed_emails'] ?? 0),
                'created_at' => $batch['created_at'] ?? current_time('mysql'),
                'completed_at' => $batch['completed_at'] ?? null,
                'delivery_rate' => 0
            );

            // Calculate delivery rate
            if ($campaign['total_emails'] > 0) {
                $campaign['delivery_rate'] = round(($campaign['sent_emails'] / $campaign['total_emails']) * 100, 2);
            }

            // Add additional stats if available
            if ($additional_stats) {
                $campaign = array_merge($campaign, $additional_stats);
            }

            $campaigns[] = $campaign;
        }

        // Sort by creation date (newest first)
        usort($campaigns, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        // Cache the result
        self::$cache[$cache_key] = $campaigns;

        return $campaigns;
    }

    /**
     * Get specific newsletter analytics
     */
    public static function get_newsletter_analytics($newsletter_id) {
        // Check cache first
        $cache_key = 'newsletter_' . $newsletter_id;
        if (isset(self::$cache[$cache_key])) {
            return self::$cache[$cache_key];
        }

        $newsletter = get_post($newsletter_id);
        if (!$newsletter || $newsletter->post_type !== 'newsletter') {
            return false;
        }

        // Get batch data for this newsletter
        $batch_id = get_post_meta($newsletter_id, '_newsletter_batch_id', true);
        $batch_data = null;

        if ($batch_id) {
            $batch_data = get_option('canwbe_batch_' . $batch_id);
        }

        $analytics = array(
            'newsletter_id' => $newsletter_id,
            'title' => $newsletter->post_title,
            'date' => $newsletter->post_date,
            'batch_id' => $batch_id,
            'status' => 'unknown',
            'total_emails' => 0,
            'sent_emails' => 0,
            'failed_emails' => 0,
            'delivery_rate' => 0,
            'created_at' => $newsletter->post_date,
            'completed_at' => null
        );

        // Add batch data if available
        if (is_array($batch_data)) {
            $analytics['status'] = $batch_data['status'] ?? 'unknown';
            $analytics['total_emails'] = (int) ($batch_data['total_emails'] ?? 0);
            $analytics['sent_emails'] = (int) ($batch_data['sent_emails'] ?? 0);
            $analytics['failed_emails'] = (int) ($batch_data['failed_emails'] ?? 0);
            $analytics['created_at'] = $batch_data['created_at'] ?? $newsletter->post_date;
            $analytics['completed_at'] = $batch_data['completed_at'] ?? null;

            // Calculate delivery rate
            if ($analytics['total_emails'] > 0) {
                $analytics['delivery_rate'] = round(($analytics['sent_emails'] / $analytics['total_emails']) * 100, 2);
            }
        }

        // Get additional stats from meta
        $meta_stats = self::get_newsletter_basic_stats($newsletter_id);
        if ($meta_stats) {
            $analytics = array_merge($analytics, $meta_stats);
        }

        // Cache the result
        self::$cache[$cache_key] = $analytics;

        return $analytics;
    }

    /**
     * Get subscriber statistics
     */
    public static function get_subscriber_stats() {
        // Check cache first
        $cache_key = 'subscriber_stats';
        if (isset(self::$cache[$cache_key])) {
            return self::$cache[$cache_key];
        }

        // Get all newsletter subscribers
        $subscribers = get_users(array(
            'role' => 'newsletter_subscriber',
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'canwbe_unsubscribe_token',
                    'compare' => 'EXISTS'
                ),
                array(
                    'key' => 'canwbe_unsubscribe_token',
                    'compare' => 'NOT EXISTS'
                )
            )
        ));

        $stats = array(
            'total_subscribers' => count($subscribers),
            'subscribers_with_token' => 0,
            'subscribers_without_token' => 0,
            'recent_subscribers' => 0
        );

        $one_month_ago = strtotime('-1 month');

        foreach ($subscribers as $subscriber) {
            // Check for token
            $token = get_user_meta($subscriber->ID, 'canwbe_unsubscribe_token', true);
            if (!empty($token)) {
                $stats['subscribers_with_token']++;
            } else {
                $stats['subscribers_without_token']++;
            }

            // Check if recent subscriber
            if (strtotime($subscriber->user_registered) > $one_month_ago) {
                $stats['recent_subscribers']++;
            }
        }

        // Cache the result
        self::$cache[$cache_key] = $stats;

        return $stats;
    }

    /**
     * Get newsletter performance summary
     */
    public static function get_performance_summary($days = 30) {
        // Check cache first
        $cache_key = 'performance_' . $days;
        if (isset(self::$cache[$cache_key])) {
            return self::$cache[$cache_key];
        }

        // Get newsletters from specified period
        $newsletters = get_posts(array(
            'post_type' => 'newsletter',
            'post_status' => 'publish',
            'date_query' => array(
                array(
                    'after' => date('Y-m-d', strtotime("-{$days} days"))
                )
            ),
            'numberposts' => -1
        ));

        $summary = array(
            'newsletters_sent' => count($newsletters),
            'total_recipients' => 0,
            'total_delivered' => 0,
            'total_failed' => 0,
            'avg_delivery_rate' => 0,
            'best_performing' => null,
            'worst_performing' => null
        );

        if (empty($newsletters)) {
            self::$cache[$cache_key] = $summary;
            return $summary;
        }

        $delivery_rates = array();

        foreach ($newsletters as $newsletter) {
            $analytics = self::get_newsletter_analytics($newsletter->ID);
            if ($analytics) {
                $summary['total_recipients'] += $analytics['total_emails'];
                $summary['total_delivered'] += $analytics['sent_emails'];
                $summary['total_failed'] += $analytics['failed_emails'];

                if ($analytics['delivery_rate'] > 0) {
                    $delivery_rates[] = array(
                        'newsletter' => $newsletter,
                        'rate' => $analytics['delivery_rate'],
                        'analytics' => $analytics
                    );
                }
            }
        }

        // Calculate average delivery rate
        if (!empty($delivery_rates)) {
            $total_rate = array_sum(array_column($delivery_rates, 'rate'));
            $summary['avg_delivery_rate'] = round($total_rate / count($delivery_rates), 2);

            // Find best and worst performing
            usort($delivery_rates, function($a, $b) {
                return $b['rate'] - $a['rate'];
            });

            $summary['best_performing'] = $delivery_rates[0];
            $summary['worst_performing'] = end($delivery_rates);
        }

        // Cache the result
        self::$cache[$cache_key] = $summary;

        return $summary;
    }

    /**
     * Helper functions
     */

    /**
     * Get all batch data using WordPress options API
     */
    private static function get_all_batch_data($limit = null) {
        global $wpdb;

        // Use WordPress safe query to get batch options
        $query = $wpdb->prepare(
            "SELECT option_name, option_value 
             FROM {$wpdb->options} 
             WHERE option_name LIKE %s 
             ORDER BY option_id DESC",
            'canwbe_batch_%'
        );

        if ($limit) {
            $query .= $wpdb->prepare(" LIMIT %d", $limit);
        }

        $results = $wpdb->get_results($query);
        $batch_data = array();

        foreach ($results as $result) {
            $data = maybe_unserialize($result->option_value);
            if (is_array($data)) {
                $batch_data[$result->option_name] = $data;
            }
        }

        return $batch_data;
    }

    /**
     * Get basic newsletter stats from post meta
     */
    private static function get_newsletter_basic_stats($newsletter_id) {
        return array(
            'view_count' => (int) get_post_meta($newsletter_id, '_newsletter_view_count', true),
            'last_viewed' => get_post_meta($newsletter_id, '_newsletter_last_viewed', true),
            'subscriber_count_at_send' => (int) get_post_meta($newsletter_id, '_newsletter_subscriber_count', true)
        );
    }

    /**
     * Check if basic SMTP logs are available
     */
    private static function has_basic_smtp_logs() {
        if (!function_exists('wp_mail_smtp')) {
            return false;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'wpmailsmtp_emails_log';

        return $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) === $table_name;
    }

    /**
     * Get SMTP logs count (safe way)
     */
    private static function get_smtp_logs_count() {
        if (!self::has_basic_smtp_logs()) {
            return 0;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'wpmailsmtp_emails_log';

        // Get count of emails that might be newsletters
        $newsletters = get_posts(array(
            'post_type' => 'newsletter',
            'post_status' => 'publish',
            'numberposts' => -1,
            'fields' => 'post_title'
        ));

        if (empty($newsletters)) {
            return 0;
        }

        $title_conditions = array();
        $prepare_values = array();

        foreach ($newsletters as $newsletter) {
            $title_conditions[] = "subject LIKE %s";
            $prepare_values[] = '%' . $wpdb->esc_like($newsletter->post_title) . '%';
        }

        $where_clause = '(' . implode(' OR ', $title_conditions) . ')';

        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} WHERE {$where_clause}",
            $prepare_values
        ));

        return (int) $count;
    }

    /**
     * Update newsletter stats when accessed
     */
    public static function track_newsletter_view($newsletter_id) {
        if (!$newsletter_id) {
            return;
        }

        // Increment view count
        $current_count = get_post_meta($newsletter_id, '_newsletter_view_count', true);
        if (!$current_count) $current_count = 0;

        update_post_meta($newsletter_id, '_newsletter_view_count', $current_count + 1);
        update_post_meta($newsletter_id, '_newsletter_last_viewed', current_time('mysql'));

        // Clear cache for this newsletter
        unset(self::$cache['newsletter_' . $newsletter_id]);
    }

    /**
     * Store newsletter stats when sending
     */
    public static function store_newsletter_send_stats($newsletter_id, $subscriber_count, $batch_id) {
        update_post_meta($newsletter_id, '_newsletter_subscriber_count', $subscriber_count);
        update_post_meta($newsletter_id, '_newsletter_batch_id', $batch_id);
        update_post_meta($newsletter_id, '_newsletter_sent_date', current_time('mysql'));

        // Clear cache
        unset(self::$cache['newsletter_' . $newsletter_id]);
        unset(self::$cache['basic_analytics']);
    }

    /**
     * Get newsletter sending history
     */
    public static function get_sending_history($limit = 10) {
        // Check cache first
        $cache_key = 'sending_history_' . $limit;
        if (isset(self::$cache[$cache_key])) {
            return self::$cache[$cache_key];
        }

        $newsletters = get_posts(array(
            'post_type' => 'newsletter',
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => '_newsletter_sent_date',
                    'compare' => 'EXISTS'
                )
            ),
            'numberposts' => $limit,
            'orderby' => 'meta_value',
            'meta_key' => '_newsletter_sent_date',
            'order' => 'DESC'
        ));

        $history = array();

        foreach ($newsletters as $newsletter) {
            $sent_date = get_post_meta($newsletter->ID, '_newsletter_sent_date', true);
            $subscriber_count = get_post_meta($newsletter->ID, '_newsletter_subscriber_count', true);
            $batch_id = get_post_meta($newsletter->ID, '_newsletter_batch_id', true);

            $history[] = array(
                'newsletter_id' => $newsletter->ID,
                'title' => $newsletter->post_title,
                'sent_date' => $sent_date,
                'subscriber_count' => (int) $subscriber_count,
                'batch_id' => $batch_id
            );
        }

        // Cache the result
        self::$cache[$cache_key] = $history;

        return $history;
    }

    /**
     * Clean up old analytics data
     */
    public static function cleanup_old_data() {
        // Clean up old batch data (older than 90 days)
        global $wpdb;

        $old_batches = $wpdb->get_results($wpdb->prepare(
            "SELECT option_name, option_value 
             FROM {$wpdb->options} 
             WHERE option_name LIKE %s",
            'canwbe_batch_%'
        ));

        $cutoff_date = strtotime('-90 days');
        $deleted_count = 0;

        foreach ($old_batches as $batch_option) {
            $batch_data = maybe_unserialize($batch_option->option_value);

            if (is_array($batch_data) && isset($batch_data['created_at'])) {
                $created_time = strtotime($batch_data['created_at']);

                if ($created_time < $cutoff_date) {
                    delete_option($batch_option->option_name);
                    $deleted_count++;
                }
            }
        }

        if ($deleted_count > 0) {
            error_log("CANWBE Basic Analytics: Cleaned up {$deleted_count} old batch records");
        }

        // Clear cache
        self::clear_cache();
    }

    /**
     * Clear cache
     */
    public static function clear_cache() {
        self::$cache = array();

        // Clear WordPress transients
        delete_transient('canwbe_basic_analytics_cache');
    }

    /**
     * Get cache statistics
     */
    public static function get_cache_stats() {
        return array(
            'cached_items' => count(self::$cache),
            'cache_keys' => array_keys(self::$cache)
        );
    }
}

// Initialize basic analytics
CANWBE_Basic_Analytics::init();
