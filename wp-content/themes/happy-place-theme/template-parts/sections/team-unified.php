<?php
/**
 * HPH Unified Team Section Template
 * 
 * Displays both Agents and Staff in a unified team layout with role badges,
 * smart data handling, and professional presentation
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Register this template part for asset loading
if (function_exists('hph_register_template_part')) {
    hph_register_template_part('sections/team-unified');
}

// Default arguments
$defaults = array(
    'style' => 'cards', // Options: 'cards', 'grid', 'carousel', 'list'
    'theme' => 'light', // Color theme: 'white', 'light', 'dark', 'primary'
    'columns' => 3, // Number of columns for grid
    'padding' => 'xl',
    'content_width' => 'normal',
    'alignment' => 'center',
    'badge' => 'Meet Our Team',
    'headline' => 'The People Behind Your Success',
    'subheadline' => 'Our experienced team of real estate professionals and support staff',
    'content' => '',
    
    // Team member options
    'include_agents' => true,
    'include_staff' => true,
    'agents_count' => 8,
    'staff_count' => 6,
    'featured_only' => false,
    'group_by_role' => true, // Group agents and staff separately
    'show_role_badges' => true,
    'show_contact_info' => true,
    'show_social_links' => true,
    'show_bio_preview' => true,
    'show_specialties' => true,
    
    // Display options
    'image_style' => 'rounded', // Options: 'rounded', 'circle', 'square'
    'card_style' => 'elevated', // Options: 'elevated', 'outlined', 'minimal'
    'animation' => true,
    'hover_effects' => true,
    'section_id' => ''
);

// Merge with provided args
$config = wp_parse_args($args ?? array(), $defaults);
extract($config);

// Build section styles
$section_styles = array(
    'position: relative',
    'width: 100%'
);

// Theme-based styling
switch ($theme) {
    case 'white':
        $section_styles[] = 'background-color: var(--hph-white)';
        $section_styles[] = 'color: var(--hph-text-color)';
        break;
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
}

// Padding styles
switch ($padding) {
    case 'sm':
        $section_styles[] = 'padding-top: var(--hph-padding-lg)';
        $section_styles[] = 'padding-bottom: var(--hph-padding-lg)';
        break;
    case 'md':
        $section_styles[] = 'padding-top: var(--hph-padding-xl)';
        $section_styles[] = 'padding-bottom: var(--hph-padding-xl)';
        break;
    case 'lg':
        $section_styles[] = 'padding-top: var(--hph-padding-2xl)';
        $section_styles[] = 'padding-bottom: var(--hph-padding-2xl)';
        break;
    case '2xl':
        $section_styles[] = 'padding-top: var(--hph-padding-4xl)';
        $section_styles[] = 'padding-bottom: var(--hph-padding-4xl)';
        break;
    case 'xl':
    default:
        $section_styles[] = 'padding-top: var(--hph-padding-3xl)';
        $section_styles[] = 'padding-bottom: var(--hph-padding-3xl)';
        break;
}

// Container styles
$container_styles = array(
    'position: relative',
    'margin-left: auto',
    'margin-right: auto',
    'padding-left: var(--hph-padding-lg)',
    'padding-right: var(--hph-padding-lg)'
);

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

// Text alignment for header
$header_alignment = '';
switch ($alignment) {
    case 'left':
        $header_alignment = 'text-align: left';
        break;
    case 'right':
        $header_alignment = 'text-align: right';
        break;
    case 'center':
    default:
        $header_alignment = 'text-align: center';
        break;
}

// Grid styles based on layout
$grid_styles = array();

switch ($style) {
    case 'cards':
    case 'grid':
        $grid_styles[] = 'display: grid';
        $grid_styles[] = 'gap: var(--hph-gap-xl)';
        
        switch ($columns) {
            case 2:
                $grid_styles[] = 'grid-template-columns: repeat(auto-fit, minmax(350px, 1fr))';
                break;
            case 4:
                $grid_styles[] = 'grid-template-columns: repeat(auto-fit, minmax(280px, 1fr))';
                break;
            case 5:
                $grid_styles[] = 'grid-template-columns: repeat(auto-fit, minmax(240px, 1fr))';
                break;
            case 3:
            default:
                $grid_styles[] = 'grid-template-columns: repeat(auto-fit, minmax(320px, 1fr))';
                break;
        }
        break;
    case 'list':
        $grid_styles[] = 'display: flex';
        $grid_styles[] = 'flex-direction: column';
        $grid_styles[] = 'gap: var(--hph-gap-2xl)';
        break;
    case 'carousel':
        $grid_styles[] = 'display: flex';
        $grid_styles[] = 'gap: var(--hph-gap-xl)';
        $grid_styles[] = 'overflow-x: auto';
        $grid_styles[] = 'scroll-snap-type: x mandatory';
        $grid_styles[] = 'padding-bottom: var(--hph-padding-sm)';
        break;
}

/**
 * Fetch and normalize team members from both Agents and Staff post types
 */
