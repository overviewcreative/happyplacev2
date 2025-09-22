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
$agency_email = get_option('hph_agency_email', 'cheers@theparkergroup.com');
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
	<meta name="google-site-verification" content="mifTc9pGwZbXzVU_5z7QGdSDO8v_s2RdGiRM7pCOeZ0" />
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="hph-site">
    <a class="skip-link screen-reader-text" href="#primary">
        <?php esc_html_e('Skip to content', 'happyplace'); ?>
    </a>

    <!-- Site Header Wrapper -->
    <div class="hph-site-header-enhanced__wrapper">
        <!-- Top Bar -->
        <div class="hph-topbar hph-site-header-enhanced__topbar">
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
                       class="hph-topbar-item hph-topbar-item--hide-mobile">
                        <i class="fas fa-envelope"></i>
                        <span><?php echo esc_html($agency_email); ?></span>
                    </a>
                </div>

                <!-- Right: Social & Quick Links (Desktop) -->
                <div class="hph-topbar-right">
                    <!-- Social Links -->
                    <div class="hph-social-links hph-social-links--header">
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
                    <div class="hph-topbar-quick-links">
                        <a href="/sellers/" class="hph-topbar-quick-link hph-topbar-link--hide-mobile">
                            <i class="fas fa-tag"></i>
                            <span>Sell</span>
                        </a>
                        <a href="https://search.parkergroupsells.com/" class="hph-topbar-quick-link hph-topbar-link--hide-mobile" target="_blank" rel="noopener noreferrer">
                            <i class="fas fa-search"></i>
                            <span>Market Wide Search</span>
                        </a>
                        <button type="button" class="hph-topbar-quick-link hph-topbar-link--hide-mobile modal-trigger"
                           data-modal-id="hph-form-modal"
                           data-modal-form="general-contact"
                           data-modal-title="Contact Us"
                           data-modal-subtitle="Send us a message and we'll get back to you soon.">
                            <i class="fas fa-users"></i>
                            <span>Contact</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header id="masthead" class="hph-site-header-enhanced <?php echo $sticky_header ? 'hph-site-header-enhanced--sticky' : ''; ?>">
        <div class="hph-container">
            <div class="hph-site-header-enhanced__content">

                <!-- Logo -->
                <div class="hph-logo hph-site-header-enhanced__logo">
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
                <nav class="hph-site-header-enhanced__nav" aria-label="Primary navigation">
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
                        echo '<li><a href="#" class="modal-trigger" data-modal-form="general-contact" data-modal-title="Contact Us" data-modal-subtitle="Send us a message and we\'ll get back to you soon." target="_self" rel="noopener">Contact</a></li>';
                        echo '</ul>';
                    }
                    ?>
                </nav>

                <!-- Page Title (shown when scrolled) -->
                <div class="hph-site-header-enhanced__title">
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
                <div class="hph-header-actions hph-site-header-enhanced__actions">
                    <!-- Search Toggle -->
                    <button class="hph-header-actions__btn hph-header-actions__btn--search hph-site-header-enhanced__search-toggle"
                            data-search-toggle aria-label="Toggle search">
                        <i class="fas fa-search hph-header-actions__icon"></i>
                    </button>

                    <!-- Mobile Menu Toggle -->
                    <button class="hph-header-actions__btn hph-header-actions__btn--menu hph-site-header-enhanced__mobile-toggle"
                            data-mobile-toggle aria-label="Toggle mobile menu">
                        <div class="hph-header-actions__hamburger">
                            <span class="hph-header-actions__hamburger-line"></span>
                            <span class="hph-header-actions__hamburger-line"></span>
                            <span class="hph-header-actions__hamburger-line"></span>
                        </div>
                    </button>
                </div>

            </div>
        </div>
        
        <!-- Search Bar (Hidden by default) -->
        <div class="hph-site-header-enhanced__search-bar" data-search-bar>
            <div class="hph-container">
                <form class="hph-site-header-enhanced__search-form hph-search-form" action="<?php echo esc_url(home_url('/listings/')); ?>" method="GET">
                    <input type="hidden" name="post_type" value="listing">

                    <!-- Search Input -->
                    <div class="hph-search-input-container">
                        <div class="hph-search-input-wrapper hph-search-input-wrapper--header has-close">
                            <input type="text"
                                   name="s"
                                   id="header-search-input"
                                   class="hph-form-input hph-search-input hph-search-input--header"
                                   placeholder="Enter city, zip, address, or MLS#"
                                   autocomplete="off">
                            <i class="fas fa-search hph-search-input-icon"></i>
                            <button type="button" class="hph-header-actions__btn hph-header-actions__btn--clear" aria-label="Clear search">
                                <i class="fas fa-times hph-header-actions__icon"></i>
                            </button>
                        </div>
                        <?php get_template_part('template-parts/components/search/search-autocomplete', null, [
                            'input_id' => 'header-search-input',
                            'container_id' => 'header-search-autocomplete',
                            'post_types' => ['listing', 'agent', 'city', 'community'],
                            'max_suggestions' => 8
                        ]); ?>
                    </div>

                    <!-- Property Type removed - handled by filter chips -->

                    <!-- Min Price -->
                    <select name="min_price" class="hph-form-select hph-site-header-enhanced__search-select">
                        <option value="">Min Price</option>
                        <option value="100000">$100k</option>
                        <option value="150000">$150k</option>
                        <option value="200000">$200k</option>
                        <option value="250000">$250k</option>
                        <option value="300000">$300k</option>
                        <option value="400000">$400k</option>
                        <option value="500000">$500k</option>
                        <option value="600000">$600k</option>
                        <option value="700000">$700k</option>
                        <option value="800000">$800k</option>
                        <option value="900000">$900k</option>
                        <option value="1000000">$1M</option>
                        <option value="1250000">$1.25M</option>
                        <option value="1500000">$1.5M</option>
                        <option value="2000000">$2M</option>
                    </select>

                    <!-- Max Price -->
                    <select name="max_price" class="hph-form-select hph-site-header-enhanced__search-select">
                        <option value="">Max Price</option>
                        <option value="150000">$150k</option>
                        <option value="200000">$200k</option>
                        <option value="250000">$250k</option>
                        <option value="300000">$300k</option>
                        <option value="400000">$400k</option>
                        <option value="500000">$500k</option>
                        <option value="600000">$600k</option>
                        <option value="700000">$700k</option>
                        <option value="800000">$800k</option>
                        <option value="900000">$900k</option>
                        <option value="1000000">$1M</option>
                        <option value="1250000">$1.25M</option>
                        <option value="1500000">$1.5M</option>
                        <option value="2000000">$2M</option>
                        <option value="3000000">$3M+</option>
                    </select>

                    <!-- Beds -->
                    <select name="bedrooms" class="hph-form-select hph-site-header-enhanced__search-select">
                        <option value="">Beds</option>
                        <option value="1">1+</option>
                        <option value="2">2+</option>
                        <option value="3">3+</option>
                        <option value="4">4+</option>
                        <option value="5">5+</option>
                    </select>

                    <!-- Baths -->
                    <select name="bathrooms" class="hph-form-select hph-site-header-enhanced__search-select">
                        <option value="">Baths</option>
                        <option value="1">1+</option>
                        <option value="2">2+</option>
                        <option value="3">3+</option>
                        <option value="4">4+</option>
                    </select>

                    <!-- Submit Button -->
                    <button type="submit" class="hph-btn hph-btn-primary hph-site-header-enhanced__search-submit">
                        <i class="fas fa-search hph-btn-icon"></i>
                        Search
                    </button>
                        
                        
                        <?php if (is_post_type_archive('listing') || is_page_template('archive-listing.php')) : ?>
                        <!-- View Controls - Only shown on listing archive pages -->
                        <div class="hph-view-controls hph-search-view-controls">
                            <div class="hph-view-modes" role="tablist">
                                <button type="button" class="hph-view-btn is-active" data-view="grid" role="tab" aria-selected="true" title="Grid View">
                                    <i class="fas fa-th-large hph-btn-icon"></i>
                                    <span class="hph-view-label">Grid</span>
                                </button>
                                <button type="button" class="hph-view-btn" data-view="list" role="tab" aria-selected="false" title="List View">
                                    <i class="fas fa-list hph-btn-icon"></i>
                                    <span class="hph-view-label">List</span>
                                </button>
                                <button type="button" class="hph-view-btn" data-view="map" role="tab" aria-selected="false" title="Map View">
                                    <i class="fas fa-map-marked-alt hph-btn-icon"></i>
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
                </form>

                <!-- Search Form Uses Archive AJAX System -->
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const searchForm = document.querySelector('.hph-site-header-enhanced__search-form');
                    if (searchForm) {
                        // Only redirect if we're NOT on the listings archive page
                        const isListingsPage = window.location.pathname.includes('/listings/') ||
                                             document.body.classList.contains('post-type-archive-listing') ||
                                             document.body.classList.contains('tax-listing_type') ||
                                             document.body.classList.contains('tax-listing_status');

                        if (!isListingsPage) {
                            // Not on listings archive - redirect to listings page
                            searchForm.addEventListener('submit', function(e) {
                                e.preventDefault();
                                const formData = new FormData(this);
                                const searchQuery = formData.get('s');

                                // Build query string for initial load
                                const params = new URLSearchParams();
                                if (searchQuery && searchQuery.trim()) {
                                    params.set('s', searchQuery);
                                }
                                params.set('post_type', 'listing');

                                // Add other filters if present
                                ['min_price', 'max_price', 'bedrooms', 'property_type'].forEach(key => {
                                    const value = formData.get(key);
                                    if (value) params.set(key, value);
                                });

                                const finalUrl = '<?php echo esc_js(home_url('/listings/')); ?>?' + params.toString();
                                window.location.href = finalUrl;
                            });
                        }
                        // If on listings page, archive AJAX system will handle it automatically
                    }

                    // Price range validation (optional)
                    const priceMinSelect = searchForm.querySelector('select[name="min_price"]');
                    const priceMaxSelect = searchForm.querySelector('select[name="max_price"]');

                    if (priceMinSelect && priceMaxSelect) {
                        // Simple validation to ensure min doesn't exceed max
                        function validatePriceRange() {
                            const minVal = parseInt(priceMinSelect.value) || 0;
                            const maxVal = parseInt(priceMaxSelect.value) || Infinity;

                            if (minVal > 0 && maxVal !== Infinity && minVal > maxVal) {
                                // Reset max to empty if min exceeds max
                                priceMaxSelect.value = '';
                            }
                        }

                        priceMinSelect.addEventListener('change', validatePriceRange);
                        priceMaxSelect.addEventListener('change', validatePriceRange);
                    }
                });
                </script>

                <!-- Quick Search Links -->
                <div class="hph-quick-searches">
                    <span class="hph-quick-label">Browse By Type:</span>
                    <a href="<?php echo esc_url(home_url('/listings/')); ?>?property_type=single-family" class="hph-quick-link">Single Family</a>
                    <a href="<?php echo esc_url(home_url('/listings/')); ?>?property_type=condo" class="hph-quick-link">Condos</a>
                    <a href="<?php echo esc_url(home_url('/listings/')); ?>?property_type=townhouse" class="hph-quick-link">Townhouses</a>
                    <a href="<?php echo esc_url(home_url('/listings/')); ?>?property_type=multi-family" class="hph-quick-link">Multi-Family</a>
                    <a href="<?php echo esc_url(home_url('/listings/')); ?>?property_type=land" class="hph-quick-link">Land</a>
                </div>
            </div>
        </div>

    </header>
    </div><!-- End Site Header Wrapper -->

    <!-- Mobile Menu -->
    <div class="hph-site-header-enhanced__mobile-menu" data-mobile-menu>
        <div class="hph-mobile-header">
            <div class="hph-logo hph-logo--sm">
                <?php if ($site_logo_url) : ?>
                    <img src="<?php echo esc_url($site_logo_url); ?>"
                         alt="<?php bloginfo('name'); ?>"
                         class="hph-logo-img">
                <?php else : ?>
                    <span class="hph-logo-text"><?php bloginfo('name'); ?></span>
                <?php endif; ?>
            </div>
            <button class="hph-header-actions__btn hph-header-actions__btn--close" aria-label="Close menu">
                <i class="fas fa-times hph-header-actions__icon"></i>
            </button>
        </div>

        <!-- Mobile Search - COMMENTED OUT FOR NOW -->
        <?php /*
        <div class="hph-mobile-search">
            <form action="<?php echo esc_url(home_url('/listings/')); ?>" method="GET">
                <input type="hidden" name="post_type" value="listing">
                <div class="hph-search-input-container">
                    <div class="hph-search-input-wrapper hph-search-input-wrapper--sm has-close">
                        <input type="text"
                               name="s"
                               id="mobile-search-input"
                               class="hph-form-input hph-search-input hph-search-input--sm"
                               placeholder="Search listings..."
                               autocomplete="off">
                        <i class="fas fa-search hph-search-input-icon"></i>
                        <button type="button" class="hph-header-actions__btn hph-header-actions__btn--clear" aria-label="Clear search">
                            <i class="fas fa-times hph-header-actions__icon"></i>
                        </button>
                    </div>
                    <?php get_template_part('template-parts/components/search/search-autocomplete', null, [
                        'input_id' => 'mobile-search-input',
                        'container_id' => 'mobile-search-autocomplete',
                        'post_types' => ['listing', 'agent', 'city', 'community'],
                        'max_suggestions' => 6
                    ]); ?>
                </div>
            </form>
        </div>
        */ ?>

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
               class="hph-btn hph-btn-primary hph-mobile-contact-btn">
                <i class="fas fa-phone"></i>
                <?php echo esc_html($agency_phone); ?>
            </a>
        </div>
    </div>

    <!-- Mobile Menu Overlay -->
    <div class="hph-site-header-enhanced__mobile-overlay" data-mobile-overlay></div>

    <!-- Header Search Toggle JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchToggle = document.querySelector('[data-search-toggle]');
        const searchBar = document.querySelector('[data-search-bar]');
        const searchInput = document.querySelector('#header-search-input');
        const clearButton = document.querySelector('.hph-header-actions__btn--clear');

        if (searchToggle && searchBar) {
            // Toggle search dropdown
            searchToggle.addEventListener('click', function() {
                const isActive = searchBar.classList.contains('active');

                if (isActive) {
                    // Close search dropdown
                    searchBar.classList.remove('active');
                    searchToggle.classList.remove('is-active');
                    searchToggle.setAttribute('aria-expanded', 'false');
                } else {
                    // Open search dropdown
                    searchBar.classList.add('active');
                    searchToggle.classList.add('is-active');
                    searchToggle.setAttribute('aria-expanded', 'true');

                    // Focus search input when opened
                    if (searchInput) {
                        setTimeout(() => searchInput.focus(), 100);
                    }
                }
            });

            // Clear search input
            if (clearButton && searchInput) {
                clearButton.addEventListener('click', function() {
                    searchInput.value = '';
                    searchInput.focus();
                });
            }

            // Close search dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchToggle.contains(e.target) && !searchBar.contains(e.target)) {
                    if (searchBar.classList.contains('active')) {
                        searchBar.classList.remove('active');
                        searchToggle.classList.remove('is-active');
                        searchToggle.setAttribute('aria-expanded', 'false');
                    }
                }
            });

            // Close search dropdown on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && searchBar.classList.contains('active')) {
                    searchBar.classList.remove('active');
                    searchToggle.classList.remove('is-active');
                    searchToggle.setAttribute('aria-expanded', 'false');
                    searchToggle.focus();
                }
            });
        }
    });
    </script>

    <!-- Main Content Area -->
    <div id="main" class="hph-main-content" role="main">
        <div class="hph-main-wrapper">
            <div class="hph-content-container"><?php // Template content will be inserted here ?>
