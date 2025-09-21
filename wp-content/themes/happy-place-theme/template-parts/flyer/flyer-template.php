<?php
/**
 * Flyer Template Part
 * 
 * Template for generating PDF flyers
 * Uses the same structure as our test template
 * 
 * @package HappyPlaceTheme
 */

$listing_id = $args['listing_id'] ?? get_the_ID();

// Get listing data using bridge functions
$listing_data = [];

// Use bridge functions if available, fallback to ACF with correct field names
$listing_data['price'] = function_exists('hpt_get_listing_price') ? hpt_get_listing_price($listing_id) : get_field('listing_price', $listing_id);
$listing_data['beds'] = function_exists('hpt_get_listing_bedrooms') ? hpt_get_listing_bedrooms($listing_id) : get_field('bedrooms', $listing_id);

// Calculate total bathrooms (full + half)
if (function_exists('hpt_get_listing_bathrooms')) {
    $listing_data['baths'] = hpt_get_listing_bathrooms($listing_id);
} else {
    $full_baths = get_field('bathrooms_full', $listing_id) ?: 0;
    $half_baths = get_field('bathrooms_half', $listing_id) ?: 0;
    $listing_data['baths'] = $full_baths + ($half_baths * 0.5);
}

$listing_data['sqft'] = function_exists('hpt_get_listing_square_feet') ? hpt_get_listing_square_feet($listing_id) : get_field('square_feet', $listing_id);

// Get lot size - prefer acres, convert from sqft if needed
if (function_exists('hpt_get_listing_lot_size')) {
    $listing_data['lot_size'] = hpt_get_listing_lot_size($listing_id);
} else {
    $lot_acres = get_field('lot_size_acres', $listing_id);
    $lot_sqft = get_field('lot_size_sqft', $listing_id);
    if ($lot_acres) {
        $listing_data['lot_size'] = $lot_acres . ' acres';
    } elseif ($lot_sqft) {
        $listing_data['lot_size'] = number_format($lot_sqft / 43560, 2) . ' acres';
    } else {
        $listing_data['lot_size'] = '';
    }
}

$listing_data['description'] = function_exists('hpt_get_listing_description') ? hpt_get_listing_description($listing_id) : get_field('property_description', $listing_id);
$listing_data['address'] = function_exists('hpt_get_listing_address') ? hpt_get_listing_address($listing_id) : [];

// Get agent information - bridge returns user ID, we need user details
$agent_id = function_exists('hpt_get_listing_agent') ? hpt_get_listing_agent($listing_id) : get_field('listing_agent', $listing_id);

if ($agent_id) {
    // If it's an array (ACF), get the first element
    if (is_array($agent_id) && !empty($agent_id)) {
        $agent_post = $agent_id[0]; // First agent from ACF relationship field
        $listing_data['agent'] = [
            'name' => $agent_post->post_title,
            'email' => get_field('email', $agent_post->ID) ?: get_field('agent_email', $agent_post->ID),
            'phone' => get_field('phone', $agent_post->ID) ?: get_field('agent_phone', $agent_post->ID)
        ];
    } else {
        // If it's a user ID, get user data
        $agent_user = get_userdata($agent_id);
        if ($agent_user) {
            $listing_data['agent'] = [
                'name' => $agent_user->display_name,
                'email' => $agent_user->user_email,
                'phone' => get_user_meta($agent_id, 'phone', true) ?: get_user_meta($agent_id, 'agent_phone', true)
            ];
        } else {
            $listing_data['agent'] = [];
        }
    }
} else {
    $listing_data['agent'] = [];
}

// Get media using correct field names
$listing_data['featured_image'] = function_exists('hpt_get_listing_featured_image') ? hpt_get_listing_featured_image($listing_id) : get_field('primary_photo', $listing_id);
$listing_data['gallery'] = function_exists('hpt_get_listing_gallery') ? hpt_get_listing_gallery($listing_id) : get_field('photo_gallery', $listing_id);

