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

<div id="listingFormModal" class="hph-modal" style="display: none !important;">
    <div id="listingFormOverlay" class="hph-modal-backdrop"></div>
    
    <div class="hph-modal-content hph-listing-modal">
        <!-- Modal Header -->
        <div class="hph-modal-header">
            <h3 id="listingFormTitle" class="hph-modal-title">
                <?php _e('Add New Listing', 'happy-place-theme'); ?>
            </h3>
            <button id="closeListingForm" class="hph-modal-close" aria-label="<?php _e('Close', 'happy-place-theme'); ?>">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="hph-modal-body">
            <!-- Form Progress -->
            <div class="hph-form-progress">
                <div class="hph-progress-bar">
                    <div class="hph-progress-fill" style="width: 25%"></div>
                </div>
                <div class="hph-step-indicators">
                    <div class="hph-step-indicator active" data-step="1">
                        <span class="hph-step-number">1</span>
                        <span class="hph-step-label"><?php _e('Basic Info', 'happy-place-theme'); ?></span>
                    </div>
                    <div class="hph-step-indicator" data-step="2">
                        <span class="hph-step-number">2</span>
                        <span class="hph-step-label"><?php _e('Details', 'happy-place-theme'); ?></span>
                    </div>
                    <div class="hph-step-indicator" data-step="3">
                        <span class="hph-step-number">3</span>
                        <span class="hph-step-label"><?php _e('Images', 'happy-place-theme'); ?></span>
                    </div>
                    <div class="hph-step-indicator" data-step="4">
                        <span class="hph-step-number">4</span>
                        <span class="hph-step-label"><?php _e('Review', 'happy-place-theme'); ?></span>
                    </div>
                </div>
            </div>

            <form id="listingForm" class="hph-listing-form" enctype="multipart/form-data">
                <!-- Hidden Fields -->
                <input type="hidden" id="listingId" name="listing_id" value="">
                <input type="hidden" name="action" value="hph_save_listing">
                <?php wp_nonce_field('hph_listing_form', 'listing_nonce'); ?>

                <!-- Step 1: Basic Information -->
                <div class="hph-form-step active" id="step-1">
                    <div class="hph-step-header">
                        <h4 class="hph-step-title"><?php _e('Basic Property Information', 'happy-place-theme'); ?></h4>
                        <p class="hph-step-description"><?php _e('Enter the essential details about your property listing.', 'happy-place-theme'); ?></p>
                    </div>
                    
                    <div class="hph-form-grid">
                        <!-- Listing Title -->
                        <div class="hph-form-group hph-col-full">
                            <label for="listingTitle" class="hph-form-label">
                                <?php _e('Property Title', 'happy-place-theme'); ?>
                                <span class="hph-required">*</span>
                            </label>
                            <input type="text" id="listingTitle" name="listing_title" class="hph-form-input" required 
                                   placeholder="<?php _e('Beautiful 3BR Home in Downtown', 'happy-place-theme'); ?>">
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
                                       placeholder="450000" min="0" step="1000">
                            </div>
                        </div>

                        <!-- Property Type -->
                        <div class="hph-form-group">
                            <label for="propertyType" class="hph-form-label">
                                <?php _e('Property Type', 'happy-place-theme'); ?>
                                <span class="hph-required">*</span>
                            </label>
                            <select id="modal-propertyType" name="property_type" class="hph-form-select" required>
                                <option value=""><?php _e('Select Type', 'happy-place-theme'); ?></option>
                                <option value="single-family"><?php _e('Single Family Home', 'happy-place-theme'); ?></option>
                                <option value="condo"><?php _e('Condominium', 'happy-place-theme'); ?></option>
                                <option value="townhouse"><?php _e('Townhouse', 'happy-place-theme'); ?></option>
                                <option value="multi-family"><?php _e('Multi-Family', 'happy-place-theme'); ?></option>
                                <option value="land"><?php _e('Land/Lot', 'happy-place-theme'); ?></option>
                                <option value="commercial"><?php _e('Commercial', 'happy-place-theme'); ?></option>
                            </select>
                        </div>

                        <!-- Street Address -->
                        <div class="hph-form-group hph-col-full">
                            <label for="streetAddress" class="hph-form-label">
                                <?php _e('Street Address', 'happy-place-theme'); ?>
                                <span class="hph-required">*</span>
                            </label>
                            <input type="text" id="streetAddress" name="street_address" class="hph-form-input" required 
                                   placeholder="<?php _e('123 Main Street', 'happy-place-theme'); ?>">
                        </div>

                        <!-- City -->
                        <div class="hph-form-group">
                            <label for="city" class="hph-form-label">
                                <?php _e('City', 'happy-place-theme'); ?>
                                <span class="hph-required">*</span>
                            </label>
                            <input type="text" id="city" name="city" class="hph-form-input" required 
                                   placeholder="<?php _e('City', 'happy-place-theme'); ?>">
                        </div>

                        <!-- State -->
                        <div class="hph-form-group">
                            <label for="state" class="hph-form-label">
                                <?php _e('State', 'happy-place-theme'); ?>
                                <span class="hph-required">*</span>
                            </label>
                            <select id="state" name="state" class="hph-form-select" required>
                                <option value=""><?php _e('Select State', 'happy-place-theme'); ?></option>
                                <!-- US States -->
                                <option value="AL">Alabama</option>
                                <option value="AK">Alaska</option>
                                <option value="AZ">Arizona</option>
                                <option value="AR">Arkansas</option>
                                <option value="CA">California</option>
                                <option value="CO">Colorado</option>
                                <option value="CT">Connecticut</option>
                                <option value="DE">Delaware</option>
                                <option value="FL">Florida</option>
                                <option value="GA">Georgia</option>
                                <!-- Add more states as needed -->
                            </select>
                        </div>

                        <!-- ZIP Code -->
                        <div class="hph-form-group">
                            <label for="zipCode" class="hph-form-label">
                                <?php _e('ZIP Code', 'happy-place-theme'); ?>
                                <span class="hph-required">*</span>
                            </label>
                            <input type="text" id="zipCode" name="zip_code" class="hph-form-input" required 
                                   placeholder="12345" pattern="[0-9]{5}(-[0-9]{4})?">
                        </div>

                        <!-- Property Description -->
                        <div class="hph-form-group hph-col-full">
                            <label for="propertyDescription" class="hph-form-label">
                                <?php _e('Property Description', 'happy-place-theme'); ?>
                                <span class="hph-required">*</span>
                            </label>
                            <textarea id="propertyDescription" name="property_description" class="hph-form-textarea" rows="5" required 
                                      placeholder="<?php _e('Describe the property features, location, and unique selling points...', 'happy-place-theme'); ?>"></textarea>
                            <div class="hph-form-help">
                                <?php _e('Provide a detailed description to attract potential buyers', 'happy-place-theme'); ?>
                                <span class="hph-char-count">(<span id="descriptionCount">0</span>/1000 <?php _e('characters', 'happy-place-theme'); ?>)</span>
                            </div>
                        </div>
                <!-- Step 2: Property Details -->
                <div class="hph-form-step" id="step-2" style="display: none;">
                    <div class="hph-step-header">
                        <h4 class="hph-step-title"><?php _e('Property Details & Features', 'happy-place-theme'); ?></h4>
                        <p class="hph-step-description"><?php _e('Add detailed information about the property specifications.', 'happy-place-theme'); ?></p>
                    </div>
                    
                    <div class="hph-form-grid">
                        <!-- Bedrooms -->
                        <div class="hph-form-group">
                            <label for="bedrooms" class="hph-form-label"><?php _e('Bedrooms', 'happy-place-theme'); ?></label>
                            <select id="modal-bedrooms" name="bedrooms" class="hph-form-select">
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
                                   placeholder="2000" min="0" step="50">
                        </div>

                        <!-- Lot Size -->
                        <div class="hph-form-group">
                            <label for="lotSize" class="hph-form-label"><?php _e('Lot Size (acres)', 'happy-place-theme'); ?></label>
                            <input type="number" id="lotSize" name="lot_size_acres" class="hph-form-input" 
                                   placeholder="0.25" min="0" step="0.01">
                        </div>

                        <!-- Year Built -->
                        <div class="hph-form-group">
                            <label for="yearBuilt" class="hph-form-label"><?php _e('Year Built', 'happy-place-theme'); ?></label>
                            <input type="number" id="yearBuilt" name="year_built" class="hph-form-input" 
                                   placeholder="<?php echo date('Y'); ?>" min="1800" max="<?php echo date('Y') + 5; ?>">
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

                        <!-- MLS Number -->
                        <div class="hph-form-group">
                            <label for="mlsNumber" class="hph-form-label"><?php _e('MLS Number', 'happy-place-theme'); ?></label>
                            <input type="text" id="mlsNumber" name="mls_number" class="hph-form-input" 
                                   placeholder="<?php _e('Optional', 'happy-place-theme'); ?>">
                        </div>

                        <!-- Property Features -->
                        <div class="hph-form-group hph-col-full">
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
                                        <input type="checkbox" name="features[]" value="<?php echo esc_attr($key); ?>" class="hph-checkbox">
                                        <span class="hph-checkbox-text"><?php echo esc_html($label); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Images -->
                <div class="hph-form-step" id="step-3" style="display: none;">
                    <div class="hph-step-header">
                        <h4 class="hph-step-title"><?php _e('Property Images', 'happy-place-theme'); ?></h4>
                        <p class="hph-step-description"><?php _e('Upload high-quality images to showcase your property.', 'happy-place-theme'); ?></p>
                    </div>
                    
                    <!-- Featured Image Upload -->
                    <div class="hph-form-group hph-col-full">
                        <label class="hph-form-label">
                            <?php _e('Featured Image', 'happy-place-theme'); ?>
                            <span class="hph-required">*</span>
                        </label>
                        <div class="hph-image-upload" id="featuredImageUpload">
                            <div class="hph-upload-area">
                                <div class="hph-upload-icon">
                                    <i class="fas fa-image"></i>
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
                                <button type="button" class="hph-remove-image" data-remove="featured">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Gallery Images Upload -->
                    <div class="hph-form-group hph-col-full">
                        <label class="hph-form-label"><?php _e('Additional Images', 'happy-place-theme'); ?></label>
                        <div class="hph-gallery-upload" id="galleryUpload">
                            <div class="hph-upload-area">
                                <div class="hph-upload-icon">
                                    <i class="fas fa-images"></i>
                                </div>
                                <div class="hph-upload-text">
                                    <strong><?php _e('Click to upload more images', 'happy-place-theme'); ?></strong>
                                    <span><?php _e('or drag and drop multiple files', 'happy-place-theme'); ?></span>
                                </div>
                                <div class="hph-upload-hint"><?php _e('JPG, PNG up to 10MB each (max 20 images)', 'happy-place-theme'); ?></div>
                            </div>
                            <input type="file" id="galleryInput" name="gallery_images[]" accept="image/*" multiple class="hph-file-input">
                            <div class="hph-gallery-preview" id="galleryPreview"></div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Review -->
                <div class="hph-form-step" id="step-4" style="display: none;">
                    <div class="hph-step-header">
                        <h4 class="hph-step-title"><?php _e('Review & Publish', 'happy-place-theme'); ?></h4>
                        <p class="hph-step-description"><?php _e('Review all information before publishing your listing.', 'happy-place-theme'); ?></p>
                    </div>
                    
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

                        <!-- Status Selection -->
                        <div class="hph-review-section">
                            <h5 class="hph-review-heading"><?php _e('Listing Status', 'happy-place-theme'); ?></h5>
                            <div class="hph-status-options">
                                <div class="hph-form-check">
                                    <input type="radio" name="listing_status" value="draft" checked class="hph-form-check-input">
                                    <label class="hph-form-check-label">
                                        <strong><?php _e('Save as Draft', 'happy-place-theme'); ?></strong>
                                        <small><?php _e('Save for later editing - not visible to public', 'happy-place-theme'); ?></small>
                                    </label>
                                </div>
                                <div class="hph-form-check">
                                    <input type="radio" name="listing_status" value="active" class="hph-form-check-input">
                                    <label class="hph-form-check-label">
                                        <strong><?php _e('Publish Active', 'happy-place-theme'); ?></strong>
                                        <small><?php _e('Make listing live and searchable immediately', 'happy-place-theme'); ?></small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </form>
        </div>
        
        <!-- Modal Footer -->
        <div class="hph-modal-footer">
            <div class="hph-modal-actions">
                <button type="button" class="hph-btn hph-btn-outline-primary" id="prevStep" style="display: none;">
                    <i class="fas fa-chevron-left"></i>
                    <?php _e('Previous', 'happy-place-theme'); ?>
                </button>
                
                <div class="hph-modal-actions-right">
                    <button type="button" class="hph-btn hph-btn-ghost" id="closeListing">
                        <?php _e('Cancel', 'happy-place-theme'); ?>
                    </button>
                    
                    <button type="button" class="hph-btn hph-btn-primary" id="nextStep">
                        <?php _e('Next', 'happy-place-theme'); ?>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    
                    <button type="submit" class="hph-btn hph-btn-success" id="submitListing" style="display: none;">
                        <i class="fas fa-check"></i>
                        <?php _e('Save Listing', 'happy-place-theme'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let currentStep = 1;
    const totalSteps = 4;
    
    // Form step management
    function showStep(stepNumber) {
        // Hide all steps
        $('.hph-form-step').hide();
        $('.hph-step-indicator').removeClass('active');
        
        // Show current step
        $('#step-' + stepNumber).show();
        $(`.hph-step-indicator[data-step="${stepNumber}"]`).addClass('active');
        
        // Update progress bar
        const progressPercent = (stepNumber / totalSteps) * 100;
        $('.hph-progress-fill').css('width', progressPercent + '%');
        
        // Update buttons
        if (stepNumber === 1) {
            $('#prevStep').hide();
        } else {
            $('#prevStep').show();
        }
        
        if (stepNumber === totalSteps) {
            $('#nextStep').hide();
            $('#submitListing').show();
        } else {
            $('#nextStep').show();
            $('#submitListing').hide();
        }
        
        currentStep = stepNumber;
    }
    
    // Next step button
    $('#nextStep').on('click', function() {
        if (validateCurrentStep()) {
            if (currentStep < totalSteps) {
                showStep(currentStep + 1);
                
                // Populate review step if we're going to step 4
                if (currentStep === 4) {
                    populateReviewStep();
                }
            }
        }
    });
    
    // Previous step button
    $('#prevStep').on('click', function() {
        if (currentStep > 1) {
            showStep(currentStep - 1);
        }
    });
    
    // Close modal buttons
    $('#closeListing, #closeListingForm').on('click', function() {
        if (typeof window.closeListingFormModal === 'function') {
            window.closeListingFormModal();
        } else {
            // Fallback
            const modal = document.getElementById('listingFormModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }
        }
    });
    
    // Form submission
    $('#submitListing').on('click', function(e) {
        e.preventDefault();
        submitListingForm();
    });
    
    // Step indicator clicks
    $('.hph-step-indicator').on('click', function() {
        const stepNumber = parseInt($(this).data('step'));
        if (stepNumber < currentStep || validateStepsUpTo(stepNumber - 1)) {
            showStep(stepNumber);
        }
    });
    
    // Validate current step
    function validateCurrentStep() {
        const currentStepElement = $('#step-' + currentStep);
        const requiredFields = currentStepElement.find('[required]');
        let isValid = true;
        
        requiredFields.each(function() {
            const field = $(this);
            if (!field.val().trim()) {
                field.addClass('error');
                isValid = false;
                
                // Show error message
                if (!field.next('.error-message').length) {
                    field.after('<div class="error-message" style="color: #dc3545; font-size: 0.875rem; margin-top: 0.25rem;">This field is required</div>');
                }
            } else {
                field.removeClass('error');
                field.next('.error-message').remove();
            }
        });
        
        if (!isValid) {
            if (typeof window.showNotification === 'function') {
                window.showNotification('Please fill in all required fields', 'error');
            } else {
                alert('Please fill in all required fields');
            }
        }
        
        return isValid;
    }
    
    // Submit form
    function submitListingForm() {
        const submitBtn = $('#submitListing');
        const originalText = submitBtn.html();
        
        // Show loading state
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
        
        // Create FormData object
        const formData = new FormData(document.getElementById('listingForm'));
        formData.append('action', 'hph_save_listing');
        formData.append('nonce', $('#hph_dashboard_nonce').val());
        
        // Add listing title from the correct field
        formData.set('listing_title', $('#listingTitle').val());
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    if (typeof window.showNotification === 'function') {
                        window.showNotification(response.data.message, 'success');
                    } else {
                        alert(response.data.message);
                    }
                    
                    if (typeof window.closeListingFormModal === 'function') {
                        window.closeListingFormModal();
                    }
                    
                    // Refresh the listings dashboard
                    if (window.ListingsDashboard) {
                        ListingsDashboard.loadListings();
                        ListingsDashboard.loadStats();
                    }
                } else {
                    const errorMsg = 'Error: ' + (response.data || 'Unknown error occurred');
                    if (typeof window.showNotification === 'function') {
                        window.showNotification(errorMsg, 'error');
                    } else {
                        alert(errorMsg);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                const errorMsg = 'Network error. Please try again.';
                if (typeof window.showNotification === 'function') {
                    window.showNotification(errorMsg, 'error');
                } else {
                    alert(errorMsg);
                }
            },
            complete: function() {
                // Reset button state
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    }
    
    // Initialize first step
    showStep(1);
});
</script>

<style>
.hph-modal {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    background: rgba(0,0,0,0.8) !important;
    z-index: 999999 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    overflow-y: auto !important;
    visibility: visible !important;
    opacity: 1 !important;
}

.hph-modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.hph-modal-content {
    background: white !important;
    border-radius: 12px !important;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3) !important;
    max-width: 800px !important;
    width: 90vw !important;
    max-height: 90vh !important;
    position: relative !important;
    z-index: 1000000 !important;
    display: flex !important;
    flex-direction: column !important;
    visibility: visible !important;
    opacity: 1 !important;
}

