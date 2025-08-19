<?php
/**
 * Add Listing Form
 * 
 * Comprehensive listing creation form with all fields and automation
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user and permissions
$current_user = wp_get_current_user();
$dashboard = \HappyPlace\Dashboard\Frontend_Admin_Dashboard::get_instance();
?>

<div class="add-listing-form">
    <form id="listing-form" class="dashboard-form" enctype="multipart/form-data">
        <?php wp_nonce_field('hph_save_listing', 'listing_nonce'); ?>
        <input type="hidden" name="action" value="hph_save_listing">
        <input type="hidden" name="listing_id" value="0">

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
                                   placeholder="<?php _e('Auto-generated from address (leave blank)', 'happy-place'); ?>">
                            <small class="text-muted"><?php _e('Leave blank to auto-generate from address', 'happy-place'); ?></small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-form-group">
                            <label for="mls_number" class="dashboard-form-label"><?php _e('MLS Number', 'happy-place'); ?></label>
                            <input type="text" id="mls_number" name="mls_number" class="dashboard-form-control" 
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
                                       placeholder="500000" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-form-group">
                            <label for="property_status" class="dashboard-form-label"><?php _e('Status *', 'happy-place'); ?></label>
                            <select id="property_status" name="property_status" class="dashboard-form-control" required>
                                <option value="active"><?php _e('Active', 'happy-place'); ?></option>
                                <option value="pending"><?php _e('Pending', 'happy-place'); ?></option>
                                <option value="under_contract"><?php _e('Under Contract', 'happy-place'); ?></option>
                                <option value="sold"><?php _e('Sold', 'happy-place'); ?></option>
                                <option value="off_market"><?php _e('Off Market', 'happy-place'); ?></option>
                                <option value="coming_soon"><?php _e('Coming Soon', 'happy-place'); ?></option>
                                <option value="draft" selected><?php _e('Draft', 'happy-place'); ?></option>
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
                                    // If not admin/broker, only show current user as agent
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
                                    $selected = '';
                                    // Auto-select current user's agent if they have one
                                    if (!$dashboard->user_can('manage_all_listings')) {
                                        $agent_user_id = get_field('wordpress_user_id', $agent->ID);
                                        if ($agent_user_id == $current_user->ID) {
                                            $selected = 'selected';
                                        }
                                    }
                                ?>
                                    <option value="<?php echo esc_attr($agent->ID); ?>" <?php echo $selected; ?>>
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
                                      maxlength="300" placeholder="<?php _e('Brief property overview (300 characters max)', 'happy-place'); ?>"></textarea>
                            <small class="text-muted"><span id="short-desc-count">0</span>/300 <?php _e('characters', 'happy-place'); ?></small>
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
                                   min="0" step="1" placeholder="3">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="dashboard-form-group">
                            <label for="bathrooms" class="dashboard-form-label"><?php _e('Bathrooms', 'happy-place'); ?></label>
                            <input type="number" id="bathrooms" name="bathrooms" class="dashboard-form-control" 
                                   min="0" step="0.5" placeholder="2.5">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="dashboard-form-group">
                            <label for="square_feet" class="dashboard-form-label"><?php _e('Square Feet', 'happy-place'); ?></label>
                            <input type="number" id="square_feet" name="square_feet" class="dashboard-form-control" 
                                   min="0" placeholder="2000">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="dashboard-form-group">
                            <label for="year_built" class="dashboard-form-label"><?php _e('Year Built', 'happy-place'); ?></label>
                            <input type="number" id="year_built" name="year_built" class="dashboard-form-control" 
                                   min="1800" max="<?php echo date('Y') + 5; ?>" placeholder="2010">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="dashboard-form-group">
                            <label for="lot_size" class="dashboard-form-label"><?php _e('Lot Size (sq ft)', 'happy-place'); ?></label>
                            <input type="number" id="lot_size" name="lot_size" class="dashboard-form-control" 
                                   min="0" placeholder="8000">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-form-group">
                            <label for="garage_spaces" class="dashboard-form-label"><?php _e('Garage Spaces', 'happy-place'); ?></label>
                            <input type="number" id="garage_spaces" name="garage_spaces" class="dashboard-form-control" 
                                   min="0" step="1" placeholder="2">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-form-group">
                            <label for="property_type" class="dashboard-form-label"><?php _e('Property Type', 'happy-place'); ?></label>
                            <select id="property_type" name="property_type" class="dashboard-form-control">
                                <option value="single_family"><?php _e('Single Family Home', 'happy-place'); ?></option>
                                <option value="condo"><?php _e('Condominium', 'happy-place'); ?></option>
                                <option value="townhouse"><?php _e('Townhouse', 'happy-place'); ?></option>
                                <option value="multi_family"><?php _e('Multi-Family', 'happy-place'); ?></option>
                                <option value="land"><?php _e('Land/Lot', 'happy-place'); ?></option>
                                <option value="commercial"><?php _e('Commercial', 'happy-place'); ?></option>
                                <option value="mobile_home"><?php _e('Mobile Home', 'happy-place'); ?></option>
                                <option value="other"><?php _e('Other', 'happy-place'); ?></option>
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
                                        <input type="checkbox" name="interior_features[]" value="<?php echo esc_attr($key); ?>">
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
                                        <input type="checkbox" name="exterior_features[]" value="<?php echo esc_attr($key); ?>">
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
                                   placeholder="<?php _e('123 Main Street', 'happy-place'); ?>" required>
                            <small class="text-muted"><?php _e('Start typing for address suggestions', 'happy-place'); ?></small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="dashboard-form-group">
                            <label for="city" class="dashboard-form-label"><?php _e('City *', 'happy-place'); ?></label>
                            <input type="text" id="city" name="city" class="dashboard-form-control" 
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
                                    <option value="<?php echo esc_attr($code); ?>"><?php echo esc_html($name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="dashboard-form-group">
                            <label for="zip_code" class="dashboard-form-label"><?php _e('ZIP Code *', 'happy-place'); ?></label>
                            <input type="text" id="zip_code" name="zip_code" class="dashboard-form-control" 
                                   placeholder="12345" pattern="[0-9]{5}(-[0-9]{4})?" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="dashboard-form-group">
                            <label for="latitude" class="dashboard-form-label"><?php _e('Latitude', 'happy-place'); ?></label>
                            <input type="number" id="latitude" name="latitude" class="dashboard-form-control" 
                                   step="any" placeholder="<?php _e('Auto-filled from address', 'happy-place'); ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="dashboard-form-group">
                            <label for="longitude" class="dashboard-form-label"><?php _e('Longitude', 'happy-place'); ?></label>
                            <input type="number" id="longitude" name="longitude" class="dashboard-form-control" 
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
                <div class="image-upload-section">
                    <div class="image-upload-area" id="image-upload-area">
                        <input type="file" id="listing_images" name="listing_images[]" multiple accept="image/*" style="display: none;">
                        <div class="upload-content">
                            <span class="hph-icon-camera" style="font-size: 48px; opacity: 0.5;"></span>
                            <h4><?php _e('Upload Property Images', 'happy-place'); ?></h4>
                            <p><?php _e('Drag and drop images here or click to browse', 'happy-place'); ?></p>
                            <small class="text-muted"><?php _e('Supported formats: JPG, PNG, GIF. Max 10MB per image.', 'happy-place'); ?></small>
                        </div>
                    </div>
                    
                    <div class="uploaded-images-container" id="uploaded-images-container">
                        <!-- Uploaded images will appear here -->
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="dashboard-form-group">
                            <label for="virtual_tour_url" class="dashboard-form-label"><?php _e('Virtual Tour URL', 'happy-place'); ?></label>
                            <input type="url" id="virtual_tour_url" name="virtual_tour_url" class="dashboard-form-control" 
                                   placeholder="https://virtualtour.com/property123">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="dashboard-form-group">
                            <label for="video_tour_url" class="dashboard-form-label"><?php _e('Video Tour URL', 'happy-place'); ?></label>
                            <input type="url" id="video_tour_url" name="video_tour_url" class="dashboard-form-control" 
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
                                       min="0" step="0.01" placeholder="5000.00">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-form-group">
                            <label for="buyer_agent_commission" class="dashboard-form-label"><?php _e('Buyer Agent Commission (%)', 'happy-place'); ?></label>
                            <div class="input-group">
                                <input type="number" id="buyer_agent_commission" name="buyer_agent_commission" class="dashboard-form-control" 
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
                              placeholder="<?php _e('Provide a detailed description of the property, highlighting key features, recent updates, neighborhood amenities, and any other selling points...', 'happy-place'); ?>"></textarea>
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
                        <?php _e('Publish Listing', 'happy-place'); ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Character counter for short description
    $('#short_description').on('input', function() {
        const count = $(this).val().length;
        $('#short-desc-count').text(count);
        
        if (count > 300) {
            $(this).val($(this).val().substring(0, 300));
            $('#short-desc-count').text(300);
        }
    });

    // Image upload handling
    $('#image-upload-area').on('click', function() {
        $('#listing_images').click();
    });

    $('#listing_images').on('change', function() {
        handleImageSelection(this.files);
    });

    // Drag and drop functionality
    $('#image-upload-area').on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('drag-over');
    });

    $('#image-upload-area').on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-over');
    });

    $('#image-upload-area').on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-over');
        
        const files = e.originalEvent.dataTransfer.files;
        handleImageSelection(files);
    });

    function handleImageSelection(files) {
        const container = $('#uploaded-images-container');
        
        Array.from(files).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imageItem = $(`
                        <div class="uploaded-image-item" data-file-index="${index}">
                            <img src="${e.target.result}" alt="Property Image">
                            <div class="image-controls">
                                <button type="button" class="btn btn-sm btn-primary set-featured" title="Set as Featured">
                                    <span class="hph-icon-star"></span>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger remove-image" title="Remove">
                                    <span class="hph-icon-trash"></span>
                                </button>
                            </div>
                            <input type="hidden" name="image_order[]" value="${index}">
                        </div>
                    `);
                    container.append(imageItem);
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Handle image removal
    $(document).on('click', '.remove-image', function() {
        $(this).closest('.uploaded-image-item').fadeOut(function() {
            $(this).remove();
        });
    });

    // Handle featured image setting
    $(document).on('click', '.set-featured', function() {
        $('.uploaded-image-item').removeClass('featured');
        $(this).closest('.uploaded-image-item').addClass('featured');
        
        // Update all buttons
        $('.set-featured').removeClass('btn-warning').addClass('btn-primary');
        $(this).removeClass('btn-primary').addClass('btn-warning');
    });

    // Address autocomplete (if Google Places is available)
    if (typeof google !== 'undefined' && google.maps && google.maps.places) {
        const addressInput = document.getElementById('street_address');
        const autocomplete = new google.maps.places.Autocomplete(addressInput);
        
        autocomplete.addListener('place_changed', function() {
            const place = autocomplete.getPlace();
            populateAddressFields(place);
        });
    }

    function populateAddressFields(place) {
        const components = place.address_components || [];
        
        // Clear existing values
        $('#city, #state, #zip_code').val('');
        
        components.forEach(component => {
            const types = component.types;
            
            if (types.includes('locality')) {
                $('#city').val(component.long_name);
            }
            if (types.includes('administrative_area_level_1')) {
                $('#state').val(component.short_name);
            }
            if (types.includes('postal_code')) {
                $('#zip_code').val(component.long_name);
            }
        });

        // Set coordinates
        if (place.geometry) {
            $('#latitude').val(place.geometry.location.lat());
            $('#longitude').val(place.geometry.location.lng());
        }
    }

    // Save as draft
    $('#save-draft').on('click', function() {
        $('#property_status').val('draft');
        $('#listing-form').submit();
    });

    // Preview listing
    $('#preview-listing').on('click', function() {
        // Generate preview modal with form data
        const formData = new FormData(document.getElementById('listing-form'));
        
        // Show preview modal (implementation would generate preview)
        alert('<?php _e('Preview functionality would show a modal with listing preview', 'happy-place'); ?>');
    });

    // Form validation
    $('#listing-form').on('submit', function(e) {
        e.preventDefault();
        
        // Validate required fields
        if (!validateForm()) {
            return false;
        }
        
        saveListing();
    });

    function validateForm() {
        let valid = true;
        const requiredFields = ['price', 'street_address', 'city', 'state', 'zip_code', 'listing_agent'];
        
        requiredFields.forEach(field => {
            const $field = $(`#${field}`);
            if (!$field.val()) {
                $field.addClass('is-invalid');
                valid = false;
            } else {
                $field.removeClass('is-invalid');
            }
        });
        
        if (!valid) {
            alert('<?php _e('Please fill in all required fields', 'happy-place'); ?>');
        }
        
        return valid;
    }

    function saveListing() {
        const formData = new FormData(document.getElementById('listing-form'));
        
        const $submitBtn = $('#listing-form [type="submit"]');
        const originalText = $submitBtn.text();
        
        $.ajax({
            url: hph_dashboard.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $submitBtn.prop('disabled', true).text('<?php _e('Saving...', 'happy-place'); ?>');
                $('#hph-loading-overlay').show();
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    const alert = $(`
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong><?php _e('Success!', 'happy-place'); ?></strong> ${response.data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `);
                    $('.add-listing-form').prepend(alert);
                    
                    // Redirect to listings or edit page
                    if (response.data.redirect) {
                        setTimeout(function() {
                            window.location.href = response.data.redirect;
                        }, 2000);
                    }
                } else {
                    // Show error message
                    const alert = $(`
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong><?php _e('Error!', 'happy-place'); ?></strong> ${response.data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `);
                    $('.add-listing-form').prepend(alert);
                }
            },
            error: function() {
                const alert = $(`
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong><?php _e('Error!', 'happy-place'); ?></strong> <?php _e('Failed to save listing. Please try again.', 'happy-place'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);
                $('.add-listing-form').prepend(alert);
            },
            complete: function() {
                $submitBtn.prop('disabled', false).text(originalText);
                $('#hph-loading-overlay').hide();
            }
        });
    }
});
</script>

<style>
/* Image Upload Styles */
.image-upload-area {
    border: 2px dashed var(--hph-border-color);
    border-radius: var(--hph-border-radius);
    padding: var(--hph-space-xl);
    text-align: center;
    cursor: pointer;
    transition: var(--hph-transition);
    background: var(--hph-gray-50);
}

