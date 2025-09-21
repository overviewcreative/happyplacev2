<?php
/**
 * HPH Testimonials Section Template
 * Displays customer testimonials with carousel, grid, or list layouts
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Register this template part for asset loading
if (function_exists('hph_register_template_part')) {
    hph_register_template_part('sections/testimonials');
}

// Default arguments
$defaults = array(
    'style' => 'carousel', // Options: 'carousel', 'grid', 'list', 'cards', 'masonry'
    'theme' => 'light', // Options: 'white', 'light', 'dark', 'primary'
    'columns' => 3, // Number of columns for grid layout (1-4)
    'padding' => 'xl', // Options: 'sm', 'md', 'lg', 'xl', '2xl'
    'container' => 'default', // Options: 'narrow', 'default', 'wide', 'full'
    'alignment' => 'center', // Options: 'left', 'center', 'right'
    'badge' => '', // Badge text to display above headline
    'headline' => 'What Our Clients Say', // Main headline text
    'subheadline' => '', // Subheadline text below headline
    'content' => '', // Additional content text
    'testimonials' => array(), // Array of testimonial objects with quote, name, company, etc.
    'show_avatars' => true, // Boolean: true/false - display customer avatars
    'show_ratings' => true, // Boolean: true/false - display star ratings
    'show_company' => true, // Boolean: true/false - display company names
    'avatar_style' => 'circle', // Options: 'circle', 'square', 'rounded'
    'card_style' => 'elevated', // Options: 'elevated', 'outlined', 'minimal'
    'auto_scroll' => false, // Boolean: true/false - auto-scroll carousel
    'scroll_speed' => 5000, // Milliseconds between auto-scroll (1000-10000)
    'animation' => false, // Boolean: true/false - enable entrance animations
    'section_id' => '' // HTML ID for the section
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
    case 'white':
        $section_styles[] = 'background-color: var(--hph-white)';
        $section_styles[] = 'color: var(--hph-text-color)';
        break;
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
}

// Padding styles
switch ($padding) {
    case 'sm':
        $section_styles[] = 'padding-top: var(--hph-space-6)';
        $section_styles[] = 'padding-bottom: var(--hph-space-6)';
        break;
    case 'md':
        $section_styles[] = 'padding-top: var(--hph-space-8)';
        $section_styles[] = 'padding-bottom: var(--hph-space-8)';
        break;
    case 'lg':
        $section_styles[] = 'padding-top: var(--hph-space-12)';
        $section_styles[] = 'padding-bottom: var(--hph-space-12)';
        break;
    case '2xl':
        $section_styles[] = 'padding-top: var(--hph-space-24)';
        $section_styles[] = 'padding-bottom: var(--hph-space-24)';
        break;
    case 'xl':
    default:
        $section_styles[] = 'padding-top: var(--hph-space-16)';
        $section_styles[] = 'padding-bottom: var(--hph-space-16)';
        break;
}

// Container styles
$container_styles = array(
    'position: relative',
    'margin-left: auto',
    'margin-right: auto',
    'padding-left: var(--hph-space-6)',
    'padding-right: var(--hph-space-6)'
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

// Testimonials container styles
$testimonials_styles = array();

if ($style === 'carousel') {
    $testimonials_styles[] = 'display: flex';
    $testimonials_styles[] = 'gap: var(--hph-gap-xl)';
    $testimonials_styles[] = 'overflow-x: auto';
    $testimonials_styles[] = 'scroll-snap-type: x mandatory';
    $testimonials_styles[] = 'padding-bottom: var(--hph-space-2)';
    $testimonials_styles[] = 'scroll-behavior: smooth';
} elseif ($style === 'grid' || $style === 'cards') {
    $testimonials_styles[] = 'display: grid';
    $testimonials_styles[] = 'gap: var(--hph-gap-xl)';
    
    switch ($columns) {
        case 2:
            $testimonials_styles[] = 'grid-template-columns: repeat(auto-fit, minmax(350px, 1fr))';
            break;
        case 4:
            $testimonials_styles[] = 'grid-template-columns: repeat(auto-fit, minmax(280px, 1fr))';
            break;
        case 3:
        default:
            $testimonials_styles[] = 'grid-template-columns: repeat(auto-fit, minmax(320px, 1fr))';
            break;
    }
} elseif ($style === 'list') {
    $testimonials_styles[] = 'display: flex';
    $testimonials_styles[] = 'flex-direction: column';
    $testimonials_styles[] = 'gap: var(--hph-gap-2xl)';
} elseif ($style === 'masonry') {
    $testimonials_styles[] = 'display: grid';
    $testimonials_styles[] = 'grid-template-columns: repeat(auto-fit, minmax(320px, 1fr))';
    $testimonials_styles[] = 'gap: var(--hph-gap-xl)';
    $testimonials_styles[] = 'align-items: start';
}

// Generate unique carousel ID for auto-scroll
$carousel_id = 'hph_testimonials_' . uniqid();
?>

<section 
    class="hph-testimonials-section hph-testimonials-<?php echo esc_attr($style); ?>"
    <?php if ($section_id): ?>id="<?php echo esc_attr($section_id); ?>"<?php endif; ?>
    data-bg="<?php echo esc_attr($theme); ?>"
    style="<?php echo implode('; ', $section_styles); ?>"
    data-animation="<?php echo $animation ? 'true' : 'false'; ?>"
>
    <div style="<?php echo implode('; ', $container_styles); ?>">
        
        <?php if ($badge || $headline || $subheadline || $content): ?>
        <!-- Section Header -->
        <div style="margin-bottom: var(--hph-space-16); <?php echo $header_alignment; ?> <?php echo $animation ? 'animation: fadeInUp 0.8s ease-out;' : ''; ?>">
            
            <?php if ($badge): ?>
            <!-- Badge -->
            <div style="margin-bottom: var(--hph-space-6);">
                <span style="display: inline-block; padding: var(--hph-space-2) var(--hph-space-4); background: var(--hph-primary-100); color: var(--hph-primary-700); border-radius: var(--hph-radius-full); font-size: var(--hph-text-sm); font-weight: var(--hph-font-semibold);">
                    <?php echo esc_html($badge); ?>
                </span>
            </div>
            <?php endif; ?>
            
            <?php if ($headline): ?>
            <!-- Headline -->
            <h2 style="margin: 0 0 var(--hph-space-6) 0; font-size: var(--hph-text-4xl); font-weight: var(--hph-font-bold); line-height: var(--hph-leading-tight);">
                <?php echo esc_html($headline); ?>
            </h2>
            <?php endif; ?>
            
            <?php if ($subheadline): ?>
            <!-- Subheadline -->
            <p style="margin: 0 0 var(--hph-space-6) 0; font-size: var(--hph-text-xl); font-weight: var(--hph-font-medium); opacity: 0.9;">
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
        
        <?php if (!empty($testimonials)): ?>
        <!-- Testimonials -->
        <div 
            id="<?php echo esc_attr($carousel_id); ?>"
            class="hph-testimonials-container"
            style="<?php echo implode('; ', $testimonials_styles); ?>"
            <?php if ($style === 'carousel' && $auto_scroll): ?>
            data-auto-scroll="true"
            data-scroll-speed="<?php echo intval($scroll_speed); ?>"
            <?php endif; ?>
        >
            
            <?php foreach ($testimonials as $index => $testimonial): 
                $testimonial_defaults = array(
                    'content' => '',
                    'author' => '',
                    'position' => '',
                    'company' => '',
                    'avatar' => '',
                    'rating' => 5,
                    'date' => '',
                    'featured' => false
                );
                $testimonial = wp_parse_args($testimonial, $testimonial_defaults);
                
                // Build testimonial item styles
                $item_styles = array();
                
                if ($style === 'cards' || ($style === 'carousel' && $card_style === 'elevated')) {
                    $item_styles[] = 'background: var(--hph-white)';
                    $item_styles[] = 'border-radius: var(--hph-radius-xl)';
                    
                    if ($card_style === 'elevated') {
                        $item_styles[] = 'box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1)';
                    } elseif ($card_style === 'outlined') {
                        $item_styles[] = 'border: 1px solid var(--hph-gray-200)';
                    }
                    
                    $item_styles[] = 'padding: var(--hph-space-8)';
                    $item_styles[] = 'transition: all 300ms ease';
                } elseif ($style === 'list') {
                    $item_styles[] = 'display: flex';
                    $item_styles[] = 'align-items: flex-start';
                    $item_styles[] = 'gap: var(--hph-gap-lg)';
                    $item_styles[] = 'padding: var(--hph-space-8)';
                    $item_styles[] = 'border-left: 4px solid var(--hph-primary)';
                    $item_styles[] = 'background: var(--hph-white)';
                    $item_styles[] = 'border-radius: var(--hph-radius-lg)';
                } else {
                    $item_styles[] = 'padding: var(--hph-space-8)';
                    $item_styles[] = 'background: var(--hph-white)';
                    $item_styles[] = 'border-radius: var(--hph-radius-lg)';
                    $item_styles[] = 'box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05)';
                }
                
                if ($style === 'carousel') {
                    $item_styles[] = 'min-width: 320px';
                    $item_styles[] = 'scroll-snap-align: start';
                }
                
                $animation_delay = $animation ? 'animation: fadeInUp 0.8s ease-out ' . ($index * 0.1) . 's; opacity: 0; animation-fill-mode: forwards;' : '';
            ?>
            
            <div 
                class="hph-testimonial-item <?php echo $testimonial['featured'] ? 'hph-testimonial-featured' : ''; ?>"
                style="<?php echo implode('; ', $item_styles); ?> <?php echo $animation_delay; ?>"
                <?php if ($card_style === 'elevated' && ($style === 'cards' || $style === 'carousel')): ?>
                onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 25px rgba(0, 0, 0, 0.15)';"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 20px rgba(0, 0, 0, 0.1)';"
                <?php endif; ?>
            >
                
                <?php if ($testimonial['featured']): ?>
                <!-- Featured Badge -->
                <div style="position: absolute; top: -8px; right: var(--hph-spacing-lg); background: var(--hph-primary); color: var(--hph-white); padding: var(--hph-space-1) var(--hph-space-2); border-radius: var(--hph-radius-md); font-size: var(--hph-text-xs); font-weight: var(--hph-font-semibold);">
                    Featured
                </div>
                <?php endif; ?>
                
                <?php if ($show_ratings && $testimonial['rating']): ?>
                <!-- Rating Stars -->
                <div style="margin-bottom: var(--hph-space-6); <?php echo $style === 'list' ? '' : 'text-align: center;'; ?>">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star" style="color: <?php echo $i <= $testimonial['rating'] ? 'var(--hph-warning)' : 'var(--hph-gray-300)'; ?>; margin-right: 2px; font-size: var(--hph-text-base);"></i>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
                
                <!-- Testimonial Content -->
                <div style="<?php echo $style === 'list' ? 'order: 2; flex: 1;' : 'margin-bottom: var(--hph-space-6);'; ?>">
                    
                    <?php if ($testimonial['content']): ?>
                    <!-- Quote -->
                    <blockquote style="margin: 0; font-size: var(--hph-text-lg); line-height: var(--hph-leading-relaxed); color: var(--hph-gray-700); font-style: italic; position: relative;">
                        <!-- Opening Quote Mark -->
                        <span style="position: absolute; top: -0.5rem; left: -1rem; font-size: var(--hph-text-4xl); color: var(--hph-primary); opacity: 0.3; font-family: serif;">"</span>
                        <?php echo wp_kses_post($testimonial['content']); ?>
                        <!-- Closing Quote Mark -->
                        <span style="font-size: var(--hph-text-2xl); color: var(--hph-primary); opacity: 0.3; font-family: serif; margin-left: 4px;">"</span>
                    </blockquote>
                    <?php endif; ?>
                    
                </div>
                
                <!-- Author Info -->
                <div style="<?php echo $style === 'list' ? 'order: 1; flex-shrink: 0;' : ''; ?>">
                    
                    <?php if ($show_avatars || $testimonial['author'] || $testimonial['position'] || ($show_company && $testimonial['company'])): ?>
                    <div style="display: flex; align-items: center; gap: var(--hph-gap-md); <?php echo $style === 'list' ? 'flex-direction: column; text-align: center;' : ''; ?>">
                        
                        <?php if ($show_avatars && $testimonial['avatar']): ?>
                        <!-- Avatar -->
                        <div style="<?php echo $style === 'list' ? 'margin-bottom: var(--hph-space-2);' : 'flex-shrink: 0;'; ?>">
                            <img 
                                src="<?php echo esc_url($testimonial['avatar']); ?>" 
                                alt="<?php echo esc_attr($testimonial['author']); ?>"
                                style="width: 3rem; height: 3rem; object-fit: cover; 
                                       <?php echo $avatar_style === 'circle' ? 'border-radius: var(--hph-radius-full);' : ($avatar_style === 'rounded' ? 'border-radius: var(--hph-radius-lg);' : 'border-radius: var(--hph-radius-md);'); ?>"
                                loading="lazy"
                            >
                        </div>
                        <?php elseif ($show_avatars): ?>
                        <!-- Default Avatar -->
                        <div style="<?php echo $style === 'list' ? 'margin-bottom: var(--hph-space-2);' : 'flex-shrink: 0;'; ?>">
                            <div style="width: 3rem; height: 3rem; background: var(--hph-gray-300); color: var(--hph-gray-600); display: flex; align-items: center; justify-content: center; font-weight: var(--hph-font-semibold); 
                                        <?php echo $avatar_style === 'circle' ? 'border-radius: var(--hph-radius-full);' : ($avatar_style === 'rounded' ? 'border-radius: var(--hph-radius-lg);' : 'border-radius: var(--hph-radius-md);'); ?>">
                                <?php echo $testimonial['author'] ? strtoupper(substr($testimonial['author'], 0, 1)) : '?'; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Author Details -->
                        <div style="<?php echo $style === 'list' ? 'text-align: center;' : 'flex: 1; min-width: 0;'; ?>">
                            
                            <?php if ($testimonial['author']): ?>
                            <div style="font-weight: var(--hph-font-semibold); color: var(--hph-gray-900); font-size: var(--hph-text-base);">
                                <?php echo esc_html($testimonial['author']); ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($testimonial['position'] || ($show_company && $testimonial['company'])): ?>
                            <div style="font-size: var(--hph-text-sm); color: var(--hph-gray-600); margin-top: 2px;">
                                <?php 
                                $title_parts = array();
                                if ($testimonial['position']) {
                                    $title_parts[] = $testimonial['position'];
                                }
                                if ($show_company && $testimonial['company']) {
                                    $title_parts[] = $testimonial['company'];
                                }
                                echo esc_html(implode(', ', $title_parts));
                                ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($testimonial['date']): ?>
                            <div style="font-size: var(--hph-text-xs); color: var(--hph-gray-500); margin-top: 4px;">
                                <?php echo esc_html($testimonial['date']); ?>
                            </div>
                            <?php endif; ?>
                            
                        </div>
                        
                    </div>
                    <?php endif; ?>
                    
                </div>
                
            </div>
            
            <?php endforeach; ?>
            
        </div>
        
        <?php if ($style === 'carousel'): ?>
        <!-- Carousel Navigation -->
        <div style="display: flex; justify-content: center; align-items: center; gap: var(--hph-gap-md); margin-top: var(--hph-space-8);">
            <button 
                id="<?php echo esc_attr($carousel_id); ?>_prev"
                style="display: inline-flex; align-items: center; justify-content: center; width: 2.5rem; height: 2.5rem; background: var(--hph-white); color: var(--hph-primary); border: 1px solid var(--hph-primary); border-radius: var(--hph-radius-full); cursor: pointer; transition: all 0.2s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"
                onmouseover="this.style.background='var(--hph-primary)'; this.style.color='var(--hph-white)'"
                onmouseout="this.style.background='var(--hph-white)'; this.style.color='var(--hph-primary)'"
            >
                <i class="fas fa-chevron-left"></i>
            </button>
            
            <div id="<?php echo esc_attr($carousel_id); ?>_indicators" style="display: flex; gap: var(--hph-gap-sm);"></div>
            
            <button 
                id="<?php echo esc_attr($carousel_id); ?>_next"
                style="display: inline-flex; align-items: center; justify-content: center; width: 2.5rem; height: 2.5rem; background: var(--hph-white); color: var(--hph-primary); border: 1px solid var(--hph-primary); border-radius: var(--hph-radius-full); cursor: pointer; transition: all 0.2s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"
                onmouseover="this.style.background='var(--hph-primary)'; this.style.color='var(--hph-white)'"
                onmouseout="this.style.background='var(--hph-white)'; this.style.color='var(--hph-primary)'"
            >
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        <?php endif; ?>
        
        <?php endif; ?>
        
    </div>
</section>

<?php if ($style === 'carousel'): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('<?php echo esc_js($carousel_id); ?>');
    const prevBtn = document.getElementById('<?php echo esc_js($carousel_id); ?>_prev');
    const nextBtn = document.getElementById('<?php echo esc_js($carousel_id); ?>_next');
    const indicators = document.getElementById('<?php echo esc_js($carousel_id); ?>_indicators');
    
    if (!carousel || !prevBtn || !nextBtn || !indicators) return;
    
    const items = carousel.querySelectorAll('.hph-testimonial-item');
    if (items.length === 0) return;
    
    let currentIndex = 0;
    let autoScrollInterval;
    
    // Create indicators
    items.forEach((_, index) => {
        const indicator = document.createElement('button');
        indicator.style.cssText = 'width: 0.5rem; height: 0.5rem; border-radius: 50%; border: none; cursor: pointer; transition: all 0.2s ease; background: var(--hph-gray-300);';
        indicator.onclick = () => scrollToIndex(index);
        indicators.appendChild(indicator);
    });
    
    const indicatorButtons = indicators.querySelectorAll('button');
    
    function updateIndicators() {
        indicatorButtons.forEach((btn, index) => {
            btn.style.background = index === currentIndex ? 'var(--hph-primary)' : 'var(--hph-gray-300)';
        });
    }
    
    function scrollToIndex(index) {
        if (index < 0 || index >= items.length) return;
        
        currentIndex = index;
        const item = items[currentIndex];
        carousel.scrollTo({
            left: item.offsetLeft - carousel.offsetLeft,
            behavior: 'smooth'
        });
        updateIndicators();
        
        // Restart auto-scroll if enabled
        if (carousel.dataset.autoScroll === 'true') {
            clearInterval(autoScrollInterval);
            startAutoScroll();
        }
    }
    
    function scrollNext() {
        const nextIndex = currentIndex + 1 >= items.length ? 0 : currentIndex + 1;
        scrollToIndex(nextIndex);
    }
    
    function scrollPrev() {
        const prevIndex = currentIndex - 1 < 0 ? items.length - 1 : currentIndex - 1;
        scrollToIndex(prevIndex);
    }
    
    function startAutoScroll() {
        if (carousel.dataset.autoScroll === 'true') {
            const speed = parseInt(carousel.dataset.scrollSpeed) || <?php echo intval($scroll_speed); ?>;
            autoScrollInterval = setInterval(scrollNext, speed);
        }
    }
    
    // Event listeners
    prevBtn.onclick = scrollPrev;
    nextBtn.onclick = scrollNext;
    
    // Pause auto-scroll on hover
    carousel.onmouseenter = () => clearInterval(autoScrollInterval);
    carousel.onmouseleave = startAutoScroll;
    
    // Initialize
    updateIndicators();
    startAutoScroll();
    
    // Handle resize
    window.addEventListener('resize', () => {
        scrollToIndex(currentIndex);
    });
});
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
/* Carousel scrollbar styling */
.hph-testimonials-container::-webkit-scrollbar {
    height: 6px;
}

