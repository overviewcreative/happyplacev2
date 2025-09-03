<?php
/**
 * Search Results Display Component
 * 
 * Handles display of search results across all post types
 * Supports grid, list, and map view modes with adaptive layouts
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

$config = $args ?? [];
$results = $config['results'] ?? [];
$post_type = $config['post_type'] ?? 'all';
$view_mode = $config['view_mode'] ?? 'grid';
$total_results = $config['total_results'] ?? 0;
$current_page = $config['current_page'] ?? 1;
$max_pages = $config['max_pages'] ?? 1;
$search_query = $config['search_query'] ?? '';
$container_id = $config['container_id'] ?? 'search-results';

// Determine if this is a mixed post type search
$is_mixed_search = $post_type === 'all' && is_array($results) && isset($results['mixed']);
?>

<div 
    class="hph-search-results" 
    id="<?php echo esc_attr($container_id); ?>"
    data-post-type="<?php echo esc_attr($post_type); ?>"
    data-view-mode="<?php echo esc_attr($view_mode); ?>"
    data-total="<?php echo esc_attr($total_results); ?>"
>
    <!-- Results Header -->
    <div class="results-header">
        <div class="results-count">
            <?php if ($total_results > 0): ?>
                <h2 class="results-title">
                    <?php if (!empty($search_query)): ?>
                        <?php printf(
                            _n('%d result for "%s"', '%d results for "%s"', $total_results, 'happy-place-theme'),
                            $total_results,
                            esc_html($search_query)
                        ); ?>
                    <?php else: ?>
                        <?php printf(
                            _n('%d result found', '%d results found', $total_results, 'happy-place-theme'),
                            $total_results
                        ); ?>
                    <?php endif; ?>
                </h2>
            <?php else: ?>
                <h2 class="results-title">
                    <?php _e('No results found', 'happy-place-theme'); ?>
                </h2>
            <?php endif; ?>
        </div>

        <?php if ($total_results > 0): ?>
            <div class="results-controls">
                <!-- View Mode Switcher -->
                <div class="view-mode-switcher" role="radiogroup" aria-label="<?php _e('View mode', 'happy-place-theme'); ?>">
                    <button 
                        type="button" 
                        class="view-mode-btn <?php echo $view_mode === 'grid' ? 'active' : ''; ?>"
                        data-view="grid"
                        aria-pressed="<?php echo $view_mode === 'grid' ? 'true' : 'false'; ?>"
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
                        aria-pressed="<?php echo $view_mode === 'list' ? 'true' : 'false'; ?>"
                        title="<?php _e('List View', 'happy-place-theme'); ?>"
                    >
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    
                    <?php if ($post_type !== 'agent'): ?>
                        <button 
                            type="button" 
                            class="view-mode-btn <?php echo $view_mode === 'map' ? 'active' : ''; ?>"
                            data-view="map"
                            aria-pressed="<?php echo $view_mode === 'map' ? 'true' : 'false'; ?>"
                            title="<?php _e('Map View', 'happy-place-theme'); ?>"
                        >
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12 1.586l-4 4v12.828l4-4V1.586zM3.707 3.293A1 1 0 002 4v10a1 1 0 00.293.707L6 18.414V5.586L3.707 3.293zM17.707 5.293L14 1.586v12.828l2.293 2.293A1 1 0 0018 16V6a1 1 0 00-.293-.707z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Sort Options -->
                <div class="sort-controls">
                    <label for="sort-select" class="screen-reader-text"><?php _e('Sort by', 'happy-place-theme'); ?></label>
                    <select id="sort-select" class="sort-select">
                        <?php if ($post_type === 'listing' || $post_type === 'all'): ?>
                            <option value="date_desc" <?php selected($_GET['sort'] ?? '', 'date_desc'); ?>><?php _e('Newest First', 'happy-place-theme'); ?></option>
                            <option value="price_asc" <?php selected($_GET['sort'] ?? '', 'price_asc'); ?>><?php _e('Price: Low to High', 'happy-place-theme'); ?></option>
                            <option value="price_desc" <?php selected($_GET['sort'] ?? '', 'price_desc'); ?>><?php _e('Price: High to Low', 'happy-place-theme'); ?></option>
                        <?php endif; ?>
                        
                        <?php if ($post_type === 'agent' || $post_type === 'all'): ?>
                            <option value="name_asc" <?php selected($_GET['sort'] ?? '', 'name_asc'); ?>><?php _e('Name A-Z', 'happy-place-theme'); ?></option>
                            <option value="experience_desc" <?php selected($_GET['sort'] ?? '', 'experience_desc'); ?>><?php _e('Most Experienced', 'happy-place-theme'); ?></option>
                        <?php endif; ?>
                        
                        <option value="relevance" <?php selected($_GET['sort'] ?? '', 'relevance'); ?>><?php _e('Most Relevant', 'happy-place-theme'); ?></option>
                    </select>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Results Content -->
    <div class="results-content">
        <?php if ($total_results > 0): ?>
            
            <?php if ($is_mixed_search): ?>
                <!-- Mixed Post Type Results -->
                <?php foreach ($results['mixed'] as $type => $posts): ?>
                    <?php if (!empty($posts)): ?>
                        <div class="result-section" data-post-type="<?php echo esc_attr($type); ?>">
                            <h3 class="section-title">
                                <?php 
                                $section_titles = [
                                    'listing' => __('Properties', 'happy-place-theme'),
                                    'agent' => __('Agents', 'happy-place-theme'),
                                    'city' => __('Cities', 'happy-place-theme'),
                                    'community' => __('Communities', 'happy-place-theme')
                                ];
                                echo esc_html($section_titles[$type] ?? ucfirst($type));
                                ?>
                                <span class="section-count">(<?php echo count($posts); ?>)</span>
                            </h3>
                            
                            <div class="results-grid <?php echo esc_attr($view_mode); ?>-view" data-post-type="<?php echo esc_attr($type); ?>">
                                <?php foreach ($posts as $post): ?>
                                    <?php 
                                    // Load appropriate card component
                                    hph_component($type . '-card', [
                                        'post' => $post,
                                        'view_mode' => $view_mode,
                                        'show_type_badge' => true
                                    ]); 
                                    ?>
                                <?php endforeach; ?>
                            </div>

                            <?php if (count($posts) >= 3): ?>
                                <div class="section-footer">
                                    <a href="<?php echo esc_url(add_query_arg(['type' => $type], get_permalink())); ?>" class="view-all-link">
                                        <?php printf(__('View all %s results', 'happy-place-theme'), $section_titles[$type] ?? $type); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                
            <?php else: ?>
                <!-- Single Post Type Results -->
                <div class="results-grid <?php echo esc_attr($view_mode); ?>-view" data-post-type="<?php echo esc_attr($post_type); ?>">
                    <?php foreach ($results as $post): ?>
                        <?php 
                        // Determine card component path
                        $card_component = 'template-parts/components/' . get_post_type($post) . '/card';
                        
                        hph_component(get_post_type($post) . '-card', [
                            'post' => $post,
                            'view_mode' => $view_mode,
                            'show_type_badge' => false
                        ]); 
                        ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Map View Container (shown when map view is active) -->
            <?php if ($view_mode === 'map' && $post_type !== 'agent'): ?>
                <div class="map-container" id="search-results-map" style="display: none;">
                    <!-- Map will be initialized by JavaScript -->
                    <div class="map-placeholder">
                        <p><?php _e('Loading map...', 'happy-place-theme'); ?></p>
                    </div>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- No Results -->
            <div class="no-results">
                <div class="no-results-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                    </svg>
                </div>
                <h3><?php _e('No results found', 'happy-place-theme'); ?></h3>
                <p><?php _e('Try adjusting your search criteria or explore our featured listings.', 'happy-place-theme'); ?></p>
                
                <div class="no-results-actions">
                    <button type="button" class="modify-search-btn">
                        <?php _e('Modify Search', 'happy-place-theme'); ?>
                    </button>
                    <a href="<?php echo esc_url(home_url('/listings/')); ?>" class="browse-all-btn">
                        <?php _e('Browse All Properties', 'happy-place-theme'); ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Loading Overlay for AJAX -->
    <div class="results-loading-overlay" style="display: none;">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p><?php _e('Loading results...', 'happy-place-theme'); ?></p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const resultsContainer = document.getElementById('<?php echo esc_js($container_id); ?>');
    const viewModeButtons = resultsContainer.querySelectorAll('.view-mode-btn');
    const sortSelect = resultsContainer.querySelector('.sort-select');
    const modifySearchBtn = resultsContainer.querySelector('.modify-search-btn');
    
    // View mode switching
    viewModeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const newView = this.dataset.view;
            
            // Update active state
            viewModeButtons.forEach(b => {
                b.classList.remove('active');
                b.setAttribute('aria-pressed', 'false');
            });
            this.classList.add('active');
            this.setAttribute('aria-pressed', 'true');
            
            // Update grid classes
            const grids = resultsContainer.querySelectorAll('.results-grid');
            grids.forEach(grid => {
                grid.className = grid.className.replace(/\w+-view/g, newView + '-view');
            });
            
            // Handle map view
            const mapContainer = resultsContainer.querySelector('.map-container');
            if (mapContainer) {
                mapContainer.style.display = newView === 'map' ? 'block' : 'none';
            }
            
            // Update URL parameter
            updateUrlParam('view', newView);
        });
    });
    
    // Sort change handling
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            updateUrlParam('sort', this.value);
            // In a full implementation, this would trigger AJAX reload
            location.reload();
        });
    }
    
    // Modify search functionality
    if (modifySearchBtn) {
        modifySearchBtn.addEventListener('click', function() {
            const searchForm = document.querySelector('.hph-search-form');
            if (searchForm) {
                searchForm.scrollIntoView({ behavior: 'smooth' });
                const searchInput = searchForm.querySelector('.search-input');
                if (searchInput) {
                    searchInput.focus();
                }
            }
        });
    }
    
    // Utility function to update URL parameters
    function updateUrlParam(param, value) {
        const url = new URL(window.location);
        url.searchParams.set(param, value);
        window.history.replaceState({}, '', url);
    }
});
</script>