.image-upload-area:hover,
.image-upload-area.drag-over {
    border-color: var(--hph-primary);
    background: var(--hph-primary-light);
}

.upload-content h4 {
    margin: var(--hph-space-md) 0 var(--hph-space-xs) 0;
    color: var(--hph-text-color);
}

.upload-content p {
    margin: 0 0 var(--hph-space-xs) 0;
    color: var(--hph-text-muted);
}

.uploaded-images-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: var(--hph-space-md);
    margin-top: var(--hph-space-lg);
}

.uploaded-image-item {
    position: relative;
    border-radius: var(--hph-border-radius);
    overflow: hidden;
    border: 2px solid transparent;
    transition: var(--hph-transition);
}

.uploaded-image-item.featured {
    border-color: var(--hph-warning);
    box-shadow: 0 0 0 2px var(--hph-warning-light);
}

.uploaded-image-item img {
    width: 100%;
    height: 120px;
    object-fit: cover;
    display: block;
}

.image-controls {
    position: absolute;
    top: 4px;
    right: 4px;
    display: flex;
    gap: 4px;
    opacity: 0;
    transition: var(--hph-transition);
}

.uploaded-image-item:hover .image-controls {
    opacity: 1;
}

.image-controls .btn {
    width: 28px;
    height: 28px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
}

