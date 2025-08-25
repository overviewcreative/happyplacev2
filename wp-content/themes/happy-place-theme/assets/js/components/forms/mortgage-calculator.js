/**
 * Mortgage Calculator JavaScript
 * 
 * Interactive mortgage calculator with real-time calculations
 * Payment breakdowns and affordability tools
 *
 * @package HappyPlaceTheme
 */

(function($) {
    'use strict';
    
    // Mortgage Calculator namespace
    HPH.MortgageCalculator = {
        
        /**
         * Initialize mortgage calculator
         */
        init: function() {
            this.initCalculators();
            this.initAffordabilityCalculator();
            this.initPrequalificationForm();
        },
        
        /**
         * Initialize mortgage calculators
         */
        initCalculators: function() {
            $('.mortgage-calculator').each(function() {
                var $calculator = $(this);
                HPH.MortgageCalculator.setupCalculator($calculator);
            });
        },
        
        /**
         * Setup individual calculator
         */
        setupCalculator: function($calculator) {
            var $inputs = $calculator.find('input, select');
            var $calculateBtn = $calculator.find('.calculate-payment');
            var $result = $calculator.find('#payment-result');
            
            // Real-time calculation
            $inputs.on('input change', HPH.debounce(function() {
                HPH.MortgageCalculator.calculatePayment($calculator);
            }, 500));
            
            // Manual calculation button
            $calculateBtn.on('click', function(e) {
                e.preventDefault();
                HPH.MortgageCalculator.calculatePayment($calculator);
            });
            
            // Initialize with default values
            HPH.MortgageCalculator.calculatePayment($calculator);
        },
        
        /**
         * Calculate mortgage payment
         */
        calculatePayment: function($calculator) {
            var loanAmount = parseFloat($calculator.find('#loan-amount').val()) || 0;
            var interestRate = parseFloat($calculator.find('#interest-rate').val()) || 0;
            var loanTerm = parseInt($calculator.find('#loan-term').val()) || 30;
            var downPayment = parseFloat($calculator.find('#down-payment').val()) || 0;
            var propertyTax = parseFloat($calculator.find('#property-tax').val()) || 0;
            var insurance = parseFloat($calculator.find('#home-insurance').val()) || 0;
            var pmi = parseFloat($calculator.find('#pmi').val()) || 0;
            var hoaFees = parseFloat($calculator.find('#hoa-fees').val()) || 0;
            
            // Calculate principal and interest
            var principal = loanAmount - downPayment;
            var monthlyRate = (interestRate / 100) / 12;
            var numPayments = loanTerm * 12;
            
            var monthlyPI = 0;
            if (monthlyRate > 0 && numPayments > 0) {
                monthlyPI = principal * (monthlyRate * Math.pow(1 + monthlyRate, numPayments)) / 
                           (Math.pow(1 + monthlyRate, numPayments) - 1);
            } else if (principal > 0 && numPayments > 0) {
                monthlyPI = principal / numPayments;
            }
            
            // Calculate other monthly costs
            var monthlyTax = propertyTax / 12;
            var monthlyInsurance = insurance / 12;
            var monthlyPMI = pmi / 12;
            var monthlyHOA = hoaFees / 12;
            
            var totalMonthlyPayment = monthlyPI + monthlyTax + monthlyInsurance + monthlyPMI + monthlyHOA;
            
            // Display results
            HPH.MortgageCalculator.displayResults($calculator, {
                principalInterest: monthlyPI,
                propertyTax: monthlyTax,
                insurance: monthlyInsurance,
                pmi: monthlyPMI,
                hoaFees: monthlyHOA,
                totalPayment: totalMonthlyPayment,
                loanAmount: loanAmount,
                downPayment: downPayment,
                principal: principal
            });
        },
        
        /**
         * Display calculation results
         */
        displayResults: function($calculator, results) {
            var $result = $calculator.find('#payment-result');
            
            if (results.totalPayment <= 0) {
                $result.html('<p class="hph-text-gray-600">Please enter valid loan details to calculate payment.</p>').removeClass('hph-hidden');
                return;
            }
            
            var resultHtml = `
                <div class="mortgage-results hph-space-y-4">
                    <div class="total-payment hph-text-center hph-p-4 hph-bg-primary-50 hph-rounded-lg">
                        <div class="hph-text-sm hph-text-gray-600 hph-mb-1">Total Monthly Payment</div>
                        <div class="hph-text-3xl hph-font-bold hph-text-primary-600">
                            ${HPH.MortgageCalculator.formatCurrency(results.totalPayment)}
                        </div>
                    </div>
                    
                    <div class="payment-breakdown hph-space-y-2">
                        <h4 class="hph-font-semibold hph-mb-3">Payment Breakdown</h4>
                        
                        <div class="breakdown-item hph-flex hph-justify-between">
                            <span>Principal & Interest</span>
                            <span class="hph-font-medium">${HPH.MortgageCalculator.formatCurrency(results.principalInterest)}</span>
                        </div>
                        
                        ${results.propertyTax > 0 ? `
                        <div class="breakdown-item hph-flex hph-justify-between">
                            <span>Property Tax</span>
                            <span class="hph-font-medium">${HPH.MortgageCalculator.formatCurrency(results.propertyTax)}</span>
                        </div>
                        ` : ''}
                        
                        ${results.insurance > 0 ? `
                        <div class="breakdown-item hph-flex hph-justify-between">
                            <span>Home Insurance</span>
                            <span class="hph-font-medium">${HPH.MortgageCalculator.formatCurrency(results.insurance)}</span>
                        </div>
                        ` : ''}
                        
                        ${results.pmi > 0 ? `
                        <div class="breakdown-item hph-flex hph-justify-between">
                            <span>PMI</span>
                            <span class="hph-font-medium">${HPH.MortgageCalculator.formatCurrency(results.pmi)}</span>
                        </div>
                        ` : ''}
                        
                        ${results.hoaFees > 0 ? `
                        <div class="breakdown-item hph-flex hph-justify-between">
                            <span>HOA Fees</span>
                            <span class="hph-font-medium">${HPH.MortgageCalculator.formatCurrency(results.hoaFees)}</span>
                        </div>
                        ` : ''}
                    </div>
                    
                    <div class="loan-summary hph-pt-4 hph-border-t hph-space-y-2">
                        <h4 class="hph-font-semibold hph-mb-3">Loan Summary</h4>
                        
                        <div class="summary-item hph-flex hph-justify-between">
                            <span>Home Price</span>
                            <span class="hph-font-medium">${HPH.MortgageCalculator.formatCurrency(results.loanAmount)}</span>
                        </div>
                        
                        <div class="summary-item hph-flex hph-justify-between">
                            <span>Down Payment</span>
                            <span class="hph-font-medium">${HPH.MortgageCalculator.formatCurrency(results.downPayment)}</span>
                        </div>
                        
                        <div class="summary-item hph-flex hph-justify-between">
                            <span>Loan Amount</span>
                            <span class="hph-font-medium">${HPH.MortgageCalculator.formatCurrency(results.principal)}</span>
                        </div>
                    </div>
                    
                    <div class="calculator-actions hph-pt-4 hph-space-y-2">
                        <button type="button" class="get-prequalified hph-btn hph-btn-primary hph-w-full">
                            Get Pre-qualified
                        </button>
                        <button type="button" class="contact-lender hph-btn hph-btn-outline hph-w-full">
                            Contact a Lender
                        </button>
                    </div>
                </div>
            `;
            
            $result.html(resultHtml).removeClass('hph-hidden');
            
            // Bind action buttons
            $result.find('.get-prequalified').on('click', function() {
                HPH.MortgageCalculator.showPrequalificationForm(results);
            });
            
            $result.find('.contact-lender').on('click', function() {
                HPH.MortgageCalculator.contactLender(results);
            });
        },
        
        /**
         * Initialize affordability calculator
         */
        initAffordabilityCalculator: function() {
            $('.affordability-calculator').each(function() {
                var $calculator = $(this);
                var $inputs = $calculator.find('input, select');
                var $calculateBtn = $calculator.find('.calculate-affordability');
                
                $inputs.on('input change', HPH.debounce(function() {
                    HPH.MortgageCalculator.calculateAffordability($calculator);
                }, 500));
                
                $calculateBtn.on('click', function(e) {
                    e.preventDefault();
                    HPH.MortgageCalculator.calculateAffordability($calculator);
                });
            });
        },
        
        /**
         * Calculate affordability
         */
        calculateAffordability: function($calculator) {
            var annualIncome = parseFloat($calculator.find('#annual-income').val()) || 0;
            var monthlyDebts = parseFloat($calculator.find('#monthly-debts').val()) || 0;
            var downPayment = parseFloat($calculator.find('#down-payment-affordability').val()) || 0;
            var interestRate = parseFloat($calculator.find('#interest-rate-affordability').val()) || 3.5;
            var loanTerm = parseInt($calculator.find('#loan-term-affordability').val()) || 30;
            
            // Calculate maximum monthly payment (28% of gross monthly income)
            var monthlyIncome = annualIncome / 12;
            var maxPayment = monthlyIncome * 0.28;
            
            // Subtract existing debts
            var availablePayment = maxPayment - monthlyDebts;
            
            if (availablePayment <= 0) {
                $calculator.find('#affordability-result').html(
                    '<p class="hph-text-red-600">Based on your current debt, you may need to reduce monthly obligations before purchasing a home.</p>'
                ).removeClass('hph-hidden');
                return;
            }
            
            // Calculate maximum loan amount
            var monthlyRate = (interestRate / 100) / 12;
            var numPayments = loanTerm * 12;
            var maxLoanAmount = 0;
            
            if (monthlyRate > 0) {
                maxLoanAmount = availablePayment * (Math.pow(1 + monthlyRate, numPayments) - 1) / 
                               (monthlyRate * Math.pow(1 + monthlyRate, numPayments));
            } else {
                maxLoanAmount = availablePayment * numPayments;
            }
            
            var maxHomePrice = maxLoanAmount + downPayment;
            
            // Display affordability results
            var resultHtml = `
                <div class="affordability-results hph-space-y-4">
                    <div class="max-price hph-text-center hph-p-4 hph-bg-green-50 hph-rounded-lg">
                        <div class="hph-text-sm hph-text-gray-600 hph-mb-1">Maximum Home Price</div>
                        <div class="hph-text-3xl hph-font-bold hph-text-green-600">
                            ${HPH.MortgageCalculator.formatCurrency(maxHomePrice)}
                        </div>
                    </div>
                    
                    <div class="affordability-breakdown hph-space-y-2">
                        <div class="breakdown-item hph-flex hph-justify-between">
                            <span>Maximum Monthly Payment</span>
                            <span class="hph-font-medium">${HPH.MortgageCalculator.formatCurrency(availablePayment)}</span>
                        </div>
                        <div class="breakdown-item hph-flex hph-justify-between">
                            <span>Maximum Loan Amount</span>
                            <span class="hph-font-medium">${HPH.MortgageCalculator.formatCurrency(maxLoanAmount)}</span>
                        </div>
                        <div class="breakdown-item hph-flex hph-justify-between">
                            <span>Down Payment</span>
                            <span class="hph-font-medium">${HPH.MortgageCalculator.formatCurrency(downPayment)}</span>
                        </div>
                    </div>
                </div>
            `;
            
            $calculator.find('#affordability-result').html(resultHtml).removeClass('hph-hidden');
        },
        
        /**
         * Initialize prequalification form
         */
        initPrequalificationForm: function() {
            $(document).on('click', '.get-prequalified', function(e) {
                e.preventDefault();
                // This would typically open a modal or redirect to a prequalification form
                HPH.showAlert('Prequalification form would open here', 'info');
            });
        },
        
        /**
         * Show prequalification form
         */
        showPrequalificationForm: function(calculationResults) {
            // Create and show prequalification modal
            var modalHtml = `
                <div id="prequalification-modal" class="hph-modal">
                    <div class="hph-modal-content">
                        <div class="hph-modal-header">
                            <h3>Get Pre-qualified</h3>
                            <button type="button" class="hph-modal-close">&times;</button>
                        </div>
                        <div class="hph-modal-body">
                            <p>Based on your calculation, you're looking at a monthly payment of approximately <strong>${HPH.MortgageCalculator.formatCurrency(calculationResults.totalPayment)}</strong>.</p>
                            <p>Would you like to get pre-qualified with one of our preferred lenders?</p>
                            <form class="prequalification-form hph-space-y-4">
                                <div class="hph-form-group">
                                    <label class="hph-label">Full Name</label>
                                    <input type="text" class="hph-input" required>
                                </div>
                                <div class="hph-form-group">
                                    <label class="hph-label">Email</label>
                                    <input type="email" class="hph-input" required>
                                </div>
                                <div class="hph-form-group">
                                    <label class="hph-label">Phone</label>
                                    <input type="tel" class="hph-input" required>
                                </div>
                                <button type="submit" class="hph-btn hph-btn-primary hph-w-full">
                                    Submit Pre-qualification Request
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(modalHtml);
            HPH.openModal('prequalification-modal');
            
            // Handle form submission
            $('.prequalification-form').on('submit', function(e) {
                e.preventDefault();
                // Handle prequalification form submission
                HPH.showAlert('Pre-qualification request submitted!', 'success');
                HPH.closeModal();
                setTimeout(function() {
                    $('#prequalification-modal').remove();
                }, 500);
            });
        },
        
        /**
         * Contact lender
         */
        contactLender: function(calculationResults) {
            // This would typically open a contact form or modal
            HPH.showAlert('Contact lender functionality would be implemented here', 'info');
        },
        
        /**
         * Format currency
         */
        formatCurrency: function(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount);
        },
        
        /**
         * Format percentage
         */
        formatPercentage: function(rate) {
            return rate.toFixed(3) + '%';
        }
    };
    
    // Initialize mortgage calculator when DOM is ready
    $(document).ready(function() {
        HPH.MortgageCalculator.init();
    });
    
})(jQuery);
