<?php
/**
 * The template for displaying single listing posts
 * Updated design based on mockup with blue theme and full-width layout
 *
 * @package HappyPlaceTheme
 */

get_header(); ?>

<?php while (have_posts()) : the_post(); 
    $listing_id = get_the_ID();
?>
    
    <!-- Full-Width Hero Carousel Section -->
    <section class="hero-carousel-section">
        <!-- Background Carousel -->
        <div class="hero-carousel-bg" id="hero-carousel">
            <?php if (function_exists('hpt_get_listing_gallery')) : 
                $gallery = hpt_get_listing_gallery($listing_id);
                if ($gallery && is_array($gallery) && !empty($gallery)) : ?>
                    
                    <?php foreach ($gallery as $index => $image) : ?>
                        <div class="hero-slide <?php echo $index === 0 ? 'active' : ''; ?>" style="background-image: url('<?php echo esc_url($image['sizes']['large'] ?? $image['url']); ?>');">
                        </div>
                    <?php endforeach; ?>
                    
                <?php elseif (has_post_thumbnail()) : ?>
                    <div class="hero-slide active" style="background-image: url('<?php echo esc_url(get_the_post_thumbnail_url($listing_id, 'large')); ?>');">
                    </div>
                <?php else : ?>
                    <div class="hero-slide active" style="background: linear-gradient(135deg, var(--hph-primary-100) 0%, var(--hph-primary-200) 100%);">
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <?php if (has_post_thumbnail()) : ?>
                    <div class="hero-slide active" style="background-image: url('<?php echo esc_url(get_the_post_thumbnail_url($listing_id, 'large')); ?>');">
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <!-- Gradient Overlay -->
            <div class="hero-carousel-overlay"></div>
            
            <!-- Carousel Controls -->
            <?php if (function_exists('hpt_get_listing_gallery')) : 
                $gallery = hpt_get_listing_gallery($listing_id);
                if ($gallery && count($gallery) > 1) : ?>
                    <div class="carousel-controls">
                        <button class="carousel-control prev" id="carousel-prev">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="carousel-control next" id="carousel-next">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    
                    <div class="carousel-dots">
                        <?php foreach ($gallery as $index => $image) : ?>
                            <button class="carousel-dot <?php echo $index === 0 ? 'active' : ''; ?>" data-slide="<?php echo $index; ?>"></button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <!-- View All Photos Button -->
            <?php if (function_exists('hpt_get_listing_gallery')) : 
                $gallery = hpt_get_listing_gallery($listing_id);
                $photo_count = $gallery ? count($gallery) : 1;
                if ($photo_count > 1) : ?>
                    <button class="view-all-photos-btn" id="view-all-photos">
                        <i class="fas fa-images"></i>
                        <span>View All <?php echo $photo_count; ?> Photos</span>
                    </button>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <!-- Hero Content Overlay -->
        <div class="hero-content-overlay">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        
                        <!-- Status Badges -->
                        <div class="property-status-badges">
                            <?php if (function_exists('hpt_get_listing_status')) : 
                                $listing_status = hpt_get_listing_status($listing_id);
                                $status_class = match($listing_status) {
                                    'sold' => 'status-sold',
                                    'pending' => 'status-pending',
                                    'coming_soon' => 'status-coming-soon',
                                    default => 'status-available'
                                };
                            ?>
                                <span class="status-badge <?php echo esc_attr($status_class); ?>">
                                    <?php if ($listing_status === 'sold') : ?>
                                        <i class="fas fa-check-circle"></i>
                                    <?php elseif ($listing_status === 'pending') : ?>
                                        <i class="fas fa-clock"></i>
                                    <?php elseif ($listing_status === 'coming_soon') : ?>
                                        <i class="fas fa-calendar-plus"></i>
                                    <?php else : ?>
                                        <i class="fas fa-home"></i>
                                    <?php endif; ?>
                                    <?php echo esc_html(ucfirst(str_replace('_', ' ', $listing_status))); ?>
                                </span>
                            <?php endif; ?>
                            
                            <!-- Additional badges -->
                            <?php if (function_exists('hpt_get_listing_virtual_tour_url') && hpt_get_listing_virtual_tour_url($listing_id)) : ?>
                                <span class="status-badge badge-virtual-tour">
                                    <i class="fas fa-video"></i>
                                    Virtual Tour
                                </span>
                            <?php endif; ?>
                            
                            <?php if (function_exists('hpt_get_listing_open_house_date') && hpt_get_listing_open_house_date($listing_id)) : ?>
                                <span class="status-badge badge-open-house">
                                    <i class="fas fa-door-open"></i>
                                    Open House
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Property Information -->
                        <div class="property-hero-info">
                            <div class="row align-items-end">
                                <div class="col-lg-8">
                                    <!-- Address & Location -->
                                    <div class="property-address-section">
                                        <h1 class="property-address">
                                            <?php if (function_exists('hpt_get_listing_address')) : ?>
                                                <?php echo esc_html(hpt_get_listing_address($listing_id)); ?>
                                            <?php else : ?>
                                                <?php the_title(); ?>
                                            <?php endif; ?>
                                        </h1>
                                        
                                        <?php if (function_exists('hpt_get_listing_city_state')) : ?>
                                            <p class="property-location">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <?php echo esc_html(hpt_get_listing_city_state($listing_id)); ?>
                                                <?php if (function_exists('hpt_get_listing_community') && hpt_get_listing_community($listing_id)) : ?>
                                                    • <?php echo esc_html(hpt_get_listing_community($listing_id)); ?>
                                                <?php endif; ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Property Stats -->
                                    <div class="property-stats-compact">
                                        <?php 
                                        $stats = [];
                                        if (function_exists('hpt_get_listing_bedrooms')) {
                                            $bedrooms = hpt_get_listing_bedrooms($listing_id);
                                            if ($bedrooms) $stats[] = ['icon' => 'fa-bed', 'value' => $bedrooms, 'label' => 'beds'];
                                        }
                                        if (function_exists('hpt_get_listing_bathrooms')) {
                                            $bathrooms = hpt_get_listing_bathrooms($listing_id);
                                            if ($bathrooms) $stats[] = ['icon' => 'fa-bath', 'value' => $bathrooms, 'label' => 'baths'];
                                        }
                                        if (function_exists('hpt_get_listing_square_feet')) {
                                            $square_feet = hpt_get_listing_square_feet($listing_id);
                                            if ($square_feet) $stats[] = ['icon' => 'fa-expand-arrows-alt', 'value' => number_format($square_feet), 'label' => 'sq ft'];
                                        }
                                        if (function_exists('hpt_get_listing_lot_size')) {
                                            $lot_size = hpt_get_listing_lot_size($listing_id);
                                            if ($lot_size) $stats[] = ['icon' => 'fa-vector-square', 'value' => $lot_size, 'label' => 'lot'];
                                        }
                                        if (function_exists('hpt_get_listing_year_built')) {
                                            $year_built = hpt_get_listing_year_built($listing_id);
                                            if ($year_built) $stats[] = ['icon' => 'fa-calendar-alt', 'value' => $year_built, 'label' => 'built'];
                                        }
                                        
                                        foreach ($stats as $stat) : ?>
                                            <div class="stat-compact">
                                                <i class="fas <?php echo esc_attr($stat['icon']); ?>"></i>
                                                <span class="stat-value"><?php echo esc_html($stat['value']); ?></span>
                                                <span class="stat-label"><?php echo esc_html($stat['label']); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <div class="col-lg-4">
                                    <!-- Price & Actions -->
                                    <div class="property-price-section">
                                        <?php if (function_exists('hpt_get_listing_price')) : ?>
                                            <div class="property-price">
                                                <?php echo esc_html(hpt_get_listing_price($listing_id)); ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (function_exists('hpt_get_listing_price_per_sqft')) : 
                                            $price_per_sqft = hpt_get_listing_price_per_sqft($listing_id);
                                            if ($price_per_sqft) : ?>
                                                <div class="property-price-details">
                                                    <?php echo esc_html($price_per_sqft); ?> per sq ft
                                                    <?php if (function_exists('hpt_get_listing_estimated_payment')) : 
                                                        $estimated_payment = hpt_get_listing_estimated_payment($listing_id);
                                                        if ($estimated_payment) : ?>
                                                            • Est. <?php echo esc_html($estimated_payment); ?>/mo
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        
                                        <!-- Quick Actions -->
                                        <div class="property-quick-actions">
                                            <button class="btn btn-primary btn-lg contact-agent-btn" data-bs-toggle="modal" data-bs-target="#contactModal">
                                                <i class="fas fa-envelope"></i>
                                                Contact Agent
                                            </button>
                                            <div class="secondary-actions">
                                                <button class="btn btn-outline-light schedule-tour-btn">
                                                    <i class="fas fa-calendar-check"></i>
                                                    Schedule Tour
                                                </button>
                                                <button class="btn btn-outline-light save-property-btn" data-property-id="<?php echo $listing_id; ?>">
                                                    <i class="fas fa-heart"></i>
                                                    Save
                                                </button>
                                                <button class="btn btn-outline-light share-property-btn">
                                                    <i class="fas fa-share"></i>
                                                    Share
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Main Content Sections -->
    <main class="property-main-content">
        
        <!-- Property Description Section -->
        <section class="content-section bg-white">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="section-header">
                            <h2><i class="fas fa-home text-primary me-3"></i>About This Property</h2>
                        </div>
                        <div class="property-description">
                            <?php 
                            $description = '';
                            if (function_exists('hpt_get_listing_description')) {
                                $description = hpt_get_listing_description($listing_id);
                            }
                            if (empty($description)) {
                                $description = get_the_content();
                            }
                            if (empty($description)) {
                                echo '<p>This beautiful property offers comfortable living in a desirable location. Contact the listing agent for more detailed information about features and amenities.</p>';
                            } else {
                                echo wp_kses_post($description);
                            }
                            ?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="property-sidebar">
                            <!-- Quick Property Details -->
                            <div class="sidebar-widget property-quick-details">
                                <h3><i class="fas fa-info-circle text-primary me-2"></i>Property Details</h3>
                                <div class="quick-details-grid">
                                    
                                    <?php 
                                    $mls_number = function_exists('hpt_get_listing_mls_number') ? hpt_get_listing_mls_number($listing_id) : null;
                                    if ($mls_number) : ?>
                                        <div class="detail-item featured">
                                            <span class="detail-label">MLS Number</span>
                                            <span class="detail-value"><?php echo esc_html($mls_number); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php 
                                    $property_type = function_exists('hpt_get_listing_property_type') ? hpt_get_listing_property_type($listing_id) : null;
                                    if ($property_type) : ?>
                                        <div class="detail-item">
                                            <span class="detail-label">Property Type</span>
                                            <span class="detail-value"><?php echo esc_html($property_type); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php 
                                    $garage_spaces = function_exists('hpt_get_listing_garage_spaces') ? hpt_get_listing_garage_spaces($listing_id) : null;
                                    if ($garage_spaces) : ?>
                                        <div class="detail-item">
                                            <span class="detail-label">Garage</span>
                                            <span class="detail-value"><?php echo esc_html($garage_spaces); ?> car spaces</span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php 
                                    $hoa_fees = function_exists('hpt_get_listing_hoa_fees') ? hpt_get_listing_hoa_fees($listing_id) : null;
                                    if ($hoa_fees) : ?>
                                        <div class="detail-item">
                                            <span class="detail-label">HOA Fees</span>
                                            <span class="detail-value">$<?php echo esc_html(number_format($hoa_fees)); ?>/mo</span>
                                        </div>
                                    <?php endif; ?>
                                    
                                </div>
                            </div>
                            
                            <!-- Agent/Company Contact Widget -->
                            <div class="sidebar-widget agent-contact-widget">
                                <?php 
                                $agent_id = function_exists('hpt_get_listing_agent') ? hpt_get_listing_agent($listing_id) : null;
                                $agent_data = null;
                                $use_company = false;
                                
                                if ($agent_id && function_exists('hpt_get_agent_data')) {
                                    $agent_data = hpt_get_agent_data($agent_id);
                                }
                                
                                // If no agent data, use company info as fallback
                                if (!$agent_data || empty($agent_data['name'])) {
                                    $use_company = true;
                                    $agent_data = [
                                        'name' => get_bloginfo('name') ?: 'The Parker Group',
                                        'title' => 'Real Estate Team',
                                        'phone' => get_option('company_phone', '(555) 123-4567'),
                                        'email' => get_option('admin_email', get_bloginfo('admin_email')),
                                        'photo' => null
                                    ];
                                }
                                ?>
                                
                                <h3><i class="fas fa-user-tie text-primary me-2"></i><?php echo $use_company ? 'Contact Us' : 'Listing Agent'; ?></h3>
                                <div class="agent-card-compact">
                                    <div class="agent-photo">
                                        <?php if (!$use_company && function_exists('hpt_get_agent_photo') && hpt_get_agent_photo($agent_id)) : ?>
                                            <img src="<?php echo esc_url(hpt_get_agent_photo($agent_id)); ?>" 
                                                 alt="<?php echo esc_attr($agent_data['name']); ?>" 
                                                 class="agent-avatar-small">
                                        <?php else : ?>
                                            <div class="agent-avatar-placeholder-small">
                                                <i class="fas <?php echo $use_company ? 'fa-building' : 'fa-user'; ?>"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="agent-details">
                                        <h4><?php echo esc_html($agent_data['name']); ?></h4>
                                        <p class="agent-title"><?php echo esc_html($agent_data['title'] ?? ''); ?></p>
                                        
                                        <div class="agent-contact-info">
                                            <?php if (!empty($agent_data['phone'])) : ?>
                                                <a href="tel:<?php echo esc_attr($agent_data['phone']); ?>" class="contact-link">
                                                    <i class="fas fa-phone"></i>
                                                    <?php echo esc_html($agent_data['phone']); ?>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($agent_data['email'])) : ?>
                                                <a href="mailto:<?php echo esc_attr($agent_data['email']); ?>" class="contact-link">
                                                    <i class="fas fa-envelope"></i>
                                                    <?php echo esc_html($agent_data['email']); ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <button class="btn btn-primary btn-sm w-100 mt-2" data-bs-toggle="modal" data-bs-target="#contactModal">
                                            <i class="fas fa-envelope"></i> Send Message
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Mortgage Calculator Widget -->
                            <div class="sidebar-widget mortgage-widget">
                                <h3><i class="fas fa-calculator text-primary me-2"></i>Quick Calculator</h3>
                                <div class="mortgage-calculator-compact">
                                    <div class="calc-input-group">
                                        <label>Home Price</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" id="sidebar-home-price" class="form-control form-control-sm" 
                                                   value="<?php echo esc_attr(function_exists('hpt_get_listing_price_numeric') ? hpt_get_listing_price_numeric($listing_id) : ''); ?>">
                                        </div>
                                    </div>
                                    <div class="calc-input-group">
                                        <label>Down Payment (%)</label>
                                        <input type="number" id="sidebar-down-payment" class="form-control form-control-sm" value="20" min="0" max="100">
                                    </div>
                                    <div class="calc-result" id="sidebar-calc-result">
                                        <div class="result-amount" id="sidebar-monthly-payment">$0</div>
                                        <div class="result-label">Est. Monthly Payment</div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Features Section -->
        <?php if (function_exists('hpt_get_listing_features')) : 
            $features = hpt_get_listing_features($listing_id);
            if ($features && is_array($features) && !empty($features)) : ?>
                <section class="content-section bg-primary-light">
                    <div class="container">
                        <div class="section-header">
                            <h2><i class="fas fa-star text-primary me-3"></i>Features & Amenities</h2>
                        </div>
                        <div class="features-grid">
                            <?php 
                            // Group features by category if possible
                            $feature_categories = [
                                'Interior' => [],
                                'Exterior' => [],
                                'Kitchen' => [],
                                'Other' => []
                            ];
                            
                            foreach ($features as $feature) {
                                // Simple categorization logic
                                $feature_lower = strtolower($feature);
                                if (strpos($feature_lower, 'kitchen') !== false || strpos($feature_lower, 'appliance') !== false) {
                                    $feature_categories['Kitchen'][] = $feature;
                                } elseif (strpos($feature_lower, 'pool') !== false || strpos($feature_lower, 'outdoor') !== false || strpos($feature_lower, 'patio') !== false || strpos($feature_lower, 'deck') !== false) {
                                    $feature_categories['Exterior'][] = $feature;
                                } elseif (strpos($feature_lower, 'floor') !== false || strpos($feature_lower, 'room') !== false || strpos($feature_lower, 'ceiling') !== false) {
                                    $feature_categories['Interior'][] = $feature;
                                } else {
                                    $feature_categories['Other'][] = $feature;
                                }
                            }
                            
                            foreach ($feature_categories as $category => $category_features) :
                                if (!empty($category_features)) : ?>
                                    <div class="feature-category">
                                        <h4><?php echo esc_html($category); ?> Features</h4>
                                        <ul class="feature-list">
                                            <?php foreach ($category_features as $feature) : ?>
                                                <li>
                                                    <i class="fas fa-check text-primary"></i>
                                                    <?php echo esc_html($feature); ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php endif; ?>
        <?php endif; ?>
        
        <!-- Location & Neighborhood Section -->
        <?php if (function_exists('hpt_get_listing_coordinates')) : 
            $coordinates = hpt_get_listing_coordinates($listing_id);
            if ($coordinates && !empty($coordinates['lat']) && !empty($coordinates['lng'])) : ?>
                <section class="content-section bg-white">
                    <div class="container">
                        <div class="section-header">
                            <h2><i class="fas fa-map-marker-alt text-primary me-3"></i>Location & Neighborhood</h2>
                        </div>
                        <div class="location-content">
                            <div class="map-container">
                                <div class="map-placeholder" 
                                     data-lat="<?php echo esc_attr($coordinates['lat']); ?>"
                                     data-lng="<?php echo esc_attr($coordinates['lng']); ?>">
                                    <div class="map-loading">
                                        <i class="fas fa-map text-primary"></i>
                                        <p>Interactive Map Loading...</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Neighborhood Highlights -->
                            <div class="neighborhood-highlights">
                                <div class="highlight-item">
                                    <div class="highlight-icon">
                                        <i class="fas fa-graduation-cap text-primary"></i>
                                    </div>
                                    <div class="highlight-content">
                                        <div class="highlight-value">9/10</div>
                                        <div class="highlight-label">School Rating</div>
                                    </div>
                                </div>
                                <div class="highlight-item">
                                    <div class="highlight-icon">
                                        <i class="fas fa-walking text-primary"></i>
                                    </div>
                                    <div class="highlight-content">
                                        <div class="highlight-value">85</div>
                                        <div class="highlight-label">Walk Score</div>
                                    </div>
                                </div>
                                <div class="highlight-item">
                                    <div class="highlight-icon">
                                        <i class="fas fa-bus text-primary"></i>
                                    </div>
                                    <div class="highlight-content">
                                        <div class="highlight-value">Good</div>
                                        <div class="highlight-label">Transit</div>
                                    </div>
                                </div>
                                <div class="highlight-item">
                                    <div class="highlight-icon">
                                        <i class="fas fa-shopping-cart text-primary"></i>
                                    </div>
                                    <div class="highlight-content">
                                        <div class="highlight-value">12</div>
                                        <div class="highlight-label">Nearby Stores</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            <?php endif; ?>
        <?php endif; ?>
        
        <!-- Agent & Contact Section -->
        <?php if (function_exists('hpt_get_listing_agent')) : 
            $agent_id = hpt_get_listing_agent($listing_id);
            if ($agent_id && function_exists('hpt_get_agent_data')) : 
                $agent_data = hpt_get_agent_data($agent_id); 
                if ($agent_data) : ?>
                    <section class="content-section bg-primary-gradient">
                        <div class="container">
                            <div class="agent-contact-section">
                                <div class="row align-items-center">
                                    <div class="col-lg-6">
                                        <div class="agent-info-card">
                                            <div class="agent-photo">
                                                <?php if (function_exists('hpt_get_agent_photo') && hpt_get_agent_photo($agent_id)) : ?>
                                                    <img src="<?php echo esc_url(hpt_get_agent_photo($agent_id)); ?>" 
                                                         alt="<?php echo esc_attr($agent_data['name'] ?? ''); ?>" 
                                                         class="agent-avatar">
                                                <?php else : ?>
                                                    <div class="agent-avatar-placeholder">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="agent-details">
                                                <h3><?php echo esc_html($agent_data['name'] ?? 'Agent Name'); ?></h3>
                                                
                                                <?php if (function_exists('hpt_get_agent_title') && hpt_get_agent_title($agent_id)) : ?>
                                                    <p class="agent-title"><?php echo esc_html(hpt_get_agent_title($agent_id)); ?></p>
                                                <?php endif; ?>
                                                
                                                <?php if (function_exists('hpt_get_agent_rating') && hpt_get_agent_rating($agent_id)) : 
                                                    $rating = hpt_get_agent_rating($agent_id); ?>
                                                    <div class="agent-rating">
                                                        <?php for ($i = 1; $i <= 5; $i++) : ?>
                                                            <i class="fas fa-star <?php echo $i <= $rating ? 'text-warning' : 'text-muted'; ?>"></i>
                                                        <?php endfor; ?>
                                                        <span class="rating-text">(<?php echo esc_html($rating); ?>/5)</span>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="agent-contact-info">
                                                    <?php if (function_exists('hpt_get_agent_phone') && hpt_get_agent_phone($agent_id)) : ?>
                                                        <a href="tel:<?php echo esc_attr(hpt_get_agent_phone($agent_id)); ?>" class="contact-link">
                                                            <i class="fas fa-phone"></i>
                                                            <?php echo esc_html(hpt_get_agent_phone($agent_id)); ?>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (function_exists('hpt_get_agent_email') && hpt_get_agent_email($agent_id)) : ?>
                                                        <a href="mailto:<?php echo esc_attr(hpt_get_agent_email($agent_id)); ?>" class="contact-link">
                                                            <i class="fas fa-envelope"></i>
                                                            <?php echo esc_html(hpt_get_agent_email($agent_id)); ?>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="contact-cta">
                                            <h3>Ready to See This Home?</h3>
                                            <p>Contact our expert agent today to schedule a private showing or get more information about this property.</p>
                                            <div class="cta-buttons">
                                                <button class="btn btn-light btn-lg me-3" data-bs-toggle="modal" data-bs-target="#contactModal">
                                                    <i class="fas fa-envelope me-2"></i>
                                                    Send Message
                                                </button>
                                                <button class="btn btn-outline-light btn-lg">
                                                    <i class="fas fa-calendar-check me-2"></i>
                                                    Schedule Tour
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
        
        <!-- Mortgage Calculator Section -->
        <section class="content-section bg-light">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="section-header">
                            <h2><i class="fas fa-calculator text-primary me-3"></i>Mortgage Calculator</h2>
                            <p>Get an estimate of your monthly payments for this property.</p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="mortgage-calculator-card">
                            <form class="mortgage-calculator-form" id="mortgage-calculator">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Home Price</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" id="home-price" class="form-control" 
                                                   value="<?php echo esc_attr(function_exists('hpt_get_listing_price_numeric') ? hpt_get_listing_price_numeric($listing_id) : ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Down Payment (%)</label>
                                        <input type="number" id="down-payment" class="form-control" value="20" min="0" max="100" step="0.5">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Interest Rate (%)</label>
                                        <input type="number" id="interest-rate" class="form-control" value="6.5" step="0.1">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Loan Term</label>
                                        <select id="loan-term" class="form-select">
                                            <option value="30">30 years</option>
                                            <option value="15">15 years</option>
                                            <option value="20">20 years</option>
                                            <option value="25">25 years</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="calculator-result" id="calculator-result">
                                    <div class="result-display">
                                        <div class="monthly-payment" id="monthly-payment">$0</div>
                                        <div class="payment-label">Estimated Monthly Payment</div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Similar Properties Section -->
        <?php if (function_exists('hpt_get_similar_listings')) : 
            $similar_listings = hpt_get_similar_listings($listing_id, 6);
            if ($similar_listings && !empty($similar_listings)) : ?>
                <section class="content-section bg-white">
                    <div class="container">
                        <div class="section-header">
                            <h2><i class="fas fa-home text-primary me-3"></i>Similar Properties</h2>
                            <p>Properties you might also be interested in</p>
                        </div>
                        <div class="similar-properties-grid">
                            <?php foreach (array_slice($similar_listings, 0, 3) as $similar_post) : 
                                setup_postdata($similar_post); ?>
                                <div class="property-card">
                                    <?php if (has_post_thumbnail($similar_post->ID)) : ?>
                                        <div class="property-card-image">
                                            <a href="<?php echo esc_url(get_permalink($similar_post->ID)); ?>">
                                                <?php echo get_the_post_thumbnail($similar_post->ID, 'medium_large', array('class' => 'img-fluid')); ?>
                                            </a>
                                            <div class="card-badges">
                                                <?php if (function_exists('hpt_get_listing_status') && hpt_get_listing_status($similar_post->ID) === 'new') : ?>
                                                    <span class="badge bg-primary">New</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="property-card-content">
                                        <?php if (function_exists('hpt_get_listing_price')) : ?>
                                            <div class="property-price"><?php echo esc_html(hpt_get_listing_price($similar_post->ID)); ?></div>
                                        <?php endif; ?>
                                        <h4 class="property-title">
                                            <a href="<?php echo esc_url(get_permalink($similar_post->ID)); ?>">
                                                <?php echo esc_html(get_the_title($similar_post->ID)); ?>
                                            </a>
                                        </h4>
                                        <div class="property-features">
                                            <?php if (function_exists('hpt_get_listing_bedrooms') && hpt_get_listing_bedrooms($similar_post->ID)) : ?>
                                                <span><i class="fas fa-bed"></i> <?php echo esc_html(hpt_get_listing_bedrooms($similar_post->ID)); ?> beds</span>
                                            <?php endif; ?>
                                            <?php if (function_exists('hpt_get_listing_bathrooms') && hpt_get_listing_bathrooms($similar_post->ID)) : ?>
                                                <span><i class="fas fa-bath"></i> <?php echo esc_html(hpt_get_listing_bathrooms($similar_post->ID)); ?> baths</span>
                                            <?php endif; ?>
                                            <?php if (function_exists('hpt_get_listing_square_feet') && hpt_get_listing_square_feet($similar_post->ID)) : ?>
                                                <span><i class="fas fa-expand-arrows-alt"></i> <?php echo esc_html(number_format(hpt_get_listing_square_feet($similar_post->ID))); ?> sq ft</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; 
                            wp_reset_postdata(); ?>
                        </div>
                    </div>
                </section>
            <?php endif; ?>
        <?php endif; ?>
        
    </main>
    
    <!-- Contact Modal -->
    <div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="contactModalLabel">
                        <i class="fas fa-envelope text-primary me-2"></i>
                        Contact Agent About This Property
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="contact-agent-form" id="contact-agent-form">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name *</label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name *</label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" name="phone" class="form-control">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Message *</label>
                                <textarea name="message" class="form-control" rows="4" required>I'm interested in this property at <?php echo esc_attr(function_exists('hpt_get_listing_address') ? hpt_get_listing_address($listing_id) : get_the_title()); ?>. Please send me more information.</textarea>
                            </div>
                        </div>
                        <input type="hidden" name="property_id" value="<?php echo esc_attr($listing_id); ?>">
                        <?php wp_nonce_field('contact_agent_' . $listing_id, 'contact_agent_nonce'); ?>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="contact-agent-form" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>Send Message
                    </button>
                </div>
            </div>
        </div>
    </div>
    
