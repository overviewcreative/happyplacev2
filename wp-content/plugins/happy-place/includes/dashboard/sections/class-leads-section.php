<?php
/**
 * Dashboard Leads Section
 * Lead management and CRM functionality
 *
 * @package HappyPlace
 */

namespace HappyPlace\Dashboard;

if (!defined('ABSPATH')) {
    exit;
}

class Leads_Section {

    private Dashboard_Manager $dashboard_manager;

    public function __construct(Dashboard_Manager $dashboard_manager) {
        $this->dashboard_manager = $dashboard_manager;
    }

    public function render(): void {
        $action = $this->dashboard_manager->get_current_action();
        
        echo '<div class="hpt-leads-section">';
        
        switch ($action) {
            case 'add':
                $this->render_add_lead_form();
                break;
            case 'edit':
                $this->render_edit_lead_form();
                break;
            case 'view':
                $this->render_lead_details();
                break;
            default:
                $this->render_leads_overview();
        }
        
        echo '</div>';
    }

    private function render_leads_overview(): void {
        echo '<div class="hpt-leads-overview">';
        
        // Header
        echo '<div class="hpt-section-header">';
        echo '<div class="hpt-section-header__left">';
        echo '<h2>Leads Management</h2>';
        echo '<p>Manage your client inquiries and track lead progress.</p>';
        echo '</div>';
        echo '<div class="hpt-section-header__right">';
        echo '<button id="add-lead-btn" class="hpt-button hpt-button--primary">';
        echo '<span class="dashicons dashicons-plus-alt2"></span> Add Lead';
        echo '</button>';
        echo '</div>';
        echo '</div>';

        // Lead Statistics
        echo '<div class="hpt-leads-stats">';
        echo '<div class="hpt-stats-grid">';
        $this->render_lead_stats();
        echo '</div>';
        echo '</div>';

        // Leads Pipeline
        echo '<div class="hpt-leads-pipeline hpt-card">';
        echo '<div class="hpt-card__header">';
        echo '<h3>Sales Pipeline</h3>';
        echo '<div class="hpt-pipeline-controls">';
        echo '<button class="hpt-button hpt-button--sm hpt-button--outline">Kanban View</button>';
        echo '<button class="hpt-button hpt-button--sm">List View</button>';
        echo '</div>';
        echo '</div>';
        echo '<div class="hpt-card__body">';
        $this->render_sales_pipeline();
        echo '</div>';
        echo '</div>';

        // Recent Lead Activity
        echo '<div class="hpt-recent-leads hpt-card">';
        echo '<div class="hpt-card__header">';
        echo '<h3>Recent Lead Activity</h3>';
        echo '<div class="hpt-leads-filters">';
        echo '<select id="lead-status-filter" class="hpt-form__select hpt-form__select--sm">';
        echo '<option value="">All Statuses</option>';
        echo '<option value="new">New</option>';
        echo '<option value="contacted">Contacted</option>';
        echo '<option value="qualified">Qualified</option>';
        echo '<option value="showing">Showing</option>';
        echo '<option value="negotiating">Negotiating</option>';
        echo '<option value="closed">Closed</option>';
        echo '<option value="lost">Lost</option>';
        echo '</select>';
        echo '</div>';
        echo '</div>';
        echo '<div class="hpt-card__body">';
        echo '<div class="hpt-leads-table-container">';
        echo '<table id="leads-table" class="hpt-data-table" style="width:100%">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Name</th>';
        echo '<th>Contact</th>';
        echo '<th>Source</th>';
        echo '<th>Interest</th>';
        echo '<th>Status</th>';
        echo '<th>Last Contact</th>';
        echo '<th>Actions</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        // Table will be populated via AJAX
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        // Add Lead Modal
        $this->render_lead_modal();
        
        $this->render_leads_scripts();
        echo '</div>';
    }

