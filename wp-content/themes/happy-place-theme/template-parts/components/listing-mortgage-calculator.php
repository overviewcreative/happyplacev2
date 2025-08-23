<?php
/**
 * Listing Mortgage Calculator Component - Interactive Charts & Advanced Calculations
 * 
 * Modern mortgage calculator with interactive charts, amortization schedules, and scenario comparison
 * Features real-time calculations, payment breakdowns, and visual analytics
 * 
 * @package HappyPlaceTheme
 * @subpackage Components
 * @since 3.0.0
 * 
 * Args:
 * - listing_id: int (required for bridge functions)
 */

// Extract listing ID from args or global context
$listing_id = $args['listing_id'] ?? get_the_ID();

// Return early if no valid listing
if (!$listing_id || get_post_type($listing_id) !== 'listing') {
    return;
}

// Default arguments
$defaults = array(
    'listing_id' => $listing_id,
    'price' => null,
    'style' => 'interactive',          // interactive, compact, minimal, advanced
    'show_charts' => true,
    'show_amortization' => true,
    'show_comparison' => false,
    'show_affordability' => false,
    'show_scenarios' => true,
    'auto_calculate' => true,
    'currency_format' => 'USD',
    'animation' => 'fade-up',          // fade-up, slide-in, none
    'accent_color' => 'primary',       // primary, secondary, accent
);

// Merge with provided args
$config = wp_parse_args($args ?? array(), $defaults);

// Get listing price with null-safe handling via bridge functions
$listing_price = $config['price'];
if (!$listing_price) {
    if (function_exists('hpt_get_listing_price')) {
        $listing_price = hpt_get_listing_price($listing_id);
    }
}

// Get additional listing data for context via bridge functions
$listing_data = array(
    'price' => $listing_price,
    'address' => function_exists('hpt_get_listing_address') ? hpt_get_listing_address($listing_id, 'street') : '',
    'hoa_fees' => function_exists('hpt_get_listing_hoa_fees') ? hpt_get_listing_hoa_fees($listing_id) : 0,
    'property_tax' => function_exists('hpt_get_listing_property_tax') ? hpt_get_listing_property_tax($listing_id) : null,
    'insurance_estimate' => function_exists('hpt_get_listing_insurance_estimate') ? hpt_get_listing_insurance_estimate($listing_id) : null,
);

// Merge listing data with config
$config = array_merge($config, $listing_data);
extract($config);

// Default calculation parameters
$defaults_calc = array(
    'down_payment_percent' => 20,
    'interest_rate' => 6.75,          // Current market rate
    'loan_term' => 30,
    'property_tax_rate' => 1.25,     // Annual percentage
    'insurance_rate' => 0.35,        // Annual percentage  
    'pmi_rate' => 0.5,               // If down payment < 20%
    'hoa_monthly' => $listing_data['hoa_fees'] ?: 0,
);

// Loan term options
$loan_terms = array(
    15 => __('15 years', 'happy-place-theme'),
    20 => __('20 years', 'happy-place-theme'),
    25 => __('25 years', 'happy-place-theme'),
    30 => __('30 years', 'happy-place-theme'),
);

// Down payment presets
$down_payment_presets = array(
    array('percent' => 5, 'label' => __('5% (FHA)', 'happy-place-theme')),
    array('percent' => 10, 'label' => __('10%', 'happy-place-theme')),
    array('percent' => 15, 'label' => __('15%', 'happy-place-theme')),
    array('percent' => 20, 'label' => __('20% (Conventional)', 'happy-place-theme')),
    array('percent' => 25, 'label' => __('25%', 'happy-place-theme')),
);

// Generate unique calculator ID and classes
$calc_id = 'hph-mortgage-calc-' . $listing_id . '-' . wp_rand();
$container_classes = array(
    'hph-mortgage-calculator',
    'hph-mortgage-calculator--' . $args['style'],
    'hph-color-scheme--' . $args['accent_color'],
    $args['animation'] !== 'none' ? 'hph-animate--' . $args['animation'] : null,
);
$container_classes = array_filter($container_classes);
?>