<?php endwhile; ?>

<style>
/* ========================================
   HERO CAROUSEL STYLES
   ======================================== */
.hero-carousel-section {
    position: relative;
    height: 100vh;
    min-height: 700px;
    overflow: hidden;
}

.hero-carousel-bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.hero-slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    opacity: 0;
    transition: opacity 1s ease-in-out;
}

.hero-slide.active {
    opacity: 1;
}

.hero-carousel-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, 
        rgba(81, 186, 224, 0.8) 0%, 
        rgba(2, 132, 199, 0.6) 50%,
        rgba(81, 186, 224, 0.9) 100%);
    z-index: 1;
}

.hero-content-overlay {
    position: relative;
    height: 100%;
    display: flex;
    align-items: flex-end;
    padding-bottom: 4rem;
    z-index: 2;
}

/* Carousel Controls */
.carousel-controls {
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    transform: translateY(-50%);
    display: flex;
    justify-content: space-between;
    padding: 0 2rem;
    z-index: 3;
}

.carousel-control {
    width: 3rem;
    height: 3rem;
    background: rgba(255, 255, 255, 0.2);
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.carousel-control:hover {
    background: rgba(255, 255, 255, 0.3);
    border-color: rgba(255, 255, 255, 0.5);
    transform: scale(1.1);
}

.carousel-dots {
    position: absolute;
    bottom: 2rem;
    left: 2rem;
    display: flex;
    gap: 0.5rem;
    z-index: 3;
}

.carousel-dot {
    width: 0.75rem;
    height: 0.75rem;
    background: rgba(255, 255, 255, 0.4);
    border: none;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.3s ease;
}

.carousel-dot.active,
.carousel-dot:hover {
    background: rgba(255, 255, 255, 0.9);
    transform: scale(1.2);
}

.view-all-photos-btn {
    position: absolute;
    bottom: 2rem;
    right: 2rem;
    background: rgba(255, 255, 255, 0.95);
    border: none;
    border-radius: 0.5rem;
    color: var(--hph-gray-800);
    padding: 0.75rem 1.25rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 3;
    backdrop-filter: blur(10px);
}

.view-all-photos-btn:hover {
    background: white;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

/* Status Badges */
.property-status-badges {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-weight: 600;
    font-size: 0.875rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.status-badge.status-available {
    background: rgba(5, 150, 105, 0.9);
    color: white;
}

.status-badge.status-pending {
    background: rgba(245, 158, 11, 0.9);
    color: white;
}

.status-badge.status-sold {
    background: rgba(220, 38, 38, 0.9);
    color: white;
}

.status-badge.badge-virtual-tour {
    background: rgba(139, 92, 246, 0.9);
    color: white;
}

.status-badge.badge-open-house {
    background: rgba(251, 191, 36, 0.9);
    color: var(--hph-gray-900);
}

/* Property Info */
.property-hero-info {
    width: 100%;
}

.property-address-section {
    margin-bottom: 2rem;
}

.property-address {
    font-size: 3rem;
    font-weight: 700;
    color: white;
    margin-bottom: 0.5rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.property-location {
    font-size: 1.25rem;
    color: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Property Stats - Compact Version */
.property-stats-compact {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-compact {
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
    min-width: 100px;
}

.stat-compact:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: translateY(-2px);
}

.stat-compact i {
    font-size: 1rem;
    color: white;
    opacity: 0.9;
}

.stat-compact .stat-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: white;
}

.stat-compact .stat-label {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.8);
    font-weight: 500;
}

/* Price Section */
.property-price-section {
    text-align: right;
}

.property-price {
    font-size: 3rem;
    font-weight: 700;
    color: white;
    margin-bottom: 0.5rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.property-price-details {
    color: rgba(255, 255, 255, 0.9);
    font-size: 1rem;
    margin-bottom: 2rem;
}

/* Quick Actions */
.property-quick-actions {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.secondary-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
    flex-wrap: wrap;
}

/* ========================================
   MAIN CONTENT SECTIONS
   ======================================== */
.property-main-content {
    position: relative;
    z-index: 1;
}

.content-section {
    padding: 4rem 0;
}

.bg-primary-light {
    background: linear-gradient(135deg, rgba(81, 186, 224, 0.05) 0%, rgba(81, 186, 224, 0.02) 100%);
}

.bg-primary-gradient {
    background: linear-gradient(135deg, var(--hph-primary) 0%, var(--hph-primary-dark) 100%);
    color: white;
}

.section-header {
    margin-bottom: 3rem;
}

.section-header h2 {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--hph-gray-900);
    margin-bottom: 1rem;
}

.bg-primary-gradient .section-header h2 {
    color: white;
}

.section-header p {
    font-size: 1.125rem;
    color: var(--hph-gray-600);
}

.bg-primary-gradient .section-header p {
    color: rgba(255, 255, 255, 0.9);
}

/* Property Description */
.property-description {
    font-size: 1.125rem;
    line-height: 1.8;
    color: var(--hph-gray-700);
}

.property-description p {
    margin-bottom: 1.5rem;
}

/* Sidebar */
.property-sidebar {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.sidebar-widget {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid var(--hph-gray-200);
}

.sidebar-widget h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: var(--hph-gray-900);
}

/* Quick Details */
.property-quick-details h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: var(--hph-gray-900);
}

.quick-details-grid .detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--hph-gray-100);
}

