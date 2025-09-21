<?php
/**
 * Form Modal Component - Happy Place Framework
 * 
 * Uses the existing Happy Place modal framework instead of Bootstrap
 * 
 * @package HappyPlaceTheme
 * @since 3.2.0
 */

// Extract arguments
$args = wp_parse_args($args, [
    'modal_id' => 'hph-form-modal',
    'form_template' => 'general-contact',
    'form_args' => [],
    'modal_title' => __('Contact Us', 'happy-place-theme'),
    'modal_subtitle' => __('Send us a message and we\'ll get back to you soon.', 'happy-place-theme'),
    'modal_size' => 'medium',
    'close_on_success' => true,
    'success_redirect' => '',
    'css_classes' => ''
]);

// Modal size mapping
$size_mapping = [
    'sm' => 'small',
    'md' => 'medium', 
    'lg' => 'large',
    'xl' => 'large'
];
$modal_size = $size_mapping[$args['modal_size']] ?? 'medium';

// Form arguments for template
$form_template_args = wp_parse_args($args['form_args'], [
    'modal_context' => true,
    'variant' => 'modern',
    'title' => $args['modal_title'],
    'description' => $args['modal_subtitle'],
    'submit_text' => __('Send Message', 'happy-place-theme'),
    'show_office_info' => false
]);
?>

<!-- Happy Place Form Modal -->
<div class="hph-modal" 
     id="<?php echo esc_attr($args['modal_id']); ?>"
     data-form-template="<?php echo esc_attr($args['form_template']); ?>"
     data-close-on-success="<?php echo $args['close_on_success'] ? 'true' : 'false'; ?>"
     data-success-redirect="<?php echo esc_url($args['success_redirect']); ?>"
     style="display: none;">
     
    <!-- Modal Backdrop -->
    <div class="hph-modal-backdrop" data-modal-close></div>
    
    <!-- Modal Content -->
    <div class="hph-modal-content hph-modal-content--<?php echo esc_attr($modal_size); ?>">
        
        <!-- Modal Header -->
        <div class="hph-modal-header">
            <h2 class="hph-modal-title"><?php echo esc_html($args['modal_title']); ?></h2>
            <button type="button" class="hph-modal-close hph-modal-close--inside" data-modal-close aria-label="<?php _e('Close', 'happy-place-theme'); ?>">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        
        <?php if ($args['modal_subtitle']): ?>
        <div class="hph-modal-subtitle-section">
            <p><?php echo esc_html($args['modal_subtitle']); ?></p>
        </div>
        <?php endif; ?>
        
        <!-- Modal Body -->
        <div class="hph-modal-body">
            <div class="hph-modal-form-container">
                <?php
                // Load the specified form template
                $form_template_path = "template-parts/forms/{$args['form_template']}";
                if (locate_template($form_template_path . '.php')) {
                    get_template_part($form_template_path, null, $form_template_args);
                } else {
                    // Fallback to general contact form
                    get_template_part('template-parts/forms/general-contact', null, $form_template_args);
                }
                ?>
            </div>
            
            <!-- Success Message (Hidden by default) -->
            <div class="hph-modal-success" style="display: none;">
                <div class="hph-success-icon">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h3><?php _e('Message Sent Successfully!', 'happy-place-theme'); ?></h3>
                <p><?php _e('Thank you for contacting us. We\'ll get back to you within 24 hours.', 'happy-place-theme'); ?></p>
            </div>
            
            <!-- Loading State -->
            <div class="hph-modal-loading" style="display: none;">
                <div class="hph-modal-spinner"></div>
                <span><?php _e('Sending your message...', 'happy-place-theme'); ?></span>
            </div>
        </div>
        
    </div>
</div>

<style>
/* Form Modal Specific Styles - Extends Happy Place Framework */

/* Subtitle styling (not in base framework) */
.hph-modal-subtitle-section {
    padding: 0 2rem 1rem;
    background: var(--hph-white);
    border-bottom: 1px solid var(--hph-gray-100);
}

.hph-modal-subtitle-section p {
    color: var(--hph-gray-600);
    margin: 0;
    font-size: 0.95rem;
    line-height: 1.4;
}

/* Form container adjustments */
.hph-modal-form-container .hph-form-header {
    display: none; /* Header handled by modal header */
}

.hph-modal-form-container .hph-form {
    margin: 0;
    background: transparent;
    border: none;
    box-shadow: none;
    padding: 0;
}

/* Fix dropdown arrow issues - ensure only one arrow appears */
.hph-modal .hph-form-select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e") !important;
    background-repeat: no-repeat !important;
    background-position: right 0.75rem center !important;
    background-size: 16px 12px !important;
}

.hph-modal .hph-form-select:focus {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2351bae0' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e") !important;
}

/* Success State */
.hph-modal-success {
    text-align: center;
    padding: 2rem;
}

