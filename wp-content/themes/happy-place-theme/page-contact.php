<?php
/**
 * Template Name: Contact Page
 * Description: Contact page with office locations, contact form, and map
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

get_header(); ?>

<main id="main-content" class="hph-site-main">
    
    <?php
    // ============================================
    // Contact Hero Section
    // ============================================
    get_template_part('template-parts/sections/hero', null, [
        'background' => 'gradient',
        'theme' => 'primary',
        'padding' => 'xl',
        'alignment' => 'center',
        'headline' => 'Get in Touch',
        'subheadline' => 'We\'re here to help you find your happy place',
        'content' => 'Visit one of our three Delaware offices or reach out today to start your real estate journey with our team of 50+ experienced agents.',
        'content_width' => 'narrow',
        'animation' => true,
        'actions' => [
            [
                'text' => 'Call Us Now',
                'url' => 'tel:+13022176692',
                'style' => 'primary',
                'icon' => 'fas fa-phone'
            ],
            [
                'text' => 'Send a Message',
                'url' => '#contact-form',
                'style' => 'outline-white',
                'icon' => 'fas fa-envelope'
            ]
        ],
        'section_id' => 'contact-hero'
    ]);
    ?>

    <?php
    // ============================================
    // Office Locations Section
    // ============================================
    ?>
    <section id="office-locations" class="hph-section hph-bg-light hph-py-24">
        <div class="hph-container">
            
            <!-- Section Header -->
            <div class="hph-text-center hph-mb-16">
                <div class="hph-badge hph-badge--primary hph-mb-4">Our Locations</div>
                <h2 class="hph-heading-2 hph-mb-6">Visit Our Delaware Offices</h2>
                <p class="hph-text-xl hph-text-muted hph-max-w-2xl hph-mx-auto">
                    Stop by any of our three convenient locations throughout Delaware for personalized service and local expertise.
                </p>
            </div>
            
            <!-- Office Cards Grid -->
            <div class="hph-grid hph-grid-cols-1 lg:hph-grid-cols-3 hph-gap-8">
                
                <!-- Georgetown Office -->
                <div class="hph-card hph-card--elevated hph-bg-white hph-border-0 hph-shadow-lg hph-rounded-xl hph-overflow-hidden hph-group hph-h-full">
                    
                    <!-- Office Photo -->
                    <div class="hph-card-image hph-relative hph-h-48 hph-overflow-hidden">
                        <img 
                            src="<?php echo get_template_directory_uri(); ?>/assets/images/office-georgetown.jpg" 
                            alt="Happy Place Real Estate Georgetown Office" 
                            class="hph-w-full hph-h-full hph-object-cover hph-transition-transform hph-duration-300 group-hover:hph-scale-105"
                            loading="lazy"
                        >
                        <div class="hph-absolute hph-top-4 hph-left-4">
                            <span class="hph-badge hph-badge--primary hph-badge--sm">Main Office</span>
                        </div>
                    </div>
                    
                    <!-- Office Info -->
                    <div class="hph-card-content hph-p-6">
                        <div class="hph-flex hph-items-start hph-gap-3 hph-mb-4">
                            <div class="hph-flex-shrink-0 hph-w-10 hph-h-10 hph-bg-primary/10 hph-rounded-lg hph-flex hph-items-center hph-justify-center">
                                <i class="fas fa-map-marker-alt hph-text-primary"></i>
                            </div>
                            <div>
                                <h3 class="hph-heading-4 hph-mb-2">Georgetown Office</h3>
                                <div class="hph-text-muted hph-mb-3">
                                    <p class="hph-mb-1">673 N. Bedford St.</p>
                                    <p>Georgetown, DE 19947</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Contact Info -->
                        <div class="hph-space-y-3 hph-mb-6">
                            <div class="hph-flex hph-items-center hph-gap-3">
                                <i class="fas fa-phone hph-text-primary hph-w-4"></i>
                                <a href="tel:+13022176692" class="hph-text-primary hover:hph-text-primary/80 hph-transition-colors">
                                    (302) 217-6692
                                </a>
                            </div>
                            <div class="hph-flex hph-items-center hph-gap-3">
                                <i class="fas fa-clock hph-text-primary hph-w-4"></i>
                                <span class="hph-text-muted">Mon-Fri: 9am-6pm, Sat-Sun: 10am-4pm</span>
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="hph-flex hph-gap-3">
                            <a href="https://maps.google.com/?q=673+N+Bedford+St+Georgetown+DE" 
                               target="_blank" 
                               rel="noopener"
                               class="hph-btn hph-btn--primary hph-btn--sm hph-flex-1">
                                <i class="fas fa-route hph-mr-2"></i>
                                Directions
                            </a>
                            <a href="tel:+13022176692" 
                               class="hph-btn hph-btn--outline hph-btn--sm hph-flex-1">
                                <i class="fas fa-phone hph-mr-2"></i>
                                Call
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Milford Office -->
                <div class="hph-card hph-card--elevated hph-bg-white hph-border-0 hph-shadow-lg hph-rounded-xl hph-overflow-hidden hph-group hph-h-full">
                    
                    <!-- Office Photo -->
                    <div class="hph-card-image hph-relative hph-h-48 hph-overflow-hidden">
                        <img 
                            src="<?php echo get_template_directory_uri(); ?>/assets/images/office-milford.jpg" 
                            alt="Happy Place Real Estate Milford Office" 
                            class="hph-w-full hph-h-full hph-object-cover hph-transition-transform hph-duration-300 group-hover:hph-scale-105"
                            loading="lazy"
                        >
                    </div>
                    
                    <!-- Office Info -->
                    <div class="hph-card-content hph-p-6">
                        <div class="hph-flex hph-items-start hph-gap-3 hph-mb-4">
                            <div class="hph-flex-shrink-0 hph-w-10 hph-h-10 hph-bg-primary/10 hph-rounded-lg hph-flex hph-items-center hph-justify-center">
                                <i class="fas fa-map-marker-alt hph-text-primary"></i>
                            </div>
                            <div>
                                <h3 class="hph-heading-4 hph-mb-2">Milford Office</h3>
                                <div class="hph-text-muted hph-mb-3">
                                    <p class="hph-mb-1">48 N. Walnut St.</p>
                                    <p>Milford, DE 19963</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Contact Info -->
                        <div class="hph-space-y-3 hph-mb-6">
                            <div class="hph-flex hph-items-center hph-gap-3">
                                <i class="fas fa-phone hph-text-primary hph-w-4"></i>
                                <a href="tel:+13022176692" class="hph-text-primary hover:hph-text-primary/80 hph-transition-colors">
                                    (302) 217-6692
                                </a>
                            </div>
                            <div class="hph-flex hph-items-center hph-gap-3">
                                <i class="fas fa-clock hph-text-primary hph-w-4"></i>
                                <span class="hph-text-muted">Mon-Fri: 9am-6pm, Sat-Sun: 10am-4pm</span>
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="hph-flex hph-gap-3">
                            <a href="https://maps.google.com/?q=48+N+Walnut+St+Milford+DE" 
                               target="_blank" 
                               rel="noopener"
                               class="hph-btn hph-btn--primary hph-btn--sm hph-flex-1">
                                <i class="fas fa-route hph-mr-2"></i>
                                Directions
                            </a>
                            <a href="tel:+13022176692" 
                               class="hph-btn hph-btn--outline hph-btn--sm hph-flex-1">
                                <i class="fas fa-phone hph-mr-2"></i>
                                Call
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Middletown Office -->
                <div class="hph-card hph-card--elevated hph-bg-white hph-border-0 hph-shadow-lg hph-rounded-xl hph-overflow-hidden hph-group hph-h-full">
                    
                    <!-- Office Photo -->
                    <div class="hph-card-image hph-relative hph-h-48 hph-overflow-hidden">
                        <img 
                            src="<?php echo get_template_directory_uri(); ?>/assets/images/office-middletown.jpg" 
                            alt="Happy Place Real Estate Middletown Office" 
                            class="hph-w-full hph-h-full hph-object-cover hph-transition-transform hph-duration-300 group-hover:hph-scale-105"
                            loading="lazy"
                        >
                    </div>
                    
                    <!-- Office Info -->
                    <div class="hph-card-content hph-p-6">
                        <div class="hph-flex hph-items-start hph-gap-3 hph-mb-4">
                            <div class="hph-flex-shrink-0 hph-w-10 hph-h-10 hph-bg-primary/10 hph-rounded-lg hph-flex hph-items-center hph-justify-center">
                                <i class="fas fa-map-marker-alt hph-text-primary"></i>
                            </div>
                            <div>
                                <h3 class="hph-heading-4 hph-mb-2">Middletown Office</h3>
                                <div class="hph-text-muted hph-mb-3">
                                    <p class="hph-mb-1">108 Patriot Dr.</p>
                                    <p>Middletown, DE 19709</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Contact Info -->
                        <div class="hph-space-y-3 hph-mb-6">
                            <div class="hph-flex hph-items-center hph-gap-3">
                                <i class="fas fa-phone hph-text-primary hph-w-4"></i>
                                <a href="tel:+13022176692" class="hph-text-primary hover:hph-text-primary/80 hph-transition-colors">
                                    (302) 217-6692
                                </a>
                            </div>
                            <div class="hph-flex hph-items-center hph-gap-3">
                                <i class="fas fa-clock hph-text-primary hph-w-4"></i>
                                <span class="hph-text-muted">Mon-Fri: 9am-6pm, Sat-Sun: 10am-4pm</span>
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="hph-flex hph-gap-3">
                            <a href="https://maps.google.com/?q=108+Patriot+Dr+Middletown+DE" 
                               target="_blank" 
                               rel="noopener"
                               class="hph-btn hph-btn--primary hph-btn--sm hph-flex-1">
                                <i class="fas fa-route hph-mr-2"></i>
                                Directions
                            </a>
                            <a href="tel:+13022176692" 
                               class="hph-btn hph-btn--outline hph-btn--sm hph-flex-1">
                                <i class="fas fa-phone hph-mr-2"></i>
                                Call
                            </a>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </section>

    <?php
    // ============================================
    // Contact Form Section
    // ============================================
    get_template_part('template-parts/sections/form', null, [
        'layout' => 'left-form',
        'background' => 'white',
        'padding' => 'xl',
        'headline' => 'Send Us a Message',
        'subheadline' => 'Ready to start your real estate journey?',
        'content' => 'Whether you\'re buying your first home, selling a property, or just have questions about the Delaware real estate market, our team of 50+ agents is here to provide expert guidance with no pressure.',
        'form_title' => 'Contact Us',
        'form_fields' => [
            [
                'type' => 'text',
                'name' => 'first_name',
                'label' => 'First Name',
                'required' => true,
                'placeholder' => 'Your first name'
            ],
            [
                'type' => 'text',
                'name' => 'last_name',
                'label' => 'Last Name',
                'required' => true,
                'placeholder' => 'Your last name'
            ],
            [
                'type' => 'email',
                'name' => 'email',
                'label' => 'Email Address',
                'required' => true,
                'placeholder' => 'your.email@example.com'
            ],
            [
                'type' => 'tel',
                'name' => 'phone',
                'label' => 'Phone Number',
                'placeholder' => '(555) 123-4567'
            ],
            [
                'type' => 'select',
                'name' => 'inquiry_type',
                'label' => 'How can we help you?',
                'options' => [
                    'buying' => 'I\'m looking to buy a home',
                    'selling' => 'I\'m thinking about selling',
                    'both' => 'Both buying and selling',
                    'investment' => 'Investment properties',
                    'market_info' => 'Market information',
                    'other' => 'Other questions'
                ]
            ],
            [
                'type' => 'textarea',
                'name' => 'message',
                'label' => 'Tell us more',
                'placeholder' => 'Share any additional details about your real estate needs...',
                'rows' => 4
            ],
            [
                'type' => 'hidden',
                'name' => 'subject',
                'value' => 'Contact Form Inquiry'
            ]
        ],
        'submit_text' => 'Send Message',
        'features' => [
            [
                'icon' => 'fas fa-comments',
                'title' => 'Free Consultation',
                'content' => 'No cost, no pressure - just honest advice from our experienced team'
            ],
            [
                'icon' => 'fas fa-map-marked-alt',
                'title' => 'Local Market Expertise',
                'content' => 'Deep knowledge of Delaware communities and neighborhoods'
            ],
            [
                'icon' => 'fas fa-handshake',
                'title' => 'No Pressure Approach',
                'content' => 'We listen to your needs first, then provide tailored solutions'
            ],
            [
                'icon' => 'fas fa-users',
                'title' => '50+ Agents Ready',
                'content' => 'Our full team of experts working together for your success'
            ]
        ],
        'section_id' => 'contact-form'
    ]);
    ?>

    <?php
    // ============================================
    // Map Section with Office Locations
    // ============================================
    
    // Get Mapbox token
    $mapbox_token = '';
    if (function_exists('hp_get_mapbox_token')) {
        $mapbox_token = hp_get_mapbox_token();
    } elseif (defined('HP_MAPBOX_ACCESS_TOKEN')) {
        $mapbox_token = HP_MAPBOX_ACCESS_TOKEN;
    }
    
    if (!empty($mapbox_token)):
    ?>
    <section id="contact-map" class="hph-section hph-bg-light hph-py-24">
        <div class="hph-container">
            
            <!-- Section Header -->
            <div class="hph-text-center hph-mb-12">
                <div class="hph-badge hph-badge--primary hph-mb-4">Find Us</div>
                <h2 class="hph-heading-2 hph-mb-6">Our Delaware Locations</h2>
                <p class="hph-text-xl hph-text-muted hph-max-w-2xl hph-mx-auto">
                    Convenient locations throughout Delaware to serve you better
                </p>
            </div>
            
            <!-- Map Container -->
            <div class="hph-map-wrapper hph-relative hph-rounded-xl hph-overflow-hidden hph-shadow-lg hph-bg-white">
                <div 
                    id="offices-map" 
                    class="hph-map-container"
                    style="height: 500px; width: 100%;"
                    data-offices='<?php echo json_encode([
                        [
                            'lat' => 38.7745,
                            'lng' => -75.4621,
                            'title' => 'Georgetown Office',
                            'address' => '673 N. Bedford St., Georgetown, DE 19947',
                            'phone' => '(302) 217-6692',
                            'type' => 'main'
                        ],
                        [
                            'lat' => 38.9581,
                            'lng' => -75.4297,
                            'title' => 'Milford Office',
                            'address' => '48 N. Walnut St., Milford, DE 19963',
                            'phone' => '(302) 217-6692',
                            'type' => 'branch'
                        ],
                        [
                            'lat' => 39.4496,
                            'lng' => -75.7161,
                            'title' => 'Middletown Office',
                            'address' => '108 Patriot Dr., Middletown, DE 19709',
                            'phone' => '(302) 217-6692',
                            'type' => 'branch'
                        ]
                    ]); ?>'
                >
                    <div class="hph-map-loading hph-flex hph-items-center hph-justify-center hph-h-full">
                        <div class="hph-loading-spinner hph-w-8 hph-h-8 hph-border-4 hph-border-primary/20 hph-border-t-primary hph-rounded-full hph-animate-spin"></div>
                    </div>
                </div>
            </div>
            
        </div>
    </section>

    <!-- Mapbox Integration -->
    <link href='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css' rel='stylesheet' />
    <script src='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js'></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const mapContainer = document.getElementById('offices-map');
        if (!mapContainer) return;
        
        const offices = JSON.parse(mapContainer.dataset.offices);
        
        // Initialize Mapbox
        mapboxgl.accessToken = '<?php echo esc_js($mapbox_token); ?>';
        
        try {
            // Remove loading indicator
            mapContainer.innerHTML = '';
            
            // Create map centered on Delaware
            const map = new mapboxgl.Map({
                container: 'offices-map',
                style: 'mapbox://styles/mapbox/streets-v12',
                center: [-75.5277, 39.1612], // Center of Delaware
                zoom: 8.5,
                pitch: 0,
                bearing: 0
            });
            
            // Add navigation controls
            map.addControl(new mapboxgl.NavigationControl({
                showCompass: false
            }), 'top-right');
            
            // Add markers for each office
            offices.forEach(function(office) {
                // Create custom marker element with hph- styling
                const markerEl = document.createElement('div');
                markerEl.className = 'hph-office-marker';
                markerEl.style.cssText = `
                    width: 40px;
                    height: 40px;
                    background: linear-gradient(135deg, var(--hph-primary) 0%, var(--hph-primary-dark) 100%);
                    border-radius: 50%;
                    border: 3px solid white;
                    box-shadow: var(--hph-shadow-primary);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    cursor: pointer;
                    transition: all 0.3s ease;
                `;
                markerEl.innerHTML = office.type === 'main' ? 
                    '<i class="fas fa-building" style="color: white; font-size: 16px;"></i>' : 
                    '<i class="fas fa-map-marker-alt" style="color: white; font-size: 16px;"></i>';
                
                // Add hover effect
                markerEl.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.1)';
                    this.style.boxShadow = 'var(--hph-shadow-xl)';
                });
                markerEl.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                    this.style.boxShadow = 'var(--hph-shadow-primary)';
                });
                
                // Create marker
                const marker = new mapboxgl.Marker({
                    element: markerEl
                })
                .setLngLat([office.lng, office.lat])
                .addTo(map);
                
                // Create popup with hph- styled content
                const popup = new mapboxgl.Popup({
                    offset: 25,
                    closeButton: true,
                    className: 'hph-office-popup'
                })
                .setHTML(`
                    <div class="hph-card hph-card--sm hph-bg-white hph-p-4 hph-min-w-64">
                        <div class="hph-flex hph-items-start hph-gap-3 hph-mb-3">
                            <div class="hph-flex-shrink-0 hph-w-8 hph-h-8 hph-bg-primary/10 hph-rounded-lg hph-flex hph-items-center hph-justify-center">
                                <i class="${office.type === 'main' ? 'fas fa-building' : 'fas fa-map-marker-alt'} hph-text-primary hph-text-sm"></i>
                            </div>
                            <div class="hph-flex-1">
                                <h4 class="hph-heading-5 hph-mb-1">${office.title}</h4>
                                ${office.type === 'main' ? '<span class="hph-badge hph-badge--primary hph-badge--xs hph-mb-2">Main Office</span>' : ''}
                                <p class="hph-text-sm hph-text-muted hph-mb-3">${office.address}</p>
                                <div class="hph-flex hph-items-center hph-gap-2 hph-mb-3">
                                    <i class="fas fa-phone hph-text-primary hph-text-xs"></i>
                                    <a href="tel:${office.phone.replace(/[^\d]/g, '')}" class="hph-text-sm hph-text-primary hover:hph-text-primary/80 hph-transition-colors">
                                        ${office.phone}
                                    </a>
                                </div>
                                <div class="hph-flex hph-gap-2">
                                    <a href="https://maps.google.com/?q=${encodeURIComponent(office.address)}" 
                                       target="_blank" 
                                       rel="noopener"
                                       class="hph-btn hph-btn--primary hph-btn--xs hph-flex-1">
                                        <i class="fas fa-route hph-mr-1"></i>
                                        Directions
                                    </a>
                                    <a href="tel:${office.phone.replace(/[^\d]/g, '')}" 
                                       class="hph-btn hph-btn--outline hph-btn--xs hph-flex-1">
                                        <i class="fas fa-phone hph-mr-1"></i>
                                        Call
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
                
                marker.setPopup(popup);
                
                // Show popup on marker click
                markerEl.addEventListener('click', function() {
                    popup.addTo(map);
                });
            });
            
            // Fit map to show all offices
            const bounds = new mapboxgl.LngLatBounds();
            offices.forEach(office => bounds.extend([office.lng, office.lat]));
            map.fitBounds(bounds, { padding: 50 });
            
        } catch (error) {
            console.error('Error initializing map:', error);
            mapContainer.innerHTML = `
                <div class="hph-map-error hph-flex hph-items-center hph-justify-center hph-h-full hph-text-center">
                    <div>
                        <i class="fas fa-exclamation-triangle hph-text-warning hph-text-2xl hph-mb-4"></i>
                        <p class="hph-text-muted">Unable to load map. Please refresh the page.</p>
                    </div>
                </div>
            `;
        }
    });
    </script>
    
    <!-- Custom CSS for popup styling -->
    <style>
    .hph-office-popup .mapboxgl-popup-content {
        padding: 0;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        border: 1px solid var(--hph-gray-200, #e5e7eb);
        max-width: 300px;
    }
    
    .hph-office-popup .mapboxgl-popup-tip {
        border-top-color: white;
    }
    
    .hph-office-popup .mapboxgl-popup-close-button {
        color: var(--hph-gray-400, #9ca3af);
        font-size: 20px;
        right: 8px;
        top: 8px;
        z-index: 10;
    }
    
    .hph-office-popup .mapboxgl-popup-close-button:hover {
        color: var(--hph-gray-600, #4b5563);
    }
    </style>
    
    <?php endif; ?>

    <?php
    // ============================================
    // Why Choose Us Section
    // ============================================
    get_template_part('template-parts/sections/stats', null, [
        'background' => 'white',
        'padding' => 'xl',
        'badge' => 'Why Choose Us',
        'headline' => 'Trusted Delaware Real Estate Experts',
        'subheadline' => 'Our track record speaks for itself',
        'stats' => [
            [
                'number' => '2,755+',
                'label' => 'Homes Sold',
                'icon' => 'fas fa-home'
            ],
            [
                'number' => '$922M+',
                'label' => 'Total Sales Volume',
                'icon' => 'fas fa-chart-line'
            ],
            [
                'number' => '51%',
                'label' => 'Faster Than Average',
                'icon' => 'fas fa-clock'
            ],
            [
                'number' => '50+',
                'label' => 'Expert Agents',
                'icon' => 'fas fa-users'
            ]
        ],
        'columns' => 4,
        'section_id' => 'why-choose-us'
    ]);
    ?>
    
</main>

<?php get_footer(); ?>
