<?php
/**
 * HPH Card Component Template
 * 
 * A versatile card component for displaying content in various layouts:
 * - Multiple card styles (default, elevated, bordered, gradient, overlay, minimal)
 * - Flexible layouts (vertical, horizontal, compact)
 * - Support for images, videos, badges, and metadata
 * - Hover effects and animations
 * - Responsive design with mobile-first approach
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 * 
 * Args:
 * - style: 'default' | 'elevated' | 'bordered' | 'gradient' | 'overlay' | 'minimal' | 'property'
 * - layout: 'vertical' | 'horizontal' | 'compact'
 * - size: 'sm' | 'md' | 'lg' | 'xl'
 * - image: string (URL)
 * - image_position: 'top' | 'left' | 'right' | 'background'
 * - image_ratio: 'square' | 'landscape' | 'portrait' | 'wide' | 'cinema'
 * - video: string (URL for video preview)
 * - badge: string
 * - badge_style: 'default' | 'primary' | 'success' | 'warning' | 'danger'
 * - badge_position: 'top-left' | 'top-right' | 'bottom-left' | 'bottom-right'
 * - title: string
 * - subtitle: string
 * - content: string
 * - content_limit: int (character limit for content)
 * - meta: array of meta items
 * - buttons: array of button configurations
 * - link_url: string (makes entire card clickable)
 * - link_target: '_self' | '_blank'
 * - hover_effect: 'none' | 'lift' | 'scale' | 'shadow' | 'overlay'
 * - animate: boolean
 * - animation_delay: string (ms)
 * - classes: string (additional custom classes)
 * - attributes: array (additional HTML attributes)
 * 
 * Property-specific args:
 * - listing_id: int
 * - show_price: boolean
 * - show_status: boolean
 * - show_address: boolean
 * - show_details: boolean (beds, baths, sqft)
 * - show_mls: boolean
 * - show_favorite: boolean
 * - show_compare: boolean
 */

// Default arguments
$defaults = array(
    'style' => 'default',
    'layout' => 'vertical',
    'size' => 'md',
    'image' => '',
    'image_position' => 'top',
    'image_ratio' => 'landscape',
    'video' => '',
    'badge' => '',
    'badge_style' => 'default',
    'badge_position' => 'top-left',
    'title' => '',
    'subtitle' => '',
    'content' => '',
    'content_limit' => 150,
    'meta' => array(),
    'buttons' => array(),
    'link_url' => '',
    'link_target' => '_self',
    'hover_effect' => 'lift',
    'animate' => false,
    'animation_delay' => '0',
    'classes' => '',
    'attributes' => array(),
    // Property-specific defaults
    'listing_id' => 0,
    'show_price' => true,
    'show_status' => true,
    'show_address' => true,
    'show_details' => true,
    'show_mls' => false,
    'show_favorite' => true,
    'show_compare' => false
);

// Merge with provided args
$config = wp_parse_args($args ?? array(), $defaults);

// Extract configuration
$style = $config['style'];
$layout = $config['layout'];
$size = $config['size'];
$image = $config['image'];
$image_position = $config['image_position'];
$image_ratio = $config['image_ratio'];
$video = $config['video'];
$badge = $config['badge'];
$badge_style = $config['badge_style'];
$badge_position = $config['badge_position'];
$title = $config['title'];
$subtitle = $config['subtitle'];
$content = $config['content'];
$content_limit = $config['content_limit'];
$meta = $config['meta'];
$buttons = $config['buttons'];
$link_url = $config['link_url'];
$link_target = $config['link_target'];
$hover_effect = $config['hover_effect'];
$animate = $config['animate'];
$animation_delay = $config['animation_delay'];
$custom_classes = $config['classes'];
$custom_attributes = $config['attributes'];

// Property-specific configuration
$listing_id = $config['listing_id'];
$show_price = $config['show_price'];
$show_status = $config['show_status'];
$show_address = $config['show_address'];
$show_details = $config['show_details'];
$show_mls = $config['show_mls'];
$show_favorite = $config['show_favorite'];
$show_compare = $config['show_compare'];

