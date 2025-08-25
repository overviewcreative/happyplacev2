<?php
/**
 * Dashboard Widget Component - Reusable widget container for dashboard elements
 *
 * @package HappyPlaceTheme
 */

// Default attributes
$widget_args = wp_parse_args($args ?? [], [
    'title' => '',
    'subtitle' => '',
    'icon' => '',
    'color' => 'default',
    'size' => 'medium',
    'actions' => [],
    'collapsible' => false,
    'collapsed' => false,
    'loading' => false,
    'error' => false,
    'error_message' => 'Unable to load widget data',
    'refresh' => false,
    'widget_id' => '',
    'header' => true,
    'padding' => true,
    'content_class' => '',
    'widget_class' => ''
]);

// Generate unique widget ID if not provided
if (empty($widget_args['widget_id'])) {
    $widget_args['widget_id'] = 'hph-widget-' . uniqid();
}

// Build widget classes using utility-first approach
$widget_classes = [
    'hph-dashboard-widget',
    'hph-bg-white',
    'hph-rounded-lg',
    'hph-shadow-sm',
    'hph-border',
    'hph-border-gray-200',
    'hph-transition-shadow',
    'hph-duration-200'
];

// Size variations
$size_classes = [
    'small' => ['hph-p-sm'],
    'medium' => ['hph-p-md'],
    'large' => ['hph-p-lg']
];

$widget_classes = array_merge($widget_classes, $size_classes[$widget_args['size']] ?? $size_classes['medium']);

// Color variations
if ($widget_args['color'] !== 'default') {
    $widget_classes[] = 'hph-widget-' . $widget_args['color'];
}
if ($widget_args['widget_class']) {
    $widget_classes[] = $widget_args['widget_class'];
}

// Content classes
$content_class = 'hph-widget-content';
if ($widget_args['padding']) {
    $content_class .= ' hph-p-4';
}
if ($widget_args['content_class']) {
    $content_class .= ' ' . $widget_args['content_class'];
}

$collapsed_class = $widget_args['collapsed'] ? 'hph-collapsed' : '';
?>

<div id="<?php echo esc_attr($widget_args['widget_id']); ?>" 
     class="<?php echo esc_attr(implode(' ', array_merge($widget_classes, [$collapsed_class]))); ?>" 
     data-widget-id="<?php echo esc_attr($widget_args['widget_id']); ?>">
    
    <?php if ($widget_args['header'] && ($widget_args['title'] || $widget_args['icon'] || !empty($widget_args['actions']))) : ?>
        <!-- Widget Header -->
        <div class="hph-widget-header hph-flex hph-items-center hph-justify-between hph-p-4 hph-border-b">
            <div class="hph-widget-title-section hph-flex hph-items-center hph-gap-3">
                <?php if ($widget_args['icon']) : ?>
                    <div class="hph-widget-icon hph-w-8 hph-h-8 hph-rounded hph-bg-<?php echo esc_attr($widget_args['color']); ?> hph-bg-opacity-10 hph-flex hph-items-center hph-justify-center">
                        <i class="fas <?php echo esc_attr($widget_args['icon']); ?> hph-text-<?php echo esc_attr($widget_args['color']); ?> hph-text-sm"></i>
                    </div>
                <?php endif; ?>
                
                <div class="hph-widget-title-text">
                    <?php if ($widget_args['title']) : ?>
                        <h3 class="hph-widget-title hph-font-medium hph-mb-0">
                            <?php echo esc_html($widget_args['title']); ?>
                        </h3>
                    <?php endif; ?>
                    
                    <?php if ($widget_args['subtitle']) : ?>
                        <p class="hph-widget-subtitle hph-text-sm hph-text-muted hph-mb-0">
                            <?php echo esc_html($widget_args['subtitle']); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="hph-widget-actions hph-flex hph-items-center hph-gap-2">
                <?php if ($widget_args['refresh']) : ?>
                    <button type="button" class="hph-widget-refresh hph-btn hph-btn-ghost hph-btn-xs"
                            data-widget-id="<?php echo esc_attr($widget_args['widget_id']); ?>"
                            title="Refresh widget">
                        <i class="fas fa-sync"></i>
                    </button>
                <?php endif; ?>
                
                <?php if (!empty($widget_args['actions'])) : ?>
                    <?php foreach ($widget_args['actions'] as $action) : ?>
                        <?php
                        $action_defaults = [
                            'text' => '',
                            'icon' => '',
                            'url' => '#',
                            'type' => 'button',
                            'color' => 'ghost',
                            'size' => 'xs',
                            'onclick' => '',
                            'title' => ''
                        ];
                        $action = wp_parse_args($action, $action_defaults);
                        
                        $btn_class = 'hph-btn hph-btn-' . $action['color'] . ' hph-btn-' . $action['size'];
                        ?>
                        
                        <?php if ($action['type'] === 'link') : ?>
                            <a href="<?php echo esc_url($action['url']); ?>" 
                               class="<?php echo esc_attr($btn_class); ?>"
                               <?php if ($action['title']) : ?>title="<?php echo esc_attr($action['title']); ?>"<?php endif; ?>>
                                <?php if ($action['icon']) : ?>
                                    <i class="fas <?php echo esc_attr($action['icon']); ?>"></i>
                                <?php endif; ?>
                                <?php if ($action['text']) : ?>
                                    <?php echo esc_html($action['text']); ?>
                                <?php endif; ?>
                            </a>
                        <?php else : ?>
                            <button type="button" class="<?php echo esc_attr($btn_class); ?>"
                                    <?php if ($action['onclick']) : ?>onclick="<?php echo esc_attr($action['onclick']); ?>"<?php endif; ?>
                                    <?php if ($action['title']) : ?>title="<?php echo esc_attr($action['title']); ?>"<?php endif; ?>>
                                <?php if ($action['icon']) : ?>
                                    <i class="fas <?php echo esc_attr($action['icon']); ?>"></i>
                                <?php endif; ?>
                                <?php if ($action['text']) : ?>
                                    <?php echo esc_html($action['text']); ?>
                                <?php endif; ?>
                            </button>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <?php if ($widget_args['collapsible']) : ?>
                    <button type="button" class="hph-widget-toggle hph-btn hph-btn-ghost hph-btn-xs"
                            data-widget-id="<?php echo esc_attr($widget_args['widget_id']); ?>"
                            title="<?php echo $widget_args['collapsed'] ? 'Expand' : 'Collapse'; ?> widget">
                        <i class="fas fa-chevron-<?php echo $widget_args['collapsed'] ? 'down' : 'up'; ?>"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Widget Body -->
    <div class="hph-widget-body <?php echo $widget_args['collapsible'] && $widget_args['collapsed'] ? 'hph-hidden' : ''; ?>">
        <?php if ($widget_args['loading']) : ?>
            <!-- Loading State -->
            <div class="<?php echo esc_attr($content_class); ?> hph-text-center">
                <div class="hph-widget-loading hph-py-8">
                    <div class="hph-spinner hph-spinner-lg hph-text-<?php echo esc_attr($widget_args['color']); ?> hph-mb-2">
                        <i class="fas fa-circle-notch fa-spin"></i>
                    </div>
                    <p class="hph-text-muted hph-text-sm">Loading...</p>
                </div>
            </div>
            
        <?php elseif ($widget_args['error']) : ?>
            <!-- Error State -->
            <div class="<?php echo esc_attr($content_class); ?> hph-text-center">
                <div class="hph-widget-error hph-py-8">
                    <div class="hph-error-icon hph-text-3xl hph-text-danger hph-mb-2">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <p class="hph-text-muted hph-text-sm"><?php echo esc_html($widget_args['error_message']); ?></p>
                    <?php if ($widget_args['refresh']) : ?>
                        <button type="button" class="hph-btn hph-btn-outline hph-btn-sm hph-mt-2 hph-widget-refresh"
                                data-widget-id="<?php echo esc_attr($widget_args['widget_id']); ?>">
                            <i class="fas fa-retry hph-mr-1"></i>
                            Try Again
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            
        <?php else : ?>
            <!-- Widget Content -->
            <div class="<?php echo esc_attr($content_class); ?>">
                <?php if (isset($content) && $content) : ?>
                    <?php echo $content; ?>
                <?php else : ?>
                    <!-- Default content placeholder -->
                    <p class="hph-text-muted hph-text-center hph-py-8">Widget content goes here</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
