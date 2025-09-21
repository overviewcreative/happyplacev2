<?php
/**
 * HPH FAQ Section Template
 * Displays frequently asked questions with accordion or grid layouts
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Register this template part for asset loading
if (function_exists('hph_register_template_part')) {
    hph_register_template_part('sections/faq');
}

// Default arguments
$defaults = array(
    'style' => 'accordion', // Options: 'accordion', 'grid', 'list', 'cards'
    'theme' => 'white', // Color theme: 'white', 'light', 'dark', 'primary'
    'columns' => 2, // For grid layout
    'padding' => 'xl',
    'container' => 'default',
    'alignment' => 'center',
    'badge' => '',
    'headline' => 'Frequently Asked Questions',
    'subheadline' => '',
    'content' => '',
    'faqs' => array(),
    'accordion_style' => 'clean', // Options: 'clean', 'bordered', 'elevated'
    'animation' => false,
    'section_id' => ''
);

// Merge with provided args
$config = wp_parse_args($args ?? array(), $defaults);
extract($config);

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

// FAQ container styles
$faq_styles = array();

if ($style === 'grid' || $style === 'cards') {
    $faq_styles[] = 'display: grid';
    $faq_styles[] = 'gap: var(--hph-gap-xl)';
    
    switch ($columns) {
        case 2:
            $faq_styles[] = 'grid-template-columns: repeat(auto-fit, minmax(400px, 1fr))';
            break;
        case 3:
            $faq_styles[] = 'grid-template-columns: repeat(auto-fit, minmax(350px, 1fr))';
            break;
        default:
            $faq_styles[] = 'grid-template-columns: 1fr';
    }
} elseif ($style === 'list') {
    $faq_styles[] = 'display: flex';
    $faq_styles[] = 'flex-direction: column';
    $faq_styles[] = 'gap: var(--hph-gap-lg)';
} else { // accordion
    $faq_styles[] = 'display: flex';
    $faq_styles[] = 'flex-direction: column';
    $faq_styles[] = 'gap: var(--hph-gap-sm)';
    $faq_styles[] = 'max-width: 800px';
    $faq_styles[] = 'margin-left: auto';
    $faq_styles[] = 'margin-right: auto';
}

// Generate unique ID for accordion functionality
$faq_section_id = 'hph_faq_' . uniqid();
?>

<section 
    class="hph-faq-section hph-faq-<?php echo esc_attr($style); ?>"
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
        
        <?php if (!empty($faqs)): ?>
        <!-- FAQ Items -->
        <div 
            id="<?php echo esc_attr($faq_section_id); ?>"
            class="hph-faq-container"
            style="<?php echo implode('; ', $faq_styles); ?>"
        >
            
            <?php foreach ($faqs as $index => $faq): 
                $faq_defaults = array(
                    'question' => '',
                    'answer' => '',
                    'icon' => ''
                );
                $faq = wp_parse_args($faq, $faq_defaults);
                
                $item_id = $faq_section_id . '_item_' . $index;
                $animation_delay = $animation ? 'animation: fadeInUp 0.8s ease-out ' . ($index * 0.1) . 's; opacity: 0; animation-fill-mode: forwards;' : '';
            ?>
            
            <?php if ($style === 'accordion'): ?>
            <!-- Accordion Item -->
            <div 
                class="hph-faq-item"
                style="<?php echo $accordion_style === 'bordered' ? 'border: 1px solid var(--hph-gray-200); border-radius: var(--hph-radius-lg);' : ''; ?> <?php echo $accordion_style === 'elevated' ? 'background: var(--hph-white); box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: var(--hph-radius-lg);' : ''; ?> <?php echo $animation_delay; ?>"
            >
                <!-- Question Button -->
                <button 
                    class="hph-faq-question"
                    onclick="toggleFAQ('<?php echo esc_js($item_id); ?>')"
                    style="width: 100%; padding: var(--hph-padding-lg); background: none; border: none; text-align: left; cursor: pointer; display: flex; align-items: center; justify-content: space-between; font-size: var(--hph-text-lg); font-weight: var(--hph-font-semibold); color: var(--hph-gray-900); <?php echo $accordion_style === 'clean' ? 'border-bottom: 1px solid var(--hph-gray-200);' : ''; ?>"
                    aria-expanded="false"
                    aria-controls="<?php echo esc_attr($item_id); ?>_answer"
                >
                    <span style="display: flex; align-items: center; gap: var(--hph-gap-md); flex: 1;">
                        <?php if ($faq['icon']): ?>
                        <i class="<?php echo esc_attr($faq['icon']); ?>" style="color: var(--hph-primary);"></i>
                        <?php endif; ?>
                        <?php echo esc_html($faq['question']); ?>
                    </span>
                    <i class="hph-faq-chevron fas fa-chevron-down" style="color: var(--hph-primary); transition: transform 0.3s ease;"></i>
                </button>
                
                <!-- Answer Content -->
                <div 
                    id="<?php echo esc_attr($item_id); ?>_answer"
                    class="hph-faq-answer"
                    style="max-height: 0; overflow: hidden; transition: max-height 0.3s ease, padding 0.3s ease;"
                >
                    <div style="padding: 0 var(--hph-padding-lg) var(--hph-padding-lg) var(--hph-padding-lg); color: var(--hph-gray-600); line-height: var(--hph-leading-relaxed);">
                        <?php echo wp_kses_post($faq['answer']); ?>
                    </div>
                </div>
            </div>
            
            <?php else: ?>
            <!-- Grid/List/Card Item -->
            <div 
                class="hph-faq-item"
                style="<?php echo $style === 'cards' ? 'background: var(--hph-white); padding: var(--hph-padding-xl); border-radius: var(--hph-radius-xl); box-shadow: 0 4px 15px rgba(0,0,0,0.1);' : 'padding: var(--hph-padding-lg);'; ?> <?php echo $animation_delay; ?>"
            >
                <!-- Question -->
                <h3 style="margin: 0 0 var(--hph-margin-md) 0; font-size: var(--hph-text-lg); font-weight: var(--hph-font-semibold); color: var(--hph-gray-900); display: flex; align-items: center; gap: var(--hph-gap-sm);">
                    <?php if ($faq['icon']): ?>
                    <i class="<?php echo esc_attr($faq['icon']); ?>" style="color: var(--hph-primary);"></i>
                    <?php endif; ?>
                    <?php echo esc_html($faq['question']); ?>
                </h3>
                
                <!-- Answer -->
                <div style="color: var(--hph-gray-600); line-height: var(--hph-leading-relaxed);">
                    <?php echo wp_kses_post($faq['answer']); ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php endforeach; ?>
            
        </div>
        <?php endif; ?>
        
    </div>
</section>

<?php if ($style === 'accordion'): ?>
<script>
function toggleFAQ(itemId) {
    const answerEl = document.getElementById(itemId + '_answer');
    const buttonEl = answerEl.previousElementSibling;
    const chevronEl = buttonEl.querySelector('.hph-faq-chevron');
    const isOpen = buttonEl.getAttribute('aria-expanded') === 'true';
    
    if (isOpen) {
        // Close
        answerEl.style.maxHeight = '0';
        buttonEl.setAttribute('aria-expanded', 'false');
        chevronEl.style.transform = 'rotate(0deg)';
    } else {
        // Close other open items (optional - comment out for multiple open)
        const container = document.getElementById('<?php echo esc_js($faq_section_id); ?>');
        container.querySelectorAll('.hph-faq-question[aria-expanded="true"]').forEach(openButton => {
            const openAnswer = openButton.nextElementSibling;
            const openChevron = openButton.querySelector('.hph-faq-chevron');
            openAnswer.style.maxHeight = '0';
            openButton.setAttribute('aria-expanded', 'false');
            openChevron.style.transform = 'rotate(0deg)';
        });
        
        // Open this item
        answerEl.style.maxHeight = answerEl.scrollHeight + 'px';
        buttonEl.setAttribute('aria-expanded', 'true');
        chevronEl.style.transform = 'rotate(180deg)';
    }
}
</script>
<?php endif; ?>

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
/* FAQ hover effects */
.hph-faq-question:hover {
    background: var(--hph-gray-50) !important;
}

.hph-faq-question:focus {
    outline: 2px solid var(--hph-primary);
    outline-offset: 2px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .hph-faq-section [style*="grid-template-columns"] {
        grid-template-columns: 1fr !important;
    }
    
    .hph-faq-question {
        font-size: var(--hph-text-base) !important;
        padding: var(--hph-padding-md) !important;
    }
}
</style>
