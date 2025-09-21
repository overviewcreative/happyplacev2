<?php
/**
 * Base Tabs Component
 * 
 * Pure UI tabs/tab panel component with multiple styles
 * 
 * @package HappyPlaceTheme
 * @subpackage Components/Base
 * @since 3.0.0
 */

$props = wp_parse_args(get_query_var('args', array()), array(
    // Tabs data
    'tabs' => array(), // Array of tab configurations
    'active' => 0, // Active tab index or ID
    
    // Appearance
    'variant' => 'default', // default, pills, underline, boxed, vertical, accordion
    'size' => 'md', // sm, md, lg
    'alignment' => 'left', // left, center, right, justify
    'theme' => 'light', // light, dark
    
    // Behavior
    'orientation' => 'horizontal', // horizontal, vertical
    'activation' => 'auto', // auto, manual (for accessibility)
    'lazy_load' => false, // Load tab content only when activated
    'closable' => false, // Tabs can be closed
    'draggable' => false, // Tabs can be reordered
    'scrollable' => false, // Tab list scrolls if too many tabs
    'keyboard' => true, // Keyboard navigation
    
    // Icons & Badges
    'show_icons' => true,
    'show_badges' => true,
    'icon_position' => 'left', // left, right, top
    
    // Animation
    'animation' => 'fade', // fade, slide, none
    'animation_duration' => 200,
    
    // Advanced
    'remember_state' => false, // Remember active tab in localStorage
    'storage_key' => '', // localStorage key
    'url_update' => false, // Update URL hash with active tab
    'overflow_menu' => false, // Show overflow tabs in dropdown
    
    // HTML
    'id' => '',
    'class' => '',
    'panel_class' => '',
    'attributes' => array(),
    'data' => array()
));

// Generate ID if not provided
if (!$props['id']) {
    $props['id'] = 'hph-tabs-' . substr(md5(serialize($props['tabs'])), 0, 8);
}

// Container classes
$container_classes = array(
    'hph-tabs',
    'hph-tabs--' . $props['variant'],
    'hph-tabs--' . $props['size'],
    'hph-tabs--' . $props['orientation'],
    'hph-tabs--' . $props['theme']
);

if ($props['alignment'] !== 'left') {
    $container_classes[] = 'hph-tabs--align-' . $props['alignment'];
}

if ($props['scrollable']) {
    $container_classes[] = 'hph-tabs--scrollable';
}

if ($props['class']) {
    $container_classes[] = $props['class'];
}

// Tab list classes
$tablist_classes = array(
    'hph-tabs__list'
);

if ($props['draggable']) {
    $tablist_classes[] = 'hph-tabs__list--draggable';
}

// Process tabs
$processed_tabs = array();
foreach ($props['tabs'] as $index => $tab) {
    $tab_defaults = array(
        'id' => '',
        'label' => 'Tab ' . ($index + 1),
        'content' => '',
        'icon' => '',
        'badge' => '',
        'badge_variant' => 'default',
        'disabled' => false,
        'hidden' => false,
        'closable' => $props['closable'],
        'href' => '', // For link tabs
        'target' => '_self',
        'tooltip' => '',
        'class' => ''
    );
    
    $processed_tab = wp_parse_args($tab, $tab_defaults);
    
    // Generate tab ID if not provided
    if (!$processed_tab['id']) {
        $processed_tab['id'] = $props['id'] . '-tab-' . $index;
    }
    
    $processed_tabs[] = $processed_tab;
}

// Determine active tab
$active_index = 0;
if (is_numeric($props['active'])) {
    $active_index = (int)$props['active'];
} else {
    // Find by ID
    foreach ($processed_tabs as $index => $tab) {
        if ($tab['id'] === $props['active']) {
            $active_index = $index;
            break;
        }
    }
}

?>

