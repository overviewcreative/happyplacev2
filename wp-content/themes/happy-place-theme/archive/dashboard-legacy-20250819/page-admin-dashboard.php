<?php
/**
 * Template Name: Admin Dashboard
 * 
 * Frontend admin dashboard page template
 * Utilizes HPH Framework for consistent styling
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get dashboard instance
$dashboard = \HappyPlace\Dashboard\Frontend_Admin_Dashboard::get_instance();

get_header(); ?>

<div class="hph-dashboard-wrapper">
    <div class="container-fluid">
        <div class="row">
            <!-- Dashboard Navigation Sidebar -->
            <div class="col-lg-3 col-xl-2 hph-dashboard-sidebar">
                <?php $dashboard->render_navigation(); ?>
            </div>
            
            <!-- Main Dashboard Content -->
            <div class="col-lg-9 col-xl-10 hph-dashboard-main">
                <div class="hph-dashboard-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h1 class="dashboard-title">
                                <?php
                                $sections = $dashboard->get_accessible_sections();
                                $current = $dashboard->get_current_section();
                                echo esc_html($sections[$current]['title'] ?? 'Dashboard');
                                ?>
                            </h1>
                            <p class="dashboard-subtitle text-muted">
                                <?php _e('Manage your real estate business efficiently', 'happy-place'); ?>
                            </p>
                        </div>
                        <div class="col-auto">
                            <div class="dashboard-actions">
                                <!-- Global quick actions will go here -->
                                <button class="btn btn-outline-primary btn-sm" id="refresh-data">
                                    <span class="hph-icon-refresh"></span>
                                    <?php _e('Refresh', 'happy-place'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="hph-dashboard-content">
                    <?php $dashboard->render_content(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dashboard Modals Container -->
<div id="hph-dashboard-modals"></div>

<!-- Loading Overlay -->
<div id="hph-loading-overlay" class="hph-loading-overlay" style="display: none;">
    <div class="loading-spinner">
        <div class="spinner"></div>
        <p class="loading-text"><?php _e('Loading...', 'happy-place'); ?></p>
    </div>
</div>

<?php get_footer(); ?>