<?php
/**
 * Archive Sidebar Component - Contextual Sidebar for Archive Pages
 * 
 * Dynamic sidebar component that displays relevant widgets and information
 * based on the current archive context (listings, agents, posts, etc.)
 * 
 * @package HappyPlaceTheme
 * @version 3.0.0
 * 
 * === Configuration Options ===
 * 
 * Core Settings:
 * - post_type: string - Current post type
 * - position: string - Sidebar position (left/right)
 * - sticky: bool - Make sidebar sticky on scroll
 * 
 * Widget Areas:
 * - widgets: array - Array of widget configurations to display
 * - show_dynamic_widgets: bool - Show post-type specific widgets
 * - show_search: bool - Display search widget
 * - show_categories: bool - Display categories widget
 * - show_recent: bool - Display recent posts widget
 * - show_featured: bool - Display featured items widget
 * - show_cta: bool - Display call-to-action widget
 * 
 * Style Options:
 * - background: string - Widget background style
 * - spacing: string - Space between widgets
 * - widget_padding: string - Padding inside widgets
 * - show_borders: bool - Show widget borders
 * 
 * Advanced Features:
 * - collapsible_widgets: bool - Make widgets collapsible
 * - ajax_load: bool - Load widget content via AJAX
 * - cache_widgets: bool - Cache widget output
 */

// Parse arguments with defaults
$args = wp_parse_args($args ?? [], [
    // Core Settings
    'post_type' => get_post_type() ?: 'post',
    'position' => 'right',
    'sticky' => false,
    
    // Widget Areas
    'widgets' => [],
    'show_dynamic_widgets' => true,
    'show_search' => true,
    'show_categories' => true,
    'show_recent' => true,
    'show_featured' => true,
    'show_cta' => true,
    
    // Style Options
    'background' => 'white',
    'spacing' => 'lg',
    'widget_padding' => 'lg',
    'show_borders' => true,
    
    // Advanced Features
    'collapsible_widgets' => false,
    'ajax_load' => false,
    'cache_widgets' => false,
]);

// Build container classes
$container_classes = [
    'hph-archive-sidebar',
    'hph-archive-sidebar--' . $args['position']
];

if ($args['sticky']) {
    $container_classes[] = 'hph-sticky';
    $container_classes[] = 'hph-top-8';
    $container_classes[] = 'hph-max-h-screen';
    $container_classes[] = 'hph-overflow-y-auto';
}

// Define dynamic widgets based on post type
if ($args['show_dynamic_widgets'] && empty($args['widgets'])) {
    switch ($args['post_type']) {
        case 'listing':
            $args['widgets'] = [
                'quick_search',
                'price_range',
                'property_types',
                'featured_listings',
                'mortgage_calculator',
                'agent_contact',
                'newsletter'
            ];
            break;
            
        case 'agent':
            $args['widgets'] = [
                'agent_search',
                'featured_agents',
                'testimonials',
                'contact_form',
                'office_locations',
                'newsletter'
            ];
            break;
            
        default:
            $args['widgets'] = [
                'search',
                'categories',
                'recent_posts',
                'tags',
                'newsletter'
            ];
    }
}

?>

