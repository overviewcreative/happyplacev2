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

// Check if gallery should be shown based on args
$show_gallery = $args['show_gallery'] ?? true;

// Get change tracking data from args (passed from single-listing.php)
$listing_changes = $args['listing_changes'] ?? [];
$listing_badges = $args['listing_badges'] ?? [];
$has_recent_changes = $args['has_recent_changes'] ?? false;
$is_new_listing = $args['is_new_listing'] ?? false;

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

// Format square feet without unit label (since label is displayed below)
$square_feet_formatted = null;
$raw_sqft = get_field('square_feet', $listing_id);
$square_feet_formatted = $raw_sqft ? number_format($raw_sqft) : '';

// Always format lot size in acres, don't use bridge function for this
$lot_size_formatted = null;
$lot_acres = get_field('lot_size_acres', $listing_id);
$lot_sqft = get_field('lot_size_sqft', $listing_id);

if ($lot_acres && $lot_acres > 0) {
    $lot_size_formatted = number_format($lot_acres, 2);
} elseif ($lot_sqft && $lot_sqft > 0) {
    // Convert sqft to acres and display as acres
    $acres_from_sqft = $lot_sqft / 43560; // 43,560 sq ft = 1 acre
    $lot_size_formatted = number_format($acres_from_sqft, 2);
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

// Always get address components directly to ensure we have data
$street_number = get_field('street_number', $listing_id) ?: '';
$street_name = get_field('street_name', $listing_id) ?: '';
$street_type = get_field('street_type', $listing_id) ?: '';
$city = get_field('city', $listing_id) ?: '';
$state = get_field('state', $listing_id) ?: '';
$zip = get_field('zip_code', $listing_id) ?: '';

// Use bridge data if available, otherwise use direct fields
if ($address_data && !empty($address_data['street'])) {
    $street_address = $address_data['street'];
    $city = $address_data['city'] ?? $city;
    $state = $address_data['state'] ?? $state;
    $zip = $address_data['zip'] ?? $zip;
} else {
    // Build street address from components
    $street_parts = array_filter([$street_number, $street_name, $street_type]);
    $street_address = implode(' ', $street_parts);
}

// Build city, state, zip display
$location_parts = array_filter([$city, $state, $zip]);
if (count($location_parts) >= 2) {
    $city_state_zip = $city . ', ' . $state . ' ' . $zip;
} else {
    $city_state_zip = implode(' ', $location_parts);
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
$unique_id = uniqid('hero_gallery_');

// Ensure Font Awesome is loaded for icons
if (!wp_style_is('font-awesome', 'enqueued')) {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', array(), '6.5.1');
}
?>

<section class="hph-hero hph-hero-lg hph-hero--modern <?php echo $show_gallery ? 'hph-hero--gallery' : 'hph-hero--simple'; ?>" 
         data-component="<?php echo $show_gallery ? 'hero-gallery' : 'hero-simple'; ?>" 
         data-listing-id="<?php echo esc_attr($listing_id); ?>">
    
    <?php if ($show_gallery) : ?>
    <!-- Gallery Background - Clickable for Gallery Mode -->
    <div class="hph-hero__gallery hph-cursor-pointer"
         onclick="toggleHeroGalleryMode('<?php echo esc_js($unique_id); ?>')">

        <!-- Image Fill Mode Toggle Button (shown only in gallery mode) -->
        <button class="hph-hero__image-fill-toggle hph-mobile-fill-toggle"
                id="image-fill-toggle-<?php echo esc_attr($unique_id); ?>"
                onclick="event.stopPropagation(); toggleImageFillMode('<?php echo esc_js($unique_id); ?>')"
                title="Toggle between fit and fill modes">
            <i class="fas fa-expand-arrows-alt"></i>
            <span class="fill-mode-text">Fit Image</span>
        </button>

        <!-- Fit Image Container (for contain mode) -->
        <div class="hph-hero__fit-image-container" id="fit-image-container-<?php echo esc_attr($unique_id); ?>">
            <img class="hph-hero__fit-image" id="fit-image-<?php echo esc_attr($unique_id); ?>" alt="" />
        </div>

    <?php else : ?>
    <!-- Simple Background Image -->
    <div class="hph-hero__background">
    <?php endif; ?>
        <?php if (!empty($gallery_images)) : ?>
            <?php if ($show_gallery) : ?>
                <?php foreach ($gallery_images as $index => $image) : ?>
                <div class="hph-hero__image <?php echo $index === 0 ? 'active' : ''; ?><?php echo $index > 0 ? ' lazy-bg' : ''; ?>" 
                     data-slide="<?php echo $index; ?>"
                     <?php if ($index === 0) : ?>
                         style="background-image: url('<?php echo esc_url(hph_add_fastly_optimization($image['url'], 'full')); ?>');"
                     <?php else : ?>
                         data-bg="<?php echo esc_url(hph_add_fastly_optimization($image['url'], 'full')); ?>"
                     <?php endif; ?>>
                </div>
                <?php endforeach; ?>
            <?php else : ?>
                <!-- Show only first image for simple hero -->
                <?php $first_image = $gallery_images[0]; ?>
                <div class="hph-hero__image active"
                     style="background-image: url('<?php echo esc_url(hph_add_fastly_optimization($first_image['url'], 'full')); ?>');">
                </div>
            <?php endif; ?>
        <?php else : ?>
            <div class="hph-hero__image hph-hero__image--placeholder active">
                <i class="fas fa-home"></i>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if ($show_gallery) : ?>
    <!-- Gallery Thumbnail Strip (hidden until gallery mode) -->
    <div class="hph-hero__gallery-strip">
        <?php if (!empty($gallery_images)) : ?>
            <?php foreach ($gallery_images as $index => $image) : ?>
            <?php
            $thumb_src = '';
            if (is_array($image)) {
                $thumb_src = $image['sizes']['medium'] ?? $image['url'] ?? '';
            } elseif (is_numeric($image)) {
                $thumb_src = wp_get_attachment_image_src($image, 'medium')[0] ?? '';
            }
            ?>
            <button class="hph-hero__gallery-thumb <?php echo $index === 0 ? 'active' : ''; ?>"
                    data-index="<?php echo esc_attr($index); ?>"
                    onclick="event.stopPropagation(); setHeroImage('<?php echo esc_js($unique_id); ?>', <?php echo esc_js($index); ?>)">
                <img src="<?php echo esc_url(hph_add_fastly_optimization($thumb_src, 'thumbnail')); ?>"
                     alt="Photo <?php echo $index + 1; ?>"
                     loading="lazy">
            </button>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <!-- Enhanced Status Badges -->
    <?php
    // Get comprehensive badges from bridge system
    $hero_badges = [];
    if (function_exists('hpt_bridge_get_comprehensive_badges')) {
        $hero_badges = hpt_bridge_get_comprehensive_badges($listing_id, 2);
    }

    if (!empty($hero_badges)) :
        foreach ($hero_badges as $badge) :
            $badge_class = 'hph-hero__status';

            // Map badge variants to CSS classes
            switch ($badge['variant']) {
                case 'success':
                    $badge_class .= ' hph-hero__status--active';
                    break;
                case 'warning':
                    $badge_class .= ' hph-hero__status--pending';
                    break;
                case 'error':
                    $badge_class .= ' hph-hero__status--sold';
                    break;
                case 'info':
                    $badge_class .= ' hph-hero__status--info';
                    break;
                case 'primary':
                    $badge_class .= ' hph-hero__status--primary';
                    break;
                default:
                    $badge_class .= ' hph-hero__status--default';
            }
    ?>
    <div class="<?php echo esc_attr($badge_class); ?>">
        <?php echo esc_html(strtoupper($badge['text'])); ?>
    </div>
    <?php
        endforeach;
    endif;
    ?>
    
    <?php if ($show_gallery) : ?>
    <!-- Photo Counter & View Gallery Button - Mobile Optimized -->
    <div class="hph-hero__photo-nav hph-mobile-nav-optimized" id="normal-nav-<?php echo esc_attr($unique_id); ?>">
        <!-- Photo Counter -->
        <span class="hph-hero__photo-count">
            <i class="fas fa-camera"></i>
            <span id="current-photo"><?php echo $current_photo; ?></span> / <?php echo $total_photos; ?>
        </span>
        
        <!-- View Gallery Button -->
        <?php if ($total_photos > 0) : ?>
        <button class="hph-hero__gallery-btn" 
                id="gallery-toggle-btn-nav-<?php echo esc_attr($unique_id); ?>"
                onclick="toggleHeroGalleryMode('<?php echo esc_js($unique_id); ?>')">
            <i class="fas fa-images"></i>
            <span class="gallery-btn-text">View Gallery</span>
        </button>
        <?php endif; ?>
    </div>
    
    <!-- Centered Navigation Controls (Left & Right) - Mobile Safe Positioning -->
    <?php if ($show_gallery && $total_photos > 1) : ?>
    <button class="hph-hero__nav-btn hph-hero__nav-btn--prev hph-hero__nav-btn--left hph-mobile-nav-safe" 
            onclick="navigateHero('<?php echo esc_js($unique_id); ?>', -1)"
            aria-label="Previous Image">
        <i class="fas fa-chevron-left"></i>
    </button>
    <button class="hph-hero__nav-btn hph-hero__nav-btn--next hph-hero__nav-btn--right hph-mobile-nav-safe" 
            onclick="navigateHero('<?php echo esc_js($unique_id); ?>', 1)"
            aria-label="Next Image">
        <i class="fas fa-chevron-right"></i>
    </button>
    <?php endif; ?>
    <?php endif; ?>
    
    <!-- Main Content Overlay - Mobile Optimized -->
    <div class="hph-hero__overlay hph-mobile-content-optimized" id="hero-content-<?php echo esc_attr($unique_id); ?>">
        <div class="hph-container">
            <div class="hph-hero__content">
                <!-- Property Info -->
                <div class="hph-hero__info hph-mobile-info-compact">
                    <h1 class="hph-hero__title">
                        <?php echo esc_html($street_address ?: get_the_title($listing_id)); ?>
                    </h1>
                    <?php if ($city_state_zip) : ?>
                    <div class="hph-hero__location">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php echo esc_html($city_state_zip); ?>
                    </div>
                    <?php endif; ?>
                    
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
                                <span class="hph-hero__stat-label">ACRES</span>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                    </div>
                    
                    <!-- Action Buttons - Mobile Optimized -->
                    <div class="hph-hero__actions hph-mobile-actions-compact">
                        <button class="hph-hero__btn hph-hero__btn--primary"
                                data-modal-form="showing-request"
                                data-modal-title="Schedule a Showing"
                                data-modal-subtitle="Let us know when you'd like to view this property."
                                data-modal-size="lg">
                            <i class="fas fa-calendar-check"></i>
                            Schedule Showing
                        </button>
                        
                        <?php 
                        // Check if virtual tour exists
                        $has_virtual_tour = get_field('virtual_tour_url', $listing_id) || get_field('virtual_tour', $listing_id);
                        if ($has_virtual_tour) : ?>
                        <button class="hph-hero__btn hph-hero__btn--secondary" onclick="scrollToVirtualTour()">
                            <i class="fas fa-cube"></i>
                            <span class="hph-mobile-btn-text">Virtual Tour</span>
                        </button>
                        <?php endif; ?>
                        
                        <button class="hph-hero__btn hph-hero__btn--secondary" 
                                id="gallery-toggle-btn-action-<?php echo esc_attr($unique_id); ?>"
                                onclick="toggleHeroGalleryMode('<?php echo esc_js($unique_id); ?>')">
                            <i class="fas fa-images"></i>
                            <span class="gallery-btn-text hph-mobile-btn-text">View All Photos</span>
                        </button>
                        
                        <!-- Temporarily disabled PDF flyer button -->
                        <!--
                        <button class="hph-hero__btn hph-hero__btn--secondary" onclick="generatePropertyFlyer()">
                            <i class="fas fa-file-pdf"></i>
                            Download Flyer
                        </button>
                        -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($show_gallery) : ?>
<script>
// Hero Gallery Data for HPH Component System
window.heroGalleryData_<?php echo $unique_id; ?> = {
    images: [
        <?php
        $image_data = [];
        foreach ($gallery_images as $image) {
            $full_src = '';
            $image_alt = '';

            if (is_array($image)) {
                $full_src = hph_add_fastly_optimization($image['url'] ?? '', 'full');
                $image_alt = $image['alt'] ?? $image['title'] ?? '';
            } elseif (is_numeric($image)) {
                $attachment_url = wp_get_attachment_image_src($image, 'full')[0] ?? '';
                $full_src = hph_add_fastly_optimization($attachment_url, 'full');
                $image_alt = get_post_meta($image, '_wp_attachment_image_alt', true) ?: get_the_title($image);
            }

            $image_data[] = json_encode(['url' => $full_src, 'alt' => $image_alt]);
        }
        echo implode(',', $image_data);
        ?>
    ],
    totalImages: <?php echo count($gallery_images); ?>,
    currentIndex: 0,
    isGalleryMode: false,
    isContainMode: false,
    isTransitioning: false,
    isFitTransitioning: false
};

// Preload first few images for smoother navigation
function preloadHeroImages(galleryId) {
    const heroImages = document.querySelectorAll(`[data-component="hero-gallery"] .hph-hero__image`);
    const preloadCount = Math.min(3, heroImages.length);

    for (let i = 1; i < preloadCount; i++) {
        const img = heroImages[i];
        if (img && img.dataset.bg && !img.style.backgroundImage) {
            img.style.backgroundImage = `url('${img.dataset.bg}')`;
            img.classList.remove('lazy-bg');
        }
    }
}

// Initialize preloading
preloadHeroImages('<?php echo $unique_id; ?>');

// Toggle between normal hero and gallery mode
function toggleHeroGalleryMode(galleryId) {
    const heroSection = document.querySelector('[data-component="hero-gallery"] .hph-hero__gallery').closest('.hph-hero');
    const toggleBtnNav = document.getElementById('gallery-toggle-btn-nav-' + galleryId);
    const toggleBtnAction = document.getElementById('gallery-toggle-btn-action-' + galleryId);
    const data = window.heroGalleryData_<?php echo $unique_id; ?>;

    if (!heroSection || !data) return;

    // Toggle the gallery mode class on the hero section
    heroSection.classList.toggle('hph-hero--gallery-mode');

    // Update gallery mode state in data
    data.isGalleryMode = heroSection.classList.contains('hph-hero--gallery-mode');

    // When exiting gallery mode, always revert to cover (fill) mode
    if (!data.isGalleryMode && data.isContainMode) {
        heroSection.classList.remove('hph-hero--contain-mode');
        data.isContainMode = false;

        // Reset button text and icon
        const fillToggleButton = document.getElementById('image-fill-toggle-' + galleryId);
        const buttonText = fillToggleButton?.querySelector('.fill-mode-text');
        const buttonIcon = fillToggleButton?.querySelector('i');

        if (buttonText) buttonText.textContent = 'Fit Image';
        if (buttonIcon) buttonIcon.className = 'fas fa-expand-arrows-alt';
    }

    // Update button text based on current state
    const newText = data.isGalleryMode ? 'Close Gallery' : 'View Gallery';
    const newActionText = data.isGalleryMode ? 'Close Gallery' : 'View All Photos';

    if (toggleBtnNav) {
        const btnText = toggleBtnNav.querySelector('.gallery-btn-text');
        if (btnText) btnText.textContent = newText;
    }

    if (toggleBtnAction) {
        const btnText = toggleBtnAction.querySelector('.gallery-btn-text');
        if (btnText) btnText.textContent = newActionText;
    }

    // Auto-exit gallery mode on scroll
    if (data.isGalleryMode) {
        initScrollExit(galleryId);
    }
}

// Initialize scroll exit functionality
function initScrollExit(galleryId) {
    const data = window.heroGalleryData_<?php echo $unique_id; ?>;

    const handleScroll = () => {
        if (!data || !data.isGalleryMode) return;

        const heroSection = document.querySelector('[data-component="hero-gallery"] .hph-hero__gallery').closest('.hph-hero');
        if (!heroSection) return;

        const heroRect = heroSection.getBoundingClientRect();
        // Exit gallery mode immediately when user scrolls past hero section
        if (heroRect.top < -20) {
            // Revert to cover mode before exiting gallery
            if (data.isContainMode) {
                heroSection.classList.remove('hph-hero--contain-mode');
                data.isContainMode = false;
            }
            toggleHeroGalleryMode(galleryId);
            window.removeEventListener('scroll', handleScroll);
        }
    };

    window.addEventListener('scroll', handleScroll, { passive: true });
}

// Navigate hero gallery with direction (-1 for previous, 1 for next)
function navigateHero(galleryId, direction) {
    const data = window.heroGalleryData_<?php echo $unique_id; ?>;
    if (!data) return;

    let newIndex = data.currentIndex + direction;

    // Handle wrap-around
    if (newIndex >= data.totalImages) {
        newIndex = 0;
    } else if (newIndex < 0) {
        newIndex = data.totalImages - 1;
    }

    setHeroImageWithCarousel(galleryId, newIndex, direction);
}

// Smooth fade transition between images
function setHeroImageWithCarousel(galleryId, index, direction = 0) {
    const data = window.heroGalleryData_<?php echo $unique_id; ?>;
    if (!data || !data.images[index]) return;

    // Prevent overlapping transitions
    if (data.isTransitioning) return;

    const heroImages = document.querySelectorAll('[data-component="hero-gallery"] .hph-hero__image');
    const currentImg = heroImages[data.currentIndex];
    const nextImg = heroImages[index];

    // If this is the same image, do nothing
    if (data.currentIndex === index) return;

    if (currentImg && nextImg) {
        data.isTransitioning = true;

        // Start fade out of current image
        currentImg.classList.add('fade-out');

        // After fade out completes, switch to new image
        setTimeout(() => {
            // Clear all active states
            heroImages.forEach(img => {
                img.classList.remove('active', 'fade-out');
            });

            // Activate new image
            nextImg.classList.add('active');

            data.isTransitioning = false;
        }, 300); // Half the transition time for smooth crossfade
    } else {
        // Direct switch without animation
        heroImages.forEach((img, i) => {
            img.classList.remove('fade-out');
            img.classList.toggle('active', i === index);
        });
    }

    data.currentIndex = index;

    // Load current image if lazy
    const activeImg = heroImages[index];
    if (activeImg && activeImg.dataset.bg && !activeImg.style.backgroundImage) {
        activeImg.style.backgroundImage = `url('${activeImg.dataset.bg}')`;
        activeImg.classList.remove('lazy-bg');
    }

    // Update fit image if in contain mode
    updateFitImage(galleryId);

    // Preload adjacent images
    preloadAdjacentImages(index, data.totalImages);

    // Update thumbnails
    updateThumbnails(index);

    // Update counters
    updateCounters(index, data.totalImages);
}

// Fallback for direct image setting (thumbnail clicks)
function setHeroImage(galleryId, index) {
    setHeroImageWithCarousel(galleryId, index, 0);
}

// Update fit image for contain mode with fade transition
function updateFitImage(galleryId) {
    const data = window.heroGalleryData_<?php echo $unique_id; ?>;
    if (!data || !data.isContainMode) return;

    const fitImg = document.getElementById('fit-image-' + galleryId);
    const currentImageData = data.images[data.currentIndex];

    if (fitImg && currentImageData) {
        // If image is already correct, don't fade
        if (fitImg.src === currentImageData.url) return;

        // Prevent overlapping transitions
        if (data.isFitTransitioning) return;
        data.isFitTransitioning = true;

        // Start fade out
        fitImg.classList.add('fade-out');

        // After fade out, switch image and fade in
        setTimeout(() => {
            fitImg.src = currentImageData.url;
            fitImg.alt = currentImageData.alt || '';

            // Update viewport-based sizing
            updateFitImageSize(fitImg);

            // Remove fade-out class to fade back in
            fitImg.classList.remove('fade-out');

            data.isFitTransitioning = false;
        }, 300); // Half the transition time for smooth crossfade
    }
}

// Update fit image dimensions based on viewport
function updateFitImageSize(fitImg) {
    if (!fitImg) return;

    const headerHeight = document.querySelector('header')?.offsetHeight || 80;
    const galleryStripHeight = 140; // 120px + 20px margin
    const margin = 64; // 4rem total margin

    const maxWidth = window.innerWidth - margin;
    const maxHeight = window.innerHeight - headerHeight - galleryStripHeight - margin;

    fitImg.style.maxWidth = maxWidth + 'px';
    fitImg.style.maxHeight = maxHeight + 'px';
}

// Helper functions
function preloadAdjacentImages(currentIndex, totalImages) {
    const heroImages = document.querySelectorAll('[data-component="hero-gallery"] .hph-hero__image');
    const nextIndex = (currentIndex + 1) % totalImages;
    const prevIndex = currentIndex === 0 ? totalImages - 1 : currentIndex - 1;

    [nextIndex, prevIndex].forEach(idx => {
        const img = heroImages[idx];
        if (img && img.dataset.bg && !img.style.backgroundImage) {
            img.style.backgroundImage = `url('${img.dataset.bg}')`;
            img.classList.remove('lazy-bg');
        }
    });
}

function updateThumbnails(currentIndex) {
    const thumbnails = document.querySelectorAll('[data-component="hero-gallery"] .hph-hero__gallery-thumb');
    thumbnails.forEach((thumb, i) => {
        thumb.classList.toggle('active', i === currentIndex);
    });
}

function updateCounters(currentIndex, totalImages) {
    const counter = document.getElementById('hero-counter-<?php echo $unique_id; ?>');
    const normalCounter = document.getElementById('current-photo');

    if (counter) {
        counter.textContent = `${currentIndex + 1} of ${totalImages}`;
    }
    if (normalCounter) {
        normalCounter.textContent = currentIndex + 1;
    }
}

// Touch/Swipe Support for Mobile Gallery Navigation
function initHeroTouchSupport(galleryId) {
    const heroGallery = document.querySelector(`[data-component="hero-gallery"] .hph-hero__gallery`);
    if (!heroGallery) return;

    let startX = 0;
    let startY = 0;
    let distX = 0;
    let distY = 0;
    let startTime = 0;
    const threshold = 100; // Minimum distance for swipe
    const restraint = 150; // Maximum distance perpendicular to swipe direction
    const allowedTime = 500; // Maximum time allowed to travel that distance

    heroGallery.addEventListener('touchstart', function(e) {
        const touchobj = e.changedTouches[0];
        startX = touchobj.pageX;
        startY = touchobj.pageY;
        startTime = new Date().getTime();
        e.preventDefault();
    });

    heroGallery.addEventListener('touchmove', function(e) {
        e.preventDefault(); // Prevent default behavior (scrolling)
    });

    heroGallery.addEventListener('touchend', function(e) {
        const touchobj = e.changedTouches[0];
        distX = touchobj.pageX - startX;
        distY = touchobj.pageY - startY;
        const elapsedTime = new Date().getTime() - startTime;

        // First check: was it a swipe at all? Dist and time thresholds must be met
        if (elapsedTime <= allowedTime && Math.abs(distX) >= threshold && Math.abs(distY) <= restraint) {
            // 2nd check: if the swipe distance was enough, check direction and call appropriate function
            if (distX > 0) {
                // Swipe right = previous
                navigateHero(galleryId, -1);
            } else {
                // Swipe left = next
                navigateHero(galleryId, 1);
            }
        }
    });

    // Also add keyboard support
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') {
            navigateHero(galleryId, -1);
        } else if (e.key === 'ArrowRight') {
            navigateHero(galleryId, 1);
        } else if (e.key === 'Escape') {
            // Exit gallery mode if in gallery mode
            const data = window.heroGalleryData_<?php echo $unique_id; ?>;
            if (data && data.isGalleryMode) {
                toggleHeroGalleryMode(galleryId);
            }
        }
    });
}

