<?php
/**
 * HPH Content Section Template
 * 
 * A flexible content section using proper HPH utilities and variables:
 * - Photo (optional)
 * - Headlines (h1, h2, h3)
 * - Text content
 * - Buttons/CTAs
 * 
 * Configurable variations:
 * - Layout: left-image, right-image, centered, full-width
 * - Background: light, dark, primary, white, gradient
 * - Padding: xs, sm, md, lg, xl, 2xl
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 * 
 * LAYOUT OPTIONS:
 * - layout: 'left-image' | 'right-image' | 'centered' | 'full-width' | 'two-column' | 'three-column' | 'four-column' | 'grid-cards' | 'icon-features' | 'stats-row' | 'accordion-list'
 * 
 * BACKGROUND OPTIONS:
 * - background: 'light' | 'dark' | 'primary' | 'white' | 'gradient' | 'secondary' | 'pattern' | 'video-bg' | 'parallax-image' | 'geometric'
 * - background_image: string (URL for image backgrounds)
 * - background_video: string (URL for video backgrounds)
 * - background_pattern: 'dots' | 'grid' | 'waves' | 'geometric' | 'diagonal'
 * 
 * SPACING & SIZING:
 * - padding: 'xs' | 'sm' | 'md' | 'lg' | 'xl' | '2xl'
 * - content_width: 'narrow' | 'normal' | 'wide' | 'full'
 * - vertical_align: 'top' | 'center' | 'bottom'
 * 
 * CONTENT ELEMENTS:
 * - image: array with 'url', 'alt', 'width', 'height'
 * - badge: string (optional)
 * - badge_style: 'default' | 'outline' | 'filled' | 'gradient'
 * - headline: string
 * - headline_tag: 'h1' | 'h2' | 'h3' | 'h4' | 'h5' | 'h6'
 * - subheadline: string (optional)
 * - content: string
 * - buttons: array of button configurations
 * - section_id: string (optional)
 * 
 * ENHANCED CONTENT TYPES:
 * - content_type: 'default' | 'features-grid' | 'stats-counter' | 'testimonial-slider' | 'faq-accordion' | 'icon-grid' | 'price-table' | 'team-grid' | 'gallery' | 'virtual-tour' | 'floor-plans' | 'location-map' | 'agent-profile' | 'property-grid'
 * 
 * ANIMATION & EFFECTS:
 * - animation: 'none' | 'fade-in' | 'slide-up' | 'slide-in-left' | 'slide-in-right' | 'zoom-in' | 'stagger'
 * - scroll_reveal: boolean (reveals content on scroll)
 * - parallax_content: boolean (parallax effect on content)
 * 
 * GRID & CARD OPTIONS (for grid layouts):
 * - columns: 2 | 3 | 4 | 6 (for grid layouts)
 * - card_style: 'default' | 'hover-lift' | 'border' | 'shadow' | 'minimal'
 * - items: array of items for grid layouts
 * 
 * STATS OPTIONS (for stats-counter content type):
 * - stats: array of stat objects with 'number', 'label', 'icon', 'suffix'
 * - counter_animation: boolean
 * 
 * FAQ OPTIONS (for faq-accordion content type):
 * - faqs: array of FAQ objects with 'question', 'answer', 'category'
 * - allow_multiple_open: boolean
 * - search_enabled: boolean
 * 
 * Property-specific args:
 * - listing_id: int (for property content)
 * - sidebar_content: string (for two-column layout)
 * - show_description: boolean
 * - show_details: boolean
 * - show_features: boolean
 */

