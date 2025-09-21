<?php
/**
 * Single City Template - Magazine Style
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Get city data
$city_id = get_the_ID();
$title = get_the_title();
$content = get_the_content();
$excerpt = get_the_excerpt();
$featured_image_id = get_post_thumbnail_id();

// ACF Fields - Only using actual city fields from ACF JSON
$state = get_field('state');
$county = get_field('county');
$population = get_field('population');
$lat = get_field('lat');
$lng = get_field('lng');
$tagline = get_field('tagline');
$description = get_field('description');
$hero_image = get_field('hero_image');
$gallery = get_field('gallery');
$related_places = get_field('related_places');
$featured_places = get_field('featured_places');

// Use hero image if available, otherwise featured image
$hero_image_url = '';
if ($hero_image) {
    $hero_image_url = wp_get_attachment_image_url($hero_image, 'full');
} elseif ($featured_image_id) {
    $hero_image_url = wp_get_attachment_image_url($featured_image_id, 'full');
}

// Build location string
$location_parts = array_filter([$state, $county]);
$location_string = implode(', ', $location_parts);

// Get additional images for photo collage
$photos = [];
if ($hero_image) {
    $photos[] = [
        'url' => wp_get_attachment_image_url($hero_image, 'large'),
        'alt' => get_post_meta($hero_image, '_wp_attachment_image_alt', true),
        'caption' => wp_get_attachment_caption($hero_image)
    ];
} elseif ($featured_image_id) {
    $photos[] = [
        'url' => wp_get_attachment_image_url($featured_image_id, 'large'),
        'alt' => get_post_meta($featured_image_id, '_wp_attachment_image_alt', true),
        'caption' => wp_get_attachment_caption($featured_image_id)
    ];
}

if ($gallery && is_array($gallery)) {
    foreach (array_slice($gallery, 0, 4) as $image_id) {
        $photos[] = [
            'url' => wp_get_attachment_image_url($image_id, 'large'),
            'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true),
            'caption' => wp_get_attachment_caption($image_id)
        ];
    }
}

get_header();
?>

<main class="hph-main">

    <?php
    // Hero Section - City image with overlay content
    get_template_part('template-parts/sections/hero', null, [
        'style' => 'image',
        'height' => 'lg',
        'is_top_of_page' => true,
        'background_image' => $hero_image_url,
        'overlay' => 'dark',
        'overlay_opacity' => '40',
        'alignment' => 'center',
        'content_width' => 'normal',
        'badge' => 'City Guide',
        'headline' => $location_string ?: $title,
        'subheadline' => $title,
        'buttons' => array_filter([
            $lat && $lng ? [
                'text' => 'View on Map',
                'url' => "https://maps.google.com/?q={$lat},{$lng}",
                'style' => 'primary',
                'icon' => 'map-marker',
                'target' => '_blank'
            ] : null
        ]),
        'section_id' => 'city-hero'
    ]);
    ?>

    <?php if ($description): ?>
    <?php
    // City Description Section
    get_template_part('template-parts/sections/content', null, [
        'layout' => 'centered',
        'background' => 'white',
        'padding' => 'xl',
        'content_width' => 'normal',
        'alignment' => 'left',
        'badge' => 'About ' . $title,
        'headline' => 'Discover ' . $title,
        'content' => $description,
        'section_id' => 'city-description'
    ]);
    ?>
    <?php endif; ?>

    <?php if ($featured_places && is_array($featured_places) && !empty($featured_places)): ?>
    <?php
    // Featured Places Section
    $features = [];
    foreach (array_slice($featured_places, 0, 6) as $place_id) {
        $place = get_post($place_id);
        if ($place) {
            $place_excerpt = get_the_excerpt($place_id) ?: get_field('micro_summary', $place_id);
            $place_type = get_field('place_type', $place_id) ?: 'Local Place';
            
            $features[] = [
                'icon' => 'map-pin',
                'title' => $place->post_title,
                'content' => $place_excerpt ?: $place_type,
                'link' => get_permalink($place_id)
            ];
        }
    }
    
    if (!empty($features)) {
        get_template_part('template-parts/sections/features', null, [
            'layout' => 'grid',
            'background' => 'light',
            'padding' => 'xl',
            'columns' => min(3, count($features)),
            'badge' => 'Featured Places',
            'headline' => 'Must-Visit Spots in ' . $title,
            'subheadline' => 'Discover the best places that make this city special',
            'features' => $features,
            'icon_style' => 'filled',
            'section_id' => 'featured-places'
        ]);
    }
    ?>
    <?php endif; ?>

    <?php if (!empty($photos) && count($photos) > 1): ?>
    <?php
    // Photo Collage Section
    get_template_part('template-parts/sections/content-photo-collage', null, [
        'layout' => 'collage-right',
        'background' => 'white',
        'padding' => 'xl',
        'content_width' => 'wide',
        'alignment' => 'left',
        'photos' => $photos,
        'collage_style' => 'organic',
        'headline' => 'Experience ' . $title,
        'content' => $content ?: 'Explore the sights, sounds, and culture that make this city unique.',
        'animation' => true,
        'hover_effects' => true,
        'section_id' => 'photo-experience'
    ]);
    ?>
    <?php endif; ?>

    <?php
    // City Stats & Information Section
    $details_content = '';
    
    // Location & Demographics
    if ($state || $county || $population) {
        $details_content .= '<div class="hph-city-stats"><h4>City Overview</h4>';
        $details_content .= '<div class="hph-stats-grid">';
        
        if ($state) {
            $details_content .= '<div class="hph-stat-item">';
            $details_content .= '<span class="hph-stat-label">State</span>';
            $details_content .= '<span class="hph-stat-value">' . esc_html($state) . '</span>';
            $details_content .= '</div>';
        }
        
        if ($county) {
            $details_content .= '<div class="hph-stat-item">';
            $details_content .= '<span class="hph-stat-label">County</span>';
            $details_content .= '<span class="hph-stat-value">' . esc_html($county) . '</span>';
            $details_content .= '</div>';
        }
        
        if ($population) {
            $details_content .= '<div class="hph-stat-item">';
            $details_content .= '<span class="hph-stat-label">Population</span>';
            $details_content .= '<span class="hph-stat-value">' . number_format((int)$population) . '</span>';
            $details_content .= '</div>';
        }
        
        $details_content .= '</div></div>';
    }
    
    // Location coordinates
    if ($lat && $lng) {
        $details_content .= '<div class="hph-city-location"><h4>Location</h4>';
        $details_content .= '<p class="hph-coordinates">Coordinates: ' . esc_html($lat) . ', ' . esc_html($lng) . '</p>';
        $details_content .= '<p><a href="https://maps.google.com/?q=' . esc_attr($lat) . ',' . esc_attr($lng) . '" target="_blank" rel="noopener">View on Google Maps</a></p>';
        $details_content .= '</div>';
    }
    
    if (!empty($details_content)) {
        get_template_part('template-parts/sections/content', null, [
            'layout' => 'two-column',
            'background' => 'light',
            'padding' => 'xl',
            'content_width' => 'normal',
            'alignment' => 'left',
            'badge' => 'City Info',
            'headline' => 'About ' . $title,
            'content' => $details_content,
            'section_id' => 'city-details'
        ]);
    }
    ?>

    <?php if ($related_places && is_array($related_places) && !empty($related_places)): ?>
    <?php
    // Related Places Section
    $related_content = '<div class="hph-related-places-grid">';
    foreach (array_slice($related_places, 0, 6) as $place_id) {
        $place = get_post($place_id);
        if ($place) {
            $related_content .= '<div class="hph-related-place-card">';
            if (has_post_thumbnail($place_id)) {
                $related_content .= '<div class="hph-card-image">';
                $related_content .= '<a href="' . get_permalink($place_id) . '">';
                $related_content .= get_the_post_thumbnail($place_id, 'medium', ['loading' => 'lazy']);
                $related_content .= '</a></div>';
            }
            $related_content .= '<div class="hph-card-content">';
            $related_content .= '<h4><a href="' . get_permalink($place_id) . '">' . esc_html($place->post_title) . '</a></h4>';
            
            $place_excerpt = get_the_excerpt($place_id) ?: get_field('micro_summary', $place_id);
            if ($place_excerpt) {
                $related_content .= '<p>' . esc_html($place_excerpt) . '</p>';
            }
            
            $place_type = get_field('place_type', $place_id);
            if ($place_type) {
                $related_content .= '<p class="hph-place-type">' . esc_html($place_type) . '</p>';
            }
            $related_content .= '</div></div>';
        }
    }
    $related_content .= '</div>';
    
    get_template_part('template-parts/sections/content', null, [
        'layout' => 'centered',
        'background' => 'white',
        'padding' => 'xl',
        'content_width' => 'wide',
        'alignment' => 'center',
        'badge' => 'Explore More',
        'headline' => 'Places to Visit in ' . $title,
        'subheadline' => 'Discover restaurants, shops, and attractions throughout the city',
        'content' => $related_content,
        'section_id' => 'related-places'
    ]);
    ?>
    <?php endif; ?>

</main>

<?php get_footer(); ?>

