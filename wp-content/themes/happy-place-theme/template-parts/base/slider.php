<?php
/**
 * Base Slider Component
 * Range slider for price filters, numeric inputs, and value selection
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slider component configuration
 */
$defaults = [
    // Basic configuration
    'type' => 'single', // single, range
    'value' => 0, // For single slider
    'values' => [0, 100], // For range slider [min_value, max_value]
    'min' => 0,
    'max' => 100,
    'step' => 1,
    
    // Display options
    'label' => '',
    'show_values' => true,
    'show_labels' => true,
    'show_ticks' => false,
    'show_tooltip' => true,
    
    // Formatting
    'format' => 'number', // number, currency, percentage
    'currency_symbol' => '$',
    'currency_position' => 'before', // before, after
    'decimal_places' => 0,
    'thousands_separator' => ',',
    'prefix' => '',
    'suffix' => '',
    
    // Style variants
    'variant' => 'default', // default, primary, success, warning, error
    'size' => 'md', // sm, md, lg
    'track_style' => 'default', // default, gradient, stepped
    
    // Behavior
    'disabled' => false,
    'readonly' => false,
    'snap_to_ticks' => false,
    'connect_range' => true, // For range sliders, connect the area between handles
    
    // Advanced options
    'orientation' => 'horizontal', // horizontal, vertical
    'reverse' => false,
    'animate' => true,
    'keyboard_navigation' => true,
    
    // Tick marks
    'ticks' => [], // Array of tick values
    'tick_labels' => [], // Custom labels for ticks
    
    // Events
    'on_change' => '',
    'on_start' => '',
    'on_end' => '',
    
    // Accessibility
    'aria_label' => '',
    'aria_describedby' => '',
    
    // CSS classes
    'container_class' => '',
    'slider_class' => '',
    'handle_class' => '',
    'track_class' => '',
    
    // Data attributes
    'data_attributes' => [],
];

$props = wp_parse_args($props ?? [], $defaults);

// Generate unique ID
$slider_id = $props['id'] ?? 'hph-slider-' . wp_unique_id();

// Determine values for range vs single
$is_range = $props['type'] === 'range';
$current_values = $is_range ? $props['values'] : [$props['value']];

// Format value function
function format_slider_value($value, $format, $props) {
    switch ($format) {
        case 'currency':
            $formatted = number_format($value, $props['decimal_places'], '.', $props['thousands_separator']);
            return $props['currency_position'] === 'before' 
                ? $props['currency_symbol'] . $formatted 
                : $formatted . $props['currency_symbol'];
        case 'percentage':
            return number_format($value, $props['decimal_places']) . '%';
        case 'number':
        default:
            return $props['prefix'] . number_format($value, $props['decimal_places'], '.', $props['thousands_separator']) . $props['suffix'];
    }
}

// Build CSS classes
$container_classes = [
    'hph-slider-container',
    'hph-slider-container--' . $props['size'],
    'hph-slider-container--' . $props['orientation'],
];

if ($props['disabled']) {
    $container_classes[] = 'hph-slider-container--disabled';
}

if (!empty($props['container_class'])) {
    $container_classes[] = $props['container_class'];
}

$slider_classes = [
    'hph-slider',
    'hph-slider--' . $props['variant'],
    'hph-slider--' . $props['track_style'],
];

if ($is_range) {
    $slider_classes[] = 'hph-slider--range';
}

if ($props['show_ticks']) {
    $slider_classes[] = 'hph-slider--with-ticks';
}

if (!empty($props['slider_class'])) {
    $slider_classes[] = $props['slider_class'];
}

// Data attributes for JavaScript
$data_attrs = [
    'data-slider-type' => $props['type'],
    'data-slider-min' => $props['min'],
    'data-slider-max' => $props['max'],
    'data-slider-step' => $props['step'],
    'data-slider-format' => $props['format'],
    'data-slider-animate' => $props['animate'] ? 'true' : 'false',
];

if ($is_range) {
    $data_attrs['data-slider-values'] = json_encode($current_values);
} else {
    $data_attrs['data-slider-value'] = $current_values[0];
}

