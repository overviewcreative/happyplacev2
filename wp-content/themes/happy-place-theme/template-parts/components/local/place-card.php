<?php
/**
 * Place Card Component
 * 
 * @package HappyPlaceTheme
 * @since 1.0.0
 * 
 * @param array $args {
 *     @type int    $place_id  Post ID of the place (defaults to current post)
 *     @type string $variant   Card variant: 'grid', 'list', 'minimal'
 *     @type bool   $show_distance Whether to show distance from user
 * }
 */

$place_id = $args['place_id'] ?? get_the_ID();
$variant = $args['variant'] ?? 'grid';
$show_distance = $args['show_distance'] ?? false;

// Get place data
$title = get_the_title($place_id);
$permalink = get_permalink($place_id);
$excerpt = get_the_excerpt($place_id);
$thumbnail_id = get_post_thumbnail_id($place_id);

// ACF Fields
$primary_city = get_field('primary_city', $place_id);
$address = get_field('address', $place_id);
$price_range = get_field('price_range', $place_id);
$phone = get_field('phone', $place_id);
$website = get_field('website', $place_id);
$is_family_friendly = get_field('is_family_friendly', $place_id);
$lat = get_field('lat', $place_id);
$lng = get_field('lng', $place_id);

// Get place category from taxonomy or post meta
$categories = wp_get_post_terms($place_id, 'place_category');
$primary_category = !empty($categories) ? $categories[0]->name : 'Local Business';

// Image handling
$image_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'medium_large') : get_template_directory_uri() . '/assets/images/placeholder-place.jpg';
$image_alt = $thumbnail_id ? get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true) : $title;

// Build modifier classes
$card_classes = [
    'hph-place-card',
    'hph-place-card--' . $variant
];

if (!$thumbnail_id) {
    $card_classes[] = 'hph-place-card--no-image';
}

if ($is_family_friendly) {
    $card_classes[] = 'is-family-friendly';
}
?>

<article class="<?php echo esc_attr(implode(' ', $card_classes)); ?>" data-place-id="<?php echo esc_attr($place_id); ?>">
    
    <?php if ($variant !== 'minimal'): ?>
    <!-- Card Media -->
    <div class="hph-place-card__media">
        <a href="<?php echo esc_url($permalink); ?>" class="hph-place-card__image-link">
            <div class="hph-place-card__image-wrapper">
                <img 
                    src="<?php echo esc_url($image_url); ?>" 
                    alt="<?php echo esc_attr($image_alt); ?>"
                    class="hph-place-card__image"
                    loading="lazy"
                >
            </div>
            
            <!-- Overlay Badges -->
            <div class="hph-place-card__badges">
                <span class="hph-badge hph-badge--category">
                    <?php echo esc_html($primary_category); ?>
                </span>
                
                <?php if ($price_range): ?>
                <span class="hph-badge hph-badge--price">
                    <?php echo esc_html($price_range); ?>
                </span>
                <?php endif; ?>
                
                <?php if ($is_family_friendly): ?>
                <span class="hph-badge hph-badge--family">
                    <i class="hph-icon hph-icon--family"></i>
                    Family Friendly
                </span>
                <?php endif; ?>
            </div>
        </a>
    </div>
    <?php endif; ?>
    
    <!-- Card Content -->
    <div class="hph-place-card__content">
        <div class="hph-place-card__header">
            <h3 class="hph-place-card__title">
                <a href="<?php echo esc_url($permalink); ?>" class="hph-place-card__title-link">
                    <?php echo esc_html($title); ?>
                </a>
            </h3>
            
            <?php if ($variant === 'grid'): ?>
            <button class="hph-place-card__favorite" data-place-id="<?php echo esc_attr($place_id); ?>" aria-label="Save to favorites">
                <i class="hph-icon hph-icon--heart-outline"></i>
            </button>
            <?php endif; ?>
        </div>
        
        <!-- Meta Information -->
        <div class="hph-place-card__meta">
            <?php if ($primary_city): ?>
            <span class="hph-place-card__location">
                <i class="hph-icon hph-icon--location"></i>
                <?php echo esc_html($primary_city->post_title); ?>
            </span>
            <?php endif; ?>
            
            <?php if ($show_distance && $lat && $lng): ?>
            <span class="hph-place-card__distance" data-lat="<?php echo esc_attr($lat); ?>" data-lng="<?php echo esc_attr($lng); ?>">
                <i class="hph-icon hph-icon--distance"></i>
                <span class="distance-value">Calculating...</span>
            </span>
            <?php endif; ?>
        </div>
        
        <?php if ($variant === 'grid' && $excerpt): ?>
        <p class="hph-place-card__excerpt">
            <?php echo wp_trim_words($excerpt, 15); ?>
        </p>
        <?php endif; ?>
        
        <?php if ($variant === 'list'): ?>
        <!-- Additional details for list view -->
        <div class="hph-place-card__details">
            <?php if ($address): ?>
            <div class="hph-place-card__address">
                <i class="hph-icon hph-icon--map-pin"></i>
                <?php echo esc_html($address); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($phone): ?>
            <div class="hph-place-card__phone">
                <i class="hph-icon hph-icon--phone"></i>
                <a href="tel:<?php echo esc_attr($phone); ?>"><?php echo esc_html($phone); ?></a>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Card Actions -->
        <div class="hph-place-card__actions">
            <a href="<?php echo esc_url($permalink); ?>" class="hph-btn hph-btn--ghost hph-btn--small">
                View Details
                <i class="hph-icon hph-icon--arrow-right"></i>
            </a>
            
            <?php if ($website && $variant !== 'minimal'): ?>
            <a href="<?php echo esc_url($website); ?>" target="_blank" rel="noopener" class="hph-btn hph-btn--text hph-btn--small">
                Website
                <i class="hph-icon hph-icon--external"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
</article>
