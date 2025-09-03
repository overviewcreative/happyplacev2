<?php
/**
 * Dashboard Header Component
 * 
 * @package HappyPlaceTheme
 */

$user = $args['user'] ?? wp_get_current_user();
$is_agent = $args['is_agent'] ?? false;
$current_section = $_GET['section'] ?? 'overview';

// Define section titles
$section_titles = [
    'overview' => __('Dashboard Overview', 'happy-place-theme'),
    'listings' => $is_agent ? __('My Listings', 'happy-place-theme') : __('Saved Listings', 'happy-place-theme'),
    'leads' => __('Lead Management', 'happy-place-theme'),
    'analytics' => __('Analytics & Reports', 'happy-place-theme'),
    'favorites' => __('Favorite Listings', 'happy-place-theme'),
    'searches' => __('Saved Searches', 'happy-place-theme'),
    'profile' => __('Profile Settings', 'happy-place-theme')
];

$page_title = $section_titles[$current_section] ?? __('Dashboard', 'happy-place-theme');
?>

<header class="hph-dashboard-header">
    
    <!-- Header Top -->
    <div class="hph-header-top">
        <div class="hph-header-left">
            <h1 class="hph-page-title"><?php echo esc_html($page_title); ?></h1>
            <nav class="hph-breadcrumb" aria-label="<?php _e('Breadcrumb', 'happy-place-theme'); ?>">
                <ol class="hph-breadcrumb-list">
                    <li class="hph-breadcrumb-item">
                        <a href="?section=overview" class="hph-breadcrumb-link"><?php _e('Dashboard', 'happy-place-theme'); ?></a>
                    </li>
                    <?php if ($current_section !== 'overview'): ?>
                        <li class="hph-breadcrumb-separator">/</li>
                        <li class="hph-breadcrumb-item hph-breadcrumb-current" aria-current="page">
                            <?php echo esc_html($page_title); ?>
                        </li>
                    <?php endif; ?>
                </ol>
            </nav>
        </div>
        
        <div class="hph-header-right">
            
            <!-- Search Bar -->
            <div class="hph-header-search">
                <form class="hph-search-form" role="search">
                    <div class="hph-search-input-group">
                        <input type="search" 
                               class="hph-search-input" 
                               placeholder="<?php _e('Search properties, leads, etc...', 'happy-place-theme'); ?>"
                               aria-label="<?php _e('Search dashboard content', 'happy-place-theme'); ?>">
                        <button type="submit" class="hph-search-btn" aria-label="<?php _e('Search', 'happy-place-theme'); ?>">
                            <span class="hph-icon-search"></span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Header Actions -->
            <div class="hph-header-actions">
                
                <!-- Notifications -->
                <div class="hph-header-action hph-notifications" id="notificationsDropdown">
                    <button class="hph-action-btn" aria-label="<?php _e('Notifications', 'happy-place-theme'); ?>">
                        <span class="hph-icon-bell"></span>
                        <span class="hph-notification-badge" id="notificationCount">3</span>
                    </button>
                    <div class="hph-dropdown-menu hph-notifications-menu">
                        <div class="hph-dropdown-header">
                            <h3 class="hph-dropdown-title"><?php _e('Notifications', 'happy-place-theme'); ?></h3>
                            <a href="#" class="hph-mark-read-all"><?php _e('Mark all read', 'happy-place-theme'); ?></a>
                        </div>
                        <div class="hph-notifications-list" id="notificationsList">
                            <!-- Notifications loaded via AJAX -->
                        </div>
                        <div class="hph-dropdown-footer">
                            <a href="#" class="hph-view-all-link"><?php _e('View all notifications', 'happy-place-theme'); ?></a>
                        </div>
                    </div>
                </div>

                <!-- User Profile Dropdown -->
                <div class="hph-header-action hph-user-menu" id="userMenuDropdown">
                    <button class="hph-user-btn" aria-label="<?php _e('User menu', 'happy-place-theme'); ?>">
                        <div class="hph-user-avatar">
                            <?php echo get_avatar($user->ID, 36, '', '', ['class' => 'hph-avatar-small']); ?>
                        </div>
                        <div class="hph-user-info">
                            <span class="hph-user-name"><?php echo esc_html($user->display_name); ?></span>
                            <span class="hph-user-role">
                                <?php echo $is_agent ? __('Agent', 'happy-place-theme') : __('Member', 'happy-place-theme'); ?>
                            </span>
                        </div>
                        <span class="hph-dropdown-arrow hph-icon-chevron-down"></span>
                    </button>
                    
                    <div class="hph-dropdown-menu hph-user-dropdown">
                        <div class="hph-dropdown-header">
                            <div class="hph-user-profile-summary">
                                <?php echo get_avatar($user->ID, 48, '', '', ['class' => 'hph-avatar-medium']); ?>
                                <div class="hph-user-details">
                                    <h4 class="hph-user-display-name"><?php echo esc_html($user->display_name); ?></h4>
                                    <p class="hph-user-email"><?php echo esc_html($user->user_email); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <nav class="hph-dropdown-nav">
                            <a href="?section=profile" class="hph-dropdown-link">
                                <span class="hph-link-icon hph-icon-user"></span>
                                <?php _e('Edit Profile', 'happy-place-theme'); ?>
                            </a>
                            <a href="#" class="hph-dropdown-link">
                                <span class="hph-link-icon hph-icon-settings"></span>
                                <?php _e('Account Settings', 'happy-place-theme'); ?>
                            </a>
                            <?php if ($is_agent): ?>
                                <a href="#" class="hph-dropdown-link">
                                    <span class="hph-link-icon hph-icon-chart"></span>
                                    <?php _e('Performance', 'happy-place-theme'); ?>
                                </a>
                            <?php endif; ?>
                        </nav>
                        
                        <div class="hph-dropdown-footer">
                            <a href="<?php echo home_url(); ?>" class="hph-dropdown-link">
                                <span class="hph-link-icon hph-icon-arrow-left"></span>
                                <?php _e('Back to Website', 'happy-place-theme'); ?>
                            </a>
                            <a href="<?php echo wp_logout_url(home_url()); ?>" class="hph-dropdown-link hph-logout-link">
                                <span class="hph-link-icon hph-icon-logout"></span>
                                <?php _e('Logout', 'happy-place-theme'); ?>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Header Bottom (Context Actions) -->
    <?php if ($is_agent && in_array($current_section, ['listings', 'leads'])): ?>
        <div class="hph-header-bottom">
            <div class="hph-context-actions">
                
                <?php if ($current_section === 'listings'): ?>
                    <div class="hph-action-group">
                        <button class="hph-btn hph-btn-primary" id="addListingBtn">
                            <span class="hph-btn-icon hph-icon-plus"></span>
                            <span class="hph-btn-text"><?php _e('Add New Listing', 'happy-place-theme'); ?></span>
                        </button>
                        <button class="hph-btn hph-btn-secondary" id="importListingsBtn">
                            <span class="hph-btn-icon hph-icon-upload"></span>
                            <span class="hph-btn-text"><?php _e('Import Listings', 'happy-place-theme'); ?></span>
                        </button>
                    </div>
                    
                    <div class="hph-filter-group">
                        <select class="hph-select" id="listingStatusFilter">
                            <option value="all"><?php _e('All Status', 'happy-place-theme'); ?></option>
                            <option value="active"><?php _e('Active', 'happy-place-theme'); ?></option>
                            <option value="pending"><?php _e('Pending', 'happy-place-theme'); ?></option>
                            <option value="sold"><?php _e('Sold', 'happy-place-theme'); ?></option>
                            <option value="draft"><?php _e('Draft', 'happy-place-theme'); ?></option>
                        </select>
                        <button class="hph-btn hph-btn-ghost" id="viewToggleBtn" title="<?php _e('Toggle view', 'happy-place-theme'); ?>">
                            <span class="hph-icon-grid"></span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if ($current_section === 'leads'): ?>
                    <div class="hph-action-group">
                        <button class="hph-btn hph-btn-primary" id="addLeadBtn">
                            <span class="hph-btn-icon hph-icon-user-plus"></span>
                            <span class="hph-btn-text"><?php _e('Add Lead', 'happy-place-theme'); ?></span>
                        </button>
                    </div>
                    
                    <div class="hph-filter-group">
                        <select class="hph-select" id="leadStatusFilter">
                            <option value="all"><?php _e('All Leads', 'happy-place-theme'); ?></option>
                            <option value="hot"><?php _e('Hot Leads', 'happy-place-theme'); ?></option>
                            <option value="warm"><?php _e('Warm Leads', 'happy-place-theme'); ?></option>
                            <option value="cold"><?php _e('Cold Leads', 'happy-place-theme'); ?></option>
                            <option value="converted"><?php _e('Converted', 'happy-place-theme'); ?></option>
                        </select>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    <?php endif; ?>

</header>