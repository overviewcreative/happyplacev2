<?php
/**
 * Stat Card Component - Reusable statistics card for dashboards
 *
 * @package HappyPlaceTheme
 */

// Default attributes
$stat_args = wp_parse_args($args ?? [], [
    'title' => '',
    'value' => '',
    'subtitle' => '',
    'icon' => 'fa-chart-line',
    'color' => 'primary',
    'size' => 'medium',
    'format' => 'number',
    'trend' => null, // 'up', 'down', 'neutral'
    'trend_value' => '',
    'link_url' => '',
    'link_text' => '',
    'loading' => false
]);

// Format value based on type
$formatted_value = $stat_args['value'];
switch ($stat_args['format']) {
    case 'currency':
        $formatted_value = '$' . number_format((float)$stat_args['value']);
        break;
    case 'percentage':
        $formatted_value = number_format((float)$stat_args['value'], 1) . '%';
        break;
    case 'number':
        if (is_numeric($stat_args['value'])) {
            $formatted_value = number_format((int)$stat_args['value']);
        }
        break;
}

// Build component classes using utility system
$card_classes = [
    'hph-card',
    'hph-rounded-lg',
    'hph-shadow-sm',
    'hph-transition-all',
    'hph-duration-300',
    'hph-hover:shadow-lg',
    'hph-hover:-translate-y-1'
];

// Size classes with proper utility names
$size_classes = [
    'small' => ['hph-p-md'],
    'medium' => ['hph-p-lg'], 
    'large' => ['hph-p-xl']
];

$card_classes = array_merge($card_classes, $size_classes[$stat_args['size']] ?? $size_classes['medium']);

// Color classes using our utility system
$color_classes = [
    'primary' => ['hph-bg-primary-50', 'hph-border-primary-200'],
    'secondary' => ['hph-bg-secondary-50', 'hph-border-secondary-200'],
    'success' => ['hph-bg-green-50', 'hph-border-green-200'],
    'danger' => ['hph-bg-red-50', 'hph-border-red-200'],
    'warning' => ['hph-bg-yellow-50', 'hph-border-yellow-200'],
    'info' => ['hph-bg-blue-50', 'hph-border-blue-200'],
    'light' => ['hph-bg-gray-50', 'hph-border-gray-200'],
    'dark' => ['hph-bg-gray-900', 'hph-text-white']
];

$card_classes = array_merge($card_classes, $color_classes[$stat_args['color']] ?? $color_classes['primary']);

// Trend classes using utilities
$trend_classes = [
    'up' => ['hph-text-green-600', 'hph-badge-success'],
    'down' => ['hph-text-red-600', 'hph-badge-danger'], 
    'neutral' => ['hph-text-gray-600', 'hph-badge-light']
];

$trend_icons = [
    'up' => 'fa-arrow-up',
    'down' => 'fa-arrow-down',
    'neutral' => 'fa-minus'
];

// Icon classes
$icon_classes = [
    'hph-w-lg',
    'hph-h-lg', 
    'hph-rounded-full',
    'hph-flex',
    'hph-items-center',
    'hph-justify-center',
    'hph-text-white',
    'hph-bg-' . $stat_args['color']
];
?>

<div class="<?php echo esc_attr(implode(' ', $card_classes)); ?>" data-stat-card="<?php echo esc_attr($stat_args['color']); ?>">
    <?php if ($stat_args['loading']) : ?>
        <div class="hph-flex hph-items-center hph-justify-center hph-min-h-xl">
            <div class="hph-animate-spin hph-loading-spin">
                <i class="fas fa-circle-notch hph-text-lg hph-text-<?php echo esc_attr($stat_args['color']); ?>"></i>
            </div>
        </div>
    <?php else : ?>
        <div class="hph-space-y-md">
            <!-- Header with icon and title -->
            <?php if ($stat_args['title'] || $stat_args['icon']) : ?>
                <div class="hph-flex hph-items-center hph-justify-between">
                    <?php if ($stat_args['title']) : ?>
                        <h4 class="hph-text-sm hph-font-medium hph-text-gray-600 hph-uppercase hph-tracking-wide">
                            <?php echo esc_html($stat_args['title']); ?>
                        </h4>
                    <?php endif; ?>
                    
                    <?php if ($stat_args['icon']) : ?>
                        <div class="<?php echo esc_attr(implode(' ', $icon_classes)); ?>">
                            <i class="fas <?php echo esc_attr($stat_args['icon']); ?> hph-text-sm"></i>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- Main value -->
            <div class="hph-flex hph-items-baseline hph-gap-sm">
                <div class="hph-text-3xl hph-font-bold hph-text-<?php echo esc_attr($stat_args['color']); ?> hph-animate-fade-in-up">
                    <?php echo esc_html($formatted_value); ?>
                </div>
                
                <?php if ($stat_args['trend']) : ?>
                    <div class="hph-badge hph-badge-xs <?php echo esc_attr(implode(' ', $trend_classes[$stat_args['trend']] ?? [])); ?>">
                        <i class="fas <?php echo esc_attr($trend_icons[$stat_args['trend']] ?? 'fa-minus'); ?> hph-mr-xs"></i>
                        <?php if ($stat_args['trend_value']) : ?>
                            <span><?php echo esc_html($stat_args['trend_value']); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Subtitle -->
            <?php if ($stat_args['subtitle']) : ?>
                <div class="hph-text-xs hph-text-gray-500">
                    <?php echo esc_html($stat_args['subtitle']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Action link -->
            <?php if ($stat_args['link_url']) : ?>
                <div class="hph-mt-md">
                    <a href="<?php echo esc_url($stat_args['link_url']); ?>" 
                       class="hph-inline-flex hph-items-center hph-text-<?php echo esc_attr($stat_args['color']); ?> hph-text-xs hph-font-medium hph-transition-colors hph-hover:text-<?php echo esc_attr($stat_args['color']); ?>-dark">
                        <?php echo esc_html($stat_args['link_text'] ?: 'View Details'); ?>
                        <i class="fas fa-arrow-right hph-ml-xs hph-transition-transform hph-hover:translate-x-1"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>