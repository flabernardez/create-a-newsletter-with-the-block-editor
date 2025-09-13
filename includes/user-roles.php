<?php
/**
 * User Roles Management
 *
 * Handles newsletter subscriber role and related functionality
 *
 * @package Create_A_Newsletter_With_The_Block_Editor
 * @since 1.3
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Create 'newsletter_subscriber' role
 */
function canwbe_create_newsletter_subscriber_role() {
    // Only create if doesn't exist
    if (!get_role('newsletter_subscriber')) {
        $subscriber_capabilities = get_role('subscriber')->capabilities;
        add_role(
            'newsletter_subscriber',
            esc_html__('Newsletter Subscriber', 'create-a-newsletter-with-the-block-editor'),
            $subscriber_capabilities
        );

        canwbe_log('Newsletter subscriber role created');
    }
}

/**
 * Allow email as username during registration
 */
function canwbe_allow_email_as_username($result) {
    if (is_email($result['user_name'])) {
        unset($result['errors']->errors['user_name']);
    }
    return $result;
}
add_filter('wpmu_validate_user_signup', 'canwbe_allow_email_as_username');

/**
 * Redirect 'newsletter_subscriber' users upon login
 */
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

/**
 * Hide admin bar for 'newsletter_subscriber'
 */
function canwbe_hide_admin_bar() {
    if (canwbe_is_newsletter_subscriber() && !current_user_can('administrator')) {
        show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'canwbe_hide_admin_bar');

/**
 * Restrict wp-admin access for 'newsletter_subscriber', allow super admins
 */
function canwbe_restrict_wp_admin() {
    if (is_admin() && !defined('DOING_AJAX') && canwbe_is_newsletter_subscriber() && !is_super_admin()) {
        wp_redirect(home_url());
        exit;
    }
}
add_action('init', 'canwbe_restrict_wp_admin');

/**
 * Add newsletter subscriber role to user profile page
 */
function canwbe_add_subscriber_role_profile_field($user) {
    if (!current_user_can('edit_users')) {
        return;
    }

    $is_subscriber = canwbe_is_newsletter_subscriber($user->ID);
    ?>
    <h3><?php esc_html_e('Newsletter Subscription', 'create-a-newsletter-with-the-block-editor'); ?></h3>
    <table class="form-table">
        <tr>
            <th>
                <label for="newsletter_subscriber">
                    <?php esc_html_e('Newsletter Subscriber', 'create-a-newsletter-with-the-block-editor'); ?>
                </label>
            </th>
            <td>
                <label>
                    <input type="checkbox" name="newsletter_subscriber" id="newsletter_subscriber"
                           value="1" <?php checked($is_subscriber); ?> />
                    <?php esc_html_e('Subscribe this user to newsletters', 'create-a-newsletter-with-the-block-editor'); ?>
                </label>
                <p class="description">
                    <?php esc_html_e('When checked, this user will receive newsletter emails.', 'create-a-newsletter-with-the-block-editor'); ?>
                </p>
            </td>
        </tr>

        <?php if ($is_subscriber): ?>
            <tr>
                <th><?php esc_html_e('Unsubscribe Token', 'create-a-newsletter-with-the-block-editor'); ?></th>
                <td>
                    <?php
                    $token = get_user_meta($user->ID, 'canwbe_unsubscribe_token', true);
                    if ($token): ?>
                        <code><?php echo esc_html(substr($token, 0, 10) . '...'); ?></code>
                        <p class="description">
                            <?php esc_html_e('Secure token for unsubscribe links.', 'create-a-newsletter-with-the-block-editor'); ?>
                        </p>

                        <button type="button" class="button button-small" onclick="canwbeRegenerateToken(<?php echo $user->ID; ?>)">
                            <?php esc_html_e('Regenerate Token', 'create-a-newsletter-with-the-block-editor'); ?>
                        </button>
                    <?php else: ?>
                        <span style="color: #d63638;">
                        <?php esc_html_e('No token generated', 'create-a-newsletter-with-the-block-editor'); ?>
                    </span>
                        <p class="description">
                            <?php esc_html_e('Token will be generated when first newsletter is sent.', 'create-a-newsletter-with-the-block-editor'); ?>
                        </p>

                        <button type="button" class="button button-small" onclick="canwbeGenerateToken(<?php echo $user->ID; ?>)">
                            <?php esc_html_e('Generate Token Now', 'create-a-newsletter-with-the-block-editor'); ?>
                        </button>
                    <?php endif; ?>
                </td>
            </tr>

            <tr>
                <th><?php esc_html_e('Newsletter Statistics', 'create-a-newsletter-with-the-block-editor'); ?></th>
                <td>
                    <?php
                    // Get newsletter statistics for this user
                    $newsletters_sent = get_user_meta($user->ID, '_newsletters_received_count', true);
                    $last_newsletter = get_user_meta($user->ID, '_last_newsletter_received', true);

                    if (!$newsletters_sent) $newsletters_sent = 0;
                    ?>

                    <p>
                        <strong><?php esc_html_e('Newsletters received:', 'create-a-newsletter-with-the-block-editor'); ?></strong>
                        <?php echo esc_html($newsletters_sent); ?>
                    </p>

                    <?php if ($last_newsletter): ?>
                        <p>
                            <strong><?php esc_html_e('Last newsletter:', 'create-a-newsletter-with-the-block-editor'); ?></strong>
                            <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($last_newsletter))); ?>
                        </p>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endif; ?>
    </table>

    <script>
        function canwbeGenerateToken(userId) {
            if (confirm('<?php esc_html_e('Generate a new unsubscribe token for this user?', 'create-a-newsletter-with-the-block-editor'); ?>')) {
                jQuery.post(ajaxurl, {
                    action: 'canwbe_generate_token',
                    user_id: userId,
                    nonce: '<?php echo wp_create_nonce('canwbe_token_action'); ?>'
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('<?php esc_html_e('Error generating token.', 'create-a-newsletter-with-the-block-editor'); ?>');
                    }
                });
            }
        }

        function canwbeRegenerateToken(userId) {
            if (confirm('<?php esc_html_e('Regenerate the unsubscribe token? This will invalidate the current token.', 'create-a-newsletter-with-the-block-editor'); ?>')) {
                jQuery.post(ajaxurl, {
                    action: 'canwbe_regenerate_token',
                    user_id: userId,
                    nonce: '<?php echo wp_create_nonce('canwbe_token_action'); ?>'
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('<?php esc_html_e('Error regenerating token.', 'create-a-newsletter-with-the-block-editor'); ?>');
                    }
                });
            }
        }
    </script>
    <?php
}
add_action('show_user_profile', 'canwbe_add_subscriber_role_profile_field');
add_action('edit_user_profile', 'canwbe_add_subscriber_role_profile_field');

