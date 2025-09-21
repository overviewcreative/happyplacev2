<?php
/**
 * HPH Enhanced Form Section Template
 * Flexible form layouts with multiple content options
 * 
 * @package HappyPlaceTheme
 * @version 4.0.0
 */

// Register this template part for asset loading
if (function_exists('hph_register_template_part')) {
    hph_register_template_part('sections/form');
}

/**
 * Get form template configuration
 */
function get_form_template_config($template_name) {
    $template_configs = [
        'general-contact' => [
            'form_type' => 'contact',
            'form_id' => 'general-contact-form',
            'headline' => 'Contact Us',
            'subheadline' => 'We\'d love to hear from you. Send us a message and we\'ll respond as soon as possible.',
            'submit_text' => 'Send Message',
            'success_message' => 'Thank you! We\'ve received your message and will get back to you within 24 hours.',
            'form_fields' => [
                [
                    'type' => 'text',
                    'name' => 'first_name',
                    'label' => 'First Name',
                    'placeholder' => 'Enter your first name',
                    'required' => true,
                    'width' => 'half'
                ],
                [
                    'type' => 'text',
                    'name' => 'last_name',
                    'label' => 'Last Name',
                    'placeholder' => 'Enter your last name',
                    'required' => true,
                    'width' => 'half'
                ],
                [
                    'type' => 'email',
                    'name' => 'email',
                    'label' => 'Email Address',
                    'placeholder' => 'your.email@example.com',
                    'required' => true,
                    'width' => 'full'
                ],
                [
                    'type' => 'textarea',
                    'name' => 'message',
                    'label' => 'Message',
                    'placeholder' => 'How can we help you?',
                    'required' => true,
                    'width' => 'full',
                    'rows' => 4
                ]
            ]
        ],
        
        'property-inquiry' => [
            'form_type' => 'inquiry',
            'form_id' => 'property-inquiry-form',
            'headline' => 'Inquire About This Property',
            'subheadline' => 'Get more information about this property or schedule a viewing.',
            'submit_text' => 'Send Inquiry',
            'success_message' => 'Thank you for your interest! We\'ll contact you soon with more information.',
            'form_fields' => [
                [
                    'type' => 'text',
                    'name' => 'first_name',
                    'label' => 'First Name',
                    'placeholder' => 'Enter your first name',
                    'required' => true,
                    'width' => 'half'
                ],
                [
                    'type' => 'text',
                    'name' => 'last_name',
                    'label' => 'Last Name',
                    'placeholder' => 'Enter your last name',
                    'required' => true,
                    'width' => 'half'
                ],
                [
                    'type' => 'email',
                    'name' => 'email',
                    'label' => 'Email Address',
                    'placeholder' => 'your.email@example.com',
                    'required' => true,
                    'width' => 'half'
                ],
                [
                    'type' => 'tel',
                    'name' => 'phone',
                    'label' => 'Phone Number',
                    'placeholder' => '(302) 555-0123',
                    'required' => false,
                    'width' => 'half'
                ],
                [
                    'type' => 'select',
                    'name' => 'inquiry_type',
                    'label' => 'How can I help you?',
                    'placeholder' => 'Select inquiry type',
                    'required' => true,
                    'width' => 'full',
                    'options' => [
                        'specific-property' => 'This specific property',
                        'buying' => 'I\'m looking to buy',
                        'selling' => 'I\'m looking to sell',
                        'both' => 'I\'m buying and selling',
                        'investment' => 'Investment opportunities',
                        'consultation' => 'General consultation'
                    ]
                ],
                [
                    'type' => 'textarea',
                    'name' => 'message',
                    'label' => 'Additional Details',
                    'placeholder' => 'Tell us more about your needs, timeline, or any specific questions...',
                    'required' => true,
                    'width' => 'full',
                    'rows' => 4
                ]
            ]
        ],
        
        'showing-request' => [
            'form_type' => 'schedule',
            'form_id' => 'showing-request-form',
            'headline' => 'Schedule a Showing',
            'subheadline' => 'Book a private tour of this property at your convenience.',
            'submit_text' => 'Request Showing',
            'success_message' => 'Your showing request has been submitted! We\'ll contact you within 2 hours to confirm the appointment.',
            'form_fields' => [
                [
                    'type' => 'text',
                    'name' => 'first_name',
                    'label' => 'First Name',
                    'placeholder' => 'Enter your first name',
                    'required' => true,
                    'width' => 'half'
                ],
                [
                    'type' => 'text',
                    'name' => 'last_name',
                    'label' => 'Last Name',
                    'placeholder' => 'Enter your last name',
                    'required' => true,
                    'width' => 'half'
                ],
                [
                    'type' => 'email',
                    'name' => 'email',
                    'label' => 'Email Address',
                    'placeholder' => 'your.email@example.com',
                    'required' => true,
                    'width' => 'half'
                ],
                [
                    'type' => 'tel',
                    'name' => 'phone',
                    'label' => 'Phone Number',
                    'placeholder' => '(302) 555-0123',
                    'required' => true,
                    'width' => 'half'
                ],
                [
                    'type' => 'select',
                    'name' => 'preferred_time',
                    'label' => 'Preferred Showing Time',
                    'placeholder' => 'Select preferred time',
                    'required' => true,
                    'width' => 'half',
                    'options' => [
                        'morning' => 'Morning (9am - 12pm)',
                        'afternoon' => 'Afternoon (12pm - 5pm)',
                        'evening' => 'Evening (5pm - 8pm)',
                        'weekend' => 'Weekends only',
                        'flexible' => 'I\'m flexible'
                    ]
                ],
                [
                    'type' => 'select',
                    'name' => 'showing_type',
                    'label' => 'Type of Showing',
                    'placeholder' => 'Select showing type',
                    'required' => false,
                    'width' => 'half',
                    'options' => [
                        'private' => 'Private showing',
                        'virtual' => 'Virtual tour',
                        'group' => 'Group showing (if available)'
                    ]
                ],
                [
                    'type' => 'textarea',
                    'name' => 'message',
                    'label' => 'Special Requests or Questions',
                    'placeholder' => 'Any special requests, accessibility needs, or questions about the property?',
                    'required' => false,
                    'width' => 'full',
                    'rows' => 3
                ]
            ]
        ],
        
        'valuation-request' => [
            'form_type' => 'valuation',
            'form_id' => 'valuation-request-form',
            'headline' => 'Get Your Home\'s Value',
            'subheadline' => 'Receive a comprehensive market analysis of your property value from our expert team.',
            'submit_text' => 'Request Free Valuation',
            'success_message' => 'Thank you! We\'ll prepare your comprehensive market analysis and send it within 24 hours.',
            'form_fields' => [
                [
                    'type' => 'text',
                    'name' => 'first_name',
                    'label' => 'First Name',
                    'placeholder' => 'Enter your first name',
                    'required' => true,
                    'width' => 'half'
                ],
                [
                    'type' => 'text',
                    'name' => 'last_name',
                    'label' => 'Last Name',
                    'placeholder' => 'Enter your last name',
                    'required' => true,
                    'width' => 'half'
                ],
                [
                    'type' => 'email',
                    'name' => 'email',
                    'label' => 'Email Address',
                    'placeholder' => 'your.email@example.com',
                    'required' => true,
                    'width' => 'half'
                ],
                [
                    'type' => 'tel',
                    'name' => 'phone',
                    'label' => 'Phone Number',
                    'placeholder' => '(302) 555-0123',
                    'required' => true,
                    'width' => 'half'
                ],
                [
                    'type' => 'text',
                    'name' => 'property_address',
                    'label' => 'Property Address',
                    'placeholder' => '123 Main St, City, State, ZIP',
                    'required' => true,
                    'width' => 'full'
                ],
                [
                    'type' => 'select',
                    'name' => 'property_type',
                    'label' => 'Property Type',
                    'placeholder' => 'Select property type',
                    'required' => true,
                    'width' => 'half',
                    'options' => [
                        'single-family' => 'Single Family Home',
                        'condo' => 'Condominium',
                        'townhome' => 'Townhome',
                        'multi-family' => 'Multi-Family',
                        'land' => 'Land/Lot'
                    ]
                ],
                [
                    'type' => 'select',
                    'name' => 'timeline',
                    'label' => 'When are you thinking of selling?',
                    'placeholder' => 'Select timeline',
                    'required' => true,
                    'width' => 'half',
                    'options' => [
                        'immediately' => 'Immediately',
                        '1-3months' => 'Within 1-3 months',
                        '3-6months' => 'Within 3-6 months',
                        '6-12months' => 'Within 6-12 months',
                        'just-curious' => 'Just curious about value'
                    ]
                ],
                [
                    'type' => 'textarea',
                    'name' => 'message',
                    'label' => 'Additional Information',
                    'placeholder' => 'Tell us about any recent improvements, unique features, or specific questions about your property...',
                    'required' => false,
                    'width' => 'full',
                    'rows' => 4
                ]
            ]
        ],
        
        'agent-contact' => [
            'form_type' => 'inquiry',
            'form_id' => 'agent-contact-form',
            'headline' => 'Contact Agent',
            'subheadline' => 'Get in touch with our expert agent for personalized assistance.',
            'submit_text' => 'Send Message',
            'success_message' => 'Thank you for contacting our agent! You\'ll hear back within a few hours.',
            'form_fields' => [
                [
                    'type' => 'text',
                    'name' => 'first_name',
                    'label' => 'First Name',
                    'placeholder' => 'Enter your first name',
                    'required' => true,
                    'width' => 'half'
                ],
                [
                    'type' => 'text',
                    'name' => 'last_name',
                    'label' => 'Last Name',
                    'placeholder' => 'Enter your last name',
                    'required' => true,
                    'width' => 'half'
                ],
                [
                    'type' => 'email',
                    'name' => 'email',
                    'label' => 'Email Address',
                    'placeholder' => 'your.email@example.com',
                    'required' => true,
                    'width' => 'half'
                ],
                [
                    'type' => 'tel',
                    'name' => 'phone',
                    'label' => 'Phone Number',
                    'placeholder' => '(302) 555-0123',
                    'required' => false,
                    'width' => 'half'
                ],
                [
                    'type' => 'select',
                    'name' => 'contact_reason',
                    'label' => 'How can I help you?',
                    'placeholder' => 'Select reason for contact',
                    'required' => true,
                    'width' => 'full',
                    'options' => [
                        'buying' => 'I\'m looking to buy',
                        'selling' => 'I\'m looking to sell',
                        'both' => 'I\'m buying and selling',
                        'investment' => 'Investment opportunities',
                        'market-analysis' => 'Market analysis',
                        'consultation' => 'General consultation'
                    ]
                ],
                [
                    'type' => 'textarea',
                    'name' => 'message',
                    'label' => 'Message',
                    'placeholder' => 'Tell us about your real estate needs, timeline, or any questions you have...',
                    'required' => true,
                    'width' => 'full',
                    'rows' => 4
                ]
            ]
        ]
    ];
    
    return $template_configs[$template_name] ?? null;
}

