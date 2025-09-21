<?php
/**
 * Agent Bridge Functions
 * 
 * Provides a comprehensive interface between the plugin layer and templates
 * for the agent post type. All data access should go through these functions
 * rather than direct WordPress or ACF calls.
 *
 * @package HappyPlaceTheme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get all agent data with enhanced service integration
 * 
 * @param int|WP_Post $agent Agent ID or post object
 * @return array Complete agent data with performance stats
 */
function hpt_get_agent($agent = null) {
    $agent = get_post($agent);
    
    if (!$agent || $agent->post_type !== 'agent') {
        return null;
    }
    
    // Get enhanced data from Agent Service if available
    $enhanced_data = null;
    $service_stats = null;
    
    if (class_exists('HappyPlace\\Services\\AgentService')) {
        $agent_service = new \HappyPlace\Services\AgentService();
        $agent_service->init();
        
        // Get user ID associated with this agent post
        $user_id = get_post_meta($agent->ID, 'agent_user_id', true);
        if ($user_id) {
            $enhanced_data = $agent_service->get_agent_by_user($user_id);
            $service_stats = $enhanced_data['stats'] ?? null;
        }
    }
    
    $base_data = array(
        'id' => $agent->ID,
        'name' => get_the_title($agent),
        'slug' => $agent->post_name,
        'url' => get_permalink($agent),
        'status' => $agent->post_status,
        
        // Personal info
        'first_name' => hpt_get_agent_first_name($agent->ID),
        'last_name' => hpt_get_agent_last_name($agent->ID),
        'title' => hpt_get_agent_title($agent->ID),
        'license_number' => hpt_get_agent_license($agent->ID),
        'years_experience' => hpt_get_agent_experience($agent->ID),
        
        // Contact
        'phone' => hpt_get_agent_phone($agent->ID),
        'mobile' => hpt_get_agent_mobile($agent->ID),
        'email' => hpt_get_agent_email($agent->ID),
        'website' => hpt_get_agent_website($agent->ID),
        
        // Bio and specialties
        'bio' => hpt_get_agent_bio($agent->ID),
        'specialties' => hpt_get_agent_specialties($agent->ID),
        'languages' => hpt_get_agent_languages($agent->ID),
        'certifications' => hpt_get_agent_certifications($agent->ID),
        
        // Media
        'profile_photo' => hpt_get_agent_photo($agent->ID),
        'cover_photo' => hpt_get_agent_cover_photo($agent->ID),
        
        // Social media
        'social_links' => hpt_get_agent_social_links($agent->ID),
        
        // Performance
        'total_sales' => hpt_get_agent_total_sales($agent->ID),
        'active_listings' => hpt_get_agent_active_listings_count($agent->ID),
        'sold_listings' => hpt_get_agent_sold_listings_count($agent->ID),
        'average_dom' => hpt_get_agent_average_dom($agent->ID),
        'rating' => hpt_get_agent_rating($agent->ID),
        'reviews_count' => hpt_get_agent_reviews_count($agent->ID),
        
        // Relationships
        'office' => hpt_get_agent_office($agent->ID),
        'team' => hpt_get_agent_team($agent->ID),
        'user_id' => hpt_get_agent_user_id($agent->ID),
        
        // Status
        'is_featured' => hpt_is_agent_featured($agent->ID),
    );
    
    // Merge with enhanced service data if available
    if ($enhanced_data) {
        $base_data['enhanced_stats'] = $service_stats;
        $base_data['user_data'] = $enhanced_data['user'] ?? null;
        $base_data['wordpress_user_id'] = $enhanced_data['user_id'] ?? null;
        
        // Override with more accurate service data if available
        if (isset($service_stats['active_listings'])) {
            $base_data['active_listings'] = $service_stats['active_listings'];
        }
        if (isset($service_stats['sold_listings'])) {
            $base_data['sold_listings'] = $service_stats['sold_listings'];
        }
        if (isset($service_stats['total_volume'])) {
            $base_data['total_sales'] = $service_stats['total_volume'];
        }
    }
    
    return $base_data;
}

/**
 * Get agent first name
 */
function hpt_get_agent_first_name($agent_id) {
    $first_name = get_field('first_name', $agent_id);
    
    if (!$first_name) {
        $full_name = get_the_title($agent_id);
        $parts = explode(' ', $full_name);
        $first_name = $parts[0] ?? '';
    }
    
    return $first_name;
}

/**
 * Get agent last name
 */
function hpt_get_agent_last_name($agent_id) {
    $last_name = get_field('last_name', $agent_id);
    
    if (!$last_name) {
        $full_name = get_the_title($agent_id);
        $parts = explode(' ', $full_name);
        $last_name = end($parts) ?: '';
    }
    
    return $last_name;
}

/**
 * Get agent full name
 */
function hpt_get_agent_name($agent_id) {
    return get_the_title($agent_id);
}

/**
 * Get agent title
 */
function hpt_get_agent_title($agent_id) {
    return get_field('title', $agent_id) ?: __('Real Estate Agent', 'happy-place-theme');
}

/**
 * Get agent license number
 */
