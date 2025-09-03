<?php
/**
 * Base Empty State Component
 * Display for when no content or data is available
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Empty state component configuration
 */
$defaults = [
    // Content
    'title' => 'No Results Found',
    'description' => 'We couldn\'t find any results matching your criteria.',
    'icon' => 'search-x', // Icon name or custom HTML
    'image' => '', // Custom image URL
    
    // Actions
    'primary_action' => null, // ['label' => 'Button Text', 'url' => '#', 'action' => 'callback']
    'secondary_action' => null,
    'actions' => [], // Additional actions
    
    // Visual variants
    'variant' => 'default', // default, search, error, maintenance, no-data, no-results
    'size' => 'md', // sm, md, lg, xl
    'layout' => 'center', // center, left, card
    
    // Icon/Image configuration
    'icon_size' => 'lg', // xs, sm, md, lg, xl, 2xl
    'icon_color' => 'muted', // primary, secondary, muted, success, warning, error
    'show_icon' => true,
    
    // Style options
    'background' => false, // Add background container
    'border' => false, // Add border
    'shadow' => false, // Add shadow
    'rounded' => false, // Rounded corners
    'full_height' => false, // Take full viewport height
    'padding' => 'lg', // xs, sm, md, lg, xl
    
    // Content organization
    'show_suggestions' => false, // Show helpful suggestions
    'suggestions' => [], // Array of suggestion text
    'show_help_link' => false,
    'help_link_text' => 'Need help?',
    'help_link_url' => '#',
    
    // Animation
    'animate' => false,
    'animation_delay' => 0,
    
    // CSS classes
    'container_class' => '',
    'content_class' => '',
    
    // Data attributes
    'data_attributes' => [],
];

$props = wp_parse_args($props ?? [], $defaults);

// Generate unique ID
$empty_id = $props['id'] ?? 'hph-empty-' . wp_unique_id();

// Determine variant-specific defaults
$variant_config = [
    'search' => [
        'icon' => 'search-x',
        'title' => 'No Properties Found',
        'description' => 'Try adjusting your search criteria or browse all available properties.',
    ],
    'error' => [
        'icon' => 'alert-triangle',
        'icon_color' => 'error',
        'title' => 'Something Went Wrong',
        'description' => 'We encountered an error while loading the content. Please try again.',
    ],
    'maintenance' => [
        'icon' => 'wrench',
        'icon_color' => 'warning',
        'title' => 'Under Maintenance',
        'description' => 'This feature is temporarily unavailable while we make improvements.',
    ],
    'no-data' => [
        'icon' => 'database-x',
        'title' => 'No Data Available',
        'description' => 'There is currently no data to display.',
    ],
    'no-results' => [
        'icon' => 'search-x',
        'title' => 'No Results Found',
        'description' => 'Your search didn\'t return any results. Try different keywords.',
    ],
];

// Apply variant defaults
if (isset($variant_config[$props['variant']])) {
    foreach ($variant_config[$props['variant']] as $key => $value) {
        if (!isset($props[$key]) || $props[$key] === $defaults[$key]) {
            $props[$key] = $value;
        }
    }
}

// Build CSS classes
$container_classes = [
    'hph-empty-state',
    'hph-empty-state--' . $props['variant'],
    'hph-empty-state--' . $props['size'],
    'hph-empty-state--' . $props['layout'],
    'hph-empty-state--padding-' . $props['padding'],
];

if ($props['background']) {
    $container_classes[] = 'hph-empty-state--background';
}

if ($props['border']) {
    $container_classes[] = 'hph-empty-state--border';
}

if ($props['shadow']) {
    $container_classes[] = 'hph-empty-state--shadow';
}

if ($props['rounded']) {
    $container_classes[] = 'hph-empty-state--rounded';
}

if ($props['full_height']) {
    $container_classes[] = 'hph-empty-state--full-height';
}

if ($props['animate']) {
    $container_classes[] = 'hph-empty-state--animate';
}

if (!empty($props['container_class'])) {
    $container_classes[] = $props['container_class'];
}

$content_classes = [
    'hph-empty-state__content',
];

if (!empty($props['content_class'])) {
    $content_classes[] = $props['content_class'];
}

// Data attributes
$data_attrs = [
    'data-empty-variant' => $props['variant'],
    'data-empty-size' => $props['size'],
];

if ($props['animate'] && $props['animation_delay'] > 0) {
    $data_attrs['data-animation-delay'] = $props['animation_delay'];
}

if (!empty($props['data_attributes'])) {
    $data_attrs = array_merge($data_attrs, $props['data_attributes']);
}

// Helper function to render action button
function render_action_button($action, $is_primary = false) {
    if (!$action) return;
    
    $button_variant = $is_primary ? 'primary' : 'secondary';
    $button_props = [
        'text' => $action['label'],
        'variant' => $button_variant,
        'size' => 'md',
        'href' => $action['url'] ?? null,
        'onclick' => $action['action'] ?? null,
    ];
    
    get_template_part('template-parts/base/button', null, $button_props);
}
?>

