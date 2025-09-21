<?php
/**
 * HPH Stats Section Template - Modern Design
 * 
 * Beautiful statistics section with animated counters, modern cards, and responsive design
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Register this template part for asset loading
if (function_exists('hph_register_template_part')) {
    hph_register_template_part('sections/stats');
}

// Default arguments
$defaults = array(
    'style' => 'cards', // Options: 'cards', 'counters', 'minimal', 'gradient-cards', 'outlined'
    'theme' => 'primary', // Options: 'primary', 'white', 'dark', 'light', 'gradient'
    'layout' => 'grid', // Options: 'grid', 'inline', 'stacked'
    'padding' => 'xl', // Options: 'sm', 'md', 'lg', 'xl', '2xl'
    'content_width' => 'normal', // Options: 'narrow', 'normal', 'wide', 'full'
    'alignment' => 'center', // Options: 'left', 'center', 'right'
    'columns' => 0, // Number of columns (0 = auto-detect, 1-6)
    'background_image' => '', // URL to background image
    'overlay' => 'dark', // Options: 'dark', 'light', 'primary', 'none'
    'badge' => '', // Badge text to display above headline
    'headline' => '', // Main headline text
    'subheadline' => '', // Subheadline text below headline
    'content' => '', // Additional content text
    'stats' => array(), // Array of stat objects with number, label, icon, etc.
    'animate_counters' => true, // Boolean: true/false - animate numbers counting up
    'counter_duration' => 2000, // Milliseconds for counter animation (500-5000)
    'hover_effects' => true, // Boolean: true/false - enable card hover effects
    'section_id' => '' // HTML ID for the section
);

// Merge with provided args
$config = wp_parse_args($args ?? array(), $defaults);
extract($config);

// Auto-detect columns if not set
if (!$columns && !empty($stats)) {
    $stats_count = count($stats);
    if ($stats_count <= 2) {
        $columns = 2;
    } elseif ($stats_count <= 4) {
        $columns = $stats_count;
    } else {
        $columns = 4; // Max 4 columns
    }
}

// Build section styles
$section_styles = array(
    'position: relative',
    'width: 100%',
    'overflow: hidden'
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
    case 'gradient':
        $section_styles[] = 'background: var(--hph-gradient-primary)';
        $section_styles[] = 'color: var(--hph-white)';
        break;
}

// Background image handling
if ($background_image) {
    $section_styles[] = "background-image: url('" . esc_url($background_image) . "')";
    $section_styles[] = "background-size: cover";
    $section_styles[] = "background-position: center";
    $section_styles[] = "background-repeat: no-repeat";
    $section_styles[] = "background-attachment: fixed";
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
    'z-index: 1',
    'margin-left: auto',
    'margin-right: auto',
    'padding-left: var(--hph-space-6)',
    'padding-right: var(--hph-space-6)'
);

switch ($content_width) {
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
    case 'normal':
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

// Grid styles based on layout
$grid_styles = array();

if ($layout === 'grid') {
    $grid_styles[] = 'display: grid';
    $grid_styles[] = 'gap: var(--hph-gap-xl)';
    $grid_styles[] = 'grid-template-columns: repeat(auto-fit, minmax(250px, 1fr))';
    $grid_styles[] = 'justify-content: center';
    $grid_styles[] = 'align-items: stretch';
} elseif ($layout === 'inline') {
    $grid_styles[] = 'display: flex';
    $grid_styles[] = 'flex-wrap: wrap';
    $grid_styles[] = 'justify-content: center';
    $grid_styles[] = 'align-items: stretch';
    $grid_styles[] = 'gap: var(--hph-gap-xl)';
} elseif ($layout === 'stacked') {
    $grid_styles[] = 'display: flex';
    $grid_styles[] = 'flex-direction: column';
    $grid_styles[] = 'align-items: center';
    $grid_styles[] = 'gap: var(--hph-gap-2xl)';
    $grid_styles[] = 'max-width: 400px';
    $grid_styles[] = 'margin-left: auto';
    $grid_styles[] = 'margin-right: auto';
}

// Generate unique ID for animations
$stats_section_id = 'hph_stats_' . uniqid();

// Overlay styles for background images
$overlay_styles = array();
if ($background_image) {
    $overlay_styles[] = 'position: absolute';
    $overlay_styles[] = 'top: 0';
    $overlay_styles[] = 'left: 0';
    $overlay_styles[] = 'right: 0';
    $overlay_styles[] = 'bottom: 0';
    $overlay_styles[] = 'z-index: 0';
    
    switch ($overlay) {
        case 'dark':
            $overlay_styles[] = 'background: linear-gradient(135deg, rgba(0,0,0,0.7), rgba(0,0,0,0.5))';
            break;
        case 'light':
            $overlay_styles[] = 'background: linear-gradient(135deg, rgba(255,255,255,0.8), rgba(255,255,255,0.6))';
            break;
        case 'primary':
            $overlay_styles[] = 'background: linear-gradient(135deg, rgba(var(--hph-primary-rgb), 0.8), rgba(var(--hph-primary-rgb), 0.6))';
            break;
        case 'gradient':
            $overlay_styles[] = 'background: var(--hph-gradient-primary)';
            break;
    }
}
?>

<section 
    class="hph-stats-section hph-stats-<?php echo esc_attr($style); ?>"
    <?php if ($section_id): ?>id="<?php echo esc_attr($section_id); ?>"<?php endif; ?>
    data-bg="<?php echo esc_attr($theme); ?>"
    data-style="<?php echo esc_attr($style); ?>"
    style="<?php echo implode('; ', $section_styles); ?>"
>
    
    <?php if ($background_image && !empty($overlay_styles)): ?>
    <!-- Background Overlay -->
    <div style="<?php echo implode('; ', $overlay_styles); ?>"></div>
    <?php endif; ?>
    
    <!-- Container -->
    <div style="<?php echo implode('; ', $container_styles); ?>">
        
        <?php if ($badge || $headline || $subheadline || $content): ?>
        <!-- Section Header -->
        <div style="margin-bottom: var(--hph-space-16); <?php echo $header_alignment; ?>">
            
            <?php if ($badge): ?>
            <!-- Badge -->
            <div style="margin-bottom: var(--hph-space-6);">
                <span style="display: inline-block; padding: var(--hph-space-2) var(--hph-space-4); background: rgba(255, 255, 255, 0.15); color: currentColor; border-radius: var(--hph-radius-full); font-size: var(--hph-text-sm); font-weight: var(--hph-font-semibold); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2);">
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
            <div style="font-size: var(--hph-text-base); line-height: var(--hph-leading-relaxed); max-width: 65ch; margin-left: auto; margin-right: auto; opacity: 0.85;">
                <?php echo wp_kses_post($content); ?>
            </div>
            <?php endif; ?>
            
        </div>
        <?php endif; ?>
        
        <?php if (!empty($stats)): ?>
        <!-- Stats Grid -->
        <div 
            id="<?php echo esc_attr($stats_section_id); ?>" 
            style="<?php echo implode('; ', $grid_styles); ?>" 
            data-animate="<?php echo $animate_counters ? 'true' : 'false'; ?>"
        >
            
            <?php foreach ($stats as $index => $stat): 
                $stat_defaults = array(
                    'number' => '0',
                    'label' => '',
                    'icon' => '',
                    'description' => '',
                    'prefix' => '',
                    'suffix' => '',
                    'color' => '',
                    'progress' => 0, // 0-100 for progress bar
                    'chart_type' => '', // 'progress', 'circle', 'bar'
                    'trend' => '', // 'up', 'down', 'stable'
                    'trend_value' => ''
                );
                $stat = wp_parse_args($stat, $stat_defaults);
                
                // Extract numeric value for animation
                $numeric_value = preg_replace('/[^0-9.]/', '', $stat['number']);
                $display_number = $stat['number'];
                
                // Build stat card with HPH framework classes
                $card_classes = array('hph-stat-item');
                $card_styles = array();
                
                // Style-specific card styling using HPH framework
                switch ($style) {
                    case 'cards':
                        $card_classes[] = 'hph-bg-white hph-shadow-md hover:hph-shadow-lg';
                        $card_classes[] = 'hph-rounded-lg hph-p-6';
                        $card_classes[] = 'hph-transition-all hph-duration-300';
                        break;
                        
                    case 'gradient-cards':
                        $card_classes[] = 'hph-bg-gradient-to-br hph-from-white/10 hph-to-white/5';
                        $card_classes[] = 'hph-backdrop-blur-sm hph-border hph-border-white/20';
                        $card_classes[] = 'hph-rounded-xl hph-p-8';
                        $card_classes[] = 'hph-shadow-xl hover:hph-shadow-2xl';
                        $card_classes[] = 'hph-transition-all hph-duration-300';
                        break;
                        
                    case 'outlined':
                        $card_classes[] = 'hph-bg-transparent hph-border-2 hph-border-current';
                        $card_classes[] = 'hph-rounded-xl hph-p-6';
                        $card_classes[] = 'hph-transition-all hph-duration-300 hover:hph-bg-white/5';
                        break;
                        
                    case 'minimal':
                        $card_classes[] = 'hph-p-4';
                        break;
                        
                    case 'counters':
                    default:
                        $card_classes[] = 'hph-p-6';
                        break;
                }
                
                // Add common classes
                $card_classes[] = 'hph-text-center hph-relative hph-flex hph-flex-col hph-items-center';
                
                // Ensure proper height for content and z-index
                $card_styles[] = 'min-height: 280px';
                $card_styles[] = 'display: flex';
                $card_styles[] = 'flex-direction: column';
                $card_styles[] = 'justify-content: space-between';
                $card_styles[] = 'position: relative';
                $card_styles[] = 'z-index: 2';
                
                // Add hover effects
                $hover_attributes = '';
                if ($hover_effects) {
                    switch ($style) {
                        case 'cards':
                        case 'gradient-cards':
                            $hover_attributes = 'onmouseover="this.style.transform=\'translateY(-8px) scale(1.02)\'; this.style.boxShadow=\'0 25px 50px rgba(0, 0, 0, 0.2)\';" onmouseout="this.style.transform=\'translateY(0) scale(1)\'; this.style.boxShadow=\'' . ($style === 'cards' ? '0 8px 32px rgba(0, 0, 0, 0.1)' : '0 20px 40px rgba(0, 0, 0, 0.15)') . '\';"';
                            break;
                        case 'outlined':
                            $hover_attributes = 'onmouseover="this.style.transform=\'translateY(-4px)\'; this.style.opacity=\'1\'; this.style.boxShadow=\'0 10px 25px rgba(255, 255, 255, 0.1)\';" onmouseout="this.style.transform=\'translateY(0)\'; this.style.opacity=\'0.8\'; this.style.boxShadow=\'none\';"';
                            break;
                        case 'minimal':
                        case 'counters':
                        default:
                            $hover_attributes = 'onmouseover="this.style.transform=\'translateY(-4px)\';" onmouseout="this.style.transform=\'translateY(0)\';"';
                            break;
                    }
                }
            ?>
            
            <div 
                class="<?php echo implode(' ', $card_classes); ?>" 
                style="<?php echo implode('; ', $card_styles); ?>"
                <?php echo $hover_attributes; ?>
            >
                
                <?php if ($stat['icon']): ?>
                <!-- Icon -->
                <div class="hph-flex-shrink-0 hph-mb-4">
                    <div class="hph-w-16 hph-h-16 hph-mx-auto hph-bg-white/10 hph-rounded-full hph-flex hph-items-center hph-justify-center hph-mb-3">
                        <i class="<?php echo esc_attr($stat['icon']); ?> hph-text-2xl" style="<?php echo $stat['color'] ? 'color: ' . esc_attr($stat['color']) . ';' : ''; ?>"></i>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Number with Trend -->
                <div class="hph-flex-1 hph-mb-4">
                    <div class="hph-flex hph-items-center hph-justify-center hph-gap-2 hph-flex-wrap">
                        <span 
                            class="hph-stat-number hph-text-5xl hph-font-bold hph-leading-none hph-text-gradient" 
                            style="background: linear-gradient(135deg, currentColor, rgba(255,255,255,0.8)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"
                            data-target="<?php echo esc_attr($numeric_value); ?>"
                            data-display="<?php echo esc_attr($display_number); ?>"
                            data-prefix="<?php echo esc_attr($stat['prefix']); ?>"
                            data-suffix="<?php echo esc_attr($stat['suffix']); ?>"
                        >
                            <?php echo $animate_counters ? '0' : esc_html($display_number); ?>
                        </span>
                        
                        <?php if ($stat['trend']): ?>
                        <!-- Trend Indicator -->
                        <div class="hph-flex hph-items-center hph-gap-1 hph-text-sm hph-opacity-80">
                            <?php 
                            $trend_color = '';
                            $trend_icon = '';
                            switch($stat['trend']) {
                                case 'up':
                                    $trend_color = 'hph-text-green-500';
                                    $trend_icon = 'fas fa-arrow-up';
                                    break;
                                case 'down':
                                    $trend_color = 'hph-text-red-500';
                                    $trend_icon = 'fas fa-arrow-down';
                                    break;
                                case 'stable':
                                    $trend_color = 'hph-text-gray-500';
                                    $trend_icon = 'fas fa-minus';
                                    break;
                            }
                            ?>
                            <i class="<?php echo esc_attr($trend_icon); ?> <?php echo esc_attr($trend_color); ?> hph-text-xs"></i>
                            <?php if ($stat['trend_value']): ?>
                            <span class="<?php echo esc_attr($trend_color); ?> hph-font-medium">
                                <?php echo esc_html($stat['trend_value']); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($stat['chart_type'] === 'progress' && $stat['progress']): ?>
                    <!-- Progress Bar -->
                    <div class="hph-mt-3 hph-w-full">
                        <div class="hph-w-full hph-h-2 hph-bg-white/20 hph-rounded-full hph-overflow-hidden">
                            <div 
                                class="hph-progress-bar hph-h-full hph-rounded-full hph-transition-all hph-duration-1000 hph-ease-out"
                                style="background: linear-gradient(90deg, <?php echo $stat['color'] ?: 'var(--hph-primary)'; ?>, rgba(255,255,255,0.8)); width: 0%;"
                                data-progress="<?php echo esc_attr($stat['progress']); ?>"
                            ></div>
                        </div>
                        <div class="hph-text-right hph-mt-1 hph-text-xs hph-opacity-70">
                            <?php echo esc_html($stat['progress']); ?>%
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($stat['chart_type'] === 'circle' && $stat['progress']): ?>
                    <!-- Circular Progress -->
                    <div class="hph-mt-3 hph-flex hph-justify-center">
                        <div class="hph-relative hph-w-20 hph-h-20">
                            <svg class="hph-w-full hph-h-full hph-transform hph-rotate-90" style="transform: rotate(-90deg);" viewBox="0 0 36 36">
                                <path 
                                    d="m18,2.0845 a 15.9155,15.9155 0 0,1 0,31.831 a 15.9155,15.9155 0 0,1 0,-31.831"
                                    fill="none" 
                                    stroke="rgba(255,255,255,0.2)" 
                                    stroke-width="2"
                                />
                                <path 
                                    class="hph-circle-progress hph-transition-all hph-duration-1000 hph-ease-out"
                                    d="m18,2.0845 a 15.9155,15.9155 0 0,1 0,31.831 a 15.9155,15.9155 0 0,1 0,-31.831"
                                    fill="none" 
                                    stroke="<?php echo $stat['color'] ?: 'var(--hph-primary)'; ?>" 
                                    stroke-width="2"
                                    stroke-dasharray="0, 100"
                                    data-progress="<?php echo esc_attr($stat['progress']); ?>"
                                />
                            </svg>
                            <div class="hph-absolute hph-top-1/2 hph-left-1/2 hph-transform hph--translate-x-1/2 hph--translate-y-1/2 hph-text-xs hph-font-semibold hph-opacity-80">
                                <?php echo esc_html($stat['progress']); ?>%
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="hph-flex-shrink-0 hph-mt-auto">
                    <?php if ($stat['label']): ?>
                    <!-- Label -->
                    <h3 class="hph-text-lg hph-font-semibold hph-uppercase hph-tracking-wide hph-opacity-90 hph-mb-2">
                        <?php echo esc_html($stat['label']); ?>
                    </h3>
                    <?php endif; ?>
                    
                    <?php if ($stat['description']): ?>
                    <!-- Description -->
                    <p class="hph-text-sm hph-leading-relaxed hph-opacity-70 hph-italic hph-m-0">
                        <?php echo esc_html($stat['description']); ?>
                    </p>
                    <?php endif; ?>
                </div>
                
            </div>
            
            <?php endforeach; ?>
            
        </div>
        <?php endif; ?>
        
    </div>
    
</section>

<?php if ($animate_counters && !empty($stats)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const statsSection = document.getElementById('<?php echo esc_js($stats_section_id); ?>');
    if (!statsSection) return;
    
    // Intersection Observer to trigger animation when section is visible
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.dataset.animated) {
                entry.target.dataset.animated = 'true';
                animateStats(entry.target);
            }
        });
    }, {
        threshold: 0.2,
        rootMargin: '-50px'
    });
    
    observer.observe(statsSection);
    
    function animateStats(container) {
        const numbers = container.querySelectorAll('.hph-stat-number');
        const progressBars = container.querySelectorAll('.hph-progress-bar');
        const circleProgress = container.querySelectorAll('.hph-circle-progress');
        const duration = <?php echo intval($counter_duration); ?>;
        
        // Animate each counter with a slight delay for staggered effect
        numbers.forEach((numberEl, index) => {
            setTimeout(() => {
                const target = parseFloat(numberEl.dataset.target) || 0;
                const display = numberEl.dataset.display;
                const prefix = numberEl.dataset.prefix || '';
                const suffix = numberEl.dataset.suffix || '';
                
                // If the display value contains non-numeric characters, use it as-is
                if (display && display !== target.toString()) {
                    animateCustomNumber(numberEl, display, duration);
                } else {
                    animateNumber(numberEl, target, duration, prefix, suffix);
                }
            }, index * 150); // Stagger animations by 150ms
        });
        
        // Animate progress bars
        progressBars.forEach((bar, index) => {
            setTimeout(() => {
                const progress = bar.dataset.progress || 0;
                bar.style.width = progress + '%';
            }, index * 150 + 300); // Start after counters
        });
        
        // Animate circular progress
        circleProgress.forEach((circle, index) => {
            setTimeout(() => {
                const progress = circle.dataset.progress || 0;
                circle.style.strokeDasharray = progress + ', 100';
            }, index * 150 + 300); // Start after counters
        });
    }
    
    function animateNumber(element, target, duration, prefix, suffix) {
        const start = 0;
        const startTime = performance.now();
        const isDecimal = target % 1 !== 0;
        
        function updateNumber(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Use easing function for smoother animation
            const easedProgress = easeOutExpo(progress);
            const current = start + (target - start) * easedProgress;
            
            // Format the number
            let displayValue;
            if (isDecimal) {
                displayValue = current.toFixed(1);
            } else {
                displayValue = Math.floor(current).toLocaleString();
            }
            
            element.textContent = prefix + displayValue + suffix;
            
            if (progress < 1) {
                requestAnimationFrame(updateNumber);
            } else {
                // Ensure final value is exact
                element.textContent = prefix + (isDecimal ? target.toFixed(1) : target.toLocaleString()) + suffix;
            }
        }
        
        requestAnimationFrame(updateNumber);
    }
    
    function animateCustomNumber(element, display, duration) {
        // For custom displays like "$2.1M", animate the numeric part
        const numericMatch = display.match(/[\d,]+\.?\d*/);
        if (!numericMatch) {
            element.textContent = display;
            return;
        }
        
        const numericPart = numericMatch[0].replace(/,/g, '');
        const target = parseFloat(numericPart);
        const prefix = display.substring(0, numericMatch.index);
        const suffix = display.substring(numericMatch.index + numericMatch[0].length);
        
        animateNumber(element, target, duration, prefix, suffix);
    }
    
    // Improved easing function for more dramatic effect
    function easeOutExpo(t) {
        return t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
    }
});
</script>
<?php endif; ?>

