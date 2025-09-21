<?php
/**
 * Open House Event Card Template Part
 *
 * Specialized event card for open house listings.
 * Extends the universal event card with real estate specific features.
 *
 * @package HappyPlaceTheme
 * @version 1.0.0
 * 
 * @param array $args {
 *     Required open house data and display options.
 *     @type array $event Open house event data (same structure as universal event card)
 *     @type array $args Calendar configuration arguments
 * }
 */

if (!isset($args['event']) || !is_array($args['event'])) {
    return;
}

$event = $args['event'];
$calendar_args = isset($args['args']) ? $args['args'] : array();

// Get listing data from event or fallback to meta queries
$listing_id = $event['id'];
$property_data = isset($event['property']) ? $event['property'] : array();

// Format open house specific data from event or meta
$open_house_date = $event['date'] ?? get_post_meta($listing_id, 'open_house_date', true);
$open_house_time = $event['time'] ?? get_post_meta($listing_id, 'open_house_time', true);
$open_house_end_time = $event['end_date'] ?? get_post_meta($listing_id, 'open_house_end_time', true);

// Format date and time
$event_date = !empty($open_house_date) ? new DateTime($open_house_date) : null;
$event_location = $event['location'] ?? '';

// Get property details from event data or meta fallback
$price = $property_data['price_formatted'] ?? get_post_meta($listing_id, 'price_formatted', true);
$bedrooms = $property_data['bedrooms'] ?? get_post_meta($listing_id, 'bedrooms', true);
$bathrooms = $property_data['bathrooms'] ?? get_post_meta($listing_id, 'bathrooms', true);
$square_feet = $property_data['square_feet'] ?? get_post_meta($listing_id, 'square_feet', true);
$property_type = $property_data['property_type'] ?? get_post_meta($listing_id, 'property_type', true);
$listing_status = $property_data['listing_status'] ?? get_post_meta($listing_id, 'listing_status', true);

// Build card classes
$card_classes = array('hph-event-card', 'hph-open-house-card', 'hph-loop-item');

// Add status classes
$is_past = $event_date && $event_date < new DateTime();
$is_today = $event_date && $event_date->format('Y-m-d') === date('Y-m-d');
$is_this_weekend = $event_date && in_array($event_date->format('N'), [6, 7]) && $event_date >= new DateTime();

if ($is_past) {
    $card_classes[] = 'open-house-past';
} elseif ($is_today) {
    $card_classes[] = 'open-house-today';
} elseif ($is_this_weekend) {
    $card_classes[] = 'open-house-weekend';
}

// Add property type classes
if (!empty($property_type)) {
    $card_classes[] = 'property-type-' . sanitize_html_class($property_type);
}

// Get images
$featured_image = $event['featured_image'] ?? '';
$gallery_count = 0; // TODO: Add gallery count support
?>

