<?php
/**
 * Dashboard Open Houses Management Section
 *
 * @package HappyPlace
 */

if (!defined('ABSPATH')) {
    exit;
}

$current_user_id = get_current_user_id();
$current_user = wp_get_current_user();
$user_roles = $current_user->roles ?? [];
$user_role = !empty($user_roles) ? $user_roles[0] : '';
$can_create_open_houses = current_user_can('manage_options') || current_user_can('manage_listings') || in_array('agent', $user_roles) || in_array('staff', $user_roles);

// Add nonce field for AJAX requests
wp_nonce_field('hph_dashboard_nonce', 'hph_dashboard_nonce', false);

// Get available agents from agent posts
$agent_posts = get_posts([
    'post_type' => 'agent',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC'
]);

// Also get WordPress users with agent role as fallback
$agent_users = get_users(['role__in' => ['agent', 'administrator']]);
?>

<div class="hph-open-houses-management">
    <!-- Header Section -->
    <div class="hph-section-header hph-flex hph-flex-row hph-justify-between hph-items-center hph-mb-lg">
        <div>
            <h2 class="hph-text-2xl hph-font-bold hph-text-gray-800 hph-mb-xs">Open Houses</h2>
            <p class="hph-text-sm hph-text-gray-600">Schedule and manage property open houses</p>
        </div>
        
        <?php if ($can_create_open_houses): ?>
        <div class="hph-section-actions">
            <button 
                type="button" 
                class="hph-btn hph-btn-primary" 
                data-action="create-open-house"
                data-bs-toggle="modal" 
                data-bs-target="#openHouseModal">
                <i class="fas fa-plus hph-mr-xs"></i>
                Schedule Open House
            </button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Filters & Actions -->
    <div class="hph-bg-white hph-border hph-border-gray-200 hph-rounded-lg hph-p-lg hph-mb-lg">
        <div class="hph-filters-grid hph-grid hph-grid-cols-12 hph-gap-md hph-mb-md">
            <!-- Status Filter -->
            <div class="hph-col-span-12 md:hph-col-span-3">
                <label class="hph-form-label">Status</label>
                <select id="open-house-status-filter" class="hph-form-select">
                    <option value="">All Status</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="active">Active</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <!-- Date Range Filter -->
            <div class="hph-col-span-12 md:hph-col-span-3">
                <label class="hph-form-label">Date From</label>
                <input type="date" id="open-house-date-from" class="hph-form-input" />
            </div>
            
            <div class="hph-col-span-12 md:hph-col-span-3">
                <label class="hph-form-label">Date To</label>
                <input type="date" id="open-house-date-to" class="hph-form-input" />
            </div>

            <!-- Agent Filter (Admin/Staff only) -->
            <?php if (in_array($user_role, ['administrator', 'staff'])): ?>
            <div class="hph-col-span-12 md:hph-col-span-3">
                <label class="hph-form-label">Agent</label>
                <select id="open-house-agent-filter" class="hph-form-select">
                    <option value="">All Agents</option>
                    <?php
                    // Show agent posts first
                    foreach ($agent_posts as $agent_post):
                        $agent_email = get_field('email', $agent_post->ID);
                    ?>
                        <option value="agent_<?php echo esc_attr($agent_post->ID); ?>">
                            <?php echo esc_html($agent_post->post_title); ?>
                            <?php if ($agent_email): ?> (<?php echo esc_html($agent_email); ?>)<?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                    
                    <?php if (!empty($agent_posts) && !empty($agent_users)): ?>
                        <option disabled>─── WordPress Users ───</option>
                    <?php endif; ?>
                    
                    <?php
                    // Then show WordPress users
                    foreach ($agent_users as $agent_user):
                    ?>
                        <option value="user_<?php echo esc_attr($agent_user->ID); ?>">
                            <?php echo esc_html($agent_user->display_name); ?>
                            (<?php echo esc_html($agent_user->user_email); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
        </div>

        <!-- Search and Actions Row -->
        <div class="hph-flex hph-flex-row hph-justify-between hph-items-center">
            <div class="hph-search-box hph-flex-grow hph-max-w-md">
                <div class="hph-relative">
                    <input 
                        type="text" 
                        id="open-house-search" 
                        class="hph-form-input hph-pl-10" 
                        placeholder="Search open houses..."
                    />
                    <i class="fas fa-search hph-absolute hph-left-3 hph-top-1/2 hph-transform -hph-translate-y-1/2 hph-text-gray-400"></i>
                </div>
            </div>

            <div class="hph-actions-group hph-flex hph-flex-row hph-gap-sm">
                <button type="button" id="apply-open-house-filters" class="hph-btn hph-btn-secondary">
                    <i class="fas fa-filter hph-mr-xs"></i>
                    Apply Filters
                </button>
                
                <button type="button" id="clear-open-house-filters" class="hph-btn hph-btn-outline-primary">
                    Clear
                </button>
            </div>
        </div>

        <!-- Bulk Actions (for agents and admins) -->
        <?php if ($can_create_open_houses): ?>
        <div class="hph-bulk-actions hph-mt-md hph-p-md hph-bg-gray-50 hph-rounded-md" style="display: none;">
            <div class="hph-flex hph-flex-row hph-items-center hph-gap-md">
                <span class="hph-text-sm hph-text-gray-600">
                    <span id="selected-count">0</span> open house(s) selected
                </span>
                
                <select id="bulk-action-select" class="hph-form-select hph-w-auto">
                    <option value="">Bulk Actions</option>
                    <option value="cancel">Cancel</option>
                    <option value="duplicate">Duplicate</option>
                    <option value="export">Export RSVP List</option>
                    <option value="delete">Delete</option>
                </select>
                
                <button type="button" id="apply-bulk-action" class="hph-btn hph-btn-secondary hph-btn-sm">
                    Apply
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Open Houses Table -->
    <div class="hph-bg-white hph-border hph-border-gray-200 hph-rounded-lg hph-overflow-hidden">
        <div class="hph-table-responsive">
            <table class="hph-table hph-table-hover" id="open-houses-table">
                <thead class="hph-bg-gray-50">
                    <tr>
                        <?php if ($can_create_open_houses): ?>
                        <th class="hph-w-8">
                            <input 
                                type="checkbox" 
                                id="select-all-open-houses" 
                                class="hph-form-checkbox"
                            />
                        </th>
                        <?php endif; ?>
                        <th>Property</th>
                        <th>Date & Time</th>
                        <th>Agent</th>
                        <th>RSVPs</th>
                        <th>Status</th>
                        <th class="hph-text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="open-houses-tbody">
                    <!-- Content will be loaded via AJAX -->
                    <tr>
                        <td colspan="<?php echo $can_create_open_houses ? '7' : '6'; ?>" class="hph-text-center hph-py-12">
                            <div class="hph-loading-spinner">
                                <i class="fas fa-spinner fa-spin hph-text-gray-400 hph-text-2xl"></i>
                                <p class="hph-text-gray-500 hph-mt-2">Loading open houses...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="hph-table-pagination hph-border-t hph-border-gray-200 hph-px-lg hph-py-md">
            <div class="hph-flex hph-flex-row hph-justify-between hph-items-center">
                <div class="hph-text-sm hph-text-gray-600">
                    Showing <span id="showing-from">0</span> to <span id="showing-to">0</span> of <span id="total-count">0</span> open houses
                </div>
                
                <div class="hph-flex hph-flex-row hph-gap-sm">
                    <button type="button" id="prev-page" class="hph-btn hph-btn-outline-primary hph-btn-sm" disabled>
                        <i class="fas fa-chevron-left"></i>
                        Previous
                    </button>
                    
                    <span class="hph-text-sm hph-text-gray-600 hph-px-md hph-py-2">
                        Page <span id="current-page">1</span> of <span id="total-pages">1</span>
                    </span>
                    
                    <button type="button" id="next-page" class="hph-btn hph-btn-outline-primary hph-btn-sm" disabled>
                        Next
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Open House Modal -->
<div class="modal fade" id="openHouseModal" tabindex="-1" aria-labelledby="openHouseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="openHouseModalLabel">Schedule Open House</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="open-house-form">
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Property Selection -->
                        <div class="col-12">
                            <label for="oh-listing-id" class="form-label">Property <span class="text-danger">*</span></label>
                            <select id="oh-listing-id" name="listing_id" class="hph-form-select" required>
                                <option value="">Select a property...</option>
                                <?php
                                $listings_args = [
                                    'post_type' => 'listing',
                                    'post_status' => 'publish',
                                    'posts_per_page' => -1,
                                    'orderby' => 'title',
                                    'order' => 'ASC'
                                ];
                                
                                // If agent role, only show their listings
                                if ($user_role === 'agent') {
                                    $listings_args['author'] = $current_user_id;
                                }
                                
                                $listings = get_posts($listings_args);
                                foreach ($listings as $listing):
                                    $address = get_post_meta($listing->ID, 'street_address', true);
                                ?>
                                    <option value="<?php echo esc_attr($listing->ID); ?>">
                                        <?php echo esc_html($listing->post_title); ?>
                                        <?php if ($address): ?> - <?php echo esc_html($address); ?><?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Title -->
                        <div class="col-12">
                            <label for="oh-title" class="form-label">Open House Title <span class="text-danger">*</span></label>
                            <input type="text" id="oh-title" name="title" class="hph-form-input" required 
                                   placeholder="e.g., Weekend Open House - Beautiful Family Home">
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label for="oh-description" class="form-label">Description</label>
                            <textarea id="oh-description" name="description" class="hph-form-textarea" rows="3"
                                      placeholder="Additional details about the open house..."></textarea>
                        </div>

                        <!-- Date Selection -->
                        <div class="col-md-6">
                            <label for="oh-event-date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" id="oh-event-date" name="event_date" class="hph-form-input" required
                                   min="<?php echo date('Y-m-d'); ?>">
                            <small class="form-text text-muted">Select the date for your open house</small>
                        </div>
                        
                        <!-- Hosting Agent Selection -->
                        <div class="col-md-6">
                            <label for="oh-hosting-agent" class="form-label">Hosting Agent <span class="text-danger">*</span></label>
                            <select id="oh-hosting-agent" name="hosting_agent" class="hph-form-select" required>
                                <option value="">Select hosting agent...</option>
                                <?php 
                                // First add agent posts
                                foreach ($agent_posts as $agent_post):
                                    $agent_email = get_field('email', $agent_post->ID);
                                    $agent_phone = get_field('phone', $agent_post->ID);
                                ?>
                                    <option value="agent_<?php echo esc_attr($agent_post->ID); ?>">
                                        <?php echo esc_html($agent_post->post_title); ?>
                                        <?php if ($agent_email): ?> (<?php echo esc_html($agent_email); ?>)<?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                                
                                <?php if (!empty($agent_posts) && !empty($agent_users)): ?>
                                    <option disabled>── WordPress Users ──</option>
                                <?php endif; ?>
                                
                                <?php 
                                // Then add WordPress users
                                foreach ($agent_users as $agent_user):
                                ?>
                                    <option value="user_<?php echo esc_attr($agent_user->ID); ?>"
                                            <?php selected($agent_user->ID, $current_user_id); ?>>
                                        <?php echo esc_html($agent_user->display_name); ?>
                                        (<?php echo esc_html($agent_user->user_email); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Agent who will host this open house</small>
                        </div>

                        <!-- Simplified Time Selection -->
                        <div class="col-md-6">
                            <label for="oh-start-time" class="form-label">Start Time <span class="text-danger">*</span></label>
                            <select id="oh-start-time" name="start_time" class="hph-form-select" required>
                                <option value="">Select start time...</option>
                                <option value="09:00">9:00 AM</option>
                                <option value="09:30">9:30 AM</option>
                                <option value="10:00">10:00 AM</option>
                                <option value="10:30">10:30 AM</option>
                                <option value="11:00">11:00 AM</option>
                                <option value="11:30">11:30 AM</option>
                                <option value="12:00">12:00 PM</option>
                                <option value="12:30">12:30 PM</option>
                                <option value="13:00">1:00 PM</option>
                                <option value="13:30">1:30 PM</option>
                                <option value="14:00">2:00 PM</option>
                                <option value="14:30">2:30 PM</option>
                                <option value="15:00">3:00 PM</option>
                                <option value="15:30">3:30 PM</option>
                                <option value="16:00">4:00 PM</option>
                                <option value="16:30">4:30 PM</option>
                                <option value="17:00">5:00 PM</option>
                                <option value="17:30">5:30 PM</option>
                                <option value="18:00">6:00 PM</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="oh-end-time" class="form-label">End Time <span class="text-danger">*</span></label>
                            <select id="oh-end-time" name="end_time" class="hph-form-select" required>
                                <option value="">Select end time...</option>
                                <option value="10:00">10:00 AM</option>
                                <option value="10:30">10:30 AM</option>
                                <option value="11:00">11:00 AM</option>
                                <option value="11:30">11:30 AM</option>
                                <option value="12:00">12:00 PM</option>
                                <option value="12:30">12:30 PM</option>
                                <option value="13:00">1:00 PM</option>
                                <option value="13:30">1:30 PM</option>
                                <option value="14:00">2:00 PM</option>
                                <option value="14:30">2:30 PM</option>
                                <option value="15:00">3:00 PM</option>
                                <option value="15:30">3:30 PM</option>
                                <option value="16:00">4:00 PM</option>
                                <option value="16:30">4:30 PM</option>
                                <option value="17:00">5:00 PM</option>
                                <option value="17:30">5:30 PM</option>
                                <option value="18:00">6:00 PM</option>
                                <option value="18:30">6:30 PM</option>
                                <option value="19:00">7:00 PM</option>
                                <option value="19:30">7:30 PM</option>
                                <option value="20:00">8:00 PM</option>
                            </select>
                        </div>

                        <!-- Contact Information -->
                        <div class="col-md-6">
                            <label for="oh-contact-phone" class="form-label">Contact Phone</label>
                            <input type="tel" id="oh-contact-phone" name="contact_phone" class="hph-form-input"
                                   placeholder="(555) 123-4567">
                            <small class="form-text text-muted">Phone number for inquiries</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="oh-contact-email" class="form-label">Contact Email</label>
                            <input type="email" id="oh-contact-email" name="contact_email" class="hph-form-input"
                                   value="<?php echo esc_attr($current_user->user_email); ?>"
                                   placeholder="agent@example.com">
                            <small class="form-text text-muted">Email for RSVP notifications</small>
                        </div>

                        <!-- Settings -->
                        <div class="col-md-6">
                            <label for="oh-max-visitors" class="form-label">Max Visitors (0 = unlimited)</label>
                            <input type="number" id="oh-max-visitors" name="max_visitors" class="hph-form-input" 
                                   min="0" value="0">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="oh-timezone" class="form-label">Timezone</label>
                            <select id="oh-timezone" name="timezone" class="hph-form-select">
                                <option value="America/New_York">Eastern Time (EST/EDT)</option>
                                <option value="America/Chicago">Central Time (CST/CDT)</option>
                                <option value="America/Denver">Mountain Time (MST/MDT)</option>
                                <option value="America/Los_Angeles">Pacific Time (PST/PDT)</option>
                            </select>
                        </div>

                        <!-- Options -->
                        <div class="col-12">
                            <div class="hph-form-check">
                                <input class="hph-form-check-input" type="checkbox" id="oh-require-registration" 
                                       name="require_registration" value="1" checked>
                                <label class="hph-form-check-label" for="oh-require-registration">
                                    Require RSVP Registration
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="hph-form-check">
                                <input class="hph-form-check-input" type="checkbox" id="oh-public-visibility" 
                                       name="public_visibility" value="1" checked>
                                <label class="hph-form-check-label" for="oh-public-visibility">
                                    Show publicly on website
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="hph-form-check">
                                <input class="hph-form-check-input" type="checkbox" id="oh-send-reminders" 
                                       name="send_reminders" value="1" checked>
                                <label class="hph-form-check-label" for="oh-send-reminders">
                                    Send reminder emails to attendees
                                </label>
                            </div>
                        </div>

                        <!-- Special Instructions -->
                        <div class="col-12">
                            <label for="oh-special-instructions" class="form-label">Special Instructions</label>
                            <textarea id="oh-special-instructions" name="special_instructions" class="hph-form-textarea" rows="2"
                                      placeholder="Parking instructions, entry requirements, etc."></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="hph-btn hph-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="hph-btn hph-btn-primary">
                        <span class="hph-btn-text">Schedule Open House</span>
                        <span class="hph-btn-loading d-none">
                            <span class="spinner-border spinner-border-sm me-2"></span>
                            Scheduling...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- RSVP List Modal -->
<div class="modal fade" id="rsvpListModal" tabindex="-1" aria-labelledby="rsvpListModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rsvpListModalLabel">RSVP List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="rsvp-list-content">
                    <!-- Content loaded via AJAX -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="hph-btn hph-btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="hph-btn hph-btn-primary" id="export-rsvp-list">
                    <i class="fas fa-download me-1"></i>
                    Export to CSV
                </button>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Auto-update end time when start time changes
    $('#oh-start-time').on('change', function() {
        const startTime = $(this).val();
        const $endTime = $('#oh-end-time');
        
        if (startTime) {
            // Clear current selection
            $endTime.val('');
            
            // Enable only times after start time
            $endTime.find('option').each(function() {
                const optionValue = $(this).val();
                if (optionValue && optionValue <= startTime) {
                    $(this).prop('disabled', true);
                } else {
                    $(this).prop('disabled', false);
                }
            });
            
            // Auto-select a reasonable end time (2 hours later)
            const startHour = parseInt(startTime.split(':')[0]);
            const startMinute = parseInt(startTime.split(':')[1]);
            const endHour = startHour + 2;
            const endMinute = startMinute;
            const suggestedEndTime = String(endHour).padStart(2, '0') + ':' + String(endMinute).padStart(2, '0');
            
            if ($endTime.find('option[value="' + suggestedEndTime + '"]').length) {
                $endTime.val(suggestedEndTime);
            }
        } else {
            // Re-enable all options if no start time selected
            $endTime.find('option').prop('disabled', false);
        }
    });
    
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    $('#oh-event-date').attr('min', today);
    
    // Auto-populate contact info when hosting agent changes
    $('#oh-hosting-agent').on('change', function() {
        const selectedValue = $(this).val();
        
        if (selectedValue.startsWith('agent_')) {
            // Agent post selected - could fetch via AJAX if needed
            console.log('Agent post selected:', selectedValue);
        } else if (selectedValue.startsWith('user_')) {
            // WordPress user selected
            console.log('WordPress user selected:', selectedValue);
        }
    });
    
    // Form validation and submission
    $('#open-house-form').on('submit', function(e) {
        e.preventDefault();
        
        const startTime = $('#oh-start-time').val();
        const endTime = $('#oh-end-time').val();
        
        if (startTime && endTime && endTime <= startTime) {
            alert('End time must be after start time.');
            return false;
        }
        
        const eventDate = $('#oh-event-date').val();
        const today = new Date().toISOString().split('T')[0];
        
        if (eventDate && eventDate < today) {
            alert('Event date cannot be in the past.');
            return false;
        }
        
        // Show loading state
        const $submitBtn = $(this).find('button[type="submit"]');
        const $btnText = $submitBtn.find('.btn-text');
        const $btnLoading = $submitBtn.find('.btn-loading');
        
        $btnText.addClass('d-none');
        $btnLoading.removeClass('d-none');
        $submitBtn.prop('disabled', true);
        
        // Prepare form data
        const formData = new FormData(this);
        formData.append('action', 'hph_create_open_house');
        formData.append('nonce', $('#hph_dashboard_nonce').val());
        
        // Submit via AJAX
        $.ajax({
            url: '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Show success message
                    alert(response.data.message || 'Open house created successfully!');
                    
                    // Close modal
                    $('#openHouseModal').modal('hide');
                    
                    // Reset form
                    $('#open-house-form')[0].reset();
                    
                    // Reload open houses table
                    if (typeof loadOpenHouses === 'function') {
                        loadOpenHouses();
                    } else {
                        // Fallback: reload page
                        window.location.reload();
                    }
                } else {
                    alert(response.data || 'Error creating open house. Please try again.');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                alert('Network error. Please check your connection and try again.');
            },
            complete: function() {
                // Reset button state
                $btnText.removeClass('d-none');
                $btnLoading.addClass('d-none');
                $submitBtn.prop('disabled', false);
            }
        });
    });
    
    // Load open houses table
    function loadOpenHouses(page = 1) {
        const filters = {
            page: page,
            per_page: 20,
            search: $('#open-house-search').val(),
            status: $('#open-house-status-filter').val(),
            agent: $('#open-house-agent-filter').val(),
            date_from: $('#open-house-date-from').val(),
            date_to: $('#open-house-date-to').val(),
            action: 'hph_get_open_houses',
            nonce: $('#hph_dashboard_nonce').val()
        };
        
        // Show loading
        $('#open-houses-tbody').html(`
            <tr>
                <td colspan="7" class="hph-text-center hph-py-12">
                    <div class="hph-loading-spinner">
                        <i class="fas fa-spinner fa-spin hph-text-gray-400 hph-text-2xl"></i>
                        <p class="hph-text-gray-500 hph-mt-2">Loading open houses...</p>
                    </div>
                </td>
            </tr>
        `);
        
        $.ajax({
            url: '/wp-admin/admin-ajax.php',
            type: 'GET',
            data: filters,
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    
                    // Update table
                    if (data.html) {
                        $('#open-houses-tbody').html(data.html);
                    } else {
                        $('#open-houses-tbody').html(`
                            <tr>
                                <td colspan="7" class="hph-text-center hph-py-8">
                                    <p class="hph-text-gray-500">No open houses found.</p>
                                </td>
                            </tr>
                        `);
                    }
                    
                    // Update pagination
                    updatePagination(data.current_page, data.pages, data.total);
                    
                    // Update counters
                    updateCounters(data.total);
                    
                } else {
                    $('#open-houses-tbody').html(`
                        <tr>
                            <td colspan="7" class="hph-text-center hph-py-8">
                                <p class="hph-text-red-500">Error loading open houses: ${response.data || 'Unknown error'}</p>
                            </td>
                        </tr>
                    `);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading open houses:', error);
                $('#open-houses-tbody').html(`
                    <tr>
                        <td colspan="7" class="hph-text-center hph-py-8">
                            <p class="hph-text-red-500">Network error. Please refresh the page.</p>
                        </td>
                    </tr>
                `);
            }
        });
    }
    
    function updatePagination(currentPage, totalPages, totalCount) {
        $('#current-page').text(currentPage);
        $('#total-pages').text(totalPages);
        $('#total-count').text(totalCount);
        
        const showingFrom = totalCount > 0 ? ((currentPage - 1) * 20) + 1 : 0;
        const showingTo = Math.min(currentPage * 20, totalCount);
        
        $('#showing-from').text(showingFrom);
        $('#showing-to').text(showingTo);
        
        // Update pagination buttons
        $('#prev-page').prop('disabled', currentPage <= 1);
        $('#next-page').prop('disabled', currentPage >= totalPages);
    }
    
    function updateCounters(total) {
        // Update any counter displays
        $('.hph-open-houses-count').text(total);
    }
    
    // Event handlers for filters and actions
    $('#apply-open-house-filters').on('click', function() {
        loadOpenHouses(1);
    });
    
    $('#clear-open-house-filters').on('click', function() {
        $('#open-house-search').val('');
        $('#open-house-status-filter').val('');
        $('#open-house-agent-filter').val('');
        $('#open-house-date-from').val('');
        $('#open-house-date-to').val('');
        loadOpenHouses(1);
    });
    
    // Pagination handlers
    $('#prev-page').on('click', function() {
        if (!$(this).prop('disabled')) {
            const currentPage = parseInt($('#current-page').text());
            loadOpenHouses(currentPage - 1);
        }
    });
    
    $('#next-page').on('click', function() {
        if (!$(this).prop('disabled')) {
            const currentPage = parseInt($('#current-page').text());
            loadOpenHouses(currentPage + 1);
        }
    });
    
    // Action handlers
    $(document).on('click', '[data-action="delete"]', function() {
        const openHouseId = $(this).data('open-house-id');
        
        if (confirm('Are you sure you want to delete this open house? This action cannot be undone.')) {
            $.ajax({
                url: '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'hph_delete_open_house',
                    open_house_id: openHouseId,
                    nonce: $('#hph_dashboard_nonce').val()
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message || 'Open house deleted successfully');
                        loadOpenHouses();
                    } else {
                        alert(response.data || 'Error deleting open house');
                    }
                },
                error: function() {
                    alert('Network error. Please try again.');
                }
            });
        }
    });
    
    $(document).on('click', '[data-action="duplicate"]', function() {
        const openHouseId = $(this).data('open-house-id');
        
        $.ajax({
            url: '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: {
                action: 'hph_duplicate_open_house',
                open_house_id: openHouseId,
                nonce: $('#hph_dashboard_nonce').val()
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message || 'Open house duplicated successfully');
                    if (response.data.edit_url) {
                        if (confirm('Would you like to edit the duplicated open house now?')) {
                            window.open(response.data.edit_url, '_blank');
                        }
                    }
                    loadOpenHouses();
                } else {
                    alert(response.data || 'Error duplicating open house');
                }
            },
            error: function() {
                alert('Network error. Please try again.');
            }
        });
    });
    
    $(document).on('click', '[data-action="view-rsvps"]', function() {
        const openHouseId = $(this).data('open-house-id');
        
        $.ajax({
            url: '/wp-admin/admin-ajax.php',
            type: 'GET',
            data: {
                action: 'hph_get_open_house_rsvps',
                open_house_id: openHouseId,
                nonce: $('#hph_dashboard_nonce').val()
            },
            success: function(response) {
                if (response.success) {
                    const rsvps = response.data.rsvps;
                    let rsvpHtml = '';
                    
                    if (rsvps && rsvps.length > 0) {
                        rsvpHtml = '<div class="table-responsive"><table class="table table-striped"><thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Party Size</th><th>RSVP Date</th></tr></thead><tbody>';
                        rsvps.forEach(function(rsvp) {
                            rsvpHtml += `<tr>
                                <td>${rsvp.first_name} ${rsvp.last_name}</td>
                                <td>${rsvp.email}</td>
                                <td>${rsvp.phone || 'N/A'}</td>
                                <td>${rsvp.party_size}</td>
                                <td>${new Date(rsvp.rsvp_date).toLocaleDateString()}</td>
                            </tr>`;
                        });
                        rsvpHtml += '</tbody></table></div>';
                    } else {
                        rsvpHtml = '<p class="text-muted">No RSVPs yet for this open house.</p>';
                    }
                    
                    $('#rsvp-list-content').html(rsvpHtml);
                    $('#rsvpListModal').modal('show');
                } else {
                    alert(response.data || 'Error loading RSVPs');
                }
            },
            error: function() {
                alert('Network error. Please try again.');
            }
        });
    });
    
    // Bulk actions
    $(document).on('change', '#select-all-open-houses', function() {
        $('.hph-open-house-checkbox').prop('checked', $(this).is(':checked'));
        updateBulkActions();
    });
    
    $(document).on('change', '.hph-open-house-checkbox', function() {
        updateBulkActions();
    });
    
    function updateBulkActions() {
        const checkedCount = $('.hph-open-house-checkbox:checked').length;
        $('#selected-count').text(checkedCount);
        
        if (checkedCount > 0) {
            $('.hph-bulk-actions').show();
        } else {
            $('.hph-bulk-actions').hide();
        }
    }
    
    $('#apply-bulk-action').on('click', function() {
        const action = $('#bulk-action-select').val();
        const selectedIds = $('.hph-open-house-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
        
        if (!action || selectedIds.length === 0) {
            alert('Please select an action and at least one open house.');
            return;
        }
        
        if (action === 'delete' && !confirm(`Are you sure you want to delete ${selectedIds.length} open house(s)? This action cannot be undone.`)) {
            return;
        }
        
        $.ajax({
            url: '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: {
                action: 'hph_bulk_open_house_actions',
                action_type: action,
                open_house_ids: selectedIds,
                nonce: $('#hph_dashboard_nonce').val()
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message || 'Bulk action completed');
                    $('#bulk-action-select').val('');
                    $('.hph-bulk-actions').hide();
                    loadOpenHouses();
                } else {
                    alert(response.data || 'Error performing bulk action');
                }
            },
            error: function() {
                alert('Network error. Please try again.');
            }
        });
    });
    
    // Search with debouncing
    let searchTimeout;
    $('#open-house-search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            loadOpenHouses(1);
        }, 300);
    });
    
    // Load initial data
    loadOpenHouses(1);
    
    // Make loadOpenHouses globally available
    window.loadOpenHouses = loadOpenHouses;
});
</script>
