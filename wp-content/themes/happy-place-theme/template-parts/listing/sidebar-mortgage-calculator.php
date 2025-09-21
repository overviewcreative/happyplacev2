<?php
/**
 * Mortgage Calculator Widget Template Part
 * File: template-parts/listing/sidebar-mortgage-calculator.php
 * 
 * Interactive mortgage calculator for property listings
 * Uses HPH framework utilities and CSS variables
 * 
 * @package HappyPlaceTheme
 */

$listing_id = $args['listing_id'] ?? get_the_ID();

// Get listing price and property tax data
$listing_price = get_field('listing_price', $listing_id) ?: 0;
$annual_taxes = get_field('annual_taxes', $listing_id) ?: 0;
$hoa_fee = get_field('hoa_fee', $listing_id) ?: 0;
$hoa_frequency = get_field('hoa_frequency', $listing_id) ?: 'monthly';

// Calculate monthly HOA
$monthly_hoa = 0;
if ($hoa_fee) {
    switch ($hoa_frequency) {
        case 'monthly':
            $monthly_hoa = $hoa_fee;
            break;
        case 'quarterly':
            $monthly_hoa = $hoa_fee / 3;
            break;
        case 'annually':
            $monthly_hoa = $hoa_fee / 12;
            break;
    }
}

// Default calculator values
$default_down_payment_percent = 20;
$default_down_payment = $listing_price * ($default_down_payment_percent / 100);
$default_loan_amount = $listing_price - $default_down_payment;
$default_interest_rate = 7.0; // Current average rate
$default_loan_term = 30; // years
$monthly_taxes = $annual_taxes ? $annual_taxes / 12 : 0;
$monthly_insurance = $listing_price * 0.0035 / 12; // Estimate 0.35% annually

if (!$listing_price) {
    return;
}
?>

