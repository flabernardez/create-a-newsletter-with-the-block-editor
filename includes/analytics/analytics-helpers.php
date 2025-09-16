<?php
/**
 * Analytics Helpers
 *
 * General utility functions and helpers for newsletter analytics
 * Contains global functions, constants, and utility methods
 *
 * @package Create_A_Newsletter_With_The_Block_Editor
 * @since 1.4.1
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Analytics Constants
 */
if (!defined('CANWBE_ANALYTICS_CACHE_TIME')) {
    define('CANWBE_ANALYTICS_CACHE_TIME', HOUR_IN_SECONDS);
}

if (!defined('CANWBE_ANALYTICS_BATCH_CLEANUP_DAYS')) {
    define('CANWBE_ANALYTICS_BATCH_CLEANUP_DAYS', 90);
}

if (!defined('CANWBE_ANALYTICS_DEFAULT_LIMIT')) {
    define('CANWBE_ANALYTICS_DEFAULT_LIMIT', 20);
}

/**
 * Check if analytics system is properly initialized
 */
function canwbe_analytics_is_initialized() {
    return class_exists('CANWBE_Analytics_Manager') &&
        class_exists('CANWBE_Basic_Analytics') &&
        class_exists('CANWBE_Analytics_Renderer');
}

/**
 * Get current analytics mode (pro or basic)
 */
function canwbe_analytics_get_mode() {
    if (canwbe_analytics_has_smtp_pro()) {
        return 'pro';
    }
    return 'basic';
}

/**
 * Check if WP Mail SMTP Pro is available
 */
function canwbe_analytics_has_smtp_pro() {
    return class_exists('CANWBE_SMTP_Integration') &&
        CANWBE_SMTP_Integration::is_available();
}

/**
 * Get analytics data based on current mode
 */
function canwbe_analytics_get_data($days = 30) {
    if (!canwbe_analytics_is_initialized()) {
        return false;
    }

    return CANWBE_Analytics_Manager::get_analytics_data($days);
}

/**
 * Get campaign data based on current mode
 */
function canwbe_analytics_get_campaigns($limit = null) {
    if (!canwbe_analytics_is_initialized()) {
        return array();
    }

    if ($limit === null) {
        $limit = CANWBE_ANALYTICS_DEFAULT_LIMIT;
    }

    return CANWBE_Analytics_Manager::get_campaign_data($limit);
}

/**
 * Get newsletter performance summary
 */
function canwbe_analytics_get_newsletter_performance($newsletter_id) {
    if (!$newsletter_id) {
        return false;
    }

    if (canwbe_analytics_has_smtp_pro()) {
        return CANWBE_SMTP_Integration::get_newsletter_analytics($newsletter_id);
    } else {
        return CANWBE_Basic_Analytics::get_newsletter_analytics($newsletter_id);
    }
}

/**
 * Track newsletter view
 */
function canwbe_analytics_track_view($newsletter_id) {
    if (!$newsletter_id || !canwbe_analytics_is_initialized()) {
        return false;
    }

    // Always track in basic analytics
    CANWBE_Basic_Analytics::track_newsletter_view($newsletter_id);

    // Additional tracking if Pro is available
    if (canwbe_analytics_has_smtp_pro()) {
        // Could add Pro-specific tracking here
        do_action('canwbe_analytics_newsletter_viewed', $newsletter_id);
    }

    return true;
}

/**
 * Get subscriber statistics
 */
function canwbe_analytics_get_subscriber_stats() {
    if (!class_exists('CANWBE_Basic_Analytics')) {
        return array();
    }

    return CANWBE_Basic_Analytics::get_subscriber_stats();
}

/**
 * Format analytics number for display
 */
function canwbe_analytics_format_number($number, $type = 'default') {
    if (!is_numeric($number)) {
        return '0';
    }

    switch ($type) {
        case 'percentage':
            return number_format($number, 1) . '%';

        case 'rate':
            return number_format($number, 2) . '%';

        case 'compact':
            if ($number >= 1000000) {
                return number_format($number / 1000000, 1) . 'M';
            } elseif ($number >= 1000) {
                return number_format($number / 1000, 1) . 'K';
            }
            return number_format($number);

        case 'currency':
            // For future use with paid analytics features
            return '$' . number_format($number, 2);

        default:
            return number_format_i18n($number);
    }
}

