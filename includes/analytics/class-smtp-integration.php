<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class CANWBE_SMTP_Integration {

    /**
     * Cache for analytics data
     */
    private static $cache = array();

    /**
     * Initialize the integration
     */
    public static function init() {
        // Only initialize if WP Mail SMTP Pro is available
        if (!self::is_available()) {
            return;
        }

        // Hook into WP Mail SMTP Pro events if they exist
        add_action('wp_mail_smtp_pro_emails_logs_email_saved', array(__CLASS__, 'track_newsletter_email'), 10, 2);
        add_action('wp_mail_smtp_pro_emails_logs_email_updated', array(__CLASS__, 'update_newsletter_tracking'), 10, 2);

        // Schedule cleanup task
        if (!wp_next_scheduled('canwbe_cleanup_smtp_mappings')) {
            wp_schedule_event(time(), 'weekly', 'canwbe_cleanup_smtp_mappings');
        }
        add_action('canwbe_cleanup_smtp_mappings', array(__CLASS__, 'cleanup_old_mappings'));
    }

    /**
     * Check if WP Mail SMTP Pro is available and active
     */
    public static function is_available() {
        return function_exists('wp_mail_smtp') &&
            defined('WPMS_PLUGIN_VER') &&
            class_exists('WPMailSMTP\Pro\Pro');
    }

    /**
     * Get WP Mail SMTP Pro table information using safe WordPress functions
     */
    public static function get_smtp_pro_tables() {
        if (!self::is_available()) {
            return array();
        }

        global $wpdb;
        $tables = array();

        // Check for main emails log table
        $emails_table = $wpdb->prefix . 'wpmailsmtp_emails_log';
        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $emails_table)) === $emails_table) {
            $tables['emails'] = array(
                'table' => $emails_table,
                'exists' => true
            );
        }

        // Check for events table (opens, clicks, etc.)
        $events_table = $wpdb->prefix . 'wpmailsmtp_email_log_events';
        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $events_table)) === $events_table) {
            $tables['events'] = array(
                'table' => $events_table,
                'exists' => true
            );
        }

        // Check for attachments table
        $attachments_table = $wpdb->prefix . 'wpmailsmtp_email_log_attachments';
        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $attachments_table)) === $attachments_table) {
            $tables['attachments'] = array(
                'table' => $attachments_table,
                'exists' => true
            );
        }

        return $tables;
    }

    /**
     * Track newsletter emails when they're logged by WP Mail SMTP Pro
     */
    public static function track_newsletter_email($email_id, $email_data) {
        if (!is_array($email_data) || !isset($email_data['subject'])) {
            return;
        }

        // Check if this is a newsletter email
        if (!self::is_newsletter_email($email_data['subject'])) {
            return;
        }

        // Get newsletter ID from subject
        $newsletter_id = self::get_newsletter_id_from_subject($email_data['subject']);
        if (!$newsletter_id) {
            return;
        }

        $batch_id = get_post_meta($newsletter_id, '_newsletter_batch_id', true);
        if (!$batch_id) {
            return;
        }

        // Store the mapping between our batch system and WP Mail SMTP Pro
        $mapping_data = get_option('canwbe_smtp_email_mappings', array());
        $mapping_data[$email_id] = array(
            'newsletter_id' => $newsletter_id,
            'batch_id' => $batch_id,
            'recipient_email' => isset($email_data['to']) ? sanitize_email($email_data['to']) : '',
            'sent_time' => current_time('mysql'),
            'subject' => isset($email_data['subject']) ? sanitize_text_field($email_data['subject']) : ''
        );
        update_option('canwbe_smtp_email_mappings', $mapping_data);

        // Log the tracking
        error_log('CANWBE SMTP Pro: Tracked newsletter email - Newsletter ID: ' . $newsletter_id . ', Email ID: ' . $email_id);
    }

    /**
     * Update tracking when email events occur (opens, clicks, etc.)
     */
    public static function update_newsletter_tracking($email_id, $event_data) {
        if (!is_array($event_data) || !isset($event_data['event_type'])) {
            return;
        }

        $mappings = get_option('canwbe_smtp_email_mappings', array());

        if (!isset($mappings[$email_id])) {
            return;
        }

        $newsletter_data = $mappings[$email_id];
        $newsletter_id = $newsletter_data['newsletter_id'];

        // Update newsletter statistics based on event type
        switch ($event_data['event_type']) {
            case 'opened':
            case 'open':
                self::increment_newsletter_stat($newsletter_id, 'opens');
                break;
            case 'clicked':
            case 'click':
                self::increment_newsletter_stat($newsletter_id, 'clicks');
                break;
            case 'bounced':
            case 'bounce':
                self::increment_newsletter_stat($newsletter_id, 'bounces');
                break;
            case 'complained':
            case 'complaint':
                self::increment_newsletter_stat($newsletter_id, 'complaints');
                break;
            case 'unsubscribed':
            case 'unsubscribe':
                self::increment_newsletter_stat($newsletter_id, 'unsubscribes');
                break;
        }

        // Log the event
        error_log('CANWBE SMTP Pro: Updated newsletter stats - Newsletter ID: ' . $newsletter_id . ', Event: ' . $event_data['event_type']);
    }

    /**
     * Get enhanced analytics data for a specific newsletter
     */
    public static function get_newsletter_analytics($newsletter_id) {
        if (!self::is_available()) {
            return false;
        }

        // Check cache first
        $cache_key = 'newsletter_' . $newsletter_id;
        if (isset(self::$cache[$cache_key])) {
            return self::$cache[$cache_key];
        }

        $newsletter = get_post($newsletter_id);
        if (!$newsletter || $newsletter->post_type !== 'newsletter') {
            return false;
        }

        $analytics = array(
            'newsletter_id' => $newsletter_id,
            'title' => $newsletter->post_title,
            'date' => $newsletter->post_date,
            'emails_sent' => 0,
            'delivered' => 0,
            'opens' => 0,
            'unique_opens' => 0,
            'clicks' => 0,
            'unique_clicks' => 0,
            'bounces' => 0,
            'complaints' => 0,
            'unsubscribes' => 0,
            'open_rate' => 0,
            'click_rate' => 0,
            'bounce_rate' => 0
        );

        // Get data from SMTP logs using safe WordPress functions
        $email_data = self::get_newsletter_email_data($newsletter->post_title, $newsletter->post_date);

        if ($email_data) {
            $analytics = array_merge($analytics, $email_data);
        }

        // Get data from our own tracking
        $our_stats = self::get_newsletter_stats($newsletter_id);
        if ($our_stats) {
            // Merge with our tracking data (use SMTP data as primary, our data as fallback)
            foreach ($our_stats as $key => $value) {
                if ($analytics[$key] == 0 && $value > 0) {
                    $analytics[$key] = $value;
                }
            }
        }

        // Calculate rates
        if ($analytics['delivered'] > 0) {
            $analytics['open_rate'] = round(($analytics['unique_opens'] / $analytics['delivered']) * 100, 2);
            $analytics['click_rate'] = round(($analytics['unique_clicks'] / $analytics['delivered']) * 100, 2);
            $analytics['bounce_rate'] = round(($analytics['bounces'] / $analytics['delivered']) * 100, 2);
        }

        // Cache the result
        self::$cache[$cache_key] = $analytics;

        return $analytics;
    }

    /**
     * Get overall SMTP analytics for all newsletters (using safe WordPress functions)
     */
    public static function get_overall_analytics($days = 30) {
        if (!self::is_available()) {
            return false;
        }

        // Check cache first
        $cache_key = 'overall_' . $days;
        if (isset(self::$cache[$cache_key])) {
            return self::$cache[$cache_key];
        }

        $stats = array(
            'total_emails' => 0,
            'delivered' => 0,
            'opens' => 0,
            'unique_opens' => 0,
            'clicks' => 0,
            'unique_clicks' => 0,
            'bounces' => 0,
            'delivery_rate' => 0,
            'open_rate' => 0,
            'click_rate' => 0,
            'bounce_rate' => 0
        );

        // Get all newsletters from the specified period
        $newsletters = get_posts(array(
            'post_type' => 'newsletter',
            'post_status' => 'publish',
            'numberposts' => -1,
            'date_query' => array(
                array(
                    'after' => date('Y-m-d', strtotime("-{$days} days"))
                )
            )
        ));

        if (empty($newsletters)) {
            self::$cache[$cache_key] = $stats;
            return $stats;
        }

        // Aggregate stats from all newsletters
        foreach ($newsletters as $newsletter) {
            $newsletter_analytics = self::get_newsletter_analytics($newsletter->ID);
            if ($newsletter_analytics) {
                $stats['total_emails'] += $newsletter_analytics['emails_sent'];
                $stats['delivered'] += $newsletter_analytics['delivered'];
                $stats['opens'] += $newsletter_analytics['opens'];
                $stats['unique_opens'] += $newsletter_analytics['unique_opens'];
                $stats['clicks'] += $newsletter_analytics['clicks'];
                $stats['unique_clicks'] += $newsletter_analytics['unique_clicks'];
                $stats['bounces'] += $newsletter_analytics['bounces'];
            }
        }

        // Calculate rates
        if ($stats['total_emails'] > 0) {
            $stats['delivery_rate'] = round(($stats['delivered'] / $stats['total_emails']) * 100, 2);
        }

        if ($stats['delivered'] > 0) {
            $stats['open_rate'] = round(($stats['unique_opens'] / $stats['delivered']) * 100, 2);
            $stats['click_rate'] = round(($stats['unique_clicks'] / $stats['delivered']) * 100, 2);
            $stats['bounce_rate'] = round(($stats['bounces'] / $stats['delivered']) * 100, 2);
        }

        // Cache the result
        self::$cache[$cache_key] = $stats;

        return $stats;
    }

    /**
     * Get detailed campaign list with SMTP data
     */
    public static function get_campaigns_with_smtp_data($limit = 20) {
        if (!self::is_available()) {
            return array();
        }

        // Check cache first
        $cache_key = 'campaigns_' . $limit;
        if (isset(self::$cache[$cache_key])) {
            return self::$cache[$cache_key];
        }

        $campaigns = array();

        // Get published newsletters
        $newsletters = get_posts(array(
            'post_type' => 'newsletter',
            'post_status' => 'publish',
            'numberposts' => $limit,
            'orderby' => 'date',
            'order' => 'DESC'
        ));

        foreach ($newsletters as $newsletter) {
            $analytics = self::get_newsletter_analytics($newsletter->ID);
            if ($analytics) {
                $campaigns[] = $analytics;
            }
        }

        // Cache the result
        self::$cache[$cache_key] = $campaigns;

        return $campaigns;
    }

    /**
     * Get newsletter email data from SMTP logs (safe WordPress way)
     */
    private static function get_newsletter_email_data($newsletter_title, $newsletter_date) {
        $tables = self::get_smtp_pro_tables();

        if (!isset($tables['emails'])) {
            return false;
        }

        global $wpdb;
        $emails_table = $tables['emails']['table'];

        // Use safe WordPress prepare method
        $emails = $wpdb->get_results($wpdb->prepare(
            "SELECT id, status, date_sent, people FROM {$emails_table} 
             WHERE subject LIKE %s AND date_sent >= %s 
             ORDER BY date_sent DESC",
            '%' . $wpdb->esc_like($newsletter_title) . '%',
            date('Y-m-d', strtotime($newsletter_date))
        ));

        $data = array(
            'emails_sent' => count($emails),
            'delivered' => 0
        );

        // Count delivered emails
        foreach ($emails as $email) {
            if ($email->status === 'sent') {
                $data['delivered']++;
            }
        }

        // Get event data if events table exists
        if (isset($tables['events']) && !empty($emails)) {
            $email_ids = wp_list_pluck($emails, 'id');
            $events_data = self::get_events_data($email_ids);
            $data = array_merge($data, $events_data);
        }

        return $data;
    }

    /**
     * Get events data for email IDs (safe WordPress way)
     */
    private static function get_events_data($email_ids) {
        if (empty($email_ids)) {
            return array();
        }

        $tables = self::get_smtp_pro_tables();
        if (!isset($tables['events'])) {
            return array();
        }

        global $wpdb;
        $events_table = $tables['events']['table'];

        // Create safe placeholders for IN clause
        $placeholders = implode(',', array_fill(0, count($email_ids), '%d'));

        $events = $wpdb->get_results($wpdb->prepare(
            "SELECT event_type, COUNT(*) as total_count, COUNT(DISTINCT email_log_id) as unique_count 
             FROM {$events_table} 
             WHERE email_log_id IN ({$placeholders}) 
             GROUP BY event_type",
            $email_ids
        ));

        $data = array(
            'opens' => 0,
            'unique_opens' => 0,
            'clicks' => 0,
            'unique_clicks' => 0,
            'bounces' => 0,
            'complaints' => 0,
            'unsubscribes' => 0
        );

        foreach ($events as $event) {
            switch ($event->event_type) {
                case 'open':
                case 'opened':
                    $data['opens'] = (int) $event->total_count;
                    $data['unique_opens'] = (int) $event->unique_count;
                    break;
                case 'click':
                case 'clicked':
                    $data['clicks'] = (int) $event->total_count;
                    $data['unique_clicks'] = (int) $event->unique_count;
                    break;
                case 'bounce':
                case 'bounced':
                    $data['bounces'] = (int) $event->unique_count;
                    break;
                case 'complaint':
                case 'complained':
                    $data['complaints'] = (int) $event->unique_count;
                    break;
                case 'unsubscribe':
                case 'unsubscribed':
                    $data['unsubscribes'] = (int) $event->unique_count;
                    break;
            }
        }

        return $data;
    }

    /**
     * Helper functions
     */

    /**
     * Check if email subject belongs to a newsletter (safe way)
     */
    private static function is_newsletter_email($subject) {
        $newsletters = get_posts(array(
            'post_type' => 'newsletter',
            'post_status' => 'publish',
            'numberposts' => -1,
            'fields' => 'post_title'
        ));

        foreach ($newsletters as $newsletter) {
            if (stripos($subject, $newsletter->post_title) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get newsletter ID from email subject (safe way)
     */
    private static function get_newsletter_id_from_subject($subject) {
        $newsletters = get_posts(array(
            'post_type' => 'newsletter',
            'post_status' => 'publish',
            'numberposts' => -1
        ));

        foreach ($newsletters as $newsletter) {
            if (stripos($subject, $newsletter->post_title) !== false) {
                return $newsletter->ID;
            }
        }

        return false;
    }

    /**
     * Increment newsletter statistic (safe way)
     */
    private static function increment_newsletter_stat($newsletter_id, $stat_type) {
        $current_value = get_post_meta($newsletter_id, "_newsletter_stat_{$stat_type}", true);
        if (!$current_value) $current_value = 0;

        update_post_meta($newsletter_id, "_newsletter_stat_{$stat_type}", $current_value + 1);
        update_post_meta($newsletter_id, "_newsletter_last_activity", current_time('mysql'));

        // Clear cache for this newsletter
        unset(self::$cache['newsletter_' . $newsletter_id]);
    }

    /**
     * Get newsletter statistics from meta
     */
    public static function get_newsletter_stats($newsletter_id) {
        return array(
            'opens' => (int) get_post_meta($newsletter_id, '_newsletter_stat_opens', true),
            'unique_opens' => (int) get_post_meta($newsletter_id, '_newsletter_stat_opens', true), // Same as opens for our tracking
            'clicks' => (int) get_post_meta($newsletter_id, '_newsletter_stat_clicks', true),
            'unique_clicks' => (int) get_post_meta($newsletter_id, '_newsletter_stat_clicks', true), // Same as clicks for our tracking
            'bounces' => (int) get_post_meta($newsletter_id, '_newsletter_stat_bounces', true),
            'complaints' => (int) get_post_meta($newsletter_id, '_newsletter_stat_complaints', true),
            'unsubscribes' => (int) get_post_meta($newsletter_id, '_newsletter_stat_unsubscribes', true),
            'last_activity' => get_post_meta($newsletter_id, '_newsletter_last_activity', true)
        );
    }

    /**
     * Clean up old mapping data (runs weekly)
     */
    public static function cleanup_old_mappings() {
        $mappings = get_option('canwbe_smtp_email_mappings', array());
        $cutoff_date = strtotime('-60 days');
        $cleaned = false;

        foreach ($mappings as $email_id => $data) {
            if (isset($data['sent_time']) && strtotime($data['sent_time']) < $cutoff_date) {
                unset($mappings[$email_id]);
                $cleaned = true;
            }
        }

        if ($cleaned) {
            update_option('canwbe_smtp_email_mappings', $mappings);
            error_log('CANWBE SMTP Pro: Cleaned up old email mappings');
        }
    }

    /**
     * Get system status for debugging
     */
    public static function get_system_status() {
        $status = array(
            'wp_mail_smtp_active' => function_exists('wp_mail_smtp'),
            'wp_mail_smtp_pro_active' => self::is_available(),
            'tables_available' => array(),
            'mapping_count' => 0
        );

        if (self::is_available()) {
            $tables = self::get_smtp_pro_tables();
            $status['tables_available'] = array_keys($tables);
        }

        $mappings = get_option('canwbe_smtp_email_mappings', array());
        $status['mapping_count'] = count($mappings);

        return $status;
    }

    /**
     * Clear cache
     */
    public static function clear_cache() {
        self::$cache = array();

        // Also clear WordPress transients if we're using them
        delete_transient('canwbe_smtp_analytics_cache');
    }
}

// Initialize the integration when the class is loaded
if (CANWBE_SMTP_Integration::is_available()) {
    CANWBE_SMTP_Integration::init();
}

/**
 * Detect WP Mail SMTP logs table name.
 *
 * @return string|false Full table name or false if not found.
 */
function canwbe_smtp_detect_wpmailsmtp_table() {
    global $wpdb;

    $candidates = array(
        $wpdb->prefix . 'wpmailsmtp_emails',
        $wpdb->prefix . 'wpmailsmtp_logs',
        $wpdb->prefix . 'wp_mail_smtp_emails',
        $wpdb->prefix . 'mail_smtp_emails',
        $wpdb->prefix . 'mail_smtp_logs',
        $wpdb->prefix . 'wp_mail_smtp_logs',
        $wpdb->prefix . 'wpmail_smtp_emails',
    );

    foreach ( $candidates as $table ) {
        $found = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) );
        if ( $found === $table ) {
            return $table;
        }
    }

    // Fallback: quick scan of all tables for common substrings.
    $all = $wpdb->get_col( "SHOW TABLES" );
    if ( $all ) {
        foreach ( $all as $tname ) {
            if ( false !== stripos( $tname, 'wpmailsmtp' ) || false !== stripos( $tname, 'mail_smtp' ) || false !== stripos( $tname, 'wp_mail_smtp' ) ) {
                return $tname;
            }
        }
    }

    return false;
}

