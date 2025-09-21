<?php
/**
 * Enhanced Open House Archive Template - Corrected for Open House Post Type
 *
 * @package HappyPlaceTheme
 * @version 2.0.0
 */

// Debug: Log that this template is being used
if (HPH_DEV_MODE && current_user_can('manage_options')) {
    error_log('OPEN HOUSE ARCHIVE TEMPLATE LOADED: archive-open-house.php');
}

// Open house assets are now handled automatically by theme-assets.php

get_header();


// Get current view mode (default: calendar)
$view_mode = isset($_GET['view']) ? sanitize_key($_GET['view']) : 'calendar';
$valid_views = ['calendar', 'list', 'grid', 'map'];
if (!in_array($view_mode, $valid_views)) {
    $view_mode = 'calendar';
}

// Get filter parameters
$date_filter = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : date('Y-m-d');
$city_filter = isset($_GET['city']) ? sanitize_text_field($_GET['city']) : '';
$price_range = isset($_GET['price_range']) ? sanitize_text_field($_GET['price_range']) : '';
$agent_filter = isset($_GET['agent']) ? sanitize_text_field($_GET['agent']) : '';

// Setup calendar arguments for open_house post type
$calendar_args = array(
    'view' => $view_mode === 'calendar' ? 'month' : ($view_mode === 'list' ? 'list' : 'grid'),
    'post_type' => 'open_house', // Correct post type
    'current_date' => $date_filter,
    'show_navigation' => true,
    'show_view_selector' => false,
    'css_classes' => array('calendar-open-houses', 'calendar-view-' . $view_mode),
    'calendar_id' => 'open-houses-calendar',
    'posts_per_page' => 50
);

// Add filters if specified
$additional_meta_query = array();

if (!empty($city_filter)) {
    // We need to join with listing data to filter by city
    // This would require a custom query modification
}

if (!empty($agent_filter)) {
    $additional_meta_query[] = array(
        'key' => 'hosting_agent',
        'value' => $agent_filter,
        'compare' => '='
    );
}

if (!empty($additional_meta_query)) {
    $calendar_args['additional_meta_query'] = $additional_meta_query;
}
?>

