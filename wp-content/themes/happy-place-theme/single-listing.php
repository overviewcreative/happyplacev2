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

// Framework handles sidebar styling automatically

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
        'show_gallery' => true,  // Disable gallery in hero since we have media hero
        'show_price' => true,
        'show_stats' => true,
        'show_share' => true,
        'show_save' => true
    ]); ?>
    
    <!-- Main Content Area with Sidebar - Framework Layout -->
    <section class="hph-listing-content">
        <div class="hph-listing-layout layout-with-sidebar">
            
            <!-- Main Body Content -->
            <main class="hph-listing-main main-content">
                
                <!-- Include main body template part for all property details -->
                <?php get_template_part('template-parts/listing/main-body', null, ['listing_id' => $listing_id]); ?>
                
                <!-- Map Section -->
                <?php 
                // Check if coordinates exist for map display
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
                    <?php get_template_part('template-parts/listing/simple-map', null, ['listing_id' => $listing_id]); ?>
                <?php endif; ?>
                
                <!-- City/Community Information Card -->
                <?php get_template_part('template-parts/listing/city-community-card', null, ['listing_id' => $listing_id]); ?>
                
                <!-- Neighborhood Section -->
                <?php // get_template_part('template-parts/listing/neighborhood-section', null, ['listing_id' => $listing_id]); ?>
                
            </main>
            
            <!-- Framework Sidebar -->
            <aside class="hph-listing-sidebar-wrapper sidebar">
                
                <!-- Agent Sidebar (includes collapsible contact form) -->
                <?php get_template_part('template-parts/listing/sidebar-agent', null, ['listing_id' => $listing_id]); ?>
                
                <!-- Mortgage Calculator Widget (collapsible, collapsed by default) -->
                <?php get_template_part('template-parts/listing/sidebar-mortgage-calculator', null, ['listing_id' => $listing_id]); ?>
                
                <!-- Open Houses Widget -->
                <?php get_template_part('template-parts/listing/sidebar-open-houses', null, ['listing_id' => $listing_id]); ?>
                
            </aside>
            
        </div>
    </section>

    
    <!-- Virtual Tour Section -->
    <?php get_template_part('template-parts/listing/virtual-tour', null, ['listing_id' => $listing_id]); ?>
    
    <!-- Full Width Similar Listings Section -->
    
    
    <?php endwhile; ?>
    
</main>

<?php get_footer(); ?>