// Default arguments
$defaults = array(
    // Layout & Structure
    'layout' => 'centered',
    'columns' => 3,
    'content_width' => 'normal',
    'vertical_align' => 'center',
    
    // Background & Visual
    'background' => 'white',
    'background_image' => '',
    'background_video' => '',
    'background_pattern' => '',
    'padding' => 'xl',
    
    // Content Elements
    'image' => null,
    'badge' => '',
    'badge_style' => 'default',
    'headline' => 'Content Section',
    'headline_tag' => 'h2',
    'subheadline' => '',
    'content' => '',
    'buttons' => array(),
    'section_id' => '',
    
    // Enhanced Content Types
    'content_type' => 'default',
    'items' => array(),
    'stats' => array(),
    'faqs' => array(),
    'card_style' => 'default',
    
    // Animation & Effects
    'animation' => 'none',
    'scroll_reveal' => false,
    'parallax_content' => false,
    'counter_animation' => true,
    
    // FAQ Options
    'allow_multiple_open' => false,
    'search_enabled' => false,
    
    // Property-specific defaults
    'listing_id' => 0,
    'sidebar_content' => '',
    'show_description' => false,
    'show_details' => false,
    'show_features' => false
);

// Merge with provided args
$config = wp_parse_args($args ?? array(), $defaults);

// Extract configuration
// Layout & Structure
$layout = $config['layout'];
$columns = $config['columns'];
$content_width = $config['content_width'];
$vertical_align = $config['vertical_align'];

// Background & Visual
$background = $config['background'];
$background_image = $config['background_image'];
$background_video = $config['background_video'];
$background_pattern = $config['background_pattern'];
$padding = $config['padding'];

// Content Elements
$image = $config['image'];
$badge = $config['badge'];
$badge_style = $config['badge_style'];
$headline = $config['headline'];
$headline_tag = $config['headline_tag'];
$subheadline = $config['subheadline'];
$content = $config['content'];
$buttons = $config['buttons'];
$section_id = $config['section_id'];

// Enhanced Content Types
$content_type = $config['content_type'];
$items = $config['items'];
$stats = $config['stats'];
$faqs = $config['faqs'];
$card_style = $config['card_style'];

// Animation & Effects
$animation = $config['animation'];
$scroll_reveal = $config['scroll_reveal'];
$parallax_content = $config['parallax_content'];
$counter_animation = $config['counter_animation'];

// FAQ Options
$allow_multiple_open = $config['allow_multiple_open'];
$search_enabled = $config['search_enabled'];

// Property-specific configuration
$listing_id = $config['listing_id'];
$sidebar_content = $config['sidebar_content'];
$show_description = $config['show_description'];
$show_details = $config['show_details'];
$show_features = $config['show_features'];

// Build section classes using HPH utilities
$section_classes = array(
    'hph-section',
    'hph-w-full',
    'hph-content-section'
);

// Add layout-specific classes
$section_classes[] = 'hph-layout-' . str_replace('_', '-', $layout);

// Add content type classes
if ($content_type !== 'default') {
    $section_classes[] = 'hph-content-type-' . str_replace('_', '-', $content_type);
}

// Add animation classes
if ($animation !== 'none') {
    $section_classes[] = 'hph-animate-' . str_replace('_', '-', $animation);
}

if ($scroll_reveal) {
    $section_classes[] = 'hph-scroll-reveal';
}

if ($parallax_content) {
    $section_classes[] = 'hph-parallax-content';
}

// Add content width class
$section_classes[] = 'hph-content-width-' . $content_width;

// Add vertical alignment class
$section_classes[] = 'hph-vertical-align-' . $vertical_align;

// Add padding based on design system
switch ($padding) {
    case 'xs':
        $section_classes[] = 'hph-py-2xl';  // Still generous even for "xs"
        break;
    case 'sm':
        $section_classes[] = 'hph-py-3xl';
        break;
    case 'md':
        $section_classes[] = 'hph-py-4xl';
        break;
    case 'lg':
        $section_classes[] = 'hph-py-5xl';
        break;
    case 'xl':
        $section_classes[] = 'hph-section-spacing';
        break;
    case '2xl':
        $section_classes[] = 'hph-section-spacing-xl';
        break;
    default:
        $section_classes[] = 'hph-section-spacing';
        break;
}

