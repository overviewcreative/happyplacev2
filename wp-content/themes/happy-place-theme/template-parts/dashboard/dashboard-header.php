<?php
/**
 * Dashboard Sidebar Navigation - Modern Design
 * 
 * @package HappyPlaceTheme
 */

$is_agent = $args['is_agent'] ?? false;
$is_admin = $args['is_admin'] ?? false;
$user_role = $args['user_role'] ?? 'user';
$current_section = $_GET['section'] ?? 'overview';
$user = wp_get_current_user();

// Navigation items with role-based filtering
$nav_items = [
    'overview' => [
        'label' => 'Overview',
        'icon' => 'chart-line',
        'badge' => null,
        'roles' => ['all']
    ],
    'listings' => [
        'label' => $is_agent ? 'My Listings' : 'Saved Properties',
        'icon' => 'home',
        'badge' => $is_agent ? '12' : '5',
        'badge_type' => 'default',
        'roles' => ['all']
    ],
    'open-houses' => [
        'label' => 'Open Houses',
        'icon' => 'calendar-check',
        'badge' => '3',
        'badge_type' => 'primary',
        'roles' => ['agent']
    ],
    'marketing' => [
        'label' => 'Marketing',
        'icon' => 'bullhorn',
        'badge' => null,
        'roles' => ['agent']
    ],
    'leads' => [
        'label' => 'Leads',
        'icon' => 'users',
        'badge' => '7',
        'badge_type' => 'danger',
        'roles' => ['agent']
    ],
    'analytics' => [
        'label' => 'Analytics',
        'icon' => 'chart-bar',
        'badge' => null,
        'roles' => ['agent']
    ],
    'favorites' => [
        'label' => 'Favorites',
        'icon' => 'heart',
        'badge' => '8',
        'badge_type' => 'default',
        'roles' => ['all']
    ],
    'searches' => [
        'label' => 'Saved Searches',
        'icon' => 'bookmark',
        'badge' => '4',
        'badge_type' => 'default',
        'roles' => ['all']
    ],
    'profile' => [
        'label' => 'Profile',
        'icon' => 'user-cog',
        'badge' => null,
        'roles' => ['all']
    ]
];
?>

<aside class="dashboard-sidebar" id="header-dashboardSidebar">
    <!-- Sidebar Brand -->
    <div class="sidebar-brand">
        <a href="<?php echo home_url(); ?>" class="hph-flex hph-items-center hph-gap-3">
            <?php if (has_custom_logo()): ?>
                <?php 
                $custom_logo_id = get_theme_mod('custom_logo');
                $logo = wp_get_attachment_image_src($custom_logo_id, 'full');
                ?>
                <img src="<?php echo esc_url($logo[0]); ?>" alt="<?php bloginfo('name'); ?>" class="hph-h-8">
            <?php else: ?>
                <span class="hph-text-xl hph-font-bold hph-text-primary">
                    <?php bloginfo('name'); ?>
                </span>
            <?php endif; ?>
        </a>
        
        <!-- Sidebar Toggle (Desktop) -->
        <button class="hph-ml-auto hph-hidden lg:hph-block hph-text-gray-500 hover:hph-text-gray-700" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    
    <!-- User Profile Summary -->
    <div class="hph-p-6 hph-border-b hph-border-gray-100">
        <div class="hph-flex hph-items-center hph-gap-3">
            <?php echo get_avatar($user->ID, 48, '', '', ['class' => 'hph-rounded-full hph-border-2 hph-border-primary-100']); ?>
            <div class="sidebar-profile-info">
                <div class="hph-font-medium hph-text-gray-900">
                    <?php echo esc_html($user->display_name); ?>
                </div>
                <div class="hph-text-xs hph-text-gray-600">
                    <?php 
                    if ($is_admin) {
                        echo 'Administrator';
                    } elseif ($is_agent) {
                        echo 'Real Estate Agent';
                    } else {
                        echo 'Member';
                    }
                    ?>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <?php if ($is_agent || $is_admin): ?>
        <div class="hph-grid hph-grid-cols-3 hph-gap-2 hph-mt-4">
            <div class="hph-text-center hph-p-2 hph-bg-gray-50 hph-rounded-lg">
                <div class="hph-text-lg hph-font-semibold hph-text-primary">12</div>
                <div class="hph-text-xs hph-text-gray-600">Active</div>
            </div>
            <div class="hph-text-center hph-p-2 hph-bg-gray-50 hph-rounded-lg">
                <div class="hph-text-lg hph-font-semibold hph-text-success">5</div>
                <div class="hph-text-xs hph-text-gray-600">Sold</div>
            </div>
            <div class="hph-text-center hph-p-2 hph-bg-gray-50 hph-rounded-lg">
                <div class="hph-text-lg hph-font-semibold hph-text-warning">7</div>
                <div class="hph-text-xs hph-text-gray-600">Leads</div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Navigation -->
    <nav class="sidebar-nav">
        <ul class="hph-space-y-1">
            <?php foreach ($nav_items as $key => $item): ?>
                <?php 
                // Check role permissions (admins see everything)
                if ($item['roles'] !== ['all'] && !$is_admin && !$is_agent && in_array('agent', $item['roles'])) {
                    continue;
                }
                
                $is_active = ($current_section === $key);
                ?>
                <li class="sidebar-nav-item">
                    <a href="?section=<?php echo esc_attr($key); ?>" 
                       class="sidebar-nav-link <?php echo $is_active ? 'active' : ''; ?>">
                        <i class="fas fa-<?php echo esc_attr($item['icon']); ?> sidebar-nav-icon"></i>
                        <span class="sidebar-nav-text"><?php echo esc_html($item['label']); ?></span>
                        <?php if ($item['badge']): ?>
                            <span class="sidebar-nav-badge <?php echo esc_attr($item['badge_type'] ?? ''); ?>">
                                <?php echo esc_html($item['badge']); ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    
    <?php if ($is_agent || $is_admin): ?>
    <!-- Quick Actions -->
    <div class="hph-p-6 hph-border-t hph-border-gray-100">
        <h4 class="hph-text-xs hph-font-semibold hph-text-gray-500 hph-uppercase hph-tracking-wider hph-mb-3">
            Quick Actions
        </h4>
        <div class="hph-space-y-2">
            <button id="header-quickAddListingBtn" class="hph-btn hph-btn-primary hph-btn-sm hph-w-full">
                <i class="fas fa-plus hph-mr-2"></i>
                Add Listing
            </button>
            <button class="hph-btn hph-btn-outline hph-btn-sm hph-w-full">
                <i class="fas fa-user-plus hph-mr-2"></i>
                Add Lead
            </button>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Sidebar Footer -->
    <div class="hph-mt-auto hph-p-6 hph-border-t hph-border-gray-100">
        <a href="<?php echo home_url(); ?>" class="hph-flex hph-items-center hph-gap-3 hph-text-sm hph-text-gray-600 hover:hph-text-gray-900 hph-transition hph-mb-3">
            <i class="fas fa-arrow-left"></i>
            <span class="sidebar-nav-text">Back to Website</span>
        </a>
        <a href="<?php echo wp_logout_url(home_url()); ?>" class="hph-flex hph-items-center hph-gap-3 hph-text-sm hph-text-danger hover:hph-text-danger-dark hph-transition">
            <i class="fas fa-sign-out-alt"></i>
            <span class="sidebar-nav-text">Logout</span>
        </a>
    </div>
</aside>

