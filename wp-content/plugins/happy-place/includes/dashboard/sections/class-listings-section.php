<?php
/**
 * Dashboard Listings Section
 * Manage agent listings with DataTable interface
 *
 * @package HappyPlace
 */

namespace HappyPlace\Dashboard;

if (!defined('ABSPATH')) {
    exit;
}

class Listings_Section {

    private Dashboard_Manager $dashboard_manager;

    public function __construct(Dashboard_Manager $dashboard_manager) {
        $this->dashboard_manager = $dashboard_manager;
    }

    public function render(): void {
        $action = $this->dashboard_manager->get_current_action();
        $view_agent = get_query_var('view_agent', '');
        
        echo '<div class="hpt-listings-section">';
        
        switch ($action) {
            case 'new':
                $this->render_listing_form();
                break;
            case 'edit':
                // Extract listing ID from view_agent parameter (since we're using it as the third URL segment)
                $listing_id = is_numeric($view_agent) ? intval($view_agent) : 0;
                $this->render_listing_form($listing_id);
                break;
            case 'openhouse':
                $listing_id = is_numeric($view_agent) ? intval($view_agent) : 0;
                $this->render_openhouse_form($listing_id);
                break;
            default:
                $this->render_listings_table();
        }
        
        echo '</div>';
    }

    private function render_listings_table(): void {
        echo '<div class="hpt-listings-table-section">';
        
        // Header with actions
        echo '<div class="hpt-section-header">';
        echo '<div class="hpt-section-header__left">';
        echo '<h2>My Listings</h2>';
        echo '<p>Manage your property listings, update information, and track performance.</p>';
        echo '</div>';
        echo '<div class="hpt-section-header__right">';
        echo '<button id="add-new-listing" class="hpt-button hpt-button--primary">';
        echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="margin-right: 8px;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/></svg>';
        echo 'Add New Listing';
        echo '</button>';
        echo '</div>';
        echo '</div>';

        // Filters
        echo '<div class="hpt-listings-filters hpt-card">';
        echo '<div class="hpt-card__body">';
        echo '<div class="hpt-filters-grid">';
        
        echo '<div class="hpt-filter-group">';
        echo '<label for="status-filter">Status</label>';
        echo '<select id="status-filter" class="hpt-form__select">';
        echo '<option value="">All Statuses</option>';
        echo '<option value="active">Active</option>';
        echo '<option value="pending">Pending</option>';
        echo '<option value="sold">Sold</option>';
        echo '<option value="coming_soon">Coming Soon</option>';
        echo '<option value="off_market">Off Market</option>';
        echo '</select>';
        echo '</div>';
        
        echo '<div class="hpt-filter-group">';
        echo '<label for="price-filter">Price Range</label>';
        echo '<select id="price-filter" class="hpt-form__select">';
        echo '<option value="">All Prices</option>';
        echo '<option value="0-200000">Under $200K</option>';
        echo '<option value="200000-500000">$200K - $500K</option>';
        echo '<option value="500000-1000000">$500K - $1M</option>';
        echo '<option value="1000000-">Over $1M</option>';
        echo '</select>';
        echo '</div>';
        
        echo '<div class="hpt-filter-group">';
        echo '<label for="featured-filter">Featured</label>';
        echo '<select id="featured-filter" class="hpt-form__select">';
        echo '<option value="">All Listings</option>';
        echo '<option value="1">Featured Only</option>';
        echo '<option value="0">Non-Featured</option>';
        echo '</select>';
        echo '</div>';
        
        echo '<div class="hpt-filter-actions">';
        echo '<button type="button" id="apply-filters" class="hpt-button hpt-button--sm">Apply Filters</button>';
        echo '<button type="button" id="clear-filters" class="hpt-button hpt-button--outline hpt-button--sm">Clear</button>';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
        echo '</div>';

        // DataTable
        echo '<div class="hpt-listings-table-container hpt-card">';
        echo '<div class="hpt-card__body">';
        echo '<table id="listings-table" class="hpt-data-table" style="width:100%">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Property</th>';
        echo '<th>Price</th>';
        echo '<th>Status</th>';
        echo '<th>Beds/Baths</th>';
        echo '<th>Sq Ft</th>';
        echo '<th>Days on Market</th>';
        echo '<th>Views</th>';
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

        // Initialize DataTable
        $this->render_listings_table_script();
    }