function hph_get_unified_team_members($include_agents, $include_staff, $agents_count, $staff_count, $featured_only) {
    $team_members = array();
    
    // Fetch Agents
    if ($include_agents) {
        $agent_args = array(
            'post_type' => 'agent',
            'post_status' => 'publish',
            'posts_per_page' => $agents_count,
            'orderby' => 'menu_order',
            'order' => 'ASC'
        );
        
        if ($featured_only) {
            $agent_args['meta_query'] = array(
                array(
                    'key' => 'featured',
                    'value' => '1',
                    'compare' => '='
                )
            );
        }
        
        $agents = get_posts($agent_args);
        
        foreach ($agents as $agent) {
            $first_name = get_field('first_name', $agent->ID);
            $last_name = get_field('last_name', $agent->ID);
            $full_name = trim($first_name . ' ' . $last_name) ?: $agent->post_title;
            
            $profile_photo = get_field('profile_photo', $agent->ID);
            $photo_url = '';
            if ($profile_photo) {
                if (is_array($profile_photo)) {
                    $photo_url = $profile_photo['sizes']['medium'] ?? $profile_photo['url'];
                } elseif (is_numeric($profile_photo)) {
                    $photo_url = wp_get_attachment_image_url($profile_photo, 'medium');
                }
            }
            
            if (!$photo_url) {
                $photo_url = get_the_post_thumbnail_url($agent->ID, 'medium');
            }
            
            $specialties = get_field('specialties', $agent->ID);
            $specialties_list = '';
            if (is_array($specialties)) {
                $specialties_list = implode(', ', array_map(function($spec) {
                    return is_array($spec) ? $spec['label'] : $spec;
                }, array_slice($specialties, 0, 3)));
            }
            
            $team_members[] = array(
                'id' => $agent->ID,
                'name' => $full_name,
                'title' => get_field('title', $agent->ID) ?: 'Real Estate Agent',
                'role' => 'agent',
                'role_label' => 'Agent',
                'image' => $photo_url,
                'bio' => wp_trim_words(strip_tags(get_field('bio', $agent->ID)), 25),
                'email' => get_field('email', $agent->ID),
                'phone' => get_field('phone', $agent->ID),
                'years_experience' => get_field('years_experience', $agent->ID),
                'specialties' => $specialties_list,
                'featured' => get_field('featured', $agent->ID),
                'social' => array(
                    'linkedin' => get_field('linkedin', $agent->ID),
                    'twitter' => get_field('twitter', $agent->ID),
                    'facebook' => get_field('facebook', $agent->ID),
                    'instagram' => get_field('instagram', $agent->ID)
                ),
                'link' => get_permalink($agent->ID),
                'post_type' => 'agent'
            );
        }
    }
    
    // Fetch Staff
    if ($include_staff) {
        $staff_args = array(
            'post_type' => 'staff',
            'post_status' => 'publish',
            'posts_per_page' => $staff_count,
            'meta_query' => array(
                array(
                    'key' => 'show_on_team_page',
                    'value' => '1',
                    'compare' => '='
                ),
                array(
                    'key' => 'status',
                    'value' => 'active',
                    'compare' => '='
                )
            ),
            'meta_key' => 'display_order',
            'orderby' => 'meta_value_num',
            'order' => 'ASC'
        );
        
        if ($featured_only) {
            $staff_args['meta_query'][] = array(
                'key' => 'featured',
                'value' => '1',
                'compare' => '='
            );
        }
        
        $staff_members = get_posts($staff_args);
        
        foreach ($staff_members as $staff) {
            $first_name = get_field('first_name', $staff->ID);
            $last_name = get_field('last_name', $staff->ID);
            $full_name = trim($first_name . ' ' . $last_name) ?: $staff->post_title;
            
            $profile_photo = get_field('profile_photo', $staff->ID);
            $photo_url = '';
            if ($profile_photo) {
                if (is_array($profile_photo)) {
                    $photo_url = $profile_photo['sizes']['medium'] ?? $profile_photo['url'];
                } elseif (is_numeric($profile_photo)) {
                    $photo_url = wp_get_attachment_image_url($profile_photo, 'medium');
                }
            }
            
            if (!$photo_url) {
                $photo_url = get_the_post_thumbnail_url($staff->ID, 'medium');
            }
            
            $specialties = get_field('specialties', $staff->ID);
            $specialties_list = '';
            if ($specialties) {
                $specialties_array = array_map('trim', explode("\n", $specialties));
                $specialties_list = implode(', ', array_slice($specialties_array, 0, 3));
            }
            
            $department = get_field('department', $staff->ID);
            $department_label = '';
            if ($department) {
                $department_labels = array(
                    'administration' => 'Administration',
                    'marketing' => 'Marketing',
                    'operations' => 'Operations',
                    'customer_service' => 'Customer Service',
                    'finance' => 'Finance',
                    'it' => 'IT / Technology',
                    'legal' => 'Legal',
                    'human_resources' => 'Human Resources',
                    'other' => 'Other'
                );
                $department_label = $department_labels[$department] ?? ucfirst($department);
            }
            
            $team_members[] = array(
                'id' => $staff->ID,
                'name' => $full_name,
                'title' => get_field('job_title', $staff->ID) ?: 'Staff Member',
                'role' => 'staff',
                'role_label' => $department_label ?: 'Staff',
                'image' => $photo_url,
                'bio' => wp_trim_words(strip_tags(get_field('bio', $staff->ID)), 25),
                'email' => get_field('show_contact_publicly', $staff->ID) ? get_field('email', $staff->ID) : '',
                'phone' => get_field('show_contact_publicly', $staff->ID) ? get_field('phone', $staff->ID) : '',
                'years_experience' => get_field('years_experience', $staff->ID),
                'specialties' => $specialties_list,
                'featured' => get_field('featured', $staff->ID),
                'social' => array(
                    'linkedin' => get_field('linkedin', $staff->ID),
                    'twitter' => get_field('twitter', $staff->ID),
                    'facebook' => get_field('facebook', $staff->ID),
                    'instagram' => get_field('instagram', $staff->ID)
                ),
                'link' => '', // Staff don't have single pages typically
                'post_type' => 'staff'
            );
        }
    }
    
    return $team_members;
}

