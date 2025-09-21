<?php
/**
 * Template Name: Register Page
 * Custom registration page that overrides WordPress default
 */

// Redirect if already logged in
if (is_user_logged_in()) {
    wp_redirect(home_url('/dashboard/'));
    exit;
}

// Handle registration form submission
$register_errors = [];
$register_message = '';

if ($_POST && isset($_POST['register_form'])) {
    $username = sanitize_user($_POST['username']);
    $email = sanitize_email($_POST['email']);
    $first_name = sanitize_text_field($_POST['first_name']);
    $last_name = sanitize_text_field($_POST['last_name']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $terms = isset($_POST['terms']);
    
    // Validation
    if (empty($username)) {
        $register_errors[] = 'Username is required.';
    } elseif (username_exists($username)) {
        $register_errors[] = 'Username already exists.';
    } elseif (!validate_username($username)) {
        $register_errors[] = 'Username contains invalid characters.';
    }
    
    if (empty($email)) {
        $register_errors[] = 'Email is required.';
    } elseif (!is_email($email)) {
        $register_errors[] = 'Please enter a valid email address.';
    } elseif (email_exists($email)) {
        $register_errors[] = 'Email address is already registered.';
    }
    
    if (empty($first_name)) {
        $register_errors[] = 'First name is required.';
    }
    
    if (empty($last_name)) {
        $register_errors[] = 'Last name is required.';
    }
    
    if (empty($password)) {
        $register_errors[] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $register_errors[] = 'Password must be at least 8 characters long.';
    }
    
    if ($password !== $password_confirm) {
        $register_errors[] = 'Passwords do not match.';
    }
    
    if (!$terms) {
        $register_errors[] = 'You must agree to the Terms of Service.';
    }
    
    if (empty($register_errors)) {
        // Create user
        $user_id = wp_create_user($username, $password, $email);
        
        if (is_wp_error($user_id)) {
            $register_errors[] = $user_id->get_error_message();
        } else {
            // Update user meta
            wp_update_user([
                'ID' => $user_id,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'display_name' => $first_name . ' ' . $last_name
            ]);
            
            // Set default role
            $user = get_user_by('id', $user_id);
            $user->set_role('subscriber'); // Default role for new registrations
            
            // Send welcome email (optional)
            wp_new_user_notification($user_id, null, 'both');
            
            // Redirect to login with success message
            wp_redirect(home_url('/login/?registered=1'));
            exit;
        }
    }
}

get_header();
?>

<div class="auth-page">
    <div class="auth-container register-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <?php if (has_custom_logo()): ?>
                        <?php the_custom_logo(); ?>
                    <?php else: ?>
                        <h1><?php bloginfo('name'); ?></h1>
                    <?php endif; ?>
                </div>
                <h2>Create Your Account</h2>
                <p>Join us and start your real estate journey</p>
            </div>

            <?php if (!empty($register_errors)): ?>
                <div class="auth-message error">
                    <?php foreach ($register_errors as $error): ?>
                        <p><?php echo esc_html($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" class="auth-form">
                <?php wp_nonce_field('register_form', 'register_nonce'); ?>
                <input type="hidden" name="register_form" value="1">

                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name <span class="required">*</span></label>
                        <input type="text" id="first_name" name="first_name" 
                               value="<?php echo esc_attr($_POST['first_name'] ?? ''); ?>" 
                               required autocomplete="given-name">
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name <span class="required">*</span></label>
                        <input type="text" id="last_name" name="last_name" 
                               value="<?php echo esc_attr($_POST['last_name'] ?? ''); ?>" 
                               required autocomplete="family-name">
                    </div>
                </div>

                <div class="form-group">
                    <label for="username">Username <span class="required">*</span></label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo esc_attr($_POST['username'] ?? ''); ?>" 
                           required autocomplete="username">
                    <small>Username must be unique and contain only letters, numbers, and underscores</small>
                </div>

                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo esc_attr($_POST['email'] ?? ''); ?>" 
                           required autocomplete="email">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password <span class="required">*</span></label>
                        <input type="password" id="password" name="password" required autocomplete="new-password">
                        <small>At least 8 characters</small>
                    </div>

                    <div class="form-group">
                        <label for="password_confirm">Confirm Password <span class="required">*</span></label>
                        <input type="password" id="password_confirm" name="password_confirm" required autocomplete="new-password">
                    </div>
                </div>

                <div class="form-group checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="terms" value="1" required>
                        <span class="checkmark"></span>
                        I agree to the <a href="<?php echo home_url('/legal/#terms'); ?>" target="_blank">Terms of Service</a> and <a href="<?php echo home_url('/legal/#privacy'); ?>" target="_blank">Privacy Policy</a>
                    </label>
                </div>

                <button type="submit" class="hph-btn hph-btn-primary btn-full">
                    Create Account
                </button>
            </form>

            <div class="auth-divider">
                <span>or</span>
            </div>

            <div class="auth-footer">
                <p>Already have an account? <a href="<?php echo esc_url(home_url('/login/')); ?>">Sign in</a></p>
            </div>
        </div>

        <div class="auth-sidebar">
            <div class="auth-feature">
                <div class="feature-icon">🎯</div>
                <h3>Personalized Experience</h3>
                <p>Get property recommendations tailored to your preferences</p>
            </div>
            
            <div class="auth-feature">
                <div class="feature-icon">💾</div>
                <h3>Save Favorites</h3>
                <p>Bookmark properties and create custom collections</p>
            </div>
            
            <div class="auth-feature">
                <div class="feature-icon">🔔</div>
                <h3>Instant Notifications</h3>
                <p>Be the first to know about new listings in your area</p>
            </div>
            
            <div class="auth-feature">
                <div class="feature-icon">📱</div>
                <h3>Mobile Access</h3>
                <p>Access your account and listings from any device</p>
            </div>
        </div>
    </div>
</div>

<style>
.register-container {
    max-width: 1200px;
}

.register-container .auth-card {
    max-width: none;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group small {
    margin-top: 0.25rem;
    color: #718096;
    font-size: 0.875rem;
}

.required {
    color: #e53e3e;
}

.checkbox-label a {
    color: #667eea;
    text-decoration: none;
}

.checkbox-label a:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}

/* Password Strength Indicator */
.password-strength {
    margin-top: 0.5rem;
    padding: 0.5rem;
    border-radius: 4px;
    font-size: 0.875rem;
    font-weight: 500;
}

.password-strength.weak {
    background: #fed7d7;
    color: #e53e3e;
}

.password-strength.medium {
    background: #fef5e7;
    color: #dd6b20;
}

.password-strength.strong {
    background: #f0fff4;
    color: #38a169;
}
</style>

<script>
if (window.HPH) {
    HPH.register('registerPage', function() {
        return {
            usernameTimeout: null,

            init: function() {
                this.initPasswordValidation();
                this.initUsernameValidation();
                this.initFormValidation();
            },

            checkPasswordStrength: function(password) {
                let strength = 0;
                if (password.length >= 8) strength += 1;
                if (/[a-z]/.test(password)) strength += 1;
                if (/[A-Z]/.test(password)) strength += 1;
                if (/[0-9]/.test(password)) strength += 1;
                if (/[^A-Za-z0-9]/.test(password)) strength += 1;
                return strength;
            },

            displayPasswordStrength: function(strength, passwordInput) {
                let existingIndicator = document.querySelector('.password-strength');
                if (existingIndicator) {
                    existingIndicator.remove();
                }

                if (passwordInput.value.length === 0) return;

                const indicator = document.createElement('div');
                indicator.className = 'password-strength';

                if (strength < 2) {
                    indicator.className += ' weak';
                    indicator.textContent = 'Weak password';
                } else if (strength < 4) {
                    indicator.className += ' medium';
                    indicator.textContent = 'Medium strength password';
                } else {
                    indicator.className += ' strong';
                    indicator.textContent = 'Strong password';
                }

                passwordInput.parentNode.appendChild(indicator);
            },

            showFieldError: function(field, message) {
                this.clearFieldError(field);
                const error = document.createElement('div');
                error.className = 'field-error';
                error.style.color = '#e53e3e';
                error.style.fontSize = '0.875rem';
                error.style.marginTop = '0.25rem';
                error.textContent = message;
                field.parentNode.appendChild(error);
                field.style.borderColor = '#e53e3e';
            },

            clearFieldError: function(field) {
                const error = field.parentNode.querySelector('.field-error');
                if (error) error.remove();
                field.style.borderColor = '';
            },

            initPasswordValidation: function() {
                const passwordInput = document.getElementById('password');
                const confirmInput = document.getElementById('password_confirm');

                if (passwordInput && confirmInput) {
                    HPH.events.on(passwordInput, 'input', () => {
                        const strength = this.checkPasswordStrength(passwordInput.value);
                        this.displayPasswordStrength(strength, passwordInput);

                        if (confirmInput.value) {
                            this.validatePasswordMatch(passwordInput, confirmInput);
                        }
                    });

                    HPH.events.on(confirmInput, 'input', () => {
                        this.validatePasswordMatch(passwordInput, confirmInput);
                    });
                }
            },

            validatePasswordMatch: function(passwordInput, confirmInput) {
                if (passwordInput.value !== confirmInput.value) {
                    this.showFieldError(confirmInput, 'Passwords do not match');
                } else {
                    this.clearFieldError(confirmInput);
                }
            },

            initUsernameValidation: function() {
                const usernameInput = document.getElementById('username');
                if (usernameInput) {
                    HPH.events.on(usernameInput, 'input', () => {
                        this.checkUsernameAvailability(usernameInput.value);
                    });
                }
            },

            checkUsernameAvailability: function(username) {
                clearTimeout(this.usernameTimeout);
                this.usernameTimeout = setTimeout(() => {
                    if (username.length < 3) return;

                    const usernameInput = document.getElementById('username');
                    if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                        this.showFieldError(usernameInput, 'Username can only contain letters, numbers, and underscores');
                    } else {
                        this.clearFieldError(usernameInput);
                    }
                }, 500);
            },

            initFormValidation: function() {
                const form = document.querySelector('.auth-form');
                if (form) {
                    HPH.events.on(form, 'submit', (e) => {
                        let isValid = true;
                        const passwordInput = document.getElementById('password');
                        const confirmInput = document.getElementById('password_confirm');

                        // Clear previous errors
                        document.querySelectorAll('.field-error').forEach(error => error.remove());
                        document.querySelectorAll('input').forEach(input => input.style.borderColor = '');

                        // Validate required fields
                        const requiredFields = form.querySelectorAll('[required]');
                        requiredFields.forEach(field => {
                            if (!field.value.trim()) {
                                this.showFieldError(field, 'This field is required');
                                isValid = false;
                            }
                        });

                        // Validate password match
                        if (passwordInput.value !== confirmInput.value) {
                            this.showFieldError(confirmInput, 'Passwords do not match');
                            isValid = false;
                        }

                        // Validate password strength
                        const strength = this.checkPasswordStrength(passwordInput.value);
                        if (strength < 2) {
                            this.showFieldError(passwordInput, 'Password is too weak');
                            isValid = false;
                        }

                        if (!isValid) {
                            e.preventDefault();
                        }
                    });
                }
            }
        };
    });
}
</script>

<?php get_footer(); ?>
