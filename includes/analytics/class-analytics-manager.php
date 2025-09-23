<?php
/**
 * Analytics Manager
 *
 * Main controller for newsletter analytics system
 * Manages basic and Pro analytics integration
 *
 * @package Create_A_Newsletter_With_The_Block_Editor
 * @since 1.4.1
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class CANWBE_Analytics_Manager {

    /**
     * Initialize analytics system
     */
    public static function init() {
        // Load analytics classes
        self::load_analytics_classes();

        // Initialize components
        self::init_components();

        // Add admin menu
        add_action('admin_menu', array(__CLASS__, 'add_analytics_menu'), 20);

        // Enqueue admin styles
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_styles'));

        // AJAX handlers
        add_action('wp_ajax_canwbe_refresh_analytics', array(__CLASS__, 'ajax_refresh_analytics'));
        add_action('wp_ajax_canwbe_get_campaign_details', array(__CLASS__, 'ajax_get_campaign_details'));
        add_action('wp_ajax_canwbe_export_analytics', array(__CLASS__, 'ajax_export_analytics'));

        // Export handler
        add_action('admin_init', array(__CLASS__, 'handle_export_request'));
    }

    /**
     * Load analytics classes
     */
    private static function load_analytics_classes() {
        $analytics_path = CANWBE_PLUGIN_PATH . 'includes/analytics/';

        $files = array(
            'class-smtp-integration.php',
            'class-basic-analytics.php',
            'class-analytics-renderer.php',
            'class-analytics-renderer-helpers.php',
            'analytics-helpers.php'
        );

        foreach ($files as $file) {
            $file_path = $analytics_path . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            }
        }
    }

    /**
     * Initialize components
     */
    private static function init_components() {
        // Initialize SMTP Pro integration if available
        if (class_exists('CANWBE_SMTP_Integration')) {
            CANWBE_SMTP_Integration::init();
        }

        // Initialize basic analytics
        if (class_exists('CANWBE_Basic_Analytics')) {
            CANWBE_Basic_Analytics::init();
        }

        // Initialize renderer
        if (class_exists('CANWBE_Analytics_Renderer')) {
            CANWBE_Analytics_Renderer::init();
        }
    }

    /**
     * Check if WP Mail SMTP Pro integration is available
     */
    public static function has_smtp_pro_integration() {
        return class_exists('CANWBE_SMTP_Integration') &&
            CANWBE_SMTP_Integration::is_available();
    }

    /**
     * Enqueue admin styles for analytics pages
     */
    public static function enqueue_admin_styles($hook) {
        // Only enqueue on analytics pages
        if (strpos($hook, 'canwbe-analytics') === false &&
            strpos($hook, 'newsletter') === false) {
            return;
        }

        // Enqueue CSS file
        wp_enqueue_style(
            'canwbe-analytics-admin',
            CANWBE_PLUGIN_URL . 'assets/css/analytics-admin.css',
            array(),
            CANWBE_VERSION
        );
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
     * Main analytics page
     */
    public static function analytics_page() {
        $has_smtp_pro = self::has_smtp_pro_integration();

        if ($has_smtp_pro) {
            // Show Pro analytics
            self::show_pro_analytics();
        } else {
            // Show basic analytics with upgrade notice
            self::show_basic_analytics();
        }
    }

    /**
     * Show Pro analytics page
     */
    private static function show_pro_analytics() {
        if (!class_exists('CANWBE_SMTP_Integration') || !class_exists('CANWBE_Analytics_Renderer')) {
            wp_die(__('Required analytics classes not found', 'create-a-newsletter-with-the-block-editor'));
        }

        // Get Pro analytics data
        $overall_stats = CANWBE_SMTP_Integration::get_overall_analytics(30);
        $campaigns = CANWBE_SMTP_Integration::get_campaigns_with_smtp_data(20);
        $system_status = CANWBE_SMTP_Integration::get_system_status();

        // Render Pro analytics page
        CANWBE_Analytics_Renderer::render_pro_page($overall_stats, $campaigns, $system_status);
    }

    /**
     * Show basic analytics page
     */
    private static function show_basic_analytics() {
        if (!class_exists('CANWBE_Basic_Analytics') || !class_exists('CANWBE_Analytics_Renderer')) {
            wp_die(__('Required analytics classes not found', 'create-a-newsletter-with-the-block-editor'));
        }

        // Get basic analytics data
        $analytics_data = CANWBE_Basic_Analytics::get_analytics_data();
        $campaigns = CANWBE_Basic_Analytics::get_newsletter_campaigns();
        $has_basic_smtp = function_exists('wp_mail_smtp');

        // Render basic analytics page
        CANWBE_Analytics_Renderer::render_basic_page($analytics_data, $campaigns, $has_basic_smtp);
    }

    /**
     * Get analytics data based on available integration
     */
    public static function get_analytics_data($days = 30) {
        if (self::has_smtp_pro_integration()) {
            return CANWBE_SMTP_Integration::get_overall_analytics($days);
        } else {
            return CANWBE_Basic_Analytics::get_analytics_data();
        }
    }

    /**
     * Get campaign data based on available integration
     */
    public static function get_campaign_data($limit = 20) {
        if (self::has_smtp_pro_integration()) {
            return CANWBE_SMTP_Integration::get_campaigns_with_smtp_data($limit);
        } else {
            return CANWBE_Basic_Analytics::get_newsletter_campaigns($limit);
        }
    }

    /**
     * AJAX handler for refreshing analytics
     */
    public static function ajax_refresh_analytics() {
        check_ajax_referer('canwbe_admin', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'create-a-newsletter-with-the-block-editor'));
        }

        // Clear any caches if needed
        if (self::has_smtp_pro_integration()) {
            CANWBE_SMTP_Integration::clear_cache();
        }

        wp_send_json_success(array(
            'message' => __('Analytics data refreshed successfully', 'create-a-newsletter-with-the-block-editor'),
            'has_pro' => self::has_smtp_pro_integration()
        ));
    }

    /**
     * AJAX handler for getting campaign details
     */
    public static function ajax_get_campaign_details() {
        check_ajax_referer('canwbe_admin', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'create-a-newsletter-with-the-block-editor'));
        }

        $newsletter_id = isset($_POST['newsletter_id']) ? intval($_POST['newsletter_id']) : 0;
        if (!$newsletter_id) {
            wp_send_json_error(__('Invalid newsletter ID', 'create-a-newsletter-with-the-block-editor'));
        }

        // Get detailed campaign data if SMTP Pro is available
        if (self::has_smtp_pro_integration()) {
            $campaign_data = CANWBE_SMTP_Integration::get_newsletter_analytics($newsletter_id);
            if ($campaign_data) {
                wp_send_json_success($campaign_data);
            } else {
                wp_send_json_error(__('Campaign data not found', 'create-a-newsletter-with-the-block-editor'));
            }
        } else {
            wp_send_json_error(__('Advanced analytics not available', 'create-a-newsletter-with-the-block-editor'));
        }
    }

    /**
     * AJAX handler for exporting analytics
     */
    public static function ajax_export_analytics() {
        check_ajax_referer('canwbe_admin', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'create-a-newsletter-with-the-block-editor'));
        }

        // Redirect to export handler
        wp_send_json_success(array(
            'redirect' => wp_nonce_url(
                admin_url('admin.php?action=canwbe_export_analytics'),
                'canwbe_export_analytics',
                'nonce'
            )
        ));
    }

    /**
     * Handle export request
     */
    public static function handle_export_request() {
        if (!isset($_GET['action']) || $_GET['action'] !== 'canwbe_export_analytics') {
            return;
        }

        if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'canwbe_export_analytics')) {
            wp_die(__('Invalid nonce', 'create-a-newsletter-with-the-block-editor'));
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('Permission denied', 'create-a-newsletter-with-the-block-editor'));
        }

        // Export analytics data
        self::export_analytics_csv();
    }

    /**
     * Export analytics to CSV
     */
    private static function export_analytics_csv() {
        $campaigns = self::get_campaign_data(50); // Get more for export

        if (empty($campaigns)) {
            wp_die(__('No campaign data to export', 'create-a-newsletter-with-the-block-editor'));
        }

        $filename = 'newsletter-analytics-' . date('Y-m-d') . '.csv';

        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // Determine CSV headers based on available data
        if (self::has_smtp_pro_integration()) {
            // Pro headers
            fputcsv($output, array(
                'Newsletter Title',
                'Date',
                'Emails Sent',
                'Delivered',
                'Opens',
                'Unique Opens',
                'Open Rate %',
                'Clicks',
                'Unique Clicks',
                'Click Rate %',
                'Bounces',
                'Bounce Rate %',
                'Complaints',
                'Unsubscribes'
            ));

            foreach ($campaigns as $campaign) {
                fputcsv($output, array(
                    $campaign['title'] ?? '',
                    $campaign['date'] ?? '',
                    $campaign['emails_sent'] ?? 0,
                    $campaign['delivered'] ?? 0,
                    $campaign['opens'] ?? 0,
                    $campaign['unique_opens'] ?? 0,
                    $campaign['open_rate'] ?? 0,
                    $campaign['clicks'] ?? 0,
                    $campaign['unique_clicks'] ?? 0,
                    $campaign['click_rate'] ?? 0,
                    $campaign['bounces'] ?? 0,
                    $campaign['bounce_rate'] ?? 0,
                    $campaign['complaints'] ?? 0,
                    $campaign['unsubscribes'] ?? 0
                ));
            }
        } else {
            // Basic headers
            fputcsv($output, array(
                'Newsletter Title',
                'Date',
                'Total Recipients',
                'Emails Delivered',
                'Failed Emails',
                'Status',
                'Delivery Rate %'
            ));

            foreach ($campaigns as $campaign) {
                $delivery_rate = 0;
                if (isset($campaign['total_emails']) && $campaign['total_emails'] > 0) {
                    $sent_emails = isset($campaign['sent_emails']) ? $campaign['sent_emails'] : 0;
                    $delivery_rate = round(($sent_emails / $campaign['total_emails']) * 100, 2);
                }

                fputcsv($output, array(
                    $campaign['title'] ?? '',
                    $campaign['created_at'] ?? '',
                    $campaign['total_emails'] ?? 0,
                    $campaign['sent_emails'] ?? 0,
                    $campaign['failed_emails'] ?? 0,
                    isset($campaign['status']) ? ucfirst($campaign['status']) : 'Unknown',
                    $delivery_rate
                ));
            }
        }

        fclose($output);
        exit;
    }

    /**
     * Get system status
     */
    public static function get_system_status() {
        $status = array(
            'wp_mail_smtp_active' => function_exists('wp_mail_smtp'),
            'wp_mail_smtp_pro_active' => self::has_smtp_pro_integration(),
            'batch_system_active' => class_exists('CANWBE_Batch_Email_Sender'),
            'total_subscribers' => count(get_users(array('role' => 'newsletter_subscriber', 'fields' => 'ID'))),
            'total_newsletters' => count(get_posts(array('post_type' => 'newsletter', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids')))
        );

        if (self::has_smtp_pro_integration()) {
            $status = array_merge($status, CANWBE_SMTP_Integration::get_system_status());
        }

        return $status;
    }

    /**
     * Check if basic logs are available
     */
    public static function has_basic_logs() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wpmailsmtp_emails_log';
        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) === $table_name;

        return $table_exists;
    }

    /**
     * Wrapper: get campaign stats from WP Mail SMTP logs.
     *
     * @param string $subject
     * @return array|WP_Error
     */
    public function canwbe_get_wpmailsmtp_campaign_stats( $subject ) {
        // Use the procedural helper we added in class-smtp-integration.php
        if ( ! function_exists( 'canwbe_smtp_get_campaign_stats' ) ) {
            return new WP_Error( 'helper_missing', 'Integración SMTP no disponible.' );
        }

        return canwbe_smtp_get_campaign_stats( $subject );
    }
}

// Initialize the analytics system
CANWBE_Analytics_Manager::init();
