<?php
/**
 * Template Name: Listing Form
 * Page for adding/editing property listings
 */

// Security check
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

$current_user = wp_get_current_user();
$is_agent = current_user_can('manage_listings') || current_user_can('manage_options');

if (!$is_agent) {
    wp_die('You do not have permission to manage listings.');
}

// Get listing ID if editing
$listing_id = isset($_GET['listing_id']) ? intval($_GET['listing_id']) : 0;
$is_edit = $listing_id > 0;

// Load listing data if editing
$listing_data = null;
if ($is_edit) {
    $listing = get_post($listing_id);
    if (!$listing || $listing->post_type !== 'listing') {
        wp_die('Listing not found.');
    }
    
    // Check permissions using our custom permission system
    if (!hph_can_user_edit_listing($listing_id)) {
        wp_die('You do not have permission to edit this listing. Only administrators and the assigned listing agent can edit listings.');
    }
    
    $listing_data = [
        'title' => $listing->post_title,
        'description' => $listing->post_content,
        'price' => get_field('price', $listing_id),
        'property_type' => get_field('property_type', $listing_id),
        'street_address' => get_field('street_address', $listing_id),
        'city' => get_field('city', $listing_id),
        'state' => get_field('state', $listing_id),
        'zip_code' => get_field('zip_code', $listing_id),
        'bedrooms' => get_field('bedrooms', $listing_id),
        'bathrooms_full' => get_field('bathrooms_full', $listing_id),
        'bathrooms_half' => get_field('bathrooms_half', $listing_id),
        'square_feet' => get_field('square_feet', $listing_id),
        'lot_size_acres' => get_field('lot_size_acres', $listing_id),
        'year_built' => get_field('year_built', $listing_id),
        'garage' => get_field('garage', $listing_id),
        'mls_number' => get_field('mls_number', $listing_id),
        'listing_status' => get_field('listing_status', $listing_id),
        'features' => get_field('features', $listing_id),
    ];
}

get_header();
?>

