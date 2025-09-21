<?php
/**
 * General Contact Form Template
 * Multi-purpose contact form with flexible routing
 * Designed for contact pages, footer, and modal contexts
 * 
 * @package HappyPlaceTheme
 */

// Extract arguments
$args = wp_parse_args($args, [
    'variant' => 'default',
    'modal_context' => false,
    'title' => __('Contact Us', 'happy-place-theme'),
    'description' => __('We\'d love to hear from you. Send us a message and we\'ll respond as soon as possible.', 'happy-place-theme'),
    'submit_text' => __('Send Message', 'happy-place-theme'),
    'show_office_info' => true,
    'department_routing' => true,
    'css_classes' => '',
    'layout' => 'vertical', // vertical, horizontal
    'size' => 'normal' // compact, normal, large
]);

// Form classes
$form_classes = ['hph-form', 'hph-general-contact-form'];
if ($args['variant'] === 'compact') $form_classes[] = 'hph-form--compact';
if ($args['variant'] === 'modern') $form_classes[] = 'hph-form--modern';
if ($args['variant'] === 'email') $form_classes[] = 'hph-form--email';
if ($args['modal_context']) $form_classes[] = 'hph-form--modal';
if ($args['layout'] === 'horizontal') $form_classes[] = 'hph-form--inline';
if ($args['size'] !== 'normal') $form_classes[] = 'hph-form--' . $args['size'];
if ($args['css_classes']) $form_classes[] = $args['css_classes'];

// Get office information
$office_info = [
    'phone' => get_option('hp_office_phone', '(555) 123-4567'),
    'email' => get_option('hp_office_email', 'cheers@theparkergroup.com'),
    'address' => get_option('hp_office_address', '123 Main Street, City, ST 12345'),
    'hours' => get_option('hp_office_hours', 'Mon-Fri: 9am-6pm, Sat-Sun: 10am-4pm')
];
?>

