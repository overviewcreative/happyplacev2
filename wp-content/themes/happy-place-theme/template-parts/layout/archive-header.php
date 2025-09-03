<?php
/**
 * Archive Hero Header - Post Type Adaptive
 * 
 * @package HappyPlaceTheme
 */

// Detect current post type and context
$current_post_type = get_post_type() ?: get_query_var('post_type', 'post');

// Post type specific configurations
$hero_configs = [
    'agent' => [
        'title' => 'Our Real Estate Agents',
        'subtitle' => 'Meet our experienced team of professionals ready to help you find your dream home',
        'search_placeholder' => 'Search agents by name, specialty, or location...',
        'background_type' => 'gradient',
        'gradient' => 'linear-gradient(135deg, #51bae0 0%, #0c4a6e 100%)',
        'quick_filters' => [
            'featured' => 'Featured',
            'luxury' => 'Luxury Specialist',
            'commercial' => 'Commercial',
            'residential' => 'Residential',
            'new-construction' => 'New Construction'
        ],
        'stats' => [
            'total_agents' => wp_count_posts('agent')->publish,
            'average_experience' => '15+ years',
            'properties_sold' => '2,500+'
        ]
    ],
    'listing' => [
        'title' => 'Available Properties',
        'subtitle' => 'Discover your perfect home from our curated selection',
        'search_placeholder' => 'Search by address, city, ZIP, or MLS#...',
        'background_type' => 'gradient',
        'gradient' => 'linear-gradient(135deg, #7c9b59 0%, #4a5e35 100%)',
        'quick_filters' => [
            'for-sale' => 'For Sale',
            'for-rent' => 'For Rent',
            'new-listing' => 'New Listings',
            'open-house' => 'Open Houses',
            'reduced' => 'Price Reduced'
        ],
        'stats' => [
            'active_listings' => wp_count_posts('listing')->publish,
            'avg_days_on_market' => '28',
            'sold_this_month' => '45'
        ]
    ],
    'post' => [
        'title' => get_the_archive_title(),
        'subtitle' => get_the_archive_description(),
        'search_placeholder' => 'Search articles...',
        'background_type' => 'gradient',
        'gradient' => 'linear-gradient(135deg, #e8a87c 0%, #9c6347 100%)',
        'quick_filters' => [],
        'stats' => []
    ]
];

// Get config for current post type
$config = $hero_configs[$current_post_type] ?? $hero_configs['post'];

// Context-aware title/subtitle detection
if (is_tax()) {
    $term = get_queried_object();
    $config['title'] = single_term_title('', false);
    $config['subtitle'] = $term->description;
} elseif (is_search()) {
    global $wp_query;
    $total_results = $wp_query->found_posts ?? 0;
    $config['title'] = sprintf('Search Results for "%s"', get_search_query());
    $config['subtitle'] = sprintf('Found %d results', $total_results);
} elseif (is_author()) {
    $author = get_queried_object();
    $config['title'] = 'Agent Profile: ' . $author->display_name;
    $config['subtitle'] = $author->description;
} elseif (is_date()) {
    $config['title'] = get_the_archive_title();
    $config['subtitle'] = get_the_archive_description();
}

// Dynamic filter detection from data
if ($current_post_type === 'agent') {
    // Get from ACF field choices if available
    $specialty_field = get_field_object('field_specialties');
    if ($specialty_field && isset($specialty_field['choices'])) {
        $config['quick_filters'] = array_merge($config['quick_filters'], $specialty_field['choices']);
    }
} elseif ($current_post_type === 'listing') {
    // Get from property type taxonomy
    $property_types = get_terms(['taxonomy' => 'property_type', 'hide_empty' => true]);
    if (!is_wp_error($property_types)) {
        $filters = [];
        foreach ($property_types as $type) {
            $filters[$type->slug] = $type->name;
        }
        $config['quick_filters'] = array_merge($config['quick_filters'], $filters);
    }
}

// Parse arguments with dynamic config as defaults
$header_args = wp_parse_args($args ?? [], array_merge($config, [
    'post_type' => $current_post_type,
    'total_results' => 0,
    'background_image' => '',
    'show_search' => true,
    'show_quick_filters' => true,
    'show_stats' => false,
    'filter_options' => $config['quick_filters'] ?? []
]));

?>

