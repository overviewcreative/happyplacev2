<?php
/**
 * Dashboard Form Component - Reusable form container for dashboard actions
 *
 * @package HappyPlaceTheme
 */

// Default attributes
$form_args = wp_parse_args($args ?? [], [
    'title' => '',
    'description' => '',
    'form_id' => 'hph-dashboard-form',
    'method' => 'POST',
    'action' => '',
    'ajax' => true,
    'nonce_action' => 'hph_dashboard_action',
    'submit_text' => 'Submit',
    'submit_color' => 'primary',
    'cancel_url' => '',
    'cancel_text' => 'Cancel',
    'loading_text' => 'Processing...',
    'success_message' => 'Action completed successfully!',
    'error_message' => 'An error occurred. Please try again.',
    'fields' => [],
    'compact' => false,
    'show_header' => true
]);

// Generate unique form ID if not provided
if (empty($form_args['form_id'])) {
    $form_args['form_id'] = 'hph-form-' . uniqid();
}

$form_class = $form_args['compact'] ? 'hph-dashboard-form-compact' : 'hph-dashboard-form';
?>

<div class="<?php echo esc_attr($form_class); ?> hph-card">
    <?php if ($form_args['show_header'] && ($form_args['title'] || $form_args['description'])) : ?>
        <div class="hph-form-header hph-p-4 hph-border-b">
            <?php if ($form_args['title']) : ?>
                <h3 class="hph-form-title hph-font-medium hph-mb-2">
                    <?php echo esc_html($form_args['title']); ?>
                </h3>
            <?php endif; ?>
            
            <?php if ($form_args['description']) : ?>
                <p class="hph-form-description hph-text-sm hph-text-muted hph-mb-0">
                    <?php echo esc_html($form_args['description']); ?>
                </p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="hph-form-body hph-p-4">
        <!-- Success/Error Messages -->
        <div id="<?php echo esc_attr($form_args['form_id']); ?>-messages" class="hph-form-messages hph-hidden hph-mb-4">
            <div class="hph-alert hph-alert-success hph-hidden" id="<?php echo esc_attr($form_args['form_id']); ?>-success">
                <i class="fas fa-check-circle hph-mr-2"></i>
                <span class="hph-message-text"><?php echo esc_html($form_args['success_message']); ?></span>
            </div>
            
            <div class="hph-alert hph-alert-danger hph-hidden" id="<?php echo esc_attr($form_args['form_id']); ?>-error">
                <i class="fas fa-exclamation-triangle hph-mr-2"></i>
                <span class="hph-message-text"><?php echo esc_html($form_args['error_message']); ?></span>
            </div>
        </div>
        
        <form id="<?php echo esc_attr($form_args['form_id']); ?>" 
              method="<?php echo esc_attr($form_args['method']); ?>"
              <?php if ($form_args['action']) : ?>action="<?php echo esc_url($form_args['action']); ?>"<?php endif; ?>
              <?php if ($form_args['ajax']) : ?>class="hph-ajax-form"<?php endif; ?>>
            
            <?php if ($form_args['ajax']) : ?>
                <?php wp_nonce_field($form_args['nonce_action'], 'nonce'); ?>
            <?php endif; ?>
            
            <!-- Form Fields -->
            <div class="hph-form-fields hph-space-y-4">
                <?php foreach ($form_args['fields'] as $field) : ?>
                    <?php
                    $field_defaults = [
                        'type' => 'text',
                        'name' => '',
                        'label' => '',
                        'placeholder' => '',
                        'value' => '',
                        'required' => false,
                        'options' => [],
                        'description' => '',
                        'class' => '',
                        'wrapper_class' => ''
                    ];
                    $field = wp_parse_args($field, $field_defaults);
                    
                    $field_id = $form_args['form_id'] . '-' . $field['name'];
                    $required_attr = $field['required'] ? 'required' : '';
                    $field_class = 'hph-form-control ' . $field['class'];
                    ?>
                    
                    <div class="hph-form-group <?php echo esc_attr($field['wrapper_class']); ?>">
                        <?php if ($field['label']) : ?>
                            <label for="<?php echo esc_attr($field_id); ?>" class="hph-form-label">
                                <?php echo esc_html($field['label']); ?>
                                <?php if ($field['required']) : ?>
                                    <span class="hph-text-danger">*</span>
                                <?php endif; ?>
                            </label>
                        <?php endif; ?>
                        
                        <?php switch ($field['type']) :
                            case 'textarea': ?>
                                <textarea id="<?php echo esc_attr($field_id); ?>" 
                                         name="<?php echo esc_attr($field['name']); ?>"
                                         class="<?php echo esc_attr($field_class); ?>"
                                         placeholder="<?php echo esc_attr($field['placeholder']); ?>"
                                         <?php echo $required_attr; ?>
                                         rows="3"><?php echo esc_textarea($field['value']); ?></textarea>
                                <?php break;
                            
                            case 'select': ?>
                                <select id="<?php echo esc_attr($field_id); ?>" 
                                       name="<?php echo esc_attr($field['name']); ?>"
                                       class="<?php echo esc_attr($field_class); ?>"
                                       <?php echo $required_attr; ?>>
                                    <?php if ($field['placeholder']) : ?>
                                        <option value=""><?php echo esc_html($field['placeholder']); ?></option>
                                    <?php endif; ?>
                                    
                                    <?php foreach ($field['options'] as $option_value => $option_label) : ?>
                                        <option value="<?php echo esc_attr($option_value); ?>" 
                                                <?php selected($field['value'], $option_value); ?>>
                                            <?php echo esc_html($option_label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php break;
                            
                            case 'checkbox': ?>
                                <div class="hph-form-checkbox">
                                    <input type="checkbox" 
                                           id="<?php echo esc_attr($field_id); ?>" 
                                           name="<?php echo esc_attr($field['name']); ?>"
                                           value="1"
                                           <?php checked($field['value'], '1'); ?>
                                           <?php echo $required_attr; ?>>
                                    <?php if ($field['label']) : ?>
                                        <label for="<?php echo esc_attr($field_id); ?>" class="hph-checkbox-label">
                                            <?php echo esc_html($field['label']); ?>
                                        </label>
                                    <?php endif; ?>
                                </div>
                                <?php break;
                            
                            default: ?>
                                <input type="<?php echo esc_attr($field['type']); ?>" 
                                       id="<?php echo esc_attr($field_id); ?>" 
                                       name="<?php echo esc_attr($field['name']); ?>"
                                       class="<?php echo esc_attr($field_class); ?>"
                                       placeholder="<?php echo esc_attr($field['placeholder']); ?>"
                                       value="<?php echo esc_attr($field['value']); ?>"
                                       <?php echo $required_attr; ?>>
                                <?php break;
                        endswitch; ?>
                        
                        <?php if ($field['description']) : ?>
                            <small class="hph-form-text hph-text-muted"><?php echo esc_html($field['description']); ?></small>
                        <?php endif; ?>
                    </div>
                    
                <?php endforeach; ?>
            </div>
            
            <!-- Form Actions -->
            <div class="hph-form-actions hph-flex hph-gap-3 hph-mt-6">
                <button type="submit" class="hph-btn hph-btn-<?php echo esc_attr($form_args['submit_color']); ?>">
                    <span class="hph-btn-text"><?php echo esc_html($form_args['submit_text']); ?></span>
                    <span class="hph-btn-loading hph-hidden">
                        <i class="fas fa-circle-notch fa-spin hph-mr-2"></i>
                        <?php echo esc_html($form_args['loading_text']); ?>
                    </span>
                </button>
                
                <?php if ($form_args['cancel_url']) : ?>
                    <a href="<?php echo esc_url($form_args['cancel_url']); ?>" class="hph-btn hph-btn-outline">
                        <?php echo esc_html($form_args['cancel_text']); ?>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php if ($form_args['ajax']) : ?>
<script>
jQuery(document).ready(function($) {
    $('#<?php echo esc_js($form_args['form_id']); ?>').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $submitBtn = $form.find('button[type="submit"]');
        var $messages = $('#<?php echo esc_js($form_args['form_id']); ?>-messages');
        var $success = $('#<?php echo esc_js($form_args['form_id']); ?>-success');
        var $error = $('#<?php echo esc_js($form_args['form_id']); ?>-error');
        
        // Show loading state
        $submitBtn.find('.hph-btn-text').addClass('hph-hidden');
        $submitBtn.find('.hph-btn-loading').removeClass('hph-hidden');
        $submitBtn.prop('disabled', true);
        
        // Hide previous messages
        $messages.addClass('hph-hidden');
        $success.addClass('hph-hidden');
        $error.addClass('hph-hidden');
        
        // Submit form
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $success.find('.hph-message-text').text(response.data.message || '<?php echo esc_js($form_args['success_message']); ?>');
                    $success.removeClass('hph-hidden');
                    $messages.removeClass('hph-hidden');
                    
                    // Reset form if successful
                    $form[0].reset();
                    
                    // Trigger custom success event
                    $form.trigger('hph-form-success', [response]);
                } else {
                    $error.find('.hph-message-text').text(response.data.message || '<?php echo esc_js($form_args['error_message']); ?>');
                    $error.removeClass('hph-hidden');
                    $messages.removeClass('hph-hidden');
                    
                    // Trigger custom error event
                    $form.trigger('hph-form-error', [response]);
                }
            },
            error: function() {
                $error.find('.hph-message-text').text('<?php echo esc_js($form_args['error_message']); ?>');
                $error.removeClass('hph-hidden');
                $messages.removeClass('hph-hidden');
            },
            complete: function() {
                // Reset loading state
                $submitBtn.find('.hph-btn-text').removeClass('hph-hidden');
                $submitBtn.find('.hph-btn-loading').addClass('hph-hidden');
                $submitBtn.prop('disabled', false);
            }
        });
    });
});
</script>
<?php endif; ?>