    private function render_listings_table_script(): void {
        $agent_id = $this->dashboard_manager->get_current_agent_id();
        
        echo '<script>';
        echo 'jQuery(document).ready(function($) {';
        
        // Add New Listing handler
        echo '$("#add-new-listing").on("click", function() {';
        echo 'showListingForm();';
        echo '});';
        
        echo '$(document).on("click", ".edit-listing", function() {';
        echo 'var listingId = $(this).data("listing-id");';
        echo 'showListingForm(listingId);';
        echo '});';

        // Initialize DataTable
        echo 'var listingsTable = $("#listings-table").DataTable({';
        echo 'processing: true,';
        echo 'serverSide: false,';
        echo 'ajax: {';
        echo 'url: hptDashboard.ajaxUrl,';
        echo 'type: "POST",';
        echo 'data: function(d) {';
        echo 'var requestData = {';
        echo 'action: "hpt_dashboard_data",';
        echo 'section: "listings",';
        echo 'action_type: "get_table_data",';
        echo 'agent_id: ' . $agent_id . ',';
        echo 'nonce: hptDashboard.nonce,';
        echo 'filters: window.currentListingFilters || {}';
        echo '};';
        echo 'console.log("DataTables Ajax Request:", requestData);';
        echo 'return requestData;';
        echo '},';
        echo 'error: function(xhr, error, code) {';
        echo 'console.error("DataTables Ajax Error:", {xhr: xhr, error: error, code: code, response: xhr.responseText});';
        echo 'alert("Error loading listings data. Please check the console for details.");';
        echo '}';
        echo '},';
        echo 'columns: [';
        echo '{ data: "property", orderable: false },';
        echo '{ data: "price", className: "text-right" },';
        echo '{ data: "status", className: "text-center" },';
        echo '{ data: "beds_baths", className: "text-center", orderable: false },';
        echo '{ data: "sqft", className: "text-right" },';
        echo '{ data: "days_on_market", className: "text-center" },';
        echo '{ data: "views", className: "text-center" },';
        echo '{ data: "actions", className: "text-center", orderable: false }';
        echo '],';
        echo 'order: [[ 5, "desc" ]],';
        echo 'pageLength: 25,';
        echo 'responsive: true,';
        echo 'language: {';
        echo 'emptyTable: "No listings found. <a href=\"" + "' . admin_url('post-new.php?post_type=listing') . '\" class=\"hpt-button hpt-button--sm\">Create your first listing</a>"';
        echo '}';
        echo '});';

        // Initialize filter storage
        echo 'window.currentListingFilters = {};';
        
        // Filter handlers
        echo '$("#apply-filters").on("click", function() {';
        echo 'window.currentListingFilters = {';
        echo 'status: $("#status-filter").val(),';
        echo 'price: $("#price-filter").val(),';
        echo 'featured: $("#featured-filter").val()';
        echo '};';
        echo 'listingsTable.ajax.reload();';
        echo '});';

        echo '$("#clear-filters").on("click", function() {';
        echo '$("#status-filter, #price-filter, #featured-filter").val("");';
        echo 'window.currentListingFilters = {};';
        echo 'listingsTable.ajax.reload();';
        echo '});';

        // Action handlers
        echo '$(document).on("click", ".toggle-featured", function(e) {';
        echo 'e.preventDefault();';
        echo 'var $btn = $(this);';
        echo 'var listingId = $btn.data("listing-id");';
        echo '$.post(hptDashboard.ajaxUrl, {';
        echo 'action: "hpt_dashboard_action",';
        echo 'dashboard_action: "toggle_featured",';
        echo 'listing_id: listingId,';
        echo 'nonce: hptDashboard.nonce';
        echo '}, function(response) {';
        echo 'if (response.success) {';
        echo 'listingsTable.ajax.reload(null, false);';
        echo 'hptShowNotice("success", response.data.message);';
        echo '} else {';
        echo 'hptShowNotice("error", response.data.message);';
        echo '}';
        echo '});';
        echo '});';

        echo '$(document).on("click", ".delete-listing", function(e) {';
        echo 'e.preventDefault();';
        echo 'if (!confirm("Are you sure you want to delete this listing?")) return;';
        echo 'var $btn = $(this);';
        echo 'var listingId = $btn.data("listing-id");';
        echo '$.post(hptDashboard.ajaxUrl, {';
        echo 'action: "hpt_dashboard_action",';
        echo 'dashboard_action: "delete_listing",';
        echo 'listing_id: listingId,';
        echo 'nonce: hptDashboard.nonce';
        echo '}, function(response) {';
        echo 'if (response.success) {';
        echo 'listingsTable.ajax.reload(null, false);';
        echo 'hptShowNotice("success", response.data.message);';
        echo '} else {';
        echo 'hptShowNotice("error", response.data.message);';
        echo '}';
        echo '});';
        echo '});';

        // Schedule Open House handler
        echo '$(document).on("click", ".schedule-openhouse", function(e) {';
        echo 'e.preventDefault();';
        echo 'var listingId = $(this).data("listing-id");';
        echo 'window.location.href = "' . home_url('/agent-dashboard/listings/openhouse/') . '" + listingId + "/";';
        echo '});';
        
        // Dropdown toggle
        echo '$(document).on("click", ".hpt-dropdown-toggle", function(e) {';
        echo 'e.preventDefault();';
        echo 'e.stopPropagation();';
        echo '$(this).siblings(".hpt-dropdown-menu").toggle();';
        echo '});';
        
        // Close dropdowns when clicking outside
        echo '$(document).on("click", function(e) {';
        echo 'if (!$(e.target).closest(".hpt-table-actions-dropdown").length) {';
        echo '$(".hpt-dropdown-menu").hide();';
        echo '}';
        echo '});';
        
        echo '});';
        echo '</script>';
    }

