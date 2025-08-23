<?php
/**
 * Single Listing Template
 * 
 * Modern single listing template using bridge functions and component system
 * with comprehensive null handling and graceful degradation
 *
 * @package HappyPlaceTheme
 * @since 3.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Get listing ID and verify it exists
$listing_id = get_the_ID();
if (!$listing_id || get_post_type($listing_id) !== 'listing') {
    get_template_part('template-parts/content', 'none');
    get_footer();
    return;
}

// Verify listing exists using bridge functions with fallback
$listing_data = null;
if (function_exists('hpt_get_listing')) {
    $listing_data = hpt_get_listing($listing_id);
} else {
    $post = get_post($listing_id);
    if ($post && $post->post_type === 'listing') {
        $listing_data = array('id' => $listing_id);
    }
}

// Check if listing is viewable
if (!$listing_data || (get_post_status($listing_id) !== 'publish' && !current_user_can('edit_posts'))) {
    get_template_part('template-parts/content', 'none');
    get_footer();
    return;
}

// Get basic listing info with null safety
$listing_title = get_the_title($listing_id) ?: __('Property Listing', 'happy-place-theme');
$listing_status = function_exists('hpt_get_listing_status') ? hpt_get_listing_status($listing_id) : 'active';

// Check if listing is available for viewing
if ($listing_status === 'sold' || $listing_status === 'off_market') {
    $show_contact_forms = false;
} else {
    $show_contact_forms = true;
}
?>

<main class="hph-main hph-single-listing" id="main" role="main">

    <!-- Property Hero Section -->
    <?php
    // Only show hero if we have images or basic data
    $has_images = function_exists('hpt_get_listing_gallery') ? 
                  !empty(hpt_get_listing_gallery($listing_id)) : 
                  has_post_thumbnail($listing_id);
    
    if ($has_images || $listing_data) :
        get_template_part('template-parts/components/listing-hero', null, array(
            'listing_id' => $listing_id,
            'style' => 'slider',
            'height' => 'xl',
            'show_gallery_nav' => true,
            'show_status' => true,
            'show_actions' => $show_contact_forms,
            'overlay' => 'gradient'
        ));
    endif;
    ?>

    <!-- Main Content Container -->
    <div class="hph-container-xl hph-py-xl">
        <div class="hph-space-y-xl">
                
                <!-- Property Overview Details -->
                <?php
                // Always show details - component handles null cases internally
                get_template_part('template-parts/components/listing-details', null, array(
                    'listing_id' => $listing_id,
                    'show_sidebar' => false, // We have a separate sidebar
                    'section_id' => 'property-overview'
                ));
                ?>

                <!-- Photo Gallery -->
                <?php
                // Only show gallery if we have images
                $gallery_images = function_exists('hpt_get_listing_gallery') ? 
                                hpt_get_listing_gallery($listing_id) : 
                                array();
                
                if (!empty($gallery_images) || has_post_thumbnail($listing_id)) :
                    // Check if component exists before loading
                    $gallery_template = locate_template('template-parts/components/listing-photo-gallery.php');
                    if ($gallery_template) :
                        get_template_part('template-parts/components/listing-photo-gallery', null, array(
                            'listing_id' => $listing_id,
                            'style' => 'masonry',
                            'columns' => 3,
                            'show_thumbnails' => true,
                            'show_counter' => true,
                            'lazy_load' => true
                        ));
                    endif;
                endif;
                ?>

                <!-- Virtual Tour -->
                <?php
                // Only show if virtual tour exists
                $virtual_tour = function_exists('hpt_get_listing_virtual_tour') ? 
                              hpt_get_listing_virtual_tour($listing_id) : 
                              get_field('virtual_tour_url', $listing_id);
                
                if ($virtual_tour) :
                    $tour_template = locate_template('template-parts/components/listing-virtual-tour.php');
                    if ($tour_template) :
                        get_template_part('template-parts/components/listing-virtual-tour', null, array(
                            'listing_id' => $listing_id,
                            'style' => 'immersive',
                            'show_navigation' => true,
                            'show_minimap' => true,
                            'show_hotspots' => true
                        ));
                    endif;
                endif;
                ?>

                <!-- Floor Plans -->
                <?php
                // Only show if floor plans exist
                $floor_plans = function_exists('hpt_get_listing_floor_plans') ? 
                             hpt_get_listing_floor_plans($listing_id) : 
                             get_field('floor_plans', $listing_id);
                
                if (!empty($floor_plans) && is_array($floor_plans)) :
                    $floor_plans_template = locate_template('template-parts/components/listing-floor-plans.php');
                    if ($floor_plans_template) :
                        get_template_part('template-parts/components/listing-floor-plans', null, array(
                            'listing_id' => $listing_id,
                            'style' => 'interactive',
                            'show_measurements' => true,
                            'show_room_labels' => true,
                            'zoom_enabled' => true
                        ));
                    endif;
                endif;
                ?>

                <!-- Property Features -->
                <?php
                // Check if features component exists and if we have features
                $features_template = locate_template('template-parts/components/listing-features.php');
                if ($features_template) :
                    $has_features = function_exists('hpt_get_listing_features') ? 
                                  !empty(hpt_get_listing_features($listing_id)) : 
                                  !empty(get_field('features', $listing_id));
                    
                    if ($has_features) :
                        get_template_part('template-parts/components/listing-features', null, array(
                            'listing_id' => $listing_id,
                            'style' => 'magazine',
                            'show_categories' => true,
                            'columns' => 3,
                            'show_icons' => true
                        ));
                    endif;
                endif;
                ?>

                <!-- Location & Map -->
                <?php
                // Only show map if we have coordinates or address
                $has_location = false;
                if (function_exists('hpt_get_listing_coordinates')) {
                    $coords = hpt_get_listing_coordinates($listing_id);
                    $has_location = !empty($coords['lat']) && !empty($coords['lng']);
                }
                
                if (!$has_location && function_exists('hpt_get_listing_address')) {
                    $address = hpt_get_listing_address($listing_id);
                    $has_location = !empty($address);
                }
                
                if (!$has_location) {
                    // Fallback address check
                    $address = get_field('street_address', $listing_id) ?: get_field('address', $listing_id);
                    $has_location = !empty($address);
                }
                
                if ($has_location) :
                    $map_template = locate_template('template-parts/components/listing-map.php');
                    if ($map_template) :
                        get_template_part('template-parts/components/listing-map', null, array(
                            'listing_id' => $listing_id,
                            'style' => 'interactive',
                            'height' => '400px',
                            'show_nearby' => true,
                            'show_schools' => true,
                            'show_commute' => true
                        ));
                    endif;
                endif;
                ?>

                <!-- Similar Listings -->
                <?php
                // Show similar listings if function exists
                if (function_exists('hpt_get_similar_listings')) :
                    $similar_listings = hpt_get_similar_listings($listing_id, 4);
                    if (!empty($similar_listings)) :
                        echo '<section class="hph-similar-listings hph-py-xl">';
                        echo '<div class="hph-container">';
                        echo '<h2 class="hph-text-2xl hph-font-bold hph-mb-lg">' . __('Similar Properties', 'happy-place-theme') . '</h2>';
                        echo '<div class="hph-grid hph-grid-cols-1 hph-md:grid-cols-2 hph-gap-lg">';
                        
                        foreach ($similar_listings as $similar_listing) :
                            get_template_part('template-parts/components/listing-card', null, array(
                                'listing_id' => $similar_listing->ID,
                                'style' => 'default',
                                'show_actions' => true,
                                'show_stats' => true,
                                'show_agent' => false,
                                'show_favorite' => true,
                                'compact' => false
                            ));
                        endforeach;
                        
                        echo '</div>';
                        echo '</div>';
                        echo '</section>';
                    endif;
                endif;
                ?>

        </div>
    </div>

    <!-- Contact CTA Section (only for available listings) -->
    <?php if ($show_contact_forms) : ?>
        <?php
        // Get agent phone for CTA, with fallbacks
        $agent_phone = null;
        if ($agent_id && function_exists('hpt_get_agent_phone')) {
            $agent_phone = hpt_get_agent_phone($agent_id);
        }
        
        // Fallback to general business phone
        if (!$agent_phone) {
            $agent_phone = get_theme_mod('business_phone', '(302) 217-6692');
        }
        
        $cta_template = locate_template('template-parts/sections/content.php');
        if ($cta_template) :
            get_template_part('template-parts/sections/content', null, array(
                'layout' => 'centered',
                'background' => 'primary',
                'padding' => 'xl',
                'headline' => __('Interested in This Property?', 'happy-place-theme'),
                'content' => __('Ready to schedule a tour or learn more? Our experienced Delaware team is here to help.', 'happy-place-theme'),
                'buttons' => array(
                    array(
                        'text' => __('Schedule Tour', 'happy-place-theme'),
                        'url' => '#contact',
                        'style' => 'white',
                        'size' => 'lg'
                    ),
                    array(
                        'text' => sprintf(__('Call %s', 'happy-place-theme'), $agent_phone),
                        'url' => 'tel:' . preg_replace('/[^0-9]/', '', $agent_phone),
                        'style' => 'outline-white',
                        'size' => 'lg'
                    )
                ),
                'section_id' => 'contact-cta'
            ));
        else :
            // Fallback CTA if template doesn't exist
            echo '<section class="hph-cta-section hph-bg-primary hph-py-xl hph-text-center" id="contact-cta">';
            echo '<div class="hph-container">';
            echo '<h2 class="hph-text-white hph-text-3xl hph-font-bold hph-mb-md">' . __('Interested in This Property?', 'happy-place-theme') . '</h2>';
            echo '<p class="hph-text-white hph-text-lg hph-mb-lg">' . __('Ready to schedule a tour or learn more? Our experienced Delaware team is here to help.', 'happy-place-theme') . '</p>';
            echo '<div class="hph-flex hph-flex-col hph-sm:flex-row hph-gap-md hph-justify-center">';
            echo '<a href="#contact" class="hph-btn hph-btn-white hph-btn-lg">' . __('Schedule Tour', 'happy-place-theme') . '</a>';
            echo '<a href="tel:' . preg_replace('/[^0-9]/', '', $agent_phone) . '" class="hph-btn hph-btn-outline-white hph-btn-lg">' . sprintf(__('Call %s', 'happy-place-theme'), esc_html($agent_phone)) . '</a>';
            echo '</div>';
            echo '</div>';
            echo '</section>';
        endif;
        ?>
    <?php else : ?>
        <!-- Alternative CTA for sold/off-market listings -->
        <section class="hph-cta-section hph-bg-gray-100 hph-py-xl hph-text-center">
            <div class="hph-container">
                <h2 class="hph-text-gray-800 hph-text-3xl hph-font-bold hph-mb-md">
                    <?php 
                    if ($listing_status === 'sold') {
                        _e('This Property Has Been Sold', 'happy-place-theme');
                    } else {
                        _e('Looking for Similar Properties?', 'happy-place-theme');
                    }
                    ?>
                </h2>
                <p class="hph-text-gray-600 hph-text-lg hph-mb-lg">
                    <?php _e('Browse our current listings or contact us to learn about new properties coming to market.', 'happy-place-theme'); ?>
                </p>
                <div class="hph-flex hph-flex-col hph-sm:flex-row hph-gap-md hph-justify-center">
                    <a href="<?php echo esc_url(get_post_type_archive_link('listing')); ?>" class="hph-btn hph-btn-primary hph-btn-lg">
                        <?php _e('View All Listings', 'happy-place-theme'); ?>
                    </a>
                    <a href="<?php echo esc_url(get_permalink(get_page_by_path('contact'))); ?>" class="hph-btn hph-btn-outline hph-btn-lg">
                        <?php _e('Contact Us', 'happy-place-theme'); ?>
                    </a>
                </div>
            </div>
        </section>
    <?php endif; ?>

</main>

<?php
/**
 * Add structured data for listing
 */
