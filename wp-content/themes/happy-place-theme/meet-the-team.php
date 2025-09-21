<?php
/**
 * Meet The Team Template - Staff and Agents Combined
 * Template Name: Meet The Team
 * 
 * @package HappyPlaceTheme
 */

get_header();

// Enqueue archive-specific assets
wp_enqueue_style('hph-hero-search-form', get_template_directory_uri() . '/assets/css/framework/components/organisms/hero-search-form.css', ['hph-framework'], filemtime(get_template_directory() . '/assets/css/framework/components/organisms/hero-search-form.css'));

// Enqueue meet-the-team specific styles
wp_enqueue_style('hph-meet-the-team', get_template_directory_uri() . '/assets/css/framework/pages/meet-the-team.css', ['hph-framework'], filemtime(get_template_directory() . '/assets/css/framework/pages/meet-the-team.css'));

// Get filter parameters
$search = sanitize_text_field($_GET['search'] ?? '');
$specialty = sanitize_text_field($_GET['specialty'] ?? '');
$experience = sanitize_text_field($_GET['experience'] ?? '');
$sort = sanitize_text_field($_GET['sort'] ?? 'name_asc');

// Build query args for staff
$staff_args = [
    'post_type' => 'staff',
    'post_status' => 'publish', 
    'posts_per_page' => -1,
    'meta_query' => []
];

// Build query args for agents  
$agent_args = [
    'post_type' => 'agent',
    'post_status' => 'publish', 
    'posts_per_page' => -1,
    'meta_query' => []
];

// Add search to both queries
if (!empty($search)) {
    $staff_args['s'] = $search;
    $agent_args['s'] = $search;
}

// Add specialty filter to agents only (staff don't have specialties)
if (!empty($specialty)) {
    $agent_args['meta_query'][] = [
        'key' => 'specialties',
        'value' => $specialty,
        'compare' => 'LIKE'
    ];
}

// Add experience filter to both
if (!empty($experience)) {
    $experience_meta_query = [];
    switch ($experience) {
        case '0-5':
            $experience_meta_query = [
                'key' => 'years_experience',
                'value' => [0, 5],
                'type' => 'NUMERIC',
                'compare' => 'BETWEEN'
            ];
            break;
        case '5-10':
            $experience_meta_query = [
                'key' => 'years_experience',
                'value' => [5, 10],
                'type' => 'NUMERIC',
                'compare' => 'BETWEEN'
            ];
            break;
        case '10+':
            $experience_meta_query = [
                'key' => 'years_experience',
                'value' => 10,
                'type' => 'NUMERIC',
                'compare' => '>='
            ];
            break;
    }
    if (!empty($experience_meta_query)) {
        $staff_args['meta_query'][] = $experience_meta_query;
        $agent_args['meta_query'][] = $experience_meta_query;
    }
}

// Add sorting to both queries
$sort_config = [];
switch ($sort) {
    case 'name_desc':
        $sort_config = ['orderby' => 'title', 'order' => 'DESC'];
        break;
    case 'experience_desc':
        $sort_config = ['meta_key' => 'years_experience', 'orderby' => 'meta_value_num', 'order' => 'DESC'];
        break;
    case 'experience_asc':
        $sort_config = ['meta_key' => 'years_experience', 'orderby' => 'meta_value_num', 'order' => 'ASC'];
        break;
    case 'recent':
        $sort_config = ['orderby' => 'date', 'order' => 'DESC'];
        break;
    default: // name_asc
        $sort_config = ['orderby' => 'title', 'order' => 'ASC'];
        break;
}

$staff_args = array_merge($staff_args, $sort_config);
$agent_args = array_merge($agent_args, $sort_config);

// Execute queries
$staff_query = new WP_Query($staff_args);
$agents_query = new WP_Query($agent_args);

// Calculate total count
$total_count = $staff_query->found_posts + $agents_query->found_posts;
?>

<div class="hph-archive-container">

    <!-- Archive Hero Section -->
    <div class="hph-archive-hero-section">
        <?php 
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
                            Meet Our Team
                        </h1>
                        <p class="hph-text-xl hph-opacity-90 hph-max-w-2xl hph-mx-auto hph-mb-lg">
                            <?php if ($total_count > 0): ?>
                                Meet our team of <?php echo $total_count; ?> experienced professionals
                            <?php else: ?>
                                Meet our team of dedicated real estate professionals
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
                                <label for="hero-search" class="hph-block hph-text-lg hph-font-semibold hph-text-gray-900 hph-mb-md">
                                    Find Your Perfect Team Member
                                </label>
                                <div class="hph-relative hph-max-w-lg hph-mx-auto">
                                    <input type="text" 
                                           id="hero-search" 
                                           name="search" 
                                           placeholder="Search by name..."
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
                            
                            <!-- Submit Button -->
                            <div class="hph-text-center">
                                <button type="submit" class="hph-btn hph-btn-primary hph-btn-lg hph-px-xl hph-py-md hph-font-semibold hph-rounded-lg">
                                    <i class="fas fa-search hph-mr-sm"></i>
                                    Search Team
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Grid Section -->
    <?php if ($staff_query->have_posts() || $agents_query->have_posts()) : ?>
    <div class="hph-results-section hph-py-xl">
        <div class="hph-container">
            <div class="hph-results-grid">
                <div class="hph-grid hph-grid-cols-1 md:hph-grid-cols-2 xl:hph-grid-cols-3 2xl:hph-grid-cols-4 hph-gap-xl">
                    
                    <?php 
                    // Display staff first
                    if ($staff_query->have_posts()) :
                        while ($staff_query->have_posts()) : $staff_query->the_post();
                            get_template_part('template-parts/agent-card', null, array(
                                'agent_id' => get_the_ID()
                            ));
                        endwhile;
                    endif;
                    
                    // Then display agents
                    if ($agents_query->have_posts()) :
                        while ($agents_query->have_posts()) : $agents_query->the_post();
                            get_template_part('template-parts/agent-card', null, array(
                                'agent_id' => get_the_ID()
                            ));
                        endwhile;
                    endif;
                    ?>
                    
                </div>
            </div>
        </div>
    </div>
    <?php else : ?>
    <!-- No Results Section -->
    <div class="hph-no-results hph-text-center hph-py-2xl">
        <div class="hph-container">
            <div class="hph-max-w-md hph-mx-auto">
                <i class="fas fa-users hph-text-6xl hph-text-gray-300 hph-mb-lg"></i>
                <h2 class="hph-text-2xl hph-font-bold hph-text-gray-900 hph-mb-md">No Team Members Found</h2>
                <p class="hph-text-gray-600 hph-mb-lg">
                    We couldn't find any team members matching your criteria. Try adjusting your search or filters.
                </p>
                <a href="<?php echo get_permalink(); ?>" 
                   class="hph-btn hph-btn-primary">
                    <i class="fas fa-search hph-mr-sm"></i>
                    View All Team Members
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
