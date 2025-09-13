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

// 1. Create 'newsletter' Custom Post Type
function canwbe_create_cpt_newsletter() {
    $args = array(
        'public' => true,
        'label'  => esc_html__('Newsletters', 'create-a-newsletter-with-the-block-editor'),
        'show_in_rest' => true,
        'supports' => array('title', 'editor', 'excerpt', 'author', 'custom-fields'), // Adding 'custom-fields' here
        'rewrite' => array('slug' => 'newsletters'),
        'has_archive' => true,
    );
    register_post_type('newsletter', $args);
}
add_action('init', 'canwbe_create_cpt_newsletter');

// 2. Register meta fields for 'newsletter'
function canwbe_register_newsletter_meta() {
    // Field for intro message
    register_post_meta(
        'newsletter',
        'canwbe_intro_message',
        array(
            'type'              => 'string',
            'single'            => true,
            'sanitize_callback' => 'sanitize_textarea_field',
            'show_in_rest'      => true,
        )
    );

    // Field for unsubscribe message
    register_post_meta(
        'newsletter',
        'canwbe_unsubscribe_message',
        array(
            'type'              => 'string',
            'single'            => true,
            'sanitize_callback' => 'sanitize_text_field',
            'show_in_rest'      => true,
        )
    );

    // Field for recipient roles
    register_post_meta(
        'newsletter',
        'canwbe_recipient_roles',
        array(
            'type'              => 'array',
            'single'            => true,
            'default'           => array('newsletter_subscriber'),
            'sanitize_callback' => 'canwbe_sanitize_roles',
            'show_in_rest'      => array(
                'schema' => array(
                    'type'  => 'array',
                    'items' => array(
                        'type' => 'string',
                    ),
                ),
            ),
        )
    );
}
add_action('init', 'canwbe_register_newsletter_meta');

// Sanitize roles
function canwbe_sanitize_roles($roles) {
    global $wp_roles;
    $all_roles = $wp_roles->roles;
    $all_roles_keys = array_keys($all_roles);
    $roles = (array) $roles;
    $roles = array_intersect($roles, $all_roles_keys);
    return $roles;
}

// Enqueue editor assets and pass roles to JavaScript
function canwbe_enqueue_editor_assets() {
    wp_enqueue_script(
        'canwbe-editor-script',
        CANWBE_PLUGIN_URL . 'build/index.js',
        array(
            'wp-plugins',
            'wp-edit-post',
            'wp-element',
            'wp-components',
            'wp-data',
            'wp-i18n',
            'wp-core-data',
        ),
        CANWBE_VERSION,
        true
    );

    global $wp_roles;
    $all_roles = $wp_roles->roles;
    $roles = array();

    foreach ($all_roles as $role_key => $role_data) {
        $roles[] = array(
            'value' => $role_key,
            'label' => translate_user_role($role_data['name']),
        );
    }

    wp_localize_script(
        'canwbe-editor-script',
        'canwbeRoles',
        $roles
    );
}
add_action('enqueue_block_editor_assets', 'canwbe_enqueue_editor_assets');

// 3. Add a new column to the newsletter list in the admin area
function canwbe_add_excerpt_column($columns) {
    $columns['excerpt'] = esc_html__('Excerpt', 'create-a-newsletter-with-the-block-editor');
    return $columns;
}
add_filter('manage_newsletter_posts_columns', 'canwbe_add_excerpt_column');

// Display the excerpt in the new column
function canwbe_show_excerpt_column($column, $post_id) {
    if ($column === 'excerpt') {
        $post = get_post($post_id);
        echo esc_html(apply_filters('the_excerpt', $post->post_excerpt));
    }
}
add_action('manage_newsletter_posts_custom_column', 'canwbe_show_excerpt_column', 10, 2);

// Make the excerpt column sortable
function canwbe_excerpt_column_register_sortable($columns) {
    $columns['excerpt'] = 'excerpt';
    return $columns;
}
add_filter('manage_edit-newsletter_sortable_columns', 'canwbe_excerpt_column_register_sortable');

// Adjust the query to sort by excerpt
function canwbe_excerpt_column_orderby($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    if ($query->get('orderby') === 'excerpt') {
        $query->set('orderby', 'post_excerpt');
    }
}
add_action('pre_get_posts', 'canwbe_excerpt_column_orderby');

