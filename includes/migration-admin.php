<?php
/**
 * Migration Admin Page
 *
 * Provides a manual migration interface in the WordPress admin
 *
 * @package Create_A_Newsletter_With_The_Block_Editor
 * @since 1.3
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Add migration page to admin menu
function canwbe_add_migration_admin_page() {
    add_submenu_page(
        'edit.php?post_type=newsletter',
        __('Migration Tool', 'create-a-newsletter-with-the-block-editor'),
        __('Migration Tool', 'create-a-newsletter-with-the-block-editor'),
        'manage_options',
        'canwbe-migration',
        'canwbe_migration_admin_page'
    );
}
add_action('admin_menu', 'canwbe_add_migration_admin_page');

// Migration admin page content
function canwbe_migration_admin_page() {
    // Handle form submissions
    if (isset($_POST['run_migration']) && wp_verify_nonce($_POST['migration_nonce'], 'canwbe_migration')) {
        $results = CANWBE_Migration::run_migration();
        echo '<div class="notice notice-success"><p>' .
            sprintf(__('Migration completed: %d posts and %d users migrated.', 'create-a-newsletter-with-the-block-editor'),
                $results['posts_migrated'], $results['users_migrated']) .
            '</p></div>';

        if (!empty($results['errors'])) {
            echo '<div class="notice notice-error"><p>' .
                __('Some errors occurred:', 'create-a-newsletter-with-the-block-editor') .
                '</p><ul>';
            foreach ($results['errors'] as $error) {
                echo '<li>' . esc_html($error) . '</li>';
            }
            echo '</ul></div>';
        }

        update_option('canwbe_migration_completed', true);
    }

    if (isset($_POST['cleanup_old_data']) && wp_verify_nonce($_POST['cleanup_nonce'], 'canwbe_cleanup')) {
        $cleanup_results = CANWBE_Migration::cleanup_old_data();
        echo '<div class="notice notice-info"><p>' .
            sprintf(__('Cleanup completed: %d old posts deleted, role removed: %s', 'create-a-newsletter-with-the-block-editor'),
                $cleanup_results['posts_deleted'],
                $cleanup_results['role_removed'] ? __('Yes', 'create-a-newsletter-with-the-block-editor') : __('No', 'create-a-newsletter-with-the-block-editor')) .
            '</p></div>';
    }

    // Get migration status
    $status = CANWBE_Migration::get_migration_status();
    $migration_completed = get_option('canwbe_migration_completed', false);
    ?>

    <div class="wrap">
        <h1><?php esc_html_e('Newsletter Migration Tool', 'create-a-newsletter-with-the-block-editor'); ?></h1>

        <div class="card">
            <h2><?php esc_html_e('Migration Status', 'create-a-newsletter-with-the-block-editor'); ?></h2>

            <table class="widefat">
                <tbody>
                <tr>
                    <th><?php esc_html_e('Old newsletters (boletin)', 'create-a-newsletter-with-the-block-editor'); ?></th>
                    <td><?php echo intval($status['old_posts_count']); ?></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Migrated newsletters', 'create-a-newsletter-with-the-block-editor'); ?></th>
                    <td><?php echo intval($status['migrated_posts_count']); ?></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Old subscribers (suscriptor_boletin)', 'create-a-newsletter-with-the-block-editor'); ?></th>
                    <td><?php echo intval($status['old_users_count']); ?></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Migrated subscribers', 'create-a-newsletter-with-the-block-editor'); ?></th>
                    <td><?php echo intval($status['migrated_users_count']); ?></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Migration completed', 'create-a-newsletter-with-the-block-editor'); ?></th>
                    <td>
                        <?php if ($migration_completed): ?>
                            <span class="dashicons dashicons-yes-alt" style="color: green;"></span>
                            <?php esc_html_e('Yes', 'create-a-newsletter-with-the-block-editor'); ?>
                        <?php else: ?>
                            <span class="dashicons dashicons-minus" style="color: orange;"></span>
                            <?php esc_html_e('No', 'create-a-newsletter-with-the-block-editor'); ?>
                        <?php endif; ?>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <?php if ($status['needs_migration'] && !$migration_completed): ?>
            <div class="card">
                <h2><?php esc_html_e('Run Migration', 'create-a-newsletter-with-the-block-editor'); ?></h2>
                <p><?php esc_html_e('This will migrate all data from the old lafu.studio Newsletter Plugin to the new system.', 'create-a-newsletter-with-the-block-editor'); ?></p>

                <form method="post" action="">
                    <?php wp_nonce_field('canwbe_migration', 'migration_nonce'); ?>
                    <input type="submit" name="run_migration" class="button button-primary"
                           value="<?php esc_attr_e('Run Migration', 'create-a-newsletter-with-the-block-editor'); ?>"
                           onclick="return confirm('<?php esc_attr_e('Are you sure you want to run the migration? This action cannot be undone.', 'create-a-newsletter-with-the-block-editor'); ?>');">
                </form>
            </div>
        <?php elseif ($migration_completed): ?>
            <div class="card">
                <h2><?php esc_html_e('Migration Complete', 'create-a-newsletter-with-the-block-editor'); ?></h2>
                <p><?php esc_html_e('The migration has been completed successfully. You can now safely deactivate the old plugin.', 'create-a-newsletter-with-the-block-editor'); ?></p>

                <?php if ($status['old_posts_count'] > 0 || $status['old_users_count'] > 0): ?>
                    <div style="border: 1px solid #dc3232; padding: 15px; background: #fff; margin: 15px 0;">
                        <h3 style="color: #dc3232; margin-top: 0;"><?php esc_html_e('⚠️ Cleanup Old Data', 'create-a-newsletter-with-the-block-editor'); ?></h3>
                        <p><?php esc_html_e('The old plugin data is still present. You can clean it up once you\'re sure everything is working correctly.', 'create-a-newsletter-with-the-block-editor'); ?></p>
                        <p><strong><?php esc_html_e('WARNING: This action is irreversible!', 'create-a-newsletter-with-the-block-editor'); ?></strong></p>

                        <form method="post" action="">
                            <?php wp_nonce_field('canwbe_cleanup', 'cleanup_nonce'); ?>
                            <input type="submit" name="cleanup_old_data" class="button button-secondary"
                                   value="<?php esc_attr_e('Delete Old Plugin Data', 'create-a-newsletter-with-the-block-editor'); ?>"
                                   onclick="return confirm('<?php esc_attr_e('Are you absolutely sure? This will permanently delete all old plugin data. This cannot be undone!', 'create-a-newsletter-with-the-block-editor'); ?>');">
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="card">
                <h2><?php esc_html_e('No Migration Needed', 'create-a-newsletter-with-the-block-editor'); ?></h2>
                <p><?php esc_html_e('No data from the old plugin was found, so no migration is necessary.', 'create-a-newsletter-with-the-block-editor'); ?></p>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2><?php esc_html_e('What Gets Migrated?', 'create-a-newsletter-with-the-block-editor'); ?></h2>
            <ul>
                <li><?php esc_html_e('✅ All newsletter posts from "boletin" to "newsletter" post type', 'create-a-newsletter-with-the-block-editor'); ?></li>
                <li><?php esc_html_e('✅ Post titles, content, excerpts, and authors', 'create-a-newsletter-with-the-block-editor'); ?></li>
                <li><?php esc_html_e('✅ Original publication dates and status (preserved exactly)', 'create-a-newsletter-with-the-block-editor'); ?></li>
                <li><?php esc_html_e('🛡️ Safe migration: No emails sent during the process', 'create-a-newsletter-with-the-block-editor'); ?></li>
                <li><?php esc_html_e('✅ Users from "suscriptor_boletin" to "newsletter_subscriber" role', 'create-a-newsletter-with-the-block-editor'); ?></li>
                <li><?php esc_html_e('✅ Migration tracking metadata', 'create-a-newsletter-with-the-block-editor'); ?></li>
            </ul>

            <h3><?php esc_html_e('Important Notes:', 'create-a-newsletter-with-the-block-editor'); ?></h3>
            <ul>
                <li><?php esc_html_e('📅 DATES PRESERVED: Original publication dates are maintained', 'create-a-newsletter-with-the-block-editor'); ?></li>
                <li><?php esc_html_e('📊 STATUS PRESERVED: Published posts remain published, drafts remain drafts', 'create-a-newsletter-with-the-block-editor'); ?></li>
                <li><?php esc_html_e('🔒 NO EMAILS: Migrated posts won\'t trigger automatic sending', 'create-a-newsletter-with-the-block-editor'); ?></li>
                <li><?php esc_html_e('🔄 Migration can be run multiple times safely (duplicates are avoided)', 'create-a-newsletter-with-the-block-editor'); ?></li>
                <li><?php esc_html_e('💾 Original data is preserved until you choose to clean it up', 'create-a-newsletter-with-the-block-editor'); ?></li>
                <li><?php esc_html_e('🎛️ New newsletters get default settings for the new plugin features', 'create-a-newsletter-with-the-block-editor'); ?></li>
            </ul>
        </div>
    </div>

    <style>
        .card {
            background: white;
            border: 1px solid #c3c4c7;
            border-left: 4px solid #72aee6;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
            padding: 1em 2em;
            margin: 20px 0;
        }

        .widefat th {
            width: 200px;
            font-weight: 600;
        }
    </style>
    <?php
}

// Include the migration admin page
require_once CANWBE_PLUGIN_PATH . 'includes/migration-admin.php';
