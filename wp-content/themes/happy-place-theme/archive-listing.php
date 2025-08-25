<?php
/**
 * Archive Template for Listings
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

get_header();

// Get archive settings
$archive_title = '';
$archive_description = '';
$hero_image = get_theme_mod('listing_archive_hero_image', '');
$default_view = get_theme_mod('listing_archive_default_view', 'grid');
$show_filters = get_theme_mod('listing_archive_show_filters', true);
$show_map = get_theme_mod('listing_archive_show_map', true);
$items_per_page = get_theme_mod('listing_archive_items_per_page', 12);

// Determine archive context
if (is_post_type_archive('listing')) {
    $archive_title = post_type_archive_title('', false) ?: 'All Listings';
    $archive_description = get_the_archive_description();
} elseif (is_tax()) {
    $term = get_queried_object();
    $archive_title = single_term_title('', false);
    $archive_description = term_description();
    
    // Try to get term meta for hero image
    if (function_exists('get_term_meta')) {
        $term_hero = get_term_meta($term->term_id, 'hero_image', true);
        if ($term_hero) {
            $hero_image = $term_hero;
        }
    }
} elseif (is_author()) {
    $author = get_queried_object();
    $archive_title = 'Listings by ' . $author->display_name;
    $archive_description = get_the_author_meta('description', $author->ID);
}

// Get the query
global $wp_query;
$total_listings = $wp_query->found_posts;
$current_page = max(1, get_query_var('paged'));

// Build filter options dynamically based on available listings
$filter_options = array();

if ($show_filters) {
    // Property Type Filter
    $property_types = get_terms(array(
        'taxonomy' => 'property_type',
        'hide_empty' => true
    ));
    
    if (!is_wp_error($property_types) && !empty($property_types)) {
        $type_options = array();
        foreach ($property_types as $type) {
            $type_options[$type->slug] = $type->name . ' (' . $type->count . ')';
        }
        
        $filter_options[] = array(
            'type' => 'select',
            'name' => 'property_type',
            'label' => 'Property Type',
            'options' => $type_options,
            'placeholder' => 'All Types'
        );
    }
    
    // Status Filter
    $statuses = get_terms(array(
        'taxonomy' => 'listing_status',
        'hide_empty' => true
    ));
    
    if (!is_wp_error($statuses) && !empty($statuses)) {
        $status_options = array();
        foreach ($statuses as $status) {
            $status_options[$status->slug] = $status->name;
        }
        
        $filter_options[] = array(
            'type' => 'checkbox',
            'name' => 'listing_status',
            'label' => 'Status',
            'options' => $status_options
        );
    }
    
    // Bedrooms Filter
    $filter_options[] = array(
        'type' => 'select',
        'name' => 'bedrooms',
        'label' => 'Bedrooms',
        'options' => array(
            '1' => '1+',
            '2' => '2+',
            '3' => '3+',
            '4' => '4+',
            '5' => '5+'
        ),
        'placeholder' => 'Any'
    );
    
    // Bathrooms Filter
    $filter_options[] = array(
        'type' => 'select',
        'name' => 'bathrooms',
        'label' => 'Bathrooms',
        'options' => array(
            '1' => '1+',
            '2' => '2+',
            '3' => '3+',
            '4' => '4+'
        ),
        'placeholder' => 'Any'
    );
    
    // Price Range Filter
    // Get min and max prices from current query
    $price_range = $wpdb->get_row("
        SELECT MIN(CAST(meta_value AS UNSIGNED)) as min_price, 
               MAX(CAST(meta_value AS UNSIGNED)) as max_price
        FROM {$wpdb->postmeta}
        WHERE meta_key = '_listing_price'
        AND post_id IN (SELECT ID FROM {$wpdb->posts} WHERE post_type = 'listing' AND post_status = 'publish')
    ");
    
    if ($price_range && $price_range->min_price && $price_range->max_price) {
        $filter_options[] = array(
            'type' => 'range',
            'name' => 'price',
            'label' => 'Price Range',
            'min' => $price_range->min_price,
            'max' => $price_range->max_price
        );
    }
}

// Sort options
$sort_options = array(
    'date_desc' => 'Newest First',
    'date_asc' => 'Oldest First',
    'price_asc' => 'Price: Low to High',
    'price_desc' => 'Price: High to Low',
    'title_asc' => 'Title: A-Z',
    'title_desc' => 'Title: Z-A'
);

// Add featured sort if applicable
if (function_exists('hpt_has_featured_listings')) {
    $sort_options = array('featured' => 'Featured First') + $sort_options;
}

?>

<!-- Hero Section -->
<?php get_template_part('template-parts/sections/hero', null, array(
    'style' => $hero_image ? 'image' : 'gradient',
    'height' => 'md',
    'background_image' => $hero_image,
    'overlay' => 'gradient-reverse',
    'overlay_opacity' => '60',
    'alignment' => 'center',
    'headline' => $archive_title,
    'subheadline' => $total_listings . ' ' . _n('Property Available', 'Properties Available', $total_listings, 'happy-place-theme'),
    'content' => $archive_description,
    'fade_in' => true,
    'buttons' => array(
        array(
            'text' => 'Save Search',
            'url' => '#save-search',
            'style' => 'white',
            'size' => 'l',
            'icon' => 'fas fa-heart',
            'icon_position' => 'left'
        ),
        array(
            'text' => 'Get Alerts',
            'url' => '#alerts',
            'style' => 'outline-white',
            'size' => 'l',
            'icon' => 'fas fa-bell',
            'icon_position' => 'left'
        )
    )
)); ?>

<!-- Main Content -->
<div class="hph-archive-listing">
    <div class="hph-container">
        
        <!-- Breadcrumb -->
        <?php if (function_exists('hph_breadcrumb')): ?>
        <div class="hph-breadcrumb-wrapper">
            <?php hph_breadcrumb(); ?>
        </div>
        <?php endif; ?>
        
        <!-- Archive Header -->
        <div class="hph-archive-header">
            <div class="hph-archive-info">
                <h2 class="hph-archive-results">
                    <?php if ($wp_query->found_posts > 0): ?>
                        Showing <?php echo (($current_page - 1) * $items_per_page) + 1; ?>-<?php echo min($current_page * $items_per_page, $total_listings); ?> 
                        of <?php echo $total_listings; ?> results
                    <?php else: ?>
                        No results found
                    <?php endif; ?>
                </h2>
                
                <?php if (is_tax() && $term): ?>
                <div class="hph-term-meta">
                    <?php if (function_exists('get_term_meta')): ?>
                        <?php $additional_info = get_term_meta($term->term_id, 'additional_info', true); ?>
                        <?php if ($additional_info): ?>
                        <p class="hph-term-info"><?php echo esc_html($additional_info); ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Quick Stats -->
            <div class="hph-archive-stats">
                <?php
                // Calculate average price
                $avg_price_result = $wpdb->get_var("
                    SELECT AVG(CAST(meta_value AS UNSIGNED))
                    FROM {$wpdb->postmeta}
                    WHERE meta_key = '_listing_price'
                    AND post_id IN (SELECT ID FROM {$wpdb->posts} WHERE post_type = 'listing' AND post_status = 'publish')
                ");
                
                if ($avg_price_result): ?>
                <div class="hph-stat-item">
                    <span class="hph-stat-label">Avg. Price</span>
                    <span class="hph-stat-value">$<?php echo number_format($avg_price_result); ?></span>
                </div>
                <?php endif; ?>
                
                <div class="hph-stat-item">
                    <span class="hph-stat-label">New This Week</span>
                    <span class="hph-stat-value">
                        <?php
                        $new_this_week = $wpdb->get_var("
                            SELECT COUNT(*)
                            FROM {$wpdb->posts}
                            WHERE post_type = 'listing'
                            AND post_status = 'publish'
                            AND post_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                        ");
                        echo $new_this_week ?: '0';
                        ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Listings Grid/List/Map -->
        <?php get_template_part('template-parts/layout/card-layout', null, array(
            'layout' => $default_view,
            'columns' => array(
                'mobile' => 1,
                'tablet' => 2,
                'desktop' => 3,
                'wide' => 4
            ),
            'gap' => 'lg',
            'items' => $wp_query,
            'card_args' => array(
                'style' => 'property',
                'show_price' => true,
                'show_status' => true,
                'show_address' => true,
                'show_details' => true,
                'show_favorite' => true,
                'hover_effect' => 'lift'
            ),
            'show_controls' => true,
            'show_filters' => $show_filters,
            'show_sort' => true,
            'show_pagination' => true,
            'items_per_page' => $items_per_page,
            'filters' => $filter_options,
            'sort_options' => $sort_options,
            'empty_message' => 'No listings match your criteria. Try adjusting your filters or search terms.',
            'container_id' => 'listing-archive',
            'ajax_enabled' => true,
            'animate_cards' => true,
            'map_args' => array(
                'enabled' => $show_map,
                'center_lat' => get_theme_mod('map_center_lat', 38.7296),
                'center_lng' => get_theme_mod('map_center_lng', -75.1327),
                'zoom' => 12,
                'cluster_markers' => true
            )
        )); ?>
        
    </div>
</div>

<!-- CTA Section -->
<section class="hph-archive-cta">
    <div class="hph-container">
        <div class="hph-cta-content">
            <h2 class="hph-cta-title">Can't find what you're looking for?</h2>
            <p class="hph-cta-description">Our team can help you find the perfect property. Get in touch for personalized assistance.</p>
            <div class="hph-cta-buttons">
                <a href="/contact" class="hph-btn hph-btn-primary hph-btn-lg">
                    <i class="fas fa-envelope"></i>
                    Contact an Agent
                </a>
                <a href="/property-alerts" class="hph-btn hph-btn-outline hph-btn-lg">
                    <i class="fas fa-bell"></i>
                    Set up Property Alerts
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Save Search Modal -->
<div id="save-search" class="hph-modal" style="display: none;">
    <div class="hph-modal-content">
        <button class="hph-modal-close">&times;</button>
        <h3>Save This Search</h3>
        <form class="hph-save-search-form">
            <input type="text" name="search_name" placeholder="Name your search" required>
            <label>
                <input type="checkbox" name="email_alerts" checked>
                Send me email alerts for new matches
            </label>
            <button type="submit" class="hph-btn hph-btn-primary">Save Search</button>
        </form>
    </div>
</div>

<style>
/* Archive Page Styles */
.hph-archive-listing {
    padding: var(--hph-space-3xl) 0;
    background: var(--hph-gray-50);
}

