<?php
/**
 * Enhanced Agent Archive Template
 * Using the same implementation pattern as the listings archive
 * 
 * @package HappyPlaceTheme
 */

get_header();

// Get filter parameters from URL
$search_query = sanitize_text_field($_GET['s'] ?? '');
$specialty = sanitize_text_field($_GET['specialty'] ?? '');
$language = sanitize_text_field($_GET['language'] ?? '');
$office = sanitize_text_field($_GET['office'] ?? '');
$experience = sanitize_text_field($_GET['experience'] ?? '');
$view = sanitize_text_field($_GET['view'] ?? 'grid');
$sort = sanitize_text_field($_GET['sort'] ?? 'name_asc');

// Build query args
$args = [
    'post_type' => 'agent',
    'post_status' => 'publish',
    'posts_per_page' => 60,
    'paged' => get_query_var('paged') ?: 1,
    'meta_query' => ['relation' => 'AND']
];

// Add search
if (!empty($search_query)) {
    $args['s'] = $search_query;
}

// Add specialty filter
if (!empty($specialty)) {
    $args['meta_query'][] = [
        'key' => 'specialties',
        'value' => $specialty,
        'compare' => 'LIKE'
    ];
}

// Add language filter
if (!empty($language)) {
    $args['meta_query'][] = [
        'key' => 'languages',
        'value' => $language,
        'compare' => 'LIKE'
    ];
}

// Add office filter
if (!empty($office)) {
    $args['meta_query'][] = [
        'key' => 'office',
        'value' => intval($office),
        'compare' => '='
    ];
}

