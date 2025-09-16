<?php
/**
 * Analytics Renderer Helpers
 *
 * Helper functions and styles for analytics rendering
 * Contains CSS styles and utility functions for the analytics pages
 *
 * @package Create_A_Newsletter_With_The_Block_Editor
 * @since 1.4.1
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class CANWBE_Analytics_Renderer_Helpers {

    /**
     * Render Pro styles
     */
    public static function render_pro_styles() {
        ?>
        <style>
            .pro-badge {
                background: linear-gradient(45deg, #f093fb 0%, #f5576c 100%);
                color: white;
                padding: 4px 8px;
                border-radius: 12px;
                font-size: 11px;
                font-weight: bold;
                margin-left: 10px;
            }

            .canwbe-analytics-overview.pro {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 20px;
                margin: 20px 0;
            }

            .canwbe-metric-card.pro-card {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border-radius: 12px;
                padding: 24px;
                text-align: center;
                box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }

            .canwbe-metric-card.pro-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            }

            .canwbe-metric-number {
                font-size: 2.5em;
                font-weight: bold;
                margin-bottom: 8px;
            }

            .canwbe-metric-label {
                font-size: 1.1em;
                margin-bottom: 4px;
                opacity: 0.95;
            }

            .canwbe-metric-period {
                font-size: 0.9em;
                opacity: 0.8;
            }

            .card {
                background: white;
                border: 1px solid #c3c4c7;
                border-left: 4px solid #72aee6;
                box-shadow: 0 2px 10px rgba(0,0,0,.08);
                padding: 2em;
                margin: 20px 0;
                border-radius: 6px;
            }

            .table-full-width {
                margin: 0 -2em;
                background: white;
            }

            .pro-table {
                margin: 0;
                width: 100%;
                border-collapse: collapse;
            }

            .pro-table th,
            .pro-table td {
                padding: 16px 20px;
                border-bottom: 1px solid #e1e1e1;
                vertical-align: top;
            }

            .pro-table th {
                background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
                font-weight: 600;
                text-align: left;
                color: #4a5568;
            }

            .number-cell {
                text-align: center;
                font-weight: bold;
                min-width: 90px;
            }

            .performance-cell {
                min-width: 140px;
            }

            .performance-indicators {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 8px;
            }

            .performance-bars {
                display: flex;
                flex-direction: column;
                gap: 4px;
                width: 100%;
            }

            .performance-bar {
                width: 100%;
                height: 4px;
                background: #f0f0f0;
                border-radius: 2px;
                overflow: hidden;
            }

            .performance-segment {
                height: 100%;
                transition: width 0.3s ease;
                border-radius: 2px;
            }

            .performance-text {
                text-align: center;
            }

            .insights-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 20px;
                margin-top: 20px;
            }

            .insight-item {
                background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                padding: 24px;
                text-align: center;
                position: relative;
                transition: transform 0.3s ease;
            }

            .insight-item:hover {
                transform: translateY(-3px);
                box-shadow: 0 8px 25px rgba(0,0,0,.1);
            }

            .insight-icon {
                font-size: 2em;
                margin-bottom: 12px;
            }

            .insight-item h4 {
                margin: 0 0 12px 0;
                color: #2d3748;
                font-size: 14px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                font-weight: 600;
            }

            .insight-value {
                font-size: 2.2em;
                font-weight: bold;
                margin: 12px 0;
            }

            .insight-value.excellent { color: #00a32a; }
            .insight-value.good { color: #10b981; }
            .insight-value.average { color: #dba617; }
            .insight-value.poor { color: #d63638; }

            .insight-benchmark {
                font-size: 12px;
                color: #718096;
                margin: 8px 0;
            }

            .insight-trend {
                margin-top: 12px;
                font-size: 12px;
                font-weight: 500;
            }

            .recommendations-section {
                margin-top: 30px;
                padding: 20px 0;
                border-top: 1px solid #e2e8f0;
            }

            .recommendations-section h3 {
                color: #2d3748;
                margin-bottom: 16px;
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .recommendations-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 16px;
            }

            .recommendation-item {
                display: flex;
                align-items: flex-start;
                gap: 12px;
                padding: 16px;
                background: #fffaf0;
                border: 1px solid #fed7aa;
                border-radius: 8px;
            }

            .recommendation-icon {
                font-size: 1.5em;
                flex-shrink: 0;
            }

            .recommendation-content h4 {
                margin: 0 0 8px 0;
                font-size: 14px;
                color: #92400e;
                font-weight: 600;
            }

            .recommendation-content p {
                margin: 0;
                font-size: 13px;
                color: #78350f;
                line-height: 1.4;
            }

            .title-cell {
                min-width: 250px;
            }

            .status-cell {
                text-align: center;
                min-width: 100px;
            }

            .notice.inline {
                margin: 0 0 20px 0;
                padding: 12px 16px;
                border-radius: 4px;
            }
        </style>
        <?php
    }

    /**
     * Render basic styles (for non-Pro version)
     */
    public static function render_basic_styles() {
        ?>
        <style>
            .upgrade-notice {
                background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
                border: 1px solid #bae6fd;
                border-left: 4px solid #0284c7;
                padding: 0;
                border-radius: 8px;
                overflow: hidden;
            }

            .upgrade-content {
                display: flex;
                align-items: flex-start;
                gap: 20px;
                padding: 20px;
            }

            .upgrade-icon {
                font-size: 3em;
                flex-shrink: 0;
            }

            .upgrade-text h3 {
                margin: 0 0 12px 0;
                color: #0c4a6e;
                font-size: 18px;
            }

            .upgrade-text p {
                margin: 0 0 16px 0;
                color: #164e63;
                line-height: 1.5;
            }

            .upgrade-features {
                list-style: none;
                margin: 0 0 20px 0;
                padding: 0;
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 8px;
            }

            .upgrade-features li {
                color: #0c4a6e;
                font-size: 14px;
                font-weight: 500;
            }

            .upgrade-buttons {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
            }

            .canwbe-analytics-overview.basic {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin: 20px 0;
            }

            .canwbe-metric-card.basic-card {
                background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
                border: 1px solid #e2e8f0;
                color: #334155;
                border-radius: 8px;
                padding: 20px;
                text-align: center;
                transition: transform 0.3s ease;
            }

            .canwbe-metric-card.basic-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,.1);
            }

            .canwbe-metric-card.upgrade-card {
                background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
                border: 1px solid #f59e0b;
                color: #92400e;
                position: relative;
                overflow: hidden;
            }

            .canwbe-metric-card.upgrade-card::before {
                content: '🔒';
                position: absolute;
                top: 10px;
                right: 10px;
                font-size: 1.2em;
                opacity: 0.7;
            }

            .upgrade-link {
                color: #0284c7 !important;
                text-decoration: none;
                font-weight: 600;
                font-size: 12px;
            }

            .upgrade-link:hover {
                text-decoration: underline;
            }

            .canwbe-metric-number {
                font-size: 2.5em;
                font-weight: bold;
                margin-bottom: 8px;
            }

            .canwbe-metric-label {
                font-size: 1.1em;
                margin-bottom: 4px;
                font-weight: 600;
            }

            .canwbe-metric-period {
                font-size: 0.9em;
                opacity: 0.8;
            }

            .card {
                background: white;
                border: 1px solid #c3c4c7;
                border-left: 4px solid #72aee6;
                box-shadow: 0 1px 3px rgba(0,0,0,.1);
                padding: 1.5em 2em;
                margin: 20px 0;
                border-radius: 4px;
            }

            .table-full-width {
                margin: 0 -2em;
                background: white;
            }

            .basic-table {
                margin: 0;
                width: 100%;
                border-collapse: collapse;
            }

            .basic-table th,
            .basic-table td {
                padding: 12px 16px;
                border-bottom: 1px solid #e2e8f0;
            }

            .basic-table th {
                background: #f8f9fa;
                font-weight: 600;
                text-align: left;
                color: #495057;
            }

            .upgrade-column {
                background: #fffbf0;
                border-left: 2px solid #f59e0b;
            }

            .upgrade-placeholder {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 8px;
                padding: 8px;
            }

            .upgrade-metrics {
                display: flex;
                flex-direction: column;
                gap: 4px;
                width: 100%;
            }

            .metric-placeholder {
                display: flex;
                justify-content: space-between;
                align-items: center;
                font-size: 11px;
                color: #6b7280;
            }

            .metric-label {
                font-weight: 500;
            }

            .metric-value {
                font-weight: bold;
                color: #f59e0b;
            }

            .upgrade-button {
                background: #f59e0b;
                color: white;
                border: none;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 10px;
                font-weight: 600;
                text-decoration: none;
                transition: background 0.3s ease;
            }

            .upgrade-button:hover {
                background: #d97706;
                color: white;
                text-decoration: none;
            }

            .number-cell {
                text-align: center;
                font-weight: bold;
                min-width: 80px;
            }

            .status-cell {
                text-align: center;
                min-width: 100px;
            }

            .title-cell {
                min-width: 250px;
            }

            .notice.inline {
                margin: 0 0 20px 0;
                padding: 12px 16px;
                border-radius: 4px;
            }

            /* Responsive Design */
            @media (max-width: 768px) {
                .upgrade-content {
                    flex-direction: column;
                    gap: 15px;
                }

                .upgrade-features {
                    grid-template-columns: 1fr;
                }

                .canwbe-analytics-overview {
                    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                    gap: 15px;
                }

                .insights-grid {
                    grid-template-columns: 1fr;
                }

                .recommendations-grid {
                    grid-template-columns: 1fr;
                }

                .table-full-width {
                    overflow-x: auto;
                }

                .canwbe-campaigns-table {
                    min-width: 600px;
                }

                .card {
                    padding: 1em;
                    margin: 15px 0;
                }

                .table-full-width {
                    margin: 0 -1em;
                }
            }

            @media (max-width: 480px) {
                .canwbe-metric-number {
                    font-size: 2em;
                }

                .canwbe-metric-label {
                    font-size: 1em;
                }

                .upgrade-icon {
                    font-size: 2.5em;
                }

                .pro-table th,
                .pro-table td,
                .basic-table th,
                .basic-table td {
                    padding: 8px 12px;
                }
            }
        </style>
        <?php
    }

    /**
     * Render performance chart (for future use)
     */
    public static function render_performance_chart($data) {
        if (empty($data)) {
            return;
        }
        ?>
        <div class="performance-chart">
            <canvas id="canwbe-performance-chart" width="400" height="200"></canvas>
        </div>
        <script>
            // Chart.js implementation would go here if needed
            console.log('Performance chart data:', <?php echo wp_json_encode($data); ?>);
        </script>
        <?php
    }

    /**
     * Format number for display
     */
    public static function format_number($number) {
        if ($number >= 1000000) {
            return round($number / 1000000, 1) . 'M';
        } elseif ($number >= 1000) {
            return round($number / 1000, 1) . 'K';
        }
        return number_format_i18n($number);
    }

    /**
     * Format percentage for display
     */
    public static function format_percentage($value, $decimals = 1) {
        return number_format($value, $decimals) . '%';
    }

    /**
     * Get color for performance value
     */
    public static function get_performance_color($value, $type = 'open_rate') {
        $colors = array(
            'open_rate' => array(
                'excellent' => '#00a32a',  // >= 25%
                'good' => '#10b981',       // >= 20%
                'average' => '#dba617',    // >= 15%
                'poor' => '#d63638'        // < 15%
            ),
            'click_rate' => array(
                'excellent' => '#00a32a',  // >= 5%
                'good' => '#10b981',       // >= 3%
                'average' => '#dba617',    // >= 2%
                'poor' => '#d63638'        // < 2%
            ),
            'bounce_rate' => array(
                'excellent' => '#00a32a',  // <= 1%
                'good' => '#10b981',       // <= 2%
                'average' => '#dba617',    // <= 5%
                'poor' => '#d63638'        // > 5%
            ),
            'delivery_rate' => array(
                'excellent' => '#00a32a',  // >= 98%
                'good' => '#10b981',       // >= 95%
                'average' => '#dba617',    // >= 90%
                'poor' => '#d63638'        // < 90%
            )
        );

        if (!isset($colors[$type])) {
            return '#666';
        }

        switch ($type) {
            case 'open_rate':
                if ($value >= 25) return $colors[$type]['excellent'];
                if ($value >= 20) return $colors[$type]['good'];
                if ($value >= 15) return $colors[$type]['average'];
                return $colors[$type]['poor'];

            case 'click_rate':
                if ($value >= 5) return $colors[$type]['excellent'];
                if ($value >= 3) return $colors[$type]['good'];
                if ($value >= 2) return $colors[$type]['average'];
                return $colors[$type]['poor'];

            case 'bounce_rate':
                if ($value <= 1) return $colors[$type]['excellent'];
                if ($value <= 2) return $colors[$type]['good'];
                if ($value <= 5) return $colors[$type]['average'];
                return $colors[$type]['poor'];

            case 'delivery_rate':
                if ($value >= 98) return $colors[$type]['excellent'];
                if ($value >= 95) return $colors[$type]['good'];
                if ($value >= 90) return $colors[$type]['average'];
                return $colors[$type]['poor'];

            default:
                return '#666';
        }
    }

    /**
     * Render loading spinner
     */
    public static function render_loading_spinner($message = '') {
        if (empty($message)) {
            $message = __('Loading analytics data...', 'create-a-newsletter-with-the-block-editor');
        }
        ?>
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p><?php echo esc_html($message); ?></p>
        </div>
        <style>
            .loading-spinner {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 40px;
                color: #666;
            }
            .spinner {
                width: 40px;
                height: 40px;
                border: 4px solid #f3f3f3;
                border-top: 4px solid #0073aa;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin-bottom: 16px;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        </style>
        <?php
    }

    /**
     * Render error message
     */
    public static function render_error_message($message, $details = '') {
        ?>
        <div class="notice notice-error">
            <p><strong><?php esc_html_e('Analytics Error:', 'create-a-newsletter-with-the-block-editor'); ?></strong> <?php echo esc_html($message); ?></p>
            <?php if (!empty($details)): ?>
                <p><small><?php echo esc_html($details); ?></small></p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render success message
     */
    public static function render_success_message($message) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
        <?php
    }

    /**
     * Render info message
     */
    public static function render_info_message($message, $dismissible = true) {
        $classes = 'notice notice-info';
        if ($dismissible) {
            $classes .= ' is-dismissible';
        }
        ?>
        <div class="<?php echo esc_attr($classes); ?>">
            <p><?php echo esc_html($message); ?></p>
        </div>
        <?php
    }

    /**
     * Get human readable time difference
     */
    public static function time_ago($datetime) {
        if (empty($datetime)) {
            return __('Never', 'create-a-newsletter-with-the-block-editor');
        }

        $time = time() - strtotime($datetime);

        if ($time < 60) {
            return __('Just now', 'create-a-newsletter-with-the-block-editor');
        }

        $time_units = array(
            31536000 => __('year', 'create-a-newsletter-with-the-block-editor'),
            2592000  => __('month', 'create-a-newsletter-with-the-block-editor'),
            604800   => __('week', 'create-a-newsletter-with-the-block-editor'),
            86400    => __('day', 'create-a-newsletter-with-the-block-editor'),
            3600     => __('hour', 'create-a-newsletter-with-the-block-editor'),
            60       => __('minute', 'create-a-newsletter-with-the-block-editor')
        );

        foreach ($time_units as $unit => $text) {
            if ($time < $unit) continue;
            $number_of_units = floor($time / $unit);

            if ($number_of_units == 1) {
                return sprintf(__('1 %s ago', 'create-a-newsletter-with-the-block-editor'), $text);
            } else {
                return sprintf(__('%d %ss ago', 'create-a-newsletter-with-the-block-editor'), $number_of_units, $text);
            }
        }

        return __('Just now', 'create-a-newsletter-with-the-block-editor');
    }

    /**
     * Sanitize and validate metric value
     */
    public static function sanitize_metric($value, $type = 'number') {
        switch ($type) {
            case 'percentage':
                $value = floatval($value);
                return max(0, min(100, $value));

            case 'number':
                return max(0, intval($value));

            case 'rate':
                $value = floatval($value);
                return max(0, $value);

            default:
                return sanitize_text_field($value);
        }
    }
}