<article class="<?php echo esc_attr(implode(' ', $card_classes)); ?>" data-listing-id="<?php echo esc_attr($listing_id); ?>" data-open-house-date="<?php echo esc_attr($open_house_date); ?>">
    
    <!-- Open House Image Section -->
    <?php if ($featured_image) : ?>
    <div class="hph-open-house-card-image">
        <a href="<?php echo esc_url($event['permalink']); ?>" class="hph-image-link">
            <img src="<?php echo esc_url($featured_image); ?>" 
                 alt="<?php echo esc_attr($event['title']); ?>"
                 class="hph-open-house-card-img"
                 loading="lazy">
            
            <!-- Image Overlays -->
            <div class="hph-image-overlays">
                
                <!-- Status Badge -->
                <?php if ($is_today) : ?>
                <div class="hph-status-badge hph-status-today">
                    <i class="fas fa-clock" aria-hidden="true"></i>
                    <?php esc_html_e('Today!', 'happy-place-theme'); ?>
                </div>
                <?php elseif ($is_this_weekend) : ?>
                <div class="hph-status-badge hph-status-weekend">
                    <i class="fas fa-calendar-week" aria-hidden="true"></i>
                    <?php esc_html_e('This Weekend', 'happy-place-theme'); ?>
                </div>
                <?php elseif ($is_past) : ?>
                <div class="hph-status-badge hph-status-past">
                    <i class="fas fa-check" aria-hidden="true"></i>
                    <?php esc_html_e('Completed', 'happy-place-theme'); ?>
                </div>
                <?php endif; ?>
                
                <!-- Photo Count -->
                <?php if ($gallery_count > 1) : ?>
                <div class="hph-photo-count">
                    <i class="fas fa-camera" aria-hidden="true"></i>
                    <span><?php echo esc_html($gallery_count); ?></span>
                </div>
                <?php endif; ?>
                
                <!-- Property Type Badge -->
                <?php if ($property_type) : ?>
                <div class="hph-property-type-badge">
                    <?php echo esc_html($property_type); ?>
                </div>
                <?php endif; ?>
                
            </div>
        </a>
    </div>
    <?php endif; ?>
    
    <!-- Open House Date Badge (Always Visible) -->
    <?php if ($event_date) : ?>
    <div class="hph-open-house-card-date">
        <span class="hph-open-house-card-month"><?php echo esc_html($event_date->format('M')); ?></span>
        <span class="hph-open-house-card-day"><?php echo esc_html($event_date->format('j')); ?></span>
        <?php if ($event_date->format('Y') !== date('Y')) : ?>
        <span class="hph-open-house-card-year"><?php echo esc_html($event_date->format('Y')); ?></span>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <!-- Open House Content -->
    <div class="hph-open-house-card-content">
        
        <!-- Property Address -->
        <h3 class="hph-open-house-card-title">
            <a href="<?php echo esc_url($event['permalink']); ?>" class="hph-open-house-card-link">
                <?php echo esc_html($event_location ?: $event['title']); ?>
            </a>
        </h3>
        
        <!-- Price -->
        <?php if ($price) : ?>
        <div class="hph-open-house-card-price">
            <?php echo esc_html($price); ?>
        </div>
        <?php endif; ?>
        
        <!-- Property Details -->
        <?php if ($bedrooms || $bathrooms || $square_feet) : ?>
        <div class="hph-open-house-card-details">
            <?php if ($bedrooms) : ?>
            <span class="hph-detail-item">
                <i class="fas fa-bed" aria-hidden="true"></i>
                <?php echo esc_html($bedrooms); ?> <?php esc_html_e('bed', 'happy-place-theme'); ?><?php echo $bedrooms != 1 ? 's' : ''; ?>
            </span>
            <?php endif; ?>
            
            <?php if ($bathrooms) : ?>
            <span class="hph-detail-item">
                <i class="fas fa-bath" aria-hidden="true"></i>
                <?php echo esc_html($bathrooms); ?> <?php esc_html_e('bath', 'happy-place-theme'); ?><?php echo $bathrooms != 1 ? 's' : ''; ?>
            </span>
            <?php endif; ?>
            
            <?php if ($square_feet) : ?>
            <span class="hph-detail-item">
                <i class="fas fa-ruler-combined" aria-hidden="true"></i>
                <?php echo esc_html(number_format($square_feet)); ?> <?php esc_html_e('sqft', 'happy-place-theme'); ?>
            </span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Open House Time Information -->
        <div class="hph-open-house-card-schedule">
            
            <?php if ($open_house_time || $open_house_end_time) : ?>
            <div class="hph-schedule-time">
                <i class="fas fa-clock" aria-hidden="true"></i>
                <span>
                    <?php 
                    if ($open_house_time) {
                        echo esc_html(date('g:i A', strtotime($open_house_time)));
                    }
                    if ($open_house_time && $open_house_end_time) {
                        echo ' - ';
                    }
                    if ($open_house_end_time) {
                        echo esc_html(date('g:i A', strtotime($open_house_end_time)));
                    }
                    ?>
                </span>
            </div>
            <?php endif; ?>
            
            <?php if ($event_date) : ?>
            <div class="hph-schedule-date">
                <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                <span><?php echo esc_html($event_date->format('l, M j')); ?></span>
            </div>
            <?php endif; ?>
            
        </div>
        
        <!-- Open House Actions -->
        <div class="hph-open-house-card-actions">
            
            <!-- Primary Action -->
            <?php if (!$is_past) : ?>
            <button class="hph-btn hph-btn-primary hph-btn-sm hph-rsvp-btn" 
                    data-listing-id="<?php echo esc_attr($listing_id); ?>"
                    data-open-house-date="<?php echo esc_attr($open_house_date); ?>">
                <i class="fas fa-calendar-plus" aria-hidden="true"></i>
                <?php esc_html_e('RSVP', 'happy-place-theme'); ?>
            </button>
            <?php endif; ?>
            
            <!-- View Property Details -->
            <a href="<?php echo esc_url($event['permalink']); ?>" 
               class="hph-btn hph-btn-outline hph-btn-sm">
                <?php esc_html_e('Property Details', 'happy-place-theme'); ?>
                <i class="fas fa-arrow-right" aria-hidden="true"></i>
            </a>
            
            <!-- Secondary Actions -->
            <div class="hph-secondary-actions">
                
                <!-- Add to Calendar -->
                <?php if ($event_date && !$is_past) : ?>
                <a href="<?php echo esc_url(hph_generate_open_house_calendar_url($listing_id, $open_house_date, $open_house_time, $open_house_end_time)); ?>" 
                   class="hph-btn hph-btn-ghost hph-btn-sm" 
                   target="_blank" 
                   rel="noopener"
                   title="<?php esc_attr_e('Add to Google Calendar', 'happy-place-theme'); ?>">
                    <i class="fas fa-calendar-plus" aria-hidden="true"></i>
                    <span class="hph-btn-text"><?php esc_html_e('Add to Calendar', 'happy-place-theme'); ?></span>
                </a>
                <?php endif; ?>
                
                <!-- Share -->
                <button class="hph-btn hph-btn-ghost hph-btn-sm hph-share-btn" 
                        data-url="<?php echo esc_url($event['permalink']); ?>"
                        data-title="<?php echo esc_attr($event['title']); ?>"
                        title="<?php esc_attr_e('Share Open House', 'happy-place-theme'); ?>">
                    <i class="fas fa-share-alt" aria-hidden="true"></i>
                    <span class="hph-btn-text"><?php esc_html_e('Share', 'happy-place-theme'); ?></span>
                </button>
                
                <!-- Save/Favorite -->
                <button class="hph-btn hph-btn-ghost hph-btn-sm hph-favorite-btn" 
                        data-listing-id="<?php echo esc_attr($listing_id); ?>"
                        title="<?php esc_attr_e('Save Property', 'happy-place-theme'); ?>">
                    <i class="far fa-heart" aria-hidden="true"></i>
                    <span class="hph-btn-text"><?php esc_html_e('Save', 'happy-place-theme'); ?></span>
                </button>
                
            </div>
            
        </div>
        
    </div>
    
