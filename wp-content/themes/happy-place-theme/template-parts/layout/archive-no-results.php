<?php
/**
 * Base Archive No Results - No results state with CTAs
 *
 * @package HappyPlaceTheme
 */

$no_results_args = wp_parse_args($args ?? [], [
    'post_type' => 'post',
    'icon' => 'fa-search',
    'title' => '',
    'message' => '',
    'show_actions' => true,
    'show_clear_filters' => true,
    'show_save_search' => false,
    'custom_actions' => [],
    'container_class' => 'hph-container'
]);

// Get search query and check for active filters
$search_query = get_search_query();
$has_filters = !empty($_GET) && array_intersect_key($_GET, array_flip(['status', 'property_type', 'min_price', 'max_price', 'beds', 'baths', 'city', 'specialty', 'languages']));

// Generate contextual content based on post type and state
$post_type_object = get_post_type_object($no_results_args['post_type']);
$post_type_name = $post_type_object ? $post_type_object->labels->name : __('Items', 'happy-place-theme');
$post_type_singular = $post_type_object ? $post_type_object->labels->singular_name : __('Item', 'happy-place-theme');

// Set defaults based on post type
if (empty($no_results_args['title'])) {
    switch ($no_results_args['post_type']) {
        case 'listing':
            $no_results_args['icon'] = 'fa-home';
            $no_results_args['title'] = __('No Properties Found', 'happy-place-theme');
            break;
        case 'agent':
            $no_results_args['icon'] = 'fa-user-tie';
            $no_results_args['title'] = __('No Agents Found', 'happy-place-theme');
            break;
        case 'open_house':
            $no_results_args['icon'] = 'fa-calendar-alt';
            $no_results_args['title'] = __('No Open Houses Found', 'happy-place-theme');
            break;
        default:
            $no_results_args['title'] = sprintf(__('No %s Found', 'happy-place-theme'), $post_type_name);
            break;
    }
}

if (empty($no_results_args['message'])) {
    if (!empty($search_query)) {
        $no_results_args['message'] = sprintf(
            __('Sorry, no %s match your search for "%s".', 'happy-place-theme'),
            strtolower($post_type_name),
            esc_html($search_query)
        );
    } elseif ($has_filters) {
        $no_results_args['message'] = sprintf(
            __('No %s match your current filters. Try adjusting your search criteria.', 'happy-place-theme'),
            strtolower($post_type_name)
        );
    } else {
        $no_results_args['message'] = sprintf(
            __('There are currently no %s available.', 'happy-place-theme'),
            strtolower($post_type_name)
        );
    }
}

// Generate archive link
$archive_link = get_post_type_archive_link($no_results_args['post_type']);
?>