function hph_add_listing_structured_data($listing_id) {
    if (!function_exists('hpt_get_listing')) {
        return;
    }
    
    $listing_data = hpt_get_listing($listing_id);
    if (!$listing_data) {
        return;
    }
    
    $structured_data = array(
        '@context' => 'https://schema.org',
        '@type' => 'RealEstateListing',
        'name' => get_the_title($listing_id),
        'url' => get_permalink($listing_id),
        'description' => $listing_data['description'] ?? '',
    );
    
    // Add price if available
    if (!empty($listing_data['price'])) {
        $structured_data['offers'] = array(
            '@type' => 'Offer',
            'price' => $listing_data['price'],
            'priceCurrency' => 'USD'
        );
    }
    
    // Add address if available
    if (!empty($listing_data['address'])) {
        $address_parts = $listing_data['address'];
        if (is_string($address_parts)) {
            $structured_data['address'] = $address_parts;
        } elseif (is_array($address_parts)) {
            $structured_data['address'] = array(
                '@type' => 'PostalAddress',
                'streetAddress' => $address_parts['street'] ?? '',
                'addressLocality' => $address_parts['city'] ?? '',
                'addressRegion' => $address_parts['state'] ?? '',
                'postalCode' => $address_parts['zip'] ?? ''
            );
        }
    }
    
    // Add property details
    if (!empty($listing_data['bedrooms'])) {
        $structured_data['numberOfBedrooms'] = $listing_data['bedrooms'];
    }
    
    if (!empty($listing_data['bathrooms'])) {
        $structured_data['numberOfBathroomsTotal'] = $listing_data['bathrooms'];
    }
    
    if (!empty($listing_data['square_feet'])) {
        $structured_data['floorSize'] = array(
            '@type' => 'QuantitativeValue',
            'value' => $listing_data['square_feet'],
            'unitText' => 'sqft'
        );
    }
    
    echo '<script type="application/ld+json">' . wp_json_encode($structured_data, JSON_UNESCAPED_SLASHES) . '</script>';
}
add_action('wp_head', function() {
    if (is_singular('listing')) {
        hph_add_listing_structured_data(get_the_ID());
    }
});

