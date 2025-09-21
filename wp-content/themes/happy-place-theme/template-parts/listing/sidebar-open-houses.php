<?php
/**
 * Open Houses Widget Template Part
 * File: template-parts/listing/sidebar-open-houses.php
 * 
 * Displays scheduled open houses for the property
 * Uses HPH framework utilities and CSS variables
 * 
 * @package HappyPlaceTheme
 */

$listing_id = $args['listing_id'] ?? get_the_ID();

// Get open house data from ACF repeater field
$open_houses = get_field('open_houses', $listing_id) ?: [];

// Filter for upcoming open houses only
$upcoming_open_houses = [];
$today = new DateTime();

foreach ($open_houses as $open_house) {
    if (!empty($open_house['date'])) {
        $event_date = DateTime::createFromFormat('Y-m-d', $open_house['date']);
        if ($event_date && $event_date >= $today) {
            $open_house['datetime_obj'] = $event_date;
            $upcoming_open_houses[] = $open_house;
        }
    }
}

// Sort by date
usort($upcoming_open_houses, function($a, $b) {
    return $a['datetime_obj'] <=> $b['datetime_obj'];
});

// Also check for virtual tour info
$virtual_tour_date = get_field('virtual_tour_date', $listing_id);
$virtual_tour_time = get_field('virtual_tour_time', $listing_id);
$virtual_tour_link = get_field('virtual_tour_registration_link', $listing_id);

if (empty($upcoming_open_houses) && !$virtual_tour_date) {
    return;
}

// Get agent info for RSVP
$agent_email = get_field('agent_email', get_field('listing_agent', $listing_id)) ?: get_the_author_meta('email');
$agent_name = get_field('agent_name', get_field('listing_agent', $listing_id)) ?: get_the_author_meta('display_name');
$agent_phone = get_field('agent_phone', get_field('listing_agent', $listing_id));

$property_address = trim(get_field('street_number', $listing_id) . ' ' . 
                        get_field('street_name', $listing_id) . ' ' . 
                        get_field('street_type', $listing_id));
?>

