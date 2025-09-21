<?php
/**
 * Single Local Place Template - Magazine Style
 * 
 * @package HappyPlaceTheme
 * @since 1.0.0
 */

// Get place data
$place_id = get_the_ID();
$title = get_the_title();
$content = get_the_content();
$excerpt = get_the_excerpt();
$featured_image_id = get_post_thumbnail_id();

// ACF Fields
$primary_city = get_field('primary_city');
$micro_summary = get_field('micro_summary');
$address = get_field('address');
$phone = get_field('phone');
$website = get_field('website');
$reservation_url = get_field('reservation_url');
$menu_url = get_field('menu_url');
$instagram_url = get_field('instagram_url');
$facebook_url = get_field('facebook_url');
$price_range = get_field('price_range');
$is_family_friendly = get_field('is_family_friendly');
$quick_flags = get_field('quick_flags');
$accessibility = get_field('accessibility');
$hours_json = get_field('hours_json');
$hours_notes = get_field('hours_notes');
$why_we_love_it = get_field('why_we_love_it');
$menu_highlights = get_field('menu_highlights');
$accolades = get_field('accolades');
$quotes = get_field('quotes');
$related_places = get_field('related_places');

// Parse hours
$hours = [];
if ($hours_json) {
    $hours = json_decode($hours_json, true) ?: [];
}

