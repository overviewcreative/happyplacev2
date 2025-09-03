Follow Up Boss Integration Implementation
1. Field Mapping
Your table fields map well to Follow Up Boss's contact schema:
phpfunction map_lead_to_fub($lead) {
    return [
        // Core contact fields
        'firstName' => $lead['first_name'],
        'lastName' => $lead['last_name'],
        'emails' => [
            ['value' => $lead['email']]
        ],
        'phones' => [
            ['value' => $lead['phone']]
        ],
        
        // Additional fields
        'message' => $lead['message'],
        'source' => $lead['source'] ?? 'website',
        'sourceUrl' => $lead['source_url'],
        
        // Property interest (from listing_id)
        'propertyAddress' => get_listing_address($lead['listing_id']),
        'propertyUrl' => get_permalink($lead['listing_id']),
        
        // Lead metadata
        'tags' => explode(',', $lead['tags']),
        'leadScore' => $lead['lead_score'],
        
        // UTM tracking
        'utmSource' => $lead['utm_source'],
        'utmMedium' => $lead['utm_medium'],
        'utmCampaign' => $lead['utm_campaign'],
        
        // Custom fields for FUB
        'customFields' => [
            'referrer' => $lead['referrer'],
            'ipAddress' => $lead['ip_address'],
            'agentId' => $lead['agent_id'],
            'priority' => $lead['priority'],
            'status' => $lead['status']
        ]
    ];
}
2. Real-time Lead Sync
phpclass FollowUpBossIntegration {
    private $api_key;
    private $api_url = 'https://api.followupboss.com/v1/';
    
    public function __construct() {
        $this->api_key = get_option('fub_api_key');
    }
    
    /**
     * Send lead to Follow Up Boss when created
     */
    public function send_lead($lead_data) {
        // Map your database fields to FUB format
        $fub_data = [
            'source' => $lead_data['source'] ?? 'Website',
            'type' => 'General Inquiry',
            'firstName' => $lead_data['first_name'],
            'lastName' => $lead_data['last_name'],
            'emails' => [
                ['value' => $lead_data['email']]
            ],
            'phones' => [
                ['value' => $lead_data['phone']]
            ],
            'message' => $this->build_message($lead_data),
            'propertyUrl' => $this->get_property_url($lead_data['listing_id']),
            'assignedTo' => $this->get_agent_email($lead_data['agent_id']),
            
            // Custom fields
            'customFields' => [
                'leadScore' => $lead_data['lead_score'],
                'priority' => $lead_data['priority'],
                'utmSource' => $lead_data['utm_source'],
                'utmMedium' => $lead_data['utm_medium'],
                'utmCampaign' => $lead_data['utm_campaign']
            ]
        ];
        
        // Send to FUB Events API
        $response = wp_remote_post($this->api_url . 'events', [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->api_key . ':'),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'body' => json_encode($fub_data),
            'timeout' => 30
        ]);
        
        if (!is_wp_error($response)) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            
            // Store FUB ID back in your database
            if (isset($body['id'])) {
                $this->update_lead_fub_id($lead_data['id'], $body['id']);
            }
            
            return $body;
        }
        
        return false;
    }
    
    /**
     * Build detailed message for FUB
     */
    private function build_message($lead_data) {
        $message = $lead_data['message'] ?? '';
        
        // Add property details if viewing a listing
        if ($lead_data['listing_id']) {
            $listing = get_post($lead_data['listing_id']);
            $message .= "\n\nInterested in Property: " . get_the_title($lead_data['listing_id']);
            $message .= "\nPrice: $" . number_format(get_field('listing_price', $lead_data['listing_id']));
            $message .= "\nMLS#: " . get_field('mls_number', $lead_data['listing_id']);
        }
        
        // Add source information
        if ($lead_data['source_url']) {
            $message .= "\n\nSource Page: " . $lead_data['source_url'];
        }
        
        return $message;
    }
}
3. Hook into Lead Creation
php// Trigger FUB sync when lead is created
add_action('lead_created', function($lead_id) {
    global $wpdb;
    
    // Get lead data from your table
    $lead = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}leads WHERE id = %d",
        $lead_id
    ), ARRAY_A);
    
    if ($lead) {
        $fub = new FollowUpBossIntegration();
        $result = $fub->send_lead($lead);
        
        // Log the result
        if ($result) {
            error_log('Lead sent to FUB successfully: ' . $lead_id);
        } else {
            error_log('Failed to send lead to FUB: ' . $lead_id);
        }
    }
});
4. Webhook Handler for FUB Updates
php// Create endpoint to receive FUB webhooks
add_action('rest_api_init', function() {
    register_rest_route('fub/v1', '/webhook', [
        'methods' => 'POST',
        'callback' => 'handle_fub_webhook',
        'permission_callback' => 'verify_fub_webhook'
    ]);
});