<div class="<?php echo esc_attr(implode(' ', $container_classes)); ?>" 
     id="<?php echo esc_attr($calc_id); ?>"
     data-listing-id="<?php echo esc_attr($listing_id); ?>"
     data-component="listing-mortgage-calculator"
     data-style="<?php echo esc_attr($args['style']); ?>">

    <!-- Calculator Header -->
    <div class="hph-calc-header hph-mb-6">
        <div class="hph-calc-header__icon hph-mb-3">
            <span class="hph-icon-calculator hph-text-3xl hph-text--accent"></span>
        </div>
        <h3 class="hph-calc-header__title hph-heading hph-heading--h4 hph-mb-2">
            <?php esc_html_e('Mortgage Calculator', 'happy-place-theme'); ?>
        </h3>
        <p class="hph-calc-header__subtitle hph-text--muted">
            <?php esc_html_e('Calculate your estimated monthly mortgage payment', 'happy-place-theme'); ?>
        </p>
        <?php if (!empty($listing_data['address'])): ?>
            <div class="hph-calc-header__property hph-mt-2 hph-text-sm hph-text--muted">
                <span class="hph-icon-map-marker hph-mr-2"></span>
                <?php echo esc_html($listing_data['address']); ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Calculator Form -->
    <form id="<?php echo esc_attr($calc_id); ?>_form" 
          class="hph-calc-form" 
          data-auto-calculate="<?php echo $args['auto_calculate'] ? 'true' : 'false'; ?>">
        
        <!-- Home Price Section -->
        <div class="hph-calc-section hph-mb-6">
            <h4 class="hph-calc-section__title hph-heading hph-heading--h6 hph-mb-4">
                <span class="hph-icon-home hph-mr-2"></span>
                <?php esc_html_e('Purchase Details', 'happy-place-theme'); ?>
            </h4>
            
            <!-- Home Price -->
            <div class="hph-form-group hph-mb-4">
                <label for="home_price_<?php echo esc_attr($calc_id); ?>" class="hph-form-label">
                    <?php esc_html_e('Home Price', 'happy-place-theme'); ?>
                    <span class="hph-form-help-icon" title="<?php esc_attr_e('Total purchase price of the property', 'happy-place-theme'); ?>">
                        <span class="hph-icon-info-circle"></span>
                    </span>
                </label>
                <div class="hph-form-input-group">
                    <span class="hph-form-input-group__prefix">$</span>
                    <input type="number" 
                           id="home_price_<?php echo esc_attr($calc_id); ?>" 
                           name="home_price" 
                           class="hph-form-control hph-calc-input" 
                           value="<?php echo esc_attr($listing_price); ?>"
                           min="50000"
                           max="10000000"
                           step="1000"
                           data-format="currency"
                           required>
                </div>
            </div>
            
            <!-- Down Payment Section -->
            <div class="hph-form-group">
                <label class="hph-form-label">
                    <?php esc_html_e('Down Payment', 'happy-place-theme'); ?>
                    <span class="hph-form-help-icon" title="<?php esc_attr_e('Initial payment made when purchasing', 'happy-place-theme'); ?>">
                        <span class="hph-icon-info-circle"></span>
                    </span>
                </label>
                
                <!-- Down Payment Presets -->
                <div class="hph-down-payment-presets hph-mb-3">
                    <div class="hph-btn-group hph-btn-group--sm">
                        <?php foreach ($down_payment_presets as $preset): ?>
                            <button type="button" 
                                    class="hph-btn hph-btn--outline hph-btn--sm hph-down-payment-preset" 
                                    data-percent="<?php echo esc_attr($preset['percent']); ?>">
                                <?php echo esc_html($preset['label']); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Down Payment Inputs -->
                <div class="hph-form-grid hph-grid hph-grid-cols-2 hph-gap-4">
                    <div class="hph-form-group">
                        <div class="hph-form-input-group">
                            <span class="hph-form-input-group__prefix">$</span>
                            <input type="number" 
                                   id="down_payment_<?php echo esc_attr($calc_id); ?>" 
                                   name="down_payment" 
                                   class="hph-form-control hph-calc-input" 
                                   value="<?php echo esc_attr($listing_price ? ($listing_price * $defaults_calc['down_payment_percent'] / 100) : 0); ?>"
                                   min="0"
                                   step="1000"
                                   data-format="currency">
                        </div>
                    </div>
                    <div class="hph-form-group">
                        <div class="hph-form-input-group">
                            <input type="number" 
                                   id="down_payment_percent_<?php echo esc_attr($calc_id); ?>" 
                                   name="down_payment_percent" 
                                   class="hph-form-control hph-calc-input" 
                                   value="<?php echo esc_attr($defaults_calc['down_payment_percent']); ?>"
                                   min="0"
                                   max="100"
                                   step="1">
                            <span class="hph-form-input-group__suffix">%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Loan Terms Section -->
        <div class="hph-calc-section hph-mb-6">
            <h4 class="hph-calc-section__title hph-heading hph-heading--h6 hph-mb-4">
                <span class="hph-icon-percentage hph-mr-2"></span>
                <?php esc_html_e('Loan Terms', 'happy-place-theme'); ?>
            </h4>
            
            <div class="hph-form-grid hph-grid hph-md:grid-cols-2 hph-gap-4">
                <!-- Interest Rate -->
                <div class="hph-form-group">
                    <label for="interest_rate_<?php echo esc_attr($calc_id); ?>" class="hph-form-label">
                        <?php esc_html_e('Interest Rate', 'happy-place-theme'); ?>
                        <span class="hph-form-help-icon" title="<?php esc_attr_e('Annual interest rate for the mortgage', 'happy-place-theme'); ?>">
                            <span class="hph-icon-info-circle"></span>
                        </span>
                    </label>
                    <div class="hph-form-input-group">
                        <input type="number" 
                               id="interest_rate_<?php echo esc_attr($calc_id); ?>" 
                               name="interest_rate" 
                               class="hph-form-control hph-calc-input" 
                               value="<?php echo esc_attr($defaults_calc['interest_rate']); ?>"
                               min="1"
                               max="15"
                               step="0.125">
                        <span class="hph-form-input-group__suffix">%</span>
                    </div>
                    <div class="hph-form-help hph-text-xs hph-text--muted">
                        <?php esc_html_e('Current average: 6.75%', 'happy-place-theme'); ?>
                    </div>
                </div>
                
                <!-- Loan Term -->
                <div class="hph-form-group">
                    <label for="loan_term_<?php echo esc_attr($calc_id); ?>" class="hph-form-label">
                        <?php esc_html_e('Loan Term', 'happy-place-theme'); ?>
                    </label>
                    <select id="loan_term_<?php echo esc_attr($calc_id); ?>" 
                            name="loan_term" 
                            class="hph-form-control hph-calc-input">
                        <?php foreach ($loan_terms as $years => $label): ?>
                            <option value="<?php echo esc_attr($years); ?>" 
                                    <?php selected($defaults_calc['loan_term'], $years); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Additional Costs Section -->
        <div class="hph-calc-section hph-mb-6">
            <h4 class="hph-calc-section__title hph-heading hph-heading--h6 hph-mb-4">
                <span class="hph-icon-dollar hph-mr-2"></span>
                <?php esc_html_e('Additional Costs', 'happy-place-theme'); ?>
            </h4>
            
            <div class="hph-form-grid hph-grid hph-md:grid-cols-2 hph-gap-4">
                <!-- Property Tax -->
                <div class="hph-form-group">
                    <label for="property_tax_<?php echo esc_attr($calc_id); ?>" class="hph-form-label">
                        <?php esc_html_e('Property Tax', 'happy-place-theme'); ?>
                    </label>
                    <div class="hph-form-input-group">
                        <input type="number" 
                               id="property_tax_<?php echo esc_attr($calc_id); ?>" 
                               name="property_tax_rate" 
                               class="hph-form-control hph-calc-input" 
                               value="<?php echo esc_attr($defaults_calc['property_tax_rate']); ?>"
                               min="0"
                               max="5"
                               step="0.1">
                        <span class="hph-form-input-group__suffix">% / year</span>
                    </div>
                </div>
                
                <!-- Home Insurance -->
                <div class="hph-form-group">
                    <label for="insurance_rate_<?php echo esc_attr($calc_id); ?>" class="hph-form-label">
                        <?php esc_html_e('Home Insurance', 'happy-place-theme'); ?>
                    </label>
                    <div class="hph-form-input-group">
                        <input type="number" 
                               id="insurance_rate_<?php echo esc_attr($calc_id); ?>" 
                               name="insurance_rate" 
                               class="hph-form-control hph-calc-input" 
                               value="<?php echo esc_attr($defaults_calc['insurance_rate']); ?>"
                               min="0"
                               max="2"
                               step="0.05">
                        <span class="hph-form-input-group__suffix">% / year</span>
                    </div>
                </div>
                
                <!-- HOA Fees -->
                <div class="hph-form-group">
                    <label for="hoa_fees_<?php echo esc_attr($calc_id); ?>" class="hph-form-label">
                        <?php esc_html_e('HOA Fees', 'happy-place-theme'); ?>
                    </label>
                    <div class="hph-form-input-group">
                        <span class="hph-form-input-group__prefix">$</span>
                        <input type="number" 
                               id="hoa_fees_<?php echo esc_attr($calc_id); ?>" 
                               name="hoa_fees" 
                               class="hph-form-control hph-calc-input" 
                               value="<?php echo esc_attr($defaults_calc['hoa_monthly']); ?>"
                               min="0"
                               step="25">
                        <span class="hph-form-input-group__suffix">/ month</span>
                    </div>
                </div>
                
                <!-- PMI Rate -->
                <div class="hph-form-group">
                    <label for="pmi_rate_<?php echo esc_attr($calc_id); ?>" class="hph-form-label">
                        <?php esc_html_e('PMI Rate', 'happy-place-theme'); ?>
                        <span class="hph-form-help-icon" title="<?php esc_attr_e('Private Mortgage Insurance if down payment < 20%', 'happy-place-theme'); ?>">
                            <span class="hph-icon-info-circle"></span>
                        </span>
                    </label>
                    <div class="hph-form-input-group">
                        <input type="number" 
                               id="pmi_rate_<?php echo esc_attr($calc_id); ?>" 
                               name="pmi_rate" 
                               class="hph-form-control hph-calc-input" 
                               value="<?php echo esc_attr($defaults_calc['pmi_rate']); ?>"
                               min="0"
                               max="2"
                               step="0.1">
                        <span class="hph-form-input-group__suffix">% / year</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Calculate Button -->
        <div class="hph-calc-actions hph-mb-6">
            <button type="button" 
                    class="hph-btn hph-btn--primary hph-btn--lg hph-btn--block hph-calc-submit" 
                    id="<?php echo esc_attr($calc_id); ?>_calculate">
                <span class="hph-btn__icon">
                    <span class="hph-icon-calculator"></span>
                </span>
                <span class="hph-btn__text"><?php esc_html_e('Calculate Payment', 'happy-place-theme'); ?></span>
                <span class="hph-btn__loading hph-hidden">
                    <span class="hph-spinner hph-spinner--sm"></span>
                </span>
            </button>
        </div>
    </form>

    <!-- Results Section -->
    <div class="hph-calc-results hph-hidden" id="<?php echo esc_attr($calc_id); ?>_results">
        
        <!-- Primary Result -->
        <div class="hph-calc-result-primary hph-text-center hph-mb-6">
            <div class="hph-calc-result-primary__label hph-text--muted hph-mb-2">
                <?php esc_html_e('Estimated Monthly Payment', 'happy-place-theme'); ?>
            </div>
            <div class="hph-calc-result-primary__amount hph-text-4xl hph-font-bold hph-text--accent" 
                 id="<?php echo esc_attr($calc_id); ?>_monthly_payment">
                $0
            </div>
        </div>
        
        <!-- Payment Breakdown -->
        <div class="hph-calc-breakdown hph-mb-6">
            <h4 class="hph-heading hph-heading--h5 hph-mb-4">
                <?php esc_html_e('Payment Breakdown', 'happy-place-theme'); ?>
            </h4>
            
            <div class="hph-breakdown-list">
                <div class="hph-breakdown-item">
                    <span class="hph-breakdown-item__label">
                        <span class="hph-breakdown-item__color" style="background-color: #3B82F6;"></span>
                        <?php esc_html_e('Principal & Interest', 'happy-place-theme'); ?>
                    </span>
                    <span class="hph-breakdown-item__value" id="<?php echo esc_attr($calc_id); ?>_principal_interest">$0</span>
                </div>
                
                <div class="hph-breakdown-item">
                    <span class="hph-breakdown-item__label">
                        <span class="hph-breakdown-item__color" style="background-color: #10B981;"></span>
                        <?php esc_html_e('Property Tax', 'happy-place-theme'); ?>
                    </span>
                    <span class="hph-breakdown-item__value" id="<?php echo esc_attr($calc_id); ?>_property_tax">$0</span>
                </div>
                
                <div class="hph-breakdown-item">
                    <span class="hph-breakdown-item__label">
                        <span class="hph-breakdown-item__color" style="background-color: #F59E0B;"></span>
                        <?php esc_html_e('Home Insurance', 'happy-place-theme'); ?>
                    </span>
                    <span class="hph-breakdown-item__value" id="<?php echo esc_attr($calc_id); ?>_insurance">$0</span>
                </div>
                
                <div class="hph-breakdown-item hph-breakdown-item--hoa">
                    <span class="hph-breakdown-item__label">
                        <span class="hph-breakdown-item__color" style="background-color: #8B5CF6;"></span>
                        <?php esc_html_e('HOA Fees', 'happy-place-theme'); ?>
                    </span>
                    <span class="hph-breakdown-item__value" id="<?php echo esc_attr($calc_id); ?>_hoa">$0</span>
                </div>
                
                <div class="hph-breakdown-item hph-breakdown-item--pmi">
                    <span class="hph-breakdown-item__label">
                        <span class="hph-breakdown-item__color" style="background-color: #EF4444;"></span>
                        <?php esc_html_e('PMI', 'happy-place-theme'); ?>
                    </span>
                    <span class="hph-breakdown-item__value" id="<?php echo esc_attr($calc_id); ?>_pmi">$0</span>
                </div>
            </div>
        </div>
        
        <?php if ($args['show_charts']): ?>
            <!-- Interactive Charts -->
            <div class="hph-calc-charts hph-mb-6">
                <div class="hph-chart-tabs hph-mb-4">
                    <div class="hph-btn-group hph-btn-group--sm">
                        <button type="button" class="hph-btn hph-btn--outline hph-btn--sm active" data-chart="payment-breakdown">
                            <?php esc_html_e('Payment Breakdown', 'happy-place-theme'); ?>
                        </button>
                        <?php if ($args['show_amortization']): ?>
                            <button type="button" class="hph-btn hph-btn--outline hph-btn--sm" data-chart="amortization">
                                <?php esc_html_e('Amortization', 'happy-place-theme'); ?>
                            </button>
                        <?php endif; ?>
                        <?php if ($args['show_scenarios']): ?>
                            <button type="button" class="hph-btn hph-btn--outline hph-btn--sm" data-chart="scenarios">
                                <?php esc_html_e('Scenarios', 'happy-place-theme'); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Chart Container -->
                <div class="hph-chart-container">
                    <canvas id="<?php echo esc_attr($calc_id); ?>_chart" width="400" height="200"></canvas>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Loan Summary -->
        <div class="hph-calc-summary hph-mb-6">
            <h4 class="hph-heading hph-heading--h5 hph-mb-4">
                <?php esc_html_e('Loan Summary', 'happy-place-theme'); ?>
            </h4>
            
            <div class="hph-summary-grid hph-grid hph-md:grid-cols-3 hph-gap-4">
                <div class="hph-summary-item hph-text-center">
                    <div class="hph-summary-item__value hph-text-xl hph-font-bold" 
                         id="<?php echo esc_attr($calc_id); ?>_loan_amount">$0</div>
                    <div class="hph-summary-item__label hph-text--muted hph-text-sm">
                        <?php esc_html_e('Loan Amount', 'happy-place-theme'); ?>
                    </div>
                </div>
                
                <div class="hph-summary-item hph-text-center">
                    <div class="hph-summary-item__value hph-text-xl hph-font-bold" 
                         id="<?php echo esc_attr($calc_id); ?>_total_interest">$0</div>
                    <div class="hph-summary-item__label hph-text--muted hph-text-sm">
                        <?php esc_html_e('Total Interest', 'happy-place-theme'); ?>
                    </div>
                </div>
                
                <div class="hph-summary-item hph-text-center">
                    <div class="hph-summary-item__value hph-text-xl hph-font-bold" 
                         id="<?php echo esc_attr($calc_id); ?>_total_paid">$0</div>
                    <div class="hph-summary-item__label hph-text--muted hph-text-sm">
                        <?php esc_html_e('Total Paid', 'happy-place-theme'); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="hph-calc-actions-secondary">
            <div class="hph-flex hph-gap-3">
                <button type="button" 
                        class="hph-btn hph-btn--outline hph-btn--sm hph-flex-1" 
                        id="<?php echo esc_attr($calc_id); ?>_share">
                    <span class="hph-icon-share hph-mr-2"></span>
                    <?php esc_html_e('Share', 'happy-place-theme'); ?>
                </button>
                
                <button type="button" 
                        class="hph-btn hph-btn--outline hph-btn--sm hph-flex-1" 
                        id="<?php echo esc_attr($calc_id); ?>_print">
                    <span class="hph-icon-print hph-mr-2"></span>
                    <?php esc_html_e('Print', 'happy-place-theme'); ?>
                </button>
                
                <button type="button" 
                        class="hph-btn hph-btn--primary hph-btn--sm hph-flex-1" 
                        id="<?php echo esc_attr($calc_id); ?>_contact">
                    <span class="hph-icon-envelope hph-mr-2"></span>
                    <?php esc_html_e('Get Pre-approved', 'happy-place-theme'); ?>
                </button>
            </div>
        </div>
        
        <!-- Disclaimer -->
        <div class="hph-calc-disclaimer hph-mt-6 hph-p-4 hph-text-center">
            <p class="hph-text-sm hph-text--muted">
                <span class="hph-icon-info-circle hph-mr-2"></span>
                <?php esc_html_e('This calculator provides estimates for informational purposes only. Actual payment amounts may vary based on your specific situation, credit score, and lender requirements.', 'happy-place-theme'); ?>
            </p>
        </div>
    </div>

