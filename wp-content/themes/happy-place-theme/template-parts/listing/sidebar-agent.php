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
    <div class="hph-text-center hph-mb-lg">
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
            <div style="width: 200px; height: 200px; margin: 0 auto; border-radius: var(--hph-radius-full); overflow: hidden; background: linear-gradient(135deg, var(--hph-primary) 0%, var(--hph-primary-600) 100%); padding: var(--hph-space-4); box-shadow: var(--hph-shadow-md);">
                <img src="<?php echo esc_url($agent_photo); ?>" 
                     alt="<?php echo esc_attr($agent_data['name']); ?>" 
                     style="width: 100%; height: 100%; object-fit: cover; border-radius: var(--hph-radius-full);">
            </div>
        <?php else: ?>
            <div style="width: 200px; height: 200px; margin: 0 auto; border-radius: var(--hph-radius-full); background: linear-gradient(135deg, var(--hph-primary) 0%, var(--hph-primary-600) 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: var(--hph-text-4xl); box-shadow: var(--hph-shadow-md);">
                <i class="fas fa-user-tie"></i>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Agent Name and Title -->
    <div class="hph-text-center hph-mb-lg">
        <h3 class="hph-text-xl hph-font-semibold hph-m-0 hph-mb-sm" style="color: var(--hph-text-color);">
            <?php echo esc_html($agent_data['name']); ?>
        </h3>
        <div class="hph-text-sm hph-font-medium hph-uppercase" style="color: var(--hph-primary); letter-spacing: 0.5px;">
            <?php echo esc_html($agent_data['title']); ?>
        </div>
    </div>
    
    <!-- Contact Information -->
    <div class="hph-text-center hph-mb-lg">
        <?php if ($agent_data['phone']): ?>
            <div class="hph-mb-sm">
                <i class="fas fa-phone" style="color: var(--hph-primary); margin-right: 10px; width: 18px; font-size: 14px;"></i>
                <a href="tel:<?php echo esc_attr($agent_data['phone']); ?>"
                   class="contact-link phone-link" style="color: var(--hph-gray-700); text-decoration: none; font-weight: 500; font-size: 15px;">
                    <?php echo esc_html($agent_data['phone']); ?>
                </a>
            </div>
        <?php endif; ?>

        <?php if ($agent_data['email']): ?>
            <div>
                <i class="fas fa-envelope" style="color: var(--hph-primary); margin-right: 10px; width: 18px; font-size: 14px;"></i>
                <a href="mailto:<?php echo esc_attr($agent_data['email']); ?>"
                   class="contact-link email-link" style="color: var(--hph-gray-700); text-decoration: none; font-weight: 500; font-size: 15px;">
                    <?php echo esc_html($agent_data['email']); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Call and Email Buttons -->
    <div class="hph-grid hph-gap-sm hph-mb-lg" style="grid-template-columns: 1fr 1fr;">
        <?php if ($agent_data['phone']): ?>
            <a href="tel:<?php echo esc_attr($agent_data['phone']); ?>"
               class="action-button call-button" style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 16px; background: var(--hph-primary); color: white; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px; transition: all 0.3s ease;">
                <i class="fas fa-phone" style="font-size: 14px;"></i>
                <span>Call</span>
            </a>
        <?php endif; ?>

        <?php if ($agent_data['email']): ?>
            <a href="mailto:<?php echo esc_attr($agent_data['email']); ?>"
               class="action-button email-button" style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 16px; background: var(--hph-primary); color: white; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px; transition: all 0.3s ease;">
                <i class="fas fa-envelope" style="font-size: 14px;"></i>
                <span>Email</span>
            </a>
        <?php endif; ?>
    </div>
    
    <!-- Contact Agent Section Header -->
    <div class="hph-text-center hph-my-xl hph-pb-sm hph-relative" style="border-bottom: 2px solid var(--hph-gray-200);">
        <h4 class="hph-text-lg hph-font-semibold hph-m-0" style="color: var(--hph-text-color);">Contact Agent</h4>
        <div class="hph-absolute hph-bottom-0 hph-h-px" style="left: 50%; transform: translateX(-50%); width: 3rem; margin-bottom: -2px; background: var(--hph-primary);"></div>
    </div>
    
    <!-- Contact Form -->
    <form class="agent-contact-form" id="agent-contact-form-<?php echo $listing_id; ?>">
        
        <div class="hph-mb-md">
            <input type="text" 
                   name="name" 
                   placeholder="Your Name" 
                   required 
                   class="hph-w-full hph-py-sm hph-px-md hph-border hph-text-sm hph-transition-all" style="border-color: var(--hph-gray-200); background: var(--hph-gray-50); border-radius: var(--hph-input-radius);">
        </div>
        
        <div class="hph-mb-md">
            <input type="email" 
                   name="email" 
                   placeholder="Your Email" 
                   required 
                   class="hph-w-full hph-py-sm hph-px-md hph-border hph-text-sm hph-transition-all" style="border-color: var(--hph-gray-200); background: var(--hph-gray-50); border-radius: var(--hph-input-radius);">
        </div>
        
        <div class="hph-mb-md">
            <input type="tel" 
                   name="phone" 
                   placeholder="Your Phone" 
                   class="hph-w-full hph-py-sm hph-px-md hph-border hph-text-sm hph-transition-all" style="border-color: var(--hph-gray-200); background: var(--hph-gray-50); border-radius: var(--hph-input-radius);">
        </div>
        
        <div class="hph-mb-lg">
            <textarea name="message" 
                      rows="4" 
                      placeholder="I'm interested in <?php echo esc_attr($property_title); ?><?php if ($street_address): ?> at <?php echo esc_attr($street_address); ?><?php endif; ?>" 
                      class="hph-w-full hph-py-sm hph-px-md hph-border hph-text-sm hph-transition-all" style="border-color: var(--hph-gray-200); background: var(--hph-gray-50); border-radius: var(--hph-input-radius); resize: vertical; min-height: 100px;"></textarea>
        </div>
        
        <!-- Hidden fields -->
        <input type="hidden" name="listing_id" value="<?php echo esc_attr($listing_id); ?>">
        <input type="hidden" name="agent_id" value="<?php echo esc_attr($agent_data['id']); ?>">
        <input type="hidden" name="action" value="hph_route_form">
        <input type="hidden" name="route_type" value="property_inquiry">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('hph_lead_nonce'); ?>">
        
        <div id="form-message-<?php echo $listing_id; ?>" class="hph-hidden hph-py-sm hph-px-md hph-mb-md hph-text-sm" style="border-radius: var(--hph-radius-md);"></div>

        <button type="submit"
                class="submit-button" style="display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 14px 20px; background: var(--hph-primary); color: white; border: none; border-radius: 8px; font-weight: 600; font-size: 15px; cursor: pointer; transition: all 0.3s ease;">
            <i class="fas fa-paper-plane" style="font-size: 14px;"></i>
            <span class="submit-text">Send Message</span>
        </button>
        
    </form>
    
