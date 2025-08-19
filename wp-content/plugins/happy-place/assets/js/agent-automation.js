/**
 * Agent Automation JavaScript
 * Provides real-time previews and automation for agent forms
 */

(function($) {
    'use strict';

    /**
     * Agent automation functionality
     */
    var AgentAutomation = {
        
        /**
         * Initialize the automation
         */
        init: function() {
            if (typeof hp_agent_automation === 'undefined') {
                return;
            }

            this.bindEvents();
            this.initializePreviews();
            this.updateAllPreviews();
        },

        /**
         * Bind events to form fields
         */
        bindEvents: function() {
            // Name fields - update display name and slug preview
            $('input[name*="first_name"], input[name*="middle_name"], input[name*="last_name"], select[name*="suffix"]')
                .on('change keyup', this.updateNamePreviews);

            // Display name field - update when manually changed
            $('input[name*="display_name"]')
                .on('change keyup', this.updateDisplayNamePreview);

            // Experience date - calculate years automatically
            $('input[name*="date_started"]')
                .on('change', this.calculateExperience);

            // Performance fields - update stats
            $('input[name*="total_sales_volume"], input[name*="total_transactions"]')
                .on('change keyup', this.updatePerformanceDisplay);

            // Certification fields - update summary
            $('.acf-repeater[data-name="certifications"]')
                .on('change', 'input', this.updateCertificationSummary);

            // Social media fields - validate URLs
            $('input[name*="_url"]')
                .on('blur', this.validateSocialUrls);

            console.log('Agent automation events bound');
        },

        /**
         * Initialize preview containers
         */
        initializePreviews: function() {
            var previewHtml = `
                <div class="agent-automation-previews">
                    <div class="preview-section">
                        <h4>${hp_agent_automation.strings.display_name_preview}</h4>
                        <div id="agent-display-name-preview" class="preview-value">--</div>
                    </div>
                    <div class="preview-section">
                        <h4>${hp_agent_automation.strings.slug_preview}</h4>
                        <div id="agent-slug-preview" class="preview-value">--</div>
                    </div>
                    <div class="preview-section">
                        <h4>${hp_agent_automation.strings.experience_preview}</h4>
                        <div id="agent-experience-preview" class="preview-value">--</div>
                    </div>
                    <div class="preview-section">
                        <h4>${hp_agent_automation.strings.performance_preview}</h4>
                        <div id="agent-performance-preview" class="preview-value">--</div>
                    </div>
                    <div class="preview-section">
                        <h4>${hp_agent_automation.strings.certification_summary}</h4>
                        <div id="agent-certification-summary" class="preview-value">--</div>
                    </div>
                </div>
            `;

            // Insert after the first tab content
            $('.acf-tab-group .acf-tab-wrap:first').after(previewHtml);
        },

        /**
         * Update name-related previews
         */
        updateNamePreviews: function() {
            AgentAutomation.updateDisplayNamePreview();
            AgentAutomation.updateSlugPreview();
        },

        /**
         * Update display name preview
         */
        updateDisplayNamePreview: function() {
            var displayName = $('input[name*="display_name"]').val();
            
            if (!displayName) {
                // Build from name components
                var firstName = $('input[name*="first_name"]').val() || '';
                var middleName = $('input[name*="middle_name"]').val() || '';
                var lastName = $('input[name*="last_name"]').val() || '';
                var suffix = $('select[name*="suffix"]').val() || '';

                var nameParts = [];
                if (firstName) nameParts.push(firstName);
                if (middleName) nameParts.push(middleName);
                if (lastName) nameParts.push(lastName);
                if (suffix && suffix !== '') nameParts.push(suffix);

                displayName = nameParts.join(' ');
            }

            $('#agent-display-name-preview').text(displayName || '--');
        },

        /**
         * Update slug preview
         */
        updateSlugPreview: function() {
            var firstName = $('input[name*="first_name"]').val() || '';
            var lastName = $('input[name*="last_name"]').val() || '';

            var slugParts = [];
            if (firstName) slugParts.push(AgentAutomation.slugify(firstName));
            if (lastName) slugParts.push(AgentAutomation.slugify(lastName));

            var slug = slugParts.join('-');
            $('#agent-slug-preview').text(slug || '--');
        },

        /**
         * Calculate years of experience
         */
        calculateExperience: function() {
            var dateStarted = $(this).val();
            
            if (dateStarted) {
                var startDate = new Date(dateStarted);
                var currentDate = new Date();
                var diffTime = Math.abs(currentDate - startDate);
                var diffYears = Math.floor(diffTime / (1000 * 60 * 60 * 24 * 365.25));

                // Update the years field
                $('input[name*="years_experience"]').val(diffYears);
                
                // Update preview
                $('#agent-experience-preview').text(diffYears + ' years');
            } else {
                $('#agent-experience-preview').text('--');
            }
        },

        /**
         * Update performance display
         */
        updatePerformanceDisplay: function() {
            var totalVolume = parseInt($('input[name*="total_sales_volume"]').val()) || 0;
            var totalTransactions = parseInt($('input[name*="total_transactions"]').val()) || 0;

            var avgPrice = totalTransactions > 0 ? Math.round(totalVolume / totalTransactions) : 0;

            var performanceText = '';
            if (totalVolume > 0) {
                performanceText += '$' + AgentAutomation.formatNumber(totalVolume) + ' total volume';
            }
            if (totalTransactions > 0) {
                if (performanceText) performanceText += ' | ';
                performanceText += totalTransactions + ' transactions';
            }
            if (avgPrice > 0) {
                if (performanceText) performanceText += ' | ';
                performanceText += '$' + AgentAutomation.formatNumber(avgPrice) + ' avg price';
            }

            $('#agent-performance-preview').text(performanceText || '--');
        },

        /**
         * Update certification summary
         */
        updateCertificationSummary: function() {
            var certifications = [];
            
            $('.acf-repeater[data-name="certifications"] .acf-row').each(function() {
                if (!$(this).hasClass('acf-clone')) {
                    var abbr = $(this).find('input[name*="abbreviation"]').val();
                    if (abbr) {
                        certifications.push(abbr);
                    }
                }
            });

            var summaryText = certifications.length > 0 ? certifications.join(', ') : '--';
            $('#agent-certification-summary').text(summaryText);
        },

        /**
         * Validate social media URLs
         */
        validateSocialUrls: function() {
            var $field = $(this);
            var url = $field.val();
            var fieldName = $field.attr('name');

            if (!url) return;

            var isValid = true;
            var expectedDomain = '';

            // Check expected domains
            if (fieldName.includes('facebook')) {
                expectedDomain = 'facebook.com';
            } else if (fieldName.includes('instagram')) {
                expectedDomain = 'instagram.com';
            } else if (fieldName.includes('linkedin')) {
                expectedDomain = 'linkedin.com';
            } else if (fieldName.includes('twitter')) {
                expectedDomain = 'twitter.com';
            } else if (fieldName.includes('youtube')) {
                expectedDomain = 'youtube.com';
            } else if (fieldName.includes('tiktok')) {
                expectedDomain = 'tiktok.com';
            } else if (fieldName.includes('zillow')) {
                expectedDomain = 'zillow.com';
            }

            if (expectedDomain && url.indexOf(expectedDomain) === -1) {
                isValid = false;
            }

            // Add visual feedback
            if (isValid) {
                $field.removeClass('invalid-url').addClass('valid-url');
            } else {
                $field.removeClass('valid-url').addClass('invalid-url');
            }
        },

        /**
         * Update all previews
         */
        updateAllPreviews: function() {
            this.updateNamePreviews();
            this.calculateExperience();
            this.updatePerformanceDisplay();
            this.updateCertificationSummary();
        },

        /**
         * Convert text to slug format
         */
        slugify: function(text) {
            return text.toLowerCase()
                .replace(/[^\w\s-]/g, '') // Remove special characters
                .replace(/[\s_-]+/g, '-') // Replace spaces and underscores with hyphens
                .replace(/^-+|-+$/g, ''); // Remove leading/trailing hyphens
        },

        /**
         * Format number with commas
         */
        formatNumber: function(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }
    };

    /**
     * Initialize when DOM is ready
     */
    $(document).ready(function() {
        AgentAutomation.init();
    });

    /**
     * Reinitialize on ACF append (for repeater fields)
     */
    $(document).on('acf/setup_fields', function() {
        AgentAutomation.updateAllPreviews();
    });

})(jQuery);