if (!empty($props['ticks'])) {
    $data_attrs['data-slider-ticks'] = json_encode($props['ticks']);
}

if (!empty($props['data_attributes'])) {
    $data_attrs = array_merge($data_attrs, $props['data_attributes']);
}

// Calculate percentages for positioning
function value_to_percentage($value, $min, $max) {
    return (($value - $min) / ($max - $min)) * 100;
}
?>

<div 
    class="<?php echo esc_attr(implode(' ', $container_classes)); ?>"
    <?php foreach ($data_attrs as $key => $value): ?>
        <?php echo esc_attr($key); ?>="<?php echo esc_attr($value); ?>"
    <?php endforeach; ?>
>
    <?php if ($props['label'] || $props['show_values']): ?>
        <div class="hph-slider__header">
            <?php if ($props['label']): ?>
                <label for="<?php echo esc_attr($slider_id); ?>" class="hph-slider__label">
                    <?php echo esc_html($props['label']); ?>
                </label>
            <?php endif; ?>
            
            <?php if ($props['show_values']): ?>
                <div class="hph-slider__values">
                    <?php if ($is_range): ?>
                        <span class="hph-slider__value hph-slider__value--min" data-value="min">
                            <?php echo esc_html(format_slider_value($current_values[0], $props['format'], $props)); ?>
                        </span>
                        <span class="hph-slider__separator">-</span>
                        <span class="hph-slider__value hph-slider__value--max" data-value="max">
                            <?php echo esc_html(format_slider_value($current_values[1], $props['format'], $props)); ?>
                        </span>
                    <?php else: ?>
                        <span class="hph-slider__value" data-value="single">
                            <?php echo esc_html(format_slider_value($current_values[0], $props['format'], $props)); ?>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="hph-slider__wrapper">
        <div 
            id="<?php echo esc_attr($slider_id); ?>"
            class="<?php echo esc_attr(implode(' ', $slider_classes)); ?>"
            role="slider"
            aria-valuemin="<?php echo esc_attr($props['min']); ?>"
            aria-valuemax="<?php echo esc_attr($props['max']); ?>"
            <?php if (!$is_range): ?>
                aria-valuenow="<?php echo esc_attr($current_values[0]); ?>"
            <?php endif; ?>
            <?php if ($props['aria_label']): ?>
                aria-label="<?php echo esc_attr($props['aria_label']); ?>"
            <?php endif; ?>
            <?php if ($props['aria_describedby']): ?>
                aria-describedby="<?php echo esc_attr($props['aria_describedby']); ?>"
            <?php endif; ?>
            tabindex="0"
        >
            <!-- Track -->
            <div class="hph-slider__track">
                <!-- Range fill -->
                <?php if ($is_range && $props['connect_range']): ?>
                    <div 
                        class="hph-slider__range"
                        style="left: <?php echo value_to_percentage($current_values[0], $props['min'], $props['max']); ?>%; width: <?php echo value_to_percentage($current_values[1] - $current_values[0], 0, $props['max'] - $props['min']); ?>%;"
                    ></div>
                <?php elseif (!$is_range): ?>
                    <div 
                        class="hph-slider__fill"
                        style="width: <?php echo value_to_percentage($current_values[0], $props['min'], $props['max']); ?>%;"
                    ></div>
                <?php endif; ?>
                
                <!-- Ticks -->
                <?php if ($props['show_ticks'] && !empty($props['ticks'])): ?>
                    <div class="hph-slider__ticks">
                        <?php foreach ($props['ticks'] as $index => $tick): ?>
                            <div 
                                class="hph-slider__tick"
                                style="left: <?php echo value_to_percentage($tick, $props['min'], $props['max']); ?>%;"
                            >
                                <?php if (!empty($props['tick_labels'][$index])): ?>
                                    <span class="hph-slider__tick-label">
                                        <?php echo esc_html($props['tick_labels'][$index]); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Handles -->
            <?php if ($is_range): ?>
                <div 
                    class="hph-slider__handle hph-slider__handle--min <?php echo esc_attr($props['handle_class']); ?>"
                    style="left: <?php echo value_to_percentage($current_values[0], $props['min'], $props['max']); ?>%;"
                    role="slider"
                    aria-valuemin="<?php echo esc_attr($props['min']); ?>"
                    aria-valuemax="<?php echo esc_attr($current_values[1]); ?>"
                    aria-valuenow="<?php echo esc_attr($current_values[0]); ?>"
                    aria-label="Minimum value"
                    tabindex="0"
                >
                    <?php if ($props['show_tooltip']): ?>
                        <div class="hph-slider__tooltip">
                            <?php echo esc_html(format_slider_value($current_values[0], $props['format'], $props)); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div 
                    class="hph-slider__handle hph-slider__handle--max <?php echo esc_attr($props['handle_class']); ?>"
                    style="left: <?php echo value_to_percentage($current_values[1], $props['min'], $props['max']); ?>%;"
                    role="slider"
                    aria-valuemin="<?php echo esc_attr($current_values[0]); ?>"
                    aria-valuemax="<?php echo esc_attr($props['max']); ?>"
                    aria-valuenow="<?php echo esc_attr($current_values[1]); ?>"
                    aria-label="Maximum value"
                    tabindex="0"
                >
                    <?php if ($props['show_tooltip']): ?>
                        <div class="hph-slider__tooltip">
                            <?php echo esc_html(format_slider_value($current_values[1], $props['format'], $props)); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div 
                    class="hph-slider__handle <?php echo esc_attr($props['handle_class']); ?>"
                    style="left: <?php echo value_to_percentage($current_values[0], $props['min'], $props['max']); ?>%;"
                >
                    <?php if ($props['show_tooltip']): ?>
                        <div class="hph-slider__tooltip">
                            <?php echo esc_html(format_slider_value($current_values[0], $props['format'], $props)); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Hidden inputs for form submission -->
        <?php if ($is_range): ?>
            <input 
                type="hidden" 
                name="<?php echo esc_attr($props['name'] ?? $slider_id); ?>_min" 
                value="<?php echo esc_attr($current_values[0]); ?>"
                class="hph-slider__input hph-slider__input--min"
            >
            <input 
                type="hidden" 
                name="<?php echo esc_attr($props['name'] ?? $slider_id); ?>_max" 
                value="<?php echo esc_attr($current_values[1]); ?>"
                class="hph-slider__input hph-slider__input--max"
            >
        <?php else: ?>
            <input 
                type="hidden" 
                name="<?php echo esc_attr($props['name'] ?? $slider_id); ?>" 
                value="<?php echo esc_attr($current_values[0]); ?>"
                class="hph-slider__input"
            >
        <?php endif; ?>
    </div>

    <?php if ($props['show_labels'] && ($props['min'] !== 0 || $props['max'] !== 100)): ?>
        <div class="hph-slider__labels">
            <span class="hph-slider__label-min">
                <?php echo esc_html(format_slider_value($props['min'], $props['format'], $props)); ?>
            </span>
            <span class="hph-slider__label-max">
                <?php echo esc_html(format_slider_value($props['max'], $props['format'], $props)); ?>
            </span>
        </div>
    <?php endif; ?>
</div>

<?php
/**
 * Usage Examples:
 * 
 * Price range slider:
 * get_template_part('template-parts/base/slider', null, [
 *     'type' => 'range',
 *     'values' => [300000, 800000],
 *     'min' => 0,
 *     'max' => 2000000,
 *     'step' => 10000,
 *     'format' => 'currency',
 *     'label' => 'Price Range',
 *     'show_values' => true
 * ]);
 * 
 * Property size slider:
 * get_template_part('template-parts/base/slider', null, [
 *     'type' => 'single',
 *     'value' => 1200,
 *     'min' => 500,
 *     'max' => 5000,
 *     'step' => 50,
 *     'suffix' => ' sq ft',
 *     'label' => 'Minimum Size'
 * ]);
 */
?>
