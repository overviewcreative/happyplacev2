<?php
/**
 * HPH Team Section Template
 * Displays team member grid with photos, bios, and social links
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Register this template part for asset loading
if (function_exists('hph_register_template_part')) {
    hph_register_template_part('sections/team');
}

// Default arguments
$defaults = array(
    'style' => 'grid', // Options: 'grid', 'carousel', 'list', 'cards'
    'theme' => 'white', // Color theme: 'white', 'light', 'dark', 'primary'
    'columns' => 3, // Number of columns for grid
    'padding' => 'xl',
    'container' => 'default',
    'alignment' => 'center',
    'badge' => '',
    'headline' => 'Meet Our Team',
    'subheadline' => '',
    'content' => '',
    'team_members' => array(),
    'show_social' => true,
    'show_bio' => true,
    'image_style' => 'rounded', // Options: 'rounded', 'circle', 'square'
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

// Grid styles based on layout
$grid_styles = array();

if ($style === 'grid' || $style === 'cards') {
    $grid_styles[] = 'display: grid';
    $grid_styles[] = 'gap: var(--hph-gap-xl)';
    
    switch ($columns) {
        case 2:
            $grid_styles[] = 'grid-template-columns: repeat(auto-fit, minmax(320px, 1fr))';
            break;
        case 4:
            $grid_styles[] = 'grid-template-columns: repeat(auto-fit, minmax(280px, 1fr))';
            break;
        case 5:
            $grid_styles[] = 'grid-template-columns: repeat(auto-fit, minmax(240px, 1fr))';
            break;
        case 3:
        default:
            $grid_styles[] = 'grid-template-columns: repeat(auto-fit, minmax(300px, 1fr))';
            break;
    }
} elseif ($style === 'list') {
    $grid_styles[] = 'display: flex';
    $grid_styles[] = 'flex-direction: column';
    $grid_styles[] = 'gap: var(--hph-gap-2xl)';
} elseif ($style === 'carousel') {
    $grid_styles[] = 'display: flex';
    $grid_styles[] = 'gap: var(--hph-gap-xl)';
    $grid_styles[] = 'overflow-x: auto';
    $grid_styles[] = 'scroll-snap-type: x mandatory';
    $grid_styles[] = 'padding-bottom: var(--hph-padding-sm)';
}

// Image styles based on image_style
function getImageStyles($image_style) {
    $styles = array(
        'width: 100%',
        'height: auto',
        'object-fit: cover'
    );
    
    switch ($image_style) {
        case 'circle':
            $styles[] = 'border-radius: var(--hph-radius-full)';
            $styles[] = 'aspect-ratio: 1';
            break;
        case 'square':
            $styles[] = 'border-radius: var(--hph-radius-lg)';
            $styles[] = 'aspect-ratio: 1';
            break;
        case 'rounded':
        default:
            $styles[] = 'border-radius: var(--hph-radius-xl)';
            $styles[] = 'aspect-ratio: 4/5';
            break;
    }
    
    return implode('; ', $styles);
}
?>

<section 
    class="hph-team-section hph-team-<?php echo esc_attr($style); ?>"
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
        
        <?php if (!empty($team_members)): ?>
        <!-- Team Members -->
        <div style="<?php echo implode('; ', $grid_styles); ?>">
            
            <?php foreach ($team_members as $index => $member): 
                $member_defaults = array(
                    'name' => '',
                    'position' => '',
                    'image' => '',
                    'bio' => '',
                    'social' => array(),
                    'email' => '',
                    'phone' => ''
                );
                $member = wp_parse_args($member, $member_defaults);
                
                // Build member item styles
                $item_styles = array();
                
                if ($style === 'cards') {
                    $item_styles[] = 'background: var(--hph-white)';
                    $item_styles[] = 'border-radius: var(--hph-radius-xl)';
                    $item_styles[] = 'box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1)';
                    $item_styles[] = 'padding: var(--hph-padding-xl)';
                    $item_styles[] = 'transition: all 300ms ease';
                    $item_styles[] = 'text-align: center';
                } elseif ($style === 'list') {
                    $item_styles[] = 'display: flex';
                    $item_styles[] = 'align-items: center';
                    $item_styles[] = 'gap: var(--hph-gap-xl)';
                    $item_styles[] = 'text-align: left';
                } elseif ($style === 'carousel') {
                    $item_styles[] = 'min-width: 300px';
                    $item_styles[] = 'scroll-snap-align: start';
                    $item_styles[] = 'text-align: center';
                } else {
                    $item_styles[] = 'text-align: center';
                }
                
                $animation_delay = $animation ? 'animation: fadeInUp 0.8s ease-out ' . ($index * 0.1) . 's; opacity: 0; animation-fill-mode: forwards;' : '';
            ?>
            
            <div 
                class="hph-team-member"
                style="<?php echo implode('; ', $item_styles); ?> <?php echo $animation_delay; ?>"
                <?php if ($style === 'cards'): ?>
                onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 8px 25px rgba(0, 0, 0, 0.15)';"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(0, 0, 0, 0.1)';"
                <?php endif; ?>
            >
                
                <?php if ($member['image']): ?>
                <!-- Member Image -->
                <div style="<?php echo $style === 'list' ? 'flex-shrink: 0; width: 120px;' : 'margin-bottom: var(--hph-margin-lg);'; ?>">
                    <img 
                        src="<?php echo esc_url($member['image']); ?>" 
                        alt="<?php echo esc_attr($member['name']); ?>"
                        style="<?php echo getImageStyles($image_style); ?>"
                        loading="lazy"
                    >
                </div>
                <?php endif; ?>
                
                <!-- Member Details -->
                <div style="<?php echo $style === 'list' ? 'flex: 1;' : ''; ?>">
                    
                    <?php if ($member['name']): ?>
                    <!-- Name -->
                    <h3 style="margin: 0 0 var(--hph-margin-sm) 0; font-size: var(--hph-text-xl); font-weight: var(--hph-font-bold); line-height: var(--hph-leading-tight);">
                        <?php echo esc_html($member['name']); ?>
                    </h3>
                    <?php endif; ?>
                    
                    <?php if ($member['position']): ?>
                    <!-- Position -->
                    <p style="margin: 0 0 var(--hph-margin-md) 0; font-size: var(--hph-text-base); font-weight: var(--hph-font-medium); color: var(--hph-primary); opacity: 0.9;">
                        <?php echo esc_html($member['position']); ?>
                    </p>
                    <?php endif; ?>
                    
                    <?php if ($show_bio && $member['bio']): ?>
                    <!-- Bio -->
                    <p style="margin: 0 0 var(--hph-margin-lg) 0; color: var(--hph-gray-600); line-height: var(--hph-leading-relaxed); font-size: var(--hph-text-sm);">
                        <?php echo wp_kses_post($member['bio']); ?>
                    </p>
                    <?php endif; ?>
                    
                    <?php if ($member['email'] || $member['phone']): ?>
                    <!-- Contact Info -->
                    <div style="margin-bottom: var(--hph-margin-lg); font-size: var(--hph-text-sm);">
                        <?php if ($member['email']): ?>
                        <p style="margin: 0 0 var(--hph-margin-xs) 0;">
                            <a href="mailto:<?php echo esc_attr($member['email']); ?>" style="color: var(--hph-primary); text-decoration: none;">
                                <i class="fas fa-envelope" style="margin-right: var(--hph-margin-xs);"></i>
                                <?php echo esc_html($member['email']); ?>
                            </a>
                        </p>
                        <?php endif; ?>
                        
                        <?php if ($member['phone']): ?>
                        <p style="margin: 0;">
                            <a href="tel:<?php echo esc_attr($member['phone']); ?>" style="color: var(--hph-primary); text-decoration: none;">
                                <i class="fas fa-phone" style="margin-right: var(--hph-margin-xs);"></i>
                                <?php echo esc_html($member['phone']); ?>
                            </a>
                        </p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($show_social && !empty($member['social'])): ?>
                    <!-- Social Links -->
                    <div style="display: flex; gap: var(--hph-gap-sm); <?php echo $style === 'list' ? 'justify-content: flex-start;' : 'justify-content: center;'; ?>">
                        <?php foreach ($member['social'] as $platform => $url): 
                            if (!$url || $url === '#') continue;
                            
                            $icon_class = '';
                            switch ($platform) {
                                case 'linkedin':
                                    $icon_class = 'fab fa-linkedin-in';
                                    break;
                                case 'twitter':
                                    $icon_class = 'fab fa-twitter';
                                    break;
                                case 'facebook':
                                    $icon_class = 'fab fa-facebook-f';
                                    break;
                                case 'instagram':
                                    $icon_class = 'fab fa-instagram';
                                    break;
                                case 'youtube':
                                    $icon_class = 'fab fa-youtube';
                                    break;
                                case 'tiktok':
                                    $icon_class = 'fab fa-tiktok';
                                    break;
                                default:
                                    $icon_class = 'fas fa-link';
                            }
                        ?>
                        <a 
                            href="<?php echo esc_url($url); ?>" 
                            target="_blank" 
                            rel="noopener noreferrer"
                            style="display: inline-flex; align-items: center; justify-content: center; width: 2.5rem; height: 2.5rem; background: var(--hph-gray-100); color: var(--hph-gray-600); border-radius: var(--hph-radius-full); transition: all 0.2s ease; text-decoration: none;"
                            onmouseover="this.style.background='var(--hph-primary)'; this.style.color='var(--hph-white)'; this.style.transform='translateY(-2px)'"
                            onmouseout="this.style.background='var(--hph-gray-100)'; this.style.color='var(--hph-gray-600)'; this.style.transform='translateY(0)'"
                            title="<?php echo esc_attr(ucfirst($platform)); ?>"
                        >
                            <i class="<?php echo esc_attr($icon_class); ?>"></i>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                </div>
                
            </div>
            
            <?php endforeach; ?>
            
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

/* Carousel scrollbar styling */
.hph-team-carousel::-webkit-scrollbar {
    height: 6px;
}

.hph-team-carousel::-webkit-scrollbar-track {
    background: var(--hph-gray-100);
    border-radius: 3px;
}

.hph-team-carousel::-webkit-scrollbar-thumb {
    background: var(--hph-primary);
    border-radius: 3px;
}

.hph-team-carousel::-webkit-scrollbar-thumb:hover {
    background: var(--hph-primary-dark);
}
</style>
<?php endif; ?>

<style>
/* Responsive adjustments */
@media (max-width: 768px) {
    .hph-team-member {
        text-align: center !important;
    }
    
    .hph-team-section [style*="display: flex"] .hph-team-member {
        flex-direction: column;
        text-align: center;
    }
    
    .hph-team-section [style*="display: flex"] .hph-team-member > div:first-child {
        width: 120px !important;
        margin: 0 auto var(--hph-margin-lg) auto;
    }
}

@media (max-width: 480px) {
    .hph-team-section [style*="grid-template-columns"] {
        grid-template-columns: 1fr !important;
    }
}
</style>
