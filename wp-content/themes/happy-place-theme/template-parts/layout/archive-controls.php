<?php
/**
 * Base Archive Controls - Search, filters, view switcher, sort options
 *
 * @package HappyPlaceTheme
 */

$controls_args = wp_parse_args($args ?? [], [
    'view_modes' => ['grid', 'list'],
    'current_view' => 'grid',
    'current_sort' => 'date-desc',
    'per_page' => 12,
    'per_page_options' => [12, 24, 48],
    'show_search_toggle' => true,
    'show_view_switcher' => true,
    'show_sort' => true,
    'show_per_page' => true,
    'show_save_search' => false,
    'container_class' => 'hph-container',
    'post_type' => 'post',
    'sort_options' => [
        'date-desc' => __('Newest First', 'happy-place-theme'),
        'date-asc' => __('Oldest First', 'happy-place-theme'),
        'title-asc' => __('A-Z', 'happy-place-theme'),
        'title-desc' => __('Z-A', 'happy-place-theme')
    ]
]);

// Post-type specific sort options
if ($controls_args['post_type'] === 'listing') {
    $controls_args['sort_options'] = array_merge($controls_args['sort_options'], [
        'price-asc' => __('Price: Low to High', 'happy-place-theme'),
        'price-desc' => __('Price: High to Low', 'happy-place-theme'),
        'sqft-asc' => __('Size: Small to Large', 'happy-place-theme'),
        'sqft-desc' => __('Size: Large to Small', 'happy-place-theme'),
        'featured' => __('Featured First', 'happy-place-theme')
    ]);
}

$view_icons = [
    'grid' => 'fa-th',
    'list' => 'fa-list',
    'map' => 'fa-map-marked-alt',
    'gallery' => 'fa-images'
];
?>