.quick-details-grid .detail-item:last-child {
    border-bottom: none;
}

.quick-details-grid .detail-item.featured {
    background: rgba(81, 186, 224, 0.1);
    padding: 1rem;
    border-radius: 0.5rem;
    border: 1px solid var(--hph-primary-200);
    border-bottom: 1px solid var(--hph-primary-200);
}

.detail-label {
    font-weight: 500;
    color: var(--hph-gray-600);
}

.detail-value {
    font-weight: 600;
    color: var(--hph-gray-900);
}

.detail-item.featured .detail-value {
    color: var(--hph-primary-dark);
}

/* Features Grid */
.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.feature-category h4 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--hph-primary-dark);
    margin-bottom: 1rem;
}

.feature-list {
    list-style: none;
    padding: 0;
}

.feature-list li {
    padding: 0.5rem 0;
    color: var(--hph-gray-700);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

/* Location Section */
.map-container {
    background: var(--hph-gray-100);
    border-radius: 1rem;
    height: 400px;
    margin-bottom: 2rem;
    overflow: hidden;
}

.map-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--hph-gray-500);
}

.map-loading {
    text-align: center;
}

.map-loading i {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.neighborhood-highlights {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.highlight-item {
    background: white;
    border-radius: 0.75rem;
    padding: 1.5rem;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid var(--hph-gray-200);
    transition: all 0.3s ease;
}

.highlight-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.highlight-icon {
    font-size: 2rem;
    margin-bottom: 1rem;
}

.highlight-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--hph-gray-900);
    margin-bottom: 0.25rem;
}

