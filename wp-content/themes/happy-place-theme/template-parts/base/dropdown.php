<?php
/**
 * Base Dropdown Component
 * Accessible dropdown menu with comprehensive configuration options
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Dropdown component configuration
 */
$defaults = [
    // Content
    'trigger_text' => 'Dropdown',
    'trigger_icon' => 'chevron-down',
    'items' => [],
    
    // Behavior
    'trigger' => 'click', // click, hover
    'placement' => 'bottom-start', // bottom-start, bottom-end, top-start, top-end, left, right
    'auto_close' => true,
    'close_on_select' => true,
    'disabled' => false,
    
    // Style variants
    'variant' => 'default', // default, outline, ghost, minimal
    'size' => 'md', // sm, md, lg
    'full_width' => false,
    
    // Advanced options
    'max_height' => '300px',
    'searchable' => false,
    'multi_select' => false,
    'show_selected_count' => false,
    'placeholder' => 'Select options...',
    
    // Accessibility
    'label' => '',
    'description' => '',
    'required' => false,
    
    // CSS classes
    'container_class' => '',
    'trigger_class' => '',
    'menu_class' => '',
    'item_class' => '',
    
    // Data attributes
    'data_attributes' => [],
    
    // Advanced features
    'ajax_url' => '',
    'loading_text' => 'Loading...',
    'no_results_text' => 'No results found',
    'select_all_text' => 'Select All',
    'clear_all_text' => 'Clear All',
];

$props = wp_parse_args(hph_get_arg() ?? [], $defaults);

// Generate unique ID
$dropdown_id = $props['id'] ?? 'hph-dropdown-' . wp_unique_id();
$trigger_id = $dropdown_id . '-trigger';
$menu_id = $dropdown_id . '-menu';

// Build CSS classes
$container_classes = [
    'hph-dropdown',
    'hph-dropdown--' . $props['variant'],
    'hph-dropdown--' . $props['size'],
];

if ($props['full_width']) {
    $container_classes[] = 'hph-dropdown--full-width';
}

if ($props['disabled']) {
    $container_classes[] = 'hph-dropdown--disabled';
}

if ($props['searchable']) {
    $container_classes[] = 'hph-dropdown--searchable';
}

if ($props['multi_select']) {
    $container_classes[] = 'hph-dropdown--multi-select';
}

if (!empty($props['container_class'])) {
    $container_classes[] = $props['container_class'];
}

$trigger_classes = [
    'hph-dropdown__trigger',
];

if (!empty($props['trigger_class'])) {
    $trigger_classes[] = $props['trigger_class'];
}

$menu_classes = [
    'hph-dropdown__menu',
    'hph-dropdown__menu--' . $props['placement'],
];

if (!empty($props['menu_class'])) {
    $menu_classes[] = $props['menu_class'];
}

// Data attributes
$data_attrs = [
    'data-dropdown-trigger' => $props['trigger'],
    'data-dropdown-placement' => $props['placement'],
    'data-dropdown-auto-close' => $props['auto_close'] ? 'true' : 'false',
    'data-dropdown-close-on-select' => $props['close_on_select'] ? 'true' : 'false',
];

if ($props['ajax_url']) {
    $data_attrs['data-dropdown-ajax-url'] = $props['ajax_url'];
}

if (!empty($props['data_attributes'])) {
    $data_attrs = array_merge($data_attrs, $props['data_attributes']);
}

// Process items if they're not already structured
$processed_items = [];
if (!empty($props['items'])) {
    foreach ($props['items'] as $item) {
        if (is_string($item)) {
            $processed_items[] = [
                'text' => $item,
                'value' => sanitize_title($item),
                'href' => '',
                'icon' => '',
                'disabled' => false,
                'selected' => false,
                'group' => '',
            ];
        } elseif (is_array($item)) {
            $processed_items[] = wp_parse_args($item, [
                'text' => '',
                'value' => '',
                'href' => '',
                'icon' => '',
                'disabled' => false,
                'selected' => false,
                'group' => '',
                'class' => '',
            ]);
        }
    }
}

// Build attributes
$container_attrs = [];
foreach ($data_attrs as $key => $value) {
    $container_attrs[] = esc_attr($key) . '="' . esc_attr($value) . '"';
}
?>

<div 
    id="<?php echo esc_attr($dropdown_id); ?>"
    class="<?php echo esc_attr(implode(' ', $container_classes)); ?>"
    <?php echo implode(' ', $container_attrs); ?>
    <?php if ($props['disabled']): ?>aria-disabled="true"<?php endif; ?>
