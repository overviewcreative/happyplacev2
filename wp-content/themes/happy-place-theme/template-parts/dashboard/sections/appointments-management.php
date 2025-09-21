<?php
/**
 * Appointments Management Section - Calendly Integration
 * Agent dashboard section for managing appointment bookings from hp_appointments table
 * 
 * @package HappyPlaceTheme
 */

// Security check
if (!is_user_logged_in()) {
    return;
}

$current_user = wp_get_current_user();
$user_roles = $current_user->roles;
$is_agent = in_array('agent', $user_roles) || in_array('administrator', $user_roles);

if (!$is_agent) {
    return;
}

global $wpdb;

// Create appointments table if it doesn't exist
$appointments_table = $wpdb->prefix . 'hp_appointments';
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$appointments_table'") === $appointments_table;

if (!$table_exists && class_exists('HappyPlace\\Services\\CalendlyService')) {
    $calendly_service = new \HappyPlace\Services\CalendlyService();
    $calendly_service->create_appointments_table();
}

// Get appointments filters
$status_filter = isset($_GET['appointment_status']) ? sanitize_text_field($_GET['appointment_status']) : 'all';
$search_query = isset($_GET['appointments_search']) ? sanitize_text_field($_GET['appointments_search']) : '';
$type_filter = isset($_GET['appointment_type']) ? sanitize_text_field($_GET['appointment_type']) : '';

// Build query for hp_appointments table
$where_conditions = ['1=1'];
$query_params = [];

// Filter by agent - Show all appointments for administrators
$is_admin = in_array('administrator', $user_roles);
if (!$is_admin) {
    $where_conditions[] = 'agent_id = %d';
    $query_params[] = $current_user->ID;
}

// Filter by status if specified
if ($status_filter !== 'all') {
    $where_conditions[] = 'status = %s';
    $query_params[] = $status_filter;
}

// Filter by type if specified  
if ($type_filter) {
    $where_conditions[] = 'appointment_type = %s';
    $query_params[] = $type_filter;
}

// Search filter
if ($search_query) {
    $where_conditions[] = '(client_name LIKE %s OR client_email LIKE %s OR message LIKE %s)';
    $search_term = '%' . $wpdb->esc_like($search_query) . '%';
    $query_params[] = $search_term;
    $query_params[] = $search_term;
    $query_params[] = $search_term;
}

$where_clause = implode(' AND ', $where_conditions);

// Get total appointments count
$count_query = "SELECT COUNT(*) FROM $appointments_table WHERE $where_clause";
if ($query_params) {
    $count_query = $wpdb->prepare($count_query, ...$query_params);
}
$total_appointments = $wpdb->get_var($count_query);

// Get appointments for current page
$per_page = 20;
$page = max(1, intval($_GET['appointments_page'] ?? 1));
$offset = ($page - 1) * $per_page;

$appointments_query = "SELECT * FROM $appointments_table WHERE $where_clause ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
if ($query_params) {
    $appointments_query = $wpdb->prepare($appointments_query, ...$query_params);
}

$appointments = $wpdb->get_results($appointments_query, ARRAY_A);

// Get status counts for tabs
$status_counts = [];
$status_count_query = "SELECT status, COUNT(*) as count FROM $appointments_table GROUP BY status";
$status_results = $wpdb->get_results($status_count_query, ARRAY_A);
foreach ($status_results as $status) {
    $status_counts[$status['status']] = intval($status['count']);
}

// Calculate pagination
$total_pages = ceil($total_appointments / $per_page);
?>

