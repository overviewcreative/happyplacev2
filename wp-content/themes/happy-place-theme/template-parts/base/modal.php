<?php
/**
 * Base Modal Component
 * 
 * Pure UI modal/dialog component with multiple variations
 * 
 * @package HappyPlaceTheme
 * @subpackage Components/Base
 * @since 3.0.0
 */

$props = wp_parse_args(get_query_var('args', array()), array(
    // Content
    'title' => '',
    'subtitle' => '',
    'content' => '', // Can be HTML string or component call
    'footer' => '', // Footer content/buttons
    
    // Appearance
    'variant' => 'default', // default, centered, fullscreen, drawer, notification
    'size' => 'md', // xs, sm, md, lg, xl, full
    'position' => 'center', // center, top, bottom, left, right (for drawer variant)
    'theme' => 'light', // light, dark, auto
    
    // Header
    'show_header' => true,
    'show_close' => true,
    'close_position' => 'header', // header, outside, both
    'header_actions' => '', // Additional header buttons/actions
    
    // Behavior
    'closable' => true, // Can be closed by user
    'close_on_backdrop' => true,
    'close_on_escape' => true,
    'backdrop' => true, // true, false, 'static'
    'backdrop_blur' => false,
    'keyboard' => true,
    'focus_trap' => true,
    'auto_focus' => true, // Auto-focus first input
    'restore_focus' => true, // Restore focus when closed
    
    // Animation
    'animation' => 'fade', // fade, slide, zoom, drawer, none
    'animation_duration' => 300,
    
    // State
    'open' => false,
    'loading' => false,
    'persistent' => false, // Stays in DOM when closed
    
    // Advanced
    'scrollable' => true, // Body scrolls if content overflows
    'padded' => true, // Adds padding to body
    'dividers' => false, // Shows dividers between sections
    'overlay_close_button' => false, // Floating close button
    
    // Events (JavaScript hook names)
    'on_open' => '',
    'on_close' => '',
    'on_confirm' => '',
    'on_cancel' => '',
    
    // HTML
    'id' => '',
    'class' => '',
    'attributes' => array(),
    'data' => array()
));

// Generate ID if not provided
if (!$props['id']) {
    $props['id'] = 'hph-modal-' . wp_generate_uuid4();
}

// Modal wrapper classes
$modal_classes = array(
    'hph-modal',
    'hph-modal--' . $props['variant'],
    'hph-modal--' . $props['size'],
    'hph-modal--' . $props['theme']
);

if ($props['position'] !== 'center' && $props['variant'] === 'drawer') {
    $modal_classes[] = 'hph-modal--' . $props['position'];
}

if ($props['animation'] !== 'none') {
    $modal_classes[] = 'hph-modal--animated';
    $modal_classes[] = 'hph-modal--' . $props['animation'];
}

if ($props['loading']) {
    $modal_classes[] = 'is-loading';
}

if ($props['open']) {
    $modal_classes[] = 'is-open';
}

if (!$props['padded']) {
    $modal_classes[] = 'hph-modal--no-padding';
}

if ($props['dividers']) {
    $modal_classes[] = 'hph-modal--dividers';
}

if ($props['class']) {
    $modal_classes[] = $props['class'];
}

// Build modal attributes
$modal_attrs = array(
    'id' => $props['id'],
    'class' => implode(' ', $modal_classes),
    'role' => 'dialog',
    'aria-modal' => 'true',
    'aria-labelledby' => $props['id'] . '-title',
    'tabindex' => '-1'
);

if (!$props['open']) {
    $modal_attrs['aria-hidden'] = 'true';
    $modal_attrs['style'] = 'display: none;';
}

if ($props['animation_duration']) {
    $modal_attrs['style'] = ($modal_attrs['style'] ?? '') . ' --animation-duration: ' . $props['animation_duration'] . 'ms;';
}

// Data attributes
$modal_attrs['data-modal'] = 'true';
if ($props['closable']) {
    $modal_attrs['data-closable'] = 'true';
}
if ($props['close_on_backdrop']) {
    $modal_attrs['data-close-on-backdrop'] = 'true';
}
if ($props['close_on_escape']) {
    $modal_attrs['data-close-on-escape'] = 'true';
}
if ($props['keyboard']) {
    $modal_attrs['data-keyboard'] = 'true';
}
if ($props['focus_trap']) {
    $modal_attrs['data-focus-trap'] = 'true';
}

foreach ($props['data'] as $key => $value) {
    $modal_attrs['data-' . $key] = $value;
}

// Add event handlers
if ($props['on_open']) {
    $modal_attrs['data-on-open'] = $props['on_open'];
}
if ($props['on_close']) {
    $modal_attrs['data-on-close'] = $props['on_close'];
}

// Add custom attributes
foreach ($props['attributes'] as $key => $value) {
    if (!isset($modal_attrs[$key])) {
        $modal_attrs[$key] = $value;
    }
}

?>

<div <?php hph_render_attributes($modal_attrs); ?>>
    
    <?php if ($props['backdrop'] !== false): ?>
    <div class="hph-modal__backdrop<?php echo $props['backdrop_blur'] ? ' hph-modal__backdrop--blur' : ''; ?>"
         <?php if ($props['backdrop'] === 'static'): ?>data-static="true"<?php endif; ?>>
    </div>
    <?php endif; ?>
    
    <?php if ($props['overlay_close_button'] && $props['closable']): ?>
    <button type="button" 
            class="hph-modal__close-overlay" 
            aria-label="Close"
            data-modal-close="true">
        <span data-icon="x"></span>
    </button>
    <?php endif; ?>
    
    <div class="hph-modal__container">
        <div class="hph-modal__dialog" role="document">
            
            <?php if ($props['show_close'] && $props['close_position'] === 'outside' && $props['closable']): ?>
            <button type="button" 
                    class="hph-modal__close hph-modal__close--outside" 
                    aria-label="Close"
                    data-modal-close="true">
                <span data-icon="x"></span>
            </button>
            <?php endif; ?>
            
            <div class="hph-modal__content">
                
                <?php if ($props['show_header']): ?>
                <div class="hph-modal__header">
                    <?php if ($props['show_close'] && in_array($props['close_position'], array('header', 'both')) && $props['closable']): ?>
                    <button type="button" 
                            class="hph-modal__close" 
                            aria-label="Close"
                            data-modal-close="true">
                        <span data-icon="x"></span>
                    </button>
                    <?php endif; ?>
                    
                    <?php if ($props['title']): ?>
                    <h2 class="hph-modal__title" id="<?php echo esc_attr($props['id']); ?>-title">
                        <?php echo esc_html($props['title']); ?>
                    </h2>
                    <?php endif; ?>
                    
                    <?php if ($props['subtitle']): ?>
                    <p class="hph-modal__subtitle">
                        <?php echo esc_html($props['subtitle']); ?>
                    </p>
                    <?php endif; ?>
                    
                    <?php if ($props['header_actions']): ?>
                    <div class="hph-modal__header-actions">
                        <?php echo $props['header_actions']; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <div class="hph-modal__body<?php echo $props['scrollable'] ? ' hph-modal__body--scrollable' : ''; ?>">
                    <?php if ($props['loading']): ?>
                    <div class="hph-modal__loader">
                        <span class="hph-spinner" data-size="lg"></span>
                    </div>
                    <?php else: ?>
                        <?php echo $props['content']; ?>
                    <?php endif; ?>
                </div>
                
                <?php if ($props['footer']): ?>
                <div class="hph-modal__footer">
                    <?php echo $props['footer']; ?>
                </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
    
</div>