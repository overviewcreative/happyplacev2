<?php
/**
 * Template Registration
 * 
 * Registers custom page templates for the admin dropdown
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class HPH_Template_Registration {
    
    public function __construct() {
        add_filter('theme_page_templates', array($this, 'add_page_templates'));
        add_filter('template_include', array($this, 'load_page_template'));
        
        // Add template selection for posts
        add_filter('theme_post_templates', array($this, 'add_post_templates'));
    }
    
    /**
     * Add custom page templates
     */
    public function add_page_templates($templates) {
        $templates['page-contact.php'] = __('Contact Page', 'happy-place-theme');
        $templates['page-about.php'] = __('About Us Page', 'happy-place-theme');
        $templates['page-services.php'] = __('Services Page', 'happy-place-theme');
        $templates['page-team.php'] = __('Team Page', 'happy-place-theme');
        
        return $templates;
    }
    
    /**
     * Add custom post templates
     */
    public function add_post_templates($templates) {
        $templates['single-blog-post.php'] = __('Blog Post Layout', 'happy-place-theme');
        $templates['single-featured.php'] = __('Featured Post Layout', 'happy-place-theme');
        
        return $templates;
    }
    
    /**
     * Load custom page templates
     */
    public function load_page_template($template) {
        global $post;
        
        if (!$post) {
            return $template;
        }
        
        // Get the selected template
        $page_template = get_post_meta($post->ID, '_wp_page_template', true);
        
        if (!$page_template || $page_template === 'default') {
            return $template;
        }
        
        // Check if the template file exists
        $template_file = get_template_directory() . '/' . $page_template;
        
        if (file_exists($template_file)) {
            return $template_file;
        }
        
        return $template;
    }
    
    /**
     * Get available templates
     */
    public function get_available_templates() {
        return array(
            'page-contact.php' => array(
                'name' => __('Contact Page', 'happy-place-theme'),
                'description' => __('Comprehensive contact page with forms, office locations, and contact information', 'happy-place-theme'),
                'post_types' => array('page'),
                'features' => array('contact-form', 'office-locations', 'map-integration')
            ),
            'page-about.php' => array(
                'name' => __('About Us Page', 'happy-place-theme'),
                'description' => __('Company information, team, and history page', 'happy-place-theme'),
                'post_types' => array('page'),
                'features' => array('team-showcase', 'company-stats', 'timeline')
            ),
            'page-services.php' => array(
                'name' => __('Services Page', 'happy-place-theme'),
                'description' => __('Detailed services and offerings page', 'happy-place-theme'),
                'post_types' => array('page'),
                'features' => array('services-grid', 'pricing-tables', 'testimonials')
            ),
            'page-team.php' => array(
                'name' => __('Team Page', 'happy-place-theme'),
                'description' => __('Team members and agent profiles', 'happy-place-theme'),
                'post_types' => array('page'),
                'features' => array('agent-grid', 'agent-profiles', 'contact-info')
            )
        );
    }
    
    /**
     * Check if template has specific feature
     */
    public function template_has_feature($template_name, $feature) {
        $templates = $this->get_available_templates();
        
        if (isset($templates[$template_name]['features'])) {
            return in_array($feature, $templates[$template_name]['features']);
        }
        
        return false;
    }
    
    /**
     * Get template by post ID
     */
    public function get_post_template($post_id) {
        return get_post_meta($post_id, '_wp_page_template', true);
    }
    
    /**
     * Set template for post
     */
    public function set_post_template($post_id, $template) {
        return update_post_meta($post_id, '_wp_page_template', $template);
    }
}

// Initialize template registration
new HPH_Template_Registration();