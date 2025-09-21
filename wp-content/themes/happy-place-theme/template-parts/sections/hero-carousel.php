<?php
/**
 * HPH Hero Carousel Section Template
 * 
 * Hero carousel that rotates entire hero sections with all content
 * Perfect for multiple properties, service areas, or marketing messages
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Register this template part for asset loading
if (function_exists('hph_register_template_part')) {
    hph_register_template_part('sections/hero-carousel');
}

// Default arguments
$defaults = array(
    'slides' => array(), // Array of slide configurations (each slide has hero.php options)
    'autoplay' => true, // Boolean: true/false - automatically advance slides
    'autoplay_speed' => 5000, // Milliseconds between slides (1000-10000)
    'transition_speed' => 800, // Milliseconds for transition animation (300-1500)
    'transition_type' => 'slide', // Options: 'slide', 'fade', 'zoom'
    'show_navigation' => true, // Boolean: true/false - show prev/next arrows
    'show_pagination' => true, // Boolean: true/false - show dot indicators
    'show_progress' => false, // Boolean: true/false - show progress bar
    'pause_on_hover' => true, // Boolean: true/false - pause autoplay on hover
    'infinite_loop' => true, // Boolean: true/false - loop back to first slide
    'height' => 'lg', // Options: 'sm', 'md', 'lg', 'xl', 'full'
    'section_id' => '', // HTML ID for the section
    'fade_in' => false // Boolean: true/false - enable entrance animations
);

// Merge with provided args
$config = wp_parse_args($args ?? array(), $defaults);
extract($config);

// Ensure we have slides
if (empty($slides)) {
    // Default demo slides
    $slides = array(
        array(
            'style' => 'image',
            'background_image' => hph_get_image_url_only('assets/images/hero-bg.jpg'),
            'overlay' => 'dark',
            'headline' => 'Find Your Dream Home',
            'subheadline' => 'Discover amazing properties in the most desirable locations',
            'buttons' => array(
                array('text' => 'View Listings', 'url' => '/listings/', 'style' => 'white', 'icon' => 'fas fa-home'),
                array('text' => 'Learn More', 'url' => '/about/', 'style' => 'outline-white')
            )
        ),
        array(
            'style' => 'gradient',
            'theme' => 'primary',
            'overlay' => 'none',
            'headline' => 'Expert Real Estate Services',
            'subheadline' => 'Professional guidance for buying, selling, and investing',
            'buttons' => array(
                array('text' => 'Our Services', 'url' => '/services/', 'style' => 'white', 'icon' => 'fas fa-star'),
                array('text' => 'Contact Us', 'url' => '/contact/', 'style' => 'outline-white')
            )
        )
    );
}

// Height styles using CSS variables
$height_style = '';
switch ($height) {
    case 'sm':
        $height_style = 'min-height: 50vh';
        break;
    case 'md':
        $height_style = 'min-height: 60vh';
        break;
    case 'lg':
        $height_style = 'min-height: 75vh';
        break;
    case 'xl':
        $height_style = 'min-height: 85vh';
        break;
    case 'full':
        $height_style = 'min-height: 100vh';
        break;
}

// Ensure Font Awesome is loaded for icons
if (!wp_script_is('font-awesome', 'enqueued')) {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', array(), '6.5.1');
}
?>

<section 
    class="hph-hero-carousel <?php echo $fade_in ? 'hph-animate-fade-in' : ''; ?>"
    <?php if ($section_id): ?>id="<?php echo esc_attr($section_id); ?>"<?php endif; ?>
    style="position: relative; <?php echo $height_style; ?>; overflow: hidden;"
    data-autoplay="<?php echo $autoplay ? 'true' : 'false'; ?>"
    data-autoplay-speed="<?php echo esc_attr($autoplay_speed); ?>"
    data-transition-speed="<?php echo esc_attr($transition_speed); ?>"
    data-transition-type="<?php echo esc_attr($transition_type); ?>"
    data-pause-on-hover="<?php echo $pause_on_hover ? 'true' : 'false'; ?>"
    data-infinite-loop="<?php echo $infinite_loop ? 'true' : 'false'; ?>"
>
    
    <!-- Carousel Slides Container -->
    <div class="hph-carousel-slides" style="position: relative; width: 100%; height: 100%;">
        
        <?php foreach ($slides as $index => $slide): ?>
        <?php
        // Slide defaults
        $slide_defaults = array(
            'style' => 'gradient',
            'theme' => 'primary',
            'background_image' => '',
            'background_video' => '',
            'overlay' => 'dark',
            'overlay_opacity' => '40',
            'gradient_overlay' => '',
            'alignment' => 'center',
            'content_width' => 'normal',
            'badge' => '',
            'badge_icon' => '',
            'headline' => '',
            'subheadline' => '',
            'content' => '',
            'buttons' => array(),
            'parallax' => false
        );
        
        $slide = wp_parse_args($slide, $slide_defaults);
        
        // Build slide styles
        $slide_styles = array(
            'position: absolute',
            'top: 0',
            'left: 0',
            'width: 100%',
            'height: 100%',
            'display: flex',
            'align-items: center',
            'justify-content: center',
            'overflow: hidden'
        );
        
        // Set visibility for first slide
        if ($index === 0) {
            $slide_styles[] = 'opacity: 1';
            $slide_styles[] = 'z-index: 2';
        } else {
            $slide_styles[] = 'opacity: 0';
            $slide_styles[] = 'z-index: 1';
        }
        
        // Background styles
        if ($slide['background_image']) {
            $slide_styles[] = "background-image: url('" . esc_url($slide['background_image']) . "')";
            $slide_styles[] = "background-size: cover";
            $slide_styles[] = "background-position: center";
            $slide_styles[] = "background-repeat: no-repeat";
            if ($slide['parallax']) {
                $slide_styles[] = "background-attachment: fixed";
            }
        } elseif ($slide['style'] === 'gradient') {
            // Apply theme gradient
            switch($slide['theme']) {
                case 'primary':
                    $slide_styles[] = "background: var(--hph-gradient-primary)";
                    break;
                case 'secondary':
                    $slide_styles[] = "background: var(--hph-gradient-secondary)";
                    break;
                case 'ocean':
                    $slide_styles[] = "background: var(--hph-gradient-ocean)";
                    break;
                case 'sunset':
                    $slide_styles[] = "background: var(--hph-gradient-sunset)";
                    break;
                case 'forest':
                    $slide_styles[] = "background: var(--hph-gradient-forest)";
                    break;
                default:
                    $slide_styles[] = "background: var(--hph-gradient-primary)";
            }
        }
        
        // Theme text color
        $text_color = 'var(--hph-white)';
        if ($slide['theme'] === 'light') {
            $text_color = 'var(--hph-gray-900)';
        }
        $slide_styles[] = "color: $text_color";
        
        // Build overlay styles
        $overlay_styles = array();
        if ($slide['overlay'] !== 'none') {
            $overlay_styles[] = 'position: absolute';
            $overlay_styles[] = 'top: 0';
            $overlay_styles[] = 'left: 0';
            $overlay_styles[] = 'right: 0';
            $overlay_styles[] = 'bottom: 0';
            $overlay_styles[] = 'z-index: 1';
            $overlay_styles[] = 'pointer-events: none';
            
            switch ($slide['overlay']) {
                case 'dark':
                    $overlay_styles[] = 'background: var(--hph-gradient-overlay-dark)';
                    break;
                case 'light':
                    $overlay_styles[] = 'background: var(--hph-gradient-overlay-light)';
                    break;
                case 'gradient':
                    $overlay_styles[] = 'background: var(--hph-gradient-primary-overlay)';
                    break;
                default:
                    $overlay_styles[] = 'background: var(--hph-gradient-overlay-dark)';
            }
        }
        
        // Content alignment
        $content_justify = '';
        $text_align_style = '';
        switch ($slide['alignment']) {
            case 'left':
                $text_align_style = 'text-align: left;';
                $content_justify = 'align-items: flex-start;';
                break;
            case 'right':
                $text_align_style = 'text-align: right;';
                $content_justify = 'align-items: flex-end;';
                break;
            case 'center':
            default:
                $text_align_style = 'text-align: center;';
                $content_justify = 'align-items: center;';
                break;
        }
        
        // Content width
        $container_max_width = '';
        switch ($slide['content_width']) {
            case 'narrow':
                $container_max_width = 'max-width: var(--hph-container-sm);';
                break;
            case 'wide':
                $container_max_width = 'max-width: var(--hph-container-2xl);';
                break;
            case 'full':
                $container_max_width = 'max-width: 100%; padding-left: 0; padding-right: 0;';
                break;
            case 'normal':
            default:
                $container_max_width = 'max-width: var(--hph-container-xl);';
                break;
        }
        ?>
        
        <div class="hph-carousel-slide" 
             data-slide="<?php echo $index; ?>"
             style="<?php echo implode('; ', $slide_styles); ?>"
             data-bg="<?php echo esc_attr($slide['style'] === 'gradient' ? 'gradient' : 'dark'); ?>">
            
            <?php if ($slide['background_video']): ?>
            <!-- Video Background -->
            <video 
                class="hph-slide-video"
                style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; width: 100%; height: 100%; object-fit: cover; z-index: 0;"
                autoplay 
                muted 
                loop 
                playsinline
                <?php if ($slide['background_image']): ?>poster="<?php echo esc_url($slide['background_image']); ?>"<?php endif; ?>
            >
                <source src="<?php echo esc_url($slide['background_video']); ?>" type="video/mp4">
                <?php if ($slide['background_image']): ?>
                <img src="<?php echo esc_url($slide['background_image']); ?>" alt="Hero background" style="width: 100%; height: 100%; object-fit: cover;">
                <?php endif; ?>
            </video>
            <?php endif; ?>
            
            <?php if (!empty($overlay_styles)): ?>
            <!-- Overlay -->
            <div class="hph-slide-overlay" style="<?php echo implode('; ', $overlay_styles); ?>"></div>
            <?php endif; ?>
            
            <!-- Content Container -->
            <div class="hph-slide-container" style="position: relative; z-index: 2; width: 100%; padding: var(--hph-space-8) var(--hph-space-6);">
                <div class="hph-slide-inner" style="<?php echo $container_max_width; ?> margin-left: auto; margin-right: auto;">
                    <div class="hph-slide-content" style="display: flex; flex-direction: column; <?php echo $content_justify; ?> gap: var(--hph-gap-lg); <?php echo $text_align_style; ?>">
                        
                        <?php if ($slide['badge']): ?>
                        <!-- Badge -->
                        <div style="margin-bottom: var(--hph-space-2);">
                            <span style="display: inline-flex; align-items: center; gap: var(--hph-gap-sm); padding: var(--hph-space-2) var(--hph-space-4); background: rgba(255, 255, 255, 0.2); color: currentColor; backdrop-filter: blur(10px); border-radius: var(--hph-radius-full); font-size: var(--hph-text-sm); font-weight: var(--hph-font-semibold);">
                                <?php if ($slide['badge_icon']): ?>
                                <i class="<?php echo esc_attr($slide['badge_icon']); ?>"></i>
                                <?php endif; ?>
                                <?php echo esc_html($slide['badge']); ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($slide['headline']): ?>
                        <!-- Headline -->
                        <h1 class="hph-slide-headline" 
                            style="margin: 0 0 var(--hph-space-4) 0; font-size: var(--hph-text-5xl); font-weight: var(--hph-font-bold); line-height: var(--hph-leading-tight);">
                            <?php echo esc_html($slide['headline']); ?>
                        </h1>
                        <?php endif; ?>
                        
                        <?php if ($slide['subheadline']): ?>
                        <!-- Subheadline -->
                        <h2 class="hph-slide-subheadline" 
                            style="margin: 0 0 var(--hph-space-4) 0; font-size: var(--hph-text-xl); font-weight: var(--hph-font-medium); line-height: var(--hph-leading-snug); opacity: 0.9;">
                            <?php echo esc_html($slide['subheadline']); ?>
                        </h2>
                        <?php endif; ?>
                        
                        <?php if ($slide['content']): ?>
                        <!-- Content -->
                        <div class="hph-slide-content-text" 
                             style="margin: 0 0 var(--hph-space-8) 0; font-size: var(--hph-text-lg); line-height: var(--hph-leading-normal); opacity: 0.85;">
                            <?php echo wp_kses_post($slide['content']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($slide['buttons'])): ?>
                        <!-- Buttons -->
                        <div class="hph-slide-buttons" 
                             style="display: flex; flex-wrap: wrap; gap: var(--hph-gap-lg); align-items: center; <?php echo $slide['alignment'] === 'center' ? 'justify-content: center;' : ($slide['alignment'] === 'right' ? 'justify-content: flex-end;' : 'justify-content: flex-start;'); ?>">
                            <?php foreach ($slide['buttons'] as $btn_index => $button): 
                                $btn_defaults = array(
                                    'text' => 'Button',
                                    'url' => '#',
                                    'style' => 'white',
                                    'size' => 'xl',
                                    'icon' => '',
                                    'icon_position' => 'left',
                                    'target' => '_self'
                                );
                                $btn = wp_parse_args($button, $btn_defaults);
                                
                                // Button styles
                                $btn_styles = array(
                                    'display: inline-flex',
                                    'align-items: center',
                                    'justify-content: center',
                                    'text-decoration: none',
                                    'font-weight: var(--hph-font-semibold)',
                                    'border-radius: var(--hph-radius-lg)',
                                    'transition: all 300ms ease',
                                    'box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)'
                                );
                                
                                // Size-based padding
                                switch($btn['size']) {
                                    case 's':
                                        $btn_styles[] = 'padding: var(--hph-space-2) var(--hph-space-4)';
                                        $btn_styles[] = 'font-size: var(--hph-text-sm)';
                                        break;
                                    case 'm':
                                        $btn_styles[] = 'padding: var(--hph-space-4) var(--hph-space-6)';
                                        $btn_styles[] = 'font-size: var(--hph-text-base)';
                                        break;
                                    case 'l':
                                        $btn_styles[] = 'padding: var(--hph-space-4) var(--hph-space-8)';
                                        $btn_styles[] = 'font-size: var(--hph-text-base)';
                                        break;
                                    case 'xl':
                                        $btn_styles[] = 'padding: var(--hph-space-6) var(--hph-space-12)';
                                        $btn_styles[] = 'font-size: var(--hph-text-lg)';
                                        break;
                                }
                                
                                // Style-based colors
                                switch($btn['style']) {
                                    case 'white':
                                        $btn_styles[] = 'background-color: var(--hph-white)';
                                        $btn_styles[] = 'color: var(--hph-primary)';
                                        $btn_styles[] = 'border: 2px solid var(--hph-white)';
                                        break;
                                    case 'outline-white':
                                        $btn_styles[] = 'background-color: transparent';
                                        $btn_styles[] = 'color: var(--hph-white)';
                                        $btn_styles[] = 'border: 2px solid var(--hph-white)';
                                        break;
                                    case 'primary':
                                        $btn_styles[] = 'background-color: var(--hph-primary)';
                                        $btn_styles[] = 'color: var(--hph-white)';
                                        $btn_styles[] = 'border: 2px solid var(--hph-primary)';
                                        break;
                                    case 'outline-primary':
                                        $btn_styles[] = 'background-color: transparent';
                                        $btn_styles[] = 'color: var(--hph-primary)';
                                        $btn_styles[] = 'border: 2px solid var(--hph-primary)';
                                        break;
                                    default:
                                        $btn_styles[] = 'background-color: var(--hph-primary)';
                                        $btn_styles[] = 'color: var(--hph-white)';
                                        $btn_styles[] = 'border: 2px solid var(--hph-primary)';
                                }
                                
                                $btn_style_attr = 'style="' . implode('; ', $btn_styles) . '"';
                            ?>
                            <a 
                                href="<?php echo esc_url($btn['url']); ?>"
                                class="hph-slide-btn"
                                <?php echo $btn_style_attr; ?>
                                <?php if ($btn['target'] !== '_self'): ?>target="<?php echo esc_attr($btn['target']); ?>"<?php endif; ?>
                                onmouseover="this.style.transform='translateY(-2px)'"
                                onmouseout="this.style.transform='translateY(0)'"
                            >
                                <?php if ($btn['icon'] && $btn['icon_position'] === 'left'): ?>
                                <i class="<?php echo esc_attr($btn['icon']); ?>" style="margin-right: var(--hph-space-2);"></i>
                                <?php endif; ?>
                                <span><?php echo esc_html($btn['text']); ?></span>
                                <?php if ($btn['icon'] && $btn['icon_position'] === 'right'): ?>
                                <i class="<?php echo esc_attr($btn['icon']); ?>" style="margin-left: var(--hph-space-2);"></i>
                                <?php endif; ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                    </div>
                </div>
            </div>
            
        </div>
        
        <?php endforeach; ?>
        
    </div>
    
    <?php if ($show_navigation && count($slides) > 1): ?>
    <!-- Navigation Arrows -->
    <button class="hph-carousel-prev" 
            style="position: absolute; top: 50%; left: var(--hph-space-6); transform: translateY(-50%); z-index: 10; background: rgba(255, 255, 255, 0.2); color: var(--hph-white); border: none; border-radius: 50%; width: 3rem; height: 3rem; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 300ms ease; backdrop-filter: blur(10px);"
            onmouseover="this.style.background='rgba(255, 255, 255, 0.3)'"
            onmouseout="this.style.background='rgba(255, 255, 255, 0.2)'"
            aria-label="Previous slide">
        <i class="fas fa-chevron-left" style="font-size: 1.25rem;"></i>
    </button>
    
    <button class="hph-carousel-next" 
            style="position: absolute; top: 50%; right: var(--hph-space-6); transform: translateY(-50%); z-index: 10; background: rgba(255, 255, 255, 0.2); color: var(--hph-white); border: none; border-radius: 50%; width: 3rem; height: 3rem; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 300ms ease; backdrop-filter: blur(10px);"
            onmouseover="this.style.background='rgba(255, 255, 255, 0.3)'"
            onmouseout="this.style.background='rgba(255, 255, 255, 0.2)'"
            aria-label="Next slide">
        <i class="fas fa-chevron-right" style="font-size: 1.25rem;"></i>
    </button>
    <?php endif; ?>
    
    <?php if ($show_pagination && count($slides) > 1): ?>
    <!-- Pagination Dots -->
    <div class="hph-carousel-pagination" 
         style="position: absolute; bottom: var(--hph-space-6); left: 50%; transform: translateX(-50%); z-index: 10; display: flex; gap: var(--hph-gap-sm);">
        <?php for ($i = 0; $i < count($slides); $i++): ?>
        <button class="hph-carousel-dot <?php echo $i === 0 ? 'active' : ''; ?>" 
                data-slide="<?php echo $i; ?>"
                style="width: 0.75rem; height: 0.75rem; border-radius: 50%; border: none; background: <?php echo $i === 0 ? 'var(--hph-white)' : 'rgba(255, 255, 255, 0.5)'; ?>; cursor: pointer; transition: all 300ms ease;"
                aria-label="Go to slide <?php echo $i + 1; ?>"></button>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    
    <?php if ($show_progress && count($slides) > 1): ?>
    <!-- Progress Bar -->
    <div class="hph-carousel-progress" 
         style="position: absolute; bottom: 0; left: 0; width: 100%; height: 0.25rem; background: rgba(255, 255, 255, 0.2); z-index: 10;">
        <div class="hph-progress-bar" 
             style="height: 100%; background: var(--hph-white); width: 0%; transition: width linear;"></div>
    </div>
    <?php endif; ?>
    
</section>

<style>
/* Hero Carousel Animations */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideLeft {
    from { transform: translateX(100%); }
    to { transform: translateX(0); }
}

