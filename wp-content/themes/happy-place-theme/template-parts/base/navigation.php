<?php
/**
 * Base Navigation Component
 * 
 * Pure UI navigation component for menus, breadcrumbs, and nav bars
 * 
 * @package HappyPlaceTheme
 * @subpackage Components/Base
 * @since 3.0.0
 */

$props = wp_parse_args(get_query_var('args', array()), array(
    // Navigation items
    'items' => array(), // Array of navigation items
    'logo' => array(
        'src' => '',
        'alt' => '',
        'href' => '/',
        'text' => '' // Text alternative to logo
    ),
    
    // Type and layout
    'type' => 'navbar', // navbar, sidebar, breadcrumb, tabs, pills, pagination, steps
    'layout' => 'horizontal', // horizontal, vertical, inline
    'position' => 'static', // static, fixed, sticky, absolute
    'placement' => 'top', // top, bottom, left, right
    
    // Appearance
    'variant' => 'default', // default, transparent, dark, light, gradient, bordered
    'size' => 'md', // sm, md, lg, xl
    'full_width' => true,
    'container' => true, // Wrap in container
    'shadow' => false,
    'blur_backdrop' => false,
    
    // Mobile behavior
    'responsive' => true,
    'mobile_breakpoint' => 'lg', // When to show mobile menu
    'mobile_type' => 'hamburger', // hamburger, bottom-bar, off-canvas
    'mobile_position' => 'right', // left, right, full
    
    // Features
    'sticky' => false,
    'auto_hide' => false, // Hide on scroll down
    'search' => false, // Include search
    'user_menu' => false, // Include user menu
    'notifications' => false, // Include notifications
    'cart' => false, // Include cart
    
    // Item display
    'show_icons' => true,
    'show_badges' => true,
    'show_descriptions' => false,
    'mega_menu' => false, // Support mega menu dropdowns
    
    // Breadcrumb specific
    'separator' => '/', // /, >, •, →
    'show_home' => true,
    'truncate' => 0, // Max items before truncating
    
    // Steps specific
    'current_step' => 0,
    'completed_steps' => array(),
    'clickable_steps' => false,
    
    // State
    'active_item' => '', // ID or index of active item
    'expanded_items' => array(), // For collapsible menus
    
    // HTML
    'id' => '',
    'class' => '',
    'attributes' => array(),
    'data' => array()
));

// Generate ID if needed
if (!$props['id']) {
    $props['id'] = 'hph-nav-' . substr(md5(serialize($props['items'])), 0, 8);
}

// Process navigation items
$processed_items = array();
foreach ($props['items'] as $index => $item) {
    $item_defaults = array(
        'id' => 'nav-item-' . $index,
        'label' => '',
        'href' => '#',
        'icon' => '',
        'badge' => '',
        'description' => '',
        'target' => '_self',
        'active' => false,
        'disabled' => false,
        'divider' => false,
        'heading' => false,
        'children' => array(), // Sub-items
        'mega' => false, // Mega menu content
        'class' => '',
        'attributes' => array()
    );
    
    $processed_items[] = wp_parse_args($item, $item_defaults);
}

// Build navigation classes
$nav_classes = array(
    'hph-nav',
    'hph-nav--' . $props['type'],
    'hph-nav--' . $props['variant'],
    'hph-nav--' . $props['size']
);

if ($props['layout'] !== 'horizontal') {
    $nav_classes[] = 'hph-nav--' . $props['layout'];
}

if ($props['position'] !== 'static') {
    $nav_classes[] = 'hph-nav--' . $props['position'];
    if ($props['placement'] !== 'top') {
        $nav_classes[] = 'hph-nav--' . $props['placement'];
    }
}

if ($props['full_width']) {
    $nav_classes[] = 'hph-nav--full';
}

if ($props['shadow']) {
    $nav_classes[] = 'hph-nav--shadow';
}

if ($props['blur_backdrop']) {
    $nav_classes[] = 'hph-nav--blur';
}

if ($props['sticky']) {
    $nav_classes[] = 'hph-nav--sticky';
}

if ($props['auto_hide']) {
    $nav_classes[] = 'hph-nav--auto-hide';
}

if ($props['class']) {
    $nav_classes[] = $props['class'];
}

