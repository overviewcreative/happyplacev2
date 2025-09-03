<?php
/**
 * Single Listing Template - Complete Rewrite
 * 
 * Modern, responsive single listing page with comprehensive null handling
 * Uses modular template parts for maintainability
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (have_posts()) : 
    while (have_posts()) : the_post();
        
        $listing_id = get_the_ID();
        
        // Comprehensive data validation and defaults
        $listing_data = hph_get_safe_listing_data($listing_id);
        
        // Check if listing exists and is valid
        if (!$listing_data || get_post_status($listing_id) !== 'publish') {
            get_template_part('template-parts/listing/listing-not-found');
            break;
        }
        
        // Pass data to template parts
        $template_args = [
            'listing_id' => $listing_id,
            'listing_data' => $listing_data
        ];
        ?>
        
        <main id="primary" class="hph-single-listing">
            
            <!-- Hero Section with Gallery -->
            <?php get_template_part('template-parts/listing/hero', null, $template_args); ?>
            
            <!-- Main Content Area -->
            <div class="hph-listing-container">
                <div class="hph-listing-layout">
                    
                    <!-- Left Column: Main Content -->
                    <div class="hph-listing-main">
                        
                        <!-- Property Description & Details -->
                        <?php get_template_part('template-parts/listing/main-body', null, $template_args); ?>
                        
                        <!-- Photo Gallery & Virtual Tour -->
                        <?php if (hph_has_media($listing_data)) : ?>
                            <?php get_template_part('template-parts/listing/gallery-tour-section', null, $template_args); ?>
                        <?php endif; ?>
                        
                        <!-- Map Section -->
                        <?php if (hph_has_location($listing_data)) : ?>
                            <?php get_template_part('template-parts/listing/map-section', null, $template_args); ?>
                        <?php endif; ?>
                        
                        <!-- Neighborhood Information -->
                        <?php get_template_part('template-parts/listing/neighborhood-section', null, $template_args); ?>
                        
                        <!-- School Information -->
                        <?php get_template_part('template-parts/listing/schools-section', null, $template_args); ?>
                        
                        <!-- Similar Listings -->
                        <?php get_template_part('template-parts/listing/similar-listings', null, $template_args); ?>
                        
                    </div>
                    
                    <!-- Right Column: Sidebar -->
                    <div class="hph-listing-sidebar-wrapper">
                        <?php get_template_part('template-parts/listing/sidebar-agent', null, $template_args); ?>
                    </div>
                    
                </div>
            </div>
            
        </main>
        
        <?php
        
    endwhile;
endif;

get_footer();

/**
 * Get safe listing data with comprehensive null handling
 */
