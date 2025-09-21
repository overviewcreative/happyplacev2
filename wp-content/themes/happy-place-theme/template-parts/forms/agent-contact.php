<?php
/**
 * Agent Contact Form Template
 * Specialized form for contacting specific real estate agents
 * Designed for agent profile pages and agent-specific inquiries
 * 
 * @package HappyPlaceTheme
 */

// Extract arguments
$args = wp_parse_args($args, [
    'agent_id' => 0,
    'listing_id' => 0, // If contacted from a specific listing
    'variant' => 'default',
    'modal_context' => false,
    'title' => __('Contact Agent', 'happy-place-theme'),
    'description' => '',
    'submit_text' => __('Send Message', 'happy-place-theme'),
    'show_agent_info' => true,
    'show_specialties' => true,
    'show_stats' => true,
    'calendly_enabled' => true,
    'css_classes' => ''
]);

// Get agent data
$agent_data = null;
$listing_data = null;

if ($args['agent_id']) {
    $agent_data = [
        'id' => $args['agent_id'],
        'name' => get_the_title($args['agent_id']),
        'email' => get_field('email', $args['agent_id']),
        'phone' => get_field('phone', $args['agent_id']),
        'photo' => get_the_post_thumbnail_url($args['agent_id'], 'medium'),
        'bio' => get_field('bio', $args['agent_id']),
        'specialties' => get_field('specialties', $args['agent_id']),
        'license_number' => get_field('license_number', $args['agent_id']),
        'experience_years' => get_field('experience_years', $args['agent_id']),
        'languages' => get_field('languages_spoken', $args['agent_id']),
        'certifications' => get_field('certifications', $args['agent_id']),
        // Stats (these would come from actual data in production)
        'total_sales' => get_field('total_sales', $args['agent_id']) ?: 0,
        'active_listings' => get_field('active_listings', $args['agent_id']) ?: 0,
        'avg_days_on_market' => get_field('avg_days_on_market', $args['agent_id']) ?: 0
    ];
    
    // Set dynamic title and description if not provided
    if (!$args['modal_context'] && $agent_data['name']) {
        $args['title'] = sprintf(__('Contact %s', 'happy-place-theme'), $agent_data['name']);
        if (!$args['description']) {
            $args['description'] = sprintf(__('Get in touch with %s for expert real estate guidance and personalized service.', 'happy-place-theme'), $agent_data['name']);
        }
    }
}

// Get listing data if provided
if ($args['listing_id']) {
    $listing_data = [
        'id' => $args['listing_id'],
        'title' => get_the_title($args['listing_id']),
        'address' => get_field('street_address', $args['listing_id']),
        'price' => get_field('price', $args['listing_id']),
        'bedrooms' => get_field('bedrooms', $args['listing_id']),
        'bathrooms_full' => get_field('bathrooms_full', $args['listing_id']),
    ];
}

// Form classes
$form_classes = ['hph-form', 'hph-agent-contact-form'];
if ($args['variant'] === 'compact') $form_classes[] = 'hph-form--compact';
if ($args['variant'] === 'modern') $form_classes[] = 'hph-form--modern';
if ($args['modal_context']) $form_classes[] = 'hph-form--modal';
if ($args['css_classes']) $form_classes[] = $args['css_classes'];

// Determine route type
$route_type = 'agent_contact';
if ($args['calendly_enabled'] && $agent_data) {
    $route_type = 'agent_contact_with_booking';
}
?>