// Add background classes
switch ($background) {
    case 'dark':
        $section_classes[] = 'hph-section-dark';
        break;
    case 'primary':
        $section_classes[] = 'hph-section-primary';
        break;
    case 'secondary':
        $section_classes[] = 'hph-section-secondary';
        break;
    case 'gradient':
        $section_classes[] = 'hph-section-gradient';
        break;
    case 'pattern':
        $section_classes[] = 'hph-section-pattern';
        if ($background_pattern) {
            $section_classes[] = 'hph-pattern-' . $background_pattern;
        }
        break;
    case 'video-bg':
        $section_classes[] = 'hph-section-video';
        break;
    case 'parallax-image':
        $section_classes[] = 'hph-section-parallax';
        break;
    case 'geometric':
        $section_classes[] = 'hph-section-geometric';
        break;
    case 'light':
        $section_classes[] = 'hph-section-light';
        break;
    case 'image':
        $section_classes[] = 'hph-section-image';
        $section_classes[] = 'hph-section-light';
        break;
    case 'image':
        $section_classes[] = 'hph-section-image';
        break;
    case 'white':
    default:
        $section_classes[] = 'hph-section-white';
        break;
}

// Build container classes using HPH utilities
$container_classes = array(
    'hph-w-full',
    'hph-mx-auto'
);

// Add content width classes
switch ($content_width) {
    case 'narrow':
        $container_classes[] = 'hph-max-w-4xl';
        break;
    case 'wide':
        $container_classes[] = 'hph-max-w-7xl';
        break;
    case 'full':
        $container_classes[] = 'hph-max-w-none';
        break;
    case 'normal':
    default:
        $container_classes[] = 'hph-magazine-container';
        break;
}

// Build content grid classes based on layout using HPH utilities
$grid_classes = array('hph-grid');
$content_classes = array();
$image_classes = array();

// Add vertical alignment
switch ($vertical_align) {
    case 'top':
        $grid_classes[] = 'hph-items-start';
        break;
    case 'bottom':
        $grid_classes[] = 'hph-items-end';
        break;
    case 'center':
    default:
        $grid_classes[] = 'hph-items-center';
        break;
}

switch ($layout) {
    case 'left-image':
        $grid_classes[] = 'hph-lg:grid-cols-2';
        $grid_classes[] = 'hph-gap-xl';
        $content_classes[] = 'hph-lg:order-2';
        $image_classes[] = 'hph-lg:order-1';
        break;
    case 'right-image':
        $grid_classes[] = 'hph-lg:grid-cols-2';
        $grid_classes[] = 'hph-gap-xl';
        $content_classes[] = 'hph-lg:order-1';
        $image_classes[] = 'hph-lg:order-2';
        break;
    case 'two-column':
        $grid_classes[] = 'hph-lg:grid-cols-3';
        $grid_classes[] = 'hph-gap-xl';
        $content_classes[] = 'hph-lg:col-span-2';
        break;
    case 'three-column':
        $grid_classes[] = 'hph-lg:grid-cols-3';
        $grid_classes[] = 'hph-gap-lg';
        break;
    case 'four-column':
        $grid_classes[] = 'hph-lg:grid-cols-4';
        $grid_classes[] = 'hph-gap-lg';
        break;
    case 'grid-cards':
        $grid_classes[] = 'hph-grid-cols-1';
        $grid_classes[] = 'hph-md:grid-cols-2';
        $grid_classes[] = 'hph-lg:grid-cols-' . $columns;
        $grid_classes[] = 'hph-gap-lg';
        break;
    case 'icon-features':
        $grid_classes[] = 'hph-grid-cols-1';
        $grid_classes[] = 'hph-md:grid-cols-2';
        $grid_classes[] = 'hph-lg:grid-cols-' . min($columns, 4);
        $grid_classes[] = 'hph-gap-xl';
        break;
    case 'stats-row':
        $grid_classes[] = 'hph-grid-cols-2';
        $grid_classes[] = 'hph-lg:grid-cols-' . min(count($stats), 4);
        $grid_classes[] = 'hph-gap-lg';
        break;
    case 'accordion-list':
        $grid_classes[] = 'hph-grid-cols-1';
        $grid_classes[] = 'hph-gap-md';
        break;
    case 'full-width':
        $grid_classes[] = 'hph-grid-cols-1';
        break;
    case 'centered':
    default:
        $grid_classes[] = 'hph-grid-cols-1';
        break;
}