// Get additional images for photo collage
$gallery = get_field('gallery');
$photos = [];
if ($featured_image_id) {
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
    // Hero Section - Featured image with overlay content
    get_template_part('template-parts/sections/hero', null, [
        'style' => 'image',
        'height' => 'lg',
        'is_top_of_page' => true,
        'background_image' => $featured_image_id ? wp_get_attachment_image_url($featured_image_id, 'full') : '',
        'overlay' => 'dark',
        'overlay_opacity' => '50',
        'alignment' => 'left',
        'content_width' => 'wide',
        'badge' => $primary_city ? $primary_city->post_title : '',
        'headline' => $title,
        'subheadline' => $micro_summary ?: $excerpt,
        'buttons' => array_filter([
            $phone ? [
                'text' => 'Call Now',
                'url' => 'tel:' . $phone,
                'style' => 'primary',
                'icon' => 'phone'
            ] : null,
            $website ? [
                'text' => 'Visit Website',
                'url' => $website,
                'style' => 'outline-primary',
                'icon' => 'external-link',
                'target' => '_blank'
            ] : null,
            $reservation_url ? [
                'text' => 'Make Reservation',
                'url' => $reservation_url,
                'style' => 'secondary',
                'icon' => 'calendar',
                'target' => '_blank'
            ] : null
        ]),
        'section_id' => 'place-hero'
    ]);
    ?>

    <?php if ($why_we_love_it): ?>
    <?php
    // Why We Love It Section
    get_template_part('template-parts/sections/content', null, [
        'layout' => 'centered',
        'background' => 'white',
        'padding' => 'xl',
        'content_width' => 'normal',
        'alignment' => 'left',
        'badge' => 'Why We Love It',
        'headline' => 'What Makes ' . $title . ' Special',
        'content' => $why_we_love_it,
        'section_id' => 'why-we-love-it'
    ]);
    ?>
    <?php endif; ?>

    <?php if ($menu_highlights && is_array($menu_highlights)): ?>
    <?php
    // Menu Highlights Section
    $features = [];
    foreach ($menu_highlights as $item) {
        if (!empty($item['item'])) {
            $features[] = [
                'icon' => 'utensils',
                'title' => $item['item'],
                'content' => $item['note'] . ($item['price'] ? ' - ' . $item['price'] : ''),
                'link' => !empty($item['link']) ? $item['link'] : null
            ];
        }
    }
    
    if (!empty($features)) {
        get_template_part('template-parts/sections/features', null, [
            'layout' => 'grid',
            'background' => 'light',
            'padding' => 'xl',
            'columns' => min(3, count($features)),
            'badge' => 'Menu Highlights',
            'headline' => 'Must-Try Items',
            'subheadline' => 'Our favorite dishes that keep us coming back',
            'features' => $features,
            'icon_style' => 'filled',
            'section_id' => 'menu-highlights'
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
        'content' => $content ?: 'Step inside and discover what makes this place truly special.',
        'animation' => true,
        'hover_effects' => true,
        'section_id' => 'photo-experience'
    ]);
    ?>
    <?php endif; ?>

    <?php if ($quotes && is_array($quotes)): ?>
    <?php
    // Agent Quotes & Tips Section
    $testimonials = [];
    foreach ($quotes as $quote) {
        if (!empty($quote['text']) && !empty($quote['person'])) {
            $person = is_array($quote['person']) ? $quote['person'][0] : $quote['person'];
            $testimonials[] = [
                'quote' => $quote['text'],
                'name' => get_the_title($person),
                'title' => get_field('title', $person) ?: 'Local Expert',
                'image' => get_post_thumbnail_id($person) ? wp_get_attachment_image_url(get_post_thumbnail_id($person), 'medium') : '',
                'rating' => 5
            ];
        }
    }
    
    if (!empty($testimonials)) {
        get_template_part('template-parts/sections/testimonials', null, [
            'background' => 'primary',
            'padding' => 'xl',
            'badge' => 'Local Insights',
            'headline' => 'Tips from Our Team',
            'subheadline' => 'Insider knowledge from the people who know this place best',
            'testimonials' => $testimonials,
            'layout' => 'carousel',
            'section_id' => 'local-tips'
        ]);
    }
    ?>
    <?php endif; ?>

    <?php
    // Details & Information Section
    $details_content = '';
    
    // Hours
    if (!empty($hours)) {
        $details_content .= '<div class="hph-place-hours"><h4>Hours</h4>';
        $details_content .= '<div class="hph-hours-grid">';
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        foreach ($days as $day) {
            if (isset($hours[$day])) {
                $details_content .= '<div class="hph-hours-row">';
                $details_content .= '<span class="hph-day">' . ucfirst($day) . '</span>';
                $details_content .= '<span class="hph-time">' . ($hours[$day] === 'closed' ? 'Closed' : $hours[$day]) . '</span>';
                $details_content .= '</div>';
            }
        }
        $details_content .= '</div>';
        if ($hours_notes) {
            $details_content .= '<p class="hph-hours-notes">' . esc_html($hours_notes) . '</p>';
        }
        $details_content .= '</div>';
    }
    
    // Contact Info
    if ($address || $phone) {
        $details_content .= '<div class="hph-place-contact"><h4>Contact & Location</h4>';
        if ($address) {
            $details_content .= '<p class="hph-address">' . nl2br(esc_html($address)) . '</p>';
        }
        if ($phone) {
            $details_content .= '<p class="hph-phone"><a href="tel:' . esc_attr($phone) . '">' . esc_html($phone) . '</a></p>';
        }
        $details_content .= '</div>';
    }
    
    // Quick Info
    $quick_info = [];
    if ($price_range) $quick_info[] = 'Price Range: ' . $price_range;
    if ($is_family_friendly) $quick_info[] = 'Family Friendly';
    if ($quick_flags && is_array($quick_flags)) {
        foreach ($quick_flags as $flag) {
            $quick_info[] = ucwords(str_replace('_', ' ', $flag));
        }
    }
    
    if (!empty($quick_info)) {
        $details_content .= '<div class="hph-place-quick-info"><h4>Good to Know</h4>';
        $details_content .= '<ul class="hph-quick-list">';
        foreach ($quick_info as $info) {
            $details_content .= '<li>' . esc_html($info) . '</li>';
        }
        $details_content .= '</ul></div>';
    }
    
    // Accessibility
    if ($accessibility && is_array($accessibility)) {
        $details_content .= '<div class="hph-place-accessibility"><h4>Accessibility</h4>';
        $details_content .= '<ul class="hph-accessibility-list">';
        foreach ($accessibility as $feature) {
            $details_content .= '<li>' . esc_html(ucwords(str_replace('_', ' ', $feature))) . '</li>';
        }
        $details_content .= '</ul></div>';
    }
    
    if (!empty($details_content)) {
        get_template_part('template-parts/sections/content', null, [
            'layout' => 'two-column',
            'background' => 'light',
            'padding' => 'xl',
            'content_width' => 'normal',
            'alignment' => 'left',
            'badge' => 'Details',
            'headline' => 'Plan Your Visit',
            'content' => $details_content,
            'section_id' => 'place-details'
        ]);
    }
    ?>

    <?php if ($accolades && is_array($accolades) && !empty($accolades)): ?>
    <?php
    // Accolades Section
    $accolades_content = '<div class="hph-accolades-grid">';
    foreach ($accolades as $accolade) {
        if (!empty($accolade['text'])) {
            $accolades_content .= '<div class="hph-accolade-item">';
            if (!empty($accolade['source'])) {
                $accolades_content .= '<h5 class="hph-accolade-source">' . esc_html($accolade['source']) . '</h5>';
            }
            $accolades_content .= '<p class="hph-accolade-text">' . esc_html($accolade['text']) . '</p>';
            if (!empty($accolade['year'])) {
                $accolades_content .= '<span class="hph-accolade-year">' . esc_html($accolade['year']) . '</span>';
            }
            $accolades_content .= '</div>';
        }
    }
    $accolades_content .= '</div>';
    
    get_template_part('template-parts/sections/content', null, [
        'layout' => 'centered',
        'background' => 'white',
        'padding' => 'lg',
        'content_width' => 'normal',
        'alignment' => 'center',
        'badge' => 'Recognition',
        'headline' => 'Awards & Press',
        'content' => $accolades_content,
        'section_id' => 'accolades'
    ]);
    ?>
    <?php endif; ?>

    <?php if ($related_places && is_array($related_places) && !empty($related_places)): ?>
    <?php
    // Related Places Section
    $related_content = '<div class="hph-related-places-grid">';
    foreach (array_slice($related_places, 0, 3) as $related_place) {
        $related_content .= '<div class="hph-related-place-card">';
        if (has_post_thumbnail($related_place->ID)) {
            $related_content .= '<div class="hph-card-image">';
            $related_content .= '<a href="' . get_permalink($related_place->ID) . '">';
            $related_content .= get_the_post_thumbnail($related_place->ID, 'medium', ['loading' => 'lazy']);
            $related_content .= '</a></div>';
        }
        $related_content .= '<div class="hph-card-content">';
        $related_content .= '<h4><a href="' . get_permalink($related_place->ID) . '">' . esc_html($related_place->post_title) . '</a></h4>';
        if ($related_place->post_excerpt) {
            $related_content .= '<p>' . esc_html($related_place->post_excerpt) . '</p>';
        }
        $related_city = get_field('primary_city', $related_place->ID);
        if ($related_city) {
            $related_content .= '<p class="hph-place-location">' . esc_html($related_city->post_title) . '</p>';
        }
        $related_content .= '</div></div>';
    }
    $related_content .= '</div>';
    
    get_template_part('template-parts/sections/content', null, [
        'layout' => 'centered',
        'background' => 'light',
        'padding' => 'xl',
        'content_width' => 'wide',
        'alignment' => 'center',
        'badge' => 'Discover More',
        'headline' => 'You Might Also Love',
        'subheadline' => 'Similar places that caught our attention',
        'content' => $related_content,
        'section_id' => 'related-places'
    ]);
    ?>
    <?php endif; ?>

</main>

<?php get_footer(); ?>