function handle_fub_webhook($request) {
    $data = $request->get_json_params();
    
    // Update lead status based on FUB events
    if ($data['event'] === 'contact.updated') {
        global $wpdb;
        
        // Find lead by email
        $lead = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}leads WHERE email = %s",
            $data['contact']['emails'][0]['value']
        ));
        
        if ($lead) {
            // Update lead status
            $wpdb->update(
                $wpdb->prefix . 'leads',
                [
                    'status' => map_fub_stage_to_status($data['contact']['stage']),
                    'updated_at' => current_time('mysql')
                ],
                ['id' => $lead->id]
            );
        }
    }
    
    return new WP_REST_Response(['success' => true], 200);
}
5. Bulk Import Existing Leads
phpfunction bulk_import_leads_to_fub() {
    global $wpdb;
    
    // Get all leads not yet synced
    $leads = $wpdb->get_results(
        "SELECT * FROM {$wpdb->prefix}leads 
         WHERE last_contacted IS NULL 
         ORDER BY created_at DESC 
         LIMIT 100",
        ARRAY_A
    );
    
    $fub = new FollowUpBossIntegration();
    $batch = [];
    
    foreach ($leads as $lead) {
        $batch[] = $fub->map_lead_to_fub($lead);
        
        // Send in batches of 50
        if (count($batch) >= 50) {
            $fub->send_batch($batch);
            $batch = [];
            sleep(1); // Rate limiting
        }
    }
    
    // Send remaining
    if (!empty($batch)) {
        $fub->send_batch($batch);
    }
}
6. Settings Page for Configuration
php// Add admin page for FUB settings
add_action('admin_menu', function() {
    add_submenu_page(
        'options-general.php',
        'Follow Up Boss Integration',
        'FUB Integration',
        'manage_options',
        'fub-integration',
        'render_fub_settings_page'
    );
});

function render_fub_settings_page() {
    ?>
    <div class="wrap">
        <h1>Follow Up Boss Integration</h1>
        <form method="post" action="options.php">
            <?php settings_fields('fub_settings'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">API Key</th>
                    <td>
                        <input type="text" 
                               name="fub_api_key" 
                               value="<?php echo get_option('fub_api_key'); ?>" 
                               class="regular-text" />
                        <p class="description">
                            Get your API key from FUB Settings > API
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Auto Sync</th>
                    <td>
                        <label>
                            <input type="checkbox" 
                                   name="fub_auto_sync" 
                                   value="1" 
                                   <?php checked(get_option('fub_auto_sync'), 1); ?> />
                            Automatically send new leads to Follow Up Boss
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Lead Source</th>
                    <td>
                        <input type="text" 
                               name="fub_lead_source" 
                               value="<?php echo get_option('fub_lead_source', 'Website'); ?>" 
                               class="regular-text" />
                        <p class="description">
                            Default source name for leads sent to FUB
                        </p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
        
        <hr>
        
        <h2>Test Connection</h2>
        <button class="button" onclick="testFubConnection()">Test API Connection</button>
        <div id="test-result"></div>
        
        <h2>Sync Status</h2>
        <?php
        global $wpdb;
        $total = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}leads");
        $synced = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}leads WHERE last_contacted IS NOT NULL");
        ?>
        <p>Total Leads: <?php echo $total; ?></p>
        <p>Synced to FUB: <?php echo $synced; ?></p>
        
        <?php if ($total > $synced): ?>
            <button class="button" onclick="syncAllLeads()">Sync Remaining Leads</button>
        <?php endif; ?>
    </div>
    <?php
}
7. Required Database Additions
To fully support FUB integration, consider adding these fields to your leads table:
sqlALTER TABLE wp_leads 
ADD COLUMN fub_contact_id VARCHAR(50) NULL AFTER last_contacted,
ADD COLUMN fub_sync_status VARCHAR(20) DEFAULT 'pending',
ADD COLUMN fub_last_sync DATETIME NULL,
ADD INDEX idx_fub_contact (fub_contact_id),
ADD INDEX idx_fub_sync (fub_sync_status);
Key Integration Points:

