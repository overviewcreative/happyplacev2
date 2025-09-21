<?php
/**
 * Content Section Component
 * 
 * Flexible content section for various page layouts
 * 
 * @block-config
 * {
 *   "title": "Content Section",
 *   "category": "hph-sections",
 *   "icon": "align-left",
 *   "supports": ["gutenberg"],
 *   "attributes": {
 *     "layout": {
 *       "type": "string",
 *       "control": "select",
 *       "label": "Layout",
 *       "options": [
 *         {"label": "Default", "value": "default"},
 *         {"label": "Two Column", "value": "two-column"}
 *       ],
 *       "default": "default"
 *     }
 *   }
 * }
 */

$props = wp_parse_args($args ?? array(), array(
    // Content
    'eyebrow' => '', // Small text above title
    'title' => '',
    'subtitle' => '',
    'content' => '', // Main content/description
    'buttons' => array(),
    
    // Layout
    'layout' => 'default', // default, centered, two-column, three-column, alternating, cards
    'alignment' => 'left', // left, center, right
    'container_width' => 'default', // narrow, default, wide, full
    'spacing' => 'default', // none, compact, default, spacious, custom
    'spacing_top' => '',
    'spacing_bottom' => '',
    
    // Media/Visual elements
    'media' => array(
        'type' => '', // image, video, icon, illustration
        'src' => '',
        'alt' => '',
        'position' => 'top', // top, bottom, left, right, background
        'size' => 'default', // sm, default, lg, full
        'aspect_ratio' => 'landscape'
    ),
    
    // Background
    'background' => array(
        'color' => '',
        'gradient' => false,
        'pattern' => '', // dots, lines, waves, mesh
        'image' => '',
        'overlay' => false
    ),
    
    // Items (for multi-item layouts)
    'items' => array(), // Array of content items
    'columns' => 3, // Number of columns for items
    'item_style' => 'default', // default, card, minimal, feature
    
    // Animation
    'animate' => false,
    'animation_type' => 'fade-up', // fade, fade-up, slide, zoom
    'stagger_items' => false,
    
    // Theme
    'theme' => 'light', // light, dark, inherit
    'inverted' => false, // Invert text colors
    
    // HTML
    'id' => '',
    'class' => '',
    'attributes' => array(),
    'data' => array()
));

// Build section classes
$section_classes = array(
    'hph-content-section',
    'hph-content-section--' . $props['layout'],
    'hph-content-section--align-' . $props['alignment'],
    'hph-content-section--' . $props['theme']
);

if ($props['spacing'] !== 'custom') {
    $section_classes[] = 'hph-content-section--spacing-' . $props['spacing'];
}

if ($props['inverted']) {
    $section_classes[] = 'hph-content-section--inverted';
}

if ($props['animate']) {
    $section_classes[] = 'hph-content-section--animated';
}

if ($props['background']['pattern']) {
    $section_classes[] = 'hph-content-section--pattern-' . $props['background']['pattern'];
}

if ($props['class']) {
    $section_classes[] = $props['class'];
}

// Build section styles
$section_styles = array();

if ($props['background']['color']) {
    $section_styles[] = 'background-color: ' . $props['background']['color'];
}

if ($props['background']['image']) {
    $section_styles[] = 'background-image: url(' . esc_url($props['background']['image']) . ')';
    $section_styles[] = 'background-size: cover';
    $section_styles[] = 'background-position: center';
}

if ($props['spacing'] === 'custom') {
    if ($props['spacing_top']) {
        $section_styles[] = 'padding-top: ' . $props['spacing_top'];
    }
    if ($props['spacing_bottom']) {
        $section_styles[] = 'padding-bottom: ' . $props['spacing_bottom'];
    }
}

// Container width class
$container_class = 'hph-container';
if ($props['container_width'] !== 'default') {
    $container_class .= ' hph-container--' . $props['container_width'];
}

?>

