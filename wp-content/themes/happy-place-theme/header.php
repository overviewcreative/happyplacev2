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

// Use fallback values for contact info
$agency_phone = get_option('hph_agency_phone', '(302) 555-0123');
$agency_email = get_option('hph_agency_email', 'info@happyplace.com');
$agency_hours = get_option('hph_agency_hours', 'Mon-Fri 9AM-6PM');
$social_links = array(
    'facebook' => get_option('hph_facebook_url', '#'),
    'instagram' => get_option('hph_instagram_url', '#'),
    'linkedin' => get_option('hph_linkedin_url', '#')
);

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
$sticky_header = get_theme_mod('sticky_header_enabled', true);

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

    <!-- Header Wrapper for Archive Mode Styling -->
    <div class="hph-header-wrapper">
        <!-- Top Bar -->
        <div class="hph-topbar">
            <div class="hph-container">
                <div class="hph-topbar-content">
                    <!-- Left: Phone -->
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
                </div>
                
                <!-- Right: Email (Mobile) + Social & Quick Links (Desktop) -->
                <div class="hph-topbar-right">
                    <!-- Email for mobile space-between layout -->
                    <a href="mailto:<?php echo esc_attr($agency_email); ?>" 
                       class="hph-topbar-item hph-show-mobile">
                        <i class="fas fa-envelope"></i>
                        <span><?php echo esc_html($agency_email); ?></span>
                    </a>
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
                        <a href="/sellers/" class="hph-topbar-link">
                            <i class="fas fa-tag"></i>
                            <span>Sell</span>
                        </a>
                        <a href="/mortgages/" class="hph-topbar-link">
                            <i class="fas fa-calculator"></i>
                            <span>Mortgage</span>
                        </a>
                        <a href="/contact/" class="hph-topbar-link modal-trigger" 
                           data-modal-form="general-contact" 
                           data-modal-title="Contact Us" 
                           data-modal-subtitle="Send us a message and we'll get back to you soon.">
                            <i class="fas fa-users"></i>
                            <span>Contact</span>
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
                        'fallback_cb' => 'hph_default_fallback_menu'
                    ));
                    
                    // Fallback menu function
                    function hph_default_fallback_menu() {
                        echo '<ul class="hph-nav-menu">';
                        echo '<li><a href="' . esc_url(home_url('/')) . '">Home</a></li>';
                        echo '<li><a href="' . esc_url(home_url('/listings/')) . '">Listings</a></li>';
                        echo '<li><a href="' . esc_url(home_url('/buyers/')) . '">Find Your Happy Place</a></li>';
                        echo '<li><a href="' . esc_url(home_url('/sellers/')) . '">List With Us</a></li>';
                        echo '<li><a href="' . esc_url(home_url('/mortgages/')) . '">Mortgages</a></li>';
                        echo '<li><a href="' . esc_url(home_url('/about/')) . '">About</a></li>';
                        echo '<li><a href="' . esc_url(home_url('/contact/')) . '" data-no-modal>Contact</a></li>';
                        echo '</ul>';
                    }
                    ?>
                </nav>
                
                <!-- Page Title (shown when scrolled) -->
                <div class="hph-header-title">
                    <h1><?php 
                        if (is_singular()) {
                            echo esc_html(get_the_title());
                        } elseif (is_home() || is_front_page()) {
                            echo esc_html(get_bloginfo('name'));
                        } elseif (is_post_type_archive('listing')) {
                            echo esc_html__('Property Listings', 'happy-place-theme');
                        } elseif (is_page()) {
                            echo esc_html(get_the_title());
                        } else {
                            echo esc_html(wp_get_document_title());
                        }
                    ?></h1>
                </div>
                
                <!-- Header Actions -->
                <div class="hph-header-actions">
                    <!-- Search Toggle -->
                    <button class="hph-search-toggle" data-search-toggle aria-label="Toggle search">
                        <i class="fas fa-search"></i>
                    </button>
                    
                    <!-- Mobile Menu Toggle -->
                    <button class="hph-mobile-toggle" data-mobile-toggle aria-label="Toggle mobile menu">
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
                <form class="hph-search-form" action="<?php echo esc_url(home_url('/listings/')); ?>" method="GET">
                    <input type="hidden" name="post_type" value="listing">
                    <div class="hph-search-grid">
                        <!-- Search Input -->
                        <div class="hph-search-input-wrapper">
                            <i class="fas fa-search"></i>
                            <input type="text" 
                                   name="s" 
                                   id="header-search-input"
                                   class="hph-search-input" 
                                   placeholder="Enter city, zip, address, or MLS#"
                                   autocomplete="off">
                            <?php get_template_part('template-parts/components/search/search-autocomplete', null, [
                                'input_id' => 'header-search-input',
                                'container_id' => 'header-search-autocomplete',
                                'post_types' => ['listing', 'agent', 'city', 'community'],
                                'max_suggestions' => 8
                            ]); ?>
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
                        
                        
                        <?php if (is_post_type_archive('listing') || is_page_template('archive-listing.php')) : ?>
                        <!-- View Controls - Only shown on listing archive pages -->
                        <div class="hph-view-controls hph-search-view-controls">
                            <div class="hph-view-modes" role="tablist">
                                <button type="button" class="hph-view-btn active" data-view="grid" role="tab" aria-selected="true" title="Grid View">
                                    <i class="fas fa-th-large"></i>
                                    <span class="hph-view-label">Grid</span>
                                </button>
                                <button type="button" class="hph-view-btn" data-view="list" role="tab" aria-selected="false" title="List View">
                                    <i class="fas fa-list"></i>
                                    <span class="hph-view-label">List</span>
                                </button>
                                <button type="button" class="hph-view-btn" data-view="map" role="tab" aria-selected="false" title="Map View">
                                    <i class="fas fa-map-marked-alt"></i>
                                    <span class="hph-view-label">Map</span>
                                </button>
                            </div>
                        </div>
                        
                        <script>
                        // Integrate header view controls with archive functionality
                        document.addEventListener('DOMContentLoaded', function() {
                            if (typeof window.ArchiveListingEnhanced !== 'undefined') {
                                // Sync header view controls with archive view controls
                                const headerViewBtns = document.querySelectorAll('.hph-search-view-controls .hph-view-btn');
                                
                                headerViewBtns.forEach(btn => {
                                    btn.addEventListener('click', function() {
                                        // Update header button states
                                        headerViewBtns.forEach(b => {
                                            b.classList.remove('active');
                                            b.setAttribute('aria-selected', 'false');
                                        });
                                        
                                        this.classList.add('active');
                                        this.setAttribute('aria-selected', 'true');
                                    });
                                });
                            }
                        });
                        </script>
                        <?php endif; ?>
                    </div>
                </form>
                
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const searchForm = document.querySelector('.hph-search-form');
                    if (searchForm) {
                        searchForm.addEventListener('submit', function(e) {
                            // Remove empty parameters before submission
                            const formData = new FormData(this);
                            const url = new URL(this.action);
                            
                            // Clear existing search params
                            url.search = '';
                            
                            // Add only non-empty values
                            for (let [key, value] of formData.entries()) {
                                if (value && value.trim() !== '') {
                                    url.searchParams.set(key, value);
                                }
                            }
                            
                            // Redirect to clean URL
                            e.preventDefault();
                            window.location.href = url.toString();
                        });
                    }
                });
                </script>
                
                <!-- Quick Search Links -->
                <div class="hph-quick-searches">
                    <span class="hph-quick-label">Browse By Type:</span>
                    <a href="/listings/?property_type=single-family" class="hph-quick-link">Single Family</a>
                    <a href="/listings/?property_type=condo" class="hph-quick-link">Condos</a>
                    <a href="/listings/?property_type=townhouse" class="hph-quick-link">Townhouses</a>
                    <a href="/listings/?property_type=multi-family" class="hph-quick-link">Multi-Family</a>
                    <a href="/listings/?property_type=land" class="hph-quick-link">Land</a>
                </div>
            </div>
            
            <!-- Close Search Button -->
            <button class="hph-search-close" aria-label="Close search">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </header>
    </div><!-- End .hph-header-wrapper -->

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
            <form action="<?php echo esc_url(home_url('/listings/')); ?>" method="GET">
                <input type="hidden" name="post_type" value="listing">
                <div class="hph-mobile-search-input">
                    <i class="fas fa-search"></i>
                    <input type="text" 
                           name="s" 
                           id="mobile-search-input"
                           placeholder="Search listings..."
                           autocomplete="off">
                    <?php get_template_part('template-parts/components/search/search-autocomplete', null, [
                        'input_id' => 'mobile-search-input',
                        'container_id' => 'mobile-search-autocomplete',
                        'post_types' => ['listing', 'agent', 'city', 'community'],
                        'max_suggestions' => 6
                    ]); ?>
                </div>
            </form>
        </div>
        
        <!-- Mobile Navigation -->
        <nav class="hph-mobile-nav">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'primary',
                'menu_id' => 'mobile-menu',
                'container' => false,
                'menu_class' => 'hph-mobile-menu-list',
                'fallback_cb' => false
            ));
            ?>
        </nav>
        
        
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
