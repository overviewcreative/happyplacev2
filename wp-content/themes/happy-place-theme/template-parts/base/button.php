<?php
/**
 * Base Button Component
 * 
 * Pure UI button component with extensive variations
 * No data dependencies, just presentation
 * 
 * @package HappyPlaceTheme
 * @subpackage Components/Base
 * @since 3.0.0
 */

$props = wp_parse_args(get_query_var('args', array()), array(
    // Content
    'text' => '',
    'icon' => '', // Icon identifier (e.g., 'arrow-right', 'download', 'heart')
    'icon_position' => 'left', // left, right, only
    
    // Appearance
    'variant' => 'primary', // primary, secondary, outline, ghost, danger, success, warning, info, dark, light
    'size' => 'md', // xs, sm, md, lg, xl
    'shape' => 'default', // default, rounded, pill, square
    'width' => 'auto', // auto, full, fixed
    
    // State
    'state' => 'default', // default, hover, active, disabled, loading
    'disabled' => false,
    'loading' => false,
    'selected' => false,
    
    // Behavior
    'type' => 'button', // button, submit, reset, link
    'href' => '',
    'target' => '_self',
    'download' => false,
    
    // Advanced
    'dropdown' => false, // Shows dropdown arrow
    'badge' => '', // Badge text/number
    'tooltip' => '', // Tooltip text
    'hotkey' => '', // Keyboard shortcut display
    
    // HTML
    'id' => '',
    'class' => '',
    'attributes' => array(),
    'data' => array() // Data attributes
));

// Build button classes
$btn_classes = array(
    'hph-btn',
    'hph-btn--' . $props['variant'],
    'hph-btn--' . $props['size'],
    'hph-btn--' . $props['shape']
);

if ($props['width'] !== 'auto') {
    $btn_classes[] = 'hph-btn--' . $props['width'];
}

if ($props['loading']) {
    $btn_classes[] = 'is-loading';
}

if ($props['selected']) {
    $btn_classes[] = 'is-selected';
}

if ($props['disabled']) {
    $btn_classes[] = 'is-disabled';
}

if ($props['icon'] && !$props['text']) {
    $btn_classes[] = 'hph-btn--icon-only';
}

if ($props['dropdown']) {
    $btn_classes[] = 'hph-btn--dropdown';
}

if ($props['class']) {
    $btn_classes[] = $props['class'];
}

// Determine element type
$element = 'button';
if ($props['type'] === 'link' || $props['href']) {
    $element = 'a';
}

// Build attributes
$attributes = array(
    'class' => implode(' ', $btn_classes)
);

if ($props['id']) {
    $attributes['id'] = $props['id'];
}

if ($element === 'button') {
    $attributes['type'] = $props['type'] !== 'link' ? $props['type'] : 'button';
    if ($props['disabled']) {
        $attributes['disabled'] = 'disabled';
    }
} else {
    $attributes['href'] = $props['href'] ?: '#';
    if ($props['target'] !== '_self') {
        $attributes['target'] = $props['target'];
    }
    if ($props['download']) {
        $attributes['download'] = is_string($props['download']) ? $props['download'] : '';
    }
    if ($props['disabled']) {
        $attributes['aria-disabled'] = 'true';
    }
}

if ($props['tooltip']) {
    $attributes['title'] = $props['tooltip'];
    $attributes['data-tooltip'] = $props['tooltip'];
}

if ($props['hotkey']) {
    $attributes['data-hotkey'] = $props['hotkey'];
}

// Add data attributes
foreach ($props['data'] as $key => $value) {
    $attributes['data-' . $key] = $value;
}

// Add custom attributes
foreach ($props['attributes'] as $key => $value) {
    $attributes[$key] = $value;
}

?>

<<?php echo $element; ?> <?php hph_render_attributes($attributes); ?>>
    <span class="hph-btn__inner">
        <?php if ($props['loading']): ?>
            <span class="hph-btn__loader" aria-hidden="true"></span>
        <?php endif; ?>
        
        <?php if ($props['icon'] && $props['icon_position'] === 'left'): ?>
            <span class="hph-btn__icon hph-btn__icon--left" data-icon="<?php echo esc_attr($props['icon']); ?>"></span>
        <?php endif; ?>
        
        <?php if ($props['text']): ?>
            <span class="hph-btn__text"><?php echo esc_html($props['text']); ?></span>
        <?php elseif ($props['icon'] && $props['icon_position'] === 'only'): ?>
            <span class="hph-btn__icon" data-icon="<?php echo esc_attr($props['icon']); ?>"></span>
        <?php endif; ?>
        
        <?php if ($props['icon'] && $props['icon_position'] === 'right'): ?>
            <span class="hph-btn__icon hph-btn__icon--right" data-icon="<?php echo esc_attr($props['icon']); ?>"></span>
        <?php endif; ?>
        
        <?php if ($props['badge']): ?>
            <span class="hph-btn__badge"><?php echo esc_html($props['badge']); ?></span>
        <?php endif; ?>
        
        <?php if ($props['dropdown']): ?>
            <span class="hph-btn__dropdown" data-icon="chevron-down"></span>
        <?php endif; ?>
        
        <?php if ($props['hotkey']): ?>
            <kbd class="hph-btn__hotkey"><?php echo esc_html($props['hotkey']); ?></kbd>
        <?php endif; ?>
    </span>
</<?php echo $element; ?>>
