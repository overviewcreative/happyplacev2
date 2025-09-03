<?php
/**
 * Create Default Pages
 * 
 * Creates default pages like Contact, About, etc. if they don't exist
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class HPH_Default_Pages {
    
    public function __construct() {
        add_action('after_setup_theme', array($this, 'maybe_create_default_pages'));
        add_action('admin_notices', array($this, 'show_page_creation_notice'));
    }
    
    /**
     * Maybe create default pages
     */
    public function maybe_create_default_pages() {
        // Only run on admin and if user has capability
        if (!is_admin() || !current_user_can('manage_options')) {
            return;
        }
        
        // Check if we should create pages
        $create_pages = get_option('hph_create_default_pages', true);
        
        if (!$create_pages) {
            return;
        }
        
        $created_pages = array();
        
        // Contact Page
        if (!$this->page_exists('contact')) {
            $contact_page_id = $this->create_contact_page();
            if ($contact_page_id) {
                $created_pages[] = 'Contact';
            }
        }
        
        // About Page
        if (!$this->page_exists('about')) {
            $about_page_id = $this->create_about_page();
            if ($about_page_id) {
                $created_pages[] = 'About';
            }
        }
        
        // Services Page
        if (!$this->page_exists('services')) {
            $services_page_id = $this->create_services_page();
            if ($services_page_id) {
                $created_pages[] = 'Services';
            }
        }
        
        if (!empty($created_pages)) {
            set_transient('hph_pages_created', $created_pages, 30);
        }
        
        // Mark that we've run this
        update_option('hph_create_default_pages', false);
    }
    
    /**
     * Check if page exists by slug
     */
    private function page_exists($slug) {
        $page = get_page_by_path($slug);
        return $page !== null;
    }
    
    /**
     * Create contact page
     */
    private function create_contact_page() {
        $page_data = array(
            'post_title' => 'Contact Us',
            'post_content' => 'This page uses the Contact Page template to display contact forms, office locations, and contact information.',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_name' => 'contact',
            'meta_input' => array(
                '_wp_page_template' => 'page-contact.php'
            )
        );
        
        return wp_insert_post($page_data);
    }
    
    /**
     * Create about page
     */
    private function create_about_page() {
        $page_data = array(
            'post_title' => 'About Us',
            'post_content' => 'Learn more about our company, team, and mission in real estate.',
            'post_status' => 'draft', // Keep as draft since we don't have the template yet
            'post_type' => 'page',
            'post_name' => 'about'
        );
        
        return wp_insert_post($page_data);
    }
    
    /**
     * Create services page
     */
    private function create_services_page() {
        $page_data = array(
            'post_title' => 'Our Services',
            'post_content' => 'Discover our comprehensive real estate services.',
            'post_status' => 'draft', // Keep as draft since we don't have the template yet
            'post_type' => 'page',
            'post_name' => 'services'
        );
        
        return wp_insert_post($page_data);
    }
    
    /**
     * Show admin notice for created pages
     */
    public function show_page_creation_notice() {
        $created_pages = get_transient('hph_pages_created');
        
        if ($created_pages) {
            $pages_list = implode(', ', $created_pages);
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p><strong>Happy Place Theme:</strong> Default pages created: ' . esc_html($pages_list) . '. ';
            echo '<a href="' . admin_url('edit.php?post_type=page') . '">View Pages</a></p>';
            echo '</div>';
            
            delete_transient('hph_pages_created');
        }
    }
    
    /**
     * Get default pages configuration
     */
    public function get_default_pages_config() {
        return array(
            'contact' => array(
                'title' => 'Contact Us',
                'template' => 'page-contact.php',
                'content' => 'This page uses the Contact Page template to display contact forms, office locations, and contact information.',
                'status' => 'publish'
            ),
            'about' => array(
                'title' => 'About Us',
                'template' => 'page-about.php',
                'content' => 'Learn more about our company, team, and mission in real estate.',
                'status' => 'draft'
            ),
            'services' => array(
                'title' => 'Our Services',
                'template' => 'page-services.php',
                'content' => 'Discover our comprehensive real estate services.',
                'status' => 'draft'
            ),
            'team' => array(
                'title' => 'Our Team',
                'template' => 'page-team.php',
                'content' => 'Meet our experienced real estate professionals.',
                'status' => 'draft'
            )
        );
    }
    
    /**
     * Manually create a specific page
     */
    public function create_page($page_key) {
        $config = $this->get_default_pages_config();
        
        if (!isset($config[$page_key])) {
            return false;
        }
        
        $page_config = $config[$page_key];
        
        $page_data = array(
            'post_title' => $page_config['title'],
            'post_content' => $page_config['content'],
            'post_status' => $page_config['status'],
            'post_type' => 'page',
            'post_name' => $page_key
        );
        
        if (isset($page_config['template'])) {
            $page_data['meta_input'] = array(
                '_wp_page_template' => $page_config['template']
            );
        }
        
        return wp_insert_post($page_data);
    }
}

// Initialize default pages creator
new HPH_Default_Pages();