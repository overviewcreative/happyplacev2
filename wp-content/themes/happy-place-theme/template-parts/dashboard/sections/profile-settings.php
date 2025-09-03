<?php
/**
 * Dashboard Profile Settings Section
 * 
 * @package HappyPlaceTheme
 */

$user = wp_get_current_user();
$is_agent = in_array('agent', $user->roles) || in_array('administrator', $user->roles);
?>

<div class="hph-dashboard-section hph-profile-section">
    
    <!-- Profile Header -->
    <div class="hph-section-header">
        <h2 class="hph-section-title">
            <i class="fas fa-user-cog"></i>
            Profile Settings
        </h2>
        <p class="hph-section-description">
            Manage your account information, preferences, and security settings.
        </p>
    </div>

    <div class="hph-profile-grid">
        
        <!-- Personal Information -->
        <div class="hph-profile-card">
            <div class="hph-card-header">
                <h3 class="hph-card-title">Personal Information</h3>
            </div>
            <div class="hph-card-content">
                <form class="hph-profile-form" method="post" action="">
                    <?php wp_nonce_field('update_profile', 'profile_nonce'); ?>
                    
                    <div class="hph-form-group">
                        <label for="display_name" class="hph-form-label">Display Name</label>
                        <input type="text" id="display_name" name="display_name" 
                               value="<?php echo esc_attr($user->display_name); ?>" 
                               class="hph-form-input" required>
                    </div>
                    
                    <div class="hph-form-group">
                        <label for="user_email" class="hph-form-label">Email Address</label>
                        <input type="email" id="user_email" name="user_email" 
                               value="<?php echo esc_attr($user->user_email); ?>" 
                               class="hph-form-input" required>
                    </div>
                    
                    <div class="hph-form-group">
                        <label for="phone_number" class="hph-form-label">Phone Number</label>
                        <input type="tel" id="phone_number" name="phone_number" 
                               value="<?php echo esc_attr(get_user_meta($user->ID, 'phone_number', true)); ?>" 
                               class="hph-form-input">
                    </div>
                    
                    <?php if ($is_agent): ?>
                    <div class="hph-form-group">
                        <label for="license_number" class="hph-form-label">License Number</label>
                        <input type="text" id="license_number" name="license_number" 
                               value="<?php echo esc_attr(get_user_meta($user->ID, 'license_number', true)); ?>" 
                               class="hph-form-input">
                    </div>
                    
                    <div class="hph-form-group">
                        <label for="brokerage" class="hph-form-label">Brokerage</label>
                        <input type="text" id="brokerage" name="brokerage" 
                               value="<?php echo esc_attr(get_user_meta($user->ID, 'brokerage', true)); ?>" 
                               class="hph-form-input">
                    </div>
                    <?php endif; ?>
                    
                    <div class="hph-form-actions">
                        <button type="submit" name="update_profile" class="hph-btn hph-btn-primary">
                            <i class="fas fa-save"></i>
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Profile Photo -->
        <div class="hph-profile-card">
            <div class="hph-card-header">
                <h3 class="hph-card-title">Profile Photo</h3>
            </div>
            <div class="hph-card-content">
                <div class="hph-profile-photo-section">
                    <div class="hph-current-photo">
                        <?php echo get_avatar($user->ID, 120, '', '', ['class' => 'hph-profile-avatar']); ?>
                    </div>
                    <div class="hph-photo-actions">
                        <button type="button" class="hph-btn hph-btn-outline hph-btn-sm">
                            <i class="fas fa-camera"></i>
                            Change Photo
                        </button>
                        <p class="hph-photo-note">
                            Upload a professional headshot for the best results. 
                            Recommended size: 300x300 pixels.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification Preferences -->
        <div class="hph-profile-card">
            <div class="hph-card-header">
                <h3 class="hph-card-title">Notification Preferences</h3>
            </div>
            <div class="hph-card-content">
                <form class="hph-notifications-form" method="post" action="">
                    <?php wp_nonce_field('update_notifications', 'notifications_nonce'); ?>
                    
                    <div class="hph-notification-group">
                        <h4 class="hph-notification-group-title">Email Notifications</h4>
                        
                        <div class="hph-form-check">
                            <input type="checkbox" id="email_new_listings" name="email_new_listings" 
                                   class="hph-form-checkbox" checked>
                            <label for="email_new_listings" class="hph-form-check-label">
                                New listing matches my saved searches
                            </label>
                        </div>
                        
                        <div class="hph-form-check">
                            <input type="checkbox" id="email_price_changes" name="email_price_changes" 
                                   class="hph-form-checkbox" checked>
                            <label for="email_price_changes" class="hph-form-check-label">
                                Price changes on favorited properties
                            </label>
                        </div>
                        
                        <?php if ($is_agent): ?>
                        <div class="hph-form-check">
                            <input type="checkbox" id="email_new_leads" name="email_new_leads" 
                                   class="hph-form-checkbox" checked>
                            <label for="email_new_leads" class="hph-form-check-label">
                                New leads and inquiries
                            </label>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="hph-form-actions">
                        <button type="submit" name="update_notifications" class="hph-btn hph-btn-primary">
                            <i class="fas fa-bell"></i>
                            Save Preferences
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Security Settings -->
        <div class="hph-profile-card">
            <div class="hph-card-header">
                <h3 class="hph-card-title">Security Settings</h3>
            </div>
            <div class="hph-card-content">
                <div class="hph-security-section">
                    <div class="hph-security-item">
                        <div class="hph-security-info">
                            <h4>Change Password</h4>
                            <p>Update your account password for security</p>
                        </div>
                        <button type="button" class="hph-btn hph-btn-outline hph-btn-sm" id="changePasswordBtn">
                            <i class="fas fa-key"></i>
                            Change Password
                        </button>
                    </div>
                    
                    <div class="hph-security-item">
                        <div class="hph-security-info">
                            <h4>Two-Factor Authentication</h4>
                            <p>Add an extra layer of security to your account</p>
                        </div>
                        <button type="button" class="hph-btn hph-btn-outline hph-btn-sm">
                            <i class="fas fa-shield-alt"></i>
                            Enable 2FA
                        </button>
                    </div>
                    
                    <div class="hph-security-item">
                        <div class="hph-security-info">
                            <h4>Active Sessions</h4>
                            <p>Manage your logged in devices</p>
                        </div>
                        <button type="button" class="hph-btn hph-btn-outline hph-btn-sm">
                            <i class="fas fa-devices"></i>
                            View Sessions
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Coming Soon Features -->
    <div class="hph-coming-soon">
        <div class="hph-coming-soon-content">
            <i class="fas fa-cogs hph-coming-soon-icon"></i>
            <h3>Advanced Settings Coming Soon</h3>
            <p>We're adding privacy controls, data export options, and integration settings.</p>
        </div>
    </div>

</div>
