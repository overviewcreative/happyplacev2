<?php
/**
 * Team Bridge Functions
 * 
 * Provides a comprehensive interface between the plugin layer and templates
 * for the team post type. All data access should go through these functions
 * rather than direct WordPress or ACF calls.
 *
 * @package HappyPlaceTheme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get all team member data
 * 
 * @param int|WP_Post $team_member Team Member ID or post object
 * @return array Complete team member data
 */
function hpt_get_team_member($team_member = null) {
    $team_member = get_post($team_member);
    
    if (!$team_member || $team_member->post_type !== 'team') {
        return null;
    }
    
    return array(
        'id' => $team_member->ID,
        'name' => get_the_title($team_member),
        'slug' => $team_member->post_name,
        'url' => get_permalink($team_member),
        'status' => $team_member->post_status,
        'date_created' => $team_member->post_date,
        'date_modified' => $team_member->post_modified,
        
        // Personal information
        'first_name' => hpt_get_team_member_first_name($team_member->ID),
        'last_name' => hpt_get_team_member_last_name($team_member->ID),
        'title' => hpt_get_team_member_title($team_member->ID),
        'department' => hpt_get_team_member_department($team_member->ID),
        'role' => hpt_get_team_member_role($team_member->ID),
        'team_name' => hpt_get_team_member_team_name($team_member->ID),
        
        // Contact information
        'phone' => hpt_get_team_member_phone($team_member->ID),
        'mobile' => hpt_get_team_member_mobile($team_member->ID),
        'email' => hpt_get_team_member_email($team_member->ID),
        'website' => hpt_get_team_member_website($team_member->ID),
        'office_address' => hpt_get_team_member_office_address($team_member->ID),
        
        // Bio and background
        'bio' => hpt_get_team_member_bio($team_member->ID),
        'specialties' => hpt_get_team_member_specialties($team_member->ID),
        'languages' => hpt_get_team_member_languages($team_member->ID),
        'experience_years' => hpt_get_team_member_experience($team_member->ID),
        'education' => hpt_get_team_member_education($team_member->ID),
        'certifications' => hpt_get_team_member_certifications($team_member->ID),
        
        // Media
        'profile_photo' => hpt_get_team_member_photo($team_member->ID),
        'cover_photo' => hpt_get_team_member_cover_photo($team_member->ID),
        
        // Social media
        'social_links' => hpt_get_team_member_social_links($team_member->ID),
        
        // Professional details
        'license_number' => hpt_get_team_member_license($team_member->ID),
        'hire_date' => hpt_get_team_member_hire_date($team_member->ID),
        'employment_status' => hpt_get_team_member_employment_status($team_member->ID),
        
        // Relationships
        'office' => hpt_get_team_member_office($team_member->ID),
        'manager' => hpt_get_team_member_manager($team_member->ID),
        'direct_reports' => hpt_get_team_member_direct_reports($team_member->ID),
        'related_agent' => hpt_get_team_member_related_agent($team_member->ID),
        
        // Status and visibility
        'is_featured' => hpt_is_team_member_featured($team_member->ID),
        'is_active' => hpt_is_team_member_active($team_member->ID),
        'display_order' => hpt_get_team_member_display_order($team_member->ID),
    );
}

/**
 * Get team member first name
 */
function hpt_get_team_member_first_name($team_member_id) {
    $first_name = get_field('first_name', $team_member_id);
    
    if (!$first_name) {
        $full_name = get_the_title($team_member_id);
        $parts = explode(' ', $full_name);
        $first_name = $parts[0] ?? '';
    }
    
    return $first_name;
}

/**
 * Get team member last name
 */
function hpt_get_team_member_last_name($team_member_id) {
    $last_name = get_field('last_name', $team_member_id);
    
    if (!$last_name) {
        $full_name = get_the_title($team_member_id);
        $parts = explode(' ', $full_name);
        $last_name = end($parts) ?: '';
    }
    
    return $last_name;
}

/**
 * Get team member full name
 */
function hpt_get_team_member_name($team_member_id) {
    return get_the_title($team_member_id);
}

/**
 * Get team member title/position
 */
