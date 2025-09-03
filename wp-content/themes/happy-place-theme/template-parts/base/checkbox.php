<?php
/**
 * Base Checkbox Component
 * Checkbox inputs for forms, filters, and multi-select options
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Checkbox component configuration
 */
$defaults = [
    // Basic configuration
    'checked' => false,
    'value' => '1',
    'name' => '',
    
    // Labels and text
    'label' => '',
    'label_position' => 'right', // left, right, top, bottom
    'description' => '',
    'helper_text' => '',
    
    // Style variants
    'variant' => 'default', // default, primary, success, warning, error, info
    'size' => 'md', // xs, sm, md, lg, xl
    'style' => 'checkbox', // checkbox, switch, button, card
    
    // Behavior
    'disabled' => false,
    'readonly' => false,
    'required' => false,
    'indeterminate' => false, // For partial selections
    
    // Visual features
    'icon' => 'check', // Icon to show when checked
    'rounded' => false,
    'bordered' => true,
    'shadow' => false,
    
    // Checkbox group options
    'is_group' => false,
    'group_options' => [], // Array of options for checkbox groups
    'group_layout' => 'vertical', // vertical, horizontal, grid
    'group_columns' => 2, // For grid layout
    
    // Events
    'on_change' => '',
    'on_check' => '',
    'on_uncheck' => '',
    
    // Accessibility
    'aria_label' => '',
    'aria_describedby' => '',
    'aria_labelledby' => '',
    
    // CSS classes
    'container_class' => '',
    'checkbox_class' => '',
    'label_class' => '',
    
    // Data attributes
    'data_attributes' => [],
];

$props = wp_parse_args($props ?? [], $defaults);

// Generate unique ID
$checkbox_id = $props['id'] ?? 'hph-checkbox-' . wp_unique_id();
$label_id = $checkbox_id . '-label';
$desc_id = $checkbox_id . '-desc';

// Build CSS classes
$container_classes = [
    'hph-checkbox-container',
    'hph-checkbox-container--' . $props['size'],
    'hph-checkbox-container--' . $props['style'],
    'hph-checkbox-container--label-' . $props['label_position'],
];

if ($props['disabled']) {
    $container_classes[] = 'hph-checkbox-container--disabled';
}

if ($props['is_group']) {
    $container_classes[] = 'hph-checkbox-container--group';
    $container_classes[] = 'hph-checkbox-container--' . $props['group_layout'];
}

if (!empty($props['container_class'])) {
    $container_classes[] = $props['container_class'];
}

$checkbox_classes = [
    'hph-checkbox',
    'hph-checkbox--' . $props['variant'],
    'hph-checkbox--' . $props['style'],
];

if ($props['checked']) {
    $checkbox_classes[] = 'hph-checkbox--checked';
}

if ($props['indeterminate']) {
    $checkbox_classes[] = 'hph-checkbox--indeterminate';
}

if ($props['rounded']) {
    $checkbox_classes[] = 'hph-checkbox--rounded';
}

if (!$props['bordered']) {
    $checkbox_classes[] = 'hph-checkbox--borderless';
}

if ($props['shadow']) {
    $checkbox_classes[] = 'hph-checkbox--shadow';
}

if (!empty($props['checkbox_class'])) {
    $checkbox_classes[] = $props['checkbox_class'];
}

$label_classes = [
    'hph-checkbox__label',
];

if (!empty($props['label_class'])) {
    $label_classes[] = $props['label_class'];
}

// Data attributes
$data_attrs = [
    'data-checkbox-size' => $props['size'],
    'data-checkbox-style' => $props['style'],
    'data-checkbox-variant' => $props['variant'],
];

if ($props['on_change']) {
    $data_attrs['data-on-change'] = $props['on_change'];
}

if (!empty($props['data_attributes'])) {
    $data_attrs = array_merge($data_attrs, $props['data_attributes']);
}

// Input attributes
function build_checkbox_input_attrs($checkbox_props, $option = null) {
    $option_id = $option ? $checkbox_props['id'] . '-' . sanitize_title($option['value']) : $checkbox_props['id'];
    $option_name = $checkbox_props['is_group'] && $option ? $checkbox_props['name'] . '[]' : $checkbox_props['name'];
    $option_value = $option ? $option['value'] : $checkbox_props['value'];
    $option_checked = $option ? ($option['checked'] ?? false) : $checkbox_props['checked'];
    
    $attrs = [
        'type' => 'checkbox',
        'id' => $option_id,
        'name' => $option_name ?: $option_id,
        'value' => $option_value,
        'class' => 'hph-checkbox__input',
    ];

    if ($option_checked) {
        $attrs['checked'] = 'checked';
    }

    if ($checkbox_props['disabled']) {
        $attrs['disabled'] = 'disabled';
    }

    if ($checkbox_props['readonly']) {
        $attrs['readonly'] = 'readonly';
    }

    if ($checkbox_props['required']) {
        $attrs['required'] = 'required';
    }

    return $attrs;
}