// Get team members
$team_members = hph_get_unified_team_members($include_agents, $include_staff, $agents_count, $staff_count, $featured_only);

// Group by role if requested
if ($group_by_role && $include_agents && $include_staff) {
    $agents = array_filter($team_members, function($member) { return $member['role'] === 'agent'; });
    $staff = array_filter($team_members, function($member) { return $member['role'] === 'staff'; });
} else {
    // Sort by featured first, then by role
    usort($team_members, function($a, $b) {
        if ($a['featured'] !== $b['featured']) {
            return $b['featured'] - $a['featured']; // Featured first
        }
        if ($a['role'] !== $b['role']) {
            return $a['role'] === 'agent' ? -1 : 1; // Agents before staff
        }
        return 0;
    });
}

/**
 * Render a team member card
 */
function hph_render_team_member($member, $index, $style, $card_style, $image_style, $show_role_badges, $show_contact_info, $show_social_links, $show_bio_preview, $show_specialties, $hover_effects, $animation) {
    // Build member item styles
    $item_styles = array('position: relative');
    
    switch ($style) {
        case 'cards':
            switch ($card_style) {
                case 'elevated':
                    $item_styles[] = 'background: var(--hph-white)';
                    $item_styles[] = 'border-radius: var(--hph-radius-xl)';
                    $item_styles[] = 'box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1)';
                    $item_styles[] = 'padding: var(--hph-padding-xl)';
                    $item_styles[] = 'transition: all 300ms ease';
                    break;
                case 'outlined':
                    $item_styles[] = 'background: transparent';
                    $item_styles[] = 'border: 2px solid var(--hph-gray-200)';
                    $item_styles[] = 'border-radius: var(--hph-radius-xl)';
                    $item_styles[] = 'padding: var(--hph-padding-xl)';
                    $item_styles[] = 'transition: all 300ms ease';
                    break;
                case 'minimal':
                    $item_styles[] = 'padding: var(--hph-padding-lg)';
                    $item_styles[] = 'transition: all 300ms ease';
                    break;
            }
            $item_styles[] = 'text-align: center';
            break;
        case 'list':
            $item_styles[] = 'display: flex';
            $item_styles[] = 'align-items: flex-start';
            $item_styles[] = 'gap: var(--hph-gap-xl)';
            $item_styles[] = 'text-align: left';
            $item_styles[] = 'padding: var(--hph-padding-lg)';
            $item_styles[] = 'border-radius: var(--hph-radius-lg)';
            $item_styles[] = 'transition: all 300ms ease';
            break;
        case 'carousel':
            $item_styles[] = 'min-width: 320px';
            $item_styles[] = 'scroll-snap-align: start';
            $item_styles[] = 'text-align: center';
            $item_styles[] = 'padding: var(--hph-padding-lg)';
            break;
        default:
            $item_styles[] = 'text-align: center';
            $item_styles[] = 'padding: var(--hph-padding-lg)';
    }
    
    // Image styles
    $img_styles = array(
        'width: 100%',
        'height: auto',
        'object-fit: cover',
        'transition: all 300ms ease'
    );
    
    switch ($image_style) {
        case 'circle':
            $img_styles[] = 'border-radius: var(--hph-radius-full)';
            $img_styles[] = 'aspect-ratio: 1';
            break;
        case 'square':
            $img_styles[] = 'border-radius: var(--hph-radius-lg)';
            $img_styles[] = 'aspect-ratio: 1';
            break;
        case 'rounded':
        default:
            $img_styles[] = 'border-radius: var(--hph-radius-xl)';
            $img_styles[] = 'aspect-ratio: 4/5';
            break;
    }
    
    // Hover effects
    $hover_attributes = '';
    if ($hover_effects) {
        switch ($card_style) {
            case 'elevated':
                $hover_attributes = 'onmouseover="this.style.transform=\'translateY(-8px)\'; this.style.boxShadow=\'0 20px 40px rgba(0, 0, 0, 0.15)\';" onmouseout="this.style.transform=\'translateY(0)\'; this.style.boxShadow=\'0 8px 25px rgba(0, 0, 0, 0.1)\';"';
                break;
            case 'outlined':
                $hover_attributes = 'onmouseover="this.style.borderColor=\'var(--hph-primary)\'; this.style.transform=\'translateY(-4px)\'; this.style.boxShadow=\'0 8px 25px rgba(0, 0, 0, 0.1)\';" onmouseout="this.style.borderColor=\'var(--hph-gray-200)\'; this.style.transform=\'translateY(0)\'; this.style.boxShadow=\'none\';"';
                break;
            default:
                $hover_attributes = 'onmouseover="this.style.transform=\'translateY(-4px)\';" onmouseout="this.style.transform=\'translateY(0)\';"';
        }
    }
    
    $animation_delay = $animation ? 'animation: fadeInUp 0.8s ease-out ' . ($index * 0.1) . 's; opacity: 0; animation-fill-mode: forwards;' : '';
    ?>
    
    <div 
        class="hph-team-member hph-team-member-<?php echo esc_attr($member['role']); ?>"
        style="<?php echo implode('; ', $item_styles); ?> <?php echo $animation_delay; ?>"
        <?php echo $hover_attributes; ?>
        data-member-id="<?php echo esc_attr($member['id']); ?>"
        data-member-type="<?php echo esc_attr($member['post_type']); ?>"
    >
        
        <?php if ($show_role_badges): ?>
        <!-- Role Badge -->
        <div style="position: absolute; top: var(--hph-padding-sm); right: var(--hph-padding-sm); z-index: 10;">
            <span style="display: inline-block; padding: var(--hph-padding-xs) var(--hph-padding-sm); background: <?php echo $member['role'] === 'agent' ? 'var(--hph-primary)' : 'var(--hph-secondary)'; ?>; color: var(--hph-white); border-radius: var(--hph-radius-full); font-size: var(--hph-text-xs); font-weight: var(--hph-font-semibold); text-transform: uppercase; letter-spacing: 0.05em;">
                <?php echo esc_html($member['role_label']); ?>
            </span>
        </div>
        <?php endif; ?>
        
        <?php if ($member['featured']): ?>
        <!-- Featured Badge -->
        <div style="position: absolute; top: var(--hph-padding-sm); left: var(--hph-padding-sm); z-index: 10;">
            <span style="display: inline-flex; align-items: center; padding: var(--hph-padding-xs) var(--hph-padding-sm); background: var(--hph-accent); color: var(--hph-white); border-radius: var(--hph-radius-full); font-size: var(--hph-text-xs); font-weight: var(--hph-font-semibold);">
                <i class="fas fa-star" style="margin-right: var(--hph-margin-xs); font-size: 10px;"></i>
                Featured
            </span>
        </div>
        <?php endif; ?>
        
        <?php if ($member['image']): ?>
        <!-- Member Image -->
        <div style="<?php echo $style === 'list' ? 'flex-shrink: 0; width: 150px;' : 'margin-bottom: var(--hph-margin-lg);'; ?>">
            <?php if ($member['link']): ?>
            <a href="<?php echo esc_url($member['link']); ?>" style="display: block; text-decoration: none;">
            <?php endif; ?>
                <img 
                    src="<?php echo esc_url($member['image']); ?>" 
                    alt="<?php echo esc_attr($member['name']); ?>"
                    style="<?php echo implode('; ', $img_styles); ?>"
                    loading="lazy"
                >
            <?php if ($member['link']): ?>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Member Details -->
        <div style="<?php echo $style === 'list' ? 'flex: 1;' : ''; ?>">
            
            <!-- Name -->
            <h3 style="margin: 0 0 var(--hph-margin-xs) 0; font-size: var(--hph-text-xl); font-weight: var(--hph-font-bold); line-height: var(--hph-leading-tight);">
                <?php if ($member['link']): ?>
                <a href="<?php echo esc_url($member['link']); ?>" style="color: inherit; text-decoration: none;">
                    <?php echo esc_html($member['name']); ?>
                </a>
                <?php else: ?>
                <?php echo esc_html($member['name']); ?>
                <?php endif; ?>
            </h3>
            
            <!-- Title -->
            <p style="margin: 0 0 var(--hph-margin-sm) 0; font-size: var(--hph-text-base); font-weight: var(--hph-font-medium); color: var(--hph-primary); opacity: 0.9;">
                <?php echo esc_html($member['title']); ?>
            </p>
            
            <?php if ($member['years_experience']): ?>
            <!-- Experience -->
            <p style="margin: 0 0 var(--hph-margin-sm) 0; font-size: var(--hph-text-sm); color: var(--hph-gray-600); font-style: italic;">
                <?php echo esc_html($member['years_experience']); ?> years experience
            </p>
            <?php endif; ?>
            
            <?php if ($show_bio_preview && $member['bio']): ?>
            <!-- Bio Preview -->
            <p style="margin: 0 0 var(--hph-margin-md) 0; color: var(--hph-gray-600); line-height: var(--hph-leading-relaxed); font-size: var(--hph-text-sm);">
                <?php echo esc_html($member['bio']); ?>
            </p>
            <?php endif; ?>
            
            <?php if ($show_specialties && $member['specialties']): ?>
            <!-- Specialties -->
            <div style="margin: 0 0 var(--hph-margin-md) 0;">
                <p style="margin: 0 0 var(--hph-margin-xs) 0; font-size: var(--hph-text-xs); font-weight: var(--hph-font-semibold); text-transform: uppercase; color: var(--hph-gray-500); letter-spacing: 0.05em;">
                    Specialties
                </p>
                <p style="margin: 0; font-size: var(--hph-text-sm); color: var(--hph-gray-600); line-height: var(--hph-leading-relaxed);">
                    <?php echo esc_html($member['specialties']); ?>
                </p>
            </div>
            <?php endif; ?>
            
            <?php if ($show_contact_info && ($member['email'] || $member['phone'])): ?>
            <!-- Contact Info -->
            <div style="margin-bottom: var(--hph-margin-md); font-size: var(--hph-text-sm);">
                <?php if ($member['email']): ?>
                <p style="margin: 0 0 var(--hph-margin-xs) 0;">
                    <a href="mailto:<?php echo esc_attr($member['email']); ?>" style="color: var(--hph-primary); text-decoration: none; display: inline-flex; align-items: center;">
                        <i class="fas fa-envelope" style="margin-right: var(--hph-margin-xs); width: 14px;"></i>
                        <?php echo esc_html($member['email']); ?>
                    </a>
                </p>
                <?php endif; ?>
                
                <?php if ($member['phone']): ?>
                <p style="margin: 0;">
                    <a href="tel:<?php echo esc_attr($member['phone']); ?>" style="color: var(--hph-primary); text-decoration: none; display: inline-flex; align-items: center;">
                        <i class="fas fa-phone" style="margin-right: var(--hph-margin-xs); width: 14px;"></i>
                        <?php echo esc_html($member['phone']); ?>
                    </a>
                </p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($show_social_links && !empty(array_filter($member['social']))): ?>
            <!-- Social Links -->
            <div style="display: flex; gap: var(--hph-gap-sm); <?php echo $style === 'list' ? 'justify-content: flex-start;' : 'justify-content: center;'; ?>">
                <?php foreach ($member['social'] as $platform => $url): 
                    if (!$url || $url === '#') continue;
                    
                    $icon_class = '';
                    switch ($platform) {
                        case 'linkedin':
                            $icon_class = 'fab fa-linkedin-in';
                            break;
                        case 'twitter':
                            $icon_class = 'fab fa-twitter';
                            break;
                        case 'facebook':
                            $icon_class = 'fab fa-facebook-f';
                            break;
                        case 'instagram':
                            $icon_class = 'fab fa-instagram';
                            break;
                        default:
                            $icon_class = 'fas fa-link';
                    }
                ?>
                <a 
                    href="<?php echo esc_url($url); ?>" 
                    target="_blank" 
                    rel="noopener noreferrer"
                    style="display: inline-flex; align-items: center; justify-content: center; width: 2.5rem; height: 2.5rem; background: var(--hph-gray-100); color: var(--hph-gray-600); border-radius: var(--hph-radius-full); transition: all 0.2s ease; text-decoration: none;"
                    onmouseover="this.style.background='var(--hph-primary)'; this.style.color='var(--hph-white)'; this.style.transform='translateY(-2px)'"
                    onmouseout="this.style.background='var(--hph-gray-100)'; this.style.color='var(--hph-gray-600)'; this.style.transform='translateY(0)'"
                    title="<?php echo esc_attr(ucfirst($platform)); ?>"
                >
                    <i class="<?php echo esc_attr($icon_class); ?>" style="font-size: var(--hph-text-sm);"></i>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
        </div>
        
    </div>
    
    <?php
}
?>

