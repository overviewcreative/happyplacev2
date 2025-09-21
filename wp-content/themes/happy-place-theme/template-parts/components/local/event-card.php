<?php
/**
 * Event Card Component
 * 
 * @package HappyPlaceTheme
 * @since 1.0.0
 * 
 * @param array $args {
 *     @type int    $event_id  Post ID of the event (defaults to current post)
 *     @type string $variant   Card variant: 'default', 'timeline', 'compact', 'featured'
 *     @type bool   $show_description Whether to show event description
 * }
 */

$event_id = $args['event_id'] ?? get_the_ID();
$variant = $args['variant'] ?? 'default';
$show_description = $args['show_description'] ?? ($variant === 'featured');

// Get event data
$title = get_the_title($event_id);
$permalink = get_permalink($event_id);
$excerpt = get_the_excerpt($event_id);
$thumbnail_id = get_post_thumbnail_id($event_id);

// ACF Fields
$start_datetime = get_field('start_datetime', $event_id);
$end_datetime = get_field('end_datetime', $event_id);
$is_free = get_field('is_free', $event_id);
$price = get_field('price', $event_id);
$primary_city = get_field('primary_city', $event_id);
$venue_name = get_field('venue_name', $event_id);
$venue_address = get_field('venue_address', $event_id);
$organizer_name = get_field('organizer_name', $event_id);
$tickets_url = get_field('tickets_url', $event_id);
$age_min = get_field('age_min', $event_id);

// Parse dates
$start_date = $start_datetime ? strtotime($start_datetime) : null;
$end_date = $end_datetime ? strtotime($end_datetime) : null;

// Format date components
if ($start_date) {
    $month = date('M', $start_date);
    $day = date('j', $start_date);
    $year = date('Y', $start_date);
    $time = date('g:i A', $start_date);
    $full_date = date('F j, Y', $start_date);
    $day_of_week = date('l', $start_date);
}

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
$is_tomorrow = $start_date && date('Y-m-d', $start_date) === date('Y-m-d', strtotime('+1 day'));

// Build modifier classes
$card_classes = [
    'hph-event-card',
    'hph-event-card--' . $variant
];

if ($is_past) {
    $card_classes[] = 'is-past';
}

if ($is_today) {
    $card_classes[] = 'is-today';
}

if ($is_free) {
    $card_classes[] = 'is-free';
}

// Image handling
$image_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'medium') : '';
?>

<article class="<?php echo esc_attr(implode(' ', $card_classes)); ?>" data-event-id="<?php echo esc_attr($event_id); ?>" data-start="<?php echo esc_attr($start_datetime); ?>">
    
    <?php if ($variant === 'timeline'): ?>
    <!-- Timeline Variant -->
    <div class="hph-event-card__timeline-marker"></div>
    <?php endif; ?>
    
    <!-- Date Block -->
    <div class="hph-event-card__date-block">
        <?php if ($start_date): ?>
        <span class="hph-event-card__month"><?php echo esc_html($month); ?></span>
        <span class="hph-event-card__day"><?php echo esc_html($day); ?></span>
        
        <?php if ($variant !== 'compact'): ?>
        <span class="hph-event-card__time"><?php echo esc_html($time); ?></span>
        <?php endif; ?>
        
        <?php if ($is_today): ?>
        <span class="hph-badge hph-badge--today">Today</span>
        <?php elseif ($is_tomorrow): ?>
        <span class="hph-badge hph-badge--tomorrow">Tomorrow</span>
        <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <!-- Event Content -->
    <div class="hph-event-card__content">
        <div class="hph-event-card__header">
            <h3 class="hph-event-card__title">
                <a href="<?php echo esc_url($permalink); ?>" class="hph-event-card__title-link">
                    <?php echo esc_html($title); ?>
                </a>
            </h3>
            
            <div class="hph-event-card__badges">
                <?php if ($is_free): ?>
                <span class="hph-badge hph-badge--free">FREE</span>
                <?php elseif ($price): ?>
                <span class="hph-badge hph-badge--price"><?php echo esc_html($price); ?></span>
                <?php endif; ?>
                
                <?php if ($age_min): ?>
                <span class="hph-badge hph-badge--age"><?php echo esc_html($age_min); ?>+</span>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Event Details -->
        <div class="hph-event-card__details">
            <?php if ($venue_name): ?>
            <div class="hph-event-card__venue">
                <i class="hph-icon hph-icon--venue"></i>
                <span><?php echo esc_html($venue_name); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($variant !== 'compact' && $venue_address): ?>
            <div class="hph-event-card__address">
                <i class="hph-icon hph-icon--location"></i>
                <span><?php echo esc_html($venue_address); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($duration && $variant !== 'compact'): ?>
            <div class="hph-event-card__duration">
                <i class="hph-icon hph-icon--clock"></i>
                <span><?php echo esc_html($duration); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($organizer_name && $variant === 'featured'): ?>
            <div class="hph-event-card__organizer">
                <i class="hph-icon hph-icon--user"></i>
                <span>By <?php echo esc_html($organizer_name); ?></span>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($show_description && $excerpt): ?>
        <p class="hph-event-card__description">
            <?php echo wp_trim_words($excerpt, 20); ?>
        </p>
        <?php endif; ?>
        
        <!-- Card Actions -->
        <div class="hph-event-card__actions">
            <?php if (!$is_past && $tickets_url): ?>
            <a href="<?php echo esc_url($tickets_url); ?>" target="_blank" rel="noopener" class="hph-btn hph-btn--primary hph-btn--small">
                Get Tickets
                <i class="hph-icon hph-icon--ticket"></i>
            </a>
            <?php endif; ?>
            
            <a href="<?php echo esc_url($permalink); ?>" class="hph-btn hph-btn--ghost hph-btn--small">
                View Details
                <i class="hph-icon hph-icon--arrow-right"></i>
            </a>
            
            <?php if (!$is_past): ?>
            <button class="hph-btn hph-btn--icon hph-btn--small" data-event-id="<?php echo esc_attr($event_id); ?>" aria-label="Add to calendar">
                <i class="hph-icon hph-icon--calendar-add"></i>
            </button>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($variant === 'featured' && $image_url): ?>
    <!-- Featured Image -->
    <div class="hph-event-card__media">
        <a href="<?php echo esc_url($permalink); ?>" class="hph-event-card__image-link">
            <img 
                src="<?php echo esc_url($image_url); ?>" 
                alt="<?php echo esc_attr($title); ?>"
                class="hph-event-card__image"
                loading="lazy"
            >
        </a>
    </div>
    <?php endif; ?>
</article>
