/**
 * HPH Content Section Interactions
 * JavaScript for content section components (accordion, counters, etc.)
 * 
 * @package HappyPlaceTheme
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Accordion functionality
    const accordionTriggers = document.querySelectorAll('.hph-accordion-trigger');
    
    accordionTriggers.forEach(trigger => {
        trigger.addEventListener('click', function() {
            const content = document.getElementById(this.getAttribute('aria-controls'));
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            // Close all other accordions if multiple open is not allowed
            if (!isExpanded && !document.querySelector('.hph-accordion-list').dataset.allowMultiple) {
                accordionTriggers.forEach(otherTrigger => {
                    if (otherTrigger !== this) {
                        otherTrigger.setAttribute('aria-expanded', 'false');
                        const otherContent = document.getElementById(otherTrigger.getAttribute('aria-controls'));
                        otherContent.classList.remove('hph-show');
                        otherContent.classList.add('hph-hidden');
                    }
                });
            }
            
            // Toggle current accordion
            if (isExpanded) {
                this.setAttribute('aria-expanded', 'false');
                content.classList.remove('hph-show');
                content.classList.add('hph-hidden');
            } else {
                this.setAttribute('aria-expanded', 'true');
                content.classList.remove('hph-hidden');
                content.classList.add('hph-show');
            }
            
            // Add icon rotation if needed
            const icon = this.querySelector('[data-icon]');
            if (icon) {
                icon.style.transform = isExpanded ? 'rotate(0deg)' : 'rotate(180deg)';
            }
        });
    });
    
    // Counter animation with Intersection Observer
    const counters = document.querySelectorAll('.hph-stat-counter');
    
    if (counters.length > 0) {
        const observerOptions = {
            threshold: 0.5,
            rootMargin: '0px 0px -100px 0px'
        };
        
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                const counter = entry.target;
                
                if (entry.isIntersecting && !counter.classList.contains('animated')) {
                    counter.classList.add('animated');
                    
                    const target = parseInt(counter.getAttribute('data-target') || counter.textContent);
                    const duration = parseInt(counter.getAttribute('data-duration') || 2000);
                    const startTime = performance.now();
                    
                    function updateCounter(currentTime) {
                        const elapsed = currentTime - startTime;
                        const progress = Math.min(elapsed / duration, 1);
                        
                        // Easing function for smooth animation
                        const easedProgress = 1 - Math.pow(1 - progress, 3);
                        const currentValue = Math.round(target * easedProgress);
                        
                        counter.textContent = currentValue;
                        
                        if (progress < 1) {
                            requestAnimationFrame(updateCounter);
                        } else {
                            counter.textContent = target;
                        }
                    }
                    
                    requestAnimationFrame(updateCounter);
                    
                    // Unobserve after animation completes
                    counterObserver.unobserve(counter);
                }
            });
        }, observerOptions);
        
        counters.forEach(counter => {
            counterObserver.observe(counter);
        });
    }
    
});