<section class="hph-py-lg hph-bg-white hph-border-b hph-border-gray-200">
    <div class="<?php echo esc_attr($controls_args['container_class']); ?>">
        <div class="hph-flex hph-flex-col sm:hph-flex-row hph-items-center hph-justify-between hph-gap-lg">
            
            <div class="hph-flex hph-items-center hph-gap-md">
                <?php if ($controls_args['show_search_toggle']) : ?>
                    <button class="hph-inline-flex hph-items-center hph-gap-sm hph-px-lg hph-py-md hph-bg-gray-100 hph-text-gray-700 hph-font-medium hph-rounded-lg hph-transition-all hph-duration-200 hph-hover:bg-gray-200 hph-hover:text-gray-900 hph-border hph-border-transparent hph-hover:border-gray-300" 
                            aria-expanded="false"
                            aria-controls="advanced-search">
                        <i class="fas fa-search"></i>
                        <span><?php _e('Advanced Search', 'happy-place-theme'); ?></span>
                        <i class="fas fa-chevron-down hph-transition-transform hph-duration-200"></i>
                    </button>
                <?php endif; ?>
            </div>
            
            <div class="hph-flex hph-items-center hph-gap-lg">
                <?php if ($controls_args['show_view_switcher'] && count($controls_args['view_modes']) > 1) : ?>
                    <div class="hph-flex hph-items-center hph-bg-gray-100 hph-rounded-lg hph-p-1" role="tablist">
                        <?php foreach ($controls_args['view_modes'] as $view_mode) : 
                            $is_active = $view_mode === $controls_args['current_view'];
                            $icon_class = $view_icons[$view_mode] ?? 'fa-th';
                            $view_label = ucfirst($view_mode);
                            
                            $button_classes = [
                                'hph-inline-flex',
                                'hph-items-center',
                                'hph-gap-sm',
                                'hph-px-md',
                                'hph-py-sm',
                                'hph-text-sm',
                                'hph-font-medium',
                                'hph-rounded-md',
                                'hph-transition-all',
                                'hph-duration-200'
                            ];
                            
                            if ($is_active) {
                                $button_classes = array_merge($button_classes, [
                                    'hph-bg-white',
                                    'hph-text-primary-600',
                                    'hph-shadow-sm'
                                ]);
                            } else {
                                $button_classes = array_merge($button_classes, [
                                    'hph-text-gray-600',
                                    'hph-hover:text-gray-900',
                                    'hph-hover:bg-gray-50'
                                ]);
                            }
                        ?>
                            <button class="<?php echo esc_attr(implode(' ', $button_classes)); ?>" 
                                    data-view="<?php echo esc_attr($view_mode); ?>"
                                    role="tab"
                                    aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
                                    title="<?php echo esc_attr($view_label . ' View'); ?>">
                                <i class="fas <?php echo esc_attr($icon_class); ?>"></i>
                                <span class="hph-hidden sm:hph-inline"><?php echo esc_html($view_label); ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            
                <?php if ($controls_args['show_sort']) : ?>
                    <div class="hph-flex hph-items-center hph-gap-sm">
                        <label for="archive-sort" class="hph-text-sm hph-font-medium hph-text-gray-700 hph-hidden sm:hph-inline"><?php _e('Sort by:', 'happy-place-theme'); ?></label>
                        <select id="archive-sort" 
                                class="hph-px-md hph-py-sm hph-border hph-border-gray-300 hph-rounded-lg hph-text-sm hph-bg-white hph-focus:border-primary-500 hph-focus:ring-2 hph-focus:ring-primary-100" 
                                data-current="<?php echo esc_attr($controls_args['current_sort']); ?>">
                            <?php foreach ($controls_args['sort_options'] as $value => $label) : ?>
                                <option value="<?php echo esc_attr($value); ?>" <?php selected($controls_args['current_sort'], $value); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
            
                <?php if ($controls_args['show_per_page']) : ?>
                    <div class="hph-flex hph-items-center hph-gap-sm">
                        <label for="per-page" class="hph-text-sm hph-font-medium hph-text-gray-700 hph-hidden sm:hph-inline"><?php _e('Show:', 'happy-place-theme'); ?></label>
                        <select id="per-page" class="hph-px-md hph-py-sm hph-border hph-border-gray-300 hph-rounded-lg hph-text-sm hph-bg-white hph-focus:border-primary-500 hph-focus:ring-2 hph-focus:ring-primary-100">
                            <?php foreach ($controls_args['per_page_options'] as $option) : ?>
                                <option value="<?php echo esc_attr($option); ?>" <?php selected($controls_args['per_page'], $option); ?>>
                                    <?php echo esc_html($option); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
            
                <?php if ($controls_args['show_save_search'] && is_user_logged_in()) : ?>
                    <button class="hph-inline-flex hph-items-center hph-justify-center hph-w-10 hph-h-10 hph-text-gray-500 hph-hover:text-primary-600 hph-hover:bg-primary-50 hph-rounded-lg hph-transition-all hph-duration-200" 
                            title="<?php esc_attr_e('Save this search', 'happy-place-theme'); ?>"
                            aria-label="<?php esc_attr_e('Save search', 'happy-place-theme'); ?>">
                        <i class="far fa-star"></i>
                    </button>
                <?php endif; ?>
            </div>
            
        </div>
    </div>
</section>

<?php if ($controls_args['show_search_toggle']) : ?>
    <section id="advanced-search" class="hph-hidden hph-bg-gray-50 hph-border-b hph-border-gray-200 hph-animate-fade-in-up" aria-hidden="true">
        <div class="<?php echo esc_attr($controls_args['container_class']); ?>">
            <div class="hph-py-xl">
                <?php 
                // Load post-type specific advanced search form
                $search_template = 'template-parts/forms/advanced-search-' . $controls_args['post_type'];
                if (locate_template($search_template . '.php')) {
                    get_template_part($search_template, null, $controls_args);
                } else {
                    // Fallback to generic advanced search
                    hph_component('advanced-search-form', $controls_args);
                }
                ?>
            </div>
        </div>
    </section>
<?php endif; ?>