<section 
    class="hph-team-unified-section hph-team-<?php echo esc_attr($style); ?>"
    <?php if ($section_id): ?>id="<?php echo esc_attr($section_id); ?>"<?php endif; ?>
    data-bg="<?php echo esc_attr($theme); ?>"
    style="<?php echo implode('; ', $section_styles); ?>"
    data-animation="<?php echo $animation ? 'true' : 'false'; ?>"
>
    <div style="<?php echo implode('; ', $container_styles); ?>">
        
        <?php if ($badge || $headline || $subheadline || $content): ?>
        <!-- Section Header -->
        <div style="margin-bottom: var(--hph-margin-3xl); <?php echo $header_alignment; ?> <?php echo $animation ? 'animation: fadeInUp 0.8s ease-out;' : ''; ?>">
            
            <?php if ($badge): ?>
            <!-- Badge -->
            <div style="margin-bottom: var(--hph-margin-lg);">
                <span style="display: inline-block; padding: var(--hph-padding-sm) var(--hph-padding-md); background: var(--hph-primary-100); color: var(--hph-primary-700); border-radius: var(--hph-radius-full); font-size: var(--hph-text-sm); font-weight: var(--hph-font-semibold);">
                    <?php echo esc_html($badge); ?>
                </span>
            </div>
            <?php endif; ?>
            
            <?php if ($headline): ?>
            <!-- Headline -->
            <h2 style="margin: 0 0 var(--hph-margin-lg) 0; font-size: var(--hph-text-4xl); font-weight: var(--hph-font-bold); line-height: var(--hph-leading-tight);">
                <?php echo esc_html($headline); ?>
            </h2>
            <?php endif; ?>
            
            <?php if ($subheadline): ?>
            <!-- Subheadline -->
            <p style="margin: 0 0 var(--hph-margin-lg) 0; font-size: var(--hph-text-xl); font-weight: var(--hph-font-medium); opacity: 0.9;">
                <?php echo esc_html($subheadline); ?>
            </p>
            <?php endif; ?>
            
            <?php if ($content): ?>
            <!-- Content -->
            <div style="font-size: var(--hph-text-base); line-height: var(--hph-leading-relaxed); max-width: 65ch; margin-left: auto; margin-right: auto; opacity: 0.8;">
                <?php echo wp_kses_post($content); ?>
            </div>
            <?php endif; ?>
            
        </div>
        <?php endif; ?>
        
        <?php if (!empty($team_members)): ?>
        
        <?php if ($group_by_role && isset($agents) && isset($staff)): ?>
        
        <!-- Grouped Layout: Agents First, Then Staff -->
        <?php if (!empty($agents)): ?>
        <div style="margin-bottom: var(--hph-margin-3xl);">
            <h3 style="margin: 0 0 var(--hph-margin-xl) 0; font-size: var(--hph-text-2xl); font-weight: var(--hph-font-semibold); text-align: <?php echo $alignment; ?>; color: var(--hph-primary);">
                Our Real Estate Agents
            </h3>
            <div style="<?php echo implode('; ', $grid_styles); ?>">
                <?php foreach ($agents as $index => $member): ?>
                    <?php hph_render_team_member($member, $index, $style, $card_style, $image_style, $show_role_badges, $show_contact_info, $show_social_links, $show_bio_preview, $show_specialties, $hover_effects, $animation); ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($staff)): ?>
        <div>
            <h3 style="margin: 0 0 var(--hph-margin-xl) 0; font-size: var(--hph-text-2xl); font-weight: var(--hph-font-semibold); text-align: <?php echo $alignment; ?>; color: var(--hph-secondary);">
                Our Support Team
            </h3>
            <div style="<?php echo implode('; ', $grid_styles); ?>">
                <?php foreach ($staff as $index => $member): ?>
                    <?php hph_render_team_member($member, $index + count($agents), $style, $card_style, $image_style, $show_role_badges, $show_contact_info, $show_social_links, $show_bio_preview, $show_specialties, $hover_effects, $animation); ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        
        <!-- Unified Layout: All Team Members Together -->
        <div style="<?php echo implode('; ', $grid_styles); ?>">
            <?php foreach ($team_members as $index => $member): ?>
                <?php hph_render_team_member($member, $index, $style, $card_style, $image_style, $show_role_badges, $show_contact_info, $show_social_links, $show_bio_preview, $show_specialties, $hover_effects, $animation); ?>
            <?php endforeach; ?>
        </div>
        
        <?php endif; ?>
        
        <?php endif; ?>
        
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

