<?php
/**
 * HPH Content Section Template - Photo Collage Variation
 * Content template with overlapping photo collage and animations
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Register this template part for asset loading
if (function_exists('hph_register_template_part')) {
    hph_register_template_part('sections/content-photo-collage');
}

// Default arguments
$defaults = array(
    'layout' => 'collage-left', // collage-left, collage-right, collage-centered
    'background' => 'white',
    'padding' => 'xl',
    'content_width' => 'normal',
    'alignment' => 'left',
    'photos' => array(), // Array of photo objects with url, alt, caption
    'collage_style' => 'organic', // organic, grid, scattered, stacked
    'badge' => '',
    'headline' => 'Our Story',
    'headline_tag' => 'h2',
    'subheadline' => '',
    'content' => '',
    'buttons' => array(),
    'animation' => true, // Animations work well with photo collages
    'hover_effects' => true,
    'section_id' => ''
);

// Merge with provided args
$config = wp_parse_args($args ?? array(), $defaults);
extract($config);

// Build section styles
$section_styles = array(
    'position: relative',
    'width: 100%',
    'overflow: hidden' // Important for collage effects
);

// Background styles
switch ($background) {
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
    case 'white':
    default:
        $section_styles[] = 'background-color: var(--hph-white)';
        $section_styles[] = 'color: var(--hph-text-color)';
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
    'padding-right: var(--hph-space-6)',
    'display: grid',
    'gap: var(--hph-gap-3xl)',
    'align-items: center',
    'grid-template-columns: 1fr' // Mobile first
);

// Content width
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

// Generate unique ID for this collage
$collage_id = 'hph-collage-' . uniqid();

// Dynamic positioning handled by JavaScript - no PHP positioning needed
?>

<section 
    class="hph-content-section hph-content-photo-collage"
    <?php if ($section_id): ?>id="<?php echo esc_attr($section_id); ?>"<?php endif; ?>
    data-bg="<?php echo esc_attr($background); ?>"
    style="<?php echo implode('; ', $section_styles); ?>"
    data-animation="<?php echo $animation ? 'true' : 'false'; ?>"
>
    <style>
        /* Simple Photo Carousel */
        .<?php echo $collage_id; ?>-container {
            position: relative;
            width: 100%;
            height: 400px;
            margin: 0 auto;
        }
        
        .<?php echo $collage_id; ?>-photo {
            position: absolute;
            width: 300px;
            height: 200px;
            border-radius: var(--hph-radius-lg);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            transition: all 800ms ease;
            cursor: pointer;
            overflow: hidden;
            opacity: 0.6;
        }
        
        .<?php echo $collage_id; ?>-photo.active {
            opacity: 1;
            z-index: 10;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }
        
        .<?php echo $collage_id; ?>-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        /* Navigation dots */
        .<?php echo $collage_id; ?>-nav {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 8px;
            z-index: 20;
        }
        
        .<?php echo $collage_id; ?>-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: background 300ms ease;
        }
        
        .<?php echo $collage_id; ?>-dot.active {
            background: rgba(255, 255, 255, 0.9);
        }
        
        /* Responsive grid layout */
        @media (min-width: 768px) {
            .hph-content-collage-grid {
                grid-template-columns: 1fr 1fr !important;
            }
        }
        
        /* Mobile adjustments */
        @media (max-width: 767px) {
            .<?php echo $collage_id; ?>-container {
                height: 400px;
            }
            
            .<?php echo $collage_id; ?>-photo {
                position: relative !important;
                width: 80% !important;
                height: 120px !important;
                margin: 10px auto !important;
                top: auto !important;
                left: auto !important;
                right: auto !important;
                transform: none !important;
            }
        }
    </style>
    
    <div 
        class="hph-content-container <?php echo in_array($layout, ['collage-left', 'collage-right']) ? 'hph-content-collage-grid' : ''; ?>"
        style="<?php echo implode('; ', $container_styles); ?>"
    >
        
        <?php if ($layout === 'collage-left' && !empty($photos)): ?>
        <!-- Left Collage Layout -->
        <div style="order: 1;">
            <div class="<?php echo $collage_id; ?>-container">
                <?php foreach ($photos as $index => $photo): 
                    if ($index >= 4) break; // Max 4 photos
                ?>
                <div 
                    class="<?php echo $collage_id; ?>-photo <?php echo $index === 0 ? 'active' : ''; ?>"
                    data-index="<?php echo $index; ?>"
                    onclick="setActivePhoto(<?php echo $index; ?>, '<?php echo $collage_id; ?>')"
                >
                    <img 
                        src="<?php echo esc_url($photo['url']); ?>" 
                        alt="<?php echo esc_attr($photo['alt'] ?? ''); ?>"
                        loading="lazy"
                    >
                </div>
                <?php endforeach; ?>
                
                <!-- Navigation Dots -->
                <div class="<?php echo $collage_id; ?>-nav">
                    <?php foreach ($photos as $index => $photo): 
                        if ($index >= 4) break;
                    ?>
                    <div 
                        class="<?php echo $collage_id; ?>-dot <?php echo $index === 0 ? 'active' : ''; ?>"
                        data-index="<?php echo $index; ?>"
                        onclick="setActivePhoto(<?php echo $index; ?>, '<?php echo $collage_id; ?>')"
                    ></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <div style="order: 2; <?php echo $animation ? 'animation: fadeInUp 0.8s ease-out 0.2s; opacity: 0; animation-fill-mode: forwards;' : ''; ?>">
        
        <?php elseif ($layout === 'collage-right' && !empty($photos)): ?>
        <!-- Right Collage Layout -->
        <div style="order: 1; <?php echo $animation ? 'animation: fadeInUp 0.8s ease-out;' : ''; ?>">
        
        <?php elseif ($layout === 'collage-centered' && !empty($photos)): ?>
        <!-- Centered Collage Layout -->
        <div style="text-align: center; margin-bottom: var(--hph-space-16);">
            <div class="<?php echo $collage_id; ?>-container" style="max-width: 800px;">
                <?php foreach ($photos as $index => $photo): 
                    if ($index >= 4) break;
                ?>
                <div 
                    class="<?php echo $collage_id; ?>-photo <?php echo $index === 0 ? 'active' : ''; ?>"
                    data-index="<?php echo $index; ?>"
                    onclick="setActivePhoto(<?php echo $index; ?>, '<?php echo $collage_id; ?>')"
                >
                    <img 
                        src="<?php echo esc_url($photo['url']); ?>" 
                        alt="<?php echo esc_attr($photo['alt'] ?? ''); ?>"
                        loading="lazy"
                    >
                </div>
                <?php endforeach; ?>
                
                <!-- Navigation Dots -->
                <div class="<?php echo $collage_id; ?>-nav">
                    <?php foreach ($photos as $index => $photo): 
                        if ($index >= 4) break;
                    ?>
                    <div 
                        class="<?php echo $collage_id; ?>-dot <?php echo $index === 0 ? 'active' : ''; ?>"
                        data-index="<?php echo $index; ?>"
                        onclick="setActivePhoto(<?php echo $index; ?>, '<?php echo $collage_id; ?>')"
                    ></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <div style="<?php echo $animation ? 'animation: fadeInUp 0.8s ease-out 0.4s; opacity: 0; animation-fill-mode: forwards;' : ''; ?>">
        
        <?php endif; ?>
        
        <!-- Content Section -->
        <?php if ($layout !== 'collage-centered'): ?>
            
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
            <<?php echo esc_attr($headline_tag); ?> style="margin: 0 0 var(--hph-space-6) 0; font-size: var(--hph-text-4xl); font-weight: var(--hph-font-bold); line-height: var(--hph-leading-tight);">
                <?php echo esc_html($headline); ?>
            </<?php echo esc_attr($headline_tag); ?>>
            <?php endif; ?>
            
            <?php if ($subheadline): ?>
            <!-- Subheadline -->
            <p style="margin: 0 0 var(--hph-space-6) 0; font-size: var(--hph-text-xl); font-weight: var(--hph-font-medium); opacity: 0.9;">
                <?php echo esc_html($subheadline); ?>
            </p>
            <?php endif; ?>
            
            <?php if ($content): ?>
            <!-- Content -->
            <div style="margin: 0 0 var(--hph-space-12) 0; font-size: var(--hph-text-base); line-height: var(--hph-leading-relaxed);">
                <?php echo wp_kses_post($content); ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($buttons)): ?>
            <!-- Buttons -->
            <div style="display: flex; flex-wrap: wrap; gap: var(--hph-gap-lg); align-items: center; <?php echo $alignment === 'center' ? 'justify-content: center;' : ($alignment === 'right' ? 'justify-content: flex-end;' : 'justify-content: flex-start;'); ?>">
                <?php foreach ($buttons as $button): 
                    $btn_defaults = array(
                        'text' => 'Button',
                        'url' => '#',
                        'style' => 'primary',
                        'size' => 'lg',
                        'icon' => '',
                        'target' => '_self'
                    );
                    $btn = wp_parse_args($button, $btn_defaults);
                    
                    $btn_styles = array(
                        'display: inline-flex',
                        'align-items: center',
                        'justify-content: center',
                        'text-decoration: none',
                        'font-weight: var(--hph-font-semibold)',
                        'border-radius: var(--hph-radius-lg)',
                        'transition: all 300ms ease',
                        'box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1)',
                        'padding: var(--hph-space-4) var(--hph-space-8)',
                        'font-size: var(--hph-text-base)'
                    );
                    
                    // Button style
                    switch($btn['style']) {
                        case 'secondary':
                            $btn_styles[] = 'background-color: var(--hph-secondary)';
                            $btn_styles[] = 'color: var(--hph-white)';
                            $btn_styles[] = 'border: 2px solid var(--hph-secondary)';
                            break;
                        case 'outline':
                            $btn_styles[] = 'background-color: transparent';
                            $btn_styles[] = 'color: var(--hph-primary)';
                            $btn_styles[] = 'border: 2px solid var(--hph-primary)';
                            break;
                        case 'primary':
                        default:
                            $btn_styles[] = 'background-color: var(--hph-primary)';
                            $btn_styles[] = 'color: var(--hph-white)';
                            $btn_styles[] = 'border: 2px solid var(--hph-primary)';
                    }
                ?>
                <a 
                    href="<?php echo esc_url($btn['url']); ?>"
                    style="<?php echo implode('; ', $btn_styles); ?>"
                    <?php if ($btn['target'] !== '_self'): ?>target="<?php echo esc_attr($btn['target']); ?>"<?php endif; ?>
                    onmouseover="this.style.transform='translateY(-2px)'"
                    onmouseout="this.style.transform='translateY(0)'"
                >
                    <?php if ($btn['icon']): ?>
                    <i class="<?php echo esc_attr($btn['icon']); ?>" style="margin-right: var(--hph-space-2);"></i>
                    <?php endif; ?>
                    <span><?php echo esc_html($btn['text']); ?></span>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
        </div>
        
        <?php if ($layout === 'collage-right' && !empty($photos)): ?>
        <!-- Right Collage -->
        <div style="order: 2;">
            <div class="<?php echo $collage_id; ?>-container">
                <?php foreach ($photos as $index => $photo): 
                    if ($index >= 4) break;
                ?>
                <div 
                    class="<?php echo $collage_id; ?>-photo <?php echo $index === 0 ? 'active' : ''; ?>"
                    data-index="<?php echo $index; ?>"
                    onclick="setActivePhoto(<?php echo $index; ?>, '<?php echo $collage_id; ?>')"
                >
                    <img 
                        src="<?php echo esc_url($photo['url']); ?>" 
                        alt="<?php echo esc_attr($photo['alt'] ?? ''); ?>"
                        loading="lazy"
                    >
                </div>
                <?php endforeach; ?>
                
                <!-- Navigation Dots -->
                <div class="<?php echo $collage_id; ?>-nav">
                    <?php foreach ($photos as $index => $photo): 
                        if ($index >= 4) break;
                    ?>
                    <div 
                        class="<?php echo $collage_id; ?>-dot <?php echo $index === 0 ? 'active' : ''; ?>"
                        data-index="<?php echo $index; ?>"
                        onclick="setActivePhoto(<?php echo $index; ?>, '<?php echo $collage_id; ?>')"
                    ></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <!-- Centered Layout Content -->
            
            <?php if ($badge): ?>
            <div style="margin-bottom: var(--hph-space-6);">
                <span style="display: inline-block; padding: var(--hph-space-2) var(--hph-space-4); background: var(--hph-primary-100); color: var(--hph-primary-700); border-radius: var(--hph-radius-full); font-size: var(--hph-text-sm); font-weight: var(--hph-font-semibold);">
                    <?php echo esc_html($badge); ?>
                </span>
            </div>
            <?php endif; ?>
            
            <?php if ($headline): ?>
            <<?php echo esc_attr($headline_tag); ?> style="margin: 0 0 var(--hph-space-6) 0; font-size: var(--hph-text-4xl); font-weight: var(--hph-font-bold); line-height: var(--hph-leading-tight);">
                <?php echo esc_html($headline); ?>
            </<?php echo esc_attr($headline_tag); ?>>
            <?php endif; ?>
            
            <?php if ($subheadline): ?>
            <p style="margin: 0 0 var(--hph-space-6) 0; font-size: var(--hph-text-xl); font-weight: var(--hph-font-medium); opacity: 0.9;">
                <?php echo esc_html($subheadline); ?>
            </p>
            <?php endif; ?>
            
            <?php if ($content): ?>
            <div style="margin: 0 0 var(--hph-space-12) 0; font-size: var(--hph-text-base); line-height: var(--hph-leading-relaxed); max-width: 65ch; margin-left: auto; margin-right: auto;">
                <?php echo wp_kses_post($content); ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($buttons)): ?>
            <div style="display: flex; flex-wrap: wrap; gap: var(--hph-gap-lg); align-items: center; justify-content: center;">
                <?php foreach ($buttons as $button): 
                    $btn_defaults = array(
                        'text' => 'Button',
                        'url' => '#',
                        'style' => 'primary',
                        'target' => '_self'
                    );
                    $btn = wp_parse_args($button, $btn_defaults);
                ?>
                <a href="<?php echo esc_url($btn['url']); ?>" 
                   style="display: inline-flex; align-items: center; padding: var(--hph-space-4) var(--hph-space-8); background-color: var(--hph-primary); color: var(--hph-white); text-decoration: none; border-radius: var(--hph-radius-lg); font-weight: var(--hph-font-semibold); transition: all 300ms ease;"
                   onmouseover="this.style.transform='translateY(-2px)'"
                   onmouseout="this.style.transform='translateY(0)'"
                   <?php if ($btn['target'] !== '_self'): ?>target="<?php echo esc_attr($btn['target']); ?>"<?php endif; ?>>
                    <span><?php echo esc_html($btn['text']); ?></span>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
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

