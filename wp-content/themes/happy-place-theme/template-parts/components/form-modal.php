<?php
/**
 * Reusable Form Modal Component
 * 
 * A universal modal that can display any form template
 * Designed to replace contact page redirects with inline modals
 * 
 * @package HappyPlaceTheme
 * @since 3.2.0
 */

// Extract arguments
$args = wp_parse_args($args, [
    'modal_id' => 'hph-form-modal',
    'form_template' => 'general-contact', // Default form
    'form_args' => [],
    'modal_title' => __('Contact Us', 'happy-place-theme'),
    'modal_subtitle' => __('Send us a message and we\'ll get back to you soon.', 'happy-place-theme'),
    'modal_size' => 'lg', // sm, md, lg, xl
    'close_on_success' => true,
    'success_redirect' => '',
    'css_classes' => ''
]);

// Modal classes
$modal_classes = ['hph-form-modal', 'modal', 'fade'];
if ($args['css_classes']) $modal_classes[] = $args['css_classes'];

// Modal size classes
$modal_dialog_classes = ['modal-dialog', 'modal-dialog-centered', 'modal-dialog-scrollable'];
switch ($args['modal_size']) {
    case 'sm':
        $modal_dialog_classes[] = 'modal-sm';
        break;
    case 'lg':
        $modal_dialog_classes[] = 'modal-lg';
        break;
    case 'xl':
        $modal_dialog_classes[] = 'modal-xl';
        break;
    default:
        // Default modal size (md)
        break;
}

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

<!-- Form Modal -->
<div class="<?php echo implode(' ', $modal_classes); ?>" 
     id="<?php echo esc_attr($args['modal_id']); ?>" 
     tabindex="-1" 
     role="dialog" 
     aria-labelledby="<?php echo esc_attr($args['modal_id']); ?>Label" 
     aria-hidden="true"
     data-form-template="<?php echo esc_attr($args['form_template']); ?>"
     data-close-on-success="<?php echo $args['close_on_success'] ? 'true' : 'false'; ?>"
     data-success-redirect="<?php echo esc_url($args['success_redirect']); ?>">
     
    <div class="<?php echo implode(' ', $modal_dialog_classes); ?>" role="document">
        <div class="modal-content">
            
            <!-- Modal Header -->
            <div class="modal-header">
                <div class="hph-modal-header-content">
                    <h4 class="modal-title" id="<?php echo esc_attr($args['modal_id']); ?>Label">
                        <?php echo esc_html($args['modal_title']); ?>
                    </h4>
                    <?php if ($args['modal_subtitle']): ?>
                    <p class="hph-modal-subtitle">
                        <?php echo esc_html($args['modal_subtitle']); ?>
                    </p>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php _e('Close', 'happy-place-theme'); ?>">
                    <span aria-hidden="true"><i class="fas fa-times"></i></span>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body">
                <div id="hph-modal-form-container">
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
                <div id="hph-modal-success" class="hph-modal-success-message" style="display: none;">
                    <div class="hph-success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h4><?php _e('Message Sent Successfully!', 'happy-place-theme'); ?></h4>
                    <p><?php _e('Thank you for contacting us. We\'ll get back to you within 24 hours.', 'happy-place-theme'); ?></p>
                </div>
                
                <!-- Loading State -->
                <div id="hph-modal-loading" class="hph-modal-loading" style="display: none;">
                    <div class="hph-loading-spinner"></div>
                    <span><?php _e('Sending your message...', 'happy-place-theme'); ?></span>
                </div>
            </div>
            
            <!-- Modal Footer (Optional) -->
            <div class="modal-footer hph-modal-footer" style="display: none;">
                <button type="button" class="hph-btn hph-btn-secondary" data-bs-dismiss="modal">
                    <?php _e('Close', 'happy-place-theme'); ?>
                </button>
            </div>
            
        </div>
    </div>
</div>

