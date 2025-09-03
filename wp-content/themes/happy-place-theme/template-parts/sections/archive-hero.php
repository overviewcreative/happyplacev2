<?php
/**
 * HPH Archive Hero Section with Search Template
 * 
 * Hero section with integrated search and filter functionality
 * Perfect for archive pages, property search pages, and listing directories
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Register this template part for asset loading
if (function_exists('hph_register_template_part')) {
    hph_register_template_part('sections/archive-hero');
}

// Default arguments
$defaults = array(
    // Hero settings
    'style' => 'gradient',
    'theme' => 'primary',
    'height' => 'lg',
    'background_image' => '',
    'background_video' => '',
    'overlay' => 'dark',
    'overlay_opacity' => '40',
    'gradient_overlay' => '',
    'alignment' => 'center',
    'content_width' => 'normal',
    'parallax' => false,
    'fade_in' => true,
    
    // Content settings
    'badge' => '',
    'badge_icon' => '',
    'headline' => 'Find Your Dream Home',
    'subheadline' => 'Search through thousands of properties to find the perfect one for you',
    'content' => '',
    'show_stats' => true,
    'stats' => array(), // Array of stats like ['listings' => 1234, 'agents' => 50, 'cities' => 25]
    
    // Search settings
    'show_search' => true,
    'search_placeholder' => 'Enter city, zip, address, or MLS#',
    'search_action' => '',
    'show_filters' => true,
    'show_advanced_filters' => true,
    'filter_layout' => 'inline', // Options: 'inline', 'dropdown', 'sidebar'
    'show_quick_searches' => true,
    'quick_searches' => array(),
    
    // Additional settings
    'section_id' => '',
    'scroll_indicator' => false
);

// Merge with provided args
$config = wp_parse_args($args ?? array(), $defaults);
extract($config);

// Auto-fix: Switch to image style if background_image is provided
if (!empty($background_image) && $style === 'gradient') {
    $style = 'image';
}

// Set default search action if not provided
if (empty($search_action)) {
    $search_action = home_url('/advanced-search/');
}

// Default quick searches if not provided
if (empty($quick_searches) && $show_quick_searches) {
    $quick_searches = array(
        array('label' => 'Open Houses', 'url' => '/listings/?status=open-house'),
        array('label' => 'New Listings', 'url' => '/listings/?status=new'),
        array('label' => 'Waterfront', 'url' => '/listings/?feature=waterfront'),
        array('label' => 'With Pool', 'url' => '/listings/?feature=pool'),
        array('label' => 'Price Reduced', 'url' => '/listings/?status=reduced')
    );
}

// Theme configuration
$theme_config = array();
if (!empty($theme)) {
    switch($theme) {
        case 'primary':
            $theme_config = array(
                'gradient_bg' => 'var(--hph-gradient-primary)',
                'text_color' => 'var(--hph-white)',
                'badge_bg' => 'rgba(255, 255, 255, 0.2)',
                'badge_color' => 'var(--hph-white)',
                'search_bg' => 'rgba(255, 255, 255, 0.95)',
                'search_text' => 'var(--hph-gray-900)'
            );
            break;
        case 'dark':
            $theme_config = array(
                'gradient_bg' => 'linear-gradient(135deg, var(--hph-gray-900) 0%, var(--hph-black) 100%)',
                'text_color' => 'var(--hph-white)',
                'badge_bg' => 'rgba(255, 255, 255, 0.1)',
                'badge_color' => 'var(--hph-white)',
                'search_bg' => 'rgba(255, 255, 255, 0.95)',
                'search_text' => 'var(--hph-gray-900)'
            );
            break;
        case 'light':
            $theme_config = array(
                'gradient_bg' => 'linear-gradient(135deg, var(--hph-white) 0%, var(--hph-gray-100) 100%)',
                'text_color' => 'var(--hph-gray-900)',
                'badge_bg' => 'var(--hph-primary-100)',
                'badge_color' => 'var(--hph-primary-700)',
                'search_bg' => 'var(--hph-white)',
                'search_text' => 'var(--hph-gray-900)'
            );
            break;
    }
}

// Build inline styles for hero section
$hero_styles = array();
$hero_styles[] = 'position: relative';
$hero_styles[] = 'display: flex';
$hero_styles[] = 'align-items: center';
$hero_styles[] = 'justify-content: center';
$hero_styles[] = 'width: 100%';
$hero_styles[] = 'overflow: hidden';

// Height styles
switch ($height) {
    case 'sm':
        $hero_styles[] = 'min-height: 50vh';
        break;
    case 'md':
        $hero_styles[] = 'min-height: 60vh';
        break;
    case 'lg':
        $hero_styles[] = 'min-height: 75vh';
        break;
    case 'xl':
        $hero_styles[] = 'min-height: 85vh';
        break;
    case 'full':
        $hero_styles[] = 'min-height: 100vh';
        break;
}

// Background styles
if ($background_image) {
    $hero_styles[] = "background-image: url('" . esc_url($background_image) . "')";
    $hero_styles[] = "background-size: cover";
    $hero_styles[] = "background-position: center";
    $hero_styles[] = "background-repeat: no-repeat";
    if ($parallax) {
        $hero_styles[] = "background-attachment: fixed";
    }
} elseif ($style === 'gradient' && !empty($theme_config['gradient_bg'])) {
    $hero_styles[] = "background: " . $theme_config['gradient_bg'];
}

// Text color
if (!empty($theme_config['text_color'])) {
    $hero_styles[] = "color: " . $theme_config['text_color'];
}

$hero_style_attr = !empty($hero_styles) ? 'style="' . implode('; ', $hero_styles) . '"' : '';

// Build overlay styles
$overlay_styles = array();
if ($overlay !== 'none') {
    switch ($overlay) {
        case 'dark':
            $overlay_styles[] = 'background: var(--hph-gradient-overlay-dark)';
            break;
        case 'light':
            $overlay_styles[] = 'background: var(--hph-gradient-overlay-light)';
            break;
        default:
            $opacity_value = intval($overlay_opacity) / 100;
            $overlay_styles[] = 'background: rgba(0, 0, 0, ' . $opacity_value . ')';
    }
}

// Content container width
$container_max_width = '';
switch ($content_width) {
    case 'narrow':
        $container_max_width = 'max-width: var(--hph-container-sm);';
        break;
    case 'wide':
        $container_max_width = 'max-width: var(--hph-container-2xl);';
        break;
    case 'full':
        $container_max_width = 'max-width: 100%; padding-left: 0; padding-right: 0;';
        break;
    case 'normal':
    default:
        $container_max_width = 'max-width: var(--hph-container-xl);';
        break;
}

// Text alignment
$text_align_style = '';
$content_justify = '';
switch ($alignment) {
    case 'left':
        $text_align_style = 'text-align: left;';
        $content_justify = 'align-items: flex-start;';
        break;
    case 'right':
        $text_align_style = 'text-align: right;';
        $content_justify = 'align-items: flex-end;';
        break;
    case 'center':
    default:
        $text_align_style = 'text-align: center;';
        $content_justify = 'align-items: center;';
        break;
}

// Ensure Font Awesome is loaded
if (!wp_script_is('font-awesome', 'enqueued')) {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', array(), '6.5.1');
}
?>

<section 
    class="hph-archive-hero hph-hero-<?php echo esc_attr($style); ?> <?php echo $background_image ? 'has-bg-image' : ''; ?> <?php echo $parallax ? 'hph-hero-parallax' : ''; ?>"
    <?php if ($section_id): ?>id="<?php echo esc_attr($section_id); ?>"<?php endif; ?>
    <?php echo $hero_style_attr; ?>
>
    
    <?php if ($background_video): ?>
    <!-- Video Background -->
    <video 
        class="hph-hero-video"
        style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; width: 100%; height: 100%; object-fit: cover; z-index: 0;"
        autoplay muted loop playsinline
        <?php if ($background_image): ?>poster="<?php echo esc_url($background_image); ?>"<?php endif; ?>
    >
        <source src="<?php echo esc_url($background_video); ?>" type="video/mp4">
    </video>
    <?php endif; ?>
    
    <?php if ($overlay !== 'none'): ?>
    <!-- Overlay -->
    <div class="hph-hero-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 1; pointer-events: none; <?php echo implode('; ', $overlay_styles); ?>"></div>
    <?php endif; ?>
    
    <!-- Content Container -->
    <div class="hph-hero-container" style="position: relative; z-index: 2; width: 100%; padding: var(--hph-padding-xl) var(--hph-padding-lg);">
        <div class="hph-hero-inner" style="<?php echo $container_max_width; ?> margin-left: auto; margin-right: auto;">
            <div class="hph-hero-content" style="display: flex; flex-direction: column; <?php echo $content_justify; ?> gap: var(--hph-gap-lg); <?php echo $text_align_style; ?>">
                
                <?php if ($badge): ?>
                <!-- Badge -->
                <div style="margin-bottom: var(--hph-margin-sm);">
                    <?php 
                    $badge_bg = !empty($theme_config['badge_bg']) ? $theme_config['badge_bg'] : 'rgba(255, 255, 255, 0.2)';
                    $badge_color = !empty($theme_config['badge_color']) ? $theme_config['badge_color'] : 'currentColor';
                    ?>
                    <span style="display: inline-flex; align-items: center; gap: var(--hph-gap-sm); padding: var(--hph-padding-sm) var(--hph-padding-md); background: <?php echo $badge_bg; ?>; color: <?php echo $badge_color; ?>; backdrop-filter: blur(10px); border-radius: var(--hph-radius-full); font-size: var(--hph-text-sm); font-weight: var(--hph-font-semibold);">
                        <?php if ($badge_icon): ?>
                        <i class="<?php echo esc_attr($badge_icon); ?>"></i>
                        <?php endif; ?>
                        <?php echo esc_html($badge); ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <?php if ($headline): ?>
                <!-- Hero Headline -->
                <h1 class="hph-hero-headline <?php echo $fade_in ? 'hph-animate-fade-in-up' : ''; ?>" 
                    style="margin: 0 0 var(--hph-margin-md) 0; font-size: var(--hph-text-5xl); font-weight: var(--hph-font-bold); line-height: var(--hph-leading-tight);">
                    <?php echo esc_html($headline); ?>
                </h1>
                <?php endif; ?>
                
                <?php if ($subheadline): ?>
                <!-- Hero Subheadline -->
                <h2 class="hph-hero-subheadline <?php echo $fade_in ? 'hph-animate-fade-in-up' : ''; ?>" 
                    style="margin: 0 0 var(--hph-margin-lg) 0; font-size: var(--hph-text-xl); font-weight: var(--hph-font-medium); line-height: var(--hph-leading-snug); opacity: 0.9;">
                    <?php echo esc_html($subheadline); ?>
                </h2>
                <?php endif; ?>
                
                <?php if ($content): ?>
                <!-- Hero Content -->
                <div class="hph-hero-content-text <?php echo $fade_in ? 'hph-animate-fade-in-up' : ''; ?>" 
                     style="margin: 0 0 var(--hph-margin-xl) 0; font-size: var(--hph-text-lg); line-height: var(--hph-leading-normal); opacity: 0.85;">
                    <?php echo wp_kses_post($content); ?>
                </div>
                <?php endif; ?>
                
                <?php if ($show_search): ?>
                <!-- Search Form -->
                <div class="hph-hero-search <?php echo $fade_in ? 'hph-animate-fade-in-up' : ''; ?>" 
                     style="width: 100%; margin: var(--hph-margin-lg) 0;">
                    
                    <?php
                    $search_bg = !empty($theme_config['search_bg']) ? $theme_config['search_bg'] : 'rgba(255, 255, 255, 0.95)';
                    $search_text = !empty($theme_config['search_text']) ? $theme_config['search_text'] : 'var(--hph-gray-900)';
                    ?>
                    
                    <form class="hph-hero-search-form" 
                          action="<?php echo esc_url($search_action); ?>" 
                          method="GET"
                          style="background: <?php echo $search_bg; ?>; color: <?php echo $search_text; ?>; padding: var(--hph-padding-lg); border-radius: var(--hph-radius-xl); box-shadow: var(--hph-shadow-2xl); backdrop-filter: blur(10px);">
                        
                        <input type="hidden" name="type" value="listing">
                        
                        <?php if ($show_filters && $filter_layout === 'inline'): ?>
                        <!-- Inline Filters Layout -->
                        <div class="hph-search-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--hph-gap-md); margin-bottom: var(--hph-margin-md);">
                            
                            <!-- Search Input -->
                            <div class="hph-search-input-wrapper" style="grid-column: 1 / -1; position: relative;">
                                <i class="fas fa-search" style="position: absolute; left: var(--hph-padding-md); top: 50%; transform: translateY(-50%); color: var(--hph-gray-400); pointer-events: none;"></i>
                                <input type="text" 
                                       name="s" 
                                       class="hph-search-input" 
                                       placeholder="<?php echo esc_attr($search_placeholder); ?>"
                                       autocomplete="off"
                                       style="width: 100%; padding: var(--hph-padding-md) var(--hph-padding-md) var(--hph-padding-md) calc(var(--hph-padding-md) * 3); border: 2px solid var(--hph-gray-200); border-radius: var(--hph-radius-lg); font-size: var(--hph-text-lg); background: var(--hph-white); color: var(--hph-gray-900); transition: all 0.3s ease; box-shadow: var(--hph-shadow-sm);"
                                       onfocus="this.style.borderColor='var(--hph-primary)'; this.style.boxShadow='0 0 0 3px rgba(var(--hph-primary-rgb), 0.1)';"
                                       onblur="this.style.borderColor='var(--hph-gray-200)'; this.style.boxShadow='var(--hph-shadow-sm)';">
                                <div class="hph-search-suggestions"></div>
                            </div>
                            
                            <!-- Property Type -->
                            <select name="property_type" class="hph-search-select" 
                                    style="padding: var(--hph-padding-md); border: 2px solid var(--hph-gray-200); border-radius: var(--hph-radius-lg); font-size: var(--hph-text-base); background: var(--hph-white); color: var(--hph-gray-900); cursor: pointer; transition: all 0.3s ease;"
                                    onfocus="this.style.borderColor='var(--hph-primary)';"
                                    onblur="this.style.borderColor='var(--hph-gray-200)';">
                                <option value="">All Types</option>
                                <option value="single_family">Single Family</option>
                                <option value="condo">Condo</option>
                                <option value="townhouse">Townhouse</option>
                                <option value="multi_family">Multi-Family</option>
                                <option value="land">Land</option>
                            </select>
                            
                            <!-- Price Range -->
                            <select name="price_range" class="hph-search-select"
                                    style="padding: var(--hph-padding-md); border: 2px solid var(--hph-gray-200); border-radius: var(--hph-radius-lg); font-size: var(--hph-text-base); background: var(--hph-white); color: var(--hph-gray-900); cursor: pointer; transition: all 0.3s ease;"
                                    onfocus="this.style.borderColor='var(--hph-primary)';"
                                    onblur="this.style.borderColor='var(--hph-gray-200)';">
                                <option value="">Price Range</option>
                                <option value="0-250000">Under $250k</option>
                                <option value="250000-500000">$250k - $500k</option>
                                <option value="500000-750000">$500k - $750k</option>
                                <option value="750000-1000000">$750k - $1M</option>
                                <option value="1000000-9999999">Over $1M</option>
                            </select>
                            
                            <!-- Beds -->
                            <select name="bedrooms" class="hph-search-select"
                                    style="padding: var(--hph-padding-md); border: 2px solid var(--hph-gray-200); border-radius: var(--hph-radius-lg); font-size: var(--hph-text-base); background: var(--hph-white); color: var(--hph-gray-900); cursor: pointer; transition: all 0.3s ease;"
                                    onfocus="this.style.borderColor='var(--hph-primary)';"
                                    onblur="this.style.borderColor='var(--hph-gray-200)';">
                                <option value="">Beds</option>
                                <option value="1">1+</option>
                                <option value="2">2+</option>
                                <option value="3">3+</option>
                                <option value="4">4+</option>
                                <option value="5">5+</option>
                            </select>
                            
                            <!-- Baths -->
                            <select name="bathrooms" class="hph-search-select"
                                    style="padding: var(--hph-padding-md); border: 2px solid var(--hph-gray-200); border-radius: var(--hph-radius-lg); font-size: var(--hph-text-base); background: var(--hph-white); color: var(--hph-gray-900); cursor: pointer; transition: all 0.3s ease;"
                                    onfocus="this.style.borderColor='var(--hph-primary)';"
                                    onblur="this.style.borderColor='var(--hph-gray-200)';">
                                <option value="">Baths</option>
                                <option value="1">1+</option>
                                <option value="2">2+</option>
                                <option value="3">3+</option>
                                <option value="4">4+</option>
                            </select>
                            
                            <?php if ($show_advanced_filters): ?>
                            <!-- Square Feet -->
                            <select name="sqft_range" class="hph-search-select"
                                    style="padding: var(--hph-padding-md); border: 2px solid var(--hph-gray-200); border-radius: var(--hph-radius-lg); font-size: var(--hph-text-base); background: var(--hph-white); color: var(--hph-gray-900); cursor: pointer; transition: all 0.3s ease;"
                                    onfocus="this.style.borderColor='var(--hph-primary)';"
                                    onblur="this.style.borderColor='var(--hph-gray-200)';">
                                <option value="">Square Feet</option>
                                <option value="0-1000">Under 1,000</option>
                                <option value="1000-1500">1,000 - 1,500</option>
                                <option value="1500-2000">1,500 - 2,000</option>
                                <option value="2000-3000">2,000 - 3,000</option>
                                <option value="3000-99999">Over 3,000</option>
                            </select>
                            
                            <!-- Status -->
                            <select name="status" class="hph-search-select"
                                    style="padding: var(--hph-padding-md); border: 2px solid var(--hph-gray-200); border-radius: var(--hph-radius-lg); font-size: var(--hph-text-base); background: var(--hph-white); color: var(--hph-gray-900); cursor: pointer; transition: all 0.3s ease;"
                                    onfocus="this.style.borderColor='var(--hph-primary)';"
                                    onblur="this.style.borderColor='var(--hph-gray-200)';">
                                <option value="">Any Status</option>
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                                <option value="sold">Sold</option>
                                <option value="new">New Listing</option>
                                <option value="reduced">Price Reduced</option>
                            </select>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <!-- Simple Search Layout -->
                        <div class="hph-search-simple" style="display: flex; gap: var(--hph-gap-md); align-items: center;">
                            <div class="hph-search-input-wrapper" style="flex: 1; position: relative;">
                                <i class="fas fa-search" style="position: absolute; left: var(--hph-padding-md); top: 50%; transform: translateY(-50%); color: var(--hph-gray-400); pointer-events: none;"></i>
                                <input type="text" 
                                       name="s" 
                                       class="hph-search-input" 
                                       placeholder="<?php echo esc_attr($search_placeholder); ?>"
                                       autocomplete="off"
                                       style="width: 100%; padding: var(--hph-padding-md) var(--hph-padding-md) var(--hph-padding-md) calc(var(--hph-padding-md) * 3); border: 2px solid var(--hph-gray-200); border-radius: var(--hph-radius-lg); font-size: var(--hph-text-lg); background: var(--hph-white); color: var(--hph-gray-900); transition: all 0.3s ease;"
                                       onfocus="this.style.borderColor='var(--hph-primary)';"
                                       onblur="this.style.borderColor='var(--hph-gray-200)';">
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Action Buttons -->
                        <div class="hph-search-actions" style="display: flex; gap: var(--hph-gap-md); justify-content: <?php echo $alignment === 'center' ? 'center' : ($alignment === 'right' ? 'flex-end' : 'flex-start'); ?>; margin-top: var(--hph-margin-lg);">
                            <!-- Submit Button -->
                            <button type="submit" class="hph-search-submit"
                                    style="padding: var(--hph-padding-md) var(--hph-padding-xl); background: var(--hph-primary); color: var(--hph-white); border: none; border-radius: var(--hph-radius-lg); font-size: var(--hph-text-lg); font-weight: var(--hph-font-semibold); cursor: pointer; display: inline-flex; align-items: center; gap: var(--hph-gap-sm); transition: all 0.3s ease; box-shadow: var(--hph-shadow-lg);"
                                    onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='var(--hph-shadow-xl)';"
                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='var(--hph-shadow-lg)';">
                                <i class="fas fa-search"></i>
                                Search Properties
                            </button>
                            
                            <?php if ($show_advanced_filters): ?>
                            <!-- Advanced Search Link -->
                            <a href="<?php echo esc_url(home_url('/advanced-search/')); ?>" 
                               class="hph-advanced-search"
                               style="padding: var(--hph-padding-md) var(--hph-padding-xl); background: transparent; color: var(--hph-primary); border: 2px solid var(--hph-primary); border-radius: var(--hph-radius-lg); font-size: var(--hph-text-lg); font-weight: var(--hph-font-semibold); text-decoration: none; display: inline-flex; align-items: center; gap: var(--hph-gap-sm); transition: all 0.3s ease;"
                               onmouseover="this.style.background='var(--hph-primary)'; this.style.color='var(--hph-white)';"
                               onmouseout="this.style.background='transparent'; this.style.color='var(--hph-primary)';">
                                <i class="fas fa-sliders-h"></i>
                                Advanced Search
                            </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
                
                <?php if ($show_quick_searches && !empty($quick_searches)): ?>
                <!-- Quick Search Links -->
                <div class="hph-quick-searches <?php echo $fade_in ? 'hph-animate-fade-in-up' : ''; ?>" 
                     style="display: flex; flex-wrap: wrap; gap: var(--hph-gap-md); align-items: center; justify-content: <?php echo $alignment === 'center' ? 'center' : ($alignment === 'right' ? 'flex-end' : 'flex-start'); ?>; margin-top: var(--hph-margin-md); opacity: 0.9;">
                    <span class="hph-quick-label" style="font-weight: var(--hph-font-semibold);">Quick Search:</span>
                    <?php foreach ($quick_searches as $quick): ?>
                    <a href="<?php echo esc_url($quick['url']); ?>" 
                       class="hph-quick-link"
                       style="padding: var(--hph-padding-sm) var(--hph-padding-md); background: rgba(255, 255, 255, 0.2); color: currentColor; backdrop-filter: blur(10px); border-radius: var(--hph-radius-full); text-decoration: none; font-size: var(--hph-text-sm); transition: all 0.3s ease;"
                       onmouseover="this.style.background='rgba(255, 255, 255, 0.3)';"
                       onmouseout="this.style.background='rgba(255, 255, 255, 0.2)';">
                        <?php echo esc_html($quick['label']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($show_stats && !empty($stats)): ?>
                <!-- Statistics -->
                <div class="hph-hero-stats <?php echo $fade_in ? 'hph-animate-fade-in-up' : ''; ?>" 
                     style="display: flex; flex-wrap: wrap; gap: var(--hph-gap-xl); justify-content: <?php echo $alignment === 'center' ? 'center' : ($alignment === 'right' ? 'flex-end' : 'flex-start'); ?>; margin-top: var(--hph-margin-xl); padding-top: var(--hph-padding-xl); border-top: 1px solid rgba(255, 255, 255, 0.2);">
                    <?php foreach ($stats as $stat_key => $stat_value): ?>
                    <div class="hph-stat" style="text-align: center;">
                        <div style="font-size: var(--hph-text-3xl); font-weight: var(--hph-font-bold); margin-bottom: var(--hph-margin-sm);">
                            <?php echo esc_html(number_format($stat_value)); ?>
                        </div>
                        <div style="font-size: var(--hph-text-sm); text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.8;">
                            <?php echo esc_html(ucfirst($stat_key)); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
    
    <?php if ($scroll_indicator): ?>
    <!-- Scroll Indicator -->
    <div class="hph-hero-scroll" style="position: absolute; bottom: var(--hph-margin-lg); left: 50%; transform: translateX(-50%); cursor: pointer; transition: opacity 0.3s ease;">
        <div class="hph-scroll-indicator" style="display: flex; flex-direction: column; align-items: center; color: currentColor; opacity: 0.75;">
            <span style="font-size: var(--hph-text-sm); margin-bottom: var(--hph-margin-sm); font-weight: var(--hph-font-medium);">Scroll</span>
            <div style="width: 2rem; height: 2.5rem; border: 2px solid currentColor; border-radius: var(--hph-radius-full); position: relative;">
                <div class="hph-scroll-dot" style="position: absolute; top: 0.5rem; left: 50%; width: 0.25rem; height: 0.5rem; background: currentColor; border-radius: var(--hph-radius-full); transform: translateX(-50%); animation: bounce 1.5s infinite;"></div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
</section>

<style>
@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateX(-50%) translateY(0); }
    40% { transform: translateX(-50%) translateY(8px); }
    60% { transform: translateX(-50%) translateY(4px); }
}

.hph-animate-fade-in-up {
    animation: fadeInUp 0.8s ease forwards;
    opacity: 0;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .hph-search-grid {
        grid-template-columns: 1fr !important;
    }
    
    .hph-search-actions {
        flex-direction: column;
        width: 100%;
    }
    
    .hph-search-actions button,
    .hph-search-actions a {
        width: 100%;
        justify-content: center;
    }
}
</style>