// Format address from bridge data or individual fields
$address = '';
$location = '';
if (is_array($listing_data['address']) && !empty($listing_data['address'])) {
    $address = $listing_data['address']['street'] ?? '';
    $location = ($listing_data['address']['city'] ?? '') .
                ($listing_data['address']['state'] ? ', ' . $listing_data['address']['state'] : '') .
                ($listing_data['address']['zip'] ? ' ' . $listing_data['address']['zip'] : '');
} else {
    // Build address from individual ACF fields
    $street_number = get_field('street_number', $listing_id);
    $street_dir_prefix = get_field('street_dir_prefix', $listing_id);
    $street_name = get_field('street_name', $listing_id);
    $street_type = get_field('street_type', $listing_id);
    $street_dir_suffix = get_field('street_dir_suffix', $listing_id);
    $unit_number = get_field('unit_number', $listing_id);

    // Build full street address
    if ($street_number) $address .= $street_number . ' ';
    if ($street_dir_prefix) $address .= $street_dir_prefix . ' ';
    if ($street_name) $address .= $street_name . ' ';
    if ($street_type) $address .= $street_type . ' ';
    if ($street_dir_suffix) $address .= $street_dir_suffix . ' ';
    if ($unit_number) $address .= $unit_number;
    $address = trim($address);

    // Build city, state, zip
    $city = get_field('city', $listing_id);
    $state = get_field('state', $listing_id);
    $zip = get_field('zip_code', $listing_id);
    if ($city) $location .= $city;
    if ($state) $location .= ($location ? ', ' : '') . $state;
    if ($zip) $location .= ' ' . $zip;
}

// Format price
$formatted_price = '';
if ($listing_data['price']) {
    if (function_exists('hpt_get_listing_price_formatted')) {
        $formatted_price = hpt_get_listing_price_formatted($listing_id);
    } else {
        $formatted_price = '$' . number_format($listing_data['price']);
    }
}

// Format square feet
$formatted_sqft = '';
if ($listing_data['sqft'] && $listing_data['sqft'] > 0) {
    $formatted_sqft = number_format($listing_data['sqft']);
} else {
    $formatted_sqft = 'TBD'; // Show 'TBD' when square feet is 0 or empty
}

// Format lot size for display (extract numeric value from string)
$formatted_lot_size = '';
if ($listing_data['lot_size']) {
    if (is_array($listing_data['lot_size'])) {
        // Bridge function returns array
        $formatted_lot_size = $listing_data['lot_size']['acres'] ?? ($listing_data['lot_size']['value'] ?? '');
    } else {
        // String format - extract just the number part
        $lot_size_str = $listing_data['lot_size'];
        if (preg_match('/([0-9]+\.?[0-9]*)\s*(acre|ac)/i', $lot_size_str, $matches)) {
            $formatted_lot_size = $matches[1];
        } elseif (preg_match('/([0-9]+\.?[0-9]*)/', $lot_size_str, $matches)) {
            $formatted_lot_size = $matches[1];
        }
    }
}

// If still empty, try direct ACF field access
if (!$formatted_lot_size) {
    $lot_acres = get_field('lot_size_acres', $listing_id);
    $lot_sqft = get_field('lot_size_sqft', $listing_id);
    if ($lot_acres) {
        $formatted_lot_size = $lot_acres;
    } elseif ($lot_sqft) {
        $formatted_lot_size = number_format($lot_sqft / 43560, 2);
    }
}

// Extract agent data (already processed above)
$agent_name = '';
$agent_phone = '';
$agent_email = '';

// Get agent info from the agent array we built
if (is_array($listing_data['agent']) && !empty($listing_data['agent'])) {
    $agent_name = $listing_data['agent']['name'] ?? '';
    $agent_phone = $listing_data['agent']['phone'] ?? '';
    $agent_email = $listing_data['agent']['email'] ?? '';
} else {
    // Fallback: Get agent info directly from ACF relationship field
    $agent_relationship = get_field('listing_agent', $listing_id);
    if ($agent_relationship && is_array($agent_relationship) && !empty($agent_relationship)) {
        $agent_post = $agent_relationship[0]; // First agent
        $agent_name = $agent_post->post_title;

        // Try different field names for phone and email
        $agent_phone = get_field('phone', $agent_post->ID) ?:
                       get_field('agent_phone', $agent_post->ID) ?:
                       get_field('contact_phone', $agent_post->ID) ?:
                       '(302) 217-6692'; // Default company phone

        $agent_email = get_field('email', $agent_post->ID) ?:
                       get_field('agent_email', $agent_post->ID) ?:
                       get_field('contact_email', $agent_post->ID) ?:
                       'cheers@theparkergroup.com'; // Default company email
    }
}

