/**
 * Listing Automation JavaScript
 * Handles frontend automation features for listing management
 */

(function($) {
    'use strict';

    /**
     * Initialize listing automation
     */
    function initListingAutomation() {
        // Initialize bathroom display automation
        initBathroomFormatting();
        
        // Initialize address slug preview
        initAddressSlugPreview();
    }

    /**
     * Initialize bathroom formatting
     */
    function initBathroomFormatting() {
        const fullBathsField = $('input[name*="full_bathrooms"]');
        const halfBathsField = $('input[name*="half_bathrooms"]');
        
        if (fullBathsField.length && halfBathsField.length) {
            // Create display element if it doesn't exist
            if (!$('#bathroom-display').length) {
                const displayContainer = $('<div id="bathroom-display-container" class="acf-field acf-field-message"><div class="acf-label"><label>Bathroom Display</label></div><div class="acf-input"><div id="bathroom-display" class="bathroom-formatted-display">--</div></div></div>');
                halfBathsField.closest('.acf-field').after(displayContainer);
            }
            
            // Update on field changes
            fullBathsField.add(halfBathsField).on('input change', updateBathroomDisplay);
            
            // Initial update
            updateBathroomDisplay();
        }
    }

    /**
     * Update bathroom display formatting
     */
    function updateBathroomDisplay() {
        const fullBaths = parseInt($('input[name*="full_bathrooms"]').val()) || 0;
        const halfBaths = parseInt($('input[name*="half_bathrooms"]').val()) || 0;
        
        const displayText = fullBaths + ' | ' + halfBaths;
        const displayElement = $('#bathroom-display');
        
        if (displayElement.length) {
            displayElement.text(displayText);
            
            // Add styling classes
            displayElement.removeClass('has-baths no-baths');
            if (fullBaths > 0 || halfBaths > 0) {
                displayElement.addClass('has-baths');
            } else {
                displayElement.addClass('no-baths');
            }
        }
    }

    /**
     * Initialize address slug preview
     */
    function initAddressSlugPreview() {
        const addressFields = $('input[name*="street_number"], input[name*="street_name"], select[name*="street_suffix"], input[name*="city"], select[name*="state"]');
        
        if (addressFields.length) {
            // Create slug preview element if it doesn't exist
            if (!$('#address-slug-preview').length) {
                const previewContainer = $('<div id="slug-preview-container" class="acf-field acf-field-message"><div class="acf-label"><label>Post Slug Preview</label></div><div class="acf-input"><div id="address-slug-preview" class="address-slug-preview">--</div></div></div>');
                $('select[name*="state"]').closest('.acf-field').after(previewContainer);
            }
            
            // Update on field changes
            addressFields.on('input change', updateSlugPreview);
            
            // Initial update
            updateSlugPreview();
        }
    }

    /**
     * Update slug preview
     */
    function updateSlugPreview() {
        const streetNumber = $('input[name*="street_number"]').val();
        const streetName = $('input[name*="street_name"]').val();
        const streetSuffix = $('select[name*="street_suffix"]').val();
        const city = $('input[name*="city"]').val();
        const state = $('select[name*="state"]').val();
        
        const addressParts = [streetNumber, streetName, streetSuffix].filter(part => part && part.trim());
        const slugParts = [];
        
        if (addressParts.length > 0) {
            slugParts.push(addressParts.join('-'));
        }
        
        if (city && city.trim()) {
            slugParts.push(city.trim());
        }
        
        if (state && state.trim()) {
            slugParts.push(state.trim());
        }
        
        const slug = slugParts.join('-').toLowerCase()
            .replace(/[^a-z0-9\-]/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
        
        const previewElement = $('#address-slug-preview');
        if (previewElement.length) {
            previewElement.text(slug || 'No address entered');
            
            // Add styling classes
            previewElement.removeClass('has-slug no-slug');
            if (slug) {
                previewElement.addClass('has-slug');
            } else {
                previewElement.addClass('no-slug');
            }
        }
    }

    /**
     * Handle gallery image tagging
     */
    function initGalleryTagging() {
        // Handle tag changes to show counts
        $(document).on('change', 'select[name*="[tag]"]', function() {
            updateTagCounts();
        });
        
        // Initial count
        updateTagCounts();
    }

    /**
     * Update tag counts for gallery organization
     */
    function updateTagCounts() {
        const tagCounts = {};
        
        $('select[name*="[tag]"]').each(function() {
            const tag = $(this).val();
            if (tag) {
                tagCounts[tag] = (tagCounts[tag] || 0) + 1;
            }
        });
        
        // Update tag count display if container exists
        let countDisplay = $('#gallery-tag-counts');
        if (countDisplay.length === 0) {
            countDisplay = $('<div id="gallery-tag-counts" class="gallery-tag-summary"></div>');
            $('.acf-field[data-name="gallery_images"]').append(countDisplay);
        }
        
        let countHtml = '<h4>Image Categories:</h4><ul>';
        for (const [tag, count] of Object.entries(tagCounts)) {
            const tagLabel = tag.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            countHtml += `<li>${tagLabel}: ${count}</li>`;
        }
        countHtml += '</ul>';
        
        if (Object.keys(tagCounts).length === 0) {
            countHtml = '<p>No images categorized yet.</p>';
        }
        
        countDisplay.html(countHtml);
    }

    /**
     * Status badge preview
     */
    function initStatusBadgePreview() {
        const statusField = $('select[name*="listing_status_taxonomy"]');
        const dateField = $('input[name*="listing_date"]');
        
        if (statusField.length && dateField.length) {
            // Create badge preview
            if (!$('#status-badge-preview').length) {
                const previewContainer = $('<div id="status-preview-container" class="acf-field acf-field-message"><div class="acf-label"><label>Status Badge Preview</label></div><div class="acf-input"><div id="status-badge-preview" class="status-badge-preview">--</div></div></div>');
                dateField.closest('.acf-field').after(previewContainer);
            }
            
            statusField.add(dateField).on('change', updateStatusBadgePreview);
            updateStatusBadgePreview();
        }
    }

    /**
     * Update status badge preview
     */
    function updateStatusBadgePreview() {
        const status = $('select[name*="listing_status_taxonomy"] option:selected').text();
        const listingDate = $('input[name*="listing_date"]').val();
        
        let badgeText = status || 'No Status';
        let badgeClass = 'status-default';
        
        if (listingDate && status) {
            const listingDateObj = new Date(listingDate);
            const currentDate = new Date();
            const daysDiff = Math.floor((currentDate - listingDateObj) / (1000 * 60 * 60 * 24));
            
            if (status.toLowerCase().includes('coming soon')) {
                badgeText = 'Coming Soon';
                badgeClass = 'status-coming-soon';
            } else if (status.toLowerCase().includes('active')) {
                if (daysDiff <= 3) {
                    badgeText = 'New Listing';
                    badgeClass = 'status-new';
                } else if (daysDiff <= 7) {
                    badgeText = 'New This Week';
                    badgeClass = 'status-recent';
                } else {
                    badgeText = status;
                    badgeClass = 'status-active';
                }
            } else if (status.toLowerCase().includes('pending')) {
                badgeClass = 'status-pending';
            } else if (status.toLowerCase().includes('sold')) {
                badgeClass = 'status-sold';
            }
        }
        
        const previewElement = $('#status-badge-preview');
        if (previewElement.length) {
            previewElement.text(badgeText).removeClass().addClass('status-badge-preview ' + badgeClass);
        }
    }

    // Initialize when document is ready
    $(document).ready(function() {
        initListingAutomation();
        initGalleryTagging();
        initStatusBadgePreview();
    });

    // Re-initialize for ACF repeater rows
    $(document).on('acf/setup_fields', function(e, postbox) {
        initListingAutomation();
        initGalleryTagging();
    });

})(jQuery);