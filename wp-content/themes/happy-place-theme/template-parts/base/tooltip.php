<?php
/**
 * Base Tooltip Component
 * Contextual information overlay with comprehensive configuration options
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Tooltip component configuration
 */
$defaults = [
    // Content
    'content' => '',
    'title' => '',
    'trigger_content' => '',
    'trigger_type' => 'span', // span, button, div, a
    
    // Behavior
    'trigger' => 'hover', // hover, click, focus, manual
    'placement' => 'top', // top, bottom, left, right, auto
    'offset' => 8,
    'delay' => [0, 0], // [show_delay, hide_delay] in milliseconds
    'duration' => 300,
    'interactive' => false,
    'follow_cursor' => false,
    'hide_on_click' => true,
    
    // Style variants
    'variant' => 'default', // default, dark, light, error, warning, success, info
    'size' => 'md', // sm, md, lg
    'max_width' => '320px',
    'arrow' => true,
    
    // Content types
    'allow_html' => false,
    'rich_content' => false,
    
    // Accessibility
    'role' => 'tooltip',
    'live_region' => false,
    
    // CSS classes
    'container_class' => '',
    'trigger_class' => '',
    'tooltip_class' => '',
    
    // Data attributes
    'data_attributes' => [],
    
    // Advanced features
    'boundary' => 'viewport', // viewport, window, element
    'flip' => true,
    'prevent_overflow' => true,
    'z_index' => 9999,
];

$props = wp_parse_args(hph_get_arg() ?? [], $defaults);

// Generate unique ID
$tooltip_id = $props['id'] ?? 'hph-tooltip-' . wp_unique_id();
$trigger_id = $tooltip_id . '-trigger';
$content_id = $tooltip_id . '-content';

// Build CSS classes
$container_classes = [
    'hph-tooltip-container',
];

if (!empty($props['container_class'])) {
    $container_classes[] = $props['container_class'];
}

$trigger_classes = [
    'hph-tooltip-trigger',
];

if (!empty($props['trigger_class'])) {
    $trigger_classes[] = $props['trigger_class'];
}

$tooltip_classes = [
    'hph-tooltip',
    'hph-tooltip--' . $props['variant'],
    'hph-tooltip--' . $props['size'],
    'hph-tooltip--' . $props['placement'],
];

if ($props['interactive']) {
    $tooltip_classes[] = 'hph-tooltip--interactive';
}

if ($props['arrow']) {
    $tooltip_classes[] = 'hph-tooltip--with-arrow';
}

if (!empty($props['tooltip_class'])) {
    $tooltip_classes[] = $props['tooltip_class'];
}

// Data attributes
$data_attrs = [
    'data-tooltip-trigger' => $props['trigger'],
    'data-tooltip-placement' => $props['placement'],
    'data-tooltip-offset' => $props['offset'],
    'data-tooltip-interactive' => $props['interactive'] ? 'true' : 'false',
];

if ($props['delay'][0] > 0 || $props['delay'][1] > 0) {
    $data_attrs['data-tooltip-delay'] = json_encode($props['delay']);
}

if ($props['duration']) {
    $data_attrs['data-tooltip-duration'] = $props['duration'];
}

if ($props['follow_cursor']) {
    $data_attrs['data-tooltip-follow-cursor'] = 'true';
}

if (!$props['hide_on_click']) {
    $data_attrs['data-tooltip-hide-on-click'] = 'false';
}

if ($props['max_width']) {
    $data_attrs['data-tooltip-max-width'] = $props['max_width'];
}

if (!empty($props['data_attributes'])) {
    $data_attrs = array_merge($data_attrs, $props['data_attributes']);
}

// Trigger attributes
$trigger_attrs = [
    'id' => $trigger_id,
    'class' => implode(' ', $trigger_classes),
];

// Accessibility attributes
if ($props['trigger'] === 'hover' || $props['trigger'] === 'focus') {
    $trigger_attrs['aria-describedby'] = $content_id;
} else {
    $trigger_attrs['aria-labelledby'] = $content_id;
    $trigger_attrs['aria-expanded'] = 'false';
}

if ($props['trigger_type'] === 'button') {
    $trigger_attrs['type'] = 'button';
}

// Tooltip content attributes
$content_attrs = [
    'id' => $content_id,
    'class' => implode(' ', $tooltip_classes),
    'role' => $props['role'],
    'style' => 'max-width: ' . esc_attr($props['max_width']) . '; z-index: ' . esc_attr($props['z_index']) . ';',
];

if ($props['live_region']) {
    $content_attrs['aria-live'] = 'polite';
}

// Build attributes helper
function build_tooltip_attrs($attrs) {
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
    <!-- Trigger Element -->
    <<?php echo esc_attr($props['trigger_type']); ?> <?php echo build_tooltip_attrs($trigger_attrs); ?>>
        <?php if ($props['trigger_content']): ?>
            <?php echo wp_kses_post($props['trigger_content']); ?>
        <?php else: ?>
            <!-- Default trigger icon if no content provided -->
            <svg class="hph-tooltip-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                <circle cx="12" cy="17" r="1" fill="currentColor"/>
            </svg>
        <?php endif; ?>
    </<?php echo esc_attr($props['trigger_type']); ?>>

    <!-- Tooltip Content -->
    <div <?php echo build_tooltip_attrs($content_attrs); ?> hidden>
        <?php if ($props['arrow']): ?>
            <div class="hph-tooltip__arrow" data-popper-arrow></div>
        <?php endif; ?>
        
        <div class="hph-tooltip__content">
            <?php if ($props['title']): ?>
                <div class="hph-tooltip__title">
                    <?php echo esc_html($props['title']); ?>
                </div>
            <?php endif; ?>
            
            <div class="hph-tooltip__body">
                <?php if ($props['allow_html'] || $props['rich_content']): ?>
                    <?php echo wp_kses_post($props['content']); ?>
                <?php else: ?>
                    <?php echo esc_html($props['content']); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($props['rich_content']): ?>
    <!-- Rich content example structure -->
    <script type="application/json" class="hph-tooltip-rich-content" data-tooltip="<?php echo esc_attr($tooltip_id); ?>">
    {
        "title": <?php echo json_encode($props['title']); ?>,
        "content": <?php echo json_encode($props['content']); ?>,
        "variant": <?php echo json_encode($props['variant']); ?>
    }
    </script>
<?php endif; ?>
