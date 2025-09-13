<?php
/**
 * Admin Columns Management
 *
 * Handles custom columns in the newsletter admin list
 *
 * @package Create_A_Newsletter_With_The_Block_Editor
 * @since 1.3
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Add custom columns to the newsletter list in the admin area
 */
function canwbe_add_admin_columns($columns) {
    // Insert new columns after title
    $new_columns = array();
    foreach ($columns as $key => $title) {
        $new_columns[$key] = $title;

        if ($key === 'title') {
            $new_columns['excerpt'] = esc_html__('Number or Sender', 'create-a-newsletter-with-the-block-editor');
            $new_columns['recipients'] = esc_html__('Recipients', 'create-a-newsletter-with-the-block-editor');
            $new_columns['status'] = esc_html__('Sending Status', 'create-a-newsletter-with-the-block-editor');
        }
    }

    return $new_columns;
}
add_filter('manage_newsletter_posts_columns', 'canwbe_add_admin_columns');

/**
 * Display content for custom columns
 */
function canwbe_show_admin_column_content($column, $post_id) {
    switch ($column) {
        case 'excerpt':
            $post = get_post($post_id);
            echo esc_html(apply_filters('the_excerpt', $post->post_excerpt));
            break;

        case 'recipients':
            $recipient_roles = get_post_meta($post_id, 'canwbe_recipient_roles', true);
            if (empty($recipient_roles)) {
                $recipient_roles = array('newsletter_subscriber');
            }

            $total_recipients = 0;
            foreach ($recipient_roles as $role) {
                $users = get_users(array('role' => $role, 'fields' => 'ID'));
                $total_recipients += count($users);
            }

            echo esc_html($total_recipients . ' subscribers');

            // Show roles
            if (count($recipient_roles) > 1) {
                echo '<br><small>' . esc_html(implode(', ', $recipient_roles)) . '</small>';
            }
            break;

        case 'status':
            $batch_id = get_post_meta($post_id, '_newsletter_batch_id', true);

            if (!$batch_id) {
                if (get_post_status($post_id) === 'publish') {
                    echo '<span style="color: #666;">' . esc_html__('Not sent', 'create-a-newsletter-with-the-block-editor') . '</span>';
                } else {
                    echo '<span style="color: #999;">' . esc_html__('Draft', 'create-a-newsletter-with-the-block-editor') . '</span>';
                }
                break;
            }

            $batch_data = get_option('canwbe_batch_' . $batch_id);
            if (!$batch_data) {
                echo '<span style="color: #666;">' . esc_html__('Unknown', 'create-a-newsletter-with-the-block-editor') . '</span>';
                break;
            }

            $status = $batch_data['status'];
            $sent = isset($batch_data['sent_emails']) ? $batch_data['sent_emails'] : 0;
            $failed = isset($batch_data['failed_emails']) ? $batch_data['failed_emails'] : 0;
            $total = isset($batch_data['total_emails']) ? $batch_data['total_emails'] : 0;

            switch ($status) {
                case 'queued':
                    echo '<span style="color: #0073aa;">' . esc_html__('Queued', 'create-a-newsletter-with-the-block-editor') . '</span>';
                    break;

                case 'processing':
                    $progress = $total > 0 ? round(($sent + $failed) / $total * 100) : 0;
                    echo '<span style="color: #d54e21;">' .
                        sprintf(esc_html__('Sending... %d%%', 'create-a-newsletter-with-the-block-editor'), $progress) .
                        '</span>';
                    break;

                case 'completed':
                    if ($failed > 0) {
                        echo '<span style="color: #ffba00;">' .
                            sprintf(esc_html__('Sent: %d, Failed: %d', 'create-a-newsletter-with-the-block-editor'), $sent, $failed) .
                            '</span>';
                    } else {
                        echo '<span style="color: #46b450;">' .
                            sprintf(esc_html__('Sent to %d recipients', 'create-a-newsletter-with-the-block-editor'), $sent) .
                            '</span>';
                    }
                    break;

                case 'cancelled':
                    echo '<span style="color: #dc3232;">' . esc_html__('Cancelled', 'create-a-newsletter-with-the-block-editor') . '</span>';
                    break;

                default:
                    echo '<span style="color: #666;">' . esc_html(ucfirst($status)) . '</span>';
            }

            // Add link to batch details if available
            if (in_array($status, array('processing', 'completed', 'cancelled'))) {
                $batch_url = admin_url('edit.php?post_type=newsletter&page=canwbe-email-batches');
                echo '<br><a href="' . esc_url($batch_url) . '" style="font-size: 11px;">' .
                    esc_html__('View details', 'create-a-newsletter-with-the-block-editor') . '</a>';
            }
            break;
    }
}
add_action('manage_newsletter_posts_custom_column', 'canwbe_show_admin_column_content', 10, 2);

/**
 * Make custom columns sortable
 */
function canwbe_register_sortable_columns($columns) {
    $columns['excerpt'] = 'excerpt';
    $columns['recipients'] = 'recipients';
    return $columns;
}
add_filter('manage_edit-newsletter_sortable_columns', 'canwbe_register_sortable_columns');

/**
 * Handle sorting for custom columns
 */
function canwbe_handle_column_sorting($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    $orderby = $query->get('orderby');

    switch ($orderby) {
        case 'excerpt':
            $query->set('orderby', 'post_excerpt');
            break;

        case 'recipients':
            // This is complex, so we'll skip it for now
            // Could be implemented with a custom meta query
            break;
    }
}
add_action('pre_get_posts', 'canwbe_handle_column_sorting');

/**
 * Change the "Excerpt" label in newsletters
 */
function canwbe_change_excerpt_label($translated_text, $text, $domain) {
    global $post_type;

    if ($post_type === 'newsletter' && $translated_text === 'Excerpt') {
        $translated_text = esc_html__('Number or Sender', 'create-a-newsletter-with-the-block-editor');
    }

    return $translated_text;
}
add_filter('gettext', 'canwbe_change_excerpt_label', 20, 3);

/**
 * Add custom CSS for admin columns
 */
function canwbe_admin_column_styles() {
    $screen = get_current_screen();
    if ($screen && $screen->id === 'edit-newsletter') {
        ?>
        <style>
            .wp-list-table .column-excerpt { width: 15%; }
            .wp-list-table .column-recipients { width: 12%; }
            .wp-list-table .column-status { width: 20%; }
            .wp-list-table .column-date { width: 12%; }
        </style>
        <?php
    }
}
add_action('admin_head', 'canwbe_admin_column_styles');
