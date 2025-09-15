<?php
/**
 * Newsletter Subscription Form Block
 *
 * Provides a Gutenberg block for newsletter subscription with GDPR compliance
 *
 * @package Create_A_Newsletter_With_The_Block_Editor
 * @since 1.4
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register the subscription form block
 */
function canwbe_register_subscription_form_block() {
    // Register block script
    wp_register_script(
        'canwbe-subscription-form-block',
        CANWBE_PLUGIN_URL . 'build/index.js',
        array(
            'wp-blocks',
            'wp-element',
            'wp-editor',
            'wp-components',
            'wp-i18n'
        ),
        CANWBE_VERSION
    );

    // Register block style
    wp_register_style(
        'canwbe-subscription-form-block',
        CANWBE_PLUGIN_URL . 'assets/css/subscription-form-block.css',
        array(),
        CANWBE_VERSION
    );

    // Register the block
    register_block_type('canwbe/subscription-form', array(
        'editor_script' => 'canwbe-subscription-form-block',
        'editor_style' => 'canwbe-subscription-form-block',
        'style' => 'canwbe-subscription-form-block',
        'render_callback' => 'canwbe_render_subscription_form_block',
        'attributes' => array(
            'title' => array(
                'type' => 'string',
                'default' => __('Subscribe to Newsletter', 'create-a-newsletter-with-the-block-editor')
            ),
            'description' => array(
                'type' => 'string',
                'default' => __('Stay updated with our latest news and updates.', 'create-a-newsletter-with-the-block-editor')
            ),
            'buttonText' => array(
                'type' => 'string',
                'default' => __('Subscribe', 'create-a-newsletter-with-the-block-editor')
            ),
            'successMessage' => array(
                'type' => 'string',
                'default' => __('Thank you for subscribing!', 'create-a-newsletter-with-the-block-editor')
            ),
            'placeholderEmail' => array(
                'type' => 'string',
                'default' => __('Your email address', 'create-a-newsletter-with-the-block-editor')
            ),
            'placeholderName' => array(
                'type' => 'string',
                'default' => __('Your name (optional)', 'create-a-newsletter-with-the-block-editor')
            ),
            'showNameField' => array(
                'type' => 'boolean',
                'default' => true
            ),
            'alignment' => array(
                'type' => 'string',
                'default' => 'left'
            ),
            'privacyText' => array(
                'type' => 'string',
                'default' => __('I accept the privacy policy', 'create-a-newsletter-with-the-block-editor')
            ),
            'gdprText' => array(
                'type' => 'string',
                'default' => __('Data Controller: Website Name. Purpose: To send you a weekly newsletter via email. Legal basis: Your consent. Recipients: Your hosting provider. Rights: Access, rectification, limitation and deletion of your data if you request it. We will use your email address solely to send you the newsletters from this subscription.', 'create-a-newsletter-with-the-block-editor')
            )
        )
    ));
}
add_action('init', 'canwbe_register_subscription_form_block');

/**
 * Render the subscription form block
 */
