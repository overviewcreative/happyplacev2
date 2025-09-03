<?php
/**
 * Base Grid Component
 * 
 * Pure UI responsive grid layout system
 * 
 * @package HappyPlaceTheme
 * @subpackage Components/Base
 * @since 3.0.0
 */

$props = wp_parse_args(get_query_var('args', array()), array(
    // Grid configuration
    'columns' => array(
        'default' => 1,
        'sm' => 1,
        'md' => 2,
        'lg' => 3,
        'xl' => 4,
        'xxl' => 4
    ),
    'rows' => 'auto', // auto, number, or array of sizes
    'gap' => 'md', // none, xs, sm, md, lg, xl, 2xl
    'gap_x' => '', // Override horizontal gap
    'gap_y' => '', // Override vertical gap
    
    // Layout type
    'type' => 'fixed', // fixed, fluid, auto-fit, auto-fill
    'min_column_width' => '250px', // For auto-fit/auto-fill
    'max_column_width' => '1fr', // For auto-fit/auto-fill
    
    // Alignment
    'align_items' => 'stretch', // start, end, center, stretch, baseline
    'justify_items' => 'stretch', // start, end, center, stretch
    'align_content' => 'start', // start, end, center, stretch, space-between, space-around, space-evenly
    'justify_content' => 'start', // start, end, center, stretch, space-between, space-around, space-evenly
    
    // Special layouts
    'masonry' => false, // Enable masonry layout
    'dense' => false, // Dense packing algorithm
    'template' => '', // Custom grid-template-areas
    
    // Items
    'items' => array(), // Array of items or HTML strings
    'item_wrapper' => true, // Wrap each item in grid item div
    'item_class' => '', // Additional class for each item
    
    // Responsive
    'stack_at' => '', // Breakpoint to stack to single column (sm, md, lg, xl)
    'reverse' => false, // Reverse item order
    'reverse_at' => '', // Breakpoint to reverse
    
    // HTML
    'tag' => 'div',
    'id' => '',
    'class' => '',
    'attributes' => array(),
    'style' => array() // Inline styles
));

// Build grid classes
$grid_classes = array(
    'hph-grid'
);

// Add type-specific classes
if ($props['type'] === 'fluid') {
    $grid_classes[] = 'hph-grid--fluid';
} elseif (in_array($props['type'], array('auto-fit', 'auto-fill'))) {
    $grid_classes[] = 'hph-grid--' . $props['type'];
} else {
    // Fixed column grid
    foreach ($props['columns'] as $breakpoint => $cols) {
        if ($breakpoint === 'default') {
            $grid_classes[] = 'hph-grid--cols-' . $cols;
        } else {
            $grid_classes[] = 'hph-grid--' . $breakpoint . '-cols-' . $cols;
        }
    }
}

// Gap classes
if ($props['gap'] !== 'none') {
    $grid_classes[] = 'hph-grid--gap-' . $props['gap'];
}
if ($props['gap_x']) {
    $grid_classes[] = 'hph-grid--gap-x-' . $props['gap_x'];
}
if ($props['gap_y']) {
    $grid_classes[] = 'hph-grid--gap-y-' . $props['gap_y'];
}

// Alignment classes
if ($props['align_items'] !== 'stretch') {
    $grid_classes[] = 'hph-grid--align-' . $props['align_items'];
}
if ($props['justify_items'] !== 'stretch') {
    $grid_classes[] = 'hph-grid--justify-' . $props['justify_items'];
}
if ($props['align_content'] !== 'start') {
    $grid_classes[] = 'hph-grid--content-' . $props['align_content'];
}
if ($props['justify_content'] !== 'start') {
    $grid_classes[] = 'hph-grid--place-' . $props['justify_content'];
}

// Special layout classes
if ($props['masonry']) {
    $grid_classes[] = 'hph-grid--masonry';
}
if ($props['dense']) {
    $grid_classes[] = 'hph-grid--dense';
}
if ($props['stack_at']) {
    $grid_classes[] = 'hph-grid--stack-' . $props['stack_at'];
}
if ($props['reverse']) {
    $grid_classes[] = 'hph-grid--reverse';
}
if ($props['reverse_at']) {
    $grid_classes[] = 'hph-grid--reverse-' . $props['reverse_at'];
}

if ($props['class']) {
    $grid_classes[] = $props['class'];
}

// Build inline styles
$inline_styles = array();

// Auto-fit/auto-fill styles
if (in_array($props['type'], array('auto-fit', 'auto-fill'))) {
    $inline_styles[] = '--grid-min-width: ' . $props['min_column_width'];
    $inline_styles[] = '--grid-max-width: ' . $props['max_column_width'];
}

// Custom template
if ($props['template']) {
    $inline_styles[] = 'grid-template-areas: ' . $props['template'];
}

// Custom row configuration
if ($props['rows'] !== 'auto') {
    if (is_array($props['rows'])) {
        $inline_styles[] = 'grid-template-rows: ' . implode(' ', $props['rows']);
    } else {
        $inline_styles[] = 'grid-auto-rows: ' . $props['rows'];
    }
}

// Additional custom styles
foreach ($props['style'] as $property => $value) {
    $inline_styles[] = $property . ': ' . $value;
}

// Build attributes
$attributes = array(
    'class' => implode(' ', $grid_classes)
);

if ($props['id']) {
    $attributes['id'] = $props['id'];
}

if (!empty($inline_styles)) {
    $attributes['style'] = implode('; ', $inline_styles);
}

foreach ($props['attributes'] as $key => $value) {
    $attributes[$key] = $value;
}

?>

<<?php echo $props['tag']; ?> <?php hph_render_attributes($attributes); ?>>
    <?php 
    if (!empty($props['items'])):
        foreach ($props['items'] as $index => $item):
            // Check if item has specific grid placement
            $item_attrs = array();
            $item_classes = array('hph-grid__item');
            
            if (is_array($item) && isset($item['content'])) {
                // Item with configuration
                if (isset($item['span'])) {
                    $item_classes[] = 'hph-grid__item--span-' . $item['span'];
                }
                if (isset($item['span_row'])) {
                    $item_classes[] = 'hph-grid__item--row-span-' . $item['span_row'];
                }
                if (isset($item['start'])) {
                    $item_classes[] = 'hph-grid__item--start-' . $item['start'];
                }
                if (isset($item['end'])) {
                    $item_classes[] = 'hph-grid__item--end-' . $item['end'];
                }
                if (isset($item['area'])) {
                    $item_attrs['style'] = 'grid-area: ' . $item['area'];
                }
                if (isset($item['class'])) {
                    $item_classes[] = $item['class'];
                }
                
                $content = $item['content'];
            } else {
                // Simple content
                $content = $item;
            }
            
            if ($props['item_class']) {
                $item_classes[] = $props['item_class'];
            }
            
            $item_attrs['class'] = implode(' ', $item_classes);
            
            if ($props['item_wrapper']):
    ?>
        <div <?php hph_render_attributes($item_attrs); ?>>
            <?php echo $content; ?>
        </div>
    <?php 
            else:
                echo $content;
            endif;
        endforeach;
    endif;
    ?>
</<?php echo $props['tag']; ?>>