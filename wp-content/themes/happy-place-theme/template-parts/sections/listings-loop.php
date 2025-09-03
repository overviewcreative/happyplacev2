<?php
/**
 * HPH Listings Loop Section Template
 * Display property listings in various layouts
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 * 
 * Args:
 * - layout: 'grid' | 'list' | 'map-split' | 'featured' | 'masonry' | 'carousel'
 * - columns: 2 | 3 | 4 (for grid layout)
 * - background: 'white' | 'light' | 'dark' | 'primary' | 'gradient'
 * - padding: 'sm' | 'md' | 'lg' | 'xl' | '2xl'
 * - content_width: 'narrow' | 'normal' | 'wide' | 'full'
 * - headline: string
 * - subheadline: string
 * - listings: array of listing data (or WP_Query args)
 * - show_price: boolean
 * - show_status: boolean
 * - show_details: boolean
 * - show_agent: boolean
 * - show_favorite: boolean
 * - show_compare: boolean
 * - image_aspect: 'square' | '4:3' | '16:9'
 * - animation: boolean
 * - filters: boolean
 * - section_id: string
 */

// Register this template part
if (function_exists('hph_register_template_part')) {
    hph_register_template_part('sections/listings-loop');
}

// Early safety check - if we're in admin or doing AJAX, skip expensive operations
if (is_admin() || wp_doing_ajax()) {
    return;
}

// Default arguments
$defaults = array(
    'layout' => 'grid',
    'columns' => 3,
    'background' => 'light',
    'padding' => 'xl',
    'content_width' => 'normal',
    'headline' => 'Featured Properties',
    'subheadline' => '',
    'listings' => array(),
    'show_price' => true,
    'show_status' => true,
    'show_details' => true,
    'show_agent' => false,
    'show_favorite' => true,
    'show_compare' => false,
    'image_aspect' => '4:3',
    'animation' => false,
    'filters' => false,
    'section_id' => '',
    'query_args' => array()
);

$config = wp_parse_args($args ?? array(), $defaults);
extract($config);

// Debug flag - set to true to see what's happening
$debug_listings = defined('WP_DEBUG') && WP_DEBUG;

// Safety: Limit processing time and queries
$max_listings = 12; // Hard limit
$start_time = microtime(true);
$max_execution_time = 2; // seconds

if ($debug_listings) {
    error_log('Listings Loop: Starting template processing');
}

