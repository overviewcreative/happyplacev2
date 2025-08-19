<?php
/**
 * Listing Form Modal
 * Complete form for adding/editing listings matching ACF field groups
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get listing data if editing
$listing_id = isset($_GET['listing_id']) ? intval($_GET['listing_id']) : 0;
$is_editing = $listing_id > 0;
$listing_data = array();

if ($is_editing) {
    $listing_post = get_post($listing_id);
    if ($listing_post) {
        $listing_data = get_fields($listing_id);
        $listing_data['post_title'] = $listing_post->post_title;
        $listing_data['post_content'] = $listing_post->post_content;
    }
}
?>

<!-- Listing Form Modal -->
<div class="modal fade" id="listingFormModal" tabindex="-1" aria-labelledby="listingFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="listingFormModalLabel">
                    <i class="fas fa-home me-2"></i>
                    <?php echo $is_editing ? 'Edit Listing' : 'Add New Listing'; ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="listingForm" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <!-- Form Progress Indicator -->
                    <div class="form-progress mb-4">
                        <div class="progress-steps">
                            <div class="step active" data-step="1">
                                <span class="step-number">1</span>
                                <span class="step-title">Basic Info</span>
                            </div>
                            <div class="step" data-step="2">
                                <span class="step-number">2</span>
                                <span class="step-title">Address</span>
                            </div>
                            <div class="step" data-step="3">
                                <span class="step-number">3</span>
                                <span class="step-title">Features</span>
                            </div>
                            <div class="step" data-step="4">
                                <span class="step-number">4</span>
                                <span class="step-title">Media</span>
                            </div>
                            <div class="step" data-step="5">
                                <span class="step-number">5</span>
                                <span class="step-title">Financial</span>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden Fields -->
                    <input type="hidden" name="action" value="save_listing">
                    <input type="hidden" name="listing_id" value="<?php echo $listing_id; ?>">
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('save_listing_nonce'); ?>">
                    <input type="hidden" name="dashboard_nonce" value="<?php echo wp_create_nonce('dashboard_nonce'); ?>">

                    <!-- Step 1: Basic Information -->
                    <div class="form-step active" data-step="1">
                        <h4 class="step-heading">
                            <i class="fas fa-info-circle me-2"></i>
                            Basic Information
                        </h4>
                        
                        <div class="row">
                            <!-- Property Title -->
                            <div class="col-12 mb-3">
                                <label for="post_title" class="form-label required">Property Title</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="post_title" 
                                       name="post_title" 
                                       placeholder="e.g., Beautiful 3-Bedroom Home in Downtown"
                                       value="<?php echo esc_attr($listing_data['post_title'] ?? ''); ?>"
                                       required>
                                <div class="form-text">This will be the main headline for your listing</div>
                            </div>

                            <!-- Short Description -->
                            <div class="col-md-6 mb-3">
                                <label for="short_description" class="form-label">Short Description</label>
                                <textarea class="form-control" 
                                          id="short_description" 
                                          name="short_description" 
                                          rows="3" 
                                          maxlength="200"
                                          placeholder="Brief property summary for search results and previews (150-200 characters)"><?php echo esc_textarea($listing_data['short_description'] ?? ''); ?></textarea>
                                <div class="form-text">
                                    <span class="char-count">0</span>/200 characters
                                </div>
                            </div>

                            <!-- Full Description -->
                            <div class="col-md-6 mb-3">
                                <label for="post_content" class="form-label">Full Description</label>
                                <textarea class="form-control" 
                                          id="post_content" 
                                          name="post_content" 
                                          rows="3"
                                          placeholder="Complete property description with all details and features"><?php echo esc_textarea($listing_data['post_content'] ?? ''); ?></textarea>
                                <div class="form-text">Detailed description for the property page</div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Listing Date -->
                            <div class="col-md-4 mb-3">
                                <label for="listing_date" class="form-label required">Listing Date</label>
                                <input type="date" 
                                       class="form-control" 
                                       id="listing_date" 
                                       name="listing_date" 
                                       value="<?php echo esc_attr($listing_data['listing_date'] ?? date('Y-m-d')); ?>"
                                       required>
                            </div>

                            <!-- Price -->
                            <div class="col-md-4 mb-3">
                                <label for="price" class="form-label required">Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="price" 
                                           name="price" 
                                           placeholder="750000"
                                           min="0"
                                           value="<?php echo esc_attr($listing_data['price'] ?? ''); ?>"
                                           required>
                                </div>
                            </div>

                            <!-- Listing Status -->
                            <div class="col-md-4 mb-3">
                                <label for="listing_status_taxonomy" class="form-label required">Listing Status</label>
                                <select class="form-select" id="listing_status_taxonomy" name="listing_status_taxonomy" required>
                                    <option value="">Select Status</option>
                                    <option value="active" <?php selected($listing_data['listing_status_taxonomy'] ?? '', 'active'); ?>>Active</option>
                                    <option value="pending" <?php selected($listing_data['listing_status_taxonomy'] ?? '', 'pending'); ?>>Pending</option>
                                    <option value="sold" <?php selected($listing_data['listing_status_taxonomy'] ?? '', 'sold'); ?>>Sold</option>
                                    <option value="coming-soon" <?php selected($listing_data['listing_status_taxonomy'] ?? '', 'coming-soon'); ?>>Coming Soon</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Property Type -->
                            <div class="col-md-4 mb-3">
                                <label for="property_type" class="form-label required">Property Type</label>
                                <select class="form-select" id="property_type" name="property_type" required>
                                    <option value="">Select Type</option>
                                    <option value="single_family" <?php selected($listing_data['property_type'] ?? '', 'single_family'); ?>>Single Family Home</option>
                                    <option value="condo" <?php selected($listing_data['property_type'] ?? '', 'condo'); ?>>Condominium</option>
                                    <option value="townhouse" <?php selected($listing_data['property_type'] ?? '', 'townhouse'); ?>>Townhouse</option>
                                    <option value="duplex" <?php selected($listing_data['property_type'] ?? '', 'duplex'); ?>>Duplex</option>
                                    <option value="multi_family" <?php selected($listing_data['property_type'] ?? '', 'multi_family'); ?>>Multi-Family</option>
                                    <option value="commercial" <?php selected($listing_data['property_type'] ?? '', 'commercial'); ?>>Commercial</option>
                                    <option value="land" <?php selected($listing_data['property_type'] ?? '', 'land'); ?>>Land</option>
                                    <option value="mobile_home" <?php selected($listing_data['property_type'] ?? '', 'mobile_home'); ?>>Mobile Home</option>
                                </select>
                            </div>

                            <!-- MLS Number -->
                            <div class="col-md-4 mb-3">
                                <label for="mls_number" class="form-label">MLS Number</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="mls_number" 
                                       name="mls_number" 
                                       placeholder="MLS123456"
                                       value="<?php echo esc_attr($listing_data['mls_number'] ?? ''); ?>">
                            </div>

                            <!-- Featured Listing -->
                            <div class="col-md-4 mb-3">
                                <label for="featured_listing" class="form-label">Featured Listing</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="featured_listing" 
                                           name="featured_listing" 
                                           value="1"
                                           <?php checked($listing_data['featured_listing'] ?? 0, 1); ?>>
                                    <label class="form-check-label" for="featured_listing">
                                        Feature this listing
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Bedrooms -->
                            <div class="col-md-3 mb-3">
                                <label for="bedrooms" class="form-label">Bedrooms</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="bedrooms" 
                                       name="bedrooms" 
                                       placeholder="3"
                                       min="0" 
                                       max="20"
                                       value="<?php echo esc_attr($listing_data['bedrooms'] ?? ''); ?>">
                            </div>

                            <!-- Full Bathrooms -->
                            <div class="col-md-3 mb-3">
                                <label for="full_bathrooms" class="form-label">Full Bathrooms</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="full_bathrooms" 
                                       name="full_bathrooms" 
                                       placeholder="2"
                                       min="0" 
                                       max="20"
                                       value="<?php echo esc_attr($listing_data['full_bathrooms'] ?? ''); ?>">
                            </div>

                            <!-- Half Bathrooms -->
                            <div class="col-md-3 mb-3">
                                <label for="half_bathrooms" class="form-label">Half Bathrooms</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="half_bathrooms" 
                                       name="half_bathrooms" 
                                       placeholder="1"
                                       min="0" 
                                       max="10"
                                       value="<?php echo esc_attr($listing_data['half_bathrooms'] ?? ''); ?>">
                            </div>

                            <!-- Garage Spaces -->
                            <div class="col-md-3 mb-3">
                                <label for="garage_spaces" class="form-label">Garage Spaces</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="garage_spaces" 
                                       name="garage_spaces" 
                                       placeholder="2"
                                       min="0" 
                                       max="10"
                                       value="<?php echo esc_attr($listing_data['garage_spaces'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <!-- Square Feet -->
                            <div class="col-md-4 mb-3">
                                <label for="square_feet" class="form-label">Square Feet</label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control" 
                                           id="square_feet" 
                                           name="square_feet" 
                                           placeholder="2500"
                                           min="0"
                                           value="<?php echo esc_attr($listing_data['square_feet'] ?? ''); ?>">
                                    <span class="input-group-text">sqft</span>
                                </div>
                            </div>

                            <!-- Lot Size -->
                            <div class="col-md-4 mb-3">
                                <label for="lot_size_acres" class="form-label">Lot Size</label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control" 
                                           id="lot_size_acres" 
                                           name="lot_size_acres" 
                                           placeholder="0.25"
                                           min="0"
                                           step="0.01"
                                           value="<?php echo esc_attr($listing_data['lot_size_acres'] ?? ''); ?>">
                                    <span class="input-group-text">acres</span>
                                </div>
                            </div>

                            <!-- Year Built -->
                            <div class="col-md-4 mb-3">
                                <label for="year_built" class="form-label">Year Built</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="year_built" 
                                       name="year_built" 
                                       placeholder="2020"
                                       min="1800" 
                                       max="2030"
                                       value="<?php echo esc_attr($listing_data['year_built'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Navigation buttons will be added here via JavaScript -->
                    <div class="form-navigation">
                        <button type="button" class="btn btn-secondary" id="prevStep" style="display: none;">
                            <i class="fas fa-chevron-left me-1"></i> Previous
                        </button>
                        <button type="button" class="btn btn-primary" id="nextStep">
                            Next <i class="fas fa-chevron-right ms-1"></i>
                        </button>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="submitListing">
                        <i class="fas fa-save me-2"></i>
                        <?php echo $is_editing ? 'Update Listing' : 'Create Listing'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="listing-form-loading" id="listingFormLoading" style="display: none;">
    <div class="loading-content">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3">Saving listing...</p>
    </div>
</div>