/* Carousel scrollbar styling */
.hph-team-carousel::-webkit-scrollbar {
    height: 6px;
}

.hph-team-carousel::-webkit-scrollbar-track {
    background: var(--hph-gray-100);
    border-radius: 3px;
}

.hph-team-carousel::-webkit-scrollbar-thumb {
    background: var(--hph-primary);
    border-radius: 3px;
}

.hph-team-carousel::-webkit-scrollbar-thumb:hover {
    background: var(--hph-primary-dark);
}
</style>
<?php endif; ?>

<style>
/* Enhanced team member styling */
.hph-team-member-agent {
    position: relative;
}

.hph-team-member-staff {
    position: relative;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .hph-team-member {
        text-align: center !important;
    }
    
    .hph-team-unified-section [style*="display: flex"] .hph-team-member {
        flex-direction: column;
        text-align: center;
    }
    
    .hph-team-unified-section [style*="display: flex"] .hph-team-member > div:first-child {
        width: 150px !important;
        margin: 0 auto var(--hph-margin-lg) auto;
    }
}

@media (max-width: 480px) {
    .hph-team-unified-section [style*="grid-template-columns"] {
        grid-template-columns: 1fr !important;
    }
    
    .hph-team-member {
        padding: var(--hph-padding-md) !important;
    }
}

/* Badge positioning adjustments for mobile */
@media (max-width: 480px) {
    .hph-team-member [style*="position: absolute"] {
        position: relative !important;
        top: auto !important;
        right: auto !important;
        left: auto !important;
        margin-bottom: var(--hph-margin-sm);
        display: inline-block;
    }
}
</style>
