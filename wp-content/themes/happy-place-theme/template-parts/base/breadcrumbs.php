<?php
/**
 * Base Breadcrumbs Component
 * 
 * Pure UI breadcrumbs component for navigation trails
 * No data dependencies, just presentation
 * 
 * @package HappyPlaceTheme
 * @subpackage Components/Base
 * @since 3.0.0
 */

$props = wp_parse_args(hph_get_arg(), array(
    // Content
    'items' => array(), // Array of breadcrumb items with 'text', 'url', 'icon' keys
    
    // Appearance
    'variant' => 'default', // default, minimal, rich, simple
    'size' => 'md', // sm, md, lg
    'separator' => 'chevron', // chevron, slash, arrow, dash, dot, custom
    'custom_separator' => '', // Custom separator text/icon
    
    // Behavior
    'show_home' => true, // Show home link at beginning
    'show_current' => true, // Show current page (last item)
    'max_items' => 0, // Maximum items to show (0 = unlimited)
    'collapse_on_mobile' => true, // Collapse to just current page on mobile
    
    // Icons
    'show_icons' => false, // Show icons for items
    'home_icon' => 'home', // Icon for home link
    
    // Schema markup
    'schema_markup' => true, // Include structured data
    
    // Labels
    'labels' => array(
        'home' => __('Home', 'happy-place-theme'),
        'breadcrumbs' => __('Breadcrumbs', 'happy-place-theme'),
        'current_page' => __('Current page', 'happy-place-theme')
    ),
    
    // HTML
    'id' => '',
    'class' => '',
    'attributes' => array(),
    'data' => array()
));

// Validate items
if (empty($props['items']) || !is_array($props['items'])) {
    return;
}

// Add home item if requested and not already present
if ($props['show_home'] && (empty($props['items']) || $props['items'][0]['text'] !== $props['labels']['home'])) {
    array_unshift($props['items'], array(
        'text' => $props['labels']['home'],
        'url' => home_url('/'),
        'icon' => $props['home_icon']
    ));
}

// Limit items if max_items is set
if ($props['max_items'] > 0 && count($props['items']) > $props['max_items']) {
    $items_to_show = $props['max_items'];
    $total_items = count($props['items']);
    
    // Keep first item (home) and last few items
    $keep_start = 1;
    $keep_end = $items_to_show - 2; // Account for ellipsis
    
    $breadcrumb_items = array_slice($props['items'], 0, $keep_start);
    $breadcrumb_items[] = array('text' => '...', 'ellipsis' => true);
    $breadcrumb_items = array_merge($breadcrumb_items, array_slice($props['items'], -$keep_end));
    
    $props['items'] = $breadcrumb_items;
}

// Generate unique ID if not provided
if (empty($props['id'])) {
    $props['id'] = 'hph-breadcrumbs-' . uniqid();
}

// Build breadcrumbs classes
$breadcrumbs_classes = array(
    'hph-breadcrumbs',
    'hph-breadcrumbs--' . $props['variant'],
    'hph-breadcrumbs--' . $props['size'],
    'hph-breadcrumbs--separator-' . $props['separator']
);

if ($props['show_icons']) {
    $breadcrumbs_classes[] = 'hph-breadcrumbs--with-icons';
}

if ($props['collapse_on_mobile']) {
    $breadcrumbs_classes[] = 'hph-breadcrumbs--collapse-mobile';
}

if (!empty($props['class'])) {
    $breadcrumbs_classes[] = $props['class'];
}

// Prepare data attributes
$data_attrs = array_merge(array(
    'separator' => $props['separator'],
    'max-items' => $props['max_items']
), $props['data']);

// Build attributes string
$attributes = array();
foreach ($props['attributes'] as $key => $value) {
    $attributes[] = esc_attr($key) . '="' . esc_attr($value) . '"';
}
foreach ($data_attrs as $key => $value) {
    $attributes[] = 'data-' . esc_attr($key) . '="' . esc_attr($value) . '"';
}
$attributes_string = implode(' ', $attributes);

