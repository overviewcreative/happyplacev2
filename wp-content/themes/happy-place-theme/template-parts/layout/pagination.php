<?php
/**
 * Base Pagination - Consistent pagination across all archives
 *
 * @package HappyPlaceTheme
 */

// Parse arguments with utility-first approach
$pagination_args = wp_parse_args($args ?? [], [
    'query' => null,
    'current_page' => 1,
    'show_all' => false,
    'end_size' => 2,
    'mid_size' => 2,
    'prev_text' => '<i class="fas fa-chevron-left hph-mr-sm"></i>' . __('Previous', 'happy-place-theme'),
    'next_text' => __('Next', 'happy-place-theme') . '<i class="fas fa-chevron-right hph-ml-sm"></i>',
    'type' => 'list',
    'add_args' => [],
    'screen_reader_text' => __('Posts navigation', 'happy-place-theme'),
    'aria_label' => __('Posts', 'happy-place-theme'),
    'container_classes' => [
        'hph-pagination',
        'hph-flex',
        'hph-flex-col',
        'hph-items-center',
        'hph-space-y-lg',
        'hph-py-xl'
    ]
]);

// Use global query if none provided
if (!$pagination_args['query']) {
    global $wp_query;
    $pagination_args['query'] = $wp_query;
}

$query = $pagination_args['query'];
$max_pages = $query->max_num_pages ?? 1;
$current_page = $pagination_args['current_page'];

// Don't show pagination if only one page
if ($max_pages <= 1) {
    return;
}

// Get current URL parameters to preserve filters
$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$url_parts = parse_url($current_url);
$query_params = [];
if (isset($url_parts['query'])) {
    parse_str($url_parts['query'], $query_params);
}

// Remove paged parameter to avoid conflicts
unset($query_params['paged']);

// Merge with additional args
$query_params = array_merge($query_params, $pagination_args['add_args']);

// Build pagination links
$pagination_links = paginate_links([
    'base' => add_query_arg($query_params, get_pagenum_link(999999999)),
    'format' => '?paged=%#%',
    'current' => max(1, $current_page),
    'total' => $max_pages,
    'show_all' => $pagination_args['show_all'],
    'end_size' => $pagination_args['end_size'],
    'mid_size' => $pagination_args['mid_size'],
    'prev_text' => $pagination_args['prev_text'],
    'next_text' => $pagination_args['next_text'],
    'type' => $pagination_args['type'],
    'add_args' => $query_params,
    'before_page_number' => '<span class="screen-reader-text">' . __('Page', 'happy-place-theme') . ' </span>'
]);

if (!$pagination_links) {
    return;
}
?>

<nav class="<?php echo esc_attr(implode(' ', $pagination_args['container_classes'])); ?>" 
     aria-label="<?php echo esc_attr($pagination_args['aria_label']); ?>"
     role="navigation">
    
    <h2 class="hph-sr-only">
        <?php echo esc_html($pagination_args['screen_reader_text']); ?>
    </h2>
    
    <div class="hph-pagination-links hph-flex hph-items-center hph-space-x-sm">
        <?php 
        // Custom styling for pagination links
        $styled_links = str_replace(
            [
                '<a class="page-numbers"',
                '<span class="page-numbers current"',
                '<a class="next page-numbers"',
                '<a class="prev page-numbers"'
            ],
            [
                '<a class="hph-inline-flex hph-items-center hph-px-md hph-py-sm hph-border hph-border-gray-300 hph-text-sm hph-font-medium hph-text-gray-700 hph-bg-white hph-hover:bg-gray-50 hph-hover:border-gray-400 hph-transition-all hph-duration-200 hph-rounded-md"',
                '<span class="hph-inline-flex hph-items-center hph-px-md hph-py-sm hph-border hph-border-primary-600 hph-text-sm hph-font-medium hph-text-white hph-bg-primary-600 hph-rounded-md"',
                '<a class="hph-inline-flex hph-items-center hph-px-lg hph-py-sm hph-border hph-border-gray-300 hph-text-sm hph-font-medium hph-text-gray-700 hph-bg-white hph-hover:bg-gray-50 hph-hover:border-gray-400 hph-transition-all hph-duration-200 hph-rounded-md"',
                '<a class="hph-inline-flex hph-items-center hph-px-lg hph-py-sm hph-border hph-border-gray-300 hph-text-sm hph-font-medium hph-text-gray-700 hph-bg-white hph-hover:bg-gray-50 hph-hover:border-gray-400 hph-transition-all hph-duration-200 hph-rounded-md"'
            ],
            $pagination_links
        );
        echo $styled_links;
        ?>
    </div>
    
    <div class="hph-flex hph-flex-col sm:hph-flex-row hph-items-center hph-gap-md hph-text-sm hph-text-gray-600">
        <span class="hph-pagination-current hph-font-medium">
            <?php printf(
                __('Page %1$s of %2$s', 'happy-place-theme'),
                '<span class="hph-text-primary-600 hph-font-semibold">' . number_format($current_page) . '</span>',
                '<span class="hph-text-primary-600 hph-font-semibold">' . number_format($max_pages) . '</span>'
            ); ?>
        </span>
        
        <?php if (isset($query->found_posts)) : ?>
            <span class="hph-pagination-total hph-text-gray-500">
                <?php printf(
                    _n('%s item total', '%s items total', $query->found_posts, 'happy-place-theme'),
                    '<span class="hph-font-medium">' . number_format($query->found_posts) . '</span>'
                ); ?>
            </span>
        <?php endif; ?>
    </div>
    
</nav>
