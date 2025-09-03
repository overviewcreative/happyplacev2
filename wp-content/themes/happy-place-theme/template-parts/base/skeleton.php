<?php
/**
 * Base Skeleton Component
 * Loading placeholder with animated shimmer effect
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Skeleton component configuration
 */
$defaults = [
    // Basic configuration
    'type' => 'text', // text, heading, paragraph, image, avatar, card, list, table, custom
    'lines' => 3, // Number of lines for paragraph type
    'width' => null, // Custom width (auto, 100%, 300px, etc.)
    'height' => null, // Custom height (auto, 40px, 200px, etc.)
    
    // Visual variants
    'animation' => 'pulse', // pulse, wave, shimmer, none
    'speed' => 'medium', // slow, medium, fast
    'rounded' => null, // null (default), true, false, or specific value (sm, md, lg, full)
    
    // Size variants
    'size' => 'md', // xs, sm, md, lg, xl (affects default dimensions)
    
    // Layout
    'count' => 1, // Number of skeleton elements
    'spacing' => 'md', // xs, sm, md, lg, xl (space between multiple elements)
    'full_width' => false,
    
    // Content-specific configurations
    'avatar_size' => 'md', // xs, sm, md, lg, xl
    'image_aspect' => '16:9', // aspect ratio for image skeletons
    'card_elements' => ['image', 'title', 'text'], // Elements to show in card skeleton
    'list_items' => 5, // Number of items for list skeleton
    'table_rows' => 5, // Number of rows for table skeleton
    'table_cols' => 4, // Number of columns for table skeleton
    
    // Advanced options
    'show_shimmer' => true,
    'delay' => 0, // Animation delay in ms
    'duration' => null, // Custom animation duration
    
    // CSS classes
    'container_class' => '',
    'skeleton_class' => '',
    
    // Data attributes
    'data_attributes' => [],
];

$props = wp_parse_args($props ?? [], $defaults);

// Generate unique ID
$skeleton_id = $props['id'] ?? 'hph-skeleton-' . wp_unique_id();

// Build CSS classes
$container_classes = [
    'hph-skeleton-container',
    'hph-skeleton-container--' . $props['type'],
    'hph-skeleton-container--' . $props['size'],
    'hph-skeleton-container--spacing-' . $props['spacing'],
];

if ($props['animation'] !== 'none') {
    $container_classes[] = 'hph-skeleton-container--animated';
    $container_classes[] = 'hph-skeleton-container--' . $props['animation'];
    $container_classes[] = 'hph-skeleton-container--speed-' . $props['speed'];
}

if ($props['full_width']) {
    $container_classes[] = 'hph-skeleton-container--full-width';
}

if (!empty($props['container_class'])) {
    $container_classes[] = $props['container_class'];
}

$skeleton_classes = [
    'hph-skeleton',
    'hph-skeleton--' . $props['type'],
];

if ($props['show_shimmer'] && $props['animation'] !== 'none') {
    $skeleton_classes[] = 'hph-skeleton--shimmer';
}

// Handle rounded property
if ($props['rounded'] === true) {
    $skeleton_classes[] = 'hph-skeleton--rounded';
} elseif ($props['rounded'] === false) {
    $skeleton_classes[] = 'hph-skeleton--sharp';
} elseif (is_string($props['rounded'])) {
    $skeleton_classes[] = 'hph-skeleton--rounded-' . $props['rounded'];
} else {
    // Default rounding based on type
    switch ($props['type']) {
        case 'avatar':
            $skeleton_classes[] = 'hph-skeleton--rounded-full';
            break;
        case 'image':
        case 'card':
            $skeleton_classes[] = 'hph-skeleton--rounded';
            break;
        default:
            $skeleton_classes[] = 'hph-skeleton--rounded-sm';
            break;
    }
}

if (!empty($props['skeleton_class'])) {
    $skeleton_classes[] = $props['skeleton_class'];
}

