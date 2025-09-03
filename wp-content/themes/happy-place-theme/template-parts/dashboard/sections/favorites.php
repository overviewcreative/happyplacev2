<?php
/**
 * Dashboard Favorites Section
 * 
 * @package HappyPlaceTheme
 */

$user = wp_get_current_user();

// Get user's favorite listings (this would typically be stored in user meta or custom table)
$favorites = get_user_meta($user->ID, 'favorite_listings', true);
if (!is_array($favorites)) {
    $favorites = [];
}

// Get favorite listings data
$favorite_listings = [];
if (!empty($favorites)) {
    $favorite_listings = get_posts([
        'post_type' => 'listing',
        'post_status' => 'publish',
        'post__in' => $favorites,
        'posts_per_page' => -1,
        'orderby' => 'post__in'
    ]);
}
?>

<div class="hph-dashboard-section hph-favorites-section">
    
    <!-- Favorites Header -->
    <div class="hph-section-header">
        <h2 class="hph-section-title">
            <i class="fas fa-heart"></i>
            Favorite Listings
        </h2>
        <p class="hph-section-description">
            Properties you've saved for quick access and comparison.
        </p>
    </div>

    <?php if (!empty($favorite_listings)): ?>
        
        <!-- Favorites Controls -->
        <div class="hph-favorites-controls">
            <div class="hph-controls-left">
                <span class="hph-results-count">
                    <?php printf(_n('%d favorite property', '%d favorite properties', count($favorite_listings), 'happy-place-theme'), count($favorite_listings)); ?>
                </span>
            </div>
            <div class="hph-controls-right">
                <div class="hph-view-toggle">
                    <button type="button" class="hph-view-btn active" data-view="grid">
                        <i class="fas fa-th-large"></i>
                    </button>
                    <button type="button" class="hph-view-btn" data-view="list">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
                <button type="button" class="hph-btn hph-btn-outline hph-btn-sm" id="clearAllFavorites">
                    <i class="fas fa-trash"></i>
                    Clear All
                </button>
            </div>
        </div>

        <!-- Favorites Grid -->
        <div class="hph-favorites-grid" id="favoritesGrid">
            <?php foreach ($favorite_listings as $listing): ?>
                <?php
                $listing_id = $listing->ID;
                $listing_price = get_field('listing_price', $listing_id);
                $bedrooms = get_field('bedrooms', $listing_id);
                $bathrooms_full = get_field('bathrooms_full', $listing_id);
                $square_feet = get_field('square_feet', $listing_id);
                $listing_status = get_field('listing_status', $listing_id);
                $street_address = trim(get_field('street_number', $listing_id) . ' ' . get_field('street_name', $listing_id));
                $city = get_field('city', $listing_id);
                $state = get_field('state', $listing_id);
                $primary_photo = get_field('primary_photo', $listing_id);
                ?>
                
                <div class="hph-favorite-card" data-listing-id="<?php echo esc_attr($listing_id); ?>">
                    
                    <!-- Card Image -->
                    <div class="hph-card-image">
                        <?php if ($primary_photo): ?>
                            <img src="<?php echo esc_url($primary_photo['sizes']['medium_large'] ?? $primary_photo['url']); ?>" 
                                 alt="<?php echo esc_attr($listing->post_title); ?>"
                                 class="hph-listing-image">
                        <?php else: ?>
                            <div class="hph-image-placeholder">
                                <i class="fas fa-home"></i>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Status Badge -->
                        <?php if ($listing_status): ?>
                            <div class="hph-status-badge hph-status-<?php echo esc_attr($listing_status); ?>">
                                <?php echo esc_html(ucfirst($listing_status)); ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Remove from Favorites -->
                        <button type="button" class="hph-favorite-remove" data-listing-id="<?php echo esc_attr($listing_id); ?>">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>

                    <!-- Card Content -->
                    <div class="hph-card-content">
                        
                        <!-- Price -->
                        <div class="hph-listing-price">
                            <?php if ($listing_price): ?>
                                $<?php echo number_format($listing_price); ?>
                            <?php else: ?>
                                Price Available Upon Request
                            <?php endif; ?>
                        </div>

                        <!-- Property Details -->
                        <div class="hph-listing-details">
                            <?php if ($bedrooms || $bathrooms_full || $square_feet): ?>
                                <div class="hph-property-stats">
                                    <?php if ($bedrooms): ?>
                                        <span class="hph-stat">
                                            <i class="fas fa-bed"></i>
                                            <?php echo esc_html($bedrooms); ?> bed<?php echo $bedrooms != 1 ? 's' : ''; ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($bathrooms_full): ?>
                                        <span class="hph-stat">
                                            <i class="fas fa-bath"></i>
                                            <?php echo esc_html($bathrooms_full); ?> bath<?php echo $bathrooms_full != 1 ? 's' : ''; ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($square_feet): ?>
                                        <span class="hph-stat">
                                            <i class="fas fa-ruler-combined"></i>
                                            <?php echo number_format($square_feet); ?> sq ft
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Address -->
                        <div class="hph-listing-address">
                            <?php if ($street_address): ?>
                                <div class="hph-street-address"><?php echo esc_html($street_address); ?></div>
                            <?php endif; ?>
                            <?php if ($city || $state): ?>
                                <div class="hph-city-state">
                                    <?php echo esc_html(trim($city . ', ' . $state, ', ')); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Actions -->
                        <div class="hph-card-actions">
                            <a href="<?php echo get_permalink($listing_id); ?>" class="hph-btn hph-btn-primary hph-btn-sm">
                                <i class="fas fa-eye"></i>
                                View Details
                            </a>
                            <button type="button" class="hph-btn hph-btn-outline hph-btn-sm" data-action="share" data-listing-id="<?php echo esc_attr($listing_id); ?>">
                                <i class="fas fa-share"></i>
                                Share
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
                <i class="fas fa-heart hph-empty-icon"></i>
                <h3 class="hph-empty-title">No Favorites Yet</h3>
                <p class="hph-empty-description">
                    Start saving properties you're interested in by clicking the heart icon on any listing.
                </p>
                <a href="/listings" class="hph-btn hph-btn-primary">
                    <i class="fas fa-search"></i>
                    Browse Listings
                </a>
            </div>
        </div>
        
    <?php endif; ?>

    <!-- Favorites Tips -->
    <div class="hph-favorites-tips">
        <div class="hph-tips-content">
            <h3>Tips for Managing Favorites</h3>
            <ul class="hph-tips-list">
                <li><i class="fas fa-lightbulb"></i> Save listings to compare properties side by side</li>
                <li><i class="fas fa-bell"></i> Get notified when favorited properties have price changes</li>
                <li><i class="fas fa-share"></i> Share your favorite listings with family and friends</li>
                <li><i class="fas fa-heart"></i> Use favorites to keep track of open houses and showings</li>
            </ul>
        </div>
    </div>

