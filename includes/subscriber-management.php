<?php
/**
 * Subscriber Management
 *
 * Handles newsletter subscriber-related functionality
 *
 * @package Create_A_Newsletter_With_The_Block_Editor
 * @since 1.3
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Add subscribers management to admin menu
 */
function canwbe_add_subscribers_admin_menu() {
    add_submenu_page(
        'edit.php?post_type=newsletter',
        __('Subscribers', 'create-a-newsletter-with-the-block-editor'),
        __('Subscribers', 'create-a-newsletter-with-the-block-editor'),
        'manage_options',
        'canwbe-subscribers',
        'canwbe_subscribers_admin_page'
    );
}
add_action('admin_menu', 'canwbe_add_subscribers_admin_menu');

/**
 * Subscribers admin page
 */
function canwbe_subscribers_admin_page() {
    // Handle actions
    if (isset($_POST['action']) && wp_verify_nonce($_POST['canwbe_nonce'], 'canwbe_subscriber_action')) {
        canwbe_handle_subscriber_actions();
    }

    $subscribers = canwbe_get_all_subscribers();
    $total_subscribers = count($subscribers);

    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Newsletter Subscribers', 'create-a-newsletter-with-the-block-editor'); ?></h1>

        <div class="notice notice-info">
            <p>
                <?php
                printf(
                    esc_html__('Total subscribers: %d', 'create-a-newsletter-with-the-block-editor'),
                    $total_subscribers
                );
                ?>
            </p>
        </div>

        <div class="tablenav top">
            <div class="alignleft actions">
                <form method="post" action="" style="display: inline-block;">
                    <?php wp_nonce_field('canwbe_subscriber_action', 'canwbe_nonce'); ?>
                    <input type="hidden" name="action" value="add_subscriber">

                    <input type="email" name="subscriber_email" placeholder="<?php esc_attr_e('Email address', 'create-a-newsletter-with-the-block-editor'); ?>" required>
                    <input type="text" name="subscriber_name" placeholder="<?php esc_attr_e('Display name (optional)', 'create-a-newsletter-with-the-block-editor'); ?>">

                    <?php submit_button(__('Add Subscriber', 'create-a-newsletter-with-the-block-editor'), 'secondary', 'submit', false); ?>
                </form>
            </div>
        </div>

        <?php if (empty($subscribers)): ?>
            <div class="notice notice-warning">
                <p><?php esc_html_e('No subscribers found. Add some subscribers to start sending newsletters!', 'create-a-newsletter-with-the-block-editor'); ?></p>
            </div>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                <tr>
                    <th scope="col"><?php esc_html_e('Email', 'create-a-newsletter-with-the-block-editor'); ?></th>
                    <th scope="col"><?php esc_html_e('Name', 'create-a-newsletter-with-the-block-editor'); ?></th>
                    <th scope="col"><?php esc_html_e('Registered', 'create-a-newsletter-with-the-block-editor'); ?></th>
                    <th scope="col"><?php esc_html_e('Status', 'create-a-newsletter-with-the-block-editor'); ?></th>
                    <th scope="col"><?php esc_html_e('Actions', 'create-a-newsletter-with-the-block-editor'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($subscribers as $subscriber): ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($subscriber->user_email); ?></strong>
                            <?php if (get_user_meta($subscriber->ID, '_migrated_from_suscriptor_boletin', true)): ?>
                                <br><span class="description"><?php esc_html_e('Migrated user', 'create-a-newsletter-with-the-block-editor'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html($subscriber->display_name); ?></td>
                        <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($subscriber->user_registered))); ?></td>
                        <td>
                            <?php if (get_user_meta($subscriber->ID, 'canwbe_unsubscribe_token', true)): ?>
                                <span style="color: green;">✓ <?php esc_html_e('Active', 'create-a-newsletter-with-the-block-editor'); ?></span>
                            <?php else: ?>
                                <span style="color: orange;">⚠ <?php esc_html_e('No token', 'create-a-newsletter-with-the-block-editor'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="post" action="" style="display: inline-block;">
                                <?php wp_nonce_field('canwbe_subscriber_action', 'canwbe_nonce'); ?>
                                <input type="hidden" name="action" value="delete_subscriber">
                                <input type="hidden" name="subscriber_id" value="<?php echo esc_attr($subscriber->ID); ?>">

                                <input type="submit" class="button button-small" value="<?php esc_attr_e('Remove', 'create-a-newsletter-with-the-block-editor'); ?>"
                                       onclick="return confirm('<?php esc_attr_e('Are you sure you want to remove this subscriber?', 'create-a-newsletter-with-the-block-editor'); ?>');">
                            </form>

                            <a href="<?php echo esc_url(get_edit_user_link($subscriber->ID)); ?>" class="button button-small">
                                <?php esc_html_e('Edit', 'create-a-newsletter-with-the-block-editor'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <span class="displaying-num">
                    <?php
                    printf(
                        esc_html(_n('%s subscriber', '%s subscribers', $total_subscribers, 'create-a-newsletter-with-the-block-editor')),
                        number_format_i18n($total_subscribers)
                    );
                    ?>
                </span>
            </div>
        </div>

        <!-- Import/Export Section -->
        <div class="card" style="margin-top: 20px;">
            <h2><?php esc_html_e('Import/Export Subscribers', 'create-a-newsletter-with-the-block-editor'); ?></h2>

            <h3><?php esc_html_e('Export Subscribers', 'create-a-newsletter-with-the-block-editor'); ?></h3>
            <p><?php esc_html_e('Download all subscribers as a CSV file.', 'create-a-newsletter-with-the-block-editor'); ?></p>

            <p>
                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?action=export_subscribers'), 'canwbe_export_subscribers', 'nonce')); ?>"
                   class="button button-secondary">
                    <?php esc_html_e('Export to CSV', 'create-a-newsletter-with-the-block-editor'); ?>
                </a>
            </p>

            <h3><?php esc_html_e('Quick Actions', 'create-a-newsletter-with-the-block-editor'); ?></h3>
            <p>
                <a href="<?php echo esc_url(admin_url('users.php')); ?>" class="button">
                    <?php esc_html_e('Manage All Users', 'create-a-newsletter-with-the-block-editor'); ?>
                </a>

                <a href="<?php echo esc_url(admin_url('edit.php?post_type=newsletter&page=canwbe-email-settings')); ?>" class="button">
                    <?php esc_html_e('Email Settings', 'create-a-newsletter-with-the-block-editor'); ?>
                </a>
            </p>
        </div>
    </div>

    <style>
        .card {
            background: white;
            border: 1px solid #c3c4c7;
            border-left: 4px solid #72aee6;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
            padding: 1em 2em;
        }
    </style>
    <?php
}

/**
 * Handle subscriber management actions
 */
function canwbe_handle_subscriber_actions() {
    $action = sanitize_text_field($_POST['action']);

    switch ($action) {
        case 'add_subscriber':
            canwbe_add_new_subscriber();
            break;

        case 'delete_subscriber':
            canwbe_delete_subscriber();
            break;
    }
}

/**
 * Add new subscriber
 */
function canwbe_add_new_subscriber() {
    $email = sanitize_email($_POST['subscriber_email']);
    $name = sanitize_text_field($_POST['subscriber_name']);

    if (!is_email($email)) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>' .
                esc_html__('Invalid email address.', 'create-a-newsletter-with-the-block-editor') .
                '</p></div>';
        });
        return;
    }

    // Check if user already exists
    if (email_exists($email)) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>' .
                esc_html__('A user with this email already exists.', 'create-a-newsletter-with-the-block-editor') .
                '</p></div>';
        });
        return;
    }

    // Create user
    $user_data = array(
        'user_login' => $email,
        'user_email' => $email,
        'display_name' => !empty($name) ? $name : $email,
        'role' => 'newsletter_subscriber'
    );

    $user_id = wp_insert_user($user_data);

    if (is_wp_error($user_id)) {
        add_action('admin_notices', function() use ($user_id) {
            echo '<div class="notice notice-error"><p>' .
                sprintf(esc_html__('Error creating subscriber: %s', 'create-a-newsletter-with-the-block-editor'), $user_id->get_error_message()) .
                '</p></div>';
        });
        return;
    }

    // Generate unsubscribe token
    canwbe_generate_unsubscribe_token($user_id);

    canwbe_log('New subscriber added', array(
        'user_id' => $user_id,
        'email' => $email
    ));

    add_action('admin_notices', function() {
        echo '<div class="notice notice-success"><p>' .
            esc_html__('Subscriber added successfully!', 'create-a-newsletter-with-the-block-editor') .
            '</p></div>';
    });
}

