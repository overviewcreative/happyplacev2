<?php
/**
 * Agent Sidebar Template Part
 * File: template-parts/listing/sidebar-agent.php
 * 
 * Displays agent info and contact form using HPH framework utilities
 * 
 * @package HappyPlaceTheme
 */

$listing_id = $args['listing_id'] ?? get_the_ID();

// Get listing data for calculations
$listing_price = get_field('listing_price', $listing_id);
$property_title = get_field('property_title', $listing_id) ?: get_the_title($listing_id);

// Get agent data - simple and direct
$agent_data = null;
$agent_posts = get_field('listing_agent', $listing_id); // This returns an array of agent posts

// Debug what we're getting
echo '<!-- DEBUG: listing_agent field result: ' . esc_html(print_r($agent_posts, true)) . ' -->';

if (!empty($agent_posts) && is_array($agent_posts)) {
    // Get the first agent from the array
    $agent_post = $agent_posts[0];
    
    if ($agent_post && is_object($agent_post) && $agent_post->post_type === 'agent') {
        // Agent is retrieved via relationship field
        $agent_id = $agent_post->ID;
        echo '<!-- DEBUG: Agent ID found: ' . esc_html($agent_id) . ' -->';
        
        $first_name = get_field('first_name', $agent_id) ?: '';
        $last_name = get_field('last_name', $agent_id) ?: '';
        $full_name = trim($first_name . ' ' . $last_name) ?: get_the_title($agent_id);
        
        echo '<!-- DEBUG: First name: ' . esc_html($first_name) . ', Last name: ' . esc_html($last_name) . ', Full name: ' . esc_html($full_name) . ' -->';
        
        $office_id = get_field('office', $agent_id);
        $brokerage_name = $office_id ? get_the_title($office_id) : '';
        
        $agent_data = array(
            'id' => $agent_id,
            'name' => $full_name,
            'title' => get_field('title', $agent_id) ?: 'Real Estate Agent',
            'phone' => get_field('phone', $agent_id),
            'mobile' => get_field('phone', $agent_id),
            'email' => get_field('email', $agent_id),
            'photo' => get_field('profile_photo', $agent_id),
            'license' => get_field('license_number', $agent_id),
            'bio' => get_field('bio', $agent_id),
            'brokerage' => $brokerage_name,
        );
        
        echo '<!-- DEBUG: Final agent_data: ' . esc_html(print_r($agent_data, true)) . ' -->';
    } else {
        echo '<!-- DEBUG: Wrong post type. Expected "agent", got: ' . esc_html($agent_post->post_type ?? 'unknown') . ' -->';
        
        // TEMPORARY: Since we're getting an attachment that looks like it should be an agent,
        // let's try to find the actual agent post by searching for the name
        $agent_name = $agent_post->post_title ?? '';
        if ($agent_name) {
            $agent_query = new WP_Query(array(
                'post_type' => 'agent',
                'title' => $agent_name,
                'posts_per_page' => 1
            ));
            
            if ($agent_query->have_posts()) {
                $real_agent_post = $agent_query->posts[0];
                $agent_id = $real_agent_post->ID;
                
                echo '<!-- DEBUG: Found matching agent post by name. Agent ID: ' . esc_html($agent_id) . ' -->';
                
                $first_name = get_field('first_name', $agent_id) ?: '';
                $last_name = get_field('last_name', $agent_id) ?: '';
                $full_name = trim($first_name . ' ' . $last_name) ?: get_the_title($agent_id);
                
                $office_id = get_field('office', $agent_id);
                $brokerage_name = $office_id ? get_the_title($office_id) : '';
                
                $agent_data = array(
                    'id' => $agent_id,
                    'name' => $full_name,
                    'title' => get_field('title', $agent_id) ?: 'Real Estate Agent',
                    'phone' => get_field('phone', $agent_id),
                    'mobile' => get_field('phone', $agent_id),
                    'email' => get_field('email', $agent_id),
                    'photo' => get_field('profile_photo', $agent_id),
                    'license' => get_field('license_number', $agent_id),
                    'bio' => get_field('bio', $agent_id),
                    'brokerage' => $brokerage_name,
                );
            }
        }
    }
} else {
    echo '<!-- DEBUG: No agent posts found or not an array. Agent posts: ' . esc_html(print_r($agent_posts, true)) . ' -->';
}

