<?php
/**
 * HPH Process Section Template
 * Displays process steps with timeline, icons, and descriptions
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Register this template part for asset loading
if (function_exists('hph_register_template_part')) {
    hph_register_template_part('sections/process');
}

// Default arguments
$defaults = array(
    'style' => 'timeline', // Options: 'timeline', 'icons-top', 'icons-left', 'cards', 'numbered'
    'theme' => 'white', // Color theme: 'white', 'light', 'dark', 'primary'
    'columns' => 4, // Number of columns for grid layouts
    'padding' => 'xl',
    'container' => 'default',
    'alignment' => 'center',
    'badge' => '',
    'headline' => 'Our Process',
    'subheadline' => '',
    'content' => '',
    'steps' => array(),
    'step_style' => 'auto', // 'auto', 'circle', 'square', 'diamond'
    'connector_style' => 'line', // 'line', 'arrow', 'dotted', 'none'
    'animation' => false,
    'section_id' => ''
);

// Merge with provided args
$config = wp_parse_args($args ?? array(), $defaults);
extract($config);

// Auto-detect columns if not set for grid layouts
if (($style === 'icons-top' || $style === 'cards') && !empty($steps)) {
    $steps_count = count($steps);
    if ($columns === 0) {
        if ($steps_count <= 3) {
            $columns = $steps_count;
        } elseif ($steps_count <= 6) {
            $columns = min(4, $steps_count);
        } else {
            $columns = 4;
        }
    }
}

// Build section styles
$section_styles = array(
    'position: relative',
    'width: 100%'
);

// Theme-based styling
switch ($theme) {
    case 'light':
        $section_styles[] = 'background-color: var(--hph-gray-50)';
        $section_styles[] = 'color: var(--hph-text-color)';
        break;
    case 'dark':
        $section_styles[] = 'background-color: var(--hph-gray-900)';
        $section_styles[] = 'color: var(--hph-white)';
        break;
    case 'primary':
        $section_styles[] = 'background-color: var(--hph-primary)';
        $section_styles[] = 'color: var(--hph-white)';
        break;
    case 'white':
    default:
        $section_styles[] = 'background-color: var(--hph-white)';
        $section_styles[] = 'color: var(--hph-text-color)';
        break;
}

// Padding styles
switch ($padding) {
    case 'sm':
        $section_styles[] = 'padding-top: var(--hph-padding-lg)';
        $section_styles[] = 'padding-bottom: var(--hph-padding-lg)';
        break;
    case 'md':
        $section_styles[] = 'padding-top: var(--hph-padding-xl)';
        $section_styles[] = 'padding-bottom: var(--hph-padding-xl)';
        break;
    case 'lg':
        $section_styles[] = 'padding-top: var(--hph-padding-2xl)';
        $section_styles[] = 'padding-bottom: var(--hph-padding-2xl)';
        break;
    case '2xl':
        $section_styles[] = 'padding-top: var(--hph-padding-4xl)';
        $section_styles[] = 'padding-bottom: var(--hph-padding-4xl)';
        break;
    case 'xl':
    default:
        $section_styles[] = 'padding-top: var(--hph-padding-3xl)';
        $section_styles[] = 'padding-bottom: var(--hph-padding-3xl)';
        break;
}

// Container styles
$container_styles = array(
    'position: relative',
    'margin-left: auto',
    'margin-right: auto',
    'padding-left: var(--hph-padding-lg)',
    'padding-right: var(--hph-padding-lg)'
);

switch ($container) {
    case 'narrow':
        $container_styles[] = 'max-width: var(--hph-container-sm)';
        break;
    case 'wide':
        $container_styles[] = 'max-width: var(--hph-container-2xl)';
        break;
    case 'full':
        $container_styles[] = 'max-width: 100%';
        $container_styles[] = 'padding-left: 0';
        $container_styles[] = 'padding-right: 0';
        break;
    case 'default':
    default:
        $container_styles[] = 'max-width: var(--hph-container-xl)';
        break;
}

// Text alignment for header
$header_alignment = '';
switch ($alignment) {
    case 'left':
        $header_alignment = 'text-align: left';
        break;
    case 'right':
        $header_alignment = 'text-align: right';
        break;
    case 'center':
    default:
        $header_alignment = 'text-align: center';
        break;
}

// Process container styles based on layout
$process_styles = array();

if ($style === 'timeline') {
    $process_styles[] = 'position: relative';
    $process_styles[] = 'display: flex';
    $process_styles[] = 'flex-direction: column';
    $process_styles[] = 'gap: var(--hph-gap-2xl)';
} elseif ($style === 'icons-top' || $style === 'cards' || $style === 'numbered') {
    $process_styles[] = 'display: grid';
    $process_styles[] = 'gap: var(--hph-gap-xl)';
    
    switch ($columns) {
        case 2:
            $process_styles[] = 'grid-template-columns: repeat(auto-fit, minmax(300px, 1fr))';
            break;
        case 3:
            $process_styles[] = 'grid-template-columns: repeat(auto-fit, minmax(280px, 1fr))';
            break;
        case 4:
            $process_styles[] = 'grid-template-columns: repeat(auto-fit, minmax(250px, 1fr))';
            break;
        case 5:
            $process_styles[] = 'grid-template-columns: repeat(auto-fit, minmax(220px, 1fr))';
            break;
        default:
            $process_styles[] = 'grid-template-columns: repeat(auto-fit, minmax(280px, 1fr))';
    }
} elseif ($style === 'icons-left') {
    $process_styles[] = 'display: flex';
    $process_styles[] = 'flex-direction: column';
    $process_styles[] = 'gap: var(--hph-gap-2xl)';
}

// Step indicator styles
function getStepIndicatorStyles($step_style, $index, $theme) {
    $styles = array(
        'display: inline-flex',
        'align-items: center',
        'justify-content: center',
        'font-weight: var(--hph-font-bold)',
        'color: var(--hph-white)',
        'background: var(--hph-primary)',
        'border: 3px solid var(--hph-white)',
        'box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15)'
    );
    
    switch ($step_style) {
        case 'circle':
            $styles[] = 'width: 3rem';
            $styles[] = 'height: 3rem';
            $styles[] = 'border-radius: var(--hph-radius-full)';
            $styles[] = 'font-size: var(--hph-text-lg)';
            break;
        case 'square':
            $styles[] = 'width: 3rem';
            $styles[] = 'height: 3rem';
            $styles[] = 'border-radius: var(--hph-radius-md)';
            $styles[] = 'font-size: var(--hph-text-lg)';
            break;
        case 'diamond':
            $styles[] = 'width: 3rem';
            $styles[] = 'height: 3rem';
            $styles[] = 'border-radius: var(--hph-radius-md)';
            $styles[] = 'transform: rotate(45deg)';
            $styles[] = 'font-size: var(--hph-text-base)';
            break;
        case 'auto':
        default:
            $styles[] = 'width: 2.5rem';
            $styles[] = 'height: 2.5rem';
            $styles[] = 'border-radius: var(--hph-radius-full)';
            $styles[] = 'font-size: var(--hph-text-base)';
            break;
    }
    
    return implode('; ', $styles);
}
?>

<section 
    class="hph-process-section hph-process-<?php echo esc_attr($style); ?>"
    <?php if ($section_id): ?>id="<?php echo esc_attr($section_id); ?>"<?php endif; ?>
    data-bg="<?php echo esc_attr($theme); ?>"
    style="<?php echo implode('; ', $section_styles); ?>"
    data-animation="<?php echo $animation ? 'true' : 'false'; ?>"
>
    <div style="<?php echo implode('; ', $container_styles); ?>">
        
        <?php if ($badge || $headline || $subheadline || $content): ?>
        <!-- Section Header -->
        <div style="margin-bottom: var(--hph-margin-3xl); <?php echo $header_alignment; ?> <?php echo $animation ? 'animation: fadeInUp 0.8s ease-out;' : ''; ?>">
            
            <?php if ($badge): ?>
            <!-- Badge -->
            <div style="margin-bottom: var(--hph-margin-lg);">
                <span style="display: inline-block; padding: var(--hph-padding-sm) var(--hph-padding-md); background: var(--hph-primary-100); color: var(--hph-primary-700); border-radius: var(--hph-radius-full); font-size: var(--hph-text-sm); font-weight: var(--hph-font-semibold);">
                    <?php echo esc_html($badge); ?>
                </span>
            </div>
            <?php endif; ?>
            
            <?php if ($headline): ?>
            <!-- Headline -->
            <h2 style="margin: 0 0 var(--hph-margin-lg) 0; font-size: var(--hph-text-4xl); font-weight: var(--hph-font-bold); line-height: var(--hph-leading-tight);">
                <?php echo esc_html($headline); ?>
            </h2>
            <?php endif; ?>
            
            <?php if ($subheadline): ?>
            <!-- Subheadline -->
            <p style="margin: 0 0 var(--hph-margin-lg) 0; font-size: var(--hph-text-xl); font-weight: var(--hph-font-medium); opacity: 0.9;">
                <?php echo esc_html($subheadline); ?>
            </p>
            <?php endif; ?>
            
            <?php if ($content): ?>
            <!-- Content -->
            <div style="font-size: var(--hph-text-base); line-height: var(--hph-leading-relaxed); max-width: 65ch; margin-left: auto; margin-right: auto; opacity: 0.8;">
                <?php echo wp_kses_post($content); ?>
            </div>
            <?php endif; ?>
            
        </div>
        <?php endif; ?>
        
        <?php if (!empty($steps)): ?>
        <!-- Process Steps -->
        <div style="<?php echo implode('; ', $process_styles); ?>">
            
            <?php if ($style === 'timeline' && $connector_style !== 'none'): ?>
            <!-- Timeline Connector Line -->
            <div style="position: absolute; left: 1.25rem; top: 2rem; bottom: 2rem; width: 2px; background: var(--hph-gray-200); z-index: 1;"></div>
            <?php endif; ?>
            
            <?php foreach ($steps as $index => $step): 
                $step_defaults = array(
                    'number' => $index + 1,
                    'title' => '',
                    'description' => '',
                    'icon' => '',
                    'image' => ''
                );
                $step = wp_parse_args($step, $step_defaults);
                
                // Build step item styles
                $item_styles = array();
                
                if ($style === 'cards') {
                    $item_styles[] = 'background: var(--hph-white)';
                    $item_styles[] = 'border-radius: var(--hph-radius-xl)';
                    $item_styles[] = 'box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1)';
                    $item_styles[] = 'padding: var(--hph-padding-xl)';
                    $item_styles[] = 'text-align: center';
                    $item_styles[] = 'transition: all 300ms ease';
                } elseif ($style === 'timeline') {
                    $item_styles[] = 'position: relative';
                    $item_styles[] = 'padding-left: 4rem';
                    $item_styles[] = 'z-index: 2';
                } elseif ($style === 'icons-left') {
                    $item_styles[] = 'display: flex';
                    $item_styles[] = 'align-items: flex-start';
                    $item_styles[] = 'gap: var(--hph-gap-lg)';
                } else {
                    $item_styles[] = 'text-align: center';
                }
                
                $animation_delay = $animation ? 'animation: fadeInUp 0.8s ease-out ' . ($index * 0.15) . 's; opacity: 0; animation-fill-mode: forwards;' : '';
                $is_last_step = $index === count($steps) - 1;
            ?>
            
            <div 
                class="hph-process-step"
                style="<?php echo implode('; ', $item_styles); ?> <?php echo $animation_delay; ?>"
                <?php if ($style === 'cards'): ?>
                onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 8px 25px rgba(0, 0, 0, 0.15)';"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(0, 0, 0, 0.1)';"
                <?php endif; ?>
            >
                
                <!-- Step Indicator/Icon -->
                <div style="<?php echo $style === 'icons-left' ? 'flex-shrink: 0;' : 'margin-bottom: var(--hph-margin-lg);'; ?>">
                    
                    <?php if ($style === 'timeline'): ?>
                    <!-- Timeline Step Indicator -->
                    <div style="position: absolute; left: 0; top: 0;">
                        <div style="<?php echo getStepIndicatorStyles($step_style, $index, $theme); ?>">
                            <?php if ($step['icon']): ?>
                                <i class="<?php echo esc_attr($step['icon']); ?>"></i>
                            <?php else: ?>
                                <?php echo intval($step['number']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php elseif ($step['icon']): ?>
                    <!-- Icon Display -->
                    <div style="display: inline-flex; align-items: center; justify-content: center; width: 4rem; height: 4rem; background: var(--hph-primary-100); color: var(--hph-primary); border-radius: var(--hph-radius-full); margin-bottom: var(--hph-margin-md);">
                        <i class="<?php echo esc_attr($step['icon']); ?>" style="font-size: var(--hph-text-2xl);"></i>
                    </div>
                    
                    <?php elseif ($style === 'numbered' || $style === 'icons-top'): ?>
                    <!-- Number Display -->
                    <div style="<?php echo getStepIndicatorStyles($step_style, $index, $theme); ?>">
                        <?php echo intval($step['number']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($style === 'icons-top' && !$is_last_step && $connector_style !== 'none'): ?>
                    <!-- Horizontal Connector -->
                    <div style="position: absolute; top: 1.5rem; left: calc(50% + 2rem); right: calc(-50% + 2rem); height: 2px; background: var(--hph-gray-200); z-index: -1;"></div>
                    <?php endif; ?>
                    
                </div>
                
                <!-- Step Content -->
                <div style="<?php echo $style === 'icons-left' ? 'flex: 1;' : ''; ?>">
                    
                    <?php if ($step['title']): ?>
                    <!-- Title -->
                    <h3 style="margin: 0 0 var(--hph-margin-sm) 0; font-size: var(--hph-text-xl); font-weight: var(--hph-font-bold); line-height: var(--hph-leading-tight);">
                        <?php echo esc_html($step['title']); ?>
                    </h3>
                    <?php endif; ?>
                    
                    <?php if ($step['description']): ?>
                    <!-- Description -->
                    <p style="margin: 0; color: var(--hph-gray-600); line-height: var(--hph-leading-relaxed); font-size: var(--hph-text-base);">
                        <?php echo wp_kses_post($step['description']); ?>
                    </p>
                    <?php endif; ?>
                    
                    <?php if ($step['image']): ?>
                    <!-- Step Image -->
                    <div style="margin-top: var(--hph-margin-md);">
                        <img 
                            src="<?php echo esc_url($step['image']); ?>" 
                            alt="<?php echo esc_attr($step['title']); ?>"
                            style="width: 100%; height: auto; border-radius: var(--hph-radius-lg);"
                            loading="lazy"
                        >
                    </div>
                    <?php endif; ?>
                    
                </div>
                
            </div>
            
            <?php endforeach; ?>
            
        </div>
        <?php endif; ?>
        
    </div>
</section>

<?php if ($animation): ?>
<style>
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
</style>
<?php endif; ?>

<style>
/* Responsive adjustments */
@media (max-width: 1024px) {
    .hph-process-section [style*="position: absolute"][style*="left: calc"] {
        display: none; /* Hide horizontal connectors on tablet/mobile */
    }
}

