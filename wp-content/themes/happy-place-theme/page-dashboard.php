<?php
/**
 * Template Name: Dashboard
 * Main Dashboard Template
 * 
 * @package HappyPlaceTheme
 */

// Security check
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

// Get current user and check role
$current_user = wp_get_current_user();
$is_admin = current_user_can('manage_options');
$is_agent = in_array('agent', $current_user->roles) || current_user_can('manage_listings') || $is_admin;
$current_section = $_GET['section'] ?? 'overview';

// For admins, treat them as super-agents with access to everything
$user_role = $is_admin ? 'admin' : ($is_agent ? 'agent' : 'user');

// Dashboard doesn't use site header - it has its own layout

// NOTE: Dashboard assets are automatically loaded by the theme asset system
// See includes/assets/theme-assets.php - no manual enqueuing needed

// Add debugging information
if (defined('WP_DEBUG') && WP_DEBUG && file_exists(get_template_directory() . '/debug-dashboard-css.php')) {
    include get_template_directory() . '/debug-dashboard-css.php';
}

// REMOVED: Duplicate wp_localize_script - handled automatically by theme-assets.php
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo get_bloginfo('name'); ?> - Dashboard</title>
    
    <!-- REMOVED: Duplicate Font Awesome - loaded automatically by theme-assets.php -->
    
    <?php wp_head(); ?>
    
    <!-- Load archive listing styles for consistent card display -->
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/framework/features/listing/archive-enhanced.css">
    
    <!-- ENSURE JQUERY IS LOADED -->
    <?php wp_enqueue_script('jquery'); ?>
    
    <!-- DASHBOARD AJAX CONFIGURATION -->
    <script>
    window.hphDashboard = {
        ajaxurl: '<?php echo admin_url('admin-ajax.php'); ?>',
        nonce: '<?php echo wp_create_nonce('hph_dashboard_nonce'); ?>',
        userId: <?php echo get_current_user_id(); ?>,
        isAgent: <?php echo (current_user_can('manage_listings') || current_user_can('manage_options')) ? 1 : 0; ?>,
        isAdmin: <?php echo current_user_can('manage_options') ? 1 : 0; ?>
    };
    window.ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    
    // Asset loading verification
    console.log('🔧 Dashboard Assets Check:');
    console.log('- AJAX URL:', window.hphDashboard.ajaxurl);
    console.log('- User ID:', window.hphDashboard.userId);
    console.log('- Is Agent:', window.hphDashboard.isAgent);
    console.log('- Is Admin:', window.hphDashboard.isAdmin);
    </script>
    
    <!-- UNIFIED DASHBOARD SYSTEM CSS -->
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/framework/features/dashboard/dashboard-unified.css?v=<?php echo filemtime(get_template_directory() . '/assets/css/framework/features/dashboard/dashboard-unified.css'); ?>">
    
    <!-- ENHANCED DASHBOARD JAVASCRIPT -->
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/dashboard-enhanced.js?v=<?php echo filemtime(get_template_directory() . '/assets/js/dashboard-enhanced.js'); ?>" defer></script>
</head>
<body <?php body_class('dashboard-page'); ?>>

<div class="dashboard-wrapper">
    <!-- Dashboard Sidebar -->
    <?php 
    get_template_part('template-parts/dashboard/dashboard', 'header', [
        'is_agent' => $is_agent,
        'is_admin' => $is_admin,
        'user_role' => $user_role,
        'user' => $current_user
    ]); 
    ?>
    
    <!-- Main Content Area -->
    <main class="dashboard-main">
        
        <!-- Dashboard Content -->
        <div class="dashboard-content">
        <?php
        // Load appropriate section template
        $allowed_sections = [
            'overview',
            'listings',
            'open-houses', 
            'marketing',
            'leads',
            'analytics',
            'favorites',
            'searches',
            'profile'
        ];
        
        if (in_array($current_section, $allowed_sections)) {
            // Check agent-only sections (admins have access to everything)
            $agent_only = ['open-houses', 'marketing', 'leads', 'analytics'];
            
            if (in_array($current_section, $agent_only) && !$is_agent && !$is_admin) {
                // Redirect non-agents/non-admins trying to access agent sections
                $current_section = 'overview';
            }
            
            // Map sections to their actual template files
            $section_templates = [
                'overview' => 'template-parts/dashboard/dashboard-overview',
                'listings' => 'template-parts/dashboard/sections/listings-management',
                'open-houses' => 'template-parts/dashboard/sections/open-houses-management',
                'marketing' => 'template-parts/dashboard/sections/marketing-materials',
                'leads' => 'template-parts/dashboard/sections/leads-management',
                'appointments' => 'template-parts/dashboard/sections/appointments-management',
                'analytics' => 'template-parts/dashboard/sections/analytics',
                'favorites' => 'template-parts/dashboard/sections/favorites',
                'searches' => 'template-parts/dashboard/sections/saved-searches',
                'profile' => 'template-parts/dashboard/sections/profile-settings'
            ];
            
            $template_file = $section_templates[$current_section] ?? 'template-parts/dashboard/dashboard-overview';
            
            if (locate_template($template_file . '.php')) {
                // Load the requested section template
                get_template_part($template_file, '', [
                    'is_agent' => $is_agent,
                    'is_admin' => $is_admin,
                    'user_role' => $user_role,
                    'user' => $current_user,
                    'current_section' => $current_section
                ]);
            } else {
                // Fallback to overview if section template doesn't exist
                echo '<!-- Template file not found: ' . $template_file . '.php -->';
                get_template_part('template-parts/dashboard/dashboard', 'overview', [
                    'is_agent' => $is_agent,
                    'is_admin' => $is_admin,
                    'user_role' => $user_role,
                    'user' => $current_user,
                    'current_section' => $current_section
                ]);
            }
        } else {
            // Invalid section, show overview
            get_template_part('template-parts/dashboard/dashboard', 'overview', [
                'is_agent' => $is_agent,
                'is_admin' => $is_admin,
                'user_role' => $user_role,
                'user' => $current_user
            ]);
        }
        ?>
        </div>
    </main>