</div>

<style>
/* Contact link hover effects */
.contact-link:hover {
    color: var(--hph-primary) !important;
    text-decoration: underline;
}

/* Action button hover effects */
.action-button:hover {
    background: #2980b9 !important;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.action-button:active {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

/* Submit button hover effects */
.submit-button:hover {
    background: #2980b9 !important;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.submit-button:active {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.submit-button:disabled {
    background: var(--hph-gray-400) !important;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
    opacity: 0.7;
}

/* Form input focus styles */
.sidebar-widget input:focus,
.sidebar-widget textarea:focus {
    outline: none;
    border-color: var(--hph-primary);
    background: var(--hph-white);
    box-shadow: 0 0 0 3px rgba(80, 186, 225, 0.1);
}

/* Form messages */
.form-message-success {
    background: var(--hph-success-light);
    border: 1px solid var(--hph-success);
    color: var(--hph-success-dark);
}

.form-message-error {
    background: var(--hph-danger-light);
    border: 1px solid var(--hph-danger);
    color: var(--hph-danger-dark);
}

/* Icon styling consistency */
.sidebar-widget .fas {
    vertical-align: middle;
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
<div class="sidebar-widget hph-text-center">
    <div style="width: 120px; height: 120px; margin: 0 auto var(--hph-space-6); border-radius: var(--hph-radius-full); background: linear-gradient(135deg, var(--hph-primary) 0%, var(--hph-primary-600) 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: var(--hph-text-3xl);">
        <i class="fas fa-building"></i>
    </div>
    <h3 class="hph-text-lg hph-mb-md" style="color: var(--hph-text-color);">Contact for Information</h3>
    <p class="hph-mb-lg" style="color: var(--hph-text-light);">For details about this property, please contact us.</p>
    
    <form class="general-contact-form" id="general-contact-form-<?php echo $listing_id; ?>">
        <div class="hph-mb-md">
            <input type="text" name="name" placeholder="Your Name" required 
                   class="hph-w-full hph-py-sm hph-px-md hph-border hph-text-sm" style="border-color: var(--hph-gray-200); background: var(--hph-gray-50); border-radius: var(--hph-input-radius);">
        </div>
        <div class="hph-mb-md">
            <input type="email" name="email" placeholder="Your Email" required 
                   class="hph-w-full hph-py-sm hph-px-md hph-border hph-text-sm" style="border-color: var(--hph-gray-200); background: var(--hph-gray-50); border-radius: var(--hph-input-radius);">
        </div>
        <div class="hph-mb-md">
            <input type="tel" name="phone" placeholder="Your Phone" 
                   class="hph-w-full hph-py-sm hph-px-md hph-border hph-text-sm" style="border-color: var(--hph-gray-200); background: var(--hph-gray-50); border-radius: var(--hph-input-radius);">
        </div>
        <div class="hph-mb-lg">
            <textarea name="message" rows="4" placeholder="I'm interested in <?php echo esc_attr($property_title); ?><?php if ($street_address): ?> at <?php echo esc_attr($street_address); ?><?php endif; ?>" 
                      class="hph-w-full hph-py-sm hph-px-md hph-border hph-text-sm" style="border-color: var(--hph-gray-200); background: var(--hph-gray-50); border-radius: var(--hph-input-radius); resize: vertical; min-height: 100px;"></textarea>
        </div>
        
        <input type="hidden" name="listing_id" value="<?php echo esc_attr($listing_id); ?>">
        <input type="hidden" name="action" value="hph_route_form">
        <input type="hidden" name="route_type" value="property_inquiry">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('hph_lead_nonce'); ?>">
        
        <div id="general-form-message-<?php echo $listing_id; ?>" class="hph-hidden form-message"></div>

        <button type="submit"
                class="submit-button" style="display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 14px 20px; background: var(--hph-primary); color: white; border: none; border-radius: 8px; font-weight: 600; font-size: 15px; cursor: pointer; transition: all 0.3s ease;">
            <i class="fas fa-paper-plane" style="font-size: 14px;"></i>
            <span class="submit-text">Send Message</span>
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile form toggle functionality
    if (window.innerWidth <= 480) {
        const formHeader = document.querySelector('.sidebar-widget .hph-text-center.hph-my-xl');
        const contactForm = document.querySelector('.sidebar-widget .agent-contact-form');
        
        if (formHeader && contactForm) {
            formHeader.style.cursor = 'pointer';
            formHeader.style.userSelect = 'none';
            
            formHeader.addEventListener('click', function() {
                const isExpanded = this.classList.contains('expanded');
                
                if (isExpanded) {
                    this.classList.remove('expanded');
                    contactForm.style.display = 'none';
                } else {
                    this.classList.add('expanded');
                    contactForm.style.display = 'block';
                }
            });
        }
    }
    
    // Responsive adjustments on window resize
    window.addEventListener('resize', function() {
        const formHeader = document.querySelector('.sidebar-widget .hph-text-center.hph-my-xl');
        const contactForm = document.querySelector('.sidebar-widget .agent-contact-form');
        
        if (formHeader && contactForm) {
            if (window.innerWidth > 480) {
                // Desktop/tablet - always show form
                contactForm.style.display = 'block';
                formHeader.classList.remove('expanded');
                formHeader.style.cursor = 'default';
            } else {
                // Mobile - hide form by default
                if (!formHeader.classList.contains('expanded')) {
                    contactForm.style.display = 'none';
                }
                formHeader.style.cursor = 'pointer';
            }
        }
    });
});
</script>

<?php endif; ?>