<div class="hph-widget hph-widget--calculator hph-widget--collapsible hph-bg-white hph-rounded-lg hph-shadow-md hph-p-lg hph-mb-xl">
    
    <div class="hph-widget__header hph-widget__header--collapsible hph-mb-lg" onclick="toggleMortgageCalculator()">
        <div class="hph-widget__title-wrapper">
            <h3 class="hph-widget__title hph-text-xl hph-font-bold hph-flex hph-items-center hph-gap-sm">
                <i class="fas fa-calculator hph-text-primary"></i>
                Mortgage Calculator
            </h3>
            <p class="hph-text-sm hph-text-gray-600 hph-mt-xs">
                Estimate your monthly payment
            </p>
        </div>
        <button type="button" 
                class="hph-collapse-toggle hph-collapse-toggle--calculator"
                aria-expanded="false"
                aria-controls="mortgage-calculator-content"
                aria-label="Toggle mortgage calculator">
            <i class="fas fa-chevron-down"></i>
        </button>
    </div>
    
    <div class="hph-widget__content hph-widget__content--collapsible" id="mortgage-calculator-content" style="display: none;">
    
    <form id="mortgage-calculator" class="hph-calculator-form">
        
        <!-- Home Price -->
        <div class="hph-form-group">
            <label class="hph-form-label" for="home-price">
                Home Price
            </label>
            <div class="hph-input-group">
                <div class="hph-input-group-prepend">
                    <span class="hph-input-group-text">$</span>
                </div>
                <input type="number" 
                       id="home-price" 
                       name="home_price" 
                       value="<?php echo esc_attr($listing_price); ?>"
                       class="hph-form-input"
                       min="0"
                       step="1000">
            </div>
        </div>
        
        <!-- Down Payment -->
        <div class="hph-form-group">
            <label class="hph-form-label">
                Down Payment
            </label>
            <div class="hph-form-grid hph-form-grid-2">
                <div class="hph-input-group">
                    <div class="hph-input-group-prepend">
                        <span class="hph-input-group-text">$</span>
                    </div>
                    <input type="number" 
                           id="down-payment-amount" 
                           name="down_payment_amount" 
                           value="<?php echo esc_attr($default_down_payment); ?>"
                           class="hph-form-input"
                           min="0"
                           step="1000">
                </div>
                <div class="hph-input-group">
                    <input type="number" 
                           id="down-payment-percent" 
                           name="down_payment_percent" 
                           value="<?php echo esc_attr($default_down_payment_percent); ?>"
                           class="hph-form-input"
                           min="0"
                           max="100"
                           step="0.1">
                    <div class="hph-input-group-append">
                        <span class="hph-input-group-text">%</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Interest Rate -->
        <div class="hph-form-group">
            <label class="hph-form-label" for="interest-rate">
                Interest Rate
            </label>
            <div class="hph-input-group">
                <input type="number" 
                       id="interest-rate" 
                       name="interest_rate" 
                       value="<?php echo esc_attr($default_interest_rate); ?>"
                       class="hph-form-input"
                       min="0"
                       max="50"
                       step="0.01">
                <div class="hph-input-group-append">
                    <span class="hph-input-group-text">%</span>
                </div>
            </div>
        </div>
        
        <!-- Loan Term -->
        <div class="hph-form-group">
            <label class="hph-form-label" for="loan-term">
                Loan Term
            </label>
            <select id="loan-term" 
                    name="loan_term" 
                    class="hph-form-select">
                <option value="15">15 years</option>
                <option value="30" selected>30 years</option>
            </select>
        <!-- Advanced Options Toggle -->
        <button type="button" 
                id="toggle-advanced" 
                class="hph-btn hph-btn--link hph-text-primary hph-flex hph-items-center hph-gap-2 hph-mb-4">
            <i class="fas fa-chevron-down hph-transition-transform"></i>
            Advanced Options
        </button>
        
        <!-- Advanced Options (Initially Hidden) -->
        <div id="advanced-options" class="hph-hidden hph-space-y-md hph-mb-md">
            
            <!-- Property Tax -->
            <div class="hph-form-group">
                <label class="hph-form-label hph-text-sm hph-font-medium hph-text-gray-700 hph-mb-xs hph-block">
                    Property Tax (Monthly)
                </label>
                <div class="hph-input-group">
                    <div class="hph-input-group-prepend">
                        <span class="hph-input-group-text">$</span>
                    </div>
                    <input type="number" 
                           id="property-tax" 
                           name="property_tax" 
                           value="<?php echo esc_attr(round($monthly_taxes)); ?>"
                           class="hph-form-input"
                           min="0"
                           step="10">
                </div>
            </div>
            
            <!-- Home Insurance -->
            <div class="hph-form-group">
                <label class="hph-form-label hph-text-sm hph-font-medium hph-text-gray-700 hph-mb-xs hph-block">
                    Home Insurance (Monthly)
                </label>
                <div class="hph-input-group">
                    <div class="hph-input-group-prepend">
                        <span class="hph-input-group-text">$</span>
                    </div>
                    <input type="number" 
                           id="home-insurance" 
                           name="home_insurance" 
                           value="<?php echo esc_attr(round($monthly_insurance)); ?>"
                           class="hph-form-input"
                           min="0"
                           step="10">
                </div>
            </div>
            
            <!-- HOA Fees -->
            <?php if ($monthly_hoa) : ?>
            <div class="hph-form-group">
                <label class="hph-form-label hph-text-sm hph-font-medium hph-text-gray-700 hph-mb-xs hph-block">
                    HOA Fees (Monthly)
                </label>
                <div class="hph-input-group">
                    <div class="hph-input-group-prepend">
                        <span class="hph-input-group-text">$</span>
                    </div>
                    <input type="number" 
                           id="hoa-fees" 
                           name="hoa_fees" 
                           value="<?php echo esc_attr(round($monthly_hoa)); ?>"
                           class="hph-form-input"
                           min="0"
                           step="10">
                </div>
            </div>
            <?php endif; ?>
            
            <!-- PMI -->
            <div class="hph-form-group">
                <label class="hph-form-label hph-text-sm hph-font-medium hph-text-gray-700 hph-mb-xs hph-block">
                    PMI (Monthly)
                </label>
                <div class="hph-input-group">
                    <div class="hph-input-group-prepend">
                        <span class="hph-input-group-text">$</span>
                    </div>
                    <input type="number" 
                           id="pmi" 
                           name="pmi" 
                           value="0"
                           class="hph-form-input"
                           min="0"
                           step="10">
                </div>
                <p class="hph-text-xs hph-text-gray-500 hph-mt-xs">
                    Required if down payment is less than 20%
                </p>
            </div>
            
        </div>
        
    </form>
    
    <!-- Results Display -->
    <div class="hph-calculator-results hph-bg-primary-50 hph-rounded-lg hph-p-lg hph-mt-6">
        
        <div class="hph-result-primary hph-text-center hph-mb-6">
            <div class="hph-text-sm hph-text-gray-600 hph-mb-2">Estimated Monthly Payment</div>
            <div id="monthly-payment" class="hph-text-3xl hph-font-bold hph-text-primary">$0</div>
        </div>
        
        <div class="hph-result-breakdown hph-pt-6 hph-border-t hph-border-primary-100">
            <div class="hph-breakdown-title hph-text-sm hph-font-semibold hph-text-gray-700 hph-mb-4">
                Payment Breakdown
            </div>
            
            <div class="hph-breakdown-items hph-space-y-3">
                
                <div class="hph-breakdown-item hph-flex hph-justify-between hph-text-sm">
                    <span class="hph-text-gray-600">Principal & Interest</span>
                    <span id="principal-interest" class="hph-font-medium">$0</span>
                </div>
                
                <div class="hph-breakdown-item hph-flex hph-justify-between hph-text-sm">
                    <span class="hph-text-gray-600">Property Tax</span>
                    <span id="tax-display" class="hph-font-medium">$<?php echo esc_html(round($monthly_taxes)); ?></span>
                </div>
                
                <div class="hph-breakdown-item hph-flex hph-justify-between hph-text-sm">
                    <span class="hph-text-gray-600">Home Insurance</span>
                    <span id="insurance-display" class="hph-font-medium">$<?php echo esc_html(round($monthly_insurance)); ?></span>
                </div>
                
                <?php if ($monthly_hoa) : ?>
                <div class="hph-breakdown-item hph-flex hph-justify-between hph-text-sm">
                    <span class="hph-text-gray-600">HOA Fees</span>
                    <span id="hoa-display" class="hph-font-medium">$<?php echo esc_html(round($monthly_hoa)); ?></span>
                </div>
                <?php endif; ?>
                
                <div id="pmi-display-row" class="hph-breakdown-item hph-flex hph-justify-between hph-text-sm hph-hidden">
                    <span class="hph-text-gray-600">PMI</span>
                    <span id="pmi-display" class="hph-font-medium">$0</span>
                </div>
                
            </div>
        </div>
        
        <!-- Loan Summary -->
        <div class="hph-loan-summary hph-pt-6 hph-mt-6 hph-border-t hph-border-primary-100">
            <div class="hph-summary-items hph-space-y-3 hph-text-xs hph-text-gray-600">
                <div class="hph-flex hph-justify-between">
                    <span>Loan Amount:</span>
                    <span id="loan-amount-display" class="hph-font-medium hph-text-gray-800">$0</span>
                </div>
                <div class="hph-flex hph-justify-between">
                    <span>Total Interest Paid:</span>
                    <span id="total-interest" class="hph-font-medium hph-text-gray-800">$0</span>
                </div>
                <div class="hph-flex hph-justify-between">
                    <span>Total Amount Paid:</span>
                    <span id="total-paid" class="hph-font-medium hph-text-gray-800">$0</span>
                </div>
            </div>
        </div>
        
    </div>
    
    <!-- Action Buttons -->
    <div class="hph-form-actions hph-flex hph-gap-3 hph-mt-6">
        <button type="button" 
                id="print-calculation" 
                class="hph-calculator-action-btn hph-flex-1">
            <i class="fas fa-print"></i>
            <span>Print</span>
        </button>
        
        <!-- Share button removed - not currently integrated -->
        </div>
    
    </div> <!-- Close collapsible content -->
    
