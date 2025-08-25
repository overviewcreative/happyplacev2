<?php
/**
 * Dashboard Layout Component - Flexible layout container for dashboard content
 *
 * @package HappyPlaceTheme
 */

// Default attributes
$layout_args = wp_parse_args($args ?? [], [
    'title' => '',
    'subtitle' => '',
    'breadcrumbs' => [],
    'actions' => [],
    'layout' => 'full', // 'full', 'sidebar', 'grid'
    'sidebar_position' => 'right', // 'left', 'right'
    'grid_columns' => 2,
    'header' => true,
    'container_class' => '',
    'content_class' => '',
    'sidebar_class' => ''
]);

$layout_class = 'hph-dashboard-layout hph-dashboard-layout-' . $layout_args['layout'];
if ($layout_args['container_class']) {
    $layout_class .= ' ' . $layout_args['container_class'];
}

// Grid column classes
$grid_classes = [
    1 => 'hph-grid-cols-1',
    2 => 'hph-grid-cols-1 md:hph-grid-cols-2',
    3 => 'hph-grid-cols-1 md:hph-grid-cols-2 lg:hph-grid-cols-3',
    4 => 'hph-grid-cols-1 md:hph-grid-cols-2 lg:hph-grid-cols-4'
];
$grid_class = $grid_classes[$layout_args['grid_columns']] ?? $grid_classes[2];
?>