function hph_get_safe_listing_data($listing_id) {
    if (!$listing_id || !get_post($listing_id)) {
        return false;
    }
    
    // Default structure for all possible fields
    $defaults = [
        // Basic Info
        'listing_price' => 0,
        'listing_status' => 'active',
        'mls_number' => '',
        'property_type' => '',
        'property_subtype' => '',
        
        // Property Details
        'bedrooms' => 0,
        'bathrooms_full' => 0,
        'bathrooms_half' => 0,
        'square_feet' => 0,
        'lot_size_acres' => 0,
        'lot_size_sqft' => 0,
        'garage_spaces' => 0,
        'parking_spaces' => 0,
        'year_built' => '',
        
        // Address Components
        'street_number' => '',
        'street_name' => '',
        'street_type' => '',
        'unit_number' => '',
        'city' => '',
        'state' => '',
        'zip_code' => '',
        'county' => '',
        'subdivision' => '',
        
        // Financial Info
        'annual_taxes' => 0,
        'tax_year' => '',
        'hoa_fee' => 0,
        'hoa_frequency' => 'monthly',
        'price_per_sqft' => 0,
        
        // Description & Marketing
        'property_description' => '',
        'public_remarks' => '',
        'private_remarks' => '',
        'showing_instructions' => '',
        
        // Media
        'primary_photo' => null,
        'photo_gallery' => [],
        'virtual_tour_url' => '',
        'video_url' => '',
        'floor_plans' => [],
        'property_documents' => [],
        
        // Features
        'interior_features' => [],
        'exterior_features' => [],
        'property_features' => [],
        'appliances' => [],
        'heating_cooling' => [],
        'flooring' => [],
        
        // Location
        'latitude' => null,
        'longitude' => null,
        'directions' => '',
        
        // Listing Details
        'list_date' => '',
        'status_change_date' => '',
        'days_on_market' => 0,
        'listing_agent' => null,
        'listing_office' => '',
        'co_listing_agent' => null,
        
        // Utilities & Systems
        'utilities' => [],
        'construction_materials' => [],
        'roof_type' => '',
        'foundation_type' => '',
        'water_source' => '',
        'sewer_type' => '',
        
        // School Information
        'school_district' => '',
        'elementary_school' => '',
        'middle_school' => '',
        'high_school' => '',
        
        // Additional
        'zoning' => '',
        'flood_zone' => '',
        'waterfront' => false,
        'pool' => false,
        'fireplace' => false,
        'basement' => false,
        'attic' => false
    ];
    
    // Get field values with null checking
    $data = [];
    foreach ($defaults as $field => $default) {
        $value = get_field($field, $listing_id);
        
        // Handle different data types appropriately
        if ($value === null || $value === '' || $value === false) {
            $data[$field] = $default;
        } else {
            // Type-specific validation
            switch (gettype($default)) {
                case 'integer':
                    $data[$field] = is_numeric($value) ? (int) $value : $default;
                    break;
                case 'double':
                    $data[$field] = is_numeric($value) ? (float) $value : $default;
                    break;
                case 'array':
                    $data[$field] = is_array($value) ? $value : $default;
                    break;
                case 'boolean':
                    $data[$field] = (bool) $value;
                    break;
                default:
                    $data[$field] = sanitize_text_field($value);
            }
        }
    }
    
    // Calculate derived fields
    $data['total_bathrooms'] = $data['bathrooms_full'] + ($data['bathrooms_half'] * 0.5);
    $data['full_address'] = hph_build_address($data);
    $data['street_address'] = trim($data['street_number'] . ' ' . $data['street_name'] . ' ' . $data['street_type']);
    $data['city_state_zip'] = trim($data['city'] . ', ' . $data['state'] . ' ' . $data['zip_code']);
    
    // Calculate price per square foot
    if ($data['listing_price'] > 0 && $data['square_feet'] > 0) {
        $data['price_per_sqft'] = round($data['listing_price'] / $data['square_feet']);
    }
    
    // Ensure media arrays are not empty
    if (empty($data['photo_gallery']) && has_post_thumbnail($listing_id)) {
        $data['photo_gallery'] = [
            [
                'url' => get_the_post_thumbnail_url($listing_id, 'full'),
                'alt' => get_the_title($listing_id),
                'caption' => ''
            ]
        ];
    }
    
    return $data;
}

/**
 * Build full address from components
 */
function hph_build_address($data) {
    $parts = [];
    
    // Street address
    $street_parts = array_filter([
        $data['street_number'],
        $data['street_name'],
        $data['street_type']
    ]);
    
    if (!empty($street_parts)) {
        $street = implode(' ', $street_parts);
        if (!empty($data['unit_number'])) {
            $street .= ', Unit ' . $data['unit_number'];
        }
        $parts[] = $street;
    }
    
    // City, State ZIP
    $location_parts = array_filter([
        $data['city'],
        $data['state'],
        $data['zip_code']
    ]);
    
    if (!empty($location_parts)) {
        $parts[] = implode(', ', $location_parts);
    }
    
    return implode(', ', $parts);
}

/**
 * Check if listing has media content
 */
