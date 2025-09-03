<?php
/**
 * Base Rating Component
 * Star rating display and input with comprehensive configuration options
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rating component configuration
 */
$defaults = [
    // Content
    'value' => 0,
    'max_rating' => 5,
    'precision' => 1, // 1 = whole stars, 0.5 = half stars, 0.1 = decimal
    'name' => 'rating',
    
    // Behavior
    'interactive' => false,
    'readonly' => false,
    'disabled' => false,
    'required' => false,
    'hover_preview' => true,
    
    // Display
    'show_value' => false,
    'show_count' => false,
    'count' => 0,
    'show_labels' => false,
    'labels' => [], // Custom labels for each rating value
    
    // Style variants
    'variant' => 'default', // default, filled, outline, minimal
    'size' => 'md', // xs, sm, md, lg, xl
    'color' => 'default', // default, primary, secondary, warning, custom
    'shape' => 'star', // star, heart, thumb, circle, square
    
    // Icons
    'filled_icon' => '',
    'empty_icon' => '',
    'half_icon' => '',
    
    // Text
    'empty_text' => 'Not rated',
    'singular_text' => 'star',
    'plural_text' => 'stars',
    
    // Accessibility
    'label' => '',
    'description' => '',
    'aria_label' => '',
    
    // CSS classes
    'container_class' => '',
    'star_class' => '',
    'text_class' => '',
    
    // Data attributes
    'data_attributes' => [],
    
    // Advanced features
    'allow_zero' => true,
    'allow_clear' => false,
    'show_tooltip' => false,
    'custom_messages' => [],
];

$props = wp_parse_args(hph_get_arg() ?? [], $defaults);

// Generate unique ID
$rating_id = $props['id'] ?? 'hph-rating-' . wp_unique_id();
$input_id = $rating_id . '-input';

// Normalize value based on precision
if ($props['precision'] === 0.5) {
    $props['value'] = round($props['value'] * 2) / 2;
} elseif ($props['precision'] === 1) {
    $props['value'] = round($props['value']);
} else {
    $props['value'] = round($props['value'] / $props['precision']) * $props['precision'];
}

// Build CSS classes
$container_classes = [
    'hph-rating',
    'hph-rating--' . $props['variant'],
    'hph-rating--' . $props['size'],
    'hph-rating--' . $props['color'],
    'hph-rating--' . $props['shape'],
];

if ($props['interactive']) {
    $container_classes[] = 'hph-rating--interactive';
}

if ($props['readonly']) {
    $container_classes[] = 'hph-rating--readonly';
}

if ($props['disabled']) {
    $container_classes[] = 'hph-rating--disabled';
}

if ($props['show_value'] || $props['show_count']) {
    $container_classes[] = 'hph-rating--with-text';
}

if (!empty($props['container_class'])) {
    $container_classes[] = $props['container_class'];
}

$stars_classes = [
    'hph-rating__stars',
];

if (!empty($props['star_class'])) {
    $stars_classes[] = $props['star_class'];
}

// Data attributes
$data_attrs = [
    'data-rating-value' => $props['value'],
    'data-rating-max' => $props['max_rating'],
    'data-rating-precision' => $props['precision'],
];

if ($props['interactive']) {
    $data_attrs['data-rating-interactive'] = 'true';
    $data_attrs['data-rating-name'] = $props['name'];
}

if ($props['hover_preview']) {
    $data_attrs['data-rating-hover'] = 'true';
}

if (!empty($props['data_attributes'])) {
    $data_attrs = array_merge($data_attrs, $props['data_attributes']);
}

