<?php
/**
 * HPH Dynamic Block Registration System
 * 
 * Automatically discovers and registers Gutenberg blocks from PHP component templates
 * 
 * @package HappyPlaceTheme        // Parse props into attributes - improved regex for array syntax
        if (preg_match_all('/[\'"](\w+)[\'"]\s*=>\s*([^,\n\r]+(?:\([^)]*\))?[^,\n\r]*)/s', $props_content, $prop_matches)) {
            foreach ($prop_matches[1] as $index => $prop_name) {
                $default_value = trim($prop_matches[2][$index]);
                
                // Skip nested arrays for now (they're complex for block attributes)
                if (strpos($default_value, 'array(') === 0 || strpos($default_value, '[') === 0) {
                    continue;
                }
                
                $config['attributes'][$prop_name] = $this->parse_attribute_config($prop_name, $default_value);
            }
        }nce 3.0.0
 */

class HPH_Block_Registry {
    
    /**
     * Registered blocks cache
     */
    private $registered_blocks = array();
    
    /**
     * Component Loader instance
     */
    private $component_loader = null;
    
    /**
     * Initialize the registry
     */
    public function __construct() {
        add_action('init', array($this, 'register_blocks_from_component_loader'), 15); // After component loader
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_assets'));
        add_filter('block_categories_all', array($this, 'register_block_categories'));
    }
    
