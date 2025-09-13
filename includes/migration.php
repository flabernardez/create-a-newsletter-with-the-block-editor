<?php
/**
 * Migration from lafu.studio Newsletter Plugin to Create a Newsletter with the Block Editor
 *
 * This file handles the migration of data from the old 'boletin' CPT to the new 'newsletter' CPT
 * and from 'suscriptor_boletin' role to 'newsletter_subscriber' role.
 *
 * @package Create_A_Newsletter_With_The_Block_Editor
 * @since 1.3
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class CANWBE_Migration {

    /**
     * Run the complete migration process
     */
    public static function run_migration() {
        $results = array(
            'posts_migrated' => 0,
            'users_migrated' => 0,
            'errors' => array()
        );

        // Check if old plugin data exists
        if (!self::has_old_data()) {
            $results['errors'][] = __('No data from the old plugin was found.', 'create-a-newsletter-with-the-block-editor');
            return $results;
        }

        // Migrate posts
        $post_results = self::migrate_posts();
        $results['posts_migrated'] = $post_results['migrated'];
        if (!empty($post_results['errors'])) {
            $results['errors'] = array_merge($results['errors'], $post_results['errors']);
        }

        // Migrate users
        $user_results = self::migrate_users();
        $results['users_migrated'] = $user_results['migrated'];
        if (!empty($user_results['errors'])) {
            $results['errors'] = array_merge($results['errors'], $user_results['errors']);
        }

        // Log the migration results
        error_log('CANWBE Migration completed: ' . print_r($results, true));

        return $results;
    }

    /**
     * Check if old plugin data exists
     */
    private static function has_old_data() {
        // Check for old posts
        $old_posts = get_posts(array(
            'post_type' => 'boletin',
            'post_status' => 'any',
            'numberposts' => 1
        ));

        // Check for old users
        $old_users = get_users(array(
            'role' => 'suscriptor_boletin',
            'number' => 1
        ));

        return !empty($old_posts) || !empty($old_users);
    }

    /**
     * Migrate posts from 'boletin' CPT to 'newsletter' CPT
     */
    private static function migrate_posts() {
        $results = array(
            'migrated' => 0,
            'errors' => array()
        );

        // Get all 'boletin' posts
        $old_posts = get_posts(array(
            'post_type' => 'boletin',
            'post_status' => 'any',
            'numberposts' => -1
        ));

        if (empty($old_posts)) {
            return $results;
        }

        foreach ($old_posts as $old_post) {
            try {
                // Check if this post has already been migrated
                $existing_newsletter = get_posts(array(
                    'post_type' => 'newsletter',
                    'meta_query' => array(
                        array(
                            'key' => '_migrated_from_boletin_id',
                            'value' => $old_post->ID,
                            'compare' => '='
                        )
                    ),
                    'numberposts' => 1
                ));

                if (!empty($existing_newsletter)) {
                    continue; // Skip if already migrated
                }

                // Create new newsletter post
                $new_post_data = array(
                    'post_title'   => $old_post->post_title,
                    'post_content' => $old_post->post_content,
                    'post_excerpt' => $old_post->post_excerpt,
                    'post_status'  => $old_post->post_status,
                    'post_author'  => $old_post->post_author,
                    'post_date'    => $old_post->post_date,
                    'post_date_gmt' => $old_post->post_date_gmt,
                    'post_type'    => 'newsletter',
                    'post_name'    => $old_post->post_name . '-migrated'
                );

                $new_post_id = wp_insert_post($new_post_data);

                if (is_wp_error($new_post_id)) {
                    $results['errors'][] = sprintf(
                        __('Error migrating post "%s": %s', 'create-a-newsletter-with-the-block-editor'),
                        $old_post->post_title,
                        $new_post_id->get_error_message()
                    );
                    continue;
                }

                // Add meta to track migration
                update_post_meta($new_post_id, '_migrated_from_boletin_id', $old_post->ID);
                update_post_meta($new_post_id, '_migration_date', current_time('mysql'));
                update_post_meta($new_post_id, '_original_status', $old_post->post_status);

                // Set default meta values for new newsletter system
                update_post_meta($new_post_id, 'canwbe_recipient_roles', array('newsletter_subscriber'));

                // Add migration notice in intro message
                if ($old_post->post_status === 'publish') {
                    update_post_meta($new_post_id, 'canwbe_intro_message',
                        __('⚠️ MIGRATED NEWSLETTER - This newsletter was migrated from the previous system and saved as draft to prevent automatic sending. Review and publish manually if needed.', 'create-a-newsletter-with-the-block-editor')
                    );
                } else {
                    update_post_meta($new_post_id, 'canwbe_intro_message',
                        __('This newsletter was migrated from the previous system.', 'create-a-newsletter-with-the-block-editor')
                    );
                }

                $results['migrated']++;

            } catch (Exception $e) {
                $results['errors'][] = sprintf(
                    __('Exception migrating post "%s": %s', 'create-a-newsletter-with-the-block-editor'),
                    $old_post->post_title,
                    $e->getMessage()
                );
            }
        }

        return $results;
    }

    /**
     * Migrate users from 'suscriptor_boletin' role to 'newsletter_subscriber' role
     */
    private static function migrate_users() {
        $results = array(
            'migrated' => 0,
            'errors' => array()
        );

        // Get all users with 'suscriptor_boletin' role
        $old_subscribers = get_users(array(
            'role' => 'suscriptor_boletin'
        ));

        if (empty($old_subscribers)) {
            return $results;
        }

        foreach ($old_subscribers as $user) {
            try {
                $user_obj = new WP_User($user->ID);

                // Check if user already has the new role
                if (in_array('newsletter_subscriber', $user_obj->roles)) {
                    continue; // Skip if already has new role
                }

                // Remove old role and add new role
                $user_obj->remove_role('suscriptor_boletin');
                $user_obj->add_role('newsletter_subscriber');

                // Add migration meta
                update_user_meta($user->ID, '_migrated_from_suscriptor_boletin', true);
                update_user_meta($user->ID, '_migration_date', current_time('mysql'));

                $results['migrated']++;

            } catch (Exception $e) {
                $results['errors'][] = sprintf(
                    __('Exception migrating user "%s": %s', 'create-a-newsletter-with-the-block-editor'),
                    $user->user_email,
                    $e->getMessage()
                );
            }
        }

        return $results;
    }

    /**
     * Clean up old data after successful migration (optional)
     */
    public static function cleanup_old_data() {
        // This method can be called manually if you want to remove old data
        // BE CAREFUL: This will permanently delete the old data

        $results = array(
            'posts_deleted' => 0,
            'role_removed' => false,
            'errors' => array()
        );

        // Delete old 'boletin' posts
        $old_posts = get_posts(array(
            'post_type' => 'boletin',
            'post_status' => 'any',
            'numberposts' => -1
        ));

        foreach ($old_posts as $post) {
            if (wp_delete_post($post->ID, true)) {
                $results['posts_deleted']++;
            }
        }

        // Remove old role (only if no users have it)
        $users_with_old_role = get_users(array('role' => 'suscriptor_boletin'));
        if (empty($users_with_old_role)) {
            remove_role('suscriptor_boletin');
            $results['role_removed'] = true;
        }

        return $results;
    }

    /**
     * Get migration status
     */
    public static function get_migration_status() {
        $status = array(
            'old_posts_count' => 0,
            'migrated_posts_count' => 0,
            'old_users_count' => 0,
            'migrated_users_count' => 0,
            'needs_migration' => false
        );

        // Count old posts
        $old_posts = get_posts(array(
            'post_type' => 'boletin',
            'post_status' => 'any',
            'numberposts' => -1,
            'fields' => 'ids'
        ));
        $status['old_posts_count'] = count($old_posts);

        // Count migrated posts
        $migrated_posts = get_posts(array(
            'post_type' => 'newsletter',
            'meta_key' => '_migrated_from_boletin_id',
            'numberposts' => -1,
            'fields' => 'ids'
        ));
        $status['migrated_posts_count'] = count($migrated_posts);

        // Count old users
        $old_users = get_users(array(
            'role' => 'suscriptor_boletin',
            'fields' => 'ids'
        ));
        $status['old_users_count'] = count($old_users);

        // Count migrated users
        $migrated_users = get_users(array(
            'meta_key' => '_migrated_from_suscriptor_boletin',
            'meta_value' => true,
            'fields' => 'ids'
        ));
        $status['migrated_users_count'] = count($migrated_users);

        // Determine if migration is needed
        $status['needs_migration'] = ($status['old_posts_count'] > 0 || $status['old_users_count'] > 0);

        return $status;
    }

    /**
     * Temporarily disable newsletter sending to prevent emails during migration
     */
    private static function disable_newsletter_sending() {
        // Set a transient flag that the sending function can check
        set_transient('canwbe_migration_in_progress', true, HOUR_IN_SECONDS);

        // Log the action
        error_log('CANWBE: Newsletter sending DISABLED for migration');
    }

    /**
     * Re-enable newsletter sending after migration
     */
    private static function enable_newsletter_sending() {
        // Remove the migration flag
        delete_transient('canwbe_migration_in_progress');

        // Log the action
        error_log('CANWBE: Newsletter sending RE-ENABLED after migration');
    }
}