<div class="hph-widget hph-widget--open-houses hph-bg-white hph-rounded-lg hph-shadow-md hph-p-lg hph-mb-xl">
    
    <div class="hph-widget__header hph-mb-lg">
        <h3 class="hph-widget__title hph-text-xl hph-font-bold hph-flex hph-items-center hph-gap-sm">
            <i class="fas fa-calendar-check hph-text-primary"></i>
            Open Houses & Tours
        </h3>
        <?php if (count($upcoming_open_houses) > 0) : ?>
        <p class="hph-text-sm hph-text-gray-600 hph-mt-xs">
            <?php echo count($upcoming_open_houses); ?> upcoming event<?php echo count($upcoming_open_houses) > 1 ? 's' : ''; ?>
        </p>
        <?php endif; ?>
    </div>
    
    <!-- In-Person Open Houses -->
    <?php if (!empty($upcoming_open_houses)) : ?>
    <div class="hph-open-houses-list hph-space-y-md hph-mb-lg">
        
        <?php foreach ($upcoming_open_houses as $index => $open_house) : ?>
        <?php 
        $date = $open_house['datetime_obj'];
        $start_time = $open_house['start_time'] ?? '10:00 AM';
        $end_time = $open_house['end_time'] ?? '4:00 PM';
        $host_name = $open_house['host_name'] ?? $agent_name;
        $host_phone = $open_house['host_phone'] ?? $agent_phone;
        $notes = $open_house['notes'] ?? '';
        $requires_rsvp = $open_house['requires_rsvp'] ?? false;
        
        // Check if this is happening today
        $is_today = $date->format('Y-m-d') === $today->format('Y-m-d');
        $is_this_week = $date->format('W Y') === $today->format('W Y');
        ?>
        
        <div class="hph-open-house-event hph-border hph-border-gray-200 hph-rounded-lg hph-p-md <?php echo $is_today ? 'hph-border-primary hph-bg-primary-50' : ''; ?>">
            
            <?php if ($is_today) : ?>
            <div class="hph-event-badge hph-inline-flex hph-items-center hph-gap-xs hph-px-sm hph-py-xs hph-bg-primary hph-text-white hph-rounded-full hph-text-xs hph-font-semibold hph-mb-sm">
                <i class="fas fa-clock"></i>
                Today
            </div>
            <?php elseif ($is_this_week) : ?>
            <div class="hph-event-badge hph-inline-flex hph-items-center hph-gap-xs hph-px-sm hph-py-xs hph-bg-success hph-text-white hph-rounded-full hph-text-xs hph-font-semibold hph-mb-sm">
                This Week
            </div>
            <?php endif; ?>
            
            <div class="hph-event-date hph-flex hph-items-start hph-gap-md hph-mb-md">
                <div class="hph-date-icon hph-flex-shrink-0 hph-w-12 hph-h-12 hph-bg-primary-100 hph-rounded-lg hph-flex hph-flex-col hph-items-center hph-justify-center">
                    <span class="hph-text-xs hph-font-medium hph-text-primary-600">
                        <?php echo $date->format('M'); ?>
                    </span>
                    <span class="hph-text-lg hph-font-bold hph-text-primary">
                        <?php echo $date->format('j'); ?>
                    </span>
                </div>
                
                <div class="hph-event-details hph-flex-1">
                    <div class="hph-event-day hph-font-semibold hph-text-gray-900 hph-mb-xs">
                        <?php echo $date->format('l, F j, Y'); ?>
                    </div>
                    <div class="hph-event-time hph-text-sm hph-text-gray-700 hph-flex hph-items-center hph-gap-xs">
                        <i class="fas fa-clock hph-text-gray-400"></i>
                        <?php echo esc_html($start_time); ?> - <?php echo esc_html($end_time); ?>
                    </div>
                    
                    <?php if ($host_name) : ?>
                    <div class="hph-event-host hph-text-sm hph-text-gray-600 hph-mt-xs hph-flex hph-items-center hph-gap-xs">
                        <i class="fas fa-user hph-text-gray-400"></i>
                        Hosted by <?php echo esc_html($host_name); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($notes) : ?>
                    <div class="hph-event-notes hph-text-sm hph-text-gray-600 hph-mt-sm hph-p-sm hph-bg-gray-50 hph-rounded-md">
                        <?php echo esc_html($notes); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="hph-event-actions hph-flex hph-gap-sm">
                
                <button type="button" 
                        class="hph-btn hph-btn-primary hph-btn-sm hph-flex-1 hph-flex hph-items-center hph-justify-center hph-gap-xs"
                        data-open-house-id="<?php echo esc_attr($index); ?>"
                        onclick="openRSVPModal(<?php echo esc_attr($index); ?>)">
                    <i class="fas fa-calendar-plus"></i>
                    <?php echo $requires_rsvp ? 'RSVP Required' : 'RSVP'; ?>
                </button>
                
                <button type="button" 
                        class="hph-btn hph-btn-secondary hph-btn-sm hph-flex hph-items-center hph-justify-center hph-gap-xs"
                        onclick="addToCalendar(<?php echo esc_attr($index); ?>)">
                    <i class="fas fa-calendar"></i>
                    <span class="hph-hidden hph-sm:hph-inline">Add to Calendar</span>
                </button>
                
            </div>
            
        </div>
        <?php endforeach; ?>
        
    </div>
    <?php endif; ?>
    
    <!-- Virtual Tour Option -->
    <?php if ($virtual_tour_date) : ?>
    <?php 
    $virtual_date = DateTime::createFromFormat('Y-m-d', $virtual_tour_date);
    $is_upcoming = $virtual_date && $virtual_date >= $today;
    ?>
    
    <?php if ($is_upcoming) : ?>
    <div class="hph-virtual-tour-event hph-bg-gray-50 hph-border hph-border-gray-200 hph-rounded-lg hph-p-md">
        
        <div class="hph-event-header hph-flex hph-items-center hph-gap-sm hph-mb-md">
            <i class="fas fa-video hph-text-primary hph-text-lg"></i>
            <h4 class="hph-font-semibold">Virtual Tour Available</h4>
        </div>
        
        <div class="hph-event-details hph-text-sm hph-text-gray-700 hph-mb-md">
            <div class="hph-mb-xs">
                <i class="fas fa-calendar hph-text-gray-400 hph-mr-xs"></i>
                <?php echo $virtual_date->format('l, F j, Y'); ?>
            </div>
            <?php if ($virtual_tour_time) : ?>
            <div>
                <i class="fas fa-clock hph-text-gray-400 hph-mr-xs"></i>
                <?php echo esc_html($virtual_tour_time); ?>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($virtual_tour_link) : ?>
        <a href="<?php echo esc_url($virtual_tour_link); ?>" 
           target="_blank"
           class="hph-btn hph-btn-primary hph-btn-sm w-full hph-flex hph-items-center hph-justify-center hph-gap-xs">
            <i class="fas fa-external-link-alt"></i>
            Register for Virtual Tour
        </a>
        <?php else : ?>
        <button type="button"
                onclick="requestVirtualTour()"
                class="hph-btn hph-btn-primary hph-btn-sm w-full hph-flex hph-items-center hph-justify-center hph-gap-xs">
            <i class="fas fa-video"></i>
            Request Virtual Tour
        </button>
        <?php endif; ?>
        
    </div>
    <?php endif; ?>
    <?php endif; ?>
    
    <!-- Private Showing Request -->
    <div class="hph-private-showing hph-mt-lg hph-pt-lg hph-border-t hph-border-gray-200">
        <h4 class="hph-text-sm hph-font-semibold hph-mb-sm">Prefer a Private Showing?</h4>
        <p class="hph-text-xs hph-text-gray-600 hph-mb-md">
            Schedule a convenient time with our agent
        </p>
        <button type="button"
                onclick="requestPrivateShowing()"
                class="hph-btn hph-btn-secondary hph-btn-sm w-full hph-flex hph-items-center hph-justify-center hph-gap-xs">
            <i class="fas fa-home"></i>
            Request Private Tour
        </button>
    </div>
    
