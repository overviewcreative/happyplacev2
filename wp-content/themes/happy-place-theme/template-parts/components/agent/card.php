<?php
/**
 * Agent Card Adapter Component
 * 
 * Adapter component that uses the adapter service to transform agent data
 * for the base card component. Follows proper separation of data and presentation.
 * 
 * @package HappyPlaceTheme
 * @subpackage Components/Agent
 * @since 3.0.0
 */

// Get component args (compatible with both hph_component and get_template_part)
$component_args = $GLOBALS['hph_component_args'] ?? get_query_var('args', array());
$args = wp_parse_args($component_args, array(
    // Data props
    'agent_id' => get_the_ID(),
    
    // Display options
    'layout' => 'vertical', // vertical, horizontal, compact
    'variant' => 'default', // default, elevated, bordered
    'size' => 'md', // sm, md, lg
    'show_contact' => true,
    'show_stats' => true,
    'show_social' => true,
    'show_specialties' => false,
    'show_listings_count' => true,
    
    // Behavior
    'clickable' => true,
    'hover_effects' => true,
    
    // HTML
    'class' => '',
    'attributes' => array()
));

// Validate agent
$agent_id = $args['agent_id'];
if (!$agent_id) {
    return;
}

$post = get_post($agent_id);
if (!$post || $post->post_type !== 'agent') {
    return;
}

// Use adapter service to transform data
$adapter_service = hpt_adapter();
if (!$adapter_service) {
    // Fallback to direct bridge functions if adapter service unavailable
    $agent = function_exists('hpt_get_agent') ? hpt_get_agent($agent_id) : null;
    if (!$agent) {
        // Fallback to basic post data
        $agent = array(
            'id' => $agent_id,
            'name' => get_the_title($agent_id),
            'url' => get_permalink($agent_id),
            'bio' => get_the_excerpt($agent_id),
            'avatar' => get_the_post_thumbnail_url($agent_id, 'medium'),
            'phone' => get_post_meta($agent_id, 'phone', true),
            'email' => get_post_meta($agent_id, 'email', true)
        );
    }
} else {
    // Transform data using adapter service
    $card_props = $adapter_service->transform('agent_card', $agent_id, array(
        'variant' => $args['variant'],
        'layout' => $args['layout'],
        'size' => $args['size']
    ));
    
    if (!$card_props) {
        return;
    }
}

