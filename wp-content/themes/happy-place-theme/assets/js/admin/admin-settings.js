/**
 * Happy Place Theme Admin Settings JavaScript
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

(function($) {
    'use strict';
    
    // Initialize when document is ready
    $(document).ready(function() {
        initTabs();
        initColorPickers();
        initMediaUploader();
        initPasswordToggle();
        initFormValidation();
        initExportImport();
        initTooltips();
    });
    
    /**
     * Initialize tab functionality
     */
    function initTabs() {
        $('.nav-tab').on('click', function(e) {
            e.preventDefault();
            
            const targetTab = $(this).data('tab');
            
            // Update active tab
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            
            // Show corresponding content
            $('.hph-tab-content').hide();
            $('#' + targetTab + '-tab').fadeIn(300).addClass('fade-in');
            
            // Update URL hash
            window.location.hash = targetTab;
        });
        
        // Initialize based on URL hash
        const hash = window.location.hash.substring(1);
        if (hash && $('.nav-tab[data-tab="' + hash + '"]').length) {
            $('.nav-tab[data-tab="' + hash + '"]').click();
        }
    }
    
    /**
     * Initialize WordPress color pickers
     */
    function initColorPickers() {
        if ($.fn.wpColorPicker) {
            $('.color-picker').wpColorPicker({
                change: function(event, ui) {
                    // Update CSS custom property in real-time
                    const colorName = $(this).attr('id').split('_').pop();
                    const color = ui.color.toString();
                    updateCSSVariable('--hph-' + colorName, color);
                },
                clear: function() {
                    // Reset to default color
                    const colorName = $(this).attr('id').split('_').pop();
                    const defaultColor = $(this).data('default-color');
                    updateCSSVariable('--hph-' + colorName, defaultColor);
                }
            });
        }
    }
    
    /**
     * Initialize media uploader
     */
    function initMediaUploader() {
        let mediaUploader;
        
        $('.upload-media').on('click', function(e) {
            e.preventDefault();
            
            const button = $(this);
            const fieldId = button.data('field');
            const preview = $('#' + fieldId + '-preview');
            const input = $('#' + fieldId);
            const removeBtn = button.siblings('.remove-media');
            
            // Create media uploader if it doesn't exist
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }
            
            mediaUploader = wp.media({
                title: hphAdmin.strings.chooseImage,
                button: {
                    text: hphAdmin.strings.useImage
                },
                multiple: false
            });
            
            // When image is selected
            mediaUploader.on('select', function() {
                const attachment = mediaUploader.state().get('selection').first().toJSON();
                
                // Update input value
                input.val(attachment.id);
                
                // Update preview
                if (attachment.url) {
                    const img = $('<img>').attr({
                        'src': attachment.url,
                        'alt': attachment.alt || '',
                        'style': 'max-width: 150px; height: auto;'
                    });
                    preview.html(img);
                    removeBtn.show();
                }
            });
            
            mediaUploader.open();
        });
        
        // Remove media
        $('.remove-media').on('click', function(e) {
            e.preventDefault();
            
            const button = $(this);
            const fieldId = button.data('field');
            const preview = $('#' + fieldId + '-preview');
            const input = $('#' + fieldId);
            
            input.val('');
            preview.empty();
            button.hide();
        });
    }
    
    /**
     * Initialize password field toggle
     */
    function initPasswordToggle() {
        $('.toggle-password').on('click', function(e) {
            e.preventDefault();
            
            const button = $(this);
            const targetId = button.data('target');
            const input = $('#' + targetId);
            const icon = button.find('.dashicons');
            
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('dashicons-visibility').addClass('dashicons-hidden');
            } else {
                input.attr('type', 'password');
                icon.removeClass('dashicons-hidden').addClass('dashicons-visibility');
            }
        });
    }
    
    /**
     * Initialize form validation
     */
    function initFormValidation() {
        // Email validation
        $('input[type="email"]').on('blur', function() {
            const email = $(this).val();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                $(this).addClass('invalid');
                showFieldError($(this), 'Please enter a valid email address.');
            } else {
                $(this).removeClass('invalid');
                hideFieldError($(this));
            }
        });
        
        // URL validation for social links
        $('input[type="url"]').on('blur', function() {
            const url = $(this).val();
            
            if (url && !isValidURL(url)) {
                $(this).addClass('invalid');
                showFieldError($(this), 'Please enter a valid URL.');
            } else {
                $(this).removeClass('invalid');
                hideFieldError($(this));
            }
        });
        
        // Phone number formatting
        $('input[name="hph_agency_phone"]').on('input', function() {
            let value = $(this).val().replace(/\D/g, '');
            if (value.length >= 6) {
                value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
            } else if (value.length >= 3) {
                value = value.replace(/(\d{3})(\d{1,3})/, '($1) $2');
            }
            $(this).val(value);
        });
        
        // Form submission validation
        $('form').on('submit', function(e) {
            let hasErrors = false;
            
            // Check required fields
            $(this).find('[required]').each(function() {
                if (!$(this).val()) {
                    $(this).addClass('invalid');
                    showFieldError($(this), 'This field is required.');
                    hasErrors = true;
                } else {
                    $(this).removeClass('invalid');
                    hideFieldError($(this));
                }
            });
            
            // Check for any validation errors
            if ($(this).find('.invalid').length > 0) {
                hasErrors = true;
            }
            
            if (hasErrors) {
                e.preventDefault();
                showNotice('Please correct the errors before saving.', 'error');
                scrollToFirstError();
            }
        });
    }
    
    /**
     * Initialize export/import functionality
     */
    function initExportImport() {
        // Export settings
        $('#export-settings').on('click', function(e) {
            e.preventDefault();
            
            const button = $(this);
            button.addClass('loading').prop('disabled', true);
            
            // Create download link
            const exportUrl = ajaxurl + '?action=hph_export_settings&nonce=' + hphAdmin.nonce;
            
            // Create temporary link and trigger download
            const link = $('<a>').attr({
                'href': exportUrl,
                'download': 'hph-theme-settings-' + getCurrentDate() + '.json'
            });
            
            $('body').append(link);
            link[0].click();
            link.remove();
            
            setTimeout(function() {
                button.removeClass('loading').prop('disabled', false);
                showNotice(hphAdmin.strings.exportSuccess, 'success');
            }, 1000);
        });
        
        // Import settings
        $('#import-settings').on('click', function(e) {
            e.preventDefault();
            
            // Create file input
            const fileInput = $('<input>').attr({
                'type': 'file',
                'accept': '.json'
            });
            
            fileInput.on('change', function() {
                const file = this.files[0];
                if (!file) return;
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    try {
                        const settings = JSON.parse(e.target.result);
                        importSettings(settings);
                    } catch (error) {
                        showNotice(hphAdmin.strings.importError, 'error');
                    }
                };
                reader.readAsText(file);
            });
            
            fileInput.click();
        });
    }
    
    /**
     * Initialize tooltips
     */
    function initTooltips() {
        // Add tooltips to help icons
        $('.help-icon').tooltip({
            position: { my: "left+15 center", at: "right center" },
            content: function() {
                return $(this).attr('title');
            }
        });
    }
    
    /**
     * Helper Functions
     */
    
    function updateCSSVariable(property, value) {
        document.documentElement.style.setProperty(property, value);
    }
    
    function isValidURL(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }
    
    function showFieldError(field, message) {
        hideFieldError(field);
        
        const errorElement = $('<div class="field-error">')
            .css({
                'color': '#dc3232',
                'font-size': '12px',
                'margin-top': '5px'
            })
            .text(message);
        
        field.after(errorElement);
    }
    
    function hideFieldError(field) {
        field.siblings('.field-error').remove();
    }
    
    function showNotice(message, type) {
        const notice = $('<div class="notice notice-' + type + ' is-dismissible">')
            .html('<p>' + message + '</p>');
        
        $('.hph-admin-settings h1').after(notice);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            notice.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    function scrollToFirstError() {
        const firstError = $('.invalid').first();
        if (firstError.length) {
            $('html, body').animate({
                scrollTop: firstError.offset().top - 100
            }, 500);
        }
    }
    
    function getCurrentDate() {
        const date = new Date();
        return date.getFullYear() + '-' + 
               String(date.getMonth() + 1).padStart(2, '0') + '-' + 
               String(date.getDate()).padStart(2, '0');
    }
    
    function importSettings(settings) {
        if (!settings || typeof settings !== 'object') {
            showNotice(hphAdmin.strings.importError, 'error');
            return;
        }
        
        // Update form fields with imported settings
        $.each(settings, function(key, value) {
            const field = $('[name="' + key + '"]');
            
            if (field.length) {
                if (field.attr('type') === 'checkbox') {
                    field.prop('checked', !!value);
                } else if (field.hasClass('color-picker')) {
                    field.wpColorPicker('color', value);
                } else if (field.is('select')) {
                    field.val(value);
                } else {
                    field.val(value);
                }
                
                // Trigger change event
                field.trigger('change');
            }
        });
        
        showNotice(hphAdmin.strings.importSuccess, 'success');
    }
    
    // Auto-save functionality (optional)
    function initAutoSave() {
        let saveTimeout;
        
        $('input, textarea, select').on('change input', function() {
            clearTimeout(saveTimeout);
            
            saveTimeout = setTimeout(function() {
                // Auto-save logic here
                console.log('Auto-saving...');
            }, 2000);
        });
    }
    
    // Live preview functionality
    function initLivePreview() {
        // Brand colors live preview
        $('.color-picker').on('change', function() {
            const colorName = $(this).attr('id').split('_').pop();
            const color = $(this).val();
            
            // Update preview elements
            updatePreviewColors(colorName, color);
        });
        
        // Logo live preview
        $('[name="hph_brand_logo"]').on('change', function() {
            const logoId = $(this).val();
            if (logoId) {
                updatePreviewLogo(logoId);
            }
        });
    }
    
    function updatePreviewColors(colorName, color) {
        // Update any preview elements with the new color
        $('.color-preview-' + colorName).css('background-color', color);
    }
    
    function updatePreviewLogo(logoId) {
        // Update logo preview
        wp.media.attachment(logoId).fetch().then(function(attachment) {
            $('.logo-preview').attr('src', attachment.get('url'));
        });
    }
    
    // Initialize additional features
    // initAutoSave();
    // initLivePreview();
    
})(jQuery);

// Additional vanilla JavaScript for features that don't require jQuery
document.addEventListener('DOMContentLoaded', function() {
    
    // Keyboard navigation for tabs
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Tab' && e.target.classList.contains('nav-tab')) {
            // Handle tab keyboard navigation
        }
    });
    
    // Form submission with loading state
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function() {
            const submitButton = form.querySelector('[type="submit"]');
            if (submitButton) {
                submitButton.classList.add('loading');
                submitButton.disabled = true;
            }
        });
    });
    
    // Copy to clipboard functionality
    function initCopyToClipboard() {
        const copyButtons = document.querySelectorAll('.copy-to-clipboard');
        
        copyButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const target = document.querySelector(this.dataset.target);
                if (target) {
                    navigator.clipboard.writeText(target.value).then(function() {
                        // Show success feedback
                        const originalText = button.textContent;
                        button.textContent = 'Copied!';
                        setTimeout(function() {
                            button.textContent = originalText;
                        }, 2000);
                    });
                }
            });
        });
    }
    
    initCopyToClipboard();
});