<aside class="<?php echo esc_attr(implode(' ', $container_classes)); ?>" role="complementary" aria-label="<?php esc_attr_e('Sidebar', 'happy-place-theme'); ?>">
    <div class="hph-space-y-<?php echo esc_attr($args['spacing']); ?>">
        
        <?php foreach ($args['widgets'] as $widget) : ?>
            <?php
            // Build widget classes
            $widget_classes = ['hph-sidebar-widget'];
            
            if ($args['background'] === 'white') {
                $widget_classes[] = 'hph-bg-white';
                $widget_classes[] = 'hph-rounded-lg';
                if ($args['show_borders']) {
                    $widget_classes[] = 'hph-border';
                    $widget_classes[] = 'hph-border-gray-200';
                }
                $widget_classes[] = 'hph-shadow-sm';
            }
            
            $widget_classes[] = 'hph-p-' . $args['widget_padding'];
            ?>
            
            <?php switch ($widget) :
                
                case 'quick_search':
                case 'agent_search':
                case 'search': ?>
                    <?php if ($args['show_search']) : ?>
                    <div class="<?php echo esc_attr(implode(' ', $widget_classes)); ?>">
                        <h3 class="hph-text-lg hph-font-semibold hph-mb-4"><?php esc_html_e('Search', 'happy-place-theme'); ?></h3>
                        <form action="<?php echo esc_url(home_url('/')); ?>" method="get" class="hph-sidebar-search">
                            <?php if ($args['post_type'] !== 'post') : ?>
                                <input type="hidden" name="post_type" value="<?php echo esc_attr($args['post_type']); ?>">
                            <?php endif; ?>
                            <div class="hph-relative">
                                <input 
                                    type="search" 
                                    name="s" 
                                    placeholder="<?php esc_attr_e('Search...', 'happy-place-theme'); ?>"
                                    class="hph-form-control hph-w-full hph-pr-10"
                                    value="<?php echo get_search_query(); ?>"
                                >
                                <button type="submit" class="hph-absolute hph-right-2 hph-top-1/2 hph-transform hph--translate-y-1/2 hph-text-gray-400 hover:hph-text-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>
                    <?php break;
                    
                case 'price_range': ?>
                    <?php if ($args['post_type'] === 'listing') : ?>
                    <div class="<?php echo esc_attr(implode(' ', $widget_classes)); ?>">
                        <h3 class="hph-text-lg hph-font-semibold hph-mb-4"><?php esc_html_e('Price Range', 'happy-place-theme'); ?></h3>
                        <form class="hph-price-filter">
                            <div class="hph-space-y-3">
                                <div>
                                    <label class="hph-text-sm hph-text-gray-600"><?php esc_html_e('Min Price', 'happy-place-theme'); ?></label>
                                    <select class="hph-form-select hph-w-full hph-mt-1">
                                        <option value=""><?php esc_html_e('No Min', 'happy-place-theme'); ?></option>
                                        <?php
                                        $prices = [50000, 100000, 150000, 200000, 250000, 300000, 400000, 500000, 750000, 1000000];
                                        foreach ($prices as $price) {
                                            echo '<option value="' . $price . '">$' . number_format($price) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="hph-text-sm hph-text-gray-600"><?php esc_html_e('Max Price', 'happy-place-theme'); ?></label>
                                    <select class="hph-form-select hph-w-full hph-mt-1">
                                        <option value=""><?php esc_html_e('No Max', 'happy-place-theme'); ?></option>
                                        <?php
                                        foreach ($prices as $price) {
                                            echo '<option value="' . $price . '">$' . number_format($price) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <button type="submit" class="hph-btn hph-btn-primary hph-w-full">
                                    <?php esc_html_e('Update Results', 'happy-place-theme'); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>
                    <?php break;
                    
                case 'property_types': ?>
                    <?php if ($args['post_type'] === 'listing') : ?>
                    <div class="<?php echo esc_attr(implode(' ', $widget_classes)); ?>">
                        <h3 class="hph-text-lg hph-font-semibold hph-mb-4"><?php esc_html_e('Property Types', 'happy-place-theme'); ?></h3>
                        <div class="hph-space-y-2">
                            <?php
                            $property_types = [
                                'single-family' => __('Single Family', 'happy-place-theme'),
                                'condo' => __('Condo', 'happy-place-theme'),
                                'townhouse' => __('Townhouse', 'happy-place-theme'),
                                'multi-family' => __('Multi-Family', 'happy-place-theme'),
                                'land' => __('Land', 'happy-place-theme'),
                            ];
                            foreach ($property_types as $value => $label) :
                            ?>
                                <label class="hph-flex hph-items-center hph-cursor-pointer">
                                    <input type="checkbox" class="hph-form-checkbox hph-mr-2" value="<?php echo esc_attr($value); ?>">
                                    <span class="hph-text-sm"><?php echo esc_html($label); ?></span>
                                    <span class="hph-ml-auto hph-text-xs hph-text-gray-500">(<?php echo rand(5, 25); ?>)</span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php break;
                    
                case 'categories': ?>
                    <?php if ($args['show_categories']) : ?>
                    <?php
                    $categories = get_categories([
                        'orderby' => 'name',
                        'hide_empty' => true,
                        'number' => 10
                    ]);
                    if (!empty($categories)) :
                    ?>
                    <div class="<?php echo esc_attr(implode(' ', $widget_classes)); ?>">
                        <h3 class="hph-text-lg hph-font-semibold hph-mb-4"><?php esc_html_e('Categories', 'happy-place-theme'); ?></h3>
                        <ul class="hph-space-y-2">
                            <?php foreach ($categories as $category) : ?>
                                <li>
                                    <a href="<?php echo esc_url(get_category_link($category)); ?>" class="hph-flex hph-items-center hph-justify-between hph-text-sm hph-text-gray-700 hover:hph-text-primary hph-transition">
                                        <span><?php echo esc_html($category->name); ?></span>
                                        <span class="hph-text-xs hph-text-gray-500">(<?php echo esc_html($category->count); ?>)</span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                    <?php break;
                    
                case 'featured_listings':
                case 'featured_agents':
                case 'recent_posts': ?>
                    <?php if ($args['show_recent'] || $args['show_featured']) : ?>
                    <?php
                    $query_args = [
                        'post_type' => $args['post_type'],
                        'posts_per_page' => 3,
                        'orderby' => 'date',
                        'order' => 'DESC'
                    ];
                    
                    if (strpos($widget, 'featured') !== false) {
                        $query_args['meta_key'] = 'featured';
                        $query_args['meta_value'] = '1';
                    }
                    
                    $recent_query = new WP_Query($query_args);
                    if ($recent_query->have_posts()) :
                    ?>
                    <div class="<?php echo esc_attr(implode(' ', $widget_classes)); ?>">
                        <h3 class="hph-text-lg hph-font-semibold hph-mb-4">
                            <?php
                            if (strpos($widget, 'featured') !== false) {
                                esc_html_e('Featured', 'happy-place-theme');
                            } else {
                                esc_html_e('Recent', 'happy-place-theme');
                            }
                            ?>
                        </h3>
                        <div class="hph-space-y-4">
                            <?php while ($recent_query->have_posts()) : $recent_query->the_post(); ?>
                                <article class="hph-flex hph-gap-3">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <div class="hph-flex-shrink-0">
                                            <a href="<?php the_permalink(); ?>" class="hph-block hph-w-20 hph-h-20 hph-rounded-lg hph-overflow-hidden">
                                                <?php the_post_thumbnail('thumbnail', ['class' => 'hph-w-full hph-h-full hph-object-cover']); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <div class="hph-flex-1 hph-min-w-0">
                                        <h4 class="hph-text-sm hph-font-medium hph-mb-1">
                                            <a href="<?php the_permalink(); ?>" class="hph-text-gray-900 hover:hph-text-primary hph-transition">
                                                <?php the_title(); ?>
                                            </a>
                                        </h4>
                                        <?php if ($args['post_type'] === 'listing' && function_exists('hpt_get_listing_price')) : ?>
                                            <p class="hph-text-sm hph-font-semibold hph-text-primary">
                                                <?php echo hpt_get_listing_price(); ?>
                                            </p>
                                        <?php else : ?>
                                            <p class="hph-text-xs hph-text-gray-500">
                                                <?php echo get_the_date(); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    <?php
                    endif;
                    wp_reset_postdata();
                    ?>
                    <?php endif; ?>
                    <?php break;
                    
                case 'mortgage_calculator': ?>
                    <?php if ($args['post_type'] === 'listing') : ?>
                    <div class="<?php echo esc_attr(implode(' ', $widget_classes)); ?>">
                        <h3 class="hph-text-lg hph-font-semibold hph-mb-4"><?php esc_html_e('Mortgage Calculator', 'happy-place-theme'); ?></h3>
                        <div class="hph-space-y-3">
                            <div>
                                <label class="hph-text-sm hph-text-gray-600"><?php esc_html_e('Home Price', 'happy-place-theme'); ?></label>
                                <input type="number" class="hph-form-control hph-w-full hph-mt-1" placeholder="$400,000">
                            </div>
                            <div>
                                <label class="hph-text-sm hph-text-gray-600"><?php esc_html_e('Down Payment', 'happy-place-theme'); ?></label>
                                <input type="number" class="hph-form-control hph-w-full hph-mt-1" placeholder="20%">
                            </div>
                            <div>
                                <label class="hph-text-sm hph-text-gray-600"><?php esc_html_e('Interest Rate', 'happy-place-theme'); ?></label>
                                <input type="number" class="hph-form-control hph-w-full hph-mt-1" placeholder="6.5%">
                            </div>
                            <button class="hph-btn hph-btn-primary hph-w-full">
                                <?php esc_html_e('Calculate Payment', 'happy-place-theme'); ?>
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php break;
                    
                case 'agent_contact':
                case 'contact_form': ?>
                    <?php if ($args['show_cta']) : ?>
                    <div class="<?php echo esc_attr(implode(' ', array_merge($widget_classes, ['hph-bg-primary-50', 'hph-border-primary-200']))); ?>">
                        <h3 class="hph-text-lg hph-font-semibold hph-mb-4"><?php esc_html_e('Need Help?', 'happy-place-theme'); ?></h3>
                        <p class="hph-text-sm hph-text-gray-700 hph-mb-4">
                            <?php esc_html_e('Our expert team is ready to assist you with your real estate needs.', 'happy-place-theme'); ?>
                        </p>
                        <div class="hph-space-y-3">
                            <a href="tel:302-555-0100" class="hph-btn hph-btn-primary hph-w-full">
                                <i class="fas fa-phone"></i>
                                <span><?php esc_html_e('Call Now', 'happy-place-theme'); ?></span>
                            </a>
                            <a href="<?php echo esc_url(home_url('/contact')); ?>" class="hph-btn hph-btn-outline hph-w-full">
                                <i class="fas fa-envelope"></i>
                                <span><?php esc_html_e('Send Message', 'happy-place-theme'); ?></span>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php break;
                    
                case 'newsletter': ?>
                    <div class="<?php echo esc_attr(implode(' ', $widget_classes)); ?>">
                        <h3 class="hph-text-lg hph-font-semibold hph-mb-4"><?php esc_html_e('Newsletter', 'happy-place-theme'); ?></h3>
                        <p class="hph-text-sm hph-text-gray-600 hph-mb-4">
                            <?php esc_html_e('Get the latest listings and market updates delivered to your inbox.', 'happy-place-theme'); ?>
                        </p>
                        <form class="hph-newsletter-form">
                            <div class="hph-space-y-3">
                                <input 
                                    type="email" 
                                    placeholder="<?php esc_attr_e('Your email', 'happy-place-theme'); ?>"
                                    class="hph-form-control hph-w-full"
                                    required
                                >
                                <button type="submit" class="hph-btn hph-btn-primary hph-w-full">
                                    <?php esc_html_e('Subscribe', 'happy-place-theme'); ?>
                                </button>
                            </div>
                            <p class="hph-text-xs hph-text-gray-500 hph-mt-3">
                                <?php esc_html_e('We respect your privacy. Unsubscribe at any time.', 'happy-place-theme'); ?>
                            </p>
                        </form>
                    </div>
                    <?php break;
                    
                case 'testimonials': ?>
                    <?php if ($args['post_type'] === 'agent') : ?>
                    <div class="<?php echo esc_attr(implode(' ', $widget_classes)); ?>">
                        <h3 class="hph-text-lg hph-font-semibold hph-mb-4"><?php esc_html_e('Client Reviews', 'happy-place-theme'); ?></h3>
                        <div class="hph-space-y-4">
                            <blockquote class="hph-border-l-4 hph-border-primary hph-pl-4">
                                <p class="hph-text-sm hph-italic hph-text-gray-700 hph-mb-2">
                                    "Outstanding service! Made our home buying process smooth and stress-free."
                                </p>
                                <footer class="hph-text-xs hph-text-gray-600">
                                    <strong>Sarah M.</strong> - <?php esc_html_e('Home Buyer', 'happy-place-theme'); ?>
                                </footer>
                            </blockquote>
                            <div class="hph-flex hph-items-center hph-justify-center">
                                <div class="hph-flex hph-gap-1">
                                    <?php for ($i = 0; $i < 5; $i++) : ?>
                                        <i class="fas fa-star hph-text-warning"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="hph-ml-2 hph-text-sm hph-font-semibold">5.0</span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php break;
                    
                case 'tags': ?>
                    <?php
                    $tags = get_tags([
                        'orderby' => 'count',
                        'order' => 'DESC',
                        'number' => 15
                    ]);
                    if (!empty($tags)) :
                    ?>
                    <div class="<?php echo esc_attr(implode(' ', $widget_classes)); ?>">
                        <h3 class="hph-text-lg hph-font-semibold hph-mb-4"><?php esc_html_e('Popular Tags', 'happy-place-theme'); ?></h3>
                        <div class="hph-flex hph-flex-wrap hph-gap-2">
                            <?php foreach ($tags as $tag) : ?>
                                <a href="<?php echo esc_url(get_tag_link($tag)); ?>" class="hph-inline-block hph-px-3 hph-py-1 hph-bg-gray-100 hph-text-sm hph-text-gray-700 hph-rounded-full hover:hph-bg-primary hover:hph-text-white hph-transition">
                                    <?php echo esc_html($tag->name); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php break;
                    
                case 'office_locations': ?>
                    <?php if ($args['post_type'] === 'agent') : ?>
                    <div class="<?php echo esc_attr(implode(' ', $widget_classes)); ?>">
                        <h3 class="hph-text-lg hph-font-semibold hph-mb-4"><?php esc_html_e('Our Offices', 'happy-place-theme'); ?></h3>
                        <div class="hph-space-y-3">
                            <div class="hph-pb-3 hph-border-b hph-border-gray-200">
                                <h4 class="hph-font-medium hph-text-sm hph-mb-1"><?php esc_html_e('Wilmington Office', 'happy-place-theme'); ?></h4>
                                <p class="hph-text-xs hph-text-gray-600">123 Main St, Wilmington, DE 19801</p>
                                <a href="tel:302-555-0100" class="hph-text-xs hph-text-primary hover:hph-underline">(302) 555-0100</a>
                            </div>
                            <div>
                                <h4 class="hph-font-medium hph-text-sm hph-mb-1"><?php esc_html_e('Newark Office', 'happy-place-theme'); ?></h4>
                                <p class="hph-text-xs hph-text-gray-600">456 College Ave, Newark, DE 19711</p>
                                <a href="tel:302-555-0200" class="hph-text-xs hph-text-primary hover:hph-underline">(302) 555-0200</a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php break;
                    
            endswitch; ?>
            
        <?php endforeach; ?>
        
        <?php
        // Display WordPress widget area if it exists
        $sidebar_id = 'archive-' . $args['post_type'];
        if (is_active_sidebar($sidebar_id)) :
        ?>
            <div class="hph-wordpress-widgets">
                <?php dynamic_sidebar($sidebar_id); ?>
            </div>
        <?php endif; ?>
        
    </div>
</aside>

<?php if ($args['collapsible_widgets']) : ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const widgets = document.querySelectorAll('.hph-sidebar-widget');
    widgets.forEach(widget => {
        const title = widget.querySelector('h3');
        if (title) {
            title.classList.add('hph-cursor-pointer', 'hph-select-none');
            title.innerHTML += '<i class="fas fa-chevron-down hph-ml-2 hph-text-xs hph-transition-transform"></i>';
            
            title.addEventListener('click', function() {
                const content = widget.querySelector('h3 ~ *');
                if (content) {
                    content.classList.toggle('hph-hidden');
                    const icon = title.querySelector('i');
                    icon.classList.toggle('hph-rotate-180');
                }
            });
        }
    });
});
</script>
<?php endif; ?>