/**
 * Get performance color for metric values
 */
function canwbe_analytics_get_performance_color($value, $metric_type) {
    $thresholds = array(
        'open_rate' => array('excellent' => 25, 'good' => 20, 'average' => 15),
        'click_rate' => array('excellent' => 5, 'good' => 3, 'average' => 2),
        'delivery_rate' => array('excellent' => 98, 'good' => 95, 'average' => 90),
        'bounce_rate' => array('excellent' => 1, 'good' => 2, 'average' => 5) // Reverse logic
    );

    if (!isset($thresholds[$metric_type])) {
        return '#666';
    }

    $colors = array(
        'excellent' => '#00a32a',
        'good' => '#10b981',
        'average' => '#dba617',
        'poor' => '#d63638'
    );

    $threshold = $thresholds[$metric_type];

    if ($metric_type === 'bounce_rate') {
        // Lower is better for bounce rate
        if ($value <= $threshold['excellent']) return $colors['excellent'];
        if ($value <= $threshold['good']) return $colors['good'];
        if ($value <= $threshold['average']) return $colors['average'];
        return $colors['poor'];
    } else {
        // Higher is better for other metrics
        if ($value >= $threshold['excellent']) return $colors['excellent'];
        if ($value >= $threshold['good']) return $colors['good'];
        if ($value >= $threshold['average']) return $colors['average'];
        return $colors['poor'];
    }
}

/**
 * Get performance class for CSS styling
 */
function canwbe_analytics_get_performance_class($value, $metric_type) {
    $thresholds = array(
        'open_rate' => array('excellent' => 25, 'good' => 20, 'average' => 15),
        'click_rate' => array('excellent' => 5, 'good' => 3, 'average' => 2),
        'delivery_rate' => array('excellent' => 98, 'good' => 95, 'average' => 90),
        'bounce_rate' => array('excellent' => 1, 'good' => 2, 'average' => 5) // Reverse logic
    );

    if (!isset($thresholds[$metric_type])) {
        return 'average';
    }

    $threshold = $thresholds[$metric_type];

    if ($metric_type === 'bounce_rate') {
        // Lower is better for bounce rate
        if ($value <= $threshold['excellent']) return 'excellent';
        if ($value <= $threshold['good']) return 'good';
        if ($value <= $threshold['average']) return 'average';
        return 'poor';
    } else {
        // Higher is better for other metrics
        if ($value >= $threshold['excellent']) return 'excellent';
        if ($value >= $threshold['good']) return 'good';
        if ($value >= $threshold['average']) return 'average';
        return 'poor';
    }
}

/**
 * Calculate time difference in human readable format
 */
function canwbe_analytics_time_diff($datetime) {
    if (empty($datetime)) {
        return __('Never', 'create-a-newsletter-with-the-block-editor');
    }

    $time = time() - strtotime($datetime);

    if ($time < 0) {
        return __('In the future', 'create-a-newsletter-with-the-block-editor');
    }

    if ($time < 60) {
        return __('Just now', 'create-a-newsletter-with-the-block-editor');
    }

    return human_time_diff(strtotime($datetime), time()) . ' ' . __('ago', 'create-a-newsletter-with-the-block-editor');
}

/**
 * Validate analytics date range
 */
function canwbe_analytics_validate_date_range($days) {
    $days = intval($days);

    // Allowed ranges: 7, 30, 90, 365 days
    $allowed_ranges = array(7, 30, 90, 365);

    if (!in_array($days, $allowed_ranges)) {
        return 30; // Default to 30 days
    }

    return $days;
}

/**
 * Get analytics cache key
 */
function canwbe_analytics_get_cache_key($type, $params = array()) {
    $key_parts = array('canwbe_analytics', $type);

    if (!empty($params)) {
        $key_parts[] = md5(serialize($params));
    }

    return implode('_', $key_parts);
}

/**
 * Set analytics cache
 */
function canwbe_analytics_set_cache($key, $data, $expiration = null) {
    if ($expiration === null) {
        $expiration = CANWBE_ANALYTICS_CACHE_TIME;
    }

    return set_transient($key, $data, $expiration);
}

/**
 * Get analytics cache
 */
function canwbe_analytics_get_cache($key) {
    return get_transient($key);
}

/**
 * Delete analytics cache
 */
