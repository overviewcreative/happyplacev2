<?php
/**
 * Base Single Hero - Hero section with images/gallery
 *
 * @package HappyPlaceTheme
 */

$hero_args = wp_parse_args($args ?? [], [
    'post_id' => get_the_ID(),
    'post_type' => get_post_type(),
    'style' => 'default', // default, slider, gallery, video, minimal
    'height' => 'lg', // sm, md, lg, xl, full
    'show_gallery' => true,
    'show_status' => true,
    'show_actions' => true,
    'show_meta' => true,
    'overlay' => 'gradient', // none, dark, gradient, custom
    'container_class' => 'hph-container',
    'background_color' => '#f8fafc'
]);

// Get post data using bridge functions
$post_data = [];
switch ($hero_args['post_type']) {
    case 'listing':
        if (function_exists('hpt_get_listing')) {
            $post_data = hpt_get_listing($hero_args['post_id']);
        }
        break;
    case 'agent':
        if (function_exists('hpt_get_agent')) {
            $post_data = hpt_get_agent($hero_args['post_id']);
        }
        break;
    default:
        $post_data = [
            'id' => $hero_args['post_id'],
            'title' => get_the_title($hero_args['post_id']),
            'featured_image' => get_the_post_thumbnail_url($hero_args['post_id'], 'full')
        ];
        break;
}

// Check if we have media to display
$has_media = false;
$media_items = [];

// Get images based on post type
switch ($hero_args['post_type']) {
    case 'listing':
        if (function_exists('hpt_get_listing_gallery')) {
            $media_items = hpt_get_listing_gallery($hero_args['post_id']);
            $has_media = !empty($media_items);
        }
        if (!$has_media && has_post_thumbnail($hero_args['post_id'])) {
            $media_items = [get_the_post_thumbnail_url($hero_args['post_id'], 'full')];
            $has_media = true;
        }
        break;
    default:
        if (has_post_thumbnail($hero_args['post_id'])) {
            $media_items = [get_the_post_thumbnail_url($hero_args['post_id'], 'full')];
            $has_media = true;
        }
        break;
}

// Don't show hero if no media and not minimal style
if (!$has_media && $hero_args['style'] !== 'minimal') {
    return;
}

// Build hero classes using utility-first approach
$hero_classes = [
    'hph-single-hero',
    'hph-relative',
    'hph-overflow-hidden'
];

// Height variations using utility classes
$height_classes = [
    'sm' => ['hph-h-64', 'md:hph-h-80'],
    'md' => ['hph-h-80', 'md:hph-h-96'],
    'lg' => ['hph-h-96', 'md:hph-h-screen/2'],
    'xl' => ['hph-h-screen/2', 'md:hph-h-screen/3*2'],
    'full' => ['hph-h-screen']
];

$hero_classes = array_merge($hero_classes, $height_classes[$hero_args['height']] ?? $height_classes['lg']);

// Add post type specific classes
$hero_classes[] = 'hph-hero-' . $hero_args['post_type'];

$hero_id = 'hero-' . $hero_args['post_id'];
?>