// Initialize touch support when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initHeroTouchSupport('<?php echo $unique_id; ?>');
    initPinchGesture('<?php echo $unique_id; ?>');
});

// Function to scroll to virtual tour section
function scrollToVirtualTour() {
    const virtualTourSection = document.querySelector('.virtual-tour-section, #virtual-tour, [data-virtual-tour]');
    if (virtualTourSection) {
        virtualTourSection.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    } else {
        console.log('Virtual tour section not found');
    }
}

// Image Fill Mode Toggle - Cover vs Fit with dynamic sizing
function toggleImageFillMode(galleryId) {
    const data = window.heroGalleryData_<?php echo $unique_id; ?>;
    if (!data) return;

    const heroSection = document.querySelector('[data-component="hero-gallery"] .hph-hero__gallery').closest('.hph-hero');
    const toggleButton = document.getElementById('image-fill-toggle-' + galleryId);
    const buttonText = toggleButton?.querySelector('.fill-mode-text');
    const buttonIcon = toggleButton?.querySelector('i');

    if (!heroSection) return;

    // Toggle contain mode class
    heroSection.classList.toggle('hph-hero--contain-mode');

    // Update state
    data.isContainMode = heroSection.classList.contains('hph-hero--contain-mode');

    // Update button text and icon
    if (data.isContainMode) {
        if (buttonText) buttonText.textContent = 'Fill Screen';
        if (buttonIcon) {
            buttonIcon.className = 'fas fa-compress-arrows-alt';
        }
        // Initialize fit image
        updateFitImage(galleryId);
        // Add resize listener for viewport changes
        window.addEventListener('resize', () => updateFitImageSize(document.getElementById('fit-image-' + galleryId)));
    } else {
        if (buttonText) buttonText.textContent = 'Fit Image';
        if (buttonIcon) {
            buttonIcon.className = 'fas fa-expand-arrows-alt';
        }
        // Remove resize listener
        window.removeEventListener('resize', () => updateFitImageSize(document.getElementById('fit-image-' + galleryId)));
    }
}