<div class="<?php echo esc_attr(implode(' ', $container_classes)); ?>"
     id="<?php echo esc_attr($props['id']); ?>"
     <?php if ($props['remember_state']): ?>
     data-remember="true"
     data-storage-key="<?php echo esc_attr($props['storage_key'] ?: $props['id']); ?>"
     <?php endif; ?>
     <?php if ($props['url_update']): ?>
     data-url-update="true"
     <?php endif; ?>
     <?php foreach ($props['data'] as $key => $value): ?>
     data-<?php echo esc_attr($key); ?>="<?php echo esc_attr($value); ?>"
     <?php endforeach; ?>
     <?php foreach ($props['attributes'] as $key => $value): ?>
     <?php echo esc_attr($key); ?>="<?php echo esc_attr($value); ?>"
     <?php endforeach; ?>>
    
    <?php if ($props['variant'] === 'accordion'): ?>
    <!-- Accordion variant -->
    <div class="hph-tabs__accordion">
        <?php foreach ($processed_tabs as $index => $tab): 
            if ($tab['hidden']) continue;
            $is_active = $index === $active_index;
        ?>
        <div class="hph-tabs__accordion-item<?php echo $is_active ? ' is-active' : ''; ?>">
            <button type="button"
                    class="hph-tabs__accordion-header"
                    id="<?php echo esc_attr($tab['id']); ?>-header"
                    aria-expanded="<?php echo $is_active ? 'true' : 'false'; ?>"
                    aria-controls="<?php echo esc_attr($tab['id']); ?>-panel"
                    <?php echo $tab['disabled'] ? 'disabled' : ''; ?>>
                
                <?php if ($tab['icon'] && $props['show_icons']): ?>
                <span class="hph-tabs__icon" data-icon="<?php echo esc_attr($tab['icon']); ?>"></span>
                <?php endif; ?>
                
                <span class="hph-tabs__label"><?php echo esc_html($tab['label']); ?></span>
                
                <?php if ($tab['badge'] && $props['show_badges']): ?>
                <span class="hph-tabs__badge hph-badge hph-badge--<?php echo esc_attr($tab['badge_variant']); ?>">
                    <?php echo esc_html($tab['badge']); ?>
                </span>
                <?php endif; ?>
                
                <span class="hph-tabs__accordion-icon" data-icon="chevron-down"></span>
            </button>
            
            <div class="hph-tabs__accordion-panel"
                 id="<?php echo esc_attr($tab['id']); ?>-panel"
                 aria-labelledby="<?php echo esc_attr($tab['id']); ?>-header"
                 <?php if (!$is_active): ?>style="display: none;"<?php endif; ?>>
                <div class="hph-tabs__panel-content">
                    <?php echo $tab['content']; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <?php else: ?>
    <!-- Standard tabs -->
    <div class="hph-tabs__header">
        <?php if ($props['overflow_menu']): ?>
        <button type="button" class="hph-tabs__overflow-trigger" aria-label="More tabs" style="display: none;">
            <span data-icon="more-horizontal"></span>
        </button>
        <?php endif; ?>
        
        <div class="<?php echo esc_attr(implode(' ', $tablist_classes)); ?>"
             role="tablist"
             <?php if ($props['orientation'] === 'vertical'): ?>
             aria-orientation="vertical"
             <?php endif; ?>>
            
            <?php foreach ($processed_tabs as $index => $tab): 
                if ($tab['hidden']) continue;
                $is_active = $index === $active_index;
                
                $tab_classes = array('hph-tabs__tab');
                if ($is_active) {
                    $tab_classes[] = 'is-active';
                }
                if ($tab['disabled']) {
                    $tab_classes[] = 'is-disabled';
                }
                if ($tab['class']) {
                    $tab_classes[] = $tab['class'];
                }
            ?>
            
            <?php if ($tab['href']): ?>
            <!-- Link tab -->
            <a href="<?php echo esc_url($tab['href']); ?>"
               target="<?php echo esc_attr($tab['target']); ?>"
               class="<?php echo esc_attr(implode(' ', $tab_classes)); ?>"
               <?php if ($tab['tooltip']): ?>
               title="<?php echo esc_attr($tab['tooltip']); ?>"
               <?php endif; ?>>
            <?php else: ?>
            <!-- Regular tab -->
            <button type="button"
                    class="<?php echo esc_attr(implode(' ', $tab_classes)); ?>"
                    id="<?php echo esc_attr($tab['id']); ?>"
                    role="tab"
                    aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
                    aria-controls="<?php echo esc_attr($tab['id']); ?>-panel"
                    tabindex="<?php echo $is_active ? '0' : '-1'; ?>"
                    <?php echo $tab['disabled'] ? 'disabled' : ''; ?>
                    <?php if ($tab['tooltip']): ?>
                    title="<?php echo esc_attr($tab['tooltip']); ?>"
                    <?php endif; ?>
                    <?php if ($props['draggable']): ?>
                    draggable="true"
                    <?php endif; ?>>
            <?php endif; ?>
                
                <span class="hph-tabs__tab-inner">
                    <?php if ($tab['icon'] && $props['show_icons'] && $props['icon_position'] === 'left'): ?>
                    <span class="hph-tabs__icon" data-icon="<?php echo esc_attr($tab['icon']); ?>"></span>
                    <?php endif; ?>
                    
                    <?php if ($tab['icon'] && $props['show_icons'] && $props['icon_position'] === 'top'): ?>
                    <span class="hph-tabs__icon hph-tabs__icon--top" data-icon="<?php echo esc_attr($tab['icon']); ?>"></span>
                    <?php endif; ?>
                    
                    <span class="hph-tabs__label"><?php echo esc_html($tab['label']); ?></span>
                    
                    <?php if ($tab['icon'] && $props['show_icons'] && $props['icon_position'] === 'right'): ?>
                    <span class="hph-tabs__icon" data-icon="<?php echo esc_attr($tab['icon']); ?>"></span>
                    <?php endif; ?>
                    
                    <?php if ($tab['badge'] && $props['show_badges']): ?>
                    <span class="hph-tabs__badge hph-badge hph-badge--<?php echo esc_attr($tab['badge_variant']); ?>">
                        <?php echo esc_html($tab['badge']); ?>
                    </span>
                    <?php endif; ?>
                    
                    <?php if ($tab['closable']): ?>
                    <button type="button" 
                            class="hph-tabs__close" 
                            aria-label="Close tab"
                            data-tab-close="<?php echo esc_attr($tab['id']); ?>">
                        <span data-icon="x"></span>
                    </button>
                    <?php endif; ?>
                </span>
                
            <?php if ($tab['href']): ?>
            </a>
            <?php else: ?>
            </button>
            <?php endif; ?>
            
            <?php endforeach; ?>
        </div>
        
        <?php if ($props['overflow_menu']): ?>
        <div class="hph-tabs__overflow-menu" style="display: none;">
            <!-- Populated by JavaScript with overflow tabs -->
        </div>
        <?php endif; ?>
    </div>
    
    <div class="hph-tabs__panels<?php echo $props['panel_class'] ? ' ' . esc_attr($props['panel_class']) : ''; ?>">
        <?php foreach ($processed_tabs as $index => $tab): 
            if ($tab['hidden'] || $tab['href']) continue;
            $is_active = $index === $active_index;
        ?>
        <div class="hph-tabs__panel<?php echo $props['animation'] !== 'none' ? ' hph-tabs__panel--' . $props['animation'] : ''; ?>"
             id="<?php echo esc_attr($tab['id']); ?>-panel"
             role="tabpanel"
             aria-labelledby="<?php echo esc_attr($tab['id']); ?>"
             tabindex="0"
             <?php if (!$is_active): ?>
             hidden
             <?php endif; ?>
             <?php if ($props['lazy_load'] && !$is_active): ?>
             data-lazy="true"
             data-content="<?php echo esc_attr(base64_encode($tab['content'])); ?>"
             <?php endif; ?>>
            
            <?php if (!$props['lazy_load'] || $is_active): ?>
                <?php echo $tab['content']; ?>
            <?php else: ?>
                <div class="hph-tabs__panel-loader">
                    <span class="hph-spinner"></span>
                </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
</div>
