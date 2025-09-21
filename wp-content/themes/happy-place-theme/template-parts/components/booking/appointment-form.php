<?php
/**
 * Appointment Booking Form Component
 * 
 * Custom booking form that integrates with Calendly API
 * Uses HPH framework styling for brand consistency
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Default arguments
$args = wp_parse_args($args ?? [], [
    'type' => 'consultation', // 'showing', 'consultation', 'listing'
    'listing_id' => 0,
    'agent_id' => 0,
    'title' => '',
    'subtitle' => '',
    'button_text' => 'Book Appointment',
    'show_message' => true,
    'compact' => false,
    'style' => 'modern' // 'modern', 'simple', 'inline'
]);

// Generate form ID
$form_id = 'appointment-form-' . uniqid();

// Get contextual info
$listing = $args['listing_id'] ? get_post($args['listing_id']) : null;
$agent = $args['agent_id'] ? get_post($args['agent_id']) : null;

// Auto-generate titles if not provided
if (empty($args['title'])) {
    switch ($args['type']) {
        case 'showing':
            $args['title'] = $listing ? 'Schedule a Showing' : 'Book Property Showing';
            break;
        case 'consultation':
            $args['title'] = $agent ? 'Book Consultation' : 'Schedule Consultation';
            break;
        case 'listing':
            $args['title'] = 'Schedule Listing Appointment';
            break;
        default:
            $args['title'] = 'Book Appointment';
    }
}

if (empty($args['subtitle'])) {
    switch ($args['type']) {
        case 'showing':
            $args['subtitle'] = $listing ? 'Tour this property at a convenient time' : 'Schedule a private property tour';
            break;
        case 'consultation':
            $args['subtitle'] = 'Get expert guidance for your real estate needs';
            break;
        case 'listing':
            $args['subtitle'] = 'Discuss listing your property with our experts';
            break;
    }
}

// Form classes
$form_classes = ['hph-appointment-form', 'hph-form'];
if ($args['style']) {
    $form_classes[] = 'hph-form--' . $args['style'];
}
if ($args['compact']) {
    $form_classes[] = 'hph-form--compact';
}
?>

<div class="hph-appointment-booking" id="<?php echo esc_attr($form_id); ?>">
    <!-- Form Header -->
    <?php if (!$args['compact']) : ?>
    <div class="hph-form-header">
        <h3 class="hph-form-title"><?php echo esc_html($args['title']); ?></h3>
        <?php if ($args['subtitle']) : ?>
        <p class="hph-form-subtitle"><?php echo esc_html($args['subtitle']); ?></p>
        <?php endif; ?>
        
        <?php if ($listing) : ?>
        <div class="hph-form-context">
            <div class="hph-property-preview">
                <div class="hph-property-info">
                    <h4 class="hph-property-title"><?php echo esc_html($listing->post_title); ?></h4>
                    <?php 
                    $address = get_field('address', $listing->ID);
                    $price = get_field('price', $listing->ID);
                    ?>
                    <?php if ($address) : ?>
                    <p class="hph-property-address">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php echo esc_html($address); ?>
                    </p>
                    <?php endif; ?>
                    <?php if ($price) : ?>
                    <p class="hph-property-price">
                        <i class="fas fa-tag"></i>
                        $<?php echo number_format($price); ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <!-- Booking Form -->
    <form class="<?php echo esc_attr(implode(' ', $form_classes)); ?> hph-form" 
          data-appointment-form 
          data-route-type="booking_request"
          data-form-context="appointment_booking"
          id="<?php echo esc_attr($form_id); ?>">
        
        <!-- Hidden Fields -->
        <input type="hidden" name="action" value="hph_route_form">
        <input type="hidden" name="route_type" value="booking_request">
        <input type="hidden" name="form_type" value="appointment_booking">
        <input type="hidden" name="appointment_type" value="<?php echo esc_attr($args['type']); ?>">
        <input type="hidden" name="listing_id" value="<?php echo esc_attr($args['listing_id']); ?>">
        <input type="hidden" name="agent_id" value="<?php echo esc_attr($args['agent_id']); ?>">
        <input type="hidden" name="source" value="appointment_form_component">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('hph_booking_form'); ?>">
        
        <!-- Personal Information Section -->
        <div class="hph-form-section">
            <?php if (!$args['compact']) : ?>
            <h4 class="hph-form-section-title">
                <i class="fas fa-user"></i>
                Your Information
            </h4>
            <?php endif; ?>
            
            <div class="hph-form-row">
                <div class="hph-form-group">
                    <label class="hph-form-label required" for="client_name_<?php echo $form_id; ?>">
                        Full Name
                    </label>
                    <input type="text" 
                           id="client_name_<?php echo $form_id; ?>"
                           name="client_name" 
                           class="hph-form-control" 
                           required>
                </div>
                
                <div class="hph-form-group">
                    <label class="hph-form-label required" for="client_email_<?php echo $form_id; ?>">
                        Email Address
                    </label>
                    <input type="email" 
                           id="client_email_<?php echo $form_id; ?>"
                           name="client_email" 
                           class="hph-form-control" 
                           required>
                </div>
            </div>
            
            <div class="hph-form-group">
                <label class="hph-form-label" for="client_phone_<?php echo $form_id; ?>">
                    Phone Number
                </label>
                <input type="tel" 
                       id="client_phone_<?php echo $form_id; ?>"
                       name="client_phone" 
                       class="hph-form-control" 
                       placeholder="(555) 123-4567">
            </div>
        </div>
        
        <!-- Appointment Details Section -->
        <?php if ($args['show_message']) : ?>
        <div class="hph-form-section">
            <?php if (!$args['compact']) : ?>
            <h4 class="hph-form-section-title">
                <i class="fas fa-calendar-alt"></i>
                Appointment Details
            </h4>
            <?php endif; ?>
            
            <div class="hph-form-group">
                <label class="hph-form-label" for="message_<?php echo $form_id; ?>">
                    <?php 
                    switch ($args['type']) {
                        case 'showing':
                            echo 'Special requests or questions about the property';
                            break;
                        case 'consultation':
                            echo 'Tell us about your real estate goals';
                            break;
                        case 'listing':
                            echo 'Tell us about your property';
                            break;
                        default:
                            echo 'Additional information or questions';
                    }
                    ?>
                </label>
                <textarea id="message_<?php echo $form_id; ?>"
                          name="message" 
                          class="hph-form-control" 
                          rows="4"
                          placeholder="Optional: Any specific questions or preferences for your appointment..."></textarea>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Submit Section -->
        <div class="hph-form-actions">
            <button type="submit" class="hph-btn hph-btn-primary hph-btn-lg hph-appointment-submit">
                <i class="fas fa-calendar-check"></i>
                <span class="hph-btn-text"><?php echo esc_html($args['button_text']); ?></span>
                <div class="hph-btn-loader" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
            </button>
        </div>
        
        <!-- Loading State -->
        <div class="hph-form-loading" style="display: none;">
            <div class="hph-loading-content">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Setting up your appointment...</p>
            </div>
        </div>
        
        <!-- Success State -->
        <div class="hph-form-success" style="display: none;">
            <div class="hph-success-content">
                <i class="fas fa-check-circle"></i>
                <h4>Appointment Request Sent!</h4>
                <p>You'll be redirected to select your preferred time slot.</p>
                <div class="hph-success-actions">
                    <a href="#" class="hph-btn hph-btn-primary hph-calendly-link" target="_blank">
                        Select Time Slot
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Error State -->
        <div class="hph-form-error" style="display: none;">
            <div class="hph-error-content">
                <i class="fas fa-exclamation-triangle"></i>
                <h4>Booking Error</h4>
                <p class="hph-error-message">Something went wrong. Please try again or call us directly.</p>
                <div class="hph-error-actions">
                    <button type="button" class="hph-btn hph-btn-secondary hph-try-again">
                        Try Again
                    </button>
                    <?php 
                    $agency_phone = hph_get_agency_phone();
                    if ($agency_phone) : 
                    ?>
                    <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9]/', '', $agency_phone)); ?>" 
                       class="hph-btn hph-btn-outline-primary">
                        <i class="fas fa-phone"></i>
                        Call <?php echo esc_html($agency_phone); ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </form>
    
    <!-- Agent Contact Card (if available and not compact) -->
    <?php if ($agent && !$args['compact']) : ?>
    <div class="hph-agent-contact-card">
        <div class="hph-agent-info">
            <?php 
            $agent_photo = get_field('photo', $agent->ID);
            if ($agent_photo) :
            ?>
            <div class="hph-agent-avatar">
                <img src="<?php echo esc_url($agent_photo['sizes']['thumbnail']); ?>" 
                     alt="<?php echo esc_attr($agent->post_title); ?>">
            </div>
            <?php endif; ?>
            <div class="hph-agent-details">
                <h5 class="hph-agent-name"><?php echo esc_html($agent->post_title); ?></h5>
                <p class="hph-agent-title"><?php echo esc_html(get_field('title', $agent->ID)); ?></p>
                <?php 
                $agent_phone = get_field('phone', $agent->ID);
                $agent_email = get_field('email', $agent->ID);
                ?>
                <?php if ($agent_phone) : ?>
                <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9]/', '', $agent_phone)); ?>" 
                   class="hph-agent-contact">
                    <i class="fas fa-phone"></i>
                    <?php echo esc_html($agent_phone); ?>
                </a>
                <?php endif; ?>
                <?php if ($agent_email) : ?>
                <a href="mailto:<?php echo esc_attr($agent_email); ?>" 
                   class="hph-agent-contact">
                    <i class="fas fa-envelope"></i>
                    <?php echo esc_html($agent_email); ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Initialize appointment form
        const $form = $('#<?php echo esc_js($form_id); ?>').find('[data-appointment-form]');
        const $submitButton = $form.find('.hph-appointment-submit');
        const $buttonText = $submitButton.find('.hph-btn-text');
        const $buttonLoader = $submitButton.find('.hph-btn-loader');
        
        $form.on('submit', function(e) {
            e.preventDefault();
            
            // Show loading state
            $submitButton.prop('disabled', true);
            $buttonText.hide();
            $buttonLoader.show();
            $form.find('.hph-form-error').hide();
            
            // Submit form data
            $.ajax({
                url: '<?php echo admin_url("admin-ajax.php"); ?>',
                method: 'POST',
                data: $form.serialize() + '&action=hp_book_appointment',
                success: function(response) {
                    if (response.success && response.data.booking_url) {
                        // Show success state
                        $form.find('.hph-form-success').show();
                        $form.find('.hph-calendly-link').attr('href', response.data.booking_url);
                        
                        // Auto-redirect after 2 seconds
                        setTimeout(function() {
                            window.open(response.data.booking_url, '_blank');
                        }, 2000);
                        
                    } else {
                        showError(response.data?.message || 'Booking failed. Please try again.');
                    }
                },
                error: function() {
                    showError('Network error. Please check your connection and try again.');
                }
            });
        });
        
        // Try again button
        $form.find('.hph-try-again').on('click', function() {
            resetForm();
        });
        
        function showError(message) {
            $form.find('.hph-error-message').text(message);
            $form.find('.hph-form-error').show();
            resetButton();
        }
        
        function resetForm() {
            $form.find('.hph-form-error, .hph-form-success').hide();
            resetButton();
        }
        
        function resetButton() {
            $submitButton.prop('disabled', false);
            $buttonText.show();
            $buttonLoader.hide();
        }
    });
    
})(jQuery);
</script>

<style>
/* Appointment Form Specific Styles */
.hph-appointment-booking {
    background: var(--hph-white);
    border-radius: var(--hph-radius-lg);
    padding: var(--hph-card-padding-lg);
    box-shadow: var(--hph-shadow-lg);
    margin: var(--hph-section-margin) 0;
}

