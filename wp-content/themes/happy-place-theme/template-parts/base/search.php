<?php
/**
 * Base Search Component
 * Accessible search input with comprehensive configuration options
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Search component configuration
 */
$defaults = [
    // Content
    'placeholder' => 'Search...',
    'value' => '',
    'name' => 'search',
    'form_action' => '',
    'method' => 'get',
    
    // Behavior
    'autocomplete' => true,
    'autofocus' => false,
    'live_search' => false,
    'min_length' => 1,
    'delay' => 300,
    'disabled' => false,
    'readonly' => false,
    
    // Style variants
    'variant' => 'default', // default, filled, outline, minimal
    'size' => 'md', // sm, md, lg
    'full_width' => false,
    'rounded' => false,
    
    // Icons
    'search_icon' => 'search',
    'clear_icon' => 'x',
    'show_search_icon' => true,
    'show_clear_button' => false,
    'icon_position' => 'left', // left, right
    
    // Button
    'show_button' => false,
    'button_text' => 'Search',
    'button_variant' => 'primary',
    
    // Autocomplete/suggestions
    'suggestions_url' => '',
    'max_suggestions' => 10,
    'show_recent' => false,
    'show_popular' => false,
    
    // Accessibility
    'label' => '',
    'description' => '',
    'required' => false,
    'error' => '',
    
    // CSS classes
    'container_class' => '',
    'input_class' => '',
    'button_class' => '',
    
    // Data attributes
    'data_attributes' => [],
    
    // Advanced features
    'voice_search' => false,
    'barcode_scanner' => false,
    'filter_button' => false,
    'category_filter' => [],
];

$props = wp_parse_args(hph_get_arg() ?? [], $defaults);

// Generate unique ID
$search_id = $props['id'] ?? 'hph-search-' . wp_unique_id();
$input_id = $search_id . '-input';
$suggestions_id = $search_id . '-suggestions';

// Build CSS classes
$container_classes = [
    'hph-search',
    'hph-search--' . $props['variant'],
    'hph-search--' . $props['size'],
];

if ($props['full_width']) {
    $container_classes[] = 'hph-search--full-width';
}

if ($props['rounded']) {
    $container_classes[] = 'hph-search--rounded';
}

if ($props['disabled']) {
    $container_classes[] = 'hph-search--disabled';
}

if ($props['show_button']) {
    $container_classes[] = 'hph-search--with-button';
}

if ($props['live_search']) {
    $container_classes[] = 'hph-search--live';
}

if ($props['error']) {
    $container_classes[] = 'hph-search--error';
}

if (!empty($props['container_class'])) {
    $container_classes[] = $props['container_class'];
}

$input_classes = [
    'hph-search__input',
];

if ($props['show_search_icon']) {
    $input_classes[] = 'hph-search__input--with-icon';
    $input_classes[] = 'hph-search__input--icon-' . $props['icon_position'];
}

if (!empty($props['input_class'])) {
    $input_classes[] = $props['input_class'];
}

// Data attributes
$data_attrs = [];

if ($props['live_search']) {
    $data_attrs['data-live-search'] = 'true';
    $data_attrs['data-min-length'] = $props['min_length'];
    $data_attrs['data-delay'] = $props['delay'];
}

if ($props['suggestions_url']) {
    $data_attrs['data-suggestions-url'] = $props['suggestions_url'];
    $data_attrs['data-max-suggestions'] = $props['max_suggestions'];
}

if (!empty($props['data_attributes'])) {
    $data_attrs = array_merge($data_attrs, $props['data_attributes']);
}

// Input attributes
$input_attrs = [
    'id' => $input_id,
    'name' => $props['name'],
    'type' => 'search',
    'class' => implode(' ', $input_classes),
    'placeholder' => $props['placeholder'],
    'value' => $props['value'],
];

if ($props['required']) {
    $input_attrs['required'] = 'required';
    $input_attrs['aria-required'] = 'true';
}

if ($props['disabled']) {
    $input_attrs['disabled'] = 'disabled';
}

if ($props['readonly']) {
    $input_attrs['readonly'] = 'readonly';
}

if ($props['autofocus']) {
    $input_attrs['autofocus'] = 'autofocus';
}

if (!$props['autocomplete']) {
    $input_attrs['autocomplete'] = 'off';
}

if ($props['description']) {
    $input_attrs['aria-describedby'] = $search_id . '-description';
}

if ($props['suggestions_url']) {
    $input_attrs['aria-autocomplete'] = 'list';
    $input_attrs['aria-controls'] = $suggestions_id;
    $input_attrs['aria-expanded'] = 'false';
}

// Form attributes
$form_attrs = [];
if ($props['form_action']) {
    $form_attrs['action'] = $props['form_action'];
}
$form_attrs['method'] = $props['method'];
$form_attrs['role'] = 'search';

// Build attributes string helper
function build_attrs($attrs) {
    $output = [];
    foreach ($attrs as $key => $value) {
        if ($value === true || $value === 'true') {
            $output[] = esc_attr($key);
        } elseif ($value !== false && $value !== null && $value !== '') {
            $output[] = esc_attr($key) . '="' . esc_attr($value) . '"';
        }
    }
    return implode(' ', $output);
}
?>

<div 
    id="<?php echo esc_attr($search_id); ?>"
    class="<?php echo esc_attr(implode(' ', $container_classes)); ?>"
    <?php foreach ($data_attrs as $key => $value): ?>
        <?php echo esc_attr($key); ?>="<?php echo esc_attr($value); ?>"
    <?php endforeach; ?>
