<?php
/**
 * DEBUG BATCH SYSTEM
 *
 * Advanced debugging tool for batch email system
 * Access via: yoursite.com/wp-admin/admin.php?page=canwbe-debug-batch
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add debug batch menu (hidden from main menu)
 */
function canwbe_add_debug_batch_page() {
    add_submenu_page(
        null, // No parent menu (hidden)
        __('Batch System Debug', 'create-a-newsletter-with-the-block-editor'),
        __('Batch Debug', 'create-a-newsletter-with-the-block-editor'),
        'manage_options',
        'canwbe-debug-batch',
        'canwbe_debug_batch_page'
    );
}
add_action('admin_menu', 'canwbe_add_debug_batch_page');

/**
 * Debug batch page
 */
function canwbe_debug_batch_page() {
    // Security check
    if (!current_user_can('manage_options')) {
        wp_die(__('Access denied', 'create-a-newsletter-with-the-block-editor'));
    }
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title><?php esc_html_e('Newsletter Batch System Debug', 'create-a-newsletter-with-the-block-editor'); ?></title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; background: #f1f1f1; }
            .container { background: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
            .error { background: #ffdddd; border-left: 4px solid #d63638; padding: 10px; margin: 10px 0; }
            .success { background: #ddffdd; border-left: 4px solid #00a32a; padding: 10px; margin: 10px 0; }
            .warning { background: #ffffdd; border-left: 4px solid #dba617; padding: 10px; margin: 10px 0; }
            pre { background: #f5f5f5; padding: 10px; overflow-x: auto; border-radius: 3px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
            th { background: #f9f9f9; }
            .button { background: #0073aa; color: white; padding: 8px 12px; text-decoration: none; border-radius: 3px; }
        </style>
    </head>
    <body>
    <h1><?php esc_html_e('Newsletter Batch System Debug', 'create-a-newsletter-with-the-block-editor'); ?></h1>

    <?php
    echo '<div class="container">';
    echo '<h2>' . esc_html__('1. System Status', 'create-a-newsletter-with-the-block-editor') . '</h2>';

    // Check WP Cron
    if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) {
        echo '<div class="error">❌ ' . esc_html__('WP Cron is DISABLED', 'create-a-newsletter-with-the-block-editor') . '</div>';
    } else {
        echo '<div class="success">✅ ' . esc_html__('WP Cron is enabled', 'create-a-newsletter-with-the-block-editor') . '</div>';
    }

    // Check if batch class exists
    if (class_exists('CANWBE_Batch_Email_Sender')) {
        echo '<div class="success">✅ ' . esc_html__('CANWBE_Batch_Email_Sender class loaded', 'create-a-newsletter-with-the-block-editor') . '</div>';
    } else {
        echo '<div class="error">❌ ' . esc_html__('CANWBE_Batch_Email_Sender class NOT loaded', 'create-a-newsletter-with-the-block-editor') . '</div>';
    }

    // Check for AJAX handler
    $ajax_handlers = array(
        'canwbe_cancel_batch',
        'canwbe_cancel_and_restart_batch',
        'canwbe_get_batch_status'
    );

    foreach ($ajax_handlers as $handler) {
        if (has_action('wp_ajax_' . $handler)) {
            echo '<div class="success">✅ ' . sprintf(esc_html__('AJAX handler registered: %s', 'create-a-newsletter-with-the-block-editor'), $handler) . '</div>';
        } else {
            echo '<div class="error">❌ ' . sprintf(esc_html__('AJAX handler MISSING: %s', 'create-a-newsletter-with-the-block-editor'), $handler) . '</div>';
        }
    }
    echo '</div>';

    // Get all batch data
    global $wpdb;
    echo '<div class="container">';
    echo '<h2>' . esc_html__('2. Current Batch Data', 'create-a-newsletter-with-the-block-editor') . '</h2>';

    $batch_options = $wpdb->get_results("
            SELECT option_name, CHAR_LENGTH(option_value) as size 
            FROM {$wpdb->options} 
            WHERE option_name LIKE 'canwbe_batch_%' 
            ORDER BY option_id DESC 
            LIMIT 10
        ");

    if (empty($batch_options)) {
        echo '<div class="warning">⚠️ ' . esc_html__('No batch data found', 'create-a-newsletter-with-the-block-editor') . '</div>';
    } else {
        echo '<table>';
        echo '<tr><th>' . esc_html__('Batch Option', 'create-a-newsletter-with-the-block-editor') . '</th><th>' . esc_html__('Data Size', 'create-a-newsletter-with-the-block-editor') . '</th><th>' . esc_html__('Status', 'create-a-newsletter-with-the-block-editor') . '</th><th>' . esc_html__('Actions', 'create-a-newsletter-with-the-block-editor') . '</th></tr>';

        foreach ($batch_options as $option) {
            $batch_data = get_option($option->option_name);
            $batch_id = str_replace('canwbe_batch_', '', $option->option_name);

            echo '<tr>';
            echo '<td><code>' . esc_html($batch_id) . '</code></td>';
            echo '<td>' . esc_html($option->size) . ' bytes</td>';

            if (is_array($batch_data) && isset($batch_data['status'])) {
                $status = $batch_data['status'];
                $sent = isset($batch_data['sent_emails']) ? $batch_data['sent_emails'] : 0;
                $total = isset($batch_data['total_emails']) ? $batch_data['total_emails'] : 0;
                $failed = isset($batch_data['failed_emails']) ? $batch_data['failed_emails'] : 0;

                echo '<td>' . esc_html($status) . '<br><small>' . $sent . '/' . $total . ' (' . $failed . ' failed)</small></td>';
                echo '<td>';
                echo '<a href="?page=canwbe-debug-batch&action=inspect&batch=' . urlencode($batch_id) . '" class="button">' . esc_html__('Inspect', 'create-a-newsletter-with-the-block-editor') . '</a> ';
                echo '<a href="?page=canwbe-debug-batch&action=delete&batch=' . urlencode($batch_id) . '" class="button" onclick="return confirm(\'' . esc_js__('Delete batch?', 'create-a-newsletter-with-the-block-editor') . '\')">' . esc_html__('Delete', 'create-a-newsletter-with-the-block-editor') . '</a>';
                echo '</td>';
            } else {
                echo '<td><span style="color:red;">' . esc_html__('Invalid data', 'create-a-newsletter-with-the-block-editor') . '</span></td>';
                echo '<td><a href="?page=canwbe-debug-batch&action=delete&batch=' . urlencode($batch_id) . '" class="button">' . esc_html__('Delete', 'create-a-newsletter-with-the-block-editor') . '</a></td>';
            }
            echo '</tr>';
        }
        echo '</table>';
    }
    echo '</div>';

    // Handle actions
    if (isset($_GET['action'])) {
        echo '<div class="container">';
        echo '<h2>' . esc_html__('3. Action Result', 'create-a-newsletter-with-the-block-editor') . '</h2>';

        $action = sanitize_text_field($_GET['action']);
        $batch_id = isset($_GET['batch']) ? sanitize_text_field($_GET['batch']) : '';

        if ($action === 'inspect' && $batch_id) {
            $batch_data = get_option('canwbe_batch_' . $batch_id);
            if ($batch_data) {
                echo '<h3>' . sprintf(esc_html__('Batch Data: %s', 'create-a-newsletter-with-the-block-editor'), esc_html($batch_id)) . '</h3>';
                echo '<pre>' . esc_html(print_r($batch_data, true)) . '</pre>';

                // Check for restart capability
                if (class_exists('CANWBE_Batch_Email_Sender')) {
                    echo '<h3>' . esc_html__('Manual Restart Test', 'create-a-newsletter-with-the-block-editor') . '</h3>';
                    echo '<p><a href="?page=canwbe-debug-batch&action=manual_restart&batch=' . urlencode($batch_id) . '" class="button">' . esc_html__('Try Manual Restart', 'create-a-newsletter-with-the-block-editor') . '</a></p>';
                }
            } else {
                echo '<div class="error">' . esc_html__('Batch data not found', 'create-a-newsletter-with-the-block-editor') . '</div>';
            }

        } elseif ($action === 'delete' && $batch_id) {
            if (delete_option('canwbe_batch_' . $batch_id)) {
                echo '<div class="success">' . esc_html__('Batch deleted successfully', 'create-a-newsletter-with-the-block-editor') . '</div>';
            } else {
                echo '<div class="error">' . esc_html__('Failed to delete batch', 'create-a-newsletter-with-the-block-editor') . '</div>';
            }

        } elseif ($action === 'manual_restart' && $batch_id) {
            if (class_exists('CANWBE_Batch_Email_Sender')) {
                try {
                    $new_batch_id = CANWBE_Batch_Email_Sender::cancel_and_restart_batch($batch_id);
                    if ($new_batch_id) {
                        echo '<div class="success">' . sprintf(esc_html__('Manual restart successful! New batch ID: %s', 'create-a-newsletter-with-the-block-editor'), esc_html($new_batch_id)) . '</div>';
                    } else {
                        echo '<div class="error">' . esc_html__('Manual restart failed', 'create-a-newsletter-with-the-block-editor') . '</div>';
                    }
                } catch (Exception $e) {
                    echo '<div class="error">' . sprintf(esc_html__('Exception during restart: %s', 'create-a-newsletter-with-the-block-editor'), esc_html($e->getMessage())) . '</div>';
                }
            }

        } elseif ($action === 'test_ajax') {
            echo '<h3>' . esc_html__('AJAX Test Results', 'create-a-newsletter-with-the-block-editor') . '</h3>';
            echo '<div id="ajax-test-results">' . esc_html__('Testing AJAX endpoints...', 'create-a-newsletter-with-the-block-editor') . '</div>';
            ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var results = document.getElementById('ajax-test-results');

                    // Test AJAX endpoint
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>');
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4) {
                            if (xhr.status === 200) {
                                results.innerHTML = '<div class="success">✅ <?php echo esc_js__('AJAX endpoint accessible', 'create-a-newsletter-with-the-block-editor'); ?></div><pre>' + xhr.responseText + '</pre>';
                            } else {
                                results.innerHTML = '<div class="error">❌ <?php echo esc_js__('AJAX endpoint error:', 'create-a-newsletter-with-the-block-editor'); ?> ' + xhr.status + '</div>';
                            }
                        }
                    };
                    xhr.send('action=canwbe_get_batch_status&batch_id=test&nonce=<?php echo wp_create_nonce('canwbe_admin'); ?>');
                });
            </script>
            <?php
        }

        echo '</div>';
    }

    // Check scheduled events
    echo '<div class="container">';
    echo '<h2>' . esc_html__('4. Scheduled Events', 'create-a-newsletter-with-the-block-editor') . '</h2>';

    $cron_array = _get_cron_array();
    $canwbe_events = array();

    foreach ($cron_array as $timestamp => $cron) {
        foreach ($cron as $hook => $events) {
            if (strpos($hook, 'canwbe_') === 0) {
                $canwbe_events[] = array(
                    'hook' => $hook,
                    'timestamp' => $timestamp,
                    'time' => date('Y-m-d H:i:s', $timestamp),
                    'events' => $events
                );
            }
        }
    }

    if (empty($canwbe_events)) {
        echo '<div class="warning">⚠️ ' . esc_html__('No scheduled CANWBE events found', 'create-a-newsletter-with-the-block-editor') . '</div>';
    } else {
        echo '<table>';
        echo '<tr><th>' . esc_html__('Hook', 'create-a-newsletter-with-the-block-editor') . '</th><th>' . esc_html__('Scheduled Time', 'create-a-newsletter-with-the-block-editor') . '</th><th>' . esc_html__('Arguments', 'create-a-newsletter-with-the-block-editor') . '</th></tr>';
        foreach ($canwbe_events as $event) {
            echo '<tr>';
            echo '<td>' . esc_html($event['hook']) . '</td>';
            echo '<td>' . esc_html($event['time']) . '</td>';
            echo '<td><pre>' . esc_html(print_r($event['events'], true)) . '</pre></td>';
            echo '</tr>';
        }
        echo '</table>';
    }
    echo '</div>';

    // Action buttons
    echo '<div class="container">';
    echo '<h2>' . esc_html__('5. Manual Actions', 'create-a-newsletter-with-the-block-editor') . '</h2>';
    echo '<p>';
    echo '<a href="?page=canwbe-debug-batch" class="button">' . esc_html__('Refresh', 'create-a-newsletter-with-the-block-editor') . '</a> ';
    echo '<a href="?page=canwbe-debug-batch&action=test_ajax" class="button">' . esc_html__('Test AJAX', 'create-a-newsletter-with-the-block-editor') . '</a> ';
    echo '<a href="' . admin_url('edit.php?post_type=newsletter&page=canwbe-email-batches') . '" class="button">' . esc_html__('Back to Batches', 'create-a-newsletter-with-the-block-editor') . '</a>';
    echo '</p>';
    echo '</div>';
    ?>

    </body>
    </html>
    <?php
}