// Icon mapping
$icon_map = [
    'star' => [
        'filled' => '<polygon points="12,2 15,8 22,9 17,14 18,21 12,18 6,21 7,14 2,9 9,8" fill="currentColor"/>',
        'empty' => '<polygon points="12,2 15,8 22,9 17,14 18,21 12,18 6,21 7,14 2,9 9,8" stroke="currentColor" stroke-width="2" fill="none"/>',
        'half' => '<defs><mask id="half-star"><rect x="0" y="0" width="12" height="24" fill="white"/></mask></defs><polygon points="12,2 15,8 22,9 17,14 18,21 12,18 6,21 7,14 2,9 9,8" fill="currentColor" mask="url(#half-star)"/><polygon points="12,2 15,8 22,9 17,14 18,21 12,18 6,21 7,14 2,9 9,8" stroke="currentColor" stroke-width="2" fill="none"/>'
    ],
    'heart' => [
        'filled' => '<path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" fill="currentColor"/>',
        'empty' => '<path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" stroke="currentColor" stroke-width="2" fill="none"/>',
        'half' => '<defs><mask id="half-heart"><rect x="0" y="0" width="12" height="24" fill="white"/></mask></defs><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" fill="currentColor" mask="url(#half-heart)"/><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" stroke="currentColor" stroke-width="2" fill="none"/>'
    ],
    'thumb' => [
        'filled' => '<path d="M7 10v12l4 1h6c1.1 0 2-.9 2-2v-5.5c0-.8-.7-1.5-1.5-1.5H14l1.4-5.6c.1-.4-.1-.8-.4-1.1L14 7l-3.6 3.6c-.4.4-.6.9-.6 1.4z" fill="currentColor"/><path d="M1 21h4V10H1v11z" fill="currentColor"/>',
        'empty' => '<path d="M7 10v12l4 1h6c1.1 0 2-.9 2-2v-5.5c0-.8-.7-1.5-1.5-1.5H14l1.4-5.6c.1-.4-.1-.8-.4-1.1L14 7l-3.6 3.6c-.4.4-.6.9-.6 1.4z" stroke="currentColor" stroke-width="2" fill="none"/><path d="M1 21h4V10H1v11z" stroke="currentColor" stroke-width="2" fill="none"/>',
        'half' => '<defs><mask id="half-thumb"><rect x="0" y="0" width="12" height="24" fill="white"/></mask></defs><path d="M7 10v12l4 1h6c1.1 0 2-.9 2-2v-5.5c0-.8-.7-1.5-1.5-1.5H14l1.4-5.6c.1-.4-.1-.8-.4-1.1L14 7l-3.6 3.6c-.4.4-.6.9-.6 1.4z" fill="currentColor" mask="url(#half-thumb)"/><path d="M1 21h4V10H1v11z" fill="currentColor" mask="url(#half-thumb)"/><path d="M7 10v12l4 1h6c1.1 0 2-.9 2-2v-5.5c0-.8-.7-1.5-1.5-1.5H14l1.4-5.6c.1-.4-.1-.8-.4-1.1L14 7l-3.6 3.6c-.4.4-.6.9-.6 1.4z" stroke="currentColor" stroke-width="2" fill="none"/><path d="M1 21h4V10H1v11z" stroke="currentColor" stroke-width="2" fill="none"/>'
    ],
    'circle' => [
        'filled' => '<circle cx="12" cy="12" r="10" fill="currentColor"/>',
        'empty' => '<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/>',
        'half' => '<defs><mask id="half-circle"><rect x="0" y="0" width="12" height="24" fill="white"/></mask></defs><circle cx="12" cy="12" r="10" fill="currentColor" mask="url(#half-circle)"/><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/>'
    ],
    'square' => [
        'filled' => '<rect x="2" y="2" width="20" height="20" rx="2" fill="currentColor"/>',
        'empty' => '<rect x="2" y="2" width="20" height="20" rx="2" stroke="currentColor" stroke-width="2" fill="none"/>',
        'half' => '<defs><mask id="half-square"><rect x="0" y="0" width="12" height="24" fill="white"/></mask></defs><rect x="2" y="2" width="20" height="20" rx="2" fill="currentColor" mask="url(#half-square)"/><rect x="2" y="2" width="20" height="20" rx="2" stroke="currentColor" stroke-width="2" fill="none"/>'
    ]
];

$current_icons = $icon_map[$props['shape']] ?? $icon_map['star'];

// Override with custom icons if provided
if ($props['filled_icon']) $current_icons['filled'] = $props['filled_icon'];
if ($props['empty_icon']) $current_icons['empty'] = $props['empty_icon'];
if ($props['half_icon']) $current_icons['half'] = $props['half_icon'];

// Generate rating text
function generate_rating_text($value, $max, $count, $singular, $plural, $show_value, $show_count) {
    $parts = [];
    
    if ($show_value) {
        if ($value == 0) {
            $parts[] = 'Not rated';
        } else {
            $star_text = $value == 1 ? $singular : $plural;
            $parts[] = number_format($value, 1) . ' ' . $star_text;
        }
    }
    
    if ($show_count && $count > 0) {
        $parts[] = '(' . number_format($count) . ')';
    }
    
    return implode(' ', $parts);
}

$rating_text = generate_rating_text(
    $props['value'], 
    $props['max_rating'], 
    $props['count'], 
    $props['singular_text'], 
    $props['plural_text'], 
    $props['show_value'], 
    $props['show_count']
);

// Size mapping for icons
$icon_sizes = [
    'xs' => '12',
    'sm' => '16',
    'md' => '20',
    'lg' => '24',
    'xl' => '28'
];
$icon_size = $icon_sizes[$props['size']] ?? '20';