function hpt_get_agent_license($agent_id) {
    return get_field('license_number', $agent_id) ?: '';
}

/**
 * Get agent years of experience
 */
function hpt_get_agent_experience($agent_id) {
    return intval(get_field('years_experience', $agent_id) ?: get_field('experience_years', $agent_id));
}

/**
 * Get agent phone
 */
function hpt_get_agent_phone($agent_id) {
    return get_field('phone', $agent_id) ?: '';
}

/**
 * Get agent mobile phone
 */
function hpt_get_agent_mobile($agent_id) {
    return get_field('mobile_phone', $agent_id) ?: get_field('mobile', $agent_id) ?: '';
}

/**
 * Get agent email
 */
function hpt_get_agent_email($agent_id) {
    return get_field('email', $agent_id) ?: '';
}

/**
 * Get agent website
 */
function hpt_get_agent_website($agent_id) {
    return get_field('website', $agent_id) ?: '';
}

/**
 * Get agent bio
 */
function hpt_get_agent_bio($agent_id) {
    $bio = get_field('bio', $agent_id);
    
    if (!$bio) {
        $post = get_post($agent_id);
        $bio = $post->post_content;
    }
    
    return $bio;
}

/**
 * Get agent specialties
 */
function hpt_get_agent_specialties($agent_id) {
    $specialties = get_field('specialties', $agent_id);
    
    if (!is_array($specialties)) {
        $specialties = array();
    }
    
    return $specialties;
}

/**
 * Get agent languages
 */
function hpt_get_agent_languages($agent_id) {
    $languages = get_field('languages', $agent_id);
    
    if (!is_array($languages)) {
        $languages = array('English');
    }
    
    return $languages;
}

/**
 * Get agent certifications
 */
function hpt_get_agent_certifications($agent_id) {
    $certifications = get_field('certifications', $agent_id);
    
    if (!is_array($certifications)) {
        $certifications = array();
    }
    
    return $certifications;
}

/**
 * Get agent profile photo
 */
function hpt_get_agent_photo($agent_id, $size = 'medium') {
    // Try ACF field first
    $photo = get_field('profile_photo', $agent_id);
    
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
    if (has_post_thumbnail($agent_id)) {
        return array(
            'id' => get_post_thumbnail_id($agent_id),
            'url' => get_the_post_thumbnail_url($agent_id, $size),
            'alt' => get_post_meta(get_post_thumbnail_id($agent_id), '_wp_attachment_image_alt', true),
        );
    }
    
    // Return fallback using centralized system
    return hph_get_typed_fallback_image('agent', $size);
}

/**
 * Get agent cover photo
 */
function hpt_get_agent_cover_photo($agent_id, $size = 'large') {
    $photo = get_field('cover_photo', $agent_id);
    
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
 * Get agent social links
 */
function hpt_get_agent_social_links($agent_id) {
    $social = get_field('social_links', $agent_id);
    
    if (!is_array($social)) {
        $social = array();
        
        // Try individual fields
        $platforms = array('facebook', 'instagram', 'linkedin', 'twitter', 'youtube');
        
        foreach ($platforms as $platform) {
            $url = get_field($platform . '_url', $agent_id);
            if ($url) {
                $social[$platform] = $url;
            }
        }
    }
    
    return $social;
}

/**
 * Get agent total sales volume
 */
function hpt_get_agent_total_sales($agent_id) {
    $total = get_field('total_sales_volume', $agent_id);
    
    if (!$total) {
        // Calculate from sold listings
        $sold_listings = get_posts(array(
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'listing_agent',
                    'value' => $agent_id,
                    'compare' => '='
                ),
                array(
                    'key' => 'listing_status',
                    'value' => 'sold',
                    'compare' => '='
                )
            )
        ));
        
        $total = 0;
        foreach ($sold_listings as $listing) {
            $total += floatval(get_field('listing_price', $listing->ID));
        }
    }
    
    return floatval($total);
}

/**
 * Get agent active listings count
 */
function hpt_get_agent_active_listings_count($agent_id) {
    $count = get_field('active_listings_count', $agent_id);
    
    if ($count === null || $count === false) {
        // Count active listings
        $active = get_posts(array(
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'listing_agent',
                    'value' => $agent_id,
                    'compare' => '='
                ),
                array(
                    'key' => 'listing_status',
                    'value' => 'active',
                    'compare' => '='
                )
            ),
            'fields' => 'ids'
        ));
        
        $count = count($active);
    }
    
    return intval($count);
}

/**
 * Get agent sold listings count
 */
function hpt_get_agent_sold_listings_count($agent_id) {
    $count = get_field('sold_listings_count', $agent_id);
    
    if ($count === null || $count === false) {
        // Count sold listings
        $sold = get_posts(array(
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'listing_agent',
                    'value' => $agent_id,
                    'compare' => '='
                ),
                array(
                    'key' => 'listing_status',
                    'value' => 'sold',
                    'compare' => '='
                )
            ),
            'fields' => 'ids'
        ));
        
        $count = count($sold);
    }
    
    return intval($count);
}

/**
 * Get agent average days on market
 */
function hpt_get_agent_average_dom($agent_id) {
    return intval(get_field('average_dom', $agent_id));
}

