<?php
/**
 * ============================================
 * UNIFIED DASHBOARD COMPONENT SYSTEM
 * ============================================
 * 
 * This file defines ALL dashboard HTML structures
 * using ONLY main theme components.
 * 
 * RULE: Every dashboard element must use main site classes!
 * 
 * @package HappyPlaceTheme
 * @version UNIFIED-1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) exit;

class HPH_Unified_Dashboard_Components {
    
    /**
     * ENHANCED LISTING CARD: Inline editing with full admin capabilities
     * Uses street address as title, inline price/status editing
     */
    public static function listing_card($listing) {
        $current_user = wp_get_current_user();
        $is_admin = in_array('administrator', $current_user->roles);
        $is_agent = in_array('agent', $current_user->roles);

        // Use bridge function for permission checking
        $can_edit = hpt_can_user_edit_listing($listing['id']);
        
        // Get listing meta
        $street_address = get_post_meta($listing['id'], '_listing_address', true) ?: $listing['title'];
        $city = get_post_meta($listing['id'], '_listing_city', true);
        $state = get_post_meta($listing['id'], '_listing_state', true);
        $zip = get_post_meta($listing['id'], '_listing_zip', true);
        $full_address = trim($street_address . ', ' . $city . ', ' . $state . ' ' . $zip, ', ');
        
        $bedrooms = get_post_meta($listing['id'], '_listing_bedrooms', true);
        $bathrooms = get_post_meta($listing['id'], '_listing_bathrooms', true);
        $square_feet = get_post_meta($listing['id'], '_listing_square_feet', true);
        $listing_type = wp_get_post_terms($listing['id'], 'listing_type');
        
        ?>
        <article class="hph-card hph-card--listing hph-listing-card" data-listing-id="<?php echo esc_attr($listing['id']); ?>">
            <div class="hph-card__media">
                <?php if ($listing['featured_image']): ?>
                    <img src="<?php echo esc_url($listing['featured_image']); ?>" 
                         alt="<?php echo esc_attr($street_address); ?>"
                         class="hph-card__image">
                <?php else: ?>
                    <div class="hph-card__placeholder">
                        <i class="fas fa-home"></i>
                        <span>No Image</span>
                    </div>
                <?php endif; ?>
                
                <!-- Fixed Badge Container -->
                <div class="hph-card__badges">
                    <?php if ($listing['status']): ?>
                        <span class="hph-badge hph-badge--<?php echo esc_attr(strtolower($listing['status'])); ?>" 
                              data-field="status">
                            <?php echo esc_html(ucfirst($listing['status'])); ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if (!empty($listing_type) && !is_wp_error($listing_type)): ?>
                        <span class="hph-badge hph-badge--type">
                            <?php echo esc_html($listing_type[0]->name); ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <!-- Quick View Button -->
                <div class="hph-card__quick-actions">
                    <button class="hph-btn hph-btn-icon hph-btn-ghost" 
                            data-action="quick-view-listing" 
                            data-listing-id="<?php echo esc_attr($listing['id']); ?>"
                            title="Quick View">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="hph-card__content">
                <!-- Address as Title -->
                <h3 class="hph-card__title">
                    <a href="<?php echo esc_url($listing['permalink']); ?>" class="hph-card__title-link">
                        <?php echo esc_html($street_address); ?>
                    </a>
                </h3>
                
                <!-- Location Details -->
                <?php if ($city || $state): ?>
                    <p class="hph-card__location">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php echo esc_html(trim($city . ', ' . $state, ', ')); ?>
                    </p>
                <?php endif; ?>
                
                <!-- Property Details -->
                <?php if ($bedrooms || $bathrooms || $square_feet): ?>
                    <div class="hph-card__details">
                        <?php if ($bedrooms): ?>
                            <span class="hph-card__detail">
                                <i class="fas fa-bed"></i>
                                <?php echo esc_html($bedrooms); ?> bed<?php echo $bedrooms != 1 ? 's' : ''; ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($bathrooms): ?>
                            <span class="hph-card__detail">
                                <i class="fas fa-bath"></i>
                                <?php echo esc_html($bathrooms); ?> bath<?php echo $bathrooms != 1 ? 's' : ''; ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($square_feet): ?>
                            <span class="hph-card__detail">
                                <i class="fas fa-ruler-combined"></i>
                                <?php echo number_format($square_feet); ?> sq ft
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Inline Editable Price -->
                <div class="hph-card__price-container">
                    <?php if ($can_edit): ?>
                        <div class="hph-inline-edit hph-inline-edit--price">
                            <span class="hph-card__price hph-inline-edit__display" data-field="price">
                                $<?php echo number_format($listing['price']); ?>
                            </span>
                            <div class="hph-inline-edit__form" style="display: none;">
                                <input type="number" 
                                       class="hph-inline-edit__input" 
                                       data-field="price"
                                       data-listing-id="<?php echo esc_attr($listing['id']); ?>"
                                       value="<?php echo esc_attr($listing['price']); ?>"
                                       step="1000"
                                       min="0">
                                <div class="hph-inline-edit__actions">
                                    <button class="hph-btn hph-btn-xs hph-btn-success" data-action="save-inline">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="hph-btn hph-btn-xs hph-btn-secondary" data-action="cancel-inline">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <span class="hph-card__price">
                            $<?php echo number_format($listing['price']); ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <!-- Inline Editable Status -->
                <?php if ($can_edit): ?>
                    <div class="hph-inline-edit hph-inline-edit--status">
                        <span class="hph-card__status hph-inline-edit__display" data-field="status">
                            Status: <?php echo esc_html(ucfirst($listing['status'])); ?>
                        </span>
                        <div class="hph-inline-edit__form" style="display: none;">
                            <select class="hph-inline-edit__select" 
                                    data-field="status"
                                    data-listing-id="<?php echo esc_attr($listing['id']); ?>">
                                <option value="active" <?php selected($listing['status'], 'active'); ?>>Active</option>
                                <option value="pending" <?php selected($listing['status'], 'pending'); ?>>Pending</option>
                                <option value="sold" <?php selected($listing['status'], 'sold'); ?>>Sold</option>
                                <option value="off-market" <?php selected($listing['status'], 'off-market'); ?>>Off Market</option>
                                <option value="coming-soon" <?php selected($listing['status'], 'coming-soon'); ?>>Coming Soon</option>
                            </select>
                            <div class="hph-inline-edit__actions">
                                <button class="hph-btn hph-btn-xs hph-btn-success" data-action="save-inline">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button class="hph-btn hph-btn-xs hph-btn-secondary" data-action="cancel-inline">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Enhanced Actions -->
            <div class="hph-card__actions">
                <div class="hph-card__actions-primary">
                    <?php if ($can_edit): ?>
                        <button class="hph-btn hph-btn-primary hph-btn-sm" 
                                data-action="edit-listing-full" 
                                data-listing-id="<?php echo esc_attr($listing['id']); ?>">
                            <i class="fas fa-edit"></i>
                            Edit Full Listing
                        </button>
                    <?php endif; ?>
                    
                    <a href="<?php echo esc_url($listing['permalink']); ?>" 
                       class="hph-btn hph-btn-secondary hph-btn-sm"
                       target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                        View Public
                    </a>
                </div>
                
                <?php if ($can_edit): ?>
                    <div class="hph-card__actions-secondary">
                        <button class="hph-btn hph-btn-ghost hph-btn-xs" 
                                data-action="duplicate-listing" 
                                data-listing-id="<?php echo esc_attr($listing['id']); ?>"
                                title="Duplicate Listing">
                            <i class="fas fa-copy"></i>
                        </button>
                        
                        <button class="hph-btn hph-btn-ghost hph-btn-xs hph-btn-danger" 
                                data-action="delete-listing" 
                                data-listing-id="<?php echo esc_attr($listing['id']); ?>"
                                title="Delete Listing">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Loading State -->
            <div class="hph-card__loading" style="display: none;">
                <div class="hph-loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                    Updating...
                </div>
            </div>
        </article>
        <?php
    }
    
    /**
     * COMPREHENSIVE ADD LISTING FORM: WordPress Admin Equivalent
     * All fields, taxonomies, and capabilities matching wp-admin
     */
    public static function comprehensive_listing_form($listing_id = null) {
        $is_edit = !empty($listing_id);
        $listing = $is_edit ? get_post($listing_id) : null;
        
        // Get all listing taxonomies
        $property_types = get_terms(['taxonomy' => 'listing_type', 'hide_empty' => false]);
        $property_status = get_terms(['taxonomy' => 'listing_status', 'hide_empty' => false]); 
        $property_features = get_terms(['taxonomy' => 'listing_features', 'hide_empty' => false]);
        $neighborhoods = get_terms(['taxonomy' => 'neighborhood', 'hide_empty' => false]);
        
        // Get current values if editing
        $current_values = $is_edit ? [
            'street_address' => get_post_meta($listing_id, '_listing_address', true),
            'city' => get_post_meta($listing_id, '_listing_city', true),
            'state' => get_post_meta($listing_id, '_listing_state', true),
            'zip' => get_post_meta($listing_id, '_listing_zip', true),
            'price' => get_post_meta($listing_id, '_listing_price', true),
            'bedrooms' => get_post_meta($listing_id, '_listing_bedrooms', true),
            'bathrooms' => get_post_meta($listing_id, '_listing_bathrooms', true),
            'half_baths' => get_post_meta($listing_id, '_listing_half_baths', true),
            'square_feet' => get_post_meta($listing_id, '_listing_square_feet', true),
            'lot_size' => get_post_meta($listing_id, '_listing_lot_size', true),
            'year_built' => get_post_meta($listing_id, '_listing_year_built', true),
            'garage_spaces' => get_post_meta($listing_id, '_listing_garage_spaces', true),
            'hoa_fees' => get_post_meta($listing_id, '_listing_hoa_fees', true),
            'property_taxes' => get_post_meta($listing_id, '_listing_property_taxes', true),
            'mls_number' => get_post_meta($listing_id, '_listing_mls_number', true),
            'virtual_tour_url' => get_post_meta($listing_id, '_listing_virtual_tour_url', true),
            'listing_agent_notes' => get_post_meta($listing_id, '_listing_agent_notes', true),
            'showing_instructions' => get_post_meta($listing_id, '_listing_showing_instructions', true),
            'open_house_dates' => get_post_meta($listing_id, '_listing_open_house_dates', true),
        ] : [];
        
        ?>
        <form class="hph-form hph-form--comprehensive" id="comprehensive-listing-form" enctype="multipart/form-data">
            <?php wp_nonce_field('save_listing', 'listing_nonce'); ?>
            <?php if ($is_edit): ?>
                <input type="hidden" name="listing_id" value="<?php echo esc_attr($listing_id); ?>">
            <?php endif; ?>
            
            <!-- Property Address Section -->
            <div class="hph-form__section">
                <h3 class="hph-form__section-title">
                    <i class="fas fa-map-marker-alt"></i>
                    Property Address
                </h3>
                
                <div class="hph-form__row">
                    <div class="hph-form__group">
                        <label for="street_address" class="hph-form__label hph-form__label--required">
                            Street Address (This will be the listing title)
                        </label>
                        <input type="text" 
                               id="street_address" 
                               name="street_address" 
                               class="hph-form__input" 
                               placeholder="123 Main Street"
                               value="<?php echo esc_attr($current_values['street_address'] ?? ''); ?>"
                               required>
                    </div>
                </div>
                
                <div class="hph-form__row">
                    <div class="hph-form__group hph-form__group--half">
                        <label for="city" class="hph-form__label hph-form__label--required">
                            City
                        </label>
                        <input type="text" 
                               id="city" 
                               name="city" 
                               class="hph-form__input" 
                               placeholder="City"
                               value="<?php echo esc_attr($current_values['city'] ?? ''); ?>"
                               required>
                    </div>
                    
                    <div class="hph-form__group hph-form__group--third">
                        <label for="state" class="hph-form__label hph-form__label--required">
                            State
                        </label>
                        <select id="state" name="state" class="hph-form__select" required>
                            <option value="">Select State</option>
                            <?php
                            $states = ['AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY'];
                            foreach ($states as $state) {
                                $selected = selected($current_values['state'] ?? '', $state, false);
                                echo "<option value=\"{$state}\" {$selected}>{$state}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="hph-form__group hph-form__group--third">
                        <label for="zip" class="hph-form__label hph-form__label--required">
                            ZIP Code
                        </label>
                        <input type="text" 
                               id="zip" 
                               name="zip" 
                               class="hph-form__input" 
                               placeholder="12345"
                               value="<?php echo esc_attr($current_values['zip'] ?? ''); ?>"
                               required>
                    </div>
                </div>
                
                <?php if (!empty($neighborhoods)): ?>
                    <div class="hph-form__row">
                        <div class="hph-form__group">
                            <label for="neighborhood" class="hph-form__label">
                                Neighborhood
                            </label>
                            <select id="neighborhood" name="neighborhood[]" class="hph-form__select" multiple>
                                <?php foreach ($neighborhoods as $neighborhood): ?>
                                    <option value="<?php echo esc_attr($neighborhood->term_id); ?>">
                                        <?php echo esc_html($neighborhood->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Property Details Section -->
            <div class="hph-form__section">
                <h3 class="hph-form__section-title">
                    <i class="fas fa-home"></i>
                    Property Details
                </h3>
                
                <div class="hph-form__row">
                    <div class="hph-form__group hph-form__group--half">
                        <label for="property_type" class="hph-form__label hph-form__label--required">
                            Property Type
                        </label>
                        <select id="property_type" name="property_type[]" class="hph-form__select" required>
                            <option value="">Select Type</option>
                            <?php foreach ($property_types as $type): ?>
                                <option value="<?php echo esc_attr($type->term_id); ?>">
                                    <?php echo esc_html($type->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="hph-form__group hph-form__group--half">
                        <label for="listing_status" class="hph-form__label hph-form__label--required">
                            Listing Status
                        </label>
                        <select id="listing_status" name="listing_status[]" class="hph-form__select" required>
                            <option value="">Select Status</option>
                            <?php foreach ($property_status as $status): ?>
                                <option value="<?php echo esc_attr($status->term_id); ?>">
                                    <?php echo esc_html($status->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="hph-form__row">
                    <div class="hph-form__group hph-form__group--third">
                        <label for="bedrooms" class="hph-form__label">
                            Bedrooms
                        </label>
                        <input type="number" 
                               id="bedrooms" 
                               name="bedrooms" 
                               class="hph-form__input" 
                               min="0" 
                               step="1"
                               value="<?php echo esc_attr($current_values['bedrooms'] ?? ''); ?>">
                    </div>
                    
                    <div class="hph-form__group hph-form__group--third">
                        <label for="bathrooms" class="hph-form__label">
                            Full Bathrooms
                        </label>
                        <input type="number" 
                               id="bathrooms" 
                               name="bathrooms" 
                               class="hph-form__input" 
                               min="0" 
                               step="0.5"
                               value="<?php echo esc_attr($current_values['bathrooms'] ?? ''); ?>">
                    </div>
                    
                    <div class="hph-form__group hph-form__group--third">
                        <label for="half_baths" class="hph-form__label">
                            Half Bathrooms
                        </label>
                        <input type="number" 
                               id="half_baths" 
                               name="half_baths" 
                               class="hph-form__input" 
                               min="0" 
                               step="1"
                               value="<?php echo esc_attr($current_values['half_baths'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="hph-form__row">
                    <div class="hph-form__group hph-form__group--half">
                        <label for="square_feet" class="hph-form__label">
                            Square Feet
                        </label>
                        <input type="number" 
                               id="square_feet" 
                               name="square_feet" 
                               class="hph-form__input" 
                               min="0" 
                               step="1"
                               value="<?php echo esc_attr($current_values['square_feet'] ?? ''); ?>">
                    </div>
                    
                    <div class="hph-form__group hph-form__group--half">
                        <label for="lot_size" class="hph-form__label">
                            Lot Size (sq ft)
                        </label>
                        <input type="number" 
                               id="lot_size" 
                               name="lot_size" 
                               class="hph-form__input" 
                               min="0" 
                               step="1"
                               value="<?php echo esc_attr($current_values['lot_size'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="hph-form__row">
                    <div class="hph-form__group hph-form__group--half">
                        <label for="year_built" class="hph-form__label">
                            Year Built
                        </label>
                        <input type="number" 
                               id="year_built" 
                               name="year_built" 
                               class="hph-form__input" 
                               min="1800" 
                               max="<?php echo date('Y') + 5; ?>"
                               value="<?php echo esc_attr($current_values['year_built'] ?? ''); ?>">
                    </div>
                    
                    <div class="hph-form__group hph-form__group--half">
                        <label for="garage_spaces" class="hph-form__label">
                            Garage Spaces
                        </label>
                        <input type="number" 
                               id="garage_spaces" 
                               name="garage_spaces" 
                               class="hph-form__input" 
                               min="0" 
                               step="1"
                               value="<?php echo esc_attr($current_values['garage_spaces'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <!-- Pricing Section -->
            <div class="hph-form__section">
                <h3 class="hph-form__section-title">
                    <i class="fas fa-dollar-sign"></i>
                    Pricing & Financial
                </h3>
                
                <div class="hph-form__row">
                    <div class="hph-form__group hph-form__group--third">
                        <label for="price" class="hph-form__label hph-form__label--required">
                            Listing Price
                        </label>
                        <input type="number" 
                               id="price" 
                               name="price" 
                               class="hph-form__input" 
                               min="0" 
                               step="1000"
                               value="<?php echo esc_attr($current_values['price'] ?? ''); ?>"
                               required>
                    </div>
                    
                    <div class="hph-form__group hph-form__group--third">
                        <label for="hoa_fees" class="hph-form__label">
                            HOA Fees (Monthly)
                        </label>
                        <input type="number" 
                               id="hoa_fees" 
                               name="hoa_fees" 
                               class="hph-form__input" 
                               min="0" 
                               step="1"
                               value="<?php echo esc_attr($current_values['hoa_fees'] ?? ''); ?>">
                    </div>
                    
                    <div class="hph-form__group hph-form__group--third">
                        <label for="property_taxes" class="hph-form__label">
                            Property Taxes (Annual)
                        </label>
                        <input type="number" 
                               id="property_taxes" 
                               name="property_taxes" 
                               class="hph-form__input" 
                               min="0" 
                               step="1"
                               value="<?php echo esc_attr($current_values['property_taxes'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <!-- Property Description -->
            <div class="hph-form__section">
                <h3 class="hph-form__section-title">
                    <i class="fas fa-align-left"></i>
                    Property Description
                </h3>
                
                <div class="hph-form__row">
                    <div class="hph-form__group">
                        <label for="description" class="hph-form__label">
                            Public Description
                        </label>
                        <textarea id="description" 
                                  name="description" 
                                  class="hph-form__textarea" 
                                  placeholder="Describe this beautiful property..."
                                  rows="6"><?php echo $is_edit ? esc_textarea($listing->post_content) : ''; ?></textarea>
                        <p class="hph-form__help-text">This will be visible to potential buyers on the public listing.</p>
                    </div>
                </div>
            </div>

            <!-- Features & Amenities -->
            <?php if (!empty($property_features)): ?>
                <div class="hph-form__section">
                    <h3 class="hph-form__section-title">
                        <i class="fas fa-star"></i>
                        Features & Amenities
                    </h3>
                    
                    <div class="hph-form__row">
                        <div class="hph-form__group">
                            <div class="hph-checkbox-grid">
                                <?php foreach ($property_features as $feature): ?>
                                    <div class="hph-form-check hph-form-check-inline">
                                        <input type="checkbox" 
                                               name="property_features[]" 
                                               value="<?php echo esc_attr($feature->term_id); ?>"
                                               class="hph-form-check-input">
                                        <label class="hph-form-check-label">
                                            <?php echo esc_html($feature->name); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Professional Information -->
            <div class="hph-form__section">
                <h3 class="hph-form__section-title">
                    <i class="fas fa-briefcase"></i>
                    Professional Information
                </h3>
                
                <div class="hph-form__row">
                    <div class="hph-form__group hph-form__group--half">
                        <label for="mls_number" class="hph-form__label">
                            MLS Number
                        </label>
                        <input type="text" 
                               id="mls_number" 
                               name="mls_number" 
                               class="hph-form__input" 
                               placeholder="MLS123456"
                               value="<?php echo esc_attr($current_values['mls_number'] ?? ''); ?>">
                    </div>
                    
                    <div class="hph-form__group hph-form__group--half">
                        <label for="virtual_tour_url" class="hph-form__label">
                            Virtual Tour URL
                        </label>
                        <input type="url" 
                               id="virtual_tour_url" 
                               name="virtual_tour_url" 
                               class="hph-form__input" 
                               placeholder="https://..."
                               value="<?php echo esc_attr($current_values['virtual_tour_url'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="hph-form__row">
                    <div class="hph-form__group">
                        <label for="agent_notes" class="hph-form__label">
                            Private Agent Notes
                        </label>
                        <textarea id="agent_notes" 
                                  name="agent_notes" 
                                  class="hph-form__textarea" 
                                  placeholder="Private notes for agents only..."
                                  rows="3"><?php echo esc_textarea($current_values['listing_agent_notes'] ?? ''); ?></textarea>
                        <p class="hph-form__help-text">These notes are only visible to agents and will not be shown publicly.</p>
                    </div>
                </div>
                
                <div class="hph-form__row">
                    <div class="hph-form__group">
                        <label for="showing_instructions" class="hph-form__label">
                            Showing Instructions
                        </label>
                        <textarea id="showing_instructions" 
                                  name="showing_instructions" 
                                  class="hph-form__textarea" 
                                  placeholder="Special instructions for showings..."
                                  rows="2"><?php echo esc_textarea($current_values['showing_instructions'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Images Section -->
            <div class="hph-form__section">
                <h3 class="hph-form__section-title">
                    <i class="fas fa-images"></i>
                    Property Images
                </h3>
                
                <div class="hph-form__row">
                    <div class="hph-form__group">
                        <label for="property_images" class="hph-form__label">
                            Upload Property Images
                        </label>
                        <div class="hph-file-upload-area" id="imageUploadArea">
                            <input type="file" 
                                   id="property_images" 
                                   name="property_images[]" 
                                   accept="image/*" 
                                   multiple
                                   class="hph-file-input">
                            <div class="hph-file-upload-content">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Click to select images or drag and drop</p>
                                <p class="hph-form__help-text">JPG, PNG, WebP up to 10MB each. First image will be featured.</p>
                            </div>
                        </div>
                        <div id="imagePreviewContainer" class="hph-image-preview-container"></div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="hph-form__actions">
                <div class="hph-form__actions-primary">
                    <button type="submit" class="hph-btn hph-btn-primary hph-btn-lg">
                        <i class="fas fa-save"></i>
                        <?php echo $is_edit ? 'Update Listing' : 'Create Listing'; ?>
                    </button>
                    
                    <button type="button" class="hph-btn hph-btn-secondary" data-action="save-draft">
                        <i class="fas fa-file-alt"></i>
                        Save as Draft
                    </button>
                </div>
                
                <div class="hph-form__actions-secondary">
                    <button type="button" class="hph-btn hph-btn-ghost" data-action="cancel">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                </div>
            </div>
        </form>
        <?php
    }
    
    /**
     * STATS GRID: Uses unified stat cards
     */
    public static function stats_overview($stats) {
        ?>
        <div class="hph-stats-grid">
            <div class="hph-stat-card">
                <span class="hph-stat-value"><?php echo number_format($stats['total_listings']); ?></span>
                <span class="hph-stat-label">Total Listings</span>
            </div>
            
            <div class="hph-stat-card">
                <span class="hph-stat-value"><?php echo number_format($stats['active_listings']); ?></span>
                <span class="hph-stat-label">Active Listings</span>
            </div>
            
            <div class="hph-stat-card">
                <span class="hph-stat-value"><?php echo number_format($stats['pending_listings']); ?></span>
                <span class="hph-stat-label">Pending Listings</span>
            </div>
            
            <div class="hph-stat-card">
                <span class="hph-stat-value"><?php echo number_format($stats['total_leads']); ?></span>
                <span class="hph-stat-label">Total Leads</span>
            </div>
        </div>
        <?php
    }
    
    /**
     * MODAL: Modern flat modal design
     * Uses .hph-modal class with dashboard styling
     */
    public static function confirmation_modal($title, $message, $actions) {
        ?>
        <div class="hph-modal" id="confirmation-modal" style="display: none;">
            <div class="hph-modal__backdrop" data-action="close-modal"></div>
            <div class="hph-modal__container">
                <div class="hph-modal__header">
                    <h3 class="hph-modal__title">
                        <i class="fas fa-question-circle"></i>
                        <?php echo esc_html($title); ?>
                    </h3>
                    <button class="hph-modal__close" data-action="close-modal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="hph-modal__body">
                    <p class="hph-modal__message">
                        <?php echo esc_html($message); ?>
                    </p>
                </div>
                
                <div class="hph-modal__footer">
                    <?php foreach ($actions as $action): ?>
                        <button class="hph-btn hph-btn-<?php echo esc_attr($action['type']); ?>" 
                                data-action="<?php echo esc_attr($action['action']); ?>">
                            <?php if (isset($action['icon'])): ?>
                                <i class="fas fa-<?php echo esc_attr($action['icon']); ?>"></i>
                            <?php endif; ?>
                            <?php echo esc_html($action['label']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * ALERT/NOTICE: Modern flat alert design
     */
    public static function alert($message, $type = 'info', $icon = null) {
        $icons = [
            'info' => 'info-circle',
            'success' => 'check-circle', 
            'warning' => 'exclamation-triangle',
            'danger' => 'times-circle'
        ];
        
        $alert_icon = $icon ?? $icons[$type] ?? 'info-circle';
        ?>
        <div class="hph-alert hph-alert--<?php echo esc_attr($type); ?>">
            <div class="hph-alert__icon">
                <i class="fas fa-<?php echo esc_attr($alert_icon); ?>"></i>
            </div>
            <div class="hph-alert__content">
                <p class="hph-alert__message">
                    <?php echo esc_html($message); ?>
                </p>
            </div>
        </div>
        <?php
    }
    
    /**
     * DATA TABLE: Uses main site table styling
     */
    public static function data_table($headers, $rows, $actions = []) {
        ?>
        <div class="hph-table-container">
            <table class="hph-table hph-table--dashboard">
                <thead class="hph-table__head">
                    <tr class="hph-table__row">
                        <?php foreach ($headers as $header): ?>
                            <th class="hph-table__header">
                                <?php echo esc_html($header); ?>
                            </th>
                        <?php endforeach; ?>
                        <?php if (!empty($actions)): ?>
                            <th class="hph-table__header hph-table__header--actions">
                                Actions
                            </th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="hph-table__body">
                    <?php foreach ($rows as $row): ?>
                        <tr class="hph-table__row">
                            <?php foreach ($row as $cell): ?>
                                <td class="hph-table__cell">
                                    <?php echo wp_kses_post($cell); ?>
                                </td>
                            <?php endforeach; ?>
                            <?php if (!empty($actions)): ?>
                                <td class="hph-table__cell hph-table__cell--actions">
                                    <?php foreach ($actions as $action): ?>
                                        <button class="hph-btn hph-btn-<?php echo esc_attr($action['type']); ?> hph-btn-xs" 
                                                data-action="<?php echo esc_attr($action['action']); ?>"
                                                <?php if (isset($action['data'])): ?>
                                                    <?php foreach ($action['data'] as $key => $value): ?>
                                                        data-<?php echo esc_attr($key); ?>="<?php echo esc_attr($value); ?>"
                                                    <?php endforeach; ?>
                                                <?php endif; ?>>
                                            <?php echo esc_html($action['label']); ?>
                                        </button>
                                    <?php endforeach; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
?>