</div>

<script>
// Favorites functionality
jQuery(document).ready(function($) {
    
    // View toggle
    $('.hph-view-btn').on('click', function() {
        const view = $(this).data('view');
        $('.hph-view-btn').removeClass('active');
        $(this).addClass('active');
        
        const grid = $('#favoritesGrid');
        grid.removeClass('hph-view-grid hph-view-list').addClass('hph-view-' + view);
    });
    
    // Remove favorite
    $('.hph-favorite-remove').on('click', function() {
        const listingId = $(this).data('listing-id');
        const card = $(this).closest('.hph-favorite-card');
        
        if (confirm('Remove this property from your favorites?')) {
            // AJAX call to remove favorite
            $.post(ajaxurl, {
                action: 'remove_favorite',
                listing_id: listingId,
                nonce: '<?php echo wp_create_nonce("favorite_nonce"); ?>'
            }, function(response) {
                if (response.success) {
                    card.fadeOut(300, function() {
                        card.remove();
                        // Update count
                        const remaining = $('.hph-favorite-card').length;
                        if (remaining === 0) {
                            location.reload();
                        }
                    });
                }
            });
        }
    });
    
    // Clear all favorites
    $('#clearAllFavorites').on('click', function() {
        if (confirm('Remove all properties from your favorites? This action cannot be undone.')) {
            // AJAX call to clear all favorites
            $.post(ajaxurl, {
                action: 'clear_all_favorites',
                nonce: '<?php echo wp_create_nonce("favorite_nonce"); ?>'
            }, function(response) {
                if (response.success) {
                    location.reload();
                }
            });
        }
    });
    
});
</script>