function hph_has_media($listing_data) {
    return !empty($listing_data['photo_gallery']) || 
           !empty($listing_data['virtual_tour_url']) || 
           !empty($listing_data['video_url']) ||
           !empty($listing_data['primary_photo']);
}

/**
 * Check if listing has location data
 */
function hph_has_location($listing_data) {
    return !empty($listing_data['latitude']) && !empty($listing_data['longitude']);
}

/**
 * Get formatted price
 */
function hph_format_price($price, $show_currency = true) {
    if (!is_numeric($price) || $price <= 0) {
        return 'Price Available Upon Request';
    }
    
    $formatted = number_format($price);
    return $show_currency ? '$' . $formatted : $formatted;
}

/**
 * Get formatted square footage
 */
function hph_format_sqft($sqft) {
    if (!is_numeric($sqft) || $sqft <= 0) {
        return '';
    }
    
    return number_format($sqft) . ' sq ft';
}

/**
 * Get formatted lot size
 */
function hph_format_lot_size($acres = 0, $sqft = 0) {
    if ($acres > 0) {
        return number_format($acres, 2) . ' acres';
    } elseif ($sqft > 0) {
        return number_format($sqft) . ' sq ft';
    }
    
    return '';
}

/**
 * Get bathroom count display
 */
function hph_format_bathrooms($full, $half) {
    $total = $full + ($half * 0.5);
    
    if ($total <= 0) {
        return '';
    }
    
    if ($half > 0) {
        return $full . '.' . $half . ' baths';
    }
    
    return $total . ' bath' . ($total != 1 ? 's' : '');
}

/**
 * Get listing status display
 */
function hph_get_status_display($status) {
    $statuses = [
        'active' => 'Active',
        'pending' => 'Sale Pending',
        'sold' => 'Sold',
        'coming_soon' => 'Coming Soon',
        'withdrawn' => 'Withdrawn',
        'expired' => 'Expired',
        'off_market' => 'Off Market'
    ];
    
    return $statuses[$status] ?? 'Available';
}

/**
 * Check if field has content
 */
