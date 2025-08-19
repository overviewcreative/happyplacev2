<?php
/**
 * Dashboard Agent Profile Management Section
 * 
 * Agent profile editing interface with real-time previews
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
?>

<div class="agent-profile-management">
    <form id="agent-profile-form" class="dashboard-form" data-agent-id="<?php echo esc_attr($agent_id); ?>">
        <?php wp_nonce_field('hph_save_agent_profile', 'profile_nonce'); ?>
        
        <!-- Basic Information Section -->
        <div class="listing-form-section">
            <div class="form-section-header">
                <h3 class="form-section-title">
                    <span class="hph-icon-user"></span>
                    <?php _e('Basic Information', 'happy-place'); ?>
                </h3>
            </div>
            <div class="form-section-content">
                <div class="row">
                    <div class="col-md-4">
                        <div class="dashboard-form-group">
                            <label for="first_name" class="dashboard-form-label"><?php _e('First Name *', 'happy-place'); ?></label>
                            <input type="text" id="first_name" name="first_name" class="dashboard-form-control" 
                                   value="<?php echo esc_attr(get_field('first_name', $agent_id) ?: ''); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="dashboard-form-group">
                            <label for="middle_name" class="dashboard-form-label"><?php _e('Middle Name', 'happy-place'); ?></label>
                            <input type="text" id="middle_name" name="middle_name" class="dashboard-form-control" 
                                   value="<?php echo esc_attr(get_field('middle_name', $agent_id) ?: ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-form-group">
                            <label for="last_name" class="dashboard-form-label"><?php _e('Last Name *', 'happy-place'); ?></label>
                            <input type="text" id="last_name" name="last_name" class="dashboard-form-control" 
                                   value="<?php echo esc_attr(get_field('last_name', $agent_id) ?: ''); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="dashboard-form-group">
                            <label for="suffix" class="dashboard-form-label"><?php _e('Suffix', 'happy-place'); ?></label>
                            <select id="suffix" name="suffix" class="dashboard-form-control">
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
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="dashboard-form-group">
                            <label for="display_name" class="dashboard-form-label"><?php _e('Display Name', 'happy-place'); ?></label>
                            <input type="text" id="display_name" name="display_name" class="dashboard-form-control" 
                                   value="<?php echo esc_attr(get_field('display_name', $agent_id) ?: ''); ?>"
                                   placeholder="<?php _e('Auto-generated from name fields', 'happy-place'); ?>">
                            <small class="text-muted"><?php _e('Leave blank to auto-generate from name fields', 'happy-place'); ?></small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="dashboard-form-group">
                            <label for="title" class="dashboard-form-label"><?php _e('Professional Title', 'happy-place'); ?></label>
                            <input type="text" id="title" name="title" class="dashboard-form-control" 
                                   value="<?php echo esc_attr(get_field('title', $agent_id) ?: ''); ?>"
                                   placeholder="<?php _e('e.g., Real Estate Agent, Broker', 'happy-place'); ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="dashboard-form-group">
                            <label for="short_bio" class="dashboard-form-label"><?php _e('Short Bio', 'happy-place'); ?></label>
                            <textarea id="short_bio" name="short_bio" class="dashboard-form-control" rows="3" 
                                      maxlength="200" placeholder="<?php _e('Brief professional summary (200 characters max)', 'happy-place'); ?>"><?php echo esc_textarea(get_field('short_bio', $agent_id) ?: ''); ?></textarea>
                            <small class="text-muted"><span id="short-bio-count">0</span>/200 <?php _e('characters', 'happy-place'); ?></small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="dashboard-form-group">
                            <label for="full_bio" class="dashboard-form-label"><?php _e('Full Bio', 'happy-place'); ?></label>
                            <textarea id="full_bio" name="full_bio" class="dashboard-form-control" rows="3" 
                                      placeholder="<?php _e('Detailed professional background and experience', 'happy-place'); ?>"><?php echo esc_textarea(get_field('full_bio', $agent_id) ?: ''); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information Section -->
        <div class="listing-form-section">
            <div class="form-section-header">
                <h3 class="form-section-title">
                    <span class="hph-icon-phone"></span>
                    <?php _e('Contact Information', 'happy-place'); ?>
                </h3>
            </div>
            <div class="form-section-content">
                <div class="row">
                    <div class="col-md-6">
                        <div class="dashboard-form-group">
                            <label for="email" class="dashboard-form-label"><?php _e('Email Address *', 'happy-place'); ?></label>
                            <input type="email" id="email" name="email" class="dashboard-form-control" 
                                   value="<?php echo esc_attr(get_field('email', $agent_id) ?: $current_user->user_email); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="dashboard-form-group">
                            <label for="phone" class="dashboard-form-label"><?php _e('Primary Phone', 'happy-place'); ?></label>
                            <input type="tel" id="phone" name="phone" class="dashboard-form-control" 
                                   value="<?php echo esc_attr(get_field('phone', $agent_id) ?: ''); ?>"
                                   placeholder="(555) 123-4567">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="dashboard-form-group">
                            <label for="mobile_phone" class="dashboard-form-label"><?php _e('Mobile Phone', 'happy-place'); ?></label>
                            <input type="tel" id="mobile_phone" name="mobile_phone" class="dashboard-form-control" 
                                   value="<?php echo esc_attr(get_field('mobile_phone', $agent_id) ?: ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-form-group">
                            <label for="office_phone" class="dashboard-form-label"><?php _e('Office Phone', 'happy-place'); ?></label>
                            <input type="tel" id="office_phone" name="office_phone" class="dashboard-form-control" 
                                   value="<?php echo esc_attr(get_field('office_phone', $agent_id) ?: ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-form-group">
                            <label for="website_url" class="dashboard-form-label"><?php _e('Website URL', 'happy-place'); ?></label>
                            <input type="url" id="website_url" name="website_url" class="dashboard-form-control" 
                                   value="<?php echo esc_attr(get_field('website_url', $agent_id) ?: ''); ?>"
                                   placeholder="https://yourwebsite.com">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Professional Details Section -->
        <div class="listing-form-section">
            <div class="form-section-header">
                <h3 class="form-section-title">
                    <span class="hph-icon-briefcase"></span>
                    <?php _e('Professional Details', 'happy-place'); ?>
                </h3>
            </div>
            <div class="form-section-content">
                <div class="row">
                    <div class="col-md-4">
                        <div class="dashboard-form-group">
                            <label for="license_number" class="dashboard-form-label"><?php _e('License Number', 'happy-place'); ?></label>
                            <input type="text" id="license_number" name="license_number" class="dashboard-form-control" 
                                   value="<?php echo esc_attr(get_field('license_number', $agent_id) ?: ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-form-group">
                            <label for="license_state" class="dashboard-form-label"><?php _e('License State', 'happy-place'); ?></label>
                            <select id="license_state" name="license_state" class="dashboard-form-control">
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
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-form-group">
                            <label for="license_expiration" class="dashboard-form-label"><?php _e('License Expiration', 'happy-place'); ?></label>
                            <input type="date" id="license_expiration" name="license_expiration" class="dashboard-form-control" 
                                   value="<?php echo esc_attr(get_field('license_expiration', $agent_id) ?: ''); ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="dashboard-form-group">
                            <label for="date_started" class="dashboard-form-label"><?php _e('Career Start Date', 'happy-place'); ?></label>
                            <input type="date" id="date_started" name="date_started" class="dashboard-form-control" 
                                   value="<?php echo esc_attr(get_field('date_started', $agent_id) ?: ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-form-group">
                            <label for="years_experience" class="dashboard-form-label"><?php _e('Years Experience', 'happy-place'); ?></label>
                            <input type="number" id="years_experience" name="years_experience" class="dashboard-form-control" 
                                   value="<?php echo esc_attr(get_field('years_experience', $agent_id) ?: ''); ?>" readonly>
                            <small class="text-muted"><?php _e('Auto-calculated from start date', 'happy-place'); ?></small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-form-group">
                            <label for="office_name" class="dashboard-form-label"><?php _e('Office/Brokerage', 'happy-place'); ?></label>
                            <input type="text" id="office_name" name="office_name" class="dashboard-form-control" 
                                   value="<?php echo esc_attr(get_field('office_name', $agent_id) ?: ''); ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="dashboard-form-group">
                            <label class="dashboard-form-label"><?php _e('Specialties', 'happy-place'); ?></label>
                            <div class="specialty-checkboxes">
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
                                    <label class="specialty-checkbox">
                                        <input type="checkbox" name="specialties[]" value="<?php echo esc_attr($key); ?>" 
                                               <?php checked(in_array($key, $current_specialties)); ?>>
                                        <?php echo esc_html($label); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="dashboard-form-group">
                            <label class="dashboard-form-label"><?php _e('Languages', 'happy-place'); ?></label>
                            <div class="language-checkboxes">
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
                                    <label class="language-checkbox">
                                        <input type="checkbox" name="languages[]" value="<?php echo esc_attr($key); ?>" 
                                               <?php checked(in_array($key, $current_languages)); ?>>
                                        <?php echo esc_html($label); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Social Media Section -->
        <div class="listing-form-section">
            <div class="form-section-header">
                <h3 class="form-section-title">
                    <span class="hph-icon-share"></span>
                    <?php _e('Social Media & Online Presence', 'happy-place'); ?>
                </h3>
            </div>
            <div class="form-section-content">
                <div class="row">
                    <div class="col-md-6">
                        <div class="dashboard-form-group">
                            <label for="facebook_url" class="dashboard-form-label"><?php _e('Facebook Profile', 'happy-place'); ?></label>
                            <input type="url" id="facebook_url" name="facebook_url" class="dashboard-form-control" 
                                   value="<?php echo esc_attr(get_field('facebook_url', $agent_id) ?: ''); ?>"
                                   placeholder="https://facebook.com/yourprofile">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="dashboard-form-group">
                            <label for="instagram_url" class="dashboard-form-label"><?php _e('Instagram Profile', 'happy-place'); ?></label>
                            <input type="url" id="instagram_url" name="instagram_url" class="dashboard-form-control" 
                                   value="<?php echo esc_attr(get_field('instagram_url', $agent_id) ?: ''); ?>"
                                   placeholder="https://instagram.com/yourprofile">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="dashboard-form-group">
                            <label for="linkedin_url" class="dashboard-form-label"><?php _e('LinkedIn Profile', 'happy-place'); ?></label>
                            <input type="url" id="linkedin_url" name="linkedin_url" class="dashboard-form-control" 
                                   value="<?php echo esc_attr(get_field('linkedin_url', $agent_id) ?: ''); ?>"
                                   placeholder="https://linkedin.com/in/yourprofile">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="dashboard-form-group">
                            <label for="twitter_url" class="dashboard-form-label"><?php _e('Twitter Profile', 'happy-place'); ?></label>
                            <input type="url" id="twitter_url" name="twitter_url" class="dashboard-form-control" 
                                   value="<?php echo esc_attr(get_field('twitter_url', $agent_id) ?: ''); ?>"
                                   placeholder="https://twitter.com/yourprofile">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="dashboard-form-group">
                            <label for="youtube_url" class="dashboard-form-label"><?php _e('YouTube Channel', 'happy-place'); ?></label>
                            <input type="url" id="youtube_url" name="youtube_url" class="dashboard-form-control" 
                                   value="<?php echo esc_attr(get_field('youtube_url', $agent_id) ?: ''); ?>"
                                   placeholder="https://youtube.com/c/yourchannel">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="dashboard-form-group">
                            <label for="zillow_profile_url" class="dashboard-form-label"><?php _e('Zillow Profile', 'happy-place'); ?></label>
                            <input type="url" id="zillow_profile_url" name="zillow_profile_url" class="dashboard-form-control" 
                                   value="<?php echo esc_attr(get_field('zillow_profile_url', $agent_id) ?: ''); ?>"
                                   placeholder="https://zillow.com/profile/yourprofile">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <div class="row">
                <div class="col-md-6">
                    <button type="button" class="btn btn-outline-secondary" id="preview-profile">
                        <span class="hph-icon-eye"></span>
                        <?php _e('Preview Profile', 'happy-place'); ?>
                    </button>
                </div>
                <div class="col-md-6 text-end">
                    <button type="submit" class="btn btn-primary">
                        <span class="hph-icon-save"></span>
                        <?php _e('Save Profile', 'happy-place'); ?>
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Profile Preview Modal will be inserted here -->
</div>

<script>
jQuery(document).ready(function($) {
    // Character counter for short bio
    $('#short_bio').on('input', function() {
        const count = $(this).val().length;
        $('#short-bio-count').text(count);
        
        if (count > 200) {
            $(this).val($(this).val().substring(0, 200));
            $('#short-bio-count').text(200);
        }
    });
    
    // Trigger initial count
    $('#short_bio').trigger('input');

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

    // Social media URL validation
    $('input[name$="_url"]').on('blur', function() {
        const $input = $(this);
        const url = $input.val();
        const name = $input.attr('name');
        
        if (url && !isValidSocialUrl(name, url)) {
            $input.addClass('is-invalid');
            showFieldError($input, 'Please enter a valid ' + name.replace('_url', '') + ' URL');
        } else {
            $input.removeClass('is-invalid');
            removeFieldError($input);
        }
    });

    function isValidSocialUrl(fieldName, url) {
        const domains = {
            'facebook_url': 'facebook.com',
            'instagram_url': 'instagram.com',
            'linkedin_url': 'linkedin.com',
            'twitter_url': 'twitter.com',
            'youtube_url': 'youtube.com',
            'zillow_profile_url': 'zillow.com'
        };
        
        const expectedDomain = domains[fieldName];
        return !expectedDomain || url.includes(expectedDomain);
    }

    function showFieldError($field, message) {
        removeFieldError($field);
        $field.after(`<div class="field-error text-danger small">${message}</div>`);
    }

    function removeFieldError($field) {
        $field.siblings('.field-error').remove();
    }

    // Profile preview
    $('#preview-profile').on('click', function() {
        const formData = $('#agent-profile-form').serialize();
        
        // Open preview modal
        const modal = $(`
            <div class="hph-modal-overlay">
                <div class="hph-modal hph-modal-lg">
                    <div class="hph-modal-header">
                        <h3 class="hph-modal-title"><?php _e('Profile Preview', 'happy-place'); ?></h3>
                        <button type="button" class="hph-modal-close">&times;</button>
                    </div>
                    <div class="hph-modal-body">
                        <div class="profile-preview-loading">
                            <div class="spinner"></div>
                            <p><?php _e('Generating preview...', 'happy-place'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        `);
        
        $('body').append(modal);
        modal.fadeIn();
        
        // Load preview content
        $.ajax({
            url: hph_dashboard.ajax_url,
            type: 'POST',
            data: formData + '&action=hph_preview_agent_profile&nonce=' + hph_dashboard.nonce,
            success: function(response) {
                if (response.success) {
                    modal.find('.hph-modal-body').html(response.data.preview);
                } else {
                    modal.find('.hph-modal-body').html('<p class="text-danger"><?php _e('Failed to generate preview', 'happy-place'); ?></p>');
                }
            },
            error: function() {
                modal.find('.hph-modal-body').html('<p class="text-danger"><?php _e('Error generating preview', 'happy-place'); ?></p>');
            }
        });
        
        // Close modal events
        modal.find('.hph-modal-close').on('click', function() {
            modal.fadeOut(function() { modal.remove(); });
        });
        
        modal.on('click', function(e) {
            if (e.target === this) {
                modal.fadeOut(function() { modal.remove(); });
            }
        });
    });

    // Form submission
    $('#agent-profile-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'hph_save_agent_profile');
        
        const $submitBtn = $(this).find('[type="submit"]');
        const originalText = $submitBtn.text();
        
        $.ajax({
            url: hph_dashboard.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $submitBtn.prop('disabled', true).text('<?php _e('Saving...', 'happy-place'); ?>');
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    const alert = $(`
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong><?php _e('Success!', 'happy-place'); ?></strong> ${response.data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `);
                    $('.agent-profile-management').prepend(alert);
                    
                    // Auto-dismiss
                    setTimeout(() => alert.fadeOut(), 5000);
                } else {
                    // Show error message
                    const alert = $(`
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong><?php _e('Error!', 'happy-place'); ?></strong> ${response.data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `);
                    $('.agent-profile-management').prepend(alert);
                }
            },
            error: function() {
                const alert = $(`
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong><?php _e('Error!', 'happy-place'); ?></strong> <?php _e('Failed to save profile. Please try again.', 'happy-place'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);
                $('.agent-profile-management').prepend(alert);
            },
            complete: function() {
                $submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });
});
</script>

<style>
.specialty-checkboxes,
.language-checkboxes {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--hph-space-xs);
    max-height: 200px;
    overflow-y: auto;
    padding: var(--hph-space-sm);
    border: 1px solid var(--hph-border-color);
    border-radius: var(--hph-border-radius);
    background: var(--hph-gray-50);
}

.specialty-checkbox,
.language-checkbox {
    display: flex;
    align-items: center;
    padding: var(--hph-space-xs);
    font-size: var(--hph-text-sm);
    cursor: pointer;
    border-radius: var(--hph-border-radius);
    transition: var(--hph-transition);
}

.specialty-checkbox:hover,
.language-checkbox:hover {
    background: var(--hph-primary-light);
}

.specialty-checkbox input,
.language-checkbox input {
    margin-right: var(--hph-space-xs);
    margin-top: 0;
}

.form-actions {
    background: var(--hph-white);
    border-radius: var(--hph-card-radius);
    padding: var(--hph-space-lg);
    margin-top: var(--hph-space-lg);
    box-shadow: var(--hph-shadow-sm);
    border-top: 3px solid var(--hph-primary);
}

.field-error {
    margin-top: var(--hph-space-xs);
}

.is-invalid {
    border-color: var(--hph-danger) !important;
    box-shadow: 0 0 0 2px var(--hph-danger-light) !important;
}

.profile-preview-loading {
    text-align: center;
    padding: var(--hph-space-xl);
}

.hph-modal-lg .hph-modal {
    max-width: 800px;
}

@media (max-width: 767px) {
    .specialty-checkboxes,
    .language-checkboxes {
        grid-template-columns: 1fr;
        max-height: 150px;
    }
    
    .form-actions .row > div {
        margin-bottom: var(--hph-space-sm);
    }
    
    .form-actions .text-end {
        text-align: left !important;
    }
}
</style>