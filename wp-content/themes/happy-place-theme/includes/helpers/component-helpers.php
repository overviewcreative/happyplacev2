<?php
/**
 * Component Helper Functions
 * 
 * Helper functions to integrate base components with section templates
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render a form component within a section
 * 
 * @param array $config Form configuration
 * @return string Rendered form HTML
 */
if (!function_exists('hph_render_section_form')) {
    function hph_render_section_form($config = array()) {
    $defaults = array(
        'title' => '',
        'description' => '',
        'fields' => array(),
        'button_text' => 'Submit',
        'button_style' => 'primary',
        'button_size' => 'lg',
        'layout' => 'vertical', // vertical, horizontal, inline
        'spacing' => 'lg',
        'background' => 'white',
        'form_id' => '',
        'action' => '',
        'method' => 'POST'
    );
    
    $config = wp_parse_args($config, $defaults);
    
    // Generate form ID if not provided
    if (empty($config['form_id'])) {
        $config['form_id'] = 'hph-form-' . uniqid();
    }
    
    // Build form classes
    $form_classes = array(
        'hph-section-form',
        'hph-form-layout-' . $config['layout'],
        'hph-form-spacing-' . $config['spacing'],
        'hph-form-bg-' . $config['background']
    );
    
    ob_start();
    ?>
    <div class="hph-section-form-wrapper">
        
        <?php if ($config['title'] || $config['description']): ?>
        <div class="hph-form-header hph-mb-2xl hph-text-center">
            <?php if ($config['title']): ?>
            <h3 class="hph-form-title hph-text-2xl hph-font-semibold hph-mb-md">
                <?php echo esc_html($config['title']); ?>
            </h3>
            <?php endif; ?>
            
            <?php if ($config['description']): ?>
            <p class="hph-form-description hph-text-gray-600 hph-text-lg">
                <?php echo esc_html($config['description']); ?>
            </p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <form 
            id="<?php echo esc_attr($config['form_id']); ?>"
            class="<?php echo esc_attr(implode(' ', $form_classes)); ?>"
            <?php if ($config['action']): ?>action="<?php echo esc_url($config['action']); ?>"<?php endif; ?>
            method="<?php echo esc_attr($config['method']); ?>"
        >
            <div class="hph-form-fields hph-space-y-<?php echo esc_attr($config['spacing']); ?>">
                
                <?php foreach ($config['fields'] as $field): ?>
                    <?php
                    // Set default field properties for section forms
                    $field_defaults = array(
                        'variant' => 'outlined',
                        'size' => 'lg',
                        'width' => 'full'
                    );
                    $field = wp_parse_args($field, $field_defaults);
                    
                    // Render field using our form component
                    set_query_var('args', $field);
                    get_template_part('template-parts/base/form-input');
                    ?>
                <?php endforeach; ?>
                
            </div>
            
            <div class="hph-form-actions hph-mt-2xl hph-text-center">
                <button 
                    type="submit" 
                    class="hph-btn hph-btn-<?php echo esc_attr($config['button_style']); ?> hph-btn-<?php echo esc_attr($config['button_size']); ?> hph-w-full hph-md:w-auto hph-px-2xl"
                >
                    <?php echo esc_html($config['button_text']); ?>
                </button>
            </div>
        </form>
        
    </div>
    <?php
    return ob_get_clean();
    }
}

/**
 * Render an accordion component within a section
 * 
 * @param array $config Accordion configuration
 * @return string Rendered accordion HTML
 */
if (!function_exists('hph_render_section_accordion')) {
    function hph_render_section_accordion($config = array()) {
    $defaults = array(
        'items' => array(),
        'variant' => 'default',
        'size' => 'lg',
        'allow_multiple' => false,
        'searchable' => false,
        'initial_open' => 0,
        'icon_type' => 'chevron'
    );
    
    $config = wp_parse_args($config, $defaults);
    
    // Set up component args
    set_query_var('hph_component_args', $config);
    
    ob_start();
    get_template_part('template-parts/base/accordion');
    return ob_get_clean();
    }
}

/**
 * Render stats counter within a section
 * 
 * @param array $stats Array of stat items
 * @param array $config Stats configuration
 * @return string Rendered stats HTML
 */
if (!function_exists('hph_render_section_stats')) {
    function hph_render_section_stats($stats = array(), $config = array()) {
    $defaults = array(
        'layout' => 'row', // row, grid, vertical
        'columns' => 4,
        'animation' => true,
        'size' => 'lg'
    );
    
    $config = wp_parse_args($config, $defaults);
    
    if (empty($stats)) {
        return '';
    }
    
    // Build stats classes
    $stats_classes = array(
        'hph-stats-wrapper',
        'hph-stats-layout-' . $config['layout'],
        'hph-stats-size-' . $config['size']
    );
    
    if ($config['layout'] === 'grid' || $config['layout'] === 'row') {
        $stats_classes[] = 'hph-grid';
        switch ($config['columns']) {
            case 2:
                $stats_classes[] = 'hph-sm:grid-cols-2';
                break;
            case 3:
                $stats_classes[] = 'hph-sm:grid-cols-2 hph-lg:grid-cols-3';
                break;
            case 4:
            default:
                $stats_classes[] = 'hph-sm:grid-cols-2 hph-lg:grid-cols-4';
                break;
        }
        $stats_classes[] = 'hph-gap-xl';
    } else {
        $stats_classes[] = 'hph-space-y-xl';
    }
    
    ob_start();
    ?>
    <div class="<?php echo esc_attr(implode(' ', $stats_classes)); ?>">
        <?php foreach ($stats as $index => $stat): ?>
        <div class="hph-stat-item hph-text-center <?php echo $config['animation'] ? 'hph-animate-fade-in-up' : ''; ?>" 
             <?php if ($config['animation']): ?>style="animation-delay: <?php echo $index * 150; ?>ms;"<?php endif; ?>>
            <div class="hph-stat-number hph-text-4xl hph-md:text-5xl hph-font-bold hph-text-primary hph-mb-sm">
                <?php echo esc_html($stat['number']); ?>
                <?php if (!empty($stat['suffix'])): ?>
                <span class="hph-text-2xl hph-text-gray-600"><?php echo esc_html($stat['suffix']); ?></span>
                <?php endif; ?>
            </div>
            <div class="hph-stat-label hph-text-lg hph-font-medium hph-text-gray-700 hph-mb-xs">
                <?php echo esc_html($stat['label']); ?>
            </div>
            <?php if (!empty($stat['description'])): ?>
            <p class="hph-stat-description hph-text-sm hph-text-gray-600">
                <?php echo esc_html($stat['description']); ?>
            </p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
    }
}

