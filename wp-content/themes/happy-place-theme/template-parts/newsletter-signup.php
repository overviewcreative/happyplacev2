<?php
/**
 * Newsletter Signup Component
 *
 * @package HappyPlaceTheme
 */
?>

<div class="newsletter-signup bg-gradient-to-r from-primary to-primary-dark text-white rounded-lg p-6">
    <div class="text-center mb-6">
        <h3 class="text-xl font-semibold mb-2"><?php esc_html_e('Stay in the Loop', 'happy-place-theme'); ?></h3>
        <p class="text-sm opacity-90"><?php esc_html_e('Get the latest listings and market updates delivered to your inbox', 'happy-place-theme'); ?></p>
    </div>
    
    <form class="newsletter-form" method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('newsletter_signup', 'newsletter_nonce'); ?>
        <input type="hidden" name="action" value="newsletter_signup">
        
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <label for="newsletter-email" class="sr-only"><?php esc_html_e('Email Address', 'happy-place-theme'); ?></label>
                <input 
                    type="email" 
                    id="newsletter-email" 
                    name="email" 
                    placeholder="<?php esc_attr_e('Enter your email address', 'happy-place-theme'); ?>" 
                    class="hph-form-input w-full bg-white bg-opacity-20 border-white border-opacity-30 text-white placeholder-white placeholder-opacity-70 focus:bg-opacity-30"
                    required
                >
            </div>
            <button type="submit" class="hph-btn hph-btn-secondary px-6 whitespace-nowrap">
                <?php esc_html_e('Subscribe', 'happy-place-theme'); ?>
            </button>
        </div>
        
        <div class="flex items-center mt-3">
            <input type="checkbox" id="newsletter-terms" name="terms" class="mr-2" required>
            <label for="newsletter-terms" class="text-xs opacity-90">
                <?php esc_html_e('I agree to receive marketing emails and can unsubscribe at any time.', 'happy-place-theme'); ?>
            </label>
        </div>
    </form>
</div>

<style>
/* Newsletter Signup Specific Styles */
.newsletter-signup input[type="email"]::placeholder {
    color: rgba(255, 255, 255, 0.7);
}

.newsletter-signup input[type="email"]:focus {
    background-color: rgba(255, 255, 255, 0.3);
    border-color: rgba(255, 255, 255, 0.5);
    outline: none;
    box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.2);
}

@media (max-width: 640px) {
    .newsletter-signup .btn {
        justify-content: center;
    }
}
</style>