<div class="listing-form-page">
    <div class="container">
        <div class="listing-form-header">
            <div class="form-breadcrumb">
                <a href="<?php echo home_url('/dashboard/?section=listings'); ?>">← Back to Dashboard</a>
            </div>
            <h1><?php echo $is_edit ? 'Edit Listing' : 'Add New Listing'; ?></h1>
            <?php if ($is_edit): ?>
                <p>Editing: <?php echo esc_html($listing_data['title']); ?></p>
            <?php endif; ?>
        </div>

        <form id="listingForm" class="listing-form" method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('hph_save_listing', 'listing_nonce'); ?>
            <input type="hidden" name="listing_id" value="<?php echo $listing_id; ?>">
            <input type="hidden" name="action" value="hph_save_listing">

            <!-- Basic Information -->
            <div class="form-section">
                <h2>Basic Information</h2>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="property_title">Marketing Title</label>
                        <input type="text" id="property_title" name="property_title" 
                               value="<?php echo esc_attr(get_field('property_title', $listing_id) ?? ''); ?>" 
                               maxlength="100" placeholder="Stunning Waterfront Estate in Georgetown">
                        <small>Optional: Override default title (uses address if blank)</small>
                    </div>

                    <div class="form-group">
                        <label for="listing_price">Listing Price <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-prefix">$</span>
                            <input type="number" id="listing_price" name="listing_price" 
                                   value="<?php echo esc_attr(get_field('price', $listing_id) ?? ''); ?>" 
                                   required min="0" placeholder="450000">
                        </div>
                        <small>Enter numbers only (no commas or dollar signs)</small>
                    </div>

                    <div class="form-group">
                        <label for="mls_number">MLS Number</label>
                        <input type="text" id="mls_number" name="mls_number" 
                               value="<?php echo esc_attr(get_field('mls_number', $listing_id) ?? ''); ?>" 
                               maxlength="20" placeholder="MLS123456">
                        <small>Multiple Listing Service ID</small>
                    </div>

                    <div class="form-group">
                        <label for="listing_status">Listing Status</label>
                        <select id="listing_status" name="listing_status">
                            <option value="active" <?php selected(get_field('listing_status', $listing_id) ?: 'active', 'active'); ?>>Active</option>
                            <option value="pending" <?php selected(get_field('listing_status', $listing_id) ?: 'active', 'pending'); ?>>Pending</option>
                            <option value="sold" <?php selected(get_field('listing_status', $listing_id) ?: 'active', 'sold'); ?>>Sold</option>
                            <option value="withdrawn" <?php selected(get_field('listing_status', $listing_id) ?: 'active', 'withdrawn'); ?>>Withdrawn</option>
                        </select>
                        <small>Current market status of this listing</small>
                    </div>

                    <div class="form-group">
                        <label for="is_featured">Featured Listing</label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="is_featured" name="is_featured" value="1" 
                                   <?php checked(get_field('is_featured', $listing_id) ?? 0, 1); ?>>
                            <span class="toggle-slider"></span>
                            Feature this listing
                        </label>
                    </div>

                    <div class="form-group">
                        <label for="listing_date">Listing Date</label>
                        <input type="date" id="listing_date" name="listing_date" 
                               value="<?php echo esc_attr(get_field('listing_date', $listing_id) ?? ''); ?>">
                        <small>Date property was listed</small>
                    </div>

                    <div class="form-group">
                        <label for="days_on_market">Days on Market</label>
                        <input type="number" id="days_on_market" name="days_on_market" 
                               value="<?php echo esc_attr(get_field('days_on_market', $listing_id) ?? ''); ?>" 
                               min="0" step="1">
                        <small>Number of days since listing</small>
                    </div>

                    <div class="form-group full-width">
                        <label for="property_description">Property Description <span class="required">*</span></label>
                        <textarea id="property_description" name="property_description" 
                                  rows="6" required placeholder="Main marketing description..."><?php echo esc_textarea(get_field('property_description', $listing_id) ?? ''); ?></textarea>
                        <small>Main marketing description</small>
                    </div>

                    <div class="form-group full-width">
                        <label for="showing_instructions">Showing Instructions</label>
                        <textarea id="showing_instructions" name="showing_instructions" 
                                  rows="3" placeholder="Call listing agent 2 hours before showing. Lockbox on back door."><?php echo esc_textarea(get_field('showing_instructions', $listing_id) ?? ''); ?></textarea>
                        <small>Special instructions for showing agents</small>
                    </div>

                    <div class="form-group full-width">
                        <label for="internal_notes">Internal Notes</label>
                        <textarea id="internal_notes" name="internal_notes" 
                                  rows="3" placeholder="Agent notes, special circumstances, etc."><?php echo esc_textarea(get_field('internal_notes', $listing_id) ?? ''); ?></textarea>
                        <small>Private notes (not displayed publicly)</small>
                    </div>
                </div>
            </div>

            <!-- Location -->
            <div class="form-section">
                <h2>Property Address</h2>
                <div class="form-grid">
                    <div class="form-group" style="grid-column: span 1;">
                        <label for="street_number">Street Number</label>
                        <input type="text" id="street_number" name="street_number" 
                               value="<?php echo esc_attr(get_field('street_number', $listing_id) ?? ''); ?>" 
                               maxlength="10" placeholder="123">
                    </div>

                    <div class="form-group" style="grid-column: span 1;">
                        <label for="street_dir_prefix">Prefix</label>
                        <select id="street_dir_prefix" name="street_dir_prefix">
                            <option value="">None</option>
                            <option value="N" <?php selected(get_field('street_dir_prefix', $listing_id) ?? '', 'N'); ?>>N</option>
                            <option value="S" <?php selected(get_field('street_dir_prefix', $listing_id) ?? '', 'S'); ?>>S</option>
                            <option value="E" <?php selected(get_field('street_dir_prefix', $listing_id) ?? '', 'E'); ?>>E</option>
                            <option value="W" <?php selected(get_field('street_dir_prefix', $listing_id) ?? '', 'W'); ?>>W</option>
                            <option value="NE" <?php selected(get_field('street_dir_prefix', $listing_id) ?? '', 'NE'); ?>>NE</option>
                            <option value="NW" <?php selected(get_field('street_dir_prefix', $listing_id) ?? '', 'NW'); ?>>NW</option>
                            <option value="SE" <?php selected(get_field('street_dir_prefix', $listing_id) ?? '', 'SE'); ?>>SE</option>
                            <option value="SW" <?php selected(get_field('street_dir_prefix', $listing_id) ?? '', 'SW'); ?>>SW</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="street_name">Street Name <span class="required">*</span></label>
                        <input type="text" id="street_name" name="street_name" 
                               value="<?php echo esc_attr(get_field('street_name', $listing_id) ?? ''); ?>" 
                               required placeholder="Main">
                    </div>

                    <div class="form-group">
                        <label for="street_type">Street Type</label>
                        <select id="street_type" name="street_type">
                            <option value="St" <?php selected(get_field('street_type', $listing_id) ?? 'St', 'St'); ?>>Street</option>
                            <option value="Ave" <?php selected(get_field('street_type', $listing_id) ?? '', 'Ave'); ?>>Avenue</option>
                            <option value="Blvd" <?php selected(get_field('street_type', $listing_id) ?? '', 'Blvd'); ?>>Boulevard</option>
                            <option value="Dr" <?php selected(get_field('street_type', $listing_id) ?? '', 'Dr'); ?>>Drive</option>
                            <option value="Rd" <?php selected(get_field('street_type', $listing_id) ?? '', 'Rd'); ?>>Road</option>
                            <option value="Ln" <?php selected(get_field('street_type', $listing_id) ?? '', 'Ln'); ?>>Lane</option>
                            <option value="Way" <?php selected(get_field('street_type', $listing_id) ?? '', 'Way'); ?>>Way</option>
                            <option value="Ct" <?php selected(get_field('street_type', $listing_id) ?? '', 'Ct'); ?>>Court</option>
                            <option value="Pl" <?php selected(get_field('street_type', $listing_id) ?? '', 'Pl'); ?>>Place</option>
                            <option value="Cir" <?php selected(get_field('street_type', $listing_id) ?? '', 'Cir'); ?>>Circle</option>
                            <option value="Pkwy" <?php selected(get_field('street_type', $listing_id) ?? '', 'Pkwy'); ?>>Parkway</option>
                            <option value="Trl" <?php selected(get_field('street_type', $listing_id) ?? '', 'Trl'); ?>>Trail</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="street_dir_suffix">Suffix</label>
                        <select id="street_dir_suffix" name="street_dir_suffix">
                            <option value="">None</option>
                            <option value="N" <?php selected(get_field('street_dir_suffix', $listing_id) ?? '', 'N'); ?>>N</option>
                            <option value="S" <?php selected(get_field('street_dir_suffix', $listing_id) ?? '', 'S'); ?>>S</option>
                            <option value="E" <?php selected(get_field('street_dir_suffix', $listing_id) ?? '', 'E'); ?>>E</option>
                            <option value="W" <?php selected(get_field('street_dir_suffix', $listing_id) ?? '', 'W'); ?>>W</option>
                            <option value="NE" <?php selected(get_field('street_dir_suffix', $listing_id) ?? '', 'NE'); ?>>NE</option>
                            <option value="NW" <?php selected(get_field('street_dir_suffix', $listing_id) ?? '', 'NW'); ?>>NW</option>
                            <option value="SE" <?php selected(get_field('street_dir_suffix', $listing_id) ?? '', 'SE'); ?>>SE</option>
                            <option value="SW" <?php selected(get_field('street_dir_suffix', $listing_id) ?? '', 'SW'); ?>>SW</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="unit_number">Unit/Apt</label>
                        <input type="text" id="unit_number" name="unit_number" 
                               value="<?php echo esc_attr(get_field('unit_number', $listing_id) ?? ''); ?>" 
                               placeholder="#2A">
                    </div>

                    <div class="form-group">
                        <label for="city">City <span class="required">*</span></label>
                        <input type="text" id="city" name="city" 
                               value="<?php echo esc_attr(get_field('city', $listing_id) ?? ''); ?>" 
                               required placeholder="Georgetown">
                    </div>

                    <div class="form-group">
                        <label for="state">State</label>
                        <select id="state" name="state">
                            <option value="DE" <?php selected(get_field('state', $listing_id) ?? 'DE', 'DE'); ?>>Delaware</option>
                            <option value="MD" <?php selected(get_field('state', $listing_id) ?? '', 'MD'); ?>>Maryland</option>
                            <option value="PA" <?php selected(get_field('state', $listing_id) ?? '', 'PA'); ?>>Pennsylvania</option>
                            <option value="NJ" <?php selected(get_field('state', $listing_id) ?? '', 'NJ'); ?>>New Jersey</option>
                            <option value="VA" <?php selected(get_field('state', $listing_id) ?? '', 'VA'); ?>>Virginia</option>
                            <option value="DC" <?php selected(get_field('state', $listing_id) ?? '', 'DC'); ?>>Washington DC</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="zip_code">ZIP Code <span class="required">*</span></label>
                        <input type="text" id="zip_code" name="zip_code" 
                               value="<?php echo esc_attr(get_field('zip_code', $listing_id) ?? ''); ?>" 
                               required maxlength="10" placeholder="19947">
                    </div>

                    <div class="form-group">
                        <label for="county">County</label>
                        <input type="text" id="county" name="county" 
                               value="<?php echo esc_attr(get_field('county', $listing_id) ?? ''); ?>" 
                               placeholder="Sussex County">
                    </div>

                    <div class="form-group">
                        <label for="school_district">School District</label>
                        <input type="text" id="school_district" name="school_district" 
                               value="<?php echo esc_attr(get_field('school_district', $listing_id) ?? ''); ?>" 
                               maxlength="100" placeholder="Example School District">
                        <small>Local school district name</small>
                    </div>

                    <div class="form-group">
                        <label for="zoning">Zoning</label>
                        <input type="text" id="zoning" name="zoning" 
                               value="<?php echo esc_attr(get_field('zoning', $listing_id) ?? ''); ?>" 
                               maxlength="20" placeholder="R-1, C-1, etc.">
                        <small>Property zoning classification</small>
                    </div>

                    <div class="form-group">
                        <label for="subdivision">Subdivision/Neighborhood</label>
                        <input type="text" id="subdivision" name="subdivision" 
                               value="<?php echo esc_attr(get_field('subdivision', $listing_id) ?? ''); ?>" 
                               maxlength="100" placeholder="Neighborhood name">
                        <small>Neighborhood or subdivision name</small>
                    </div>

                    <div class="form-group">
                        <label for="flood_zone">Flood Zone</label>
                        <select id="flood_zone" name="flood_zone">
                            <option value="no" <?php selected(get_field('flood_zone', $listing_id) ?? 'no', 'no'); ?>>No Flood Zone</option>
                            <option value="ae" <?php selected(get_field('flood_zone', $listing_id) ?? '', 'ae'); ?>>AE Zone</option>
                            <option value="a" <?php selected(get_field('flood_zone', $listing_id) ?? '', 'a'); ?>>A Zone</option>
                            <option value="x" <?php selected(get_field('flood_zone', $listing_id) ?? '', 'x'); ?>>X Zone</option>
                            <option value="ve" <?php selected(get_field('flood_zone', $listing_id) ?? '', 've'); ?>>VE Zone</option>
                            <option value="other" <?php selected(get_field('flood_zone', $listing_id) ?? '', 'other'); ?>>Other</option>
                        </select>
                        <small>FEMA flood zone designation</small>
                    </div>

                    <div class="form-group">
                        <label for="address_display">Address Display</label>
                        <select id="address_display" name="address_display">
                            <option value="full" <?php selected(get_field('address_display', $listing_id) ?? 'full', 'full'); ?>>Show Full Address</option>
                            <option value="street" <?php selected(get_field('address_display', $listing_id) ?? '', 'street'); ?>>Street Only (No Number)</option>
                            <option value="area" <?php selected(get_field('address_display', $listing_id) ?? '', 'area'); ?>>Area Only (City/State)</option>
                            <option value="hidden" <?php selected(get_field('address_display', $listing_id) ?? '', 'hidden'); ?>>Do Not Display</option>
                        </select>
                        <small>How to display address publicly</small>
                    </div>
                </div>
            </div>

            <!-- Property Details -->
            <div class="form-section">
                <h2>Core Property Information</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="bedrooms">Bedrooms</label>
                        <select id="bedrooms" name="bedrooms">
                            <option value="">Select</option>
                            <?php for ($i = 0; $i <= 20; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php selected(get_field('bedrooms', $listing_id) ?? '3', $i); ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="bathrooms_full">Full Bathrooms</label>
                        <select id="bathrooms_full" name="bathrooms_full">
                            <option value="">Select</option>
                            <?php for ($i = 0; $i <= 10; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php selected(get_field('bathrooms_full', $listing_id) ?? '2', $i); ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                        <small>Number of full bathrooms</small>
                    </div>

                    <div class="form-group">
                        <label for="bathrooms_half">Half Bathrooms</label>
                        <select id="bathrooms_half" name="bathrooms_half">
                            <option value="">Select</option>
                            <?php for ($i = 0; $i <= 10; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php selected(get_field('bathrooms_half', $listing_id) ?? '0', $i); ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                        <small>Number of half bathrooms</small>
                    </div>

                    <div class="form-group">
                        <label for="square_feet">Square Feet</label>
                        <input type="number" id="square_feet" name="square_feet" 
                               value="<?php echo esc_attr(get_field('square_feet', $listing_id) ?? ''); ?>" 
                               min="0" step="1" placeholder="2500">
                        <small>Total living area</small>
                    </div>

                    <div class="form-group">
                        <label for="lot_size_acres">Lot Size (acres)</label>
                        <input type="number" id="lot_size_acres" name="lot_size_acres" 
                               value="<?php echo esc_attr(get_field('lot_size_acres', $listing_id) ?? ''); ?>" 
                               min="0" step="0.01" placeholder="0.25">
                        <small>Lot size in acres (leave blank for condos)</small>
                    </div>

                    <div class="form-group">
                        <label for="lot_size_sqft">Lot Size (Sq Ft)</label>
                        <input type="number" id="lot_size_sqft" name="lot_size_sqft" 
                               value="<?php echo esc_attr(get_field('lot_size_sqft', $listing_id) ?? ''); ?>" 
                               min="0" step="100" placeholder="10890">
                        <small>Alternative: Enter in square feet</small>
                    </div>

                    <div class="form-group">
                        <label for="year_built">Year Built</label>
                        <input type="number" id="year_built" name="year_built" 
                               value="<?php echo esc_attr(get_field('year_built', $listing_id) ?? ''); ?>" 
                               min="1800" max="2030" step="1" placeholder="2020">
                    </div>

                    <div class="form-group">
                        <label for="garage_spaces">Garage Spaces</label>
                        <select id="garage_spaces" name="garage_spaces">
                            <option value="">Select</option>
                            <?php for ($i = 0; $i <= 10; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php selected(get_field('garage_spaces', $listing_id) ?? '2', $i); ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="property_style">Architectural Style</label>
                        <select id="property_style" name="property_style">
                            <option value="">Select style...</option>
                            <option value="colonial" <?php selected(get_field('property_style', $listing_id) ?? '', 'colonial'); ?>>Colonial</option>
                            <option value="contemporary" <?php selected(get_field('property_style', $listing_id) ?? '', 'contemporary'); ?>>Contemporary</option>
                            <option value="traditional" <?php selected(get_field('property_style', $listing_id) ?? '', 'traditional'); ?>>Traditional</option>
                            <option value="ranch" <?php selected(get_field('property_style', $listing_id) ?? '', 'ranch'); ?>>Ranch</option>
                            <option value="cape_cod" <?php selected(get_field('property_style', $listing_id) ?? '', 'cape_cod'); ?>>Cape Cod</option>
                            <option value="victorian" <?php selected(get_field('property_style', $listing_id) ?? '', 'victorian'); ?>>Victorian</option>
                            <option value="craftsman" <?php selected(get_field('property_style', $listing_id) ?? '', 'craftsman'); ?>>Craftsman</option>
                            <option value="modern" <?php selected(get_field('property_style', $listing_id) ?? '', 'modern'); ?>>Modern</option>
                            <option value="tudor" <?php selected(get_field('property_style', $listing_id) ?? '', 'tudor'); ?>>Tudor</option>
                            <option value="mediterranean" <?php selected(get_field('property_style', $listing_id) ?? '', 'mediterranean'); ?>>Mediterranean</option>
                            <option value="farmhouse" <?php selected(get_field('property_style', $listing_id) ?? '', 'farmhouse'); ?>>Farmhouse</option>
                            <option value="two_story" <?php selected(get_field('property_style', $listing_id) ?? '', 'two_story'); ?>>Two Story</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="stories">Stories/Levels</label>
                        <select id="stories" name="stories">
                            <option value="">Select</option>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php selected(get_field('stories', $listing_id) ?? '1', $i); ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                        <small>Number of floors/levels</small>
                    </div>

                    <div class="form-group">
                        <label for="condition">Property Condition</label>
                        <select id="condition" name="condition">
                            <option value="">Select condition...</option>
                            <option value="excellent" <?php selected(get_field('condition', $listing_id) ?? '', 'excellent'); ?>>Excellent</option>
                            <option value="very_good" <?php selected(get_field('condition', $listing_id) ?? '', 'very_good'); ?>>Very Good</option>
                            <option value="good" <?php selected(get_field('condition', $listing_id) ?? '', 'good'); ?>>Good</option>
                            <option value="fair" <?php selected(get_field('condition', $listing_id) ?? '', 'fair'); ?>>Fair</option>
                            <option value="needs_work" <?php selected(get_field('condition', $listing_id) ?? '', 'needs_work'); ?>>Needs Work</option>
                            <option value="fixer_upper" <?php selected(get_field('condition', $listing_id) ?? '', 'fixer_upper'); ?>>Fixer Upper</option>
                        </select>
                        <small>Overall condition of the property</small>
                    </div>
                </div>
            </div>

            <!-- Features & Amenities -->
            <div class="form-section">
                <h2>Features & Amenities</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="has_pool">Swimming Pool</label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="has_pool" name="has_pool" value="1" 
                                   <?php checked(get_field('has_pool', $listing_id) ?? 0, 1); ?>>
                            <span class="toggle-slider"></span>
                            Has Swimming Pool
                        </label>
                    </div>

                    <div class="form-group">
                        <label for="pool_type">Pool Type</label>
                        <select id="pool_type" name="pool_type">
                            <option value="">Select type...</option>
                            <option value="inground" <?php selected(get_field('pool_type', $listing_id) ?? 'inground', 'inground'); ?>>In-ground</option>
                            <option value="above_ground" <?php selected(get_field('pool_type', $listing_id) ?? '', 'above_ground'); ?>>Above Ground</option>
                            <option value="infinity" <?php selected(get_field('pool_type', $listing_id) ?? '', 'infinity'); ?>>Infinity Pool</option>
                            <option value="lap" <?php selected(get_field('pool_type', $listing_id) ?? '', 'lap'); ?>>Lap Pool</option>
                            <option value="saltwater" <?php selected(get_field('pool_type', $listing_id) ?? '', 'saltwater'); ?>>Saltwater Pool</option>
                            <option value="heated" <?php selected(get_field('pool_type', $listing_id) ?? '', 'heated'); ?>>Heated Pool</option>
                        </select>
                        <small>Type of swimming pool</small>
                    </div>

                    <div class="form-group">
                        <label for="has_spa">Spa/Hot Tub</label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="has_spa" name="has_spa" value="1" 
                                   <?php checked(get_field('has_spa', $listing_id) ?? 0, 1); ?>>
                            <span class="toggle-slider"></span>
                            Has Spa/Hot Tub
                        </label>
                    </div>

                    <div class="form-group">
                        <label for="garage_type">Garage Type</label>
                        <select id="garage_type" name="garage_type">
                            <option value="attached" <?php selected(get_field('garage_type', $listing_id) ?? 'attached', 'attached'); ?>>Attached Garage</option>
                            <option value="detached" <?php selected(get_field('garage_type', $listing_id) ?? '', 'detached'); ?>>Detached Garage</option>
                            <option value="carport" <?php selected(get_field('garage_type', $listing_id) ?? '', 'carport'); ?>>Carport</option>
                            <option value="none" <?php selected(get_field('garage_type', $listing_id) ?? '', 'none'); ?>>No Garage</option>
                            <option value="tandem" <?php selected(get_field('garage_type', $listing_id) ?? '', 'tandem'); ?>>Tandem Garage</option>
                            <option value="oversized" <?php selected(get_field('garage_type', $listing_id) ?? '', 'oversized'); ?>>Oversized Garage</option>
                        </select>
                        <small>Type of garage</small>
                    </div>

                    <div class="form-group">
                        <label for="air_conditioning">Air Conditioning</label>
                        <select id="air_conditioning" name="air_conditioning">
                            <option value="central" <?php selected(get_field('air_conditioning', $listing_id) ?? 'central', 'central'); ?>>Central Air</option>
                            <option value="window_units" <?php selected(get_field('air_conditioning', $listing_id) ?? '', 'window_units'); ?>>Window Units</option>
                            <option value="split_system" <?php selected(get_field('air_conditioning', $listing_id) ?? '', 'split_system'); ?>>Split System</option>
                            <option value="heat_pump" <?php selected(get_field('air_conditioning', $listing_id) ?? '', 'heat_pump'); ?>>Heat Pump</option>
                            <option value="none" <?php selected(get_field('air_conditioning', $listing_id) ?? '', 'none'); ?>>None</option>
                        </select>
                        <small>Type of air conditioning system</small>
                    </div>

                    <div class="form-group">
                        <label for="heating">Heating System</label>
                        <select id="heating" name="heating">
                            <option value="central" <?php selected(get_field('heating', $listing_id) ?? 'central', 'central'); ?>>Central Heat</option>
                            <option value="gas" <?php selected(get_field('heating', $listing_id) ?? '', 'gas'); ?>>Gas Heat</option>
                            <option value="electric" <?php selected(get_field('heating', $listing_id) ?? '', 'electric'); ?>>Electric Heat</option>
                            <option value="radiant" <?php selected(get_field('heating', $listing_id) ?? '', 'radiant'); ?>>Radiant Heat</option>
                            <option value="baseboard" <?php selected(get_field('heating', $listing_id) ?? '', 'baseboard'); ?>>Baseboard Heat</option>
                            <option value="fireplace" <?php selected(get_field('heating', $listing_id) ?? '', 'fireplace'); ?>>Fireplace Only</option>
                            <option value="none" <?php selected(get_field('heating', $listing_id) ?? '', 'none'); ?>>None</option>
                        </select>
                        <small>Type of heating system</small>
                    </div>

                    <div class="form-group">
                        <label for="water_heater">Water Heater</label>
                        <select id="water_heater" name="water_heater">
                            <option value="gas" <?php selected(get_field('water_heater', $listing_id) ?? 'gas', 'gas'); ?>>Gas Water Heater</option>
                            <option value="electric" <?php selected(get_field('water_heater', $listing_id) ?? '', 'electric'); ?>>Electric Water Heater</option>
                            <option value="tankless" <?php selected(get_field('water_heater', $listing_id) ?? '', 'tankless'); ?>>Tankless Water Heater</option>
                            <option value="solar" <?php selected(get_field('water_heater', $listing_id) ?? '', 'solar'); ?>>Solar Water Heater</option>
                            <option value="hybrid" <?php selected(get_field('water_heater', $listing_id) ?? '', 'hybrid'); ?>>Hybrid Water Heater</option>
                        </select>
                        <small>Type of water heater</small>
                    </div>
                </div>
            </div>

            <!-- Financial Information -->
            <div class="form-section">
                <h2>Financial Information</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="property_taxes">Annual Property Tax</label>
                        <div class="input-group">
                            <span class="input-prefix">$</span>
                            <input type="number" id="property_taxes" name="property_taxes" 
                                   value="<?php echo esc_attr(get_field('property_taxes', $listing_id) ?? ''); ?>" 
                                   min="0" step="100">
                            <span class="input-suffix">/year</span>
                        </div>
                        <small>Yearly property tax amount</small>
                    </div>

                    <div class="form-group">
                        <label for="hoa_fees">HOA Fees</label>
                        <div class="input-group">
                            <span class="input-prefix">$</span>
                            <input type="number" id="hoa_fees" name="hoa_fees" 
                                   value="<?php echo esc_attr(get_field('hoa_fees', $listing_id) ?? ''); ?>" 
                                   min="0" step="25">
                            <span class="input-suffix">/month</span>
                        </div>
                        <small>Monthly HOA (if not from community)</small>
                    </div>

                    <div class="form-group">
                        <label for="buyer_commission">Buyer Agent Commission</label>
                        <input type="text" id="buyer_commission" name="buyer_commission" 
                               value="<?php echo esc_attr(get_field('buyer_commission', $listing_id) ?? ''); ?>" 
                               maxlength="20" placeholder="3%">
                        <small>Commission offered (e.g., '3%' or '$15,000')</small>
                    </div>

                    <div class="form-group">
                        <label for="estimated_insurance">Est. Monthly Insurance</label>
                        <div class="input-group">
                            <span class="input-prefix">$</span>
                            <input type="number" id="estimated_insurance" name="estimated_insurance" 
                                   value="<?php echo esc_attr(get_field('estimated_insurance', $listing_id) ?? ''); ?>" 
                                   min="0" step="25">
                            <span class="input-suffix">/month</span>
                        </div>
                        <small>Estimated homeowners insurance</small>
                    </div>

                    <div class="form-group">
                        <label for="estimated_utilities">Est. Monthly Utilities</label>
                        <div class="input-group">
                            <span class="input-prefix">$</span>
                            <input type="number" id="estimated_utilities" name="estimated_utilities" 
                                   value="<?php echo esc_attr(get_field('estimated_utilities', $listing_id) ?? ''); ?>" 
                                   min="0" step="25">
                            <span class="input-suffix">/month</span>
                        </div>
                        <small>Average monthly utility costs</small>
                    </div>

                    <div class="form-group">
                        <label for="tax_id">Tax ID / Parcel Number</label>
                        <input type="text" id="tax_id" name="tax_id" 
                               value="<?php echo esc_attr(get_field('tax_id', $listing_id) ?? ''); ?>" 
                               maxlength="50" placeholder="123-45.67-89.10">
                        <small>Property tax identification number</small>
                    </div>

                    <div class="form-group">
                        <label for="price_per_sqft">Price per Sq Ft</label>
                        <div class="input-group">
                            <span class="input-prefix">$</span>
                            <input type="number" id="price_per_sqft" name="price_per_sqft" 
                                   value="<?php echo esc_attr(get_field('price_per_sqft', $listing_id) ?? ''); ?>" 
                                   min="0" step="0.01">
                            <span class="input-suffix">/sq ft</span>
                        </div>
                        <small>Calculated price per square foot</small>
                    </div>
                </div>
            </div>

            <!-- Construction & Systems -->
            <div class="form-section">
                <h2>Construction & Systems</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="builder">Builder</label>
                        <input type="text" id="builder" name="builder" 
                               value="<?php echo esc_attr(get_field('builder', $listing_id) ?? ''); ?>" 
                               maxlength="100" placeholder="Builder Name">
                        <small>Name of the builder/developer</small>
                    </div>

                    <div class="form-group">
                        <label for="roof_type">Roof Type</label>
                        <select id="roof_type" name="roof_type">
                            <option value="">Select roof type...</option>
                            <option value="asphalt_shingle" <?php selected(get_field('roof_type', $listing_id) ?? '', 'asphalt_shingle'); ?>>Asphalt Shingle</option>
                            <option value="architectural_shingle" <?php selected(get_field('roof_type', $listing_id) ?? '', 'architectural_shingle'); ?>>Architectural Shingle</option>
                            <option value="metal" <?php selected(get_field('roof_type', $listing_id) ?? '', 'metal'); ?>>Metal</option>
                            <option value="tile" <?php selected(get_field('roof_type', $listing_id) ?? '', 'tile'); ?>>Tile</option>
                            <option value="slate" <?php selected(get_field('roof_type', $listing_id) ?? '', 'slate'); ?>>Slate</option>
                            <option value="wood_shake" <?php selected(get_field('roof_type', $listing_id) ?? '', 'wood_shake'); ?>>Wood Shake</option>
                            <option value="rubber" <?php selected(get_field('roof_type', $listing_id) ?? '', 'rubber'); ?>>Rubber</option>
                            <option value="tar_gravel" <?php selected(get_field('roof_type', $listing_id) ?? '', 'tar_gravel'); ?>>Tar & Gravel</option>
                            <option value="other" <?php selected(get_field('roof_type', $listing_id) ?? '', 'other'); ?>>Other</option>
                        </select>
                        <small>Primary roofing material</small>
                    </div>

                    <div class="form-group">
                        <label for="foundation_type">Foundation</label>
                        <select id="foundation_type" name="foundation_type">
                            <option value="">Select foundation...</option>
                            <option value="slab" <?php selected(get_field('foundation_type', $listing_id) ?? '', 'slab'); ?>>Slab</option>
                            <option value="crawl_space" <?php selected(get_field('foundation_type', $listing_id) ?? '', 'crawl_space'); ?>>Crawl Space</option>
                            <option value="full_basement" <?php selected(get_field('foundation_type', $listing_id) ?? '', 'full_basement'); ?>>Full Basement</option>
                            <option value="partial_basement" <?php selected(get_field('foundation_type', $listing_id) ?? '', 'partial_basement'); ?>>Partial Basement</option>
                            <option value="pier_beam" <?php selected(get_field('foundation_type', $listing_id) ?? '', 'pier_beam'); ?>>Pier & Beam</option>
                            <option value="block" <?php selected(get_field('foundation_type', $listing_id) ?? '', 'block'); ?>>Block</option>
                            <option value="other" <?php selected(get_field('foundation_type', $listing_id) ?? '', 'other'); ?>>Other</option>
                        </select>
                        <small>Foundation type</small>
                    </div>

                    <div class="form-group">
                        <label for="heating_system">Heating System</label>
                        <select id="heating_system" name="heating_system">
                            <option value="">Select heating...</option>
                            <option value="central_air" <?php selected(get_field('heating_system', $listing_id) ?? '', 'central_air'); ?>>Central Air</option>
                            <option value="heat_pump" <?php selected(get_field('heating_system', $listing_id) ?? '', 'heat_pump'); ?>>Heat Pump</option>
                            <option value="forced_air" <?php selected(get_field('heating_system', $listing_id) ?? '', 'forced_air'); ?>>Forced Air</option>
                            <option value="baseboard" <?php selected(get_field('heating_system', $listing_id) ?? '', 'baseboard'); ?>>Baseboard</option>
                            <option value="radiant" <?php selected(get_field('heating_system', $listing_id) ?? '', 'radiant'); ?>>Radiant</option>
                            <option value="wood_stove" <?php selected(get_field('heating_system', $listing_id) ?? '', 'wood_stove'); ?>>Wood Stove</option>
                            <option value="fireplace" <?php selected(get_field('heating_system', $listing_id) ?? '', 'fireplace'); ?>>Fireplace</option>
                            <option value="geothermal" <?php selected(get_field('heating_system', $listing_id) ?? '', 'geothermal'); ?>>Geothermal</option>
                            <option value="solar" <?php selected(get_field('heating_system', $listing_id) ?? '', 'solar'); ?>>Solar</option>
                            <option value="none" <?php selected(get_field('heating_system', $listing_id) ?? '', 'none'); ?>>None</option>
                            <option value="other" <?php selected(get_field('heating_system', $listing_id) ?? '', 'other'); ?>>Other</option>
                        </select>
                        <small>Primary heating system</small>
                    </div>

                    <div class="form-group">
                        <label for="heating_fuel">Heating Fuel</label>
                        <select id="heating_fuel" name="heating_fuel">
                            <option value="">Select fuel...</option>
                            <option value="electric" <?php selected(get_field('heating_fuel', $listing_id) ?? '', 'electric'); ?>>Electric</option>
                            <option value="natural_gas" <?php selected(get_field('heating_fuel', $listing_id) ?? '', 'natural_gas'); ?>>Natural Gas</option>
                            <option value="propane" <?php selected(get_field('heating_fuel', $listing_id) ?? '', 'propane'); ?>>Propane</option>
                            <option value="oil" <?php selected(get_field('heating_fuel', $listing_id) ?? '', 'oil'); ?>>Oil</option>
                            <option value="wood" <?php selected(get_field('heating_fuel', $listing_id) ?? '', 'wood'); ?>>Wood</option>
                            <option value="solar" <?php selected(get_field('heating_fuel', $listing_id) ?? '', 'solar'); ?>>Solar</option>
                            <option value="geothermal" <?php selected(get_field('heating_fuel', $listing_id) ?? '', 'geothermal'); ?>>Geothermal</option>
                            <option value="other" <?php selected(get_field('heating_fuel', $listing_id) ?? '', 'other'); ?>>Other</option>
                        </select>
                        <small>Primary heating fuel source</small>
                    </div>

                    <div class="form-group">
                        <label for="cooling_system">Cooling System</label>
                        <select id="cooling_system" name="cooling_system">
                            <option value="">Select cooling...</option>
                            <option value="central_air" <?php selected(get_field('cooling_system', $listing_id) ?? '', 'central_air'); ?>>Central Air</option>
                            <option value="heat_pump" <?php selected(get_field('cooling_system', $listing_id) ?? '', 'heat_pump'); ?>>Heat Pump</option>
                            <option value="window_units" <?php selected(get_field('cooling_system', $listing_id) ?? '', 'window_units'); ?>>Window Units</option>
                            <option value="evaporative" <?php selected(get_field('cooling_system', $listing_id) ?? '', 'evaporative'); ?>>Evaporative</option>
                            <option value="geothermal" <?php selected(get_field('cooling_system', $listing_id) ?? '', 'geothermal'); ?>>Geothermal</option>
                            <option value="fans" <?php selected(get_field('cooling_system', $listing_id) ?? '', 'fans'); ?>>Ceiling Fans Only</option>
                            <option value="none" <?php selected(get_field('cooling_system', $listing_id) ?? '', 'none'); ?>>None</option>
                            <option value="other" <?php selected(get_field('cooling_system', $listing_id) ?? '', 'other'); ?>>Other</option>
                        </select>
                        <small>Primary cooling system</small>
                    </div>

                    <div class="form-group">
                        <label for="water_source">Water Source</label>
                        <select id="water_source" name="water_source">
                            <option value="">Select water source...</option>
                            <option value="city" <?php selected(get_field('water_source', $listing_id) ?? '', 'city'); ?>>City Water</option>
                            <option value="well" <?php selected(get_field('water_source', $listing_id) ?? '', 'well'); ?>>Private Well</option>
                            <option value="community" <?php selected(get_field('water_source', $listing_id) ?? '', 'community'); ?>>Community Well</option>
                            <option value="other" <?php selected(get_field('water_source', $listing_id) ?? '', 'other'); ?>>Other</option>
                        </select>
                        <small>Primary water source</small>
                    </div>

                    <div class="form-group">
                        <label for="sewer_system">Sewer System</label>
                        <select id="sewer_system" name="sewer_system">
                            <option value="">Select sewer system...</option>
                            <option value="city_sewer" <?php selected(get_field('sewer_system', $listing_id) ?? '', 'city_sewer'); ?>>City Sewer</option>
                            <option value="septic" <?php selected(get_field('sewer_system', $listing_id) ?? '', 'septic'); ?>>Septic System</option>
                            <option value="community" <?php selected(get_field('sewer_system', $listing_id) ?? '', 'community'); ?>>Community System</option>
                            <option value="lpp" <?php selected(get_field('sewer_system', $listing_id) ?? '', 'lpp'); ?>>Low Pressure Pipe (LPP)</option>
                            <option value="other" <?php selected(get_field('sewer_system', $listing_id) ?? '', 'other'); ?>>Other</option>
                        </select>
                        <small>Waste water system</small>
                    </div>

                    <div class="form-group">
                        <label for="electric_service">Electric Service</label>
                        <select id="electric_service" name="electric_service">
                            <option value="">Select service...</option>
                            <option value="100_amp" <?php selected(get_field('electric_service', $listing_id) ?? '', '100_amp'); ?>>100 Amp</option>
                            <option value="150_amp" <?php selected(get_field('electric_service', $listing_id) ?? '', '150_amp'); ?>>150 Amp</option>
                            <option value="200_amp" <?php selected(get_field('electric_service', $listing_id) ?? '', '200_amp'); ?>>200 Amp</option>
                            <option value="200_plus_amp" <?php selected(get_field('electric_service', $listing_id) ?? '', '200_plus_amp'); ?>>200+ Amp</option>
                            <option value="400_amp" <?php selected(get_field('electric_service', $listing_id) ?? '', '400_amp'); ?>>400 Amp</option>
                            <option value="other" <?php selected(get_field('electric_service', $listing_id) ?? '', 'other'); ?>>Other</option>
                        </select>
                        <small>Electrical service capacity</small>
                    </div>

                    <div class="form-group">
                        <label for="hot_water">Hot Water System</label>
                        <select id="hot_water" name="hot_water">
                            <option value="">Select hot water system...</option>
                            <option value="electric_tank" <?php selected(get_field('hot_water', $listing_id) ?? '', 'electric_tank'); ?>>Electric Tank</option>
                            <option value="gas_tank" <?php selected(get_field('hot_water', $listing_id) ?? '', 'gas_tank'); ?>>Gas Tank</option>
                            <option value="tankless_electric" <?php selected(get_field('hot_water', $listing_id) ?? '', 'tankless_electric'); ?>>Tankless Electric</option>
                            <option value="tankless_gas" <?php selected(get_field('hot_water', $listing_id) ?? '', 'tankless_gas'); ?>>Tankless Gas</option>
                            <option value="heat_pump" <?php selected(get_field('hot_water', $listing_id) ?? '', 'heat_pump'); ?>>Heat Pump Water Heater</option>
                            <option value="solar" <?php selected(get_field('hot_water', $listing_id) ?? '', 'solar'); ?>>Solar</option>
                            <option value="other" <?php selected(get_field('hot_water', $listing_id) ?? '', 'other'); ?>>Other</option>
                        </select>
                        <small>Hot water heating system</small>
                    </div>

                    <div class="form-group full-width">
                        <label for="construction_materials">Construction Materials</label>
                        <textarea id="construction_materials" name="construction_materials" 
                                  rows="4" placeholder="Frame construction, insulation details, etc."><?php echo esc_textarea(get_field('construction_materials', $listing_id) ?? ''); ?></textarea>
                        <small>Additional construction details and materials</small>
                    </div>
                </div>
            </div>

            <!-- Agent Information -->
            <div class="form-section">
                <h2>Agent Information</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="listing_agent">Listing Agent <span class="required">*</span></label>
                        <select id="listing_agent" name="listing_agent" required>
                            <option value="">Select Agent</option>
                            <?php
                            // Get agent posts
                            $agent_posts = get_posts([
                                'post_type' => 'agent',
                                'post_status' => 'publish',
                                'numberposts' => -1,
                                'orderby' => 'title',
                                'order' => 'ASC'
                            ]);
                            
                            // Get users with agent role (fallback/additional)
                            $agent_users = get_users([
                                'role' => 'agent',
                                'orderby' => 'display_name',
                                'order' => 'ASC'
                            ]);
                            
                            $current_agent_id = get_field('listing_agent', $listing_id) ?? '';
                            $options_added = [];
                            
                            // Add agent posts first (primary method)
                            if (!empty($agent_posts)) {
                                echo '<optgroup label="Agent Profiles">';
                                foreach ($agent_posts as $agent) {
                                    $selected = selected($current_agent_id, $agent->ID, false);
                                    echo '<option value="' . $agent->ID . '" ' . $selected . '>' . esc_html($agent->post_title) . '</option>';
                                    $options_added[] = $agent->ID;
                                }
                                echo '</optgroup>';
                            }
                            
                            // Add agent users that aren't already linked to posts
                            if (!empty($agent_users)) {
                                $unlinked_users = [];
                                foreach ($agent_users as $user) {
                                    $linked_agent_id = get_user_meta($user->ID, '_synced_agent_id', true);
                                    if (!$linked_agent_id || !in_array($linked_agent_id, $options_added)) {
                                        $unlinked_users[] = $user;
                                    }
                                }
                                
                                if (!empty($unlinked_users)) {
                                    echo '<optgroup label="User Accounts">';
                                    foreach ($unlinked_users as $user) {
                                        // Use negative ID to distinguish from posts
                                        $user_value = 'user_' . $user->ID;
                                        $selected = selected($current_agent_id, $user_value, false);
                                        echo '<option value="' . $user_value . '" ' . $selected . '>' . esc_html($user->display_name) . ' (User)</option>';
                                    }
                                    echo '</optgroup>';
                                }
                            }
                            ?>
                        </select>
                        <small>Primary listing agent</small>
                    </div>

                    <div class="form-group">
                        <label for="co_listing_agent">Co-Listing Agent</label>
                        <select id="co_listing_agent" name="co_listing_agent">
                            <option value="">Select Co-Agent</option>
                            <?php
                            $current_co_agent_id = get_field('co_listing_agent', $listing_id) ?? '';
                            $co_options_added = [];
                            
                            // Add agent posts
                            if (!empty($agent_posts)) {
                                echo '<optgroup label="Agent Profiles">';
                                foreach ($agent_posts as $agent) {
                                    $selected = selected($current_co_agent_id, $agent->ID, false);
                                    echo '<option value="' . $agent->ID . '" ' . $selected . '>' . esc_html($agent->post_title) . '</option>';
                                    $co_options_added[] = $agent->ID;
                                }
                                echo '</optgroup>';
                            }
                            
                            // Add unlinked agent users
                            if (!empty($agent_users)) {
                                $unlinked_users = [];
                                foreach ($agent_users as $user) {
                                    $linked_agent_id = get_user_meta($user->ID, '_synced_agent_id', true);
                                    if (!$linked_agent_id || !in_array($linked_agent_id, $co_options_added)) {
                                        $unlinked_users[] = $user;
                                    }
                                }
                                
                                if (!empty($unlinked_users)) {
                                    echo '<optgroup label="User Accounts">';
                                    foreach ($unlinked_users as $user) {
                                        $user_value = 'user_' . $user->ID;
                                        $selected = selected($current_co_agent_id, $user_value, false);
                                        echo '<option value="' . $user_value . '" ' . $selected . '>' . esc_html($user->display_name) . ' (User)</option>';
                                    }
                                    echo '</optgroup>';
                                }
                            }
                            ?>
                        </select>
                        <small>Optional co-listing agent</small>
                    </div>

                    <div class="form-group">
                        <label for="listing_office">Listing Office</label>
                        <input type="text" id="listing_office" name="listing_office" 
                               value="<?php echo esc_attr(get_field('listing_office', $listing_id) ?? 'Happy Place Homes'); ?>" 
                               placeholder="Happy Place Homes">
                        <small>Brokerage name</small>
                    </div>

                    <div class="form-group">
                        <label for="listing_office_phone">Office Phone</label>
                        <input type="text" id="listing_office_phone" name="listing_office_phone" 
                               value="<?php echo esc_attr(get_field('listing_office_phone', $listing_id) ?? ''); ?>" 
                               placeholder="(302) 555-0123">
                    </div>
                </div>
            </div>

            <!-- Photos & Media -->
            <div class="form-section">
                <h2>Photos & Media</h2>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="primary_photo">Primary Photo</label>
                        <input type="file" id="primary_photo" name="primary_photo" accept="image/*">
                        <small>Main listing photo (overrides featured image). Min: 1200x800px. Formats: JPG, PNG, WEBP</small>
                    </div>

                    <div class="form-group full-width">
                        <label for="photo_gallery">Photo Gallery</label>
                        <input type="file" id="photo_gallery" name="photo_gallery[]" accept="image/*" multiple>
                        <small>Upload all property photos (max 50). Formats: JPG, PNG, WEBP</small>
                    </div>

                    <div class="form-group">
                        <label for="virtual_tour_url">Virtual Tour URL</label>
                        <input type="url" id="virtual_tour_url" name="virtual_tour_url" 
                               value="<?php echo esc_attr(get_field('virtual_tour_url', $listing_id) ?? ''); ?>" 
                               placeholder="https://my.matterport.com/show/?m=...">
                        <small>Link to Matterport or other virtual tour</small>
                    </div>

                    <div class="form-group">
                        <label for="video_url">Video Tour URL</label>
                        <input type="url" id="video_url" name="video_url" 
                               value="<?php echo esc_attr(get_field('video_url', $listing_id) ?? ''); ?>" 
                               placeholder="https://www.youtube.com/watch?v=...">
                        <small>YouTube or Vimeo link</small>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="<?php echo home_url('/dashboard/?section=listings'); ?>" class="btn btn-secondary">
                    <i class="icon-arrow-left"></i> Cancel
                </a>
                <div class="action-buttons">
                    <button type="button" id="saveDraftBtn" class="btn btn-outline">
                        <i class="icon-save"></i> Save as Draft
                    </button>
                    <button type="submit" id="publishBtn" class="btn btn-primary">
                        <i class="icon-check"></i> <?php echo $is_edit ? 'Update & Publish' : 'Save & Publish'; ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
/* Modern Form Styling */
:root {
    --primary: #2563eb;
    --primary-hover: #1d4ed8;
    --secondary: #64748b;
    --success: #059669;
    --warning: #d97706;
    --error: #dc2626;
    --gray-50: #f8fafc;
    --gray-100: #f1f5f9;
    --gray-200: #e2e8f0;
    --gray-300: #cbd5e1;
    --gray-400: #94a3b8;
    --gray-500: #64748b;
    --gray-600: #475569;
    --gray-700: #334155;
    --gray-800: #1e293b;
    --gray-900: #0f172a;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    --radius: 8px;
    --radius-lg: 12px;
}

.listing-form-page {
    min-height: 100vh;
    background: linear-gradient(135deg, var(--gray-50) 0%, #e0e7ff 100%);
    padding: 2rem 0;
}

.container {
    max-width: 900px;
    margin: 0 auto;
    padding: 0 1rem;
}

.listing-form-header {
    background: white;
    padding: 2.5rem;
    border-radius: var(--radius-lg);
    margin-bottom: 2rem;
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--gray-200);
    position: relative;
    overflow: hidden;
}

.listing-form-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary) 0%, var(--hph-primary) 100%);
}

.form-breadcrumb {
    margin-bottom: 1.5rem;
}

.form-breadcrumb a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.95rem;
    transition: all 0.2s;
}

