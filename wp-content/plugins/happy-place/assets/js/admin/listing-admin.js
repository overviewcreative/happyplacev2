// File: /assets/js/admin/listing-admin.js
// FIXED VERSION - ACF Compatible

(function($) {
    'use strict';
    
    /**
     * HP Listing Admin Manager - ACF Compatible Version
     */
    const HPListingAdmin = {
        
        debug: true,
        
        init() {
            this.log('Initializing HP Listing Admin (Fixed Version)');
            
            // Wait for ACF to be fully ready
            if (typeof acf !== 'undefined') {
                acf.addAction('ready', () => {
                    this.bindEvents();
                    this.initEnhancements();
                });
            } else {
                this.log('ACF not found - admin enhancements disabled');
            }
        },
        
        log(message, data = null) {
            if (this.debug && console && console.log) {
                console.log('[HP Admin Fixed]', message, data || '');
            }
        },
        
        bindEvents() {
            // Use ACF's field change events instead of jQuery
            acf.addAction('change', (field) => {
                const name = field.data('name');
                
                // Handle bathroom changes
                if (name === 'bathrooms_full' || name === 'bathrooms_half') {
                    this.updateBathroomDisplay();
                }
                
                // Handle address component changes
                if (this.isAddressField(name)) {
                    this.updateAddressPreview();
                }
            });
            
            // Geocode button (safe - doesn't modify fields directly)
            $(document).on('click', '.hp-geocode-btn', this.geocodeAddress.bind(this));
            
            // Converter buttons - use ACF API
            $(document).on('click', '.hp-convert-acres', this.convertToSqftSafe.bind(this));
            $(document).on('click', '.hp-convert-sqft', this.convertToAcresSafe.bind(this));
        },
        
        initEnhancements() {
            this.addAddressPreview();
            this.addBathroomDisplay();
            this.addConverterButtons();
        },
        
        isAddressField(name) {
            const addressFields = [
                'street_number', 'street_name', 'street_type',
                'street_dir_prefix', 'street_dir_suffix', 
                'unit_number', 'city', 'state', 'zip_code'
            ];
            return addressFields.includes(name);
        },
        
        /**
         * Add address preview (safe version)
         */
        addAddressPreview() {
            // Only add if not already present
            if ($('.hp-address-preview').length) return;
            
            const zipField = acf.getField('field_zip_code');
            if (!zipField) return;
            
            // Add OUTSIDE the ACF field structure
            const previewHtml = `
                <div class="hp-address-preview" style="margin: 20px 0; padding: 15px; background: #f5f5f5; border: 1px solid #ddd;">
                    <strong>Address Preview:</strong>
                    <div class="hp-address-display" style="margin: 10px 0;">
                        <em>Enter address components above</em>
                    </div>
                    <button type="button" class="button hp-geocode-btn">
                        📍 Geocode Address
                    </button>
                    <span class="hp-geocode-status"></span>
                </div>
            `;
            
            // Insert AFTER the entire field group, not inside it
            zipField.$el.closest('.acf-fields').after(previewHtml);
            this.updateAddressPreview();
        },
        
        /**
         * Update address preview using ACF API
         */
        updateAddressPreview() {
            // Use ACF API to get field values
            const components = {
                number: acf.getField('field_street_number')?.val() || '',
                name: acf.getField('field_street_name')?.val() || '',
                type: acf.getField('field_street_type')?.val() || '',
                city: acf.getField('field_city')?.val() || '',
                state: acf.getField('field_state')?.val() || '',
                zip: acf.getField('field_zip_code')?.val() || ''
            };
            
            const street = [components.number, components.name, components.type]
                .filter(Boolean).join(' ');
            
            const fullAddress = [
                street,
                components.city,
                [components.state, components.zip].filter(Boolean).join(' ')
            ].filter(Boolean).join(', ');
            
            $('.hp-address-display').html(fullAddress || '<em>Enter address components above</em>');
        },
        
        /**
         * Safe geocoding that uses ACF API
         */
        geocodeAddress(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const $status = $('.hp-geocode-status');
            
            $btn.prop('disabled', true).text('Geocoding...');
            
            $.post(ajaxurl, {
                action: 'hp_geocode_address',
                nonce: hpAdmin.nonce,
                post_id: $('#post_ID').val()
            })
            .done(response => {
                if (response.success && response.data) {
                    // Use ACF API to set field values
                    if (response.data.latitude) {
                        const latField = acf.getField('field_latitude');
                        const lngField = acf.getField('field_longitude');
                        
                        if (latField) latField.val(response.data.latitude);
                        if (lngField) lngField.val(response.data.longitude);
                    }
                    
                    $status.text('✅ Geocoded successfully');
                    this.log('Geocoding successful:', response.data);
                } else {
                    $status.text('❌ Geocoding failed');
                }
            })
            .always(() => {
                $btn.prop('disabled', false).text('📍 Geocode Address');
                setTimeout(() => $status.fadeOut(), 3000);
            });
        },
        
        /**
         * Add converter buttons safely
         */
        addConverterButtons() {
            // Only add once
            if ($('.hp-convert-acres').length) return;
            
            const acresField = acf.getField('field_lot_size_acres');
            const sqftField = acf.getField('field_lot_size_sqft');
            
            if (acresField) {
                acresField.$el.find('.acf-input').append(`
                    <button type="button" class="button hp-convert-acres" style="margin-top: 5px;">
                        Convert to Sq Ft →
                    </button>
                `);
            }
            
            if (sqftField) {
                sqftField.$el.find('.acf-input').append(`
                    <button type="button" class="button hp-convert-sqft" style="margin-top: 5px;">
                        Convert to Acres →
                    </button>
                `);
            }
        },
        
        /**
         * Safe conversion using ACF API
         */
        convertToSqftSafe(e) {
            e.preventDefault();
            
            const acresField = acf.getField('field_lot_size_acres');
            const sqftField = acf.getField('field_lot_size_sqft');
            
            if (!acresField || !sqftField) return;
            
            const acres = parseFloat(acresField.val());
            if (!acres) return;
            
            const sqft = Math.round(acres * 43560);
            
            // Use ACF API to set value
            sqftField.val(sqft);
            
            this.log('Converted acres to sqft:', {acres, sqft});
        },
        
        /**
         * Safe conversion using ACF API
         */
        convertToAcresSafe(e) {
            e.preventDefault();
            
            const acresField = acf.getField('field_lot_size_acres');
            const sqftField = acf.getField('field_lot_size_sqft');
            
            if (!acresField || !sqftField) return;
            
            const sqft = parseFloat(sqftField.val());
            if (!sqft) return;
            
            const acres = (sqft / 43560).toFixed(2);
            
            // Use ACF API to set value
            acresField.val(acres);
            
            this.log('Converted sqft to acres:', {sqft, acres});
        },
        
        /**
         * Add bathroom display safely
         */
        addBathroomDisplay() {
            if ($('.hp-bathroom-display').length) return;
            
            const halfBathField = acf.getField('field_bathrooms_half');
            if (!halfBathField) return;
            
            const displayHtml = `
                <div class="hp-bathroom-display" style="margin: 15px 0; padding: 10px; background: #f9f9f9; border-left: 3px solid #2271b1;">
                    <strong>Total Bathrooms:</strong>
                    <span class="hp-bathroom-total">Calculating...</span>
                </div>
            `;
            
            // Add AFTER the field group, not inside
            halfBathField.$el.closest('.acf-fields').after(displayHtml);
            this.updateBathroomDisplay();
        },
        
        /**
         * Update bathroom display using ACF API
         */
        updateBathroomDisplay() {
            const fullField = acf.getField('field_bathrooms_full');
            const halfField = acf.getField('field_bathrooms_half');
            
            if (!fullField || !halfField) return;
            
            const full = parseInt(fullField.val()) || 0;
            const half = parseInt(halfField.val()) || 0;
            const total = full + (half * 0.5);
            
            $('.hp-bathroom-total').text(`${total} (${full} full, ${half} half)`);
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        HPListingAdmin.init();
    });
    
})(jQuery);