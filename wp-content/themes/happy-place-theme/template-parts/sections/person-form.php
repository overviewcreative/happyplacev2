<?php
/**
 * Template Part: Form with Person Info Section
 * 
 * A flexible section that combines a form with a person/agent info card
 * Perfect for contact forms, consultation requests, or agent-specific inquiries
 * 
 * @param array $args Configuration options for the section
 */

// Parse arguments with defaults
$args = wp_parse_args($args ?? [], [
    // Section settings
    'style' => 'split', // 'split', 'overlay', 'stacked', 'sidebar'
    'theme' => 'light', // 'light', 'dark', 'white', 'primary', 'gradient'
    'layout' => 'form-left', // 'form-left', 'form-right', 'form-top', 'form-bottom'
    'padding' => 'xl', // 'sm', 'md', 'lg', 'xl', 'xxl'
    'container' => 'default', // 'narrow', 'default', 'wide', 'full'
    'background_image' => '',
    'background_pattern' => '', // 'dots', 'lines', 'waves', 'circles'
    'section_id' => 'contact-form-section',
    'section_class' => '',
    
    // Section content
    'headline' => '',
    'subheadline' => '',
    'content' => '',
    'content_alignment' => 'left', // 'left', 'center', 'right'
    
    // Form settings
    'form_id' => 'contact-form',
    'form_title' => 'Get in Touch',
    'form_subtitle' => '',
    'form_style' => 'modern', // 'simple', 'modern', 'floating', 'material'
    'form_theme' => 'white', // 'white', 'light', 'transparent'
    'show_form_icon' => true,
    'form_icon' => 'fas fa-envelope',
    'form_fields' => [], // Array of field configurations
    'form_button_text' => 'Send Message',
    'form_button_style' => 'primary',
    'form_button_size' => 'lg',
    'form_button_full_width' => false,
    'form_ajax' => true,
    'form_action' => '',
    'form_method' => 'POST',
    'show_required_note' => true,
    'success_message' => 'Thank you for your message! We\'ll be in touch soon.',
    'error_message' => 'There was an error submitting the form. Please try again.',
    
    // Person/Agent info settings
    'person_data' => [], // Array with person details
    'person_card_style' => 'elevated', // 'flat', 'elevated', 'bordered', 'gradient'
    'show_person_image' => true,
    'person_image_style' => 'circle', // 'circle', 'rounded', 'square'
    'person_image_size' => 'lg', // 'sm', 'md', 'lg', 'xl'
    'show_person_name' => true,
    'show_person_title' => true,
    'show_person_bio' => true,
    'show_person_contact' => true,
    'show_person_social' => true,
    'show_person_stats' => true,
    'show_person_badges' => true,
    'show_person_specialties' => true,
    'show_person_cta' => true,
    'person_cta_text' => 'View Full Profile',
    'person_cta_style' => 'outline',
    
    // Animation settings
    'animation' => true,
    'animation_type' => 'fade-up', // 'fade', 'fade-up', 'fade-down', 'slide', 'zoom'
    'animation_delay' => 0,
    'stagger_animation' => true,
]);

