<?php
/**
 * Event Card Template Part
 *
 * Displays an individual event in card format.
 * Designed to work with the universal calendar and loop systems.
 *
 * @package HappyPlaceTheme
 * @version 1.0.0
 * 
 * @param array $args {
 *     Required event data and display options.
 *     @type array $event {
 *         Event data array containing:
 *         @type int    $id             Event post ID
 *         @type string $title          Event title
 *         @type string $permalink      Event permalink
 *         @type string $excerpt        Event excerpt
 *         @type string $date           Event date
 *         @type string $end_date       Event end date
 *         @type string $time           Event time
 *         @type string $location       Event location
 *         @type string $featured_image Event featured image URL
 *         @type array  $categories     Event categories
 *     }
 *     @type array $args Calendar configuration arguments
 * }
 */

if (!isset($args['event']) || !is_array($args['event'])) {
    return;
}

$event = $args['event'];
$calendar_args = isset($args['args']) ? $args['args'] : array();

// Format date and time
$event_date = !empty($event['date']) ? new DateTime($event['date']) : null;
$event_time = !empty($event['time']) ? $event['time'] : '';
$event_location = !empty($event['location']) ? $event['location'] : '';

// Build card classes
$card_classes = array('hph-event-card', 'hph-loop-item');

// Add category classes
if (!empty($event['categories'])) {
    foreach ($event['categories'] as $category) {
        $card_classes[] = 'event-category-' . sanitize_html_class($category->slug);
    }
}

// Determine if it's a past event
$is_past = $event_date && $event_date < new DateTime();
if ($is_past) {
    $card_classes[] = 'event-past';
}
?>

<article class="<?php echo esc_attr(implode(' ', $card_classes)); ?>" data-event-id="<?php echo esc_attr($event['id']); ?>">
    
    <?php if ($event_date) : ?>
    <!-- Event Date Badge -->
    <div class="hph-event-card-date">
        <span class="hph-event-card-month"><?php echo esc_html($event_date->format('M')); ?></span>
        <span class="hph-event-card-day"><?php echo esc_html($event_date->format('j')); ?></span>
    </div>
    <?php endif; ?>
    
    <!-- Event Content -->
    <div class="hph-event-card-content">
        
        <!-- Event Title -->
        <h3 class="hph-event-card-title">
            <a href="<?php echo esc_url($event['permalink']); ?>" class="hph-event-card-link">
                <?php echo esc_html($event['title']); ?>
            </a>
        </h3>
        
        <!-- Event Meta Information -->
        <?php if ($event_time || $event_location || !empty($event['categories'])) : ?>
        <div class="hph-event-card-meta">
            
            <?php if ($event_time) : ?>
            <div class="hph-event-card-time">
                <i class="fas fa-clock" aria-hidden="true"></i>
                <span><?php echo esc_html(date('g:i A', strtotime($event_time))); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($event_location) : ?>
            <div class="hph-event-card-location">
                <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                <span><?php echo esc_html($event_location); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($event['categories'])) : ?>
            <div class="hph-event-card-categories">
                <?php foreach ($event['categories'] as $category) : ?>
                    <span class="hph-badge hph-badge-secondary hph-event-category">
                        <?php echo esc_html($category->name); ?>
                    </span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
        </div>
        <?php endif; ?>
        
        <!-- Event Excerpt -->
        <?php if (!empty($event['excerpt'])) : ?>
        <div class="hph-event-card-excerpt">
            <?php echo wp_kses_post($event['excerpt']); ?>
        </div>
        <?php endif; ?>
        
        <!-- Event Actions -->
        <div class="hph-event-card-actions">
            <a href="<?php echo esc_url($event['permalink']); ?>" class="hph-btn hph-btn-primary hph-btn-sm">
                <?php esc_html_e('View Details', 'happy-place-theme'); ?>
                <i class="fas fa-arrow-right" aria-hidden="true"></i>
            </a>
            
            <?php 
            // Add to calendar button
            if ($event_date) :
                $calendar_url = hph_generate_calendar_url($event);
            ?>
            <a href="<?php echo esc_url($calendar_url); ?>" 
               class="hph-btn hph-btn-outline hph-btn-sm" 
               target="_blank" 
               rel="noopener">
                <i class="fas fa-calendar-plus" aria-hidden="true"></i>
                <?php esc_html_e('Add to Calendar', 'happy-place-theme'); ?>
            </a>
            <?php endif; ?>
        </div>
        
    </div>
    
    <?php if (!empty($event['featured_image'])) : ?>
    <!-- Event Featured Image (Optional) -->
    <div class="hph-event-card-image">
        <a href="<?php echo esc_url($event['permalink']); ?>">
            <img src="<?php echo esc_url($event['featured_image']); ?>" 
                 alt="<?php echo esc_attr($event['title']); ?>"
                 class="hph-event-card-img"
                 loading="lazy">
        </a>
    </div>
    <?php endif; ?>
    
</article>

<?php
/**
 * Generate calendar URL for adding event to calendar
 */
function hph_generate_calendar_url($event) {
    if (empty($event['date'])) {
        return '#';
    }
    
    $start_date = new DateTime($event['date']);
    $end_date = !empty($event['end_date']) ? new DateTime($event['end_date']) : clone $start_date;
    
    // If no end date, assume 1 hour duration
    if (empty($event['end_date'])) {
        $end_date->modify('+1 hour');
    }
    
    // Format for Google Calendar
    $start_formatted = $start_date->format('Ymd\THis\Z');
    $end_formatted = $end_date->format('Ymd\THis\Z');
    
    $params = array(
        'action' => 'TEMPLATE',
        'text' => $event['title'],
        'dates' => $start_formatted . '/' . $end_formatted,
        'details' => !empty($event['excerpt']) ? strip_tags($event['excerpt']) : '',
        'location' => !empty($event['location']) ? $event['location'] : '',
        'sf' => 'true',
        'output' => 'xml'
    );
    
    return 'https://calendar.google.com/calendar/render?' . http_build_query($params);
}
?>