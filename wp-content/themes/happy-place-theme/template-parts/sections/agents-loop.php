<?php
/**
 * HPH Agents Loop Section Template
 * Display agents/team members in various layouts
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 * 
 * Args:
 * - layout: 'grid' | 'list' | 'featured' | 'carousel' | 'minimal'
 * - columns: 2 | 3 | 4 (for grid layout)
 * - background: 'white' | 'light' | 'dark' | 'primary' | 'gradient'
 * - padding: 'sm' | 'md' | 'lg' | 'xl' | '2xl'
 * - content_width: 'narrow' | 'normal' | 'wide' | 'full'
 * - headline: string
 * - subheadline: string
 * - agents: array of agent data (or WP_Query args)
 * - show_bio: boolean
 * - show_contact: boolean
 * - show_social: boolean
 * - show_stats: boolean
 * - show_button: boolean
 * - animation: boolean
 * - section_id: string
 */

// Register this template part for asset loading
if (function_exists('hph_register_template_part')) {
    hph_register_template_part('sections/agents-loop');
}

// Default arguments
$defaults = array(
    'layout' => 'grid',
    'columns' => 3,
    'background' => 'white',
    'padding' => 'xl',
    'content_width' => 'normal',
    'headline' => 'Meet Our Agents',
    'subheadline' => '',
    'agents' => array(),
    'show_bio' => true,
    'show_contact' => true,
    'show_social' => true,
    'show_stats' => false,
    'show_button' => true,
    'animation' => false,
    'section_id' => '',
    'query_args' => array()
);

// Merge with provided args - handle cases where $args might not be set
if (!isset($args) || !is_array($args)) {
    $args = array();
}
$config = wp_parse_args($args, $defaults);
extract($config);