.hph-modal-header {
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.hph-modal-title {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
    color: #1f2937;
}

.hph-modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #6b7280;
    padding: 0.5rem;
    border-radius: 50%;
    transition: all 0.2s;
}

.hph-modal-close:hover {
    background: #f3f4f6;
    color: #374151;
}

.hph-modal-body {
    padding: 2rem;
    flex: 1;
    overflow-y: auto;
}

.hph-modal-footer {
    padding: 1.5rem 2rem;
    border-top: 1px solid #e5e7eb;
    background: #f9fafb;
    border-radius: 0 0 12px 12px;
}

.hph-modal-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.hph-modal-actions-right {
    display: flex;
    gap: 0.75rem;
}

.hph-btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
    text-decoration: none;
}

.hph-btn-primary {
    background: var(--hph-primary);
    color: white;
}

.hph-btn-primary:hover {
    background: #2563eb;
}

.hph-btn-success {
    background: #10b981;
    color: white;
}

.hph-btn-success:hover {
    background: #059669;
}

.hph-btn-outline {
    background: transparent;
    color: #6b7280;
    border: 1px solid #d1d5db;
}

.hph-btn-outline:hover {
    background: #f3f4f6;
    border-color: #9ca3af;
}

.hph-btn-ghost {
    background: transparent;
    color: #6b7280;
}

