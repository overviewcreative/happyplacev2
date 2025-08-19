<?php
/**
 * Sample Data Generator
 * Creates test data for development and testing purposes
 *
 * @package HappyPlace\Utilities
 */

namespace HappyPlace\Utilities;

if (!defined('ABSPATH')) {
    exit;
}

class Sample_Data_Generator {

    private static $instance = null;
    
    /**
     * Sample agent names and details
     */
    private $sample_agents = [
        [
            'first_name' => 'Sarah',
            'last_name' => 'Johnson',
            'title' => 'Senior Real Estate Agent',
            'email' => 'sarah@happyplace.com',
            'phone' => '(555) 123-4567',
            'specialties' => ['Luxury Homes', 'First-Time Buyers'],
            'years_experience' => 8
        ],
        [
            'first_name' => 'Michael',
            'last_name' => 'Chen',
            'title' => 'Listing Specialist',
            'email' => 'michael@happyplace.com',
            'phone' => '(555) 234-5678',
            'specialties' => ['Investment Properties', 'Commercial'],
            'years_experience' => 12
        ],
        [
            'first_name' => 'Jessica',
            'last_name' => 'Williams',
            'title' => 'Buyer\'s Agent',
            'email' => 'jessica@happyplace.com',
            'phone' => '(555) 345-6789',
            'specialties' => ['Condominiums', 'Downtown Living'],
            'years_experience' => 5
        ]
    ];

    /**
     * Sample listing data
     */
    private $sample_listings = [
        [
            'title' => 'Modern Downtown Condo with City Views',
            'price' => 450000,
            'bedrooms' => 2,
            'bathrooms' => 2,
            'square_feet' => 1200,
            'property_type' => 'Condominium',
            'listing_status' => 'active',
            'street_address' => '123 Main Street',
            'city' => 'Austin',
            'state' => 'TX',
            'zip_code' => '78701',
            'year_built' => 2018
        ],
        [
            'title' => 'Luxury Family Home in Westlake',
            'price' => 1250000,
            'bedrooms' => 4,
            'bathrooms' => 3,
            'square_feet' => 3200,
            'property_type' => 'Single Family',
            'listing_status' => 'active',
            'street_address' => '456 Oak Hill Drive',
            'city' => 'Austin',
            'state' => 'TX',
            'zip_code' => '78746',
            'year_built' => 2015
        ],
        [
            'title' => 'Charming Bungalow in East Austin',
            'price' => 650000,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'square_feet' => 1800,
            'property_type' => 'Single Family',
            'listing_status' => 'pending',
            'street_address' => '789 Cedar Lane',
            'city' => 'Austin',
            'state' => 'TX',
            'zip_code' => '78702',
            'year_built' => 1965
        ]
    ];

    /**
     * Sample communities
     */
    private $sample_communities = [
        [
            'title' => 'Westlake Hills',
            'community_type' => 'master_planned',
            'price_range_min' => 800000,
            'price_range_max' => 2500000,
            'amenities' => ['pool', 'tennis_court', 'clubhouse', 'walking_trails'],
            'school_district' => 'Eanes Independent School District'
        ],
        [
            'title' => 'The Domain',
            'community_type' => 'urban_district',
            'price_range_min' => 350000,
            'price_range_max' => 1200000,
            'amenities' => ['fitness_center', 'pool', 'concierge', 'business_center'],
            'school_district' => 'Austin Independent School District'
        ]
    ];

