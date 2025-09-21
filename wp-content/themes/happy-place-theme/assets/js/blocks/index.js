/**
 * HPH Blocks - Main Entry Point
 * 
 * Imports and registers all auto-generated blocks
 * 
 * @package HappyPlaceTheme
 */

// Import dependencies
import { registerBlockType } from '@wordpress/blocks';
import { createElement } from '@wordpress/element';
import { generateBlockControls } from './block-generator';

// Import auto-generated blocks if they exist
try {
    require('./auto-generated');
} catch (e) {
    // Auto-generated file doesn't exist yet
}

/**
 * Fallback registration for when auto-generated blocks aren't available
 */
function registerFallbackBlocks() {
    // Check if we have block configurations from the server
    if (!window.hphBlocks?.blocks) {
        return;
    }
    
    Object.keys(window.hphBlocks.blocks).forEach(blockName => {
        const blockData = window.hphBlocks.blocks[blockName];
        const config = blockData.config;
        
        // Convert attributes to Gutenberg format
        const attributes = {};
        if (config.attributes) {
            Object.keys(config.attributes).forEach(attrName => {
                const attrConfig = config.attributes[attrName];
                attributes[attrName] = {
                    type: attrConfig.type,
                    default: attrConfig.default || null
                };
            });
        }
        
        // Register the block
        registerBlockType(blockName, {
            title: config.title || 'HPH Component',
            description: config.description || '',
            category: config.category || 'hph-sections',
            icon: config.icon || 'layout',
            keywords: [
                'hph',
                'component',
                blockData.type,
                blockData.component
            ],
            attributes: attributes,
            supports: {
                html: false,
                anchor: true,
                className: true,
                color: {
                    background: true,
                    text: true
                },
                spacing: {
                    margin: true,
                    padding: true
                }
            },
            edit: (props) => generateBlockControls(blockName, props),
            save: () => null // Server-side rendered
        });
    });
}

// Register fallback blocks when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(registerFallbackBlocks, 100);
});

// Also register immediately in case DOM is already loaded
if (document.readyState === 'loading') {
    registerFallbackBlocks();
}