.hph-form-header {
    text-align: center;
    margin-bottom: 2rem;
}

.hph-form-title {
    font-size: var(--hph-text-2xl);
    font-weight: 700;
    color: var(--hph-gray-900);
    margin-bottom: 0.5rem;
}

.hph-form-subtitle {
    font-size: var(--hph-text-base);
    color: var(--hph-gray-600);
    margin-bottom: 0;
}

.hph-form-context {
    margin-top: 1.5rem;
    padding: 1rem;
    background: var(--hph-gradient-primary-subtle);
    border-radius: var(--hph-radius-md);
}

.hph-property-preview {
    text-align: left;
}

.hph-property-title {
    font-size: var(--hph-text-lg);
    font-weight: 600;
    color: var(--hph-primary-dark);
    margin-bottom: 0.5rem;
}

.hph-property-address,
.hph-property-price {
    font-size: var(--hph-text-sm);
    color: var(--hph-gray-700);
    margin: 0.25rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.hph-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.hph-form-loading,
.hph-form-success,
.hph-form-error {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(4px);
    border-radius: inherit;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    z-index: 10;
}

.hph-loading-content,
.hph-success-content,
.hph-error-content {
    padding: 2rem;
}

.hph-success-content i {
    font-size: 3rem;
    color: var(--hph-success);
    margin-bottom: 1rem;
}

.hph-error-content i {
    font-size: 3rem;
    color: var(--hph-danger);
    margin-bottom: 1rem;
}

.hph-success-actions,
.hph-error-actions {
    margin-top: 1.5rem;
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.hph-agent-contact-card {
    margin-top: 2rem;
    padding: 1.5rem;
    background: var(--hph-gray-50);
    border-radius: var(--hph-radius-md);
    border-left: 4px solid var(--hph-primary);
}

.hph-agent-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.hph-agent-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
}

.hph-agent-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.hph-agent-name {
    font-size: var(--hph-text-lg);
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
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--hph-primary);
    text-decoration: none;
    font-size: var(--hph-text-sm);
    margin-right: 1rem;
    transition: color 0.2s ease;
}

.hph-agent-contact:hover {
    color: var(--hph-primary-dark);
}

/* Compact variant */
.hph-appointment-booking .hph-form--compact {
    padding: 1rem;
}

.hph-form--compact .hph-form-row {
    grid-template-columns: 1fr;
    gap: 0.75rem;
}

.hph-form--compact .hph-form-section {
    margin-bottom: 1rem;
}

/* Responsive */
@media (max-width: 768px) {
    .hph-form-row {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .hph-agent-info {
        flex-direction: column;
        text-align: center;
    }
    
    .hph-success-actions,
    .hph-error-actions {
        flex-direction: column;
        align-items: center;
    }
}
</style>
