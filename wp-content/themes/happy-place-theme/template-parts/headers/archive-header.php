<?php
/**
 * Archive-specific header template
 * Fixed header with integrated search and view controls for listing archives
 */

// Ensure we have proper document structure
if (!did_action('wp_head')) {
    ?><!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php wp_head(); ?>
    </head>
    <body <?php body_class('archive-listing-no-header'); ?>>
    <?php wp_body_open(); ?>
    <div id="page" class="site">
    <?php
}

// Get current search parameters
$search = sanitize_text_field($_GET['s'] ?? $_GET['search'] ?? '');
$property_type = sanitize_text_field($_GET['property_type'] ?? '');
$min_price = intval($_GET['min_price'] ?? 0);
$max_price = intval($_GET['max_price'] ?? 0);
$zip_code = sanitize_text_field($_GET['zip_code'] ?? '');
$bedrooms = sanitize_text_field($_GET['bedrooms'] ?? '');
$bathrooms = sanitize_text_field($_GET['bathrooms'] ?? '');

// Get listings count for display
global $wp_query;
$listings_count = $wp_query->found_posts ?? 0;

// Get theme options
$agency_phone = get_option('hph_agency_phone', '(302) 555-0123');
$agency_email = get_option('hph_agency_email', 'cheers@theparkergroup.com');
$social_links = array(
    'facebook' => get_option('hph_facebook_url', '#'),
    'instagram' => get_option('hph_instagram_url', '#'),
    'linkedin' => get_option('hph_linkedin_url', '#')
);
?>

