<?php
/**
 * Card Grid Component - Wrapper for Grid + Card components with adapter support
 *
 * @package HappyPlaceTheme
 */

// Get data from query vars (new approach) or args (old approach)
$posts = get_query_var('archive_posts') ?: ($args['posts'] ?? []);
$post_type = get_query_var('archive_post_type') ?: get_query_var('post_type') ?: ($args['post_type'] ?? 'post');
$card_adapter = get_query_var('archive_card_adapter') ?: ($args['card_adapter'] ?? null);
$adapter_options = get_query_var('archive_adapter_options') ?: ($args['adapter_options'] ?? []);

// Auto-detect adapter based on post type
if (!$card_adapter && $post_type === 'agent') {
    $card_adapter = 'agent_card';
}

// Set the card component based on adapter or post type
$card_component = 'card'; // Always use base card component

// If no posts, show message
if (empty($posts)) {
    echo '<div class="hph-no-results">';
    echo '<p>' . __('No items found.', 'happy-place-theme') . '</p>';
    echo '</div>';
    return;
}

// Process each post through the adapter
$processed_items = [];

foreach ($posts as $post) {
    $post_id = is_object($post) ? $post->ID : $post;
    
    // Use adapter service if available
    if (class_exists('HappyPlaceTheme\\Services\\AdapterService') && !empty($card_adapter) && $card_adapter === 'agent_card') {
        try {
            $adapter_service = \HappyPlaceTheme\Services\AdapterService::get_instance();
            $processed_data = $adapter_service->adapt_agent_card($post_id, $adapter_options);
            
            // Add debug info to processed data
            $processed_data['id'] = $post_id;
            
            $processed_items[] = $processed_data;
        } catch (Exception $e) {
            // Fallback to basic post data on adapter error
            $processed_items[] = [
                'id' => $post_id,
                'variant' => 'bordered',
                'layout' => 'vertical',
                'title' => [
                    'text' => get_the_title($post_id),
                    'link' => get_permalink($post_id)
                ],
                'description' => get_the_excerpt($post_id),
                'actions' => [
                    [
                        'text' => __('View Profile', 'happy-place-theme'),
                        'href' => get_permalink($post_id),
                        'variant' => 'primary',
                        'size' => 'sm'
                    ]
                ]
            ];
        }
    } else {
        // Fallback: Basic card data
        $processed_items[] = [
            'id' => $post_id,
            'variant' => 'bordered',
            'layout' => 'vertical',
            'title' => [
                'text' => get_the_title($post_id),
                'link' => get_permalink($post_id)
            ],
            'description' => get_the_excerpt($post_id),
            'actions' => [
                [
                    'text' => __('View Profile', 'happy-place-theme'),
                    'href' => get_permalink($post_id),
                    'variant' => 'primary',
                    'size' => 'sm'
                ]
            ]
        ];
    }
}

// Render the cards
foreach ($processed_items as $card_props) {
    // Try the component loader first, with error handling
    if (class_exists('HPH_Component_Loader')) {
        try {
            
    // The AdapterService already transformed the data into base component props
    // So we can pass the processed data directly to the base card component
    
    ob_start();
            HPH_Component_Loader::load_component($card_component, $card_props);
            $card_output = ob_get_contents();
            ob_end_clean();
            
            // If component loaded successfully (has content), output it
            if ($card_output && trim($card_output)) {
                echo $card_output;
            } else {
                // Component didn't output anything, use manual fallback
                echo '<div class="hph-fallback-card">';
                echo '<h3><a href="' . esc_url($card_props['title']['link'] ?? '#') . '">' . esc_html($card_props['title']['text'] ?? 'Agent') . '</a></h3>';
                echo '<p>' . esc_html($card_props['description'] ?? '') . '</p>';
                if (!empty($card_props['actions'])) {
                    foreach ($card_props['actions'] as $action) {
                        echo '<a href="' . esc_url($action['href'] ?? '#') . '" class="hph-fallback-card-action">' . esc_html($action['text'] ?? 'Action') . '</a>';
                    }
                }
                echo '</div>';
            }
        } catch (Exception $e) {
            // Error in component loader, use fallback
            echo '<div class="hph-fallback-card">';
            echo '<h3><a href="' . esc_url($card_props['title']['link'] ?? '#') . '">' . esc_html($card_props['title']['text'] ?? 'Agent') . '</a></h3>';
            echo '<p>' . esc_html($card_props['description'] ?? '') . '</p>';
            if (!empty($card_props['actions'])) {
                foreach ($card_props['actions'] as $action) {
                    echo '<a href="' . esc_url($action['href'] ?? '#') . '" style="display: inline-block; padding: 8px 16px; background: #007cba; color: white; text-decoration: none; margin-right: 10px;">' . esc_html($action['text'] ?? 'Action') . '</a>';
                }
            }
            echo '</div>';
        }
    } else {
        // No component loader, use get_template_part
        hph_component('universal-card', $card_props);
    }
}
