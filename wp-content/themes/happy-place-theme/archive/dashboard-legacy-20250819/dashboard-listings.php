<?php
/**
 * Dashboard Listings Management Template
 * 
 * @package HappyPlaceTheme
 * @subpackage Dashboard
 * 
 * File Location: /wp-content/themes/happy-place/templates/dashboard/dashboard-listings.php
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check user permissions
if (!is_user_logged_in() || !current_user_can('edit_posts')) {
    wp_redirect(home_url());
    exit;
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Get filter parameters
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';
$sort_by = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : 'date';
$search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

// Pagination
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$per_page = 12;
?>

<div class="listings-page" id="listingsPage">
    <!-- Page Header -->
    <div class="listings-header">
        <div class="header-content">
            <h1 class="page-title">My Listings</h1>
            <p class="page-subtitle">Manage your property listings</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-outline" id="importListingsBtn">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"/>
                </svg>
                <span>Import</span>
            </button>
            <button class="btn btn-outline" id="exportListingsBtn">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z"/>
                </svg>
                <span>Export</span>
            </button>
            <button class="btn btn-primary" id="addListingBtn">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/>
                </svg>
                <span>Add Listing</span>
            </button>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="listings-stats">
        <div class="stat-card stat-card-mini">
            <div class="stat-icon stat-icon-primary">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Listings</span>
                <span class="stat-value" id="totalListings">0</span>
            </div>
        </div>
        
        <div class="stat-card stat-card-mini">
            <div class="stat-icon stat-icon-success">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Active</span>
                <span class="stat-value" id="activeListings">0</span>
            </div>
        </div>
        
        <div class="stat-card stat-card-mini">
            <div class="stat-icon stat-icon-warning">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Pending</span>
                <span class="stat-value" id="pendingListings">0</span>
            </div>
        </div>
        
        <div class="stat-card stat-card-mini">
            <div class="stat-icon stat-icon-danger">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Sold</span>
                <span class="stat-value" id="soldListings">0</span>
            </div>
        </div>
        
        <div class="stat-card stat-card-mini">
            <div class="stat-icon stat-icon-info">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Average Days</span>
                <span class="stat-value" id="avgDaysOnMarket">0</span>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="listings-filters">
        <div class="filters-left">
            <!-- Status Filter -->
            <div class="filter-group">
                <label class="filter-label">Status:</label>
                <div class="filter-buttons" id="statusFilter">
                    <button class="filter-btn active" data-status="all">All</button>
                    <button class="filter-btn" data-status="active">Active</button>
                    <button class="filter-btn" data-status="pending">Pending</button>
                    <button class="filter-btn" data-status="sold">Sold</button>
                    <button class="filter-btn" data-status="coming-soon">Coming Soon</button>
                    <button class="filter-btn" data-status="off-market">Off Market</button>
                </div>
            </div>
            
            <!-- Sort Options -->
            <div class="filter-group">
                <label class="filter-label">Sort by:</label>
                <select class="filter-select" id="sortBy">
                    <option value="date-desc">Newest First</option>
                    <option value="date-asc">Oldest First</option>
                    <option value="price-desc">Price (High to Low)</option>
                    <option value="price-asc">Price (Low to High)</option>
                    <option value="title">Title (A-Z)</option>
                    <option value="status">Status</option>
                </select>
            </div>
        </div>
        
        <div class="filters-right">
            <!-- Search -->
            <div class="listings-search">
                <svg class="search-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                </svg>
                <input type="search" 
                       class="search-input" 
                       id="listingsSearch"
                       placeholder="Search by address, MLS#, or description..." 
                       value="<?php echo esc_attr($search_query); ?>">
            </div>
            
            <!-- View Toggle -->
            <div class="view-toggle">
                <button class="view-btn active" id="gridViewBtn" aria-label="Grid view">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM13 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2h-2z"/>
                    </svg>
                </button>
                <button class="view-btn" id="listViewBtn" aria-label="List view">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Bulk Actions Bar (hidden by default) -->
    <div class="bulk-actions-bar" id="bulkActionsBar" style="display: none;">
        <div class="bulk-actions-left">
            <input type="checkbox" id="selectAllListings" class="bulk-checkbox">
            <span class="bulk-selected-count">
                <span id="selectedCount">0</span> selected
            </span>
        </div>
        <div class="bulk-actions-right">
            <button class="btn btn-sm btn-outline" id="bulkEditBtn">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M11.013 2.513a1.75 1.75 0 012.475 2.475L6.226 12.25a2.751 2.751 0 01-.892.596l-2.047.848a.75.75 0 01-.98-.98l.848-2.047a2.751 2.751 0 01.596-.892l7.262-7.262z"/>
                </svg>
                Edit
            </button>
            <button class="btn btn-sm btn-outline" id="bulkExportBtn">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M2.75 14A1.75 1.75 0 011 12.25v-2.5a.75.75 0 011.5 0v2.5c0 .138.112.25.25.25h10.5a.25.25 0 00.25-.25v-2.5a.75.75 0 011.5 0v2.5A1.75 1.75 0 0113.25 14H2.75z"/>
                    <path d="M7.25 7.689V2a.75.75 0 011.5 0v5.689l1.97-1.969a.749.749 0 111.06 1.06l-3.25 3.25a.749.749 0 01-1.06 0L4.22 6.78a.749.749 0 111.06-1.06l1.97 1.969z"/>
                </svg>
                Export
            </button>
            <button class="btn btn-sm btn-outline-danger" id="bulkDeleteBtn">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M6.5 1.75a.25.25 0 01.25-.25h2.5a.25.25 0 01.25.25V3h-3V1.75zm4.5 0V3h2.25a.75.75 0 010 1.5H2.75a.75.75 0 010-1.5H5V1.75C5 .784 5.784 0 6.75 0h2.5C10.216 0 11 .784 11 1.75zM4.496 6.675a.75.75 0 10-1.492.15l.66 6.6A1.75 1.75 0 005.405 15h5.19c.9 0 1.652-.681 1.741-1.576l.66-6.6a.75.75 0 00-1.492-.149l-.66 6.6a.25.25 0 01-.249.225h-5.19a.25.25 0 01-.249-.225l-.66-6.6z"/>
                </svg>
                Delete
            </button>
        </div>
    </div>

    <!-- Listings Grid/List Container -->
    <div class="listings-container" id="listingsContainer">
        <div class="listings-grid" id="listingsGrid">
            <!-- Listings will be loaded here via AJAX -->
            <div class="loading-skeleton-grid">
                <div class="skeleton-card">
                    <div class="skeleton-image"></div>
                    <div class="skeleton-content">
                        <div class="skeleton-line"></div>
                        <div class="skeleton-line short"></div>
                        <div class="skeleton-line"></div>
                    </div>
                </div>
                <div class="skeleton-card">
                    <div class="skeleton-image"></div>
                    <div class="skeleton-content">
                        <div class="skeleton-line"></div>
                        <div class="skeleton-line short"></div>
                        <div class="skeleton-line"></div>
                    </div>
                </div>
                <div class="skeleton-card">
                    <div class="skeleton-image"></div>
                    <div class="skeleton-content">
                        <div class="skeleton-line"></div>
                        <div class="skeleton-line short"></div>
                        <div class="skeleton-line"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Empty State -->
        <div class="empty-state" id="emptyState" style="display: none;">
            <div class="empty-state-icon">
                <svg width="64" height="64" viewBox="0 0 64 64" fill="currentColor" opacity="0.3">
                    <path d="M32 8l4 4v8h8l4 4v32H16V24l4-4h8v-8l4-4z"/>
                </svg>
            </div>
            <h3 class="empty-state-title">No listings found</h3>
            <p class="empty-state-text">Start by adding your first property listing</p>
            <button class="btn btn-primary" onclick="document.getElementById('addListingBtn').click()">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/>
                </svg>
                Add Your First Listing
            </button>
        </div>
    </div>

    <!-- Pagination -->
    <div class="listings-pagination" id="listingsPagination">
        <div class="pagination-info">
            Showing <span id="showingStart">1</span>-<span id="showingEnd">12</span> 
            of <span id="totalResults">0</span> listings
        </div>
        <div class="pagination-controls">
            <button class="pagination-btn" id="prevPage" disabled>
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
            </button>
            <div class="pagination-numbers" id="paginationNumbers">
                <!-- Page numbers will be generated here -->
            </div>
            <button class="pagination-btn" id="nextPage">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<!-- Add/Edit Listing Modal -->
<div class="modal modal-large" id="listingModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title" id="listingModalTitle">Add New Listing</h2>
            <button class="modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="listingForm" class="listing-form">
                <input type="hidden" id="listingId" name="listing_id" value="">
                
                <!-- Basic Information -->
                <div class="form-section">
                    <h3 class="form-section-title">Basic Information</h3>
                    <div class="form-grid">
                        <div class="form-group form-group-full">
                            <label for="listingTitle" class="form-label required">Listing Title</label>
                            <input type="text" 
                                   id="listingTitle" 
                                   name="title" 
                                   class="form-input" 
                                   placeholder="e.g., Beautiful 3BR Home in Downtown"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="listingStatus" class="form-label required">Status</label>
                            <select id="listingStatus" name="listing_status" class="form-select" required>
                                <option value="">Select Status</option>
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                                <option value="sold">Sold</option>
                                <option value="coming-soon">Coming Soon</option>
                                <option value="off-market">Off Market</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="mlsNumber" class="form-label">MLS Number</label>
                            <input type="text" 
                                   id="mlsNumber" 
                                   name="mls_number" 
                                   class="form-input" 
                                   placeholder="MLS#">
                        </div>
                    </div>
                </div>
                
                <!-- Property Details -->
                <div class="form-section">
                    <h3 class="form-section-title">Property Details</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="propertyType" class="form-label required">Property Type</label>
                            <select id="propertyType" name="property_type" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="single-family">Single Family</option>
                                <option value="condo">Condo</option>
                                <option value="townhouse">Townhouse</option>
                                <option value="multi-family">Multi-Family</option>
                                <option value="land">Land</option>
                                <option value="commercial">Commercial</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="listingPrice" class="form-label required">Price</label>
                            <div class="input-group">
                                <span class="input-prefix">$</span>
                                <input type="number" 
                                       id="listingPrice" 
                                       name="price" 
                                       class="form-input" 
                                       placeholder="0"
                                       min="0"
                                       required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="bedrooms" class="form-label">Bedrooms</label>
                            <input type="number" 
                                   id="bedrooms" 
                                   name="bedrooms" 
                                   class="form-input" 
                                   min="0" 
                                   placeholder="0">
                        </div>
                        
                        <div class="form-group">
                            <label for="bathrooms" class="form-label">Bathrooms</label>
                            <input type="number" 
                                   id="bathrooms" 
                                   name="bathrooms" 
                                   class="form-input" 
                                   min="0" 
                                   step="0.5" 
                                   placeholder="0">
                        </div>
                        
                        <div class="form-group">
                            <label for="squareFeet" class="form-label">Square Feet</label>
                            <input type="number" 
                                   id="squareFeet" 
                                   name="square_feet" 
                                   class="form-input" 
                                   min="0" 
                                   placeholder="0">
                        </div>
                        
                        <div class="form-group">
                            <label for="lotSize" class="form-label">Lot Size (acres)</label>
                            <input type="number" 
                                   id="lotSize" 
                                   name="lot_size" 
                                   class="form-input" 
                                   min="0" 
                                   step="0.01" 
                                   placeholder="0.00">
                        </div>
                        
                        <div class="form-group">
                            <label for="yearBuilt" class="form-label">Year Built</label>
                            <input type="number" 
                                   id="yearBuilt" 
                                   name="year_built" 
                                   class="form-input" 
                                   min="1800" 
                                   max="2024" 
                                   placeholder="YYYY">
                        </div>
                        
                        <div class="form-group">
                            <label for="garageSpaces" class="form-label">Garage Spaces</label>
                            <input type="number" 
                                   id="garageSpaces" 
                                   name="garage_spaces" 
                                   class="form-input" 
                                   min="0" 
                                   placeholder="0">
                        </div>
                    </div>
                </div>
                
                <!-- Location -->
                <div class="form-section">
                    <h3 class="form-section-title">Location</h3>
                    <div class="form-grid">
                        <div class="form-group form-group-full">
                            <label for="streetAddress" class="form-label required">Street Address</label>
                            <input type="text" 
                                   id="streetAddress" 
                                   name="street_address" 
                                   class="form-input" 
                                   placeholder="123 Main Street"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="city" class="form-label required">City</label>
                            <input type="text" 
                                   id="city" 
                                   name="city" 
                                   class="form-input" 
                                   placeholder="City"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="state" class="form-label required">State</label>
                            <select id="state" name="state" class="form-select" required>
                                <option value="">Select State</option>
                                <option value="DE">Delaware</option>
                                <!-- Add all states -->
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="zipCode" class="form-label required">ZIP Code</label>
                            <input type="text" 
                                   id="zipCode" 
                                   name="zip_code" 
                                   class="form-input" 
                                   placeholder="12345"
                                   pattern="[0-9]{5}"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="neighborhood" class="form-label">Neighborhood</label>
                            <input type="text" 
                                   id="neighborhood" 
                                   name="neighborhood" 
                                   class="form-input" 
                                   placeholder="Neighborhood">
                        </div>
                    </div>
                </div>
                
                <!-- Description -->
                <div class="form-section">
                    <h3 class="form-section-title">Description</h3>
                    <div class="form-group">
                        <label for="description" class="form-label">Property Description</label>
                        <textarea id="description" 
                                  name="description" 
                                  class="form-textarea" 
                                  rows="6"
                                  placeholder="Describe the property features, amenities, and highlights..."></textarea>
                        <span class="form-help">Include key selling points and unique features</span>
                    </div>
                </div>
                
                <!-- Media Upload -->
                <div class="form-section">
                    <h3 class="form-section-title">Photos & Media</h3>
                    <div class="form-group">
                        <label class="form-label">Property Photos</label>
                        <div class="media-upload-area" id="photoUploadArea">
                            <div class="upload-dropzone" id="photoDropzone">
                                <svg width="48" height="48" viewBox="0 0 48 48" fill="currentColor" opacity="0.3">
                                    <path d="M28 16H20v8H12v8h24v-8h-8v-8zM12 40h24c2.21 0 4-1.79 4-4v-24c0-2.21-1.79-4-4-4H12c-2.21 0-4 1.79-4 4v24c0 2.21 1.79 4 4 4z"/>
                                </svg>
                                <p>Drag & drop photos here or <button type="button" class="link-btn">browse</button></p>
                                <input type="file" 
                                       id="photoInput" 
                                       name="photos[]" 
                                       multiple 
                                       accept="image/*" 
                                       style="display: none;">
                            </div>
                            <div class="uploaded-photos" id="uploadedPhotos">
                                <!-- Uploaded photos will appear here -->
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="virtualTourUrl" class="form-label">Virtual Tour URL</label>
                        <input type="url" 
                               id="virtualTourUrl" 
                               name="virtual_tour_url" 
                               class="form-input" 
                               placeholder="https://...">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeListingModal()">Cancel</button>
            <button type="submit" form="listingForm" class="btn btn-primary" id="saveListingBtn">
                <span class="btn-text">Save Listing</span>
                <span class="btn-loading" style="display: none;">
                    <svg class="spinner" width="20" height="20" viewBox="0 0 20 20">
                        <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="2" fill="none" stroke-dasharray="50.265" stroke-dashoffset="37.699" stroke-linecap="round">
                            <animateTransform attributeName="transform" type="rotate" from="0 10 10" to="360 10 10" dur="1s" repeatCount="indefinite"/>
                        </circle>
                    </svg>
                    Saving...
                </span>
            </button>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal" id="importModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Import Listings</h2>
            <button class="modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="import-options">
                <div class="import-option">
                    <h4>CSV Import</h4>
                    <p>Upload a CSV file with your listings data</p>
                    <input type="file" id="csvFile" accept=".csv" class="form-input">
                    <button class="btn btn-primary mt-2" id="importCsvBtn">Import CSV</button>
                </div>
                <div class="import-option">
                    <h4>MLS Sync</h4>
                    <p>Sync listings from your MLS account</p>
                    <button class="btn btn-primary" id="syncMlsBtn">Sync from MLS</button>
                </div>
                <div class="import-option">
                    <h4>Airtable Import</h4>
                    <p>Import listings from Airtable base</p>
                    <button class="btn btn-primary" id="syncAirtableBtn">Import from Airtable</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions Menu -->
<div class="quick-actions-menu" id="quickActionsMenu" style="display: none;">
    <button class="quick-action-item" data-action="edit">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
            <path d="M11.013 2.513a1.75 1.75 0 012.475 2.475L6.226 12.25a2.751 2.751 0 01-.892.596l-2.047.848a.75.75 0 01-.98-.98l.848-2.047a2.751 2.751 0 01.596-.892l7.262-7.262z"/>
        </svg>
        Edit Listing
    </button>
    <button class="quick-action-item" data-action="view">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
            <path d="M8 3.5a4.5 4.5 0 100 9 4.5 4.5 0 000-9zM2 8a6 6 0 1112 0A6 6 0 012 8z"/>
            <path d="M8 6a2 2 0 100 4 2 2 0 000-4z"/>
        </svg>
        View Details
    </button>
    <button class="quick-action-item" data-action="open-house">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
            <path d="M4.5 3A1.5 1.5 0 003 4.5V6h10V4.5A1.5 1.5 0 0011.5 3h-7zM13 7H3v5.5A1.5 1.5 0 004.5 14h7a1.5 1.5 0 001.5-1.5V7z"/>
        </svg>
        Schedule Open House
    </button>
    <button class="quick-action-item" data-action="duplicate">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
            <path d="M4 2a2 2 0 00-2 2v8c0 1.1.9 2 2 2h8a2 2 0 002-2V4a2 2 0 00-2-2H4zm0 2h8v8H4V4z"/>
        </svg>
        Duplicate
    </button>
    <button class="quick-action-item" data-action="share">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
            <path d="M8.636 3.5a.5.5 0 00-.5-.5H1.5A1.5 1.5 0 000 4.5v10A1.5 1.5 0 001.5 16h10a1.5 1.5 0 001.5-1.5V7.864a.5.5 0 00-1 0V14.5a.5.5 0 01-.5.5h-10a.5.5 0 01-.5-.5v-10a.5.5 0 01.5-.5h6.636a.5.5 0 00.5-.5z"/>
            <path d="M16 .5a.5.5 0 00-.5-.5h-5a.5.5 0 000 1h3.793L6.146 9.146a.5.5 0 10.708.708L15 1.707V5.5a.5.5 0 001 0v-5z"/>
        </svg>
        Share Listing
    </button>
    <div class="quick-action-divider"></div>
    <button class="quick-action-item text-danger" data-action="delete">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
            <path d="M6.5 1.75a.25.25 0 01.25-.25h2.5a.25.25 0 01.25.25V3h-3V1.75zm4.5 0V3h2.25a.75.75 0 010 1.5H2.75a.75.75 0 010-1.5H5V1.75C5 .784 5.784 0 6.75 0h2.5C10.216 0 11 .784 11 1.75zM4.496 6.675a.75.75 0 10-1.492.15l.66 6.6A1.75 1.75 0 005.405 15h5.19c.9 0 1.652-.681 1.741-1.576l.66-6.6a.75.75 0 00-1.492-.149l-.66 6.6a.25.25 0 01-.249.225h-5.19a.25.25 0 01-.249-.225l-.66-6.6z"/>
        </svg>
        Delete
    </button>
</div>