</div>

<!-- Calculator Configuration Data -->
<script type="text/javascript">
window.hphMortgageCalculators = window.hphMortgageCalculators || {};
window.hphMortgageCalculators['<?php echo esc_js($calc_id); ?>'] = {
    calcId: '<?php echo esc_js($calc_id); ?>',
    listingId: <?php echo intval($listing_id); ?>,
    listingPrice: <?php echo floatval($listing_price); ?>,
    config: {
        autoCalculate: <?php echo $args['auto_calculate'] ? 'true' : 'false'; ?>,
        showCharts: <?php echo $args['show_charts'] ? 'true' : 'false'; ?>,
        showAmortization: <?php echo $args['show_amortization'] ? 'true' : 'false'; ?>,
        showScenarios: <?php echo $args['show_scenarios'] ? 'true' : 'false'; ?>,
        currencyFormat: '<?php echo esc_js($args['currency_format']); ?>',
        chartColors: {
            principalInterest: '#3B82F6',
            propertyTax: '#10B981',
            insurance: '#F59E0B',
            hoa: '#8B5CF6',
            pmi: '#EF4444'
        }
    },
    defaults: <?php echo wp_json_encode($defaults_calc); ?>,
    listingData: <?php echo wp_json_encode($listing_data); ?>,
    ajaxUrl: '<?php echo esc_js(admin_url('admin-ajax.php')); ?>',
    nonce: '<?php echo wp_create_nonce('hph_mortgage_calc_' . $listing_id); ?>'
};

// Initialize calculator when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.initHphMortgageCalculator === 'function') {
        window.initHphMortgageCalculator('<?php echo esc_js($calc_id); ?>');
    }
});
</script>