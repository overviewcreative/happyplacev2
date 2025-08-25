<?php
/**
 * Base Single CTA - Call-to-action sections for single posts
 *
 * @package HappyPlaceTheme
 */

$cta_args = wp_parse_args($args ?? [], [
    'post_id' => get_the_ID(),
    'post_type' => get_post_type(),
    'style' => 'primary', // primary, secondary, gradient, custom
    'layout' => 'centered', // centered, split, minimal
    'container_class' => 'hph-container',
    'background_color' => '',
    'text_color' => '',
    'show_cta' => true,
    'headline' => '',
    'subheadline' => '',
    'buttons' => [],
    'custom_content' => ''
]);

// Don't show CTA if disabled
if (!$cta_args['show_cta']) {
    return;
}

// Get post-type specific CTA content
if (empty($cta_args['headline']) || empty($cta_args['buttons'])) {
    switch ($cta_args['post_type']) {
        case 'listing':
            // Check listing availability
            $listing_status = function_exists('hpt_get_listing_status') ? 
                            hpt_get_listing_status($cta_args['post_id']) : 
                            get_field('listing_status', $cta_args['post_id']);
            
            if ($listing_status === 'sold' || $listing_status === 'off_market') {
                $cta_args['headline'] = __('This Property Has Been Sold', 'happy-place-theme');
                $cta_args['subheadline'] = __('Browse our current listings or contact us to learn about new properties coming to market.', 'happy-place-theme');
                $cta_args['style'] = 'secondary';
                $cta_args['buttons'] = [
                    [
                        'text' => __('View All Listings', 'happy-place-theme'),
                        'url' => get_post_type_archive_link('listing'),
                        'style' => 'primary',
                        'icon' => 'fa-home'
                    ],
                    [
                        'text' => __('Contact Us', 'happy-place-theme'),
                        'url' => home_url('/contact'),
                        'style' => 'outline',
                        'icon' => 'fa-envelope'
                    ]
                ];
            } else {
                $cta_args['headline'] = __('Interested in This Property?', 'happy-place-theme');
                $cta_args['subheadline'] = __('Ready to schedule a tour or learn more? Our experienced Delaware team is here to help.', 'happy-place-theme');
                
                // Get agent phone
                $agent_id = function_exists('hpt_get_listing_agent') ? 
                          hpt_get_listing_agent($cta_args['post_id']) : 
                          get_field('listing_agent', $cta_args['post_id']);
                $agent_phone = null;
                
                if ($agent_id && function_exists('hpt_get_agent_phone')) {
                    $agent_phone = hpt_get_agent_phone($agent_id);
                }
                
                if (!$agent_phone) {
                    $agent_phone = get_theme_mod('business_phone', '(302) 217-6692');
                }
                
                $cta_args['buttons'] = [
                    [
                        'text' => __('Schedule Tour', 'happy-place-theme'),
                        'url' => '#contact',
                        'style' => 'white',
                        'icon' => 'fa-calendar-alt'
                    ],
                    [
                        'text' => sprintf(__('Call %s', 'happy-place-theme'), $agent_phone),
                        'url' => 'tel:' . preg_replace('/[^0-9]/', '', $agent_phone),
                        'style' => 'outline-white',
                        'icon' => 'fa-phone'
                    ]
                ];
            }
            break;
            
        case 'agent':
            $cta_args['headline'] = __('Ready to Work Together?', 'happy-place-theme');
            $cta_args['subheadline'] = __('Let\'s discuss your real estate goals and how I can help you achieve them.', 'happy-place-theme');
            
            // Get agent contact info
            $agent_phone = function_exists('hpt_get_agent_phone') ? 
                         hpt_get_agent_phone($cta_args['post_id']) : 
                         get_field('agent_phone', $cta_args['post_id']);
            $agent_email = function_exists('hpt_get_agent_email') ? 
                         hpt_get_agent_email($cta_args['post_id']) : 
                         get_field('agent_email', $cta_args['post_id']);
            
            $cta_args['buttons'] = [];
            
            if ($agent_phone) {
                $cta_args['buttons'][] = [
                    'text' => sprintf(__('Call %s', 'happy-place-theme'), $agent_phone),
                    'url' => 'tel:' . preg_replace('/[^0-9]/', '', $agent_phone),
                    'style' => 'white',
                    'icon' => 'fa-phone'
                ];
            }
            
            if ($agent_email) {
                $cta_args['buttons'][] = [
                    'text' => __('Send Email', 'happy-place-theme'),
                    'url' => 'mailto:' . $agent_email,
                    'style' => 'outline-white',
                    'icon' => 'fa-envelope'
                ];
            }
            
            $cta_args['buttons'][] = [
                'text' => __('View Listings', 'happy-place-theme'),
                'url' => add_query_arg('agent', $cta_args['post_id'], get_post_type_archive_link('listing')),
                'style' => 'outline-white',
                'icon' => 'fa-home'
            ];
            break;
            
        default:
            $cta_args['headline'] = __('Ready to Get Started?', 'happy-place-theme');
            $cta_args['subheadline'] = __('Contact us today to learn more about our services.', 'happy-place-theme');
            $cta_args['buttons'] = [
                [
                    'text' => __('Contact Us', 'happy-place-theme'),
                    'url' => home_url('/contact'),
                    'style' => 'white',
                    'icon' => 'fa-envelope'
                ],
                [
                    'text' => __('Learn More', 'happy-place-theme'),
                    'url' => home_url('/about'),
                    'style' => 'outline-white',
                    'icon' => 'fa-info-circle'
                ]
            ];
            break;
    }
}

