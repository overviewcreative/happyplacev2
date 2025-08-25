<?php
/**
 * Template part for displaying agent cards with integrated service stats
 *
 * @package HappyPlaceTheme
 */

// Get agent data from bridge function
$agent_data = hpt_get_agent(get_the_ID());
if (!$agent_data) return;

// Get agent service instance and enhanced stats
$agent_service_stats = null;
if (class_exists('HappyPlace\\Services\\AgentService')) {
    $agent_service = new \HappyPlace\Services\AgentService();
    $agent_service->init();
    
    // Get user ID from agent post
    $user_id = get_post_meta(get_the_ID(), 'agent_user_id', true);
    if ($user_id) {
        $enhanced_agent = $agent_service->get_agent_by_user($user_id);
        if ($enhanced_agent) {
            $agent_service_stats = $enhanced_agent['stats'];
        }
    }
}

$agent_listings = hpt_get_agent_listings(get_the_ID(), 3);
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('hph-card hph-agent-card hph-hover-lift'); ?>>
    
    <div class="hph-card-header hph-text-center">
        <?php if (!empty($agent_data['photo'])) : ?>
            <div class="hph-agent-photo">
                <img src="<?php echo esc_url($agent_data['photo']['url']); ?>" 
                     alt="<?php echo esc_attr($agent_data['name']); ?>" 
                     class="hph-avatar hph-avatar-lg hph-rounded-full hph-mx-auto hph-mb-4">
            </div>
        <?php endif; ?>
        
        <div class="hph-agent-info">
            <h3 class="hph-card-title">
                <a href="<?php the_permalink(); ?>" class="hph-link-primary">
                    <?php echo esc_html($agent_data['name'] ?? get_the_title()); ?>
                </a>
            </h3>
            
            <?php if (!empty($agent_data['title'])) : ?>
                <p class="hph-text-muted hph-mb-3"><?php echo esc_html($agent_data['title']); ?></p>
            <?php endif; ?>
            
            <?php if (!empty($agent_data['specialties']) && is_array($agent_data['specialties'])) : ?>
                <div class="hph-agent-specialties hph-flex hph-flex-wrap hph-justify-center hph-gap-2">
                    <?php foreach (array_slice($agent_data['specialties'], 0, 2) as $specialty) : ?>
                        <span class="hph-badge hph-badge-primary"><?php echo esc_html($specialty); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="hph-card-body">
        
        <?php if (!empty($agent_data['bio'])) : ?>
            <div class="hph-card-excerpt hph-text-sm hph-text-muted hph-mb-4 hph-line-clamp-3">
                <?php echo wp_trim_words($agent_data['bio'], 25, '...'); ?>
            </div>
        <?php endif; ?>
        
        <div class="hph-agent-stats hph-grid hph-grid-cols-3 hph-gap-4 hph-mb-4">
            <?php if ($agent_service_stats) : ?>
                <!-- Enhanced stats from Agent Service -->
                <div class="hph-stat hph-text-center">
                    <div class="hph-stat-number hph-text-primary hph-font-bold"><?php echo esc_html($agent_service_stats['active_listings'] ?? 0); ?></div>
                    <div class="hph-stat-label hph-text-xs hph-text-muted"><?php esc_html_e('Active', 'happy-place-theme'); ?></div>
                </div>
                
                <div class="hph-stat hph-text-center">
                    <div class="hph-stat-number hph-text-primary hph-font-bold"><?php echo esc_html($agent_service_stats['sold_listings'] ?? 0); ?></div>
                    <div class="hph-stat-label hph-text-xs hph-text-muted"><?php esc_html_e('Sold', 'happy-place-theme'); ?></div>
                </div>
                
                <div class="hph-stat hph-text-center">
                    <div class="hph-stat-number hph-text-primary hph-font-bold"><?php echo esc_html($agent_service_stats['conversion_rate'] ?? 0); ?>%</div>
                    <div class="hph-stat-label hph-text-xs hph-text-muted"><?php esc_html_e('Success', 'happy-place-theme'); ?></div>
                </div>
            <?php else : ?>
                <!-- Fallback to legacy stats -->
                <?php if (!empty($agent_data['years_experience'])) : ?>
                    <div class="hph-stat hph-text-center">
                        <div class="hph-stat-number hph-text-primary hph-font-bold"><?php echo esc_html($agent_data['years_experience']); ?>+</div>
                        <div class="hph-stat-label hph-text-xs hph-text-muted"><?php esc_html_e('Years', 'happy-place-theme'); ?></div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($agent_data['total_sales'])) : ?>
                    <div class="hph-stat hph-text-center">
                        <div class="hph-stat-number hph-text-primary hph-font-bold"><?php echo esc_html($agent_data['total_sales']); ?>+</div>
                        <div class="hph-stat-label hph-text-xs hph-text-muted"><?php esc_html_e('Sold', 'happy-place-theme'); ?></div>
                    </div>
                <?php endif; ?>
                
                <?php 
                $active_listings_count = count(hpt_get_agent_listings(get_the_ID()));
                if ($active_listings_count > 0) :
                ?>
                    <div class="hph-stat hph-text-center">
                        <div class="hph-stat-number hph-text-primary hph-font-bold"><?php echo esc_html($active_listings_count); ?></div>
                        <div class="hph-stat-label hph-text-xs hph-text-muted"><?php esc_html_e('Active', 'happy-place-theme'); ?></div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <!-- Enhanced performance metrics if available -->
        <?php if ($agent_service_stats && ($agent_service_stats['ytd_volume'] > 0 || $agent_service_stats['avg_days_on_market'] > 0)) : ?>
            <div class="hph-agent-performance hph-grid hph-grid-cols-2 hph-gap-4 hph-mb-4 hph-p-3 hph-bg-light hph-rounded">
                <?php if ($agent_service_stats['ytd_volume'] > 0) : ?>
                    <div class="hph-performance-stat hph-text-center">
                        <div class="hph-stat-number hph-text-success hph-font-bold">$<?php echo number_format($agent_service_stats['ytd_volume'] / 1000000, 1); ?>M</div>
                        <div class="hph-stat-label hph-text-xs hph-text-muted"><?php esc_html_e('YTD Volume', 'happy-place-theme'); ?></div>
                    </div>
                <?php endif; ?>
                
                <?php if ($agent_service_stats['avg_days_on_market'] > 0) : ?>
                    <div class="hph-performance-stat hph-text-center">
                        <div class="hph-stat-number hph-text-info hph-font-bold"><?php echo esc_html($agent_service_stats['avg_days_on_market']); ?></div>
                        <div class="hph-stat-label hph-text-xs hph-text-muted"><?php esc_html_e('Avg DOM', 'happy-place-theme'); ?></div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($agent_data['languages']) && is_array($agent_data['languages'])) : ?>
            <div class="hph-agent-languages hph-mb-4">
                <div class="hph-text-sm hph-font-medium hph-mb-1"><?php esc_html_e('Languages:', 'happy-place-theme'); ?></div>
                <div class="hph-text-sm hph-text-muted"><?php echo esc_html(implode(', ', $agent_data['languages'])); ?></div>
            </div>
        <?php endif; ?>
        
        <div class="hph-card-actions">
            <a href="<?php the_permalink(); ?>" class="hph-btn hph-btn-primary hph-btn-block">
                <?php esc_html_e('View Profile', 'happy-place-theme'); ?>
            </a>
            
            <div class="hph-contact-buttons hph-grid hph-grid-cols-2 hph-gap-2 hph-mt-3">
                <?php if (!empty($agent_data['phone'])) : ?>
                    <a href="tel:<?php echo esc_attr($agent_data['phone']); ?>" class="hph-btn hph-btn-outline hph-btn-sm">
                        <i class="fas fa-phone hph-mr-1"></i>
                        <?php esc_html_e('Call', 'happy-place-theme'); ?>
                    </a>
                <?php endif; ?>
                
                <?php if (!empty($agent_data['email'])) : ?>
                    <a href="mailto:<?php echo esc_attr($agent_data['email']); ?>" class="hph-btn hph-btn-outline hph-btn-sm">
                        <i class="fas fa-envelope hph-mr-1"></i>
                        <?php esc_html_e('Email', 'happy-place-theme'); ?>
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Lead form integration -->
            <div class="hph-agent-contact-form hph-mt-4">
                <?php 
                $user_id = get_post_meta(get_the_ID(), 'agent_user_id', true);
                if ($user_id) :
                    echo do_shortcode('[hp_lead_form title="Quick Message" agent_id="' . $user_id . '" show_message="true" show_phone="false" button_text="Send Message" class="hph-compact-form"]');
                endif; 
                ?>
            </div>
        </div>
        
    </div>
    
</article>
