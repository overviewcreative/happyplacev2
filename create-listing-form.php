<?php
/**
 * Interactive Listing Creator
 * Visit this page to create listings from MLS data
 */

require_once 'wp-load.php';

// Check if user is logged in and has permissions
if (!is_user_logged_in() || !current_user_can('edit_posts')) {
    wp_die('You do not have permission to access this page.');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Listing from MLS Data</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input, select, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        input[type="checkbox"] { width: auto; }
        .row { display: flex; gap: 15px; }
        .col { flex: 1; }
        .btn { background: #0073aa; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #005a87; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin: 20px 0; }
        textarea { height: 100px; }
        .description { height: 200px; }
    </style>
</head>
<body>
    <h1>Create Listing from MLS Data</h1>
    
    <?php
    if ($_POST && isset($_POST['create_listing'])) {
        // Process form submission
        $listing_data = [
            'title' => sanitize_text_field($_POST['street_number'] . ' ' . $_POST['street_name'] . ' ' . $_POST['street_type']),
            'status' => 'publish',
            'post_type' => 'listing',
            'description' => sanitize_textarea_field($_POST['description']),
        ];
        
        // Create the post
        $post_id = wp_insert_post([
            'post_title' => $listing_data['title'],
            'post_content' => $listing_data['description'],
            'post_status' => 'publish',
            'post_type' => 'listing',
        ]);
        
        if (!is_wp_error($post_id)) {
            // Update ACF fields
            $fields = [
                'street_number', 'street_name', 'street_type', 'city', 'state', 'zip_code',
                'listing_price', 'mls_number', 'listing_status', 'property_type',
                'bedrooms', 'bathrooms_full', 'bathrooms_half', 'square_feet', 'lot_size',
                'year_built', 'garage_spaces', 'school_district', 'zoning', 'builder',
                'style', 'condition', 'heating_type', 'cooling_type', 'water_source',
                'inclusions', 'exclusions', 'days_on_market'
            ];
            
            foreach ($fields as $field) {
                if (isset($_POST[$field]) && $_POST[$field] !== '') {
                    $value = $_POST[$field];
                    
                    // Convert numeric fields
                    if (in_array($field, ['listing_price', 'bedrooms', 'bathrooms_full', 'bathrooms_half', 'square_feet', 'lot_size', 'year_built', 'garage_spaces', 'days_on_market'])) {
                        $value = intval($value);
                    }
                    
                    // Convert boolean fields
                    if (in_array($field, ['is_featured', 'pool', 'waterfront', 'fireplace', 'basement', 'central_air'])) {
                        $value = isset($_POST[$field]) ? true : false;
                    }
                    
                    update_field($field, $value, $post_id);
                }
            }
            
            // Handle checkboxes separately
            $checkboxes = ['is_featured', 'pool', 'waterfront', 'fireplace', 'basement', 'central_air'];
            foreach ($checkboxes as $checkbox) {
                update_field($checkbox, isset($_POST[$checkbox]), $post_id);
            }
            
            echo '<div class="success">';
            echo '<strong>Success!</strong> Listing created successfully.<br>';
            echo 'Listing ID: ' . $post_id . '<br>';
            echo '<a href="' . get_permalink($post_id) . '" target="_blank">View Listing</a> | ';
            echo '<a href="' . admin_url('post.php?post=' . $post_id . '&action=edit') . '" target="_blank">Edit in WordPress</a>';
            echo '</div>';
        } else {
            echo '<div class="error"><strong>Error:</strong> Failed to create listing.</div>';
        }
    }
    ?>
    
    <form method="post">
        <h2>Property Address</h2>
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label>Street Number</label>
                    <input type="text" name="street_number" value="21222" required>
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label>Street Name</label>
                    <input type="text" name="street_name" value="Cool Spring" required>
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label>Street Type</label>
                    <input type="text" name="street_type" value="Rd" required>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label>City</label>
                    <input type="text" name="city" value="Milton" required>
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label>State</label>
                    <input type="text" name="state" value="DE" required>
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label>Zip Code</label>
                    <input type="text" name="zip_code" value="19968" required>
                </div>
            </div>
        </div>
        
        <h2>Basic Information</h2>
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label>Listing Price</label>
                    <input type="number" name="listing_price" value="869900" required>
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label>MLS Number</label>
                    <input type="text" name="mls_number" value="DESU2095796">
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label>Listing Status</label>
                    <select name="listing_status">
                        <option value="Active" selected>Active</option>
                        <option value="Pending">Pending</option>
                        <option value="Sold">Sold</option>
                        <option value="Withdrawn">Withdrawn</option>
                    </select>
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label>Property Type</label>
                    <select name="property_type">
                        <option value="Residential" selected>Residential</option>
                        <option value="Commercial">Commercial</option>
                        <option value="Land">Land</option>
                        <option value="Multi-Family">Multi-Family</option>
                    </select>
                </div>
            </div>
        </div>
        
        <h2>Property Details</h2>
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label>Bedrooms</label>
                    <input type="number" name="bedrooms" value="4">
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label>Full Bathrooms</label>
                    <input type="number" name="bathrooms_full" value="3">
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label>Half Bathrooms</label>
                    <input type="number" name="bathrooms_half" value="0">
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label>Square Feet</label>
                    <input type="number" name="square_feet" value="2910">
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label>Lot Size (sq ft)</label>
                    <input type="number" name="lot_size" value="63162">
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label>Year Built</label>
                    <input type="number" name="year_built" value="2022">
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label>Garage Spaces</label>
                    <input type="number" name="garage_spaces" value="6">
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label>Days on Market</label>
                    <input type="number" name="days_on_market" value="2">
                </div>
            </div>
        </div>
        
        <h2>Additional Information</h2>
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label>School District</label>
                    <input type="text" name="school_district" value="Cape Henlopen">
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label>Zoning</label>
                    <input type="text" name="zoning" value="AR-1">
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label>Builder</label>
                    <input type="text" name="builder" value="WN Builders inc">
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label>Style</label>
                    <input type="text" name="style" value="Coastal">
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label>Condition</label>
                    <input type="text" name="condition" value="Excellent">
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label>Heating Type</label>
                    <input type="text" name="heating_type" value="Heat Pump">
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label>Cooling Type</label>
                    <input type="text" name="cooling_type" value="Heat Pump">
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label>Water Source</label>
                    <input type="text" name="water_source" value="Well">
                </div>
            </div>
        </div>
        
        <h2>Features</h2>
        <div class="row">
            <div class="col">
                <label><input type="checkbox" name="is_featured"> Featured Listing</label><br>
                <label><input type="checkbox" name="pool"> Pool</label><br>
                <label><input type="checkbox" name="waterfront"> Waterfront</label><br>
            </div>
            <div class="col">
                <label><input type="checkbox" name="fireplace" checked> Fireplace</label><br>
                <label><input type="checkbox" name="basement"> Basement</label><br>
                <label><input type="checkbox" name="central_air"> Central Air</label><br>
            </div>
        </div>
        
        <h2>Inclusions & Exclusions</h2>
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label>Inclusions</label>
                    <textarea name="inclusions">WN9500DF Generator</textarea>
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label>Exclusions</label>
                    <textarea name="exclusions">Furniture</textarea>
                </div>
            </div>
        </div>
        
        <h2>Description</h2>
        <div class="form-group">
            <label>Property Description</label>
            <textarea name="description" class="description">Welcome to 21222 Cool Spring Road in Milton, Delaware masterfully crafted by local WN Builders in 2022. This 4-bedroom, 3-bathroom home offers over 2,900 square feet of carefully refined living space on a tranquil 1.45-acre lot, surrounded by mature trees with no HOA and no restrictions! Inside, the open-concept design is bathed in natural light, showcasing a chef's kitchen with granite countertops, stainless steel appliances, convenient walk-in pantry with extra cabinetry, and a generous dining room perfect for hosting. The stunning sunroom, complete with a temperature-controlled gas fireplace, provides an escape to relaxation like no other. Relaxation is only increased by the included hot tub on the patio just steps outside. The first-floor primary suite boasts a luxurious bathroom offering its own water closet, & standalone soaking tub. You'll also find a beautiful walk-in custom closet to provide you with both function and style. Step outside to the professionally curated lawn complete with a built-in irrigation system and marvel at the 24x40 fully insulated pole barn with a 12 high door and an internal RV plug so you can say goodbye to paying for storage for your RVs, boats, or other toys! Across the street is the Woodridge neighborhood - perfect for getting your steps in! Located just minutes from downtown Milton and Long Neck, you can enjoy shopping, historic architecture, and local events all year long. Plus, with Rehoboth and Lewes Beaches only a short drive away, you never have to worry about having nothing to do! Other incredible features include a separate private guest suite, a whole house generator, a whole house surge protector, and an attached 2-car garage. With low property taxes and no HOA, what are you waiting for?</textarea>
        </div>
        
        <div class="form-group">
            <button type="submit" name="create_listing" class="btn">Create Listing</button>
        </div>
    </form>
    
    <hr>
    <h2>Quick Instructions</h2>
    <p><strong>To use this tool:</strong></p>
    <ol>
        <li>Fill in the form above with your MLS data</li>
        <li>Click "Create Listing" to generate the WordPress post</li>
        <li>The listing will be created with all ACF fields populated</li>
        <li>You can then edit the listing in WordPress admin if needed</li>
    </ol>
    
    <p><strong>All ACF fields will be automatically populated including:</strong></p>
    <ul>
        <li>Address components (street number, name, type, city, state, zip)</li>
        <li>Property details (price, beds, baths, square feet, etc.)</li>
        <li>Features (garage, fireplace, pool, etc.)</li>
        <li>Construction details (builder, style, condition, etc.)</li>
        <li>Utilities and systems information</li>
    </ul>
</body>
</html>
