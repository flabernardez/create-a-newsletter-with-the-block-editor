<?php
/**
 * Plugin Name: Create a Newsletter with the Block Editor
 * Description: Creates a newsletter in Substack style using the content editor.
 * Version: 1.3
 * Author: Flavia Bernárdez Rodríguez
 * Author URI: https://flabernardez.com
 * License: GPL v3 or later
 * Text Domain: create-a-newsletter-with-the-block-editor
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('CANWBE_VERSION', '1.3');
define('CANWBE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CANWBE_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Include core functionality
require_once CANWBE_PLUGIN_PATH . 'includes/core-functions.php';
require_once CANWBE_PLUGIN_PATH . 'includes/custom-post-type.php';
require_once CANWBE_PLUGIN_PATH . 'includes/meta-fields.php';
require_once CANWBE_PLUGIN_PATH . 'includes/admin-columns.php';
require_once CANWBE_PLUGIN_PATH . 'includes/user-roles.php';
require_once CANWBE_PLUGIN_PATH . 'includes/email-sender.php';
require_once CANWBE_PLUGIN_PATH . 'includes/pages-manager.php';
require_once CANWBE_PLUGIN_PATH . 'includes/subscriber-management.php';
require_once CANWBE_PLUGIN_PATH . 'includes/email-settings.php';

// Include advanced features (optional)
$advanced_files = array(
    'includes/batch-email-sender.php',
    'includes/batch-email-config.php'
);

foreach ($advanced_files as $file) {
    $file_path = CANWBE_PLUGIN_PATH . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
    }
}

// Plugin activation hook
register_activation_hook(__FILE__, 'canwbe_activate_plugin');

function canwbe_activate_plugin() {
    // Create newsletter subscriber role
    canwbe_create_newsletter_subscriber_role();

    // Create necessary pages
    canwbe_create_pages();

    // Flush rewrite rules
    flush_rewrite_rules();
}

// Plugin deactivation hook
register_deactivation_hook(__FILE__, 'canwbe_deactivate_plugin');

function canwbe_deactivate_plugin() {
    // Clean up scheduled events
    wp_clear_scheduled_hook('canwbe_process_email_batch');
    wp_clear_scheduled_hook('canwbe_retry_failed_emails');
    wp_clear_scheduled_hook('canwbe_cleanup_old_batches');

    // Flush rewrite rules
    flush_rewrite_rules();
}

// Load text domain for translations
function canwbe_load_textdomain() {
    load_plugin_textdomain(
        'create-a-newsletter-with-the-block-editor',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
}
add_action('plugins_loaded', 'canwbe_load_textdomain');
