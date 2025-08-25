<?php
/**
 * Team Grid Content Type
 * 
 * Displays team members in a responsive grid layout with photos and details
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 * 
 * Args:
 * - members: array of member objects with 'name', 'title', 'photo', 'bio', 'social'
 * - columns: int (2, 3, 4, 5)
 * - card_style: 'default' | 'overlay' | 'minimal' | 'card' | 'circle'
 * - show_bio: bool
 * - show_social: bool
 * - hover_effect: 'none' | 'lift' | 'zoom' | 'slide'
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

$members = $args['members'] ?? array();
$columns = $args['columns'] ?? 3;
$card_style = $args['card_style'] ?? 'default';
$show_bio = $args['show_bio'] ?? true;
$show_social = $args['show_social'] ?? true;
$hover_effect = $args['hover_effect'] ?? 'lift';

if (empty($members)) {
    return;
}

// Generate responsive grid classes
$responsive_cols = 'hph-grid-cols-1 hph-md:grid-cols-2 hph-lg:grid-cols-' . min($columns, 4);
?>

<div class="hph-team-grid hph-team-style-<?php echo esc_attr($card_style); ?> hph-team-hover-<?php echo esc_attr($hover_effect); ?>">
    <div class="hph-grid <?php echo esc_attr($responsive_cols); ?> hph-gap-xl">
        
        <?php foreach ($members as $index => $member): 
            $name = $member['name'] ?? '';
            $title = $member['title'] ?? '';
            $photo = $member['photo'] ?? '';
            $bio = $member['bio'] ?? '';
            $social = $member['social'] ?? array();
            $email = $member['email'] ?? '';
            $phone = $member['phone'] ?? '';
            $delay = $index * 100; // For stagger animation
        ?>
        
        <div class="hph-team-member" data-animation-delay="<?php echo esc_attr($delay); ?>">
            
            <?php if ($photo): ?>
            <div class="hph-team-photo">
                <img src="<?php echo esc_url($photo); ?>" 
                     alt="<?php echo esc_attr($name); ?>" 
                     class="hph-team-image">
                     
                <?php if ($card_style === 'overlay' && $show_social && !empty($social)): ?>
                <div class="hph-team-overlay">
                    <div class="hph-team-social">
                        <?php foreach ($social as $platform => $url): ?>
                        <a href="<?php echo esc_url($url); ?>" 
                           class="hph-social-link hph-social-<?php echo esc_attr($platform); ?>"
                           target="_blank" rel="noopener"
                           aria-label="<?php echo esc_attr($name . ' on ' . ucfirst($platform)); ?>">
                            <i class="fab fa-<?php echo esc_attr($platform); ?>"></i>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <div class="hph-team-content">
                <?php if ($name): ?>
                <h3 class="hph-team-name">
                    <?php echo esc_html($name); ?>
                </h3>
                <?php endif; ?>
                
                <?php if ($title): ?>
                <p class="hph-team-title">
                    <?php echo esc_html($title); ?>
                </p>
                <?php endif; ?>
                
                <?php if ($show_bio && $bio): ?>
                <div class="hph-team-bio">
                    <?php echo wp_kses_post($bio); ?>
                </div>
                <?php endif; ?>
                
                <?php if ($email || $phone): ?>
                <div class="hph-team-contact">
                    <?php if ($email): ?>
                    <a href="mailto:<?php echo esc_attr($email); ?>" 
                       class="hph-team-email">
                        <i class="fas fa-envelope"></i>
                        <?php echo esc_html($email); ?>
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($phone): ?>
                    <a href="tel:<?php echo esc_attr($phone); ?>" 
                       class="hph-team-phone">
                        <i class="fas fa-phone"></i>
                        <?php echo esc_html($phone); ?>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($card_style !== 'overlay' && $show_social && !empty($social)): ?>
                <div class="hph-team-social">
                    <?php foreach ($social as $platform => $url): ?>
                    <a href="<?php echo esc_url($url); ?>" 
                       class="hph-social-link hph-social-<?php echo esc_attr($platform); ?>"
                       target="_blank" rel="noopener"
                       aria-label="<?php echo esc_attr($name . ' on ' . ucfirst($platform)); ?>">
                        <i class="fab fa-<?php echo esc_attr($platform); ?>"></i>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php endforeach; ?>
    </div>
</div>

<style>
/* Team Grid Base Styles */
.hph-team-grid {
    width: 100%;
}