/**
 * Enqueue single listing specific assets with null safety
 */
function hph_enqueue_single_listing_assets() {
    if (!is_singular('listing')) {
        return;
    }
    
    $theme_version = get_theme_mod('theme_version', '3.1.0');
    $template_uri = get_template_directory_uri();
    
    // Single listing layout CSS
    $layout_css = $template_uri . '/assets/css/pages/hph-single-listing-layout.css';
    if (file_exists(get_template_directory() . '/assets/css/pages/hph-single-listing-layout.css')) {
        wp_enqueue_style(
            'hph-single-listing-layout',
            $layout_css,
            array('hph-framework'),
            $theme_version
        );
    }
    
    // Single listing JavaScript
    $listing_js = $template_uri . '/assets/js/single-listing.js';
    if (file_exists(get_template_directory() . '/assets/js/single-listing.js')) {
        wp_enqueue_script(
            'hph-single-listing-js',
            $listing_js,
            array('jquery'),
            $theme_version,
            true
        );
        
        // Add listing data for JavaScript
        $listing_id = get_the_ID();
        $js_data = array(
            'listingId' => $listing_id,
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hph_single_listing'),
            'strings' => array(
                'loading' => __('Loading...', 'happy-place-theme'),
                'error' => __('Something went wrong. Please try again.', 'happy-place-theme'),
                'success' => __('Success!', 'happy-place-theme')
            )
        );
        
        wp_localize_script('hph-single-listing-js', 'hphListingData', $js_data);
    }
    
    // Ensure Font Awesome is loaded for icons
    if (!wp_style_is('font-awesome', 'enqueued')) {
        wp_enqueue_style(
            'font-awesome', 
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', 
            array(), 
            '6.5.1'
        );
    }
}
add_action('wp_enqueue_scripts', 'hph_enqueue_single_listing_assets');