// Change the "Excerpt" label in newsletters
function canwbe_change_excerpt_label($translated_text, $text, $domain) {
    global $post_type;

    if ($post_type === 'newsletter' && $translated_text === 'Excerpt') {
        $translated_text = esc_html__('Number or Sender', 'create-a-newsletter-with-the-block-editor');
    }

    return $translated_text;
}
add_filter('gettext', 'canwbe_change_excerpt_label', 20, 3);

// 4. Create 'newsletter_subscriber' role
function canwbe_create_newsletter_subscriber_role() {
    $subscriber_capabilities = get_role('subscriber')->capabilities;
    add_role('newsletter_subscriber', esc_html__('Newsletter Subscriber', 'create-a-newsletter-with-the-block-editor'), $subscriber_capabilities);
}
register_activation_hook(__FILE__, 'canwbe_create_newsletter_subscriber_role');

// Allow email as username during registration
function canwbe_allow_email_as_username($result) {
    if (is_email($result['user_name'])) {
        unset($result['errors']->errors['user_name']);
    }
    return $result;
}
add_filter('wpmu_validate_user_signup', 'canwbe_allow_email_as_username');

// Replace CSS variables in the HTML content
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

// Function to generate a secure token for unsubscribe links
function canwbe_generate_unsubscribe_token($user_id) {
    $token = wp_generate_password(20, false);
    update_user_meta($user_id, 'canwbe_unsubscribe_token', $token);
    return $token;
}

