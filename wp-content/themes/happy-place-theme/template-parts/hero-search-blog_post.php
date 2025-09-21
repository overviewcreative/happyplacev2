<?php
/**
 * Blog Post Search Form
 * 
 * Search form for blog posts with category and tag filtering
 * 
 * @package Happy_Place_Theme
 */

// Get current filters
$current_search = get_search_query();
$current_category = get_query_var('blog_category');
$current_tag = get_query_var('blog_tag');
$current_date = get_query_var('date_query');

// Get categories and tags
$categories = get_terms([
    'taxonomy' => 'blog_category',
    'hide_empty' => true,
    'orderby' => 'name'
]);

$tags = get_terms([
    'taxonomy' => 'blog_tag',
    'hide_empty' => true,
    'orderby' => 'count',
    'order' => 'DESC',
    'number' => 20
]);
?>

<div class="hph-hero-search-form hph-blog-search">
    <form method="get" action="<?php echo esc_url(get_post_type_archive_link('blog_post')); ?>" class="hph-search-form">
        
        <!-- Search Input -->
        <div class="hph-search-field hph-flex-1">
            <div class="hph-input-group">
                <input type="text" 
                       name="s" 
                       value="<?php echo esc_attr($current_search); ?>"
                       placeholder="Search blog posts..."
                       class="hph-form-input hph-search-input">
                <button type="submit" class="hph-search-submit">
                    <i class="fas fa-search"></i>
                    <span class="hph-sr-only">Search</span>
                </button>
            </div>
        </div>
        
        <!-- Filters Row -->
        <div class="hph-search-filters hph-grid hph-sm:grid-cols-2 hph-lg:grid-cols-3 hph-gap-md hph-mt-md">
            
            <!-- Category Filter -->
            <div class="hph-filter-group">
                <label for="blog_category" class="hph-filter-label">Category</label>
                <select name="blog_category" id="blog_category" class="hph-form-select">
                    <option value="">All Categories</option>
                    <?php if ($categories && !is_wp_error($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo esc_attr($category->slug); ?>" 
                                    <?php selected($current_category, $category->slug); ?>>
                                <?php echo esc_html($category->name); ?> (<?php echo $category->count; ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <!-- Tag Filter -->
            <div class="hph-filter-group">
                <label for="blog_tag" class="hph-filter-label">Tag</label>
                <select name="blog_tag" id="blog_tag" class="hph-form-select">
                    <option value="">All Tags</option>
                    <?php if ($tags && !is_wp_error($tags)): ?>
                        <?php foreach ($tags as $tag): ?>
                            <option value="<?php echo esc_attr($tag->slug); ?>" 
                                    <?php selected($current_tag, $tag->slug); ?>>
                                <?php echo esc_html($tag->name); ?> (<?php echo $tag->count; ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <!-- Date Filter -->
            <div class="hph-filter-group">
                <label for="date_filter" class="hph-filter-label">Date</label>
                <select name="date_filter" id="date_filter" class="hph-form-select">
                    <option value="">All Time</option>
                    <option value="today" <?php selected(get_query_var('date_filter'), 'today'); ?>>Today</option>
                    <option value="week" <?php selected(get_query_var('date_filter'), 'week'); ?>>This Week</option>
                    <option value="month" <?php selected(get_query_var('date_filter'), 'month'); ?>>This Month</option>
                    <option value="year" <?php selected(get_query_var('date_filter'), 'year'); ?>>This Year</option>
                </select>
            </div>
            
        </div>
        
        <!-- Hidden field to specify post type -->
        <input type="hidden" name="post_type" value="blog_post">
        
    </form>
    
    <!-- Active Filters Display -->
    <?php 
    $active_filters = [];
    
    if (!empty($current_search)) {
        $active_filters[] = [
            'type' => 'search',
            'label' => 'Search: "' . $current_search . '"',
            'remove_url' => remove_query_arg('s')
        ];
    }
    
    if (!empty($current_category)) {
        $cat_term = get_term_by('slug', $current_category, 'blog_category');
        if ($cat_term) {
            $active_filters[] = [
                'type' => 'category',
                'label' => 'Category: ' . $cat_term->name,
                'remove_url' => remove_query_arg('blog_category')
            ];
        }
    }
    
    if (!empty($current_tag)) {
        $tag_term = get_term_by('slug', $current_tag, 'blog_tag');
        if ($tag_term) {
            $active_filters[] = [
                'type' => 'tag',
                'label' => 'Tag: ' . $tag_term->name,
                'remove_url' => remove_query_arg('blog_tag')
            ];
        }
    }
    
    if (!empty($active_filters)):
    ?>
        <div class="hph-active-filters hph-mt-lg">
            <div class="hph-flex hph-items-center hph-gap-sm hph-flex-wrap">
                <span class="hph-text-sm hph-text-white hph-opacity-90">Active filters:</span>
                
                <?php foreach ($active_filters as $filter): ?>
                    <div class="hph-active-filter hph-flex hph-items-center hph-gap-xs hph-px-sm hph-py-xs hph-bg-white hph-bg-opacity-20 hph-rounded hph-text-sm">
                        <span><?php echo esc_html($filter['label']); ?></span>
                        <a href="<?php echo esc_url($filter['remove_url']); ?>" 
                           class="hph-filter-remove hph-text-white hph-opacity-75 hover:hph-opacity-100">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
                
                <a href="<?php echo esc_url(get_post_type_archive_link('blog_post')); ?>" 
                   class="hph-clear-all hph-text-sm hph-text-white hph-opacity-90 hover:hph-opacity-100 hph-underline">
                    Clear all
                </a>
            </div>
        </div>
    <?php endif; ?>
    
</div>

<style>
.hph-blog-search .hph-search-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    max-width: 800px;
    margin: 0 auto;
}

.hph-blog-search .hph-input-group {
    position: relative;
    display: flex;
}

.hph-blog-search .hph-search-input {
    flex: 1;
    padding: 0.75rem 3rem 0.75rem 1rem;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 0.5rem;
    background: rgba(255, 255, 255, 0.1);
    color: white;
    font-size: 1rem;
}

.hph-blog-search .hph-search-input::placeholder {
    color: rgba(255, 255, 255, 0.7);
}

.hph-blog-search .hph-search-submit {
    position: absolute;
    right: 0.5rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: white;
    font-size: 1.1rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 0.25rem;
    transition: background-color 0.2s;
}

.hph-blog-search .hph-search-submit:hover {
    background: rgba(255, 255, 255, 0.1);
}

.hph-blog-search .hph-filter-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: white;
    margin-bottom: 0.25rem;
    opacity: 0.9;
}

.hph-blog-search .hph-form-select {
    width: 100%;
    padding: 0.5rem;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 0.375rem;
    background: rgba(255, 255, 255, 0.1);
    color: white;
    font-size: 0.875rem;
}

.hph-blog-search .hph-form-select option {
    background: #374151;
    color: white;
}

@media (max-width: 640px) {
    .hph-blog-search .hph-search-filters {
        grid-template-columns: 1fr;
    }
}
</style>