// Get separator icon/text
function get_separator_content($separator, $custom = '') {
    switch ($separator) {
        case 'chevron':
            return '<span data-icon="chevron-right" aria-hidden="true"></span>';
        case 'slash':
            return '<span aria-hidden="true">/</span>';
        case 'arrow':
            return '<span data-icon="arrow-right" aria-hidden="true"></span>';
        case 'dash':
            return '<span aria-hidden="true">—</span>';
        case 'dot':
            return '<span aria-hidden="true">•</span>';
        case 'custom':
            return '<span aria-hidden="true">' . esc_html($custom) . '</span>';
        default:
            return '<span data-icon="chevron-right" aria-hidden="true"></span>';
    }
}

$total_items = count($props['items']);
?>

<nav 
    id="<?php echo esc_attr($props['id']); ?>"
    class="<?php echo esc_attr(implode(' ', $breadcrumbs_classes)); ?>"
    <?php echo $attributes_string; ?>
    role="navigation"
    aria-label="<?php echo esc_attr($props['labels']['breadcrumbs']); ?>"
    <?php if ($props['schema_markup']): ?>
    itemscope 
    itemtype="https://schema.org/BreadcrumbList"
    <?php endif; ?>
>
    
    <ol class="hph-breadcrumbs__list">
        
        <?php foreach ($props['items'] as $index => $item): 
            $is_last = ($index === $total_items - 1);
            $is_current = $is_last && $props['show_current'];
            $is_ellipsis = !empty($item['ellipsis']);
            
            // Item classes
            $item_classes = array('hph-breadcrumbs__item');
            if ($is_current) {
                $item_classes[] = 'hph-breadcrumbs__item--current';
            }
            if ($is_ellipsis) {
                $item_classes[] = 'hph-breadcrumbs__item--ellipsis';
            }
        ?>
        
        <li 
            class="<?php echo esc_attr(implode(' ', $item_classes)); ?>"
            <?php if ($props['schema_markup'] && !$is_ellipsis): ?>
            itemprop="itemListElement" 
            itemscope 
            itemtype="https://schema.org/ListItem"
            <?php endif; ?>
        >
            
            <?php if ($is_ellipsis): ?>
                <span class="hph-breadcrumbs__ellipsis" aria-hidden="true">
                    <?php echo esc_html($item['text']); ?>
                </span>
            
            <?php elseif ($is_current): ?>
                <span 
                    class="hph-breadcrumbs__current"
                    aria-current="page"
                    aria-label="<?php echo esc_attr($props['labels']['current_page']); ?>"
                    <?php if ($props['schema_markup']): ?>
                    itemprop="name"
                    <?php endif; ?>
                >
                    <?php if ($props['show_icons'] && !empty($item['icon'])): ?>
                    <span class="hph-breadcrumbs__icon" data-icon="<?php echo esc_attr($item['icon']); ?>" aria-hidden="true"></span>
                    <?php endif; ?>
                    <?php echo esc_html($item['text']); ?>
                </span>
                
                <?php if ($props['schema_markup']): ?>
                <meta itemprop="position" content="<?php echo esc_attr($index + 1); ?>">
                <?php endif; ?>
            
            <?php else: ?>
                <a 
                    href="<?php echo esc_url($item['url']); ?>"
                    class="hph-breadcrumbs__link"
                    <?php if ($props['schema_markup']): ?>
                    itemprop="item"
                    <?php endif; ?>
                >
                    <?php if ($props['show_icons'] && !empty($item['icon'])): ?>
                    <span class="hph-breadcrumbs__icon" data-icon="<?php echo esc_attr($item['icon']); ?>" aria-hidden="true"></span>
                    <?php endif; ?>
                    
                    <span 
                        class="hph-breadcrumbs__text"
                        <?php if ($props['schema_markup']): ?>
                        itemprop="name"
                        <?php endif; ?>
                    >
                        <?php echo esc_html($item['text']); ?>
                    </span>
                </a>
                
                <?php if ($props['schema_markup']): ?>
                <meta itemprop="position" content="<?php echo esc_attr($index + 1); ?>">
                <?php endif; ?>
            
            <?php endif; ?>
            
            <!-- Separator -->
            <?php if (!$is_last && !$is_ellipsis): ?>
            <span class="hph-breadcrumbs__separator">
                <?php echo get_separator_content($props['separator'], $props['custom_separator']); ?>
            </span>
            <?php endif; ?>
            
        </li>
        
        <?php endforeach; ?>
        
    </ol>
    
</nav>