// Use adapter service data if available, otherwise fallback to manual building
if ($adapter_service && isset($card_props)) {
    // Adapter service provides properly formatted card arguments
    $final_card_args = $card_props;
    
    // Apply display options from component args
    if ($args['show_contact'] && !isset($final_card_args['show_contact'])) {
        $final_card_args['show_contact'] = $args['show_contact'];
    }
    
    // Override any layout/variant options
    $final_card_args['variant'] = $args['variant'];
    $final_card_args['layout'] = $args['layout'];
    $final_card_args['size'] = $args['size'];
    $final_card_args['hover_effects'] = $args['hover_effects'];
    
    // Add component-specific classes
    $final_card_args['class'] = ($final_card_args['class'] ?? '') . ' ' . $args['class'];
    $final_card_args['attributes'] = array_merge($final_card_args['attributes'] ?? array(), $args['attributes']);
    
} else {
    // Fallback: Build card arguments manually using bridge functions
    // Build contact info
    $contact_methods = array();
    if ($args['show_contact']) {
        if (!empty($agent['phone'])) {
            $contact_methods[] = array(
                'type' => 'phone',
                'label' => 'Call',
                'value' => $agent['phone'],
                'href' => 'tel:' . preg_replace('/[^0-9+]/', '', $agent['phone']),
                'icon' => 'phone'
            );
        }
        if (!empty($agent['email'])) {
            $contact_methods[] = array(
                'type' => 'email',
                'label' => 'Email',
                'value' => $agent['email'],
                'href' => 'mailto:' . $agent['email'],
                'icon' => 'envelope'
            );
        }
    }

    // Build stats
    $stats_content = '';
    if ($args['show_stats'] || $args['show_listings_count']) {
        $stats = array();
        
        if ($args['show_listings_count']) {
            $listings_count = function_exists('hpt_get_agent_listings_count') ? 
                             hpt_get_agent_listings_count($agent_id) : 0;
            if ($listings_count > 0) {
                $stats[] = sprintf(
                    '<span class="hph-agent-stat">
                        <strong>%d</strong> Active Listing%s
                    </span>',
                    $listings_count,
                    $listings_count === 1 ? '' : 's'
                );
            }
        }
        
        if ($args['show_stats']) {
            $total_sales = function_exists('hpt_get_agent_total_sales') ? 
                          hpt_get_agent_total_sales($agent_id) : 0;
            if ($total_sales > 0) {
                $stats[] = sprintf(
                    '<span class="hph-agent-stat">
                        <strong>%d</strong> Sale%s
                    </span>',
                    $total_sales,
                    $total_sales === 1 ? '' : 's'
                );
            }
            
            $years_experience = function_exists('hpt_get_agent_years_experience') ? 
                               hpt_get_agent_years_experience($agent_id) : 0;
            if ($years_experience > 0) {
                $stats[] = sprintf(
                    '<span class="hph-agent-stat">
                        <strong>%d</strong> Year%s Experience
                    </span>',
                    $years_experience,
                    $years_experience === 1 ? '' : 's'
                );
            }
        }
        
        if (!empty($stats)) {
            $stats_content = sprintf(
                '<div class="hph-agent-stats hph-text-sm hph-text-gray-600 hph-flex hph-flex-wrap hph-gap-md hph-mb-sm">
                    %s
                </div>',
                implode(' • ', $stats)
            );
        }
    }

    // Build specialties
    $specialties_content = '';
    if ($args['show_specialties']) {
        $specialties = function_exists('hpt_get_agent_specialties') ? 
                       hpt_get_agent_specialties($agent_id) : array();
        if (!empty($specialties)) {
            $specialties_content = sprintf(
                '<div class="hph-agent-specialties hph-mb-sm">
                    <div class="hph-text-xs hph-text-gray-500 hph-mb-xs">Specialties:</div>
                    <div class="hph-flex hph-flex-wrap hph-gap-xs">
                        %s
                    </div>
                </div>',
                implode('', array_map(function($specialty) {
                    return sprintf(
                        '<span class="hph-badge hph-badge-sm hph-badge-light">%s</span>',
                        esc_html($specialty)
                    );
                }, $specialties))
            );
        }
    }

    // Build social links
    $social_content = '';
    if ($args['show_social']) {
        $social_links = function_exists('hpt_get_agent_social_links') ? 
                        hpt_get_agent_social_links($agent_id) : array();
        if (!empty($social_links)) {
            $social_buttons = array();
            foreach ($social_links as $platform => $url) {
                if (!empty($url)) {
                    $social_buttons[] = sprintf(
                        '<a href="%s" target="_blank" rel="noopener" class="hph-btn hph-btn-ghost hph-btn-sm" aria-label="%s">
                            <i class="fab fa-%s"></i>
                        </a>',
                        esc_url($url),
                        esc_attr(ucfirst($platform)),
                        esc_attr($platform === 'linkedin' ? 'linkedin-in' : $platform)
                    );
                }
            }
            
            if (!empty($social_buttons)) {
                $social_content = sprintf(
                    '<div class="hph-agent-social hph-flex hph-gap-xs hph-mt-sm">
                        %s
                    </div>',
                    implode('', $social_buttons)
                );
            }
        }
    }

    // Build contact actions
    $actions = array();
    foreach ($contact_methods as $method) {
        $actions[] = array(
            'type' => 'link',
            'text' => $method['label'],
            'href' => $method['href'],
            'icon' => $method['icon'],
            'variant' => $method['type'] === 'phone' ? 'primary' : 'outline',
            'size' => 'sm'
        );
    }

    // Build image data
    $image_data = null;
    if (!empty($agent['avatar'])) {
        $image_data = array(
            'url' => $agent['avatar'],
            'alt' => $agent['name'] . ' - Real Estate Agent',
            'aspect_ratio' => '1:1'
        );
    }

    // Build content
    $content_parts = array_filter(array(
        $agent['bio'] ? '<p class="hph-text-sm hph-text-gray-600 hph-mb-sm">' . esc_html($agent['bio']) . '</p>' : '',
        $stats_content,
        $specialties_content
    ));

    $footer_parts = array_filter(array(
        $social_content
    ));

    // Prepare base card arguments (fallback)
    $final_card_args = array(
        // Content
        'title' => $agent['name'],
        'subtitle' => function_exists('hpt_get_agent_title') ? hpt_get_agent_title($agent_id) : 'Real Estate Agent',
        'content' => implode('', $content_parts),
        'footer' => implode('', $footer_parts),
        
        // Media
        'image' => $image_data,
        
        // Behavior
        'href' => $args['clickable'] ? $agent['url'] : '',
        'target' => '_self',
        
        // Appearance
        'variant' => $args['variant'],
        'layout' => $args['layout'],
        'size' => $args['size'],
        'hover_effects' => $args['hover_effects'],
        
        // Actions
        'actions' => $actions,
        
        // HTML
        'id' => 'agent-card-' . $agent_id,
        'class' => 'hph-agent-card ' . $args['class'],
        'attributes' => array_merge(array(
            'data-agent-id' => $agent_id,
            'data-agent-name' => $agent['name']
        ), $args['attributes'])
    );
}

// Load the base card component with final arguments
hph_component('card', $final_card_args);
?>
