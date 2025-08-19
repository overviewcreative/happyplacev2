<?php
/**
 * Listing Form Steps 2-5
 * Additional form steps for the listing form modal
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Step 2: Address & Location -->
<div class="form-step" data-step="2">
    <h4 class="step-heading">
        <i class="fas fa-map-marker-alt me-2"></i>
        Address & Location
    </h4>
    
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        Enter the street address components below. Location intelligence data will be automatically populated.
    </div>

    <div class="row">
        <!-- Street Number -->
        <div class="col-md-2 mb-3">
            <label for="street_number" class="form-label">Street Number</label>
            <input type="text" 
                   class="form-control" 
                   id="street_number" 
                   name="street_number" 
                   placeholder="123"
                   maxlength="20"
                   value="<?php echo esc_attr($listing_data['street_number'] ?? ''); ?>">
        </div>

        <!-- Street Name -->
        <div class="col-md-4 mb-3">
            <label for="street_name" class="form-label">Street Name</label>
            <input type="text" 
                   class="form-control" 
                   id="street_name" 
                   name="street_name" 
                   placeholder="Main"
                   maxlength="100"
                   value="<?php echo esc_attr($listing_data['street_name'] ?? ''); ?>">
        </div>

        <!-- Street Type -->
        <div class="col-md-2 mb-3">
            <label for="street_suffix" class="form-label">Street Type</label>
            <select class="form-select" id="street_suffix" name="street_suffix">
                <option value="">Select</option>
                <option value="St" <?php selected($listing_data['street_suffix'] ?? '', 'St'); ?>>Street</option>
                <option value="Ave" <?php selected($listing_data['street_suffix'] ?? '', 'Ave'); ?>>Avenue</option>
                <option value="Blvd" <?php selected($listing_data['street_suffix'] ?? '', 'Blvd'); ?>>Boulevard</option>
                <option value="Dr" <?php selected($listing_data['street_suffix'] ?? '', 'Dr'); ?>>Drive</option>
                <option value="Rd" <?php selected($listing_data['street_suffix'] ?? '', 'Rd'); ?>>Road</option>
                <option value="Ln" <?php selected($listing_data['street_suffix'] ?? '', 'Ln'); ?>>Lane</option>
                <option value="Ct" <?php selected($listing_data['street_suffix'] ?? '', 'Ct'); ?>>Court</option>
                <option value="Pl" <?php selected($listing_data['street_suffix'] ?? '', 'Pl'); ?>>Place</option>
                <option value="Way" <?php selected($listing_data['street_suffix'] ?? '', 'Way'); ?>>Way</option>
                <option value="Cir" <?php selected($listing_data['street_suffix'] ?? '', 'Cir'); ?>>Circle</option>
                <option value="Trl" <?php selected($listing_data['street_suffix'] ?? '', 'Trl'); ?>>Trail</option>
                <option value="Pkwy" <?php selected($listing_data['street_suffix'] ?? '', 'Pkwy'); ?>>Parkway</option>
            </select>
        </div>

        <!-- Unit Number -->
        <div class="col-md-4 mb-3">
            <label for="unit_number" class="form-label">Unit/Apt (Optional)</label>
            <input type="text" 
                   class="form-control" 
                   id="unit_number" 
                   name="unit_number" 
                   placeholder="Unit 2A"
                   maxlength="50"
                   value="<?php echo esc_attr($listing_data['unit_number'] ?? ''); ?>">
        </div>
    </div>

    <div class="row">
        <!-- City -->
        <div class="col-md-4 mb-3">
            <label for="city" class="form-label">City</label>
            <input type="text" 
                   class="form-control" 
                   id="city" 
                   name="city" 
                   placeholder="Georgetown"
                   maxlength="100"
                   value="<?php echo esc_attr($listing_data['city'] ?? ''); ?>">
        </div>

        <!-- State -->
        <div class="col-md-4 mb-3">
            <label for="state" class="form-label">State</label>
            <select class="form-select" id="state" name="state">
                <option value="">Select State</option>
                <option value="DE" <?php selected($listing_data['state'] ?? '', 'DE'); ?>>Delaware</option>
                <option value="MD" <?php selected($listing_data['state'] ?? '', 'MD'); ?>>Maryland</option>
                <option value="AL" <?php selected($listing_data['state'] ?? '', 'AL'); ?>>Alabama</option>
                <option value="AK" <?php selected($listing_data['state'] ?? '', 'AK'); ?>>Alaska</option>
                <option value="AZ" <?php selected($listing_data['state'] ?? '', 'AZ'); ?>>Arizona</option>
                <option value="AR" <?php selected($listing_data['state'] ?? '', 'AR'); ?>>Arkansas</option>
                <option value="CA" <?php selected($listing_data['state'] ?? '', 'CA'); ?>>California</option>
                <option value="CO" <?php selected($listing_data['state'] ?? '', 'CO'); ?>>Colorado</option>
                <option value="CT" <?php selected($listing_data['state'] ?? '', 'CT'); ?>>Connecticut</option>
                <option value="FL" <?php selected($listing_data['state'] ?? '', 'FL'); ?>>Florida</option>
                <option value="GA" <?php selected($listing_data['state'] ?? '', 'GA'); ?>>Georgia</option>
                <option value="TX" <?php selected($listing_data['state'] ?? '', 'TX'); ?>>Texas</option>
                <option value="NY" <?php selected($listing_data['state'] ?? '', 'NY'); ?>>New York</option>
                <option value="CA" <?php selected($listing_data['state'] ?? '', 'CA'); ?>>California</option>
                <!-- Add more states as needed -->
            </select>
        </div>

        <!-- ZIP Code -->
        <div class="col-md-4 mb-3">
            <label for="zip_code" class="form-label">ZIP Code</label>
            <input type="text" 
                   class="form-control" 
                   id="zip_code" 
                   name="zip_code" 
                   placeholder="19947"
                   maxlength="10"
                   value="<?php echo esc_attr($listing_data['zip_code'] ?? ''); ?>">
        </div>
    </div>

    <div class="row">
        <!-- Parcel Number -->
        <div class="col-md-6 mb-3">
            <label for="parcel_number" class="form-label">Parcel Number (Optional)</label>
            <input type="text" 
                   class="form-control" 
                   id="parcel_number" 
                   name="parcel_number" 
                   placeholder="Enter parcel number"
                   maxlength="50"
                   value="<?php echo esc_attr($listing_data['parcel_number'] ?? ''); ?>">
        </div>

        <!-- Address Visibility -->
        <div class="col-md-6 mb-3">
            <label for="address_visibility" class="form-label">Address Display</label>
            <select class="form-select" id="address_visibility" name="address_visibility">
                <option value="full" <?php selected($listing_data['address_visibility'] ?? 'full', 'full'); ?>>Show Full Address</option>
                <option value="partial" <?php selected($listing_data['address_visibility'] ?? '', 'partial'); ?>>Show Street Only</option>
                <option value="city_only" <?php selected($listing_data['address_visibility'] ?? '', 'city_only'); ?>>Show City/State Only</option>
                <option value="hidden" <?php selected($listing_data['address_visibility'] ?? '', 'hidden'); ?>>Do Not Display</option>
            </select>
            <div class="form-text">How should the address be displayed publicly?</div>
        </div>
    </div>
</div>

<!-- Step 3: Features & Amenities -->
<div class="form-step" data-step="3">
    <h4 class="step-heading">
        <i class="fas fa-star me-2"></i>
        Features & Amenities
    </h4>

    <!-- Interior Features -->
    <div class="mb-4">
        <label class="form-label">Interior Features</label>
        <div class="form-text mb-3">Select all applicable interior features</div>
        
        <div class="row">
            <?php 
            $interior_features = [
                'hardwood_floors' => 'Hardwood Floors',
                'tile_floors' => 'Tile Floors',
                'carpet' => 'Carpet',
                'laminate_floors' => 'Laminate Floors',
                'granite_countertops' => 'Granite Countertops',
                'quartz_countertops' => 'Quartz Countertops',
                'stainless_appliances' => 'Stainless Steel Appliances',
                'updated_kitchen' => 'Updated Kitchen',
                'island_kitchen' => 'Kitchen Island',
                'walk_in_closets' => 'Walk-in Closets',
                'master_suite' => 'Master Suite',
                'fireplace' => 'Fireplace',
                'ceiling_fans' => 'Ceiling Fans',
                'crown_molding' => 'Crown Molding',
                'built_in_shelving' => 'Built-in Shelving',
                'pantry' => 'Pantry',
                'laundry_room' => 'Laundry Room',
                'office_study' => 'Office/Study',
                'bonus_room' => 'Bonus Room',
                'high_ceilings' => 'High Ceilings',
                'skylights' => 'Skylights'
            ];

            $selected_interior = $listing_data['interior_features'] ?? array();
            if (!is_array($selected_interior)) {
                $selected_interior = array();
            }

            foreach ($interior_features as $key => $label) : ?>
                <div class="col-md-4 col-lg-3 mb-2">
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="interior_<?php echo $key; ?>" 
                               name="interior_features[]" 
                               value="<?php echo $key; ?>"
                               <?php checked(in_array($key, $selected_interior)); ?>>
                        <label class="form-check-label" for="interior_<?php echo $key; ?>">
                            <?php echo esc_html($label); ?>
                        </label>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Exterior Features -->
    <div class="mb-4">
        <label class="form-label">Exterior Features</label>
        <div class="form-text mb-3">Select all applicable exterior features</div>
        
        <div class="row">
            <?php 
            $exterior_features = [
                'pool' => 'Swimming Pool',
                'hot_tub' => 'Hot Tub/Spa',
                'deck' => 'Deck',
                'patio' => 'Patio',
                'balcony' => 'Balcony',
                'fenced_yard' => 'Fenced Yard',
                'landscaping' => 'Professional Landscaping',
                'sprinkler_system' => 'Sprinkler System',
                'outdoor_kitchen' => 'Outdoor Kitchen',
                'fire_pit' => 'Fire Pit',
                'gazebo' => 'Gazebo',
                'shed' => 'Storage Shed',
                'workshop' => 'Workshop',
                'carport' => 'Carport',
                'circular_drive' => 'Circular Driveway'
            ];

            $selected_exterior = $listing_data['exterior_features'] ?? array();
            if (!is_array($selected_exterior)) {
                $selected_exterior = array();
            }

            foreach ($exterior_features as $key => $label) : ?>
                <div class="col-md-4 col-lg-3 mb-2">
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="exterior_<?php echo $key; ?>" 
                               name="exterior_features[]" 
                               value="<?php echo $key; ?>"
                               <?php checked(in_array($key, $selected_exterior)); ?>>
                        <label class="form-check-label" for="exterior_<?php echo $key; ?>">
                            <?php echo esc_html($label); ?>
                        </label>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Step 4: Media & Photos -->
<div class="form-step" data-step="4">
    <h4 class="step-heading">
        <i class="fas fa-camera me-2"></i>
        Media & Photos
    </h4>

    <!-- Featured Image -->
    <div class="mb-4">
        <label for="featured_image" class="form-label">Featured Image</label>
        <div class="form-text mb-3">Upload the main property photo that will be displayed as the featured image</div>
        
        <div class="featured-image-upload">
            <input type="file" 
                   class="form-control" 
                   id="featured_image" 
                   name="featured_image" 
                   accept="image/*">
            <div class="featured-image-preview mt-3" id="featuredImagePreview" style="display: none;">
                <img src="" alt="Featured Image Preview" class="img-thumbnail" style="max-width: 300px;">
                <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removePreviewImage('featuredImagePreview')">
                    <i class="fas fa-trash me-1"></i> Remove
                </button>
            </div>
        </div>
    </div>

    <!-- Property Gallery -->
    <div class="mb-4">
        <label for="property_gallery" class="form-label">Property Gallery</label>
        <div class="form-text mb-3">Upload additional property photos (multiple files allowed)</div>
        
        <div class="gallery-upload">
            <input type="file" 
                   class="form-control" 
                   id="property_gallery" 
                   name="property_gallery[]" 
                   accept="image/*" 
                   multiple>
            <div class="gallery-preview mt-3" id="galleryPreview">
                <!-- Gallery previews will be added here via JavaScript -->
            </div>
        </div>
    </div>

    <!-- Virtual Tour URL -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="virtual_tour_url" class="form-label">Virtual Tour URL</label>
            <input type="url" 
                   class="form-control" 
                   id="virtual_tour_url" 
                   name="virtual_tour_url" 
                   placeholder="https://example.com/virtual-tour"
                   value="<?php echo esc_attr($listing_data['virtual_tour_url'] ?? ''); ?>">
            <div class="form-text">Link to 360° virtual tour or video walkthrough</div>
        </div>

        <!-- Video URL -->
        <div class="col-md-6 mb-3">
            <label for="video_url" class="form-label">Video URL</label>
            <input type="url" 
                   class="form-control" 
                   id="video_url" 
                   name="video_url" 
                   placeholder="https://youtube.com/watch?v=..."
                   value="<?php echo esc_attr($listing_data['video_url'] ?? ''); ?>">
            <div class="form-text">YouTube, Vimeo, or other video platform URL</div>
        </div>
    </div>
</div>

<!-- Step 5: Financial Information -->
<div class="form-step" data-step="5">
    <h4 class="step-heading">
        <i class="fas fa-dollar-sign me-2"></i>
        Financial Information
    </h4>

    <div class="row">
        <!-- HOA Fees -->
        <div class="col-md-4 mb-3">
            <label for="hoa_fees" class="form-label">HOA Fees</label>
            <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" 
                       class="form-control" 
                       id="hoa_fees" 
                       name="hoa_fees" 
                       placeholder="250"
                       min="0"
                       value="<?php echo esc_attr($listing_data['hoa_fees'] ?? ''); ?>">
                <span class="input-group-text">/month</span>
            </div>
        </div>

        <!-- Property Taxes -->
        <div class="col-md-4 mb-3">
            <label for="property_taxes" class="form-label">Annual Property Taxes</label>
            <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" 
                       class="form-control" 
                       id="property_taxes" 
                       name="property_taxes" 
                       placeholder="5000"
                       min="0"
                       value="<?php echo esc_attr($listing_data['property_taxes'] ?? ''); ?>">
                <span class="input-group-text">/year</span>
            </div>
        </div>

        <!-- Insurance -->
        <div class="col-md-4 mb-3">
            <label for="insurance_cost" class="form-label">Annual Insurance</label>
            <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" 
                       class="form-control" 
                       id="insurance_cost" 
                       name="insurance_cost" 
                       placeholder="1200"
                       min="0"
                       value="<?php echo esc_attr($listing_data['insurance_cost'] ?? ''); ?>">
                <span class="input-group-text">/year</span>
            </div>
        </div>
    </div>

    <!-- Financial Summary -->
    <div class="alert alert-light">
        <h6><i class="fas fa-calculator me-2"></i>Estimated Monthly Payment</h6>
        <div id="monthlyPaymentCalculator">
            <div class="row">
                <div class="col-md-6">
                    <small class="text-muted">Principal & Interest:</small>
                    <div id="principalInterest">$0</div>
                </div>
                <div class="col-md-6">
                    <small class="text-muted">Total Monthly Payment:</small>
                    <div id="totalMonthly">$0</div>
                </div>
            </div>
        </div>
    </div>
</div>