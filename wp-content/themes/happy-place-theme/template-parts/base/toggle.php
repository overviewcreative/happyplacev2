<?php
/**
 * Base Toggle Component
 * Switch/toggle controls for settings, preferences, and binary options
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Toggle component configuration
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
    'on_text' => '',
    'off_text' => '',
    
    // Style variants
    'variant' => 'default', // default, primary, success, warning, error, info
    'size' => 'md', // xs, sm, md, lg, xl
    'style' => 'switch', // switch, checkbox, button
    
    // Behavior
    'disabled' => false,
    'readonly' => false,
    'required' => false,
    'loading' => false,
    
    // Visual features
    'show_icons' => false,
    'on_icon' => 'check',
    'off_icon' => 'x',
    'animate' => true,
    
    // Events
    'on_change' => '',
    'on_toggle' => '',
    
    // Accessibility
    'aria_label' => '',
    'aria_describedby' => '',
    'aria_labelledby' => '',
    
    // CSS classes
    'container_class' => '',
    'toggle_class' => '',
    'label_class' => '',
    
    // Data attributes
    'data_attributes' => [],
];

$props = wp_parse_args($props ?? [], $defaults);

// Generate unique ID
$toggle_id = $props['id'] ?? 'hph-toggle-' . wp_unique_id();
$label_id = $toggle_id . '-label';
$desc_id = $toggle_id . '-desc';

// Build CSS classes
$container_classes = [
    'hph-toggle-container',
    'hph-toggle-container--' . $props['size'],
    'hph-toggle-container--' . $props['style'],
    'hph-toggle-container--label-' . $props['label_position'],
];

if ($props['disabled']) {
    $container_classes[] = 'hph-toggle-container--disabled';
}

if ($props['loading']) {
    $container_classes[] = 'hph-toggle-container--loading';
}

if (!empty($props['container_class'])) {
    $container_classes[] = $props['container_class'];
}

$toggle_classes = [
    'hph-toggle',
    'hph-toggle--' . $props['variant'],
    'hph-toggle--' . $props['style'],
];

if ($props['checked']) {
    $toggle_classes[] = 'hph-toggle--checked';
}

if ($props['show_icons']) {
    $toggle_classes[] = 'hph-toggle--with-icons';
}

if ($props['animate']) {
    $toggle_classes[] = 'hph-toggle--animated';
}

if (!empty($props['toggle_class'])) {
    $toggle_classes[] = $props['toggle_class'];
}

$label_classes = [
    'hph-toggle__label',
];

if (!empty($props['label_class'])) {
    $label_classes[] = $props['label_class'];
}

// Data attributes
$data_attrs = [
    'data-toggle-size' => $props['size'],
    'data-toggle-style' => $props['style'],
    'data-toggle-variant' => $props['variant'],
];

if ($props['on_change']) {
    $data_attrs['data-on-change'] = $props['on_change'];
}

if (!empty($props['data_attributes'])) {
    $data_attrs = array_merge($data_attrs, $props['data_attributes']);
}

// Input attributes
$input_attrs = [
    'type' => 'checkbox',
    'id' => $toggle_id,
    'name' => $props['name'] ?: $toggle_id,
    'value' => $props['value'],
    'class' => 'hph-toggle__input',
];

if ($props['checked']) {
    $input_attrs['checked'] = 'checked';
}

if ($props['disabled']) {
    $input_attrs['disabled'] = 'disabled';
}

if ($props['readonly']) {
    $input_attrs['readonly'] = 'readonly';
}

if ($props['required']) {
    $input_attrs['required'] = 'required';
}

if ($props['aria_label']) {
    $input_attrs['aria-label'] = $props['aria_label'];
}

if ($props['aria_describedby'] || $props['description']) {
    $input_attrs['aria-describedby'] = $props['aria_describedby'] ?: $desc_id;
}

if ($props['aria_labelledby'] || $props['label']) {
    $input_attrs['aria-labelledby'] = $props['aria_labelledby'] ?: $label_id;
}

// Build attributes helper
function build_toggle_attrs($attrs) {
    $output = [];
    foreach ($attrs as $key => $value) {
        if ($value !== null && $value !== '') {
            if ($value === true) {
                $output[] = esc_attr($key);
            } else {
                $output[] = esc_attr($key) . '="' . esc_attr($value) . '"';
            }
        }
    }
    return implode(' ', $output);
}
?>

<div 
    class="<?php echo esc_attr(implode(' ', $container_classes)); ?>"
    <?php foreach ($data_attrs as $key => $value): ?>
        <?php echo esc_attr($key); ?>="<?php echo esc_attr($value); ?>"
    <?php endforeach; ?>
>
    <?php if ($props['label'] && $props['label_position'] === 'top'): ?>
        <label id="<?php echo esc_attr($label_id); ?>" for="<?php echo esc_attr($toggle_id); ?>" class="<?php echo esc_attr(implode(' ', $label_classes)); ?>">
            <?php echo esc_html($props['label']); ?>
            <?php if ($props['required']): ?>
                <span class="hph-toggle__required" aria-label="required">*</span>
            <?php endif; ?>
        </label>
    <?php endif; ?>

    <div class="hph-toggle__wrapper">
        <?php if ($props['label'] && $props['label_position'] === 'left'): ?>
            <label id="<?php echo esc_attr($label_id); ?>" for="<?php echo esc_attr($toggle_id); ?>" class="<?php echo esc_attr(implode(' ', $label_classes)); ?>">
                <?php echo esc_html($props['label']); ?>
                <?php if ($props['required']): ?>
                    <span class="hph-toggle__required" aria-label="required">*</span>
                <?php endif; ?>
            </label>
        <?php endif; ?>

        <div class="<?php echo esc_attr(implode(' ', $toggle_classes)); ?>">
            <input <?php echo build_toggle_attrs($input_attrs); ?>>
            
            <span class="hph-toggle__slider">
                <?php if ($props['loading']): ?>
                    <span class="hph-toggle__spinner"></span>
                <?php else: ?>
                    <!-- Toggle thumb/handle -->
                    <span class="hph-toggle__thumb">
                        <?php if ($props['show_icons']): ?>
                            <span class="hph-toggle__icon hph-toggle__icon--on">
                                <?php
                                // Integrate with icon component
                                get_template_part('template-parts/base/icon', null, [
                                    'name' => $props['on_icon'],
                                    'size' => 'xs'
                                ]);
                                ?>
                            </span>
                            <span class="hph-toggle__icon hph-toggle__icon--off">
                                <?php
                                get_template_part('template-parts/base/icon', null, [
                                    'name' => $props['off_icon'],
                                    'size' => 'xs'
                                ]);
                                ?>
                            </span>
                        <?php endif; ?>
                    </span>
                    
                    <!-- Text labels inside toggle -->
                    <?php if ($props['on_text'] || $props['off_text']): ?>
                        <span class="hph-toggle__text hph-toggle__text--on">
                            <?php echo esc_html($props['on_text']); ?>
                        </span>
                        <span class="hph-toggle__text hph-toggle__text--off">
                            <?php echo esc_html($props['off_text']); ?>
                        </span>
                    <?php endif; ?>
                <?php endif; ?>
            </span>
        </div>

        <?php if ($props['label'] && $props['label_position'] === 'right'): ?>
            <label id="<?php echo esc_attr($label_id); ?>" for="<?php echo esc_attr($toggle_id); ?>" class="<?php echo esc_attr(implode(' ', $label_classes)); ?>">
                <?php echo esc_html($props['label']); ?>
                <?php if ($props['required']): ?>
                    <span class="hph-toggle__required" aria-label="required">*</span>
                <?php endif; ?>
            </label>
        <?php endif; ?>
    </div>

    <?php if ($props['label'] && $props['label_position'] === 'bottom'): ?>
        <label id="<?php echo esc_attr($label_id); ?>" for="<?php echo esc_attr($toggle_id); ?>" class="<?php echo esc_attr(implode(' ', $label_classes)); ?>">
            <?php echo esc_html($props['label']); ?>
            <?php if ($props['required']): ?>
                <span class="hph-toggle__required" aria-label="required">*</span>
            <?php endif; ?>
        </label>
    <?php endif; ?>

    <?php if ($props['description']): ?>
        <div id="<?php echo esc_attr($desc_id); ?>" class="hph-toggle__description">
            <?php echo wp_kses_post($props['description']); ?>
        </div>
    <?php endif; ?>
</div>

<?php
/**
 * Usage Examples:
 * 
 * Email notifications toggle:
 * get_template_part('template-parts/base/toggle', null, [
 *     'label' => 'Email Notifications',
 *     'checked' => true,
 *     'name' => 'email_notifications',
 *     'description' => 'Receive updates about new properties and market changes'
 * ]);
 * 
 * Property availability toggle:
 * get_template_part('template-parts/base/toggle', null, [
 *     'label' => 'Available Properties Only',
 *     'variant' => 'primary',
 *     'size' => 'lg',
 *     'show_icons' => true,
 *     'on_icon' => 'check',
 *     'off_icon' => 'x'
 * ]);
 * 
 * Button style toggle:
 * get_template_part('template-parts/base/toggle', null, [
 *     'style' => 'button',
 *     'on_text' => 'ON',
 *     'off_text' => 'OFF',
 *     'variant' => 'success'
 * ]);
 */
?>