<style>
/* Modern stats section styling */
.hph-stats-section {
    overflow: hidden;
}

/* HPH Framework Responsive Adjustments */
@media (max-width: 1024px) {
    .hph-stats-section [style*="grid-template-columns"] {
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)) !important;
    }
}

@media (max-width: 768px) {
    .hph-stats-section [style*="grid-template-columns"] {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)) !important;
    }
    
    .hph-stats-section .hph-stat-item {
        min-height: 240px !important;
    }
    
    .hph-stats-section .hph-text-5xl {
        font-size: var(--hph-text-4xl) !important;
    }
}

@media (max-width: 640px) {
    .hph-stats-section [style*="grid-template-columns"] {
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)) !important;
    }
    
    .hph-stats-section .hph-stat-item {
        min-height: 200px !important;
    }
    
    .hph-stats-section .hph-text-5xl {
        font-size: var(--hph-text-3xl) !important;
    }
    
    .hph-stats-section .hph-w-16 {
        width: 3rem !important;
        height: 3rem !important;
    }
}

/* Enhanced loading state */
.hph-stat-number {
    display: inline-block;
    min-width: 2ch;
    font-variant-numeric: tabular-nums;
}

/* Glassmorphism effects for better visual appeal */
.hph-stats-cards .hph-stat-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border-radius: inherit;
    background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
    pointer-events: none;
}

/* Icon container enhancements */
.hph-stat-item [style*="width: 4rem"] {
    transition: all 300ms ease;
}

.hph-stat-item:hover [style*="width: 4rem"] {
    transform: scale(1.1);
    background: rgba(255, 255, 255, 0.2) !important;
}

/* Text gradient fallback for non-webkit browsers */
@supports not (-webkit-background-clip: text) {
    .hph-stat-number {
        background: none !important;
        -webkit-background-clip: unset !important;
        -webkit-text-fill-color: unset !important;
        background-clip: unset !important;
    }
}

/* Animation performance optimizations */
.hph-stats-section * {
    will-change: transform;
}

.hph-stat-item {
    contain: layout style paint;
}
</style>
