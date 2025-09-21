<?php
/**
 * Staff Card - Team View
 * File: templ// Final fallback to placeholder
if (!$staff_photo) {
    $staff_photo = get_template_directory_uri() . '/assets/images/team-placeholder.jpg';
}parts/staff-card.php
 * 
 * @package HappyPlaceTheme
 */

$staff_id = $args['staff_id'] ?? get_the_ID();
$style = $args['style'] ?? 'default';

// Get staff data
$first_name = get_field('first_name', $staff_id) ?? get_field('staff_first_name', $staff_id);
$last_name = get_field('last_name', $staff_id) ?? get_field('staff_last_name', $staff_id);
$position = get_field('position', $staff_id) ?? get_field('staff_position', $staff_id);
$title = get_field('title', $staff_id) ?? $position;
$email = get_field('email', $staff_id) ?? get_field('staff_email', $staff_id);
$phone = get_field('phone', $staff_id) ?? get_field('staff_phone', $staff_id);
$bio = get_field('bio', $staff_id) ?? get_field('staff_bio', $staff_id);
$department = get_field('department', $staff_id) ?? get_field('staff_department', $staff_id);
$years_with_company = get_field('years_with_company', $staff_id) ?? get_field('staff_years_experience', $staff_id);

// Social media
$facebook = get_field('facebook', $staff_id) ?? get_field('staff_facebook', $staff_id);
$instagram = get_field('instagram', $staff_id) ?? get_field('staff_instagram', $staff_id);
$linkedin = get_field('linkedin', $staff_id) ?? get_field('staff_linkedin', $staff_id);
$twitter = get_field('twitter', $staff_id) ?? get_field('staff_twitter', $staff_id);

// Build full name
$full_name = trim($first_name . ' ' . $last_name);
if (empty($full_name)) {
    $full_name = get_the_title($staff_id);
}

// Get staff photo - try multiple field names
$staff_photo = get_field('profile_photo', $staff_id) ?? 
               get_field('staff_photo', $staff_id) ?? 
               get_field('photo', $staff_id);

if ($staff_photo && is_array($staff_photo)) {
    // ACF image field returns array
    $staff_photo = $staff_photo['sizes']['medium'] ?? $staff_photo['url'];
} elseif ($staff_photo && is_numeric($staff_photo)) {
    // If it's an attachment ID
    $staff_photo = wp_get_attachment_image_url($staff_photo, 'medium');
} elseif (!$staff_photo) {
    // Fallback to featured image if profile photo not set
    $staff_photo = get_the_post_thumbnail_url($staff_id, 'medium');
}

// Final fallback to placeholder
if (!$staff_photo) {
    $staff_photo = get_template_directory_uri() . '/assets/images/placeholder-person.jpg';
}

// Process specialties/skills for display
$skills = get_field('skills', $staff_id) ?? get_field('staff_skills', $staff_id);
$skills_list = [];
if ($skills) {
    if (is_array($skills)) {
        $skills_list = $skills;
    } else {
        $skills_list = explode(',', $skills);
    }
    $skills_list = array_map('trim', $skills_list);
}
?>

