import { registerBlockType } from '@wordpress/blocks';
import {
    TextControl,
    TextareaControl,
    ToggleControl,
    PanelBody,
    RangeControl,
    __experimentalUnitControl as UnitControl
} from '@wordpress/components';
import {
    InspectorControls,
    BlockControls,
    AlignmentToolbar,
    useBlockProps,
    __experimentalPanelColorGradientSettings as PanelColorGradientSettings,
    FontSizePicker,
    __experimentalFontFamilyControl as FontFamilyControl,
    __experimentalFontAppearanceControl as FontAppearanceControl,
    useSetting
} from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

registerBlockType('canwbe/subscription-form', {
    apiVersion: 2,
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
    supports: {
        align: ['wide', 'full'],
        spacing: {
            padding: true,
            margin: true
        }
    },
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
            type: 'string'
        },
        titleFontFamily: {
            type: 'string'
        },
        titleFontStyle: {
            type: 'string'
        },
        titleFontWeight: {
            type: 'string'
        },

        // Tipografía - Descripción
        descriptionFontSize: {
            type: 'string'
        },
        descriptionFontFamily: {
            type: 'string'
        },
        descriptionFontStyle: {
            type: 'string'
        },
        descriptionFontWeight: {
            type: 'string'
        },

        // Tipografía - Inputs
        inputFontSize: {
            type: 'string'
        },
        inputFontFamily: {
            type: 'string'
        },

        // Tipografía - Botón
        buttonFontSize: {
            type: 'string'
        },
        buttonFontFamily: {
            type: 'string'
        },
        buttonFontStyle: {
            type: 'string'
        },
        buttonFontWeight: {
            type: 'string'
        },

        // Colores - Título
        titleColor: {
            type: 'string'
        },
        titleGradient: {
            type: 'string'
        },

        // Colores - Descripción
        descriptionColor: {
            type: 'string'
        },
        descriptionGradient: {
            type: 'string'
        },

        // Colores - Container
        containerBackgroundColor: {
            type: 'string'
        },
        containerGradient: {
            type: 'string'
        },

        // Colores - Inputs
        inputTextColor: {
            type: 'string'
        },
        inputBackgroundColor: {
            type: 'string'
        },
        inputBorderColor: {
            type: 'string'
        },
        inputPlaceholderColor: {
            type: 'string'
        },

        // Colores - Botón
        buttonTextColor: {
            type: 'string'
        },
        buttonBackgroundColor: {
            type: 'string'
        },
        buttonGradient: {
            type: 'string'
        },
        buttonBorderColor: {
            type: 'string'
        },

        // Colores - Hover states
        buttonHoverTextColor: {
            type: 'string'
        },
        buttonHoverBackgroundColor: {
            type: 'string'
        },
        inputFocusBorderColor: {
            type: 'string'
        },

        // Colores - Privacy/GDPR
        privacyTextColor: {
            type: 'string'
        },
        gdprTextColor: {
            type: 'string'
        },
        gdprBackgroundColor: {
            type: 'string'
        },

        // Espaciado y bordes
        containerPadding: {
            type: 'string',
            default: '2em'
        },
        borderRadius: {
            type: 'string',
            default: '8px'
        },
        inputBorderRadius: {
            type: 'string',
            default: '4px'
        },
        buttonBorderRadius: {
            type: 'string',
            default: '4px'
        },
        inputBorderWidth: {
            type: 'string',
            default: '1px'
        },
        buttonBorderWidth: {
            type: 'string',
            default: '0px'
        }
    },

    edit: function(props) {
        const { attributes, setAttributes } = props;
        const blockProps = useBlockProps();

        // Obtener configuraciones del tema
        const fontSizes = useSetting('typography.fontSizes') || [];
        const fontFamilies = useSetting('typography.fontFamilies') || [];
        const colors = useSetting('color.palette') || [];
        const gradients = useSetting('color.gradients') || [];

        const {
            title,
            description,
            buttonText,
            placeholderEmail,
            placeholderName,
            showNameField,
            alignment,
            privacyText,
            gdprText,

            // Typography
            titleFontSize,
            titleFontFamily,
            titleFontStyle,
            titleFontWeight,
            descriptionFontSize,
            descriptionFontFamily,
            descriptionFontStyle,
            descriptionFontWeight,
            inputFontSize,
            inputFontFamily,
            buttonFontSize,
            buttonFontFamily,
            buttonFontStyle,
            buttonFontWeight,

            // Colors
            titleColor,
            titleGradient,
            descriptionColor,
            descriptionGradient,
            containerBackgroundColor,
            containerGradient,
            inputTextColor,
            inputBackgroundColor,
            inputBorderColor,
            inputPlaceholderColor,
            buttonTextColor,
            buttonBackgroundColor,
            buttonGradient,
            buttonBorderColor,
            buttonHoverTextColor,
            buttonHoverBackgroundColor,
            inputFocusBorderColor,
            privacyTextColor,
            gdprTextColor,
            gdprBackgroundColor,

            // Spacing
            containerPadding,
            borderRadius,
            inputBorderRadius,
            buttonBorderRadius,
            inputBorderWidth,
            buttonBorderWidth
        } = attributes;

        // Construir estilos dinámicos
        const containerStyle = {
            textAlign: alignment,
            background: containerGradient || containerBackgroundColor || '#f9f9f9',
            padding: containerPadding,
            borderRadius: borderRadius
        };

        const titleStyle = {
            color: titleColor,
            background: titleGradient,
            WebkitBackgroundClip: titleGradient ? 'text' : undefined,
            WebkitTextFillColor: titleGradient ? 'transparent' : undefined,
            fontSize: titleFontSize,
            fontFamily: titleFontFamily,
            fontStyle: titleFontStyle,
            fontWeight: titleFontWeight
        };

        const descriptionStyle = {
            color: descriptionColor,
            background: descriptionGradient,
            WebkitBackgroundClip: descriptionGradient ? 'text' : undefined,
            WebkitTextFillColor: descriptionGradient ? 'transparent' : undefined,
            fontSize: descriptionFontSize,
            fontFamily: descriptionFontFamily,
            fontStyle: descriptionFontStyle,
            fontWeight: descriptionFontWeight
        };

        const inputStyle = {
            color: inputTextColor,
            backgroundColor: inputBackgroundColor,
            borderColor: inputBorderColor,
            borderWidth: inputBorderWidth,
            borderRadius: inputBorderRadius,
            fontSize: inputFontSize,
            fontFamily: inputFontFamily
        };

        const buttonStyle = {
            color: buttonTextColor,
            background: buttonGradient || buttonBackgroundColor || '#0073aa',
            borderColor: buttonBorderColor,
            borderWidth: buttonBorderWidth,
            borderRadius: buttonBorderRadius,
            fontSize: buttonFontSize,
            fontFamily: buttonFontFamily,
            fontStyle: buttonFontStyle,
            fontWeight: buttonFontWeight
        };

        const privacyStyle = {
            color: privacyTextColor
        };

        const gdprStyle = {
            color: gdprTextColor,
            backgroundColor: gdprBackgroundColor
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
                    {/* Form Settings */}
                    <PanelBody
                        title={__('Form Settings', 'create-a-newsletter-with-the-block-editor')}
                        initialOpen={true}
                    >
                        <TextControl
                            label={__('Form Title', 'create-a-newsletter-with-the-block-editor')}
                            value={title}
                            onChange={(value) => setAttributes({ title: value })}
                            __nextHasNoMarginBottom
                        />

                        <TextareaControl
                            label={__('Description', 'create-a-newsletter-with-the-block-editor')}
                            value={description}
                            onChange={(value) => setAttributes({ description: value })}
                            help={__('Brief description about your newsletter', 'create-a-newsletter-with-the-block-editor')}
                            __nextHasNoMarginBottom
                        />

                        <ToggleControl
                            label={__('Show Name Field', 'create-a-newsletter-with-the-block-editor')}
                            checked={showNameField}
                            onChange={(value) => setAttributes({ showNameField: value })}
                            __nextHasNoMarginBottom
                        />
                    </PanelBody>

                    {/* Typography - Title */}
                    <PanelBody
                        title={__('Typography - Title', 'create-a-newsletter-with-the-block-editor')}
                        initialOpen={false}
                    >
                        <FontSizePicker
                            fontSizes={fontSizes}
                            value={titleFontSize}
                            onChange={(value) => setAttributes({ titleFontSize: value })}
                            withReset
                            __nextHasNoMarginBottom
                        />

                        {FontFamilyControl && (
                            <FontFamilyControl
                                fontFamilies={fontFamilies}
                                value={titleFontFamily}
                                onChange={(value) => setAttributes({ titleFontFamily: value })}
                                __nextHasNoMarginBottom
                            />
                        )}

                        {FontAppearanceControl && (
                            <FontAppearanceControl
                                value={{
                                    fontStyle: titleFontStyle,
                                    fontWeight: titleFontWeight
                                }}
                                onChange={(value) => {
                                    setAttributes({
                                        titleFontStyle: value.fontStyle,
                                        titleFontWeight: value.fontWeight
                                    });
                                }}
                                __nextHasNoMarginBottom
                            />
                        )}
                    </PanelBody>

                    {/* Typography - Description */}
                    <PanelBody
                        title={__('Typography - Description', 'create-a-newsletter-with-the-block-editor')}
                        initialOpen={false}
                    >
                        <FontSizePicker
                            fontSizes={fontSizes}
                            value={descriptionFontSize}
                            onChange={(value) => setAttributes({ descriptionFontSize: value })}
                            withReset
                            __nextHasNoMarginBottom
                        />

                        {FontFamilyControl && (
                            <FontFamilyControl
                                fontFamilies={fontFamilies}
                                value={descriptionFontFamily}
                                onChange={(value) => setAttributes({ descriptionFontFamily: value })}
                                __nextHasNoMarginBottom
                            />
                        )}

                        {FontAppearanceControl && (
                            <FontAppearanceControl
                                value={{
                                    fontStyle: descriptionFontStyle,
                                    fontWeight: descriptionFontWeight
                                }}
                                onChange={(value) => {
                                    setAttributes({
                                        descriptionFontStyle: value.fontStyle,
                                        descriptionFontWeight: value.fontWeight
                                    });
                                }}
                                __nextHasNoMarginBottom
                            />
                        )}
                    </PanelBody>

                    {/* Typography - Inputs */}
                    <PanelBody
                        title={__('Typography - Input Fields', 'create-a-newsletter-with-the-block-editor')}
                        initialOpen={false}
                    >
                        <FontSizePicker
                            fontSizes={fontSizes}
                            value={inputFontSize}
                            onChange={(value) => setAttributes({ inputFontSize: value })}
                            withReset
                            __nextHasNoMarginBottom
                        />

                        {FontFamilyControl && (
                            <FontFamilyControl
                                fontFamilies={fontFamilies}
                                value={inputFontFamily}
                                onChange={(value) => setAttributes({ inputFontFamily: value })}
                                __nextHasNoMarginBottom
                            />
                        )}
                    </PanelBody>

                    {/* Typography - Button */}
                    <PanelBody
                        title={__('Typography - Button', 'create-a-newsletter-with-the-block-editor')}
                        initialOpen={false}
                    >
                        <FontSizePicker
                            fontSizes={fontSizes}
                            value={buttonFontSize}
                            onChange={(value) => setAttributes({ buttonFontSize: value })}
                            withReset
                            __nextHasNoMarginBottom
                        />

                        {FontFamilyControl && (
                            <FontFamilyControl
                                fontFamilies={fontFamilies}
                                value={buttonFontFamily}
                                onChange={(value) => setAttributes({ buttonFontFamily: value })}
                                __nextHasNoMarginBottom
                            />
                        )}

                        {FontAppearanceControl && (
                            <FontAppearanceControl
                                value={{
                                    fontStyle: buttonFontStyle,
                                    fontWeight: buttonFontWeight
                                }}
                                onChange={(value) => {
                                    setAttributes({
                                        buttonFontStyle: value.fontStyle,
                                        buttonFontWeight: value.fontWeight
                                    });
                                }}
                                __nextHasNoMarginBottom
                            />
                        )}
                    </PanelBody>

                    {/* Colors - Title */}
                    {PanelColorGradientSettings && (
                        <PanelColorGradientSettings
                            title={__('Title Colors', 'create-a-newsletter-with-the-block-editor')}
                            initialOpen={false}
                            settings={[
                                {
                                    colorValue: titleColor,
                                    gradientValue: titleGradient,
                                    colors: colors,
                                    gradients: gradients,
                                    label: __('Text Color', 'create-a-newsletter-with-the-block-editor'),
                                    onColorChange: (value) => setAttributes({ titleColor: value }),
                                    onGradientChange: (value) => setAttributes({ titleGradient: value })
                                }
                            ]}
                        />
                    )}

                    {/* Colors - Description */}
                    {PanelColorGradientSettings && (
                        <PanelColorGradientSettings
                            title={__('Description Colors', 'create-a-newsletter-with-the-block-editor')}
                            initialOpen={false}
                            settings={[
                                {
                                    colorValue: descriptionColor,
                                    gradientValue: descriptionGradient,
                                    colors: colors,
                                    gradients: gradients,
                                    label: __('Text Color', 'create-a-newsletter-with-the-block-editor'),
                                    onColorChange: (value) => setAttributes({ descriptionColor: value }),
                                    onGradientChange: (value) => setAttributes({ descriptionGradient: value })
                                }
                            ]}
                        />
                    )}

                    {/* Colors - Container */}
                    {PanelColorGradientSettings && (
                        <PanelColorGradientSettings
                            title={__('Container Colors', 'create-a-newsletter-with-the-block-editor')}
                            initialOpen={false}
                            settings={[
                                {
                                    colorValue: containerBackgroundColor,
                                    gradientValue: containerGradient,
                                    colors: colors,
                                    gradients: gradients,
                                    label: __('Background', 'create-a-newsletter-with-the-block-editor'),
                                    onColorChange: (value) => setAttributes({ containerBackgroundColor: value }),
                                    onGradientChange: (value) => setAttributes({ containerGradient: value })
                                }
                            ]}
                        />
                    )}

                    {/* Colors - Input Fields */}
                    {PanelColorGradientSettings && (
                        <PanelColorGradientSettings
                            title={__('Input Field Colors', 'create-a-newsletter-with-the-block-editor')}
                            initialOpen={false}
                            settings={[
                                {
                                    colorValue: inputTextColor,
                                    colors: colors,
                                    label: __('Text Color', 'create-a-newsletter-with-the-block-editor'),
                                    onColorChange: (value) => setAttributes({ inputTextColor: value })
                                },
                                {
                                    colorValue: inputBackgroundColor,
                                    colors: colors,
                                    label: __('Background Color', 'create-a-newsletter-with-the-block-editor'),
                                    onColorChange: (value) => setAttributes({ inputBackgroundColor: value })
                                },
                                {
                                    colorValue: inputBorderColor,
                                    colors: colors,
                                    label: __('Border Color', 'create-a-newsletter-with-the-block-editor'),
                                    onColorChange: (value) => setAttributes({ inputBorderColor: value })
                                },
                                {
                                    colorValue: inputFocusBorderColor,
                                    colors: colors,
                                    label: __('Focus Border Color', 'create-a-newsletter-with-the-block-editor'),
                                    onColorChange: (value) => setAttributes({ inputFocusBorderColor: value })
                                },
                                {
                                    colorValue: inputPlaceholderColor,
                                    colors: colors,
                                    label: __('Placeholder Color', 'create-a-newsletter-with-the-block-editor'),
                                    onColorChange: (value) => setAttributes({ inputPlaceholderColor: value })
                                }
                            ]}
                        />
                    )}

                    {/* Colors - Button */}
                    {PanelColorGradientSettings && (
                        <PanelColorGradientSettings
                            title={__('Button Colors', 'create-a-newsletter-with-the-block-editor')}
                            initialOpen={false}
                            settings={[
                                {
                                    colorValue: buttonTextColor,
                                    colors: colors,
                                    label: __('Text Color', 'create-a-newsletter-with-the-block-editor'),
                                    onColorChange: (value) => setAttributes({ buttonTextColor: value })
                                },
                                {
                                    colorValue: buttonBackgroundColor,
                                    gradientValue: buttonGradient,
                                    colors: colors,
                                    gradients: gradients,
                                    label: __('Background', 'create-a-newsletter-with-the-block-editor'),
                                    onColorChange: (value) => setAttributes({ buttonBackgroundColor: value }),
                                    onGradientChange: (value) => setAttributes({ buttonGradient: value })
                                },
                                {
                                    colorValue: buttonBorderColor,
                                    colors: colors,
                                    label: __('Border Color', 'create-a-newsletter-with-the-block-editor'),
                                    onColorChange: (value) => setAttributes({ buttonBorderColor: value })
                                },
                                {
                                    colorValue: buttonHoverTextColor,
                                    colors: colors,
                                    label: __('Hover Text Color', 'create-a-newsletter-with-the-block-editor'),
                                    onColorChange: (value) => setAttributes({ buttonHoverTextColor: value })
                                },
                                {
                                    colorValue: buttonHoverBackgroundColor,
                                    colors: colors,
                                    label: __('Hover Background', 'create-a-newsletter-with-the-block-editor'),
                                    onColorChange: (value) => setAttributes({ buttonHoverBackgroundColor: value })
                                }
                            ]}
                        />
                    )}

                    {/* Colors - Privacy & GDPR */}
                    {PanelColorGradientSettings && (
                        <PanelColorGradientSettings
                            title={__('Privacy & GDPR Colors', 'create-a-newsletter-with-the-block-editor')}
                            initialOpen={false}
                            settings={[
                                {
                                    colorValue: privacyTextColor,
                                    colors: colors,
                                    label: __('Privacy Text Color', 'create-a-newsletter-with-the-block-editor'),
                                    onColorChange: (value) => setAttributes({ privacyTextColor: value })
                                },
                                {
                                    colorValue: gdprTextColor,
                                    colors: colors,
                                    label: __('GDPR Text Color', 'create-a-newsletter-with-the-block-editor'),
                                    onColorChange: (value) => setAttributes({ gdprTextColor: value })
                                },
                                {
                                    colorValue: gdprBackgroundColor,
                                    colors: colors,
                                    label: __('GDPR Background', 'create-a-newsletter-with-the-block-editor'),
                                    onColorChange: (value) => setAttributes({ gdprBackgroundColor: value })
                                }
                            ]}
                        />
                    )}

                    {/* Spacing & Borders */}
                    <PanelBody
                        title={__('Spacing & Borders', 'create-a-newsletter-with-the-block-editor')}
                        initialOpen={false}
                    >
                        {UnitControl ? (
                            <>
                                <UnitControl
                                    label={__('Container Padding', 'create-a-newsletter-with-the-block-editor')}
                                    value={containerPadding}
                                    onChange={(value) => setAttributes({ containerPadding: value })}
                                    units={[
                                        { value: 'px', label: 'px' },
                                        { value: 'em', label: 'em' },
                                        { value: 'rem', label: 'rem' },
                                        { value: '%', label: '%' }
                                    ]}
                                />

                                <UnitControl
                                    label={__('Container Border Radius', 'create-a-newsletter-with-the-block-editor')}
                                    value={borderRadius}
                                    onChange={(value) => setAttributes({ borderRadius: value })}
                                    units={[
                                        { value: 'px', label: 'px' },
                                        { value: 'em', label: 'em' },
                                        { value: '%', label: '%' }
                                    ]}
                                />

                                <UnitControl
                                    label={__('Input Border Radius', 'create-a-newsletter-with-the-block-editor')}
                                    value={inputBorderRadius}
                                    onChange={(value) => setAttributes({ inputBorderRadius: value })}
                                    units={[
                                        { value: 'px', label: 'px' },
                                        { value: 'em', label: 'em' }
                                    ]}
                                />

                                <UnitControl
                                    label={__('Input Border Width', 'create-a-newsletter-with-the-block-editor')}
                                    value={inputBorderWidth}
                                    onChange={(value) => setAttributes({ inputBorderWidth: value })}
                                    units={[
                                        { value: 'px', label: 'px' }
                                    ]}
                                />

                                <UnitControl
                                    label={__('Button Border Radius', 'create-a-newsletter-with-the-block-editor')}
                                    value={buttonBorderRadius}
                                    onChange={(value) => setAttributes({ buttonBorderRadius: value })}
                                    units={[
                                        { value: 'px', label: 'px' },
                                        { value: 'em', label: 'em' }
                                    ]}
                                />

                                <UnitControl
                                    label={__('Button Border Width', 'create-a-newsletter-with-the-block-editor')}
                                    value={buttonBorderWidth}
                                    onChange={(value) => setAttributes({ buttonBorderWidth: value })}
                                    units={[
                                        { value: 'px', label: 'px' }
                                    ]}
                                />
                            </>
                        ) : null}
                    </PanelBody>

                    {/* Field Settings */}
                    <PanelBody
                        title={__('Field Settings', 'create-a-newsletter-with-the-block-editor')}
                        initialOpen={false}
                    >
                        <TextControl
                            label={__('Email Placeholder', 'create-a-newsletter-with-the-block-editor')}
                            value={placeholderEmail}
                            onChange={(value) => setAttributes({ placeholderEmail: value })}
                            __nextHasNoMarginBottom
                        />

                        {showNameField && (
                            <TextControl
                                label={__('Name Placeholder', 'create-a-newsletter-with-the-block-editor')}
                                value={placeholderName}
                                onChange={(value) => setAttributes({ placeholderName: value })}
                                __nextHasNoMarginBottom
                            />
                        )}

                        <TextControl
                            label={__('Button Text', 'create-a-newsletter-with-the-block-editor')}
                            value={buttonText}
                            onChange={(value) => setAttributes({ buttonText: value })}
                            __nextHasNoMarginBottom
                        />
                    </PanelBody>

                    {/* Privacy & GDPR */}
                    <PanelBody
                        title={__('Privacy & GDPR', 'create-a-newsletter-with-the-block-editor')}
                        initialOpen={false}
                    >
                        <TextControl
                            label={__('Privacy Checkbox Text', 'create-a-newsletter-with-the-block-editor')}
                            value={privacyText}
                            onChange={(value) => setAttributes({ privacyText: value })}
                            __nextHasNoMarginBottom
                        />

                        <TextareaControl
                            label={__('GDPR Information Text', 'create-a-newsletter-with-the-block-editor')}
                            value={gdprText}
                            onChange={(value) => setAttributes({ gdprText: value })}
                            rows={5}
                            __nextHasNoMarginBottom
                        />
                    </PanelBody>
                </InspectorControls>

                <div {...blockProps}>
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
                                    <label className="canwbe-privacy-label" style={privacyStyle}>
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
                                        <p className="canwbe-gdpr-text" style={gdprStyle}>{gdprText}</p>
                                    </div>
                                )}

                                <div className="canwbe-form-field">
                                    <button
                                        type="button"
                                        className="canwbe-form-button"
                                        style={buttonStyle}
                                        disabled
                                    >
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
                                {__('Preview: This form will be functional on the frontend.', 'create-a-newsletter-with-the-block-editor')}
                            </div>
                        </div>
                    </div>

                    {/* Dynamic CSS for hover states */}
                    {(buttonHoverTextColor || buttonHoverBackgroundColor || inputFocusBorderColor || inputPlaceholderColor) && (
                        <style>
                            {`
                                ${buttonHoverTextColor || buttonHoverBackgroundColor ? `
                                    .canwbe-form-button:hover {
                                        ${buttonHoverTextColor ? `color: ${buttonHoverTextColor} !important;` : ''}
                                        ${buttonHoverBackgroundColor ? `background-color: ${buttonHoverBackgroundColor} !important;` : ''}
                                    }
                                ` : ''}
                                
                                ${inputFocusBorderColor ? `
                                    .canwbe-form-input:focus {
                                        border-color: ${inputFocusBorderColor} !important;
                                    }
                                ` : ''}
                                
                                ${inputPlaceholderColor ? `
                                    .canwbe-form-input::placeholder {
                                        color: ${inputPlaceholderColor};
                                    }
                                ` : ''}
                            `}
                        </style>
                    )}
                </div>
            </>
        );
    },

    save: function() {
        return null; // Dynamic rendering via PHP
    }
});