.hph-success-icon {
    width: 4rem;
    height: 4rem;
    margin: 0 auto 1.5rem;
    color: var(--hph-success, #10b981);
}

.hph-modal-success h3 {
    color: var(--hph-gray-900, #111827);
    margin: 0 0 1rem 0;
    font-size: 1.25rem;
    font-weight: 600;
}

.hph-modal-success p {
    color: var(--hph-gray-600, #6b7280);
    margin: 0;
    line-height: 1.5;
}

/* Loading State */
.hph-modal-loading {
    text-align: center;
    padding: 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.hph-modal-loading span {
    color: var(--hph-gray-600, #6b7280);
    font-size: 0.95rem;
}

/* Fix icon centering in circle buttons */
.hph-modal-close--inside {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    line-height: 1 !important;
}

.hph-modal-close--inside span {
    display: block;
    line-height: 1;
    font-size: inherit;
}

/* Responsive adjustments for subtitle */
@media (max-width: 640px) {
    .hph-modal-subtitle-section {
        padding: 0 1.5rem 1rem;
    }
}

@media (max-width: 480px) {
    .hph-modal-subtitle-section {
        padding: 0 1rem 1rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('<?php echo esc_js($args['modal_id']); ?>');
    if (!modal) return;
    
    const formContainer = modal.querySelector('.hph-modal-form-container');
    const successMessage = modal.querySelector('.hph-modal-success');
    const loadingMessage = modal.querySelector('.hph-modal-loading');
    
    // Close modal function - uses Happy Place framework patterns
    function closeModal() {
        modal.classList.remove('active');
        document.body.style.overflow = '';
        
        // Reset modal state after animation
        setTimeout(() => {
            modal.style.display = 'none';
            formContainer.style.display = 'block';
            successMessage.style.display = 'none';
            loadingMessage.style.display = 'none';
            
            // Clear form
            const form = modal.querySelector('.hph-form');
            if (form) {
                form.reset();
            }
            
            // Remove error messages
            modal.querySelectorAll('.hph-form-error, .alert').forEach(el => el.remove());
        }, 300);
    }
    
    // Open modal function - uses Happy Place framework patterns
    window.openHphFormModal = function() {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Trigger animation
        requestAnimationFrame(() => {
            modal.classList.add('active');
        });
    };
    
    // Backward compatibility
    window.openCustomModal = window.openHphFormModal;
    
    // Close modal event handlers
    modal.addEventListener('click', function(e) {
        // Check if clicked element or its parent has data-modal-close or is the backdrop
        const closeElement = e.target.closest('[data-modal-close]');
        const isBackdrop = e.target.classList.contains('hph-modal-backdrop');
        
        if (closeElement || isBackdrop) {
            closeModal();
        }
    });
    
    // Escape key to close
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            closeModal();
        }
    });
    
    // Handle form submission
    modal.addEventListener('submit', function(e) {
        const form = e.target;
        if (!form.matches('.hph-form')) return;
        
        e.preventDefault();
        
        // Show loading state
        formContainer.style.display = 'none';
        loadingMessage.style.display = 'block';
        
        // Get form data
        const formData = new FormData(form);
        
        // Submit form via AJAX
        const ajaxUrl = window.hph_ajax?.ajax_url || window.hph_ajax?.url || '/wp-admin/admin-ajax.php';
        
        fetch(ajaxUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Hide loading state
            loadingMessage.style.display = 'none';
            
            if (data.success) {
                // Show success message
                successMessage.style.display = 'block';
                
                // Auto-close modal if configured
                if (modal.dataset.closeOnSuccess === 'true') {
                    setTimeout(() => {
                        closeModal();
                    }, 3000);
                }
                
                // Redirect if configured
                const redirectUrl = modal.dataset.successRedirect;
                if (redirectUrl) {
                    setTimeout(() => {
                        window.location.href = redirectUrl;
                    }, 2000);
                }
            } else {
                // Show error and return to form
                formContainer.style.display = 'block';
                
                // Show error message
                const errorDiv = document.createElement('div');
                errorDiv.className = 'hph-form-error';
                errorDiv.style.cssText = 'background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.375rem; margin-bottom: 1rem;';
                errorDiv.textContent = data.data?.message || 'An error occurred. Please try again.';
                formContainer.insertBefore(errorDiv, formContainer.firstChild);
                
                // Remove error after 5 seconds
                setTimeout(() => {
                    errorDiv.remove();
                }, 5000);
            }
        })
        .catch(error => {
            console.error('Form submission error:', error);
            
            // Hide loading, show form with error
            loadingMessage.style.display = 'none';
            formContainer.style.display = 'block';
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'hph-form-error';
            errorDiv.style.cssText = 'background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.375rem; margin-bottom: 1rem;';
            errorDiv.textContent = 'Network error. Please check your connection and try again.';
            formContainer.insertBefore(errorDiv, formContainer.firstChild);
            
            setTimeout(() => {
                errorDiv.remove();
            }, 5000);
        });
    });
});
</script>