Real-time sync: Leads are sent to FUB immediately upon creation
Field mapping: Your fields map cleanly to FUB's schema
Property details: Listing information is included when available
Agent assignment: Uses agent_id to assign in FUB
Lead scoring: Your lead_score field can be passed to FUB
UTM tracking: All UTM parameters are preserved
Two-way sync: Webhooks can update your local database

The integration will work smoothly with your existing table structure. The main fields (name, email, phone, message) map directly, and additional fields like lead_score, UTM parameters, and agent_id can be passed as custom fields or used for routing/assignment in Follow Up Boss.RetryPHow could this integrate with users? They can sign in to save their favorites, searches, etc.EditSearched project for “user registration login favorites saved searches authentication member”5 resultsadvanced-filters.php2 relevant sectionstextsearch-autocomplete.php2 relevant sectionstextcomponents/atoms/avatar.csstextlayout/header.css2 relevant sectionstextcard.phptextSearched project for “WP user account profile dashboard my account favorites saved”7 resultsfeatures/dashboard/dashboard-layout.csstextfeatures/listing/listing-card.csstextutilities/spacing.csstextadvanced-filters.php2 relevant sectionstextarchive-controls.phptextsearch-autocomplete.phptextlayout/grid.csstextExcellent question! Let me show you how to integrate user accounts with your leads and listings system for a comprehensive user experience. This will create a seamless connection between leads, registered users, favorites, saved searches, and personalized features.
Complete User Integration Architecture
1. Enhanced Database Schema
First, let's extend your leads table and create supporting tables:
sql-- Update leads table to link with WordPress users
ALTER TABLE wp_leads 
ADD COLUMN user_id BIGINT(20) UNSIGNED NULL AFTER id,
ADD COLUMN conversion_date DATETIME NULL AFTER last_contacted,
ADD COLUMN account_status VARCHAR(20) DEFAULT 'guest',
ADD INDEX idx_user_id (user_id),
ADD FOREIGN KEY (user_id) REFERENCES wp_users(ID) ON DELETE SET NULL;

-- User favorites table
CREATE TABLE wp_user_favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT(20) UNSIGNED NOT NULL,
    listing_id BIGINT(20) UNSIGNED NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    rating INT(1),
    UNIQUE KEY user_listing (user_id, listing_id),
    INDEX idx_user (user_id),
    INDEX idx_listing (listing_id),
    FOREIGN KEY (user_id) REFERENCES wp_users(ID) ON DELETE CASCADE,
    FOREIGN KEY (listing_id) REFERENCES wp_posts(ID) ON DELETE CASCADE
);

-- Saved searches table
CREATE TABLE wp_saved_searches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT(20) UNSIGNED NOT NULL,
    search_name VARCHAR(100),
    search_criteria JSON,
    frequency VARCHAR(20) DEFAULT 'daily', -- instant, daily, weekly
    last_sent DATETIME,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_active (is_active, frequency),
    FOREIGN KEY (user_id) REFERENCES wp_users(ID) ON DELETE CASCADE
);

