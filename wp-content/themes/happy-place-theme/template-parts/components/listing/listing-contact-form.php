<?php
/**
 * Listing Contact Form Component - AJAX Enhanced
 * 
 * Modern contact form with AJAX submission, multiple form types, and comprehensive validation
 * Supports various inquiry types with smart field configuration and real-time feedback
 * 
 * @package HappyPlaceTheme
 * @subpackage Components
 * @since 3.0.0
 * 
 * Args:
 * - listing_id: int (required for bridge functions)
 */

// Extract listing ID from args or global context
$listing_id = $args['listing_id'] ?? get_the_ID();

// Return early if no valid listing
if (!$listing_id || get_post_type($listing_id) !== 'listing') {
    return;
}

// Default arguments
$defaults = array(
    'listing_id' => $listing_id,
    'agent_id' => null,
    'form_type' => 'inquiry',           // inquiry, tour, callback, info, offer
    'style' => 'modern',                // modern, compact, minimal, floating
    'show_phone' => true,
    'show_tour_scheduling' => true,
    'show_prequalification' => true,
    'show_newsletter_signup' => true,
    'show_budget_range' => false,
    'show_timeline' => false,
    'show_additional_info' => true,
    'required_phone' => false,
    'auto_focus' => false,
    'submit_redirect' => null,
    'success_message' => null,
    'animation' => 'fade-up',           // fade-up, slide-in, none
    'accent_color' => 'primary',        // primary, secondary, accent
);

// Merge with provided args
$config = wp_parse_args($args ?? array(), $defaults);

// Get agent information via bridge functions
$agent_id = $config['agent_id'];
if (!$agent_id && function_exists('hpt_get_listing_agent_id')) {
    $agent_id = hpt_get_listing_agent_id($listing_id);
    $config['agent_id'] = $agent_id;
}

// Get listing data for form pre-population via bridge functions
$listing_data = array();
if ($listing_id) {
    $listing_data = array(
        'title' => function_exists('hpt_get_listing_title') ? hpt_get_listing_title($listing_id) : get_the_title($listing_id),
        'address' => function_exists('hpt_get_listing_address') ? hpt_get_listing_address($listing_id, 'full') : '',
        'price' => function_exists('hpt_get_listing_price_formatted') ? hpt_get_listing_price_formatted($listing_id) : '',
        'mls_number' => function_exists('hpt_get_listing_mls_number') ? hpt_get_listing_mls_number($listing_id) : '',
        'url' => get_permalink($listing_id)
    );
}

// Merge listing data with config
$config = array_merge($config, $listing_data);
extract($config);

// Form type configurations
$form_configs = array(
    'inquiry' => array(
        'title' => __('Property Inquiry', 'happy-place-theme'),
        'subtitle' => __('Get more information about this property', 'happy-place-theme'),
        'submit_text' => __('Send Inquiry', 'happy-place-theme'),
        'default_message' => __('I am interested in this property and would like more information.', 'happy-place-theme'),
        'icon' => 'hph-icon-envelope',
        'primary_color' => 'primary',
    ),
    'tour' => array(
        'title' => __('Schedule a Tour', 'happy-place-theme'),
        'subtitle' => __('Book a private showing of this property', 'happy-place-theme'),
        'submit_text' => __('Request Tour', 'happy-place-theme'),
        'default_message' => __('I would like to schedule a tour of this property.', 'happy-place-theme'),
        'icon' => 'hph-icon-calendar',
        'primary_color' => 'success',
    ),
    'callback' => array(
        'title' => __('Request Callback', 'happy-place-theme'),
        'subtitle' => __('Have an agent call you back', 'happy-place-theme'),
        'submit_text' => __('Request Call', 'happy-place-theme'),
        'default_message' => __('Please call me to discuss this property.', 'happy-place-theme'),
        'icon' => 'hph-icon-phone',
        'primary_color' => 'warning',
    ),
    'info' => array(
        'title' => __('Get Information', 'happy-place-theme'),
        'subtitle' => __('Request detailed property information', 'happy-place-theme'),
        'submit_text' => __('Get Info', 'happy-place-theme'),
        'default_message' => __('Please send me detailed information about this property.', 'happy-place-theme'),
        'icon' => 'hph-icon-info',
        'primary_color' => 'info',
    ),
    'offer' => array(
        'title' => __('Make an Offer', 'happy-place-theme'),
        'subtitle' => __('Submit an offer for this property', 'happy-place-theme'),
        'submit_text' => __('Submit Offer', 'happy-place-theme'),
        'default_message' => __('I am interested in making an offer on this property.', 'happy-place-theme'),
        'icon' => 'hph-icon-handshake',
        'primary_color' => 'accent',
    ),
);