// Property address for contact form
$street_address = trim(get_field('street_number', $listing_id) . ' ' . 
                      get_field('street_name', $listing_id) . ' ' . 
                      get_field('street_type', $listing_id));

// Debug output (remove in production)
if (defined('WP_DEBUG') && WP_DEBUG && current_user_can('manage_options')) {
    echo '<!-- Debug: Agent Data -->';
    echo '<!-- Listing ID: ' . esc_html($listing_id) . ' -->';
    echo '<!-- Agent Data: ' . esc_html(print_r($agent_data, true)) . ' -->';
    echo '<!-- Street Address: ' . esc_html($street_address) . ' -->';
}
?>

    <!-- Agent Information -->
    <?php if ($agent_data) : ?>
    <div class="hph-widget hph-widget--agent hph-mb-xl hph-p-lg hph-bg-white hph-rounded-lg hph-shadow-md">
        
        <!-- Agent Photo Row -->
        <div class="hph-text-center hph-mb-lg hph-py-lg">
            <?php 
            // Handle agent photo with better fallbacks
            $agent_photo = null;
            if (!empty($agent_data['photo'])) {
                if (is_array($agent_data['photo'])) {
                    // ACF image field returns array
                    $agent_photo = $agent_data['photo']['url'] ?? $agent_data['photo']['sizes']['medium'] ?? null;
                } elseif (is_numeric($agent_data['photo'])) {
                    // Attachment ID
                    $agent_photo = wp_get_attachment_image_url($agent_data['photo'], 'medium');
                } else {
                    // Direct URL
                    $agent_photo = $agent_data['photo'];
                }
            }
            
            if ($agent_photo) : ?>
            <img src="<?php echo esc_url($agent_photo); ?>" 
                 alt="<?php echo esc_attr($agent_data['name']); ?>" 
                 class="hph-agent-photo hph-w-100 hph-h-100 hph-rounded-full hph-mx-auto hph-block"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
            <div class="hph-agent-photo-placeholder hph-w-48 hph-h-48 hph-rounded-full hph-bg-gray-200 hph-flex hph-items-center hph-justify-center hph-mx-auto" style="display:none;">
                <i class="fas fa-user-tie hph-text-gray-500 hph-text-5xl"></i>
            </div>
            <?php else : ?>
            <div class="hph-agent-photo-placeholder hph-w-48 hph-h-48 hph-rounded-full hph-bg-gray-200 hph-flex hph-items-center hph-justify-center hph-mx-auto">
                <i class="fas fa-user-tie hph-text-gray-500 hph-text-5xl"></i>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Agent Name Row -->
        <div class="hph-text-center hph-mb-sm">
            <h3 class="hph-agent-name hph-text-xl hph-font-bold hph-text-gray-900">
                <?php echo esc_html($agent_data['name']); ?>
            </h3>
        </div>
        
        <!-- Agent Title Row -->
        <?php if ($agent_data['title']) : ?>
        <div class="hph-text-center hph-mb-md">
            <div class="hph-agent-title hph-text-sm hph-font-medium hph-text-blue-600 hph-uppercase hph-tracking-wide">
                <?php echo esc_html($agent_data['title']); ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Contact Phone Row -->
        <?php 
        $contact_phone = $agent_data['mobile'] ?: $agent_data['phone'];
        if ($contact_phone) : ?>
        <div class="hph-text-center hph-mb-xs">
            <i class="fas fa-phone hph-text-gray-400 hph-text-sm hph-mr-xs"></i>
            <span class="hph-text-sm hph-text-gray-700"><?php echo esc_html($contact_phone); ?></span>
        </div>
        <?php endif; ?>
        
        <!-- Contact Email Row -->
        <?php if ($agent_data['email']) : ?>
        <div class="hph-text-center hph-mb-md">
            <i class="fas fa-envelope hph-text-gray-400 hph-text-sm hph-mr-xs"></i>
            <span class="hph-text-sm hph-text-gray-700"><?php echo esc_html($agent_data['email']); ?></span>
        </div>
        <?php endif; ?>
        
        <!-- Brokerage Row -->
        <?php if ($agent_data['brokerage']) : ?>
        <div class="hph-text-center hph-mb-xs">
            <div class="hph-agent-brokerage hph-text-sm hph-text-gray-600">
                <?php echo esc_html($agent_data['brokerage']); ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- License Row -->
        <?php if ($agent_data['license']) : ?>
        <div class="hph-text-center hph-mb-lg">
            <div class="hph-agent-license hph-text-xs hph-text-gray-500">
                License: <?php echo esc_html($agent_data['license']); ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Quick Contact Buttons -->
        <div class="hph-agent-actions hph-grid hph-grid-cols-2 hph-gap-sm hph-mb-lg">
            <?php 
            // Use mobile first, then phone
            $contact_phone = $agent_data['mobile'] ?: $agent_data['phone'];
            if ($contact_phone) : ?>
            <a href="tel:<?php echo esc_attr($contact_phone); ?>" 
               class="hph-btn hph-btn--secondary hph-btn--sm hph-flex hph-items-center hph-justify-center hph-gap-xs">
                <i class="fas fa-phone"></i>
                <span>Call</span>
            </a>
            <?php endif; ?>
            
            <?php if ($agent_data['email']) : ?>
            <a href="mailto:<?php echo esc_attr($agent_data['email']); ?>" 
               class="hph-btn hph-btn--secondary hph-btn--sm hph-flex hph-items-center hph-justify-center hph-gap-xs">
                <i class="fas fa-envelope"></i>
                <span>Email</span>
            </a>
            <?php endif; ?>
        </div>
        
        <!-- Contact Form -->
        <div class="hph-agent-form">
            <h4 class="hph-form-title hph-text-lg hph-font-semibold hph-mb-md">Contact Agent</h4>
            
            <form class="hph-agent-contact-form hph-space-y-md" id="agent-contact-form">
                <div class="hph-form-group">
                    <input type="text" 
                           name="name" 
                           placeholder="Your Name" 
                           required
                           class="hph-form-control hph-w-full hph-px-md hph-py-sm hph-border hph-border-gray-300 hph-rounded-md">
                </div>
                
                <div class="hph-form-group">
                    <input type="email" 
                           name="email" 
                           placeholder="Your Email" 
                           required
                           class="hph-form-control hph-w-full hph-px-md hph-py-sm hph-border hph-border-gray-300 hph-rounded-md">
                </div>
                
                <div class="hph-form-group">
                    <input type="tel" 
                           name="phone" 
                           placeholder="Your Phone"
                           class="hph-form-control hph-w-full hph-px-md hph-py-sm hph-border hph-border-gray-300 hph-rounded-md">
                </div>
                
                <div class="hph-form-group">
                    <textarea name="message" 
                              rows="4" 
                              placeholder="I'm interested in <?php echo esc_attr($property_title); ?> at <?php echo esc_attr($street_address); ?>"
                              class="hph-form-control hph-w-full hph-px-md hph-py-sm hph-border hph-border-gray-300 hph-rounded-md"></textarea>
                </div>
                
                <input type="hidden" name="listing_id" value="<?php echo esc_attr($listing_id); ?>">
                <input type="hidden" name="agent_id" value="<?php echo esc_attr($agent_data['id']); ?>">
                <input type="hidden" name="lead_type" value="agent_contact">
                <input type="hidden" name="action" value="hph_submit_lead">
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('hph_lead_nonce'); ?>">
                
                <div id="agent-form-message" style="display: none;" class="hph-form-message"></div>
                
                <button type="submit" 
                        class="hph-btn hph-btn--primary hph-btn--full hph-py-md hph-font-semibold">
                    <i class="fas fa-paper-plane"></i> Send Message
                </button>
            </form>
        </div>
        
        <!-- Quick Actions within Agent Section -->
        <div class="hph-quick-actions hph-grid hph-grid-cols-2 hph-gap-sm hph-mt-lg hph-pt-lg hph-border-t hph-border-gray-200">
            
            <button class="hph-action-btn hph-p-md hph-text-center hph-border hph-border-gray-300 hph-rounded-md hph-transition-all hover:hph-bg-primary-50 hover:hph-border-primary">
                <i class="fas fa-calculator hph-text-xl hph-mb-xs"></i>
                <span class="hph-text-xs">Mortgage Calc</span>
            </button>
            
            <button class="hph-action-btn hph-p-md hph-text-center hph-border hph-border-gray-300 hph-rounded-md hph-transition-all hover:hph-bg-primary-50 hover:hph-border-primary">
                <i class="fas fa-print hph-text-xl hph-mb-xs"></i>
                <span class="hph-text-xs">Print</span>
            </button>
            
            <button class="hph-action-btn hph-p-md hph-text-center hph-border hph-border-gray-300 hph-rounded-md hph-transition-all hover:hph-bg-primary-50 hover:hph-border-primary">
                <i class="fas fa-share hph-text-xl hph-mb-xs"></i>
                <span class="hph-text-xs">Share</span>
            </button>
            
            <button class="hph-action-btn hph-p-md hph-text-center hph-border hph-border-gray-300 hph-rounded-md hph-transition-all hover:hph-bg-primary-50 hover:hph-border-primary">
                <i class="fas fa-heart hph-text-xl hph-mb-xs"></i>
                <span class="hph-text-xs">Save</span>
            </button>
            
        </div>
        
    </div>
    <?php else : ?>
    
    <!-- No Agent Data Fallback -->
    <div class="hph-widget hph-widget--agent hph-mb-xl hph-p-lg hph-bg-white hph-rounded-lg hph-shadow-md hph-text-center">
        <div class="hph-agent-photo-placeholder hph-w-20 hph-h-20 hph-rounded-full hph-mx-auto hph-mb-md hph-bg-gray-200 hph-flex hph-items-center hph-justify-center">
            <i class="fas fa-user-tie hph-text-gray-500 hph-text-xl"></i>
        </div>
        
        <h3 class="hph-text-lg hph-font-semibold hph-mb-sm">Contact for More Information</h3>
        <p class="hph-text-sm hph-text-gray-600 hph-mb-lg">
            For details about this property, please contact us directly.
        </p>
        
        <!-- Generic Contact Form -->
        <form class="hph-contact-form hph-space-y-md" id="general-contact-form">
            <div class="hph-form-group">
                <input type="text" 
                       name="name" 
                       placeholder="Your Name" 
                       required
                       class="hph-form-control hph-w-full hph-px-md hph-py-sm hph-border hph-border-gray-300 hph-rounded-md">
            </div>
            
            <div class="hph-form-group">
                <input type="email" 
                       name="email" 
                       placeholder="Your Email" 
                       required
                       class="hph-form-control hph-w-full hph-px-md hph-py-sm hph-border hph-border-gray-300 hph-rounded-md">
            </div>
            
            <div class="hph-form-group">
                <input type="tel" 
                       name="phone" 
                       placeholder="Your Phone"
                       class="hph-form-control hph-w-full hph-px-md hph-py-sm hph-border hph-border-gray-300 hph-rounded-md">
            </div>
            
            <div class="hph-form-group">
                <textarea name="message" 
                          rows="3" 
                          placeholder="I'm interested in <?php echo esc_attr($property_title); ?> at <?php echo esc_attr($street_address); ?>"
                          class="hph-form-control hph-w-full hph-px-md hph-py-sm hph-border hph-border-gray-300 hph-rounded-md"></textarea>
            </div>
            
            <input type="hidden" name="listing_id" value="<?php echo esc_attr($listing_id); ?>">
            <input type="hidden" name="lead_type" value="general_inquiry">
            <input type="hidden" name="action" value="hph_submit_lead">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('hph_lead_nonce'); ?>">
            
            <div id="general-form-message" style="display: none;" class="hph-form-message"></div>
            
            <button type="submit" 
                    class="hph-btn hph-btn--primary hph-btn--full hph-py-md hph-font-semibold">
                <i class="fas fa-paper-plane"></i> Send Inquiry
            </button>
        </form>
        
        <!-- Quick Actions for Fallback Section -->
        <div class="hph-quick-actions hph-grid hph-grid-cols-2 hph-gap-sm hph-mt-lg hph-pt-lg hph-border-t hph-border-gray-200">
            
            <button class="hph-action-btn hph-p-md hph-text-center hph-border hph-border-gray-300 hph-rounded-md hph-transition-all hover:hph-bg-primary-50 hover:hph-border-primary">
                <i class="fas fa-calculator hph-text-xl hph-mb-xs"></i>
                <span class="hph-text-xs">Mortgage Calc</span>
            </button>
            
            <button class="hph-action-btn hph-p-md hph-text-center hph-border hph-border-gray-300 hph-rounded-md hph-transition-all hover:hph-bg-primary-50 hover:hph-border-primary">
                <i class="fas fa-print hph-text-xl hph-mb-xs"></i>
                <span class="hph-text-xs">Print</span>
            </button>
            
            <button class="hph-action-btn hph-p-md hph-text-center hph-border hph-border-gray-300 hph-rounded-md hph-transition-all hover:hph-bg-primary-50 hover:hph-border-primary">
                <i class="fas fa-share hph-text-xl hph-mb-xs"></i>
                <span class="hph-text-xs">Share</span>
            </button>
            
            <button class="hph-action-btn hph-p-md hph-text-center hph-border hph-border-gray-300 hph-rounded-md hph-transition-all hover:hph-bg-primary-50 hover:hph-border-primary">
                <i class="fas fa-heart hph-text-xl hph-mb-xs"></i>
                <span class="hph-text-xs">Save</span>
            </button>
            
        </div>
    </div>
    
    <?php endif; ?>