/**
 * Add body classes for single listing styling with null safety
 */
function hph_single_listing_body_class($classes) {
    if (!is_singular('listing')) {
        return $classes;
    }
    
    $classes[] = 'hph-single-listing';
    $classes[] = 'hph-property-page';
    $classes[] = 'hph-modern-layout';
    
    // Add listing status class if available
    $listing_id = get_the_ID();
    if ($listing_id) {
        $status = function_exists('hpt_get_listing_status') ? 
                 hpt_get_listing_status($listing_id) : 
                 get_field('listing_status', $listing_id);
        
        if ($status) {
            $classes[] = 'hph-listing-status-' . sanitize_html_class($status);
        }
        
        // Add property type class if available
        $property_type = function_exists('hpt_get_listing_property_type') ? 
                       hpt_get_listing_property_type($listing_id) : 
                       get_field('property_type', $listing_id);
        
        if ($property_type) {
            $classes[] = 'hph-property-type-' . sanitize_html_class($property_type);
        }
    }
    
    return $classes;
}
add_filter('body_class', 'hph_single_listing_body_class');

/**
 * Add listing ID as data attribute to body
 */
function hph_add_listing_data_attributes() {
    if (is_singular('listing')) {
        $listing_id = get_the_ID();
        echo ' data-listing-id="' . esc_attr($listing_id) . '"';
    }
}
add_action('body_data_attributes', 'hph_add_listing_data_attributes');

get_footer();
?>