-- User viewing history
CREATE TABLE wp_viewing_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT(20) UNSIGNED NOT NULL,
    listing_id BIGINT(20) UNSIGNED NOT NULL,
    view_count INT DEFAULT 1,
    last_viewed DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    time_spent INT DEFAULT 0, -- seconds
    source VARCHAR(50), -- search, email, direct, etc.
    UNIQUE KEY user_listing (user_id, listing_id),
    INDEX idx_user_viewed (user_id, last_viewed),
    FOREIGN KEY (user_id) REFERENCES wp_users(ID) ON DELETE CASCADE
);

-- User preferences/profile extension
CREATE TABLE wp_user_preferences (
    user_id BIGINT(20) UNSIGNED PRIMARY KEY,
    preferred_locations JSON,
    property_types JSON,
    price_range_min INT,
    price_range_max INT,
    min_bedrooms INT,
    min_bathrooms DECIMAL(3,1),
    min_square_feet INT,
    must_have_features JSON,
    communication_preferences JSON,
    lead_score INT DEFAULT 0,
    buyer_status VARCHAR(50), -- looking, under_contract, closed, not_active
    timeline VARCHAR(50), -- asap, 1_3_months, 3_6_months, 6_12_months, just_browsing
    pre_approved BOOLEAN DEFAULT FALSE,
    assigned_agent_id INT,
    FOREIGN KEY (user_id) REFERENCES wp_users(ID) ON DELETE CASCADE
);
2. Lead to User Conversion System
phpclass LeadUserConversion {
    
    /**
     * Convert a lead to a registered user
     */
    public function convert_lead_to_user($lead_id, $password = null) {
        global $wpdb;
        
        // Get lead data
        $lead = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}leads WHERE id = %d",
            $lead_id
        ), ARRAY_A);
        
        if (!$lead) {
            return new WP_Error('lead_not_found', 'Lead not found');
        }
        
        // Check if user already exists
        if (email_exists($lead['email'])) {
            $user_id = email_exists($lead['email']);
            
            // Link lead to existing user
            $wpdb->update(
                $wpdb->prefix . 'leads',
                [
                    'user_id' => $user_id,
                    'account_status' => 'existing_user',
                    'conversion_date' => current_time('mysql')
                ],
                ['id' => $lead_id]
            );
            
            return $user_id;
        }
        
        // Create new user
        $userdata = [
            'user_login' => $lead['email'],
            'user_email' => $lead['email'],
            'user_pass' => $password ?: wp_generate_password(),
            'first_name' => $lead['first_name'],
            'last_name' => $lead['last_name'],
            'display_name' => $lead['first_name'] . ' ' . $lead['last_name'],
            'role' => 'subscriber'
        ];
        
        $user_id = wp_insert_user($userdata);
        
        if (!is_wp_error($user_id)) {
            // Update lead record
            $wpdb->update(
                $wpdb->prefix . 'leads',
                [
                    'user_id' => $user_id,
                    'account_status' => 'converted',
                    'conversion_date' => current_time('mysql')
                ],
                ['id' => $lead_id]
            );
            
            // Set user meta
            update_user_meta($user_id, 'phone', $lead['phone']);
            update_user_meta($user_id, 'lead_source', $lead['source']);
            update_user_meta($user_id, 'original_lead_id', $lead_id);
            update_user_meta($user_id, 'lead_score', $lead['lead_score']);
            
            // Initialize user preferences
            $this->initialize_user_preferences($user_id, $lead);
            
            // Send welcome email
            $this->send_welcome_email($user_id, $password);
            
            // Sync with Follow Up Boss
            do_action('user_converted_from_lead', $user_id, $lead_id);
        }
        
        return $user_id;
    }
    
    /**
     * Initialize user preferences based on lead activity
     */
    private function initialize_user_preferences($user_id, $lead) {
        global $wpdb;
        
        // Extract preferences from lead's listing interest
        $preferences = [
            'user_id' => $user_id,
            'lead_score' => $lead['lead_score'] ?: 0,
            'communication_preferences' => json_encode([
                'email' => true,
                'sms' => !empty($lead['phone']),
                'frequency' => 'immediate'
            ])
        ];
        
        // If lead was viewing a specific listing, extract preferences
        if ($lead['listing_id']) {
            $listing = get_post($lead['listing_id']);
            if ($listing) {
                $preferences['preferred_locations'] = json_encode([
                    get_field('city', $lead['listing_id'])
                ]);
                $preferences['property_types'] = json_encode([
                    get_field('property_type', $lead['listing_id'])
                ]);
                $preferences['price_range_max'] = get_field('listing_price', $lead['listing_id']) * 1.2;
                $preferences['price_range_min'] = get_field('listing_price', $lead['listing_id']) * 0.8;
            }
        }
        
        $wpdb->insert($wpdb->prefix . 'user_preferences', $preferences);
    }
}
3. User Dashboard Components
php// User Dashboard Template
// template-user-dashboard.php

