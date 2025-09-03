<?php
/**
 * Virtual Tour Template Part
 * File: template-parts/listing/virtual-tour.php
 * 
 * Displays virtual tour iframe or video tour using bridge functions
 * Uses HPH framework utilities and CSS variables
 * 
 * @package HappyPlaceTheme
 */

$listing_id = $args['listing_id'] ?? get_the_ID();

// Get virtual tour data using bridge functions with fallbacks
$virtual_tour_url = null;
if (function_exists('hpt_get_listing_virtual_tour_url')) {
    try {
        $virtual_tour_url = hpt_get_listing_virtual_tour_url($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_virtual_tour_url failed: ' . $e->getMessage());
    }
}
if (!$virtual_tour_url) {
    $virtual_tour_url = get_field('virtual_tour_url', $listing_id);
}

$video_tour_url = null;
if (function_exists('hpt_get_listing_video')) {
    try {
        $video_tour_url = hpt_get_listing_video($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_video failed: ' . $e->getMessage());
    }
}
if (!$video_tour_url) {
    $video_tour_url = get_field('video_tour_url', $listing_id) ?: get_field('video_url', $listing_id);
}

$floor_plan_images = null;
if (function_exists('hpt_get_listing_floor_plans')) {
    try {
        $floor_plan_images = hpt_get_listing_floor_plans($listing_id);
    } catch (Exception $e) {
        error_log('Bridge function hpt_get_listing_floor_plans failed: ' . $e->getMessage());
    }
}
if (empty($floor_plan_images)) {
    $floor_plan_images = get_field('floor_plans', $listing_id) ?: [];
}

if (!$virtual_tour_url && !$video_tour_url && empty($floor_plan_images)) {
    return;
}
?>

<section class="hph-virtual-tour hph-py-3xl hph-bg-gray-50">
    <div class="hph-container">
        
        <div class="hph-section__header hph-text-center hph-mb-xl">
            <h2 class="hph-section__title hph-text-3xl hph-font-bold hph-mb-sm">
                Virtual Tour & Floor Plans
            </h2>
            <p class="hph-section__subtitle hph-text-lg hph-text-gray-600">
                Explore the property virtually
            </p>
        </div>
        
        <!-- Tab Navigation -->
        <div class="hph-tour-tabs hph-flex hph-justify-center hph-gap-md hph-mb-xl">
            <?php if ($virtual_tour_url) : ?>
            <button class="hph-tab-btn hph-px-lg hph-py-sm hph-bg-white hph-rounded-md hph-font-medium hph-text-gray-700 hover:hph-bg-primary hover:hph-text-white hph-transition-all hph-active"
                    data-tab="virtual-tour">
                <i class="fas fa-vr-cardboard hph-mr-sm"></i>
                Virtual Tour
            </button>
            <?php endif; ?>
            
            <?php if ($video_tour_url) : ?>
            <button class="hph-tab-btn hph-px-lg hph-py-sm hph-bg-white hph-rounded-md hph-font-medium hph-text-gray-700 hover:hph-bg-primary hover:hph-text-white hph-transition-all"
                    data-tab="video-tour">
                <i class="fas fa-video hph-mr-sm"></i>
                Video Tour
            </button>
            <?php endif; ?>
            
            <?php if (!empty($floor_plan_images)) : ?>
            <button class="hph-tab-btn hph-px-lg hph-py-sm hph-bg-white hph-rounded-md hph-font-medium hph-text-gray-700 hover:hph-bg-primary hover:hph-text-white hph-transition-all"
                    data-tab="floor-plans">
                <i class="fas fa-blueprint hph-mr-sm"></i>
                Floor Plans
            </button>
            <?php endif; ?>
        </div>
        
        <!-- Tab Content -->
        <div class="hph-tour-content">
            
            <?php if ($virtual_tour_url) : ?>
            <!-- Virtual Tour Content -->
            <div class="hph-tab-content hph-active" data-content="virtual-tour">
                <div class="hph-tour-iframe-wrapper hph-relative hph-bg-white hph-rounded-lg hph-shadow-lg hph-overflow-hidden" 
                     style="padding-bottom: 56.25%; height: 0;">
                    <iframe src="<?php echo esc_url($virtual_tour_url); ?>" 
                            class="hph-absolute hph-inset-0 hph-w-full hph-h-full"
                            frameborder="0" 
                            allowfullscreen
                            allow="vr; xr; accelerometer; gyroscope; autoplay">
                    </iframe>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($video_tour_url) : ?>
            <!-- Video Tour Content -->
            <div class="hph-tab-content <?php echo !$virtual_tour_url ? 'hph-active' : ''; ?>" data-content="video-tour">
                <div class="hph-video-wrapper hph-relative hph-bg-white hph-rounded-lg hph-shadow-lg hph-overflow-hidden" 
                     style="padding-bottom: 56.25%; height: 0;">
                    <?php
                    // Check if YouTube or Vimeo
                    if (strpos($video_tour_url, 'youtube.com') !== false || strpos($video_tour_url, 'youtu.be') !== false) {
                        // Extract YouTube ID
                        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $video_tour_url, $matches);
                        $youtube_id = $matches[1] ?? '';
                        if ($youtube_id) {
                            $embed_url = "https://www.youtube.com/embed/{$youtube_id}";
                        }
                    } elseif (strpos($video_tour_url, 'vimeo.com') !== false) {
                        // Extract Vimeo ID
                        preg_match('/vimeo\.com\/([0-9]+)/', $video_tour_url, $matches);
                        $vimeo_id = $matches[1] ?? '';
                        if ($vimeo_id) {
                            $embed_url = "https://player.vimeo.com/video/{$vimeo_id}";
                        }
                    } else {
                        $embed_url = $video_tour_url;
                    }
                    ?>
                    <iframe src="<?php echo esc_url($embed_url); ?>" 
                            class="hph-absolute hph-inset-0 hph-w-full hph-h-full"
                            frameborder="0" 
                            allowfullscreen>
                    </iframe>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($floor_plan_images)) : ?>
            <!-- Floor Plans Content -->
            <div class="hph-tab-content <?php echo (!$virtual_tour_url && !$video_tour_url) ? 'hph-active' : ''; ?>" data-content="floor-plans">
                <div class="hph-floor-plans hph-grid hph-grid-cols-1 hph-grid-cols-md-2 hph-gap-lg">
                    <?php foreach ($floor_plan_images as $floor_plan) : ?>
                    <div class="hph-floor-plan-item hph-bg-white hph-rounded-lg hph-shadow-md hph-overflow-hidden">
                        <img src="<?php echo esc_url($floor_plan['url']); ?>" 
                             alt="<?php echo esc_attr($floor_plan['alt'] ?? 'Floor Plan'); ?>"
                             class="hph-w-full hph-h-auto hph-cursor-pointer"
                             data-lightbox="floor-plans">
                        <?php if (!empty($floor_plan['caption'])) : ?>
                        <div class="hph-p-md hph-text-center hph-text-sm hph-text-gray-600">
                            <?php echo esc_html($floor_plan['caption']); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
        
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching functionality
    const tabs = document.querySelectorAll('.hph-tab-btn');
    const contents = document.querySelectorAll('.hph-tab-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.dataset.tab;
            
            // Update active states
            tabs.forEach(t => t.classList.remove('hph-active'));
            contents.forEach(c => c.classList.remove('hph-active'));
            
            this.classList.add('hph-active');
            document.querySelector(`[data-content="${targetTab}"]`).classList.add('hph-active');
        });
    });
});
</script>