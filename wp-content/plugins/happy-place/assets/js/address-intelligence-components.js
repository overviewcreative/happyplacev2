/**
 * Address Intelligence Admin JavaScript
 * Handles auto-formatting of address from components and manual refresh controls
 */

jQuery(document).ready(function($) {
    
    // Auto-format full address when components change
    var addressFields = [
        'field_street_number',
        'field_street_dir_prefix', 
        'field_street_name',
        'field_street_suffix',
        'field_street_dir_suffix',
        'field_unit_number',
        'field_city',
        'field_state', 
        'field_zip_code'
    ];
    
    // Watch for changes in address component fields
    addressFields.forEach(function(fieldName) {
        $('[data-name="' + fieldName + '"]').on('change', 'input, select', function() {
            updateFormattedAddress();
        });
    });
    
    /**
     * Update the formatted address field from components
     */
    function updateFormattedAddress() {
        var components = [];
        var streetAddress = '';
        
        // Build street address
        var streetNumber = $('[data-name="field_street_number"] input').val();
        if (streetNumber) components.push(streetNumber);
        
        var preDirection = $('[data-name="field_street_dir_prefix"] select').val();
        if (preDirection) components.push(preDirection);
        
        var streetName = $('[data-name="field_street_name"] input').val();
        if (streetName) components.push(streetName);
        
        var streetSuffix = $('[data-name="field_street_suffix"] select').val();
        if (streetSuffix) components.push(streetSuffix);
        
        var postDirection = $('[data-name="field_street_dir_suffix"] select').val();
        if (postDirection) components.push(postDirection);
        
        var unitNumber = $('[data-name="field_unit_number"] input').val();
        if (unitNumber) components.push(unitNumber);
        
        streetAddress = components.join(' ');
        
        // Add city, state, zip
        var city = $('[data-name="field_city"] input').val();
        var state = $('[data-name="field_state"] select').val();
        var zip = $('[data-name="field_zip_code"] input').val();
        
        var fullAddress = streetAddress;
        
        if (city) {
            fullAddress += ', ' + city;
        }
        
        if (state) {
            fullAddress += ', ' + state;
        }
        
        if (zip) {
            fullAddress += ' ' + zip;
        }
        
        // Update the formatted address field
        $('[data-name="field_formatted_address"] input').val(fullAddress);
    }
    
    // Initial formatting on page load
    updateFormattedAddress();
});

/**
 * Manual refresh functions (called by buttons in ACF field)
 */

function hphGeocodeAddress() {
    var postId = getPostId();
    
    if (!postId) {
        alert('Unable to determine post ID');
        return;
    }
    
    // Show loading state
    var button = event.target;
    var originalText = button.textContent;
    button.textContent = 'Geocoding...';
    button.disabled = true;
    
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'hph_geocode_address',
            post_id: postId,
            nonce: hph_address_intelligence.nonce
        },
        success: function(response) {
            if (response.success) {
                alert('Address geocoded successfully!');
                location.reload(); // Refresh to show updated coordinates
            } else {
                alert('Error: ' + response.data);
            }
        },
        error: function() {
            alert('Network error occurred');
        },
        complete: function() {
            button.textContent = originalText;
            button.disabled = false;
        }
    });
}

function hphRefreshLocationData() {
    var postId = getPostId();
    
    if (!postId) {
        alert('Unable to determine post ID');
        return;
    }
    
    // Show loading state
    var button = event.target;
    var originalText = button.textContent;
    button.textContent = 'Refreshing...';
    button.disabled = true;
    
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'hph_refresh_location_data',
            post_id: postId,
            nonce: hph_address_intelligence.nonce
        },
        success: function(response) {
            if (response.success) {
                alert('All location data refreshed successfully!');
                location.reload(); // Refresh to show updated data
            } else {
                alert('Error: ' + response.data);
            }
        },
        error: function() {
            alert('Network error occurred');
        },
        complete: function() {
            button.textContent = originalText;
            button.disabled = false;
        }
    });
}

function hphRefreshSchoolData() {
    var postId = getPostId();
    
    if (!postId) {
        alert('Unable to determine post ID');
        return;
    }
    
    // Show loading state
    var button = event.target;
    var originalText = button.textContent;
    button.textContent = 'Refreshing...';
    button.disabled = true;
    
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'hph_refresh_school_data',
            post_id: postId,
            nonce: hph_address_intelligence.nonce
        },
        success: function(response) {
            if (response.success) {
                alert('School data refreshed successfully!');
                location.reload(); // Refresh to show updated data
            } else {
                alert('Error: ' + response.data);
            }
        },
        error: function() {
            alert('Network error occurred');
        },
        complete: function() {
            button.textContent = originalText;
            button.disabled = false;
        }
    });
}

function hphRefreshWalkabilityData() {
    var postId = getPostId();
    
    if (!postId) {
        alert('Unable to determine post ID');
        return;
    }
    
    // Show loading state
    var button = event.target;
    var originalText = button.textContent;
    button.textContent = 'Refreshing...';
    button.disabled = true;
    
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'hph_refresh_walkability_data',
            post_id: postId,
            nonce: hph_address_intelligence.nonce
        },
        success: function(response) {
            if (response.success) {
                alert('Walkability data refreshed successfully!');
                location.reload(); // Refresh to show updated data
            } else {
                alert('Error: ' + response.data);
            }
        },
        error: function() {
            alert('Network error occurred');
        },
        complete: function() {
            button.textContent = originalText;
            button.disabled = false;
        }
    });
}

function hphRefreshAmenities() {
    var postId = getPostId();
    
    if (!postId) {
        alert('Unable to determine post ID');
        return;
    }
    
    // Show loading state
    var button = event.target;
    var originalText = button.textContent;
    button.textContent = 'Refreshing...';
    button.disabled = true;
    
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'hph_refresh_amenities',
            post_id: postId,
            nonce: hph_address_intelligence.nonce
        },
        success: function(response) {
            if (response.success) {
                alert('Amenities data refreshed successfully!');
                location.reload(); // Refresh to show updated data
            } else {
                alert('Error: ' + response.data);
            }
        },
        error: function() {
            alert('Network error occurred');
        },
        complete: function() {
            button.textContent = originalText;
            button.disabled = false;
        }
    });
}

/**
 * Get current post ID from various sources
 */
function getPostId() {
    // Try different methods to get post ID
    if (typeof acf !== 'undefined' && acf.get && acf.get('post_id')) {
        return acf.get('post_id');
    }
    
    if (window.typenow && window.pagenow === 'post' && window.adminpage === 'post-php') {
        var urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('post');
    }
    
    if (jQuery('#post_ID').length) {
        return jQuery('#post_ID').val();
    }
    
    return null;
}
