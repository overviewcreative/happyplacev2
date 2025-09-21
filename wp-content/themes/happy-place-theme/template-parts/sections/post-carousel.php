<?php
/**
 * Simple Post Carousel Section Template
 * 
 * A clean, simple carousel for rotating posts with ACF fields
 * Perfect for hero sections or featured content
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Default arguments
$defaults = array(
    'post_type' => 'post',
    'posts_per_page' => 5,
    'post_ids' => array(), // Specific post IDs to show
    'category' => '', // Category slug or ID
    'meta_query' => array(), // Custom meta query
    'orderby' => 'date',
    'order' => 'DESC',
    'background' => 'dark',
    'height' => '60vh', // CSS height value
    'autoplay' => true,
    'autoplay_speed' => 5000, // milliseconds
    'show_dots' => true,
    'show_arrows' => true,
    'overlay' => true,
    'animation' => 'fade', // 'slide' or 'fade'
    'section_id' => 'post-carousel-' . wp_rand(1000, 9999)
);

// Merge with provided args
$config = wp_parse_args($args ?? array(), $defaults);
extract($config);

// Build query args
$query_args = array(
    'post_type' => $post_type,
    'posts_per_page' => $posts_per_page,
    'post_status' => 'publish',
    'orderby' => $orderby,
    'order' => $order,
    'meta_query' => $meta_query
);

// Add specific posts if provided
if (!empty($post_ids)) {
    $query_args['post__in'] = $post_ids;
    $query_args['orderby'] = 'post__in';
}

// Add category filter
if (!empty($category)) {
    if (is_numeric($category)) {
        $query_args['cat'] = $category;
    } else {
        $query_args['category_name'] = $category;
    }
}

// Get posts
$carousel_posts = new WP_Query($query_args);

// Debug: Log query details
error_log('Post Carousel Debug - Post Type: ' . $post_type . ', Posts Found: ' . $carousel_posts->found_posts);

if (!$carousel_posts->have_posts()) {
    // Show fallback message instead of silent return
    ?>
    <section class="hph-post-carousel-empty" style="
        padding: 4rem 2rem;
        text-align: center;
        background: <?php echo $background === 'dark' ? '#1a1a1a' : '#f8f9fa'; ?>;
        color: <?php echo $background === 'dark' ? 'white' : '#333'; ?>;
    ">
        <div style="max-width: 600px; margin: 0 auto;">
            <h3 style="margin-bottom: 1rem; color: inherit;">
                No <?php echo ucfirst($post_type); ?>s Found
            </h3>
            <p style="margin-bottom: 2rem; opacity: 0.8;">
                <?php if ($post_type === 'listing'): ?>
                    No listings are available at the moment. Check back soon for featured properties!
                <?php elseif ($post_type === 'agent'): ?>
                    Our team information is being updated. Please check back soon.
                <?php else: ?>
                    No <?php echo $post_type; ?>s are available at the moment.
                <?php endif; ?>
            </p>
            <?php if (current_user_can('manage_options')): ?>
            <p style="background: #ffeaa7; color: #333; padding: 1rem; border-radius: 0.5rem; font-size: 0.9rem;">
                <strong>Admin Note:</strong> Add some <?php echo $post_type; ?>s in the WordPress admin, 
                or check the query parameters in your template.
            </p>
            <?php endif; ?>
        </div>
    </section>
    <?php
    return;
}

// Generate unique ID for this carousel
$carousel_id = $section_id;

// Debug: Add visible debug info for admins
$debug_info = '';
if (current_user_can('manage_options')) {
    $debug_info = "<!-- Post Carousel Debug: Found {$carousel_posts->found_posts} {$post_type}(s) -->";
}
?>

<?php echo $debug_info; ?>

<section class="hph-post-carousel" 
         id="<?php echo esc_attr($carousel_id); ?>" 
         style="
             position: relative;
             height: <?php echo esc_attr($height); ?>;
             background: <?php echo $background === 'dark' ? '#1a1a1a' : '#f8f9fa'; ?>;
             overflow: hidden;
             border: 2px solid red; /* DEBUG: Remove this after testing */
         ">
    
    <!-- Carousel Slides -->
    <div class="hph-carousel-slides" style="position: relative; height: 100%; width: 100%;">
        
        <!-- Debug: Show carousel is loading -->
        <?php if (current_user_can('manage_options')): ?>
        <div style="position: absolute; top: 10px; left: 10px; background: rgba(255,255,0,0.8); padding: 5px; font-size: 12px; z-index: 100; color: black;">
            DEBUG: Carousel loaded with <?php echo $carousel_posts->found_posts; ?> <?php echo $post_type; ?>(s)
        </div>
        <?php endif; ?>
        
        <?php $slide_index = 0; ?>
        <?php while ($carousel_posts->have_posts()) : $carousel_posts->the_post(); ?>
            <div class="hph-carousel-slide <?php echo $slide_index === 0 ? 'active' : ''; ?>" 
                 data-slide="<?php echo $slide_index; ?>"
                 style="
                     position: absolute;
                     top: 0;
                     left: 0;
                     width: 100%;
                     height: 100%;
                     opacity: <?php echo $slide_index === 0 ? '1' : '0'; ?>;
                     transition: opacity 1s ease-in-out;
                     background-size: cover;
                     background-position: center;
                     background-image: url('<?php 
                         // Smart image detection based on post type
                         $carousel_image = '';
                         
                         // Try featured image first
                         $featured_image = get_the_post_thumbnail_url(get_the_ID(), 'full');
                         
                         if ($featured_image) {
                             $carousel_image = $featured_image;
                         } elseif (function_exists('get_field')) {
                             // Try ACF fields based on post type
                             switch ($post_type) {
                                 case 'listing':
                                     $carousel_image = get_field('hero_image') 
                                                    ?: get_field('primary_image') 
                                                    ?: get_field('featured_image')
                                                    ?: get_field('gallery')[0]['url'] ?? '';
                                     break;
                                     
                                 case 'agent':
                                 case 'staff':
                                     $carousel_image = get_field('profile_photo') 
                                                    ?: get_field('headshot') 
                                                    ?: get_field('photo');
                                     break;
                                     
                                 case 'community':
                                 case 'city':
                                     $carousel_image = get_field('hero_image') 
                                                    ?: get_field('featured_image')
                                                    ?: get_field('main_image');
                                     break;
                                     
                                 default:
                                     $carousel_image = get_field('hero_image') 
                                                    ?: get_field('featured_image');
                                     break;
                             }
                         }
                         
                         // Fallback to placeholder based on post type
                         if (empty($carousel_image)) {
                             $placeholders = [
                                 'listing' => 'placeholder-property.jpg',
                                 'agent' => 'placeholder-agent.jpg',
                                 'staff' => 'placeholder-agent.jpg',
                                 'community' => 'community-placeholder.jpg',
                                 'city' => 'city-placeholder.jpg',
                                 'open_house' => 'placeholder-property.jpg'
                             ];
                             
                             $placeholder = $placeholders[$post_type] ?? 'placeholder.jpg';
                             $carousel_image = get_template_directory_uri() . '/assets/images/' . $placeholder;
                         }
                         
                         echo esc_url($carousel_image);
                     ?>');
                 ">
                
                <!-- Overlay -->
                <?php if ($overlay): ?>
                <div class="hph-carousel-overlay" style="
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: linear-gradient(135deg, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0.3) 100%);
                "></div>
                <?php endif; ?>
                
                <!-- Content -->
                <div class="hph-carousel-content" style="
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    text-align: center;
                    color: white;
                    max-width: 800px;
                    padding: 2rem;
                    z-index: 10;
                ">
                    
                    <!-- Category/Badge -->
                    <?php 
                    $badge_text = '';
                    
                    // Get appropriate taxonomy term based on post type
                    switch ($post_type) {
                        case 'listing':
                            $property_types = get_the_terms(get_the_ID(), 'property_type');
                            $listing_types = get_the_terms(get_the_ID(), 'listing_type');
                            $statuses = get_the_terms(get_the_ID(), 'listing_status');
                            
                            if ($statuses && !is_wp_error($statuses)) {
                                $badge_text = $statuses[0]->name; // e.g., "For Sale", "Sold", "Active"
                            } elseif ($property_types && !is_wp_error($property_types)) {
                                $badge_text = $property_types[0]->name; // e.g., "Single Family", "Condo"
                            } elseif ($listing_types && !is_wp_error($listing_types)) {
                                $badge_text = $listing_types[0]->name;
                            }
                            break;
                            
                        case 'agent':
                        case 'staff':
                            $specialties = get_the_terms(get_the_ID(), 'agent_specialty');
                            $departments = get_the_terms(get_the_ID(), 'department');
                            
                            if ($specialties && !is_wp_error($specialties)) {
                                $badge_text = $specialties[0]->name; // e.g., "Buyer's Agent", "Listing Specialist"
                            } elseif ($departments && !is_wp_error($departments)) {
                                $badge_text = $departments[0]->name;
                            } else {
                                $badge_text = ucfirst($post_type); // "Agent" or "Staff"
                            }
                            break;
                            
                        case 'open_house':
                            $badge_text = 'Open House';
                            // Could add date/time here if needed
                            if (function_exists('get_field')) {
                                $event_date = get_field('event_date');
                                if ($event_date) {
                                    $badge_text = 'Open House - ' . date('M j', strtotime($event_date));
                                }
                            }
                            break;
                            
                        case 'community':
                            $community_types = get_the_terms(get_the_ID(), 'community_type');
                            if ($community_types && !is_wp_error($community_types)) {
                                $badge_text = $community_types[0]->name;
                            } else {
                                $badge_text = 'Community';
                            }
                            break;
                            
                        case 'city':
                            $badge_text = 'City Guide';
                            break;
                            
                        default:
                            // Standard post categories
                            $categories = get_the_category();
                            if (!empty($categories)) {
                                $badge_text = $categories[0]->name;
                            }
                            break;
                    }
                    
                    if ($badge_text) : 
                    ?>
                    <span style="
                        display: inline-block;
                        background: var(--hph-primary, #50bae1);
                        color: white;
                        padding: 0.5rem 1rem;
                        border-radius: 2rem;
                        font-size: 0.9rem;
                        font-weight: 600;
                        letter-spacing: 0.05em;
                        text-transform: uppercase;
                        margin-bottom: 1rem;
                    "><?php echo esc_html($badge_text); ?></span>
                    <?php endif; ?>
                    
                    <!-- Title -->
                    <h2 style="
                        font-size: clamp(2rem, 5vw, 4rem);
                        font-weight: 700;
                        line-height: 1.1;
                        margin-bottom: 1rem;
                        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
                    "><?php the_title(); ?></h2>
                    
                    <!-- Excerpt or ACF Content -->
                    <?php 
                    // Smart content detection based on post type
                    $carousel_content = '';
                    
                    if (function_exists('get_field')) {
                        switch ($post_type) {
                            case 'listing':
                                $carousel_content = get_field('property_description') 
                                                ?: get_field('listing_description') 
                                                ?: get_field('short_description')
                                                ?: get_field('description');
                                break;
                                
                            case 'agent':
                            case 'staff':
                                $carousel_content = get_field('bio') 
                                                ?: get_field('biography') 
                                                ?: get_field('about')
                                                ?: get_field('description');
                                break;
                                
                            case 'open_house':
                                $carousel_content = get_field('event_description') 
                                                ?: get_field('details')
                                                ?: get_field('description');
                                break;
                                
                            case 'community':
                            case 'city':
                                $carousel_content = get_field('location_description') 
                                                ?: get_field('overview')
                                                ?: get_field('description');
                                break;
                                
                            default:
                                // Generic fallbacks for any post type
                                $carousel_content = get_field('hero_description') 
                                                ?: get_field('excerpt') 
                                                ?: get_field('description')
                                                ?: get_field('content_excerpt');
                                break;
                        }
                    }
                    
                    // Fallback to WordPress excerpt if no ACF content found
                    if (empty($carousel_content)) {
                        $carousel_content = get_the_excerpt();
                    }
                    
                    if ($carousel_content) :
                    ?>
                    <p style="
                        font-size: 1.25rem;
                        line-height: 1.6;
                        margin-bottom: 2rem;
                        opacity: 0.9;
                        text-shadow: 0 1px 2px rgba(0,0,0,0.3);
                    "><?php echo wp_trim_words($carousel_content, 25); ?></p>
                    <?php endif; ?>
                    
                    <!-- CTA Button -->
                    <?php 
                    // Smart button text and URL detection based on post type
                    $button_text = '';
                    $button_url = get_permalink();
                    
                    // Set default button text based on post type
                    switch ($post_type) {
                        case 'listing':
                            $button_text = 'View Property';
                            break;
                        case 'agent':
                        case 'staff':
                            $button_text = 'View Profile';
                            break;
                        case 'open_house':
                            $button_text = 'Event Details';
                            break;
                        case 'community':
                            $button_text = 'Explore Community';
                            break;
                        case 'city':
                            $button_text = 'Explore City';
                            break;
                        default:
                            $button_text = 'Read More';
                            break;
                    }
                    
                    // Check for ACF button fields (these override defaults)
                    if (function_exists('get_field')) {
                        $acf_button_text = get_field('button_text') 
                                        ?: get_field('cta_text') 
                                        ?: get_field('action_text');
                        $acf_button_url = get_field('button_url') 
                                       ?: get_field('cta_url') 
                                       ?: get_field('external_link');
                        
                        if ($acf_button_text) $button_text = $acf_button_text;
                        if ($acf_button_url) $button_url = $acf_button_url;
                    }
                    
                    // Special handling for listings - check for external listing URL
                    if ($post_type === 'listing' && function_exists('get_field')) {
                        $listing_url = get_field('listing_url') ?: get_field('mls_link') ?: get_field('external_url');
                        if ($listing_url) {
                            $button_url = $listing_url;
                            $button_text = get_field('button_text') ?: 'View Listing';
                        }
                    }
                    ?>
                    <a href="<?php echo esc_url($button_url); ?>" 
                       style="
                           display: inline-flex;
                           align-items: center;
                           gap: 0.5rem;
                           padding: 1rem 2rem;
                           background: var(--hph-primary, #50bae1);
                           color: white;
                           text-decoration: none;
                           border-radius: 0.5rem;
                           font-weight: 600;
                           transition: all 0.3s ease;
                           box-shadow: 0 4px 15px rgba(80, 186, 225, 0.3);
                       "
                       onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(80, 186, 225, 0.4)';"
                       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(80, 186, 225, 0.3)';">
                        <?php echo esc_html($button_text); ?>
                        <i class="fas fa-arrow-right" style="font-size: 0.9rem;"></i>
                    </a>
                </div>
            </div>
            <?php $slide_index++; ?>
        <?php endwhile; ?>
        <?php wp_reset_postdata(); ?>
    </div>
    
    <!-- Navigation Arrows -->
    <?php if ($show_arrows && $carousel_posts->post_count > 1): ?>
    <button class="hph-carousel-prev" 
            onclick="hphCarousel.prev('<?php echo esc_js($carousel_id); ?>')"
            style="
                position: absolute;
                top: 50%;
                left: 2rem;
                transform: translateY(-50%);
                background: rgba(255,255,255,0.2);
                border: none;
                color: white;
                padding: 1rem;
                border-radius: 50%;
                cursor: pointer;
                transition: all 0.3s ease;
                backdrop-filter: blur(10px);
                z-index: 20;
            "
            onmouseover="this.style.background='rgba(255,255,255,0.3)'"
            onmouseout="this.style.background='rgba(255,255,255,0.2)'"
            aria-label="Previous slide">
        <i class="fas fa-chevron-left"></i>
    </button>
    
    <button class="hph-carousel-next" 
            onclick="hphCarousel.next('<?php echo esc_js($carousel_id); ?>')"
            style="
                position: absolute;
                top: 50%;
                right: 2rem;
                transform: translateY(-50%);
                background: rgba(255,255,255,0.2);
                border: none;
                color: white;
                padding: 1rem;
                border-radius: 50%;
                cursor: pointer;
                transition: all 0.3s ease;
                backdrop-filter: blur(10px);
                z-index: 20;
            "
            onmouseover="this.style.background='rgba(255,255,255,0.3)'"
            onmouseout="this.style.background='rgba(255,255,255,0.2)'"
            aria-label="Next slide">
        <i class="fas fa-chevron-right"></i>
    </button>
    <?php endif; ?>
    
    <!-- Dot Indicators -->
    <?php if ($show_dots && $carousel_posts->post_count > 1): ?>
    <div class="hph-carousel-dots" style="
        position: absolute;
        bottom: 2rem;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 0.5rem;
        z-index: 20;
    ">
        <?php for ($i = 0; $i < $carousel_posts->post_count; $i++): ?>
        <button class="hph-carousel-dot <?php echo $i === 0 ? 'active' : ''; ?>" 
                onclick="hphCarousel.goTo('<?php echo esc_js($carousel_id); ?>', <?php echo $i; ?>)"
                data-slide="<?php echo $i; ?>"
                style="
                    width: 12px;
                    height: 12px;
                    border-radius: 50%;
                    border: none;
                    background: <?php echo $i === 0 ? 'white' : 'rgba(255,255,255,0.4)'; ?>;
                    cursor: pointer;
                    transition: all 0.3s ease;
                "
                onmouseover="if(!this.classList.contains('active')) this.style.background='rgba(255,255,255,0.6)'"
                onmouseout="if(!this.classList.contains('active')) this.style.background='rgba(255,255,255,0.4)'"
                aria-label="Go to slide <?php echo $i + 1; ?>"></button>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    
</section>

<!-- Carousel JavaScript -->
<script>
// Debug: Log that script is loading
console.log('Post Carousel Script Loading for:', '<?php echo esc_js($carousel_id); ?>');

// Simple carousel functionality with error handling
window.hphCarousel = window.hphCarousel || {
    timers: {},
    
    init: function(carouselId, autoplay, speed) {
        console.log('Initializing carousel:', carouselId, 'autoplay:', autoplay);
        
        try {
            if (autoplay) {
                this.startAutoplay(carouselId, speed);
            }
            
            // Pause on hover if autoplay is enabled
            const carousel = document.getElementById(carouselId);
            if (carousel && autoplay) {
                carousel.addEventListener('mouseenter', () => this.pauseAutoplay(carouselId));
                carousel.addEventListener('mouseleave', () => this.startAutoplay(carouselId, speed));
            }
            
            // Keyboard navigation
            document.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowLeft') this.prev(carouselId);
                if (e.key === 'ArrowRight') this.next(carouselId);
            });
            
            console.log('Carousel initialized successfully:', carouselId);
        } catch (error) {
            console.error('Error initializing carousel:', error);
        }
    },
    
    next: function(carouselId) {
        const carousel = document.getElementById(carouselId);
        if (!carousel) return;
        
        const slides = carousel.querySelectorAll('.hph-carousel-slide');
        const currentSlide = carousel.querySelector('.hph-carousel-slide.active');
        const currentIndex = Array.from(slides).indexOf(currentSlide);
        const nextIndex = (currentIndex + 1) % slides.length;
        
        this.goTo(carouselId, nextIndex);
    },
    
    prev: function(carouselId) {
        const carousel = document.getElementById(carouselId);
        if (!carousel) return;
        
        const slides = carousel.querySelectorAll('.hph-carousel-slide');
        const currentSlide = carousel.querySelector('.hph-carousel-slide.active');
        const currentIndex = Array.from(slides).indexOf(currentSlide);
        const prevIndex = currentIndex === 0 ? slides.length - 1 : currentIndex - 1;
        
        this.goTo(carouselId, prevIndex);
    },
    
    goTo: function(carouselId, slideIndex) {
        const carousel = document.getElementById(carouselId);
        if (!carousel) return;
        
        const slides = carousel.querySelectorAll('.hph-carousel-slide');
        const dots = carousel.querySelectorAll('.hph-carousel-dot');
        
        // Update slides
        slides.forEach((slide, index) => {
            if (index === slideIndex) {
                slide.style.opacity = '1';
                slide.classList.add('active');
            } else {
                slide.style.opacity = '0';
                slide.classList.remove('active');
            }
        });
        
        // Update dots
        dots.forEach((dot, index) => {
            if (index === slideIndex) {
                dot.style.background = 'white';
                dot.classList.add('active');
            } else {
                dot.style.background = 'rgba(255,255,255,0.4)';
                dot.classList.remove('active');
            }
        });
    },
    
    startAutoplay: function(carouselId, speed) {
        this.pauseAutoplay(carouselId);
        this.timers[carouselId] = setInterval(() => {
            this.next(carouselId);
        }, speed);
    },
    
    pauseAutoplay: function(carouselId) {
        if (this.timers[carouselId]) {
            clearInterval(this.timers[carouselId]);
            delete this.timers[carouselId];
        }
    }
};

// Initialize this carousel
document.addEventListener('DOMContentLoaded', function() {
    hphCarousel.init('<?php echo esc_js($carousel_id); ?>', <?php echo $autoplay ? 'true' : 'false'; ?>, <?php echo intval($autoplay_speed); ?>);
});
</script>