function canwbe_analytics_delete_cache($key = null) {
    if ($key) {
        return delete_transient($key);
    }

    // Delete all analytics caches
    global $wpdb;

    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            $wpdb->esc_like('_transient_canwbe_analytics') . '%'
        )
    );

    return true;
}

/**
 * Check if user can view analytics
 */
function canwbe_analytics_user_can_view() {
    return current_user_can('manage_options');
}

/**
 * Check if user can export analytics
 */
function canwbe_analytics_user_can_export() {
    return current_user_can('manage_options');
}

/**
 * Log analytics event
 */
function canwbe_analytics_log($message, $data = array(), $level = 'info') {
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }

    $log_message = 'CANWBE Analytics [' . strtoupper($level) . ']: ' . $message;

    if (!empty($data)) {
        $log_message .= ' - Data: ' . wp_json_encode($data);
    }

    error_log($log_message);
}

/**
 * Get analytics system status
 */
function canwbe_analytics_get_system_status() {
    if (!canwbe_analytics_is_initialized()) {
        return array('status' => 'error', 'message' => 'Analytics system not initialized');
    }

    return CANWBE_Analytics_Manager::get_system_status();
}

/**
 * Clean up old analytics data
 */
function canwbe_analytics_cleanup_old_data($days = null) {
    if ($days === null) {
        $days = CANWBE_ANALYTICS_BATCH_CLEANUP_DAYS;
    }

    // Clean batch data
    if (class_exists('CANWBE_Basic_Analytics')) {
        CANWBE_Basic_Analytics::cleanup_old_data();
    }

    // Clean SMTP mappings
    if (class_exists('CANWBE_SMTP_Integration')) {
        CANWBE_SMTP_Integration::cleanup_old_mappings();
    }

    // Clean caches
    canwbe_analytics_delete_cache();

    canwbe_analytics_log('Cleaned up old analytics data', array('days' => $days));

    return true;
}

/**
 * Get default analytics settings
 */
function canwbe_analytics_get_default_settings() {
    return array(
        'cache_enabled' => true,
        'cache_duration' => CANWBE_ANALYTICS_CACHE_TIME,
        'cleanup_days' => CANWBE_ANALYTICS_BATCH_CLEANUP_DAYS,
        'default_date_range' => 30,
        'show_pro_features' => true,
        'export_format' => 'csv'
    );
}

/**
 * Update analytics settings
 */
function canwbe_analytics_update_settings($settings) {
    $default_settings = canwbe_analytics_get_default_settings();
    $settings = wp_parse_args($settings, $default_settings);

    return update_option('canwbe_analytics_settings', $settings);
}

/**
 * Get analytics settings
 */
function canwbe_analytics_get_settings() {
    $settings = get_option('canwbe_analytics_settings', array());
    return wp_parse_args($settings, canwbe_analytics_get_default_settings());
}

/**
 * Calculate growth rate between two values
 */
function canwbe_analytics_calculate_growth($current, $previous) {
    if ($previous == 0) {
        return $current > 0 ? 100 : 0;
    }

    return round((($current - $previous) / $previous) * 100, 1);
}

/**
 * Get performance recommendations based on metrics
 */
