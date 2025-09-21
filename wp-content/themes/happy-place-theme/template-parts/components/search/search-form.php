<?php
/**
 * Universal Search Form Component
 * 
 * Reusable search form that works across all post types
 * Supports both simple and advanced search modes
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Get component configuration
$config = $args ?? [];
$form_id = $config['form_id'] ?? 'universal-search';
$placeholder = $config['placeholder'] ?? __('Search properties, agents, cities, communities...', 'happy-place-theme');
$show_filters = $config['show_filters'] ?? true;
$show_post_type_selector = $config['show_post_type_selector'] ?? true;
$current_query = $config['current_query'] ?? '';
$current_post_type = $config['current_post_type'] ?? 'all';
$advanced_mode = $config['advanced_mode'] ?? false;
$form_action = $config['form_action'] ?? home_url('/advanced-search/');
$form_method = $config['form_method'] ?? 'GET';

// Define searchable post types
$post_types = [
    'all' => __('Everything', 'happy-place-theme'),
    'listing' => __('Properties', 'happy-place-theme'),
    'agent' => __('Agents', 'happy-place-theme'),
    'city' => __('Cities', 'happy-place-theme'),
    'community' => __('Communities', 'happy-place-theme')
];
?>

<div class="hph-search-form-wrapper" 
     data-form-id="<?php echo esc_attr($form_id); ?>"
     style="background: var(--hph-white); border-radius: var(--hph-radius-lg); padding: var(--hph-padding-lg); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);">
    <form 
        class="hph-search-form <?php echo $advanced_mode ? 'hph-advanced-mode' : 'hph-simple-mode'; ?>" 
        id="<?php echo esc_attr($form_id); ?>"
        action="<?php echo esc_url($form_action); ?>" 
        method="<?php echo esc_attr($form_method); ?>"
        role="search"
        style="display: flex; flex-direction: column; gap: var(--hph-gap-lg);"
    >
        <div class="search-form-header">
            <?php if ($show_post_type_selector): ?>
                <div class="post-type-selector">
                    <label for="search_type" class="screen-reader-text">
                        <?php _e('Search in:', 'happy-place-theme'); ?>
                    </label>
                    <select name="post_type" id="search_type" class="search-type-select">
                        <?php foreach ($post_types as $value => $label): ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($current_post_type, $value); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="search-input-wrapper">
                <label for="search_query" class="screen-reader-text">
                    <?php _e('Search query', 'happy-place-theme'); ?>
                </label>
                <input 
                    type="search" 
                    name="s" 
                    id="search_query"
                    class="search-input hph-search-input"
                    placeholder="<?php echo esc_attr($placeholder); ?>"
                    value="<?php echo esc_attr($current_query); ?>"
                    autocomplete="off"
                    data-autocomplete="enabled"
                />
                <?php get_template_part('template-parts/components/search/search-autocomplete', null, [
                    'input_id' => 'search_query',
                    'container_id' => 'search-form-autocomplete',
                    'post_types' => ['listing', 'agent', 'city', 'community'],
                    'max_suggestions' => 8
                ]); ?>
                <button type="submit" class="search-submit">
                    <span class="search-icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    <span class="search-text"><?php _e('Search', 'happy-place-theme'); ?></span>
                </button>
            </div>
        </div>

        <?php if ($show_filters && $advanced_mode): ?>
            <div class="search-filters-wrapper">
                <button type="button" class="toggle-filters" aria-expanded="false">
                    <span class="filter-icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    <span><?php _e('Advanced Filters', 'happy-place-theme'); ?></span>
                </button>

                <div class="filters-panel" style="display: none;">
                    <?php 
                    // Include advanced filters based on post type
                    hph_component('search-filters', [
                        'current_post_type' => $current_post_type,
                        'form_id' => $form_id
                    ]); 
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Autocomplete suggestions container -->
        <div class="search-suggestions" style="display: none;">
            <ul class="suggestions-list" role="listbox"></ul>
        </div>

        <!-- Hidden fields for maintaining state -->
        <?php if (isset($_GET['view'])): ?>
            <input type="hidden" name="view" value="<?php echo esc_attr($_GET['view']); ?>">
        <?php endif; ?>
        
        <?php if (isset($_GET['sort'])): ?>
            <input type="hidden" name="sort" value="<?php echo esc_attr($_GET['sort']); ?>">
        <?php endif; ?>
        
        <?php if (isset($_GET['per_page'])): ?>
            <input type="hidden" name="per_page" value="<?php echo esc_attr($_GET['per_page']); ?>">
        <?php endif; ?>
        
        <?php wp_nonce_field('hph_search_nonce', 'search_nonce'); ?>
    </form>

    <!-- Quick search suggestions -->
    <?php if (!$advanced_mode): ?>
        <div class="quick-search-suggestions">
            <span class="suggestions-label"><?php _e('Try:', 'happy-place-theme'); ?></span>
            <button type="button" class="quick-search-btn" data-query="3 bedroom house">
                <?php _e('3 bedroom house', 'happy-place-theme'); ?>
            </button>
            <button type="button" class="quick-search-btn" data-query="waterfront property">
                <?php _e('waterfront property', 'happy-place-theme'); ?>
            </button>
            <button type="button" class="quick-search-btn" data-query="new construction">
                <?php _e('new construction', 'happy-place-theme'); ?>
            </button>
            <button type="button" class="quick-search-btn" data-query="top agents">
                <?php _e('top agents', 'happy-place-theme'); ?>
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- JavaScript functionality is handled by search-filters-enhanced.js -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('<?php echo esc_js($form_id); ?>');
    const quickSearchBtns = document.querySelectorAll('.quick-search-btn');
    
    // Quick search functionality only (other functionality handled by enhanced script)
    quickSearchBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const searchInput = searchForm.querySelector('.hph-search-input, .search-input');
            if (searchInput) {
                searchInput.value = this.dataset.query;
                searchForm.submit();
            }
        });
    });
    
    // Mark form as enhanced-ready
    if (searchForm) {
        searchForm.classList.add('hph-enhanced-search');
        searchForm.setAttribute('data-enhanced', 'true');
    }
});
</script>