// Property mode auto-configuration
if ($style === 'property' && $listing_id) {
    // Get property data using bridge functions
    if (function_exists('hpt_get_listing_title') && empty($title)) {
        $title = hpt_get_listing_title($listing_id);
    }
    if (function_exists('hpt_get_listing_address') && empty($subtitle) && $show_address) {
        $subtitle = hpt_get_listing_address($listing_id);
    }
    if (function_exists('hpt_get_listing_featured_image') && empty($image)) {
        $image = hpt_get_listing_featured_image($listing_id);
    }
    if (function_exists('hpt_get_listing_permalink') && empty($link_url)) {
        $link_url = hpt_get_listing_permalink($listing_id);
    }
    if (function_exists('hpt_get_listing_excerpt') && empty($content)) {
        $content = hpt_get_listing_excerpt($listing_id);
    }
    
    // Build property meta array
    $property_meta = array();
    
    if ($show_details) {
        if (function_exists('hpt_get_listing_bedrooms')) {
            $beds = hpt_get_listing_bedrooms($listing_id);
            if ($beds) {
                $property_meta[] = array(
                    'icon' => 'fas fa-bed',
                    'text' => $beds . ' ' . _n('Bed', 'Beds', intval($beds), 'happy-place-theme')
                );
            }
        }
        if (function_exists('hpt_get_listing_bathrooms')) {
            $baths = hpt_get_listing_bathrooms($listing_id);
            if ($baths) {
                $property_meta[] = array(
                    'icon' => 'fas fa-bath',
                    'text' => $baths . ' ' . _n('Bath', 'Baths', floatval($baths), 'happy-place-theme')
                );
            }
        }
        if (function_exists('hpt_get_listing_square_footage')) {
            $sqft = hpt_get_listing_square_footage($listing_id);
            if ($sqft) {
                $property_meta[] = array(
                    'icon' => 'fas fa-ruler-combined',
                    'text' => number_format($sqft) . ' Sq Ft'
                );
            }
        }
    }
    
    if ($show_mls && function_exists('hpt_get_listing_mls_number')) {
        $mls = hpt_get_listing_mls_number($listing_id);
        if ($mls) {
            $property_meta[] = array(
                'icon' => 'fas fa-hashtag',
                'text' => 'MLS #' . $mls
            );
        }
    }
    
    // Merge property meta with any provided meta
    $meta = array_merge($property_meta, $meta);
    
    // Set up price badge if enabled
    if ($show_price && function_exists('hpt_get_listing_price_formatted')) {
        $price = hpt_get_listing_price_formatted($listing_id);
        if ($price && empty($badge)) {
            $badge = $price;
            $badge_style = 'primary';
            $badge_position = 'bottom-left';
        }
    }
    
    // Set up status badge if enabled
    if ($show_status && function_exists('hpt_get_listing_status')) {
        $status = hpt_get_listing_status($listing_id);
        if ($status && $status !== 'Active') {
            // Override price badge if status is not active
            $badge = $status;
            $badge_style = $status === 'Sold' ? 'success' : 'warning';
            $badge_position = 'top-right';
        }
    }
}

// Build card classes
$card_classes = array(
    'hph-card',
    'hph-card-' . $style,
    'hph-card-' . $layout,
    'hph-card-' . $size
);

// Add hover effect class
if ($hover_effect !== 'none') {
    $card_classes[] = 'hph-card-hover-' . $hover_effect;
}

// Add animation classes
if ($animate) {
    $card_classes[] = 'hph-card-animate';
    $card_classes[] = 'hph-fade-in-up';
}

// Add image position class
if ($image && $image_position !== 'top') {
    $card_classes[] = 'hph-card-image-' . $image_position;
}

// Add custom classes
if ($custom_classes) {
    $card_classes[] = $custom_classes;
}

// Build wrapper element
$wrapper_tag = $link_url ? 'a' : 'article';
$wrapper_attrs = array(
    'class' => implode(' ', $card_classes)
);

