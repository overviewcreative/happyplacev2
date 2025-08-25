<?php
/**
 * Base Card List Component - Vertical list layout for displaying cards
 *
 * @package HappyPlaceTheme
 */

// Default attributes
$list_args = wp_parse_args($args ?? [], [
    'posts' => [], // Array of post objects or post IDs
    'post_type' => 'post',
    'card_args' => [], // Arguments to pass to each card
    'query_args' => [], // WP_Query args if posts not provided
    'show_pagination' => false,
    'pagination_type' => 'numbers', // numbers, load_more, infinite
    'empty_message' => 'No items found.',
    'loading' => false,
    'container_class' => '',
    'list_class' => '',
    'spacing' => 4, // Tailwind spacing between cards
    'dividers' => false, // Show dividers between cards
    'compact' => false, // Compact list style
    'numbered' => false, // Show numbers/order
    'search_form' => false, // Show search input
    'sort_controls' => false, // Show sort dropdown
    'filter_controls' => false, // Show filter buttons
]);

// Build list classes
$list_classes = ['hph-card-list', 'hph-space-y-' . $list_args['spacing']];

if ($list_args['dividers']) {
    $list_classes[] = 'hph-divide-y hph-divide-gray-200';
}

if ($list_args['compact']) {
    $list_classes[] = 'hph-card-list-compact';
}

if ($list_args['numbered']) {
    $list_classes[] = 'hph-card-list-numbered';
}

if ($list_args['list_class']) {
    $list_classes[] = $list_args['list_class'];
}

$list_class = implode(' ', $list_classes);

// Container classes
$container_classes = ['hph-card-list-container'];
if ($list_args['container_class']) {
    $container_classes[] = $list_args['container_class'];
}
$container_class = implode(' ', $container_classes);

// Get posts if not provided
$posts = $list_args['posts'];
$query = null;

if (empty($posts) && !empty($list_args['query_args'])) {
    $query_args = wp_parse_args($list_args['query_args'], [
        'post_type' => $list_args['post_type'],
        'post_status' => 'publish',
        'posts_per_page' => 10
    ]);
    
    $query = new WP_Query($query_args);
    $posts = $query->posts;
}

// Convert post IDs to post objects if needed
if (!empty($posts) && is_numeric($posts[0])) {
    $posts = array_map('get_post', $posts);
}

$unique_id = 'hph-list-' . uniqid();
?>