<script>
// Handle Agent Contact Form Submissions (avoiding conflicts with theme contact form)
document.addEventListener('DOMContentLoaded', function() {
    
    // Agent Contact Form (specific to sidebar)
    const agentForm = document.getElementById('agent-contact-form');
    if (agentForm) {
        agentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleAgentFormSubmission(this, 'agent-form-message');
        });
    }
    
    // General Contact Form (when no agent)
    const generalForm = document.getElementById('general-contact-form');
    if (generalForm) {
        generalForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleAgentFormSubmission(this, 'general-form-message');
        });
    }
    
    function handleAgentFormSubmission(form, messageId) {
        const formData = new FormData(form);
        const messageDiv = document.getElementById(messageId);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        
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
                messageDiv.className = 'hph-form-message hph-p-md hph-rounded-md hph-bg-green-100 hph-text-green-700 hph-border hph-border-green-200';
                messageDiv.textContent = data.data || 'Message sent successfully!';
                form.reset();
            } else {
                messageDiv.className = 'hph-form-message hph-p-md hph-rounded-md hph-bg-red-100 hph-text-red-700 hph-border hph-border-red-200';
                messageDiv.textContent = data.data || 'An error occurred. Please try again.';
            }
            
            // Hide message after 5 seconds
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 5000);
        })
        .catch(error => {
            console.error('Form submission error:', error);
            messageDiv.style.display = 'block';
            messageDiv.className = 'hph-form-message hph-p-md hph-rounded-md hph-bg-red-100 hph-text-red-700 hph-border hph-border-red-200';
            messageDiv.textContent = 'Network error. Please check your connection and try again.';
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        });
    }
    
    // Quick Actions
    document.querySelectorAll('.hph-action-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const icon = this.querySelector('i');
            const text = this.querySelector('span').textContent;
            
            switch(text) {
                case 'Mortgage Calc':
                    // Handled by onclick attribute
                    break;
                case 'Print':
                    window.print();
                    break;
                case 'Share':
                    if (navigator.share) {
                        navigator.share({
                            title: document.title,
                            url: window.location.href
                        });
                    } else {
                        // Fallback to copy URL
                        navigator.clipboard.writeText(window.location.href);
                        alert('Link copied to clipboard!');
                    }
                    break;
                case 'Save':
                    // TODO: Add to favorites functionality
                    icon.classList.toggle('far');
                    icon.classList.toggle('fas');
                    console.log('Save listing clicked');
                    break;
            }
        });
    });
});
</script>