// Get agents data with proper null handling using bridge functions
if (empty($agents) && !empty($query_args)) {
    // Use WP_Query if query args provided
    $agent_query = new WP_Query($query_args);
    $agents = array();
    
    if ($agent_query->have_posts()) {
        while ($agent_query->have_posts()) {
            $agent_query->the_post();
            $agent_id = get_the_ID();
            
            // Use bridge functions with comprehensive null handling
            if (function_exists('hpt_get_agent')) {
                // Use full agent bridge function
                $agent_data = hpt_get_agent($agent_id);
                if ($agent_data) {
                    $agents[] = array(
                        'id' => $agent_data['id'],
                        'name' => $agent_data['name'] ?: 'Agent Name',
                        'title' => $agent_data['title'] ?: 'Real Estate Agent',
                        'bio' => $agent_data['bio'] ?: '',
                        'image' => $agent_data['profile_photo']['url'] ?? '',
                        'email' => $agent_data['email'] ?: '',
                        'phone' => $agent_data['phone'] ?: '',
                        'social' => array_filter($agent_data['social_links'] ?: array()), // Remove empty values
                        'stats' => array(
                            'sold' => $agent_data['sold_listings'] ?: 0,
                            'years' => $agent_data['years_experience'] ?: 0,
                            'rating' => $agent_data['rating'] ?: 5
                        ),
                        'link' => $agent_data['url'] ?: '#'
                    );
                }
            } else {
                // Fallback to individual bridge functions with null handling
                $agents[] = array(
                    'id' => $agent_id,
                    'name' => get_the_title() ?: 'Agent Name',
                    'title' => function_exists('hpt_get_agent_title') ? (hpt_get_agent_title($agent_id) ?: 'Real Estate Agent') : 'Real Estate Agent',
                    'bio' => get_the_excerpt() ?: '',
                    'image' => function_exists('hpt_get_agent_photo') ? (hpt_get_agent_photo($agent_id)['url'] ?? '') : '',
                    'email' => function_exists('hpt_get_agent_email') ? (hpt_get_agent_email($agent_id) ?: '') : '',
                    'phone' => function_exists('hpt_get_agent_phone') ? (hpt_get_agent_phone($agent_id) ?: '') : '',
                    'social' => function_exists('hpt_get_agent_social_links') ? array_filter(hpt_get_agent_social_links($agent_id) ?: array()) : array(),
                    'stats' => array(
                        'sold' => function_exists('hpt_get_agent_sold_listings_count') ? (hpt_get_agent_sold_listings_count($agent_id) ?: 0) : 0,
                        'years' => function_exists('hpt_get_agent_experience') ? (hpt_get_agent_experience($agent_id) ?: 0) : 0,
                        'rating' => function_exists('hpt_get_agent_rating') ? (hpt_get_agent_rating($agent_id) ?: 5) : 5
                    ),
                    'link' => get_permalink() ?: '#'
                );
            }
        }
        wp_reset_postdata();
    }
} elseif (empty($agents)) {
    // Query for agent posts if no custom agents provided
    $agent_query = new WP_Query(array(
        'post_type' => 'agent',
        'post_status' => 'publish',
        'posts_per_page' => 6,
        'meta_query' => array(
            array(
                'key' => 'featured_agent',
                'value' => '1',
                'compare' => '='
            )
        )
    ));
    
    if (!$agent_query->have_posts()) {
        // If no featured agents, get any published agents
        $agent_query = new WP_Query(array(
            'post_type' => 'agent',
            'post_status' => 'publish',
            'posts_per_page' => 6
        ));
    }
    
    $agents = array();
    if ($agent_query->have_posts()) {
        while ($agent_query->have_posts()) {
            $agent_query->the_post();
            $agent_id = get_the_ID();
            
            // Use bridge functions with comprehensive null handling
            if (function_exists('hpt_get_agent')) {
                $agent_data = hpt_get_agent($agent_id);
                if ($agent_data) {
                    $agents[] = array(
                        'id' => $agent_data['id'],
                        'name' => $agent_data['name'] ?: 'Agent Name',
                        'title' => $agent_data['title'] ?: 'Real Estate Agent',
                        'bio' => $agent_data['bio'] ?: '',
                        'image' => $agent_data['profile_photo']['url'] ?? '',
                        'email' => $agent_data['email'] ?: '',
                        'phone' => $agent_data['phone'] ?: '',
                        'social' => array_filter($agent_data['social_links'] ?: array()),
                        'stats' => array(
                            'sold' => $agent_data['sold_listings'] ?: 0,
                            'years' => $agent_data['years_experience'] ?: 0,
                            'rating' => $agent_data['rating'] ?: 5
                        ),
                        'link' => $agent_data['url'] ?: '#'
                    );
                }
            }
        }
        wp_reset_postdata();
    }
    
    // If still no agents, use demo data
    if (empty($agents)) {
        $demo_image_base = function_exists('hph_get_image_url') ? '' : hph_get_image_url_only('assets/images/');
        
        $agents = array(
            array(
                'name' => 'Sarah Johnson',
                'title' => 'Senior Real Estate Agent',
                'bio' => 'With over 15 years of experience in luxury real estate, Sarah specializes in waterfront properties.',
                'image' => function_exists('hph_get_image_url') ? hph_get_image_url('26590 Mariners Rd. 1.jpg') : $demo_image_base . 'agent-placeholder.jpg',
                'email' => 'sarah@example.com',
                'phone' => '(555) 123-4567',
                'social' => array(
                    'facebook' => '#',
                    'linkedin' => '#',
                    'instagram' => '#'
                ),
                'stats' => array(
                    'sold' => 127,
                    'years' => 15,
                    'rating' => 5
                ),
                'link' => '#'
            ),
            array(
                'name' => 'Michael Chen',
                'title' => 'Luxury Property Specialist',
                'bio' => 'Michael brings a unique perspective to real estate with his architectural background.',
                'image' => function_exists('hph_get_image_url') ? hph_get_image_url('26590 Mariners Rd. 2.jpg') : $demo_image_base . 'agent-placeholder.jpg',
                'email' => 'michael@example.com',
                'phone' => '(555) 234-5678',
                'social' => array(
                    'twitter' => '#',
                    'linkedin' => '#'
                ),
                'stats' => array(
                    'sold' => 89,
                    'years' => 8,
                    'rating' => 4.9
                ),
                'link' => '#'
            ),
            array(
                'name' => 'Emily Rodriguez',
                'title' => 'First-Time Buyer Expert',
                'bio' => 'Emily is passionate about helping first-time buyers navigate the real estate market.',
                'image' => function_exists('hph_get_image_url') ? hph_get_image_url('26590 Mariners Rd. 3.jpg') : $demo_image_base . 'agent-placeholder.jpg',
                'email' => 'emily@example.com',
                'phone' => '(555) 345-6789',
                'social' => array(
                    'facebook' => '#',
                    'instagram' => '#'
                ),
                'stats' => array(
                    'sold' => 76,
                    'years' => 6,
                    'rating' => 5
                ),
                'link' => '#'
            )
        );
    }
}

