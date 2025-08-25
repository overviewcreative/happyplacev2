<?php
/**
 * HPH Property Overview Section
 * 
 * Displays property description, key features, and essential details
 * in a clean, scannable layout with icon-enhanced features
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 * 
 * Args:
 * - listing_id: int (required for bridge functions)
 */

// Extract listing ID from args or global context
$listing_id = $args['listing_id'] ?? get_the_ID();

// Default arguments
$defaults = array(
    'listing_id' => $listing_id,
    'description' => '',
    'highlights' => array(),
    'features' => array(),
    'property_style' => '',
    'lot_size' => '',
    'parking' => '',
    'heating' => '',
    'cooling' => '',
    'hoa_fee' => '',
    'tax_amount' => '',
    'tax_year' => date('Y') - 1,
    'listing_date' => '',
    'last_updated' => '',
    'views' => 0,
    'saves' => 0,
    'share_url' => '',
    'listing_agent' => array(),
    'section_id' => 'property-overview',
    'show_sidebar' => true
);

// Merge with provided args
$config = wp_parse_args($args ?? array(), $defaults);

// Use bridge functions to get data if listing_id exists
if ($listing_id) {
    // Get comprehensive overview data via bridge function
    if (function_exists('hpt_get_listing_overview_details')) {
        $overview_data = hpt_get_listing_overview_details($listing_id);
        
        // Merge bridge data with config (bridge data takes precedence for empty values)
        foreach ($overview_data as $key => $value) {
            if (empty($config[$key]) && !empty($value)) {
                $config[$key] = $value;
            }
        }
    }
    
    // Set share URL if empty
    if (empty($config['share_url'])) {
        $config['share_url'] = get_permalink($listing_id);
    }
}

extract($config);

// Add JavaScript context data (use correct script handle from HPH_Assets service)
wp_add_inline_script('hph-listing-details', sprintf(
    'window.hphContext = window.hphContext || {}; window.hphContext.propertyId = %d; window.hphContext.ajaxUrl = %s; window.hphContext.nonce = %s;',
    $listing_id,
    wp_json_encode(admin_url('admin-ajax.php')),
    wp_json_encode(wp_create_nonce('hph_listing_details'))
), 'before');

// Default features if none provided
if (empty($features)) {
    $features = array(
        array('icon' => 'fas fa-home', 'label' => 'Property Style', 'value' => $property_style ?: 'Traditional'),
        array('icon' => 'fas fa-expand-arrows-alt', 'label' => 'Lot Size', 'value' => $lot_size ?: '0.25 acres'),
        array('icon' => 'fas fa-car', 'label' => 'Parking', 'value' => $parking ?: '2 Car Garage'),
        array('icon' => 'fas fa-fire', 'label' => 'Heating', 'value' => $heating ?: 'Central'),
        array('icon' => 'fas fa-snowflake', 'label' => 'Cooling', 'value' => $cooling ?: 'Central Air'),
        array('icon' => 'fas fa-building', 'label' => 'HOA Fee', 'value' => $hoa_fee ?: 'None')
    );
}

// Default highlights if none provided
if (empty($highlights)) {
    $highlights = array(
        'Move-in ready condition',
        'Recently renovated kitchen',
        'Hardwood floors throughout',
        'Energy efficient appliances',
        'Prime location near schools'
    );
}

// Format dates
$formatted_listing_date = $listing_date ? date('M j, Y', strtotime($listing_date)) : '';
$formatted_updated = $last_updated ? date('M j, Y', strtotime($last_updated)) : '';

// Format numbers
$formatted_views = number_format($views);
$formatted_saves = number_format($saves);

// Component assets are loaded by HPH_Assets service automatically
?>