<div class="dashboard-section" id="appointments-section">
    <div class="section-header">
        <div class="section-title">
            <h2><i class="fas fa-calendar-check"></i> Appointment Management</h2>
            <div class="section-stats">
                <span class="stat-badge">
                    <span class="stat-number"><?php echo number_format($total_appointments); ?></span>
                    <span class="stat-label">Total Appointments</span>
                </span>
            </div>
        </div>
    </div>

    <!-- Filters and Actions -->
    <div class="section-controls">
        <div class="filters-row">
            <!-- Status Tabs -->
            <div class="filter-tabs">
                <a href="?tab=appointments&appointment_status=all" 
                   class="filter-tab <?php echo $status_filter === 'all' ? 'active' : ''; ?>">
                    All <span class="tab-count"><?php echo number_format($total_appointments); ?></span>
                </a>
                <a href="?tab=appointments&appointment_status=scheduled" 
                   class="filter-tab <?php echo $status_filter === 'scheduled' ? 'active' : ''; ?>">
                    Scheduled <span class="tab-count"><?php echo number_format($status_counts['scheduled'] ?? 0); ?></span>
                </a>
                <a href="?tab=appointments&appointment_status=completed" 
                   class="filter-tab <?php echo $status_filter === 'completed' ? 'active' : ''; ?>">
                    Completed <span class="tab-count"><?php echo number_format($status_counts['completed'] ?? 0); ?></span>
                </a>
                <a href="?tab=appointments&appointment_status=canceled" 
                   class="filter-tab <?php echo $status_filter === 'canceled' ? 'active' : ''; ?>">
                    Canceled <span class="tab-count"><?php echo number_format($status_counts['canceled'] ?? 0); ?></span>
                </a>
            </div>
            
            <!-- Search and Filters -->
            <div class="search-filters">
                <form method="GET" class="search-form">
                    <input type="hidden" name="tab" value="appointments">
                    <input type="hidden" name="appointment_status" value="<?php echo esc_attr($status_filter); ?>">
                    
                    <div class="search-group">
                        <input type="text" 
                               name="appointments_search" 
                               value="<?php echo esc_attr($search_query); ?>" 
                               placeholder="Search appointments..."
                               class="search-input">
                        <button type="submit" class="search-button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    
                    <select name="appointment_type" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="showing" <?php selected($type_filter, 'showing'); ?>>Showings</option>
                        <option value="consultation" <?php selected($type_filter, 'consultation'); ?>>Consultations</option>
                        <option value="listing" <?php selected($type_filter, 'listing'); ?>>Listing Appointments</option>
                    </select>
                    
                    <?php if ($search_query || $type_filter) : ?>
                    <a href="?tab=appointments&appointment_status=<?php echo esc_attr($status_filter); ?>" 
                       class="clear-filters">
                        <i class="fas fa-times"></i> Clear
                    </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- Appointments Table -->
    <div class="appointments-table-container">
        <?php if (empty($appointments)) : ?>
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-calendar-times"></i>
            </div>
            <h3>No Appointments Found</h3>
            <p>
                <?php if ($search_query || $type_filter || $status_filter !== 'all') : ?>
                    No appointments match your current filters. <a href="?tab=appointments">View all appointments</a>
                <?php else : ?>
                    You haven't received any appointment bookings yet.
                <?php endif; ?>
            </p>
        </div>
        <?php else : ?>
        <div class="table-responsive">
            <table class="appointments-table">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Type</th>
                        <th>Property</th>
                        <th>Status</th>
                        <th>Date Requested</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment) : 
                        // Get additional data
                        $listing = null;
                        $listing_title = '';
                        $listing_address = '';
                        $listing_price = '';
                        
                        if ($appointment['listing_id']) {
                            $listing = get_post($appointment['listing_id']);
                            if ($listing) {
                                $listing_title = $listing->post_title;
                                $listing_address = get_field('address', $listing->ID);
                                $listing_price = get_field('price', $listing->ID);
                            }
                        }
                        
                        // Get agent info
                        $agent_name = '';
                        if ($appointment['agent_id']) {
                            $agent = get_post($appointment['agent_id']);
                            if ($agent) {
                                $agent_name = $agent->post_title;
                            }
                        }
                        
                        // Format date
                        $created_date = date('M j, Y g:i A', strtotime($appointment['created_at']));
                    ?>
                    <tr class="appointment-row" data-appointment-id="<?php echo esc_attr($appointment['id']); ?>">
                        <td class="client-info">
                            <div class="client-details">
                                <strong class="client-name"><?php echo esc_html($appointment['client_name']); ?></strong>
                                <div class="client-contact">
                                    <a href="mailto:<?php echo esc_attr($appointment['client_email']); ?>" class="contact-link">
                                        <i class="fas fa-envelope"></i> <?php echo esc_html($appointment['client_email']); ?>
                                    </a>
                                    <?php if ($appointment['client_phone']) : ?>
                                    <a href="tel:<?php echo esc_attr($appointment['client_phone']); ?>" class="contact-link">
                                        <i class="fas fa-phone"></i> <?php echo esc_html($appointment['client_phone']); ?>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="appointment-type">
                            <?php
                            $type_badges = [
                                'showing' => '<span class="appointment-badge type-showing"><i class="fas fa-home"></i> Showing</span>',
                                'consultation' => '<span class="appointment-badge type-consultation"><i class="fas fa-handshake"></i> Consultation</span>',
                                'listing' => '<span class="appointment-badge type-listing"><i class="fas fa-tag"></i> Listing Appt</span>'
                            ];
                            echo $type_badges[$appointment['appointment_type']] ?? '<span class="appointment-badge">' . ucfirst($appointment['appointment_type']) . '</span>';
                            ?>
                        </td>
                        <td class="property-info">
                            <?php if ($listing) : ?>
                            <div class="property-details">
                                <strong class="property-title"><?php echo esc_html($listing_title); ?></strong>
                                <?php if ($listing_address) : ?>
                                <div class="property-address"><?php echo esc_html($listing_address); ?></div>
                                <?php endif; ?>
                                <?php if ($listing_price) : ?>
                                <div class="property-price">$<?php echo number_format($listing_price); ?></div>
                                <?php endif; ?>
                            </div>
                            <?php else : ?>
                            <span class="no-property">No specific property</span>
                            <?php endif; ?>
                        </td>
                        <td class="appointment-status">
                            <?php
                            $status_badges = [
                                'scheduled' => '<span class="status-badge status-scheduled">Scheduled</span>',
                                'completed' => '<span class="status-badge status-completed">Completed</span>',
                                'canceled' => '<span class="status-badge status-canceled">Canceled</span>',
                                'no_show' => '<span class="status-badge status-no-show">No Show</span>'
                            ];
                            echo $status_badges[$appointment['status']] ?? '<span class="status-badge">' . ucfirst($appointment['status']) . '</span>';
                            ?>
                        </td>
                        <td class="appointment-date">
                            <?php echo esc_html($created_date); ?>
                            <?php if ($appointment['scheduled_time']) : ?>
                            <div class="scheduled-time">
                                <i class="fas fa-clock"></i>
                                <?php echo date('M j, Y g:i A', strtotime($appointment['scheduled_time'])); ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td class="appointment-actions">
                            <div class="action-buttons">
                                <?php if ($appointment['booking_url']) : ?>
                                <a href="<?php echo esc_url($appointment['booking_url']); ?>" 
                                   target="_blank" 
                                   class="action-button view-calendly" 
                                   title="View on Calendly">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                                <?php endif; ?>
                                
                                <button type="button" 
                                        class="action-button view-details" 
                                        data-appointment-id="<?php echo esc_attr($appointment['id']); ?>"
                                        title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                <?php if ($appointment['status'] !== 'canceled') : ?>
                                <button type="button" 
                                        class="action-button cancel-appointment" 
                                        data-appointment-id="<?php echo esc_attr($appointment['id']); ?>"
                                        title="Cancel Appointment">
                                    <i class="fas fa-times"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Expandable Message Row -->
                    <?php if (!empty($appointment['message'])) : ?>
                    <tr class="appointment-message" id="message-<?php echo esc_attr($appointment['id']); ?>" style="display: none;">
                        <td colspan="6">
                            <div class="message-content">
                                <strong>Client Message:</strong>
                                <p><?php echo esc_html($appointment['message']); ?></p>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1) : ?>
        <div class="pagination-wrapper">
            <div class="pagination-info">
                Showing <?php echo (($page - 1) * $per_page + 1); ?> - <?php echo min($page * $per_page, $total_appointments); ?> 
                of <?php echo number_format($total_appointments); ?> appointments
            </div>
            <div class="pagination">
                <?php if ($page > 1) : ?>
                <a href="<?php echo esc_url(add_query_arg('appointments_page', $page - 1)); ?>" class="page-btn">
                    <i class="fas fa-chevron-left"></i>
                </a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++) : ?>
                <a href="<?php echo esc_url(add_query_arg('appointments_page', $i)); ?>" 
                   class="page-btn <?php echo $i === $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages) : ?>
                <a href="<?php echo esc_url(add_query_arg('appointments_page', $page + 1)); ?>" class="page-btn">
                    <i class="fas fa-chevron-right"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Appointment Details Modal -->