    private function render_listing_form($listing_id = null): void {
        $listing_data = null;
        $is_edit = false;
        
        if ($listing_id) {
            $listing = get_post($listing_id);
            if ($listing && $listing->post_type === 'listing') {
                $is_edit = true;
                $listing_data = [
                    'title' => $listing->post_title,
                    'description' => $listing->post_content,
                    'price' => get_field('price', $listing_id),
                    'status' => get_field('listing_status', $listing_id),
                    'bedrooms' => get_field('bedrooms', $listing_id),
                    'bathrooms' => get_field('bathrooms', $listing_id),
                    'square_feet' => get_field('square_feet', $listing_id),
                    'address' => get_field('street_address', $listing_id),
                    'city' => get_field('city', $listing_id),
                    'state' => get_field('state', $listing_id),
                    'zip_code' => get_field('zip_code', $listing_id),
                    'featured' => get_field('featured_listing', $listing_id)
                ];
            }
        }

        echo '<div class="hpt-listing-form-section">';
        
        // Header
        echo '<div class="hpt-section-header">';
        echo '<div class="hpt-section-header__left">';
        echo '<h2>' . ($is_edit ? 'Edit Listing' : 'Add New Listing') . '</h2>';
        echo '</div>';
        echo '<div class="hpt-section-header__right">';
        echo '<a href="' . esc_url(home_url('/agent-dashboard/listings/')) . '" class="hpt-button hpt-button--outline">';
        echo '<span class="dashicons dashicons-arrow-left-alt2"></span> Back to Listings';
        echo '</a>';
        echo '</div>';
        echo '</div>';

        // Form
        echo '<form id="listing-form" class="hpt-listing-form hpt-card">';
        echo '<div class="hpt-card__body">';

        echo '<input type="hidden" name="listing_id" value="' . esc_attr($listing_id ?: 0) . '">';
        
        // Basic Information
        echo '<div class="hpt-form-section">';
        echo '<h3>Basic Information</h3>';
        echo '<div class="hpt-form-grid">';
        
        echo '<div class="hpt-form__group hpt-form__group--full">';
        echo '<label for="listing-title" class="hpt-form__label">Property Title <span class="required">*</span></label>';
        echo '<input type="text" id="listing-title" name="title" class="hpt-form__input" value="' . esc_attr($listing_data['title'] ?? '') . '" required>';
        echo '</div>';
        
        echo '<div class="hpt-form__group">';
        echo '<label for="listing-price" class="hpt-form__label">Price <span class="required">*</span></label>';
        echo '<input type="number" id="listing-price" name="price" class="hpt-form__input" value="' . esc_attr($listing_data['price'] ?? '') . '" min="0" step="1000" required>';
        echo '</div>';
        
        echo '<div class="hpt-form__group">';
        echo '<label for="listing-status" class="hpt-form__label">Status <span class="required">*</span></label>';
        echo '<select id="listing-status" name="status" class="hpt-form__select" required>';
        $statuses = [
            'active' => 'Active',
            'pending' => 'Pending',
            'sold' => 'Sold',
            'coming_soon' => 'Coming Soon',
            'off_market' => 'Off Market'
        ];
        foreach ($statuses as $value => $label) {
            $selected = ($listing_data['status'] ?? 'active') === $value ? ' selected' : '';
            echo '<option value="' . esc_attr($value) . '"' . $selected . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        
        echo '<div class="hpt-form__group">';
        echo '<label for="listing-bedrooms" class="hpt-form__label">Bedrooms</label>';
        echo '<input type="number" id="listing-bedrooms" name="bedrooms" class="hpt-form__input" value="' . esc_attr($listing_data['bedrooms'] ?? '') . '" min="0" max="20">';
        echo '</div>';
        
        echo '<div class="hpt-form__group">';
        echo '<label for="listing-bathrooms" class="hpt-form__label">Bathrooms</label>';
        echo '<input type="number" id="listing-bathrooms" name="bathrooms" class="hpt-form__input" value="' . esc_attr($listing_data['bathrooms'] ?? '') . '" min="0" max="20" step="0.5">';
        echo '</div>';
        
        echo '<div class="hpt-form__group">';
        echo '<label for="listing-sqft" class="hpt-form__label">Square Feet</label>';
        echo '<input type="number" id="listing-sqft" name="square_feet" class="hpt-form__input" value="' . esc_attr($listing_data['square_feet'] ?? '') . '" min="0">';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';

        // Address Information
        echo '<div class="hpt-form-section">';
        echo '<h3>Address</h3>';
        echo '<div class="hpt-form-grid">';
        
        echo '<div class="hpt-form__group hpt-form__group--full">';
        echo '<label for="listing-address" class="hpt-form__label">Street Address <span class="required">*</span></label>';
        echo '<input type="text" id="listing-address" name="address" class="hpt-form__input" value="' . esc_attr($listing_data['address'] ?? '') . '" required>';
        echo '</div>';
        
        echo '<div class="hpt-form__group">';
        echo '<label for="listing-city" class="hpt-form__label">City <span class="required">*</span></label>';
        echo '<input type="text" id="listing-city" name="city" class="hpt-form__input" value="' . esc_attr($listing_data['city'] ?? '') . '" required>';
        echo '</div>';
        
        echo '<div class="hpt-form__group">';
        echo '<label for="listing-state" class="hpt-form__label">State <span class="required">*</span></label>';
        echo '<select id="listing-state" name="state" class="hpt-form__select" required>';
        echo '<option value="">Select State</option>';
        $states = [
            'TX' => 'Texas', 'CA' => 'California', 'FL' => 'Florida', 'NY' => 'New York',
            // Add more states as needed
        ];
        foreach ($states as $code => $name) {
            $selected = ($listing_data['state'] ?? '') === $code ? ' selected' : '';
            echo '<option value="' . esc_attr($code) . '"' . $selected . '>' . esc_html($name) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        
        echo '<div class="hpt-form__group">';
        echo '<label for="listing-zip" class="hpt-form__label">ZIP Code <span class="required">*</span></label>';
        echo '<input type="text" id="listing-zip" name="zip_code" class="hpt-form__input" value="' . esc_attr($listing_data['zip_code'] ?? '') . '" pattern="[0-9]{5}" required>';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';

        // Description
        echo '<div class="hpt-form-section">';
        echo '<h3>Description</h3>';
        echo '<div class="hpt-form__group">';
        echo '<label for="listing-description" class="hpt-form__label">Property Description</label>';
        echo '<textarea id="listing-description" name="description" class="hpt-form__textarea" rows="6">' . esc_textarea($listing_data['description'] ?? '') . '</textarea>';
        echo '</div>';
        echo '</div>';

        // Options
        echo '<div class="hpt-form-section">';
        echo '<h3>Options</h3>';
        echo '<div class="hpt-form__group">';
        echo '<label class="hpt-form__checkbox">';
        echo '<input type="checkbox" name="featured" value="1"' . (($listing_data['featured'] ?? false) ? ' checked' : '') . '>';
        echo '<span class="hpt-form__checkbox-text">Feature this listing</span>';
        echo '</label>';
        echo '</div>';
        echo '</div>';

        // Form Actions
        echo '<div class="hpt-form__actions">';
        echo '<button type="submit" class="hpt-button hpt-button--primary">';
        echo $is_edit ? 'Update Listing' : 'Create Listing';
        echo '</button>';
        echo '<a href="' . esc_url(home_url('/agent-dashboard/listings/')) . '" class="hpt-button hpt-button--secondary">Cancel</a>';
        echo '</div>';

        echo '</div>';
        echo '</form>';
        
        echo '</div>';

        // Form submission script
        $this->render_listing_form_script();
    }

    private function render_listing_form_script(): void {
        echo '<script>';
        echo 'jQuery(document).ready(function($) {';
        echo '$("#listing-form").on("submit", function(e) {';
        echo 'e.preventDefault();';
        echo 'var formData = $(this).serialize();';
        echo 'formData += "&action=hpt_dashboard_action&dashboard_action=save_listing&nonce=" + hptDashboard.nonce;';
        echo '$.post(hptDashboard.ajaxUrl, formData, function(response) {';
        echo 'if (response.success) {';
        echo 'hptShowNotice("success", response.data.message);';
        echo 'if (response.data.redirect) {';
        echo 'setTimeout(function() { window.location.href = response.data.redirect; }, 2000);';
        echo '}';
        echo '} else {';
        echo 'hptShowNotice("error", response.data.message);';
        echo '}';
        echo '}).fail(function() {';
        echo 'hptShowNotice("error", "An error occurred. Please try again.");';
        echo '});';
        echo '});';
        
        // Notification system
        echo 'function hptShowNotice(type, message) {';
        echo 'var noticeClass = type === "success" ? "notice-success" : "notice-error";';
        echo 'var notice = $("<div class=\\"hpt-notice " + noticeClass + "\\"><p>" + message + "</p></div>");';
        echo '$(".hpt-listings-section").prepend(notice);';
        echo 'setTimeout(function() { notice.fadeOut(function() { notice.remove(); }); }, 5000);';
        echo '}';

        // Show inline listing form
        echo 'function showListingForm(listingId) {';
        echo 'var isEdit = listingId ? true : false;';
        echo 'var formHtml = `<div id="inline-listing-form" class="hpt-inline-form-overlay">`;';
        echo 'formHtml += `<div class="hpt-inline-form-container">`;';
        echo 'formHtml += `<div class="hpt-inline-form-header">`;';
        echo 'formHtml += `<h3>` + (isEdit ? "Edit Listing" : "Add New Listing") + `</h3>`;';
        echo 'formHtml += `<button type="button" class="hpt-close-form" onclick="closeInlineForm()">&times;</button>`;';
        echo 'formHtml += `</div>`;';
        echo 'formHtml += `<form id="inline-listing-form-data">`;';
        echo 'formHtml += `<input type="hidden" name="listing_id" value="` + (listingId || 0) + `">`;';
        echo 'formHtml += `<div class="hpt-form-grid">`;';
        echo 'formHtml += `<div class="hpt-form__group hpt-form__group--full">`;';
        echo 'formHtml += `<label class="hpt-form__label">Property Title <span class="required">*</span></label>`;';
        echo 'formHtml += `<input type="text" name="title" class="hpt-form__input" required>`;';
        echo 'formHtml += `</div>`;';
        echo 'formHtml += `<div class="hpt-form__group">`;';
        echo 'formHtml += `<label class="hpt-form__label">Price <span class="required">*</span></label>`;';
        echo 'formHtml += `<input type="number" name="price" class="hpt-form__input" min="0" step="1000" required>`;';
        echo 'formHtml += `</div>`;';
        echo 'formHtml += `<div class="hpt-form__group">`;';
        echo 'formHtml += `<label class="hpt-form__label">Status <span class="required">*</span></label>`;';
        echo 'formHtml += `<select name="status" class="hpt-form__select" required>`;';
        echo 'formHtml += `<option value="active">Active</option>`;';
        echo 'formHtml += `<option value="pending">Pending</option>`;';
        echo 'formHtml += `<option value="sold">Sold</option>`;';
        echo 'formHtml += `<option value="coming_soon">Coming Soon</option>`;';
        echo 'formHtml += `<option value="off_market">Off Market</option>`;';
        echo 'formHtml += `</select>`;';
        echo 'formHtml += `</div>`;';
        echo 'formHtml += `<div class="hpt-form__group">`;';
        echo 'formHtml += `<label class="hpt-form__label">Bedrooms</label>`;';
        echo 'formHtml += `<input type="number" name="bedrooms" class="hpt-form__input" min="0" max="20">`;';
        echo 'formHtml += `</div>`;';
        echo 'formHtml += `<div class="hpt-form__group">`;';
        echo 'formHtml += `<label class="hpt-form__label">Bathrooms</label>`;';
        echo 'formHtml += `<input type="number" name="bathrooms" class="hpt-form__input" min="0" max="20" step="0.5">`;';
        echo 'formHtml += `</div>`;';
        echo 'formHtml += `<div class="hpt-form__group">`;';
        echo 'formHtml += `<label class="hpt-form__label">Square Feet</label>`;';
        echo 'formHtml += `<input type="number" name="square_feet" class="hpt-form__input" min="0">`;';
        echo 'formHtml += `</div>`;';
        echo 'formHtml += `<div class="hpt-form__group hpt-form__group--full">`;';
        echo 'formHtml += `<label class="hpt-form__label">Street Address <span class="required">*</span></label>`;';
        echo 'formHtml += `<input type="text" name="address" class="hpt-form__input" required>`;';
        echo 'formHtml += `</div>`;';
        echo 'formHtml += `<div class="hpt-form__group">`;';
        echo 'formHtml += `<label class="hpt-form__label">City <span class="required">*</span></label>`;';
        echo 'formHtml += `<input type="text" name="city" class="hpt-form__input" required>`;';
        echo 'formHtml += `</div>`;';
        echo 'formHtml += `<div class="hpt-form__group">`;';
        echo 'formHtml += `<label class="hpt-form__label">State <span class="required">*</span></label>`;';
        echo 'formHtml += `<select name="state" class="hpt-form__select" required>`;';
        echo 'formHtml += `<option value="">Select State</option>`;';
        echo 'formHtml += `<option value="TX">Texas</option>`;';
        echo 'formHtml += `<option value="CA">California</option>`;';
        echo 'formHtml += `<option value="FL">Florida</option>`;';
        echo 'formHtml += `<option value="NY">New York</option>`;';
        echo 'formHtml += `</select>`;';
        echo 'formHtml += `</div>`;';
        echo 'formHtml += `<div class="hpt-form__group">`;';
        echo 'formHtml += `<label class="hpt-form__label">ZIP Code <span class="required">*</span></label>`;';
        echo 'formHtml += `<input type="text" name="zip_code" class="hpt-form__input" pattern="[0-9]{5}" required>`;';
        echo 'formHtml += `</div>`;';
        echo 'formHtml += `<div class="hpt-form__group hpt-form__group--full">`;';
        echo 'formHtml += `<label class="hpt-form__label">Description</label>`;';
        echo 'formHtml += `<textarea name="description" class="hpt-form__textarea" rows="4"></textarea>`;';
        echo 'formHtml += `</div>`;';
        echo 'formHtml += `<div class="hpt-form__group hpt-form__group--full">`;';
        echo 'formHtml += `<label class="hpt-form__checkbox">`;';
        echo 'formHtml += `<input type="checkbox" name="featured" value="1">`;';
        echo 'formHtml += `<span class="hpt-form__checkbox-text">Feature this listing</span>`;';
        echo 'formHtml += `</label>`;';
        echo 'formHtml += `</div>`;';
        echo 'formHtml += `</div>`;';
        echo 'formHtml += `<div class="hpt-form__actions">`;';
        echo 'formHtml += `<button type="submit" class="hpt-button hpt-button--primary">` + (isEdit ? "Update Listing" : "Create Listing") + `</button>`;';
        echo 'formHtml += `<button type="button" onclick="closeInlineForm()" class="hpt-button hpt-button--secondary">Cancel</button>`;';
        echo 'formHtml += `</div>`;';
        echo 'formHtml += `</form>`;';
        echo 'formHtml += `</div>`;';
        echo 'formHtml += `</div>`;';
        echo '$("body").append(formHtml);';
        
        // If editing, load existing data
        echo 'if (isEdit) {';
        echo '$.post(hptDashboard.ajaxUrl, {';
        echo 'action: "hpt_dashboard_action",';
        echo 'dashboard_action: "get_listing_data",';
        echo 'listing_id: listingId,';
        echo 'nonce: hptDashboard.nonce';
        echo '}, function(response) {';
        echo 'if (response.success && response.data) {';
        echo 'var data = response.data;';
        echo '$("#inline-listing-form-data input[name=\\"title\\"]").val(data.title || "");';
        echo '$("#inline-listing-form-data input[name=\\"price\\"]").val(data.price || "");';
        echo '$("#inline-listing-form-data select[name=\\"status\\"]").val(data.status || "active");';
        echo '$("#inline-listing-form-data input[name=\\"bedrooms\\"]").val(data.bedrooms || "");';
        echo '$("#inline-listing-form-data input[name=\\"bathrooms\\"]").val(data.bathrooms || "");';
        echo '$("#inline-listing-form-data input[name=\\"square_feet\\"]").val(data.square_feet || "");';
        echo '$("#inline-listing-form-data input[name=\\"address\\"]").val(data.address || "");';
        echo '$("#inline-listing-form-data input[name=\\"city\\"]").val(data.city || "");';
        echo '$("#inline-listing-form-data select[name=\\"state\\"]").val(data.state || "");';
        echo '$("#inline-listing-form-data input[name=\\"zip_code\\"]").val(data.zip_code || "");';
        echo '$("#inline-listing-form-data textarea[name=\\"description\\"]").val(data.description || "");';
        echo '$("#inline-listing-form-data input[name=\\"featured\\"]").prop("checked", data.featured || false);';
        echo '}';
        echo '});';
        echo '}';
        
        echo '$("#inline-listing-form-data").on("submit", function(e) {';
        echo 'e.preventDefault();';
        echo 'var formData = $(this).serialize();';
        echo 'formData += "&action=hpt_dashboard_action&dashboard_action=save_listing&nonce=" + hptDashboard.nonce;';
        echo '$.post(hptDashboard.ajaxUrl, formData, function(response) {';
        echo 'if (response.success) {';
        echo 'hptShowNotice("success", response.data.message);';
        echo 'closeInlineForm();';
        echo 'listingsTable.ajax.reload(null, false);';
        echo '} else {';
        echo 'hptShowNotice("error", response.data.message);';
        echo '}';
        echo '}).fail(function() {';
        echo 'hptShowNotice("error", "An error occurred. Please try again.");';
        echo '});';
        echo '});';
        echo '}';

        echo 'window.closeInlineForm = function() {';
        echo '$("#inline-listing-form").remove();';
        echo '};';
        
        echo '});';
        echo '</script>';
        
        // Add CSS for inline form
        echo '<style>';
        echo '.hpt-inline-form-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; display: flex; align-items: center; justify-content: center; }';
        echo '.hpt-inline-form-container { background: white; width: 90%; max-width: 800px; max-height: 90vh; overflow-y: auto; border-radius: 8px; }';
        echo '.hpt-inline-form-header { display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid #ddd; background: #f9f9f9; }';
        echo '.hpt-inline-form-header h3 { margin: 0; }';
        echo '.hpt-close-form { background: none; border: none; font-size: 24px; cursor: pointer; padding: 0; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; }';
        echo '.hpt-inline-form-container form { padding: 20px; }';
        echo '.hpt-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }';
        echo '.hpt-form__group--full { grid-column: 1 / -1; }';
        echo '.hpt-form__label { display: block; margin-bottom: 5px; font-weight: 500; }';
        echo '.hpt-form__input, .hpt-form__select, .hpt-form__textarea { width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; }';
        echo '.hpt-form__actions { margin-top: 20px; display: flex; gap: 10px; }';
        echo '.hpt-notice { padding: 15px; margin-bottom: 20px; border-radius: 4px; }';
        echo '.notice-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }';
        echo '.notice-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }';
        echo '.required { color: #dc3545; }';
        echo '</style>';
    }

    private function render_openhouse_form($listing_id = null): void {
        if (!$listing_id) {
            echo '<div class="hpt-error">Invalid listing ID provided.</div>';
            return;
        }
        
        $listing = get_post($listing_id);
        if (!$listing || $listing->post_type !== 'listing') {
            echo '<div class="hpt-error">Listing not found.</div>';
            return;
        }
        
        // Get existing open houses for this listing
        $existing_openhouses = get_posts([
            'post_type' => 'open_house',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => 'listing',
                    'value' => $listing_id,
                    'compare' => '='
                ]
            ],
            'orderby' => 'meta_value',
            'meta_key' => 'start_date',
            'order' => 'ASC'
        ]);

        echo '<div class="hpt-openhouse-form-section">';
        
        // Header
        echo '<div class="hpt-section-header">';
        echo '<div class="hpt-section-header__left">';
        echo '<h2>Schedule Open House</h2>';
        echo '<p>Schedule open house events for: <strong>' . esc_html(get_field('street_address', $listing_id) ?: $listing->post_title) . '</strong></p>';
        echo '</div>';
        echo '<div class="hpt-section-header__right">';
        echo '<a href="' . esc_url(home_url('/agent-dashboard/listings/')) . '" class="hpt-button hpt-button--outline">';
        echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="margin-right: 8px;"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>';
        echo 'Back to Listings';
        echo '</a>';
        echo '</div>';
        echo '</div>';

        // Existing Open Houses
        if (!empty($existing_openhouses)) {
            echo '<div class="hpt-existing-openhouses hpt-card">';
            echo '<div class="hpt-card__header">';
            echo '<h3>Scheduled Open Houses</h3>';
            echo '</div>';
            echo '<div class="hpt-card__body">';
            echo '<div class="hpt-openhouse-list">';
            
            foreach ($existing_openhouses as $openhouse) {
                $start_date = get_field('start_date', $openhouse->ID);
                $end_date = get_field('end_date', $openhouse->ID);
                $start_time = get_field('start_time', $openhouse->ID);
                $end_time = get_field('end_time', $openhouse->ID);
                
                echo '<div class="hpt-openhouse-item">';
                echo '<div class="hpt-openhouse-details">';
                echo '<div class="hpt-openhouse-date">' . date('l, F j, Y', strtotime($start_date)) . '</div>';
                echo '<div class="hpt-openhouse-time">' . date('g:i A', strtotime($start_time)) . ' - ' . date('g:i A', strtotime($end_time)) . '</div>';
                echo '</div>';
                echo '<div class="hpt-openhouse-actions">';
                echo '<button class="hpt-button hpt-button--sm hpt-button--outline edit-openhouse" data-openhouse-id="' . $openhouse->ID . '">Edit</button>';
                echo '<button class="hpt-button hpt-button--sm hpt-button--danger delete-openhouse" data-openhouse-id="' . $openhouse->ID . '">Delete</button>';
                echo '</div>';
                echo '</div>';
            }
            
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }

        // New Open House Form
        echo '<form id="openhouse-form" class="hpt-openhouse-form hpt-card">';
        echo '<div class="hpt-card__header">';
        echo '<h3>Schedule New Open House</h3>';
        echo '</div>';
        echo '<div class="hpt-card__body">';

        echo '<input type="hidden" name="listing_id" value="' . esc_attr($listing_id) . '">';
        echo '<input type="hidden" name="openhouse_id" value="0">';
        
        echo '<div class="hpt-form-grid">';
        
        // Date fields
        echo '<div class="hpt-form__group">';
        echo '<label for="openhouse-start-date" class="hpt-form__label">Date <span class="required">*</span></label>';
        echo '<input type="date" id="openhouse-start-date" name="start_date" class="hpt-form__input" required>';
        echo '</div>';
        
        // Time fields
        echo '<div class="hpt-form__group">';
        echo '<label for="openhouse-start-time" class="hpt-form__label">Start Time <span class="required">*</span></label>';
        echo '<input type="time" id="openhouse-start-time" name="start_time" class="hpt-form__input" required>';
        echo '</div>';
        
        echo '<div class="hpt-form__group">';
        echo '<label for="openhouse-end-time" class="hpt-form__label">End Time <span class="required">*</span></label>';
        echo '<input type="time" id="openhouse-end-time" name="end_time" class="hpt-form__input" required>';
        echo '</div>';
        
        // Description
        echo '<div class="hpt-form__group hpt-form__group--full">';
        echo '<label for="openhouse-description" class="hpt-form__label">Description (Optional)</label>';
        echo '<textarea id="openhouse-description" name="description" class="hpt-form__textarea" rows="3" placeholder="Special instructions or highlights for visitors..."></textarea>';
        echo '</div>';
        
        // Options
        echo '<div class="hpt-form__group hpt-form__group--full">';
        echo '<label class="hpt-form__checkbox">';
        echo '<input type="checkbox" name="send_notifications" value="1" checked>';
        echo '<span class="hpt-form__checkbox-text">Send email notifications to interested prospects</span>';
        echo '</label>';
        echo '</div>';
        
        echo '</div>';

        // Form Actions
        echo '<div class="hpt-form__actions">';
        echo '<button type="submit" class="hpt-button hpt-button--primary">Schedule Open House</button>';
        echo '<button type="button" id="clear-form" class="hpt-button hpt-button--secondary">Clear Form</button>';
        echo '</div>';

        echo '</div>';
        echo '</form>';
        
        echo '</div>';

        // Form submission script
        $this->render_openhouse_form_script();
    }

