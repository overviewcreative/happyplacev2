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

// Get listing change tracking data - available for all template parts
$listing_changes = function_exists('hpt_bridge_get_listing_changes') ? hpt_bridge_get_listing_changes($listing_id) : [];
$listing_badges = function_exists('hpt_bridge_get_listing_badges') ? hpt_bridge_get_listing_badges($listing_id, 3) : [];
$has_recent_changes = function_exists('hpt_bridge_has_recent_changes') ? hpt_bridge_has_recent_changes($listing_id) : false;
$is_new_listing = function_exists('hpt_is_new_listing') ? hpt_is_new_listing($listing_id) : false;

// Only get minimal data needed for gallery strip - let template parts handle their own data
$gallery_images = [];
if (function_exists('hpt_get_listing_gallery_data')) {
    try {
        $gallery_data = hpt_get_listing_gallery_data($listing_id);
        $gallery_images = $gallery_data['images'] ?? [];
    } catch (Exception $e) {
        if (HPH_DEV_MODE) {
            error_log('Gallery bridge function failed: ' . $e->getMessage());
        }
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
    
    <!-- Hero Section -->
    <?php get_template_part('template-parts/listing/hero', null, [
        'listing_id' => $listing_id,
        'layout' => 'full-width',
        'show_gallery' => true,  // Disable gallery in hero since we have media hero
        'show_price' => true,
        'show_stats' => true,
        'show_share' => true,
        'show_save' => true,
        // Change tracking data
        'listing_changes' => $listing_changes,
        'listing_badges' => $listing_badges,
        'has_recent_changes' => $has_recent_changes,
        'is_new_listing' => $is_new_listing
    ]); ?>
    
    <!-- Main Content Area with Sidebar - Framework Layout -->
    <section class="hph-listing-content">
        <div class="hph-listing-layout layout-with-sidebar">
            
            <!-- Main Body Content -->
            <main class="hph-listing-main main-content">
                
                <!-- Include main body template part for all property details -->
                <?php get_template_part('template-parts/listing/main-body', null, [
                    'listing_id' => $listing_id,
                    'listing_changes' => $listing_changes,
                    'listing_badges' => $listing_badges,
                    'has_recent_changes' => $has_recent_changes,
                    'is_new_listing' => $is_new_listing
                ]); ?>
                
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
                <?php // get_template_part('template-parts/listing/city-community-card', null, ['listing_id' => $listing_id]); ?>
                
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

<!-- Hidden Flyer Template for PDF Generation -->
<div id="flyer-template" style="position: fixed; top: 0; left: 0; visibility: hidden; z-index: -1;">
    <?php get_template_part('template-parts/flyer/flyer-template', null, ['listing_id' => $listing_id]); ?>
</div>

<?php 
// Output listing data for JavaScript consumption
if ($listing_id) {
    // Collect all the data we need for the flyer generator
    $js_listing_data = [];
    
    // Address fields
    $js_listing_data['street_number'] = get_field('street_number', $listing_id);
    $js_listing_data['street_dir_prefix'] = get_field('street_dir_prefix', $listing_id);
    $js_listing_data['street_name'] = get_field('street_name', $listing_id);
    $js_listing_data['street_type'] = get_field('street_type', $listing_id);
    $js_listing_data['street_dir_suffix'] = get_field('street_dir_suffix', $listing_id);
    $js_listing_data['unit_number'] = get_field('unit_number', $listing_id);
    $js_listing_data['city'] = get_field('city', $listing_id);
    $js_listing_data['state'] = get_field('state', $listing_id);
    $js_listing_data['zip_code'] = get_field('zip_code', $listing_id);
    
    // Property details
    $js_listing_data['price'] = get_field('listing_price', $listing_id);
    $js_listing_data['beds'] = get_field('bedrooms', $listing_id);
    $js_listing_data['baths'] = get_field('bathrooms', $listing_id) ?: get_field('full_bathrooms', $listing_id) ?: get_field('baths', $listing_id);
    $js_listing_data['sqft'] = get_field('square_feet', $listing_id) ?: get_field('sqft', $listing_id);
    $js_listing_data['lot_size'] = get_field('lot_size', $listing_id) ?: get_field('lot_area', $listing_id);
    $js_listing_data['description'] = get_field('property_description', $listing_id) ?: get_the_content(null, false, $listing_id);
    
    // Media fields
    $js_listing_data['primary_photo'] = get_field('primary_photo', $listing_id);
    $js_listing_data['photo_gallery'] = get_field('photo_gallery', $listing_id);
    
    // Agent information
    $js_listing_data['agent_name'] = get_field('agent_name', $listing_id);
    $js_listing_data['agent_phone'] = get_field('agent_phone', $listing_id);
    $js_listing_data['agent_email'] = get_field('agent_email', $listing_id);
    
    // Office information  
    $js_listing_data['office_phone'] = get_field('office_phone', $listing_id);
    $js_listing_data['office_email'] = get_field('office_email', $listing_id);
    $js_listing_data['office_address'] = get_field('office_address', $listing_id);
    
    // Remove null values
    $js_listing_data = array_filter($js_listing_data, function($value) {
        return $value !== null && $value !== '';
    });
    ?>
    
    <script type="text/javascript">
        // Make listing data available to JavaScript
        window.wpListingData = <?php echo json_encode($js_listing_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
        window.post_id = <?php echo intval($listing_id); ?>;
        
        // Initialize flyer generator when page loads
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof PropertyFlyerGenerator !== 'undefined') {
                window.flyerGenerator = new PropertyFlyerGenerator();
                console.log('Property Flyer Generator initialized with listing data');
            }
        });
        
        // Global function for the Download Flyer button
        function generatePropertyFlyer() {
            console.log('Generating property flyer...');
            
            // Show loading state
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
            button.disabled = true;
            
            try {
                // Load html2pdf library if not already loaded
                if (typeof html2pdf === 'undefined') {
                    loadHTML2PDF().then(() => {
                        generatePDF();
                    }).catch(error => {
                        console.error('Failed to load PDF library:', error);
                        showError();
                    });
                } else {
                    generatePDF();
                }
                
                function generatePDF() {
                    const flyerTemplate = document.querySelector('#flyer-template');
                    const element = document.querySelector('#flyer-template .flyer-container');
                    if (!element || !flyerTemplate) {
                        throw new Error('Flyer template not found');
                    }

                    // Temporarily make template visible for capture
                    flyerTemplate.style.visibility = 'visible';
                    flyerTemplate.style.position = 'fixed';
                    flyerTemplate.style.top = '0';
                    flyerTemplate.style.left = '0';
                    flyerTemplate.style.zIndex = '9999';

                    const opt = {
                        margin: [0, 0, 0, 0],
                        filename: 'property-flyer-<?php echo sanitize_title(get_the_title($listing_id)); ?>.pdf',
                        image: { type: 'jpeg', quality: 0.98 },
                        html2canvas: {
                            scale: 1.5,
                            useCORS: true,
                            allowTaint: true,
                            backgroundColor: '#ffffff',
                            height: 1056,
                            width: 816
                        },
                        jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
                    };

                    html2pdf().from(element).set(opt).save().then(() => {
                        console.log('PDF generated successfully');
                        // Hide template again
                        flyerTemplate.style.visibility = 'hidden';
                        flyerTemplate.style.zIndex = '-1';
                        // Restore button state
                        button.innerHTML = originalText;
                        button.disabled = false;
                    }).catch(error => {
                        console.error('PDF generation failed:', error);
                        // Hide template again on error
                        flyerTemplate.style.visibility = 'hidden';
                        flyerTemplate.style.zIndex = '-1';
                        showError();
                    });
                }
                
                function showError() {
                    alert('Failed to generate PDF. Please try again.');
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
                
            } catch (error) {
                console.error('Error generating flyer:', error);
                showError();
            }
        }
        
        function loadHTML2PDF() {
            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }
        
        // Also make it available globally for testing
        window.generatePropertyFlyer = generatePropertyFlyer;
    </script>
    <?php
}
?>

<?php
// Add floating schedule button for mobile
$agent_name = get_field('agent_name', $listing_id);
$agent_phone = get_field('agent_phone', $listing_id);
$agent_email = get_field('agent_email', $listing_id);

// Only show if we have agent contact info
if ($agent_name && ($agent_phone || $agent_email)) :
?>
<!-- Floating Schedule Button (Mobile Only) -->
<a href="#" 
   class="hph-floating-schedule-btn modal-trigger" 
   data-modal-form="general-contact"
   data-modal-title="Schedule a Showing"
   data-modal-subtitle="Fill out the form below and we'll contact you to schedule a showing."
   data-agent-name="<?php echo esc_attr($agent_name); ?>"
   data-listing-id="<?php echo esc_attr($listing_id); ?>"
   data-listing-title="<?php echo esc_attr(get_the_title()); ?>">
    <i class="fas fa-calendar-alt"></i>
    <span>Schedule Showing</span>
</a>
<?php endif; ?>

<?php get_footer(); ?>