    private function render_lead_stats(): void {
        $agent_id = $this->dashboard_manager->get_current_agent_id();
        $stats = $this->get_lead_statistics($agent_id);
        
        $stat_cards = [
            [
                'title' => 'Total Leads',
                'value' => $stats['total_leads'] ?? 0,
                'change' => '+12 this month',
                'trend' => 'up',
                'icon' => 'dashicons-groups'
            ],
            [
                'title' => 'New Leads',
                'value' => $stats['new_leads'] ?? 0,
                'change' => '+5 this week',
                'trend' => 'up',
                'icon' => 'dashicons-plus-alt2'
            ],
            [
                'title' => 'Hot Prospects',
                'value' => $stats['hot_leads'] ?? 0,
                'change' => '2 ready to buy',
                'trend' => 'up',
                'icon' => 'dashicons-star-filled'
            ],
            [
                'title' => 'Conversion Rate',
                'value' => $stats['conversion_rate'] ?? 0 . '%',
                'change' => '+3% improved',
                'trend' => 'up',
                'icon' => 'dashicons-chart-line'
            ]
        ];

        foreach ($stat_cards as $card) {
            echo '<div class="hpt-lead-stat-card">';
            echo '<div class="hpt-stat-card__icon">';
            echo '<span class="dashicons ' . esc_attr($card['icon']) . '"></span>';
            echo '</div>';
            echo '<div class="hpt-stat-card__content">';
            echo '<div class="hpt-stat-card__value">' . esc_html($card['value']) . '</div>';
            echo '<div class="hpt-stat-card__title">' . esc_html($card['title']) . '</div>';
            echo '<div class="hpt-stat-card__change hpt-stat-card__change--' . esc_attr($card['trend']) . '">';
            echo esc_html($card['change']);
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    }

    private function render_sales_pipeline(): void {
        $pipeline_stages = [
            'new' => ['title' => 'New Leads', 'count' => 8, 'color' => 'gray'],
            'contacted' => ['title' => 'Contacted', 'count' => 5, 'color' => 'blue'],
            'qualified' => ['title' => 'Qualified', 'count' => 3, 'color' => 'green'],
            'showing' => ['title' => 'Showing', 'count' => 4, 'color' => 'orange'],
            'negotiating' => ['title' => 'Negotiating', 'count' => 2, 'color' => 'purple'],
            'closed' => ['title' => 'Closed Won', 'count' => 1, 'color' => 'success']
        ];

        echo '<div class="hpt-pipeline-stages">';
        foreach ($pipeline_stages as $stage => $data) {
            echo '<div class="hpt-pipeline-stage">';
            echo '<div class="hpt-pipeline-stage-header">';
            echo '<h4>' . esc_html($data['title']) . '</h4>';
            echo '<span class="hpt-pipeline-count">' . $data['count'] . '</span>';
            echo '</div>';
            echo '<div class="hpt-pipeline-stage-content">';
            $this->render_pipeline_leads($stage, $data['count']);
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    }

    private function render_pipeline_leads($stage, $count): void {
        // Mock lead data for pipeline view
        $mock_leads = [
            'new' => [
                ['name' => 'Sarah Johnson', 'value' => '$450K', 'source' => 'Website'],
                ['name' => 'Mike Davis', 'value' => '$320K', 'source' => 'Referral'],
                ['name' => 'Lisa Chen', 'value' => '$580K', 'source' => 'Social Media']
            ],
            'contacted' => [
                ['name' => 'Robert Wilson', 'value' => '$400K', 'source' => 'Phone'],
                ['name' => 'Emma Brown', 'value' => '$650K', 'source' => 'Email']
            ],
            'qualified' => [
                ['name' => 'David Miller', 'value' => '$750K', 'source' => 'Open House'],
                ['name' => 'Jennifer Taylor', 'value' => '$425K', 'source' => 'Walk-in']
            ],
            'showing' => [
                ['name' => 'Tom Anderson', 'value' => '$525K', 'source' => 'Website'],
                ['name' => 'Amy Garcia', 'value' => '$680K', 'source' => 'Referral']
            ],
            'negotiating' => [
                ['name' => 'Chris Martinez', 'value' => '$720K', 'source' => 'Social Media']
            ],
            'closed' => [
                ['name' => 'Maria Rodriguez', 'value' => '$610K', 'source' => 'Referral']
            ]
        ];

        $leads = $mock_leads[$stage] ?? [];
        
        foreach (array_slice($leads, 0, 3) as $lead) { // Show max 3 per stage
            echo '<div class="hpt-pipeline-lead-card" draggable="true">';
            echo '<div class="hpt-pipeline-lead-name">' . esc_html($lead['name']) . '</div>';
            echo '<div class="hpt-pipeline-lead-value">' . esc_html($lead['value']) . '</div>';
            echo '<div class="hpt-pipeline-lead-source">' . esc_html($lead['source']) . '</div>';
            echo '</div>';
        }

        if ($count > 3) {
            echo '<div class="hpt-pipeline-more">+' . ($count - 3) . ' more</div>';
        }
    }

    private function render_lead_modal(): void {
        echo '<div id="lead-modal" class="hpt-modal" style="display: none;">';
        echo '<div class="hpt-modal__backdrop"></div>';
        echo '<div class="hpt-modal__container hpt-modal__container--large">';
        echo '<div class="hpt-modal__header">';
        echo '<h3 id="lead-modal-title">Add New Lead</h3>';
        echo '<button class="hpt-modal__close" id="close-lead-modal">&times;</button>';
        echo '</div>';
        echo '<div class="hpt-modal__body">';
        
        echo '<form id="lead-form" class="hpt-lead-form">';
        echo '<input type="hidden" id="lead-id" name="lead_id" value="">';
        
        echo '<div class="hpt-form-tabs">';
        echo '<button type="button" class="hpt-tab-button active" data-tab="contact">Contact Info</button>';
        echo '<button type="button" class="hpt-tab-button" data-tab="details">Lead Details</button>';
        echo '<button type="button" class="hpt-tab-button" data-tab="notes">Notes & Activity</button>';
        echo '</div>';

        // Contact Info Tab
        echo '<div class="hpt-tab-content active" data-tab="contact">';
        echo '<div class="hpt-form-grid">';
        
        echo '<div class="hpt-form__group">';
        echo '<label for="lead-first-name" class="hpt-form__label">First Name <span class="required">*</span></label>';
        echo '<input type="text" id="lead-first-name" name="first_name" class="hpt-form__input" required>';
        echo '</div>';
        
        echo '<div class="hpt-form__group">';
        echo '<label for="lead-last-name" class="hpt-form__label">Last Name <span class="required">*</span></label>';
        echo '<input type="text" id="lead-last-name" name="last_name" class="hpt-form__input" required>';
        echo '</div>';
        
        echo '<div class="hpt-form__group">';
        echo '<label for="lead-email" class="hpt-form__label">Email <span class="required">*</span></label>';
        echo '<input type="email" id="lead-email" name="email" class="hpt-form__input" required>';
        echo '</div>';
        
        echo '<div class="hpt-form__group">';
        echo '<label for="lead-phone" class="hpt-form__label">Phone</label>';
        echo '<input type="tel" id="lead-phone" name="phone" class="hpt-form__input">';
        echo '</div>';
        
        echo '<div class="hpt-form__group hpt-form__group--full">';
        echo '<label for="lead-address" class="hpt-form__label">Address</label>';
        echo '<input type="text" id="lead-address" name="address" class="hpt-form__input">';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';

        // Lead Details Tab
        echo '<div class="hpt-tab-content" data-tab="details">';
        echo '<div class="hpt-form-grid">';
        
        echo '<div class="hpt-form__group">';
        echo '<label for="lead-source" class="hpt-form__label">Lead Source</label>';
        echo '<select id="lead-source" name="source" class="hpt-form__select">';
        echo '<option value="">Select Source</option>';
        echo '<option value="website">Website</option>';
        echo '<option value="social_media">Social Media</option>';
        echo '<option value="referral">Referral</option>';
        echo '<option value="open_house">Open House</option>';
        echo '<option value="walk_in">Walk-in</option>';
        echo '<option value="phone">Phone Call</option>';
        echo '<option value="email">Email Campaign</option>';
        echo '<option value="other">Other</option>';
        echo '</select>';
        echo '</div>';
        
        echo '<div class="hpt-form__group">';
        echo '<label for="lead-status" class="hpt-form__label">Status</label>';
        echo '<select id="lead-status" name="status" class="hpt-form__select">';
        echo '<option value="new">New</option>';
        echo '<option value="contacted">Contacted</option>';
        echo '<option value="qualified">Qualified</option>';
        echo '<option value="showing">Showing</option>';
        echo '<option value="negotiating">Negotiating</option>';
        echo '<option value="closed">Closed</option>';
        echo '<option value="lost">Lost</option>';
        echo '</select>';
        echo '</div>';
        
        echo '<div class="hpt-form__group">';
        echo '<label for="lead-budget-min" class="hpt-form__label">Min Budget</label>';
        echo '<input type="number" id="lead-budget-min" name="budget_min" class="hpt-form__input" step="1000">';
        echo '</div>';
        
        echo '<div class="hpt-form__group">';
        echo '<label for="lead-budget-max" class="hpt-form__label">Max Budget</label>';
        echo '<input type="number" id="lead-budget-max" name="budget_max" class="hpt-form__input" step="1000">';
        echo '</div>';
        
        echo '<div class="hpt-form__group">';
        echo '<label for="lead-bedrooms" class="hpt-form__label">Bedrooms</label>';
        echo '<input type="number" id="lead-bedrooms" name="bedrooms" class="hpt-form__input" min="0" max="10">';
        echo '</div>';
        
        echo '<div class="hpt-form__group">';
        echo '<label for="lead-bathrooms" class="hpt-form__label">Bathrooms</label>';
        echo '<input type="number" id="lead-bathrooms" name="bathrooms" class="hpt-form__input" min="0" max="10" step="0.5">';
        echo '</div>';
        
        echo '<div class="hpt-form__group hpt-form__group--full">';
        echo '<label for="lead-areas" class="hpt-form__label">Preferred Areas</label>';
        echo '<input type="text" id="lead-areas" name="preferred_areas" class="hpt-form__input" placeholder="Downtown, Westside, etc.">';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';

        // Notes Tab
        echo '<div class="hpt-tab-content" data-tab="notes">';
        echo '<div class="hpt-form__group">';
        echo '<label for="lead-notes" class="hpt-form__label">Notes</label>';
        echo '<textarea id="lead-notes" name="notes" class="hpt-form__textarea" rows="6" placeholder="Add notes about this lead..."></textarea>';
        echo '</div>';
        echo '<div class="hpt-lead-activity">';
        echo '<h4>Activity History</h4>';
        echo '<div id="lead-activity-list" class="hpt-activity-list">';
        echo '<p class="hpt-empty-state">No activity recorded yet.</p>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '</form>';
        
        echo '</div>';
        echo '<div class="hpt-modal__footer">';
        echo '<button type="button" class="hpt-button hpt-button--secondary" id="cancel-lead">Cancel</button>';
        echo '<button type="submit" form="lead-form" class="hpt-button hpt-button--primary" id="save-lead">Save Lead</button>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    private function render_add_lead_form(): void {
        // Standalone add lead form if needed
        echo '<div class="hpt-add-lead-form">';
        echo '<div class="hpt-section-header">';
        echo '<div class="hpt-section-header__left">';
        echo '<h2>Add New Lead</h2>';
        echo '</div>';
        echo '<div class="hpt-section-header__right">';
        echo '<a href="' . esc_url(home_url('/agent-dashboard/leads/')) . '" class="hpt-button hpt-button--outline">';
        echo '<span class="dashicons dashicons-arrow-left-alt2"></span> Back to Leads';
        echo '</a>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    private function render_edit_lead_form(): void {
        // Standalone edit lead form if needed
        echo '<div class="hpt-edit-lead-form">';
        echo '<div class="hpt-section-header">';
        echo '<div class="hpt-section-header__left">';
        echo '<h2>Edit Lead</h2>';
        echo '</div>';
        echo '<div class="hpt-section-header__right">';
        echo '<a href="' . esc_url(home_url('/agent-dashboard/leads/')) . '" class="hpt-button hpt-button--outline">';
        echo '<span class="dashicons dashicons-arrow-left-alt2"></span> Back to Leads';
        echo '</a>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    private function render_lead_details(): void {
        // Standalone lead details view if needed
        echo '<div class="hpt-lead-details">';
        echo '<div class="hpt-section-header">';
        echo '<div class="hpt-section-header__left">';
        echo '<h2>Lead Details</h2>';
        echo '</div>';
        echo '<div class="hpt-section-header__right">';
        echo '<a href="' . esc_url(home_url('/agent-dashboard/leads/')) . '" class="hpt-button hpt-button--outline">';
        echo '<span class="dashicons dashicons-arrow-left-alt2"></span> Back to Leads';
        echo '</a>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    private function render_leads_scripts(): void {
        echo '<script>';
        echo 'jQuery(document).ready(function($) {';
        
        // Initialize DataTable
        echo 'var leadsTable = $("#leads-table").DataTable({';
        echo 'processing: true,';
        echo 'serverSide: false,';
        echo 'ajax: {';
        echo 'url: ajaxurl,';
        echo 'type: "POST",';
        echo 'data: {';
        echo 'action: "hpt_dashboard_data",';
        echo 'section: "leads",';
        echo 'action_type: "get_table_data",';
        echo 'nonce: hptDashboard.nonce';
        echo '}';
        echo '},';
        echo 'columns: [';
        echo '{ data: "name", orderable: false },';
        echo '{ data: "contact", orderable: false },';
        echo '{ data: "source" },';
        echo '{ data: "interest", orderable: false },';
        echo '{ data: "status", className: "text-center" },';
        echo '{ data: "last_contact" },';
        echo '{ data: "actions", className: "text-center", orderable: false }';
        echo '],';
        echo 'order: [[ 5, "desc" ]],';
        echo 'pageLength: 25,';
        echo 'responsive: true';
        echo '});';

        // Modal handlers
        echo '$("#add-lead-btn").on("click", function() {';
        echo '$("#lead-modal").show();';
        echo '});';

        echo '$("#close-lead-modal, #cancel-lead, .hpt-modal__backdrop").on("click", function() {';
        echo '$("#lead-modal").hide();';
        echo '$("#lead-form")[0].reset();';
        echo '$(".hpt-tab-button").removeClass("active");';
        echo '$(".hpt-tab-content").removeClass("active");';
        echo '$(".hpt-tab-button[data-tab=contact]").addClass("active");';
        echo '$(".hpt-tab-content[data-tab=contact]").addClass("active");';
        echo '});';

        // Tab switching
        echo '$(document).on("click", ".hpt-tab-button", function() {';
        echo 'var tab = $(this).data("tab");';
        echo '$(".hpt-tab-button").removeClass("active");';
        echo '$(".hpt-tab-content").removeClass("active");';
        echo '$(this).addClass("active");';
        echo '$(".hpt-tab-content[data-tab=" + tab + "]").addClass("active");';
        echo '});';

        // Lead form submission
        echo '$("#lead-form").on("submit", function(e) {';
        echo 'e.preventDefault();';
        echo 'var formData = $(this).serialize();';
        echo 'formData += "&action=hpt_dashboard_action&dashboard_action=save_lead&nonce=" + hptDashboard.nonce;';
        echo '$.post(ajaxurl, formData, function(response) {';
        echo 'if (response.success) {';
        echo '$("#lead-modal").hide();';
        echo '$("#lead-form")[0].reset();';
        echo 'leadsTable.ajax.reload();';
        echo 'hptShowNotice("success", response.data.message);';
        echo '} else {';
        echo 'hptShowNotice("error", response.data.message);';
        echo '}';
        echo '});';
        echo '});';

        // Status filter
        echo '$("#lead-status-filter").on("change", function() {';
        echo 'var status = $(this).val();';
        echo '// Filter table by status';
        echo '});';

        echo '});';
        echo '</script>';
    }

    private function get_lead_statistics($agent_id): array {
        // Mock statistics - in real implementation, calculate from database
        return [
            'total_leads' => rand(50, 150),
            'new_leads' => rand(5, 20),
            'hot_leads' => rand(2, 10),
            'conversion_rate' => rand(15, 35)
        ];
    }

    public function handle_ajax_get_table_data($data): array {
        $agent_id = $this->dashboard_manager->get_current_agent_id();
        
        if (!$agent_id) {
            return ['success' => false, 'message' => 'Invalid agent ID'];
        }

        // Mock leads data
        $leads = [
            [
                'name' => '<div class="hpt-lead-name"><strong>Sarah Johnson</strong><br><small>sarah.johnson@email.com</small></div>',
                'contact' => '<div class="hpt-lead-contact">(555) 123-4567<br><small>Prefers Email</small></div>',
                'source' => 'Website',
                'interest' => '<div class="hpt-lead-interest">$400K - $500K<br><small>3 bed, 2 bath</small></div>',
                'status' => '<span class="hpt-lead-status hpt-lead-status--new">New</span>',
                'last_contact' => '2 days ago',
                'actions' => '<div class="hpt-table-actions"><button class="hpt-button hpt-button--sm hpt-button--outline">View</button> <button class="hpt-button hpt-button--sm hpt-button--outline">Edit</button></div>'
            ],
            [
                'name' => '<div class="hpt-lead-name"><strong>Mike Davis</strong><br><small>m.davis@email.com</small></div>',
                'contact' => '<div class="hpt-lead-contact">(555) 987-6543<br><small>Prefers Phone</small></div>',
                'source' => 'Referral',
                'interest' => '<div class="hpt-lead-interest">$300K - $400K<br><small>2 bed, 2 bath</small></div>',
                'status' => '<span class="hpt-lead-status hpt-lead-status--contacted">Contacted</span>',
                'last_contact' => '1 week ago',
                'actions' => '<div class="hpt-table-actions"><button class="hpt-button hpt-button--sm hpt-button--outline">View</button> <button class="hpt-button hpt-button--sm hpt-button--outline">Edit</button></div>'
            ]
        ];

        return [
            'success' => true,
            'data' => $leads
        ];
    }

    public function handle_ajax_save_lead($data): array {
        $agent_id = $this->dashboard_manager->get_current_agent_id();
        $lead_id = (int) ($data['lead_id'] ?? 0);
        
        if (!$agent_id) {
            return ['success' => false, 'message' => 'Agent not found'];
        }

        // Validate required fields
        $required_fields = ['first_name', 'last_name', 'email'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => 'Missing required field: ' . $field];
            }
        }

        // Sanitize data
        $lead_data = [
            'first_name' => sanitize_text_field($data['first_name']),
            'last_name' => sanitize_text_field($data['last_name']),
            'email' => sanitize_email($data['email']),
            'phone' => sanitize_text_field($data['phone'] ?? ''),
            'address' => sanitize_text_field($data['address'] ?? ''),
            'source' => sanitize_text_field($data['source'] ?? ''),
            'status' => sanitize_text_field($data['status'] ?? 'new'),
            'budget_min' => (int) ($data['budget_min'] ?? 0),
            'budget_max' => (int) ($data['budget_max'] ?? 0),
            'bedrooms' => (int) ($data['bedrooms'] ?? 0),
            'bathrooms' => (float) ($data['bathrooms'] ?? 0),
            'preferred_areas' => sanitize_text_field($data['preferred_areas'] ?? ''),
            'notes' => sanitize_textarea_field($data['notes'] ?? ''),
            'agent_id' => $agent_id
        ];

        // In real implementation, save to database
        // For now, just return success
        return [
            'success' => true,
            'data' => [
                'message' => $lead_id ? 'Lead updated successfully' : 'Lead created successfully',
                'lead_id' => $lead_id ?: rand(1000, 9999)
            ]
        ];
    }

    public function handle_ajax_delete_lead($data): array {
        $lead_id = (int) ($data['lead_id'] ?? 0);
        $agent_id = $this->dashboard_manager->get_current_agent_id();
        
        if (!$lead_id || !$agent_id) {
            return ['success' => false, 'message' => 'Invalid request'];
        }

        // Verify ownership and delete lead
        // In real implementation, this would delete from database
        
        return [
            'success' => true,
            'data' => ['message' => 'Lead deleted successfully']
        ];
    }
}