    private function render_openhouse_form_script(): void {
        echo '<script>';
        echo 'jQuery(document).ready(function($) {';
        
        // Form submission
        echo '$("#openhouse-form").on("submit", function(e) {';
        echo 'e.preventDefault();';
        echo 'var formData = $(this).serialize();';
        echo 'formData += "&action=hpt_dashboard_action&dashboard_action=schedule_openhouse&nonce=" + hptDashboard.nonce;';
        echo '$.post(hptDashboard.ajaxUrl, formData, function(response) {';
        echo 'if (response.success) {';
        echo 'hptShowNotice("success", response.data.message);';
        echo '$("#openhouse-form")[0].reset();';
        echo 'if (response.data.redirect) {';
        echo 'setTimeout(function() { window.location.reload(); }, 2000);';
        echo '}';
        echo '} else {';
        echo 'hptShowNotice("error", response.data.message);';
        echo '}';
        echo '}).fail(function() {';
        echo 'hptShowNotice("error", "An error occurred. Please try again.");';
        echo '});';
        echo '});';
        
        // Clear form
        echo '$("#clear-form").on("click", function() {';
        echo '$("#openhouse-form")[0].reset();';
        echo '});';
        
        // Edit existing open house
        echo '$(document).on("click", ".edit-openhouse", function() {';
        echo 'var openhouseId = $(this).data("openhouse-id");';
        echo '// Load existing data via AJAX';
        echo '$.post(hptDashboard.ajaxUrl, {';
        echo 'action: "hpt_dashboard_data",';
        echo 'section: "listings",';
        echo 'action_type: "get_openhouse_data",';
        echo 'openhouse_id: openhouseId,';
        echo 'nonce: hptDashboard.nonce';
        echo '}, function(response) {';
        echo 'if (response.success && response.data) {';
        echo 'var data = response.data;';
        echo '$("#openhouse-start-date").val(data.start_date);';
        echo '$("#openhouse-start-time").val(data.start_time);';
        echo '$("#openhouse-end-time").val(data.end_time);';
        echo '$("#openhouse-description").val(data.description);';
        echo '$("input[name=openhouse_id]").val(openhouseId);';
        echo '$("button[type=submit]").text("Update Open House");';
        echo '}';
        echo '});';
        echo '});';
        
        // Delete open house
        echo '$(document).on("click", ".delete-openhouse", function() {';
        echo 'if (!confirm("Are you sure you want to delete this open house?")) return;';
        echo 'var openhouseId = $(this).data("openhouse-id");';
        echo '$.post(hptDashboard.ajaxUrl, {';
        echo 'action: "hpt_dashboard_action",';
        echo 'dashboard_action: "delete_openhouse",';
        echo 'openhouse_id: openhouseId,';
        echo 'nonce: hptDashboard.nonce';
        echo '}, function(response) {';
        echo 'if (response.success) {';
        echo 'hptShowNotice("success", response.data.message);';
        echo 'setTimeout(function() { window.location.reload(); }, 1500);';
        echo '} else {';
        echo 'hptShowNotice("error", response.data.message);';
        echo '}';
        echo '});';
        echo '});';
        
        echo '});';
        echo '</script>';
    }