// Aria label
$aria_label = $props['aria_label'] ?: 
    ($props['value'] ? 
        sprintf('Rated %s out of %s %s', $props['value'], $props['max_rating'], $props['plural_text']) : 
        $props['empty_text']);
?>

<div 
    id="<?php echo esc_attr($rating_id); ?>"
    class="<?php echo esc_attr(implode(' ', $container_classes)); ?>"
    <?php foreach ($data_attrs as $key => $value): ?>
        <?php echo esc_attr($key); ?>="<?php echo esc_attr($value); ?>"
    <?php endforeach; ?>
    role="<?php echo $props['interactive'] ? 'radiogroup' : 'img'; ?>"
    aria-label="<?php echo esc_attr($aria_label); ?>"
    <?php if ($props['description']): ?>aria-describedby="<?php echo esc_attr($rating_id); ?>-description"<?php endif; ?>
>
    <?php if ($props['label']): ?>
        <label class="hph-rating__label" <?php if ($props['interactive']): ?>for="<?php echo esc_attr($input_id); ?>"<?php endif; ?>>
            <?php echo esc_html($props['label']); ?>
            <?php if ($props['required']): ?>
                <span class="hph-rating__required" aria-label="required">*</span>
            <?php endif; ?>
        </label>
    <?php endif; ?>

    <?php if ($props['description']): ?>
        <div class="hph-rating__description" id="<?php echo esc_attr($rating_id); ?>-description">
            <?php echo wp_kses_post($props['description']); ?>
        </div>
    <?php endif; ?>

    <div class="hph-rating__container">
        <div class="<?php echo esc_attr(implode(' ', $stars_classes)); ?>">
            <?php for ($i = 1; $i <= $props['max_rating']; $i++): ?>
                <?php
                $star_value = $i;
                $is_filled = $props['value'] >= $star_value;
                $is_half = !$is_filled && $props['precision'] === 0.5 && $props['value'] >= ($star_value - 0.5);
                
                $star_classes = ['hph-rating__star'];
                if ($is_filled) $star_classes[] = 'hph-rating__star--filled';
                if ($is_half) $star_classes[] = 'hph-rating__star--half';
                if (!$is_filled && !$is_half) $star_classes[] = 'hph-rating__star--empty';
                
                $star_icon = $is_filled ? $current_icons['filled'] : 
                            ($is_half ? $current_icons['half'] : $current_icons['empty']);
                
                if ($props['interactive']):
                ?>
                    <label class="<?php echo esc_attr(implode(' ', $star_classes)); ?>" 
                           data-rating="<?php echo esc_attr($star_value); ?>">
                        <input 
                            type="radio" 
                            name="<?php echo esc_attr($props['name']); ?>" 
                            value="<?php echo esc_attr($star_value); ?>"
                            <?php checked($props['value'], $star_value); ?>
                            <?php if ($props['disabled']): ?>disabled<?php endif; ?>
                            <?php if ($props['required']): ?>required<?php endif; ?>
                            class="hph-rating__input"
                            aria-label="<?php echo esc_attr($star_value); ?> <?php echo $star_value == 1 ? $props['singular_text'] : $props['plural_text']; ?>"
                        >
                        <svg class="hph-rating__icon" width="<?php echo esc_attr($icon_size); ?>" height="<?php echo esc_attr($icon_size); ?>" viewBox="0 0 24 24" aria-hidden="true">
                            <?php echo $star_icon; ?>
                        </svg>
                    </label>
                <?php else: ?>
                    <span class="<?php echo esc_attr(implode(' ', $star_classes)); ?>" 
                          data-rating="<?php echo esc_attr($star_value); ?>"
                          aria-hidden="true">
                        <svg class="hph-rating__icon" width="<?php echo esc_attr($icon_size); ?>" height="<?php echo esc_attr($icon_size); ?>" viewBox="0 0 24 24">
                            <?php echo $star_icon; ?>
                        </svg>
                    </span>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($props['allow_clear'] && $props['interactive']): ?>
                <button type="button" class="hph-rating__clear" aria-label="Clear rating">
                    <svg class="hph-rating__clear-icon" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 6 6 18"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 6 12 12"/>
                    </svg>
                </button>
            <?php endif; ?>
        </div>

        <?php if ($rating_text): ?>
            <div class="hph-rating__text <?php echo esc_attr($props['text_class']); ?>">
                <?php echo esc_html($rating_text); ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($props['show_labels'] && !empty($props['labels'])): ?>
        <div class="hph-rating__labels">
            <?php foreach ($props['labels'] as $index => $label): ?>
                <span class="hph-rating__label-item <?php echo $props['value'] == ($index + 1) ? 'hph-rating__label-item--active' : ''; ?>">
                    <?php echo esc_html($label); ?>
                </span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
