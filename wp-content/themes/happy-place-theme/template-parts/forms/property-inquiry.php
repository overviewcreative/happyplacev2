<?php
/**
 * Property Inquiry Form Template
 * Integrated with FormRouter system for unified handling
 * Designed for inline, modal, and page contexts
 * 
 * @package HappyPlaceTheme
 */

// Extract arguments
$args = wp_parse_args($args, [
    'listing_id' => 0,
    'variant' => 'default',
    'modal_context' => false,
    'agent_id' => 0,
    'show_property_details' => true,
    'title' => __('Inquire About This Property', 'happy-place-theme'),
    'description' => __('Get more information about this property or schedule a viewing.', 'happy-place-theme'),
    'submit_text' => __('Send Inquiry', 'happy-place-theme'),
    'calendly_enabled' => true,
    'css_classes' => ''
]);

// Get property data if listing_id provided
$property_data = null;
if ($args['listing_id']) {
    $property_data = [
        'title' => get_the_title($args['listing_id']),
        'address' => get_field('street_address', $args['listing_id']),
        'price' => get_field('price', $args['listing_id']),
        'bedrooms' => get_field('bedrooms', $args['listing_id']),
        'bathrooms_full' => get_field('bathrooms_full', $args['listing_id']),
        'agent_id' => get_field('listing_agent', $args['listing_id']) ?: $args['agent_id']
    ];
}

// Form classes
$form_classes = ['hph-form', 'hph-property-inquiry-form'];
if ($args['variant'] === 'compact') $form_classes[] = 'hph-form--compact';
if ($args['variant'] === 'modern') $form_classes[] = 'hph-form--modern';
if ($args['modal_context']) $form_classes[] = 'hph-form--modal';
if ($args['css_classes']) $form_classes[] = $args['css_classes'];

// Determine route type based on configuration
$route_type = 'inquiry';
if ($args['calendly_enabled'] && $property_data) {
    $route_type = 'inquiry_with_booking';
}
?>