function hpt_get_team_member_title($team_member_id) {
    return get_field('title', $team_member_id) ?: get_field('position', $team_member_id) ?: '';
}

/**
 * Get team member department
 */
function hpt_get_team_member_department($team_member_id) {
    return get_field('department', $team_member_id) ?: '';
}

/**
 * Get team member role
 */
function hpt_get_team_member_role($team_member_id) {
    return get_field('role', $team_member_id) ?: 'team_member';
}

/**
 * Get role label
 */
function hpt_get_team_member_role_label($team_member_id) {
    $role = hpt_get_team_member_role($team_member_id);
    
    $labels = array(
        'team_member' => __('Team Member', 'happy-place-theme'),
        'manager' => __('Manager', 'happy-place-theme'),
        'director' => __('Director', 'happy-place-theme'),
        'broker' => __('Broker', 'happy-place-theme'),
        'assistant' => __('Assistant', 'happy-place-theme'),
        'coordinator' => __('Coordinator', 'happy-place-theme'),
        'specialist' => __('Specialist', 'happy-place-theme'),
        'administrator' => __('Administrator', 'happy-place-theme'),
        'support' => __('Support Staff', 'happy-place-theme'),
    );
    
    return $labels[$role] ?? ucfirst(str_replace('_', ' ', $role));
}

/**
 * Get team name
 */
function hpt_get_team_member_team_name($team_member_id) {
    return get_field('team_name', $team_member_id) ?: '';
}

/**
 * Get team member phone
 */
function hpt_get_team_member_phone($team_member_id) {
    return get_field('phone', $team_member_id) ?: '';
}

/**
 * Get team member mobile phone
 */
function hpt_get_team_member_mobile($team_member_id) {
    return get_field('mobile_phone', $team_member_id) ?: get_field('mobile', $team_member_id) ?: '';
}

/**
 * Get team member email
 */
function hpt_get_team_member_email($team_member_id) {
    return get_field('email', $team_member_id) ?: '';
}

/**
 * Get team member website
 */
function hpt_get_team_member_website($team_member_id) {
    return get_field('website', $team_member_id) ?: '';
}

/**
 * Get office address
 */
function hpt_get_team_member_office_address($team_member_id) {
    return get_field('office_address', $team_member_id) ?: '';
}

/**
 * Get team member bio
 */
function hpt_get_team_member_bio($team_member_id) {
    $bio = get_field('bio', $team_member_id);
    
    if (!$bio) {
        $post = get_post($team_member_id);
        $bio = $post->post_content;
    }
    
    return $bio;
}

/**
 * Get team member specialties
 */
function hpt_get_team_member_specialties($team_member_id) {
    $specialties = get_field('specialties', $team_member_id);
    
    if (!is_array($specialties)) {
        $specialties = array();
    }
    
    return $specialties;
}

/**
 * Get team member languages
 */
function hpt_get_team_member_languages($team_member_id) {
    $languages = get_field('languages', $team_member_id);
    
    if (!is_array($languages)) {
        $languages = array('English');
    }
    
    return $languages;
}

/**
 * Get years of experience
 */
function hpt_get_team_member_experience($team_member_id) {
    return intval(get_field('years_experience', $team_member_id) ?: get_field('experience_years', $team_member_id));
}

/**
 * Get education
 */
function hpt_get_team_member_education($team_member_id) {
    $education = get_field('education', $team_member_id);
    
    if (!is_array($education)) {
        $education = array();
    }
    
    return $education;
}

/**
 * Get certifications
 */
function hpt_get_team_member_certifications($team_member_id) {
    $certifications = get_field('certifications', $team_member_id);
    
    if (!is_array($certifications)) {
        $certifications = array();
    }
    
    return $certifications;
}

/**
 * Get team member profile photo
 */
