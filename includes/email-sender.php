<?php
/**
 * Email Sender Management
 *
 * Handles newsletter email sending with batch system integration
 *
 * @package Create_A_Newsletter_With_The_Block_Editor
 * @since 1.3
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Main function to send newsletters to subscribers
 */
function canwbe_send_newsletter_to_subscribers($new_status, $old_status, $post) {
    // Only send when transitioning to publish
    if ($post->post_type === 'newsletter' && $new_status === 'publish' && $old_status !== 'publish') {
        canwbe_process_newsletter_sending($post);
    }
}
add_action('transition_post_status', 'canwbe_send_newsletter_to_subscribers', 10, 3);

/**
 * Process newsletter sending
 */
function canwbe_process_newsletter_sending($post) {
    // Get recipient roles from meta field
    $recipient_roles = get_post_meta($post->ID, 'canwbe_recipient_roles', true);
    if (empty($recipient_roles)) {
        $recipient_roles = array('newsletter_subscriber');
    }

    // Get subscribers
    $subscribers = canwbe_get_users_by_roles($recipient_roles);

    if (empty($subscribers)) {
        canwbe_log('No subscribers found', array(
            'post_id' => $post->ID,
            'roles' => $recipient_roles
        ));
        return;
    }

    canwbe_log('Found subscribers', array(
        'post_id' => $post->ID,
        'subscriber_count' => count($subscribers),
        'roles' => $recipient_roles
    ));

    // Prepare email content
    $email_data = canwbe_prepare_email_content($post);

    // Try batch sending first, fallback to direct sending
    if (class_exists('CANWBE_Batch_Email_Sender')) {
        $batch_id = CANWBE_Batch_Email_Sender::queue_newsletter(
            $post->ID,
            $subscribers,
            $email_data['subject'],
            $email_data['message'],
            $email_data['headers'],
            $email_data['unsubscribe_message']
        );

        // Store batch ID for tracking
        update_post_meta($post->ID, '_newsletter_batch_id', $batch_id);

        canwbe_log('Newsletter queued for batch sending', array(
            'post_id' => $post->ID,
            'batch_id' => $batch_id,
            'recipient_count' => count($subscribers)
        ));

        // Show admin notice
        canwbe_add_batch_admin_notice($post, $batch_id);
    } else {
        // Fallback to direct sending
        canwbe_send_direct_emails($post, $subscribers, $email_data);
    }
}

/**
 * Prepare email content for sending
 */
function canwbe_prepare_email_content($post) {
    $subject = sanitize_text_field($post->post_title);
    $newsletter_excerpt = get_the_excerpt($post->ID);
    $newsletter_content = apply_filters('the_content', $post->post_content);
    $newsletter_link = get_permalink($post->ID);

    // Build message
    $message = '';

    // Add intro message if provided
    $intro_message = get_post_meta($post->ID, 'canwbe_intro_message', true);
    if (!empty($intro_message)) {
        $message .= wp_kses_post($intro_message) . '<br><br>';

        // Add web view link if enabled
        $web_view_enabled = get_option('canwbe_web_view_enabled', 'yes');
        if ($web_view_enabled === 'yes') {
            $web_view_text = get_option('canwbe_web_view_text', __('View on the web with graphics and images', 'create-a-newsletter-with-the-block-editor'));
            $web_view_text = apply_filters('canwbe_web_view_text', $web_view_text);

            if (!empty($web_view_text)) {
                $message .= '<a href="' . esc_url($newsletter_link) . '">' . esc_html($web_view_text) . '</a><br><br>';
            }
        }
    }

    // Add main content
    $message .= wp_kses_post($newsletter_content);

    // Replace CSS variables
    $variables = canwbe_get_default_css_variables();
    $message = canwbe_replace_css_variables($message, $variables);

    // Prepare headers
    $headers = array('Content-Type: text/html; charset=UTF-8');

    // Get unsubscribe message
    $unsubscribe_message = get_post_meta($post->ID, 'canwbe_unsubscribe_message', true);
    if (empty($unsubscribe_message)) {
        $unsubscribe_message = esc_html__('Unsubscribe from this newsletter', 'create-a-newsletter-with-the-block-editor');
    } else {
        $unsubscribe_message = sanitize_text_field($unsubscribe_message);
    }

    return array(
        'subject' => $subject,
        'message' => $message,
        'headers' => $headers,
        'unsubscribe_message' => $unsubscribe_message,
        'excerpt' => $newsletter_excerpt
    );
}

/**
 * Send emails directly (fallback method)
 */
function canwbe_send_direct_emails($post, $subscribers, $email_data) {
    canwbe_log('Using direct email sending (batch system not available)', array(
        'post_id' => $post->ID,
        'subscriber_count' => count($subscribers)
    ));

    // Set sender name filter
    add_filter('wp_mail_from_name', function($name) use ($email_data) {
        return sanitize_text_field($email_data['excerpt']);
    });

    $success_count = 0;
    $failed_count = 0;

    foreach ($subscribers as $subscriber) {
        $token = canwbe_generate_unsubscribe_token($subscriber->ID);
        $login_url = esc_url(home_url('/?canwbe_login=1&user_id=' . $subscriber->ID . '&token=' . $token));
        $final_message = $email_data['message'] . '<br><br><a href="' . $login_url . '">' .
            esc_html($email_data['unsubscribe_message']) . '</a>';

        $mail_sent = wp_mail(
            sanitize_email($subscriber->user_email),
            $email_data['subject'],
            $final_message,
            $email_data['headers']
        );

        if ($mail_sent) {
            $success_count++;

            // Track newsletter statistics
            if (function_exists('canwbe_track_newsletter_sent')) {
                canwbe_track_newsletter_sent($subscriber->ID, $post->ID);
            }
        } else {
            $failed_count++;
            canwbe_log('Direct email failed', array(
                'email' => $subscriber->user_email,
                'post_id' => $post->ID
            ));
        }
    }

    // Remove sender name filter
    remove_filter('wp_mail_from_name', function($name) use ($email_data) {
        return sanitize_text_field($email_data['excerpt']);
    });

    canwbe_log('Direct sending completed', array(
        'post_id' => $post->ID,
        'success' => $success_count,
        'failed' => $failed_count
    ));
}

/**
 * Add admin notice for batch queuing
 */
function canwbe_add_batch_admin_notice($post, $batch_id) {
    add_action('admin_notices', function() use ($post, $batch_id) {
        if (is_admin() && isset($_GET['post']) && $_GET['post'] == $post->ID) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <?php
                    printf(
                        __('Newsletter "%s" has been queued for batch sending (Batch ID: %s). <a href="%s">Monitor progress here</a>.', 'create-a-newsletter-with-the-block-editor'),
                        esc_html($post->post_title),
                        esc_html($batch_id),
                        esc_url(admin_url('edit.php?post_type=newsletter&page=canwbe-email-batches'))
                    );
                    ?>
                </p>
            </div>
            <?php
        }
    });
}
