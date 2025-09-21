<?php
/**
 * Agent Sidebar Template - Matches Screenshot Design
 * Clean, centered agent card with contact form
 * 
 * @package HappyPlaceTheme
 */

$listing_id = $args['listing_id'] ?? get_the_ID();

// Get agent data simply
$agent_posts = get_field('listing_agent', $listing_id);
$agent_data = null;

if (!empty($agent_posts) && is_array($agent_posts)) {
    $agent_post = $agent_posts[0];
    
    if ($agent_post && isset($agent_post->ID)) {
        $agent_id = $agent_post->ID;
        $first_name = get_field('first_name', $agent_id) ?: '';
        $last_name = get_field('last_name', $agent_id) ?: '';
        $full_name = trim($first_name . ' ' . $last_name) ?: get_the_title($agent_id);
        
        $agent_data = [
            'id' => $agent_id,
            'name' => $full_name,
            'title' => get_field('title', $agent_id) ?: 'REAL ESTATE AGENT',
            'phone' => get_field('phone', $agent_id),
            'email' => get_field('email', $agent_id),
            'photo' => get_field('profile_photo', $agent_id),
            'license' => get_field('license_number', $agent_id),
            'brokerage' => get_field('brokerage', $agent_id)
        ];
    }
}

// Get property details for form
$property_title = get_the_title($listing_id);
$street_address = trim(get_field('street_number', $listing_id) . ' ' . 
                      get_field('street_name', $listing_id) . ' ' . 
                      get_field('street_type', $listing_id));
?>