// Build CTA classes using utility-first approach
$cta_classes = [
    'hph-single-cta',
    'hph-py-2xl',
    'hph-animate-fade-in-up'
];

// Style variations using utility classes
$style_classes = [
    'primary' => ['hph-bg-primary-600', 'hph-text-white'],
    'secondary' => ['hph-bg-gray-100', 'hph-text-gray-900'],
    'gradient' => ['hph-bg-gradient-to-r', 'hph-from-primary-600', 'hph-to-primary-800', 'hph-text-white'],
    'custom' => ['hph-bg-gray-900', 'hph-text-white']
];

$cta_classes = array_merge($cta_classes, $style_classes[$cta_args['style']] ?? $style_classes['primary']);

// Add post type specific classes
$cta_classes[] = 'hph-cta-' . $cta_args['post_type'];

$cta_styles = [];
if (!empty($cta_args['background_color'])) {
    $cta_styles[] = 'background-color: ' . esc_attr($cta_args['background_color']);
}
if (!empty($cta_args['text_color'])) {
    $cta_styles[] = 'color: ' . esc_attr($cta_args['text_color']);
}
?>

<section class="<?php echo esc_attr(implode(' ', $cta_classes)); ?>"
         <?php if (!empty($cta_styles)) : ?>
         style="<?php echo esc_attr(implode('; ', $cta_styles)); ?>"
         <?php endif; ?>>
    
    <div class="<?php echo esc_attr($cta_args['container_class']); ?>">
        <?php
        // Build content layout classes based on layout setting
        $content_classes = ['hph-text-center', 'hph-space-y-lg'];
        
        if ($cta_args['layout'] === 'split') {
            $content_classes = [
                'hph-grid', 
                'hph-grid-cols-1', 
                'md:hph-grid-cols-2', 
                'hph-gap-xl', 
                'hph-items-center'
            ];
        } elseif ($cta_args['layout'] === 'minimal') {
            $content_classes = ['hph-max-w-2xl', 'hph-mx-auto', 'hph-text-center', 'hph-space-y-md'];
        } else {
            $content_classes = ['hph-max-w-4xl', 'hph-mx-auto', 'hph-text-center', 'hph-space-y-lg'];
        }
        ?>
        
        <div class="<?php echo esc_attr(implode(' ', $content_classes)); ?>">
            
            <?php if (!empty($cta_args['custom_content'])) : ?>
                
                <div class="hph-prose hph-prose-lg hph-mx-auto">
                    <?php echo wp_kses_post($cta_args['custom_content']); ?>
                </div>
                
            <?php else : ?>
                
                <div class="hph-space-y-md">
                    <?php if (!empty($cta_args['headline'])) : ?>
                        <h2 class="hph-text-3xl md:hph-text-4xl hph-font-bold hph-leading-tight">
                            <?php echo esc_html($cta_args['headline']); ?>
                        </h2>
                    <?php endif; ?>
                    
                    <?php if (!empty($cta_args['subheadline'])) : ?>
                        <p class="hph-text-lg hph-leading-relaxed hph-opacity-90">
                            <?php echo esc_html($cta_args['subheadline']); ?>
                        </p>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($cta_args['buttons'])) : ?>
                    <div class="hph-flex hph-flex-col sm:hph-flex-row hph-items-center hph-justify-center hph-gap-md hph-pt-lg">
                        <?php foreach ($cta_args['buttons'] as $button) : ?>
                            <?php
                            $button = wp_parse_args($button, [
                                'text' => '',
                                'url' => '#',
                                'style' => 'primary',
                                'icon' => '',
                                'target' => '_self',
                                'size' => 'lg'
                            ]);
                            
                            // Build button classes based on style
                            $button_classes = [
                                'hph-inline-flex',
                                'hph-items-center',
                                'hph-gap-sm',
                                'hph-px-xl',
                                'hph-py-md',
                                'hph-font-semibold',
                                'hph-rounded-lg',
                                'hph-transition-all',
                                'hph-duration-300'
                            ];
                            
                            // Style variations
                            switch ($button['style']) {
                                case 'white':
                                    $button_classes = array_merge($button_classes, [
                                        'hph-bg-white',
                                        'hph-text-gray-900',
                                        'hph-hover:bg-gray-100',
                                        'hph-hover:scale-105',
                                        'hph-shadow-md'
                                    ]);
                                    break;
                                case 'outline-white':
                                    $button_classes = array_merge($button_classes, [
                                        'hph-border-2',
                                        'hph-border-white',
                                        'hph-text-white',
                                        'hph-hover:bg-white',
                                        'hph-hover:text-gray-900',
                                        'hph-hover:scale-105'
                                    ]);
                                    break;
                                case 'outline':
                                    $button_classes = array_merge($button_classes, [
                                        'hph-border-2',
                                        'hph-border-current',
                                        'hph-hover:bg-current',
                                        'hph-hover:text-white',
                                        'hph-hover:scale-105'
                                    ]);
                                    break;
                                default: // primary
                                    $button_classes = array_merge($button_classes, [
                                        'hph-bg-primary-600',
                                        'hph-text-white',
                                        'hph-hover:bg-primary-700',
                                        'hph-hover:scale-105',
                                        'hph-shadow-md'
                                    ]);
                                    break;
                            }
                            
                            if ($button['size'] === 'xl') {
                                $button_classes[] = 'hph-px-2xl';
                                $button_classes[] = 'hph-py-lg';
                                $button_classes[] = 'hph-text-lg';
                            }
                            ?>
                            <a href="<?php echo esc_url($button['url']); ?>" 
                               class="<?php echo esc_attr(implode(' ', $button_classes)); ?>"
                               target="<?php echo esc_attr($button['target']); ?>">
                                <?php if (!empty($button['icon'])) : ?>
                                    <i class="fas <?php echo esc_attr($button['icon']); ?>"></i>
                                <?php endif; ?>
                                <?php echo esc_html($button['text']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
            <?php endif; ?>
            
        </div>
    </div>
    
</section>