// Build attributes helper
function build_checkbox_attrs($attrs) {
    $output = [];
    foreach ($attrs as $key => $value) {
        if ($value !== null && $value !== '') {
            if ($value === true || $key === 'checked' || $key === 'disabled' || $key === 'required' || $key === 'readonly') {
                $output[] = esc_attr($key);
            } else {
                $output[] = esc_attr($key) . '="' . esc_attr($value) . '"';
            }
        }
    }
    return implode(' ', $output);
}

// Single checkbox renderer
function render_single_checkbox($props, $option = null) {
    $option_id = $option ? $props['id'] . '-' . sanitize_title($option['value']) : $props['id'];
    $option_label = $option ? $option['label'] : $props['label'];
    $option_description = $option ? ($option['description'] ?? '') : $props['description'];
    
    $input_attrs = build_checkbox_input_attrs($props, $option);
    $checkbox_classes = $props['checkbox_classes'];
    
    if ($option && ($option['checked'] ?? false)) {
        $checkbox_classes[] = 'hph-checkbox--checked';
    }
    ?>
    <div class="hph-checkbox__item">
        <div class="<?php echo esc_attr(implode(' ', $checkbox_classes)); ?>">
            <input <?php echo build_checkbox_attrs($input_attrs); ?>>
            
            <span class="hph-checkbox__indicator">
                <span class="hph-checkbox__icon">
                    <?php
                    get_template_part('template-parts/base/icon', null, [
                        'name' => $props['icon'],
                        'size' => 'xs'
                    ]);
                    ?>
                </span>
            </span>
            
            <?php if ($option_label): ?>
                <label for="<?php echo esc_attr($option_id); ?>" class="<?php echo esc_attr(implode(' ', $props['label_classes'])); ?>">
                    <?php echo esc_html($option_label); ?>
                    <?php if ($props['required']): ?>
                        <span class="hph-checkbox__required" aria-label="required">*</span>
                    <?php endif; ?>
                </label>
            <?php endif; ?>
        </div>
        
        <?php if ($option_description): ?>
            <div class="hph-checkbox__description">
                <?php echo wp_kses_post($option_description); ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

// Store classes for use in render function
$props['checkbox_classes'] = $checkbox_classes;
$props['label_classes'] = $label_classes;
?>

<div 
    class="<?php echo esc_attr(implode(' ', $container_classes)); ?>"
    <?php foreach ($data_attrs as $key => $value): ?>
        <?php echo esc_attr($key); ?>="<?php echo esc_attr($value); ?>"
    <?php endforeach; ?>
    <?php if ($props['is_group'] && $props['group_layout'] === 'grid'): ?>
        style="--checkbox-grid-columns: <?php echo intval($props['group_columns']); ?>;"
    <?php endif; ?>
>
    <?php if ($props['label'] && $props['label_position'] === 'top' && !$props['is_group']): ?>
        <div id="<?php echo esc_attr($label_id); ?>" class="hph-checkbox__group-label">
            <?php echo esc_html($props['label']); ?>
            <?php if ($props['required']): ?>
                <span class="hph-checkbox__required" aria-label="required">*</span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="hph-checkbox__wrapper">
        <?php if ($props['is_group'] && !empty($props['group_options'])): ?>
            <!-- Checkbox Group -->
            <?php foreach ($props['group_options'] as $option): ?>
                <?php render_single_checkbox($props, $option); ?>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Single Checkbox -->
            <?php render_single_checkbox($props); ?>
        <?php endif; ?>
    </div>

    <?php if ($props['helper_text']): ?>
        <div id="<?php echo esc_attr($desc_id); ?>" class="hph-checkbox__helper-text">
            <?php echo wp_kses_post($props['helper_text']); ?>
        </div>
    <?php endif; ?>
</div>

<?php
/**
 * Usage Examples:
 * 
 * Single checkbox:
 * get_template_part('template-parts/base/checkbox', null, [
 *     'label' => 'I agree to the terms and conditions',
 *     'name' => 'terms_agreement',
 *     'required' => true,
 *     'description' => 'Please read our terms before proceeding'
 * ]);
 * 
 * Property amenities checkbox group:
 * get_template_part('template-parts/base/checkbox', null, [
 *     'is_group' => true,
 *     'name' => 'amenities',
 *     'label' => 'Property Amenities',
 *     'group_layout' => 'grid',
 *     'group_columns' => 3,
 *     'group_options' => [
 *         ['label' => 'Swimming Pool', 'value' => 'pool', 'checked' => true],
 *         ['label' => 'Garage', 'value' => 'garage'],
 *         ['label' => 'Garden', 'value' => 'garden'],
 *         ['label' => 'Gym', 'value' => 'gym'],
 *         ['label' => 'Balcony', 'value' => 'balcony'],
 *         ['label' => 'Fireplace', 'value' => 'fireplace']
 *     ]
 * ]);
 * 
 * Property type filter:
 * get_template_part('template-parts/base/checkbox', null, [
 *     'style' => 'card',
 *     'variant' => 'primary',
 *     'label' => 'Luxury Property',
 *     'description' => 'High-end properties with premium features'
 * ]);
 */
?>
