<?php
/**
 * Single Agent Template - Enhanced with Hero, Bio, Listings, and Content Areas
 * 
 * Features complete agent profile with:
 * - Hero section with photo and key info
 * - Bio and credentials section
 * - Contact information with social links
 * - Agent's current listings
 * - Agent blog/content area
 * 
 * @package HappyPlaceTheme
 * @since 3.2.0
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Get agent ID and verify it exists
$agent_id = get_the_ID();
if (!$agent_id || get_post_type($agent_id) !== 'agent') {
    get_template_part('template-parts/base/content-none');
    get_footer();
    return;
}

// Get agent data
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
$office_address = '';
if ($office_id) {
    $office_name = get_the_title($office_id);
    $office_address = get_field('address', $office_id);
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
    $agent_photo = $agent_photo['sizes']['large'] ?? $agent_photo['url'];
} elseif ($agent_photo && is_numeric($agent_photo)) {
    // If it's an attachment ID
    $agent_photo = wp_get_attachment_image_url($agent_photo, 'large');
} elseif (!$agent_photo) {
    // Fallback to featured image if profile photo not set
    $agent_photo = get_the_post_thumbnail_url($agent_id, 'large');
}

// Final fallback to placeholder
if (!$agent_photo) {
    $agent_photo = get_template_directory_uri() . '/assets/images/placeholder-agent.jpg';
}
?>

<div class="single-agent-page" data-agent-id="<?php echo esc_attr($agent_id); ?>">
    
    <!-- Hero Section -->
    <section class="hero hero-agent">
        <div class="hero-background">
            <?php if ($agent_photo): ?>
                <div class="hero-image-bg" style="background-image: url('<?php echo esc_url($agent_photo); ?>')"></div>
                <div class="hero-overlay"></div>
            <?php endif; ?>
        </div>
        
        <div class="container">
            <div class="hero-content">
                <div class="row align-items-center">
                    
                    <!-- Agent Photo -->
                    <div class="col-lg-4 text-center mb-6 mb-lg-0">
                        <div class="agent-hero-photo">
                            <img src="<?php echo esc_url($agent_photo); ?>" 
                                 alt="<?php echo esc_attr($full_name); ?>" 
                                 class="agent-photo-large">
                            
                            <?php if ($featured): ?>
                                <div class="featured-badge">
                                    <i class="fas fa-star"></i>
                                    <span><?php _e('Featured Agent', 'happy-place-theme'); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Agent Info -->
                    <div class="col-lg-8">
                        <div class="agent-hero-info">
                            <h1 class="agent-name"><?php echo esc_html($full_name); ?></h1>
                            
                            <?php if ($title): ?>
                                <p class="agent-title"><?php echo esc_html($title); ?></p>
                            <?php endif; ?>
                            
                            <?php if ($office_name): ?>
                                <p class="agent-office">
                                    <i class="fas fa-building"></i>
                                    <?php echo esc_html($office_name); ?>
                                </p>
                            <?php endif; ?>
                            
                            <!-- Quick Stats -->
                            <div class="agent-quick-stats">
                                <?php if ($years_experience): ?>
                                    <div class="stat-item">
                                        <span class="stat-number"><?php echo esc_html($years_experience); ?></span>
                                        <span class="stat-label"><?php _e('Years Experience', 'happy-place-theme'); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($total_listings_sold): ?>
                                    <div class="stat-item">
                                        <span class="stat-number"><?php echo esc_html($total_listings_sold); ?></span>
                                        <span class="stat-label"><?php _e('Properties Sold', 'happy-place-theme'); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($total_sales_volume): ?>
                                    <div class="stat-item">
                                        <span class="stat-number"><?php echo esc_html(number_format($total_sales_volume / 1000000, 1)); ?>M</span>
                                        <span class="stat-label"><?php _e('Sales Volume', 'happy-place-theme'); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Contact Actions -->
                            <div class="agent-hero-actions">
                                <?php if ($phone): ?>
                                    <a href="tel:<?php echo esc_attr($phone); ?>" class="btn btn-primary">
                                        <i class="fas fa-phone"></i>
                                        <?php echo esc_html($phone); ?>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($email): ?>
                                    <a href="mailto:<?php echo esc_attr($email); ?>" class="btn btn-outline">
                                        <i class="fas fa-envelope"></i>
                                        <?php _e('Send Email', 'happy-place-theme'); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </section>
    
    <!-- Bio & Credentials Section -->
    <section class="agent-bio-section py-12">
        <div class="container">
            <div class="row">
                
                <!-- Bio Content -->
                <div class="col-lg-8">
                    <div class="bio-content">
                        <h2 class="section-title"><?php _e('About', 'happy-place-theme'); ?> <?php echo esc_html($first_name ?: $full_name); ?></h2>
                        
                        <?php if ($bio): ?>
                            <div class="bio-text">
                                <?php echo wp_kses_post(wpautop($bio)); ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Specialties -->
                        <?php if ($specialties && is_array($specialties)): ?>
                            <div class="agent-specialties mt-6">
                                <h3><?php _e('Specialties', 'happy-place-theme'); ?></h3>
                                <div class="specialty-tags">
                                    <?php foreach ($specialties as $specialty): ?>
                                        <span class="specialty-tag"><?php echo esc_html($specialty['label'] ?? $specialty); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Languages -->
                        <?php if ($languages && is_array($languages)): ?>
                            <div class="agent-languages mt-4">
                                <h3><?php _e('Languages', 'happy-place-theme'); ?></h3>
                                <div class="language-list">
                                    <?php echo esc_html(implode(', ', array_map(function($lang) {
                                        return $lang['label'] ?? $lang;
                                    }, $languages))); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Contact Info Sidebar -->
                <div class="col-lg-4">
                    <div class="agent-contact-card">
                        <h3><?php _e('Contact Information', 'happy-place-theme'); ?></h3>
                        
                        <div class="contact-details">
                            <?php if ($phone): ?>
                                <div class="contact-item">
                                    <i class="fas fa-phone"></i>
                                    <a href="tel:<?php echo esc_attr($phone); ?>"><?php echo esc_html($phone); ?></a>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($email): ?>
                                <div class="contact-item">
                                    <i class="fas fa-envelope"></i>
                                    <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($office_address): ?>
                                <div class="contact-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo esc_html($office_address); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($license_number): ?>
                                <div class="contact-item">
                                    <i class="fas fa-id-card"></i>
                                    <span><?php _e('License:', 'happy-place-theme'); ?> <?php echo esc_html($license_number); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Social Media -->
                        <?php if ($facebook || $instagram || $linkedin || $twitter): ?>
                            <div class="social-links">
                                <h4><?php _e('Follow Me', 'happy-place-theme'); ?></h4>
                                <div class="social-icons">
                                    <?php if ($facebook): ?>
                                        <a href="<?php echo esc_url($facebook); ?>" target="_blank" rel="noopener">
                                            <i class="fab fa-facebook"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($instagram): ?>
                                        <a href="<?php echo esc_url($instagram); ?>" target="_blank" rel="noopener">
                                            <i class="fab fa-instagram"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($linkedin): ?>
                                        <a href="<?php echo esc_url($linkedin); ?>" target="_blank" rel="noopener">
                                            <i class="fab fa-linkedin"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($twitter): ?>
                                        <a href="<?php echo esc_url($twitter); ?>" target="_blank" rel="noopener">
                                            <i class="fab fa-twitter"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Contact Form -->
                        <div class="agent-contact-form mt-6">
                            <h4><?php _e('Send a Message', 'happy-place-theme'); ?></h4>
                            <form class="contact-form" data-agent-id="<?php echo esc_attr($agent_id); ?>">
                                <div class="form-group">
                                    <input type="text" name="name" placeholder="<?php _e('Your Name', 'happy-place-theme'); ?>" required>
                                </div>
                                <div class="form-group">
                                    <input type="email" name="email" placeholder="<?php _e('Your Email', 'happy-place-theme'); ?>" required>
                                </div>
                                <div class="form-group">
                                    <input type="tel" name="phone" placeholder="<?php _e('Your Phone', 'happy-place-theme'); ?>">
                                </div>
                                <div class="form-group">
                                    <textarea name="message" rows="4" placeholder="<?php _e('Your Message', 'happy-place-theme'); ?>" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <?php _e('Send Message', 'happy-place-theme'); ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </section>
    
    <!-- Agent Listings Section -->
    <section class="agent-listings-section py-12 bg-gray-50">
        <div class="container">
            <div class="section-header text-center mb-8">
                <h2 class="section-title">
                    <?php printf(__('%s\'s Current Listings', 'happy-place-theme'), esc_html($first_name ?: $full_name)); ?>
                </h2>
                <p class="section-subtitle">
                    <?php _e('Browse available properties from this agent', 'happy-place-theme'); ?>
                </p>
            </div>
            
            <?php
            // Query agent's listings
            $listings_args = array(
                'post_type' => 'listing',
                'posts_per_page' => 6,
                'post_status' => 'publish',
                'meta_query' => array(
                    array(
                        'key' => 'agent',
                        'value' => $agent_id,
                        'compare' => '='
                    )
                ),
                'orderby' => 'date',
                'order' => 'DESC'
            );
            
            $listings_query = new WP_Query($listings_args);
            ?>
            
            <?php if ($listings_query->have_posts()): ?>
                <div class="listings-grid">
                    <div class="row">
                        <?php while ($listings_query->have_posts()): $listings_query->the_post(); ?>
                            <div class="col-lg-4 col-md-6 mb-6">
                                <?php get_template_part('template-parts/listing-card', null, array('show_agent' => false)); ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                
                <?php if ($listings_query->found_posts > 6): ?>
                    <div class="text-center mt-8">
                        <a href="<?php echo esc_url(add_query_arg('agent', $agent_id, get_post_type_archive_link('listing'))); ?>" 
                           class="btn btn-primary">
                            <?php printf(__('View All %d Listings', 'happy-place-theme'), $listings_query->found_posts); ?>
                        </a>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="no-listings text-center py-8">
                    <i class="fas fa-home fa-3x text-gray-400 mb-4"></i>
                    <h3 class="text-gray-600"><?php _e('No Current Listings', 'happy-place-theme'); ?></h3>
                    <p class="text-gray-500"><?php printf(__('%s doesn\'t have any active listings at this time.', 'happy-place-theme'), esc_html($first_name ?: $full_name)); ?></p>
                </div>
            <?php endif; ?>
            
            <?php wp_reset_postdata(); ?>
        </div>
    </section>
    
    <!-- Agent Blog/Content Area -->
    <section class="agent-content-section py-12">
        <div class="container">
            <div class="row">
                
                <!-- Main Content -->
                <div class="col-lg-8">
                    <div class="agent-blog-content">
                        <h2 class="section-title">
                            <?php printf(__('Latest from %s', 'happy-place-theme'), esc_html($first_name ?: $full_name)); ?>
                        </h2>
                        
                        <?php
                        // Query agent's blog posts/content
                        $content_args = array(
                            'post_type' => 'post',
                            'posts_per_page' => 5,
                            'post_status' => 'publish',
                            'meta_query' => array(
                                array(
                                    'key' => 'agent_author',
                                    'value' => $agent_id,
                                    'compare' => '='
                                )
                            ),
                            'orderby' => 'date',
                            'order' => 'DESC'
                        );
                        
                        $content_query = new WP_Query($content_args);
                        ?>
                        
                        <?php if ($content_query->have_posts()): ?>
                            <div class="agent-blog-posts">
                                <?php while ($content_query->have_posts()): $content_query->the_post(); ?>
                                    <article class="blog-post-item">
                                        <div class="row align-items-center">
                                            
                                            <?php if (has_post_thumbnail()): ?>
                                                <div class="col-md-4">
                                                    <div class="post-thumbnail">
                                                        <a href="<?php the_permalink(); ?>">
                                                            <?php the_post_thumbnail('medium', array('class' => 'img-fluid rounded')); ?>
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="col-md-8">
                                            <?php else: ?>
                                                <div class="col-12">
                                            <?php endif; ?>
                                            
                                                <div class="post-content">
                                                    <div class="post-meta">
                                                        <span class="post-date">
                                                            <i class="fas fa-calendar"></i>
                                                            <?php echo get_the_date(); ?>
                                                        </span>
                                                        <span class="post-category">
                                                            <?php
                                                            $categories = get_the_category();
                                                            if (!empty($categories)) {
                                                                echo '<i class="fas fa-tag"></i> ' . esc_html($categories[0]->name);
                                                            }
                                                            ?>
                                                        </span>
                                                    </div>
                                                    
                                                    <h3 class="post-title">
                                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                                    </h3>
                                                    
                                                    <div class="post-excerpt">
                                                        <?php echo wp_trim_words(get_the_excerpt(), 25, '...'); ?>
                                                    </div>
                                                    
                                                    <a href="<?php the_permalink(); ?>" class="read-more">
                                                        <?php _e('Read More', 'happy-place-theme'); ?>
                                                        <i class="fas fa-arrow-right"></i>
                                                    </a>
                                                </div>
                                                
                                            </div>
                                        </div>
                                    </article>
                                <?php endwhile; ?>
                            </div>
                            
                            <div class="text-center mt-6">
                                <a href="<?php echo esc_url(add_query_arg('agent_author', $agent_id, get_permalink(get_option('page_for_posts')))); ?>" 
                                   class="btn btn-outline">
                                    <?php printf(__('View All Posts by %s', 'happy-place-theme'), esc_html($first_name ?: $full_name)); ?>
                                </a>
                            </div>
                            
                        <?php else: ?>
                            <div class="no-content text-center py-6">
                                <i class="fas fa-pen fa-2x text-gray-400 mb-3"></i>
                                <h4 class="text-gray-600"><?php _e('No Blog Posts Yet', 'happy-place-theme'); ?></h4>
                                <p class="text-gray-500"><?php printf(__('%s hasn\'t published any blog posts yet.', 'happy-place-theme'), esc_html($first_name ?: $full_name)); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php wp_reset_postdata(); ?>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="agent-sidebar">
                        
                        <!-- Quick Contact -->
                        <div class="sidebar-widget contact-widget">
                            <h4 class="widget-title"><?php _e('Quick Contact', 'happy-place-theme'); ?></h4>
                            <div class="quick-contact-actions">
                                <?php if ($phone): ?>
                                    <a href="tel:<?php echo esc_attr($phone); ?>" class="btn btn-primary btn-block">
                                        <i class="fas fa-phone"></i>
                                        <?php _e('Call Now', 'happy-place-theme'); ?>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($email): ?>
                                    <a href="mailto:<?php echo esc_attr($email); ?>" class="btn btn-outline btn-block">
                                        <i class="fas fa-envelope"></i>
                                        <?php _e('Send Email', 'happy-place-theme'); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Agent Stats -->
                        <div class="sidebar-widget stats-widget">
                            <h4 class="widget-title"><?php _e('Performance Stats', 'happy-place-theme'); ?></h4>
                            <div class="agent-stats">
                                <?php if ($years_experience): ?>
                                    <div class="stat-item">
                                        <div class="stat-number"><?php echo esc_html($years_experience); ?></div>
                                        <div class="stat-label"><?php _e('Years Experience', 'happy-place-theme'); ?></div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($total_listings_sold): ?>
                                    <div class="stat-item">
                                        <div class="stat-number"><?php echo esc_html($total_listings_sold); ?></div>
                                        <div class="stat-label"><?php _e('Properties Sold', 'happy-place-theme'); ?></div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($total_sales_volume): ?>
                                    <div class="stat-item">
                                        <div class="stat-number">$<?php echo esc_html(number_format($total_sales_volume / 1000000, 1)); ?>M</div>
                                        <div class="stat-label"><?php _e('Sales Volume', 'happy-place-theme'); ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Office Info -->
                        <?php if ($office_name): ?>
                            <div class="sidebar-widget office-widget">
                                <h4 class="widget-title"><?php _e('Office Information', 'happy-place-theme'); ?></h4>
                                <div class="office-info">
                                    <h5><?php echo esc_html($office_name); ?></h5>
                                    <?php if ($office_address): ?>
                                        <p class="office-address">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <?php echo esc_html($office_address); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                </div>
                
            </div>
        </div>
    </section>
    
</div>

<?php
// Enqueue single-agent specific styles
wp_enqueue_style('single-agent', get_template_directory_uri() . '/dist/css/single-agent.D-FFQA1S.css', array(), '1.0.0');

/**
 * JavaScript for enhanced functionality
 */
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Contact form handling
    const contactForm = document.querySelector('.contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'agent_contact_form');
            formData.append('agent_id', '<?php echo esc_js($agent_id); ?>');
            formData.append('nonce', '<?php echo wp_create_nonce('agent_contact_nonce'); ?>');
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?php _e('Sending...', 'happy-place-theme'); ?>';
            submitBtn.disabled = true;
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    this.innerHTML = '<div class="alert alert-success"><i class="fas fa-check"></i> <?php _e('Message sent successfully!', 'happy-place-theme'); ?></div>';
                } else {
                    // Show error message
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-danger';
                    errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + (data.data || '<?php _e('Failed to send message. Please try again.', 'happy-place-theme'); ?>');
                    this.insertBefore(errorDiv, this.firstChild);
                    
                    // Reset button
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger';
                errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> <?php _e('An error occurred. Please try again.', 'happy-place-theme'); ?>';
                this.insertBefore(errorDiv, this.firstChild);
                
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Analytics tracking for agent interactions
    function trackAgentInteraction(action, element) {
        if (typeof gtag !== 'undefined') {
            gtag('event', 'agent_interaction', {
                'agent_id': '<?php echo esc_js($agent_id); ?>',
                'agent_name': '<?php echo esc_js($full_name); ?>',
                'action': action,
                'element': element
            });
        }
    }
    
    // Track phone clicks
    document.querySelectorAll('a[href^="tel:"]').forEach(link => {
        link.addEventListener('click', function() {
            trackAgentInteraction('phone_click', 'hero_phone');
        });
    });
    
    // Track email clicks
    document.querySelectorAll('a[href^="mailto:"]').forEach(link => {
        link.addEventListener('click', function() {
            trackAgentInteraction('email_click', 'hero_email');
        });
    });
    
    // Track social media clicks
    document.querySelectorAll('.social-icons a').forEach(link => {
        link.addEventListener('click', function() {
            const platform = this.querySelector('i').className.includes('facebook') ? 'facebook' : 
                            this.querySelector('i').className.includes('instagram') ? 'instagram' :
                            this.querySelector('i').className.includes('linkedin') ? 'linkedin' : 'twitter';
            trackAgentInteraction('social_click', platform);
        });
    });
});
</script>

<?php get_footer(); ?>