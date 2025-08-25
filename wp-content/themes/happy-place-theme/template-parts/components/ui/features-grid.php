<?php
/**
 * Features Grid Component - Utility-First Implementation
 * 
 * Displays features/services in a responsive grid layout with icons
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 * 
 * Args:
 * - items: array of feature objects with 'title', 'description', 'icon', 'link'
 * - columns: int (2, 3, 4, 6)
 * - card_style: 'default' | 'hover-lift' | 'border' | 'shadow' | 'minimal'
 * - animate: bool
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Parse arguments with defaults following hero.php pattern
$feature_args = wp_parse_args($args ?? [], [
    'items' => [],
    'columns' => 3,
    'card_style' => 'default',
    'animate' => true,
    'stagger_delay' => 100
]);

if (empty($feature_args['items'])) {
    return;
}

// Build grid classes using utility-first approach
$grid_classes = [
    'hph-grid',
    'hph-grid-cols-1',
    'md:hph-grid-cols-2',
    'lg:hph-grid-cols-' . min($feature_args['columns'], 4),
    'hph-gap-lg'
];

// Build container classes
$container_classes = [
    'hph-features-grid',
    'hph-w-full'
];

if ($feature_args['animate']) {
    $container_classes[] = 'hph-animate-fade-in-up';
}

// Card style variations using utility classes
$card_style_classes = [
    'default' => [
        'hph-bg-white',
        'hph-border',
        'hph-border-gray-200',
        'hph-rounded-lg',
        'hph-p-xl',
        'hph-h-full',
        'hph-transition-all',
        'hph-duration-300'
    ],
    'hover-lift' => [
        'hph-bg-white',
        'hph-border',
        'hph-border-gray-200',
        'hph-rounded-lg',
        'hph-p-xl',
        'hph-h-full',
        'hph-transition-all',
        'hph-duration-300',
        'hph-hover:shadow-lg',
        'hph-hover:-translate-y-1'
    ],
    'shadow' => [
        'hph-bg-white',
        'hph-shadow-md',
        'hph-rounded-lg',
        'hph-p-xl',
        'hph-h-full',
        'hph-transition-all',
        'hph-duration-300',
        'hph-hover:shadow-lg'
    ],
    'border' => [
        'hph-bg-white',
        'hph-border-2',
        'hph-border-gray-300',
        'hph-rounded-lg',
        'hph-p-xl',
        'hph-h-full',
        'hph-transition-all',
        'hph-duration-300',
        'hph-hover:border-primary-300'
    ],
    'minimal' => [
        'hph-bg-transparent',
        'hph-p-lg',
        'hph-h-full',
        'hph-transition-all',
        'hph-duration-300'
    ]
];

$base_card_classes = $card_style_classes[$feature_args['card_style']] ?? $card_style_classes['default'];
?>

<div class="<?php echo esc_attr(implode(' ', $container_classes)); ?>">
    <div class="<?php echo esc_attr(implode(' ', $grid_classes)); ?>">
        
        <?php foreach ($feature_args['items'] as $index => $item): 
            $title = $item['title'] ?? '';
            $description = $item['description'] ?? '';
            $icon = $item['icon'] ?? '';
            $link = $item['link'] ?? '';
            $color = $item['color'] ?? 'primary';
            
            // Build individual card classes
            $card_classes = array_merge($base_card_classes, [
                'hph-flex',
                'hph-flex-col',
                'hph-text-center'
            ]);
            
            if ($feature_args['animate']) {
                $card_classes[] = 'hph-animate-fade-in-up';
            }
            
            // Icon color classes
            $icon_classes = [
                'hph-w-20',
                'hph-h-20',
                'hph-mx-auto',
                'hph-mb-lg',
                'hph-flex',
                'hph-items-center',
                'hph-justify-center',
                'hph-rounded-full',
                'hph-text-2xl',
                'hph-transition-all',
                'hph-duration-300'
            ];
            
            // Color variations
            $color_classes = [
                'primary' => [
                    'icon_bg' => 'hph-bg-primary-100',
                    'icon_text' => 'hph-text-primary-600',
                    'hover_bg' => 'hph-hover:bg-primary-600',
                    'hover_text' => 'hph-hover:text-white',
                    'hover_scale' => 'hph-hover:scale-110'
                ],
                'secondary' => [
                    'icon_bg' => 'hph-bg-secondary-100',
                    'icon_text' => 'hph-text-secondary-600',
                    'hover_bg' => 'hph-hover:bg-secondary-600',
                    'hover_text' => 'hph-hover:text-white',
                    'hover_scale' => 'hph-hover:scale-110'
                ],
                'success' => [
                    'icon_bg' => 'hph-bg-success-100',
                    'icon_text' => 'hph-text-success-600',
                    'hover_bg' => 'hph-hover:bg-success-600',
                    'hover_text' => 'hph-hover:text-white',
                    'hover_scale' => 'hph-hover:scale-110'
                ]
            ];
            
            $selected_colors = $color_classes[$color] ?? $color_classes['primary'];
            $icon_classes = array_merge($icon_classes, [
                $selected_colors['icon_bg'],
                $selected_colors['icon_text'],
                $selected_colors['hover_bg'],
                $selected_colors['hover_text'],
                $selected_colors['hover_scale']
            ]);
        ?>
        
        <div class="<?php echo esc_attr(implode(' ', $card_classes)); ?>" 
             <?php if ($feature_args['animate']): ?>
             style="animation-delay: <?php echo esc_attr($index * $feature_args['stagger_delay']); ?>ms;"
             <?php endif; ?>>
            
            <?php if ($link): ?>
            <a href="<?php echo esc_url($link); ?>" 
               class="hph-flex hph-flex-col hph-h-full hph-text-decoration-none hph-text-inherit hph-hover:text-inherit">
            <?php endif; ?>
            
                <?php if ($icon): ?>
                <div class="<?php echo esc_attr(implode(' ', $icon_classes)); ?>">
                    <i class="<?php echo esc_attr($icon); ?>"></i>
                </div>
                <?php endif; ?>
                
                <div class="hph-flex-1">
                    <?php if ($title): ?>
                    <h3 class="hph-text-xl hph-font-semibold hph-text-gray-900 hph-mb-md hph-leading-tight">
                        <?php echo esc_html($title); ?>
                    </h3>
                    <?php endif; ?>
                    
                    <?php if ($description): ?>
                    <p class="hph-text-gray-600 hph-leading-relaxed hph-m-0">
                        <?php echo wp_kses_post($description); ?>
                    </p>
                    <?php endif; ?>
                </div>
                
                <?php if ($link): ?>
                <div class="hph-mt-lg hph-text-primary-600 hph-opacity-0 hph-transform hph-translate-x-2 hph-transition-all hph-duration-300 hph-group-hover:opacity-100 hph-group-hover:translate-x-0">
                    <i class="fas fa-arrow-right"></i>
                </div>
                <?php endif; ?>
            
            <?php if ($link): ?>
            </a>
            <?php endif; ?>
        </div>
        
        <?php endforeach; ?>
    </div>
</div>