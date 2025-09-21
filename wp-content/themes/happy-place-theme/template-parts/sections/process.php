<?php
/**
 * HPH Process Section Template
 * Displays process steps with multiple layouts and styles
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Register this template part for asset loading
if (function_exists('hph_register_template_part')) {
    hph_register_template_part('sections/process');
}

// Default arguments with enhanced options
$defaults = array(
    // Layout & Style
    'style' => 'timeline', // timeline, icons-top, icons-left, cards, numbered, minimal, boxed
    'theme' => 'white', // white, light, dark, primary, secondary, gradient, pattern
    'pattern_type' => 'dots', // dots, grid, waves, diagonal (only if theme is 'pattern')
    
    // Grid Configuration
    'columns' => 'auto', // auto, 2, 3, 4, 5, 6
    'columns_tablet' => 'auto', // auto, 1, 2, 3
    'columns_mobile' => '1', // 1, 2
    'gap' => 'lg', // sm, md, lg, xl, 2xl
    
    // Spacing & Container
    'padding' => 'xl', // sm, md, lg, xl, 2xl, 3xl, 4xl
    'padding_top' => '', // Override top padding
    'padding_bottom' => '', // Override bottom padding
    'container' => 'default', // narrow, default, wide, full
    'container_alignment' => 'center', // left, center, right
    
    // Header Content
    'badge' => '',
    'badge_style' => 'primary', // primary, secondary, success, warning, danger
    'headline' => 'Our Process',
    'headline_tag' => 'h2', // h1, h2, h3, h4
    'subheadline' => '',
    'content' => '',
    'header_alignment' => 'center', // left, center, right
    'header_max_width' => '65ch', // CSS max-width value
    
    // Steps Configuration
    'steps' => array(),
    'step_number_type' => 'numeric', // numeric, alpha, roman, custom
    'step_style' => 'circle', // circle, square, diamond, hexagon, badge
    'step_size' => 'md', // sm, md, lg, xl
    'step_color' => 'primary', // primary, secondary, success, gradient
    'connector_style' => 'line', // line, arrow, dotted, dashed, gradient, none
    'connector_animation' => false, // Animate connector on scroll
    
    // Icon Settings
    'icon_style' => 'filled', // filled, outline, gradient, shadow
    'icon_size' => 'lg', // sm, md, lg, xl, 2xl
    'icon_color' => 'primary', // primary, secondary, auto (from step)
    
    // Card Settings (for card style)
    'card_shadow' => 'md', // none, sm, md, lg, xl
    'card_hover' => true, // Enable hover effects
    'card_border' => false, // Add border to cards
    'card_radius' => 'lg', // sm, md, lg, xl, 2xl
    
    // Animation
    'animation' => true,
    'animation_type' => 'fadeInUp', // fadeInUp, slideIn, scaleIn, rotateIn
    'animation_stagger' => true, // Stagger animations
    'animation_duration' => 'normal', // fast, normal, slow
    
    // Advanced
    'section_id' => '',
    'section_class' => '', // Additional custom classes
    'attributes' => array(), // Additional data attributes
    'reverse_mobile' => false, // Reverse step order on mobile
    'equal_height' => true, // Make all cards equal height
    'show_progress' => false, // Show progress bar for timeline
    'clickable_steps' => false, // Make steps clickable
    'completed_steps' => 0, // Number of completed steps (for progress indication)
);

// Merge with provided args
$config = wp_parse_args($args ?? array(), $defaults);
extract($config);

// Generate unique section ID if not provided
$section_id = $section_id ?: 'hph-process-' . uniqid();

// Auto-detect columns if set to 'auto'
if ($columns === 'auto' && !empty($steps)) {
    $steps_count = count($steps);
    if ($steps_count <= 3) {
        $columns = $steps_count;
    } elseif ($steps_count <= 4) {
        $columns = 4;
    } elseif ($steps_count <= 6) {
        $columns = 3;
    } else {
        $columns = 4;
    }
}

// Auto-detect tablet columns
if ($columns_tablet === 'auto') {
    $columns_tablet = min(2, $columns);
}

// Build section CSS classes
$section_classes = array(
    'hph-section',
    'hph-process-section',
    'hph-process--' . $style,
    'hph-section-' . $theme,
    'hph-padding-' . $padding
);

// Add pattern class if using pattern theme
if ($theme === 'pattern' && $pattern_type) {
    $section_classes[] = 'hph-pattern-' . $pattern_type;
}

// Add padding overrides
if ($padding_top) {
    $section_classes[] = 'hph-pt-' . $padding_top;
}
if ($padding_bottom) {
    $section_classes[] = 'hph-pb-' . $padding_bottom;
}

// Add animation classes
if ($animation) {
    $section_classes[] = 'hph-animated';
    $section_classes[] = 'hph-animation-' . $animation_type;
}

// Add custom classes
if ($section_class) {
    $section_classes[] = $section_class;
}

// Container classes
$container_classes = array(
    'hph-container'
);

switch ($container) {
    case 'narrow':
        $container_classes[] = 'hph-container--narrow';
        break;
    case 'wide':
        $container_classes[] = 'hph-container--wide';
        break;
    case 'full':
        $container_classes[] = 'hph-container--full';
        break;
    default:
        // Uses default container width
        break;
}

// Header classes
$header_classes = array(
    'hph-process__header',
    'hph-text-' . $header_alignment
);

// Process list classes
$process_classes = array(
    'hph-process__list',
    'hph-process__list--' . $style
);

// Add grid classes for appropriate styles
if (in_array($style, array('icons-top', 'cards', 'numbered', 'boxed', 'minimal'))) {
    $process_classes[] = 'hph-grid';
    $process_classes[] = 'hph-cols-' . $columns;
    $process_classes[] = 'hph-cols-tablet-' . $columns_tablet;
    $process_classes[] = 'hph-cols-mobile-' . $columns_mobile;
    $process_classes[] = 'hph-gap-' . $gap;
    
    if ($equal_height) {
        $process_classes[] = 'hph-equal-height';
    }
}

// Build data attributes
$data_attributes = array(
    'data-style="' . esc_attr($style) . '"',
    'data-animation="' . ($animation ? 'true' : 'false') . '"',
    'data-connector="' . esc_attr($connector_style) . '"'
);

if ($animation_stagger) {
    $data_attributes[] = 'data-stagger="true"';
}

if ($clickable_steps) {
    $data_attributes[] = 'data-clickable="true"';
}

if ($completed_steps > 0) {
    $data_attributes[] = 'data-completed="' . intval($completed_steps) . '"';
}

// Add custom attributes
foreach ($attributes as $key => $value) {
    $data_attributes[] = sprintf('data-%s="%s"', esc_attr($key), esc_attr($value));
}

// Helper function for step indicators
function hph_get_step_number($index, $type, $custom_number = null) {
    if ($custom_number) {
        return $custom_number;
    }
    
    switch ($type) {
        case 'alpha':
            return chr(65 + $index); // A, B, C...
        case 'roman':
            $map = array('I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X');
            return isset($map[$index]) ? $map[$index] : ($index + 1);
        case 'custom':
            return ''; // User provides custom content
        case 'numeric':
        default:
            return $index + 1;
    }
}
?>

<section 
    id="<?php echo esc_attr($section_id); ?>"
    class="<?php echo esc_attr(implode(' ', $section_classes)); ?>"
    <?php echo implode(' ', $data_attributes); ?>
>
    <?php if ($show_progress && $style === 'timeline'): ?>
    <!-- Progress Bar -->
    <div class="hph-process__progress" aria-hidden="true">
        <div class="hph-process__progress-bar" style="height: <?php echo ($completed_steps / count($steps)) * 100; ?>%"></div>
    </div>
    <?php endif; ?>
    
    <div class="<?php echo esc_attr(implode(' ', $container_classes)); ?>">
        
        <?php if ($badge || $headline || $subheadline || $content): ?>
        <!-- Section Header -->
        <div class="<?php echo esc_attr(implode(' ', $header_classes)); ?>">
            
            <?php if ($badge): ?>
            <!-- Badge -->
            <div class="hph-badge-wrapper">
                <span class="hph-badge hph-badge--<?php echo esc_attr($badge_style); ?>">
                    <?php echo esc_html($badge); ?>
                </span>
            </div>
            <?php endif; ?>
            
            <?php if ($headline): ?>
            <!-- Headline -->
            <<?php echo $headline_tag; ?> class="hph-process__headline hph-section-title">
                <?php echo esc_html($headline); ?>
            </<?php echo $headline_tag; ?>>
            <?php endif; ?>
            
            <?php if ($subheadline): ?>
            <!-- Subheadline -->
            <p class="hph-process__subheadline hph-section-subtitle">
                <?php echo esc_html($subheadline); ?>
            </p>
            <?php endif; ?>
            
            <?php if ($content): ?>
            <!-- Content -->
            <div class="hph-process__content" <?php if ($header_max_width): ?>style="max-width: <?php echo esc_attr($header_max_width); ?>; margin-left: auto; margin-right: auto;"<?php endif; ?>>
                <?php echo wp_kses_post($content); ?>
            </div>
            <?php endif; ?>
            
        </div>
        <?php endif; ?>
        
        <?php if (!empty($steps)): ?>
        <!-- Process Steps -->
        <div class="<?php echo esc_attr(implode(' ', $process_classes)); ?>">
            
            <?php if ($style === 'timeline' && $connector_style !== 'none'): ?>
            <!-- Timeline Connector -->
            <div class="hph-process__connector hph-process__connector--<?php echo esc_attr($connector_style); ?>" aria-hidden="true"></div>
            <?php endif; ?>
            
            <?php foreach ($steps as $index => $step): 
                // Step defaults
                $step_defaults = array(
                    'number' => '', // Custom number override
                    'title' => '',
                    'description' => '',
                    'icon' => '',
                    'icon_type' => 'class', // class or image
                    'image' => '',
                    'link' => '',
                    'link_text' => 'Learn More',
                    'link_target' => '_self',
                    'button' => array(), // Full button configuration
                    'highlight' => false, // Highlight this step
                    'completed' => $index < $completed_steps, // Mark as completed
                    'disabled' => false, // Disable this step
                    'custom_color' => '', // Override step color
                    'meta' => '', // Additional meta text
                    'list_items' => array(), // Bullet points under description
                );
                $step = wp_parse_args($step, $step_defaults);
                
                // Build step classes
                $step_classes = array(
                    'hph-process__step',
                    'hph-process__step--' . $style
                );
                
                if ($step['completed']) {
                    $step_classes[] = 'is-completed';
                }
                
                if ($step['highlight']) {
                    $step_classes[] = 'is-highlighted';
                }
                
                if ($step['disabled']) {
                    $step_classes[] = 'is-disabled';
                }
                
                if ($animation && $animation_stagger) {
                    $step_classes[] = 'hph-animate-item';
                }
                
                // Card specific classes
                if ($style === 'cards' || $style === 'boxed') {
                    $step_classes[] = 'hph-card';
                    $step_classes[] = 'hph-shadow-' . $card_shadow;
                    $step_classes[] = 'hph-radius-' . $card_radius;
                    
                    if ($card_hover) {
                        $step_classes[] = 'hph-card--hover';
                    }
                    
                    if ($card_border) {
                        $step_classes[] = 'hph-card--bordered';
                    }
                }
                
                $step_color = $step['custom_color'] ?: $step_color;
                $is_last_step = $index === count($steps) - 1;
                
                // Animation delay
                $animation_delay = '';
                if ($animation && $animation_stagger) {
                    $delay_ms = $index * 150;
                    $animation_delay = 'style="animation-delay: ' . $delay_ms . 'ms;"';
                }
            ?>
            
            <div 
                class="<?php echo esc_attr(implode(' ', $step_classes)); ?>"
                data-step="<?php echo esc_attr($index + 1); ?>"
                <?php echo $animation_delay; ?>
                <?php if ($clickable_steps && $step['link']): ?>
                    onclick="window.location.href='<?php echo esc_url($step['link']); ?>'"
                    style="cursor: pointer;"
                <?php endif; ?>
            >
                
                <?php if ($style !== 'minimal'): ?>
                <!-- Step Indicator -->
                <div class="hph-process__indicator hph-process__indicator--<?php echo esc_attr($step_style); ?> hph-process__indicator--<?php echo esc_attr($step_size); ?> hph-bg-<?php echo esc_attr($step_color); ?>">
                    <?php if ($step['icon'] && $step['icon_type'] === 'class'): ?>
                        <i class="<?php echo esc_attr($step['icon']); ?>"></i>
                    <?php elseif ($step['icon'] && $step['icon_type'] === 'image'): ?>
                        <img src="<?php echo esc_url($step['icon']); ?>" alt="">
                    <?php else: ?>
                        <span class="hph-process__number">
                            <?php echo esc_html(hph_get_step_number($index, $step_number_type, $step['number'])); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- Step Content -->
                <div class="hph-process__content-wrapper">
                    
                    <?php if ($style === 'minimal' && $step['icon']): ?>
                    <!-- Minimal Style Icon -->
                    <div class="hph-process__icon hph-process__icon--<?php echo esc_attr($icon_style); ?> hph-icon-<?php echo esc_attr($icon_size); ?>">
                        <?php if ($step['icon_type'] === 'image'): ?>
                            <img src="<?php echo esc_url($step['icon']); ?>" alt="">
                        <?php else: ?>
                            <i class="<?php echo esc_attr($step['icon']); ?> hph-text-<?php echo esc_attr($icon_color); ?>"></i>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($step['title']): ?>
                    <!-- Step Title -->
                    <h3 class="hph-process__title">
                        <?php echo esc_html($step['title']); ?>
                    </h3>
                    <?php endif; ?>
                    
                    <?php if ($step['meta']): ?>
                    <!-- Step Meta -->
                    <div class="hph-process__meta hph-text-muted">
                        <?php echo esc_html($step['meta']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($step['description']): ?>
                    <!-- Step Description -->
                    <div class="hph-process__description">
                        <?php echo wp_kses_post($step['description']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($step['list_items'])): ?>
                    <!-- Step List Items -->
                    <ul class="hph-process__list-items">
                        <?php foreach ($step['list_items'] as $item): ?>
                        <li><?php echo esc_html($item); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                    
                    <?php if ($step['image']): ?>
                    <!-- Step Image -->
                    <div class="hph-process__image">
                        <img 
                            src="<?php echo esc_url($step['image']); ?>" 
                            alt="<?php echo esc_attr($step['title']); ?>"
                            loading="lazy"
                            class="hph-radius-md"
                        >
                    </div>
                    <?php endif; ?>
                    
                    <?php if (($step['link'] && !$clickable_steps) || !empty($step['button'])): ?>
                    <!-- Step Actions -->
                    <div class="hph-process__actions">
                        
                        <?php if (!empty($step['button'])): ?>
                        <?php
                        $button = wp_parse_args($step['button'], array(
                            'text' => 'Learn More',
                            'url' => '#',
                            'style' => 'primary',
                            'size' => 'sm',
                            'target' => '_self',
                            'icon' => '',
                            'full_width' => false
                        ));
                        
                        $button_classes = array(
                            'hph-btn',
                            'hph-btn--' . $button['style'],
                            'hph-btn--' . $button['size']
                        );
                        
                        if ($button['full_width']) {
                            $button_classes[] = 'hph-btn--block';
                        }
                        ?>
                        <a href="<?php echo esc_url($button['url']); ?>"
                           class="<?php echo esc_attr(implode(' ', $button_classes)); ?>"
                           <?php if ($button['target'] !== '_self'): ?>target="<?php echo esc_attr($button['target']); ?>"<?php endif; ?>>
                            <?php if ($button['icon']): ?>
                                <i class="<?php echo esc_attr($button['icon']); ?>"></i>
                            <?php endif; ?>
                            <?php echo esc_html($button['text']); ?>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($step['link'] && !$clickable_steps): ?>
                        <a href="<?php echo esc_url($step['link']); ?>" 
                           class="hph-process__link"
                           <?php if ($step['link_target'] !== '_self'): ?>target="<?php echo esc_attr($step['link_target']); ?>"<?php endif; ?>>
                            <?php echo esc_html($step['link_text']); ?>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <?php endif; ?>
                        
                    </div>
                    <?php endif; ?>
                    
                </div>
                
                <?php if (in_array($style, array('icons-top', 'numbered', 'cards')) && !$is_last_step && $connector_style !== 'none'): ?>
                <!-- Horizontal Connector -->
                <div class="hph-process__connector-horizontal hph-process__connector--<?php echo esc_attr($connector_style); ?>" aria-hidden="true"></div>
                <?php endif; ?>
                
            </div>
            
            <?php endforeach; ?>
            
        </div>
        <?php endif; ?>
        
    </div>
</section>

<!-- Process Section Styles -->
<style>
/* Process Section Base Styles */
.hph-process-section {
    position: relative;
    overflow: visible;
}