<div class="hph-py-2xl hph-animate-fade-in-up">
    <div class="<?php echo esc_attr($no_results_args['container_class']); ?>">
        <div class="hph-max-w-lg hph-mx-auto hph-text-center hph-space-y-lg">
            
            <div class="hph-w-24 hph-h-24 hph-mx-auto hph-flex hph-items-center hph-justify-center hph-bg-gray-100 hph-rounded-full hph-text-4xl hph-text-gray-400">
                <i class="fas <?php echo esc_attr($no_results_args['icon']); ?>"></i>
            </div>
            
            <div class="hph-space-y-md">
                <h2 class="hph-text-2xl hph-font-bold hph-text-gray-900">
                    <?php echo esc_html($no_results_args['title']); ?>
                </h2>
                
                <p class="hph-text-lg hph-text-gray-600 hph-leading-relaxed">
                    <?php echo esc_html($no_results_args['message']); ?>
                </p>
            </div>
            
            <?php if ($no_results_args['show_actions']) : ?>
                <div class="hph-flex hph-flex-col sm:hph-flex-row hph-items-center hph-justify-center hph-gap-md hph-pt-lg">
                    
                    <?php if ($no_results_args['show_clear_filters'] && ($has_filters || !empty($search_query))) : ?>
                        <a href="<?php echo esc_url($archive_link); ?>" 
                           class="hph-inline-flex hph-items-center hph-gap-sm hph-px-lg hph-py-md hph-bg-primary-600 hph-text-white hph-font-medium hph-rounded-lg hph-transition-all hph-duration-300 hph-hover:bg-primary-700 hph-hover:scale-105">
                            <i class="fas fa-redo"></i> 
                            <?php _e('Clear Filters', 'happy-place-theme'); ?>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($no_results_args['show_save_search'] && is_user_logged_in() && ($has_filters || !empty($search_query))) : ?>
                        <button class="hph-inline-flex hph-items-center hph-gap-sm hph-px-lg hph-py-md hph-border-2 hph-border-primary-600 hph-text-primary-600 hph-font-medium hph-rounded-lg hph-transition-all hph-duration-300 hph-hover:bg-primary-50 hph-hover:scale-105">
                            <i class="fas fa-bell"></i> 
                            <?php _e('Get Alerts for This Search', 'happy-place-theme'); ?>
                        </button>
                    <?php endif; ?>
                    
                    <?php
                    // Post-type specific default actions
                    switch ($no_results_args['post_type']) {
                        case 'listing':
                            ?>
                            <a href="<?php echo esc_url(home_url('/contact')); ?>" 
                               class="hph-inline-flex hph-items-center hph-gap-sm hph-px-lg hph-py-md hph-border-2 hph-border-gray-300 hph-text-gray-700 hph-font-medium hph-rounded-lg hph-transition-all hph-duration-300 hph-hover:bg-gray-50 hph-hover:border-gray-400">
                                <i class="fas fa-envelope"></i>
                                <?php _e('Contact Agent', 'happy-place-theme'); ?>
                            </a>
                            <?php
                            break;
                        case 'agent':
                            ?>
                            <a href="<?php echo esc_url(home_url('/contact')); ?>" 
                               class="hph-inline-flex hph-items-center hph-gap-sm hph-px-lg hph-py-md hph-border-2 hph-border-gray-300 hph-text-gray-700 hph-font-medium hph-rounded-lg hph-transition-all hph-duration-300 hph-hover:bg-gray-50 hph-hover:border-gray-400">
                                <i class="fas fa-phone"></i>
                                <?php _e('Contact Us', 'happy-place-theme'); ?>
                            </a>
                            <?php
                            break;
                        case 'open_house':
                            ?>
                            <a href="<?php echo esc_url(get_post_type_archive_link('listing')); ?>" 
                               class="hph-inline-flex hph-items-center hph-gap-sm hph-px-lg hph-py-md hph-border-2 hph-border-gray-300 hph-text-gray-700 hph-font-medium hph-rounded-lg hph-transition-all hph-duration-300 hph-hover:bg-gray-50 hph-hover:border-gray-400">
                                <i class="fas fa-home"></i>
                                <?php _e('Browse Properties', 'happy-place-theme'); ?>
                            </a>
                            <?php
                            break;
                        default:
                            ?>
                            <a href="<?php echo esc_url(home_url('/')); ?>" 
                               class="hph-inline-flex hph-items-center hph-gap-sm hph-px-lg hph-py-md hph-border-2 hph-border-gray-300 hph-text-gray-700 hph-font-medium hph-rounded-lg hph-transition-all hph-duration-300 hph-hover:bg-gray-50 hph-hover:border-gray-400">
                                <i class="fas fa-home"></i>
                                <?php _e('Go Home', 'happy-place-theme'); ?>
                            </a>
                            <?php
                            break;
                    }
                    ?>
                    
                    <?php
                    // Custom actions
                    if (!empty($no_results_args['custom_actions'])) {
                        foreach ($no_results_args['custom_actions'] as $action) {
                            $action = wp_parse_args($action, [
                                'text' => '',
                                'url' => '#',
                                'icon' => '',
                                'class' => 'hph-btn-secondary',
                                'target' => '_self'
                            ]);
                            ?>
                            <a href="<?php echo esc_url($action['url']); ?>" 
                               class="hph-btn <?php echo esc_attr($action['class']); ?>"
                               target="<?php echo esc_attr($action['target']); ?>">
                                <?php if (!empty($action['icon'])) : ?>
                                    <i class="fas <?php echo esc_attr($action['icon']); ?>"></i>
                                <?php endif; ?>
                                <?php echo esc_html($action['text']); ?>
                            </a>
                            <?php
                        }
                    }
                    ?>
                    
                </div>
            <?php endif; ?>
            
        </div>
    </div>
</div>
