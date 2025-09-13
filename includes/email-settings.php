<?php
/**
 * Email Settings Management
 *
 * Handles email sender configuration and settings
 *
 * @package Create_A_Newsletter_With_The_Block_Editor
 * @since 1.3
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Set custom email address as "info@yourdomain.com" and name for the newsletter sender
 */
function canwbe_set_mail_from($email) {
    // Get the domain of the current site
    $domain = parse_url(home_url(), PHP_URL_HOST);
    return 'info@' . $domain;
}

function canwbe_set_mail_from_name($name) {
    return 'Your Sender Name'; // Change this to the desired sender name
}

/**
 * Apply custom email and name only when sending newsletters
 */
function canwbe_set_sender_for_newsletter($new_status, $old_status, $post) {
    if ($post->post_type === 'newsletter' && $new_status === 'publish' && $old_status !== 'publish') {
        add_filter('wp_mail_from', 'canwbe_set_mail_from');
        add_filter('wp_mail_from_name', 'canwbe_set_mail_from_name');
    }
}

// Hook into the newsletter sending function to apply filters
add_action('transition_post_status', 'canwbe_set_sender_for_newsletter', 5, 3);

/**
 * Remove filters after sending email to prevent affecting other emails
 */
function canwbe_remove_sender_filters() {
    remove_filter('wp_mail_from', 'canwbe_set_mail_from');
    remove_filter('wp_mail_from_name', 'canwbe_set_mail_from_name');
}
add_action('wp_mail_succeeded', 'canwbe_remove_sender_filters');
add_action('wp_mail_failed', 'canwbe_remove_sender_filters');
