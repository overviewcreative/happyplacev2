<?php
/**
 * Open House Card Component
 *
 * @package HappyPlaceTheme
 */

// Get Open House Service
$open_house_service = null;
if (class_exists('HappyPlace\\Services\\OpenHouseService')) {
    $open_house_service = new \HappyPlace\Services\OpenHouseService();
    $open_house_service->init();
}

// Get open house data
$listing_id = get_post_meta(get_the_ID(), 'listing_id', true);
$start_date = get_post_meta(get_the_ID(), 'start_date', true);
$start_time = get_post_meta(get_the_ID(), 'start_time', true);
$end_time = get_post_meta(get_the_ID(), 'end_time', true);
$agent_id = get_post_meta(get_the_ID(), 'agent_id', true);
$event_status = get_post_meta(get_the_ID(), 'event_status', true) ?: 'scheduled';

// Get RSVP count if service is available
$rsvp_count = 0;
if ($open_house_service) {
    $rsvp_count = $open_house_service->get_rsvp_count(get_the_ID());
}

// Get listing data if connected
$listing_data = null;
$listing_address = '';
$listing_price = '';
$listing_photo = '';

if ($listing_id) {
    $listing_data = hpt_get_listing($listing_id);
    if ($listing_data) {
        $listing_address = $listing_data['address'] ?? '';
        $listing_price = $listing_data['price'] ?? '';
        $listing_photo = $listing_data['featured_image'] ?? '';
    }
}

// Get agent info
$agent_name = '';
$agent_phone = '';
if ($agent_id) {
    $agent_data = get_userdata($agent_id);
    if ($agent_data) {
        $agent_name = $agent_data->display_name;
        $agent_phone = get_user_meta($agent_id, 'phone', true);
    }
}

// Format date/time
$formatted_date = $start_date ? date('M j, Y', strtotime($start_date)) : '';
$formatted_start_time = $start_time ? date('g:i A', strtotime($start_time)) : '';
$formatted_end_time = $end_time ? date('g:i A', strtotime($end_time)) : '';
$time_range = $formatted_start_time . ($formatted_end_time ? ' - ' . $formatted_end_time : '');

// Status styling
$status_class = 'hph-status-' . $event_status;
$status_label = ucfirst(str_replace('_', ' ', $event_status));

// Check if event is today or soon
$is_today = $start_date && date('Y-m-d') === $start_date;
$is_upcoming = $start_date && strtotime($start_date) > time() && strtotime($start_date) <= strtotime('+7 days');
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('hph-card hph-open-house-card hph-hover-lift'); ?>>
    
    <?php if ($listing_photo) : ?>
        <div class="hph-card-image">
            <img src="<?php echo esc_url($listing_photo); ?>" 
                 alt="<?php echo esc_attr(get_the_title()); ?>" 
                 class="hph-card-img">
            
            <div class="hph-card-badges">
                <?php if ($is_today) : ?>
                    <span class="hph-badge hph-badge-danger hph-badge-pulse">Today</span>
                <?php elseif ($is_upcoming) : ?>
                    <span class="hph-badge hph-badge-warning">Upcoming</span>
                <?php endif; ?>
                
                <span class="hph-badge hph-badge-secondary <?php echo esc_attr($status_class); ?>">
                    <?php echo esc_html($status_label); ?>
                </span>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="hph-card-header">
        <div class="hph-open-house-date hph-mb-2">
            <div class="hph-date-time hph-flex hph-items-center hph-gap-2">
                <i class="fas fa-calendar hph-text-primary"></i>
                <div>
                    <div class="hph-date hph-font-medium"><?php echo esc_html($formatted_date); ?></div>
                    <?php if ($time_range) : ?>
                        <div class="hph-time hph-text-sm hph-text-muted"><?php echo esc_html($time_range); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <h3 class="hph-card-title">
            <a href="<?php the_permalink(); ?>" class="hph-link-primary">
                <?php echo $listing_data ? esc_html(get_the_title($listing_id)) : get_the_title(); ?>
            </a>
        </h3>
        
        <?php if ($listing_address) : ?>
            <div class="hph-property-address hph-flex hph-items-center hph-gap-1 hph-text-sm hph-text-muted hph-mb-2">
                <i class="fas fa-map-marker-alt"></i>
                <span><?php echo esc_html($listing_address); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($listing_price) : ?>
            <div class="hph-property-price hph-text-lg hph-font-bold hph-text-primary hph-mb-3">
                <?php echo esc_html($listing_price); ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="hph-card-body">
        <?php if (get_the_content()) : ?>
            <div class="hph-open-house-description hph-text-sm hph-text-muted hph-mb-4 hph-line-clamp-3">
                <?php echo wp_trim_words(get_the_content(), 20, '...'); ?>
            </div>
        <?php endif; ?>
        
        <div class="hph-open-house-stats hph-grid hph-grid-cols-2 hph-gap-4 hph-mb-4">
            <div class="hph-stat hph-text-center hph-p-3 hph-bg-light hph-rounded">
                <div class="hph-stat-number hph-text-primary hph-font-bold"><?php echo esc_html($rsvp_count); ?></div>
                <div class="hph-stat-label hph-text-xs hph-text-muted">RSVPs</div>
            </div>
            
            <?php if ($agent_name) : ?>
                <div class="hph-agent-info hph-p-3 hph-bg-light hph-rounded">
                    <div class="hph-agent-name hph-text-sm hph-font-medium"><?php echo esc_html($agent_name); ?></div>
                    <div class="hph-agent-label hph-text-xs hph-text-muted">Host Agent</div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="hph-card-actions">
            <?php if ($event_status === 'scheduled' || $event_status === 'active') : ?>
                <div class="hph-rsvp-form hph-mb-3">
                    <?php echo do_shortcode('[hp_open_house_rsvp open_house_id="' . get_the_ID() . '" title="" button_text="RSVP Now"]'); ?>
                </div>
            <?php endif; ?>
            
            <div class="hph-action-buttons hph-grid hph-grid-cols-2 hph-gap-2">
                <a href="<?php the_permalink(); ?>" class="hph-btn hph-btn-primary hph-btn-sm">
                    <i class="fas fa-info-circle hph-mr-1"></i>
                    Details
                </a>
                
                <?php if ($listing_id) : ?>
                    <a href="<?php echo get_permalink($listing_id); ?>" class="hph-btn hph-btn-outline hph-btn-sm">
                        <i class="fas fa-home hph-mr-1"></i>
                        View Property
                    </a>
                <?php endif; ?>
            </div>
            
            <?php if ($agent_phone && ($event_status === 'scheduled' || $event_status === 'active')) : ?>
                <div class="hph-contact-agent hph-mt-3">
                    <a href="tel:<?php echo esc_attr($agent_phone); ?>" class="hph-btn hph-btn-success hph-btn-sm hph-btn-block">
                        <i class="fas fa-phone hph-mr-1"></i>
                        Call Host Agent
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
</article>