.hph-testimonials-container::-webkit-scrollbar-track {
    background: var(--hph-gray-100);
    border-radius: 3px;
}

.hph-testimonials-container::-webkit-scrollbar-thumb {
    background: var(--hph-primary);
    border-radius: 3px;
}

.hph-testimonials-container::-webkit-scrollbar-thumb:hover {
    background: var(--hph-primary-dark);
}

/* Featured testimonial styling */
.hph-testimonial-featured {
    position: relative;
    border: 2px solid var(--hph-primary) !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .hph-testimonials-section [style*="grid-template-columns"] {
        grid-template-columns: 1fr !important;
        gap: var(--hph-gap-lg);
    }
    
    .hph-testimonials-section .hph-testimonial-item {
        min-width: 280px !important;
    }
    
    .hph-testimonials-section [style*="display: flex"] .hph-testimonial-item {
        flex-direction: column;
        text-align: center;
    }
    
    .hph-testimonials-section [style*="display: flex"] .hph-testimonial-item > div:first-child {
        order: 2;
        margin-bottom: 0;
        margin-top: var(--hph-space-6);
    }
}

@media (max-width: 480px) {
    .hph-testimonials-section .hph-testimonial-item {
        min-width: 260px !important;
        padding: var(--hph-space-6) !important;
    }
}
</style>