// Get listings data using bridge functions
if (empty($listings) && !empty($query_args)) {
    // Use WP_Query if query args provided
    $listing_query = new WP_Query($query_args);
    $listings = array();
    
    if ($listing_query->have_posts()) {
        while ($listing_query->have_posts()) {
            $listing_query->the_post();
            $listing_id = get_the_ID();
            
            // Use bridge functions with null handling
            if (function_exists('hpt_get_listing')) {
                $listing_data = hpt_get_listing($listing_id);
                if ($listing_data) {
                    $listings[] = $listing_data;
                }
            }
        }
        wp_reset_postdata();
    }
} elseif (empty($listings)) {
    // Check if listing post type exists before querying
    if (post_type_exists('listing')) {
        // Try to get featured listings first
        $featured_query_args = array(
            'post_type' => 'listing',
            'post_status' => 'publish',
            'posts_per_page' => min(6, $max_listings),
            'no_found_rows' => true, // Improve performance
            'update_post_term_cache' => false, // Improve performance
            'meta_query' => array(
                array(
                    'key' => 'is_featured',
                    'value' => '1',
                    'compare' => '='
                )
            )
        );
        
        if ($debug_listings) {
            error_log('Listings Loop: Querying featured listings');
        }
        
        $featured_query = new WP_Query($featured_query_args);
        $listings = array();
        
        if ($featured_query->have_posts()) {
            $count = 0;
            while ($featured_query->have_posts() && $count < $max_listings) {
                // Check execution time
                if ((microtime(true) - $start_time) > $max_execution_time) {
                    if ($debug_listings) {
                        error_log('Listings Loop: Execution time exceeded, breaking');
                    }
                    break;
                }
                
                $featured_query->the_post();
                $listing_id = get_the_ID();
                
                if (function_exists('hpt_get_listing')) {
                    $listing_data = hpt_get_listing($listing_id);
                    if ($listing_data) {
                        $listings[] = $listing_data;
                        $count++;
                    }
                }
            }
            wp_reset_postdata();
        }
        
        // If no featured listings, get recent listings
        if (empty($listings) && (microtime(true) - $start_time) < $max_execution_time) {
            $recent_query_args = array(
                'post_type' => 'listing',
                'post_status' => 'publish',
                'posts_per_page' => min(6, $max_listings),
                'no_found_rows' => true,
                'update_post_term_cache' => false,
                'orderby' => 'date',
                'order' => 'DESC'
            );
            
            if ($debug_listings) {
                error_log('Listings Loop: Querying recent listings');
            }
            
            $recent_query = new WP_Query($recent_query_args);
            
            if ($recent_query->have_posts()) {
                $count = 0;
                while ($recent_query->have_posts() && $count < $max_listings) {
                    // Check execution time
                    if ((microtime(true) - $start_time) > $max_execution_time) {
                        if ($debug_listings) {
                            error_log('Listings Loop: Execution time exceeded in recent query');
                        }
                        break;
                    }
                    
                    $recent_query->the_post();
                    $listing_id = get_the_ID();
                    
                    if (function_exists('hpt_get_listing')) {
                        $listing_data = hpt_get_listing($listing_id);
                        if ($listing_data) {
                            $listings[] = $listing_data;
                            $count++;
                        }
                    }
                }
                wp_reset_postdata();
            }
        }
    }
    
    // Fallback to demo data if no listings found or post type doesn't exist
    if (empty($listings)) {
        $listings = array(
            array(
                'id' => 0,
                'title' => 'Luxury Oceanfront Villa',
                'address' => '123 Beach Drive, Miami Beach, FL',
                'price' => 2850000,
                'listing_status' => 'For Sale',
                'property_type' => 'House',
                'bedrooms' => 5,
                'bathrooms' => 4.5,
                'square_feet' => 4200,
                'featured_image' => array(
                    'url' => get_template_directory_uri() . '/assets/images/listing-1.jpg',
                    'alt' => 'Luxury Oceanfront Villa'
                ),
                'is_featured' => true,
                'url' => '#'
            ),
            array(
                'id' => 0,
                'title' => 'Modern Downtown Condo',
                'address' => '456 City Plaza, Downtown, NY',
                'price' => 875000,
                'listing_status' => 'For Sale',
                'property_type' => 'Condo',
                'bedrooms' => 2,
                'bathrooms' => 2,
                'square_feet' => 1450,
                'featured_image' => array(
                    'url' => get_template_directory_uri() . '/assets/images/listing-2.jpg',
                    'alt' => 'Modern Downtown Condo'
                ),
                'is_featured' => false,
                'url' => '#'
            ),
            array(
                'id' => 0,
                'title' => 'Suburban Family Home',
                'address' => '789 Oak Street, Westfield, NJ',
                'price' => 650000,
                'listing_status' => 'Pending',
                'property_type' => 'House',
                'bedrooms' => 4,
                'bathrooms' => 3,
                'square_feet' => 2800,
                'featured_image' => array(
                    'url' => get_template_directory_uri() . '/assets/images/listing-3.jpg',
                    'alt' => 'Suburban Family Home'
                ),
                'is_featured' => false,
                'url' => '#'
            ),
            array(
                'id' => 0,
                'title' => 'Historic Townhouse',
                'address' => '321 Heritage Lane, Boston, MA',
                'price' => 1250000,
                'listing_status' => 'For Sale',
                'property_type' => 'Townhouse',
                'bedrooms' => 3,
                'bathrooms' => 2.5,
                'square_feet' => 2200,
                'featured_image' => array(
                    'url' => get_template_directory_uri() . '/assets/images/listing-4.jpg',
                    'alt' => 'Historic Townhouse'
                ),
                'is_featured' => false,
                'url' => '#'
            )
        );
    }
}

// Final safety check and debug logging
if ($debug_listings) {
    error_log('Listings Loop: Final listings count: ' . count($listings));
    error_log('Listings Loop: Execution time: ' . (microtime(true) - $start_time) . ' seconds');
}

// Safety: If we still have no listings and execution time is exceeded, show minimal demo data
if (empty($listings) || count($listings) === 0) {
    $listings = array(
        array(
            'id' => 0,
            'title' => 'Sample Property',
            'address' => 'Location Available',
            'price' => 500000,
            'listing_status' => 'Available',
            'property_type' => 'Home',
            'bedrooms' => 3,
            'bathrooms' => 2,
            'square_feet' => 1500,
            'featured_image' => array(
                'url' => get_template_directory_uri() . '/assets/images/listing-placeholder.jpg',
                'alt' => 'Sample Property'
            ),
            'is_featured' => false,
            'url' => '#'
        )
    );
}

// Section styles
$section_styles = array(
    'position: relative',
    'width: 100%'
);

// Background styles
switch ($background) {
    case 'light':
        $section_styles[] = 'background-color: var(--hph-gray-50)';
        break;
    case 'dark':
        $section_styles[] = 'background-color: var(--hph-gray-900)';
        $section_styles[] = 'color: var(--hph-white)';
        break;
    case 'primary':
        $section_styles[] = 'background-color: var(--hph-primary)';
        $section_styles[] = 'color: var(--hph-white)';
        break;
    case 'gradient':
        $section_styles[] = 'background: var(--hph-gradient-primary)';
        $section_styles[] = 'color: var(--hph-white)';
        break;
    default:
        $section_styles[] = 'background-color: var(--hph-white)';
}