.form-breadcrumb a:hover {
    color: var(--primary-hover);
    transform: translateX(-2px);
}

.listing-form-header h1 {
    margin: 0 0 0.5rem 0;
    color: var(--gray-900);
    font-size: 2.25rem;
    font-weight: 700;
    letter-spacing: -0.025em;
}

.listing-form-header p {
    color: var(--gray-600);
    margin: 0;
    font-size: 1.1rem;
}

.listing-form {
    background: white;
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--gray-200);
}

.form-section {
    padding: 2.5rem;
    border-bottom: 1px solid var(--gray-200);
    position: relative;
}

.form-section:last-of-type {
    border-bottom: none;
}

.form-section h2 {
    margin: 0 0 2rem 0;
    color: var(--gray-800);
    font-size: 1.75rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid var(--gray-100);
}

.form-section h2::before {
    content: '';
    width: 4px;
    height: 24px;
    background: var(--primary);
    border-radius: 2px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
    align-items: start;
}

.form-group {
    display: flex;
    flex-direction: column;
    position: relative;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    font-weight: 600;
    color: var(--gray-700);
    margin-bottom: 0.75rem;
    font-size: 0.95rem;
    letter-spacing: 0.01em;
}

.required {
    color: var(--error);
    font-weight: 700;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 0.875rem 1rem;
    border: 2px solid var(--gray-300);
    border-radius: var(--radius);
    font-size: 1rem;
    transition: all 0.2s ease;
    background: white;
    color: var(--gray-900);
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgb(37 99 235 / 0.1);
    transform: translateY(-1px);
}