// Default arguments
$defaults = array(
    // Layout options
    'style' => 'centered', // centered, split, full-width, card, overlay
    'layout' => 'form-right', // form-right, form-left, form-top, form-bottom
    'content_type' => 'text', // text, agent-card, features, image, video, map, testimonial
    
    // Section styling
    'background' => 'white', // white, gray, primary, gradient, image, pattern
    'background_image' => '',
    'background_overlay' => false,
    'padding' => 'xl', // none, sm, md, lg, xl, 2xl
    'container' => 'default', // full, wide, default, narrow
    'border_radius' => false,
    'shadow' => false,
    
    // Content
    'badge' => '',
    'headline' => 'Get in Touch',
    'subheadline' => '',
    'content' => '',
    
    // Form configuration
    'form_template' => '', // agent-contact, general-contact, property-inquiry, showing-request, valuation-request
    'form_id' => 'contact-form',
    'form_type' => 'contact', // contact, schedule, newsletter, inquiry, valuation
    'form_style' => 'default', // default, floating-labels, minimal, bordered
    'form_fields' => array(),
    'submit_text' => 'Send Message',
    'submit_style' => 'primary', // primary, secondary, gradient, outline
    'success_message' => 'Thank you! We\'ll get back to you soon.',
    
    // Agent/Staff Card (when content_type = 'agent-card')
    'agent' => array(
        'name' => '',
        'title' => '',
        'photo' => '',
        'phone' => '',
        'email' => '',
        'bio' => '',
        'social' => array(),
        'credentials' => array(),
    ),
    
    // Features List (when content_type = 'features')
    'features' => array(),
    
    // Image/Video (when content_type = 'image' or 'video')
    'media_url' => '',
    'media_caption' => '',
    
    // Testimonial (when content_type = 'testimonial')
    'testimonial' => array(
        'quote' => '',
        'author' => '',
        'role' => '',
        'photo' => '',
        'rating' => 5,
    ),
    
    // Advanced options
    'section_id' => '',
    'section_class' => '',
    'ajax_action' => 'hph_route_form',
    'animation' => false, // fade-in, slide-up, zoom-in
);

// Merge with provided args
$config = wp_parse_args($args ?? array(), $defaults);
extract($config);

