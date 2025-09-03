<?php
/**
 * Helper Functions for Agent Archive
 * 
 * @package HappyPlaceTheme
 */

/**
 * Get office options for agent filtering
 * 
 * @return array Office options
 */
function hpt_get_office_options() {
    $offices = get_posts([
        'post_type' => 'office',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC'
    ]);
    
    $options = [];
    
    foreach ($offices as $office) {
        $options[$office->ID] = $office->post_title;
    }
    
    return $options;
}

/**
 * Get agent specialties options
 * 
 * @return array Specialty options
 */
function hpt_get_agent_specialties_options() {
    // If using ACF
    if (function_exists('get_field_object')) {
        $field = get_field_object('field_specialties'); // Replace with actual field key
        return $field['choices'] ?? [];
    }
    
    // Fallback to taxonomy if available
    if (taxonomy_exists('agent_specialty')) {
        $terms = get_terms([
            'taxonomy' => 'agent_specialty',
            'hide_empty' => false
        ]);
        
        $options = [];
        foreach ($terms as $term) {
            $options[$term->slug] = $term->name;
        }
        return $options;
    }
    
    // Default options
    return [
        'residential' => __('Residential Sales', 'happy-place-theme'),
        'commercial' => __('Commercial Real Estate', 'happy-place-theme'),
        'luxury' => __('Luxury Properties', 'happy-place-theme'),
        'investment' => __('Investment Properties', 'happy-place-theme'),
        'first_time' => __('First-Time Buyers', 'happy-place-theme'),
        'relocation' => __('Relocation Services', 'happy-place-theme')
    ];
}

/**
 * Get agent languages options
 * 
 * @return array Language options
 */
function hpt_get_agent_languages_options() {
    // If using ACF
    if (function_exists('get_field_object')) {
        $field = get_field_object('field_languages'); // Replace with actual field key
        return $field['choices'] ?? [];
    }
    
    // Default options
    return [
        'english' => __('English', 'happy-place-theme'),
        'spanish' => __('Spanish', 'happy-place-theme'),
        'french' => __('French', 'happy-place-theme'),
        'mandarin' => __('Mandarin', 'happy-place-theme'),
        'portuguese' => __('Portuguese', 'happy-place-theme'),
        'german' => __('German', 'happy-place-theme')
    ];
}
