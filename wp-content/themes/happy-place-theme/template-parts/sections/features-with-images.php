<?php
/**
 * HPH Features Section Template - Cards with Background Images
 * Each feature card has its own background image with overlay
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Register this template part for asset loading
if (function_exists('hph_register_template_part')) {
    hph_register_template_part('sections/features-cards-background');
}

// Default arguments
$defaults = array(
    'layout' => 'grid', // Options: 'grid', 'masonry', 'list'
    'section_background' => 'white', // Options: 'white', 'light', 'dark', 'gradient'
    'padding' => 'xl', // Options: 'sm', 'md', 'lg', 'xl', '2xl'
    'content_width' => 'normal', // Options: 'narrow', 'normal', 'wide', 'full'
    'columns' => 3, // Number of columns in grid layout (1-4)
    'card_width' => 'normal', // Options: 'narrow', 'normal', 'wide'
    'card_aspect_ratio' => 'auto', // Options: 'square', 'portrait', 'landscape', 'auto'
    'columns_mobile' => 1, // Number of columns on mobile (1-2)
    'columns_tablet' => 2, // Number of columns on tablet (1-3)
    'gap' => 'normal', // Options: 'tight', 'normal', 'loose'
    'card_height' => 'medium', // Options: 'small', 'medium', 'large', 'auto'
    'overlay_type' => 'gradient', // Options: 'gradient', 'solid', 'none'
    'overlay_color' => 'dark', // Options: 'primary', 'secondary', 'dark', 'light'
    'overlay_opacity' => 0.6, // Float value: 0.0-1.0
    'badge' => '', // Badge text to display above headline
    'headline' => 'Our Features', // Main headline text
    'subheadline' => '', // Subheadline text below headline
    'content' => '', // Additional content text
    'features' => array(), // Array of feature objects with title, content, image, etc.
    'animation' => false, // Boolean: true/false - enable entrance animations
    'section_id' => '', // HTML ID for the section
    'hover_effect' => 'zoom', // Options: 'zoom', 'lift', 'fade', 'none'
    'content_position' => 'center', // Options: 'top', 'center', 'bottom'
    'css_classes' => array() // Array of additional CSS classes
);

// Merge with provided args
$config = wp_parse_args($args ?? array(), $defaults);
extract($config);

// Generate unique section ID
$section_id = $section_id ?: 'hph-features-cards-' . uniqid();

// Build section CSS classes
$section_classes = array(
    'hph-features-section',
    'hph-features-cards-bg',
    'hph-bg-' . $section_background,
    'hph-padding-' . $padding,
    'hph-content-' . $content_width,
    'hph-cols-' . $columns,
    'hph-cols-mobile-' . $columns_mobile,
    'hph-cols-tablet-' . $columns_tablet,
    'hph-cards-' . $card_height,
    'hph-card-width-' . $card_width,
    'hph-card-ratio-' . $card_aspect_ratio,
    'hph-gap-' . $gap,
    'hph-hover-' . $hover_effect,
    'hph-content-pos-' . $content_position
);

if ($animation) {
    $section_classes[] = 'hph-animated';
}

// Add custom classes
if (!empty($css_classes)) {
    $section_classes = array_merge($section_classes, (array)$css_classes);
}

// Build data attributes
$data_attributes = array(
    'data-layout="' . esc_attr($layout) . '"',
    'data-overlay="' . esc_attr($overlay_type) . '"',
    'data-overlay-color="' . esc_attr($overlay_color) . '"',
    'data-animation="' . ($animation ? 'true' : 'false') . '"'
);

// Build CSS variables for the grid layout
$css_variables = array(
    '--columns: ' . intval($columns),
    '--tablet-columns: ' . intval($columns_tablet),
    '--mobile-columns: ' . intval($columns_mobile)
);

// Set gap variable
switch ($gap) {
    case 'tight':
        $css_variables[] = '--gap: 1rem';
        break;
    case 'loose':
        $css_variables[] = '--gap: 2rem';
        break;
    default:
        $css_variables[] = '--gap: 1.5rem';
        break;
}

$inline_styles = 'style="' . implode('; ', $css_variables) . '"';
?>

<section 
    id="<?php echo esc_attr($section_id); ?>"
    class="<?php echo esc_attr(implode(' ', $section_classes)); ?>"
    <?php echo implode(' ', $data_attributes); ?>
    <?php echo $inline_styles; ?>
>
    <div class="hph-container">
        
        <?php if ($badge || $headline || $subheadline || $content): ?>
        <!-- Section Header -->
        <div class="hph-section-header">
            
            <?php if ($badge): ?>
            <div class="hph-badge-wrapper">
                <span class="hph-badge">
                    <?php echo esc_html($badge); ?>
                </span>
            </div>
            <?php endif; ?>
            
            <?php if ($headline): ?>
            <h2 class="hph-section-headline">
                <?php echo esc_html($headline); ?>
            </h2>
            <?php endif; ?>
            
            <?php if ($subheadline): ?>
            <p class="hph-section-subheadline">
                <?php echo esc_html($subheadline); ?>
            </p>
            <?php endif; ?>
            
            <?php if ($content): ?>
            <div class="hph-section-content">
                <?php echo wp_kses_post($content); ?>
            </div>
            <?php endif; ?>
            
        </div>
        <?php endif; ?>
        
        <?php if (!empty($features)): ?>
        <!-- Features Grid -->
        <div class="hph-features-grid">
            
            <?php foreach ($features as $index => $feature):
                $feature_defaults = array(
                    'background_image' => null, // Background image for this card
                    'icon' => null, // Optional icon overlay
                    'title' => '',
                    'content' => '',
                    'button' => null,
                    'link' => null,
                    'custom_overlay_color' => '', // Override overlay for this card
                    'custom_overlay_opacity' => '' // Override opacity for this card
                );
                $feature = wp_parse_args($feature, $feature_defaults);
                
                $card_classes = array('hph-feature-card-bg');
                if ($animation) {
                    $card_classes[] = 'hph-animate-item';
                }
                
                // Card-specific overlay settings
                $card_overlay_color = $feature['custom_overlay_color'] ?: $overlay_color;
                $card_overlay_opacity = $feature['custom_overlay_opacity'] ?: $overlay_opacity;
                
                // Inline style for background image (only this needs to be inline)
                $card_style = '';
                if (!empty($feature['background_image'])) {
                    if (is_array($feature['background_image'])) {
                        $bg_url = $feature['background_image']['url'] ?? '';
                    } else {
                        $bg_url = $feature['background_image'];
                    }
                    if ($bg_url) {
                        $card_style = sprintf('background-image: url(%s);', esc_url($bg_url));
                    }
                }
            ?>
            
            <div class="<?php echo esc_attr(implode(' ', $card_classes)); ?>" 
                 data-index="<?php echo esc_attr($index); ?>"
                 data-card-overlay-color="<?php echo esc_attr($card_overlay_color); ?>"
                 data-card-overlay-opacity="<?php echo esc_attr($card_overlay_opacity); ?>"
                 <?php if ($card_style): ?>style="<?php echo esc_attr($card_style); ?>"<?php endif; ?>>
                
                <!-- Overlay Layer -->
                <div class="hph-card-overlay" aria-hidden="true"></div>
                
                <!-- Content Layer -->
                <div class="hph-card-content">
                    
                    <?php if (!empty($feature['icon'])): ?>
                    <!-- Icon -->
                    <div class="hph-card-icon">
                        <?php if (is_array($feature['icon']) && !empty($feature['icon']['url'])): ?>
                            <img src="<?php echo esc_url($feature['icon']['url']); ?>" 
                                 alt="<?php echo esc_attr($feature['icon']['alt'] ?? ''); ?>">
                        <?php else: ?>
                            <i class="<?php echo esc_attr($feature['icon']); ?>"></i>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($feature['title'])): ?>
                    <h3 class="hph-card-title">
                        <?php echo esc_html($feature['title']); ?>
                    </h3>
                    <?php endif; ?>
                    
                    <?php if (!empty($feature['content'])): ?>
                    <p class="hph-card-description">
                        <?php echo wp_kses_post($feature['content']); ?>
                    </p>
                    <?php endif; ?>
                    
                    <?php if (!empty($feature['button']) || !empty($feature['link'])): ?>
                    <div class="hph-card-actions">
                        
                        <?php if (!empty($feature['button'])): ?>
                        <?php
                        $button = wp_parse_args($feature['button'], array(
                            'text' => 'Learn More',
                            'url' => '#',
                            'style' => 'white-outline',
                            'size' => 'md',
                            'target' => '_self'
                        ));
                        
                        $button_classes = array(
                            'hph-btn',
                            'hph-btn-' . $button['style'],
                            'hph-btn-' . $button['size']
                        );
                        ?>
                        <a href="<?php echo esc_url($button['url']); ?>"
                           class="<?php echo esc_attr(implode(' ', $button_classes)); ?>"
                           <?php if ($button['target'] !== '_self'): ?>target="<?php echo esc_attr($button['target']); ?>"<?php endif; ?>>
                            <?php echo esc_html($button['text']); ?>
                        </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($feature['link']) && !empty($feature['link']['url'])): ?>
                        <a href="<?php echo esc_url($feature['link']['url']); ?>" 
                           class="hph-card-link"
                           <?php if (!empty($feature['link']['target'])): ?>target="<?php echo esc_attr($feature['link']['target']); ?>"<?php endif; ?>>
                            <?php echo esc_html($feature['link']['text'] ?? 'Learn More'); ?>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <?php endif; ?>
                        
                    </div>
                    <?php endif; ?>
                    
                </div>
                
            </div>
            
            <?php endforeach; ?>
            
        </div>
        <?php endif; ?>
        
    </div>
</section>

<style>
/* Critical inline styles for overlay colors */
<?php foreach ($features as $index => $feature): 
    if (!empty($feature['custom_overlay_color']) || !empty($feature['custom_overlay_opacity'])): 
        $card_overlay_color = $feature['custom_overlay_color'] ?: $overlay_color;
        $card_overlay_opacity = $feature['custom_overlay_opacity'] ?: $overlay_opacity;
?>
#<?php echo esc_attr($section_id); ?> .hph-feature-card-bg[data-index="<?php echo $index; ?>"] .hph-card-overlay {
    <?php if ($overlay_type === 'gradient'): ?>
        <?php if ($card_overlay_color === 'primary'): ?>
        background: linear-gradient(135deg, rgba(14, 89, 174, <?php echo $card_overlay_opacity; ?>) 0%, rgba(8, 54, 105, <?php echo $card_overlay_opacity; ?>) 100%);
        <?php elseif ($card_overlay_color === 'dark'): ?>
        background: linear-gradient(135deg, rgba(0, 0, 0, <?php echo $card_overlay_opacity; ?>) 0%, rgba(30, 30, 30, <?php echo $card_overlay_opacity; ?>) 100%);
        <?php endif; ?>
    <?php else: ?>
        background-color: rgba(0, 0, 0, <?php echo $card_overlay_opacity; ?>);
    <?php endif; ?>
}
<?php endif; endforeach; ?>
</style>