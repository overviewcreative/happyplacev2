<?php
/**
 * Modern Listing Hero Section
 * File: template-parts/listing/hero.php
 * 
 * Clean, modern hero with overlay information
 * Matches contemporary real estate site design
 * Uses bridge functions <section class="hph-hero hph-hero-lg hph-hero--modern <?php echo $show_gallery ? 'hph-hero--gallery' : 'hph-hero--simple'; ?>" 
         data-component="<?php echo $show_gallery ? 'hero-gallery' : 'hero-simple'; ?>" 
         data-listing-id="<?php echo esc_attr($listing_id); ?>">
    
    <?php if ($show_gallery) : ?>
    <!-- Gallery Background - Clickable for Gallery Mode -->
    <div class="hph-hero__gallery hph-cursor-pointer" 
         onclick="toggleHeroGalleryMode('<?php echo esc_js($unique_id); ?>')">
    <?php else : ?>
    <!-- Simple Background Image -->
    <div class="hph-hero__background">
    <?php endif; ?>a access
 * 
 * @package HappyPlaceTheme
 */

$listing_id = $args['listing_id'] ?? get_the_ID();

// Check if gallery should be shown based on args
$show_gallery = $args['show_gallery'] ?? true;

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
                         style="background-image: url('<?php echo esc_url($image['url']); ?>');"
                     <?php else : ?>
                         data-bg="<?php echo esc_url($image['url']); ?>"
                     <?php endif; ?>>
                </div>
                <?php endforeach; ?>
            <?php else : ?>
                <!-- Show only first image for simple hero -->
                <?php $first_image = $gallery_images[0]; ?>
                <div class="hph-hero__image active" 
                     style="background-image: url('<?php echo esc_url($first_image['url']); ?>');">
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
                <img src="<?php echo esc_url($thumb_src); ?>" 
                     alt="Photo <?php echo $index + 1; ?>"
                     loading="lazy">
            </button>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <!-- Status Badge -->
    <?php if ($listing_status === 'coming_soon') : ?>
    <div class="hph-hero__status">
        COMING SOON
    </div>
        <?php elseif ($listing_status === 'active') : ?>
    <div class="hph-hero__status hph-hero__status--active">
        ACTIVE
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
    
    <?php if ($show_gallery) : ?>
    <!-- Photo Counter & Navigation (normal mode) - Single Row Layout -->
    <div class="hph-hero__photo-nav" id="normal-nav-<?php echo esc_attr($unique_id); ?>">
        <!-- Photo Counter -->
        <span class="hph-hero__photo-count">
            <i class="fas fa-camera"></i>
            <span id="current-photo"><?php echo $current_photo; ?></span> / <?php echo $total_photos; ?>
        </span>
        
        <!-- View Gallery Button -->
        <?php if ($total_photos > 0) : ?>
        <button class="hph-hero__gallery-btn" 
                onclick="toggleHeroGalleryMode('<?php echo esc_js($unique_id); ?>')">
            <i class="fas fa-images"></i>
            View Gallery
        </button>
        <?php endif; ?>
        
        <!-- Navigation Controls -->
        <?php if ($total_photos > 1) : ?>
        <div class="hph-hero__nav-controls">
            <button class="hph-hero__nav-btn hph-hero__nav-btn--prev" onclick="navigateHero('<?php echo esc_js($unique_id); ?>', -1)">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="hph-hero__nav-btn hph-hero__nav-btn--next" onclick="navigateHero('<?php echo esc_js($unique_id); ?>', 1)">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <!-- Main Content Overlay -->
    <div class="hph-hero__overlay" id="hero-content-<?php echo esc_attr($unique_id); ?>">
        <div class="hph-container">
            <div class="hph-hero__content">
                <!-- Property Info -->
                <div class="hph-hero__info">
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
                    
                    <!-- Action Buttons -->
                    <div class="hph-hero__actions">
                        <button class="hph-hero__btn hph-hero__btn--primary" onclick="openContactAgentModal()">
                            <i class="fas fa-calendar-check"></i>
                            Schedule Showing
                        </button>
                        
                        <?php 
                        // Check if virtual tour exists
                        $has_virtual_tour = get_field('virtual_tour_url', $listing_id) || get_field('virtual_tour', $listing_id);
                        if ($has_virtual_tour) : ?>
                        <button class="hph-hero__btn hph-hero__btn--secondary" onclick="scrollToVirtualTour()">
                            <i class="fas fa-cube"></i>
                            Virtual Tour
                        </button>
                        <?php endif; ?>
                        
                        <button class="hph-hero__btn hph-hero__btn--secondary" 
                                id="gallery-toggle-btn-<?php echo esc_attr($unique_id); ?>"
                                onclick="toggleHeroGalleryMode('<?php echo esc_js($unique_id); ?>')">
                            <i class="fas fa-images"></i>
                            <span class="gallery-btn-text">View All Photos</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($show_gallery) : ?>
<script>
// Hero gallery data and state
window.heroGalleryData = window.heroGalleryData || {};
window.heroGalleryData['<?php echo $unique_id; ?>'] = {
    images: [
        <?php
        $image_data = [];
        foreach ($gallery_images as $image) {
            $full_src = '';
            $image_alt = '';
            
            if (is_array($image)) {
                $full_src = $image['url'] ?? '';
                $image_alt = $image['alt'] ?? $image['title'] ?? '';
            } elseif (is_numeric($image)) {
                $full_src = wp_get_attachment_image_src($image, 'full')[0] ?? '';
                $image_alt = get_post_meta($image, '_wp_attachment_image_alt', true) ?: get_the_title($image);
            }
            
            $image_data[] = json_encode(['url' => $full_src, 'alt' => $image_alt]);
        }
        echo implode(',', $image_data);
        ?>
    ],
    currentIndex: 0,
    isGalleryMode: false
};

