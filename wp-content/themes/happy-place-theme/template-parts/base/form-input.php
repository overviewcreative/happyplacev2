<?php
/**
 * Base Form Input Component
 * 
 * Pure UI input component with extensive field types and variations
 * 
 * @package HappyPlaceTheme
 * @subpackage Components/Base
 * @since 3.0.0
 */

$props = wp_parse_args(get_query_var('args', array()), array(
    // Field basics
    'type' => 'text', // text, email, password, number, tel, url, search, date, time, datetime-local, month, week, color, range, file, select, textarea, checkbox, radio, toggle, chips
    'name' => '',
    'value' => '',
    'placeholder' => '',
    
    // Label & Help
    'label' => '',
    'label_position' => 'top', // top, left, floating, inline, hidden
    'help' => '', // Help text
    'help_position' => 'bottom', // top, bottom, tooltip
    'required' => false,
    'required_indicator' => '*',
    
    // Appearance
    'variant' => 'default', // default, outlined, filled, borderless, rounded
    'size' => 'md', // xs, sm, md, lg, xl
    'width' => 'full', // full, auto, fixed, min, max
    
    // Icons & Addons
    'icon_left' => '',
    'icon_right' => '',
    'prefix' => '', // Text prefix (e.g., '$')
    'suffix' => '', // Text suffix (e.g., '.00')
    'addon_before' => '', // Button/element before
    'addon_after' => '', // Button/element after
    
    // Validation & State
    'validation' => array(
        'min' => '',
        'max' => '',
        'minlength' => '',
        'maxlength' => '',
        'pattern' => '',
        'step' => ''
    ),
    'state' => 'default', // default, success, warning, error, disabled, readonly, loading
    'error' => '', // Error message
    'success' => '', // Success message
    'counter' => false, // Character counter
    
    // Behavior
    'disabled' => false,
    'readonly' => false,
    'autofocus' => false,
    'autocomplete' => '',
    'spellcheck' => null,
    
    // Select/Dropdown specific
    'options' => array(), // For select, radio, checkbox groups
    'multiple' => false,
    'searchable' => false, // Makes select searchable
    'clearable' => false, // Adds clear button
    'groups' => array(), // Grouped options
    
    // Textarea specific
    'rows' => 3,
    'resize' => 'vertical', // none, both, horizontal, vertical
    'autosize' => false,
    
    // Number/Range specific
    'min' => '',
    'max' => '',
    'step' => '',
    'show_value' => false, // Shows current value for range
    
    // File specific
    'accept' => '',
    'multiple_files' => false,
    'max_files' => '',
    'max_size' => '',
    'dropzone' => false, // Drag & drop zone
    
    // Advanced
    'mask' => '', // Input mask pattern
    'formatter' => '', // Format function name
    'debounce' => 0, // Debounce delay in ms
    
    // HTML
    'id' => '',
    'class' => '',
    'container_class' => '',
    'attributes' => array(),
    'data' => array()
));

// Generate ID if not provided
if (!$props['id'] && $props['name']) {
    $props['id'] = 'hph-input-' . sanitize_html_class($props['name']);
}

// Container classes
$container_classes = array(
    'hph-form-field',
    'hph-form-field--' . $props['type'],
    'hph-form-field--' . $props['variant'],
    'hph-form-field--' . $props['size'],
    'hph-form-field--label-' . $props['label_position']
);

if ($props['width'] !== 'auto') {
    $container_classes[] = 'hph-form-field--' . $props['width'];
}

if ($props['state'] !== 'default') {
    $container_classes[] = 'hph-form-field--' . $props['state'];
}

if ($props['required']) {
    $container_classes[] = 'hph-form-field--required';
}

if ($props['disabled']) {
    $container_classes[] = 'hph-form-field--disabled';
}

if ($props['readonly']) {
    $container_classes[] = 'hph-form-field--readonly';
}

if ($props['icon_left']) {
    $container_classes[] = 'hph-form-field--icon-left';
}

if ($props['icon_right']) {
    $container_classes[] = 'hph-form-field--icon-right';
}

