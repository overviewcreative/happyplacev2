/**
 * Universal Calendar JavaScript
 * 
 * Handles calendar interactions, view switching, navigation, and AJAX loading.
 * Works with the universal calendar component following atomic design principles.
 * 
 * @package HappyPlaceTheme
 * @version 1.0.0
 */

(function($) {
    'use strict';

    // Calendar functionality
    const HphCalendar = {
        
        /**
         * Initialize all calendars on the page
         */
        init: function() {
            $('.hph-universal-calendar').each(function() {
                HphCalendar.initCalendar($(this));
            });
        },

        /**
         * Initialize a single calendar instance
         */
        initCalendar: function($calendar) {
            const calendarId = $calendar.attr('id');
            
            // Store calendar data
            $calendar.data('hph-calendar', {
                id: calendarId,
                currentDate: $calendar.data('date'),
                currentView: $calendar.data('view'),
                isLoading: false
            });

            // Bind events
            HphCalendar.bindEvents($calendar);
            
            // Initialize view-specific functionality
            HphCalendar.initializeView($calendar);
        },

        /**
         * Bind calendar events
         */
        bindEvents: function($calendar) {
            // Navigation buttons
            $calendar.on('click', '.hph-calendar-nav-btn', function(e) {
                e.preventDefault();
                HphCalendar.handleNavigation($calendar, $(this));
            });

            // View selector
            $calendar.on('change', '[data-calendar-view-selector]', function(e) {
                HphCalendar.handleViewChange($calendar, $(this).val());
            });

            // Calendar day clicks
            $calendar.on('click', '.hph-calendar-day', function(e) {
                if (!$(e.target).closest('.hph-calendar-event, .hph-calendar-more-events').length) {
                    HphCalendar.handleDayClick($calendar, $(this));
                }
            });

            // Event clicks
            $calendar.on('click', '.hph-calendar-event', function(e) {
                e.stopPropagation();
                HphCalendar.handleEventClick($calendar, $(this));
            });

            // More events button
            $calendar.on('click', '.hph-calendar-more-events', function(e) {
                e.stopPropagation();
                HphCalendar.handleMoreEventsClick($calendar, $(this));
            });

            // Event card interactions
            $calendar.on('click', '.hph-event-card', function(e) {
                // Allow clicking on links and buttons within cards
                if (!$(e.target).closest('a, button').length) {
                    const $link = $(this).find('.hph-event-card-link').first();
                    if ($link.length) {
                        window.location.href = $link.attr('href');
                    }
                }
            });
        },

        /**
         * Initialize view-specific functionality
         */
        initializeView: function($calendar) {
            const view = $calendar.data('hph-calendar').currentView;
            
            switch (view) {
                case 'month':
                    HphCalendar.initMonthView($calendar);
                    break;
                case 'list':
                    HphCalendar.initListView($calendar);
                    break;
                case 'grid':
                    HphCalendar.initGridView($calendar);
                    break;
            }
        },

        /**
         * Handle calendar navigation
         */
        handleNavigation: function($calendar, $button) {
            if (HphCalendar.isLoading($calendar)) {
                return;
            }

            const newDate = $button.data('date');
            if (!newDate) {
                return;
            }

            // Update calendar data
            const calendarData = $calendar.data('hph-calendar');
            calendarData.currentDate = newDate;

            // Load new calendar data
            HphCalendar.loadCalendarData($calendar, {
                date: newDate,
                view: calendarData.currentView
            });
        },

        /**
         * Handle view change
         */
        handleViewChange: function($calendar, newView) {
            if (HphCalendar.isLoading($calendar)) {
                return;
            }

            const calendarData = $calendar.data('hph-calendar');
            calendarData.currentView = newView;

            // Update calendar data attribute
            $calendar.attr('data-view', newView);
            $calendar.removeClass(function(index, className) {
                return (className.match(/(^|\s)hph-calendar-view-\S+/g) || []).join(' ');
            }).addClass('hph-calendar-view-' + newView);

            // Load new calendar data
            HphCalendar.loadCalendarData($calendar, {
                date: calendarData.currentDate,
                view: newView
            });
        },

        /**
         * Handle day click in calendar grid
         */
        handleDayClick: function($calendar, $day) {
            const date = $day.data('date');
            if (!date) {
                return;
            }

            // Remove previous selection
            $calendar.find('.hph-calendar-day.is-selected').removeClass('is-selected');
            
            // Add selection to clicked day
            $day.addClass('is-selected');

            // Trigger custom event
            $calendar.trigger('hph-calendar:day-selected', [date, $day]);

            // If there are events, you might want to show them in a sidebar or modal
            const events = $day.find('.hph-calendar-event');
            if (events.length > 0) {
                HphCalendar.showDayEvents($calendar, date, events);
            }
        },

        /**
         * Handle event click
         */
        handleEventClick: function($calendar, $event) {
            const eventId = $event.data('event-id');
            
            // Trigger custom event
            $calendar.trigger('hph-calendar:event-clicked', [eventId, $event]);

            // You can customize this behavior
            // For now, let's find and follow the event link
            const eventLink = $event.find('a').first();
            if (eventLink.length) {
                window.location.href = eventLink.attr('href');
            } else {
                // If no link, maybe open in a modal or show details
                HphCalendar.showEventDetails($calendar, eventId);
            }
        },

        /**
         * Handle "more events" button click
         */
        handleMoreEventsClick: function($calendar, $button) {
            const date = $button.data('date');
            
            // You can customize this to show a modal, dropdown, or navigate to day view
            HphCalendar.showMoreEventsModal($calendar, date);
        },

        /**
         * Load calendar data via AJAX
         */
        loadCalendarData: function($calendar, params) {
            if (HphCalendar.isLoading($calendar)) {
                return;
            }

            HphCalendar.setLoading($calendar, true);

            // Prepare AJAX data
            const ajaxData = {
                action: 'hph_load_calendar_data',
                nonce: hphCalendar.nonce, // Assume this is localized
                ...params,
                calendar_id: $calendar.attr('id')
            };

            // Make AJAX request
            $.ajax({
                url: hphCalendar.ajaxurl, // Assume this is localized
                type: 'POST',
                data: ajaxData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        HphCalendar.updateCalendarContent($calendar, response.data);
                    } else {
                        HphCalendar.handleError($calendar, response.data);
                    }
                },
                error: function(xhr, status, error) {
                    HphCalendar.handleError($calendar, 'AJAX request failed: ' + error);
                },
                complete: function() {
                    HphCalendar.setLoading($calendar, false);
                }
            });
        },

        /**
         * Update calendar content with new data
         */
        updateCalendarContent: function($calendar, data) {
            // Update header if provided
            if (data.header) {
                $calendar.find('.hph-calendar-header').html(data.header);
            }

            // Update content
            if (data.content) {
                $calendar.find('.hph-calendar-content').html(data.content);
            }

            // Re-initialize view-specific functionality
            HphCalendar.initializeView($calendar);

            // Trigger custom event
            $calendar.trigger('hph-calendar:updated', [data]);
        },

        /**
         * Show day events (can be customized)
         */
        showDayEvents: function($calendar, date, $events) {
            // This is a basic implementation - you can enhance it
            
            // You might want to show events in a sidebar, modal, or tooltip
            // For now, let's trigger a custom event that can be handled externally
            $calendar.trigger('hph-calendar:day-events-requested', [date, $events]);
        },

        /**
         * Show event details (can be customized)
         */
        showEventDetails: function($calendar, eventId) {
            // This is a placeholder - implement according to your needs
            
            // You might want to open a modal, navigate to event page, etc.
            $calendar.trigger('hph-calendar:event-details-requested', [eventId]);
        },

        /**
         * Show more events modal
         */
        showMoreEventsModal: function($calendar, date) {
            // This is a placeholder - implement according to your needs
            
            // You might want to open a modal with all events for the day
            $calendar.trigger('hph-calendar:more-events-requested', [date]);
        },

        /**
         * Initialize month view specific functionality
         */
        initMonthView: function($calendar) {
            // Add any month-specific initialization
            // For example, tooltips, drag and drop, etc.
        },

        /**
         * Initialize list view specific functionality
         */
        initListView: function($calendar) {
            // Add any list-specific initialization
            // For example, infinite scroll, filtering, etc.
        },

        /**
         * Initialize grid view specific functionality
         */
        initGridView: function($calendar) {
            // Add any grid-specific initialization
            // For example, masonry layout, etc.
        },

        /**
         * Set loading state
         */
        setLoading: function($calendar, isLoading) {
            const calendarData = $calendar.data('hph-calendar');
            calendarData.isLoading = isLoading;

            if (isLoading) {
                $calendar.addClass('is-loading');
            } else {
                $calendar.removeClass('is-loading');
            }
        },

        /**
         * Check if calendar is loading
         */
        isLoading: function($calendar) {
            const calendarData = $calendar.data('hph-calendar');
            return calendarData && calendarData.isLoading;
        },

        /**
         * Handle errors
         */
        handleError: function($calendar, error) {
            
            // You can customize error handling
            // For example, show a notification, modal, etc.
            $calendar.trigger('hph-calendar:error', [error]);
        },

        /**
         * Utility: Get calendar instance data
         */
        getCalendarData: function($calendar) {
            return $calendar.data('hph-calendar');
        },

        /**
         * Utility: Refresh calendar
         */
        refresh: function($calendar) {
            const calendarData = HphCalendar.getCalendarData($calendar);
            if (calendarData) {
                HphCalendar.loadCalendarData($calendar, {
                    date: calendarData.currentDate,
                    view: calendarData.currentView
                });
            }
        }
    };

    // Initialize calendars when document is ready
    $(document).ready(function() {
        HphCalendar.init();
    });

    // Make HphCalendar globally available
    window.HphCalendar = HphCalendar;

    // Example of how to handle custom events (you can customize these)
    $(document).on('hph-calendar:day-selected', function(e, date, $day) {
        // Custom handling here
    });

    $(document).on('hph-calendar:event-clicked', function(e, eventId, $event) {
        // Custom handling here
    });

    $(document).on('hph-calendar:updated', function(e, data) {
        // Custom handling here
    });

})(jQuery);