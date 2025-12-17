<?php
/**
 * Newsletter Subscription Form Block - FIXED VERSION
 *
 * Solución: Usar !important en los estilos inline para sobrescribir el CSS del tema
 */

if (!defined('ABSPATH')) {
    exit;
}

function canwbe_register_subscription_form_block() {
    wp_register_script(
        'canwbe-subscription-form-block',
        CANWBE_PLUGIN_URL . 'build/index.js',
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-compose'),
        CANWBE_VERSION
    );

    wp_register_style(
        'canwbe-subscription-form-block',
        CANWBE_PLUGIN_URL . 'assets/css/subscription-form-block.css',
        array(),
        CANWBE_VERSION
    );

    register_block_type('canwbe/subscription-form', array(
        'editor_script' => 'canwbe-subscription-form-block',
        'editor_style' => 'canwbe-subscription-form-block',
        'style' => 'canwbe-subscription-form-block',
        'render_callback' => 'canwbe_render_subscription_form_block',
        'attributes' => array(
            'title' => array('type' => 'string', 'default' => __('Subscribe to Newsletter', 'create-a-newsletter-with-the-block-editor')),
            'description' => array('type' => 'string', 'default' => __('Stay updated with our latest news and updates.', 'create-a-newsletter-with-the-block-editor')),
            'buttonText' => array('type' => 'string', 'default' => __('Subscribe', 'create-a-newsletter-with-the-block-editor')),
            'successMessage' => array('type' => 'string', 'default' => __('Thank you for subscribing!', 'create-a-newsletter-with-the-block-editor')),
            'placeholderEmail' => array('type' => 'string', 'default' => __('Your email address', 'create-a-newsletter-with-the-block-editor')),
            'placeholderName' => array('type' => 'string', 'default' => __('Your name (optional)', 'create-a-newsletter-with-the-block-editor')),
            'showNameField' => array('type' => 'boolean', 'default' => true),
            'alignment' => array('type' => 'string', 'default' => 'left'),
            'privacyText' => array('type' => 'string', 'default' => __('I accept the privacy policy', 'create-a-newsletter-with-the-block-editor')),
            'gdprText' => array('type' => 'string', 'default' => ''),
            'titleFontSize' => array('type' => 'string', 'default' => ''),
            'titleFontWeight' => array('type' => 'string', 'default' => '600'),
            'descriptionFontSize' => array('type' => 'string', 'default' => ''),
            'buttonFontSize' => array('type' => 'string', 'default' => ''),
            'buttonFontWeight' => array('type' => 'string', 'default' => '600'),
            'titleColor' => array('type' => 'string', 'default' => ''),
            'descriptionColor' => array('type' => 'string', 'default' => ''),
            'backgroundColor' => array('type' => 'string', 'default' => ''),
            'buttonBackgroundColor' => array('type' => 'string', 'default' => ''),
            'buttonTextColor' => array('type' => 'string', 'default' => ''),
            'inputBorderColor' => array('type' => 'string', 'default' => ''),
            'containerPadding' => array('type' => 'number', 'default' => 32),
            'borderRadius' => array('type' => 'number', 'default' => 8),
            'inputBorderRadius' => array('type' => 'number', 'default' => 4),
            'buttonBorderRadius' => array('type' => 'number', 'default' => 4)
        )
    ));
}
add_action('init', 'canwbe_register_subscription_form_block');

