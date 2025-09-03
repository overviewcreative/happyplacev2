<?php
/**
 * Base Badge Component
 * 
 * Pure UI badge/label component for status indicators, counts, etc.
 * 
 * @package HappyPlaceTheme
 * @subpackage Components/Base
 * @since 3.0.0
 */

$props = wp_parse_args(get_query_var('args', array()), array(
    // Content
    'text' => '',
    'icon' => '',
    'count' => '', // For numeric badges
    
    // Appearance
    'variant' => 'default', // default, primary, secondary, success, warning, danger, info, dark, light
    'style' => 'solid', // solid, outline, soft (soft background)
    'size' => 'md', // xs, sm, md, lg
    'shape' => 'default', // default, pill, square, circle
    
    // Position (when used as overlay)
    'position' => '', // top-left, top-right, bottom-left, bottom-right, top-center, bottom-center
    'offset' => 'default', // default, outside (positioned outside parent bounds)
    
    // Behavior
    'removable' => false, // Shows x button
    'clickable' => false, // Makes badge clickable
    'animated' => false, // Pulse or bounce animation
    'animation' => 'pulse', // pulse, bounce, shake
    
    // State
    'active' => false,
    'hidden' => false,
    
    // HTML
    'id' => '',
    'class' => '',
    'attributes' => array(),
    'href' => '', // If clickable
    'title' => ''
));

// Build badge classes
$badge_classes = array(
    'hph-badge',
    'hph-badge--' . $props['variant'],
    'hph-badge--' . $props['style'],
    'hph-badge--' . $props['size'],
    'hph-badge--' . $props['shape']
);

if ($props['position']) {
    $badge_classes[] = 'hph-badge--positioned';
    $badge_classes[] = 'hph-badge--' . $props['position'];
    
    if ($props['offset'] === 'outside') {
        $badge_classes[] = 'hph-badge--outside';
    }
}

if ($props['removable']) {
    $badge_classes[] = 'hph-badge--removable';
}

if ($props['clickable'] || $props['href']) {
    $badge_classes[] = 'hph-badge--clickable';
}

if ($props['animated']) {
    $badge_classes[] = 'hph-badge--animated';
    $badge_classes[] = 'hph-badge--' . $props['animation'];
}

if ($props['active']) {
    $badge_classes[] = 'is-active';
}

if ($props['hidden']) {
    $badge_classes[] = 'is-hidden';
}

if (!$props['text'] && $props['count']) {
    $badge_classes[] = 'hph-badge--count';
}

if ($props['icon'] && !$props['text'] && !$props['count']) {
    $badge_classes[] = 'hph-badge--icon-only';
}

if ($props['class']) {
    $badge_classes[] = $props['class'];
}

// Determine element
$element = 'span';
if ($props['href']) {
    $element = 'a';
} elseif ($props['clickable']) {
    $element = 'button';
}

// Build attributes
$attributes = array(
    'class' => implode(' ', $badge_classes)
);

if ($props['id']) {
    $attributes['id'] = $props['id'];
}

if ($props['title']) {
    $attributes['title'] = $props['title'];
}

if ($element === 'a') {
    $attributes['href'] = $props['href'];
} elseif ($element === 'button') {
    $attributes['type'] = 'button';
}

// Add custom attributes
foreach ($props['attributes'] as $key => $value) {
    $attributes[$key] = $value;
}

?>

<<?php echo $element; ?> <?php hph_render_attributes($attributes); ?>>
    <?php if ($props['icon']): ?>
        <span class="hph-badge__icon" data-icon="<?php echo esc_attr($props['icon']); ?>"></span>
    <?php endif; ?>
    
    <?php if ($props['text']): ?>
        <span class="hph-badge__text"><?php echo esc_html($props['text']); ?></span>
    <?php elseif ($props['count']): ?>
        <span class="hph-badge__count"><?php echo esc_html($props['count']); ?></span>
    <?php endif; ?>
    
    <?php if ($props['removable']): ?>
        <button type="button" class="hph-badge__remove" aria-label="Remove">
            <span data-icon="x"></span>
        </button>
    <?php endif; ?>
</<?php echo $element; ?>>