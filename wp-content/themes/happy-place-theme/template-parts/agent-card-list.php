<?php
/**
 * Agent Card - List View - Uses Universal Card System
 * File: template-parts/agent-card-list.php
 *
 * @package HappyPlaceTheme
 */

$agent_id = $args['agent_id'] ?? get_the_ID();

// Use the universal card system with horizontal layout
hph_component('universal-card', [
    'post_id' => $agent_id,
    'layout' => 'horizontal',
    'variant' => $args['variant'] ?? 'default',
    'size' => $args['size'] ?? 'md',
    'show_actions' => true,
    'clickable' => true
]);

return; // Exit early - rest of file is legacy code to be removed

// Get agent data (same as grid view)
$first_name = get_field('first_name', $agent_id);
$last_name = get_field('last_name', $agent_id);
$title = get_field('title', $agent_id);
$email = get_field('email', $agent_id);
$phone = get_field('phone', $agent_id);
$bio = get_field('bio', $agent_id);
$years_experience = get_field('years_experience', $agent_id);
$specialties = get_field('specialties', $agent_id);
$languages = get_field('languages', $agent_id);
$license_number = get_field('license_number', $agent_id);
$total_sales_volume = get_field('total_sales_volume', $agent_id);
$total_listings_sold = get_field('total_listings_sold', $agent_id);
$featured = get_field('featured', $agent_id);

// Office information
$office_id = get_field('office', $agent_id);
$office_name = '';
if ($office_id) {
    $office_name = get_the_title($office_id);
}

// Social media
$facebook = get_field('facebook', $agent_id);
$instagram = get_field('instagram', $agent_id);
$linkedin = get_field('linkedin', $agent_id);
$twitter = get_field('twitter', $agent_id);

// Build full name
$full_name = trim($first_name . ' ' . $last_name);
if (empty($full_name)) {
    $full_name = get_the_title($agent_id);
}

// Get agent photo - use profile photo field instead of featured image
$agent_photo = get_field('profile_photo', $agent_id);
if ($agent_photo && is_array($agent_photo)) {
    // ACF image field returns array
    $agent_photo = $agent_photo['sizes']['medium'] ?? $agent_photo['url'];
} elseif ($agent_photo && is_numeric($agent_photo)) {
    // If it's an attachment ID
    $agent_photo = wp_get_attachment_image_url($agent_photo, 'medium');
} elseif (!$agent_photo) {
    // Fallback to featured image if profile photo not set
    $agent_photo = get_the_post_thumbnail_url($agent_id, 'medium');
}

// Final fallback to placeholder
if (!$agent_photo) {
    $agent_photo = hph_get_image_url_only('assets/images/placeholder-agent.jpg');
}

// Process specialties for display
$specialty_list = [];
if ($specialties) {
    if (is_array($specialties)) {
        $specialty_list = $specialties;
    } else {
        $specialty_list = explode(',', $specialties);
    }
    $specialty_list = array_map('trim', $specialty_list);
}

// Process languages for display
$language_list = [];
if ($languages) {
    if (is_array($languages)) {
        $language_list = $languages;
    } else {
        $language_list = explode(',', $languages);
    }
    $language_list = array_map('trim', $language_list);
}
?>

