<?php
/**
 * Modern Listing Hero Section
 * File: template-parts/listing/hero.php
 * 
 * Clean, modern hero with overlay information
 * Matches contemporary real estate site design
 * Uses bridge functions for data access
 * 
 * @package HappyPlaceTheme
 */

$listing_id = $args['listing_id'] ?? get_the_ID();

// Get listing data using bridge functions with fallbacks
$listing_data = null;
if (function_exists('hpt_get_listing')) {
    try {
        $listing_data = hpt_get_listing($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing failed: ' . $e->getMessage());
    }
}

// Early return only if we can't get basic data at all
if (!$listing_data && !get_the_title($listing_id)) {
    return;
}

// Get media using gallery bridge with fallback
$gallery_data = null;
if (function_exists('hpt_get_listing_gallery_data')) {
    try {
        $gallery_data = hpt_get_listing_gallery_data($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_gallery_data failed: ' . $e->getMessage());
    }
}

// Fallback to direct field access if bridge not available
if (!$gallery_data) {
    $primary_photo = get_field('primary_photo', $listing_id);
    $photo_gallery = get_field('photo_gallery', $listing_id) ?: [];
} else {
    $primary_photo = $gallery_data['primary_photo'] ?? null;
    $photo_gallery = $gallery_data['images'] ?? [];
}

// Get property info using bridge functions with fallbacks
$listing_price = null;
if (function_exists('hpt_get_listing_price_formatted')) {
    try {
        $listing_price = hpt_get_listing_price_formatted($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_price_formatted failed: ' . $e->getMessage());
    }
}
if (!$listing_price) {
    $raw_price = get_field('listing_price', $listing_id);
    $listing_price = $raw_price ? '$' . number_format($raw_price) : 'Price Upon Request';
}

$bedrooms = null;
if (function_exists('hpt_get_listing_bedrooms')) {
    try {
        $bedrooms = hpt_get_listing_bedrooms($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_bedrooms failed: ' . $e->getMessage());
    }
}
if (!$bedrooms) {
    $bedrooms = get_field('bedrooms', $listing_id) ?: 0;
}

$bathrooms_formatted = null;
if (function_exists('hpt_get_listing_bathrooms_formatted')) {
    try {
        $bathrooms_formatted = hpt_get_listing_bathrooms_formatted($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_bathrooms_formatted failed: ' . $e->getMessage());
    }
}
if (!$bathrooms_formatted) {
    $bathrooms_full = get_field('bathrooms_full', $listing_id) ?: 0;
    $bathrooms_half = get_field('bathrooms_half', $listing_id) ?: 0;
    $total_baths = $bathrooms_full + ($bathrooms_half * 0.5);
    $bathrooms_formatted = $total_baths > 0 ? number_format($total_baths, ($bathrooms_half > 0 ? 1 : 0)) : '0';
}

$square_feet_formatted = null;
if (function_exists('hpt_get_listing_square_feet_formatted')) {
    try {
        $square_feet_formatted = hpt_get_listing_square_feet_formatted($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_square_feet_formatted failed: ' . $e->getMessage());
    }
}
if (!$square_feet_formatted) {
    $raw_sqft = get_field('square_feet', $listing_id);
    $square_feet_formatted = $raw_sqft ? number_format($raw_sqft) : '';
}

$lot_size_formatted = null;
if (function_exists('hpt_get_listing_lot_size_formatted')) {
    try {
        $lot_size_formatted = hpt_get_listing_lot_size_formatted($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_lot_size_formatted failed: ' . $e->getMessage());
    }
}
if (!$lot_size_formatted) {
    $lot_acres = get_field('lot_size_acres', $listing_id);
    $lot_sqft = get_field('lot_size_sqft', $listing_id);
    if ($lot_acres) {
        $lot_size_formatted = number_format($lot_acres, 2);
    } elseif ($lot_sqft) {
        // Convert sqft to acres and display as acres
        $acres_from_sqft = $lot_sqft / 43560; // 43,560 sq ft = 1 acre
        $lot_size_formatted = number_format($acres_from_sqft, 2);
    }
}

// Status using bridge functions with fallback
$listing_status = null;
$status_badge = null;
if (function_exists('hpt_get_listing_status')) {
    try {
        $listing_status = hpt_get_listing_status($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_status failed: ' . $e->getMessage());
    }
}
if (function_exists('hpt_get_listing_status_badge')) {
    try {
        $status_badge = hpt_get_listing_status_badge($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_status_badge failed: ' . $e->getMessage());
    }
}
if (!$listing_status) {
    $listing_status = get_field('listing_status', $listing_id) ?: 'Active';
}

// Address using bridge functions with fallback
$address_data = null;
if (function_exists('hpt_get_listing_address')) {
    try {
        $address_data = hpt_get_listing_address($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_address failed: ' . $e->getMessage());
    }
}

if (!$address_data) {
    $street_number = get_field('street_number', $listing_id);
    $street_name = get_field('street_name', $listing_id);
    $street_type = get_field('street_type', $listing_id);
    $city = get_field('city', $listing_id);
    $state = get_field('state', $listing_id);
    $zip = get_field('zip_code', $listing_id);
    
    $street_address = trim($street_number . ' ' . $street_name . ' ' . $street_type);
    $city_state_zip = trim($city . ($state ? ', ' . $state : '') . ($zip ? ' ' . $zip : ''));
} else {
    $street_address = $address_data['street'] ?? '';
    // Build city_state_zip from bridge function array data
    $city = $address_data['city'] ?? '';
    $state = $address_data['state'] ?? '';
    $zip = $address_data['zip'] ?? '';
    $city_state_zip = trim($city . ($state ? ', ' . $state : '') . ($zip ? ' ' . $zip : ''));
}

// Prepare gallery images
$gallery_images = [];
if ($primary_photo) {
    $gallery_images[] = $primary_photo;
}
if (!empty($photo_gallery)) {
    $gallery_images = array_merge($gallery_images, $photo_gallery);
}

// Fallback to featured image
if (empty($gallery_images) && has_post_thumbnail($listing_id)) {
    $gallery_images[] = [
        'url' => get_the_post_thumbnail_url($listing_id, 'full'),
        'alt' => get_the_title($listing_id)
    ];
}

$total_photos = count($gallery_images);
$current_photo = 1;
?>

<section class="hph-hero hph-hero-lg hph-hero--modern">
    <!-- Gallery Background -->
    <div class="hph-hero__gallery">
        <?php if (!empty($gallery_images)) : ?>
            <?php foreach ($gallery_images as $index => $image) : ?>
            <div class="hph-hero__image <?php echo $index === 0 ? 'active' : ''; ?>" 
                 data-slide="<?php echo $index; ?>"
                 style="background-image: url('<?php echo esc_url($image['url']); ?>');">
            </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="hph-hero__image hph-hero__image--placeholder active">
                <i class="fas fa-home"></i>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Status Badge -->
    <?php if ($listing_status === 'coming_soon') : ?>
    <div class="hph-hero__status">
        COMING SOON
    </div>
    <?php elseif ($listing_status === 'pending') : ?>
    <div class="hph-hero__status hph-hero__status--pending">
        SALE PENDING
    </div>
    <?php elseif ($listing_status === 'sold') : ?>
    <div class="hph-hero__status hph-hero__status--sold">
        SOLD
    </div>
    <?php endif; ?>
    
    <!-- Photo Counter & Navigation -->
    <div class="hph-hero__photo-nav">
        <span class="hph-hero__photo-count">
            <i class="fas fa-camera"></i>
            <span id="current-photo"><?php echo $current_photo; ?></span> / <?php echo $total_photos; ?>
        </span>
        <?php if ($total_photos > 1) : ?>
        <button class="hph-hero__nav-btn hph-hero__nav-btn--prev" onclick="previousSlide()">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="hph-hero__nav-btn hph-hero__nav-btn--next" onclick="nextSlide()">
            <i class="fas fa-chevron-right"></i>
        </button>
        <?php endif; ?>
    </div>
    
    <!-- Main Content Overlay -->
    <div class="hph-hero__overlay">
        <div class="hph-container">
            <div class="hph-hero__content">
                <!-- Property Info -->
                <div class="hph-hero__info">
                    <h1 class="hph-hero__title"><?php echo esc_html($street_address); ?></h1>
                    <div class="hph-hero__location">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php echo esc_html($city_state_zip); ?>
                    </div>
                    
                    <div class="hph-hero__price-wrapper">
                        <div class="hph-hero__price">
                            <?php echo esc_html($listing_price); ?>
                        </div>
                        <?php 
                        $price_per_sqft = hpt_get_listing_price_per_sqft($listing_id);
                        if ($price_per_sqft) : ?>
                        <div class="hph-hero__price-per">
                            $<?php echo esc_html(number_format($price_per_sqft)); ?>/sq ft
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Property Stats -->
                    <div class="hph-hero__stats">
                        <?php if ($bedrooms) : ?>
                        <div class="hph-hero__stat">
                            <i class="fas fa-bed"></i>
                            <div class="hph-hero__stat-info">
                                <span class="hph-hero__stat-value"><?php echo esc_html($bedrooms); ?></span>
                                <span class="hph-hero__stat-label">BEDROOMS</span>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($bathrooms_formatted) : ?>
                        <div class="hph-hero__stat">
                            <i class="fas fa-bath"></i>
                            <div class="hph-hero__stat-info">
                                <span class="hph-hero__stat-value"><?php echo esc_html($bathrooms_formatted); ?></span>
                                <span class="hph-hero__stat-label">BATHROOMS</span>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($square_feet_formatted) : ?>
                        <div class="hph-hero__stat">
                            <i class="fas fa-ruler-combined"></i>
                            <div class="hph-hero__stat-info">
                                <span class="hph-hero__stat-value"><?php echo esc_html($square_feet_formatted); ?></span>
                                <span class="hph-hero__stat-label">SQ FT</span>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($lot_size_formatted) : ?>
                        <div class="hph-hero__stat">
                            <i class="fas fa-expand-arrows-alt"></i>
                            <div class="hph-hero__stat-info">
                                <span class="hph-hero__stat-value"><?php echo esc_html($lot_size_formatted); ?></span>
                                <span class="hph-hero__stat-label">LOT SIZE</span>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="hph-hero__actions">
                        <button class="hph-hero__btn hph-hero__btn--primary" onclick="scheduleShowing()">
                            <i class="fas fa-calendar-check"></i>
                            Schedule Tour
                        </button>
                        
                        <button class="hph-hero__btn hph-hero__btn--secondary" onclick="openGallery()">
                            <i class="fas fa-images"></i>
                            View All Photos
                        </button>
                        
                        <button class="hph-hero__btn hph-hero__btn--icon" onclick="toggleFavorite()">
                            <i class="far fa-heart"></i>
                        </button>
                        
                        <button class="hph-hero__btn hph-hero__btn--icon" onclick="shareProperty()">
                            <i class="fas fa-share-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>




<script>
let currentSlide = 0;
const slides = document.querySelectorAll('.hph-hero__image');
const totalSlides = slides.length;

function showSlide(index) {
    slides.forEach(slide => slide.classList.remove('active'));
    currentSlide = (index + totalSlides) % totalSlides;
    slides[currentSlide].classList.add('active');
    
    // Update counter
    const counter = document.getElementById('current-photo');
    if (counter) {
        counter.textContent = currentSlide + 1;
    }
}

function nextSlide() {
    showSlide(currentSlide + 1);
}

function previousSlide() {
    showSlide(currentSlide - 1);
}

function scheduleShowing() {
    // Open scheduling modal or redirect
    console.log('Schedule showing clicked');
}

function openGallery() {
    // Open full gallery modal
    if (typeof openGalleryModal === 'function') {
        openGalleryModal();
    }
}

function toggleFavorite() {
    const btn = event.currentTarget;
    const icon = btn.querySelector('i');
    icon.classList.toggle('far');
    icon.classList.toggle('fas');
}

function shareProperty() {
    if (navigator.share) {
        navigator.share({
            title: document.title,
            url: window.location.href
        });
    }
}

// Auto-rotate slides
if (totalSlides > 1) {
    setInterval(() => {
        nextSlide();
    }, 5000);
}
</script>