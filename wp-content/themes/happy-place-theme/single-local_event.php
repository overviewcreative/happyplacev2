<?php
/**
 * Single Event Template
 * 
 * @package HappyPlaceTheme
 * @since 1.0.0
 */

// Enqueue required assets
wp_enqueue_style('hph-local-single', get_template_directory_uri() . '/assets/css/framework/features/local/local-single.css', ['hph-framework'], '1.0.0');
wp_enqueue_script('hph-single-event', get_template_directory_uri() . '/assets/js/pages/single-event.js', ['hph-framework'], '1.0.0', true);

// Get event data
$event_id = get_the_ID();
$title = get_the_title();
$content = get_the_content();
$excerpt = get_the_excerpt();

// ACF Fields
$start_datetime = get_field('start_datetime');
$end_datetime = get_field('end_datetime');
$is_free = get_field('is_free');
$price = get_field('price');
$age_min = get_field('age_min');
$tickets_url = get_field('tickets_url');
$organizer_name = get_field('organizer_name');
$venue_name = get_field('venue_name');
$venue_address = get_field('venue_address');
$lat = get_field('lat');
$lng = get_field('lng');
$primary_city = get_field('primary_city');
$source_url = get_field('source_url');
$attribution = get_field('attribution');

// Parse dates
$start_date = $start_datetime ? strtotime($start_datetime) : null;
$end_date = $end_datetime ? strtotime($end_datetime) : null;

// Calculate duration
$duration = '';
if ($start_date && $end_date) {
    $diff = $end_date - $start_date;
    $hours = floor($diff / 3600);
    $minutes = floor(($diff % 3600) / 60);
    
    if ($hours > 24) {
        $days = floor($hours / 24);
        $duration = $days . ' day' . ($days > 1 ? 's' : '');
    } elseif ($hours > 0) {
        $duration = $hours . ' hour' . ($hours > 1 ? 's' : '');
        if ($minutes > 0) {
            $duration .= ' ' . $minutes . ' min';
        }
    } elseif ($minutes > 0) {
        $duration = $minutes . ' minutes';
    }
}

// Event status
$is_past = $start_date && $start_date < time();
$is_today = $start_date && date('Y-m-d', $start_date) === date('Y-m-d');
$is_soon = $start_date && $start_date > time() && $start_date < strtotime('+24 hours');

// Related events (same venue or organizer)
$related_events = new WP_Query([
    'post_type' => 'local_event',
    'posts_per_page' => 4,
    'post__not_in' => [$event_id],
    'meta_query' => [
        'relation' => 'OR',
        [
            'key' => 'venue_name',
            'value' => $venue_name,
            'compare' => '='
        ],
        [
            'key' => 'organizer_name',
            'value' => $organizer_name,
            'compare' => '='
        ]
    ],
    'meta_key' => 'start_datetime',
    'orderby' => 'meta_value',
    'order' => 'ASC'
]);

// Gallery images
$gallery = get_field('gallery');
$featured_image = get_post_thumbnail_id();

get_header();
?>