.form-group input:hover,
.form-group select:hover,
.form-group textarea:hover {
    border-color: var(--gray-400);
}

.input-group {
    display: flex;
    align-items: stretch;
    border-radius: var(--radius);
    overflow: hidden;
    border: 2px solid var(--gray-300);
    transition: all 0.2s ease;
}

.input-group:focus-within {
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgb(37 99 235 / 0.1);
}

.input-prefix,
.input-suffix {
    background: var(--gray-100);
    border: none;
    padding: 0.875rem 1rem;
    color: var(--gray-600);
    font-weight: 600;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    white-space: nowrap;
}

.input-group input {
    border: none;
    flex: 1;
    border-radius: 0;
}

.input-group input:focus {
    transform: none;
    box-shadow: none;
}

/* Enhanced Toggle Switch */
.toggle-switch {
    display: inline-flex;
    align-items: center;
    cursor: pointer;
    user-select: none;
    gap: 0.75rem;
    padding: 0.5rem;
    border-radius: var(--radius);
    transition: all 0.2s;
}

.toggle-switch:hover {
    background: var(--gray-50);
}

.toggle-switch input[type="checkbox"] {
    display: none;
}

.toggle-slider {
    position: relative;
    width: 52px;
    height: 28px;
    background: var(--gray-300);
    border-radius: 28px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
}

