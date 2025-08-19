<?php
/**
 * Template part for displaying agent cards
 *
 * @package HappyPlaceTheme
 */

$agent_data = hpt_get_agent(get_the_ID());
if (!$agent_data) return;

$agent_listings = hpt_get_agent_listings(get_the_ID(), 3);
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('card agent-card hover-lift'); ?>>
    
    <div class="card-header text-center">
        <?php if (!empty($agent_data['photo'])) : ?>
            <div class="agent-photo">
                <img src="<?php echo esc_url($agent_data['photo']['url']); ?>" 
                     alt="<?php echo esc_attr($agent_data['name']); ?>" 
                     class="avatar avatar-lg rounded-full mx-auto mb-4">
            </div>
        <?php endif; ?>
        
        <div class="agent-info">
            <h3 class="card-title">
                <a href="<?php the_permalink(); ?>" class="link-primary">
                    <?php echo esc_html($agent_data['name'] ?? get_the_title()); ?>
                </a>
            </h3>
            
            <?php if (!empty($agent_data['title'])) : ?>
                <p class="text-muted mb-3"><?php echo esc_html($agent_data['title']); ?></p>
            <?php endif; ?>
            
            <?php if (!empty($agent_data['specialties']) && is_array($agent_data['specialties'])) : ?>
                <div class="agent-specialties flex flex-wrap justify-center gap-2">
                    <?php foreach (array_slice($agent_data['specialties'], 0, 2) as $specialty) : ?>
                        <span class="badge badge-primary"><?php echo esc_html($specialty); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    
    <div class="card-body">
        
        <?php if (!empty($agent_data['bio'])) : ?>
            <div class="card-excerpt text-sm text-muted mb-4 line-clamp-3">
                <?php echo wp_trim_words($agent_data['bio'], 25, '...'); ?>
            </div>
        <?php endif; ?>
        
        <div class="agent-stats grid grid-cols-3 gap-4 mb-4">
            <?php if (!empty($agent_data['years_experience'])) : ?>
                <div class="stat text-center">
                    <div class="stat-number text-primary font-bold"><?php echo esc_html($agent_data['years_experience']); ?>+</div>
                    <div class="stat-label text-xs text-muted"><?php esc_html_e('Years', 'happy-place-theme'); ?></div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($agent_data['total_sales'])) : ?>
                <div class="stat text-center">
                    <div class="stat-number text-primary font-bold"><?php echo esc_html($agent_data['total_sales']); ?>+</div>
                    <div class="stat-label text-xs text-muted"><?php esc_html_e('Sold', 'happy-place-theme'); ?></div>
                </div>
            <?php endif; ?>
            
            <?php 
            $active_listings_count = count(hpt_get_agent_listings(get_the_ID()));
            if ($active_listings_count > 0) :
            ?>
                <div class="stat text-center">
                    <div class="stat-number text-primary font-bold"><?php echo esc_html($active_listings_count); ?></div>
                    <div class="stat-label text-xs text-muted"><?php esc_html_e('Active', 'happy-place-theme'); ?></div>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($agent_data['languages']) && is_array($agent_data['languages'])) : ?>
            <div class="agent-languages mb-4">
                <div class="text-sm font-medium mb-1"><?php esc_html_e('Languages:', 'happy-place-theme'); ?></div>
                <div class="text-sm text-muted"><?php echo esc_html(implode(', ', $agent_data['languages'])); ?></div>
            </div>
        <?php endif; ?>
        
        <div class="card-actions">
            <a href="<?php the_permalink(); ?>" class="btn btn-primary btn-block">
                <?php esc_html_e('View Profile', 'happy-place-theme'); ?>
            </a>
            
            <div class="contact-buttons grid grid-cols-2 gap-2 mt-3">
                <?php if (!empty($agent_data['phone'])) : ?>
                    <a href="tel:<?php echo esc_attr($agent_data['phone']); ?>" class="btn btn-outline btn-sm">
                        <i class="fas fa-phone mr-1"></i>
                        <?php esc_html_e('Call', 'happy-place-theme'); ?>
                    </a>
                <?php endif; ?>
                
                <?php if (!empty($agent_data['email'])) : ?>
                    <a href="mailto:<?php echo esc_attr($agent_data['email']); ?>" class="btn btn-outline btn-sm">
                        <i class="fas fa-envelope mr-1"></i>
                        <?php esc_html_e('Email', 'happy-place-theme'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
    
</article>


</style>