/* Process List Layouts */
.hph-process__list--timeline {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: var(--hph-gap-2xl);
}

.hph-process__list--icons-left .hph-process__step {
    display: flex;
    align-items: flex-start;
    gap: var(--hph-gap-lg);
}

/* Timeline Connector */
.hph-process--timeline .hph-process__connector {
    position: absolute;
    left: 1.5rem;
    top: 2.5rem;
    bottom: 2.5rem;
    width: 2px;
    background: var(--hph-border-color);
    z-index: 0;
}

.hph-process--timeline .hph-process__connector--gradient {
    background: linear-gradient(180deg, var(--hph-primary-light) 0%, var(--hph-primary) 100%);
}

.hph-process--timeline .hph-process__connector--dotted {
    background: repeating-linear-gradient(
        to bottom,
        var(--hph-border-color),
        var(--hph-border-color) 5px,
        transparent 5px,
        transparent 10px
    );
}

.hph-process--timeline .hph-process__connector--dashed {
    background: repeating-linear-gradient(
        to bottom,
        var(--hph-border-color),
        var(--hph-border-color) 10px,
        transparent 10px,
        transparent 15px
    );
}

/* Timeline Steps */
.hph-process--timeline .hph-process__step {
    position: relative;
    padding-left: 4rem;
    z-index: 1;
}