// Add experience filter
if (!empty($experience)) {
    switch ($experience) {
        case '1-5':
            $args['meta_query'][] = [
                'key' => 'years_experience',
                'value' => [1, 5],
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
        case '10-15':
            $args['meta_query'][] = [
                'key' => 'years_experience',
                'value' => [10, 15],
                'type' => 'NUMERIC',
                'compare' => 'BETWEEN'
            ];
            break;
        case '15+':
            $args['meta_query'][] = [
                'key' => 'years_experience',
                'value' => 15,
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
    case 'sales_desc':
        $args['meta_key'] = 'total_sales_volume';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'DESC';
        break;
    case 'featured':
        $args['meta_key'] = 'featured';
        $args['orderby'] = ['meta_value' => 'DESC', 'title' => 'ASC'];
        break;
    default: // name_asc
        $args['orderby'] = 'title';
        $args['order'] = 'ASC';
        break;
}

// Execute query
$agents_query = new WP_Query($args);

// Helper function to build URL with params
function build_agent_filter_url($additional_params = []) {
    $base_url = get_post_type_archive_link('agent');
    $current_params = $_GET;
    $params = array_merge($current_params, $additional_params);
    
    // Remove empty params
    $params = array_filter($params, function($value) {
        return $value !== '' && $value !== '0' && $value !== 0;
    });
    
    if (!empty($params)) {
        return $base_url . '?' . http_build_query($params);
    }
    return $base_url;
}
?>

<!-- Hero Section -->
<section class="hph-bg-gradient-primary hph-py-xl hph-mb-lg">
    <div class="hph-container">
        <div class="hph-max-w-3xl hph-mx-auto hph-text-center hph-text-white">
            <h1 class="hph-text-4xl hph-font-bold hph-mb-md">Meet Our Expert Agents</h1>
            <p class="hph-text-lg hph-opacity-90">
                <?php printf('%d professional agents ready to help', $agents_query->found_posts); ?>
            </p>
        </div>
    </div>
</section>

<div class="hph-container hph-py-lg">
    
    <!-- Search & Filters Card -->
    <div class="hph-bg-white hph-rounded-xl hph-shadow-md hph-p-lg hph-mb-lg">
        <form method="get" id="filter-form">
            <!-- Hidden fields for view and sort -->
            <input type="hidden" name="view" value="<?php echo esc_attr($view); ?>">
            <input type="hidden" name="sort" value="<?php echo esc_attr($sort); ?>">
            
            <div class="hph-grid hph-grid-cols-1 md:hph-grid-cols-4 hph-gap-md hph-mb-md">
                
                <!-- Search Field -->
                <div class="md:hph-col-span-2">
                    <label class="hph-block hph-mb-xs hph-font-medium hph-text-gray-700">Search Agents</label>
                    <input type="text" name="s" value="<?php echo esc_attr($search_query); ?>" 
                           placeholder="Name, specialty, or location"
                           class="hph-w-full hph-px-md hph-py-sm hph-border-2 hph-border-gray-200 hph-rounded-lg focus:hph-border-primary focus:hph-outline-none">
                </div>
                
                <!-- Specialty -->
                <div>
                    <label class="hph-block hph-mb-xs hph-font-medium hph-text-gray-700">Specialty</label>
                    <select name="specialty" class="hph-w-full hph-px-md hph-py-sm hph-border-2 hph-border-gray-200 hph-rounded-lg focus:hph-border-primary focus:hph-outline-none">
                        <option value="">All Specialties</option>
                        <option value="buyers-agent" <?php selected($specialty, 'buyers-agent'); ?>>Buyer's Agent</option>
                        <option value="listing-agent" <?php selected($specialty, 'listing-agent'); ?>>Listing Agent</option>
                        <option value="luxury-homes" <?php selected($specialty, 'luxury-homes'); ?>>Luxury Homes</option>
                        <option value="first-time-buyers" <?php selected($specialty, 'first-time-buyers'); ?>>First-Time Buyers</option>
                        <option value="investment-properties" <?php selected($specialty, 'investment-properties'); ?>>Investment Properties</option>
                        <option value="commercial" <?php selected($specialty, 'commercial'); ?>>Commercial</option>
                    </select>
                </div>
                
                <!-- Language -->
                <div>
                    <label class="hph-block hph-mb-xs hph-font-medium hph-text-gray-700">Language</label>
                    <select name="language" class="hph-w-full hph-px-md hph-py-sm hph-border-2 hph-border-gray-200 hph-rounded-lg focus:hph-border-primary focus:hph-outline-none">
                        <option value="">All Languages</option>
                        <option value="english" <?php selected($language, 'english'); ?>>English</option>
                        <option value="spanish" <?php selected($language, 'spanish'); ?>>Spanish</option>
                        <option value="french" <?php selected($language, 'french'); ?>>French</option>
                        <option value="mandarin" <?php selected($language, 'mandarin'); ?>>Mandarin</option>
                        <option value="german" <?php selected($language, 'german'); ?>>German</option>
                    </select>
                </div>
                
                <!-- Experience -->
                <div>
                    <label class="hph-block hph-mb-xs hph-font-medium hph-text-gray-700">Experience</label>
                    <select name="experience" class="hph-w-full hph-px-md hph-py-sm hph-border-2 hph-border-gray-200 hph-rounded-lg focus:hph-border-primary focus:hph-outline-none">
                        <option value="">Any Experience</option>
                        <option value="1-5" <?php selected($experience, '1-5'); ?>>1-5 Years</option>
                        <option value="5-10" <?php selected($experience, '5-10'); ?>>5-10 Years</option>
                        <option value="10-15" <?php selected($experience, '10-15'); ?>>10-15 Years</option>
                        <option value="15+" <?php selected($experience, '15+'); ?>>15+ Years</option>
                    </select>
                </div>
                
                <!-- Office -->
                <div>
                    <label class="hph-block hph-mb-xs hph-font-medium hph-text-gray-700">Office</label>
                    <select name="office" class="hph-w-full hph-px-md hph-py-sm hph-border-2 hph-border-gray-200 hph-rounded-lg focus:hph-border-primary focus:hph-outline-none">
                        <option value="">All Offices</option>
                        <?php
                        // Get offices dynamically
                        $offices = get_posts([
                            'post_type' => 'office',
                            'posts_per_page' => -1,
                            'post_status' => 'publish',
                            'orderby' => 'title',
                            'order' => 'ASC'
                        ]);
                        foreach ($offices as $office_post) {
                            printf(
                                '<option value="%d" %s>%s</option>',
                                $office_post->ID,
                                selected($office, $office_post->ID, false),
                                esc_html($office_post->post_title)
                            );
                        }
                        ?>
                    </select>
                </div>
                
                <!-- Submit Button -->
                <div class="hph-flex hph-items-end">
                    <button type="submit" class="hph-btn hph-btn-primary hph-w-full">
                        <i class="fas fa-search hph-mr-sm"></i> Search
                    </button>
                </div>
            </div>
            
            <!-- Quick Filters -->
            <div class="hph-flex hph-flex-wrap hph-gap-sm hph-pt-md hph-border-t">
                <span class="hph-font-medium hph-text-gray-700 hph-mr-sm">Quick Filters:</span>
                <a href="<?php echo build_agent_filter_url(['sort' => 'featured']); ?>" 
                   class="hph-px-md hph-py-xs hph-rounded-full hph-border-2 <?php echo $sort === 'featured' ? 'hph-bg-primary hph-text-white hph-border-primary' : 'hph-border-gray-300 hph-text-gray-700 hover:hph-border-primary'; ?>">
                    <i class="fas fa-star"></i> Featured
                </a>
                <a href="<?php echo build_agent_filter_url(['specialty' => 'luxury-homes']); ?>"
                   class="hph-px-md hph-py-xs hph-rounded-full hph-border-2 <?php echo $specialty === 'luxury-homes' ? 'hph-bg-primary hph-text-white hph-border-primary' : 'hph-border-gray-300 hph-text-gray-700 hover:hph-border-primary'; ?>">
                    <i class="fas fa-gem"></i> Luxury Specialist
                </a>
                <a href="<?php echo build_agent_filter_url(['specialty' => 'first-time-buyers']); ?>"
                   class="hph-px-md hph-py-xs hph-rounded-full hph-border-2 <?php echo $specialty === 'first-time-buyers' ? 'hph-bg-primary hph-text-white hph-border-primary' : 'hph-border-gray-300 hph-text-gray-700 hover:hph-border-primary'; ?>">
                    <i class="fas fa-home"></i> First-Time Buyer Expert
                </a>
                <?php if($search_query || $specialty || $language || $experience || $office): ?>
                    <a href="<?php echo get_post_type_archive_link('agent'); ?>" class="hph-px-md hph-py-xs hph-rounded-full hph-border-2 hph-border-danger hph-text-danger hover:hph-bg-danger hover:hph-text-white hph-ml-auto">
                        <i class="fas fa-times"></i> Clear All
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <!-- Controls Bar -->
    <div class="hph-bg-white hph-rounded-lg hph-shadow-sm hph-p-md hph-mb-lg">
        <div class="hph-flex hph-justify-between hph-items-center hph-flex-wrap hph-gap-md">
            
            <!-- Results Count -->
            <div class="hph-text-gray-600">
                <span class="hph-font-semibold hph-text-gray-900"><?php echo $agents_query->found_posts; ?></span> 
                agents found
            </div>
            
            <!-- View & Sort Controls -->
            <div class="hph-flex hph-items-center hph-gap-md">
                
                <!-- View Switcher -->
                <div class="hph-btn-group">
                    <a href="<?php echo build_agent_filter_url(['view' => 'grid']); ?>" 
                       class="hph-btn-sm <?php echo $view === 'grid' ? 'hph-btn-primary' : 'hph-btn-outline'; ?>">
                        <i class="fas fa-th"></i> Grid
                    </a>
                    <a href="<?php echo build_agent_filter_url(['view' => 'list']); ?>" 
                       class="hph-btn-sm <?php echo $view === 'list' ? 'hph-btn-primary' : 'hph-btn-outline'; ?>">
                        <i class="fas fa-list"></i> List
                    </a>
                </div>
                
                <!-- Sort Dropdown -->
                <select onchange="window.location.href=this.value" class="hph-px-md hph-py-sm hph-border-2 hph-border-gray-200 hph-rounded-lg">
                    <option value="<?php echo build_agent_filter_url(['sort' => 'name_asc']); ?>" <?php selected($sort, 'name_asc'); ?>>Name A-Z</option>
                    <option value="<?php echo build_agent_filter_url(['sort' => 'name_desc']); ?>" <?php selected($sort, 'name_desc'); ?>>Name Z-A</option>
                    <option value="<?php echo build_agent_filter_url(['sort' => 'experience_desc']); ?>" <?php selected($sort, 'experience_desc'); ?>>Most Experienced</option>
                    <option value="<?php echo build_agent_filter_url(['sort' => 'sales_desc']); ?>" <?php selected($sort, 'sales_desc'); ?>>Top Performers</option>
                    <option value="<?php echo build_agent_filter_url(['sort' => 'featured']); ?>" <?php selected($sort, 'featured'); ?>>Featured First</option>
                </select>
            </div>
        </div>
    </div>
    
    <!-- Search Panel (Hidden by default, shown when advanced search is triggered) -->
    <div id="search-panel" class="hph-search-panel hph-hidden">
        <div class="hph-bg-white hph-rounded-lg hph-shadow-md hph-p-lg hph-mb-lg">
            <h3 class="hph-text-lg hph-font-semibold hph-mb-md">Advanced Agent Search</h3>
            <!-- Advanced search content would go here -->
        </div>
    </div>
    
    <!-- Active Filters Display -->
    <div id="active-filters" class="hph-active-filters" style="display: none;">
        <div class="hph-bg-gray-50 hph-rounded-lg hph-p-md hph-mb-lg">
            <div class="hph-flex hph-flex-wrap hph-items-center hph-gap-sm">
                <span class="hph-font-medium hph-text-gray-700">Active Filters:</span>
                <div id="filter-tags" class="hph-flex hph-flex-wrap hph-gap-sm"></div>
            </div>
        </div>
    </div>
    
    <!-- Filter Controls -->
    <div id="filter-controls" class="hph-filter-controls hph-mb-lg">
        <!-- This can house additional filter UI elements -->
    </div>
    
    <!-- AJAX Loading Indicator -->
    <div id="ajax-loader" class="hph-ajax-loader hph-hidden">
        <div class="hph-text-center hph-py-lg">
            <div class="hph-spinner hph-inline-block"></div>
            <p class="hph-mt-sm hph-text-gray-600">Loading agents...</p>
        </div>
    </div>
    
    <!-- Results Container -->
    <div id="results-container" class="hph-results-container">
    
    <!-- Agents -->
    <?php if ($agents_query->have_posts()): ?>
        
        <!-- Grid/List Container -->
        <div class="<?php echo $view === 'list' ? 'hph-space-y-md' : 'hph-grid hph-grid-cols-1 md:hph-grid-cols-2 lg:hph-grid-cols-3 hph-gap-lg'; ?>">
            <?php while ($agents_query->have_posts()): $agents_query->the_post(); ?>
                <?php 
                // Use the appropriate agent card template based on view
                if ($view === 'list') {
                    get_template_part('template-parts/agent-card-list', null, ['agent_id' => get_the_ID()]);
                } else {
                    get_template_part('template-parts/agent-card', null, ['agent_id' => get_the_ID()]);
                }
                ?>
            <?php endwhile; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($agents_query->max_num_pages > 1): ?>
            <nav class="hph-mt-xl hph-flex hph-justify-center">
                <div class="hph-pagination">
                    <?php
                    echo paginate_links([
                        'total' => $agents_query->max_num_pages,
                        'current' => max(1, get_query_var('paged')),
                        'format' => '?paged=%#%',
                        'base' => build_agent_filter_url() . '%_%',
                        'add_args' => false,
                        'prev_text' => '<i class="fas fa-chevron-left"></i>',
                        'next_text' => '<i class="fas fa-chevron-right"></i>',
                        'type' => 'list',
                        'end_size' => 2,
                        'mid_size' => 1
                    ]);
                    ?>
                </div>
            </nav>
        <?php endif; ?>
        
    <?php else: ?>
        
        <!-- No Results -->
        <div class="hph-text-center hph-py-xl">
            <div class="hph-mb-lg">
                <i class="fas fa-users hph-text-gray-300 hph-text-6xl"></i>
            </div>
            <h3 class="hph-text-2xl hph-font-semibold hph-mb-md">No Agents Found</h3>
            <p class="hph-text-gray-600 hph-mb-lg">
                Try adjusting your search criteria or removing some filters.
            </p>
            <a href="<?php echo get_post_type_archive_link('agent'); ?>" class="hph-btn hph-btn-primary">
                View All Agents
            </a>
        </div>
        
    <?php endif; ?>
    
</div> <!-- End results-container -->
</div> <!-- End main container -->

<!-- Simple CSS for pagination styling -->
<style>
.hph-pagination ul {
    display: flex;
    gap: var(--hph-gap-sm);
    list-style: none;
    padding: 0;
}

.hph-pagination a,
.hph-pagination .current {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 0 var(--hph-padding-md);
    border: 2px solid var(--hph-gray-300);
    border-radius: var(--hph-radius-md);
    color: var(--hph-gray-700);
    text-decoration: none;
    transition: var(--hph-transition-fast);
}

.hph-pagination a:hover {
    border-color: var(--hph-primary);
    color: var(--hph-primary);
}

.hph-pagination .current {
    background: var(--hph-primary);
    color: var(--hph-white);
    border-color: var(--hph-primary);
}

.hph-btn-group {
    display: inline-flex;
    border-radius: var(--hph-radius-md);
    overflow: hidden;
}

.hph-btn-group .hph-btn-sm {
    border-radius: 0;
    margin: 0;
}

.hph-btn-group .hph-btn-sm:first-child {
    border-radius: var(--hph-radius-md) 0 0 var(--hph-radius-md);
}

.hph-btn-group .hph-btn-sm:last-child {
    border-radius: 0 var(--hph-radius-md) var(--hph-radius-md) 0;
}

/* Search and Filter Enhancements */
.hph-search-panel.hph-hidden,
.hph-ajax-loader.hph-hidden {
    display: none !important;
}

.hph-active-filters {
    transition: all 0.3s ease;
}

.hph-spinner {
    width: 24px;
    height: 24px;
    border: 3px solid var(--hph-gray-200);
    border-top: 3px solid var(--hph-primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.hph-filter-tag {
    display: inline-flex;
    align-items: center;
    gap: var(--hph-gap-xs);
    padding: var(--hph-padding-xs) var(--hph-padding-sm);
    background: var(--hph-primary);
    color: var(--hph-white);
    border-radius: var(--hph-radius-full);
    font-size: 0.875rem;
    line-height: 1.25;
}

.hph-filter-tag .remove {
    cursor: pointer;
    opacity: 0.8;
    transition: opacity 0.2s ease;
}

.hph-filter-tag .remove:hover {
    opacity: 1;
}

/* Enhanced form styling */
#filter-form select:focus,
#filter-form input:focus {
    box-shadow: 0 0 0 3px rgba(var(--hph-primary-rgb), 0.1);
}

/* Agent card hover effects */
.hph-agent-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.hph-agent-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}
</style>

<!-- Initialize JavaScript Global Variables -->
<script type="text/javascript">
window.hphArchive = {
    ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>',
    nonce: '<?php echo wp_create_nonce('hph_archive_nonce'); ?>',
    postType: 'agent',
    currentPage: <?php echo max(1, get_query_var('paged')); ?>,
    maxPages: <?php echo $agents_query->max_num_pages; ?>,
    currentFilters: {
        search: '<?php echo esc_js($search_query); ?>',
        specialty: '<?php echo esc_js($specialty); ?>',
        language: '<?php echo esc_js($language); ?>',
        office: '<?php echo esc_js($office); ?>',
        experience: '<?php echo esc_js($experience); ?>',
        view: '<?php echo esc_js($view); ?>',
        sort: '<?php echo esc_js($sort); ?>'
    },
    strings: {
        loading: '<?php echo esc_js(__('Loading...', 'happy-place-theme')); ?>',
        noResults: '<?php echo esc_js(__('No agents found', 'happy-place-theme')); ?>',
        error: '<?php echo esc_js(__('Error loading results', 'happy-place-theme')); ?>'
    }
};
</script>

<?php
wp_reset_postdata();
get_footer();
?>