<?php
/**
 * Showing Request Form Template
 * Specialized form for scheduling property showings with Calendly integration
 * Designed for property pages, modal contexts, and dedicated showing pages
 * 
 * @package HappyPlaceTheme
 */

// Extract arguments
$args = wp_parse_args($args, [
    'listing_id' => 0,
    'variant' => 'default',
    'modal_context' => false,
    'agent_id' => 0,
    'title' => __('Schedule a Showing', 'happy-place-theme'),
    'description' => __('Book a private tour of this property at your convenience.', 'happy-place-theme'),
    'submit_text' => __('Request Showing', 'happy-place-theme'),
    'calendly_enabled' => true,
    'calendly_priority' => true, // Show Calendly option prominently
    'show_property_details' => true,
    'show_agent_info' => true,
    'allow_group_showings' => true,
    'css_classes' => ''
]);

// Get property data if listing_id provided
$property_data = null;
$agent_data = null;
if ($args['listing_id']) {
    $property_data = [
        'title' => get_the_title($args['listing_id']),
        'address' => get_field('street_address', $args['listing_id']),
        'city' => get_field('city', $args['listing_id']),
        'state' => get_field('state', $args['listing_id']),
        'price' => get_field('price', $args['listing_id']),
        'bedrooms' => get_field('bedrooms', $args['listing_id']),
        'bathrooms_full' => get_field('bathrooms_full', $args['listing_id']),
        'square_feet' => get_field('square_feet', $args['listing_id']),
        'featured_image' => get_the_post_thumbnail_url($args['listing_id'], 'medium'),
        'agent_id' => get_field('listing_agent', $args['listing_id']) ?: $args['agent_id']
    ];
    
    // Get agent information
    if ($property_data['agent_id']) {
        $agent_data = [
            'name' => get_the_title($property_data['agent_id']),
            'phone' => get_field('phone', $property_data['agent_id']),
            'email' => get_field('email', $property_data['agent_id']),
            'photo' => get_the_post_thumbnail_url($property_data['agent_id'], 'thumbnail'),
            'specialties' => get_field('specialties', $property_data['agent_id'])
        ];
    }
}

// Form classes
$form_classes = ['hph-form', 'hph-showing-request-form'];
if ($args['variant'] === 'compact') $form_classes[] = 'hph-form--compact';
if ($args['variant'] === 'modern') $form_classes[] = 'hph-form--modern';
if ($args['modal_context']) $form_classes[] = 'hph-form--modal';
if ($args['css_classes']) $form_classes[] = $args['css_classes'];

// Determine route type
$route_type = $args['calendly_enabled'] ? 'showing_with_booking' : 'showing_request';
?>