.hph-process--timeline .hph-process__indicator {
    position: absolute;
    left: 0;
    top: 0;
}

/* Step Indicators */
.hph-process__indicator {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--hph-white);
    color: var(--hph-primary);
    border: 3px solid;
    border-color: currentColor;
    font-weight: var(--hph-font-bold);
    flex-shrink: 0;
    transition: var(--hph-transition);
}

/* Indicator Shapes */
.hph-process__indicator--circle {
    border-radius: var(--hph-radius-full);
}

.hph-process__indicator--square {
    border-radius: var(--hph-radius-md);
}

.hph-process__indicator--diamond {
    border-radius: var(--hph-radius-md);
    transform: rotate(45deg);
}

.hph-process__indicator--diamond > * {
    transform: rotate(-45deg);
}

.hph-process__indicator--hexagon {
    clip-path: polygon(30% 0%, 70% 0%, 100% 50%, 70% 100%, 30% 100%, 0% 50%);
    border: none;
    padding: 0.25rem;
}

.hph-process__indicator--badge {
    border-radius: var(--hph-radius-full);
    padding: 0.25rem 0.75rem;
    width: auto;
    min-width: 2.5rem;
}

/* Indicator Sizes */
.hph-process__indicator--sm {
    width: 2rem;
    height: 2rem;
    font-size: var(--hph-text-sm);
}