if ($link_url) {
    $wrapper_attrs['href'] = esc_url($link_url);
    if ($link_target !== '_self') {
        $wrapper_attrs['target'] = esc_attr($link_target);
    }
}

if ($animate && $animation_delay !== '0') {
    $wrapper_attrs['style'] = 'animation-delay: ' . esc_attr($animation_delay) . 'ms';
}

// Add custom attributes
foreach ($custom_attributes as $key => $value) {
    $wrapper_attrs[$key] = esc_attr($value);
}

// Limit content if specified
if ($content && $content_limit > 0) {
    $content = wp_trim_words($content, $content_limit, '...');
}

// Build image ratio class
$image_ratio_class = 'hph-card-media-' . $image_ratio;

// Ensure Font Awesome is loaded
if (!wp_script_is('font-awesome', 'enqueued')) {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', array(), '6.5.1');
}
?>

<<?php echo $wrapper_tag; ?> <?php foreach ($wrapper_attrs as $attr => $value): ?>
    <?php echo $attr; ?>="<?php echo $value; ?>"
<?php endforeach; ?>>
    
    <?php if ($image && in_array($image_position, array('top', 'left', 'right', 'background'))): ?>
    <!-- Card Media -->
    <div class="hph-card-media <?php echo esc_attr($image_ratio_class); ?>">
        <?php if ($video): ?>
        <!-- Video Preview -->
        <video 
            class="hph-card-video"
            muted 
            loop 
            playsinline
            data-autoplay="hover"
            poster="<?php echo esc_url($image); ?>"
        >
            <source src="<?php echo esc_url($video); ?>" type="video/mp4">
            <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>" class="hph-card-image">
        </video>
        <?php else: ?>
        <!-- Image -->
        <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>" class="hph-card-image" loading="lazy">
        <?php endif; ?>
        
        <?php if ($style === 'overlay'): ?>
        <!-- Overlay Gradient -->
        <div class="hph-card-overlay"></div>
        <?php endif; ?>
        
        <?php if ($badge && in_array($image_position, array('top', 'background'))): ?>
        <!-- Badge -->
        <div class="hph-card-badge hph-badge-<?php echo esc_attr($badge_position); ?> hph-badge-<?php echo esc_attr($badge_style); ?>">
            <span><?php echo esc_html($badge); ?></span>
        </div>
        <?php endif; ?>
        
        <?php if ($style === 'property' && ($show_favorite || $show_compare)): ?>
        <!-- Property Actions -->
        <div class="hph-card-actions">
            <?php if ($show_favorite): ?>
            <button class="hph-card-action hph-action-favorite" data-listing-id="<?php echo esc_attr($listing_id); ?>" aria-label="Add to favorites">
                <i class="far fa-heart"></i>
            </button>
            <?php endif; ?>
            <?php if ($show_compare): ?>
            <button class="hph-card-action hph-action-compare" data-listing-id="<?php echo esc_attr($listing_id); ?>" aria-label="Add to compare">
                <i class="fas fa-exchange-alt"></i>
            </button>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <!-- Card Content -->
    <div class="hph-card-content">
        
        <?php if ($badge && !in_array($image_position, array('top', 'background'))): ?>
        <!-- Badge (for non-image cards) -->
        <div class="hph-card-badge-inline hph-badge-<?php echo esc_attr($badge_style); ?>">
            <span><?php echo esc_html($badge); ?></span>
        </div>
        <?php endif; ?>
        
        <?php if ($subtitle): ?>
        <!-- Card Subtitle -->
        <div class="hph-card-subtitle">
            <?php echo esc_html($subtitle); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($title): ?>
        <!-- Card Title -->
        <h3 class="hph-card-title">
            <?php echo esc_html($title); ?>
        </h3>
        <?php endif; ?>
        
        <?php if ($content): ?>
        <!-- Card Description -->
        <div class="hph-card-description">
            <?php echo wp_kses_post($content); ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($meta)): ?>
        <!-- Card Meta -->
        <div class="hph-card-meta">
            <?php foreach ($meta as $meta_item): 
                $meta_defaults = array(
                    'icon' => '',
                    'text' => '',
                    'url' => '',
                    'tooltip' => ''
                );
                $meta_item = wp_parse_args($meta_item, $meta_defaults);
            ?>
            <div class="hph-card-meta-item" <?php if ($meta_item['tooltip']): ?>title="<?php echo esc_attr($meta_item['tooltip']); ?>"<?php endif; ?>>
                <?php if ($meta_item['icon']): ?>
                <i class="<?php echo esc_attr($meta_item['icon']); ?> hph-meta-icon"></i>
                <?php endif; ?>
                <?php if ($meta_item['url']): ?>
                <a href="<?php echo esc_url($meta_item['url']); ?>" class="hph-meta-link">
                    <?php echo esc_html($meta_item['text']); ?>
                </a>
                <?php else: ?>
                <span class="hph-meta-text"><?php echo esc_html($meta_item['text']); ?></span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($buttons)): ?>
        <!-- Card Actions -->
        <div class="hph-card-buttons">
            <?php foreach ($buttons as $button): 
                $btn_defaults = array(
                    'text' => 'Button',
                    'url' => '#',
                    'style' => 'primary',
                    'size' => 'sm',
                    'icon' => '',
                    'icon_position' => 'left',
                    'target' => '_self',
                    'classes' => ''
                );
                $btn = wp_parse_args($button, $btn_defaults);
                
                $btn_classes = array(
                    'hph-btn',
                    'hph-btn-' . $btn['style'],
                    'hph-btn-' . $btn['size'],
                    'hph-card-btn'
                );
                
                if ($btn['classes']) {
                    $btn_classes[] = $btn['classes'];
                }
            ?>
            <a 
                href="<?php echo esc_url($btn['url']); ?>"
                class="<?php echo esc_attr(implode(' ', $btn_classes)); ?>"
                <?php if ($btn['target'] !== '_self'): ?>target="<?php echo esc_attr($btn['target']); ?>"<?php endif; ?>
                <?php if ($link_url): ?>onclick="event.stopPropagation();"<?php endif; ?>
            >
                <?php if ($btn['icon'] && $btn['icon_position'] === 'left'): ?>
                <i class="<?php echo esc_attr($btn['icon']); ?> hph-mr-xs"></i>
                <?php endif; ?>
                <span><?php echo esc_html($btn['text']); ?></span>
                <?php if ($btn['icon'] && $btn['icon_position'] === 'right'): ?>
                <i class="<?php echo esc_attr($btn['icon']); ?> hph-ml-xs"></i>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
    </div>
    
