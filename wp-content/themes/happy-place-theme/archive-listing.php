<?php
/**
 * The template for displaying listing archives
 *
 * @package HappyPlaceTheme
 */

get_header(); ?>

<section class="section section-primary">
    <div class="section-container">
        <div class="article-header" style="text-align: center; max-width: 800px; margin: 0 auto;">
            <span class="article-category" style="color: var(--hph-primary-200);"><?php esc_html_e('Real Estate', 'happy-place-theme'); ?></span>
            <h1 class="hero-title" style="color: var(--hph-white);"><?php esc_html_e('Property Listings', 'happy-place-theme'); ?></h1>
            <p class="hero-subtitle" style="color: var(--hph-primary-100);">
                <?php esc_html_e('Explore our featured properties and find your perfect home', 'happy-place-theme'); ?>
            </p>
        </div>
    </div>
</section>

<main id="primary" class="site-main">
    <div class="section-container" style="padding: var(--hph-space-4xl) 0;">
        
        <!-- Listing Filters -->
        <div class="listing-filters" style="margin-bottom: var(--hph-space-3xl);">
            <div class="card">
                <div class="card-body">
                    <div class="content-grid content-grid-4" style="gap: var(--hph-space-lg);">
                        <div class="filter-group">
                            <select class="form-select" id="property-type-filter">
                                <option value=""><?php esc_html_e('All Property Types', 'happy-place-theme'); ?></option>
                                <!-- Options will be populated via AJAX -->
                            </select>
                        </div>
                        <div class="filter-group">
                            <select class="form-select" id="price-range-filter">
                                <option value=""><?php esc_html_e('Any Price', 'happy-place-theme'); ?></option>
                                <option value="0-200000"><?php esc_html_e('Under $200K', 'happy-place-theme'); ?></option>
                                <option value="200000-500000"><?php esc_html_e('$200K - $500K', 'happy-place-theme'); ?></option>
                                <option value="500000-1000000"><?php esc_html_e('$500K - $1M', 'happy-place-theme'); ?></option>
                                <option value="1000000+"><?php esc_html_e('Over $1M', 'happy-place-theme'); ?></option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <select class="form-select" id="bedrooms-filter">
                                <option value=""><?php esc_html_e('Any Bedrooms', 'happy-place-theme'); ?></option>
                                <option value="1"><?php esc_html_e('1+ Bedroom', 'happy-place-theme'); ?></option>
                                <option value="2"><?php esc_html_e('2+ Bedrooms', 'happy-place-theme'); ?></option>
                                <option value="3"><?php esc_html_e('3+ Bedrooms', 'happy-place-theme'); ?></option>
                                <option value="4"><?php esc_html_e('4+ Bedrooms', 'happy-place-theme'); ?></option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <button type="button" class="btn btn-primary btn-block" id="apply-filters">
                                <i class="fas fa-search" style="margin-right: var(--hph-space-sm);"></i>
                                <?php esc_html_e('Search Properties', 'happy-place-theme'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="content-area">
            
            <?php if (have_posts()) : ?>
                
                <div class="archive-meta" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--hph-space-xl);">
                    <div class="results-count" style="color: var(--hph-gray-600); font-weight: 500;">
                        <?php
                        global $wp_query;
                        printf(
                            esc_html(_n('Showing %d property', 'Showing %d properties', $wp_query->found_posts, 'happy-place-theme')),
                            $wp_query->found_posts
                        );
                        ?>
                    </div>
                    <div class="view-toggle">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary is-active" data-view="grid">
                                <i class="fas fa-th-large"></i>
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-view="list">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="listings-container" id="listings-grid">
                    <div class="listings-grid content-grid content-grid-3" style="gap: var(--hph-space-2xl);">
                        
                        <?php while (have_posts()) : the_post(); ?>
                            
                            <div class="listing-item">
                                <?php get_template_part('template-parts/listing-card'); ?>
                            </div>
                            
                        <?php endwhile; ?>
                        
                    </div>
                </div>
                
                <!-- Pagination -->
                <div class="pagination-wrapper" style="margin-top: var(--hph-space-3xl);">
                    <?php
                    the_posts_pagination(array(
                        'mid_size'  => 2,
                        'prev_text' => '<i class="fas fa-chevron-left"></i> ' . __('Previous', 'happy-place-theme'),
                        'next_text' => __('Next', 'happy-place-theme') . ' <i class="fas fa-chevron-right"></i>',
                        'class' => 'pagination-modern'
                    ));
                    ?>
                </div>
                
            <?php else : ?>
                
                <div class="no-results" style="text-align: center; padding: var(--hph-space-6xl) var(--hph-space-xl);">
                    <div class="no-results-icon" style="font-size: 4rem; color: var(--hph-gray-300); margin-bottom: var(--hph-space-xl);">
                        <i class="fas fa-home"></i>
                    </div>
                    <h2 class="no-results-title" style="font-size: var(--hph-text-2xl); font-weight: 700; color: var(--hph-primary-700); margin-bottom: var(--hph-space-lg);">
                        <?php esc_html_e('No Properties Found', 'happy-place-theme'); ?>
                    </h2>
                    <p class="no-results-message" style="color: var(--hph-gray-600); margin-bottom: var(--hph-space-2xl); max-width: 500px; margin-left: auto; margin-right: auto;">
                        <?php esc_html_e('We don\'t have any properties matching your criteria right now. Try adjusting your search filters or check back later.', 'happy-place-theme'); ?>
                    </p>
                    
                    <div class="no-results-actions" style="display: flex; justify-content: center; gap: var(--hph-space-lg); flex-wrap: wrap;">
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-primary">
                            <i class="fas fa-home" style="margin-right: var(--hph-space-sm);"></i>
                            <?php esc_html_e('Back to Home', 'happy-place-theme'); ?>
                        </a>
                        
                        <?php if (post_type_exists('agent')) : ?>
                            <a href="<?php echo esc_url(get_post_type_archive_link('agent')); ?>" class="btn btn-outline-primary">
                                <i class="fas fa-user-tie" style="margin-right: var(--hph-space-sm);"></i>
                                <?php esc_html_e('Contact Our Agents', 'happy-place-theme'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
            <?php endif; ?>
            
        </div>
        
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // View toggle functionality
    const viewToggle = document.querySelectorAll('[data-view]');
    const listingsContainer = document.getElementById('listings-grid');
    
    viewToggle.forEach(button => {
        button.addEventListener('click', function() {
            const view = this.dataset.view;
            
            // Update active button
            viewToggle.forEach(btn => btn.classList.remove('is-active'));
            this.classList.add('is-active');
            
            // Update grid layout
            const gridContainer = listingsContainer.querySelector('.listings-grid');
            if (view === 'list') {
                gridContainer.className = 'listings-list';
                gridContainer.style.cssText = 'display: flex; flex-direction: column; gap: var(--hph-space-xl);';
            } else {
                gridContainer.className = 'listings-grid content-grid content-grid-3';
                gridContainer.style.cssText = 'gap: var(--hph-space-2xl);';
            }
        });
    });
    
    // Filter functionality (basic implementation)
    const applyFiltersBtn = document.getElementById('apply-filters');
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', function() {
            // Get filter values
            const propertyType = document.getElementById('property-type-filter').value;
            const priceRange = document.getElementById('price-range-filter').value;
            const bedrooms = document.getElementById('bedrooms-filter').value;
            
            // Build query string
            const params = new URLSearchParams();
            if (propertyType) params.set('property_type', propertyType);
            if (priceRange) params.set('price_range', priceRange);
            if (bedrooms) params.set('bedrooms', bedrooms);
            
            // Redirect with filters
            const currentUrl = new URL(window.location);
            params.forEach((value, key) => {
                currentUrl.searchParams.set(key, value);
            });
            
            window.location.href = currentUrl.toString();
        });
    }
});
</script>

<?php get_footer(); ?>
