<?php
/**
 * Open House Visitor Registration Template
 * 
 * @package HappyPlaceTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$open_house_id = get_query_var('open_house_id', 0);
$open_house = null;

if ($open_house_id) {
    $open_house = get_post($open_house_id);
    if (!$open_house || $open_house->post_type !== 'open_house') {
        $open_house = null;
    }
}

if (!$open_house) {
    echo '<p class="alert alert-danger">Open house not found.</p>';
    return;
}

// Get open house details
$listing_id = get_post_meta($open_house->ID, 'listing_id', true);
$listing = get_post($listing_id);
$event_date = get_post_meta($open_house->ID, 'start_date', true);
$start_time = get_post_meta($open_house->ID, 'start_time', true);
$end_time = get_post_meta($open_house->ID, 'end_time', true);
$require_registration = get_post_meta($open_house->ID, 'require_registration', true);

// Format date and time
$event_datetime = date('l, F j, Y', strtotime($event_date));
$time_range = date('g:i A', strtotime($start_time)) . ' - ' . date('g:i A', strtotime($end_time));
?>

<div class="visitor-registration-form">
    <!-- Open House Info Header -->
    <div class="open-house-header card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="h4 mb-1"><?php echo esc_html($open_house->post_title); ?></h2>
                    <?php if ($listing): ?>
                        <p class="text-muted mb-1"><?php echo esc_html($listing->post_title); ?></p>
                        <?php 
                        $address = get_post_meta($listing_id, 'street_address', true);
                        if ($address): 
                        ?>
                            <p class="text-muted mb-0">
                                <i class="fas fa-map-marker-alt"></i> <?php echo esc_html($address); ?>
                            </p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="event-details">
                        <div class="event-date">
                            <strong><?php echo $event_datetime; ?></strong>
                        </div>
                        <div class="event-time text-muted">
                            <?php echo $time_range; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Registration Form -->
    <div class="card">
        <div class="card-header">
            <h3 class="h5 mb-0">
                <i class="fas fa-user-plus me-2"></i>
                <?php echo $require_registration ? 'RSVP Required' : 'Register for Updates'; ?>
            </h3>
        </div>
        <div class="card-body">
            <?php if (!$require_registration): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Registration is optional for this open house, but helps us prepare better for your visit.
                </div>
            <?php endif; ?>

            <form id="visitor-registration-form" 
                  class="needs-validation" 
                  novalidate
                  data-route-type="lead_capture"
                  data-form-context="open_house_registration">
                <input type="hidden" name="action" value="hph_submit_lead">
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('hph_lead_nonce'); ?>">
                <input type="hidden" name="open_house_id" value="<?php echo esc_attr($open_house->ID); ?>">
                <input type="hidden" name="source" value="open_house_registration">
                <input type="hidden" name="form_type" value="open_house">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="first_name" class="form-label">
                            First Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="hph-form-input" id="first_name" name="first_name" required>
                        <div class="invalid-feedback">
                            Please provide your first name.
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="last_name" class="form-label">
                            Last Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="hph-form-input" id="last_name" name="last_name" required>
                        <div class="invalid-feedback">
                            Please provide your last name.
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">
                            Email Address <span class="text-danger">*</span>
                        </label>
                        <input type="email" class="hph-form-input" id="email" name="email" required>
                        <div class="invalid-feedback">
                            Please provide a valid email address.
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="hph-form-input" id="phone" name="phone" placeholder="(555) 123-4567">
                        <small class="form-text text-muted">Optional - helps us contact you if needed</small>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="party_size" class="form-label">Party Size</label>
                        <select class="hph-form-select" id="party_size" name="party_size">
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php selected($i, 1); ?>>
                                    <?php echo $i; ?> <?php echo $i == 1 ? 'person' : 'people'; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="interest_level" class="form-label">Interest Level</label>
                        <select class="hph-form-select" id="interest_level" name="interest_level">
                            <option value="3">General Interest</option>
                            <option value="2">Seriously Considering</option>
                            <option value="1">Ready to Make an Offer</option>
                            <option value="4">Just Browsing</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="message" class="form-label">Questions or Comments</label>
                    <textarea class="hph-form-textarea" id="message" name="message" rows="3" 
                              placeholder="Any specific questions about the property or special requirements for your visit?"></textarea>
                </div>
                
                <div class="hph-form-check mb-3">
                    <input class="hph-form-check-input" type="checkbox" id="marketing_consent" name="marketing_consent" value="1">
                    <label class="hph-form-check-label" for="marketing_consent">
                        I would like to receive updates about similar properties and market information
                    </label>
                </div>
                
                <div class="hph-form-check mb-4">
                    <input class="hph-form-check-input" type="checkbox" id="agent_contact" name="agent_contact" value="1">
                    <label class="hph-form-check-label" for="agent_contact">
                        I'm interested in speaking with a real estate agent about this property
                    </label>
                </div>
                
                <!-- Form Messages -->
                <div id="form-messages" class="mb-3" style="display: none;"></div>
                
                <!-- Submit Button -->
                <div class="d-grid">
                    <button type="submit" class="hph-btn hph-btn-primary hph-btn-lg">
                        <span class="hph-btn-text">
                            <i class="fas fa-check me-2"></i>
                            <?php echo $require_registration ? 'Confirm RSVP' : 'Register Interest'; ?>
                        </span>
                        <span class="hph-btn-loading d-none">
                            <i class="fas fa-spinner fa-spin me-2"></i>
                            Submitting...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Additional Information -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-info-circle text-info me-2"></i>What to Expect
                    </h5>
                    <ul class="list-unstyled mb-0">
                        <li><i class="fas fa-check text-success me-2"></i>Tour the entire property</li>
                        <li><i class="fas fa-check text-success me-2"></i>Ask questions about features and neighborhood</li>
                        <li><i class="fas fa-check text-success me-2"></i>Speak with the listing agent</li>
                        <li><i class="fas fa-check text-success me-2"></i>Get market information and comparable sales</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-shield-alt text-success me-2"></i>Privacy & Security
                    </h5>
                    <ul class="list-unstyled mb-0">
                        <li><i class="fas fa-lock text-success me-2"></i>Your information is kept secure</li>
                        <li><i class="fas fa-user-shield text-success me-2"></i>Only shared with relevant agents</li>
                        <li><i class="fas fa-times-circle text-success me-2"></i>No spam or unwanted calls</li>
                        <li><i class="fas fa-envelope text-success me-2"></i>Unsubscribe from emails anytime</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="registrationSuccessModal" tabindex="-1" aria-labelledby="registrationSuccessLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="registrationSuccessLabel">
                    <i class="fas fa-check-circle me-2"></i>Registration Confirmed!
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Thank you for registering for our open house!</p>
                
                <div class="registration-summary">
                    <h6>Event Details:</h6>
                    <p class="mb-2"><strong><?php echo esc_html($open_house->post_title); ?></strong></p>
                    <p class="mb-2"><?php echo $event_datetime; ?> at <?php echo $time_range; ?></p>
                    <?php if ($listing && $address): ?>
                        <p class="mb-3"><i class="fas fa-map-marker-alt me-1"></i> <?php echo esc_html($address); ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-envelope me-2"></i>
                    You'll receive a confirmation email with event details and directions.
                </div>
                
                <div class="next-steps">
                    <h6>What's Next:</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-calendar-check text-success me-2"></i>Add to your calendar</li>
                        <li><i class="fas fa-route text-success me-2"></i>Plan your route to the property</li>
                        <li><i class="fas fa-list text-success me-2"></i>Prepare any questions you'd like to ask</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="hph-btn hph-btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="<?php echo get_permalink($listing_id); ?>" class="hph-btn hph-btn-primary">
                    <i class="fas fa-home me-2"></i>View Property Details
                </a>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Form validation and submission
    $('#visitor-registration-form').on('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        const $form = $(form);
        const $submitBtn = $form.find('button[type="submit"]');
        const $btnText = $submitBtn.find('.btn-text');
        const $btnLoading = $submitBtn.find('.btn-loading');
        const $messages = $('#form-messages');
        
        // Bootstrap validation
        if (!form.checkValidity()) {
            e.stopPropagation();
            form.classList.add('was-validated');
            return;
        }
        
        // Show loading state
        $submitBtn.prop('disabled', true);
        $btnText.addClass('d-none');
        $btnLoading.removeClass('d-none');
        $messages.hide();
        
        // Submit form
        $.post(ajaxurl || '/wp-admin/admin-ajax.php', $form.serialize())
            .done(function(response) {
                if (response.success) {
                    // Show success modal
                    $('#registrationSuccessModal').modal('show');
                    
                    // Reset form
                    form.reset();
                    form.classList.remove('was-validated');
                } else {
                    // Show error message
                    $messages.html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>' + 
                                   (response.data.message || 'Registration failed. Please try again.') + '</div>').show();
                }
            })
            .fail(function() {
                $messages.html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>' +
                               'Network error. Please check your connection and try again.</div>').show();
            })
            .always(function() {
                // Reset button state
                $submitBtn.prop('disabled', false);
                $btnText.removeClass('d-none');
                $btnLoading.addClass('d-none');
            });
    });
    
    // Auto-format phone number
    $('#phone').on('input', function() {
        let value = this.value.replace(/\D/g, '');
        if (value.length >= 10) {
            value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
        }
        this.value = value;
    });
});
</script>

<style>
.visitor-registration-form .open-house-header {
    border-left: 4px solid #0d6efd;
}

.visitor-registration-form .event-details {
    text-align: right;
}

.visitor-registration-form .event-date {
    font-size: 1.1em;
    color: #0d6efd;
}

.visitor-registration-form .btn-loading {
    display: none;
}

.visitor-registration-form .btn-loading.show {
    display: inline;
}

.visitor-registration-form .card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: none;
}

.visitor-registration-form .card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.visitor-registration-form .hph-form-check-input:checked {
    background-color: var(--hph-primary);
    border-color: var(--hph-primary);
}

@media (max-width: 768px) {
    .visitor-registration-form .event-details {
        text-align: left;
        margin-top: 1rem;
    }
}
</style>
