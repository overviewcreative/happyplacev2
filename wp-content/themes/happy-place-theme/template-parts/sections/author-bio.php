<?php
/**
 * Author Bio Section Template Part
 * 
 * @package Happy_Place_Theme
 */

$author_id = $args['author_id'] ?? 0; // Author ID for fetching data
$author_name = $args['author_name'] ?? ''; // Author display name
$author_bio = $args['author_bio'] ?? ''; // Author biography text
$author_avatar = $args['author_avatar'] ?? ''; // URL to author avatar image
$post_count = $args['post_count'] ?? 0; // Number of posts by author
$background = $args['background'] ?? 'light'; // Options: 'light', 'dark', 'white', 'gray', 'primary'
$padding = $args['padding'] ?? 'lg'; // Options: 'sm', 'md', 'lg', 'xl', 'none'
$section_id = $args['section_id'] ?? ''; // HTML ID for the section

if (!$author_id || !$author_bio) {
    return;
}

$section_classes = [
    'hph-section',
    'hph-author-bio-section',
    'hph-bg-' . $background,
    'hph-py-' . $padding
];
?>

<section <?php if ($section_id): ?>id="<?php echo esc_attr($section_id); ?>"<?php endif; ?> 
         class="<?php echo esc_attr(implode(' ', $section_classes)); ?>">
    <div class="hph-container">
        <div class="hph-content-width-normal hph-mx-auto">
            
            <div class="hph-author-intro hph-flex hph-items-center hph-gap-lg hph-p-xl hph-bg-white hph-rounded-lg hph-shadow-sm">
                
                <?php if ($author_avatar): ?>
                    <div class="hph-author-avatar hph-flex-shrink-0">
                        <img src="<?php echo esc_url($author_avatar); ?>" 
                             alt="<?php echo esc_attr($author_name); ?>"
                             class="hph-w-16 hph-h-16 hph-rounded-full hph-border-2 hph-border-primary">
                    </div>
                <?php endif; ?>
                
                <div class="hph-author-details">
                    <h3 class="hph-text-lg hph-font-bold hph-mb-xs hph-text-gray-900">
                        <?php echo esc_html($author_name); ?>
                    </h3>
                    
                    <?php if ($post_count > 0): ?>
                        <p class="hph-text-sm hph-text-gray-500 hph-mb-sm">
                            <?php printf(_n('%d Article', '%d Articles', $post_count, 'happy-place-theme'), $post_count); ?>
                        </p>
                    <?php endif; ?>
                    
                    <p class="hph-text-gray-600 hph-leading-relaxed">
                        <?php echo esc_html(wp_trim_words($author_bio, 25)); ?>
                    </p>
                </div>
                
            </div>
            
        </div>
    </div>
</section>