.hph-process__indicator--md {
    width: 3rem;
    height: 3rem;
    font-size: var(--hph-text-base);
}

.hph-process__indicator--lg {
    width: 4rem;
    height: 4rem;
    font-size: var(--hph-text-lg);
}

.hph-process__indicator--xl {
    width: 5rem;
    height: 5rem;
    font-size: var(--hph-text-xl);
}

/* Completed State */
.hph-process__step.is-completed .hph-process__indicator {
    background: var(--hph-success);
    color: var(--hph-white);
    border-color: var(--hph-success);
}

.hph-process__step.is-completed .hph-process__indicator::after {
    content: '\f00c';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
}

.hph-process__step.is-completed .hph-process__number {
    display: none;
}

/* Highlighted State */
.hph-process__step.is-highlighted {
    transform: scale(1.02);
}

.hph-process__step.is-highlighted .hph-process__indicator {
    box-shadow: 0 0 0 8px rgba(var(--hph-primary-rgb), 0.1);
}

/* Disabled State */
.hph-process__step.is-disabled {
    opacity: 0.5;
    pointer-events: none;
}

/* Card Styles */
.hph-process--cards .hph-process__step,
.hph-process--boxed .hph-process__step {
    padding: var(--hph-space-8);
    background: var(--hph-white);
    height: 100%;
    display: flex;
    flex-direction: column;
}