if ($props['container_class']) {
    $container_classes[] = $props['container_class'];
}

// Input classes
$input_classes = array(
    'hph-form-input',
    'hph-form-input--' . $props['type']
);

if ($props['class']) {
    $input_classes[] = $props['class'];
}

// Build input attributes
$input_attrs = array(
    'class' => implode(' ', $input_classes),
    'name' => $props['name'],
    'id' => $props['id']
);

// Add type-specific attributes
if (!in_array($props['type'], array('select', 'textarea', 'checkbox', 'radio', 'toggle', 'chips'))) {
    $input_attrs['type'] = $props['type'] === 'toggle' ? 'checkbox' : $props['type'];
    $input_attrs['value'] = $props['value'];
}

if ($props['placeholder']) {
    $input_attrs['placeholder'] = $props['placeholder'];
}

if ($props['required']) {
    $input_attrs['required'] = 'required';
    $input_attrs['aria-required'] = 'true';
}

if ($props['disabled']) {
    $input_attrs['disabled'] = 'disabled';
}

if ($props['readonly']) {
    $input_attrs['readonly'] = 'readonly';
}

if ($props['autofocus']) {
    $input_attrs['autofocus'] = 'autofocus';
}

if ($props['autocomplete']) {
    $input_attrs['autocomplete'] = $props['autocomplete'];
}

if ($props['spellcheck'] !== null) {
    $input_attrs['spellcheck'] = $props['spellcheck'] ? 'true' : 'false';
}

// Add validation attributes
foreach ($props['validation'] as $key => $value) {
    if ($value !== '') {
        $input_attrs[$key] = $value;
    }
}

// Add data attributes
foreach ($props['data'] as $key => $value) {
    $input_attrs['data-' . $key] = $value;
}

// Add custom attributes
foreach ($props['attributes'] as $key => $value) {
    if (!isset($input_attrs[$key])) {
        $input_attrs[$key] = $value;
    }
}

?>

