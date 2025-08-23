<?php
/**
 * Icon Grid Content Type
 * 
 * Displays icons with labels in a responsive grid layout
 * Perfect for skills, technologies, services, or partner logos
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 * 
 * Args:
 * - items: array of icon objects with 'icon', 'label', 'link', 'description'
 * - columns: int (3, 4, 5, 6, 8, 10)
 * - style: 'default' | 'circle' | 'square' | 'minimal' | 'logo'
 * - size: 'sm' | 'md' | 'lg' | 'xl'
 * - hover_effect: 'none' | 'lift' | 'rotate' | 'scale' | 'glow'
 * - layout: 'grid' | 'masonry' | 'carousel'
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

$items = $args['items'] ?? array();
$columns = $args['columns'] ?? 5;
$style = $args['style'] ?? 'default';
$size = $args['size'] ?? 'md';
$hover_effect = $args['hover_effect'] ?? 'lift';
$layout = $args['layout'] ?? 'grid';

if (empty($items)) {
    return;
}

// Generate responsive grid classes
$grid_classes = array(
    'hph-icon-grid',
    'hph-icon-style-' . $style,
    'hph-icon-size-' . $size,
    'hph-icon-hover-' . $hover_effect,
    'hph-icon-layout-' . $layout
);

// Grid columns based on count
$responsive_cols = 'hph-grid-cols-2 hph-md:grid-cols-' . min($columns, 4) . ' hph-lg:grid-cols-' . $columns;
?>

<div class="<?php echo esc_attr(implode(' ', $grid_classes)); ?>">
    <div class="hph-grid <?php echo esc_attr($responsive_cols); ?> hph-gap-lg">
        
        <?php foreach ($items as $index => $item): 
            $icon = $item['icon'] ?? '';
            $label = $item['label'] ?? '';
            $link = $item['link'] ?? '';
            $description = $item['description'] ?? '';
            $image = $item['image'] ?? ''; // For logo style
            $color = $item['color'] ?? 'primary';
            $delay = $index * 50; // For stagger animation
        ?>
        
        <div class="hph-icon-item hph-icon-color-<?php echo esc_attr($color); ?>" 
             data-animation-delay="<?php echo esc_attr($delay); ?>"
             <?php if ($description): ?>title="<?php echo esc_attr($description); ?>"<?php endif; ?>>
            
            <?php if ($link): ?>
            <a href="<?php echo esc_url($link); ?>" class="hph-icon-link" 
               <?php if ($description): ?>aria-label="<?php echo esc_attr($description); ?>"<?php endif; ?>>
            <?php endif; ?>
            
                <div class="hph-icon-container">
                    <?php if ($style === 'logo' && $image): ?>
                        <img src="<?php echo esc_url($image); ?>" 
                             alt="<?php echo esc_attr($label); ?>" 
                             class="hph-icon-image">
                    <?php elseif ($icon): ?>
                        <i class="<?php echo esc_attr($icon); ?>" aria-hidden="true"></i>
                    <?php endif; ?>
                </div>
                
                <?php if ($label): ?>
                <div class="hph-icon-label">
                    <?php echo esc_html($label); ?>
                </div>
                <?php endif; ?>
            
            <?php if ($link): ?>
            </a>
            <?php endif; ?>
        </div>
        
        <?php endforeach; ?>
    </div>
</div>

<style>
/* Icon Grid Base Styles */
.hph-icon-grid {
    width: 100%;
}

.hph-icon-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    transition: all 0.3s ease;
    opacity: 0;
    animation: fadeInUp 0.5s ease forwards;
}

.hph-icon-link {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    color: inherit;
    width: 100%;
    padding: var(--hph-space-md);
    border-radius: var(--hph-radius-md);
    transition: all 0.3s ease;
}

.hph-icon-link:hover {
    text-decoration: none;
    color: inherit;
}

.hph-icon-container {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: var(--hph-space-sm);
    transition: all 0.3s ease;
}

