<?php
/**
 * Content Section Template
 * 
 * A flexible content template that supports single or multiple images
 * with various layout options including masonry and gallery layouts.
 * 
 * @package HappyPlaceTheme
 * @version 3.0.0
 */

// Extract arguments with defaults
$background = $args['background'] ?? 'white';
$padding = $args['padding'] ?? 'lg';
$alignment = $args['alignment'] ?? 'left';
$badge = $args['badge'] ?? '';
$headline = $args['headline'] ?? '';
$subheadline = $args['subheadline'] ?? '';
$content = $args['content'] ?? '';
$buttons = $args['buttons'] ?? [];

// Image handling - support both single image and multiple images
$image = $args['image'] ?? null;
$images = $args['images'] ?? [];

// If single image is provided, convert it to images array
if ($image && empty($images)) {
    $images = [$image];
}

// Image styling options
$image_style = $args['image_style'] ?? 'default';
$image_size = $args['image_size'] ?? 'medium';

// Layout options for multiple images
$layout = $args['layout'] ?? 'default'; // 'default', 'masonry', 'gallery'
$masonry_columns = $args['masonry_columns'] ?? 3;
$gallery_gap = $args['gallery_gap'] ?? 'md';

// Animation
$animation = $args['animation'] ?? false;

/**
 * Get image styles based on style and size parameters
 */
function getContentImageStyles($style, $size) {
    $styles = [];
    
    // Base styles
    $styles[] = 'width: 100%';
    $styles[] = 'height: auto';
    $styles[] = 'border-radius: var(--hph-radius-lg)';
    $styles[] = 'box-shadow: 0 10px 30px rgba(0,0,0,0.1)';
    
    // Size variations
    switch ($size) {
        case 'small':
            $styles[] = 'max-width: 300px';
            break;
        case 'medium':
            $styles[] = 'max-width: 500px';
            break;
        case 'large':
            $styles[] = 'max-width: 700px';
            break;
        case 'full':
            $styles[] = 'max-width: 100%';
            break;
    }
    
    // Style variations
    switch ($style) {
        case 'square':
            $styles[] = 'aspect-ratio: 1/1';
            $styles[] = 'object-fit: cover';
            break;
        case 'wide':
            $styles[] = 'aspect-ratio: 16/9';
            $styles[] = 'object-fit: cover';
            break;
        case 'circle':
            $styles[] = 'border-radius: 50%';
            $styles[] = 'aspect-ratio: 1/1';
            $styles[] = 'object-fit: cover';
            break;
        case 'clean':
            $styles[] = 'border-radius: 0';
            $styles[] = 'box-shadow: none';
            break;
        default: // 'default'
            // Keep default styles
            break;
    }
    
    return implode('; ', $styles);
}
?>