/**
 * Save newsletter subscriber role from profile page
 */
function canwbe_save_subscriber_role_profile_field($user_id) {
    if (!current_user_can('edit_users')) {
        return;
    }

    $user = get_user_by('id', $user_id);
    if (!$user) {
        return;
    }

    $is_subscriber = isset($_POST['newsletter_subscriber']) && $_POST['newsletter_subscriber'] == '1';
    $was_subscriber = canwbe_is_newsletter_subscriber($user_id);

    if ($is_subscriber && !$was_subscriber) {
        // Add newsletter subscriber role
        $user->add_role('newsletter_subscriber');

        // Generate token if doesn't exist
        if (!get_user_meta($user_id, 'canwbe_unsubscribe_token', true)) {
            canwbe_generate_unsubscribe_token($user_id);
        }

        canwbe_log('User added to newsletter subscribers via profile', array(
            'user_id' => $user_id,
            'email' => $user->user_email
        ));

    } elseif (!$is_subscriber && $was_subscriber) {
        // Remove newsletter subscriber role
        $user->remove_role('newsletter_subscriber');

        canwbe_log('User removed from newsletter subscribers via profile', array(
            'user_id' => $user_id,
            'email' => $user->user_email
        ));
    }
}
add_action('personal_options_update', 'canwbe_save_subscriber_role_profile_field');
add_action('edit_user_profile_update', 'canwbe_save_subscriber_role_profile_field');