.highlight-label {
    color: var(--hph-gray-600);
    font-size: 0.875rem;
}

/* Agent Section */
.agent-contact-section {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 1rem;
    padding: 3rem;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.agent-info-card {
    display: flex;
    gap: 1.5rem;
    align-items: center;
}

.agent-photo {
    flex-shrink: 0;
}

.agent-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    border: 4px solid rgba(255, 255, 255, 0.2);
}

.agent-avatar-placeholder {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    border: 4px solid rgba(255, 255, 255, 0.2);
}

.agent-details h3 {
    font-size: 1.75rem;
    font-weight: 700;
    color: white;
    margin-bottom: 0.5rem;
}

.agent-title {
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 1rem;
}

.agent-rating {
    margin-bottom: 1rem;
}

.rating-text {
    color: rgba(255, 255, 255, 0.8);
    margin-left: 0.5rem;
}

.agent-contact-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.contact-link {
    color: white;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.contact-link:hover {
    color: rgba(255, 255, 255, 0.8);
}

.contact-cta h3 {
    font-size: 2rem;
    font-weight: 700;
    color: white;
    margin-bottom: 1rem;
}

.contact-cta p {
    color: rgba(255, 255, 255, 0.9);
    font-size: 1.125rem;
    margin-bottom: 2rem;
}

.cta-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

/* Mortgage Calculator */
.mortgage-calculator-card {
    background: white;
    border-radius: 1rem;
    padding: 2rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    border: 1px solid var(--hph-gray-200);
}

.calculator-result {
    background: linear-gradient(135deg, rgba(81, 186, 224, 0.1) 0%, rgba(81, 186, 224, 0.05) 100%);
    border: 2px solid var(--hph-primary-200);
    border-radius: 0.75rem;
    padding: 2rem;
    text-align: center;
    margin-top: 1.5rem;
}

.monthly-payment {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--hph-primary-dark);
    margin-bottom: 0.5rem;
}