<section class="hph-content-section" style="background: var(--hph-<?php echo esc_attr($background); ?>); padding: var(--hph-padding-<?php echo esc_attr($padding); ?>) 0;">
    <div class="hph-container" style="max-width: var(--hph-max-width); margin: 0 auto; padding: 0 var(--hph-padding-base);">
        
        <?php if ($badge || $headline || $subheadline || $content): ?>
        <div style="text-align: <?php echo esc_attr($alignment); ?>; margin-bottom: var(--hph-space-8);">
            <?php if ($badge): ?>
            <span style="
                display: inline-block;
                background: var(--hph-primary);
                color: white;
                padding: var(--hph-space-1) var(--hph-space-2);
                border-radius: var(--hph-radius-full);
                font-size: var(--hph-text-sm);
                font-weight: 600;
                letter-spacing: 0.05em;
                text-transform: uppercase;
                margin-bottom: var(--hph-space-4);
            "><?php echo esc_html($badge); ?></span>
            <?php endif; ?>
            
            <?php if ($headline): ?>
            <h2 style="font-size: var(--hph-text-4xl); font-weight: 700; line-height: var(--hph-leading-tight); color: var(--hph-gray-900); margin-bottom: var(--hph-space-4);">
                <?php echo esc_html($headline); ?>
            </h2>
            <?php endif; ?>
            
            <?php if ($subheadline): ?>
            <p style="font-size: var(--hph-text-xl); color: var(--hph-gray-600); margin-bottom: var(--hph-space-6); font-weight: 500;">
                <?php echo esc_html($subheadline); ?>
            </p>
            <?php endif; ?>
            
            <?php if ($content): ?>
            <div style="font-size: var(--hph-text-base); line-height: var(--hph-leading-relaxed); max-width: 65ch; margin: 0 auto;">
                <?php echo wp_kses_post($content); ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Images Container -->
        <?php if (!empty($images)): ?>
        <div class="hph-content-container" style="<?php
            if ($layout === 'masonry') {
                echo 'column-count: ' . intval($masonry_columns) . '; column-gap: 2rem; margin-bottom: 2rem;';
            } elseif ($layout === 'gallery') {
                echo 'display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-bottom: 2rem;';
            } else {
                echo 'margin-bottom: 2rem;';
            }
        ?>">
            
            <?php foreach ($images as $index => $img): ?>
            <div style="<?php echo $layout === 'masonry' ? 'break-inside: avoid; margin-bottom: 2rem;' : 'margin-bottom: 2rem;'; ?> <?php echo $animation ? 'animation: fadeInUp 0.8s ease-out ' . ($index * 0.1) . 's; opacity: 0; animation-fill-mode: forwards;' : ''; ?>">
                <img 
                    src="<?php echo esc_url($img['url']); ?>" 
                    alt="<?php echo esc_attr($img['alt'] ?? ''); ?>"
                    style="<?php echo getContentImageStyles($image_style, $image_size); ?>; cursor: pointer; display: block;"
                    loading="lazy"
                    onclick="if(typeof openImageModal === 'function') openImageModal('<?php echo esc_js($img['url']); ?>', '<?php echo esc_js($img['alt'] ?? ''); ?>');"
                />
                <?php if (!empty($img['caption'])): ?>
                <p style="margin-top: 1rem; text-align: center; color: var(--hph-text-light); font-size: 0.9rem;">
                    <?php echo esc_html($img['caption']); ?>
                </p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            
        </div>
        <?php endif; ?>
        
        <!-- Buttons -->
        <?php if (!empty($buttons)): ?>
        <div style="text-align: <?php echo esc_attr($alignment); ?>; margin-top: var(--hph-space-8);">
            <?php foreach ($buttons as $button): ?>
            <a href="<?php echo esc_url($button['url'] ?? '#'); ?>" 
               style="
                   display: inline-flex;
                   align-items: center;
                   gap: 0.5rem;
                   padding: var(--hph-space-4) var(--hph-space-6);
                   margin: 0 var(--hph-space-2) var(--hph-space-2) 0;
                   background: <?php echo ($button['style'] ?? 'primary') === 'primary' ? 'var(--hph-primary)' : 'transparent'; ?>;
                   color: <?php echo ($button['style'] ?? 'primary') === 'primary' ? 'white' : 'var(--hph-primary)'; ?>;
                   border: 2px solid var(--hph-primary);
                   border-radius: var(--hph-radius-lg);
                   text-decoration: none;
                   font-weight: 600;
                   transition: all 0.3s ease;
               "
               onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 25px rgba(80,186,225,0.3)';"
               onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                <?php if (!empty($button['icon'])): ?>
                <i class="<?php echo esc_attr($button['icon']); ?>"></i>
                <?php endif; ?>
                <?php echo esc_html($button['text'] ?? 'Button'); ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
    </div>
</section>

<?php if ($layout === 'masonry' || $layout === 'gallery'): ?>
<style>
/* Masonry and Gallery Layouts */
@media (max-width: 768px) {
    .hph-content-container[style*="column-count"] {
        column-count: 1 !important;
    }
    
    .hph-content-container[style*="grid-template-columns"] {
        grid-template-columns: 1fr !important;
    }
}

@media (max-width: 1024px) and (min-width: 769px) {
    .hph-content-container[style*="column-count: 4"] {
        column-count: 2 !important;
    }
    
    .hph-content-container[style*="column-count: 5"] {
        column-count: 3 !important;
    }
}

/* Image hover effects for gallery/masonry */
.hph-content-container img {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 2px solid red !important; /* Debug border */
    min-height: 200px !important; /* Debug minimum height */
}

.hph-content-container img:hover {
    transform: scale(1.02);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15) !important;
}

/* Masonry specific styles */
.hph-content-container[style*="column-count"] > div {
    display: inline-block;
    width: 100%;
    border: 1px solid blue !important; /* Debug border for containers */
    margin-bottom: 2rem;
}
</style>
<?php endif; ?>

<?php if ($animation): ?>
<style>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
<?php endif; ?>
