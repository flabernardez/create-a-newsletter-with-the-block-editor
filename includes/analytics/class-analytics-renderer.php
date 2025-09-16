<?php
/**
 * Analytics Renderer
 *
 * Handles rendering of analytics pages and UI components
 * Separates presentation logic from data logic
 *
 * @package Create_A_Newsletter_With_The_Block_Editor
 * @since 1.4.1
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class CANWBE_Analytics_Renderer {

    /**
     * Initialize renderer
     */
    public static function init() {
        // Add any renderer-specific hooks here
    }

    /**
     * Render Pro analytics page
     */
    public static function render_pro_page($overall_stats, $campaigns, $system_status) {
        ?>
        <div class="wrap">
            <h1>
                <?php esc_html_e('Newsletter Analytics', 'create-a-newsletter-with-the-block-editor'); ?>
                <span class="pro-badge">PRO</span>
                <button type="button" class="page-title-action" onclick="location.reload()">
                    <?php esc_html_e('Refresh Data', 'create-a-newsletter-with-the-block-editor'); ?>
                </button>
                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?action=canwbe_export_analytics'), 'canwbe_export_analytics', 'nonce')); ?>"
                   class="page-title-action">
                    <?php esc_html_e('Export CSV', 'create-a-newsletter-with-the-block-editor'); ?>
                </a>
            </h1>

            <?php self::render_pro_status_notice($system_status); ?>
            <?php self::render_pro_overview_cards($overall_stats); ?>
            <?php self::render_pro_campaigns_table($campaigns); ?>
            <?php self::render_pro_insights($overall_stats, $campaigns); ?>
        </div>
        <?php
    }

    /**
     * Render basic analytics page
     */
    public static function render_basic_page($analytics_data, $campaigns, $has_basic_smtp) {
        ?>
        <div class="wrap">
            <h1>
                <?php esc_html_e('Newsletter Analytics', 'create-a-newsletter-with-the-block-editor'); ?>
                <button type="button" class="page-title-action" onclick="location.reload()">
                    <?php esc_html_e('Refresh Data', 'create-a-newsletter-with-the-block-editor'); ?>
                </button>
            </h1>

            <?php self::render_upgrade_notice(); ?>
            <?php self::render_system_status($has_basic_smtp); ?>
            <?php self::render_basic_overview_cards($analytics_data); ?>
            <?php self::render_basic_campaigns_table($campaigns); ?>
        </div>
        <?php
    }

    /**
     * Render Pro status notice
     */
    private static function render_pro_status_notice($system_status) {
        ?>
        <div class="notice notice-success">
            <p><strong>✅ <?php esc_html_e('Advanced Analytics Active', 'create-a-newsletter-with-the-block-editor'); ?></strong></p>
            <p><?php esc_html_e('WP Mail SMTP Pro integration provides detailed open rates, click tracking, bounce analysis, and delivery insights.', 'create-a-newsletter-with-the-block-editor'); ?></p>
            <?php if (!empty($system_status['tables_available'])): ?>
                <p><strong><?php esc_html_e('Available tables:', 'create-a-newsletter-with-the-block-editor'); ?></strong> <?php echo esc_html(implode(', ', $system_status['tables_available'])); ?></p>
            <?php endif; ?>
            <?php if (isset($system_status['mapping_count'])): ?>
                <p><strong><?php esc_html_e('Email mappings tracked:', 'create-a-newsletter-with-the-block-editor'); ?></strong> <?php echo esc_html(number_format_i18n($system_status['mapping_count'])); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render upgrade notice for basic version
     */
    private static function render_upgrade_notice() {
        ?>
        <div class="notice notice-info upgrade-notice">
            <div class="upgrade-content">
                <div class="upgrade-icon">🚀</div>
                <div class="upgrade-text">
                    <h3><?php esc_html_e('Upgrade to Advanced Analytics', 'create-a-newsletter-with-the-block-editor'); ?></h3>
                    <p><?php esc_html_e('Get detailed open rates, click tracking, bounce analysis, and delivery insights with WP Mail SMTP Pro!', 'create-a-newsletter-with-the-block-editor'); ?></p>
                    <ul class="upgrade-features">
                        <li>✅ <?php esc_html_e('Real-time open rate tracking', 'create-a-newsletter-with-the-block-editor'); ?></li>
                        <li>✅ <?php esc_html_e('Click-through rate analysis', 'create-a-newsletter-with-the-block-editor'); ?></li>
                        <li>✅ <?php esc_html_e('Bounce and complaint monitoring', 'create-a-newsletter-with-the-block-editor'); ?></li>
                        <li>✅ <?php esc_html_e('Performance benchmarking', 'create-a-newsletter-with-the-block-editor'); ?></li>
                    </ul>
                    <div class="upgrade-buttons">
                        <a href="https://wpmailsmtp.com/?utm_source=plugin&utm_medium=newsletter-analytics&utm_campaign=canwbe"
                           class="button button-primary" target="_blank">
                            <?php esc_html_e('Get WP Mail SMTP Pro', 'create-a-newsletter-with-the-block-editor'); ?>
                        </a>
                        <?php if (!function_exists('wp_mail_smtp')): ?>
                            <a href="<?php echo esc_url(admin_url('plugin-install.php?s=wp-mail-smtp&tab=search&type=term')); ?>"
                               class="button button-secondary">
                                <?php esc_html_e('Install Free Version', 'create-a-newsletter-with-the-block-editor'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render system status
     */
    private static function render_system_status($has_basic_smtp) {
        $notice_class = $has_basic_smtp ? 'notice-success' : 'notice-warning';
        ?>
        <div class="notice <?php echo esc_attr($notice_class); ?>">
            <p><strong><?php esc_html_e('Current Status:', 'create-a-newsletter-with-the-block-editor'); ?></strong></p>
            <ul>
                <li>WP Mail SMTP: <?php echo function_exists('wp_mail_smtp') ? '✅ Active' : '❌ Not found'; ?></li>
                <li>WP Mail SMTP Pro: <?php echo class_exists('CANWBE_SMTP_Integration') && CANWBE_SMTP_Integration::is_available() ? '✅ Active' : '❌ Not available'; ?></li>
                <li>Basic Logs: <?php echo $has_basic_smtp ? '✅ Available' : '⚠️ Limited'; ?></li>
                <li>Batch System: <?php echo class_exists('CANWBE_Batch_Email_Sender') ? '✅ Active' : '❌ Not available'; ?></li>
            </ul>
        </div>
        <?php
    }

    /**
     * Render Pro overview cards
     */
    private static function render_pro_overview_cards($stats) {
        ?>
        <div class="canwbe-analytics-overview pro">
            <div class="canwbe-metric-card pro-card">
                <div class="canwbe-metric-number"><?php echo esc_html(number_format_i18n($stats['total_emails'])); ?></div>
                <div class="canwbe-metric-label"><?php esc_html_e('Emails Sent', 'create-a-newsletter-with-the-block-editor'); ?></div>
                <div class="canwbe-metric-period">
                    <?php echo esc_html($stats['delivery_rate']); ?>% <?php esc_html_e('delivered', 'create-a-newsletter-with-the-block-editor'); ?>
                </div>
            </div>

            <div class="canwbe-metric-card pro-card">
                <div class="canwbe-metric-number"><?php echo esc_html(number_format_i18n($stats['unique_opens'])); ?></div>
                <div class="canwbe-metric-label"><?php esc_html_e('Unique Opens', 'create-a-newsletter-with-the-block-editor'); ?></div>
                <div class="canwbe-metric-period">
                    <?php echo esc_html($stats['open_rate']); ?>% <?php esc_html_e('open rate', 'create-a-newsletter-with-the-block-editor'); ?>
                </div>
            </div>

            <div class="canwbe-metric-card pro-card">
                <div class="canwbe-metric-number"><?php echo esc_html(number_format_i18n($stats['unique_clicks'])); ?></div>
                <div class="canwbe-metric-label"><?php esc_html_e('Unique Clicks', 'create-a-newsletter-with-the-block-editor'); ?></div>
                <div class="canwbe-metric-period">
                    <?php echo esc_html($stats['click_rate']); ?>% <?php esc_html_e('click rate', 'create-a-newsletter-with-the-block-editor'); ?>
                </div>
            </div>

            <div class="canwbe-metric-card pro-card">
                <div class="canwbe-metric-number"><?php echo esc_html(number_format_i18n($stats['bounces'])); ?></div>
                <div class="canwbe-metric-label"><?php esc_html_e('Bounces', 'create-a-newsletter-with-the-block-editor'); ?></div>
                <div class="canwbe-metric-period">
                    <?php echo esc_html($stats['bounce_rate']); ?>% <?php esc_html_e('bounce rate', 'create-a-newsletter-with-the-block-editor'); ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render basic overview cards
     */
    private static function render_basic_overview_cards($data) {
        ?>
        <div class="canwbe-analytics-overview basic">
            <div class="canwbe-metric-card basic-card">
                <div class="canwbe-metric-number"><?php echo esc_html(number_format_i18n($data['total_newsletters'])); ?></div>
                <div class="canwbe-metric-label"><?php esc_html_e('Newsletters Sent', 'create-a-newsletter-with-the-block-editor'); ?></div>
                <div class="canwbe-metric-period"><?php esc_html_e('Total campaigns', 'create-a-newsletter-with-the-block-editor'); ?></div>
            </div>

            <div class="canwbe-metric-card basic-card">
                <div class="canwbe-metric-number"><?php echo esc_html(number_format_i18n($data['total_emails_sent'])); ?></div>
                <div class="canwbe-metric-label"><?php esc_html_e('Emails Delivered', 'create-a-newsletter-with-the-block-editor'); ?></div>
                <div class="canwbe-metric-period">
                    <?php echo esc_html($data['delivery_rate']); ?>% <?php esc_html_e('delivery rate', 'create-a-newsletter-with-the-block-editor'); ?>
                </div>
            </div>

            <div class="canwbe-metric-card basic-card">
                <div class="canwbe-metric-number"><?php echo esc_html(number_format_i18n($data['total_subscribers'])); ?></div>
                <div class="canwbe-metric-label"><?php esc_html_e('Active Subscribers', 'create-a-newsletter-with-the-block-editor'); ?></div>
                <div class="canwbe-metric-period"><?php esc_html_e('Current', 'create-a-newsletter-with-the-block-editor'); ?></div>
            </div>

            <div class="canwbe-metric-card upgrade-card">
                <div class="canwbe-metric-number">?</div>
                <div class="canwbe-metric-label"><?php esc_html_e('Open Rate', 'create-a-newsletter-with-the-block-editor'); ?></div>
                <div class="canwbe-metric-period">
                    <a href="https://wpmailsmtp.com/?utm_source=plugin&utm_medium=analytics-upgrade" target="_blank" class="upgrade-link">
                        <?php esc_html_e('Upgrade for tracking', 'create-a-newsletter-with-the-block-editor'); ?> ↗
                    </a>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render Pro campaigns table
     */
    private static function render_pro_campaigns_table($campaigns) {
        ?>
        <div class="card">
            <h2><?php esc_html_e('Campaign Performance Analysis', 'create-a-newsletter-with-the-block-editor'); ?></h2>
            <p><?php esc_html_e('Detailed performance metrics for each newsletter campaign with real-time tracking from WP Mail SMTP Pro.', 'create-a-newsletter-with-the-block-editor'); ?></p>

            <?php if (empty($campaigns)): ?>
                <div class="notice notice-info inline">
                    <p><strong><?php esc_html_e('No campaigns found with tracking data.', 'create-a-newsletter-with-the-block-editor'); ?></strong></p>
                    <p><?php esc_html_e('Send a newsletter to see detailed analytics here.', 'create-a-newsletter-with-the-block-editor'); ?></p>
                </div>
            <?php else: ?>
                <div class="table-full-width">
                    <table class="wp-list-table widefat striped canwbe-campaigns-table pro-table">
                        <thead>
                        <tr>
                            <th><?php esc_html_e('Newsletter', 'create-a-newsletter-with-the-block-editor'); ?></th>
                            <th><?php esc_html_e('Sent', 'create-a-newsletter-with-the-block-editor'); ?></th>
                            <th><?php esc_html_e('Delivered', 'create-a-newsletter-with-the-block-editor'); ?></th>
                            <th><?php esc_html_e('Opens', 'create-a-newsletter-with-the-block-editor'); ?></th>
                            <th><?php esc_html_e('Clicks', 'create-a-newsletter-with-the-block-editor'); ?></th>
                            <th><?php esc_html_e('Bounces', 'create-a-newsletter-with-the-block-editor'); ?></th>
                            <th><?php esc_html_e('Performance', 'create-a-newsletter-with-the-block-editor'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($campaigns as $campaign): ?>
                            <tr>
                                <td class="title-cell">
                                    <strong>
                                        <a href="<?php echo esc_url(get_edit_post_link($campaign['newsletter_id'])); ?>">
                                            <?php echo esc_html($campaign['title']); ?>
                                        </a>
                                    </strong>
                                    <br>
                                    <small style="color: #666;">
                                        <?php echo esc_html(date_i18n('M j, Y g:i A', strtotime($campaign['date']))); ?>
                                    </small>
                                </td>
                                <td class="number-cell">
                                    <?php echo esc_html(number_format_i18n($campaign['emails_sent'])); ?>
                                </td>
                                <td class="number-cell">
                                    <strong style="color: #00a32a;">
                                        <?php echo esc_html(number_format_i18n($campaign['delivered'])); ?>
                                    </strong>
                                    <?php if ($campaign['emails_sent'] > 0): ?>
                                        <br><small>
                                            <?php echo esc_html(round(($campaign['delivered'] / $campaign['emails_sent']) * 100, 1)); ?>%
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td class="number-cell">
                                    <strong style="color: #2271b1;">
                                        <?php echo esc_html(number_format_i18n($campaign['unique_opens'] ?? 0)); ?>
                                    </strong>
                                    <?php if (($campaign['opens'] ?? 0) > ($campaign['unique_opens'] ?? 0)): ?>
                                        <span style="color: #666;">(<?php echo esc_html(number_format_i18n($campaign['opens'] ?? 0)); ?>)</span>
                                    <?php endif; ?>
                                    <br><small><?php echo esc_html($campaign['open_rate'] ?? 0); ?>%</small>
                                </td>
                                <td class="number-cell">
                                    <strong style="color: #d63638;">
                                        <?php echo esc_html(number_format_i18n($campaign['unique_clicks'] ?? 0)); ?>
                                    </strong>
                                    <?php if (($campaign['clicks'] ?? 0) > ($campaign['unique_clicks'] ?? 0)): ?>
                                        <span style="color: #666;">(<?php echo esc_html(number_format_i18n($campaign['clicks'] ?? 0)); ?>)</span>
                                    <?php endif; ?>
                                    <br><small><?php echo esc_html($campaign['click_rate'] ?? 0); ?>%</small>
                                </td>
                                <td class="number-cell">
                                    <?php if (($campaign['bounces'] ?? 0) > 0): ?>
                                        <span style="color: #d63638; font-weight: bold;">
                                            <?php echo esc_html(number_format_i18n($campaign['bounces'])); ?>
                                        </span>
                                        <br><small><?php echo esc_html($campaign['bounce_rate'] ?? 0); ?>%</small>
                                    <?php else: ?>
                                        <span style="color: #00a32a;">0</span>
                                    <?php endif; ?>
                                </td>
                                <td class="performance-cell">
                                    <?php self::render_performance_indicator($campaign); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render basic campaigns table
     */
    private static function render_basic_campaigns_table($campaigns) {
        ?>
        <div class="card">
            <h2><?php esc_html_e('Newsletter Campaigns', 'create-a-newsletter-with-the-block-editor'); ?></h2>
            <p><?php esc_html_e('Basic sending statistics from the batch system. Upgrade to WP Mail SMTP Pro for detailed analytics including opens, clicks, and bounces.', 'create-a-newsletter-with-the-block-editor'); ?></p>

            <?php if (empty($campaigns)): ?>
                <div class="notice notice-info inline">
                    <p><strong><?php esc_html_e('No newsletter campaigns found.', 'create-a-newsletter-with-the-block-editor'); ?></strong></p>
                    <p><?php esc_html_e('Create and send your first newsletter to see analytics here.', 'create-a-newsletter-with-the-block-editor'); ?></p>
                    <p>
                        <a href="<?php echo esc_url(admin_url('post-new.php?post_type=newsletter')); ?>" class="button button-primary">
                            <?php esc_html_e('Create Your First Newsletter', 'create-a-newsletter-with-the-block-editor'); ?>
                        </a>
                    </p>
                </div>
            <?php else: ?>
                <div class="table-full-width">
                    <table class="wp-list-table widefat striped canwbe-campaigns-table basic-table">
                        <thead>
                        <tr>
                            <th><?php esc_html_e('Newsletter Title', 'create-a-newsletter-with-the-block-editor'); ?></th>
                            <th><?php esc_html_e('Recipients', 'create-a-newsletter-with-the-block-editor'); ?></th>
                            <th><?php esc_html_e('Delivered', 'create-a-newsletter-with-the-block-editor'); ?></th>
                            <th><?php esc_html_e('Failed', 'create-a-newsletter-with-the-block-editor'); ?></th>
                            <th><?php esc_html_e('Status', 'create-a-newsletter-with-the-block-editor'); ?></th>
                            <th class="upgrade-column"><?php esc_html_e('Advanced Metrics', 'create-a-newsletter-with-the-block-editor'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($campaigns as $campaign): ?>
                            <tr>
                                <td class="title-cell">
                                    <strong>
                                        <a href="<?php echo esc_url(get_edit_post_link($campaign['post_id'])); ?>">
                                            <?php echo esc_html($campaign['title']); ?>
                                        </a>
                                    </strong>
                                    <br>
                                    <small style="color: #666;">
                                        <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($campaign['created_at']))); ?>
                                    </small>
                                </td>
                                <td class="number-cell"><?php echo esc_html(number_format_i18n($campaign['total_emails'])); ?></td>
                                <td class="number-cell">
                                    <span style="color: #00a32a; font-weight: bold;">
                                        <?php echo esc_html(number_format_i18n($campaign['sent_emails'])); ?>
                                    </span>
                                    <?php if ($campaign['total_emails'] > 0): ?>
                                        <br><small>
                                            <?php echo esc_html(round(($campaign['sent_emails'] / $campaign['total_emails']) * 100, 1)); ?>%
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td class="number-cell">
                                    <?php if ($campaign['failed_emails'] > 0): ?>
                                        <span style="color: #d63638; font-weight: bold;">
                                            <?php echo esc_html(number_format_i18n($campaign['failed_emails'])); ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: #666;">0</span>
                                    <?php endif; ?>
                                </td>
                                <td class="status-cell">
                                    <?php echo self::render_campaign_status($campaign['status']); ?>
                                </td>
                                <td class="upgrade-column">
                                    <?php self::render_upgrade_placeholder(); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render performance indicator for Pro campaigns
     */
    private static function render_performance_indicator($campaign) {
        $open_rate = $campaign['open_rate'] ?? 0;
        $click_rate = $campaign['click_rate'] ?? 0;
        $bounce_rate = $campaign['bounce_rate'] ?? 0;

        // Determine performance colors
        $open_color = $open_rate >= 25 ? '#00a32a' : ($open_rate >= 15 ? '#dba617' : '#d63638');
        $click_color = $click_rate >= 5 ? '#00a32a' : ($click_rate >= 2 ? '#dba617' : '#d63638');
        $bounce_color = $bounce_rate <= 2 ? '#00a32a' : ($bounce_rate <= 5 ? '#dba617' : '#d63638');
        ?>
        <div class="performance-indicators">
            <div class="performance-bars">
                <div class="performance-bar open-bar">
                    <div class="performance-segment"
                         style="width: <?php echo esc_attr(min($open_rate, 100)); ?>%; background: <?php echo esc_attr($open_color); ?>;"></div>
                </div>
                <div class="performance-bar click-bar">
                    <div class="performance-segment"
                         style="width: <?php echo esc_attr(min($click_rate * 5, 100)); ?>%; background: <?php echo esc_attr($click_color); ?>;"></div>
                </div>
            </div>
            <div class="performance-text">
                <small>
                    <span style="color: <?php echo esc_attr($open_color); ?>;">O: <?php echo esc_html($open_rate); ?>%</span> |
                    <span style="color: <?php echo esc_attr($click_color); ?>;">C: <?php echo esc_html($click_rate); ?>%</span>
                    <?php if ($bounce_rate > 0): ?>
                        | <span style="color: <?php echo esc_attr($bounce_color); ?>;">B: <?php echo esc_html($bounce_rate); ?>%</span>
                    <?php endif; ?>
                </small>
            </div>
        </div>
        <?php
    }

    /**
     * Render campaign status badge
     */
    private static function render_campaign_status($status) {
        $status_colors = array(
            'completed' => '#00a32a',
            'processing' => '#dba617',
            'queued' => '#2271b1',
            'cancelled' => '#d63638',
            'failed' => '#d63638'
        );

        $color = isset($status_colors[$status]) ? $status_colors[$status] : '#666';

        return '<span style="color: ' . esc_attr($color) . '; font-weight: bold;">' .
            esc_html(ucfirst($status)) . '</span>';
    }

    /**
     * Render upgrade placeholder for basic version
     */
    private static function render_upgrade_placeholder() {
        ?>
        <div class="upgrade-placeholder">
            <div class="upgrade-metrics">
                <div class="metric-placeholder">
                    <span class="metric-label"><?php esc_html_e('Opens:', 'create-a-newsletter-with-the-block-editor'); ?></span>
                    <span class="metric-value">?</span>
                </div>
                <div class="metric-placeholder">
                    <span class="metric-label"><?php esc_html_e('Clicks:', 'create-a-newsletter-with-the-block-editor'); ?></span>
                    <span class="metric-value">?</span>
                </div>
            </div>
            <a href="https://wpmailsmtp.com/?utm_source=plugin&utm_medium=campaign-row"
               class="upgrade-button" target="_blank">
                <?php esc_html_e('Upgrade', 'create-a-newsletter-with-the-block-editor'); ?>
            </a>
        </div>
        <?php
    }

    /**
     * Render Pro insights section
     */
    private static function render_pro_insights($overall_stats, $campaigns) {
        if (empty($campaigns)) {
            return;
        }

        // Calculate insights
        $campaign_count = count($campaigns);
        $total_opens = array_sum(array_column($campaigns, 'open_rate'));
        $total_clicks = array_sum(array_column($campaigns, 'click_rate'));
        $total_bounces = array_sum(array_column($campaigns, 'bounce_rate'));

        $avg_open_rate = $campaign_count > 0 ? round($total_opens / $campaign_count, 2) : 0;
        $avg_click_rate = $campaign_count > 0 ? round($total_clicks / $campaign_count, 2) : 0;
        $avg_bounce_rate = $campaign_count > 0 ? round($total_bounces / $campaign_count, 2) : 0;
        ?>
        <div class="card">
            <h2><?php esc_html_e('Performance Insights', 'create-a-newsletter-with-the-block-editor'); ?></h2>

            <div class="insights-grid">
                <div class="insight-item">
                    <div class="insight-icon">📊</div>
                    <h4><?php esc_html_e('Average Open Rate', 'create-a-newsletter-with-the-block-editor'); ?></h4>
                    <div class="insight-value <?php echo self::get_performance_class($avg_open_rate, 25, 20, 15); ?>">
                        <?php echo esc_html($avg_open_rate); ?>%
                    </div>
                    <p class="insight-benchmark">
                        <?php esc_html_e('Industry average: ~22%', 'create-a-newsletter-with-the-block-editor'); ?>
                    </p>
                    <div class="insight-trend">
                        <?php if ($avg_open_rate >= 22): ?>
                            <span style="color: #00a32a;">📈 <?php esc_html_e('Above average', 'create-a-newsletter-with-the-block-editor'); ?></span>
                        <?php else: ?>
                            <span style="color: #d63638;">📉 <?php esc_html_e('Below average', 'create-a-newsletter-with-the-block-editor'); ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="insight-item">
                    <div class="insight-icon">🖱️</div>
                    <h4><?php esc_html_e('Average Click Rate', 'create-a-newsletter-with-the-block-editor'); ?></h4>
                    <div class="insight-value <?php echo self::get_performance_class($avg_click_rate, 5, 3, 2); ?>">
                        <?php echo esc_html($avg_click_rate); ?>%
                    </div>
                    <p class="insight-benchmark">
                        <?php esc_html_e('Industry average: ~3%', 'create-a-newsletter-with-the-block-editor'); ?>
                    </p>
                    <div class="insight-trend">
                        <?php if ($avg_click_rate >= 3): ?>
                            <span style="color: #00a32a;">📈 <?php esc_html_e('Above average', 'create-a-newsletter-with-the-block-editor'); ?></span>
                        <?php else: ?>
                            <span style="color: #d63638;">📉 <?php esc_html_e('Below average', 'create-a-newsletter-with-the-block-editor'); ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="insight-item">
                    <div class="insight-icon">📬</div>
                    <h4><?php esc_html_e('Delivery Health', 'create-a-newsletter-with-the-block-editor'); ?></h4>
                    <div class="insight-value <?php echo self::get_performance_class($overall_stats['delivery_rate'], 98, 95, 90); ?>">
                        <?php echo esc_html($overall_stats['delivery_rate']); ?>%
                    </div>
                    <p class="insight-benchmark">
                        <?php esc_html_e('Target: >95%', 'create-a-newsletter-with-the-block-editor'); ?>
                    </p>
                    <div class="insight-trend">
                        <?php if ($overall_stats['delivery_rate'] >= 95): ?>
                            <span style="color: #00a32a;">✅ <?php esc_html_e('Excellent delivery', 'create-a-newsletter-with-the-block-editor'); ?></span>
                        <?php else: ?>
                            <span style="color: #d63638;">⚠️ <?php esc_html_e('Needs attention', 'create-a-newsletter-with-the-block-editor'); ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="insight-item">
                    <div class="insight-icon">⚡</div>
                    <h4><?php esc_html_e('Bounce Rate', 'create-a-newsletter-with-the-block-editor'); ?></h4>
                    <div class="insight-value <?php echo self::get_performance_class_reverse($avg_bounce_rate, 1, 2, 5); ?>">
                        <?php echo esc_html($avg_bounce_rate); ?>%
                    </div>
                    <p class="insight-benchmark">
                        <?php esc_html_e('Target: <2%', 'create-a-newsletter-with-the-block-editor'); ?>
                    </p>
                    <div class="insight-trend">
                        <?php if ($avg_bounce_rate <= 2): ?>
                            <span style="color: #00a32a;">✅ <?php esc_html_e('Healthy bounce rate', 'create-a-newsletter-with-the-block-editor'); ?></span>
                        <?php else: ?>
                            <span style="color: #d63638;">⚠️ <?php esc_html_e('High bounce rate', 'create-a-newsletter-with-the-block-editor'); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php self::render_recommendations($avg_open_rate, $avg_click_rate, $avg_bounce_rate, $overall_stats['delivery_rate']); ?>
        </div>
        <?php
    }

    /**
     * Render recommendations section
     */
    private static function render_recommendations($open_rate, $click_rate, $bounce_rate, $delivery_rate) {
        $has_recommendations = $open_rate < 20 || $click_rate < 2 || $bounce_rate > 3 || $delivery_rate < 95;

        if (!$has_recommendations) {
            return;
        }
        ?>
        <div class="recommendations-section">
            <h3><?php esc_html_e('Recommendations', 'create-a-newsletter-with-the-block-editor'); ?></h3>
            <div class="recommendations-grid">
                <?php if ($open_rate < 20): ?>
                    <div class="recommendation-item">
                        <div class="recommendation-icon">📧</div>
                        <div class="recommendation-content">
                            <h4><?php esc_html_e('Improve Open Rates', 'create-a-newsletter-with-the-block-editor'); ?></h4>
                            <p><?php esc_html_e('Try more compelling subject lines, test send times, and segment your audience.', 'create-a-newsletter-with-the-block-editor'); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($click_rate < 2): ?>
                    <div class="recommendation-item">
                        <div class="recommendation-icon">🎯</div>
                        <div class="recommendation-content">
                            <h4><?php esc_html_e('Boost Engagement', 'create-a-newsletter-with-the-block-editor'); ?></h4>
                            <p><?php esc_html_e('Add clear call-to-action buttons, include relevant links, and create compelling content.', 'create-a-newsletter-with-the-block-editor'); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($bounce_rate > 3): ?>
                    <div class="recommendation-item">
                        <div class="recommendation-icon">🧹</div>
                        <div class="recommendation-content">
                            <h4><?php esc_html_e('Clean Your List', 'create-a-newsletter-with-the-block-editor'); ?></h4>
                            <p><?php esc_html_e('Remove invalid email addresses and use double opt-in for new subscribers.', 'create-a-newsletter-with-the-block-editor'); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($delivery_rate < 95): ?>
                    <div class="recommendation-item">
                        <div class="recommendation-icon">🛠️</div>
                        <div class="recommendation-content">
                            <h4><?php esc_html_e('Fix Delivery Issues', 'create-a-newsletter-with-the-block-editor'); ?></h4>
                            <p><?php esc_html_e('Check SMTP settings, verify domain authentication, and monitor sender reputation.', 'create-a-newsletter-with-the-block-editor'); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Helper functions
     */

    /**
     * Get performance class for metrics (higher is better)
     */
    private static function get_performance_class($value, $excellent, $good, $average) {
        if ($value >= $excellent) return 'excellent';
        if ($value >= $good) return 'good';
        if ($value >= $average) return 'average';
        return 'poor';
    }

    /**
     * Get performance class for metrics (lower is better, like bounce rate)
     */
    private static function get_performance_class_reverse($value, $excellent, $good, $average) {
        if ($value <= $excellent) return 'excellent';
        if ($value <= $good) return 'good';
        if ($value <= $average) return 'average';
        return 'poor';
    }

    /**
     * Render Pro styles - load from helper class
     */
    private static function render_pro_styles() {
        if (class_exists('CANWBE_Analytics_Renderer_Helpers')) {
            CANWBE_Analytics_Renderer_Helpers::render_pro_styles();
        }
    }

    /**
     * Render basic styles - load from helper class
     */
    private static function render_basic_styles() {
        if (class_exists('CANWBE_Analytics_Renderer_Helpers')) {
            CANWBE_Analytics_Renderer_Helpers::render_basic_styles();
        }
    }
}