function hpt_get_team_member_photo($team_member_id, $size = 'medium') {
    // Try ACF field first
    $photo = get_field('profile_photo', $team_member_id);
    
    if ($photo) {
        if (is_array($photo)) {
            return array(
                'id' => $photo['ID'],
                'url' => $photo['sizes'][$size] ?? $photo['url'],
                'alt' => $photo['alt'],
            );
        } else {
            return array(
                'id' => $photo,
                'url' => wp_get_attachment_image_url($photo, $size),
                'alt' => get_post_meta($photo, '_wp_attachment_image_alt', true),
            );
        }
    }
    
    // Try featured image
    if (has_post_thumbnail($team_member_id)) {
        return array(
            'id' => get_post_thumbnail_id($team_member_id),
            'url' => get_the_post_thumbnail_url($team_member_id, $size),
            'alt' => get_post_meta(get_post_thumbnail_id($team_member_id), '_wp_attachment_image_alt', true),
        );
    }
    
    // Return placeholder
    return array(
        'id' => 0,
        'url' => get_template_directory_uri() . '/assets/images/team-placeholder.jpg',
        'alt' => __('Team member photo', 'happy-place-theme'),
    );
}

/**
 * Get team member cover photo
 */
function hpt_get_team_member_cover_photo($team_member_id, $size = 'large') {
    $photo = get_field('cover_photo', $team_member_id);
    
    if ($photo) {
        if (is_array($photo)) {
            return array(
                'id' => $photo['ID'],
                'url' => $photo['sizes'][$size] ?? $photo['url'],
                'alt' => $photo['alt'],
            );
        } else {
            return array(
                'id' => $photo,
                'url' => wp_get_attachment_image_url($photo, $size),
                'alt' => get_post_meta($photo, '_wp_attachment_image_alt', true),
            );
        }
    }
    
    return null;
}

/**
 * Get team member social links
 */
function hpt_get_team_member_social_links($team_member_id) {
    $social = get_field('social_links', $team_member_id);
    
    if (!is_array($social)) {
        $social = array();
        
        // Try individual fields
        $platforms = array('facebook', 'instagram', 'linkedin', 'twitter', 'youtube');
        
        foreach ($platforms as $platform) {
            $url = get_field($platform . '_url', $team_member_id);
            if ($url) {
                $social[$platform] = $url;
            }
        }
    }
    
    return $social;
}

/**
 * Get license number
 */
function hpt_get_team_member_license($team_member_id) {
    return get_field('license_number', $team_member_id) ?: '';
}

/**
 * Get hire date
 */
function hpt_get_team_member_hire_date($team_member_id) {
    return get_field('hire_date', $team_member_id) ?: '';
}

/**
 * Get employment status
 */
function hpt_get_team_member_employment_status($team_member_id) {
    return get_field('employment_status', $team_member_id) ?: 'active';
}

/**
 * Get employment status label
 */
function hpt_get_team_member_employment_status_label($team_member_id) {
    $status = hpt_get_team_member_employment_status($team_member_id);
    
    $labels = array(
        'active' => __('Active', 'happy-place-theme'),
        'inactive' => __('Inactive', 'happy-place-theme'),
        'on_leave' => __('On Leave', 'happy-place-theme'),
        'terminated' => __('Terminated', 'happy-place-theme'),
        'retired' => __('Retired', 'happy-place-theme'),
    );
    
    return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
}

/**
 * Get team member office
 */
function hpt_get_team_member_office($team_member_id) {
    $office_id = get_field('office', $team_member_id);
    
    return $office_id ? intval($office_id) : null;
}

/**
 * Get team member manager
 */
function hpt_get_team_member_manager($team_member_id) {
    $manager_id = get_field('manager', $team_member_id);
    
    if (!$manager_id) {
        $manager_id = get_field('reports_to', $team_member_id);
    }
    
    return $manager_id ? intval($manager_id) : null;
}

/**
 * Get direct reports
 */
function hpt_get_team_member_direct_reports($team_member_id) {
    return get_posts(array(
        'post_type' => 'team',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'manager',
                'value' => $team_member_id,
                'compare' => '='
            ),
            array(
                'key' => 'employment_status',
                'value' => 'active',
                'compare' => '='
            )
        ),
        'orderby' => 'title',
        'order' => 'ASC'
    ));
}

/**
 * Get related agent profile
 */