// Set default image if none provided
if (!$image && ($layout === 'left-image' || $layout === 'right-image')) {
    $image = array(
        'url' => get_template_directory_uri() . '/assets/images/placeholder-property.jpg',
        'alt' => 'Beautiful property',
        'width' => 600,
        'height' => 400
    );
}

// Build inline styles for background
$inline_styles = array();
if ($background === 'image' && $background_image) {
    $inline_styles[] = "background-image: url('" . esc_url($background_image) . "')";
    $inline_styles[] = "background-size: cover";
    $inline_styles[] = "background-position: center";
    $inline_styles[] = "background-repeat: no-repeat";
}
if ($background === 'video-bg' && $background_video) {
    $inline_styles[] = "position: relative";
}
$style_attr = !empty($inline_styles) ? 'style="' . implode('; ', $inline_styles) . '"' : '';
?>

<section 
    class="<?php echo esc_attr(implode(' ', $section_classes)); ?>"
    <?php if ($section_id): ?>id="<?php echo esc_attr($section_id); ?>"<?php endif; ?>
    <?php echo $style_attr; ?>
    <?php if ($scroll_reveal): ?>data-scroll-reveal="true"<?php endif; ?>
    <?php if ($animation !== 'none'): ?>data-animation="<?php echo esc_attr($animation); ?>"<?php endif; ?>