<div class="hph-inquiry-form-container">
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
    <div class="hph-property-context">
        <div class="hph-property-mini-card">
            <div class="hph-property-info">
                <h4 class="hph-property-title"><?php echo esc_html($property_data['title']); ?></h4>
                <?php if ($property_data['address']): ?>
                <p class="hph-property-address">
                    <i class="fas fa-map-marker-alt"></i>
                    <?php echo esc_html($property_data['address']); ?>
                </p>
                <?php endif; ?>
                <div class="hph-property-details">
                    <?php if ($property_data['price']): ?>
                    <span class="hph-property-price">$<?php echo number_format($property_data['price']); ?></span>
                    <?php endif; ?>
                    <?php if ($property_data['bedrooms'] || $property_data['bathrooms_full']): ?>
                    <span class="hph-property-specs">
                        <?php if ($property_data['bedrooms']): ?>
                            <?php echo $property_data['bedrooms']; ?> bed
                        <?php endif; ?>
                        <?php if ($property_data['bedrooms'] && $property_data['bathrooms_full']): ?>•<?php endif; ?>
                        <?php if ($property_data['bathrooms_full']): ?>
                            <?php echo $property_data['bathrooms_full']; ?> bath
                        <?php endif; ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Inquiry Form -->
    <form 
        class="<?php echo implode(' ', $form_classes); ?>" 
        data-route-type="property_inquiry"
        data-property-id="<?php echo esc_attr($args['listing_id']); ?>"
        data-agent-id="<?php echo esc_attr($property_data['agent_id'] ?? $args['agent_id']); ?>"
        method="post"
        action="<?php echo admin_url('admin-ajax.php'); ?>"
    >
        <?php wp_nonce_field('hph_property_inquiry', 'inquiry_nonce'); ?>
        
        <!-- Hidden Fields -->
        <input type="hidden" name="form_type" value="property_inquiry">
        <input type="hidden" name="property_id" value="<?php echo esc_attr($args['listing_id']); ?>">
        <input type="hidden" name="agent_id" value="<?php echo esc_attr($property_data['agent_id'] ?? $args['agent_id']); ?>">
        <input type="hidden" name="property_title" value="<?php echo esc_attr($property_data['title'] ?? ''); ?>">
        <input type="hidden" name="source_url" value="<?php echo esc_url(get_permalink()); ?>">

        <div class="hph-form-row">
            <!-- First Name -->
            <div class="hph-form-group hph-form-col--half">
                <label for="inquiry-first-name" class="hph-form-label">
                    <?php _e('First Name', 'happy-place-theme'); ?>
                    <span class="hph-required">*</span>
                </label>
                <input 
                    type="text" 
                    id="inquiry-first-name" 
                    name="first_name" 
                    class="hph-form-input" 
                    required 
                    placeholder="<?php _e('John', 'happy-place-theme'); ?>"
                >
            </div>

            <!-- Last Name -->
            <div class="hph-form-group hph-form-col--half">
                <label for="inquiry-last-name" class="hph-form-label">
                    <?php _e('Last Name', 'happy-place-theme'); ?>
                    <span class="hph-required">*</span>
                </label>
                <input 
                    type="text" 
                    id="inquiry-last-name" 
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
                <label for="inquiry-email" class="hph-form-label">
                    <?php _e('Email', 'happy-place-theme'); ?>
                    <span class="hph-required">*</span>
                </label>
                <input 
                    type="email" 
                    id="inquiry-email" 
                    name="email" 
                    class="hph-form-input" 
                    required 
                    placeholder="<?php _e('john@example.com', 'happy-place-theme'); ?>"
                >
            </div>

            <!-- Phone -->
            <div class="hph-form-group hph-form-col--half">
                <label for="inquiry-phone" class="hph-form-label">
                    <?php _e('Phone', 'happy-place-theme'); ?>
                    <span class="hph-required">*</span>
                </label>
                <input 
                    type="tel" 
                    id="inquiry-phone" 
                    name="phone" 
                    class="hph-form-input" 
                    required 
                    placeholder="<?php _e('(555) 123-4567', 'happy-place-theme'); ?>"
                >
            </div>
        </div>

        <!-- Inquiry Type -->
        <div class="hph-form-group">
            <label for="inquiry-type" class="hph-form-label">
                <?php _e('I am interested in', 'happy-place-theme'); ?>
            </label>
            <select id="inquiry-type" name="inquiry_type" class="hph-form-select">
                <option value="general"><?php _e('General Information', 'happy-place-theme'); ?></option>
                <option value="showing"><?php _e('Scheduling a Showing', 'happy-place-theme'); ?></option>
                <option value="offer"><?php _e('Making an Offer', 'happy-place-theme'); ?></option>
                <option value="financing"><?php _e('Financing Options', 'happy-place-theme'); ?></option>
                <option value="neighborhood"><?php _e('Neighborhood Information', 'happy-place-theme'); ?></option>
                <option value="other"><?php _e('Other', 'happy-place-theme'); ?></option>
            </select>
        </div>

        <!-- Message -->
        <div class="hph-form-group">
            <label for="inquiry-message" class="hph-form-label">
                <?php _e('Message', 'happy-place-theme'); ?>
            </label>
            <textarea 
                id="inquiry-message" 
                name="message" 
                class="hph-form-textarea" 
                rows="4" 
                placeholder="<?php _e('Tell us more about your interest in this property...', 'happy-place-theme'); ?>"
            ></textarea>
            <div class="hph-form-text">
                <?php _e('Optional: Include any specific questions or requests', 'happy-place-theme'); ?>
            </div>
        </div>

        <!-- Timeline -->
        <div class="hph-form-group">
            <label for="timeline" class="hph-form-label">
                <?php _e('Timeline', 'happy-place-theme'); ?>
            </label>
            <select id="timeline" name="timeline" class="hph-form-select">
                <option value=""><?php _e('Select your timeline', 'happy-place-theme'); ?></option>
                <option value="asap"><?php _e('As soon as possible', 'happy-place-theme'); ?></option>
                <option value="1-month"><?php _e('Within 1 month', 'happy-place-theme'); ?></option>
                <option value="3-months"><?php _e('Within 3 months', 'happy-place-theme'); ?></option>
                <option value="6-months"><?php _e('Within 6 months', 'happy-place-theme'); ?></option>
                <option value="exploring"><?php _e('Just exploring', 'happy-place-theme'); ?></option>
            </select>
        </div>

        <!-- Communication Preferences -->
        <div class="hph-form-group">
            <label class="hph-form-label">
                <?php _e('Preferred Contact Method', 'happy-place-theme'); ?>
            </label>
            <div class="hph-checkbox-group">
                <label class="hph-form-check">
                    <input type="checkbox" name="contact_methods[]" value="email" class="hph-form-check-input" checked>
                    <span class="hph-form-check-label"><?php _e('Email', 'happy-place-theme'); ?></span>
                </label>
                <label class="hph-form-check">
                    <input type="checkbox" name="contact_methods[]" value="phone" class="hph-form-check-input">
                    <span class="hph-form-check-label"><?php _e('Phone Call', 'happy-place-theme'); ?></span>
                </label>
                <label class="hph-form-check">
                    <input type="checkbox" name="contact_methods[]" value="text" class="hph-form-check-input">
                    <span class="hph-form-check-label"><?php _e('Text Message', 'happy-place-theme'); ?></span>
                </label>
            </div>
        </div>

        <?php if ($args['calendly_enabled']): ?>
        <!-- Calendly Integration Notice -->
        <div class="hph-form-group">
            <div class="hph-info-box">
                <i class="fas fa-calendar-check"></i>
                <div class="hph-info-content">
                    <strong><?php _e('Schedule a Showing', 'happy-place-theme'); ?></strong>
                    <p><?php _e('After submitting this form, you\'ll have the option to schedule a viewing directly from our calendar.', 'happy-place-theme'); ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Form Actions -->
        <div class="hph-form-buttons">
            <button type="submit" class="hph-btn hph-btn-primary hph-btn-full">
                <i class="fas fa-paper-plane"></i>
                <?php echo esc_html($args['submit_text']); ?>
            </button>
            
            <?php if ($args['calendly_enabled']): ?>
            <button type="button" class="hph-btn hph-btn-outline hph-schedule-direct">
                <i class="fas fa-calendar-alt"></i>
                <?php _e('Schedule Showing Directly', 'happy-place-theme'); ?>
            </button>
            <?php endif; ?>
        </div>

        <!-- Loading State -->
        <div class="hph-form-loading" style="display: none;">
            <div class="hph-loading-spinner"></div>
            <span><?php _e('Sending your inquiry...', 'happy-place-theme'); ?></span>
        </div>

        <!-- Success Message -->
        <div class="hph-form-success" style="display: none;">
            <div class="hph-success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h4><?php _e('Inquiry Sent Successfully!', 'happy-place-theme'); ?></h4>
            <p><?php _e('Thank you for your interest. We\'ll get back to you shortly.', 'happy-place-theme'); ?></p>
            <?php if ($args['calendly_enabled']): ?>
            <div class="hph-calendly-link-container" style="display: none;">
                <a href="#" class="hph-btn hph-btn-primary hph-calendly-link">
                    <i class="fas fa-calendar-alt"></i>
                    <?php _e('Schedule Your Showing Now', 'happy-place-theme'); ?>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Inline CSS for form-specific styles -->
