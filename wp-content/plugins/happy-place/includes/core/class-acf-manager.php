<?php
/**
 * ACF Manager
 * Handles Advanced Custom Fields integration and management
 *
 * @package HappyPlace
 */

namespace HappyPlace\Core;

if (!defined('ABSPATH')) {
    exit;
}

class ACF_Manager {

    private static $instance = null;
    private $field_groups = [];
    private $acf_available = false;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Alias for backwards compatibility
    public static function instance() {
        return self::get_instance();
    }

    private function __construct() {
        // Constructor only sets up the instance
    }

    /**
     * Initialize the ACF Manager
     */
    public function init() {
        $this->check_acf_availability();
        $this->init_hooks();
        hp_log('ACF Manager initialized', 'info', 'ACF_MANAGER');
    }

    private function init_hooks() {
        // Set up ACF JSON sync paths EARLY - these need to be set before ACF fully loads
        add_filter('acf/settings/save_json', [$this, 'acf_json_save_path'], 1);
        add_filter('acf/settings/load_json', [$this, 'acf_json_load_paths'], 1);
        
        // ACF Pro sync integration - ensure our field groups are recognized by sync
        add_filter('acf/json/load_paths', [$this, 'acf_json_load_paths'], 1);
        add_filter('acf/json/save_path', [$this, 'acf_json_save_path'], 1);
        
        // Enable JSON sync for ACF Pro - this is crucial for proper management
        add_action('acf/init', [$this, 'setup_acf_pro_sync'], 1);
        
        // Priority loading - try immediately if ACF is available
        if ($this->acf_available) {
            // ACF is already loaded, initialize immediately
            add_action('init', [$this, 'immediate_field_groups_load'], 15);
        }
        
        // Standard hooks - various fallback attempts
        add_action('acf/init', [$this, 'acf_init'], 5);
        add_action('init', [$this, 'ensure_field_groups_loaded'], 20);
        add_action('admin_init', [$this, 'ensure_field_groups_loaded'], 5);
        add_action('wp_loaded', [$this, 'ensure_field_groups_loaded'], 5);
        add_action('plugins_loaded', [$this, 'ensure_field_groups_loaded'], 20);
        
        // Late fallback - ensure field groups are definitely loaded
        add_action('wp_loaded', [$this, 'final_field_groups_check'], 99);
        
        add_action('acf/include_field_types', [$this, 'include_custom_fields']);
        
        // ACF Pro sync hooks for proper management
        add_action('acf/sync_complete', [$this, 'after_acf_sync'], 10, 2);
        add_action('acf/create_field_group', [$this, 'after_field_group_create'], 10, 1);
        add_action('acf/update_field_group', [$this, 'after_field_group_update'], 10, 1);
        
        // Admin notices
        add_action('admin_notices', [$this, 'acf_admin_notices']);
        
        // Add admin menu item for ACF Pro sync
        add_action('admin_menu', [$this, 'add_acf_sync_admin_menu']);
        
        // Field validation
        add_filter('acf/validate_value', [$this, 'validate_custom_fields'], 10, 4);
        
        // Field formatting
        add_filter('acf/format_value', [$this, 'format_field_values'], 10, 3);
        
        // Save hooks
        add_action('acf/save_post', [$this, 'after_save_post'], 20);
        
        hp_log('ACF Manager hooks initialized with ACF Pro sync integration', 'info', 'ACF_MANAGER');
    }

    private function check_acf_availability() {
        $this->acf_available = class_exists('ACF') || function_exists('acf');
        
        hp_log('ACF availability check - class_exists(ACF): ' . (class_exists('ACF') ? 'true' : 'false'), 'info', 'ACF_MANAGER');
        hp_log('ACF availability check - function_exists(acf): ' . (function_exists('acf') ? 'true' : 'false'), 'info', 'ACF_MANAGER');
        hp_log('ACF availability result: ' . ($this->acf_available ? 'true' : 'false'), 'info', 'ACF_MANAGER');
        
        if (!$this->acf_available) {
            add_action('admin_notices', [$this, 'acf_missing_notice']);
            hp_log('ACF not available - admin notice scheduled', 'warning', 'ACF_MANAGER');
        } else {
            hp_log('ACF is available', 'info', 'ACF_MANAGER');
        }
    }

    /**
     * Setup ACF Pro sync functionality
     */
    public function setup_acf_pro_sync() {
        hp_log('Setting up ACF Pro sync functionality', 'info', 'ACF_MANAGER');
        
        // Ensure our JSON path is writable
        $json_path = $this->acf_json_save_path('');
        if (!is_dir($json_path)) {
            wp_mkdir_p($json_path);
        }
        
        // Make sure ACF Pro recognizes our custom path
        if (function_exists('acf_get_json_path')) {
            $current_paths = acf_get_setting('load_json');
            if (!in_array($json_path, $current_paths)) {
                acf_append_setting('load_json', $json_path);
                hp_log("Added custom JSON path to ACF Pro: {$json_path}", 'info', 'ACF_MANAGER');
            }
        }
    }

