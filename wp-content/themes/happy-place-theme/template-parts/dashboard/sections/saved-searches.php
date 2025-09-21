<?php
/**
 * Saved Searches Dashboard Section
 * 
 * @package HappyPlaceTheme
 */

$user = wp_get_current_user();
$saved_searches = get_user_meta($user->ID, 'saved_property_searches', true) ?: [];
?>

<div class="hph-dashboard-section hph-searches-section">
    
    <!-- Section Header -->
    <div class="hph-section-header hph-mb-8">
        <h2 class="hph-section-title">
            <i class="fas fa-search hph-mr-3"></i>
            <?php _e('Saved Searches', 'happy-place-theme'); ?>
        </h2>
        <p class="hph-section-description">
            <?php _e('Manage your saved property searches and get instant notifications for new matches.', 'happy-place-theme'); ?>
        </p>
    </div>

    <!-- Quick Stats -->
    <div class="hph-stats-grid hph-grid hph-grid-cols-1 sm:hph-grid-cols-2 lg:hph-grid-cols-4 hph-gap-lg hph-mb-8">
        <div class="hph-stat-card primary">
            <div class="hph-stat-card-icon">
                <i class="fas fa-bookmark"></i>
            </div>
            <div class="hph-stat-card-value"><?php echo count($saved_searches); ?></div>
            <div class="hph-stat-card-label"><?php _e('Saved Searches', 'happy-place-theme'); ?></div>
        </div>
        
        <div class="hph-stat-card success">
            <div class="hph-stat-card-icon">
                <i class="fas fa-bell"></i>
            </div>
            <div class="hph-stat-card-value" id="activeAlertsCount">-</div>
            <div class="hph-stat-card-label"><?php _e('Active Alerts', 'happy-place-theme'); ?></div>
        </div>
        
        <div class="hph-stat-card warning">
            <div class="hph-stat-card-icon">
                <i class="fas fa-home"></i>
            </div>
            <div class="hph-stat-card-value" id="newMatchesCount">-</div>
            <div class="hph-stat-card-label"><?php _e('New Matches', 'happy-place-theme'); ?></div>
        </div>
        
        <div class="hph-stat-card info">
            <div class="hph-stat-card-icon">
                <i class="fas fa-eye"></i>
            </div>
            <div class="hph-stat-card-value" id="recentViewsCount">-</div>
            <div class="hph-stat-card-label"><?php _e('Recent Views', 'happy-place-theme'); ?></div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="hph-card">
        <div class="hph-card-header">
            <h3 class="hph-card-title"><?php _e('Your Saved Searches', 'happy-place-theme'); ?></h3>
            <a href="<?php echo home_url('/listings/'); ?>" class="hph-btn hph-btn-primary">
                <i class="fas fa-plus hph-mr-2"></i>
                <?php _e('Create New Search', 'happy-place-theme'); ?>
            </a>
        </div>
        
        <div class="hph-card-content">
            <?php if (!empty($saved_searches)): ?>
                <div class="hph-searches-list">
                    <?php foreach ($saved_searches as $search_id => $search): ?>
                        <div class="search-item" data-search-id="<?php echo esc_attr($search_id); ?>">
                            <div class="search-info">
                                <h4 class="search-title">
                                    <?php echo esc_html($search['name'] ?? 'Untitled Search'); ?>
                                </h4>
                                <div class="search-criteria">
                                    <?php
                                    $criteria = [];
                                    if (!empty($search['location'])) {
                                        $criteria[] = $search['location'];
                                    }
                                    if (!empty($search['price_min']) || !empty($search['price_max'])) {
                                        $price_range = '';
                                        if (!empty($search['price_min'])) {
                                            $price_range .= '$' . number_format($search['price_min']);
                                        }
                                        $price_range .= ' - ';
                                        if (!empty($search['price_max'])) {
                                            $price_range .= '$' . number_format($search['price_max']);
                                        }
                                        $criteria[] = $price_range;
                                    }
                                    if (!empty($search['bedrooms'])) {
                                        $criteria[] = $search['bedrooms'] . '+ beds';
                                    }
                                    echo implode(' • ', $criteria);
                                    ?>
                                </div>
                                <div class="search-meta">
                                    <span class="search-date">
                                        <?php _e('Created:', 'happy-place-theme'); ?>
                                        <?php echo date('M j, Y', strtotime($search['created'] ?? 'now')); ?>
                                    </span>
                                    <span class="search-matches">
                                        <i class="fas fa-home hph-mr-1"></i>
                                        <?php echo $search['match_count'] ?? 0; ?> <?php _e('matches', 'happy-place-theme'); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="search-actions">
                                <a href="<?php echo add_query_arg(array_merge(['search_id' => $search_id], $search), home_url('/listings/')); ?>" 
                                   class="hph-btn hph-btn-outline hph-btn-sm">
                                    <i class="fas fa-search"></i>
                                    <?php _e('View Results', 'happy-place-theme'); ?>
                                </a>
                                
                                <button class="hph-btn hph-btn-ghost hph-btn-sm edit-search" data-search-id="<?php echo esc_attr($search_id); ?>">
                                    <i class="fas fa-edit"></i>
                                    <?php _e('Edit', 'happy-place-theme'); ?>
                                </button>
                                
                                <button class="hph-btn hph-btn-ghost hph-btn-sm text-danger delete-search" data-search-id="<?php echo esc_attr($search_id); ?>">
                                    <i class="fas fa-trash"></i>
                                    <?php _e('Delete', 'happy-place-theme'); ?>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="hph-empty-state">
                    <div class="hph-empty-state-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3 class="hph-empty-state-title"><?php _e('No Saved Searches', 'happy-place-theme'); ?></h3>
                    <p class="hph-empty-state-description">
                        <?php _e('Start searching for properties and save your searches to get notified of new matches.', 'happy-place-theme'); ?>
                    </p>
                    <a href="<?php echo home_url('/listings/'); ?>" class="hph-btn hph-btn-primary hph-btn-lg hph-mt-4">
                        <i class="fas fa-search hph-mr-2"></i>
                        <?php _e('Start Searching', 'happy-place-theme'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
</div>

<style>
.search-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--hph-space-4);
    border: 1px solid var(--hph-gray-200);
    border-radius: var(--hph-radius-lg);
    margin-bottom: var(--hph-space-4);
    transition: all 0.2s ease;
}

