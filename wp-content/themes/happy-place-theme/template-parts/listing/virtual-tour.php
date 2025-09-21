<?php
/**
 * Virtual Tour Section Template Part
 * File: template-parts/listing/virtual-tour.php
 * 
 * Full-width virtual tour section with embedded iframe
 * Matches hero section styling approach with modern design
 * 
 * @package HappyPlaceTheme
 */

$listing_id = $args['listing_id'] ?? get_the_ID();

// Get virtual tour URL using bridge function with fallback
$virtual_tour_url = null;
if (function_exists('hpt_get_listing_virtual_tour_url')) {
    try {
        $virtual_tour_url = hpt_get_listing_virtual_tour_url($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_virtual_tour_url failed: ' . $e->getMessage());
    }
}

// Fallback to direct field access if bridge not available
if (!$virtual_tour_url) {
    $virtual_tour_url = get_field('virtual_tour_url', $listing_id) ?: get_field('virtual_tour', $listing_id) ?: null;
}

// Early return if no virtual tour URL
if (!$virtual_tour_url) {
    return;
}

// Get property title for section header
$property_title = get_the_title($listing_id);
$property_address = get_field('address', $listing_id) ?: '';

// Sanitize and prepare URL for embedding
$embed_url = esc_url($virtual_tour_url);

// Check if it's a common virtual tour platform and adjust URL for embedding
if (strpos($virtual_tour_url, 'matterport.com') !== false) {
    // Matterport embedding adjustments
    if (strpos($virtual_tour_url, '/show/') !== false) {
        $embed_url = str_replace('/show/', '/showcase/', $virtual_tour_url);
    }
} elseif (strpos($virtual_tour_url, 'youtube.com') !== false || strpos($virtual_tour_url, 'youtu.be') !== false) {
    // YouTube embedding adjustments
    if (strpos($virtual_tour_url, 'watch?v=') !== false) {
        $video_id = substr($virtual_tour_url, strpos($virtual_tour_url, 'v=') + 2);
        $embed_url = "https://www.youtube.com/embed/{$video_id}?rel=0&showinfo=0&modestbranding=1";
    } elseif (strpos($virtual_tour_url, 'youtu.be/') !== false) {
        $video_id = substr($virtual_tour_url, strrpos($virtual_tour_url, '/') + 1);
        $embed_url = "https://www.youtube.com/embed/{$video_id}?rel=0&showinfo=0&modestbranding=1";
    }
}
?>

<section id="virtual-tour" class="hph-virtual-tour-section">
    <div class="hph-virtual-tour-container">
        
        <!-- Section Header -->
        <div class="hph-virtual-tour-header">
            <div class="hph-container">
                <div class="hph-virtual-tour-title-area">
                    <h2 class="hph-virtual-tour-title">
                        <i class="fas fa-cube hph-mr-3"></i>
                        Virtual Tour
                    </h2>
                    <?php if ($property_title || $property_address) : ?>
                        <p class="hph-virtual-tour-subtitle">
                            <?php 
                            if ($property_address) {
                                echo esc_html($property_address);
                            } elseif ($property_title) {
                                echo esc_html($property_title);
                            }
                            ?>
                        </p>
                    <?php endif; ?>
                </div>
                
                <!-- Tour Controls -->
                <div class="hph-virtual-tour-controls">
                    <button type="button" 
                            class="hph-tour-fullscreen-btn"
                            onclick="toggleTourFullscreen()"
                            title="Toggle Fullscreen">
                        <i class="fas fa-expand"></i>
                        <span class="hph-sr-only">Toggle Fullscreen</span>
                    </button>
                    
                    <a href="<?php echo esc_url($virtual_tour_url); ?>" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="hph-tour-external-btn"
                       title="Open in New Window">
                        <i class="fas fa-external-link-alt"></i>
                        <span class="hph-sr-only">Open in New Window</span>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Virtual Tour Embed -->
        <div class="hph-virtual-tour-embed" id="virtual-tour-embed">
            <iframe src="<?php echo esc_url($embed_url); ?>"
                    width="100%" 
                    height="600"
                    frameborder="0" 
                    allowfullscreen
                    allow="xr-spatial-tracking; gyroscope; accelerometer"
                    loading="lazy"
                    title="Virtual Tour - <?php echo esc_attr($property_title ?: 'Property Tour'); ?>">
            </iframe>
            
            <!-- Loading Overlay -->
            <div class="hph-tour-loading" id="tour-loading">
                <div class="hph-tour-loading-content">
                    <div class="hph-tour-loading-spinner"></div>
                    <p class="hph-tour-loading-text">Loading Virtual Tour...</p>
                </div>
            </div>
        </div>
        
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Hide loading overlay when iframe loads
    const iframe = document.querySelector('#virtual-tour-embed iframe');
    const loading = document.getElementById('tour-loading');
    
    if (iframe && loading) {
        iframe.addEventListener('load', function() {
            loading.style.display = 'none';
        });
        
        // Fallback: hide loading after 10 seconds
        setTimeout(function() {
            if (loading) {
                loading.style.display = 'none';
            }
        }, 10000);
    }
});

// Fullscreen toggle function
function toggleTourFullscreen() {
    const embedContainer = document.getElementById('virtual-tour-embed');
    const iframe = embedContainer.querySelector('iframe');
    
    if (!document.fullscreenElement) {
        // Enter fullscreen
        if (embedContainer.requestFullscreen) {
            embedContainer.requestFullscreen();
        } else if (embedContainer.webkitRequestFullscreen) {
            embedContainer.webkitRequestFullscreen();
        } else if (embedContainer.mozRequestFullScreen) {
            embedContainer.mozRequestFullScreen();
        } else if (embedContainer.msRequestFullscreen) {
            embedContainer.msRequestFullscreen();
        }
        
        // Adjust iframe for fullscreen
        iframe.style.height = '100vh';
        embedContainer.classList.add('hph-tour-fullscreen');
        
    } else {
        // Exit fullscreen
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
        
        // Reset iframe height
        iframe.style.height = '600px';
        embedContainer.classList.remove('hph-tour-fullscreen');
    }
}

// Listen for fullscreen changes
document.addEventListener('fullscreenchange', function() {
    const embedContainer = document.getElementById('virtual-tour-embed');
    const iframe = embedContainer.querySelector('iframe');
    
    if (!document.fullscreenElement) {
        iframe.style.height = '600px';
        embedContainer.classList.remove('hph-tour-fullscreen');
    }
});
</script>