<div class="hph-showing-form-container">
    <?php if (!$args['modal_context']): ?>
    <!-- Form Header -->
    <div class="hph-form-header">
        <h3 class="hph-form-title"><?php echo esc_html($args['title']); ?></h3>
        <?php if ($args['description']): ?>
        <p class="hph-form-description"><?php echo esc_html($args['description']); ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($args['show_property_details'] && $property_data): ?>
    <!-- Property Details Card -->
    <div class="hph-property-showcase">
        <div class="hph-property-showcase-card">
            <?php if ($property_data['featured_image']): ?>
            <div class="hph-property-image">
                <img src="<?php echo esc_url($property_data['featured_image']); ?>" 
                     alt="<?php echo esc_attr($property_data['title']); ?>"
                     class="hph-property-photo">
            </div>
            <?php endif; ?>
            
            <div class="hph-property-summary">
                <h4 class="hph-property-title"><?php echo esc_html($property_data['title']); ?></h4>
                
                <?php if ($property_data['address']): ?>
                <p class="hph-property-address">
                    <i class="fas fa-map-marker-alt"></i>
                    <?php echo esc_html($property_data['address']); ?>
                    <?php if ($property_data['city']): ?>
                        , <?php echo esc_html($property_data['city']); ?>
                    <?php endif; ?>
                    <?php if ($property_data['state']): ?>
                        , <?php echo esc_html($property_data['state']); ?>
                    <?php endif; ?>
                </p>
                <?php endif; ?>
                
                <div class="hph-property-highlights">
                    <?php if ($property_data['price']): ?>
                    <span class="hph-property-price">$<?php echo number_format($property_data['price']); ?></span>
                    <?php endif; ?>
                    
                    <div class="hph-property-specs">
                        <?php if ($property_data['bedrooms']): ?>
                        <span class="hph-spec">
                            <i class="fas fa-bed"></i>
                            <?php echo $property_data['bedrooms']; ?> bed<?php echo $property_data['bedrooms'] > 1 ? 's' : ''; ?>
                        </span>
                        <?php endif; ?>
                        
                        <?php if ($property_data['bathrooms_full']): ?>
                        <span class="hph-spec">
                            <i class="fas fa-bath"></i>
                            <?php echo $property_data['bathrooms_full']; ?> bath<?php echo $property_data['bathrooms_full'] > 1 ? 's' : ''; ?>
                        </span>
                        <?php endif; ?>
                        
                        <?php if ($property_data['square_feet']): ?>
                        <span class="hph-spec">
                            <i class="fas fa-ruler-combined"></i>
                            <?php echo number_format($property_data['square_feet']); ?> sq ft
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($args['show_agent_info'] && $agent_data): ?>
    <!-- Agent Information -->
    <div class="hph-agent-info">
        <div class="hph-agent-card">
            <?php if ($agent_data['photo']): ?>
            <div class="hph-agent-photo">
                <img src="<?php echo esc_url($agent_data['photo']); ?>" 
                     alt="<?php echo esc_attr($agent_data['name']); ?>"
                     class="hph-agent-avatar">
            </div>
            <?php endif; ?>
            
            <div class="hph-agent-details">
                <h5 class="hph-agent-name"><?php echo esc_html($agent_data['name']); ?></h5>
                <p class="hph-agent-title"><?php _e('Listing Agent', 'happy-place-theme'); ?></p>
                
                <div class="hph-agent-contact">
                    <?php if ($agent_data['phone']): ?>
                    <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $agent_data['phone'])); ?>" 
                       class="hph-agent-contact-link">
                        <i class="fas fa-phone"></i>
                        <?php echo esc_html($agent_data['phone']); ?>
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($agent_data['email']): ?>
                    <a href="mailto:<?php echo esc_attr($agent_data['email']); ?>" 
                       class="hph-agent-contact-link">
                        <i class="fas fa-envelope"></i>
                        <?php echo esc_html($agent_data['email']); ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="hph-showing-options">
        <?php if ($args['calendly_enabled'] && $args['calendly_priority']): ?>
        <!-- Calendly Booking Option (Primary) -->
        <div class="hph-booking-option hph-booking-primary">
            <div class="hph-booking-header">
                <div class="hph-booking-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="hph-booking-content">
                    <h4 class="hph-booking-title"><?php _e('Book Instantly', 'happy-place-theme'); ?></h4>
                    <p class="hph-booking-description">
                        <?php _e('Choose your preferred time from our available slots and book your showing immediately.', 'happy-place-theme'); ?>
                    </p>
                </div>
            </div>
            
            <div class="hph-booking-actions">
                <button type="button" class="hph-btn hph-btn-primary hph-btn-lg hph-calendly-trigger"
                        data-property-id="<?php echo esc_attr($args['listing_id']); ?>"
                        data-agent-id="<?php echo esc_attr($agent_data['agent_id'] ?? $args['agent_id']); ?>">
                    <i class="fas fa-calendar-plus"></i>
                    <?php _e('View Available Times', 'happy-place-theme'); ?>
                </button>
            </div>
        </div>

        <div class="hph-option-divider">
            <span><?php _e('or', 'happy-place-theme'); ?></span>
        </div>
        <?php endif; ?>

        <!-- Request Form Option -->
        <div class="hph-booking-option hph-booking-secondary">
            <div class="hph-booking-header">
                <div class="hph-booking-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="hph-booking-content">
                    <h4 class="hph-booking-title"><?php _e('Request Custom Time', 'happy-place-theme'); ?></h4>
                    <p class="hph-booking-description">
                        <?php _e('Submit a showing request and we\'ll coordinate a time that works for you.', 'happy-place-theme'); ?>
                    </p>
                </div>
            </div>

            <!-- Showing Request Form -->
            <form 
                class="<?php echo implode(' ', $form_classes); ?>" 
                data-route-type="<?php echo esc_attr($route_type); ?>"
                data-property-id="<?php echo esc_attr($args['listing_id']); ?>"
                data-agent-id="<?php echo esc_attr($agent_data['agent_id'] ?? $args['agent_id']); ?>"
            >
                <?php wp_nonce_field('hph_showing_request', 'showing_nonce'); ?>
                
                <!-- Hidden Fields -->
                <input type="hidden" name="form_type" value="showing_request">
                <input type="hidden" name="property_id" value="<?php echo esc_attr($args['listing_id']); ?>">
                <input type="hidden" name="agent_id" value="<?php echo esc_attr($agent_data['agent_id'] ?? $args['agent_id']); ?>">
                <input type="hidden" name="property_title" value="<?php echo esc_attr($property_data['title'] ?? ''); ?>">
                <input type="hidden" name="source_url" value="<?php echo esc_url(get_permalink()); ?>">

                <div class="hph-form-row">
                    <!-- First Name -->
                    <div class="hph-form-group hph-form-col--half">
                        <label for="showing-first-name" class="hph-form-label">
                            <?php _e('First Name', 'happy-place-theme'); ?>
                            <span class="hph-required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="showing-first-name" 
                            name="first_name" 
                            class="hph-form-input" 
                            required 
                            placeholder="<?php _e('John', 'happy-place-theme'); ?>"
                        >
                    </div>

                    <!-- Last Name -->
                    <div class="hph-form-group hph-form-col--half">
                        <label for="showing-last-name" class="hph-form-label">
                            <?php _e('Last Name', 'happy-place-theme'); ?>
                            <span class="hph-required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="showing-last-name" 
                            name="last_name" 
                            class="hph-form-input" 
                            required 
                            placeholder="<?php _e('Smith', 'happy-place-theme'); ?>"
                        >
                    </div>
                </div>

                <div class="hph-form-row">
                    <!-- Email -->
                    <div class="hph-form-group hph-form-col--half">
                        <label for="showing-email" class="hph-form-label">
                            <?php _e('Email', 'happy-place-theme'); ?>
                            <span class="hph-required">*</span>
                        </label>
                        <input 
                            type="email" 
                            id="showing-email" 
                            name="email" 
                            class="hph-form-input" 
                            required 
                            placeholder="<?php _e('john@example.com', 'happy-place-theme'); ?>"
                        >
                    </div>

                    <!-- Phone -->
                    <div class="hph-form-group hph-form-col--half">
                        <label for="showing-phone" class="hph-form-label">
                            <?php _e('Phone', 'happy-place-theme'); ?>
                            <span class="hph-required">*</span>
                        </label>
                        <input 
                            type="tel" 
                            id="showing-phone" 
                            name="phone" 
                            class="hph-form-input" 
                            required 
                            placeholder="<?php _e('(555) 123-4567', 'happy-place-theme'); ?>"
                        >
                    </div>
                </div>

                <div class="hph-form-row">
                    <!-- Preferred Date -->
                    <div class="hph-form-group hph-form-col--half">
                        <label for="preferred-date" class="hph-form-label">
                            <?php _e('Preferred Date', 'happy-place-theme'); ?>
                        </label>
                        <input 
                            type="date" 
                            id="preferred-date" 
                            name="preferred_date" 
                            class="hph-form-input"
                            min="<?php echo date('Y-m-d'); ?>"
                            max="<?php echo date('Y-m-d', strtotime('+90 days')); ?>"
                        >
                    </div>

                    <!-- Preferred Time -->
                    <div class="hph-form-group hph-form-col--half">
                        <label for="preferred-time" class="hph-form-label">
                            <?php _e('Preferred Time', 'happy-place-theme'); ?>
                        </label>
                        <select id="preferred-time" name="preferred_time" class="hph-form-select">
                            <option value=""><?php _e('Any time', 'happy-place-theme'); ?></option>
                            <option value="morning"><?php _e('Morning (9am - 12pm)', 'happy-place-theme'); ?></option>
                            <option value="afternoon"><?php _e('Afternoon (12pm - 5pm)', 'happy-place-theme'); ?></option>
                            <option value="evening"><?php _e('Evening (5pm - 8pm)', 'happy-place-theme'); ?></option>
                            <option value="weekend"><?php _e('Weekend Only', 'happy-place-theme'); ?></option>
                        </select>
                    </div>
                </div>

                <!-- Number of Attendees -->
                <div class="hph-form-group">
                    <label for="attendees" class="hph-form-label">
                        <?php _e('Number of People Attending', 'happy-place-theme'); ?>
                    </label>
                    <select id="attendees" name="attendees" class="hph-form-select">
                        <option value="1">1 <?php _e('person', 'happy-place-theme'); ?></option>
                        <option value="2" selected>2 <?php _e('people', 'happy-place-theme'); ?></option>
                        <option value="3">3 <?php _e('people', 'happy-place-theme'); ?></option>
                        <option value="4">4 <?php _e('people', 'happy-place-theme'); ?></option>
                        <option value="5+">5+ <?php _e('people', 'happy-place-theme'); ?></option>
                    </select>
                </div>

                <!-- Showing Type -->
                <div class="hph-form-group">
                    <label class="hph-form-label">
                        <?php _e('Showing Preference', 'happy-place-theme'); ?>
                    </label>
                    <div class="hph-radio-group">
                        <label class="hph-form-check">
                            <input type="radio" name="showing_type" value="private" class="hph-form-check-input" checked>
                            <span class="hph-form-check-label">
                                <strong><?php _e('Private Showing', 'happy-place-theme'); ?></strong>
                                <small><?php _e('Just for you and your group', 'happy-place-theme'); ?></small>
                            </span>
                        </label>
                        <?php if ($args['allow_group_showings']): ?>
                        <label class="hph-form-check">
                            <input type="radio" name="showing_type" value="open" class="hph-form-check-input">
                            <span class="hph-form-check-label">
                                <strong><?php _e('Open House', 'happy-place-theme'); ?></strong>
                                <small><?php _e('Join an existing open house', 'happy-place-theme'); ?></small>
                            </span>
                        </label>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Special Requirements -->
                <div class="hph-form-group">
                    <label for="special-requirements" class="hph-form-label">
                        <?php _e('Special Requirements or Questions', 'happy-place-theme'); ?>
                    </label>
                    <textarea 
                        id="special-requirements" 
                        name="special_requirements" 
                        class="hph-form-textarea" 
                        rows="3" 
                        placeholder="<?php _e('Any accessibility needs, specific areas of interest, or questions about the property...', 'happy-place-theme'); ?>"
                    ></textarea>
                </div>

                <!-- Pre-qualification Status -->
                <div class="hph-form-group">
                    <label for="qualification-status" class="hph-form-label">
                        <?php _e('Financing Status', 'happy-place-theme'); ?>
                    </label>
                    <select id="qualification-status" name="qualification_status" class="hph-form-select">
                        <option value=""><?php _e('Prefer not to say', 'happy-place-theme'); ?></option>
                        <option value="pre-approved"><?php _e('Pre-approved for mortgage', 'happy-place-theme'); ?></option>
                        <option value="cash-buyer"><?php _e('Cash buyer', 'happy-place-theme'); ?></option>
                        <option value="need-pre-approval"><?php _e('Need pre-approval assistance', 'happy-place-theme'); ?></option>
                        <option value="exploring"><?php _e('Still exploring options', 'happy-place-theme'); ?></option>
                    </select>
                </div>

                <!-- Timeline -->
                <div class="hph-form-group">
                    <label for="buying-timeline" class="hph-form-label">
                        <?php _e('Buying Timeline', 'happy-place-theme'); ?>
                    </label>
                    <select id="buying-timeline" name="buying_timeline" class="hph-form-select">
                        <option value=""><?php _e('Select timeline', 'happy-place-theme'); ?></option>
                        <option value="immediate"><?php _e('Ready to buy immediately', 'happy-place-theme'); ?></option>
                        <option value="30-days"><?php _e('Within 30 days', 'happy-place-theme'); ?></option>
                        <option value="90-days"><?php _e('Within 3 months', 'happy-place-theme'); ?></option>
                        <option value="6-months"><?php _e('Within 6 months', 'happy-place-theme'); ?></option>
                        <option value="exploring"><?php _e('Just browsing', 'happy-place-theme'); ?></option>
                    </select>
                </div>

                <!-- Form Actions -->
                <div class="hph-form-buttons">
                    <button type="submit" class="hph-btn hph-btn-primary hph-btn-full">
                        <i class="fas fa-calendar-plus"></i>
                        <?php echo esc_html($args['submit_text']); ?>
                    </button>
                </div>

                <!-- Loading State -->
                <div class="hph-form-loading" style="display: none;">
                    <div class="hph-loading-spinner"></div>
                    <span><?php _e('Processing your showing request...', 'happy-place-theme'); ?></span>
                </div>

                <!-- Success Message -->
                <div class="hph-form-success" style="display: none;">
                    <div class="hph-success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h4><?php _e('Showing Request Submitted!', 'happy-place-theme'); ?></h4>
                    <p><?php _e('We\'ll contact you within 2 hours to confirm your showing appointment.', 'happy-place-theme'); ?></p>
                    <div class="hph-calendly-link-container" style="display: none;">
                        <a href="#" class="hph-btn hph-btn-primary hph-calendly-link">
                            <i class="fas fa-calendar-alt"></i>
                            <?php _e('Or Schedule Online Now', 'happy-place-theme'); ?>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Inline CSS for showing-specific styles -->
