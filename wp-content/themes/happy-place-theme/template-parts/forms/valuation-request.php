<?php
/**
 * Property Valuation Request Form Template
 * Specialized form for home valuation and CMA requests
 * Designed for seller leads and property evaluation inquiries
 * 
 * @package HappyPlaceTheme
 */

// Extract arguments
$args = wp_parse_args($args, [
    'variant' => 'default',
    'modal_context' => false,
    'title' => __('Get Your Home\'s Value', 'happy-place-theme'),
    'description' => __('Receive a comprehensive market analysis of your property value from our expert team.', 'happy-place-theme'),
    'submit_text' => __('Request Free Valuation', 'happy-place-theme'),
    'show_value_estimate' => true,
    'show_market_trends' => true,
    'cma_enabled' => true,
    'instant_estimate' => false,
    'css_classes' => ''
]);

// Form classes
$form_classes = ['hph-form', 'hph-valuation-form'];
if ($args['variant'] === 'compact') $form_classes[] = 'hph-form--compact';
if ($args['variant'] === 'modern') $form_classes[] = 'hph-form--modern';
if ($args['modal_context']) $form_classes[] = 'hph-form--modal';
if ($args['css_classes']) $form_classes[] = $args['css_classes'];

// Determine route type based on features
$route_type = 'valuation_request';
if ($args['cma_enabled']) {
    $route_type = 'valuation_with_cma';
}
?>