<section class="<?php echo esc_attr(implode(' ', $hero_classes)); ?>" 
         id="<?php echo esc_attr($hero_id); ?>"
         <?php if (!$has_media && $hero_args['background_color']) : ?>
         style="background-color: <?php echo esc_attr($hero_args['background_color']); ?>"
         <?php endif; ?>>
    
    <?php if ($has_media) : ?>
        
        <?php if ($hero_args['style'] === 'slider' && count($media_items) > 1) : ?>
            <!-- Image Slider -->
            <div class="hph-absolute hph-inset-0 hph-w-full hph-h-full" data-hero-slider>
                <div class="hph-relative hph-w-full hph-h-full">
                    <?php foreach ($media_items as $index => $image_url) : ?>
                        <div class="hph-absolute hph-inset-0 hph-w-full hph-h-full hph-transition-opacity hph-duration-500 <?php echo $index === 0 ? 'hph-opacity-100' : 'hph-opacity-0'; ?>" data-slide="<?php echo $index; ?>">
                            <img src="<?php echo esc_url($image_url); ?>" 
                                 alt="<?php echo esc_attr(get_the_title($hero_args['post_id'])); ?>"
                                 class="hph-w-full hph-h-full hph-object-cover"
                                 <?php echo $index === 0 ? '' : 'loading="lazy"'; ?>>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if ($hero_args['show_gallery'] && count($media_items) > 1) : ?>
                    <div class="hph-absolute hph-inset-x-0 hph-bottom-4 hph-flex hph-items-center hph-justify-between hph-px-4">
                        <button class="hph-w-10 hph-h-10 hph-bg-black/50 hph-text-white hph-rounded-full hph-flex hph-items-center hph-justify-center hph-transition-all hph-duration-200 hph-hover:bg-black/75" 
                                aria-label="<?php esc_attr_e('Previous image', 'happy-place-theme'); ?>" 
                                data-action="prev">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <div class="hph-bg-black/50 hph-text-white hph-px-md hph-py-sm hph-rounded-full hph-text-sm hph-font-medium">
                            <span data-current="1">1</span>
                            <span class="hph-mx-1">/</span>
                            <span><?php echo count($media_items); ?></span>
                        </div>
                        <button class="hph-w-10 hph-h-10 hph-bg-black/50 hph-text-white hph-rounded-full hph-flex hph-items-center hph-justify-center hph-transition-all hph-duration-200 hph-hover:bg-black/75" 
                                aria-label="<?php esc_attr_e('Next image', 'happy-place-theme'); ?>" 
                                data-action="next">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
            
        <?php else : ?>
            <!-- Single Image -->
            <div class="hph-absolute hph-inset-0 hph-w-full hph-h-full">
                <img src="<?php echo esc_url($media_items[0]); ?>" 
                     alt="<?php echo esc_attr(get_the_title($hero_args['post_id'])); ?>"
                     class="hph-w-full hph-h-full hph-object-cover">
            </div>
        <?php endif; ?>
        
    <?php endif; ?>
    
    <?php if ($hero_args['overlay'] !== 'none' || $hero_args['style'] === 'minimal') : ?>
        <?php
        // Build overlay classes
        $overlay_classes = [
            'hph-absolute',
            'hph-inset-0',
            'hph-flex',
            'hph-items-end',
            'hph-justify-center',
            'hph-p-lg'
        ];
        
        // Overlay background based on type
        switch ($hero_args['overlay']) {
            case 'gradient':
                $overlay_classes[] = 'hph-bg-gradient-to-t';
                $overlay_classes[] = 'hph-from-black/75';
                $overlay_classes[] = 'hph-via-black/25';
                $overlay_classes[] = 'hph-to-transparent';
                break;
            case 'dark':
                $overlay_classes[] = 'hph-bg-black/50';
                break;
            case 'custom':
                $overlay_classes[] = 'hph-bg-primary-900/75';
                break;
        }
        ?>
        <div class="<?php echo esc_attr(implode(' ', $overlay_classes)); ?>">
            <div class="<?php echo esc_attr($hero_args['container_class']); ?> hph-w-full">
                <div class="hph-text-white hph-text-center hph-space-y-md hph-max-w-4xl hph-mx-auto">
                    
                    <?php if ($hero_args['show_status'] && !empty($post_data['status'])) : ?>
                        <div class="hph-mb-md">
                            <?php
                            $status_classes = [
                                'hph-inline-block',
                                'hph-px-lg',
                                'hph-py-sm',
                                'hph-rounded-full',
                                'hph-text-sm',
                                'hph-font-semibold',
                                'hph-uppercase',
                                'hph-tracking-wide'
                            ];
                            
                            // Status-specific colors
                            switch ($post_data['status']) {
                                case 'active':
                                case 'available':
                                    $status_classes = array_merge($status_classes, ['hph-bg-success-600', 'hph-text-white']);
                                    break;
                                case 'sold':
                                case 'rented':
                                    $status_classes = array_merge($status_classes, ['hph-bg-gray-600', 'hph-text-white']);
                                    break;
                                case 'pending':
                                    $status_classes = array_merge($status_classes, ['hph-bg-warning-600', 'hph-text-white']);
                                    break;
                                default:
                                    $status_classes = array_merge($status_classes, ['hph-bg-primary-600', 'hph-text-white']);
                            }
                            ?>
                            <span class="<?php echo esc_attr(implode(' ', $status_classes)); ?>">
                                <?php echo esc_html(ucfirst(str_replace('_', ' ', $post_data['status']))); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <h1 class="hph-text-4xl md:hph-text-5xl lg:hph-text-6xl hph-font-bold hph-text-white hph-leading-tight hph-mb-lg">
                        <?php echo esc_html($post_data['title'] ?? get_the_title($hero_args['post_id'])); ?>
                    </h1>
                    
                    <?php if ($hero_args['show_meta']) : ?>
                        <div class="hph-space-y-sm hph-text-lg hph-text-white/90">
                            <?php
                            // Post-type specific meta display
                            switch ($hero_args['post_type']) {
                                case 'listing':
                                    if (!empty($post_data['price'])) {
                                        echo '<div class="hph-text-3xl hph-font-bold hph-text-white">$' . number_format($post_data['price']) . '</div>';
                                    }
                                    if (!empty($post_data['address'])) {
                                        echo '<div class="hph-flex hph-items-center hph-justify-center hph-gap-sm">';
                                        echo '<i class="fas fa-map-marker-alt"></i>';
                                        echo '<span>' . esc_html($post_data['address']) . '</span>';
                                        echo '</div>';
                                    }
                                    break;
                                case 'agent':
                                    if (!empty($post_data['title'])) {
                                        echo '<div class="hph-text-xl hph-font-semibold">' . esc_html($post_data['title']) . '</div>';
                                    }
                                    if (!empty($post_data['company'])) {
                                        echo '<div class="hph-text-lg">' . esc_html($post_data['company']) . '</div>';
                                    }
                                    break;
                                default:
                                    echo '<div class="hph-flex hph-items-center hph-justify-center hph-gap-sm">';
                                    echo '<i class="fas fa-calendar-alt"></i>';
                                    echo '<span>' . get_the_date('', $hero_args['post_id']) . '</span>';
                                    echo '</div>';
                                    break;
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($hero_args['show_actions']) : ?>
                        <div class="hph-flex hph-flex-col sm:hph-flex-row hph-items-center hph-justify-center hph-gap-md hph-pt-lg">
                            <?php
                            // Load post-type specific actions
                            $actions_template = 'template-parts/single/hero-actions-' . $hero_args['post_type'];
                            if (locate_template($actions_template . '.php')) {
                                get_template_part($actions_template, null, $hero_args);
                            } else {
                                // Default actions
                                if ($has_media && count($media_items) > 1) {
                                    echo '<button class="hph-inline-flex hph-items-center hph-gap-sm hph-px-xl hph-py-md hph-bg-white hph-text-gray-900 hph-font-semibold hph-rounded-lg hph-transition-all hph-duration-300 hph-hover:bg-gray-100 hph-hover:scale-105" data-action="gallery">';
                                    echo '<i class="fas fa-images"></i>';
                                    echo '<span>' . sprintf(__('View All Photos (%d)', 'happy-place-theme'), count($media_items)) . '</span>';
                                    echo '</button>';
                                }
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                    
                </div>
            </div>
        </div>
    <?php endif; ?>
    
</section>
