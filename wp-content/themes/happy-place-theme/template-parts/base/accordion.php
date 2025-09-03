<?php
/**
 * Base Accordion Component
 * 
 * Pure UI accordion component for collapsible content sections
 * No data dependencies, just presentation
 * 
 * @package HappyPlaceTheme
 * @subpackage Components/Base
 * @since 3.0.0
 */

// Get component arguments
$props = wp_parse_args(hph_get_arg(), array(
    // Content
    'items' => array(), // Array of accordion items with 'title', 'content', 'open' keys
    
    // Appearance
    'variant' => 'default', // default, bordered, minimal, filled, flush
    'size' => 'md', // sm, md, lg
    'icon_position' => 'right', // left, right, none
    'icon_type' => 'chevron', // chevron, plus, arrow, caret
    
    // Behavior
    'allow_multiple' => false, // Allow multiple panels open
    'collapse_all' => true, // Allow all panels to be closed
    'animate' => true, // Animation on open/close
    'initial_open' => 0, // Index of initially open item (false for none)
    
    // Advanced
    'searchable' => false, // Add search functionality
    'nested' => false, // Support nested accordions
    'keyboard_nav' => true, // Keyboard navigation support
    
    // HTML
    'id' => '',
    'class' => '',
    'attributes' => array(),
    'data' => array()
));

// Validate items
if (empty($props['items']) || !is_array($props['items'])) {
    return;
}

// Generate unique ID if not provided
if (empty($props['id'])) {
    $props['id'] = 'hph-accordion-' . uniqid();
}

// Build accordion classes
$accordion_classes = array(
    'hph-accordion',
    'hph-accordion--' . $props['variant'],
    'hph-accordion--' . $props['size'],
    'hph-accordion--icon-' . $props['icon_position']
);

if ($props['animate']) {
    $accordion_classes[] = 'hph-accordion--animated';
}

if ($props['searchable']) {
    $accordion_classes[] = 'hph-accordion--searchable';
}

if ($props['nested']) {
    $accordion_classes[] = 'hph-accordion--nested';
}

if (!empty($props['class'])) {
    $accordion_classes[] = $props['class'];
}

// Prepare data attributes
$data_attrs = array_merge(array(
    'allow-multiple' => $props['allow_multiple'] ? 'true' : 'false',
    'collapse-all' => $props['collapse_all'] ? 'true' : 'false',
    'keyboard-nav' => $props['keyboard_nav'] ? 'true' : 'false'
), $props['data']);

// Build attributes string
$attributes = array();
foreach ($props['attributes'] as $key => $value) {
    $attributes[] = esc_attr($key) . '="' . esc_attr($value) . '"';
}
foreach ($data_attrs as $key => $value) {
    $attributes[] = 'data-' . esc_attr($key) . '="' . esc_attr($value) . '"';
}
$attributes_string = implode(' ', $attributes);
?>

<div 
    id="<?php echo esc_attr($props['id']); ?>"
    class="<?php echo esc_attr(implode(' ', $accordion_classes)); ?>"
    <?php echo $attributes_string; ?>
    role="tablist"
    aria-multiselectable="<?php echo $props['allow_multiple'] ? 'true' : 'false'; ?>"
>
    
    <?php if ($props['searchable']): ?>
    <div class="hph-accordion__search">
        <input 
            type="search" 
            class="hph-accordion__search-input"
            placeholder="<?php esc_attr_e('Search accordion items...', 'happy-place-theme'); ?>"
            aria-label="<?php esc_attr_e('Search accordion', 'happy-place-theme'); ?>"
        >
    </div>
    <?php endif; ?>
    
    <?php foreach ($props['items'] as $index => $item): 
        $is_open = (
            (is_numeric($props['initial_open']) && $props['initial_open'] === $index) ||
            (!empty($item['open']) && $item['open'])
        );
        
        $item_id = $props['id'] . '-item-' . $index;
        $panel_id = $props['id'] . '-panel-' . $index;
        
        // Item classes
        $item_classes = array('hph-accordion__item');
        if ($is_open) {
            $item_classes[] = 'hph-accordion__item--open';
        }
        if (!empty($item['class'])) {
            $item_classes[] = $item['class'];
        }
    ?>
    
    <div class="<?php echo esc_attr(implode(' ', $item_classes)); ?>" data-index="<?php echo esc_attr($index); ?>">
        
        <button
            id="<?php echo esc_attr($item_id); ?>"
            class="hph-accordion__trigger"
            type="button"
            aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>"
            aria-controls="<?php echo esc_attr($panel_id); ?>"
            role="tab"
        >
            <span class="hph-accordion__title">
                <?php echo wp_kses_post($item['title'] ?? ''); ?>
            </span>
            
            <?php if ($props['icon_position'] !== 'none'): ?>
            <span class="hph-accordion__icon" data-icon="<?php echo esc_attr($props['icon_type']); ?>" aria-hidden="true"></span>
            <?php endif; ?>
        </button>
        
        <div
            id="<?php echo esc_attr($panel_id); ?>"
            class="hph-accordion__panel"
            role="tabpanel"
            aria-labelledby="<?php echo esc_attr($item_id); ?>"
            <?php if (!$is_open): ?>aria-hidden="true"<?php endif; ?>
        >
            <div class="hph-accordion__content">
                <?php echo wp_kses_post($item['content'] ?? ''); ?>
            </div>
        </div>
        
    </div>
    
    <?php endforeach; ?>
    
</div>
