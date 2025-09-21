<?php
/**
 * Universal Calendar Implementation Examples
 *
 * This file demonstrates how to use the universal calendar component
 * in different contexts and with various configurations.
 *
 * @package HappyPlaceTheme
 * @version 1.0.0
 */

/*
 * BASIC USAGE EXAMPLES
 * ===================
 */

// Example 1: Basic Event Calendar (Month View)
function example_basic_event_calendar() {
    get_template_part('template-parts/components/universal-calendar', '', array(
        'view' => 'month',
        'post_type' => 'event',
        'date_field' => 'event_date',
        'time_field' => 'event_time',
        'location_field' => 'event_location',
        'category_field' => 'event_category'
    ));
}

// Example 2: Real Estate Open Houses Calendar
function example_open_houses_calendar() {
    get_template_part('template-parts/components/universal-calendar', '', array(
        'view' => 'month',
        'post_type' => 'listing',
        'date_field' => 'open_house_date',
        'time_field' => 'open_house_time',
        'location_field' => 'address',
        'category_field' => 'listing_type',
        'css_classes' => array('calendar-open-houses'),
        'show_view_selector' => true
    ));
}

// Example 3: Blog Posts Calendar (List View)
function example_blog_calendar() {
    get_template_part('template-parts/components/universal-calendar', '', array(
        'view' => 'list',
        'post_type' => 'blog_post',
        'date_field' => 'publish_date',
        'category_field' => 'blog_category',
        'posts_per_page' => 6,
        'css_classes' => array('calendar-blog-posts')
    ));
}

// Example 4: Filtered Events Calendar
function example_filtered_events_calendar() {
    // Get specific event categories
    $featured_categories = get_terms(array(
        'taxonomy' => 'event_category',
        'meta_query' => array(
            array(
                'key' => 'is_featured',
                'value' => '1'
            )
        )
    ));
    
    $category_ids = wp_list_pluck($featured_categories, 'term_id');
    
    get_template_part('template-parts/components/universal-calendar', '', array(
        'view' => 'grid',
        'post_type' => 'event',
        'categories' => $category_ids,
        'css_classes' => array('calendar-featured-events'),
        'show_navigation' => true,
        'show_view_selector' => false
    ));
}

/*
 * ADVANCED USAGE EXAMPLES
 * ======================
 */

// Example 5: Multi-Post Type Calendar
function example_multi_post_type_calendar() {
    // This would require extending the component to handle multiple post types
    // For now, you could create multiple calendars and combine them with JavaScript
    
    echo '<div class="hph-multi-calendar-container">';
    
    // Events
    get_template_part('template-parts/components/universal-calendar', '', array(
        'view' => 'month',
        'post_type' => 'event',
        'css_classes' => array('calendar-events'),
        'calendar_id' => 'calendar-events'
    ));
    
    // Open Houses (hidden by default, toggled with JavaScript)
    get_template_part('template-parts/components/universal-calendar', '', array(
        'view' => 'month',
        'post_type' => 'listing',
        'date_field' => 'open_house_date',
        'css_classes' => array('calendar-open-houses', 'hidden'),
        'calendar_id' => 'calendar-open-houses'
    ));
    
    echo '</div>';
}

// Example 6: Calendar with Custom Styling
function example_custom_styled_calendar() {
    get_template_part('template-parts/components/universal-calendar', '', array(
        'view' => 'month',
        'post_type' => 'event',
        'css_classes' => array('calendar-dark-theme', 'calendar-large'),
        'current_date' => '2024-06-01' // Start from specific date
    ));
}

/*
 * SHORTCODE IMPLEMENTATION
 * =======================
 */

// Example 7: Calendar Shortcode
function hph_calendar_shortcode($atts) {
    $atts = shortcode_atts(array(
        'view' => 'month',
        'post_type' => 'event',
        'categories' => '',
        'date' => '',
        'show_navigation' => 'true',
        'show_view_selector' => 'true',
        'class' => ''
    ), $atts);
    
    // Parse categories
    $categories = !empty($atts['categories']) ? explode(',', $atts['categories']) : array();
    $categories = array_map('trim', $categories);
    $categories = array_map('intval', $categories);
    
    // Parse CSS classes
    $css_classes = !empty($atts['class']) ? explode(' ', $atts['class']) : array();
    
    // Parse boolean attributes
    $show_navigation = filter_var($atts['show_navigation'], FILTER_VALIDATE_BOOLEAN);
    $show_view_selector = filter_var($atts['show_view_selector'], FILTER_VALIDATE_BOOLEAN);
    
    // Start output buffering
    ob_start();
    
    get_template_part('template-parts/components/universal-calendar', '', array(
        'view' => sanitize_key($atts['view']),
        'post_type' => sanitize_key($atts['post_type']),
        'categories' => $categories,
        'current_date' => !empty($atts['date']) ? sanitize_text_field($atts['date']) : date('Y-m-d'),
        'show_navigation' => $show_navigation,
        'show_view_selector' => $show_view_selector,
        'css_classes' => $css_classes
    ));
    
    return ob_get_clean();
}
add_shortcode('hph_calendar', 'hph_calendar_shortcode');