$form_config = $form_configs[$args['form_type']] ?? $form_configs['inquiry'];

// Get listing data for form pre-population
$listing_data = array(
    'title' => get_the_title($listing_id),
    'url' => get_permalink($listing_id),
    'price' => function_exists('hpt_get_listing_price_formatted') ? hpt_get_listing_price_formatted($listing_id) : '',
    'address' => function_exists('hpt_get_listing_address') ? hpt_get_listing_address($listing_id, 'street') : '',
    'mls' => function_exists('hpt_get_listing_mls_number') ? hpt_get_listing_mls_number($listing_id) : '',
);

// Get current user data for pre-population
$current_user = wp_get_current_user();
$user_data = array(
    'name' => $current_user->exists() ? $current_user->display_name : '',
    'email' => $current_user->exists() ? $current_user->user_email : '',
    'phone' => $current_user->exists() ? get_user_meta($current_user->ID, 'phone', true) : '',
);

// Generate unique form ID and classes
$form_id = 'hph-contact-form-' . $listing_id . '-' . wp_rand();
$container_classes = array(
    'hph-contact-form',
    'hph-contact-form--' . $args['style'],
    'hph-contact-form--' . $args['form_type'],
    'hph-color-scheme--' . $args['accent_color'],
    $args['animation'] !== 'none' ? 'hph-animate--' . $args['animation'] : null,
);
$container_classes = array_filter($container_classes);

// Budget ranges for offer forms
$budget_ranges = array(
    '' => __('Select Budget Range', 'happy-place-theme'),
    '0-250000' => __('Under $250K', 'happy-place-theme'),
    '250000-500000' => __('$250K - $500K', 'happy-place-theme'),
    '500000-750000' => __('$500K - $750K', 'happy-place-theme'),
    '750000-1000000' => __('$750K - $1M', 'happy-place-theme'),
    '1000000-1500000' => __('$1M - $1.5M', 'happy-place-theme'),
    '1500000+' => __('$1.5M+', 'happy-place-theme'),
);

// Timeline options
$timeline_options = array(
    '' => __('Select Timeline', 'happy-place-theme'),
    'asap' => __('As soon as possible', 'happy-place-theme'),
    '1-3months' => __('1-3 months', 'happy-place-theme'),
    '3-6months' => __('3-6 months', 'happy-place-theme'),
    '6-12months' => __('6-12 months', 'happy-place-theme'),
    '12months+' => __('More than 12 months', 'happy-place-theme'),
);
?>