<header class="hph-archive-header">
    <div class="hph-topbar">
        <div class="hph-container">
            <div class="hph-topbar-content">
                <!-- Contact Info -->
                <div class="hph-topbar-left">
                    <a href="tel:<?php echo esc_attr(str_replace(['(', ')', ' ', '-'], '', $agency_phone)); ?>" class="hph-topbar-item">
                        <i class="fas fa-phone"></i>
                        <?php echo esc_html($agency_phone); ?>
                    </a>
                    <a href="mailto:<?php echo esc_attr($agency_email); ?>" class="hph-topbar-item">
                        <i class="fas fa-envelope"></i>
                        <?php echo esc_html($agency_email); ?>
                    </a>
                </div>
                
                <!-- Social Links -->
                <div class="hph-topbar-right">
                    <div class="hph-social-links">
                        <?php foreach ($social_links as $platform => $url) : ?>
                            <?php if (!empty($url) && $url !== '#') : ?>
                                <a href="<?php echo esc_url($url); ?>" class="hph-social-link" target="_blank" rel="noopener">
                                    <i class="fab fa-<?php echo esc_attr($platform); ?>"></i>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Header Section -->
    <div class="hph-header-section">
        <div class="hph-container">
            <div class="hph-header-content">
                <!-- Logo -->
                <div class="hph-logo">
                    <a href="<?php echo esc_url(home_url('/')); ?>">
                        <?php if (has_custom_logo()) : ?>
                            <img src="<?php echo esc_url(wp_get_attachment_image_url(get_theme_mod('custom_logo'), 'full')); ?>" 
                                 alt="<?php bloginfo('name'); ?>" class="hph-logo-img">
                        <?php else : ?>
                            <span class="hph-logo-text"><?php bloginfo('name'); ?></span>
                        <?php endif; ?>
                    </a>
                </div>
                
                <!-- Navigation -->
                <nav class="hph-main-nav">
                    <?php
                    wp_nav_menu([
                        'theme_location' => 'primary',
                        'menu_class' => 'hph-nav-menu',
                        'container' => false,
                        'fallback_cb' => false
                    ]);
                    ?>
                </nav>
                
                <!-- Header Actions -->
                <div class="hph-header-actions">
                    <?php if (is_user_logged_in()) : ?>
                        <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="hph-action-btn" title="Logout">
                            <i class="fas fa-sign-out-alt"></i>
                            <span class="hph-user-name"><?php echo esc_html(wp_get_current_user()->display_name); ?></span>
                        </a>
                    <?php else : ?>
                        <a href="<?php echo esc_url(wp_login_url()); ?>" class="hph-action-btn" title="Login">
                            <i class="fas fa-user"></i>
                        </a>
                    <?php endif; ?>
                    
                    <button class="hph-mobile-toggle" aria-label="Toggle mobile menu">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Search & Filter Section -->
    <div class="hph-search-section">
        <div class="hph-container">
            <form method="get" class="hph-archive-search-form hph-ajax-search" data-ajax-search>
                <input type="hidden" name="post_type" value="listing">
                <input type="hidden" name="action" value="hph_filter_listings">
                <div class="hph-search-row">
                    <!-- Main Search -->
                    <div class="hph-search-input">
                        <input type="text" 
                               name="s" 
                               value="<?php echo esc_attr($search); ?>" 
                               placeholder="Enter city, zip, address, or MLS#"
                               class="hph-form-input">
                    </div>
                    
                    <!-- Property Type -->
                    <div class="hph-search-filter">
                        <select name="property_type" class="hph-form-select">
                            <option value="">All Types</option>
                            <option value="single_family" <?php selected($property_type, 'single_family'); ?>>Single Family</option>
                            <option value="condo" <?php selected($property_type, 'condo'); ?>>Condo</option>
                            <option value="townhouse" <?php selected($property_type, 'townhouse'); ?>>Townhouse</option>
                            <option value="multi_family" <?php selected($property_type, 'multi_family'); ?>>Multi-Family</option>
                            <option value="land" <?php selected($property_type, 'land'); ?>>Land</option>
                        </select>
                    </div>
                    
                    <!-- Price Range -->
                    <div class="hph-search-filter">
                        <select name="min_price" class="hph-form-select">
                            <option value="">Min Price</option>
                            <option value="100000" <?php selected($min_price, 100000); ?>>$100K</option>
                            <option value="200000" <?php selected($min_price, 200000); ?>>$200K</option>
                            <option value="300000" <?php selected($min_price, 300000); ?>>$300K</option>
                            <option value="500000" <?php selected($min_price, 500000); ?>>$500K</option>
                            <option value="750000" <?php selected($min_price, 750000); ?>>$750K</option>
                            <option value="1000000" <?php selected($min_price, 1000000); ?>>$1M</option>
                        </select>
                    </div>
                    
                    <div class="hph-search-filter">
                        <select name="max_price" class="hph-form-select">
                            <option value="">Max Price</option>
                            <option value="200000" <?php selected($max_price, 200000); ?>>$200K</option>
                            <option value="300000" <?php selected($max_price, 300000); ?>>$300K</option>
                            <option value="500000" <?php selected($max_price, 500000); ?>>$500K</option>
                            <option value="750000" <?php selected($max_price, 750000); ?>>$750K</option>
                            <option value="1000000" <?php selected($max_price, 1000000); ?>>$1M</option>
                            <option value="2000000" <?php selected($max_price, 2000000); ?>>$2M+</option>
                        </select>
                    </div>
                    
                    <!-- Zip Code Search -->
                    <div class="hph-search-filter">
                        <input type="text" 
                               name="zip_code" 
                               value="<?php echo esc_attr($zip_code); ?>" 
                               placeholder="Zip Code"
                               class="hph-form-input hph-zip-search"
                               pattern="[0-9]{5}"
                               maxlength="5">
                    </div>
                    
                    <!-- Beds/Baths -->
                    <div class="hph-search-filter">
                        <select name="bedrooms" class="hph-form-select">
                            <option value="">Beds</option>
                            <option value="1" <?php selected($bedrooms, '1'); ?>>1+</option>
                            <option value="2" <?php selected($bedrooms, '2'); ?>>2+</option>
                            <option value="3" <?php selected($bedrooms, '3'); ?>>3+</option>
                            <option value="4+" <?php selected($bedrooms, '4+'); ?>>4+</option>
                        </select>
                    </div>
                    
                    <div class="hph-search-filter">
                        <select name="bathrooms" class="hph-form-select">
                            <option value="">Baths</option>
                            <option value="1" <?php selected($bathrooms, '1'); ?>>1+</option>
                            <option value="2" <?php selected($bathrooms, '2'); ?>>2+</option>
                            <option value="3+" <?php selected($bathrooms, '3+'); ?>>3+</option>
                        </select>
                    </div>
                    
                    <!-- Search Button -->
                    <div class="hph-search-action">
                        <button type="submit" class="hph-btn hph-btn-primary">
                            <i class="fas fa-search"></i>
                            Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</header>
