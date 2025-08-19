<?php
/**
 * Template Hooks
 * Action and filter hooks for template customization
 *
 * @package HappyPlaceTheme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Header hooks
 */

// Add extra meta tags to head
add_action('wp_head', 'happy_place_add_meta_tags');
function happy_place_add_meta_tags() {
    ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#007bff">
    <?php
}

// Add preconnect links for performance
add_action('wp_head', 'happy_place_add_preconnect_links', 1);
function happy_place_add_preconnect_links() {
    ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <?php
}

/**
 * Content hooks
 */

// Add schema markup to articles
add_action('happy_place_before_content', 'happy_place_add_article_schema');
function happy_place_add_article_schema() {
    if (is_single() && get_post_type() === 'post') {
        ?>
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Article",
            "headline": "<?php echo esc_js(get_the_title()); ?>",
            "datePublished": "<?php echo esc_js(get_the_date('c')); ?>",
            "dateModified": "<?php echo esc_js(get_the_modified_date('c')); ?>",
            "author": {
                "@type": "Person",
                "name": "<?php echo esc_js(get_the_author()); ?>"
            }
        }
        </script>
        <?php
    }
}

// Add breadcrumbs before main content
add_action('happy_place_before_content', 'happy_place_add_breadcrumbs');
function happy_place_add_breadcrumbs() {
    if (!is_front_page()) {
        echo '<div class="container"><div class="breadcrumbs-wrapper py-4">';
        echo happy_place_get_breadcrumbs();
        echo '</div></div>';
    }
}

// Add social sharing after single post content
add_action('happy_place_after_single_content', 'happy_place_add_social_sharing');
function happy_place_add_social_sharing() {
    if (is_single()) {
        echo '<div class="social-sharing-wrapper mt-6">';
        echo happy_place_get_social_share();
        echo '</div>';
    }
}

/**
 * Footer hooks
 */

// Add back to top button
add_action('wp_footer', 'happy_place_add_back_to_top');
function happy_place_add_back_to_top() {
    ?>
    <button id="back-to-top" class="btn btn-primary btn-round position-fixed" style="bottom: 20px; right: 20px; display: none; z-index: 1000;" title="<?php esc_attr_e('Back to Top', 'happy-place-theme'); ?>">
        <i class="fas fa-chevron-up"></i>
    </button>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const backToTopBtn = document.getElementById('back-to-top');
        
        if (backToTopBtn) {
            // Show/hide button based on scroll position
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    backToTopBtn.style.display = 'block';
                } else {
                    backToTopBtn.style.display = 'none';
                }
            });
            
            // Scroll to top when clicked
            backToTopBtn.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }
    });
    </script>
    <?php
}

/**
 * Search form customization
 */

// Customize search form
add_filter('get_search_form', 'happy_place_custom_search_form');
function happy_place_custom_search_form($form) {
    $search_value = get_search_query() ? get_search_query() : '';
    
    $form = '<form role="search" method="get" class="search-form" action="' . esc_url(home_url('/')) . '">';
    $form .= '<div class="input-group">';
    $form .= '<input type="search" class="form-control search-field" placeholder="' . esc_attr__('Search...', 'happy-place-theme') . '" value="' . esc_attr($search_value) . '" name="s" />';
    $form .= '<div class="input-group-append">';
    $form .= '<button type="submit" class="btn btn-primary search-submit">';
    $form .= '<i class="fas fa-search"></i>';
    $form .= '<span class="sr-only">' . esc_html__('Search', 'happy-place-theme') . '</span>';
    $form .= '</button>';
    $form .= '</div>';
    $form .= '</div>';
    $form .= '</form>';
    
    return $form;
}

/**
 * Comments customization
 */

// Customize comment form
add_filter('comment_form_defaults', 'happy_place_comment_form_defaults');
function happy_place_comment_form_defaults($defaults) {
    $defaults['class_form'] = 'comment-form';
    $defaults['class_submit'] = 'btn btn-primary';
    $defaults['submit_button'] = '<button type="submit" class="%3$s">%4$s</button>';
    
    return $defaults;
}