<div class="<?php echo esc_attr(implode(' ', $container_classes)); ?>" 
     data-listing-id="<?php echo esc_attr($listing_id); ?>"
     data-agent-id="<?php echo esc_attr($agent_id); ?>"
     data-component="listing-contact-form"
     data-form-type="<?php echo esc_attr($args['form_type']); ?>">

    <!-- Form Header -->
    <div class="hph-form-header hph-mb-6">
        <div class="hph-form-header__icon hph-mb-3">
            <span class="<?php echo esc_attr($form_config['icon']); ?> hph-text-3xl hph-text--<?php echo esc_attr($form_config['primary_color']); ?>"></span>
        </div>
        <h3 class="hph-form-header__title hph-heading hph-heading--h4 hph-mb-2">
            <?php echo esc_html($form_config['title']); ?>
        </h3>
        <p class="hph-form-header__subtitle hph-text--muted">
            <?php echo esc_html($form_config['subtitle']); ?>
        </p>
    </div>

    <!-- Contact Form -->
    <form id="<?php echo esc_attr($form_id); ?>" 
          class="hph-form hph-ajax-form" 
          method="post"
          data-action="hph_submit_contact_form"
          data-nonce="<?php echo wp_create_nonce('hph_contact_form_' . $listing_id); ?>"
          data-success-redirect="<?php echo esc_attr($args['submit_redirect']); ?>"
          novalidate>
        
        <!-- Hidden Fields -->
        <input type="hidden" name="action" value="hph_submit_contact_form">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('hph_contact_form_' . $listing_id); ?>">
        <input type="hidden" name="listing_id" value="<?php echo esc_attr($listing_id); ?>">
        <input type="hidden" name="agent_id" value="<?php echo esc_attr($agent_id); ?>">
        <input type="hidden" name="form_type" value="<?php echo esc_attr($args['form_type']); ?>">
        <input type="hidden" name="listing_title" value="<?php echo esc_attr($listing_data['title']); ?>">
        <input type="hidden" name="listing_url" value="<?php echo esc_url($listing_data['url']); ?>">
        <input type="hidden" name="listing_price" value="<?php echo esc_attr($listing_data['price']); ?>">
        <input type="hidden" name="listing_mls" value="<?php echo esc_attr($listing_data['mls']); ?>">
        
        <!-- Contact Information Section -->
        <div class="hph-form-section hph-mb-6">
            <h4 class="hph-form-section__title hph-heading hph-heading--h6 hph-mb-4">
                <?php esc_html_e('Your Information', 'happy-place-theme'); ?>
            </h4>
            
            <div class="hph-form-grid hph-grid hph-md:grid-cols-2 hph-gap-4">
                <!-- Name Field -->
                <div class="hph-form-group">
                    <label for="contact_name_<?php echo esc_attr($form_id); ?>" class="hph-form-label hph-form-label--required">
                        <?php esc_html_e('Full Name', 'happy-place-theme'); ?>
                    </label>
                    <input type="text" 
                           id="contact_name_<?php echo esc_attr($form_id); ?>" 
                           name="contact_name" 
                           class="hph-form-control" 
                           value="<?php echo esc_attr($user_data['name']); ?>"
                           required
                           data-validate="required"
                           <?php echo $args['auto_focus'] ? 'autofocus' : ''; ?>>
                    <div class="hph-form-error" data-field="contact_name"></div>
                </div>
                
                <!-- Email Field -->
                <div class="hph-form-group">
                    <label for="contact_email_<?php echo esc_attr($form_id); ?>" class="hph-form-label hph-form-label--required">
                        <?php esc_html_e('Email Address', 'happy-place-theme'); ?>
                    </label>
                    <input type="email" 
                           id="contact_email_<?php echo esc_attr($form_id); ?>" 
                           name="contact_email" 
                           class="hph-form-control" 
                           value="<?php echo esc_attr($user_data['email']); ?>"
                           required
                           data-validate="required email">
                    <div class="hph-form-error" data-field="contact_email"></div>
                </div>
            </div>
            
            <?php if ($args['show_phone']): ?>
                <!-- Phone Field -->
                <div class="hph-form-group hph-mt-4">
                    <label for="contact_phone_<?php echo esc_attr($form_id); ?>" class="hph-form-label <?php echo $args['required_phone'] ? 'hph-form-label--required' : ''; ?>">
                        <?php esc_html_e('Phone Number', 'happy-place-theme'); ?>
                    </label>
                    <input type="tel" 
                           id="contact_phone_<?php echo esc_attr($form_id); ?>" 
                           name="contact_phone" 
                           class="hph-form-control" 
                           value="<?php echo esc_attr($user_data['phone']); ?>"
                           placeholder="(555) 123-4567"
                           <?php echo $args['required_phone'] ? 'required' : ''; ?>
                           data-validate="<?php echo $args['required_phone'] ? 'required phone' : 'phone'; ?>">
                    <div class="hph-form-error" data-field="contact_phone"></div>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($args['show_tour_scheduling'] && in_array($args['form_type'], array('tour', 'inquiry'))): ?>
            <!-- Tour Scheduling Section -->
            <div class="hph-form-section hph-mb-6">
                <h4 class="hph-form-section__title hph-heading hph-heading--h6 hph-mb-4">
                    <span class="hph-icon-calendar hph-mr-2"></span>
                    <?php esc_html_e('Tour Preferences', 'happy-place-theme'); ?>
                </h4>
                
                <div class="hph-form-grid hph-grid hph-md:grid-cols-2 hph-gap-4">
                    <!-- Preferred Date -->
                    <div class="hph-form-group">
                        <label for="preferred_date_<?php echo esc_attr($form_id); ?>" class="hph-form-label">
                            <?php esc_html_e('Preferred Date', 'happy-place-theme'); ?>
                        </label>
                        <input type="date" 
                               id="preferred_date_<?php echo esc_attr($form_id); ?>" 
                               name="preferred_date" 
                               class="hph-form-control" 
                               min="<?php echo date('Y-m-d'); ?>"
                               max="<?php echo date('Y-m-d', strtotime('+90 days')); ?>">
                        <div class="hph-form-error" data-field="preferred_date"></div>
                    </div>
                    
                    <!-- Preferred Time -->
                    <div class="hph-form-group">
                        <label for="preferred_time_<?php echo esc_attr($form_id); ?>" class="hph-form-label">
                            <?php esc_html_e('Preferred Time', 'happy-place-theme'); ?>
                        </label>
                        <select id="preferred_time_<?php echo esc_attr($form_id); ?>" 
                                name="preferred_time" 
                                class="hph-form-control">
                            <option value=""><?php esc_html_e('Select a time', 'happy-place-theme'); ?></option>
                            <option value="morning"><?php esc_html_e('Morning (9am - 12pm)', 'happy-place-theme'); ?></option>
                            <option value="afternoon"><?php esc_html_e('Afternoon (12pm - 5pm)', 'happy-place-theme'); ?></option>
                            <option value="evening"><?php esc_html_e('Evening (5pm - 7pm)', 'happy-place-theme'); ?></option>
                            <option value="weekend"><?php esc_html_e('Weekend Only', 'happy-place-theme'); ?></option>
                        </select>
                        <div class="hph-form-error" data-field="preferred_time"></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($args['show_budget_range'] || $args['show_timeline']): ?>
            <!-- Purchase Information Section -->
            <div class="hph-form-section hph-mb-6">
                <h4 class="hph-form-section__title hph-heading hph-heading--h6 hph-mb-4">
                    <span class="hph-icon-dollar hph-mr-2"></span>
                    <?php esc_html_e('Purchase Information', 'happy-place-theme'); ?>
                </h4>
                
                <div class="hph-form-grid hph-grid hph-md:grid-cols-2 hph-gap-4">
                    <?php if ($args['show_budget_range']): ?>
                        <!-- Budget Range -->
                        <div class="hph-form-group">
                            <label for="budget_range_<?php echo esc_attr($form_id); ?>" class="hph-form-label">
                                <?php esc_html_e('Budget Range', 'happy-place-theme'); ?>
                            </label>
                            <select id="budget_range_<?php echo esc_attr($form_id); ?>" 
                                    name="budget_range" 
                                    class="hph-form-control">
                                <?php foreach ($budget_ranges as $value => $label): ?>
                                    <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($args['show_timeline']): ?>
                        <!-- Timeline -->
                        <div class="hph-form-group">
                            <label for="timeline_<?php echo esc_attr($form_id); ?>" class="hph-form-label">
                                <?php esc_html_e('Purchase Timeline', 'happy-place-theme'); ?>
                            </label>
                            <select id="timeline_<?php echo esc_attr($form_id); ?>" 
                                    name="timeline" 
                                    class="hph-form-control">
                                <?php foreach ($timeline_options as $value => $label): ?>
                                    <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Message Section -->
        <div class="hph-form-section hph-mb-6">
            <div class="hph-form-group">
                <label for="contact_message_<?php echo esc_attr($form_id); ?>" class="hph-form-label">
                    <?php esc_html_e('Message', 'happy-place-theme'); ?>
                </label>
                <textarea id="contact_message_<?php echo esc_attr($form_id); ?>" 
                          name="contact_message" 
                          class="hph-form-control" 
                          rows="5"
                          placeholder="<?php echo esc_attr($form_config['default_message']); ?>"
                          data-validate="maxlength:1000"></textarea>
                <div class="hph-form-help">
                    <?php esc_html_e('Maximum 1000 characters', 'happy-place-theme'); ?>
                </div>
                <div class="hph-form-error" data-field="contact_message"></div>
            </div>
        </div>
        
        <?php if ($args['show_additional_info']): ?>
            <!-- Additional Options Section -->
            <div class="hph-form-section hph-mb-6">
                <h4 class="hph-form-section__title hph-heading hph-heading--h6 hph-mb-4">
                    <?php esc_html_e('Additional Options', 'happy-place-theme'); ?>
                </h4>
                
                <?php if ($args['show_prequalification']): ?>
                    <div class="hph-form-group hph-mb-3">
                        <label class="hph-form-checkbox">
                            <input type="checkbox" name="prequalified" value="1" class="hph-form-checkbox__input">
                            <span class="hph-form-checkbox__checkmark"></span>
                            <span class="hph-form-checkbox__label">
                                <?php esc_html_e('I am pre-qualified for a mortgage', 'happy-place-theme'); ?>
                            </span>
                        </label>
                    </div>
                <?php endif; ?>
                
                <div class="hph-form-group hph-mb-3">
                    <label class="hph-form-checkbox">
                        <input type="checkbox" name="first_time_buyer" value="1" class="hph-form-checkbox__input">
                        <span class="hph-form-checkbox__checkmark"></span>
                        <span class="hph-form-checkbox__label">
                            <?php esc_html_e('I am a first-time home buyer', 'happy-place-theme'); ?>
                        </span>
                    </label>
                </div>
                
                <?php if ($args['show_newsletter_signup']): ?>
                    <div class="hph-form-group">
                        <label class="hph-form-checkbox">
                            <input type="checkbox" name="newsletter_signup" value="1" class="hph-form-checkbox__input" checked>
                            <span class="hph-form-checkbox__checkmark"></span>
                            <span class="hph-form-checkbox__label">
                                <?php esc_html_e('Send me updates about similar properties', 'happy-place-theme'); ?>
                            </span>
                        </label>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Form Actions -->
        <div class="hph-form-actions">
            <button type="submit" class="hph-btn hph-btn--<?php echo esc_attr($form_config['primary_color']); ?> hph-btn--lg hph-btn--block">
                <span class="hph-btn__icon">
                    <span class="<?php echo esc_attr($form_config['icon']); ?>"></span>
                </span>
                <span class="hph-btn__text"><?php echo esc_html($form_config['submit_text']); ?></span>
                <span class="hph-btn__loading hph-hidden">
                    <span class="hph-spinner hph-spinner--sm"></span>
                </span>
            </button>
        </div>
        
        <!-- Form Disclaimer -->
        <div class="hph-form-disclaimer hph-mt-4 hph-text-center">
            <p class="hph-text-sm hph-text--muted">
                <?php esc_html_e('By submitting this form, you agree to our', 'happy-place-theme'); ?>
                <a href="<?php echo esc_url(home_url('/privacy-policy')); ?>" 
                   target="_blank" 
                   class="hph-link">
                    <?php esc_html_e('Privacy Policy', 'happy-place-theme'); ?>
                </a>
                <?php esc_html_e('and consent to being contacted regarding your inquiry.', 'happy-place-theme'); ?>
            </p>
        </div>
    </form>
    
    <!-- Form Response Messages -->
    <div class="hph-form-messages">
        <!-- Success Message -->
        <div class="hph-form-message hph-form-message--success hph-hidden" data-message="success">
            <div class="hph-form-message__icon">
                <span class="hph-icon-check-circle hph-text--success"></span>
            </div>
            <div class="hph-form-message__content">
                <h4 class="hph-form-message__title">
                    <?php esc_html_e('Thank you!', 'happy-place-theme'); ?>
                </h4>
                <p class="hph-form-message__text">
                    <?php 
                    echo esc_html($args['success_message'] ?: 
                        __('Your message has been sent successfully. We will contact you soon.', 'happy-place-theme')
                    ); 
                    ?>
                </p>
            </div>
        </div>
        
        <!-- Error Message -->
        <div class="hph-form-message hph-form-message--error hph-hidden" data-message="error">
            <div class="hph-form-message__icon">
                <span class="hph-icon-exclamation-circle hph-text--danger"></span>
            </div>
            <div class="hph-form-message__content">
                <h4 class="hph-form-message__title">
                    <?php esc_html_e('Error', 'happy-place-theme'); ?>
                </h4>
                <p class="hph-form-message__text">
                    <?php esc_html_e('There was an error sending your message. Please try again or contact us directly.', 'happy-place-theme'); ?>
                </p>
            </div>
        </div>
        
        <!-- Validation Message -->
        <div class="hph-form-message hph-form-message--warning hph-hidden" data-message="validation">
            <div class="hph-form-message__icon">
                <span class="hph-icon-exclamation-triangle hph-text--warning"></span>
            </div>
            <div class="hph-form-message__content">
                <h4 class="hph-form-message__title">
                    <?php esc_html_e('Please check your information', 'happy-place-theme'); ?>
                </h4>
                <p class="hph-form-message__text">
                    <?php esc_html_e('Please correct the errors below and try again.', 'happy-place-theme'); ?>
                </p>
            </div>
        </div>
    </div>
    