<article class="hph-card hph-card-elevated hph-transition-all hover:hph-shadow-xl hph-agent-card">
    <a href="<?php echo get_permalink($agent_id); ?>" class="hph-flex hph-flex-col md:hph-flex-row">
        
        <!-- Photo Container -->
        <div class="hph-relative md:hph-w-1/4 hph-aspect-ratio-1-1 md:hph-aspect-ratio-none md:hph-h-auto hph-overflow-hidden hph-bg-gray-200">
            <img src="<?php echo esc_url($agent_photo); ?>" 
                 alt="<?php echo esc_attr($full_name); ?>"
                 class="hph-w-full hph-h-full hph-object-cover"
                 loading="lazy">
            
            <!-- Featured Badge -->
            <?php if ($featured): ?>
            <div class="hph-absolute hph-top-md hph-left-md">
                <span class="hph-px-sm hph-py-xs hph-rounded-md hph-text-xs hph-font-semibold hph-bg-warning hph-text-white">
                    <i class="fas fa-star hph-mr-xs"></i>Featured
                </span>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Content -->
        <div class="hph-flex-grow hph-p-lg hph-flex hph-flex-col">
            
            <!-- Header Row -->
            <div class="hph-flex hph-justify-between hph-items-start hph-mb-md">
                <div class="hph-flex-grow">
                    <!-- Name & Title -->
                    <h3 class="hph-text-xl hph-font-semibold hph-text-gray-900 hph-mb-xs">
                        <?php echo esc_html($full_name); ?>
                    </h3>
                    
                    <?php if ($title): ?>
                    <p class="hph-text-primary hph-font-medium hph-mb-xs">
                        <?php echo esc_html($title); ?>
                    </p>
                    <?php endif; ?>
                    
                    <!-- Office and License -->
                    <div class="hph-flex hph-gap-sm hph-text-sm hph-text-gray-500">
                        <?php if ($office_name): ?>
                        <span><?php echo esc_html($office_name); ?></span>
                        <?php endif; ?>
                        
                        <?php if ($license_number): ?>
                        <span><?php echo $office_name ? '•' : ''; ?> License #<?php echo esc_html($license_number); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Experience Badge -->
                <?php if ($years_experience): ?>
                <div class="hph-text-center hph-ml-md">
                    <div class="hph-bg-primary hph-text-white hph-px-md hph-py-sm hph-rounded-lg">
                        <div class="hph-text-lg hph-font-bold"><?php echo $years_experience; ?>+</div>
                        <div class="hph-text-xs">Years</div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Bio -->
            <?php if ($bio): ?>
            <p class="hph-text-gray-700 hph-mb-md hph-line-clamp-3">
                <?php echo wp_trim_words($bio, 40); ?>
            </p>
            <?php endif; ?>
            
            <!-- Stats Row -->
            <?php if ($total_sales_volume || $total_listings_sold): ?>
            <div class="hph-grid hph-grid-cols-2 hph-gap-md hph-py-md hph-border-y hph-border-gray-200 hph-mb-md">
                <?php if ($total_sales_volume): ?>
                <div class="hph-text-center">
                    <div class="hph-text-xs hph-text-gray-500 hph-mb-xs">Sales Volume</div>
                    <div class="hph-font-semibold hph-text-gray-900">$<?php echo number_format($total_sales_volume); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($total_listings_sold): ?>
                <div class="hph-text-center">
                    <div class="hph-text-xs hph-text-gray-500 hph-mb-xs">Properties Sold</div>
                    <div class="hph-font-semibold hph-text-gray-900"><?php echo number_format($total_listings_sold); ?></div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Footer Row -->
            <div class="hph-flex hph-justify-between hph-items-center hph-mt-auto">
                
                <!-- Contact Info -->
                <div class="hph-flex hph-flex-col hph-gap-xs hph-text-sm hph-text-gray-700">
                    <?php if ($phone): ?>
                    <div class="hph-flex hph-items-center hph-gap-xs">
                        <i class="fas fa-phone hph-text-primary"></i>
                        <span><?php echo esc_html($phone); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($email): ?>
                    <div class="hph-flex hph-items-center hph-gap-xs">
                        <i class="fas fa-envelope hph-text-primary"></i>
                        <span><?php echo esc_html($email); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Specialties and Languages -->
                <div class="hph-text-right">
                    <!-- Specialties -->
                    <?php if (!empty($specialty_list) && count($specialty_list) > 0): ?>
                    <div class="hph-mb-sm">
                        <div class="hph-text-xs hph-text-gray-500 hph-mb-xs">Specialties</div>
                        <div class="hph-flex hph-flex-wrap hph-justify-end hph-gap-xs">
                            <?php 
                            $displayed_specialties = array_slice($specialty_list, 0, 3);
                            foreach ($displayed_specialties as $specialty): 
                                $clean_specialty = str_replace('-', ' ', $specialty);
                            ?>
                            <span class="hph-bg-primary hph-bg-opacity-10 hph-text-primary hph-px-sm hph-py-xs hph-rounded hph-text-xs hph-font-medium">
                                <?php echo esc_html(ucwords($clean_specialty)); ?>
                            </span>
                            <?php endforeach; ?>
                            
                            <?php if (count($specialty_list) > 3): ?>
                            <span class="hph-text-xs hph-text-gray-500">
                                +<?php echo count($specialty_list) - 3; ?> more
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Languages -->
                    <?php if (!empty($language_list) && count($language_list) > 0): ?>
                    <div>
                        <div class="hph-text-xs hph-text-gray-500 hph-mb-xs">Languages</div>
                        <div class="hph-flex hph-flex-wrap hph-justify-end hph-gap-xs">
                            <?php 
                            $displayed_languages = array_slice($language_list, 0, 3);
                            foreach ($displayed_languages as $language): 
                            ?>
                            <span class="hph-bg-gray-100 hph-text-gray-700 hph-px-sm hph-py-xs hph-rounded hph-text-xs">
                                <?php echo esc_html(ucwords($language)); ?>
                            </span>
                            <?php endforeach; ?>
                            
                            <?php if (count($language_list) > 3): ?>
                            <span class="hph-text-xs hph-text-gray-500">
                                +<?php echo count($language_list) - 3; ?> more
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
    </a>
</article>