/**
 * Delete subscriber
 */
function canwbe_delete_subscriber() {
    $user_id = intval($_POST['subscriber_id']);

    if (!$user_id || !canwbe_is_newsletter_subscriber($user_id)) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>' .
                esc_html__('Invalid subscriber.', 'create-a-newsletter-with-the-block-editor') .
                '</p></div>';
        });
        return;
    }

    require_once(ABSPATH . 'wp-admin/includes/user.php');

    $user = get_user_by('id', $user_id);
    if ($user) {
        canwbe_log('Subscriber removed by admin', array(
            'user_id' => $user_id,
            'email' => $user->user_email
        ));

        wp_delete_user($user_id);

        add_action('admin_notices', function() {
            echo '<div class="notice notice-success"><p>' .
                esc_html__('Subscriber removed successfully!', 'create-a-newsletter-with-the-block-editor') .
                '</p></div>';
        });
    }
}

/**
 * Get all newsletter subscribers
 */
function canwbe_get_all_subscribers() {
    return get_users(array(
        'role' => 'newsletter_subscriber',
        'orderby' => 'registered',
        'order' => 'DESC'
    ));
}

/**
 * Export subscribers to CSV
 */
function canwbe_export_subscribers_csv() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to export subscribers.', 'create-a-newsletter-with-the-block-editor'));
    }

    $subscribers = canwbe_get_all_subscribers();

    $filename = 'newsletter-subscribers-' . date('Y-m-d') . '.csv';

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // CSV headers
    fputcsv($output, array(
        'ID',
        'Email',
        'Display Name',
        'Registered Date',
        'Last Login',
        'Has Token'
    ));

    foreach ($subscribers as $subscriber) {
        $has_token = get_user_meta($subscriber->ID, 'canwbe_unsubscribe_token', true) ? 'Yes' : 'No';
        $last_login = get_user_meta($subscriber->ID, 'last_login', true);
        $last_login = $last_login ? date('Y-m-d H:i:s', $last_login) : 'Never';

        fputcsv($output, array(
            $subscriber->ID,
            $subscriber->user_email,
            $subscriber->display_name,
            $subscriber->user_registered,
            $last_login,
            $has_token
        ));
    }

    fclose($output);
    exit;
}

