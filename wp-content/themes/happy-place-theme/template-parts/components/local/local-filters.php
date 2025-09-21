<?php
/**
 * Local Content Filters Component
 * 
 * @package HappyPlaceTheme
 * @since 1.0.0
 * 
 * @param array $args {
 *     @type string $post_type     Post type being filtered ('local_place' or 'local_event')
 *     @type bool   $show_map_toggle Whether to show map/grid toggle
 *     @type bool   $show_search    Whether to show search bar
 *     @type array  $active_filters Currently active filters
 * }
 */

$post_type = $args['post_type'] ?? 'local_place';
$show_map_toggle = $args['show_map_toggle'] ?? true;
$show_search = $args['show_search'] ?? true;
$active_filters = $args['active_filters'] ?? [];

// Get current filter values from URL
$search_query = $_GET['search'] ?? '';
$city_filter = $_GET['city'] ?? '';
$category_filter = $_GET['category'] ?? '';
$price_filter = $_GET['price'] ?? '';
$date_filter = $_GET['date'] ?? '';

// Get cities for dropdown
$cities = get_posts([
    'post_type' => 'city',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
    'fields' => 'ids'
]);

// Define categories based on post type
$categories = [];
if ($post_type === 'local_place') {
    $categories = [
        'restaurant' => 'Restaurants',
        'cafe' => 'Cafes & Coffee',
        'bar' => 'Bars & Nightlife',
        'shopping' => 'Shopping',
        'park' => 'Parks & Recreation',
        'museum' => 'Museums & Culture',
        'entertainment' => 'Entertainment',
        'service' => 'Services',
        'hotel' => 'Hotels & Lodging'
    ];
} elseif ($post_type === 'local_event') {
    $categories = [
        'music' => 'Music & Concerts',
        'sports' => 'Sports',
        'arts' => 'Arts & Theater',
        'food' => 'Food & Drink',
        'family' => 'Family & Kids',
        'festival' => 'Festivals',
        'community' => 'Community',
        'education' => 'Classes & Workshops'
    ];
}

// Price ranges for places
$price_ranges = [
    '$' => '$',
    '$$' => '$$',
    '$$$' => '$$$',
    '$$$$' => '$$$$'
];

// Date ranges for events
$date_ranges = [
    'today' => 'Today',
    'tomorrow' => 'Tomorrow',
    'this-week' => 'This Week',
    'this-weekend' => 'This Weekend',
    'next-week' => 'Next Week',
    'this-month' => 'This Month'
];
?>

