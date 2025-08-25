<?php
/**
 * Testimonials Carousel Component
 *
 * @package HappyPlaceTheme
 */

// Sample testimonials - in a real implementation, these would come from a custom post type or option
$testimonials = array(
    array(
        'content' => 'Working with the Happy Place team was an amazing experience. They helped us find our dream home in just two weeks!',
        'author' => 'Sarah Johnson',
        'role' => 'Homebuyer',
        'rating' => 5,
        'image' => ''
    ),
    array(
        'content' => 'Professional, knowledgeable, and always available. They sold our house for above asking price in record time.',
        'author' => 'Mike Chen',
        'role' => 'Home Seller',
        'rating' => 5,
        'image' => ''
    ),
    array(
        'content' => 'The market knowledge and negotiation skills of this team are unmatched. Highly recommend for anyone buying or selling.',
        'author' => 'Emily Rodriguez',
        'role' => 'Real Estate Investor',
        'rating' => 5,
        'image' => ''
    ),
    array(
        'content' => 'From start to finish, the process was smooth and stress-free. They truly care about their clients success.',
        'author' => 'David Thompson',
        'role' => 'First-time Homebuyer',
        'rating' => 5,
        'image' => ''
    )
);
?>

<section class="testimonials section bg-gray-50">
    <div class="container">
        <div class="section-header text-center mb-12">
            <h2 class="section-title text-3xl font-bold mb-4"><?php esc_html_e('What Our Clients Say', 'happy-place-theme'); ?></h2>
            <p class="section-subtitle text-gray-600"><?php esc_html_e('Real feedback from real people who trusted us with their real estate needs', 'happy-place-theme'); ?></p>
        </div>
        
        <div class="testimonials-carousel relative" id="testimonials-carousel">
            <div class="testimonials-track flex transition-transform duration-500 ease-in-out">
                <?php foreach ($testimonials as $index => $testimonial) : ?>
                    <div class="testimonial-slide w-full flex-shrink-0 px-4">
                        <div class="testimonial-card card text-center p-8 max-w-2xl mx-auto">
                            <!-- Star Rating -->
                            <div class="testimonial-rating flex justify-center mb-4">
                                <?php for ($i = 1; $i <= 5; $i++) : ?>
                                    <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'text-yellow-400' : 'text-gray-300'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            
                            <!-- Quote -->
                            <blockquote class="testimonial-content text-lg text-gray-700 mb-6 italic">
                                "<?php echo esc_html($testimonial['content']); ?>"
                            </blockquote>
                            
                            <!-- Author Info -->
                            <div class="testimonial-author">
                                <?php if (!empty($testimonial['image'])) : ?>
                                    <img src="<?php echo esc_url($testimonial['image']); ?>" alt="<?php echo esc_attr($testimonial['author']); ?>" class="w-16 h-16 rounded-full mx-auto mb-4">
                                <?php else : ?>
                                    <div class="author-avatar w-16 h-16 bg-primary text-white rounded-full flex items-center justify-center mx-auto mb-4">
                                        <span class="text-xl font-semibold"><?php echo esc_html(substr($testimonial['author'], 0, 1)); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <h4 class="author-name text-lg font-semibold text-gray-900"><?php echo esc_html($testimonial['author']); ?></h4>
                                <p class="author-role text-sm text-gray-600"><?php echo esc_html($testimonial['role']); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Navigation Arrows -->
            <button type="button" class="testimonial-nav testimonial-prev absolute left-4 top-1/2 transform -translate-y-1/2 bg-white shadow-lg rounded-full w-12 h-12 flex items-center justify-center text-gray-600 hover:text-primary transition-colors z-10" onclick="prevTestimonial()">
                <i class="fas fa-chevron-left"></i>
            </button>
            
            <button type="button" class="testimonial-nav testimonial-next absolute right-4 top-1/2 transform -translate-y-1/2 bg-white shadow-lg rounded-full w-12 h-12 flex items-center justify-center text-gray-600 hover:text-primary transition-colors z-10" onclick="nextTestimonial()">
                <i class="fas fa-chevron-right"></i>
            </button>
            
            <!-- Dots Indicator -->
            <div class="testimonial-dots flex justify-center mt-8 space-x-2">
                <?php foreach ($testimonials as $index => $testimonial) : ?>
                    <button type="button" class="testimonial-dot w-3 h-3 rounded-full bg-gray-300 hover:bg-primary transition-colors <?php echo $index === 0 ? 'bg-primary' : ''; ?>" onclick="goToTestimonial(<?php echo $index; ?>)"></button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<script>
let currentTestimonial = 0;
const testimonialCount = <?php echo count($testimonials); ?>;

function updateTestimonialDisplay() {
    const track = document.querySelector('.testimonials-track');
    const dots = document.querySelectorAll('.testimonial-dot');
    
    track.style.transform = `translateX(-${currentTestimonial * 100}%)`;
    
    dots.forEach((dot, index) => {
        dot.classList.toggle('bg-primary', index === currentTestimonial);
        dot.classList.toggle('bg-gray-300', index !== currentTestimonial);
    });
}

function nextTestimonial() {
    currentTestimonial = (currentTestimonial + 1) % testimonialCount;
    updateTestimonialDisplay();
}

function prevTestimonial() {
    currentTestimonial = (currentTestimonial - 1 + testimonialCount) % testimonialCount;
    updateTestimonialDisplay();
}

function goToTestimonial(index) {
    currentTestimonial = index;
    updateTestimonialDisplay();
}

// Auto-advance testimonials
setInterval(nextTestimonial, 5000);

// Touch/swipe support for mobile
let startX = 0;
let endX = 0;

document.querySelector('.testimonials-carousel').addEventListener('touchstart', function(e) {
    startX = e.touches[0].clientX;
});

document.querySelector('.testimonials-carousel').addEventListener('touchmove', function(e) {
    endX = e.touches[0].clientX;
});

document.querySelector('.testimonials-carousel').addEventListener('touchend', function() {
    const threshold = 50;
    const diff = startX - endX;
    
    if (Math.abs(diff) > threshold) {
        if (diff > 0) {
            nextTestimonial();
        } else {
            prevTestimonial();
        }
    }
});
</script>

<style>
/* Hide navigation on small screens to prevent clutter */
@media (max-width: 768px) {
    .testimonial-nav {
        display: none;
    }
}

/* Smooth scrolling for carousel */
.testimonials-track {
    transition: transform 0.5s ease-in-out;
}

/* Testimonial card hover effect */
.testimonial-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.testimonial-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}
</style>
