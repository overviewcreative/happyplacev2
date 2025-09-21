<?php
/**
 * Archive Template for Listings - Map View
 * Dedicated template for full-screen map view with property listings
 *
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get search parameters
$search_query = sanitize_text_field($_GET['s'] ?? '');
$min_price = intval($_GET['min_price'] ?? 0);
$max_price = intval($_GET['max_price'] ?? 0);
$bedrooms = sanitize_text_field($_GET['bedrooms'] ?? '');
$bathrooms = sanitize_text_field($_GET['bathrooms'] ?? '');
$property_type = sanitize_text_field($_GET['property_type'] ?? '');
$property_status = sanitize_text_field($_GET['property_status'] ?? '');
$listing_location = sanitize_text_field($_GET['listing_location'] ?? '');
$sort = sanitize_text_field($_GET['sort'] ?? 'date_desc');
$paged = get_query_var('paged', 1);

// Get Mapbox token for map functionality
$mapbox_token = '';
if (defined('HP_MAPBOX_ACCESS_TOKEN')) {
    $mapbox_token = HP_MAPBOX_ACCESS_TOKEN;
} else if (class_exists('\HappyPlace\Core\ConfigurationManager')) {
    $config_manager = \HappyPlace\Core\ConfigurationManager::get_instance();
    $mapbox_token = $config_manager->get('mapbox_access_token', '');
}

// Load Mapbox scripts if token is available
if (!empty($mapbox_token)) {
    wp_enqueue_script('mapbox-gl-js', 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js', [], '2.15.0', false);
    wp_enqueue_style('mapbox-gl-css', 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css', [], '2.15.0');

    // Load archive bundle which includes HPH Map component
    wp_enqueue_script('hph-archive-js', get_template_directory_uri() . '/dist/js/archive-js-DPIsG0XB.min.js', ['mapbox-gl-js'], filemtime(get_template_directory() . '/dist/js/archive-js-DPIsG0XB.min.js'), true);
    wp_enqueue_script('hph-archive-unified', get_template_directory_uri() . '/dist/js/chunks/unified-archive-Cy72yypt.min.js', ['hph-archive-js'], filemtime(get_template_directory() . '/dist/js/chunks/unified-archive-Cy72yypt.min.js'), true);

    wp_add_inline_script('mapbox-gl-js', '
        console.log("Setting Mapbox config with token:", "' . esc_js(substr($mapbox_token, 0, 10)) . '...");
        window.hph_mapbox_config = {
            access_token: "' . esc_js($mapbox_token) . '",
            has_token: true
        };
    ', 'before');
} else {
    // Add inline script directly since we don't have other scripts to depend on
    echo '<script>console.log("No Mapbox token configured"); window.hph_mapbox_config = { access_token: "", has_token: false };</script>';
}

// Build query arguments for listings
$args = [
    'post_type' => 'listing',
    'post_status' => 'publish',
    'posts_per_page' => 100, // Load more for map view
    'paged' => $paged,
    'meta_query' => [],
    'tax_query' => []
];

// Add search
if (!empty($search_query)) {
    $args['s'] = $search_query;
}

// Add sorting
switch ($sort) {
    case 'price_asc':
        $args['meta_key'] = 'listing_price';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'ASC';
        break;
    case 'price_desc':
        $args['meta_key'] = 'listing_price';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'DESC';
        break;
    case 'date_desc':
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
        break;
    case 'date_asc':
        $args['orderby'] = 'date';
        $args['order'] = 'ASC';
        break;
}

// Price range filter
if ($min_price || $max_price) {
    $price_query = ['key' => 'listing_price', 'type' => 'NUMERIC'];

    if ($min_price && $max_price) {
        $price_query['value'] = [$min_price, $max_price];
        $price_query['compare'] = 'BETWEEN';
    } elseif ($min_price) {
        $price_query['value'] = $min_price;
        $price_query['compare'] = '>=';
    } else {
        $price_query['value'] = $max_price;
        $price_query['compare'] = '<=';
    }

    $args['meta_query'][] = $price_query;
}

// Bedrooms filter
if (!empty($bedrooms)) {
    $bedrooms_value = (strpos($bedrooms, '+') !== false) ?
        intval(str_replace('+', '', $bedrooms)) : intval($bedrooms);

    $args['meta_query'][] = [
        'key' => 'listing_bedrooms',
        'value' => $bedrooms_value,
        'compare' => (strpos($bedrooms, '+') !== false) ? '>=' : '='
    ];
}

// Bathrooms filter
if (!empty($bathrooms)) {
    $bathrooms_value = (strpos($bathrooms, '+') !== false) ?
        intval(str_replace('+', '', $bathrooms)) : intval($bathrooms);

    $args['meta_query'][] = [
        'key' => 'listing_bathrooms',
        'value' => $bathrooms_value,
        'compare' => (strpos($bathrooms, '+') !== false) ? '>=' : '='
    ];
}

// Property type taxonomy
if (!empty($property_type)) {
    $args['tax_query'][] = [
        'taxonomy' => 'property_type',
        'field' => is_numeric($property_type) ? 'term_id' : 'slug',
        'terms' => $property_type
    ];
}

// Property status taxonomy
if (!empty($property_status)) {
    $args['tax_query'][] = [
        'taxonomy' => 'property_status',
        'field' => is_numeric($property_status) ? 'term_id' : 'slug',
        'terms' => $property_status
    ];
}

// Location filter
if (!empty($listing_location)) {
    $args['tax_query'][] = [
        'taxonomy' => 'listing_location',
        'field' => is_numeric($listing_location) ? 'term_id' : 'slug',
        'terms' => $listing_location
    ];
}

// Set relations for multiple queries
if (count($args['meta_query']) > 1) {
    $args['meta_query']['relation'] = 'AND';
}
if (count($args['tax_query']) > 1) {
    $args['tax_query']['relation'] = 'AND';
}

// Execute query
$listings = new WP_Query($args);

// Build listings data for map
$map_listings = [];
if ($listings->have_posts()) {
    while ($listings->have_posts()) {
        $listings->the_post();
        $listing_id = get_the_ID();

        // Get coordinates
        $coordinates = null;
        if (function_exists('hpt_get_listing_coordinates')) {
            try {
                $coordinates = hpt_get_listing_coordinates($listing_id);
            } catch (Exception $e) {
                $lat = get_field('latitude', $listing_id);
                $lng = get_field('longitude', $listing_id);
                $coordinates = ($lat && $lng) ? ['lat' => floatval($lat), 'lng' => floatval($lng)] : null;
            }
        } else {
            $lat = get_field('latitude', $listing_id);
            $lng = get_field('longitude', $listing_id);
            $coordinates = ($lat && $lng) ? ['lat' => floatval($lat), 'lng' => floatval($lng)] : null;
        }

        if ($coordinates && $coordinates['lat'] && $coordinates['lng']) {
            // Build listing data for map
            $listing_data = [
                'id' => $listing_id,
                'title' => get_the_title($listing_id),
                'latitude' => $coordinates['lat'],
                'longitude' => $coordinates['lng'],
                'permalink' => get_permalink($listing_id),
                'status' => get_field('listing_status', $listing_id) ?: 'active',
                'featured_image' => get_the_post_thumbnail_url($listing_id, 'medium')
            ];

            // Add price
            if (function_exists('hpt_get_listing_price')) {
                try {
                    $listing_data['price'] = hpt_get_listing_price($listing_id);
                } catch (Exception $e) {
                    $listing_data['price'] = get_field('price', $listing_id);
                }
            } else {
                $listing_data['price'] = get_field('price', $listing_id);
            }

            // Add basic stats
            $listing_data['bedrooms'] = get_field('bedrooms', $listing_id);
            $listing_data['bathrooms'] = get_field('bathrooms_full', $listing_id);
            $listing_data['square_feet'] = get_field('square_feet', $listing_id);

            // Add address
            if (function_exists('hpt_get_listing_address')) {
                try {
                    $address_data = hpt_get_listing_address($listing_id);
                    $listing_data['street_address'] = $address_data['street_address'] ?? '';
                    $listing_data['city'] = $address_data['city'] ?? '';
                    $listing_data['state'] = $address_data['state'] ?? '';
                    $listing_data['zip_code'] = $address_data['zip_code'] ?? '';
                } catch (Exception $e) {
                    $listing_data['street_address'] = trim((get_field('street_number', $listing_id) ?: '') . ' ' . (get_field('street_name', $listing_id) ?: '') . ' ' . (get_field('street_type', $listing_id) ?: ''));
                    $listing_data['city'] = get_field('city', $listing_id);
                    $listing_data['state'] = get_field('state', $listing_id);
                    $listing_data['zip_code'] = get_field('zip_code', $listing_id);
                }
            } else {
                $listing_data['street_address'] = trim((get_field('street_number', $listing_id) ?: '') . ' ' . (get_field('street_name', $listing_id) ?: '') . ' ' . (get_field('street_type', $listing_id) ?: ''));
                $listing_data['city'] = get_field('city', $listing_id);
                $listing_data['state'] = get_field('state', $listing_id);
                $listing_data['zip_code'] = get_field('zip_code', $listing_id);
            }

            $map_listings[] = $listing_data;
        }
    }
    wp_reset_postdata();
}

// Calculate center point from all listings
$map_center = [-75.1398, 38.7816]; // Default to Delaware
if (!empty($map_listings)) {
    $avg_lat = array_sum(array_column($map_listings, 'latitude')) / count($map_listings);
    $avg_lng = array_sum(array_column($map_listings, 'longitude')) / count($map_listings);
    $map_center = [$avg_lng, $avg_lat];
}

// Set up AJAX variables for map functionality
wp_localize_script('hph-archive-js', 'hphArchiveMap', [
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('hph_archive_nonce'),
    'post_type' => 'listing',
    'listings' => $map_listings,
    'center' => $map_center,
    'total_found' => $listings->found_posts,
    'enable_clustering' => true,
    'cluster_radius' => 50,
    'cluster_max_zoom' => 14
]);

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    <?php
    // Load core HPH CSS framework files for map view
    $css_files = [
        get_template_directory_uri() . '/assets/css/framework/core/tokens.css',
        get_template_directory_uri() . '/assets/css/framework/core/variables.css',
        get_template_directory_uri() . '/assets/css/framework/components/organisms/map.css',
        get_template_directory_uri() . '/assets/css/framework/features/listing/listing-map.css'
    ];

    foreach ($css_files as $css_file) {
        echo '<link rel="stylesheet" href="' . $css_file . '" />' . "\n    ";
    }
    ?>

    <style>
        /* Map View Layout - Using HPH Variables */
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
        }

        /* Full screen map layout */
        .hph-map-layout {
            display: flex;
            height: 100vh;
            width: 100vw;
            background: var(--hph-gray-50, #f9fafb);
        }

        /* Map container - uses existing HPH map styles */
        .hph-map-container {
            flex: 1;
            height: 100%;
            position: relative;
            background: var(--hph-gray-100, #f3f4f6);
        }

        /* Enhanced search header with HPH styling */
        .hph-map-header {
            position: absolute;
            top: 20px;
            left: 20px;
            right: 420px; /* Space for sidebar */
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            z-index: 1000;
            padding: 20px;
            border: 1px solid #e5e7eb;
            /* Debug styling to ensure visibility */
            min-height: 80px;
            display: block !important;
            visibility: visible !important;
        }

        .hph-map-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: var(--hph-spacing-lg, 20px);
        }

        .hph-map-search-form {
            flex: 1;
        }

        .hph-map-search-container {
            display: flex;
            gap: var(--hph-spacing-md, 12px);
            align-items: center;
            flex-wrap: wrap;
        }

        .hph-map-view-controls {
            flex-shrink: 0;
        }

        .hph-view-toggle-group {
            display: flex;
            background: var(--hph-gray-100, #f3f4f6);
            border-radius: var(--hph-radius-lg, 12px);
            padding: var(--hph-spacing-xs, 4px);
            gap: var(--hph-spacing-xs, 4px);
        }

        .hph-view-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border: none;
            background: transparent;
            color: var(--hph-gray-600, #6b7280);
            border-radius: var(--hph-radius-md, 8px);
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            font-size: var(--hph-text-base, 16px);
        }

        .hph-view-btn:hover {
            background: var(--hph-gray-200, #e5e7eb);
            color: var(--hph-gray-800, #1f2937);
        }

        .hph-view-btn.active {
            background: var(--hph-primary, #2563eb);
            color: var(--hph-white, #ffffff);
            box-shadow: var(--hph-shadow-sm, 0 1px 3px 0 rgba(0, 0, 0, 0.1));
        }

        .hph-view-btn.active:hover {
            background: var(--hph-primary-dark, #1d4ed8);
            color: var(--hph-white, #ffffff);
        }

        .hph-map-search-input {
            flex: 1;
            min-width: 200px;
            padding: 12px 16px;
            border: 2px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            background: #ffffff;
            transition: all 0.2s ease;
            outline: none;
        }

        .hph-map-search-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .hph-map-filter-select {
            padding: 12px 16px;
            border: 2px solid #d1d5db;
            border-radius: 8px;
            background: #ffffff;
            font-size: 14px;
            color: #374151;
            cursor: pointer;
            transition: all 0.2s ease;
            outline: none;
            min-width: 120px;
        }

        .hph-map-filter-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .hph-map-search-btn {
            padding: 12px 20px;
            background: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .hph-map-search-btn:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        /* Sidebar with HPH panel styling */
        .hph-map-panel {
            width: 400px;
            min-width: 350px;
            background: #ffffff;
            border-left: 2px solid #e5e7eb;
            overflow: hidden;
            z-index: 1000;
            display: flex !important;
            flex-direction: column;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            /* Debug styling to ensure visibility */
            visibility: visible !important;
            opacity: 1 !important;
        }

        .hph-map-panel-header {
            padding: var(--hph-spacing-xl, 24px) var(--hph-spacing-lg, 20px);
            border-bottom: 1px solid var(--hph-gray-200, #e5e7eb);
            background: var(--hph-white, #ffffff);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .hph-map-panel-title {
            font-size: var(--hph-text-lg, 18px);
            font-weight: 600;
            color: var(--hph-gray-900, #111827);
        }

        .hph-map-panel-count {
            font-size: var(--hph-text-sm, 14px);
            color: var(--hph-gray-600, #6b7280);
            margin-top: var(--hph-spacing-xs, 4px);
        }

        .hph-close-map-btn {
            background: var(--hph-gray-600, #6b7280);
            color: var(--hph-white, #ffffff);
            border: none;
            padding: var(--hph-spacing-sm, 8px) var(--hph-spacing-md, 16px);
            border-radius: var(--hph-radius-md, 8px);
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: var(--hph-spacing-xs, 6px);
            font-size: var(--hph-text-sm, 14px);
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .hph-close-map-btn:hover {
            background: var(--hph-gray-700, #374151);
            text-decoration: none;
            color: var(--hph-white, #ffffff);
            transform: translateY(-1px);
        }

        .hph-map-panel-content {
            flex: 1;
            overflow-y: auto;
            padding: 0;
        }

        /* Map cards using existing HPH card system */
        .hph-map-listings {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .hph-map-card {
            border-bottom: 1px solid var(--hph-gray-100, #f3f4f6);
            padding: var(--hph-spacing-lg, 20px);
            transition: all 0.2s ease;
            cursor: pointer;
            position: relative;
        }

        .hph-map-card:hover {
            background-color: var(--hph-gray-50, #f9fafb);
        }

        .hph-map-card.active {
            background-color: var(--hph-primary-50, #eff6ff);
            border-left: 3px solid var(--hph-primary, #2563eb);
        }

        .hph-map-card-content {
            display: flex;
            gap: var(--hph-spacing-md, 16px);
            align-items: flex-start;
        }

        .hph-map-card-image {
            width: 100px;
            height: 80px;
            border-radius: var(--hph-radius-lg, 12px);
            overflow: hidden;
            flex-shrink: 0;
            background: var(--hph-gray-100, #f3f4f6);
            position: relative;
        }

        .hph-map-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .hph-map-card:hover .hph-map-card-image img {
            transform: scale(1.05);
        }

        .hph-map-card-details {
            flex: 1;
            min-width: 0;
        }

        .hph-map-card-price {
            font-size: var(--hph-text-lg, 18px);
            font-weight: 700;
            color: var(--hph-primary, #2563eb);
            margin: 0 0 var(--hph-spacing-xs, 6px) 0;
        }

        .hph-map-card-title {
            font-size: var(--hph-text-base, 16px);
            font-weight: 600;
            color: var(--hph-gray-900, #111827);
            margin: 0 0 var(--hph-spacing-xs, 6px) 0;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .hph-map-card-address {
            font-size: var(--hph-text-sm, 14px);
            color: var(--hph-gray-600, #6b7280);
            margin: 0 0 var(--hph-spacing-sm, 10px) 0;
            line-height: 1.3;
        }

        .hph-map-card-features {
            display: flex;
            gap: var(--hph-spacing-sm, 10px);
            font-size: var(--hph-text-xs, 12px);
            color: var(--hph-gray-500, #6b7280);
        }

        .hph-map-card-features .feature {
            display: flex;
            align-items: center;
            gap: var(--hph-spacing-xs, 4px);
        }

        .hph-map-card-features .feature i {
            font-size: 10px;
            color: var(--hph-gray-400, #9ca3af);
        }

        /* Loading animation with HPH styling */
        .hph-map-loading {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            background: var(--hph-gray-50, #f9fafb);
            color: var(--hph-gray-600, #6b7280);
        }

        .hph-loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid var(--hph-gray-200, #e5e7eb);
            border-top: 4px solid var(--hph-primary, #2563eb);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto var(--hph-spacing-lg, 20px);
        }

        /* Responsive design */
        @media (max-width: 1200px) {
            .hph-map-panel {
                width: 350px;
                min-width: 300px;
            }
        }

        @media (max-width: 768px) {
            .hph-map-layout {
                flex-direction: column;
            }

            .hph-map-container {
                height: 60vh;
            }

            .hph-map-panel {
                width: 100%;
                height: 40vh;
                min-width: unset;
                border-left: none;
                border-top: 1px solid var(--hph-gray-200, #e5e7eb);
            }

            .hph-map-header {
                right: var(--hph-spacing-lg, 20px);
                left: var(--hph-spacing-lg, 20px);
            }

            .hph-map-search-container {
                flex-direction: column;
                align-items: stretch;
            }
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="hph-map-view">

<div class="hph-map-layout">
    <!-- Map Container -->
    <div class="hph-map-container">
        <!-- Map Search Header with View Controls -->
        <div class="hph-map-header">
            <div class="hph-map-header-content">
                <!-- Search Form -->
                <form class="hph-map-search-form" method="GET" action="<?php echo get_post_type_archive_link('listing'); ?>">
                    <input type="hidden" name="view" value="map">
                    <div class="hph-map-search-container">
                        <input type="text"
                               name="s"
                               placeholder="Search by address, city, or ZIP code"
                               class="hph-map-search-input"
                               value="<?php echo esc_attr($search_query); ?>">

                        <select name="property_type" class="hph-map-filter-select">
                            <option value="">Property Type</option>
                            <option value="single-family" <?php selected($property_type, 'single-family'); ?>>Single Family</option>
                            <option value="condo" <?php selected($property_type, 'condo'); ?>>Condo</option>
                            <option value="townhouse" <?php selected($property_type, 'townhouse'); ?>>Townhouse</option>
                            <option value="multi-family" <?php selected($property_type, 'multi-family'); ?>>Multi-Family</option>
                        </select>

                        <select name="min_price" class="hph-map-filter-select">
                            <option value="">Min Price</option>
                            <?php
                            $price_options = [100000, 150000, 200000, 250000, 300000, 400000, 500000, 600000, 700000, 800000, 900000, 1000000];
                            foreach ($price_options as $price) :
                                $selected = ($min_price == $price) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $price; ?>" <?php echo $selected; ?>>
                                    $<?php echo number_format($price / 1000) . 'k'; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <select name="max_price" class="hph-map-filter-select">
                            <option value="">Max Price</option>
                            <?php
                            $max_price_options = [200000, 300000, 400000, 500000, 600000, 700000, 800000, 900000, 1000000, 1500000, 2000000];
                            foreach ($max_price_options as $price) :
                                $selected = ($max_price == $price) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $price; ?>" <?php echo $selected; ?>>
                                    $<?php echo number_format($price / 1000) . 'k'; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <select name="bedrooms" class="hph-map-filter-select">
                            <option value="">Beds</option>
                            <?php for ($i = 1; $i <= 5; $i++) : ?>
                                <option value="<?php echo $i; ?>" <?php selected($bedrooms, $i); ?>>
                                    <?php echo $i; ?>+
                                </option>
                            <?php endfor; ?>
                        </select>

                        <select name="bathrooms" class="hph-map-filter-select">
                            <option value="">Baths</option>
                            <?php for ($i = 1; $i <= 4; $i++) : ?>
                                <option value="<?php echo $i; ?>" <?php selected($bathrooms, $i); ?>>
                                    <?php echo $i; ?>+
                                </option>
                            <?php endfor; ?>
                        </select>

                        <button type="submit" class="hph-map-search-btn">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </form>

                <!-- View Controls -->
                <div class="hph-map-view-controls">
                    <div class="hph-view-toggle-group" role="group" aria-label="View options">
                        <?php
                        // Build URLs that preserve all current parameters
                        $current_params = $_GET;
                        
                        // Grid view (remove view parameter)
                        $grid_params = $current_params;
                        unset($grid_params['view']);
                        $grid_url = get_post_type_archive_link('listing');
                        if (!empty($grid_params)) {
                            $grid_url = add_query_arg($grid_params, $grid_url);
                        }
                        
                        // List view
                        $list_params = $current_params;
                        $list_params['view'] = 'list';
                        $list_url = add_query_arg($list_params, get_post_type_archive_link('listing'));
                        
                        // Map view (current)
                        $map_params = $current_params;
                        $map_params['view'] = 'map';
                        $map_url = add_query_arg($map_params, get_post_type_archive_link('listing'));
                        ?>
                        <a href="<?php echo esc_url($grid_url); ?>"
                           class="hph-view-btn"
                           data-view="grid"
                           title="Grid View"
                           aria-pressed="false">
                            <i class="fas fa-th-large"></i>
                            <span class="sr-only">Grid</span>
                        </a>
                        <a href="<?php echo esc_url($list_url); ?>"
                           class="hph-view-btn"
                           data-view="list"
                           title="List View"
                           aria-pressed="false">
                            <i class="fas fa-list"></i>
                            <span class="sr-only">List</span>
                        </a>
                        <button type="button"
                                class="hph-view-btn active"
                                data-view="map"
                                title="Map View"
                                aria-pressed="true">
                            <i class="fas fa-map-marked-alt"></i>
                            <span class="sr-only">Map</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map Element -->
        <div id="mapbox-listings-map"
             class="hph-map-canvas"
             data-map-center="<?php echo esc_attr(json_encode($map_center)); ?>"
             data-map-zoom="11"
             data-map-style="mapbox://styles/mapbox/light-v11"
             data-map-listings="<?php echo esc_attr(json_encode($map_listings)); ?>"
             style="height: 100vh; width: 100%;">
            <div class="hph-map-loading">
                <div style="text-align: center;">
                    <div class="hph-loading-spinner"></div>
                    <p style="color: var(--hph-gray-600, #6b7280); font-size: var(--hph-text-sm, 14px); margin: 0;">Loading map...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar using HPH panel styling -->
    <div class="hph-map-panel">
        <div class="hph-map-panel-header">
            <div>
                <div class="hph-map-panel-title">Properties</div>
                <div class="hph-map-panel-count">
                    <?php echo number_format($listings->found_posts); ?> found
                </div>
            </div>
            <?php
            // Generate clean close URL without view parameter
            $close_url = get_post_type_archive_link('listing');
            $current_params = $_GET;
            unset($current_params['view']); // Remove view parameter completely
            if (!empty($current_params)) {
                $close_url = add_query_arg($current_params, $close_url);
            }
            ?>
            <a href="<?php echo esc_url($close_url); ?>" class="hph-close-map-btn">
                <i class="fas fa-times"></i> Close Map
            </a>
        </div>

        <div class="hph-map-panel-content">
            <div class="hph-map-listings">
                <?php
                // Debug output
                echo '<!-- Debug: Total listings found: ' . $listings->found_posts . ' -->';
                echo '<!-- Debug: Posts per page: ' . $listings->query_vars['posts_per_page'] . ' -->';

                if ($listings->have_posts()) : ?>
                    <?php while ($listings->have_posts()) : $listings->the_post();
                        $listing_id = get_the_ID();

                        // Get address components
                        $street_address = '';
                        if (function_exists('hpt_get_listing_address')) {
                            try {
                                $address_data = hpt_get_listing_address($listing_id);
                                $street_address = $address_data['street_address'] ?? '';
                            } catch (Exception $e) {
                                $street_address = trim((get_field('street_number', $listing_id) ?: '') . ' ' . (get_field('street_name', $listing_id) ?: '') . ' ' . (get_field('street_type', $listing_id) ?: ''));
                            }
                        } else {
                            $street_address = trim((get_field('street_number', $listing_id) ?: '') . ' ' . (get_field('street_name', $listing_id) ?: '') . ' ' . (get_field('street_type', $listing_id) ?: ''));
                        }

                        $city = get_field('city', $listing_id);
                        $state = get_field('state', $listing_id);
                        $location = implode(', ', array_filter([$city, $state]));

                        $price = get_field('price', $listing_id);
                        $bedrooms = get_field('bedrooms', $listing_id);
                        $bathrooms = get_field('bathrooms_full', $listing_id);
                        $sqft = get_field('square_feet', $listing_id);

                        // Debug output for each listing
                        echo '<!-- Debug: Processing listing ID: ' . $listing_id . ', Title: ' . get_the_title() . ' -->';
                    ?>
                        <div class="hph-map-card" data-listing-id="<?php echo $listing_id; ?>" style="border: 1px solid #e5e7eb; margin-bottom: 1px; padding: 16px; background: white;">
                            <div class="hph-map-card-content">
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="hph-map-card-image">
                                        <img src="<?php echo get_the_post_thumbnail_url($listing_id, 'medium'); ?>"
                                             alt="<?php echo esc_attr(get_the_title()); ?>"
                                             loading="lazy">
                                    </div>
                                <?php endif; ?>

                                <div class="hph-map-card-details">
                                    <?php if ($price) : ?>
                                        <div class="hph-map-card-price">
                                            $<?php echo number_format($price); ?>
                                        </div>
                                    <?php endif; ?>

                                    <h3 class="hph-map-card-title">
                                        <a href="<?php echo get_permalink(); ?>">
                                            <?php echo $street_address ?: get_the_title(); ?>
                                        </a>
                                    </h3>

                                    <?php if ($location && $location !== $street_address) : ?>
                                        <div class="hph-map-card-address"><?php echo $location; ?></div>
                                    <?php endif; ?>

                                    <?php if ($bedrooms || $bathrooms || $sqft) : ?>
                                        <div class="hph-map-card-features">
                                            <?php if ($bedrooms) : ?>
                                                <div class="feature">
                                                    <i class="fas fa-bed"></i>
                                                    <span><?php echo $bedrooms; ?> bed<?php echo $bedrooms != 1 ? 's' : ''; ?></span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($bathrooms) : ?>
                                                <div class="feature">
                                                    <i class="fas fa-bath"></i>
                                                    <span><?php echo $bathrooms; ?> bath<?php echo $bathrooms != 1 ? 's' : ''; ?></span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($sqft) : ?>
                                                <div class="feature">
                                                    <i class="fas fa-ruler-combined"></i>
                                                    <span><?php echo number_format($sqft); ?> sq ft</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else : ?>
                    <div class="hph-map-empty-state" style="text-align: center; padding: 40px; color: var(--hph-gray-600, #6b7280);">
                        <i class="fas fa-search" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5; color: var(--hph-gray-400, #9ca3af);"></i>
                        <h3 style="color: var(--hph-gray-900, #111827); margin-bottom: 8px;">No Properties Found</h3>
                        <p style="margin-bottom: 20px;">Try adjusting your search criteria or <a href="<?php echo get_post_type_archive_link('listing'); ?>" style="color: var(--hph-primary, #2563eb);">view all properties</a>.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Map initialization using bundled HPH system
document.addEventListener('DOMContentLoaded', function() {
    // Set body class to indicate map view
    document.body.classList.add('hph-map-view');
    
    // Prevent automatic view switching to map by overriding default view behavior
    if (window.hphArchiveMap) {
        // Override any default view switching
        window.hphArchiveMap.default_view = 'grid'; // Reset to grid to prevent persistence
    }

    // Initialize map after a brief delay to ensure all scripts are loaded
    setTimeout(function() {
        if (window.HPHArchiveMap && window.HPHArchiveMap.initializeMapView) {
            window.HPHArchiveMap.initializeMapView();
        } else {
            console.log('HPH Archive Map system not available, using fallback initialization');
            initializeFallbackMap();
        }
    }, 1000);

    function initializeFallbackMap() {
        const mapContainer = document.getElementById('mapbox-listings-map');
        if (!mapContainer || !window.hph_mapbox_config || !window.hphArchiveMap) return;

        if (typeof mapboxgl === 'undefined') {
            console.error('Mapbox GL JS not loaded');
            return;
        }

        try {
            mapboxgl.accessToken = window.hph_mapbox_config.access_token;
            const mapData = window.hphArchiveMap;
            const listings = mapData.listings || [];
            const center = mapData.center || [-75.1398, 38.7816];

            // Initialize with HPHMap if available
            if (typeof HPHMap !== 'undefined') {
                const mapInstance = new HPHMap(mapContainer, {
                    center: center,
                    zoom: 11,
                    styleTheme: 'professional',
                    markerTheme: 'happyPlace'
                });

                mapInstance.map.on('load', function() {
                    mapInstance.addListingMarkers(listings, {
                        enableClustering: mapData.enable_clustering || true,
                        clusterRadius: mapData.cluster_radius || 50,
                        clusterMaxZoom: mapData.cluster_max_zoom || 14,
                        showPopup: true,
                        fitBounds: listings.length > 1
                    });

                    // Hide loading indicator
                    const loadingEl = mapContainer.querySelector('.hph-map-loading');
                    if (loadingEl) loadingEl.style.display = 'none';

                    // Setup sidebar sync
                    setupSidebarSync(mapInstance);
                });
            }
        } catch (error) {
            console.error('Map initialization failed:', error);
        }
    }

    function setupSidebarSync(mapInstance) {
        const mapCards = document.querySelectorAll('.hph-map-card');

        mapCards.forEach(card => {
            card.addEventListener('click', function() {
                const listingId = this.dataset.listingId;

                // Update card states
                mapCards.forEach(c => c.classList.remove('active'));
                this.classList.add('active');

                // Highlight on map
                if (mapInstance && mapInstance.highlightListingMarker) {
                    mapInstance.highlightListingMarker(listingId);
                }
            });
        });

        // Listen for map clicks to sync sidebar
        document.addEventListener('hph-map-listing-click', function(event) {
            const listingId = event.detail.listingId;
            const targetCard = document.querySelector(`[data-listing-id="${listingId}"]`);
            if (targetCard) {
                mapCards.forEach(c => c.classList.remove('active'));
                targetCard.classList.add('active');
                targetCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });
    }
});
</script>

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<?php wp_footer(); ?>
</body>
</html>