<div class="<?php echo esc_attr($layout_class); ?>">
    
    <?php if ($layout_args['header']) : ?>
        <!-- Dashboard Header -->
        <div class="hph-dashboard-header hph-mb-6">
            <?php if (!empty($layout_args['breadcrumbs'])) : ?>
                <nav class="hph-breadcrumbs hph-text-sm hph-text-muted hph-mb-2">
                    <?php foreach ($layout_args['breadcrumbs'] as $index => $crumb) : ?>
                        <?php if ($index > 0) : ?>
                            <span class="hph-breadcrumb-separator hph-mx-2">/</span>
                        <?php endif; ?>
                        
                        <?php if (isset($crumb['url']) && $crumb['url']) : ?>
                            <a href="<?php echo esc_url($crumb['url']); ?>" class="hph-breadcrumb-link hph-hover-underline">
                                <?php echo esc_html($crumb['label']); ?>
                            </a>
                        <?php else : ?>
                            <span class="hph-breadcrumb-current"><?php echo esc_html($crumb['label']); ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </nav>
            <?php endif; ?>
            
            <div class="hph-dashboard-title-row hph-flex hph-flex-col md:hph-flex-row md:hph-items-center hph-justify-between hph-gap-4">
                <div class="hph-dashboard-title-section">
                    <?php if ($layout_args['title']) : ?>
                        <h1 class="hph-dashboard-title hph-text-2xl hph-font-bold hph-mb-1">
                            <?php echo esc_html($layout_args['title']); ?>
                        </h1>
                    <?php endif; ?>
                    
                    <?php if ($layout_args['subtitle']) : ?>
                        <p class="hph-dashboard-subtitle hph-text-muted hph-mb-0">
                            <?php echo esc_html($layout_args['subtitle']); ?>
                        </p>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($layout_args['actions'])) : ?>
                    <div class="hph-dashboard-actions hph-flex hph-gap-2">
                        <?php foreach ($layout_args['actions'] as $action) : ?>
                            <?php
                            $action_defaults = [
                                'text' => '',
                                'url' => '#',
                                'type' => 'button', // 'button', 'link'
                                'color' => 'primary',
                                'size' => 'default',
                                'icon' => '',
                                'onclick' => '',
                                'class' => ''
                            ];
                            $action = wp_parse_args($action, $action_defaults);
                            
                            $btn_class = 'hph-btn hph-btn-' . $action['color'];
                            if ($action['size'] !== 'default') {
                                $btn_class .= ' hph-btn-' . $action['size'];
                            }
                            if ($action['class']) {
                                $btn_class .= ' ' . $action['class'];
                            }
                            ?>
                            
                            <?php if ($action['type'] === 'link') : ?>
                                <a href="<?php echo esc_url($action['url']); ?>" class="<?php echo esc_attr($btn_class); ?>">
                                    <?php if ($action['icon']) : ?>
                                        <i class="fas <?php echo esc_attr($action['icon']); ?> hph-mr-1"></i>
                                    <?php endif; ?>
                                    <?php echo esc_html($action['text']); ?>
                                </a>
                            <?php else : ?>
                                <button type="button" class="<?php echo esc_attr($btn_class); ?>"
                                        <?php if ($action['onclick']) : ?>onclick="<?php echo esc_attr($action['onclick']); ?>"<?php endif; ?>>
                                    <?php if ($action['icon']) : ?>
                                        <i class="fas <?php echo esc_attr($action['icon']); ?> hph-mr-1"></i>
                                    <?php endif; ?>
                                    <?php echo esc_html($action['text']); ?>
                                </button>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Dashboard Content -->
    <div class="hph-dashboard-content">
        
        <?php if ($layout_args['layout'] === 'sidebar') : ?>
            <!-- Sidebar Layout -->
            <div class="hph-dashboard-sidebar-layout hph-grid hph-grid-cols-1 lg:hph-grid-cols-4 hph-gap-6">
                <?php if ($layout_args['sidebar_position'] === 'left') : ?>
                    <!-- Left Sidebar -->
                    <div class="hph-dashboard-sidebar hph-sidebar-left <?php echo esc_attr($layout_args['sidebar_class']); ?>">
                        <div class="hph-sidebar-content">
                            <?php if (isset($content_sidebar)) : ?>
                                <?php echo $content_sidebar; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Main Content -->
                    <div class="hph-dashboard-main hph-col-span-3 <?php echo esc_attr($layout_args['content_class']); ?>">
                        <?php if (isset($content_main)) : ?>
                            <?php echo $content_main; ?>
                        <?php endif; ?>
                    </div>
                <?php else : ?>
                    <!-- Main Content -->
                    <div class="hph-dashboard-main hph-col-span-3 <?php echo esc_attr($layout_args['content_class']); ?>">
                        <?php if (isset($content_main)) : ?>
                            <?php echo $content_main; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Right Sidebar -->
                    <div class="hph-dashboard-sidebar hph-sidebar-right <?php echo esc_attr($layout_args['sidebar_class']); ?>">
                        <div class="hph-sidebar-content">
                            <?php if (isset($content_sidebar)) : ?>
                                <?php echo $content_sidebar; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
        <?php elseif ($layout_args['layout'] === 'grid') : ?>
            <!-- Grid Layout -->
            <div class="hph-dashboard-grid hph-grid <?php echo esc_attr($grid_class); ?> hph-gap-6">
                <?php if (isset($content_main)) : ?>
                    <?php echo $content_main; ?>
                <?php endif; ?>
            </div>
            
        <?php else : ?>
            <!-- Full Width Layout -->
            <div class="hph-dashboard-full <?php echo esc_attr($layout_args['content_class']); ?>">
                <?php if (isset($content_main)) : ?>
                    <?php echo $content_main; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<?php
/**
 * Usage Examples:
 * 
 * Basic usage:
 * get_template_part('template-parts/components/dashboard-layout', '', [
 *     'title' => 'My Dashboard',
 *     'subtitle' => 'Welcome back!'
 * ]);
 * 
 * With breadcrumbs and actions:
 * get_template_part('template-parts/components/dashboard-layout', '', [
 *     'title' => 'Agent Dashboard',
 *     'breadcrumbs' => [
 *         ['label' => 'Home', 'url' => '/'],
 *         ['label' => 'Dashboard']
 *     ],
 *     'actions' => [
 *         [
 *             'text' => 'New Listing',
 *             'url' => '/new-listing',
 *             'type' => 'link',
 *             'color' => 'primary',
 *             'icon' => 'fa-plus'
 *         ]
 *     ]
 * ]);
 * 
 * Grid layout:
 * ob_start();
 * // Your grid content here
 * $content_main = ob_get_clean();
 * 
 * get_template_part('template-parts/components/dashboard-layout', '', [
 *     'title' => 'Statistics',
 *     'layout' => 'grid',
 *     'grid_columns' => 3,
 *     'content_main' => $content_main
 * ]);
 */
?>