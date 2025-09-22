<?php
/**
 * Contact Agent Form - Delegation Pattern
 * Now uses the unified delegation system instead of hardcoded AJAX
 *
 * @package HappyPlaceTheme
 */

$agent_id = $args['agent_id'] ?? null;
$property_address = $args['property_address'] ?? '';
$listing_id = $args['listing_id'] ?? get_the_ID();

if (!$agent_id) return;
?>

<form
    class="hph-form hph-agent-contact-form"
    data-route-type="agent_contact"
    data-agent-id="<?php echo esc_attr($agent_id); ?>"
    data-listing-id="<?php echo esc_attr($listing_id); ?>"
    method="post"
    action="<?php echo admin_url('admin-ajax.php'); ?>"
>
    <?php wp_nonce_field('hph_route_form_nonce', 'nonce'); ?>

    <!-- Hidden Fields -->
    <input type="hidden" name="action" value="hph_route_form">
    <input type="hidden" name="route_type" value="agent_contact">
    <input type="hidden" name="form_type" value="agent_contact">
    <input type="hidden" name="agent_id" value="<?php echo esc_attr($agent_id); ?>">
    <input type="hidden" name="listing_id" value="<?php echo esc_attr($listing_id); ?>">
    <input type="hidden" name="source_url" value="<?php echo esc_url(get_permalink()); ?>">

    <div class="hph-form-group">
        <input type="text"
               name="name"
               placeholder="Your Name*"
               required
               class="hph-form-input">
    </div>

    <div class="hph-form-group">
        <input type="email"
               name="email"
               placeholder="Your Email*"
               required
               class="hph-form-input">
    </div>

    <div class="hph-form-group">
        <input type="tel"
               name="phone"
               placeholder="Your Phone"
               class="hph-form-input">
    </div>

    <div class="hph-form-group">
        <textarea name="message"
                  placeholder="Message"
                  rows="4"
                  class="hph-form-textarea">I'm interested in <?php echo esc_attr($property_address); ?></textarea>
    </div>

    <div class="hph-form-group">
        <label class="hph-form-label">Preferred Contact Method</label>
        <div class="hph-form-radio-group">
            <div class="hph-form-check hph-form-check-inline">
                <input type="radio" name="contact_preference" value="email" class="hph-form-check-input" checked>
                <label class="hph-form-check-label">Email</label>
            </div>
            <div class="hph-form-check hph-form-check-inline">
                <input type="radio" name="contact_preference" value="phone" class="hph-form-check-input">
                <label class="hph-form-check-label">Phone</label>
            </div>
            <div class="hph-form-check hph-form-check-inline">
                <input type="radio" name="contact_preference" value="text" class="hph-form-check-input">
                <label class="hph-form-check-label">Text</label>
            </div>
        </div>
    </div>

    <button type="submit" class="hph-btn hph-btn-primary w-full">
        <i class="fas fa-paper-plane"></i>
        Send Message
    </button>

    <!-- Loading State -->
    <div class="hph-form-loading" style="display: none;">
        <div class="hph-loading-spinner"></div>
        <span>Sending your message...</span>
    </div>

    <!-- Success Message -->
    <div class="hph-form-success" style="display: none;">
        <div class="hph-success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h4>Message Sent Successfully!</h4>
        <p>Thank you for contacting the agent. They'll get back to you shortly.</p>
    </div>
</form>