// Padding
switch ($padding) {
    case 'sm':
        $section_styles[] = 'padding: var(--hph-padding-lg) 0';
        break;
    case 'md':
        $section_styles[] = 'padding: var(--hph-padding-xl) 0';
        break;
    case 'lg':
        $section_styles[] = 'padding: var(--hph-padding-2xl) 0';
        break;
    case '2xl':
        $section_styles[] = 'padding: var(--hph-padding-4xl) 0';
        break;
    default:
        $section_styles[] = 'padding: var(--hph-padding-3xl) 0';
}

// Container styles
$container_styles = array(
    'margin: 0 auto',
    'padding: 0 var(--hph-padding-lg)'
);

switch ($content_width) {
    case 'narrow':
        $container_styles[] = 'max-width: var(--hph-container-sm)';
        break;
    case 'wide':
        $container_styles[] = 'max-width: var(--hph-container-2xl)';
        break;
    case 'full':
        $container_styles[] = 'max-width: 100%';
        $container_styles[] = 'padding: 0';
        break;
    default:
        $container_styles[] = 'max-width: var(--hph-container-xl)';
}

// Grid styles
$grid_styles = array();
if ($layout === 'grid' || $layout === 'masonry') {
    $grid_styles[] = 'display: grid';
    $grid_styles[] = 'gap: var(--hph-gap-xl)';
    
    switch ($columns) {
        case 2:
            $grid_styles[] = 'grid-template-columns: repeat(auto-fill, minmax(400px, 1fr))';
            break;
        case 4:
            $grid_styles[] = 'grid-template-columns: repeat(auto-fill, minmax(280px, 1fr))';
            break;
        default:
            $grid_styles[] = 'grid-template-columns: repeat(auto-fill, minmax(350px, 1fr))';
    }
} elseif ($layout === 'list') {
    $grid_styles[] = 'display: flex';
    $grid_styles[] = 'flex-direction: column';
    $grid_styles[] = 'gap: var(--hph-gap-xl)';
} elseif ($layout === 'featured') {
    $grid_styles[] = 'display: grid';
    $grid_styles[] = 'grid-template-columns: 3fr 2fr';
    $grid_styles[] = 'gap: var(--hph-gap-xl)';
}

// Image aspect ratio
$aspect_style = '';
switch ($image_aspect) {
    case 'square':
        $aspect_style = 'aspect-ratio: 1';
        break;
    case '16:9':
        $aspect_style = 'aspect-ratio: 16/9';
        break;
    default:
        $aspect_style = 'aspect-ratio: 4/3';
}

// Helper function to format price with null handling
function hph_format_listing_price($price) {
    if (empty($price) || !is_numeric($price)) {
        return 'Price on Request';
    }
    
    if ($price >= 1000000) {
        return '$' . number_format($price / 1000000, 2) . 'M';
    } else {
        return '$' . number_format($price);
    }
}

// Status badge colors with null handling
function hph_get_listing_status_color($status) {
    if (empty($status)) {
        return 'var(--hph-gray-500)';
    }
    
    switch (strtolower($status)) {
        case 'for sale':
        case 'active':
            return 'var(--hph-success)';
        case 'pending':
        case 'under contract':
            return 'var(--hph-warning)';
        case 'sold':
        case 'closed':
            return 'var(--hph-danger)';
        default:
            return 'var(--hph-primary)';
    }
}

// Helper to get listing image with fallbacks
function hph_get_listing_image($listing, $size = 'large') {
    // Try bridge function first
    if (function_exists('hpt_get_listing_featured_image') && !empty($listing['id'])) {
        $image = hpt_get_listing_featured_image($listing['id'], $size);
        if (!empty($image['url'])) {
            return $image;
        }
    }
    
    // Try direct featured_image data
    if (!empty($listing['featured_image'])) {
        if (is_array($listing['featured_image'])) {
            return $listing['featured_image'];
        } elseif (is_string($listing['featured_image'])) {
            return array(
                'url' => $listing['featured_image'],
                'alt' => $listing['title'] ?? 'Property Image'
            );
        }
    }
    
    // Try image helper function with encoding
    if (function_exists('hph_get_image_url') && !empty($listing['featured_image'])) {
        $encoded_url = hph_get_image_url($listing['featured_image']);
        if (!empty($encoded_url)) {
            return array(
                'url' => $encoded_url,
                'alt' => $listing['title'] ?? 'Property Image'
            );
        }
    }
    
    // Final fallback
    return array(
        'url' => get_template_directory_uri() . '/assets/images/listing-placeholder.jpg',
        'alt' => 'Property Image Placeholder'
    );
}

// Color scheme for text on background
$is_dark_bg = in_array($background, ['dark', 'primary', 'gradient']);
$heading_color = $is_dark_bg ? 'var(--hph-white)' : 'var(--hph-primary-800)';
$text_color = $is_dark_bg ? 'rgba(255, 255, 255, 0.9)' : 'var(--hph-gray-700)';
?>