</<?php echo $wrapper_tag; ?>>


<script>
// Auto-play video on hover
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.hph-card');
    
    cards.forEach(card => {
        const video = card.querySelector('.hph-card-video[data-autoplay="hover"]');
        
        if (video) {
            card.addEventListener('mouseenter', () => {
                video.play().catch(() => {
                    // Autoplay might be blocked
                });
            });
            
            card.addEventListener('mouseleave', () => {
                video.pause();
                video.currentTime = 0;
            });
        }
        
        // Handle favorite and compare buttons
        const favoriteBtn = card.querySelector('.hph-action-favorite');
        const compareBtn = card.querySelector('.hph-action-compare');
        
        if (favoriteBtn) {
            favoriteBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.toggle('active');
                const icon = this.querySelector('i');
                icon.classList.toggle('far');
                icon.classList.toggle('fas');
                
                // Trigger custom event
                const event = new CustomEvent('hph:favorite-toggle', {
                    detail: { listingId: this.dataset.listingId }
                });
                document.dispatchEvent(event);
            });
        }
        
        if (compareBtn) {
            compareBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.toggle('active');
                
                // Trigger custom event
                const event = new CustomEvent('hph:compare-toggle', {
                    detail: { listingId: this.dataset.listingId }
                });
                document.dispatchEvent(event);
            });
        }
    });
});
</script>