<style>
.hph-showing-form-container {
    max-width: 600px;
    margin: 0 auto;
}

.hph-property-showcase {
    margin-bottom: 2rem;
}

.hph-property-showcase-card {
    display: flex;
    background: var(--hph-white);
    border: 1px solid var(--hph-border-color-light);
    border-radius: var(--hph-radius-lg);
    overflow: hidden;
    box-shadow: var(--hph-shadow-sm);
}

.hph-property-image {
    flex-shrink: 0;
    width: 140px;
    height: 100px;
    overflow: hidden;
}

.hph-property-photo {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.hph-property-summary {
    padding: 1rem;
    flex: 1;
}

.hph-property-title {
    font-size: var(--hph-text-lg);
    font-weight: 600;
    color: var(--hph-gray-900);
    margin: 0 0 0.5rem 0;
    line-height: 1.2;
}

.hph-property-address {
    font-size: var(--hph-text-sm);
    color: var(--hph-gray-600);
    margin: 0 0 0.75rem 0;
}

.hph-property-address i {
    color: var(--hph-primary);
    margin-right: 0.25rem;
}

.hph-property-highlights {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.hph-property-price {
    font-weight: 700;
    color: var(--hph-primary);
    font-size: var(--hph-text-lg);
}

.hph-property-specs {
    display: flex;
    gap: 0.75rem;
    font-size: var(--hph-text-xs);
    color: var(--hph-gray-700);
}

.hph-spec {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.hph-spec i {
    color: var(--hph-gray-500);
    font-size: 0.875rem;
}

.hph-agent-info {
    margin-bottom: 2rem;
}

.hph-agent-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--hph-gray-50);
    border: 1px solid var(--hph-border-color-light);
    border-radius: var(--hph-radius-md);
}

.hph-agent-photo {
    flex-shrink: 0;
}

.hph-agent-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--hph-white);
    box-shadow: var(--hph-shadow-sm);
}

