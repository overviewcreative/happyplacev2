<?php
/**
 * Sample Data Generator for Testing
 * 
 * Creates realistic sample agents and listings for dashboard testing
 */

if (!defined('ABSPATH')) {
    exit;
}

class Happy_Place_Sample_Data_Generator {
    
    public function __construct() {
        add_action('wp_ajax_hph_generate_sample_data', [$this, 'ajax_generate_sample_data']);
        add_action('wp_ajax_hph_cleanup_sample_data', [$this, 'ajax_cleanup_sample_data']);
    }
    
    /**
     * AJAX handler for generating sample data
     */
    public function ajax_generate_sample_data() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }
        
        if (!wp_verify_nonce($_POST['nonce'], 'hph_sample_data')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        try {
            $result = $this->generate_all_sample_data();
            wp_send_json_success($result);
        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Error generating sample data: ' . $e->getMessage()]);
        }
    }
    
    /**
     * AJAX handler for cleaning up sample data
     */
    public function ajax_cleanup_sample_data() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }
        
        if (!wp_verify_nonce($_POST['nonce'], 'hph_sample_data')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }
        
        try {
            $result = $this->cleanup_sample_data();
            wp_send_json_success($result);
        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Error cleaning up sample data: ' . $e->getMessage()]);
        }
    }
    
    private $agent_names = [
        ['first' => 'Sarah', 'last' => 'Johnson', 'email' => 'sarah.johnson@happyplace.com'],
        ['first' => 'Michael', 'last' => 'Chen', 'email' => 'michael.chen@happyplace.com'],
        ['first' => 'Emily', 'last' => 'Rodriguez', 'email' => 'emily.rodriguez@happyplace.com'],
        ['first' => 'David', 'last' => 'Thompson', 'email' => 'david.thompson@happyplace.com'],
        ['first' => 'Jessica', 'last' => 'Williams', 'email' => 'jessica.williams@happyplace.com'],
        ['first' => 'Robert', 'last' => 'Davis', 'email' => 'robert.davis@happyplace.com'],
        ['first' => 'Amanda', 'last' => 'Brown', 'email' => 'amanda.brown@happyplace.com'],
        ['first' => 'Christopher', 'last' => 'Wilson', 'email' => 'christopher.wilson@happyplace.com'],
        ['first' => 'Lauren', 'last' => 'Garcia', 'email' => 'lauren.garcia@happyplace.com'],
        ['first' => 'Mark', 'last' => 'Anderson', 'email' => 'mark.anderson@happyplace.com']
    ];
    
    private $property_addresses = [
        ['street' => '1245 Maple Avenue', 'city' => 'Austin', 'state' => 'TX', 'zip' => '78701', 'lat' => 30.2672, 'lng' => -97.7431],
        ['street' => '892 Oak Street', 'city' => 'Austin', 'state' => 'TX', 'zip' => '78702', 'lat' => 30.2849, 'lng' => -97.7341],
        ['street' => '3456 Pine Boulevard', 'city' => 'Austin', 'state' => 'TX', 'zip' => '78703', 'lat' => 30.2711, 'lng' => -97.7694],
        ['street' => '789 Cedar Lane', 'city' => 'Austin', 'state' => 'TX', 'zip' => '78704', 'lat' => 30.2500, 'lng' => -97.7594],
        ['street' => '2134 Elm Drive', 'city' => 'Austin', 'state' => 'TX', 'zip' => '78705', 'lat' => 30.2849, 'lng' => -97.7431],
        ['street' => '567 Birch Court', 'city' => 'Round Rock', 'state' => 'TX', 'zip' => '78681', 'lat' => 30.5083, 'lng' => -97.6789],
        ['street' => '890 Willow Way', 'city' => 'Cedar Park', 'state' => 'TX', 'zip' => '78613', 'lat' => 30.5050, 'lng' => -97.8203],
        ['street' => '1567 Hickory Hill', 'city' => 'Pflugerville', 'state' => 'TX', 'zip' => '78660', 'lat' => 30.4394, 'lng' => -97.6200],
        ['street' => '4321 Sunset Ridge', 'city' => 'Lake Travis', 'state' => 'TX', 'zip' => '78734', 'lat' => 30.3577, 'lng' => -97.9178],
        ['street' => '8765 River Bend', 'city' => 'Westlake', 'state' => 'TX', 'zip' => '78746', 'lat' => 30.2711, 'lng' => -97.8375],
        ['street' => '2468 Mountain View', 'city' => 'Dripping Springs', 'state' => 'TX', 'zip' => '78620', 'lat' => 30.1905, 'lng' => -98.0867],
        ['street' => '1357 Valley Vista', 'city' => 'Bee Cave', 'state' => 'TX', 'zip' => '78738', 'lat' => 30.3077, 'lng' => -97.9475],
        ['street' => '9876 Lakeside Drive', 'city' => 'Lakeway', 'state' => 'TX', 'zip' => '78734', 'lat' => 30.3632, 'lng' => -97.9364],
        ['street' => '5432 Garden Lane', 'city' => 'Georgetown', 'state' => 'TX', 'zip' => '78628', 'lat' => 30.6332, 'lng' => -97.6779],
        ['street' => '6789 Creek Side', 'city' => 'Leander', 'state' => 'TX', 'zip' => '78641', 'lat' => 30.5788, 'lng' => -97.8536],
        ['street' => '3210 Heritage Park', 'city' => 'Buda', 'state' => 'TX', 'zip' => '78610', 'lat' => 30.0855, 'lng' => -97.8394],
        ['street' => '7890 Meadow Brook', 'city' => 'Kyle', 'state' => 'TX', 'zip' => '78640', 'lat' => 30.0005, 'lng' => -97.8772],
        ['street' => '4567 Forest Glen', 'city' => 'San Marcos', 'state' => 'TX', 'zip' => '78666', 'lat' => 29.8833, 'lng' => -97.9414],
        ['street' => '1111 Riverside Terrace', 'city' => 'Austin', 'state' => 'TX', 'zip' => '78741', 'lat' => 30.2338, 'lng' => -97.7147],
        ['street' => '2222 Highland Vista', 'city' => 'Austin', 'state' => 'TX', 'zip' => '78731', 'lat' => 30.3638, 'lng' => -97.7544]
    ];
    
    private $property_features = [
        'hardwood_floors', 'granite_countertops', 'stainless_steel_appliances', 'walk_in_closet', 
        'master_suite', 'fireplace', 'updated_kitchen', 'open_floor_plan', 'vaulted_ceilings',
        'crown_molding', 'tile_flooring', 'breakfast_bar', 'pantry', 'laundry_room', 'garage',
        'covered_patio', 'fenced_yard', 'mature_trees', 'sprinkler_system', 'security_system'
    ];
    
    private $specialties = [
        'buyer_agent', 'listing_agent', 'first_time_buyers', 'luxury_homes', 'commercial',
        'investment', 'condos', 'new_construction', 'relocation', 'foreclosure'
    ];
    
    private $languages = ['english', 'spanish', 'french', 'german', 'chinese'];
    
    private $created_agents = [];
    
    /**
     * Generate all sample data
     */
    public function generate_all_sample_data() {
        // First create agents
        $this->generate_sample_agents();
        
        // Then create listings
        $this->generate_sample_listings();
        
        return [
            'agents_created' => count($this->created_agents),
            'listings_created' => 20,
            'message' => 'Sample data generated successfully!'
        ];
    }
    
    /**
     * Generate sample agents
     */
    private function generate_sample_agents() {
        foreach ($this->agent_names as $index => $agent_data) {
            $username = strtolower($agent_data['first'] . '.' . $agent_data['last']);
            
            // Create WordPress user
            $user_id = wp_create_user(
                $username,
                'password123', // Simple password for testing
                $agent_data['email']
            );
            
            if (is_wp_error($user_id)) {
                continue; // Skip if user creation fails
            }
            
            // Add agent role
            $user = new WP_User($user_id);
            $user->set_role('agent');
            
            // Create agent post
            $agent_post_data = [
                'post_title' => $agent_data['first'] . ' ' . $agent_data['last'],
                'post_type' => 'agent',
                'post_status' => 'publish',
                'post_content' => $this->generate_agent_bio($agent_data['first'], $agent_data['last'])
            ];
            
            $agent_id = wp_insert_post($agent_post_data);
            
            if (!$agent_id || is_wp_error($agent_id)) {
                continue;
            }
            
            // Add agent meta data
            $this->add_agent_meta_data($agent_id, $user_id, $agent_data, $index);
            
            $this->created_agents[] = $agent_id;
        }
    }
    
    /**
     * Add comprehensive agent meta data
     */
    private function add_agent_meta_data($agent_id, $user_id, $agent_data, $index) {
        $years_experience = rand(2, 25);
        $start_date = date('Y-m-d', strtotime("-{$years_experience} years"));
        
        $agent_fields = [
            // WordPress user connection
            'wordpress_user_id' => $user_id,
            
            // Basic Information
            'first_name' => $agent_data['first'],
            'last_name' => $agent_data['last'],
            'display_name' => $agent_data['first'] . ' ' . $agent_data['last'],
            'title' => $this->get_random_title(),
            'short_bio' => $this->generate_short_bio($agent_data['first']),
            'full_bio' => $this->generate_agent_bio($agent_data['first'], $agent_data['last']),
            
            // Contact Information
            'email' => $agent_data['email'],
            'phone' => $this->generate_phone_number(),
            'mobile_phone' => $this->generate_phone_number(),
            'office_phone' => $this->generate_phone_number(),
            'website_url' => 'https://' . strtolower($agent_data['first'] . $agent_data['last']) . '.realtor.com',
            
            // Professional Details
            'license_number' => 'TX' . rand(100000, 999999),
            'license_state' => 'TX',
            'license_expiration' => date('Y-m-d', strtotime('+2 years')),
            'date_started' => $start_date,
            'years_experience' => $years_experience,
            'office_name' => 'Happy Place Realty',
            
            // Specialties and Languages
            'specialties' => $this->get_random_specialties(),
            'languages' => $this->get_random_languages(),
            
            // Social Media
            'facebook_url' => 'https://facebook.com/' . strtolower($agent_data['first'] . $agent_data['last']),
            'instagram_url' => 'https://instagram.com/' . strtolower($agent_data['first'] . $agent_data['last']),
            'linkedin_url' => 'https://linkedin.com/in/' . strtolower($agent_data['first'] . '-' . $agent_data['last']),
            'twitter_url' => 'https://twitter.com/' . strtolower($agent_data['first'] . $agent_data['last']),
            
            // Performance Metrics (simulated)
            'total_sales_volume' => rand(5000000, 50000000),
            'active_listings_count' => rand(5, 25),
            'sold_listings_count' => rand(20, 200),
            'average_dom' => rand(15, 45),
            'agent_rating' => number_format(rand(40, 50) / 10, 1)
        ];
        
        foreach ($agent_fields as $key => $value) {
            update_field($key, $value, $agent_id);
        }
    }
    
    /**
     * Generate sample listings
     */
    private function generate_sample_listings() {
        $statuses = ['active', 'pending', 'sold', 'coming_soon', 'withdrawn'];
        $property_types = ['Single Family', 'Condo', 'Townhouse', 'Multi-Family', 'Land'];
        
        for ($i = 0; $i < 20; $i++) {
            $address_data = $this->property_addresses[$i];
            $agent_id = !empty($this->created_agents) ? $this->created_agents[array_rand($this->created_agents)] : null;
            
            // Create listing post
            $listing_post_data = [
                'post_title' => $address_data['street'] . ', ' . $address_data['city'],
                'post_type' => 'listing',
                'post_status' => 'publish',
                'post_content' => $this->generate_listing_description($address_data)
            ];
            
            $listing_id = wp_insert_post($listing_post_data);
            
            if (!$listing_id || is_wp_error($listing_id)) {
                continue;
            }
            
            // Add listing meta data
            $this->add_listing_meta_data($listing_id, $address_data, $agent_id, $statuses, $property_types);
        }
    }
    
    /**
     * Add comprehensive listing meta data
     */
    private function add_listing_meta_data($listing_id, $address_data, $agent_id, $statuses, $property_types) {
        $bedrooms = rand(2, 6);
        $bathrooms = rand(1, 4) + (rand(0, 1) * 0.5); // Include half baths
        $square_feet = rand(1200, 4500);
        $price = $this->calculate_realistic_price($square_feet, $address_data['city']);
        $year_built = rand(1950, 2023);
        
        $listing_fields = [
            // Basic Property Information
            'price' => $price,
            'property_status' => $statuses[array_rand($statuses)],
            'mls_number' => 'MLS' . rand(1000000, 9999999),
            'year_built' => $year_built,
            'lot_size' => number_format(rand(5000, 25000) / 1000, 2),
            'square_feet' => $square_feet,
            'bedrooms' => $bedrooms,
            'bathrooms' => $bathrooms,
            'property_type' => $property_types[array_rand($property_types)],
            
            // Location Information
            'street_address' => $address_data['street'],
            'city' => $address_data['city'],
            'state' => $address_data['state'],
            'zip_code' => $address_data['zip'],
            'county' => 'Travis County',
            'latitude' => $address_data['lat'],
            'longitude' => $address_data['lng'],
            
            // Features & Amenities
            'property_features' => $this->get_random_features(),
            'garage_spaces' => rand(0, 3),
            'has_pool' => rand(0, 1) ? 'Yes' : 'No',
            'has_spa' => rand(0, 1) ? 'Yes' : 'No',
            'parking_spaces' => rand(1, 4),
            'stories' => rand(1, 3),
            
            // Relationships
            'listing_agent' => $agent_id,
            
            // Financial Information
            'hoa_fees' => rand(0, 1) ? rand(50, 500) : 0,
            'property_taxes' => round($price * 0.015), // Approximate 1.5% tax rate
            'buyer_agent_commission' => 3.0,
            
            // Additional Details
            'listing_date' => date('Y-m-d', strtotime('-' . rand(1, 180) . ' days')),
            'virtual_tour_url' => 'https://virtualtour.example.com/' . $listing_id,
            'school_district' => $this->get_school_district($address_data['city']),
            
            // Tracking
            'view_count' => rand(15, 250),
            'inquiry_count' => rand(2, 25),
            'days_on_market' => rand(1, 120)
        ];
        
        foreach ($listing_fields as $key => $value) {
            update_field($key, $value, $listing_id);
        }
        
        // Set featured image (placeholder)
        $this->set_placeholder_featured_image($listing_id);
    }
    
    /**
     * Generate realistic agent bio
     */
    private function generate_agent_bio($first_name, $last_name) {
        $bios = [
            "With over a decade of experience in the Austin real estate market, {$first_name} {$last_name} brings unparalleled expertise and dedication to every transaction. Specializing in luxury homes and first-time buyers, {$first_name} has helped hundreds of families find their perfect home in the Austin area.",
            
            "{$first_name} {$last_name} is a top-producing agent with a passion for helping clients navigate the competitive Austin real estate market. With extensive knowledge of local neighborhoods and market trends, {$first_name} provides personalized service and expert guidance throughout the buying and selling process.",
            
            "As a lifelong Austin resident, {$first_name} {$last_name} has an intimate knowledge of the local market and community. {$first_name} combines this local expertise with cutting-edge marketing strategies to deliver exceptional results for both buyers and sellers.",
            
            "{$first_name} {$last_name} is dedicated to providing exceptional real estate services with a focus on communication, integrity, and results. Whether you're buying your first home or selling a luxury property, {$first_name} ensures a smooth and successful transaction."
        ];
        
        return $bios[array_rand($bios)];
    }
    
    /**
     * Generate short bio for agent
     */
    private function generate_short_bio($first_name) {
        $short_bios = [
            "Experienced Austin realtor specializing in luxury homes and first-time buyers.",
            "Top-producing agent with 10+ years serving the greater Austin area.",
            "Dedicated to exceptional service and outstanding results for every client.",
            "Local expert helping families find their perfect home in Austin.",
            "Passionate about real estate with a commitment to client satisfaction."
        ];
        
        return $short_bios[array_rand($short_bios)];
    }
    
    /**
     * Generate listing description
     */
    private function generate_listing_description($address_data) {
        $descriptions = [
            "Beautiful home located in the heart of {$address_data['city']}. This stunning property features an open floor plan, updated kitchen with granite countertops, and spacious bedrooms. The backyard is perfect for entertaining with a large deck and mature landscaping.",
            
            "Don't miss this incredible opportunity to own a piece of {$address_data['city']}! This well-maintained home boasts hardwood floors throughout, a gourmet kitchen, and a luxurious master suite. Located in a quiet neighborhood with excellent schools nearby.",
            
            "Charming home in desirable {$address_data['city']} location. Features include vaulted ceilings, fireplace, updated bathrooms, and a two-car garage. The private backyard offers a peaceful retreat with established gardens and a covered patio.",
            
            "Move-in ready home in {$address_data['city']} with modern updates throughout. The spacious living areas flow seamlessly together, perfect for both daily living and entertaining. Close to shopping, dining, and major highways for easy commuting."
        ];
        
        return $descriptions[array_rand($descriptions)];
    }
    
    /**
     * Calculate realistic price based on square footage and location
     */
    private function calculate_realistic_price($square_feet, $city) {
        $price_per_sqft = [
            'Austin' => rand(250, 450),
            'Round Rock' => rand(180, 280),
            'Cedar Park' => rand(200, 320),
            'Pflugerville' => rand(170, 250),
            'Lake Travis' => rand(400, 600),
            'Westlake' => rand(500, 800),
            'Dripping Springs' => rand(300, 450),
            'Bee Cave' => rand(350, 500),
            'Lakeway' => rand(350, 500),
            'Georgetown' => rand(160, 240),
            'Leander' => rand(170, 250),
            'Buda' => rand(200, 300),
            'Kyle' => rand(190, 280),
            'San Marcos' => rand(150, 220)
        ];
        
        $base_price_per_sqft = $price_per_sqft[$city] ?? 200;
        return round(($square_feet * $base_price_per_sqft) / 1000) * 1000; // Round to nearest thousand
    }
    
    /**
     * Get random property features
     */
    private function get_random_features() {
        $num_features = rand(5, 12);
        $selected_features = array_rand($this->property_features, $num_features);
        
        if (!is_array($selected_features)) {
            $selected_features = [$selected_features];
        }
        
        return array_map(function($index) {
            return $this->property_features[$index];
        }, $selected_features);
    }
    
    /**
     * Get random specialties for agent
     */
    private function get_random_specialties() {
        $num_specialties = rand(3, 6);
        $selected = array_rand($this->specialties, $num_specialties);
        
        if (!is_array($selected)) {
            $selected = [$selected];
        }
        
        return array_map(function($index) {
            return $this->specialties[$index];
        }, $selected);
    }
    
    /**
     * Get random languages for agent
     */
    private function get_random_languages() {
        $num_languages = rand(1, 3);
        $selected = array_rand($this->languages, $num_languages);
        
        if (!is_array($selected)) {
            $selected = [$selected];
        }
        
        return array_map(function($index) {
            return $this->languages[$index];
        }, $selected);
    }
    
    /**
     * Generate phone number
     */
    private function generate_phone_number() {
        return '(' . rand(200, 999) . ') ' . rand(200, 999) . '-' . rand(1000, 9999);
    }
    
    /**
     * Get random professional title
     */
    private function get_random_title() {
        $titles = [
            'Senior Real Estate Agent',
            'Real Estate Broker',
            'Associate Broker',
            'Real Estate Consultant',
            'Luxury Home Specialist',
            'Buyer\'s Agent',
            'Listing Specialist',
            'Real Estate Professional'
        ];
        
        return $titles[array_rand($titles)];
    }
    
    /**
     * Get school district based on city
     */
    private function get_school_district($city) {
        $districts = [
            'Austin' => 'Austin ISD',
            'Round Rock' => 'Round Rock ISD',
            'Cedar Park' => 'Leander ISD',
            'Pflugerville' => 'Pflugerville ISD',
            'Georgetown' => 'Georgetown ISD',
            'Leander' => 'Leander ISD'
        ];
        
        return $districts[$city] ?? 'Austin ISD';
    }
    
    /**
     * Set placeholder featured image for listing
     */
    private function set_placeholder_featured_image($listing_id) {
        // Create a simple placeholder image or use a default one
        // For now, we'll just add a comment about where real images would go
        update_post_meta($listing_id, '_thumbnail_placeholder', 'true');
    }
    
    /**
     * Clean up all sample data
     */
    public function cleanup_sample_data() {
        // Delete sample listings
        $listings = get_posts([
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);
        
        foreach ($listings as $listing_id) {
            wp_delete_post($listing_id, true);
        }
        
        // Delete sample agents
        $agents = get_posts([
            'post_type' => 'agent',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);
        
        foreach ($agents as $agent_id) {
            wp_delete_post($agent_id, true);
        }
        
        // Delete sample users (agents)
        $users = get_users(['role' => 'agent']);
        foreach ($users as $user) {
            if (strpos($user->user_email, '@happyplace.com') !== false) {
                wp_delete_user($user->ID);
            }
        }
        
        return [
            'listings_deleted' => count($listings),
            'agents_deleted' => count($agents),
            'users_deleted' => count($users),
            'message' => 'Sample data cleaned up successfully!'
        ];
    }
}

// Add admin page for sample data generation
add_action('admin_menu', function() {
    add_submenu_page(
        'happy-place',
        'Sample Data Generator',
        'Sample Data',
        'manage_options',
        'happy-place-sample-data',
        'happy_place_sample_data_page'
    );
});

function happy_place_sample_data_page() {
    ?>
    <div class="wrap">
        <h1>Sample Data Generator</h1>
        <p>Generate realistic sample data for testing the dashboard functionality.</p>
        
        
        <div class="card" style="max-width: 600px;">
            <h2>Generate Sample Data</h2>
            <p>This will create:</p>
            <ul>
                <li><strong>10 Sample Agents</strong> - With realistic profiles, contact info, and specialties</li>
                <li><strong>20 Sample Listings</strong> - With varied prices, locations, and features</li>
                <li><strong>WordPress Users</strong> - Linked to agents with proper roles</li>
            </ul>
            
            <div id="sample-data-controls">
                <button type="button" id="generate-sample-data" class="button button-primary button-large">
                    Generate Sample Data
                </button>
                
                <button type="button" id="cleanup-sample-data" class="button button-secondary" style="margin-left: 10px;">
                    Clean Up Sample Data
                </button>
            </div>
            
            <div id="sample-data-status" style="margin-top: 20px;"></div>
        </div>
        
        <div class="card" style="max-width: 600px; margin-top: 20px;">
            <h3>Sample User Credentials</h3>
            <p>After generating sample data, you can log in as any agent using:</p>
            <ul>
                <li><strong>Username:</strong> firstname.lastname (e.g., sarah.johnson)</li>
                <li><strong>Password:</strong> password123</li>
            </ul>
            <p><em>Note: Change passwords for production use!</em></p>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        const $generateBtn = $('#generate-sample-data');
        const $cleanupBtn = $('#cleanup-sample-data');
        const $status = $('#sample-data-status');
        
        $generateBtn.on('click', function() {
            $generateBtn.prop('disabled', true).text('Generating...');
            $status.html('<div class="notice notice-info"><p>Generating sample data, please wait...</p></div>');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_generate_sample_data',
                    nonce: '<?php echo wp_create_nonce('hph_sample_data'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $status.html(
                            '<div class="notice notice-success">' +
                            '<p><strong>Success!</strong> ' + response.data.message + '</p>' +
                            '<ul>' +
                            '<li>Agents created: ' + response.data.agents_created + '</li>' +
                            '<li>Listings created: ' + response.data.listings_created + '</li>' +
                            '</ul>' +
                            '</div>'
                        );
                    } else {
                        $status.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                    }
                },
                error: function() {
                    $status.html('<div class="notice notice-error"><p>An error occurred while generating sample data.</p></div>');
                },
                complete: function() {
                    $generateBtn.prop('disabled', false).text('Generate Sample Data');
                }
            });
        });
        
        $cleanupBtn.on('click', function() {
            if (!confirm('Are you sure you want to delete all sample data? This cannot be undone.')) {
                return;
            }
            
            $cleanupBtn.prop('disabled', true).text('Cleaning up...');
            $status.html('<div class="notice notice-info"><p>Cleaning up sample data, please wait...</p></div>');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_cleanup_sample_data',
                    nonce: '<?php echo wp_create_nonce('hph_sample_data'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $status.html(
                            '<div class="notice notice-success">' +
                            '<p><strong>Cleanup Complete!</strong> ' + response.data.message + '</p>' +
                            '<ul>' +
                            '<li>Listings deleted: ' + response.data.listings_deleted + '</li>' +
                            '<li>Agents deleted: ' + response.data.agents_deleted + '</li>' +
                            '<li>Users deleted: ' + response.data.users_deleted + '</li>' +
                            '</ul>' +
                            '</div>'
                        );
                    } else {
                        $status.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                    }
                },
                error: function() {
                    $status.html('<div class="notice notice-error"><p>An error occurred while cleaning up sample data.</p></div>');
                },
                complete: function() {
                    $cleanupBtn.prop('disabled', false).text('Clean Up Sample Data');
                }
            });
        });
    });
    </script>
    <?php
}

// Initialize the sample data generator
new Happy_Place_Sample_Data_Generator();
?>