function canwbe_render_subscription_form_block($attributes) {
    $title = esc_html($attributes['title']);
    $description = esc_html($attributes['description']);
    $button_text = esc_html($attributes['buttonText']);
    $success_message = esc_html($attributes['successMessage']);
    $placeholder_email = esc_attr($attributes['placeholderEmail']);
    $placeholder_name = esc_attr($attributes['placeholderName']);
    $show_name_field = $attributes['showNameField'];
    $alignment = esc_attr($attributes['alignment']);
    $privacy_text = $attributes['privacyText'];
    $gdpr_text = $attributes['gdprText'];

    // Get privacy policy URL from settings or WordPress default
    $privacy_url = canwbe_get_privacy_policy_url();

    $form_id = 'canwbe-subscription-form-' . wp_rand(1000, 9999);

    ob_start();
    ?>
    <div class="canwbe-subscription-form-wrapper" style="text-align: <?php echo $alignment; ?>;">
        <div class="canwbe-subscription-form-container">
            <?php if (!empty($title)): ?>
                <h3 class="canwbe-subscription-form-title"><?php echo $title; ?></h3>
            <?php endif; ?>

            <?php if (!empty($description)): ?>
                <p class="canwbe-subscription-form-description"><?php echo $description; ?></p>
            <?php endif; ?>

            <form id="<?php echo $form_id; ?>" class="canwbe-subscription-form" method="post">
                <?php wp_nonce_field('canwbe_subscription_form', 'canwbe_nonce'); ?>
                <input type="hidden" name="action" value="canwbe_subscribe">

                <div class="canwbe-form-fields">
                    <?php if ($show_name_field): ?>
                        <div class="canwbe-form-field">
                            <input
                                type="text"
                                name="subscriber_name"
                                placeholder="<?php echo $placeholder_name; ?>"
                                class="canwbe-form-input canwbe-name-input"
                            >
                        </div>
                    <?php endif; ?>

                    <div class="canwbe-form-field">
                        <input
                            type="email"
                            name="subscriber_email"
                            placeholder="<?php echo $placeholder_email; ?>"
                            class="canwbe-form-input canwbe-email-input"
                            required
                        >
                    </div>

                    <div class="canwbe-form-field canwbe-privacy-field">
                        <label class="canwbe-privacy-label">
                            <input
                                type="checkbox"
                                name="privacy_accepted"
                                class="canwbe-privacy-checkbox"
                                required
                            >
                            <span class="canwbe-privacy-text">
                                <?php if ($privacy_url): ?>
                                    <?php echo str_replace(
                                        __('privacy policy', 'create-a-newsletter-with-the-block-editor'),
                                        '<a href="' . esc_url($privacy_url) . '" target="_blank">' . __('privacy policy', 'create-a-newsletter-with-the-block-editor') . '</a>',
                                        $privacy_text
                                    ); ?>
                                <?php else: ?>
                                    <?php echo esc_html($privacy_text); ?>
                                <?php endif; ?>
                            </span>
                        </label>
                    </div>

                    <?php if (!empty($gdpr_text)): ?>
                        <div class="canwbe-form-field canwbe-gdpr-field">
                            <p class="canwbe-gdpr-text"><?php echo wp_kses_post($gdpr_text); ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="canwbe-form-field">
                        <button type="submit" class="canwbe-form-button">
                            <?php echo $button_text; ?>
                        </button>
                    </div>
                </div>

                <div class="canwbe-form-messages">
                    <div class="canwbe-success-message" style="display: none;">
                        <?php echo $success_message; ?>
                    </div>
                    <div class="canwbe-error-message" style="display: none;"></div>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function() {
            const form = document.getElementById('<?php echo $form_id; ?>');
            if (!form) return;

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const button = form.querySelector('.canwbe-form-button');
                const successMessage = form.querySelector('.canwbe-success-message');
                const errorMessage = form.querySelector('.canwbe-error-message');
                const originalButtonText = button.textContent;

                // Reset messages
                successMessage.style.display = 'none';
                errorMessage.style.display = 'none';

                // Validate privacy checkbox
                const privacyCheckbox = form.querySelector('.canwbe-privacy-checkbox');
                if (privacyCheckbox && !privacyCheckbox.checked) {
                    errorMessage.style.display = 'block';
                    errorMessage.textContent = '<?php echo esc_js(__('You must accept the privacy policy to subscribe.', 'create-a-newsletter-with-the-block-editor')); ?>';
                    return;
                }

                // Show loading state
                button.textContent = '<?php echo esc_js(__('Subscribing...', 'create-a-newsletter-with-the-block-editor')); ?>';
                button.disabled = true;

                // Prepare form data
                const formData = new FormData(form);
                formData.append('action', 'canwbe_ajax_subscribe');

                // Send AJAX request
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            form.reset();
                            successMessage.style.display = 'block';
                            successMessage.textContent = data.data.message || '<?php echo esc_js($success_message); ?>';
                        } else {
                            errorMessage.style.display = 'block';
                            errorMessage.textContent = data.data.message || '<?php echo esc_js(__('An error occurred. Please try again.', 'create-a-newsletter-with-the-block-editor')); ?>';
                        }
                    })
                    .catch(error => {
                        errorMessage.style.display = 'block';
                        errorMessage.textContent = '<?php echo esc_js(__('An error occurred. Please try again.', 'create-a-newsletter-with-the-block-editor')); ?>';
                    })
                    .finally(() => {
                        button.textContent = originalButtonText;
                        button.disabled = false;
                    });
            });
        })();
    </script>
    <?php

    return ob_get_clean();
}