</div>

<!-- Form Configuration Data -->
<script type="text/javascript">
window.hphContactForms = window.hphContactForms || {};
window.hphContactForms['<?php echo esc_js($form_id); ?>'] = {
    formId: '<?php echo esc_js($form_id); ?>',
    listingId: <?php echo intval($listing_id); ?>,
    agentId: <?php echo intval($agent_id); ?>,
    formType: '<?php echo esc_js($args['form_type']); ?>',
    ajaxUrl: '<?php echo esc_js(admin_url('admin-ajax.php')); ?>',
    nonce: '<?php echo wp_create_nonce('hph_contact_form_' . $listing_id); ?>',
    config: {
        validateOnBlur: true,
        showRealTimeValidation: true,
        autoHideMessages: 5000,
        successRedirect: <?php echo $args['submit_redirect'] ? "'" . esc_js($args['submit_redirect']) . "'" : 'null'; ?>,
        trackAnalytics: true
    },
    messages: {
        sending: '<?php esc_html_e('Sending...', 'happy-place-theme'); ?>',
        success: '<?php echo esc_js($args['success_message'] ?: __('Message sent successfully!', 'happy-place-theme')); ?>',
        error: '<?php esc_html_e('Error sending message. Please try again.', 'happy-place-theme'); ?>',
        validation: '<?php esc_html_e('Please correct the errors and try again.', 'happy-place-theme'); ?>'
    }
};
</script>