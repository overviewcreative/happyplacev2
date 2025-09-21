<?php
/**
 * Base Card Component
 * 
 * Pure UI component with no data dependencies
 * Accepts standardized props and renders card markup
 * 
 * @package HappyPlaceTheme
 * @subpackage Components/Base
 * @since 3.0.0
 */

// Get component args - compatible with both component loader and get_template_part
$args = $args ?? get_query_var('args', array());

// Debug disabled for production

// Extract with defaults - pure UI props only
$props = wp_parse_args($args, array(
    // Display props
    'variant' => 'default', // default, elevated, bordered, minimal, overlay, property
    'layout' => 'vertical', // vertical, horizontal, compact
    'size' => 'md', // sm, md, lg, xl
    
    // Content props
    'image' => array(
        'src' => '',
        'alt' => '',
        'ratio' => 'landscape', // square, landscape, portrait, wide
        'position' => 'top' // top, left, right, background
    ),
    'title' => array(
        'text' => '',
        'tag' => 'h3',
        'link' => ''
    ),
    'subtitle' => '',
    'description' => '',
    'description_limit' => 150,
    
    // Meta props
    'badges' => array(), // Array of badge configs
    'meta_items' => array(), // Array of meta items
    'actions' => array(), // Array of button/link configs
    
    // Behavior props
    'link_wrapper' => '', // Makes entire card clickable
    'hover_effect' => 'lift', // none, lift, scale, shadow
    'animate' => false,
    'animation_delay' => 0,
    
    // HTML props
    'id' => '',
    'class' => '',
    'attributes' => array()
));

// Props parsed and ready for rendering

// Build card classes - matching CSS file naming
$card_classes = array(
    'hph-card',
    'hph-card-' . $props['variant'],
    'hph-card-' . $props['size']
);

// Add layout class
if ($props['layout'] !== 'vertical') {
    $card_classes[] = 'hph-card-' . $props['layout'];
}

// Layout class applied

// Add hover effect class
if ($props['hover_effect'] !== 'none') {
    $card_classes[] = 'hph-card-hover-' . $props['hover_effect'];
}

// Add animation class
if ($props['animate']) {
    $card_classes[] = 'hph-card-animate';
    $card_classes[] = 'hph-fade-in-up';
}

// Add image position class
if (($props['image']['position'] ?? 'top') !== 'top' && $props['image']['src']) {
    $card_classes[] = 'hph-card-image-' . $props['image']['position'];
}

// Add custom classes
if ($props['class']) {
    $card_classes[] = $props['class'];
}

// Build wrapper element
$wrapper_tag = $props['link_wrapper'] ? 'a' : 'article';
$wrapper_attrs = '';

// Build attributes string
$attrs_array = array();
$attrs_array[] = 'class="' . esc_attr(implode(' ', $card_classes)) . '"';

if ($props['id']) {
    $attrs_array[] = 'id="' . esc_attr($props['id']) . '"';
}

if ($props['link_wrapper']) {
    $attrs_array[] = 'href="' . esc_url($props['link_wrapper']) . '"';
}

if ($props['animate'] && $props['animation_delay']) {
    $attrs_array[] = 'style="--animation-delay: ' . (int)$props['animation_delay'] . 'ms;"';
}

foreach ($props['attributes'] as $key => $value) {
    $attrs_array[] = esc_attr($key) . '="' . esc_attr($value) . '"';
}

$wrapper_attrs = implode(' ', $attrs_array);

// Process description
$description = $props['description'];
if ($description && $props['description_limit'] > 0) {
    $description = wp_trim_words($description, $props['description_limit'], '...');
}

?>