// Preload first few images for smoother navigation
function preloadHeroImages(galleryId) {
    const heroImages = document.querySelectorAll(`[data-component="hero-gallery"][data-listing-id="${galleryId}"] .hph-hero__image`);
    const preloadCount = Math.min(3, heroImages.length); // Preload first 3 images
    
    for (let i = 1; i < preloadCount; i++) { // Start from 1 since first image is already loaded
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
    const heroSection = document.querySelector('[data-component="hero-gallery"][data-listing-id] .hph-hero__gallery').closest('.hph-hero');
    const toggleBtn = document.getElementById('gallery-toggle-btn-' + galleryId);
    const btnText = toggleBtn.querySelector('.gallery-btn-text');
    
    if (!heroSection) return;
    
    // Toggle the gallery mode class on the hero section
    heroSection.classList.toggle('hph-hero--gallery-mode');
    
    // Update button text based on current state
    if (heroSection.classList.contains('hph-hero--gallery-mode')) {
        btnText.textContent = 'Close Gallery';
    } else {
        btnText.textContent = 'View All Photos';
    }
}

// Navigate hero images
function navigateHero(galleryId, direction) {
    const data = window.heroGalleryData[galleryId];
    if (!data) return;
    
    let newIndex = data.currentIndex + direction;
    
    if (newIndex < 0) newIndex = data.images.length - 1;
    if (newIndex >= data.images.length) newIndex = 0;
    
    setHeroImage(galleryId, newIndex);
}

// Set hero image by index
function setHeroImage(galleryId, index) {
    const data = window.heroGalleryData[galleryId];
    const heroImages = document.querySelectorAll(`[data-component="hero-gallery"][data-listing-id] .hph-hero__image`);
    const thumbs = document.querySelectorAll('#gallery-overlay-' + galleryId + ' .hph-hero__gallery-thumb');
    const counter = document.getElementById('hero-counter-' + galleryId);
    const normalCounter = document.getElementById('current-photo');
    
    if (!data) return;
    
    // Update current index
    data.currentIndex = parseInt(index);
    
    // Update hero images
    heroImages.forEach((img, i) => {
        if (i === data.currentIndex) {
            img.classList.add('active');
            // Load lazy image if not already loaded
            if (img.dataset.bg && !img.style.backgroundImage) {
                img.style.backgroundImage = `url('${img.dataset.bg}')`;
                img.classList.remove('lazy-bg');
            }
        } else {
            img.classList.remove('active');
        }
    });
    
    // Update thumbnails
    thumbs.forEach((thumb, i) => {
        if (i === data.currentIndex) {
            thumb.classList.add('active');
        } else {
            thumb.classList.remove('active');
        }
    });
    
    // Update counters
    if (counter) {
        counter.textContent = data.currentIndex + 1;
    }
    if (normalCounter) {
        normalCounter.textContent = data.currentIndex + 1;
    }
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    const galleryModeActive = document.body.classList.contains('hero-gallery-mode');
    if (!galleryModeActive) return;
    
    // Find active gallery
    const activeGalleryId = Object.keys(window.heroGalleryData).find(id => 
        window.heroGalleryData[id].isGalleryMode
    );
    
    if (!activeGalleryId) return;
    
    switch(e.key) {
        case 'Escape':
            toggleHeroGalleryMode(activeGalleryId);
            break;
        case 'ArrowLeft':
            navigateHero(activeGalleryId, -1);
            break;
        case 'ArrowRight':
            navigateHero(activeGalleryId, 1);
            break;
    }
});

function openContactAgentModal() {
    // Scroll to the showing request form in the sidebar
    const showingForm = document.getElementById('schedule-showing-form');
    if (showingForm) {
        showingForm.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start',
            inline: 'nearest'
        });
        
        // Add a brief highlight effect to draw attention
        setTimeout(() => {
            showingForm.style.boxShadow = '0 0 20px rgba(var(--hph-primary-rgb), 0.3)';
            setTimeout(() => {
                showingForm.style.boxShadow = '';
            }, 2000);
        }, 500);
    } else {
        // Fallback: scroll to contact form in sidebar
        const contactForm = document.querySelector('.hph-contact-form, #contact-form, .contact-form');
        if (contactForm) {
            contactForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            console.log('Showing form not found');
        }
    }
}

function scrollToVirtualTour() {
    const virtualTourSection = document.getElementById('virtual-tour');
    if (virtualTourSection) {
        virtualTourSection.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start',
            inline: 'nearest'
        });
        
        // Add a slight delay then focus the virtual tour for better UX
        setTimeout(() => {
            const iframe = virtualTourSection.querySelector('iframe');
            if (iframe) {
                iframe.focus();
            }
        }, 1000);
    } else {
        console.log('Virtual tour section not found');
    }
}

function openGallery() {
    // Open full gallery modal
    if (typeof openGalleryModal === 'function') {
        openGalleryModal();
    }
}


// Auto-rotate slides
if (totalSlides > 1) {
    setInterval(() => {
        nextSlide();
    }, 5000);
}

// Lazy loading for background images
document.addEventListener('DOMContentLoaded', function() {
    // Intersection Observer for lazy loading background images
    const bgLazyObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const element = entry.target;
                const bgUrl = element.dataset.bg;
                if (bgUrl) {
                    element.style.backgroundImage = `url('${bgUrl}')`;
                    element.classList.remove('lazy-bg');
                }
                bgLazyObserver.unobserve(element);
            }
        });
    }, {
        rootMargin: '50px 0px',
        threshold: 0.1
    });

    // Observe all lazy background elements
    document.querySelectorAll('.lazy-bg').forEach(el => {
        bgLazyObserver.observe(el);
    });
});
</script>
<?php endif; ?>