/**
 * Get privacy policy URL
 */
function canwbe_get_privacy_policy_url() {
    // First check plugin settings
    $privacy_url = get_option('canwbe_privacy_policy_url');

    // If not set, use WordPress default privacy policy page
    if (empty($privacy_url)) {
        $privacy_policy_page_id = get_option('wp_page_for_privacy_policy');
        if ($privacy_policy_page_id) {
            $privacy_url = get_permalink($privacy_policy_page_id);
        }
    }

    return $privacy_url;
}

/**
 * Handle AJAX subscription
 */
function canwbe_handle_ajax_subscription() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['canwbe_nonce'], 'canwbe_subscription_form')) {
        wp_send_json_error(array(
            'message' => __('Security check failed.', 'create-a-newsletter-with-the-block-editor')
        ));
    }

    $email = sanitize_email($_POST['subscriber_email']);
    $name = sanitize_text_field($_POST['subscriber_name']);
    $privacy_accepted = isset($_POST['privacy_accepted']) && $_POST['privacy_accepted'];

    // Validate required fields
    if (!is_email($email)) {
        wp_send_json_error(array(
            'message' => __('Please enter a valid email address.', 'create-a-newsletter-with-the-block-editor')
        ));
    }

    if (!$privacy_accepted) {
        wp_send_json_error(array(
            'message' => __('You must accept the privacy policy to subscribe.', 'create-a-newsletter-with-the-block-editor')
        ));
    }

    // Check if user already exists
    if (email_exists($email)) {
        $existing_user = get_user_by('email', $email);
        if ($existing_user && canwbe_is_newsletter_subscriber($existing_user->ID)) {
            wp_send_json_error(array(
                'message' => __('You are already subscribed to our newsletter.', 'create-a-newsletter-with-the-block-editor')
            ));
        } else if ($existing_user) {
            // Add newsletter subscriber role to existing user
            $existing_user->add_role('newsletter_subscriber');
            canwbe_generate_unsubscribe_token($existing_user->ID);

            // Store privacy acceptance
            update_user_meta($existing_user->ID, 'canwbe_privacy_accepted', current_time('mysql'));

            wp_send_json_success(array(
                'message' => __('Thank you! You have been subscribed to our newsletter.', 'create-a-newsletter-with-the-block-editor')
            ));
        }
    }

    // Create new user
    $user_data = array(
        'user_login' => $email,
        'user_email' => $email,
        'display_name' => !empty($name) ? $name : $email,
        'role' => 'newsletter_subscriber'
    );

    $user_id = wp_insert_user($user_data);

    if (is_wp_error($user_id)) {
        wp_send_json_error(array(
            'message' => __('An error occurred while subscribing. Please try again.', 'create-a-newsletter-with-the-block-editor')
        ));
    }

    // Generate unsubscribe token
    canwbe_generate_unsubscribe_token($user_id);

    // Store privacy acceptance
    update_user_meta($user_id, 'canwbe_privacy_accepted', current_time('mysql'));

    // Log subscription
    canwbe_log('New subscription via form block', array(
        'user_id' => $user_id,
        'email' => $email,
        'name' => $name,
        'privacy_accepted' => true
    ));

    wp_send_json_success(array(
        'message' => __('Thank you! You have been subscribed to our newsletter.', 'create-a-newsletter-with-the-block-editor')
    ));
}
add_action('wp_ajax_canwbe_ajax_subscribe', 'canwbe_handle_ajax_subscription');
add_action('wp_ajax_nopriv_canwbe_ajax_subscribe', 'canwbe_handle_ajax_subscription');

/**
 * Enqueue block assets for frontend
 */
function canwbe_enqueue_subscription_form_assets() {
    if (has_block('canwbe/subscription-form')) {
        wp_enqueue_style(
            'canwbe-subscription-form-frontend',
            CANWBE_PLUGIN_URL . 'assets/css/subscription-form-block.css',
            array(),
            CANWBE_VERSION
        );
    }
}
add_action('wp_enqueue_scripts', 'canwbe_enqueue_subscription_form_assets');

