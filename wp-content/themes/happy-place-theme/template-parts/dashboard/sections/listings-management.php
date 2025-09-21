<?php
/**
 * Listings Management Section - Using Framework Styles
 * Clean, functional listings dashboard with framework styling
 */

if (!is_user_logged_in()) {
    return;
}

$current_user = wp_get_current_user();
$is_admin = current_user_can('manage_options');
$is_agent = current_user_can('manage_listings') || $is_admin;

if (!$is_agent) {
    echo '<div class="hph-card hph-p-lg hph-text-center hph-text-red-600">You do not have permission to manage listings.</div>';
    return;
}

// Enqueue framework styles for listings
wp_enqueue_style('hph-listing-card', get_template_directory_uri() . '/assets/css/framework/features/listing/listing-card.css', ['hph-framework'], '1.0.0');
wp_enqueue_style('hph-archive-enhanced', get_template_directory_uri() . '/assets/css/framework/features/listing/archive-enhanced.css', ['hph-framework'], '1.0.0');

wp_nonce_field('hph_dashboard_nonce', 'hph_dashboard_nonce', false);
?>

<div class="listings-dashboard hph-max-w-7xl hph-mx-auto hph-p-lg">
    
    <!-- Header -->
    <div class="hph-flex hph-justify-between hph-items-center hph-mb-lg hph-pb-md hph-border-b-2 hph-border-gray-200">
        <div>
            <h1 class="hph-text-3xl hph-font-bold hph-text-gray-900 hph-mb-xs">My Listings</h1>
            <p class="hph-text-gray-600">Manage your property listings</p>
        </div>
        <a href="<?php echo home_url('/listing-form/'); ?>" class="hph-btn hph-btn-primary hph-btn-lg">
            <i class="fas fa-plus hph-mr-sm"></i>
            Add New Listing
        </a>
    </div>

    <!-- Stats Overview -->
    <div class="hph-grid hph-grid-cols-2 md:hph-grid-cols-4 hph-gap-md hph-mb-lg">
        <div class="hph-card hph-card-elevated hph-p-md hph-text-center">
            <div class="hph-text-3xl hph-font-bold hph-text-success hph-mb-xs" id="active-count">0</div>
            <div class="hph-text-sm hph-text-gray-600 hph-font-medium">Active</div>
        </div>
        <div class="hph-card hph-card-elevated hph-p-md hph-text-center">
            <div class="hph-text-3xl hph-font-bold hph-text-warning hph-mb-xs" id="pending-count">0</div>
            <div class="hph-text-sm hph-text-gray-600 hph-font-medium">Pending</div>
        </div>
        <div class="hph-card hph-card-elevated hph-p-md hph-text-center">
            <div class="hph-text-3xl hph-font-bold hph-text-danger hph-mb-xs" id="sold-count">0</div>
            <div class="hph-text-sm hph-text-gray-600 hph-font-medium">Sold</div>
        </div>
        <div class="hph-card hph-card-elevated hph-p-md hph-text-center">
            <div class="hph-text-3xl hph-font-bold hph-text-primary hph-mb-xs" id="total-count">0</div>
            <div class="hph-text-sm hph-text-gray-600 hph-font-medium">Total</div>
        </div>
    </div>

    <!-- Controls -->
    <div class="hph-card hph-p-lg hph-mb-lg">
        <div class="hph-flex hph-flex-wrap hph-gap-md hph-items-end">
            <div class="hph-flex hph-flex-col hph-gap-xs">
                <label class="hph-label">Status</label>
                <select id="status-filter" class="hph-select">
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="pending">Pending</option>
                    <option value="sold">Sold</option>
                    <option value="draft">Draft</option>
                </select>
            </div>
            <div class="hph-flex hph-flex-col hph-gap-xs hph-flex-grow">
                <label class="hph-label">Search</label>
                <input type="text" id="search-listings" class="hph-input" placeholder="Search listings...">
            </div>
            <div class="hph-flex hph-flex-col hph-gap-xs">
                <label class="hph-label">Sort</label>
                <select id="sort-listings" class="hph-select">
                    <option value="date-desc">Newest First</option>
                    <option value="date-asc">Oldest First</option>
                    <option value="price-desc">Price High to Low</option>
                    <option value="price-asc">Price Low to High</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div id="listings-loading" class="hph-hidden hph-text-center hph-p-xl">
        <i class="fas fa-spinner fa-spin hph-text-primary hph-text-2xl hph-mb-md"></i>
        <p class="hph-text-gray-600">Loading listings...</p>
    </div>

    <!-- Listings Container -->
    <div id="listings-container" class="hph-container">
        <div class="hph-grid hph-grid-cols-1 md:hph-grid-cols-2 xl:hph-grid-cols-3 hph-gap-xl hph-min-h-96">
            <!-- Listings will be loaded here via AJAX -->
        </div>
    </div>

    <!-- No Listings State -->
    <div id="empty-state" class="hph-hidden hph-text-center hph-p-xl">
        <div class="hph-mb-lg">
            <i class="fas fa-home hph-text-6xl hph-text-gray-300 hph-mb-md"></i>
            <h3 class="hph-text-xl hph-font-semibold hph-text-gray-700 hph-mb-sm">No listings found</h3>
            <p class="hph-text-gray-600 hph-mb-lg">You haven't created any listings yet.</p>
            <a href="<?php echo home_url('/listing-form/'); ?>" class="hph-btn hph-btn-primary hph-btn-lg">
                <i class="fas fa-plus hph-mr-sm"></i>
                Create Your First Listing
            </a>
        </div>
    </div>

    <!-- Pagination -->
    <div id="listings-pagination" class="hph-mt-lg hph-text-center">
        <!-- Pagination will be loaded here -->
    </div>