.hph-process--cards .hph-process__indicator,
.hph-process--boxed .hph-process__indicator {
    margin-bottom: var(--hph-space-4);
}

/* Minimal Style */
.hph-process--minimal .hph-process__step {
    text-align: center;
    padding: var(--hph-space-6);
}

.hph-process--minimal .hph-process__icon {
    margin-bottom: var(--hph-space-4);
}

/* Icon Styles */
.hph-process__icon--filled {
    background: var(--hph-primary-light);
    padding: var(--hph-space-4);
    border-radius: var(--hph-radius-full);
    display: inline-flex;
}

.hph-process__icon--outline {
    border: 2px solid var(--hph-primary);
    padding: var(--hph-space-4);
    border-radius: var(--hph-radius-full);
    display: inline-flex;
}

.hph-process__icon--gradient {
    background: linear-gradient(135deg, var(--hph-primary-light) 0%, var(--hph-primary) 100%);
    padding: var(--hph-space-4);
    border-radius: var(--hph-radius-full);
    display: inline-flex;
    color: var(--hph-white);
}

.hph-process__icon--shadow {
    background: var(--hph-white);
    padding: var(--hph-space-4);
    border-radius: var(--hph-radius-full);
    display: inline-flex;
    box-shadow: var(--hph-shadow-md);
}