    public function acf_init() {
        hp_log('acf_init hook fired', 'info', 'ACF_MANAGER');
        
        if (!$this->acf_available) {
            hp_log('ACF not available during acf_init', 'warning', 'ACF_MANAGER');
            return;
        }

        hp_log('ACF is available, proceeding with initialization', 'info', 'ACF_MANAGER');
        $this->load_field_groups();
        $this->setup_options_pages();
        $this->register_custom_fields();
        $this->verify_field_groups();
    }

    /**
     * Immediate field groups loading for when ACF is already available
     */
    public function immediate_field_groups_load() {
        hp_log('immediate_field_groups_load called - ACF should be available', 'info', 'ACF_MANAGER');
        
        if (function_exists('acf_add_local_field_group')) {
            $this->load_field_groups();
            $this->register_custom_fields();
            $this->verify_field_groups();
            hp_log('Immediate field groups loading completed', 'info', 'ACF_MANAGER');
        } else {
            hp_log('Immediate loading failed - ACF functions not yet available', 'warning', 'ACF_MANAGER');
        }
    }

    /**
     * Final check to ensure field groups are definitely loaded
     */
    public function final_field_groups_check() {
        hp_log('final_field_groups_check - last chance to load field groups', 'info', 'ACF_MANAGER');
        
        if (empty($this->field_groups) && function_exists('acf_add_local_field_group')) {
            hp_log('Final attempt to load field groups', 'warning', 'ACF_MANAGER');
            $this->load_field_groups();
            $this->register_custom_fields();
            $this->verify_field_groups();
        }
        
        // Log final status
        $total_loaded = count($this->field_groups);
        hp_log("Final field groups status: {$total_loaded} groups loaded", 'info', 'ACF_MANAGER');
        
        if ($total_loaded === 0) {
            hp_log('WARNING: No field groups loaded after all attempts!', 'error', 'ACF_MANAGER');
        }
    }

    /**
     * Ensure field groups are loaded - called from multiple hooks as fallback
     */
    public function ensure_field_groups_loaded() {
        // Only run once per request per hook
        static $loaded_from = [];
        $current_hook = current_filter();
        
        if (in_array($current_hook, $loaded_from)) {
            return;
        }
        $loaded_from[] = $current_hook;
        
        hp_log("ensure_field_groups_loaded called from hook: {$current_hook}", 'info', 'ACF_MANAGER');
        
        // Check if ACF is available now
        if (!$this->acf_available) {
            $this->check_acf_availability();
        }
        
        if (!$this->acf_available) {
            hp_log('ACF still not available, skipping field group loading', 'warning', 'ACF_MANAGER');
            return;
        }
        
        // If we have no field groups loaded and ACF functions are available, try to load them
        if (empty($this->field_groups) && function_exists('acf_add_local_field_group')) {
            hp_log('Attempting to load field groups from ensure_field_groups_loaded', 'info', 'ACF_MANAGER');
            $this->load_field_groups();
            $this->register_custom_fields();
            $this->verify_field_groups();
        } else {
            hp_log("Field groups status from {$current_hook}: " . count($this->field_groups) . ' loaded', 'debug', 'ACF_MANAGER');
        }
    }

    /**
     * Set ACF JSON save path
     */
    public function acf_json_save_path($path) {
        $custom_path = HP_PLUGIN_DIR . 'includes/fields/acf-json';
        hp_log("ACF JSON save path set to: {$custom_path}", 'info', 'ACF_MANAGER');
        return $custom_path;
    }

    /**
     * Set ACF JSON load paths (array of paths)
     */
    public function acf_json_load_paths($paths) {
        $custom_path = HP_PLUGIN_DIR . 'includes/fields/acf-json';
        
        // Remove the default path to avoid conflicts
        unset($paths[0]);
        
        // Add our custom path
        $paths[] = $custom_path;
        
        hp_log("ACF JSON load paths: " . implode(', ', $paths), 'info', 'ACF_MANAGER');
        return $paths;
    }

    private function load_field_groups() {
        $field_groups_dir = HP_PLUGIN_DIR . 'includes/fields/acf-json/';
        
        if (!is_dir($field_groups_dir)) {
            hp_log("ACF field groups directory not found: {$field_groups_dir}", 'error', 'ACF_MANAGER');
            return false;
        }

        $field_files = glob($field_groups_dir . '*.json');
        hp_log("Found " . count($field_files) . " ACF field group files", 'info', 'ACF_MANAGER');
        
        if (empty($field_files)) {
            hp_log("No JSON field group files found in directory", 'warning', 'ACF_MANAGER');
            return false;
        }
        
        $loaded_count = 0;
        $registered_count = 0;
        
        foreach ($field_files as $file) {
            $filename = basename($file);
            
            // Skip export files
            if (strpos($filename, 'acf-export-') === 0) {
                continue;
            }
            
            $json_content = file_get_contents($file);
            if ($json_content === false) {
                hp_log("Failed to read file: {$filename}", 'warning', 'ACF_MANAGER');
                continue;
            }
            
            $group_data = json_decode($json_content, true);
            
            if ($group_data && isset($group_data['key'], $group_data['title'])) {
                // Store in our cache
                $this->field_groups[$group_data['key']] = $group_data;
                $loaded_count++;
                
                // Register with ACF immediately if possible
                if (function_exists('acf_add_local_field_group')) {
                    try {
                        acf_add_local_field_group($group_data);
                        $registered_count++;
                        hp_log("Successfully loaded and registered: {$group_data['key']} - {$group_data['title']}", 'info', 'ACF_MANAGER');
                    } catch (Exception $e) {
                        hp_log("Failed to register field group {$group_data['key']}: " . $e->getMessage(), 'error', 'ACF_MANAGER');
                    }
                } else {
                    hp_log("ACF function 'acf_add_local_field_group' not available, field group stored for later: {$group_data['key']}", 'warning', 'ACF_MANAGER');
                }
            } else {
                hp_log("Invalid field group data in file: {$filename} - missing key or title", 'warning', 'ACF_MANAGER');
            }
        }
        
        hp_log("Field group loading complete: {$loaded_count} loaded, {$registered_count} registered with ACF", 'info', 'ACF_MANAGER');
        
        return $loaded_count > 0;
    }