>
    
    <?php if ($background === 'video-bg' && $background_video): ?>
    <!-- Video Background -->
    <div class="hph-video-background">
        <video autoplay muted loop playsinline class="hph-video-bg">
            <source src="<?php echo esc_url($background_video); ?>" type="video/mp4">
        </video>
        <div class="hph-video-overlay"></div>
    </div>
    <?php endif; ?>
    
    <div class="<?php echo esc_attr(implode(' ', $container_classes)); ?>">
        
        <?php
        // Render different content types
        switch ($content_type) {
            case 'stats-counter':
                get_template_part('template-parts/content-types/stats-counter', null, compact('stats', 'counter_animation', 'grid_classes'));
                break;
            
            case 'faq-accordion':
                get_template_part('template-parts/content-types/faq-accordion', null, compact('faqs', 'allow_multiple_open', 'search_enabled', 'headline', 'subheadline', 'content'));
                break;
            
            case 'features-grid':
                get_template_part('template-parts/content-types/features-grid', null, compact('items', 'columns', 'card_style', 'grid_classes'));
                break;
            
            case 'icon-grid':
                get_template_part('template-parts/content-types/icon-grid', null, compact('items', 'columns', 'grid_classes'));
                break;
                
            case 'team-grid':
                get_template_part('template-parts/content-types/team-grid', null, compact('items', 'columns', 'card_style', 'grid_classes'));
                break;
            
            default:
                // Render standard content layout
                ?>
                <div class="<?php echo esc_attr(implode(' ', $grid_classes)); ?>">
                <?php
                break;
        }
        
        // Only render default content structure for default content type
        if ($content_type === 'default'):
        ?>
            
            <?php if ($content_type === 'default' && $image && ($layout === 'left-image' || $layout === 'right-image')): ?>
            <!-- Image Column -->
            <div class="<?php echo esc_attr(implode(' ', $image_classes)); ?>">
                <div class="hph-relative hph-overflow-hidden hph-rounded-lg hph-shadow-lg hph-transition-transform">
                    <img 
                        src="<?php echo esc_url($image['url']); ?>"
                        alt="<?php echo esc_attr($image['alt'] ?? ''); ?>"
                        class="hph-w-full hph-h-auto hph-object-cover hph-transition-transform hph-property-image"
                        <?php if (isset($image['width'])): ?>width="<?php echo esc_attr($image['width']); ?>"<?php endif; ?>
                        <?php if (isset($image['height'])): ?>height="<?php echo esc_attr($image['height']); ?>"<?php endif; ?>
                        loading="lazy"
                    >
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Content Column -->
            <div class="<?php echo esc_attr(implode(' ', $content_classes)); ?> <?php echo ($layout === 'centered' || $layout === 'full-width') ? 'hph-text-center' : 'hph-text-left'; ?>">
                
                <?php if ($badge): ?>
                <!-- Badge -->
                <div class="hph-mb-lg">
                    <span class="hph-badge hph-badge-<?php echo esc_attr($badge_style); ?> hph-inline-flex hph-items-center hph-px-md hph-py-sm hph-rounded-full hph-text-sm hph-font-medium hph-transition-colors">
                        <?php echo esc_html($badge); ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <?php if ($headline): ?>
                <!-- Main Headline -->
                <<?php echo esc_attr($headline_tag); ?> class="hph-headline-spacing hph-text-2xl hph-lg:text-3xl hph-font-bold hph-leading-tight hph-text-primary">
                    <?php echo esc_html($headline); ?>
                </<?php echo esc_attr($headline_tag); ?>>
                <?php endif; ?>
                
                <?php if ($subheadline): ?>
                <!-- Subheadline -->
                <h3 class="hph-subheading-spacing hph-text-base hph-lg:text-lg hph-font-medium hph-opacity-75 hph-text-gray-600">
                    <?php echo esc_html($subheadline); ?>
                </h3>
                <?php endif; ?>
                
                <?php if ($content): ?>
                <!-- Content -->
                <div class="hph-content-spacing hph-text-base hph-leading-relaxed hph-text-gray-700">
                    <?php echo wp_kses_post($content); ?>
                </div>
                <?php endif; ?>
                
                <?php if ($content_type !== 'default' && $listing_id): ?>
                <!-- Property-specific content -->
                <div class="hph-property-content hph-mt-xl">
                    <?php
                    // Load appropriate template part based on content type
                    switch ($content_type) {
                        case 'gallery':
                            get_template_part('template-parts/listing-card', null, ['listing_id' => $listing_id]);
                            break;
                        case 'agent-profile':
                            get_template_part('template-parts/agent-card', null, ['listing_id' => $listing_id]);
                            break;
                        default:
                            // Fallback: show basic property info
                            if (function_exists('hpt_get_listing_title')) {
                                echo '<p class="hph-text-muted hph-text-sm">Property ID: ' . esc_html($listing_id) . '</p>';
                                echo '<p class="hph-text-base">' . esc_html(hpt_get_listing_title($listing_id)) . '</p>';
                            }
                            break;
                    }
                    ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($buttons)): ?>
                <!-- Buttons -->
                <div class="hph-button-spacing hph-flex hph-flex-wrap hph-gap-md hph-items-center <?php echo ($layout === 'centered' || $layout === 'full-width') ? 'hph-justify-center' : 'hph-justify-start'; ?>">
                    <?php foreach ($buttons as $button): 
                        $btn_defaults = array(
                            'text' => 'Button',
                            'url' => '#',
                            'style' => 'primary',
                            'size' => 'lg',
                            'icon' => '',
                            'target' => '_self'
                        );
                        $btn = wp_parse_args($button, $btn_defaults);
                        
                        $btn_classes = array(
                            'hph-btn',
                            'hph-btn-' . $btn['style'],
                            'hph-btn-' . $btn['size'],
                            'hph-transition-all',
                            'hph-duration-300'
                        );
                    ?>
                    <a 
                        href="<?php echo esc_url($btn['url']); ?>"
                        class="<?php echo esc_attr(implode(' ', $btn_classes)); ?>"
                        <?php if ($btn['target'] !== '_self'): ?>target="<?php echo esc_attr($btn['target']); ?>"<?php endif; ?>
                    >
                        <?php if ($btn['icon']): ?>
                        <i class="<?php echo esc_attr($btn['icon']); ?> hph-mr-sm"></i>
                        <?php endif; ?>
                        <?php echo esc_html($btn['text']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
            </div>
            
            <?php if ($layout === 'two-column' && $sidebar_content): ?>
            <!-- Sidebar Content -->
            <div class="hph-lg:col-span-1">
                <div class="hph-sticky hph-top-6">
                    <?php
                    // Handle different sidebar content types
                    if ($sidebar_content === 'contact-price' && $listing_id) {
                        // Load property sidebar components
                        get_template_part('template-parts/listing-card', null, [
                            'listing_id' => $listing_id,
                            'display_mode' => 'sidebar'
                        ]);
                    } else {
                        // Custom sidebar content
                        echo wp_kses_post($sidebar_content);
                    }
                    ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($image && ($layout === 'full-width' || $layout === 'centered')): ?>
            <!-- Full Width Image -->
            <div class="hph-mt-2xl">
                <div class="hph-relative hph-overflow-hidden hph-rounded-lg hph-shadow-xl hph-transition-transform">
                    <img 
                        src="<?php echo esc_url($image['url']); ?>"
                        alt="<?php echo esc_attr($image['alt'] ?? ''); ?>"
                        class="hph-w-full hph-h-auto hph-object-cover hph-property-image"
                        <?php if (isset($image['width'])): ?>width="<?php echo esc_attr($image['width']); ?>"<?php endif; ?>
                        <?php if (isset($image['height'])): ?>height="<?php echo esc_attr($image['height']); ?>"<?php endif; ?>
                        loading="lazy"
                    >
                </div>
            </div>
            <?php endif; ?>
            
        </div>
        
        <?php endif; // End default content type check ?>
        
    </div>
</section>

<style>
/* Custom styles using CSS variables for properties not yet in utilities */

/* Consistent spacing utilities */
.hph-mb-xs { margin-bottom: var(--hph-space-xs) !important; }
.hph-mb-sm { margin-bottom: var(--hph-space-sm) !important; }
.hph-mb-md { margin-bottom: var(--hph-space-md) !important; }
.hph-mb-lg { margin-bottom: var(--hph-space-lg) !important; }
.hph-mb-xl { margin-bottom: var(--hph-space-xl) !important; }
.hph-mb-2xl { margin-bottom: var(--hph-space-2xl) !important; }

.hph-mt-xs { margin-top: var(--hph-space-xs) !important; }
.hph-mt-sm { margin-top: var(--hph-space-sm) !important; }
.hph-mt-md { margin-top: var(--hph-space-md) !important; }
.hph-mt-lg { margin-top: var(--hph-space-lg) !important; }
.hph-mt-xl { margin-top: var(--hph-space-xl) !important; }
.hph-mt-2xl { margin-top: var(--hph-space-2xl) !important; }

.hph-px-xs { padding-left: var(--hph-space-xs) !important; padding-right: var(--hph-space-xs) !important; }
.hph-px-sm { padding-left: var(--hph-space-sm) !important; padding-right: var(--hph-space-sm) !important; }
.hph-px-md { padding-left: var(--hph-space-md) !important; padding-right: var(--hph-space-md) !important; }
.hph-px-lg { padding-left: var(--hph-space-lg) !important; padding-right: var(--hph-space-lg) !important; }

.hph-py-xs { padding-top: var(--hph-space-xs) !important; padding-bottom: var(--hph-space-xs) !important; }
.hph-py-sm { padding-top: var(--hph-space-sm) !important; padding-bottom: var(--hph-space-sm) !important; }
.hph-py-md { padding-top: var(--hph-space-md) !important; padding-bottom: var(--hph-space-md) !important; }
.hph-py-lg { padding-top: var(--hph-space-lg) !important; padding-bottom: var(--hph-space-lg) !important; }

.hph-gap-xs { gap: var(--hph-space-xs) !important; }
.hph-gap-sm { gap: var(--hph-space-sm) !important; }
.hph-gap-md { gap: var(--hph-space-md) !important; }
.hph-gap-lg { gap: var(--hph-space-lg) !important; }

/* Additional vertical spacing utilities */
.hph-mb-3xl { margin-bottom: var(--hph-space-3xl) !important; }
.hph-mt-3xl { margin-top: var(--hph-space-3xl) !important; }

/* Text flow spacing */
.hph-text-flow > * + * {
    margin-top: var(--hph-space-lg);
}

.hph-text-flow-tight > * + * {
    margin-top: var(--hph-space-md);
}

.hph-text-flow-loose > * + * {
    margin-top: var(--hph-space-xl);
}

/* Opacity utilities */
.hph-opacity-75 {
    opacity: 0.75;
}
.hph-text-center {
    text-align: center;
}

.hph-text-left {
    text-align: left;
}

.hph-items-center {
    align-items: center;
}

.hph-justify-center {
    justify-content: center;
}

.hph-justify-start {
    justify-content: flex-start;
}

.hph-flex-wrap {
    flex-wrap: wrap;
}

.hph-object-cover {
    object-fit: cover;
}

.hph-leading-tight {
    line-height: var(--hph-leading-tight);
}

.hph-leading-relaxed {
    line-height: var(--hph-leading-relaxed);
}

.hph-text-3xl {
    font-size: var(--hph-text-3xl, 1.875rem);
}

.hph-text-4xl {
    font-size: var(--hph-text-4xl, 2.25rem);
}

.hph-text-5xl {
    font-size: var(--hph-text-5xl, 3rem);
}


.hph-text-xl {
    font-size: var(--hph-text-xl);
}

.hph-text-base {
    font-size: var(--hph-text-base);
}

.hph-text-2xl {
    font-size: var(--hph-text-2xl);
}

.hph-text-lg {
    font-size: var(--hph-text-lg);
}

.hph-text-sm {
    font-size: var(--hph-text-sm);
}

.hph-font-bold {
    font-weight: var(--hph-font-bold);
}

.hph-font-medium {
    font-weight: var(--hph-font-medium);
}

.hph-text-primary {
    color: var(--hph-primary);
}

.hph-text-gray-600 {
    color: var(--hph-gray-600);
}

.hph-text-gray-700 {
    color: var(--hph-gray-700);
}

.hph-text-primary-700 {
    color: var(--hph-primary-700);
}

.hph-bg-primary-100 {
    background-color: var(--hph-primary-100);
}

/* Section Background Classes */
.hph-section-white {
    background-color: var(--hph-white);
    color: var(--hph-text-primary);
}

.hph-section-light {
    background-color: var(--hph-gray-50);
    color: var(--hph-text-primary);
}

.hph-section-dark {
    background-color: var(--hph-gray-800);
    color: var(--hph-white);
}

.hph-section-primary {
    background-color: var(--hph-primary);
    color: var(--hph-white);
}

.hph-section-secondary {
    background-color: var(--hph-secondary);
    color: var(--hph-white);
}

.hph-section-gradient {
    background: linear-gradient(135deg, var(--hph-primary) 0%, var(--hph-primary-dark) 100%);
    color: var(--hph-white);
}

.hph-section-image {
    position: relative;
    color: var(--hph-white);
}

.hph-section-image::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.4);
    z-index: 1;
}

