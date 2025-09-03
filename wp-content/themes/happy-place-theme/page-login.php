<?php
/**
 * Custom Login Page Template
 * 
 * Template Name: Custom Login
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Redirect if already logged in
if (is_user_logged_in()) {
    wp_redirect(home_url('/dashboard'));
    exit;
}

// Handle login form submission
$login_error = '';
$login_success = '';

if (isset($_POST['login_submit'])) {
    $username = sanitize_user($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;
    
    $credentials = array(
        'user_login'    => $username,
        'user_password' => $password,
        'remember'      => $remember,
    );
    
    $user = wp_signon($credentials, false);
    
    if (is_wp_error($user)) {
        $login_error = $user->get_error_message();
    } else {
        $redirect_to = isset($_GET['redirect_to']) ? $_GET['redirect_to'] : home_url('/dashboard');
        wp_redirect($redirect_to);
        exit;
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

<div class="hph-login-page">
    <!-- Login Hero Section -->
    <section class="hph-login-hero hph-bg-gradient-primary hph-py-5xl hph-relative hph-overflow-hidden">
        <!-- Background Pattern -->
        <div class="hph-login-pattern hph-absolute hph-inset-0 hph-opacity-10"></div>
        
        <div class="hph-container hph-relative hph-z-10">
            <div class="hph-max-w-4xl hph-mx-auto hph-text-center hph-text-white">
                <h1 class="hph-text-4xl hph-font-bold hph-mb-md">
                    <?php esc_html_e('Welcome Back', 'happy-place-theme'); ?>
                </h1>
                <p class="hph-text-lg hph-opacity-90">
                    <?php esc_html_e('Sign in to access your account and manage your listings', 'happy-place-theme'); ?>
                </p>
            </div>
        </div>
    </section>

    <!-- Login Form Section -->
    <section class="hph-login-form-section hph-py-5xl hph-bg-gray-50">
        <div class="hph-container">
            <div class="hph-max-w-md hph-mx-auto">
                
                <!-- Login Card -->
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
                    <?php if ($login_error) : ?>
                        <div class="hph-alert hph-alert-danger hph-mb-lg">
                            <i class="fas fa-exclamation-circle hph-mr-sm"></i>
                            <?php echo esc_html($login_error); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Success Messages -->
                    <?php if ($login_success) : ?>
                        <div class="hph-alert hph-alert-success hph-mb-lg">
                            <i class="fas fa-check-circle hph-mr-sm"></i>
                            <?php echo esc_html($login_success); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Login Form -->
                    <form method="post" action="" class="hph-login-form">
                        <?php wp_nonce_field('hph_login_nonce', 'login_nonce'); ?>
                        
                        <!-- Username Field -->
                        <div class="hph-form-group hph-mb-lg">
                            <label for="username" class="hph-form-label hph-form-label-required">
                                <?php esc_html_e('Username or Email', 'happy-place-theme'); ?>
                            </label>
                            <div class="hph-form-input-group">
                                <i class="hph-form-input-icon hph-form-input-icon-left fas fa-user"></i>
                                <input type="text" 
                                       id="username" 
                                       name="username" 
                                       class="hph-form-input hph-form-input-with-icon-left" 
                                       placeholder="<?php esc_attr_e('Enter your username or email', 'happy-place-theme'); ?>"
                                       value="<?php echo isset($_POST['username']) ? esc_attr($_POST['username']) : ''; ?>"
                                       required>
                            </div>
                        </div>

                        <!-- Password Field -->
                        <div class="hph-form-group hph-mb-lg">
                            <label for="password" class="hph-form-label hph-form-label-required">
                                <?php esc_html_e('Password', 'happy-place-theme'); ?>
                            </label>
                            <div class="hph-form-input-group hph-relative">
                                <i class="hph-form-input-icon hph-form-input-icon-left fas fa-lock"></i>
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       class="hph-form-input hph-form-input-with-icon-left" 
                                       placeholder="<?php esc_attr_e('Enter your password', 'happy-place-theme'); ?>"
                                       required>
                                <button type="button" 
                                        class="hph-password-toggle hph-absolute hph-right-3 hph-top-1/2 hph-transform hph--translate-y-1/2 hph-text-gray-400 hover:hph-text-gray-600"
                                        onclick="togglePassword()">
                                    <i class="fas fa-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Remember Me & Forgot Password -->
                        <div class="hph-flex hph-justify-between hph-items-center hph-mb-xl">
                            <label class="hph-form-checkbox-label">
                                <input type="checkbox" name="remember" value="1" class="hph-form-checkbox">
                                <span class="hph-form-checkmark"></span>
                                <span class="hph-text-sm hph-text-gray-600">
                                    <?php esc_html_e('Remember me', 'happy-place-theme'); ?>
                                </span>
                            </label>
                            
                            <a href="<?php echo wp_lostpassword_url(); ?>" 
                               class="hph-text-sm hph-text-primary hover:hph-text-primary-dark hph-no-underline">
                                <?php esc_html_e('Forgot password?', 'happy-place-theme'); ?>
                            </a>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" 
                                name="login_submit" 
                                class="hph-btn hph-btn-primary hph-btn-lg hph-w-full hph-mb-lg">
                            <i class="fas fa-sign-in-alt hph-mr-sm"></i>
                            <?php esc_html_e('Sign In', 'happy-place-theme'); ?>
                        </button>

                        <!-- Social Login (if available) -->
                        <?php if (function_exists('hph_social_login_buttons')) : ?>
                            <div class="hph-social-login hph-text-center hph-mb-lg">
                                <div class="hph-divider hph-mb-md">
                                    <span class="hph-divider-text hph-text-sm hph-text-gray-500">
                                        <?php esc_html_e('or continue with', 'happy-place-theme'); ?>
                                    </span>
                                </div>
                                <?php hph_social_login_buttons(); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Registration Link -->
                        <?php if (get_option('users_can_register')) : ?>
                            <div class="hph-text-center">
                                <p class="hph-text-sm hph-text-gray-600">
                                    <?php esc_html_e("Don't have an account?", 'happy-place-theme'); ?>
                                    <a href="<?php echo wp_registration_url(); ?>" 
                                       class="hph-text-primary hover:hph-text-primary-dark hph-font-medium hph-no-underline">
                                        <?php esc_html_e('Create one', 'happy-place-theme'); ?>
                                    </a>
                                </p>
                            </div>
                        <?php endif; ?>
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
function togglePassword() {
    const passwordField = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

// Form validation and enhancement
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.hph-login-form');
    const submitBtn = form.querySelector('[name="login_submit"]');
    
    form.addEventListener('submit', function() {
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin hph-mr-sm"></i><?php esc_html_e("Signing in...", "happy-place-theme"); ?>';
        submitBtn.disabled = true;
    });
    
    // Add focus states for better UX
    const inputs = form.querySelectorAll('.hph-form-input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentNode.classList.add('hph-form-input-focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentNode.classList.remove('hph-form-input-focused');
        });
    });
});
</script>

<?php get_footer(); ?>