.search-item:hover {
    border-color: var(--hph-primary);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.search-info {
    flex: 1;
}

.search-title {
    font-size: var(--hph-text-lg);
    font-weight: var(--hph-font-semibold);
    color: var(--hph-gray-900);
    margin-bottom: var(--hph-space-2);
}

.search-criteria {
    color: var(--hph-gray-600);
    font-size: var(--hph-text-sm);
    margin-bottom: var(--hph-space-2);
}

.search-meta {
    display: flex;
    gap: var(--hph-space-4);
    font-size: var(--hph-text-sm);
    color: var(--hph-gray-500);
}

.search-actions {
    display: flex;
    gap: var(--hph-space-2);
    align-items: center;
}

@media (max-width: 768px) {
    .search-item {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--hph-space-3);
    }
    
    .search-actions {
        width: 100%;
        justify-content: flex-end;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    'use strict';
    
    // Handle search deletion
    $(document).on('click', '.delete-search', function(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to delete this saved search?')) {
            return;
        }
        
        const searchId = $(this).data('search-id');
        const $searchItem = $(this).closest('.search-item');
        
        // AJAX call to delete search
        $.post(ajaxurl, {
            action: 'hph_delete_saved_search',
            search_id: searchId,
            nonce: $('#hph_dashboard_nonce').val()
        })
        .done(function(response) {
            if (response.success) {
                $searchItem.fadeOut(() => {
                    $searchItem.remove();
                    
                    // Check if no more searches
                    if (!$('.search-item').length) {
                        location.reload();
                    }
                });
            } else {
                alert('Failed to delete search. Please try again.');
            }
        })
        .fail(function() {
            alert('Network error. Please try again.');
        });
    });
    
    // Handle search editing (placeholder)
    $(document).on('click', '.edit-search', function(e) {
        e.preventDefault();
        
        const searchId = $(this).data('search-id');
        // TODO: Implement search editing modal
        alert('Search editing coming soon!');
    });
});
</script>
