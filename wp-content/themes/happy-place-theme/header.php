<?php
/**
 * Header Template
 * 
 * The header for the Happy Place theme
 *
 * @package HappyPlaceTheme
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    
    <!-- Navigation Header -->
    <header id="masthead" class="site-header" role="banner">
        <nav class="navbar navbar-light navbar-expand-lg" 
             style="background: var(--hph-white); box-shadow: var(--hph-shadow-md); position: sticky; top: 0; z-index: 1000;">
            <div class="section-container">
                <div class="navbar-content" 
                     style="display: flex; align-items: center; justify-content: space-between; padding: var(--hph-space-md) 0;">
                    
                    <!-- Brand/Logo -->
                    <div class="navbar-brand">
                        <?php if (has_custom_logo()) : ?>
                            <div class="custom-logo-wrapper" style="height: 3rem; width: auto;">
                                <?php the_custom_logo(); ?>
                            </div>
                        <?php else : ?>
                            <a href="<?php echo esc_url(home_url('/')); ?>" 
                               class="brand-link"
                               style="font-size: var(--hph-text-2xl); font-weight: 700; color: var(--hph-primary); text-decoration: none; transition: var(--hph-transition-fast);"
                               rel="home"
                               onmouseover="this.style.color='var(--hph-primary-dark)'"
                               onmouseout="this.style.color='var(--hph-primary)'">
                                <?php bloginfo('name'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Primary Navigation -->
                    <div class="navbar-nav-wrapper" style="display: none; @media (min-width: 1024px) { display: block; }">
                        <?php
                        wp_nav_menu(array(
                            'theme_location' => 'primary',
                            'menu_id'        => 'primary-menu',
                            'menu_class'     => 'primary-nav',
                            'container'      => false,
                            'fallback_cb'    => 'hp_fallback_menu',
                            'items_wrap'     => '<ul id="%1$s" class="%2$s" style="display: flex; align-items: center; gap: var(--hph-space-2xl); list-style: none; margin: 0; padding: 0;">%3$s</ul>',
                        ));
                        ?>
                    </div>
                    
                    <!-- Mobile Menu Toggle -->
                    <button class="mobile-menu-toggle" 
                            style="display: none; @media (max-width: 1023px) { display: flex; } flex-direction: column; align-items: center; justify-content: center; width: 2.5rem; height: 2.5rem; background: none; border: none; cursor: pointer; gap: 0.25rem;"
                            aria-controls="primary-menu" 
                            aria-expanded="false"
                            onclick="toggleMobileMenu()">
                        <span class="menu-line" style="width: 1.5rem; height: 2px; background: var(--hph-gray-700); transition: var(--hph-transition-fast);"></span>
                        <span class="menu-line" style="width: 1.5rem; height: 2px; background: var(--hph-gray-700); transition: var(--hph-transition-fast);"></span>
                        <span class="menu-line" style="width: 1.5rem; height: 2px; background: var(--hph-gray-700); transition: var(--hph-transition-fast);"></span>
                    </button>
                    
                </div>
            </div>
        </nav>
    </header><!-- #masthead -->

<?php
/**
 * Fallback menu when no menu is assigned - uses framework variables
 */
function hp_fallback_menu() {
    ?>
    <ul id="fallback-menu" class="primary-nav" 
        style="display: flex; align-items: center; gap: var(--hph-space-2xl); list-style: none; margin: 0; padding: 0;">
        <li>
            <a href="<?php echo esc_url(home_url('/')); ?>" 
               class="nav-link"
               style="color: var(--hph-gray-700); font-weight: 500; text-decoration: none; transition: var(--hph-transition-fast);"
               onmouseover="this.style.color='var(--hph-primary)'"
               onmouseout="this.style.color='var(--hph-gray-700)'">
               <?php esc_html_e('Home', 'happy-place-theme'); ?>
            </a>
        </li>
        
        <?php if (post_type_exists('listing')) : ?>
            <li>
                <a href="<?php echo esc_url(get_post_type_archive_link('listing')); ?>" 
                   class="nav-link"
                   style="color: var(--hph-gray-700); font-weight: 500; text-decoration: none; transition: var(--hph-transition-fast);"
                   onmouseover="this.style.color='var(--hph-primary)'"
                   onmouseout="this.style.color='var(--hph-gray-700)'">
                   <?php esc_html_e('Properties', 'happy-place-theme'); ?>
                </a>
            </li>
        <?php endif; ?>
        
        <?php if (post_type_exists('agent')) : ?>
            <li>
                <a href="<?php echo esc_url(get_post_type_archive_link('agent')); ?>" 
                   class="nav-link"
                   style="color: var(--hph-gray-700); font-weight: 500; text-decoration: none; transition: var(--hph-transition-fast);"
                   onmouseover="this.style.color='var(--hph-primary)'"
                   onmouseout="this.style.color='var(--hph-gray-700)'">
                   <?php esc_html_e('Agents', 'happy-place-theme'); ?>
                </a>
            </li>
        <?php endif; ?>
        
        <?php if (post_type_exists('community')) : ?>
            <li>
                <a href="<?php echo esc_url(get_post_type_archive_link('community')); ?>" 
                   class="nav-link"
                   style="color: var(--hph-gray-700); font-weight: 500; text-decoration: none; transition: var(--hph-transition-fast);"
                   onmouseover="this.style.color='var(--hph-primary)'"
                   onmouseout="this.style.color='var(--hph-gray-700)'">
                   <?php esc_html_e('Communities', 'happy-place-theme'); ?>
                </a>
            </li>
        <?php endif; ?>
        
        <?php if (post_type_exists('open-house')) : ?>
            <li>
                <a href="<?php echo esc_url(get_post_type_archive_link('open-house')); ?>" 
                   class="nav-link"
                   style="color: var(--hph-gray-700); font-weight: 500; text-decoration: none; transition: var(--hph-transition-fast);"
                   onmouseover="this.style.color='var(--hph-primary)'"
                   onmouseout="this.style.color='var(--hph-gray-700)'">
                   <?php esc_html_e('Open Houses', 'happy-place-theme'); ?>
                </a>
            </li>
        <?php endif; ?>
        
        <li>
            <a href="<?php echo esc_url(home_url('/contact')); ?>" 
               class="btn btn-primary btn-sm"
               style="background: var(--hph-primary); color: var(--hph-white); padding: var(--hph-space-sm) var(--hph-space-lg); border-radius: var(--hph-radius-md); text-decoration: none; font-weight: 600; transition: var(--hph-transition-fast);"
               onmouseover="this.style.background='var(--hph-primary-dark)'"
               onmouseout="this.style.background='var(--hph-primary)'">
               <?php esc_html_e('Contact', 'happy-place-theme'); ?>
            </a>
        </li>
    </ul>
    
    <script>
    function toggleMobileMenu() {
        const button = document.querySelector('.mobile-menu-toggle');
        const menu = document.querySelector('.primary-nav, #fallback-menu');
        const isExpanded = button.getAttribute('aria-expanded') === 'true';
        
        button.setAttribute('aria-expanded', !isExpanded);
        // Add mobile menu toggle functionality here
        console.log('Mobile menu toggled:', !isExpanded);
    }
    </script>
    <?php
}
?>
