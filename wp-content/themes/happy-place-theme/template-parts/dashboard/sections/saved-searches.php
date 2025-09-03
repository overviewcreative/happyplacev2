<?php
/**
 * Dashboard Saved Searches Section
 * 
 * @package HappyPlaceTheme
 */

$user = wp_get_current_user();

// Get user's saved searches (this would typically be stored in user meta or custom table)
$saved_searches = get_user_meta($user->ID, 'saved_searches', true);
if (!is_array($saved_searches)) {
    $saved_searches = [];
}
?>

<div class="hph-dashboard-section hph-saved-searches-section">
    
    <!-- Saved Searches Header -->
    <div class="hph-section-header">
        <h2 class="hph-section-title">
            <i class="fas fa-search"></i>
            Saved Searches
        </h2>
        <p class="hph-section-description">
            Your personalized property searches with automatic notifications for new matches.
        </p>
    </div>

    <?php if (!empty($saved_searches)): ?>
        
        <!-- Searches Controls -->
        <div class="hph-searches-controls">
            <div class="hph-controls-left">
                <span class="hph-results-count">
                    <?php printf(_n('%d saved search', '%d saved searches', count($saved_searches), 'happy-place-theme'), count($saved_searches)); ?>
                </span>
            </div>
            <div class="hph-controls-right">
                <button type="button" class="hph-btn hph-btn-primary hph-btn-sm" id="newSearchBtn">
                    <i class="fas fa-plus"></i>
                    New Search
                </button>
            </div>
        </div>

        <!-- Saved Searches List -->
        <div class="hph-searches-list">
            <?php foreach ($saved_searches as $index => $search): ?>
                <div class="hph-search-card" data-search-id="<?php echo esc_attr($index); ?>">
                    
                    <div class="hph-search-header">
                        <div class="hph-search-info">
                            <h3 class="hph-search-name">
                                <?php echo esc_html($search['name'] ?? 'Untitled Search'); ?>
                            </h3>
                            <div class="hph-search-meta">
                                <span class="hph-search-date">
                                    Created: <?php echo esc_html(date('M j, Y', strtotime($search['created'] ?? 'now'))); ?>
                                </span>
                                <span class="hph-search-frequency">
                                    Notifications: <?php echo esc_html($search['notification_frequency'] ?? 'Weekly'); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="hph-search-actions">
                            <div class="hph-search-toggle">
                                <input type="checkbox" 
                                       id="search_active_<?php echo esc_attr($index); ?>" 
                                       class="hph-toggle-input"
                                       <?php checked($search['active'] ?? true); ?>>
                                <label for="search_active_<?php echo esc_attr($index); ?>" class="hph-toggle-label">
                                    <span class="hph-toggle-switch"></span>
                                </label>
                            </div>
                            
                            <div class="hph-search-menu">
                                <button type="button" class="hph-menu-trigger">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="hph-menu-dropdown">
                                    <button type="button" class="hph-menu-item" data-action="edit">
                                        <i class="fas fa-edit"></i> Edit Search
                                    </button>
                                    <button type="button" class="hph-menu-item" data-action="duplicate">
                                        <i class="fas fa-copy"></i> Duplicate
                                    </button>
                                    <button type="button" class="hph-menu-item" data-action="delete">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Search Criteria -->
                    <div class="hph-search-criteria">
                        <div class="hph-criteria-tags">
                            <?php if (!empty($search['location'])): ?>
                                <span class="hph-criteria-tag">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo esc_html($search['location']); ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php if (!empty($search['price_min']) || !empty($search['price_max'])): ?>
                                <span class="hph-criteria-tag">
                                    <i class="fas fa-dollar-sign"></i>
                                    <?php if ($search['price_min'] && $search['price_max']): ?>
                                        $<?php echo number_format($search['price_min']); ?> - $<?php echo number_format($search['price_max']); ?>
                                    <?php elseif ($search['price_min']): ?>
                                        $<?php echo number_format($search['price_min']); ?>+
                                    <?php else: ?>
                                        Up to $<?php echo number_format($search['price_max']); ?>
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php if (!empty($search['bedrooms'])): ?>
                                <span class="hph-criteria-tag">
                                    <i class="fas fa-bed"></i>
                                    <?php echo esc_html($search['bedrooms']); ?>+ beds
                                </span>
                            <?php endif; ?>
                            
                            <?php if (!empty($search['bathrooms'])): ?>
                                <span class="hph-criteria-tag">
                                    <i class="fas fa-bath"></i>
                                    <?php echo esc_html($search['bathrooms']); ?>+ baths
                                </span>
                            <?php endif; ?>
                            
                            <?php if (!empty($search['property_type'])): ?>
                                <span class="hph-criteria-tag">
                                    <i class="fas fa-home"></i>
                                    <?php echo esc_html($search['property_type']); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Search Results Summary -->
                    <div class="hph-search-results">
                        <div class="hph-results-summary">
                            <div class="hph-result-stat">
                                <span class="hph-stat-number">12</span>
                                <span class="hph-stat-label">New matches</span>
                            </div>
                            <div class="hph-result-stat">
                                <span class="hph-stat-number">45</span>
                                <span class="hph-stat-label">Total listings</span>
                            </div>
                            <div class="hph-result-stat">
                                <span class="hph-stat-number">3</span>
                                <span class="hph-stat-label">Price drops</span>
                            </div>
                        </div>
                        
                        <div class="hph-results-actions">
                            <a href="/listings?search_id=<?php echo esc_attr($index); ?>" class="hph-btn hph-btn-outline hph-btn-sm">
                                <i class="fas fa-eye"></i>
                                View Results
                            </a>
                            <button type="button" class="hph-btn hph-btn-primary hph-btn-sm" data-action="run-search">
                                <i class="fas fa-sync"></i>
                                Run Search
                            </button>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

    <?php else: ?>
        
        <!-- Empty State -->
        <div class="hph-empty-state">
            <div class="hph-empty-content">
                <i class="fas fa-search hph-empty-icon"></i>
                <h3 class="hph-empty-title">No Saved Searches</h3>
                <p class="hph-empty-description">
                    Create personalized searches and get notified when new properties match your criteria.
                </p>
                <button type="button" class="hph-btn hph-btn-primary" id="createFirstSearch">
                    <i class="fas fa-plus"></i>
                    Create Your First Search
                </button>
            </div>
        </div>
        
    <?php endif; ?>

    <!-- Search Tips -->
    <div class="hph-search-tips">
        <div class="hph-tips-content">
            <h3>Make the Most of Saved Searches</h3>
            <div class="hph-tips-grid">
                <div class="hph-tip-item">
                    <i class="fas fa-bell hph-tip-icon"></i>
                    <h4>Get Notified</h4>
                    <p>Receive email alerts when new properties match your criteria.</p>
                </div>
                <div class="hph-tip-item">
                    <i class="fas fa-sliders-h hph-tip-icon"></i>
                    <h4>Refine Criteria</h4>
                    <p>Adjust your search parameters to get better matches.</p>
                </div>
                <div class="hph-tip-item">
                    <i class="fas fa-copy hph-tip-icon"></i>
                    <h4>Multiple Searches</h4>
                    <p>Create different searches for various neighborhoods or price ranges.</p>
                </div>
                <div class="hph-tip-item">
                    <i class="fas fa-chart-line hph-tip-icon"></i>
                    <h4>Track Market</h4>
                    <p>Monitor price trends and new inventory in your areas of interest.</p>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- New Search Modal (placeholder) -->
