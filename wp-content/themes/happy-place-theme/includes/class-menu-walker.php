<?php
/**
 * Custom Menu Walker for HPH Theme
 * Handles mega menu functionality
 */

class HPH_Mega_Menu_Walker extends Walker_Nav_Menu {
    
    function start_lvl(&$output, $depth = 0, $args = null) {
        $indent = str_repeat("\t", $depth);
        $output .= "\n$indent<ul class=\"sub-menu\">\n";
    }
    
    function end_lvl(&$output, $depth = 0, $args = null) {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent</ul>\n";
    }
    
    function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $indent = ($depth) ? str_repeat("\t", $depth) : '';
        
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;
        
        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
        $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';
        
        $id = apply_filters('nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args);
        $id = $id ? ' id="' . esc_attr($id) . '"' : '';
        
        $output .= $indent . '<li' . $id . $class_names .'>';
        
        $attributes = ! empty($item->attr_title) ? ' title="'  . esc_attr($item->attr_title) .'"' : '';
        $attributes .= ! empty($item->target)     ? ' target="' . esc_attr($item->target     ) .'"' : '';
        $attributes .= ! empty($item->xfn)        ? ' rel="'    . esc_attr($item->xfn        ) .'"' : '';
        $attributes .= ! empty($item->url)        ? ' href="'   . esc_attr($item->url        ) .'"' : '';
        
        $item_output = isset($args->before) ? $args->before : '';
        $item_output .= '<a' . $attributes . '>';
        $item_output .= (isset($args->link_before) ? $args->link_before : '') . apply_filters('the_title', $item->title, $item->ID) . (isset($args->link_after) ? $args->link_after : '');
        
        // Add dropdown arrow for parent items
        if (in_array('menu-item-has-children', $classes)) {
            $item_output .= ' <i class="fas fa-chevron-down"></i>';
        }
        
        $item_output .= '</a>';
        $item_output .= isset($args->after) ? $args->after : '';
        
        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }
    
    function end_el(&$output, $item, $depth = 0, $args = null) {
        $output .= "</li>\n";
    }
}

/**
 * Default menu fallback
 */
function hph_default_menu() {
    echo '<ul class="hph-nav-menu">';
    echo '<li><a href="' . home_url('/') . '">Home</a></li>';
    echo '<li><a href="' . home_url('/listings') . '">Listings</a></li>';
    echo '<li><a href="' . home_url('/agents') . '">Agents</a></li>';
    echo '<li><a href="' . home_url('/about') . '">About</a></li>';
    echo '<li><a href="#" class="modal-trigger" data-modal-id="hph-form-modal" data-modal-form="general-contact" data-modal-title="Contact Us" data-modal-subtitle="Send us a message and we\'ll get back to you soon." onclick="return false;">Contact</a></li>';
    echo '</ul>';
}

/**
 * Default mobile menu fallback
 */
function hph_default_mobile_menu() {
    echo '<ul class="hph-mobile-menu-list">';
    echo '<li><a href="' . home_url('/') . '">Home</a></li>';
    echo '<li><a href="' . home_url('/listings') . '">Listings</a></li>';
    echo '<li><a href="' . home_url('/agents') . '">Agents</a></li>';
    echo '<li><a href="' . home_url('/about') . '">About</a></li>';
    echo '<li><a href="#" class="modal-trigger" data-modal-id="hph-form-modal" data-modal-form="general-contact" data-modal-title="Contact Us" data-modal-subtitle="Send us a message and we\'ll get back to you soon." onclick="return false;">Contact</a></li>';
    echo '</ul>';
}
?>
