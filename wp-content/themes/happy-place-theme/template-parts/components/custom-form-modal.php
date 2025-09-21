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
        <div class="hph-modal-subtitle">
            <p class="hph-text-gray-600 hph-text-sm hph-leading-relaxed hph-m-0"><?php echo esc_html($args['modal_subtitle']); ?></p>
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
            <div class="hph-modal-success hph-text-center hph-p-8" style="display: none;">
                <div class="hph-success-icon hph-w-16 hph-h-16 hph-mx-auto hph-mb-6 hph-text-success">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h3 class="hph-text-xl hph-font-semibold hph-text-gray-900 hph-mb-4"><?php _e('Message Sent Successfully!', 'happy-place-theme'); ?></h3>
                <p class="hph-text-gray-600 hph-leading-relaxed hph-m-0"><?php _e('Thank you for contacting us. We\'ll get back to you within 24 hours.', 'happy-place-theme'); ?></p>
            </div>

            <!-- Loading State -->
            <div class="hph-modal-loading hph-flex hph-flex-col hph-items-center hph-gap-4 hph-p-8" style="display: none;">
                <div class="hph-modal-spinner"></div>
                <span class="hph-text-gray-600 hph-text-sm"><?php _e('Sending your message...', 'happy-place-theme'); ?></span>
            </div>
        </div>

    </div>
</div>


<!-- Modal JavaScript handled by form-modal.js -->
