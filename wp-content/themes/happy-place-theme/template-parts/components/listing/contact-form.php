<?php
/**
 * Listing Contact Form Component
 * Property inquiry form with agent integration
 * 
 * @package HappyPlaceTheme
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Extract component args (compatible with hph_component system)
$component_args = $GLOBALS['hph_component_args'] ?? get_query_var('args', []);
$args = wp_parse_args($component_args, [
    'listing_id' => null,
    'agent_id' => null,
    'form_type' => 'inquiry',
    'style' => 'default',
    'show_agent' => true
]);

// Validate listing ID
$listing_id = $args['listing_id'];
if (!$listing_id) {
    return;
}

// Get listing data using bridge functions
$listing = function_exists('hpt_get_listing') ? hpt_get_listing($listing_id) : null;
if (!$listing) {
    return;
}

// Get agent data if available
$agent = null;
if ($args['agent_id'] && function_exists('hpt_get_agent')) {
    $agent = hpt_get_agent($args['agent_id']);
}

$form_id = 'listing-contact-' . $listing_id;
$nonce_field = wp_nonce_field('listing_contact_' . $listing_id, '_contact_nonce', true, false);

// Pre-fill message based on form type
$default_messages = [
    'inquiry' => sprintf(__('Hi, I\'m interested in the property at %s. Please send me more information.', 'happy-place-theme'), $listing['address']['display'] ?? $listing['title']),
    'showing' => sprintf(__('Hi, I would like to schedule a showing for the property at %s. Please let me know your availability.', 'happy-place-theme'), $listing['address']['display'] ?? $listing['title']),
    'info' => sprintf(__('Hi, I would like more information about the property at %s.', 'happy-place-theme'), $listing['address']['display'] ?? $listing['title'])
];

$default_message = $default_messages[$args['form_type']] ?? $default_messages['inquiry'];
?>

<div class="hph-listing-contact-form hph-listing-contact-form--<?php echo esc_attr($args['style']); ?>">
    
    <div class="hph-card">
        <div class="hph-card__header">
            <h3 class="hph-card__title">
                <?php 
                if ($args['form_type'] === 'showing') {
                    _e('Schedule a Showing', 'happy-place-theme');
                } else {
                    _e('Request Information', 'happy-place-theme');
                }
                ?>
            </h3>
            
            <?php if ($agent && $args['show_agent']): ?>
            <p class="hph-card__subtitle hph-text-sm hph-text-gray-600">
                <?php printf(__('Contact %s for details', 'happy-place-theme'), esc_html($agent['name'] ?? $agent['title'])); ?>
            </p>
            <?php endif; ?>
        </div>
        
        <div class="hph-card__content">
            
            <form id="<?php echo esc_attr($form_id); ?>" 
                  class="hph-contact-form hph-form" 
                  method="post" 
                  action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>"
                  data-route-type="property_inquiry"
                  data-form-context="listing_component"
                  data-listing-id="<?php echo esc_attr($listing['id']); ?>">
                
                <input type="hidden" name="action" value="hph_route_form">
                <input type="hidden" name="route_type" value="property_inquiry">
                <input type="hidden" name="form_type" value="listing_contact">
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('hph_inquiry_form'); ?>">
                <input type="hidden" name="listing_id" value="<?php echo esc_attr($listing['id']); ?>">
                <input type="hidden" name="agent_id" value="<?php echo esc_attr($agent['id'] ?? ''); ?>">
                <input type="hidden" name="lead_type" value="<?php echo esc_attr($args['form_type']); ?>">
                <input type="hidden" name="source" value="listing_contact_form">
                <input type="hidden" name="source_url" value="<?php echo esc_url(get_permalink()); ?>">
                <input type="hidden" name="source_page" value="<?php echo esc_attr(get_the_title()); ?>">
                
                <!-- Contact Information -->
                <div class="hph-form-section hph-mb-6">
                    
                    <div class="hph-form-row hph-grid hph-grid-cols-1 md:hph-grid-cols-2 hph-gap-4 hph-mb-4">
                        
                        <div class="hph-form-group">
                            <label for="<?php echo esc_attr($form_id); ?>_first_name" class="hph-form-label">
                                <?php _e('First Name', 'happy-place-theme'); ?> <span class="hph-required">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="<?php echo esc_attr($form_id); ?>_first_name" 
                                name="first_name" 
                                class="hph-form-input" 
                                required 
                                value="<?php echo esc_attr(is_user_logged_in() ? wp_get_current_user()->user_firstname : ''); ?>"
                            >
                        </div>
                        
                        <div class="hph-form-group">
                            <label for="<?php echo esc_attr($form_id); ?>_last_name" class="hph-form-label">
                                <?php _e('Last Name', 'happy-place-theme'); ?> <span class="hph-required">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="<?php echo esc_attr($form_id); ?>_last_name" 
                                name="last_name" 
                                class="hph-form-input" 
                                required 
                                value="<?php echo esc_attr(is_user_logged_in() ? wp_get_current_user()->user_lastname : ''); ?>"
                            >
                        </div>
                        
                    </div>
                    
                    <div class="hph-form-row hph-grid hph-grid-cols-1 md:hph-grid-cols-2 hph-gap-4 hph-mb-4">
                        
                        <div class="hph-form-group">
                            <label for="<?php echo esc_attr($form_id); ?>_email" class="hph-form-label">
                                <?php _e('Email Address', 'happy-place-theme'); ?> <span class="hph-required">*</span>
                            </label>
                            <input 
                                type="email" 
                                id="<?php echo esc_attr($form_id); ?>_email" 
                                name="email" 
                                class="hph-form-input" 
                                required 
                                value="<?php echo esc_attr(is_user_logged_in() ? wp_get_current_user()->user_email : ''); ?>"
                            >
                        </div>
                        
                        <div class="hph-form-group">
                            <label for="<?php echo esc_attr($form_id); ?>_phone" class="hph-form-label">
                                <?php _e('Phone Number', 'happy-place-theme'); ?>
                            </label>
                            <input 
                                type="tel" 
                                id="<?php echo esc_attr($form_id); ?>_phone" 
                                name="phone" 
                                class="hph-form-input"
                                placeholder="(555) 123-4567"
                            >
                        </div>
                        
                    </div>
                    
                </div>
                
                <!-- Message -->
                <div class="hph-form-section hph-mb-6">
                    
                    <div class="hph-form-group">
                        <label for="<?php echo esc_attr($form_id); ?>_message" class="hph-form-label">
                            <?php _e('Message', 'happy-place-theme'); ?> <span class="hph-required">*</span>
                        </label>
                        <textarea 
                            id="<?php echo esc_attr($form_id); ?>_message" 
                            name="message" 
                            class="hph-form-textarea" 
                            rows="5" 
                            required 
                            placeholder="<?php echo esc_attr($default_message); ?>"
                        ><?php echo esc_textarea($default_message); ?></textarea>
                    </div>
                    
                </div>
                
                <!-- Additional Options for Showing Request -->
                <?php if ($args['form_type'] === 'showing'): ?>
                <div class="hph-form-section hph-mb-6">
                    
                    <div class="hph-form-row hph-grid hph-grid-cols-1 md:hph-grid-cols-2 hph-gap-4 hph-mb-4">
                        
                        <div class="hph-form-group">
                            <label for="<?php echo esc_attr($form_id); ?>_preferred_date" class="hph-form-label">
                                <?php _e('Preferred Date', 'happy-place-theme'); ?>
                            </label>
                            <input 
                                type="date" 
                                id="<?php echo esc_attr($form_id); ?>_preferred_date" 
                                name="preferred_date" 
                                class="hph-form-input"
                                min="<?php echo date('Y-m-d'); ?>"
                            >
                        </div>
                        
                        <div class="hph-form-group">
                            <label for="<?php echo esc_attr($form_id); ?>_preferred_time" class="hph-form-label">
                                <?php _e('Preferred Time', 'happy-place-theme'); ?>
                            </label>
                            <select id="<?php echo esc_attr($form_id); ?>_preferred_time" name="preferred_time" class="hph-form-select">
                                <option value=""><?php _e('Any time', 'happy-place-theme'); ?></option>
                                <option value="morning"><?php _e('Morning (9am - 12pm)', 'happy-place-theme'); ?></option>
                                <option value="afternoon"><?php _e('Afternoon (12pm - 5pm)', 'happy-place-theme'); ?></option>
                                <option value="evening"><?php _e('Evening (5pm - 7pm)', 'happy-place-theme'); ?></option>
                            </select>
                        </div>
                        
                    </div>
                    
                </div>
                <?php endif; ?>
                
                <!-- Financing Interest -->
                <div class="hph-form-section hph-mb-6">
                    
                    <div class="hph-form-group">
                        <label class="hph-form-label hph-mb-3 hph-block">
                            <?php _e('How can we help?', 'happy-place-theme'); ?>
                        </label>
                        
                        <div class="hph-checkbox-group hph-space-y-2">
                            
                            <label class="hph-checkbox-item hph-flex hph-items-center">
                                <input type="checkbox" name="interests[]" value="financing" class="hph-checkbox">
                                <span class="hph-checkbox-label"><?php _e('I need financing information', 'happy-place-theme'); ?></span>
                            </label>
                            
                            <label class="hph-checkbox-item hph-flex hph-items-center">
                                <input type="checkbox" name="interests[]" value="inspection" class="hph-checkbox">
                                <span class="hph-checkbox-label"><?php _e('I want to schedule an inspection', 'happy-place-theme'); ?></span>
                            </label>
                            
                            <label class="hph-checkbox-item hph-flex hph-items-center">
                                <input type="checkbox" name="interests[]" value="similar" class="hph-checkbox">
                                <span class="hph-checkbox-label"><?php _e('Show me similar properties', 'happy-place-theme'); ?></span>
                            </label>
                            
                        </div>
                        
                    </div>
                    
                </div>
                
                <!-- Submit Button -->
                <div class="hph-form-actions">
                    
                    <button type="submit" class="hph-btn hph-btn-primary hph-btn-lg hph-w-full">
                        <span class="hph-btn-text">
                            <?php 
                            if ($args['form_type'] === 'showing') {
                                _e('Request Showing', 'happy-place-theme');
                            } else {
                                _e('Send Message', 'happy-place-theme');
                            }
                            ?>
                        </span>
                        <span class="hph-btn-loading hph-hidden">
                            <i class="hph-icon hph-icon-spinner hph-spin" aria-hidden="true"></i>
                            <?php _e('Sending...', 'happy-place-theme'); ?>
                        </span>
                    </button>
                    
                </div>
                
                <!-- Success/Error Messages -->
                <div class="hph-form-messages hph-mt-4">
                    
                    <div class="hph-form-message hph-form-message--success hph-hidden">
                        <i class="hph-icon hph-icon-check-circle hph-text-green-500" aria-hidden="true"></i>
                        <span class="hph-message-text">
                            <?php _e('Thank you! Your message has been sent successfully. We\'ll get back to you soon.', 'happy-place-theme'); ?>
                        </span>
                    </div>
                    
                    <div class="hph-form-message hph-form-message--error hph-hidden">
                        <i class="hph-icon hph-icon-exclamation-circle hph-text-red-500" aria-hidden="true"></i>
                        <span class="hph-message-text">
                            <?php _e('Sorry, there was an error sending your message. Please try again.', 'happy-place-theme'); ?>
                        </span>
                    </div>
                    
                </div>
                
            </form>
            
        </div>
    </div>
    
</div>

<style>
.hph-form-label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.hph-required {
    color: #dc2626;
}

.hph-form-input,
.hph-form-textarea,
.hph-form-select {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 1rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.hph-form-input:focus,
.hph-form-textarea:focus,
.hph-form-select:focus {
    outline: none;
    border-color: var(--hph-primary);
    box-shadow: 0 0 0 3px rgba(var(--hph-primary-rgb), 0.1);
}

.hph-checkbox-item {
    cursor: pointer;
    gap: 0.5rem;
}

.hph-checkbox {
    width: 1rem;
    height: 1rem;
    accent-color: var(--hph-primary);
}

.hph-checkbox-label {
    color: #374151;
    font-size: 0.875rem;
}

.hph-form-message {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
}

.hph-form-message--success {
    background-color: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #166534;
}

.hph-form-message--error {
    background-color: #fef2f2;
    border: 1px solid #fecaca;
    color: #dc2626;
}
</style>
