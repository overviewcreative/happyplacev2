<?php
function get_agent_card_props($agent_id, $options = []) {
    // Only use data actually available from bridge
    $name = get_the_title($agent_id);
    $phone = hpt_get_agent_phone($agent_id);
    $email = hpt_get_agent_email($agent_id);
    $photo = hpt_get_agent_photo($agent_id);
    $title = hpt_get_agent_title($agent_id);
    $active_listings = hpt_get_agent_active_listings_count($agent_id);
    $sold_listings = hpt_get_agent_sold_listings_count($agent_id);
    $is_featured = hpt_is_agent_featured($agent_id);
    
    return [
        'variant' => $options['variant'] ?? 'default',
        'layout' => 'vertical',
        'image' => [
            'src' => $photo ?: get_template_directory_uri() . '/assets/images/default-agent.jpg',
            'alt' => $name,
            'ratio' => 'square'
        ],
        'title' => [
            'text' => $name,
            'link' => get_permalink($agent_id)
        ],
        'subtitle' => $title,
        'badges' => $is_featured ? [['text' => 'Featured Agent', 'variant' => 'success']] : [],
        'meta_items' => array_filter([
            $active_listings !== null ? ['icon' => 'home', 'text' => $active_listings . ' listings'] : null,
            $sold_listings !== null ? ['icon' => 'check', 'text' => $sold_listings . ' sold'] : null,
            $phone ? ['icon' => 'phone', 'text' => $phone] : null
        ]),
        'actions' => array_filter([
            ['text' => 'View Profile', 'href' => get_permalink($agent_id)],
            $email ? ['text' => 'Contact', 'variant' => 'outline', 'href' => 'mailto:' . $email] : null
        ]),
        'link_wrapper' => get_permalink($agent_id),
        'hover_effect' => 'shadow'
    ];
}