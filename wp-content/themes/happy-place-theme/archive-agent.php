<?php
/**
 * Enhanced Agent Archive Template
 * Features: Enhanced filtering, search, and layout similar to listing archive
 */

// Agent archive assets are now handled automatically by theme-assets.php bundles

// Add body class for archive page styling
add_filter('body_class', function($classes) {
    $classes[] = 'archive-agent';
    return $classes;
});

get_header();

// DEBUG: Archive template is being used
echo '<!-- ARCHIVE-AGENT.PHP TEMPLATE LOADED -->';

// Get filter parameters
$search = sanitize_text_field($_GET['search'] ?? '');
$specialty = sanitize_text_field($_GET['specialty'] ?? '');
$experience = sanitize_text_field($_GET['experience'] ?? '');
$sort = sanitize_text_field($_GET['sort'] ?? 'name_asc');

// Build query
$paged = get_query_var('paged') ? get_query_var('paged') : 1;
$args = [
    'post_type' => 'agent',
    'post_status' => 'publish', 
    'posts_per_page' => -1, // Show all agents on one page
    'meta_query' => []
];

// Add search
if (!empty($search)) {
    $args['s'] = $search;
}

// Add specialty filter
if (!empty($specialty)) {
    $args['meta_query'][] = [
        'key' => 'specialties',
        'value' => $specialty,
        'compare' => 'LIKE'
    ];
}

// Add experience filter
if (!empty($experience)) {
    switch ($experience) {
        case '0-5':
            $args['meta_query'][] = [
                'key' => 'years_experience',
                'value' => [0, 5],
                'type' => 'NUMERIC',
                'compare' => 'BETWEEN'
            ];
            break;
        case '5-10':
            $args['meta_query'][] = [
                'key' => 'years_experience',
                'value' => [5, 10],
                'type' => 'NUMERIC',
                'compare' => 'BETWEEN'
            ];
            break;
        case '10+':
            $args['meta_query'][] = [
                'key' => 'years_experience',
                'value' => 10,
                'type' => 'NUMERIC',
                'compare' => '>='
            ];
            break;
    }
}

// Add sorting
switch ($sort) {
    case 'name_desc':
        $args['orderby'] = 'title';
        $args['order'] = 'DESC';
        break;
    case 'experience_desc':
        $args['meta_key'] = 'years_experience';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'DESC';
        break;
    case 'experience_asc':
        $args['meta_key'] = 'years_experience';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'ASC';
        break;
    case 'recent':
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
        break;
    default: // name_asc
        $args['orderby'] = 'title';
        $args['order'] = 'ASC';
        break;
}

$agents = new WP_Query($args);
?>