// Default form fields if none provided
if (empty($args['form_fields'])) {
    $args['form_fields'] = [
        [
            'type' => 'text',
            'name' => 'name',
            'label' => 'Your Name',
            'placeholder' => 'John Smith',
            'required' => true,
            'width' => 'half',
            'icon' => 'fas fa-user'
        ],
        [
            'type' => 'email',
            'name' => 'email',
            'label' => 'Email Address',
            'placeholder' => 'john@example.com',
            'required' => true,
            'width' => 'half',
            'icon' => 'fas fa-envelope'
        ],
        [
            'type' => 'tel',
            'name' => 'phone',
            'label' => 'Phone Number',
            'placeholder' => '(302) 555-0123',
            'required' => false,
            'width' => 'half',
            'icon' => 'fas fa-phone'
        ],
        [
            'type' => 'select',
            'name' => 'interest',
            'label' => 'I\'m Interested In',
            'required' => true,
            'width' => 'half',
            'icon' => 'fas fa-home',
            'options' => [
                '' => 'Please Select',
                'buying' => 'Buying a Home',
                'selling' => 'Selling a Home',
                'both' => 'Both Buying & Selling',
                'investing' => 'Investment Properties',
                'consultation' => 'Free Consultation'
            ]
        ],
        [
            'type' => 'select',
            'name' => 'timeline',
            'label' => 'Timeline',
            'required' => false,
            'width' => 'full',
            'icon' => 'fas fa-calendar',
            'options' => [
                '' => 'No Rush',
                'asap' => 'As Soon As Possible',
                '1-3months' => 'Within 1-3 Months',
                '3-6months' => 'Within 3-6 Months',
                '6-12months' => 'Within 6-12 Months',
                'exploring' => 'Just Exploring Options'
            ]
        ],
        [
            'type' => 'textarea',
            'name' => 'message',
            'label' => 'How Can We Help?',
            'placeholder' => 'Tell us about your real estate goals...',
            'required' => false,
            'width' => 'full',
            'rows' => 4,
            'icon' => 'fas fa-comment-dots'
        ]
    ];
}

// Default person data if none provided
if (empty($args['person_data'])) {
    $args['person_data'] = [
        'name' => 'Dustin Parker',
        'title' => 'Founder & Lead Agent',
        'image' => function_exists('hph_get_image_url') ? hph_get_image_url('agents/dustin-parker.jpg') : '',
        'bio' => 'As a ninth-generation Sussex County native and former educator, I bring deep local knowledge and a teaching approach to every real estate transaction. Let me help you find your happy place!',
        'phone' => '(302) 555-0123',
        'email' => 'dustin@theparkergroup.com',
        'social' => [
            'facebook' => '#',
            'instagram' => '#',
            'linkedin' => '#'
        ],
        'stats' => [
            ['label' => 'Homes Sold', 'value' => '500+'],
            ['label' => 'Years Experience', 'value' => '9+'],
            ['label' => 'Client Satisfaction', 'value' => '5.0★'],
            ['label' => 'Response Time', 'value' => '<1hr']
        ],
        'badges' => [
            'Top Producer 2025',
            'Good Neighbor Award',
            'Delaware Today Top Agent'
        ],
        'specialties' => [
            'First-Time Buyers',
            'Luxury Homes',
            'Investment Properties',
            'Waterfront Properties'
        ],
        'profile_url' => '/agents/dustin-parker'
    ];
}

// Build section classes
$section_classes = [
    'form-person-section',
    'section-style-' . esc_attr($args['style']),
    'section-theme-' . esc_attr($args['theme']),
    'section-layout-' . esc_attr($args['layout']),
    'section-padding-' . esc_attr($args['padding']),
    'container-' . esc_attr($args['container'])
];

if ($args['animation']) {
    $section_classes[] = 'has-animation';
}

if ($args['background_image']) {
    $section_classes[] = 'has-bg-image';
}

if ($args['background_pattern']) {
    $section_classes[] = 'has-pattern pattern-' . esc_attr($args['background_pattern']);
}

if ($args['section_class']) {
    $section_classes[] = esc_attr($args['section_class']);
}

$person = $args['person_data'];
?>