<div class="hph-valuation-form-container">
    <?php if (!$args['modal_context']): ?>
    <!-- Form Header -->
    <div class="hph-form-header">
        <h3 class="hph-form-title"><?php echo esc_html($args['title']); ?></h3>
        <?php if ($args['description']): ?>
        <p class="hph-form-description"><?php echo esc_html($args['description']); ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($args['show_value_estimate']): ?>
    <!-- Value Estimate Teaser -->
    <div class="hph-value-teaser">
        <div class="hph-estimate-preview">
            <div class="hph-estimate-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="hph-estimate-content">
                <h4 class="hph-estimate-title"><?php _e('What You\'ll Receive:', 'happy-place-theme'); ?></h4>
                <ul class="hph-estimate-features">
                    <li><i class="fas fa-check"></i> <?php _e('Current market value estimate', 'happy-place-theme'); ?></li>
                    <li><i class="fas fa-check"></i> <?php _e('Comparable recent sales analysis', 'happy-place-theme'); ?></li>
                    <li><i class="fas fa-check"></i> <?php _e('Market trends and pricing insights', 'happy-place-theme'); ?></li>
                    <li><i class="fas fa-check"></i> <?php _e('Professional consultation included', 'happy-place-theme'); ?></li>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Valuation Request Form -->
    <form 
        class="<?php echo implode(' ', $form_classes); ?>" 
        data-route-type="<?php echo esc_attr($route_type); ?>"
        data-form-context="valuation"
    >
        <?php wp_nonce_field('hph_route_form_nonce', 'nonce'); ?>
        
        <!-- Hidden Fields -->
        <input type="hidden" name="form_type" value="valuation_request">
        <input type="hidden" name="source_url" value="<?php echo esc_url(get_permalink()); ?>">
        <input type="hidden" name="cma_enabled" value="<?php echo $args['cma_enabled'] ? '1' : '0'; ?>">

        <!-- Property Information Section -->
        <div class="hph-form-section">
            <h4 class="hph-form-section-title">
                <i class="fas fa-home"></i>
                <?php _e('Property Information', 'happy-place-theme'); ?>
            </h4>

            <!-- Property Address -->
            <div class="hph-form-group">
                <label for="property-address" class="hph-form-label">
                    <?php _e('Property Address', 'happy-place-theme'); ?>
                    <span class="hph-required">*</span>
                </label>
                <input 
                    type="text" 
                    id="property-address" 
                    name="property_address" 
                    class="hph-form-input" 
                    required 
                    placeholder="<?php _e('123 Main Street, City, State, ZIP', 'happy-place-theme'); ?>"
                >
                <div class="hph-form-help">
                    <?php _e('Enter the complete address of the property you want valued', 'happy-place-theme'); ?>
                </div>
            </div>

            <div class="hph-form-row">
                <!-- City -->
                <div class="hph-form-group hph-form-col--half">
                    <label for="property-city" class="hph-form-label">
                        <?php _e('City', 'happy-place-theme'); ?>
                        <span class="hph-required">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="property-city" 
                        name="property_city" 
                        class="hph-form-input" 
                        required 
                        placeholder="<?php _e('City', 'happy-place-theme'); ?>"
                    >
                </div>

                <!-- ZIP Code -->
                <div class="hph-form-group hph-form-col--half">
                    <label for="property-zip" class="hph-form-label">
                        <?php _e('ZIP Code', 'happy-place-theme'); ?>
                        <span class="hph-required">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="property-zip" 
                        name="property_zip" 
                        class="hph-form-input" 
                        required 
                        placeholder="<?php _e('12345', 'happy-place-theme'); ?>"
                        pattern="[0-9]{5}(-[0-9]{4})?"
                    >
                </div>
            </div>

            <div class="hph-form-row">
                <!-- Property Type -->
                <div class="hph-form-group hph-form-col--half">
                    <label for="property-type" class="hph-form-label">
                        <?php _e('Property Type', 'happy-place-theme'); ?>
                        <span class="hph-required">*</span>
                    </label>
                    <select id="property-type" name="property_type" class="hph-form-select" required>
                        <option value=""><?php _e('Select Property Type', 'happy-place-theme'); ?></option>
                        <option value="single-family"><?php _e('Single Family Home', 'happy-place-theme'); ?></option>
                        <option value="condo"><?php _e('Condominium', 'happy-place-theme'); ?></option>
                        <option value="townhouse"><?php _e('Townhouse', 'happy-place-theme'); ?></option>
                        <option value="multi-family"><?php _e('Multi-Family (2-4 units)', 'happy-place-theme'); ?></option>
                        <option value="mobile-home"><?php _e('Mobile/Manufactured Home', 'happy-place-theme'); ?></option>
                        <option value="land"><?php _e('Vacant Land', 'happy-place-theme'); ?></option>
                        <option value="other"><?php _e('Other', 'happy-place-theme'); ?></option>
                    </select>
                </div>

                <!-- Ownership Status -->
                <div class="hph-form-group hph-form-col--half">
                    <label for="ownership-status" class="hph-form-label">
                        <?php _e('I am the', 'happy-place-theme'); ?>
                    </label>
                    <select id="ownership-status" name="ownership_status" class="hph-form-select">
                        <option value="owner"><?php _e('Property Owner', 'happy-place-theme'); ?></option>
                        <option value="potential-buyer"><?php _e('Potential Buyer', 'happy-place-theme'); ?></option>
                        <option value="agent"><?php _e('Real Estate Agent', 'happy-place-theme'); ?></option>
                        <option value="other"><?php _e('Other', 'happy-place-theme'); ?></option>
                    </select>
                </div>
            </div>

            <div class="hph-form-row">
                <!-- Bedrooms -->
                <div class="hph-form-group hph-form-col--third">
                    <label for="bedrooms" class="hph-form-label">
                        <?php _e('Bedrooms', 'happy-place-theme'); ?>
                    </label>
                    <select id="bedrooms" name="bedrooms" class="hph-form-select">
                        <option value=""><?php _e('Select', 'happy-place-theme'); ?></option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6+">6+</option>
                    </select>
                </div>

                <!-- Bathrooms -->
                <div class="hph-form-group hph-form-col--third">
                    <label for="bathrooms" class="hph-form-label">
                        <?php _e('Bathrooms', 'happy-place-theme'); ?>
                    </label>
                    <select id="bathrooms" name="bathrooms" class="hph-form-select">
                        <option value=""><?php _e('Select', 'happy-place-theme'); ?></option>
                        <option value="1">1</option>
                        <option value="1.5">1.5</option>
                        <option value="2">2</option>
                        <option value="2.5">2.5</option>
                        <option value="3">3</option>
                        <option value="3.5">3.5</option>
                        <option value="4+">4+</option>
                    </select>
                </div>

                <!-- Square Footage -->
                <div class="hph-form-group hph-form-col--third">
                    <label for="square-feet" class="hph-form-label">
                        <?php _e('Square Feet', 'happy-place-theme'); ?>
                    </label>
                    <input 
                        type="number" 
                        id="square-feet" 
                        name="square_feet" 
                        class="hph-form-input" 
                        placeholder="<?php _e('2000', 'happy-place-theme'); ?>"
                        min="0" 
                        step="50"
                    >
                </div>
            </div>

            <div class="hph-form-row">
                <!-- Year Built -->
                <div class="hph-form-group hph-form-col--half">
                    <label for="year-built" class="hph-form-label">
                        <?php _e('Year Built', 'happy-place-theme'); ?>
                    </label>
                    <input 
                        type="number" 
                        id="year-built" 
                        name="year_built" 
                        class="hph-form-input" 
                        placeholder="<?php echo date('Y'); ?>" 
                        min="1800" 
                        max="<?php echo date('Y') + 2; ?>"
                    >
                </div>

                <!-- Lot Size -->
                <div class="hph-form-group hph-form-col--half">
                    <label for="lot-size" class="hph-form-label">
                        <?php _e('Lot Size', 'happy-place-theme'); ?>
                    </label>
                    <div class="hph-input-group">
                        <input 
                            type="number" 
                            id="lot-size" 
                            name="lot_size" 
                            class="hph-form-input" 
                            placeholder="0.25" 
                            min="0" 
                            step="0.01"
                        >
                        <span class="hph-input-suffix"><?php _e('acres', 'happy-place-theme'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Property Condition -->
            <div class="hph-form-group">
                <label for="property-condition" class="hph-form-label">
                    <?php _e('Property Condition', 'happy-place-theme'); ?>
                </label>
                <select id="property-condition" name="property_condition" class="hph-form-select">
                    <option value=""><?php _e('Select Condition', 'happy-place-theme'); ?></option>
                    <option value="excellent"><?php _e('Excellent - Move-in Ready', 'happy-place-theme'); ?></option>
                    <option value="good"><?php _e('Good - Minor Updates Needed', 'happy-place-theme'); ?></option>
                    <option value="fair"><?php _e('Fair - Some Renovations Required', 'happy-place-theme'); ?></option>
                    <option value="needs-work"><?php _e('Needs Work - Major Renovations Required', 'happy-place-theme'); ?></option>
                    <option value="fixer-upper"><?php _e('Fixer Upper', 'happy-place-theme'); ?></option>
                </select>
            </div>

            <!-- Recent Improvements -->
            <div class="hph-form-group">
                <label class="hph-form-label">
                    <?php _e('Recent Improvements (Select all that apply)', 'happy-place-theme'); ?>
                </label>
                <div class="hph-checkbox-grid">
                    <?php
                    $improvements = [
                        'kitchen_renovation' => __('Kitchen Renovation', 'happy-place-theme'),
                        'bathroom_remodel' => __('Bathroom Remodel', 'happy-place-theme'),
                        'new_flooring' => __('New Flooring', 'happy-place-theme'),
                        'new_roof' => __('New Roof', 'happy-place-theme'),
                        'hvac_system' => __('HVAC System', 'happy-place-theme'),
                        'windows_doors' => __('Windows/Doors', 'happy-place-theme'),
                        'painting' => __('Fresh Paint', 'happy-place-theme'),
                        'landscaping' => __('Landscaping', 'happy-place-theme'),
                        'electrical' => __('Electrical Updates', 'happy-place-theme'),
                        'plumbing' => __('Plumbing Updates', 'happy-place-theme'),
                        'insulation' => __('Insulation', 'happy-place-theme'),
                        'appliances' => __('New Appliances', 'happy-place-theme'),
                    ];
                    
                    foreach ($improvements as $key => $label):
                    ?>
                        <div class="hph-form-check">
                            <input type="checkbox" name="improvements[]" value="<?php echo esc_attr($key); ?>" class="hph-form-check-input">
                            <label class="hph-form-check-label"><?php echo esc_html($label); ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Contact Information Section -->
        <div class="hph-form-section">
            <h4 class="hph-form-section-title">
                <i class="fas fa-user"></i>
                <?php _e('Contact Information', 'happy-place-theme'); ?>
            </h4>

            <div class="hph-form-row">
                <!-- First Name -->
                <div class="hph-form-group hph-form-col--half">
                    <label for="first-name" class="hph-form-label">
                        <?php _e('First Name', 'happy-place-theme'); ?>
                        <span class="hph-required">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="first-name" 
                        name="first_name" 
                        class="hph-form-input" 
                        required 
                        placeholder="<?php _e('John', 'happy-place-theme'); ?>"
                    >
                </div>

                <!-- Last Name -->
                <div class="hph-form-group hph-form-col--half">
                    <label for="last-name" class="hph-form-label">
                        <?php _e('Last Name', 'happy-place-theme'); ?>
                        <span class="hph-required">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="last-name" 
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
                    <label for="email" class="hph-form-label">
                        <?php _e('Email Address', 'happy-place-theme'); ?>
                        <span class="hph-required">*</span>
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="hph-form-input" 
                        required 
                        placeholder="<?php _e('john@example.com', 'happy-place-theme'); ?>"
                    >
                </div>

                <!-- Phone -->
                <div class="hph-form-group hph-form-col--half">
                    <label for="phone" class="hph-form-label">
                        <?php _e('Phone Number', 'happy-place-theme'); ?>
                        <span class="hph-required">*</span>
                    </label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone" 
                        class="hph-form-input" 
                        required 
                        placeholder="<?php _e('(555) 123-4567', 'happy-place-theme'); ?>"
                    >
                </div>
            </div>
        </div>

        <!-- Valuation Purpose Section -->
        <div class="hph-form-section">
            <h4 class="hph-form-section-title">
                <i class="fas fa-target"></i>
                <?php _e('Valuation Purpose', 'happy-place-theme'); ?>
            </h4>

            <!-- Purpose -->
            <div class="hph-form-group">
                <label for="valuation-purpose" class="hph-form-label">
                    <?php _e('Why do you need this valuation?', 'happy-place-theme'); ?>
                </label>
                <select id="valuation-purpose" name="valuation_purpose" class="hph-form-select">
                    <option value=""><?php _e('Select Purpose', 'happy-place-theme'); ?></option>
                    <option value="selling"><?php _e('Planning to sell', 'happy-place-theme'); ?></option>
                    <option value="refinancing"><?php _e('Refinancing', 'happy-place-theme'); ?></option>
                    <option value="curiosity"><?php _e('Just curious about current value', 'happy-place-theme'); ?></option>
                    <option value="insurance"><?php _e('Insurance purposes', 'happy-place-theme'); ?></option>
                    <option value="estate-planning"><?php _e('Estate planning', 'happy-place-theme'); ?></option>
                    <option value="divorce"><?php _e('Divorce settlement', 'happy-place-theme'); ?></option>
                    <option value="investment"><?php _e('Investment analysis', 'happy-place-theme'); ?></option>
                    <option value="other"><?php _e('Other', 'happy-place-theme'); ?></option>
                </select>
            </div>

            <!-- Selling Timeline -->
            <div class="hph-form-group" id="selling-timeline-group" style="display: none;">
                <label for="selling-timeline" class="hph-form-label">
                    <?php _e('When are you planning to sell?', 'happy-place-theme'); ?>
                </label>
                <select id="selling-timeline" name="selling_timeline" class="hph-form-select">
                    <option value=""><?php _e('Select Timeline', 'happy-place-theme'); ?></option>
                    <option value="immediately"><?php _e('Immediately', 'happy-place-theme'); ?></option>
                    <option value="1-3-months"><?php _e('Within 1-3 months', 'happy-place-theme'); ?></option>
                    <option value="3-6-months"><?php _e('Within 3-6 months', 'happy-place-theme'); ?></option>
                    <option value="6-12-months"><?php _e('Within 6-12 months', 'happy-place-theme'); ?></option>
                    <option value="over-year"><?php _e('Over a year', 'happy-place-theme'); ?></option>
                    <option value="undecided"><?php _e('Still undecided', 'happy-place-theme'); ?></option>
                </select>
            </div>

            <!-- Additional Comments -->
            <div class="hph-form-group">
                <label for="additional-info" class="hph-form-label">
                    <?php _e('Additional Information', 'happy-place-theme'); ?>
                </label>
                <textarea 
                    id="additional-info" 
                    name="additional_info" 
                    class="hph-form-textarea" 
                    rows="4" 
                    placeholder="<?php _e('Any additional details about your property or specific questions about the valuation...', 'happy-place-theme'); ?>"
                ></textarea>
            </div>

            <!-- Preferred Contact Method -->
            <div class="hph-form-group">
                <label class="hph-form-label">
                    <?php _e('How would you prefer to receive your valuation?', 'happy-place-theme'); ?>
                </label>
                <div class="hph-radio-group">
                    <label class="hph-form-check">
                        <input type="radio" name="delivery_method" value="email" class="hph-form-check-input" checked>
                        <span class="hph-form-check-label">
                            <strong><?php _e('Email Report', 'happy-place-theme'); ?></strong>
                            <small><?php _e('Detailed PDF report sent to your email', 'happy-place-theme'); ?></small>
                        </span>
                    </label>
                    <label class="hph-form-check">
                        <input type="radio" name="delivery_method" value="phone" class="hph-form-check-input">
                        <span class="hph-form-check-label">
                            <strong><?php _e('Phone Consultation', 'happy-place-theme'); ?></strong>
                            <small><?php _e('Personal consultation to discuss your property value', 'happy-place-theme'); ?></small>
                        </span>
                    </label>
                    <label class="hph-form-check">
                        <input type="radio" name="delivery_method" value="in-person" class="hph-form-check-input">
                        <span class="hph-form-check-label">
                            <strong><?php _e('In-Person Meeting', 'happy-place-theme'); ?></strong>
                            <small><?php _e('Schedule a meeting to review the valuation together', 'happy-place-theme'); ?></small>
                        </span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="hph-form-buttons">
            <button type="submit" class="hph-btn hph-btn-primary hph-btn-lg w-full">
                <i class="fas fa-calculator"></i>
                <?php echo esc_html($args['submit_text']); ?>
            </button>
            
            <p class="hph-form-disclaimer">
                <small>
                    <?php _e('By submitting this form, you consent to receive communications from The Parker Group regarding your property valuation. This is a free service with no obligation.', 'happy-place-theme'); ?>
                </small>
            </p>
        </div>

        <!-- Loading State -->
        <div class="hph-form-loading" style="display: none;">
            <div class="hph-loading-spinner"></div>
            <span><?php _e('Processing your valuation request...', 'happy-place-theme'); ?></span>
        </div>

        <!-- Success Message -->
        <div class="hph-form-success" style="display: none;">
            <div class="hph-success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h4><?php _e('Valuation Request Submitted!', 'happy-place-theme'); ?></h4>
            <p><?php _e('Thank you! You\'ll receive your comprehensive property valuation within 24 hours.', 'happy-place-theme'); ?></p>
            <div class="hph-next-steps">
                <h5><?php _e('What happens next?', 'happy-place-theme'); ?></h5>
                <ul>
                    <li><?php _e('We\'ll analyze comparable sales in your area', 'happy-place-theme'); ?></li>
                    <li><?php _e('Review current market conditions and trends', 'happy-place-theme'); ?></li>
                    <li><?php _e('Prepare your detailed valuation report', 'happy-place-theme'); ?></li>
                    <li><?php _e('Deliver your results via your preferred method', 'happy-place-theme'); ?></li>
                </ul>
            </div>
        </div>
    </form>
</div>

<!-- Inline CSS for valuation-specific styles -->
<style>
.hph-valuation-form-container {
    max-width: 700px;
    margin: 0 auto;
}

.hph-value-teaser {
    margin-bottom: 2rem;
    background: linear-gradient(135deg, var(--hph-primary-50) 0%, var(--hph-primary-100) 100%);
    border: 1px solid var(--hph-primary-200);
    border-radius: var(--hph-radius-lg);
    padding: 2rem;
}

.hph-estimate-preview {
    display: flex;
    align-items: flex-start;
    gap: 1.5rem;
}

.hph-estimate-icon {
    flex-shrink: 0;
    width: 4rem;
    height: 4rem;
    background: var(--hph-primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.hph-estimate-title {
    font-size: var(--hph-text-xl);
    font-weight: 600;
    color: var(--hph-primary-800);
    margin: 0 0 1rem 0;
}

.hph-estimate-features {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    gap: 0.75rem;
}

.hph-estimate-features li {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: var(--hph-text-sm);
    color: var(--hph-primary-700);
}

.hph-estimate-features i {
    color: var(--hph-success);
    font-size: 1rem;
}

.hph-form-section {
    background: var(--hph-white);
    border: 1px solid var(--hph-border-color-light);
    border-radius: var(--hph-radius-lg);
    padding: 2rem;
    margin-bottom: 2rem;
}

.hph-form-section:last-of-type {
    margin-bottom: 0;
}

.hph-form-section-title {
    font-size: var(--hph-text-lg);
    font-weight: 600;
    color: var(--hph-gray-900);
    margin: 0 0 1.5rem 0;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid var(--hph-primary-100);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.hph-form-section-title i {
    color: var(--hph-primary);
    font-size: 1.25rem;
}

.hph-checkbox-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 0.75rem;
    margin-top: 0.75rem;
}

.hph-checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: var(--hph-text-sm);
    cursor: pointer;
    padding: 0.5rem;
    border-radius: var(--hph-radius-sm);
    transition: background-color 0.2s ease;
}

.hph-input-group {
    display: flex;
    align-items: stretch;
}

.hph-input-group .hph-form-input {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    border-right: none;
}

.hph-input-suffix {
    display: flex;
    align-items: center;
    padding: var(--hph-input-padding-y) var(--hph-input-padding-x);
    background: var(--hph-gray-100);
    border: var(--hph-input-border-width) var(--hph-input-border-style) var(--hph-input-border-color);
    border-left: none;
    border-top-right-radius: var(--hph-input-radius);
    border-bottom-right-radius: var(--hph-input-radius);
    font-size: var(--hph-text-sm);
    color: var(--hph-gray-600);
}

.hph-radio-group {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-top: 0.75rem;
}

.hph-form-check {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 1rem;
    border: 1px solid var(--hph-border-color-light);
    border-radius: var(--hph-radius-md);
    cursor: pointer;
    transition: all 0.2s ease;
}

.hph-form-check:hover {
    border-color: var(--hph-primary-200);
    background: var(--hph-primary-25);
}

.hph-form-check-input:checked + .hph-form-check-label {
    color: var(--hph-primary-800);
}

.hph-form-check:has(input:checked) {
    border-color: var(--hph-primary);
    background: var(--hph-primary-50);
}

.hph-form-check-input {
    margin-top: 0.125rem;
}

.hph-form-check-label {
    flex: 1;
}

.hph-form-check-label strong {
    display: block;
    color: var(--hph-gray-900);
    margin-bottom: 0.25rem;
}

.hph-form-check-label small {
    color: var(--hph-gray-600);
    font-size: var(--hph-text-xs);
    line-height: 1.4;
}

.hph-form-disclaimer {
    text-align: center;
    margin-top: 1rem;
    color: var(--hph-gray-600);
    font-size: var(--hph-text-xs);
    line-height: 1.4;
}

.hph-next-steps {
    margin-top: 1.5rem;
    padding: 1.5rem;
    background: var(--hph-success-50);
    border: 1px solid var(--hph-success-200);
    border-radius: var(--hph-radius-md);
}

.hph-next-steps h5 {
    color: var(--hph-success-800);
    margin: 0 0 1rem 0;
    font-size: var(--hph-text-base);
}

.hph-next-steps ul {
    margin: 0;
    padding-left: 1.25rem;
    color: var(--hph-success-700);
}

.hph-next-steps li {
    margin-bottom: 0.5rem;
    font-size: var(--hph-text-sm);
}

@media (max-width: 768px) {
    .hph-value-teaser {
        padding: 1.5rem;
    }
    
    .hph-estimate-preview {
        flex-direction: column;
        text-align: center;
    }
    
    .hph-estimate-icon {
        align-self: center;
    }
    
    .hph-form-section {
        padding: 1.5rem;
    }
    
    .hph-checkbox-grid {
        grid-template-columns: 1fr;
    }
    
    .hph-form-check {
        padding: 0.75rem;
    }
}

@media (max-width: 480px) {
    .hph-estimate-features {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show selling timeline when selling is selected as purpose
    const purposeSelect = document.getElementById('valuation-purpose');
    const timelineGroup = document.getElementById('selling-timeline-group');
    
    if (purposeSelect && timelineGroup) {
        purposeSelect.addEventListener('change', function() {
            if (this.value === 'selling') {
                timelineGroup.style.display = 'block';
            } else {
                timelineGroup.style.display = 'none';
            }
        });
    }
});
</script>
