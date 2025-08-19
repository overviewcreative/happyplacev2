<?php
/**
 * Dashboard Calendar Section
 * Calendar management for showings and appointments
 *
 * @package HappyPlace
 */

namespace HappyPlace\Dashboard;

if (!defined('ABSPATH')) {
    exit;
}

class Calendar_Section {

    private Dashboard_Manager $dashboard_manager;

    public function __construct(Dashboard_Manager $dashboard_manager) {
        $this->dashboard_manager = $dashboard_manager;
    }

    public function render(): void {
        $action = $this->dashboard_manager->get_current_action();
        
        echo '<div class="hpt-calendar-section">';
        
        switch ($action) {
            case 'add':
                $this->render_add_event_form();
                break;
            case 'edit':
                $this->render_edit_event_form();
                break;
            default:
                $this->render_calendar_view();
        }
        
        echo '</div>';
    }

    private function render_calendar_view(): void {
        echo '<div class="hpt-calendar-view">';
        
        // Header
        echo '<div class="hpt-section-header">';
        echo '<div class="hpt-section-header__left">';
        echo '<h2>Calendar</h2>';
        echo '<p>Manage your showings, appointments, and events.</p>';
        echo '</div>';
        echo '<div class="hpt-section-header__right">';
        echo '<button id="add-event-btn" class="hpt-button hpt-button--primary">';
        echo '<span class="dashicons dashicons-plus-alt2"></span> Schedule Event';
        echo '</button>';
        echo '</div>';
        echo '</div>';

        // Calendar Controls
        echo '<div class="hpt-calendar-controls hpt-card">';
        echo '<div class="hpt-card__body">';
        echo '<div class="hpt-calendar-nav">';
        echo '<button id="prev-month" class="hpt-button hpt-button--outline hpt-button--sm">';
        echo '<span class="dashicons dashicons-arrow-left-alt2"></span>';
        echo '</button>';
        echo '<h3 id="current-month">' . date('F Y') . '</h3>';
        echo '<button id="next-month" class="hpt-button hpt-button--outline hpt-button--sm">';
        echo '<span class="dashicons dashicons-arrow-right-alt2"></span>';
        echo '</button>';
        echo '</div>';

        echo '<div class="hpt-calendar-filters">';
        echo '<div class="hpt-filter-group">';
        echo '<label for="event-type-filter">Event Type</label>';
        echo '<select id="event-type-filter" class="hpt-form__select">';
        echo '<option value="">All Events</option>';
        echo '<option value="showing">Showings</option>';
        echo '<option value="open_house">Open Houses</option>';
        echo '<option value="appointment">Appointments</option>';
        echo '<option value="closing">Closings</option>';
        echo '<option value="meeting">Meetings</option>';
        echo '</select>';
        echo '</div>';
        echo '<div class="hpt-filter-group">';
        echo '<label for="listing-filter">Property</label>';
        echo '<select id="listing-filter" class="hpt-form__select">';
        echo '<option value="">All Properties</option>';
        $this->render_listing_options();
        echo '</select>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        // Calendar Grid
        echo '<div class="hpt-calendar-container hpt-card">';
        echo '<div class="hpt-card__body">';
        echo '<div id="calendar-grid" class="hpt-calendar-grid">';
        $this->render_calendar_grid();
        echo '</div>';
        echo '</div>';
        echo '</div>';

        // Upcoming Events Sidebar
        echo '<div class="hpt-upcoming-events hpt-card">';
        echo '<div class="hpt-card__header">';
        echo '<h3>Upcoming Events</h3>';
        echo '</div>';
        echo '<div class="hpt-card__body">';
        $this->render_upcoming_events();
        echo '</div>';
        echo '</div>';

        // Add Event Modal
        $this->render_event_modal();
        
        $this->render_calendar_scripts();
        echo '</div>';
    }

