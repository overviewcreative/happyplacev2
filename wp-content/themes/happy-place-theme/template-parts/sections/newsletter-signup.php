<?php
/**
 * Newsletter Signup Section Template Part
 * 
 * @package Happy_Place_Theme
 */

$background = $args['background'] ?? 'primary';
$padding = $args['padding'] ?? 'xl';
$alignment = $args['alignment'] ?? 'center';
$content_width = $args['content_width'] ?? 'normal';
$headline = $args['headline'] ?? 'Stay Updated';
$subheadline = $args['subheadline'] ?? 'Subscribe to our newsletter for the latest updates';
$section_id = $args['section_id'] ?? '';

$section_classes = [
    'hph-section',
    'hph-newsletter-section',
    'hph-bg-' . $background,
    'hph-py-' . $padding,
    'hph-text-' . $alignment
];

// Adjust text color based on background
$text_color_class = $background === 'primary' ? 'hph-text-white' : 'hph-text-gray-900';
?>

<section <?php if ($section_id): ?>id="<?php echo esc_attr($section_id); ?>"<?php endif; ?> 
         class="<?php echo esc_attr(implode(' ', $section_classes)); ?>">
    <div class="hph-container">
        <div class="hph-content-width-<?php echo esc_attr($content_width); ?> hph-mx-auto">
            
            <div class="hph-newsletter-content">
                
                <h3 class="hph-text-3xl hph-font-bold hph-mb-md <?php echo $text_color_class; ?>">
                    <?php echo esc_html($headline); ?>
                </h3>
                
                <?php if ($subheadline): ?>
                    <p class="hph-text-lg hph-mb-xl <?php echo $background === 'primary' ? 'hph-text-white hph-opacity-90' : 'hph-text-gray-600'; ?>">
                        <?php echo esc_html($subheadline); ?>
                    </p>
                <?php endif; ?>
                
                <form class="hph-newsletter-form hph-max-w-md hph-mx-auto" 
                      action="#" 
                      method="post">
                    
                    <div class="hph-form-group hph-flex hph-gap-sm">
                        <input type="email" 
                               name="email" 
                               placeholder="Enter your email address" 
                               class="hph-form-input hph-flex-1 hph-px-lg hph-py-md hph-rounded-lg hph-border-0 hph-text-gray-900"
                               required>
                        
                        <button type="submit" 
                                class="hph-btn <?php echo $background === 'primary' ? 'hph-btn-white' : 'hph-btn-primary'; ?> hph-px-lg hph-py-md hph-rounded-lg hph-font-medium hph-whitespace-nowrap">
                            <i class="fas fa-envelope hph-mr-xs"></i>
                            Subscribe
                        </button>
                    </div>
                    
                    <p class="hph-text-xs hph-mt-md <?php echo $background === 'primary' ? 'hph-text-white hph-opacity-75' : 'hph-text-gray-500'; ?>">
                        We respect your privacy. Unsubscribe at any time.
                    </p>
                    
                </form>
                
            </div>
            
        </div>
    </div>
</section>

<style>
.hph-newsletter-section .hph-btn-white {
    background: white;
    color: var(--hph-primary);
    border: 2px solid white;
}

.hph-newsletter-section .hph-btn-white:hover {
    background: transparent;
    color: white;
    border-color: white;
}

.hph-newsletter-section .hph-form-input:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
}
</style>