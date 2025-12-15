import { registerBlockType } from '@wordpress/blocks';
import {
    TextControl,
    TextareaControl,
    ToggleControl,
    SelectControl,
    PanelBody,
    RangeControl
} from '@wordpress/components';
import {
    InspectorControls,
    BlockControls,
    AlignmentToolbar,
    __experimentalFontFamilyControl as FontFamilyControl,
    __experimentalFontSizePicker as FontSizePicker,
    __experimentalColorGradientControl as ColorGradientControl,
    __experimentalUseMultipleOriginColorsAndGradients as useMultipleOriginColorsAndGradients,
    withColors,
    PanelColorSettings
} from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { compose } from '@wordpress/compose';

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
        // Textos del formulario
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
        privacyText: {
            type: 'string',
            default: __('I accept the privacy policy', 'create-a-newsletter-with-the-block-editor')
        },
        gdprText: {
            type: 'string',
            default: __('Data Controller: Website Name. Purpose: To send you a weekly newsletter via email. Legal basis: Your consent. Recipients: Your hosting provider. Rights: Access, rectification, limitation and deletion of your data if you request it. We will use your email address solely to send you the newsletters from this subscription.', 'create-a-newsletter-with-the-block-editor')
        },

        // Opciones de visualización
        showNameField: {
            type: 'boolean',
            default: true
        },
        alignment: {
            type: 'string',
            default: 'left'
        },

        // Tipografía - Título
        titleFontSize: {
            type: 'string',
            default: ''
        },
        titleFontFamily: {
            type: 'string',
            default: ''
        },
        titleFontWeight: {
            type: 'string',
            default: '600'
        },

        // Tipografía - Descripción
        descriptionFontSize: {
            type: 'string',
            default: ''
        },
        descriptionFontFamily: {
            type: 'string',
            default: ''
        },

        // Tipografía - Botón
        buttonFontSize: {
            type: 'string',
            default: ''
        },
        buttonFontFamily: {
            type: 'string',
            default: ''
        },
        buttonFontWeight: {
            type: 'string',
            default: '600'
        },

        // Colores
        titleColor: {
            type: 'string',
            default: ''
        },
        descriptionColor: {
            type: 'string',
            default: ''
        },
        backgroundColor: {
            type: 'string',
            default: ''
        },
        buttonBackgroundColor: {
            type: 'string',
            default: ''
        },
        buttonTextColor: {
            type: 'string',
            default: ''
        },
        inputBorderColor: {
            type: 'string',
            default: ''
        },

        // Espaciado
        containerPadding: {
            type: 'number',
            default: 32
        },
        borderRadius: {
            type: 'number',
            default: 8
        },
        inputBorderRadius: {
            type: 'number',
            default: 4
        },
        buttonBorderRadius: {
            type: 'number',
            default: 4
        }
    },

    edit: compose([
        withColors('titleColor', 'descriptionColor', 'backgroundColor', 'buttonBackgroundColor', 'buttonTextColor')
    ])(function(props) {
        const {
            attributes,
            setAttributes,
            titleColor,
            setTitleColor,
            descriptionColor,
            setDescriptionColor,
            backgroundColor,
            setBackgroundColor,
            buttonBackgroundColor,
            setButtonBackgroundColor,
            buttonTextColor,
            setButtonTextColor
        } = props;

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
            gdprText,
            titleFontSize,
            titleFontFamily,
            titleFontWeight,
            descriptionFontSize,
            descriptionFontFamily,
            buttonFontSize,
            buttonFontFamily,
            buttonFontWeight,
            inputBorderColor,
            containerPadding,
            borderRadius,
            inputBorderRadius,
            buttonBorderRadius
        } = attributes;

        const colorSettings = useMultipleOriginColorsAndGradients();

        // Estilos dinámicos para el contenedor
        const containerStyle = {
            textAlign: alignment,
            backgroundColor: backgroundColor?.color,
            padding: `${containerPadding}px`,
            borderRadius: `${borderRadius}px`
        };

        // Estilos dinámicos para el título
        const titleStyle = {
            color: titleColor?.color,
            fontSize: titleFontSize,
            fontFamily: titleFontFamily,
            fontWeight: titleFontWeight
        };

        // Estilos dinámicos para la descripción
        const descriptionStyle = {
            color: descriptionColor?.color,
            fontSize: descriptionFontSize,
            fontFamily: descriptionFontFamily
        };

        // Estilos dinámicos para inputs
        const inputStyle = {
            borderColor: inputBorderColor,
            borderRadius: `${inputBorderRadius}px`
        };

        // Estilos dinámicos para el botón
        const buttonStyle = {
            backgroundColor: buttonBackgroundColor?.color,
            color: buttonTextColor?.color,
            fontSize: buttonFontSize,
            fontFamily: buttonFontFamily,
            fontWeight: buttonFontWeight,
            borderRadius: `${buttonBorderRadius}px`
        };

        return (
            <>
                <BlockControls>
                    <AlignmentToolbar
                        value={alignment}
                        onChange={(value) => setAttributes({ alignment: value })}
                    />
                </BlockControls>

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

                        <ToggleControl
                            label={__('Show Name Field', 'create-a-newsletter-with-the-block-editor')}
                            checked={showNameField}
                            onChange={(value) => setAttributes({ showNameField: value })}
                            help={__('Allow users to enter their name along with email', 'create-a-newsletter-with-the-block-editor')}
                        />
                    </PanelBody>

                    <PanelBody
                        title={__('Typography - Title', 'create-a-newsletter-with-the-block-editor')}
                        initialOpen={false}
                    >
                        <FontSizePicker
                            value={titleFontSize}
                            onChange={(value) => setAttributes({ titleFontSize: value })}
                        />

                        <SelectControl
                            label={__('Font Weight', 'create-a-newsletter-with-the-block-editor')}
                            value={titleFontWeight}
                            options={[
                                { label: __('Normal', 'create-a-newsletter-with-the-block-editor'), value: '400' },
                                { label: __('Medium', 'create-a-newsletter-with-the-block-editor'), value: '500' },
                                { label: __('Semi Bold', 'create-a-newsletter-with-the-block-editor'), value: '600' },
                                { label: __('Bold', 'create-a-newsletter-with-the-block-editor'), value: '700' }
                            ]}
                            onChange={(value) => setAttributes({ titleFontWeight: value })}
                        />
                    </PanelBody>

                    <PanelBody
                        title={__('Typography - Description', 'create-a-newsletter-with-the-block-editor')}
                        initialOpen={false}
                    >
                        <FontSizePicker
                            value={descriptionFontSize}
                            onChange={(value) => setAttributes({ descriptionFontSize: value })}
                        />
                    </PanelBody>

                    <PanelBody
                        title={__('Typography - Button', 'create-a-newsletter-with-the-block-editor')}
                        initialOpen={false}
                    >
                        <FontSizePicker
                            value={buttonFontSize}
                            onChange={(value) => setAttributes({ buttonFontSize: value })}
                        />

                        <SelectControl
                            label={__('Font Weight', 'create-a-newsletter-with-the-block-editor')}
                            value={buttonFontWeight}
                            options={[
                                { label: __('Normal', 'create-a-newsletter-with-the-block-editor'), value: '400' },
                                { label: __('Medium', 'create-a-newsletter-with-the-block-editor'), value: '500' },
                                { label: __('Semi Bold', 'create-a-newsletter-with-the-block-editor'), value: '600' },
                                { label: __('Bold', 'create-a-newsletter-with-the-block-editor'), value: '700' }
                            ]}
                            onChange={(value) => setAttributes({ buttonFontWeight: value })}
                        />
                    </PanelBody>

                    <PanelColorSettings
                        title={__('Color Settings', 'create-a-newsletter-with-the-block-editor')}
                        initialOpen={false}
                        colorSettings={[
                            {
                                value: titleColor?.color,
                                onChange: setTitleColor,
                                label: __('Title Color', 'create-a-newsletter-with-the-block-editor')
                            },
                            {
                                value: descriptionColor?.color,
                                onChange: setDescriptionColor,
                                label: __('Description Color', 'create-a-newsletter-with-the-block-editor')
                            },
                            {
                                value: backgroundColor?.color,
                                onChange: setBackgroundColor,
                                label: __('Background Color', 'create-a-newsletter-with-the-block-editor')
                            },
                            {
                                value: buttonBackgroundColor?.color,
                                onChange: setButtonBackgroundColor,
                                label: __('Button Background', 'create-a-newsletter-with-the-block-editor')
                            },
                            {
                                value: buttonTextColor?.color,
                                onChange: setButtonTextColor,
                                label: __('Button Text Color', 'create-a-newsletter-with-the-block-editor')
                            }
                        ]}
                    />

                    <PanelBody
                        title={__('Spacing & Borders', 'create-a-newsletter-with-the-block-editor')}
                        initialOpen={false}
                    >
                        <RangeControl
                            label={__('Container Padding', 'create-a-newsletter-with-the-block-editor')}
                            value={containerPadding}
                            onChange={(value) => setAttributes({ containerPadding: value })}
                            min={0}
                            max={100}
                        />

                        <RangeControl
                            label={__('Container Border Radius', 'create-a-newsletter-with-the-block-editor')}
                            value={borderRadius}
                            onChange={(value) => setAttributes({ borderRadius: value })}
                            min={0}
                            max={50}
                        />

                        <RangeControl
                            label={__('Input Border Radius', 'create-a-newsletter-with-the-block-editor')}
                            value={inputBorderRadius}
                            onChange={(value) => setAttributes({ inputBorderRadius: value })}
                            min={0}
                            max={25}
                        />

                        <RangeControl
                            label={__('Button Border Radius', 'create-a-newsletter-with-the-block-editor')}
                            value={buttonBorderRadius}
                            onChange={(value) => setAttributes({ buttonBorderRadius: value })}
                            min={0}
                            max={25}
                        />
                    </PanelBody>

                    <PanelBody
                        title={__('Field Settings', 'create-a-newsletter-with-the-block-editor')}
                        initialOpen={false}
                    >
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

                <div className="canwbe-subscription-form-wrapper">
                    <div className="canwbe-subscription-form-container" style={containerStyle}>
                        {title && (
                            <h3 className="canwbe-subscription-form-title" style={titleStyle}>
                                {title}
                            </h3>
                        )}

                        {description && (
                            <p className="canwbe-subscription-form-description" style={descriptionStyle}>
                                {description}
                            </p>
                        )}

                        <div className="canwbe-form-fields">
                            {showNameField && (
                                <div className="canwbe-form-field">
                                    <input
                                        type="text"
                                        placeholder={placeholderName}
                                        className="canwbe-form-input canwbe-name-input"
                                        style={inputStyle}
                                        disabled
                                    />
                                </div>
                            )}

                            <div className="canwbe-form-field">
                                <input
                                    type="email"
                                    placeholder={placeholderEmail}
                                    className="canwbe-form-input canwbe-email-input"
                                    style={inputStyle}
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
                                <button type="button" className="canwbe-form-button" style={buttonStyle} disabled>
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
    }),

    save: function() {
        // Return null because we use render_callback for dynamic rendering
        return null;
    }
});
