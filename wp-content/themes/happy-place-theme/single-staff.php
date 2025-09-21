<?php
/**
 * Single Staff Template - Based on Single Agent Template
 * 
 * Complete staff profile using the unified section system:
 * - Hero section with staff photo and key info
 * - Bio section showcasing experience and background
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
$position = get_field('position', $staff_id) ?? get_field('staff_position', $staff_id) ?? get_field('title', $staff_id);
$email = get_field('email', $staff_id) ?? get_field('staff_email', $staff_id);
$phone = get_field('phone', $staff_id) ?? get_field('staff_phone', $staff_id);
$bio = get_field('bio', $staff_id) ?? get_field('staff_bio', $staff_id);
$years_experience = get_field('years_experience', $staff_id) ?? get_field('staff_years_experience', $staff_id);
$skills = get_field('skills', $staff_id) ?? get_field('staff_skills', $staff_id);
$department = get_field('department', $staff_id) ?? get_field('staff_department', $staff_id);
$employment_status = get_field('employment_status', $staff_id) ?? get_field('staff_employment_status', $staff_id);

// Office information
$office_id = get_field('office', $staff_id);
$office_name = '';
if ($office_id) {
    $office_name = get_the_title($office_id);
}

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
$staff_photo = get_field('profile_photo', $staff_id);
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
    $staff_photo = get_template_directory_uri() . '/assets/images/placeholder-agent.jpg';
}
?>

    <?php
    // ============================================
    // Hero Section - Staff Profile
    // ============================================
    $bio_preview = $bio ? wp_trim_words(strip_tags($bio), 25, '...') : 'Dedicated team member committed to providing exceptional service and support.';
    ?>

    <!-- Custom Hero Section with Staff Profile Photo -->
    <section 
        class="hph-hero-section"
        id="hero-staff-<?php echo esc_attr($staff_id); ?>"
        data-bg="gradient"
        style="position: relative; width: 100%; background: var(--hph-gradient-primary); color: var(--hph-white); padding-top: var(--hph-padding-3xl); padding-bottom: var(--hph-padding-3xl);"
        data-animation="false"
    >
        <div style="position: relative; z-index: 10; margin-left: auto; margin-right: auto; padding-left: var(--hph-space-6); padding-right: var(--hph-space-6); max-width: var(--hph-container-xl); padding-top: var(--hph-space-20);">

            <!-- Hero Content -->
            <div style="text-align: center;">

                <!-- Staff Profile Photo -->
                <div style="margin-bottom: var(--hph-space-12);">
                    <div style="display: inline-block; position: relative;">
                        <div style="width: 200px; height: 200px; border-radius: var(--hph-radius-full); overflow: hidden; border: 6px solid rgba(255, 255, 255, 0.2); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2); margin: 0 auto;">
                            <img
                                src="<?php echo esc_url($staff_photo); ?>"
                                alt="<?php echo esc_attr($full_name); ?>"
                                style="width: 100%; height: 100%; object-fit: cover;"
                                loading="eager"
                            >
                        </div>
                    </div>
                </div>

                <!-- Staff Name -->
                <h1 style="margin: 0 0 var(--hph-space-4) 0; font-size: var(--hph-text-5xl); font-weight: var(--hph-font-bold); line-height: var(--hph-leading-tight);">
                    <?php echo esc_html($full_name); ?>
                </h1>

                <!-- Staff Position -->
                <?php if ($position): ?>
                <p style="margin: 0 0 var(--hph-space-8) 0; font-size: var(--hph-text-xl); font-weight: var(--hph-font-medium); opacity: 0.9;">
                    <?php echo esc_html($position); ?>
                </p>
                <?php endif; ?>

                <!-- Department Info -->
                <?php if ($department): ?>
                <p style="margin: 0 0 var(--hph-space-6) 0; font-size: var(--hph-text-base); opacity: 0.8;">
                    <i class="fas fa-building" style="margin-right: var(--hph-space-2);"></i>
                    <?php echo esc_html($department); ?>
                </p>
                <?php endif; ?>

                <!-- Office Info -->
                <?php if ($office_name): ?>
                <p style="margin: 0 0 var(--hph-space-10) 0; font-size: var(--hph-text-base); opacity: 0.8;">
                    <i class="fas fa-map-marker-alt" style="margin-right: var(--hph-space-2);"></i>
                    <?php echo esc_html($office_name); ?>
                </p>
                <?php endif; ?>

                <!-- Bio Preview -->
                <div style="font-size: var(--hph-text-lg); line-height: var(--hph-leading-relaxed); max-width: 65ch; margin: 0 auto var(--hph-space-12) auto; opacity: 0.85;">
                    <?php echo esc_html($bio_preview); ?>
                </div>

                <!-- Action Buttons -->
                <div style="display: flex; flex-wrap: wrap; gap: var(--hph-space-6); align-items: center; justify-content: center; margin-top: var(--hph-space-8); margin-bottom: var(--hph-space-10);">
                    <?php if ($phone): ?>
                    <a
                        href="tel:<?php echo esc_attr($phone); ?>"
                        style="display: inline-flex; align-items: center; justify-content: center; text-decoration: none; font-weight: var(--hph-font-semibold); border-radius: var(--hph-radius-lg); transition: all 300ms ease; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); padding: var(--hph-space-4) var(--hph-space-8); font-size: var(--hph-text-base); background-color: var(--hph-white); color: var(--hph-primary); border: 2px solid var(--hph-white); min-width: 180px;"
                        onmouseover="this.style.transform='translateY(-2px)'"
                        onmouseout="this.style.transform='translateY(0)'"
                    >
                        <i class="fas fa-phone" style="margin-right: var(--hph-space-3);"></i>
                        <span>Call <?php echo esc_html($phone); ?></span>
                    </a>
                    <?php endif; ?>

                    <?php if ($email): ?>
                    <a
                        href="mailto:<?php echo esc_attr($email); ?>"
                        style="display: inline-flex; align-items: center; justify-content: center; text-decoration: none; font-weight: var(--hph-font-semibold); border-radius: var(--hph-radius-lg); transition: all 300ms ease; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); padding: var(--hph-space-4) var(--hph-space-8); font-size: var(--hph-text-base); background-color: transparent; color: var(--hph-white); border: 2px solid var(--hph-white); min-width: 180px;"
                        onmouseover="this.style.transform='translateY(-2px)'"
                        onmouseout="this.style.transform='translateY(0)'"
                    >
                        <i class="fas fa-envelope" style="margin-right: var(--hph-space-3);"></i>
                        <span>Send Email</span>
                    </a>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </section>

    <?php
    // ============================================
    // Content Section - About the Staff Member
    // ============================================
    if ($bio || $skills || $years_experience) {
        get_template_part('template-parts/sections/content', null, array(
            'background' => 'light',
            'padding' => 'xl',
            'content_width' => 'normal',
            'badge' => 'About ' . ($first_name ?: $full_name),
            'headline' => 'Get to Know ' . ($full_name),
            'content' => $bio ? wp_kses_post(wpautop($bio)) : '',
            'layout' => 'single-column',
            'section_id' => 'about-staff-' . $staff_id
        ));
    }
    ?>

<?php get_footer(); ?>