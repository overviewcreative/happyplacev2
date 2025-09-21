<?php
/**
 * Template Name: Login Page
 * Custom login page that overrides WordPress default
 */

// Redirect if already logged in
if (is_user_logged_in()) {
    wp_redirect(home_url('/dashboard/'));
    exit;
}

// Handle login form submission
$login_errors = [];
$login_message = '';

if ($_POST && isset($_POST['login_form'])) {
    $username = sanitize_user($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    if (empty($username)) {
        $login_errors[] = 'Username is required.';
    }
    
    if (empty($password)) {
        $login_errors[] = 'Password is required.';
    }
    
    if (empty($login_errors)) {
        $creds = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => $remember,
        );
        
        $user = wp_signon($creds, false);
        
        if (is_wp_error($user)) {
            $login_errors[] = 'Invalid username or password.';
        } else {
            // Successful login - redirect
            $redirect_to = isset($_GET['redirect_to']) ? esc_url_raw($_GET['redirect_to']) : home_url('/dashboard/');
            wp_redirect($redirect_to);
            exit;
        }
    }
}

get_header();
?>

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
                    <?php esc_html_e('Sign in to your account to continue', 'happy-place-theme'); ?>
                </p>
            </div>
        </div>
    </section>

    <!-- Login Form Section -->
    <section class="hph-login-form-section hph-py-5xl">
        <div class="hph-container">
            <div class="hph-max-w-md hph-mx-auto">
                <!-- Login Card -->
                <div class="hph-login-card hph-bg-white hph-rounded-lg hph-shadow-lg hph-p-2xl hph-border">
                    <!-- Logo -->
                    <div class="hph-text-center hph-mb-2xl">
                        <?php if (has_custom_logo()): ?>
                            <img src="<?php echo esc_url(wp_get_attachment_image_url(get_theme_mod('custom_logo'), 'full')); ?>" 
                                 alt="<?php bloginfo('name'); ?>"
                                 class="hph-login-logo hph-mx-auto hph-h-12 hph-w-auto">
                        <?php else: ?>
                            <h2 class="hph-text-2xl hph-font-bold hph-text-gray-900">
                                <?php bloginfo('name'); ?>
                            </h2>
                        <?php endif; ?>
                    </div>

                    <!-- Error Messages -->
                    <?php if (!empty($login_errors)): ?>
                        <div class="hph-alert hph-alert-danger hph-mb-lg">
                            <?php foreach ($login_errors as $error): ?>
                                <p class="hph-mb-0"><?php echo esc_html($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Success Messages -->
                    <?php if (isset($_GET['registered'])): ?>
                        <div class="hph-alert hph-alert-success hph-mb-lg">
                            <p class="hph-mb-0"><?php esc_html_e('Registration successful! Please log in with your credentials.', 'happy-place-theme'); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['password-reset'])): ?>
                        <div class="hph-alert hph-alert-success hph-mb-lg">
                            <p class="hph-mb-0"><?php esc_html_e('Password reset email sent! Check your inbox.', 'happy-place-theme'); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['logged_out'])): ?>
                        <div class="hph-alert hph-alert-success hph-mb-lg">
                            <p class="hph-mb-0"><?php esc_html_e('You have been successfully logged out.', 'happy-place-theme'); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Login Form -->
                    <form method="post" class="hph-login-form">
                        <?php wp_nonce_field('login_form', 'login_nonce'); ?>
                        <input type="hidden" name="login_form" value="1">

                        <!-- Username/Email Field -->
                        <div class="hph-form-group hph-mb-lg">
                            <label for="username" class="hph-form-label hph-block hph-text-sm hph-font-medium hph-text-gray-700 hph-mb-xs">
                                <?php esc_html_e('Username or Email', 'happy-place-theme'); ?>
                            </label>
                            <div class="hph-relative">
                                <div class="hph-absolute hph-inset-y-0 hph-left-0 hph-pl-3 hph-flex hph-items-center hph-pointer-events-none">
                                    <svg class="hph-h-5 hph-w-5 hph-text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <input type="text" 
                                       id="username" 
                                       name="username" 
                                       value="<?php echo esc_attr($_POST['username'] ?? ''); ?>" 
                                       required 
                                       autocomplete="username"
                                       class="hph-form-input hph-form-input-with-icon-left hph-block hph-w-full hph-pl-10">
                            </div>
                        </div>

                        <!-- Password Field -->
                        <div class="hph-form-group hph-mb-lg">
                            <label for="password" class="hph-form-label hph-block hph-text-sm hph-font-medium hph-text-gray-700 hph-mb-xs">
                                <?php esc_html_e('Password', 'happy-place-theme'); ?>
                            </label>
                            <div class="hph-relative">
                                <div class="hph-absolute hph-inset-y-0 hph-left-0 hph-pl-3 hph-flex hph-items-center hph-pointer-events-none">
                                    <svg class="hph-h-5 hph-w-5 hph-text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       required 
                                       autocomplete="current-password"
                                       class="hph-form-input hph-form-input-with-icon-left hph-block hph-w-full hph-pl-10">
                                <button type="button" 
                                        class="hph-password-toggle hph-absolute hph-inset-y-0 hph-right-0 hph-pr-3 hph-flex hph-items-center">
                                    <svg class="hph-h-5 hph-w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Remember Me -->
                        <div class="hph-form-group hph-mb-lg">
                            <div class="hph-flex hph-items-center">
                                <input type="checkbox" 
                                       id="remember" 
                                       name="remember" 
                                       value="1"
                                       class="hph-form-checkbox hph-h-4 hph-w-4 hph-text-primary hph-rounded">
                                <label for="remember" class="hph-ml-2 hph-block hph-text-sm hph-text-gray-700">
                                    <?php esc_html_e('Remember me', 'happy-place-theme'); ?>
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="hph-btn hph-btn-primary hph-btn-block hph-w-full hph-mb-lg">
                            <?php esc_html_e('Sign In', 'happy-place-theme'); ?>
                        </button>
                    </form>

                    <!-- Additional Links -->
                    <div class="hph-text-center hph-space-y-4">
                        <!-- Forgot Password -->
                        <div>
                            <a href="<?php echo esc_url(wp_lostpassword_url(get_permalink())); ?>" 
                               class="hph-text-sm hph-text-primary hph-hover-text-primary-dark">
                                <?php esc_html_e('Forgot your password?', 'happy-place-theme'); ?>
                            </a>
                        </div>

                        <!-- Divider -->
                        <div class="hph-relative">
                            <div class="hph-absolute hph-inset-0 hph-flex hph-items-center">
                                <div class="hph-w-full hph-border-t hph-border-gray-300"></div>
                            </div>
                            <div class="hph-relative hph-flex hph-justify-center hph-text-sm">
                                <span class="hph-px-2 hph-bg-white hph-text-gray-500">
                                    <?php esc_html_e('or', 'happy-place-theme'); ?>
                                </span>
                            </div>
                        </div>

                        <!-- Register Link -->
                        <div>
                            <p class="hph-text-sm hph-text-gray-600">
                                <?php esc_html_e("Don't have an account?", 'happy-place-theme'); ?>
                                <a href="<?php echo esc_url(home_url('/register/')); ?>" 
                                   class="hph-font-medium hph-text-primary hph-hover-text-primary-dark">
                                    <?php esc_html_e('Sign up', 'happy-place-theme'); ?>
                                </a>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Features -->
                <div class="hph-mt-2xl hph-text-center">
                    <div class="hph-grid hph-grid-cols-1 hph-gap-6 hph-sm-grid-cols-3">
                        <div class="hph-text-center">
                            <div class="hph-text-3xl hph-mb-2">🏠</div>
                            <h3 class="hph-text-sm hph-font-medium hph-text-gray-900 hph-mb-1">
                                <?php esc_html_e('Find Your Dream Home', 'happy-place-theme'); ?>
                            </h3>
                            <p class="hph-text-xs hph-text-gray-600">
                                <?php esc_html_e('Access exclusive listings', 'happy-place-theme'); ?>
                            </p>
                        </div>
                        
                        <div class="hph-text-center">
                            <div class="hph-text-3xl hph-mb-2">🔍</div>
                            <h3 class="hph-text-sm hph-font-medium hph-text-gray-900 hph-mb-1">
                                <?php esc_html_e('Advanced Search', 'happy-place-theme'); ?>
                            </h3>
                            <p class="hph-text-xs hph-text-gray-600">
                                <?php esc_html_e('Custom search & alerts', 'happy-place-theme'); ?>
                            </p>
                        </div>
                        
                        <div class="hph-text-center">
                            <div class="hph-text-3xl hph-mb-2">�</div>
                            <h3 class="hph-text-sm hph-font-medium hph-text-gray-900 hph-mb-1">
                                <?php esc_html_e('Market Insights', 'happy-place-theme'); ?>
                            </h3>
                            <p class="hph-text-xs hph-text-gray-600">
                                <?php esc_html_e('Real-time market data', 'happy-place-theme'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

                        <div class="hph-text-center">
                            <div class="hph-text-3xl hph-mb-2">📊</div>
                            <h3 class="hph-text-sm hph-font-medium hph-text-gray-900 hph-mb-1">
                                <?php esc_html_e('Market Insights', 'happy-place-theme'); ?>
                            </h3>
                            <p class="hph-text-xs hph-text-gray-600">
                                <?php esc_html_e('Real-time market data', 'happy-place-theme'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
if (window.HPH) {
    HPH.register('loginPage', function() {
        return {
            init: function() {
                this.initPasswordToggle();
                this.initFormEffects();
                this.initFormValidation();
                this.initAccessibility();
            },

            initPasswordToggle: function() {
                const passwordToggle = document.querySelector('.hph-password-toggle');
                const passwordInput = document.querySelector('#password');

                if (passwordToggle && passwordInput) {
                    HPH.events.on(passwordToggle, 'click', () => {
                        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                        passwordInput.setAttribute('type', type);

                        const icon = passwordToggle.querySelector('svg');
                        if (icon) {
                            icon.style.opacity = type === 'text' ? '0.6' : '1';
                        }
                    });
                }
            },

            initFormEffects: function() {
                const inputs = document.querySelectorAll('.hph-form-input');
                inputs.forEach(input => {
                    HPH.events.on(input, 'focus', function() {
                        this.classList.add('hph-form-input-focused');
                    });

                    HPH.events.on(input, 'blur', function() {
                        this.classList.remove('hph-form-input-focused');
                    });
                });
            },

            initFormValidation: function() {
                const loginForm = document.querySelector('.hph-login-form');
                const submitButton = loginForm?.querySelector('.hph-btn');

                if (loginForm) {
                    HPH.events.on(loginForm, 'submit', (e) => {
                        const username = loginForm.querySelector('#username').value.trim();
                        const password = loginForm.querySelector('#password').value;

                        if (!username || !password) {
                            e.preventDefault();
                            this.showAlert('Please fill in all required fields.', 'error');
                            return;
                        }

                        if (submitButton) {
                            submitButton.disabled = true;
                            loginForm.classList.add('is-loading');
                            submitButton.textContent = 'Signing In...';
                        }
                    });
                }
            },

            showAlert: function(message, type = 'info') {
                const existingAlerts = document.querySelectorAll('.hph-alert');
                existingAlerts.forEach(alert => alert.remove());

                const alert = document.createElement('div');
                alert.className = `hph-alert hph-alert-${type} hph-mb-lg`;
                alert.innerHTML = `<p class="hph-mb-0">${message}</p>`;

                const form = document.querySelector('.hph-login-form');
                if (form) {
                    form.parentNode.insertBefore(alert, form);
                }

                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 5000);
            },

            initAccessibility: function() {
                const usernameInput = document.querySelector('#username');
                const passwordField = document.querySelector('#password');
                const passwordToggle = document.querySelector('.hph-password-toggle');

                if (usernameInput) {
                    usernameInput.setAttribute('aria-label', 'Username or Email Address');
                }

                if (passwordField) {
                    passwordField.setAttribute('aria-label', 'Password');
                }

                if (passwordToggle) {
                    passwordToggle.setAttribute('tabindex', '0');
                    passwordToggle.setAttribute('role', 'button');
                    passwordToggle.setAttribute('aria-label', 'Toggle password visibility');

                    HPH.events.on(passwordToggle, 'keydown', function(e) {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            this.click();
                        }
                    });
                }
            }
        };
    });
}
</script>

