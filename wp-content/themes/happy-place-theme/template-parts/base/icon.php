<?php
/**
 * Base Icon Component
 * Simple icon display with SVG support
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Icon component configuration
 */
$defaults = [
    'name' => 'default',
    'size' => '24',
    'color' => 'currentColor',
    'class' => '',
    'aria_label' => '',
    'title' => '',
];

$props = wp_parse_args(hph_get_arg() ?? [], $defaults);

// Icon library - basic set for demonstration
$icons = [
    'home' => '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4m8 6h7"/>',
    'user' => '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2" fill="none"/>',
    'search' => '<circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2" fill="none"/><path d="m21 21-4.35-4.35" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>',
    'chevron-down' => '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/>',
    'chevron-left' => '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 18-6-6 6-6"/>',
    'chevron-right' => '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6"/>',
    'x' => '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 6 6 18"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 6 12 12"/>',
    'plus' => '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"/>',
    'minus' => '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"/>',
    'check' => '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 6 9 17l-5-5"/>',
    'star' => '<polygon points="12,2 15,8 22,9 17,14 18,21 12,18 6,21 7,14 2,9 9,8" stroke="currentColor" stroke-width="2" fill="none"/>',
    'heart' => '<path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" stroke="currentColor" stroke-width="2" fill="none"/>',
    'building' => '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 21V7l8-4v18"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V11l-6-4"/>',
    'map' => '<polygon points="3,6 9,3 15,6 21,3 21,18 15,21 9,18 3,21" stroke="currentColor" stroke-width="2" fill="none"/><line x1="9" y1="3" x2="9" y2="18" stroke="currentColor" stroke-width="2"/><line x1="15" y1="6" x2="15" y2="21" stroke="currentColor" stroke-width="2"/>',
    'microphone' => '<path d="M12 1a4 4 0 0 0-4 4v7a4 4 0 0 0 8 0V5a4 4 0 0 0-4-4z" stroke="currentColor" stroke-width="2" fill="none"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23" stroke="currentColor" stroke-width="2"/>',
    'scan' => '<rect x="3" y="3" width="5" height="5" stroke="currentColor" stroke-width="2" fill="none"/><rect x="16" y="3" width="5" height="5" stroke="currentColor" stroke-width="2" fill="none"/><rect x="3" y="16" width="5" height="5" stroke="currentColor" stroke-width="2" fill="none"/><rect x="16" y="16" width="5" height="5" stroke="currentColor" stroke-width="2" fill="none"/>',
    'filter' => '<polygon points="22,3 2,3 10,12.46 10,19 14,21 14,12.46" stroke="currentColor" stroke-width="2" fill="none"/>',
    'default' => '<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 12 2 2 4-4"/>'
];

// Get the icon path
$icon_path = $icons[$props['name']] ?? $icons['default'];

// Build classes
$classes = ['hph-icon'];
if (!empty($props['class'])) {
    $classes[] = $props['class'];
}

// Build attributes
$attributes = [
    'class' => implode(' ', $classes),
    'width' => $props['size'],
    'height' => $props['size'],
    'viewBox' => '0 0 24 24',
    'fill' => 'none',
    'xmlns' => 'http://www.w3.org/2000/svg'
];

if ($props['aria_label']) {
    $attributes['aria-label'] = $props['aria_label'];
} else {
    $attributes['aria-hidden'] = 'true';
}

if ($props['title']) {
    $show_title = true;
}
?>

<svg
    <?php foreach ($attributes as $key => $value): ?>
        <?php echo esc_attr($key); ?>="<?php echo esc_attr($value); ?>"
    <?php endforeach; ?>
>
    <?php if (!empty($props['title'])): ?>
        <title><?php echo esc_html($props['title']); ?></title>
    <?php endif; ?>
    <?php echo $icon_path; ?>
</svg>