/* Content Elements */
.hph-process__title {
    font-size: var(--hph-text-xl);
    font-weight: var(--hph-font-bold);
    margin: 0 0 var(--hph-space-2) 0;
    line-height: var(--hph-leading-tight);
}

.hph-process__meta {
    font-size: var(--hph-text-sm);
    margin-bottom: var(--hph-space-2);
}

.hph-process__description {
    color: var(--hph-text-muted);
    line-height: var(--hph-leading-relaxed);
    margin-bottom: var(--hph-space-4);
}

.hph-process__list-items {
    list-style: none;
    padding: 0;
    margin: var(--hph-space-4) 0;
}

.hph-process__list-items li {
    position: relative;
    padding-left: 1.5rem;
    margin-bottom: 0.5rem;
}

.hph-process__list-items li::before {
    content: '\f00c';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    position: absolute;
    left: 0;
    color: var(--hph-success);
    font-size: var(--hph-text-sm);
}

.hph-process__image {
    margin: var(--hph-space-4) 0;
}

.hph-process__image img {
    width: 100%;
    height: auto;
    display: block;
}

/* Actions */
.hph-process__actions {
    margin-top: var(--hph-space-4);
    display: flex;
    gap: var(--hph-gap-sm);
    flex-wrap: wrap;
}

.hph-process__link {
    color: var(--hph-primary);
    text-decoration: none;
    font-weight: var(--hph-font-medium);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: var(--hph-transition);
}

.hph-process__link:hover {
    color: var(--hph-primary-dark);
    gap: 0.75rem;
}

/* Horizontal Connectors */
.hph-process__connector-horizontal {
    position: absolute;
    top: 1.5rem;
    left: calc(50% + 2rem);
    right: calc(-50% + 2rem);
    height: 2px;
    background: var(--hph-border-color);
    z-index: -1;
}

.hph-process__connector-horizontal.hph-process__connector--arrow::after {
    content: '';
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 0;
    height: 0;
    border-left: 8px solid var(--hph-border-color);
    border-top: 4px solid transparent;
    border-bottom: 4px solid transparent;
}

/* Progress Bar */
.hph-process__progress {
    position: absolute;
    left: 1.5rem;
    top: 2.5rem;
    bottom: 2.5rem;
    width: 4px;
    background: var(--hph-gray-200);
    border-radius: var(--hph-radius-full);
    z-index: 0;
}

.hph-process__progress-bar {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    background: var(--hph-success);
    border-radius: var(--hph-radius-full);
    transition: height 1s ease-out;
}

/* Animation Classes */
.hph-animated .hph-animate-item {
    opacity: 0;
    animation-fill-mode: forwards;
}

.hph-animation-fadeInUp .hph-animate-item {
    animation: fadeInUp 0.8s ease-out;
}

.hph-animation-slideIn .hph-animate-item {
    animation: slideInLeft 0.8s ease-out;
}

