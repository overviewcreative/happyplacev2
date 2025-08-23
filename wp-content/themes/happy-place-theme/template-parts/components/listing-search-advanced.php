<?php
/**
 * Advanced Search Form Component
 * Location: /template-parts/components/listing-search-advanced.php
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Get current filter values
$current_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$current_type = isset($_GET['property_type']) ? sanitize_text_field($_GET['property_type']) : '';
$current_min_price = isset($_GET['min_price']) ? intval($_GET['min_price']) : '';
$current_max_price = isset($_GET['max_price']) ? intval($_GET['max_price']) : '';
$current_beds = isset($_GET['beds']) ? intval($_GET['beds']) : '';
$current_baths = isset($_GET['baths']) ? intval($_GET['baths']) : '';
$current_min_sqft = isset($_GET['min_sqft']) ? intval($_GET['min_sqft']) : '';
$current_max_sqft = isset($_GET['max_sqft']) ? intval($_GET['max_sqft']) : '';
$current_city = isset($_GET['city']) ? sanitize_text_field($_GET['city']) : '';
$current_keywords = isset($_GET['keywords']) ? sanitize_text_field($_GET['keywords']) : '';
?>

<div class="hph-advanced-search">
    <div class="hph-container">
        <form id="advanced-search-form" class="hph-search-form" method="GET">
            
            <div class="hph-search-form__grid">
                
                <!-- Keywords -->
                <div class="hph-form-group hph-form-group--full">
                    <label for="search-keywords">Keywords</label>
                    <input type="text" 
                           id="search-keywords" 
                           name="keywords" 
                           class="hph-form-control" 
                           value="<?php echo esc_attr($current_keywords); ?>"
                           placeholder="Enter keywords...">
                </div>
                
                <!-- Status -->
                <div class="hph-form-group">
                    <label for="search-status">Status</label>
                    <select id="search-status" name="status" class="hph-form-control">
                        <option value="">Any Status</option>
                        <?php
                        $statuses = get_terms(array(
                            'taxonomy' => 'listing_status',
                            'hide_empty' => false,
                        ));
                        
                        if (!is_wp_error($statuses) && !empty($statuses)) {
                            foreach ($statuses as $status) {
                                printf(
                                    '<option value="%s"%s>%s</option>',
                                    esc_attr($status->slug),
                                    selected($current_status, $status->slug, false),
                                    esc_html($status->name)
                                );
                            }
                        }
                        ?>
                    </select>
                </div>
                
                <!-- Property Type -->
                <div class="hph-form-group">
                    <label for="search-type">Property Type</label>
                    <select id="search-type" name="property_type" class="hph-form-control">
                        <option value="">Any Type</option>
                        <?php
                        $types = get_terms(array(
                            'taxonomy' => 'property_type',
                            'hide_empty' => false,
                        ));
                        
                        if (!is_wp_error($types) && !empty($types)) {
                            foreach ($types as $type) {
                                printf(
                                    '<option value="%s"%s>%s</option>',
                                    esc_attr($type->slug),
                                    selected($current_type, $type->slug, false),
                                    esc_html($type->name)
                                );
                            }
                        }
                        ?>
                    </select>
                </div>
                
                <!-- Price Range -->
                <div class="hph-form-group">
                    <label for="search-min-price">Min Price</label>
                    <select id="search-min-price" name="min_price" class="hph-form-control">
                        <option value="">No Min</option>
                        <?php
                        $price_ranges = array(
                            50000 => '$50,000',
                            100000 => '$100,000',
                            150000 => '$150,000',
                            200000 => '$200,000',
                            250000 => '$250,000',
                            300000 => '$300,000',
                            400000 => '$400,000',
                            500000 => '$500,000',
                            750000 => '$750,000',
                            1000000 => '$1,000,000',
                            1500000 => '$1,500,000',
                            2000000 => '$2,000,000',
                        );
                        
                        foreach ($price_ranges as $value => $label) {
                            printf(
                                '<option value="%d"%s>%s</option>',
                                $value,
                                selected($current_min_price, $value, false),
                                $label
                            );
                        }
                        ?>
                    </select>
                </div>
                
                <div class="hph-form-group">
                    <label for="search-max-price">Max Price</label>
                    <select id="search-max-price" name="max_price" class="hph-form-control">
                        <option value="">No Max</option>
                        <?php
                        foreach ($price_ranges as $value => $label) {
                            printf(
                                '<option value="%d"%s>%s</option>',
                                $value,
                                selected($current_max_price, $value, false),
                                $label
                            );
                        }
                        ?>
                    </select>
                </div>
                
                <!-- Bedrooms -->
                <div class="hph-form-group">
                    <label for="search-beds">Bedrooms</label>
                    <select id="search-beds" name="beds" class="hph-form-control">
                        <option value="">Any</option>
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php selected($current_beds, $i); ?>>
                                <?php echo $i; ?>+ Bed<?php echo $i > 1 ? 's' : ''; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <!-- Bathrooms -->
                <div class="hph-form-group">
                    <label for="search-baths">Bathrooms</label>
                    <select id="search-baths" name="baths" class="hph-form-control">
                        <option value="">Any</option>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php selected($current_baths, $i); ?>>
                                <?php echo $i; ?>+ Bath<?php echo $i > 1 ? 's' : ''; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <!-- Square Footage -->
                <div class="hph-form-group">
                    <label for="search-min-sqft">Min Sq Ft</label>
                    <select id="search-min-sqft" name="min_sqft" class="hph-form-control">
                        <option value="">No Min</option>
                        <?php
                        $sqft_ranges = array(
                            500 => '500+',
                            750 => '750+',
                            1000 => '1,000+',
                            1250 => '1,250+',
                            1500 => '1,500+',
                            1750 => '1,750+',
                            2000 => '2,000+',
                            2500 => '2,500+',
                            3000 => '3,000+',
                            3500 => '3,500+',
                            4000 => '4,000+',
                            5000 => '5,000+',
                        );
                        
                        foreach ($sqft_ranges as $value => $label) {
                            printf(
                                '<option value="%d"%s>%s sq ft</option>',
                                $value,
                                selected($current_min_sqft, $value, false),
                                $label
                            );
                        }
                        ?>
                    </select>
                </div>
                
                <div class="hph-form-group">
                    <label for="search-max-sqft">Max Sq Ft</label>
                    <select id="search-max-sqft" name="max_sqft" class="hph-form-control">
                        <option value="">No Max</option>
                        <?php
                        foreach ($sqft_ranges as $value => $label) {
                            printf(
                                '<option value="%d"%s>%s sq ft</option>',
                                $value,
                                selected($current_max_sqft, $value, false),
                                $label
                            );
                        }
                        ?>
                    </select>
                </div>
                
                <!-- City -->
                <div class="hph-form-group">
                    <label for="search-city">City</label>
                    <input type="text" 
                           id="search-city" 
                           name="city" 
                           class="hph-form-control" 
                           value="<?php echo esc_attr($current_city); ?>"
                           placeholder="Enter city...">
                </div>
                
            </div>
            
            <!-- Form Actions -->
            <div class="hph-search-form__actions">
                <button type="button" class="hph-btn hph-btn--ghost" id="clear-search">
                    <i class="fas fa-times"></i>
                    Clear All
                </button>
                <button type="submit" class="hph-btn hph-btn--primary">
                    <i class="fas fa-search"></i>
                    Search Properties
                </button>
            </div>
            
        </form>
    </div>
</div>
