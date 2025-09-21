<?php
/**
 * Add Listing Page - Complete Frontend Listing Form
 * 
 * Exact replication of wp-admin listing fields for frontend use
 * 
 * @package HappyPlaceTheme
 */

// Security and access check
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

$current_user = wp_get_current_user();
$user_roles = $current_user->roles;
$is_agent = in_array('agent', $user_roles) || in_array('administrator', $user_roles) || in_array('staff', $user_roles);

if (!$is_agent) {
    wp_die(__('Access denied. Only agents can add listings.', 'happy-place-theme'));
}

get_header(); ?>

<div class="add-listing-page">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                
                <!-- Page Header -->
                <div class="listing-form-header py-4 border-bottom mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h2 mb-1">Add New Listing</h1>
                            <p class="text-muted mb-0">Create a comprehensive property listing with all details</p>
                        </div>
                        <div>
                            <a href="<?php echo home_url('/user-dashboard/'); ?>" class="hph-btn hph-btn-outline-primary-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Progress Indicator -->
                <div class="form-progress mb-4">
                    <div class="progress-steps">
                        <div class="step active" data-step="1">
                            <div class="step-number">1</div>
                            <div class="step-label">Basic Info</div>
                        </div>
                        <div class="step" data-step="2">
                            <div class="step-number">2</div>
                            <div class="step-label">Property Details</div>
                        </div>
                        <div class="step" data-step="3">
                            <div class="step-number">3</div>
                            <div class="step-label">Location & Address</div>
                        </div>
                        <div class="step" data-step="4">
                            <div class="step-number">4</div>
                            <div class="step-label">Financial Info</div>
                        </div>
                        <div class="step" data-step="5">
                            <div class="step-number">5</div>
                            <div class="step-label">Features & Amenities</div>
                        </div>
                        <div class="step" data-step="6">
                            <div class="step-number">6</div>
                            <div class="step-label">Media & Gallery</div>
                        </div>
                        <div class="step" data-step="7">
                            <div class="step-number">7</div>
                            <div class="step-label">Review & Publish</div>
                        </div>
                    </div>
                </div>

                <!-- Main Form -->
                <form id="addListingForm" class="add-listing-form" enctype="multipart/form-data">
                    
                    <!-- Security Fields -->
                    <?php wp_nonce_field('add_listing_form', 'add_listing_nonce'); ?>
                    <input type="hidden" name="action" value="hph_create_listing">
                    <input type="hidden" name="listing_id" id="listingId" value="">
                    
                    <!-- Step 1: Basic Information -->
                    <div class="form-step active" data-step="1">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title mb-0">Basic Property Information</h3>
                                <p class="text-muted mb-0">Essential details about the property</p>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    
                                    <!-- Property Title -->
                                    <div class="col-12">
                                        <label for="post_title" class="form-label">
                                            Property Title <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="post_title" name="post_title" required
                                               placeholder="e.g., Beautiful 3BR Colonial in Downtown">
                                        <div class="form-text">Create an attractive, descriptive title for your listing</div>
                                    </div>

                                    <!-- Listing Price -->
                                    <div class="col-md-4">
                                        <label for="listing_price" class="form-label">
                                            Listing Price <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="listing_price" name="listing_price" 
                                                   required min="0" step="1000" placeholder="450000">
                                        </div>
                                        <div class="form-text">Enter numbers only (no commas)</div>
                                    </div>

                                    <!-- MLS Number -->
                                    <div class="col-md-4">
                                        <label for="mls_number" class="form-label">MLS Number</label>
                                        <input type="text" class="form-control" id="mls_number" name="mls_number" 
                                               maxlength="20" placeholder="MLS123456">
                                        <div class="form-text">Multiple Listing Service ID</div>
                                    </div>

                                    <!-- Featured Listing -->
                                    <div class="col-md-4">
                                        <label for="is_featured" class="form-label">Featured Listing</label>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1">
                                            <label class="form-check-label" for="is_featured">Feature this listing</label>
                                        </div>
                                        <div class="form-text">Display as featured listing</div>
                                    </div>

                                    <!-- Property Type -->
                                    <div class="col-md-6">
                                        <label for="property_type" class="form-label">
                                            Property Type <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="property_type" name="property_type" required>
                                            <option value="">Select Property Type</option>
                                            <option value="single_family">Single Family Home</option>
                                            <option value="condo">Condominium</option>
                                            <option value="townhouse">Townhouse</option>
                                            <option value="multi_family">Multi-Family</option>
                                            <option value="land">Land/Lot</option>
                                            <option value="commercial">Commercial</option>
                                            <option value="mobile_home">Mobile Home</option>
                                            <option value="farm_ranch">Farm/Ranch</option>
                                        </select>
                                    </div>

                                    <!-- Listing Status -->
                                    <div class="col-md-6">
                                        <label for="listing_status" class="form-label">
                                            Listing Status <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="listing_status" name="listing_status" required>
                                            <option value="draft">Draft</option>
                                            <option value="active" selected>Active</option>
                                            <option value="pending">Pending</option>
                                            <option value="sold">Sold</option>
                                            <option value="off_market">Off Market</option>
                                        </select>
                                    </div>

                                    <!-- Property Description -->
                                    <div class="col-12">
                                        <label for="property_description" class="form-label">
                                            Property Description <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control" id="property_description" name="property_description" 
                                                  rows="6" required placeholder="Describe the property features, location, and unique selling points..."></textarea>
                                        <div class="form-text">Provide a detailed, compelling description to attract potential buyers</div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Property Details -->
                    <div class="form-step" data-step="2">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title mb-0">Property Details</h3>
                                <p class="text-muted mb-0">Structural and physical characteristics</p>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    
                                    <!-- Bedrooms -->
                                    <div class="col-md-3">
                                        <label for="bedrooms" class="form-label">Bedrooms</label>
                                        <select class="form-select" id="bedrooms" name="bedrooms">
                                            <option value="">Select</option>
                                            <?php for($i = 0; $i <= 10; $i++): ?>
                                                <option value="<?php echo $i; ?>" <?php echo $i == 3 ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                            <?php endfor; ?>
                                            <option value="10+">10+</option>
                                        </select>
                                    </div>

                                    <!-- Full Bathrooms -->
                                    <div class="col-md-3">
                                        <label for="bathrooms_full" class="form-label">Full Bathrooms</label>
                                        <select class="form-select" id="bathrooms_full" name="bathrooms_full">
                                            <option value="">Select</option>
                                            <?php for($i = 0; $i <= 10; $i++): ?>
                                                <option value="<?php echo $i; ?>" <?php echo $i == 2 ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                        <div class="form-text">Number of full bathrooms</div>
                                    </div>

                                    <!-- Half Bathrooms -->
                                    <div class="col-md-3">
                                        <label for="bathrooms_half" class="form-label">Half Bathrooms</label>
                                        <select class="form-select" id="bathrooms_half" name="bathrooms_half">
                                            <option value="">Select</option>
                                            <?php for($i = 0; $i <= 5; $i++): ?>
                                                <option value="<?php echo $i; ?>" <?php echo $i == 0 ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                        <div class="form-text">Number of half bathrooms</div>
                                    </div>

                                    <!-- Square Feet -->
                                    <div class="col-md-3">
                                        <label for="square_feet" class="form-label">Square Feet</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="square_feet" name="square_feet" 
                                                   min="0" step="1" placeholder="2500">
                                            <span class="input-group-text">sq ft</span>
                                        </div>
                                        <div class="form-text">Total living area</div>
                                    </div>

                                    <!-- Lot Size (Acres) -->
                                    <div class="col-md-4">
                                        <label for="lot_size_acres" class="form-label">Lot Size (Acres)</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="lot_size_acres" name="lot_size_acres" 
                                                   min="0" step="0.01" placeholder="0.25">
                                            <span class="input-group-text">acres</span>
                                        </div>
                                        <div class="form-text">Leave blank for condos</div>
                                    </div>

                                    <!-- Lot Size (Square Feet) -->
                                    <div class="col-md-4">
                                        <label for="lot_size_sqft" class="form-label">Lot Size (Sq Ft)</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="lot_size_sqft" name="lot_size_sqft" 
                                                   min="0" step="100" placeholder="10890">
                                            <span class="input-group-text">sq ft</span>
                                        </div>
                                        <div class="form-text">Alternative to acres</div>
                                    </div>

                                    <!-- Year Built -->
                                    <div class="col-md-4">
                                        <label for="year_built" class="form-label">Year Built</label>
                                        <input type="number" class="form-control" id="year_built" name="year_built" 
                                               min="1800" max="2030" step="1" placeholder="2020">
                                    </div>

                                    <!-- Garage Spaces -->
                                    <div class="col-md-4">
                                        <label for="garage_spaces" class="form-label">Garage Spaces</label>
                                        <select class="form-select" id="garage_spaces" name="garage_spaces">
                                            <option value="">Select</option>
                                            <?php for($i = 0; $i <= 10; $i++): ?>
                                                <option value="<?php echo $i; ?>" <?php echo $i == 2 ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>

                                    <!-- Stories/Levels -->
                                    <div class="col-md-4">
                                        <label for="stories" class="form-label">Stories/Levels</label>
                                        <select class="form-select" id="stories" name="stories">
                                            <option value="">Select</option>
                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                                <option value="<?php echo $i; ?>" <?php echo $i == 1 ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                        <div class="form-text">Number of floors/levels</div>
                                    </div>

                                    <!-- Architectural Style -->
                                    <div class="col-md-4">
                                        <label for="property_style" class="form-label">Architectural Style</label>
                                        <select class="form-select" id="property_style" name="property_style">
                                            <option value="">Select style...</option>
                                            <option value="colonial">Colonial</option>
                                            <option value="contemporary">Contemporary</option>
                                            <option value="traditional">Traditional</option>
                                            <option value="ranch">Ranch</option>
                                            <option value="cape_cod">Cape Cod</option>
                                            <option value="victorian">Victorian</option>
                                            <option value="craftsman">Craftsman</option>
                                            <option value="modern">Modern</option>
                                            <option value="tudor">Tudor</option>
                                            <option value="mediterranean">Mediterranean</option>
                                            <option value="farmhouse">Farmhouse</option>
                                            <option value="two_story">Two Story</option>
                                        </select>
                                    </div>

                                    <!-- Property Condition -->
                                    <div class="col-md-6">
                                        <label for="condition" class="form-label">Property Condition</label>
                                        <select class="form-select" id="condition" name="condition">
                                            <option value="">Select condition...</option>
                                            <option value="excellent">Excellent</option>
                                            <option value="very_good">Very Good</option>
                                            <option value="good">Good</option>
                                            <option value="fair">Fair</option>
                                            <option value="needs_work">Needs Work</option>
                                            <option value="fixer_upper">Fixer Upper</option>
                                        </select>
                                        <div class="form-text">Overall condition of the property</div>
                                    </div>

                                    <!-- Listing Date -->
                                    <div class="col-md-3">
                                        <label for="listing_date" class="form-label">Listing Date</label>
                                        <input type="date" class="form-control" id="listing_date" name="listing_date" 
                                               value="<?php echo date('Y-m-d'); ?>">
                                        <div class="form-text">Date property was listed</div>
                                    </div>

                                    <!-- Days on Market -->
                                    <div class="col-md-3">
                                        <label for="days_on_market" class="form-label">Days on Market</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="days_on_market" name="days_on_market" 
                                                   min="0" step="1" value="0">
                                            <span class="input-group-text">days</span>
                                        </div>
                                        <div class="form-text">Number of days since listing</div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Location & Address -->
                    <div class="form-step" data-step="3">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title mb-0">Location & Address</h3>
                                <p class="text-muted mb-0">Property address and location details</p>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    
                                    <!-- Street Address -->
                                    <div class="col-12">
                                        <label for="street_address" class="form-label">
                                            Street Address <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="street_address" name="street_address" 
                                               required placeholder="123 Main Street">
                                    </div>

                                    <!-- City -->
                                    <div class="col-md-4">
                                        <label for="city" class="form-label">
                                            City <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="city" name="city" 
                                               required placeholder="City Name">
                                    </div>

                                    <!-- State -->
                                    <div class="col-md-4">
                                        <label for="state" class="form-label">
                                            State <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="state" name="state" 
                                               required placeholder="State">
                                    </div>

                                    <!-- ZIP Code -->
                                    <div class="col-md-4">
                                        <label for="zip_code" class="form-label">
                                            ZIP Code <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="zip_code" name="zip_code" 
                                               required placeholder="12345" pattern="[0-9]{5}(-[0-9]{4})?">
                                    </div>

                                    <!-- County -->
                                    <div class="col-md-6">
                                        <label for="county" class="form-label">County</label>
                                        <input type="text" class="form-control" id="county" name="county" 
                                               placeholder="County Name">
                                    </div>

                                    <!-- Neighborhood -->
                                    <div class="col-md-6">
                                        <label for="neighborhood" class="form-label">Neighborhood</label>
                                        <input type="text" class="form-control" id="neighborhood" name="neighborhood" 
                                               placeholder="Neighborhood or Subdivision">
                                    </div>

                                    <!-- Show Address Publicly -->
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="show_address_publicly" 
                                                   name="show_address_publicly" value="1" checked>
                                            <label class="form-check-label" for="show_address_publicly">
                                                Show complete address publicly
                                            </label>
                                            <div class="form-text">Uncheck to only show general area for privacy</div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Financial Information -->
                    <div class="form-step" data-step="4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title mb-0">Financial Information</h3>
                                <p class="text-muted mb-0">Taxes, fees, and financial details</p>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    
                                    <!-- Annual Property Tax -->
                                    <div class="col-md-4">
                                        <label for="property_taxes" class="form-label">Annual Property Tax</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="property_taxes" name="property_taxes" 
                                                   min="0" step="100" placeholder="5000">
                                            <span class="input-group-text">/year</span>
                                        </div>
                                        <div class="form-text">Yearly property tax amount</div>
                                    </div>

                                    <!-- HOA Fees -->
                                    <div class="col-md-4">
                                        <label for="hoa_fees" class="form-label">HOA Fees</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="hoa_fees" name="hoa_fees" 
                                                   min="0" step="10" placeholder="150">
                                            <span class="input-group-text">/month</span>
                                        </div>
                                        <div class="form-text">Monthly HOA fees (if applicable)</div>
                                    </div>

                                    <!-- Utilities Estimate -->
                                    <div class="col-md-4">
                                        <label for="utilities_estimate" class="form-label">Monthly Utilities Estimate</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="utilities_estimate" name="utilities_estimate" 
                                                   min="0" step="25" placeholder="200">
                                            <span class="input-group-text">/month</span>
                                        </div>
                                        <div class="form-text">Estimated monthly utilities cost</div>
                                    </div>

                                    <!-- Commission Rate -->
                                    <div class="col-md-6">
                                        <label for="commission_rate" class="form-label">Commission Rate</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="commission_rate" name="commission_rate" 
                                                   min="0" max="10" step="0.25" placeholder="6.0">
                                            <span class="input-group-text">%</span>
                                        </div>
                                        <div class="form-text">Agent commission percentage</div>
                                    </div>

                                    <!-- Price Per Square Foot -->
                                    <div class="col-md-6">
                                        <label for="price_per_sqft" class="form-label">Price Per Sq Ft</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="price_per_sqft" name="price_per_sqft" 
                                                   min="0" step="1" readonly>
                                        </div>
                                        <div class="form-text">Automatically calculated</div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 5: Features & Amenities -->
                    <div class="form-step" data-step="5">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title mb-0">Features & Amenities</h3>
                                <p class="text-muted mb-0">Property features and special amenities</p>
                            </div>
                            <div class="card-body">
                                
                                <!-- Interior Features -->
                                <div class="mb-4">
                                    <h5 class="mb-3">Interior Features</h5>
                                    <div class="row g-2">
                                        <?php 
                                        $interior_features = [
                                            'hardwood_floors' => 'Hardwood Floors',
                                            'tile_floors' => 'Tile Floors',
                                            'carpet' => 'Carpet',
                                            'laminate_floors' => 'Laminate Floors',
                                            'updated_kitchen' => 'Updated Kitchen',
                                            'granite_counters' => 'Granite Counters',
                                            'stainless_appliances' => 'Stainless Appliances',
                                            'walk_in_closet' => 'Walk-in Closet',
                                            'master_suite' => 'Master Suite',
                                            'laundry_room' => 'Laundry Room',
                                            'storage_space' => 'Storage Space',
                                            'fireplace' => 'Fireplace',
                                            'vaulted_ceilings' => 'Vaulted Ceilings',
                                            'ceiling_fans' => 'Ceiling Fans',
                                            'central_air' => 'Central Air Conditioning',
                                            'forced_air' => 'Forced Air Heating'
                                        ];
                                        
                                        foreach ($interior_features as $key => $label): ?>
                                            <div class="col-md-6 col-lg-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="interior_features[]" 
                                                           value="<?php echo $key; ?>" id="<?php echo $key; ?>">
                                                    <label class="form-check-label" for="<?php echo $key; ?>">
                                                        <?php echo $label; ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Exterior Features -->
                                <div class="mb-4">
                                    <h5 class="mb-3">Exterior Features</h5>
                                    <div class="row g-2">
                                        <?php 
                                        $exterior_features = [
                                            'pool' => 'Swimming Pool',
                                            'spa_hot_tub' => 'Spa/Hot Tub',
                                            'patio_deck' => 'Patio/Deck',
                                            'fenced_yard' => 'Fenced Yard',
                                            'landscaping' => 'Landscaping',
                                            'sprinkler_system' => 'Sprinkler System',
                                            'outdoor_kitchen' => 'Outdoor Kitchen',
                                            'fire_pit' => 'Fire Pit',
                                            'gazebo' => 'Gazebo',
                                            'shed' => 'Storage Shed',
                                            'workshop' => 'Workshop',
                                            'rv_parking' => 'RV Parking',
                                            'boat_dock' => 'Boat Dock',
                                            'tennis_court' => 'Tennis Court',
                                            'basketball_court' => 'Basketball Court',
                                            'playground' => 'Playground'
                                        ];
                                        
                                        foreach ($exterior_features as $key => $label): ?>
                                            <div class="col-md-6 col-lg-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="exterior_features[]" 
                                                           value="<?php echo $key; ?>" id="<?php echo $key; ?>">
                                                    <label class="form-check-label" for="<?php echo $key; ?>">
                                                        <?php echo $label; ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Appliances Included -->
                                <div class="mb-4">
                                    <h5 class="mb-3">Appliances Included</h5>
                                    <div class="row g-2">
                                        <?php 
                                        $appliances = [
                                            'refrigerator' => 'Refrigerator',
                                            'range_oven' => 'Range/Oven',
                                            'dishwasher' => 'Dishwasher',
                                            'microwave' => 'Microwave',
                                            'garbage_disposal' => 'Garbage Disposal',
                                            'washer' => 'Washer',
                                            'dryer' => 'Dryer',
                                            'wine_cooler' => 'Wine Cooler',
                                            'ice_maker' => 'Ice Maker',
                                            'water_softener' => 'Water Softener',
                                            'security_system' => 'Security System',
                                            'intercom' => 'Intercom System'
                                        ];
                                        
                                        foreach ($appliances as $key => $label): ?>
                                            <div class="col-md-6 col-lg-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="appliances[]" 
                                                           value="<?php echo $key; ?>" id="<?php echo $key; ?>">
                                                    <label class="form-check-label" for="<?php echo $key; ?>">
                                                        <?php echo $label; ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Special Features -->
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="special_features" class="form-label">Special Features & Notes</label>
                                        <textarea class="form-control" id="special_features" name="special_features" 
                                                  rows="3" placeholder="Any unique features, recent upgrades, or special notes about the property..."></textarea>
                                        <div class="form-text">Highlight any unique selling points or recent improvements</div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Step 6: Media & Gallery -->
                    <div class="form-step" data-step="6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title mb-0">Media & Gallery</h3>
                                <p class="text-muted mb-0">Upload property images and media</p>
                            </div>
                            <div class="card-body">
                                
                                <!-- Featured Image -->
                                <div class="mb-4">
                                    <label class="form-label">
                                        Featured Image <span class="text-danger">*</span>
                                    </label>
                                    <div class="image-upload-area" id="featuredImageUpload">
                                        <div class="upload-placeholder">
                                            <div class="upload-icon">
                                                <i class="fas fa-cloud-upload-alt fa-3x text-muted"></i>
                                            </div>
                                            <div class="upload-text">
                                                <h5>Click to upload featured image</h5>
                                                <p class="text-muted">or drag and drop</p>
                                            </div>
                                            <div class="upload-specs">
                                                <small class="text-muted">JPG, PNG up to 10MB</small>
                                            </div>
                                        </div>
                                        <input type="file" name="featured_image" accept="image/*" class="file-input" required>
                                        <div class="image-preview" style="display: none;">
                                            <img src="" alt="Featured Image Preview" class="preview-img">
                                            <button type="button" class="btn btn-sm btn-danger remove-image">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="form-text">This will be the main image shown in listings</div>
                                </div>

                                <!-- Gallery Images -->
                                <div class="mb-4">
                                    <label class="form-label">Additional Images</label>
                                    <div class="image-upload-area" id="galleryUpload">
                                        <div class="upload-placeholder">
                                            <div class="upload-icon">
                                                <i class="fas fa-images fa-3x text-muted"></i>
                                            </div>
                                            <div class="upload-text">
                                                <h5>Click to upload more images</h5>
                                                <p class="text-muted">or drag and drop multiple files</p>
                                            </div>
                                            <div class="upload-specs">
                                                <small class="text-muted">JPG, PNG up to 10MB each, maximum 20 images</small>
                                            </div>
                                        </div>
                                        <input type="file" name="gallery_images[]" accept="image/*" multiple class="file-input">
                                        <div class="gallery-preview"></div>
                                    </div>
                                    <div class="form-text">Upload interior, exterior, and detail shots</div>
                                </div>

                                <!-- Virtual Tour URL -->
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="virtual_tour_url" class="form-label">Virtual Tour URL</label>
                                        <input type="url" class="form-control" id="virtual_tour_url" name="virtual_tour_url" 
                                               placeholder="https://example.com/virtual-tour">
                                        <div class="form-text">Link to 360° virtual tour or video walkthrough</div>
                                    </div>

                                    <!-- Video URL -->
                                    <div class="col-md-6">
                                        <label for="video_url" class="form-label">Video URL</label>
                                        <input type="url" class="form-control" id="video_url" name="video_url" 
                                               placeholder="https://youtube.com/watch?v=...">
                                        <div class="form-text">YouTube, Vimeo, or other video platform URL</div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Step 7: Review & Publish -->
                    <div class="form-step" data-step="7">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title mb-0">Review & Publish</h3>
                                <p class="text-muted mb-0">Review your listing before publishing</p>
                            </div>
                            <div class="card-body">
                                
                                <!-- Review Summary -->
                                <div class="review-summary">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h4 id="reviewTitle">Property Title</h4>
                                            <p class="text-muted" id="reviewAddress">Address</p>
                                            <div class="property-highlights mb-3">
                                                <span class="badge bg-primary me-2" id="reviewBedrooms">0 bed</span>
                                                <span class="badge bg-primary me-2" id="reviewBathrooms">0 bath</span>
                                                <span class="badge bg-primary me-2" id="reviewSquareFeet">0 sq ft</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <div class="price-display">
                                                <h3 class="text-success mb-0" id="reviewPrice">$0</h3>
                                                <small class="text-muted" id="reviewPricePerSqft">$0/sq ft</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <div class="review-sections">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6>Property Details</h6>
                                                <ul class="list-unstyled small">
                                                    <li><strong>Type:</strong> <span id="reviewPropertyType">-</span></li>
                                                    <li><strong>Year Built:</strong> <span id="reviewYearBuilt">-</span></li>
                                                    <li><strong>Garage:</strong> <span id="reviewGarage">-</span></li>
                                                    <li><strong>Style:</strong> <span id="reviewStyle">-</span></li>
                                                    <li><strong>Condition:</strong> <span id="reviewCondition">-</span></li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Financial Information</h6>
                                                <ul class="list-unstyled small">
                                                    <li><strong>Property Tax:</strong> <span id="reviewTax">-</span></li>
                                                    <li><strong>HOA Fees:</strong> <span id="reviewHOA">-</span></li>
                                                    <li><strong>Utilities Est:</strong> <span id="reviewUtilities">-</span></li>
                                                    <li><strong>Commission:</strong> <span id="reviewCommission">-</span></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <div class="mb-3">
                                        <h6>Description</h6>
                                        <p class="small" id="reviewDescription">No description provided.</p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <h6>Selected Features</h6>
                                        <div id="reviewFeatures" class="small">
                                            <span class="text-muted">No features selected</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Publishing Options -->
                                <div class="publishing-options mt-4">
                                    <h5>Publishing Options</h5>
                                    
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="final_status" class="form-label">Listing Status</label>
                                            <select class="form-select" id="final_status" name="final_status">
                                                <option value="draft">Save as Draft</option>
                                                <option value="active" selected>Publish (Active)</option>
                                                <option value="coming_soon">Coming Soon</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="agent_assignment" class="form-label">Assigned Agent</label>
                                            <select class="form-select" id="agent_assignment" name="agent_id">
                                                <option value="<?php echo get_current_user_id(); ?>" selected>
                                                    <?php echo $current_user->display_name; ?> (You)
                                                </option>
                                                <?php if (current_user_can('manage_options')): ?>
                                                    <?php 
                                                    $agents = get_users(['role__in' => ['agent', 'administrator']]);
                                                    foreach ($agents as $agent): 
                                                        if ($agent->ID != get_current_user_id()): ?>
                                                            <option value="<?php echo $agent->ID; ?>">
                                                                <?php echo $agent->display_name; ?>
                                                            </option>
                                                        <?php endif;
                                                    endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-info mt-3">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Ready to publish?</strong> Your listing will be visible on the website and available for search once published.
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="form-navigation mt-4">
                        <div class="d-flex justify-content-between">
                            <button type="button" class="hph-btn hph-btn-outline-primary-secondary" id="prevStep" style="visibility: hidden;">
                                <i class="fas fa-chevron-left me-1"></i>
                                Previous
                            </button>
                            
                            <div class="step-info">
                                Step <span id="currentStep">1</span> of <span id="totalSteps">7</span>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="button" class="hph-btn hph-btn-outline-primary-primary" id="saveDraft">
                                    <i class="fas fa-save me-1"></i>
                                    Save Draft
                                </button>
                                
                                <button type="button" class="hph-btn hph-btn-primary" id="nextStep">
                                    Next
                                    <i class="fas fa-chevron-right ms-1"></i>
                                </button>
                                
                                <button type="submit" class="hph-btn hph-btn-success" id="submitListing" style="display: none;">
                                    <i class="fas fa-check me-1"></i>
                                    Publish Listing
                                </button>
                            </div>
                        </div>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>

