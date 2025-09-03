<?php
/**
 * HPH Sitewide Header Template
 * 
 * Modern, responsive header with multiple navigation levels,
 * search functionality, and user account features
 * Location: /wp-content/themes/happy-place/header.php
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Get theme options
$site_logo = get_theme_mod('custom_logo');
$site_logo_url = wp_get_attachment_image_url($site_logo, 'full');

// Use new theme settings helper functions
$agency_phone = hph_get_agency_phone();
$agency_email = hph_get_agency_email();
$agency_hours = hph_get_agency_hours();
$social_links = hph_get_social_links();

// Check if we have a custom brand logo
$brand_logo = hph_get_brand_logo();
if ($brand_logo) {
    $site_logo_url = $brand_logo;
}

// User account
$is_logged_in = is_user_logged_in();
$current_user = wp_get_current_user();
$saved_properties_count = 0;
if ($is_logged_in) {
    $saved_properties = get_user_meta($current_user->ID, 'saved_properties', true);
    $saved_properties_count = is_array($saved_properties) ? count($saved_properties) : 0;
}

// Property search parameters
$property_types = array('All Types', 'Single Family', 'Condo', 'Townhouse', 'Multi-Family', 'Land');
$price_ranges = array(
    '' => 'Price Range',
    '0-250000' => 'Under $250k',
    '250000-500000' => '$250k - $500k',
    '500000-750000' => '$500k - $750k',
    '750000-1000000' => '$750k - $1M',
    '1000000-9999999' => 'Over $1M'
);
$bed_options = array('Beds', '1+', '2+', '3+', '4+', '5+');
$bath_options = array('Baths', '1+', '2+', '3+', '4+');

// Check if sticky header is enabled
$sticky_header = hph_is_sticky_header_enabled();

// Ensure Font Awesome is loaded
if (!wp_script_is('font-awesome', 'enqueued')) {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', array(), '6.5.1');
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="hph-site">
    <a class="skip-link screen-reader-text" href="#primary">
        <?php esc_html_e('Skip to content', 'happyplace'); ?>
    </a>

    <!-- Top Bar -->
    <div class="hph-topbar">
        <div class="hph-container">
            <div class="hph-topbar-content">
                <!-- Left: Contact Info -->
                <div class="hph-topbar-left">
                    <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9]/', '', $agency_phone)); ?>" 
                       class="hph-topbar-item">
                        <i class="fas fa-phone"></i>
                        <span><?php echo esc_html($agency_phone); ?></span>
                    </a>
                    <a href="mailto:<?php echo esc_attr($agency_email); ?>" 
                       class="hph-topbar-item hph-hide-mobile">
                        <i class="fas fa-envelope"></i>
                        <span><?php echo esc_html($agency_email); ?></span>
                    </a>
                    <div class="hph-topbar-item hph-hide-mobile">
                        <i class="fas fa-clock"></i>
                        <span><?php echo esc_html($agency_hours); ?></span>
                    </div>
                </div>
                
                <!-- Right: Social & Quick Links -->
                <div class="hph-topbar-right">
                    <!-- Social Links -->
                    <div class="hph-social-links">
                        <?php foreach ($social_links as $platform => $url) : ?>
                        <a href="<?php echo esc_url($url); ?>" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="hph-social-link"
                           aria-label="<?php echo esc_attr(ucfirst($platform)); ?>">
                            <i class="fab fa-<?php echo esc_attr($platform); ?>"></i>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Quick Links -->
                    <div class="hph-quick-links">
                        <a href="/sell" class="hph-topbar-link">
                            <i class="fas fa-tag"></i>
                            <span>Sell</span>
                        </a>
                        <a href="/mortgage" class="hph-topbar-link">
                            <i class="fas fa-calculator"></i>
                            <span>Mortgage</span>
                        </a>
                        <a href="/agents" class="hph-topbar-link">
                            <i class="fas fa-users"></i>
                            <span>Agents</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header id="masthead" class="hph-header <?php echo $sticky_header ? 'hph-sticky-header' : ''; ?>">
        <div class="hph-container">
            <div class="hph-header-content">
                
                <!-- Logo -->
                <div class="hph-logo">
                    <a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                        <?php if ($site_logo_url) : ?>
                            <img src="<?php echo esc_url($site_logo_url); ?>" 
                                 alt="<?php bloginfo('name'); ?>"
                                 class="hph-logo-img">
                        <?php else : ?>
                            <span class="hph-logo-text">
                                <?php bloginfo('name'); ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>
                
                <!-- Primary Navigation -->
                <nav class="hph-primary-nav" aria-label="Primary navigation">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'primary',
                        'menu_id' => 'primary-menu',
                        'container' => false,
                        'menu_class' => 'hph-nav-menu',
                        'fallback_cb' => 'hph_default_menu',
                        'walker' => class_exists('HPH_Mega_Menu_Walker') ? new HPH_Mega_Menu_Walker() : ''
                    ));
                    ?>
                </nav>
                
                <!-- Header Actions -->
                <div class="hph-header-actions">
                    <!-- Search Toggle -->
                    <button class="hph-action-btn hph-search-toggle" 
                            aria-label="Toggle search"
                            data-toggle="search">
                        <i class="fas fa-search"></i>
                    </button>
                    
                    <!-- Saved Listings -->
                    <?php if ($is_logged_in) : ?>
                    <a href="/my-account/saved-listings" 
                       class="hph-action-btn hph-saved-properties">
                        <i class="fas fa-heart"></i>
                        <?php if ($saved_properties_count > 0) : ?>
                        <span class="hph-badge"><?php echo esc_html($saved_properties_count); ?></span>
                        <?php endif; ?>
                    </a>
                    <?php endif; ?>
                    
                    <!-- User Account -->
                    <div class="hph-user-dropdown">
                        <button class="hph-action-btn hph-user-toggle" 
                                aria-label="User menu"
                                aria-expanded="false">
                            <i class="fas fa-user-circle"></i>
                        </button>
                        
                        <div class="hph-dropdown-menu hph-user-menu">
                            <?php if ($is_logged_in) : ?>
                                <div class="hph-user-info">
                                    <div class="hph-user-avatar">
                                        <?php echo get_avatar($current_user->ID, 60); ?>
                                    </div>
                                    <div class="hph-user-details">
                                        <span class="hph-user-greeting">Welcome back!</span>
                                        <span class="hph-user-email"><?php echo esc_html($current_user->user_email); ?></span>
                                    </div>
                                </div>
                                <div class="hph-dropdown-divider"></div>
                                <a href="/my-account" class="hph-dropdown-link">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard
                                </a>
                                <a href="/my-account/saved-listings" class="hph-dropdown-link">
                                    <i class="fas fa-heart"></i> Saved Listings
                                </a>
                                <a href="/my-account/saved-searches" class="hph-dropdown-link">
                                    <i class="fas fa-search"></i> Saved Searches
                                </a>
                                <a href="/my-account/property-alerts" class="hph-dropdown-link">
                                    <i class="fas fa-bell"></i> Property Alerts
                                </a>
                                <div class="hph-dropdown-divider"></div>
                                <a href="/my-account/settings" class="hph-dropdown-link">
                                    <i class="fas fa-cog"></i> Settings
                                </a>
                                <a href="<?php echo wp_logout_url(home_url()); ?>" class="hph-dropdown-link">
                                    <i class="fas fa-sign-out-alt"></i> Sign Out
                                </a>
                            <?php else : ?>
                                <a href="/login" class="hph-dropdown-link hph-login-link">
                                    <i class="fas fa-sign-in-alt"></i> Sign In
                                </a>
                                <a href="/register" class="hph-dropdown-link hph-register-link">
                                    <i class="fas fa-user-plus"></i> Create Account
                                </a>
                                <div class="hph-dropdown-divider"></div>
                                <p class="hph-dropdown-text">
                                    Sign in to save listings and searches
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Mobile Menu Toggle -->
                    <button class="hph-action-btn hph-mobile-toggle" 
                            aria-label="Toggle mobile menu"
                            aria-expanded="false">
                        <span class="hph-hamburger">
                            <span></span>
                            <span></span>
                            <span></span>
                        </span>
                    </button>
                </div>
                
            </div>
        </div>
        
        <!-- Search Bar (Hidden by default) -->
        <div class="hph-search-bar" data-search-bar>
            <div class="hph-container">
                <form class="hph-search-form" action="<?php echo esc_url(home_url('/advanced-search/')); ?>" method="GET">
                    <input type="hidden" name="post_type" value="listing">
                    <div class="hph-search-grid">
                        <!-- Search Input -->
                        <div class="hph-search-input-wrapper">
                            <i class="fas fa-search"></i>
                            <input type="text" 
                                   name="s" 
                                   class="hph-search-input" 
                                   placeholder="Enter city, zip, address, or MLS#"
                                   autocomplete="off">
                            <div class="hph-search-suggestions"></div>
                        </div>
                        
                        <!-- Property Type -->
                        <select name="property_type" class="hph-search-select">
                            <option value="">All Types</option>
                            <option value="single_family">Single Family</option>
                            <option value="condo">Condo</option>
                            <option value="townhouse">Townhouse</option>
                            <option value="multi_family">Multi-Family</option>
                            <option value="land">Land</option>
                        </select>
                        
                        <!-- Price Range -->
                        <select name="price_range" class="hph-search-select">
                            <option value="">Price Range</option>
                            <option value="0-250000">Under $250k</option>
                            <option value="250000-500000">$250k - $500k</option>
                            <option value="500000-750000">$500k - $750k</option>
                            <option value="750000-1000000">$750k - $1M</option>
                            <option value="1000000-9999999">Over $1M</option>
                        </select>
                        
                        <!-- Beds -->
                        <select name="bedrooms" class="hph-search-select">
                            <option value="">Beds</option>
                            <option value="1">1+</option>
                            <option value="2">2+</option>
                            <option value="3">3+</option>
                            <option value="4">4+</option>
                            <option value="5">5+</option>
                        </select>
                        
                        <!-- Baths -->
                        <select name="bathrooms" class="hph-search-select">
                            <option value="">Baths</option>
                            <option value="1">1+</option>
                            <option value="2">2+</option>
                            <option value="3">3+</option>
                            <option value="4">4+</option>
                        </select>
                        
                        <!-- Submit Button -->
                        <button type="submit" class="hph-search-submit">
                            <i class="fas fa-search"></i>
                            Search
                        </button>
                        
                        <!-- Advanced Search Link -->
                        <a href="<?php echo esc_url(home_url('/advanced-search/')); ?>" class="hph-advanced-search">
                            <i class="fas fa-sliders-h"></i>
                            Advanced Search
                        </a>
                    </div>
                </form>
                
                <!-- Quick Search Links -->
                <div class="hph-quick-searches">
                    <span class="hph-quick-label">Quick Search:</span>
                    <a href="/listings/?status=open-house" class="hph-quick-link">Open Houses</a>
                    <a href="/listings/?status=new" class="hph-quick-link">New Listings</a>
                    <a href="/listings/?feature=waterfront" class="hph-quick-link">Waterfront</a>
                    <a href="/listings/?feature=pool" class="hph-quick-link">With Pool</a>
                    <a href="/listings/?status=reduced" class="hph-quick-link">Price Reduced</a>
                </div>
            </div>
            
            <!-- Close Search Button -->
            <button class="hph-search-close" aria-label="Close search">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </header>

    <!-- Mobile Menu -->
    <div class="hph-mobile-menu" data-mobile-menu>
        <div class="hph-mobile-header">
            <div class="hph-mobile-logo">
                <?php if ($site_logo_url) : ?>
                    <img src="<?php echo esc_url($site_logo_url); ?>" 
                         alt="<?php bloginfo('name'); ?>">
                <?php else : ?>
                    <span><?php bloginfo('name'); ?></span>
                <?php endif; ?>
            </div>
            <button class="hph-mobile-close" aria-label="Close menu">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Mobile Search -->
        <div class="hph-mobile-search">
            <form action="<?php echo esc_url(home_url('/advanced-search/')); ?>" method="GET">
                <input type="hidden" name="type" value="listing">
                <div class="hph-mobile-search-input">
                    <i class="fas fa-search"></i>
                    <input type="text" 
                           name="s" 
                           placeholder="Search listings..."
                           autocomplete="off">
                </div>
            </form>
        </div>
        
        <!-- Mobile Navigation -->
        <nav class="hph-mobile-nav">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'mobile',
                'menu_id' => 'mobile-menu',
                'container' => false,
                'menu_class' => 'hph-mobile-menu-list',
                'fallback_cb' => 'hph_default_mobile_menu'
            ));
            ?>
        </nav>
        
        <!-- Mobile User Section -->
        <div class="hph-mobile-user">
            <?php if ($is_logged_in) : ?>
                <div class="hph-mobile-user-info">
                    <?php echo get_avatar($current_user->ID, 40); ?>
                    <span><?php echo esc_html($current_user->display_name); ?></span>
                </div>
                <a href="/my-account" class="hph-mobile-btn">
                    <i class="fas fa-tachometer-alt"></i> My Account
                </a>
                <a href="<?php echo wp_logout_url(home_url()); ?>" class="hph-mobile-btn">
                    <i class="fas fa-sign-out-alt"></i> Sign Out
                </a>
            <?php else : ?>
                <a href="/login" class="hph-mobile-btn hph-mobile-btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </a>
                <a href="/register" class="hph-mobile-btn">
                    <i class="fas fa-user-plus"></i> Create Account
                </a>
            <?php endif; ?>
        </div>
        
        <!-- Mobile Contact -->
        <div class="hph-mobile-contact">
            <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9]/', '', $agency_phone)); ?>" 
               class="hph-mobile-contact-btn">
                <i class="fas fa-phone"></i>
                <?php echo esc_html($agency_phone); ?>
            </a>
        </div>
    </div>
    
    <!-- Mobile Menu Overlay -->
    <div class="hph-mobile-overlay" data-mobile-overlay></div>

    <!-- Main Content Area -->
    <div id="main" class="hph-main-content" role="main">
        <div class="hph-main-wrapper">
            <div class="hph-content-container"><?php // Template content will be inserted here ?>