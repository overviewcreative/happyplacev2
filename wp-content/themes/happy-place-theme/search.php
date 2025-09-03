<?php
/**
 * WordPress Native Search Results Template
 * 
 * Handles WordPress core search functionality using our universal search components
 * Integrates with existing search infrastructure and components from Phase 1
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

get_header();

// Get search parameters from URL
$search_query = get_search_query();
$post_type = get_query_var('post_type', 'all');
$view_mode = sanitize_text_field($_GET['view'] ?? 'grid');
$sort = sanitize_text_field($_GET['sort'] ?? 'relevance');

// If no search query, redirect to advanced search
if (empty($search_query)) {
    wp_redirect(home_url('/advanced-search/'));
    exit;
}

// Get the main query results
global $wp_query;
$search_results = $wp_query;

// Prepare page title and description
$page_title = sprintf(__('Search Results for "%s"', 'happy-place-theme'), esc_html($search_query));
$page_description = '';

if ($search_results->found_posts > 0) {
    $page_description = sprintf(
        _n('Found %d result', 'Found %d results', $search_results->found_posts, 'happy-place-theme'),
        $search_results->found_posts
    );
} else {
    $page_description = __('No results found. Try adjusting your search terms or use the advanced search.', 'happy-place-theme');
}

// Group results by post type for mixed display
$grouped_results = [];
$post_types_found = [];

if ($search_results->found_posts > 0) {
    foreach ($search_results->posts as $post) {
        $current_post_type = get_post_type($post);
        if (!isset($grouped_results[$current_post_type])) {
            $grouped_results[$current_post_type] = [];
        }
        $grouped_results[$current_post_type][] = $post;
        $post_types_found[] = $current_post_type;
    }
    $post_types_found = array_unique($post_types_found);
}

$is_mixed_search = count($post_types_found) > 1;
?>

<div class="hero hero-secondary search-hero">
    <div class="container">
        <div class="hero-content">
            <h1 class="page-title"><?php echo esc_html($page_title); ?></h1>
            <p class="page-subtitle"><?php echo esc_html($page_description); ?></p>
        </div>
    </div>
</div>

<main class="hph-main-content search-results-page" role="main">
    <div class="hph-container">
        
        <!-- Enhanced Search Form -->
        <section class="search-form-section">
            <?php 
            hph_component('search-form', [
                'form_id' => 'wordpress-search',
                'placeholder' => __('Refine your search...', 'happy-place-theme'),
                'show_filters' => true,
                'show_post_type_selector' => true,
                'current_query' => $search_query,
                'current_post_type' => $post_type,
                'advanced_mode' => true,
                'form_action' => home_url('/'),
                'form_method' => 'GET'
            ]);
            ?>
        </section>

        <!-- Search Results Section -->
        <section class="search-results-section">
            <?php if ($search_results->found_posts > 0): ?>
                
                <!-- Results Controls -->
                <div class="search-results-controls">
                    <div class="results-summary">
                        <span class="results-count"><?php echo esc_html($page_description); ?></span>
                        <?php if (count($post_types_found) === 1): ?>
                            <span class="results-type">
                                <?php 
                                $type_labels = [
                                    'listing' => __('Properties', 'happy-place-theme'),
                                    'agent' => __('Agents', 'happy-place-theme'), 
                                    'city' => __('Cities', 'happy-place-theme'),
                                    'community' => __('Communities', 'happy-place-theme'),
                                    'post' => __('Blog Posts', 'happy-place-theme')
                                ];
                                $single_type = $post_types_found[0];
                                echo 'in ' . esc_html($type_labels[$single_type] ?? ucfirst($single_type));
                                ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- View Mode and Sort Controls -->
                    <div class="results-display-controls">
                        <!-- View Mode Switcher -->
                        <div class="view-mode-switcher">
                            <button 
                                type="button" 
                                class="view-mode-btn <?php echo $view_mode === 'grid' ? 'active' : ''; ?>"
                                data-view="grid"
                                title="<?php _e('Grid View', 'happy-place-theme'); ?>"
                            >
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                            </button>
                            
                            <button 
                                type="button" 
                                class="view-mode-btn <?php echo $view_mode === 'list' ? 'active' : ''; ?>"
                                data-view="list"
                                title="<?php _e('List View', 'happy-place-theme'); ?>"
                            >
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>

                        <!-- Sort Options -->
                        <div class="sort-controls">
                            <label for="search-sort-select" class="screen-reader-text">
                                <?php _e('Sort results by', 'happy-place-theme'); ?>
                            </label>
                            <select id="search-sort-select" class="sort-select">
                                <option value="relevance" <?php selected($sort, 'relevance'); ?>>
                                    <?php _e('Most Relevant', 'happy-place-theme'); ?>
                                </option>
                                <option value="date" <?php selected($sort, 'date'); ?>>
                                    <?php _e('Newest First', 'happy-place-theme'); ?>
                                </option>
                                <option value="title" <?php selected($sort, 'title'); ?>>
                                    <?php _e('Title A-Z', 'happy-place-theme'); ?>
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Results Display -->
                <div class="search-results-display">
                    <?php if ($is_mixed_search): ?>
                        <!-- Mixed Post Type Results -->
                        <?php foreach (['listing', 'agent', 'city', 'community', 'post'] as $type): ?>
                            <?php if (!empty($grouped_results[$type])): ?>
                                <div class="result-section" data-post-type="<?php echo esc_attr($type); ?>">
                                    <div class="section-header">
                                        <h3 class="section-title">
                                            <?php 
                                            $type_labels = [
                                                'listing' => __('Properties', 'happy-place-theme'),
                                                'agent' => __('Agents', 'happy-place-theme'),
                                                'city' => __('Cities', 'happy-place-theme'), 
                                                'community' => __('Communities', 'happy-place-theme'),
                                                'post' => __('Blog Posts', 'happy-place-theme')
                                            ];
                                            echo esc_html($type_labels[$type] ?? ucfirst($type));
                                            ?>
                                            <span class="section-count">(<?php echo count($grouped_results[$type]); ?>)</span>
                                        </h3>
                                        
                                        <?php if (count($grouped_results[$type]) >= 3): ?>
                                            <a href="<?php echo esc_url(add_query_arg(['post_type' => $type], get_search_link($search_query))); ?>" 
                                               class="view-all-link">
                                                <?php _e('View all', 'happy-place-theme'); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="results-grid <?php echo esc_attr($view_mode); ?>-view">
                                        <?php foreach ($grouped_results[$type] as $post): ?>
                                            <?php 
                                            setup_postdata($post);
                                            
                                            // Load appropriate card component using unified system
                                            $component_name = $type . '-card';
                                            if (array_key_exists($component_name, HPH_Component_Loader::get_components())) {
                                                hph_component($component_name, [
                                                    'post' => $post,
                                                    'view_mode' => $view_mode,
                                                    'show_type_badge' => true,
                                                    'search_query' => $search_query
                                                ]); 
                                            } else {
                                                // Fallback to generic card display
                                                ?>
                                                <article class="search-result-card <?php echo esc_attr($type); ?>-card">
                                                    <div class="card-header">
                                                        <span class="post-type-badge"><?php echo esc_html($type_labels[$type] ?? ucfirst($type)); ?></span>
                                                    </div>
                                                    <div class="card-content">
                                                        <h4 class="card-title">
                                                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                                        </h4>
                                                        <?php if (has_excerpt()): ?>
                                                            <p class="card-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                </article>
                                                <?php
                                            }
                                            ?>
                                        <?php endforeach; ?>
                                        <?php wp_reset_postdata(); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        
                    <?php else: ?>
                        <!-- Single Post Type Results -->
                        <div class="results-grid <?php echo esc_attr($view_mode); ?>-view single-type-results">
                            <?php 
                            while (have_posts()): 
                                the_post();
                                $current_post_type = get_post_type();
                                
                                // Load appropriate card component using unified system
                                $component_name = $current_post_type . '-card';
                                if (array_key_exists($component_name, HPH_Component_Loader::get_components())) {
                                    hph_component($component_name, [
                                        'post' => get_post(),
                                        'view_mode' => $view_mode,
                                        'show_type_badge' => false,
                                        'search_query' => $search_query
                                    ]); 
                                } else {
                                    // Fallback to generic search result display
                                    ?>
                                    <article id="post-<?php the_ID(); ?>" <?php post_class('search-result-card fallback-card'); ?>>
                                        <?php if (has_post_thumbnail()): ?>
                                            <div class="card-image">
                                                <a href="<?php the_permalink(); ?>">
                                                    <?php the_post_thumbnail('medium'); ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="card-content">
                                            <div class="card-meta">
                                                <span class="post-type"><?php echo esc_html(get_post_type_object($current_post_type)->labels->singular_name); ?></span>
                                                <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                                    <?php echo esc_html(get_the_date()); ?>
                                                </time>
                                            </div>
                                            
                                            <h3 class="card-title">
                                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                            </h3>
                                            
                                            <?php if (has_excerpt() || get_the_content()): ?>
                                                <div class="card-excerpt">
                                                    <?php
                                                    $excerpt = get_the_excerpt();
                                                    // Highlight search terms
                                                    if ($search_query && $excerpt) {
                                                        $highlighted = preg_replace(
                                                            '/(' . preg_quote($search_query, '/') . ')/i',
                                                            '<mark>$1</mark>',
                                                            $excerpt
                                                        );
                                                        echo wp_kses_post($highlighted);
                                                    } else {
                                                        echo wp_kses_post($excerpt);
                                                    }
                                                    ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="card-actions">
                                                <a href="<?php the_permalink(); ?>" class="read-more-link">
                                                    <?php _e('View Details', 'happy-place-theme'); ?>
                                                </a>
                                            </div>
                                        </div>
                                    </article>
                                    <?php
                                }
                            endwhile; 
                            ?>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($search_results->max_num_pages > 1): ?>
                            <div class="search-pagination">
                                <?php
                                hph_component('numbered-pagination', [
                                    'current_page' => get_query_var('paged', 1),
                                    'max_pages' => $search_results->max_num_pages,
                                    'total_results' => $search_results->found_posts,
                                    'per_page' => get_query_var('posts_per_page'),
                                    'per_page_options' => [10, 20, 50],
                                    'style' => 'numbered',
                                    'ajax_enabled' => false, // WordPress native search doesn't need AJAX
                                    'base_url' => get_search_link($search_query)
                                ]);
                                ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <!-- No Results -->
                <div class="search-no-results">
                    <div class="no-results-content">
                        <div class="no-results-icon">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                            </svg>
                        </div>
                        
                        <h2><?php _e('No results found', 'happy-place-theme'); ?></h2>
                        
                        <p>
                            <?php printf(
                                __('We couldn\'t find anything for "%s". Try different keywords or browse our featured content below.', 'happy-place-theme'),
                                esc_html($search_query)
                            ); ?>
                        </p>
                        
                        <div class="no-results-actions">
                            <a href="<?php echo esc_url(home_url('/advanced-search/')); ?>" class="hph-button hph-button-primary">
                                <?php _e('Try Advanced Search', 'happy-place-theme'); ?>
                            </a>
                            <a href="<?php echo esc_url(get_post_type_archive_link('listing')); ?>" class="hph-button hph-button-outline">
                                <?php _e('Browse Properties', 'happy-place-theme'); ?>
                            </a>
                        </div>
                        
                        <!-- Search Tips -->
                        <div class="search-tips">
                            <h3><?php _e('Search Tips:', 'happy-place-theme'); ?></h3>
                            <ul>
                                <li><?php _e('Check your spelling and try again', 'happy-place-theme'); ?></li>
                                <li><?php _e('Try using fewer or different keywords', 'happy-place-theme'); ?></li>
                                <li><?php _e('Try more general terms', 'happy-place-theme'); ?></li>
                                <li><?php _e('Use the advanced search for more options', 'happy-place-theme'); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php get_footer(); ?>