/* Dynamic floating and interactive animations */
<?php if ($hover_effects): ?>
.<?php echo $collage_id; ?>-photo {
    animation: photoFloat 8s ease-in-out infinite, photoTilt 12s ease-in-out infinite;
}

.<?php echo $collage_id; ?>-photo:nth-child(1) {
    animation-delay: 0s, 1s;
    animation-duration: 7s, 11s;
}

.<?php echo $collage_id; ?>-photo:nth-child(2) {
    animation-delay: 2s, 3s;
    animation-duration: 9s, 13s;
}

.<?php echo $collage_id; ?>-photo:nth-child(3) {
    animation-delay: 4s, 2s;
    animation-duration: 8s, 15s;
}

.<?php echo $collage_id; ?>-photo:nth-child(4) {
    animation-delay: 1s, 4s;
    animation-duration: 10s, 12s;
}

/* Add glow effect on collage container hover */
.<?php echo $collage_id; ?>-container:hover .<?php echo $collage_id; ?>-photo {
    animation: photoFloat 8s ease-in-out infinite, photoTilt 12s ease-in-out infinite, photoGlow 3s ease-in-out infinite;
}

/* Parallax-like effect on scroll */
.<?php echo $collage_id; ?>-photo:nth-child(odd) {
    transform-origin: top left;
}