/**
 * Check whether a column exists in a table.
 *
 * @param string $table
 * @param string $column
 * @return bool
 */
function canwbe_smtp_table_has_column( $table, $column ) {
    global $wpdb;

    // table name comes from internal detection, nevertheless validate that it looks like a table name
    if ( ! is_string( $table ) || strpos( $table, $wpdb->prefix ) !== 0 ) {
        return false;
    }

    $col = $wpdb->get_var( $wpdb->prepare( "SHOW COLUMNS FROM {$table} LIKE %s", $column ) );
    return (bool) $col;
}

/**
 * Get campaign stats by subject from WP Mail SMTP logs.
 *
 * Returns array: subject, table, sent, failed, opens, clicks, open_rate, click_rate
 *
 * @param string $subject
 * @return array|WP_Error
 */
function canwbe_smtp_get_campaign_stats( $subject ) {
    global $wpdb;

    $subject = sanitize_text_field( $subject );
    if ( empty( $subject ) ) {
        return new WP_Error( 'empty_subject', 'Asunto vacío.' );
    }

    $table = canwbe_smtp_detect_wpmailsmtp_table();
    if ( ! $table ) {
        return new WP_Error( 'no_table', 'No se ha encontrado la tabla de WP Mail SMTP en la base de datos.' );
    }

    // TOTAL SENT (exact subject match)
    $sent = (int) $wpdb->get_var(
        $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE subject = %s", $subject )
    );

    // FAILED (if status column present)
    $failed = 0;
    if ( canwbe_smtp_table_has_column( $table, 'status' ) ) {
        $failed = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE subject = %s AND ( status IS NULL OR status != 'sent' )",
                $subject
            )
        );
    }

    // OPENS - try several possible columns
    $opens = 0;
    if ( canwbe_smtp_table_has_column( $table, 'opened_at' ) ) {
        $opens = (int) $wpdb->get_var(
            $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE subject = %s AND opened_at IS NOT NULL AND opened_at != ''", $subject )
        );
    } elseif ( canwbe_smtp_table_has_column( $table, 'opened' ) ) {
        $opens = (int) $wpdb->get_var(
            $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE subject = %s AND (opened = 1 OR opened = '1' OR opened = 'Yes' OR opened = 'Sí' OR opened = 'Si')", $subject )
        );
    } elseif ( canwbe_smtp_table_has_column( $table, 'headers' ) ) {
        // best-effort: headers may contain tracking markers
        $opens = (int) $wpdb->get_var(
            $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE subject = %s AND headers LIKE %s", $subject, '%opened%' )
        );
    }

    // CLICKS - similar approach
    $clicks = 0;
    if ( canwbe_smtp_table_has_column( $table, 'clicked_at' ) ) {
        $clicks = (int) $wpdb->get_var(
            $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE subject = %s AND clicked_at IS NOT NULL AND clicked_at != ''", $subject )
        );
    } elseif ( canwbe_smtp_table_has_column( $table, 'clicked' ) ) {
        $clicks = (int) $wpdb->get_var(
            $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE subject = %s AND (clicked = 1 OR clicked = '1' OR clicked = 'Yes' OR clicked = 'Sí' OR clicked = 'Si')", $subject )
        );
    }

    $open_rate  = $sent > 0 ? round( ( $opens / $sent ) * 100, 2 ) : 0;
    $click_rate = $sent > 0 ? round( ( $clicks / $sent ) * 100, 2 ) : 0;

    return array(
        'subject'    => $subject,
        'table'      => $table,
        'sent'       => $sent,
        'failed'     => $failed,
        'opens'      => $opens,
        'clicks'     => $clicks,
        'open_rate'  => $open_rate,
        'click_rate' => $click_rate,
    );
}

