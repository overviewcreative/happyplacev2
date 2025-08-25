<?php
/**
 * Open House Widget - Upcoming Open Houses Display
 *
 * @package HappyPlaceTheme
 */

// Default attributes - can be overridden when including this component
$widget_args = wp_parse_args($args ?? [], [
    'title' => 'Upcoming Open Houses',
    'limit' => 5,
    'show_rsvp_button' => true,
    'show_agent_info' => true,
    'compact_mode' => false
]);

// Get Open House Service
$open_house_service = null;
$upcoming_open_houses = [];

if (class_exists('HappyPlace\\Services\\OpenHouseService')) {
    $open_house_service = new \HappyPlace\Services\OpenHouseService();
    $open_house_service->init();
    $upcoming_open_houses = $open_house_service->get_upcoming_open_houses($widget_args['limit']);
}

if (empty($upcoming_open_houses)) {
    return;
}
?>

<div class="hph-open-house-widget hph-widget">
    <?php if ($widget_args['title']) : ?>
        <div class="hph-widget-header">
            <h3 class="hph-widget-title"><?php echo esc_html($widget_args['title']); ?></h3>
        </div>
    <?php endif; ?>
    
    <div class="hph-widget-content">
        <div class="hph-open-house-list">
            <?php foreach ($upcoming_open_houses as $open_house) : 
                $start_date = $open_house['start_date'];
                $start_time = $open_house['start_time'];
                $end_time = $open_house['end_time'];
                
                $formatted_date = date('M j', strtotime($start_date));
                $day_of_week = date('l', strtotime($start_date));
                $formatted_start_time = $start_time ? date('g:i A', strtotime($start_time)) : '';
                $formatted_end_time = $end_time ? date('g:i A', strtotime($end_time)) : '';
                $time_range = $formatted_start_time . ($formatted_end_time ? ' - ' . $formatted_end_time : '');
                
                $is_today = date('Y-m-d') === $start_date;
                $is_tomorrow = date('Y-m-d', strtotime('+1 day')) === $start_date;
                
                // Get listing data
                $listing_data = null;
                $listing_photo = '';
                $listing_price = '';
                
                if ($open_house['listing_id']) {
                    $listing_data = hpt_get_listing($open_house['listing_id']);
                    if ($listing_data) {
                        $listing_photo = $listing_data['featured_image'] ?? '';
                        $listing_price = $listing_data['price'] ?? '';
                    }
                }
            ?>
                
                <div class="hph-open-house-item hph-card hph-hover-lift hph-mb-4">
                    <div class="hph-card-content hph-p-4">
                        <div class="hph-flex hph-gap-4">
                            
                            <!-- Date Circle -->
                            <div class="hph-date-circle hph-flex-shrink-0">
                                <div class="hph-date-display hph-text-center hph-p-3 hph-bg-primary hph-text-white hph-rounded-full <?php echo $is_today ? 'hph-pulse' : ''; ?>">
                                    <div class="hph-date-month hph-text-xs hph-font-medium"><?php echo date('M', strtotime($start_date)); ?></div>
                                    <div class="hph-date-day hph-text-lg hph-font-bold"><?php echo date('j', strtotime($start_date)); ?></div>
                                </div>
                                
                                <?php if ($is_today) : ?>
                                    <div class="hph-date-badge hph-text-xs hph-text-center hph-mt-1 hph-font-medium hph-text-primary">Today</div>
                                <?php elseif ($is_tomorrow) : ?>
                                    <div class="hph-date-badge hph-text-xs hph-text-center hph-mt-1 hph-font-medium hph-text-warning">Tomorrow</div>
                                <?php else : ?>
                                    <div class="hph-date-badge hph-text-xs hph-text-center hph-mt-1 hph-text-muted"><?php echo date('D', strtotime($start_date)); ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Open House Info -->
                            <div class="hph-open-house-info hph-flex-1 hph-min-w-0">
                                <div class="hph-property-title hph-mb-1">
                                    <a href="<?php echo esc_url($open_house['permalink']); ?>" class="hph-link-primary hph-font-medium hph-text-sm">
                                        <?php echo esc_html($open_house['listing_title'] ?: $open_house['title']); ?>
                                    </a>
                                </div>
                                
                                <?php if ($open_house['address']) : ?>
                                    <div class="hph-property-address hph-text-xs hph-text-muted hph-mb-2 hph-flex hph-items-center hph-gap-1">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span class="hph-truncate"><?php echo esc_html($open_house['address']); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="hph-time-info hph-flex hph-items-center hph-gap-3 hph-text-xs hph-text-muted hph-mb-2">
                                    <div class="hph-flex hph-items-center hph-gap-1">
                                        <i class="fas fa-clock"></i>
                                        <span><?php echo esc_html($time_range); ?></span>
                                    </div>
                                    
                                    <?php if ($open_house['rsvp_count'] > 0) : ?>
                                        <div class="hph-flex hph-items-center hph-gap-1">
                                            <i class="fas fa-users"></i>
                                            <span><?php echo esc_html($open_house['rsvp_count']); ?> RSVPs</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($listing_price && !$widget_args['compact_mode']) : ?>
                                    <div class="hph-property-price hph-font-bold hph-text-primary hph-text-sm hph-mb-2">
                                        <?php echo esc_html($listing_price); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Action Buttons -->
                                <div class="hph-action-buttons hph-flex hph-gap-2">
                                    <?php if ($widget_args['show_rsvp_button'] && $open_house['status'] !== 'completed') : ?>
                                        <button class="hph-btn hph-btn-primary hph-btn-xs hph-rsvp-quick-btn" 
                                                data-open-house-id="<?php echo esc_attr($open_house['id']); ?>">
                                            <i class="fas fa-check hph-mr-1"></i>
                                            RSVP
                                        </button>
                                    <?php endif; ?>
                                    
                                    <a href="<?php echo esc_url($open_house['permalink']); ?>" 
                                       class="hph-btn hph-btn-outline hph-btn-xs">
                                        <i class="fas fa-info-circle hph-mr-1"></i>
                                        Details
                                    </a>
                                    
                                    <?php if ($open_house['listing_id']) : ?>
                                        <a href="<?php echo get_permalink($open_house['listing_id']); ?>" 
                                           class="hph-btn hph-btn-outline hph-btn-xs">
                                            <i class="fas fa-home hph-mr-1"></i>
                                            Property
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Property Photo (if not compact mode) -->
                            <?php if (!$widget_args['compact_mode'] && $listing_photo) : ?>
                                <div class="hph-property-photo hph-flex-shrink-0">
                                    <img src="<?php echo esc_url($listing_photo); ?>" 
                                         alt="<?php echo esc_attr($open_house['listing_title']); ?>" 
                                         class="hph-w-16 hph-h-16 hph-object-cover hph-rounded">
                                </div>
                            <?php endif; ?>
                            
                        </div>
                    </div>
                </div>
                
            <?php endforeach; ?>
        </div>
        
        <!-- View All Link -->
        <div class="hph-widget-footer hph-text-center hph-mt-4">
            <a href="<?php echo get_post_type_archive_link('open_house'); ?>" class="hph-btn hph-btn-outline hph-btn-sm">
                View All Open Houses
            </a>
        </div>
    </div>
