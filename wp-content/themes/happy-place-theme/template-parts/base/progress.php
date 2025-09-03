<?php
/**
 * Base Progress Component
 * Visual progress indicator with multiple variants and animations
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Progress component configuration
 */
$defaults = [
    // Progress value
    'value' => 0, // 0-100
    'max' => 100,
    'min' => 0,
    
    // Display options
    'show_value' => true,
    'show_label' => true,
    'label' => '',
    'value_format' => 'percentage', // percentage, fraction, custom
    'custom_format' => '', // Custom format string
    
    // Style variants
    'variant' => 'default', // default, primary, success, warning, error, info
    'size' => 'md', // xs, sm, md, lg, xl
    'shape' => 'rounded', // rounded, square, pill
    
    // Visual features
    'animated' => false,
    'striped' => false,
    'gradient' => false,
    'pulse' => false,
    'indeterminate' => false,
    
    // Multiple progress bars (stacked)
    'segments' => [], // Array of progress segments
    'stacked' => false,
    
    // Accessibility
    'aria_label' => '',
    'aria_describedby' => '',
    'live_region' => true,
    
    // CSS classes
    'container_class' => '',
    'bar_class' => '',
    'fill_class' => '',
    'label_class' => '',
    
    // Data attributes
    'data_attributes' => [],
];

$props = wp_parse_args(hph_get_arg() ?? [], $defaults);

// Normalize value
$value = max($props['min'], min($props['max'], floatval($props['value'])));
$percentage = $props['max'] > 0 ? ($value / $props['max']) * 100 : 0;

// Generate unique ID
$progress_id = $props['id'] ?? 'hph-progress-' . wp_unique_id();

// Build CSS classes
$container_classes = [
    'hph-progress-container',
    'hph-progress-container--' . $props['size'],
];

if (!empty($props['container_class'])) {
    $container_classes[] = $props['container_class'];
}

$bar_classes = [
    'hph-progress',
    'hph-progress--' . $props['variant'],
    'hph-progress--' . $props['shape'],
];

if ($props['animated']) {
    $bar_classes[] = 'hph-progress--animated';
}

if ($props['striped']) {
    $bar_classes[] = 'hph-progress--striped';
}

if ($props['gradient']) {
    $bar_classes[] = 'hph-progress--gradient';
}

if ($props['pulse']) {
    $bar_classes[] = 'hph-progress--pulse';
}

if ($props['indeterminate']) {
    $bar_classes[] = 'hph-progress--indeterminate';
}

if ($props['stacked']) {
    $bar_classes[] = 'hph-progress--stacked';
}

if (!empty($props['bar_class'])) {
    $bar_classes[] = $props['bar_class'];
}

$fill_classes = [
    'hph-progress__fill',
];

if (!empty($props['fill_class'])) {
    $fill_classes[] = $props['fill_class'];
}

$label_classes = [
    'hph-progress__label',
];

if (!empty($props['label_class'])) {
    $label_classes[] = $props['label_class'];
}

// Format value display
function format_progress_value($value, $max, $format, $custom_format = '') {
    switch ($format) {
        case 'percentage':
            return round(($value / $max) * 100) . '%';
        case 'fraction':
            return $value . '/' . $max;
        case 'custom':
            return sprintf($custom_format, $value, $max, round(($value / $max) * 100));
        default:
            return round(($value / $max) * 100) . '%';
    }
}

$formatted_value = format_progress_value($value, $props['max'], $props['value_format'], $props['custom_format']);

// Data attributes
$data_attrs = [
    'data-progress-value' => $value,
    'data-progress-max' => $props['max'],
    'data-progress-percentage' => round($percentage, 2),
];

if (!empty($props['data_attributes'])) {
    $data_attrs = array_merge($data_attrs, $props['data_attributes']);
}

// Accessibility attributes
$progress_attrs = [
    'role' => 'progressbar',
    'aria-valuenow' => $value,
    'aria-valuemin' => $props['min'],
    'aria-valuemax' => $props['max'],
];

if ($props['aria_label']) {
    $progress_attrs['aria-label'] = $props['aria_label'];
} elseif ($props['label']) {
    $progress_attrs['aria-labelledby'] = $progress_id . '-label';
}

