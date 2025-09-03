<?php
/**
 * Base Textarea Component
 * Multi-line text input for comments, descriptions, and long-form content
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Textarea component configuration
 */
$defaults = [
    // Basic configuration
    'name' => '',
    'value' => '',
    'placeholder' => '',
    
    // Labels and text
    'label' => '',
    'label_position' => 'top', // top, left, inside, floating
    'description' => '',
    'helper_text' => '',
    'error_message' => '',
    
    // Textarea attributes
    'rows' => 4,
    'cols' => 50,
    'min_length' => 0,
    'max_length' => 0,
    'resize' => 'vertical', // none, both, horizontal, vertical
    
    // Style variants
    'variant' => 'default', // default, primary, success, warning, error, info
    'size' => 'md', // xs, sm, md, lg, xl
    'style' => 'outlined', // outlined, filled, underlined, borderless
    
    // Behavior
    'disabled' => false,
    'readonly' => false,
    'required' => false,
    'autofocus' => false,
    'spellcheck' => true,
    'wrap' => 'soft', // soft, hard, off
    
    // Visual features
    'rounded' => false,
    'shadow' => false,
    'show_counter' => false,
    'auto_resize' => false,
    'full_width' => true,
    
    // Icons
    'icon_left' => '',
    'icon_right' => '',
    
    // Events
    'on_change' => '',
    'on_input' => '',
    'on_focus' => '',
    'on_blur' => '',
    
    // Accessibility
    'aria_label' => '',
    'aria_describedby' => '',
    'aria_labelledby' => '',
    
    // CSS classes
    'container_class' => '',
    'textarea_class' => '',
    'label_class' => '',
    
    // Data attributes
    'data_attributes' => [],
];

$props = wp_parse_args($props ?? [], $defaults);

// Generate unique ID
$textarea_id = $props['id'] ?? 'hph-textarea-' . wp_unique_id();
$label_id = $textarea_id . '-label';
$desc_id = $textarea_id . '-desc';
$error_id = $textarea_id . '-error';

// Determine state
$has_error = !empty($props['error_message']);
$has_value = !empty($props['value']);

// Build CSS classes
$container_classes = [
    'hph-textarea-container',
    'hph-textarea-container--' . $props['size'],
    'hph-textarea-container--' . $props['style'],
    'hph-textarea-container--label-' . $props['label_position'],
];

if ($props['disabled']) {
    $container_classes[] = 'hph-textarea-container--disabled';
}

if ($props['readonly']) {
    $container_classes[] = 'hph-textarea-container--readonly';
}

if ($has_error) {
    $container_classes[] = 'hph-textarea-container--error';
}

if ($has_value) {
    $container_classes[] = 'hph-textarea-container--has-value';
}

if ($props['full_width']) {
    $container_classes[] = 'hph-textarea-container--full-width';
}

if ($props['icon_left']) {
    $container_classes[] = 'hph-textarea-container--with-icon-left';
}

if ($props['icon_right']) {
    $container_classes[] = 'hph-textarea-container--with-icon-right';
}

if (!empty($props['container_class'])) {
    $container_classes[] = $props['container_class'];
}

$wrapper_classes = [
    'hph-textarea__wrapper',
];

$textarea_classes = [
    'hph-textarea',
    'hph-textarea--' . $props['variant'],
    'hph-textarea--' . $props['style'],
    'hph-textarea--resize-' . $props['resize'],
];

if ($props['rounded']) {
    $textarea_classes[] = 'hph-textarea--rounded';
}

if ($props['shadow']) {
    $textarea_classes[] = 'hph-textarea--shadow';
}

if ($props['auto_resize']) {
    $textarea_classes[] = 'hph-textarea--auto-resize';
}

if (!empty($props['textarea_class'])) {
    $textarea_classes[] = $props['textarea_class'];
}

$label_classes = [
    'hph-textarea__label',
];

if ($props['required']) {
    $label_classes[] = 'hph-textarea__label--required';
}

if (!empty($props['label_class'])) {
    $label_classes[] = $props['label_class'];
}

// Data attributes
$data_attrs = [
    'data-textarea-size' => $props['size'],
    'data-textarea-style' => $props['style'],
    'data-textarea-variant' => $props['variant'],
];

if ($props['auto_resize']) {
    $data_attrs['data-auto-resize'] = 'true';
}

if ($props['max_length'] > 0) {
    $data_attrs['data-max-length'] = $props['max_length'];
}

if (!empty($props['data_attributes'])) {
    $data_attrs = array_merge($data_attrs, $props['data_attributes']);
}

// Textarea attributes
$textarea_attrs = [
    'id' => $textarea_id,
    'name' => $props['name'] ?: $textarea_id,
    'class' => implode(' ', $textarea_classes),
    'rows' => $props['rows'],
    'cols' => $props['cols'],
    'wrap' => $props['wrap'],
];

if ($props['placeholder']) {
    $textarea_attrs['placeholder'] = $props['placeholder'];
}

if ($props['disabled']) {
    $textarea_attrs['disabled'] = 'disabled';
}

if ($props['readonly']) {
    $textarea_attrs['readonly'] = 'readonly';
}

if ($props['required']) {
    $textarea_attrs['required'] = 'required';
}