// Early return if no agents and no demo data
if (empty($agents)) {
    return;
}

// Build section styles
$section_styles = array(
    'position: relative',
    'width: 100%'
);

// Background styles with null handling
switch ($background) {
    case 'light':
        $section_styles[] = 'background-color: var(--hph-gray-50)';
        $section_styles[] = 'color: var(--hph-text-color)';
        break;
    case 'dark':
        $section_styles[] = 'background-color: var(--hph-gray-900)';
        $section_styles[] = 'color: var(--hph-white)';
        break;
    case 'primary':
        $section_styles[] = 'background-color: var(--hph-primary)';
        $section_styles[] = 'color: var(--hph-white)';
        break;
    case 'secondary':
        $section_styles[] = 'background-color: var(--hph-secondary)';
        $section_styles[] = 'color: var(--hph-white)';
        break;
    case 'gradient':
        $section_styles[] = 'background: var(--hph-gradient-primary)';
        $section_styles[] = 'color: var(--hph-white)';
        break;
    case 'white':
    default:
        $section_styles[] = 'background-color: var(--hph-white)';
        $section_styles[] = 'color: var(--hph-text-color)';
        break;
}

// Padding styles
switch ($padding) {
    case 'sm':
        $section_styles[] = 'padding-top: var(--hph-space-6)';
        $section_styles[] = 'padding-bottom: var(--hph-space-6)';
        break;
    case 'md':
        $section_styles[] = 'padding-top: var(--hph-space-8)';
        $section_styles[] = 'padding-bottom: var(--hph-space-8)';
        break;
    case 'lg':
        $section_styles[] = 'padding-top: var(--hph-space-12)';
        $section_styles[] = 'padding-bottom: var(--hph-space-12)';
        break;
    case '2xl':
        $section_styles[] = 'padding-top: var(--hph-space-24)';
        $section_styles[] = 'padding-bottom: var(--hph-space-24)';
        break;
    case 'xl':
    default:
        $section_styles[] = 'padding-top: var(--hph-space-16)';
        $section_styles[] = 'padding-bottom: var(--hph-space-16)';
        break;
}

// Container styles
$container_styles = array(
    'position: relative',
    'margin-left: auto',
    'margin-right: auto',
    'padding-left: var(--hph-space-6)',
    'padding-right: var(--hph-space-6)'
);

// Content width
switch ($content_width) {
    case 'narrow':
        $container_styles[] = 'max-width: var(--hph-container-sm)';
        break;
    case 'wide':
        $container_styles[] = 'max-width: var(--hph-container-2xl)';
        break;
    case 'full':
        $container_styles[] = 'max-width: 100%';
        $container_styles[] = 'padding-left: 0';
        $container_styles[] = 'padding-right: 0';
        break;
    case 'normal':
    default:
        $container_styles[] = 'max-width: var(--hph-container-xl)';
        break;
}

