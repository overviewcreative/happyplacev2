<?php
/**
 * Template Name: Dashboard
 * 
 * Frontend user dashboard for property management and user interactions
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Security check - ensure user is logged in
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

// Get current user info
$current_user = wp_get_current_user();
$user_roles = $current_user->roles;
$is_agent = in_array('agent', $user_roles) || in_array('administrator', $user_roles);

// Override body class for dashboard
add_filter('body_class', function($classes) {
    $classes[] = 'hph-dashboard-body';
    return $classes;
});

get_header(); ?>
    
    <!-- Dashboard Wrapper -->
    <div id="dashboardWrapper" style="
        display: flex;
        min-height: 100vh;
        background: var(--hph-gray-50);
        font-family: var(--hph-font-primary);
    ">
        
        <!-- Mobile Menu Toggle -->
        <button id="mobileMenuToggle" aria-label="<?php _e('Toggle Dashboard Menu', 'happy-place-theme'); ?>" style="
            display: none;
            position: fixed;
            top: var(--hph-padding-4);
            left: var(--hph-padding-4);
            z-index: 1000;
            background: var(--hph-white);
            border: 1px solid var(--hph-gray-200);
            border-radius: var(--hph-border-radius);
            padding: var(--hph-padding-3);
            box-shadow: var(--hph-shadow-sm);
            cursor: pointer;
        ">
            <span style="display: block; width: 20px; height: 2px; background: var(--hph-gray-800); margin: 3px 0; transition: all 0.2s ease;"></span>
            <span style="display: block; width: 20px; height: 2px; background: var(--hph-gray-800); margin: 3px 0; transition: all 0.2s ease;"></span>
            <span style="display: block; width: 20px; height: 2px; background: var(--hph-gray-800); margin: 3px 0; transition: all 0.2s ease;"></span>
        </button>

        <!-- Dashboard Sidebar -->
        <?php get_template_part('template-parts/dashboard/dashboard-navigation', null, ['is_agent' => $is_agent]); ?>

        <!-- Main Content Area -->
        <main id="dashboardContent" style="
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: var(--hph-gray-50);
        ">
            
            <!-- Dashboard Header -->
            <?php get_template_part('template-parts/dashboard/dashboard-header', null, [
                'user' => $current_user,
                'is_agent' => $is_agent
            ]); ?>

            <!-- Dashboard Content -->
            <div style="
                flex: 1;
                padding: var(--hph-padding-6);
                overflow-y: auto;
                background: var(--hph-gray-50);
            ">
                <?php
                // Determine which dashboard to show based on URL hash or default
                $dashboard_section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : 'overview';
                
                // Add some debugging
                if (HPH_DEV_MODE) {
                    echo '<!-- Dashboard Section: ' . esc_html($dashboard_section) . ' -->';
                    echo '<!-- User Role: ' . ($is_agent ? 'Agent' : 'User') . ' -->';
                }
                
                switch ($dashboard_section) {
                    case 'listings':
                        if ($is_agent) {
                            if (locate_template('template-parts/dashboard/sections/listings-management.php')) {
                                get_template_part('template-parts/dashboard/sections/listings-management');
                            } else {
                                echo '<div class="hph-error"><h3>Listings Management</h3><p>Template part coming soon...</p></div>';
                            }
                        } else {
                            if (locate_template('template-parts/dashboard/sections/saved-listings.php')) {
                                get_template_part('template-parts/dashboard/sections/saved-listings');
                            } else {
                                echo '<div class="hph-error"><h3>Saved Listings</h3><p>Template part coming soon...</p></div>';
                            }
                        }
                        break;
                        
                    case 'leads':
                        if ($is_agent) {
                            if (locate_template('template-parts/dashboard/sections/leads-management.php')) {
                                get_template_part('template-parts/dashboard/sections/leads-management');
                            } else {
                                echo '<div class="hph-error"><h3>Lead Management</h3><p>Template part coming soon...</p></div>';
                            }
                        }
                        break;
                        
                    case 'analytics':
                        if ($is_agent) {
                            if (locate_template('template-parts/dashboard/sections/analytics-overview.php')) {
                                get_template_part('template-parts/dashboard/sections/analytics-overview');
                            } else {
                                echo '<div class="hph-error"><h3>Analytics</h3><p>Template part coming soon...</p></div>';
                            }
                        }
                        break;
                        
                    case 'profile':
                        if (locate_template('template-parts/dashboard/sections/profile-settings.php')) {
                            get_template_part('template-parts/dashboard/sections/profile-settings');
                        } else {
                            echo '<div class="hph-error"><h3>Profile Settings</h3><p>Template part coming soon...</p></div>';
                        }
                        break;
                        
                    case 'favorites':
                        if (locate_template('template-parts/dashboard/sections/favorites.php')) {
                            get_template_part('template-parts/dashboard/sections/favorites');
                        } else {
                            echo '<div class="hph-error"><h3>Favorites</h3><p>Template part coming soon...</p></div>';
                        }
                        break;
                        
                    case 'searches':
                        if (locate_template('template-parts/dashboard/sections/saved-searches.php')) {
                            get_template_part('template-parts/dashboard/sections/saved-searches');
                        } else {
                            echo '<div class="hph-error"><h3>Saved Searches</h3><p>Template part coming soon...</p></div>';
                        }
                        break;
                        
                    default:
                        // Dashboard Overview
                        if (locate_template('template-parts/dashboard/dashboard-overview.php')) {
                            get_template_part('template-parts/dashboard/dashboard-overview', null, [
                                'user' => $current_user,
                                'is_agent' => $is_agent
                            ]);
                        } else {
                            echo '<div class="hph-error"><h3>Dashboard Overview</h3><p>Template part not found. Please check template files.</p></div>';
                        }
                        break;
                }
                ?>
            </div>
        </main>
    </div>

    <!-- Dashboard Modals -->
    <?php if ($is_agent): ?>
        <?php get_template_part('template-parts/dashboard/modals/listing-form-modal'); ?>
    <?php endif; ?>
    
    <?php get_template_part('template-parts/dashboard/modals/quick-actions-modal'); ?>
    
    <!-- Dashboard JavaScript initialization -->
    <script>
    // Initialize dashboard when DOM is ready
    jQuery(document).ready(function($) {
        if (typeof DashboardController !== 'undefined') {
            DashboardController.init();
        } else {
            console.log('DashboardController will be loaded via assets system');
            // Fallback - try again after a short delay
            setTimeout(function() {
                if (typeof DashboardController !== 'undefined') {
                    DashboardController.init();
                } else {
                    console.warn('DashboardController not loaded - check asset enqueue');
                }
            }, 500);
        }
    });
    </script>

<?php get_footer(); ?>