<?php get_footer(); ?>
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
}

.auth-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    max-width: 1000px;
    width: 100%;
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.auth-card {
    padding: 3rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.auth-sidebar {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 3rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.auth-header {
    text-align: center;
    margin-bottom: 2rem;
}

.auth-logo img {
    max-height: 60px;
    width: auto;
}

.auth-header h1,
.auth-header h2 {
    color: #1a202c;
    margin: 1rem 0 0.5rem 0;
}

.auth-header h2 {
    font-size: 1.875rem;
    font-weight: 700;
}

.auth-header p {
    color: #718096;
    font-size: 1rem;
}

.auth-message {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    font-weight: 500;
}

.auth-message.success {
    background: #f0fff4;
    color: #38a169;
    border: 1px solid #9ae6b4;
}

.auth-message.error {
    background: #fed7d7;
    color: #e53e3e;
    border: 1px solid #feb2b2;
}

.auth-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
}

.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="password"] {
    padding: 0.875rem 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.2s ease;
    background: #f7fafc;
}

.form-group input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    background: white;
}

.checkbox-group {
    flex-direction: row !important;
    align-items: center;
    gap: 0.5rem;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-size: 0.95rem;
    color: #4a5568;
}

.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: #667eea;
}

.btn {
    padding: 0.875rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.btn-full {
    width: 100%;
}

.auth-links {
    text-align: center;
    margin: 1.5rem 0;
}

.auth-links a {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
}

.auth-links a:hover {
    text-decoration: underline;
}

.auth-divider {
    text-align: center;
    margin: 1.5rem 0;
    position: relative;
    color: #a0aec0;
}

.auth-divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: #e2e8f0;
}

