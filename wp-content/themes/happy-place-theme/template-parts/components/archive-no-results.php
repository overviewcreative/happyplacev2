<?php
/**
 * Archive No Results Component - Empty State Display
 * 
 * Professional empty state component for when no results are found
 * in archive pages. Provides helpful suggestions and actions.
 * 
 * @package HappyPlaceTheme
 * @version 3.0.0
 * 
 * === Configuration Options ===
 * 
 * Core Settings:
 * - post_type: string - Current post type
 * - search_query: string - Current search query
 * - current_filters: array - Active filters
 * 
 * Display Options:
 * - show_icon: bool - Display large icon
 * - show_suggestions: bool - Display helpful suggestions
 * - show_search: bool - Display search form
 * - show_clear_filters: bool - Display clear filters button
 * - show_popular: bool - Display popular items
 * - show_contact: bool - Display contact CTA
 * 
 * Content:
 * - title: string - Main message title
 * - message: string - Descriptive message
 * - icon: string - Icon class to display
 * 
 * Style Options:
 * - layout: string - 'centered', 'left-aligned'
 * - container_classes: array - Additional classes
 * - background: string - Background style
 */

// Parse arguments with defaults
$args = wp_parse_args($args ?? [], [
    // Core Settings
    'post_type' => get_post_type() ?: 'post',
    'search_query' => get_search_query(),
    'current_filters' => [],
    
    // Display Options
    'show_icon' => true,
    'show_suggestions' => true,
    'show_search' => true,
    'show_clear_filters' => true,
    'show_popular' => true,
    'show_contact' => true,
    
    // Content
    'title' => '',
    'message' => '',
    'icon' => 'fas fa-search',
    
    // Style Options
    'layout' => 'centered',
    'container_classes' => [],
    'background' => 'white',
]);

// Set default title and message based on context
if (empty($args['title'])) {
    if (!empty($args['search_query'])) {
        $args['title'] = sprintf(__('No results for "%s"', 'happy-place-theme'), esc_html($args['search_query']));
    } elseif (!empty($args['current_filters'])) {
        $args['title'] = __('No results match your filters', 'happy-place-theme');
    } else {
        switch ($args['post_type']) {
            case 'listing':
                $args['title'] = __('No properties found', 'happy-place-theme');
                break;
            case 'agent':
                $args['title'] = __('No agents found', 'happy-place-theme');
                break;
            default:
                $args['title'] = __('No results found', 'happy-place-theme');
        }
    }
}

if (empty($args['message'])) {
    if (!empty($args['search_query'])) {
        $args['message'] = __('Try adjusting your search terms or browse our categories below.', 'happy-place-theme');
    } elseif (!empty($args['current_filters'])) {
        $args['message'] = __('Try removing some filters or start a new search.', 'happy-place-theme');
    } else {
        switch ($args['post_type']) {
            case 'listing':
                $args['message'] = __('We couldn\'t find any properties matching your criteria. Try adjusting your filters or contact us for personalized assistance.', 'happy-place-theme');
                $args['icon'] = 'fas fa-home';
                break;
            case 'agent':
                $args['message'] = __('We couldn\'t find any agents matching your criteria. Our team is here to help - contact us directly.', 'happy-place-theme');
                $args['icon'] = 'fas fa-users';
                break;
            default:
                $args['message'] = __('We couldn\'t find what you\'re looking for. Try searching or browsing our categories.', 'happy-place-theme');
        }
    }
}

// Build container classes
$container_classes = [
    'hph-no-results',
    'hph-py-5xl'
];

if ($args['layout'] === 'centered') {
    $container_classes[] = 'hph-text-center';
}

if ($args['background'] === 'white') {
    $container_classes[] = 'hph-bg-white';
} elseif ($args['background'] === 'gray') {
    $container_classes[] = 'hph-bg-gray-50';
}

// Add custom classes
if (!empty($args['container_classes'])) {
    $container_classes = array_merge($container_classes, $args['container_classes']);
}

// Check if we have filters to clear
$has_filters = !empty($args['current_filters']) || !empty($args['search_query']);

?>