/**
 * AJAX handler to return campaign stats (JSON).
 * Requires manage_options capability.
 */
function canwbe_ajax_get_campaign_stats() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'No permission' ), 403 );
    }

    check_ajax_referer( 'canwbe_analytics_nonce', 'nonce' );

    $subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
    if ( empty( $subject ) ) {
        wp_send_json_error( array( 'message' => 'Asunto vacío' ), 400 );
    }

    $stats = canwbe_smtp_get_campaign_stats( $subject );
    if ( is_wp_error( $stats ) ) {
        wp_send_json_error( array( 'message' => $stats->get_error_message() ), 500 );
    }

    wp_send_json_success( $stats );
}
add_action( 'wp_ajax_canwbe_get_campaign_stats', 'canwbe_ajax_get_campaign_stats' );

/**
 * Enqueue admin JS for the analytics page (only on analytics/newsletter admin pages).
 */
function canwbe_smtp_enqueue_admin_assets( $hook ) {
    // limit load: only if page query looks like your analytics page (adjust if needed)
    if ( ! isset( $_GET['page'] ) ) {
        return;
    }

    $page = sanitize_text_field( wp_unslash( $_GET['page'] ) );
    if ( false === stripos( $page, 'analytics' ) && false === stripos( $page, 'newsletter' ) && false === stripos( $page, 'canwbe' ) ) {
        return;
    }

    // assets path: adjust relative path if necessary
    wp_enqueue_script( 'canwbe-analytics-admin', plugin_dir_url( __FILE__ ) . '../../assets/js/canwbe-analytics-admin.js', array( 'jquery' ), '1.0', true );
    wp_localize_script( 'canwbe-analytics-admin', 'canwbeAnalytics', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'canwbe_analytics_nonce' ),
    ) );
}
add_action( 'admin_enqueue_scripts', 'canwbe_smtp_enqueue_admin_assets' );