</div>

<!-- Quick RSVP Modal -->
<div id="hph-quick-rsvp-modal" class="hph-modal hph-hidden">
    <div class="hph-modal-overlay"></div>
    <div class="hph-modal-content hph-max-w-md">
        <div class="hph-modal-header hph-flex hph-justify-between hph-items-center hph-p-4 hph-border-b">
            <h3 class="hph-modal-title">Quick RSVP</h3>
            <button class="hph-modal-close hph-btn hph-btn-ghost hph-btn-sm">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="hph-modal-body hph-p-4">
            <div id="hph-quick-rsvp-form-container">
                <!-- RSVP form will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Quick RSVP button handler
    $('.hph-rsvp-quick-btn').on('click', function(e) {
        e.preventDefault();
        var openHouseId = $(this).data('open-house-id');
        
        // Load RSVP form
        var formHtml = '<?php echo addslashes(do_shortcode('[hp_open_house_rsvp open_house_id="' . '" title="" button_text="Submit RSVP"]')); ?>';
        formHtml = formHtml.replace('open_house_id=""', 'open_house_id="' + openHouseId + '"');
        
        $('#hph-quick-rsvp-form-container').html(formHtml);
        $('#hph-quick-rsvp-modal').removeClass('hph-hidden');
    });
    
    // Close modal
    $('.hph-modal-close, .hph-modal-overlay').on('click', function() {
        $('#hph-quick-rsvp-modal').addClass('hph-hidden');
    });
});
</script>