<div id="newSearchModal" class="hph-modal" style="display: none;">
    <div class="hph-modal-content">
        <div class="hph-modal-header">
            <h3>Create New Search</h3>
            <button type="button" class="hph-modal-close">&times;</button>
        </div>
        <div class="hph-modal-body">
            <p>Search builder coming soon...</p>
            <p>For now, use the main search page to find properties, then save your search from there.</p>
        </div>
        <div class="hph-modal-footer">
            <button type="button" class="hph-btn hph-btn-outline" onclick="$('#newSearchModal').hide();">Close</button>
            <a href="/listings" class="hph-btn hph-btn-primary">Go to Search</a>
        </div>
    </div>
</div>

<script>
// Saved searches functionality
jQuery(document).ready(function($) {
    
    // Toggle search active/inactive
    $('.hph-toggle-input').on('change', function() {
        const searchId = $(this).attr('id').replace('search_active_', '');
        const isActive = $(this).is(':checked');
        
        // AJAX call to update search status
        $.post(ajaxurl, {
            action: 'toggle_saved_search',
            search_id: searchId,
            active: isActive,
            nonce: '<?php echo wp_create_nonce("search_nonce"); ?>'
        });
    });
    
    // Menu actions
    $('.hph-menu-item').on('click', function() {
        const action = $(this).data('action');
        const searchCard = $(this).closest('.hph-search-card');
        const searchId = searchCard.data('search-id');
        
        switch(action) {
            case 'edit':
                // Open edit modal (placeholder)
                alert('Edit functionality coming soon');
                break;
            case 'duplicate':
                // Duplicate search (placeholder)
                alert('Duplicate functionality coming soon');
                break;
            case 'delete':
                if (confirm('Delete this saved search?')) {
                    // AJAX call to delete search
                    $.post(ajaxurl, {
                        action: 'delete_saved_search',
                        search_id: searchId,
                        nonce: '<?php echo wp_create_nonce("search_nonce"); ?>'
                    }, function(response) {
                        if (response.success) {
                            searchCard.fadeOut(300, function() {
                                searchCard.remove();
                            });
                        }
                    });
                }
                break;
        }
    });
    
    // New search buttons
    $('#newSearchBtn, #createFirstSearch').on('click', function() {
        $('#newSearchModal').show();
    });
    
    // Run search
    $('[data-action="run-search"]').on('click', function() {
        const button = $(this);
        const searchCard = button.closest('.hph-search-card');
        const searchId = searchCard.data('search-id');
        
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Running...');
        
        // AJAX call to run search
        $.post(ajaxurl, {
            action: 'run_saved_search',
            search_id: searchId,
            nonce: '<?php echo wp_create_nonce("search_nonce"); ?>'
        }, function(response) {
            button.prop('disabled', false).html('<i class="fas fa-sync"></i> Run Search');
            
            if (response.success) {
                // Update results summary
                location.reload();
            }
        });
    });
    
});
</script>
