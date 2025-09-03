<?php
/**
 * Base Select Component
 * Standard select dropdown for single and multi-select options
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Select component configuration
 */
$defaults = [
    // Basic configuration
    'name' => '',
    'value' => '', // Selected value(s) - string for single, array for multi
    'multiple' => false,
    'size' => 1, // Number of visible options (for multiple selects)
    
    // Options
    'options' => [], // Array of options
    'option_groups' => [], // Grouped options
    'placeholder' => 'Select an option...',
    
    // Labels and text
    'label' => '',
    'label_position' => 'top', // top, left, inside, floating
    'description' => '',
    'helper_text' => '',
    'error_message' => '',
    
    // Style variants
    'variant' => 'default', // default, primary, success, warning, error, info
    'size_variant' => 'md', // xs, sm, md, lg, xl
    'style' => 'outlined', // outlined, filled, underlined, borderless
    
    // Behavior
    'disabled' => false,
    'readonly' => false,
    'required' => false,
    'autofocus' => false,
    'searchable' => false, // Enable search functionality
    'clearable' => false, // Show clear button
    
    // Visual features
    'rounded' => false,
    'shadow' => false,
    'full_width' => true,
    'native' => true, // Use native select vs custom dropdown
    
    // Icons
    'icon_left' => '',
    'dropdown_icon' => 'chevron-down',
    
    // Events
    'on_change' => '',
    'on_select' => '',
    'on_search' => '',
    
    // Accessibility
    'aria_label' => '',
    'aria_describedby' => '',
    'aria_labelledby' => '',
    
    // CSS classes
    'container_class' => '',
    'select_class' => '',
    'label_class' => '',
    
    // Data attributes
    'data_attributes' => [],
];

$props = wp_parse_args($props ?? [], $defaults);

// Generate unique ID
$select_id = $props['id'] ?? 'hph-select-' . wp_unique_id();
$label_id = $select_id . '-label';
$desc_id = $select_id . '-desc';
$error_id = $select_id . '-error';

// Normalize value for multiple selects
if ($props['multiple'] && !is_array($props['value'])) {
    $props['value'] = !empty($props['value']) ? [$props['value']] : [];
}

// Determine state
$has_error = !empty($props['error_message']);
$has_value = $props['multiple'] ? !empty($props['value']) : ($props['value'] !== '' && $props['value'] !== null);

// Build CSS classes
$container_classes = [
    'hph-select-container',
    'hph-select-container--' . $props['size_variant'],
    'hph-select-container--' . $props['style'],
    'hph-select-container--label-' . $props['label_position'],
];

if ($props['multiple']) {
    $container_classes[] = 'hph-select-container--multiple';
}

if ($props['disabled']) {
    $container_classes[] = 'hph-select-container--disabled';
}

if ($props['readonly']) {
    $container_classes[] = 'hph-select-container--readonly';
}

if ($has_error) {
    $container_classes[] = 'hph-select-container--error';
}

if ($has_value) {
    $container_classes[] = 'hph-select-container--has-value';
}

if ($props['full_width']) {
    $container_classes[] = 'hph-select-container--full-width';
}

if ($props['icon_left']) {
    $container_classes[] = 'hph-select-container--with-icon-left';
}

if ($props['searchable']) {
    $container_classes[] = 'hph-select-container--searchable';
}

if ($props['clearable']) {
    $container_classes[] = 'hph-select-container--clearable';
}

if (!$props['native']) {
    $container_classes[] = 'hph-select-container--custom';
}

if (!empty($props['container_class'])) {
    $container_classes[] = $props['container_class'];
}

$wrapper_classes = [
    'hph-select__wrapper',
];

$select_classes = [
    'hph-select',
    'hph-select--' . $props['variant'],
    'hph-select--' . $props['style'],
];

if ($props['rounded']) {
    $select_classes[] = 'hph-select--rounded';
}

if ($props['shadow']) {
    $select_classes[] = 'hph-select--shadow';
}

if (!empty($props['select_class'])) {
    $select_classes[] = $props['select_class'];
}

$label_classes = [
    'hph-select__label',
];

if ($props['required']) {
    $label_classes[] = 'hph-select__label--required';
}

if (!empty($props['label_class'])) {
    $label_classes[] = $props['label_class'];
}

// Data attributes
$data_attrs = [
    'data-select-size' => $props['size_variant'],
    'data-select-style' => $props['style'],
    'data-select-variant' => $props['variant'],
];