<style>
/* Form Styles */
.add-listing-page {
    min-height: 100vh;
    background: #f8f9fa;
    padding: 20px 0;
}

.form-progress {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.progress-steps {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
}

.progress-steps::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 25px;
    right: 25px;
    height: 2px;
    background: #e9ecef;
    z-index: 1;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 2;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-bottom: 8px;
    transition: all 0.3s ease;
}

.step.active .step-number,
.step.completed .step-number {
    background: #007bff;
    color: white;
}

.step-label {
    font-size: 12px;
    text-align: center;
    color: #6c757d;
    font-weight: 500;
}

.step.active .step-label {
    color: #007bff;
    font-weight: 600;
}

.form-step {
    display: none;
}

.form-step.active {
    display: block;
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.form-navigation {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.step-info {
    display: flex;
    align-items: center;
    font-weight: 500;
    color: #6c757d;
}

.card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.card-header {
    background: white;
    border-bottom: 1px solid #e9ecef;
}

.form-label {
    font-weight: 500;
    color: #495057;
}

.text-danger {
    color: #dc3545 !important;
}

.form-text {
    font-size: 0.875em;
    color: #6c757d;
}

/* Image Upload Styles */
.image-upload-area {
    border: 2px dashed #e9ecef;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    background: #f8f9fa;
    transition: all 0.3s ease;
    position: relative;
    cursor: pointer;
}

.image-upload-area:hover {
    border-color: #007bff;
    background: #e7f3ff;
}

.image-upload-area.dragover {
    border-color: #28a745;
    background: #e8f5e8;
}

.file-input {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.upload-placeholder {
    pointer-events: none;
}

.upload-icon {
    margin-bottom: 15px;
}

.upload-text h5 {
    margin-bottom: 5px;
    color: #495057;
}

.upload-specs {
    margin-top: 10px;
}

.image-preview {
    position: relative;
    display: inline-block;
    margin: 10px;
}

.preview-img {
    max-width: 150px;
    max-height: 150px;
    border-radius: 8px;
    object-fit: cover;
}

.remove-image {
    position: absolute;
    top: -8px;
    right: -8px;
    width: 24px;
    height: 24px;
    padding: 0;
    border-radius: 50%;
}

.gallery-preview {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 15px;
}

.gallery-preview .image-preview {
    margin: 0;
}

/* Review Summary Styles */
.review-summary {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.property-highlights .badge {
    font-size: 0.875em;
}

.price-display h3 {
    font-size: 2.5rem;
    font-weight: bold;
}

.review-sections h6 {
    font-weight: 600;
    color: #495057;
    margin-bottom: 10px;
}

.review-sections ul li {
    margin-bottom: 3px;
}

/* Publishing Options */
.publishing-options {
    border-top: 1px solid #e9ecef;
    padding-top: 20px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .progress-steps {
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .progress-steps::before {
        display: none;
    }
    
    .step-label {
        font-size: 11px;
    }
    
    .form-navigation .d-flex {
        flex-direction: column;
        gap: 15px;
    }
    
    .form-navigation .d-flex > div:last-child {
        justify-content: center;
    }
    
    .price-display h3 {
        font-size: 2rem;
    }
    
    .review-summary {
        padding: 15px;
    }
    
    .gallery-preview {
        justify-content: center;
    }
}
</style>

<script>
if (window.HPH) {
    HPH.register('addListingPage', function() {
        return {
            init: function() {
                const $ = jQuery;
    
    // Form state
    let currentStep = 1;
    const totalSteps = 7;
    
    // Navigation
    $('#nextStep').click(function() {
        if (validateCurrentStep()) {
            if (currentStep < totalSteps) {
                goToStep(currentStep + 1);
            }
        }
    });
    
    $('#prevStep').click(function() {
        if (currentStep > 1) {
            goToStep(currentStep - 1);
        }
    });
    
    // Step navigation
    function goToStep(step) {
        // Hide current step
        $('.form-step').removeClass('active');
        $('.step').removeClass('active completed');
        
        // Show new step
        $(`.form-step[data-step="${step}"]`).addClass('active');
        $(`.step[data-step="${step}"]`).addClass('active');
        
        // Mark previous steps as completed
        for (let i = 1; i < step; i++) {
            $(`.step[data-step="${i}"]`).addClass('completed');
        }
        
        currentStep = step;
        $('#currentStep').text(step);
        
        // Update navigation buttons
        if (step === 1) {
            $('#prevStep').css('visibility', 'hidden');
        } else {
            $('#prevStep').css('visibility', 'visible');
        }
        
        if (step === totalSteps) {
            HPH.dom.hide('#nextStep');
            HPH.dom.show('#submitListing');
        } else {
            HPH.dom.show('#nextStep');
            HPH.dom.hide('#submitListing');
        }
        
        // Update review summary if moving to step 7
        if (step === 7) {
            updateReviewSummary();
        }
        
        // Scroll to top
        $('html, body').animate({ scrollTop: 0 }, 300);
    }
    
    // Form validation
    function validateCurrentStep() {
        const currentStepElement = $(`.form-step[data-step="${currentStep}"]`);
        const requiredFields = currentStepElement.find('[required]');
        let isValid = true;
        
        requiredFields.each(function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (!isValid) {
            alert('Please fill in all required fields before continuing.');
        }
        
        return isValid;
    }
    
    // Auto-calculate price per square foot
    $('#listing_price, #square_feet').on('input', function() {
        const price = parseFloat($('#listing_price').val()) || 0;
        const sqft = parseFloat($('#square_feet').val()) || 0;
        
        if (price > 0 && sqft > 0) {
            const pricePerSqft = Math.round(price / sqft);
            $('#price_per_sqft').val(pricePerSqft);
        } else {
            $('#price_per_sqft').val('');
        }
    });
    
    // Image Upload Handlers
    $('.image-upload-area').each(function() {
        const uploadArea = $(this);
        const fileInput = uploadArea.find('input[type="file"]');
        const preview = uploadArea.find('.image-preview');
        const galleryPreview = uploadArea.find('.gallery-preview');
        const placeholder = uploadArea.find('.upload-placeholder');
        
        // Click to upload
        uploadArea.on('click', function(e) {
            if (e.target === this || $(e.target).closest('.upload-placeholder').length) {
                fileInput.click();
            }
        });
        
        // Drag and drop
        uploadArea.on('dragover dragenter', function(e) {
            e.preventDefault();
            uploadArea.addClass('dragover');
        });
        
        uploadArea.on('dragleave dragend', function(e) {
            e.preventDefault();
            uploadArea.removeClass('dragover');
        });
        
        uploadArea.on('drop', function(e) {
            e.preventDefault();
            uploadArea.removeClass('dragover');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                fileInput[0].files = files;
                handleFileUpload(fileInput[0]);
            }
        });
        
        // File input change
        fileInput.on('change', function() {
            handleFileUpload(this);
        });
        
        // Remove image
        uploadArea.on('click', '.remove-image', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const imagePreview = $(this).closest('.image-preview');
            imagePreview.remove();
            
            // Reset file input if it's the featured image
            if (uploadArea.attr('id') === 'featuredImageUpload') {
                fileInput.val('');
                placeholder.show();
            }
        });
    });
    
    function handleFileUpload(input) {
        const uploadArea = $(input).closest('.image-upload-area');
        const preview = uploadArea.find('.image-preview').first();
        const galleryPreview = uploadArea.find('.gallery-preview');
        const placeholder = uploadArea.find('.upload-placeholder');
        const isGallery = uploadArea.attr('id') === 'galleryUpload';
        
        if (input.files && input.files.length > 0) {
            if (isGallery) {
                // Handle multiple gallery images
                galleryPreview.empty();
                placeholder.hide();
                
                Array.from(input.files).forEach(function(file, index) {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const imageDiv = $(`
                                <div class="image-preview">
                                    <img src="${e.target.result}" alt="Gallery Image ${index + 1}" class="preview-img">
                                    <button type="button" class="btn btn-sm btn-danger remove-image">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            `);
                            galleryPreview.append(imageDiv);
                        };
                        reader.readAsDataURL(file);
                    }
                });
                
                if (input.files.length === 0) {
                    placeholder.show();
                }
            } else {
                // Handle single featured image
                const file = input.files[0];
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.find('img').attr('src', e.target.result);
                        preview.show();
                        placeholder.hide();
                    };
                    reader.readAsDataURL(file);
                }
            }
        }
    }
    
    // Update review summary when moving to step 7
    function updateReviewSummary() {
        // Basic information
        $('#reviewTitle').text($('#post_title').val() || 'Property Title');
        const address = [
            $('#street_address').val(),
            $('#city').val(),
            $('#state').val(),
            $('#zip_code').val()
        ].filter(Boolean).join(', ');
        $('#reviewAddress').text(address || 'Address not provided');
        
        // Property details
        const bedrooms = $('#bedrooms').val() || 0;
        const fullBath = parseFloat($('#bathrooms_full').val()) || 0;
        const halfBath = parseFloat($('#bathrooms_half').val()) || 0;
        const totalBath = fullBath + (halfBath * 0.5);
        const sqft = $('#square_feet').val() || 0;
        
        $('#reviewBedrooms').text(`${bedrooms} bed`);
        $('#reviewBathrooms').text(`${totalBath} bath`);
        $('#reviewSquareFeet').text(sqft ? `${Number(sqft).toLocaleString()} sq ft` : '0 sq ft');
        
        // Price
        const price = parseFloat($('#listing_price').val()) || 0;
        const pricePerSqft = parseFloat($('#price_per_sqft').val()) || 0;
        $('#reviewPrice').text(price ? `$${Number(price).toLocaleString()}` : '$0');
        $('#reviewPricePerSqft').text(pricePerSqft ? `$${pricePerSqft}/sq ft` : '$0/sq ft');
        
        // Property details section
        $('#reviewPropertyType').text($('#property_type option:selected').text() || '-');
        $('#reviewYearBuilt').text($('#year_built').val() || '-');
        $('#reviewGarage').text($('#garage_spaces').val() ? `${$('#garage_spaces').val()} spaces` : '-');
        $('#reviewStyle').text($('#property_style option:selected').text() || '-');
        $('#reviewCondition').text($('#condition option:selected').text() || '-');
        
        // Financial information
        const propertyTax = parseFloat($('#property_taxes').val()) || 0;
        const hoaFees = parseFloat($('#hoa_fees').val()) || 0;
        const utilities = parseFloat($('#utilities_estimate').val()) || 0;
        const commission = parseFloat($('#commission_rate').val()) || 0;
        
        $('#reviewTax').text(propertyTax ? `$${Number(propertyTax).toLocaleString()}/year` : '-');
        $('#reviewHOA').text(hoaFees ? `$${Number(hoaFees).toLocaleString()}/month` : '-');
        $('#reviewUtilities').text(utilities ? `$${Number(utilities).toLocaleString()}/month` : '-');
        $('#reviewCommission').text(commission ? `${commission}%` : '-');
        
        // Description
        $('#reviewDescription').text($('#property_description').val() || 'No description provided.');
        
        // Features
        const selectedFeatures = [];
        $('input[name="interior_features[]"]:checked, input[name="exterior_features[]"]:checked, input[name="appliances[]"]:checked').each(function() {
            const label = $(this).siblings('label').text();
            selectedFeatures.push(label);
        });
        
        if (selectedFeatures.length > 0) {
            const featuresBadges = selectedFeatures.map(feature => 
                `<span class="badge bg-secondary me-1 mb-1">${feature}</span>`
            ).join('');
            $('#reviewFeatures').html(featuresBadges);
        } else {
            $('#reviewFeatures').html('<span class="text-muted">No features selected</span>');
        }
    }
    
    // Form submission
    $('#addListingForm').submit(function(e) {
        e.preventDefault();
        
        if (!validateCurrentStep()) {
            return;
        }
        
        const formData = new FormData(this);
        const submitBtn = $('#submitListing');
        
        // Show loading state
        submitBtn.prop('disabled', true)
                  .html('<i class="fas fa-spinner fa-spin me-1"></i> Publishing...');
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert('Listing published successfully!');
                    window.location.href = '<?php echo home_url('/user-dashboard/'); ?>';
                } else {
                    alert('Error: ' + (response.data || 'Unknown error occurred'));
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            },
            complete: function() {
                submitBtn.prop('disabled', false)
                          .html('<i class="fas fa-check me-1"></i> Publish Listing');
            }
        });
    });
    
    // Save draft functionality
    $('#saveDraft').click(function() {
        const formData = new FormData($('#addListingForm')[0]);
        formData.set('listing_status', 'draft');
        
        const btn = $(this);
        btn.prop('disabled', true)
           .html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST', 
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert('Draft saved successfully!');
                    if (response.data && response.data.listing_id) {
                        $('#listingId').val(response.data.listing_id);
                    }
                } else {
                    alert('Error saving draft: ' + (response.data || 'Unknown error'));
                }
            },
            error: function() {
                alert('Error saving draft. Please try again.');
            },
            complete: function() {
                btn.prop('disabled', false)
                   .html('<i class="fas fa-save me-1"></i> Save Draft');
            }
        });
    });
    
    // Remove validation errors on input
    $('input, select, textarea').on('input change', function() {
        $(this).removeClass('is-invalid');
    });
    
                console.log('Add Listing Form initialized');
            }
        };
    });
}
</script>

<?php get_footer(); ?>
