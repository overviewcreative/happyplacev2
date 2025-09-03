<?php
/**
 * HPH Content Section Template
 * Rewritten using only CSS variables and inline styles
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Register this template part for asset loading
if (function_exists('hph_register_template_part')) {
    hph_register_template_part('sections/content');
}

// Default arguments
$defaults = array(
    'layout' => 'centered',
    'background' => 'white',
    'padding' => 'xl',
    'content_width' => 'normal',
    'alignment' => 'center',
    'image' => null,
    'badge' => '',
    'headline' => 'Content Section',
    'headline_tag' => 'h2',
    'subheadline' => '',
    'content' => '',
    'buttons' => array(),
    'items' => array(),
    'stats' => array(),
    'columns' => 3,
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
    case 'secondary':
        $section_styles[] = 'background-color: var(--hph-secondary)';
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

// Layout-specific styles
if ($layout === 'two-column' || $layout === 'left-image' || $layout === 'right-image') {
    $container_styles[] = 'display: grid';
    $container_styles[] = 'grid-template-columns: 1fr';
    $container_styles[] = 'gap: var(--hph-gap-2xl)';
    $container_styles[] = 'align-items: center';
}

if (in_array($layout, array('grid', 'grid-cards', 'icon-features'))) {
    $container_styles[] = 'display: grid';
    $container_styles[] = 'gap: var(--hph-gap-xl)';
    
    switch ($columns) {
        case 2:
            $container_styles[] = 'grid-template-columns: repeat(auto-fit, minmax(300px, 1fr))';
            break;
        case 4:
            $container_styles[] = 'grid-template-columns: repeat(auto-fit, minmax(250px, 1fr))';
            break;
        case 3:
        default:
            $container_styles[] = 'grid-template-columns: repeat(auto-fit, minmax(280px, 1fr))';
            break;
    }
}

if (in_array($layout, array('stats', 'stats-row'))) {
    $container_styles[] = 'display: grid';
    $container_styles[] = 'gap: var(--hph-gap-lg)';
    $container_styles[] = 'justify-content: center';
    
    // Determine grid layout based on number of stats
    $stat_count = is_array($stats) ? count($stats) : 0;
    
    if ($stat_count === 4) {
        // For 4 items, create a centered 2x2 grid on larger screens
        $container_styles[] = 'grid-template-columns: repeat(2, minmax(200px, 300px))';
        $container_styles[] = 'max-width: 800px'; // constrain width for centering
        $container_styles[] = 'margin-left: auto';
        $container_styles[] = 'margin-right: auto';
    } else {
        // For other counts, use auto-fit
        $container_styles[] = 'grid-template-columns: repeat(auto-fit, minmax(200px, 1fr))';
    }
}

// Text alignment
switch ($alignment) {
    case 'left':
        $container_styles[] = 'text-align: left';
        break;
    case 'right':
        $container_styles[] = 'text-align: right';
        break;
    case 'center':
    default:
        $container_styles[] = 'text-align: center';
        break;
}

// For desktop, directly add the two-column layout to container styles
if ($layout === 'two-column' || $layout === 'left-image' || $layout === 'right-image') {
    // Add a CSS class for responsive behavior
    $container_styles[] = 'grid-template-columns: 1fr'; // mobile first
}

// For desktop, directly add the two-column layout to container styles
if ($layout === 'two-column' || $layout === 'left-image' || $layout === 'right-image') {
    // Add a CSS class for responsive behavior
    $container_styles[] = 'grid-template-columns: 1fr'; // mobile first
}
?>

<section 
    class="hph-content-section"
    <?php if ($section_id): ?>id="<?php echo esc_attr($section_id); ?>"<?php endif; ?>
    data-bg="<?php echo esc_attr($background); ?>"
    style="<?php echo implode('; ', $section_styles); ?>"
    data-animation="<?php echo $animation ? 'true' : 'false'; ?>"
>
    <style>
        /* Responsive grid layout for content sections */
        @media (min-width: 768px) {
            .hph-content-two-column {
                grid-template-columns: 1fr 1fr !important;
            }
        }
        
        /* Stats responsive layout */
        @media (min-width: 640px) {
            .hph-content-container[data-stats="4"] {
                grid-template-columns: repeat(2, minmax(200px, 300px)) !important;
            }
        }
        
        @media (min-width: 1024px) {
            .hph-content-container[data-stats="4"] {
                grid-template-columns: repeat(4, minmax(200px, 240px)) !important;
                max-width: 1200px !important;
            }
        }
        
        @media (max-width: 639px) {
            .hph-content-container[data-stats] {
                grid-template-columns: 1fr !important;
                max-width: 400px !important;
            }
        }
    </style>
    
    <div 
        class="hph-content-container <?php echo ($layout === 'two-column' || $layout === 'left-image' || $layout === 'right-image') ? 'hph-content-two-column' : ''; ?>" 
        style="<?php echo implode('; ', $container_styles); ?>"
        <?php if (in_array($layout, array('stats', 'stats-row')) && !empty($stats)): ?>data-stats="<?php echo count($stats); ?>"<?php endif; ?>
    >
        
        <?php if ($layout === 'left-image' && $image): ?>
        <!-- Left Image Layout -->
        <div style="order: 1; <?php echo $animation ? 'animation: fadeInUp 0.8s ease-out;' : ''; ?>">
            <img 
                src="<?php echo esc_url($image['url']); ?>" 
                alt="<?php echo esc_attr($image['alt'] ?? ''); ?>"
                style="width: 100%; height: auto; border-radius: var(--hph-radius-lg); box-shadow: 0 10px 30px rgba(0,0,0,0.1);"
                loading="lazy"
            >
            <?php if (!empty($image['caption'])): ?>
            <p style="font-size: var(--hph-text-sm); color: var(--hph-gray-600); margin-top: var(--hph-margin-sm); font-style: italic;">
                <?php echo esc_html($image['caption']); ?>
            </p>
            <?php endif; ?>
        </div>
        
        <div style="order: 2; <?php echo $animation ? 'animation: fadeInUp 0.8s ease-out 0.1s; opacity: 0; animation-fill-mode: forwards;' : ''; ?>">
        
        <?php elseif ($layout === 'right-image' && $image): ?>
        <!-- Right Image Layout -->
        <div style="order: 1; <?php echo $animation ? 'animation: fadeInUp 0.8s ease-out;' : ''; ?>">
        
        <?php elseif ($layout === 'centered' || $layout === 'two-column'): ?>
        <!-- Centered or Two-Column Layout -->
        <div style="<?php echo $layout === 'two-column' ? 'grid-column: span 2;' : ''; ?> <?php echo $animation ? 'animation: fadeInUp 0.8s ease-out;' : ''; ?>">
        
        <?php endif; ?>
        
        <?php if ($layout !== 'grid' && $layout !== 'stats'): ?>
        
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
            <<?php echo esc_attr($headline_tag); ?> style="margin: 0 0 var(--hph-margin-lg) 0; font-size: var(--hph-text-4xl); font-weight: var(--hph-font-bold); line-height: var(--hph-leading-tight);">
                <?php echo esc_html($headline); ?>
            </<?php echo esc_attr($headline_tag); ?>>
            <?php endif; ?>
            
            <?php if ($subheadline): ?>
            <!-- Subheadline -->
            <p style="margin: 0 0 var(--hph-margin-lg) 0; font-size: var(--hph-text-xl); font-weight: var(--hph-font-medium); opacity: 0.9;">
                <?php echo esc_html($subheadline); ?>
            </p>
            <?php endif; ?>
            
            <?php if ($content): ?>
            <!-- Content -->
            <div style="margin: 0 0 var(--hph-margin-2xl) 0; font-size: var(--hph-text-base); line-height: var(--hph-leading-relaxed);">
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
                        'box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1)'
                    );
                    
                    // Button size
                    switch($btn['size']) {
                        case 'sm':
                            $btn_styles[] = 'padding: var(--hph-padding-sm) var(--hph-padding-md)';
                            $btn_styles[] = 'font-size: var(--hph-text-sm)';
                            break;
                        case 'lg':
                            $btn_styles[] = 'padding: var(--hph-padding-md) var(--hph-padding-xl)';
                            $btn_styles[] = 'font-size: var(--hph-text-base)';
                            break;
                        case 'xl':
                            $btn_styles[] = 'padding: var(--hph-padding-lg) var(--hph-padding-2xl)';
                            $btn_styles[] = 'font-size: var(--hph-text-lg)';
                            break;
                        default:
                            $btn_styles[] = 'padding: var(--hph-padding-md) var(--hph-padding-lg)';
                            $btn_styles[] = 'font-size: var(--hph-text-base)';
                    }
                    
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
                        case 'white':
                            $btn_styles[] = 'background-color: var(--hph-white)';
                            $btn_styles[] = 'color: var(--hph-primary)';
                            $btn_styles[] = 'border: 2px solid var(--hph-white)';
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
                    <i class="<?php echo esc_attr($btn['icon']); ?>" style="margin-right: var(--hph-margin-sm);"></i>
                    <?php endif; ?>
                    <span><?php echo esc_html($btn['text']); ?></span>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        
        </div>
        
        <?php if ($layout === 'right-image' && $image): ?>
        <!-- Right Image -->
        <div style="order: 2; <?php echo $animation ? 'animation: fadeInUp 0.8s ease-out 0.1s; opacity: 0; animation-fill-mode: forwards;' : ''; ?>">
            <img 
                src="<?php echo esc_url($image['url']); ?>" 
                alt="<?php echo esc_attr($image['alt'] ?? ''); ?>"
                style="width: 100%; height: auto; border-radius: var(--hph-radius-lg); box-shadow: 0 10px 30px rgba(0,0,0,0.1);"
                loading="lazy"
            >
            <?php if (!empty($image['caption'])): ?>
            <p style="font-size: var(--hph-text-sm); color: var(--hph-gray-600); margin-top: var(--hph-margin-sm); font-style: italic;">
                <?php echo esc_html($image['caption']); ?>
            </p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php elseif ($layout === 'grid' && !empty($items)): ?>
        
        <!-- Grid Layout -->
        <?php if ($headline || $subheadline): ?>
        <div style="grid-column: 1 / -1; text-align: center; margin-bottom: var(--hph-margin-2xl); <?php echo $animation ? 'animation: fadeInUp 0.8s ease-out;' : ''; ?>">
            <?php if ($headline): ?>
            <<?php echo esc_attr($headline_tag); ?> style="margin: 0 0 var(--hph-margin-lg) 0; font-size: var(--hph-text-4xl); font-weight: var(--hph-font-bold);">
                <?php echo esc_html($headline); ?>
            </<?php echo esc_attr($headline_tag); ?>>
            <?php endif; ?>
            
            <?php if ($subheadline): ?>
            <p style="font-size: var(--hph-text-xl); font-weight: var(--hph-font-medium); opacity: 0.9;">
                <?php echo esc_html($subheadline); ?>
            </p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php foreach ($items as $index => $item): ?>
        <div style="background: var(--hph-white); border-radius: var(--hph-radius-lg); box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); padding: var(--hph-padding-xl); transition: all 300ms ease; <?php echo $animation ? 'animation: fadeInUp 0.8s ease-out ' . ($index * 0.1) . 's; opacity: 0; animation-fill-mode: forwards;' : ''; ?>"
             onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 15px rgba(0, 0, 0, 0.15)';"
             onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px rgba(0, 0, 0, 0.1)';">
            <?php if (!empty($item['icon'])): ?>
            <div style="margin-bottom: var(--hph-margin-lg);">
                <i class="<?php echo esc_attr($item['icon']); ?>" style="font-size: var(--hph-text-3xl); color: var(--hph-primary);"></i>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($item['title'])): ?>
            <h3 style="margin: 0 0 var(--hph-margin-md) 0; font-size: var(--hph-text-xl); font-weight: var(--hph-font-semibold);">
                <?php echo esc_html($item['title']); ?>
            </h3>
            <?php endif; ?>
            
            <?php if (!empty($item['content'])): ?>
            <p style="color: var(--hph-gray-600); line-height: var(--hph-leading-relaxed);">
                <?php echo wp_kses_post($item['content']); ?>
            </p>
            <?php endif; ?>
            
            <?php if (!empty($item['link'])): ?>
            <a href="<?php echo esc_url($item['link']['url']); ?>" 
               style="display: inline-flex; align-items: center; margin-top: var(--hph-margin-lg); color: var(--hph-primary); font-weight: var(--hph-font-medium); text-decoration: none;"
               onmouseover="this.style.textDecoration='underline'"
               onmouseout="this.style.textDecoration='none'">
                <?php echo esc_html($item['link']['text'] ?? 'Learn More'); ?>
                <i class="fas fa-arrow-right" style="margin-left: var(--hph-margin-sm); font-size: var(--hph-text-sm);"></i>
            </a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        
        <?php elseif ($layout === 'stats' && !empty($stats)): ?>
        
        <!-- Stats Layout -->
        <?php if ($headline || $subheadline): ?>
        <div style="grid-column: 1 / -1; text-align: center; margin-bottom: var(--hph-margin-2xl); <?php echo $animation ? 'animation: fadeInUp 0.8s ease-out;' : ''; ?>">
            <?php if ($headline): ?>
            <<?php echo esc_attr($headline_tag); ?> style="margin: 0 0 var(--hph-margin-lg) 0; font-size: var(--hph-text-4xl); font-weight: var(--hph-font-bold);">
                <?php echo esc_html($headline); ?>
            </<?php echo esc_attr($headline_tag); ?>>
            <?php endif; ?>
            
            <?php if ($subheadline): ?>
            <p style="font-size: var(--hph-text-xl); font-weight: var(--hph-font-medium); opacity: 0.9;">
                <?php echo esc_html($subheadline); ?>
            </p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php foreach ($stats as $index => $stat): ?>
        <div style="text-align: center; padding: var(--hph-padding-xl); background: var(--hph-white); border-radius: var(--hph-radius-lg); box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); transition: all 300ms ease; <?php echo $animation ? 'animation: fadeInUp 0.8s ease-out ' . ($index * 0.1) . 's; opacity: 0; animation-fill-mode: forwards;' : ''; ?>"
             onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 15px rgba(0, 0, 0, 0.15)';"
             onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px rgba(0, 0, 0, 0.1)';">
            <div style="font-size: var(--hph-text-4xl); font-weight: var(--hph-font-bold); color: var(--hph-primary); margin-bottom: var(--hph-margin-sm); line-height: 1.2; display: flex; align-items: center; justify-content: center; gap: 0.2rem;">
                <span style="font-size: var(--hph-text-4xl);"><?php echo esc_html($stat['number']); ?></span>
                <?php if (!empty($stat['suffix'])): ?>
                <span style="font-size: var(--hph-text-xl); color: var(--hph-gray-600); font-weight: var(--hph-font-medium);"><?php echo esc_html($stat['suffix']); ?></span>
                <?php endif; ?>
            </div>
            <div style="font-size: var(--hph-text-lg); font-weight: var(--hph-font-semibold); color: var(--hph-gray-900); line-height: var(--hph-leading-tight); margin-bottom: var(--hph-margin-xs); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                <?php echo esc_html($stat['label']); ?>
            </div>
            <?php if (!empty($stat['description'])): ?>
            <p style="margin: 0; font-size: var(--hph-text-sm); color: var(--hph-gray-600); line-height: var(--hph-leading-relaxed);">
                <?php echo esc_html($stat['description']); ?>
            </p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        
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
</style>
<?php endif; ?>