    /**
     * Verify field groups are properly loaded and registered
     */
    private function verify_field_groups() {
        if (!function_exists('acf_get_field_groups')) {
            hp_log('ACF function acf_get_field_groups not available', 'warning', 'ACF_MANAGER');
            return;
        }

        $db_groups = acf_get_field_groups();
        $loaded_groups = $this->field_groups;
        
        hp_log("ACF verification - JSON loaded: " . count($loaded_groups) . ", DB available: " . count($db_groups), 'info', 'ACF_MANAGER');
        
        // Check if our field groups are available in ACF
        $our_groups = [];
        foreach ($db_groups as $group) {
            if (strpos($group['key'], 'group_') === 0 && isset($loaded_groups[$group['key']])) {
                $our_groups[] = $group['key'];
            }
        }
        
        hp_log("Successfully registered field groups: " . implode(', ', $our_groups), 'info', 'ACF_MANAGER');
        
        // Check for any missing groups
        $expected_groups = [
            'group_listing_basic',
            'group_listing_address', 
            'group_listing_features',
            'group_listing_media',
            'group_listing_financial',
            'group_listing_relationships',
            'group_agent_profile',
            'group_community',
            'group_open_house'
        ];
        
        $missing_groups = [];
        foreach ($expected_groups as $expected) {
            if (!in_array($expected, $our_groups)) {
                $missing_groups[] = $expected;
            }
        }
        
        if (!empty($missing_groups)) {
            hp_log("Missing ACF field groups: " . implode(', ', $missing_groups), 'warning', 'ACF_MANAGER');
        } else {
            hp_log("All expected ACF field groups are loaded and registered", 'info', 'ACF_MANAGER');
        }
    }

    private function setup_options_pages() {
        if (!function_exists('acf_add_options_page')) {
            return;
        }

        // Main options page
        acf_add_options_page([
            'page_title' => __('Happy Place Settings', 'happy-place'),
            'menu_title' => __('HP Settings', 'happy-place'),
            'menu_slug' => 'happy-place-settings',
            'capability' => 'manage_options',
            'icon_url' => 'dashicons-admin-home',
            'position' => 58,
        ]);

        // Agent settings
        acf_add_options_sub_page([
            'page_title' => __('Agent Settings', 'happy-place'),
            'menu_title' => __('Agents', 'happy-place'),
            'parent_slug' => 'happy-place-settings',
        ]);

        // Listing settings
        acf_add_options_sub_page([
            'page_title' => __('Listing Settings', 'happy-place'),
            'menu_title' => __('Listings', 'happy-place'),
            'parent_slug' => 'happy-place-settings',
        ]);

        // Marketing settings
        acf_add_options_sub_page([
            'page_title' => __('Marketing Settings', 'happy-place'),
            'menu_title' => __('Marketing', 'happy-place'),
            'parent_slug' => 'happy-place-settings',
        ]);
    }

    private function register_custom_fields() {
        // Register custom field types if needed
        $this->register_price_field();
        $this->register_address_field();
        $this->register_agent_selector_field();
    }

    private function register_price_field() {
        // Custom price field with formatting
        add_filter('acf/render_field/type=number', function($field) {
            if (isset($field['name']) && strpos($field['name'], 'price') !== false) {
                echo '<div class="hpt-price-field-wrapper">';
                echo '<span class="hpt-price-prefix">$</span>';
            }
        }, 9);

        add_filter('acf/render_field/type=number', function($field) {
            if (isset($field['name']) && strpos($field['name'], 'price') !== false) {
                echo '</div>';
                echo '<script>
                    jQuery(document).ready(function($) {
                        $("input[name*=\'price\']").on("input", function() {
                            var val = $(this).val().replace(/,/g, "");
                            if (val && !isNaN(val)) {
                                $(this).val(parseInt(val).toLocaleString());
                            }
                        });
                    });
                </script>';
            }
        }, 11);
    }

