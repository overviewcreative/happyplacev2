<?php
/**
 * Dashboard Listings Management Section
 * 
 * Comprehensive listing management interface with search, filter, and CRUD operations
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user and dashboard instance
$current_user = wp_get_current_user();
$dashboard = \HappyPlace\Dashboard\Frontend_Admin_Dashboard::get_instance();

// Check permissions
$can_manage_all = $dashboard->user_can('manage_all_listings');
$can_manage_own = $dashboard->user_can('manage_own_listings');
$can_manage_team = $dashboard->user_can('manage_team_listings');

if (!$can_manage_all && !$can_manage_own && !$can_manage_team) {
    echo '<div class="alert alert-warning">' . __('You do not have permission to manage listings.', 'happy-place') . '</div>';
    return;
}

// Get current action
$action = get_query_var('dashboard_action', 'list');
$listing_id = get_query_var('dashboard_id', 0);
?>

<div class="listings-management">
    <!-- Listings Header -->
    <div class="dashboard-section">
        <div class="section-header">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="section-title">
                        <?php
                        switch ($action) {
                            case 'add':
                                _e('Add New Listing', 'happy-place');
                                break;
                            case 'edit':
                                _e('Edit Listing', 'happy-place');
                                break;
                            case 'view':
                                _e('View Listing', 'happy-place');
                                break;
                            default:
                                _e('Property Listings', 'happy-place');
                        }
                        ?>
                    </h2>
                </div>
                <div class="col-auto">
                    <div class="section-actions">
                        <?php if ($action === 'list'): ?>
                            <button class="btn btn-outline-primary btn-sm" id="refresh-listings">
                                <span class="hph-icon-refresh"></span>
                                <?php _e('Refresh', 'happy-place'); ?>
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" id="export-listings">
                                <span class="hph-icon-download"></span>
                                <?php _e('Export', 'happy-place'); ?>
                            </button>
                            <a href="<?php echo esc_url(add_query_arg(['dashboard_section' => 'listings', 'dashboard_action' => 'add'], get_permalink())); ?>" class="btn btn-primary btn-sm">
                                <span class="hph-icon-plus"></span>
                                <?php _e('Add Listing', 'happy-place'); ?>
                            </a>
                        <?php else: ?>
                            <a href="<?php echo esc_url(add_query_arg('dashboard_section', 'listings', remove_query_arg(['dashboard_action', 'dashboard_id']))); ?>" class="btn btn-outline-secondary btn-sm">
                                <span class="hph-icon-arrow-left"></span>
                                <?php _e('Back to Listings', 'happy-place'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-content">
            <?php
            switch ($action) {
                case 'add':
                    include get_template_directory() . '/template-parts/dashboard/listings/add-listing.php';
                    break;
                case 'edit':
                    include get_template_directory() . '/template-parts/dashboard/listings/edit-listing.php';
                    break;
                case 'view':
                    include get_template_directory() . '/template-parts/dashboard/listings/view-listing.php';
                    break;
                default:
                    include get_template_directory() . '/template-parts/dashboard/listings/list-listings.php';
            }
            ?>
        </div>
    </div>
</div>

<!-- Listing Modals Container -->
<div id="listing-modals-container"></div>

<script>
// Initialize listings management
jQuery(document).ready(function($) {
    // Initialize listings table if on list view
    if ('<?php echo $action; ?>' === 'list') {
        initializeListingsTable();
    }
    
    // Initialize listing form if on add/edit view
    if (['add', 'edit'].includes('<?php echo $action; ?>')) {
        initializeListingForm();
    }

    // Refresh listings
    $('#refresh-listings').on('click', function() {
        refreshListingsTable();
    });

    // Export listings
    $('#export-listings').on('click', function() {
        exportListings();
    });

    function initializeListingsTable() {
        // Initialize DataTable or custom table functionality
        if ($.fn.DataTable) {
            $('#listings-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: hph_dashboard.ajax_url,
                    type: 'POST',
                    data: function(d) {
                        d.action = 'hph_get_listings';
                        d.nonce = hph_dashboard.nonce;
                        return d;
                    }
                },
                columns: [
                    { data: 'thumbnail', orderable: false },
                    { data: 'title' },
                    { data: 'address' },
                    { data: 'price' },
                    { data: 'status' },
                    { data: 'agent' },
                    { data: 'date_created' },
                    { data: 'actions', orderable: false }
                ],
                order: [[6, 'desc']],
                pageLength: 25,
                responsive: true,
                language: {
                    emptyTable: '<?php _e("No listings found", "happy-place"); ?>',
                    processing: '<?php _e("Loading listings...", "happy-place"); ?>'
                }
            });
        }
    }

    function refreshListingsTable() {
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#listings-table')) {
            $('#listings-table').DataTable().ajax.reload();
        } else {
            location.reload();
        }
    }

    function exportListings() {
        // Handle listings export
        const exportUrl = hph_dashboard.ajax_url + '?action=hph_export_listings&nonce=' + hph_dashboard.nonce;
        window.open(exportUrl, '_blank');
    }

    function initializeListingForm() {
        // Initialize form validation and AJAX submission
        $('#listing-form').on('submit', function(e) {
            e.preventDefault();
            saveListing();
        });

        // Initialize image upload
        initializeImageUpload();
        
        // Initialize address autocomplete
        initializeAddressAutocomplete();
    }

    function saveListing() {
        const formData = new FormData(document.getElementById('listing-form'));
        formData.append('action', 'hph_save_listing');
        formData.append('nonce', hph_dashboard.nonce);

        $.ajax({
            url: hph_dashboard.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                showLoadingOverlay();
            },
            success: function(response) {
                hideLoadingOverlay();
                if (response.success) {
                    showSuccessMessage(response.data.message);
                    if (response.data.redirect) {
                        setTimeout(function() {
                            window.location.href = response.data.redirect;
                        }, 1500);
                    }
                } else {
                    showErrorMessage(response.data.message);
                }
            },
            error: function() {
                hideLoadingOverlay();
                showErrorMessage('<?php _e("An error occurred while saving the listing", "happy-place"); ?>');
            }
        });
    }

    function initializeImageUpload() {
        // Initialize multiple image upload functionality
        $('.image-upload-area').on('click', function() {
            $(this).find('input[type="file"]').click();
        });

        $('input[type="file"][name="listing_images[]"]').on('change', function() {
            handleImageUpload(this.files);
        });
    }

    function handleImageUpload(files) {
        const container = $('.uploaded-images-container');
        
        Array.from(files).forEach(file => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imagePreview = $(`
                        <div class="uploaded-image-item">
                            <img src="${e.target.result}" alt="Preview">
                            <button type="button" class="remove-image btn btn-sm btn-danger">×</button>
                        </div>
                    `);
                    container.append(imagePreview);
                };
                reader.readAsDataURL(file);
            }
        });
    }

    function initializeAddressAutocomplete() {
        // Initialize Google Places Autocomplete if available
        if (typeof google !== 'undefined' && google.maps && google.maps.places) {
            const addressInput = document.getElementById('listing-address');
            if (addressInput) {
                const autocomplete = new google.maps.places.Autocomplete(addressInput);
                autocomplete.addListener('place_changed', function() {
                    const place = autocomplete.getPlace();
                    populateAddressFields(place);
                });
            }
        }
    }

    function populateAddressFields(place) {
        // Auto-populate address fields from Google Places result
        const components = place.address_components;
        
        components.forEach(component => {
            const types = component.types;
            
            if (types.includes('street_number')) {
                $('#listing-street-number').val(component.long_name);
            }
            if (types.includes('route')) {
                $('#listing-street-name').val(component.long_name);
            }
            if (types.includes('locality')) {
                $('#listing-city').val(component.long_name);
            }
            if (types.includes('administrative_area_level_1')) {
                $('#listing-state').val(component.short_name);
            }
            if (types.includes('postal_code')) {
                $('#listing-zip').val(component.long_name);
            }
        });

        // Set coordinates
        if (place.geometry) {
            $('#listing-latitude').val(place.geometry.location.lat());
            $('#listing-longitude').val(place.geometry.location.lng());
        }
    }

    // Utility functions
    function showLoadingOverlay() {
        $('#hph-loading-overlay').show();
    }

    function hideLoadingOverlay() {
        $('#hph-loading-overlay').hide();
    }

    function showSuccessMessage(message) {
        // Show success notification
        const alert = $(`
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        $('.section-content').prepend(alert);
        setTimeout(() => alert.fadeOut(), 5000);
    }

    function showErrorMessage(message) {
        // Show error notification
        const alert = $(`
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        $('.section-content').prepend(alert);
    }
});
</script>

<style>
/* Listings Management Specific Styles */
.listings-management .section-content {
    padding: 0;
}

