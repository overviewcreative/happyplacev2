<?php
/**
 * Transactions Dashboard Page
 * 
 * @package HappyPlaceTheme
 * @subpackage Dashboard
 * 
 * File Location: /wp-content/themes/happy-place/templates/dashboard/dashboard-transactions.php
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

<div class="transactions-page" id="transactionsPage">
    <!-- Page Header with Stats -->
    <div class="page-header">
        <div class="header-stats">
            <div class="stat-item">
                <div class="stat-icon stat-icon-primary">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value" id="activeTransactions">-</h3>
                    <p class="stat-label">Active Deals</p>
                </div>
            </div>
            
            <div class="stat-item">
                <div class="stat-icon stat-icon-success">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value" id="closedDeals">-</h3>
                    <p class="stat-label">Closed This Month</p>
                </div>
            </div>
            
            <div class="stat-item">
                <div class="stat-icon stat-icon-warning">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value" id="totalCommission">-</h3>
                    <p class="stat-label">Commission YTD</p>
                </div>
            </div>
            
            <div class="stat-item">
                <div class="stat-icon stat-icon-info">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M2 12C2 6.48 6.48 2 12 2s10 4.48 10 10-4.48 10-10 10S2 17.52 2 12zm4.64-1.96l1.414 1.414L10 9.517l2.946 2.946 1.414-1.414L12 8.69 9.64 10.04z"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value" id="avgDealTime">-</h3>
                    <p class="stat-label">Avg Days to Close</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pipeline Overview -->
    <div class="pipeline-overview">
        <h2 class="pipeline-title">Deal Pipeline</h2>
        <div class="pipeline-stages">
            <div class="pipeline-stage" data-stage="prospect">
                <div class="stage-header">
                    <h3 class="stage-title">Prospects</h3>
                    <span class="stage-count" id="prospectCount">0</span>
                </div>
                <div class="stage-content" id="prospectDeals">
                    <!-- Deals will be loaded here -->
                </div>
            </div>
            
            <div class="pipeline-stage" data-stage="under_contract">
                <div class="stage-header">
                    <h3 class="stage-title">Under Contract</h3>
                    <span class="stage-count" id="contractCount">0</span>
                </div>
                <div class="stage-content" id="contractDeals">
                    <!-- Deals will be loaded here -->
                </div>
            </div>
            
            <div class="pipeline-stage" data-stage="pending">
                <div class="stage-header">
                    <h3 class="stage-title">Pending</h3>
                    <span class="stage-count" id="pendingCount">0</span>
                </div>
                <div class="stage-content" id="pendingDeals">
                    <!-- Deals will be loaded here -->
                </div>
            </div>
            
            <div class="pipeline-stage" data-stage="closing">
                <div class="stage-header">
                    <h3 class="stage-title">Closing Soon</h3>
                    <span class="stage-count" id="closingCount">0</span>
                </div>
                <div class="stage-content" id="closingDeals">
                    <!-- Deals will be loaded here -->
                </div>
            </div>
            
            <div class="pipeline-stage stage-closed" data-stage="closed">
                <div class="stage-header">
                    <h3 class="stage-title">Closed</h3>
                    <span class="stage-count" id="closedCount">0</span>
                </div>
                <div class="stage-content" id="closedDeals">
                    <!-- Recent closed deals -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Controls and Filters -->
    <div class="page-controls">
        <div class="controls-left">
            <div class="filter-group">
                <select class="form-select" id="statusFilter">
                    <option value="">All Statuses</option>
                    <option value="prospect">Prospects</option>
                    <option value="under_contract">Under Contract</option>
                    <option value="pending">Pending</option>
                    <option value="closing">Closing Soon</option>
                    <option value="closed">Closed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            
            <div class="filter-group">
                <select class="form-select" id="typeFilter">
                    <option value="">All Types</option>
                    <option value="buyer">Buyer Rep</option>
                    <option value="seller">Listing</option>
                    <option value="dual">Dual Agency</option>
                </select>
            </div>
            
            <div class="filter-group">
                <select class="form-select" id="timeFilter">
                    <option value="all">All Time</option>
                    <option value="this_month">This Month</option>
                    <option value="last_30">Last 30 Days</option>
                    <option value="this_quarter">This Quarter</option>
                    <option value="this_year">This Year</option>
                </select>
            </div>
            
            <button class="btn btn-outline btn-sm" id="clearFilters">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                    <path d="m4.646 4.646.708.708L8 8l2.646-2.646.708.708L8.708 8.5l2.646 2.646-.708.708L8 9.208l-2.646 2.646-.708-.708L7.292 8.5 4.646 5.854z"/>
                </svg>
                Clear
            </button>
        </div>
        
        <div class="controls-right">
            <button class="btn btn-outline btn-sm" id="exportTransactions">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M8.5 1.5A1.5 1.5 0 0 1 10 0h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h6c-.314.418-.5.937-.5 1.5v6h-2L8 10.5 10.5 8H9V1.5z"/>
                </svg>
                Export
            </button>
            
            <button class="btn btn-outline btn-sm" id="commissionCalc">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5v-3zM2.5 2a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zM1 10.5A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3z"/>
                </svg>
                Calculator
            </button>
            
            <button class="btn btn-primary btn-sm" id="addTransactionBtn">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                </svg>
                Add Transaction
            </button>
        </div>
    </div>
    
    <!-- Detailed Transactions List -->
    <div class="transactions-content">
        <!-- Loading State -->
        <div class="loading-container" id="transactionsLoading">
            <div class="loading-spinner">
                <svg class="spinner" width="48" height="48" viewBox="0 0 48 48">
                    <circle cx="24" cy="24" r="20" stroke="var(--hph-primary)" stroke-width="3" fill="none" stroke-dasharray="125.66" stroke-dashoffset="94.245" stroke-linecap="round">
                        <animateTransform attributeName="transform" type="rotate" from="0 24 24" to="360 24 24" dur="1s" repeatCount="indefinite"/>
                    </circle>
                </svg>
            </div>
            <p>Loading transactions...</p>
        </div>
        
        <!-- Empty State -->
        <div class="empty-state" id="emptyState" style="display: none;">
            <div class="empty-icon">
                <svg width="64" height="64" viewBox="0 0 64 64" fill="currentColor" opacity="0.3">
                    <path d="M8 8h14v14H8V8zm18 0h14v14H26V8zM8 26h14v14H8V26zm18 0h14v14H26V26z"/>
                </svg>
            </div>
            <h3 class="empty-title">No transactions yet</h3>
            <p class="empty-message">Track your deals from prospect to closing. Add your first transaction to get started!</p>
            <button class="btn btn-primary" id="addFirstTransaction">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                </svg>
                Add Your First Transaction
            </button>
        </div>
        
        <!-- Transactions Table -->
        <div class="transactions-table-container" id="transactionsTableContainer" style="display: none;">
            <div class="table-header">
                <h3>All Transactions</h3>
                <div class="table-actions">
                    <button class="btn btn-outline btn-sm" id="toggleView">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                            <path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5v-3zM2.5 2a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zM1 10.5A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3z"/>
                        </svg>
                        Card View
                    </button>
                </div>
            </div>
            
            <div class="transactions-table-wrapper">
                <table class="transactions-table" id="transactionsTable">
                    <thead>
                        <tr>
                            <th>Property</th>
                            <th>Client</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Sale Price</th>
                            <th>Commission</th>
                            <th>Close Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="transactionsTableBody">
                        <!-- Transactions will be rendered here -->
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Transactions Cards -->
        <div class="transactions-cards" id="transactionsCards" style="display: none;">
            <!-- Cards will be rendered here -->
        </div>
    </div>
    
    <!-- Pagination -->
    <div class="pagination-wrapper" id="transactionsPagination" style="display: none;">
        <div class="pagination-info">
            <span id="paginationInfo">Showing 0-0 of 0 transactions</span>
        </div>
        <div class="pagination-controls">
            <button class="btn btn-outline btn-sm" id="prevPage" disabled>
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                </svg>
                Previous
            </button>
            <span id="pageNumbers" class="page-numbers">
                <!-- Page numbers will be inserted here -->
            </span>
            <button class="btn btn-outline btn-sm" id="nextPage" disabled>
                Next
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<!-- Transaction Details Modal -->
<div class="modal" id="transactionDetailsModal">
    <div class="modal-content modal-xl">
        <div class="modal-header">
            <h2 class="modal-title">Transaction Details</h2>
            <button class="modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body" id="transactionDetailsContent">
            <!-- Details will be loaded here -->
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" data-dismiss="modal">Close</button>
            <button class="btn btn-primary" id="editTransactionBtn">Edit Transaction</button>
        </div>
    </div>
</div>

<!-- Add/Edit Transaction Modal -->
<div class="modal" id="transactionFormModal">
    <div class="modal-content modal-xl">
        <div class="modal-header">
            <h2 class="modal-title" id="transactionFormTitle">Add Transaction</h2>
            <button class="modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="transactionForm" class="transaction-form">
                <input type="hidden" id="transactionId" name="transaction_id" value="">
                
                <!-- Basic Information -->
                <div class="form-section">
                    <h3 class="section-title">Basic Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="transactionType" class="form-label required">Transaction Type</label>
                            <select id="transactionType" name="transaction_type" class="form-select" required>
                                <option value="">Select type...</option>
                                <option value="buyer">Buyer Representation</option>
                                <option value="seller">Listing (Seller Rep)</option>
                                <option value="dual">Dual Agency</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="transactionStatus" class="form-label required">Status</label>
                            <select id="transactionStatus" name="status" class="form-select" required>
                                <option value="prospect">Prospect</option>
                                <option value="under_contract">Under Contract</option>
                                <option value="pending">Pending</option>
                                <option value="closing">Closing Soon</option>
                                <option value="closed">Closed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Property Information -->
                <div class="form-section">
                    <h3 class="section-title">Property Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="propertyAddress" class="form-label required">Property Address</label>
                            <input type="text" id="propertyAddress" name="property_address" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="listingId" class="form-label">Associated Listing</label>
                            <select id="listingId" name="listing_id" class="form-select">
                                <option value="">Not associated with listing</option>
                                <!-- Listings will be loaded dynamically -->
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Client Information -->
                <div class="form-section">
                    <h3 class="section-title">Client Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="clientName" class="form-label required">Client Name</label>
                            <input type="text" id="clientName" name="client_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="clientEmail" class="form-label">Client Email</label>
                            <input type="email" id="clientEmail" name="client_email" class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="clientPhone" class="form-label">Client Phone</label>
                            <input type="tel" id="clientPhone" name="client_phone" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="coClientName" class="form-label">Co-Client Name</label>
                            <input type="text" id="coClientName" name="co_client_name" class="form-control">
                        </div>
                    </div>
                </div>
                
                <!-- Financial Information -->
                <div class="form-section">
                    <h3 class="section-title">Financial Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="salePrice" class="form-label">Sale Price</label>
                            <input type="number" id="salePrice" name="sale_price" class="form-control" step="0.01" min="0">
                        </div>
                        <div class="form-group">
                            <label for="commissionRate" class="form-label">Commission Rate (%)</label>
                            <input type="number" id="commissionRate" name="commission_rate" class="form-control" step="0.01" min="0" max="100">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="commissionAmount" class="form-label">Commission Amount</label>
                            <input type="number" id="commissionAmount" name="commission_amount" class="form-control" step="0.01" min="0" readonly>
                        </div>
                        <div class="form-group">
                            <label for="brokerageSplit" class="form-label">Brokerage Split (%)</label>
                            <input type="number" id="brokerageSplit" name="brokerage_split" class="form-control" step="0.01" min="0" max="100" value="50">
                        </div>
                    </div>
                </div>
                
                <!-- Important Dates -->
                <div class="form-section">
                    <h3 class="section-title">Important Dates</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="contractDate" class="form-label">Contract Date</label>
                            <input type="date" id="contractDate" name="contract_date" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="expectedCloseDate" class="form-label">Expected Close Date</label>
                            <input type="date" id="expectedCloseDate" name="expected_close_date" class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="actualCloseDate" class="form-label">Actual Close Date</label>
                            <input type="date" id="actualCloseDate" name="actual_close_date" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="inspectionDate" class="form-label">Inspection Date</label>
                            <input type="date" id="inspectionDate" name="inspection_date" class="form-control">
                        </div>
                    </div>
                </div>
                
                <!-- Additional Information -->
                <div class="form-section">
                    <h3 class="section-title">Additional Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="leadSource" class="form-label">Lead Source</label>
                            <select id="leadSource" name="lead_source" class="form-select">
                                <option value="">Not specified</option>
                                <option value="referral">Referral</option>
                                <option value="website">Website</option>
                                <option value="social_media">Social Media</option>
                                <option value="open_house">Open House</option>
                                <option value="advertising">Advertising</option>
                                <option value="cold_call">Cold Call</option>
                                <option value="repeat_client">Repeat Client</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="referralSource" class="form-label">Referral Source</label>
                            <input type="text" id="referralSource" name="referral_source" class="form-control" 
                                   placeholder="Who referred this client?">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="transactionNotes" class="form-label">Notes</label>
                        <textarea id="transactionNotes" name="notes" class="form-control" rows="4" 
                                  placeholder="Add any additional information about this transaction..."></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" data-dismiss="modal">Cancel</button>
            <button type="submit" form="transactionForm" class="btn btn-primary" id="saveTransactionBtn">
                <span class="btn-text">Save Transaction</span>
                <span class="btn-loading" style="display: none;">
                    <svg class="spinner-sm" width="16" height="16" viewBox="0 0 16 16">
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

<!-- Commission Calculator Modal -->
<div class="modal" id="commissionCalculatorModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Commission Calculator</h2>
            <button class="modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="calculator-form">
                <div class="form-group">
                    <label for="calcSalePrice" class="form-label">Sale Price</label>
                    <input type="number" id="calcSalePrice" class="form-control" step="1" min="0" placeholder="500000">
                </div>
                
                <div class="form-group">
                    <label for="calcCommissionRate" class="form-label">Total Commission Rate (%)</label>
                    <input type="number" id="calcCommissionRate" class="form-control" step="0.01" min="0" max="100" value="6" placeholder="6.0">
                </div>
                
                <div class="form-group">
                    <label for="calcSplit" class="form-label">Your Split (%)</label>
                    <input type="number" id="calcSplit" class="form-control" step="0.01" min="0" max="100" value="50" placeholder="50">
                </div>
                
                <div class="calculation-results">
                    <div class="result-item">
                        <label>Total Commission:</label>
                        <span class="result-value" id="totalCommissionResult">$0</span>
                    </div>
                    <div class="result-item">
                        <label>Your Commission:</label>
                        <span class="result-value result-highlight" id="yourCommissionResult">$0</span>
                    </div>
                    <div class="result-item">
                        <label>Brokerage Commission:</label>
                        <span class="result-value" id="brokerageCommissionResult">$0</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" data-dismiss="modal">Close</button>
        </div>
    </div>
</div>