</div>

<!-- RSVP Modal -->
<div id="rsvp-modal" class="hph-modal hph-fixed hph-inset-0 hph-z-50 hph-hidden">
    <div class="hph-modal__overlay hph-absolute hph-inset-0 hph-bg-black hph-bg-opacity-50"></div>
    
    <div class="hph-modal__content hph-relative hph-mx-auto hph-my-xl hph-max-w-lg hph-bg-white hph-rounded-lg hph-shadow-xl hph-p-xl">
        
        <button type="button" 
                onclick="closeRSVPModal()"
                class="hph-modal__close hph-absolute hph-top-md hph-right-md hph-text-gray-400 hover:hph-text-gray-600">
            <i class="fas fa-times hph-text-xl"></i>
        </button>
        
        <h3 class="hph-modal__title hph-text-xl hph-font-bold hph-mb-lg">RSVP for Open House</h3>
        
        <form id="rsvp-form" 
              class="hph-space-y-md hph-form"
              data-route-type="booking_request"
              data-form-context="open_house_rsvp">
            
            <div class="hph-form-group">
                <label class="hph-form-label hph-text-sm hph-font-medium hph-text-gray-700 hph-mb-xs hph-block">
                    Name <span class="hph-text-danger">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       required
                       class="hph-form-input">
            </div>
            
            <div class="hph-form-group">
                <label class="hph-form-label hph-text-sm hph-font-medium hph-text-gray-700 hph-mb-xs hph-block">
                    Email <span class="hph-text-danger">*</span>
                </label>
                <input type="email" 
                       name="email" 
                       required
                       class="hph-form-input">
            </div>
            
            <div class="hph-form-group">
                <label class="hph-form-label hph-text-sm hph-font-medium hph-text-gray-700 hph-mb-xs hph-block">
                    Phone
                </label>
                <input type="tel" 
                       name="phone"
                       class="hph-form-input">
            </div>
            
            <div class="hph-form-group">
                <label class="hph-form-label hph-text-sm hph-font-medium hph-text-gray-700 hph-mb-xs hph-block">
                    Number of Guests
                </label>
                <select name="guests" 
                        class="hph-form-select">
                    <option value="1">1 person</option>
                    <option value="2">2 people</option>
                    <option value="3">3 people</option>
                    <option value="4">4 people</option>
                    <option value="5+">5 or more</option>
                </select>
            </div>
            
            <div class="hph-form-group">
                <label class="hph-form-label hph-text-sm hph-font-medium hph-text-gray-700 hph-mb-xs hph-block">
                    Questions or Comments
                </label>
                <textarea name="message" 
                          rows="3"
                          class="hph-form-textarea"></textarea>
            </div>
            
            <input type="hidden" name="listing_id" value="<?php echo esc_attr($listing_id); ?>">
            <input type="hidden" name="property_address" value="<?php echo esc_attr($property_address); ?>">
            <input type="hidden" name="open_house_date" value="">
            <input type="hidden" name="open_house_time" value="">
            <input type="hidden" name="action" value="hph_route_form">
            <input type="hidden" name="route_type" value="booking_request">
            <input type="hidden" name="form_type" value="open_house_rsvp">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('hph_booking_form'); ?>">
            
            <div class="hph-form-actions hph-flex hph-gap-sm hph-pt-md">
                <button type="submit" 
                        class="hph-btn hph-btn-primary hph-flex-1">
                    Confirm RSVP
                </button>
                <button type="button" 
                        onclick="closeRSVPModal()"
                        class="hph-btn hph-btn-secondary hph-flex-1">
                    Cancel
                </button>
            </div>
            
        </form>
        
    </div>
