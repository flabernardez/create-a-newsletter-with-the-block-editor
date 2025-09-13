<?php
/**
 * Pages Management
 *
 * Handles creation and management of newsletter-related pages
 *
 * @package Create_A_Newsletter_With_The_Block_Editor
 * @since 1.3
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Create necessary pages on plugin activation
 */
function canwbe_create_pages() {
    canwbe_create_unsubscribe_page();
    canwbe_create_subscriber_dashboard_page();
}

/**
 * Create unsubscribe confirmation page
 */
function canwbe_create_unsubscribe_page() {
    $unsubscribe_page = get_page_by_path('unsubscribe');

    if (!$unsubscribe_page) {
        $unsubscribe_page = array(
            'post_title'    => esc_html__('Unsubscribe', 'create-a-newsletter-with-the-block-editor'),
            'post_name'     => 'unsubscribe',
            'post_content'  => esc_html__('You have successfully unsubscribed from this newsletter.', 'create-a-newsletter-with-the-block-editor'),
            'post_status'   => 'publish',
            'post_type'     => 'page',
        );
        $unsubscribe_page_id = wp_insert_post($unsubscribe_page);
    } else {
        $unsubscribe_page_id = $unsubscribe_page->ID;
    }

    // Store the ID of the unsubscribe page
    update_option('canwbe_unsubscribe_page_id', $unsubscribe_page_id);
}

/**
 * Create subscriber dashboard page
 */
function canwbe_create_subscriber_dashboard_page() {
    $dashboard_page = get_page_by_path('subscriber-dashboard');

    if (!$dashboard_page) {
        // Content with blocks for the dashboard page
        $dashboard_content = '
            <!-- wp:paragraph -->
            <p>' . esc_html__('To unsubscribe from the newsletter, click the button below:', 'create-a-newsletter-with-the-block-editor') . '</p>
            <!-- /wp:paragraph -->

            <!-- wp:button -->
            <div class="wp-block-button">
                <a class="wp-block-button__link" href="{{unsubscribe_link}}">' . esc_html__('Unsubscribe', 'create-a-newsletter-with-the-block-editor') . '</a>
            </div>
            <!-- /wp:button -->
        ';

        $dashboard_page = array(
            'post_title'    => esc_html__('Subscriber Dashboard', 'create-a-newsletter-with-the-block-editor'),
            'post_name'     => 'subscriber-dashboard',
            'post_content'  => $dashboard_content,
            'post_status'   => 'publish',
            'post_type'     => 'page',
        );
        $dashboard_page_id = wp_insert_post($dashboard_page);
    } else {
        $dashboard_page_id = $dashboard_page->ID;
    }

    // Store the ID of the subscriber dashboard page
    update_option('canwbe_dashboard_page_id', $dashboard_page_id);
}

/**
 * Replace the placeholder with the actual unsubscribe link
 */
function canwbe_replace_unsubscribe_link($content) {
    if (is_page() && is_user_logged_in()) {
        $user_id = get_current_user_id();
        $unsubscribe_url = esc_url(home_url('/unsubscribe/?user_id=' . $user_id));
        $content = str_replace('{{unsubscribe_link}}', $unsubscribe_url, $content);
    }
    return $content;
}
add_filter('the_content', 'canwbe_replace_unsubscribe_link');

/**
 * Handle unsubscribe requests
 */
function canwbe_handle_unsubscribe_request() {
    if (is_page('unsubscribe') && isset($_GET['user_id'])) {
        $user_id = intval($_GET['user_id']);
        $current_user_id = get_current_user_id();

        if ($user_id === $current_user_id) {
            // Include necessary file for wp_delete_user
            require_once(ABSPATH . 'wp-admin/includes/user.php');

            $user = get_user_by('id', $user_id);

            if ($user) {
                // Delete the user
                wp_delete_user($user_id);

                // Redirect to unsubscribe confirmation page
                wp_redirect(home_url('/unsubscribe/'));
                exit;
            } else {
                echo '<p>' . esc_html__('Invalid request or user not found.', 'create-a-newsletter-with-the-block-editor') . '</p>';
            }
        } else {
            echo '<p>' . esc_html__('You do not have permission to perform this action.', 'create-a-newsletter-with-the-block-editor') . '</p>';
        }

        exit;
    }
}
add_action('template_redirect', 'canwbe_handle_unsubscribe_request');

/**
 * Handle email login and redirect to subscriber dashboard
 */
function canwbe_handle_email_login() {
    if (isset($_GET['canwbe_login']) && isset($_GET['user_id']) && isset($_GET['token'])) {
        $user_id = intval($_GET['user_id']);
        $token = sanitize_text_field($_GET['token']);

        // Retrieve stored token for the user
        $stored_token = get_user_meta($user_id, 'canwbe_unsubscribe_token', true);

        if ($token === $stored_token) {
            // Temporarily log in the user
            wp_set_current_user($user_id);
            wp_set_auth_cookie($user_id);

            // Redirect to subscriber dashboard
            wp_redirect(home_url('/subscriber-dashboard/'));
            exit;
        } else {
            wp_redirect(home_url('/invalid-token/')); // Redirect to error page or handle invalid token
            exit;
        }
    }
}
add_action('template_redirect', 'canwbe_handle_email_login');