.<?php echo $collage_id; ?>-photo:nth-child(even) {
    transform-origin: bottom right;
}
<?php endif; ?>
</style>

<script>
// Photo collage carousel functionality - scoped to avoid conflicts
(function() {
    const collageId = '<?php echo $collage_id; ?>';
    const container = document.querySelector('.' + collageId + '-container');
    
    if (!container) return;
    
    // Initialize carousel
    let currentPhotoIndex = 0;
    const photos = document.querySelectorAll('.' + collageId + '-photo');
    const dots = document.querySelectorAll('.' + collageId + '-dot');
    let autoRotateTimer;
    
    // Simple positions - overlapping in center
    const positions = [
        { top: '50%', left: '50%', transform: 'translate(-50%, -50%)' },
        { top: '45%', left: '45%', transform: 'translate(-50%, -50%)' },
        { top: '55%', left: '55%', transform: 'translate(-50%, -50%)' },
        { top: '50%', left: '40%', transform: 'translate(-50%, -50%)' }
    ];
    
    // Set active photo
    function setActivePhoto(index) {
        // Remove active class
        photos.forEach(photo => photo.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        
        // Position all photos and set active
        photos.forEach((photo, i) => {
            const position = positions[i % positions.length];
            photo.style.top = position.top;
            photo.style.left = position.left;
            photo.style.transform = position.transform;
            
            if (i === index) {
                photo.classList.add('active');
            }
        });
        
        // Update active dot
        if (dots[index]) {
            dots[index].classList.add('active');
        }
        
        currentPhotoIndex = index;
    }
    
    // Auto-rotation function
    function startAutoRotation() {
        autoRotateTimer = setInterval(() => {
            const nextIndex = (currentPhotoIndex + 1) % photos.length;
            setActivePhoto(nextIndex);
        }, 4000); // Change photo every 4 seconds
    }
    
    // Stop auto-rotation (for user interaction)
    function stopAutoRotation() {
        if (autoRotateTimer) {
            clearInterval(autoRotateTimer);
        }
    }
    
    // Restart auto-rotation after user interaction
    function restartAutoRotation() {
        stopAutoRotation();
        setTimeout(startAutoRotation, 6000); // Wait 6 seconds before resuming auto-rotation
    }
    
    // Initialize
    if (photos.length > 0) {
        setActivePhoto(0);
        startAutoRotation();
    }
    
    // Add click handlers to photos
    photos.forEach((photo, index) => {
        photo.addEventListener('click', () => {
            setActivePhoto(index);
            restartAutoRotation();
        });
    });
    
    // Add click handlers to dots
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            setActivePhoto(index);
            restartAutoRotation();
        });
    });
    
    // Pause auto-rotation on hover
    container.addEventListener('mouseenter', stopAutoRotation);
    container.addEventListener('mouseleave', startAutoRotation);
    
    // Global function for onclick handlers in HTML
    window.setActivePhoto = function(index, id) {
        if (id === collageId) {
            setActivePhoto(index);
            restartAutoRotation();
        }
    };
    
})();

// Ensure DOM is loaded before running additional effects
document.addEventListener('DOMContentLoaded', function() {
    console.log('Photo carousel loaded for:', '<?php echo $collage_id; ?>');
});
</script>
<?php endif; ?>
