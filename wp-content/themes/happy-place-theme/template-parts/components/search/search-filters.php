<?php
/**
 * Advanced Search Filters Component
 * 
 * Dynamic filters that adapt based on post type selection
 * Supports all searchable post types with relevant fields
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

$config = $args ?? [];
$current_post_type = $config['current_post_type'] ?? 'all';
$form_id = $config['form_id'] ?? 'universal-search';
?>

<div class="hph-search-filters" 
     data-form-id="<?php echo esc_attr($form_id); ?>" 
     style="background: var(--hph-white); border-radius: var(--hph-radius-lg); padding: var(--hph-padding-lg); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);">
    
    <!-- Universal Filters (shown for all post types) -->
    <div class="hph-filter-group hph-universal-filters" 
         style="margin-bottom: var(--hph-margin-lg);">
        <h4 class="hph-filter-group-title" 
            style="margin: 0 0 var(--hph-margin-md) 0; font-size: var(--hph-text-lg); font-weight: var(--hph-font-semibold); color: var(--hph-gray-900);">
            <?php _e('General Filters', 'happy-place-theme'); ?>
        </h4>
        
        <div class="hph-filter-row" 
             style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: var(--hph-gap-md); margin-bottom: var(--hph-margin-md);">
            <div class="hph-filter-field">
                <label for="location" 
                       style="display: block; margin-bottom: var(--hph-margin-xs); font-size: var(--hph-text-sm); font-weight: var(--hph-font-medium); color: var(--hph-gray-700);">
                    <?php _e('Location', 'happy-place-theme'); ?>
                </label>
                <input 
                    type="text" 
                    name="location" 
                    id="location"
                    class="hph-input"
                    style="width: 100%; padding: var(--hph-padding-sm) var(--hph-padding-md); border: 2px solid var(--hph-gray-200); border-radius: var(--hph-radius-md); font-size: var(--hph-text-base); color: var(--hph-gray-900); background: var(--hph-white); transition: all 0.2s ease; outline: none;"
                    placeholder="<?php _e('City, State, ZIP', 'happy-place-theme'); ?>"
                    value="<?php echo esc_attr($_GET['location'] ?? ''); ?>"
                    onfocus="this.style.borderColor='var(--hph-primary)'; this.style.boxShadow='0 0 0 3px rgba(80, 186, 225, 0.1)'"
                    onblur="this.style.borderColor='var(--hph-gray-200)'; this.style.boxShadow='none'"
                />
            </div>
        </div>
    </div>

    <!-- Property/Listing Specific Filters -->
    <div class="hph-filter-group hph-listing-filters" 
         data-show-for="listing,all" 
         style="margin-bottom: var(--hph-margin-lg);">
        <h4 class="hph-filter-group-title" 
            style="margin: 0 0 var(--hph-margin-md) 0; font-size: var(--hph-text-lg); font-weight: var(--hph-font-semibold); color: var(--hph-gray-900);">
            <?php _e('Property Filters', 'happy-place-theme'); ?>
        </h4>
        
        <div class="hph-filter-row" 
             style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: var(--hph-gap-md); margin-bottom: var(--hph-margin-md);">
            <div class="hph-filter-field">
                <label for="min_price" 
                       style="display: block; margin-bottom: var(--hph-margin-xs); font-size: var(--hph-text-sm); font-weight: var(--hph-font-medium); color: var(--hph-gray-700);">
                    <?php _e('Price Range', 'happy-place-theme'); ?>
                </label>
                <div class="hph-price-range-inputs" 
                     style="display: flex; align-items: center; gap: var(--hph-gap-sm);">
                    <input 
                        type="number" 
                        name="min_price" 
                        id="min_price"
                        class="hph-input"
                        style="flex: 1; padding: var(--hph-padding-sm) var(--hph-padding-md); border: 2px solid var(--hph-gray-200); border-radius: var(--hph-radius-md); font-size: var(--hph-text-base); color: var(--hph-gray-900); background: var(--hph-white); transition: all 0.2s ease; outline: none;"
                        placeholder="<?php _e('Min Price', 'happy-place-theme'); ?>"
                        value="<?php echo esc_attr($_GET['min_price'] ?? ''); ?>"
                        min="0"
                        step="1000"
                        onfocus="this.style.borderColor='var(--hph-primary)'; this.style.boxShadow='0 0 0 3px rgba(80, 186, 225, 0.1)'"
                        onblur="this.style.borderColor='var(--hph-gray-200)'; this.style.boxShadow='none'"
                    />
                    <span class="hph-price-separator" 
                          style="color: var(--hph-gray-500); font-size: var(--hph-text-sm); font-weight: var(--hph-font-medium);">
                        <?php _e('to', 'happy-place-theme'); ?>
                    </span>
                    <input 
                        type="number" 
                        name="max_price" 
                        id="max_price"
                        class="hph-input"
                        style="flex: 1; padding: var(--hph-padding-sm) var(--hph-padding-md); border: 2px solid var(--hph-gray-200); border-radius: var(--hph-radius-md); font-size: var(--hph-text-base); color: var(--hph-gray-900); background: var(--hph-white); transition: all 0.2s ease; outline: none;"
                        placeholder="<?php _e('Max Price', 'happy-place-theme'); ?>"
                        value="<?php echo esc_attr($_GET['max_price'] ?? ''); ?>"
                        min="0"
                        step="1000"
                        onfocus="this.style.borderColor='var(--hph-primary)'; this.style.boxShadow='0 0 0 3px rgba(80, 186, 225, 0.1)'"
                        onblur="this.style.borderColor='var(--hph-gray-200)'; this.style.boxShadow='none'"
                    />
                </div>
            </div>
        </div>

        <div class="hph-filter-row" 
             style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--hph-gap-md); margin-bottom: var(--hph-margin-md);">
            <div class="hph-filter-field">
                <label for="bedrooms" 
                       style="display: block; margin-bottom: var(--hph-margin-xs); font-size: var(--hph-text-sm); font-weight: var(--hph-font-medium); color: var(--hph-gray-700);">
                    <?php _e('Bedrooms', 'happy-place-theme'); ?>
                </label>
                <select name="bedrooms" 
                        id="bedrooms" 
                        class="hph-select"
                        style="width: 100%; padding: var(--hph-padding-sm) var(--hph-padding-md); border: 2px solid var(--hph-gray-200); border-radius: var(--hph-radius-md); font-size: var(--hph-text-base); color: var(--hph-gray-900); background: var(--hph-white); transition: all 0.2s ease; outline: none; cursor: pointer;"
                        onfocus="this.style.borderColor='var(--hph-primary)'; this.style.boxShadow='0 0 0 3px rgba(80, 186, 225, 0.1)'"
                        onblur="this.style.borderColor='var(--hph-gray-200)'; this.style.boxShadow='none'">
                    <option value=""><?php _e('Any', 'happy-place-theme'); ?></option>
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php selected($_GET['bedrooms'] ?? '', $i); ?>>
                            <?php echo $i . ($i == 6 ? '+' : ''); ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="hph-filter-field">
                <label for="bathrooms" 
                       style="display: block; margin-bottom: var(--hph-margin-xs); font-size: var(--hph-text-sm); font-weight: var(--hph-font-medium); color: var(--hph-gray-700);">
                    <?php _e('Bathrooms', 'happy-place-theme'); ?>
                </label>
                <select name="bathrooms" 
                        id="bathrooms" 
                        class="hph-select"
                        style="width: 100%; padding: var(--hph-padding-sm) var(--hph-padding-md); border: 2px solid var(--hph-gray-200); border-radius: var(--hph-radius-md); font-size: var(--hph-text-base); color: var(--hph-gray-900); background: var(--hph-white); transition: all 0.2s ease; outline: none; cursor: pointer;"
                        onfocus="this.style.borderColor='var(--hph-primary)'; this.style.boxShadow='0 0 0 3px rgba(80, 186, 225, 0.1)'"
                        onblur="this.style.borderColor='var(--hph-gray-200)'; this.style.boxShadow='none'">
                    <option value=""><?php _e('Any', 'happy-place-theme'); ?></option>
                    <?php 
                    $bathroom_options = [1, 1.5, 2, 2.5, 3, 3.5, 4, '4+'];
                    foreach ($bathroom_options as $option): 
                    ?>
                        <option value="<?php echo $option; ?>" <?php selected($_GET['bathrooms'] ?? '', $option); ?>>
                            <?php echo $option; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="filter-row">
            <div class="filter-field">
                <label for="property_type"><?php _e('Property Type', 'happy-place-theme'); ?></label>
                <select name="property_type" id="property_type">
                    <option value=""><?php _e('Any Type', 'happy-place-theme'); ?></option>
                    <option value="single_family" <?php selected($_GET['property_type'] ?? '', 'single_family'); ?>><?php _e('Single Family', 'happy-place-theme'); ?></option>
                    <option value="condo" <?php selected($_GET['property_type'] ?? '', 'condo'); ?>><?php _e('Condo', 'happy-place-theme'); ?></option>
                    <option value="townhouse" <?php selected($_GET['property_type'] ?? '', 'townhouse'); ?>><?php _e('Townhouse', 'happy-place-theme'); ?></option>
                    <option value="multi_family" <?php selected($_GET['property_type'] ?? '', 'multi_family'); ?>><?php _e('Multi Family', 'happy-place-theme'); ?></option>
                    <option value="land" <?php selected($_GET['property_type'] ?? '', 'land'); ?>><?php _e('Land', 'happy-place-theme'); ?></option>
                    <option value="commercial" <?php selected($_GET['property_type'] ?? '', 'commercial'); ?>><?php _e('Commercial', 'happy-place-theme'); ?></option>
                </select>
            </div>
            
            <div class="filter-field">
                <label for="status"><?php _e('Status', 'happy-place-theme'); ?></label>
                <select name="status" id="status">
                    <option value=""><?php _e('Any Status', 'happy-place-theme'); ?></option>
                    <option value="active" <?php selected($_GET['status'] ?? '', 'active'); ?>><?php _e('Active', 'happy-place-theme'); ?></option>
                    <option value="pending" <?php selected($_GET['status'] ?? '', 'pending'); ?>><?php _e('Pending', 'happy-place-theme'); ?></option>
                    <option value="sold" <?php selected($_GET['status'] ?? '', 'sold'); ?>><?php _e('Recently Sold', 'happy-place-theme'); ?></option>
                </select>
            </div>
        </div>

        <div class="filter-row">
            <div class="filter-field checkbox-field">
                <label>
                    <input 
                        type="checkbox" 
                        name="waterfront" 
                        value="1"
                        <?php checked($_GET['waterfront'] ?? '', '1'); ?>
                    />
                    <?php _e('Waterfront', 'happy-place-theme'); ?>
                </label>
            </div>
            
            <div class="filter-field checkbox-field">
                <label>
                    <input 
                        type="checkbox" 
                        name="new_construction" 
                        value="1"
                        <?php checked($_GET['new_construction'] ?? '', '1'); ?>
                    />
                    <?php _e('New Construction', 'happy-place-theme'); ?>
                </label>
            </div>
        </div>
    </div>

    <!-- Agent Specific Filters -->
    <div class="filter-group agent-filters" data-show-for="agent,all">
        <h4 class="filter-group-title"><?php _e('Agent Filters', 'happy-place-theme'); ?></h4>
        
        <div class="filter-row">
            <div class="filter-field">
                <label for="specialty"><?php _e('Specialty', 'happy-place-theme'); ?></label>
                <select name="specialty" id="specialty">
                    <option value=""><?php _e('Any Specialty', 'happy-place-theme'); ?></option>
                    <option value="buyer_agent" <?php selected($_GET['specialty'] ?? '', 'buyer_agent'); ?>><?php _e('Buyer\'s Agent', 'happy-place-theme'); ?></option>
                    <option value="listing_agent" <?php selected($_GET['specialty'] ?? '', 'listing_agent'); ?>><?php _e('Listing Agent', 'happy-place-theme'); ?></option>
                    <option value="luxury" <?php selected($_GET['specialty'] ?? '', 'luxury'); ?>><?php _e('Luxury Properties', 'happy-place-theme'); ?></option>
                    <option value="first_time_buyers" <?php selected($_GET['specialty'] ?? '', 'first_time_buyers'); ?>><?php _e('First-Time Buyers', 'happy-place-theme'); ?></option>
                    <option value="investment" <?php selected($_GET['specialty'] ?? '', 'investment'); ?>><?php _e('Investment Properties', 'happy-place-theme'); ?></option>
                    <option value="commercial" <?php selected($_GET['specialty'] ?? '', 'commercial'); ?>><?php _e('Commercial', 'happy-place-theme'); ?></option>
                </select>
            </div>
            
            <div class="filter-field">
                <label for="language"><?php _e('Language', 'happy-place-theme'); ?></label>
                <select name="language" id="language">
                    <option value=""><?php _e('Any Language', 'happy-place-theme'); ?></option>
                    <option value="english" <?php selected($_GET['language'] ?? '', 'english'); ?>><?php _e('English', 'happy-place-theme'); ?></option>
                    <option value="spanish" <?php selected($_GET['language'] ?? '', 'spanish'); ?>><?php _e('Spanish', 'happy-place-theme'); ?></option>
                    <option value="french" <?php selected($_GET['language'] ?? '', 'french'); ?>><?php _e('French', 'happy-place-theme'); ?></option>
                </select>
            </div>
        </div>

        <div class="filter-row">
            <div class="filter-field">
                <label for="experience"><?php _e('Experience', 'happy-place-theme'); ?></label>
                <select name="experience" id="experience">
                    <option value=""><?php _e('Any Experience', 'happy-place-theme'); ?></option>
                    <option value="1-5" <?php selected($_GET['experience'] ?? '', '1-5'); ?>><?php _e('1-5 years', 'happy-place-theme'); ?></option>
                    <option value="5-10" <?php selected($_GET['experience'] ?? '', '5-10'); ?>><?php _e('5-10 years', 'happy-place-theme'); ?></option>
                    <option value="10-15" <?php selected($_GET['experience'] ?? '', '10-15'); ?>><?php _e('10-15 years', 'happy-place-theme'); ?></option>
                    <option value="15+" <?php selected($_GET['experience'] ?? '', '15+'); ?>><?php _e('15+ years', 'happy-place-theme'); ?></option>
                </select>
            </div>
        </div>
    </div>

    <!-- Community Specific Filters -->
    <div class="filter-group community-filters" data-show-for="community,all">
        <h4 class="filter-group-title"><?php _e('Community Filters', 'happy-place-theme'); ?></h4>
        
        <div class="filter-row">
            <div class="filter-field">
                <label for="price_range"><?php _e('Price Range', 'happy-place-theme'); ?></label>
                <select name="price_range" id="price_range">
                    <option value=""><?php _e('Any Range', 'happy-place-theme'); ?></option>
                    <option value="affordable" <?php selected($_GET['price_range'] ?? '', 'affordable'); ?>><?php _e('Affordable (Under $300k)', 'happy-place-theme'); ?></option>
                    <option value="moderate" <?php selected($_GET['price_range'] ?? '', 'moderate'); ?>><?php _e('Moderate ($300k-$600k)', 'happy-place-theme'); ?></option>
                    <option value="upscale" <?php selected($_GET['price_range'] ?? '', 'upscale'); ?>><?php _e('Upscale ($600k-$1M)', 'happy-place-theme'); ?></option>
                    <option value="luxury" <?php selected($_GET['price_range'] ?? '', 'luxury'); ?>><?php _e('Luxury ($1M+)', 'happy-place-theme'); ?></option>
                </select>
            </div>
        </div>
    </div>

    <!-- City Specific Filters -->
    <div class="filter-group city-filters" data-show-for="city,all">
        <h4 class="filter-group-title"><?php _e('City Filters', 'happy-place-theme'); ?></h4>
        
        <div class="filter-row">
            <div class="filter-field">
                <label for="state"><?php _e('State', 'happy-place-theme'); ?></label>
                <select name="state" id="state">
                    <option value=""><?php _e('Any State', 'happy-place-theme'); ?></option>
                    <option value="DE" <?php selected($_GET['state'] ?? '', 'DE'); ?>><?php _e('Delaware', 'happy-place-theme'); ?></option>
                    <option value="MD" <?php selected($_GET['state'] ?? '', 'MD'); ?>><?php _e('Maryland', 'happy-place-theme'); ?></option>
                    <option value="PA" <?php selected($_GET['state'] ?? '', 'PA'); ?>><?php _e('Pennsylvania', 'happy-place-theme'); ?></option>
                </select>
            </div>
            
            <div class="filter-field">
                <label for="population"><?php _e('Population', 'happy-place-theme'); ?></label>
                <select name="population" id="population">
                    <option value=""><?php _e('Any Size', 'happy-place-theme'); ?></option>
                    <option value="small" <?php selected($_GET['population'] ?? '', 'small'); ?>><?php _e('Small (Under 10k)', 'happy-place-theme'); ?></option>
                    <option value="medium" <?php selected($_GET['population'] ?? '', 'medium'); ?>><?php _e('Medium (10k-50k)', 'happy-place-theme'); ?></option>
                    <option value="large" <?php selected($_GET['population'] ?? '', 'large'); ?>><?php _e('Large (50k+)', 'happy-place-theme'); ?></option>
                </select>
            </div>
        </div>
    </div>

    <!-- Filter Actions -->
    <div class="hph-filter-actions" 
         style="display: flex; flex-wrap: wrap; gap: var(--hph-gap-md); align-items: center; justify-content: flex-end; padding-top: var(--hph-padding-lg); border-top: 1px solid var(--hph-gray-200); margin-top: var(--hph-margin-lg);">
        <button type="button" 
                class="hph-btn hph-btn-outline hph-clear-filters-btn"
                style="display: inline-flex; align-items: center; justify-content: center; gap: var(--hph-gap-sm); padding: var(--hph-padding-sm) var(--hph-padding-lg); border: 2px solid var(--hph-gray-300); border-radius: var(--hph-radius-md); background: transparent; color: var(--hph-gray-700); font-size: var(--hph-text-sm); font-weight: var(--hph-font-medium); cursor: pointer; transition: all 0.2s ease; outline: none;"
                onmouseover="this.style.borderColor='var(--hph-gray-400)'; this.style.color='var(--hph-gray-800)'"
                onmouseout="this.style.borderColor='var(--hph-gray-300)'; this.style.color='var(--hph-gray-700)'"
                onclick="this.style.transform='scale(0.98)'; setTimeout(() => this.style.transform='scale(1)', 100)">
            <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
            <?php _e('Clear All Filters', 'happy-place-theme'); ?>
        </button>
        <button type="submit" 
                class="hph-btn hph-btn-primary hph-apply-filters-btn"
                style="display: inline-flex; align-items: center; justify-content: center; gap: var(--hph-gap-sm); padding: var(--hph-padding-sm) var(--hph-padding-lg); border: 2px solid var(--hph-primary); border-radius: var(--hph-radius-md); background: var(--hph-primary); color: var(--hph-white); font-size: var(--hph-text-sm); font-weight: var(--hph-font-semibold); cursor: pointer; transition: all 0.2s ease; outline: none; box-shadow: 0 2px 4px rgba(80, 186, 225, 0.2);"
                onmouseover="this.style.background='var(--hph-primary-600)'; this.style.borderColor='var(--hph-primary-600)'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(80, 186, 225, 0.3)'"
                onmouseout="this.style.background='var(--hph-primary)'; this.style.borderColor='var(--hph-primary)'; this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(80, 186, 225, 0.2)'"
                onclick="this.style.transform='scale(0.98)'; setTimeout(() => this.style.transform='scale(1)', 100)">
            <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
            </svg>
            <?php _e('Apply Filters', 'happy-place-theme'); ?>
        </button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filtersContainer = document.querySelector('[data-form-id="<?php echo esc_js($form_id); ?>"] .hph-search-filters');
    const postTypeSelect = document.querySelector('[data-form-id="<?php echo esc_js($form_id); ?>"] .search-type-select');
    const clearFiltersBtn = filtersContainer.querySelector('.clear-filters-btn');
    
    // Show/hide filter groups based on post type selection
    function toggleFilterGroups(postType) {
        const filterGroups = filtersContainer.querySelectorAll('.filter-group[data-show-for]');
        
        filterGroups.forEach(group => {
            const showFor = group.dataset.showFor.split(',');
            const shouldShow = showFor.includes(postType) || showFor.includes('all');
            group.style.display = shouldShow ? 'block' : 'none';
        });
    }
    
    // Initialize filter visibility
    if (postTypeSelect) {
        toggleFilterGroups(postTypeSelect.value);
        
        postTypeSelect.addEventListener('change', function() {
            toggleFilterGroups(this.value);
        });
    }
    
    // Clear all filters functionality
    clearFiltersBtn.addEventListener('click', function() {
        const form = filtersContainer.closest('form');
        const inputs = form.querySelectorAll('input:not([type="hidden"]), select');
        
        inputs.forEach(input => {
            if (input.type === 'checkbox') {
                input.checked = false;
            } else if (input.name !== 's' && input.name !== 'type') {
                input.value = '';
            }
        });
    });
});
</script>