<div class="<?php echo esc_attr($container_class); ?>" id="<?php echo esc_attr($unique_id); ?>">
    
    <!-- List Controls -->
    <?php if ($list_args['filter_controls'] || $list_args['sort_controls'] || $list_args['search_form']) : ?>
        <div class="hph-list-controls hph-flex hph-flex-wrap hph-items-center hph-justify-between hph-gap-4 hph-mb-6">
            
            <!-- Left Controls -->
            <div class="hph-list-controls-left hph-flex hph-items-center hph-gap-4">
                
                <!-- Search Form -->
                <?php if ($list_args['search_form']) : ?>
                    <div class="hph-list-search">
                        <div class="hph-relative">
                            <input type="text" 
                                   class="hph-form-control hph-form-control-sm hph-pl-8" 
                                   placeholder="Search..."
                                   id="<?php echo esc_attr($unique_id); ?>-search">
                            <i class="fas fa-search hph-absolute hph-left-2 hph-top-1/2 hph-transform hph--translate-y-1/2 hph-text-muted"></i>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Filter Controls -->
                <?php if ($list_args['filter_controls']) : ?>
                    <div class="hph-list-filters">
                        <div class="hph-btn-group" role="group">
                            <button class="hph-btn hph-btn-outline hph-btn-sm hph-filter-btn active" data-filter="*">
                                All
                            </button>
                            <!-- Dynamic filter buttons based on post type -->
                            <?php if ($list_args['post_type'] === 'listing') : ?>
                                <button class="hph-btn hph-btn-outline hph-btn-sm hph-filter-btn" data-filter=".for-sale">
                                    For Sale
                                </button>
                                <button class="hph-btn hph-btn-outline hph-btn-sm hph-filter-btn" data-filter=".for-rent">
                                    For Rent
                                </button>
                                <button class="hph-btn hph-btn-outline hph-btn-sm hph-filter-btn" data-filter=".sold">
                                    Sold
                                </button>
                            <?php elseif ($list_args['post_type'] === 'transaction') : ?>
                                <button class="hph-btn hph-btn-outline hph-btn-sm hph-filter-btn" data-filter=".active">
                                    Active
                                </button>
                                <button class="hph-btn hph-btn-outline hph-btn-sm hph-filter-btn" data-filter=".pending">
                                    Pending
                                </button>
                                <button class="hph-btn hph-btn-outline hph-btn-sm hph-filter-btn" data-filter=".closed">
                                    Closed
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
            </div>
            
            <!-- Right Controls -->
            <div class="hph-list-controls-right hph-flex hph-items-center hph-gap-4">
                
                <!-- Sort Controls -->
                <?php if ($list_args['sort_controls']) : ?>
                    <div class="hph-list-sort">
                        <select class="hph-form-control hph-form-control-sm" id="<?php echo esc_attr($unique_id); ?>-sort">
                            <option value="date-desc">Newest First</option>
                            <option value="date-asc">Oldest First</option>
                            <option value="title-asc">Title A-Z</option>
                            <option value="title-desc">Title Z-A</option>
                            <?php if ($list_args['post_type'] === 'listing') : ?>
                                <option value="price-asc">Price Low to High</option>
                                <option value="price-desc">Price High to Low</option>
                            <?php elseif ($list_args['post_type'] === 'transaction') : ?>
                                <option value="amount-asc">Amount Low to High</option>
                                <option value="amount-desc">Amount High to Low</option>
                                <option value="closing-asc">Closing Date</option>
                            <?php endif; ?>
                        </select>
                    </div>
                <?php endif; ?>
                
                <!-- Results Count -->
                <?php if (!empty($posts)) : ?>
                    <div class="hph-results-count hph-text-sm hph-text-muted">
                        <?php 
                        $total = $query ? $query->found_posts : count($posts);
                        printf(
                            _n('%d result', '%d results', $total, 'happy-place-theme'),
                            number_format_i18n($total)
                        );
                        ?>
                    </div>
                <?php endif; ?>
                
            </div>
            
        </div>
    <?php endif; ?>
    
    <!-- Loading State -->
    <?php if ($list_args['loading']) : ?>
        <div class="hph-list-loading hph-text-center hph-py-12">
            <div class="hph-spinner hph-spinner-lg hph-text-primary hph-mb-4">
                <i class="fas fa-circle-notch fa-spin"></i>
            </div>
            <p class="hph-text-muted">Loading...</p>
        </div>
    
    <!-- Empty State -->
    <?php elseif (empty($posts)) : ?>
        <div class="hph-list-empty hph-text-center hph-py-12">
            <div class="hph-empty-icon hph-text-6xl hph-text-muted hph-mb-4">
                <?php switch ($list_args['post_type']) :
                    case 'listing': ?>
                        <i class="fas fa-home"></i>
                        <?php break;
                    case 'agent': ?>
                        <i class="fas fa-users"></i>
                        <?php break;
                    case 'open_house': ?>
                        <i class="fas fa-calendar"></i>
                        <?php break;
                    case 'transaction': ?>
                        <i class="fas fa-handshake"></i>
                        <?php break;
                    default: ?>
                        <i class="fas fa-list"></i>
                        <?php break;
                endswitch; ?>
            </div>
            <h3 class="hph-text-lg hph-font-medium hph-mb-2">No Items Found</h3>
            <p class="hph-text-muted"><?php echo esc_html($list_args['empty_message']); ?></p>
        </div>
    
    <!-- List Content -->
    <?php else : ?>
        <div class="<?php echo esc_attr($list_class); ?>" id="<?php echo esc_attr($unique_id); ?>-list">
            <?php foreach ($posts as $index => $post) : ?>
                <?php
                // Merge card arguments with post-specific data
                $card_arguments = array_merge($list_args['card_args'], [
                    'post_id' => $post->ID,
                    'post_type' => $post->post_type,
                    'layout' => 'list',
                    'image_position' => 'left',
                    'show_excerpt' => true // Lists typically show more content
                ]);
                
                // Add filter classes for filtering functionality
                $filter_classes = [];
                if ($list_args['filter_controls']) {
                    switch ($post->post_type) {
                        case 'listing':
                            $status = get_post_meta($post->ID, 'listing_status', true);
                            if ($status) {
                                $filter_classes[] = 'for-' . $status;
                            }
                            break;
                        case 'transaction':
                            $status = get_post_meta($post->ID, 'transaction_status', true);
                            if ($status) {
                                $filter_classes[] = $status;
                            }
                            break;
                    }
                }
                
                if (!empty($filter_classes)) {
                    $card_arguments['container_class'] = implode(' ', $filter_classes) . ' ' . ($card_arguments['container_class'] ?? '');
                }
                ?>
                
                <div class="hph-list-item <?php echo $list_args['dividers'] ? 'hph-py-' . $list_args['spacing'] : ''; ?>">
                    
                    <?php if ($list_args['numbered']) : ?>
                        <div class="hph-list-number hph-flex hph-items-start hph-gap-4">
                            <div class="hph-number hph-flex-shrink-0 hph-w-8 hph-h-8 hph-rounded-full hph-bg-primary hph-text-white hph-flex hph-items-center hph-justify-center hph-font-bold hph-text-sm">
                                <?php echo $index + 1; ?>
                            </div>
                            <div class="hph-flex-1">
                                <?php hph_component('card', $card_arguments); ?>
                            </div>
                        </div>
                    <?php else : ?>
                        <?php hph_component('card', $card_arguments); ?>
                    <?php endif; ?>
                    
                </div>
                
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($list_args['show_pagination'] && $query) : ?>
            <div class="hph-list-pagination hph-mt-8">
                <?php if ($list_args['pagination_type'] === 'load_more') : ?>
                    <div class="hph-text-center">
                        <button class="hph-btn hph-btn-outline hph-load-more-btn" 
                                data-page="2"
                                data-max-pages="<?php echo esc_attr($query->max_num_pages); ?>">
                            <i class="fas fa-plus hph-mr-2"></i>
                            Load More
                        </button>
                    </div>
                <?php elseif ($list_args['pagination_type'] === 'infinite') : ?>
                    <div class="hph-infinite-loader hph-hidden">
                        <div class="hph-text-center hph-py-4">
                            <div class="hph-spinner hph-spinner-lg hph-text-primary">
                                <i class="fas fa-circle-notch fa-spin"></i>
                            </div>
                        </div>
                    </div>
                <?php else : ?>
                    <!-- Number pagination -->
                    <?php
                    $pagination_links = paginate_links([
                        'total' => $query->max_num_pages,
                        'current' => max(1, get_query_var('paged')),
                        'prev_next' => true,
                        'prev_text' => '<i class="fas fa-chevron-left"></i> Previous',
                        'next_text' => 'Next <i class="fas fa-chevron-right"></i>',
                        'type' => 'array'
                    ]);
                    
                    if ($pagination_links) : ?>
                        <nav class="hph-pagination hph-flex hph-justify-between hph-items-center">
                            <div class="hph-pagination-info hph-text-sm hph-text-muted">
                                <?php
                                $paged = max(1, get_query_var('paged'));
                                $start = (($paged - 1) * $query->query_vars['posts_per_page']) + 1;
                                $end = min($start + $query->query_vars['posts_per_page'] - 1, $query->found_posts);
                                
                                printf(
                                    'Showing %d to %d of %d results',
                                    number_format_i18n($start),
                                    number_format_i18n($end),
                                    number_format_i18n($query->found_posts)
                                );
                                ?>
                            </div>
                            
                            <div class="hph-pagination-links hph-flex hph-gap-1">
                                <?php foreach ($pagination_links as $link) : ?>
                                    <?php echo str_replace(['page-numbers', 'current'], ['hph-btn hph-btn-outline hph-btn-sm', 'hph-btn hph-btn-primary hph-btn-sm'], $link); ?>
                                <?php endforeach; ?>
                            </div>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
    <?php endif; ?>
    
