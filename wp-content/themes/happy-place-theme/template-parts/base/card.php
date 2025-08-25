<?php
/**
 * HPH Card Component - Professional card layout matching section quality
 * 
 * A flexible, beautiful card component using proper HPH utilities and variables:
 * - Dynamic post-type adaptation (listing, agent, post)
 * - Bridge function integration for data
 * - Professional hover effects and animations
 * - Responsive image handling with fallbacks
 * - Interactive elements (favorites, sharing, contact)
 * 
 * Configurable variations:
 * - Layout: default, horizontal, featured, compact, minimal
 * - Style: modern, classic, bordered, elevated, flat
 * - Image: top, left, right, background, none
 * - Size: small, medium, large, full
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 * 
 * LAYOUT OPTIONS:
 * - layout: 'default' | 'horizontal' | 'featured' | 'compact' | 'minimal' | 'overlay'
 * - style: 'modern' | 'classic' | 'bordered' | 'elevated' | 'flat' | 'glass'
 * - image_position: 'top' | 'left' | 'right' | 'background' | 'none'
 * - size: 'small' | 'medium' | 'large' | 'full'
 * 
 * VISUAL STYLING:
 * - hover_effect: 'lift' | 'scale' | 'tilt' | 'glow' | 'none'
 * - border_radius: 'none' | 'sm' | 'md' | 'lg' | 'xl' | 'full'
 * - shadow: 'none' | 'sm' | 'md' | 'lg' | 'xl' | 'inner'
 * - overlay_style: 'none' | 'gradient' | 'solid' | 'blur'
 * 
 * CONTENT ELEMENTS:
 * - show_image: boolean
 * - show_badge: boolean (status, featured, etc.)
 * - show_meta: boolean (date, author, stats)
 * - show_excerpt: boolean
 * - show_actions: boolean (buttons, interactions)
 * - show_price: boolean (for listings)
 * - show_stats: boolean (beds/baths for listings, ratings for agents)
 * 
 * POST-TYPE SPECIFIC:
 * - listing: price, address, bed/bath/sqft, status, gallery count
 * - agent: company, title, rating, contact info, listings count
 * - post: date, author, categories, reading time
 * - open_house: date/time, location, agent
 * 
 * INTERACTIVE ELEMENTS:
 * - actions: array of custom action buttons
 * - enable_favorites: boolean (heart icon for listings)
 * - enable_sharing: boolean (share button)
 * - enable_quick_view: boolean (modal preview)
 * - click_action: 'navigate' | 'modal' | 'none'
 * 
 * DATA INTEGRATION:
 * - post_id: int (WordPress post ID)
 * - post_type: string (auto-detected or manual)
 * - bridge_data: array (override bridge function data)
 * - fallback_image: string (custom fallback image URL)
 * 
 * RESPONSIVE & ACCESSIBILITY:
 * - responsive_images: boolean (srcset support)
 * - lazy_loading: boolean (intersection observer)
 * - aria_labels: array (custom accessibility labels)
 * - keyboard_navigation: boolean
 */

// Default arguments with comprehensive options
$defaults = array(
    // Core Configuration
    'post_id' => get_the_ID(),
    'post_type' => get_post_type(),
    
    // Layout & Style
    'layout' => 'default',
    'style' => 'modern',
    'image_position' => 'top',
    'size' => 'medium',
    
    // Visual Effects
    'hover_effect' => 'lift',
    'border_radius' => 'lg',
    'shadow' => 'sm',
    'overlay_style' => 'gradient',
    
    // Content Display
    'show_image' => true,
    'show_badge' => true,
    'show_meta' => true,
    'show_excerpt' => false,
    'show_actions' => true,
    'show_price' => true,
    'show_stats' => true,
    
    // Interactive Features
    'enable_favorites' => true,
    'enable_sharing' => true,
    'enable_quick_view' => false,
    'click_action' => 'navigate',
    
    // Image Settings
    'image_size' => 'medium_large',
    'responsive_images' => true,
    'lazy_loading' => true,
    'fallback_image' => '',
    
    // Custom Content
    'actions' => array(),
    'bridge_data' => array(),
    'container_class' => '',
    'data_attributes' => array(),
    
    // Accessibility
    'aria_labels' => array(),
    'keyboard_navigation' => true
);

