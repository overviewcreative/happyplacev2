<?php
/**
 * Blog/Posts Hero Search Form
 * 
 * Search and filter form for blog post archives
 * 
 * @package Happy_Place_Theme
 */

// Get search query
$search_query = get_search_query();

// Get current category
$current_category = '';
if (is_category()) {
    $current_category = get_queried_object()->slug;
}

// Get all categories for filter
$categories = get_categories([
    'hide_empty' => true,
    'exclude' => [1] // Exclude "Uncategorized"
]);
?>

<div class="hph-hero-search-form hph-blog-search-form">
    <form class="hph-search-form" method="get" action="<?php echo esc_url(home_url('/')); ?>">
        
        <div class="hph-search-form-row">
            
            <!-- Search Input -->
            <div class="hph-search-input-group">
                <input 
                    type="text" 
                    name="s" 
                    class="hph-search-input hph-form-input" 
                    placeholder="Search articles, news, and insights..."
                    value="<?php echo esc_attr($search_query); ?>"
                    aria-label="Search blog posts"
                >
                <i class="fas fa-search hph-search-icon"></i>
            </div>
            
            <!-- Category Filter -->
            <div class="hph-filter-group">
                <select name="category_name" class="hph-form-select" aria-label="Filter by category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo esc_attr($category->slug); ?>" <?php selected($current_category, $category->slug); ?>>
                            <?php echo esc_html($category->name); ?> (<?php echo $category->count; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Date Filter -->
            <div class="hph-filter-group">
                <select name="date_filter" class="hph-form-select" aria-label="Filter by date">
                    <option value="">All Time</option>
                    <option value="this_week" <?php selected(get_query_var('date_filter'), 'this_week'); ?>>This Week</option>
                    <option value="this_month" <?php selected(get_query_var('date_filter'), 'this_month'); ?>>This Month</option>
                    <option value="this_year" <?php selected(get_query_var('date_filter'), 'this_year'); ?>>This Year</option>
                </select>
            </div>
            
            <!-- Author Filter -->
            <?php 
            $authors = get_users([
                'who' => 'authors',
                'has_published_posts' => ['post'],
                'fields' => ['ID', 'display_name']
            ]);
            if (!empty($authors)): 
            ?>
                <div class="hph-filter-group">
                    <select name="author" class="hph-form-select" aria-label="Filter by author">
                        <option value="">All Authors</option>
                        <?php foreach ($authors as $author): ?>
                            <option value="<?php echo esc_attr($author->ID); ?>" <?php selected(get_query_var('author'), $author->ID); ?>>
                                <?php echo esc_html($author->display_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            
            <!-- Search Button -->
            <button type="submit" class="hph-btn hph-btn-primary hph-search-btn">
                <i class="fas fa-search"></i>
                <span class="hph-btn-text">Search</span>
            </button>
            
        </div>
        
        <!-- Active Filters Display -->
        <?php if ($search_query || $current_category || get_query_var('author') || get_query_var('date_filter')): ?>
            <div class="hph-active-filters">
                
                <?php if ($search_query): ?>
                    <span class="hph-filter-badge">
                        Search: "<?php echo esc_html($search_query); ?>"
                        <a href="<?php echo esc_url(remove_query_arg('s')); ?>" class="hph-filter-remove" aria-label="Remove search filter">×</a>
                    </span>
                <?php endif; ?>
                
                <?php if ($current_category): ?>
                    <span class="hph-filter-badge">
                        Category: <?php echo esc_html(get_category_by_slug($current_category)->name); ?>
                        <a href="<?php echo esc_url(get_post_type_archive_link('post')); ?>" class="hph-filter-remove" aria-label="Remove category filter">×</a>
                    </span>
                <?php endif; ?>
                
                <?php if (get_query_var('author')): ?>
                    <span class="hph-filter-badge">
                        Author: <?php echo esc_html(get_the_author_meta('display_name', get_query_var('author'))); ?>
                        <a href="<?php echo esc_url(remove_query_arg('author')); ?>" class="hph-filter-remove" aria-label="Remove author filter">×</a>
                    </span>
                <?php endif; ?>
                
                <?php if (get_query_var('date_filter')): ?>
                    <span class="hph-filter-badge">
                        Date: <?php echo esc_html(ucwords(str_replace('_', ' ', get_query_var('date_filter')))); ?>
                        <a href="<?php echo esc_url(remove_query_arg('date_filter')); ?>" class="hph-filter-remove" aria-label="Remove date filter">×</a>
                    </span>
                <?php endif; ?>
                
                <a href="<?php echo esc_url(get_post_type_archive_link('post')); ?>" class="hph-clear-all-filters">
                    <i class="fas fa-times-circle"></i> Clear All Filters
                </a>
                
            </div>
        <?php endif; ?>
        
    </form>
</div>