<section id="<?php echo esc_attr($args['section_id']); ?>" 
         class="<?php echo implode(' ', $section_classes); ?>"
         <?php if ($args['background_image']) : ?>
         style="background-image: url('<?php echo esc_url($args['background_image']); ?>');"
         <?php endif; ?>>
    
    <div class="container <?php echo esc_attr($args['container']); ?>">
        
        <?php if ($args['headline'] || $args['subheadline'] || $args['content']) : ?>
        <div class="section-header">
            <?php if ($args['headline']) : ?>
                <h2 class="section-title"><?php echo esc_html($args['headline']); ?></h2>
            <?php endif; ?>
            
            <?php if ($args['subheadline']) : ?>
                <p class="section-subtitle"><?php echo esc_html($args['subheadline']); ?></p>
            <?php endif; ?>
            
            <?php if ($args['content']) : ?>
                <div class="section-description">
                    <?php echo wp_kses_post($args['content']); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <div class="row">
            
            <!-- Form Column -->
            <div class="col-lg-6 <?php echo ($args['layout'] === 'form-right') ? 'order-lg-2' : ''; ?>">
                
                <div class="contact-form-wrapper">
                    
                    <?php if ($args['form_title'] || $args['form_subtitle']) : ?>
                    <div class="form-header">
                        <?php if ($args['show_form_icon'] && $args['form_icon']) : ?>
                            <i class="<?php echo esc_attr($args['form_icon']); ?> form-icon"></i>
                        <?php endif; ?>
                        
                        <?php if ($args['form_title']) : ?>
                            <h3><?php echo esc_html($args['form_title']); ?></h3>
                        <?php endif; ?>
                        
                        <?php if ($args['form_subtitle']) : ?>
                            <p><?php echo esc_html($args['form_subtitle']); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <form id="<?php echo esc_attr($args['form_id']); ?>" 
                          class="contact-form <?php echo $args['form_ajax'] ? 'ajax-form' : ''; ?>"
                          action="<?php echo esc_url($args['form_action'] ?: admin_url('admin-ajax.php')); ?>"
                          method="<?php echo esc_attr($args['form_method']); ?>">
                        
                        <?php if ($args['form_ajax']) : ?>
                            <input type="hidden" name="action" value="hph_route_form">
                            <input type="hidden" name="route_type" value="lead_capture">
                            <input type="hidden" name="form_type" value="person_contact">
                            <?php wp_nonce_field('hph_route_form_nonce', 'nonce'); ?>
                        <?php endif; ?>
                        
                        <div class="row">
                            <?php foreach ($args['form_fields'] as $field) : 
                                $field = wp_parse_args($field, [
                                    'type' => 'text',
                                    'name' => '',
                                    'label' => '',
                                    'placeholder' => '',
                                    'required' => false,
                                    'width' => 'full',
                                    'icon' => '',
                                    'options' => [],
                                    'rows' => 4,
                                ]);
                                
                                $col_class = ($field['width'] === 'half') ? 'col-md-6' : 'col-12';
                            ?>
                            
                            <div class="<?php echo esc_attr($col_class); ?> form-group">
                                <?php if ($field['label']) : ?>
                                    <label for="<?php echo esc_attr($field['name']); ?>">
                                        <?php echo esc_html($field['label']); ?>
                                        <?php if ($field['required']) : ?>
                                            <span class="required">*</span>
                                        <?php endif; ?>
                                    </label>
                                <?php endif; ?>
                                
                                <?php if ($field['type'] === 'textarea') : ?>
                                    <textarea 
                                        name="<?php echo esc_attr($field['name']); ?>"
                                        id="<?php echo esc_attr($field['name']); ?>"
                                        class="hph-form-textarea"
                                        placeholder="<?php echo esc_attr($field['placeholder']); ?>"
                                        rows="<?php echo esc_attr($field['rows']); ?>"
                                        <?php echo $field['required'] ? 'required' : ''; ?>
                                    ></textarea>
                                
                                <?php elseif ($field['type'] === 'select') : ?>
                                    <select 
                                        name="<?php echo esc_attr($field['name']); ?>"
                                        id="<?php echo esc_attr($field['name']); ?>"
                                        class="hph-form-select"
                                        <?php echo $field['required'] ? 'required' : ''; ?>>
                                        <?php foreach ($field['options'] as $value => $label) : ?>
                                            <option value="<?php echo esc_attr($value); ?>">
                                                <?php echo esc_html($label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                
                                <?php else : ?>
                                    <input 
                                        type="<?php echo esc_attr($field['type']); ?>"
                                        name="<?php echo esc_attr($field['name']); ?>"
                                        id="<?php echo esc_attr($field['name']); ?>"
                                        class="hph-form-input"
                                        placeholder="<?php echo esc_attr($field['placeholder']); ?>"
                                        <?php echo $field['required'] ? 'required' : ''; ?>>
                                <?php endif; ?>
                            </div>
                            
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if ($args['show_required_note']) : ?>
                            <p class="form-note"><span class="required">*</span> Required fields</p>
                        <?php endif; ?>
                        
                        <button type="submit"
                                class="hph-btn hph-btn-<?php echo esc_attr($args['form_button_style']); ?>
                                       hph-btn-<?php echo esc_attr($args['form_button_size']); ?>
                                       <?php echo $args['form_button_full_width'] ? 'hph-btn-block' : ''; ?>">
                            <?php echo esc_html($args['form_button_text']); ?>
                            <i class="fas fa-arrow-right hph-btn-icon"></i>
                        </button>
                        
                        <div class="form-response-messages">
                            <div class="alert alert-success" style="display: none;">
                                <?php echo esc_html($args['success_message']); ?>
                            </div>
                            <div class="alert alert-danger" style="display: none;">
                                <?php echo esc_html($args['error_message']); ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Person Info Column -->
            <div class="col-lg-6 <?php echo ($args['layout'] === 'form-right') ? 'order-lg-1' : ''; ?>">
                
                <div class="team-member-card <?php echo $args['hover_effects'] ? 'has-hover' : ''; ?>">
                    
                    <?php if ($args['show_person_image'] && $person['image']) : ?>
                    <div class="member-image <?php echo esc_attr($args['image_style']); ?>">
                        <img src="<?php echo esc_url($person['image']); ?>" 
                             alt="<?php echo esc_attr($person['name']); ?>">
                        <?php if (!empty($person['badges']) && $args['show_person_badges']) : ?>
                            <span class="badge badge-primary"><?php echo esc_html($person['badges'][0]); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="member-info">
                        <?php if ($args['show_person_name'] && $person['name']) : ?>
                            <h3 class="member-name"><?php echo esc_html($person['name']); ?></h3>
                        <?php endif; ?>
                        
                        <?php if ($args['show_person_title'] && $person['title']) : ?>
                            <p class="member-title"><?php echo esc_html($person['title']); ?></p>
                        <?php endif; ?>
                        
                        <?php if ($args['show_person_bio'] && $person['bio']) : ?>
                            <div class="member-bio">
                                <?php echo wp_kses_post($person['bio']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($args['show_person_contact'] && ($person['phone'] || $person['email'])) : ?>
                        <div class="member-contact">
                            <?php if ($person['phone']) : ?>
                                <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9]/', '', $person['phone'])); ?>" 
                                   class="contact-link">
                                    <i class="fas fa-phone"></i>
                                    <?php echo esc_html($person['phone']); ?>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($person['email']) : ?>
                                <a href="mailto:<?php echo esc_attr($person['email']); ?>" 
                                   class="contact-link">
                                    <i class="fas fa-envelope"></i>
                                    <?php echo esc_html($person['email']); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($args['show_person_social'] && !empty($person['social'])) : ?>
                        <div class="member-social">
                            <?php foreach ($person['social'] as $platform => $url) : ?>
                                <a href="<?php echo esc_url($url); ?>" 
                                   class="social-link" 
                                   target="_blank" 
                                   rel="noopener noreferrer">
                                    <i class="fab fa-<?php echo esc_attr($platform); ?>"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($args['show_person_stats'] && !empty($person['stats'])) : ?>
                        <div class="member-stats row">
                            <?php foreach ($person['stats'] as $stat) : ?>
                                <div class="col-6 stat-item">
                                    <div class="stat-value"><?php echo esc_html($stat['value']); ?></div>
                                    <div class="stat-label"><?php echo esc_html($stat['label']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($args['show_person_specialties'] && !empty($person['specialties'])) : ?>
                        <div class="member-specialties">
                            <h4>Specialties</h4>
                            <div class="specialty-tags">
                                <?php foreach ($person['specialties'] as $specialty) : ?>
                                    <span class="tag"><?php echo esc_html($specialty); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($args['show_person_cta'] && $person['profile_url']) : ?>
                            <a href="<?php echo esc_url($person['profile_url']); ?>" 
                               class="hph-btn hph-btn-outline-primary">
                                <?php echo esc_html($args['person_cta_text']); ?>
                                <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</section>
