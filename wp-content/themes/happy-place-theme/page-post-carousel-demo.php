<?php
/**
 * Template Name: Post Carousel Demo
 * 
 * Demo page showing different post carousel configurations
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

get_header();
?>

<div style="padding: 2rem 0;">
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 1rem;">
        <h1 style="text-align: center; margin-bottom: 3rem; color: var(--hph-gray-900);">
            Post Carousel Demo
        </h1>
        
        <div style="margin-bottom: 2rem;">
            <p style="text-align: center; color: var(--hph-gray-600); font-size: 1.1rem; max-width: 600px; margin: 0 auto;">
                This page demonstrates different configurations of the simple post carousel template. 
                Each carousel automatically detects and displays ACF fields when available.
            </p>
        </div>
    </div>
</div>

<?php
// ============================================
// Example 1: Basic Post Carousel
// ============================================
?>
<h2 style="text-align: center; margin: 3rem 0 1rem; color: var(--hph-gray-800);">
    Basic Post Carousel
</h2>
<?php
get_template_part('template-parts/sections/post-carousel', null, array(
    'post_type' => 'post',
    'posts_per_page' => 3,
    'background' => 'dark',
    'height' => '50vh',
    'autoplay' => true,
    'autoplay_speed' => 4000,
    'show_dots' => true,
    'show_arrows' => true,
    'overlay' => true,
    'section_id' => 'basic-carousel'
));
?>

<?php
// ============================================
// Example 2: Featured Listings Carousel
// ============================================
?>
<h2 style="text-align: center; margin: 3rem 0 1rem; color: var(--hph-gray-800);">
    Featured Listings Carousel
</h2>
<?php
get_template_part('template-parts/sections/post-carousel', null, array(
    'post_type' => 'listing',
    'posts_per_page' => 5,
    'meta_query' => array(
        array(
            'key' => 'featured',
            'value' => '1',
            'compare' => '='
        )
    ),
    'background' => 'dark',
    'height' => '60vh',
    'autoplay' => true,
    'autoplay_speed' => 5000,
    'show_dots' => true,
    'show_arrows' => true,
    'overlay' => true,
    'section_id' => 'listings-carousel'
));
?>

<?php
// ============================================
// Example 3: Meet Our Team - Agent Carousel
// ============================================
?>
<h2 style="text-align: center; margin: 3rem 0 1rem; color: var(--hph-gray-800);">
    Meet Our Team - Agent Carousel
</h2>
<?php
get_template_part('template-parts/sections/post-carousel', null, array(
    'post_type' => 'agent',
    'posts_per_page' => 4,
    'background' => 'light',
    'height' => '55vh',
    'autoplay' => true,
    'autoplay_speed' => 6000,
    'show_dots' => true,
    'show_arrows' => true,
    'overlay' => true,
    'section_id' => 'agents-carousel'
));
?>

<?php
// ============================================
// Example 4: Explore Communities
// ============================================
?>
<h2 style="text-align: center; margin: 3rem 0 1rem; color: var(--hph-gray-800);">
    Explore Communities Carousel
</h2>
<?php
get_template_part('template-parts/sections/post-carousel', null, array(
    'post_type' => 'community',
    'posts_per_page' => 5,
    'background' => 'dark',
    'height' => '50vh',
    'autoplay' => true,
    'autoplay_speed' => 7000,
    'show_dots' => true,
    'show_arrows' => true,
    'overlay' => true,
    'section_id' => 'communities-carousel'
));
?>

<?php
// ============================================
// Example 5: City Guides
// ============================================
?>
<h2 style="text-align: center; margin: 3rem 0 1rem; color: var(--hph-gray-800);">
    City Guides Carousel
</h2>
<?php
get_template_part('template-parts/sections/post-carousel', null, array(
    'post_type' => 'city',
    'posts_per_page' => 4,
    'background' => 'light',
    'height' => '55vh',
    'autoplay' => false, // No autoplay for browsing
    'show_dots' => true,
    'show_arrows' => true,
    'overlay' => true,
    'section_id' => 'cities-carousel'
));
?>

<div style="padding: 3rem 0;">
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 1rem;">
        <div style="background: var(--hph-gray-50); padding: 2rem; border-radius: 1rem; margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1rem; color: var(--hph-gray-900);">
                Custom Post Type Integration
            </h3>
            <p style="margin-bottom: 1rem; color: var(--hph-gray-700);">
                The carousel is optimized for your Happy Place CPTs and automatically detects appropriate fields:
            </p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin: 2rem 0;">
                
                <div style="background: white; padding: 1.5rem; border-radius: 0.5rem; border-left: 4px solid var(--hph-primary);">
                    <h4 style="color: var(--hph-primary); margin-bottom: 0.5rem;">📍 Listings</h4>
                    <p style="font-size: 0.9rem; color: var(--hph-gray-600); line-height: 1.5;">
                        <strong>Badge:</strong> listing_status, property_type<br>
                        <strong>Content:</strong> property_description, listing_description<br>
                        <strong>Images:</strong> hero_image, primary_image, gallery<br>
                        <strong>Button:</strong> "View Property" → listing_url or permalink
                    </p>
                </div>
                
                <div style="background: white; padding: 1.5rem; border-radius: 0.5rem; border-left: 4px solid var(--hph-secondary);">
                    <h4 style="color: var(--hph-secondary); margin-bottom: 0.5rem;">👥 Agents & Staff</h4>
                    <p style="font-size: 0.9rem; color: var(--hph-gray-600); line-height: 1.5;">
                        <strong>Badge:</strong> agent_specialty, department<br>
                        <strong>Content:</strong> bio, biography, about<br>
                        <strong>Images:</strong> profile_photo, headshot, photo<br>
                        <strong>Button:</strong> "View Profile" → permalink
                    </p>
                </div>
                
                <div style="background: white; padding: 1.5rem; border-radius: 0.5rem; border-left: 4px solid #28a745;">
                    <h4 style="color: #28a745; margin-bottom: 0.5rem;">🏘️ Communities & Cities</h4>
                    <p style="font-size: 0.9rem; color: var(--hph-gray-600); line-height: 1.5;">
                        <strong>Badge:</strong> community_type or "City Guide"<br>
                        <strong>Content:</strong> location_description, overview<br>
                        <strong>Images:</strong> hero_image, featured_image<br>
                        <strong>Button:</strong> "Explore" → permalink
                    </p>
                </div>
            </div>
        </div>
        
        <div style="background: var(--hph-primary); color: white; padding: 2rem; border-radius: 1rem;">
            <h3 style="margin-bottom: 1rem;">
                Smart Features
            </h3>
            <ul style="line-height: 1.8; opacity: 0.9; columns: 2; gap: 2rem;">
                <li>✅ Automatic CPT-specific field detection</li>
                <li>✅ Smart placeholder images by post type</li>
                <li>✅ Responsive design with mobile-friendly touch controls</li>
                <li>✅ SEO-friendly markup with proper ARIA labels</li>
                <li>✅ Keyboard navigation support</li>
                <li>✅ Autoplay with pause on hover</li>
            </ul>
        </div>
    </div>
</div>

<?php get_footer(); ?>
