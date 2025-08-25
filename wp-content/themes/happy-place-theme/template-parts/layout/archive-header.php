<?php
/**
 * Base Archive Header - Title, description, counts, breadcrumbs
 *
 * @package HappyPlaceTheme
 */

$header_args = wp_parse_args($args ?? [], [
    'title' => get_the_archive_title(),
    'description' => get_the_archive_description(),
    'total_results' => 0,
    'show_breadcrumbs' => true,
    'show_count' => true,
    'show_description' => true,
    'background' => 'light',
    'text_align' => 'left',
    'container_class' => 'hph-container',
    'custom_title' => '',
    'custom_description' => '',
    'post_type' => 'post'
]);

// Override title/description if custom ones provided
if (!empty($header_args['custom_title'])) {
    $header_args['title'] = $header_args['custom_title'];
}
if (!empty($header_args['custom_description'])) {
    $header_args['description'] = $header_args['custom_description'];
}

// Generate contextual title based on query state
$search_query = get_search_query();
$is_filtered = !empty($_GET) && array_intersect_key($_GET, array_flip(['status', 'property_type', 'min_price', 'max_price', 'beds', 'baths', 'city']));

if (!empty($search_query)) {
    $header_args['title'] = sprintf(__('Search Results for "%s"', 'happy-place-theme'), esc_html($search_query));
} elseif (is_tax()) {
    $term = get_queried_object();
    if ($term) {
        $header_args['title'] = $term->name . ' ' . ucfirst($header_args['post_type']) . 's';
    }
} elseif ($is_filtered) {
    $header_args['title'] = __('Filtered Results', 'happy-place-theme');
}

// Build header classes using utility-first approach
$header_classes = [
    'hph-archive-header',
    'hph-py-2xl',
    'hph-animate-fade-in-up'
];

// Background variations
$background_classes = [
    'light' => ['hph-bg-white', 'hph-border-b', 'hph-border-gray-200'],
    'dark' => ['hph-bg-gray-900', 'hph-text-white'],
    'primary' => ['hph-bg-primary-600', 'hph-text-white'],
    'transparent' => ['hph-bg-transparent']
];

$header_classes = array_merge($header_classes, $background_classes[$header_args['background']] ?? $background_classes['light']);

// Text alignment classes
$alignment_classes = [
    'left' => [],
    'center' => ['hph-text-center'],
    'right' => ['hph-text-right']
];

$header_classes = array_merge($header_classes, $alignment_classes[$header_args['text_align']] ?? []);
?>

<section class="<?php echo esc_attr(implode(' ', $header_classes)); ?>">
    <div class="<?php echo esc_attr($header_args['container_class']); ?>">
        
        <?php if ($header_args['show_breadcrumbs']) : ?>
            <?php hph_component('breadcrumbs', ['post_type' => $header_args['post_type']]); ?>
        <?php endif; ?>
        
        <div class="hph-space-y-lg">
            
            <div class="hph-space-y-md">
                
                <?php if (!empty($header_args['title'])) : ?>
                    <h1 class="hph-text-4xl hph-font-bold hph-text-gray-900 hph-leading-tight">
                        <?php echo wp_kses_post($header_args['title']); ?>
                    </h1>
                <?php endif; ?>
                
                <?php if ($header_args['show_count'] && $header_args['total_results'] >= 0) : ?>
                    <div class="hph-flex hph-items-center hph-gap-sm hph-text-lg">
                        <span class="hph-font-semibold hph-text-primary-600">
                            <?php echo number_format($header_args['total_results']); ?>
                        </span>
                        <span class="hph-text-gray-600">
                            <?php
                            $post_type_object = get_post_type_object($header_args['post_type']);
                            $label = $post_type_object ? $post_type_object->labels->name : __('Items', 'happy-place-theme');
                            
                            if ($header_args['total_results'] === 1) {
                                echo esc_html(rtrim($label, 's')) . ' ' . __('Found', 'happy-place-theme');
                            } else {
                                echo esc_html($label) . ' ' . __('Found', 'happy-place-theme');
                            }
                            ?>
                        </span>
                    </div>
                <?php endif; ?>
                
                <?php if ($header_args['show_description'] && !empty($header_args['description'])) : ?>
                    <div class="hph-text-lg hph-text-gray-600 hph-leading-relaxed hph-max-w-3xl">
                        <?php echo wp_kses_post($header_args['description']); ?>
                    </div>
                <?php endif; ?>
                
            </div>
            
            <?php
            // Allow post-type specific header additions
            $header_template = 'template-parts/archive/header-' . $header_args['post_type'] . '.php';
            if (locate_template($header_template)) {
                get_template_part('template-parts/archive/header', $header_args['post_type'], $header_args);
            }
            ?>
            
        </div>
        
    </div>
</section>