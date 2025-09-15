import { useState, useEffect } from '@wordpress/element';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { TextareaControl, TextControl, FormTokenField } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useEntityProp } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';

const NewsletterMetaFields = () => {
    // Get the current post type
    const postType = useSelect((select) => {
        return select('core/editor').getCurrentPostType();
    }, []);

    // Only show for newsletter post type
    if (postType !== 'newsletter') {
        return null;
    }

    // Get meta data
    const [meta, setMeta] = useEntityProp('postType', postType, 'meta');
    const [isReady, setIsReady] = useState(false);

    // Wait for meta to be available
    useEffect(() => {
        if (meta) {
            setIsReady(true);
        }
    }, [meta]);

    if (!isReady) {
        return null;
    }

    // Extract meta values with defaults
    const {
        canwbe_intro_message: introMessage = '',
        canwbe_unsubscribe_message: unsubscribeMessage = '',
        canwbe_recipient_roles: recipientRoles = []
    } = meta;

    // Update meta function
    const updateMeta = (key, value) => {
        setMeta({
            ...meta,
            [key]: value
        });
    };

    // Get roles from localized script
    const availableRoles = window.canwbeRoles || [];
    const roleSuggestions = availableRoles.map(role => role.label);

    // Convert role values to labels for display
    const selectedRoleLabels = availableRoles
        .filter(role => recipientRoles.includes(role.value))
        .map(role => role.label);

    // Get translatable help text from config
    const helpTextRoles = window.canwbeConfig?.i18n?.helpTextRoles || __('Separate with commas or Enter key.', 'create-a-newsletter-with-the-block-editor');

    return (
        <PluginDocumentSettingPanel
            name="canwbe-meta-fields-panel"
            title={__('Newsletter Settings', 'create-a-newsletter-with-the-block-editor')}
            className="canwbe-meta-fields-panel"
        >
            <TextareaControl
                label={__('Intro Message', 'create-a-newsletter-with-the-block-editor')}
                value={introMessage}
                onChange={(value) => updateMeta('canwbe_intro_message', value)}
                help={__('Message that appears at the beginning of the newsletter. Leave blank for no message.', 'create-a-newsletter-with-the-block-editor')}
            />

            <TextControl
                label={__('Unsubscribe Message', 'create-a-newsletter-with-the-block-editor')}
                value={unsubscribeMessage}
                onChange={(value) => updateMeta('canwbe_unsubscribe_message', value)}
                help={__('Message for the unsubscribe link.', 'create-a-newsletter-with-the-block-editor')}
            />

            <FormTokenField
                label={__('Recipient Roles', 'create-a-newsletter-with-the-block-editor')}
                value={selectedRoleLabels}
                suggestions={roleSuggestions}
                onChange={(selectedLabels) => {
                    // Convert labels back to values
                    const selectedValues = availableRoles
                        .filter(role => selectedLabels.includes(role.label))
                        .map(role => role.value);

                    updateMeta('canwbe_recipient_roles', selectedValues);
                }}
                help={helpTextRoles}
            />
        </PluginDocumentSettingPanel>
    );
};

export default NewsletterMetaFields;