/**
 * Render card grid within a section
 * 
 * @param array $items Array of card items
 * @param array $config Grid configuration
 * @return string Rendered card grid HTML
 */
if (!function_exists('hph_render_section_card_grid')) {
    function hph_render_section_card_grid($items = array(), $config = array()) {
    $defaults = array(
        'columns' => 3,
        'card_style' => 'default', // default, hover-lift, border, shadow, minimal
        'image_aspect' => '16:9', // 16:9, 4:3, 1:1, 3:2
        'animation' => true,
        'gap' => 'xl'
    );
    
    $config = wp_parse_args($config, $defaults);
    
    if (empty($items)) {
        return '';
    }
    
    // Build grid classes
    $grid_classes = array(
        'hph-card-grid',
        'hph-grid',
        'hph-gap-' . $config['gap']
    );
    
    switch ($config['columns']) {
        case 2:
            $grid_classes[] = 'hph-sm:grid-cols-2';
            break;
        case 4:
            $grid_classes[] = 'hph-sm:grid-cols-2 hph-lg:grid-cols-4';
            break;
        case 3:
        default:
            $grid_classes[] = 'hph-sm:grid-cols-2 hph-lg:grid-cols-3';
            break;
    }
    
    // Card base classes
    $card_base_classes = array(
        'hph-card',
        'hph-card-' . $config['card_style'],
        'hph-bg-white',
        'hph-rounded-lg',
        'hph-overflow-hidden',
        'hph-transition-all',
        'hph-duration-300'
    );
    
    if ($config['card_style'] === 'hover-lift') {
        $card_base_classes[] = 'hph-hover-lift';
        $card_base_classes[] = 'hph-shadow-md';
        $card_base_classes[] = 'hph-hover:shadow-lg';
    } elseif ($config['card_style'] === 'shadow') {
        $card_base_classes[] = 'hph-shadow-lg';
    } elseif ($config['card_style'] === 'border') {
        $card_base_classes[] = 'hph-border';
        $card_base_classes[] = 'hph-border-gray-200';
    }
    
    ob_start();
    ?>
    <div class="<?php echo esc_attr(implode(' ', $grid_classes)); ?>">
        <?php foreach ($items as $index => $item): ?>
        <div class="<?php echo esc_attr(implode(' ', $card_base_classes)); ?> <?php echo $config['animation'] ? 'hph-animate-fade-in-up' : ''; ?>"
             <?php if ($config['animation']): ?>style="animation-delay: <?php echo $index * 150; ?>ms;"<?php endif; ?>>
            
            <?php if (!empty($item['image'])): ?>
            <div class="hph-card-image hph-aspect-<?php echo str_replace(':', '-', $config['image_aspect']); ?>">
                <img 
                    src="<?php echo esc_url($item['image']['url']); ?>" 
                    alt="<?php echo esc_attr($item['image']['alt'] ?? $item['title'] ?? ''); ?>"
                    class="hph-w-full hph-h-full hph-object-cover"
                    loading="lazy"
                >
            </div>
            <?php endif; ?>
            
            <div class="hph-card-content hph-p-xl">
                <?php if (!empty($item['title'])): ?>
                <h3 class="hph-card-title hph-text-xl hph-font-semibold hph-mb-md">
                    <?php echo esc_html($item['title']); ?>
                </h3>
                <?php endif; ?>
                
                <?php if (!empty($item['content'])): ?>
                <p class="hph-card-text hph-text-gray-600 hph-mb-lg">
                    <?php echo wp_kses_post($item['content']); ?>
                </p>
                <?php endif; ?>
                
                <?php if (!empty($item['link'])): ?>
                <a 
                    href="<?php echo esc_url($item['link']); ?>"
                    class="hph-inline-flex hph-items-center hph-text-primary hph-font-medium hph-hover-underline"
                >
                    <?php echo esc_html($item['link_text'] ?? 'Learn More'); ?>
                    <i class="fas fa-arrow-right hph-ml-sm hph-text-sm"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
    }
}

/**
 * Get component args helper (for component integration)
 */
if (!function_exists('hph_get_arg')) {
    function hph_get_arg() {
        return get_query_var('hph_component_args', array());
    }
}

/**
 * Render HTML attributes helper
 */
if (!function_exists('hph_render_attributes')) {
    function hph_render_attributes($attributes) {
        foreach ($attributes as $key => $value) {
            if ($value !== '' && $value !== null && $value !== false) {
                echo esc_attr($key) . '="' . esc_attr($value) . '" ';
            }
        }
    }
}