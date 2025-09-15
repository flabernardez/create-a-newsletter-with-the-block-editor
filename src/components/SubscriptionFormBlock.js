import { registerBlockType } from '@wordpress/blocks';
import {
    TextControl,
    TextareaControl,
    ToggleControl,
    SelectControl,
    PanelBody
} from '@wordpress/components';
import { InspectorControls } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

registerBlockType('canwbe/subscription-form', {
    title: __('Newsletter Subscription Form', 'create-a-newsletter-with-the-block-editor'),
    description: __('A GDPR-compliant form for users to subscribe to your newsletter.', 'create-a-newsletter-with-the-block-editor'),
    category: 'widgets',
    icon: 'email-alt',
    keywords: [
        __('newsletter', 'create-a-newsletter-with-the-block-editor'),
        __('subscription', 'create-a-newsletter-with-the-block-editor'),
        __('email', 'create-a-newsletter-with-the-block-editor'),
        __('form', 'create-a-newsletter-with-the-block-editor'),
        __('gdpr', 'create-a-newsletter-with-the-block-editor'),
        __('privacy', 'create-a-newsletter-with-the-block-editor')
    ],
    attributes: {
        title: {
            type: 'string',
            default: __('Subscribe to Newsletter', 'create-a-newsletter-with-the-block-editor')
        },
        description: {
            type: 'string',
            default: __('Stay updated with our latest news and updates.', 'create-a-newsletter-with-the-block-editor')
        },
        buttonText: {
            type: 'string',
            default: __('Subscribe', 'create-a-newsletter-with-the-block-editor')
        },
        successMessage: {
            type: 'string',
            default: __('Thank you for subscribing!', 'create-a-newsletter-with-the-block-editor')
        },
        placeholderEmail: {
            type: 'string',
            default: __('Your email address', 'create-a-newsletter-with-the-block-editor')
        },
        placeholderName: {
            type: 'string',
            default: __('Your name (optional)', 'create-a-newsletter-with-the-block-editor')
        },
        showNameField: {
            type: 'boolean',
            default: true
        },
        alignment: {
            type: 'string',
            default: 'left'
        },
        privacyText: {
            type: 'string',
            default: __('I accept the privacy policy', 'create-a-newsletter-with-the-block-editor')
        },
        gdprText: {
            type: 'string',
            default: __('Data Controller: Website Name. Purpose: To send you a weekly newsletter via email. Legal basis: Your consent. Recipients: Your hosting provider. Rights: Access, rectification, limitation and deletion of your data if you request it. We will use your email address solely to send you the newsletters from this subscription.', 'create-a-newsletter-with-the-block-editor')
        }
    },

    edit: function(props) {
        const { attributes, setAttributes } = props;
        const {
            title,
            description,
            buttonText,
            successMessage,
            placeholderEmail,
            placeholderName,
            showNameField,
            alignment,
            privacyText,
            gdprText
        } = attributes;

        return (
            <>
                <InspectorControls>
                    <PanelBody
                        title={__('Form Settings', 'create-a-newsletter-with-the-block-editor')}
                        initialOpen={true}
                    >
                        <TextControl
                            label={__('Form Title', 'create-a-newsletter-with-the-block-editor')}
                            value={title}
                            onChange={(value) => setAttributes({ title: value })}
                        />

                        <TextareaControl
                            label={__('Description', 'create-a-newsletter-with-the-block-editor')}
                            value={description}
                            onChange={(value) => setAttributes({ description: value })}
                            help={__('Brief description about your newsletter', 'create-a-newsletter-with-the-block-editor')}
                        />

                        <SelectControl
                            label={__('Alignment', 'create-a-newsletter-with-the-block-editor')}
                            value={alignment}
                            options={[
                                { label: __('Left', 'create-a-newsletter-with-the-block-editor'), value: 'left' },
                                { label: __('Center', 'create-a-newsletter-with-the-block-editor'), value: 'center' },
                                { label: __('Right', 'create-a-newsletter-with-the-block-editor'), value: 'right' }
                            ]}
                            onChange={(value) => setAttributes({ alignment: value })}
                        />
                    </PanelBody>

                    <PanelBody
                        title={__('Field Settings', 'create-a-newsletter-with-the-block-editor')}
                        initialOpen={false}
                    >
                        <ToggleControl
                            label={__('Show Name Field', 'create-a-newsletter-with-the-block-editor')}
                            checked={showNameField}
                            onChange={(value) => setAttributes({ showNameField: value })}
                            help={__('Allow users to enter their name along with email', 'create-a-newsletter-with-the-block-editor')}
                        />

                        <TextControl
                            label={__('Email Placeholder', 'create-a-newsletter-with-the-block-editor')}
                            value={placeholderEmail}
                            onChange={(value) => setAttributes({ placeholderEmail: value })}
                        />

                        {showNameField && (
                            <TextControl
                                label={__('Name Placeholder', 'create-a-newsletter-with-the-block-editor')}
                                value={placeholderName}
                                onChange={(value) => setAttributes({ placeholderName: value })}
                            />
                        )}

                        <TextControl
                            label={__('Button Text', 'create-a-newsletter-with-the-block-editor')}
                            value={buttonText}
                            onChange={(value) => setAttributes({ buttonText: value })}
                        />
                    </PanelBody>

                    <PanelBody
                        title={__('Privacy & GDPR', 'create-a-newsletter-with-the-block-editor')}
                        initialOpen={false}
                    >
                        <TextControl
                            label={__('Privacy Checkbox Text', 'create-a-newsletter-with-the-block-editor')}
                            value={privacyText}
                            onChange={(value) => setAttributes({ privacyText: value })}
                            help={__('Text for the privacy policy acceptance checkbox', 'create-a-newsletter-with-the-block-editor')}
                        />

                        <TextareaControl
                            label={__('GDPR Information Text', 'create-a-newsletter-with-the-block-editor')}
                            value={gdprText}
                            onChange={(value) => setAttributes({ gdprText: value })}
                            help={__('Legal information about data processing (GDPR compliance)', 'create-a-newsletter-with-the-block-editor')}
                            rows={5}
                        />
                    </PanelBody>

                    <PanelBody
                        title={__('Messages', 'create-a-newsletter-with-the-block-editor')}
                        initialOpen={false}
                    >
                        <TextControl
                            label={__('Success Message', 'create-a-newsletter-with-the-block-editor')}
                            value={successMessage}
                            onChange={(value) => setAttributes({ successMessage: value })}
                            help={__('Message shown after successful subscription', 'create-a-newsletter-with-the-block-editor')}
                        />
                    </PanelBody>
                </InspectorControls>

                <div className="canwbe-subscription-form-wrapper" style={{ textAlign: alignment }}>
                    <div className="canwbe-subscription-form-container">
                        {title && (
                            <h3 className="canwbe-subscription-form-title">{title}</h3>
                        )}

                        {description && (
                            <p className="canwbe-subscription-form-description">{description}</p>
                        )}

                        <div className="canwbe-form-fields">
                            {showNameField && (
                                <div className="canwbe-form-field">
                                    <input
                                        type="text"
                                        placeholder={placeholderName}
                                        className="canwbe-form-input canwbe-name-input"
                                        disabled
                                    />
                                </div>
                            )}

                            <div className="canwbe-form-field">
                                <input
                                    type="email"
                                    placeholder={placeholderEmail}
                                    className="canwbe-form-input canwbe-email-input"
                                    disabled
                                />
                            </div>

                            <div className="canwbe-form-field canwbe-privacy-field">
                                <label className="canwbe-privacy-label">
                                    <input
                                        type="checkbox"
                                        className="canwbe-privacy-checkbox"
                                        disabled
                                    />
                                    <span className="canwbe-privacy-text">{privacyText}</span>
                                </label>
                            </div>

                            {gdprText && (
                                <div className="canwbe-form-field canwbe-gdpr-field">
                                    <p className="canwbe-gdpr-text">{gdprText}</p>
                                </div>
                            )}

                            <div className="canwbe-form-field">
                                <button type="button" className="canwbe-form-button" disabled>
                                    {buttonText}
                                </button>
                            </div>
                        </div>

                        <div className="canwbe-editor-notice" style={{
                            marginTop: '1em',
                            padding: '0.5em',
                            background: '#e8f4fd',
                            border: '1px solid #d0e4f2',
                            borderRadius: '4px',
                            fontSize: '0.9em',
                            color: '#0073aa'
                        }}>
                            {__('Preview: This form will be functional on the frontend and includes GDPR compliance.', 'create-a-newsletter-with-the-block-editor')}
                        </div>
                    </div>
                </div>
            </>
        );
    },

    save: function() {
        // Return null because we use render_callback for dynamic rendering
        return null;
    }
});