<<?php echo $wrapper_tag; ?> <?php echo $wrapper_attrs; ?>>
    
    <?php if ($props['image']['src']): ?>
    <div class="hph-card-media hph-card-media-<?php echo esc_attr($props['image']['ratio']); ?>">
        <img 
            src="<?php echo esc_url($props['image']['src']); ?>" 
            alt="<?php echo esc_attr($props['image']['alt'] ?: $props['title']['text']); ?>"
            class="hph-card-image"
            loading="lazy"
        >
        
        <?php if (!empty($props['badges'])): ?>
        <div class="hph-card-badges">
            <?php foreach ($props['badges'] as $badge): ?>
                <span class="hph-card-badge hph-badge-<?php echo esc_attr($badge['variant'] ?? 'default'); ?>">
                    <?php echo esc_html($badge['text']); ?>
                </span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <div class="hph-card-body">
    <div class="hph-card-content">
        
        <?php if ($props['subtitle']): ?>
        <div class="hph-card-subtitle">
            <?php echo wp_kses_post($props['subtitle']); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($props['title']['text']): ?>
        <<?php echo $props['title']['tag']; ?> class="hph-card-title">
            <?php if (($props['title']['link'] ?? '') && !$props['link_wrapper']): ?>
                <a href="<?php echo esc_url($props['title']['link']); ?>">
                    <?php echo esc_html($props['title']['text']); ?>
                </a>
            <?php else: ?>
                <?php echo esc_html($props['title']['text']); ?>
            <?php endif; ?>
        </<?php echo $props['title']['tag']; ?>>
        <?php endif; ?>
        
        <?php if ($description): ?>
        <div class="hph-card-description">
            <?php echo wp_kses_post($description); ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($props['meta_items'])): ?>
        <div class="hph-card-meta">
            <?php foreach ($props['meta_items'] as $meta): ?>
            <div class="hph-card-meta-item <?php echo esc_attr($meta['class'] ?? ''); ?>">
                <?php if (!empty($meta['icon'])): ?>
                    <i class="hph-card-meta-icon fas fa-<?php echo esc_attr($meta['icon']); ?>"></i>
                <?php endif; ?>
                <?php if (!empty($meta['text'])): ?>
                    <span><?php echo esc_html($meta['text']); ?></span>
                <?php elseif (!empty($meta['value'])): ?>
                    <span><?php echo esc_html($meta['value']); ?></span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($props['price_per_sqft'])): ?>
        <div class="hph-card-price-per-sqft">
            <?php echo esc_html($props['price_per_sqft']); ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($props['actions'])): ?>
        <div class="hph-card-actions hph-card-buttons">
            <?php foreach ($props['actions'] as $action): ?>
                <?php
                // Build button classes
                $btn_classes = array('hph-btn', 'hph-card-btn');
                $btn_classes[] = 'hph-btn-' . ($action['variant'] ?? 'primary');
                $btn_classes[] = 'hph-btn-' . ($action['size'] ?? 'sm');
                ?>
                <?php if (!empty($action['href'])): ?>
                    <a href="<?php echo esc_url($action['href']); ?>" 
                       class="<?php echo esc_attr(implode(' ', $btn_classes)); ?>"
                       <?php if (!empty($action['target'])): ?>target="<?php echo esc_attr($action['target']); ?>"<?php endif; ?>>
                        <?php if (!empty($action['icon'])): ?>
                            <i class="fas fa-<?php echo esc_attr($action['icon']); ?> hph-mr-xs"></i>
                        <?php endif; ?>
                        <?php echo esc_html($action['text']); ?>
                    </a>
                <?php else: ?>
                    <button type="button" 
                            class="<?php echo esc_attr(implode(' ', $btn_classes)); ?>"
                            <?php if (!empty($action['data'])): ?>
                                <?php foreach ($action['data'] as $data_key => $data_value): ?>
                                    data-<?php echo esc_attr($data_key); ?>="<?php echo esc_attr($data_value); ?>"
                                <?php endforeach; ?>
                            <?php endif; ?>>
                        <?php if (!empty($action['icon'])): ?>
                            <i class="fas fa-<?php echo esc_attr($action['icon']); ?> hph-mr-xs"></i>
                        <?php endif; ?>
                        <?php echo esc_html($action['text']); ?>
                    </button>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
    </div>
    </div>
    
</<?php echo $wrapper_tag; ?>>
