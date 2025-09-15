<?php
/**
 * Custom Post Type Registration
 *
 * Handles the newsletter custom post type registration
 *
 * @package Create_A_Newsletter_With_The_Block_Editor
 * @since 1.3
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Create 'newsletter' Custom Post Type
 */
function canwbe_create_cpt_newsletter() {
    $labels = array(
        'name'               => esc_html__('Newsletters', 'create-a-newsletter-with-the-block-editor'),
        'singular_name'      => esc_html__('Newsletter', 'create-a-newsletter-with-the-block-editor'),
        'menu_name'          => esc_html__('Newsletters', 'create-a-newsletter-with-the-block-editor'),
        'add_new'            => esc_html__('Add New', 'create-a-newsletter-with-the-block-editor'),
        'add_new_item'       => esc_html__('Add New Newsletter', 'create-a-newsletter-with-the-block-editor'),
        'new_item'           => esc_html__('New Newsletter', 'create-a-newsletter-with-the-block-editor'),
        'edit_item'          => esc_html__('Edit Newsletter', 'create-a-newsletter-with-the-block-editor'),
        'view_item'          => esc_html__('View Newsletter', 'create-a-newsletter-with-the-block-editor'),
        'all_items'          => esc_html__('All Newsletters', 'create-a-newsletter-with-the-block-editor'),
        'search_items'       => esc_html__('Search Newsletters', 'create-a-newsletter-with-the-block-editor'),
        'parent_item_colon'  => esc_html__('Parent Newsletters:', 'create-a-newsletter-with-the-block-editor'),
        'not_found'          => esc_html__('No newsletters found.', 'create-a-newsletter-with-the-block-editor'),
        'not_found_in_trash' => esc_html__('No newsletters found in Trash.', 'create-a-newsletter-with-the-block-editor'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'newsletters'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-email-alt',
        'show_in_rest'       => true,
        'supports'           => array(
            'title',
            'editor',
            'excerpt',
            'author',
            'custom-fields',
            'revisions'
        ),
        // Completely empty - no template at all
    );

    register_post_type('newsletter', $args);
}
add_action('init', 'canwbe_create_cpt_newsletter');

/**
 * Add custom post states for newsletters
 */
function canwbe_add_newsletter_post_states($post_states, $post) {
    if ($post->post_type === 'newsletter') {
        // Add batch status if available
        if (get_post_meta($post->ID, '_newsletter_batch_id', true)) {
            $batch_id = get_post_meta($post->ID, '_newsletter_batch_id', true);
            $batch_data = get_option('canwbe_batch_' . $batch_id);

            if ($batch_data && isset($batch_data['status'])) {
                switch ($batch_data['status']) {
                    case 'queued':
                        $post_states['newsletter_queued'] = esc_html__('Queued for Sending', 'create-a-newsletter-with-the-block-editor');
                        break;
                    case 'processing':
                        $post_states['newsletter_sending'] = esc_html__('Sending...', 'create-a-newsletter-with-the-block-editor');
                        break;
                    case 'completed':
                        $sent_count = isset($batch_data['sent_emails']) ? $batch_data['sent_emails'] : 0;
                        $post_states['newsletter_sent'] = sprintf(
                            esc_html__('Sent (%d recipients)', 'create-a-newsletter-with-the-block-editor'),
                            $sent_count
                        );
                        break;
                }
            }
        }

        // Add migration status if migrated
        if (get_post_meta($post->ID, '_migrated_from_boletin_id', true)) {
            $post_states['migrated'] = esc_html__('Migrated', 'create-a-newsletter-with-the-block-editor');
        }
    }

    return $post_states;
}
add_filter('display_post_states', 'canwbe_add_newsletter_post_states', 10, 2);
