<?php
/**
 * Base Radio Component
 * Radio button inputs for single-select options and forms
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Radio component configuration
 */
$defaults = [
    // Basic configuration
    'name' => '',
    'value' => '',
    'selected' => '', // The currently selected value
    
    // Radio group options
    'options' => [], // Array of radio options
    'layout' => 'vertical', // vertical, horizontal, grid, cards
    'columns' => 2, // For grid layout
    
    // Labels and text
    'label' => '', // Group label
    'description' => '',
    'helper_text' => '',
    
    // Style variants
    'variant' => 'default', // default, primary, success, warning, error, info
    'size' => 'md', // xs, sm, md, lg, xl
    'style' => 'radio', // radio, button, card, pill
    
    // Behavior
    'disabled' => false,
    'readonly' => false,
    'required' => false,
    
    // Visual features
    'bordered' => true,
    'rounded' => false,
    'shadow' => false,
    'show_icons' => false,
    
    // Events
    'on_change' => '',
    'on_select' => '',
    
    // Accessibility
    'aria_label' => '',
    'aria_describedby' => '',
    'aria_labelledby' => '',
    
    // CSS classes
    'container_class' => '',
    'radio_class' => '',
    'label_class' => '',
    
    // Data attributes
    'data_attributes' => [],
];

$props = wp_parse_args($props ?? [], $defaults);

// Generate unique ID
$radio_id = $props['id'] ?? 'hph-radio-' . wp_unique_id();
$label_id = $radio_id . '-label';
$desc_id = $radio_id . '-desc';

// Ensure we have a name for the radio group
if (!$props['name']) {
    $props['name'] = $radio_id;
}

// Build CSS classes
$container_classes = [
    'hph-radio-container',
    'hph-radio-container--' . $props['size'],
    'hph-radio-container--' . $props['style'],
    'hph-radio-container--' . $props['layout'],
];

if ($props['disabled']) {
    $container_classes[] = 'hph-radio-container--disabled';
}

if (!empty($props['container_class'])) {
    $container_classes[] = $props['container_class'];
}

$radio_classes = [
    'hph-radio',
    'hph-radio--' . $props['variant'],
    'hph-radio--' . $props['style'],
];

if ($props['rounded']) {
    $radio_classes[] = 'hph-radio--rounded';
}

if (!$props['bordered']) {
    $radio_classes[] = 'hph-radio--borderless';
}

if ($props['shadow']) {
    $radio_classes[] = 'hph-radio--shadow';
}

if ($props['show_icons']) {
    $radio_classes[] = 'hph-radio--with-icons';
}

if (!empty($props['radio_class'])) {
    $radio_classes[] = $props['radio_class'];
}

$label_classes = [
    'hph-radio__label',
];

if (!empty($props['label_class'])) {
    $label_classes[] = $props['label_class'];
}

// Data attributes
$data_attrs = [
    'data-radio-size' => $props['size'],
    'data-radio-style' => $props['style'],
    'data-radio-variant' => $props['variant'],
    'data-radio-layout' => $props['layout'],
];

if ($props['on_change']) {
    $data_attrs['data-on-change'] = $props['on_change'];
}

if (!empty($props['data_attributes'])) {
    $data_attrs = array_merge($data_attrs, $props['data_attributes']);
}