<?php
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Get user stats
$favorites_count = get_user_favorites_count($user_id);
$saved_searches_count = get_user_saved_searches_count($user_id);
$viewing_history = get_user_viewing_history($user_id, 10);
?>

<div class="hph-dashboard">
    <!-- Sidebar Navigation -->
    <aside class="hph-dashboard-sidebar">
        <div class="hph-user-profile">
            <?php echo get_avatar($user_id, 80); ?>
            <h3><?php echo esc_html($current_user->display_name); ?></h3>
            <p><?php echo esc_html($current_user->user_email); ?></p>
        </div>
        
        <nav class="hph-dashboard-nav">
            <ul>
                <li><a href="#overview" class="active">
                    <i class="fas fa-home"></i> Overview
                </a></li>
                <li><a href="#favorites">
                    <i class="fas fa-heart"></i> Saved Properties 
                    <span class="badge"><?php echo $favorites_count; ?></span>
                </a></li>
                <li><a href="#searches">
                    <i class="fas fa-search"></i> Saved Searches
                    <span class="badge"><?php echo $saved_searches_count; ?></span>
                </a></li>
                <li><a href="#history">
                    <i class="fas fa-clock"></i> Viewing History
                </a></li>
                <li><a href="#preferences">
                    <i class="fas fa-sliders-h"></i> Preferences
                </a></li>
                <li><a href="#messages">
                    <i class="fas fa-envelope"></i> Messages
                </a></li>
            </ul>
        </nav>
    </aside>
    
    <!-- Main Content -->
    <main class="hph-dashboard-content">
        <!-- Overview Section -->
        <section id="overview" class="hph-dashboard-section">
            <h2>Welcome back, <?php echo esc_html($current_user->first_name); ?>!</h2>
            
            <!-- Quick Stats -->
            <div class="hph-stats-grid">
                <div class="hph-stat-card">
                    <i class="fas fa-heart"></i>
                    <div class="stat-content">
                        <h4><?php echo $favorites_count; ?></h4>
                        <p>Saved Properties</p>
                    </div>
                </div>
                <div class="hph-stat-card">
                    <i class="fas fa-bell"></i>
                    <div class="stat-content">
                        <h4><?php echo count(get_new_listings_for_user($user_id)); ?></h4>
                        <p>New Matches</p>
                    </div>
                </div>
                <div class="hph-stat-card">
                    <i class="fas fa-calendar"></i>
                    <div class="stat-content">
                        <h4><?php echo count(get_user_scheduled_showings($user_id)); ?></h4>
                        <p>Scheduled Showings</p>
                    </div>
                </div>
            </div>
            
            <!-- Recommended Properties -->
            <?php
            $recommendations = get_personalized_recommendations($user_id, 4);
            if ($recommendations) : ?>
            <div class="hph-recommendations">
                <h3>Recommended For You</h3>
                <div class="hph-listings-grid">
                    <?php foreach ($recommendations as $listing_id) : ?>
                        <?php hph_component('listing-card', ['listing_id' => $listing_id]); ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </section>
        
        <!-- Favorites Section -->
        <section id="favorites" class="hph-dashboard-section" style="display: none;">
            <h2>Saved Properties</h2>
            <?php echo do_shortcode('[user_favorites]'); ?>
        </section>
        
        <!-- Saved Searches Section -->
        <section id="searches" class="hph-dashboard-section" style="display: none;">
            <h2>Saved Searches</h2>
            <?php echo do_shortcode('[saved_searches]'); ?>
        </section>
    </main>
