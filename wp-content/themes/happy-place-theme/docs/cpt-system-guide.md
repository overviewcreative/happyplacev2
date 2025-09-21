# CPT Archive & Single Template System

## Overview
This system provides a scalable, modular approach for creating archive and single templates for any Custom Post Type (CPT). Based on the successful implementation for `local_place` and `city` post types.

## System Architecture

### 1. Required Files for Each New CPT

#### Core Templates (required)
```
archive-{post_type}.php          # Main archive template
single-{post_type}.php           # Single post template  
template-parts/hero-search-{post_type}.php  # Search form for hero
```

#### Bridge Functions (required)
```
includes/adapters/{post_type}-card-adapter.php  # Universal card data adapter
includes/ajax/{post_type}-ajax.php               # AJAX load more handler
assets/js/archive-{post_type}.js                 # Frontend JavaScript
```

#### Styling (recommended)
```
assets/css/archive-{post_type}.css               # CPT-specific styles
```

### 2. Universal Hero System

All CPTs automatically inherit hero functionality via:
- `template-parts/archive-hero.php` (universal template)
- `includes/helpers/archive-hero-helpers.php` (data provider)
- `.hph-archive-hero-section` CSS class system

#### Adding New CPT to Hero System
1. Add configuration to `hpt_get_archive_hero_data()` in `archive-hero-helpers.php`
2. Add CSS gradient in `archive-map-fixes.css`
3. Create hero search form in `template-parts/hero-search-{post_type}.php`

### 3. Universal Card System

All CPTs use the same card display via:
- `template-parts/cards/universal-card.php`
- Bridge adapter functions that standardize data format

#### Required Bridge Function Pattern
```php
function hpt_get_card_data_{post_type}($post_id) {
    return [
        'id' => $post_id,
        'title' => get_the_title($post_id),
        'subtitle' => get_field('subtitle', $post_id) ?: get_field('location', $post_id),
        'price' => get_field('price', $post_id),
        'image' => get_the_post_thumbnail_url($post_id, 'medium'),
        'url' => get_permalink($post_id),
        'meta' => [
            'location' => get_field('location', $post_id),
            'date' => get_field('event_date', $post_id),
            'status' => get_field('status', $post_id)
        ],
        'badges' => [
            'featured' => get_field('featured', $post_id),
            'new' => (strtotime(get_the_date('c', $post_id)) > strtotime('-7 days'))
        ]
    ];
}
```

### 4. AJAX Load More System

Each CPT needs standardized AJAX handler following this pattern:

#### PHP Handler (`includes/ajax/{post_type}-ajax.php`)
```php
function handle_{post_type}_load_more() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], '{post_type}_load_more_nonce')) {
        wp_send_json_error('Invalid nonce');
    }
    
    // Get parameters
    $page = intval($_POST['page']);
    $posts_per_page = intval($_POST['posts_per_page']);
    $search = sanitize_text_field($_POST['search'] ?? '');
    $filters = $_POST['filters'] ?? [];
    
    // Build query
    $args = [
        'post_type' => '{post_type}',
        'posts_per_page' => $posts_per_page,
        'paged' => $page,
        'post_status' => 'publish'
    ];
    
    // Add search and filters
    if (!empty($search)) {
        $args['s'] = $search;
    }
    
    // Process custom filters (location, date, etc.)
    
    // Execute query and render cards
    $query = new WP_Query($args);
    // ... render logic
}
add_action('wp_ajax_{post_type}_load_more', 'handle_{post_type}_load_more');
add_action('wp_ajax_nopriv_{post_type}_load_more', 'handle_{post_type}_load_more');
```

#### JavaScript (`assets/js/archive-{post_type}.js`)
```javascript
class {PostType}Archive {
    constructor() {
        this.currentPage = 1;
        this.loading = false;
        this.init();
    }
    
    init() {
        this.bindEvents();
    }
    
    bindEvents() {
        // Load more button
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-load-more="{post_type}"]')) {
                e.preventDefault();
                this.loadMore();
            }
        });
        
        // Search and filters
        // ... standard event binding
    }
    
    async loadMore() {
        if (this.loading) return;
        
        this.loading = true;
        const formData = new FormData();
        formData.append('action', '{post_type}_load_more');
        formData.append('nonce', window.{post_type}Ajax.nonce);
        formData.append('page', this.currentPage + 1);
        // ... add other parameters
        
        try {
            const response = await fetch(window.{post_type}Ajax.url, {
                method: 'POST',
                body: formData
            });
            
            // Handle response
            // ... standard response handling
        } catch (error) {
            console.error('Load more failed:', error);
        } finally {
            this.loading = false;
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new {PostType}Archive();
});
```

### 5. Archive Template Pattern

Standard archive template structure:

