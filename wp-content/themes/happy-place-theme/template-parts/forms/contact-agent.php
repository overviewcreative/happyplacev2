<?php
/**
 * Contact Agent Form
 * File: template-parts/forms/contact-agent.php
 * 
 * @package HappyPlaceTheme
 */

$agent_id = $args['agent_id'] ?? null;
$property_address = $args['property_address'] ?? '';
$listing_id = $args['listing_id'] ?? get_the_ID();

if (!$agent_id) return;
?>

<form id="contact-agent-form" class="hph-contact-form">
    <?php wp_nonce_field('contact_agent_nonce', 'contact_nonce'); ?>
    
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
            <label class="hph-form-radio">
                <input type="radio" name="contact_method" value="email" checked>
                <span>Email</span>
            </label>
            <label class="hph-form-radio">
                <input type="radio" name="contact_method" value="phone">
                <span>Phone</span>
            </label>
            <label class="hph-form-radio">
                <input type="radio" name="contact_method" value="text">
                <span>Text</span>
            </label>
        </div>
    </div>
    
    <input type="hidden" name="agent_id" value="<?php echo esc_attr($agent_id); ?>">
    <input type="hidden" name="listing_id" value="<?php echo esc_attr($listing_id); ?>">
    
    <button type="submit" class="hph-btn hph-btn--primary hph-btn--full">
        <i class="fas fa-paper-plane"></i>
        Send Message
    </button>
    
    <div id="form-message" class="hph-form-message" style="display: none;"></div>
</form>

<script>
document.getElementById('contact-agent-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    formData.append('action', 'send_agent_contact');
    
    const messageDiv = document.getElementById('form-message');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        messageDiv.style.display = 'block';
        
        if (data.success) {
            messageDiv.className = 'hph-form-message hph-form-message--success';
            messageDiv.textContent = 'Message sent successfully!';
            form.reset();
        } else {
            messageDiv.className = 'hph-form-message hph-form-message--error';
            messageDiv.textContent = data.data || 'An error occurred.';
        }
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Message';
    });
});
</script>