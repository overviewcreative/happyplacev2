<?php
/**
 * Single Agent Template - Component-Based Architecture
 * 
 * Uses the new component system with agent bridge functions for data access.
 * Features agent profile, stats, listings, testimonials, and contact functionality.
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Get agent ID and verify it exists
$agent_id = get_the_ID();
if (!$agent_id || get_post_type($agent_id) !== 'agent') {
    hph_component('content-none');
    get_footer();
    return;
}

// Get agent data using bridge functions
$agent_data = hpt_get_agent($agent_id);
if (!$agent_data) {
    hph_component('content-none');
    get_footer();
    return;
}

// Check if agent profile is viewable
if (!current_user_can('read_post', $agent_id)) {
    hph_component('content-none');
    get_footer();
    return;
}

// Get agent's listings
$agent_listings = hph_get_agent_listings($agent_id, 6);

// Prepare single layout arguments
$single_args = [
    'post_type' => 'agent',
    'post_id' => $agent_id,
    'title' => $agent_data['name'],
    'data' => $agent_data,
    'show_sidebar' => false, // Agent profiles typically don't need sidebar
    'layout' => 'full-width'
];
?>

<div class="hph-page hph-single-page" data-post-type="agent" data-post-id="<?php echo esc_attr($agent_id); ?>">
    
    <!-- Agent Hero Section -->
    <section class="hph-py-2xl hph-bg-gray-50">
        <div class="hph-container">
            <div class="hph-grid hph-grid-cols-1 md:hph-grid-cols-2 hph-gap-lg hph-items-center">
                
                <!-- Agent Photo -->
                <div class="hph-order-2 md:hph-order-1">
                    <?php if (!empty($agent_data['photo'])): ?>
                        <img src="<?php echo esc_url($agent_data['photo']); ?>" 
                             alt="<?php echo esc_attr($agent_data['name']); ?>" 
                             class="hph-w-full hph-h-auto hph-rounded-lg hph-shadow-lg">
                    <?php else: ?>
                        <div class="hph-w-full aspect-square hph-bg-gray-300 hph-rounded-lg hph-flex hph-items-center hph-justify-center">
                            <i class="fas fa-user hph-text-4xl hph-text-gray-600"></i>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Agent Info -->
                <div class="hph-order-1 md:hph-order-2">
                    <div class="hph-space-y-lg">
                        <div class="hph-space-y-sm">
                            <h1 class="hph-text-4xl md:hph-text-5xl hph-font-bold hph-text-gray-900 hph-animate-fade-in-up">
                                <?php echo esc_html($agent_data['name']); ?>
                            </h1>
                            
                            <?php if (!empty($agent_data['title'])): ?>
                            <p class="hph-text-xl hph-text-gray-600 hph-font-medium">
                                <?php echo esc_html($agent_data['title']); ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($agent_data['bio'])): ?>
                        <div class="hph-prose hph-text-gray-700 hph-leading-relaxed">
                            <?php echo wp_kses_post($agent_data['bio']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Contact Info -->
                        <div class="hph-flex hph-flex-wrap hph-gap-lg hph-items-center">
                            <?php if (!empty($agent_data['phone'])): ?>
                            <a href="tel:<?php echo esc_attr($agent_data['phone']); ?>" 
                               class="hph-contact-item hph-inline-flex hph-items-center hph-gap-sm hph-text-gray-700 hph-transition-colors hph-hover:text-primary">
                                <div class="hph-w-md hph-h-md hph-bg-primary-100 hph-text-primary hph-rounded-full hph-flex hph-items-center hph-justify-center">
                                    <i class="fas fa-phone hph-text-sm"></i>
                                </div>
                                <span class="hph-font-medium"><?php echo esc_html($agent_data['phone']); ?></span>
                            </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($agent_data['email'])): ?>
                            <a href="mailto:<?php echo esc_attr($agent_data['email']); ?>" 
                               class="hph-contact-item hph-flex hph-items-center hph-gap-2">
                                <i class="fas fa-envelope hph-text-primary"></i>
                                <span><?php echo esc_html($agent_data['email']); ?></span>
                            </a>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="hph-agent-actions hph-flex hph-gap-4">
                            <button class="hph-btn hph-btn-primary" data-action="contact-agent">
                                <i class="fas fa-comments"></i>
                                <?php _e('Contact Agent', 'happy-place-theme'); ?>
                            </button>
                            
                            <?php if (!empty($agent_listings)): ?>
                            <a href="#agent-listings" class="hph-btn hph-btn-outline">
                                <i class="fas fa-home"></i>
                                <?php printf(__('View %d Listings', 'happy-place-theme'), count($agent_listings)); ?>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </section>
    
    <!-- Agent Stats -->
    <?php if (!empty($agent_data['stats'])): ?>
    <section class="hph-agent-stats hph-py-12">
        <div class="hph-container">
            <div class="hph-section-header hph-mb-8 hph-text-center">
                <h2 class="hph-section-title"><?php _e('Performance Statistics', 'happy-place-theme'); ?></h2>
            </div>
            
            <?php
            $stats_data = [
                [
                    'value' => $agent_data['stats']['active_listings'] ?? 0,
                    'label' => __('Active Listings', 'happy-place-theme'),
                    'icon' => 'fas fa-home',
                    'format' => 'number'
                ],
                [
                    'value' => $agent_data['stats']['total_sales'] ?? 0,
                    'label' => __('Properties Sold', 'happy-place-theme'),
                    'icon' => 'fas fa-handshake',
                    'format' => 'number'
                ],
                [
                    'value' => $agent_data['stats']['sales_volume'] ?? 0,
                    'label' => __('Sales Volume', 'happy-place-theme'),
                    'icon' => 'fas fa-dollar-sign',
                    'format' => 'currency'
                ],
                [
                    'value' => $agent_data['stats']['average_rating'] ?? 0,
                    'label' => __('Client Rating', 'happy-place-theme'),
                    'icon' => 'fas fa-star',
                    'format' => 'decimal'
                ]
            ];
            ?>
            
            <div class="hph-stat-cards hph-grid hph-grid-cols-2 lg:hph-grid-cols-4 hph-gap-6">
                <?php foreach ($stats_data as $stat): ?>
                    <div class="hph-stat-card-wrapper">
                        <?php
                        hph_component('stat-card', [
                            'value' => $stat['value'],
                            'label' => $stat['label'],
                            'icon' => $stat['icon'],
                            'format' => $stat['format'],
                            'variant' => 'primary',
                            'animate' => true
                        ]);
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Agent Listings -->
    <?php if (!empty($agent_listings)): ?>
    <section id="agent-listings" class="hph-agent-listings hph-py-12 hph-bg-gray-50">
        <div class="hph-container">
            <div class="hph-section-header hph-mb-8">
                <h2 class="hph-section-title"><?php _e('Current Listings', 'happy-place-theme'); ?></h2>
                <p class="hph-section-subtitle">
                    <?php printf(__('Browse %s\'s available properties', 'happy-place-theme'), esc_html($agent_data['name'])); ?>
                </p>
            </div>
            
            <?php
            // Load listings grid using card-grid component
            hph_component('card-grid', [
                'posts' => $agent_listings,
                'columns' => 3,
                'card_style' => 'standard',
                'show_excerpt' => false,
                'show_meta' => true,
                'show_agent' => false // Don't show agent info since we're on agent page
            ]);
            ?>
            
            <?php if (count($agent_listings) >= 6): ?>
            <div class="hph-text-center hph-mt-8">
                <a href="<?php echo esc_url(add_query_arg('agent', $agent_id, get_post_type_archive_link('listing'))); ?>" 
                   class="hph-btn hph-btn-primary">
                    <?php _e('View All Listings', 'happy-place-theme'); ?>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Contact CTA -->
    <section class="hph-contact-cta hph-py-12 hph-bg-primary hph-text-white">
        <div class="hph-container">
            <div class="hph-text-center">
                <h2 class="hph-section-title hph-text-white hph-mb-4">
                    <?php printf(__('Ready to Work with %s?', 'happy-place-theme'), esc_html($agent_data['name'])); ?>
                </h2>
                <p class="hph-section-subtitle hph-text-white hph-opacity-90 hph-mb-8">
                    <?php _e('Get in touch today to discuss your real estate needs.', 'happy-place-theme'); ?>
                </p>
                
                <div id="agent-contact-form" class="hph-max-w-lg hph-mx-auto">
                    <?php
                    hph_component('agent-contact-form', [
                        'agent_data' => $agent_data,
                        'form_style' => 'inline',
                        'background' => 'transparent'
                    ]);
                    ?>
                </div>
            </div>
        </div>
    </section>
    
</div>

<?php
// Helper function to get agent listings
function hph_get_agent_listings($agent_id, $limit = 6) {
    $args = [
        'post_type' => 'listing',
        'posts_per_page' => $limit,
        'post_status' => 'publish',
        'meta_query' => [
            [
                'key' => '_listing_agent',
                'value' => $agent_id,
                'compare' => '='
            ]
        ],
        'orderby' => 'date',
        'order' => 'DESC'
    ];
    
    $listings_query = new WP_Query($args);
    $listings = [];
    
    if ($listings_query->have_posts()) {
        while ($listings_query->have_posts()) {
            $listings_query->the_post();
            $listings[] = get_post();
        }
        wp_reset_postdata();
    }
    
    return $listings;
}

// Enqueue page-specific assets
wp_enqueue_style('hph-single-agent', HPH_THEME_URI . '/assets/css/framework/05-pages/hph-single-agent.css', ['hph-framework'], HPH_VERSION);
wp_enqueue_script('hph-single-agent', HPH_THEME_URI . '/assets/js/pages/single-agent.js', ['hph-framework-core'], HPH_VERSION, true);

// Localize script with agent context
wp_localize_script('hph-single-agent', 'hphAgent', [
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('hph_agent_nonce'),
    'agentId' => $agent_id,
    'agentName' => $agent_data['name'] ?? '',
    'agentEmail' => $agent_data['email'] ?? '',
    'agentPhone' => $agent_data['phone'] ?? '',
    'hasListings' => !empty($agent_listings),
    'listingCount' => count($agent_listings ?? []),
    'isLoggedIn' => is_user_logged_in(),
    'userId' => get_current_user_id(),
    'strings' => [
        'loading' => __('Loading...', 'happy-place-theme'),
        'contactSent' => __('Message sent successfully!', 'happy-place-theme'),
        'contactError' => __('Failed to send message. Please try again.', 'happy-place-theme'),
        'viewAllListings' => __('View All Listings', 'happy-place-theme'),
        'scheduleCall' => __('Schedule a Call', 'happy-place-theme'),
        'error' => __('An error occurred. Please try again.', 'happy-place-theme')
    ]
]);

get_footer();
?>