function hpt_get_team_member_related_agent($team_member_id) {
    $agent_id = get_field('related_agent', $team_member_id);
    
    if (!$agent_id) {
        $agent_id = get_field('agent_profile', $team_member_id);
    }
    
    return $agent_id ? intval($agent_id) : null;
}

/**
 * Check if team member is featured
 */
function hpt_is_team_member_featured($team_member_id) {
    return get_field('featured', $team_member_id) == true;
}

/**
 * Check if team member is active
 */
function hpt_is_team_member_active($team_member_id) {
    $status = hpt_get_team_member_employment_status($team_member_id);
    return $status === 'active';
}

/**
 * Get display order
 */
function hpt_get_team_member_display_order($team_member_id) {
    return intval(get_field('display_order', $team_member_id));
}

/**
 * Query team members
 */
function hpt_query_team_members($args = array()) {
    $defaults = array(
        'post_type' => 'team',
        'post_status' => 'publish',
        'posts_per_page' => 20,
        'orderby' => 'menu_order title',
        'order' => 'ASC',
    );
    
    $args = wp_parse_args($args, $defaults);
    
    return new WP_Query($args);
}

/**
 * Get active team members
 */
function hpt_get_active_team_members($limit = -1) {
    return get_posts(array(
        'post_type' => 'team',
        'posts_per_page' => $limit,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'employment_status',
                'value' => 'active',
                'compare' => '='
            )
        ),
        'meta_key' => 'display_order',
        'orderby' => 'meta_value_num title',
        'order' => 'ASC'
    ));
}

/**
 * Get featured team members
 */
function hpt_get_featured_team_members($limit = 6) {
    return get_posts(array(
        'post_type' => 'team',
        'posts_per_page' => $limit,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'featured',
                'value' => true,
                'compare' => '='
            ),
            array(
                'key' => 'employment_status',
                'value' => 'active',
                'compare' => '='
            )
        ),
        'meta_key' => 'display_order',
        'orderby' => 'meta_value_num title',
        'order' => 'ASC'
    ));
}

/**
 * Get team members by department
 */
function hpt_get_team_members_by_department($department, $limit = -1) {
    return get_posts(array(
        'post_type' => 'team',
        'posts_per_page' => $limit,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'department',
                'value' => $department,
                'compare' => '='
            ),
            array(
                'key' => 'employment_status',
                'value' => 'active',
                'compare' => '='
            )
        ),
        'meta_key' => 'display_order',
        'orderby' => 'meta_value_num title',
        'order' => 'ASC'
    ));
}

/**
 * Get team members by role
 */
function hpt_get_team_members_by_role($role, $limit = -1) {
    return get_posts(array(
        'post_type' => 'team',
        'posts_per_page' => $limit,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'role',
                'value' => $role,
                'compare' => '='
            ),
            array(
                'key' => 'employment_status',
                'value' => 'active',
                'compare' => '='
            )
        ),
        'meta_key' => 'display_order',
        'orderby' => 'meta_value_num title',
        'order' => 'ASC'
    ));
}

/**
 * Get team hierarchy
 */
function hpt_get_team_hierarchy($parent_id = null) {
    $hierarchy = array();
    
    if ($parent_id) {
        // Get direct reports
        $direct_reports = hpt_get_team_member_direct_reports($parent_id);
        
        foreach ($direct_reports as $report) {
            $member_data = hpt_get_team_member($report->ID);
            $member_data['children'] = hpt_get_team_hierarchy($report->ID);
            $hierarchy[] = $member_data;
        }
    } else {
        // Get top-level managers (those without managers)
        $top_level = get_posts(array(
            'post_type' => 'team',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => 'manager',
                    'compare' => 'NOT EXISTS'
                ),
                array(
                    'key' => 'employment_status',
                    'value' => 'active',
                    'compare' => '='
                )
            ),
            'orderby' => 'title',
            'order' => 'ASC'
        ));
        
        foreach ($top_level as $member) {
            $member_data = hpt_get_team_member($member->ID);
            $member_data['children'] = hpt_get_team_hierarchy($member->ID);
            $hierarchy[] = $member_data;
        }
    }
    
    return $hierarchy;
}