<div class="hph-archive-container">

    <!-- Archive Hero Section -->
    <div class="hph-archive-hero-section">
        <?php 
        // Get count of current agents
        $total_agents = $agents->found_posts;
        
        // Build search terms display
        $search_terms = [];
        if ($search) $search_terms[] = "Search: \"$search\"";
        if ($specialty) $search_terms[] = "Specialty: " . ucfirst(str_replace('-', ' ', $specialty));
        if ($experience) $search_terms[] = "Experience: $experience years";
        ?>
        
        <div class="hph-relative hph-bg-primary hph-text-white">
            <!-- Background Pattern/Image -->
            <div class="hph-absolute hph-inset-0 hph-opacity-10" 
                 style="background-image: url('<?php echo get_template_directory_uri(); ?>/assets/images/pattern-bg.svg'); background-repeat: repeat;"></div>
            
            <div class="hph-container hph-relative hph-z-10">
                <div class="hph-py-xl lg:hph-py-2xl">
                    
                    <!-- Hero Content -->
                    <div class="hph-text-center hph-mb-xl">
                        <h1 class="hph-text-4xl lg:hph-text-5xl hph-font-bold hph-mb-md">
                            Our Expert Agents
                        </h1>
                        <p class="hph-text-xl hph-opacity-90 hph-max-w-2xl hph-mx-auto hph-mb-lg">
                            <?php if ($total_agents > 0): ?>
                                Find the perfect agent from our team of <?php echo $total_agents; ?> experienced professionals
                            <?php else: ?>
                                Find the perfect agent to help you buy or sell your home
                            <?php endif; ?>
                        </p>
                        
                        <?php if (!empty($search_terms)): ?>
                        <div class="hph-flex hph-flex-wrap hph-gap-sm hph-justify-center hph-mb-md">
                            <?php foreach ($search_terms as $index => $term): ?>
                            <span class="hph-inline-flex hph-items-center hph-px-md hph-py-sm hph-rounded-full hph-text-sm hph-font-medium <?php echo $index === 0 ? 'hph-bg-white hph-bg-opacity-20' : 'hph-bg-white hph-bg-opacity-10'; ?> hph-border hph-border-white hph-border-opacity-30">
                                <?php echo esc_html($term); ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Enhanced Search Form -->
                    <div class="hph-bg-white hph-rounded-xl hph-shadow-2xl hph-p-lg hph-max-w-4xl hph-mx-auto">
                        <form method="get" class="hph-space-y-lg">
                            
                            <!-- Main Search Bar -->
                            <div class="hph-text-center">
                                <label for="hero-agent-search" class="hph-block hph-text-lg hph-font-semibold hph-text-gray-900 hph-mb-md">
                                    Find Your Perfect Agent
                                </label>
                                <div class="hph-relative hph-max-w-lg hph-mx-auto">
                                    <input type="text" 
                                           id="hero-agent-search" 
                                           name="search" 
                                           placeholder="Search by agent name..."
                                           value="<?php echo esc_attr($search); ?>"
                                           class="hph-w-full hph-px-lg hph-py-lg hph-text-lg hph-rounded-lg hph-border-2 hph-border-gray-200 focus:hph-border-primary hph-transition-colors">
                                    <i class="fas fa-search hph-absolute hph-right-lg hph-top-1/2 hph-transform hph--translate-y-1/2 hph-text-gray-400"></i>
                                </div>
                            </div>

                            <!-- Filter Grid -->
                            <div class="hph-grid hph-grid-cols-1 md:hph-grid-cols-3 hph-gap-md">
                                
                                <!-- Specialty Filter -->
                                <div>
                                    <label for="specialty" class="hph-block hph-text-sm hph-font-medium hph-text-gray-700 hph-mb-sm">Specialty</label>
                                    <select id="specialty" name="specialty" class="hph-form-select">
                                        <option value="">All Specialties</option>
                                        <option value="Residential" <?php selected($specialty, 'Residential'); ?>>Residential</option>
                                        <option value="Commercial" <?php selected($specialty, 'Commercial'); ?>>Commercial</option>
                                        <option value="First-time Buyers" <?php selected($specialty, 'First-time Buyers'); ?>>First-time Buyers</option>
                                        <option value="Luxury" <?php selected($specialty, 'Luxury'); ?>>Luxury Homes</option>
                                        <option value="Investment" <?php selected($specialty, 'Investment'); ?>>Investment Properties</option>
                                        <option value="Relocation" <?php selected($specialty, 'Relocation'); ?>>Relocation</option>
                                    </select>
                                </div>
                                
                                <!-- Experience Filter -->
                                <div>
                                    <label for="experience" class="hph-block hph-text-sm hph-font-medium hph-text-gray-700 hph-mb-sm">Experience</label>
                                    <select id="experience" name="experience" class="hph-form-select">
                                        <option value="">Any Experience</option>
                                        <option value="0-5" <?php selected($experience, '0-5'); ?>>0-5 Years</option>
                                        <option value="5-10" <?php selected($experience, '5-10'); ?>>5-10 Years</option>
                                        <option value="10+" <?php selected($experience, '10+'); ?>>10+ Years</option>
                                    </select>
                                </div>
                                
                                <!-- Sort Order -->
                                <div>
                                    <label for="sort" class="hph-block hph-text-sm hph-font-medium hph-text-gray-700 hph-mb-sm">Sort By</label>
                                    <select id="sort" name="sort" class="hph-form-select">
                                        <option value="name_asc" <?php selected($sort, 'name_asc'); ?>>Name A-Z</option>
                                        <option value="name_desc" <?php selected($sort, 'name_desc'); ?>>Name Z-A</option>
                                        <option value="experience_desc" <?php selected($sort, 'experience_desc'); ?>>Most Experienced</option>
                                        <option value="experience_asc" <?php selected($sort, 'experience_asc'); ?>>Least Experienced</option>
                                        <option value="recent" <?php selected($sort, 'recent'); ?>>Recently Added</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="hph-flex hph-flex-col sm:hph-flex-row hph-gap-sm hph-justify-center hph-items-center">
                                <button type="submit" class="hph-btn hph-btn-primary hph-btn-lg">
                                    <i class="fas fa-search hph-mr-sm"></i>
                                    Search Agents
                                </button>
                                
                                <?php if (!empty($search_terms)): ?>
                                <a href="<?php echo get_post_type_archive_link('agent'); ?>" 
                                   class="hph-btn hph-btn-outline-gray hph-btn-lg">
                                    <i class="fas fa-times hph-mr-sm"></i>
                                    Clear All Filters
                                </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Agents Grid Section -->
    <?php if ($agents->have_posts()) : ?>
    <div class="hph-results-section hph-py-xl">
        <div class="hph-container">
            <div class="hph-results-grid">
                <div class="hph-grid hph-grid-cols-1 md:hph-grid-cols-2 xl:hph-grid-cols-3 2xl:hph-grid-cols-4 hph-gap-xl">
                    <?php while ($agents->have_posts()) : $agents->the_post(); ?>
                        <?php get_template_part('template-parts/agent-card', null, array(
                            'agent_id' => get_the_ID()
                        )); ?>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
    <?php else : ?>
    <!-- No Results Section -->
    <div class="hph-no-results hph-text-center hph-py-2xl">
        <div class="hph-container">
            <div class="hph-max-w-md hph-mx-auto">
                <i class="fas fa-user-friends hph-text-6xl hph-text-gray-300 hph-mb-lg"></i>
                <h2 class="hph-text-2xl hph-font-bold hph-text-gray-900 hph-mb-md">No Agents Found</h2>
                <p class="hph-text-gray-600 hph-mb-lg">
                    We couldn't find any agents matching your criteria. Try adjusting your search or filters.
                </p>
                <a href="<?php echo get_post_type_archive_link('agent'); ?>" 
                   class="hph-btn hph-btn-primary">
                    <i class="fas fa-search hph-mr-sm"></i>
                    View All Agents
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php
wp_reset_postdata();
get_footer();
?>
