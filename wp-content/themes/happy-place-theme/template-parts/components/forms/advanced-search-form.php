<?php
/**
 * Template part for advanced property search form
 *
 * @package HappyPlaceTheme
 */

// Default attributes following utility-first standards
$form_args = wp_parse_args($args ?? [], [
    'style' => 'default',
    'layout' => 'horizontal',
    'size' => 'lg',
    'background' => 'white',
    'show_tabs' => true,
    'compact' => false
]);

// Build form container classes
$form_classes = [
    'hph-advanced-search-form',
    'hph-bg-white',
    'hph-rounded-lg',
    'hph-shadow-lg',
    'hph-transition-shadow',
    'hph-duration-300'
];

// Size variations
$size_classes = [
    'sm' => ['hph-p-md'],
    'md' => ['hph-p-lg'],
    'lg' => ['hph-p-xl']
];

$form_classes = array_merge($form_classes, $size_classes[$form_args['size']] ?? $size_classes['lg']);

if ($form_args['compact']) {
    $form_classes[] = 'hph-compact-form';
}
?>

<div class="<?php echo esc_attr(implode(' ', $form_classes)); ?>">
    <h3 class="hph-text-xl hph-font-semibold hph-mb-lg hph-text-gray-900"><?php esc_html_e('Find Your Perfect Property', 'happy-place-theme'); ?></h3>
    
    <form class="property-search" method="GET" action="<?php echo esc_url(get_post_type_archive_link('listing')); ?>">
        
        <!-- Search Type Tabs -->
        <div class="hph-flex hph-border-b hph-border-gray-200 hph-mb-xl">
            <button type="button" class="hph-px-lg hph-py-md hph-text-sm hph-font-medium hph-border-b-2 hph-border-primary-600 hph-text-primary-600 hph-bg-primary-50" data-type="buy">
                <?php esc_html_e('Buy', 'happy-place-theme'); ?>
            </button>
            <button type="button" class="hph-px-lg hph-py-md hph-text-sm hph-font-medium hph-text-gray-500 hph-hover:text-primary-600" data-type="rent">
                <?php esc_html_e('Rent', 'happy-place-theme'); ?>
            </button>
            <button type="button" class="hph-px-lg hph-py-md hph-text-sm hph-font-medium hph-text-gray-500 hph-hover:text-primary-600" data-type="sold">
                <?php esc_html_e('Sold', 'happy-place-theme'); ?>
            </button>
        </div>
        
        <!-- Primary Search Fields -->
        <div class="hph-grid hph-grid-cols-1 md:hph-grid-cols-3 hph-gap-lg hph-mb-xl">
            <div class="hph-space-y-sm">
                <label class="hph-block hph-text-sm hph-font-medium hph-text-gray-700"><?php esc_html_e('Location', 'happy-place-theme'); ?></label>
                <div class="hph-relative">
                    <i class="fas fa-map-marker-alt hph-absolute hph-left-md hph-top-1/2 hph-transform hph--translate-y-1/2 hph-text-gray-400"></i>
                    <input type="text" name="search" 
                           placeholder="<?php esc_attr_e('City, neighborhood, or address', 'happy-place-theme'); ?>" 
                           class="hph-w-full hph-pl-lg hph-pr-md hph-py-md hph-border hph-border-gray-300 hph-rounded-lg hph-focus:border-primary-500 hph-focus:ring-2 hph-focus:ring-primary-100">
                </div>
            </div>
            
            <div class="hph-space-y-sm">
                <label class="hph-block hph-text-sm hph-font-medium hph-text-gray-700"><?php esc_html_e('Property Type', 'happy-place-theme'); ?></label>
                <select name="property_type" class="hph-w-full hph-px-md hph-py-md hph-border hph-border-gray-300 hph-rounded-lg hph-focus:border-primary-500 hph-focus:ring-2 hph-focus:ring-primary-100">
                    <option value=""><?php esc_html_e('All Types', 'happy-place-theme'); ?></option>
                    <option value="house"><?php esc_html_e('House', 'happy-place-theme'); ?></option>
                    <option value="condo"><?php esc_html_e('Condo', 'happy-place-theme'); ?></option>
                    <option value="townhouse"><?php esc_html_e('Townhouse', 'happy-place-theme'); ?></option>
                    <option value="apartment"><?php esc_html_e('Apartment', 'happy-place-theme'); ?></option>
                    <option value="land"><?php esc_html_e('Land', 'happy-place-theme'); ?></option>
                </select>
            </div>
            
            <div class="hph-space-y-sm">
                <label class="hph-block hph-text-sm hph-font-medium hph-text-gray-700"><?php esc_html_e('Price Range', 'happy-place-theme'); ?></label>
                <div class="hph-grid hph-grid-cols-2 hph-gap-sm">
                    <select name="min_price" class="hph-w-full hph-px-md hph-py-md hph-border hph-border-gray-300 hph-rounded-lg hph-focus:border-primary-500 hph-focus:ring-2 hph-focus:ring-primary-100">
                        <option value=""><?php esc_html_e('Min', 'happy-place-theme'); ?></option>
                        <option value="100000">$100K</option>
                        <option value="200000">$200K</option>
                        <option value="300000">$300K</option>
                        <option value="400000">$400K</option>
                        <option value="500000">$500K</option>
                        <option value="750000">$750K</option>
                        <option value="1000000">$1M</option>
                    </select>
                    <select name="max_price" class="hph-w-full hph-px-md hph-py-md hph-border hph-border-gray-300 hph-rounded-lg hph-focus:border-primary-500 hph-focus:ring-2 hph-focus:ring-primary-100">
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
        <div class="hph-mb-lg">
            <button type="button" class="hph-text-primary-600 hph-hover:text-primary-700 hph-flex hph-items-center hph-gap-sm hph-font-medium hph-transition-colors hph-duration-200" onclick="toggleAdvancedFilters()">
                <span><?php esc_html_e('More Filters', 'happy-place-theme'); ?></span>
                <i class="fas fa-chevron-down hph-transition-transform hph-duration-200" id="advanced-toggle-icon"></i>
            </button>
        </div>
        
        <div class="hph-hidden" id="advanced-filters">
            <div class="hph-grid hph-grid-cols-1 md:hph-grid-cols-4 hph-gap-lg hph-mb-xl">
                <div class="hph-space-y-sm">
                    <label class="hph-block hph-text-sm hph-font-medium hph-text-gray-700"><?php esc_html_e('Bedrooms', 'happy-place-theme'); ?></label>
                    <select name="bedrooms" class="hph-w-full hph-px-md hph-py-md hph-border hph-border-gray-300 hph-rounded-lg hph-focus:border-primary-500 hph-focus:ring-2 hph-focus:ring-primary-100">
                        <option value=""><?php esc_html_e('Any', 'happy-place-theme'); ?></option>
                        <option value="1">1+</option>
                        <option value="2">2+</option>
                        <option value="3">3+</option>
                        <option value="4">4+</option>
                        <option value="5">5+</option>
                    </select>
                </div>
                
                <div class="hph-space-y-sm">
                    <label class="hph-block hph-text-sm hph-font-medium hph-text-gray-700"><?php esc_html_e('Bathrooms', 'happy-place-theme'); ?></label>
                    <select name="bathrooms" class="hph-w-full hph-px-md hph-py-md hph-border hph-border-gray-300 hph-rounded-lg hph-focus:border-primary-500 hph-focus:ring-2 hph-focus:ring-primary-100">
                        <option value=""><?php esc_html_e('Any', 'happy-place-theme'); ?></option>
                        <option value="1">1+</option>
                        <option value="2">2+</option>
                        <option value="3">3+</option>
                        <option value="4">4+</option>
                    </select>
                </div>
                
                <div class="hph-space-y-sm">
                    <label class="hph-block hph-text-sm hph-font-medium hph-text-gray-700"><?php esc_html_e('Square Feet', 'happy-place-theme'); ?></label>
                    <div class="hph-grid hph-grid-cols-2 hph-gap-sm">
                        <input type="number" name="min_sqft" placeholder="Min" class="hph-w-full hph-px-md hph-py-md hph-border hph-border-gray-300 hph-rounded-lg hph-focus:border-primary-500 hph-focus:ring-2 hph-focus:ring-primary-100">
                        <input type="number" name="max_sqft" placeholder="Max" class="hph-w-full hph-px-md hph-py-md hph-border hph-border-gray-300 hph-rounded-lg hph-focus:border-primary-500 hph-focus:ring-2 hph-focus:ring-primary-100">
                    </div>
                </div>
                
                <div class="hph-space-y-sm">
                    <label class="hph-block hph-text-sm hph-font-medium hph-text-gray-700"><?php esc_html_e('Year Built', 'happy-place-theme'); ?></label>
                    <div class="hph-grid hph-grid-cols-2 hph-gap-sm">
                        <input type="number" name="min_year" placeholder="From" class="hph-w-full hph-px-md hph-py-md hph-border hph-border-gray-300 hph-rounded-lg hph-focus:border-primary-500 hph-focus:ring-2 hph-focus:ring-primary-100">
                        <input type="number" name="max_year" placeholder="To" class="hph-w-full hph-px-md hph-py-md hph-border hph-border-gray-300 hph-rounded-lg hph-focus:border-primary-500 hph-focus:ring-2 hph-focus:ring-primary-100">
                    </div>
                </div>
            </div>
            
            <!-- Features -->
            <div class="hph-mb-xl">
                <label class="hph-block hph-text-sm hph-font-medium hph-text-gray-700 hph-mb-md"><?php esc_html_e('Features', 'happy-place-theme'); ?></label>
                <div class="hph-grid hph-grid-cols-2 md:hph-grid-cols-4 hph-gap-md">
                    <label class="hph-flex hph-items-center">
                        <input type="checkbox" name="features[]" value="pool" class="hph-mr-sm hph-text-primary-600 hph-focus:ring-primary-500">
                        <span class="hph-text-sm"><?php esc_html_e('Pool', 'happy-place-theme'); ?></span>
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
        <div class="hph-flex hph-gap-md">
            <button type="submit" class="hph-flex-1 hph-inline-flex hph-items-center hph-justify-center hph-gap-sm hph-px-xl hph-py-md hph-bg-primary-600 hph-text-white hph-font-semibold hph-rounded-lg hph-transition-all hph-duration-300 hph-hover:bg-primary-700 hph-hover:scale-105">
                <i class="fas fa-search"></i>
                <?php esc_html_e('Search Properties', 'happy-place-theme'); ?>
            </button>
            <button type="button" class="hph-px-lg hph-py-md hph-border-2 hph-border-gray-300 hph-text-gray-700 hph-font-medium hph-rounded-lg hph-transition-all hph-duration-300 hph-hover:border-gray-400 hph-hover:bg-gray-50" onclick="resetForm()">
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