<div class="hph-agent-contact-container">
    <?php if (!$args['modal_context']): ?>
    <!-- Form Header -->
    <div class="hph-form-header">
        <h3 class="hph-form-title"><?php echo esc_html($args['title']); ?></h3>
        <?php if ($args['description']): ?>
        <p class="hph-form-description"><?php echo esc_html($args['description']); ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="hph-agent-contact-layout">
        <!-- Agent Information Card -->
        <?php if ($args['show_agent_info'] && $agent_data): ?>
        <div class="hph-agent-profile-card">
            <div class="hph-agent-header">
                <?php if ($agent_data['photo']): ?>
                <div class="hph-agent-photo">
                    <img src="<?php echo esc_url($agent_data['photo']); ?>" 
                         alt="<?php echo esc_attr($agent_data['name']); ?>"
                         class="hph-agent-image">
                </div>
                <?php endif; ?>
                
                <div class="hph-agent-info">
                    <h4 class="hph-agent-name"><?php echo esc_html($agent_data['name']); ?></h4>
                    <p class="hph-agent-title"><?php _e('Licensed Real Estate Agent', 'happy-place-theme'); ?></p>
                    
                    <?php if ($agent_data['license_number']): ?>
                    <p class="hph-agent-license">
                        <?php _e('License #', 'happy-place-theme'); ?><?php echo esc_html($agent_data['license_number']); ?>
                    </p>
                    <?php endif; ?>
                    
                    <div class="hph-agent-contact-methods">
                        <?php if ($agent_data['phone']): ?>
                        <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $agent_data['phone'])); ?>" 
                           class="hph-contact-method">
                            <i class="fas fa-phone"></i>
                            <?php echo esc_html($agent_data['phone']); ?>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($agent_data['email']): ?>
                        <a href="mailto:<?php echo esc_attr($agent_data['email']); ?>" 
                           class="hph-contact-method">
                            <i class="fas fa-envelope"></i>
                            <?php echo esc_html($agent_data['email']); ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($args['show_stats'] && ($agent_data['total_sales'] || $agent_data['active_listings'])): ?>
            <!-- Agent Statistics -->
            <div class="hph-agent-stats">
                <div class="hph-stat-grid">
                    <?php if ($agent_data['total_sales']): ?>
                    <div class="hph-stat-item">
                        <span class="hph-stat-value"><?php echo number_format($agent_data['total_sales']); ?></span>
                        <span class="hph-stat-label"><?php _e('Homes Sold', 'happy-place-theme'); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($agent_data['active_listings']): ?>
                    <div class="hph-stat-item">
                        <span class="hph-stat-value"><?php echo number_format($agent_data['active_listings']); ?></span>
                        <span class="hph-stat-label"><?php _e('Active Listings', 'happy-place-theme'); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($agent_data['experience_years']): ?>
                    <div class="hph-stat-item">
                        <span class="hph-stat-value"><?php echo number_format($agent_data['experience_years']); ?></span>
                        <span class="hph-stat-label"><?php _e('Years Experience', 'happy-place-theme'); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($agent_data['avg_days_on_market']): ?>
                    <div class="hph-stat-item">
                        <span class="hph-stat-value"><?php echo number_format($agent_data['avg_days_on_market']); ?></span>
                        <span class="hph-stat-label"><?php _e('Avg. Days on Market', 'happy-place-theme'); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($args['show_specialties'] && $agent_data['specialties']): ?>
            <!-- Agent Specialties -->
            <div class="hph-agent-specialties">
                <h5 class="hph-specialties-title"><?php _e('Specialties', 'happy-place-theme'); ?></h5>
                <div class="hph-specialties-tags">
                    <?php 
                    $specialties = is_array($agent_data['specialties']) ? $agent_data['specialties'] : explode(',', $agent_data['specialties']);
                    foreach ($specialties as $specialty):
                    ?>
                    <span class="hph-specialty-tag"><?php echo esc_html(trim($specialty)); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($agent_data['languages']): ?>
            <!-- Languages -->
            <div class="hph-agent-languages">
                <h5 class="hph-languages-title"><?php _e('Languages', 'happy-place-theme'); ?></h5>
                <p class="hph-languages-list"><?php echo esc_html($agent_data['languages']); ?></p>
            </div>
            <?php endif; ?>

            <?php if ($agent_data['bio']): ?>
            <!-- Agent Bio -->
            <div class="hph-agent-bio">
                <h5 class="hph-bio-title"><?php _e('About', 'happy-place-theme'); ?></h5>
                <p class="hph-bio-text"><?php echo wp_kses_post($agent_data['bio']); ?></p>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Contact Form Section -->
        <div class="hph-contact-form-section">
            <?php if ($listing_data): ?>
            <!-- Listing Context -->
            <div class="hph-listing-context">
                <div class="hph-context-header">
                    <i class="fas fa-home"></i>
                    <span><?php _e('Regarding:', 'happy-place-theme'); ?></span>
                </div>
                <div class="hph-listing-summary">
                    <h5 class="hph-listing-title"><?php echo esc_html($listing_data['title']); ?></h5>
                    <?php if ($listing_data['address']): ?>
                    <p class="hph-listing-address"><?php echo esc_html($listing_data['address']); ?></p>
                    <?php endif; ?>
                    <?php if ($listing_data['price']): ?>
                    <p class="hph-listing-price">$<?php echo number_format($listing_data['price']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Agent Contact Form -->
            <form 
                class="<?php echo implode(' ', $form_classes); ?>" 
                data-route-type="<?php echo esc_attr($route_type); ?>"
                data-agent-id="<?php echo esc_attr($args['agent_id']); ?>"
                data-listing-id="<?php echo esc_attr($args['listing_id']); ?>"
            >
                <?php wp_nonce_field('hph_route_form_nonce', 'nonce'); ?>
                
                <!-- Hidden Fields -->
                <input type="hidden" name="form_type" value="agent_contact">
                <input type="hidden" name="agent_id" value="<?php echo esc_attr($args['agent_id']); ?>">
                <input type="hidden" name="listing_id" value="<?php echo esc_attr($args['listing_id']); ?>">
                <input type="hidden" name="agent_name" value="<?php echo esc_attr($agent_data['name'] ?? ''); ?>">
                <input type="hidden" name="source_url" value="<?php echo esc_url(get_permalink()); ?>">

                <div class="hph-form-row">
                    <!-- First Name -->
                    <div class="hph-form-group hph-form-col--half">
                        <label for="agent-contact-first-name" class="hph-form-label">
                            <?php _e('First Name', 'happy-place-theme'); ?>
                            <span class="hph-required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="agent-contact-first-name" 
                            name="first_name" 
                            class="hph-form-input" 
                            required 
                            placeholder="<?php _e('John', 'happy-place-theme'); ?>"
                        >
                    </div>

                    <!-- Last Name -->
                    <div class="hph-form-group hph-form-col--half">
                        <label for="agent-contact-last-name" class="hph-form-label">
                            <?php _e('Last Name', 'happy-place-theme'); ?>
                            <span class="hph-required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="agent-contact-last-name" 
                            name="last_name" 
                            class="hph-form-input" 
                            required 
                            placeholder="<?php _e('Smith', 'happy-place-theme'); ?>"
                        >
                    </div>
                </div>

                <div class="hph-form-row">
                    <!-- Email -->
                    <div class="hph-form-group hph-form-col--half">
                        <label for="agent-contact-email" class="hph-form-label">
                            <?php _e('Email', 'happy-place-theme'); ?>
                            <span class="hph-required">*</span>
                        </label>
                        <input 
                            type="email" 
                            id="agent-contact-email" 
                            name="email" 
                            class="hph-form-input" 
                            required 
                            placeholder="<?php _e('john@example.com', 'happy-place-theme'); ?>"
                        >
                    </div>

                    <!-- Phone -->
                    <div class="hph-form-group hph-form-col--half">
                        <label for="agent-contact-phone" class="hph-form-label">
                            <?php _e('Phone', 'happy-place-theme'); ?>
                        </label>
                        <input 
                            type="tel" 
                            id="agent-contact-phone" 
                            name="phone" 
                            class="hph-form-input" 
                            placeholder="<?php _e('(555) 123-4567', 'happy-place-theme'); ?>"
                        >
                    </div>
                </div>

                <!-- Inquiry Type -->
                <div class="hph-form-group">
                    <label for="inquiry-type" class="hph-form-label">
                        <?php _e('How can I help you?', 'happy-place-theme'); ?>
                    </label>
                    <select id="inquiry-type" name="inquiry_type" class="hph-form-select">
                        <option value=""><?php _e('Select inquiry type', 'happy-place-theme'); ?></option>
                        <?php if ($listing_data): ?>
                        <option value="specific-property"><?php _e('This specific property', 'happy-place-theme'); ?></option>
                        <?php endif; ?>
                        <option value="buying"><?php _e('I\'m looking to buy', 'happy-place-theme'); ?></option>
                        <option value="selling"><?php _e('I\'m looking to sell', 'happy-place-theme'); ?></option>
                        <option value="both"><?php _e('I\'m buying and selling', 'happy-place-theme'); ?></option>
                        <option value="renting"><?php _e('Rental properties', 'happy-place-theme'); ?></option>
                        <option value="investment"><?php _e('Investment opportunities', 'happy-place-theme'); ?></option>
                        <option value="market-analysis"><?php _e('Market analysis', 'happy-place-theme'); ?></option>
                        <option value="consultation"><?php _e('General consultation', 'happy-place-theme'); ?></option>
                        <option value="other"><?php _e('Other', 'happy-place-theme'); ?></option>
                    </select>
                </div>

                <!-- Timeline -->
                <div class="hph-form-group">
                    <label for="timeline" class="hph-form-label">
                        <?php _e('Timeline', 'happy-place-theme'); ?>
                    </label>
                    <select id="timeline" name="timeline" class="hph-form-select">
                        <option value=""><?php _e('Select timeline', 'happy-place-theme'); ?></option>
                        <option value="immediately"><?php _e('Ready immediately', 'happy-place-theme'); ?></option>
                        <option value="1-month"><?php _e('Within 1 month', 'happy-place-theme'); ?></option>
                        <option value="3-months"><?php _e('Within 3 months', 'happy-place-theme'); ?></option>
                        <option value="6-months"><?php _e('Within 6 months', 'happy-place-theme'); ?></option>
                        <option value="over-year"><?php _e('Over a year', 'happy-place-theme'); ?></option>
                        <option value="exploring"><?php _e('Just exploring options', 'happy-place-theme'); ?></option>
                    </select>
                </div>

                <!-- Budget Range (for buyers) -->
                <div class="hph-form-group" id="budget-group" style="display: none;">
                    <label for="budget-range" class="hph-form-label">
                        <?php _e('Budget Range', 'happy-place-theme'); ?>
                    </label>
                    <select id="budget-range" name="budget_range" class="hph-form-select">
                        <option value=""><?php _e('Select budget range', 'happy-place-theme'); ?></option>
                        <option value="under-200k"><?php _e('Under $200,000', 'happy-place-theme'); ?></option>
                        <option value="200k-300k"><?php _e('$200,000 - $300,000', 'happy-place-theme'); ?></option>
                        <option value="300k-400k"><?php _e('$300,000 - $400,000', 'happy-place-theme'); ?></option>
                        <option value="400k-500k"><?php _e('$400,000 - $500,000', 'happy-place-theme'); ?></option>
                        <option value="500k-750k"><?php _e('$500,000 - $750,000', 'happy-place-theme'); ?></option>
                        <option value="750k-1m"><?php _e('$750,000 - $1,000,000', 'happy-place-theme'); ?></option>
                        <option value="over-1m"><?php _e('Over $1,000,000', 'happy-place-theme'); ?></option>
                        <option value="flexible"><?php _e('Flexible/Depends on property', 'happy-place-theme'); ?></option>
                    </select>
                </div>

                <!-- Message -->
                <div class="hph-form-group">
                    <label for="agent-message" class="hph-form-label">
                        <?php _e('Message', 'happy-place-theme'); ?>
                        <span class="hph-required">*</span>
                    </label>
                    <textarea 
                        id="agent-message" 
                        name="message" 
                        class="hph-form-textarea" 
                        rows="5" 
                        required 
                        placeholder="<?php 
                        if ($listing_data) {
                            printf(__('Hi %s, I\'m interested in the property at %s. Could you please provide more information?', 'happy-place-theme'), 
                                   $agent_data['name'] ?? 'there', 
                                   $listing_data['address'] ?? 'this location');
                        } else {
                            printf(__('Hi %s, I would like to discuss my real estate needs with you...', 'happy-place-theme'), 
                                   $agent_data['name'] ?? 'there');
                        }
                        ?>"
                    ></textarea>
                </div>

                <!-- Communication Preferences -->
                <div class="hph-form-group">
                    <label class="hph-form-label">
                        <?php _e('Preferred Contact Method', 'happy-place-theme'); ?>
                    </label>
                    <div class="hph-checkbox-group">
                        <label class="hph-form-check">
                            <input type="checkbox" name="contact_methods[]" value="email" class="hph-form-check-input" checked>
                            <span class="hph-form-check-label"><?php _e('Email', 'happy-place-theme'); ?></span>
                        </label>
                        <label class="hph-form-check">
                            <input type="checkbox" name="contact_methods[]" value="phone" class="hph-form-check-input">
                            <span class="hph-form-check-label"><?php _e('Phone Call', 'happy-place-theme'); ?></span>
                        </label>
                        <label class="hph-form-check">
                            <input type="checkbox" name="contact_methods[]" value="text" class="hph-form-check-input">
                            <span class="hph-form-check-label"><?php _e('Text Message', 'happy-place-theme'); ?></span>
                        </label>
                    </div>
                </div>

                <!-- Best Time to Contact -->
                <div class="hph-form-group">
                    <label for="best-time" class="hph-form-label">
                        <?php _e('Best Time to Contact', 'happy-place-theme'); ?>
                    </label>
                    <select id="best-time" name="best_time" class="hph-form-select">
                        <option value=""><?php _e('Any time', 'happy-place-theme'); ?></option>
                        <option value="morning"><?php _e('Morning (9am - 12pm)', 'happy-place-theme'); ?></option>
                        <option value="afternoon"><?php _e('Afternoon (12pm - 5pm)', 'happy-place-theme'); ?></option>
                        <option value="evening"><?php _e('Evening (5pm - 8pm)', 'happy-place-theme'); ?></option>
                        <option value="weekends"><?php _e('Weekends only', 'happy-place-theme'); ?></option>
                    </select>
                </div>

                <!-- Quick Actions -->
                <div class="hph-form-actions">
                    <div class="hph-primary-action">
                        <button type="submit" class="hph-btn hph-btn-primary w-full">
                            <i class="fas fa-paper-plane"></i>
                            <?php echo esc_html($args['submit_text']); ?>
                        </button>
                    </div>
                    
                    <?php if ($args['calendly_enabled'] && $agent_data): ?>
                    <div class="hph-secondary-actions">
                        <button type="button" class="hph-btn hph-btn-outline hph-calendly-trigger"
                                data-agent-id="<?php echo esc_attr($args['agent_id']); ?>"
                                data-listing-id="<?php echo esc_attr($args['listing_id']); ?>">
                            <i class="fas fa-calendar-alt"></i>
                            <?php _e('Schedule Meeting', 'happy-place-theme'); ?>
                        </button>
                        
                        <?php if ($agent_data['phone']): ?>
                        <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $agent_data['phone'])); ?>" 
                           class="hph-btn hph-btn-outline">
                            <i class="fas fa-phone"></i>
                            <?php _e('Call Now', 'happy-place-theme'); ?>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Loading State -->
                <div class="hph-form-loading" style="display: none;">
                    <div class="hph-loading-spinner"></div>
                    <span><?php _e('Sending your message...', 'happy-place-theme'); ?></span>
                </div>

                <!-- Success Message -->
                <div class="hph-form-success" style="display: none;">
                    <div class="hph-success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h4><?php _e('Message Sent Successfully!', 'happy-place-theme'); ?></h4>
                    <p><?php printf(__('Thank you for contacting %s. You\'ll hear back within a few hours.', 'happy-place-theme'), $agent_data['name'] ?? 'our agent'); ?></p>
                    
                    <?php if ($args['calendly_enabled']): ?>
                    <div class="hph-followup-actions">
                        <button type="button" class="hph-btn hph-btn-primary hph-calendly-trigger">
                            <i class="fas fa-calendar-alt"></i>
                            <?php _e('Schedule a Meeting', 'happy-place-theme'); ?>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Inline CSS for agent contact specific styles -->
