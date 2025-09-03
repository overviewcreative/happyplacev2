<?php
/**
 * Base Pagination Component
 * 
 * Pure UI pagination component for page navigation
 * No data dependencies, just presentation
 * 
 * @package HappyPlaceTheme
 * @subpackage Components/Base
 * @since 3.0.0
 */

$props = wp_parse_args(hph_get_arg(), array(
    // Pagination data
    'current_page' => 1,
    'total_pages' => 1,
    'base_url' => '',
    'query_params' => array(), // Additional query parameters to preserve
    
    // Appearance
    'variant' => 'default', // default, minimal, compact, numbered
    'size' => 'md', // sm, md, lg
    'alignment' => 'center', // left, center, right
    
    // Behavior
    'show_first_last' => true, // Show first/last page links
    'show_prev_next' => true, // Show previous/next links
    'show_page_info' => false, // Show "Page X of Y" text
    'show_total_items' => false, // Show total items count
    'max_visible_pages' => 7, // Maximum page numbers to show
    'total_items' => 0, // Total number of items (for info display)
    'items_per_page' => 10, // Items per page (for info display)
    
    // Advanced
    'ajax' => false, // Use AJAX for pagination
    'infinite_scroll' => false, // Enable infinite scroll
    'keyboard_nav' => true, // Keyboard navigation support
    
    // Labels
    'labels' => array(
        'first' => __('First', 'happy-place-theme'),
        'previous' => __('Previous', 'happy-place-theme'),
        'next' => __('Next', 'happy-place-theme'),
        'last' => __('Last', 'happy-place-theme'),
        'page_info' => __('Page %1$s of %2$s', 'happy-place-theme'),
        'total_items' => __('%s total items', 'happy-place-theme'),
        'go_to_page' => __('Go to page %s', 'happy-place-theme'),
        'current_page' => __('Current page, page %s', 'happy-place-theme')
    ),
    
    // HTML
    'id' => '',
    'class' => '',
    'attributes' => array(),
    'data' => array()
));

// Validate pagination data
if ($props['total_pages'] <= 1) {
    return; // No pagination needed
}

$current_page = max(1, (int) $props['current_page']);
$total_pages = max(1, (int) $props['total_pages']);

// Generate unique ID if not provided
if (empty($props['id'])) {
    $props['id'] = 'hph-pagination-' . uniqid();
}

// Build pagination classes
$pagination_classes = array(
    'hph-pagination',
    'hph-pagination--' . $props['variant'],
    'hph-pagination--' . $props['size'],
    'hph-pagination--' . $props['alignment']
);

if ($props['ajax']) {
    $pagination_classes[] = 'hph-pagination--ajax';
}

if ($props['infinite_scroll']) {
    $pagination_classes[] = 'hph-pagination--infinite';
}

if (!empty($props['class'])) {
    $pagination_classes[] = $props['class'];
}

