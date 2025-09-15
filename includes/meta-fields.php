<?php
/**
 * Meta Fields Management
 *
 * Handles newsletter meta fields registration and processing
 *
 * @package Create_A_Newsletter_With_The_Block_Editor
 * @since 1.3
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Register meta fields for 'newsletter'
 */
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
            'default'           => '',
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
            'default'           => '',
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

    // Field for batch ID (internal use)
    register_post_meta(
        'newsletter',
        '_newsletter_batch_id',
        array(
            'type'              => 'string',
            'single'            => true,
            'sanitize_callback' => 'sanitize_text_field',
            'show_in_rest'      => false,
        )
    );
}
add_action('init', 'canwbe_register_newsletter_meta');

/**
 * Sanitize roles
 */
function canwbe_sanitize_roles($roles) {
    global $wp_roles;
    $all_roles = $wp_roles->roles;
    $all_roles_keys = array_keys($all_roles);
    $roles = (array) $roles;
    $roles = array_intersect($roles, $all_roles_keys);

    // Ensure at least one role is selected
    if (empty($roles)) {
        $roles = array('newsletter_subscriber');
    }

    return $roles;
}

/**
 * Enqueue editor assets and pass roles to JavaScript
 */
function canwbe_enqueue_editor_assets() {
    // Only enqueue on newsletter edit screen
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'newsletter') {
        return;
    }

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

    // Get all user roles for the editor
    global $wp_roles;
    $all_roles = $wp_roles->roles;
    $roles = array();

    foreach ($all_roles as $role_key => $role_data) {
        $roles[] = array(
            'value' => $role_key,
            'label' => translate_user_role($role_data['name']),
        );
    }

    // Localize script with roles data
    wp_localize_script(
        'canwbe-editor-script',
        'canwbeRoles',
        $roles
    );

    // Also add some config data
    wp_localize_script(
        'canwbe-editor-script',
        'canwbeConfig',
        array(
            'pluginUrl' => CANWBE_PLUGIN_URL,
            'ajaxUrl'   => admin_url('admin-ajax.php'),
            'nonce'     => wp_create_nonce('canwbe_editor_nonce'),
            // Add translatable strings for JavaScript
            'i18n'     => array(
                'helpTextRoles' => __('Separate with commas or Enter key.', 'create-a-newsletter-with-the-block-editor'),
            ),
        )
    );
}
add_action('enqueue_block_editor_assets', 'canwbe_enqueue_editor_assets');

/**
 * Save additional newsletter data when post is saved
 */
function canwbe_save_newsletter_post($post_id, $post, $update) {
    // Only process newsletter posts
    if ($post->post_type !== 'newsletter') {
        return;
    }

    // Skip autosaves and revisions
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if ($post->post_status === 'auto-draft') {
        return;
    }

    // Log newsletter save
    canwbe_log('Newsletter saved', array(
        'post_id' => $post_id,
        'post_status' => $post->post_status,
        'is_update' => $update
    ));
}
add_action('save_post', 'canwbe_save_newsletter_post', 10, 3);