if ($props['autofocus']) {
    $textarea_attrs['autofocus'] = 'autofocus';
}

if (!$props['spellcheck']) {
    $textarea_attrs['spellcheck'] = 'false';
}

if ($props['min_length'] > 0) {
    $textarea_attrs['minlength'] = $props['min_length'];
}

if ($props['max_length'] > 0) {
    $textarea_attrs['maxlength'] = $props['max_length'];
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
    $textarea_attrs['aria-describedby'] = implode(' ', $aria_describedby);
}

if ($props['aria_label']) {
    $textarea_attrs['aria-label'] = $props['aria_label'];
}

if ($props['aria_labelledby'] || $props['label']) {
    $textarea_attrs['aria-labelledby'] = $props['aria_labelledby'] ?: $label_id;
}

if ($has_error) {
    $textarea_attrs['aria-invalid'] = 'true';
}

// Build attributes helper
function build_textarea_attrs($attrs) {
    $output = [];
    foreach ($attrs as $key => $value) {
        if ($value !== null && $value !== '') {
            if ($value === true || $key === 'disabled' || $key === 'readonly' || $key === 'required' || $key === 'autofocus') {
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
    <?php if ($props['label'] && in_array($props['label_position'], ['top', 'left'])): ?>
        <label id="<?php echo esc_attr($label_id); ?>" for="<?php echo esc_attr($textarea_id); ?>" class="<?php echo esc_attr(implode(' ', $label_classes)); ?>">
            <?php echo esc_html($props['label']); ?>
            <?php if ($props['required']): ?>
                <span class="hph-textarea__required" aria-label="required">*</span>
            <?php endif; ?>
        </label>
    <?php endif; ?>

    <?php if ($props['description']): ?>
        <div id="<?php echo esc_attr($desc_id); ?>" class="hph-textarea__description">
            <?php echo wp_kses_post($props['description']); ?>
        </div>
    <?php endif; ?>

    <div class="<?php echo esc_attr(implode(' ', $wrapper_classes)); ?>">
        <?php if ($props['icon_left']): ?>
            <span class="hph-textarea__icon hph-textarea__icon--left">
                <?php
                get_template_part('template-parts/base/icon', null, [
                    'name' => $props['icon_left'],
                    'size' => 'sm'
                ]);
                ?>
            </span>
        <?php endif; ?>

        <div class="hph-textarea__field">
            <?php if ($props['label'] && in_array($props['label_position'], ['inside', 'floating'])): ?>
                <label id="<?php echo esc_attr($label_id); ?>" for="<?php echo esc_attr($textarea_id); ?>" class="<?php echo esc_attr(implode(' ', $label_classes)); ?>">
                    <?php echo esc_html($props['label']); ?>
                    <?php if ($props['required']): ?>
                        <span class="hph-textarea__required" aria-label="required">*</span>
                    <?php endif; ?>
                </label>
            <?php endif; ?>

            <textarea <?php echo build_textarea_attrs($textarea_attrs); ?>><?php echo esc_textarea($props['value']); ?></textarea>
        </div>

        <?php if ($props['icon_right']): ?>
            <span class="hph-textarea__icon hph-textarea__icon--right">
                <?php
                get_template_part('template-parts/base/icon', null, [
                    'name' => $props['icon_right'],
                    'size' => 'sm'
                ]);
                ?>
            </span>
        <?php endif; ?>
    </div>

    <div class="hph-textarea__footer">
        <?php if ($has_error): ?>
            <div id="<?php echo esc_attr($error_id); ?>" class="hph-textarea__error" role="alert">
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
            <div class="hph-textarea__helper-text">
                <?php echo wp_kses_post($props['helper_text']); ?>
            </div>
        <?php endif; ?>

        <?php if ($props['show_counter'] && $props['max_length'] > 0): ?>
            <div class="hph-textarea__counter">
                <span class="hph-textarea__counter-current"><?php echo strlen($props['value']); ?></span>
                <span class="hph-textarea__counter-separator">/</span>
                <span class="hph-textarea__counter-max"><?php echo $props['max_length']; ?></span>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
/**
 * Usage Examples:
 * 
 * Property description textarea:
 * get_template_part('template-parts/base/textarea', null, [
 *     'name' => 'property_description',
 *     'label' => 'Property Description',
 *     'placeholder' => 'Describe the property features, location, and highlights...',
 *     'rows' => 6,
 *     'max_length' => 1000,
 *     'show_counter' => true,
 *     'required' => true,
 *     'description' => 'Provide a detailed description to attract potential buyers'
 * ]);
 * 
 * Contact message textarea:
 * get_template_part('template-parts/base/textarea', null, [
 *     'name' => 'message',
 *     'label' => 'Your Message',
 *     'placeholder' => 'Tell us about your property needs...',
 *     'style' => 'filled',
 *     'auto_resize' => true,
 *     'min_length' => 10,
 *     'helper_text' => 'Our team will respond within 24 hours'
 * ]);
 * 
 * Floating label textarea:
 * get_template_part('template-parts/base/textarea', null, [
 *     'name' => 'special_requests',
 *     'label' => 'Special Requests',
 *     'label_position' => 'floating',
 *     'variant' => 'primary',
 *     'rows' => 3,
 *     'resize' => 'none'
 * ]);
 */
?>