.hph-icon-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--hph-gray-700);
    line-height: 1.4;
    transition: all 0.3s ease;
}

/* Size Variations */
.hph-icon-size-sm .hph-icon-container {
    width: 40px;
    height: 40px;
    font-size: 1.25rem;
}

.hph-icon-size-sm .hph-icon-label {
    font-size: 0.75rem;
}

.hph-icon-size-md .hph-icon-container {
    width: 60px;
    height: 60px;
    font-size: 1.5rem;
}

.hph-icon-size-lg .hph-icon-container {
    width: 80px;
    height: 80px;
    font-size: 2rem;
}

.hph-icon-size-lg .hph-icon-label {
    font-size: 1rem;
}

.hph-icon-size-xl .hph-icon-container {
    width: 100px;
    height: 100px;
    font-size: 2.5rem;
}

.hph-icon-size-xl .hph-icon-label {
    font-size: 1.125rem;
    font-weight: 600;
}

/* Style Variations */
.hph-icon-style-default .hph-icon-container {
    background: var(--hph-gray-50);
    border: 1px solid var(--hph-gray-200);
    border-radius: var(--hph-radius-md);
    color: var(--hph-gray-600);
}

.hph-icon-style-circle .hph-icon-container {
    background: var(--hph-primary-100);
    border-radius: 50%;
    color: var(--hph-primary);
}

.hph-icon-style-square .hph-icon-container {
    background: var(--hph-gray-900);
    border-radius: var(--hph-radius-sm);
    color: var(--hph-white);
}

.hph-icon-style-minimal .hph-icon-container {
    background: transparent;
    color: var(--hph-gray-600);
}

.hph-icon-style-logo .hph-icon-container {
    background: var(--hph-white);
    border: 1px solid var(--hph-gray-100);
    border-radius: var(--hph-radius-md);
    padding: var(--hph-space-sm);
}

.hph-icon-style-logo .hph-icon-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    filter: grayscale(1);
    transition: filter 0.3s ease;
}

/* Hover Effects */
.hph-icon-hover-lift .hph-icon-item:hover {
    transform: translateY(-4px);
}

.hph-icon-hover-scale .hph-icon-item:hover .hph-icon-container {
    transform: scale(1.1);
}

.hph-icon-hover-rotate .hph-icon-item:hover .hph-icon-container {
    transform: rotate(5deg);
}

.hph-icon-hover-glow .hph-icon-item:hover .hph-icon-container {
    box-shadow: 0 0 20px rgba(var(--hph-primary-rgb), 0.3);
}

/* Color Variations */
.hph-icon-color-primary .hph-icon-style-circle .hph-icon-container,
.hph-icon-color-primary .hph-icon-style-default .hph-icon-container {
    background: var(--hph-primary-100);
    color: var(--hph-primary);
    border-color: var(--hph-primary-200);
}

.hph-icon-color-secondary .hph-icon-style-circle .hph-icon-container,
.hph-icon-color-secondary .hph-icon-style-default .hph-icon-container {
    background: var(--hph-secondary-100);
    color: var(--hph-secondary);
    border-color: var(--hph-secondary-200);
}

.hph-icon-color-success .hph-icon-style-circle .hph-icon-container,
.hph-icon-color-success .hph-icon-style-default .hph-icon-container {
    background: var(--hph-success-100);
    color: var(--hph-success);
    border-color: var(--hph-success-200);
}

.hph-icon-color-warning .hph-icon-style-circle .hph-icon-container,
.hph-icon-color-warning .hph-icon-style-default .hph-icon-container {
    background: var(--hph-warning-100);
    color: var(--hph-warning);
    border-color: var(--hph-warning-200);
}

.hph-icon-color-info .hph-icon-style-circle .hph-icon-container,
.hph-icon-color-info .hph-icon-style-default .hph-icon-container {
    background: var(--hph-info-100);
    color: var(--hph-info);
    border-color: var(--hph-info-200);
}

/* Interactive States */
.hph-icon-link:hover .hph-icon-container {
    background: var(--hph-primary);
    color: var(--hph-white);
    border-color: var(--hph-primary);
}