<?php if ($agent_data): ?>
<div class="sidebar-widget">
    
    <!-- Agent Photo - Large Centered -->
    <div style="text-align: center; margin-bottom: 1.5rem;">
        <?php 
        $agent_photo = null;
        if ($agent_data['photo']) {
            if (is_array($agent_data['photo'])) {
                $agent_photo = $agent_data['photo']['sizes']['medium'] ?? $agent_data['photo']['url'];
            } elseif (is_numeric($agent_data['photo'])) {
                $agent_photo = wp_get_attachment_image_url($agent_data['photo'], 'medium');
            }
        }
        
        if ($agent_photo): ?>
            <div style="width: 200px; height: 200px; margin: 0 auto; border-radius: 50%; overflow: hidden; background: linear-gradient(135deg, #87CEEB 0%, #4A90E2 100%); padding: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                <img src="<?php echo esc_url($agent_photo); ?>" 
                     alt="<?php echo esc_attr($agent_data['name']); ?>" 
                     style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
            </div>
        <?php else: ?>
            <div style="width: 200px; height: 200px; margin: 0 auto; border-radius: 50%; background: linear-gradient(135deg, #87CEEB 0%, #4A90E2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 4rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                <i class="fas fa-user-tie"></i>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Agent Name and Title -->
    <div style="text-align: center; margin-bottom: 1.5rem;">
        <h3 style="font-size: 1.5rem; font-weight: 600; color: #2d3748; margin: 0 0 0.5rem 0;">
            <?php echo esc_html($agent_data['name']); ?>
        </h3>
        <div style="font-size: 0.875rem; font-weight: 500; color: #87CEEB; text-transform: uppercase; letter-spacing: 0.5px;">
            <?php echo esc_html($agent_data['title']); ?>
        </div>
    </div>
    
    <!-- Contact Information -->
    <div style="text-align: center; margin-bottom: 1.5rem;">
        <?php if ($agent_data['phone']): ?>
            <div style="margin-bottom: 0.75rem;">
                <i class="fas fa-phone" style="color: #87CEEB; margin-right: 8px; width: 16px;"></i>
                <a href="tel:<?php echo esc_attr($agent_data['phone']); ?>" 
                   style="color: #4a5568; text-decoration: none; font-weight: 500;">
                    <?php echo esc_html($agent_data['phone']); ?>
                </a>
            </div>
        <?php endif; ?>
        
        <?php if ($agent_data['email']): ?>
            <div>
                <i class="fas fa-envelope" style="color: #87CEEB; margin-right: 8px; width: 16px;"></i>
                <a href="mailto:<?php echo esc_attr($agent_data['email']); ?>" 
                   style="color: #87CEEB; text-decoration: none; font-weight: 500;">
                    <?php echo esc_html($agent_data['email']); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Call and Email Buttons -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1.5rem;">
        <?php if ($agent_data['phone']): ?>
            <a href="tel:<?php echo esc_attr($agent_data['phone']); ?>" 
               style="background: #87CEEB; color: white; padding: 0.75rem; border-radius: 6px; text-align: center; text-decoration: none; font-weight: 500; transition: all 0.2s;">
                <i class="fas fa-phone" style="margin-right: 6px;"></i>Call
            </a>
        <?php endif; ?>
        
        <?php if ($agent_data['email']): ?>
            <a href="mailto:<?php echo esc_attr($agent_data['email']); ?>" 
               style="background: #87CEEB; color: white; padding: 0.75rem; border-radius: 6px; text-align: center; text-decoration: none; font-weight: 500; transition: all 0.2s;">
                <i class="fas fa-envelope" style="margin-right: 6px;"></i>Email
            </a>
        <?php endif; ?>
    </div>
    
    <!-- Contact Agent Section Header -->
    <div style="text-align: center; margin: 2rem 0 1rem 0; padding-bottom: 0.75rem; border-bottom: 2px solid #e2e8f0; position: relative;">
        <h4 style="font-size: 1.25rem; font-weight: 600; color: #2d3748; margin: 0;">Contact Agent</h4>
        <div style="position: absolute; bottom: -2px; left: 50%; transform: translateX(-50%); width: 3rem; height: 2px; background: #87CEEB;"></div>
    </div>
    
    <!-- Contact Form -->
    <form class="agent-contact-form" id="agent-contact-form-<?php echo $listing_id; ?>">
        
        <div style="margin-bottom: 1rem;">
            <input type="text" 
                   name="name" 
                   placeholder="Your Name" 
                   required 
                   style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.875rem; background: #f7fafc; transition: all 0.2s;">
        </div>
        
        <div style="margin-bottom: 1rem;">
            <input type="email" 
                   name="email" 
                   placeholder="Your Email" 
                   required 
                   style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.875rem; background: #f7fafc; transition: all 0.2s;">
        </div>
        
        <div style="margin-bottom: 1rem;">
            <input type="tel" 
                   name="phone" 
                   placeholder="Your Phone" 
                   style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.875rem; background: #f7fafc; transition: all 0.2s;">
        </div>
        
        <div style="margin-bottom: 1.5rem;">
            <textarea name="message" 
                      rows="4" 
                      placeholder="I'm interested in <?php echo esc_attr($property_title); ?><?php if ($street_address): ?> at <?php echo esc_attr($street_address); ?><?php endif; ?>" 
                      style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.875rem; background: #f7fafc; resize: vertical; min-height: 100px; transition: all 0.2s;"></textarea>
        </div>
        
        <!-- Hidden fields -->
        <input type="hidden" name="listing_id" value="<?php echo esc_attr($listing_id); ?>">
        <input type="hidden" name="agent_id" value="<?php echo esc_attr($agent_data['id']); ?>">
        <input type="hidden" name="action" value="hph_route_form">
        <input type="hidden" name="route_type" value="property_inquiry">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('hph_lead_nonce'); ?>">
        
        <div id="form-message-<?php echo $listing_id; ?>" style="display: none; padding: 0.75rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.875rem;"></div>
        
        <button type="submit" 
                style="width: 100%; background: #87CEEB; color: white; padding: 1rem; border: none; border-radius: 6px; font-weight: 500; cursor: pointer; transition: all 0.2s; font-size: 1rem;">
            <i class="fas fa-paper-plane" style="margin-right: 8px;"></i>
            <span class="submit-text">Send Message</span>
        </button>
        
    </form>
    
</div>

<style>
/* Hover effects for buttons */
.sidebar-widget a[href^="tel"]:hover,
.sidebar-widget a[href^="mailto"]:hover {
    background: #4A90E2 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(135, 206, 235, 0.3);
}

.sidebar-widget input:focus,
.sidebar-widget textarea:focus {
    outline: none;
    border-color: #87CEEB;
    background: white;
    box-shadow: 0 0 0 3px rgba(135, 206, 235, 0.1);
}