</div>

<script>
// Toggle mortgage calculator visibility
function toggleMortgageCalculator() {
    const content = document.getElementById('mortgage-calculator-content');
    const toggleBtn = document.querySelector('.hph-collapse-toggle--calculator');
    
    if (!content || !toggleBtn) return;
    
    const icon = toggleBtn.querySelector('i');
    const isVisible = content.style.display !== 'none';
    
    if (isVisible) {
        content.style.display = 'none';
        toggleBtn.setAttribute('aria-expanded', 'false');
        if (icon) icon.className = 'fas fa-chevron-down';
    } else {
        content.style.display = 'block';
        toggleBtn.setAttribute('aria-expanded', 'true');
        if (icon) icon.className = 'fas fa-chevron-up';
        
        // Initialize calculator if first time opening
        if (!window.calculatorInitialized && window.calculator) {
            window.calculator.init();
            window.calculatorInitialized = true;
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    window.calculator = {
        // Form elements
        homePrice: document.getElementById('home-price'),
        downPaymentAmount: document.getElementById('down-payment-amount'),
        downPaymentPercent: document.getElementById('down-payment-percent'),
        interestRate: document.getElementById('interest-rate'),
        loanTerm: document.getElementById('loan-term'),
        propertyTax: document.getElementById('property-tax'),
        homeInsurance: document.getElementById('home-insurance'),
        hoaFees: document.getElementById('hoa-fees'),
        pmi: document.getElementById('pmi'),
        
        // Display elements
        monthlyPayment: document.getElementById('monthly-payment'),
        principalInterest: document.getElementById('principal-interest'),
        taxDisplay: document.getElementById('tax-display'),
        insuranceDisplay: document.getElementById('insurance-display'),
        hoaDisplay: document.getElementById('hoa-display'),
        pmiDisplay: document.getElementById('pmi-display'),
        pmiDisplayRow: document.getElementById('pmi-display-row'),
        loanAmountDisplay: document.getElementById('loan-amount-display'),
        totalInterest: document.getElementById('total-interest'),
        totalPaid: document.getElementById('total-paid'),
        
        init() {
            this.attachEventListeners();
            this.calculate();
        },
        
        attachEventListeners() {
            // Update calculations on input
            const inputs = document.querySelectorAll('#mortgage-calculator input, #mortgage-calculator select');
            inputs.forEach(input => {
                input.addEventListener('input', () => this.calculate());
            });
            
            // Down payment sync
            this.downPaymentAmount.addEventListener('input', () => this.syncDownPayment('amount'));
            this.downPaymentPercent.addEventListener('input', () => this.syncDownPayment('percent'));
            
            // Advanced options toggle
            document.getElementById('toggle-advanced').addEventListener('click', function() {
                const advanced = document.getElementById('advanced-options');
                const icon = this.querySelector('i');
                
                advanced.classList.toggle('hph-hidden');
                icon.classList.toggle('hph-rotate-180');
            });
            
            // Print button
            document.getElementById('print-calculation').addEventListener('click', () => this.print());
            
            // Share button event listener removed - not currently integrated
        },
        
        syncDownPayment(changed) {
            const price = parseFloat(this.homePrice.value) || 0;
            
            if (changed === 'amount') {
                const amount = parseFloat(this.downPaymentAmount.value) || 0;
                const percent = price > 0 ? (amount / price * 100).toFixed(1) : 0;
                this.downPaymentPercent.value = percent;
            } else {
                const percent = parseFloat(this.downPaymentPercent.value) || 0;
                const amount = price * (percent / 100);
                this.downPaymentAmount.value = Math.round(amount);
            }
            
            // Update PMI
            this.updatePMI();
        },
        
        updatePMI() {
            const percent = parseFloat(this.downPaymentPercent.value) || 0;
            if (percent < 20 && !this.pmi.value) {
                const price = parseFloat(this.homePrice.value) || 0;
                const pmiAmount = Math.round(price * 0.005 / 12); // 0.5% annually
                this.pmi.value = pmiAmount;
            } else if (percent >= 20) {
                this.pmi.value = 0;
            }
        },
        
        calculate() {
            // Get values
            const price = parseFloat(this.homePrice.value) || 0;
            const downPayment = parseFloat(this.downPaymentAmount.value) || 0;
            const loanAmount = price - downPayment;
            const rate = parseFloat(this.interestRate.value) || 0;
            const years = parseFloat(this.loanTerm.value) || 30;
            const tax = parseFloat(this.propertyTax.value) || 0;
            const insurance = parseFloat(this.homeInsurance.value) || 0;
            const hoa = this.hoaFees ? parseFloat(this.hoaFees.value) || 0 : 0;
            const pmi = parseFloat(this.pmi.value) || 0;
            
            // Calculate monthly payment (Principal & Interest)
            const monthlyRate = rate / 100 / 12;
            const numPayments = years * 12;
            
            let monthlyPI = 0;
            if (monthlyRate > 0) {
                monthlyPI = loanAmount * (monthlyRate * Math.pow(1 + monthlyRate, numPayments)) / 
                           (Math.pow(1 + monthlyRate, numPayments) - 1);
            } else {
                monthlyPI = loanAmount / numPayments;
            }
            
            // Calculate totals
            const totalMonthly = monthlyPI + tax + insurance + hoa + pmi;
            const totalInterestPaid = (monthlyPI * numPayments) - loanAmount;
            const totalAmountPaid = monthlyPI * numPayments;
            
            // Update display
            this.monthlyPayment.textContent = '$' + Math.round(totalMonthly).toLocaleString();
            this.principalInterest.textContent = '$' + Math.round(monthlyPI).toLocaleString();
            this.taxDisplay.textContent = '$' + Math.round(tax).toLocaleString();
            this.insuranceDisplay.textContent = '$' + Math.round(insurance).toLocaleString();
            
            if (this.hoaDisplay) {
                this.hoaDisplay.textContent = '$' + Math.round(hoa).toLocaleString();
            }
            
            if (pmi > 0) {
                this.pmiDisplayRow.classList.remove('hph-hidden');
                this.pmiDisplay.textContent = '$' + Math.round(pmi).toLocaleString();
            } else {
                this.pmiDisplayRow.classList.add('hph-hidden');
            }
            
            this.loanAmountDisplay.textContent = '$' + Math.round(loanAmount).toLocaleString();
            this.totalInterest.textContent = '$' + Math.round(totalInterestPaid).toLocaleString();
            this.totalPaid.textContent = '$' + Math.round(totalAmountPaid).toLocaleString();
        },
        
        print() {
            window.print();
        },
        
        // Share function removed - not currently integrated
    };
    
    // Don't auto-init calculator since it's hidden by default
    // Will be initialized when user first opens it
});
</script>
