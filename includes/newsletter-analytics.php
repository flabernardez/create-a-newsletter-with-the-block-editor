<?php
/**
 * Newsletter Analytics
 *
 * Handles newsletter analytics using batch system logs and WP Mail SMTP
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
        add_action('admin_menu', array(__CLASS__, 'add_analytics_menu'), 20);
        add_action('wp_ajax_canwbe_refresh_analytics', array(__CLASS__, 'ajax_refresh_analytics'));
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
     * Get WP Mail SMTP table info
     */
    public static function get_wp_mail_smtp_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wpmailsmtp_emails_log';

        // Verificar si la tabla existe
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") !== $table_name) {
            return false;
        }

        // Obtener estructura de la tabla
        $columns = $wpdb->get_results("DESCRIBE {$table_name}");
        $column_names = array();

        foreach ($columns as $column) {
            $column_names[] = $column->Field;
        }

        return array(
            'table_name' => $table_name,
            'columns' => $column_names
        );
    }

    /**
     * Analytics admin page
     */
    public static function analytics_page() {
        $wp_mail_table = self::get_wp_mail_smtp_table();
        $analytics_data = self::get_analytics_data();
        $newsletter_campaigns = self::get_newsletter_campaigns();

        ?>
        <div class="wrap">
            <h1>
                <?php esc_html_e('Newsletter Analytics', 'create-a-newsletter-with-the-block-editor'); ?>
                <button type="button" class="page-title-action" onclick="location.reload()">
                    <?php esc_html_e('Refresh Data', 'create-a-newsletter-with-the-block-editor'); ?>
                </button>
            </h1>

            <!-- System Status -->
            <div class="notice notice-success">
                <p><strong>System Status:</strong></p>
                <ul>
                    <li>WP Mail SMTP: <?php echo function_exists('wp_mail_smtp') ? '✅ Active' : '❌ Not found'; ?></li>
                    <li>Logs Table: <?php echo $wp_mail_table ? '✅ Found (' . esc_html($wp_mail_table['table_name']) . ')' : '❌ Not found'; ?></li>
                    <li>Batch System: <?php echo class_exists('CANWBE_Batch_Email_Sender') ? '✅ Active' : '❌ Not available'; ?></li>
                </ul>
            </div>

            <!-- Overview Cards -->
            <div class="canwbe-analytics-overview">
                <div class="canwbe-metric-card">
                    <div class="canwbe-metric-number"><?php echo esc_html(number_format_i18n($analytics_data['total_newsletters'])); ?></div>
                    <div class="canwbe-metric-label"><?php esc_html_e('Newsletters Sent', 'create-a-newsletter-with-the-block-editor'); ?></div>
                    <div class="canwbe-metric-period"><?php esc_html_e('Total campaigns', 'create-a-newsletter-with-the-block-editor'); ?></div>
                </div>

                <div class="canwbe-metric-card">
                    <div class="canwbe-metric-number"><?php echo esc_html(number_format_i18n($analytics_data['total_emails_sent'])); ?></div>
                    <div class="canwbe-metric-label"><?php esc_html_e('Emails Delivered', 'create-a-newsletter-with-the-block-editor'); ?></div>
                    <div class="canwbe-metric-period"><?php esc_html_e('From batch system', 'create-a-newsletter-with-the-block-editor'); ?></div>
                </div>

                <div class="canwbe-metric-card">
                    <div class="canwbe-metric-number"><?php echo esc_html(number_format_i18n($analytics_data['total_subscribers'])); ?></div>
                    <div class="canwbe-metric-label"><?php esc_html_e('Active Subscribers', 'create-a-newsletter-with-the-block-editor'); ?></div>
                    <div class="canwbe-metric-period"><?php esc_html_e('Current', 'create-a-newsletter-with-the-block-editor'); ?></div>
                </div>

                <div class="canwbe-metric-card">
                    <div class="canwbe-metric-number"><?php echo esc_html(number_format_i18n($analytics_data['smtp_logs_count'])); ?></div>
                    <div class="canwbe-metric-label"><?php esc_html_e('SMTP Logs', 'create-a-newsletter-with-the-block-editor'); ?></div>
                    <div class="canwbe-metric-period"><?php esc_html_e('Total emails', 'create-a-newsletter-with-the-block-editor'); ?></div>
                </div>
            </div>

            <!-- Newsletter Campaigns -->
            <div class="card" style="margin-top: 30px;">
                <h2><?php esc_html_e('Newsletter Campaigns', 'create-a-newsletter-with-the-block-editor'); ?></h2>
                <p><?php esc_html_e('Newsletters sent through the batch system', 'create-a-newsletter-with-the-block-editor'); ?></p>

                <?php if (empty($newsletter_campaigns)): ?>
                    <div class="notice notice-info inline">
                        <p><strong><?php esc_html_e('No newsletter campaigns found.', 'create-a-newsletter-with-the-block-editor'); ?></strong></p>
                        <p><?php esc_html_e('This could mean:', 'create-a-newsletter-with-the-block-editor'); ?></p>
                        <ul>
                            <li><?php esc_html_e('No newsletters have been sent through the batch system yet', 'create-a-newsletter-with-the-block-editor'); ?></li>
                            <li><?php esc_html_e('Batch data has been cleaned up (older than 30 days)', 'create-a-newsletter-with-the-block-editor'); ?></li>
                        </ul>
                        <p>
                            <a href="<?php echo admin_url('post-new.php?post_type=newsletter'); ?>" class="button button-primary">
                                <?php esc_html_e('Create Your First Newsletter', 'create-a-newsletter-with-the-block-editor'); ?>
                            </a>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="table-full-width">
                        <table class="wp-list-table widefat striped canwbe-campaigns-table">
                            <thead>
                            <tr>
                                <th><?php esc_html_e('Newsletter Title', 'create-a-newsletter-with-the-block-editor'); ?></th>
                                <th><?php esc_html_e('Recipients', 'create-a-newsletter-with-the-block-editor'); ?></th>
                                <th><?php esc_html_e('Delivered', 'create-a-newsletter-with-the-block-editor'); ?></th>
                                <th><?php esc_html_e('Opens', 'create-a-newsletter-with-the-block-editor'); ?></th>
                                <th><?php esc_html_e('Open Rate', 'create-a-newsletter-with-the-block-editor'); ?></th>
                                <th><?php esc_html_e('Failed', 'create-a-newsletter-with-the-block-editor'); ?></th>
                                <th><?php esc_html_e('Status', 'create-a-newsletter-with-the-block-editor'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($newsletter_campaigns as $campaign): ?>
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
                                        <?php if ($campaign['batch_id']): ?>
                                            <br>
                                            <small>
                                                <code><?php echo esc_html($campaign['batch_id']); ?></code>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="number-cell"><?php echo esc_html(number_format_i18n($campaign['total_emails'])); ?></td>
                                    <td class="number-cell">
                                        <span style="color: #00a32a; font-weight: bold;">
                                            <?php echo esc_html(number_format_i18n($campaign['sent_emails'])); ?>
                                        </span>
                                    </td>
                                    <td class="number-cell">
                                        <?php if ($campaign['opens'] > 0): ?>
                                            <span style="color: #2271b1; font-weight: bold;">
                                                <?php echo esc_html(number_format_i18n($campaign['opens'])); ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="color: #666;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="number-cell">
                                        <?php
                                        $open_rate = $campaign['sent_emails'] > 0 ? round(($campaign['opens'] / $campaign['sent_emails']) * 100, 1) : 0;
                                        if ($open_rate > 0):
                                            $rate_color = $open_rate >= 25 ? '#00a32a' : ($open_rate >= 15 ? '#dba617' : '#d63638');
                                            ?>
                                            <span style="color: <?php echo esc_attr($rate_color); ?>; font-weight: bold;">
                                                <?php echo esc_html($open_rate . '%'); ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="color: #666;">—</span>
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
                                        <?php
                                        $status_colors = array(
                                            'completed' => '#00a32a',
                                            'processing' => '#dba617',
                                            'queued' => '#2271b1',
                                            'cancelled' => '#d63638',
                                            'failed' => '#d63638'
                                        );
                                        $color = isset($status_colors[$campaign['status']]) ? $status_colors[$campaign['status']] : '#666';
                                        ?>
                                        <span style="color: <?php echo esc_attr($color); ?>; font-weight: bold;">
                                            <?php echo esc_html(ucfirst($campaign['status'])); ?>
                                        </span>
                                        <?php if ($campaign['status'] === 'completed' && $campaign['failed_emails'] === 0): ?>
                                            <br><small style="color: #00a32a;">✓ Perfect delivery</small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- WP Mail SMTP Integration -->
            <?php if ($wp_mail_table): ?>
                <div class="card" style="margin-top: 20px;">
                    <h3><?php esc_html_e('WP Mail SMTP Integration', 'create-a-newsletter-with-the-block-editor'); ?></h3>

                    <?php
                    // Obtener algunos datos de ejemplo de la tabla de WP Mail SMTP
                    global $wpdb;
                    $sample_emails = $wpdb->get_results("
                    SELECT subject, status, date_sent 
                    FROM {$wp_mail_table['table_name']} 
                    ORDER BY date_sent DESC 
                    LIMIT 5
                ");
                    ?>

                    <p><strong>Recent emails in WP Mail SMTP logs:</strong></p>
                    <?php if ($sample_emails): ?>
                        <table class="form-table">
                            <?php foreach ($sample_emails as $email): ?>
                                <tr>
                                    <td style="width: 50%;"><?php echo esc_html($email->subject); ?></td>
                                    <td style="width: 20%;"><?php echo esc_html($email->status); ?></td>
                                    <td style="width: 30%;"><?php echo esc_html(date_i18n('Y-m-d H:i', strtotime($email->date_sent))); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php else: ?>
                        <p><?php esc_html_e('No emails found in SMTP logs.', 'create-a-newsletter-with-the-block-editor'); ?></p>
                    <?php endif; ?>

                    <p>
                        <a href="<?php echo admin_url('admin.php?page=wp-mail-smtp-logs'); ?>" class="button">
                            <?php esc_html_e('View Full SMTP Logs', 'create-a-newsletter-with-the-block-editor'); ?>
                        </a>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Debug Information -->
            <div class="card" style="margin-top: 20px;">
                <h3><?php esc_html_e('Debug Information', 'create-a-newsletter-with-the-block-editor'); ?></h3>

                <table class="form-table">
                    <tr>
                        <th>WP Mail SMTP Table:</th>
                        <td>
                            <?php if ($wp_mail_table): ?>
                                <code><?php echo esc_html($wp_mail_table['table_name']); ?></code>
                                <br>Columns: <?php echo esc_html(implode(', ', $wp_mail_table['columns'])); ?>
                            <?php else: ?>
                                Not found
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Published Newsletters:</th>
                        <td><?php echo esc_html($analytics_data['published_newsletters']); ?></td>
                    </tr>
                    <tr>
                        <th>Batch Options in Database:</th>
                        <td><?php echo esc_html($analytics_data['batch_options_count']); ?></td>
                    </tr>
                </table>
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
                margin: 20px 0;
            }

            .notice.inline {
                margin: 0 0 20px 0;
                padding: 10px 15px;
            }

            .table-full-width {
                margin: 0 -2em; /* Extend beyond card padding */
                background: white;
            }

            .canwbe-campaigns-table {
                margin: 0;
                width: 100%;
                table-layout: auto;
                border-left: none;
                border-right: none;
                border-collapse: collapse;
            }

            .canwbe-campaigns-table th,
            .canwbe-campaigns-table td {
                padding: 15px 20px;
                border-bottom: 1px solid #e1e1e1;
            }

            .canwbe-campaigns-table th {
                background: #f9f9f9;
                font-weight: 600;
                text-align: left;
            }

            .title-cell {
                min-width: 300px;
                word-wrap: break-word;
            }

            .number-cell {
                text-align: center;
                font-weight: bold;
                white-space: nowrap;
                min-width: 80px;
            }

            .status-cell {
                text-align: center;
                min-width: 120px;
            }
        </style>
        <?php
    }

    /**
     * Get analytics data from our own batch system
     */
    public static function get_analytics_data() {
        global $wpdb;

        $data = array(
            'total_newsletters' => 0,
            'total_emails_sent' => 0,
            'total_subscribers' => 0,
            'smtp_logs_count' => 0,
            'published_newsletters' => 0,
            'batch_options_count' => 0
        );

        // Obtener newsletters publicados
        $newsletters = get_posts(array(
            'post_type' => 'newsletter',
            'post_status' => 'publish',
            'numberposts' => -1,
            'fields' => 'ids'
        ));
        $data['published_newsletters'] = count($newsletters);

        // Obtener suscriptores activos
        $subscribers = get_users(array(
            'role' => 'newsletter_subscriber',
            'fields' => 'ID'
        ));
        $data['total_subscribers'] = count($subscribers);

        // Obtener datos de batches
        $batch_options = $wpdb->get_results("
            SELECT option_name, option_value 
            FROM {$wpdb->options} 
            WHERE option_name LIKE 'canwbe_batch_%'
        ");

        $data['batch_options_count'] = count($batch_options);

        foreach ($batch_options as $option) {
            $batch_data = maybe_unserialize($option->option_value);
            if (is_array($batch_data)) {
                $data['total_newsletters']++;
                $data['total_emails_sent'] += (int) ($batch_data['sent_emails'] ?? 0);
            }
        }

        // Obtener datos de WP Mail SMTP si está disponible
        $wp_mail_table = self::get_wp_mail_smtp_table();
        if ($wp_mail_table) {
            $smtp_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wp_mail_table['table_name']}");
            $data['smtp_logs_count'] = (int) $smtp_count;
        }

        return $data;
    }

    /**
     * Get newsletter campaigns from batch data with SMTP opens
     */
    public static function get_newsletter_campaigns() {
        global $wpdb;

        $campaigns = array();
        $wp_mail_table = self::get_wp_mail_smtp_table();

        // Obtener todos los batches
        $batch_options = $wpdb->get_results("
            SELECT option_name, option_value 
            FROM {$wpdb->options} 
            WHERE option_name LIKE 'canwbe_batch_%'
            ORDER BY option_id DESC
            LIMIT 20
        ");

        foreach ($batch_options as $option) {
            $batch_data = maybe_unserialize($option->option_value);

            if (!is_array($batch_data) || !isset($batch_data['post_id'])) {
                continue;
            }

            $post = get_post($batch_data['post_id']);
            if (!$post) {
                continue;
            }

            $batch_id = str_replace('canwbe_batch_', '', $option->option_name);

            // Obtener aperturas de WP Mail SMTP si está disponible
            $opens = 0;
            if ($wp_mail_table && $batch_data['status'] === 'completed') {
                try {
                    // Buscar emails en los logs de SMTP que coincidan con el título del newsletter
                    $subject_like = '%' . $wpdb->esc_like($post->post_title) . '%';

                    // Verificar si la tabla tiene columna de aperturas
                    $columns = $wp_mail_table['columns'];
                    $has_opens = false;
                    $opens_column = '';

                    if (in_array('opened', $columns)) {
                        $has_opens = true;
                        $opens_column = 'opened';
                    } elseif (in_array('date_opened', $columns)) {
                        $has_opens = true;
                        $opens_column = 'date_opened';
                    } elseif (in_array('is_opened', $columns)) {
                        $has_opens = true;
                        $opens_column = 'is_opened';
                    }

                    if ($has_opens) {
                        // Buscar aperturas por asunto del newsletter
                        $opens_query = $wpdb->prepare("
                            SELECT COUNT(*) 
                            FROM {$wp_mail_table['table_name']} 
                            WHERE subject LIKE %s 
                            AND {$opens_column} IS NOT NULL 
                            AND {$opens_column} != ''
                            AND {$opens_column} != '0000-00-00 00:00:00'
                        ", $subject_like);

                        $opens = (int) $wpdb->get_var($opens_query);
                    }
                } catch (Exception $e) {
                    error_log('CANWBE Analytics: Error getting opens for ' . $post->post_title . ' - ' . $e->getMessage());
                }
            }

            $campaigns[] = array(
                'post_id' => $post->ID,
                'title' => $post->post_title,
                'batch_id' => $batch_id,
                'status' => $batch_data['status'] ?? 'unknown',
                'total_emails' => (int) ($batch_data['total_emails'] ?? 0),
                'sent_emails' => (int) ($batch_data['sent_emails'] ?? 0),
                'failed_emails' => (int) ($batch_data['failed_emails'] ?? 0),
                'opens' => $opens,
                'created_at' => $batch_data['created_at'] ?? 'Unknown'
            );
        }

        return $campaigns;
    }

    /**
     * AJAX handler for refreshing analytics
     */
    public static function ajax_refresh_analytics() {
        check_ajax_referer('canwbe_analytics', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        wp_send_json_success(array(
            'message' => __('Analytics data refreshed successfully', 'create-a-newsletter-with-the-block-editor')
        ));
    }
}

// Initialize analytics system
CANWBE_Newsletter_Analytics::init();
