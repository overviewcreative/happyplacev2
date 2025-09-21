<?php
/**
 * Base Single Sidebar - Sidebar with related info/CTAs
 *
 * @package HappyPlaceTheme
 */

$sidebar_args = wp_parse_args($args ?? [], [
    'post_id' => get_the_ID(),
    'post_type' => get_post_type(),
    'widgets' => [],
    'show_related_posts' => true,
    'show_contact_cta' => true,
    'show_social_sharing' => false,
    'related_count' => 5,
    'container_class' => 'hph-space-y-xl'
]);

// Get post-type specific widgets if none provided
if (empty($sidebar_args['widgets'])) {
    switch ($sidebar_args['post_type']) {
        case 'listing':
            $sidebar_args['widgets'] = [
                'listing-quick-info' => ['title' => __('Property Details', 'happy-place-theme')],
                'listing-agent' => ['title' => __('Listing Agent', 'happy-place-theme')],
                'mortgage-calculator' => ['title' => __('Mortgage Calculator', 'happy-place-theme')],
                'listing-contact-form' => ['title' => __('Schedule a Tour', 'happy-place-theme')],
                'similar-listings' => ['title' => __('Similar Properties', 'happy-place-theme')]
            ];
            break;
        case 'agent':
            $sidebar_args['widgets'] = [
                'agent-contact-info' => ['title' => __('Contact Information', 'happy-place-theme')],
                'agent-stats' => ['title' => __('Performance Stats', 'happy-place-theme')],
                'agent-contact-form' => ['title' => __('Get in Touch', 'happy-place-theme')],
                'recent-listings' => ['title' => __('Recent Listings', 'happy-place-theme')]
            ];
            break;
        default:
            $sidebar_args['widgets'] = [
                'post-meta' => ['title' => __('Post Details', 'happy-place-theme')],
                'author-bio' => ['title' => __('About the Author', 'happy-place-theme')],
                'related-posts' => ['title' => __('Related Posts', 'happy-place-theme')]
            ];
            break;
    }
}
?>

<div class="<?php echo esc_attr($sidebar_args['container_class']); ?>">
    
    <?php foreach ($sidebar_args['widgets'] as $widget_id => $widget_config) : ?>
        <?php
        $widget_config = wp_parse_args($widget_config, [
            'title' => '',
            'show_title' => true,
            'component' => $widget_id,
            'args' => [],
            'condition' => true,
            'wrapper_class' => 'hph-bg-white hph-p-lg hph-rounded-lg hph-shadow-sm hph-border hph-border-gray-200'
        ]);
        
        // Skip if condition not met
        if (!$widget_config['condition']) {
            continue;
        }
        
        // Check widget-specific conditions
        $show_widget = true;
        switch ($widget_id) {
            case 'listing-agent':
                // Only show if listing has an agent assigned
                $agent_id = function_exists('hpt_get_listing_agent') ? 
                          hpt_get_listing_agent($sidebar_args['post_id']) : 
                          get_field('listing_agent', $sidebar_args['post_id']);
                $show_widget = !empty($agent_id);
                break;
            case 'mortgage-calculator':
                // Only show for listings with price
                $price = function_exists('hpt_get_listing_price') ? 
                       hpt_get_listing_price($sidebar_args['post_id']) : 
                       get_field('listing_price', $sidebar_args['post_id']);
                $show_widget = !empty($price) && is_numeric($price);
                break;
            case 'author-bio':
                $author_id = get_post_field('post_author', $sidebar_args['post_id']);
                $author_bio = get_the_author_meta('description', $author_id);
                $show_widget = !empty($author_bio);
                break;
        }
        
        if (!$show_widget) {
            continue;
        }
        
        // Check if component exists
        $component_path = '';
        $component_exists = false;
        
        // Try different component locations
        $possible_paths = [
            'template-parts/components/' . $widget_config['component'] . '.php',
            'template-parts/sidebar/' . $widget_config['component'] . '.php',
            'template-parts/widgets/' . $widget_config['component'] . '.php'
        ];
        
        foreach ($possible_paths as $path) {
            if (locate_template($path)) {
                $component_path = str_replace('.php', '', $path);
                $component_exists = true;
                break;
            }
        }
        
        if (!$component_exists) {
            continue;
        }
        ?>
        
        <div class="<?php echo esc_attr($widget_config['wrapper_class'] . ' hph-animate-fade-in-up hph-sidebar-widget--' . $widget_id); ?>">
            
            <?php if ($widget_config['show_title'] && !empty($widget_config['title'])) : ?>
                <h3 class="hph-text-lg hph-font-semibold hph-text-gray-900 hph-mb-md hph-border-b hph-border-gray-100 hph-pb-sm">
                    <?php echo esc_html($widget_config['title']); ?>
                </h3>
            <?php endif; ?>
            
            <div class="hph-space-y-md">
                <?php
                // Prepare widget arguments
                $widget_args = array_merge([
                    'post_id' => $sidebar_args['post_id'],
                    'post_type' => $sidebar_args['post_type'],
                    'context' => 'sidebar'
                ], $widget_config['args']);
                
                // Load the component
                get_template_part($component_path, null, $widget_args);
                ?>
            </div>
            
        </div>
        
    <?php endforeach; ?>
    
    <?php if ($sidebar_args['show_social_sharing']) : ?>
        <div class="hph-bg-white hph-p-lg hph-rounded-lg hph-shadow-sm hph-border hph-border-gray-200 hph-animate-fade-in-up hph-sidebar-widget--social-sharing">
            <h3 class="hph-text-lg hph-font-semibold hph-text-gray-900 hph-mb-md hph-border-b hph-border-gray-100 hph-pb-sm">
                <?php _e('Share This', 'happy-place-theme'); ?>
            </h3>
            <div class="hph-space-y-md">
                <?php hph_component('social-sharing', [
                    'post_id' => $sidebar_args['post_id'],
                    'title' => get_the_title($sidebar_args['post_id']),
                    'url' => get_permalink($sidebar_args['post_id']),
                    'layout' => 'vertical'
                ]); ?>
            </div>
        </div>
    <?php endif; ?>
    
    <?php
    // Load post-type specific sidebar additions
    $sidebar_template = 'template-parts/sidebar/single-' . $sidebar_args['post_type'] . '-additions';
    if (locate_template($sidebar_template . '.php')) {
        get_template_part($sidebar_template, null, $sidebar_args);
    }
    ?>
    
</div>
