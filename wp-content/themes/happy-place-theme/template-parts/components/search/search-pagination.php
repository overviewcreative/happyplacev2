<?php
/**
 * Search Results Pagination Component
 * 
 * Handles pagination for search results with AJAX support
 * Supports multiple pagination styles and per-page options
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

$config = $args ?? [];
$current_page = max(1, intval($config['current_page'] ?? 1));
$max_pages = max(1, intval($config['max_pages'] ?? 1));
$total_results = intval($config['total_results'] ?? 0);
$per_page = intval($config['per_page'] ?? 12);
$per_page_options = $config['per_page_options'] ?? [12, 24, 48];
$style = $config['style'] ?? 'numbered'; // numbered, simple, infinite
$ajax_enabled = $config['ajax_enabled'] ?? true;
$base_url = $config['base_url'] ?? get_permalink();

// Don't show pagination if there's only one page
if ($max_pages <= 1 && $style !== 'infinite') {
    return;
}

// Calculate pagination numbers
$pagination_range = 5; // Show 5 page numbers around current page
$start_page = max(1, $current_page - floor($pagination_range / 2));
$end_page = min($max_pages, $start_page + $pagination_range - 1);

// Adjust start page if we're near the end
if ($end_page - $start_page + 1 < $pagination_range) {
    $start_page = max(1, $end_page - $pagination_range + 1);
}

// Calculate result range for display
$start_result = (($current_page - 1) * $per_page) + 1;
$end_result = min($current_page * $per_page, $total_results);
?>

<div class="hph-search-pagination" data-style="<?php echo esc_attr($style); ?>">
    
    <!-- Results Summary -->
    <div class="pagination-summary">
        <span class="results-range">
            <?php printf(
                __('Showing %s-%s of %s results', 'happy-place-theme'),
                number_format_i18n($start_result),
                number_format_i18n($end_result),
                number_format_i18n($total_results)
            ); ?>
        </span>
        
        <?php if (!empty($per_page_options) && count($per_page_options) > 1): ?>
            <div class="per-page-selector">
                <label for="per-page-select" class="per-page-label">
                    <?php _e('Show:', 'happy-place-theme'); ?>
                </label>
                <select id="per-page-select" class="per-page-select">
                    <?php foreach ($per_page_options as $option): ?>
                        <option value="<?php echo esc_attr($option); ?>" <?php selected($per_page, $option); ?>>
                            <?php echo esc_html($option); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="per-page-suffix"><?php _e('per page', 'happy-place-theme'); ?></span>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($style === 'numbered' && $max_pages > 1): ?>
        <!-- Numbered Pagination -->
        <nav class="pagination-nav" role="navigation" aria-label="<?php _e('Search results pagination', 'happy-place-theme'); ?>">
            <ul class="pagination-list">
                
                <!-- Previous Page -->
                <?php if ($current_page > 1): ?>
                    <li class="pagination-item prev-page">
                        <a 
                            href="<?php echo esc_url(add_query_arg('paged', $current_page - 1, $base_url)); ?>" 
                            class="pagination-link"
                            data-page="<?php echo esc_attr($current_page - 1); ?>"
                            aria-label="<?php _e('Previous page', 'happy-place-theme'); ?>"
                        >
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                            </svg>
                            <span><?php _e('Previous', 'happy-place-theme'); ?></span>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- First Page -->
                <?php if ($start_page > 1): ?>
                    <li class="pagination-item">
                        <a 
                            href="<?php echo esc_url(remove_query_arg('paged', $base_url)); ?>" 
                            class="pagination-link"
                            data-page="1"
                        >1</a>
                    </li>
                    <?php if ($start_page > 2): ?>
                        <li class="pagination-item ellipsis">
                            <span class="pagination-ellipsis">…</span>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Page Numbers -->
                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <li class="pagination-item <?php echo $i === $current_page ? 'current' : ''; ?>">
                        <?php if ($i === $current_page): ?>
                            <span class="pagination-current" aria-current="page"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a 
                                href="<?php echo esc_url($i === 1 ? remove_query_arg('paged', $base_url) : add_query_arg('paged', $i, $base_url)); ?>" 
                                class="pagination-link"
                                data-page="<?php echo esc_attr($i); ?>"
                            ><?php echo $i; ?></a>
                        <?php endif; ?>
                    </li>
                <?php endfor; ?>

                <!-- Last Page -->
                <?php if ($end_page < $max_pages): ?>
                    <?php if ($end_page < $max_pages - 1): ?>
                        <li class="pagination-item ellipsis">
                            <span class="pagination-ellipsis">…</span>
                        </li>
                    <?php endif; ?>
                    <li class="pagination-item">
                        <a 
                            href="<?php echo esc_url(add_query_arg('paged', $max_pages, $base_url)); ?>" 
                            class="pagination-link"
                            data-page="<?php echo esc_attr($max_pages); ?>"
                        ><?php echo $max_pages; ?></a>
                    </li>
                <?php endif; ?>

                <!-- Next Page -->
                <?php if ($current_page < $max_pages): ?>
                    <li class="pagination-item next-page">
                        <a 
                            href="<?php echo esc_url(add_query_arg('paged', $current_page + 1, $base_url)); ?>" 
                            class="pagination-link"
                            data-page="<?php echo esc_attr($current_page + 1); ?>"
                            aria-label="<?php _e('Next page', 'happy-place-theme'); ?>"
                        >
                            <span><?php _e('Next', 'happy-place-theme'); ?></span>
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                            </svg>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>

    <?php elseif ($style === 'simple' && $max_pages > 1): ?>
        <!-- Simple Previous/Next Pagination -->
        <nav class="pagination-nav simple" role="navigation" aria-label="<?php _e('Search results pagination', 'happy-place-theme'); ?>">
            <div class="pagination-simple">
                <?php if ($current_page > 1): ?>
                    <a 
                        href="<?php echo esc_url(add_query_arg('paged', $current_page - 1, $base_url)); ?>" 
                        class="pagination-prev"
                        data-page="<?php echo esc_attr($current_page - 1); ?>"
                    >
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                            <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                        </svg>
                        <?php _e('Previous', 'happy-place-theme'); ?>
                    </a>
                <?php endif; ?>

                <span class="pagination-info">
                    <?php printf(__('Page %d of %d', 'happy-place-theme'), $current_page, $max_pages); ?>
                </span>

                <?php if ($current_page < $max_pages): ?>
                    <a 
                        href="<?php echo esc_url(add_query_arg('paged', $current_page + 1, $base_url)); ?>" 
                        class="pagination-next"
                        data-page="<?php echo esc_attr($current_page + 1); ?>"
                    >
                        <?php _e('Next', 'happy-place-theme'); ?>
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                        </svg>
                    </a>
                <?php endif; ?>
            </div>
        </nav>

    <?php elseif ($style === 'infinite'): ?>
        <!-- Infinite Scroll / Load More -->
        <div class="pagination-infinite">
            <?php if ($current_page < $max_pages): ?>
                <button 
                    type="button" 
                    class="load-more-btn"
                    data-page="<?php echo esc_attr($current_page + 1); ?>"
                    data-max-pages="<?php echo esc_attr($max_pages); ?>"
                >
                    <span class="load-more-text"><?php _e('Load More Results', 'happy-place-theme'); ?></span>
                    <span class="load-more-spinner" style="display: none;">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                            <path d="M8 2a6 6 0 1 0 6 6V2a6 6 0 0 0-6 6z"/>
                        </svg>
                    </span>
                </button>
                
                <div class="infinite-scroll-end" style="display: none;">
                    <p><?php _e('You\'ve reached the end of the results.', 'happy-place-theme'); ?></p>
                </div>
            <?php else: ?>
                <div class="infinite-scroll-end">
                    <p><?php _e('You\'ve reached the end of the results.', 'happy-place-theme'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Loading indicator for AJAX -->
    <div class="pagination-loading" style="display: none;">
        <div class="loading-spinner">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2a10 10 0 1 0 10 10V2a10 10 0 0 0-10 10z"/>
            </svg>
        </div>
        <span><?php _e('Loading...', 'happy-place-theme'); ?></span>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paginationContainer = document.querySelector('.hph-search-pagination');
    const perPageSelect = paginationContainer.querySelector('.per-page-select');
    const paginationLinks = paginationContainer.querySelectorAll('.pagination-link');
    const loadMoreBtn = paginationContainer.querySelector('.load-more-btn');
    
    // Per-page selector change
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            const url = new URL(window.location);
            url.searchParams.set('per_page', this.value);
            url.searchParams.delete('paged'); // Reset to first page
            window.location.href = url.toString();
        });
    }
    
    // AJAX pagination (if enabled)
    <?php if ($ajax_enabled): ?>
    if (paginationLinks.length > 0) {
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                const page = this.dataset.page;
                const loadingIndicator = paginationContainer.querySelector('.pagination-loading');
                
                // Show loading
                if (loadingIndicator) {
                    loadingIndicator.style.display = 'flex';
                }
                
                // Update URL
                const url = new URL(window.location);
                if (page === '1') {
                    url.searchParams.delete('paged');
                } else {
                    url.searchParams.set('paged', page);
                }
                window.history.pushState({}, '', url);
                
                // TODO: Implement AJAX request to load new page
                // For now, just reload the page
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            });
        });
    }
    
    // Load more functionality for infinite scroll
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            const nextPage = parseInt(this.dataset.page);
            const maxPages = parseInt(this.dataset.maxPages);
            const spinner = this.querySelector('.load-more-spinner');
            const text = this.querySelector('.load-more-text');
            
            // Show loading state
            spinner.style.display = 'inline-block';
            text.textContent = '<?php echo esc_js(__('Loading...', 'happy-place-theme')); ?>';
            this.disabled = true;
            
            // TODO: Implement AJAX request to load more results
            // This would append new results to the existing grid
            
            // For now, simulate loading
            setTimeout(() => {
                if (nextPage >= maxPages) {
                    this.style.display = 'none';
                    paginationContainer.querySelector('.infinite-scroll-end').style.display = 'block';
                } else {
                    this.dataset.page = nextPage + 1;
                    spinner.style.display = 'none';
                    text.textContent = '<?php echo esc_js(__('Load More Results', 'happy-place-theme')); ?>';
                    this.disabled = false;
                }
            }, 1000);
        });
    }
    <?php endif; ?>
});
</script>