// Render based on type
switch ($props['type']):
    
    case 'navbar': ?>
        
        <nav class="<?php echo esc_attr(implode(' ', $nav_classes)); ?>"
             id="<?php echo esc_attr($props['id']); ?>"
             <?php foreach ($props['data'] as $key => $value): ?>
             data-<?php echo esc_attr($key); ?>="<?php echo esc_attr($value); ?>"
             <?php endforeach; ?>>
            
            <?php if ($props['container']): ?>
            <div class="hph-nav__container">
            <?php endif; ?>
                
                <?php if ($props['logo']['src'] || $props['logo']['text']): ?>
                <div class="hph-nav__brand">
                    <a href="<?php echo esc_url($props['logo']['href']); ?>" class="hph-nav__logo">
                        <?php if ($props['logo']['src']): ?>
                            <img src="<?php echo esc_url($props['logo']['src']); ?>" 
                                 alt="<?php echo esc_attr($props['logo']['alt']); ?>"
                                 class="hph-nav__logo-img">
                        <?php else: ?>
                            <span class="hph-nav__logo-text"><?php echo esc_html($props['logo']['text']); ?></span>
                        <?php endif; ?>
                    </a>
                </div>
                <?php endif; ?>
                
                <?php if ($props['responsive']): ?>
                <button type="button" 
                        class="hph-nav__toggle"
                        aria-label="Toggle navigation"
                        aria-expanded="false"
                        aria-controls="<?php echo esc_attr($props['id']); ?>-menu">
                    <span class="hph-nav__toggle-icon"></span>
                </button>
                <?php endif; ?>
                
                <div class="hph-nav__menu" id="<?php echo esc_attr($props['id']); ?>-menu">
                    <ul class="hph-nav__list">
                        <?php hph_render_nav_items($processed_items, $props); ?>
                    </ul>
                </div>
                
                <?php if ($props['search'] || $props['user_menu'] || $props['notifications'] || $props['cart']): ?>
                <div class="hph-nav__actions">
                    <?php if ($props['search']): ?>
                    <button type="button" class="hph-nav__action hph-nav__search-trigger" aria-label="Search">
                        <span data-icon="search"></span>
                    </button>
                    <?php endif; ?>
                    
                    <?php if ($props['notifications']): ?>
                    <button type="button" class="hph-nav__action hph-nav__notifications" aria-label="Notifications">
                        <span data-icon="bell"></span>
                        <span class="hph-nav__badge">3</span>
                    </button>
                    <?php endif; ?>
                    
                    <?php if ($props['cart']): ?>
                    <button type="button" class="hph-nav__action hph-nav__cart" aria-label="Cart">
                        <span data-icon="shopping-cart"></span>
                        <span class="hph-nav__badge">2</span>
                    </button>
                    <?php endif; ?>
                    
                    <?php if ($props['user_menu']): ?>
                    <button type="button" class="hph-nav__action hph-nav__user" aria-label="User menu">
                        <span data-icon="user"></span>
                    </button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
            <?php if ($props['container']): ?>
            </div>
            <?php endif; ?>
            
        </nav>
        
        <?php break;
        
    case 'breadcrumb': ?>
        
        <nav class="<?php echo esc_attr(implode(' ', $nav_classes)); ?>"
             id="<?php echo esc_attr($props['id']); ?>"
             aria-label="Breadcrumb">
            <ol class="hph-nav__list">
                <?php if ($props['show_home']): ?>
                <li class="hph-nav__item">
                    <a href="/" class="hph-nav__link">
                        <span data-icon="home"></span>
                        <span>Home</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php 
                $total = count($processed_items);
                $start = 0;
                
                // Handle truncation
                if ($props['truncate'] > 0 && $total > $props['truncate']) {
                    $start = $total - $props['truncate'] + 1;
                    ?>
                    <li class="hph-nav__item hph-nav__item--truncated">
                        <button type="button" class="hph-nav__expand" aria-label="Show all">
                            <span data-icon="more-horizontal"></span>
                        </button>
                    </li>
                    <?php
                }
                
                foreach (array_slice($processed_items, $start) as $index => $item): 
                    $is_last = ($index + $start) === ($total - 1);
                ?>
                <li class="hph-nav__item<?php echo $is_last ? ' hph-nav__item--current' : ''; ?>">
                    <?php if (!$is_last): ?>
                        <a href="<?php echo esc_url($item['href']); ?>" class="hph-nav__link">
                            <?php echo esc_html($item['label']); ?>
                        </a>
                        <span class="hph-nav__separator" aria-hidden="true">
                            <?php echo esc_html($props['separator']); ?>
                        </span>
                    <?php else: ?>
                        <span aria-current="page"><?php echo esc_html($item['label']); ?></span>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ol>
        </nav>
        
        <?php break;
        
    case 'steps': ?>
        
        <nav class="<?php echo esc_attr(implode(' ', $nav_classes)); ?>"
             id="<?php echo esc_attr($props['id']); ?>"
             aria-label="Progress">
            <ol class="hph-nav__list">
                <?php foreach ($processed_items as $index => $item): 
                    $is_current = $index === $props['current_step'];
                    $is_completed = in_array($index, $props['completed_steps']);
                    
                    $step_classes = array('hph-nav__item', 'hph-nav__step');
                    if ($is_current) $step_classes[] = 'hph-nav__step--current';
                    if ($is_completed) $step_classes[] = 'hph-nav__step--completed';
                    if ($item['disabled']) $step_classes[] = 'hph-nav__step--disabled';
                ?>
                <li class="<?php echo esc_attr(implode(' ', $step_classes)); ?>">
                    <?php if ($props['clickable_steps'] && !$item['disabled'] && ($is_completed || $is_current)): ?>
                    <a href="<?php echo esc_url($item['href']); ?>" class="hph-nav__step-link">
                    <?php else: ?>
                    <div class="hph-nav__step-link">
                    <?php endif; ?>
                        
                        <span class="hph-nav__step-indicator">
                            <?php if ($is_completed): ?>
                                <span data-icon="check"></span>
                            <?php else: ?>
                                <?php echo $index + 1; ?>
                            <?php endif; ?>
                        </span>
                        
                        <span class="hph-nav__step-content">
                            <span class="hph-nav__step-label"><?php echo esc_html($item['label']); ?></span>
                            <?php if ($item['description'] && $props['show_descriptions']): ?>
                            <span class="hph-nav__step-description"><?php echo esc_html($item['description']); ?></span>
                            <?php endif; ?>
                        </span>
                        
                    <?php if ($props['clickable_steps'] && !$item['disabled'] && ($is_completed || $is_current)): ?>
                    </a>
                    <?php else: ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($index < count($processed_items) - 1): ?>
                    <span class="hph-nav__step-connector"></span>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ol>
        </nav>
        
        <?php break;
        
    case 'sidebar': ?>
        
        <nav class="<?php echo esc_attr(implode(' ', $nav_classes)); ?>"
             id="<?php echo esc_attr($props['id']); ?>">
            
            <?php if ($props['logo']['src'] || $props['logo']['text']): ?>
            <div class="hph-nav__header">
                <a href="<?php echo esc_url($props['logo']['href']); ?>" class="hph-nav__logo">
                    <?php if ($props['logo']['src']): ?>
                        <img src="<?php echo esc_url($props['logo']['src']); ?>" 
                             alt="<?php echo esc_attr($props['logo']['alt']); ?>">
                    <?php else: ?>
                        <span><?php echo esc_html($props['logo']['text']); ?></span>
                    <?php endif; ?>
                </a>
            </div>
            <?php endif; ?>
            
            <ul class="hph-nav__list">
                <?php hph_render_nav_items($processed_items, $props, true); ?>
            </ul>
            
        </nav>
        
        <?php break;
        