.payment-label {
    color: var(--hph-gray-600);
    font-weight: 500;
}

/* Agent/Company Contact Widget */
.agent-contact-widget {
    border: 2px solid var(--hph-primary-200);
    background: linear-gradient(135deg, rgba(81, 186, 224, 0.05) 0%, rgba(81, 186, 224, 0.02) 100%);
}

.agent-card-compact {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}

.agent-avatar-small {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    border: 2px solid var(--hph-primary-200);
    object-fit: cover;
}

.agent-avatar-placeholder-small {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--hph-primary-100);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: var(--hph-primary);
    border: 2px solid var(--hph-primary-200);
}

.agent-card-compact h4 {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--hph-gray-900);
    margin-bottom: 0.25rem;
}

.agent-card-compact .agent-title {
    color: var(--hph-gray-600);
    font-size: 0.875rem;
    margin-bottom: 0.75rem;
}

.agent-card-compact .agent-contact-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.agent-card-compact .contact-link {
    color: var(--hph-gray-700);
    text-decoration: none;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.agent-card-compact .contact-link:hover {
    color: var(--hph-primary);
}

/* Mortgage Calculator Widget */
.mortgage-calculator-compact .calc-input-group {
    margin-bottom: 1rem;
}

.mortgage-calculator-compact label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--hph-gray-700);
    margin-bottom: 0.5rem;
}