    /**
     * Sample leads
     */
    private $sample_leads = [
        [
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'john.smith@example.com',
            'phone' => '(555) 111-2222',
            'source' => 'website',
            'status' => 'new',
            'budget_min' => 400000,
            'budget_max' => 600000,
            'timeline' => '3_6_months',
            'message' => 'Looking for a 3-bedroom home in Central Austin with good schools.'
        ],
        [
            'first_name' => 'Emily',
            'last_name' => 'Davis',
            'email' => 'emily.davis@example.com',
            'phone' => '(555) 333-4444',
            'source' => 'referral',
            'status' => 'qualified',
            'budget_min' => 800000,
            'budget_max' => 1500000,
            'timeline' => '1_3_months',
            'message' => 'First-time buyer looking for luxury condo downtown.'
        ]
    ];

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Constructor
    }

    /**
     * Generate all sample data
     */
    public function generate_all_sample_data($force_regenerate = false) {
        $results = [
            'agents' => 0,
            'listings' => 0,
            'communities' => 0,
            'leads' => 0,
            'errors' => []
        ];

        try {
            // Check if sample data already exists
            if (!$force_regenerate && $this->sample_data_exists()) {
                $results['message'] = 'Sample data already exists. Use force_regenerate to recreate.';
                return $results;
            }

            // Generate agents first (needed for listings)
            $agent_ids = $this->generate_sample_agents();
            $results['agents'] = count($agent_ids);

            // Generate communities
            $community_ids = $this->generate_sample_communities();
            $results['communities'] = count($community_ids);

            // Generate listings (assign to agents)
            $listing_ids = $this->generate_sample_listings($agent_ids, $community_ids);
            $results['listings'] = count($listing_ids);

            // Generate leads (assign to agents and listings)
            $lead_ids = $this->generate_sample_leads($agent_ids, $listing_ids);
            $results['leads'] = count($lead_ids);

            hp_log('Sample data generation completed', 'info', 'SAMPLE_DATA');

        } catch (Exception $e) {
            $results['errors'][] = $e->getMessage();
            hp_log('Sample data generation error: ' . $e->getMessage(), 'error', 'SAMPLE_DATA');
        }

        return $results;
    }

    /**
     * Check if sample data already exists
     */
    private function sample_data_exists() {
        $existing_agents = get_posts([
            'post_type' => 'agent',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => '_sample_data',
                    'value' => '1',
                    'compare' => '='
                ]
            ]
        ]);

        return !empty($existing_agents);
    }

    /**
     * Generate sample agents
     */
    public function generate_sample_agents() {
        $agent_ids = [];

        foreach ($this->sample_agents as $agent_data) {
            // Create agent post
            $agent_id = wp_insert_post([
                'post_title' => $agent_data['first_name'] . ' ' . $agent_data['last_name'],
                'post_type' => 'agent',
                'post_status' => 'publish',
                'post_content' => 'Sample agent created for testing purposes.',
            ]);

            if (!is_wp_error($agent_id)) {
                // Add ACF fields
                update_field('first_name', $agent_data['first_name'], $agent_id);
                update_field('last_name', $agent_data['last_name'], $agent_id);
                update_field('title', $agent_data['title'], $agent_id);
                update_field('email', $agent_data['email'], $agent_id);
                update_field('phone', $agent_data['phone'], $agent_id);
                update_field('years_experience', $agent_data['years_experience'], $agent_id);
                
                if (isset($agent_data['specialties'])) {
                    update_field('specialties', $agent_data['specialties'], $agent_id);
                }

                // Mark as sample data
                update_post_meta($agent_id, '_sample_data', '1');
                
                $agent_ids[] = $agent_id;
                hp_log("Created sample agent: {$agent_data['first_name']} {$agent_data['last_name']} (ID: $agent_id)", 'info', 'SAMPLE_DATA');
            }
        }

        return $agent_ids;
    }

    /**
     * Generate sample listings
     */
    public function generate_sample_listings($agent_ids = [], $community_ids = []) {
        if (empty($agent_ids)) {
            $agent_ids = $this->get_existing_agent_ids();
        }

        $listing_ids = [];

        foreach ($this->sample_listings as $listing_data) {
            // Create listing post
            $listing_id = wp_insert_post([
                'post_title' => $listing_data['title'],
                'post_type' => 'listing',
                'post_status' => 'publish',
                'post_content' => 'Sample listing created for testing purposes. This is a beautiful property with modern amenities and great location.',
            ]);

            if (!is_wp_error($listing_id)) {
                // Add ACF fields
                foreach ($listing_data as $key => $value) {
                    if ($key !== 'title') {
                        update_field($key, $value, $listing_id);
                    }
                }

                // Assign random agent
                if (!empty($agent_ids)) {
                    $random_agent = $agent_ids[array_rand($agent_ids)];
                    update_field('listing_agent', $random_agent, $listing_id);
                }

                // Assign random community if available
                if (!empty($community_ids)) {
                    $random_community = $community_ids[array_rand($community_ids)];
                    update_field('community', $random_community, $listing_id);
                }

                // Mark as sample data
                update_post_meta($listing_id, '_sample_data', '1');
                
                $listing_ids[] = $listing_id;
                hp_log("Created sample listing: {$listing_data['title']} (ID: $listing_id)", 'info', 'SAMPLE_DATA');
            }
        }

        return $listing_ids;
    }

    /**
     * Generate sample communities
     */
    public function generate_sample_communities() {
        $community_ids = [];

        foreach ($this->sample_communities as $community_data) {
            // Create community post
            $community_id = wp_insert_post([
                'post_title' => $community_data['title'],
                'post_type' => 'community',
                'post_status' => 'publish',
                'post_content' => 'Sample community created for testing purposes. This community offers excellent amenities and great location.',
            ]);

            if (!is_wp_error($community_id)) {
                // Add ACF fields
                foreach ($community_data as $key => $value) {
                    if ($key !== 'title') {
                        update_field($key, $value, $community_id);
                    }
                }

                // Mark as sample data
                update_post_meta($community_id, '_sample_data', '1');
                
                $community_ids[] = $community_id;
                hp_log("Created sample community: {$community_data['title']} (ID: $community_id)", 'info', 'SAMPLE_DATA');
            }
        }

        return $community_ids;
    }

    /**
     * Generate sample leads
     */
    public function generate_sample_leads($agent_ids = [], $listing_ids = []) {
        if (empty($agent_ids)) {
            $agent_ids = $this->get_existing_agent_ids();
        }

        $lead_ids = [];

        foreach ($this->sample_leads as $lead_data) {
            // Create lead post
            $lead_id = wp_insert_post([
                'post_title' => $lead_data['first_name'] . ' ' . $lead_data['last_name'] . ' - Lead',
                'post_type' => 'lead',
                'post_status' => 'publish',
                'post_content' => 'Sample lead created for testing purposes.',
            ]);

            if (!is_wp_error($lead_id)) {
                // Add ACF fields
                foreach ($lead_data as $key => $value) {
                    update_field($key, $value, $lead_id);
                }

                // Assign random agent
                if (!empty($agent_ids)) {
                    $random_agent = $agent_ids[array_rand($agent_ids)];
                    update_field('assigned_agent', $random_agent, $lead_id);
                }

                // Assign random interested property if available
                if (!empty($listing_ids) && rand(0, 1)) {
                    $random_listing = $listing_ids[array_rand($listing_ids)];
                    update_field('interested_property', $random_listing, $lead_id);
                }

                // Mark as sample data
                update_post_meta($lead_id, '_sample_data', '1');
                
                $lead_ids[] = $lead_id;
                hp_log("Created sample lead: {$lead_data['first_name']} {$lead_data['last_name']} (ID: $lead_id)", 'info', 'SAMPLE_DATA');
            }
        }

        return $lead_ids;
    }

    /**
     * Get existing agent IDs
     */
    private function get_existing_agent_ids() {
        $agents = get_posts([
            'post_type' => 'agent',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'post_status' => 'publish'
        ]);

        return $agents;
    }

    /**
     * Clean up all sample data
     */
    public function cleanup_sample_data() {
        $post_types = ['agent', 'listing', 'community', 'lead'];
        $deleted_count = 0;

        foreach ($post_types as $post_type) {
            $sample_posts = get_posts([
                'post_type' => $post_type,
                'posts_per_page' => -1,
                'fields' => 'ids',
                'meta_query' => [
                    [
                        'key' => '_sample_data',
                        'value' => '1',
                        'compare' => '='
                    ]
                ]
            ]);

            foreach ($sample_posts as $post_id) {
                if (wp_delete_post($post_id, true)) {
                    $deleted_count++;
                }
            }
        }

        hp_log("Cleaned up $deleted_count sample data posts", 'info', 'SAMPLE_DATA');
        
        return $deleted_count;
    }

    /**
     * Get sample data statistics
     */
    public function get_sample_data_stats() {
        $post_types = ['agent', 'listing', 'community', 'lead'];
        $stats = [];

        foreach ($post_types as $post_type) {
            $sample_posts = get_posts([
                'post_type' => $post_type,
                'posts_per_page' => -1,
                'fields' => 'ids',
                'meta_query' => [
                    [
                        'key' => '_sample_data',
                        'value' => '1',
                        'compare' => '='
                    ]
                ]
            ]);

            $stats[$post_type] = count($sample_posts);
        }

        return $stats;
    }
}