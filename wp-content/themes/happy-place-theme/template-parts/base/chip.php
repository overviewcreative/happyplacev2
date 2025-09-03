<?php
/**
 * Base Chip Component
 * Small, interactive elements for tags, filters, and selections
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Chip component configuration
 */
$defaults = [
    // Content
    'text' => 'Chip',
    'value' => '', // Hidden value for forms
    'count' => null, // Optional count/number to display
    
    // Visual variants
    'variant' => 'default', // default, primary, secondary, success, warning, error, info
    'style' => 'filled', // filled, outlined, soft, minimal
    'size' => 'md', // xs, sm, md, lg
    
    // Interactive states
    'selectable' => false, // Can be selected/deselected
    'selected' => false, // Currently selected state
    'removable' => false, // Show remove/close button
    'clickable' => true, // Is clickable/interactive
    'disabled' => false,
    
    // Visual features
    'rounded' => true,
    'shadow' => false,
    'border' => false,
    
    // Icons
    'icon_left' => '', // Icon on the left
    'icon_right' => '', // Icon on the right
    'avatar' => '', // Avatar image URL
    'avatar_alt' => '', // Avatar alt text
    
    // Behavior
    'href' => '', // Make chip a link
    'target' => '', // Link target
    'toggle' => false, // Toggle selection on click
    'name' => '', // Form input name
    'form_value' => '', // Value to submit with form
    
    // Events
    'on_click' => '',
    'on_select' => '',
    'on_remove' => '',
    
    // Accessibility
    'aria_label' => '',
    'aria_pressed' => null, // For toggle buttons
    'role' => '', // Custom ARIA role
    
    // CSS classes
    'container_class' => '',
    'chip_class' => '',
    
    // Data attributes
    'data_attributes' => [],
];

$props = wp_parse_args($props ?? [], $defaults);

// Generate unique ID
$chip_id = $props['id'] ?? 'hph-chip-' . wp_unique_id();

// Determine element type
$element = 'span';
if ($props['href']) {
    $element = 'a';
} elseif ($props['clickable'] || $props['selectable'] || $props['on_click']) {
    $element = 'button';
}

// Build CSS classes
$chip_classes = [
    'hph-chip',
    'hph-chip--' . $props['variant'],
    'hph-chip--' . $props['style'],
    'hph-chip--' . $props['size'],
];

if ($props['selectable']) {
    $chip_classes[] = 'hph-chip--selectable';
}

if ($props['selected']) {
    $chip_classes[] = 'hph-chip--selected';
}

if ($props['removable']) {
    $chip_classes[] = 'hph-chip--removable';
}

if ($props['clickable']) {
    $chip_classes[] = 'hph-chip--clickable';
}

if ($props['disabled']) {
    $chip_classes[] = 'hph-chip--disabled';
}

if ($props['rounded']) {
    $chip_classes[] = 'hph-chip--rounded';
}

if ($props['shadow']) {
    $chip_classes[] = 'hph-chip--shadow';
}

if ($props['border']) {
    $chip_classes[] = 'hph-chip--border';
}

if ($props['icon_left'] || $props['avatar']) {
    $chip_classes[] = 'hph-chip--with-icon-left';
}

if ($props['icon_right']) {
    $chip_classes[] = 'hph-chip--with-icon-right';
}

if ($props['count'] !== null) {
    $chip_classes[] = 'hph-chip--with-count';
}

if (!empty($props['chip_class'])) {
    $chip_classes[] = $props['chip_class'];
}

// Container classes (if needed)
$container_classes = [
    'hph-chip-container',
];

if (!empty($props['container_class'])) {
    $container_classes[] = $props['container_class'];
}

// Build attributes
$attributes = [
    'id' => $chip_id,
    'class' => implode(' ', $chip_classes),
];

if ($element === 'button') {
    $attributes['type'] = 'button';
    
    if ($props['disabled']) {
        $attributes['disabled'] = 'disabled';
    }
    
    if ($props['selectable'] && $props['aria_pressed'] !== null) {
        $attributes['aria-pressed'] = $props['selected'] ? 'true' : 'false';
    }
    
    if ($props['toggle']) {
        $attributes['data-toggle'] = 'true';
    }
}

if ($element === 'a') {
    $attributes['href'] = $props['href'];
    
    if ($props['target']) {
        $attributes['target'] = $props['target'];
    }
    
    if ($props['target'] === '_blank') {
        $attributes['rel'] = 'noopener noreferrer';
    }
}

if ($props['aria_label']) {
    $attributes['aria-label'] = $props['aria_label'];
}

if ($props['role']) {
    $attributes['role'] = $props['role'];
}

// Data attributes
$data_attrs = [
    'data-chip-variant' => $props['variant'],
    'data-chip-style' => $props['style'],
    'data-chip-size' => $props['size'],
];

if ($props['value']) {
    $data_attrs['data-value'] = $props['value'];
}

if ($props['selectable']) {
    $data_attrs['data-selectable'] = 'true';
}

if ($props['selected']) {
    $data_attrs['data-selected'] = 'true';
}

