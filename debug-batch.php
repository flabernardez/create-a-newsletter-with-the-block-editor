<?php
/**
 * DEBUG BATCH SYSTEM
 *
 * Upload this to your plugin root and access via browser to debug batch issues
 * URL: yoursite.com/wp-content/plugins/create-a-newsletter-with-the-block-editor/debug-batch.php
 */

// Load WordPress
if (!defined('ABSPATH')) {
    $wp_load_path = dirname(__FILE__) . '/../../../wp-load.php';
    if (file_exists($wp_load_path)) {
        require_once $wp_load_path;
    } else {
        die('Could not load WordPress');
    }
}

// Security check
if (!current_user_can('manage_options')) {
    die('Access denied');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Newsletter Batch System Debug</title>
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
<h1>Newsletter Batch System Debug</h1>

<?php
echo '<div class="container">';
echo '<h2>1. System Status</h2>';

// Check WP Cron
if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) {
    echo '<div class="error">❌ WP Cron is DISABLED</div>';
} else {
    echo '<div class="success">✅ WP Cron is enabled</div>';
}

// Check if batch class exists
if (class_exists('CANWBE_Batch_Email_Sender')) {
    echo '<div class="success">✅ CANWBE_Batch_Email_Sender class loaded</div>';
} else {
    echo '<div class="error">❌ CANWBE_Batch_Email_Sender class NOT loaded</div>';
}

// Check for AJAX handler
$ajax_handlers = array(
    'canwbe_cancel_batch',
    'canwbe_cancel_and_restart_batch',
    'canwbe_get_batch_status'
);

foreach ($ajax_handlers as $handler) {
    if (has_action('wp_ajax_' . $handler)) {
        echo '<div class="success">✅ AJAX handler registered: ' . $handler . '</div>';
    } else {
        echo '<div class="error">❌ AJAX handler MISSING: ' . $handler . '</div>';
    }
}
echo '</div>';

// Get all batch data
global $wpdb;
echo '<div class="container">';
echo '<h2>2. Current Batch Data</h2>';

$batch_options = $wpdb->get_results("
        SELECT option_name, CHAR_LENGTH(option_value) as size 
        FROM {$wpdb->options} 
        WHERE option_name LIKE 'canwbe_batch_%' 
        ORDER BY option_id DESC 
        LIMIT 10
    ");

if (empty($batch_options)) {
    echo '<div class="warning">⚠️ No batch data found</div>';
} else {
    echo '<table>';
    echo '<tr><th>Batch Option</th><th>Data Size</th><th>Status</th><th>Actions</th></tr>';

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
            echo '<a href="?action=inspect&batch=' . urlencode($batch_id) . '" class="button">Inspect</a> ';
            echo '<a href="?action=delete&batch=' . urlencode($batch_id) . '" class="button" onclick="return confirm(\'Delete batch?\')">Delete</a>';
            echo '</td>';
        } else {
            echo '<td><span style="color:red;">Invalid data</span></td>';
            echo '<td><a href="?action=delete&batch=' . urlencode($batch_id) . '" class="button">Delete</a></td>';
        }
        echo '</tr>';
    }
    echo '</table>';
}
echo '</div>';

// Handle actions
if (isset($_GET['action'])) {
    echo '<div class="container">';
    echo '<h2>3. Action Result</h2>';

    $action = $_GET['action'];
    $batch_id = isset($_GET['batch']) ? sanitize_text_field($_GET['batch']) : '';

    if ($action === 'inspect' && $batch_id) {
        $batch_data = get_option('canwbe_batch_' . $batch_id);
        if ($batch_data) {
            echo '<h3>Batch Data: ' . esc_html($batch_id) . '</h3>';
            echo '<pre>' . esc_html(print_r($batch_data, true)) . '</pre>';

            // Check for restart capability
            if (class_exists('CANWBE_Batch_Email_Sender')) {
                echo '<h3>Manual Restart Test</h3>';
                echo '<p><a href="?action=manual_restart&batch=' . urlencode($batch_id) . '" class="button">Try Manual Restart</a></p>';
            }
        } else {
            echo '<div class="error">Batch data not found</div>';
        }

    } elseif ($action === 'delete' && $batch_id) {
        if (delete_option('canwbe_batch_' . $batch_id)) {
            echo '<div class="success">Batch deleted successfully</div>';
        } else {
            echo '<div class="error">Failed to delete batch</div>';
        }

    } elseif ($action === 'manual_restart' && $batch_id) {
        if (class_exists('CANWBE_Batch_Email_Sender')) {
            try {
                $new_batch_id = CANWBE_Batch_Email_Sender::cancel_and_restart_batch($batch_id);
                if ($new_batch_id) {
                    echo '<div class="success">Manual restart successful! New batch ID: ' . esc_html($new_batch_id) . '</div>';
                } else {
                    echo '<div class="error">Manual restart failed</div>';
                }
            } catch (Exception $e) {
                echo '<div class="error">Exception during restart: ' . esc_html($e->getMessage()) . '</div>';
            }
        }

    } elseif ($action === 'test_ajax') {
        echo '<h3>AJAX Test Results</h3>';
        echo '<div id="ajax-test-results">Testing AJAX endpoints...</div>';
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
                            results.innerHTML = '<div class="success">✅ AJAX endpoint accessible</div><pre>' + xhr.responseText + '</pre>';
                        } else {
                            results.innerHTML = '<div class="error">❌ AJAX endpoint error: ' + xhr.status + '</div>';
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
echo '<h2>4. Scheduled Events</h2>';

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
    echo '<div class="warning">⚠️ No scheduled CANWBE events found</div>';
} else {
    echo '<table>';
    echo '<tr><th>Hook</th><th>Scheduled Time</th><th>Arguments</th></tr>';
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
echo '<h2>5. Manual Actions</h2>';
echo '<p>';
echo '<a href="?" class="button">Refresh</a> ';
echo '<a href="?action=test_ajax" class="button">Test AJAX</a> ';
echo '<a href="' . admin_url('edit.php?post_type=newsletter&page=canwbe-email-batches') . '" class="button">Back to Batches</a>';
echo '</p>';
echo '</div>';
?>

</body>
</html>