// Prepare data attributes
$data_attrs = array_merge(array(
    'current-page' => $current_page,
    'total-pages' => $total_pages,
    'ajax' => $props['ajax'] ? 'true' : 'false',
    'keyboard-nav' => $props['keyboard_nav'] ? 'true' : 'false'
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

// Calculate visible page range
$max_visible = $props['max_visible_pages'];
$half_visible = floor($max_visible / 2);

$start_page = max(1, $current_page - $half_visible);
$end_page = min($total_pages, $current_page + $half_visible);

// Adjust range if we're near the beginning or end
if ($end_page - $start_page + 1 < $max_visible) {
    if ($start_page === 1) {
        $end_page = min($total_pages, $start_page + $max_visible - 1);
    } else {
        $start_page = max(1, $end_page - $max_visible + 1);
    }
}

// Helper function to build URL
function build_pagination_url($page, $base_url, $query_params) {
    $url = $base_url;
    $params = array_merge($query_params, array('paged' => $page));
    
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    
    return $url;
}
?>

<nav 
    id="<?php echo esc_attr($props['id']); ?>"
    class="<?php echo esc_attr(implode(' ', $pagination_classes)); ?>"
    <?php echo $attributes_string; ?>
    role="navigation"
    aria-label="<?php esc_attr_e('Pagination Navigation', 'happy-place-theme'); ?>"
>
    
    <?php if ($props['show_page_info'] || $props['show_total_items']): ?>
    <div class="hph-pagination__info">
        <?php if ($props['show_page_info']): ?>
        <span class="hph-pagination__page-info">
            <?php echo sprintf(esc_html($props['labels']['page_info']), $current_page, $total_pages); ?>
        </span>
        <?php endif; ?>
        
        <?php if ($props['show_total_items'] && $props['total_items'] > 0): ?>
        <span class="hph-pagination__total-items">
            <?php echo sprintf(esc_html($props['labels']['total_items']), number_format_i18n($props['total_items'])); ?>
        </span>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <ul class="hph-pagination__list">
        
        <!-- First Page -->
        <?php if ($props['show_first_last'] && $current_page > 1): ?>
        <li class="hph-pagination__item hph-pagination__item--first">
            <a 
                href="<?php echo esc_url(build_pagination_url(1, $props['base_url'], $props['query_params'])); ?>"
                class="hph-pagination__link"
                aria-label="<?php echo esc_attr($props['labels']['first']); ?>"
                <?php if ($props['ajax']): ?>data-page="1"<?php endif; ?>
            >
                <span data-icon="chevrons-left" aria-hidden="true"></span>
                <span class="hph-pagination__text"><?php echo esc_html($props['labels']['first']); ?></span>
            </a>
        </li>
        <?php endif; ?>
        
        <!-- Previous Page -->
        <?php if ($props['show_prev_next'] && $current_page > 1): ?>
        <li class="hph-pagination__item hph-pagination__item--prev">
            <a 
                href="<?php echo esc_url(build_pagination_url($current_page - 1, $props['base_url'], $props['query_params'])); ?>"
                class="hph-pagination__link"
                aria-label="<?php echo esc_attr($props['labels']['previous']); ?>"
                <?php if ($props['ajax']): ?>data-page="<?php echo $current_page - 1; ?>"<?php endif; ?>
            >
                <span data-icon="chevron-left" aria-hidden="true"></span>
                <span class="hph-pagination__text"><?php echo esc_html($props['labels']['previous']); ?></span>
            </a>
        </li>
        <?php endif; ?>
        
        <!-- Page Numbers -->
        <?php if ($start_page > 1): ?>
        <li class="hph-pagination__item hph-pagination__item--ellipsis">
            <span class="hph-pagination__ellipsis" aria-hidden="true">…</span>
        </li>
        <?php endif; ?>
        
        <?php for ($page = $start_page; $page <= $end_page; $page++): ?>
        <li class="hph-pagination__item hph-pagination__item--number <?php echo $page === $current_page ? 'hph-pagination__item--current' : ''; ?>">
            <?php if ($page === $current_page): ?>
            <span 
                class="hph-pagination__link hph-pagination__link--current"
                aria-current="page"
                aria-label="<?php echo esc_attr(sprintf($props['labels']['current_page'], $page)); ?>"
            >
                <?php echo esc_html($page); ?>
            </span>
            <?php else: ?>
            <a 
                href="<?php echo esc_url(build_pagination_url($page, $props['base_url'], $props['query_params'])); ?>"
                class="hph-pagination__link"
                aria-label="<?php echo esc_attr(sprintf($props['labels']['go_to_page'], $page)); ?>"
                <?php if ($props['ajax']): ?>data-page="<?php echo $page; ?>"<?php endif; ?>
            >
                <?php echo esc_html($page); ?>
            </a>
            <?php endif; ?>
        </li>
        <?php endfor; ?>
        
        <?php if ($end_page < $total_pages): ?>
        <li class="hph-pagination__item hph-pagination__item--ellipsis">
            <span class="hph-pagination__ellipsis" aria-hidden="true">…</span>
        </li>
        <?php endif; ?>
        
        <!-- Next Page -->
        <?php if ($props['show_prev_next'] && $current_page < $total_pages): ?>
        <li class="hph-pagination__item hph-pagination__item--next">
            <a 
                href="<?php echo esc_url(build_pagination_url($current_page + 1, $props['base_url'], $props['query_params'])); ?>"
                class="hph-pagination__link"
                aria-label="<?php echo esc_attr($props['labels']['next']); ?>"
                <?php if ($props['ajax']): ?>data-page="<?php echo $current_page + 1; ?>"<?php endif; ?>
            >
                <span class="hph-pagination__text"><?php echo esc_html($props['labels']['next']); ?></span>
                <span data-icon="chevron-right" aria-hidden="true"></span>
            </a>
        </li>
        <?php endif; ?>
        
        <!-- Last Page -->
        <?php if ($props['show_first_last'] && $current_page < $total_pages): ?>
        <li class="hph-pagination__item hph-pagination__item--last">
            <a 
                href="<?php echo esc_url(build_pagination_url($total_pages, $props['base_url'], $props['query_params'])); ?>"
                class="hph-pagination__link"
                aria-label="<?php echo esc_attr($props['labels']['last']); ?>"
                <?php if ($props['ajax']): ?>data-page="<?php echo $total_pages; ?>"<?php endif; ?>
            >
                <span class="hph-pagination__text"><?php echo esc_html($props['labels']['last']); ?></span>
                <span data-icon="chevrons-right" aria-hidden="true"></span>
            </a>
        </li>
        <?php endif; ?>
        
    </ul>
    
</nav>