$args = wp_parse_args($args ?? [], $defaults);

$post_id = $args['post_id'];
$post_type = $args['post_type'];

if (!$post_id || !get_post($post_id)) return;

// Get post data using bridge functions or fallback
$post_data = array();
if (!empty($args['bridge_data'])) {
    $post_data = $args['bridge_data'];
} else {
    switch ($post_type) {
        case 'listing':
            if (function_exists('hpt_get_listing')) {
                $post_data = hpt_get_listing($post_id);
            }
            break;
        case 'agent':
            if (function_exists('hpt_get_agent')) {
                $post_data = hpt_get_agent($post_id);
            }
            break;
        case 'open_house':
            if (function_exists('hpt_get_open_house')) {
                $post_data = hpt_get_open_house($post_id);
            }
            break;
        default:
            $post_data = array(
                'title' => get_the_title($post_id),
                'excerpt' => get_the_excerpt($post_id),
                'permalink' => get_permalink($post_id),
                'date' => get_the_date('', $post_id),
                'author' => get_the_author_meta('display_name', get_post_field('post_author', $post_id))
            );
            break;
    }
}

// Merge with fallback data
$post_data = wp_parse_args($post_data, array(
    'title' => get_the_title($post_id),
    'permalink' => get_permalink($post_id)
));

// Build sophisticated card classes using utility system
$card_classes = array('hph-card');

// Base styling
$base_styles = array(
    'modern' => array('hph-bg-white', 'hph-overflow-hidden', 'hph-transition-all', 'hph-duration-300'),
    'classic' => array('hph-bg-white', 'hph-border', 'hph-border-gray-200', 'hph-transition-all', 'hph-duration-300'),
    'bordered' => array('hph-bg-white', 'hph-border-2', 'hph-border-gray-300', 'hph-transition-all', 'hph-duration-300'),
    'elevated' => array('hph-bg-white', 'hph-shadow-lg', 'hph-transition-all', 'hph-duration-300'),
    'flat' => array('hph-bg-gray-50', 'hph-transition-all', 'hph-duration-300'),
    'glass' => array('hph-bg-white', 'hph-bg-opacity-90', 'hph-backdrop-blur-sm', 'hph-transition-all', 'hph-duration-300')
);

// Border radius variations
$radius_classes = array(
    'none' => array(),
    'sm' => array('hph-rounded-sm'),
    'md' => array('hph-rounded-md'),
    'lg' => array('hph-rounded-lg'),
    'xl' => array('hph-rounded-xl'),
    'full' => array('hph-rounded-2xl')
);

// Shadow variations
$shadow_classes = array(
    'none' => array(),
    'sm' => array('hph-shadow-sm'),
    'md' => array('hph-shadow-md'),
    'lg' => array('hph-shadow-lg'),
    'xl' => array('hph-shadow-xl'),
    'inner' => array('hph-shadow-inner')
);

// Hover effects
$hover_effects = array(
    'lift' => array('hph-hover:shadow-xl', 'hph-hover:-translate-y-2', 'hph-hover:border-primary-200'),
    'scale' => array('hph-hover:scale-105', 'hph-hover:shadow-lg'),
    'tilt' => array('hph-hover:rotate-1', 'hph-hover:shadow-lg'),
    'glow' => array('hph-hover:shadow-2xl', 'hph-hover:ring-2', 'hph-hover:ring-primary-200'),
    'none' => array()
);

// Layout-specific classes
$layout_classes = array(
    'horizontal' => array('hph-flex', 'hph-flex-col', 'md:hph-flex-row'),
    'featured' => array('hph-ring-2', 'hph-ring-primary-100'),
    'compact' => array('hph-text-sm'),
    'minimal' => array('hph-border-0', 'hph-shadow-none'),
    'overlay' => array('hph-relative', 'hph-text-white')
);

// Size variations
$size_classes = array(
    'small' => array('hph-max-w-xs'),
    'medium' => array('hph-max-w-sm'),
    'large' => array('hph-max-w-md'),
    'full' => array('hph-w-full')
);