<section class="<?php echo esc_attr(implode(' ', $section_classes)); ?>"
         <?php if ($props['id']): ?>id="<?php echo esc_attr($props['id']); ?>"<?php endif; ?>
         <?php if (!empty($section_styles)): ?>style="<?php echo esc_attr(implode('; ', $section_styles)); ?>"<?php endif; ?>
         <?php foreach ($props['data'] as $key => $value): ?>
         data-<?php echo esc_attr($key); ?>="<?php echo esc_attr($value); ?>"
         <?php endforeach; ?>
         <?php foreach ($props['attributes'] as $key => $value): ?>
         <?php echo esc_attr($key); ?>="<?php echo esc_attr($value); ?>"
         <?php endforeach; ?>>
    
    <?php if ($props['background']['overlay']): ?>
    <div class="hph-content-section__overlay"></div>
    <?php endif; ?>
    
    <div class="<?php 
        if ($props['container_width'] === 'narrow') echo 'hph-container hph-container-sm';      // 640px - narrow content
        elseif ($props['container_width'] === 'wide') echo 'hph-container hph-container-2xl';   // 1536px - wide content 
        elseif ($props['container_width'] === 'full') echo 'hph-container hph-container--full';
        else echo 'hph-container';  // 1440px - default content width (no modifier needed)
    ?>">
        
        <?php if ($props['layout'] === 'two-column'): ?>
        <!-- Two Column Layout -->
        <div class="hph-content-section__grid hph-content-section__grid--two-column">
            <div class="hph-content-section__column hph-content-section__column--content">
                <?php hph_render_section_header($props); ?>
                <?php hph_render_section_content($props); ?>
            </div>
            <div class="hph-content-section__column hph-content-section__column--media">
                <?php hph_render_section_media($props['media']); ?>
            </div>
        </div>
        
        <?php elseif ($props['layout'] === 'three-column'): ?>
        <!-- Three Column Layout -->
        <div class="hph-content-section__header">
            <?php hph_render_section_header($props); ?>
        </div>
        <div class="hph-content-section__grid hph-content-section__grid--three-column">
            <?php foreach (array_slice($props['items'], 0, 3) as $index => $item): ?>
            <div class="hph-content-section__column"
                 <?php if ($props['animate'] && $props['stagger_items']): ?>
                 style="animation-delay: <?php echo $index * 100; ?>ms"
                 <?php endif; ?>>
                <?php hph_render_content_item($item, $props['item_style']); ?>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php elseif ($props['layout'] === 'alternating'): ?>
        <!-- Alternating Layout -->
        <?php foreach ($props['items'] as $index => $item): 
            $is_reversed = $index % 2 !== 0;
        ?>
        <div class="hph-content-section__row hph-content-section__row--alternating <?php echo $is_reversed ? 'hph-content-section__row--reversed' : ''; ?>">
            <div class="hph-content-section__column hph-content-section__column--content">
                <?php hph_render_content_item($item, 'expanded'); ?>
            </div>
            <div class="hph-content-section__column hph-content-section__column--media">
                <?php if (isset($item['media'])): ?>
                    <?php hph_render_section_media($item['media']); ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php elseif ($props['layout'] === 'cards'): ?>
        <!-- Cards Layout -->
        <div class="hph-content-section__header">
            <?php hph_render_section_header($props); ?>
        </div>
        <div class="hph-content-section__grid hph-content-section__grid--cards hph-content-section__grid--cols-<?php echo esc_attr($props['columns']); ?>">
            <?php foreach ($props['items'] as $index => $item): ?>
            <div class="hph-content-section__card"
                 <?php if ($props['animate'] && $props['stagger_items']): ?>
                 style="animation-delay: <?php echo $index * 50; ?>ms"
                 <?php endif; ?>>
                <?php 
                // Use base card component
                hph_component('base/card', array(
                    'variant' => 'elevated',
                    'title' => array('text' => $item['title'] ?? ''),
                    'description' => $item['content'] ?? '',
                    'image' => array(
                        'src' => $item['image'] ?? '',
                        'alt' => $item['title'] ?? ''
                    ),
                    'link_wrapper' => $item['link'] ?? ''
                ));
                ?>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php elseif ($props['layout'] === 'centered'): ?>
        <!-- Centered Layout -->
        <div class="hph-content-section__centered">
            <?php if ($props['media']['position'] === 'top'): ?>
                <?php hph_render_section_media($props['media']); ?>
            <?php endif; ?>
            
            <?php hph_render_section_header($props); ?>
            <?php hph_render_section_content($props); ?>
            
            <?php if ($props['media']['position'] === 'bottom'): ?>
                <?php hph_render_section_media($props['media']); ?>
            <?php endif; ?>
        </div>
        
        <?php else: ?>
        <!-- Default Layout -->
        <div class="hph-content-section__default">
            <?php hph_render_section_header($props); ?>
            <?php hph_render_section_content($props); ?>
            
            <?php if (!empty($props['items'])): ?>
            <div class="hph-content-section__items">
                <?php foreach ($props['items'] as $item): ?>
                    <?php hph_render_content_item($item, $props['item_style']); ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
    </div>