.listings-filters {
    background: var(--hph-gray-50);
    padding: var(--hph-space-lg);
    border-bottom: 1px solid var(--hph-border-color);
}

.filter-group {
    display: flex;
    gap: var(--hph-space-md);
    align-items: end;
    flex-wrap: wrap;
}

.filter-item {
    min-width: 200px;
}

.listings-table-container {
    padding: var(--hph-space-lg);
}

.listing-thumbnail {
    width: 60px;
    height: 45px;
    object-fit: cover;
    border-radius: var(--hph-border-radius);
}

.listing-status-badge {
    padding: var(--hph-space-xs) var(--hph-space-sm);
    border-radius: var(--hph-border-radius);
    font-size: var(--hph-text-xs);
    font-weight: var(--hph-font-semibold);
    text-transform: uppercase;
}

.status-active {
    background: var(--hph-success-light);
    color: var(--hph-success-dark);
}

.status-pending {
    background: var(--hph-warning-light);
    color: var(--hph-warning-dark);
}

.status-sold {
    background: var(--hph-primary-light);
    color: var(--hph-primary-dark);
}

.status-draft {
    background: var(--hph-gray-200);
    color: var(--hph-gray-700);
}

.image-upload-area {
    border: 2px dashed var(--hph-border-color);
    border-radius: var(--hph-border-radius);
    padding: var(--hph-space-xl);
    text-align: center;
    cursor: pointer;
    transition: var(--hph-transition);
}

