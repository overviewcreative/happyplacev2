<?php
/**
 * Dashboard Navigation Component
 * 
 * @package HappyPlaceTheme
 */

$is_agent = $args['is_agent'] ?? false;
$current_section = $_GET['section'] ?? 'overview';
?>

<aside class="hph-dashboard-sidebar" id="dashboardSidebar">
    
    <!-- Sidebar Header -->
    <div class="hph-sidebar-header">
        <div class="hph-sidebar-logo">
            <?php if (has_custom_logo()): ?>
                <?php the_custom_logo(); ?>
            <?php else: ?>
                <h2 class="hph-sidebar-title"><?php bloginfo('name'); ?></h2>
            <?php endif; ?>
        </div>
        
        <!-- Mobile Close Button -->
        <button class="hph-sidebar-close" id="sidebarClose" aria-label="<?php _e('Close Menu', 'happy-place-theme'); ?>">
            <span class="hph-icon-close">×</span>
        </button>
    </div>

    <!-- User Profile Summary -->
    <div class="hph-sidebar-profile">
        <div class="hph-profile-avatar">
            <?php echo get_avatar(get_current_user_id(), 48, '', '', ['class' => 'hph-avatar-image']); ?>
        </div>
        <div class="hph-profile-info">
            <h3 class="hph-profile-name"><?php echo esc_html(wp_get_current_user()->display_name); ?></h3>
            <p class="hph-profile-role">
                <?php if ($is_agent): ?>
                    <?php _e('Real Estate Agent', 'happy-place-theme'); ?>
                <?php else: ?>
                    <?php _e('Member', 'happy-place-theme'); ?>
                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="hph-sidebar-nav" role="navigation">
        <ul class="hph-nav-list">
            
            <!-- Overview -->
            <li class="hph-nav-item">
                <a href="?section=overview" class="hph-nav-link <?php echo $current_section === 'overview' ? 'active' : ''; ?>">
                    <span class="hph-nav-icon hph-icon-dashboard"></span>
                    <span class="hph-nav-text"><?php _e('Overview', 'happy-place-theme'); ?></span>
                </a>
            </li>

            <!-- Listings Management (Agents) / Saved Listings (Users) -->
            <li class="hph-nav-item">
                <a href="?section=listings" class="hph-nav-link <?php echo $current_section === 'listings' ? 'active' : ''; ?>">
                    <span class="hph-nav-icon hph-icon-home"></span>
                    <span class="hph-nav-text">
                        <?php echo $is_agent ? __('My Listings', 'happy-place-theme') : __('Saved Properties', 'happy-place-theme'); ?>
                    </span>
                    <?php if ($is_agent): ?>
                        <span class="hph-nav-count" id="listingsCount">-</span>
                    <?php endif; ?>
                </a>
            </li>

            <?php if ($is_agent): ?>
                <!-- Leads Management (Agents Only) -->
                <li class="hph-nav-item">
                    <a href="?section=leads" class="hph-nav-link <?php echo $current_section === 'leads' ? 'active' : ''; ?>">
                        <span class="hph-nav-icon hph-icon-users"></span>
                        <span class="hph-nav-text"><?php _e('Leads', 'happy-place-theme'); ?></span>
                        <span class="hph-nav-count hph-nav-count-hot" id="hotLeadsCount">-</span>
                    </a>
                </li>

                <!-- Analytics (Agents Only) -->
                <li class="hph-nav-item">
                    <a href="?section=analytics" class="hph-nav-link <?php echo $current_section === 'analytics' ? 'active' : ''; ?>">
                        <span class="hph-nav-icon hph-icon-chart"></span>
                        <span class="hph-nav-text"><?php _e('Analytics', 'happy-place-theme'); ?></span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Favorites (All Users) -->
            <li class="hph-nav-item">
                <a href="?section=favorites" class="hph-nav-link <?php echo $current_section === 'favorites' ? 'active' : ''; ?>">
                    <span class="hph-nav-icon hph-icon-heart"></span>
                    <span class="hph-nav-text"><?php _e('Favorites', 'happy-place-theme'); ?></span>
                    <span class="hph-nav-count" id="favoritesCount">-</span>
                </a>
            </li>

            <!-- Saved Searches -->
            <li class="hph-nav-item">
                <a href="?section=searches" class="hph-nav-link <?php echo $current_section === 'searches' ? 'active' : ''; ?>">
                    <span class="hph-nav-icon hph-icon-search"></span>
                    <span class="hph-nav-text"><?php _e('Saved Searches', 'happy-place-theme'); ?></span>
                    <span class="hph-nav-count" id="searchesCount">-</span>
                </a>
            </li>

            <!-- Profile Settings -->
            <li class="hph-nav-item">
                <a href="?section=profile" class="hph-nav-link <?php echo $current_section === 'profile' ? 'active' : ''; ?>">
                    <span class="hph-nav-icon hph-icon-settings"></span>
                    <span class="hph-nav-text"><?php _e('Profile', 'happy-place-theme'); ?></span>
                </a>
            </li>

        </ul>

        <?php if ($is_agent): ?>
            <!-- Quick Actions -->
            <div class="hph-sidebar-actions">
                <h4 class="hph-sidebar-section-title"><?php _e('Quick Actions', 'happy-place-theme'); ?></h4>
                <div class="hph-quick-actions-list">
                    <button class="hph-quick-action-btn hph-btn-primary" id="quickAddBtn">
                        <span class="hph-btn-icon hph-icon-plus"></span>
                        <span class="hph-btn-text"><?php _e('Add Listing', 'happy-place-theme'); ?></span>
                    </button>
                </div>
            </div>
        <?php endif; ?>

    </nav>

    <!-- Sidebar Footer -->
    <div class="hph-sidebar-footer">
        <a href="<?php echo home_url(); ?>" class="hph-sidebar-link">
            <span class="hph-nav-icon hph-icon-arrow-left"></span>
            <span class="hph-nav-text"><?php _e('Back to Website', 'happy-place-theme'); ?></span>
        </a>
        <a href="<?php echo wp_logout_url(home_url()); ?>" class="hph-sidebar-link hph-sidebar-logout">
            <span class="hph-nav-icon hph-icon-logout"></span>
            <span class="hph-nav-text"><?php _e('Logout', 'happy-place-theme'); ?></span>
        </a>
    </div>

</aside>