@keyframes slideRight {
    from { transform: translateX(-100%); }
    to { transform: translateX(0); }
}

@keyframes zoomIn {
    from { transform: scale(1.1); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

.hph-hero-carousel .hph-carousel-slide {
    transition: opacity <?php echo $transition_speed; ?>ms ease-in-out, transform <?php echo $transition_speed; ?>ms ease-in-out;
}

.hph-hero-carousel .hph-carousel-slide.slide-left {
    animation: slideLeft <?php echo $transition_speed; ?>ms ease-in-out;
}

.hph-hero-carousel .hph-carousel-slide.slide-right {
    animation: slideRight <?php echo $transition_speed; ?>ms ease-in-out;
}

.hph-hero-carousel .hph-carousel-slide.fade-in {
    animation: fadeIn <?php echo $transition_speed; ?>ms ease-in-out;
}

.hph-hero-carousel .hph-carousel-slide.zoom-in {
    animation: zoomIn <?php echo $transition_speed; ?>ms ease-in-out;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .hph-carousel-prev,
    .hph-carousel-next {
        width: 2.5rem !important;
        height: 2.5rem !important;
    }
    
    .hph-carousel-prev i,
    .hph-carousel-next i {
        font-size: 1rem !important;
    }
    
    .hph-slide-buttons {
        flex-direction: column !important;
        width: 100% !important;
    }
    
    .hph-slide-btn {
        width: 100% !important;
        justify-content: center !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.querySelector('.hph-hero-carousel');
    if (!carousel) return;
    
    const slides = carousel.querySelectorAll('.hph-carousel-slide');
    const prevBtn = carousel.querySelector('.hph-carousel-prev');
    const nextBtn = carousel.querySelector('.hph-carousel-next');
    const dots = carousel.querySelectorAll('.hph-carousel-dot');
    const progressBar = carousel.querySelector('.hph-progress-bar');
    
    const autoplay = carousel.dataset.autoplay === 'true';
    const autoplaySpeed = parseInt(carousel.dataset.autoplaySpeed) || 5000;
    const transitionSpeed = parseInt(carousel.dataset.transitionSpeed) || 800;
    const transitionType = carousel.dataset.transitionType || 'slide';
    const pauseOnHover = carousel.dataset.pauseOnHover === 'true';
    const infiniteLoop = carousel.dataset.infiniteLoop === 'true';
    
    let currentSlide = 0;
    let autoplayTimer = null;
    let progressTimer = null;
    let isTransitioning = false;
    
    function showSlide(index, direction = 'next') {
        if (isTransitioning || slides.length <= 1) return;
        isTransitioning = true;
        
        const currentSlideEl = slides[currentSlide];
        const nextSlideEl = slides[index];
        
        // Remove animation classes
        slides.forEach(slide => {
            slide.classList.remove('slide-left', 'slide-right', 'fade-in', 'zoom-in');
        });
        
        // Apply transition animation
        switch(transitionType) {
            case 'slide':
                if (direction === 'next') {
                    nextSlideEl.classList.add('slide-left');
                } else {
                    nextSlideEl.classList.add('slide-right');
                }
                break;
            case 'fade':
                nextSlideEl.classList.add('fade-in');
                break;
            case 'zoom':
                nextSlideEl.classList.add('zoom-in');
                break;
        }
        
        // Update slide visibility
        currentSlideEl.style.opacity = '0';
        currentSlideEl.style.zIndex = '1';
        
        nextSlideEl.style.opacity = '1';
        nextSlideEl.style.zIndex = '2';
        
        // Update pagination dots
        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === index);
            dot.style.background = i === index ? 'var(--hph-white)' : 'rgba(255, 255, 255, 0.5)';
        });
        
        currentSlide = index;
        
        setTimeout(() => {
            isTransitioning = false;
        }, transitionSpeed);
    }
    
    function nextSlide() {
        const next = currentSlide + 1 >= slides.length ? (infiniteLoop ? 0 : currentSlide) : currentSlide + 1;
        if (next !== currentSlide) {
            showSlide(next, 'next');
        }
    }
    
    function prevSlide() {
        const prev = currentSlide - 1 < 0 ? (infiniteLoop ? slides.length - 1 : currentSlide) : currentSlide - 1;
        if (prev !== currentSlide) {
            showSlide(prev, 'prev');
        }
    }
    
    function startAutoplay() {
        if (!autoplay || slides.length <= 1) return;
        
        stopAutoplay();
        
        autoplayTimer = setInterval(nextSlide, autoplaySpeed);
        
        if (progressBar) {
            progressBar.style.transition = 'none';
            progressBar.style.width = '0%';
            
            setTimeout(() => {
                progressBar.style.transition = `width ${autoplaySpeed}ms linear`;
                progressBar.style.width = '100%';
            }, 50);
        }
    }
    
    function stopAutoplay() {
        if (autoplayTimer) {
            clearInterval(autoplayTimer);
            autoplayTimer = null;
        }
        
        if (progressBar) {
            progressBar.style.transition = 'none';
            progressBar.style.width = '0%';
        }
    }
    
    function resetAutoplay() {
        if (autoplay) {
            stopAutoplay();
            setTimeout(startAutoplay, 100);
        }
    }
    
    // Event listeners
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            nextSlide();
            resetAutoplay();
        });
    }
    
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            prevSlide();
            resetAutoplay();
        });
    }
    
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            if (index !== currentSlide) {
                const direction = index > currentSlide ? 'next' : 'prev';
                showSlide(index, direction);
                resetAutoplay();
            }
        });
    });
    
    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (carousel.matches(':hover') || document.activeElement.closest('.hph-hero-carousel')) {
            if (e.key === 'ArrowLeft') {
                prevSlide();
                resetAutoplay();
            } else if (e.key === 'ArrowRight') {
                nextSlide();
                resetAutoplay();
            }
        }
    });
    
    // Pause on hover
    if (pauseOnHover) {
        carousel.addEventListener('mouseenter', stopAutoplay);
        carousel.addEventListener('mouseleave', () => {
            if (autoplay) startAutoplay();
        });
    }
    
    // Touch/swipe support
    let startX = 0;
    let startY = 0;
    
    carousel.addEventListener('touchstart', (e) => {
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
    }, { passive: true });
    
    carousel.addEventListener('touchend', (e) => {
        if (!startX || !startY) return;
        
        const endX = e.changedTouches[0].clientX;
        const endY = e.changedTouches[0].clientY;
        
        const diffX = startX - endX;
        const diffY = startY - endY;
        
        // Only trigger if horizontal swipe is more significant than vertical
        if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
            if (diffX > 0) {
                nextSlide();
            } else {
                prevSlide();
            }
            resetAutoplay();
        }
        
        startX = 0;
        startY = 0;
    }, { passive: true });
    
    // Initialize
    if (slides.length > 1) {
        startAutoplay();
    }
    
    // Intersection Observer for performance
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                if (autoplay && !autoplayTimer) startAutoplay();
            } else {
                stopAutoplay();
            }
        });
    });
    
    observer.observe(carousel);
});
</script>