<article class="hph-card hph-card-elevated hph-h-full hph-flex hph-flex-col hph-transition-all hover:hph-shadow-xl hph-staff-card">
    <div class="hph-block hph-h-full hph-flex hph-flex-col">
        
        <!-- Photo Container -->
        <div class="hph-relative hph-aspect-ratio-1-1 hph-overflow-hidden hph-bg-gray-200">
            <img src="<?php echo esc_url($staff_photo); ?>" 
                 alt="<?php echo esc_attr($full_name); ?>"
                 class="hph-w-full hph-h-full hph-object-cover"
                 loading="lazy">
            
            <!-- Department Badge -->
            <?php if ($department): ?>
            <div class="hph-absolute hph-top-md hph-right-md">
                <span class="hph-bg-secondary hph-text-white hph-px-sm hph-py-xs hph-rounded hph-text-xs hph-font-semibold">
                    <?php echo esc_html($department); ?>
                </span>
            </div>
            <?php endif; ?>
            
            <!-- Years Badge -->
            <?php if ($years_with_company): ?>
            <div class="hph-absolute hph-top-md hph-left-md">
                <span class="hph-px-sm hph-py-xs hph-rounded-md hph-text-xs hph-font-semibold hph-bg-success hph-text-white">
                    <?php echo $years_with_company; ?>+ Years
                </span>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Content -->
        <div class="hph-p-md hph-flex-grow hph-flex hph-flex-col">
            
            <!-- Name & Position -->
            <div class="hph-mb-sm">
                <h3 class="hph-text-lg hph-font-semibold hph-text-gray-900 hph-mb-xs hph-line-clamp-1">
                    <?php echo esc_html($full_name); ?>
                </h3>
                
                <?php if ($title || $position): ?>
                <p class="hph-text-sm hph-text-secondary hph-font-medium">
                    <?php echo esc_html($title ?: $position); ?>
                </p>
                <?php endif; ?>
            </div>
            
            <!-- Bio -->
            <?php if ($bio): ?>
            <p class="hph-text-sm hph-text-gray-600 hph-mb-md hph-line-clamp-2">
                <?php echo wp_trim_words($bio, 15); ?>
            </p>
            <?php endif; ?>
            
            <!-- Skills -->
            <?php if (!empty($skills_list) && count($skills_list) > 0): ?>
            <div class="hph-mb-md">
                <div class="hph-flex hph-flex-wrap hph-gap-xs">
                    <?php 
                    $displayed_skills = array_slice($skills_list, 0, 2);
                    foreach ($displayed_skills as $skill): 
                        $clean_skill = str_replace('-', ' ', $skill);
                    ?>
                    <span class="hph-bg-gray-100 hph-text-gray-700 hph-px-xs hph-py-xs hph-rounded hph-text-xs">
                        <?php echo esc_html(ucwords($clean_skill)); ?>
                    </span>
                    <?php endforeach; ?>
                    
                    <?php if (count($skills_list) > 2): ?>
                    <span class="hph-text-xs hph-text-gray-500">
                        +<?php echo count($skills_list) - 2; ?> more
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Contact Info -->
            <div class="hph-mt-auto hph-text-sm hph-text-gray-700">
                <?php if ($phone): ?>
                <div class="hph-flex hph-items-center hph-gap-xs hph-mb-xs">
                    <i class="fas fa-phone hph-text-secondary"></i>
                    <span><?php echo esc_html($phone); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if ($email): ?>
                <div class="hph-flex hph-items-center hph-gap-xs hph-mb-xs">
                    <i class="fas fa-envelope hph-text-secondary"></i>
                    <span class="hph-truncate"><?php echo esc_html($email); ?></span>
                </div>
                <?php endif; ?>
                
                <!-- Social Media -->
                <?php if ($linkedin || $facebook || $instagram || $twitter): ?>
                <div class="hph-flex hph-items-center hph-gap-xs hph-mt-sm hph-pt-sm hph-border-t hph-border-gray-200">
                    <?php if ($linkedin): ?>
                    <a href="<?php echo esc_url($linkedin); ?>" 
                       target="_blank" 
                       rel="noopener" 
                       class="hph-text-gray-400 hover:hph-text-secondary hph-transition-colors"
                       aria-label="<?php echo esc_attr($full_name); ?> LinkedIn Profile">
                        <i class="fab fa-linkedin"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($facebook): ?>
                    <a href="<?php echo esc_url($facebook); ?>" 
                       target="_blank" 
                       rel="noopener" 
                       class="hph-text-gray-400 hover:hph-text-secondary hph-transition-colors"
                       aria-label="<?php echo esc_attr($full_name); ?> Facebook Profile">
                        <i class="fab fa-facebook"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($instagram): ?>
                    <a href="<?php echo esc_url($instagram); ?>" 
                       target="_blank" 
                       rel="noopener" 
                       class="hph-text-gray-400 hover:hph-text-secondary hph-transition-colors"
                       aria-label="<?php echo esc_attr($full_name); ?> Instagram Profile">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($twitter): ?>
                    <a href="<?php echo esc_url($twitter); ?>" 
                       target="_blank" 
                       rel="noopener" 
                       class="hph-text-gray-400 hover:hph-text-secondary hph-transition-colors"
                       aria-label="<?php echo esc_attr($full_name); ?> Twitter Profile">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            
        </div>
    </div>
</article>
