/**
 * HPH Block Generator
 * 
 * Generates dynamic block controls for auto-registered blocks
 * 
 * @package HappyPlaceTheme
 */

import { createElement } from '@wordpress/element';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { 
    PanelBody, 
    TextControl, 
    ToggleControl, 
    SelectControl, 
    RangeControl,
    TextareaControl,
    ColorPicker,
    MediaUpload,
    Button,
    __experimentalUnitControl as UnitControl
} from '@wordpress/components';
import { ServerSideRender } from '@wordpress/editor';

/**
 * Generate block controls based on configuration
 */
export function generateBlockControls(blockName, props) {
    const { attributes, setAttributes } = props;
    const blockProps = useBlockProps();
    
    // Get block configuration from localized data
    const blockConfig = window.hphBlocks?.blocks?.[blockName];
    
    if (!blockConfig) {
        return createElement('div', blockProps, 'Block configuration not found');
    }
    
    const config = blockConfig.config;
    
    return createElement('div', blockProps, [
        // Inspector Controls
        createElement(InspectorControls, {}, [
            createElement(PanelBody, {
                title: config.title || 'Settings',
                initialOpen: true
            }, generateControls(config.attributes, attributes, setAttributes))
        ]),
        
        // Server-side render for preview
        createElement(ServerSideRender, {
            block: blockName,
            attributes: attributes
        })
    ]);
}

/**
 * Generate individual controls based on attribute configuration
 */
function generateControls(attributeConfigs, attributes, setAttributes) {
    if (!attributeConfigs) return [];
    
    return Object.keys(attributeConfigs).map(attrName => {
        const config = attributeConfigs[attrName];
        const value = attributes[attrName];
        
        return generateControl(attrName, config, value, setAttributes);
    });
}

/**
 * Generate a single control based on type
 */
function generateControl(name, config, value, setAttributes) {
    const onChange = (newValue) => {
        setAttributes({ [name]: newValue });
    };
    
    const commonProps = {
        key: name,
        label: config.label || name,
        value: value !== undefined ? value : config.default,
        onChange: onChange
    };
    
    switch (config.control) {
        case 'text':
            return createElement(TextControl, commonProps);
            
        case 'textarea':
            return createElement(TextareaControl, {
                ...commonProps,
                rows: config.rows || 4
            });
            
        case 'number':
            return createElement(RangeControl, {
                ...commonProps,
                min: config.min || 0,
                max: config.max || 100,
                step: config.step || 1
            });
            
        case 'toggle':
            return createElement(ToggleControl, {
                ...commonProps,
                checked: value || false
            });
            
        case 'select':
            return createElement(SelectControl, {
                ...commonProps,
                options: config.options || []
            });
            
        case 'color':
            return createElement('div', { key: name }, [
                createElement('label', {}, config.label || name),
                createElement(ColorPicker, {
                    color: value || config.default,
                    onChange: onChange
                })
            ]);
            
        case 'media':
            return createElement(MediaUpload, {
                key: name,
                onSelect: (media) => onChange(media.url),
                allowedTypes: ['image'],
                value: value,
                render: ({ open }) => createElement(Button, {
                    onClick: open,
                    isSecondary: true
                }, value ? 'Change Image' : 'Select Image')
            });
            
        case 'url':
            return createElement(TextControl, {
                ...commonProps,
                type: 'url'
            });
            
        default:
            return createElement(TextControl, commonProps);
    }
}

/**
 * Generate block variations based on component style
 */
export function generateBlockVariations(blockName, config) {
    const variations = [];
    
    // Add default variation
    variations.push({
        name: 'default',
        title: config.title,
        description: config.description,
        icon: config.icon,
        attributes: {}
    });
    
    // Add style-based variations if available
    if (config.attributes?.style?.options) {
        config.attributes.style.options.forEach(option => {
            variations.push({
                name: option.value,
                title: `${config.title} - ${option.label}`,
                description: `${config.description} with ${option.label.toLowerCase()} style`,
                icon: config.icon,
                attributes: {
                    style: option.value
                }
            });
        });
    }
    
    return variations;
}
