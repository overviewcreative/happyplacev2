<?php
/**
 * Template Name: Section Templates Demo
 * 
 * Comprehensive demonstration page showing all available section template parts
 * with examples, configurations, and usage instructions.
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

get_header();
?>

<style>
/* Demo page specific styles */
.demo-wrapper {
    background: #f1f5f9;
    min-height: 100vh;
}

.demo-section {
    margin: 40px 0;
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.demo-header {
    background: linear-gradient(135deg, #1e293b, #334155);
    color: white;
    padding: 30px 40px;
    position: relative;
}

.demo-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
}

.demo-header > * {
    position: relative;
    z-index: 1;
}

.demo-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.demo-title i {
    font-size: 1.8rem;
    color: #10b981;
}

.demo-description {
    opacity: 0.9;
    line-height: 1.6;
    margin-bottom: 20px;
}

.demo-features {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

.demo-feature-tag {
    background: rgba(16, 185, 129, 0.2);
    color: #10b981;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.demo-content {
    padding: 0;
}

.demo-example {
    border-bottom: 3px solid #f1f5f9;
}

.demo-code {
    background: #1e293b;
    color: #e2e8f0;
    padding: 30px;
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
    overflow-x: auto;
    border-top: 1px solid #e2e8f0;
}

.demo-code pre {
    margin: 0;
    white-space: pre-wrap;
    line-height: 1.6;
}

.demo-code .comment {
    color: #94a3b8;
}

.demo-code .keyword {
    color: #60a5fa;
}

.demo-code .string {
    color: #34d399;
}

.intro-section {
    background: linear-gradient(135deg, var(--hph-primary), var(--hph-secondary));
    color: white;
    padding: 80px 40px;
    text-align: center;
    margin-bottom: 40px;
}

.intro-section h1 {
    font-size: 4rem;
    font-weight: 800;
    margin-bottom: 20px;
}

.intro-section p {
    font-size: 1.4rem;
    opacity: 0.9;
    max-width: 800px;
    margin: 0 auto 40px;
    line-height: 1.5;
}

.template-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    margin-top: 40px;
}

.template-card {
    background: rgba(255,255,255,0.1);
    padding: 30px 20px;
    border-radius: 12px;
    text-align: center;
    backdrop-filter: blur(10px);
    transition: transform 0.3s ease;
}

.template-card:hover {
    transform: translateY(-5px);
}

.template-card i {
    font-size: 3rem;
    margin-bottom: 15px;
    display: block;
}

.template-card h3 {
    margin-bottom: 10px;
    font-size: 1.2rem;
}

.template-card p {
    opacity: 0.8;
    font-size: 0.9rem;
}

.quick-nav {
    background: white;
    padding: 30px;
    border-radius: 12px;
    margin: 40px 0;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.quick-nav h3 {
    color: #1e293b;
    margin-bottom: 20px;
    font-size: 1.5rem;
}

.nav-links {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.nav-link {
    display: block;
    padding: 15px 20px;
    background: #f8fafc;
    color: #334155;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

.nav-link:hover {
    background: #e2e8f0;
    border-left-color: var(--hph-primary);
    transform: translateX(5px);
}

@media (max-width: 768px) {
    .intro-section h1 {
        font-size: 2.5rem;
    }
    
    .demo-header {
        padding: 20px;
    }
    
    .demo-code {
        padding: 20px;
        font-size: 0.8rem;
    }
}
</style>

<div class="demo-wrapper">
    <div class="container mx-auto px-4">
        <!-- Introduction -->
        <div class="intro-section">
            <h1>🧩 Section Templates Demo</h1>
            <p>Explore all available section template parts with live examples, configuration options, and implementation guides.</p>
            
            <div class="template-grid">
                <div class="template-card">
                    <i class="fas fa-rocket"></i>
                    <h3>Hero Sections</h3>
                    <p>Dynamic hero templates with carousels</p>
                </div>
                <div class="template-card">
                    <i class="fas fa-star"></i>
                    <h3>Feature Sections</h3>
                    <p>Highlight key features and benefits</p>
                </div>
                <div class="template-card">
                    <i class="fas fa-users"></i>
                    <h3>Team & Agents</h3>
                    <p>Showcase your professional team</p>
                </div>
                <div class="template-card">
                    <i class="fas fa-home"></i>
                    <h3>Property Displays</h3>
                    <p>Featured listings and property loops</p>
                </div>
                <div class="template-card">
                    <i class="fas fa-comments"></i>
                    <h3>Social Proof</h3>
                    <p>Testimonials and success stories</p>
                </div>
                <div class="template-card">
                    <i class="fas fa-cog"></i>
                    <h3>Interactive Elements</h3>
                    <p>Forms, CTAs, and engagement tools</p>
                </div>
            </div>
        </div>

        <!-- Quick Navigation -->
        <div class="quick-nav">
            <h3>🧭 Jump to Section</h3>
            <div class="nav-links">
                <a href="#hero-sections" class="nav-link">🚀 Hero Sections</a>
                <a href="#features" class="nav-link">🖼️ Image Features</a>
                <a href="#icon-features" class="nav-link">⭐ Icon Features</a>
                <a href="#content-masonry" class="nav-link">🎨 Content & Gallery</a>
                <a href="#team" class="nav-link">👥 Team</a>
                <a href="#properties" class="nav-link">🏠 Properties</a>
                <a href="#testimonials" class="nav-link">💬 Testimonials</a>
                <a href="#forms" class="nav-link">📝 Forms & CTAs</a>
                <a href="#process" class="nav-link">🔄 Process</a>
                <a href="#stats" class="nav-link">📊 Statistics</a>
                <a href="#faq" class="nav-link">❓ FAQ</a>
                <a href="#maps" class="nav-link">🗺️ Maps</a>
            </div>
        </div>

        <!-- Hero Sections -->
        <div id="hero-sections" class="demo-section">
            <div class="demo-header">
                <h2 class="demo-title">
                    <i class="fas fa-rocket"></i>
                    Hero Sections
                </h2>
                <p class="demo-description">
                    Dynamic hero templates including standard heroes and the new carousel system for rotating content. Perfect for creating compelling first impressions.
                </p>
                <div class="demo-features">
                    <span class="demo-feature-tag">Multiple Styles</span>
                    <span class="demo-feature-tag">Carousel Support</span>
                    <span class="demo-feature-tag">Background Options</span>
                    <span class="demo-feature-tag">Call-to-Actions</span>
                </div>
            </div>
            <div class="demo-content">
                <!-- Standard Hero Example -->
                <div class="demo-example">
                    <?php
                    get_template_part('template-parts/sections/hero', null, array(
                        'style' => 'gradient',
                        'theme' => 'primary',
                        'height' => 'md',
                        'alignment' => 'center',
                        'headline' => 'Standard Hero Section',
                        'subheadline' => 'Clean, focused messaging with single call-to-action',
                        'content' => 'Perfect for landing pages, about sections, and focused messaging. Supports gradients, images, and custom themes.',
                        'buttons' => array(
                            array(
                                'text' => 'Explore Features',
                                'url' => '#features',
                                'style' => 'white',
                                'size' => 'lg',
                                'icon' => 'fas fa-arrow-down'
                            )
                        )
                    )); ?>
                </div>

                <!-- Hero Carousel Example -->
                <div class="demo-example">
                    <?php
                    get_template_part('template-parts/sections/hero-carousel', null, array(
                        'autoplay' => true,
                        'autoplay_speed' => 3500,
                        'transition_type' => 'slide',
                        'height' => 'md',
                        'show_progress' => true,
                        'slides' => array(
                            array(
                                'style' => 'gradient',
                                'theme' => 'ocean',
                                'alignment' => 'left',
                                'headline' => 'Hero Carousel Demo',
                                'subheadline' => 'Slide 1: Ocean Theme',
                                'content' => 'Rotate through multiple messages with smooth transitions and full content control.',
                                'buttons' => array(
                                    array('text' => 'Learn More', 'url' => '#', 'style' => 'white')
                                )
                            ),
                            array(
                                'style' => 'gradient',
                                'theme' => 'sunset',
                                'alignment' => 'center',
                                'headline' => 'Dynamic Content',
                                'subheadline' => 'Slide 2: Sunset Theme',
                                'content' => 'Each slide can have completely different themes, content, and calls-to-action.',
                                'buttons' => array(
                                    array('text' => 'Get Started', 'url' => '#', 'style' => 'white')
                                )
                            ),
                            array(
                                'style' => 'gradient',
                                'theme' => 'forest',
                                'alignment' => 'right',
                                'headline' => 'Engaging Experience',
                                'subheadline' => 'Slide 3: Forest Theme',
                                'content' => 'Keep visitors engaged with rotating content that showcases different aspects of your business.',
                                'buttons' => array(
                                    array('text' => 'View Details', 'url' => '#', 'style' => 'white')
                                )
                            )
                        )
                    )); ?>
                </div>

                <div class="demo-code">
                    <pre><code><span class="comment">// Standard Hero</span>
<span class="keyword">get_template_part</span>(<span class="string">'template-parts/sections/hero'</span>, <span class="keyword">null</span>, [
    <span class="string">'style'</span> => <span class="string">'gradient'</span>,
    <span class="string">'theme'</span> => <span class="string">'primary'</span>,
    <span class="string">'height'</span> => <span class="string">'md'</span>,
    <span class="string">'headline'</span> => <span class="string">'Your Compelling Headline'</span>,
    <span class="string">'subheadline'</span> => <span class="string">'Supporting message'</span>,
    <span class="string">'buttons'</span> => [
        [<span class="string">'text'</span> => <span class="string">'Call to Action'</span>, <span class="string">'url'</span> => <span class="string">'/contact/'</span>, <span class="string">'style'</span> => <span class="string">'white'</span>]
    ]
]);

<span class="comment">// Hero Carousel</span>
<span class="keyword">get_template_part</span>(<span class="string">'template-parts/sections/hero-carousel'</span>, <span class="keyword">null</span>, [
    <span class="string">'autoplay'</span> => <span class="keyword">true</span>,
    <span class="string">'transition_type'</span> => <span class="string">'slide'</span>,
    <span class="string">'slides'</span> => [
        [<span class="string">'headline'</span> => <span class="string">'Slide 1'</span>, <span class="string">'theme'</span> => <span class="string">'ocean'</span>],
        [<span class="string">'headline'</span> => <span class="string">'Slide 2'</span>, <span class="string">'theme'</span> => <span class="string">'sunset'</span>]
    ]
]);</code></pre>
                </div>
            </div>
        </div>

        <!-- Image-Based Features Section -->
        <div id="features" class="demo-section">
            <div class="demo-header">
                <h2 class="demo-title">
                    <i class="fas fa-image"></i>
                    Image-Based Features
                </h2>
                <p class="demo-description">
                    Highlight key features with beautiful images, blue overlay effects, and enhanced button functionality. Features smooth hover animations and comprehensive button styling options.
                </p>
                <div class="demo-features">
                    <span class="demo-feature-tag">Image Overlays</span>
                    <span class="demo-feature-tag">Button Support</span>
                    <span class="demo-feature-tag">Multiple Styles</span>
                    <span class="demo-feature-tag">Hover Animations</span>
                    <span class="demo-feature-tag">Responsive</span>
                </div>
            </div>
            <div class="demo-content">
                <div class="demo-example">
                    <?php
                    get_template_part('template-parts/sections/features-with-images', null, array(
                        'headline' => 'Why Choose Our Services',
                        'subheadline' => 'Experience the difference with our comprehensive real estate solutions',
                        'layout' => 'cards',
                        'image_style' => 'rounded',
                        'features' => array(
                            array(
                                'image' => array(
                                    'url' => get_template_directory_uri() . '/assets/images/features/market-knowledge.jpg',
                                    'alt' => 'Market expertise and analysis'
                                ),
                                'title' => 'Expert Market Knowledge',
                                'content' => 'Deep understanding of local markets and pricing trends to ensure you make informed decisions with confidence and clarity.',
                                'button' => array(
                                    'text' => 'View Market Reports',
                                    'url' => '#market',
                                    'style' => 'primary',
                                    'size' => 'md',
                                    'icon' => 'fas fa-chart-line'
                                )
                            ),
                            array(
                                'image' => array(
                                    'url' => get_template_directory_uri() . '/assets/images/features/personalized-service.jpg',
                                    'alt' => 'Personalized client service'
                                ),
                                'title' => 'Personalized Service',
                                'content' => 'Dedicated support throughout your entire real estate journey with tailored solutions that match your specific needs and goals.',
                                'button' => array(
                                    'text' => 'Schedule Consultation',
                                    'url' => '#consultation',
                                    'style' => 'secondary',
                                    'size' => 'md',
                                    'icon' => 'fas fa-calendar'
                                )
                            ),
                            array(
                                'image' => array(
                                    'url' => get_template_directory_uri() . '/assets/images/features/track-record.jpg',
                                    'alt' => 'Proven success track record'
                                ),
                                'title' => 'Proven Track Record',
                                'content' => 'Consistently delivering successful outcomes with a history of satisfied clients and closed deals across all price ranges.',
                                'button' => array(
                                    'text' => 'See Success Stories',
                                    'url' => '#success',
                                    'style' => 'outline',
                                    'size' => 'md',
                                    'icon' => 'fas fa-trophy'
                                )
                            ),
                            array(
                                'image' => array(
                                    'url' => get_template_directory_uri() . '/assets/images/features/availability.jpg',
                                    'alt' => '24/7 availability and support'
                                ),
                                'title' => '24/7 Availability',
                                'content' => 'Always accessible when you need us most, with quick response times and flexible scheduling to accommodate your busy life.',
                                'button' => array(
                                    'text' => 'Contact Us Now',
                                    'url' => '#contact',
                                    'style' => 'text',
                                    'size' => 'md',
                                    'icon' => 'fas fa-phone'
                                )
                            )
                        )
                    )); ?>
                </div>

                <div class="demo-code">
                    <pre><code><span class="comment">// Features with Images, Overlays, and Buttons</span>
<span class="keyword">get_template_part</span>(<span class="string">'template-parts/sections/features-with-images'</span>, <span class="keyword">null</span>, [
    <span class="string">'headline'</span> => <span class="string">'Why Choose Our Services'</span>,
    <span class="string">'subheadline'</span> => <span class="string">'Comprehensive real estate solutions'</span>,
    <span class="string">'layout'</span> => <span class="string">'cards'</span>,
    <span class="string">'image_style'</span> => <span class="string">'rounded'</span>,
    <span class="string">'features'</span> => [
        [
            <span class="string">'image'</span> => [
                <span class="string">'url'</span> => <span class="string">'/path/to/feature-image.jpg'</span>,
                <span class="string">'alt'</span> => <span class="string">'Feature description'</span>
            ],
            <span class="string">'title'</span> => <span class="string">'Expert Market Knowledge'</span>,
            <span class="string">'content'</span> => <span class="string">'Deep understanding of local markets...'</span>,
            <span class="string">'button'</span> => [
                <span class="string">'text'</span> => <span class="string">'View Market Reports'</span>,
                <span class="string">'url'</span> => <span class="string">'#market'</span>,
                <span class="string">'style'</span> => <span class="string">'primary'</span>, <span class="comment">// primary, secondary, outline, text</span>
                <span class="string">'size'</span> => <span class="string">'md'</span>, <span class="comment">// sm, md, lg</span>
                <span class="string">'icon'</span> => <span class="string">'fas fa-chart-line'</span>
            ]
        ],
        <span class="comment">// More features with different button styles...</span>
        <span class="comment">// Images get blue overlay with fade-away hover effect</span>
        <span class="comment">// Buttons have hover animations and multiple styles</span>
    ]
]);</code></pre>
                </div>
            </div>
        </div>

        <!-- Clean/Transparent PNG Features Section -->
        <div id="clean-png-features" class="demo-section">
            <div class="demo-header">
                <h2 class="demo-title">
                    <i class="fas fa-image"></i>
                    Clean PNG Features (No Background)
                </h2>
                <p class="demo-description">
                    Perfect for transparent PNG images! Use the 'clean' image style and disable overlay effects to showcase transparent graphics without any background boxes or overlays.
                </p>
                <div class="demo-features">
                    <span class="demo-feature-tag">Transparent PNG Support</span>
                    <span class="demo-feature-tag">No Overlays</span>
                    <span class="demo-feature-tag">Clean Styling</span>
                    <span class="demo-feature-tag">No Borders</span>
                </div>
            </div>
            <div class="demo-content">
                <div class="demo-example">
                    <?php
                    get_template_part('template-parts/sections/features-with-images', null, array(
                        'headline' => 'Our Digital Solutions',
                        'subheadline' => 'Modern tools and technologies that make real estate simple',
                        'layout' => 'grid',
                        'background' => 'light',
                        'image_style' => 'clean',
                        'overlay_effects' => false,
                        'features' => array(
                            array(
                                'image' => array(
                                    'url' => get_template_directory_uri() . '/assets/images/icons/mobile-app-transparent.png',
                                    'alt' => 'Mobile app icon'
                                ),
                                'title' => 'Mobile App',
                                'content' => 'Search properties on-the-go with our intuitive mobile application featuring real-time notifications and saved searches.',
                                'button' => array(
                                    'text' => 'Download App',
                                    'url' => '#app',
                                    'style' => 'primary',
                                    'icon' => 'fas fa-download'
                                )
                            ),
                            array(
                                'image' => array(
                                    'url' => get_template_directory_uri() . '/assets/images/icons/virtual-tour-transparent.png',
                                    'alt' => 'Virtual tour icon'
                                ),
                                'title' => 'Virtual Tours',
                                'content' => 'Experience properties remotely with immersive 360° virtual tours and detailed floor plans.',
                                'button' => array(
                                    'text' => 'Take a Tour',
                                    'url' => '#tours',
                                    'style' => 'secondary',
                                    'icon' => 'fas fa-vr-cardboard'
                                )
                            ),
                            array(
                                'image' => array(
                                    'url' => get_template_directory_uri() . '/assets/images/icons/ai-assistant-transparent.png',
                                    'alt' => 'AI assistant icon'
                                ),
                                'title' => 'AI Assistant',
                                'content' => 'Get instant answers to property questions with our intelligent chatbot powered by machine learning.',
                                'button' => array(
                                    'text' => 'Try Assistant',
                                    'url' => '#ai',
                                    'style' => 'outline',
                                    'icon' => 'fas fa-robot'
                                )
                            )
                        )
                    )); ?>
                </div>

                <div class="demo-code">
                    <pre><code><span class="comment">// Clean PNG Features - No overlays or backgrounds</span>
<span class="keyword">get_template_part</span>(<span class="string">'template-parts/sections/features-with-images'</span>, <span class="keyword">null</span>, [
    <span class="string">'headline'</span> => <span class="string">'Our Digital Solutions'</span>,
    <span class="string">'layout'</span> => <span class="string">'grid'</span>,
    <span class="string">'background'</span> => <span class="string">'light'</span>,
    <span class="string">'image_style'</span> => <span class="string">'clean'</span>, <span class="comment">// Perfect for transparent PNGs!</span>
    <span class="string">'overlay_effects'</span> => <span class="keyword">false</span>, <span class="comment">// Disable blue overlays</span>
    <span class="string">'features'</span> => [
        [
            <span class="string">'image'</span> => [
                <span class="string">'url'</span> => <span class="string">'/path/to/transparent-icon.png'</span>,
                <span class="string">'alt'</span> => <span class="string">'Feature icon'</span>
            ],
            <span class="string">'title'</span> => <span class="string">'Mobile App'</span>,
            <span class="string">'content'</span> => <span class="string">'Search properties on-the-go...'</span>,
            <span class="string">'button'</span> => [<span class="comment">// Buttons still work perfectly!</span>
                <span class="string">'text'</span> => <span class="string">'Download App'</span>,
                <span class="string">'style'</span> => <span class="string">'primary'</span>,
                <span class="string">'icon'</span> => <span class="string">'fas fa-download'</span>
            ]
        ]
        <span class="comment">// Clean style removes:</span>
        <span class="comment">// ✗ Blue overlays</span>
        <span class="comment">// ✗ Border radius</span>
        <span class="comment">// ✗ Fixed aspect ratios</span>
        <span class="comment">// ✗ Hover scaling effects</span>
        <span class="comment">// Perfect for transparent PNGs and clean designs!</span>
    ]
]);</code></pre>
                </div>
            </div>
        </div>

        <!-- Icon-Based Features Section -->
        <div id="icon-features" class="demo-section">
            <div class="demo-header">
                <h2 class="demo-title">
                    <i class="fas fa-icons"></i>
                    Icon-Based Features
                </h2>
                <p class="demo-description">
                    The regular features template now supports buttons too! Showcase features with icons and enhanced button functionality including multiple styles, sizes, and hover animations.
                </p>
                <div class="demo-features">
                    <span class="demo-feature-tag">Font Awesome Icons</span>
                    <span class="demo-feature-tag">Button Styles</span>
                    <span class="demo-feature-tag">Multiple Layouts</span>
                    <span class="demo-feature-tag">Hover Effects</span>
                    <span class="demo-feature-tag">Responsive</span>
                </div>
            </div>
            <div class="demo-content">
                <div class="demo-example">
                    <?php
                    get_template_part('template-parts/sections/features', null, array(
                        'headline' => 'Our Core Services',
                        'subheadline' => 'Comprehensive real estate solutions with enhanced button functionality',
                        'layout' => 'cards',
                        'background' => 'light',
                        'features' => array(
                            array(
                                'icon' => 'fas fa-home',
                                'title' => 'Property Sales',
                                'content' => 'Expert guidance through every step of buying or selling your property with market insights and negotiation skills.',
                                'button' => array(
                                    'text' => 'Browse Listings',
                                    'url' => '#listings',
                                    'style' => 'primary',
                                    'size' => 'md',
                                    'icon' => 'fas fa-search'
                                )
                            ),
                            array(
                                'icon' => 'fas fa-handshake',
                                'title' => 'Buyer Representation',
                                'content' => 'Dedicated advocacy and representation to ensure you get the best deal and protect your interests throughout the process.',
                                'button' => array(
                                    'text' => 'Get Started',
                                    'url' => '#representation',
                                    'style' => 'secondary',
                                    'size' => 'md',
                                    'icon' => 'fas fa-user-check'
                                )
                            ),
                            array(
                                'icon' => 'fas fa-chart-line',
                                'title' => 'Market Analysis',
                                'content' => 'Comprehensive market research and analysis to help you make informed decisions based on current trends and data.',
                                'button' => array(
                                    'text' => 'View Reports',
                                    'url' => '#analysis',
                                    'style' => 'outline',
                                    'size' => 'md',
                                    'icon' => 'fas fa-file-chart'
                                )
                            )
                        )
                    )); ?>
                </div>

                <div class="demo-code">
                    <pre><code><span class="comment">// Icon-Based Features with Button Support</span>
<span class="keyword">get_template_part</span>(<span class="string">'template-parts/sections/features'</span>, <span class="keyword">null</span>, [
    <span class="string">'headline'</span> => <span class="string">'Our Core Services'</span>,
    <span class="string">'layout'</span> => <span class="string">'cards'</span>,
    <span class="string">'background'</span> => <span class="string">'light'</span>,
    <span class="string">'features'</span> => [
        [
            <span class="string">'icon'</span> => <span class="string">'fas fa-home'</span>,
            <span class="string">'title'</span> => <span class="string">'Property Sales'</span>,
            <span class="string">'content'</span> => <span class="string">'Expert guidance through every step...'</span>,
            <span class="string">'button'</span> => [
                <span class="string">'text'</span> => <span class="string">'Browse Listings'</span>,
                <span class="string">'url'</span> => <span class="string">'#listings'</span>,
                <span class="string">'style'</span> => <span class="string">'primary'</span>, <span class="comment">// primary, secondary, outline, outline-secondary, text</span>
                <span class="string">'size'</span> => <span class="string">'md'</span>, <span class="comment">// sm, md, lg</span>
                <span class="string">'icon'</span> => <span class="string">'fas fa-search'</span>,
                <span class="string">'target'</span> => <span class="string">'_self'</span> <span class="comment">// _self, _blank</span>
            ]
        ],
        <span class="comment">// Features can use both icons and buttons together</span>
        <span class="comment">// Button styles: primary (blue), secondary (peach), outline, text</span>
        <span class="comment">// All buttons include hover animations and accessibility</span>
    ]
]);</code></pre>
                </div>
            </div>
        </div>

        <!-- Content & Gallery Section -->
        <div id="content-masonry" class="demo-section">
            <div class="demo-header">
                <h2 class="demo-title">
                    <i class="fas fa-th-large"></i>
                    Content Template with Gallery & Masonry
                </h2>
                <p class="demo-description">
                    The content template now supports multiple images with masonry and gallery layouts, plus flexible image sizing and aspect ratios!
                </p>
                <div class="demo-features">
                    <span class="demo-feature-tag">Image Size Control</span>
                    <span class="demo-feature-tag">Masonry Layout</span>
                    <span class="demo-feature-tag">Gallery Grid</span>
                    <span class="demo-feature-tag">Aspect Ratios</span>
                    <span class="demo-feature-tag">Mobile Responsive</span>
                </div>
            </div>
            <div class="demo-content">
                <div class="demo-example">
                    <?php
                    // Masonry layout example
                    get_template_part('template-parts/sections/content', null, array(
                        'layout' => 'masonry',
                        'headline' => 'Our Recent Projects',
                        'subheadline' => 'A beautiful masonry layout showcasing our portfolio',
                        'content' => 'Discover the variety and quality of our recent real estate projects across different property types and locations.',
                        'background' => 'light',
                        'image_style' => 'default',
                        'image_size' => 'medium',
                        'masonry_columns' => 3,
                        'gallery_gap' => 'lg',
                        'animation' => true,
                        'images' => array(
                            array(
                                'url' => get_template_directory_uri() . '/assets/images/gallery/project-1.jpg',
                                'alt' => 'Modern luxury home exterior',
                                'caption' => 'Luxury Modern Home - Completed 2024'
                            ),
                            array(
                                'url' => get_template_directory_uri() . '/assets/images/gallery/project-2.jpg',
                                'alt' => 'Contemporary kitchen design',
                                'caption' => 'Designer Kitchen Renovation'
                            ),
                            array(
                                'url' => get_template_directory_uri() . '/assets/images/gallery/project-3.jpg',
                                'alt' => 'Spacious living room',
                                'caption' => 'Open Concept Living Space'
                            ),
                            array(
                                'url' => get_template_directory_uri() . '/assets/images/gallery/project-4.jpg',
                                'alt' => 'Beautiful master bedroom',
                                'caption' => 'Master Suite Design'
                            ),
                            array(
                                'url' => get_template_directory_uri() . '/assets/images/gallery/project-5.jpg',
                                'alt' => 'Outdoor patio area',
                                'caption' => 'Outdoor Entertainment Area'
                            ),
                            array(
                                'url' => get_template_directory_uri() . '/assets/images/gallery/project-6.jpg',
                                'alt' => 'Home office space',
                                'caption' => 'Modern Home Office'
                            )
                        )
                    )); ?>
                    
                    <div style="margin-top: var(--hph-margin-3xl);"></div>
                    
                    <?php
                    // Gallery layout example
                    get_template_part('template-parts/sections/content', null, array(
                        'layout' => 'gallery',
                        'headline' => 'Property Types',
                        'subheadline' => 'Grid layout perfect for showcasing different categories',
                        'background' => 'white',
                        'image_style' => 'square',
                        'image_size' => 'large',
                        'gallery_gap' => 'md',
                        'images' => array(
                            array(
                                'url' => get_template_directory_uri() . '/assets/images/property-types/single-family.jpg',
                                'alt' => 'Single family homes',
                                'caption' => 'Single Family Homes'
                            ),
                            array(
                                'url' => get_template_directory_uri() . '/assets/images/property-types/condos.jpg',
                                'alt' => 'Condominium units',
                                'caption' => 'Condominiums'
                            ),
                            array(
                                'url' => get_template_directory_uri() . '/assets/images/property-types/townhomes.jpg',
                                'alt' => 'Townhouse properties',
                                'caption' => 'Townhomes'
                            ),
                            array(
                                'url' => get_template_directory_uri() . '/assets/images/property-types/luxury.jpg',
                                'alt' => 'Luxury estate homes',
                                'caption' => 'Luxury Estates'
                            )
                        )
                    )); ?>
                </div>

                <div class="demo-code">
                    <pre><code><span class="comment">// Masonry Layout with Mixed Heights</span>
<span class="keyword">get_template_part</span>(<span class="string">'template-parts/sections/content'</span>, <span class="keyword">null</span>, [
    <span class="string">'layout'</span> => <span class="string">'masonry'</span>, <span class="comment">// Pinterest-style columns</span>
    <span class="string">'headline'</span> => <span class="string">'Our Recent Projects'</span>,
    <span class="string">'background'</span> => <span class="string">'light'</span>,
    <span class="string">'image_style'</span> => <span class="string">'default'</span>, <span class="comment">// default, square, wide, circle, clean</span>
    <span class="string">'image_size'</span> => <span class="string">'medium'</span>, <span class="comment">// small, medium, large, full</span>
    <span class="string">'masonry_columns'</span> => <span class="number">3</span>, <span class="comment">// 2, 3, 4, 5 columns</span>
    <span class="string">'gallery_gap'</span> => <span class="string">'lg'</span>, <span class="comment">// sm, md, lg spacing</span>
    <span class="string">'images'</span> => [
        [
            <span class="string">'url'</span> => <span class="string">'/path/to/image1.jpg'</span>,
            <span class="string">'alt'</span> => <span class="string">'Image description'</span>,
            <span class="string">'caption'</span> => <span class="string">'Optional caption'</span>
        ],
        <span class="comment">// More images...</span>
        <span class="comment">// Images auto-arrange in masonry columns</span>
        <span class="comment">// Mobile responsive (1 column on mobile)</span>
    ]
]);

<span class="comment">// Gallery Grid Layout</span>
<span class="keyword">get_template_part</span>(<span class="string">'template-parts/sections/content'</span>, <span class="keyword">null</span>, [
    <span class="string">'layout'</span> => <span class="string">'gallery'</span>, <span class="comment">// Equal-height grid</span>
    <span class="string">'image_style'</span> => <span class="string">'square'</span>, <span class="comment">// Perfect for consistent grid</span>
    <span class="string">'image_size'</span> => <span class="string">'large'</span>,
    <span class="string">'images'</span> => [<span class="comment">/* images array */</span>]
]);

<span class="comment">// NEW: Image Size Options</span>
<span class="comment">// 'small' = max 200px</span>
<span class="comment">// 'medium' = max 500px (default)</span>
<span class="comment">// 'large' = max 800px</span>
<span class="comment">// 'full' = 100% width</span>

<span class="comment">// Single image with size control</span>
<span class="keyword">get_template_part</span>(<span class="string">'template-parts/sections/content'</span>, <span class="keyword">null</span>, [
    <span class="string">'layout'</span> => <span class="string">'left-image'</span>,
    <span class="string">'image_size'</span> => <span class="string">'small'</span>, <span class="comment">// Small profile image</span>
    <span class="string">'image_style'</span> => <span class="string">'circle'</span>, <span class="comment">// Circular crop</span>
    <span class="string">'image'</span> => [<span class="string">'url'</span> => <span class="string">'/headshot.jpg'</span>]
]);</code></pre>
                </div>
            </div>
        </div>

        <!-- Team Section -->
        <div id="team" class="demo-section">
            <div class="demo-header">
                <h2 class="demo-title">
                    <i class="fas fa-users"></i>
                    Team & Agents
                </h2>
                <p class="demo-description">
                    Showcase your professional team with agent profiles, contact information, and specialties.
                </p>
                <div class="demo-features">
                    <span class="demo-feature-tag">Agent Profiles</span>
                    <span class="demo-feature-tag">Contact Cards</span>
                    <span class="demo-feature-tag">Specialties</span>
                </div>
            </div>
            <div class="demo-content">
                <div class="demo-example">
                    <?php
                    get_template_part('template-parts/sections/team', null, array(
                        'headline' => 'Meet Our Expert Team',
                        'subheadline' => 'Experienced professionals dedicated to your real estate success',
                        'team_members' => array(
                            array(
                                'name' => 'Sarah Johnson',
                                'title' => 'Senior Real Estate Agent',
                                'bio' => 'With over 10 years of experience in luxury properties, Sarah specializes in high-end residential sales.',
                                'image' => get_template_directory_uri() . '/assets/images/team/agent-1.jpg',
                                'phone' => '(555) 123-4567',
                                'email' => 'sarah@example.com',
                                'specialties' => array('Luxury Homes', 'Investment Properties', 'First-Time Buyers')
                            ),
                            array(
                                'name' => 'Michael Chen',
                                'title' => 'Commercial Specialist',
                                'bio' => 'Expert in commercial real estate with a focus on retail spaces and office buildings.',
                                'image' => get_template_directory_uri() . '/assets/images/team/agent-2.jpg',
                                'phone' => '(555) 123-4568',
                                'email' => 'michael@example.com',
                                'specialties' => array('Commercial Properties', 'Retail Spaces', 'Investment Analysis')
                            )
                        )
                    )); ?>
                </div>

                <div class="demo-code">
                    <pre><code><span class="keyword">get_template_part</span>(<span class="string">'template-parts/sections/team'</span>, <span class="keyword">null</span>, [
    <span class="string">'headline'</span> => <span class="string">'Meet Our Expert Team'</span>,
    <span class="string">'team_members'</span> => [
        [
            <span class="string">'name'</span> => <span class="string">'Sarah Johnson'</span>,
            <span class="string">'title'</span> => <span class="string">'Senior Real Estate Agent'</span>,
            <span class="string">'bio'</span> => <span class="string">'With over 10 years of experience...'</span>,
            <span class="string">'image'</span> => <span class="string">'/path/to/agent-photo.jpg'</span>,
            <span class="string">'specialties'</span> => [<span class="string">'Luxury Homes'</span>, <span class="string">'Investment Properties'</span>]
        ]
        <span class="comment">// More team members...</span>
    ]
]);</code></pre>
                </div>
            </div>
        </div>

        <!-- Featured Properties -->
        <div id="properties" class="demo-section">
            <div class="demo-header">
                <h2 class="demo-title">
                    <i class="fas fa-home"></i>
                    Featured Properties
                </h2>
                <p class="demo-description">
                    Display featured listings and property showcases with beautiful cards and detailed information.
                </p>
                <div class="demo-features">
                    <span class="demo-feature-tag">Property Cards</span>
                    <span class="demo-feature-tag">Price Display</span>
                    <span class="demo-feature-tag">Property Details</span>
                </div>
            </div>
            <div class="demo-content">
                <div class="demo-example">
                    <?php
                    get_template_part('template-parts/sections/featured-properties', null, array(
                        'headline' => 'Featured Properties',
                        'subheadline' => 'Discover exceptional properties in prime locations',
                        'show_filters' => false,
                        'properties' => array(
                            array(
                                'title' => 'Luxury Waterfront Estate',
                                'price' => '$1,250,000',
                                'address' => '123 Lakeshore Drive',
                                'bedrooms' => 4,
                                'bathrooms' => 3,
                                'sqft' => '3,200',
                                'image' => get_template_directory_uri() . '/assets/images/properties/luxury-home.jpg',
                                'url' => '#',
                                'features' => array('Waterfront', 'Private Dock', 'Gourmet Kitchen')
                            ),
                            array(
                                'title' => 'Modern Downtown Loft',
                                'price' => '$875,000',
                                'address' => '456 City Center Blvd',
                                'bedrooms' => 2,
                                'bathrooms' => 2,
                                'sqft' => '1,800',
                                'image' => get_template_directory_uri() . '/assets/images/properties/modern-loft.jpg',
                                'url' => '#',
                                'features' => array('City Views', 'Exposed Brick', 'Rooftop Access')
                            ),
                            array(
                                'title' => 'Family Dream Home',
                                'price' => '$625,000',
                                'address' => '789 Suburban Lane',
                                'bedrooms' => 5,
                                'bathrooms' => 4,
                                'sqft' => '4,500',
                                'image' => get_template_directory_uri() . '/assets/images/properties/family-home.jpg',
                                'url' => '#',
                                'features' => array('Large Backyard', 'Home Office', '3-Car Garage')
                            )
                        )
                    )); ?>
                </div>

                <div class="demo-code">
                    <pre><code><span class="keyword">get_template_part</span>(<span class="string">'template-parts/sections/featured-properties'</span>, <span class="keyword">null</span>, [
    <span class="string">'headline'</span> => <span class="string">'Featured Properties'</span>,
    <span class="string">'properties'</span> => [
        [
            <span class="string">'title'</span> => <span class="string">'Luxury Waterfront Estate'</span>,
            <span class="string">'price'</span> => <span class="string">'$1,250,000'</span>,
            <span class="string">'bedrooms'</span> => <span class="keyword">4</span>,
            <span class="string">'bathrooms'</span> => <span class="keyword">3</span>,
            <span class="string">'sqft'</span> => <span class="string">'3,200'</span>,
            <span class="string">'features'</span> => [<span class="string">'Waterfront'</span>, <span class="string">'Private Dock'</span>]
        ]
        <span class="comment">// More properties...</span>
    ]
]);</code></pre>
                </div>
            </div>
        </div>

        <!-- Testimonials -->
        <div id="testimonials" class="demo-section">
            <div class="demo-header">
                <h2 class="demo-title">
                    <i class="fas fa-comments"></i>
                    Testimonials
                </h2>
                <p class="demo-description">
                    Build trust and credibility with client testimonials and success stories.
                </p>
                <div class="demo-features">
                    <span class="demo-feature-tag">Client Reviews</span>
                    <span class="demo-feature-tag">Star Ratings</span>
                    <span class="demo-feature-tag">Photo Support</span>
                </div>
            </div>
            <div class="demo-content">
                <div class="demo-example">
                    <?php
                    get_template_part('template-parts/sections/testimonials', null, array(
                        'headline' => 'What Our Clients Say',
                        'subheadline' => 'Real stories from satisfied homeowners and investors',
                        'testimonials' => array(
                            array(
                                'name' => 'Jennifer Martinez',
                                'title' => 'First-Time Homebuyer',
                                'content' => 'The team made buying my first home incredibly smooth and stress-free. Their expertise and patience throughout the process was invaluable.',
                                'rating' => 5,
                                'image' => get_template_directory_uri() . '/assets/images/testimonials/client-1.jpg'
                            ),
                            array(
                                'name' => 'David Thompson',
                                'title' => 'Property Investor',
                                'content' => 'Outstanding market knowledge and investment insights. They helped me build a profitable real estate portfolio.',
                                'rating' => 5,
                                'image' => get_template_directory_uri() . '/assets/images/testimonials/client-2.jpg'
                            ),
                            array(
                                'name' => 'Lisa & Mark Williams',
                                'title' => 'Home Sellers',
                                'content' => 'Sold our home above asking price in just two weeks. Their marketing strategy and negotiation skills are exceptional.',
                                'rating' => 5,
                                'image' => get_template_directory_uri() . '/assets/images/testimonials/client-3.jpg'
                            )
                        )
                    )); ?>
                </div>

                <div class="demo-code">
                    <pre><code><span class="keyword">get_template_part</span>(<span class="string">'template-parts/sections/testimonials'</span>, <span class="keyword">null</span>, [
    <span class="string">'headline'</span> => <span class="string">'What Our Clients Say'</span>,
    <span class="string">'testimonials'</span> => [
        [
            <span class="string">'name'</span> => <span class="string">'Jennifer Martinez'</span>,
            <span class="string">'title'</span> => <span class="string">'First-Time Homebuyer'</span>,
            <span class="string">'content'</span> => <span class="string">'The team made buying my first home incredibly smooth...'</span>,
            <span class="string">'rating'</span> => <span class="keyword">5</span>,
            <span class="string">'image'</span> => <span class="string">'/path/to/client-photo.jpg'</span>
        ]
        <span class="comment">// More testimonials...</span>
    ]
]);</code></pre>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div id="forms" class="demo-section">
            <div class="demo-header">
                <h2 class="demo-title">
                    <i class="fas fa-bullhorn"></i>
                    Call-to-Action & Forms
                </h2>
                <p class="demo-description">
                    Drive conversions with compelling CTAs and lead generation forms.
                </p>
                <div class="demo-features">
                    <span class="demo-feature-tag">Lead Generation</span>
                    <span class="demo-feature-tag">Contact Forms</span>
                    <span class="demo-feature-tag">Conversion Optimized</span>
                </div>
            </div>
            <div class="demo-content">
                <div class="demo-example">
                    <?php
                    get_template_part('template-parts/sections/cta', null, array(
                        'style' => 'gradient',
                        'theme' => 'primary',
                        'headline' => 'Ready to Find Your Dream Home?',
                        'subheadline' => 'Let our expert team guide you through every step of your real estate journey',
                        'content' => 'From first consultation to closing day, we provide personalized service and expert guidance to ensure your success.',
                        'buttons' => array(
                            array(
                                'text' => 'Start Your Search',
                                'url' => '/properties/',
                                'style' => 'white',
                                'size' => 'xl',
                                'icon' => 'fas fa-search'
                            ),
                            array(
                                'text' => 'Get Free Consultation',
                                'url' => '/contact/',
                                'style' => 'outline-white',
                                'size' => 'xl',
                                'icon' => 'fas fa-phone'
                            )
                        )
                    )); ?>
                </div>

                <div class="demo-code">
                    <pre><code><span class="keyword">get_template_part</span>(<span class="string">'template-parts/sections/cta'</span>, <span class="keyword">null</span>, [
    <span class="string">'style'</span> => <span class="string">'gradient'</span>,
    <span class="string">'theme'</span> => <span class="string">'primary'</span>,
    <span class="string">'headline'</span> => <span class="string">'Ready to Find Your Dream Home?'</span>,
    <span class="string">'subheadline'</span> => <span class="string">'Expert guidance every step of the way'</span>,
    <span class="string">'buttons'</span> => [
        [<span class="string">'text'</span> => <span class="string">'Start Your Search'</span>, <span class="string">'url'</span> => <span class="string">'/properties/'</span>, <span class="string">'style'</span> => <span class="string">'white'</span>],
        [<span class="string">'text'</span> => <span class="string">'Get Consultation'</span>, <span class="string">'url'</span> => <span class="string">'/contact/'</span>, <span class="string">'style'</span> => <span class="string">'outline-white'</span>]
    ]
]);</code></pre>
                </div>
            </div>
        </div>

        <!-- Process Section -->
        <div id="process" class="demo-section">
            <div class="demo-header">
                <h2 class="demo-title">
                    <i class="fas fa-list-ol"></i>
                    Process Steps
                </h2>
                <p class="demo-description">
                    Show your workflow and process with clear, numbered steps that build confidence.
                </p>
                <div class="demo-features">
                    <span class="demo-feature-tag">Step-by-Step</span>
                    <span class="demo-feature-tag">Visual Timeline</span>
                    <span class="demo-feature-tag">Process Clarity</span>
                </div>
            </div>
            <div class="demo-content">
                <div class="demo-example">
                    <?php
                    get_template_part('template-parts/sections/process', null, array(
                        'headline' => 'Our Proven Process',
                        'subheadline' => 'Simple steps to real estate success',
                        'steps' => array(
                            array(
                                'icon' => 'fas fa-search',
                                'title' => 'Initial Consultation',
                                'description' => 'We start with understanding your needs, budget, and timeline to create a personalized strategy.'
                            ),
                            array(
                                'icon' => 'fas fa-home',
                                'title' => 'Property Search',
                                'description' => 'Using our market expertise, we identify properties that match your criteria and arrange viewings.'
                            ),
                            array(
                                'icon' => 'fas fa-handshake',
                                'title' => 'Negotiation & Offer',
                                'description' => 'We handle all negotiations to secure the best possible terms and price for your property.'
                            ),
                            array(
                                'icon' => 'fas fa-key',
                                'title' => 'Closing & Beyond',
                                'description' => 'We guide you through closing and provide ongoing support as your trusted real estate partner.'
                            )
                        )
                    )); ?>
                </div>

                <div class="demo-code">
                    <pre><code><span class="keyword">get_template_part</span>(<span class="string">'template-parts/sections/process'</span>, <span class="keyword">null</span>, [
    <span class="string">'headline'</span> => <span class="string">'Our Proven Process'</span>,
    <span class="string">'steps'</span> => [
        [
            <span class="string">'icon'</span> => <span class="string">'fas fa-search'</span>,
            <span class="string">'title'</span> => <span class="string">'Initial Consultation'</span>,
            <span class="string">'description'</span> => <span class="string">'Understanding your needs and creating strategy...'</span>
        ]
        <span class="comment">// More steps...</span>
    ]
]);</code></pre>
                </div>
            </div>
        </div>

        <!-- Stats Section -->
        <div id="stats" class="demo-section">
            <div class="demo-header">
                <h2 class="demo-title">
                    <i class="fas fa-chart-bar"></i>
                    Statistics
                </h2>
                <p class="demo-description">
                    Build credibility with impressive statistics and key performance metrics.
                </p>
                <div class="demo-features">
                    <span class="demo-feature-tag">Key Metrics</span>
                    <span class="demo-feature-tag">Animated Numbers</span>
                    <span class="demo-feature-tag">Visual Impact</span>
                </div>
            </div>
            <div class="demo-content">
                <div class="demo-example">
                    <?php
                    get_template_part('template-parts/sections/stats', null, array(
                        'headline' => 'Proven Track Record',
                        'subheadline' => 'Numbers that speak to our expertise and success',
                        'stats' => array(
                            array(
                                'number' => '500+',
                                'label' => 'Properties Sold',
                                'icon' => 'fas fa-home'
                            ),
                            array(
                                'number' => '98%',
                                'label' => 'Client Satisfaction',
                                'icon' => 'fas fa-smile'
                            ),
                            array(
                                'number' => '$50M+',
                                'label' => 'Total Sales Volume',
                                'icon' => 'fas fa-dollar-sign'
                            ),
                            array(
                                'number' => '15+',
                                'label' => 'Years Experience',
                                'icon' => 'fas fa-calendar'
                            )
                        )
                    )); ?>
                </div>

                <div class="demo-code">
                    <pre><code><span class="keyword">get_template_part</span>(<span class="string">'template-parts/sections/stats'</span>, <span class="keyword">null</span>, [
    <span class="string">'headline'</span> => <span class="string">'Proven Track Record'</span>,
    <span class="string">'stats'</span> => [
        [
            <span class="string">'number'</span> => <span class="string">'500+'</span>,
            <span class="string">'label'</span> => <span class="string">'Properties Sold'</span>,
            <span class="string">'icon'</span> => <span class="string">'fas fa-home'</span>
        ]
        <span class="comment">// More stats...</span>
    ]
]);</code></pre>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div id="faq" class="demo-section">
            <div class="demo-header">
                <h2 class="demo-title">
                    <i class="fas fa-question-circle"></i>
                    FAQ Section
                </h2>
                <p class="demo-description">
                    Address common questions and concerns with an organized, searchable FAQ section.
                </p>
                <div class="demo-features">
                    <span class="demo-feature-tag">Expandable Questions</span>
                    <span class="demo-feature-tag">Search Function</span>
                    <span class="demo-feature-tag">Categories</span>
                </div>
            </div>
            <div class="demo-content">
                <div class="demo-example">
                    <?php
                    get_template_part('template-parts/sections/faq', null, array(
                        'headline' => 'Frequently Asked Questions',
                        'subheadline' => 'Get answers to common real estate questions',
                        'faqs' => array(
                            array(
                                'question' => 'How long does it typically take to buy a home?',
                                'answer' => 'The home buying process typically takes 30-45 days from offer acceptance to closing, though this can vary based on financing, inspections, and other factors.'
                            ),
                            array(
                                'question' => 'What should I do to prepare for selling my home?',
                                'answer' => 'Start with decluttering and deep cleaning, consider minor repairs and improvements, and work with your agent to price competitively based on current market conditions.'
                            ),
                            array(
                                'question' => 'How much should I budget for closing costs?',
                                'answer' => 'Closing costs typically range from 2-5% of the home\'s purchase price and include fees for inspections, appraisals, title insurance, and other services.'
                            ),
                            array(
                                'question' => 'Do I need a real estate agent to buy or sell?',
                                'answer' => 'While not legally required, a professional real estate agent provides valuable market knowledge, negotiation skills, and guidance through the complex transaction process.'
                            )
                        )
                    )); ?>
                </div>

                <div class="demo-code">
                    <pre><code><span class="keyword">get_template_part</span>(<span class="string">'template-parts/sections/faq'</span>, <span class="keyword">null</span>, [
    <span class="string">'headline'</span> => <span class="string">'Frequently Asked Questions'</span>,
    <span class="string">'faqs'</span> => [
        [
            <span class="string">'question'</span> => <span class="string">'How long does it take to buy a home?'</span>,
            <span class="string">'answer'</span> => <span class="string">'The process typically takes 30-45 days...'</span>
        ]
        <span class="comment">// More FAQs...</span>
    ]
]);</code></pre>
                </div>
            </div>
        </div>

        <!-- Final CTA -->
        <div style="text-align: center; padding: 80px 40px; background: linear-gradient(135deg, var(--hph-primary), var(--hph-secondary)); color: white; border-radius: 16px; margin-top: 40px;">
            <h2 style="font-size: 2.5rem; margin-bottom: 20px;">🚀 Ready to Build Amazing Pages?</h2>
            <p style="font-size: 1.2rem; opacity: 0.9; max-width: 600px; margin: 0 auto 30px;">Use these section templates to create compelling, conversion-focused pages that engage your visitors and grow your business.</p>
            <a href="<?php echo admin_url('edit.php?post_type=page'); ?>" style="display: inline-block; background: white; color: var(--hph-primary); padding: 15px 30px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 1.1rem;">
                Create New Page
            </a>
        </div>
    </div>
</div>

<?php get_footer(); ?>
