<?php
/**
 * Custom Registration Page Template
 * 
 * Template Name: Custom Registration
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Redirect if already logged in
if (is_user_logged_in()) {
    wp_redirect(home_url('/dashboard'));
    exit;
}

// Check if registration is enabled
if (!get_option('users_can_register')) {
    wp_redirect(home_url());
    exit;
}

// Handle registration form submission
$registration_error = '';
$registration_success = '';

if (isset($_POST['register_submit'])) {
    $username = sanitize_user($_POST['username']);
    $email = sanitize_email($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = sanitize_text_field($_POST['first_name']);
    $last_name = sanitize_text_field($_POST['last_name']);
    $user_type = sanitize_text_field($_POST['user_type'] ?? 'buyer');
    
    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $registration_error = __('Please fill in all required fields.', 'happy-place-theme');
    } elseif (username_exists($username)) {
        $registration_error = __('Username already exists.', 'happy-place-theme');
    } elseif (email_exists($email)) {
        $registration_error = __('Email address already exists.', 'happy-place-theme');
    } elseif ($password !== $confirm_password) {
        $registration_error = __('Passwords do not match.', 'happy-place-theme');
    } elseif (strlen($password) < 8) {
        $registration_error = __('Password must be at least 8 characters long.', 'happy-place-theme');
    } else {
        // Create user
        $user_id = wp_create_user($username, $password, $email);
        
        if (is_wp_error($user_id)) {
            $registration_error = $user_id->get_error_message();
        } else {
            // Update user meta
            wp_update_user(array(
                'ID' => $user_id,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'display_name' => $first_name . ' ' . $last_name
            ));
            
            // Set user type
            update_user_meta($user_id, 'user_type', $user_type);
            
            $registration_success = __('Registration successful! Please check your email to activate your account.', 'happy-place-theme');
            
            // Optional: Auto login user
            wp_set_current_user($user_id);
            wp_set_auth_cookie($user_id);
            
            // Redirect to dashboard
            wp_redirect(home_url('/dashboard'));
            exit;
        }
    }
}

// Get theme branding
$site_logo = get_theme_mod('custom_logo');
$site_logo_url = wp_get_attachment_image_url($site_logo, 'full');
$brand_logo = hph_get_brand_logo();
if ($brand_logo) {
    $site_logo_url = $brand_logo;
}

get_header(); ?>

<div class="hph-registration-page">
    <!-- Registration Hero Section -->
    <section class="hph-registration-hero hph-bg-gradient-primary hph-py-5xl hph-relative hph-overflow-hidden">
        <!-- Background Pattern -->
        <div class="hph-login-pattern hph-absolute hph-inset-0 hph-opacity-10"></div>
        
        <div class="hph-container hph-relative hph-z-10">
            <div class="hph-max-w-4xl hph-mx-auto hph-text-center hph-text-white">
                <h1 class="hph-text-4xl hph-font-bold hph-mb-md">
                    <?php esc_html_e('Join Our Community', 'happy-place-theme'); ?>
                </h1>
                <p class="hph-text-lg hph-opacity-90">
                    <?php esc_html_e('Create your account to save listings, connect with agents, and more', 'happy-place-theme'); ?>
                </p>
            </div>
        </div>
    </section>

    <!-- Registration Form Section -->
    <section class="hph-registration-form-section hph-py-5xl hph-bg-gray-50">
        <div class="hph-container">
            <div class="hph-max-w-md hph-mx-auto">
                
                <!-- Registration Card -->
                <div class="hph-login-card hph-bg-white hph-rounded-lg hph-shadow-lg hph-p-2xl hph-border">
                    
                    <!-- Logo -->
                    <?php if ($site_logo_url) : ?>
                        <div class="hph-text-center hph-mb-xl">
                            <img src="<?php echo esc_url($site_logo_url); ?>" 
                                 alt="<?php bloginfo('name'); ?>"
                                 class="hph-login-logo hph-mx-auto hph-h-12 hph-w-auto">
                        </div>
                    <?php else : ?>
                        <div class="hph-text-center hph-mb-xl">
                            <h2 class="hph-text-2xl hph-font-bold hph-text-gray-900">
                                <?php bloginfo('name'); ?>
                            </h2>
                        </div>
                    <?php endif; ?>

                    <!-- Error Messages -->
                    <?php if ($registration_error) : ?>
                        <div class="hph-alert hph-alert-danger hph-mb-lg">
                            <i class="fas fa-exclamation-circle hph-mr-sm"></i>
                            <?php echo esc_html($registration_error); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Success Messages -->
                    <?php if ($registration_success) : ?>
                        <div class="hph-alert hph-alert-success hph-mb-lg">
                            <i class="fas fa-check-circle hph-mr-sm"></i>
                            <?php echo esc_html($registration_success); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Registration Form -->
                    <form method="post" action="" class="hph-registration-form">
                        <?php wp_nonce_field('hph_registration_nonce', 'registration_nonce'); ?>
                        
                        <!-- Name Fields -->
                        <div class="hph-form-grid hph-form-grid-2 hph-mb-lg">
                            <div class="hph-form-group">
                                <label for="first_name" class="hph-form-label hph-form-label-required">
                                    <?php esc_html_e('First Name', 'happy-place-theme'); ?>
                                </label>
                                <input type="text" 
                                       id="first_name" 
                                       name="first_name" 
                                       class="hph-form-input" 
                                       value="<?php echo isset($_POST['first_name']) ? esc_attr($_POST['first_name']) : ''; ?>"
                                       required>
                            </div>
                            
                            <div class="hph-form-group">
                                <label for="last_name" class="hph-form-label hph-form-label-required">
                                    <?php esc_html_e('Last Name', 'happy-place-theme'); ?>
                                </label>
                                <input type="text" 
                                       id="last_name" 
                                       name="last_name" 
                                       class="hph-form-input" 
                                       value="<?php echo isset($_POST['last_name']) ? esc_attr($_POST['last_name']) : ''; ?>"
                                       required>
                            </div>
                        </div>

                        <!-- Username Field -->
                        <div class="hph-form-group hph-mb-lg">
                            <label for="username" class="hph-form-label hph-form-label-required">
                                <?php esc_html_e('Username', 'happy-place-theme'); ?>
                            </label>
                            <div class="hph-form-input-group">
                                <i class="hph-form-input-icon hph-form-input-icon-left fas fa-user"></i>
                                <input type="text" 
                                       id="username" 
                                       name="username" 
                                       class="hph-form-input hph-form-input-with-icon-left" 
                                       value="<?php echo isset($_POST['username']) ? esc_attr($_POST['username']) : ''; ?>"
                                       required>
                            </div>
                        </div>

                        <!-- Email Field -->
                        <div class="hph-form-group hph-mb-lg">
                            <label for="email" class="hph-form-label hph-form-label-required">
                                <?php esc_html_e('Email Address', 'happy-place-theme'); ?>
                            </label>
                            <div class="hph-form-input-group">
                                <i class="hph-form-input-icon hph-form-input-icon-left fas fa-envelope"></i>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       class="hph-form-input hph-form-input-with-icon-left" 
                                       value="<?php echo isset($_POST['email']) ? esc_attr($_POST['email']) : ''; ?>"
                                       required>
                            </div>
                        </div>

                        <!-- Password Fields -->
                        <div class="hph-form-grid hph-form-grid-2 hph-mb-lg">
                            <div class="hph-form-group">
                                <label for="password" class="hph-form-label hph-form-label-required">
                                    <?php esc_html_e('Password', 'happy-place-theme'); ?>
                                </label>
                                <div class="hph-form-input-group hph-relative">
                                    <i class="hph-form-input-icon hph-form-input-icon-left fas fa-lock"></i>
                                    <input type="password" 
                                           id="password" 
                                           name="password" 
                                           class="hph-form-input hph-form-input-with-icon-left" 
                                           required>
                                </div>
                            </div>
                            
                            <div class="hph-form-group">
                                <label for="confirm_password" class="hph-form-label hph-form-label-required">
                                    <?php esc_html_e('Confirm Password', 'happy-place-theme'); ?>
                                </label>
                                <div class="hph-form-input-group hph-relative">
                                    <i class="hph-form-input-icon hph-form-input-icon-left fas fa-lock"></i>
                                    <input type="password" 
                                           id="confirm_password" 
                                           name="confirm_password" 
                                           class="hph-form-input hph-form-input-with-icon-left" 
                                           required>
                                </div>
                            </div>
                        </div>

                        <!-- User Type -->
                        <div class="hph-form-group hph-mb-lg">
                            <label for="user_type" class="hph-form-label">
                                <?php esc_html_e('I am a...', 'happy-place-theme'); ?>
                            </label>
                            <select id="user_type" name="user_type" class="hph-form-select">
                                <option value="buyer"><?php esc_html_e('Home Buyer', 'happy-place-theme'); ?></option>
                                <option value="seller"><?php esc_html_e('Home Seller', 'happy-place-theme'); ?></option>
                                <option value="agent"><?php esc_html_e('Real Estate Agent', 'happy-place-theme'); ?></option>
                                <option value="investor"><?php esc_html_e('Investor', 'happy-place-theme'); ?></option>
                            </select>
                        </div>

                        <!-- Terms Agreement -->
                        <div class="hph-form-group hph-mb-xl">
                            <label class="hph-form-checkbox-label">
                                <input type="checkbox" name="terms" value="1" class="hph-form-checkbox" required>
                                <span class="hph-form-checkmark"></span>
                                <span class="hph-text-sm hph-text-gray-600">
                                    <?php 
                                    printf(
                                        __('I agree to the %s and %s', 'happy-place-theme'),
                                        '<a href="/terms" class="hph-text-primary">Terms of Service</a>',
                                        '<a href="/privacy" class="hph-text-primary">Privacy Policy</a>'
                                    );
                                    ?>
                                </span>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" 
                                name="register_submit" 
                                class="hph-btn hph-btn-primary hph-btn-lg hph-w-full hph-mb-lg">
                            <i class="fas fa-user-plus hph-mr-sm"></i>
                            <?php esc_html_e('Create Account', 'happy-place-theme'); ?>
                        </button>

                        <!-- Login Link -->
                        <div class="hph-text-center">
                            <p class="hph-text-sm hph-text-gray-600">
                                <?php esc_html_e('Already have an account?', 'happy-place-theme'); ?>
                                <a href="<?php echo wp_login_url(); ?>" 
                                   class="hph-text-primary hover:hph-text-primary-dark hph-font-medium hph-no-underline">
                                    <?php esc_html_e('Sign in', 'happy-place-theme'); ?>
                                </a>
                            </p>
                        </div>
                    </form>
                </div>

                <!-- Additional Links -->
                <div class="hph-text-center hph-mt-xl">
                    <p class="hph-text-sm hph-text-gray-500">
                        <a href="<?php echo home_url(); ?>" 
                           class="hph-text-gray-600 hover:hph-text-gray-800 hph-no-underline">
                            <i class="fas fa-arrow-left hph-mr-xs"></i>
                            <?php esc_html_e('Back to website', 'happy-place-theme'); ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
// Form validation and enhancement
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.hph-registration-form');
    const submitBtn = form.querySelector('[name="register_submit"]');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    // Password confirmation validation
    function validatePasswords() {
        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('<?php esc_html_e("Passwords do not match", "happy-place-theme"); ?>');
        } else {
            confirmPassword.setCustomValidity('');
        }
    }
    
    password.addEventListener('input', validatePasswords);
    confirmPassword.addEventListener('input', validatePasswords);
    
    form.addEventListener('submit', function() {
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin hph-mr-sm"></i><?php esc_html_e("Creating account...", "happy-place-theme"); ?>';
        submitBtn.disabled = true;
    });
});
</script>

<?php get_footer(); ?>