if ($props['searchable']) {
    $data_attrs['data-searchable'] = 'true';
}

if ($props['clearable']) {
    $data_attrs['data-clearable'] = 'true';
}

if (!empty($props['data_attributes'])) {
    $data_attrs = array_merge($data_attrs, $props['data_attributes']);
}

// Select attributes
$select_attrs = [
    'id' => $select_id,
    'name' => $props['name'] ?: $select_id,
    'class' => implode(' ', $select_classes),
];

if ($props['multiple']) {
    $select_attrs['multiple'] = 'multiple';
    $select_attrs['name'] = ($props['name'] ?: $select_id) . '[]';
    if ($props['size'] > 1) {
        $select_attrs['size'] = $props['size'];
    }
}

if ($props['disabled']) {
    $select_attrs['disabled'] = 'disabled';
}

if ($props['required']) {
    $select_attrs['required'] = 'required';
}

if ($props['autofocus']) {
    $select_attrs['autofocus'] = 'autofocus';
}

// ARIA attributes
$aria_describedby = [];

if ($props['description']) {
    $aria_describedby[] = $desc_id;
}

if ($has_error) {
    $aria_describedby[] = $error_id;
}

if (!empty($aria_describedby)) {
    $select_attrs['aria-describedby'] = implode(' ', $aria_describedby);
}

if ($props['aria_label']) {
    $select_attrs['aria-label'] = $props['aria_label'];
}

if ($props['aria_labelledby'] || $props['label']) {
    $select_attrs['aria-labelledby'] = $props['aria_labelledby'] ?: $label_id;
}

if ($has_error) {
    $select_attrs['aria-invalid'] = 'true';
}

// Helper functions
function build_select_attrs($attrs) {
    $output = [];
    foreach ($attrs as $key => $value) {
        if ($value !== null && $value !== '') {
            if ($value === true || in_array($key, ['multiple', 'disabled', 'required', 'autofocus'])) {
                $output[] = esc_attr($key);
            } else {
                $output[] = esc_attr($key) . '="' . esc_attr($value) . '"';
            }
        }
    }
    return implode(' ', $output);
}

function is_option_selected($option_value, $selected_values, $multiple = false) {
    if ($multiple) {
        return in_array($option_value, (array) $selected_values);
    }
    return $option_value == $selected_values;
}

function render_option($option, $selected_values, $multiple = false) {
    $is_selected = is_option_selected($option['value'], $selected_values, $multiple);
    ?>
    <option 
        value="<?php echo esc_attr($option['value']); ?>"
        <?php if ($is_selected): ?>selected<?php endif; ?>
        <?php if ($option['disabled'] ?? false): ?>disabled<?php endif; ?>
        <?php if (!empty($option['data'])): ?>
            <?php foreach ($option['data'] as $key => $value): ?>
                data-<?php echo esc_attr($key); ?>="<?php echo esc_attr($value); ?>"
            <?php endforeach; ?>
        <?php endif; ?>
    >
        <?php echo esc_html($option['label']); ?>
    </option>
    <?php
}
?>

<div 
    class="<?php echo esc_attr(implode(' ', $container_classes)); ?>"
    <?php foreach ($data_attrs as $key => $value): ?>
        <?php echo esc_attr($key); ?>="<?php echo esc_attr($value); ?>"
    <?php endforeach; ?>