/* Feature Checkboxes */
.feature-checkboxes {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--hph-space-xs);
    max-height: 250px;
    overflow-y: auto;
    padding: var(--hph-space-sm);
    border: 1px solid var(--hph-border-color);
    border-radius: var(--hph-border-radius);
    background: var(--hph-gray-50);
}

.feature-checkbox {
    display: flex;
    align-items: center;
    padding: var(--hph-space-xs);
    font-size: var(--hph-text-sm);
    cursor: pointer;
    border-radius: var(--hph-border-radius);
    transition: var(--hph-transition);
}

.feature-checkbox:hover {
    background: var(--hph-primary-light);
}

.feature-checkbox input {
    margin-right: var(--hph-space-xs);
    margin-top: 0;
}

/* Input Groups */
.input-group {
    display: flex;
    align-items: stretch;
}

.input-group-text {
    background: var(--hph-gray-100);
    border: 1px solid var(--hph-border-color);
    border-right: 0;
    padding: var(--hph-space-sm);
    border-radius: var(--hph-border-radius) 0 0 var(--hph-border-radius);
    color: var(--hph-text-muted);
    font-weight: var(--hph-font-semibold);
}

.input-group .dashboard-form-control {
    border-left: 0;
    border-radius: 0 var(--hph-border-radius) var(--hph-border-radius) 0;
}

.input-group .dashboard-form-control:focus {
    box-shadow: 0 0 0 2px var(--hph-primary-light);
}

/* Validation */
.is-invalid {
    border-color: var(--hph-danger) !important;
    box-shadow: 0 0 0 2px var(--hph-danger-light) !important;
}

@media (max-width: 767px) {
    .feature-checkboxes {
        grid-template-columns: 1fr;
        max-height: 200px;
    }
    
    .uploaded-images-container {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    }
    
    .uploaded-image-item img {
        height: 80px;
    }
    
    .form-actions .text-end {
        text-align: left !important;
        margin-top: var(--hph-space-sm);
    }
}
</style>