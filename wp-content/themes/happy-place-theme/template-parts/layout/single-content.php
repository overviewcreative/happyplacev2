<?php
/**
 * Base Single Content - Main content sections wrapper
 *
 * @package HappyPlaceTheme
 */

// Parse arguments with utility-first approach
$content_args = wp_parse_args($args ?? [], [
    'post_id' => get_the_ID(),
    'post_type' => get_post_type(),
    'sections' => [], // Custom content sections
    'show_title' => false, // Usually shown in hero
    'show_content' => true,
    'show_meta' => true,
    'show_tags' => true,
    'show_sharing' => true,
    'container_classes' => [
        'hph-single-content-wrapper',
        'hph-space-y-2xl',
        'hph-py-xl'
    ]
]);

// Get post data
$post = get_post($content_args['post_id']);
if (!$post) {
    return;
}

// Get post-type specific sections
if (empty($content_args['sections'])) {
    switch ($content_args['post_type']) {
        case 'listing':
            $content_args['sections'] = [
                'overview' => ['component' => 'listing-details', 'title' => __('Property Overview', 'happy-place-theme')],
                'features' => ['component' => 'listing-features', 'title' => __('Features & Amenities', 'happy-place-theme')],
                'gallery' => ['component' => 'listing-photo-gallery', 'title' => __('Photo Gallery', 'happy-place-theme')],
                'virtual-tour' => ['component' => 'listing-virtual-tour', 'title' => __('Virtual Tour', 'happy-place-theme')],
                'floor-plans' => ['component' => 'listing-floor-plans', 'title' => __('Floor Plans', 'happy-place-theme')],
                'map' => ['component' => 'listing-map', 'title' => __('Location & Map', 'happy-place-theme')],
                'mortgage' => ['component' => 'listing-mortgage-calculator', 'title' => __('Mortgage Calculator', 'happy-place-theme')]
            ];
            break;
        case 'agent':
            $content_args['sections'] = [
                'bio' => ['component' => 'agent-bio', 'title' => __('About', 'happy-place-theme')],
                'stats' => ['component' => 'agent-stats', 'title' => __('Performance', 'happy-place-theme')],
                'testimonials' => ['component' => 'agent-testimonials', 'title' => __('Client Reviews', 'happy-place-theme')],
                'listings' => ['component' => 'agent-listings', 'title' => __('Current Listings', 'happy-place-theme')],
                'contact' => ['component' => 'agent-contact-form', 'title' => __('Contact', 'happy-place-theme')]
            ];
            break;
        default:
            $content_args['sections'] = [
                'content' => ['component' => 'post-content', 'title' => '']
            ];
            break;
    }
}
?>

