<?php
/**
 * Listings List View
 * 
 * Display listings in a table format with filters and actions
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user and permissions
$current_user = wp_get_current_user();
$dashboard = \HappyPlace\Dashboard\Frontend_Admin_Dashboard::get_instance();
?>

<!-- Listings Filters -->
<div class="listings-filters">
    <form id="listings-filter-form" class="filter-form">
        <div class="filter-group">
            <div class="filter-item">
                <label for="filter-status" class="dashboard-form-label"><?php _e('Status', 'happy-place'); ?></label>
                <select id="filter-status" name="status" class="dashboard-form-control">
                    <option value=""><?php _e('All Statuses', 'happy-place'); ?></option>
                    <option value="active"><?php _e('Active', 'happy-place'); ?></option>
                    <option value="pending"><?php _e('Pending', 'happy-place'); ?></option>
                    <option value="sold"><?php _e('Sold', 'happy-place'); ?></option>
                    <option value="off_market"><?php _e('Off Market', 'happy-place'); ?></option>
                    <option value="draft"><?php _e('Draft', 'happy-place'); ?></option>
                </select>
            </div>

            <div class="filter-item">
                <label for="filter-agent" class="dashboard-form-label"><?php _e('Agent', 'happy-place'); ?></label>
                <select id="filter-agent" name="agent" class="dashboard-form-control">
                    <option value=""><?php _e('All Agents', 'happy-place'); ?></option>
                    <?php if ($dashboard->user_can('manage_all_listings')): ?>
                        <?php
                        // Get all agents for dropdown
                        $agents = get_posts([
                            'post_type' => 'agent',
                            'posts_per_page' => -1,
                            'post_status' => 'publish',
                            'orderby' => 'title',
                            'order' => 'ASC'
                        ]);
                        
                        foreach ($agents as $agent):
                        ?>
                            <option value="<?php echo esc_attr($agent->ID); ?>"><?php echo esc_html($agent->post_title); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="filter-item">
                <label for="filter-price-min" class="dashboard-form-label"><?php _e('Min Price', 'happy-place'); ?></label>
                <input type="number" id="filter-price-min" name="price_min" class="dashboard-form-control" placeholder="$0">
            </div>

            <div class="filter-item">
                <label for="filter-price-max" class="dashboard-form-label"><?php _e('Max Price', 'happy-place'); ?></label>
                <input type="number" id="filter-price-max" name="price_max" class="dashboard-form-control" placeholder="No limit">
            </div>

            <div class="filter-item">
                <label for="filter-search" class="dashboard-form-label"><?php _e('Search', 'happy-place'); ?></label>
                <input type="text" id="filter-search" name="search" class="dashboard-form-control" placeholder="<?php _e('Address, MLS, etc...', 'happy-place'); ?>">
            </div>

            <div class="filter-item">
                <label class="dashboard-form-label">&nbsp;</label>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <span class="hph-icon-search"></span>
                        <?php _e('Filter', 'happy-place'); ?>
                    </button>
                    <button type="reset" class="btn btn-outline-secondary btn-sm">
                        <span class="hph-icon-x"></span>
                        <?php _e('Clear', 'happy-place'); ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Listings Table -->
<div class="listings-table-container">
    <div class="dashboard-table-wrapper">
        <table id="listings-table" class="dashboard-table">
            <thead>
                <tr>
                    <th width="80"><?php _e('Photo', 'happy-place'); ?></th>
                    <th><?php _e('Property', 'happy-place'); ?></th>
                    <th><?php _e('Address', 'happy-place'); ?></th>
                    <th width="120"><?php _e('Price', 'happy-place'); ?></th>
                    <th width="100"><?php _e('Status', 'happy-place'); ?></th>
                    <th width="120"><?php _e('Agent', 'happy-place'); ?></th>
                    <th width="100"><?php _e('Date', 'happy-place'); ?></th>
                    <th width="120"><?php _e('Actions', 'happy-place'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Get listings based on user permissions
                $listings_args = [
                    'post_type' => 'listing',
                    'posts_per_page' => 25,
                    'post_status' => ['publish', 'draft', 'pending'],
                    'orderby' => 'date',
                    'order' => 'DESC'
                ];

                // Restrict to user's listings if not admin/broker
                if (!$dashboard->user_can('manage_all_listings')) {
                    $listings_args['meta_query'] = [
                        [
                            'key' => 'listing_agent',
                            'value' => $current_user->ID,
                            'compare' => '='
                        ]
                    ];
                }

                $listings_query = new WP_Query($listings_args);

                if ($listings_query->have_posts()):
                    while ($listings_query->have_posts()): $listings_query->the_post();
                        $listing_id = get_the_ID();
                        $listing_price = get_field('price', $listing_id);
                        $listing_status = get_field('property_status', $listing_id) ?: 'draft';
                        $listing_agent_id = get_field('listing_agent', $listing_id);
                        $listing_agent = $listing_agent_id ? get_post($listing_agent_id) : null;
                        $listing_address = get_field('street_address', $listing_id);
                        $listing_city = get_field('city', $listing_id);
                        $listing_state = get_field('state', $listing_id);
                        $featured_image = get_the_post_thumbnail($listing_id, 'thumbnail');
                        ?>
                        <tr data-listing-id="<?php echo esc_attr($listing_id); ?>">
                            <td>
                                <?php if ($featured_image): ?>
                                    <?php echo $featured_image; ?>
                                <?php else: ?>
                                    <div class="listing-thumbnail-placeholder">
                                        <span class="hph-icon-home"></span>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="listing-title">
                                    <strong><?php the_title(); ?></strong>
                                    <?php
                                    $mls_number = get_field('mls_number', $listing_id);
                                    if ($mls_number):
                                    ?>
                                        <div class="listing-mls">MLS# <?php echo esc_html($mls_number); ?></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="listing-address">
                                    <?php if ($listing_address): ?>
                                        <?php echo esc_html($listing_address); ?><br>
                                    <?php endif; ?>
                                    <?php if ($listing_city || $listing_state): ?>
                                        <small class="text-muted">
                                            <?php echo esc_html($listing_city); ?><?php if ($listing_city && $listing_state): ?>, <?php endif; ?><?php echo esc_html($listing_state); ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($listing_price): ?>
                                    <strong>$<?php echo number_format($listing_price); ?></strong>
                                <?php else: ?>
                                    <span class="text-muted"><?php _e('Not set', 'happy-place'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="listing-status-badge status-<?php echo esc_attr($listing_status); ?>">
                                    <?php
                                    $status_labels = [
                                        'active' => __('Active', 'happy-place'),
                                        'pending' => __('Pending', 'happy-place'),
                                        'sold' => __('Sold', 'happy-place'),
                                        'off_market' => __('Off Market', 'happy-place'),
                                        'draft' => __('Draft', 'happy-place')
                                    ];
                                    echo esc_html($status_labels[$listing_status] ?? ucfirst($listing_status));
                                    ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($listing_agent): ?>
                                    <small><?php echo esc_html($listing_agent->post_title); ?></small>
                                <?php else: ?>
                                    <span class="text-muted"><?php _e('No agent', 'happy-place'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small><?php echo get_the_date('M j, Y'); ?></small>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="<?php echo esc_url(add_query_arg(['dashboard_action' => 'view', 'dashboard_id' => $listing_id], get_permalink())); ?>" 
                                       class="btn-action btn-action-view" title="<?php _e('View', 'happy-place'); ?>">
                                        <span class="hph-icon-eye"></span>
                                    </a>
                                    <?php if ($dashboard->user_can('manage_all_listings') || $listing_agent_id == $current_user->ID): ?>
                                        <a href="<?php echo esc_url(add_query_arg(['dashboard_action' => 'edit', 'dashboard_id' => $listing_id], get_permalink())); ?>" 
                                           class="btn-action btn-action-edit" title="<?php _e('Edit', 'happy-place'); ?>">
                                            <span class="hph-icon-edit"></span>
                                        </a>
                                        <button type="button" class="btn-action btn-action-delete" 
                                                data-listing-id="<?php echo esc_attr($listing_id); ?>" 
                                                title="<?php _e('Delete', 'happy-place'); ?>">
                                            <span class="hph-icon-trash"></span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                else:
                    ?>
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <div class="empty-state">
                                <span class="hph-icon-home" style="font-size: 48px; opacity: 0.3;"></span>
                                <h4><?php _e('No listings found', 'happy-place'); ?></h4>
                                <p class="text-muted"><?php _e('Create your first listing to get started.', 'happy-place'); ?></p>
                                <a href="<?php echo esc_url(add_query_arg(['dashboard_action' => 'add'], get_permalink())); ?>" class="btn btn-primary">
                                    <span class="hph-icon-plus"></span>
                                    <?php _e('Add Listing', 'happy-place'); ?>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($listings_query->max_num_pages > 1): ?>
        <div class="listings-pagination">
            <nav aria-label="<?php _e('Listings pagination', 'happy-place'); ?>">
                <?php
                echo paginate_links([
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'current' => max(1, get_query_var('paged')),
                    'total' => $listings_query->max_num_pages,
                    'prev_text' => '<span class="hph-icon-arrow-left"></span> ' . __('Previous', 'happy-place'),
                    'next_text' => __('Next', 'happy-place') . ' <span class="hph-icon-arrow-right"></span>',
                    'type' => 'list',
                    'class' => 'pagination-list'
                ]);
                ?>
            </nav>
        </div>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    // Handle filter form submission
    $('#listings-filter-form').on('submit', function(e) {
        e.preventDefault();
        applyFilters();
    });

    // Handle filter reset
    $('#listings-filter-form').on('reset', function(e) {
        setTimeout(function() {
            applyFilters();
        }, 100);
    });

    // Handle delete buttons
    $('.btn-action-delete').on('click', function() {
        const listingId = $(this).data('listing-id');
        const row = $(this).closest('tr');
        
        if (confirm(hph_dashboard.strings.confirm_delete)) {
            deleteListing(listingId, row);
        }
    });

    function applyFilters() {
        const formData = $('#listings-filter-form').serialize();
        
        // Show loading
        showLoadingOverlay();
        
        // Reload page with filters
        const currentUrl = new URL(window.location);
        const params = new URLSearchParams(formData);
        
        // Update URL parameters
        for (const [key, value] of params) {
            if (value) {
                currentUrl.searchParams.set(key, value);
            } else {
                currentUrl.searchParams.delete(key);
            }
        }
        
        window.location.href = currentUrl.toString();
    }

    function deleteListing(listingId, row) {
        $.ajax({
            url: hph_dashboard.ajax_url,
            type: 'POST',
            data: {
                action: 'hph_delete_listing',
                listing_id: listingId,
                nonce: hph_dashboard.nonce
            },
            beforeSend: function() {
                row.addClass('deleting');
            },
            success: function(response) {
                if (response.success) {
                    row.fadeOut(function() {
                        row.remove();
                        
                        // Check if table is empty
                        if ($('#listings-table tbody tr:visible').length === 0) {
                            location.reload();
                        }
                    });
                } else {
                    alert(response.data.message || '<?php _e("Error deleting listing", "happy-place"); ?>');
                    row.removeClass('deleting');
                }
            },
            error: function() {
                alert('<?php _e("Error deleting listing", "happy-place"); ?>');
                row.removeClass('deleting');
            }
        });
    }

    function showLoadingOverlay() {
        $('#hph-loading-overlay').show();
    }
});
</script>

<style>
.listing-thumbnail-placeholder {
    width: 60px;
    height: 45px;
    background: var(--hph-gray-100);
    border-radius: var(--hph-border-radius);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--hph-gray-400);
}

.listing-title strong {
    display: block;
    margin-bottom: 2px;
}

.listing-mls {
    font-size: var(--hph-text-xs);
    color: var(--hph-text-muted);
}

.listing-address {
    font-size: var(--hph-text-sm);
}

.empty-state {
    padding: var(--hph-space-xl);
    text-align: center;
}

.empty-state h4 {
    margin: var(--hph-space-md) 0 var(--hph-space-xs) 0;
    color: var(--hph-text-color);
}

.listings-pagination {
    padding: var(--hph-space-lg);
    border-top: 1px solid var(--hph-border-color);
}

.pagination-list {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: var(--hph-space-xs);
    margin: 0;
    padding: 0;
    list-style: none;
}

.pagination-list li {
    margin: 0;
}

.pagination-list a,
.pagination-list span {
    display: block;
    padding: var(--hph-space-xs) var(--hph-space-sm);
    border: 1px solid var(--hph-border-color);
    border-radius: var(--hph-border-radius);
    text-decoration: none;
    color: var(--hph-text-color);
    transition: var(--hph-transition);
}

.pagination-list a:hover {
    background: var(--hph-primary);
    color: white;
    border-color: var(--hph-primary);
}

.pagination-list .current span {
    background: var(--hph-primary);
    color: white;
    border-color: var(--hph-primary);
}

.deleting {
    opacity: 0.5;
    pointer-events: none;
}

.filter-actions {
    display: flex;
    gap: var(--hph-space-xs);
}

@media (max-width: 767px) {
    .dashboard-table th,
    .dashboard-table td {
        font-size: var(--hph-text-xs);
        padding: var(--hph-space-xs);
    }
    
    .action-buttons {
        flex-direction: column;
        gap: 2px;
    }
    
    .btn-action {
        font-size: 10px;
        padding: 2px 4px;
    }
}
</style>