// Build inline styles
$styles = [];

if ($props['width']) {
    $styles[] = 'width: ' . (is_numeric($props['width']) ? $props['width'] . 'px' : $props['width']);
}

if ($props['height']) {
    $styles[] = 'height: ' . (is_numeric($props['height']) ? $props['height'] . 'px' : $props['height']);
}

if ($props['delay'] > 0) {
    $styles[] = 'animation-delay: ' . $props['delay'] . 'ms';
}

if ($props['duration']) {
    $styles[] = 'animation-duration: ' . $props['duration'];
}

$style_attr = !empty($styles) ? 'style="' . implode('; ', $styles) . '"' : '';

// Data attributes
$data_attrs = [
    'data-skeleton-type' => $props['type'],
    'data-skeleton-animation' => $props['animation'],
];

if (!empty($props['data_attributes'])) {
    $data_attrs = array_merge($data_attrs, $props['data_attributes']);
}

// Helper functions
function render_skeleton_element($classes, $style = '', $content = '') {
    $element_classes = is_array($classes) ? implode(' ', $classes) : $classes;
    echo '<div class="' . esc_attr($element_classes) . '"' . ($style ? ' ' . $style : '') . '>' . $content . '</div>';
}

function get_aspect_ratio_class($aspect) {
    $ratios = [
        '1:1' => 'square',
        '4:3' => 'aspect-4-3',
        '16:9' => 'aspect-16-9',
        '21:9' => 'aspect-21-9',
        '3:2' => 'aspect-3-2',
    ];
    return $ratios[$aspect] ?? 'aspect-16-9';
}
?>

<div 
    id="<?php echo esc_attr($skeleton_id); ?>"
    class="<?php echo esc_attr(implode(' ', $container_classes)); ?>"
    <?php foreach ($data_attrs as $key => $value): ?>
        <?php echo esc_attr($key); ?>="<?php echo esc_attr($value); ?>"
    <?php endforeach; ?>