.hph-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 var(--hph-space-xl);
}

.hph-breadcrumb-wrapper {
    margin-bottom: var(--hph-space-2xl);
}

.hph-archive-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--hph-space-2xl);
    padding-bottom: var(--hph-space-xl);
    border-bottom: 1px solid var(--hph-gray-200);
}

.hph-archive-results {
    font-size: var(--hph-text-2xl);
    font-weight: var(--hph-font-bold);
    color: var(--hph-text-primary);
    margin: 0;
}

.hph-term-meta {
    margin-top: var(--hph-space-md);
}

.hph-term-info {
    color: var(--hph-text-secondary);
    font-size: var(--hph-text-base);
}

.hph-archive-stats {
    display: flex;
    gap: var(--hph-space-xl);
}

.hph-stat-item {
    text-align: center;
}

.hph-stat-label {
    display: block;
    font-size: var(--hph-text-sm);
    color: var(--hph-text-secondary);
    margin-bottom: var(--hph-space-xs);
}

.hph-stat-value {
    display: block;
    font-size: var(--hph-text-xl);
    font-weight: var(--hph-font-bold);
    color: var(--hph-primary);
}

/* CTA Section */
.hph-archive-cta {
    padding: var(--hph-space-4xl) 0;
    background: linear-gradient(135deg, var(--hph-primary) 0%, var(--hph-primary-dark) 100%);
    color: var(--hph-white);
    text-align: center;
}