/**
 * Get users by newsletter roles
 */
function canwbe_get_users_by_roles($roles = array()) {
    if (empty($roles)) {
        $roles = array('newsletter_subscriber');
    }

    return get_users(array(
        'role__in' => $roles,
        'fields' => array('ID', 'user_email', 'display_name'),
    ));
}

/**
 * Add bulk actions for newsletter subscribers in users list
 */
function canwbe_add_user_bulk_actions($actions) {
    $actions['add_newsletter_subscriber'] = esc_html__('Add to Newsletter Subscribers', 'create-a-newsletter-with-the-block-editor');
    $actions['remove_newsletter_subscriber'] = esc_html__('Remove from Newsletter Subscribers', 'create-a-newsletter-with-the-block-editor');
    return $actions;
}
add_filter('bulk_actions-users', 'canwbe_add_user_bulk_actions');

/**
 * Handle bulk actions for newsletter subscribers
 */
function canwbe_handle_user_bulk_actions($redirect_to, $doaction, $user_ids) {
    if ($doaction === 'add_newsletter_subscriber') {
        $count = 0;
        foreach ($user_ids as $user_id) {
            $user = get_user_by('id', $user_id);
            if ($user && !canwbe_is_newsletter_subscriber($user_id)) {
                $user->add_role('newsletter_subscriber');
                canwbe_generate_unsubscribe_token($user_id);
                $count++;
            }
        }

        $redirect_to = add_query_arg('canwbe_added', $count, $redirect_to);

    } elseif ($doaction === 'remove_newsletter_subscriber') {
        $count = 0;
        foreach ($user_ids as $user_id) {
            $user = get_user_by('id', $user_id);
            if ($user && canwbe_is_newsletter_subscriber($user_id)) {
                $user->remove_role('newsletter_subscriber');
                $count++;
            }
        }

        $redirect_to = add_query_arg('canwbe_removed', $count, $redirect_to);
    }

    return $redirect_to;
}
add_filter('handle_bulk_actions-users', 'canwbe_handle_user_bulk_actions', 10, 3);

/**
 * Show admin notices for bulk actions
 */
function canwbe_user_bulk_action_notices() {
    if (!empty($_REQUEST['canwbe_added'])) {
        $count = intval($_REQUEST['canwbe_added']);
        printf(
            '<div class="notice notice-success is-dismissible"><p>' .
            _n('%d user added to newsletter subscribers.', '%d users added to newsletter subscribers.', $count, 'create-a-newsletter-with-the-block-editor') .
            '</p></div>',
            $count
        );
    }

    if (!empty($_REQUEST['canwbe_removed'])) {
        $count = intval($_REQUEST['canwbe_removed']);
        printf(
            '<div class="notice notice-success is-dismissible"><p>' .
            _n('%d user removed from newsletter subscribers.', '%d users removed from newsletter subscribers.', $count, 'create-a-newsletter-with-the-block-editor') .
            '</p></div>',
            $count
        );
    }
}
add_action('admin_notices', 'canwbe_user_bulk_action_notices');

/**
 * Add newsletter subscriber column to users list
 */
function canwbe_add_user_column($columns) {
    $columns['newsletter_subscriber'] = esc_html__('Newsletter', 'create-a-newsletter-with-the-block-editor');
    return $columns;
}
add_filter('manage_users_columns', 'canwbe_add_user_column');

/**
 * Show newsletter subscriber status in users list
 */