function canwbe_render_subscription_form_block($attributes) {
    $title = isset($attributes['title']) ? esc_html($attributes['title']) : '';
    $description = isset($attributes['description']) ? esc_html($attributes['description']) : '';
    $button_text = isset($attributes['buttonText']) ? esc_html($attributes['buttonText']) : __('Subscribe', 'create-a-newsletter-with-the-block-editor');
    $success_message = isset($attributes['successMessage']) ? esc_html($attributes['successMessage']) : __('Thank you for subscribing!', 'create-a-newsletter-with-the-block-editor');
    $placeholder_email = isset($attributes['placeholderEmail']) ? esc_attr($attributes['placeholderEmail']) : __('Your email address', 'create-a-newsletter-with-the-block-editor');
    $placeholder_name = isset($attributes['placeholderName']) ? esc_attr($attributes['placeholderName']) : __('Your name (optional)', 'create-a-newsletter-with-the-block-editor');
    $show_name_field = isset($attributes['showNameField']) ? $attributes['showNameField'] : true;
    $alignment = isset($attributes['alignment']) ? esc_attr($attributes['alignment']) : 'left';
    $privacy_text = isset($attributes['privacyText']) ? $attributes['privacyText'] : __('I accept the privacy policy', 'create-a-newsletter-with-the-block-editor');
    $gdpr_text = isset($attributes['gdprText']) ? $attributes['gdprText'] : '';

    $title_font_size = isset($attributes['titleFontSize']) ? esc_attr($attributes['titleFontSize']) : '';
    $title_font_weight = isset($attributes['titleFontWeight']) ? esc_attr($attributes['titleFontWeight']) : '600';
    $description_font_size = isset($attributes['descriptionFontSize']) ? esc_attr($attributes['descriptionFontSize']) : '';
    $button_font_size = isset($attributes['buttonFontSize']) ? esc_attr($attributes['buttonFontSize']) : '';
    $button_font_weight = isset($attributes['buttonFontWeight']) ? esc_attr($attributes['buttonFontWeight']) : '600';

    $title_color = isset($attributes['titleColor']) ? esc_attr($attributes['titleColor']) : '';
    $description_color = isset($attributes['descriptionColor']) ? esc_attr($attributes['descriptionColor']) : '';
    $background_color = isset($attributes['backgroundColor']) ? esc_attr($attributes['backgroundColor']) : '';
    $button_bg_color = isset($attributes['buttonBackgroundColor']) ? esc_attr($attributes['buttonBackgroundColor']) : '';
    $button_text_color = isset($attributes['buttonTextColor']) ? esc_attr($attributes['buttonTextColor']) : '';
    $input_border_color = isset($attributes['inputBorderColor']) ? esc_attr($attributes['inputBorderColor']) : '';

    $container_padding = isset($attributes['containerPadding']) ? intval($attributes['containerPadding']) : 32;
    $border_radius = isset($attributes['borderRadius']) ? intval($attributes['borderRadius']) : 8;
    $input_border_radius = isset($attributes['inputBorderRadius']) ? intval($attributes['inputBorderRadius']) : 4;
    $button_border_radius = isset($attributes['buttonBorderRadius']) ? intval($attributes['buttonBorderRadius']) : 4;

    $privacy_url = canwbe_get_privacy_policy_url();
    $form_id = 'canwbe-subscription-form-' . wp_rand(1000, 9999);

    // *** AQUÍ ESTÁ EL FIX: Añadir !important a los estilos ***
    $container_style_parts = array();
    $container_style_parts[] = 'text-align: ' . $alignment . ' !important;';
    if ($background_color) {
        $container_style_parts[] = 'background-color: ' . $background_color . ' !important;';
        $container_style_parts[] = 'background: ' . $background_color . ' !important;';
    }
    if ($container_padding) $container_style_parts[] = 'padding: ' . $container_padding . 'px !important;';
    if ($border_radius) $container_style_parts[] = 'border-radius: ' . $border_radius . 'px !important;';
    $container_style = implode(' ', $container_style_parts);

    $title_style_parts = array();
    if ($title_color) $title_style_parts[] = 'color: ' . $title_color . ' !important;';
    if ($title_font_size) $title_style_parts[] = 'font-size: ' . $title_font_size . ' !important;';
    if ($title_font_weight) $title_style_parts[] = 'font-weight: ' . $title_font_weight . ' !important;';
    $title_style = implode(' ', $title_style_parts);

    $description_style_parts = array();
    if ($description_color) $description_style_parts[] = 'color: ' . $description_color . ' !important;';
    if ($description_font_size) $description_style_parts[] = 'font-size: ' . $description_font_size . ' !important;';
    $description_style = implode(' ', $description_style_parts);

    $input_style_parts = array();
    if ($input_border_color) $input_style_parts[] = 'border-color: ' . $input_border_color . ' !important;';
    if ($input_border_radius) $input_style_parts[] = 'border-radius: ' . $input_border_radius . 'px !important;';
    $input_style = implode(' ', $input_style_parts);

    $button_style_parts = array();
    if ($button_bg_color) {
        $button_style_parts[] = 'background-color: ' . $button_bg_color . ' !important;';
        $button_style_parts[] = 'background: ' . $button_bg_color . ' !important;';
    }
    if ($button_text_color) $button_style_parts[] = 'color: ' . $button_text_color . ' !important;';
    if ($button_font_size) $button_style_parts[] = 'font-size: ' . $button_font_size . ' !important;';
    if ($button_font_weight) $button_style_parts[] = 'font-weight: ' . $button_font_weight . ' !important;';
    if ($button_border_radius) $button_style_parts[] = 'border-radius: ' . $button_border_radius . 'px !important;';
    $button_style = implode(' ', $button_style_parts);

    ob_start();
    ?>
    <div class="canwbe-subscription-form-wrapper">
        <div class="canwbe-subscription-form-container" style="<?php echo esc_attr($container_style); ?>">
            <?php if (!empty($title)): ?>
                <h3 class="canwbe-subscription-form-title" style="<?php echo esc_attr($title_style); ?>">
                    <?php echo $title; ?>
                </h3>
            <?php endif; ?>

            <?php if (!empty($description)): ?>
                <p class="canwbe-subscription-form-description" style="<?php echo esc_attr($description_style); ?>">
                    <?php echo $description; ?>
                </p>
            <?php endif; ?>

            <form id="<?php echo esc_attr($form_id); ?>" class="canwbe-subscription-form" method="post">
                <?php wp_nonce_field('canwbe_subscription_form', 'canwbe_nonce'); ?>
                <input type="hidden" name="action" value="canwbe_subscribe">

                <div class="canwbe-form-fields">
                    <?php if ($show_name_field): ?>
                        <div class="canwbe-form-field">
                            <input type="text" name="subscriber_name" placeholder="<?php echo $placeholder_name; ?>"
                                   class="canwbe-form-input canwbe-name-input" style="<?php echo esc_attr($input_style); ?>">
                        </div>
                    <?php endif; ?>

                    <div class="canwbe-form-field">
                        <input type="email" name="subscriber_email" placeholder="<?php echo $placeholder_email; ?>"
                               class="canwbe-form-input canwbe-email-input" style="<?php echo esc_attr($input_style); ?>" required>
                    </div>

                    <div class="canwbe-form-field canwbe-privacy-field">
                        <label class="canwbe-privacy-label">
                            <input type="checkbox" name="privacy_accepted" class="canwbe-privacy-checkbox" required>
                            <span class="canwbe-privacy-text">
                                <?php if ($privacy_url): ?>
                                    <?php echo str_replace(
                                        __('privacy policy', 'create-a-newsletter-with-the-block-editor'),
                                        '<a href="' . esc_url($privacy_url) . '" target="_blank">' . __('privacy policy', 'create-a-newsletter-with-the-block-editor') . '</a>',
                                        esc_html($privacy_text)
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
                        <button type="submit" class="canwbe-form-button" style="<?php echo esc_attr($button_style); ?>">
                            <?php echo $button_text; ?>
                        </button>
                    </div>
                </div>

                <div class="canwbe-form-messages">
                    <div class="canwbe-success-message" style="display: none;"><?php echo $success_message; ?></div>
                    <div class="canwbe-error-message" style="display: none;"></div>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function() {
            const form = document.getElementById('<?php echo esc_js($form_id); ?>');
            if (!form) return;
            const customSuccessMessage = <?php echo json_encode($success_message); ?>;

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const button = form.querySelector('.canwbe-form-button');
                const successMessage = form.querySelector('.canwbe-success-message');
                const errorMessage = form.querySelector('.canwbe-error-message');
                const originalButtonText = button.textContent;

                successMessage.style.display = 'none';
                errorMessage.style.display = 'none';

                const privacyCheckbox = form.querySelector('.canwbe-privacy-checkbox');
                if (privacyCheckbox && !privacyCheckbox.checked) {
                    errorMessage.style.display = 'block';
                    errorMessage.textContent = <?php echo json_encode(__('You must accept the privacy policy to subscribe.', 'create-a-newsletter-with-the-block-editor')); ?>;
                    return;
                }

                button.textContent = <?php echo json_encode(__('Subscribing...', 'create-a-newsletter-with-the-block-editor')); ?>;
                button.disabled = true;

                const formData = new FormData(form);
                formData.append('action', 'canwbe_ajax_subscribe');

                fetch(<?php echo json_encode(admin_url('admin-ajax.php')); ?>, {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            form.reset();
                            successMessage.style.display = 'block';
                            successMessage.textContent = data.data.message || customSuccessMessage;
                        } else {
                            errorMessage.style.display = 'block';
                            errorMessage.textContent = data.data.message || <?php echo json_encode(__('An error occurred. Please try again.', 'create-a-newsletter-with-the-block-editor')); ?>;
                        }
                    })
                    .catch(error => {
                        errorMessage.style.display = 'block';
                        errorMessage.textContent = <?php echo json_encode(__('An error occurred. Please try again.', 'create-a-newsletter-with-the-block-editor')); ?>;
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

function canwbe_get_privacy_policy_url() {
    $privacy_url = get_option('canwbe_privacy_policy_url');
    if (empty($privacy_url)) {
        $privacy_policy_page_id = get_option('wp_page_for_privacy_policy');
        if ($privacy_policy_page_id) {
            $privacy_url = get_permalink($privacy_policy_page_id);
        }
    }
    return $privacy_url;
}

function canwbe_handle_ajax_subscription() {
    if (!isset($_POST['canwbe_nonce']) || !wp_verify_nonce($_POST['canwbe_nonce'], 'canwbe_subscription_form')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'create-a-newsletter-with-the-block-editor')));
    }

    $email = isset($_POST['subscriber_email']) ? sanitize_email($_POST['subscriber_email']) : '';
    $name = isset($_POST['subscriber_name']) ? sanitize_text_field($_POST['subscriber_name']) : '';
    $privacy_accepted = isset($_POST['privacy_accepted']) && $_POST['privacy_accepted'];

    if (!is_email($email)) {
        wp_send_json_error(array('message' => __('Please enter a valid email address.', 'create-a-newsletter-with-the-block-editor')));
    }

    if (!$privacy_accepted) {
        wp_send_json_error(array('message' => __('You must accept the privacy policy to subscribe.', 'create-a-newsletter-with-the-block-editor')));
    }

    if (email_exists($email)) {
        $existing_user = get_user_by('email', $email);
        if ($existing_user && canwbe_is_newsletter_subscriber($existing_user->ID)) {
            wp_send_json_error(array('message' => __('You are already subscribed to our newsletter.', 'create-a-newsletter-with-the-block-editor')));
        } else if ($existing_user) {
            $existing_user->add_role('newsletter_subscriber');
            canwbe_generate_unsubscribe_token($existing_user->ID);
            update_user_meta($existing_user->ID, 'canwbe_privacy_accepted', current_time('mysql'));
            wp_send_json_success(array('message' => __('Thank you! You have been subscribed to our newsletter.', 'create-a-newsletter-with-the-block-editor')));
        }
    }

    $user_data = array(
        'user_login' => $email,
        'user_email' => $email,
        'display_name' => !empty($name) ? $name : $email,
        'role' => 'newsletter_subscriber'
    );

    $user_id = wp_insert_user($user_data);

    if (is_wp_error($user_id)) {
        wp_send_json_error(array('message' => __('An error occurred while subscribing. Please try again.', 'create-a-newsletter-with-the-block-editor')));
    }

    canwbe_generate_unsubscribe_token($user_id);
    update_user_meta($user_id, 'canwbe_privacy_accepted', current_time('mysql'));
    canwbe_log('New subscription via form block', array('user_id' => $user_id, 'email' => $email, 'name' => $name, 'privacy_accepted' => true));

    wp_send_json_success(array('message' => __('Thank you! You have been subscribed to our newsletter.', 'create-a-newsletter-with-the-block-editor')));
}
add_action('wp_ajax_canwbe_ajax_subscribe', 'canwbe_handle_ajax_subscription');
add_action('wp_ajax_nopriv_canwbe_ajax_subscribe', 'canwbe_handle_ajax_subscription');

function canwbe_enqueue_subscription_form_assets() {
    if (has_block('canwbe/subscription-form')) {
        wp_enqueue_style('canwbe-subscription-form-frontend', CANWBE_PLUGIN_URL . 'assets/css/subscription-form-block.css', array(), CANWBE_VERSION);
    }
}
add_action('wp_enqueue_scripts', 'canwbe_enqueue_subscription_form_assets');