// Build attributes helper
function build_radio_attrs($attrs) {
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

// Single radio option renderer
function render_radio_option($props, $option, $index) {
    $option_id = $props['name'] . '-' . $index;
    $is_selected = $props['selected'] === $option['value'];
    
    $input_attrs = [
        'type' => 'radio',
        'id' => $option_id,
        'name' => $props['name'],
        'value' => $option['value'],
        'class' => 'hph-radio__input',
    ];

    if ($is_selected) {
        $input_attrs['checked'] = 'checked';
    }

    if ($props['disabled'] || ($option['disabled'] ?? false)) {
        $input_attrs['disabled'] = 'disabled';
    }

    if ($props['readonly']) {
        $input_attrs['readonly'] = 'readonly';
    }

    if ($props['required']) {
        $input_attrs['required'] = 'required';
    }

    $option_radio_classes = $props['radio_classes'];
    if ($is_selected) {
        $option_radio_classes[] = 'hph-radio--selected';
    }
    if ($option['disabled'] ?? false) {
        $option_radio_classes[] = 'hph-radio--disabled';
    }
    ?>
    <div class="hph-radio__item">
        <div class="<?php echo esc_attr(implode(' ', $option_radio_classes)); ?>">
            <input <?php echo build_radio_attrs($input_attrs); ?>>
            
            <span class="hph-radio__indicator">
                <span class="hph-radio__dot"></span>
            </span>
            
            <div class="hph-radio__content">
                <label for="<?php echo esc_attr($option_id); ?>" class="<?php echo esc_attr(implode(' ', $props['label_classes'])); ?>">
                    <?php if ($props['show_icons'] && !empty($option['icon'])): ?>
                        <span class="hph-radio__icon">
                            <?php
                            get_template_part('template-parts/base/icon', null, [
                                'name' => $option['icon'],
                                'size' => 'sm'
                            ]);
                            ?>
                        </span>
                    <?php endif; ?>
                    
                    <span class="hph-radio__text">
                        <?php echo esc_html($option['label']); ?>
                        <?php if ($props['required']): ?>
                            <span class="hph-radio__required" aria-label="required">*</span>
                        <?php endif; ?>
                    </span>
                </label>
                
                <?php if (!empty($option['description'])): ?>
                    <div class="hph-radio__option-description">
                        <?php echo wp_kses_post($option['description']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($option['price'])): ?>
                    <div class="hph-radio__price">
                        <?php echo esc_html($option['price']); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}

// Store classes for use in render function
$props['radio_classes'] = $radio_classes;
$props['label_classes'] = $label_classes;
?>

<fieldset 
    class="<?php echo esc_attr(implode(' ', $container_classes)); ?>"
    <?php foreach ($data_attrs as $key => $value): ?>
        <?php echo esc_attr($key); ?>="<?php echo esc_attr($value); ?>"
    <?php endforeach; ?>
    <?php if ($props['layout'] === 'grid'): ?>
        style="--radio-grid-columns: <?php echo intval($props['columns']); ?>;"
    <?php endif; ?>
    <?php if ($props['disabled']): ?>disabled<?php endif; ?>
>
    <?php if ($props['label']): ?>
        <legend id="<?php echo esc_attr($label_id); ?>" class="hph-radio__group-label">
            <?php echo esc_html($props['label']); ?>
            <?php if ($props['required']): ?>
                <span class="hph-radio__required" aria-label="required">*</span>
            <?php endif; ?>
        </legend>
    <?php endif; ?>

    <?php if ($props['description']): ?>
        <div class="hph-radio__group-description">
            <?php echo wp_kses_post($props['description']); ?>
        </div>
    <?php endif; ?>

    <div class="hph-radio__wrapper">
        <?php if (!empty($props['options'])): ?>
            <?php foreach ($props['options'] as $index => $option): ?>
                <?php render_radio_option($props, $option, $index); ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ($props['helper_text']): ?>
        <div id="<?php echo esc_attr($desc_id); ?>" class="hph-radio__helper-text">
            <?php echo wp_kses_post($props['helper_text']); ?>
        </div>
    <?php endif; ?>
</fieldset>

<?php
/**
 * Usage Examples:
 * 
 * Property type selection:
 * get_template_part('template-parts/base/radio', null, [
 *     'name' => 'property_type',
 *     'label' => 'Property Type',
 *     'selected' => 'residential',
 *     'layout' => 'horizontal',
 *     'options' => [
 *         ['label' => 'Residential', 'value' => 'residential', 'icon' => 'home'],
 *         ['label' => 'Commercial', 'value' => 'commercial', 'icon' => 'building'],
 *         ['label' => 'Land', 'value' => 'land', 'icon' => 'map']
 *     ]
 * ]);
 * 
 * Card style radio group:
 * get_template_part('template-parts/base/radio', null, [
 *     'name' => 'listing_plan',
 *     'label' => 'Choose Your Listing Plan',
 *     'style' => 'card',
 *     'layout' => 'grid',
 *     'columns' => 3,
 *     'variant' => 'primary',
 *     'show_icons' => true,
 *     'options' => [
 *         [
 *             'label' => 'Basic',
 *             'value' => 'basic',
 *             'description' => 'Essential listing features',
 *             'price' => '$99/month',
 *             'icon' => 'star'
 *         ],
 *         [
 *             'label' => 'Premium',
 *             'value' => 'premium',
 *             'description' => 'Enhanced visibility and features',
 *             'price' => '$199/month',
 *             'icon' => 'star-fill'
 *         ],
 *         [
 *             'label' => 'Professional',
 *             'value' => 'pro',
 *             'description' => 'All features with priority support',
 *             'price' => '$299/month',
 *             'icon' => 'crown'
 *         ]
 *     ]
 * ]);
 * 
 * Button style radio:
 * get_template_part('template-parts/base/radio', null, [
 *     'name' => 'search_radius',
 *     'label' => 'Search Radius',
 *     'style' => 'button',
 *     'layout' => 'horizontal',
 *     'selected' => '10',
 *     'options' => [
 *         ['label' => '5 miles', 'value' => '5'],
 *         ['label' => '10 miles', 'value' => '10'],
 *         ['label' => '25 miles', 'value' => '25'],
 *         ['label' => '50 miles', 'value' => '50']
 *     ]
 * ]);
 */
?>
