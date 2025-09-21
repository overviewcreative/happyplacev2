<?php
/**
 * Staff Archive Template
 * 
 * @package HappyPlaceTheme
 */

get_header();

// Add body class for specific styling
add_filter('body_class', function($classes) {
    $classes[] = 'staff-archive-page';
    return $classes;
});

?>

<main class="hph-main staff-archive-main">
    
    <div class="hph-container">
        <div class="hph-content-wrapper">
            
            <!-- Archive Header -->
            <header class="archive-header">
                <h1 class="archive-title">
                    <i class="fas fa-users" aria-hidden="true"></i>
                    Our Staff
                </h1>
                <p class="archive-description">
                    Meet the dedicated professionals who support our team and ensure exceptional service.
                </p>
            </header>
            
            <?php if (have_posts()) : ?>
            
            <section class="staff-archive-section">
                <div class="team-grid staff-grid">
                    <?php while (have_posts()) : the_post(); ?>
                        <div class="team-member staff-member">
                            <?php 
                            get_template_part('template-parts/staff-card', null, [
                                'staff_id' => get_the_ID(),
                                'style' => 'archive-view'
                            ]); 
                            ?>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <?php
                // Pagination
                the_posts_pagination(array(
                    'mid_size'  => 2,
                    'prev_text' => __('← Previous', 'happy-place-theme'),
                    'next_text' => __('Next →', 'happy-place-theme'),
                ));
                ?>
            </section>
            
            <?php else : ?>
            
            <section class="no-staff-found">
                <div class="empty-state">
                    <i class="fas fa-users empty-state-icon" aria-hidden="true"></i>
                    <h3 class="empty-state-title">No Staff Members Found</h3>
                    <p class="empty-state-description">
                        Staff member information will be displayed here once it's added to the system.
                    </p>
                </div>
            </section>
            
            <?php endif; ?>
            
        </div>
    </div>
    
</main>

<?php get_footer(); ?>
