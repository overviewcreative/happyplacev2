<?php
/**
 * Features Grid Content Type
 * 
 * Displays features/services in a responsive grid layout with icons
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 * 
 * Args:
 * - items: array of feature objects with 'title', 'description', 'icon', 'link'
 * - columns: int (2, 3, 4, 6)
 * - card_style: 'default' | 'hover-lift' | 'border' | 'shadow' | 'minimal'
 * - grid_classes: array
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

$items = $args['items'] ?? array();
$columns = $args['columns'] ?? 3;
$card_style = $args['card_style'] ?? 'default';
$grid_classes = $args['grid_classes'] ?? array();

if (empty($items)) {
    return;
}

// Build grid classes if not provided
if (empty($grid_classes)) {
    $grid_classes = array(
        'hph-grid',
        'hph-grid-cols-1',
        'hph-md:grid-cols-2',
        'hph-lg:grid-cols-' . min($columns, 4),
        'hph-gap-lg'
    );
}
?>

<div class="hph-features-grid hph-features-style-<?php echo esc_attr($card_style); ?>">
    <div class="<?php echo esc_attr(implode(' ', $grid_classes)); ?>">
        
        <?php foreach ($items as $index => $item): 
            $title = $item['title'] ?? '';
            $description = $item['description'] ?? '';
            $icon = $item['icon'] ?? '';
            $link = $item['link'] ?? '';
            $color = $item['color'] ?? 'primary';
            $delay = $index * 100; // For stagger animation
        ?>
        
        <div class="hph-feature-card hph-feature-color-<?php echo esc_attr($color); ?>" 
             data-animation-delay="<?php echo esc_attr($delay); ?>">
            
            <?php if ($link): ?>
            <a href="<?php echo esc_url($link); ?>" class="hph-feature-link">
            <?php endif; ?>
            
                <?php if ($icon): ?>
                <div class="hph-feature-icon">
                    <i class="<?php echo esc_attr($icon); ?>"></i>
                </div>
                <?php endif; ?>
                
                <div class="hph-feature-content">
                    <?php if ($title): ?>
                    <h3 class="hph-feature-title">
                        <?php echo esc_html($title); ?>
                    </h3>
                    <?php endif; ?>
                    
                    <?php if ($description): ?>
                    <p class="hph-feature-description">
                        <?php echo wp_kses_post($description); ?>
                    </p>
                    <?php endif; ?>
                </div>
                
                <?php if ($link): ?>
                <div class="hph-feature-arrow">
                    <i class="fas fa-arrow-right"></i>
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
/* Features Grid Styles */
.hph-features-grid {
    width: 100%;
}

.hph-feature-card {
    background: var(--hph-white);
    border-radius: var(--hph-radius-lg);
    padding: var(--hph-space-xl);
    transition: all 0.3s ease;
    position: relative;
    display: flex;
    flex-direction: column;
    height: 100%;
    text-align: center;
}

.hph-feature-link {
    display: flex;
    flex-direction: column;
    height: 100%;
    text-decoration: none;
    color: inherit;
}

.hph-feature-link:hover {
    text-decoration: none;
    color: inherit;
}

/* Card Style Variations */
.hph-features-style-default .hph-feature-card {
    border: 1px solid var(--hph-gray-100);
}

.hph-features-style-border .hph-feature-card {
    border: 2px solid var(--hph-gray-200);
}

.hph-features-style-shadow .hph-feature-card {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: none;
}

.hph-features-style-minimal .hph-feature-card {
    background: transparent;
    border: none;
    padding: var(--hph-space-lg);
}

.hph-features-style-hover-lift .hph-feature-card {
    border: 1px solid var(--hph-gray-100);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
}

.hph-features-style-hover-lift .hph-feature-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    border-color: var(--hph-primary-200);
}

/* Icon Styles */
.hph-feature-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto var(--hph-space-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 2rem;
    background: var(--hph-primary-100);
    color: var(--hph-primary);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.hph-feature-icon::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    transition: left 0.5s ease;
}

.hph-feature-card:hover .hph-feature-icon {
    background: var(--hph-primary);
    color: var(--hph-white);
    transform: scale(1.1);
}