.image-upload-area:hover {
    border-color: var(--hph-primary);
    background: var(--hph-primary-light);
}

.uploaded-images-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: var(--hph-space-md);
    margin-top: var(--hph-space-md);
}

.uploaded-image-item {
    position: relative;
    border-radius: var(--hph-border-radius);
    overflow: hidden;
}

.uploaded-image-item img {
    width: 100%;
    height: 80px;
    object-fit: cover;
}

.remove-image {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 20px;
    height: 20px;
    padding: 0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    line-height: 1;
}

.listing-form-section {
    background: var(--hph-white);
    border-radius: var(--hph-card-radius);
    margin-bottom: var(--hph-space-lg);
    box-shadow: var(--hph-shadow-sm);
}

.form-section-header {
    padding: var(--hph-space-lg);
    border-bottom: 1px solid var(--hph-border-color);
    background: var(--hph-gray-50);
}

.form-section-title {
    font-size: var(--hph-text-lg);
    font-weight: var(--hph-font-semibold);
    margin: 0;
}

.form-section-content {
    padding: var(--hph-space-lg);
}

@media (max-width: 767px) {
    .filter-group {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-item {
        min-width: auto;
        width: 100%;
    }
    
    .section-actions {
        flex-direction: column;
        gap: var(--hph-space-xs);
    }
    
    .uploaded-images-container {
        grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
    }
}
</style>