>
    <?php if ($props['label']): ?>
        <label class="hph-dropdown__label" for="<?php echo esc_attr($trigger_id); ?>">
            <?php echo esc_html($props['label']); ?>
            <?php if ($props['required']): ?>
                <span class="hph-dropdown__required" aria-label="required">*</span>
            <?php endif; ?>
        </label>
    <?php endif; ?>

    <?php if ($props['description']): ?>
        <div class="hph-dropdown__description" id="<?php echo esc_attr($dropdown_id); ?>-description">
            <?php echo wp_kses_post($props['description']); ?>
        </div>
    <?php endif; ?>

    <div class="hph-dropdown__container">
        <button
            id="<?php echo esc_attr($trigger_id); ?>"
            class="<?php echo esc_attr(implode(' ', $trigger_classes)); ?>"
            type="button"
            aria-haspopup="true"
            aria-expanded="false"
            aria-controls="<?php echo esc_attr($menu_id); ?>"
            <?php if ($props['description']): ?>aria-describedby="<?php echo esc_attr($dropdown_id); ?>-description"<?php endif; ?>
            <?php if ($props['disabled']): ?>disabled<?php endif; ?>
        >
            <span class="hph-dropdown__trigger-content">
                <?php if ($props['multi_select'] && $props['show_selected_count']): ?>
                    <span class="hph-dropdown__selected-count" data-count="0">
                        <?php echo esc_html($props['placeholder']); ?>
                    </span>
                <?php else: ?>
                    <span class="hph-dropdown__trigger-text">
                        <?php echo esc_html($props['trigger_text']); ?>
                    </span>
                <?php endif; ?>
            </span>
            
            <?php if ($props['trigger_icon']): ?>
                <span class="hph-dropdown__trigger-icon" aria-hidden="true">
                    <?php
                    // Include icon (you may want to integrate with your icon system)
                    hph_component('base/icon', [
                        'name' => $props['trigger_icon'],
                        'size' => $props['size'] === 'sm' ? '16' : ($props['size'] === 'lg' ? '20' : '18')
                    ]);
                    ?>
                </span>
            <?php endif; ?>
        </button>

        <div
            id="<?php echo esc_attr($menu_id); ?>"
            class="<?php echo esc_attr(implode(' ', $menu_classes)); ?>"
            role="menu"
            aria-labelledby="<?php echo esc_attr($trigger_id); ?>"
            style="max-height: <?php echo esc_attr($props['max_height']); ?>;"
            hidden
        >
            <?php if ($props['searchable']): ?>
                <div class="hph-dropdown__search">
                    <input
                        type="text"
                        class="hph-dropdown__search-input"
                        placeholder="Search..."
                        aria-label="Search options"
                    >
                </div>
            <?php endif; ?>

            <?php if ($props['multi_select']): ?>
                <div class="hph-dropdown__controls">
                    <button type="button" class="hph-dropdown__select-all" data-action="select-all">
                        <?php echo esc_html($props['select_all_text']); ?>
                    </button>
                    <button type="button" class="hph-dropdown__clear-all" data-action="clear-all">
                        <?php echo esc_html($props['clear_all_text']); ?>
                    </button>
                </div>
            <?php endif; ?>

            <div class="hph-dropdown__content">
                <?php if ($props['ajax_url']): ?>
                    <div class="hph-dropdown__loading" hidden>
                        <span class="hph-dropdown__loading-text">
                            <?php echo esc_html($props['loading_text']); ?>
                        </span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($processed_items)): ?>
                    <div class="hph-dropdown__items">
                        <?php
                        $current_group = '';
                        foreach ($processed_items as $item):
                            // Handle groups
                            if (!empty($item['group']) && $item['group'] !== $current_group):
                                if ($current_group !== '') echo '</div>';
                                $current_group = $item['group'];
                                echo '<div class="hph-dropdown__group">';
                                echo '<div class="hph-dropdown__group-label">' . esc_html($current_group) . '</div>';
                            endif;

                            $item_classes = ['hph-dropdown__item'];
                            if ($item['disabled']) $item_classes[] = 'hph-dropdown__item--disabled';
                            if ($item['selected']) $item_classes[] = 'hph-dropdown__item--selected';
                            if (!empty($item['class'])) $item_classes[] = $item['class'];
                            if (!empty($props['item_class'])) $item_classes[] = $props['item_class'];

                            $item_attrs = [
                                'class' => implode(' ', $item_classes),
                                'role' => $props['multi_select'] ? 'menuitemcheckbox' : 'menuitem',
                                'data-value' => $item['value'],
                            ];

                            if ($item['disabled']) {
                                $item_attrs['aria-disabled'] = 'true';
                            }

                            if ($props['multi_select']) {
                                $item_attrs['aria-checked'] = $item['selected'] ? 'true' : 'false';
                            }

                            if ($item['href']):
                                $item_attrs['href'] = $item['href'];
                                $tag = 'a';
                            else:
                                $item_attrs['type'] = 'button';
                                $tag = 'button';
                            endif;
                            ?>
                            <<?php echo $tag; ?> <?php foreach ($item_attrs as $key => $value): ?><?php echo esc_attr($key); ?>="<?php echo esc_attr($value); ?>" <?php endforeach; ?>>
                                <?php if ($item['icon']): ?>
                                    <span class="hph-dropdown__item-icon" aria-hidden="true">
                                        <?php
                                        hph_component('base/icon', [
                                            'name' => $item['icon'],
                                            'size' => '16'
                                        ]);
                                        ?>
                                    </span>
                                <?php endif; ?>

                                <span class="hph-dropdown__item-text">
                                    <?php echo esc_html($item['text']); ?>
                                </span>

                                <?php if ($props['multi_select']): ?>
                                    <span class="hph-dropdown__item-checkbox" aria-hidden="true">
                                        <?php
                                        hph_component('base/icon', [
                                            'name' => 'check',
                                            'size' => '16'
                                        ]);
                                        ?>
                                    </span>
                                <?php endif; ?>
                            </<?php echo $tag; ?>>
                        <?php endforeach; ?>
                        
                        <?php if ($current_group !== ''): ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="hph-dropdown__empty">
                        <span class="hph-dropdown__empty-text">
                            <?php echo esc_html($props['no_results_text']); ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