<section 
    <?php if ($section_id): ?>id="<?php echo esc_attr($section_id); ?>"<?php endif; ?>
    style="<?php echo implode('; ', $section_styles); ?>"
    data-bg="<?php echo esc_attr($background); ?>"
>
    <div style="<?php echo implode('; ', $container_styles); ?>">
        
        <?php if ($headline || $subheadline): ?>
        <div style="text-align: center; margin-bottom: var(--hph-margin-3xl); <?php echo $animation ? 'animation: fadeInUp 0.8s ease-out;' : ''; ?>">
            <?php if ($headline): ?>
            <h2 style="margin: 0 0 var(--hph-margin-lg) 0; font-size: var(--hph-text-4xl); font-weight: var(--hph-font-bold); color: <?php echo $heading_color; ?>;">
                <?php echo esc_html($headline); ?>
            </h2>
            <?php endif; ?>
            
            <?php if ($subheadline): ?>
            <p style="font-size: var(--hph-text-xl); color: <?php echo $text_color; ?>; max-width: 600px; margin: 0 auto;">
                <?php echo esc_html($subheadline); ?>
            </p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($filters): ?>
        <!-- Filters Bar -->
        <div style="display: flex; gap: var(--hph-gap-md); margin-bottom: var(--hph-margin-2xl); padding: var(--hph-padding-lg); background: var(--hph-white); border-radius: var(--hph-radius-lg); box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
            <select style="flex: 1; padding: var(--hph-padding-sm) var(--hph-padding-md); border: 1px solid var(--hph-gray-200); border-radius: var(--hph-radius-md); background: var(--hph-white); color: var(--hph-gray-700);">
                <option>All Types</option>
                <option>House</option>
                <option>Condo</option>
                <option>Townhouse</option>
            </select>
            
            <select style="flex: 1; padding: var(--hph-padding-sm) var(--hph-padding-md); border: 1px solid var(--hph-gray-200); border-radius: var(--hph-radius-md); background: var(--hph-white); color: var(--hph-gray-700);">
                <option>Any Price</option>
                <option>Under $500k</option>
                <option>$500k - $1M</option>
                <option>$1M - $2M</option>
                <option>Over $2M</option>
            </select>
            
            <select style="flex: 1; padding: var(--hph-padding-sm) var(--hph-padding-md); border: 1px solid var(--hph-gray-200); border-radius: var(--hph-radius-md); background: var(--hph-white); color: var(--hph-gray-700);">
                <option>Any Beds</option>
                <option>1+</option>
                <option>2+</option>
                <option>3+</option>
                <option>4+</option>
            </select>
            
            <button style="padding: var(--hph-padding-sm) var(--hph-padding-xl); background: var(--hph-primary); color: var(--hph-white); border: none; border-radius: var(--hph-radius-md); font-weight: var(--hph-font-medium); cursor: pointer;">
                Search
            </button>
        </div>
        <?php endif; ?>
        
        <div style="<?php echo implode('; ', $grid_styles); ?>">
            
            <?php if ($layout === 'featured'): 
                // Get featured listing
                $featured = null;
                $regular = array();
                foreach ($listings as $listing) {
                    if (!empty($listing['is_featured']) && !$featured) {
                        $featured = $listing;
                    } else {
                        $regular[] = $listing;
                    }
                }
                if (!$featured && count($listings) > 0) {
                    $featured = $listings[0];
                    $regular = array_slice($listings, 1);
                }
            ?>
            
            <!-- Featured Listing -->
            <?php if ($featured): 
                $featured_image = hph_get_listing_image($featured, 'large');
            ?>
            <div style="grid-row: span 2; position: relative; border-radius: var(--hph-radius-xl); overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.1); <?php echo $animation ? 'animation: fadeInUp 0.8s ease-out;' : ''; ?>">
                <div style="position: relative; height: 100%; min-height: 600px;">
                    <img src="<?php echo esc_url($featured_image['url']); ?>" 
                         alt="<?php echo esc_attr($featured_image['alt']); ?>" 
                         style="width: 100%; height: 100%; object-fit: cover;"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    
                    <!-- Image Error Fallback -->
                    <div style="display: none; width: 100%; height: 100%; background: var(--hph-gray-100); align-items: center; justify-content: center; color: var(--hph-gray-500);">
                        <svg style="width: 64px; height: 64px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    
                    <!-- Overlay Gradient -->
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 60%; background: var(--hph-gradient-card-overlay);"></div>
                    
                    <!-- Content -->
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; padding: var(--hph-padding-2xl); color: var(--hph-white);">
                        <?php if ($show_status && !empty($featured['listing_status'])): ?>
                        <span style="display: inline-block; padding: var(--hph-padding-xs) var(--hph-padding-sm); background: <?php echo hph_get_listing_status_color($featured['listing_status']); ?>; color: var(--hph-white); border-radius: var(--hph-radius-md); font-size: var(--hph-text-sm); font-weight: var(--hph-font-semibold); margin-bottom: var(--hph-margin-md);">
                            <?php echo esc_html($featured['listing_status']); ?>
                        </span>
                        <?php endif; ?>
                        
                        <h3 style="margin: 0 0 var(--hph-margin-sm) 0; font-size: var(--hph-text-3xl); font-weight: var(--hph-font-bold);">
                            <?php echo esc_html($featured['title'] ?? 'Featured Property'); ?>
                        </h3>
                        
                        <p style="margin: 0 0 var(--hph-margin-lg) 0; opacity: 0.9;">
                            <?php echo esc_html($featured['address'] ?? 'Location Available Upon Request'); ?>
                        </p>
                        
                        <?php if ($show_price): ?>
                        <div style="font-size: var(--hph-text-4xl); font-weight: var(--hph-font-bold); margin-bottom: var(--hph-margin-lg);">
                            <?php echo hph_format_listing_price($featured['price'] ?? null); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($show_details): ?>
                        <div style="display: flex; gap: var(--hph-gap-lg); font-size: var(--hph-text-sm);">
                            <?php if (!empty($featured['bedrooms'])): ?>
                            <span><?php echo esc_html($featured['bedrooms']); ?> Beds</span>
                            <?php endif; ?>
                            <?php if (!empty($featured['bathrooms'])): ?>
                            <span><?php echo esc_html($featured['bathrooms']); ?> Baths</span>
                            <?php endif; ?>
                            <?php if (!empty($featured['square_feet'])): ?>
                            <span><?php echo number_format($featured['square_feet']); ?> Sq Ft</span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Favorite Button -->
                    <?php if ($show_favorite): ?>
                    <button style="position: absolute; top: var(--hph-padding-lg); right: var(--hph-padding-lg); width: 40px; height: 40px; border-radius: var(--hph-radius-full); background: rgba(255,255,255,0.9); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 300ms ease;"
                            onmouseover="this.style.background='var(--hph-white)'; this.style.transform='scale(1.1)';"
                            onmouseout="this.style.background='rgba(255,255,255,0.9)'; this.style.transform='scale(1)';">
                        <svg style="width: 20px; height: 20px; color: var(--hph-danger);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Regular Listings Grid -->
            <div style="display: grid; gap: var(--hph-gap-lg);">
                <?php foreach ($regular as $index => $listing): 
                    $listing_image = hph_get_listing_image($listing, 'medium');
                ?>
                <div style="background: var(--hph-white); border-radius: var(--hph-radius-lg); overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 300ms ease; <?php echo $animation ? 'animation: fadeInUp 0.8s ease-out ' . (($index + 1) * 0.1) . 's; opacity: 0; animation-fill-mode: forwards;' : ''; ?>"
                     onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 24px rgba(0,0,0,0.12)';"
                     onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)';">
                    
                    <!-- Image -->
                    <div style="position: relative; <?php echo $aspect_style; ?>; overflow: hidden;">
                        <img src="<?php echo esc_url($listing_image['url']); ?>" 
                             alt="<?php echo esc_attr($listing_image['alt']); ?>" 
                             style="width: 100%; height: 100%; object-fit: cover;"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        
                        <!-- Image Error Fallback -->
                        <div style="display: none; width: 100%; height: 100%; background: var(--hph-gray-100); align-items: center; justify-content: center; color: var(--hph-gray-500);">
                            <svg style="width: 48px; height: 48px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        
                        <?php if ($show_status && !empty($listing['listing_status'])): ?>
                        <span style="position: absolute; top: var(--hph-padding-md); left: var(--hph-padding-md); padding: var(--hph-padding-xs) var(--hph-padding-sm); background: <?php echo hph_get_listing_status_color($listing['listing_status']); ?>; color: var(--hph-white); border-radius: var(--hph-radius-md); font-size: var(--hph-text-xs); font-weight: var(--hph-font-semibold);">
                            <?php echo esc_html($listing['listing_status']); ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Content -->
                    <div style="padding: var(--hph-padding-lg);">
                        <?php if ($show_price): ?>
                        <div style="font-size: var(--hph-text-2xl); font-weight: var(--hph-font-bold); color: var(--hph-primary); margin-bottom: var(--hph-margin-sm);">
                            <?php echo hph_format_listing_price($listing['price'] ?? null); ?>
                        </div>
                        <?php endif; ?>
                        
                        <h4 style="margin: 0 0 var(--hph-margin-xs) 0; font-size: var(--hph-text-lg); font-weight: var(--hph-font-semibold); color: var(--hph-gray-900);">
                            <?php echo esc_html($listing['title'] ?? 'Property Listing'); ?>
                        </h4>
                        
                        <p style="margin: 0 0 var(--hph-margin-md) 0; color: var(--hph-gray-600); font-size: var(--hph-text-sm);">
                            <?php echo esc_html($listing['address'] ?? 'Location Available Upon Request'); ?>
                        </p>
                        
                        <?php if ($show_details): ?>
                        <div style="display: flex; gap: var(--hph-gap-md); padding-top: var(--hph-padding-md); border-top: 1px solid var(--hph-gray-100); font-size: var(--hph-text-sm); color: var(--hph-gray-600);">
                            <?php if (!empty($listing['bedrooms'])): ?>
                            <span><?php echo esc_html($listing['bedrooms']); ?> Beds</span>
                            <?php endif; ?>
                            <?php if (!empty($listing['bathrooms'])): ?>
                            <span><?php echo esc_html($listing['bathrooms']); ?> Baths</span>
                            <?php endif; ?>
                            <?php if (!empty($listing['square_feet'])): ?>
                            <span><?php echo number_format($listing['square_feet']); ?> Sq Ft</span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <?php elseif ($layout === 'list'): ?>
            <!-- List Layout -->
            <?php foreach ($listings as $index => $listing): 
                $listing_image = hph_get_listing_image($listing, 'medium');
            ?>
            <div style="display: flex; background: var(--hph-white); border-radius: var(--hph-radius-lg); overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 300ms ease; <?php echo $animation ? 'animation: fadeInUp 0.8s ease-out ' . ($index * 0.1) . 's; opacity: 0; animation-fill-mode: forwards;' : ''; ?>"
                 onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 24px rgba(0,0,0,0.12)';"
                 onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)';">
                
                <!-- Image -->
                <div style="width: 300px; position: relative;">
                    <img src="<?php echo esc_url($listing_image['url']); ?>" 
                         alt="<?php echo esc_attr($listing_image['alt']); ?>" 
                         style="width: 100%; height: 100%; object-fit: cover;"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    
                    <!-- Image Error Fallback -->
                    <div style="display: none; width: 100%; height: 100%; background: var(--hph-gray-100); align-items: center; justify-content: center; color: var(--hph-gray-500);">
                        <svg style="width: 48px; height: 48px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    
                    <?php if ($show_status && !empty($listing['listing_status'])): ?>
                    <span style="position: absolute; top: var(--hph-padding-md); left: var(--hph-padding-md); padding: var(--hph-padding-xs) var(--hph-padding-sm); background: <?php echo hph_get_listing_status_color($listing['listing_status']); ?>; color: var(--hph-white); border-radius: var(--hph-radius-md); font-size: var(--hph-text-xs); font-weight: var(--hph-font-semibold);">
                        <?php echo esc_html($listing['listing_status']); ?>
                    </span>
                    <?php endif; ?>
                    
                    <?php if ($show_favorite): ?>
                    <button style="position: absolute; top: var(--hph-padding-md); right: var(--hph-padding-md); width: 32px; height: 32px; border-radius: var(--hph-radius-full); background: rgba(255,255,255,0.9); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                        <svg style="width: 16px; height: 16px; color: var(--hph-gray-600);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </button>
                    <?php endif; ?>
                </div>
                
                <!-- Content -->
                <div style="flex: 1; padding: var(--hph-padding-xl); display: flex; flex-direction: column;">
                    <div style="flex: 1;">
                        <?php if ($show_price): ?>
                        <div style="font-size: var(--hph-text-3xl); font-weight: var(--hph-font-bold); color: var(--hph-primary); margin-bottom: var(--hph-margin-md);">
                            <?php echo hph_format_listing_price($listing['price'] ?? null); ?>
                        </div>
                        <?php endif; ?>
                        
                        <h3 style="margin: 0 0 var(--hph-margin-sm) 0; font-size: var(--hph-text-xl); font-weight: var(--hph-font-semibold); color: var(--hph-gray-900);">
                            <?php echo esc_html($listing['title'] ?? 'Property Listing'); ?>
                        </h3>
                        
                        <p style="margin: 0 0 var(--hph-margin-lg) 0; color: var(--hph-gray-600);">
                            <?php echo esc_html($listing['address'] ?? 'Location Available Upon Request'); ?>
                        </p>
                        
                        <?php if ($show_details): ?>
                        <div style="display: flex; gap: var(--hph-gap-xl); margin-bottom: var(--hph-margin-lg);">
                            <?php if (!empty($listing['bedrooms'])): ?>
                            <div style="display: flex; align-items: center; gap: var(--hph-gap-sm);">
                                <svg style="width: 20px; height: 20px; color: var(--hph-gray-400);" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                </svg>
                                <span style="color: var(--hph-gray-700);"><?php echo esc_html($listing['bedrooms']); ?> Beds</span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($listing['bathrooms'])): ?>
                            <div style="display: flex; align-items: center; gap: var(--hph-gap-sm);">
                                <svg style="width: 20px; height: 20px; color: var(--hph-gray-400);" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L13 10.414V17a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6.586L5.293 6.707A1 1 0 015 6V3z" clip-rule="evenodd"></path>
                                </svg>
                                <span style="color: var(--hph-gray-700);"><?php echo esc_html($listing['bathrooms']); ?> Baths</span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($listing['square_feet'])): ?>
                            <div style="display: flex; align-items: center; gap: var(--hph-gap-sm);">
                                <svg style="width: 20px; height: 20px; color: var(--hph-gray-400);" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 3a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2H5zm0 2h10v10H5V5z"></path>
                                </svg>
                                <span style="color: var(--hph-gray-700);"><?php echo number_format($listing['square_feet']); ?> Sq Ft</span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Actions -->
                    <div style="display: flex; gap: var(--hph-gap-md); align-items: center;">
                        <a href="<?php echo esc_url($listing['url'] ?? '#'); ?>" 
                           style="flex: 1; padding: var(--hph-padding-sm) var(--hph-padding-lg); background: var(--hph-primary); color: var(--hph-white); text-decoration: none; border-radius: var(--hph-radius-md); font-weight: var(--hph-font-medium); text-align: center; transition: all 300ms ease;"
                           onmouseover="this.style.background='var(--hph-primary-600)';"
                           onmouseout="this.style.background='var(--hph-primary)';">
                            View Details
                        </a>
                        
                        <?php if ($show_compare): ?>
                        <button style="padding: var(--hph-padding-sm) var(--hph-padding-lg); background: transparent; color: var(--hph-primary); border: 1px solid var(--hph-primary); border-radius: var(--hph-radius-md); font-weight: var(--hph-font-medium); cursor: pointer;">
                            Compare
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php else: ?>
            <!-- Grid Layout (Default) -->
            <?php foreach ($listings as $index => $listing): 
                $listing_image = hph_get_listing_image($listing, 'large');
            ?>
            <div style="position: relative; background: var(--hph-white); border-radius: var(--hph-radius-lg); overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 300ms ease; <?php echo $animation ? 'animation: fadeInUp 0.8s ease-out ' . ($index * 0.1) . 's; opacity: 0; animation-fill-mode: forwards;' : ''; ?>"
                 onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 24px rgba(0,0,0,0.12)';"
                 onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)';">
                
                <!-- Image Container -->
                <a href="<?php echo esc_url($listing['url'] ?? '#'); ?>" style="display: block; position: relative; <?php echo $aspect_style; ?>; overflow: hidden;">
                    <img src="<?php echo esc_url($listing_image['url']); ?>" 
                         alt="<?php echo esc_attr($listing_image['alt']); ?>" 
                         style="width: 100%; height: 100%; object-fit: cover; transition: transform 300ms ease;"
                         onmouseover="this.style.transform='scale(1.05)'"
                         onmouseout="this.style.transform='scale(1)'"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    
                    <!-- Image Error Fallback -->
                    <div style="display: none; width: 100%; height: 100%; background: var(--hph-gray-100); align-items: center; justify-content: center; color: var(--hph-gray-500);">
                        <svg style="width: 48px; height: 48px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    
                    <!-- Status Badge -->
                    <?php if ($show_status && !empty($listing['listing_status'])): ?>
                    <span style="position: absolute; top: var(--hph-padding-md); left: var(--hph-padding-md); padding: var(--hph-padding-xs) var(--hph-padding-sm); background: <?php echo hph_get_listing_status_color($listing['listing_status']); ?>; color: var(--hph-white); border-radius: var(--hph-radius-md); font-size: var(--hph-text-xs); font-weight: var(--hph-font-semibold); z-index: 2;">
                        <?php echo esc_html($listing['listing_status']); ?>
                    </span>
                    <?php endif; ?>
                    
                    <!-- Favorite Button -->
                    <?php if ($show_favorite): ?>
                    <button style="position: absolute; top: var(--hph-padding-md); right: var(--hph-padding-md); width: 36px; height: 36px; border-radius: var(--hph-radius-full); background: rgba(255,255,255,0.9); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 300ms ease; z-index: 2;"
                            onclick="event.preventDefault(); event.stopPropagation(); toggleFavorite(this);"
                            onmouseover="this.style.background='var(--hph-white)'; this.style.transform='scale(1.1)';"
                            onmouseout="this.style.background='rgba(255,255,255,0.9)'; this.style.transform='scale(1)';">
                        <svg style="width: 18px; height: 18px; color: var(--hph-gray-600);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </button>
                    <?php endif; ?>
                    
                    <!-- Compare Checkbox -->
                    <?php if ($show_compare): ?>
                    <div style="position: absolute; bottom: var(--hph-padding-md); right: var(--hph-padding-md); z-index: 2;">
                        <label style="display: flex; align-items: center; padding: var(--hph-padding-xs) var(--hph-padding-sm); background: rgba(255,255,255,0.9); border-radius: var(--hph-radius-md); cursor: pointer;">
                            <input type="checkbox" style="margin-right: var(--hph-margin-xs);">
                            <span style="font-size: var(--hph-text-xs); color: var(--hph-gray-700);">Compare</span>
                        </label>
                    </div>
                    <?php endif; ?>
                </a>
                
                <!-- Content -->
                <div style="padding: var(--hph-padding-lg);">
                    <?php if ($show_price): ?>
                    <div style="font-size: var(--hph-text-2xl); font-weight: var(--hph-font-bold); color: var(--hph-primary); margin-bottom: var(--hph-margin-sm);">
                        <?php echo hph_format_listing_price($listing['price'] ?? null); ?>
                    </div>
                    <?php endif; ?>
                    
                    <h3 style="margin: 0 0 var(--hph-margin-xs) 0;">
                        <a href="<?php echo esc_url($listing['url'] ?? '#'); ?>" 
                           style="font-size: var(--hph-text-lg); font-weight: var(--hph-font-semibold); color: var(--hph-gray-900); text-decoration: none; transition: color 300ms ease;"
                           onmouseover="this.style.color='var(--hph-primary)'"
                           onmouseout="this.style.color='var(--hph-gray-900)'">
                            <?php echo esc_html($listing['title'] ?? 'Property Listing'); ?>
                        </a>
                    </h3>
                    
                    <p style="margin: 0 0 var(--hph-margin-lg) 0; color: var(--hph-gray-600); font-size: var(--hph-text-sm);">
                        <svg style="width: 14px; height: 14px; display: inline-block; margin-right: 4px; vertical-align: text-bottom;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                        </svg>
                        <?php echo esc_html($listing['address'] ?? 'Location Available Upon Request'); ?>
                    </p>
                    
                    <?php if ($show_details): ?>
                    <div style="display: flex; gap: var(--hph-gap-md); padding-top: var(--hph-padding-md); border-top: 1px solid var(--hph-gray-100); font-size: var(--hph-text-sm); color: var(--hph-gray-600);">
                        <?php if (!empty($listing['bedrooms'])): ?>
                        <div style="display: flex; align-items: center; gap: 4px;">
                            <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            <span><?php echo esc_html($listing['bedrooms']); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($listing['bathrooms'])): ?>
                        <div style="display: flex; align-items: center; gap: 4px;">
                            <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h8m-4 0v10m-6-6h12"></path>
                            </svg>
                            <span><?php echo esc_html($listing['bathrooms']); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($listing['square_feet'])): ?>
                        <div style="display: flex; align-items: center; gap: 4px;">
                            <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                            </svg>
                            <span><?php echo number_format($listing['square_feet']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($show_agent && !empty($listing['agent'])): ?>
                    <div style="display: flex; align-items: center; gap: var(--hph-gap-sm); margin-top: var(--hph-padding-md); padding-top: var(--hph-padding-md); border-top: 1px solid var(--hph-gray-100);">
                        <?php 
                        $agent_data = $listing['agent'];
                        $agent_photo_url = '';
                        
                        // Try bridge function for agent photo
                        if (function_exists('hpt_get_agent_photo') && !empty($agent_data['id'])) {
                            $agent_photo = hpt_get_agent_photo($agent_data['id']);
                            if (!empty($agent_photo['url'])) {
                                $agent_photo_url = $agent_photo['url'];
                            }
                        } elseif (!empty($agent_data['image'])) {
                            $agent_photo_url = is_array($agent_data['image']) ? $agent_data['image']['url'] : $agent_data['image'];
                        }
                        ?>
                        
                        <?php if ($agent_photo_url): ?>
                        <img src="<?php echo esc_url($agent_photo_url); ?>" 
                             alt="<?php echo esc_attr($agent_data['name'] ?? 'Agent'); ?>"
                             style="width: 32px; height: 32px; border-radius: var(--hph-radius-full); object-fit: cover;"
                             onerror="this.style.display='none';">
                        <?php endif; ?>
                        <div style="font-size: var(--hph-text-xs); color: var(--hph-gray-600);">
                            Listed by <strong style="color: var(--hph-gray-700);"><?php echo esc_html($agent_data['name'] ?? 'Real Estate Agent'); ?></strong>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
            
        </div>
        
        <!-- Load More Button -->
        <?php if (count($listings) >= 6): ?>
        <div style="text-align: center; margin-top: var(--hph-margin-3xl);">
            <button style="padding: var(--hph-padding-md) var(--hph-padding-2xl); background: var(--hph-white); color: var(--hph-primary); border: 2px solid var(--hph-primary); border-radius: var(--hph-radius-lg); font-size: var(--hph-text-base); font-weight: var(--hph-font-semibold); cursor: pointer; transition: all 300ms ease;"
                    onmouseover="this.style.background='var(--hph-primary)'; this.style.color='var(--hph-white)';"
                    onmouseout="this.style.background='var(--hph-white)'; this.style.color='var(--hph-primary)';">
                Load More Properties
            </button>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
function toggleFavorite(button) {
    const svg = button.querySelector('svg');
    const isFavorited = svg.getAttribute('fill') === 'currentColor';
    
    if (isFavorited) {
        svg.setAttribute('fill', 'none');
        svg.style.color = 'var(--hph-gray-600)';
    } else {
        svg.setAttribute('fill', 'currentColor');
        svg.style.color = 'var(--hph-danger)';
    }
}
</script>

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