<div id="appointment-details-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Appointment Details</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Will be populated by JavaScript -->
        </div>
    </div>
</div>

<style>
/* Appointments Table Styles */
.appointments-table-container {
    background: var(--hph-white);
    border-radius: var(--hph-radius-lg);
    box-shadow: var(--hph-shadow-sm);
    overflow: hidden;
}

.appointments-table {
    width: 100%;
    border-collapse: collapse;
}

.appointments-table th,
.appointments-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--hph-border-color-light);
}

.appointments-table th {
    background: var(--hph-gray-50);
    font-weight: 600;
    color: var(--hph-gray-700);
    font-size: var(--hph-text-sm);
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.client-details {
    min-width: 200px;
}

.client-name {
    display: block;
    font-weight: 600;
    color: var(--hph-gray-900);
    margin-bottom: 0.25rem;
}

.client-contact {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.contact-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--hph-gray-600);
    text-decoration: none;
    font-size: var(--hph-text-sm);
    transition: color 0.2s ease;
}

.contact-link:hover {
    color: var(--hph-primary);
}

.appointment-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.375rem 0.75rem;
    border-radius: var(--hph-radius-md);
    font-size: var(--hph-text-sm);
    font-weight: 500;
}

.type-showing {
    background: var(--hph-info-50);
    color: var(--hph-info-700);
}