    /**
     * Register blocks from HPH_Component_Loader registry
     */
    public function register_blocks_from_component_loader() {
        if (!class_exists('HPH_Component_Loader')) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('HPH Block Registry: HPH_Component_Loader not available');
            }
            return;
        }
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('HPH Block Registry: Starting registration from Component Loader');
        }
        
        // Get components from the unified component loader
        $components = HPH_Component_Loader::get_components();
        
        foreach ($components as $name => $config) {
            // Only register components that should be blocks
            if ($this->should_component_be_block($config)) {
                $this->register_component_as_block($name, $config);
                
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("HPH Block Registry: Registering {$name} as block");
                }
            }
        }
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('HPH Block Registry: Found ' . count($this->registered_blocks) . ' blocks');
        }
        
        // Register the dynamic block types
        $this->register_dynamic_block_types();
    }
    
    /**
     * Scan directory for components and register as blocks
     */
    private function scan_and_register_directory($type, $dir) {
        $template_dir = get_template_directory() . '/' . $dir;
        
        if (!is_dir($template_dir)) {
            return;
        }
        
        $files = glob($template_dir . '*.php');
        
        foreach ($files as $file) {
            $component_name = basename($file, '.php');
            
            // Skip helper files and system files
            if (strpos($component_name, '-') === 0 || 
                strpos($component_name, '_') === 0 || 
                in_array($component_name, array('helper', 'section-helper', 'index'))) {
                
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("HPH Block Registry: Skipping helper file: {$component_name}");
                }
                continue;
            }
            
            // Extract component configuration
            $config = $this->extract_component_config($file);
            
            if ($config && $this->should_register_as_block($config)) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("HPH Block Registry: Registering {$component_name} as block");
                }
                $this->register_component_as_block($type, $component_name, $config);
            } else {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("HPH Block Registry: Skipping {$component_name} - no valid config found");
                }
            }
        }
    }
    
    /**
     * Extract configuration from component file header
     */
    private function extract_component_config($file) {
        $content = file_get_contents($file);
        
        // Look for configuration in file header comment
        if (preg_match('/\*\s*@block-config\s*(.*?)\s*\*\//s', $content, $matches)) {
            $config_json = trim($matches[1]);
            
            // Clean up comment asterisks and whitespace from JSON
            $config_json = preg_replace('/^\s*\*\s*/m', '', $config_json);
            $config_json = trim($config_json);
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("HPH Block Registry: Extracted JSON: " . substr($config_json, 0, 100) . "...");
            }
            
            $config = json_decode($config_json, true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("HPH Block Registry: Found JSON config for component");
                }
                return $config;
            } else {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("HPH Block Registry: JSON decode error: " . json_last_error_msg());
                }
            }
        }
        
        // Alternative: Look for inline configuration
        if (preg_match('/\/\/ BLOCK_CONFIG_START(.*?)\/\/ BLOCK_CONFIG_END/s', $content, $matches)) {
            return $this->parse_inline_config($matches[1]);
        }
        
        // Fallback: Auto-generate config from props
        return $this->generate_config_from_props($content);
    }
    
    /**
     * Parse inline configuration from component file
     */
    private function parse_inline_config($config_content) {
        $config = array(
            'title' => 'Component',
            'description' => '',
            'category' => 'hph-sections',
            'icon' => 'layout',
            'supports' => array('gutenberg'),
            'attributes' => array()
        );
        
        // Parse simple key: value pairs from the config content
        $lines = explode("\n", trim($config_content));
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, ':') === false) {
                continue;
            }
            
            list($key, $value) = explode(':', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes
            $value = trim($value, '"\'');
            
            if (isset($config[$key])) {
                $config[$key] = $value;
            }
        }
        
        return $config;
    }
    
    /**
     * Parse props from component file to generate config
     */
    private function generate_config_from_props($content) {
        // Look for wp_parse_args with hph_get_arg() pattern (your current structure)
        if (preg_match('/wp_parse_args\s*\(\s*hph_get_arg\(\)\s*,\s*array\s*\((.*?)\)\s*\)/s', $content, $matches)) {
            $props_content = $matches[1];
        }
        // Alternative: Look for wp_parse_args pattern with $args ?? [] and array
        else if (preg_match('/wp_parse_args\s*\(\s*\$args\s*\?\?\s*\[\]\s*,\s*\[(.*?)\]\s*\)/s', $content, $matches)) {
            $props_content = $matches[1];
        }
        // Another alternative: $args ?? array() 
        else if (preg_match('/wp_parse_args\s*\(\s*\$args\s*\?\?\s*array\(\)\s*,\s*array\s*\((.*?)\)\s*\)/s', $content, $matches)) {
            $props_content = $matches[1];
        }
        // Look for $defaults = array(...) pattern
        else if (preg_match('/\$defaults\s*=\s*array\s*\((.*?)\);/s', $content, $matches)) {
            $props_content = $matches[1];
        }
        else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('HPH Block Registry: No matching pattern found for component config extraction');
            }
            return null;
        }
        $config = array(
            'title' => $this->extract_component_title($content),
            'description' => $this->extract_component_description($content),
            'category' => 'hph-sections',
            'icon' => 'layout',
            'supports' => array('gutenberg'),
            'attributes' => array()
        );
        
        // Parse props into attributes - improved regex for array syntax
        if (preg_match_all('/[\'"](\w+)[\'"]\s*=>\s*([^,\]]+(?:\[[^\]]*\])?[^,\]]*)/s', $props_content, $prop_matches)) {
            foreach ($prop_matches[1] as $index => $prop_name) {
                $default_value = $prop_matches[2][$index];
                $config['attributes'][$prop_name] = $this->parse_attribute_config($prop_name, $default_value);
            }
        }
        
        return $config;
    }
    
    /**
     * Parse attribute configuration from default value
     */
    private function parse_attribute_config($name, $default_value_str) {
        $attribute = array(
            'label' => ucwords(str_replace('_', ' ', $name))
        );
        
        // Clean up the default value string
        $default_value_str = trim($default_value_str);
        
        // Determine type and control based on default value
        if (strpos($default_value_str, 'array(') !== false || strpos($default_value_str, '[') === 0) {
            $attribute['type'] = 'array';
            $attribute['control'] = 'repeater';
            $attribute['default'] = array();
        } elseif ($default_value_str === 'true' || $default_value_str === 'false') {
            $attribute['type'] = 'boolean';
            $attribute['control'] = 'toggle';
            $attribute['default'] = $default_value_str === 'true';
        } elseif (is_numeric(str_replace(array("'", '"'), '', $default_value_str))) {
            $attribute['type'] = 'number';
            $attribute['control'] = 'number';
            $attribute['default'] = intval(str_replace(array("'", '"'), '', $default_value_str));
        } else {
            $attribute['type'] = 'string';
            $attribute['default'] = trim($default_value_str, "'\"");
            
            // Check for common patterns to determine control type
            if (strpos($name, 'color') !== false) {
                $attribute['control'] = 'color';
            } elseif (strpos($name, 'image') !== false || strpos($name, 'media') !== false) {
                $attribute['control'] = 'media';
            } elseif (strpos($name, 'url') !== false || strpos($name, 'link') !== false) {
                $attribute['control'] = 'url';
            } elseif (strpos($name, 'content') !== false || strpos($name, 'description') !== false) {
                $attribute['control'] = 'textarea';
            } elseif (in_array($name, array('layout', 'style', 'variant', 'size', 'alignment'))) {
                $attribute['control'] = 'select';
                $attribute['options'] = $this->get_common_options($name);
            } else {
                $attribute['control'] = 'text';
            }
        }
        
        return $attribute;
    }
    
    /**
     * Get common options for select controls
     */
    private function get_common_options($name) {
        $options = array(
            'layout' => array(
                array('label' => 'Default', 'value' => 'default'),
                array('label' => 'Two Column', 'value' => 'two-column'),
                array('label' => 'Three Column', 'value' => 'three-column'),
                array('label' => 'Grid', 'value' => 'grid'),
                array('label' => 'List', 'value' => 'list')
            ),
            'style' => array(
                array('label' => 'Default', 'value' => 'default'),
                array('label' => 'Card', 'value' => 'card'),
                array('label' => 'Minimal', 'value' => 'minimal'),
                array('label' => 'Bordered', 'value' => 'bordered')
            ),
            'size' => array(
                array('label' => 'Small', 'value' => 'sm'),
                array('label' => 'Medium', 'value' => 'md'),
                array('label' => 'Large', 'value' => 'lg'),
                array('label' => 'Extra Large', 'value' => 'xl')
            ),
            'alignment' => array(
                array('label' => 'Left', 'value' => 'left'),
                array('label' => 'Center', 'value' => 'center'),
                array('label' => 'Right', 'value' => 'right')
            ),
            'variant' => array(
                array('label' => 'Primary', 'value' => 'primary'),
                array('label' => 'Secondary', 'value' => 'secondary'),
                array('label' => 'Default', 'value' => 'default')
            )
        );
        
        return $options[$name] ?? array();
    }
    
    /**
     * Extract component title from file header
     */
    private function extract_component_title($content) {
        // Look for component name in header comment - specific patterns
        $patterns = array(
            '/\*\s*Base\s+([^@\n]+?)\s+(?:Section\s+)?Component/i',
            '/\*\s*([^@\n]+?)\s+(?:Section\s+)?Component/i',
            '/\*\s*HPH\s+([^@\n]+?)\s+(?:Section|Component)/i',
            '/\*\s*([^@\n]+?)\s+Section(?:\s+Template)?/i'
        );
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $title = trim($matches[1]);
                
                // Clean up the title
                $title = preg_replace('/\s+/', ' ', $title); // Normalize whitespace
                $title = trim($title);
                
                if (!empty($title) && $title !== '*' && strlen($title) > 1) {
                    return $title;
                }
            }
        }
        
        return 'Component';
    }
    
    /**
     * Extract component description from file header
     */
    private function extract_component_description($content) {
        // Look for description after component name and before @package
        if (preg_match('/\*\s*.*?\s*(?:Component|Template)\s*\n\s*\*\s*\n\s*\*\s*(.*?)\n/i', $content, $matches)) {
            return trim($matches[1]);
        }
        
        // Alternative: Look for description in multi-line comment
        if (preg_match('/\*\s*.*?\s*\n\s*\*\s*\n\s*\*\s*(.*?)(?:\n\s*\*\s*\n|\n\s*\*\s*@)/s', $content, $matches)) {
            $desc = trim($matches[1]);
            // Clean up extra asterisks and whitespace
            $desc = preg_replace('/\s*\*\s*/', ' ', $desc);
            return trim($desc);
        }
        
        return '';
    }
    
    /**
     * Check if component should be registered as Gutenberg block
     */
    private function should_component_be_block($config) {
        // Only register user-facing components as blocks
        $block_categories = ['sections', 'listing', 'agent', 'search'];
        
        if (!in_array($config['category'], $block_categories)) {
            return false;
        }
        
        // Skip if explicitly disabled
        if (isset($config['block_enabled']) && $config['block_enabled'] === false) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Register component as Gutenberg block
     */
    private function register_component_as_block($name, $config) {
        $block_name = 'hph/' . $config['category'] . '-' . $name;
        
        // Store in registry
        $this->registered_blocks[$block_name] = array(
            'component_name' => $name,
            'component_path' => $config['path'],
            'config' => $config
        );
        
        // Generate block configuration
        $this->generate_block_config($block_name, $config);
    }
    
    /**
     * Register all dynamic block types
     */
    private function register_dynamic_block_types() {
        foreach ($this->registered_blocks as $block_name => $block_data) {
            $attributes = array();
            
            // Generate attributes from component args
            if (isset($block_data['config']['args'])) {
                foreach ($block_data['config']['args'] as $arg_name) {
                    $attributes[$arg_name] = array(
                        'type' => 'string',
                        'default' => ''
                    );
                }
            }
            
            // Add standard attributes
            $attributes = array_merge($attributes, array(
                'className' => array('type' => 'string', 'default' => ''),
                'anchor' => array('type' => 'string', 'default' => '')
            ));
            
            register_block_type($block_name, array(
                'render_callback' => array($this, 'render_dynamic_block'),
                'attributes' => $attributes,
                'editor_script' => 'hph-blocks-editor',
                'editor_style' => 'hph-blocks-editor',
                'style' => 'hph-blocks-frontend'
            ));
        }
    }
    
    /**
     * Unified render callback for all dynamic blocks
     */
    public function render_dynamic_block($attributes, $content, $block) {
        $block_name = $block->name;
        
        if (!isset($this->registered_blocks[$block_name])) {
            return '';
        }
        
        $block_data = $this->registered_blocks[$block_name];
        $component_name = $block_data['component_name'];
        
        // Use HPH_Component_Loader to render component
        if (class_exists('HPH_Component_Loader')) {
            return HPH_Component_Loader::load_component($component_name, $attributes, false);
        }
        
        // Fallback: Direct template include
        $template_file = get_template_directory() . '/template-parts/' . $block_data['component_path'] . '.php';
        
        if (!file_exists($template_file)) {
            return '<!-- HPH Block: Template not found: ' . esc_html($template_file) . ' -->';
        }
        
        // Set args for template access
        $GLOBALS['hph_component_args'] = $attributes;
        
        // Capture output
        ob_start();
        include $template_file;
        $output = ob_get_clean();
        
        // Clean up
        unset($GLOBALS['hph_component_args']);
        
        return $output;
    }
    
    /**
     * Generate block configuration
     */
    private function generate_block_config($block_name, $config) {
        $block_json = array(
            'apiVersion' => 2,
            'name' => $block_name,
            'title' => $config['name'],
            'category' => 'hph-' . $config['category'],
            'icon' => 'layout', // Simplified for now
            'description' => $config['description'],
            'supports' => array(
                'html' => false,
                'anchor' => true,
                'className' => true,
                'color' => array(
                    'background' => true,
                    'text' => true
                ),
                'spacing' => array(
                    'margin' => true,
                    'padding' => true
                )
            ),
            'attributes' => array(),
            'editorScript' => 'file:./index.js',
            'editorStyle' => 'file:./editor.css',
            'style' => 'file:./style.css'
        );
        
        // Generate attributes from component args
        if (isset($config['args'])) {
            foreach ($config['args'] as $arg_name) {
                $block_json['attributes'][$arg_name] = array(
                    'type' => 'string',
                    'default' => ''
                );
            }
        }
        
        // Store configuration (for potential JavaScript generation)
        // $this->store_block_json($block_name, $block_json);
    }
    
    /**
     * Store block.json for JavaScript build process
     */
    private function store_block_json($block_name, $block_json) {
        $upload_dir = wp_upload_dir();
        $blocks_dir = $upload_dir['basedir'] . '/hph-blocks-cache/';
        
        if (!is_dir($blocks_dir)) {
            wp_mkdir_p($blocks_dir);
        }
        
        $filename = str_replace('/', '-', $block_name) . '.json';
        file_put_contents($blocks_dir . $filename, json_encode($block_json, JSON_PRETTY_PRINT));
    }
    
    /**
     * Enqueue block editor assets
     */
    public function enqueue_block_assets() {
        // Generate dynamic JavaScript for blocks
        $this->generate_block_javascript();
        
        wp_enqueue_script(
            'hph-blocks-editor',
            get_template_directory_uri() . '/assets/js/blocks/index.js',
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-data'),
            filemtime(get_template_directory() . '/assets/js/blocks/index.js')
        );
        
        wp_localize_script('hph-blocks-editor', 'hphBlocks', array(
            'blocks' => $this->registered_blocks,
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hph_blocks')
        ));
    }
    
    /**
     * Generate JavaScript for dynamic blocks
     */
    private function generate_block_javascript() {
        $js_content = "/**\n * Auto-generated block registrations\n */\n\n";
        $js_content .= "import { registerBlockType } from '@wordpress/blocks';\n";
        $js_content .= "import { createElement } from '@wordpress/element';\n";
        $js_content .= "import { generateBlockControls } from './block-generator';\n\n";
        
        foreach ($this->registered_blocks as $block_name => $block_data) {
            $js_content .= $this->generate_block_js($block_name, $block_data);
        }
        
        // Save to file (only if directory is writable)
        $blocks_js_file = get_template_directory() . '/assets/js/blocks/auto-generated.js';
        $blocks_dir = dirname($blocks_js_file);
        
        // Create directory if it doesn't exist
        if (!is_dir($blocks_dir)) {
            wp_mkdir_p($blocks_dir);
        }
        
        // Only write if directory is writable
        if (is_writable($blocks_dir)) {
            file_put_contents($blocks_js_file, $js_content);
        } else {
            error_log('HPH Blocks: Cannot write to blocks directory: ' . $blocks_dir);
        }
    }
    
    /**
     * Generate JavaScript for individual block
     */
    private function generate_block_js($block_name, $block_data) {
        $config = $block_data['config'];
        $js = "\nregisterBlockType('{$block_name}', {\n";
        $js .= "    title: '" . esc_js($config['title']) . "',\n";
        $js .= "    category: '" . esc_js($config['category']) . "',\n";
        $js .= "    icon: '" . esc_js($config['icon']) . "',\n";
        $js .= "    attributes: " . json_encode($this->convert_attributes_for_js($config['attributes'])) . ",\n";
        $js .= "    edit: (props) => generateBlockControls('{$block_name}', props),\n";
        $js .= "    save: () => null\n";
        $js .= "});\n";
        
        return $js;
    }
    
    /**
     * Convert PHP attributes to JavaScript format
     */
    private function convert_attributes_for_js($attributes) {
        $js_attributes = array();
        
        foreach ($attributes as $name => $config) {
            $js_attributes[$name] = array(
                'type' => $config['type'],
                'default' => $config['default'] ?? null
            );
        }
        
        return $js_attributes;
    }
    
    /**
     * Register custom block categories
     */
    public function register_block_categories($categories) {
        return array_merge(
            array(
                array(
                    'slug' => 'hph-sections',
                    'title' => 'HPH Sections',
                    'icon' => 'layout'
                ),
                array(
                    'slug' => 'hph-components',
                    'title' => 'HPH Components',
                    'icon' => 'admin-generic'
                ),
                array(
                    'slug' => 'hph-base',
                    'title' => 'HPH Base',
                    'icon' => 'admin-tools'
                )
            ),
            $categories
        );
    }
    
    /**
     * Get all registered blocks (for admin UI)
     */
    public function get_registered_blocks() {
        return $this->registered_blocks;
    }
    
    /**
     * Export block configurations (for JavaScript build)
     */
    public function export_block_configs() {
        return json_encode($this->registered_blocks);
    }
}

// Initialize the registry
new HPH_Block_Registry();

/**
 * Helper function to add block configuration to component files
 * Add this comment block to your component files:
 * 
 * @block-config
 * {
 *   "title": "Content Section",
 *   "category": "hph-sections",
 *   "icon": "align-left",
 *   "description": "Flexible content section",
 *   "supports": ["gutenberg", "shortcode"],
 *   "attributes": {
 *     "layout": {
 *       "type": "string",
 *       "control": "select",
 *       "label": "Layout",
 *       "options": [
 *         {"label": "Default", "value": "default"},
 *         {"label": "Two Column", "value": "two-column"}
 *       ],
 *       "default": "default"
 *     }
 *   }
 * }
 */