</div>

<?php if ($list_args['filter_controls'] || $list_args['sort_controls'] || $list_args['search_form']) : ?>
<script>
jQuery(document).ready(function($) {
    var $container = $('#<?php echo esc_js($unique_id); ?>');
    var $list = $('#<?php echo esc_js($unique_id); ?>-list');
    
    // Search functionality
    <?php if ($list_args['search_form']) : ?>
    $('#<?php echo esc_js($unique_id); ?>-search').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        
        $list.find('.hph-list-item').each(function() {
            var itemText = $(this).text().toLowerCase();
            $(this).toggle(itemText.indexOf(searchTerm) > -1);
        });
        
        // Update results count
        var visibleCount = $list.find('.hph-list-item:visible').length;
        $container.find('.hph-results-count').text(visibleCount + ' result' + (visibleCount !== 1 ? 's' : ''));
    });
    <?php endif; ?>
    
    // Filter functionality
    <?php if ($list_args['filter_controls']) : ?>
    $('.hph-filter-btn').on('click', function() {
        $('.hph-filter-btn').removeClass('active');
        $(this).addClass('active');
        
        var filter = $(this).data('filter');
        
        if (filter === '*') {
            $list.find('.hph-list-item').show();
        } else {
            $list.find('.hph-list-item').hide();
            $list.find('.hph-list-item .hph-card' + filter).closest('.hph-list-item').show();
        }
        
        // Update results count
        var visibleCount = $list.find('.hph-list-item:visible').length;
        $container.find('.hph-results-count').text(visibleCount + ' result' + (visibleCount !== 1 ? 's' : ''));
    });
    <?php endif; ?>
    
    // Sort functionality
    <?php if ($list_args['sort_controls']) : ?>
    $('#<?php echo esc_js($unique_id); ?>-sort').on('change', function() {
        var sortValue = $(this).val();
        var $items = $list.find('.hph-list-item').get();
        
        $items.sort(function(a, b) {
            var aVal, bVal;
            
            switch (sortValue) {
                case 'title-asc':
                    aVal = $(a).find('.hph-card-title').text().toLowerCase();
                    bVal = $(b).find('.hph-card-title').text().toLowerCase();
                    return aVal.localeCompare(bVal);
                
                case 'title-desc':
                    aVal = $(a).find('.hph-card-title').text().toLowerCase();
                    bVal = $(b).find('.hph-card-title').text().toLowerCase();
                    return bVal.localeCompare(aVal);
                
                case 'price-asc':
                    aVal = parseFloat($(a).find('.hph-card-price').text().replace(/[^0-9.-]+/g, '')) || 0;
                    bVal = parseFloat($(b).find('.hph-card-price').text().replace(/[^0-9.-]+/g, '')) || 0;
                    return aVal - bVal;
                
                case 'price-desc':
                    aVal = parseFloat($(a).find('.hph-card-price').text().replace(/[^0-9.-]+/g, '')) || 0;
                    bVal = parseFloat($(b).find('.hph-card-price').text().replace(/[^0-9.-]+/g, '')) || 0;
                    return bVal - aVal;
                
                default:
                    return 0;
            }
        });
        
        $.each($items, function(index, item) {
            $list.append(item);
        });
    });
    <?php endif; ?>
    
    // Load more functionality
    <?php if ($list_args['pagination_type'] === 'load_more') : ?>
    $('.hph-load-more-btn').on('click', function() {
        var $btn = $(this);
        var page = parseInt($btn.data('page'));
        var maxPages = parseInt($btn.data('max-pages'));
        
        $btn.prop('disabled', true).html('<i class="fas fa-circle-notch fa-spin hph-mr-2"></i>Loading...');
        
        // AJAX load more implementation would go here
        
        setTimeout(function() {
            $btn.data('page', page + 1);
            
            if (page >= maxPages) {
                $btn.hide();
            } else {
                $btn.prop('disabled', false).html('<i class="fas fa-plus hph-mr-2"></i>Load More');
            }
        }, 1000);
    });
    <?php endif; ?>
    
    // Infinite scroll functionality
    <?php if ($list_args['pagination_type'] === 'infinite') : ?>
    var loading = false;
    var page = 2;
    var maxPages = <?php echo $query ? $query->max_num_pages : 1; ?>;
    
    $(window).scroll(function() {
        if (loading || page > maxPages) return;
        
        if ($(window).scrollTop() + $(window).height() >= $(document).height() - 1000) {
            loading = true;
            $('.hph-infinite-loader').removeClass('hph-hidden');
            
            // AJAX infinite scroll implementation would go here
            
            setTimeout(function() {
                $('.hph-infinite-loader').addClass('hph-hidden');
                loading = false;
                page++;
            }, 1000);
        }
    });
    <?php endif; ?>
});
</script>
<?php endif; ?>