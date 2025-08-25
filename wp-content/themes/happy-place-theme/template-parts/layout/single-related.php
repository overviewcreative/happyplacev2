<?php
/**
 * Base Single Related - Related items section
 *
 * @package HappyPlaceTheme
 */

$related_args = wp_parse_args($args ?? [], [
    'post_id' => get_the_ID(),
    'post_type' => get_post_type(),
    'count' => 4,
    'columns' => 2,
    'title' => '',
    'show_section' => true,
    'container_class' => 'hph-container',
    'layout' => 'grid', // grid, list, carousel
    'card_style' => 'default',
    'query_args' => []
]);

if (!$related_args['show_section']) {
    return;
}

// Set default title based on post type
if (empty($related_args['title'])) {
    switch ($related_args['post_type']) {
        case 'listing':
            $related_args['title'] = __('Similar Properties', 'happy-place-theme');
            break;
        case 'agent':
            $related_args['title'] = __('Other Agents', 'happy-place-theme');
            break;
        case 'open_house':
            $related_args['title'] = __('Upcoming Open Houses', 'happy-place-theme');
            break;
        default:
            $related_args['title'] = __('Related Posts', 'happy-place-theme');
            break;
    }
}

// Get related items
$related_posts = [];

if (!empty($related_args['query_args'])) {
    // Use custom query args if provided
    $query = new WP_Query($related_args['query_args']);
    $related_posts = $query->posts;
} else {
    // Get related items based on post type
    switch ($related_args['post_type']) {
        case 'listing':
            if (function_exists('hpt_get_similar_listings')) {
                $related_posts = hpt_get_similar_listings($related_args['post_id'], $related_args['count']);
            } else {
                // Fallback: get by property type or location
                $property_types = wp_get_post_terms($related_args['post_id'], 'property_type', ['fields' => 'ids']);
                $city = get_field('listing_city', $related_args['post_id']);
                
                $query_args = [
                    'post_type' => 'listing',
                    'post_status' => 'publish',
                    'posts_per_page' => $related_args['count'],
                    'post__not_in' => [$related_args['post_id']],
                    'orderby' => 'rand'
                ];
                
                if (!empty($property_types)) {
                    $query_args['tax_query'] = [
                        [
                            'taxonomy' => 'property_type',
                            'field' => 'term_id',
                            'terms' => $property_types
                        ]
                    ];
                } elseif (!empty($city)) {
                    $query_args['meta_query'] = [
                        [
                            'key' => 'listing_city',
                            'value' => $city,
                            'compare' => '='
                        ]
                    ];
                }
                
                $query = new WP_Query($query_args);
                $related_posts = $query->posts;
            }
            break;
            
        case 'agent':
            // Get other agents from same office/company
            $agent_company = get_field('agent_company', $related_args['post_id']);
            $agent_office = get_field('agent_office', $related_args['post_id']);
            
            $query_args = [
                'post_type' => 'agent',
                'post_status' => 'publish',
                'posts_per_page' => $related_args['count'],
                'post__not_in' => [$related_args['post_id']],
                'orderby' => 'rand'
            ];
            
            if (!empty($agent_company)) {
                $query_args['meta_query'] = [
                    [
                        'key' => 'agent_company',
                        'value' => $agent_company,
                        'compare' => '='
                    ]
                ];
            } elseif (!empty($agent_office)) {
                $query_args['meta_query'] = [
                    [
                        'key' => 'agent_office',
                        'value' => $agent_office,
                        'compare' => '='
                    ]
                ];
            }
            
            $query = new WP_Query($query_args);
            $related_posts = $query->posts;
            break;
            
        case 'open_house':
            // Get upcoming open houses
            $query_args = [
                'post_type' => 'open_house',
                'post_status' => 'publish',
                'posts_per_page' => $related_args['count'],
                'post__not_in' => [$related_args['post_id']],
                'meta_query' => [
                    [
                        'key' => 'start_date',
                        'value' => date('Y-m-d'),
                        'compare' => '>='
                    ]
                ],
                'meta_key' => 'start_date',
                'orderby' => 'meta_value',
                'order' => 'ASC'
            ];
            
            $query = new WP_Query($query_args);
            $related_posts = $query->posts;
            break;
            
        default:
            // Get related posts by category or tags
            $categories = wp_get_post_categories($related_args['post_id']);
            $tags = wp_get_post_tags($related_args['post_id'], ['fields' => 'ids']);
            
            $query_args = [
                'post_type' => $related_args['post_type'],
                'post_status' => 'publish',
                'posts_per_page' => $related_args['count'],
                'post__not_in' => [$related_args['post_id']],
                'orderby' => 'rand'
            ];
            
            if (!empty($categories) || !empty($tags)) {
                $tax_query = ['relation' => 'OR'];
                
                if (!empty($categories)) {
                    $tax_query[] = [
                        'taxonomy' => 'category',
                        'field' => 'term_id',
                        'terms' => $categories
                    ];
                }
                
                if (!empty($tags)) {
                    $tax_query[] = [
                        'taxonomy' => 'post_tag',
                        'field' => 'term_id',
                        'terms' => $tags
                    ];
                }
                
                $query_args['tax_query'] = $tax_query;
            }
            
            $query = new WP_Query($query_args);
            $related_posts = $query->posts;
            break;
    }
}

