<?php
/**
 * Single Listing Template
 * 
 * Template for displaying individual property listings
 * Uses HPH framework utilities and component system
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

get_header();

// Get the listing ID
$listing_id = get_the_ID();

// Only get minimal data needed for gallery strip - let template parts handle their own data
$gallery_images = [];
if (function_exists('hpt_get_listing_gallery_data')) {
    try {
        $gallery_data = hpt_get_listing_gallery_data($listing_id);
        $gallery_images = $gallery_data['images'] ?? [];
    } catch (Exception $e) {
        error_log('Gallery bridge function failed: ' . $e->getMessage());
        // Fallback to direct field access
        $gallery_images = get_field('photo_gallery', $listing_id) ?: get_field('property_gallery', $listing_id) ?: [];
    }
} else {
    // Direct fallback if bridge not available
    $gallery_images = get_field('photo_gallery', $listing_id) ?: get_field('property_gallery', $listing_id) ?: [];
}

// Get basic status for gallery display only
$listing_status = get_field('listing_status', $listing_id) ?: 'active';
$status_colors = [
    'active' => 'success',
    'pending' => 'warning', 
    'sold' => 'danger',
    'coming_soon' => 'primary'
];
$status_color = $status_colors[$listing_status] ?? 'gray';

?>

<main id="main" class="hph-site-main">
    
    <?php while (have_posts()) : the_post(); ?>
    
    <!-- Breadcrumb Navigation -->
    <?php get_template_part('template-parts/listing/breadcrumb', null, ['listing_id' => $listing_id]); ?>
    
    <!-- Hero Section -->
    <?php get_template_part('template-parts/listing/hero', null, [
        'listing_id' => $listing_id,
        'layout' => 'full-width',
        'show_gallery' => true,
        'show_price' => true,
        'show_stats' => true,
        'show_share' => true,
        'show_save' => true
    ]); ?>
    
    <!-- Gallery Strip -->
    <section class="hph-gallery-strip hph-bg-white hph-py-lg hph-border-b hph-border-gray-200">
        <div class="hph-container">
            <div class="hph-gallery-thumbnails hph-flex hph-gap-md hph-overflow-x-auto">
                <?php 
                $max_thumbnails = 5;
                
                foreach (array_slice($gallery_images, 0, $max_thumbnails) as $index => $image) : ?>
                    <div class="hph-gallery-thumb hph-flex-shrink-0 hph-w-32 hph-h-20 hph-rounded-lg hph-overflow-hidden hph-cursor-pointer hover:hph-scale-105 hph-transition-transform"
                         data-index="<?php echo esc_attr($index); ?>">
                        <img src="<?php echo esc_url($image['sizes']['thumbnail'] ?? $image['url']); ?>" 
                             alt="<?php echo esc_attr($image['alt'] ?? 'Property photo'); ?>"
                             class="hph-w-full hph-h-full hph-object-cover">
                    </div>
                <?php endforeach; ?>
                
                <?php if (count($gallery_images) > $max_thumbnails) : ?>
                    <button class="hph-view-all-photos hph-flex hph-items-center hph-justify-center hph-min-w-32 hph-px-md hph-bg-primary hph-text-white hph-rounded-lg hph-font-semibold hph-cursor-pointer hover:hph-bg-primary-dark hph-transition-colors"
                            onclick="openGalleryLightbox()">
                        <i class="fas fa-images hph-mr-sm"></i>
                        +<?php echo count($gallery_images) - $max_thumbnails; ?> Photos
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </section>
    
    <!-- Main Content Area -->
    <section class="hph-listing-content hph-py-3xl">
        <div class="hph-container">
            <div class="hph-content-grid hph-grid hph-grid-cols-1 hph-lg:hph-grid-cols-[1fr_380px] hph-gap-3xl">
                
                <!-- Main Body Content -->
                <div class="hph-main-body">
                    
                    <!-- Include main body template part for all property details -->
                    <?php get_template_part('template-parts/listing/main-body', null, ['listing_id' => $listing_id]); ?>
                    
                </div>
                
                <!-- Sidebar -->
                <aside class="hph-listing-sidebar hph-lg:hph-sticky hph-lg:hph-top-24">
                    
                    <!-- Agent Sidebar -->
                    <?php get_template_part('template-parts/listing/sidebar-agent', null, ['listing_id' => $listing_id]); ?>
                    
                    <!-- Open Houses Widget -->
                    <?php get_template_part('template-parts/listing/sidebar-open-houses', null, ['listing_id' => $listing_id]); ?>
                    
                    <!-- Mortgage Calculator Widget -->
                    <?php get_template_part('template-parts/listing/sidebar-mortgage-calculator', null, ['listing_id' => $listing_id]); ?>
                    
                </aside>
                
            </div>
        </div>
    </section>
    
    <!-- Full Width Gallery (Hidden by default, shown via JavaScript) -->
    <?php get_template_part('template-parts/listing/gallery', null, [
        'listing_id' => $listing_id,
        'style' => 'grid',
        'columns' => 3,
        'show_thumbnails' => true,
        'lightbox' => true,
        'show_count' => true
    ]); ?>
    
    <!-- Map Section -->
    <?php 
    $coordinates = null;
    if (function_exists('hpt_get_listing_coordinates')) {
        try {
            $coordinates = hpt_get_listing_coordinates($listing_id);
        } catch (Exception $e) {
            $lat = get_field('latitude', $listing_id);
            $lng = get_field('longitude', $listing_id);
            $coordinates = ($lat && $lng) ? ['lat' => $lat, 'lng' => $lng] : null;
        }
    } else {
        $lat = get_field('latitude', $listing_id);
        $lng = get_field('longitude', $listing_id);
        $coordinates = ($lat && $lng) ? ['lat' => $lat, 'lng' => $lng] : null;
    }
    
    if ($coordinates && $coordinates['lat'] && $coordinates['lng']) : ?>
        <?php get_template_part('template-parts/listing/map-section', null, ['listing_id' => $listing_id]); ?>
    <?php endif; ?>
    
    <!-- Neighborhood Section -->
    <?php get_template_part('template-parts/listing/neighborhood-section', null, ['listing_id' => $listing_id]); ?>
    
    <!-- Virtual Tour Section -->
    <?php 
    $virtual_tour_url = get_field('virtual_tour_url', $listing_id);
    $video_tour_url = get_field('video_tour_url', $listing_id);
    
    if ($virtual_tour_url || $video_tour_url) : ?>
        <?php get_template_part('template-parts/listing/virtual-tour', null, ['listing_id' => $listing_id]); ?>
    <?php endif; ?>
    
    <!-- Similar Listings -->
    <?php get_template_part('template-parts/listing/similar-listings', null, [
        'listing_id' => $listing_id
    ]); ?>
    
    <?php endwhile; ?>
    
</main>

<?php get_footer(); ?>