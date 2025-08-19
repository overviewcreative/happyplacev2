<?php
/**
 * Agent Dashboard Main Template
 * 
 * @package HappyPlaceTheme
 * @subpackage Dashboard
 * 
 * File Location: /wp-content/themes/happy-place/templates/dashboard/dashboard-main.php
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if user is logged in
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

// Get current user data
$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Check user capabilities
if (!current_user_can('edit_posts')) {
    wp_die('You do not have permission to access this dashboard.');
}

// Don't load standard header/footer for dashboard - it's a standalone interface
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Dashboard - <?php echo esc_html($current_user->display_name); ?></title>
    <?php 
    // Initialize the centralized dashboard assets manager
    require_once get_template_directory() . '/inc/dashboard-assets.php';
    
    wp_head();
    ?>
</head>

<body class="dashboard-body">
    <div class="dashboard-wrapper" id="dashboardWrapper">
        
        <!-- Mobile Menu Toggle -->
        <button class="dashboard-mobile-toggle" id="mobileMenuToggle" aria-label="Toggle menu">
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
        </button>
        
        <!-- Sidebar Navigation -->
        <aside class="dashboard-sidebar" id="dashboardSidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <h2 style="color: #fff; margin: 0;">Happy Place</h2>
                    <!-- <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo.svg" alt="Happy Place"> -->
                </div>
                <button class="sidebar-close" id="sidebarClose" aria-label="Close sidebar">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M18 6L6 18M6 6l12 12" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
            
            <nav class="sidebar-nav">
                <?php
                // Get current page for navigation active states
                $nav_current_page = get_query_var('dashboard_page', '');
                ?>
                <ul class="nav-menu">
                    <li class="nav-item <?php echo empty($nav_current_page) ? 'active' : ''; ?>">
                        <a href="/agent-dashboard/" class="nav-link">
                            <svg class="nav-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                            </svg>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($nav_current_page === 'listings') ? 'active' : ''; ?>">
                        <a href="/agent-dashboard/?dashboard_page=listings" class="nav-link">
                            <svg class="nav-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                            </svg>
                            <span>Listings</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($nav_current_page === 'open-houses') ? 'active' : ''; ?>">
                        <a href="/agent-dashboard/?dashboard_page=open-houses" class="nav-link">
                            <svg class="nav-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                            </svg>
                            <span>Open Houses</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($nav_current_page === 'leads') ? 'active' : ''; ?>">
                        <a href="/agent-dashboard/?dashboard_page=leads" class="nav-link">
                            <svg class="nav-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                            </svg>
                            <span>Leads</span>
                            <span class="nav-badge">3</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($nav_current_page === 'transactions') ? 'active' : ''; ?>">
                        <a href="/agent-dashboard/?dashboard_page=transactions" class="nav-link">
                            <svg class="nav-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Transactions</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($nav_current_page === 'profile') ? 'active' : ''; ?>">
                        <a href="/agent-dashboard/?dashboard_page=profile" class="nav-link">
                            <svg class="nav-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                            <span>My Profile</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($nav_current_page === 'marketing') ? 'active' : ''; ?>">
                        <a href="/agent-dashboard/?dashboard_page=marketing" class="nav-link">
                            <svg class="nav-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M15 8a3 3 0 10-2.977-2.63l-4.94 2.47a3 3 0 100 4.319l4.94 2.47a3 3 0 10.895-1.789l-4.94-2.47a3.027 3.027 0 000-.74l4.94-2.47C13.456 7.68 14.19 8 15 8z"/>
                            </svg>
                            <span>Marketing Suite</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($nav_current_page === 'cma') ? 'active' : ''; ?>">
                        <a href="/agent-dashboard/?dashboard_page=cma" class="nav-link">
                            <svg class="nav-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"/>
                            </svg>
                            <span>CMA Generator</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($nav_current_page === 'analytics') ? 'active' : ''; ?>">
                        <a href="/agent-dashboard/?dashboard_page=analytics" class="nav-link">
                            <svg class="nav-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                            </svg>
                            <span>Analytics</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($nav_current_page === 'resources') ? 'active' : ''; ?>">
                        <a href="/agent-dashboard/?dashboard_page=resources" class="nav-link">
                            <svg class="nav-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
                            </svg>
                            <span>Resources</span>
                        </a>
                    </li>
                </ul>
                
                <div class="nav-divider"></div>
                
                <ul class="nav-menu nav-secondary">
                    <li class="nav-item <?php echo ($nav_current_page === 'settings') ? 'active' : ''; ?>">
                        <a href="/agent-dashboard/?dashboard_page=settings" class="nav-link">
                            <svg class="nav-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                            </svg>
                            <span>Settings</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo wp_logout_url(); ?>" class="nav-link">
                            <svg class="nav-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                            </svg>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-info">
                    <img src="<?php echo get_avatar_url($user_id, ['size' => 40]); ?>" alt="<?php echo esc_attr($current_user->display_name); ?>" class="user-avatar">
                    <div class="user-details">
                        <span class="user-name"><?php echo esc_html($current_user->display_name); ?></span>
                        <span class="user-role">Real Estate Agent</span>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Main Content Area -->
        <main class="dashboard-main">
            <!-- Top Header Bar -->
            <header class="dashboard-header">
                <div class="header-left">
                    <?php
                    // Get the current page for dynamic title
                    $current_page = get_query_var('dashboard_page', '');
                    $page_titles = array(
                        'listings' => 'My Listings',
                        'open-houses' => 'Open Houses',
                        'leads' => 'Lead Management',
                        'transactions' => 'Transactions',
                        'profile' => 'My Profile',
                        'marketing' => 'Marketing Suite',
                        'cma' => 'CMA Generator',
                        'analytics' => 'Analytics',
                        'resources' => 'Resources',
                        'settings' => 'Settings'
                    );
                    $page_subtitles = array(
                        'listings' => 'Manage your property listings',
                        'open-houses' => 'Schedule and manage open houses',
                        'leads' => 'Track and nurture your leads',
                        'transactions' => 'Monitor your deals in progress',
                        'profile' => 'Update your professional profile',
                        'marketing' => 'Create marketing materials',
                        'cma' => 'Generate comparative market analyses',
                        'analytics' => 'View your performance metrics',
                        'resources' => 'Access training and resources',
                        'settings' => 'Manage your account settings'
                    );
                    
                    $title = isset($page_titles[$current_page]) ? $page_titles[$current_page] : 'Dashboard';
                    $subtitle = isset($page_subtitles[$current_page]) ? $page_subtitles[$current_page] : 'Welcome back, ' . esc_html($current_user->first_name ?: $current_user->display_name) . '!';
                    ?>
                    <h1 class="dashboard-title"><?php echo esc_html($title); ?></h1>
                    <p class="dashboard-subtitle"><?php echo esc_html($subtitle); ?></p>
                </div>
                
                <div class="header-right">
                    <!-- Search Bar -->
                    <div class="header-search">
                        <svg class="search-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                        </svg>
                        <input type="search" placeholder="Search listings, leads, or transactions..." class="search-input">
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="header-actions">
                        <?php if ($current_page === 'listings'): ?>
                        <!-- Listings-specific actions -->
                        <button class="btn btn-outline btn-sm" id="importListingsBtn">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                <path d="M2 13a1 1 0 011-1h10a1 1 0 110 2H3a1 1 0 01-1-1zm2.293-6.707a1 1 0 011.414 0L7 7.586V2a1 1 0 112 0v5.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"/>
                            </svg>
                            <span>Import</span>
                        </button>
                        <button class="btn btn-outline btn-sm" id="exportListingsBtn">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                <path d="M2 13a1 1 0 011-1h10a1 1 0 110 2H3a1 1 0 01-1-1zM10.707 6.707l3-3a1 1 0 00-1.414-1.414L11 3.586V2a1 1 0 10-2 0v1.586L7.707 2.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0z"/>
                            </svg>
                            <span>Export</span>
                        </button>
                        <button class="btn btn-primary btn-sm" 
                                data-bs-toggle="modal" 
                                data-bs-target="#listingFormModal"
                                data-listing-id="0">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                <path d="M8 2a1 1 0 011 1v4h4a1 1 0 110 2H9v4a1 1 0 11-2 0V9H3a1 1 0 110-2h4V3a1 1 0 011-1z"/>
                            </svg>
                            <span>Add Listing</span>
                        </button>
                        <?php else: ?>
                        <!-- Default actions -->
                        <!-- Notifications -->
                        <button class="action-btn notification-btn" aria-label="Notifications">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                            </svg>
                            <span class="notification-badge">5</span>
                        </button>
                        
                        <!-- Messages -->
                        <button class="action-btn" aria-label="Messages">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                            </svg>
                        </button>
                        
                        <!-- Add New -->
                        <button class="btn btn-primary btn-sm" id="quickAddBtn">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                <path d="M8 2a1 1 0 011 1v4h4a1 1 0 110 2H9v4a1 1 0 11-2 0V9H3a1 1 0 110-2h4V3a1 1 0 011-1z"/>
                            </svg>
                            <span>Add New</span>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </header>
            
            <!-- Dashboard Content -->
            <div class="dashboard-content" id="dashboardContent">
                <?php
                // Get the current page parameter
                $current_page = get_query_var('dashboard_page', '');
                
                // Debug: Show what page we're trying to load  
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    echo "<!-- DEBUG: Current page parameter: '" . esc_html($current_page) . "' -->";
                    echo "<!-- DEBUG: GET parameters: " . esc_html(print_r($_GET, true)) . " -->";
                    echo "<!-- DEBUG: agent_dashboard query var: " . esc_html(get_query_var('agent_dashboard')) . " -->";
                    echo "<!-- DEBUG: dashboard_page query var: " . esc_html(get_query_var('dashboard_page')) . " -->";
                }
                
                // Always show current page for debugging during development
                echo "<!-- Current dashboard page: '" . esc_html($current_page) . "' -->";
                
                // Load the appropriate content based on the page parameter
                switch($current_page) {
                    case 'listings':
                        // Load listings content from the separate template
                        $listings_template = get_template_directory() . '/templates/dashboard/dashboard-listings-content.php';
                        if (file_exists($listings_template)) {
                            include $listings_template;
                        } else {
                            // Fallback
                            echo '<div class="page-placeholder">';
                            echo '<h2>Listings Management</h2>';
                            echo '<p>This section is coming soon!</p>';
                            echo '</div>';
                        }
                        break;
                        
                    case 'open-houses':
                    case 'leads':
                    case 'transactions':
                    case 'profile':
                    case 'marketing':
                    case 'cma':
                    case 'analytics':
                    case 'resources':
                    case 'settings':
                        // Placeholder content for pages not yet implemented
                        $page_name = ucfirst(str_replace('-', ' ', $current_page));
                        echo '<div class="page-placeholder">';
                        echo '<div class="placeholder-icon">';
                        echo '<svg width="64" height="64" viewBox="0 0 64 64" fill="currentColor" opacity="0.3">';
                        echo '<path d="M32 8l4 4v8h8l4 4v32H16V24l4-4h8v-8l4-4z"/>';
                        echo '</svg>';
                        echo '</div>';
                        echo '<h2>' . esc_html($page_name) . '</h2>';
                        echo '<p>This section is coming soon!</p>';
                        echo '<p>We\'re working hard to bring you this feature. Check back soon for updates.</p>';
                        echo '</div>';
                        break;
                        
                    default:
                        // Default dashboard content (home page)
                        ?>
                        <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-primary">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-label">Active Listings</h3>
                            <p class="stat-value">24</p>
                            <p class="stat-change stat-change-up">
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor">
                                    <path d="M5.293 3.293a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L6 5.414 3.707 7.707a1 1 0 01-1.414-1.414l3-3z"/>
                                </svg>
                                <span>12% from last month</span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-success">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-label">Closed This Month</h3>
                            <p class="stat-value">6</p>
                            <p class="stat-change stat-change-up">
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor">
                                    <path d="M5.293 3.293a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L6 5.414 3.707 7.707a1 1 0 01-1.414-1.414l3-3z"/>
                                </svg>
                                <span>20% from last month</span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-warning">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-label">New Leads</h3>
                            <p class="stat-value">48</p>
                            <p class="stat-change stat-change-down">
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor">
                                    <path d="M6.707 8.707a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L6 6.586l2.293-2.293a1 1 0 011.414 1.414l-3 3z"/>
                                </svg>
                                <span>8% from last week</span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-info">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-label">Open Houses</h3>
                            <p class="stat-value">12</p>
                            <p class="stat-change">
                                <span>This week</span>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions Panel -->
                <div class="quick-actions-panel">
                    <h2 class="panel-title">Quick Actions</h2>
                    <div class="action-grid">
                        <button class="action-card" 
                                data-bs-toggle="modal" 
                                data-bs-target="#listingFormModal"
                                data-listing-id="0">
                            <svg class="action-icon" width="32" height="32" viewBox="0 0 32 32" fill="currentColor">
                                <path d="M16 3l1.917 1.917V10a1 1 0 001 1h5.083L26 13v13a2 2 0 01-2 2H8a2 2 0 01-2-2V5a2 2 0 012-2h8zm0 2H8v20h16V13h-5V8h-3z"/>
                            </svg>
                            <span class="action-label">Add Listing</span>
                        </button>
                        
                        <button class="action-card" data-action="schedule-open-house">
                            <svg class="action-icon" width="32" height="32" viewBox="0 0 32 32" fill="currentColor">
                                <path d="M10 6v2H6a2 2 0 00-2 2v14a2 2 0 002 2h20a2 2 0 002-2V10a2 2 0 00-2-2h-4V6a2 2 0 00-4 0v2h-4V6a2 2 0 00-4 0zm16 6v12H6V12h20z"/>
                            </svg>
                            <span class="action-label">Schedule Open House</span>
                        </button>
                        
                        <button class="action-card" data-action="create-cma">
                            <svg class="action-icon" width="32" height="32" viewBox="0 0 32 32" fill="currentColor">
                                <path d="M9 5a2 2 0 00-2 2v18a2 2 0 002 2h14a2 2 0 002-2V11.586a2 2 0 00-.586-1.414l-4.586-4.586A2 2 0 0018.414 5H9zm7 8v6l-2-2-2 2 4 4 4-4-2-2-2 2v-6z"/>
                            </svg>
                            <span class="action-label">Generate CMA</span>
                        </button>
                        
                        <button class="action-card" data-action="import-leads">
                            <svg class="action-icon" width="32" height="32" viewBox="0 0 32 32" fill="currentColor">
                                <path d="M16 4C9.373 4 4 9.373 4 16s5.373 12 12 12 12-5.373 12-12S22.627 4 16 4zm0 7a3 3 0 110 6 3 3 0 010-6zm0 14.2a9.2 9.2 0 01-7.735-4.2c.018-2.577 5.156-3.99 7.735-3.99 2.579 0 7.717 1.413 7.735 3.99a9.2 9.2 0 01-7.735 4.2z"/>
                            </svg>
                            <span class="action-label">Import Leads</span>
                        </button>
                        
                        <button class="action-card" data-action="create-marketing">
                            <svg class="action-icon" width="32" height="32" viewBox="0 0 32 32" fill="currentColor">
                                <path d="M20 10a4 4 0 10-3.97-3.507l-6.573 3.286a4 4 0 100 6.441l6.573 3.287a4 4 0 101.193-2.385l-6.573-3.287a4.04 4.04 0 000-.987l6.573-3.286C17.941 9.849 18.944 10 20 10z"/>
                            </svg>
                            <span class="action-label">Create Marketing</span>
                        </button>
                        
                        <button class="action-card" data-action="export-data">
                            <svg class="action-icon" width="32" height="32" viewBox="0 0 32 32" fill="currentColor">
                                <path d="M16 4l-1.414 1.414L18.172 9H8a6 6 0 000 12h8v-2H8a4 4 0 010-8h10.172l-3.586 3.586L16 16l6-6-6-6zm8 10v10a2 2 0 01-2 2H10a2 2 0 01-2-2v-3h2v3h12V14h-3v-2h3a2 2 0 012 2z"/>
                            </svg>
                            <span class="action-label">Export Data</span>
                        </button>
                    </div>
                </div>
                
                <!-- Recent Activity & Upcoming Events -->
                <div class="dashboard-grid">
                    <!-- Recent Activity -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h2 class="card-title">Recent Activity</h2>
                            <a href="#" class="card-link">View All</a>
                        </div>
                        <div class="card-body">
                            <div class="activity-feed" id="activityFeed">
                                <!-- Activity items will be loaded here via AJAX -->
                                <div class="activity-item">
                                    <div class="activity-icon activity-icon-success">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                            <path d="M13.78 4.22a1 1 0 010 1.415l-5.5 5.5a1 1 0 01-1.414 0l-2.5-2.5a1 1 0 111.414-1.414L7.5 8.94l4.78-4.72a1 1 0 011.414 0z"/>
                                        </svg>
                                    </div>
                                    <div class="activity-content">
                                        <p class="activity-text">New lead from 123 Main St listing</p>
                                        <span class="activity-time">2 hours ago</span>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-icon activity-icon-primary">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                            <path d="M8 2a.5.5 0 01.5.5v5h5a.5.5 0 010 1h-5v5a.5.5 0 01-1 0v-5h-5a.5.5 0 010-1h5v-5A.5.5 0 018 2z"/>
                                        </svg>
                                    </div>
                                    <div class="activity-content">
                                        <p class="activity-text">New listing added: 456 Oak Avenue</p>
                                        <span class="activity-time">5 hours ago</span>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-icon activity-icon-warning">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                            <path d="M8 4a.5.5 0 01.5.5V8l2.646 2.646a.5.5 0 01-.707.708l-3-3A.5.5 0 017 8V4.5A.5.5 0 018 4z"/>
                                            <circle cx="8" cy="8" r="6" fill="none" stroke="currentColor" stroke-width="1"/>
                                        </svg>
                                    </div>
                                    <div class="activity-content">
                                        <p class="activity-text">Open house scheduled for Sunday</p>
                                        <span class="activity-time">Yesterday</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Upcoming Events -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h2 class="card-title">Upcoming Events</h2>
                            <a href="#" class="card-link">View Calendar</a>
                        </div>
                        <div class="card-body">
                            <div class="events-list" id="upcomingEvents">
                                <!-- Events will be loaded here via AJAX -->
                                <div class="event-item">
                                    <div class="event-date">
                                        <span class="event-day">15</span>
                                        <span class="event-month">Nov</span>
                                    </div>
                                    <div class="event-details">
                                        <h4 class="event-title">Open House - 123 Main St</h4>
                                        <p class="event-time">2:00 PM - 4:00 PM</p>
                                    </div>
                                </div>
                                <div class="event-item">
                                    <div class="event-date">
                                        <span class="event-day">16</span>
                                        <span class="event-month">Nov</span>
                                    </div>
                                    <div class="event-details">
                                        <h4 class="event-title">Client Meeting - Johnson Family</h4>
                                        <p class="event-time">10:00 AM</p>
                                    </div>
                                </div>
                                <div class="event-item">
                                    <div class="event-date">
                                        <span class="event-day">18</span>
                                        <span class="event-month">Nov</span>
                                    </div>
                                    <div class="event-details">
                                        <h4 class="event-title">Property Showing - 789 Pine St</h4>
                                        <p class="event-time">3:30 PM</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hot Leads -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h2 class="card-title">Hot Leads</h2>
                            <a href="#" class="card-link">Manage Leads</a>
                        </div>
                        <div class="card-body">
                            <div class="leads-list" id="hotLeads">
                                <!-- Leads will be loaded here via AJAX -->
                                <!-- FollowUpBoss CRM Integration Required -->
                                <div class="lead-item">
                                    <div class="lead-avatar">JD</div>
                                    <div class="lead-info">
                                        <h4 class="lead-name">John Doe</h4>
                                        <p class="lead-details">Interested in 3BR homes under $500k</p>
                                        <span class="lead-badge lead-badge-hot">Hot Lead</span>
                                    </div>
                                    <button class="btn btn-sm btn-outline">Contact</button>
                                </div>
                                <div class="lead-item">
                                    <div class="lead-avatar">SJ</div>
                                    <div class="lead-info">
                                        <h4 class="lead-name">Sarah Johnson</h4>
                                        <p class="lead-details">First-time buyer, pre-approved</p>
                                        <span class="lead-badge lead-badge-warm">Warm Lead</span>
                                    </div>
                                    <button class="btn btn-sm btn-outline">Contact</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Performance Chart -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h2 class="card-title">Performance Overview</h2>
                            <select class="card-select">
                                <option>Last 30 days</option>
                                <option>Last 90 days</option>
                                <option>Year to date</option>
                            </select>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" id="performanceChart">
                                <!-- Chart.js or Google Analytics integration required -->
                                <canvas id="salesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                        break; // End default case
                } // End switch statement
                ?>
            </div>
        </main>
    </div>
    
    <!-- Quick Add Modal -->
    <div class="modal" id="quickAddModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Quick Add</h2>
                <button class="modal-close" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="quick-add-options">
                    <button class="quick-add-option" 
                            data-bs-toggle="modal" 
                            data-bs-target="#listingFormModal"
                            data-listing-id="0"
                            data-bs-dismiss="modal">
                        <svg width="48" height="48" viewBox="0 0 48 48" fill="currentColor">
                            <path d="M8 8h14v14H8V8zm18 0h14v14H26V8zM8 26h14v14H8V26zm18 0h14v14H26V26z"/>
                        </svg>
                        <span>New Listing</span>
                    </button>
                    <button class="quick-add-option" data-type="lead">
                        <svg width="48" height="48" viewBox="0 0 48 48" fill="currentColor">
                            <path d="M24 12a6 6 0 100 12 6 6 0 000-12zm0 24c-8.84 0-16 3.58-16 8h32c0-4.42-7.16-8-16-8z"/>
                        </svg>
                        <span>New Lead</span>
                    </button>
                    <button class="quick-add-option" data-type="open-house">
                        <svg width="48" height="48" viewBox="0 0 48 48" fill="currentColor">
                            <path d="M38 6h-2V2h-4v4H16V2h-4v4h-2c-2.21 0-3.98 1.79-3.98 4L6 38c0 2.21 1.79 4 4 4h28c2.21 0 4-1.79 4-4V10c0-2.21-1.79-4-4-4zm0 32H10V16h28v22zM14 20h10v10H14V20z"/>
                        </svg>
                        <span>Schedule Open House</span>
                    </button>
                    <button class="quick-add-option" data-type="transaction">
                        <svg width="48" height="48" viewBox="0 0 48 48" fill="currentColor">
                            <path d="M4 10v6h6l9.98-9.98c.39-.39.9-.59 1.41-.59.51 0 1.02.2 1.41.59l5.05 5.05c.79.78.79 2.05 0 2.83L44 30.05v-8.05c0-1.1-.9-2-2-2H16l-4-4H4zm38 12.1L30.1 10.2 12 28.3V38h9.7L42 17.7v4.4zM19.72 31H17v-2.72l9.3-9.3 2.72 2.72L19.72 31z"/>
                        </svg>
                        <span>New Transaction</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <?php
    // Include listing form modal and components
    include get_template_directory() . '/templates/dashboard/listing-form-modal.php';
    include get_template_directory() . '/templates/dashboard/listing-form-steps.php';
    include get_template_directory() . '/inc/listing-form-handler.php';
    ?>

    <!-- All assets and JavaScript are now handled by HP_Dashboard_Assets class -->
    
    <?php wp_footer(); ?>
</body>
</html>