// 5. Function to send the newsletter to subscribers
function canwbe_send_newsletter_to_subscribers($new_status, $old_status, $post) {
    // SAFETY CHECK: Don't send if migration is in progress
    if (get_transient('canwbe_migration_in_progress')) {
        error_log('CANWBE: Newsletter sending skipped - migration in progress for post ID: ' . $post->ID);
        return;
    }

    // SAFETY CHECK: Don't send migrated posts that are being created during migration
    if (get_post_meta($post->ID, '_migrated_post', true)) {
        error_log('CANWBE: Newsletter sending skipped - this is a migrated post: ' . $post->ID);
        // Remove the flag so future updates can send if needed
        delete_post_meta($post->ID, '_migrated_post');
        return;
    }

    if ($post->post_type === 'newsletter' && $new_status === 'publish' && $old_status !== 'publish') {
        // Get recipient roles from meta field
        $recipient_roles = get_post_meta($post->ID, 'canwbe_recipient_roles', true);

        if (empty($recipient_roles)) {
            $recipient_roles = array('newsletter_subscriber');
        }

        // Get users with selected roles
        $args = array(
            'role__in' => $recipient_roles,
            'fields' => array('ID', 'user_email'),
        );

        $subscribers = get_users($args);

        // Check if users were found
        if (empty($subscribers)) {
            error_log('No users found with selected roles: ' . implode(', ', $recipient_roles));
            return;
        }

        // Log the IDs of users found
        $user_ids = wp_list_pluck($subscribers, 'ID');
        error_log('Users found with roles ' . implode(', ', $recipient_roles) . ': ' . implode(', ', $user_ids));

        $subject = sanitize_text_field($post->post_title);
        $newsletter_excerpt = get_the_excerpt($post->ID);
        $newsletter_content = apply_filters('the_content', $post->post_content);
        $newsletter_link = get_permalink($post->ID);

        // Get intro message from meta field
        $intro_message = get_post_meta($post->ID, 'canwbe_intro_message', true);
        if (!empty($intro_message)) {
            $message = wp_kses_post($intro_message) . '<br><br>';

            // Add default link only if there's an intro message
            $message .= '<a href="' . esc_url($newsletter_link) . '">' . esc_html__('View on the web with graphics and images', 'create-a-newsletter-with-the-block-editor') . '</a><br><br>';
        } else {
            $message = '';
        }

        // Add newsletter content
        $message .= wp_kses_post($newsletter_content);

        // Replace CSS variables (if needed)
        $variables = array(
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
        $message = canwbe_replace_css_variables($message, $variables);

        $headers = array('Content-Type: text/html; charset=UTF-8');

        // Change the sender name
        add_filter('wp_mail_from_name', function($name) use ($newsletter_excerpt) {
            return sanitize_text_field($newsletter_excerpt);
        });

        // Get unsubscribe message from meta field
        $unsubscribe_message = get_post_meta($post->ID, 'canwbe_unsubscribe_message', true);
        if (empty($unsubscribe_message)) {
            $unsubscribe_message = esc_html__('Unsubscribe from this newsletter', 'create-a-newsletter-with-the-block-editor');
        } else {
            $unsubscribe_message = sanitize_text_field($unsubscribe_message);
        }

        // Send the email to each subscriber
        foreach ($subscribers as $subscriber) {
            $token = canwbe_generate_unsubscribe_token($subscriber->ID);
            $login_url = esc_url(home_url('/?canwbe_login=1&user_id=' . $subscriber->ID . '&token=' . $token));
            $final_message = $message . '<br><br><a href="' . $login_url . '">' . esc_html($unsubscribe_message) . '</a>';

            $mail_sent = wp_mail(sanitize_email($subscriber->user_email), $subject, $final_message, $headers);

            if (!$mail_sent) {
                error_log('Failed to send email to: ' . sanitize_email($subscriber->user_email));
            } else {
                error_log('Email sent to: ' . sanitize_email($subscriber->user_email));
            }
        }

        // Remove the filter to avoid affecting other emails
        remove_filter('wp_mail_from_name', function($name) use ($newsletter_excerpt) {
            return sanitize_text_field($newsletter_excerpt);
        });
    }
}
add_action('transition_post_status', 'canwbe_send_newsletter_to_subscribers', 10, 3);

// 6. Create unsubscribe and subscriber dashboard pages on plugin activation
function canwbe_create_pages() {
    // Create unsubscribe page
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

    // Create subscriber dashboard page
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
register_activation_hook(__FILE__, 'canwbe_create_pages');

// 7. Replace the placeholder with the actual unsubscribe link
function canwbe_replace_unsubscribe_link($content) {
    if (is_page() && is_user_logged_in()) {
        $user_id = get_current_user_id();
        $unsubscribe_url = esc_url(home_url('/unsubscribe/?user_id=' . $user_id));
        $content = str_replace('{{unsubscribe_link}}', $unsubscribe_url, $content);
    }
    return $content;
}
add_filter('the_content', 'canwbe_replace_unsubscribe_link');

// 8. Redirect 'newsletter_subscriber' users upon login
function canwbe_redirect_subscribers($redirect_to, $request, $user) {
    if (isset($user->roles) && in_array('newsletter_subscriber', $user->roles)) {
        $dashboard_page = get_page_by_path('subscriber-dashboard');

        if ($dashboard_page) {
            return get_permalink($dashboard_page->ID);
        }
    }
    return $redirect_to;
}
add_filter('login_redirect', 'canwbe_redirect_subscribers', 10, 3);

// 9. Hide admin bar for 'newsletter_subscriber'
function canwbe_hide_admin_bar() {
    if (!current_user_can('administrator')) {
        show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'canwbe_hide_admin_bar');

// 10. Restrict wp-admin access for 'newsletter_subscriber', allow super admins
function canwbe_restrict_wp_admin() {
    if (is_admin() && !defined('DOING_AJAX') && current_user_can('newsletter_subscriber') && !is_super_admin()) {
        wp_redirect(home_url());
        exit;
    }
}
add_action('init', 'canwbe_restrict_wp_admin');

// 11. Function to handle unsubscribe requests
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

// 12. Handle email login and redirect to subscriber dashboard
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

// 13. Set custom email address as "info@yourdomain.com" and name for the newsletter sender
function canwbe_set_mail_from($email) {
    // Get the domain of the current site
    $domain = parse_url(home_url(), PHP_URL_HOST);
    return 'info@' . $domain;
}

function canwbe_set_mail_from_name($name) {
    return 'Your Sender Name'; // Change this to the desired sender name
}

// Apply custom email and name only when sending newsletters
function canwbe_set_sender_for_newsletter($new_status, $old_status, $post) {
    if ($post->post_type === 'newsletter' && $new_status === 'publish' && $old_status !== 'publish') {
        add_filter('wp_mail_from', 'canwbe_set_mail_from');
        add_filter('wp_mail_from_name', 'canwbe_set_mail_from_name');
    }
}

// Hook into the newsletter sending function to apply filters
add_action('transition_post_status', 'canwbe_set_sender_for_newsletter', 5, 3);

// Remove filters after sending email to prevent affecting other emails
function canwbe_remove_sender_filters() {
    remove_filter('wp_mail_from', 'canwbe_set_mail_from');
    remove_filter('wp_mail_from_name', 'canwbe_set_mail_from_name');
}
add_action('wp_mail_succeeded', 'canwbe_remove_sender_filters');
add_action('wp_mail_failed', 'canwbe_remove_sender_filters');

// Load text domain for translations
function canwbe_load_textdomain() {
    load_plugin_textdomain(
        'create-a-newsletter-with-the-block-editor',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
}
add_action('plugins_loaded', 'canwbe_load_textdomain');
