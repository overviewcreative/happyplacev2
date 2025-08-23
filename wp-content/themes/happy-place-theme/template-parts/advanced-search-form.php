<?php
/**
 * Template part for advanced property search form
 *
 * @package HappyPlaceTheme
 */
?>

<div class="advanced-search-form bg-white rounded-lg shadow-lg p-6">
    <h3 class="text-xl font-semibold mb-6"><?php esc_html_e('Find Your Perfect Property', 'happy-place-theme'); ?></h3>
    
    <form class="property-search" method="GET" action="<?php echo esc_url(get_post_type_archive_link('listing')); ?>">
        
        <!-- Search Type Tabs -->
        <div class="search-tabs flex border-b border-gray-200 mb-6">
            <button type="button" class="search-tab-hph-btn px-6 py-3 text-sm font-medium border-b-2 border-primary text-primary bg-primary-light" data-type="buy">
                <?php esc_html_e('Buy', 'happy-place-theme'); ?>
            </button>
            <button type="button" class="search-tab-hph-btn px-6 py-3 text-sm font-medium text-gray-500 hover:text-primary" data-type="rent">
                <?php esc_html_e('Rent', 'happy-place-theme'); ?>
            </button>
            <button type="button" class="search-tab-hph-btn px-6 py-3 text-sm font-medium text-gray-500 hover:text-primary" data-type="sold">
                <?php esc_html_e('Sold', 'happy-place-theme'); ?>
            </button>
        </div>
        
        <!-- Primary Search Fields -->
        <div class="search-primary grid grid-cols-1 md:grid-cols-3 form-row-spacing mb-6">
            <div class="search-field">
                <label class="block text-sm font-medium text-gray-700 mb-2"><?php esc_html_e('Location', 'happy-place-theme'); ?></label>
                <div class="relative">
                    <i class="fas fa-map-marker-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" placeholder="<?php esc_attr_e('City, neighborhood, or address', 'happy-place-theme'); ?>" class="form-input pl-10 w-full">
                </div>
            </div>
            
            <div class="search-field">
                <label class="block text-sm font-medium text-gray-700 mb-2"><?php esc_html_e('Property Type', 'happy-place-theme'); ?></label>
                <select name="property_type" class="form-select w-full">
                    <option value=""><?php esc_html_e('All Types', 'happy-place-theme'); ?></option>
                    <option value="house"><?php esc_html_e('House', 'happy-place-theme'); ?></option>
                    <option value="condo"><?php esc_html_e('Condo', 'happy-place-theme'); ?></option>
                    <option value="townhouse"><?php esc_html_e('Townhouse', 'happy-place-theme'); ?></option>
                    <option value="apartment"><?php esc_html_e('Apartment', 'happy-place-theme'); ?></option>
                    <option value="land"><?php esc_html_e('Land', 'happy-place-theme'); ?></option>
                </select>
            </div>
            
            <div class="search-field">
                <label class="block text-sm font-medium text-gray-700 mb-2"><?php esc_html_e('Price Range', 'happy-place-theme'); ?></label>
                <div class="grid grid-cols-2 gap-2">
                    <select name="min_price" class="form-select">
                        <option value=""><?php esc_html_e('Min', 'happy-place-theme'); ?></option>
                        <option value="100000">$100K</option>
                        <option value="200000">$200K</option>
                        <option value="300000">$300K</option>
                        <option value="400000">$400K</option>
                        <option value="500000">$500K</option>
                        <option value="750000">$750K</option>
                        <option value="1000000">$1M</option>
                    </select>
                    <select name="max_price" class="form-select">
                        <option value=""><?php esc_html_e('Max', 'happy-place-theme'); ?></option>
                        <option value="200000">$200K</option>
                        <option value="300000">$300K</option>
                        <option value="400000">$400K</option>
                        <option value="500000">$500K</option>
                        <option value="750000">$750K</option>
                        <option value="1000000">$1M</option>
                        <option value="1500000">$1.5M</option>
                        <option value="2000000">$2M+</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Advanced Filters (Collapsible) -->
        <div class="advanced-filters-toggle mb-4">
            <button type="button" class="advanced-toggle-hph-btn text-primary hover:text-primary-dark flex items-center gap-2" onclick="toggleAdvancedFilters()">
                <span><?php esc_html_e('More Filters', 'happy-place-theme'); ?></span>
                <i class="fas fa-chevron-down transition-transform" id="advanced-toggle-icon"></i>
            </button>
        </div>
        
        <div class="advanced-filters hidden" id="advanced-filters">
            <div class="grid grid-cols-1 md:grid-cols-4 form-row-spacing mb-6">
                <div class="search-field">
                    <label class="block text-sm font-medium text-gray-700 mb-2"><?php esc_html_e('Bedrooms', 'happy-place-theme'); ?></label>
                    <select name="bedrooms" class="form-select w-full">
                        <option value=""><?php esc_html_e('Any', 'happy-place-theme'); ?></option>
                        <option value="1">1+</option>
                        <option value="2">2+</option>
                        <option value="3">3+</option>
                        <option value="4">4+</option>
                        <option value="5">5+</option>
                    </select>
                </div>
                
                <div class="search-field">
                    <label class="block text-sm font-medium text-gray-700 mb-2"><?php esc_html_e('Bathrooms', 'happy-place-theme'); ?></label>
                    <select name="bathrooms" class="form-select w-full">
                        <option value=""><?php esc_html_e('Any', 'happy-place-theme'); ?></option>
                        <option value="1">1+</option>
                        <option value="2">2+</option>
                        <option value="3">3+</option>
                        <option value="4">4+</option>
                    </select>
                </div>
                
                <div class="search-field">
                    <label class="block text-sm font-medium text-gray-700 mb-2"><?php esc_html_e('Square Feet', 'happy-place-theme'); ?></label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" name="min_sqft" placeholder="Min" class="form-input">
                        <input type="number" name="max_sqft" placeholder="Max" class="form-input">
                    </div>
                </div>
                
                <div class="search-field">
                    <label class="block text-sm font-medium text-gray-700 mb-2"><?php esc_html_e('Year Built', 'happy-place-theme'); ?></label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" name="min_year" placeholder="From" class="form-input">
                        <input type="number" name="max_year" placeholder="To" class="form-input">
                    </div>
                </div>
            </div>
            
            <!-- Features -->
            <div class="search-features mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3"><?php esc_html_e('Features', 'happy-place-theme'); ?></label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <label class="flex items-center">
                        <input type="checkbox" name="features[]" value="pool" class="mr-2">
                        <span class="text-sm"><?php esc_html_e('Pool', 'happy-place-theme'); ?></span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="features[]" value="garage" class="mr-2">
                        <span class="text-sm"><?php esc_html_e('Garage', 'happy-place-theme'); ?></span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="features[]" value="fireplace" class="mr-2">
                        <span class="text-sm"><?php esc_html_e('Fireplace', 'happy-place-theme'); ?></span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="features[]" value="waterfront" class="mr-2">
                        <span class="text-sm"><?php esc_html_e('Waterfront', 'happy-place-theme'); ?></span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="features[]" value="new_construction" class="mr-2">
                        <span class="text-sm"><?php esc_html_e('New Construction', 'happy-place-theme'); ?></span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="features[]" value="gated_community" class="mr-2">
                        <span class="text-sm"><?php esc_html_e('Gated Community', 'happy-place-theme'); ?></span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="features[]" value="golf_course" class="mr-2">
                        <span class="text-sm"><?php esc_html_e('Golf Course', 'happy-place-theme'); ?></span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="features[]" value="mountain_view" class="mr-2">
                        <span class="text-sm"><?php esc_html_e('Mountain View', 'happy-place-theme'); ?></span>
                    </label>
                </div>
            </div>
        </div>
        
        <!-- Search Button -->
        <div class="search-actions flex gap-4">
            <button type="submit" class="btn hph-btn-primary flex-1 flex items-center justify-center gap-2">
                <i class="fas fa-search"></i>
                <?php esc_html_e('Search Properties', 'happy-place-theme'); ?>
            </button>
            <button type="button" class="btn hph-btn-outline" onclick="resetForm()">
                <?php esc_html_e('Reset', 'happy-place-theme'); ?>
            </button>
        </div>
        
    </form>
</div>

<script>
function toggleAdvancedFilters() {
    const filters = document.getElementById('advanced-filters');
    const icon = document.getElementById('advanced-toggle-icon');
    
    if (filters.classList.contains('hidden')) {
        filters.classList.remove('hidden');
        icon.style.transform = 'rotate(180deg)';
    } else {
        filters.classList.add('hidden');
        icon.style.transform = 'rotate(0deg)';
    }
}

function resetForm() {
    document.querySelector('.property-search').reset();
}

// Tab switching functionality
document.querySelectorAll('.search-tab-btn').forEach(tab => {
    tab.addEventListener('click', function() {
        // Remove active class from all tabs
        document.querySelectorAll('.search-tab-btn').forEach(t => {
            t.classList.remove('border-primary', 'text-primary', 'bg-primary-light');
            t.classList.add('text-gray-500');
        });
        
        // Add active class to clicked tab
        this.classList.add('border-primary', 'text-primary', 'bg-primary-light');
        this.classList.remove('text-gray-500');
        
        // You can add logic here to modify form fields based on search type
        const searchType = this.dataset.type;
        console.log('Search type changed to:', searchType);
    });
});
</script>