function canwbe_analytics_get_recommendations($metrics) {
    $recommendations = array();

    if (!is_array($metrics)) {
        return $recommendations;
    }

    // Open rate recommendations
    if (isset($metrics['open_rate']) && $metrics['open_rate'] < 20) {
        $recommendations[] = array(
            'type' => 'open_rate',
            'priority' => 'high',
            'title' => __('Improve Open Rates', 'create-a-newsletter-with-the-block-editor'),
            'description' => __('Try more compelling subject lines, test send times, and segment your audience.', 'create-a-newsletter-with-the-block-editor'),
            'actions' => array(
                __('A/B test subject lines', 'create-a-newsletter-with-the-block-editor'),
                __('Optimize send times', 'create-a-newsletter-with-the-block-editor'),
                __('Segment your audience', 'create-a-newsletter-with-the-block-editor')
            )
        );
    }

    // Click rate recommendations
    if (isset($metrics['click_rate']) && $metrics['click_rate'] < 2) {
        $recommendations[] = array(
            'type' => 'click_rate',
            'priority' => 'high',
            'title' => __('Boost Engagement', 'create-a-newsletter-with-the-block-editor'),
            'description' => __('Add clear call-to-action buttons, include relevant links, and create compelling content.', 'create-a-newsletter-with-the-block-editor'),
            'actions' => array(
                __('Add clear CTAs', 'create-a-newsletter-with-the-block-editor'),
                __('Include relevant links', 'create-a-newsletter-with-the-block-editor'),
                __('Improve content quality', 'create-a-newsletter-with-the-block-editor')
            )
        );
    }

    // Bounce rate recommendations
    if (isset($metrics['bounce_rate']) && $metrics['bounce_rate'] > 3) {
        $recommendations[] = array(
            'type' => 'bounce_rate',
            'priority' => 'critical',
            'title' => __('Clean Your List', 'create-a-newsletter-with-the-block-editor'),
            'description' => __('Remove invalid email addresses and use double opt-in for new subscribers.', 'create-a-newsletter-with-the-block-editor'),
            'actions' => array(
                __('Remove invalid emails', 'create-a-newsletter-with-the-block-editor'),
                __('Use double opt-in', 'create-a-newsletter-with-the-block-editor'),
                __('Verify email addresses', 'create-a-newsletter-with-the-block-editor')
            )
        );
    }

    // Delivery rate recommendations
    if (isset($metrics['delivery_rate']) && $metrics['delivery_rate'] < 95) {
        $recommendations[] = array(
            'type' => 'delivery_rate',
            'priority' => 'critical',
            'title' => __('Fix Delivery Issues', 'create-a-newsletter-with-the-block-editor'),
            'description' => __('Check SMTP settings, verify domain authentication, and monitor sender reputation.', 'create-a-newsletter-with-the-block-editor'),
            'actions' => array(
                __('Check SMTP configuration', 'create-a-newsletter-with-the-block-editor'),
                __('Set up SPF/DKIM records', 'create-a-newsletter-with-the-block-editor'),
                __('Monitor sender reputation', 'create-a-newsletter-with-the-block-editor')
            )
        );
    }

    return $recommendations;
}

/**
 * Export analytics data to CSV
 */
function canwbe_analytics_export_csv($data, $filename = null) {
    if (empty($data) || !is_array($data)) {
        return false;
    }

    if ($filename === null) {
        $filename = 'newsletter-analytics-' . date('Y-m-d-H-i-s') . '.csv';
    }

    // Set headers
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // Add BOM for UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Write headers
    if (!empty($data)) {
        $headers = array_keys($data[0]);
        fputcsv($output, $headers);

        // Write data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }

    fclose($output);
    exit;
}

/**
 * Sanitize analytics input
 */
function canwbe_analytics_sanitize_input($input, $type = 'text') {
    switch ($type) {
        case 'int':
            return intval($input);

        case 'float':
            return floatval($input);

        case 'email':
            return sanitize_email($input);

        case 'url':
            return esc_url_raw($input);

        case 'date':
            return sanitize_text_field($input);

        case 'array':
            return is_array($input) ? array_map('sanitize_text_field', $input) : array();

        default:
            return sanitize_text_field($input);
    }
}

/**
 * Check if feature is available in current mode
 */
function canwbe_analytics_feature_available($feature) {
    $pro_features = array(
        'open_tracking',
        'click_tracking',
        'bounce_tracking',
        'detailed_reports',
        'advanced_insights',
        'performance_benchmarks'
    );

    $basic_features = array(
        'delivery_tracking',
        'basic_reports',
        'subscriber_stats',
        'export_data'
    );

    if (in_array($feature, $basic_features)) {
        return true;
    }

    if (in_array($feature, $pro_features)) {
        return canwbe_analytics_has_smtp_pro();
    }

    return false;
}

/**
 * Initialize analytics system
 */
function canwbe_analytics_init() {
    if (!canwbe_analytics_is_initialized()) {
        canwbe_analytics_log('Analytics system initialization failed', array(), 'error');
        return false;
    }

    // Schedule cleanup if not already scheduled
    if (!wp_next_scheduled('canwbe_analytics_cleanup')) {
        wp_schedule_event(time(), 'daily', 'canwbe_analytics_cleanup');
    }

    canwbe_analytics_log('Analytics system initialized successfully');
    return true;
}

// Hook cleanup function
add_action('canwbe_analytics_cleanup', 'canwbe_analytics_cleanup_old_data');

// Initialize analytics on plugin load
add_action('plugins_loaded', 'canwbe_analytics_init', 20);