<style>
.hph-property-context {
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: var(--hph-gray-50);
    border-radius: var(--hph-radius-md);
    border: 1px solid var(--hph-border-color-light);
}

.hph-property-mini-card {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.hph-property-title {
    font-size: var(--hph-text-lg);
    font-weight: 600;
    color: var(--hph-gray-900);
    margin: 0 0 0.25rem 0;
}

.hph-property-address {
    font-size: var(--hph-text-sm);
    color: var(--hph-gray-600);
    margin: 0 0 0.5rem 0;
}

.hph-property-address i {
    color: var(--hph-primary);
    margin-right: 0.25rem;
}

.hph-property-details {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: var(--hph-text-sm);
}

.hph-property-price {
    font-weight: 700;
    color: var(--hph-primary);
    font-size: var(--hph-text-base);
}

.hph-property-specs {
    color: var(--hph-gray-700);
}

.hph-info-box {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 1rem;
    background: var(--hph-primary-50);
    border: 1px solid var(--hph-primary-200);
    border-radius: var(--hph-radius-md);
}

.hph-info-box i {
    color: var(--hph-primary);
    font-size: 1.25rem;
    margin-top: 0.125rem;
}

.hph-info-content strong {
    color: var(--hph-primary-800);
    display: block;
    margin-bottom: 0.25rem;
}

.hph-info-content p {
    font-size: var(--hph-text-sm);
    color: var(--hph-primary-700);
    margin: 0;
}

.hph-checkbox-group {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.hph-form-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 1.5rem;
    text-align: center;
    color: var(--hph-gray-600);
}

.hph-loading-spinner {
    width: 24px;
    height: 24px;
    border: 3px solid var(--hph-gray-200);
    border-top: 3px solid var(--hph-primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.hph-form-success {
    text-align: center;
    padding: 2rem;
}

.hph-success-icon {
    font-size: 3rem;
    color: var(--hph-success);
    margin-bottom: 1rem;
}

.hph-form-success h4 {
    color: var(--hph-success);
    margin-bottom: 0.5rem;
}

.hph-form-success p {
    color: var(--hph-gray-600);
    margin-bottom: 1.5rem;
}

.hph-calendly-link-container {
    margin-top: 1rem;
}

@media (max-width: 768px) {
    .hph-property-mini-card {
        flex-direction: column;
        align-items: flex-start;
        text-align: left;
    }
    
    .hph-checkbox-group {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>
