<?php
/**
 * Universal Archive Hero Template Part
 * 
 * This template part handles hero sections for all archive types:
 * - Local Places
 * - Cities
 * - Open Houses
 * - Communities  
 * - Events
 * 
 * Usage: get_template_part('template-parts/archive-hero', null, $hero_data);
 * 
 * @package Happy_Place_Theme
 */

// Get hero data from args or use defaults
$hero_data = $args ?? [];

// Get post type for styling
$post_type = get_post_type() ?: get_query_var('post_type') ?: 'post';

// Default hero data
$defaults = [
    'title' => get_the_archive_title(),
    'subtitle' => get_the_archive_description(),
    'background_image' => '',
    'background_color' => '',
    'post_type' => $post_type,
    'show_search' => true,
    'show_filters' => true,
    'custom_classes' => []
];

$hero = wp_parse_args($hero_data, $defaults);

// Generate CSS classes
$hero_classes = [
    'hph-archive-hero-section',
    'hph-archive-' . str_replace('_', '-', $hero['post_type']) . '-hero'
];

if (!empty($hero['custom_classes'])) {
    $hero_classes = array_merge($hero_classes, (array) $hero['custom_classes']);
}

$hero_class_string = implode(' ', $hero_classes);

// Background styling
$background_styles = [];
if (!empty($hero['background_image'])) {
    $background_styles[] = "background-image: url('" . esc_url($hero['background_image']) . "')";
}
if (!empty($hero['background_color'])) {
    $background_styles[] = "background-color: " . esc_attr($hero['background_color']);
}

$background_style_string = !empty($background_styles) ? 'style="' . implode('; ', $background_styles) . '"' : '';
?>

<section class="<?php echo esc_attr($hero_class_string); ?>" <?php echo $background_style_string; ?>>
    <div class="hph-archive-hero-overlay"></div>
    
    <div class="hph-archive-hero-container">
        <div class="hph-archive-hero-content">
            
            <?php if (!empty($hero['title'])): ?>
                <h1 class="hph-hero-headline"><?php echo wp_kses_post($hero['title']); ?></h1>
            <?php endif; ?>
            
            <?php if (!empty($hero['subtitle'])): ?>
                <p class="hph-hero-subheadline"><?php echo wp_kses_post($hero['subtitle']); ?></p>
            <?php endif; ?>
            
            <?php if ($hero['show_search']): ?>
                <div class="hph-hero-search-container">
                    <?php 
                    // Include search form based on post type
                    $search_form_path = 'template-parts/hero-search-' . str_replace('_', '-', $hero['post_type']);
                    get_template_part($search_form_path); 
                    ?>
                </div>
            <?php endif; ?>
            
        </div>
    </div>
</section>