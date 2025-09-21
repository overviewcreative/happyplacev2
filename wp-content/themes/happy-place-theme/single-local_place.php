<?php
/**
 * Single Place Template
 * 
 * @package HappyPlaceTheme
 * @since 1.0.0
 */

// Enqueue required assets
wp_enqueue_style('hph-local-single', get_template_directory_uri() . '/assets/css/framework/features/local/local-single.css', ['hph-framework'], '1.0.0');
wp_enqueue_script('hph-single-place', get_template_directory_uri() . '/assets/js/pages/single-place.js', ['hph-framework'], '1.0.0', true);

// Check for map functionality
$mapbox_token = '';
if (function_exists('hp_get_mapbox_token')) {
    $mapbox_token = hp_get_mapbox_token();
} elseif (defined('HP_MAPBOX_ACCESS_TOKEN')) {
    $mapbox_token = HP_MAPBOX_ACCESS_TOKEN;
}

if (!empty($mapbox_token)) {
    wp_enqueue_style('mapbox-gl-css', 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css', [], '2.15.0');
    wp_enqueue_script('mapbox-gl-js', 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js', [], '2.15.0', false);
}

// Get place data
$place_id = get_the_ID();
$title = get_the_title();
$content = get_the_content();
$excerpt = get_the_excerpt();

// ACF Fields
$primary_city = get_field('primary_city');
$address = get_field('address');
$phone = get_field('phone');
$website = get_field('website');
$price_range = get_field('price_range');
$is_family_friendly = get_field('is_family_friendly');
$lat = get_field('lat');
$lng = get_field('lng');
$hours_json = get_field('hours_json');
$source_url = get_field('source_url');
$attribution = get_field('attribution');

// Parse hours
$hours = [];
if ($hours_json) {
    $hours = json_decode($hours_json, true) ?: [];
}

// Get gallery images
$gallery = get_field('gallery');
$featured_image = get_post_thumbnail_id();

// Related places
$related_places = get_field('related_places') ?: [];

// Get events at this venue
$venue_events = new WP_Query([
    'post_type' => 'local_event',
    'posts_per_page' => 5,
    'meta_query' => [
        [
            'key' => 'venue_name',
            'value' => $title,
            'compare' => 'LIKE'
        ],
        [
            'key' => 'start_datetime',
            'value' => date('Y-m-d H:i:s'),
            'compare' => '>=',
            'type' => 'DATETIME'
        ]
    ],
    'meta_key' => 'start_datetime',
    'orderby' => 'meta_value',
    'order' => 'ASC'
]);

get_header();
?>

<article class="hph-single hph-single--place">
    
    <!-- Place Hero -->
    <section class="hph-place-hero">
        <?php if ($featured_image): ?>
        <div class="hph-place-hero__media">
            <div class="hph-place-hero__image-wrapper">
                <?php echo wp_get_attachment_image($featured_image, 'full', false, [
                    'class' => 'hph-place-hero__image',
                    'loading' => 'eager'
                ]); ?>
            </div>
            <div class="hph-place-hero__overlay"></div>
        </div>
        <?php endif; ?>
        
        <div class="hph-container">
            <div class="hph-place-hero__content">
                <div class="hph-place-hero__meta">
                    <?php if ($primary_city): ?>
                    <a href="<?php echo get_permalink($primary_city); ?>" class="hph-place-hero__city">
                        <i class="hph-icon hph-icon--location"></i>
                        <?php echo $primary_city->post_title; ?>
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($price_range): ?>
                    <span class="hph-place-hero__price"><?php echo esc_html($price_range); ?></span>
                    <?php endif; ?>
                </div>
                
                <h1 class="hph-place-hero__title"><?php echo esc_html($title); ?></h1>
                
                <?php if ($excerpt): ?>
                <p class="hph-place-hero__excerpt"><?php echo esc_html($excerpt); ?></p>
                <?php endif; ?>
                
                <div class="hph-place-hero__badges">
                    <?php if ($is_family_friendly): ?>
                    <span class="hph-badge hph-badge--family">
                        <i class="hph-icon hph-icon--family"></i>
                        Family Friendly
                    </span>
                    <?php endif; ?>
                </div>
                
                <div class="hph-place-hero__actions">
                    <?php if ($phone): ?>
                    <a href="tel:<?php echo esc_attr($phone); ?>" class="hph-btn hph-btn--primary">
                        <i class="hph-icon hph-icon--phone"></i>
                        Call Now
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($website): ?>
                    <a href="<?php echo esc_url($website); ?>" target="_blank" rel="noopener" class="hph-btn hph-btn--outline">
                        <i class="hph-icon hph-icon--external"></i>
                        Visit Website
                    </a>
                    <?php endif; ?>
                    
                    <button class="hph-btn hph-btn--ghost hph-place-favorite" data-place-id="<?php echo $place_id; ?>">
                        <i class="hph-icon hph-icon--heart-outline"></i>
                        Save
                    </button>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Main Content -->
    <section class="hph-single__content">
        <div class="hph-container">
            <div class="hph-layout hph-layout--sidebar-right">
                
                <!-- Main Content -->
                <div class="hph-layout__main">
                    
                    <!-- Description -->
                    <?php if ($content): ?>
                    <div class="hph-content-section">
                        <h2 class="hph-content-section__title">About <?php echo esc_html($title); ?></h2>
                        <div class="hph-content-section__content">
                            <?php echo apply_filters('the_content', $content); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Gallery -->
                    <?php if ($gallery && is_array($gallery)): ?>
                    <div class="hph-content-section">
                        <h2 class="hph-content-section__title">Photos</h2>
                        <div class="hph-place-gallery">
                            <?php foreach ($gallery as $image_id): ?>
                                <?php if ($image_id): ?>
                                <div class="hph-place-gallery__item">
                                    <a href="<?php echo wp_get_attachment_image_url($image_id, 'full'); ?>" 
                                       class="hph-place-gallery__link"
                                       data-fancybox="place-gallery">
                                        <?php echo wp_get_attachment_image($image_id, 'medium_large', false, [
                                            'class' => 'hph-place-gallery__image',
                                            'loading' => 'lazy'
                                        ]); ?>
                                    </a>
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Upcoming Events -->
                    <?php if ($venue_events->have_posts()): ?>
                    <div class="hph-content-section">
                        <h2 class="hph-content-section__title">Upcoming Events</h2>
                        <div class="hph-venue-events">
                            <?php while ($venue_events->have_posts()): $venue_events->the_post(); ?>
                                <?php get_template_part('template-parts/components/local/event-card', null, [
                                    'event_id' => get_the_ID(),
                                    'variant' => 'compact'
                                ]); ?>
                            <?php endwhile; wp_reset_postdata(); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Sidebar -->
                <div class="hph-layout__sidebar">
                    
                    <!-- Contact Info -->
                    <div class="hph-sidebar-widget">
                        <h3 class="hph-sidebar-widget__title">Contact Info</h3>
                        <div class="hph-place-contact">
                            <?php if ($address): ?>
                            <div class="hph-place-contact__item">
                                <i class="hph-icon hph-icon--location"></i>
                                <span><?php echo esc_html($address); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($phone): ?>
                            <div class="hph-place-contact__item">
                                <i class="hph-icon hph-icon--phone"></i>
                                <a href="tel:<?php echo esc_attr($phone); ?>"><?php echo esc_html($phone); ?></a>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($website): ?>
                            <div class="hph-place-contact__item">
                                <i class="hph-icon hph-icon--globe"></i>
                                <a href="<?php echo esc_url($website); ?>" target="_blank" rel="noopener">
                                    Website
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Hours -->
                    <?php if (!empty($hours)): ?>
                    <div class="hph-sidebar-widget">
                        <h3 class="hph-sidebar-widget__title">Hours</h3>
                        <div class="hph-place-hours">
                            <?php
                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            $today = date('l');
                            
                            foreach ($days as $day):
                                $day_hours = $hours[strtolower($day)] ?? null;
                                $is_today = $day === $today;
                                $is_open = $day_hours && $day_hours !== 'closed';
                            ?>
                            <div class="hph-place-hours__day <?php echo $is_today ? 'is-today' : ''; ?>">
                                <span class="hph-place-hours__day-name"><?php echo $day; ?></span>
                                <span class="hph-place-hours__time">
                                    <?php echo $is_open ? esc_html($day_hours) : 'Closed'; ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Map -->
                    <?php if ($lat && $lng && !empty($mapbox_token)): ?>
                    <div class="hph-sidebar-widget">
                        <h3 class="hph-sidebar-widget__title">Location</h3>
                        <div class="hph-place-map" 
                             id="place-map" 
                             data-lat="<?php echo esc_attr($lat); ?>" 
                             data-lng="<?php echo esc_attr($lng); ?>"
                             data-title="<?php echo esc_attr($title); ?>"
                             data-address="<?php echo esc_attr($address); ?>">
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Share -->
                    <div class="hph-sidebar-widget">
                        <h3 class="hph-sidebar-widget__title">Share</h3>
                        <div class="hph-share-buttons">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>" 
                               target="_blank" 
                               rel="noopener"
                               class="hph-share-btn hph-share-btn--facebook">
                                <i class="hph-icon hph-icon--facebook"></i>
                                Facebook
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode($title); ?>" 
                               target="_blank" 
                               rel="noopener"
                               class="hph-share-btn hph-share-btn--twitter">
                                <i class="hph-icon hph-icon--twitter"></i>
                                Twitter
                            </a>
                            <button class="hph-share-btn hph-share-btn--copy" data-copy-url="<?php echo get_permalink(); ?>">
                                <i class="hph-icon hph-icon--link"></i>
                                Copy Link
                            </button>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </section>
    
    <!-- Related Places -->
    <?php if (!empty($related_places)): ?>
    <section class="hph-related-places">
        <div class="hph-container">
            <h2 class="hph-section__title">Similar Places</h2>
            <div class="hph-grid hph-grid--4">
                <?php foreach ($related_places as $related_id): ?>
                    <div class="hph-grid__item">
                        <?php get_template_part('template-parts/components/local/place-card', null, [
                            'place_id' => $related_id,
                            'variant' => 'grid'
                        ]); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Attribution -->
    <?php if ($attribution || $source_url): ?>
    <section class="hph-attribution">
        <div class="hph-container">
            <div class="hph-attribution__content">
                <?php if ($attribution): ?>
                <p class="hph-attribution__text">
                    Information courtesy of <?php echo esc_html($attribution); ?>
                </p>
                <?php endif; ?>
                
                <?php if ($source_url): ?>
                <p class="hph-attribution__source">
                    <a href="<?php echo esc_url($source_url); ?>" target="_blank" rel="noopener">
                        View Original Source
                    </a>
                </p>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
</article>

<?php get_footer(); ?>