    private function register_address_field() {
        // Address field with geocoding
        add_action('acf/render_field/type=text', function($field) {
            if (isset($field['name']) && in_array($field['name'], ['street_address', 'address'])) {
                echo '<script>
                    jQuery(document).ready(function($) {
                        var addressField = $("input[name=\'' . $field['name'] . '\']");
                        var cityField = $("input[name=\'city\']");
                        var stateField = $("select[name=\'state\'], input[name=\'state\']");
                        var zipField = $("input[name=\'zip_code\']");
                        var latField = $("input[name=\'latitude\']");
                        var lngField = $("input[name=\'longitude\']");
                        
                        addressField.on("blur", function() {
                            var address = $(this).val();
                            if (address && window.google && window.google.maps) {
                                var geocoder = new google.maps.Geocoder();
                                geocoder.geocode({"address": address}, function(results, status) {
                                    if (status === "OK" && results[0]) {
                                        var result = results[0];
                                        var location = result.geometry.location;
                                        
                                        if (latField.length) latField.val(location.lat());
                                        if (lngField.length) lngField.val(location.lng());
                                        
                                        // Parse address components
                                        result.address_components.forEach(function(component) {
                                            var types = component.types;
                                            if (types.includes("locality") && cityField.length) {
                                                cityField.val(component.long_name);
                                            }
                                            if (types.includes("administrative_area_level_1") && stateField.length) {
                                                stateField.val(component.short_name);
                                            }
                                            if (types.includes("postal_code") && zipField.length) {
                                                zipField.val(component.long_name);
                                            }
                                        });
                                    }
                                });
                            }
                        });
                    });
                </script>';
            }
        });
    }

    private function register_agent_selector_field() {
        // Enhanced agent selector with user linking
        add_filter('acf/fields/relationship/query/name=listing_agent', function($args, $field, $post_id) {
            // Only show active agents
            $args['meta_query'] = [
                [
                    'key' => 'agent_status',
                    'value' => 'active',
                    'compare' => '='
                ]
            ];
            return $args;
        }, 10, 3);
    }

    public function include_custom_fields($major_version) {
        // Include custom field types
        $custom_fields_dir = HP_PLUGIN_DIR . 'includes/fields/custom/';
        
        if (is_dir($custom_fields_dir)) {
            $custom_fields = glob($custom_fields_dir . '*.php');
            foreach ($custom_fields as $field_file) {
                include_once $field_file;
            }
        }
    }

    public function validate_custom_fields($valid, $value, $field, $input_name) {
        if (!$valid) {
            return $valid;
        }

        // Price validation
        if (isset($field['name']) && strpos($field['name'], 'price') !== false) {
            if ($value && !is_numeric(str_replace(['$', ','], '', $value))) {
                return __('Please enter a valid price', 'happy-place');
            }
        }

        // Email validation
        if ($field['type'] === 'email' && $value && !is_email($value)) {
            return __('Please enter a valid email address', 'happy-place');
        }

        // Phone validation
        if (isset($field['name']) && strpos($field['name'], 'phone') !== false) {
            if ($value && !preg_match('/^[\d\s\-\(\)\+\.]+$/', $value)) {
                return __('Please enter a valid phone number', 'happy-place');
            }
        }

        // Required field validation
        if (isset($field['required']) && $field['required'] && empty($value)) {
            return sprintf(__('%s is required', 'happy-place'), $field['label']);
        }

        return $valid;
    }

    public function format_field_values($value, $post_id, $field) {
        // Format price fields
        if (isset($field['name']) && strpos($field['name'], 'price') !== false && $value) {
            return number_format((float) $value);
        }

        // Format phone fields
        if (isset($field['name']) && strpos($field['name'], 'phone') !== false && $value) {
            return $this->format_phone_number($value);
        }

        // Format square footage
        if (isset($field['name']) && $field['name'] === 'square_feet' && $value) {
            return number_format((int) $value);
        }

        return $value;
    }

    public function after_save_post($post_id) {
        // Skip auto-saves and revisions
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }

        $post_type = get_post_type($post_id);