if ($props['aria_describedby']) {
    $progress_attrs['aria-describedby'] = $props['aria_describedby'];
}

if ($props['indeterminate']) {
    unset($progress_attrs['aria-valuenow']);
    $progress_attrs['aria-valuetext'] = 'Loading...';
}

// Build attributes helper
function build_progress_attrs($attrs) {
    $output = [];
    foreach ($attrs as $key => $value) {
        if ($value !== null && $value !== '') {
            $output[] = esc_attr($key) . '="' . esc_attr($value) . '"';
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
    <?php if ($props['show_label'] && ($props['label'] || $props['show_value'])): ?>
        <div class="hph-progress__header">
            <?php if ($props['label']): ?>
                <div id="<?php echo esc_attr($progress_id); ?>-label" class="<?php echo esc_attr(implode(' ', $label_classes)); ?>">
                    <?php echo esc_html($props['label']); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($props['show_value'] && !$props['indeterminate']): ?>
                <div class="hph-progress__value" <?php if ($props['live_region']): ?>aria-live="polite"<?php endif; ?>>
                    <?php echo esc_html($formatted_value); ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div 
        class="<?php echo esc_attr(implode(' ', $bar_classes)); ?>"
        <?php echo build_progress_attrs($progress_attrs); ?>
    >
        <?php if ($props['stacked'] && !empty($props['segments'])): ?>
            <?php foreach ($props['segments'] as $segment): ?>
                <?php
                $segment_value = max(0, min($props['max'], floatval($segment['value'] ?? 0)));
                $segment_percentage = $props['max'] > 0 ? ($segment_value / $props['max']) * 100 : 0;
                $segment_variant = $segment['variant'] ?? 'default';
                ?>
                <div 
                    class="hph-progress__fill hph-progress__fill--<?php echo esc_attr($segment_variant); ?>"
                    style="width: <?php echo esc_attr($segment_percentage); ?>%;"
                    data-segment-value="<?php echo esc_attr($segment_value); ?>"
                    data-segment-label="<?php echo esc_attr($segment['label'] ?? ''); ?>"
                >
                    <?php if ($props['striped']): ?>
                        <div class="hph-progress__stripes"></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div 
                class="<?php echo esc_attr(implode(' ', $fill_classes)); ?>"
                style="<?php if (!$props['indeterminate']): ?>width: <?php echo esc_attr($percentage); ?>%;<?php endif; ?>"
            >
                <?php if ($props['striped']): ?>
                    <div class="hph-progress__stripes"></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($props['show_value'] && !$props['show_label'] && !$props['indeterminate']): ?>
        <div class="hph-progress__value hph-progress__value--standalone" <?php if ($props['live_region']): ?>aria-live="polite"<?php endif; ?>>
            <?php echo esc_html($formatted_value); ?>
        </div>
    <?php endif; ?>
</div>

<?php
/**
 * Usage Examples:
 * 
 * Basic progress bar:
 * get_template_part('template-parts/base/progress', null, [
 *     'value' => 65,
 *     'label' => 'Upload Progress'
 * ]);
 * 
 * Animated striped progress:
 * get_template_part('template-parts/base/progress', null, [
 *     'value' => 45,
 *     'variant' => 'primary',
 *     'animated' => true,
 *     'striped' => true,
 *     'label' => 'Processing'
 * ]);
 * 
 * Stacked progress (multiple segments):
 * get_template_part('template-parts/base/progress', null, [
 *     'stacked' => true,
 *     'segments' => [
 *         ['value' => 30, 'variant' => 'success', 'label' => 'Completed'],
 *         ['value' => 20, 'variant' => 'warning', 'label' => 'In Progress'],
 *         ['value' => 10, 'variant' => 'error', 'label' => 'Failed']
 *     ],
 *     'label' => 'Project Status'
 * ]);
 * 
 * Indeterminate progress:
 * get_template_part('template-parts/base/progress', null, [
 *     'indeterminate' => true,
 *     'variant' => 'primary',
 *     'label' => 'Loading...'
 * ]);
 */
?>