</div>
4. Favorites System
phpclass UserFavoritesManager {
    
    /**
     * Toggle favorite status
     */
    public function toggle_favorite($listing_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        if (!$user_id) {
            return ['success' => false, 'message' => 'Please login to save favorites'];
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'user_favorites';
        
        // Check if already favorited
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d AND listing_id = %d",
            $user_id, $listing_id
        ));
        
        if ($existing) {
            // Remove favorite
            $wpdb->delete($table, [
                'user_id' => $user_id,
                'listing_id' => $listing_id
            ]);
            
            do_action('listing_unfavorited', $listing_id, $user_id);
            
            return ['success' => true, 'action' => 'removed'];
        } else {
            // Add favorite
            $wpdb->insert($table, [
                'user_id' => $user_id,
                'listing_id' => $listing_id,
                'created_at' => current_time('mysql')
            ]);
            
            do_action('listing_favorited', $listing_id, $user_id);
            
            // Update lead score if user came from a lead
            $this->update_lead_score($user_id, 5);
            
            return ['success' => true, 'action' => 'added'];
        }
    }
    
    /**
     * Get user's favorite listings
     */
    public function get_user_favorites($user_id, $limit = -1) {
        global $wpdb;
        
        $sql = "SELECT l.*, f.created_at as favorited_date, f.notes, f.rating
                FROM {$wpdb->prefix}user_favorites f
                JOIN {$wpdb->posts} l ON f.listing_id = l.ID
                WHERE f.user_id = %d
                AND l.post_status = 'publish'
                ORDER BY f.created_at DESC";
        
        if ($limit > 0) {
            $sql .= " LIMIT $limit";
        }
        
        return $wpdb->get_results($wpdb->prepare($sql, $user_id));
    }
}