</section>

<?php
// Helper functions
function hph_render_section_header($props) {
    if (!$props['eyebrow'] && !$props['title'] && !$props['subtitle']) return;
    ?>
    <header class="hph-content-section__header">
        <?php if ($props['eyebrow']): ?>
        <div class="hph-content-section__eyebrow">
            <?php echo esc_html($props['eyebrow']); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($props['title']): ?>
        <h2 class="hph-content-section__title">
            <?php echo esc_html($props['title']); ?>
        </h2>
        <?php endif; ?>
        
        <?php if ($props['subtitle']): ?>
        <p class="hph-content-section__subtitle">
            <?php echo esc_html($props['subtitle']); ?>
        </p>
        <?php endif; ?>
    </header>
    <?php
}

function hph_render_section_content($props) {
    ?>
    <?php if ($props['content']): ?>
    <div class="hph-content-section__content">
        <?php echo wp_kses_post($props['content']); ?>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($props['buttons'])): ?>
    <div class="hph-content-section__actions">
        <?php foreach ($props['buttons'] as $button): ?>
            <?php hph_component('base/button', $button); ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php
}

function hph_render_section_media($media) {
    if (!$media['src']) return;
    
    $media_classes = array(
        'hph-content-section__media',
        'hph-content-section__media--' . $media['type'],
        'hph-content-section__media--' . $media['size']
    );
    ?>
    <div class="<?php echo esc_attr(implode(' ', $media_classes)); ?>">
        <?php if ($media['type'] === 'image'): ?>
            <?php hph_component('base/image', array(
                'src' => $media['src'],
                'alt' => $media['alt'],
                'aspect_ratio' => $media['aspect_ratio'] ?? 'landscape'
            )); ?>
        <?php elseif ($media['type'] === 'video'): ?>
            <video src="<?php echo esc_url($media['src']); ?>" controls></video>
        <?php elseif ($media['type'] === 'icon'): ?>
            <div class="hph-content-section__icon">
                <span data-icon="<?php echo esc_attr($media['src']); ?>"></span>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

function hph_render_content_item($item, $style = 'default') {
    $item_classes = array(
        'hph-content-item',
        'hph-content-item--' . $style
    );
    ?>
    <div class="<?php echo esc_attr(implode(' ', $item_classes)); ?>">
        <?php if (isset($item['icon'])): ?>
        <div class="hph-content-item__icon">
            <span data-icon="<?php echo esc_attr($item['icon']); ?>"></span>
        </div>
        <?php endif; ?>
        
        <?php if (isset($item['title'])): ?>
        <h3 class="hph-content-item__title">
            <?php echo esc_html($item['title']); ?>
        </h3>
        <?php endif; ?>
        
        <?php if (isset($item['content'])): ?>
        <div class="hph-content-item__content">
            <?php echo wp_kses_post($item['content']); ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($item['link'])): ?>
        <a href="<?php echo esc_url($item['link']); ?>" class="hph-content-item__link">
            <?php echo esc_html($item['link_text'] ?? 'Learn More'); ?>
            <span data-icon="arrow-right"></span>
        </a>
        <?php endif; ?>
    </div>
    <?php
}