.hph-animation-scaleIn .hph-animate-item {
    animation: scaleIn 0.8s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes scaleIn {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

/* Responsive Design */
@media (max-width: 1024px) {
    .hph-process__connector-horizontal {
        display: none;
    }
    
    .hph-cols-tablet-1 .hph-process__step {
        grid-column: span 1;
    }
    
    .hph-cols-tablet-2 {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .hph-cols-tablet-3 {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .hph-process--timeline .hph-process__step {
        padding-left: 3rem;
    }
    
    .hph-process--timeline .hph-process__connector {
        left: 1rem;
    }
    
    .hph-process--timeline .hph-process__indicator {
        transform: scale(0.85);
    }
    
    .hph-process--icons-left .hph-process__step {
        flex-direction: column;
        text-align: center;
        align-items: center;
    }
    
    .hph-process__title {
        font-size: var(--hph-text-lg);
    }
}

@media (max-width: 640px) {
    .hph-cols-mobile-1 {
        grid-template-columns: 1fr !important;
    }
    
    .hph-cols-mobile-2 {
        grid-template-columns: repeat(2, 1fr) !important;
    }
    
    .hph-process--timeline .hph-process__connector {
        display: none;
    }
    
    .hph-process--timeline .hph-process__step {
        padding-left: 0;
        text-align: center;
    }
    
    .hph-process--timeline .hph-process__indicator {
        position: relative;
        left: auto;
        margin: 0 auto var(--hph-space-4) auto;
    }
    
    .hph-process__header {
        text-align: center !important;
    }
    
    .hph-process__actions {
        width: 100%;
    }
    
    .hph-process__actions .hph-btn {
        width: 100%;
        justify-content: center;
    }
}

/* Dark Theme Adjustments */
.hph-section-dark .hph-process__step {
    color: var(--hph-white);
}

.hph-section-dark .hph-process__description {
    color: rgba(255, 255, 255, 0.8);
}

.hph-section-dark .hph-process__connector {
    background: rgba(255, 255, 255, 0.2);
}

.hph-section-dark .hph-process--cards .hph-process__step,
.hph-section-dark .hph-process--boxed .hph-process__step {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

/* Primary Theme Adjustments */
.hph-section-primary .hph-process__indicator {
    background: var(--hph-white);
    color: var(--hph-primary);
}

/* Pattern Background Support */
.hph-section-pattern {
    position: relative;
}

.hph-section-pattern::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    opacity: 0.05;
    z-index: 0;
    pointer-events: none;
}

.hph-pattern-dots::before {
    background-image: radial-gradient(circle, var(--hph-primary) 2px, transparent 2px);
    background-size: 20px 20px;
}

.hph-pattern-grid::before {
    background-image: 
        linear-gradient(var(--hph-primary) 1px, transparent 1px),
        linear-gradient(90deg, var(--hph-primary) 1px, transparent 1px);
    background-size: 20px 20px;
}

.hph-pattern-waves::before {
    background-image: url("data:image/svg+xml,%3Csvg width='100' height='20' viewBox='0 0 100 20' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M21.184 20c.357-.13.72-.264 1.088-.402l1.768-.661C33.64 15.347 39.647 14 50 14c10.271 0 15.362 1.222 24.629 4.928.955.383 1.869.74 2.75 1.072h6.225c-2.51-.73-5.139-1.691-8.233-2.928C65.888 13.278 60.562 12 50 12c-10.626 0-16.855 1.397-26.66 5.063l-1.767.662c-.370.138-.730.270-1.088.400h.699zm0-10c.357-.13.72-.264 1.088-.402l1.768-.661C33.64 5.347 39.647 4 50 4c10.271 0 15.362 1.222 24.629 4.928.955.383 1.869.74 2.75 1.072h6.225c-2.51-.73-5.139-1.691-8.233-2.928C65.888 3.278 60.562 2 50 2c-10.626 0-16.855 1.397-26.66 5.063l-1.767.662c-.370.138-.730.270-1.088.400h.699zm0 10c.357-.13.72-.264 1.088-.402l1.768-.661C33.64 25.347 39.647 24 50 24c10.271 0 15.362 1.222 24.629 4.928.955.383 1.869.74 2.75 1.072h6.225c-2.51-.73-5.139-1.691-8.233-2.928C65.888 23.278 60.562 22 50 22c-10.626 0-16.855 1.397-26.66 5.063l-1.767.662c-.370.138-.730.270-1.088.400h.699z' fill='%2351bae0' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E");
}

.hph-pattern-diagonal::before {
    background-image: repeating-linear-gradient(
        45deg,
        transparent,
        transparent 10px,
        rgba(var(--hph-primary-rgb), 0.03) 10px,
        rgba(var(--hph-primary-rgb), 0.03) 20px
    );
}

/* Equal Height Support */
.hph-equal-height {
    align-items: stretch;
}

.hph-equal-height .hph-process__step {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.hph-equal-height .hph-process__content-wrapper {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.hph-equal-height .hph-process__description {
    flex: 1;
}

/* Print Styles */
@media print {
    .hph-process__connector,
    .hph-process__connector-horizontal {
        display: none;
    }
    
    .hph-process__step {
        page-break-inside: avoid;
        border: 1px solid var(--hph-border-color);
        padding: 1rem;
        margin-bottom: 1rem;
    }
}
</style>