<section class="hph-archive-hero hph-archive-hero--<?php echo esc_attr($header_args['post_type']); ?> hero hero-image" 
         data-post-type="<?php echo esc_attr($header_args['post_type']); ?>">
    
    <!-- Background -->
    <div class="hph-hero-background hph-absolute hph-inset-0 hph-z-0" 
         style="<?php 
         if ($header_args['background_type'] === 'gradient' && !empty($header_args['gradient'])) {
             echo 'background: ' . esc_attr($header_args['gradient']) . ';';
         } elseif ($header_args['background_type'] === 'image' && !empty($header_args['background_image'])) {
             echo 'background-image: url(' . esc_url($header_args['background_image']) . '); background-size: cover; background-position: center;';
         }
         ?>"></div>
    
    <!-- Overlay -->
    <div class="hph-hero-overlay hph-absolute hph-inset-0 hph-z-5"></div>
    
    <!-- Content Container -->
    <div class="hph-hero-container hph-relative hph-z-10 hph-w-full hph-flex hph-justify-center">
        <div class="hph-content-row hph-hero-content hph-relative hph-z-10 hph-w-full hph-max-w-6xl hph-text-center hph-text-white hph-mx-auto"><?php
         echo 'background: ' . esc_attr($header_args['gradient']) . ';';
         } elseif ($header_args['background_type'] === 'image' && !empty($header_args['background_image'])) {
             echo 'background-image: url(' . esc_url($header_args['background_image']) . ');';
         }
         ?>"></div>
    <div class="hph-archive-hero__overlay"></div>
    
    <div class="hph-container hph-archive-hero__content">
    <!-- Content Container -->
    <div class="hph-hero-container hph-relative hph-z-10 hph-w-full hph-flex hph-justify-center">
        <div class="hph-content-row hph-hero-content hph-relative hph-z-10 hph-w-full hph-max-w-6xl hph-text-center hph-text-white hph-mx-auto">
            
            <!-- Badge (Post Type) -->
            <div class="hph-mb-lg hph-animate-slide-down">
                <span class="hph-hero-badge hph-inline-flex hph-items-center hph-px-lg hph-py-md hph-rounded-full hph-bg-white hph-bg-opacity-20 hph-text-white hph-text-sm hph-font-semibold hph-backdrop-blur hph-shadow-lg">
                    <?php echo esc_html(ucfirst($header_args['post_type']) . 's'); ?>
                </span>
            </div>
            
            <!-- Hero Title -->
            <h1 class="hph-hero-headline hph-mb-xl hph-text-3xl hph-md:text-4xl hph-lg:text-5xl hph-font-bold hph-leading-tight">
                <?php echo esc_html($header_args['title']); ?>
            </h1>
            
            <?php if ($header_args['subtitle']): ?>
            <!-- Hero Subtitle -->
            <h2 class="hph-hero-subheadline hph-mb-xl hph-text-lg hph-md:text-xl hph-font-medium hph-leading-snug hph-opacity-90">
                <?php echo esc_html($header_args['subtitle']); ?>
            </h2>
            <?php endif; ?>
            
            <?php if ($header_args['total_results']): ?>
            <!-- Results Count -->
            <div class="hph-hero-content-text hph-mb-2xl hph-text-base hph-md:text-lg hph-leading-normal hph-opacity-85">
                <span class="hph-font-semibold"><?php echo number_format($header_args['total_results']); ?></span> 
                <?php echo esc_html($header_args['post_type'] === 'agent' ? 'Agents' : 'Results'); ?> Available
            </div>
            <?php endif; ?>
            
            <!-- Stats Section -->
            <?php if ($header_args['show_stats'] && !empty($header_args['stats'])): ?>
            <div class="hph-archive-hero__stats hph-mb-2xl">
                <div class="hph-stats-grid hph-flex hph-justify-center hph-gap-xl">
                    <?php foreach ($header_args['stats'] as $key => $value): ?>
                        <div class="hph-stat-item hph-text-center">
                            <div class="hph-stat-value hph-text-2xl hph-md:text-3xl hph-font-bold hph-text-accent hph-mb-sm">
                                <?php echo esc_html($value); ?>
                            </div>
                            <div class="hph-stat-label hph-text-xs hph-uppercase hph-tracking-wide hph-opacity-80">
                                <?php echo esc_html(str_replace('_', ' ', ucwords($key, '_'))); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        
        <!-- Search & Filter Bar -->
        <div class="hph-archive-hero__controls">
            <?php if ($header_args['show_search']): ?>
            <form class="hph-archive-hero__search" method="get">
                <div class="hph-search-input-group">
                    <i class="fas fa-search hph-search-icon"></i>
                    <input type="search" 
                           name="s" 
                           class="hph-search-input" 
                           placeholder="<?php echo esc_attr($header_args['search_placeholder']); ?>"
                           value="<?php echo get_search_query(); ?>">
                    <button type="submit" class="hph-search-button">Search</button>
                </div>
            </form>
            <?php endif; ?>
            
            <?php if ($header_args['show_quick_filters'] && !empty($header_args['filter_options'])): ?>
            <div class="hph-archive-hero__filters">
                <div class="hph-quick-filters">
                    <button class="hph-filter-chip hph-filter-chip--active" data-filter="all">
                        All
                    </button>
                    <?php foreach ($header_args['filter_options'] as $key => $label): ?>
                        <button class="hph-filter-chip" data-filter="<?php echo esc_attr($key); ?>">
                            <?php echo esc_html($label); ?>
                        </button>
                    <?php endforeach; ?>
                    <button class="hph-filter-advanced">
                        <i class="fas fa-sliders-h"></i>
                        More Filters
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>