.auth-divider span {
    background: white;
    padding: 0 1rem;
    font-size: 0.875rem;
}

.auth-footer {
    text-align: center;
}

.auth-footer p {
    color: #718096;
    margin: 0;
}

.auth-footer a {
    color: #667eea;
    font-weight: 600;
    text-decoration: none;
}

.auth-footer a:hover {
    text-decoration: underline;
}

.auth-feature {
    margin-bottom: 2rem;
    text-align: center;
}

.feature-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.auth-feature h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0 0 0.5rem 0;
}

.auth-feature p {
    opacity: 0.9;
    line-height: 1.6;
    margin: 0;
}

@media (max-width: 768px) {
    .auth-container {
        grid-template-columns: 1fr;
        margin: 1rem;
    }
    
    .auth-sidebar {
        order: -1;
        padding: 2rem;
    }
    
    .auth-card {
        padding: 2rem;
    }
    
    .auth-feature {
        margin-bottom: 1.5rem;
    }
}
</style>

<script>
if (window.HPH) {
    HPH.register('loginPage', function() {
        return {
            init: function() {
                this.initPasswordToggle();
                this.initFormEffects();
                this.initFormValidation();
                this.initAccessibility();
            },

            initPasswordToggle: function() {
                const passwordToggle = document.querySelector('.hph-password-toggle');
                const passwordInput = document.querySelector('#password');

                if (passwordToggle && passwordInput) {
                    HPH.events.on(passwordToggle, 'click', () => {
                        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                        passwordInput.setAttribute('type', type);

                        const icon = passwordToggle.querySelector('svg');
                        if (icon) {
                            icon.style.opacity = type === 'text' ? '0.6' : '1';
                        }
                    });
                }
            },

            initFormEffects: function() {
                const inputs = document.querySelectorAll('.hph-form-input');
                inputs.forEach(input => {
                    HPH.events.on(input, 'focus', function() {
                        this.classList.add('hph-form-input-focused');
                    });

                    HPH.events.on(input, 'blur', function() {
                        this.classList.remove('hph-form-input-focused');
                    });
                });
            },

            initFormValidation: function() {
                const loginForm = document.querySelector('.hph-login-form');
                const submitButton = loginForm?.querySelector('.hph-btn');

                if (loginForm) {
                    HPH.events.on(loginForm, 'submit', (e) => {
                        const username = loginForm.querySelector('#username').value.trim();
                        const password = loginForm.querySelector('#password').value;

                        if (!username || !password) {
                            e.preventDefault();
                            this.showAlert('Please fill in all required fields.', 'error');
                            return;
                        }

                        if (submitButton) {
                            submitButton.disabled = true;
                            loginForm.classList.add('is-loading');
                            submitButton.textContent = 'Signing In...';
                        }
                    });
                }
            },

            showAlert: function(message, type = 'info') {
                const existingAlerts = document.querySelectorAll('.hph-alert');
                existingAlerts.forEach(alert => alert.remove());

                const alert = document.createElement('div');
                alert.className = `hph-alert hph-alert-${type} hph-mb-lg`;
                alert.innerHTML = `<p class="hph-mb-0">${message}</p>`;

                const form = document.querySelector('.hph-login-form');
                if (form) {
                    form.parentNode.insertBefore(alert, form);
                }

                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 5000);
            },

            initAccessibility: function() {
                const usernameInput = document.querySelector('#username');
                const passwordField = document.querySelector('#password');
                const passwordToggle = document.querySelector('.hph-password-toggle');

                if (usernameInput) {
                    usernameInput.setAttribute('aria-label', 'Username or Email Address');
                }

                if (passwordField) {
                    passwordField.setAttribute('aria-label', 'Password');
                }

                if (passwordToggle) {
                    passwordToggle.setAttribute('tabindex', '0');
                    passwordToggle.setAttribute('role', 'button');
                    passwordToggle.setAttribute('aria-label', 'Toggle password visibility');

                    HPH.events.on(passwordToggle, 'keydown', function(e) {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            this.click();
                        }
                    });
                }
            }
        };
    });
}
</script>

<?php get_footer(); ?>