// Grid styles based on layout
$grid_styles = array();
if ($layout === 'grid') {
    $grid_styles[] = 'display: grid';
    $grid_styles[] = 'gap: var(--hph-gap-xl)';
    
    switch ($columns) {
        case 2:
            $grid_styles[] = 'grid-template-columns: repeat(auto-fit, minmax(350px, 1fr))';
            break;
        case 4:
            $grid_styles[] = 'grid-template-columns: repeat(auto-fit, minmax(250px, 1fr))';
            break;
        case 3:
        default:
            $grid_styles[] = 'grid-template-columns: repeat(auto-fit, minmax(300px, 1fr))';
            break;
    }
} elseif ($layout === 'list') {
    $grid_styles[] = 'display: flex';
    $grid_styles[] = 'flex-direction: column';
    $grid_styles[] = 'gap: var(--hph-gap-2xl)';
} elseif ($layout === 'featured') {
    $grid_styles[] = 'display: grid';
    $grid_styles[] = 'grid-template-columns: 2fr 1fr';
    $grid_styles[] = 'gap: var(--hph-gap-2xl)';
    $grid_styles[] = 'align-items: start';
}

// Color scheme for text on background
$is_dark_bg = in_array($background, ['dark', 'primary', 'gradient', 'secondary']);
$heading_color = $is_dark_bg ? 'var(--hph-white)' : 'var(--hph-primary-800)';
$text_color = $is_dark_bg ? 'rgba(255, 255, 255, 0.9)' : 'var(--hph-gray-700)';
$muted_color = $is_dark_bg ? 'rgba(255, 255, 255, 0.7)' : 'var(--hph-gray-500)';
?>

<section 
    class="hph-agents-section"
    <?php if ($section_id): ?>id="<?php echo esc_attr($section_id); ?>"<?php endif; ?>
    data-bg="<?php echo esc_attr($background); ?>"
    style="<?php echo implode('; ', $section_styles); ?>"
    data-animation="<?php echo $animation ? 'true' : 'false'; ?>"