/**
 * Get agent rating
 */
function hpt_get_agent_rating($agent_id) {
    return floatval(get_field('agent_rating', $agent_id) ?: get_field('rating', $agent_id) ?: 0);
}

/**
 * Get agent reviews count
 */
function hpt_get_agent_reviews_count($agent_id) {
    return intval(get_field('reviews_count', $agent_id) ?: 0);
}

/**
 * Get agent office
 */
function hpt_get_agent_office($agent_id) {
    $office_id = get_field('office', $agent_id);
    
    if (!$office_id) {
        $office_id = get_field('brokerage', $agent_id);
    }
    
    return $office_id ? intval($office_id) : null;
}

/**
 * Get agent team
 */
function hpt_get_agent_team($agent_id) {
    $team_id = get_field('team', $agent_id);
    
    return $team_id ? intval($team_id) : null;
}

/**
 * Get agent user ID
 * Function moved to user-bridge.php for unified agent-user system
 */

/**
 * Check if agent is featured
 */
function hpt_is_agent_featured($agent_id) {
    return get_field('featured', $agent_id) == true;
}

/**
 * Get agent listings
 */
if (!function_exists('hpt_get_agent_listings')) {
    function hpt_get_agent_listings($agent_id, $args = array()) {
        $defaults = array(
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'listing_agent',
                    'value' => $agent_id,
                    'compare' => '='
                ),
                array(
                    'key' => 'listing_status',
                    'value' => 'active',
                    'compare' => '='
                )
            ),
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        return get_posts($args);
    }
}

/**
 * Get agent sold listings
 */
function hpt_get_agent_sold_listings($agent_id, $limit = -1) {
    return get_posts(array(
        'post_type' => 'listing',
        'posts_per_page' => $limit,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'listing_agent',
                'value' => $agent_id,
                'compare' => '='
            ),
            array(
                'key' => 'listing_status',
                'value' => 'sold',
                'compare' => '='
            )
        ),
        'orderby' => 'date',
        'order' => 'DESC'
    ));
}

/**
 * Query agents
 */
function hpt_query_agents($args = array()) {
    $defaults = array(
        'post_type' => 'agent',
        'post_status' => 'publish',
        'posts_per_page' => 12,
        'orderby' => 'title',
        'order' => 'ASC',
    );
    
    $args = wp_parse_args($args, $defaults);
    
    return new WP_Query($args);
}

/**
 * Get featured agents
 */
function hpt_get_featured_agents($limit = 4) {
    return get_posts(array(
        'post_type' => 'agent',
        'posts_per_page' => $limit,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'featured',
                'value' => true,
                'compare' => '='
            )
        ),
        'orderby' => 'menu_order title',
        'order' => 'ASC'
    ));
}

/**
 * Get top agents by sales
 */
function hpt_get_top_agents($limit = 6) {
    return get_posts(array(
        'post_type' => 'agent',
        'posts_per_page' => $limit,
        'post_status' => 'publish',
        'meta_key' => 'total_sales_volume',
        'orderby' => 'meta_value_num',
        'order' => 'DESC'
    ));
}

/**
 * Get agent by user ID
 */
function hpt_get_agent_by_user($user_id) {
    $agents = get_posts(array(
        'post_type' => 'agent',
        'posts_per_page' => 1,
        'meta_query' => array(
            array(
                'key' => 'user',
                'value' => $user_id,
                'compare' => '='
            )
        )
    ));
    
    return !empty($agents) ? $agents[0] : null;
}

/**
 * Get agent contact card HTML
 */
function hpt_get_agent_contact_card($agent_id) {
    $agent = hpt_get_agent($agent_id);
    
    if (!$agent) {
        return '';
    }
    
    ob_start();
    ?>
    <div class="hp-agent-contact-card">
        <div class="hp-agent-photo">
            <img src="<?php echo esc_url($agent['profile_photo']['url']); ?>" 
                 alt="<?php echo esc_attr($agent['profile_photo']['alt']); ?>">
        </div>
        <div class="hp-agent-info">
            <h3><?php echo esc_html($agent['name']); ?></h3>
            <p class="hp-agent-title"><?php echo esc_html($agent['title']); ?></p>
            
            <?php if ($agent['phone']) : ?>
                <p class="hp-agent-phone">
                    <i class="fas fa-phone"></i>
                    <a href="tel:<?php echo esc_attr($agent['phone']); ?>">
                        <?php echo esc_html($agent['phone']); ?>
                    </a>
                </p>
            <?php endif; ?>
            
            <?php if ($agent['email']) : ?>
                <p class="hp-agent-email">
                    <i class="fas fa-envelope"></i>
                    <a href="mailto:<?php echo esc_attr($agent['email']); ?>">
                        <?php echo esc_html($agent['email']); ?>
                    </a>
                </p>
            <?php endif; ?>
            
            <a href="<?php echo esc_url($agent['url']); ?>" class="hp-btn hp-btn-primary">
                <?php esc_html_e('View Profile', 'happy-place-theme'); ?>
            </a>
        </div>
    </div>
    <?php
    
    return ob_get_clean();
}