>
    <?php if ($props['label'] && in_array($props['label_position'], ['top', 'left'])): ?>
        <label id="<?php echo esc_attr($label_id); ?>" for="<?php echo esc_attr($select_id); ?>" class="<?php echo esc_attr(implode(' ', $label_classes)); ?>">
            <?php echo esc_html($props['label']); ?>
            <?php if ($props['required']): ?>
                <span class="hph-select__required" aria-label="required">*</span>
            <?php endif; ?>
        </label>
    <?php endif; ?>

    <?php if ($props['description']): ?>
        <div id="<?php echo esc_attr($desc_id); ?>" class="hph-select__description">
            <?php echo wp_kses_post($props['description']); ?>
        </div>
    <?php endif; ?>

    <div class="<?php echo esc_attr(implode(' ', $wrapper_classes)); ?>">
        <?php if ($props['icon_left']): ?>
            <span class="hph-select__icon hph-select__icon--left">
                <?php
                get_template_part('template-parts/base/icon', null, [
                    'name' => $props['icon_left'],
                    'size' => 'sm'
                ]);
                ?>
            </span>
        <?php endif; ?>

        <div class="hph-select__field">
            <?php if ($props['label'] && in_array($props['label_position'], ['inside', 'floating'])): ?>
                <label id="<?php echo esc_attr($label_id); ?>" for="<?php echo esc_attr($select_id); ?>" class="<?php echo esc_attr(implode(' ', $label_classes)); ?>">
                    <?php echo esc_html($props['label']); ?>
                    <?php if ($props['required']): ?>
                        <span class="hph-select__required" aria-label="required">*</span>
                    <?php endif; ?>
                </label>
            <?php endif; ?>

            <select <?php echo build_select_attrs($select_attrs); ?>>
                <?php if (!$props['multiple'] && $props['placeholder']): ?>
                    <option value="" disabled <?php if (!$has_value): ?>selected<?php endif; ?>>
                        <?php echo esc_html($props['placeholder']); ?>
                    </option>
                <?php endif; ?>

                <?php if (!empty($props['option_groups'])): ?>
                    <?php foreach ($props['option_groups'] as $group): ?>
                        <optgroup label="<?php echo esc_attr($group['label']); ?>">
                            <?php foreach ($group['options'] as $option): ?>
                                <?php render_option($option, $props['value'], $props['multiple']); ?>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                <?php elseif (!empty($props['options'])): ?>
                    <?php foreach ($props['options'] as $option): ?>
                        <?php render_option($option, $props['value'], $props['multiple']); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <span class="hph-select__icon hph-select__icon--dropdown">
            <?php
            get_template_part('template-parts/base/icon', null, [
                'name' => $props['dropdown_icon'],
                'size' => 'sm'
            ]);
            ?>
        </span>

        <?php if ($props['clearable'] && $has_value): ?>
            <button type="button" class="hph-select__clear" aria-label="Clear selection">
                <?php
                get_template_part('template-parts/base/icon', null, [
                    'name' => 'x',
                    'size' => 'xs'
                ]);
                ?>
            </button>
        <?php endif; ?>
    </div>

    <div class="hph-select__footer">
        <?php if ($has_error): ?>
            <div id="<?php echo esc_attr($error_id); ?>" class="hph-select__error" role="alert">
                <?php
                get_template_part('template-parts/base/icon', null, [
                    'name' => 'alert-circle',
                    'size' => 'xs'
                ]);
                ?>
                <span><?php echo esc_html($props['error_message']); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($props['helper_text'] && !$has_error): ?>
            <div class="hph-select__helper-text">
                <?php echo wp_kses_post($props['helper_text']); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
/**
 * Usage Examples:
 * 
 * Property type select:
 * get_template_part('template-parts/base/select', null, [
 *     'name' => 'property_type',
 *     'label' => 'Property Type',
 *     'placeholder' => 'Select property type...',
 *     'required' => true,
 *     'options' => [
 *         ['label' => 'Residential', 'value' => 'residential'],
 *         ['label' => 'Commercial', 'value' => 'commercial'],
 *         ['label' => 'Land', 'value' => 'land'],
 *         ['label' => 'Rental', 'value' => 'rental']
 *     ]
 * ]);
 * 
 * Multiple amenities select:
 * get_template_part('template-parts/base/select', null, [
 *     'name' => 'amenities',
 *     'label' => 'Property Amenities',
 *     'multiple' => true,
 *     'size' => 6,
 *     'value' => ['pool', 'garage'],
 *     'options' => [
 *         ['label' => 'Swimming Pool', 'value' => 'pool'],
 *         ['label' => 'Garage', 'value' => 'garage'],
 *         ['label' => 'Garden', 'value' => 'garden'],
 *         ['label' => 'Gym', 'value' => 'gym'],
 *         ['label' => 'Balcony', 'value' => 'balcony']
 *     ]
 * ]);
 * 
 * Grouped options select:
 * get_template_part('template-parts/base/select', null, [
 *     'name' => 'location',
 *     'label' => 'Location',
 *     'placeholder' => 'Choose location...',
 *     'option_groups' => [
 *         [
 *             'label' => 'Downtown',
 *             'options' => [
 *                 ['label' => 'Financial District', 'value' => 'downtown-financial'],
 *                 ['label' => 'Arts District', 'value' => 'downtown-arts']
 *             ]
 *         ],
 *         [
 *             'label' => 'Suburbs',
 *             'options' => [
 *                 ['label' => 'North Hills', 'value' => 'suburbs-north'],
 *                 ['label' => 'West Side', 'value' => 'suburbs-west']
 *             ]
 *         ]
 *     ]
 * ]);
 */
?>