>
    <?php for ($i = 0; $i < $props['count']; $i++): ?>
        <?php if ($props['type'] === 'text'): ?>
            <?php render_skeleton_element($skeleton_classes, $style_attr); ?>

        <?php elseif ($props['type'] === 'heading'): ?>
            <?php render_skeleton_element(array_merge($skeleton_classes, ['hph-skeleton--heading']), $style_attr); ?>

        <?php elseif ($props['type'] === 'paragraph'): ?>
            <div class="hph-skeleton__paragraph">
                <?php for ($line = 0; $line < $props['lines']; $line++): ?>
                    <?php 
                    $line_classes = array_merge($skeleton_classes, ['hph-skeleton--line']);
                    if ($line === $props['lines'] - 1) {
                        $line_classes[] = 'hph-skeleton--line-last';
                    }
                    render_skeleton_element($line_classes, $style_attr);
                    ?>
                <?php endfor; ?>
            </div>

        <?php elseif ($props['type'] === 'image'): ?>
            <?php 
            $image_classes = array_merge($skeleton_classes, [
                'hph-skeleton--image',
                'hph-skeleton--' . get_aspect_ratio_class($props['image_aspect'])
            ]);
            render_skeleton_element($image_classes, $style_attr);
            ?>

        <?php elseif ($props['type'] === 'avatar'): ?>
            <?php 
            $avatar_classes = array_merge($skeleton_classes, [
                'hph-skeleton--avatar',
                'hph-skeleton--avatar-' . $props['avatar_size']
            ]);
            render_skeleton_element($avatar_classes, $style_attr);
            ?>

        <?php elseif ($props['type'] === 'card'): ?>
            <div class="hph-skeleton__card">
                <?php if (in_array('image', $props['card_elements'])): ?>
                    <?php render_skeleton_element(array_merge($skeleton_classes, ['hph-skeleton--card-image']), $style_attr); ?>
                <?php endif; ?>
                
                <div class="hph-skeleton__card-content">
                    <?php if (in_array('title', $props['card_elements'])): ?>
                        <?php render_skeleton_element(array_merge($skeleton_classes, ['hph-skeleton--card-title']), $style_attr); ?>
                    <?php endif; ?>
                    
                    <?php if (in_array('text', $props['card_elements'])): ?>
                        <div class="hph-skeleton__card-text">
                            <?php render_skeleton_element(array_merge($skeleton_classes, ['hph-skeleton--line']), $style_attr); ?>
                            <?php render_skeleton_element(array_merge($skeleton_classes, ['hph-skeleton--line', 'hph-skeleton--line-short']), $style_attr); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (in_array('button', $props['card_elements'])): ?>
                        <?php render_skeleton_element(array_merge($skeleton_classes, ['hph-skeleton--button']), $style_attr); ?>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($props['type'] === 'list'): ?>
            <div class="hph-skeleton__list">
                <?php for ($item = 0; $item < $props['list_items']; $item++): ?>
                    <div class="hph-skeleton__list-item">
                        <?php render_skeleton_element(array_merge($skeleton_classes, ['hph-skeleton--avatar', 'hph-skeleton--avatar-sm']), $style_attr); ?>
                        <div class="hph-skeleton__list-content">
                            <?php render_skeleton_element(array_merge($skeleton_classes, ['hph-skeleton--line']), $style_attr); ?>
                            <?php render_skeleton_element(array_merge($skeleton_classes, ['hph-skeleton--line', 'hph-skeleton--line-short']), $style_attr); ?>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>

        <?php elseif ($props['type'] === 'table'): ?>
            <div class="hph-skeleton__table">
                <!-- Table header -->
                <div class="hph-skeleton__table-header">
                    <?php for ($col = 0; $col < $props['table_cols']; $col++): ?>
                        <?php render_skeleton_element(array_merge($skeleton_classes, ['hph-skeleton--table-header-cell']), $style_attr); ?>
                    <?php endfor; ?>
                </div>
                
                <!-- Table rows -->
                <?php for ($row = 0; $row < $props['table_rows']; $row++): ?>
                    <div class="hph-skeleton__table-row">
                        <?php for ($col = 0; $col < $props['table_cols']; $col++): ?>
                            <?php render_skeleton_element(array_merge($skeleton_classes, ['hph-skeleton--table-cell']), $style_attr); ?>
                        <?php endfor; ?>
                    </div>
                <?php endfor; ?>
            </div>

        <?php else: // custom type ?>
            <?php render_skeleton_element($skeleton_classes, $style_attr); ?>

        <?php endif; ?>
    <?php endfor; ?>
</div>

<?php
/**
 * Usage Examples:
 * 
 * Property card skeleton:
 * get_template_part('template-parts/base/skeleton', null, [
 *     'type' => 'card',
 *     'card_elements' => ['image', 'title', 'text', 'button'],
 *     'count' => 3,
 *     'animation' => 'shimmer'
 * ]);
 * 
 * Property list skeleton:
 * get_template_part('template-parts/base/skeleton', null, [
 *     'type' => 'list',
 *     'list_items' => 5,
 *     'animation' => 'pulse'
 * ]);
 * 
 * Simple text skeleton:
 * get_template_part('template-parts/base/skeleton', null, [
 *     'type' => 'paragraph',
 *     'lines' => 4,
 *     'width' => '80%'
 * ]);
 * 
 * Image gallery skeleton:
 * get_template_part('template-parts/base/skeleton', null, [
 *     'type' => 'image',
 *     'count' => 6,
 *     'image_aspect' => '1:1',
 *     'spacing' => 'sm'
 * ]);
 * 
 * Agent profile skeleton:
 * get_template_part('template-parts/base/skeleton', null, [
 *     'type' => 'avatar',
 *     'avatar_size' => 'lg',
 *     'animation' => 'wave'
 * ]);
 */
?>