<div class="hph-filters hph-filters--local" data-post-type="<?php echo esc_attr($post_type); ?>">
    <form class="hph-filters__form" method="get" action="">
        
        <!-- Search Bar -->
        <?php if ($show_search): ?>
        <div class="hph-filters__search">
            <div class="hph-search-input">
                <input 
                    type="search" 
                    name="search" 
                    class="hph-search-input__field" 
                    placeholder="Search <?php echo $post_type === 'local_event' ? 'events' : 'places'; ?>..."
                    value="<?php echo esc_attr($search_query); ?>"
                >
                <button type="submit" class="hph-search-input__button">
                    <i class="hph-icon hph-icon--search"></i>
                </button>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Filter Controls -->
        <div class="hph-filters__controls">
            
            <!-- City Filter -->
            <?php if (!empty($cities)): ?>
            <div class="hph-filter-group">
                <label class="hph-filter-group__label" for="filter-city">
                    <i class="hph-icon hph-icon--location"></i>
                    City
                </label>
                <select name="city" id="filter-city" class="hph-filter-group__select">
                    <option value="">All Cities</option>
                    <?php foreach ($cities as $city_id): ?>
                        <option value="<?php echo esc_attr($city_id); ?>" <?php selected($city_filter, $city_id); ?>>
                            <?php echo esc_html(get_the_title($city_id)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            
            <!-- Category Filter -->
            <?php if (!empty($categories)): ?>
            <div class="hph-filter-group">
                <label class="hph-filter-group__label" for="filter-category">
                    <i class="hph-icon hph-icon--category"></i>
                    Category
                </label>
                <select name="category" id="filter-category" class="hph-filter-group__select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $value => $label): ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected($category_filter, $value); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            
            <!-- Price Filter (Places only) -->
            <?php if ($post_type === 'local_place'): ?>
            <div class="hph-filter-group">
                <label class="hph-filter-group__label" for="filter-price">
                    <i class="hph-icon hph-icon--dollar"></i>
                    Price
                </label>
                <select name="price" id="filter-price" class="hph-filter-group__select">
                    <option value="">Any Price</option>
                    <?php foreach ($price_ranges as $value => $label): ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected($price_filter, $value); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Family Friendly Toggle -->
            <div class="hph-filter-group hph-filter-group--checkbox">
                <label class="hph-checkbox">
                    <input type="checkbox" name="family_friendly" value="1" <?php checked(!empty($_GET['family_friendly'])); ?>>
                    <span class="hph-checkbox__label">
                        <i class="hph-icon hph-icon--family"></i>
                        Family Friendly
                    </span>
                </label>
            </div>
            <?php endif; ?>
            
            <!-- Date Filter (Events only) -->
            <?php if ($post_type === 'local_event'): ?>
            <div class="hph-filter-group">
                <label class="hph-filter-group__label" for="filter-date">
                    <i class="hph-icon hph-icon--calendar"></i>
                    When
                </label>
                <select name="date" id="filter-date" class="hph-filter-group__select">
                    <option value="">Any Time</option>
                    <?php foreach ($date_ranges as $value => $label): ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected($date_filter, $value); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Free Events Toggle -->
            <div class="hph-filter-group hph-filter-group--checkbox">
                <label class="hph-checkbox">
                    <input type="checkbox" name="free_only" value="1" <?php checked(!empty($_GET['free_only'])); ?>>
                    <span class="hph-checkbox__label">
                        <i class="hph-icon hph-icon--tag"></i>
                        Free Events Only
                    </span>
                </label>
            </div>
            <?php endif; ?>
            
            <!-- Filter Actions -->
            <div class="hph-filter-group hph-filter-group--actions">
                <button type="submit" class="hph-btn hph-btn-primary hph-btn-small">
                    Apply Filters
                </button>
                
                <?php if (!empty($_GET)): ?>
                <a href="<?php echo esc_url(get_post_type_archive_link($post_type)); ?>" class="hph-btn hph-btn-ghost hph-btn-small">
                    Clear All
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- View Toggle -->
        <?php if ($show_map_toggle): ?>
        <div class="hph-filters__view-toggle">
            <div class="hph-view-toggle" data-view-toggle>
                <button type="button" class="hph-view-toggle__btn is-active" data-view="grid">
                    <i class="hph-icon hph-icon--grid"></i>
                    Grid
                </button>
                <button type="button" class="hph-view-toggle__btn" data-view="list">
                    <i class="hph-icon hph-icon--list"></i>
                    List
                </button>
                <?php if ($post_type === 'local_place'): ?>
                <button type="button" class="hph-view-toggle__btn" data-view="map">
                    <i class="hph-icon hph-icon--map"></i>
                    Map
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </form>
    
    <!-- Active Filters Display -->
    <?php if (!empty($_GET)): ?>
    <div class="hph-filters__active">
        <span class="hph-filters__active-label">Active Filters:</span>
        <div class="hph-filters__active-tags">
            <?php if ($search_query): ?>
            <span class="hph-tag hph-tag--filter">
                Search: <?php echo esc_html($search_query); ?>
                <a href="<?php echo esc_url(remove_query_arg('search')); ?>" class="hph-tag__remove">
                    <i class="hph-icon hph-icon--close"></i>
                </a>
            </span>
            <?php endif; ?>
            
            <?php if ($city_filter): ?>
            <span class="hph-tag hph-tag--filter">
                City: <?php echo esc_html(get_the_title($city_filter)); ?>
                <a href="<?php echo esc_url(remove_query_arg('city')); ?>" class="hph-tag__remove">
                    <i class="hph-icon hph-icon--close"></i>
                </a>
            </span>
            <?php endif; ?>
            
            <?php if ($category_filter): ?>
            <span class="hph-tag hph-tag--filter">
                Category: <?php echo esc_html($categories[$category_filter] ?? $category_filter); ?>
                <a href="<?php echo esc_url(remove_query_arg('category')); ?>" class="hph-tag__remove">
                    <i class="hph-icon hph-icon--close"></i>
                </a>
            </span>
            <?php endif; ?>
            
            <?php if ($price_filter): ?>
            <span class="hph-tag hph-tag--filter">
                Price: <?php echo esc_html($price_filter); ?>
                <a href="<?php echo esc_url(remove_query_arg('price')); ?>" class="hph-tag__remove">
                    <i class="hph-icon hph-icon--close"></i>
                </a>
            </span>
            <?php endif; ?>
            
            <?php if (!empty($_GET['family_friendly'])): ?>
            <span class="hph-tag hph-tag--filter">
                Family Friendly
                <a href="<?php echo esc_url(remove_query_arg('family_friendly')); ?>" class="hph-tag__remove">
                    <i class="hph-icon hph-icon--close"></i>
                </a>
            </span>
            <?php endif; ?>
            
            <?php if (!empty($_GET['free_only'])): ?>
            <span class="hph-tag hph-tag--filter">
                Free Only
                <a href="<?php echo esc_url(remove_query_arg('free_only')); ?>" class="hph-tag__remove">
                    <i class="hph-icon hph-icon--close"></i>
                </a>
            </span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