// Update blurred background image for contain mode
function updateBlurredBackground(galleryId) {
    // Background now inherits automatically via CSS inherit property
    // No JavaScript needed - the ::before pseudo-element inherits the background-image
}

// Pinch gesture support for fill mode toggle
function initPinchGesture(galleryId) {
    const heroGallery = document.querySelector('[data-component="hero-gallery"] .hph-hero__gallery');
    if (!heroGallery) return;

    let initialDistance = 0;
    let isPinching = false;

    function getDistance(touches) {
        const dx = touches[0].pageX - touches[1].pageX;
        const dy = touches[0].pageY - touches[1].pageY;
        return Math.sqrt(dx * dx + dy * dy);
    }

    heroGallery.addEventListener('touchstart', function(e) {
        if (e.touches.length === 2) {
            isPinching = true;
            initialDistance = getDistance(e.touches);
            e.preventDefault();
        }
    });

    heroGallery.addEventListener('touchmove', function(e) {
        if (isPinching && e.touches.length === 2) {
            e.preventDefault();
        }
    });

    heroGallery.addEventListener('touchend', function(e) {
        if (isPinching) {
            if (e.touches.length < 2) {
                const data = window.heroGalleryData_<?php echo $unique_id; ?>;
                if (data && data.isGalleryMode) {
                    // If pinch gesture detected, toggle fill mode
                    const currentDistance = initialDistance;
                    if (Math.abs(currentDistance - initialDistance) > 50) {
                        toggleImageFillMode(galleryId);
                    }
                }
            }
        }
    });
}