.hph-cta-title {
    font-size: var(--hph-text-3xl);
    margin-bottom: var(--hph-space-md);
}

.hph-cta-description {
    font-size: var(--hph-text-lg);
    margin-bottom: var(--hph-space-2xl);
    opacity: 0.9;
}

.hph-cta-buttons {
    display: flex;
    gap: var(--hph-space-lg);
    justify-content: center;
    flex-wrap: wrap;
}

/* Modal */
.hph-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.hph-modal-content {
    background: var(--hph-white);
    padding: var(--hph-space-2xl);
    border-radius: var(--hph-radius-lg);
    max-width: 500px;
    width: 90%;
    position: relative;
}

.hph-modal-close {
    position: absolute;
    top: var(--hph-space-md);
    right: var(--hph-space-md);
    background: none;
    border: none;
    font-size: var(--hph-text-2xl);
    cursor: pointer;
    color: var(--hph-text-secondary);
}

.hph-save-search-form {
    display: flex;
    flex-direction: column;
    gap: var(--hph-space-lg);
    margin-top: var(--hph-space-xl);
}

.hph-save-search-form input[type="text"] {
    padding: var(--hph-space-md);
    border: 1px solid var(--hph-gray-300);
    border-radius: var(--hph-radius-md);
    font-size: var(--hph-text-base);
}

/* Responsive */
@media (max-width: 768px) {
    .hph-archive-header {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--hph-space-lg);
    }
    
    .hph-archive-stats {
        width: 100%;
        justify-content: space-between;
    }
    
    .hph-container {
        padding: 0 var(--hph-space-lg);
    }
}
</style>

<script>
// Archive page specific scripts
document.addEventListener('DOMContentLoaded', function() {
    // Save search modal
    const saveSearchLinks = document.querySelectorAll('[href="#save-search"]');
    const modal = document.getElementById('save-search');
    const modalClose = modal?.querySelector('.hph-modal-close');
    
    saveSearchLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            if (modal) modal.style.display = 'flex';
        });
    });
    
    modalClose?.addEventListener('click', function() {
        modal.style.display = 'none';
    });
    
    modal?.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Save search form
    const saveSearchForm = document.querySelector('.hph-save-search-form');
    saveSearchForm?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Collect current filters and search parameters
        const searchData = {
            name: this.search_name.value,
            alerts: this.email_alerts.checked,
            filters: {}, // Collect from active filters
            sort: document.querySelector('.hph-sort-select')?.value,
            query: window.location.search
        };
        
        // Send via AJAX to save
        fetch(hph_ajax.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'hph_save_search',
                nonce: hph_ajax.nonce,
                search_data: JSON.stringify(searchData)
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modal.style.display = 'none';
                // Show success message
                alert('Search saved successfully!');
            }
        });
    });
});
</script>

<?php get_footer(); ?>