</div>

<script>
// Open house data for JavaScript
const openHouses = <?php echo json_encode(array_map(function($oh) {
    return [
        'date' => $oh['datetime_obj']->format('Y-m-d'),
        'display_date' => $oh['datetime_obj']->format('l, F j, Y'),
        'start_time' => $oh['start_time'] ?? '10:00 AM',
        'end_time' => $oh['end_time'] ?? '4:00 PM',
        'host_name' => $oh['host_name'] ?? '',
        'notes' => $oh['notes'] ?? ''
    ];
}, $upcoming_open_houses)); ?>;

function openRSVPModal(index) {
    const modal = document.getElementById('rsvp-modal');
    const openHouse = openHouses[index];
    
    // Set hidden fields
    document.querySelector('[name="open_house_date"]').value = openHouse.display_date;
    document.querySelector('[name="open_house_time"]').value = openHouse.start_time + ' - ' + openHouse.end_time;
    
    modal.classList.remove('hph-hidden');
    document.body.style.overflow = 'hidden';
}

function closeRSVPModal() {
    const modal = document.getElementById('rsvp-modal');
    modal.classList.add('hph-hidden');
    document.body.style.overflow = '';
}

function addToCalendar(index) {
    const openHouse = openHouses[index];
    const title = 'Open House: <?php echo esc_js($property_address); ?>';
    const details = openHouse.notes || 'Open house viewing';
    const location = '<?php echo esc_js($property_address); ?>';
    
    // Create Google Calendar link
    const googleUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE' +
        '&text=' + encodeURIComponent(title) +
        '&dates=' + openHouse.date.replace(/-/g, '') + '/' + openHouse.date.replace(/-/g, '') +
        '&details=' + encodeURIComponent(details) +
        '&location=' + encodeURIComponent(location);
    
    window.open(googleUrl, '_blank');
}

function requestPrivateShowing() {
    // Open contact form or trigger modal
    const contactForm = document.getElementById('agent-contact-form');
    if (contactForm) {
        const message = contactForm.querySelector('[name="message"]');
        if (message) {
            message.value = 'I would like to schedule a private showing for <?php echo esc_js($property_address); ?>.';
        }
        contactForm.scrollIntoView({ behavior: 'smooth' });
    }
}

function requestVirtualTour() {
    // Similar to requestPrivateShowing
    const contactForm = document.getElementById('agent-contact-form');
    if (contactForm) {
        const message = contactForm.querySelector('[name="message"]');
        if (message) {
            message.value = 'I would like to request a virtual tour for <?php echo esc_js($property_address); ?>.';
        }
        contactForm.scrollIntoView({ behavior: 'smooth' });
    }
}

// RSVP Form submission
document.getElementById('rsvp-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Here you would typically send this to your server
    // For now, we'll just show a success message
    
    alert('Thank you for your RSVP! We look forward to seeing you at the open house.');
    closeRSVPModal();
    
    // Reset form
    this.reset();
});

// Close modal on overlay click
document.querySelector('.hph-modal__overlay')?.addEventListener('click', closeRSVPModal);
</script>