.calc-result {
    background: var(--hph-primary-50);
    border: 2px solid var(--hph-primary-200);
    border-radius: 0.5rem;
    padding: 1rem;
    text-align: center;
    margin-top: 1rem;
}

.result-amount {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--hph-primary-dark);
    margin-bottom: 0.25rem;
}

.result-label {
    color: var(--hph-gray-600);
    font-size: 0.875rem;
    font-weight: 500;
}

/* Similar Properties */
.similar-properties-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.property-card {
    background: white;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid var(--hph-gray-200);
    transition: all 0.3s ease;
}

.property-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.property-card-image {
    position: relative;
    overflow: hidden;
}

.property-card-image img {
    width: 100%;
    height: 250px;
    object-fit: cover;
    transition: all 0.3s ease;
}

.property-card:hover .property-card-image img {
    transform: scale(1.05);
}

.card-badges {
    position: absolute;
    top: 1rem;
    left: 1rem;
}

.property-card-content {
    padding: 1.5rem;
}

.property-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--hph-primary-dark);
    margin-bottom: 0.5rem;
}

.property-title {
    margin-bottom: 1rem;
}

.property-title a {
    color: var(--hph-gray-900);
    text-decoration: none;
    font-weight: 600;
}

.property-title a:hover {
    color: var(--hph-primary);
}