>
    <?php if ($props['label']): ?>
        <label class="hph-search__label" for="<?php echo esc_attr($input_id); ?>">
            <?php echo esc_html($props['label']); ?>
            <?php if ($props['required']): ?>
                <span class="hph-search__required" aria-label="required">*</span>
            <?php endif; ?>
        </label>
    <?php endif; ?>

    <?php if ($props['description']): ?>
        <div class="hph-search__description" id="<?php echo esc_attr($search_id); ?>-description">
            <?php echo wp_kses_post($props['description']); ?>
        </div>
    <?php endif; ?>

    <form class="hph-search__form" <?php echo build_attrs($form_attrs); ?>>
        <div class="hph-search__input-container">
            <?php if ($props['show_search_icon'] && $props['icon_position'] === 'left'): ?>
                <span class="hph-search__icon hph-search__icon--search" aria-hidden="true">
                    <?php
                    hph_component('base/icon', [
                        'name' => $props['search_icon'],
                        'size' => $props['size'] === 'sm' ? '16' : ($props['size'] === 'lg' ? '20' : '18')
                    ]);
                    ?>
                </span>
            <?php endif; ?>

            <input <?php echo build_attrs($input_attrs); ?>>

            <?php if ($props['show_search_icon'] && $props['icon_position'] === 'right'): ?>
                <span class="hph-search__icon hph-search__icon--search" aria-hidden="true">
                    <?php
                    hph_component('base/icon', [
                        'name' => $props['search_icon'],
                        'size' => $props['size'] === 'sm' ? '16' : ($props['size'] === 'lg' ? '20' : '18')
                    ]);
                    ?>
                </span>
            <?php endif; ?>

            <?php if ($props['show_clear_button']): ?>
                <button 
                    type="button" 
                    class="hph-search__clear"
                    aria-label="Clear search"
                    data-action="clear"
                    hidden
                >
                    <?php
                    hph_component('base/icon', [
                        'name' => $props['clear_icon'],
                        'size' => '16'
                    ]);
                    ?>
                </button>
            <?php endif; ?>

            <?php if ($props['voice_search']): ?>
                <button 
                    type="button" 
                    class="hph-search__voice"
                    aria-label="Voice search"
                    data-action="voice-search"
                >
                    <?php
                    hph_component('base/icon', [
                        'name' => 'microphone',
                        'size' => '16'
                    ]);
                    ?>
                </button>
            <?php endif; ?>

            <?php if ($props['barcode_scanner']): ?>
                <button 
                    type="button" 
                    class="hph-search__scanner"
                    aria-label="Scan barcode"
                    data-action="barcode-scan"
                >
                    <?php
                    hph_component('base/icon', [
                        'name' => 'scan',
                        'size' => '16'
                    ]);
                    ?>
                </button>
            <?php endif; ?>

            <?php if ($props['filter_button']): ?>
                <button 
                    type="button" 
                    class="hph-search__filter"
                    aria-label="Search filters"
                    data-action="toggle-filters"
                >
                    <?php
                    hph_component('base/icon', [
                        'name' => 'filter',
                        'size' => '16'
                    ]);
                    ?>
                </button>
            <?php endif; ?>
        </div>

        <?php if ($props['show_button']): ?>
            <?php
            $button_classes = ['hph-search__button'];
            if (!empty($props['button_class'])) {
                $button_classes[] = $props['button_class'];
            }
            
            hph_component('base/button', [
                'text' => $props['button_text'],
                'type' => 'submit',
                'variant' => $props['button_variant'],
                'size' => $props['size'],
                'class' => implode(' ', $button_classes),
                'disabled' => $props['disabled']
            ]);
            ?>
        <?php endif; ?>

        <?php if (!empty($props['category_filter'])): ?>
            <div class="hph-search__category-filter">
                <select class="hph-search__category-select" name="category" aria-label="Search category">
                    <option value="">All Categories</option>
                    <?php foreach ($props['category_filter'] as $value => $label): ?>
                        <option value="<?php echo esc_attr($value); ?>">
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
    </form>

    <?php if ($props['suggestions_url'] || $props['show_recent'] || $props['show_popular']): ?>
        <div 
            id="<?php echo esc_attr($suggestions_id); ?>"
            class="hph-search__suggestions"
            role="listbox"
            aria-label="Search suggestions"
            hidden
        >
            <div class="hph-search__suggestions-content">
                <?php if ($props['show_recent']): ?>
                    <div class="hph-search__suggestions-section" data-section="recent">
                        <div class="hph-search__suggestions-header">Recent Searches</div>
                        <div class="hph-search__suggestions-list" data-list="recent"></div>
                    </div>
                <?php endif; ?>

                <?php if ($props['show_popular']): ?>
                    <div class="hph-search__suggestions-section" data-section="popular">
                        <div class="hph-search__suggestions-header">Popular Searches</div>
                        <div class="hph-search__suggestions-list" data-list="popular"></div>
                    </div>
                <?php endif; ?>

                <div class="hph-search__suggestions-section" data-section="results">
                    <div class="hph-search__suggestions-list" data-list="results"></div>
                </div>

                <div class="hph-search__suggestions-loading" hidden>
                    <span class="hph-search__loading-text">Searching...</span>
                </div>

                <div class="hph-search__suggestions-empty" hidden>
                    <span class="hph-search__empty-text">No suggestions found</span>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($props['error']): ?>
        <div class="hph-search__error" role="alert">
            <?php echo esc_html($props['error']); ?>
        </div>
    <?php endif; ?>
</div>