<div class="hph-contact-form-container">
    <?php if (!$args['modal_context']): ?>
    <!-- Form Header -->
    <div class="hph-form-header">
        <h3 class="hph-form-title"><?php echo esc_html($args['title']); ?></h3>
        <?php if ($args['description']): ?>
        <p class="hph-form-description"><?php echo esc_html($args['description']); ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="hph-contact-layout">
        <!-- Contact Form -->
        <div class="hph-contact-form-section">
            <form 
                class="<?php echo implode(' ', $form_classes); ?>" 
                data-route-type="lead_capture"
                data-form-context="general"
                method="post"
                action="<?php echo admin_url('admin-ajax.php'); ?>"
            >
                <?php wp_nonce_field('hph_route_form_nonce', 'nonce'); ?>
                
                <!-- Hidden Fields -->
                <input type="hidden" name="action" value="hph_route_form">
                <input type="hidden" name="route_type" value="lead_capture">
                <input type="hidden" name="form_type" value="general_contact">
                <input type="hidden" name="source_url" value="<?php echo esc_url(get_permalink()); ?>">
                <input type="hidden" name="source_page" value="<?php echo esc_attr(get_the_title()); ?>">
                <input type="hidden" name="lead_type" value="general_inquiry">

                <?php if ($args['layout'] === 'horizontal'): ?>
                <!-- Horizontal Layout -->
                <div class="hph-form-group">
                    <input 
                        type="text" 
                        id="contact-name" 
                        name="name" 
                        class="hph-form-input" 
                        required 
                        placeholder="<?php _e('Your Name *', 'happy-place-theme'); ?>"
                    >
                </div>
                <div class="hph-form-group">
                    <input 
                        type="email" 
                        id="contact-email" 
                        name="email" 
                        class="hph-form-input" 
                        required 
                        placeholder="<?php _e('Your Email *', 'happy-place-theme'); ?>"
                    >
                </div>
                <div class="hph-form-group">
                    <input 
                        type="text" 
                        id="contact-subject" 
                        name="subject" 
                        class="hph-form-input" 
                        placeholder="<?php _e('Subject', 'happy-place-theme'); ?>"
                    >
                </div>
                <button type="submit" class="hph-btn hph-btn-primary">
                    <?php echo esc_html($args['submit_text']); ?>
                </button>

                <?php else: ?>
                <!-- Vertical Layout -->
                <?php if ($args['modal_context']): ?>
                <!-- Single Column Layout for Modals -->
                <div class="hph-form-group hph-mb-4">
                    <label for="contact-name" class="hph-form-label">
                        <?php _e('Full Name', 'happy-place-theme'); ?>
                        <span class="hph-required">*</span>
                    </label>
                    <input
                        type="text"
                        id="contact-name"
                        name="name"
                        class="hph-form-input"
                        required
                        placeholder="<?php _e('John Smith', 'happy-place-theme'); ?>"
                    >
                </div>

                <div class="hph-form-group hph-mb-4">
                    <label for="contact-email" class="hph-form-label">
                        <?php _e('Email Address', 'happy-place-theme'); ?>
                        <span class="hph-required">*</span>
                    </label>
                    <input
                        type="email"
                        id="contact-email"
                        name="email"
                        class="hph-form-input"
                        required
                        placeholder="<?php _e('john@example.com', 'happy-place-theme'); ?>"
                    >
                </div>

                <div class="hph-form-group hph-mb-4">
                    <label for="contact-phone" class="hph-form-label">
                        <?php _e('Phone Number', 'happy-place-theme'); ?>
                    </label>
                    <input
                        type="tel"
                        id="contact-phone"
                        name="phone"
                        class="hph-form-input"
                        placeholder="<?php _e('(555) 123-4567', 'happy-place-theme'); ?>"
                    >
                </div>

                <div class="hph-form-group hph-mb-4">
                    <label for="contact-subject" class="hph-form-label">
                        <?php _e('Subject', 'happy-place-theme'); ?>
                    </label>
                    <input
                        type="text"
                        id="contact-subject"
                        name="subject"
                        class="hph-form-input"
                        placeholder="<?php _e('How can we help?', 'happy-place-theme'); ?>"
                    >
                </div>

                <?php else: ?>
                <!-- Two Column Layout for Regular Pages -->
                <div class="hph-form-row hph-flex hph-flex-wrap hph-gap-4">
                    <!-- Full Name -->
                    <div class="hph-form-group hph-form-col--half hph-flex-1 hph-min-w-0">
                        <label for="contact-name" class="hph-form-label">
                            <?php _e('Full Name', 'happy-place-theme'); ?>
                            <span class="hph-required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="contact-name" 
                            name="name" 
                            class="hph-form-input" 
                            required 
                            placeholder="<?php _e('John Smith', 'happy-place-theme'); ?>"
                        >
                    </div>

                    <!-- Email -->
                    <div class="hph-form-group hph-form-col--half hph-flex-1 hph-min-w-0">
                        <label for="contact-email" class="hph-form-label">
                            <?php _e('Email Address', 'happy-place-theme'); ?>
                            <span class="hph-required">*</span>
                        </label>
                        <input 
                            type="email" 
                            id="contact-email" 
                            name="email" 
                            class="hph-form-input" 
                            required 
                            placeholder="<?php _e('john@example.com', 'happy-place-theme'); ?>"
                        >
                    </div>
                </div>

                <div class="hph-form-row hph-flex hph-flex-wrap hph-gap-4">
                    <!-- Phone -->
                    <div class="hph-form-group hph-form-col--half hph-flex-1 hph-min-w-0">
                        <label for="contact-phone" class="hph-form-label">
                            <?php _e('Phone Number', 'happy-place-theme'); ?>
                        </label>
                        <input
                            type="tel"
                            id="contact-phone"
                            name="phone"
                            class="hph-form-input"
                            placeholder="<?php _e('(555) 123-4567', 'happy-place-theme'); ?>"
                        >
                    </div>

                    <!-- Subject/Topic -->
                    <div class="hph-form-group hph-form-col--half hph-flex-1 hph-min-w-0">
                        <label for="contact-subject" class="hph-form-label">
                            <?php _e('Subject', 'happy-place-theme'); ?>
                        </label>
                        <input
                            type="text"
                            id="contact-subject"
                            name="subject"
                            class="hph-form-input"
                            placeholder="<?php _e('How can we help?', 'happy-place-theme'); ?>"
                        >
                    </div>
                </div>
                <?php endif; // End modal_context ?>

                <?php if ($args['department_routing']): ?>
                <!-- Department/Topic Selection -->
                <div class="hph-form-group hph-mb-4">
                    <label for="contact-department" class="hph-form-label hph-mb-2">
                        <?php _e('How can we help you?', 'happy-place-theme'); ?>
                    </label>
                    <select id="contact-department" name="department" class="hph-form-select">
                        <option value=""><?php _e('Select a topic', 'happy-place-theme'); ?></option>
                        <option value="buying"><?php _e('Buying a Home', 'happy-place-theme'); ?></option>
                        <option value="selling"><?php _e('Selling a Home', 'happy-place-theme'); ?></option>
                        <option value="renting"><?php _e('Rental Properties', 'happy-place-theme'); ?></option>
                        <option value="investment"><?php _e('Investment Properties', 'happy-place-theme'); ?></option>
                        <option value="commercial"><?php _e('Commercial Real Estate', 'happy-place-theme'); ?></option>
                        <option value="property_management"><?php _e('Property Management', 'happy-place-theme'); ?></option>
                        <option value="market_analysis"><?php _e('Market Analysis', 'happy-place-theme'); ?></option>
                        <option value="partnership"><?php _e('Partnership Opportunities', 'happy-place-theme'); ?></option>
                        <option value="website"><?php _e('Website Support', 'happy-place-theme'); ?></option>
                        <option value="general"><?php _e('General Inquiry', 'happy-place-theme'); ?></option>
                    </select>
                </div>
                <?php endif; ?>

                <!-- Message -->
                <div class="hph-form-group hph-mb-4">
                    <label for="contact-message" class="hph-form-label hph-mb-2">
                        <?php _e('Message', 'happy-place-theme'); ?>
                        <span class="hph-required">*</span>
                    </label>
                    <textarea 
                        id="contact-message" 
                        name="message" 
                        class="hph-form-textarea" 
                        rows="5" 
                        required 
                        placeholder="<?php _e('Tell us more about what you need help with...', 'happy-place-theme'); ?>"
                    ></textarea>
                    <div class="hph-form-text hph-text-sm hph-text-muted hph-mt-2">
                        <?php _e('Please provide as much detail as possible so we can assist you better.', 'happy-place-theme'); ?>
                    </div>
                </div>

                <!-- Preferred Contact Method -->
                <div class="hph-form-group hph-mb-4">
                    <label class="hph-form-label hph-mb-3">
                        <?php _e('Preferred Contact Method', 'happy-place-theme'); ?>
                    </label>
                    <div class="hph-checkbox-group hph-flex hph-flex-wrap hph-gap-4">
                        <label class="hph-form-check hph-flex hph-items-center hph-gap-2">
                            <input type="radio" name="contact_preference" value="email" class="hph-form-check-input" checked>
                            <span class="hph-form-check-label"><?php _e('Email', 'happy-place-theme'); ?></span>
                        </label>
                        <label class="hph-form-check hph-flex hph-items-center hph-gap-2">
                            <input type="radio" name="contact_preference" value="phone" class="hph-form-check-input">
                            <span class="hph-form-check-label"><?php _e('Phone Call', 'happy-place-theme'); ?></span>
                        </label>
                        <label class="hph-form-check hph-flex hph-items-center hph-gap-2">
                            <input type="radio" name="contact_preference" value="text" class="hph-form-check-input">
                            <span class="hph-form-check-label"><?php _e('Text Message', 'happy-place-theme'); ?></span>
                        </label>
                    </div>
                </div>

                <!-- Best Time to Contact -->
                <div class="hph-form-group hph-mb-4">
                    <label for="contact-time" class="hph-form-label hph-mb-2">
                        <?php _e('Best Time to Contact', 'happy-place-theme'); ?>
                    </label>
                    <select id="contact-time" name="best_time" class="hph-form-select">
                        <option value=""><?php _e('No preference', 'happy-place-theme'); ?></option>
                        <option value="morning"><?php _e('Morning (9am - 12pm)', 'happy-place-theme'); ?></option>
                        <option value="afternoon"><?php _e('Afternoon (12pm - 5pm)', 'happy-place-theme'); ?></option>
                        <option value="evening"><?php _e('Evening (5pm - 8pm)', 'happy-place-theme'); ?></option>
                        <option value="weekend"><?php _e('Weekends', 'happy-place-theme'); ?></option>
                    </select>
                </div>

                <!-- Privacy Notice -->
                <div class="hph-form-group hph-mb-6">
                    <label class="hph-form-check hph-flex hph-items-start hph-gap-3">
                        <input type="checkbox" name="privacy_consent" value="1" class="hph-form-check-input hph-mt-1" required>
                        <span class="hph-form-check-label hph-text-sm hph-leading-relaxed">
                            <?php _e('I agree to the', 'happy-place-theme'); ?> 
                            <a href="<?php echo home_url('/legal/#privacy'); ?>" target="_blank" class="hph-text-primary hph-underline"><?php _e('Privacy Policy', 'happy-place-theme'); ?></a> 
                            <?php _e('and consent to being contacted by The Parker Group.', 'happy-place-theme'); ?>
                            <span class="hph-required">*</span>
                        </span>
                    </label>
                </div>

                <!-- Form Actions -->
                <div class="hph-form-buttons">
                    <button type="submit" class="hph-btn hph-btn-primary w-full">
                        <i class="fas fa-paper-plane"></i>
                        <?php echo esc_html($args['submit_text']); ?>
                    </button>
                </div>
                <?php endif; ?>

                <!-- Loading State -->
                <div class="hph-form-loading" style="display: none;">
                    <div class="hph-loading-spinner"></div>
                    <span><?php _e('Sending your message...', 'happy-place-theme'); ?></span>
                </div>

                <!-- Success Message -->
                <div class="hph-form-success" style="display: none;">
                    <div class="hph-success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h4><?php _e('Message Sent Successfully!', 'happy-place-theme'); ?></h4>
                    <p><?php _e('Thank you for contacting us. We\'ll get back to you within 24 hours.', 'happy-place-theme'); ?></p>
                </div>
            </form>
        </div>

        <?php if ($args['show_office_info'] && $args['layout'] === 'vertical'): ?>
        <!-- Office Information Sidebar -->
        <div class="hph-office-info-section">
            <div class="hph-office-info">
                <h4 class="hph-office-title"><?php _e('Get In Touch', 'happy-place-theme'); ?></h4>
                <p class="hph-office-description">
                    <?php _e('Reach out to us through any of these channels. We\'re here to help with all your real estate needs.', 'happy-place-theme'); ?>
                </p>

                <div class="hph-contact-methods">
                    <!-- Phone -->
                    <div class="hph-contact-method">
                        <div class="hph-contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="hph-contact-details">
                            <strong><?php _e('Phone', 'happy-place-theme'); ?></strong>
                            <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $office_info['phone'])); ?>">
                                <?php echo esc_html($office_info['phone']); ?>
                            </a>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="hph-contact-method">
                        <div class="hph-contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="hph-contact-details">
                            <strong><?php _e('Email', 'happy-place-theme'); ?></strong>
                            <a href="mailto:<?php echo esc_attr($office_info['email']); ?>">
                                <?php echo esc_html($office_info['email']); ?>
                            </a>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="hph-contact-method">
                        <div class="hph-contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="hph-contact-details">
                            <strong><?php _e('Office', 'happy-place-theme'); ?></strong>
                            <address><?php echo esc_html($office_info['address']); ?></address>
                        </div>
                    </div>

                    <!-- Hours -->
                    <div class="hph-contact-method">
                        <div class="hph-contact-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="hph-contact-details">
                            <strong><?php _e('Hours', 'happy-place-theme'); ?></strong>
                            <span><?php echo esc_html($office_info['hours']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="hph-quick-actions">
                    <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $office_info['phone'])); ?>" 
                       class="hph-btn hph-btn-outline-primary hph-btn-sm">
                        <i class="fas fa-phone"></i>
                        <?php _e('Call Now', 'happy-place-theme'); ?>
                    </a>
                    <a href="mailto:<?php echo esc_attr($office_info['email']); ?>" 
                       class="hph-btn hph-btn-outline-primary hph-btn-sm">
                        <i class="fas fa-envelope"></i>
                        <?php _e('Email Us', 'happy-place-theme'); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Inline CSS for form-specific styles -->