// Apply form template configuration if specified
if (!empty($form_template)) {
    $template_config = get_form_template_config($form_template);
    if ($template_config) {
        // Override with template-specific configuration
        foreach ($template_config as $key => $value) {
            ${$key} = $value;
        }
        
        // Also try to load the corresponding form template part
        $form_template_path = get_template_directory() . "/template-parts/forms/{$form_template}.php";
        if (file_exists($form_template_path)) {
            // Template part exists - we'll use include method instead of inline form
            $use_template_part = true;
        }
    }
}

// Generate unique form ID
$unique_form_id = $form_id . '_' . uniqid();

// Determine route type and nonce action based on form template/type
$template_routing = [
    'general-contact' => ['route' => 'lead_capture', 'nonce' => 'hph_general_contact'],
    'property-inquiry' => ['route' => 'property_inquiry', 'nonce' => 'hph_property_inquiry'],
    'agent-contact' => ['route' => 'property_inquiry', 'nonce' => 'hph_agent_contact'],
    'showing-request' => ['route' => 'booking_request', 'nonce' => 'hph_showing_request'],
    'valuation-request' => ['route' => 'valuation_request', 'nonce' => 'hph_valuation_request']
];

// Get routing config for template or fallback to form type
if (!empty($form_template) && isset($template_routing[$form_template])) {
    $routing = $template_routing[$form_template];
    $route_type = $routing['route'];
    $nonce_action = $routing['nonce'];
    $nonce_field = 'contact_nonce'; // Use consistent field name
} else {
    // Fallback route type mapping
    $route_type_map = [
        'contact' => 'lead_capture',
        'inquiry' => 'property_inquiry', 
        'schedule' => 'booking_request',
        'valuation' => 'valuation_request',
        'newsletter' => 'email_only'
    ];
    $route_type = $route_type_map[$form_type] ?? 'lead_capture';
    $nonce_action = 'hph_lead_nonce';
    $nonce_field = 'nonce';
}

// Build section classes
$section_classes = array('hph-form-section');
$section_classes[] = 'hph-form-section--' . $style;
$section_classes[] = 'hph-form-section--bg-' . $background;
$section_classes[] = 'hph-form-section--padding-' . $padding;
if ($shadow) $section_classes[] = 'hph-form-section--shadow';
if ($border_radius) $section_classes[] = 'hph-form-section--rounded';
if ($animation) $section_classes[] = 'hph-animate hph-animate--' . $animation;
if ($section_class) $section_classes[] = $section_class;

// Container classes
$container_classes = array('hph-container');
$container_classes[] = 'hph-container--' . $container;
?>