.hph-btn-ghost:hover {
    background: #f3f4f6;
}

.hph-form-progress {
    margin-bottom: 2rem;
}

.hph-progress-bar {
    width: 100%;
    height: 4px;
    background: #e5e7eb;
    border-radius: 2px;
    overflow: hidden;
    margin-bottom: 1rem;
}

.hph-progress-fill {
    height: 100%;
    background: var(--hph-primary);
    transition: width 0.3s ease;
}

.hph-step-indicators {
    display: flex;
    justify-content: space-between;
}

.hph-step-indicator {
    display: flex;
    flex-direction: column;
    align-items: center;
    cursor: pointer;
    transition: all 0.2s;
}

.hph-step-indicator.active .hph-step-number {
    background: var(--hph-primary);
    color: white;
}

.hph-step-indicator.active .hph-step-label {
    color: var(--hph-primary);
    font-weight: 600;
}

.hph-step-number {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #e5e7eb;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.hph-step-label {
    font-size: 0.875rem;
    color: #6b7280;
}

.hph-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.hph-form-group {
    display: flex;
    flex-direction: column;
}

.hph-col-full {
    grid-column: 1 / -1;
}

.hph-form-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    display: block;
}

.hph-required {
    color: #dc2626;
}

.hph-form-input,
.hph-form-select,
.hph-form-textarea {
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 1rem;
    transition: border-color 0.2s;
}

.hph-form-input:focus,
.hph-form-select:focus,
.hph-form-textarea:focus {
    outline: none;
    border-color: var(--hph-primary);
    box-shadow: 0 0 0 3px rgba(var(--hph-primary-rgb), 0.1);
}

.hph-form-input.error,
.hph-form-select.error,
.hph-form-textarea.error {
    border-color: #dc2626;
}

.hph-form-help {
    font-size: 0.875rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

@media (max-width: 768px) {
    .hph-form-grid {
        grid-template-columns: 1fr;
    }
    
    .hph-modal-content {
        width: 95vw;
        margin: 1rem;
    }
}
</style>