/**
 * Handle CSV export request
 */
function canwbe_handle_export_request() {
    if (isset($_GET['action']) && $_GET['action'] === 'export_subscribers' &&
        isset($_GET['nonce']) && wp_verify_nonce($_GET['nonce'], 'canwbe_export_subscribers')) {
        canwbe_export_subscribers_csv();
    }
}
add_action('admin_init', 'canwbe_handle_export_request');

/**
 * Add subscriber count to admin dashboard
 */
function canwbe_add_dashboard_widget() {
    wp_add_dashboard_widget(
        'canwbe_subscriber_stats',
        __('Newsletter Subscribers', 'create-a-newsletter-with-the-block-editor'),
        'canwbe_dashboard_widget_content'
    );
}
add_action('wp_dashboard_setup', 'canwbe_add_dashboard_widget');

/**
 * Dashboard widget content
 */
function canwbe_dashboard_widget_content() {
    $subscriber_count = canwbe_get_subscribers_count();
    $recent_newsletters = get_posts(array(
        'post_type' => 'newsletter',
        'post_status' => 'publish',
        'numberposts' => 3,
        'orderby' => 'date',
        'order' => 'DESC'
    ));

    // Get batch statistics
    $active_batches = 0;
    $completed_batches = 0;

    if (class_exists('CANWBE_Batch_Email_Sender')) {
        $batches = CANWBE_Batch_Email_Sender::get_active_batches();
        foreach ($batches as $batch) {
            if (in_array($batch['status'], ['queued', 'processing'])) {
                $active_batches++;
            } elseif ($batch['status'] === 'completed') {
                $completed_batches++;
            }
        }
    }

    ?>
    <div class="activity-block">
        <h3><?php esc_html_e('Statistics', 'create-a-newsletter-with-the-block-editor'); ?></h3>
        <ul>
            <li>
                <strong><?php echo esc_html($subscriber_count); ?></strong>
                <?php esc_html_e('active subscribers', 'create-a-newsletter-with-the-block-editor'); ?>
            </li>
            <li>
                <strong><?php echo count($recent_newsletters); ?></strong>
                <?php esc_html_e('newsletters published', 'create-a-newsletter-with-the-block-editor'); ?>
            </li>
            <?php if (class_exists('CANWBE_Batch_Email_Sender')): ?>
                <li>
                    <strong><?php echo esc_html($active_batches); ?></strong>
                    <?php esc_html_e('batches sending', 'create-a-newsletter-with-the-block-editor'); ?>
                </li>
            <?php endif; ?>
        </ul>

        <?php if (!empty($recent_newsletters)): ?>
            <h3><?php esc_html_e('Recent Newsletters', 'create-a-newsletter-with-the-block-editor'); ?></h3>
            <ul>
                <?php foreach ($recent_newsletters as $newsletter): ?>
                    <li>
                        <a href="<?php echo esc_url(get_edit_post_link($newsletter->ID)); ?>">
                            <?php echo esc_html($newsletter->post_title); ?>
                        </a>
                        <span class="description"><?php echo esc_html(get_the_date('', $newsletter)); ?></span>

                        <?php
                        // Show batch status if available
                        $batch_id = get_post_meta($newsletter->ID, '_newsletter_batch_id', true);
                        if ($batch_id && class_exists('CANWBE_Batch_Email_Sender')) {
                            $batch_status = CANWBE_Batch_Email_Sender::get_batch_status($batch_id);
                            if ($batch_status) {
                                echo '<br><small style="color: #666;">' .
                                    sprintf(esc_html__('Sent to %d recipients', 'create-a-newsletter-with-the-block-editor'),
                                        $batch_status['sent_emails']) .
                                    '</small>';
                            }
                        }
                        ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <p class="community-events-footer">
            <a href="<?php echo esc_url(admin_url('edit.php?post_type=newsletter&page=canwbe-subscribers')); ?>" class="button">
                <?php esc_html_e('Manage Subscribers', 'create-a-newsletter-with-the-block-editor'); ?>
            </a>

            <a href="<?php echo esc_url(admin_url('post-new.php?post_type=newsletter')); ?>" class="button button-primary">
                <?php esc_html_e('Create Newsletter', 'create-a-newsletter-with-the-block-editor'); ?>
            </a>
        </p>

        <?php if (class_exists('CANWBE_Batch_Email_Sender') && $active_batches > 0): ?>
            <p>
                <a href="<?php echo esc_url(admin_url('edit.php?post_type=newsletter&page=canwbe-email-batches')); ?>" class="button button-secondary">
                    <?php esc_html_e('Monitor Email Batches', 'create-a-newsletter-with-the-block-editor'); ?>
                </a>
            </p>
        <?php endif; ?>
    </div>

    <style>
        #canwbe_subscriber_stats .activity-block ul {
            margin-left: 0;
        }
        #canwbe_subscriber_stats .activity-block li {
            margin-bottom: 8px;
        }
        #canwbe_subscriber_stats .description {
            color: #666;
            font-size: 12px;
            margin-left: 8px;
        }
        #canwbe_subscriber_stats .community-events-footer {
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 15px;
        }
        #canwbe_subscriber_stats .community-events-footer .button {
            margin-right: 5px;
            margin-bottom: 5px;
        }
    </style>
    <?php
}