// Customize comment form fields
add_filter('comment_form_default_fields', 'happy_place_comment_form_fields');
function happy_place_comment_form_fields($fields) {
    $commenter = wp_get_current_commenter();
    
    $fields['author'] = '<div class="form-group"><label for="author">' . __('Name', 'happy-place-theme') . ' *</label><input id="author" name="author" type="text" value="' . esc_attr($commenter['comment_author']) . '" class="form-control" required="required" /></div>';
    
    $fields['email'] = '<div class="form-group"><label for="email">' . __('Email', 'happy-place-theme') . ' *</label><input id="email" name="email" type="email" value="' . esc_attr($commenter['comment_author_email']) . '" class="form-control" required="required" /></div>';
    
    $fields['url'] = '<div class="form-group"><label for="url">' . __('Website', 'happy-place-theme') . '</label><input id="url" name="url" type="url" value="' . esc_attr($commenter['comment_author_url']) . '" class="form-control" /></div>';
    
    return $fields;
}

// Customize comment textarea
add_filter('comment_form_field_comment', 'happy_place_comment_form_textarea');
function happy_place_comment_form_textarea($field) {
    $field = '<div class="form-group"><label for="comment">' . __('Comment', 'happy-place-theme') . ' *</label><textarea id="comment" name="comment" class="form-control" rows="5" required="required"></textarea></div>';
    
    return $field;
}

/**
 * Navigation customization
 */

// Add custom walker for navigation menus
class Happy_Place_Walker_Nav_Menu extends Walker_Nav_Menu {
    
    // Start Level - Top level menu items
    function start_lvl(&$output, $depth = 0, $args = array()) {
        $indent = str_repeat("\t", $depth);
        $output .= "\n$indent<ul class=\"dropdown-menu\">\n";
    }
    
    // End Level
    function end_lvl(&$output, $depth = 0, $args = array()) {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent</ul>\n";
    }
    
    // Start Element - Individual menu items
    function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
        $indent = ($depth) ? str_repeat("\t", $depth) : '';
        
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;
        
        // Add dropdown class if item has children
        $has_children = in_array('menu-item-has-children', $classes);
        if ($has_children && $depth === 0) {
            $classes[] = 'dropdown';
        }
        
        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
        $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';
        
        $id = apply_filters('nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args);
        $id = $id ? ' id="' . esc_attr($id) . '"' : '';
        
        $output .= $indent . '<li' . $id . $class_names .'>';
        
        $attributes = ! empty($item->attr_title) ? ' title="'  . esc_attr($item->attr_title) .'"' : '';
        $attributes .= ! empty($item->target)     ? ' target="' . esc_attr($item->target     ) .'"' : '';
        $attributes .= ! empty($item->xfn)        ? ' rel="'    . esc_attr($item->xfn        ) .'"' : '';
        $attributes .= ! empty($item->url)        ? ' href="'   . esc_attr($item->url        ) .'"' : '';
        
        // Add Bootstrap dropdown attributes
        if ($has_children && $depth === 0) {
            $attributes .= ' class="nav-link dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"';
        } else {
            $attributes .= ' class="nav-link"';
        }
        
        $item_output = $args->before ?? '';
        $item_output .= '<a' . $attributes .'>';
        $item_output .= ($args->link_before ?? '') . apply_filters('the_title', $item->title, $item->ID) . ($args->link_after ?? '');
        $item_output .= '</a>';
        $item_output .= $args->after ?? '';
        
        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }
    
    // End Element
    function end_el(&$output, $item, $depth = 0, $args = array()) {
        $output .= "</li>\n";
    }
}

/**
 * Pagination customization
 */

// Customize pagination
add_filter('navigation_markup_template', 'happy_place_pagination_template', 10, 2);
function happy_place_pagination_template($template, $class) {
    return '
    <nav class="navigation %1$s" role="navigation" aria-label="%4$s">
        <div class="nav-links">%3$s</div>
    </nav>';
}

/**
 * Widget customization
 */

// Add custom classes to widgets
add_filter('dynamic_sidebar_params', 'happy_place_widget_classes');
function happy_place_widget_classes($params) {
    if (isset($params[0]['before_widget'])) {
        $params[0]['before_widget'] = str_replace('class="', 'class="widget card ', $params[0]['before_widget']);
        $params[0]['before_title'] = '<h3 class="widget-title card-header">';
        $params[0]['after_title'] = '</h3><div class="card-body">';
        $params[0]['after_widget'] = '</div>' . $params[0]['after_widget'];
    }
    
    return $params;
}