.type-consultation {
    background: var(--hph-success-50);
    color: var(--hph-success-700);
}

.type-listing {
    background: var(--hph-warning-50);
    color: var(--hph-warning-700);
}

.property-details {
    min-width: 180px;
}

.property-title {
    display: block;
    font-weight: 600;
    color: var(--hph-gray-900);
    margin-bottom: 0.25rem;
}

.property-address {
    color: var(--hph-gray-600);
    font-size: var(--hph-text-sm);
    margin-bottom: 0.25rem;
}

.property-price {
    color: var(--hph-primary);
    font-weight: 600;
    font-size: var(--hph-text-sm);
}

.no-property {
    color: var(--hph-gray-400);
    font-style: italic;
}

.status-badge {
    display: inline-block;
    padding: 0.375rem 0.75rem;
    border-radius: var(--hph-radius-full);
    font-size: var(--hph-text-sm);
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.status-scheduled {
    background: var(--hph-primary-50);
    color: var(--hph-primary-700);
}

.status-completed {
    background: var(--hph-success-50);
    color: var(--hph-success-700);
}

.status-canceled {
    background: var(--hph-danger-50);
    color: var(--hph-danger-700);
}

.status-no-show {
    background: var(--hph-warning-50);
    color: var(--hph-warning-700);
}

.scheduled-time {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.5rem;
    color: var(--hph-gray-600);
    font-size: var(--hph-text-sm);
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.action-button {
    width: 36px;
    height: 36px;
    border: none;
    border-radius: var(--hph-radius-md);
    background: var(--hph-gray-100);
    color: var(--hph-gray-600);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    text-decoration: none;
}

.action-button:hover {
    background: var(--hph-primary);
    color: var(--hph-white);
    transform: translateY(-1px);
}

.appointment-message {
    background: var(--hph-gray-50);
}

.message-content {
    padding: 1rem;
    border-left: 4px solid var(--hph-primary);
    margin: 0.5rem 0;
}

/* Responsive */
@media (max-width: 768px) {
    .appointments-table,
    .appointments-table thead,
    .appointments-table tbody,
    .appointments-table th,
    .appointments-table td,
    .appointments-table tr {
        display: block;
    }
    
    .appointments-table thead tr {
        position: absolute;
        top: -9999px;
        left: -9999px;
    }
    
    .appointments-table tr {
        border: 1px solid var(--hph-border-color);
        border-radius: var(--hph-radius-md);
        margin-bottom: 1rem;
        padding: 1rem;
    }
    
    .appointments-table td {
        border: none;
        padding: 0.5rem 0;
        position: relative;
        padding-left: 50%;
    }
    
    .appointments-table td:before {
        content: attr(data-label);
        position: absolute;
        left: 6px;
        width: 45%;
        padding-right: 10px;
        white-space: nowrap;
        font-weight: 600;
        color: var(--hph-gray-700);
    }
}
</style>

<script>
(function($) {
    'use strict';
    
    $(document).ready(function() {
        // View appointment details
        $('.view-details').on('click', function() {
            const appointmentId = $(this).data('appointment-id');
            const $row = $(this).closest('.appointment-row');
            
            // Toggle message row if exists
            const $messageRow = $('#message-' + appointmentId);
            if ($messageRow.length) {
                $messageRow.toggle();
            }
            
            // You can expand this to show a detailed modal
            // loadAppointmentDetails(appointmentId);
        });
        
        // Cancel appointment
        $('.cancel-appointment').on('click', function() {
            const appointmentId = $(this).data('appointment-id');
            const $button = $(this);
            
            if (confirm('Are you sure you want to cancel this appointment?')) {
                $.ajax({
                    url: '<?php echo admin_url("admin-ajax.php"); ?>',
                    method: 'POST',
                    data: {
                        action: 'hp_cancel_appointment',
                        appointment_id: appointmentId,
                        nonce: '<?php echo wp_create_nonce("hp_dashboard_nonce"); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update status in the row
                            const $statusCell = $button.closest('.appointment-row').find('.appointment-status');
                            $statusCell.html('<span class="status-badge status-canceled">Canceled</span>');
                            
                            // Remove cancel button
                            $button.remove();
                            
                            // Show success message
                            showNotification('Appointment canceled successfully', 'success');
                        } else {
                            showNotification(response.data.message || 'Failed to cancel appointment', 'error');
                        }
                    },
                    error: function() {
                        showNotification('Network error. Please try again.', 'error');
                    }
                });
            }
        });
        
        function showNotification(message, type) {
            // You can implement a notification system here
            // For now, just use alert
            if (type === 'success') {
                alert('✓ ' + message);
            } else {
                alert('✗ ' + message);
            }
        }
    });
    
})(jQuery);
</script>