// Merge all classes
$card_classes = array_merge(
    $card_classes,
    $base_styles[$args['style']] ?? $base_styles['modern'],
    $radius_classes[$args['border_radius']] ?? $radius_classes['lg'],
    $shadow_classes[$args['shadow']] ?? $shadow_classes['sm'],
    $hover_effects[$args['hover_effect']] ?? $hover_effects['lift'],
    $layout_classes[$args['layout']] ?? array(),
    $size_classes[$args['size']] ?? $size_classes['medium']
);

// Add post-type specific classes
$card_classes[] = 'hph-card--' . $post_type;
$card_classes[] = 'hph-card--' . $args['layout'];
$card_classes[] = 'hph-card--' . $args['style'];

// Add container class if provided
if ($args['container_class']) {
    $card_classes[] = $args['container_class'];
}

$card_class = implode(' ', array_unique($card_classes));

// Get post image with sophisticated handling
$post_image = '';
$image_alt = '';
$has_gallery = false;
$gallery_count = 0;

if ($args['show_image'] && $args['image_position'] !== 'none') {
    $post_image = get_the_post_thumbnail_url($post_id, $args['image_size']);
    $image_alt = get_the_title($post_id);
    
    // Check for gallery
    if ($post_type === 'listing' && function_exists('hpt_get_listing_gallery')) {
        $gallery_images = hpt_get_listing_gallery($post_id);
        $has_gallery = !empty($gallery_images);
        $gallery_count = count($gallery_images);
    }
    
    if (!$post_image) {
        // Post-type specific fallback images
        $fallback_images = array(
            'listing' => get_template_directory_uri() . '/assets/images/fallback-property.jpg',
            'agent' => get_template_directory_uri() . '/assets/images/fallback-agent.jpg',
            'open_house' => get_template_directory_uri() . '/assets/images/fallback-event.jpg',
            'default' => get_template_directory_uri() . '/assets/images/fallback-post.jpg'
        );
        $post_image = $args['fallback_image'] ?: ($fallback_images[$post_type] ?? $fallback_images['default']);
    }
}

// Build data attributes
$data_attrs = array(
    'data-post-id' => $post_id,
    'data-post-type' => $post_type,
    'data-card-layout' => $args['layout']
);

if (!empty($args['data_attributes'])) {
    $data_attrs = array_merge($data_attrs, $args['data_attributes']);
}

$data_attributes = '';
foreach ($data_attrs as $key => $value) {
    $data_attributes .= sprintf(' %s="%s"', esc_attr($key), esc_attr($value));
}

$unique_id = 'card-' . $post_id . '-' . uniqid();
?>