// Enhanced mobile touch feedback system
function initMobileTouchFeedback() {
    // Add touch feedback to all interactive elements
    const interactiveElements = document.querySelectorAll(
        '.hph-hero__nav-btn.hph-mobile-nav-safe, ' +
        '.hph-hero__gallery-btn, ' +
        '.hph-hero__photo-nav .hph-hero__gallery-btn, ' +
        '.hph-hero__btn, ' +
        '.hph-hero__gallery-thumb, ' +
        '.hph-hero__image-fill-toggle.hph-mobile-fill-toggle'
    );

    interactiveElements.forEach(element => {
        // Clear any stuck transforms on touch start
        element.addEventListener('touchstart', function(e) {
            // Ensure transform is reset
            if (element.classList.contains('hph-mobile-nav-safe')) {
                element.style.transform = 'translateY(-50%)';
            } else {
                element.style.transform = 'none';
            }
        }, { passive: true });

        // Ensure transforms are cleared on touch end
        element.addEventListener('touchend', function(e) {
            // Small delay to ensure visual feedback, then reset
            setTimeout(() => {
                if (element.classList.contains('hph-mobile-nav-safe')) {
                    element.style.transform = 'translateY(-50%)';
                } else {
                    element.style.transform = 'none';
                }
            }, 100);
        }, { passive: true });

        // Also handle touch cancel events
        element.addEventListener('touchcancel', function(e) {
            if (element.classList.contains('hph-mobile-nav-safe')) {
                element.style.transform = 'translateY(-50%)';
            } else {
                element.style.transform = 'none';
            }
        }, { passive: true });
    });
}

// Initialize mobile touch feedback when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initHeroTouchSupport('<?php echo $unique_id; ?>');
    initPinchGesture('<?php echo $unique_id; ?>');
    
    // Add mobile-specific improvements
    if ('ontouchstart' in window || navigator.maxTouchPoints > 0) {
        initMobileTouchFeedback();
        
        // Prevent context menu on long press for gallery elements
        const galleryElements = document.querySelectorAll('.hph-hero__gallery, .hph-hero__image, .hph-hero__nav-btn');
        galleryElements.forEach(element => {
            element.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                return false;
            });
        });
        
        // Improve gallery mode touch handling
        const heroGallery = document.querySelector('[data-component="hero-gallery"] .hph-hero__gallery');
        if (heroGallery) {
            // Prevent default zoom behavior
            heroGallery.addEventListener('gesturestart', function(e) {
                e.preventDefault();
            });
            
            heroGallery.addEventListener('gesturechange', function(e) {
                e.preventDefault();
            });
            
            heroGallery.addEventListener('gestureend', function(e) {
                e.preventDefault();
            });
        }
    }
});
</script>
<?php endif; ?>
