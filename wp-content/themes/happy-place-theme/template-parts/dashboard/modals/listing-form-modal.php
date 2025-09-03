<?php
/**
 * Listing Form Modal
 * Modal for adding/editing property listings from dashboard
 * 
 * @package HappyPlaceTheme
 */

// Security check
if (!is_user_logged_in()) {
    return;
}

$current_user = wp_get_current_user();
$user_roles = $current_user->roles;
$is_agent = in_array('agent', $user_roles) || in_array('administrator', $user_roles);

if (!$is_agent) {
    return;
}
?>

<div id="listingFormModal" style="
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
    align-items: center;
    justify-content: center;
">
    <div id="listingFormOverlay" style="
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
    "></div>
    
    <div style="
        position: relative;
        background: var(--hph-white);
        border-radius: var(--hph-border-radius-lg);
        box-shadow: var(--hph-shadow-xl);
        max-width: 90vw;
        max-height: 90vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        width: 800px;
    ">
        <div style="
            padding: var(--hph-padding-6);
            border-bottom: 1px solid var(--hph-gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
        ">
            <h3 id="listingFormTitle" style="
                font-size: var(--hph-text-xl);
                font-weight: var(--hph-font-semibold);
                color: var(--hph-gray-900);
                margin: 0;
            ">
                <?php _e('Add New Listing', 'happy-place-theme'); ?>
            </h3>
            <button id="closeListingForm" aria-label="<?php _e('Close', 'happy-place-theme'); ?>" style="
                background: none;
                border: none;
                font-size: var(--hph-text-xl);
                color: var(--hph-gray-400);
                cursor: pointer;
                padding: var(--hph-padding-1);
            ">
                <span>&times;</span>
            </button>
        </div>
        
        <div style="
            flex: 1;
            padding: var(--hph-padding-6);
            overflow-y: auto;
        ">
            
            <!-- Form Steps Indicator -->
            <div class="hph-form-steps">
                <div class="hph-step active" data-step="1">
                    <div class="hph-step-number">1</div>
                    <div class="hph-step-label"><?php _e('Basic Info', 'happy-place-theme'); ?></div>
                </div>
                <div class="hph-step" data-step="2">
                    <div class="hph-step-number">2</div>
                    <div class="hph-step-label"><?php _e('Details', 'happy-place-theme'); ?></div>
                </div>
                <div class="hph-step" data-step="3">
                    <div class="hph-step-number">3</div>
                    <div class="hph-step-label"><?php _e('Images', 'happy-place-theme'); ?></div>
                </div>
                <div class="hph-step" data-step="4">
                    <div class="hph-step-number">4</div>
                    <div class="hph-step-label"><?php _e('Review', 'happy-place-theme'); ?></div>
                </div>
            </div>

            <form id="listingForm" class="hph-listing-form" enctype="multipart/form-data">
                
                <!-- Hidden Fields -->
                <input type="hidden" id="listingId" name="listing_id" value="">
                <input type="hidden" id="formAction" name="action" value="add_listing">
                <?php wp_nonce_field('hph_listing_form', 'listing_nonce'); ?>

                <!-- Step 1: Basic Information -->
                <div class="hph-form-step hph-step-1 active">
                    <div class="hph-step-content">
                        <h4 class="hph-step-title"><?php _e('Basic Property Information', 'happy-place-theme'); ?></h4>
                        
                        <div class="hph-form-grid">
                            <!-- Listing Title -->
                            <div class="hph-form-group hph-form-full">
                                <label for="listingTitle" class="hph-form-label">
                                    <?php _e('Property Title', 'happy-place-theme'); ?>
                                    <span class="hph-required">*</span>
                                </label>
                                <input type="text" id="listingTitle" name="listing_title" class="hph-form-input" required 
                                       placeholder="<?php _e('e.g., Beautiful 3BR Home in Downtown', 'happy-place-theme'); ?>">
                                <div class="hph-form-help"><?php _e('Create an attractive title for your listing', 'happy-place-theme'); ?></div>
                            </div>

                            <!-- Price -->
                            <div class="hph-form-group">
                                <label for="listingPrice" class="hph-form-label">
                                    <?php _e('Price', 'happy-place-theme'); ?>
                                    <span class="hph-required">*</span>
                                </label>
                                <div class="hph-input-group">
                                    <span class="hph-input-prefix">$</span>
                                    <input type="number" id="listingPrice" name="price" class="hph-form-input" required 
                                           placeholder="0" min="0" step="1000">
                                </div>
                            </div>

                            <!-- Property Type -->
                            <div class="hph-form-group">
                                <label for="propertyType" class="hph-form-label">
                                    <?php _e('Property Type', 'happy-place-theme'); ?>
                                    <span class="hph-required">*</span>
                                </label>
                                <select id="propertyType" name="property_type" class="hph-form-select" required>
                                    <option value=""><?php _e('Select Type', 'happy-place-theme'); ?></option>
                                    <option value="house"><?php _e('House', 'happy-place-theme'); ?></option>
                                    <option value="condo"><?php _e('Condo', 'happy-place-theme'); ?></option>
                                    <option value="townhouse"><?php _e('Townhouse', 'happy-place-theme'); ?></option>
                                    <option value="apartment"><?php _e('Apartment', 'happy-place-theme'); ?></option>
                                    <option value="land"><?php _e('Land/Lot', 'happy-place-theme'); ?></option>
                                    <option value="commercial"><?php _e('Commercial', 'happy-place-theme'); ?></option>
                                </select>
                            </div>

                            <!-- Listing Status -->
                            <div class="hph-form-group">
                                <label for="listingStatus" class="hph-form-label">
                                    <?php _e('Status', 'happy-place-theme'); ?>
                                    <span class="hph-required">*</span>
                                </label>
                                <select id="listingStatus" name="listing_status" class="hph-form-select" required>
                                    <option value="draft"><?php _e('Draft', 'happy-place-theme'); ?></option>
                                    <option value="active"><?php _e('Active', 'happy-place-theme'); ?></option>
                                    <option value="pending"><?php _e('Pending', 'happy-place-theme'); ?></option>
                                    <option value="sold"><?php _e('Sold', 'happy-place-theme'); ?></option>
                                </select>
                            </div>

                            <!-- MLS Number -->
                            <div class="hph-form-group">
                                <label for="mlsNumber" class="hph-form-label"><?php _e('MLS Number', 'happy-place-theme'); ?></label>
                                <input type="text" id="mlsNumber" name="mls_number" class="hph-form-input" 
                                       placeholder="<?php _e('Enter MLS #', 'happy-place-theme'); ?>">
                            </div>

                            <!-- Address Fields -->
                            <div class="hph-form-group hph-form-full">
                                <label for="streetAddress" class="hph-form-label">
                                    <?php _e('Street Address', 'happy-place-theme'); ?>
                                    <span class="hph-required">*</span>
                                </label>
                                <input type="text" id="streetAddress" name="street_address" class="hph-form-input" required 
                                       placeholder="<?php _e('123 Main Street', 'happy-place-theme'); ?>">
                            </div>

                            <div class="hph-form-group">
                                <label for="city" class="hph-form-label">
                                    <?php _e('City', 'happy-place-theme'); ?>
                                    <span class="hph-required">*</span>
                                </label>
                                <input type="text" id="city" name="city" class="hph-form-input" required 
                                       placeholder="<?php _e('City Name', 'happy-place-theme'); ?>">
                            </div>

                            <div class="hph-form-group">
                                <label for="state" class="hph-form-label">
                                    <?php _e('State', 'happy-place-theme'); ?>
                                    <span class="hph-required">*</span>
                                </label>
                                <input type="text" id="state" name="state" class="hph-form-input" required 
                                       placeholder="<?php _e('State', 'happy-place-theme'); ?>">
                            </div>

                            <div class="hph-form-group">
                                <label for="zipCode" class="hph-form-label">
                                    <?php _e('ZIP Code', 'happy-place-theme'); ?>
                                    <span class="hph-required">*</span>
                                </label>
                                <input type="text" id="zipCode" name="zip_code" class="hph-form-input" required 
                                       placeholder="<?php _e('12345', 'happy-place-theme'); ?>">
                            </div>

                            <!-- Property Description -->
                            <div class="hph-form-group hph-form-full">
                                <label for="propertyDescription" class="hph-form-label">
                                    <?php _e('Property Description', 'happy-place-theme'); ?>
                                    <span class="hph-required">*</span>
                                </label>
                                <textarea id="propertyDescription" name="property_description" class="hph-form-textarea" rows="5" required 
                                          placeholder="<?php _e('Describe the property features, location, and unique selling points...', 'happy-place-theme'); ?>"></textarea>
                                <div class="hph-form-help"><?php _e('Provide a detailed description to attract potential buyers', 'happy-place-theme'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Property Details -->
                <div class="hph-form-step hph-step-2">
                    <div class="hph-step-content">
                        <h4 class="hph-step-title"><?php _e('Property Details & Features', 'happy-place-theme'); ?></h4>
                        
                        <div class="hph-form-grid">
                            <!-- Bedrooms -->
                            <div class="hph-form-group">
                                <label for="bedrooms" class="hph-form-label"><?php _e('Bedrooms', 'happy-place-theme'); ?></label>
                                <select id="bedrooms" name="bedrooms" class="hph-form-select">
                                    <option value=""><?php _e('Select', 'happy-place-theme'); ?></option>
                                    <?php for ($i = 0; $i <= 10; $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                    <option value="10+"><?php _e('10+', 'happy-place-theme'); ?></option>
                                </select>
                            </div>

                            <!-- Full Bathrooms -->
                            <div class="hph-form-group">
                                <label for="bathroomsFull" class="hph-form-label"><?php _e('Full Bathrooms', 'happy-place-theme'); ?></label>
                                <select id="bathroomsFull" name="bathrooms_full" class="hph-form-select">
                                    <option value=""><?php _e('Select', 'happy-place-theme'); ?></option>
                                    <?php for ($i = 0; $i <= 10; $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <!-- Half Bathrooms -->
                            <div class="hph-form-group">
                                <label for="bathroomsHalf" class="hph-form-label"><?php _e('Half Bathrooms', 'happy-place-theme'); ?></label>
                                <select id="bathroomsHalf" name="bathrooms_half" class="hph-form-select">
                                    <option value=""><?php _e('Select', 'happy-place-theme'); ?></option>
                                    <?php for ($i = 0; $i <= 5; $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <!-- Square Feet -->
                            <div class="hph-form-group">
                                <label for="squareFeet" class="hph-form-label"><?php _e('Square Feet', 'happy-place-theme'); ?></label>
                                <input type="number" id="squareFeet" name="square_feet" class="hph-form-input" 
                                       placeholder="<?php _e('2000', 'happy-place-theme'); ?>" min="0" step="50">
                            </div>

                            <!-- Lot Size -->
                            <div class="hph-form-group">
                                <label for="lotSize" class="hph-form-label"><?php _e('Lot Size (acres)', 'happy-place-theme'); ?></label>
                                <input type="number" id="lotSize" name="lot_size_acres" class="hph-form-input" 
                                       placeholder="<?php _e('0.25', 'happy-place-theme'); ?>" min="0" step="0.01">
                            </div>

                            <!-- Year Built -->
                            <div class="hph-form-group">
                                <label for="yearBuilt" class="hph-form-label"><?php _e('Year Built', 'happy-place-theme'); ?></label>
                                <input type="number" id="yearBuilt" name="year_built" class="hph-form-input" 
                                       placeholder="<?php _e('2020', 'happy-place-theme'); ?>" 
                                       min="1800" max="<?php echo date('Y') + 5; ?>">
                            </div>

                            <!-- Garage -->
                            <div class="hph-form-group">
                                <label for="garage" class="hph-form-label"><?php _e('Garage Spaces', 'happy-place-theme'); ?></label>
                                <select id="garage" name="garage" class="hph-form-select">
                                    <option value=""><?php _e('Select', 'happy-place-theme'); ?></option>
                                    <option value="0"><?php _e('No Garage', 'happy-place-theme'); ?></option>
                                    <?php for ($i = 1; $i <= 10; $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?> <?php _e('Car', 'happy-place-theme'); ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <!-- Property Features -->
                            <div class="hph-form-group hph-form-full">
                                <label class="hph-form-label"><?php _e('Property Features', 'happy-place-theme'); ?></label>
                                <div class="hph-checkbox-grid">
                                    <?php
                                    $features = [
                                        'pool' => __('Pool', 'happy-place-theme'),
                                        'spa' => __('Spa/Hot Tub', 'happy-place-theme'),
                                        'fireplace' => __('Fireplace', 'happy-place-theme'),
                                        'hardwood_floors' => __('Hardwood Floors', 'happy-place-theme'),
                                        'tile_floors' => __('Tile Floors', 'happy-place-theme'),
                                        'updated_kitchen' => __('Updated Kitchen', 'happy-place-theme'),
                                        'stainless_appliances' => __('Stainless Appliances', 'happy-place-theme'),
                                        'granite_counters' => __('Granite Counters', 'happy-place-theme'),
                                        'walk_in_closet' => __('Walk-in Closet', 'happy-place-theme'),
                                        'master_suite' => __('Master Suite', 'happy-place-theme'),
                                        'laundry_room' => __('Laundry Room', 'happy-place-theme'),
                                        'storage' => __('Storage Space', 'happy-place-theme'),
                                        'patio' => __('Patio/Deck', 'happy-place-theme'),
                                        'fenced_yard' => __('Fenced Yard', 'happy-place-theme'),
                                        'air_conditioning' => __('Air Conditioning', 'happy-place-theme'),
                                        'ceiling_fans' => __('Ceiling Fans', 'happy-place-theme'),
                                    ];
                                    
                                    foreach ($features as $key => $label):
                                    ?>
                                        <label class="hph-checkbox-label">
                                            <input type="checkbox" name="features[]" value="<?php echo $key; ?>" class="hph-checkbox">
                                            <span class="hph-checkbox-text"><?php echo $label; ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Images -->
                <div class="hph-form-step hph-step-3">
                    <div class="hph-step-content">
                        <h4 class="hph-step-title"><?php _e('Property Images', 'happy-place-theme'); ?></h4>
                        
                        <!-- Featured Image Upload -->
                        <div class="hph-form-group hph-form-full">
                            <label class="hph-form-label">
                                <?php _e('Featured Image', 'happy-place-theme'); ?>
                                <span class="hph-required">*</span>
                            </label>
                            <div class="hph-image-upload" id="featuredImageUpload">
                                <div class="hph-upload-area">
                                    <div class="hph-upload-icon">
                                        <span class="hph-icon-image"></span>
                                    </div>
                                    <div class="hph-upload-text">
                                        <strong><?php _e('Click to upload featured image', 'happy-place-theme'); ?></strong>
                                        <span><?php _e('or drag and drop', 'happy-place-theme'); ?></span>
                                    </div>
                                    <div class="hph-upload-hint"><?php _e('JPG, PNG up to 10MB', 'happy-place-theme'); ?></div>
                                </div>
                                <input type="file" id="featuredImageInput" name="featured_image" accept="image/*" class="hph-file-input">
                                <div class="hph-image-preview" id="featuredImagePreview" style="display: none;">
                                    <img src="" alt="Preview" class="hph-preview-image">
                                    <button type="button" class="hph-remove-image" id="removeFeaturedImage">
                                        <span class="hph-icon-close">&times;</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Gallery Images Upload -->
                        <div class="hph-form-group hph-form-full">
                            <label class="hph-form-label"><?php _e('Additional Images', 'happy-place-theme'); ?></label>
                            <div class="hph-gallery-upload" id="galleryUpload">
                                <div class="hph-upload-area">
                                    <div class="hph-upload-icon">
                                        <span class="hph-icon-images"></span>
                                    </div>
                                    <div class="hph-upload-text">
                                        <strong><?php _e('Click to upload more images', 'happy-place-theme'); ?></strong>
                                        <span><?php _e('or drag and drop multiple files', 'happy-place-theme'); ?></span>
                                    </div>
                                    <div class="hph-upload-hint"><?php _e('JPG, PNG up to 10MB each', 'happy-place-theme'); ?></div>
                                </div>
                                <input type="file" id="galleryInput" name="gallery_images[]" accept="image/*" multiple class="hph-file-input">
                                <div class="hph-gallery-preview" id="galleryPreview"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Review -->
                <div class="hph-form-step hph-step-4">
                    <div class="hph-step-content">
                        <h4 class="hph-step-title"><?php _e('Review & Publish', 'happy-place-theme'); ?></h4>
                        
                        <div class="hph-review-content">
                            <div class="hph-review-section">
                                <h5 class="hph-review-heading"><?php _e('Basic Information', 'happy-place-theme'); ?></h5>
                                <div class="hph-review-grid" id="reviewBasicInfo">
                                    <!-- Will be populated by JavaScript -->
                                </div>
                            </div>
                            
                            <div class="hph-review-section">
                                <h5 class="hph-review-heading"><?php _e('Property Details', 'happy-place-theme'); ?></h5>
                                <div class="hph-review-grid" id="reviewDetails">
                                    <!-- Will be populated by JavaScript -->
                                </div>
                            </div>
                            
                            <div class="hph-review-section">
                                <h5 class="hph-review-heading"><?php _e('Images', 'happy-place-theme'); ?></h5>
                                <div class="hph-review-images" id="reviewImages">
                                    <!-- Will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </form>
        </div>
        
        <div class="hph-modal-footer">
            <div class="hph-modal-actions">
                <button type="button" class="hph-btn hph-btn-outline" id="prevStep" style="display: none;">
                    <span class="hph-icon-chevron-left"></span>
                    <?php _e('Previous', 'happy-place-theme'); ?>
                </button>
                
                <div class="hph-modal-actions-right">
                    <button type="button" class="hph-btn hph-btn-ghost" id="saveDraft">
                        <?php _e('Save as Draft', 'happy-place-theme'); ?>
                    </button>
                    
                    <button type="button" class="hph-btn hph-btn-primary" id="nextStep">
                        <?php _e('Next', 'happy-place-theme'); ?>
                        <span class="hph-icon-chevron-right"></span>
                    </button>
                    
                    <button type="submit" class="hph-btn hph-btn-success" id="submitListing" style="display: none;">
                        <span class="hph-btn-icon hph-icon-check"></span>
                        <?php _e('Publish Listing', 'happy-place-theme'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>