</div>

<?php
// Modal removed - now using standalone listing form page
?>

<script>
jQuery(document).ready(function($) {
    console.log('🚀 Loading Listings Dashboard...');
    
    // Dashboard object
    const ListingsDashboard = {
        currentPage: 1,
        
        init() {
            console.log('🔧 Initializing Listings Dashboard');
            this.bindEvents();
            this.loadStats();
            this.loadListings();
            console.log('✅ Dashboard initialized');
        },

        bindEvents() {
            $('#status-filter, #sort-listings').on('change', () => this.loadListings());
            $('#search-listings').on('input', this.debounce(() => this.loadListings(), 300));
        },

        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        loadStats() {
            console.log('📊 Loading stats...');
            
            const ajaxurl = window.ajaxurl || '/wp-admin/admin-ajax.php';
            
            $.post(ajaxurl, {
                action: 'hph_get_listing_stats',
                nonce: $('#hph_dashboard_nonce').val()
            })
            .done((response) => {
                console.log('📊 Stats loaded:', response);
                if (response.success) {
                    $('#active-count').text(response.data.active || 0);
                    $('#pending-count').text(response.data.pending || 0);
                    $('#sold-count').text(response.data.sold || 0);
                    $('#total-count').text(response.data.total || 0);
                }
            })
            .fail((xhr) => {
                console.error('📊 Stats loading failed:', xhr.responseText);
            });
        },

        loadListings() {
            console.log('🔥 Loading listings...');
            
            const ajaxurl = window.ajaxurl || '/wp-admin/admin-ajax.php';
            
            const params = {
                action: 'hph_get_listings',
                nonce: $('#hph_dashboard_nonce').val(),
                status: $('#status-filter').val(),
                search: $('#search-listings').val(),
                sort: $('#sort-listings').val(),
                page: this.currentPage
            };

            console.log('📊 AJAX params:', params);

            $('#listings-loading').removeClass('hph-hidden').addClass('hph-block');
            $('#listings-container').addClass('hph-hidden');
            $('#empty-state').addClass('hph-hidden');

            $.post(ajaxurl, params)
            .done((response) => {
                console.log('✅ Listings loaded:', response);
                
                $('#listings-loading').addClass('hph-hidden');
                
                if (response.success) {
                    const container = $('#listings-container');
                    
                    if (response.data.listings && response.data.total > 0) {
                        container.html(response.data.listings);
                        container.removeClass('hph-hidden');
                        $('#listings-pagination').html(response.data.pagination || '');
                        console.log('📄 Found', response.data.total, 'listings');
                    } else {
                        $('#empty-state').removeClass('hph-hidden');
                        console.log('📄 No listings found');
                    }
                } else {
                    console.error('❌ Error loading listings:', response.data);
                    $('#empty-state').removeClass('hph-hidden');
                }
            })
            .fail((xhr) => {
                console.error('❌ AJAX failed:', xhr.responseText);
                $('#listings-loading').addClass('hph-hidden');
                $('#empty-state').removeClass('hph-hidden');
            });
        }
    };

    // Initialize dashboard
    ListingsDashboard.init();
    
    // Make globally available
    window.ListingsDashboard = ListingsDashboard;
    
    // Global functions for listing actions
    window.editListing = function(listingId) {
        console.log('Edit listing:', listingId);
        window.location.href = '<?php echo home_url('/listing-form/'); ?>?listing_id=' + listingId;
    };
    
    window.deleteListing = function(listingId) {
        if (confirm('Are you sure you want to delete this listing?')) {
            console.log('Delete listing:', listingId);
            deleteListingAjax(listingId);
        }
    };
    
    // Modal functions
    window.openAddListingModal = function() {
        console.log('Opening add listing modal...');
        
        // Debug: Check if modal exists
        const modal = document.getElementById('listingFormModal');
        const title = document.getElementById('listingFormTitle');
        const idField = document.getElementById('listingId');
        
        console.log('Modal element:', modal);
        console.log('Title element:', title);
        console.log('ID field:', idField);
        
        if (!modal) {
            console.error('❌ Modal element not found! Modal may not be loaded.');
            alert('Error: Listing form not loaded. Please refresh the page.');
            return;
        }
        
        if (title) {
            title.textContent = 'Add New Listing';
        }
        if (idField) {
            idField.value = '';
        }
        
        modal.style.display = 'block';
        modal.style.visibility = 'visible';
        modal.style.opacity = '1';
        modal.style.zIndex = '999999';
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
        
        console.log('✅ Modal should now be visible. Style:', modal.style.cssText);
        
        // Debug: Check computed styles
        const computedStyle = window.getComputedStyle(modal);
        console.log('📐 Computed styles:', {
            display: computedStyle.display,
            position: computedStyle.position,
            zIndex: computedStyle.zIndex,
            visibility: computedStyle.visibility,
            opacity: computedStyle.opacity,
            top: computedStyle.top,
            left: computedStyle.left,
            width: computedStyle.width,
            height: computedStyle.height
        });
        
        // Reset form if function exists
        if (typeof window.resetListingModal === 'function') {
            window.resetListingModal();
        }
    };
    
    window.openEditListingModal = function(listingId) {
        console.log('Opening edit listing modal for ID:', listingId);
        document.getElementById('listingFormTitle').textContent = 'Edit Listing';
        document.getElementById('listingId').value = listingId;
        document.getElementById('listingFormModal').style.display = 'block';
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
        
        // Load listing data
        loadListingForEdit(listingId);
    };
    
    window.closeListingFormModal = function() {
        console.log('🔄 Attempting to close modal...');
        const modal = document.getElementById('listingFormModal');
        if (modal) {
            modal.style.display = 'none';
            modal.style.visibility = 'hidden';
            modal.style.opacity = '0';
            document.body.style.overflow = ''; // Restore scrolling
            console.log('✅ Modal closed');
        } else {
            console.error('❌ Modal element not found when trying to close');
        }
    };
    
    // Delete listing via AJAX
    function deleteListingAjax(listingId) {
        const ajaxurl = window.ajaxurl || '/wp-admin/admin-ajax.php';
        
        $.post(ajaxurl, {
            action: 'hph_delete_listing',
            nonce: $('#hph_dashboard_nonce').val(),
            listing_id: listingId
        })
        .done((response) => {
            if (response.success) {
                console.log('Listing deleted successfully');
                // Reload listings
                ListingsDashboard.loadListings();
                ListingsDashboard.loadStats();
                
                // Show success message
                showNotification('Listing deleted successfully!', 'success');
            } else {
                console.error('Failed to delete listing:', response.data);
                showNotification('Failed to delete listing: ' + (response.data || 'Unknown error'), 'error');
            }
        })
        .fail((xhr) => {
            console.error('Delete AJAX failed:', xhr.responseText);
            showNotification('Failed to delete listing. Please try again.', 'error');
        });
    }
    
    // Load listing data for editing
    function loadListingForEdit(listingId) {
        const ajaxurl = window.ajaxurl || '/wp-admin/admin-ajax.php';
        
        $.post(ajaxurl, {
            action: 'hph_get_listing_details',
            nonce: $('#hph_dashboard_nonce').val(),
            listing_id: listingId
        })
        .done((response) => {
            if (response.success) {
                console.log('Listing data loaded:', response.data);
                populateFormWithData(response.data);
            } else {
                console.error('Failed to load listing data:', response.data);
                showNotification('Failed to load listing data', 'error');
            }
        })
        .fail((xhr) => {
            console.error('Load listing AJAX failed:', xhr.responseText);
            showNotification('Failed to load listing data', 'error');
        });
    }
    
    // Populate form with listing data
    function populateFormWithData(data) {
        // TODO: Implement form population
        console.log('Populating form with data:', data);
    }
    
    // Show notification (make globally available)
    window.showNotification = function(message, type = 'info') {
        const typeClasses = {
            'success': 'hph-bg-success hph-text-white',
            'error': 'hph-bg-danger hph-text-white', 
            'warning': 'hph-bg-warning hph-text-white',
            'info': 'hph-bg-primary hph-text-white'
        };
        
        const notification = $(`
            <div class="hph-fixed hph-top-md hph-right-md hph-z-50 hph-p-lg hph-rounded-lg hph-shadow-lg hph-font-medium ${typeClasses[type] || typeClasses.info}" style="max-width: 400px;">
                <div class="hph-flex hph-items-center hph-gap-sm">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle'}"></i>
                    <span>${message}</span>
                </div>
            </div>
        `);
        
        $('body').append(notification);
        
        setTimeout(() => {
            notification.fadeOut(() => {
                notification.remove();
            });
        }, 5000);
    };
    
    // Close modal when clicking outside or on close button
    $(document).on('click', '#closeListingForm, #listingFormOverlay', function() {
        closeListingFormModal();
    });
    
    // Prevent modal content clicks from closing modal
    $(document).on('click', '.hph-modal-content', function(e) {
        e.stopPropagation();
    });
    
    // Emergency close function (callable from console)
    window.forceCloseModal = function() {
        $('#listingFormModal').hide();
        document.body.style.overflow = '';
        console.log('🚨 Modal force-closed');
    };
    
    // Close modal on escape key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#listingFormModal').is(':visible')) {
            closeListingFormModal();
        }
    });
});
</script>