.hph-team-member {
    background: var(--hph-white);
    border-radius: var(--hph-radius-lg);
    overflow: hidden;
    transition: all 0.3s ease;
    opacity: 0;
    animation: fadeInUp 0.6s ease forwards;
}

/* Card Style Variations */
.hph-team-style-default .hph-team-member {
    border: 1px solid var(--hph-gray-200);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.hph-team-style-card .hph-team-member {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border: none;
}

.hph-team-style-minimal .hph-team-member {
    background: transparent;
    border: none;
    box-shadow: none;
    text-align: center;
}

.hph-team-style-circle .hph-team-photo {
    width: 150px;
    height: 150px;
    margin: 0 auto var(--hph-space-lg);
    border-radius: 50%;
    overflow: hidden;
}

.hph-team-style-circle .hph-team-member {
    text-align: center;
    padding: var(--hph-space-xl);
}

.hph-team-style-overlay .hph-team-member {
    position: relative;
    text-align: center;
}

/* Photo Styles */
.hph-team-photo {
    position: relative;
    overflow: hidden;
    aspect-ratio: 1;
}

.hph-team-style-default .hph-team-photo,
.hph-team-style-card .hph-team-photo {
    aspect-ratio: 4/5;
}

.hph-team-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: all 0.3s ease;
}

/* Overlay Style */
.hph-team-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.3s ease;
}

.hph-team-member:hover .hph-team-overlay {
    opacity: 1;
}

/* Content Styles */
.hph-team-content {
    padding: var(--hph-space-lg);
}

.hph-team-style-minimal .hph-team-content {
    padding: var(--hph-space-md) 0;
}

.hph-team-style-circle .hph-team-content {
    padding: 0;
}

.hph-team-name {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--hph-gray-900);
    margin-bottom: var(--hph-space-xs);
    line-height: 1.4;
}

.hph-team-title {
    color: var(--hph-primary);
    font-weight: 500;
    margin-bottom: var(--hph-space-md);
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.hph-team-bio {
    color: var(--hph-gray-600);
    line-height: 1.6;
    margin-bottom: var(--hph-space-md);
    font-size: 0.9rem;
}

/* Contact Info */
.hph-team-contact {
    margin-bottom: var(--hph-space-md);
}

.hph-team-email,
.hph-team-phone {
    display: inline-flex;
    align-items: center;
    gap: var(--hph-space-xs);
    color: var(--hph-gray-600);
    text-decoration: none;
    font-size: 0.875rem;
    margin-right: var(--hph-space-md);
    margin-bottom: var(--hph-space-xs);
    transition: color 0.3s ease;
}

.hph-team-email:hover,
.hph-team-phone:hover {
    color: var(--hph-primary);
    text-decoration: none;
}

/* Social Links */
.hph-team-social {
    display: flex;
    gap: var(--hph-space-sm);
    justify-content: center;
}

.hph-team-style-default .hph-team-social,
.hph-team-style-card .hph-team-social {
    justify-content: flex-start;
}

.hph-social-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--hph-gray-100);
    color: var(--hph-gray-600);
    text-decoration: none;
    transition: all 0.3s ease;
    font-size: 1rem;
}

.hph-social-link:hover {
    text-decoration: none;
    transform: translateY(-2px);
}

/* Social Platform Colors */
.hph-social-linkedin:hover {
    background: #0077b5;
    color: white;
}

.hph-social-twitter:hover {
    background: #1da1f2;
    color: white;
}

.hph-social-facebook:hover {
    background: #1877f2;
    color: white;
}

.hph-social-instagram:hover {
    background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
    color: white;
}

.hph-social-github:hover {
    background: #333;
    color: white;
}

.hph-social-dribbble:hover {
    background: #ea4c89;
    color: white;
}

.hph-social-behance:hover {
    background: #1769ff;
    color: white;
}

