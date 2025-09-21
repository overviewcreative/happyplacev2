<?php
/**
 * Single Staff Member Template - Based on Single Agent Template
 * 
 * Complete staff profile using the unified section system:
 * - Hero section with staff photo and key info
 * - Bio section showcasing experience and background
 * - Skills section highlighting areas of expertise
 * - Contact form section for communication
 * 
 * @package HappyPlaceTheme
 * @since 3.2.0
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Get staff ID and verify it exists
$staff_id = get_the_ID();
if (!$staff_id || get_post_type($staff_id) !== 'staff') {
    get_template_part('template-parts/base/content-none');
    get_footer();
    return;
}

// Get staff data with flexible field names
$first_name = get_field('first_name', $staff_id) ?? get_field('staff_first_name', $staff_id);
$last_name = get_field('last_name', $staff_id) ?? get_field('staff_last_name', $staff_id);
$position = get_field('position', $staff_id) ?? get_field('staff_position', $staff_id);
$email = get_field('email', $staff_id) ?? get_field('staff_email', $staff_id);
$phone = get_field('phone', $staff_id) ?? get_field('staff_phone', $staff_id);
$bio = get_field('bio', $staff_id) ?? get_field('staff_bio', $staff_id);
$years_experience = get_field('years_experience', $staff_id) ?? get_field('staff_years_experience', $staff_id);
$skills = get_field('skills', $staff_id) ?? get_field('staff_skills', $staff_id);
$department = get_field('department', $staff_id) ?? get_field('staff_department', $staff_id);
$employment_status = get_field('employment_status', $staff_id) ?? get_field('staff_employment_status', $staff_id);

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

// Get staff photo - use profile photo field instead of featured image
$staff_photo = get_field('profile_photo', $staff_id) ?? get_field('staff_photo', $staff_id) ?? get_field('photo', $staff_id);
if ($staff_photo && is_array($staff_photo)) {
    // ACF image field returns array
    $staff_photo = $staff_photo['sizes']['large'] ?? $staff_photo['url'];
} elseif ($staff_photo && is_numeric($staff_photo)) {
    // If it's an attachment ID
    $staff_photo = wp_get_attachment_image_url($staff_photo, 'large');
} elseif (!$staff_photo) {
    // Fallback to featured image if profile photo not set
    $staff_photo = get_the_post_thumbnail_url($staff_id, 'large');
}

// Final fallback to placeholder
if (!$staff_photo) {
    $staff_photo = get_template_directory_uri() . '/assets/images/team-placeholder.jpg';
}
?>

    <?php
    // ============================================
    // Hero Section - Staff Profile
    // ============================================
    $bio_preview = $bio ? wp_trim_words(strip_tags($bio), 25, '...') : 'Dedicated team member providing exceptional support and service to our clients.';
    ?>

    <!-- Custom Hero Section with Staff Profile Photo -->
    <section 
        class="hph-hero-section"
        id="hero-staff-<?php echo esc_attr($staff_id); ?>"
        data-bg="gradient"
        style="position: relative; width: 100%; background: var(--hph-gradient-secondary); color: var(--hph-white); padding-top: var(--hph-padding-3xl); padding-bottom: var(--hph-padding-3xl);"
        data-animation="false"
    >
        <div style="position: relative; z-index: 10; margin-left: auto; margin-right: auto; padding-left: var(--hph-padding-lg); padding-right: var(--hph-padding-lg); max-width: var(--hph-container-xl);">
            
            <!-- Hero Content -->
            <div style="text-align: center;">
                
                <!-- Staff Profile Photo -->
                <div style="margin-bottom: var(--hph-margin-2xl);">
                    <div style="display: inline-block; position: relative;">
                        <div style="width: 200px; height: 200px; border-radius: var(--hph-radius-full); overflow: hidden; border: 6px solid rgba(255, 255, 255, 0.2); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2); margin: 0 auto;">
                            <img 
                                src="<?php echo esc_url($staff_photo); ?>" 
                                alt="<?php echo esc_attr($full_name); ?>" 
                                style="width: 100%; height: 100%; object-fit: cover;"
                                loading="eager"
                            >
                        </div>
                        
                        <?php if ($employment_status === 'featured' || $employment_status === 'lead'): ?>
                        <!-- Featured Staff Badge -->
                        <div style="position: absolute; top: -10px; right: -10px; background: var(--hph-secondary); color: var(--hph-white); border-radius: var(--hph-radius-full); padding: var(--hph-padding-sm) var(--hph-padding-md); font-size: var(--hph-text-sm); font-weight: var(--hph-font-semibold); box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
                            <i class="fas fa-star" style="margin-right: var(--hph-margin-xs);"></i>
                            <span><?php _e('Team Lead', 'happy-place-theme'); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Staff Name -->
                <h1 style="margin: 0 0 var(--hph-margin-lg) 0; font-size: var(--hph-text-5xl); font-weight: var(--hph-font-bold); line-height: var(--hph-leading-tight);">
                    <?php echo esc_html($full_name); ?>
                </h1>
                
                <!-- Staff Position -->
                <?php if ($position): ?>
                <p style="margin: 0 0 var(--hph-margin-lg) 0; font-size: var(--hph-text-xl); font-weight: var(--hph-font-medium); opacity: 0.9;">
                    <?php echo esc_html($position); ?>
                </p>
                <?php endif; ?>
                
                <!-- Department Info -->
                <?php if ($department): ?>
                <p style="margin: 0 0 var(--hph-margin-xl) 0; font-size: var(--hph-text-base); opacity: 0.8;">
                    <i class="fas fa-users" style="margin-right: var(--hph-margin-sm);"></i>
                    <?php echo esc_html($department); ?> Department
                </p>
                <?php endif; ?>
                
                <!-- Bio Preview -->
                <div style="font-size: var(--hph-text-base); line-height: var(--hph-leading-relaxed); max-width: 65ch; margin: 0 auto var(--hph-margin-2xl) auto; opacity: 0.85;">
                    <?php echo esc_html($bio_preview); ?>
                </div>
                
                <!-- Action Buttons -->
                <div style="display: flex; flex-wrap: wrap; gap: var(--hph-gap-lg); align-items: center; justify-content: center;">
                    <?php if ($phone): ?>
                    <a 
                        href="tel:<?php echo esc_attr($phone); ?>"
                        style="display: inline-flex; align-items: center; justify-content: center; text-decoration: none; font-weight: var(--hph-font-semibold); border-radius: var(--hph-radius-lg); transition: all 300ms ease; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); padding: var(--hph-padding-lg) var(--hph-padding-2xl); font-size: var(--hph-text-lg); background-color: var(--hph-white); color: var(--hph-secondary); border: 2px solid var(--hph-white);"
                        onmouseover="this.style.transform='translateY(-2px)'"
                        onmouseout="this.style.transform='translateY(0)'"
                    >
                        <i class="fas fa-phone" style="margin-right: var(--hph-margin-sm);"></i>
                        <span>Call <?php echo esc_html($phone); ?></span>
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($email): ?>
                    <a 
                        href="mailto:<?php echo esc_attr($email); ?>"
                        style="display: inline-flex; align-items: center; justify-content: center; text-decoration: none; font-weight: var(--hph-font-semibold); border-radius: var(--hph-radius-lg); transition: all 300ms ease; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); padding: var(--hph-padding-lg) var(--hph-padding-2xl); font-size: var(--hph-text-lg); background-color: transparent; color: var(--hph-white); border: 2px solid var(--hph-white);"
                        onmouseover="this.style.transform='translateY(-2px)'"
                        onmouseout="this.style.transform='translateY(0)'"
                    >
                        <i class="fas fa-envelope" style="margin-right: var(--hph-margin-sm);"></i>
                        <span>Send Email</span>
                    </a>
                    <?php endif; ?>
                </div>
                
            </div>
        </div>
    </section>
    
    <?php
    // ============================================
    // Bio Section - Staff Background
    // ============================================
    if ($bio || get_the_content()): 
    ?>
    <section style="padding-top: var(--hph-padding-3xl); padding-bottom: var(--hph-padding-3xl); background-color: var(--hph-white);">
        <div style="margin-left: auto; margin-right: auto; padding-left: var(--hph-padding-lg); padding-right: var(--hph-padding-lg); max-width: var(--hph-container-lg);">
            
            <div style="text-align: center; margin-bottom: var(--hph-margin-3xl);">
                <h2 style="margin: 0 0 var(--hph-margin-lg) 0; font-size: var(--hph-text-3xl); font-weight: var(--hph-font-bold); color: var(--hph-gray-900);">
                    About <?php echo esc_html($first_name ?: get_the_title()); ?>
                </h2>
            </div>
            
            <div style="max-width: 75ch; margin: 0 auto;">
                <?php if ($bio): ?>
                <div style="font-size: var(--hph-text-lg); line-height: var(--hph-leading-relaxed); color: var(--hph-gray-700); margin-bottom: var(--hph-margin-2xl);">
                    <?php echo wpautop(esc_html($bio)); ?>
                </div>
                <?php endif; ?>
                
                <?php if (get_the_content()): ?>
                <div style="font-size: var(--hph-text-base); line-height: var(--hph-leading-relaxed); color: var(--hph-gray-700);">
                    <?php the_content(); ?>
                </div>
                <?php endif; ?>
            </div>
            
        </div>
    </section>
    <?php endif; ?>
    
    <?php
    // ============================================
    // Skills Section - Staff Expertise
    // ============================================
    if ($skills || $years_experience):
        $skills_list = [];
        if ($skills) {
            $skills_list = is_array($skills) ? $skills : explode(',', $skills);
            $skills_list = array_map('trim', $skills_list);
        }
    ?>
    <section style="padding-top: var(--hph-padding-3xl); padding-bottom: var(--hph-padding-3xl); background-color: var(--hph-gray-50);">
        <div style="margin-left: auto; margin-right: auto; padding-left: var(--hph-padding-lg); padding-right: var(--hph-padding-lg); max-width: var(--hph-container-lg);">
            
            <div style="text-align: center; margin-bottom: var(--hph-margin-3xl);">
                <h2 style="margin: 0 0 var(--hph-margin-lg) 0; font-size: var(--hph-text-3xl); font-weight: var(--hph-font-bold); color: var(--hph-gray-900);">
                    Skills & Experience
                </h2>
            </div>
            
            <div style="max-width: 4xl; margin: 0 auto;">
                
                <?php if ($years_experience): ?>
                <div style="text-align: center; margin-bottom: var(--hph-margin-3xl);">
                    <div style="display: inline-block; padding: var(--hph-padding-xl) var(--hph-padding-2xl); background-color: var(--hph-white); border-radius: var(--hph-radius-lg); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                        <div style="font-size: var(--hph-text-4xl); font-weight: var(--hph-font-bold); color: var(--hph-secondary); margin-bottom: var(--hph-margin-sm);">
                            <?php echo esc_html($years_experience); ?>+
                        </div>
                        <div style="font-size: var(--hph-text-base); font-weight: var(--hph-font-medium); color: var(--hph-gray-600);">
                            Years Experience
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($skills_list)): ?>
                <div style="display: flex; flex-wrap: wrap; gap: var(--hph-gap-md); justify-content: center;">
                    <?php foreach ($skills_list as $skill): ?>
                    <span style="display: inline-block; padding: var(--hph-padding-sm) var(--hph-padding-lg); background-color: var(--hph-secondary); color: var(--hph-white); border-radius: var(--hph-radius-full); font-size: var(--hph-text-sm); font-weight: var(--hph-font-medium);">
                        <?php echo esc_html(ucwords(str_replace('-', ' ', $skill))); ?>
                    </span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
            </div>
            
        </div>
    </section>
    <?php endif; ?>
    
    <?php
    // ============================================
    // Social Media Section
    // ============================================
    $social_links = array_filter([
        'linkedin' => $linkedin,
        'facebook' => $facebook,
        'instagram' => $instagram,
        'twitter' => $twitter
    ]);
    
    if (!empty($social_links)):
    ?>
    <section style="padding-top: var(--hph-padding-2xl); padding-bottom: var(--hph-padding-3xl); background-color: var(--hph-white);">
        <div style="margin-left: auto; margin-right: auto; padding-left: var(--hph-padding-lg); padding-right: var(--hph-padding-lg); max-width: var(--hph-container-lg);">
            
            <div style="text-align: center;">
                <h3 style="margin: 0 0 var(--hph-margin-lg) 0; font-size: var(--hph-text-xl); font-weight: var(--hph-font-semibold); color: var(--hph-gray-900);">
                    Connect with <?php echo esc_html($first_name ?: get_the_title()); ?>
                </h3>
                
                <div style="display: flex; gap: var(--hph-gap-lg); justify-content: center; align-items: center;">
                    <?php 
                    $social_icons = [
                        'linkedin' => 'fab fa-linkedin',
                        'facebook' => 'fab fa-facebook',
                        'instagram' => 'fab fa-instagram',
                        'twitter' => 'fab fa-twitter'
                    ];
                    
                    foreach ($social_links as $platform => $url): 
                    ?>
                    <a 
                        href="<?php echo esc_url($url); ?>" 
                        target="_blank" 
                        rel="noopener noreferrer"
                        style="display: inline-flex; align-items: center; justify-content: center; width: 50px; height: 50px; background-color: var(--hph-secondary); color: var(--hph-white); border-radius: var(--hph-radius-full); font-size: var(--hph-text-xl); transition: all 300ms ease; text-decoration: none;"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 15px -3px rgba(0, 0, 0, 0.1)'"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'"
                        aria-label="<?php echo esc_attr($full_name . ' on ' . ucfirst($platform)); ?>"
                    >
                        <i class="<?php echo esc_attr($social_icons[$platform]); ?>"></i>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Navigation -->
    <section style="padding-top: var(--hph-padding-2xl); padding-bottom: var(--hph-padding-2xl); background-color: var(--hph-gray-50); border-top: 1px solid var(--hph-gray-200);">
        <div style="margin-left: auto; margin-right: auto; padding-left: var(--hph-padding-lg); padding-right: var(--hph-padding-lg); max-width: var(--hph-container-lg);">
            
            <div style="display: flex; justify-content: center; align-items: center; gap: var(--hph-gap-lg);">
                
                <?php
                $prev_post = get_previous_post();
                $next_post = get_next_post();
                ?>
                
                <?php if ($prev_post): ?>
                <a 
                    href="<?php echo get_permalink($prev_post); ?>" 
                    rel="prev"
                    style="display: inline-flex; align-items: center; gap: var(--hph-gap-sm); padding: var(--hph-padding-md) var(--hph-padding-lg); background-color: var(--hph-white); border-radius: var(--hph-radius-lg); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); text-decoration: none; color: var(--hph-gray-700); font-weight: var(--hph-font-medium); transition: all 300ms ease;"
                    onmouseover="this.style.transform='translateY(-2px)'"
                    onmouseout="this.style.transform='translateY(0)'"
                >
                    <i class="fas fa-arrow-left"></i>
                    <span>Previous</span>
                </a>
                <?php endif; ?>
                
                <a 
                    href="<?php echo get_post_type_archive_link('staff'); ?>" 
                    style="display: inline-flex; align-items: center; gap: var(--hph-gap-sm); padding: var(--hph-padding-md) var(--hph-padding-xl); background-color: var(--hph-secondary); color: var(--hph-white); border-radius: var(--hph-radius-lg); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); text-decoration: none; font-weight: var(--hph-font-semibold); transition: all 300ms ease;"
                    onmouseover="this.style.transform='translateY(-2px)'"
                    onmouseout="this.style.transform='translateY(0)'"
                >
                    <i class="fas fa-users"></i>
                    <span>View All Staff</span>
                </a>
                
                <?php if ($next_post): ?>
                <a 
                    href="<?php echo get_permalink($next_post); ?>" 
                    rel="next"
                    style="display: inline-flex; align-items: center; gap: var(--hph-gap-sm); padding: var(--hph-padding-md) var(--hph-padding-lg); background-color: var(--hph-white); border-radius: var(--hph-radius-lg); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); text-decoration: none; color: var(--hph-gray-700); font-weight: var(--hph-font-medium); transition: all 300ms ease;"
                    onmouseover="this.style.transform='translateY(-2px)'"
                    onmouseout="this.style.transform='translateY(0)'"
                >
                    <span>Next</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <?php endif; ?>
                
            </div>
            
        </div>
    </section>

<?php get_footer(); ?>