// Auto-run migration on plugin activation if old data exists
function canwbe_maybe_run_migration() {
    $migration_completed = get_option('canwbe_migration_completed', false);

    if (!$migration_completed && class_exists('CANWBE_Migration')) {
        $status = CANWBE_Migration::get_migration_status();

        if ($status['needs_migration']) {
            $results = CANWBE_Migration::run_migration();

            // Mark migration as completed
            update_option('canwbe_migration_completed', true);
            update_option('canwbe_migration_results', $results);

            // Add admin notice
            add_action('admin_notices', 'canwbe_migration_notice');
        }
    }
}
add_action('admin_init', 'canwbe_maybe_run_migration');

// Show migration results in admin
function canwbe_migration_notice() {
    $results = get_option('canwbe_migration_results', array());

    if (empty($results)) {
        return;
    }

    $class = empty($results['errors']) ? 'notice-success' : 'notice-warning';

    ?>
    <div class="notice <?php echo esc_attr($class); ?> is-dismissible">
        <h3><?php esc_html_e('Newsletter Plugin Migration Completed', 'create-a-newsletter-with-the-block-editor'); ?></h3>
        <p>
            <?php
            printf(
                esc_html__('Migration results: %d newsletters migrated, %d subscribers migrated.', 'create-a-newsletter-with-the-block-editor'),
                intval($results['posts_migrated']),
                intval($results['users_migrated'])
            );
            ?>
        </p>

        <?php if (!empty($results['errors'])): ?>
            <details>
                <summary><?php esc_html_e('View errors', 'create-a-newsletter-with-the-block-editor'); ?></summary>
                <ul>
                    <?php foreach ($results['errors'] as $error): ?>
                        <li><?php echo esc_html($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </details>
        <?php endif; ?>

        <p>
            <em><?php esc_html_e('The old plugin data is still available. You can safely deactivate the old plugin once you verify everything is working correctly.', 'create-a-newsletter-with-the-block-editor'); ?></em>
        </p>
    </div>
    <?php

    // Clear the results after showing once
    delete_option('canwbe_migration_results');
}