    public function handle_ajax_get_table_data($data): array {
        $agent_id = (int) ($data['agent_id'] ?? 0);
        $filters = $data['filters'] ?? [];
        
        if (!$agent_id) {
            return ['success' => false, 'message' => 'Invalid agent ID'];
        }

        $meta_query = [
            [
                'key' => 'listing_agent',
                'value' => '"' . $agent_id . '"',
                'compare' => 'LIKE'
            ]
        ];

        // Apply filters
        if (!empty($filters['status'])) {
            $meta_query[] = [
                'key' => 'listing_status',
                'value' => $filters['status'],
                'compare' => '='
            ];
        }

        if (!empty($filters['featured'])) {
            $meta_query[] = [
                'key' => 'featured_listing',
                'value' => $filters['featured'] === '1' ? '1' : '0',
                'compare' => '='
            ];
        }

        if (!empty($filters['price'])) {
            $price_range = explode('-', $filters['price']);
            if (count($price_range) === 2) {
                $price_query = ['key' => 'price'];
                if ($price_range[0]) {
                    $price_query['value'] = [(int) $price_range[0], (int) $price_range[1]];
                    $price_query['type'] = 'NUMERIC';
                    $price_query['compare'] = 'BETWEEN';
                } else {
                    $price_query['value'] = (int) $price_range[1];
                    $price_query['type'] = 'NUMERIC';
                    $price_query['compare'] = '>=';
                }
                $meta_query[] = $price_query;
            }
        }

        $listings = get_posts([
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'meta_query' => $meta_query,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);

        $table_data = [];
        
        foreach ($listings as $listing) {
            $price = get_field('price', $listing->ID);
            $status = get_field('listing_status', $listing->ID);
            $bedrooms = get_field('bedrooms', $listing->ID);
            $bathrooms = get_field('bathrooms', $listing->ID);
            $sqft = get_field('square_feet', $listing->ID);
            $featured = get_field('featured_listing', $listing->ID);
            $address = get_field('street_address', $listing->ID);
            $city = get_field('city', $listing->ID);
            $state = get_field('state', $listing->ID);
            
            $days_on_market = (int) ((current_time('timestamp') - strtotime($listing->post_date)) / DAY_IN_SECONDS);
            
            // Property column
            $property_html = '<div class="hpt-listing-cell">';
            $property_html .= '<div class="hpt-listing-title">' . esc_html($address ?: $listing->post_title) . '</div>';
            if ($city && $state) {
                $property_html .= '<div class="hpt-listing-location">' . esc_html($city . ', ' . $state) . '</div>';
            }
            if ($featured) {
                $property_html .= '<span class="hpt-badge hpt-badge--featured">Featured</span>';
            }
            $property_html .= '</div>';
            
            // Actions column
            $actions_html = '<div class="hpt-table-actions">';
            $actions_html .= '<a href="' . esc_url(get_permalink($listing->ID)) . '" target="_blank" class="hpt-button hpt-button--sm hpt-button--outline" title="View Listing">';
            $actions_html .= '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>';
            $actions_html .= '</a>';
            
            $actions_html .= '<button class="hpt-button hpt-button--sm hpt-button--outline edit-listing" data-listing-id="' . $listing->ID . '" title="Edit Listing">';
            $actions_html .= '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>';
            $actions_html .= '</button>';
            
            $actions_html .= '<button class="hpt-button hpt-button--sm hpt-button--outline schedule-openhouse" data-listing-id="' . $listing->ID . '" title="Schedule Open House">';
            $actions_html .= '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg>';
            $actions_html .= '</button>';
            
            $actions_html .= '<div class="hpt-table-actions-dropdown">';
            $actions_html .= '<button class="hpt-button hpt-button--sm hpt-button--outline hpt-dropdown-toggle" title="More Actions">';
            $actions_html .= '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M6 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm12 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm-6 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>';
            $actions_html .= '</button>';
            $actions_html .= '<div class="hpt-dropdown-menu">';
            $actions_html .= '<button class="hpt-dropdown-item toggle-featured" data-listing-id="' . $listing->ID . '">';
            $actions_html .= '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>';
            $actions_html .= ($featured ? 'Remove from Featured' : 'Mark as Featured');
            $actions_html .= '</button>';
            $actions_html .= '<button class="hpt-dropdown-item delete-listing" data-listing-id="' . $listing->ID . '">';
            $actions_html .= '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>';
            $actions_html .= 'Delete Listing';
            $actions_html .= '</button>';
            $actions_html .= '</div>';
            $actions_html .= '</div>';
            $actions_html .= '</div>';

            $table_data[] = [
                'property' => $property_html,
                'price' => $price ? '$' . number_format($price) : 'N/A',
                'status' => '<span class="hpt-listing-status hpt-listing-status--' . esc_attr($status) . '">' . esc_html(ucfirst(str_replace('_', ' ', $status))) . '</span>',
                'beds_baths' => ($bedrooms ? $bedrooms . ' bed' : '') . ($bedrooms && $bathrooms ? ', ' : '') . ($bathrooms ? $bathrooms . ' bath' : ''),
                'sqft' => $sqft ? number_format($sqft) : 'N/A',
                'days_on_market' => $days_on_market,
                'views' => rand(10, 100), // Would be actual view count from analytics
                'actions' => $actions_html
            ];
        }

        return [
            'success' => true,
            'data' => $table_data
        ];
    }

    public function handle_ajax_get_openhouse_data($data): array {
        $openhouse_id = (int) ($data['openhouse_id'] ?? 0);
        
        if (!$openhouse_id) {
            return ['success' => false, 'message' => 'Invalid open house ID'];
        }

        $openhouse = get_post($openhouse_id);
        if (!$openhouse || $openhouse->post_type !== 'open_house') {
            return ['success' => false, 'message' => 'Open house not found'];
        }

        $openhouse_data = [
            'start_date' => get_field('start_date', $openhouse_id),
            'start_time' => get_field('start_time', $openhouse_id),
            'end_time' => get_field('end_time', $openhouse_id),
            'description' => $openhouse->post_content,
        ];

        return [
            'success' => true,
            'data' => $openhouse_data
        ];
    }

    public function handle_ajax_get_listing_data($data): array {
        $listing_id = (int) ($data['listing_id'] ?? 0);
        
        if (!$listing_id) {
            return ['success' => false, 'message' => 'Invalid listing ID'];
        }

        $listing = get_post($listing_id);
        if (!$listing || $listing->post_type !== 'listing') {
            return ['success' => false, 'message' => 'Listing not found'];
        }

        // Verify ownership
        $agent_id = $this->dashboard_manager->get_current_agent_id();
        $listing_agent = get_field('listing_agent', $listing_id);
        
        if (!$listing_agent || !in_array($agent_id, wp_list_pluck((array)$listing_agent, 'ID'))) {
            return ['success' => false, 'message' => 'You do not have permission to edit this listing'];
        }

        $listing_data = [
            'title' => $listing->post_title,
            'description' => $listing->post_content,
            'price' => get_field('price', $listing_id),
            'status' => get_field('listing_status', $listing_id) ?: 'active',
            'bedrooms' => get_field('bedrooms', $listing_id),
            'bathrooms' => get_field('bathrooms', $listing_id),
            'square_feet' => get_field('square_feet', $listing_id),
            'address' => get_field('street_address', $listing_id),
            'city' => get_field('city', $listing_id),
            'state' => get_field('state', $listing_id),
            'zip_code' => get_field('zip_code', $listing_id),
            'featured' => get_field('featured_listing', $listing_id) ? true : false,
        ];

        return [
            'success' => true,
            'data' => $listing_data
        ];
    }
}