<!-- Open House Archive Hero -->
<div class="hph-archive-hero hph-archive-open-house-hero">
    <div class="hph-container">
        <div class="hph-archive-hero-content">
            <div class="hph-archive-hero-text">
                <h1 class="hph-archive-title">
                    <i class="fas fa-home hph-archive-icon" aria-hidden="true"></i>
                    <?php esc_html_e('Open Houses', 'happy-place-theme'); ?>
                </h1>
                <p class="hph-archive-description">
                    <?php esc_html_e('Find upcoming open houses and schedule your visit. View available properties by calendar, list, or map.', 'happy-place-theme'); ?>
                </p>
                
                <!-- Quick Stats -->
                <?php
                // Get open house counts using bridge functions with error handling
                $today_events = function_exists('hpt_get_todays_open_houses') ? hpt_get_todays_open_houses() : array();
                $upcoming_events = function_exists('hpt_get_upcoming_open_houses') ? hpt_get_upcoming_open_houses(100) : array();
                $featured_events = function_exists('hpt_get_featured_open_houses') ? hpt_get_featured_open_houses(10) : array();

                $today_count = is_array($today_events) ? count($today_events) : 0;
                $upcoming_count = is_array($upcoming_events) ? count($upcoming_events) : 0;
                $featured_count = is_array($featured_events) ? count($featured_events) : 0;
                ?>
                <div class="hph-archive-stats">
                    <?php
                    // Debug check for total open house posts
                    $total_open_houses = get_posts(array(
                        'post_type' => 'open_house',
                        'posts_per_page' => -1,
                        'post_status' => 'publish',
                        'fields' => 'ids'
                    ));
                    $total_count = count($total_open_houses);
                    ?>

                    <div class="hph-stat-item">
                        <span class="hph-stat-number"><?php echo esc_html($total_count); ?></span>
                        <span class="hph-stat-label"><?php esc_html_e('Total Events', 'happy-place-theme'); ?></span>
                    </div>

                    <?php if ($today_count > 0) : ?>
                    <div class="hph-stat-item">
                        <span class="hph-stat-number"><?php echo esc_html($today_count); ?></span>
                        <span class="hph-stat-label"><?php esc_html_e('Today', 'happy-place-theme'); ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="hph-stat-item">
                        <span class="hph-stat-number"><?php echo esc_html($upcoming_count); ?></span>
                        <span class="hph-stat-label"><?php esc_html_e('Upcoming', 'happy-place-theme'); ?></span>
                    </div>

                    <?php if ($featured_count > 0) : ?>
                    <div class="hph-stat-item">
                        <span class="hph-stat-number"><?php echo esc_html($featured_count); ?></span>
                        <span class="hph-stat-label"><?php esc_html_e('Featured', 'happy-place-theme'); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<main id="primary" class="hph-site-main">
    <div class="hph-container hph-container-wide">
        
        <!-- View Controls and Filters -->
        <div class="hph-archive-controls">
            <div class="hph-archive-controls-header">
                
                <!-- View Mode Selector -->
                <div class="hph-view-selector">
                    <div class="hph-btn-group" role="group" aria-label="<?php esc_attr_e('View Options', 'happy-place-theme'); ?>">
                        <a href="<?php echo esc_url(add_query_arg('view', 'calendar')); ?>" 
                           class="hph-btn hph-btn-outline <?php echo $view_mode === 'calendar' ? 'active' : ''; ?>"
                           title="<?php esc_attr_e('Calendar View', 'happy-place-theme'); ?>">
                            <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                            <span class="hph-btn-text"><?php esc_html_e('Calendar', 'happy-place-theme'); ?></span>
                        </a>
                        <a href="<?php echo esc_url(add_query_arg('view', 'list')); ?>" 
                           class="hph-btn hph-btn-outline <?php echo $view_mode === 'list' ? 'active' : ''; ?>"
                           title="<?php esc_attr_e('List View', 'happy-place-theme'); ?>">
                            <i class="fas fa-list" aria-hidden="true"></i>
                            <span class="hph-btn-text"><?php esc_html_e('List', 'happy-place-theme'); ?></span>
                        </a>
                        <a href="<?php echo esc_url(add_query_arg('view', 'grid')); ?>" 
                           class="hph-btn hph-btn-outline <?php echo $view_mode === 'grid' ? 'active' : ''; ?>"
                           title="<?php esc_attr_e('Grid View', 'happy-place-theme'); ?>">
                            <i class="fas fa-th" aria-hidden="true"></i>
                            <span class="hph-btn-text"><?php esc_html_e('Grid', 'happy-place-theme'); ?></span>
                        </a>
                        <a href="<?php echo esc_url(add_query_arg('view', 'map')); ?>" 
                           class="hph-btn hph-btn-outline <?php echo $view_mode === 'map' ? 'active' : ''; ?>"
                           title="<?php esc_attr_e('Map View', 'happy-place-theme'); ?>">
                            <i class="fas fa-map" aria-hidden="true"></i>
                            <span class="hph-btn-text"><?php esc_html_e('Map', 'happy-place-theme'); ?></span>
                        </a>
                    </div>
                </div>
                
                <!-- Filter Toggle -->
                <button class="hph-btn hph-btn-outline hph-filter-toggle" 
                        data-toggle="collapse" 
                        data-target="#open-house-filters"
                        aria-expanded="false"
                        aria-controls="open-house-filters">
                    <i class="fas fa-filter" aria-hidden="true"></i>
                    <?php esc_html_e('Filters', 'happy-place-theme'); ?>
                    <i class="fas fa-chevron-down hph-toggle-icon" aria-hidden="true"></i>
                </button>
                
            </div>
            
            <!-- Advanced Filters Panel -->
            <div id="open-house-filters" class="hph-archive-filters collapse">
                <form method="get" class="hph-filter-form">
                    <input type="hidden" name="view" value="<?php echo esc_attr($view_mode); ?>">
                    
                    <div class="hph-filter-grid">
                        
                        <!-- Date Filter -->
                        <div class="hph-filter-group">
                            <label for="date-filter" class="hph-filter-label">
                                <?php esc_html_e('Date', 'happy-place-theme'); ?>
                            </label>
                            <input type="date" 
                                   id="date-filter" 
                                   name="date" 
                                   class="hph-form-input" 
                                   value="<?php echo esc_attr($date_filter); ?>">
                        </div>
                        
                        <!-- Agent Filter -->
                        <div class="hph-filter-group">
                            <label for="agent-filter" class="hph-filter-label">
                                <?php esc_html_e('Agent', 'happy-place-theme'); ?>
                            </label>
                            <select id="agent-filter" name="agent" class="hph-form-select">
                                <option value=""><?php esc_html_e('All Agents', 'happy-place-theme'); ?></option>
                                <?php
                                // Get agents with open houses
                                $agents = get_users(array(
                                    'role' => 'agent',
                                    'orderby' => 'display_name'
                                ));
                                foreach ($agents as $agent) :
                                ?>
                                <option value="<?php echo esc_attr($agent->ID); ?>" <?php selected($agent_filter, $agent->ID); ?>>
                                    <?php echo esc_html($agent->display_name); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- City Filter (Complex - requires join with listing data) -->
                        <div class="hph-filter-group">
                            <label for="city-filter" class="hph-filter-label">
                                <?php esc_html_e('City', 'happy-place-theme'); ?>
                            </label>
                            <input type="text" 
                                   id="city-filter" 
                                   name="city" 
                                   class="hph-form-input" 
                                   placeholder="<?php esc_attr_e('Enter city...', 'happy-place-theme'); ?>"
                                   value="<?php echo esc_attr($city_filter); ?>">
                        </div>
                        
                        <!-- Price Range Filter (Complex - requires join with listing data) -->
                        <div class="hph-filter-group">
                            <label for="price-range-filter" class="hph-filter-label">
                                <?php esc_html_e('Price Range', 'happy-place-theme'); ?>
                            </label>
                            <select id="price-range-filter" name="price_range" class="hph-form-select">
                                <option value=""><?php esc_html_e('Any Price', 'happy-place-theme'); ?></option>
                                <option value="0-300000" <?php selected($price_range, '0-300000'); ?>><?php esc_html_e('Under $300K', 'happy-place-theme'); ?></option>
                                <option value="300000-500000" <?php selected($price_range, '300000-500000'); ?>><?php esc_html_e('$300K - $500K', 'happy-place-theme'); ?></option>
                                <option value="500000-750000" <?php selected($price_range, '500000-750000'); ?>><?php esc_html_e('$500K - $750K', 'happy-place-theme'); ?></option>
                                <option value="750000-1000000" <?php selected($price_range, '750000-1000000'); ?>><?php esc_html_e('$750K - $1M', 'happy-place-theme'); ?></option>
                                <option value="1000000-999999999" <?php selected($price_range, '1000000-999999999'); ?>><?php esc_html_e('Over $1M', 'happy-place-theme'); ?></option>
                            </select>
                        </div>
                        
                    </div>
                    
                    <div class="hph-filter-actions">
                        <button type="submit" class="hph-btn hph-btn-primary">
                            <i class="fas fa-search" aria-hidden="true"></i>
                            <?php esc_html_e('Apply Filters', 'happy-place-theme'); ?>
                        </button>
                        <a href="<?php echo esc_url(remove_query_arg(array('date', 'agent', 'city', 'price_range'))); ?>" class="hph-btn hph-btn-ghost">
                            <?php esc_html_e('Reset', 'happy-place-theme'); ?>
                        </a>
                    </div>
                    
                </form>
            </div>
        </div>
        
        <!-- Main Content Area -->
        <div class="hph-archive-content">
            
            <?php if ($view_mode === 'map') : ?>
                
                <!-- Map View -->
                <div id="open-houses-map" class="hph-open-houses-map" data-view="map">
                    <div class="hph-map-container" style="height: 600px;">
                        <div class="hph-map-loading">
                            <i class="fas fa-spinner fa-spin" aria-hidden="true"></i>
                            <?php esc_html_e('Loading map...', 'happy-place-theme'); ?>
                        </div>
                        <?php 
                        // Map implementation would go here
                        // You'd need to get coordinates from related listings
                        ?>
                    </div>
                </div>
                
            <?php else : ?>
                
                <!-- Calendar/List/Grid Views -->
                <div class="hph-calendar-container">
                    <?php
                    // Use our universal calendar component for open houses
                    $template_args = array_merge($calendar_args, array(
                        'template_context' => 'open_house_archive',
                        'current_view' => $view_mode
                    ));

                    get_template_part('template-parts/components/universal-calendar', '', $template_args);
                    ?>
                </div>
                
            <?php endif; ?>
            
        </div>
        
    </div>
</main>

<?php
get_footer();
?>