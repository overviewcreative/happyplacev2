<?php
/**
 * Dashboard Assets Manager
 * Handles proper loading of dashboard CSS and JavaScript assets
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class HP_Dashboard_Assets {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_dashboard_assets'));
        add_action('wp_head', array($this, 'dashboard_inline_styles'), 20);
        add_action('wp_footer', array($this, 'dashboard_inline_scripts'), 20);
    }

    /**
     * Check if we're on a dashboard page
     */
    private function is_dashboard_page() {
        return (strpos($_SERVER['REQUEST_URI'], 'agent-dashboard') !== false || 
                isset($_GET['dashboard_page']));
    }

    /**
     * Enqueue dashboard assets
     */
    public function enqueue_dashboard_assets() {
        if (!$this->is_dashboard_page()) {
            return;
        }

        // Core dependencies
        wp_enqueue_script('jquery');
        
        // Font Awesome
        wp_enqueue_style(
            'font-awesome', 
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', 
            array(), 
            '6.4.0'
        );

        // Bootstrap 5 (for modals only)
        wp_enqueue_style(
            'bootstrap-modals', 
            'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css', 
            array(), 
            '5.1.3'
        );
        wp_enqueue_script(
            'bootstrap-modals', 
            'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js', 
            array('jquery'), 
            '5.1.3', 
            true
        );

        // Chart.js for dashboard charts
        wp_enqueue_script(
            'chart-js', 
            'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js', 
            array(), 
            '3.9.1', 
            true
        );

        // Custom Framework CSS (in proper order)
        wp_enqueue_style(
            'hph-variables', 
            get_template_directory_uri() . '/assets/css/framework/base/variables.css', 
            array(), 
            '1.0.0'
        );
        
        wp_enqueue_style(
            'hph-reset', 
            get_template_directory_uri() . '/assets/css/framework/base/reset-enhanced.css', 
            array('hph-variables'), 
            '1.0.0'
        );
        
        wp_enqueue_style(
            'hph-typography', 
            get_template_directory_uri() . '/assets/css/framework/base/typography.css', 
            array('hph-variables'), 
            '1.0.0'
        );
        
        wp_enqueue_style(
            'hph-buttons', 
            get_template_directory_uri() . '/assets/css/framework/components/buttons.css', 
            array('hph-variables'), 
            '1.0.0'
        );
        
        wp_enqueue_style(
            'hph-cards', 
            get_template_directory_uri() . '/assets/css/framework/components/cards.css', 
            array('hph-variables'), 
            '1.0.0'
        );
        
        wp_enqueue_style(
            'hph-forms', 
            get_template_directory_uri() . '/assets/css/framework/components/forms.css', 
            array('hph-variables'), 
            '1.0.0'
        );

        // Dashboard specific CSS
        wp_enqueue_style(
            'hph-dashboard', 
            get_template_directory_uri() . '/assets/css/dashboard/dashboard.css', 
            array('hph-variables', 'hph-reset', 'hph-typography', 'hph-buttons', 'hph-cards', 'hph-forms'), 
            '1.0.0'
        );

        wp_enqueue_style(
            'hph-listing-form', 
            get_template_directory_uri() . '/assets/css/dashboard/listing-form.css', 
            array('hph-dashboard', 'bootstrap-modals'), 
            '1.0.0'
        );

        // Dashboard JavaScript
        wp_enqueue_script(
            'hph-dashboard-router', 
            get_template_directory_uri() . '/assets/js/dashboard/dashboard-router.js', 
            array('jquery'), 
            '1.0.0', 
            true
        );
        
        wp_enqueue_script(
            'hph-dashboard-main', 
            get_template_directory_uri() . '/assets/js/dashboard/dashboard-main.js', 
            array('jquery', 'hph-dashboard-router'), 
            '1.0.0', 
            true
        );

        wp_enqueue_script(
            'hph-listing-form', 
            get_template_directory_uri() . '/assets/js/dashboard/listing-form.js', 
            array('jquery', 'hph-dashboard-main', 'bootstrap-modals'), 
            '1.0.0', 
            true
        );

        // Page-specific assets
        $dashboard_page = get_query_var('dashboard_page', '');
        if (!empty($dashboard_page)) {
            $css_file = get_template_directory() . '/assets/css/dashboard/dashboard-' . $dashboard_page . '.css';
            $js_file = get_template_directory() . '/assets/js/dashboard/dashboard-' . $dashboard_page . '.js';
            
            if (file_exists($css_file)) {
                wp_enqueue_style(
                    'hph-dashboard-' . $dashboard_page, 
                    get_template_directory_uri() . '/assets/css/dashboard/dashboard-' . $dashboard_page . '.css', 
                    array('hph-dashboard'), 
                    '1.0.0'
                );
            }
            
            if (file_exists($js_file)) {
                wp_enqueue_script(
                    'hph-dashboard-' . $dashboard_page, 
                    get_template_directory_uri() . '/assets/js/dashboard/dashboard-' . $dashboard_page . '.js', 
                    array('jquery', 'hph-dashboard-main'), 
                    '1.0.0', 
                    true
                );
            }
        }

        // Localize scripts
        wp_localize_script('hph-dashboard-router', 'hphDashboardSettings', array(
            'root' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
            'dashboard_nonce' => wp_create_nonce('dashboard_nonce'),
            'ajaxurl' => admin_url('admin-ajax.php'),
            'themeUrl' => get_template_directory_uri(),
            'version' => '1.0.0',
            'user_id' => get_current_user_id(),
            'is_mobile' => wp_is_mobile()
        ));
    }

    /**
     * Add critical inline styles for dashboard
     */
    public function dashboard_inline_styles() {
        if (!$this->is_dashboard_page()) {
            return;
        }
        ?>
        <style>
        /* Critical CSS to prevent FOUC */
        .dashboard-wrapper { opacity: 0; transition: opacity 0.3s ease; }
        .dashboard-loaded .dashboard-wrapper { opacity: 1; }
        
        /* ================================================
           BOOTSTRAP CONFLICT RESOLUTION
           ================================================ */
        
        /* Scope Bootstrap to only modal components */
        .dashboard-body .btn:not(.modal *, .dropdown-menu *) {
            /* Reset Bootstrap button styles, use HPH framework instead */
            all: unset;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: var(--hph-spacing-sm) var(--hph-spacing-md);
            border-radius: var(--hph-radius-md);
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border: 1px solid transparent;
        }
        
        /* Restore Bootstrap functionality only for modal buttons */
        .modal .btn {
            all: revert;
        }
        
        /* Fix modal styling conflicts */
        .dashboard-body .modal-content {
            border-radius: 0.5rem;
            border: 1px solid var(--hph-border-color);
        }
        
        .dashboard-body .modal-header {
            border-bottom-color: var(--hph-border-color);
        }
        
        .dashboard-body .modal-footer {
            border-top-color: var(--hph-border-color);
        }
        
        /* Prevent Bootstrap from overriding HPH cards */
        .dashboard-body .card:not(.modal *) {
            all: unset;
        }
        
        /* Ensure form controls work correctly */
        .dashboard-body .form-control:focus,
        .dashboard-body .form-select:focus {
            border-color: var(--hph-primary-300);
            box-shadow: 0 0 0 0.2rem rgba(var(--hph-primary-rgb), 0.25);
        }
        
        /* Fix z-index layering */
        .modal-backdrop {
            z-index: 1040;
        }
        
        .modal {
            z-index: 1050;
        }
        
        .dashboard-sidebar {
            z-index: 1030;
        }
        
        /* Prevent Bootstrap from affecting HPH typography */
        .dashboard-body h1, .dashboard-body h2, .dashboard-body h3, 
        .dashboard-body h4, .dashboard-body h5, .dashboard-body h6 {
            font-family: var(--hph-font-primary);
            font-weight: var(--hph-font-weight-semibold);
            line-height: var(--hph-line-height-tight);
            color: var(--hph-gray-900);
        }
        
        /* ================================================
           SIDEBAR NAVIGATION FIXES
           ================================================ */
        
        /* Prevent Bootstrap from affecting sidebar navigation */
        .dashboard-sidebar .nav-menu {
            all: unset;
            display: block;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .dashboard-sidebar .nav-item {
            all: unset;
            display: block;
            margin: 0;
            padding: 0;
        }
        
        .dashboard-sidebar .nav-link {
            all: unset;
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: var(--hph-gray-600);
            text-decoration: none;
            transition: all 0.2s ease;
            border-radius: 0;
            margin: 0 0.5rem;
            font-weight: 500;
        }
        
        .dashboard-sidebar .nav-link:hover {
            background: var(--hph-gray-100);
            color: var(--hph-gray-900);
            text-decoration: none;
        }
        
        .dashboard-sidebar .nav-item.active .nav-link {
            background: var(--hph-primary-100);
            color: var(--hph-primary-700);
            border-left: 3px solid var(--hph-primary-500);
        }
        
        .dashboard-sidebar .nav-icon {
            margin-right: 0.75rem;
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }
        
        /* Fix any Bootstrap list overrides */
        .dashboard-sidebar ul {
            list-style: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .dashboard-sidebar li {
            margin: 0 !important;
            padding: 0 !important;
        }
        
        /* ================================================
           MODAL FALLBACK STYLES
           ================================================ */
        
        /* Ensure modals display correctly without Bootstrap JS */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1055;
            width: 100%;
            height: 100%;
            overflow-x: hidden;
            overflow-y: auto;
            outline: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
        }
        
        .modal.show {
            display: flex !important;
            align-items: center;
            justify-content: center;
        }
        
        .modal-dialog {
            position: relative;
            width: auto;
            max-width: 90vw;
            margin: 1rem;
        }
        
        .modal-xl {
            max-width: 1140px;
        }
        </style>
        <?php
    }

    /**
     * Add dashboard initialization script
     */
    public function dashboard_inline_scripts() {
        if (!$this->is_dashboard_page()) {
            return;
        }
        ?>
        <script>
        // Dashboard initialization
        jQuery(document).ready(function($) {
            // Mark dashboard as loaded
            $('body').addClass('dashboard-loaded');
            
            // Ensure Bootstrap modals work
            if (typeof bootstrap !== 'undefined') {
                console.log('Bootstrap is available');
                
                // Initialize all modals manually if needed
                $('.modal').each(function() {
                    if (!bootstrap.Modal.getInstance(this)) {
                        new bootstrap.Modal(this);
                    }
                });
            } else {
                console.error('Bootstrap JS not loaded properly');
                
                // Fallback modal functionality
                $(document).on('click', '[data-bs-target]', function(e) {
                    e.preventDefault();
                    const target = $(this).attr('data-bs-target');
                    console.log('Fallback: showing modal', target);
                    $(target).show().addClass('show');
                });
                
                $(document).on('click', '[data-bs-dismiss=\"modal\"]', function(e) {
                    e.preventDefault();
                    $(this).closest('.modal').hide().removeClass('show');
                });
            }
            
            // Initialize listing form handlers
            if (typeof window.ListingFormHandler === 'undefined') {
                window.ListingFormHandler = {
                    init: function() {
                        // Event delegation for edit buttons
                        $(document).on('click', '.edit-listing-btn', function(e) {
                            e.preventDefault();
                            const listingId = $(this).data('listing-id');
                            window.ListingFormHandler.loadListingData(listingId);
                        });
                        
                        // Handle add new listing buttons
                        $(document).on('click', '[data-bs-target="#listingFormModal"]', function(e) {
                            console.log('Add listing button clicked');
                            const listingId = $(this).data('listing-id') || 0;
                            if (listingId === 0) {
                                window.ListingFormHandler.resetForm();
                            }
                        });
                    },
                    
                    loadListingData: function(listingId) {
                        if (listingId > 0) {
                            $('#listingFormModal').find('input[name="listing_id"]').val(listingId);
                            $('#listingFormModalLabel').html('<i class="fas fa-edit me-2"></i>Edit Listing');
                            $('#submitListing').html('<i class="fas fa-save me-2"></i>Update Listing');
                            
                            // Load data via AJAX
                            $.ajax({
                                url: hphDashboardSettings.ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'load_listing_data',
                                    listing_id: listingId,
                                    nonce: hphDashboardSettings.dashboard_nonce
                                },
                                success: function(response) {
                                    if (response.success) {
                                        window.ListingFormHandler.populateForm(response.data);
                                    }
                                },
                                error: function() {
                                    console.log('Failed to load listing data');
                                }
                            });
                        }
                    },
                    
                    populateForm: function(data) {
                        for (const [key, value] of Object.entries(data)) {
                            const $field = $(`[name="${key}"]`);
                            const $arrayField = $(`[name="${key}[]"]`);
                            
                            if ($arrayField.length && Array.isArray(value)) {
                                // Handle checkbox arrays
                                $arrayField.prop('checked', false);
                                value.forEach(function(val) {
                                    $arrayField.filter(`[value="${val}"]`).prop('checked', true);
                                });
                            } else if ($field.length) {
                                if ($field.is(':checkbox')) {
                                    $field.prop('checked', value);
                                } else if ($field.is('select')) {
                                    $field.val(value);
                                } else if ($field.is(':radio')) {
                                    $field.filter(`[value="${value}"]`).prop('checked', true);
                                } else {
                                    $field.val(value);
                                }
                            }
                        }
                    },
                    
                    resetForm: function() {
                        $('#listingFormModal').find('input[name="listing_id"]').val(0);
                        $('#listingFormModalLabel').html('<i class="fas fa-home me-2"></i>Add New Listing');
                        $('#submitListing').html('<i class="fas fa-save me-2"></i>Create Listing');
                        
                        // Reset form
                        $('#listingForm')[0].reset();
                        $('.form-step').removeClass('active');
                        $('.form-step[data-step="1"]').addClass('active');
                        
                        if (typeof window.ListingForm !== 'undefined') {
                            window.ListingForm.currentStep = 1;
                            window.ListingForm.updateProgress();
                            window.ListingForm.updateNavigation();
                        }
                    }
                };
                
                // Initialize
                window.ListingFormHandler.init();
            }
            
            // ================================================
            // SEARCH FUNCTIONALITY
            // ================================================
            
            // Header search functionality
            $('.header-search .search-input').on('input', function() {
                const query = $(this).val().toLowerCase();
                console.log('Header search:', query);
                // TODO: Implement global search across all dashboard content
            });
            
            // Listings search functionality
            $('#listingsSearch').on('input', function() {
                const query = $(this).val().toLowerCase();
                console.log('Listings search:', query);
                
                if (query === '') {
                    // Show all listings
                    $('.listing-list-item').show();
                    return;
                }
                
                // Search through listing items
                $('.listing-list-item').each(function() {
                    const $item = $(this);
                    const title = $item.find('.listing-title').text().toLowerCase();
                    const address = $item.find('.listing-address').text().toLowerCase();
                    const meta = $item.find('.listing-meta').text().toLowerCase();
                    
                    const isMatch = title.includes(query) || 
                                   address.includes(query) || 
                                   meta.includes(query);
                    
                    if (isMatch) {
                        $item.show();
                    } else {
                        $item.hide();
                    }
                });
            });
            
            // Filter buttons functionality
            $('.filter-btn').on('click', function() {
                const $btn = $(this);
                const status = $btn.data('status');
                
                // Update active state
                $('.filter-btn').removeClass('active');
                $btn.addClass('active');
                
                console.log('Filter by status:', status);
                
                if (status === 'all') {
                    $('.listing-list-item').show();
                } else {
                    $('.listing-list-item').each(function() {
                        const $item = $(this);
                        const itemStatus = $item.find('.listing-status').text().toLowerCase();
                        
                        if (itemStatus === status) {
                            $item.show();
                        } else {
                            $item.hide();
                        }
                    });
                }
            });
            
            // ================================================
            // MOBILE SIDEBAR FUNCTIONALITY
            // ================================================
            
            // Mobile menu toggle
            $('#mobileMenuToggle').on('click', function() {
                $('#dashboardSidebar').toggleClass('active');
                console.log('Mobile menu toggled');
            });
            
            // Close sidebar
            $('#sidebarClose').on('click', function() {
                $('#dashboardSidebar').removeClass('active');
                console.log('Sidebar closed');
            });
            
            // Close sidebar when clicking outside on mobile
            $(document).on('click', function(e) {
                if ($(window).width() < 1024) {
                    if (!$(e.target).closest('#dashboardSidebar, #mobileMenuToggle').length) {
                        $('#dashboardSidebar').removeClass('active');
                    }
                }
            });
        });
        </script>
        <?php
    }
}

// Initialize the dashboard assets manager
new HP_Dashboard_Assets();