/* Overlay Social Links */
.hph-team-overlay .hph-social-link {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.hph-team-overlay .hph-social-link:hover {
    background: white;
    color: var(--hph-gray-900);
}

/* Hover Effects */
.hph-team-hover-lift .hph-team-member:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
}

.hph-team-hover-zoom .hph-team-member:hover .hph-team-image {
    transform: scale(1.1);
}

.hph-team-hover-slide .hph-team-member:hover .hph-team-image {
    transform: translateY(-10px);
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

.hph-team-member:nth-child(1) { animation-delay: 0.1s; }
.hph-team-member:nth-child(2) { animation-delay: 0.2s; }
.hph-team-member:nth-child(3) { animation-delay: 0.3s; }
.hph-team-member:nth-child(4) { animation-delay: 0.4s; }
.hph-team-member:nth-child(5) { animation-delay: 0.5s; }
.hph-team-member:nth-child(6) { animation-delay: 0.6s; }

/* Responsive Design */
@media (max-width: 768px) {
    .hph-team-content {
        padding: var(--hph-space-md);
    }
    
    .hph-team-name {
        font-size: 1.125rem;
    }
    
    .hph-team-bio {
        font-size: 0.875rem;
    }
    
    .hph-team-style-circle .hph-team-photo {
        width: 120px;
        height: 120px;
    }
    
    .hph-team-contact {
        flex-direction: column;
        gap: var(--hph-space-xs);
    }
    
    .hph-team-email,
    .hph-team-phone {
        margin-right: 0;
    }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
    .hph-team-member {
        background: var(--hph-gray-800);
        border-color: var(--hph-gray-700);
    }
    
    .hph-team-name {
        color: var(--hph-white);
    }
    
    .hph-team-bio {
        color: var(--hph-gray-300);
    }
    
    .hph-team-email,
    .hph-team-phone {
        color: var(--hph-gray-400);
    }
}

/* Accessibility */
@media (prefers-reduced-motion: reduce) {
    .hph-team-member {
        animation: none;
        opacity: 1;
    }
    
    .hph-team-member:hover {
        transform: none;
    }
    
    .hph-team-member:hover .hph-team-image {
        transform: none;
    }
    
    .hph-social-link:hover {
        transform: none;
    }
}

/* Focus styles for accessibility */
.hph-social-link:focus,
.hph-team-email:focus,
.hph-team-phone:focus {
    outline: 2px solid var(--hph-primary);
    outline-offset: 2px;
}

/* Print styles */
@media print {
    .hph-team-social {
        display: none;
    }
    
    .hph-team-member {
        break-inside: avoid;
        page-break-inside: avoid;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Intersection Observer for scroll-triggered animations
    const teamMembers = document.querySelectorAll('.hph-team-member');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    
    teamMembers.forEach(member => {
        member.style.animationPlayState = 'paused';
        observer.observe(member);
    });
    
    // Stagger animation based on custom delay
    teamMembers.forEach((member, index) => {
        const delay = member.dataset.animationDelay || (index * 100);
        member.style.animationDelay = delay + 'ms';
    });
    
    // Enhanced touch support for mobile
    teamMembers.forEach(member => {
        member.addEventListener('touchstart', function() {
            this.classList.add('touch-active');
        });
        
        member.addEventListener('touchend', function() {
            setTimeout(() => {
                this.classList.remove('touch-active');
            }, 150);
        });
    });
    
    // Lazy loading for team photos
    const teamImages = document.querySelectorAll('.hph-team-image');
    if (teamImages.length > 0 && 'IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        img.classList.add('loaded');
                    }
                    imageObserver.unobserve(img);
                }
            });
        });
        
        teamImages.forEach(img => {
            if (img.dataset.src) {
                imageObserver.observe(img);
            }
        });
    }
    
    // Social link tracking (if analytics is available)
    const socialLinks = document.querySelectorAll('.hph-social-link');
    socialLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const platform = this.classList.toString().match(/hph-social-(\w+)/);
            if (platform && typeof gtag !== 'undefined') {
                gtag('event', 'social_click', {
                    'social_platform': platform[1],
                    'link_url': this.href
                });
            }
        });
    });
});
</script>