// AJAX handler for favorites
add_action('wp_ajax_toggle_favorite', function() {
    check_ajax_referer('listing_favorite_nonce');
    
    $listing_id = intval($_POST['listing_id']);
    $manager = new UserFavoritesManager();
    $result = $manager->toggle_favorite($listing_id);
    
    wp_send_json($result);
});
5. Saved Searches with Alerts
phpclass SavedSearchesManager {
    
    /**
     * Save a search for the user
     */
    public function save_search($user_id, $search_data) {
        global $wpdb;
        
        $data = [
            'user_id' => $user_id,
            'search_name' => $search_data['name'] ?: 'My Search ' . date('M j'),
            'search_criteria' => json_encode($search_data['criteria']),
            'frequency' => $search_data['frequency'] ?: 'daily',
            'is_active' => true
        ];
        
        $wpdb->insert($wpdb->prefix . 'saved_searches', $data);
        
        return $wpdb->insert_id;
    }
    
    /**
     * Check for new matches and send alerts
     */
    public function check_saved_search_matches() {
        global $wpdb;
        
        // Get active saved searches
        $searches = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}saved_searches 
             WHERE is_active = 1 
             AND (
                (frequency = 'instant') OR
                (frequency = 'daily' AND DATE(last_sent) < CURDATE()) OR
                (frequency = 'weekly' AND last_sent < DATE_SUB(NOW(), INTERVAL 7 DAY))
             )"
        );
        
        foreach ($searches as $search) {
            $criteria = json_decode($search->search_criteria, true);
            
            // Build WP_Query from saved criteria
            $query_args = $this->build_query_from_criteria($criteria);
            
            // Add date filter to get only new listings
            $query_args['date_query'] = [
                'after' => $search->last_sent ?: '1 week ago'
            ];
            
            $new_listings = new WP_Query($query_args);
            
            if ($new_listings->have_posts()) {
                // Send alert email
                $this->send_search_alert($search, $new_listings->posts);
                
                // Update last sent
                $wpdb->update(
                    $wpdb->prefix . 'saved_searches',
                    ['last_sent' => current_time('mysql')],
                    ['id' => $search->id]
                );
            }
        }
    }
    
    /**
     * Send search alert email
     */
    private function send_search_alert($search, $listings) {
        $user = get_user_by('id', $search->user_id);
        
        ob_start();
        ?>
        <h2>New Properties Matching Your Search</h2>
        <p>Hi <?php echo $user->first_name; ?>,</p>
        <p>We found <?php echo count($listings); ?> new properties matching your saved search "<?php echo $search->search_name; ?>":</p>
        
        <div style="margin: 20px 0;">
            <?php foreach ($listings as $listing) : ?>
                <div style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px;">
                    <h3><?php echo get_the_title($listing->ID); ?></h3>
                    <p>Price: $<?php echo number_format(get_field('listing_price', $listing->ID)); ?></p>
                    <p><?php echo get_field('bedrooms', $listing->ID); ?> beds • 
                       <?php echo get_field('bathrooms_full', $listing->ID); ?> baths • 
                       <?php echo get_field('square_feet', $listing->ID); ?> sq ft</p>
                    <a href="<?php echo get_permalink($listing->ID); ?>" 
                       style="background: #007cba; color: white; padding: 10px 20px; text-decoration: none; display: inline-block;">
                        View Property
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        
        <p><a href="<?php echo home_url('/my-account/saved-searches'); ?>">Manage your saved searches</a></p>
        <?php
        $html = ob_get_clean();
        
        wp_mail(
            $user->user_email,
            'New Properties Matching Your Search',
            $html,
            ['Content-Type: text/html; charset=UTF-8']
        );
    }
}

// Schedule cron job for search alerts
add_action('init', function() {
    if (!wp_next_scheduled('check_saved_searches')) {
        wp_schedule_event(time(), 'hourly', 'check_saved_searches');
    }
});

add_action('check_saved_searches', function() {
    $manager = new SavedSearchesManager();
    $manager->check_saved_search_matches();
});
6. Frontend Integration
javascript// User interactions JavaScript
class UserListingInteractions {
    constructor() {
        this.initFavorites();
        this.initSaveSearch();
        this.trackViewing();
    }
    