<article class="hph-single hph-single--event <?php echo $is_past ? 'is-past' : ''; ?>">
    
    <!-- Event Hero -->
    <section class="hph-event-hero">
        <?php if ($featured_image): ?>
        <div class="hph-event-hero__media">
            <div class="hph-event-hero__image-wrapper">
                <?php echo wp_get_attachment_image($featured_image, 'full', false, [
                    'class' => 'hph-event-hero__image',
                    'loading' => 'eager'
                ]); ?>
            </div>
            <div class="hph-event-hero__overlay"></div>
        </div>
        <?php endif; ?>
        
        <!-- Date Badge -->
        <?php if ($start_date): ?>
        <div class="hph-event-hero__date-badge">
            <span class="hph-event-hero__month"><?php echo date('M', $start_date); ?></span>
            <span class="hph-event-hero__day"><?php echo date('j', $start_date); ?></span>
            <?php if ($is_today): ?>
                <span class="hph-badge hph-badge--today">Today</span>
            <?php elseif ($is_soon): ?>
                <span class="hph-badge hph-badge--soon">Soon</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <div class="hph-container">
            <div class="hph-event-hero__content">
                <div class="hph-event-hero__meta">
                    <?php if ($primary_city): ?>
                    <a href="<?php echo get_permalink($primary_city); ?>" class="hph-event-hero__city">
                        <i class="hph-icon hph-icon--location"></i>
                        <?php echo $primary_city->post_title; ?>
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($organizer_name): ?>
                    <span class="hph-event-hero__organizer">
                        By <?php echo esc_html($organizer_name); ?>
                    </span>
                    <?php endif; ?>
                </div>
                
                <h1 class="hph-event-hero__title"><?php echo esc_html($title); ?></h1>
                
                <?php if ($excerpt): ?>
                <p class="hph-event-hero__excerpt"><?php echo esc_html($excerpt); ?></p>
                <?php endif; ?>
                
                <!-- Event Details Bar -->
                <div class="hph-event-hero__details">
                    <?php if ($start_date): ?>
                    <div class="hph-event-detail">
                        <i class="hph-icon hph-icon--calendar"></i>
                        <div>
                            <div class="hph-event-detail__label">Date & Time</div>
                            <div class="hph-event-detail__value">
                                <?php echo date('l, F j, Y', $start_date); ?><br>
                                <?php echo date('g:i A', $start_date); ?>
                                <?php if ($end_date && date('Y-m-d', $start_date) === date('Y-m-d', $end_date)): ?>
                                    - <?php echo date('g:i A', $end_date); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($venue_name): ?>
                    <div class="hph-event-detail">
                        <i class="hph-icon hph-icon--venue"></i>
                        <div>
                            <div class="hph-event-detail__label">Venue</div>
                            <div class="hph-event-detail__value">
                                <?php echo esc_html($venue_name); ?>
                                <?php if ($venue_address): ?>
                                    <br><small><?php echo esc_html($venue_address); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="hph-event-detail">
                        <i class="hph-icon hph-icon--tag"></i>
                        <div>
                            <div class="hph-event-detail__label">Price</div>
                            <div class="hph-event-detail__value">
                                <?php if ($is_free): ?>
                                    <span class="hph-badge hph-badge--free">FREE</span>
                                <?php elseif ($price): ?>
                                    <?php echo esc_html($price); ?>
                                <?php else: ?>
                                    Contact for pricing
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($duration): ?>
                    <div class="hph-event-detail">
                        <i class="hph-icon hph-icon--clock"></i>
                        <div>
                            <div class="hph-event-detail__label">Duration</div>
                            <div class="hph-event-detail__value"><?php echo esc_html($duration); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Action Buttons -->
                <div class="hph-event-hero__actions">
                    <?php if (!$is_past && $tickets_url): ?>
                    <a href="<?php echo esc_url($tickets_url); ?>" 
                       target="_blank" 
                       rel="noopener" 
                       class="hph-btn hph-btn--primary hph-btn--large">
                        <i class="hph-icon hph-icon--ticket"></i>
                        Get Tickets
                    </a>
                    <?php endif; ?>
                    
                    <?php if (!$is_past): ?>
                    <button class="hph-btn hph-btn--outline hph-btn--large" 
                            data-add-to-calendar
                            data-title="<?php echo esc_attr($title); ?>"
                            data-start="<?php echo esc_attr($start_datetime); ?>"
                            data-end="<?php echo esc_attr($end_datetime); ?>"
                            data-location="<?php echo esc_attr($venue_address ?: $venue_name); ?>">
                        <i class="hph-icon hph-icon--calendar-add"></i>
                        Add to Calendar
                    </button>
                    <?php endif; ?>
                    
                    <button class="hph-btn hph-btn--ghost hph-btn--large hph-event-favorite" 
                            data-event-id="<?php echo $event_id; ?>">
                        <i class="hph-icon hph-icon--heart-outline"></i>
                        Save Event
                    </button>
                </div>
                
                <!-- Age Restriction -->
                <?php if ($age_min): ?>
                <div class="hph-event-hero__notice">
                    <i class="hph-icon hph-icon--info"></i>
                    This event is for ages <?php echo esc_html($age_min); ?>+
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    
    <!-- Main Content -->
    <section class="hph-single__content">
        <div class="hph-container">
            <div class="hph-layout hph-layout--sidebar-right">
                
                <!-- Main Content -->
                <div class="hph-layout__main">
                    
                    <!-- Event Description -->
                    <?php if ($content): ?>
                    <div class="hph-content-section">
                        <h2 class="hph-content-section__title">Event Details</h2>
                        <div class="hph-content-section__content">
                            <?php echo apply_filters('the_content', $content); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Gallery -->
                    <?php if ($gallery && is_array($gallery)): ?>
                    <div class="hph-content-section">
                        <h2 class="hph-content-section__title">Photos</h2>
                        <div class="hph-event-gallery">
                            <?php foreach ($gallery as $image_id): ?>
                                <?php if ($image_id): ?>
                                <div class="hph-event-gallery__item">
                                    <a href="<?php echo wp_get_attachment_image_url($image_id, 'full'); ?>" 
                                       class="hph-event-gallery__link"
                                       data-fancybox="event-gallery">
                                        <?php echo wp_get_attachment_image($image_id, 'medium_large', false, [
                                            'class' => 'hph-event-gallery__image',
                                            'loading' => 'lazy'
                                        ]); ?>
                                    </a>
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                </div>
                
                <!-- Sidebar -->
                <div class="hph-layout__sidebar">
                    
                    <!-- Quick Info -->
                    <div class="hph-sidebar-widget hph-event-quick-info">
                        <h3 class="hph-sidebar-widget__title">Quick Info</h3>
                        
                        <div class="hph-event-info">
                            <?php if ($start_date): ?>
                            <div class="hph-event-info__item">
                                <strong>When:</strong>
                                <?php echo date('M j, Y \a\t g:i A', $start_date); ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($venue_name): ?>
                            <div class="hph-event-info__item">
                                <strong>Where:</strong>
                                <?php echo esc_html($venue_name); ?>
                                <?php if ($venue_address): ?>
                                    <br><?php echo esc_html($venue_address); ?>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="hph-event-info__item">
                                <strong>Price:</strong>
                                <?php if ($is_free): ?>
                                    FREE
                                <?php elseif ($price): ?>
                                    <?php echo esc_html($price); ?>
                                <?php else: ?>
                                    Contact organizer
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($organizer_name): ?>
                            <div class="hph-event-info__item">
                                <strong>Organizer:</strong>
                                <?php echo esc_html($organizer_name); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!$is_past): ?>
                        <div class="hph-event-actions">
                            <?php if ($tickets_url): ?>
                            <a href="<?php echo esc_url($tickets_url); ?>" 
                               target="_blank" 
                               rel="noopener"
                               class="hph-btn hph-btn--primary hph-btn--block">
                                Get Tickets
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Map -->
                    <?php if ($lat && $lng): ?>
                    <div class="hph-sidebar-widget">
                        <h3 class="hph-sidebar-widget__title">Location</h3>
                        <div class="hph-event-map" 
                             id="event-map" 
                             data-lat="<?php echo esc_attr($lat); ?>" 
                             data-lng="<?php echo esc_attr($lng); ?>"
                             data-title="<?php echo esc_attr($venue_name); ?>"
                             data-address="<?php echo esc_attr($venue_address); ?>">
                        </div>
                        
                        <?php if ($venue_address): ?>
                        <div class="hph-event-directions">
                            <a href="https://maps.google.com/?q=<?php echo urlencode($venue_address); ?>" 
                               target="_blank" 
                               rel="noopener"
                               class="hph-btn hph-btn--ghost hph-btn--small hph-btn--block">
                                <i class="hph-icon hph-icon--directions"></i>
                                Get Directions
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Share -->
                    <div class="hph-sidebar-widget">
                        <h3 class="hph-sidebar-widget__title">Share Event</h3>
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
    
    <!-- Related Events -->
    <?php if ($related_events->have_posts()): ?>
    <section class="hph-related-events">
        <div class="hph-container">
            <h2 class="hph-section__title">
                <?php if ($venue_name): ?>
                    More Events at <?php echo esc_html($venue_name); ?>
                <?php elseif ($organizer_name): ?>
                    More Events by <?php echo esc_html($organizer_name); ?>
                <?php else: ?>
                    Related Events
                <?php endif; ?>
            </h2>
            <div class="hph-related-events__list">
                <?php while ($related_events->have_posts()): $related_events->the_post(); ?>
                    <?php get_template_part('template-parts/components/local/event-card', null, [
                        'event_id' => get_the_ID(),
                        'variant' => 'default'
                    ]); ?>
                <?php endwhile; wp_reset_postdata(); ?>
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
                    Event information courtesy of <?php echo esc_html($attribution); ?>
                </p>
                <?php endif; ?>
                
                <?php if ($source_url): ?>
                <p class="hph-attribution__source">
                    <a href="<?php echo esc_url($source_url); ?>" target="_blank" rel="noopener">
                        View Original Event Page
                    </a>
                </p>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
</article>

<?php get_footer(); ?>
