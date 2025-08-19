/**
 * Address Intelligence Admin JavaScript
 * 
 * Handles manual refresh controls for location intelligence data
 *
 * @package HappyPlace
 */

(function($) {
    'use strict';
    
    // Global functions for ACF button onclick handlers
    window.hphGeocodeAddress = function() {
        var streetAddress = $('input[name="acf[field_street_address]"]').val();
        
        if (!streetAddress) {
            alert('Please enter a street address first.');
            return;
        }
        
        refreshLocationData('hph_geocode_address', {
            street_address: streetAddress
        }, 'Geocoding address...');
    };
    
    window.hphRefreshLocationData = function() {
        refreshLocationData('hph_refresh_location_data', {}, 'Refreshing all location data...');
    };
    
    window.hphRefreshSchoolData = function() {
        refreshLocationData('hph_refresh_school_data', {}, 'Refreshing school data...');
    };
    
    window.hphRefreshWalkabilityData = function() {
        refreshLocationData('hph_refresh_walkability_data', {}, 'Refreshing walkability data...');
    };
    
    window.hphRefreshAmenities = function() {
        refreshLocationData('hph_refresh_amenities', {}, 'Refreshing amenities data...');
    };
    
    /**
     * Generic function to refresh location data
     */
    function refreshLocationData(action, extraData, loadingMessage) {
        var $button = $('button[onclick*="' + action.replace('hph_', '') + '"]');
        var originalText = $button.text();
        
        // Show loading state
        $button.prop('disabled', true).text(loadingMessage);
        
        // Prepare AJAX data
        var ajaxData = {
            action: action,
            nonce: hpAddressIntelligence.nonce,
            post_id: hpAddressIntelligence.postId
        };
        
        // Add extra data
        $.extend(ajaxData, extraData);
        
        // Make AJAX request
        $.post(hpAddressIntelligence.ajaxUrl, ajaxData)
            .done(function(response) {
                if (response.success) {
                    showNotice(response.data, 'success');
                    
                    // Reload the page to show updated data
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showNotice('Error: ' + response.data, 'error');
                }
            })
            .fail(function() {
                showNotice('AJAX request failed. Please try again.', 'error');
            })
            .always(function() {
                // Restore button state
                $button.prop('disabled', false).text(originalText);
            });
    }
    
    /**
     * Show admin notice
     */
    function showNotice(message, type) {
        var noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
        var $notice = $('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');
        
        // Insert after page title
        $('.wrap h1').after($notice);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $notice.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    /**
     * Auto-geocode when street address changes
     */
    $(document).ready(function() {
        var $streetAddressField = $('input[name="acf[field_street_address]"]');
        var geocodeTimeout;
        
        $streetAddressField.on('input', function() {
            var address = $(this).val();
            
            // Clear previous timeout
            clearTimeout(geocodeTimeout);
            
            // Set new timeout to geocode after user stops typing
            if (address.length > 10) {
                geocodeTimeout = setTimeout(function() {
                    // Auto-geocode if address looks complete
                    if (address.match(/\d+.*[a-zA-Z].*,.*[a-zA-Z]/)) {
                        hphGeocodeAddress();
                    }
                }, 2000); // Wait 2 seconds after user stops typing
            }
        });
        
        // Style the refresh controls
        $('.hph-refresh-controls').css({
            'margin-top': '10px',
            'padding': '10px',
            'background': '#f9f9f9',
            'border': '1px solid #ddd',
            'border-radius': '4px'
        });
        
        $('.hph-refresh-controls button').css({
            'margin-right': '10px',
            'margin-bottom': '5px'
        });
        
        // Style readonly fields
        $('.hph-readonly-field input, .hph-readonly-field select, .hph-readonly-field textarea').css({
            'background-color': '#f7f7f7',
            'color': '#666'
        });
    });
    
})(jQuery);