/**
 * Create CSS file for subscription form block
 */
function canwbe_create_subscription_form_css() {
    $css_dir = CANWBE_PLUGIN_PATH . 'assets/css/';
    $css_file = $css_dir . 'subscription-form-block.css';

    // Create directory if it doesn't exist
    if (!file_exists($css_dir)) {
        wp_mkdir_p($css_dir);
    }

    // Create CSS file if it doesn't exist
    if (!file_exists($css_file)) {
        $css_content = '
.canwbe-subscription-form-wrapper {
    margin: 1.5em 0;
}

.canwbe-subscription-form-container {
    background: #f9f9f9;
    padding: 2em;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
    max-width: 500px;
    margin: 0 auto;
}

.canwbe-subscription-form-title {
    margin: 0 0 1em 0;
    font-size: 1.5em;
    font-weight: 600;
    color: #333;
}

.canwbe-subscription-form-description {
    margin: 0 0 1.5em 0;
    color: #666;
    line-height: 1.5;
}

.canwbe-form-fields {
    display: flex;
    flex-direction: column;
    gap: 1em;
}

.canwbe-form-field {
    margin: 0;
}

.canwbe-form-input {
    width: 100%;
    padding: 0.75em;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1em;
    box-sizing: border-box;
}

.canwbe-form-input:focus {
    outline: none;
    border-color: #0073aa;
    box-shadow: 0 0 0 2px rgba(0, 115, 170, 0.1);
}

.canwbe-form-button {
    width: 100%;
    padding: 0.75em 1.5em;
    background: #0073aa;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 1em;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.canwbe-form-button:hover {
    background: #005a87;
}

.canwbe-form-button:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.canwbe-privacy-field {
    margin: 1em 0;
}

.canwbe-privacy-label {
    display: flex;
    align-items: flex-start;
    gap: 0.5em;
    font-size: 0.9em;
    line-height: 1.4;
    cursor: pointer;
}

.canwbe-privacy-checkbox {
    margin: 0;
    margin-top: 0.2em;
    flex-shrink: 0;
}

.canwbe-privacy-text a {
    color: #0073aa;
    text-decoration: underline;
}

.canwbe-privacy-text a:hover {
    text-decoration: none;
}

.canwbe-gdpr-field {
    margin: 1em 0;
}

.canwbe-gdpr-text {
    font-size: 0.8em;
    color: #666;
    line-height: 1.4;
    margin: 0;
    padding: 0.75em;
    background: #f5f5f5;
    border-radius: 4px;
    border-left: 3px solid #0073aa;
}

.canwbe-form-messages {
    margin-top: 1em;
}

.canwbe-success-message {
    padding: 0.75em;
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
    border-radius: 4px;
    margin-bottom: 1em;
}

.canwbe-error-message {
    padding: 0.75em;
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
    border-radius: 4px;
    margin-bottom: 1em;
}

/* Responsive design */
@media (min-width: 768px) {
    .canwbe-form-fields {
        flex-direction: row;
        align-items: flex-end;
        flex-wrap: wrap;
    }
    
    .canwbe-form-field:not(.canwbe-privacy-field):not(.canwbe-gdpr-field):not(:last-child) {
        flex: 1;
        min-width: 200px;
    }
    
    .canwbe-form-field:last-child {
        flex-shrink: 0;
        margin-left: 1em;
    }
    
    .canwbe-privacy-field,
    .canwbe-gdpr-field {
        flex-basis: 100%;
        order: 10;
    }
    
    .canwbe-form-button {
        width: auto;
        white-space: nowrap;
    }
}

/* Editor styles */
.editor-styles-wrapper .canwbe-subscription-form-container {
    background: #f0f0f0;
    border: 2px dashed #ccc;
}

.editor-styles-wrapper .canwbe-subscription-form-title {
    margin-top: 0;
}
        ';

        file_put_contents($css_file, $css_content);
    }
}
add_action('init', 'canwbe_create_subscription_form_css');