<style>
.hph-contact-layout {
    display: grid;
    gap: 2rem;
    grid-template-columns: 1fr;
}

.hph-contact-layout.hph-with-sidebar {
    grid-template-columns: 2fr 1fr;
}

.hph-office-info {
    background: var(--hph-gray-50);
    border: 1px solid var(--hph-border-color-light);
    border-radius: var(--hph-radius-lg);
    padding: 2rem;
    height: fit-content;
}

.hph-office-title {
    color: var(--hph-gray-900);
    margin-bottom: 0.5rem;
    font-size: var(--hph-text-xl);
}

.hph-office-description {
    color: var(--hph-gray-600);
    margin-bottom: 2rem;
    font-size: var(--hph-text-sm);
}

.hph-contact-methods {
    margin-bottom: 2rem;
}

.hph-contact-method {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.hph-contact-method:last-child {
    margin-bottom: 0;
}

.hph-contact-icon {
    flex-shrink: 0;
    width: 2.5rem;
    height: 2.5rem;
    background: var(--hph-primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

.hph-contact-details strong {
    display: block;
    color: var(--hph-gray-900);
    margin-bottom: 0.25rem;
    font-size: var(--hph-text-sm);
    font-weight: 600;
}

.hph-contact-details a,
.hph-contact-details span,
.hph-contact-details address {
    color: var(--hph-gray-700);
    text-decoration: none;
    font-size: var(--hph-text-sm);
    font-style: normal;
}

.hph-contact-details a:hover {
    color: var(--hph-primary);
    text-decoration: underline;
}

.hph-quick-actions {
    display: flex;
    gap: 0.75rem;
}

.hph-quick-actions .hph-btn {
    flex: 1;
    justify-content: center;
}

.hph-checkbox-group {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

@media (min-width: 768px) {
    .hph-contact-layout.hph-with-sidebar {
        grid-template-columns: 2fr 1fr;
    }
}

@media (max-width: 768px) {
    .hph-contact-layout {
        grid-template-columns: 1fr;
    }
    
    .hph-office-info {
        padding: 1.5rem;
    }
    
    .hph-quick-actions {
        flex-direction: column;
    }
    
    .hph-checkbox-group {
        flex-direction: column;
        gap: 0.5rem;
    }
}

/* Horizontal layout specific styles */
.hph-form--inline .hph-form-group {
    margin-bottom: 0;
}

.hph-form--inline.hph-form--email .hph-form-group {
    background: transparent;
    border: none;
    padding: 0;
    box-shadow: none;
}

.hph-form--email.hph-form--inline {
    background: var(--hph-white);
    border-radius: var(--hph-radius-lg);
    padding: 0.75rem;
    box-shadow: var(--hph-shadow-lg);
    border: 1px solid var(--hph-border-color-light);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if sidebar should be shown based on layout
    const contactLayout = document.querySelector('.hph-contact-layout');
    const officeInfo = document.querySelector('.hph-office-info-section');
    
    if (contactLayout && officeInfo) {
        contactLayout.classList.add('hph-with-sidebar');
    }
});
</script>