if (!empty($props['data_attributes'])) {
    $data_attrs = array_merge($data_attrs, $props['data_attributes']);
}

// Event handlers
$events = [];

if ($props['on_click']) {
    $events['onclick'] = $props['on_click'];
}

if ($props['on_select']) {
    $events['data-on-select'] = $props['on_select'];
}

if ($props['on_remove']) {
    $events['data-on-remove'] = $props['on_remove'];
}

// Merge all attributes
$all_attributes = array_merge($attributes, $data_attrs, $events);

// Helper function to build attribute string
function build_chip_attributes($attrs) {
    $output = [];
    foreach ($attrs as $key => $value) {
        if ($value !== null && $value !== '') {
            if ($value === true || in_array($key, ['disabled'])) {
                $output[] = esc_attr($key);
            } else {
                $output[] = esc_attr($key) . '="' . esc_attr($value) . '"';
            }
        }
    }
    return implode(' ', $output);
}
?>

<div class="<?php echo esc_attr(implode(' ', $container_classes)); ?>">
    <<?php echo $element; ?> <?php echo build_chip_attributes($all_attributes); ?>>
        <?php if ($props['avatar']): ?>
            <span class="hph-chip__avatar">
                <img 
                    src="<?php echo esc_url($props['avatar']); ?>" 
                    alt="<?php echo esc_attr($props['avatar_alt'] ?: $props['text']); ?>"
                    class="hph-chip__avatar-image"
                >
            </span>
        <?php elseif ($props['icon_left']): ?>
            <span class="hph-chip__icon hph-chip__icon--left">
                <?php
                get_template_part('template-parts/base/icon', null, [
                    'name' => $props['icon_left'],
                    'size' => $props['size'] === 'xs' ? 'xs' : 'sm'
                ]);
                ?>
            </span>
        <?php endif; ?>

        <span class="hph-chip__text">
            <?php echo esc_html($props['text']); ?>
        </span>

        <?php if ($props['count'] !== null): ?>
            <span class="hph-chip__count">
                <?php echo esc_html($props['count']); ?>
            </span>
        <?php endif; ?>

        <?php if ($props['icon_right']): ?>
            <span class="hph-chip__icon hph-chip__icon--right">
                <?php
                get_template_part('template-parts/base/icon', null, [
                    'name' => $props['icon_right'],
                    'size' => $props['size'] === 'xs' ? 'xs' : 'sm'
                ]);
                ?>
            </span>
        <?php endif; ?>

        <?php if ($props['removable']): ?>
            <button 
                type="button" 
                class="hph-chip__remove" 
                aria-label="Remove <?php echo esc_attr($props['text']); ?>"
                <?php if ($props['on_remove']): ?>
                    onclick="<?php echo esc_attr($props['on_remove']); ?>"
                <?php endif; ?>
            >
                <?php
                get_template_part('template-parts/base/icon', null, [
                    'name' => 'x',
                    'size' => 'xs'
                ]);
                ?>
            </button>
        <?php endif; ?>
    </<?php echo $element; ?>>

    <?php if ($props['selectable'] && $props['name']): ?>
        <input 
            type="hidden" 
            name="<?php echo esc_attr($props['name']); ?>" 
            value="<?php echo esc_attr($props['selected'] ? ($props['form_value'] ?: $props['value']) : ''); ?>"
            class="hph-chip__input"
        >
    <?php endif; ?>
</div>

<?php
/**
 * Usage Examples:
 * 
 * Simple chip:
 * get_template_part('template-parts/base/chip', null, [
 *     'text' => 'Downtown',
 *     'variant' => 'primary'
 * ]);
 * 
 * Removable tag:
 * get_template_part('template-parts/base/chip', null, [
 *     'text' => 'Swimming Pool',
 *     'removable' => true,
 *     'on_remove' => 'removeAmenity(this)'
 * ]);
 * 
 * Selectable filter chip:
 * get_template_part('template-parts/base/chip', null, [
 *     'text' => 'For Sale',
 *     'selectable' => true,
 *     'selected' => true,
 *     'name' => 'property_status',
 *     'value' => 'for-sale',
 *     'toggle' => true
 * ]);
 * 
 * Chip with count:
 * get_template_part('template-parts/base/chip', null, [
 *     'text' => 'Apartments',
 *     'count' => 24,
 *     'icon_left' => 'building'
 * ]);
 * 
 * Agent chip with avatar:
 * get_template_part('template-parts/base/chip', null, [
 *     'text' => 'John Doe',
 *     'avatar' => '/images/agents/john-doe.jpg',
 *     'avatar_alt' => 'John Doe',
 *     'href' => '/agents/john-doe',
 *     'style' => 'outlined'
 * ]);
 * 
 * Price range chip:
 * get_template_part('template-parts/base/chip', null, [
 *     'text' => '$200K - $500K',
 *     'icon_left' => 'dollar-sign',
 *     'removable' => true,
 *     'style' => 'soft',
 *     'variant' => 'success'
 * ]);
 */
?>