endswitch;

/**
 * Helper function to render navigation items recursively
 */
function hph_render_nav_items($items, $props, $is_sidebar = false, $level = 0) {
    foreach ($items as $item) {
        if ($item['divider']) {
            echo '<li class="hph-nav__divider"></li>';
            continue;
        }
        
        if ($item['heading']) {
            echo '<li class="hph-nav__heading">' . esc_html($item['label']) . '</li>';
            continue;
        }
        
        $item_classes = array('hph-nav__item');
        if ($item['active'] || $item['id'] === $props['active_item']) {
            $item_classes[] = 'hph-nav__item--active';
        }
        if ($item['disabled']) {
            $item_classes[] = 'hph-nav__item--disabled';
        }
        if (!empty($item['children'])) {
            $item_classes[] = 'hph-nav__item--has-children';
            if (in_array($item['id'], $props['expanded_items'])) {
                $item_classes[] = 'hph-nav__item--expanded';
            }
        }
        if ($item['class']) {
            $item_classes[] = $item['class'];
        }
        ?>
        <li class="<?php echo esc_attr(implode(' ', $item_classes)); ?>">
            <a href="<?php echo esc_url($item['href']); ?>"
               class="hph-nav__link"
               <?php if ($item['target'] !== '_self'): ?>
               target="<?php echo esc_attr($item['target']); ?>"
               <?php endif; ?>
               <?php foreach ($item['attributes'] as $key => $value): ?>
               <?php echo esc_attr($key); ?>="<?php echo esc_attr($value); ?>"
               <?php endforeach; ?>>
                
                <?php if ($item['icon'] && $props['show_icons']): ?>
                <span class="hph-nav__icon" data-icon="<?php echo esc_attr($item['icon']); ?>"></span>
                <?php endif; ?>
                
                <span class="hph-nav__label"><?php echo esc_html($item['label']); ?></span>
                
                <?php if ($item['badge'] && $props['show_badges']): ?>
                <span class="hph-nav__badge"><?php echo esc_html($item['badge']); ?></span>
                <?php endif; ?>
                
                <?php if (!empty($item['children'])): ?>
                <span class="hph-nav__arrow" data-icon="chevron-down"></span>
                <?php endif; ?>
            </a>
            
            <?php if (!empty($item['children'])): ?>
            <ul class="hph-nav__submenu">
                <?php hph_render_nav_items($item['children'], $props, $is_sidebar, $level + 1); ?>
            </ul>
            <?php endif; ?>
        </li>
        <?php
    }
}