function hph_has_content($value) {
    if (is_array($value)) {
        return !empty($value);
    }
    
    return !empty($value) && $value !== '0' && $value !== 0;
}
        
        // Build address safely
        $address_parts = array_filter(array(
            $listing_data['street_number'],
            $listing_data['street_name'],
            $listing_data['street_type']
        ));
        $street_address = implode(' ', $address_parts);
        
        $city_state_parts = array_filter(array(
            $listing_data['city'],
            $listing_data['state'],
            $listing_data['zip_code']
        ));
        $city_state_zip = implode(', ', $city_state_parts);
        
        $full_address = array_filter(array($street_address, $city_state_zip));
        $full_address = implode(', ', $full_address);
        
        // Get agent data safely
        $agent_id = function_exists('hph_get_listing_agent') 
            ? hph_get_listing_agent($listing_id) 
            : get_post_field('post_author', $listing_id);
        
        $agent_data = null;
        if ($agent_id) {
            $agent_data = array(
                'id' => $agent_id,
                'name' => get_field('agent_name', $agent_id) ?: get_the_title($agent_id) ?: get_the_author_meta('display_name', $agent_id),
                'phone' => get_field('agent_phone', $agent_id) ?: '',
                'email' => get_field('agent_email', $agent_id) ?: get_the_author_meta('email', $agent_id),
                'photo' => get_field('agent_photo', $agent_id),
                'license' => get_field('agent_license', $agent_id) ?: '',
                'bio' => get_field('agent_bio', $agent_id) ?: ''
            );
        }
        
        // Check if user has favorited this listing
        $is_favorited = function_exists('hph_is_listing_favorited') 
            ? hph_is_listing_favorited($listing_id) 
            : false;
        
        // Prepare gallery images
        $gallery_images = array();
        if (!empty($listing_data['primary_photo'])) {
            $gallery_images[] = $listing_data['primary_photo'];
        }
        if (!empty($listing_data['photo_gallery']) && is_array($listing_data['photo_gallery'])) {
            $gallery_images = array_merge($gallery_images, $listing_data['photo_gallery']);
        }
        // Fallback to featured image
        if (empty($gallery_images) && has_post_thumbnail($listing_id)) {
            $gallery_images[] = array(
                'url' => get_the_post_thumbnail_url($listing_id, 'full'),
                'alt' => get_the_title($listing_id),
                'sizes' => array(
                    'thumbnail' => get_the_post_thumbnail_url($listing_id, 'thumbnail')
                )
            );
        }
        ?>
        
        <article id="listing-<?php echo $listing_id; ?>" <?php post_class('hph-single-listing'); ?>>
            
            <!-- Hero Section - Always shows even without images -->
            <?php 
            if (locate_template('template-parts/listing/hero.php')) {
                get_template_part('template-parts/listing/hero', null, array(
                    'listing_id' => $listing_id,
                    'address' => $full_address
                ));
            } else {
                // Fallback hero if template part missing
                ?>
                <section class="hph-hero hph-hero--listing">
                    <div class="hph-hero__content">
                        <div class="hph-container">
                            <h1 class="hph-hero__title"><?php the_title(); ?></h1>
                            <?php if ($full_address) : ?>
                            <p class="hph-hero__subtitle">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo esc_html($full_address); ?>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
                <?php
            }
            ?>
            
            <!-- Price and Stats Bar - Shows with available data -->
            <?php 
            if (locate_template('template-parts/listing/price-stats.php')) {
                get_template_part('template-parts/listing/price-stats', null, array(
                    'listing_id' => $listing_id
                ));
            } elseif ($listing_data['listing_price'] > 0) {
                // Fallback price display
                ?>
                <section class="hph-price-stats">
                    <div class="hph-container">
                        <div class="hph-price-stats__wrapper">
                            <div class="hph-price-stats__price">
                                <div class="hph-price-stats__amount">
                                    $<?php echo number_format($listing_data['listing_price']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <?php
            }
            ?>
            
            <!-- Main Content Area -->
            <div class="hph-listing-content">
                <div class="hph-container">
                    <div class="hph-listing-layout">
                        
                        <!-- Primary Content Column -->
                        <div class="hph-listing-main">
                            
                            <!-- Property Description - Shows if content exists -->
                            <?php 
                            $has_description = !empty($listing_data['property_description']) || !empty(get_the_content());
                            
                            if ($has_description) {
                                if (locate_template('template-parts/listing/description.php')) {
                                    get_template_part('template-parts/listing/description', null, array(
                                        'listing_id' => $listing_id
                                    ));
                                } else {
                                    // Fallback description
                                    ?>
                                    <section class="hph-overview-section">
                                        <div class="hph-overview">
                                            <h2 class="hph-section-title">About This Property</h2>
                                            <div class="hph-overview-description">
                                                <?php 
                                                if (!empty($listing_data['property_description'])) {
                                                    echo wp_kses_post($listing_data['property_description']);
                                                } else {
                                                    the_content();
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </section>
                                    <?php
                                }
                            }
                            ?>
                            
                            <!-- Property Details Grid - Shows if data exists -->
                            <?php 
                            $has_details = ($listing_data['listing_price'] > 0 || 
                                          $listing_data['bedrooms'] > 0 || 
                                          $listing_data['square_feet'] > 0 ||
                                          !empty($listing_data['year_built']));
                            
                            if ($has_details) {
                                if (locate_template('template-parts/listing/details-grid.php')) {
                                    get_template_part('template-parts/listing/details-grid', null, array(
                                        'listing_id' => $listing_id
                                    ));
                                }
                            }
                            ?>
                            
                            <!-- Features & Amenities - Shows if features exist -->
                            <?php 
                            $has_features = (!empty($listing_data['interior_features']) || 
                                           !empty($listing_data['exterior_features']) || 
                                           !empty($listing_data['property_features']));
                            
                            if ($has_features) {
                                if (locate_template('template-parts/listing/features.php')) {
                                    get_template_part('template-parts/listing/features', null, array(
                                        'listing_id' => $listing_id
                                    ));
                                }
                            }
                            ?>
                            
                            <!-- Map Section - Shows only if coordinates exist -->
                            <?php 
                            if (!empty($listing_data['latitude']) && !empty($listing_data['longitude'])) {
                                if (locate_template('template-parts/listing/map.php')) {
                                    get_template_part('template-parts/listing/map', null, array(
                                        'listing_id' => $listing_id,
                                        'address' => $full_address
                                    ));
                                }
                            }
                            ?>
                            
                            <!-- Virtual Tour - Shows if URL exists -->
                            <?php if (!empty($listing_data['virtual_tour_url'])) : ?>
                            <section class="hph-virtual-tour-section">
                                <div class="hph-section-wrapper">
                                    <h2 class="hph-section-title">
                                        <i class="fas fa-vr-cardboard"></i>
                                        Virtual Tour
                                    </h2>
                                    <div class="hph-virtual-tour-embed">
                                        <?php 
                                        $tour_url = $listing_data['virtual_tour_url'];
                                        if (strpos($tour_url, 'matterport') !== false || strpos($tour_url, 'iframe') !== false) {
                                            echo '<iframe src="' . esc_url($tour_url) . '" frameborder="0" allowfullscreen></iframe>';
                                        } else {
                                            echo '<a href="' . esc_url($tour_url) . '" target="_blank" class="hph-btn hph-btn--primary">';
                                            echo '<i class="fas fa-external-link-alt"></i> View Virtual Tour</a>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </section>
                            <?php endif; ?>
                            
                            <!-- Floor Plans - Shows if images exist -->
                            <?php if (!empty($listing_data['floor_plans']) && is_array($listing_data['floor_plans'])) : ?>
                            <section class="hph-floor-plans-section">
                                <div class="hph-section-wrapper">
                                    <h2 class="hph-section-title">
                                        <i class="fas fa-blueprint"></i>
                                        Floor Plans
                                    </h2>
                                    <div class="hph-floor-plans-grid">
                                        <?php foreach ($listing_data['floor_plans'] as $plan) : 
                                            if (!empty($plan['url'])) :
                                        ?>
                                        <div class="hph-floor-plan">
                                            <img src="<?php echo esc_url($plan['url']); ?>" 
                                                 alt="<?php echo esc_attr($plan['alt'] ?? 'Floor Plan'); ?>"
                                                 onclick="if(typeof openGalleryModal === 'function') openGalleryModal()">
                                        </div>
                                        <?php 
                                            endif;
                                        endforeach; ?>
                                    </div>
                                </div>
                            </section>
                            <?php endif; ?>
                            
                            <!-- Property Documents - Shows if documents exist -->
                            <?php if (!empty($listing_data['property_documents']) && is_array($listing_data['property_documents'])) : ?>
                            <section class="hph-documents-section">
                                <div class="hph-section-wrapper">
                                    <h2 class="hph-section-title">
                                        <i class="fas fa-file-alt"></i>
                                        Property Documents
                                    </h2>
                                    <div class="hph-documents-list">
                                        <?php foreach ($listing_data['property_documents'] as $doc) : 
                                            if (!empty($doc['url'])) :
                                        ?>
                                        <a href="<?php echo esc_url($doc['url']); ?>" 
                                           target="_blank"
                                           class="hph-document-link">
                                            <i class="fas fa-file-pdf"></i>
                                            <span><?php echo esc_html($doc['title'] ?? $doc['filename'] ?? 'Document'); ?></span>
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <?php 
                                            endif;
                                        endforeach; ?>
                                    </div>
                                </div>
                            </section>
                            <?php endif; ?>
                            
                        </div>
                        
                        <!-- Sidebar - Always shows but individual components conditional -->
                        <aside class="hph-listing-sidebar">
                            
                            <!-- Agent Card - Shows if agent exists -->
                            <?php if ($agent_data && !empty($agent_data['name'])) : ?>
                            <div class="hph-agent-card">
                                <h3 class="hph-agent-card__title">Contact Agent</h3>
                                
                                <div class="hph-agent-card__info">
                                    <?php if (!empty($agent_data['photo']['url'])) : ?>
                                    <img src="<?php echo esc_url($agent_data['photo']['url']); ?>" 
                                         alt="<?php echo esc_attr($agent_data['name']); ?>"
                                         class="hph-agent-card__photo">
                                    <?php else : ?>
                                    <div class="hph-agent-card__photo hph-agent-card__photo--placeholder">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="hph-agent-card__details">
                                        <h4 class="hph-agent-card__name"><?php echo esc_html($agent_data['name']); ?></h4>
                                        <?php if (!empty($agent_data['license'])) : ?>
                                        <p class="hph-agent-card__license">License: <?php echo esc_html($agent_data['license']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="hph-agent-card__buttons">
                                    <?php if (!empty($agent_data['phone'])) : ?>
                                    <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9]/', '', $agent_data['phone'])); ?>" 
                                       class="hph-btn hph-btn--primary hph-btn--full">
                                        <i class="fas fa-phone"></i>
                                        <?php echo esc_html($agent_data['phone']); ?>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($agent_data['email']) && is_email($agent_data['email'])) : ?>
                                    <a href="mailto:<?php echo esc_attr($agent_data['email']); ?>" 
                                       class="hph-btn hph-btn--outline hph-btn--full">
                                        <i class="fas fa-envelope"></i>
                                        Send Email
                                    </a>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Contact Form -->
                                <?php 
                                if (locate_template('template-parts/forms/contact-agent.php')) {
                                    get_template_part('template-parts/forms/contact-agent', null, array(
                                        'agent_id' => $agent_id,
                                        'property_address' => $full_address,
                                        'listing_id' => $listing_id
                                    ));
                                }
                                ?>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Mortgage Calculator - Shows if price exists -->
                            <?php if ($listing_data['listing_price'] > 0) : ?>
                            <div class="hph-mortgage-calculator">
                                <h3 class="hph-calculator__title">
                                    <i class="fas fa-calculator"></i>
                                    Mortgage Calculator
                                </h3>
                                
                                <div class="hph-calculator__content">
                                    <div class="hph-form-group">
                                        <label>Home Price</label>
                                        <input type="number" 
                                               id="calc-price" 
                                               value="<?php echo esc_attr($listing_data['listing_price']); ?>"
                                               class="hph-form-input">
                                    </div>
                                    
                                    <div class="hph-form-group">
                                        <label>Down Payment (%)</label>
                                        <input type="number" 
                                               id="calc-down" 
                                               value="20"
                                               min="0"
                                               max="100"
                                               class="hph-form-input">
                                    </div>
                                    
                                    <div class="hph-form-group">
                                        <label>Interest Rate (%)</label>
                                        <input type="number" 
                                               id="calc-rate" 
                                               value="6.5"
                                               step="0.1"
                                               class="hph-form-input">
                                    </div>
                                    
                                    <div class="hph-form-group">
                                        <label>Loan Term (years)</label>
                                        <select id="calc-term" class="hph-form-input">
                                            <option value="15">15 years</option>
                                            <option value="30" selected>30 years</option>
                                        </select>
                                    </div>
                                    
                                    <button onclick="if(typeof calculateMortgage === 'function') calculateMortgage()" 
                                            class="hph-btn hph-btn--primary hph-btn--full">
                                        Calculate Payment
                                    </button>
                                    
                                    <div id="calc-result" class="hph-calculator__result" style="display: none;">
                                        <p class="hph-calculator__label">Estimated Monthly Payment</p>
                                        <p class="hph-calculator__amount">$0</p>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Share Listing - Always shows -->
                            <div class="hph-share-card">
                                <h3 class="hph-share-card__title">Share This Property</h3>
                                
                                <div class="hph-share-buttons">
                                    <button onclick="if(typeof shareOnFacebook === 'function') shareOnFacebook()" 
                                            class="hph-share-btn hph-share-btn--facebook"
                                            aria-label="Share on Facebook">
                                        <i class="fab fa-facebook-f"></i>
                                    </button>
                                    <button onclick="if(typeof shareOnTwitter === 'function') shareOnTwitter()" 
                                            class="hph-share-btn hph-share-btn--twitter"
                                            aria-label="Share on Twitter">
                                        <i class="fab fa-twitter"></i>
                                    </button>
                                    <button onclick="if(typeof shareOnPinterest === 'function') shareOnPinterest()" 
                                            class="hph-share-btn hph-share-btn--pinterest"
                                            aria-label="Share on Pinterest">
                                        <i class="fab fa-pinterest-p"></i>
                                    </button>
                                    <button onclick="if(typeof shareViaEmail === 'function') shareViaEmail()" 
                                            class="hph-share-btn hph-share-btn--email"
                                            aria-label="Share via Email">
                                        <i class="fas fa-envelope"></i>
                                    </button>
                                    <button onclick="if(typeof copyLink === 'function') copyLink()" 
                                            class="hph-share-btn hph-share-btn--link"
                                            aria-label="Copy Link">
                                        <i class="fas fa-link"></i>
                                    </button>
                                </div>
                            </div>
                            
                        </aside>
                        
                    </div>
                </div>
            </div>
            
            <!-- Related Listings - Optional -->
            <?php
            if (!empty($listing_data['city'])) {
                $related_args = array(
                    'post_type' => 'listing',
                    'posts_per_page' => 3,
                    'post__not_in' => array($listing_id),
                    'meta_query' => array(
                        array(
                            'key' => 'city',
                            'value' => $listing_data['city'],
                            'compare' => '='
                        )
                    )
                );
                
                $related_listings = new WP_Query($related_args);
                
                if ($related_listings->have_posts()) : ?>
                <section class="hph-related-listings">
                    <div class="hph-container">
                        <h2 class="hph-section-title">
                            <i class="fas fa-home"></i>
                            Similar Properties in <?php echo esc_html($listing_data['city']); ?>
                        </h2>
                        
                        <div class="hph-listings-grid">
                            <?php 
                            while ($related_listings->have_posts()) : 
                                $related_listings->the_post();
                                if (locate_template('template-parts/listing/card.php')) {
                                    get_template_part('template-parts/listing/card');
                                } else {
                                    // Simple fallback card
                                    ?>
                                    <article class="hph-listing-card">
                                        <a href="<?php the_permalink(); ?>">
                                            <?php if (has_post_thumbnail()) : ?>
                                                <?php the_post_thumbnail('medium'); ?>
                                            <?php endif; ?>
                                            <h3><?php the_title(); ?></h3>
                                        </a>
                                    </article>
                                    <?php
                                }
                            endwhile;
                            wp_reset_postdata();
                            ?>
                        </div>
                    </div>
                </section>
                <?php endif;
            }
            ?>
            
        </article>
        
        <!-- Initialize JavaScript with safety checks -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize gallery only if function exists and images available
            if (typeof initGallery === 'function' && <?php echo !empty($gallery_images) ? 'true' : 'false'; ?>) {
                initGallery(<?php echo json_encode(array_map(function($img) {
                    return array(
                        'url' => $img['url'] ?? '',
                        'alt' => $img['alt'] ?? ''
                    );
                }, $gallery_images)); ?>);
            }
            
            // Set JavaScript variables with defaults
            window.USER_ID = <?php echo intval(get_current_user_id()); ?>;
            window.NONCE = '<?php echo wp_create_nonce('hph_listing_nonce'); ?>';
            window.MAPBOX_TOKEN = '<?php echo esc_js(get_theme_mod('mapbox_api_key', '')); ?>';
            window.ajaxurl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
        });
        
        // Safe mortgage calculator
        function calculateMortgage() {
            try {
                const priceEl = document.getElementById('calc-price');
                const downEl = document.getElementById('calc-down');
                const rateEl = document.getElementById('calc-rate');
                const termEl = document.getElementById('calc-term');
                const resultDiv = document.getElementById('calc-result');
                
                if (!priceEl || !downEl || !rateEl || !termEl || !resultDiv) return;
                
                const price = parseFloat(priceEl.value) || 0;
                const downPercent = parseFloat(downEl.value) || 0;
                const rate = parseFloat(rateEl.value) / 100 / 12 || 0;
                const term = parseFloat(termEl.value) * 12 || 360;
                
                if (price <= 0 || rate <= 0) return;
                
                const loanAmount = price * (1 - downPercent / 100);
                const payment = loanAmount * (rate * Math.pow(1 + rate, term)) / (Math.pow(1 + rate, term) - 1);
                
                const amountDiv = resultDiv.querySelector('.hph-calculator__amount');
                if (amountDiv) {
                    amountDiv.textContent = '$' + (payment || 0).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                    resultDiv.style.display = 'block';
                }
            } catch(e) {
                console.error('Calculator error:', e);
            }
        }
        
        // Safe social share functions
        function shareOnFacebook() {
            if (window.location) {
                window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(window.location.href));
            }
        }
        
        function shareOnTwitter() {
            if (window.location) {
                window.open('https://twitter.com/intent/tweet?url=' + encodeURIComponent(window.location.href) + '&text=' + encodeURIComponent(document.title || ''));
            }
        }
        
        function shareOnPinterest() {
            try {
                const heroEl = document.querySelector('.hph-hero__slide');
                if (heroEl && window.location) {
                    const bgImage = window.getComputedStyle(heroEl).backgroundImage;
                    const img = bgImage ? bgImage.slice(5, -2) : '';
                    window.open('https://pinterest.com/pin/create/button/?url=' + encodeURIComponent(window.location.href) + '&media=' + encodeURIComponent(img) + '&description=' + encodeURIComponent(document.title || ''));
                }
            } catch(e) {
                console.error('Pinterest share error:', e);
            }
        }
        
        function shareViaEmail() {
            if (window.location) {
                window.location.href = 'mailto:?subject=' + encodeURIComponent(document.title || 'Property Listing') + '&body=' + encodeURIComponent('Check out this property: ' + window.location.href);
            }
        }
        
        function copyLink() {
            try {
                const temp = document.createElement('input');
                document.body.appendChild(temp);
                temp.value = window.location.href;
                temp.select();
                document.execCommand('copy');
                document.body.removeChild(temp);
                
                if (typeof showNotification === 'function') {
                    showNotification('Link copied to clipboard!');
                } else {
                    alert('Link copied to clipboard!');
                }
            } catch(e) {
                console.error('Copy link error:', e);
            }
        }
        </script>
        
    <?php 
    endwhile;
else:
    // No listing found
    ?>
    <div class="hph-container">
        <div class="hph-no-listing">
            <h1>Property Not Found</h1>
            <p>Sorry, the property you're looking for is no longer available or doesn't exist.</p>
            <a href="<?php echo get_post_type_archive_link('listing'); ?>" class="hph-btn hph-btn--primary">
                View All Properties
            </a>
        </div>
    </div>
    <?php
endif;

get_footer(); 
?>