<style>
.hph-agent-contact-container {
    max-width: 1000px;
    margin: 0 auto;
}

.hph-agent-contact-layout {
    display: grid;
    gap: 2rem;
    grid-template-columns: 1fr;
}

.hph-agent-profile-card {
    background: var(--hph-white);
    border: 1px solid var(--hph-border-color-light);
    border-radius: var(--hph-radius-lg);
    padding: 2rem;
    box-shadow: var(--hph-shadow-sm);
}

.hph-agent-header {
    display: flex;
    align-items: flex-start;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.hph-agent-photo {
    flex-shrink: 0;
}

.hph-agent-image {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid var(--hph-primary-100);
}

.hph-agent-name {
    font-size: var(--hph-text-2xl);
    font-weight: 700;
    color: var(--hph-gray-900);
    margin: 0 0 0.5rem 0;
}

.hph-agent-title {
    font-size: var(--hph-text-base);
    color: var(--hph-primary);
    font-weight: 600;
    margin: 0 0 0.25rem 0;
}

.hph-agent-license {
    font-size: var(--hph-text-sm);
    color: var(--hph-gray-600);
    margin: 0 0 1rem 0;
}

.hph-agent-contact-methods {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.hph-contact-method {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: var(--hph-gray-700);
    text-decoration: none;
    font-size: var(--hph-text-sm);
    transition: color 0.2s ease;
}

.hph-contact-method:hover {
    color: var(--hph-primary);
}

.hph-contact-method i {
    width: 18px;
    color: var(--hph-primary);
}

.hph-agent-stats {
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: var(--hph-gray-50);
    border-radius: var(--hph-radius-md);
    border: 1px solid var(--hph-border-color-light);
}

.hph-stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 1rem;
}

.hph-stat-item {
    text-align: center;
}

.hph-stat-value {
    display: block;
    font-size: var(--hph-text-xl);
    font-weight: 700;
    color: var(--hph-primary);
    margin-bottom: 0.25rem;
}

.hph-stat-label {
    font-size: var(--hph-text-xs);
    color: var(--hph-gray-600);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.hph-agent-specialties,
.hph-agent-languages {
    margin-bottom: 1.5rem;
}

.hph-specialties-title,
.hph-languages-title,
.hph-bio-title {
    font-size: var(--hph-text-base);
    font-weight: 600;
    color: var(--hph-gray-900);
    margin: 0 0 0.75rem 0;
}

.hph-specialties-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.hph-specialty-tag {
    background: var(--hph-primary-100);
    color: var(--hph-primary-800);
    padding: 0.25rem 0.75rem;
    border-radius: var(--hph-radius-full);
    font-size: var(--hph-text-xs);
    font-weight: 500;
}

.hph-languages-list {
    color: var(--hph-gray-700);
    margin: 0;
    font-size: var(--hph-text-sm);
}

.hph-agent-bio {
    margin-bottom: 1.5rem;
}

.hph-bio-text {
    color: var(--hph-gray-700);
    line-height: 1.6;
    margin: 0;
    font-size: var(--hph-text-sm);
}

.hph-listing-context {
    background: var(--hph-primary-50);
    border: 1px solid var(--hph-primary-200);
    border-radius: var(--hph-radius-md);
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.hph-context-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: var(--hph-text-sm);
    font-weight: 600;
    color: var(--hph-primary-800);
    margin-bottom: 1rem;
}

.hph-context-header i {
    color: var(--hph-primary);
}

.hph-listing-title {
    font-size: var(--hph-text-lg);
    font-weight: 600;
    color: var(--hph-primary-900);
    margin: 0 0 0.5rem 0;
}

.hph-listing-address {
    font-size: var(--hph-text-sm);
    color: var(--hph-primary-700);
    margin: 0 0 0.5rem 0;
}

.hph-listing-price {
    font-size: var(--hph-text-lg);
    font-weight: 700;
    color: var(--hph-primary);
    margin: 0;
}

.hph-checkbox-group {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.hph-form-actions {
    margin-top: 2rem;
}

.hph-primary-action {
    margin-bottom: 1rem;
}

.hph-secondary-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.hph-followup-actions {
    margin-top: 1rem;
    text-align: center;
}

@media (min-width: 768px) {
    .hph-agent-contact-layout {
        grid-template-columns: 1fr 1.5fr;
    }
    
    .hph-secondary-actions .hph-btn {
        flex: 1;
    }
}

@media (max-width: 768px) {
    .hph-agent-header {
        flex-direction: column;
        text-align: center;
    }
    
    .hph-agent-image {
        width: 80px;
        height: 80px;
    }
    
    .hph-stat-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .hph-secondary-actions {
        flex-direction: column;
    }
    
    .hph-checkbox-group {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show budget field for buying inquiries
    const inquiryType = document.getElementById('inquiry-type');
    const budgetGroup = document.getElementById('budget-group');
    
    if (inquiryType && budgetGroup) {
        inquiryType.addEventListener('change', function() {
            if (this.value === 'buying' || this.value === 'both') {
                budgetGroup.style.display = 'block';
            } else {
                budgetGroup.style.display = 'none';
            }
        });
    }
    
    // Auto-populate message based on inquiry type
    const messageField = document.getElementById('agent-message');
    const agentName = '<?php echo esc_js($agent_data['name'] ?? ''); ?>';
    
    if (inquiryType && messageField) {
        inquiryType.addEventListener('change', function() {
            let defaultMessage = '';
            
            switch(this.value) {
                case 'buying':
                    defaultMessage = `Hi ${agentName}, I'm looking to buy a home and would like to discuss my options with you.`;
                    break;
                case 'selling':
                    defaultMessage = `Hi ${agentName}, I'm interested in selling my property and would like to get your expert advice.`;
                    break;
                case 'both':
                    defaultMessage = `Hi ${agentName}, I'm looking to sell my current home and buy a new one. I'd like to discuss how you can help with both transactions.`;
                    break;
                case 'investment':
                    defaultMessage = `Hi ${agentName}, I'm interested in investment properties in this area. Could we discuss available opportunities?`;
                    break;
                default:
                    return;
            }
            
            if (messageField.value.trim() === '' || messageField.hasAttribute('data-default')) {
                messageField.value = defaultMessage;
                messageField.setAttribute('data-default', 'true');
            }
        });
        
        messageField.addEventListener('input', function() {
            this.removeAttribute('data-default');
        });
    }
});
</script>