</div>

<!-- Loading Overlay -->
<div class="dashboard-loading-overlay" id="loadingOverlay" style="display: none;">
    <div class="loading-spinner"></div>
</div>

<!-- Toast Notifications Container -->
<div class="toast-container" id="toastContainer"></div>

<!-- Include Dashboard Modals -->
<?php
get_template_part('template-parts/dashboard/modals/listing', 'form-modal');
get_template_part('template-parts/dashboard/modals/quick', 'actions-modal');
?>


<?php wp_footer(); ?>

<script>
// Dashboard AJAX Configuration
window.hphDashboard = {
    ajaxurl: '<?php echo admin_url('admin-ajax.php'); ?>',
    nonce: '<?php echo wp_create_nonce('hph_dashboard_nonce'); ?>',
    user_id: <?php echo get_current_user_id(); ?>,
    is_agent: <?php echo $is_agent ? 'true' : 'false'; ?>,
    current_section: '<?php echo $current_section; ?>'
};

// Dashboard Main JavaScript
(function() {
    'use strict';
    
    // Initialize dashboard
    document.addEventListener('DOMContentLoaded', function() {
        
        // Handle AJAX navigation (optional - for SPA-like experience)
        const navLinks = document.querySelectorAll('.sidebar-nav-link');
        const mainContent = document.querySelector('.dashboard-content');
        const loadingOverlay = document.getElementById('loadingOverlay');
        
        // Toast notification system
        window.showToast = function(message, type = 'info') {
            const toastContainer = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `
                <div class="toast-content">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'times-circle' : 'info-circle'}"></i>
                    <span>${message}</span>
                </div>
                <button class="toast-close">&times;</button>
            `;
            
            toastContainer.appendChild(toast);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                toast.classList.add('fade-out');
                setTimeout(() => toast.remove(), 300);
            }, 5000);
            
            // Manual close
            toast.querySelector('.toast-close').addEventListener('click', () => {
                toast.classList.add('fade-out');
                setTimeout(() => toast.remove(), 300);
            });
        };
        
        // Handle responsive sidebar
        const handleResize = () => {
            const sidebar = document.querySelector('.dashboard-sidebar');
            if (window.innerWidth > 1024) {
                sidebar?.classList.remove('show');
            }
        };
        
        window.addEventListener('resize', handleResize);
        
        // Initialize tooltips (if using a tooltip library)
        const tooltips = document.querySelectorAll('[data-tooltip]');
        tooltips.forEach(el => {
            // Initialize tooltips here
        });
        
        // Handle form submissions with AJAX
        const dashboardForms = document.querySelectorAll('.dashboard-form');
        dashboardForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Show loading
                loadingOverlay.style.display = 'flex';
                
                const formData = new FormData(form);
                
                fetch(form.action, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    loadingOverlay.style.display = 'none';
                    if (data.success) {
                        showToast(data.message, 'success');
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(error => {
                    loadingOverlay.style.display = 'none';
                    showToast('An error occurred. Please try again.', 'error');
                });
            });
        });
        
    });
})();
</script>

<style>
/* Toast Notifications */
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    pointer-events: none;
}

.toast {
    background: white;
    border-radius: var(--hph-radius-lg);
    box-shadow: var(--hph-shadow-xl);
    padding: var(--hph-space-4);
    margin-bottom: var(--hph-space-3);
    min-width: 300px;
    max-width: 400px;
    pointer-events: auto;
    animation: slideIn 0.3s ease;
    position: relative;
}

.toast-content {
    display: flex;
    align-items: center;
    gap: var(--hph-space-3);
}

.toast.toast-success { border-left: 4px solid var(--hph-success); }
.toast.toast-error { border-left: 4px solid var(--hph-danger); }
.toast.toast-info { border-left: 4px solid var(--hph-primary); }

.toast-close {
    position: absolute;
    top: var(--hph-space-2);
    right: var(--hph-space-2);
    background: none;
    border: none;
    font-size: var(--hph-text-xl);
    cursor: pointer;
    color: var(--hph-gray-500);
}

.toast.fade-out {
    animation: slideOut 0.3s ease;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

/* Loading Overlay */
.dashboard-loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9998;
}
</style>

</body>
</html>