    private function render_listing_options(): void {
        $agent_id = $this->dashboard_manager->get_current_agent_id();
        
        $listings = get_posts([
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => 'listing_agent',
                    'value' => '"' . $agent_id . '"',
                    'compare' => 'LIKE'
                ]
            ],
            'orderby' => 'title',
            'order' => 'ASC'
        ]);

        foreach ($listings as $listing) {
            $address = get_field('street_address', $listing->ID);
            echo '<option value="' . $listing->ID . '">' . esc_html($address ?: $listing->post_title) . '</option>';
        }
    }

    private function render_calendar_grid(): void {
        $today = date('Y-m-d');
        $first_day = date('Y-m-01');
        $last_day = date('Y-m-t');
        $start_date = date('Y-m-d', strtotime($first_day . ' -' . date('w', strtotime($first_day)) . ' days'));
        $end_date = date('Y-m-d', strtotime($last_day . ' +' . (6 - date('w', strtotime($last_day))) . ' days'));

        // Calendar header
        echo '<div class="hpt-calendar-header">';
        $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        foreach ($days as $day) {
            echo '<div class="hpt-calendar-day-header">' . $day . '</div>';
        }
        echo '</div>';

        // Calendar body
        echo '<div class="hpt-calendar-body">';
        $current_date = $start_date;
        while ($current_date <= $end_date) {
            $is_today = $current_date === $today;
            $is_current_month = date('m', strtotime($current_date)) === date('m');
            $day_number = date('j', strtotime($current_date));
            
            $classes = ['hpt-calendar-day'];
            if ($is_today) $classes[] = 'hpt-calendar-day--today';
            if (!$is_current_month) $classes[] = 'hpt-calendar-day--other-month';
            
            echo '<div class="' . implode(' ', $classes) . '" data-date="' . $current_date . '">';
            echo '<div class="hpt-calendar-day-number">' . $day_number . '</div>';
            echo '<div class="hpt-calendar-day-events">';
            $this->render_day_events($current_date);
            echo '</div>';
            echo '</div>';
            
            $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
        }
        echo '</div>';
    }

    private function render_day_events($date): void {
        // Mock events - in real implementation, these would come from database
        $mock_events = [
            '2024-01-15' => [
                ['title' => 'Showing - Oak Street', 'time' => '10:00 AM', 'type' => 'showing'],
                ['title' => 'Client Meeting', 'time' => '2:00 PM', 'type' => 'meeting']
            ],
            '2024-01-20' => [
                ['title' => 'Open House - Sunset Villa', 'time' => '1:00 PM', 'type' => 'open_house']
            ],
            '2024-01-25' => [
                ['title' => 'Closing - Mountain View', 'time' => '11:00 AM', 'type' => 'closing']
            ]
        ];

        if (isset($mock_events[$date])) {
            foreach ($mock_events[$date] as $event) {
                echo '<div class="hpt-calendar-event hpt-calendar-event--' . esc_attr($event['type']) . '">';
                echo '<div class="hpt-event-time">' . esc_html($event['time']) . '</div>';
                echo '<div class="hpt-event-title">' . esc_html($event['title']) . '</div>';
                echo '</div>';
            }
        }
    }

    private function render_upcoming_events(): void {
        // Mock upcoming events
        $upcoming_events = [
            [
                'title' => 'Showing - 123 Oak Street',
                'type' => 'showing',
                'date' => 'Today',
                'time' => '2:00 PM',
                'client' => 'John & Jane Smith',
                'listing_id' => 123
            ],
            [
                'title' => 'Open House - Sunset Villa',
                'type' => 'open_house',
                'date' => 'Tomorrow',
                'time' => '1:00 - 4:00 PM',
                'client' => '',
                'listing_id' => 456
            ],
            [
                'title' => 'Client Consultation',
                'type' => 'appointment',
                'date' => 'Friday',
                'time' => '10:00 AM',
                'client' => 'Mary Johnson',
                'listing_id' => null
            ],
            [
                'title' => 'Property Closing',
                'type' => 'closing',
                'date' => 'Next Monday',
                'time' => '11:00 AM',
                'client' => 'Robert Wilson',
                'listing_id' => 789
            ]
        ];

        if (empty($upcoming_events)) {
            echo '<div class="hpt-empty-state">';
            echo '<p>No upcoming events.</p>';
            echo '</div>';
            return;
        }

        echo '<div class="hpt-event-list">';
        foreach ($upcoming_events as $event) {
            echo '<div class="hpt-event-item">';
            echo '<div class="hpt-event-type-indicator hpt-event-type--' . esc_attr($event['type']) . '"></div>';
            echo '<div class="hpt-event-content">';
            echo '<div class="hpt-event-title">' . esc_html($event['title']) . '</div>';
            echo '<div class="hpt-event-datetime">';
            echo '<span class="hpt-event-date">' . esc_html($event['date']) . '</span>';
            echo '<span class="hpt-event-time">' . esc_html($event['time']) . '</span>';
            echo '</div>';
            if ($event['client']) {
                echo '<div class="hpt-event-client">Client: ' . esc_html($event['client']) . '</div>';
            }
            echo '</div>';
            echo '<div class="hpt-event-actions">';
            echo '<button class="hpt-button hpt-button--sm hpt-button--outline" title="Edit">✏️</button>';
            echo '<button class="hpt-button hpt-button--sm hpt-button--outline" title="Delete">🗑</button>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    }

    private function render_event_modal(): void {
        echo '<div id="event-modal" class="hpt-modal" style="display: none;">';
        echo '<div class="hpt-modal__backdrop"></div>';
        echo '<div class="hpt-modal__container">';
        echo '<div class="hpt-modal__header">';
        echo '<h3 id="event-modal-title">Schedule Event</h3>';
        echo '<button class="hpt-modal__close" id="close-event-modal">&times;</button>';
        echo '</div>';
        echo '<div class="hpt-modal__body">';
        
        echo '<form id="event-form" class="hpt-event-form">';
        echo '<input type="hidden" id="event-id" name="event_id" value="">';
        
        echo '<div class="hpt-form-grid">';
        
        echo '<div class="hpt-form__group">';
        echo '<label for="event-title" class="hpt-form__label">Event Title <span class="required">*</span></label>';
        echo '<input type="text" id="event-title" name="title" class="hpt-form__input" required>';
        echo '</div>';
        
        echo '<div class="hpt-form__group">';
        echo '<label for="event-type" class="hpt-form__label">Event Type <span class="required">*</span></label>';
        echo '<select id="event-type" name="type" class="hpt-form__select" required>';
        echo '<option value="">Select Type</option>';
        echo '<option value="showing">Property Showing</option>';
        echo '<option value="open_house">Open House</option>';
        echo '<option value="appointment">Appointment</option>';
        echo '<option value="closing">Closing</option>';
        echo '<option value="meeting">Meeting</option>';
        echo '<option value="other">Other</option>';
        echo '</select>';
        echo '</div>';
        
        echo '<div class="hpt-form__group">';
        echo '<label for="event-date" class="hpt-form__label">Date <span class="required">*</span></label>';
        echo '<input type="date" id="event-date" name="date" class="hpt-form__input" required>';
        echo '</div>';
        
        echo '<div class="hpt-form__group">';
        echo '<label for="event-start-time" class="hpt-form__label">Start Time <span class="required">*</span></label>';
        echo '<input type="time" id="event-start-time" name="start_time" class="hpt-form__input" required>';
        echo '</div>';
        
        echo '<div class="hpt-form__group">';
        echo '<label for="event-end-time" class="hpt-form__label">End Time</label>';
        echo '<input type="time" id="event-end-time" name="end_time" class="hpt-form__input">';
        echo '</div>';
        
        echo '<div class="hpt-form__group">';
        echo '<label for="event-listing" class="hpt-form__label">Property</label>';
        echo '<select id="event-listing" name="listing_id" class="hpt-form__select">';
        echo '<option value="">Select Property</option>';
        $this->render_listing_options();
        echo '</select>';
        echo '</div>';
        
        echo '<div class="hpt-form__group hpt-form__group--full">';
        echo '<label for="event-client" class="hpt-form__label">Client Name</label>';
        echo '<input type="text" id="event-client" name="client" class="hpt-form__input">';
        echo '</div>';
        
        echo '<div class="hpt-form__group hpt-form__group--full">';
        echo '<label for="event-notes" class="hpt-form__label">Notes</label>';
        echo '<textarea id="event-notes" name="notes" class="hpt-form__textarea" rows="3"></textarea>';
        echo '</div>';
        
        echo '</div>';
        echo '</form>';
        
        echo '</div>';
        echo '<div class="hpt-modal__footer">';
        echo '<button type="button" class="hpt-button hpt-button--secondary" id="cancel-event">Cancel</button>';
        echo '<button type="submit" form="event-form" class="hpt-button hpt-button--primary" id="save-event">Save Event</button>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    private function render_add_event_form(): void {
        echo '<div class="hpt-add-event-form">';
        echo '<div class="hpt-section-header">';
        echo '<div class="hpt-section-header__left">';
        echo '<h2>Schedule New Event</h2>';
        echo '</div>';
        echo '<div class="hpt-section-header__right">';
        echo '<a href="' . esc_url(home_url('/agent-dashboard/calendar/')) . '" class="hpt-button hpt-button--outline">';
        echo '<span class="dashicons dashicons-arrow-left-alt2"></span> Back to Calendar';
        echo '</a>';
        echo '</div>';
        echo '</div>';
        // Form would be rendered here
        echo '</div>';
    }

    private function render_edit_event_form(): void {
        echo '<div class="hpt-edit-event-form">';
        echo '<div class="hpt-section-header">';
        echo '<div class="hpt-section-header__left">';
        echo '<h2>Edit Event</h2>';
        echo '</div>';
        echo '<div class="hpt-section-header__right">';
        echo '<a href="' . esc_url(home_url('/agent-dashboard/calendar/')) . '" class="hpt-button hpt-button--outline">';
        echo '<span class="dashicons dashicons-arrow-left-alt2"></span> Back to Calendar';
        echo '</a>';
        echo '</div>';
        echo '</div>';
        // Form would be rendered here
        echo '</div>';
    }

    private function render_calendar_scripts(): void {
        echo '<script>';
        echo 'jQuery(document).ready(function($) {';
        
        // Modal handlers
        echo '$("#add-event-btn").on("click", function() {';
        echo '$("#event-modal").show();';
        echo '});';

        echo '$("#close-event-modal, #cancel-event, .hpt-modal__backdrop").on("click", function() {';
        echo '$("#event-modal").hide();';
        echo '$("#event-form")[0].reset();';
        echo '});';

        // Calendar navigation
        echo '$("#prev-month").on("click", function() {';
        echo '// Navigate to previous month';
        echo 'console.log("Previous month");';
        echo '});';

        echo '$("#next-month").on("click", function() {';
        echo '// Navigate to next month';
        echo 'console.log("Next month");';
        echo '});';

        // Day click handler
        echo '$(document).on("click", ".hpt-calendar-day", function() {';
        echo 'var date = $(this).data("date");';
        echo '$("#event-date").val(date);';
        echo '$("#event-modal").show();';
        echo '});';

        // Event form submission
        echo '$("#event-form").on("submit", function(e) {';
        echo 'e.preventDefault();';
        echo 'var formData = $(this).serialize();';
        echo 'formData += "&action=hpt_dashboard_action&dashboard_action=save_event&nonce=" + hptDashboard.nonce;';
        echo '$.post(ajaxurl, formData, function(response) {';
        echo 'if (response.success) {';
        echo '$("#event-modal").hide();';
        echo '$("#event-form")[0].reset();';
        echo 'hptShowNotice("success", response.data.message);';
        echo '// Reload calendar';
        echo '} else {';
        echo 'hptShowNotice("error", response.data.message);';
        echo '}';
        echo '});';
        echo '});';

        // Filter handlers
        echo '$("#event-type-filter, #listing-filter").on("change", function() {';
        echo '// Filter calendar events';
        echo 'console.log("Filter changed");';
        echo '});';

        echo '});';
        echo '</script>';
    }

    public function handle_ajax_save_event($data): array {
        $agent_id = $this->dashboard_manager->get_current_agent_id();
        $event_id = (int) ($data['event_id'] ?? 0);
        
        if (!$agent_id) {
            return ['success' => false, 'message' => 'Agent not found'];
        }

        // Validate required fields
        $required_fields = ['title', 'type', 'date', 'start_time'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => 'Missing required field: ' . $field];
            }
        }

        // Sanitize data
        $event_data = [
            'title' => sanitize_text_field($data['title']),
            'type' => sanitize_text_field($data['type']),
            'date' => sanitize_text_field($data['date']),
            'start_time' => sanitize_text_field($data['start_time']),
            'end_time' => sanitize_text_field($data['end_time'] ?? ''),
            'listing_id' => (int) ($data['listing_id'] ?? 0),
            'client' => sanitize_text_field($data['client'] ?? ''),
            'notes' => sanitize_textarea_field($data['notes'] ?? ''),
            'agent_id' => $agent_id
        ];

        // In real implementation, save to database
        // For now, just return success
        return [
            'success' => true,
            'data' => [
                'message' => $event_id ? 'Event updated successfully' : 'Event created successfully',
                'event_id' => $event_id ?: rand(1000, 9999)
            ]
        ];
    }

    public function handle_ajax_delete_event($data): array {
        $event_id = (int) ($data['event_id'] ?? 0);
        $agent_id = $this->dashboard_manager->get_current_agent_id();
        
        if (!$event_id || !$agent_id) {
            return ['success' => false, 'message' => 'Invalid request'];
        }

        // Verify ownership and delete event
        // In real implementation, this would delete from database
        
        return [
            'success' => true,
            'data' => ['message' => 'Event deleted successfully']
        ];
    }

    public function handle_ajax_get_events($data): array {
        $agent_id = $this->dashboard_manager->get_current_agent_id();
        $date_range = $data['date_range'] ?? date('Y-m');
        
        if (!$agent_id) {
            return ['success' => false, 'message' => 'Agent not found'];
        }

        // Mock events data
        $events = [
            [
                'id' => 1,
                'title' => 'Showing - Oak Street',
                'type' => 'showing',
                'date' => '2024-01-15',
                'start_time' => '10:00',
                'end_time' => '11:00',
                'client' => 'John Smith'
            ],
            [
                'id' => 2,
                'title' => 'Open House - Sunset Villa',
                'type' => 'open_house',
                'date' => '2024-01-20',
                'start_time' => '13:00',
                'end_time' => '16:00',
                'client' => ''
            ]
        ];

        return [
            'success' => true,
            'data' => $events
        ];
    }
}