.sidebar-widget button:hover {
    background: #4A90E2 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(135, 206, 235, 0.4);
}

.sidebar-widget button:disabled {
    background: #a0aec0 !important;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.form-message-success {
    background: #f0fff4;
    border: 1px solid #9ae6b4;
    color: #2f855a;
}

.form-message-error {
    background: #fed7d7;
    border: 1px solid #feb2b2;
    color: #c53030;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('agent-contact-form-<?php echo $listing_id; ?>');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const messageDiv = document.getElementById('form-message-<?php echo $listing_id; ?>');
        const submitBtn = this.querySelector('button[type="submit"]');
        const submitText = submitBtn.querySelector('.submit-text');
        
        // Show loading state
        submitBtn.disabled = true;
        submitText.textContent = 'Sending...';
        submitBtn.querySelector('i').className = 'fas fa-spinner fa-spin';
        
        fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            messageDiv.style.display = 'block';
            if (data.success) {
                messageDiv.className = 'form-message-success';
                messageDiv.textContent = data.data?.message || 'Message sent successfully!';
                form.reset();
            } else {
                messageDiv.className = 'form-message-error';
                messageDiv.textContent = data.data?.message || 'An error occurred. Please try again.';
            }
            setTimeout(() => messageDiv.style.display = 'none', 5000);
        })
        .catch(error => {
            messageDiv.style.display = 'block';
            messageDiv.className = 'form-message-error';
            messageDiv.textContent = 'Network error. Please try again.';
        })
        .finally(() => {
            // Reset button
            submitBtn.disabled = false;
            submitText.textContent = 'Send Message';
            submitBtn.querySelector('i').className = 'fas fa-paper-plane';
        });
    });
});
</script>

<?php else: ?>

<!-- No Agent Fallback -->
<div class="sidebar-widget" style="text-align: center;">
    <div style="width: 120px; height: 120px; margin: 0 auto 1.5rem; border-radius: 50%; background: linear-gradient(135deg, #87CEEB 0%, #4A90E2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
        <i class="fas fa-building"></i>
    </div>
    <h3 style="font-size: 1.25rem; color: #2d3748; margin-bottom: 1rem;">Contact for Information</h3>
    <p style="color: #4a5568; margin-bottom: 1.5rem;">For details about this property, please contact us.</p>
    
    <form class="general-contact-form" id="general-contact-form-<?php echo $listing_id; ?>">
        <div style="margin-bottom: 1rem;">
            <input type="text" name="name" placeholder="Your Name" required 
                   style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.875rem; background: #f7fafc;">
        </div>
        <div style="margin-bottom: 1rem;">
            <input type="email" name="email" placeholder="Your Email" required 
                   style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.875rem; background: #f7fafc;">
        </div>
        <div style="margin-bottom: 1rem;">
            <input type="tel" name="phone" placeholder="Your Phone" 
                   style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.875rem; background: #f7fafc;">
        </div>
        <div style="margin-bottom: 1.5rem;">
            <textarea name="message" rows="4" placeholder="I'm interested in <?php echo esc_attr($property_title); ?><?php if ($street_address): ?> at <?php echo esc_attr($street_address); ?><?php endif; ?>" 
                      style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.875rem; background: #f7fafc; resize: vertical; min-height: 100px;"></textarea>
        </div>
        
        <input type="hidden" name="listing_id" value="<?php echo esc_attr($listing_id); ?>">
        <input type="hidden" name="action" value="hph_route_form">
        <input type="hidden" name="route_type" value="property_inquiry">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('hph_lead_nonce'); ?>">
        
        <div id="general-form-message-<?php echo $listing_id; ?>" style="display: none;" class="form-message"></div>
        
        <button type="submit" 
                style="width: 100%; background: #87CEEB; color: white; padding: 1rem; border: none; border-radius: 6px; font-weight: 500; cursor: pointer; transition: all 0.2s; font-size: 1rem;">
            <i class="fas fa-paper-plane" style="margin-right: 8px;"></i>
            <span class="submit-text">Send Message</span>
        </button>
    </form>
</div>

<?php endif; ?>