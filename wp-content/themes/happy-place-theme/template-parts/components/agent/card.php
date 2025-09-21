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

    // View type (legacy support)
    'view_type' => 'grid', // grid, list, compact

    // Display options
    'layout' => 'vertical', // vertical, horizontal, compact
    'variant' => 'default', // default, elevated, bordered, minimal
    'size' => 'md', // sm, md, lg
    'show_contact' => true,
    'show_title' => true,
    'show_bio' => true,
    'compact' => false, // for map view or small spaces

    // Behavior
    'clickable' => true,
    'hover_effects' => true,

    // HTML
    'class' => '',
    'attributes' => array()
));

// Map view type to layout (legacy support)
$view_type_map = array(
    'grid' => 'vertical',
    'list' => 'horizontal',
    'compact' => 'compact'
);

if (isset($view_type_map[$args['view_type']])) {
    $args['layout'] = $view_type_map[$args['view_type']];
}

// Handle compact flag
if ($args['compact'] || $args['view_type'] === 'compact') {
    $args['layout'] = 'compact';
    $args['size'] = 'sm';
}

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
// Temporarily disable adapter service to avoid errors - use bridge functions directly
$adapter_service = null; // hpt_adapter();
if (!$adapter_service) {
    // Fallback to direct bridge functions if adapter service unavailable
    $agent = hpt_get_agent($agent_id);
    if (!$agent) {
        return;
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
    // Get agent details
    $agent_name = $agent['name'] ?? get_the_title($agent_id);
    $agent_title = hpt_get_agent_title($agent_id) ?: 'Real Estate Agent';
    $agent_bio = hpt_get_agent_bio($agent_id);
    $agent_phone = hpt_get_agent_phone($agent_id);
    $agent_email = hpt_get_agent_email($agent_id);
    $agent_url = $agent['url'] ?? get_permalink($agent_id);
    $agent_photo = hpt_get_agent_photo($agent_id);

    // Process description - strip HTML and limit length
    $agent_description = '';
    if ($args['show_bio'] && $agent_bio) {
        $agent_description = wp_strip_all_tags($agent_bio);
        if (strlen($agent_description) > 120) {
            $agent_description = substr($agent_description, 0, 120) . '...';
        }
    }

    // Handle image fallback
    if (!$agent_photo) {
        $agent_photo = hph_get_image_url_only('assets/images/agent-placeholder.jpg');
    }

    // Build image data for base card component
    $image_data = null;
    if ($agent_photo) {
        $image_data = array(
            'src' => $agent_photo,
            'alt' => $agent_name,
            'ratio' => 'square', // Agent photos are typically circular/square
            'position' => 'top'
        );
    }

    // Build actions for contact buttons
    $actions = array();
    if ($args['show_contact']) {
        if ($agent_email) {
            $actions[] = array(
                'type' => 'link',
                'text' => 'Email',
                'icon' => 'envelope',
                'href' => 'mailto:' . $agent_email,
                'variant' => 'primary',
                'size' => 'sm',
                'class' => 'hph-agent-email-btn'
            );
        }
        if ($agent_phone) {
            $actions[] = array(
                'type' => 'link',
                'text' => 'Call',
                'icon' => 'phone',
                'href' => 'tel:' . preg_replace('/[^0-9+]/', '', $agent_phone),
                'variant' => 'outline-primary',
                'size' => 'sm',
                'class' => 'hph-agent-phone-btn'
            );
        }
    }

    // Prepare base card arguments (fallback)
    $final_card_args = array(
        // Content - properly structured for agents
        'title' => array(
            'text' => $agent_name,
            'tag' => 'h3'
        ),
        'subtitle' => ($args['show_title'] && $agent_title) ? $agent_title : '',
        'description' => $agent_description,
        'description_limit' => 120,

        // Media
        'image' => $image_data,

        // Behavior
        'link_wrapper' => $args['clickable'] ? $agent_url : '',
        'hover_effect' => $args['hover_effects'] ? 'lift' : 'none',

        // Appearance
        'variant' => $args['variant'],
        'layout' => $args['layout'],
        'size' => $args['size'],

        // Actions
        'actions' => $actions,

        // HTML
        'id' => 'agent-card-' . $agent_id,
        'class' => 'hph-agent-card ' . $args['class'],
        'attributes' => array_merge(array(
            'data-agent-id' => $agent_id,
        ), $args['attributes'])
    );

    // Add layout-specific modifications
    if ($args['layout'] === 'compact') {
        $final_card_args['description'] = ''; // Remove description in compact view
        $final_card_args['actions'] = array(); // Remove actions in compact view
    }
}

// Load the base card component with final arguments
hph_component('card', $final_card_args);
?>
