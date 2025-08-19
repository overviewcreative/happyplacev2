<?php
/**
 * The template for displaying open house archives
 *
 * @package HappyPlaceTheme
 */

get_header(); ?>

<div class="hero hero-secondary">
    <div class="container">
        <div class="hero-content">
            <h1 class="archive-title"><?php esc_html_e('Open Houses', 'happy-place-theme'); ?></h1>
            <p class="archive-description text-muted">
                <?php esc_html_e('Find upcoming open houses and schedule your visit', 'happy-place-theme'); ?>
            </p>
        </div>
    </div>
</div>

<main id="primary" class="site-main">
    <div class="container">
        
        <!-- Open House Filters -->
        <div class="openhouse-filters mb-8">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <input type="date" class="form-control" id="date-filter" value="<?php echo esc_attr(date('Y-m-d')); ?>">
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="time-filter">
                                <option value=""><?php esc_html_e('Any Time', 'happy-place-theme'); ?></option>
                                <option value="morning"><?php esc_html_e('Morning (9AM-12PM)', 'happy-place-theme'); ?></option>
                                <option value="afternoon"><?php esc_html_e('Afternoon (12PM-5PM)', 'happy-place-theme'); ?></option>
                                <option value="evening"><?php esc_html_e('Evening (5PM-8PM)', 'happy-place-theme'); ?></option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="city-filter">
                                <option value=""><?php esc_html_e('All Cities', 'happy-place-theme'); ?></option>
                                <!-- Populated via PHP or AJAX -->
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-primary w-100" id="apply-filters">
                                <i class="fas fa-search mr-2"></i>
                                <?php esc_html_e('Find Open Houses', 'happy-place-theme'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="content-area">
            
            <?php if (have_posts()) : ?>
                
                <div class="archive-meta mb-6">
                    <div class="results-count">
                        <?php
                        global $wp_query;
                        printf(
                            esc_html(_n('Found %d open house', 'Found %d open houses', $wp_query->found_posts, 'happy-place-theme')),
                            $wp_query->found_posts
                        );
                        ?>
                    </div>
                </div>
                
                <div class="openhouses-container">
                    <div class="openhouses-list space-y-6">
                        
                        <?php while (have_posts()) : the_post(); ?>
                            
                            <div class="openhouse-item">
                                <div class="card">
                                    <div class="row no-gutters">
                                        
                                        <?php if (has_post_thumbnail()) : ?>
                                            <div class="col-md-4">
                                                <div class="card-image">
                                                    <a href="<?php the_permalink(); ?>">
                                                        <?php the_post_thumbnail('medium_large', array('class' => 'img-responsive h-full object-cover')); ?>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                        <?php else : ?>
                                            <div class="col-12">
                                        <?php endif; ?>
                                        
                                            <div class="card-body">
                                                
                                                <!-- Open House Status -->
                                                <div class="openhouse-status mb-3">
                                                    <?php 
                                                    $openhouse_date = get_field('open_house_date');
                                                    $openhouse_status = 'upcoming'; // Default status
                                                    
                                                    if ($openhouse_date) {
                                                        $current_time = current_time('timestamp');
                                                        $oh_time = strtotime($openhouse_date);
                                                        
                                                        if ($oh_time < $current_time) {
                                                            $openhouse_status = 'completed';
                                                        } elseif (date('Y-m-d', $oh_time) === date('Y-m-d', $current_time)) {
                                                            $openhouse_status = 'today';
                                                        }
                                                    }
                                                    ?>
                                                    
                                                    <span class="badge badge-<?php echo esc_attr($openhouse_status === 'today' ? 'success' : ($openhouse_status === 'completed' ? 'secondary' : 'primary')); ?>">
                                                        <?php 
                                                        switch($openhouse_status) {
                                                            case 'today':
                                                                esc_html_e('Open House Today!', 'happy-place-theme');
                                                                break;
                                                            case 'completed':
                                                                esc_html_e('Completed', 'happy-place-theme');
                                                                break;
                                                            default:
                                                                esc_html_e('Upcoming', 'happy-place-theme');
                                                        }
                                                        ?>
                                                    </span>
                                                </div>
                                                
                                                <h3 class="card-title">
                                                    <a href="<?php the_permalink(); ?>" class="link-dark">
                                                        <?php the_title(); ?>
                                                    </a>
                                                </h3>
                                                
                                                <!-- Date and Time -->
                                                <?php if ($openhouse_date) : ?>
                                                    <div class="openhouse-datetime mb-3">
                                                        <div class="d-flex align-items-center text-muted">
                                                            <i class="fas fa-calendar-alt mr-2"></i>
                                                            <span><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($openhouse_date))); ?></span>
                                                        </div>
                                                        
                                                        <?php 
                                                        $start_time = get_field('start_time');
                                                        $end_time = get_field('end_time');
                                                        if ($start_time || $end_time) : ?>
                                                            <div class="d-flex align-items-center text-muted mt-1">
                                                                <i class="fas fa-clock mr-2"></i>
                                                                <span>
                                                                    <?php 
                                                                    if ($start_time) echo esc_html($start_time);
                                                                    if ($start_time && $end_time) echo ' - ';
                                                                    if ($end_time) echo esc_html($end_time);
                                                                    ?>
                                                                </span>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <!-- Property Details -->
                                                <?php 
                                                $related_listing = get_field('related_listing');
                                                if ($related_listing) : ?>
                                                    <div class="property-details mb-3">
                                                        <div class="d-flex align-items-center text-muted">
                                                            <i class="fas fa-map-marker-alt mr-2"></i>
                                                            <span><?php echo esc_html(get_the_title($related_listing)); ?></span>
                                                        </div>
                                                        
                                                        <?php if (function_exists('hpt_get_listing_price')) : ?>
                                                            <div class="property-price mt-1">
                                                                <strong class="text-primary">
                                                                    <?php echo esc_html(hpt_get_listing_price($related_listing)); ?>
                                                                </strong>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <!-- Excerpt -->
                                                <div class="card-text">
                                                    <?php the_excerpt(); ?>
                                                </div>
                                                
                                                <!-- Actions -->
                                                <div class="card-actions mt-4">
                                                    <a href="<?php the_permalink(); ?>" class="btn btn-primary">
                                                        <?php esc_html_e('View Details', 'happy-place-theme'); ?>
                                                        <i class="fas fa-arrow-right ml-2"></i>
                                                    </a>
                                                    
                                                    <?php if ($related_listing) : ?>
                                                        <a href="<?php echo esc_url(get_permalink($related_listing)); ?>" class="btn btn-outline ml-2">
                                                            <?php esc_html_e('View Property', 'happy-place-theme'); ?>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                                
                                            </div>
                                            
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                            
                        <?php endwhile; ?>
                        
                    </div>
                </div>
                
                <!-- Pagination -->
                <div class="pagination-wrapper mt-8">
                    <?php
                    the_posts_pagination(array(
                        'mid_size'  => 2,
                        'prev_text' => '<i class="fas fa-chevron-left"></i> ' . __('Previous', 'happy-place-theme'),
                        'next_text' => __('Next', 'happy-place-theme') . ' <i class="fas fa-chevron-right"></i>',
                    ));
                    ?>
                </div>
                
            <?php else : ?>
                
                <div class="no-openhouses text-center py-12">
                    <div class="no-openhouses-icon text-6xl text-muted mb-6">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                    <h2 class="no-openhouses-title text-2xl font-bold mb-4">
                        <?php esc_html_e('No Open Houses Scheduled', 'happy-place-theme'); ?>
                    </h2>
                    <p class="no-openhouses-message text-muted mb-8">
                        <?php esc_html_e('There are no open houses scheduled at this time. Check back soon or browse our available properties.', 'happy-place-theme'); ?>
                    </p>
                    
                    <div class="no-openhouses-actions">
                        <?php if (post_type_exists('listing')) : ?>
                            <a href="<?php echo esc_url(get_post_type_archive_link('listing')); ?>" class="btn btn-primary mr-4">
                                <i class="fas fa-home mr-2"></i>
                                <?php esc_html_e('Browse Properties', 'happy-place-theme'); ?>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (post_type_exists('agent')) : ?>
                            <a href="<?php echo esc_url(get_post_type_archive_link('agent')); ?>" class="btn btn-outline">
                                <i class="fas fa-user-tie mr-2"></i>
                                <?php esc_html_e('Contact Our Agents', 'happy-place-theme'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
            <?php endif; ?>
            
        </div>
        
    </div>
</main>

<?php get_footer(); ?>