/*
 * WIDGET IMPLEMENTATION
 * ====================
 */

// Example 8: Calendar Widget
class HpH_Calendar_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'hph_calendar_widget',
            __('HpH Calendar', 'happy-place-theme'),
            array('description' => __('Display a calendar of events', 'happy-place-theme'))
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        get_template_part('template-parts/components/universal-calendar', '', array(
            'view' => !empty($instance['view']) ? $instance['view'] : 'month',
            'post_type' => !empty($instance['post_type']) ? $instance['post_type'] : 'event',
            'show_navigation' => !empty($instance['show_navigation']),
            'show_view_selector' => false, // Usually disabled in widgets
            'css_classes' => array('widget-calendar')
        ));
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Events Calendar', 'happy-place-theme');
        $view = !empty($instance['view']) ? $instance['view'] : 'month';
        $post_type = !empty($instance['post_type']) ? $instance['post_type'] : 'event';
        $show_navigation = !empty($instance['show_navigation']);
        
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title:', 'happy-place-theme'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('view')); ?>"><?php _e('View:', 'happy-place-theme'); ?></label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('view')); ?>" name="<?php echo esc_attr($this->get_field_name('view')); ?>">
                <option value="month" <?php selected($view, 'month'); ?>><?php _e('Month', 'happy-place-theme'); ?></option>
                <option value="list" <?php selected($view, 'list'); ?>><?php _e('List', 'happy-place-theme'); ?></option>
                <option value="grid" <?php selected($view, 'grid'); ?>><?php _e('Grid', 'happy-place-theme'); ?></option>
            </select>
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('post_type')); ?>"><?php _e('Post Type:', 'happy-place-theme'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('post_type')); ?>" name="<?php echo esc_attr($this->get_field_name('post_type')); ?>" type="text" value="<?php echo esc_attr($post_type); ?>">
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_navigation); ?> id="<?php echo esc_attr($this->get_field_id('show_navigation')); ?>" name="<?php echo esc_attr($this->get_field_name('show_navigation')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_navigation')); ?>"><?php _e('Show Navigation', 'happy-place-theme'); ?></label>
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['view'] = (!empty($new_instance['view'])) ? sanitize_key($new_instance['view']) : 'month';
        $instance['post_type'] = (!empty($new_instance['post_type'])) ? sanitize_key($new_instance['post_type']) : 'event';
        $instance['show_navigation'] = !empty($new_instance['show_navigation']);
        
        return $instance;
    }
}

// Register the widget
function register_hph_calendar_widget() {
    register_widget('HpH_Calendar_Widget');
}
add_action('widgets_init', 'register_hph_calendar_widget');

/*
 * USAGE IN THEME FILES
 * ===================
 */

// Example 9: Use in a page template
/*
Template Name: Events Calendar

get_header(); ?>

<div class="hph-container">
    <div class="hph-row">
        <div class="hph-col-12">
            <h1><?php the_title(); ?></h1>
            <?php the_content(); ?>
            
            <?php
            // Display the calendar
            get_template_part('template-parts/components/universal-calendar', '', array(
                'view' => 'month',
                'post_type' => 'event',
                'show_navigation' => true,
                'show_view_selector' => true
            ));
            ?>
        </div>
    </div>
</div>

<?php get_footer();
*/

// Example 10: Use in a custom block or Gutenberg block
/*
function hph_calendar_block_render($attributes) {
    ob_start();
    
    get_template_part('template-parts/components/universal-calendar', '', array(
        'view' => $attributes['view'] ?? 'month',
        'post_type' => $attributes['postType'] ?? 'event',
        'show_navigation' => $attributes['showNavigation'] ?? true,
        'show_view_selector' => $attributes['showViewSelector'] ?? true,
        'categories' => $attributes['categories'] ?? array()
    ));
    
    return ob_get_clean();
}
*/
?>