</article>

<?php
/**
 * Generate Google Calendar URL for open house
 */
function hph_generate_open_house_calendar_url($listing_id, $date, $start_time = '', $end_time = '') {
    if (empty($date)) {
        return '#';
    }
    
    // Get listing data for event details
    $listing_data = function_exists('hpt_get_listing') ? hpt_get_listing($listing_id) : null;
    $title = $listing_data ? $listing_data['title'] : get_the_title($listing_id);
    $address = $listing_data ? ($listing_data['address']['formatted'] ?? '') : '';
    $description = sprintf(
        __('Open House for %s. Visit this property and learn more about the neighborhood.', 'happy-place-theme'),
        $title
    );
    
    // Format date and time
    $start_datetime = new DateTime($date);
    if ($start_time) {
        $time_parts = explode(':', $start_time);
        if (count($time_parts) >= 2) {
            $start_datetime->setTime((int)$time_parts[0], (int)$time_parts[1]);
        }
    } else {
        $start_datetime->setTime(10, 0); // Default to 10 AM
    }
    
    $end_datetime = clone $start_datetime;
    if ($end_time) {
        $end_time_parts = explode(':', $end_time);
        if (count($end_time_parts) >= 2) {
            $end_datetime->setTime((int)$end_time_parts[0], (int)$end_time_parts[1]);
        }
    } else {
        $end_datetime->modify('+2 hours'); // Default 2 hour duration
    }
    
    // Format for Google Calendar
    $start_formatted = $start_datetime->format('Ymd\THis\Z');
    $end_formatted = $end_datetime->format('Ymd\THis\Z');
    
    $params = array(
        'action' => 'TEMPLATE',
        'text' => sprintf(__('Open House: %s', 'happy-place-theme'), $title),
        'dates' => $start_formatted . '/' . $end_formatted,
        'details' => $description,
        'location' => $address,
        'sf' => 'true',
        'output' => 'xml'
    );
    
    return 'https://calendar.google.com/calendar/render?' . http_build_query($params);
}
?>