        switch ($post_type) {
            case 'listing':
                $this->after_save_listing($post_id);
                break;
            case 'agent':
                $this->after_save_agent($post_id);
                break;
        }
    }

    private function after_save_listing($post_id) {
        // Update listing search index
        $this->update_listing_search_index($post_id);
        
        // Clear related caches
        $this->clear_listing_caches($post_id);
        
        // Update agent statistics
        $listing_agent = get_field('listing_agent', $post_id);
        if ($listing_agent) {
            $agent_id = is_array($listing_agent) ? $listing_agent[0]->ID : $listing_agent->ID;
            $this->update_agent_statistics($agent_id);
        }
    }

    private function after_save_agent($post_id) {
        // Update agent search index
        $this->update_agent_search_index($post_id);
        
        // Clear agent caches
        $this->clear_agent_caches($post_id);
        
        // Update user meta if linked
        $wordpress_user = get_field('wordpress_user', $post_id);
        if ($wordpress_user) {
            update_user_meta($wordpress_user, 'hpt_agent_id', $post_id);
        }
    }

    private function update_listing_search_index($post_id) {
        $searchable_content = [];
        
        // Get all text fields
        $fields = ['street_address', 'city', 'state', 'zip_code', 'county', 'school_district'];
        foreach ($fields as $field) {
            $value = get_field($field, $post_id);
            if ($value) {
                $searchable_content[] = $value;
            }
        }
        
        // Get post content
        $post = get_post($post_id);
        if ($post) {
            $searchable_content[] = $post->post_title;
            $searchable_content[] = $post->post_content;
        }
        
        // Store searchable content
        update_post_meta($post_id, '_hpt_search_content', implode(' ', $searchable_content));
    }

    private function update_agent_search_index($post_id) {
        $searchable_content = [];
        
        // Get agent fields
        $fields = ['first_name', 'last_name', 'title', 'specialties', 'languages'];
        foreach ($fields as $field) {
            $value = get_field($field, $post_id);
            if ($value) {
                if (is_array($value)) {
                    $searchable_content = array_merge($searchable_content, $value);
                } else {
                    $searchable_content[] = $value;
                }
            }
        }
        
        // Store searchable content
        update_post_meta($post_id, '_hpt_search_content', implode(' ', $searchable_content));
    }

    private function update_agent_statistics($agent_id) {
        // Count active listings
        $active_listings = get_posts([
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'listing_agent',
                    'value' => '"' . $agent_id . '"',
                    'compare' => 'LIKE'
                ],
                [
                    'key' => 'listing_status',
                    'value' => 'active',
                    'compare' => '='
                ]
            ]
        ]);

        // Count sold listings
        $sold_listings = get_posts([
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'listing_agent',
                    'value' => '"' . $agent_id . '"',
                    'compare' => 'LIKE'
                ],
                [
                    'key' => 'listing_status',
                    'value' => 'sold',
                    'compare' => '='
                ]
            ]
        ]);

        // Calculate total volume
        $total_volume = 0;
        foreach ($sold_listings as $listing_id) {
            $price = get_field('price', $listing_id);
            if ($price) {
                $total_volume += $price;
            }
        }

        // Update agent statistics
        update_field('active_listings_count', count($active_listings), $agent_id);
        update_field('sold_listings_count', count($sold_listings), $agent_id);
        update_field('total_sales_volume', $total_volume, $agent_id);
        update_field('stats_updated', current_time('mysql'), $agent_id);
    }

    private function clear_listing_caches($post_id) {
        // Clear various caches
        wp_cache_delete("listing_data_{$post_id}", 'happy_place');
        wp_cache_delete("listing_gallery_{$post_id}", 'happy_place');
        
        // Clear agent dashboard cache
        $listing_agent = get_field('listing_agent', $post_id);
        if ($listing_agent) {
            $agent_id = is_array($listing_agent) ? $listing_agent[0]->ID : $listing_agent->ID;
            wp_cache_delete("dashboard_stats_{$agent_id}", 'happy_place');
        }
    }

    private function clear_agent_caches($post_id) {
        wp_cache_delete("agent_data_{$post_id}", 'happy_place');
        wp_cache_delete("agent_listings_{$post_id}", 'happy_place');
        wp_cache_delete("dashboard_stats_{$post_id}", 'happy_place');
    }

    private function format_phone_number($phone) {
        // Remove all non-digit characters
        $phone = preg_replace('/\D/', '', $phone);
        
        // Format US phone numbers
        if (strlen($phone) === 10) {
            return sprintf('(%s) %s-%s', 
                substr($phone, 0, 3),
                substr($phone, 3, 3),
                substr($phone, 6, 4)
            );
        }
        
        return $phone;
    }

    public function acf_admin_notices() {
        if (!$this->acf_available) {
            return;
        }

        // Check for missing field groups
        $required_groups = [
            'group_listing_basic',
            'group_agent_profile',
            'group_listing_relationships'
        ];

        $missing_groups = [];
        foreach ($required_groups as $group_key) {
            if (!isset($this->field_groups[$group_key])) {
                $missing_groups[] = $group_key;
            }
        }

        if (!empty($missing_groups)) {
            echo '<div class="notice notice-warning"><p>';
            echo sprintf(
                __('Happy Place: Missing ACF field groups: %s. Please check the field group files.', 'happy-place'),
                implode(', ', $missing_groups)
            );
            echo '</p></div>';
        }
    }

    public function acf_missing_notice() {
        echo '<div class="notice notice-error"><p>';
        echo __('Happy Place Plugin requires Advanced Custom Fields (ACF) to be installed and activated.', 'happy-place');
        echo ' <a href="' . admin_url('plugin-install.php?s=advanced+custom+fields&tab=search&type=term') . '">';
        echo __('Install ACF now', 'happy-place');
        echo '</a></p></div>';
    }

    /**
     * Add ACF Sync admin menu
     */
    public function add_acf_sync_admin_menu() {
        // Only add if ACF Pro is available
        if (!class_exists('ACF_PRO')) {
            return;
        }

        add_submenu_page(
            'edit.php?post_type=acf-field-group',
            'Happy Place Sync',
            'HP Sync',
            'manage_options',
            'happy-place-acf-sync',
            [$this, 'render_acf_sync_admin_page']
        );
    }

    /**
     * Render ACF Sync admin page
     */
    public function render_acf_sync_admin_page() {
        ?>
        <div class="wrap">
            <h1>🔄 Happy Place ACF Sync</h1>
            
            <div class="notice notice-info">
                <p><strong>ACF Pro Sync Management:</strong> Use this tool to sync Happy Place field groups between JSON files and the database.</p>
            </div>

            <?php if (isset($_GET['action'])): ?>
                <div class="notice notice-success">
                    <p><strong>Action completed!</strong> Check the results below.</p>
                </div>
            <?php endif; ?>

            <div class="card">
                <h2>🚀 Quick Actions</h2>
                <p>Choose an action to manage your Happy Place field groups:</p>
                
                <p>
                    <a href="<?php echo admin_url('admin.php?page=happy-place-acf-sync&action=sync_to_db'); ?>" 
                       class="button button-primary">
                        📥 Sync JSON to Database
                    </a>
                    <em>Import JSON field groups into database for ACF Pro editing</em>
                </p>

                <p>
                    <a href="<?php echo admin_url('admin.php?page=happy-place-acf-sync&action=export_to_json'); ?>" 
                       class="button button-secondary">
                        📤 Export Database to JSON
                    </a>
                    <em>Save database changes back to JSON files</em>
                </p>

                <p>
                    <a href="<?php echo HP_PLUGIN_URL; ?>acf-pro-sync.php" 
                       class="button button-secondary" target="_blank">
                        🔧 Advanced Sync Tool
                    </a>
                    <em>Open the full sync management interface</em>
                </p>
            </div>

            <?php if (isset($_GET['action'])): ?>
                <div class="card">
                    <h2>📊 Results</h2>
                    <?php $this->handle_sync_action($_GET['action']); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <h2>📋 Current Status</h2>
                <?php $this->display_field_groups_status(); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Handle sync actions from admin page
     */
    private function handle_sync_action($action) {
        switch ($action) {
            case 'sync_to_db':
                $result = $this->force_json_to_db_sync();
                if ($result['success']) {
                    echo "<p class='notice notice-success'>✅ Successfully synced {$result['synced']} field groups to database</p>";
                } else {
                    echo "<p class='notice notice-error'>❌ Sync failed: {$result['message']}</p>";
                }
                break;

            case 'export_to_json':
                $result = $this->import_db_field_groups_to_json();
                if ($result['success']) {
                    echo "<p class='notice notice-success'>✅ Successfully exported {$result['imported']} field groups to JSON</p>";
                } else {
                    echo "<p class='notice notice-error'>❌ Export failed: {$result['message']}</p>";
                }
                break;
        }
    }

    /**
     * Display current field groups status
     */
    private function display_field_groups_status() {
        if (!function_exists('acf_get_field_groups')) {
            echo "<p>❌ ACF functions not available</p>";
            return;
        }

        $all_groups = acf_get_field_groups();
        $happy_place_groups = array_filter($all_groups, function($group) {
            return strpos($group['key'], 'group_') === 0;
        });

        echo "<p><strong>Happy Place Field Groups:</strong> " . count($happy_place_groups) . "</p>";

        if (!empty($happy_place_groups)) {
            echo "<table class='wp-list-table widefat fixed striped'>";
            echo "<thead><tr><th>Field Group</th><th>Key</th><th>Source</th><th>Status</th></tr></thead>";
            echo "<tbody>";

            foreach ($happy_place_groups as $group) {
                $is_json = isset($group['local']) && $group['local'] === 'json';
                $source = $is_json ? '📁 JSON' : '💾 Database';
                $status = $is_json ? 'Read-only (JSON)' : 'Editable (Database)';
                $status_class = $is_json ? 'notice-info' : 'notice-success';

                echo "<tr>";
                echo "<td><strong>{$group['title']}</strong></td>";
                echo "<td><code>{$group['key']}</code></td>";
                echo "<td>{$source}</td>";
                echo "<td><span class='notice {$status_class} inline'>{$status}</span></td>";
                echo "</tr>";
            }

            echo "</tbody></table>";
        }
    }

    public function get_field_groups() {
        return $this->field_groups;
    }

    public function is_acf_available() {
        return $this->acf_available;
    }

    /**
     * Force reload/sync field groups from JSON - for debugging/manual sync
     */
    public function force_sync_field_groups() {
        hp_log('Force sync field groups requested', 'info', 'ACF_MANAGER');
        
        if (!$this->acf_available) {
            $this->check_acf_availability();
        }
        
        if (!$this->acf_available || !function_exists('acf_add_local_field_group')) {
            hp_log('Cannot sync - ACF not available or functions missing', 'error', 'ACF_MANAGER');
            return false;
        }
        
        // Clear existing field groups
        $this->field_groups = [];
        
        // Force reload
        $this->load_field_groups();
        $this->verify_field_groups();
        
        hp_log('Force sync completed - loaded ' . count($this->field_groups) . ' field groups', 'info', 'ACF_MANAGER');
        return count($this->field_groups);
    }

    /**
     * Get all field groups from database (including orphaned ones)
     */
    public function get_all_database_field_groups() {
        if (!function_exists('acf_get_field_groups')) {
            return [];
        }

        return acf_get_field_groups();
    }

    /**
     * Find orphaned field groups (in database but no JSON file)
     */
    public function find_orphaned_field_groups() {
        $db_groups = $this->get_all_database_field_groups();
        $json_groups = $this->get_field_groups();
        $orphaned = [];

        foreach ($db_groups as $db_group) {
            $key = $db_group['key'];
            // Skip if it has a corresponding JSON file
            if (!isset($json_groups[$key])) {
                // Also check if it's a Happy Place group (starts with our prefix or contains happy-place)
                if (strpos($key, 'group_') === 0 || 
                    strpos($key, 'happy_place') !== false || 
                    strpos($key, 'happy-place') !== false ||
                    strpos($key, 'hp_') !== false) {
                    $orphaned[$key] = $db_group;
                }
            }
        }

        return $orphaned;
    }

    /**
     * Clean up orphaned field groups
     */
    public function cleanup_orphaned_field_groups($dry_run = false) {
        $orphaned = $this->find_orphaned_field_groups();
        $results = [
            'found' => count($orphaned),
            'removed' => 0,
            'errors' => [],
            'groups' => []
        ];

        if (empty($orphaned)) {
            return $results;
        }

        foreach ($orphaned as $key => $group) {
            $results['groups'][] = [
                'key' => $key,
                'title' => $group['title'],
                'action' => $dry_run ? 'would_remove' : 'removed'
            ];

            if (!$dry_run) {
                try {
                    if (function_exists('acf_delete_field_group')) {
                        $deleted = acf_delete_field_group($key);
                        if ($deleted) {
                            $results['removed']++;
                            hp_log("Removed orphaned field group: {$group['title']} ({$key})", 'info', 'ACF_CLEANUP');
                        } else {
                            $results['errors'][] = "Failed to delete field group: {$group['title']}";
                        }
                    } else {
                        $results['errors'][] = "ACF delete function not available";
                    }
                } catch (Exception $e) {
                    $results['errors'][] = "Error deleting {$group['title']}: " . $e->getMessage();
                    hp_log("Error deleting field group {$key}: " . $e->getMessage(), 'error', 'ACF_CLEANUP');
                }
            }
        }

        return $results;
    }

    /**
     * Force refresh all field groups from JSON
     */
    public function refresh_field_groups_from_json() {
        $results = [
            'removed_from_db' => 0,
            'imported_from_json' => 0,
            'errors' => []
        ];

        try {
            // First, remove all existing field groups from database
            if (function_exists('acf_get_field_groups')) {
                $existing_groups = acf_get_field_groups();
                foreach ($existing_groups as $group) {
                    // Only remove Happy Place groups
                    if (strpos($group['key'], 'group_') === 0 || 
                        strpos($group['key'], 'happy_place') !== false || 
                        strpos($group['key'], 'happy-place') !== false ||
                        strpos($group['key'], 'hp_') !== false) {
                        
                        if (function_exists('acf_delete_field_group')) {
                            acf_delete_field_group($group['key']);
                            $results['removed_from_db']++;
                        }
                    }
                }
            }

            // Clear the loaded field groups cache
            $this->field_groups = [];

            // Reload from JSON
            $this->load_field_groups();
            $results['imported_from_json'] = count($this->field_groups);

            hp_log("Refreshed {$results['imported_from_json']} field groups from JSON", 'info', 'ACF_REFRESH');

        } catch (Exception $e) {
            $results['errors'][] = $e->getMessage();
            hp_log("Error refreshing field groups: " . $e->getMessage(), 'error', 'ACF_REFRESH');
        }

        return $results;
    }

    /**
     * Get field group synchronization status
     */
    public function get_sync_status() {
        $json_groups = $this->get_field_groups();
        $db_groups = $this->get_all_database_field_groups();
        $orphaned = $this->find_orphaned_field_groups();

        $db_groups_keyed = [];
        foreach ($db_groups as $group) {
            $db_groups_keyed[$group['key']] = $group;
        }

        $out_of_sync = [];
        foreach ($json_groups as $key => $json_group) {
            if (isset($db_groups_keyed[$key])) {
                $db_group = $db_groups_keyed[$key];
                
                // Compare modification times or other sync indicators
                $json_modified = isset($json_group['modified']) ? strtotime($json_group['modified']) : 0;
                $db_modified = isset($db_group['modified']) ? strtotime($db_group['modified']) : 0;
                
                if ($json_modified != $db_modified) {
                    $out_of_sync[$key] = $json_group;
                }
            }
        }

        return [
            'json_groups' => count($json_groups),
            'db_groups' => count($db_groups),
            'orphaned_groups' => count($orphaned),
            'out_of_sync' => count($out_of_sync),
            'in_sync' => count($json_groups) - count($out_of_sync),
            'orphaned_details' => $orphaned,
            'out_of_sync_details' => $out_of_sync
        ];
    }

    /**
     * Export current database field groups to JSON
     */
    public function export_field_groups_to_json() {
        if (!function_exists('acf_get_field_groups')) {
            return ['success' => false, 'message' => 'ACF not available'];
        }

        $groups = acf_get_field_groups();
        $exported = 0;
        $errors = [];

        // Ensure directory exists
        $json_save_path = $this->get_json_save_path();
        if (!file_exists($json_save_path)) {
            wp_mkdir_p($json_save_path);
        }

        foreach ($groups as $group) {
            // Only export Happy Place groups
            if (strpos($group['key'], 'group_') === 0 || 
                strpos($group['key'], 'happy_place') !== false || 
                strpos($group['key'], 'happy-place') !== false ||
                strpos($group['key'], 'hp_') !== false) {
                
                try {
                    // Get full field group with fields
                    if (function_exists('acf_get_field_group')) {
                        $full_group = acf_get_field_group($group['key']);
                        if ($full_group) {
                            // Get fields
                            if (function_exists('acf_get_fields')) {
                                $full_group['fields'] = acf_get_fields($group['key']);
                            }

                            // Save to JSON file
                            $filename = $json_save_path . $group['key'] . '.json';
                            $json_content = json_encode($full_group, JSON_PRETTY_PRINT);
                            
                            if (file_put_contents($filename, $json_content)) {
                                $exported++;
                                hp_log("Exported field group to JSON: {$group['title']}", 'info', 'ACF_EXPORT');
                            } else {
                                $errors[] = "Failed to write JSON file for: {$group['title']}";
                            }
                        }
                    }
                } catch (Exception $e) {
                    $errors[] = "Error exporting {$group['title']}: " . $e->getMessage();
                }
            }
        }

        return [
            'success' => true,
            'exported' => $exported,
            'errors' => $errors
        ];
    }

    /**
     * Get JSON save path for field groups
     */
    private function get_json_save_path() {
        return HP_PLUGIN_DIR . 'includes/fields/acf-json/';
    }

    /**
     * Handle ACF Pro sync completion
     */
    public function after_acf_sync($keys, $results) {
        hp_log('ACF Pro sync completed for keys: ' . implode(', ', $keys), 'info', 'ACF_SYNC');
        
        // Clear our internal cache after sync
        $this->field_groups = [];
        
        // Reload field groups
        $this->load_field_groups();
        
        hp_log('Field groups reloaded after ACF Pro sync', 'info', 'ACF_SYNC');
    }

    /**
     * Handle field group creation in ACF Pro
     */
    public function after_field_group_create($field_group) {
        if (isset($field_group['key']) && strpos($field_group['key'], 'group_') === 0) {
            hp_log("New Happy Place field group created: {$field_group['key']}", 'info', 'ACF_SYNC');
            
            // Add to our cache
            $this->field_groups[$field_group['key']] = $field_group;
        }
    }

    /**
     * Handle field group updates in ACF Pro
     */
    public function after_field_group_update($field_group) {
        if (isset($field_group['key']) && strpos($field_group['key'], 'group_') === 0) {
            hp_log("Happy Place field group updated: {$field_group['key']}", 'info', 'ACF_SYNC');
            
            // Update our cache
            $this->field_groups[$field_group['key']] = $field_group;
        }
    }

    /**
     * Import field groups from database to JSON (for ACF Pro management)
     */
    public function import_db_field_groups_to_json() {
        if (!function_exists('acf_get_field_groups')) {
            return ['success' => false, 'message' => 'ACF not available'];
        }

        $db_groups = acf_get_field_groups();
        $imported_count = 0;
        $errors = [];

        foreach ($db_groups as $group) {
            // Only import Happy Place groups
            if (strpos($group['key'], 'group_') === 0 || 
                strpos($group['key'], 'happy_place') !== false || 
                strpos($group['key'], 'hp_') !== false) {
                
                try {
                    // Get full field group data
                    if (function_exists('acf_get_field_group')) {
                        $full_group = acf_get_field_group($group['key']);
                        if ($full_group && function_exists('acf_get_fields')) {
                            $full_group['fields'] = acf_get_fields($group['key']);
                            
                            // Save to JSON
                            $json_file = $this->get_json_save_path() . $group['key'] . '.json';
                            $json_content = json_encode($full_group, JSON_PRETTY_PRINT);
                            
                            if (file_put_contents($json_file, $json_content)) {
                                $imported_count++;
                                hp_log("Imported field group to JSON: {$group['title']}", 'info', 'ACF_IMPORT');
                            } else {
                                $errors[] = "Failed to write JSON for: {$group['title']}";
                            }
                        }
                    }
                } catch (Exception $e) {
                    $errors[] = "Error importing {$group['title']}: " . $e->getMessage();
                    hp_log("Error importing field group {$group['key']}: " . $e->getMessage(), 'error', 'ACF_IMPORT');
                }
            }
        }

        return [
            'success' => true,
            'imported' => $imported_count,
            'errors' => $errors
        ];
    }

    /**
     * Force sync from JSON to database (for ACF Pro management interface)
     */
    public function force_json_to_db_sync() {
        if (!function_exists('acf_import_field_group')) {
            return ['success' => false, 'message' => 'ACF import functions not available'];
        }

        $json_path = $this->get_json_save_path();
        $json_files = glob($json_path . '*.json');
        $synced_count = 0;
        $errors = [];

        foreach ($json_files as $file) {
            $filename = basename($file);
            
            // Skip export files
            if (strpos($filename, 'acf-export-') === 0) {
                continue;
            }

            $json_data = json_decode(file_get_contents($file), true);
            
            if ($json_data && isset($json_data['key'], $json_data['title'])) {
                try {
                    acf_import_field_group($json_data);
                    $synced_count++;
                    hp_log("Synced to database: {$json_data['title']}", 'info', 'ACF_SYNC');
                } catch (Exception $e) {
                    $errors[] = "Failed to sync {$json_data['title']}: " . $e->getMessage();
                    hp_log("Error syncing field group {$json_data['key']}: " . $e->getMessage(), 'error', 'ACF_SYNC');
                }
            }
        }

        return [
            'success' => true,
            'synced' => $synced_count,
            'errors' => $errors
        ];
    }
}