<style>
/* Form Modal Specific Styles */
.hph-form-modal .modal-header {
    border-bottom: 1px solid var(--hph-border-color-light, #e5e7eb);
    padding: 1.5rem 2rem 1rem;
    background: linear-gradient(135deg, var(--hph-primary) 0%, var(--hph-primary-dark, #1d4ed8) 100%);
    color: white;
    border-radius: var(--hph-radius-lg, 0.5rem) var(--hph-radius-lg, 0.5rem) 0 0;
}

.hph-form-modal .modal-header .modal-title {
    color: white;
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0;
}

.hph-form-modal .hph-modal-subtitle {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.9rem;
    margin: 0.5rem 0 0;
    line-height: 1.4;
}

.hph-form-modal .btn-close {
    filter: invert(1);
    font-size: 1.2rem;
    opacity: 0.8;
}

.hph-form-modal .btn-close:hover {
    opacity: 1;
}

.hph-form-modal .modal-body {
    padding: 2rem;
}

.hph-form-modal .hph-form {
    margin: 0;
    background: transparent;
    border: none;
    box-shadow: none;
    padding: 0;
}

.hph-form-modal .hph-form-header {
    display: none; /* Header is handled by modal header */
}

/* Success Message Styles */
.hph-modal-success-message {
    text-align: center;
    padding: 2rem;
}

.hph-modal-success-message .hph-success-icon {
    font-size: 3rem;
    color: var(--hph-success, #10b981);
    margin-bottom: 1rem;
}

.hph-modal-success-message h4 {
    color: var(--hph-gray-900, #111827);
    margin-bottom: 0.5rem;
    font-size: 1.25rem;
    font-weight: 600;
}

.hph-modal-success-message p {
    color: var(--hph-gray-600, #6b7280);
    font-size: 0.9rem;
    margin: 0;
}

/* Loading State Styles */
.hph-modal-loading {
    text-align: center;
    padding: 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.hph-loading-spinner {
    width: 2rem;
    height: 2rem;
    border: 3px solid var(--hph-gray-200, #e5e7eb);
    border-top: 3px solid var(--hph-primary);
    border-radius: 50%;
    animation: hph-spin 1s linear infinite;
}

@keyframes hph-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.hph-modal-loading span {
    color: var(--hph-gray-600, #6b7280);
    font-size: 0.9rem;
}

/* Responsive Design */
@media (max-width: 576px) {
    .hph-form-modal .modal-dialog {
        margin: 1rem;
    }
    
    .hph-form-modal .modal-header,
    .hph-form-modal .modal-body {
        padding: 1.5rem;
    }
    
    .hph-form-modal .modal-header .modal-title {
        font-size: 1.25rem;
    }
}

/* Animation Enhancements */
.hph-form-modal.show .modal-dialog {
    animation: hph-modal-slide-up 0.3s ease-out;
}

@keyframes hph-modal-slide-up {
    from {
        transform: translateY(50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('<?php echo esc_js($args['modal_id']); ?>');
    if (!modal) return;
    
    const formContainer = modal.querySelector('#hph-modal-form-container');
    const successMessage = modal.querySelector('#hph-modal-success');
    const loadingMessage = modal.querySelector('#hph-modal-loading');
    const modalFooter = modal.querySelector('.modal-footer');
    
    // Handle form submission within modal
    modal.addEventListener('submit', function(e) {
        const form = e.target;
        if (!form.matches('.hph-form')) return;
        
        e.preventDefault();
        
        // Show loading state
        formContainer.style.display = 'none';
        loadingMessage.style.display = 'block';
        modalFooter.style.display = 'none';
        
        // Get form data
        const formData = new FormData(form);
        
        // Submit form via AJAX
        fetch(form.action || '<?php echo admin_url('admin-ajax.php'); ?>', {
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
                modalFooter.style.display = 'flex';
                
                // Auto-close modal if configured
                if (modal.dataset.closeOnSuccess === 'true') {
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(modal).hide();
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
                errorDiv.className = 'alert alert-danger';
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
            errorDiv.className = 'alert alert-danger';
            errorDiv.textContent = 'Network error. Please check your connection and try again.';
            formContainer.insertBefore(errorDiv, formContainer.firstChild);
            
            setTimeout(() => {
                errorDiv.remove();
            }, 5000);
        });
    });
    
    // Reset modal when closed
    modal.addEventListener('hidden.bs.modal', function() {
        // Reset to initial state
        formContainer.style.display = 'block';
        successMessage.style.display = 'none';
        loadingMessage.style.display = 'none';
        modalFooter.style.display = 'none';
        
        // Clear form
        const form = modal.querySelector('.hph-form');
        if (form) {
            form.reset();
        }
        
        // Remove any error messages
        modal.querySelectorAll('.alert').forEach(alert => alert.remove());
    });
});
</script>