```php
<?php
/**
 * Archive Template: {Post Type}
 */

get_header();

// Get hero data for this post type
$hero_data = hpt_get_archive_hero_data();
?>

<main class="hph-main-content">
    
    <?php 
    // Universal hero section
    hpt_render_archive_hero($hero_data); 
    ?>
    
    <div class="hph-{post_type}-archive-container hph-container">
        
        <?php if (have_posts()): ?>
            
            <div class="hph-{post_type}-grid hph-grid hph-grid-auto-fit-md hph-gap-lg">
                <?php while (have_posts()): the_post(); ?>
                    <?php 
                    // Use universal card system
                    get_template_part('template-parts/cards/universal-card', null, [
                        'post_type' => '{post_type}',
                        'post_id' => get_the_ID()
                    ]); 
                    ?>
                <?php endwhile; ?>
            </div>
            
            <?php 
            // Load more button (replaces pagination)
            if ($wp_query->max_num_pages > 1): 
            ?>
                <div class="hph-load-more-container hph-text-center hph-mt-xl">
                    <button 
                        class="hph-btn hph-btn-primary hph-btn-lg"
                        data-load-more="{post_type}"
                        data-current-page="1"
                        data-max-pages="<?php echo $wp_query->max_num_pages; ?>"
                        data-posts-per-page="<?php echo get_option('posts_per_page'); ?>"
                    >
                        <i class="fas fa-plus"></i>
                        Load More {Post Type}s
                    </button>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            
            <div class="hph-empty-state hph-text-center hph-py-3xl">
                <i class="fas fa-search hph-text-6xl hph-text-gray-300 hph-mb-lg"></i>
                <h2 class="hph-text-2xl hph-font-bold hph-mb-md">No {Post Type}s Found</h2>
                <p class="hph-text-gray-600 hph-mb-xl">Try adjusting your search or filter criteria.</p>
                <a href="<?php echo get_post_type_archive_link('{post_type}'); ?>" class="hph-btn hph-btn-primary">
                    View All {Post Type}s
                </a>
            </div>
            
        <?php endif; ?>
        
    </div>
    
</main>

<?php
get_footer();
```

### 6. Single Template Pattern

Standard single template structure:

```php
<?php
/**
 * Single Template: {Post Type}
 */

get_header();

while (have_posts()): the_post();
    $post_id = get_the_ID();
?>

<main class="hph-main-content">
    
    <article class="hph-{post_type}-single">
        
        <?php 
        // Hero section with {post_type} data
        get_template_part('template-parts/single-hero', null, [
            'post_type' => '{post_type}',
            'title' => get_the_title(),
            'subtitle' => get_field('subtitle') ?: get_field('location'),
            'image' => get_field('hero_image') ?: get_the_post_thumbnail_url('large'),
            'gallery' => get_field('gallery')
        ]); 
        ?>
        
        <div class="hph-{post_type}-content hph-container hph-py-3xl">
            
            <div class="hph-grid hph-lg:grid-cols-3 hph-gap-xl">
                
                <!-- Main Content -->
                <div class="hph-lg:col-span-2">
                    
                    <?php if (get_field('description')): ?>
                        <section class="hph-{post_type}-description hph-mb-2xl">
                            <h2 class="hph-text-2xl hph-font-bold hph-mb-lg">About This {Post Type}</h2>
                            <div class="hph-prose">
                                <?php echo wp_kses_post(get_field('description')); ?>
                            </div>
                        </section>
                    <?php endif; ?>
                    
                    <?php 
                    // CPT-specific sections
                    // Load additional template parts based on ACF fields
                    ?>
                    
                </div>
                
                <!-- Sidebar -->
                <div class="hph-lg:col-span-1">
                    
                    <?php 
                    // Standard sidebar components
                    get_template_part('template-parts/single-sidebar', null, [
                        'post_type' => '{post_type}',
                        'post_id' => $post_id
                    ]);
                    ?>
                    
                </div>
                
            </div>
            
        </div>
        
    </article>
    
</main>

<?php
endwhile;
get_footer();
```

## Quick Setup Checklist for New CPT

### Phase 1: Core Setup
- [ ] Add hero config to `archive-hero-helpers.php`
- [ ] Add CSS gradient to `archive-map-fixes.css`
- [ ] Create bridge adapter function
- [ ] Create archive template
- [ ] Create single template

### Phase 2: Enhanced Features
- [ ] Create hero search form template
- [ ] Build AJAX load more handler
- [ ] Add JavaScript for interactivity
- [ ] Create CPT-specific styling

### Phase 3: Testing & Polish
- [ ] Test hero display and colors
- [ ] Test universal card rendering
- [ ] Test load more functionality
- [ ] Test responsive design
- [ ] Test search and filters

## Naming Conventions

### Files
- Archives: `archive-{post_type}.php`
- Singles: `single-{post_type}.php`
- Bridge: `{post_type}-card-adapter.php`
- AJAX: `{post_type}-ajax.php`
- JS: `archive-{post_type}.js`

### Functions
- Bridge: `hpt_get_card_data_{post_type}()`
- AJAX: `handle_{post_type}_load_more()`
- Nonce: `{post_type}_load_more_nonce`

### CSS Classes
- Archive: `.hph-archive-{post_type}-hero`
- Container: `.hph-{post_type}-archive-container`
- Grid: `.hph-{post_type}-grid`

### JavaScript
- Class: `{PostType}Archive`
- Action: `{post_type}_load_more`
- Data attr: `data-load-more="{post_type}"`

This system ensures consistency, maintainability, and rapid development of new CPT archives and singles while leveraging all the existing universal components.