<article class="<?php echo esc_attr(implode(' ', $content_args['container_classes'])); ?>" 
         id="post-<?php echo esc_attr($content_args['post_id']); ?>"
         <?php post_class('hph-single-article hph-single-' . $content_args['post_type'], $content_args['post_id']); ?>>
    
    <?php if ($content_args['show_title']) : ?>
        <header class="hph-mb-xl hph-text-center">
            <h1 class="hph-text-4xl hph-font-bold hph-text-gray-900 hph-mb-md"><?php echo esc_html(get_the_title($content_args['post_id'])); ?></h1>
            
            <?php if ($content_args['show_meta']) : ?>
                <div class="hph-flex hph-items-center hph-justify-center hph-gap-lg hph-text-sm hph-text-gray-600">
                    <?php
                    // Post-type specific meta
                    switch ($content_args['post_type']) {
                        case 'listing':
                            // Listing meta handled in hero and details sections
                            break;
                        case 'agent':
                            // Agent meta handled in bio section
                            break;
                        default:
                            echo '<time class="hph-post-date" datetime="' . esc_attr(get_the_date('c', $content_args['post_id'])) . '">';
                            echo esc_html(get_the_date('', $content_args['post_id']));
                            echo '</time>';
                            
                            $author_id = get_post_field('post_author', $content_args['post_id']);
                            if ($author_id) {
                                echo '<span class="hph-post-author">' . __('by', 'happy-place-theme') . ' ';
                                echo '<a href="' . esc_url(get_author_posts_url($author_id)) . '">';
                                echo esc_html(get_the_author_meta('display_name', $author_id));
                                echo '</a></span>';
                            }
                            break;
                    }
                    ?>
                </div>
            <?php endif; ?>
        </header>
    <?php endif; ?>
    
    <div class="hph-single-content-sections">
        
        <?php if ($content_args['show_content'] && $content_args['post_type'] !== 'listing') : ?>
            <!-- Standard Post Content -->
            <section class="hph-content-section hph-content-section--main">
                <div class="hph-post-content">
                    <?php echo apply_filters('the_content', get_post_field('post_content', $content_args['post_id'])); ?>
                </div>
            </section>
        <?php endif; ?>
        
        <?php
        // Load sections
        foreach ($content_args['sections'] as $section_id => $section) {
            $section = wp_parse_args($section, [
                'component' => '',
                'title' => '',
                'show_title' => true,
                'args' => [],
                'condition' => true
            ]);
            
            // Skip if condition not met
            if (!$section['condition']) {
                continue;
            }
            
            // Check if component exists
            $component_exists = false;
            if (!empty($section['component'])) {
                $component_path = locate_template('template-parts/components/' . $section['component'] . '.php');
                $component_exists = !empty($component_path);
            }
            
            // Skip if component doesn't exist
            if (!$component_exists && !empty($section['component'])) {
                continue;
            }
            
            // Additional condition checks based on post type
            $show_section = true;
            switch ($section_id) {
                case 'gallery':
                    $gallery_images = function_exists('hpt_get_listing_gallery') ? 
                                    hpt_get_listing_gallery($content_args['post_id']) : [];
                    $show_section = !empty($gallery_images) || has_post_thumbnail($content_args['post_id']);
                    break;
                case 'virtual-tour':
                    $virtual_tour = function_exists('hpt_get_listing_virtual_tour') ? 
                                  hpt_get_listing_virtual_tour($content_args['post_id']) : 
                                  get_field('virtual_tour_url', $content_args['post_id']);
                    $show_section = !empty($virtual_tour);
                    break;
                case 'floor-plans':
                    $floor_plans = function_exists('hpt_get_listing_floor_plans') ? 
                                 hpt_get_listing_floor_plans($content_args['post_id']) : 
                                 get_field('floor_plans', $content_args['post_id']);
                    $show_section = !empty($floor_plans);
                    break;
                case 'features':
                    $features = function_exists('hpt_get_listing_features') ? 
                              hpt_get_listing_features($content_args['post_id']) : 
                              get_field('features', $content_args['post_id']);
                    $show_section = !empty($features);
                    break;
                case 'map':
                    if ($content_args['post_type'] === 'listing') {
                        $has_location = false;
                        if (function_exists('hpt_get_listing_coordinates')) {
                            $coords = hpt_get_listing_coordinates($content_args['post_id']);
                            $has_location = !empty($coords['lat']) && !empty($coords['lng']);
                        }
                        if (!$has_location) {
                            $address = get_field('street_address', $content_args['post_id']) ?: get_field('address', $content_args['post_id']);
                            $has_location = !empty($address);
                        }
                        $show_section = $has_location;
                    }
                    break;
            }
            
            if (!$show_section) {
                continue;
            }
            ?>
            
            <section class="hph-content-section hph-content-section--<?php echo esc_attr($section_id); ?>" 
                     id="section-<?php echo esc_attr($section_id); ?>">
                
                <?php if ($section['show_title'] && !empty($section['title'])) : ?>
                    <header class="hph-content-section__header">
                        <h2 class="hph-content-section__title"><?php echo esc_html($section['title']); ?></h2>
                    </header>
                <?php endif; ?>
                
                <div class="hph-content-section__content">
                    <?php
                    if (!empty($section['component'])) {
                        // Load component with merged args
                        $component_args = array_merge([
                            'post_id' => $content_args['post_id'],
                            'post_type' => $content_args['post_type']
                        ], $section['args']);
                        
                        // Try to load from components directory first
                        $component_template = 'template-parts/components/' . $section['component'];
                        if (locate_template($component_template . '.php')) {
                            get_template_part($component_template, null, $component_args);
                        } else {
                            // Try loading as hph_component
                            hph_component($section['component'], $component_args);
                        }
                    }
                    ?>
                </div>
                
            </section>
            
            <?php
        }
        ?>
        
    </div>
    
    <?php if ($content_args['show_tags'] && $content_args['post_type'] !== 'listing') : ?>
        <?php
        $tags = get_the_tags($content_args['post_id']);
        if ($tags && !is_wp_error($tags)) :
        ?>
            <footer class="hph-single-tags">
                <span class="hph-tags-label"><?php _e('Tags:', 'happy-place-theme'); ?></span>
                <ul class="hph-tag-list">
                    <?php foreach ($tags as $tag) : ?>
                        <li class="hph-tag-item">
                            <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>" class="hph-tag-link">
                                <?php echo esc_html($tag->name); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </footer>
        <?php endif; ?>
    <?php endif; ?>
    
    <?php if ($content_args['show_sharing']) : ?>
        <div class="hph-single-sharing">
            <?php hph_component('social-sharing', [
                'post_id' => $content_args['post_id'],
                'title' => get_the_title($content_args['post_id']),
                'url' => get_permalink($content_args['post_id'])
            ]); ?>
        </div>
    <?php endif; ?>
    
</article>