// Final fallback to ensure we have something to show
if (!$agent_name) $agent_name = 'The Parker Group';
if (!$agent_phone) $agent_phone = '(302) 217-6692';
if (!$agent_email) $agent_email = 'cheers@theparkergroup.com';
?>

<style>
    /* Reset any potential inherited styles */
    * {
        box-sizing: border-box;
    }

    /* Flyer Template Styles */
    .flyer-container {
        width: 816px;
        height: 1056px;
        margin: 0;
        padding: 0;
        background: white;
        position: relative;
        font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-sizing: border-box;
    }

    .flyer-header {
        background: linear-gradient(135deg, #50bae1 0%, #62c1e4 100%);
        height: 85px;
        display: flex;
        align-items: center;
        padding-left: 24px;
        flex-shrink: 0;
        margin: 0;
        position: relative;
        top: 0;
        left: 0;
        width: 100%;
    }

    .flyer-header h1 {
        color: white;
        font-size: 42px;
        font-weight: 700;
        margin: 0;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    /* Photo Grid Section */
    .photo-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        grid-template-rows: 342px 171px;
        gap: 4px;
        height: 520px;
        padding: 8px;
        background: white;
        flex-shrink: 0;
    }

    .main-photo {
        grid-column: 1 / 4;
        grid-row: 1;
        background: #e8e8e8;
        border-radius: 6px;
        overflow: hidden;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #666;
        font-size: 18px;
        background-size: cover;
        background-position: center;
    }

    .small-photo-1, .small-photo-2, .small-photo-3 {
        background: #e8e8e8;
        border-radius: 4px;
        overflow: hidden;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #666;
        font-size: 14px;
        background-size: cover;
        background-position: center;
    }

    .small-photo-1 {
        grid-column: 1;
        grid-row: 2;
    }

    .small-photo-2 {
        grid-column: 2;
        grid-row: 2;
    }

    .small-photo-3 {
        grid-column: 3;
        grid-row: 2;
    }

    /* Stats Bar */
    .stats-bar {
        background: #2c3e50;
        color: white;
        padding: 18px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
    }

    .price {
        font-size: 36px;
        font-weight: 700;
        color: white;
        letter-spacing: -0.5px;
        font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .stats-group {
        display: flex;
        gap: 32px;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 16px;
        font-weight: 600;
        font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .stat-icon {
        font-size: 20px;
        width: 24px;
        text-align: center;
    }

    .stat-value {
        font-weight: 700;
        font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .stat-label {
        font-size: 14px;
        opacity: 0.9;
        text-transform: capitalize;
        font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    /* Content Section */
    .content-section {
        padding: 12px 8px 16px 24px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        flex-grow: 1;
        min-height: 0;
    }

    .property-agent-column {
        display: flex;
        flex-direction: column;
    }

    .property-address {
        font-size: 28px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 4px;
        font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .property-location {
        font-size: 16px;
        color: #666;
        margin-bottom: 20px;
        font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .property-description {
        font-size: 14px;
        line-height: 1.5;
        color: #444;
        margin-bottom: 24px;
        flex-grow: 1;
        font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        max-height: 120px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 6;
        -webkit-box-orient: vertical;
    }

    .cta-text {
        font-size: 16px;
        font-weight: 600;
        color: #50bae1;
        margin-bottom: 20px;
        font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .agent-info {
        margin-bottom: 12px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .agent-photo {
        width: 64px;
        height: 64px;
        background: #ddd;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        color: #666;
        flex-shrink: 0;
    }

    .agent-details {
        flex: 1;
    }

    .agent-name {
        color: #50bae1;
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 1px;
        letter-spacing: -0.2px;
        font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .agent-title {
        font-size: 12px;
        color: #666;
        margin-bottom: 8px;
        font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .agent-contact-info {
        font-size: 12px;
        color: #444;
        line-height: 1.4;
        font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .company-column {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        text-align: center;
        padding: 20px;
        padding-top: 40px;
    }

    .company-logo {
        width: 180px;
        height: 60px;
        background: #ddd;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        color: #666;
    }

    .company-info {
        font-size: 12px;
        color: #666;
        line-height: 1.4;
        font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
</style>

<div class="flyer-container">
    <!-- Header -->
    <div class="flyer-header">
        <h1>For Sale</h1>
    </div>

    <!-- Photo Grid -->
    <div class="photo-grid">
        <div class="main-photo" data-field="primary_photo">
            <?php if ($listing_data['featured_image'] && is_array($listing_data['featured_image']) && isset($listing_data['featured_image']['url'])): ?>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const mainPhoto = document.querySelector('.main-photo');
                    if (mainPhoto) {
                        mainPhoto.style.backgroundImage = 'url(<?php echo esc_url($listing_data['featured_image']['url']); ?>)';
                        mainPhoto.innerHTML = '';
                    }
                });
                </script>
            <?php else: ?>
                Main Photo
            <?php endif; ?>
        </div>
        
        <?php
        $gallery = $listing_data['gallery'];
        $small_photo_classes = ['small-photo-1', 'small-photo-2', 'small-photo-3'];
        for ($i = 0; $i < 3; $i++):
            $photo = isset($gallery[$i]) ? $gallery[$i] : null;
            $class = $small_photo_classes[$i];

            // Handle different gallery data structures
            $photo_url = '';
            if ($photo && is_array($photo)) {
                $photo_url = $photo['url'] ?? $photo['URL'] ?? '';
            }
        ?>
        <div class="<?php echo $class; ?>">
            <?php if ($photo_url): ?>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const photo = document.querySelector('.<?php echo $class; ?>');
                    if (photo) {
                        photo.style.backgroundImage = 'url(<?php echo esc_url($photo_url); ?>)';
                        photo.innerHTML = '';
                    }
                });
                </script>
            <?php else: ?>
                Photo <?php echo $i + 1; ?>
            <?php endif; ?>
        </div>
        <?php endfor; ?>
    </div>

    <!-- Stats Bar -->
    <div class="stats-bar">
        <div class="price" data-field="price"><?php echo $formatted_price ?: '$0'; ?></div>
        <div class="stats-group">
            <div class="stat-item">
                <i class="fas fa-bed stat-icon"></i>
                <span class="stat-value" data-field="beds"><?php echo $listing_data['beds'] ?: '0'; ?></span>
                <span class="stat-label">Beds</span>
            </div>
            <div class="stat-item">
                <i class="fas fa-bath stat-icon"></i>
                <span class="stat-value" data-field="baths"><?php echo $listing_data['baths'] ?: '0'; ?></span>
                <span class="stat-label">Baths</span>
            </div>
            <div class="stat-item">
                <i class="fas fa-ruler-combined stat-icon"></i>
                <span class="stat-value" data-field="sqft"><?php echo $formatted_sqft ?: '0'; ?></span>
                <span class="stat-label">Sq Ft</span>
            </div>
            <div class="stat-item">
                <i class="fas fa-expand-arrows-alt stat-icon"></i>
                <span class="stat-value" data-field="lot_size"><?php echo $formatted_lot_size ?: '0'; ?></span>
                <span class="stat-label">Acres</span>
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="content-section">
        <!-- Left Column - Property & Agent Info -->
        <div class="property-agent-column">
            <div class="property-address" data-field="address"><?php echo $address ?: 'Property Address'; ?></div>
            <div class="property-location" data-field="city_state_zip"><?php echo $location ?: 'City, State ZIP'; ?></div>
            
            <div class="property-description" data-field="description">
                <?php echo wp_trim_words($listing_data['description'] ?: 'Property description will appear here.', 25, '...'); ?>
            </div>
            
            <div class="cta-text">Call today to schedule your private showing!</div>
            
            <div class="agent-info">
                <div class="agent-photo">Photo</div>
                <div class="agent-details">
                    <div class="agent-name" data-field="agent_name"><?php echo $agent_name ?: 'Agent Name'; ?></div>
                    <div class="agent-title">REALTOR®</div>
                    <div class="agent-contact-info">
                        <div data-field="agent_phone"><?php echo $agent_phone ?: 'Phone Number'; ?></div>
                        <div data-field="agent_email"><?php echo $agent_email ?: 'cheers@theparkergroup.com'; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Company Branding -->
        <div class="company-column">
            <div class="company-logo">
                <strong style="color: #50bae1; font-size: 18px;">The Parker Group</strong>
            </div>
            <div class="company-info">
                <strong>The Parker Group</strong><br>
                Professional Real Estate Services<br>
                Delaware Beach Properties<br>
                (302) 217-6692<br>
                www.theparkergroup.com
            </div>
        </div>
    </div>
</div>