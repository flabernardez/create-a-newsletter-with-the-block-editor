<?php
/**
 * Core Functions
 *
 * Core functionality and utility functions for the newsletter plugin
 *
 * @package Create_A_Newsletter_With_The_Block_Editor
 * @since 1.3
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Replace CSS variables in the HTML content
 */
function canwbe_replace_css_variables($html, $variables) {
    foreach ($variables as $var => $value) {
        $html = str_replace('var(' . sanitize_text_field($var) . ')', esc_html($value), $html);
    }

    preg_match_all('/<style(.*?)>(.*?)<\/style>/s', $html, $matches);
    if ($matches && count($matches[2]) > 0) {
        foreach ($matches[2] as $index => $styleContent) {
            $newStyleContent = $styleContent;
            foreach ($variables as $var => $value) {
                $newStyleContent = str_replace('var(' . sanitize_text_field($var) . ')', esc_html($value), $newStyleContent);
            }
            $html = str_replace($styleContent, $newStyleContent, $html);
        }
    }

    return $html;
}

/**
 * Function to generate a secure token for unsubscribe links
 */
function canwbe_generate_unsubscribe_token($user_id) {
    $token = wp_generate_password(20, false);
    update_user_meta($user_id, 'canwbe_unsubscribe_token', $token);
    return $token;
}

/**
 * Get default CSS variables for newsletters
 */
function canwbe_get_default_css_variables() {
    return array(
        '--wp--style--global--content-size' => '650px',
        '--wp--preset--color--contrast' => '#403e56',
        '--wp--preset--color--base' => '#ffffff',
        '--wp--preset--color--custom-amarillo' => '#fcb900',
        '--wp--preset--color--luminous-vivid-amber' => '#fcb900',
        '--wp--preset--color--secondary' => '#fff1f1',
        '--wp--preset--color--tertiary' => '#F6F6F6',
        '--wp--preset--font-family--ibm-plex-mono' => 'monospace',
        '--wp--preset--font-size--medium' => '20px',
        '--wp--preset--font-size--x-large' => '28px',
        '--wp--preset--spacing--30' => '30px',
        '--wp--preset--spacing--40' => '40px',
    );
}

/**
 * Check if user is newsletter subscriber
 */
function canwbe_is_newsletter_subscriber($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    $user = get_user_by('id', $user_id);
    return $user && in_array('newsletter_subscriber', $user->roles);
}

/**
 * Get newsletter subscribers count
 */
function canwbe_get_subscribers_count() {
    $subscribers = get_users(array(
        'role' => 'newsletter_subscriber',
        'fields' => 'ID',
        'count_total' => true
    ));

    return count($subscribers);
}

/**
 * Log plugin events
 */
function canwbe_log($message, $data = array()) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        $log_message = 'CANWBE: ' . $message;
        if (!empty($data)) {
            $log_message .= ' - Data: ' . print_r($data, true);
        }
        error_log($log_message);
    }
}
