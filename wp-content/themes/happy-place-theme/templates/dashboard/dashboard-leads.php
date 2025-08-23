<?php
/**
 * Lead Management Dashboard Page
 * 
 * @package HappyPlaceTheme
 * @subpackage Dashboard
 * 
 * File Location: /wp-content/themes/happy-place/templates/dashboard/dashboard-leads.php
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

<div class="hph-leads-page" id="leadsPage">
    <!-- Page Header with Stats -->
    <div class="hph-page-header">
        <div class="hph-header-stats">
            <div class="hph-stat-item">
                <div class="hph-stat-icon hph-stat-icon-primary">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div class="hph-stat-content">
                    <h3 class="hph-stat-value" id="totalLeads">-</h3>
                    <p class="hph-stat-label">Total Leads</p>
                </div>
            </div>
            
            <div class="hph-stat-item">
                <div class="hph-stat-icon hph-stat-icon-success">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="hph-stat-content">
                    <h3 class="hph-stat-value" id="hotLeads">-</h3>
                    <p class="hph-stat-label">Hot Leads</p>
                </div>
            </div>
            
            <div class="hph-stat-item">
                <div class="hph-stat-icon hph-stat-icon-warning">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                    </svg>
                </div>
                <div class="hph-stat-content">
                    <h3 class="hph-stat-value" id="followUpRequired">-</h3>
                    <p class="hph-stat-label">Need Follow-up</p>
                </div>
            </div>
            
            <div class="hph-stat-item">
                <div class="hph-stat-icon hph-stat-icon-info">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                    </svg>
                </div>
                <div class="hph-stat-content">
                    <h3 class="hph-stat-value" id="conversionRate">-</h3>
                    <p class="hph-stat-label">Conversion Rate</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filters and Controls -->
    <div class="hph-page-controls">
        <div class="hph-controls-left">
            <div class="hph-filter-group">
                <select class="hph-form-select" id="leadStatusFilter">
                    <option value="">All Statuses</option>
                    <option value="new">New</option>
                    <option value="contacted">Contacted</option>
                    <option value="qualified">Qualified</option>
                    <option value="nurturing">Nurturing</option>
                    <option value="converted">Converted</option>
                    <option value="lost">Lost</option>
                </select>
            </div>
            
            <div class="hph-filter-group">
                <select class="hph-form-select" id="leadSourceFilter">
                    <option value="">All Sources</option>
                    <option value="website">Website</option>
                    <option value="listing_inquiry">Listing Inquiry</option>
                    <option value="open_house">Open House</option>
                    <option value="referral">Referral</option>
                    <option value="social_media">Social Media</option>
                    <option value="zillow">Zillow</option>
                    <option value="realtor_com">Realtor.com</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div class="hph-filter-group">
                <select class="hph-form-select" id="leadScoreFilter">
                    <option value="">All Scores</option>
                    <option value="hot">Hot (80-100)</option>
                    <option value="warm">Warm (60-79)</option>
                    <option value="cold">Cold (0-59)</option>
                </select>
            </div>
            
            <button class="hph-btn hph-btn-outline hph-btn-sm" id="clearFilters">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                    <path d="m4.646 4.646.708.708L8 8l2.646-2.646.708.708L8.708 8.5l2.646 2.646-.708.708L8 9.208l-2.646 2.646-.708-.708L7.292 8.5 4.646 5.854z"/>
                </svg>
                Clear
            </button>
        </div>
        
        <div class="hph-controls-right">
            <div class="hph-view-toggle">
                <button class="hph-view-btn active" data-view="list" aria-label="List view">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M2 3h12a1 1 0 0 1 0 2H2a1 1 0 0 1 0-2zm0 4h12a1 1 0 0 1 0 2H2a1 1 0 0 1 0-2zm0 4h12a1 1 0 0 1 0 2H2a1 1 0 0 1 0-2z"/>
                    </svg>
                </button>
                <button class="hph-view-btn" data-view="grid" aria-label="Grid view">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5v-3zm8 0A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5v-3zm-8 8A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5v-3zm8 0A1.5 1.5 0 0 1 10.5 9h3A1.5 1.5 0 0 1 15 10.5v3A1.5 1.5 0 0 1 13.5 15h-3A1.5 1.5 0 0 1 9 13.5v-3z"/>
                    </svg>
                </button>
            </div>
            
            <button class="hph-btn hph-btn-outline hph-btn-sm" id="exportLeads">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M8.5 1.5A1.5 1.5 0 0 1 10 0h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h6c-.314.418-.5.937-.5 1.5v6h-2L8 10.5 10.5 8H9V1.5z"/>
                </svg>
                Export
            </button>
            
            <button class="hph-btn hph-btn-primary hph-btn-sm" id="addLeadBtn">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                </svg>
                Add Lead
            </button>
        </div>
    </div>
    
    <!-- Leads Content -->
    <div class="hph-leads-content">
        <!-- Loading State -->
        <div class="hph-loading-container" id="leadsLoading">
            <div class="hph-loading-hph-spinner">
                <svg class="hph-spinner" width="48" height="48" viewBox="0 0 48 48">
                    <circle cx="24" cy="24" r="20" stroke="var(--hph-primary)" stroke-width="3" fill="none" stroke-dasharray="125.66" stroke-dashoffset="94.245" stroke-linecap="round">
                        <animateTransform attributeName="transform" type="rotate" from="0 24 24" to="360 24 24" dur="1s" repeatCount="indefinite"/>
                    </circle>
                </svg>
            </div>
            <p>Loading leads...</p>
        </div>
        
        <!-- Empty State -->
        <div class="hph-empty-state" id="emptyState" style="display: none;">
            <div class="hph-empty-icon">
                <svg width="64" height="64" viewBox="0 0 64 64" fill="currentColor" opacity="0.3">
                    <path d="M32 16a8 8 0 100 16 8 8 0 000-16zm0 20c-8 0-16 4-16 10v2h32v-2c0-6-8-10-16-10z"/>
                </svg>
            </div>
            <h3 class="hph-empty-title">No leads yet</h3>
            <p class="hph-empty-message">When you capture leads, they'll appear here. Start by adding your first lead!</p>
            <button class="hph-btn hph-btn-primary" id="addFirstLead">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                </svg>
                Add Your First Lead
            </button>
        </div>
        
        <!-- Leads List -->
        <div class="hph-leads-list" id="leadsList" style="display: none;">
            <!-- Leads will be loaded here dynamically -->
        </div>
        
        <!-- Leads Grid -->
        <div class="hph-leads-grid" id="leadsGrid" style="display: none;">
            <!-- Leads will be loaded here dynamically -->
        </div>
    </div>
    
    <!-- Pagination -->
    <div class="hph-pagination-wrapper" id="leadsPagination" style="display: none;">
        <div class="hph-pagination-info">
            <span id="paginationInfo">Showing 0-0 of 0 leads</span>
        </div>
        <div class="hph-pagination-controls">
            <button class="hph-btn hph-btn-outline hph-btn-sm" id="prevPage" disabled>
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                </svg>
                Previous
            </button>
            <span id="pageNumbers" class="hph-page-numbers">
                <!-- Page numbers will be inserted here -->
            </span>
            <button class="hph-btn hph-btn-outline hph-btn-sm" id="nextPage" disabled>
                Next
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<!-- Lead Details hph-modal -->
<div class="hph-modal" id="leadDetailsModal">
    <div class="hph-modal-content hph-modal-lg">
        <div class="hph-modal-header">
            <h2 class="hph-modal-title">Lead Details</h2>
            <button class="hph-modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="hph-modal-body" id="leadDetailsContent">
            <!-- Lead details will be loaded here -->
        </div>
        <div class="hph-modal-footer">
            <button class="hph-btn hph-btn-outline" data-dismiss="hph-modal">Close</button>
            <button class="hph-btn hph-btn-primary" id="editLeadBtn">Edit Lead</button>
        </div>
    </div>
</div>

<!-- Add/Edit Lead hph-modal -->
<div class="hph-modal" id="leadFormModal">
    <div class="hph-modal-content hph-modal-lg">
        <div class="hph-modal-header">
            <h2 class="hph-modal-title" id="leadFormTitle">Add New Lead</h2>
            <button class="hph-modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="hph-modal-body">
            <form id="leadForm" class="hph-lead-form">
                <input type="hidden" id="leadId" name="lead_id" value="">
                
                <!-- Basic Information -->
                <div class="hph-form-section">
                    <h3 class="hph-section-title">Basic Information</h3>
                    <div class="form-row">
                        <div class="hph-form-group">
                            <label for="leadFirstName" class="hph-form-label required">First Name</label>
                            <input type="text" id="leadFirstName" name="first_name" class="hph-form-control" required>
                        </div>
                        <div class="hph-form-group">
                            <label for="leadLastName" class="hph-form-label required">Last Name</label>
                            <input type="text" id="leadLastName" name="last_name" class="hph-form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="hph-form-group">
                            <label for="leadEmail" class="hph-form-label required">Email</label>
                            <input type="email" id="leadEmail" name="email" class="hph-form-control" required>
                        </div>
                        <div class="hph-form-group">
                            <label for="leadPhone" class="hph-form-label">Phone</label>
                            <input type="tel" id="leadPhone" name="phone" class="hph-form-control">
                        </div>
                    </div>
                </div>
                
                <!-- Lead Details -->
                <div class="hph-form-section">
                    <h3 class="hph-section-title">Lead Details</h3>
                    <div class="form-row">
                        <div class="hph-form-group">
                            <label for="leadStatus" class="hph-form-label">Status</label>
                            <select id="leadStatus" name="status" class="hph-form-select">
                                <option value="new">New</option>
                                <option value="contacted">Contacted</option>
                                <option value="qualified">Qualified</option>
                                <option value="nurturing">Nurturing</option>
                                <option value="converted">Converted</option>
                                <option value="lost">Lost</option>
                            </select>
                        </div>
                        <div class="hph-form-group">
                            <label for="leadSource" class="hph-form-label">Source</label>
                            <select id="leadSource" name="source" class="hph-form-select">
                                <option value="website">Website</option>
                                <option value="listing_inquiry">Listing Inquiry</option>
                                <option value="open_house">Open House</option>
                                <option value="referral">Referral</option>
                                <option value="social_media">Social Media</option>
                                <option value="zillow">Zillow</option>
                                <option value="realtor_com">Realtor.com</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="hph-form-group">
                            <label for="leadScore" class="hph-form-label">Lead Score (0-100)</label>
                            <input type="number" id="leadScore" name="score" class="hph-form-control" min="0" max="100" value="50">
                        </div>
                        <div class="hph-form-group">
                            <label for="leadBudget" class="hph-form-label">Budget Range</label>
                            <select id="leadBudget" name="budget_range" class="hph-form-select">
                                <option value="">Not specified</option>
                                <option value="under_200k">Under $200K</option>
                                <option value="200k_400k">$200K - $400K</option>
                                <option value="400k_600k">$400K - $600K</option>
                                <option value="600k_800k">$600K - $800K</option>
                                <option value="800k_1m">$800K - $1M</option>
                                <option value="over_1m">Over $1M</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Property Preferences -->
                <div class="hph-form-section">
                    <h3 class="hph-section-title">Property Preferences</h3>
                    <div class="form-row">
                        <div class="hph-form-group">
                            <label for="leadPropertyType" class="hph-form-label">Property Type</label>
                            <select id="leadPropertyType" name="property_type" class="hph-form-select">
                                <option value="">Any</option>
                                <option value="single_family">Single Family Home</option>
                                <option value="townhouse">Townhouse</option>
                                <option value="condo">Condominium</option>
                                <option value="multi_family">Multi-Family</option>
                                <option value="land">Land/Lot</option>
                                <option value="commercial">Commercial</option>
                            </select>
                        </div>
                        <div class="hph-form-group">
                            <label for="leadBedrooms" class="hph-form-label">Bedrooms</label>
                            <select id="leadBedrooms" name="bedrooms" class="hph-form-select">
                                <option value="">Any</option>
                                <option value="1">1+</option>
                                <option value="2">2+</option>
                                <option value="3">3+</option>
                                <option value="4">4+</option>
                                <option value="5">5+</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="hph-form-group">
                        <label for="leadAreas" class="hph-form-label">Preferred Areas</label>
                        <input type="text" id="leadAreas" name="preferred_areas" class="hph-form-control" 
                               placeholder="e.g., Downtown, Westside, Cedar Park">
                    </div>
                </div>
                
                <!-- Notes -->
                <div class="hph-form-section">
                    <h3 class="hph-section-title">Notes</h3>
                    <div class="hph-form-group">
                        <label for="leadNotes" class="hph-form-label">Additional Notes</label>
                        <textarea id="leadNotes" name="notes" class="hph-form-control" rows="4" 
                                  placeholder="Add any additional information about this lead..."></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="hph-modal-footer">
            <button class="hph-btn hph-btn-outline" data-dismiss="hph-modal">Cancel</button>
            <button type="submit" form="leadForm" class="hph-btn hph-btn-primary" id="saveLeadBtn">
                <span class="hph-btn-text">Save Lead</span>
                <span class="hph-btn-loading" style="display: none;">
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

<!-- CRM Integration Status -->
<div class="hph-crm-status" id="crmStatus" style="display: none;">
    <div class="hph-status-indicator">
        <div class="hph-status-icon status-icon-success">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                <path d="M13.78 4.22a1 1 0 010 1.415l-5.5 5.5a1 1 0 01-1.414 0l-2.5-2.5a1 1 0 111.414-1.414L7.5 8.94l4.78-4.72a1 1 0 011.414 0z"/>
            </svg>
        </div>
        <span>FollowUpBoss Connected</span>
    </div>
</div>