    initFavorites() {
        document.querySelectorAll('.favorite-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                
                if (!userLoggedIn) {
                    // Show login modal or redirect
                    this.showLoginPrompt('Sign in to save your favorite properties');
                    return;
                }
                
                const listingId = btn.dataset.listingId;
                
                try {
                    const response = await fetch(ajaxurl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            action: 'toggle_favorite',
                            listing_id: listingId,
                            _ajax_nonce: listing_nonce
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        btn.classList.toggle('is-favorited');
                        
                        // Update button icon and text
                        const icon = btn.querySelector('i');
                        if (result.action === 'added') {
                            icon.classList.remove('far');
                            icon.classList.add('fas');
                            this.showNotification('Property saved to favorites!');
                        } else {
                            icon.classList.remove('fas');
                            icon.classList.add('far');
                            this.showNotification('Property removed from favorites');
                        }
                        
                        // Update favorites count in header
                        this.updateFavoritesCount(result.action);
                    }
                } catch (error) {
                    console.error('Error toggling favorite:', error);
                }
            });
        });
    }
    
    initSaveSearch() {
        const saveSearchBtn = document.getElementById('save-search');
        if (!saveSearchBtn) return;
        
        saveSearchBtn.addEventListener('click', async () => {
            if (!userLoggedIn) {
                this.showLoginPrompt('Sign in to save searches and get alerts');
                return;
            }
            
            // Get current search parameters
            const searchParams = new URLSearchParams(window.location.search);
            const criteria = Object.fromEntries(searchParams);
            
            // Show save search modal
            const modal = this.createSaveSearchModal(criteria);
            document.body.appendChild(modal);
        });
    }
    
    trackViewing() {
        if (!userLoggedIn) return;
        
        const listingId = document.querySelector('[data-listing-id]')?.dataset.listingId;
        if (!listingId) return;
        
        // Track page view time
        const startTime = Date.now();
        
        window.addEventListener('beforeunload', () => {
            const timeSpent = Math.round((Date.now() - startTime) / 1000);
            
            // Send beacon to track viewing
            navigator.sendBeacon(ajaxurl, new URLSearchParams({
                action: 'track_viewing',
                listing_id: listingId,
                time_spent: timeSpent,
                _ajax_nonce: tracking_nonce
            }));
        });
    }
    
    showLoginPrompt(message) {
        const modal = document.createElement('div');
        modal.className = 'login-prompt-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <h3>Sign In Required</h3>
                <p>${message}</p>
                <div class="modal-actions">
                    <a href="/wp-login.php?redirect_to=${encodeURIComponent(window.location.href)}" 
                       class="btn btn-primary">Sign In</a>
                    <a href="/register?redirect_to=${encodeURIComponent(window.location.href)}" 
                       class="btn btn-secondary">Create Account</a>
                    <button class="btn-text" onclick="this.closest('.login-prompt-modal').remove()">
                        Maybe Later
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    new UserListingInteractions();
});
7. Lead Scoring & User Engagement Tracking
phpclass UserEngagementTracker {
    
    private $scoring_actions = [
        'registration' => 10,
        'profile_complete' => 5,
        'favorite_added' => 5,
        'search_saved' => 8,
        'showing_requested' => 15,
        'contact_agent' => 12,
        'document_downloaded' => 7,
        'mortgage_calculator' => 6,
        'property_shared' => 4,
        'email_opened' => 2,
        'email_clicked' => 3
    ];
    
    /**
     * Update user's lead score based on action
     */
    public function track_action($user_id, $action, $metadata = []) {
        global $wpdb;
        
        // Get current score
        $current_score = (int) get_user_meta($user_id, 'lead_score', true);
        
        // Add points for action
        $points = $this->scoring_actions[$action] ?? 0;
        $new_score = $current_score + $points;
        
        // Update user meta
        update_user_meta($user_id, 'lead_score', $new_score);
        
        // Log the activity
        $wpdb->insert($wpdb->prefix . 'user_activity_log', [
            'user_id' => $user_id,
            'action' => $action,
            'points' => $points,
            'metadata' => json_encode($metadata),
            'created_at' => current_time('mysql')
        ]);
        
        // Check for score milestones
        $this->check_score_milestones($user_id, $new_score);
        
        // Sync with CRM
        do_action('user_engagement_tracked', $user_id, $action, $new_score);
        
        return $new_score;
    }
    
    /**
     * Check for lead score milestones and trigger actions
     */
    private function check_score_milestones($user_id, $score) {
        // Hot lead threshold
        if ($score >= 50 && get_user_meta($user_id, 'hot_lead_notified', true) !== 'yes') {
            $this->notify_agent_hot_lead($user_id);
            update_user_meta($user_id, 'hot_lead_notified', 'yes');
        }
        
        // Ready to buy threshold  
        if ($score >= 75) {
            update_user_meta($user_id, 'buyer_status', 'ready_to_buy');
        }
    }
}
This comprehensive integration creates a seamless experience where:

Leads automatically convert to users when they register
User behavior is tracked across all interactions
Personalized recommendations based on viewing history and preferences
Saved searches with automated alerts for new matches
Lead scoring that updates based on user engagement
Full sync with Follow Up Boss for both leads and registered users
Dashboard for users to manage their property search journey

The system maintains full backward compatibility with your existing leads table while adding powerful user account features that enhance engagement and conversion rates.