.property-features {
    display: flex;
    gap: 1rem;
    color: var(--hph-gray-600);
    font-size: 0.875rem;
    flex-wrap: wrap;
}

.property-features span {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

/* ========================================
   RESPONSIVE DESIGN
   ======================================== */
@media (max-width: 1024px) {
    .property-address {
        font-size: 2.5rem;
    }
    
    .property-price {
        font-size: 2.5rem;
    }
    
    .property-stats-compact {
        justify-content: center;
    }
    
    .stat-compact {
        flex: 1;
        min-width: 80px;
        justify-content: center;
    }
}

@media (max-width: 768px) {
    .hero-carousel-section {
        height: 80vh;
        min-height: 600px;
    }
    
    .hero-content-overlay {
        padding-bottom: 2rem;
    }
    
    .property-address {
        font-size: 2rem;
    }
    
    .property-price {
        font-size: 2rem;
        text-align: left;
        margin-top: 1rem;
    }
    
    .property-price-section {
        text-align: left;
    }
    
    .property-stats-compact {
        flex-direction: column;
        align-items: center;
        max-width: 300px;
    }
    
    .stat-compact {
        width: 100%;
        justify-content: center;
    }
    
    .secondary-actions {
        justify-content: flex-start;
    }
    
    .agent-info-card {
        flex-direction: column;
        text-align: center;
    }
    
    .cta-buttons {
        justify-content: center;
    }
    
    .features-grid {
        grid-template-columns: 1fr;
    }
    
    .neighborhood-highlights {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .property-sidebar {
        margin-top: 2rem;
    }
    
    .agent-card-compact {
        flex-direction: column;
        text-align: center;
    }
}

@media (max-width: 640px) {
    .carousel-controls {
        padding: 0 1rem;
    }
    
    .carousel-dots,
    .view-all-photos-btn {
        bottom: 1rem;
    }
    
    .property-stats-compact {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .stat-compact {
        width: 100%;
        padding: 0.5rem;
        justify-content: center;
    }
    
    .property-quick-actions {
        width: 100%;
    }
    
    .secondary-actions {
        flex-direction: column;
    }
    
    .agent-contact-section {
        padding: 2rem;
    }
    
    .neighborhood-highlights {
        grid-template-columns: 1fr;
    }
    
    .sidebar-widget {
        padding: 1rem;
    }
    
    .agent-card-compact {
        text-align: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Hero Carousel functionality
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.carousel-dot');
    const prevBtn = document.getElementById('carousel-prev');
    const nextBtn = document.getElementById('carousel-next');
    let currentSlide = 0;
    let slideInterval;

    if (slides.length > 1) {
        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.classList.toggle('active', i === index);
            });
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === index);
            });
            currentSlide = index;
        }

        function nextSlide() {
            const next = (currentSlide + 1) % slides.length;
            showSlide(next);
        }

        function prevSlide() {
            const prev = (currentSlide - 1 + slides.length) % slides.length;
            showSlide(prev);
        }

        // Auto-advance slides
        function startSlideshow() {
            slideInterval = setInterval(nextSlide, 5000);
        }

        function stopSlideshow() {
            clearInterval(slideInterval);
        }

        // Event listeners
        if (nextBtn) nextBtn.addEventListener('click', () => { nextSlide(); stopSlideshow(); startSlideshow(); });
        if (prevBtn) prevBtn.addEventListener('click', () => { prevSlide(); stopSlideshow(); startSlideshow(); });
        
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => { 
                showSlide(index); 
                stopSlideshow(); 
                startSlideshow(); 
            });
        });

        // Start slideshow
        startSlideshow();
        
        // Pause on hover
        const heroSection = document.querySelector('.hero-carousel-section');
        if (heroSection) {
            heroSection.addEventListener('mouseenter', stopSlideshow);
            heroSection.addEventListener('mouseleave', startSlideshow);
        }
    }

    // Mortgage Calculator
    const homePrice = document.getElementById('home-price');
    const downPayment = document.getElementById('down-payment');
    const interestRate = document.getElementById('interest-rate');
    const loanTerm = document.getElementById('loan-term');
    const monthlyPayment = document.getElementById('monthly-payment');

    function calculatePayment() {
        const price = parseFloat(homePrice.value) || 0;
        const downPercent = parseFloat(downPayment.value) || 0;
        const rate = parseFloat(interestRate.value) || 0;
        const years = parseInt(loanTerm.value) || 30;

        if (price > 0 && rate > 0) {
            const downAmount = price * (downPercent / 100);
            const loanAmount = price - downAmount;
            const monthlyRate = (rate / 100) / 12;
            const numPayments = years * 12;

            const payment = loanAmount * (monthlyRate * Math.pow(1 + monthlyRate, numPayments)) / (Math.pow(1 + monthlyRate, numPayments) - 1);
            
            monthlyPayment.textContent = '$' + payment.toLocaleString('en-US', { 
                minimumFractionDigits: 0, 
                maximumFractionDigits: 0 
            });
        }
    }

    // Auto-calculate on input
    [homePrice, downPayment, interestRate, loanTerm].forEach(input => {
        if (input) {
            input.addEventListener('input', calculatePayment);
        }
    });

    // Initial calculation
    if (homePrice && homePrice.value) {
        calculatePayment();
    }

    // Sidebar Calculator
    const sidebarHomePrice = document.getElementById('sidebar-home-price');
    const sidebarDownPayment = document.getElementById('sidebar-down-payment');
    const sidebarMonthlyPayment = document.getElementById('sidebar-monthly-payment');

    function calculateSidebarPayment() {
        const price = parseFloat(sidebarHomePrice?.value) || 0;
        const downPercent = parseFloat(sidebarDownPayment?.value) || 20;
        const rate = 6.5; // Default rate for quick calculation
        const years = 30; // Default term

        if (price > 0) {
            const downAmount = price * (downPercent / 100);
            const loanAmount = price - downAmount;
            const monthlyRate = (rate / 100) / 12;
            const numPayments = years * 12;

            const payment = loanAmount * (monthlyRate * Math.pow(1 + monthlyRate, numPayments)) / (Math.pow(1 + monthlyRate, numPayments) - 1);
            
            if (sidebarMonthlyPayment) {
                sidebarMonthlyPayment.textContent = '$' + payment.toLocaleString('en-US', { 
                    minimumFractionDigits: 0, 
                    maximumFractionDigits: 0 
                });
            }
        }
    }

    // Auto-calculate sidebar on input
    [sidebarHomePrice, sidebarDownPayment].forEach(input => {
        if (input) {
            input.addEventListener('input', calculateSidebarPayment);
        }
    });

    // Initial sidebar calculation
    if (sidebarHomePrice && sidebarHomePrice.value) {
        calculateSidebarPayment();
    }

    // Save Property functionality
    const saveBtn = document.querySelector('.save-property-btn');
    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            this.classList.toggle('saved');
            const icon = this.querySelector('i');
            if (this.classList.contains('saved')) {
                icon.className = 'fas fa-heart text-danger';
                this.innerHTML = '<i class="fas fa-heart text-danger"></i> Saved';
            } else {
                icon.className = 'fas fa-heart';
                this.innerHTML = '<i class="fas fa-heart"></i> Save';
            }
        });
    }

    // Contact form submission
    const contactForm = document.getElementById('contact-agent-form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Basic form validation
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (isValid) {
                // Here you would typically send the form data to your backend
                alert('Thank you for your message! The agent will contact you shortly.');
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('contactModal'));
                if (modal) modal.hide();
                
                // Reset form
                this.reset();
            }
        });
    }

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
});
</script>

<?php get_footer(); ?>