<?php
/**
 * HPH Agent Card with Lead Form Component
 * 
 * Combined agent information display with integrated lead capture form
 * Location: /wp-content/themes/happy-place/template-parts/components/listing-agent.php
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 * 
 * Args:
 * - listing_id: int (required for bridge functions)
 * - agent_id: int (WordPress user ID) - optional
 */

// Extract listing ID from args or global context
$listing_id = $args['listing_id'] ?? get_the_ID();

// Default arguments
$defaults = array(
    'listing_id' => $listing_id,
    'agent_id' => 0,
    'agent_name' => '',
    'agent_title' => 'Listing Agent',
    'agent_phone' => '',
    'agent_mobile' => '',
    'agent_email' => '',
    'agent_photo' => '',
    'agent_license' => '',
    'agent_bio' => '',
    'agent_specialties' => array(),
    'agent_languages' => array('English'),
    'agent_rating' => 0,
    'agent_reviews_count' => 0,
    'agent_listings_count' => 0,
    'agent_sold_count' => 0,
    'agent_years_experience' => 0,
    'agency_name' => '',
    'agency_logo' => '',
    'agency_phone' => '',
    'property_id' => $listing_id,
    'property_address' => '',
    'property_mls' => '',
    'form_title' => 'Contact Agent',
    'form_subtitle' => 'Get more information about this property',
    'show_schedule_tour' => true,
    'show_request_info' => true,
    'show_pre_approval' => true,
    'tour_types' => array('in-person', 'virtual', 'video-chat'),
    'position' => 'sidebar',
    'sticky' => true,
    'section_id' => 'agent-contact'
);

// Merge with provided args
$config = wp_parse_args($args ?? array(), $defaults);

// Use bridge functions to get agent data if listing_id exists
if ($listing_id) {
    $agent_data = array();
    
    // Get agent ID from listing if not provided
    if (empty($config['agent_id']) && function_exists('hpt_get_listing_agent_id')) {
        $config['agent_id'] = hpt_get_listing_agent_id($listing_id);
    }
    
    // Get complete agent data via bridge
    if ($config['agent_id'] && function_exists('hpt_get_agent_data')) {
        $agent_data = hpt_get_agent_data($config['agent_id']);
        
        // Merge agent data with config
        foreach ($agent_data as $key => $value) {
            if (empty($config[$key]) && !empty($value)) {
                $config[$key] = $value;
            }
        }
    }
    
    // Get property data for form context
    if (function_exists('hpt_get_listing_address')) {
        $config['property_address'] = hpt_get_listing_address($listing_id, 'full');
    }
    if (function_exists('hpt_get_listing_mls_number')) {
        $config['property_mls'] = hpt_get_listing_mls_number($listing_id);
    }
}

extract($config);

// Generate unique form ID
$form_id = 'agent-lead-form-' . uniqid();

// Position classes
$wrapper_classes = array('hph-agent-lead-form');
switch ($position) {
    case 'full-width':
        $wrapper_classes[] = 'hph-agent-full-width';
        break;
    case 'modal':
        $wrapper_classes[] = 'hph-agent-modal';
        break;
    case 'sidebar':
    default:
        $wrapper_classes[] = 'hph-agent-sidebar';
        if ($sticky) {
            $wrapper_classes[] = 'hph-agent-sticky';
        }
        break;
}

// Component assets are loaded by HPH_Assets service automatically

// Localize script for AJAX
wp_localize_script('hph-agent-lead-form', 'hphAgentForm', array(
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('hph_agent_form_nonce'),
    'propertyId' => $property_id,
    'agentId' => $agent_id
));
?>