<section class="<?php echo esc_attr(implode(' ', $section_classes)); ?>" 
    <?php if ($section_id): ?>id="<?php echo esc_attr($section_id); ?>"<?php endif; ?>
    <?php if ($background === 'image' && $background_image): ?>
        style="background-image: url('<?php echo esc_url($background_image); ?>');"
    <?php endif; ?>>
    
    <?php if ($background_overlay): ?>
    <div class="hph-section-overlay"></div>
    <?php endif; ?>
    
    <div class="<?php echo esc_attr(implode(' ', $container_classes)); ?>">
        
        <?php if ($style === 'split'): ?>
        <!-- ============================================
             SPLIT LAYOUT
             ============================================ -->
        <div class="hph-form-section__grid hph-form-section__grid--split">
            
            <?php if ($layout === 'form-right'): ?>
            
            <!-- Content Column -->
            <div class="hph-form-section__content">
                <?php 
                // Content Section
                if ($badge || $headline || $subheadline || $content || !empty($features)): ?>
                <div class="hph-form-content">
                    <?php if ($badge): ?>
                    <div class="hph-badge">
                        <span class="hph-badge-text"><?php echo esc_html($badge); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($headline): ?>
                    <h2 class="hph-form-headline"><?php echo esc_html($headline); ?></h2>
                    <?php endif; ?>
                    
                    <?php if ($subheadline): ?>
                    <p class="hph-form-subheadline"><?php echo esc_html($subheadline); ?></p>
                    <?php endif; ?>
                    
                    <?php if ($content): ?>
                    <div class="hph-form-content-text">
                        <?php echo wp_kses_post($content); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($content_type === 'features' && !empty($features)): ?>
                    <div class="hph-features-list">
                        <?php foreach ($features as $feature): ?>
                        <div class="hph-feature-item">
                            <?php if (!empty($feature['icon'])): ?>
                            <div class="hph-feature-icon">
                                <i class="<?php echo esc_attr($feature['icon']); ?>"></i>
                            </div>
                            <?php endif; ?>
                            <div class="hph-feature-content">
                                <?php if (!empty($feature['title'])): ?>
                                <h4 class="hph-feature-title"><?php echo esc_html($feature['title']); ?></h4>
                                <?php endif; ?>
                                <?php if (!empty($feature['description'])): ?>
                                <p class="hph-feature-description"><?php echo esc_html($feature['description']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Form Column -->
            <div class="hph-form-section__form">
                <div class="hph-form-wrapper hph-form-wrapper--<?php echo esc_attr($form_style); ?>">
                    <?php 
                    // Check if we should use a template part or inline form
                    if (!empty($use_template_part) && !empty($form_template)): 
                        // Use the dedicated template part with proper arguments
                        get_template_part('template-parts/forms/' . $form_template, null, [
                            'variant' => $form_style,
                            'css_classes' => 'hph-form--' . $form_type,
                            'form_id' => $unique_form_id,
                            'submit_text' => $submit_text,
                            'success_message' => $success_message
                        ]);
                    else: 
                        // Inline form generation
                    ?>
                    <form id="<?php echo esc_attr($unique_form_id); ?>" 
                          class="hph-form hph-form--<?php echo esc_attr($form_type); ?>" 
                          method="post" 
                          action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>"
                          data-route-type="<?php echo esc_attr($route_type); ?>"
                          data-form-context="section_form">
                        
                        <!-- Hidden Fields -->
                        <?php wp_nonce_field($nonce_action, $nonce_field); ?>
                        <input type="hidden" name="action" value="<?php echo esc_attr($ajax_action); ?>">
                        <input type="hidden" name="route_type" value="<?php echo esc_attr($route_type); ?>">
                        <input type="hidden" name="form_type" value="<?php echo esc_attr($form_type); ?>">
                        <input type="hidden" name="form_template" value="<?php echo esc_attr($form_template); ?>">
                        <input type="hidden" name="source_url" value="<?php echo esc_url(get_permalink()); ?>">
                        <input type="hidden" name="source_page" value="<?php echo esc_attr(get_the_title()); ?>">
                        
                        <!-- Form Fields -->
                        <div class="hph-form-fields">
                            <?php if (!empty($form_fields)): ?>
                                <?php foreach ($form_fields as $field): 
                                    $field_id = $unique_form_id . '_' . $field['name'];
                                    $field_classes = array('hph-form-group');
                                    if (!empty($field['width'])) {
                                        $field_classes[] = 'hph-form-col--' . $field['width'];
                                    }
                                    if ($form_style === 'floating-labels') {
                                        $field_classes[] = 'hph-form-floating';
                                    }
                                ?>
                                
                                <div class="<?php echo esc_attr(implode(' ', $field_classes)); ?>">
                                    
                                    <?php if ($field['type'] === 'checkbox' || $field['type'] === 'radio'): ?>
                                        
                                        <!-- Checkbox/Radio -->
                                        <div class="hph-form-check">
                                            <input type="<?php echo esc_attr($field['type']); ?>"
                                                   id="<?php echo esc_attr($field_id); ?>"
                                                   name="<?php echo esc_attr($field['name']); ?>"
                                                   class="hph-form-check-input"
                                                   value="<?php echo esc_attr($field['value'] ?? '1'); ?>"
                                                   <?php echo !empty($field['required']) ? 'required' : ''; ?>>
                                            <label for="<?php echo esc_attr($field_id); ?>" class="hph-form-check-label">
                                                <?php echo esc_html($field['label']); ?>
                                            </label>
                                        </div>
                                        
                                    <?php else: ?>
                                        
                                        <!-- Standard Field -->
                                        <label for="<?php echo esc_attr($field_id); ?>" 
                                               class="hph-form-label <?php echo !empty($field['required']) ? 'hph-form-label--required' : ''; ?>">
                                            <?php echo esc_html($field['label']); ?>
                                        </label>
                                        
                                        <?php if ($field['type'] === 'textarea'): ?>
                                            
                                            <textarea id="<?php echo esc_attr($field_id); ?>"
                                                      name="<?php echo esc_attr($field['name']); ?>"
                                                      class="hph-form-textarea"
                                                      rows="<?php echo esc_attr($field['rows'] ?? 4); ?>"
                                                      placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                                                      <?php echo !empty($field['required']) ? 'required' : ''; ?>></textarea>
                                                      
                                        <?php elseif ($field['type'] === 'select'): ?>
                                            
                                            <div class="hph-select-wrapper">
                                                <select id="<?php echo esc_attr($field_id); ?>"
                                                        name="<?php echo esc_attr($field['name']); ?>"
                                                        class="hph-form-select"
                                                        <?php echo !empty($field['required']) ? 'required' : ''; ?>>
                                                    <option value="">Choose...</option>
                                                    <?php foreach ($field['options'] as $value => $label): ?>
                                                    <option value="<?php echo esc_attr($value); ?>">
                                                        <?php echo esc_html($label); ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            
                                        <?php else: ?>
                                            
                                            <input type="<?php echo esc_attr($field['type']); ?>"
                                                   id="<?php echo esc_attr($field_id); ?>"
                                                   name="<?php echo esc_attr($field['name']); ?>"
                                                   class="hph-form-input"
                                                   placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                                                   <?php echo !empty($field['required']) ? 'required' : ''; ?>>
                                                   
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($field['help'])): ?>
                                        <span class="hph-form-text"><?php echo esc_html($field['help']); ?></span>
                                        <?php endif; ?>
                                        
                                    <?php endif; ?>
                                    
                                </div>
                                
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="hph-form-buttons">
                            <button type="submit" class="hph-btn hph-btn--<?php echo esc_attr($submit_style); ?> hph-btn--lg">
                                <span class="hph-btn-text"><?php echo esc_html($submit_text); ?></span>
                                <span class="hph-btn-loading" style="display: none;">
                                    Sending...
                                </span>
                            </button>
                        </div>
                        
                        <!-- Messages -->
                        <div class="hph-form-messages">
                            <div class="hph-form-success-message" style="display: none;"></div>
                            <div class="hph-form-error-message" style="display: none;"></div>
                        </div>
                        
                    </form>
                    <?php endif; // End template part vs inline form ?>
                </div>
            </div>
            
            <?php else: // form-left ?>
            
            <!-- Form Column -->
            <div class="hph-form-section__form">
                <div class="hph-form-wrapper hph-form-wrapper--<?php echo esc_attr($form_style); ?>">
                    <?php 
                    // Check if we should use a template part or inline form (left layout)
                    if (!empty($use_template_part) && !empty($form_template)): 
                        // Use the dedicated template part with proper arguments
                        get_template_part('template-parts/forms/' . $form_template, null, [
                            'variant' => $form_style,
                            'css_classes' => 'hph-form--' . $form_type,
                            'form_id' => $unique_form_id . '_left',
                            'submit_text' => $submit_text,
                            'success_message' => $success_message
                        ]);
                    else: 
                        // Inline form generation (left layout)
                    ?>
                    <form id="<?php echo esc_attr($unique_form_id . '_left'); ?>" 
                          class="hph-form hph-form--<?php echo esc_attr($form_type); ?>" 
                          method="post" 
                          action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>"
                          data-route-type="<?php echo esc_attr($route_type); ?>"
                          data-form-context="section_form">
                        
                        <!-- Hidden Fields -->
                        <?php wp_nonce_field($nonce_action, $nonce_field); ?>
                        <input type="hidden" name="action" value="<?php echo esc_attr($ajax_action); ?>">
                        <input type="hidden" name="route_type" value="<?php echo esc_attr($route_type); ?>">
                        <input type="hidden" name="form_type" value="<?php echo esc_attr($form_type); ?>">
                        <input type="hidden" name="form_template" value="<?php echo esc_attr($form_template); ?>">
                        <input type="hidden" name="source_url" value="<?php echo esc_url(get_permalink()); ?>">
                        <input type="hidden" name="source_page" value="<?php echo esc_attr(get_the_title()); ?>">
                        
                        <!-- Form Fields -->
                        <div class="hph-form-fields">
                            <?php if (!empty($form_fields)): ?>
                                <?php foreach ($form_fields as $field): 
                                    $field_id = $unique_form_id . '_left_' . $field['name'];
                                    $field_classes = array('hph-form-group');
                                    if (!empty($field['width'])) {
                                        $field_classes[] = 'hph-form-col--' . $field['width'];
                                    }
                                ?>
                                
                                <div class="<?php echo esc_attr(implode(' ', $field_classes)); ?>">
                                    
                                    <label for="<?php echo esc_attr($field_id); ?>" 
                                           class="hph-form-label <?php echo !empty($field['required']) ? 'hph-form-label--required' : ''; ?>">
                                        <?php echo esc_html($field['label']); ?>
                                    </label>
                                    
                                    <?php if ($field['type'] === 'textarea'): ?>
                                        <textarea id="<?php echo esc_attr($field_id); ?>"
                                                  name="<?php echo esc_attr($field['name']); ?>"
                                                  class="hph-form-textarea"
                                                  rows="<?php echo esc_attr($field['rows'] ?? 4); ?>"
                                                  placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                                                  <?php echo !empty($field['required']) ? 'required' : ''; ?>></textarea>
                                    <?php elseif ($field['type'] === 'select'): ?>
                                        <select id="<?php echo esc_attr($field_id); ?>"
                                                name="<?php echo esc_attr($field['name']); ?>"
                                                class="hph-form-select"
                                                <?php echo !empty($field['required']) ? 'required' : ''; ?>>
                                            <option value="">Choose...</option>
                                            <?php foreach ($field['options'] as $value => $label): ?>
                                            <option value="<?php echo esc_attr($value); ?>">
                                                <?php echo esc_html($label); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php else: ?>
                                        <input type="<?php echo esc_attr($field['type']); ?>"
                                               id="<?php echo esc_attr($field_id); ?>"
                                               name="<?php echo esc_attr($field['name']); ?>"
                                               class="hph-form-input"
                                               placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                                               <?php echo !empty($field['required']) ? 'required' : ''; ?>>
                                    <?php endif; ?>
                                    
                                </div>
                                
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="hph-form-buttons">
                            <button type="submit" class="hph-btn hph-btn--<?php echo esc_attr($submit_style); ?> hph-btn--lg">
                                <span class="hph-btn-text"><?php echo esc_html($submit_text); ?></span>
                                <span class="hph-btn-loading" style="display: none;">Sending...</span>
                            </button>
                        </div>
                        
                        <!-- Messages -->
                        <div class="hph-form-messages">
                            <div class="hph-form-success-message" style="display: none;"></div>
                            <div class="hph-form-error-message" style="display: none;"></div>
                        </div>
                        
                    </form>
                    <?php endif; // End template part vs inline form (left layout) ?>
                </div>
            </div>
            
            <!-- Content Column -->
            <div class="hph-form-section__content">
                <?php 
                // Content Section (duplicate for left layout)
                if ($badge || $headline || $subheadline || $content || !empty($features)): ?>
                <div class="hph-form-content">
                    <?php if ($badge): ?>
                    <div class="hph-badge">
                        <span class="hph-badge-text"><?php echo esc_html($badge); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($headline): ?>
                    <h2 class="hph-form-headline"><?php echo esc_html($headline); ?></h2>
                    <?php endif; ?>
                    
                    <?php if ($subheadline): ?>
                    <p class="hph-form-subheadline"><?php echo esc_html($subheadline); ?></p>
                    <?php endif; ?>
                    
                    <?php if ($content): ?>
                    <div class="hph-form-content-text">
                        <?php echo wp_kses_post($content); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($content_type === 'features' && !empty($features)): ?>
                    <div class="hph-features-list">
                        <?php foreach ($features as $feature): ?>
                        <div class="hph-feature-item">
                            <?php if (!empty($feature['icon'])): ?>
                            <div class="hph-feature-icon">
                                <i class="<?php echo esc_attr($feature['icon']); ?>"></i>
                            </div>
                            <?php endif; ?>
                            <div class="hph-feature-content">
                                <?php if (!empty($feature['title'])): ?>
                                <h4 class="hph-feature-title"><?php echo esc_html($feature['title']); ?></h4>
                                <?php endif; ?>
                                <?php if (!empty($feature['description'])): ?>
                                <p class="hph-feature-description"><?php echo esc_html($feature['description']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <?php endif; ?>
            
        </div>
        
        <?php elseif ($style === 'centered'): ?>
        <!-- ============================================
             CENTERED LAYOUT
             ============================================ -->
        <div class="hph-form-section__centered">
            
            <!-- Header Content -->
            <?php if ($badge || $headline || $subheadline || $content): ?>
            <div class="hph-form-section__header">
                <?php if ($badge): ?>
                <div class="hph-badge">
                    <span class="hph-badge-text"><?php echo esc_html($badge); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if ($headline): ?>
                <h2 class="hph-form-headline"><?php echo esc_html($headline); ?></h2>
                <?php endif; ?>
                
                <?php if ($subheadline): ?>
                <p class="hph-form-subheadline"><?php echo esc_html($subheadline); ?></p>
                <?php endif; ?>
                
                <?php if ($content): ?>
                <div class="hph-form-content-text">
                    <?php echo wp_kses_post($content); ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Form -->
            <div class="hph-form-section__form-centered">
                <div class="hph-form-wrapper hph-form-wrapper--<?php echo esc_attr($form_style); ?>">
                    <?php 
                    // Check if we should use a template part or inline form (centered layout)
                    if (!empty($use_template_part) && !empty($form_template)): 
                        // Use the dedicated template part with proper arguments
                        get_template_part('template-parts/forms/' . $form_template, null, [
                            'variant' => $form_style,
                            'css_classes' => 'hph-form--' . $form_type,
                            'form_id' => $unique_form_id,
                            'submit_text' => $submit_text,
                            'success_message' => $success_message
                        ]);
                    else: 
                        // Inline form generation (centered layout)
                    ?>
                    <form id="<?php echo esc_attr($unique_form_id); ?>" 
                          class="hph-form hph-form--<?php echo esc_attr($form_type); ?>" 
                          method="post" 
                          action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>"
                          data-route-type="<?php echo esc_attr($route_type); ?>"
                          data-form-context="section_form">
                        
                        <!-- Hidden Fields -->
                        <?php wp_nonce_field($nonce_action, $nonce_field); ?>
                        <input type="hidden" name="action" value="<?php echo esc_attr($ajax_action); ?>">
                        <input type="hidden" name="route_type" value="<?php echo esc_attr($route_type); ?>">
                        <input type="hidden" name="form_type" value="<?php echo esc_attr($form_type); ?>">
                        <input type="hidden" name="form_template" value="<?php echo esc_attr($form_template); ?>">
                        <input type="hidden" name="source_url" value="<?php echo esc_url(get_permalink()); ?>">
                        <input type="hidden" name="source_page" value="<?php echo esc_attr(get_the_title()); ?>">
                        
                        <!-- Form Fields -->
                        <div class="hph-form-fields">
                            <?php if (!empty($form_fields)): ?>
                                <?php foreach ($form_fields as $field): 
                                    $field_id = $unique_form_id . '_' . $field['name'];
                                    $field_classes = array('hph-form-group');
                                    if (!empty($field['width'])) {
                                        $field_classes[] = 'hph-form-col--' . $field['width'];
                                    }
                                ?>
                                
                                <div class="<?php echo esc_attr(implode(' ', $field_classes)); ?>">
                                    
                                    <label for="<?php echo esc_attr($field_id); ?>" 
                                           class="hph-form-label <?php echo !empty($field['required']) ? 'hph-form-label--required' : ''; ?>">
                                        <?php echo esc_html($field['label']); ?>
                                    </label>
                                    
                                    <?php if ($field['type'] === 'textarea'): ?>
                                        <textarea id="<?php echo esc_attr($field_id); ?>"
                                                  name="<?php echo esc_attr($field['name']); ?>"
                                                  class="hph-form-textarea"
                                                  rows="<?php echo esc_attr($field['rows'] ?? 4); ?>"
                                                  placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                                                  <?php echo !empty($field['required']) ? 'required' : ''; ?>></textarea>
                                    <?php elseif ($field['type'] === 'select'): ?>
                                        <select id="<?php echo esc_attr($field_id); ?>"
                                                name="<?php echo esc_attr($field['name']); ?>"
                                                class="hph-form-select"
                                                <?php echo !empty($field['required']) ? 'required' : ''; ?>>
                                            <option value="">Choose...</option>
                                            <?php foreach ($field['options'] as $value => $label): ?>
                                            <option value="<?php echo esc_attr($value); ?>">
                                                <?php echo esc_html($label); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php else: ?>
                                        <input type="<?php echo esc_attr($field['type']); ?>"
                                               id="<?php echo esc_attr($field_id); ?>"
                                               name="<?php echo esc_attr($field['name']); ?>"
                                               class="hph-form-input"
                                               placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                                               <?php echo !empty($field['required']) ? 'required' : ''; ?>>
                                    <?php endif; ?>
                                    
                                </div>
                                
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="hph-form-buttons">
                            <button type="submit" class="hph-btn hph-btn--<?php echo esc_attr($submit_style); ?> hph-btn--lg">
                                <span class="hph-btn-text"><?php echo esc_html($submit_text); ?></span>
                                <span class="hph-btn-loading" style="display: none;">Sending...</span>
                            </button>
                        </div>
                        
                        <!-- Messages -->
                        <div class="hph-form-messages">
                            <div class="hph-form-success-message" style="display: none;"></div>
                            <div class="hph-form-error-message" style="display: none;"></div>
                        </div>
                        
                    </form>
                    <?php endif; // End template part vs inline form (centered layout) ?>
                </div>
            </div>
            
        </div>
        
        <?php elseif ($style === 'full-width'): ?>
        <!-- ============================================
             FULL WIDTH LAYOUT
             ============================================ -->
        <div class="hph-form-section__full">
            
            <?php if ($layout === 'form-top'): ?>
            
            <!-- Form First -->
            <div class="hph-form-section__form-full">
                <div class="hph-form-wrapper hph-form-wrapper--horizontal">
                    <?php include 'partials/form-element.php'; ?>
                </div>
            </div>
            
            <!-- Content Below -->
            <?php if ($content_type !== 'none'): ?>
            <div class="hph-form-section__content-full">
                <?php include 'partials/form-content.php'; ?>
            </div>
            <?php endif; ?>
            
            <?php else: // form-bottom ?>
            
            <!-- Content First -->
            <div class="hph-form-section__content-full">
                <?php include 'partials/form-content.php'; ?>
            </div>
            
            <!-- Form Below -->
            <div class="hph-form-section__form-full">
                <div class="hph-form-wrapper hph-form-wrapper--horizontal">
                    <?php include 'partials/form-element.php'; ?>
                </div>
            </div>
            
            <?php endif; ?>
            
        </div>
        
        <?php elseif ($style === 'card'): ?>
        <!-- ============================================
             CARD LAYOUT (3 Column Grid)
             ============================================ -->
        <div class="hph-form-section__cards">
            
            <!-- Left Content Card -->
            <div class="hph-form-section__card">
                <?php if ($content_type === 'agent-card'): ?>
                    <?php include 'partials/agent-card.php'; ?>
                <?php else: ?>
                    <?php include 'partials/form-content.php'; ?>
                <?php endif; ?>
            </div>
            
            <!-- Center Form Card -->
            <div class="hph-form-section__card hph-form-section__card--primary">
                <div class="hph-form-wrapper hph-form-wrapper--card">
                    <?php include 'partials/form-header.php'; ?>
                    <?php include 'partials/form-element.php'; ?>
                </div>
            </div>
            
            <!-- Right Content Card -->
            <div class="hph-form-section__card">
                <?php if ($content_type === 'features'): ?>
                    <?php include 'partials/features-list.php'; ?>
                <?php elseif ($content_type === 'testimonial'): ?>
                    <?php include 'partials/testimonial-card.php'; ?>
                <?php else: ?>
                    <?php include 'partials/form-content.php'; ?>
                <?php endif; ?>
            </div>
            
        </div>
        
        <?php elseif ($style === 'overlay'): ?>
        <!-- ============================================
             OVERLAY LAYOUT (Form over content/image)
             ============================================ -->
        <div class="hph-form-section__overlay">
            
            <!-- Background Content/Media -->
            <div class="hph-form-section__background">
                <?php if ($content_type === 'image' && $media_url): ?>
                    <img src="<?php echo esc_url($media_url); ?>" alt="<?php echo esc_attr($media_caption); ?>">
                <?php elseif ($content_type === 'video' && $media_url): ?>
                    <video autoplay muted loop playsinline>
                        <source src="<?php echo esc_url($media_url); ?>" type="video/mp4">
                    </video>
                <?php elseif ($content_type === 'map'): ?>
                    <div class="hph-map-embed" id="form-map-<?php echo esc_attr($unique_form_id); ?>"></div>
                <?php else: ?>
                    <div class="hph-form-section__background-content">
                        <?php include 'partials/form-content.php'; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Floating Form -->
            <div class="hph-form-section__floating">
                <div class="hph-form-wrapper hph-form-wrapper--floating">
                    <?php include 'partials/form-header.php'; ?>
                    <?php include 'partials/form-element.php'; ?>
                </div>
            </div>
            
        </div>
        <?php endif; ?>
        
    </div>
</section>


<!-- Enhanced CSS -->
<style>
/* ================================================
   FORM SECTION BASE STYLES
   ================================================ */
.hph-form-section {
    position: relative;
    width: 100%;
    overflow: hidden;
}

/* Padding Variations */
.hph-form-section--padding-none { padding: 0; }
.hph-form-section--padding-sm { padding: 2rem 0; }
.hph-form-section--padding-md { padding: 3rem 0; }
.hph-form-section--padding-lg { padding: 4rem 0; }
.hph-form-section--padding-xl { padding: 5rem 0; }
.hph-form-section--padding-2xl { padding: 6rem 0; }

/* Background Variations */
.hph-form-section--bg-white { background: var(--hph-white); }
.hph-form-section--bg-gray { background: var(--hph-gray-50); }
.hph-form-section--bg-primary { background: var(--hph-primary); color: white; }
.hph-form-section--bg-gradient { 
    background: linear-gradient(135deg, var(--hph-primary) 0%, var(--hph-primary-dark) 100%); 
    color: white;
}
.hph-form-section--bg-pattern {
    background-color: var(--hph-gray-50);
    background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%2351bae0' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
.hph-form-section--bg-image {
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}

/* Container Variations */
.hph-container { 
    width: 100%; 
    margin: 0 auto; 
    padding: 0 1.5rem; 
}
.hph-container--full { max-width: 100%; padding: 0; }
.hph-container--wide { max-width: 1400px; }
.hph-container--default { max-width: 1200px; }
.hph-container--narrow { max-width: 800px; }

/* Shadow & Border Radius */
.hph-form-section--shadow { box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
.hph-form-section--rounded { border-radius: 1rem; }

/* Section Overlay */
.hph-section-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1;
}

.hph-form-section > .hph-container {
    position: relative;
    z-index: 2;
}

/* ================================================
   LAYOUT: SPLIT
   ================================================ */
.hph-form-section__grid--split {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
}

.hph-form-section__content {
    padding: 2rem;
}

.hph-form-section__form {
    padding: 2rem;
}

/* ================================================
   LAYOUT: CENTERED
   ================================================ */
.hph-form-section__centered {
    text-align: center;
    max-width: 800px;
    margin: 0 auto;
}

.hph-form-section__header {
    margin-bottom: 3rem;
}

.hph-form-section__form-centered {
    max-width: 600px;
    margin: 0 auto;
}

/* ================================================
   LAYOUT: FULL WIDTH
   ================================================ */
.hph-form-section__full {
    width: 100%;
}

.hph-form-section__form-full {
    margin-bottom: 3rem;
}

.hph-form-wrapper--horizontal .hph-form-fields {
    display: flex;
    gap: 1rem;
    align-items: flex-end;
}

.hph-form-wrapper--horizontal .hph-form-group {
    flex: 1;
    margin-bottom: 0;
}

.hph-form-wrapper--horizontal .hph-form-buttons {
    flex-shrink: 0;
    margin-top: 0;
}

/* ================================================
   LAYOUT: CARDS
   ================================================ */
.hph-form-section__cards {
    display: grid;
    grid-template-columns: 1fr 1.5fr 1fr;
    gap: 2rem;
    align-items: start;
}

.hph-form-section__card {
    background: white;
    padding: 2rem;
    border-radius: 1rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.hph-form-section__card--primary {
    transform: scale(1.05);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

/* ================================================
   LAYOUT: OVERLAY
   ================================================ */
.hph-form-section__overlay {
    position: relative;
    min-height: 600px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
}

.hph-form-section__background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 0;
}

.hph-form-section__background img,
.hph-form-section__background video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.hph-form-section__floating {
    position: relative;
    z-index: 10;
    max-width: 500px;
    margin-right: 5%;
}

.hph-form-wrapper--floating {
    background: white;
    padding: 3rem;
    border-radius: 1rem;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

/* ================================================
   FORM WRAPPER STYLES
   ================================================ */
.hph-form-wrapper {
    background: white;
    padding: 2.5rem;
    border-radius: 1rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    border: 1px solid var(--hph-gray-200, #e5e7eb);
}

.hph-form-wrapper--minimal {
    background: transparent;
    padding: 0;
    box-shadow: none;
    border: none;
}

.hph-form-wrapper--bordered {
    box-shadow: none;
    border: 2px solid var(--hph-primary);
}

.hph-form-wrapper--card {
    background: white;
    padding: 2rem;
    border-radius: 0.75rem;
}

/* ================================================
   FORM FIELDS & ELEMENTS
   ================================================ */
.hph-form-fields {
    display: grid;
    gap: 1.5rem;
    grid-template-columns: 1fr 1fr;
}

.hph-form-group {
    display: flex;
    flex-direction: column;
}

.hph-form-col--full {
    grid-column: span 2;
}

.hph-form-col--half {
    grid-column: span 1;
}

.hph-form-label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 14px;
}

.hph-form-label--required:after {
    content: ' *';
    color: #dc2626;
}

.hph-form-input,
.hph-form-textarea,
.hph-form-select {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 16px;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    background: #fff;
    box-sizing: border-box;
}

.hph-form-input:focus,
.hph-form-textarea:focus,
.hph-form-select:focus {
    outline: none;
    border-color: var(--hph-primary);
    box-shadow: var(--hph-input-focus-shadow);
}

.hph-form-textarea {
    resize: vertical;
    min-height: 120px;
}

.hph-form-buttons {
    margin-top: 2rem;
    text-align: center;
}

.hph-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 0.5rem;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease-in-out;
    text-decoration: none;
    min-width: 120px;
}

.hph-btn--primary {
    background: var(--hph-primary);
    color: #fff;
}

.hph-btn--primary:hover:not(:disabled) {
    background: #2563eb;
}

.hph-btn--primary:disabled {
    background: #9ca3af;
    cursor: not-allowed;
}

.hph-btn--lg {
    padding: 1rem 2rem;
    font-size: 18px;
}

.hph-form-messages {
    margin-top: 1rem;
}

.hph-form-success-message,
.hph-form-error-message {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    font-size: 14px;
    margin-top: 1rem;
}

.hph-form-success-message {
    background-color: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #166534;
}

.hph-form-error-message {
    background-color: #fef2f2;
    border: 1px solid #fecaca;
    color: #dc2626;
}

/* ================================================
   FEATURES LIST STYLES
   ================================================ */
.hph-features-list {
    display: grid;
    gap: 1.5rem;
    margin-top: 2rem;
}

.hph-feature-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.hph-feature-icon {
    flex-shrink: 0;
    width: 3rem;
    height: 3rem;
    background: var(--hph-primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.hph-feature-content {
    flex: 1;
}

.hph-feature-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 0.5rem 0;
}

.hph-feature-description {
    color: #6b7280;
    margin: 0;
    line-height: 1.5;
}

/* ================================================
   CONTENT STYLES
   ================================================ */
.hph-form-content {
    padding: 2rem;
}

.hph-form-headline {
    font-size: 2.25rem;
    font-weight: 700;
    line-height: 1.2;
    color: #1f2937;
    margin: 0 0 1rem 0;
}

.hph-form-subheadline {
    font-size: 1.25rem;
    font-weight: 500;
    color: #6b7280;
    margin: 0 0 1rem 0;
}

.hph-form-content-text {
    font-size: 1rem;
    line-height: 1.6;
    color: #4b5563;
    margin-bottom: 1.5rem;
}

.hph-badge {
    margin-bottom: 1rem;
}

.hph-badge-text {
    display: inline-block;
    padding: 0.5rem 1rem;
    background: #dbeafe;
    color: #1e40af;
    border-radius: 1.25rem;
    font-size: 0.875rem;
    font-weight: 600;
}

/* ================================================
   RESPONSIVE DESIGN
   ================================================ */
@media (max-width: 1024px) {
    .hph-form-section__cards {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .hph-form-section__card--primary {
        transform: none;
        order: -1;
    }
}

@media (max-width: 768px) {
    .hph-form-section__grid--split {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .hph-form-fields {
        grid-template-columns: 1fr;
    }
    
    .hph-form-col--half,
    .hph-form-col--full {
        grid-column: span 1;
    }
    
    .hph-form-section__overlay {
        justify-content: center;
        padding: 2rem;
    }
    
    .hph-form-section__floating {
        max-width: 100%;
        margin: 0;
    }
    
    .hph-form-wrapper--horizontal .hph-form-fields {
        flex-direction: column;
    }
    
    .hph-form-wrapper--horizontal .hph-form-group {
        width: 100%;
    }
    
    .hph-form-wrapper {
        padding: 1.5rem;
    }
    
    .hph-form-content {
        padding: 1.5rem;
    }
    
    .hph-form-headline {
        font-size: 1.875rem;
    }
}
    
    .hph-form-section__card--primary {
        transform: none;
        order: -1;
    }
}

@media (max-width: 768px) {
    .hph-form-section__grid--split {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .hph-form-section__overlay {
        justify-content: center;
        padding: 2rem;
    }
    
    .hph-form-section__floating {
        max-width: 100%;
        margin: 0;
    }
    
    .hph-form-wrapper--horizontal .hph-form-fields {
        flex-direction: column;
    }
    
    .hph-form-wrapper--horizontal .hph-form-group {
        width: 100%;
    }
    
    .hph-form-wrapper {
        padding: 1.5rem;
    }
}

@media (max-width: 480px) {
    .hph-form-section--padding-xl { padding: 3rem 0; }
    .hph-form-section--padding-2xl { padding: 4rem 0; }
    
    .hph-container {
        padding: 0 1rem;
    }
}

/* ================================================
   ANIMATIONS
   ================================================ */
.hph-animate {
    opacity: 0;
    transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.hph-animate.hph-in-view {
    opacity: 1;
}

.hph-animate--fade-in.hph-in-view {
    opacity: 1;
}

.hph-animate--slide-up {
    transform: translateY(30px);
}

.hph-animate--slide-up.hph-in-view {
    transform: translateY(0);
}

.hph-animate--zoom-in {
    transform: scale(0.95);
}

.hph-animate--zoom-in.hph-in-view {
    transform: scale(1);
}

/* Loading Spinner */
@keyframes spin {
    to { transform: rotate(360deg); }
}

.hph-spinner {
    animation: spin 1s linear infinite;
}
</style>

<!-- Enhanced JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all forms
    const forms = document.querySelectorAll('.hph-form');
    
    forms.forEach(form => {
        // Form submission handler
        form.addEventListener('submit', handleFormSubmit);
        
        // Real-time validation
        const inputs = form.querySelectorAll('.hph-form-input, .hph-form-textarea, .hph-form-select');
        inputs.forEach(input => {
            input.addEventListener('blur', () => validateField(input));
            input.addEventListener('input', () => clearValidation(input));
        });
        
        // File input enhancement
        const fileInputs = form.querySelectorAll('.hph-form-file-input');
        fileInputs.forEach(input => {
            input.addEventListener('change', function() {
                const label = this.nextElementSibling;
                const fileName = this.files[0]?.name || 'Choose file';
                label.textContent = fileName;
            });
        });
    });
    
    // Form submission
    async function handleFormSubmit(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const btnText = submitBtn.querySelector('.hph-btn-text');
        const btnLoading = submitBtn.querySelector('.hph-btn-loading');
        const successMsg = form.querySelector('.hph-form-success-message');
        const errorMsg = form.querySelector('.hph-form-error-message');
        
        // Validate all fields
        const isValid = validateForm(form);
        if (!isValid) return;
        
        // Show loading state
        submitBtn.disabled = true;
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline-flex';
        
        // Clear previous messages
        successMsg.style.display = 'none';
        errorMsg.style.display = 'none';
        
        try {
            console.log('Submitting form data:', Object.fromEntries(formData));
            
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            console.log('Form response:', data);
            
            if (data.success) {
                successMsg.textContent = data.data?.message || 'Thank you! We\'ll get back to you soon.';
                successMsg.style.display = 'block';
                form.reset();
                
                // Trigger success event
                form.dispatchEvent(new CustomEvent('formSuccess', { detail: data }));
            } else {
                errorMsg.textContent = data.data?.message || 'An error occurred. Please try again.';
                errorMsg.style.display = 'block';
            }
        } catch (error) {
            errorMsg.textContent = 'Network error. Please check your connection and try again.';
            errorMsg.style.display = 'block';
        } finally {
            submitBtn.disabled = false;
            btnText.style.display = 'inline';
            btnLoading.style.display = 'none';
        }
    }
    
    // Field validation
    function validateField(field) {
        const isValid = field.checkValidity();
        
        if (!isValid) {
            field.classList.add('hph-form-input--error');
            field.classList.remove('hph-form-input--success');
            
            // Add error message if not exists
            if (!field.nextElementSibling?.classList.contains('hph-form-error-message')) {
                const errorMsg = document.createElement('span');
                errorMsg.className = 'hph-form-error-message';
                errorMsg.textContent = field.validationMessage;
                field.parentNode.insertBefore(errorMsg, field.nextSibling);
            }
        } else if (field.value) {
            field.classList.add('hph-form-input--success');
            field.classList.remove('hph-form-input--error');
            
            // Remove error message
            const errorMsg = field.nextElementSibling;
            if (errorMsg?.classList.contains('hph-form-error-message')) {
                errorMsg.remove();
            }
        }
        
        return isValid;
    }
    
    // Clear validation
    function clearValidation(field) {
        field.classList.remove('hph-form-input--error', 'hph-form-input--success');
        const errorMsg = field.nextElementSibling;
        if (errorMsg?.classList.contains('hph-form-error-message')) {
            errorMsg.remove();
        }
    }
    
    // Validate entire form
    function validateForm(form) {
        const fields = form.querySelectorAll('[required]');
        let isValid = true;
        
        fields.forEach(field => {
            if (!validateField(field)) {
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    // Intersection Observer for animations
    const animatedElements = document.querySelectorAll('.hph-animate');
    
    if (animatedElements.length > 0) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('hph-in-view');
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '50px'
        });
        
        animatedElements.forEach(el => observer.observe(el));
    }
});
</script>