</div>

<?php if ($widget_args['collapsible'] || $widget_args['refresh']) : ?>
<script>
jQuery(document).ready(function($) {
    var widgetId = '<?php echo esc_js($widget_args['widget_id']); ?>';
    var $widget = $('#' + widgetId);
    
    // Collapsible functionality
    <?php if ($widget_args['collapsible']) : ?>
    $widget.find('.hph-widget-toggle').on('click', function() {
        var $body = $widget.find('.hph-widget-body');
        var $icon = $(this).find('i');
        
        if ($body.hasClass('hph-hidden')) {
            $body.removeClass('hph-hidden');
            $icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
            $widget.removeClass('hph-collapsed');
            $(this).attr('title', 'Collapse widget');
        } else {
            $body.addClass('hph-hidden');
            $icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
            $widget.addClass('hph-collapsed');
            $(this).attr('title', 'Expand widget');
        }
        
        // Save state to localStorage
        var collapsed = $body.hasClass('hph-hidden');
        localStorage.setItem('widget_' + widgetId + '_collapsed', collapsed);
    });
    
    // Restore collapsed state from localStorage
    var savedState = localStorage.getItem('widget_' + widgetId + '_collapsed');
    if (savedState === 'true') {
        $widget.find('.hph-widget-toggle').click();
    }
    <?php endif; ?>
    
    // Refresh functionality
    <?php if ($widget_args['refresh']) : ?>
    $widget.find('.hph-widget-refresh').on('click', function() {
        var $btn = $(this);
        var $icon = $btn.find('i');
        
        // Add loading state
        $icon.addClass('fa-spin');
        $btn.prop('disabled', true);
        
        // Trigger custom refresh event
        $widget.trigger('hph-widget-refresh', [widgetId]);
        
        // Remove loading state after delay (customize based on your needs)
        setTimeout(function() {
            $icon.removeClass('fa-spin');
            $btn.prop('disabled', false);
        }, 1000);
    });
    <?php endif; ?>
});
</script>
<?php endif; ?>

<?php
/**
 * Usage Examples:
 * 
 * Basic widget:
 * ob_start();
 * echo '<p>Widget content here</p>';
 * $content = ob_get_clean();
 * 
 * get_template_part('template-parts/components/dashboard-widget', '', [
 *     'title' => 'My Widget',
 *     'content' => $content
 * ]);
 * 
 * Advanced widget with actions:
 * get_template_part('template-parts/components/dashboard-widget', '', [
 *     'title' => 'Recent Activity',
 *     'subtitle' => 'Last 30 days',
 *     'icon' => 'fa-activity',
 *     'color' => 'primary',
 *     'collapsible' => true,
 *     'refresh' => true,
 *     'actions' => [
 *         [
 *             'icon' => 'fa-external-link-alt',
 *             'url' => '/full-report',
 *             'type' => 'link',
 *             'title' => 'View full report'
 *         ]
 *     ],
 *     'content' => $content
 * ]);
 */
?>