<section class="hph-property-overview" <?php if ($section_id): ?>id="<?php echo esc_attr($section_id); ?>"<?php endif; ?>>
    <div class="hph-container">
        <div class="hph-overview-grid <?php echo $show_sidebar ? 'hph-has-sidebar' : 'hph-no-sidebar'; ?>">
            
            <!-- Main Content Area -->
            <div class="hph-overview-main">
                
                <!-- Property Highlights -->
                <?php if (!empty($highlights)): ?>
                <div class="hph-overview-highlights">
                    <div class="hph-highlights-header">
                        <i class="fas fa-star hph-highlights-icon"></i>
                        <h3 class="hph-highlights-title">Property Highlights</h3>
                    </div>
                    <ul class="hph-highlights-list">
                        <?php foreach ($highlights as $highlight): ?>
                        <li class="hph-highlight-item">
                            <i class="fas fa-check-circle hph-highlight-check"></i>
                            <span><?php echo esc_html($highlight); ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <!-- Property Description -->
                <?php if ($description): ?>
                <div class="hph-overview-description">
                    <h2 class="hph-section-title">
                        <i class="fas fa-info-circle"></i>
                        About This Property
                    </h2>
                    <div class="hph-description-content">
                        <?php echo wp_kses_post(wpautop($description)); ?>
                    </div>
                    
                    <!-- Read More Toggle for Long Descriptions -->
                    <button class="hph-read-more-btn" style="display: none;">
                        <span class="hph-read-more-text">Read More</span>
                        <span class="hph-read-less-text" style="display: none;">Read Less</span>
                        <i class="fas fa-chevron-down hph-read-more-icon"></i>
                    </button>
                </div>
                <?php endif; ?>
                
                <!-- Property Details Grid -->
                <div class="hph-overview-details">
                    <h2 class="hph-section-title">
                        <i class="fas fa-clipboard-list"></i>
                        Property Details
                    </h2>
                    
                    <div class="hph-details-grid">
                        <?php foreach ($features as $feature): ?>
                        <div class="hph-detail-item">
                            <div class="hph-detail-icon">
                                <i class="<?php echo esc_attr($feature['icon']); ?>"></i>
                            </div>
                            <div class="hph-detail-content">
                                <span class="hph-detail-label"><?php echo esc_html($feature['label']); ?></span>
                                <span class="hph-detail-value"><?php echo esc_html($feature['value']); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Additional Details -->
                    <?php if ($tax_amount || $listing_date): ?>
                    <div class="hph-additional-details">
                        <h3 class="hph-subsection-title">Additional Information</h3>
                        <div class="hph-info-grid">
                            <?php if ($tax_amount): ?>
                            <div class="hph-info-item">
                                <i class="fas fa-dollar-sign"></i>
                                <span class="hph-info-label">Annual Taxes:</span>
                                <span class="hph-info-value"><?php echo esc_html($tax_amount); ?> (<?php echo esc_html($tax_year); ?>)</span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($formatted_listing_date): ?>
                            <div class="hph-info-item">
                                <i class="fas fa-calendar-alt"></i>
                                <span class="hph-info-label">Listed:</span>
                                <span class="hph-info-value"><?php echo esc_html($formatted_listing_date); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($formatted_updated): ?>
                            <div class="hph-info-item">
                                <i class="fas fa-sync-alt"></i>
                                <span class="hph-info-label">Updated:</span>
                                <span class="hph-info-value"><?php echo esc_html($formatted_updated); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Property Stats Bar -->
                <div class="hph-overview-stats">
                    <div class="hph-stat-item">
                        <i class="fas fa-eye"></i>
                        <span class="hph-stat-value"><?php echo esc_html($formatted_views); ?></span>
                        <span class="hph-stat-label">Views</span>
                    </div>
                    
                    <div class="hph-stat-item">
                        <i class="fas fa-heart"></i>
                        <span class="hph-stat-value"><?php echo esc_html($formatted_saves); ?></span>
                        <span class="hph-stat-label">Saves</span>
                    </div>
                    
                    <div class="hph-stat-item hph-share-buttons">
                        <button class="hph-share-btn" onclick="shareProperty('<?php echo esc_url($share_url); ?>')">
                            <i class="fas fa-share-alt"></i>
                            <span>Share</span>
                        </button>
                        
                        <div class="hph-share-dropdown">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($share_url); ?>" 
                               target="_blank" 
                               class="hph-share-option">
                                <i class="fab fa-facebook-f"></i> Facebook
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($share_url); ?>" 
                               target="_blank" 
                               class="hph-share-option">
                                <i class="fab fa-twitter"></i> Twitter
                            </a>
                            <a href="mailto:?subject=Check out this property&body=<?php echo urlencode($share_url); ?>" 
                               class="hph-share-option">
                                <i class="fas fa-envelope"></i> Email
                            </a>
                            <button onclick="copyToClipboard('<?php echo esc_url($share_url); ?>')" 
                                    class="hph-share-option">
                                <i class="fas fa-link"></i> Copy Link
                            </button>
                        </div>
                    </div>
                    
                    <button class="hph-print-btn" onclick="window.print()">
                        <i class="fas fa-print"></i>
                        <span>Print</span>
                    </button>
                </div>
                
            </div>
            
            <!-- Sidebar -->
            <?php if ($show_sidebar): ?>
            <aside class="hph-overview-sidebar">
                
                <!-- Listing Agent Card -->
                <?php if (!empty($listing_agent)): 
                    $agent = wp_parse_args($listing_agent, array(
                        'name' => 'Listing Agent',
                        'phone' => '',
                        'email' => '',
                        'photo' => '',
                        'license' => ''
                    ));
                ?>
                <div class="hph-agent-card">
                    <h3 class="hph-agent-title">Listed By</h3>
                    
                    <?php if ($agent['photo']): ?>
                    <div class="hph-agent-photo">
                        <img src="<?php echo esc_url($agent['photo']); ?>" 
                             alt="<?php echo esc_attr($agent['name']); ?>">
                    </div>
                    <?php else: ?>
                    <div class="hph-agent-photo hph-agent-photo-placeholder">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <?php endif; ?>
                    
                    <h4 class="hph-agent-name"><?php echo esc_html($agent['name']); ?></h4>
                    
                    <?php if ($agent['license']): ?>
                    <p class="hph-agent-license">License #<?php echo esc_html($agent['license']); ?></p>
                    <?php endif; ?>
                    
                    <div class="hph-agent-actions">
                        <?php if ($agent['phone']): ?>
                        <a href="tel:<?php echo esc_attr($agent['phone']); ?>" 
                           class="hph-agent-btn hph-agent-btn-primary">
                            <i class="fas fa-phone"></i>
                            <?php echo esc_html($agent['phone']); ?>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($agent['email']): ?>
                        <a href="mailto:<?php echo esc_attr($agent['email']); ?>" 
                           class="hph-agent-btn hph-agent-btn-secondary">
                            <i class="fas fa-envelope"></i>
                            Send Message
                        </a>
                        <?php endif; ?>
                        
                        <button class="hph-agent-btn hph-agent-btn-outline" 
                                onclick="scheduleShowing()">
                            <i class="fas fa-calendar-check"></i>
                            Schedule Showing
                        </button>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Quick Actions -->
                <div class="hph-quick-actions">
                    <h3 class="hph-actions-title">Quick Actions</h3>
                    
                    <button class="hph-action-btn" onclick="calculateMortgage()">
                        <i class="fas fa-calculator"></i>
                        <span>Mortgage Calculator</span>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    
                    <button class="hph-action-btn" onclick="getPreApproved()">
                        <i class="fas fa-check-circle"></i>
                        <span>Get Pre-Approved</span>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    
                    <button class="hph-action-btn" onclick="requestInfo()">
                        <i class="fas fa-info-circle"></i>
                        <span>Request Info</span>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    
                    <button class="hph-action-btn" onclick="saveProperty()">
                        <i class="fas fa-heart"></i>
                        <span>Save Property</span>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                
                <!-- Sticky CTA -->
                <div class="hph-sticky-cta">
                    <button class="hph-cta-btn hph-cta-primary">
                        <i class="fas fa-phone-alt"></i>
                        Contact Agent
                    </button>
                    <button class="hph-cta-btn hph-cta-secondary">
                        <i class="fas fa-calendar"></i>
                        Tour This Home
                    </button>
                </div>
                
            </aside>
            <?php endif; ?>
            
        </div>
    </div>
</section>