<div class="<?php echo esc_attr(implode(' ', $container_classes)); ?>">
    
    <?php if ($props['label'] && $props['label_position'] !== 'hidden'): ?>
    <label class="hph-form-label" for="<?php echo esc_attr($props['id']); ?>">
        <span class="hph-form-label__text">
            <?php echo esc_html($props['label']); ?>
            <?php if ($props['required']): ?>
                <span class="hph-form-label__required" aria-label="required">
                    <?php echo esc_html($props['required_indicator']); ?>
                </span>
            <?php endif; ?>
        </span>
        
        <?php if ($props['help'] && $props['help_position'] === 'tooltip'): ?>
            <span class="hph-form-label__help" data-tooltip="<?php echo esc_attr($props['help']); ?>">
                <span data-icon="help-circle"></span>
            </span>
        <?php endif; ?>
    </label>
    <?php endif; ?>
    
    <?php if ($props['help'] && $props['help_position'] === 'top'): ?>
    <div class="hph-form-help hph-form-help--top">
        <?php echo esc_html($props['help']); ?>
    </div>
    <?php endif; ?>
    
    <div class="hph-form-input-wrapper">
        
        <?php if ($props['icon_left'] || $props['prefix']): ?>
        <div class="hph-form-addon hph-form-addon--before">
            <?php if ($props['icon_left']): ?>
                <span class="hph-form-icon" data-icon="<?php echo esc_attr($props['icon_left']); ?>"></span>
            <?php endif; ?>
            <?php if ($props['prefix']): ?>
                <span class="hph-form-prefix"><?php echo esc_html($props['prefix']); ?></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($props['addon_before']): ?>
        <div class="hph-form-addon hph-form-addon--button-before">
            <?php echo $props['addon_before']; // Should be pre-rendered HTML ?>
        </div>
        <?php endif; ?>
        
        <?php 
        // Render input based on type
        switch($props['type']) {
            case 'textarea':
                ?>
                <textarea 
                    <?php hph_render_attributes($input_attrs); ?>
                    rows="<?php echo esc_attr($props['rows']); ?>"
                    <?php if ($props['resize'] !== 'both'): ?>
                    style="resize: <?php echo esc_attr($props['resize']); ?>"
                    <?php endif; ?>
                    <?php if ($props['autosize']): ?>
                    data-autosize="true"
                    <?php endif; ?>
                ><?php echo esc_textarea($props['value']); ?></textarea>
                <?php
                break;
                
            case 'select':
                ?>
                <select <?php hph_render_attributes($input_attrs); ?> <?php echo $props['multiple'] ? 'multiple' : ''; ?>>
                    <?php if ($props['placeholder'] && !$props['multiple']): ?>
                        <option value=""><?php echo esc_html($props['placeholder']); ?></option>
                    <?php endif; ?>
                    
                    <?php if (!empty($props['groups'])): ?>
                        <?php foreach ($props['groups'] as $group): ?>
                            <optgroup label="<?php echo esc_attr($group['label']); ?>">
                                <?php foreach ($group['options'] as $value => $label): ?>
                                    <option 
                                        value="<?php echo esc_attr($value); ?>"
                                        <?php selected($props['value'], $value); ?>
                                    ><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php foreach ($props['options'] as $value => $label): ?>
                            <option 
                                value="<?php echo esc_attr($value); ?>"
                                <?php selected($props['value'], $value); ?>
                            ><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <?php
                break;
                
            case 'checkbox':
            case 'radio':
                if (!empty($props['options'])) {
                    // Multiple checkboxes/radios
                    ?>
                    <div class="hph-form-group hph-form-group--<?php echo esc_attr($props['type']); ?>">
                        <?php foreach ($props['options'] as $value => $label): ?>
                            <label class="hph-form-check">
                                <input 
                                    type="<?php echo esc_attr($props['type']); ?>"
                                    name="<?php echo esc_attr($props['name']); ?><?php echo $props['type'] === 'checkbox' ? '[]' : ''; ?>"
                                    value="<?php echo esc_attr($value); ?>"
                                    <?php checked(
                                        is_array($props['value']) 
                                            ? in_array($value, $props['value']) 
                                            : $props['value'] == $value
                                    ); ?>
                                    <?php echo $props['disabled'] ? 'disabled' : ''; ?>
                                    <?php echo $props['required'] ? 'required' : ''; ?>
                                    class="hph-form-check__input"
                                >
                                <span class="hph-form-check__label"><?php echo esc_html($label); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?php
                } else {
                    // Single checkbox
                    ?>
                    <label class="hph-form-check hph-form-check--single">
                        <input <?php hph_render_attributes($input_attrs); ?> <?php checked($props['value']); ?>>
                        <?php if ($props['label'] && $props['label_position'] === 'inline'): ?>
                            <span class="hph-form-check__label"><?php echo esc_html($props['label']); ?></span>
                        <?php endif; ?>
                    </label>
                    <?php
                }
                break;
                
            case 'toggle':
                ?>
                <label class="hph-form-toggle">
                    <input 
                        type="checkbox"
                        <?php hph_render_attributes($input_attrs); ?>
                        <?php checked($props['value']); ?>
                        class="hph-form-toggle__input"
                    >
                    <span class="hph-form-toggle__slider"></span>
                    <?php if ($props['label'] && $props['label_position'] === 'inline'): ?>
                        <span class="hph-form-toggle__label"><?php echo esc_html($props['label']); ?></span>
                    <?php endif; ?>
                </label>
                <?php
                break;
                
            case 'range':
                ?>
                <div class="hph-form-range-wrapper">
                    <input <?php hph_render_attributes($input_attrs); ?>>
                    <?php if ($props['show_value']): ?>
                        <output class="hph-form-range__value" for="<?php echo esc_attr($props['id']); ?>">
                            <?php echo esc_html($props['value']); ?>
                        </output>
                    <?php endif; ?>
                </div>
                <?php
                break;
                
            case 'file':
                if ($props['dropzone']) {
                    ?>
                    <div class="hph-form-dropzone" data-input-id="<?php echo esc_attr($props['id']); ?>">
                        <input 
                            <?php hph_render_attributes($input_attrs); ?>
                            <?php echo $props['accept'] ? 'accept="' . esc_attr($props['accept']) . '"' : ''; ?>
                            <?php echo $props['multiple_files'] ? 'multiple' : ''; ?>
                            style="display: none;"
                        >
                        <div class="hph-form-dropzone__content">
                            <span class="hph-form-dropzone__icon" data-icon="upload"></span>
                            <span class="hph-form-dropzone__text">
                                Drag & drop files here or <button type="button" class="hph-form-dropzone__browse">browse</button>
                            </span>
                            <?php if ($props['help']): ?>
                                <span class="hph-form-dropzone__help"><?php echo esc_html($props['help']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="hph-form-dropzone__files"></div>
                    </div>
                    <?php
                } else {
                    ?>
                    <input 
                        <?php hph_render_attributes($input_attrs); ?>
                        <?php echo $props['accept'] ? 'accept="' . esc_attr($props['accept']) . '"' : ''; ?>
                        <?php echo $props['multiple_files'] ? 'multiple' : ''; ?>
                    >
                    <?php
                }
                break;
                
            case 'chips':
                ?>
                <div class="hph-form-chips" data-name="<?php echo esc_attr($props['name']); ?>">
                    <div class="hph-form-chips__list">
                        <?php if (is_array($props['value'])): ?>
                            <?php foreach ($props['value'] as $chip): ?>
                                <span class="hph-form-chip">
                                    <span class="hph-form-chip__text"><?php echo esc_html($chip); ?></span>
                                    <button type="button" class="hph-form-chip__remove" data-icon="x"></button>
                                    <input type="hidden" name="<?php echo esc_attr($props['name']); ?>[]" value="<?php echo esc_attr($chip); ?>">
                                </span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <input 
                        type="text"
                        class="hph-form-chips__input"
                        placeholder="<?php echo esc_attr($props['placeholder']); ?>"
                        data-chips-input="true"
                    >
                </div>
                <?php
                break;
                
            default:
                // Standard input types
                ?>
                <input <?php hph_render_attributes($input_attrs); ?>>
                <?php
                break;
        }
        ?>
        
        <?php if ($props['clearable'] && !in_array($props['type'], array('checkbox', 'radio', 'toggle'))): ?>
        <button type="button" class="hph-form-clear" aria-label="Clear">
            <span data-icon="x-circle"></span>
        </button>
        <?php endif; ?>
        
        <?php if ($props['icon_right'] || $props['suffix']): ?>
        <div class="hph-form-addon hph-form-addon--after">
            <?php if ($props['suffix']): ?>
                <span class="hph-form-suffix"><?php echo esc_html($props['suffix']); ?></span>
            <?php endif; ?>
            <?php if ($props['icon_right']): ?>
                <span class="hph-form-icon" data-icon="<?php echo esc_attr($props['icon_right']); ?>"></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($props['addon_after']): ?>
        <div class="hph-form-addon hph-form-addon--button-after">
            <?php echo $props['addon_after']; // Should be pre-rendered HTML ?>
        </div>
        <?php endif; ?>
        
    </div>
    
    <?php if ($props['counter'] && in_array($props['type'], array('text', 'textarea'))): ?>
    <div class="hph-form-counter">
        <span class="hph-form-counter__current">0</span>
        <?php if ($props['validation']['maxlength']): ?>
            / <span class="hph-form-counter__max"><?php echo esc_html($props['validation']['maxlength']); ?></span>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <?php if ($props['error']): ?>
    <div class="hph-form-message hph-form-message--error" role="alert">
        <span data-icon="alert-circle"></span>
        <?php echo esc_html($props['error']); ?>
    </div>
    <?php endif; ?>
    
    <?php if ($props['success']): ?>
    <div class="hph-form-message hph-form-message--success">
        <span data-icon="check-circle"></span>
        <?php echo esc_html($props['success']); ?>
    </div>
    <?php endif; ?>
    
    <?php if ($props['help'] && $props['help_position'] === 'bottom'): ?>
    <div class="hph-form-help">
        <?php echo esc_html($props['help']); ?>
    </div>
    <?php endif; ?>
    
</div>