<article class="<?php echo esc_attr($card_class); ?>" id="<?php echo esc_attr($unique_id); ?>"<?php echo $data_attributes; ?>>
    
    <?php if ($post_image && $args['show_image'] && $args['image_position'] !== 'none') : ?>
        <!-- Card Image Section -->
        <div class="<?php echo $args['layout'] === 'horizontal' ? 'hph-w-full md:hph-w-2/5' : 'hph-w-full'; ?> hph-relative hph-overflow-hidden <?php echo $args['image_position'] === 'background' ? 'hph-absolute hph-inset-0' : ''; ?>">
            
            <?php if ($args['click_action'] === 'navigate') : ?>
                <a href="<?php echo esc_url($post_data['permalink']); ?>" 
                   class="hph-block hph-relative hph-group hph-focus:outline-none hph-focus:ring-2 hph-focus:ring-primary-500 hph-focus:ring-offset-2 hph-rounded-lg"
                   aria-label="<?php echo esc_attr(sprintf(__('View %s', 'happy-place-theme'), $post_data['title'])); ?>">
            <?php else : ?>
                <div class="hph-block hph-relative hph-group">
            <?php endif; ?>
            
                <!-- Main Image -->
                <?php if ($args['responsive_images'] && function_exists('wp_get_attachment_image')) : ?>
                    <?php
                    $attachment_id = get_post_thumbnail_id($post_id);
                    if ($attachment_id) :
                        echo wp_get_attachment_image(
                            $attachment_id,
                            $args['image_size'],
                            false,
                            array(
                                'class' => 'hph-w-full hph-h-64 hph-object-cover hph-transition-transform hph-duration-500 hph-group-hover:scale-110',
                                'alt' => $image_alt,
                                'loading' => $args['lazy_loading'] ? 'lazy' : 'eager'
                            )
                        );
                    else : ?>
                        <img src="<?php echo esc_url($post_image); ?>" 
                             alt="<?php echo esc_attr($image_alt); ?>"
                             class="hph-w-full hph-h-64 hph-object-cover hph-transition-transform hph-duration-500 hph-group-hover:scale-110"
                             <?php echo $args['lazy_loading'] ? 'loading="lazy"' : ''; ?>>
                    <?php endif; ?>
                <?php else : ?>
                    <img src="<?php echo esc_url($post_image); ?>" 
                         alt="<?php echo esc_attr($image_alt); ?>"
                         class="hph-w-full hph-h-64 hph-object-cover hph-transition-transform hph-duration-500 hph-group-hover:scale-110"
                         <?php echo $args['lazy_loading'] ? 'loading="lazy"' : ''; ?>>
                <?php endif; ?>
                
                <!-- Image Overlay -->
                <?php if ($args['overlay_style'] !== 'none') : ?>
                    <?php
                    $overlay_classes = array('hph-absolute', 'hph-inset-0', 'hph-transition-all', 'hph-duration-300');
                    switch ($args['overlay_style']) {
                        case 'gradient':
                            $overlay_classes[] = 'hph-bg-gradient-to-t hph-from-black/60 hph-via-black/20 hph-to-transparent hph-opacity-0 hph-group-hover:opacity-100';
                            break;
                        case 'solid':
                            $overlay_classes[] = 'hph-bg-black/40 hph-opacity-0 hph-group-hover:opacity-100';
                            break;
                        case 'blur':
                            $overlay_classes[] = 'hph-backdrop-blur-sm hph-bg-white/10 hph-opacity-0 hph-group-hover:opacity-100';
                            break;
                    }
                    ?>
                    <div class="<?php echo esc_attr(implode(' ', $overlay_classes)); ?>"></div>
                <?php endif; ?>
                
                <!-- Status Badge -->
                <?php if ($args['show_badge'] && !empty($post_data['status'])) : ?>
                    <div class="hph-absolute hph-top-4 hph-left-4 hph-z-10">
                        <?php
                        $badge_classes = array(
                            'hph-inline-block',
                            'hph-px-md',
                            'hph-py-sm',
                            'hph-text-xs',
                            'hph-font-bold',
                            'hph-uppercase',
                            'hph-tracking-wide',
                            'hph-rounded-full',
                            'hph-shadow-sm',
                            'hph-backdrop-blur-sm'
                        );
                        
                        // Status-specific colors
                        $status = strtolower($post_data['status']);
                        switch ($status) {
                            case 'active':
                            case 'available':
                            case 'for_sale':
                                $badge_classes = array_merge($badge_classes, array('hph-bg-success-600', 'hph-text-white'));
                                break;
                            case 'sold':
                            case 'rented':
                            case 'off_market':
                                $badge_classes = array_merge($badge_classes, array('hph-bg-gray-600', 'hph-text-white'));
                                break;
                            case 'pending':
                            case 'under_contract':
                                $badge_classes = array_merge($badge_classes, array('hph-bg-warning-600', 'hph-text-white'));
                                break;
                            case 'featured':
                                $badge_classes = array_merge($badge_classes, array('hph-bg-primary-600', 'hph-text-white'));
                                break;
                            default:
                                $badge_classes = array_merge($badge_classes, array('hph-bg-gray-500', 'hph-text-white'));
                        }
                        ?>
                        <span class="<?php echo esc_attr(implode(' ', $badge_classes)); ?>">
                            <?php echo esc_html(ucfirst(str_replace(['_', '-'], ' ', $post_data['status']))); ?>
                        </span>
                    </div>
                <?php endif; ?>
                
                <!-- Price Badge (Listings) -->
                <?php if ($args['show_price'] && !empty($post_data['price']) && $post_type === 'listing') : ?>
                    <div class="hph-absolute hph-bottom-4 hph-right-4 hph-z-10">
                        <span class="hph-inline-block hph-px-lg hph-py-sm hph-bg-white hph-bg-opacity-95 hph-backdrop-blur-sm hph-text-gray-900 hph-font-bold hph-text-lg hph-rounded-lg hph-shadow-md">
                            <?php if (is_numeric($post_data['price'])) : ?>
                                $<?php echo esc_html(number_format($post_data['price'])); ?>
                            <?php else : ?>
                                <?php echo esc_html($post_data['price']); ?>
                            <?php endif; ?>
                        </span>
                    </div>
                <?php endif; ?>
                
                <!-- Gallery Indicator -->
                <?php if ($has_gallery && $gallery_count > 1) : ?>
                    <div class="hph-absolute hph-bottom-4 hph-left-4 hph-z-10">
                        <span class="hph-inline-flex hph-items-center hph-gap-xs hph-px-sm hph-py-xs hph-bg-black/70 hph-text-white hph-text-xs hph-font-medium hph-rounded-md hph-backdrop-blur-sm">
                            <i class="fas fa-images"></i>
                            <?php echo esc_html($gallery_count); ?>
                        </span>
                    </div>
                <?php endif; ?>
                
                <!-- Hover Action Overlay -->
                <div class="hph-absolute hph-inset-0 hph-flex hph-items-center hph-justify-center hph-opacity-0 hph-group-hover:opacity-100 hph-transition-all hph-duration-300 hph-z-10">
                    <div class="hph-transform hph-translate-y-2 hph-group-hover:translate-y-0 hph-transition-transform hph-duration-300">
                        <?php if ($args['enable_quick_view']) : ?>
                            <button class="hph-inline-flex hph-items-center hph-gap-sm hph-px-lg hph-py-md hph-bg-white hph-bg-opacity-95 hph-text-gray-900 hph-font-semibold hph-rounded-lg hph-shadow-lg hph-backdrop-blur-sm hph-hover:bg-opacity-100 hph-transition-all hph-duration-200" 
                                    data-action="quick-view" 
                                    data-post-id="<?php echo esc_attr($post_id); ?>">
                                <i class="fas fa-search-plus"></i>
                                <?php _e('Quick View', 'happy-place-theme'); ?>
                            </button>
                        <?php else : ?>
                            <span class="hph-inline-flex hph-items-center hph-gap-sm hph-px-lg hph-py-md hph-bg-white hph-bg-opacity-95 hph-text-gray-900 hph-font-semibold hph-rounded-lg hph-shadow-lg hph-backdrop-blur-sm">
                                <i class="fas fa-eye"></i>
                                <?php _e('View Details', 'happy-place-theme'); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            
            <?php if ($args['click_action'] === 'navigate') : ?>
                </a>
            <?php else : ?>
                </div>
            <?php endif; ?>
            
        </div>
    <?php endif; ?>
    
    <!-- Card Content Section -->
    <div class="<?php echo $args['layout'] === 'horizontal' ? 'hph-flex-1' : 'hph-w-full'; ?> hph-p-lg hph-space-y-md">
        
        <!-- Post Type Icon (Optional) -->
        <?php if ($args['show_badge'] && empty($post_data['status'])) : ?>
            <div class="hph-flex hph-items-center hph-gap-sm hph-text-xs hph-text-gray-500 hph-uppercase hph-tracking-wide hph-font-medium">
                <?php
                $type_icons = array(
                    'listing' => 'fa-home',
                    'agent' => 'fa-user-tie',
                    'open_house' => 'fa-calendar-alt',
                    'post' => 'fa-file-alt'
                );
                $icon = $type_icons[$post_type] ?? $type_icons['post'];
                ?>
                <i class="fas <?php echo esc_attr($icon); ?>"></i>
                <span><?php echo esc_html(ucfirst(str_replace('_', ' ', $post_type))); ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Title -->
        <h3 class="hph-text-xl hph-font-bold hph-text-gray-900 hph-leading-tight hph-line-clamp-2">
            <a href="<?php echo esc_url($post_data['permalink']); ?>" 
               class="hph-hover:text-primary-600 hph-transition-colors hph-duration-200 hph-focus:outline-none hph-focus:text-primary-600">
                <?php echo esc_html($post_data['title']); ?>
            </a>
        </h3>
        
        <!-- Post-Type Specific Meta -->
        <?php if ($args['show_meta']) : ?>
            <div class="hph-space-y-sm">
                
                <?php if ($post_type === 'listing') : ?>
                    
                    <!-- Address -->
                    <?php if (!empty($post_data['address'])) : ?>
                        <div class="hph-flex hph-items-start hph-text-gray-600 hph-text-sm">
                            <i class="fas fa-map-marker-alt hph-mt-1 hph-mr-sm hph-text-primary-500 hph-flex-shrink-0"></i>
                            <span class="hph-line-clamp-1"><?php echo esc_html($post_data['address']); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Property Stats -->
                    <?php if ($args['show_stats'] && (!empty($post_data['bedrooms']) || !empty($post_data['bathrooms']) || !empty($post_data['square_feet']))) : ?>
                        <div class="hph-flex hph-items-center hph-gap-lg hph-text-gray-600 hph-text-sm">
                            <?php if (!empty($post_data['bedrooms'])) : ?>
                                <span class="hph-flex hph-items-center hph-gap-xs">
                                    <i class="fas fa-bed hph-text-primary-500"></i>
                                    <?php echo esc_html($post_data['bedrooms']); ?>
                                    <span class="hph-hidden sm:hph-inline"><?php echo $post_data['bedrooms'] == 1 ? 'bed' : 'beds'; ?></span>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($post_data['bathrooms'])) : ?>
                                <span class="hph-flex hph-items-center hph-gap-xs">
                                    <i class="fas fa-bath hph-text-primary-500"></i>
                                    <?php echo esc_html($post_data['bathrooms']); ?>
                                    <span class="hph-hidden sm:hph-inline"><?php echo $post_data['bathrooms'] == 1 ? 'bath' : 'baths'; ?></span>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($post_data['square_feet'])) : ?>
                                <span class="hph-flex hph-items-center hph-gap-xs">
                                    <i class="fas fa-ruler-combined hph-text-primary-500"></i>
                                    <?php echo esc_html(number_format($post_data['square_feet'])); ?>
                                    <span class="hph-hidden sm:hph-inline">sq ft</span>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                <?php elseif ($post_type === 'agent') : ?>
                    
                    <div class="hph-space-y-xs">
                        <!-- Agent Title & Company -->
                        <?php if (!empty($post_data['title']) || !empty($post_data['company'])) : ?>
                            <div class="hph-text-sm">
                                <?php if (!empty($post_data['title'])) : ?>
                                    <div class="hph-text-gray-600"><?php echo esc_html($post_data['title']); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($post_data['company'])) : ?>
                                    <div class="hph-text-primary-600 hph-font-medium"><?php echo esc_html($post_data['company']); ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Agent Stats -->
                        <?php if ($args['show_stats']) : ?>
                            <div class="hph-flex hph-items-center hph-gap-lg hph-text-xs hph-text-gray-500">
                                <?php if (!empty($post_data['stats']['active_listings'])) : ?>
                                    <span class="hph-flex hph-items-center hph-gap-xs">
                                        <i class="fas fa-home hph-text-primary-400"></i>
                                        <?php echo esc_html($post_data['stats']['active_listings']); ?> listings
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($post_data['stats']['total_sales'])) : ?>
                                    <span class="hph-flex hph-items-center hph-gap-xs">
                                        <i class="fas fa-handshake hph-text-primary-400"></i>
                                        <?php echo esc_html($post_data['stats']['total_sales']); ?> sold
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Agent Rating -->
                        <?php if (!empty($post_data['stats']['average_rating']) && $post_data['stats']['average_rating'] > 0) : ?>
                            <div class="hph-flex hph-items-center hph-gap-xs hph-text-sm">
                                <div class="hph-flex hph-text-yellow-400">
                                    <?php for ($i = 1; $i <= 5; $i++) : ?>
                                        <i class="fas fa-star<?php echo $i > $post_data['stats']['average_rating'] ? ' hph-text-gray-300' : ''; ?> hph-text-xs"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="hph-text-gray-600 hph-text-xs"><?php echo esc_html(number_format($post_data['stats']['average_rating'], 1)); ?></span>
                                <?php if (!empty($post_data['stats']['review_count'])) : ?>
                                    <span class="hph-text-gray-400 hph-text-xs">(<?php echo esc_html($post_data['stats']['review_count']); ?>)</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                <?php elseif ($post_type === 'open_house') : ?>
                    
                    <!-- Date & Time -->
                    <?php if (!empty($post_data['start_date'])) : ?>
                        <div class="hph-flex hph-items-center hph-text-gray-600 hph-text-sm">
                            <i class="fas fa-calendar-alt hph-mr-sm hph-text-primary-500"></i>
                            <span><?php echo esc_html(date('M j, Y g:i A', strtotime($post_data['start_date']))); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Location -->
                    <?php if (!empty($post_data['address'])) : ?>
                        <div class="hph-flex hph-items-start hph-text-gray-600 hph-text-sm">
                            <i class="fas fa-map-marker-alt hph-mt-1 hph-mr-sm hph-text-primary-500 hph-flex-shrink-0"></i>
                            <span class="hph-line-clamp-1"><?php echo esc_html($post_data['address']); ?></span>
                        </div>
                    <?php endif; ?>
                    
                <?php else : ?>
                    
                    <!-- Standard Post Meta -->
                    <div class="hph-flex hph-items-center hph-text-gray-500 hph-text-sm">
                        <i class="fas fa-calendar-alt hph-mr-sm"></i>
                        <time datetime="<?php echo esc_attr(get_the_date('c', $post_id)); ?>">
                            <?php echo esc_html($post_data['date'] ?? get_the_date('', $post_id)); ?>
                        </time>
                        <?php if (!empty($post_data['author'])) : ?>
                            <span class="hph-mx-sm hph-text-gray-400">•</span>
                            <span><?php echo esc_html($post_data['author']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                <?php endif; ?>
                
            </div>
        <?php endif; ?>
        
        <!-- Excerpt -->
        <?php if ($args['show_excerpt']) : ?>
            <div class="hph-text-gray-600 hph-text-sm hph-leading-relaxed hph-line-clamp-2">
                <?php 
                $excerpt = $post_data['excerpt'] ?? get_the_excerpt($post_id);
                echo esc_html(wp_trim_words($excerpt, 20));
                ?>
            </div>
        <?php endif; ?>
        
        <!-- Actions -->
        <?php if ($args['show_actions']) : ?>
            <div class="hph-flex hph-items-center hph-justify-between hph-pt-sm hph-border-t hph-border-gray-100">
                
                <!-- Primary Action -->
                <a href="<?php echo esc_url($post_data['permalink']); ?>" 
                   class="hph-inline-flex hph-items-center hph-gap-sm hph-px-lg hph-py-sm hph-bg-primary-600 hph-text-white hph-font-medium hph-rounded-lg hph-transition-all hph-duration-200 hph-hover:bg-primary-700 hph-hover:scale-105 hph-shadow-sm hph-hover:shadow-md hph-focus:outline-none hph-focus:ring-2 hph-focus:ring-primary-500 hph-focus:ring-offset-2">
                    <i class="fas <?php 
                        echo $post_type === 'listing' ? 'fa-home' : 
                            ($post_type === 'agent' ? 'fa-user' : 
                            ($post_type === 'open_house' ? 'fa-calendar-check' : 'fa-arrow-right')); 
                    ?>"></i>
                    <?php 
                    switch ($post_type) {
                        case 'listing':
                            _e('View Property', 'happy-place-theme');
                            break;
                        case 'agent':
                            _e('View Profile', 'happy-place-theme');
                            break;
                        case 'open_house':
                            _e('More Details', 'happy-place-theme');
                            break;
                        default:
                            _e('Read More', 'happy-place-theme');
                            break;
                    }
                    ?>
                </a>
                
                <!-- Secondary Actions -->
                <div class="hph-flex hph-items-center hph-gap-sm">
                    
                    <?php if ($post_type === 'listing') : ?>
                        
                        <!-- Favorite Button -->
                        <?php if ($args['enable_favorites']) : ?>
                            <button class="hph-action-btn hph-favorite-btn hph-inline-flex hph-items-center hph-justify-center hph-w-10 hph-h-10 hph-text-gray-500 hph-hover:text-red-500 hph-hover:bg-red-50 hph-rounded-lg hph-transition-all hph-duration-200 hph-focus:outline-none hph-focus:ring-2 hph-focus:ring-red-500 hph-focus:ring-offset-2" 
                                    title="<?php esc_attr_e('Add to favorites', 'happy-place-theme'); ?>"
                                    data-action="favorite" 
                                    data-post-id="<?php echo esc_attr($post_id); ?>"
                                    aria-label="<?php esc_attr_e('Add to favorites', 'happy-place-theme'); ?>">
                                <i class="far fa-heart"></i>
                            </button>
                        <?php endif; ?>
                        
                        <!-- Share Button -->
                        <?php if ($args['enable_sharing']) : ?>
                            <button class="hph-action-btn hph-share-btn hph-inline-flex hph-items-center hph-justify-center hph-w-10 hph-h-10 hph-text-gray-500 hph-hover:text-primary-500 hph-hover:bg-primary-50 hph-rounded-lg hph-transition-all hph-duration-200 hph-focus:outline-none hph-focus:ring-2 hph-focus:ring-primary-500 hph-focus:ring-offset-2" 
                                    title="<?php esc_attr_e('Share property', 'happy-place-theme'); ?>"
                                    data-action="share" 
                                    data-post-id="<?php echo esc_attr($post_id); ?>"
                                    data-title="<?php echo esc_attr($post_data['title']); ?>"
                                    data-url="<?php echo esc_attr($post_data['permalink']); ?>"
                                    aria-label="<?php esc_attr_e('Share property', 'happy-place-theme'); ?>">
                                <i class="fas fa-share-alt"></i>
                            </button>
                        <?php endif; ?>
                        
                    <?php elseif ($post_type === 'agent') : ?>
                        
                        <!-- Contact Actions -->
                        <?php if (!empty($post_data['phone'])) : ?>
                            <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $post_data['phone'])); ?>" 
                               class="hph-action-btn hph-inline-flex hph-items-center hph-justify-center hph-w-10 hph-h-10 hph-text-gray-500 hph-hover:text-green-500 hph-hover:bg-green-50 hph-rounded-lg hph-transition-all hph-duration-200 hph-focus:outline-none hph-focus:ring-2 hph-focus:ring-green-500 hph-focus:ring-offset-2" 
                               title="<?php esc_attr_e('Call agent', 'happy-place-theme'); ?>"
                               aria-label="<?php esc_attr_e('Call agent', 'happy-place-theme'); ?>">
                                <i class="fas fa-phone"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($post_data['email'])) : ?>
                            <a href="mailto:<?php echo esc_attr($post_data['email']); ?>" 
                               class="hph-action-btn hph-inline-flex hph-items-center hph-justify-center hph-w-10 hph-h-10 hph-text-gray-500 hph-hover:text-blue-500 hph-hover:bg-blue-50 hph-rounded-lg hph-transition-all hph-duration-200 hph-focus:outline-none hph-focus:ring-2 hph-focus:ring-blue-500 hph-focus:ring-offset-2" 
                               title="<?php esc_attr_e('Email agent', 'happy-place-theme'); ?>"
                               aria-label="<?php esc_attr_e('Email agent', 'happy-place-theme'); ?>">
                                <i class="fas fa-envelope"></i>
                            </a>
                        <?php endif; ?>
                        
                    <?php endif; ?>
                    
                    <!-- Custom Actions -->
                    <?php if (!empty($args['actions'])) : ?>
                        <?php foreach ($args['actions'] as $action) : ?>
                            <a href="<?php echo esc_url($action['url'] ?? '#'); ?>" 
                               class="hph-action-btn hph-inline-flex hph-items-center hph-gap-sm hph-px-md hph-py-sm hph-text-sm hph-font-medium hph-rounded-lg hph-transition-all hph-duration-200 <?php echo esc_attr($action['class'] ?? 'hph-text-gray-600 hph-hover:text-primary-600 hph-hover:bg-primary-50'); ?>"
                               <?php if (!empty($action['title'])) : ?>title="<?php echo esc_attr($action['title']); ?>"<?php endif; ?>
                               <?php if (!empty($action['target'])) : ?>target="<?php echo esc_attr($action['target']); ?>"<?php endif; ?>>
                                <?php if (!empty($action['icon'])) : ?>
                                    <i class="fas <?php echo esc_attr($action['icon']); ?>"></i>
                                <?php endif; ?>
                                <?php if (!empty($action['text'])) : ?>
                                    <span><?php echo esc_html($action['text']); ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                </div>
                
            </div>
        <?php endif; ?>
        
    </div>
    
</article>