.hph-section-image > * {
    position: relative;
    z-index: 2;
}

.hph-relative {
    position: relative;
}

.hph-overflow-hidden {
    overflow: hidden;
}

.hph-transition-transform {
    transition: transform var(--hph-transition-duration, 300ms) ease-out;
}

.hph-transition-colors {
    transition: color var(--hph-transition-duration, 300ms) ease-out,
                background-color var(--hph-transition-duration, 300ms) ease-out;
}

.hph-transition-all {
    transition: all var(--hph-transition-duration, 300ms) ease-out;
}

.hph-duration-300 {
    transition-duration: 300ms;
}

/* Responsive breakpoint utilities */
@media (min-width: 1024px) {
    .hph-lg\:grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    
    .hph-lg\:order-1 {
        order: 1;
    }
    
    .hph-lg\:order-2 {
        order: 2;
    }
    
    .hph-lg\:text-4xl {
        font-size: var(--hph-text-4xl, 2.25rem);
    }
    
    .hph-lg\:text-5xl {
        font-size: var(--hph-text-5xl, 3rem);
    }
    
    .hph-lg\:text-lg {
        font-size: var(--hph-text-lg, 1.125rem);
    }
    
    .hph-lg\:text-xl {
        font-size: var(--hph-text-xl, 1.25rem);
    }
    
    .hph-lg\:text-3xl {
        font-size: var(--hph-text-3xl, 1.875rem);
    }
    
    .hph-lg\:text-2xl {
        font-size: var(--hph-text-2xl, 1.5rem);
    }
}
</style>