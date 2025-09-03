<?php
/**
 * Base Alert Component
 * 
 * Pure UI alert/notification component for messages, warnings, and info
 * 
 * @package HappyPlaceTheme
 * @subpackage Components/Base
 * @since 3.0.0
 */

$props = wp_parse_args(get_query_var('args', array()), array(
    // Content
    'title' => '',
    'message' => '',
    'details' => '', // Extended details/description
    'actions' => array(), // Action buttons
    
    // Appearance  
    'variant' => 'info', // info, success, warning, danger, neutral, custom
    'style' => 'default', // default, solid, outline, soft, minimal
    'size' => 'md', // sm, md, lg
    'icon' => '', // Custom icon, or auto based on variant
    'show_icon' => true,
    'icon_animate' => false, // Pulse, spin, etc.
    
    // Layout
    'layout' => 'horizontal', // horizontal, vertical, compact
    'position' => 'relative', // relative, fixed, absolute, sticky
    'placement' => 'top', // top, top-left, top-right, bottom, bottom-left, bottom-right, center
    
    // Behavior
    'dismissible' => false,
    'auto_dismiss' => 0, // Auto dismiss after X milliseconds
    'persist' => false, // Persist in localStorage
    'persist_key' => '',
    'collapsible' => false, // Can expand/collapse details
    'collapsed' => false, // Start collapsed
    
    // Animation
    'animation' => 'fade', // fade, slide, bounce, none
    'animation_direction' => 'down', // up, down, left, right
    
    // Progress (for timed alerts)
    'show_progress' => false,
    'progress_position' => 'bottom', // top, bottom
    
    // State
    'visible' => true,
    'loading' => false,
    
    // HTML
    'id' => '',
    'class' => '',
    'attributes' => array(),
    'data' => array(),
    'role' => 'alert' // alert, status, log
));

// Auto-set icon based on variant if not provided
if ($props['show_icon'] && !$props['icon']) {
    $variant_icons = array(
        'info' => 'info-circle',
        'success' => 'check-circle', 
        'warning' => 'alert-triangle',
        'danger' => 'alert-circle',
        'neutral' => 'message-circle'
    );
    $props['icon'] = $variant_icons[$props['variant']] ?? 'info-circle';
}

// Generate ID if needed
if (!$props['id']) {
    $props['id'] = 'hph-alert-' . substr(md5($props['message']), 0, 8);
}

// Build alert classes
$alert_classes = array(
    'hph-alert',
    'hph-alert--' . $props['variant'],
    'hph-alert--' . $props['style'],
    'hph-alert--' . $props['size'],
    'hph-alert--' . $props['layout']
);

if ($props['position'] !== 'relative') {
    $alert_classes[] = 'hph-alert--' . $props['position'];
    $alert_classes[] = 'hph-alert--' . $props['placement'];
}

if ($props['dismissible']) {
    $alert_classes[] = 'hph-alert--dismissible';
}

if ($props['collapsible']) {
    $alert_classes[] = 'hph-alert--collapsible';
}

if ($props['collapsed']) {
    $alert_classes[] = 'is-collapsed';
}

if (!$props['visible']) {
    $alert_classes[] = 'is-hidden';
}

if ($props['loading']) {
    $alert_classes[] = 'is-loading';
}

if ($props['animation'] !== 'none') {
    $alert_classes[] = 'hph-alert--animated';
    $alert_classes[] = 'hph-alert--' . $props['animation'];
    if ($props['animation'] === 'slide') {
        $alert_classes[] = 'hph-alert--slide-' . $props['animation_direction'];
    }
}

if ($props['class']) {
    $alert_classes[] = $props['class'];
}

// Build attributes
$attributes = array(
    'id' => $props['id'],
    'class' => implode(' ', $alert_classes),
    'role' => $props['role']
);

if ($props['role'] === 'alert') {
    $attributes['aria-live'] = 'assertive';
} elseif ($props['role'] === 'status') {
    $attributes['aria-live'] = 'polite';
}

if (!$props['visible']) {
    $attributes['aria-hidden'] = 'true';
    $attributes['style'] = 'display: none;';
}

if ($props['auto_dismiss'] > 0) {
    $attributes['data-auto-dismiss'] = $props['auto_dismiss'];
}

if ($props['persist']) {
    $attributes['data-persist'] = 'true';
    $attributes['data-persist-key'] = $props['persist_key'] ?: $props['id'];
}

foreach ($props['data'] as $key => $value) {
    $attributes['data-' . $key] = $value;
}

foreach ($props['attributes'] as $key => $value) {
    if (!isset($attributes[$key])) {
        $attributes[$key] = $value;
    }
}

?>

<div <?php hph_render_attributes($attributes); ?>>
    
    <?php if ($props['show_progress'] && $props['auto_dismiss'] > 0): ?>
    <div class="hph-alert__progress hph-alert__progress--<?php echo esc_attr($props['progress_position']); ?>">
        <div class="hph-alert__progress-bar" 
             style="animation-duration: <?php echo esc_attr($props['auto_dismiss']); ?>ms"></div>
    </div>
    <?php endif; ?>
    
    <div class="hph-alert__container">
        
        <?php if ($props['show_icon']): ?>
        <div class="hph-alert__icon<?php echo $props['icon_animate'] ? ' hph-alert__icon--animated' : ''; ?>">
            <span data-icon="<?php echo esc_attr($props['icon']); ?>"></span>
        </div>
        <?php endif; ?>
        
        <div class="hph-alert__content">
            <?php if ($props['title']): ?>
            <div class="hph-alert__title">
                <?php echo esc_html($props['title']); ?>
                
                <?php if ($props['collapsible']): ?>
                <button type="button" 
                        class="hph-alert__toggle" 
                        aria-expanded="<?php echo $props['collapsed'] ? 'false' : 'true'; ?>"
                        aria-controls="<?php echo esc_attr($props['id']); ?>-details">
                    <span data-icon="chevron-down"></span>
                </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($props['message']): ?>
            <div class="hph-alert__message">
                <?php echo wp_kses_post($props['message']); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($props['details']): ?>
            <div class="hph-alert__details" 
                 <?php if ($props['collapsible']): ?>
                 id="<?php echo esc_attr($props['id']); ?>-details"
                 <?php if ($props['collapsed']): ?>style="display: none;"<?php endif; ?>
                 <?php endif; ?>>
                <?php echo wp_kses_post($props['details']); ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($props['actions'])): ?>
            <div class="hph-alert__actions">
                <?php foreach ($props['actions'] as $action): ?>
                    <?php hph_component('base/button', wp_parse_args($action, array(
                        'size' => 'sm',
                        'variant' => $props['style'] === 'solid' ? 'light' : $props['variant']
                    ))); ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($props['dismissible']): ?>
        <button type="button" 
                class="hph-alert__dismiss" 
                aria-label="Dismiss alert"
                data-dismiss="alert">
            <span data-icon="x"></span>
        </button>
        <?php endif; ?>
        
    </div>
</div>