@media (max-width: 768px) {
    .hph-process-section [style*="grid-template-columns"] {
        grid-template-columns: 1fr !important;
        gap: var(--hph-gap-lg);
    }
    
    .hph-process-section .hph-process-step {
        text-align: center !important;
    }
    
    .hph-process-section [style*="display: flex"] .hph-process-step {
        flex-direction: column;
        text-align: center;
        align-items: center;
    }
    
    .hph-process-section [style*="padding-left: 4rem"] {
        padding-left: 0 !important;
        padding-top: var(--hph-padding-lg);
        text-align: center;
    }
    
    .hph-process-section [style*="position: absolute; left: 1.25rem"] {
        display: none; /* Hide timeline connector on mobile */
    }
    
    .hph-process-section [style*="position: absolute; left: 0; top: 0"] {
        position: relative !important;
        left: auto !important;
        top: auto !important;
        margin-bottom: var(--hph-margin-lg);
        text-align: center;
    }
}

@media (max-width: 480px) {
    .hph-process-section [style*="padding: var(--hph-padding-xl)"] {
        padding: var(--hph-padding-lg) !important;
    }
}

/* Diamond shape text alignment fix */
.hph-process-step [style*="transform: rotate(45deg)"] {
    transform: rotate(45deg);
}

.hph-process-step [style*="transform: rotate(45deg)"] > * {
    transform: rotate(-45deg);
}
</style>