function canwbe_show_user_column_content($value, $column_name, $user_id) {
    if ($column_name === 'newsletter_subscriber') {
        if (canwbe_is_newsletter_subscriber($user_id)) {
            $token = get_user_meta($user_id, 'canwbe_unsubscribe_token', true);
            if ($token) {
                return '<span style="color: #00a32a;" title="' . esc_attr__('Active subscriber with token', 'create-a-newsletter-with-the-block-editor') . '">✓</span>';
            } else {
                return '<span style="color: #dba617;" title="' . esc_attr__('Subscriber without token', 'create-a-newsletter-with-the-block-editor') . '">⚠</span>';
            }
        } else {
            return '<span style="color: #ddd;" title="' . esc_attr__('Not a subscriber', 'create-a-newsletter-with-the-block-editor') . '">—</span>';
        }
    }
    return $value;
}
add_filter('manage_users_custom_column', 'canwbe_show_user_column_content', 10, 3);

/**
 * AJAX handler for generating token
 */
function canwbe_ajax_generate_token() {
    check_ajax_referer('canwbe_token_action', 'nonce');

    if (!current_user_can('edit_users')) {
        wp_send_json_error('Permission denied');
        return;
    }

    $user_id = intval($_POST['user_id']);
    if (!$user_id) {
        wp_send_json_error('Invalid user ID');
        return;
    }

    $token = canwbe_generate_unsubscribe_token($user_id);

    if ($token) {
        canwbe_log('Token generated via admin', array('user_id' => $user_id));
        wp_send_json_success('Token generated');
    } else {
        wp_send_json_error('Failed to generate token');
    }
}
add_action('wp_ajax_canwbe_generate_token', 'canwbe_ajax_generate_token');

/**
 * AJAX handler for regenerating token
 */
function canwbe_ajax_regenerate_token() {
    check_ajax_referer('canwbe_token_action', 'nonce');

    if (!current_user_can('edit_users')) {
        wp_send_json_error('Permission denied');
        return;
    }

    $user_id = intval($_POST['user_id']);
    if (!$user_id) {
        wp_send_json_error('Invalid user ID');
        return;
    }

    // Delete old token and generate new one
    delete_user_meta($user_id, 'canwbe_unsubscribe_token');
    $token = canwbe_generate_unsubscribe_token($user_id);

    if ($token) {
        canwbe_log('Token regenerated via admin', array('user_id' => $user_id));
        wp_send_json_success('Token regenerated');
    } else {
        wp_send_json_error('Failed to regenerate token');
    }
}
add_action('wp_ajax_canwbe_regenerate_token', 'canwbe_ajax_regenerate_token');

/**
 * Add newsletter subscription field to user registration form
 */
function canwbe_add_registration_field() {
    if (get_option('users_can_register')) {
        ?>
        <p>
            <label for="newsletter_signup">
                <input name="newsletter_signup" type="checkbox" id="newsletter_signup" value="1" checked="checked" />
                <?php esc_html_e('Subscribe to newsletter', 'create-a-newsletter-with-the-block-editor'); ?>
            </label>
        </p>
        <?php
    }
}
add_action('register_form', 'canwbe_add_registration_field');

/**
 * Process newsletter subscription on user registration
 */
function canwbe_process_registration_subscription($user_id) {
    if (isset($_POST['newsletter_signup']) && $_POST['newsletter_signup'] == '1') {
        $user = get_user_by('id', $user_id);
        if ($user) {
            $user->add_role('newsletter_subscriber');
            canwbe_generate_unsubscribe_token($user_id);

            canwbe_log('User subscribed during registration', array(
                'user_id' => $user_id,
                'email' => $user->user_email
            ));
        }
    }
}
add_action('user_register', 'canwbe_process_registration_subscription');

/**
 * Track newsletter statistics
 */
function canwbe_track_newsletter_sent($user_id, $newsletter_id) {
    $current_count = get_user_meta($user_id, '_newsletters_received_count', true);
    if (!$current_count) $current_count = 0;

    update_user_meta($user_id, '_newsletters_received_count', $current_count + 1);
    update_user_meta($user_id, '_last_newsletter_received', current_time('mysql'));
    update_user_meta($user_id, '_last_newsletter_id', $newsletter_id);
}
