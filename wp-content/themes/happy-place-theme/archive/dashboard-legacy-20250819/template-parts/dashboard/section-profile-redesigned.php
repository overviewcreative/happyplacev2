<?php
/**
 * Dashboard Agent Profile Management Section - Redesigned
 * 
 * Modern, intuitive profile editing interface without heavy card reliance
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user and agent data
$current_user = wp_get_current_user();
$dashboard = \HappyPlace\Dashboard\Frontend_Admin_Dashboard::get_instance();

// Check permissions
if (!$dashboard->user_can('edit_profile')) {
    echo '<div class="alert alert-warning">' . __('You do not have permission to edit profiles.', 'happy-place') . '</div>';
    return;
}

// Get agent post for current user
$agent_posts = get_posts([
    'post_type' => 'agent',
    'meta_query' => [
        [
            'key' => 'wordpress_user_id',
            'value' => $current_user->ID,
            'compare' => '='
        ]
    ],
    'posts_per_page' => 1
]);

$agent_post = $agent_posts[0] ?? null;
$agent_id = $agent_post ? $agent_post->ID : 0;

// Get current profile photo
$profile_photo = get_the_post_thumbnail_url($agent_id, 'medium');
$display_name = get_field('display_name', $agent_id) ?: 
    trim(implode(' ', array_filter([
        get_field('first_name', $agent_id),
        get_field('last_name', $agent_id)
    ])));
?>

<div class="profile-management-redesigned">
    
    <!-- Profile Header Section -->
    <div class="profile-hero">
        <div class="profile-hero-bg"></div>
        <div class="profile-hero-content">
            <div class="profile-avatar-section">
                <div class="profile-avatar-container">
                    <div class="profile-avatar">
                        <?php if ($profile_photo): ?>
                            <img src="<?php echo esc_url($profile_photo); ?>" alt="<?php echo esc_attr($display_name); ?>">
                        <?php else: ?>
                            <div class="avatar-placeholder">
                                <span class="avatar-initials">
                                    <?php 
                                    $first = get_field('first_name', $agent_id) ?: 'A';
                                    $last = get_field('last_name', $agent_id) ?: 'G';
                                    echo esc_html(strtoupper(substr($first, 0, 1) . substr($last, 0, 1))); 
                                    ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        <button type="button" class="avatar-upload-btn" title="<?php _e('Change Photo', 'happy-place'); ?>">
                            <span class="hph-icon-camera"></span>
                        </button>
                    </div>
                </div>
                <div class="profile-hero-info">
                    <h1 class="profile-name"><?php echo esc_html($display_name ?: __('Your Name', 'happy-place')); ?></h1>
                    <p class="profile-title"><?php echo esc_html(get_field('title', $agent_id) ?: __('Real Estate Professional', 'happy-place')); ?></p>
                    <div class="profile-status">
                        <span class="status-indicator active"></span>
                        <span class="status-text"><?php _e('Profile Active', 'happy-place'); ?></span>
                    </div>
                </div>
            </div>
            <div class="profile-actions">
                <button type="button" class="btn btn-outline-light" id="preview-profile">
                    <span class="hph-icon-eye"></span>
                    <?php _e('Preview', 'happy-place'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Profile Form -->
    <form id="agent-profile-form" class="profile-form" data-agent-id="<?php echo esc_attr($agent_id); ?>">
        <?php wp_nonce_field('hph_save_agent_profile', 'profile_nonce'); ?>
        
        <!-- Personal Information -->
        <section class="form-section personal-info">
            <div class="section-header">
                <div class="section-icon">
                    <span class="hph-icon-user"></span>
                </div>
                <div class="section-title">
                    <h2><?php _e('Personal Information', 'happy-place'); ?></h2>
                    <p><?php _e('Your basic contact details and professional information', 'happy-place'); ?></p>
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-row name-row">
                    <div class="form-field form-field-primary">
                        <label for="first_name"><?php _e('First Name', 'happy-place'); ?> <span class="required">*</span></label>
                        <input type="text" id="first_name" name="first_name" 
                               value="<?php echo esc_attr(get_field('first_name', $agent_id) ?: ''); ?>" 
                               placeholder="<?php _e('Enter your first name', 'happy-place'); ?>" required>
                    </div>
                    <div class="form-field form-field-secondary">
                        <label for="middle_name"><?php _e('Middle Name', 'happy-place'); ?></label>
                        <input type="text" id="middle_name" name="middle_name" 
                               value="<?php echo esc_attr(get_field('middle_name', $agent_id) ?: ''); ?>"
                               placeholder="<?php _e('Optional', 'happy-place'); ?>">
                    </div>
                    <div class="form-field form-field-primary">
                        <label for="last_name"><?php _e('Last Name', 'happy-place'); ?> <span class="required">*</span></label>
                        <input type="text" id="last_name" name="last_name" 
                               value="<?php echo esc_attr(get_field('last_name', $agent_id) ?: ''); ?>" 
                               placeholder="<?php _e('Enter your last name', 'happy-place'); ?>" required>
                    </div>
                    <div class="form-field form-field-small">
                        <label for="suffix"><?php _e('Suffix', 'happy-place'); ?></label>
                        <select id="suffix" name="suffix">
                            <option value=""><?php _e('None', 'happy-place'); ?></option>
                            <?php
                            $current_suffix = get_field('suffix', $agent_id);
                            $suffixes = ['Jr.', 'Sr.', 'II', 'III', 'IV', 'V'];
                            foreach ($suffixes as $suffix):
                            ?>
                                <option value="<?php echo esc_attr($suffix); ?>" <?php selected($current_suffix, $suffix); ?>>
                                    <?php echo esc_html($suffix); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-field form-field-half">
                        <label for="display_name"><?php _e('Display Name', 'happy-place'); ?></label>
                        <input type="text" id="display_name" name="display_name" 
                               value="<?php echo esc_attr(get_field('display_name', $agent_id) ?: ''); ?>"
                               placeholder="<?php _e('How your name appears publicly', 'happy-place'); ?>">
                        <small class="field-hint"><?php _e('Leave blank to auto-generate from name fields', 'happy-place'); ?></small>
                    </div>
                    <div class="form-field form-field-half">
                        <label for="title"><?php _e('Professional Title', 'happy-place'); ?></label>
                        <input type="text" id="title" name="title" 
                               value="<?php echo esc_attr(get_field('title', $agent_id) ?: ''); ?>"
                               placeholder="<?php _e('e.g., Senior Real Estate Agent', 'happy-place'); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-field form-field-full">
                        <label for="short_bio"><?php _e('Professional Summary', 'happy-place'); ?></label>
                        <textarea id="short_bio" name="short_bio" rows="3" maxlength="200" 
                                  placeholder="<?php _e('Brief professional summary that appears on your profile card...', 'happy-place'); ?>"><?php echo esc_textarea(get_field('short_bio', $agent_id) ?: ''); ?></textarea>
                        <div class="field-footer">
                            <small class="field-hint"><?php _e('This appears on your profile card and listing pages', 'happy-place'); ?></small>
                            <span class="char-counter"><span id="short-bio-count">0</span>/200</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Information -->
        <section class="form-section contact-info">
            <div class="section-header">
                <div class="section-icon">
                    <span class="hph-icon-phone"></span>
                </div>
                <div class="section-title">
                    <h2><?php _e('Contact Information', 'happy-place'); ?></h2>
                    <p><?php _e('How clients can reach you', 'happy-place'); ?></p>
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-row">
                    <div class="form-field form-field-half">
                        <label for="email"><?php _e('Email Address', 'happy-place'); ?> <span class="required">*</span></label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo esc_attr(get_field('email', $agent_id) ?: $current_user->user_email); ?>" 
                               placeholder="<?php _e('your@email.com', 'happy-place'); ?>" required>
                    </div>
                    <div class="form-field form-field-half">
                        <label for="phone"><?php _e('Primary Phone', 'happy-place'); ?></label>
                        <input type="tel" id="phone" name="phone" 
                               value="<?php echo esc_attr(get_field('phone', $agent_id) ?: ''); ?>"
                               placeholder="<?php _e('(555) 123-4567', 'happy-place'); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-field form-field-third">
                        <label for="mobile_phone"><?php _e('Mobile Phone', 'happy-place'); ?></label>
                        <input type="tel" id="mobile_phone" name="mobile_phone" 
                               value="<?php echo esc_attr(get_field('mobile_phone', $agent_id) ?: ''); ?>"
                               placeholder="<?php _e('(555) 123-4567', 'happy-place'); ?>">
                    </div>
                    <div class="form-field form-field-third">
                        <label for="office_phone"><?php _e('Office Phone', 'happy-place'); ?></label>
                        <input type="tel" id="office_phone" name="office_phone" 
                               value="<?php echo esc_attr(get_field('office_phone', $agent_id) ?: ''); ?>"
                               placeholder="<?php _e('(555) 123-4567', 'happy-place'); ?>">
                    </div>
                    <div class="form-field form-field-third">
                        <label for="website_url"><?php _e('Website', 'happy-place'); ?></label>
                        <input type="url" id="website_url" name="website_url" 
                               value="<?php echo esc_attr(get_field('website_url', $agent_id) ?: ''); ?>"
                               placeholder="<?php _e('https://yourwebsite.com', 'happy-place'); ?>">
                    </div>
                </div>
            </div>
        </section>

        <!-- Professional Details -->
        <section class="form-section professional-info">
            <div class="section-header">
                <div class="section-icon">
                    <span class="hph-icon-briefcase"></span>
                </div>
                <div class="section-title">
                    <h2><?php _e('Professional Details', 'happy-place'); ?></h2>
                    <p><?php _e('Your licensing and experience information', 'happy-place'); ?></p>
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-row">
                    <div class="form-field form-field-third">
                        <label for="license_number"><?php _e('License Number', 'happy-place'); ?></label>
                        <input type="text" id="license_number" name="license_number" 
                               value="<?php echo esc_attr(get_field('license_number', $agent_id) ?: ''); ?>"
                               placeholder="<?php _e('Enter license number', 'happy-place'); ?>">
                    </div>
                    <div class="form-field form-field-third">
                        <label for="license_state"><?php _e('License State', 'happy-place'); ?></label>
                        <select id="license_state" name="license_state">
                            <option value=""><?php _e('Select State', 'happy-place'); ?></option>
                            <?php
                            $current_state = get_field('license_state', $agent_id);
                            $states = ['AL', 'AK', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'FL', 'GA', 'HI', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MD', 'MA', 'MI', 'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ', 'NM', 'NY', 'NC', 'ND', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC', 'SD', 'TN', 'TX', 'UT', 'VT', 'VA', 'WA', 'WV', 'WI', 'WY'];
                            foreach ($states as $state):
                            ?>
                                <option value="<?php echo esc_attr($state); ?>" <?php selected($current_state, $state); ?>>
                                    <?php echo esc_html($state); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-field form-field-third">
                        <label for="license_expiration"><?php _e('License Expiration', 'happy-place'); ?></label>
                        <input type="date" id="license_expiration" name="license_expiration" 
                               value="<?php echo esc_attr(get_field('license_expiration', $agent_id) ?: ''); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-field form-field-third">
                        <label for="date_started"><?php _e('Career Start Date', 'happy-place'); ?></label>
                        <input type="date" id="date_started" name="date_started" 
                               value="<?php echo esc_attr(get_field('date_started', $agent_id) ?: ''); ?>">
                    </div>
                    <div class="form-field form-field-third">
                        <label for="years_experience"><?php _e('Years Experience', 'happy-place'); ?></label>
                        <input type="number" id="years_experience" name="years_experience" readonly
                               value="<?php echo esc_attr(get_field('years_experience', $agent_id) ?: ''); ?>">
                        <small class="field-hint"><?php _e('Auto-calculated from start date', 'happy-place'); ?></small>
                    </div>
                    <div class="form-field form-field-third">
                        <label for="office_name"><?php _e('Office/Brokerage', 'happy-place'); ?></label>
                        <input type="text" id="office_name" name="office_name" 
                               value="<?php echo esc_attr(get_field('office_name', $agent_id) ?: ''); ?>"
                               placeholder="<?php _e('Your brokerage name', 'happy-place'); ?>">
                    </div>
                </div>
            </div>
        </section>

        <!-- Specialties & Expertise -->
        <section class="form-section specialties-section">
            <div class="section-header">
                <div class="section-icon">
                    <span class="hph-icon-star"></span>
                </div>
                <div class="section-title">
                    <h2><?php _e('Specialties & Expertise', 'happy-place'); ?></h2>
                    <p><?php _e('What areas do you specialize in?', 'happy-place'); ?></p>
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-row">
                    <div class="form-field form-field-full">
                        <label><?php _e('Property Types & Specialties', 'happy-place'); ?></label>
                        <div class="specialty-grid">
                            <?php
                            $current_specialties = get_field('specialties', $agent_id) ?: [];
                            $specialties = [
                                'buyer_agent' => __('Buyer\'s Agent', 'happy-place'),
                                'listing_agent' => __('Listing Agent', 'happy-place'),
                                'first_time_buyers' => __('First-Time Buyers', 'happy-place'),
                                'luxury_homes' => __('Luxury Homes', 'happy-place'),
                                'commercial' => __('Commercial Real Estate', 'happy-place'),
                                'investment' => __('Investment Properties', 'happy-place'),
                                'condos' => __('Condominiums', 'happy-place'),
                                'new_construction' => __('New Construction', 'happy-place'),
                                'relocation' => __('Relocation Services', 'happy-place'),
                                'foreclosure' => __('Foreclosures/REO', 'happy-place')
                            ];
                            
                            foreach ($specialties as $key => $label):
                            ?>
                                <label class="specialty-option">
                                    <input type="checkbox" name="specialties[]" value="<?php echo esc_attr($key); ?>" 
                                           <?php checked(in_array($key, $current_specialties)); ?>>
                                    <span class="specialty-label"><?php echo esc_html($label); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-field form-field-full">
                        <label><?php _e('Languages Spoken', 'happy-place'); ?></label>
                        <div class="language-grid">
                            <?php
                            $current_languages = get_field('languages', $agent_id) ?: [];
                            $languages = [
                                'english' => __('English', 'happy-place'),
                                'spanish' => __('Spanish', 'happy-place'),
                                'french' => __('French', 'happy-place'),
                                'german' => __('German', 'happy-place'),
                                'italian' => __('Italian', 'happy-place'),
                                'portuguese' => __('Portuguese', 'happy-place'),
                                'chinese' => __('Chinese', 'happy-place'),
                                'japanese' => __('Japanese', 'happy-place'),
                                'korean' => __('Korean', 'happy-place'),
                                'arabic' => __('Arabic', 'happy-place')
                            ];
                            
                            foreach ($languages as $key => $label):
                            ?>
                                <label class="language-option">
                                    <input type="checkbox" name="languages[]" value="<?php echo esc_attr($key); ?>" 
                                           <?php checked(in_array($key, $current_languages)); ?>>
                                    <span class="language-label"><?php echo esc_html($label); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Social Media & Online Presence -->
        <section class="form-section social-media">
            <div class="section-header">
                <div class="section-icon">
                    <span class="hph-icon-share"></span>
                </div>
                <div class="section-title">
                    <h2><?php _e('Online Presence', 'happy-place'); ?></h2>
                    <p><?php _e('Connect your social media profiles', 'happy-place'); ?></p>
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-row">
                    <div class="form-field form-field-half">
                        <label for="facebook_url"><?php _e('Facebook Profile', 'happy-place'); ?></label>
                        <div class="input-with-icon">
                            <span class="input-icon hph-icon-facebook"></span>
                            <input type="url" id="facebook_url" name="facebook_url" 
                                   value="<?php echo esc_attr(get_field('facebook_url', $agent_id) ?: ''); ?>"
                                   placeholder="<?php _e('https://facebook.com/yourprofile', 'happy-place'); ?>">
                        </div>
                    </div>
                    <div class="form-field form-field-half">
                        <label for="instagram_url"><?php _e('Instagram Profile', 'happy-place'); ?></label>
                        <div class="input-with-icon">
                            <span class="input-icon hph-icon-instagram"></span>
                            <input type="url" id="instagram_url" name="instagram_url" 
                                   value="<?php echo esc_attr(get_field('instagram_url', $agent_id) ?: ''); ?>"
                                   placeholder="<?php _e('https://instagram.com/yourprofile', 'happy-place'); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-field form-field-half">
                        <label for="linkedin_url"><?php _e('LinkedIn Profile', 'happy-place'); ?></label>
                        <div class="input-with-icon">
                            <span class="input-icon hph-icon-linkedin"></span>
                            <input type="url" id="linkedin_url" name="linkedin_url" 
                                   value="<?php echo esc_attr(get_field('linkedin_url', $agent_id) ?: ''); ?>"
                                   placeholder="<?php _e('https://linkedin.com/in/yourprofile', 'happy-place'); ?>">
                        </div>
                    </div>
                    <div class="form-field form-field-half">
                        <label for="twitter_url"><?php _e('Twitter Profile', 'happy-place'); ?></label>
                        <div class="input-with-icon">
                            <span class="input-icon hph-icon-twitter"></span>
                            <input type="url" id="twitter_url" name="twitter_url" 
                                   value="<?php echo esc_attr(get_field('twitter_url', $agent_id) ?: ''); ?>"
                                   placeholder="<?php _e('https://twitter.com/yourprofile', 'happy-place'); ?>">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Floating Save Button -->
        <div class="floating-save-action">
            <button type="submit" class="btn btn-primary btn-lg save-profile-btn">
                <span class="hph-icon-save"></span>
                <?php _e('Save Profile', 'happy-place'); ?>
            </button>
        </div>
    </form>
</div>

<!-- Hidden file input for avatar upload -->
<input type="file" id="avatar-upload-input" accept="image/*" style="display: none;">

<script>
jQuery(document).ready(function($) {
    // Character counter for bio
    $('#short_bio').on('input', function() {
        const count = $(this).val().length;
        $('#short-bio-count').text(count);
        
        if (count > 200) {
            $(this).val($(this).val().substring(0, 200));
            $('#short-bio-count').text(200);
        }
    }).trigger('input');

    // Auto-calculate years of experience
    $('#date_started').on('change', function() {
        const startDate = new Date($(this).val());
        if (startDate) {
            const today = new Date();
            const diffTime = Math.abs(today - startDate);
            const diffYears = Math.floor(diffTime / (1000 * 60 * 60 * 24 * 365.25));
            $('#years_experience').val(diffYears);
        }
    });

    // Auto-generate display name
    function updateDisplayName() {
        const firstName = $('#first_name').val();
        const middleName = $('#middle_name').val();
        const lastName = $('#last_name').val();
        const suffix = $('#suffix').val();
        
        if (!$('#display_name').val()) {
            const nameParts = [firstName, middleName, lastName, suffix].filter(part => part);
            $('#display_name').attr('placeholder', nameParts.join(' '));
        }
    }

    $('#first_name, #middle_name, #last_name, #suffix').on('input change', updateDisplayName);
    updateDisplayName();

    // Avatar upload handler
    $('.avatar-upload-btn').on('click', function() {
        $('#avatar-upload-input').click();
    });

    $('#avatar-upload-input').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('.profile-avatar img').attr('src', e.target.result);
                $('.avatar-placeholder').hide();
            };
            reader.readAsDataURL(file);
        }
    });

    // Form submission with enhanced feedback
    $('#agent-profile-form').on('submit', function(e) {
        e.preventDefault();
        
        const $saveBtn = $('.save-profile-btn');
        const originalText = $saveBtn.html();
        
        // Add loading state
        $saveBtn.prop('disabled', true)
               .html('<span class="spinner-border spinner-border-sm me-2"></span><?php _e('Saving...', 'happy-place'); ?>');
        
        const formData = new FormData(this);
        formData.append('action', 'hph_save_agent_profile');
        
        $.ajax({
            url: hph_dashboard.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Success state
                    $saveBtn.removeClass('btn-primary')
                           .addClass('btn-success')
                           .html('<span class="hph-icon-check"></span><?php _e('Saved!', 'happy-place'); ?>');
                    
                    // Show toast notification
                    showToast('success', '<?php _e('Profile Updated', 'happy-place'); ?>', response.data.message);
                    
                    // Reset button after delay
                    setTimeout(() => {
                        $saveBtn.removeClass('btn-success')
                               .addClass('btn-primary')
                               .html(originalText)
                               .prop('disabled', false);
                    }, 2000);
                } else {
                    // Error state
                    showToast('error', '<?php _e('Error', 'happy-place'); ?>', response.data.message);
                    $saveBtn.html(originalText).prop('disabled', false);
                }
            },
            error: function() {
                showToast('error', '<?php _e('Error', 'happy-place'); ?>', '<?php _e('Failed to save profile. Please try again.', 'happy-place'); ?>');
                $saveBtn.html(originalText).prop('disabled', false);
            }
        });
    });

    // Toast notification function
    function showToast(type, title, message) {
        const toast = $(`
            <div class="toast-notification toast-${type}">
                <div class="toast-content">
                    <div class="toast-icon">
                        <span class="hph-icon-${type === 'success' ? 'check' : 'alert'}"></span>
                    </div>
                    <div class="toast-text">
                        <strong>${title}</strong>
                        <p>${message}</p>
                    </div>
                </div>
                <button class="toast-close">&times;</button>
            </div>
        `);
        
        $('body').append(toast);
        toast.addClass('show');
        
        // Auto dismiss
        setTimeout(() => {
            toast.removeClass('show');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
        
        // Manual dismiss
        toast.find('.toast-close').on('click', () => {
            toast.removeClass('show');
            setTimeout(() => toast.remove(), 300);
        });
    }
});
</script>