.toggle-slider:before {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 24px;
    height: 24px;
    background: white;
    border-radius: 50%;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: var(--shadow);
}

.toggle-switch input[type="checkbox"]:checked + .toggle-slider {
    background: var(--primary);
}

.toggle-switch input[type="checkbox"]:checked + .toggle-slider:before {
    transform: translateX(24px);
}

.form-group small {
    margin-top: 0.5rem;
    color: var(--gray-500);
    font-size: 0.875rem;
    line-height: 1.4;
}

.form-actions {
    padding: 2.5rem;
    background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid var(--gray-200);
}

.action-buttons {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.btn {
    padding: 0.875rem 2rem;
    border-radius: var(--radius);
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.2s ease;
    font-size: 0.95rem;
    letter-spacing: 0.01em;
    position: relative;
    overflow: hidden;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary) 0%, var(--hph-primary) 100%);
    color: white;
    box-shadow: var(--shadow);
}

.btn-primary:hover:not(:disabled) {
    background: linear-gradient(135deg, var(--primary-hover) 0%, var(--primary) 100%);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.btn-outline {
    background: white;
    color: var(--gray-700);
    border-color: var(--gray-300);
}

.btn-outline:hover:not(:disabled) {
    background: var(--gray-50);
    border-color: var(--gray-400);
    transform: translateY(-1px);
}

.btn-secondary {
    background: var(--gray-100);
    color: var(--gray-700);
    border-color: var(--gray-300);
}

.btn-secondary:hover:not(:disabled) {
    background: var(--gray-200);
    transform: translateY(-1px);
}

/* Loading State */
.btn.loading::after {
    content: '';
    position: absolute;
    width: 16px;
    height: 16px;
    margin: auto;
    border: 2px solid transparent;
    border-top-color: currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

.btn.loading span {
    opacity: 0;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Success/Error Messages */
.form-message {
    padding: 1rem 1.5rem;
    border-radius: var(--radius);
    margin-bottom: 1.5rem;
    font-weight: 500;
}

.form-message.success {
    background: #ecfdf5;
    color: var(--success);
    border: 1px solid #a7f3d0;
}

.form-message.error {
    background: #fef2f2;
    color: var(--error);
    border: 1px solid #fca5a5;
}

/* Responsive Design */
@media (max-width: 768px) {
    .listing-form-page {
        padding: 1rem 0;
    }
    
    .container {
        padding: 0 0.75rem;
    }
    
    .listing-form-header,
    .form-section {
        padding: 1.5rem;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .form-actions {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
        padding: 1.5rem;
    }
    
    .action-buttons {
        flex-direction: column;
        width: 100%;
    }
    
    .btn {
        justify-content: center;
        width: 100%;
    }
    
    .listing-form-header h1 {
        font-size: 1.875rem;
    }
    
    .form-section h2 {
        font-size: 1.5rem;
    }
}

/* Icons (using simple CSS shapes for now) */
.icon-arrow-left::before {
    content: '←';
    font-weight: bold;
}

.icon-save::before {
    content: '💾';
}

.icon-check::before {
    content: '✓';
    font-weight: bold;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('listingForm');
    const publishBtn = document.getElementById('publishBtn');
    const saveDraftBtn = document.getElementById('saveDraftBtn');
    const messageContainer = document.createElement('div');
    
    // Insert message container at the top of the form
    form.parentNode.insertBefore(messageContainer, form);

    // Auto-save draft functionality
    let autoSaveTimeout;
    let hasUnsavedChanges = false;

    function showMessage(message, type = 'success') {
        messageContainer.innerHTML = `<div class="form-message ${type}">${message}</div>`;
        setTimeout(() => {
            messageContainer.innerHTML = '';
        }, 5000);
    }

    function setButtonLoading(button, loading = true) {
        if (loading) {
            button.classList.add('loading');
            button.disabled = true;
            const span = button.querySelector('span') || document.createElement('span');
            if (!button.querySelector('span')) {
                span.textContent = button.textContent;
                button.innerHTML = '';
                button.appendChild(span);
            }
        } else {
            button.classList.remove('loading');
            button.disabled = false;
        }
    }

    function saveForm(isDraft = false) {
        const button = isDraft ? saveDraftBtn : publishBtn;
        setButtonLoading(button, true);

        const formData = new FormData(form);
        
        // Explicitly set post status
        if (isDraft) {
            formData.set('post_status', 'draft');
        } else {
            formData.set('post_status', 'publish');
        }

        return fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.data.message, 'success');
                hasUnsavedChanges = false;
                
                // Always redirect to fresh form after successful submission (draft or publish)
                if (data.data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.data.redirect;
                    }, 1500);
                } else {
                    // Fallback to listing form page
                    setTimeout(() => {
                        window.location.href = '<?php echo home_url('/listing-form/'); ?>';
                    }, 1500);
                }
                
                return true;
            } else {
                throw new Error(data.data || 'Unknown error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Error: ' + error.message, 'error');
            return false;
        })
        .finally(() => {
            setButtonLoading(button, false);
        });
    }

    // Handle publish form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        saveForm(false);
    });

    // Handle draft saving
    saveDraftBtn.addEventListener('click', function(e) {
        e.preventDefault();
        saveForm(true);
    });

    // Track form changes for unsaved changes warning
    form.addEventListener('input', function() {
        hasUnsavedChanges = true;
        
        // Clear existing timeout and set new auto-save
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(() => {
            if (hasUnsavedChanges && validateForm()) { // Only auto-save if form is valid
                saveForm(true).then(success => {
                    if (success) {
                        showMessage('Draft auto-saved', 'success');
                    }
                }).catch(error => {
                    console.log('Auto-save skipped due to validation errors');
                });
            }
        }, 10000); // Auto-save after 10 seconds of inactivity
    });

    // Warn before leaving with unsaved changes
    window.addEventListener('beforeunload', function(e) {
        if (hasUnsavedChanges) {
            e.preventDefault();
            e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
            return e.returnValue;
        }
    });

    // Form validation enhancement
    function validateForm() {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            const group = field.closest('.form-group');
            
            if (!field.value.trim()) {
                field.style.borderColor = 'var(--error)';
                group.classList.add('error');
                isValid = false;
            } else {
                field.style.borderColor = '';
                group.classList.remove('error');
            }
        });
        
        return isValid;
    }

    // Real-time validation
    form.addEventListener('blur', function(e) {
        if (e.target.hasAttribute('required')) {
            const group = e.target.closest('.form-group');
            
            if (!e.target.value.trim()) {
                e.target.style.borderColor = 'var(--error)';
                group.classList.add('error');
            } else {
                e.target.style.borderColor = '';
                group.classList.remove('error');
            }
        }
    }, true);

    // Enhance number inputs with better UX
    const numberInputs = form.querySelectorAll('input[type="number"]');
    numberInputs.forEach(input => {
        input.addEventListener('wheel', function(e) {
            e.preventDefault(); // Prevent accidental scrolling changes
        });
    });

    // Price per sqft auto-calculation
    const priceInput = document.getElementById('listing_price');
    const sqftInput = document.getElementById('square_feet');
    const pricePerSqftInput = document.getElementById('price_per_sqft');

    function calculatePricePerSqft() {
        const price = parseFloat(priceInput.value) || 0;
        const sqft = parseFloat(sqftInput.value) || 0;
        
        if (price > 0 && sqft > 0) {
            const pricePerSqft = (price / sqft).toFixed(2);
            pricePerSqftInput.value = pricePerSqft;
        }
    }

    if (priceInput && sqftInput && pricePerSqftInput) {
        priceInput.addEventListener('input', calculatePricePerSqft);
        sqftInput.addEventListener('input', calculatePricePerSqft);
    }

    // Enhance file inputs with preview capability
    const photoInputs = form.querySelectorAll('input[type="file"]');
    photoInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            const group = e.target.closest('.form-group');
            
            // Remove existing preview
            const existingPreview = group.querySelector('.file-preview');
            if (existingPreview) {
                existingPreview.remove();
            }
            
            if (files.length > 0) {
                const preview = document.createElement('div');
                preview.className = 'file-preview';
                preview.style.marginTop = '0.5rem';
                
                files.slice(0, 5).forEach(file => { // Show max 5 previews
                    if (file.type.startsWith('image/')) {
                        const img = document.createElement('img');
                        img.style.width = '60px';
                        img.style.height = '60px';
                        img.style.objectFit = 'cover';
                        img.style.borderRadius = '4px';
                        img.style.marginRight = '0.5rem';
                        img.style.border = '1px solid var(--gray-300)';
                        
                        const reader = new FileReader();
                        reader.onload = e => img.src = e.target.result;
                        reader.readAsDataURL(file);
                        
                        preview.appendChild(img);
                    }
                });
                
                if (files.length > 5) {
                    const more = document.createElement('span');
                    more.textContent = `+${files.length - 5} more`;
                    more.style.color = 'var(--gray-500)';
                    more.style.fontSize = '0.875rem';
                    preview.appendChild(more);
                }
                
                group.appendChild(preview);
            }
        });
    });

    // Auto-fill Marketing Title with address
    const streetNumberInput = document.getElementById('street_number');
    const streetNameInput = document.getElementById('street_name');
    const streetTypeInput = document.getElementById('street_type');
    const marketingTitleInput = document.getElementById('property_title');

    function updateMarketingTitle() {
        if (marketingTitleInput && !marketingTitleInput.value) {
            const streetNumber = streetNumberInput?.value?.trim() || '';
            const streetName = streetNameInput?.value?.trim() || '';
            const streetType = streetTypeInput?.value || '';
            
            if (streetName) {
                let address = '';
                
                if (streetNumber) {
                    address += streetNumber + ' ';
                }
                
                address += streetName;
                
                if (streetType && streetType !== 'St') {
                    // Convert abbreviated street type to full word
                    const streetTypes = {
                        'Ave': 'Avenue',
                        'Blvd': 'Boulevard', 
                        'Dr': 'Drive',
                        'Rd': 'Road',
                        'Ln': 'Lane',
                        'Way': 'Way',
                        'Ct': 'Court',
                        'Pl': 'Place',
                        'Cir': 'Circle',
                        'Pkwy': 'Parkway',
                        'Trl': 'Trail'
                    };
                    address += ' ' + (streetTypes[streetType] || streetType);
                } else if (streetType === 'St') {
                    address += ' Street';
                }
                
                if (address.trim()) {
                    marketingTitleInput.value = address.trim();
                    marketingTitleInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }
        }
    }

    // Add listeners to address fields
    if (streetNumberInput && streetNameInput && streetTypeInput && marketingTitleInput) {
        streetNumberInput.addEventListener('input', updateMarketingTitle);
        streetNameInput.addEventListener('input', updateMarketingTitle);
        streetTypeInput.addEventListener('change', updateMarketingTitle);
        
        // Also update on page load if fields have values but title is empty
        updateMarketingTitle();
    }

    // Initialize - calculate price per sqft if values exist
    if (priceInput && sqftInput && priceInput.value && sqftInput.value) {
        calculatePricePerSqft();
    }
});
</script>

<?php get_footer(); ?>
