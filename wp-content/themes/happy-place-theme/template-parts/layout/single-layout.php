<?php
/**
 * Base Single Layout - Main single post template structure
 *
 * @package HappyPlaceTheme
 */

$single_args = wp_parse_args($args ?? [], [
    'post_id' => get_the_ID(),
    'post_type' => get_post_type(),
    'show_hero' => true,
    'show_breadcrumbs' => true,
    'show_sidebar' => false,
    'show_related' => true,
    'show_cta' => true,
    'layout' => 'full-width',
    'sidebar_position' => 'right',
    'container_class' => 'hph-container',
    'hero_style' => 'default',
    'content_sections' => [],
    'sidebar_widgets' => [],
    'related_count' => 4
]);

// Verify post exists and is viewable
if (!$single_args['post_id'] || !get_post($single_args['post_id'])) {
    get_template_part('template-parts/content', 'none');
    return;
}

// Check permissions
$post_status = get_post_status($single_args['post_id']);
if ($post_status !== 'publish' && !current_user_can('edit_posts')) {
    get_template_part('template-parts/content', 'none');
    return;
}

// Build layout classes using utility-first approach
$layout_classes = [
    'hph-single-layout',
    'hph-min-h-screen',
    'hph-bg-gray-50'
];

// Add post type specific classes
$layout_classes[] = 'hph-single-' . $single_args['post_type'];

// Add layout mode classes
if ($single_args['layout'] === 'full-width') {
    $layout_classes[] = 'hph-w-full';
} else {
    $layout_classes[] = 'hph-max-w-7xl';
    $layout_classes[] = 'hph-mx-auto';
}
?>

<main class="<?php echo esc_attr(implode(' ', $layout_classes)); ?>" id="main" role="main">
    
    <?php if ($single_args['show_breadcrumbs']) : ?>
        <?php hph_component('breadcrumbs', [
            'post_id' => $single_args['post_id'],
            'post_type' => $single_args['post_type']
        ]); ?>
    <?php endif; ?>
    
    <?php if ($single_args['show_hero']) : ?>
        <?php hph_component('single-hero', array_merge($single_args, [
            'style' => $single_args['hero_style']
        ])); ?>
    <?php endif; ?>
    
    <div class="hph-flex-1 hph-py-xl">
        <div class="<?php echo esc_attr($single_args['container_class']); ?>">
            
            <?php 
            // Build main content classes based on sidebar configuration
            $main_content_classes = ['hph-single-content'];
            
            if ($single_args['show_sidebar']) {
                $main_content_classes[] = 'hph-grid';
                $main_content_classes[] = 'hph-grid-cols-1';
                $main_content_classes[] = 'lg:hph-grid-cols-4';
                $main_content_classes[] = 'hph-gap-xl';
            }
            ?>
            
            <div class="<?php echo esc_attr(implode(' ', $main_content_classes)); ?>">
                
                <?php if ($single_args['show_sidebar'] && $single_args['sidebar_position'] === 'left') : ?>
                    <aside class="hph-col-span-1 hph-space-y-lg">
                        <?php hph_component('single-sidebar', $single_args); ?>
                    </aside>
                <?php endif; ?>
                
                <div class="<?php echo $single_args['show_sidebar'] ? 'hph-col-span-1 lg:hph-col-span-3' : 'hph-w-full'; ?>">
                    <div class="hph-animate-fade-in-up">
                        <?php hph_component('single-content', $single_args); ?>
                    </div>
                </div>
                
                <?php if ($single_args['show_sidebar'] && $single_args['sidebar_position'] === 'right') : ?>
                    <aside class="hph-col-span-1 hph-space-y-lg">
                        <?php hph_component('single-sidebar', $single_args); ?>
                    </aside>
                <?php endif; ?>
                
            </div>
            
        </div>
    </div>
    
    <?php if ($single_args['show_cta']) : ?>
        <?php hph_component('single-cta', $single_args); ?>
    <?php endif; ?>
    
    <?php if ($single_args['show_related']) : ?>
        <?php hph_component('single-related', $single_args); ?>
    <?php endif; ?>
    
</main>
