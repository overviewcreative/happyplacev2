<?php
/**
 * Dashboard Listings Template
 * Uses dynamic ACF form system
 */

// Get form handler
use HappyPlace\Forms\Listing_Form_Handler;
$form_handler = new Listing_Form_Handler();

// Get listing ID if editing
$listing_id = isset($_GET['listing_id']) ? intval($_GET['listing_id']) : null;
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';

?>

<div class="hph-dashboard-section hph-dashboard-listings">
    
    <?php if ($action === 'edit' || $action === 'new'): ?>
        
        <div class="hph-dashboard-header">
            <h2><?php echo $listing_id ? 'Edit Listing' : 'Add New Listing'; ?></h2>
            <a href="<?php echo remove_query_arg(['action', 'listing_id']); ?>" class="hph-btn hph-btn-secondary">
                <span class="dashicons dashicons-arrow-left-alt"></span> Back to Listings
            </a>
        </div>
        
        <div class="hph-dashboard-content">
            <?php $form_handler->render_form($listing_id); ?>
        </div>
        
    <?php else: ?>
        
        <div class="hph-dashboard-header">
            <h2>My Listings</h2>
            <a href="<?php echo add_query_arg('action', 'new'); ?>" class="hph-btn hph-btn-primary">
                <span class="dashicons dashicons-plus-alt"></span> Add New Listing
            </a>
        </div>
        
        <div class="hph-dashboard-content">
            <?php
            // Get user's listings
            $args = [
                'post_type' => 'listing',
                'author' => get_current_user_id(),
                'posts_per_page' => 20,
                'orderby' => 'date',
                'order' => 'DESC'
            ];
            
            $listings = new WP_Query($args);
            
            if ($listings->have_posts()): ?>
                <div class="hph-listing-grid">
                    <?php while ($listings->have_posts()): $listings->the_post(); ?>
                        <div class="hph-listing-card">
                            <?php 
                            get_template_part('template-parts/components/listing-card', null, [
                                'listing_id' => get_the_ID(),
                                'show_actions' => true
                            ]); 
                            ?>
                            <div class="listing-actions">
                                <a href="<?php echo add_query_arg(['action' => 'edit', 'listing_id' => get_the_ID()]); ?>" 
                                   class="hph-btn hph-btn-sm hph-btn-primary">
                                    Edit
                                </a>
                                <a href="<?php echo get_permalink(); ?>" 
                                   class="hph-btn hph-btn-sm hph-btn-secondary" 
                                   target="_blank">
                                    View
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                <?php wp_reset_postdata(); ?>
            <?php else: ?>
                <div class="hph-empty-state">
                    <h3>No listings yet</h3>
                    <p>Create your first listing to get started.</p>
                    <a href="<?php echo add_query_arg('action', 'new'); ?>" class="hph-btn hph-btn-primary">
                        Add Your First Listing
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
    <?php endif; ?>
    
</div>