<div class="<?php echo esc_attr(implode(' ', $container_classes)); ?>">
    <div class="hph-container hph-max-w-3xl">
        
        <?php if ($args['show_icon']) : ?>
        <div class="hph-mb-lg">
            <div class="hph-inline-flex hph-items-center hph-justify-center hph-w-24 hph-h-24 hph-rounded-full hph-bg-gray-100 hph-text-gray-400">
                <i class="<?php echo esc_attr($args['icon']); ?> hph-text-4xl"></i>
            </div>
        </div>
        <?php endif; ?>
        
        <h2 class="hph-text-2xl hph-font-semibold hph-text-gray-900 hph-mb-sm">
            <?php echo esc_html($args['title']); ?>
        </h2>
        
        <p class="hph-text-lg hph-text-gray-600 hph-mb-xl">
            <?php echo esc_html($args['message']); ?>
        </p>
        
        <?php if ($args['show_suggestions']) : ?>
        <div class="hph-bg-gray-50 hph-rounded-lg hph-p-lg hph-mb-xl <?php echo $args['layout'] === 'centered' ? 'hph-text-left' : ''; ?>">
            <h3 class="hph-text-md hph-font-semibold hph-mb-sm"><?php esc_html_e('Try these suggestions:', 'happy-place-theme'); ?></h3>
            <ul class="hph-space-y-2">
                <?php if (!empty($args['search_query'])) : ?>
                    <li class="hph-flex hph-items-start">
                        <i class="fas fa-check hph-text-primary hph-mt-1 hph-mr-2"></i>
                        <span class="hph-text-sm hph-text-gray-700"><?php esc_html_e('Check your spelling and try again', 'happy-place-theme'); ?></span>
                    </li>
                    <li class="hph-flex hph-items-start">
                        <i class="fas fa-check hph-text-primary hph-mt-1 hph-mr-2"></i>
                        <span class="hph-text-sm hph-text-gray-700"><?php esc_html_e('Use more general search terms', 'happy-place-theme'); ?></span>
                    </li>
                <?php endif; ?>
                
                <?php if (!empty($args['current_filters'])) : ?>
                    <li class="hph-flex hph-items-start">
                        <i class="fas fa-check hph-text-primary hph-mt-1 hph-mr-2"></i>
                        <span class="hph-text-sm hph-text-gray-700"><?php esc_html_e('Remove one or more filters', 'happy-place-theme'); ?></span>
                    </li>
                    <li class="hph-flex hph-items-start">
                        <i class="fas fa-check hph-text-primary hph-mt-1 hph-mr-2"></i>
                        <span class="hph-text-sm hph-text-gray-700"><?php esc_html_e('Expand your search criteria', 'happy-place-theme'); ?></span>
                    </li>
                <?php endif; ?>
                
                <li class="hph-flex hph-items-start">
                    <i class="fas fa-check hph-text-primary hph-mt-1 hph-mr-2"></i>
                    <span class="hph-text-sm hph-text-gray-700"><?php esc_html_e('Browse our categories below', 'happy-place-theme'); ?></span>
                </li>
                <li class="hph-flex hph-items-start">
                    <i class="fas fa-check hph-text-primary hph-mt-1 hph-mr-2"></i>
                    <span class="hph-text-sm hph-text-gray-700"><?php esc_html_e('Contact us for personalized help', 'happy-place-theme'); ?></span>
                </li>
            </ul>
        </div>
        <?php endif; ?>
        
        <div class="hph-flex hph-flex-wrap hph-gap-sm <?php echo $args['layout'] === 'centered' ? 'hph-justify-center' : ''; ?> hph-mb-xl">
            
            <?php if ($has_filters && $args['show_clear_filters']) : ?>
            <a href="<?php echo esc_url(get_post_type_archive_link($args['post_type'])); ?>" class="hph-btn hph-btn-primary">
                <i class="fas fa-redo"></i>
                <span><?php esc_html_e('Clear All & Start Over', 'happy-place-theme'); ?></span>
            </a>
            <?php endif; ?>
            
            <a href="<?php echo esc_url(home_url('/')); ?>" class="hph-btn hph-btn-outline">
                <i class="fas fa-home"></i>
                <span><?php esc_html_e('Go to Homepage', 'happy-place-theme'); ?></span>
            </a>
            
            <?php if ($args['show_contact']) : ?>
            <a href="<?php echo esc_url(home_url('/contact')); ?>" class="hph-btn hph-btn-outline">
                <i class="fas fa-phone"></i>
                <span><?php esc_html_e('Contact Us', 'happy-place-theme'); ?></span>
            </a>
            <?php endif; ?>
            
        </div>
        
        <?php if ($args['show_search']) : ?>
        <div class="hph-max-w-md <?php echo $args['layout'] === 'centered' ? 'hph-mx-auto' : ''; ?> hph-mb-xl">
            <h3 class="hph-text-md hph-font-semibold hph-mb-sm"><?php esc_html_e('Try a new search', 'happy-place-theme'); ?></h3>
            <form action="<?php echo esc_url(home_url('/')); ?>" method="get" class="hph-flex hph-gap-sm">
                <?php if ($args['post_type'] !== 'post') : ?>
                    <input type="hidden" name="post_type" value="<?php echo esc_attr($args['post_type']); ?>">
                <?php endif; ?>
                
                <div class="hph-flex-1">
                    <input 
                        type="search" 
                        name="s" 
                        placeholder="<?php esc_attr_e('Search...', 'happy-place-theme'); ?>"
                        class="hph-form-control hph-w-full"
                        value=""
                        autofocus
                    >
                </div>
                <button type="submit" class="hph-btn hph-btn-primary">
                    <i class="fas fa-search"></i>
                    <span class="hph-hidden sm:hph-inline"><?php esc_html_e('Search', 'happy-place-theme'); ?></span>
                </button>
            </form>
        </div>
        <?php endif; ?>
        
        <?php if ($args['show_popular']) : ?>
            <?php
            // Get popular/featured items
            $popular_args = [
                'post_type' => $args['post_type'],
                'posts_per_page' => 3,
                'orderby' => 'comment_count',
                'order' => 'DESC'
            ];
            
            // For listings, get featured
            if ($args['post_type'] === 'listing') {
                $popular_args['meta_key'] = 'featured';
                $popular_args['meta_value'] = '1';
                $popular_args['orderby'] = 'date';
            }
            
            $popular_query = new WP_Query($popular_args);
            
            if ($popular_query->have_posts()) :
            ?>
            <div class="hph-border-t hph-border-gray-200 hph-pt-xl">
                <h3 class="hph-text-lg hph-font-semibold hph-mb-lg">
                    <?php
                    switch ($args['post_type']) {
                        case 'listing':
                            esc_html_e('Featured Properties', 'happy-place-theme');
                            break;
                        case 'agent':
                            esc_html_e('Featured Agents', 'happy-place-theme');
                            break;
                        default:
                            esc_html_e('Popular Posts', 'happy-place-theme');
                    }
                    ?>
                </h3>
                
                <div class="hph-grid hph-grid-cols-1 md:hph-grid-cols-3 hph-gap-md">
                    <?php while ($popular_query->have_posts()) : $popular_query->the_post(); ?>
                        <article class="hph-card hph-card--minimal">
                            <?php if (has_post_thumbnail()) : ?>
                            <div class="hph-card__image hph-mb-sm">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('medium', ['class' => 'hph-w-full hph-h-full hph-object-cover']); ?>
                                </a>
                            </div>
                            <?php endif; ?>
                            
                            <div class="hph-card__content hph-p-0">
                                <h4 class="hph-card__title hph-text-md">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h4>
                                
                                <?php if ($args['post_type'] === 'listing' && function_exists('hpt_get_listing_price')) : ?>
                                    <p class="hph-text-lg hph-font-bold hph-text-primary hph-mt-2">
                                        <?php echo hpt_get_listing_price(); ?>
                                    </p>
                                <?php elseif ($args['post_type'] === 'agent' && function_exists('hpt_get_agent')) : ?>
                                    <?php $agent_data = hpt_get_agent(get_the_ID()); ?>
                                    <?php if ($agent_data) : ?>
                                        <p class="hph-text-sm hph-text-gray-600 hph-mt-2">
                                            <?php echo esc_html($agent_data['title'] ?? ''); ?>
                                        </p>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <p class="hph-text-sm hph-text-gray-600 hph-mt-2">
                                        <?php echo wp_trim_words(get_the_excerpt(), 15); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <a href="<?php the_permalink(); ?>" class="hph-inline-flex hph-items-center hph-text-sm hph-text-primary hover:hph-text-primary-dark hph-mt-3">
                                    <?php esc_html_e('View Details', 'happy-place-theme'); ?>
                                    <i class="fas fa-arrow-right hph-ml-2"></i>
                                </a>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
                
                <div class="hph-text-center hph-mt-lg">
                    <a href="<?php echo esc_url(get_post_type_archive_link($args['post_type'])); ?>" class="hph-btn hph-btn-primary">
                        <?php
                        switch ($args['post_type']) {
                            case 'listing':
                                esc_html_e('View All Properties', 'happy-place-theme');
                                break;
                            case 'agent':
                                esc_html_e('View All Agents', 'happy-place-theme');
                                break;
                            default:
                                esc_html_e('View All Posts', 'happy-place-theme');
                        }
                        ?>
                    </a>
                </div>
            </div>
            <?php
            endif;
            wp_reset_postdata();
            ?>
        <?php endif; ?>
        
        <?php if ($args['show_contact'] && ($args['post_type'] === 'listing' || $args['post_type'] === 'agent')) : ?>
        <div class="hph-bg-primary-50 hph-rounded-lg hph-p-xl hph-mt-xl">
            <div class="hph-text-center">
                <i class="fas fa-headset hph-text-4xl hph-text-primary hph-mb-md"></i>
                <h3 class="hph-text-xl hph-font-semibold hph-mb-sm">
                    <?php esc_html_e('Need Personal Assistance?', 'happy-place-theme'); ?>
                </h3>
                <p class="hph-text-gray-700 hph-mb-lg">
                    <?php esc_html_e('Our real estate experts are ready to help you find exactly what you\'re looking for.', 'happy-place-theme'); ?>
                </p>
                <div class="hph-flex hph-flex-wrap hph-gap-sm hph-justify-center">
                    <a href="tel:302-555-0100" class="hph-btn hph-btn-primary hph-btn-lg">
                        <i class="fas fa-phone"></i>
                        <span><?php esc_html_e('Call (302) 555-0100', 'happy-place-theme'); ?></span>
                    </a>
                    <a href="<?php echo esc_url(home_url('/contact')); ?>" class="hph-btn hph-btn-white hph-btn-lg">
                        <i class="fas fa-envelope"></i>
                        <span><?php esc_html_e('Send Message', 'happy-place-theme'); ?></span>
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
</div>