// Don't show section if no related posts
if (empty($related_posts)) {
    return;
}

$section_classes = [
    'hph-single-related',
    'hph-py-2xl',
    'hph-bg-gray-50',
    'hph-animate-fade-in-up',
    'hph-single-related--' . $related_args['post_type'],
    'hph-single-related--' . $related_args['layout']
];
?>

<section class="<?php echo esc_attr(implode(' ', $section_classes)); ?>">
    <div class="<?php echo esc_attr($related_args['container_class']); ?>">
        
        <header class="hph-text-center hph-mb-2xl">
            <h2 class="hph-text-3xl md:hph-text-4xl hph-font-bold hph-text-gray-900 hph-leading-tight">
                <?php echo esc_html($related_args['title']); ?>
            </h2>
        </header>
        
        <div class="hph-space-y-lg">
            
            <?php if ($related_args['layout'] === 'carousel') : ?>
                
                <div class="hph-relative hph-overflow-hidden hph-rounded-lg" data-carousel>
                    <div class="hph-relative">
                        <div class="hph-flex hph-transition-transform hph-duration-300 hph-ease-in-out" data-carousel-container>
                            <?php foreach ($related_posts as $related_post) : ?>
                                <div class="hph-min-w-0 hph-flex-shrink-0 hph-w-full sm:hph-w-1/2 md:hph-w-1/3 hph-px-sm">
                                    <?php
                                    // Load post-type specific card
                                    hph_component('card', [
                                        'post_id' => $related_post->ID,
                                        'post_type' => $related_args['post_type'],
                                        'layout' => $related_args['card_style'],
                                        'show_actions' => true,
                                        'context' => 'related'
                                    ]);
                                    ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="hph-absolute hph-inset-y-0 hph-left-0 hph-flex hph-items-center">
                        <button class="hph-ml-sm hph-w-10 hph-h-10 hph-bg-white hph-shadow-lg hph-rounded-full hph-flex hph-items-center hph-justify-center hph-text-gray-600 hph-hover:text-primary-600 hph-transition-all hph-duration-200 hph-hover:scale-110" 
                                aria-label="<?php esc_attr_e('Previous', 'happy-place-theme'); ?>" 
                                data-action="prev">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                    </div>
                    <div class="hph-absolute hph-inset-y-0 hph-right-0 hph-flex hph-items-center">
                        <button class="hph-mr-sm hph-w-10 hph-h-10 hph-bg-white hph-shadow-lg hph-rounded-full hph-flex hph-items-center hph-justify-center hph-text-gray-600 hph-hover:text-primary-600 hph-transition-all hph-duration-200 hph-hover:scale-110" 
                                aria-label="<?php esc_attr_e('Next', 'happy-place-theme'); ?>" 
                                data-action="next">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                
            <?php elseif ($related_args['layout'] === 'list') : ?>
                
                <div class="hph-space-y-lg">
                    <?php foreach ($related_posts as $related_post) : ?>
                        <?php
                        hph_component('card', [
                            'post_id' => $related_post->ID,
                            'post_type' => $related_args['post_type'],
                            'layout' => 'list',
                            'show_actions' => true,
                            'context' => 'related'
                        ]);
                        ?>
                    <?php endforeach; ?>
                </div>
                
            <?php else : ?>
                
                <!-- Grid Layout (Default) -->
                <div class="hph-grid hph-grid-cols-1 md:hph-grid-cols-<?php echo esc_attr($related_args['columns']); ?> hph-gap-lg">
                    <?php foreach ($related_posts as $related_post) : ?>
                        <div class="hph-animate-fade-in-up">
                            <?php
                            hph_component('card', [
                                'post_id' => $related_post->ID,
                                'post_type' => $related_args['post_type'],
                                'layout' => $related_args['card_style'],
                                'show_actions' => true,
                                'context' => 'related'
                            ]);
                            ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
            <?php endif; ?>
            
        </div>
        
        <?php
        // Show link to view all if there might be more
        $archive_link = get_post_type_archive_link($related_args['post_type']);
        if ($archive_link) :
        ?>
            <footer class="hph-text-center hph-mt-2xl">
                <a href="<?php echo esc_url($archive_link); ?>" class="hph-inline-flex hph-items-center hph-gap-sm hph-px-xl hph-py-md hph-border-2 hph-border-primary-600 hph-text-primary-600 hph-font-semibold hph-rounded-lg hph-transition-all hph-duration-300 hph-hover:bg-primary-600 hph-hover:text-white hph-hover:scale-105">
                    <?php printf(__('View All %s', 'happy-place-theme'), get_post_type_object($related_args['post_type'])->labels->name); ?>
                    <i class="fas fa-arrow-right hph-ml-sm"></i>
                </a>
            </footer>
        <?php endif; ?>
        
    </div>
</section>