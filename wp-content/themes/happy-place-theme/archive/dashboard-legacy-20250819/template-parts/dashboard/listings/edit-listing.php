<?php
/**
 * Edit Listing Form
 * 
 * Comprehensive listing editing form with all fields pre-populated
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get listing data
$listing_id = get_query_var('dashboard_id', 0);
if (!$listing_id) {
    echo '<div class="alert alert-warning">' . __('No listing specified for editing.', 'happy-place') . '</div>';
    return;
}

$listing = get_post($listing_id);
if (!$listing || $listing->post_type !== 'listing') {
    echo '<div class="alert alert-warning">' . __('Listing not found.', 'happy-place') . '</div>';
    return;
}

// Check permissions
$current_user = wp_get_current_user();
$dashboard = \HappyPlace\Dashboard\Frontend_Admin_Dashboard::get_instance();
$listing_agent_id = get_field('listing_agent', $listing_id);

if (!$dashboard->user_can('manage_all_listings') && $listing_agent_id != $current_user->ID) {
    echo '<div class="alert alert-danger">' . __('You do not have permission to edit this listing.', 'happy-place') . '</div>';
    return;
}

// Get all listing data
$listing_data = [
    'title' => $listing->post_title,
    'content' => $listing->post_content,
    'excerpt' => $listing->post_excerpt,
    'status' => $listing->post_status,
    // ACF fields
    'price' => get_field('price', $listing_id),
    'property_status' => get_field('property_status', $listing_id),
    'mls_number' => get_field('mls_number', $listing_id),
    'bedrooms' => get_field('bedrooms', $listing_id),
    'bathrooms' => get_field('bathrooms', $listing_id),
    'square_feet' => get_field('square_feet', $listing_id),
    'year_built' => get_field('year_built', $listing_id),
    'lot_size' => get_field('lot_size', $listing_id),
    'garage_spaces' => get_field('garage_spaces', $listing_id),
    'property_type' => get_field('property_type', $listing_id),
    'street_address' => get_field('street_address', $listing_id),
    'city' => get_field('city', $listing_id),
    'state' => get_field('state', $listing_id),
    'zip_code' => get_field('zip_code', $listing_id),
    'latitude' => get_field('latitude', $listing_id),
    'longitude' => get_field('longitude', $listing_id),
    'hoa_fees' => get_field('hoa_fees', $listing_id),
    'property_taxes' => get_field('property_taxes', $listing_id),
    'buyer_agent_commission' => get_field('buyer_agent_commission', $listing_id),
    'listing_agent' => get_field('listing_agent', $listing_id),
    'interior_features' => get_field('interior_features', $listing_id) ?: [],
    'exterior_features' => get_field('exterior_features', $listing_id) ?: [],
    'virtual_tour_url' => get_field('virtual_tour_url', $listing_id),
    'video_tour_url' => get_field('video_tour_url', $listing_id),
];

// Get gallery images
$gallery_images = get_field('gallery_images', $listing_id) ?: [];
$featured_image_id = get_post_thumbnail_id($listing_id);
?>

<div class="edit-listing-form">
    <div class="listing-status-banner">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-1"><?php echo esc_html($listing->post_title); ?></h4>
                <p class="mb-0 text-muted">
                    <?php printf(__('Created: %s | Last Modified: %s', 'happy-place'), 
                        get_the_date('M j, Y g:i A', $listing), 
                        get_the_modified_date('M j, Y g:i A', $listing)); ?>
                </p>
            </div>
            <div class="col-md-4 text-end">
                <span class="listing-status-badge status-<?php echo esc_attr($listing_data['property_status']); ?>">
                    <?php echo esc_html(ucfirst(str_replace('_', ' ', $listing_data['property_status']))); ?>
                </span>
                <a href="<?php echo get_permalink($listing_id); ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                    <span class="hph-icon-external-link"></span>
                    <?php _e('View Live', 'happy-place'); ?>
                </a>
            </div>
        </div>
    </div>

    <form id="listing-form" class="dashboard-form" enctype="multipart/form-data">
        <?php wp_nonce_field('hph_save_listing', 'listing_nonce'); ?>
        <input type="hidden" name="action" value="hph_save_listing">
        <input type="hidden" name="listing_id" value="<?php echo esc_attr($listing_id); ?>">

        <!-- Basic Information Section -->
        <div class="listing-form-section">
            <div class="form-section-header">
                <h3 class="form-section-title">
                    <span class="hph-icon-home"></span>
                    <?php _e('Basic Information', 'happy-place'); ?>
                </h3>
            </div>
            <div class="form-section-content">
                <div class="row">
                    <div class="col-md-8">
                        <div class="dashboard-form-group">
                            <label for="listing_title" class="dashboard-form-label"><?php _e('Property Title', 'happy-place'); ?></label>
                            <input type="text" id="listing_title" name="listing_title" class="dashboard-form-control" 
                                   value="<?php echo esc_attr($listing_data['title']); ?>"
                                   placeholder="<?php _e('Auto-generated from address (leave blank)', 'happy-place'); ?>">
                            <small class="text-muted"><?php _e('Leave blank to auto-generate from address', 'happy-place'); ?></small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-form-group">
                            <label for="mls_number" class="dashboard-form-label"><?php _e('MLS Number', 'happy-place'); ?></label>
                            <input type="text" id="mls_number" name="mls_number" class="dashboard-form-control" 
                                   value="<?php echo esc_attr($listing_data['mls_number']); ?>"
                                   placeholder="<?php _e('e.g., MLS123456', 'happy-place'); ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="dashboard-form-group">
                            <label for="price" class="dashboard-form-label"><?php _e('List Price *', 'happy-place'); ?></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" id="price" name="price" class="dashboard-form-control" 
                                       value="<?php echo esc_attr($listing_data['price']); ?>"
                                       placeholder="500000" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-form-group">
                            <label for="property_status" class="dashboard-form-label"><?php _e('Status *', 'happy-place'); ?></label>
                            <select id="property_status" name="property_status" class="dashboard-form-control" required>
                                <option value="active" <?php selected($listing_data['property_status'], 'active'); ?>><?php _e('Active', 'happy-place'); ?></option>
                                <option value="pending" <?php selected($listing_data['property_status'], 'pending'); ?>><?php _e('Pending', 'happy-place'); ?></option>
                                <option value="under_contract" <?php selected($listing_data['property_status'], 'under_contract'); ?>><?php _e('Under Contract', 'happy-place'); ?></option>
                                <option value="sold" <?php selected($listing_data['property_status'], 'sold'); ?>><?php _e('Sold', 'happy-place'); ?></option>
                                <option value="off_market" <?php selected($listing_data['property_status'], 'off_market'); ?>><?php _e('Off Market', 'happy-place'); ?></option>
                                <option value="coming_soon" <?php selected($listing_data['property_status'], 'coming_soon'); ?>><?php _e('Coming Soon', 'happy-place'); ?></option>
                                <option value="draft" <?php selected($listing_data['property_status'], 'draft'); ?>><?php _e('Draft', 'happy-place'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-form-group">
                            <label for="listing_agent" class="dashboard-form-label"><?php _e('Listing Agent *', 'happy-place'); ?></label>
                            <select id="listing_agent" name="listing_agent" class="dashboard-form-control" required>
                                <?php
                                // Get agents based on user permissions
                                $agents_args = [
                                    'post_type' => 'agent',
                                    'posts_per_page' => -1,
                                    'post_status' => 'publish',
                                    'orderby' => 'title',
                                    'order' => 'ASC'
                                ];

                                if (!$dashboard->user_can('manage_all_listings')) {
                                    $agents_args['meta_query'] = [
                                        [
                                            'key' => 'wordpress_user_id',
                                            'value' => $current_user->ID,
                                            'compare' => '='
                                        ]
                                    ];
                                }

                                $agents = get_posts($agents_args);
                                foreach ($agents as $agent):
                                ?>
                                    <option value="<?php echo esc_attr($agent->ID); ?>" <?php selected($listing_data['listing_agent'], $agent->ID); ?>>
                                        <?php echo esc_html($agent->post_title); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="dashboard-form-group">
                            <label for="short_description" class="dashboard-form-label"><?php _e('Short Description', 'happy-place'); ?></label>
                            <textarea id="short_description" name="short_description" class="dashboard-form-control" rows="3" 
                                      maxlength="300" placeholder="<?php _e('Brief property overview (300 characters max)', 'happy-place'); ?>"><?php echo esc_textarea($listing_data['excerpt']); ?></textarea>
                            <small class="text-muted"><span id="short-desc-count"><?php echo strlen($listing_data['excerpt']); ?></span>/300 <?php _e('characters', 'happy-place'); ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Property Details Section -->
        <div class="listing-form-section">
            <div class="form-section-header">
                <h3 class="form-section-title">
                    <span class="hph-icon-info"></span>
                    <?php _e('Property Details', 'happy-place'); ?>
                </h3>
            </div>
            <div class="form-section-content">
                <div class="row">
                    <div class="col-md-3">
                        <div class="dashboard-form-group">
                            <label for="bedrooms" class="dashboard-form-label"><?php _e('Bedrooms', 'happy-place'); ?></label>
                            <input type="number" id="bedrooms" name="bedrooms" class="dashboard-form-control" 
                                   value="<?php echo esc_attr($listing_data['bedrooms']); ?>"
                                   min="0" step="1" placeholder="3">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="dashboard-form-group">
                            <label for="bathrooms" class="dashboard-form-label"><?php _e('Bathrooms', 'happy-place'); ?></label>
                            <input type="number" id="bathrooms" name="bathrooms" class="dashboard-form-control" 
                                   value="<?php echo esc_attr($listing_data['bathrooms']); ?>"
                                   min="0" step="0.5" placeholder="2.5">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="dashboard-form-group">
                            <label for="square_feet" class="dashboard-form-label"><?php _e('Square Feet', 'happy-place'); ?></label>
                            <input type="number" id="square_feet" name="square_feet" class="dashboard-form-control" 
                                   value="<?php echo esc_attr($listing_data['square_feet']); ?>"
                                   min="0" placeholder="2000">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="dashboard-form-group">
                            <label for="year_built" class="dashboard-form-label"><?php _e('Year Built', 'happy-place'); ?></label>
                            <input type="number" id="year_built" name="year_built" class="dashboard-form-control" 
                                   value="<?php echo esc_attr($listing_data['year_built']); ?>"
                                   min="1800" max="<?php echo date('Y') + 5; ?>" placeholder="2010">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="dashboard-form-group">
                            <label for="lot_size" class="dashboard-form-label"><?php _e('Lot Size (sq ft)', 'happy-place'); ?></label>
                            <input type="number" id="lot_size" name="lot_size" class="dashboard-form-control" 
                                   value="<?php echo esc_attr($listing_data['lot_size']); ?>"
                                   min="0" placeholder="8000">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-form-group">
                            <label for="garage_spaces" class="dashboard-form-label"><?php _e('Garage Spaces', 'happy-place'); ?></label>
                            <input type="number" id="garage_spaces" name="garage_spaces" class="dashboard-form-control" 
                                   value="<?php echo esc_attr($listing_data['garage_spaces']); ?>"
                                   min="0" step="1" placeholder="2">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-form-group">
                            <label for="property_type" class="dashboard-form-label"><?php _e('Property Type', 'happy-place'); ?></label>
                            <select id="property_type" name="property_type" class="dashboard-form-control">
                                <option value="single_family" <?php selected($listing_data['property_type'], 'single_family'); ?>><?php _e('Single Family Home', 'happy-place'); ?></option>
                                <option value="condo" <?php selected($listing_data['property_type'], 'condo'); ?>><?php _e('Condominium', 'happy-place'); ?></option>
                                <option value="townhouse" <?php selected($listing_data['property_type'], 'townhouse'); ?>><?php _e('Townhouse', 'happy-place'); ?></option>
                                <option value="multi_family" <?php selected($listing_data['property_type'], 'multi_family'); ?>><?php _e('Multi-Family', 'happy-place'); ?></option>
                                <option value="land" <?php selected($listing_data['property_type'], 'land'); ?>><?php _e('Land/Lot', 'happy-place'); ?></option>
                                <option value="commercial" <?php selected($listing_data['property_type'], 'commercial'); ?>><?php _e('Commercial', 'happy-place'); ?></option>
                                <option value="mobile_home" <?php selected($listing_data['property_type'], 'mobile_home'); ?>><?php _e('Mobile Home', 'happy-place'); ?></option>
                                <option value="other" <?php selected($listing_data['property_type'], 'other'); ?>><?php _e('Other', 'happy-place'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Property Features -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="dashboard-form-group">
                            <label class="dashboard-form-label"><?php _e('Interior Features', 'happy-place'); ?></label>
                            <div class="feature-checkboxes">
                                <?php
                                $interior_features = [
                                    'hardwood_floors' => __('Hardwood Floors', 'happy-place'),
                                    'tile_floors' => __('Tile Floors', 'happy-place'),
                                    'carpet' => __('Carpet', 'happy-place'),
                                    'granite_counters' => __('Granite Countertops', 'happy-place'),
                                    'stainless_appliances' => __('Stainless Steel Appliances', 'happy-place'),
                                    'fireplace' => __('Fireplace', 'happy-place'),
                                    'walk_in_closet' => __('Walk-in Closet', 'happy-place'),
                                    'master_suite' => __('Master Suite', 'happy-place'),
                                    'updated_kitchen' => __('Updated Kitchen', 'happy-place'),
                                    'basement' => __('Basement', 'happy-place'),
                                    'attic' => __('Attic', 'happy-place'),
                                    'laundry_room' => __('Laundry Room', 'happy-place')
                                ];
                                
                                foreach ($interior_features as $key => $label):
                                ?>
                                    <label class="feature-checkbox">
                                        <input type="checkbox" name="interior_features[]" value="<?php echo esc_attr($key); ?>" 
                                               <?php checked(in_array($key, $listing_data['interior_features'])); ?>>
                                        <?php echo esc_html($label); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="dashboard-form-group">
                            <label class="dashboard-form-label"><?php _e('Exterior Features', 'happy-place'); ?></label>
                            <div class="feature-checkboxes">
                                <?php
                                $exterior_features = [
                                    'pool' => __('Swimming Pool', 'happy-place'),
                                    'spa' => __('Spa/Hot Tub', 'happy-place'),
                                    'deck' => __('Deck', 'happy-place'),
                                    'patio' => __('Patio', 'happy-place'),
                                    'garden' => __('Garden', 'happy-place'),
                                    'fence' => __('Fenced Yard', 'happy-place'),
                                    'sprinkler_system' => __('Sprinkler System', 'happy-place'),
                                    'shed' => __('Storage Shed', 'happy-place'),
                                    'rv_parking' => __('RV Parking', 'happy-place'),
                                    'boat_dock' => __('Boat Dock', 'happy-place'),
                                    'tennis_court' => __('Tennis Court', 'happy-place'),
                                    'workshop' => __('Workshop', 'happy-place')
                                ];
                                
                                foreach ($exterior_features as $key => $label):
                                ?>
                                    <label class="feature-checkbox">
                                        <input type="checkbox" name="exterior_features[]" value="<?php echo esc_attr($key); ?>" 
                                               <?php checked(in_array($key, $listing_data['exterior_features'])); ?>>
                                        <?php echo esc_html($label); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location Section -->
        <div class="listing-form-section">
            <div class="form-section-header">
                <h3 class="form-section-title">
                    <span class="hph-icon-map-pin"></span>
                    <?php _e('Location', 'happy-place'); ?>
                </h3>
            </div>
            <div class="form-section-content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="dashboard-form-group">
                            <label for="street_address" class="dashboard-form-label"><?php _e('Street Address *', 'happy-place'); ?></label>
                            <input type="text" id="street_address" name="street_address" class="dashboard-form-control" 
                                   value="<?php echo esc_attr($listing_data['street_address']); ?>"
                                   placeholder="<?php _e('123 Main Street', 'happy-place'); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="dashboard-form-group">
                            <label for="city" class="dashboard-form-label"><?php _e('City *', 'happy-place'); ?></label>
                            <input type="text" id="city" name="city" class="dashboard-form-control" 
                                   value="<?php echo esc_attr($listing_data['city']); ?>"
                                   placeholder="<?php _e('City Name', 'happy-place'); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="dashboard-form-group">
                            <label for="state" class="dashboard-form-label"><?php _e('State *', 'happy-place'); ?></label>
                            <select id="state" name="state" class="dashboard-form-control" required>
                                <option value=""><?php _e('Select State', 'happy-place'); ?></option>
                                <?php
                                $states = [
                                    'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas', 'CA' => 'California',
                                    'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware', 'FL' => 'Florida', 'GA' => 'Georgia',
                                    'HI' => 'Hawaii', 'ID' => 'Idaho', 'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa',
                                    'KS' => 'Kansas', 'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland',
                                    'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi', 'MO' => 'Missouri',
                                    'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada', 'NH' => 'New Hampshire', 'NJ' => 'New Jersey',
                                    'NM' => 'New Mexico', 'NY' => 'New York', 'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio',
                                    'OK' => 'Oklahoma', 'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina',
                                    'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah', 'VT' => 'Vermont',
                                    'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia', 'WI' => 'Wisconsin', 'WY' => 'Wyoming'
                                ];
                                
                                foreach ($states as $code => $name):
                                ?>
                                    <option value="<?php echo esc_attr($code); ?>" <?php selected($listing_data['state'], $code); ?>><?php echo esc_html($name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="dashboard-form-group">
                            <label for="zip_code" class="dashboard-form-label"><?php _e('ZIP Code *', 'happy-place'); ?></label>
                            <input type="text" id="zip_code" name="zip_code" class="dashboard-form-control" 
                                   value="<?php echo esc_attr($listing_data['zip_code']); ?>"
                                   placeholder="12345" pattern="[0-9]{5}(-[0-9]{4})?" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="dashboard-form-group">
                            <label for="latitude" class="dashboard-form-label"><?php _e('Latitude', 'happy-place'); ?></label>
                            <input type="number" id="latitude" name="latitude" class="dashboard-form-control" 
                                   value="<?php echo esc_attr($listing_data['latitude']); ?>"
                                   step="any" placeholder="<?php _e('Auto-filled from address', 'happy-place'); ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="dashboard-form-group">
                            <label for="longitude" class="dashboard-form-label"><?php _e('Longitude', 'happy-place'); ?></label>
                            <input type="number" id="longitude" name="longitude" class="dashboard-form-control" 
                                   value="<?php echo esc_attr($listing_data['longitude']); ?>"
                                   step="any" placeholder="<?php _e('Auto-filled from address', 'happy-place'); ?>" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Images Section -->
        <div class="listing-form-section">
            <div class="form-section-header">
                <h3 class="form-section-title">
                    <span class="hph-icon-camera"></span>
                    <?php _e('Property Images', 'happy-place'); ?>
                </h3>
            </div>
            <div class="form-section-content">
                <!-- Existing Images -->
                <?php if ($featured_image_id || !empty($gallery_images)): ?>
                    <div class="existing-images-section">
                        <h5><?php _e('Current Images', 'happy-place'); ?></h5>
                        <div class="existing-images-container">
                            <?php if ($featured_image_id): ?>
                                <div class="existing-image-item featured" data-attachment-id="<?php echo esc_attr($featured_image_id); ?>">
                                    <?php echo wp_get_attachment_image($featured_image_id, 'medium'); ?>
                                    <div class="image-controls">
                                        <span class="featured-badge"><?php _e('Featured', 'happy-place'); ?></span>
                                        <button type="button" class="btn btn-sm btn-danger remove-existing-image" 
                                                data-attachment-id="<?php echo esc_attr($featured_image_id); ?>">
                                            <span class="hph-icon-trash"></span>
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php foreach ($gallery_images as $image_id): ?>
                                <?php if ($image_id != $featured_image_id): ?>
                                    <div class="existing-image-item" data-attachment-id="<?php echo esc_attr($image_id); ?>">
                                        <?php echo wp_get_attachment_image($image_id, 'medium'); ?>
                                        <div class="image-controls">
                                            <button type="button" class="btn btn-sm btn-primary set-existing-featured" 
                                                    data-attachment-id="<?php echo esc_attr($image_id); ?>">
                                                <span class="hph-icon-star"></span>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger remove-existing-image" 
                                                    data-attachment-id="<?php echo esc_attr($image_id); ?>">
                                                <span class="hph-icon-trash"></span>
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- New Image Upload -->
                <div class="image-upload-section">
                    <div class="image-upload-area" id="image-upload-area">
                        <input type="file" id="listing_images" name="listing_images[]" multiple accept="image/*" style="display: none;">
                        <div class="upload-content">
                            <span class="hph-icon-camera" style="font-size: 48px; opacity: 0.5;"></span>
                            <h4><?php _e('Add More Images', 'happy-place'); ?></h4>
                            <p><?php _e('Drag and drop images here or click to browse', 'happy-place'); ?></p>
                            <small class="text-muted"><?php _e('Supported formats: JPG, PNG, GIF. Max 10MB per image.', 'happy-place'); ?></small>
                        </div>
                    </div>
                    
                    <div class="uploaded-images-container" id="uploaded-images-container">
                        <!-- New uploaded images will appear here -->
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="dashboard-form-group">
                            <label for="virtual_tour_url" class="dashboard-form-label"><?php _e('Virtual Tour URL', 'happy-place'); ?></label>
                            <input type="url" id="virtual_tour_url" name="virtual_tour_url" class="dashboard-form-control" 
                                   value="<?php echo esc_attr($listing_data['virtual_tour_url']); ?>"
                                   placeholder="https://virtualtour.com/property123">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="dashboard-form-group">
                            <label for="video_tour_url" class="dashboard-form-label"><?php _e('Video Tour URL', 'happy-place'); ?></label>
                            <input type="url" id="video_tour_url" name="video_tour_url" class="dashboard-form-control" 
                                   value="<?php echo esc_attr($listing_data['video_tour_url']); ?>"
                                   placeholder="https://youtube.com/watch?v=...">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Information Section -->
        <div class="listing-form-section">
            <div class="form-section-header">
                <h3 class="form-section-title">
                    <span class="hph-icon-dollar-sign"></span>
                    <?php _e('Financial Information', 'happy-place'); ?>
                </h3>
            </div>
            <div class="form-section-content">
                <div class="row">
                    <div class="col-md-4">
                        <div class="dashboard-form-group">
                            <label for="hoa_fees" class="dashboard-form-label"><?php _e('HOA Fees (Monthly)', 'happy-place'); ?></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" id="hoa_fees" name="hoa_fees" class="dashboard-form-control" 
                                       value="<?php echo esc_attr($listing_data['hoa_fees']); ?>"
                                       min="0" step="0.01" placeholder="150.00">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-form-group">
                            <label for="property_taxes" class="dashboard-form-label"><?php _e('Property Taxes (Annual)', 'happy-place'); ?></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" id="property_taxes" name="property_taxes" class="dashboard-form-control" 
                                       value="<?php echo esc_attr($listing_data['property_taxes']); ?>"
                                       min="0" step="0.01" placeholder="5000.00">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-form-group">
                            <label for="buyer_agent_commission" class="dashboard-form-label"><?php _e('Buyer Agent Commission (%)', 'happy-place'); ?></label>
                            <div class="input-group">
                                <input type="number" id="buyer_agent_commission" name="buyer_agent_commission" class="dashboard-form-control" 
                                       value="<?php echo esc_attr($listing_data['buyer_agent_commission']); ?>"
                                       min="0" max="10" step="0.1" placeholder="2.5">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Description Section -->
        <div class="listing-form-section">
            <div class="form-section-header">
                <h3 class="form-section-title">
                    <span class="hph-icon-file-text"></span>
                    <?php _e('Detailed Description', 'happy-place'); ?>
                </h3>
            </div>
            <div class="form-section-content">
                <div class="dashboard-form-group">
                    <label for="full_description" class="dashboard-form-label"><?php _e('Full Property Description', 'happy-place'); ?></label>
                    <textarea id="full_description" name="full_description" class="dashboard-form-control" rows="8" 
                              placeholder="<?php _e('Provide a detailed description of the property, highlighting key features, recent updates, neighborhood amenities, and any other selling points...', 'happy-place'); ?>"><?php echo esc_textarea($listing_data['content']); ?></textarea>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <div class="row">
                <div class="col-md-6">
                    <button type="button" class="btn btn-outline-secondary" id="preview-listing">
                        <span class="hph-icon-eye"></span>
                        <?php _e('Preview Listing', 'happy-place'); ?>
                    </button>
                    <button type="button" class="btn btn-outline-info" id="save-draft">
                        <span class="hph-icon-save"></span>
                        <?php _e('Save as Draft', 'happy-place'); ?>
                    </button>
                </div>
                <div class="col-md-6 text-end">
                    <button type="submit" class="btn btn-primary">
                        <span class="hph-icon-check"></span>
                        <?php _e('Update Listing', 'happy-place'); ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Include the same JavaScript as add-listing but with edit-specific modifications -->
<script>
jQuery(document).ready(function($) {
    // Inherit all functionality from add-listing form
    // Plus additional edit-specific features

    // Handle existing image removal
    $('.remove-existing-image').on('click', function() {
        const attachmentId = $(this).data('attachment-id');
        const $imageItem = $(this).closest('.existing-image-item');
        
        if (confirm('<?php _e('Are you sure you want to remove this image?', 'happy-place'); ?>')) {
            // Add to removal list
            $('<input>').attr({
                type: 'hidden',
                name: 'remove_images[]',
                value: attachmentId
            }).appendTo('#listing-form');
            
            $imageItem.fadeOut(function() {
                $(this).remove();
            });
        }
    });

    // Handle setting existing image as featured
    $('.set-existing-featured').on('click', function() {
        const attachmentId = $(this).data('attachment-id');
        
        // Remove featured status from all images
        $('.existing-image-item').removeClass('featured');
        $('.featured-badge').remove();
        $('.set-existing-featured').removeClass('btn-warning').addClass('btn-primary');
        
        // Set this image as featured
        $(this).closest('.existing-image-item').addClass('featured');
        $(this).removeClass('btn-primary').addClass('btn-warning');
        $(this).closest('.image-controls').prepend('<span class="featured-badge"><?php _e('Featured', 'happy-place'); ?></span>');
        
        // Add hidden input for new featured image
        $('input[name="new_featured_image"]').remove();
        $('<input>').attr({
            type: 'hidden',
            name: 'new_featured_image',
            value: attachmentId
        }).appendTo('#listing-form');
    });

    // All other functionality from add-listing.php applies here
    // Character counters, image upload, address autocomplete, etc.
    
    // Character counter for short description
    $('#short_description').on('input', function() {
        const count = $(this).val().length;
        $('#short-desc-count').text(count);
        
        if (count > 300) {
            $(this).val($(this).val().substring(0, 300));
            $('#short-desc-count').text(300);
        }
    });

    // Save as draft
    $('#save-draft').on('click', function() {
        $('#property_status').val('draft');
        $('#listing-form').submit();
    });

    // Form submission
    $('#listing-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const $submitBtn = $(this).find('[type="submit"]');
        const originalText = $submitBtn.text();
        
        $.ajax({
            url: hph_dashboard.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $submitBtn.prop('disabled', true).text('<?php _e('Updating...', 'happy-place'); ?>');
                $('#hph-loading-overlay').show();
            },
            success: function(response) {
                if (response.success) {
                    const alert = $(`
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong><?php _e('Success!', 'happy-place'); ?></strong> ${response.data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `);
                    $('.edit-listing-form').prepend(alert);
                    
                    // Auto-dismiss and refresh if needed
                    setTimeout(() => {
                        alert.fadeOut();
                        if (response.data.refresh) {
                            location.reload();
                        }
                    }, 3000);
                } else {
                    const alert = $(`
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong><?php _e('Error!', 'happy-place'); ?></strong> ${response.data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `);
                    $('.edit-listing-form').prepend(alert);
                }
            },
            error: function() {
                const alert = $(`
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong><?php _e('Error!', 'happy-place'); ?></strong> <?php _e('Failed to update listing. Please try again.', 'happy-place'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);
                $('.edit-listing-form').prepend(alert);
            },
            complete: function() {
                $submitBtn.prop('disabled', false).text(originalText);
                $('#hph-loading-overlay').hide();
            }
        });
    });
});
</script>

<style>
/* Additional styles for edit form */
.listing-status-banner {
    background: var(--hph-primary-light);
    border: 1px solid var(--hph-primary);
    border-radius: var(--hph-card-radius);
    padding: var(--hph-space-lg);
    margin-bottom: var(--hph-space-lg);
}

.existing-images-section {
    margin-bottom: var(--hph-space-lg);
    padding-bottom: var(--hph-space-lg);
    border-bottom: 1px solid var(--hph-border-color);
}

.existing-images-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: var(--hph-space-md);
    margin-top: var(--hph-space-md);
}

.existing-image-item {
    position: relative;
    border-radius: var(--hph-border-radius);
    overflow: hidden;
    border: 2px solid var(--hph-border-color);
    transition: var(--hph-transition);
}

.existing-image-item.featured {
    border-color: var(--hph-warning);
    box-shadow: 0 0 0 2px var(--hph-warning-light);
}

.existing-image-item img {
    width: 100%;
    height: 120px;
    object-fit: cover;
    display: block;
}

.featured-badge {
    background: var(--hph-warning);
    color: var(--hph-warning-dark);
    font-size: 10px;
    padding: 2px 6px;
    border-radius: var(--hph-border-radius);
    font-weight: var(--hph-font-bold);
    text-transform: uppercase;
    position: absolute;
    top: 4px;
    left: 4px;
}

/* Inherit all other styles from add-listing form */
</style>