<div 
    id="<?php echo esc_attr($empty_id); ?>"
    class="<?php echo esc_attr(implode(' ', $container_classes)); ?>"
    <?php foreach ($data_attrs as $key => $value): ?>
        <?php echo esc_attr($key); ?>="<?php echo esc_attr($value); ?>"
    <?php endforeach; ?>
>
    <div class="<?php echo esc_attr(implode(' ', $content_classes)); ?>">
        <?php if ($props['show_icon'] && ($props['icon'] || $props['image'])): ?>
            <div class="hph-empty-state__visual">
                <?php if ($props['image']): ?>
                    <img 
                        src="<?php echo esc_url($props['image']); ?>" 
                        alt="<?php echo esc_attr($props['title']); ?>"
                        class="hph-empty-state__image hph-empty-state__image--<?php echo esc_attr($props['size']); ?>"
                    >
                <?php elseif ($props['icon']): ?>
                    <div class="hph-empty-state__icon hph-empty-state__icon--<?php echo esc_attr($props['icon_color']); ?>">
                        <?php
                        get_template_part('template-parts/base/icon', null, [
                            'name' => $props['icon'],
                            'size' => $props['icon_size']
                        ]);
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="hph-empty-state__text">
            <?php if ($props['title']): ?>
                <h3 class="hph-empty-state__title">
                    <?php echo esc_html($props['title']); ?>
                </h3>
            <?php endif; ?>

            <?php if ($props['description']): ?>
                <p class="hph-empty-state__description">
                    <?php echo wp_kses_post($props['description']); ?>
                </p>
            <?php endif; ?>
        </div>

        <?php if ($props['show_suggestions'] && !empty($props['suggestions'])): ?>
            <div class="hph-empty-state__suggestions">
                <h4 class="hph-empty-state__suggestions-title">Try these suggestions:</h4>
                <ul class="hph-empty-state__suggestions-list">
                    <?php foreach ($props['suggestions'] as $suggestion): ?>
                        <li class="hph-empty-state__suggestion">
                            <?php echo wp_kses_post($suggestion); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($props['primary_action'] || $props['secondary_action'] || !empty($props['actions'])): ?>
            <div class="hph-empty-state__actions">
                <?php if ($props['primary_action']): ?>
                    <?php render_action_button($props['primary_action'], true); ?>
                <?php endif; ?>

                <?php if ($props['secondary_action']): ?>
                    <?php render_action_button($props['secondary_action'], false); ?>
                <?php endif; ?>

                <?php if (!empty($props['actions'])): ?>
                    <?php foreach ($props['actions'] as $action): ?>
                        <?php render_action_button($action, false); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($props['show_help_link']): ?>
            <div class="hph-empty-state__help">
                <a href="<?php echo esc_url($props['help_link_url']); ?>" class="hph-empty-state__help-link">
                    <?php echo esc_html($props['help_link_text']); ?>
                    <?php
                    get_template_part('template-parts/base/icon', null, [
                        'name' => 'external-link',
                        'size' => 'xs'
                    ]);
                    ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
/**
 * Usage Examples:
 * 
 * No search results:
 * get_template_part('template-parts/base/empty-state', null, [
 *     'variant' => 'search',
 *     'title' => 'No Properties Found',
 *     'description' => 'We couldn\'t find any properties matching your search criteria.',
 *     'primary_action' => [
 *         'label' => 'Clear Filters',
 *         'url' => '?clear=true'
 *     ],
 *     'secondary_action' => [
 *         'label' => 'Browse All Properties',
 *         'url' => '/listings'
 *     ],
 *     'show_suggestions' => true,
 *     'suggestions' => [
 *         'Try using fewer filters',
 *         'Check spelling of location names',
 *         'Expand your price range',
 *         'Consider nearby areas'
 *     ]
 * ]);
 * 
 * Error state:
 * get_template_part('template-parts/base/empty-state', null, [
 *     'variant' => 'error',
 *     'title' => 'Failed to Load Properties',
 *     'description' => 'There was an error loading the property listings.',
 *     'primary_action' => [
 *         'label' => 'Try Again',
 *         'action' => 'location.reload()'
 *     ],
 *     'show_help_link' => true,
 *     'help_link_text' => 'Contact Support',
 *     'help_link_url' => '/contact'
 * ]);
 * 
 * No favorites:
 * get_template_part('template-parts/base/empty-state', null, [
 *     'icon' => 'heart',
 *     'title' => 'No Saved Properties',
 *     'description' => 'You haven\'t saved any properties yet. Start browsing to find your dream home!',
 *     'primary_action' => [
 *         'label' => 'Browse Properties',
 *         'url' => '/listings'
 *     ],
 *     'layout' => 'center',
 *     'background' => true,
 *     'rounded' => true
 * ]);
 * 
 * Maintenance mode:
 * get_template_part('template-parts/base/empty-state', null, [
 *     'variant' => 'maintenance',
 *     'title' => 'Feature Coming Soon',
 *     'description' => 'We\'re working on this feature and it will be available soon.',
 *     'full_height' => true,
 *     'animate' => true
 * ]);
 */
?>
