<?php
/**
 * Open Houses Dashboard Page
 * 
 * @package HappyPlaceTheme
 * @subpackage Dashboard
 * 
 * File Location: /wp-content/themes/happy-place/templates/dashboard/dashboard-open-houses.php
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check user capabilities
if (!current_user_can('edit_posts')) {
    wp_die('You do not have permission to access this page.');
}

// Get current user data
$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Get agent ID if available
$agent_id = function_exists('hpt_get_current_user_agent_id') 
    ? hpt_get_current_user_agent_id() 
    : $user_id;
?>

<div class="open-houses-page" id="openHousesPage">
    <!-- Page Header with Stats -->
    <div class="hph-page-header">
        <div class="hph-header-stats">
            <div class="hph-stat-item">
                <div class="hph-stat-icon hph-stat-icon-primary">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="hph-stat-content">
                    <h3 class="hph-stat-value" id="totalOpenHouses">-</h3>
                    <p class="hph-stat-label">Scheduled</p>
                </div>
            </div>
            
            <div class="hph-stat-item">
                <div class="hph-stat-icon hph-stat-icon-success">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="hph-stat-content">
                    <h3 class="hph-stat-value" id="totalVisitors">-</h3>
                    <p class="hph-stat-label">Total Visitors</p>
                </div>
            </div>
            
            <div class="hph-stat-item">
                <div class="hph-stat-icon hph-stat-icon-warning">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                    </svg>
                </div>
                <div class="hph-stat-content">
                    <h3 class="hph-stat-value" id="avgVisitors">-</h3>
                    <p class="hph-stat-label">Avg per Event</p>
                </div>
            </div>
            
            <div class="hph-stat-item">
                <div class="hph-stat-icon hph-stat-icon-info">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="hph-stat-content">
                    <h3 class="hph-stat-value" id="leadsGenerated">-</h3>
                    <p class="hph-stat-label">Leads Generated</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- View Toggle and Controls -->
    <div class="hph-page-controls">
        <div class="hph-controls-left">
            <div class="hph-view-toggle">
                <button class="hph-view-btn active" data-view="calendar" aria-label="Calendar view">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/>
                    </svg>
                    Calendar
                </button>
                <button class="hph-view-btn" data-view="list" aria-label="List view">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M2 3h12a1 1 0 0 1 0 2H2a1 1 0 0 1 0-2zm0 4h12a1 1 0 0 1 0 2H2a1 1 0 0 1 0-2zm0 4h12a1 1 0 0 1 0 2H2a1 1 0 0 1 0-2z"/>
                    </svg>
                    List
                </button>
            </div>
            
            <div class="hph-filter-group">
                <select class="hph-form-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="active">Active</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            
            <div class="hph-filter-group">
                <select class="hph-form-select" id="timeFilter">
                    <option value="all">All Time</option>
                    <option value="upcoming">Upcoming</option>
                    <option value="this_week">This Week</option>
                    <option value="this_month">This Month</option>
                    <option value="past">Past Events</option>
                </select>
            </div>
        </div>
        
        <div class="hph-controls-right">
            <button class="btn btn-outline btn-sm" id="exportOpenHouses">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M8.5 1.5A1.5 1.5 0 0 1 10 0h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h6c-.314.418-.5.937-.5 1.5v6h-2L8 10.5 10.5 8H9V1.5z"/>
                </svg>
                Export
            </button>
            
            <button class="btn btn-primary btn-sm" id="scheduleOpenHouseBtn">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                </svg>
                Schedule Open House
            </button>
        </div>
    </div>
    
    <!-- Content Container -->
    <div class="open-houses-content">
        <!-- Loading State -->
        <div class="loading-container" id="openHousesLoading">
            <div class="loading-hph-spinner">
                <svg class="hph-spinner" width="48" height="48" viewBox="0 0 48 48">
                    <circle cx="24" cy="24" r="20" stroke="var(--hph-primary)" stroke-width="3" fill="none" stroke-dasharray="125.66" stroke-dashoffset="94.245" stroke-linecap="round">
                        <animateTransform attributeName="transform" type="rotate" from="0 24 24" to="360 24 24" dur="1s" repeatCount="indefinite"/>
                    </circle>
                </svg>
            </div>
            <p>Loading open houses...</p>
        </div>
        
        <!-- Empty State -->
        <div class="empty-state" id="emptyState" style="display: none;">
            <div class="empty-icon">
                <svg width="64" height="64" viewBox="0 0 64 64" fill="currentColor" opacity="0.3">
                    <path d="M16 12V6a2 2 0 012-2h28a2 2 0 012 2v6h4a2 2 0 012 2v44a2 2 0 01-2 2H14a2 2 0 01-2-2V14a2 2 0 012-2h2zm4-2v2h24V10H20zm-4 8v40h32V18H16z"/>
                </svg>
            </div>
            <h3 class="empty-title">No open houses scheduled</h3>
            <p class="empty-message">Start by scheduling your first open house event to showcase your listings!</p>
            <button class="btn btn-primary" id="scheduleFirstOpenHouse">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                </svg>
                Schedule Your First Open House
            </button>
        </div>
        
        <!-- Calendar View -->
        <div class="calendar-container" id="calendarContainer" style="display: none;">
            <div class="calendar-header">
                <button class="btn btn-outline btn-sm" id="prevMonth">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                    </svg>
                </button>
                <h2 class="calendar-title" id="calendarTitle">November 2024</h2>
                <button class="btn btn-outline btn-sm" id="nextMonth">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                    </svg>
                </button>
            </div>
            
            <div class="calendar-grid" id="calendarGrid">
                <!-- Calendar will be rendered here -->
            </div>
            
            <div class="calendar-legend">
                <div class="legend-item">
                    <span class="legend-dot legend-dot-scheduled"></span>
                    <span>Scheduled</span>
                </div>
                <div class="legend-item">
                    <span class="legend-dot legend-dot-active"></span>
                    <span>Active Today</span>
                </div>
                <div class="legend-item">
                    <span class="legend-dot legend-dot-completed"></span>
                    <span>Completed</span>
                </div>
            </div>
        </div>
        
        <!-- List View -->
        <div class="open-houses-list" id="openHousesList" style="display: none;">
            <!-- Open houses will be rendered here -->
        </div>
    </div>
</div>

<!-- Open House Details Modal -->
<div class="modal" id="openHouseDetailsModal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h2 class="modal-title">Open House Details</h2>
            <button class="modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body" id="openHouseDetailsContent">
            <!-- Details will be loaded here -->
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" data-dismiss="modal">Close</button>
            <button class="btn btn-primary" id="editOpenHouseBtn">Edit</button>
        </div>
    </div>
</div>

<!-- Schedule/Edit Open House Modal -->
<div class="modal" id="openHouseFormModal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h2 class="modal-title" id="openHouseFormTitle">Schedule Open House</h2>
            <button class="modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="openHouseForm" class="open-house-form">
                <input type="hidden" id="openHouseId" name="open_house_id" value="">
                
                <!-- Listing Selection -->
                <div class="hph-form-section">
                    <h3 class="hph-section-title">Property</h3>
                    <div class="hph-form-group">
                        <label for="listingSelect" class="hph-form-label required">Select Listing</label>
                        <select id="listingSelect" name="listing_id" class="hph-form-select" required>
                            <option value="">Choose a listing...</option>
                            <!-- Listings will be loaded dynamically -->
                        </select>
                    </div>
                    
                    <div class="selected-listing" id="selectedListing" style="display: none;">
                        <!-- Selected listing details will appear here -->
                    </div>
                </div>
                
                <!-- Event Details -->
                <div class="hph-form-section">
                    <h3 class="hph-section-title">Event Details</h3>
                    <div class="form-row">
                        <div class="hph-form-group">
                            <label for="eventDate" class="hph-form-label required">Date</label>
                            <input type="date" id="eventDate" name="event_date" class="hph-form-control" required>
                        </div>
                        <div class="hph-form-group">
                            <label for="eventStatus" class="hph-form-label">Status</label>
                            <select id="eventStatus" name="status" class="hph-form-select">
                                <option value="scheduled">Scheduled</option>
                                <option value="active">Active</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="hph-form-group">
                            <label for="startTime" class="hph-form-label required">Start Time</label>
                            <input type="time" id="startTime" name="start_time" class="hph-form-control" required>
                        </div>
                        <div class="hph-form-group">
                            <label for="endTime" class="hph-form-label required">End Time</label>
                            <input type="time" id="endTime" name="end_time" class="hph-form-control" required>
                        </div>
                    </div>
                </div>
                
                <!-- Event Information -->
                <div class="hph-form-section">
                    <h3 class="hph-section-title">Event Information</h3>
                    <div class="hph-form-group">
                        <label for="eventTitle" class="hph-form-label">Custom Title</label>
                        <input type="text" id="eventTitle" name="title" class="hph-form-control" 
                               placeholder="Leave blank to auto-generate from listing">
                    </div>
                    
                    <div class="hph-form-group">
                        <label for="eventDescription" class="hph-form-label">Description</label>
                        <textarea id="eventDescription" name="description" class="hph-form-control" rows="4" 
                                  placeholder="Add special instructions, highlights, or promotional information..."></textarea>
                    </div>
                </div>
                
                <!-- Marketing Options -->
                <div class="hph-form-section">
                    <h3 class="hph-section-title">Marketing & Promotion</h3>
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="promote_online" value="1" checked>
                            <span class="checkmark"></span>
                            Promote on website and listings
                        </label>
                        
                        <label class="checkbox-label">
                            <input type="checkbox" name="send_notifications" value="1" checked>
                            <span class="checkmark"></span>
                            Send email notifications to leads
                        </label>
                        
                        <label class="checkbox-label">
                            <input type="checkbox" name="social_media" value="1">
                            <span class="checkmark"></span>
                            Share on social media
                        </label>
                        
                        <label class="checkbox-label">
                            <input type="checkbox" name="generate_flyer" value="1">
                            <span class="checkmark"></span>
                            Generate promotional flyer
                        </label>
                    </div>
                </div>
                
                <!-- Additional Settings -->
                <div class="hph-form-section">
                    <h3 class="hph-section-title">Additional Settings</h3>
                    <div class="form-row">
                        <div class="hph-form-group">
                            <label for="maxVisitors" class="hph-form-label">Expected Visitors</label>
                            <input type="number" id="maxVisitors" name="max_visitors" class="hph-form-control" 
                                   min="1" placeholder="Optional">
                        </div>
                        <div class="hph-form-group">
                            <label for="reminderTime" class="hph-form-label">Reminder</label>
                            <select id="reminderTime" name="reminder_time" class="hph-form-select">
                                <option value="">No reminder</option>
                                <option value="1_hour">1 hour before</option>
                                <option value="2_hours">2 hours before</option>
                                <option value="1_day">1 day before</option>
                                <option value="2_days">2 days before</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="hph-form-group">
                        <label for="specialInstructions" class="hph-form-label">Special Instructions</label>
                        <textarea id="specialInstructions" name="special_instructions" class="hph-form-control" rows="3" 
                                  placeholder="Parking instructions, access codes, preparation notes..."></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" data-dismiss="modal">Cancel</button>
            <button type="submit" form="openHouseForm" class="btn btn-primary" id="saveOpenHouseBtn">
                <span class="btn-text">Schedule Event</span>
                <span class="btn-loading" style="display: none;">
                    <svg class="hph-hph-spinner-sm" width="16" height="16" viewBox="0 0 16 16">
                        <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="2" fill="none" stroke-dasharray="37.7" stroke-dashoffset="28.275" stroke-linecap="round">
                            <animateTransform attributeName="transform" type="rotate" from="0 8 8" to="360 8 8" dur="1s" repeatCount="indefinite"/>
                        </circle>
                    </svg>
                    Saving...
                </span>
            </button>
        </div>
    </div>
</div>

<!-- Visitor Registration Modal -->
<div class="modal" id="visitorRegistrationModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Register Visitor</h2>
            <button class="modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="visitorForm">
                <input type="hidden" id="visitorOpenHouseId" name="open_house_id">
                
                <div class="hph-form-group">
                    <label for="visitorName" class="hph-form-label required">Name</label>
                    <input type="text" id="visitorName" name="name" class="hph-form-control" required>
                </div>
                
                <div class="hph-form-group">
                    <label for="visitorEmail" class="hph-form-label">Email</label>
                    <input type="email" id="visitorEmail" name="email" class="hph-form-control">
                </div>
                
                <div class="hph-form-group">
                    <label for="visitorPhone" class="hph-form-label">Phone</label>
                    <input type="tel" id="visitorPhone" name="phone" class="hph-form-control">
                </div>
                
                <div class="hph-form-group">
                    <label for="visitorInterest" class="hph-form-label">Interest Level</label>
                    <select id="visitorInterest" name="interest_level" class="hph-form-select">
                        <option value="browsing">Just browsing</option>
                        <option value="interested">Interested</option>
                        <option value="very_interested">Very interested</option>
                        <option value="ready_to_buy">Ready to buy</option>
                    </select>
                </div>
                
                <div class="hph-form-group">
                    <label for="visitorNotes" class="hph-form-label">Notes</label>
                    <textarea id="visitorNotes" name="notes" class="hph-form-control" rows="3" 
                              placeholder="Additional information or comments..."></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" data-dismiss="modal">Cancel</button>
            <button type="submit" form="visitorForm" class="btn btn-primary">Register Visitor</button>
        </div>
    </div>
</div>
