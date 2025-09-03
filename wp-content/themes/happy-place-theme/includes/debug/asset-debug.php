<?php
/**
 * Asset Debug Helper
 * 
 * Displays loaded assets information in the footer for debugging
 * Only visible to administrators
 * 
 * @package HappyPlaceTheme
 * @since 3.2.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Display asset debug information
 */
function hph_display_asset_debug() {
    // Only show to admins and in development
    if (!current_user_can('manage_options') || !defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }
    
    // Get the assets service
    $theme = HPH_Theme::instance();
    $assets_service = $theme->get_service('assets');
    
    if (!$assets_service) {
        return;
    }
    
    $loaded_assets = $assets_service->get_loaded_assets();
    $page_context = $assets_service->get_page_context();
    
    ?>
    <div id="hph-asset-debug" style="position: fixed; bottom: 10px; right: 10px; background: #000; color: #0f0; padding: 10px; font-family: monospace; font-size: 12px; max-width: 400px; max-height: 300px; overflow: auto; z-index: 99999; border: 1px solid #0f0; opacity: 0.9;">
        <div style="margin-bottom: 10px; border-bottom: 1px solid #0f0; padding-bottom: 5px;">
            <strong>HPH Asset Loading Debug</strong>
            <button onclick="document.getElementById('hph-asset-debug').style.display='none'" style="float: right; background: #f00; color: #fff; border: none; padding: 2px 5px; cursor: pointer;">X</button>
        </div>
        
        <div style="margin-bottom: 10px;">
            <strong>Page Context:</strong>
            <ul style="margin: 5px 0; padding-left: 20px;">
                <?php foreach ($page_context as $key => $value): ?>
                    <?php if (is_bool($value)): ?>
                        <?php if ($value): ?>
                            <li style="color: #0f0;"><?php echo esc_html($key); ?>: ✓</li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li><?php echo esc_html($key); ?>: <?php echo esc_html($value); ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <div>
            <strong>Loaded Asset Groups (<?php echo count($loaded_assets); ?>):</strong>
            <ul style="margin: 5px 0; padding-left: 20px;">
                <?php foreach ($loaded_assets as $asset): ?>
                    <li style="color: #ff0;"><?php echo esc_html($asset); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <div style="margin-top: 10px; font-size: 10px; color: #888;">
            <?php
            global $wp_styles, $wp_scripts;
            $style_count = is_object($wp_styles) ? count($wp_styles->queue) : 0;
            $script_count = is_object($wp_scripts) ? count($wp_scripts->queue) : 0;
            ?>
            Total: <?php echo $style_count; ?> CSS | <?php echo $script_count; ?> JS
        </div>
    </div>
    <?php
}

// Add to footer
add_action('wp_footer', 'hph_display_asset_debug', 999);

/**
 * Add console logging for asset loading
 */
function hph_add_asset_console_logging() {
    if (!current_user_can('manage_options') || !defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }
    
    $theme = HPH_Theme::instance();
    $assets_service = $theme->get_service('assets');
    
    if (!$assets_service) {
        return;
    }
    
    $loaded_assets = $assets_service->get_loaded_assets();
    $page_context = $assets_service->get_page_context();
    
    ?>
    <script>
    console.group('%c📦 HPH Asset Loading', 'color: #2563eb; font-weight: bold; font-size: 14px;');
    console.log('Page Context:', <?php echo json_encode($page_context); ?>);
    console.log('Loaded Asset Groups:', <?php echo json_encode($loaded_assets); ?>);
    console.log('Total Assets:', {
        css: document.querySelectorAll('link[rel="stylesheet"]').length,
        js: document.querySelectorAll('script[src]').length
    });
    console.groupEnd();
    </script>
    <?php
}
add_action('wp_footer', 'hph_add_asset_console_logging', 1000);