>
    <div style="<?php echo implode('; ', $container_styles); ?>">
        
        <?php if ($headline || $subheadline): ?>
        <div style="text-align: center; margin-bottom: var(--hph-space-16); <?php echo $animation ? 'animation: fadeInUp 0.8s ease-out;' : ''; ?>">
            <?php if ($headline): ?>
            <h2 style="margin: 0 0 var(--hph-space-6) 0; font-size: var(--hph-text-4xl); font-weight: var(--hph-font-bold); line-height: var(--hph-leading-tight); color: <?php echo $heading_color; ?>;">
                <?php echo esc_html($headline); ?>
            </h2>
            <?php endif; ?>
            
            <?php if ($subheadline): ?>
            <p style="font-size: var(--hph-text-xl); font-weight: var(--hph-font-medium); opacity: 0.9; color: <?php echo $text_color; ?>; max-width: 600px; margin: 0 auto; line-height: var(--hph-leading-relaxed);">
                <?php echo esc_html($subheadline); ?>
            </p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <div style="<?php echo implode('; ', $grid_styles); ?>">
            
            <?php if ($layout === 'featured' && !empty($agents[0])): 
                // Featured agent (first one)
                $featured = $agents[0];
            ?>
            <!-- Featured Agent -->
            <div style="background: var(--hph-white); border-radius: var(--hph-radius-xl); overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.1); <?php echo $animation ? 'animation: fadeInUp 0.8s ease-out;' : ''; ?>">
                <div style="display: grid; grid-template-columns: 1fr 1fr; min-height: 500px;">
                    <?php if (!empty($featured['image'])): ?>
                    <div style="background-image: url('<?php echo esc_url($featured['image']); ?>'); background-size: cover; background-position: center;"></div>
                    <?php endif; ?>
                    
                    <div style="padding: var(--hph-space-12); display: flex; flex-direction: column; justify-content: center;">
                        <h3 style="margin: 0 0 var(--hph-space-2) 0; font-size: var(--hph-text-3xl); font-weight: var(--hph-font-bold); color: var(--hph-primary-800);">
                            <?php echo esc_html($featured['name']); ?>
                        </h3>
                        <p style="margin: 0 0 var(--hph-space-6) 0; color: var(--hph-primary-600); font-weight: var(--hph-font-medium);">
                            <?php echo esc_html($featured['title']); ?>
                        </p>
                        
                        <?php if ($show_bio && !empty($featured['bio'])): ?>
                        <p style="margin: 0 0 var(--hph-space-8) 0; color: var(--hph-gray-600); line-height: var(--hph-leading-relaxed);">
                            <?php echo esc_html($featured['bio']); ?>
                        </p>
                        <?php endif; ?>
                        
                        <?php if ($show_stats && !empty($featured['stats'])): ?>
                        <div style="display: flex; gap: var(--hph-gap-xl); margin-bottom: var(--hph-space-8);">
                            <?php if (!empty($featured['stats']['sold'])): ?>
                            <div>
                                <div style="font-size: var(--hph-text-2xl); font-weight: var(--hph-font-bold); color: var(--hph-primary);">
                                    <?php echo esc_html($featured['stats']['sold']); ?>
                                </div>
                                <div style="font-size: var(--hph-text-sm); color: var(--hph-gray-500);">Properties Sold</div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($featured['stats']['years'])): ?>
                            <div>
                                <div style="font-size: var(--hph-text-2xl); font-weight: var(--hph-font-bold); color: var(--hph-primary);">
                                    <?php echo esc_html($featured['stats']['years']); ?>
                                </div>
                                <div style="font-size: var(--hph-text-sm); color: var(--hph-gray-500);">Years Experience</div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($show_button && !empty($featured['link'])): ?>
                        <a href="<?php echo esc_url($featured['link']); ?>" 
                           style="display: inline-flex; align-items: center; padding: var(--hph-space-4) var(--hph-space-8); background: var(--hph-primary); color: var(--hph-white); text-decoration: none; border-radius: var(--hph-radius-lg); font-weight: var(--hph-font-semibold); transition: all 300ms ease; max-width: fit-content;"
                           onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(81, 186, 224, 0.3)';"
                           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                            View Profile
                            <svg style="width: 20px; height: 20px; margin-left: var(--hph-space-2);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Other Agents Grid -->
            <div style="display: grid; gap: var(--hph-gap-lg); grid-template-rows: repeat(<?php echo count($agents) - 1; ?>, 1fr);">
                <?php 
                $other_agents = array_slice($agents, 1);
                foreach ($other_agents as $index => $agent): 
                ?>
                <div style="display: flex; gap: var(--hph-gap-lg); padding: var(--hph-space-6); background: var(--hph-white); border-radius: var(--hph-radius-lg); box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 300ms ease; <?php echo $animation ? 'animation: fadeInUp 0.8s ease-out ' . (($index + 1) * 0.1) . 's; opacity: 0; animation-fill-mode: forwards;' : ''; ?>"
                     onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.12)';"
                     onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)';">
                    <?php 
                    // Enhanced image handling with fallbacks
                    $image_url = '';
                    if (!empty($agent['image'])) {
                        $image_url = $agent['image'];
                    } elseif (function_exists('hph_get_image_url')) {
                        $image_url = hph_get_image_url('agent-placeholder.jpg');
                    } else {
                        $image_url = hph_get_image_url_only('assets/images/agent-placeholder.jpg');
                    }
                    ?>
                    <?php if ($image_url): ?>
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($agent['name']); ?>" 
                         style="width: 80px; height: 80px; border-radius: var(--hph-radius-full); object-fit: cover; flex-shrink: 0;"
                         onerror="this.src='<?php echo esc_url(hph_get_image_url_only('assets/images/agent-placeholder.jpg')); ?>';">
                    <?php endif; ?>
                    <div style="flex: 1; min-width: 0;">
                        <h4 style="margin: 0 0 var(--hph-space-1) 0; font-size: var(--hph-text-lg); font-weight: var(--hph-font-semibold); color: var(--hph-primary-800);">
                            <?php echo esc_html($agent['name']); ?>
                        </h4>
                        <p style="margin: 0; color: var(--hph-gray-600); font-size: var(--hph-text-sm);">
                            <?php echo esc_html($agent['title']); ?>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php elseif ($layout === 'list'): ?>
            <!-- List Layout -->
            <?php foreach ($agents as $index => $agent): ?>
            <div style="display: flex; gap: var(--hph-gap-xl); padding: var(--hph-space-8); background: var(--hph-white); border-radius: var(--hph-radius-lg); box-shadow: 0 2px 8px rgba(0,0,0,0.08); align-items: center; transition: all 300ms ease; <?php echo $animation ? 'animation: fadeInUp 0.8s ease-out ' . ($index * 0.1) . 's; opacity: 0; animation-fill-mode: forwards;' : ''; ?>"
                 onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.12)';"
                 onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)';">
                <?php 
                // Enhanced image handling with fallbacks
                $image_url = '';
                if (!empty($agent['image'])) {
                    $image_url = $agent['image'];
                } elseif (function_exists('hph_get_image_url')) {
                    $image_url = hph_get_image_url('agent-placeholder.jpg');
                } else {
                    $image_url = hph_get_image_url_only('assets/images/agent-placeholder.jpg');
                }
                ?>
                <?php if ($image_url): ?>
                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($agent['name']); ?>" 
                     style="width: 200px; height: 200px; border-radius: var(--hph-radius-lg); object-fit: cover; flex-shrink: 0;"
                     onerror="this.src='<?php echo esc_url(hph_get_image_url_only('assets/images/agent-placeholder.jpg')); ?>';">
                <?php endif; ?>
                
                <div style="flex: 1; min-width: 0;">
                    <h3 style="margin: 0 0 var(--hph-space-2) 0; font-size: var(--hph-text-2xl); font-weight: var(--hph-font-bold); color: var(--hph-primary-800);">
                        <?php echo esc_html($agent['name']); ?>
                    </h3>
                    <p style="margin: 0 0 var(--hph-space-4) 0; color: var(--hph-primary-600); font-weight: var(--hph-font-medium);">
                        <?php echo esc_html($agent['title']); ?>
                    </p>
                    
                    <?php if ($show_bio && !empty($agent['bio'])): ?>
                    <p style="margin: 0 0 var(--hph-space-6) 0; color: var(--hph-gray-600); line-height: var(--hph-leading-relaxed);">
                        <?php echo esc_html($agent['bio']); ?>
                    </p>
                    <?php endif; ?>
                    
                    <div style="display: flex; gap: var(--hph-gap-xl); align-items: center; flex-wrap: wrap;">
                        <?php if ($show_contact): ?>
                            <?php if (!empty($agent['phone'])): ?>
                            <a href="tel:<?php echo esc_attr($agent['phone']); ?>" style="color: var(--hph-gray-600); text-decoration: none; display: flex; align-items: center; gap: var(--hph-gap-sm); font-size: var(--hph-text-sm);">
                                <svg style="width: 16px; height: 16px;" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path>
                                </svg>
                                <?php echo esc_html($agent['phone']); ?>
                            </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($agent['email'])): ?>
                            <a href="mailto:<?php echo esc_attr($agent['email']); ?>" style="color: var(--hph-gray-600); text-decoration: none; display: flex; align-items: center; gap: var(--hph-gap-sm); font-size: var(--hph-text-sm);">
                                <svg style="width: 16px; height: 16px;" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                                </svg>
                                Email
                            </a>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php if ($show_button && !empty($agent['link'])): ?>
                        <a href="<?php echo esc_url($agent['link']); ?>" style="color: var(--hph-primary); font-weight: var(--hph-font-medium); text-decoration: none; font-size: var(--hph-text-sm);">
                            View Profile →
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php else: ?>
            <!-- Grid Layout (Default) -->
            <?php foreach ($agents as $index => $agent): ?>
            <div style="background: var(--hph-white); border-radius: var(--hph-radius-lg); overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 300ms ease; <?php echo $animation ? 'animation: fadeInUp 0.8s ease-out ' . ($index * 0.1) . 's; opacity: 0; animation-fill-mode: forwards;' : ''; ?>"
                 onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 24px rgba(0,0,0,0.12)';"
                 onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)';">
                
                <?php 
                // Enhanced image handling with fallbacks
                $image_url = '';
                if (!empty($agent['image'])) {
                    $image_url = $agent['image'];
                } elseif (function_exists('hph_get_image_url')) {
                    $image_url = hph_get_image_url('agent-placeholder.jpg');
                } else {
                    $image_url = hph_get_image_url_only('assets/images/agent-placeholder.jpg');
                }
                ?>
                <?php if ($image_url): ?>
                <div style="aspect-ratio: 1; overflow: hidden;">
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($agent['name']); ?>" 
                         style="width: 100%; height: 100%; object-fit: cover; transition: transform 300ms ease;"
                         onmouseover="this.style.transform='scale(1.05)'"
                         onmouseout="this.style.transform='scale(1)'"
                         onerror="this.src='<?php echo esc_url(hph_get_image_url_only('assets/images/agent-placeholder.jpg')); ?>';">
                </div>
                <?php endif; ?>
                
                <div style="padding: var(--hph-space-8);">
                    <h3 style="margin: 0 0 var(--hph-space-1) 0; font-size: var(--hph-text-xl); font-weight: var(--hph-font-bold); color: var(--hph-primary-800);">
                        <?php echo esc_html($agent['name']); ?>
                    </h3>
                    <p style="margin: 0 0 var(--hph-space-4) 0; color: var(--hph-primary-600); font-size: var(--hph-text-sm); font-weight: var(--hph-font-medium);">
                        <?php echo esc_html($agent['title']); ?>
                    </p>
                    
                    <?php if ($show_bio && !empty($agent['bio'])): ?>
                    <p style="margin: 0 0 var(--hph-space-6) 0; color: var(--hph-gray-600); font-size: var(--hph-text-sm); line-height: var(--hph-leading-relaxed);">
                        <?php echo esc_html(wp_trim_words($agent['bio'], 15)); ?>
                    </p>
                    <?php endif; ?>
                    
                    <?php if ($show_contact && (!empty($agent['phone']) || !empty($agent['email']))): ?>
                    <div style="padding-top: var(--hph-space-4); border-top: 1px solid var(--hph-gray-100); margin-bottom: var(--hph-space-4);">
                        <?php if (!empty($agent['phone'])): ?>
                        <a href="tel:<?php echo esc_attr($agent['phone']); ?>" style="display: block; color: var(--hph-gray-600); text-decoration: none; font-size: var(--hph-text-sm); margin-bottom: var(--hph-space-2);">
                            <?php echo esc_html($agent['phone']); ?>
                        </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($agent['email'])): ?>
                        <a href="mailto:<?php echo esc_attr($agent['email']); ?>" style="display: block; color: var(--hph-gray-600); text-decoration: none; font-size: var(--hph-text-sm);">
                            <?php echo esc_html($agent['email']); ?>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($show_social && !empty($agent['social'])): ?>
                    <div style="display: flex; gap: var(--hph-gap-sm); margin-bottom: var(--hph-space-6);">
                        <?php foreach ($agent['social'] as $platform => $url): 
                            if (!empty($url)):
                        ?>
                        <a href="<?php echo esc_url($url); ?>" 
                           style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; background: var(--hph-gray-100); border-radius: var(--hph-radius-full); color: var(--hph-gray-600); transition: all 300ms ease; text-decoration: none;"
                           onmouseover="this.style.background='var(--hph-primary)'; this.style.color='var(--hph-white)';"
                           onmouseout="this.style.background='var(--hph-gray-100)'; this.style.color='var(--hph-gray-600)';">
                            <i class="fab fa-<?php echo esc_attr($platform); ?>" style="font-size: 14px;"></i>
                        </a>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($show_button && !empty($agent['link'])): ?>
                    <a href="<?php echo esc_url($agent['link']); ?>" 
                       style="display: block; text-align: center; padding: var(--hph-space-2) var(--hph-space-4); background: var(--hph-primary); color: var(--hph-white); text-decoration: none; border-radius: var(--hph-radius-md); font-weight: var(--hph-font-medium); transition: all 300ms ease; font-size: var(--hph-text-sm);"
                       onmouseover="this.style.background='var(--hph-primary-600)';"
                       onmouseout="this.style.background='var(--hph-primary)';">
                        View Profile
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
            
        </div>
    </div>
</section>

<?php if ($animation): ?>
<style>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
<?php endif; ?>