.hph-agent-name {
    font-size: var(--hph-text-base);
    font-weight: 600;
    color: var(--hph-gray-900);
    margin: 0 0 0.25rem 0;
}

.hph-agent-title {
    font-size: var(--hph-text-sm);
    color: var(--hph-gray-600);
    margin: 0 0 0.5rem 0;
}

.hph-agent-contact {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.hph-agent-contact-link {
    font-size: var(--hph-text-sm);
    color: var(--hph-gray-700);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.hph-agent-contact-link:hover {
    color: var(--hph-primary);
}

.hph-agent-contact-link i {
    width: 14px;
    color: var(--hph-primary);
}

.hph-showing-options {
    space-y: 2rem;
}

.hph-booking-option {
    border: 1px solid var(--hph-border-color-light);
    border-radius: var(--hph-radius-lg);
    overflow: hidden;
}

.hph-booking-primary {
    background: linear-gradient(135deg, var(--hph-primary-50) 0%, var(--hph-primary-100) 100%);
    border-color: var(--hph-primary-200);
}

.hph-booking-secondary {
    background: var(--hph-white);
    margin-top: 2rem;
}

.hph-booking-header {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1.5rem;
}

.hph-booking-primary .hph-booking-header {
    padding-bottom: 1rem;
}

.hph-booking-icon {
    flex-shrink: 0;
    width: 3rem;
    height: 3rem;
    background: var(--hph-primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.hph-booking-title {
    font-size: var(--hph-text-xl);
    font-weight: 600;
    color: var(--hph-gray-900);
    margin: 0 0 0.5rem 0;
}

.hph-booking-primary .hph-booking-title {
    color: var(--hph-primary-800);
}

.hph-booking-description {
    font-size: var(--hph-text-sm);
    color: var(--hph-gray-600);
    margin: 0;
    line-height: 1.5;
}

.hph-booking-primary .hph-booking-description {
    color: var(--hph-primary-700);
}

.hph-booking-actions {
    padding: 0 1.5rem 1.5rem 1.5rem;
}

.hph-option-divider {
    text-align: center;
    position: relative;
    margin: 2rem 0;
}

.hph-option-divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: var(--hph-border-color-light);
}

.hph-option-divider span {
    background: var(--hph-white);
    color: var(--hph-gray-500);
    padding: 0 1rem;
    font-size: var(--hph-text-sm);
    font-weight: 500;
    position: relative;
    z-index: 1;
}

.hph-radio-group {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.hph-form-check {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    cursor: pointer;
}

.hph-form-check-input {
    margin-top: 0.125rem;
}

.hph-form-check-label {
    flex: 1;
}

.hph-form-check-label strong {
    display: block;
    color: var(--hph-gray-900);
    margin-bottom: 0.25rem;
}

.hph-form-check-label small {
    color: var(--hph-gray-600);
    font-size: var(--hph-text-xs);
}

@media (max-width: 768px) {
    .hph-property-showcase-card {
        flex-direction: column;
    }
    
    .hph-property-image {
        width: 100%;
        height: 200px;
    }
    
    .hph-property-highlights {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .hph-agent-card {
        flex-direction: column;
        text-align: center;
    }
    
    .hph-booking-header {
        flex-direction: column;
        text-align: center;
    }
    
    .hph-booking-icon {
        align-self: center;
    }
}
</style>