.hph-icon-link:hover .hph-icon-label {
    color: var(--hph-primary);
}

.hph-icon-style-logo .hph-icon-link:hover .hph-icon-image {
    filter: grayscale(0);
}

.hph-icon-style-logo .hph-icon-link:hover .hph-icon-container {
    border-color: var(--hph-primary-200);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Masonry Layout */
.hph-icon-layout-masonry {
    column-count: 3;
    column-gap: var(--hph-space-lg);
}

.hph-icon-layout-masonry .hph-icon-item {
    break-inside: avoid;
    margin-bottom: var(--hph-space-lg);
}

@media (min-width: 768px) {
    .hph-icon-layout-masonry {
        column-count: 4;
    }
}

@media (min-width: 1024px) {
    .hph-icon-layout-masonry {
        column-count: 5;
    }
}

/* Responsive Design */
@media (max-width: 640px) {
    .hph-icon-size-lg .hph-icon-container,
    .hph-icon-size-xl .hph-icon-container {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
    }
    
    .hph-icon-size-lg .hph-icon-label,
    .hph-icon-size-xl .hph-icon-label {
        font-size: 0.875rem;
    }
    
    .hph-icon-layout-masonry {
        column-count: 2;
    }
}

/* Animation */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.hph-icon-item:nth-child(1) { animation-delay: 0.1s; }
.hph-icon-item:nth-child(2) { animation-delay: 0.15s; }
.hph-icon-item:nth-child(3) { animation-delay: 0.2s; }
.hph-icon-item:nth-child(4) { animation-delay: 0.25s; }
.hph-icon-item:nth-child(5) { animation-delay: 0.3s; }
.hph-icon-item:nth-child(6) { animation-delay: 0.35s; }
.hph-icon-item:nth-child(7) { animation-delay: 0.4s; }
.hph-icon-item:nth-child(8) { animation-delay: 0.45s; }
.hph-icon-item:nth-child(9) { animation-delay: 0.5s; }
.hph-icon-item:nth-child(10) { animation-delay: 0.55s; }

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.hph-icon-container {
    animation: pulse 2s infinite;
}

.hph-icon-item:hover .hph-icon-container {
    animation: none;
}

/* Accessibility */
@media (prefers-reduced-motion: reduce) {
    .hph-icon-item {
        animation: none;
        opacity: 1;
    }
    
    .hph-icon-container {
        animation: none;
    }
    
    .hph-icon-item:hover {
        transform: none;
    }
    
    .hph-icon-item:hover .hph-icon-container {
        transform: none;
    }
}

/* Focus styles for accessibility */
.hph-icon-link:focus {
    outline: 2px solid var(--hph-primary);
    outline-offset: 2px;
}

.hph-icon-link:focus .hph-icon-container {
    background: var(--hph-primary);
    color: var(--hph-white);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Intersection Observer for scroll-triggered animations
    const iconItems = document.querySelectorAll('.hph-icon-item');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    
    iconItems.forEach(item => {
        item.style.animationPlayState = 'paused';
        observer.observe(item);
    });
    
    // Stagger animation based on custom delay
    iconItems.forEach((item, index) => {
        const delay = item.dataset.animationDelay || (index * 50);
        item.style.animationDelay = delay + 'ms';
    });
    
    // Enhanced touch support
    iconItems.forEach(item => {
        const link = item.querySelector('.hph-icon-link');
        if (link) {
            link.addEventListener('touchstart', function() {
                this.style.transform = 'scale(0.95)';
            });
            
            link.addEventListener('touchend', function() {
                this.style.transform = '';
            });
        }
    });
    
    // Lazy loading for images in logo style
    const logoImages = document.querySelectorAll('.hph-icon-image');
    if (logoImages.length > 0 && 'IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                    }
                    imageObserver.unobserve(img);
                }
            });
        });
        
        logoImages.forEach(img => imageObserver.observe(img));
    }
});
</script>
