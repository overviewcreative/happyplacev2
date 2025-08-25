<?php
/**
 * Stats Counter Content Type
 * 
 * Displays statistics with animated counters and icons
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 * 
 * Args:
 * - stats: array of stat objects with 'number', 'label', 'icon', 'suffix'
 * - counter_animation: boolean
 * - grid_classes: array
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

$stats = $args['stats'] ?? array();
$counter_animation = $args['counter_animation'] ?? true;
$grid_classes = $args['grid_classes'] ?? array('hph-grid', 'hph-grid-cols-2', 'hph-lg:grid-cols-4', 'hph-gap-lg');

if (empty($stats)) {
    return;
}
?>

<div class="hph-stats-container">
    <div class="<?php echo esc_attr(implode(' ', $grid_classes)); ?>">
        <?php foreach ($stats as $index => $stat): 
            $number = $stat['number'] ?? '0';
            $label = $stat['label'] ?? '';
            $icon = $stat['icon'] ?? '';
            $suffix = $stat['suffix'] ?? '';
            $prefix = $stat['prefix'] ?? '';
            $color = $stat['color'] ?? 'primary';
        ?>
        <div class="hph-stat-item hph-stat-color-<?php echo esc_attr($color); ?>" 
             data-animation="<?php echo $counter_animation ? 'true' : 'false'; ?>"
             data-delay="<?php echo $index * 100; ?>">
            
            <?php if ($icon): ?>
            <div class="hph-stat-icon">
                <i class="<?php echo esc_attr($icon); ?>"></i>
            </div>
            <?php endif; ?>
            
            <div class="hph-stat-content">
                <div class="hph-stat-number" 
                     data-target="<?php echo esc_attr(preg_replace('/[^0-9.]/', '', $number)); ?>"
                     data-prefix="<?php echo esc_attr($prefix); ?>"
                     data-suffix="<?php echo esc_attr($suffix); ?>">
                    <?php if ($counter_animation): ?>
                        <?php echo esc_html($prefix); ?>0<?php echo esc_html($suffix); ?>
                    <?php else: ?>
                        <?php echo esc_html($number); ?>
                    <?php endif; ?>
                </div>
                
                <?php if ($label): ?>
                <div class="hph-stat-label">
                    <?php echo esc_html($label); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
/* Stats Counter Styles */
.hph-stats-container {
    width: 100%;
}

.hph-stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: var(--hph-space-lg);
    border-radius: var(--hph-radius-lg);
    background: var(--hph-white);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.hph-stat-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.hph-stat-icon {
    margin-bottom: var(--hph-space-md);
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 1.5rem;
    background: var(--hph-primary-100);
    color: var(--hph-primary);
    transition: all 0.3s ease;
}

.hph-stat-item:hover .hph-stat-icon {
    background: var(--hph-primary);
    color: var(--hph-white);
    transform: scale(1.1);
}

.hph-stat-content {
    width: 100%;
}

.hph-stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    line-height: 1;
    color: var(--hph-primary);
    margin-bottom: var(--hph-space-sm);
    transition: all 0.3s ease;
}

.hph-stat-label {
    font-size: 1rem;
    font-weight: 500;
    color: var(--hph-gray-600);
    line-height: 1.4;
}

/* Color Variations */
.hph-stat-color-secondary .hph-stat-icon {
    background: var(--hph-secondary-100);
    color: var(--hph-secondary);
}

.hph-stat-color-secondary:hover .hph-stat-icon {
    background: var(--hph-secondary);
    color: var(--hph-white);
}

.hph-stat-color-secondary .hph-stat-number {
    color: var(--hph-secondary);
}

.hph-stat-color-success .hph-stat-icon {
    background: var(--hph-success-100);
    color: var(--hph-success);
}

.hph-stat-color-success:hover .hph-stat-icon {
    background: var(--hph-success);
    color: var(--hph-white);
}

.hph-stat-color-success .hph-stat-number {
    color: var(--hph-success);
}

.hph-stat-color-warning .hph-stat-icon {
    background: var(--hph-warning-100);
    color: var(--hph-warning);
}

.hph-stat-color-warning:hover .hph-stat-icon {
    background: var(--hph-warning);
    color: var(--hph-white);
}

.hph-stat-color-warning .hph-stat-number {
    color: var(--hph-warning);
}

/* Responsive Design */
@media (max-width: 768px) {
    .hph-stat-item {
        padding: var(--hph-space-md);
    }
    
    .hph-stat-icon {
        width: 50px;
        height: 50px;
        font-size: 1.25rem;
    }
    
    .hph-stat-number {
        font-size: 2rem;
    }
    
    .hph-stat-label {
        font-size: 0.875rem;
    }
}

/* Animation for counter */
@keyframes countUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.hph-stat-item[data-animation="true"] {
    opacity: 0;
    animation: countUp 0.6s ease forwards;
}
</style>

<script>
// Counter Animation JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const stats = document.querySelectorAll('.hph-stat-item[data-animation="true"]');
    
    // Intersection Observer for scroll-triggered animation
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    
    stats.forEach(stat => observer.observe(stat));
    
    function animateCounter(statItem) {
        const numberElement = statItem.querySelector('.hph-stat-number');
        const target = parseFloat(numberElement.dataset.target);
        const prefix = numberElement.dataset.prefix || '';
        const suffix = numberElement.dataset.suffix || '';
        const delay = parseInt(statItem.dataset.delay) || 0;
        
        setTimeout(() => {
            statItem.style.opacity = '1';
            
            let current = 0;
            const increment = target / 60; // 60 steps for smooth animation
            const duration = 1500; // 1.5 seconds
            const stepTime = duration / 60;
            
            const counter = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(counter);
                }
                
                let displayValue = Math.floor(current);
                if (target % 1 !== 0) { // If decimal number
                    displayValue = current.toFixed(1);
                }
                
                numberElement.textContent = prefix + displayValue + suffix;
            }, stepTime);
        }, delay);
    }
});
</script>