<div class="<?php echo esc_attr(implode(' ', $wrapper_classes)); ?>" id="<?php echo esc_attr($section_id); ?>">
    <div class="hph-agent-card">
        
        <!-- Agent Header -->
        <div class="hph-agent-header">
            <div class="hph-agent-photo-wrapper">
                <?php if ($agent_photo): ?>
                <img src="<?php echo esc_url($agent_photo); ?>" 
                     alt="<?php echo esc_attr($agent_name); ?>" 
                     class="hph-agent-photo">
                <?php else: ?>
                <div class="hph-agent-photo-placeholder">
                    <i class="fas fa-user-tie"></i>
                </div>
                <?php endif; ?>
                
                <?php if ($agent_rating > 0): ?>
                <div class="hph-agent-rating">
                    <i class="fas fa-star"></i>
                    <span><?php echo number_format($agent_rating, 1); ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="hph-agent-info">
                <h3 class="hph-agent-name"><?php echo esc_html($agent_name); ?></h3>
                <p class="hph-agent-title"><?php echo esc_html($agent_title); ?></p>
                
                <?php if ($agent_license): ?>
                <p class="hph-agent-license">
                    <i class="fas fa-id-card"></i>
                    License #<?php echo esc_html($agent_license); ?>
                </p>
                <?php endif; ?>
                
                <?php if ($agency_name): ?>
                <div class="hph-agent-agency">
                    <?php if ($agency_logo): ?>
                    <img src="<?php echo esc_url($agency_logo); ?>" 
                         alt="<?php echo esc_attr($agency_name); ?>" 
                         class="hph-agency-logo">
                    <?php else: ?>
                    <span class="hph-agency-name"><?php echo esc_html($agency_name); ?></span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Agent Stats -->
        <div class="hph-agent-stats">
            <?php if ($agent_sold_count > 0): ?>
            <div class="hph-stat">
                <span class="hph-stat-value"><?php echo number_format($agent_sold_count); ?></span>
                <span class="hph-stat-label">Properties Sold</span>
            </div>
            <?php endif; ?>
            
            <?php if ($agent_years_experience > 0): ?>
            <div class="hph-stat">
                <span class="hph-stat-value"><?php echo esc_html($agent_years_experience); ?></span>
                <span class="hph-stat-label">Years Experience</span>
            </div>
            <?php endif; ?>
            
            <?php if ($agent_reviews_count > 0): ?>
            <div class="hph-stat">
                <span class="hph-stat-value"><?php echo number_format($agent_reviews_count); ?></span>
                <span class="hph-stat-label">Reviews</span>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Quick Contact Buttons -->
        <div class="hph-agent-quick-contact">
            <?php if ($agent_phone): ?>
            <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9]/', '', $agent_phone)); ?>" 
               class="hph-quick-btn hph-btn-call">
                <i class="fas fa-phone"></i>
                <span>Call</span>
            </a>
            <?php endif; ?>
            
            <?php if ($agent_mobile): ?>
            <a href="sms:<?php echo esc_attr(preg_replace('/[^0-9]/', '', $agent_mobile)); ?>" 
               class="hph-quick-btn hph-btn-text">
                <i class="fas fa-comment-dots"></i>
                <span>Text</span>
            </a>
            <?php endif; ?>
            
            <?php if ($agent_email): ?>
            <a href="mailto:<?php echo esc_attr($agent_email); ?>" 
               class="hph-quick-btn hph-btn-email">
                <i class="fas fa-envelope"></i>
                <span>Email</span>
            </a>
            <?php endif; ?>
        </div>
        
        <!-- Agent Details (Expandable) -->
        <div class="hph-agent-details">
            <button class="hph-details-toggle" aria-expanded="false">
                <span>View Agent Details</span>
                <i class="fas fa-chevron-down"></i>
            </button>
            
            <div class="hph-details-content">
                <?php if ($agent_bio): ?>
                <div class="hph-agent-bio">
                    <h4>About <?php echo esc_html(explode(' ', $agent_name)[0]); ?></h4>
                    <p><?php echo esc_html($agent_bio); ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($agent_specialties)): ?>
                <div class="hph-agent-specialties">
                    <h4>Specialties</h4>
                    <div class="hph-specialty-tags">
                        <?php foreach ($agent_specialties as $specialty): ?>
                        <span class="hph-tag"><?php echo esc_html($specialty); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($agent_languages)): ?>
                <div class="hph-agent-languages">
                    <h4>Languages</h4>
                    <div class="hph-language-list">
                        <?php echo esc_html(implode(', ', $agent_languages)); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="hph-agent-links">
                    <a href="#" class="hph-link">
                        <i class="fas fa-user-circle"></i>
                        View Full Profile
                    </a>
                    <?php if ($agent_listings_count > 0): ?>
                    <a href="#" class="hph-link">
                        <i class="fas fa-home"></i>
                        View All Listings (<?php echo esc_html($agent_listings_count); ?>)
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
    </div>
    
    <!-- Lead Form -->
    <div class="hph-lead-form-wrapper">
        <div class="hph-form-header">
            <h3 class="hph-form-title"><?php echo esc_html($form_title); ?></h3>
            <?php if ($form_subtitle): ?>
            <p class="hph-form-subtitle"><?php echo esc_html($form_subtitle); ?></p>
            <?php endif; ?>
        </div>
        
        <form id="<?php echo esc_attr($form_id); ?>" class="hph-lead-form" novalidate>
            
            <!-- Form Tabs -->
            <div class="hph-form-tabs">
                <?php if ($show_request_info): ?>
                <button type="button" class="hph-tab-btn active" data-tab="contact">
                    <i class="fas fa-envelope"></i>
                    <span>Contact</span>
                </button>
                <?php endif; ?>
                
                <?php if ($show_schedule_tour): ?>
                <button type="button" class="hph-tab-btn" data-tab="tour">
                    <i class="fas fa-calendar-check"></i>
                    <span>Tour</span>
                </button>
                <?php endif; ?>
                
                <?php if ($show_pre_approval): ?>
                <button type="button" class="hph-tab-btn" data-tab="finance">
                    <i class="fas fa-dollar-sign"></i>
                    <span>Finance</span>
                </button>
                <?php endif; ?>
            </div>
            
            <!-- Contact Tab -->
            <div class="hph-form-tab active" data-tab-content="contact">
                <div class="hph-form-group">
                    <input type="text" 
                           id="lead_name" 
                           name="lead_name" 
                           class="hph-form-input" 
                           placeholder="Your Name *" 
                           required>
                    <span class="hph-error-message"></span>
                </div>
                
                <div class="hph-form-row">
                    <div class="hph-form-group">
                        <input type="email" 
                               id="lead_email" 
                               name="lead_email" 
                               class="hph-form-input" 
                               placeholder="Email *" 
                               required>
                        <span class="hph-error-message"></span>
                    </div>
                    
                    <div class="hph-form-group">
                        <input type="tel" 
                               id="lead_phone" 
                               name="lead_phone" 
                               class="hph-form-input" 
                               placeholder="Phone *" 
                               required>
                        <span class="hph-error-message"></span>
                    </div>
                </div>
                
                <div class="hph-form-group">
                    <textarea id="lead_message" 
                              name="lead_message" 
                              class="hph-form-textarea" 
                              placeholder="Message (Optional)"
                              rows="3"></textarea>
                    <span class="hph-char-count">0/500</span>
                </div>
                
                <!-- Pre-written Messages -->
                <div class="hph-quick-messages">
                    <p class="hph-quick-label">Quick message:</p>
                    <div class="hph-quick-options">
                        <button type="button" class="hph-quick-msg" data-message="I'm interested in <?php echo esc_attr($property_address ?: 'this property'); ?>. Please send me more information.">
                            Request Info
                        </button>
                        <button type="button" class="hph-quick-msg" data-message="I would like to schedule a showing for <?php echo esc_attr($property_address ?: 'this property'); ?>.">
                            Schedule Showing
                        </button>
                        <button type="button" class="hph-quick-msg" data-message="Is <?php echo esc_attr($property_address ?: 'this property'); ?> still available?">
                            Check Availability
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Tour Tab -->
            <?php if ($show_schedule_tour): ?>
            <div class="hph-form-tab" data-tab-content="tour">
                <div class="hph-tour-types">
                    <p class="hph-tour-label">Tour Type:</p>
                    <div class="hph-tour-options">
                        <?php if (in_array('in-person', $tour_types)): ?>
                        <label class="hph-radio-label">
                            <input type="radio" name="tour_type" value="in-person" checked>
                            <span class="hph-radio-custom"></span>
                            <span>In-Person</span>
                        </label>
                        <?php endif; ?>
                        
                        <?php if (in_array('virtual', $tour_types)): ?>
                        <label class="hph-radio-label">
                            <input type="radio" name="tour_type" value="virtual">
                            <span class="hph-radio-custom"></span>
                            <span>Virtual Tour</span>
                        </label>
                        <?php endif; ?>
                        
                        <?php if (in_array('video-chat', $tour_types)): ?>
                        <label class="hph-radio-label">
                            <input type="radio" name="tour_type" value="video-chat">
                            <span class="hph-radio-custom"></span>
                            <span>Video Chat</span>
                        </label>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="hph-form-group">
                    <label for="tour_date" class="hph-form-label">Preferred Date</label>
                    <input type="date" 
                           id="tour_date" 
                           name="tour_date" 
                           class="hph-form-input"
                           min="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="hph-form-group">
                    <label for="tour_time" class="hph-form-label">Preferred Time</label>
                    <select id="tour_time" name="tour_time" class="hph-form-select">
                        <option value="">Select a time</option>
                        <option value="morning">Morning (9am - 12pm)</option>
                        <option value="afternoon">Afternoon (12pm - 5pm)</option>
                        <option value="evening">Evening (5pm - 8pm)</option>
                    </select>
                </div>
                
                <div class="hph-form-group">
                    <label class="hph-checkbox-label">
                        <input type="checkbox" name="tour_reminder" value="1">
                        <span class="hph-checkbox-custom"></span>
                        <span>Send me a reminder</span>
                    </label>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Finance Tab -->
            <?php if ($show_pre_approval): ?>
            <div class="hph-form-tab" data-tab-content="finance">
                <div class="hph-finance-intro">
                    <i class="fas fa-shield-alt"></i>
                    <p>Get pre-approved to strengthen your offer and know your budget.</p>
                </div>
                
                <div class="hph-form-group">
                    <label for="price_range" class="hph-form-label">Price Range</label>
                    <select id="price_range" name="price_range" class="hph-form-select">
                        <option value="">Select price range</option>
                        <option value="0-250000">Under $250,000</option>
                        <option value="250000-500000">$250,000 - $500,000</option>
                        <option value="500000-750000">$500,000 - $750,000</option>
                        <option value="750000-1000000">$750,000 - $1,000,000</option>
                        <option value="1000000+">Over $1,000,000</option>
                    </select>
                </div>
                
                <div class="hph-form-group">
                    <label for="purchase_timeline" class="hph-form-label">Purchase Timeline</label>
                    <select id="purchase_timeline" name="purchase_timeline" class="hph-form-select">
                        <option value="">Select timeline</option>
                        <option value="asap">ASAP</option>
                        <option value="1-3months">1-3 months</option>
                        <option value="3-6months">3-6 months</option>
                        <option value="6months+">6+ months</option>
                        <option value="researching">Just researching</option>
                    </select>
                </div>
                
                <div class="hph-form-group">
                    <label class="hph-checkbox-label">
                        <input type="checkbox" name="first_time_buyer" value="1">
                        <span class="hph-checkbox-custom"></span>
                        <span>I'm a first-time buyer</span>
                    </label>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Hidden Fields -->
            <input type="hidden" name="property_id" value="<?php echo esc_attr($property_id); ?>">
            <input type="hidden" name="property_address" value="<?php echo esc_attr($property_address); ?>">
            <input type="hidden" name="property_mls" value="<?php echo esc_attr($property_mls); ?>">
            <input type="hidden" name="agent_id" value="<?php echo esc_attr($agent_id); ?>">
            <input type="hidden" name="agent_email" value="<?php echo esc_attr($agent_email); ?>">
            <input type="hidden" name="form_type" value="contact">
            
            <!-- GDPR Consent -->
            <div class="hph-form-consent">
                <label class="hph-checkbox-label">
                    <input type="checkbox" name="consent" value="1" required>
                    <span class="hph-checkbox-custom"></span>
                    <span class="hph-consent-text">
                        I agree to be contacted about this property and receive marketing communications. 
                        <a href="#" target="_blank">Privacy Policy</a>
                    </span>
                </label>
                <span class="hph-error-message"></span>
            </div>
            
            <!-- Submit Button -->
            <button type="submit" class="hph-submit-btn">
                <span class="hph-btn-text">Send Message</span>
                <span class="hph-btn-loading" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i> Sending...
                </span>
            </button>
            
            <!-- Success/Error Messages -->
            <div class="hph-form-message hph-success-message" style="display: none;">
                <i class="fas fa-check-circle"></i>
                <span>Thank you! Your message has been sent.</span>
            </div>
            
            <div class="hph-form-message hph-error-message" style="display: none;">
                <i class="fas fa-exclamation-circle"></i>
                <span>Something went wrong. Please try again.</span>
            </div>
            
        </form>
        
        <!-- Alternative Contact -->
        <div class="hph-alternative-contact">
            <p class="hph-alt-text">Prefer to talk now?</p>
            <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9]/', '', $agent_phone)); ?>" 
               class="hph-alt-phone">
                <i class="fas fa-phone"></i>
                <?php echo esc_html($agent_phone); ?>
            </a>
        </div>
        
    </div>
    
    <!-- Response Time Badge -->
    <div class="hph-response-badge">
        <i class="fas fa-bolt"></i>
        <span>Typical response time: <strong>Under 1 hour</strong></span>
    </div>
    
</div>

<?php if ($position === 'modal'): ?>
<!-- Modal Wrapper -->
<div class="hph-agent-modal-overlay" id="agent-modal-overlay">
    <div class="hph-agent-modal-content">
        <button class="hph-modal-close" aria-label="Close">
            <i class="fas fa-times"></i>
        </button>
        <!-- Agent card content will be moved here via JS -->
    </div>
</div>
<?php endif; ?>