.hph-feature-card:hover .hph-feature-icon::before {
    left: 100%;
}

/* Content Styles */
.hph-feature-content {
    flex: 1;
}

.hph-feature-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--hph-gray-900);
    margin-bottom: var(--hph-space-md);
    line-height: 1.4;
}

.hph-feature-description {
    color: var(--hph-gray-600);
    line-height: 1.6;
    margin: 0;
}

/* Arrow for linked features */
.hph-feature-arrow {
    margin-top: var(--hph-space-lg);
    color: var(--hph-primary);
    opacity: 0;
    transform: translateX(-10px);
    transition: all 0.3s ease;
}

.hph-feature-link:hover .hph-feature-arrow {
    opacity: 1;
    transform: translateX(0);
}

/* Color Variations */
.hph-feature-color-secondary .hph-feature-icon {
    background: var(--hph-secondary-100);
    color: var(--hph-secondary);
}

.hph-feature-color-secondary .hph-feature-card:hover .hph-feature-icon {
    background: var(--hph-secondary);
    color: var(--hph-white);
}

.hph-feature-color-secondary .hph-feature-arrow {
    color: var(--hph-secondary);
}

.hph-feature-color-success .hph-feature-icon {
    background: var(--hph-success-100);
    color: var(--hph-success);
}

.hph-feature-color-success .hph-feature-card:hover .hph-feature-icon {
    background: var(--hph-success);
    color: var(--hph-white);
}

.hph-feature-color-success .hph-feature-arrow {
    color: var(--hph-success);
}

.hph-feature-color-warning .hph-feature-icon {
    background: var(--hph-warning-100);
    color: var(--hph-warning);
}

.hph-feature-color-warning .hph-feature-card:hover .hph-feature-icon {
    background: var(--hph-warning);
    color: var(--hph-white);
}

.hph-feature-color-warning .hph-feature-arrow {
    color: var(--hph-warning);
}

.hph-feature-color-info .hph-feature-icon {
    background: var(--hph-info-100);
    color: var(--hph-info);
}

.hph-feature-color-info .hph-feature-card:hover .hph-feature-icon {
    background: var(--hph-info);
    color: var(--hph-white);
}

.hph-feature-color-info .hph-feature-arrow {
    color: var(--hph-info);
}

/* Responsive Design */
@media (max-width: 768px) {
    .hph-feature-card {
        padding: var(--hph-space-lg);
        text-align: center;
    }
    
    .hph-feature-icon {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
        margin-bottom: var(--hph-space-md);
    }
    
    .hph-feature-title {
        font-size: 1.125rem;
    }
    
    .hph-feature-description {
        font-size: 0.875rem;
    }
}

/* Animation */
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

.hph-feature-card {
    opacity: 0;
    animation: fadeInUp 0.6s ease forwards;
}

.hph-feature-card:nth-child(1) { animation-delay: 0.1s; }
.hph-feature-card:nth-child(2) { animation-delay: 0.2s; }
.hph-feature-card:nth-child(3) { animation-delay: 0.3s; }
.hph-feature-card:nth-child(4) { animation-delay: 0.4s; }
.hph-feature-card:nth-child(5) { animation-delay: 0.5s; }
.hph-feature-card:nth-child(6) { animation-delay: 0.6s; }

/* Reduce motion for accessibility */
@media (prefers-reduced-motion: reduce) {
    .hph-feature-card {
        animation: none;
        opacity: 1;
    }
    
    .hph-feature-card:hover {
        transform: none;
    }
    
    .hph-feature-card:hover .hph-feature-icon {
        transform: none;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Intersection Observer for scroll-triggered animations
    const features = document.querySelectorAll('.hph-feature-card');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    
    features.forEach(feature => {
        feature.style.animationPlayState = 'paused';
        observer.observe(feature);
    });
    
    // Enhanced hover effects for touch devices
    features.forEach(feature => {
        feature.addEventListener('touchstart', function() {
            this.classList.add('touch-active');
        });
        
        feature.addEventListener('touchend', function() {
            setTimeout(() => {
                this.classList.remove('touch-active');
            }, 150);
        });
    });
});
</script>
