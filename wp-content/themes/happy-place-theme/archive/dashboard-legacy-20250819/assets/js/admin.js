/**
 * Happy Place Theme Admin JavaScript
 * 
 * Admin-specific functionality and WordPress admin enhancements
 * Theme customizer, admin interfaces, and backend features
 *
 * @package HappyPlaceTheme
 */

(function($) {
    'use strict';
    
    // Admin namespace
    window.HappyPlaceAdmin = {
        
        /**
         * Initialize admin functionality
         */
        init: function() {
            this.initCustomizer();
            this.initMetaBoxes();
            this.initMediaUploader();
            this.initAdminEnhancements();
            this.initAssetManager();
        },
        
        /**
         * Initialize customizer functionality
         */
        initCustomizer: function() {
            if (typeof wp !== 'undefined' && wp.customize) {
                this.initCustomizerControls();
                this.initCustomizerPreview();
            }
        },
        
        /**
         * Initialize customizer controls
         */
        initCustomizerControls: function() {
            // Color picker enhancements
            $('.customize-control-color').each(function() {
                var $control = $(this);
                var $input = $control.find('input[type="text"]');
                
                if ($input.length && !$input.hasClass('enhanced')) {
                    $input.addClass('enhanced');
                    
                    // Add color palette
                    var palette = [
                        '#2E7D32', '#388E3C', '#4CAF50', '#66BB6A',
                        '#1976D2', '#1E88E5', '#2196F3', '#42A5F5',
                        '#F57C00', '#FF9800', '#FFB74D', '#FFCC02',
                        '#D32F2F', '#F44336', '#EF5350', '#E57373'
                    ];
                    
                    $input.wpColorPicker({
                        palettes: palette
                    });
                }
            });
            
            // Range slider enhancements
            $('.customize-control-range').each(function() {
                var $control = $(this);
                var $input = $control.find('input[type="range"]');
                var $number = $control.find('input[type="number"]');
                
                // Sync range and number inputs
                $input.on('input', function() {
                    $number.val(this.value);
                });
                
                $number.on('input', function() {
                    $input.val(this.value);
                });
            });
            
            // Typography controls
            this.initTypographyControls();
            
            // Spacing controls
            this.initSpacingControls();
        },
        
        /**
         * Initialize typography controls
         */
        initTypographyControls: function() {
            $('.customize-control-typography').each(function() {
                var $control = $(this);
                var controlId = $control.attr('id');
                
                // Font family dropdown
                var $fontFamily = $control.find('.font-family-select');
                $fontFamily.on('change', function() {
                    var fontFamily = $(this).val();
                    HappyPlaceAdmin.updateTypographyPreview(controlId, 'font-family', fontFamily);
                });
                
                // Font weight dropdown
                var $fontWeight = $control.find('.font-weight-select');
                $fontWeight.on('change', function() {
                    var fontWeight = $(this).val();
                    HappyPlaceAdmin.updateTypographyPreview(controlId, 'font-weight', fontWeight);
                });
                
                // Font size slider
                var $fontSize = $control.find('.font-size-slider');
                $fontSize.on('input', function() {
                    var fontSize = $(this).val() + 'px';
                    HappyPlaceAdmin.updateTypographyPreview(controlId, 'font-size', fontSize);
                });
                
                // Line height slider
                var $lineHeight = $control.find('.line-height-slider');
                $lineHeight.on('input', function() {
                    var lineHeight = $(this).val();
                    HappyPlaceAdmin.updateTypographyPreview(controlId, 'line-height', lineHeight);
                });
            });
        },
        
        /**
         * Initialize spacing controls
         */
        initSpacingControls: function() {
            $('.customize-control-spacing').each(function() {
                var $control = $(this);
                var controlId = $control.attr('id');
                
                // Individual spacing inputs
                $control.find('.spacing-input').on('input', function() {
                    var property = $(this).data('property');
                    var value = $(this).val() + 'px';
                    HappyPlaceAdmin.updateSpacingPreview(controlId, property, value);
                });
                
                // Link/unlink button
                $control.find('.spacing-link').on('click', function(e) {
                    e.preventDefault();
                    var $inputs = $control.find('.spacing-input');
                    var isLinked = $(this).hasClass('linked');
                    
                    if (isLinked) {
                        $(this).removeClass('linked');
                        $inputs.prop('disabled', false);
                    } else {
                        $(this).addClass('linked');
                        var firstValue = $inputs.first().val();
                        $inputs.val(firstValue).prop('disabled', false);
                        $inputs.not(':first').prop('disabled', true);
                    }
                });
            });
        },
        
        /**
         * Update typography preview
         */
        updateTypographyPreview: function(controlId, property, value) {
            var selector = '#' + controlId.replace('customize-control-', '');
            
            wp.customize.preview.send('typography-update', {
                selector: selector,
                property: property,
                value: value
            });
        },
        
        /**
         * Update spacing preview
         */
        updateSpacingPreview: function(controlId, property, value) {
            var selector = '#' + controlId.replace('customize-control-', '');
            
            wp.customize.preview.send('spacing-update', {
                selector: selector,
                property: property,
                value: value
            });
        },
        
        /**
         * Initialize customizer preview
         */
        initCustomizerPreview: function() {
            // Listen for preview messages
            wp.customize.preview.bind('typography-update', function(data) {
                $(data.selector).css(data.property, data.value);
            });
            
            wp.customize.preview.bind('spacing-update', function(data) {
                $(data.selector).css(data.property, data.value);
            });
        },
        
        /**
         * Initialize meta boxes
         */
        initMetaBoxes: function() {
            // Property meta box
            this.initPropertyMetaBox();
            
            // SEO meta box
            this.initSEOMetaBox();
            
            // Page options meta box
            this.initPageOptionsMetaBox();
        },
        
        /**
         * Initialize property meta box
         */
        initPropertyMetaBox: function() {
            var $metaBox = $('#property-details-meta-box');
            
            if ($metaBox.length) {
                // Price formatting
                $metaBox.find('.price-input').on('input', function() {
                    var value = $(this).val().replace(/[^\d]/g, '');
                    $(this).val(HappyPlaceAdmin.formatPrice(value));
                });
                
                // Address autocomplete
                $metaBox.find('.address-input').each(function() {
                    HappyPlaceAdmin.initAddressAutocomplete(this);
                });
                
                // Property type conditional fields
                $metaBox.find('#property_type').on('change', function() {
                    var propertyType = $(this).val();
                    HappyPlaceAdmin.togglePropertyTypeFields(propertyType);
                });
                
                // Gallery management
                this.initPropertyGallery();
                
                // Amenities management
                this.initAmenitiesManager();
            }
        },
        
        /**
         * Initialize property gallery
         */
        initPropertyGallery: function() {
            var $gallery = $('#property-gallery');
            
            // Add image button
            $gallery.find('.add-gallery-image').on('click', function(e) {
                e.preventDefault();
                HappyPlaceAdmin.openMediaLibrary('gallery');
            });
            
            // Remove image button
            $gallery.on('click', '.remove-gallery-image', function(e) {
                e.preventDefault();
                $(this).closest('.gallery-item').remove();
                HappyPlaceAdmin.updateGalleryOrder();
            });
            
            // Sortable gallery
            $gallery.find('.gallery-items').sortable({
                items: '.gallery-item',
                handle: '.gallery-item-handle',
                update: function() {
                    HappyPlaceAdmin.updateGalleryOrder();
                }
            });
        },
        
        /**
         * Initialize amenities manager
         */
        initAmenitiesManager: function() {
            var $amenities = $('#property-amenities');
            
            // Add amenity button
            $amenities.find('.add-amenity').on('click', function(e) {
                e.preventDefault();
                var amenityHtml = `
                    <div class="amenity-item">
                        <input type="text" name="amenities[]" class="regular-text" placeholder="Enter amenity">
                        <button type="button" class="button remove-amenity">Remove</button>
                    </div>
                `;
                $amenities.find('.amenities-list').append(amenityHtml);
            });
            
            // Remove amenity button
            $amenities.on('click', '.remove-amenity', function(e) {
                e.preventDefault();
                $(this).closest('.amenity-item').remove();
            });
        },
        
        /**
         * Toggle property type fields
         */
        togglePropertyTypeFields: function(propertyType) {
            $('.property-type-field').hide();
            $('.property-type-' + propertyType).show();
        },
        
        /**
         * Initialize SEO meta box
         */
        initSEOMetaBox: function() {
            var $metaBox = $('#seo-meta-box');
            
            if ($metaBox.length) {
                // Character counters
                $metaBox.find('.seo-title').on('input', function() {
                    var length = $(this).val().length;
                    var counter = $(this).siblings('.char-counter');
                    counter.text(length + '/60');
                    
                    if (length > 60) {
                        counter.addClass('over-limit');
                    } else {
                        counter.removeClass('over-limit');
                    }
                });
                
                $metaBox.find('.seo-description').on('input', function() {
                    var length = $(this).val().length;
                    var counter = $(this).siblings('.char-counter');
                    counter.text(length + '/160');
                    
                    if (length > 160) {
                        counter.addClass('over-limit');
                    } else {
                        counter.removeClass('over-limit');
                    }
                });
                
                // SEO preview
                this.updateSEOPreview();
                $metaBox.find('input, textarea').on('input', function() {
                    HappyPlaceAdmin.updateSEOPreview();
                });
            }
        },
        
        /**
         * Update SEO preview
         */
        updateSEOPreview: function() {
            var title = $('#seo_title').val() || $('#title').val() || 'Page Title';
            var description = $('#seo_description').val() || 'Page description...';
            var url = window.location.origin + '/' + ($('#post_name').val() || 'page-url');
            
            $('#seo-preview .preview-title').text(title);
            $('#seo-preview .preview-url').text(url);
            $('#seo-preview .preview-description').text(description);
        },
        
        /**
         * Initialize page options meta box
         */
        initPageOptionsMetaBox: function() {
            var $metaBox = $('#page-options-meta-box');
            
            if ($metaBox.length) {
                // Header options
                $metaBox.find('#header_style').on('change', function() {
                    var headerStyle = $(this).val();
                    $('.header-option').hide();
                    $('.header-option-' + headerStyle).show();
                });
                
                // Background options
                $metaBox.find('#background_type').on('change', function() {
                    var backgroundType = $(this).val();
                    $('.background-option').hide();
                    $('.background-option-' + backgroundType).show();
                });
                
                // Color picker for background
                $metaBox.find('.color-picker').wpColorPicker();
                
                // Background image uploader
                $metaBox.find('.upload-background').on('click', function(e) {
                    e.preventDefault();
                    HappyPlaceAdmin.openMediaLibrary('background');
                });
            }
        },
        
        /**
         * Initialize media uploader
         */
        initMediaUploader: function() {
            // Generic media uploader for various fields
            $('.media-upload-button').on('click', function(e) {
                e.preventDefault();
                var $button = $(this);
                var target = $button.data('target');
                var mediaType = $button.data('media-type') || 'image';
                
                HappyPlaceAdmin.openMediaLibrary(target, mediaType);
            });
        },
        
        /**
         * Open media library
         */
        openMediaLibrary: function(target, mediaType) {
            mediaType = mediaType || 'image';
            
            var mediaFrame = wp.media({
                title: 'Select Media',
                button: {
                    text: 'Use this media'
                },
                library: {
                    type: mediaType
                },
                multiple: target === 'gallery'
            });
            
            mediaFrame.on('select', function() {
                var selection = mediaFrame.state().get('selection');
                
                if (target === 'gallery') {
                    selection.each(function(attachment) {
                        HappyPlaceAdmin.addGalleryImage(attachment.toJSON());
                    });
                } else {
                    var attachment = selection.first().toJSON();
                    HappyPlaceAdmin.setMediaField(target, attachment);
                }
            });
            
            mediaFrame.open();
        },
        
        /**
         * Add gallery image
         */
        addGalleryImage: function(attachment) {
            var $gallery = $('#property-gallery .gallery-items');
            var imageHtml = `
                <div class="gallery-item">
                    <div class="gallery-item-handle">
                        <img src="${attachment.sizes.thumbnail.url}" alt="">
                    </div>
                    <input type="hidden" name="gallery_images[]" value="${attachment.id}">
                    <button type="button" class="remove-gallery-image">Remove</button>
                </div>
            `;
            
            $gallery.append(imageHtml);
            this.updateGalleryOrder();
        },
        
        /**
         * Set media field
         */
        setMediaField: function(target, attachment) {
            var $field = $('#' + target);
            var $preview = $('#' + target + '_preview');
            
            $field.val(attachment.id);
            
            if ($preview.length) {
                var imageUrl = attachment.sizes && attachment.sizes.medium 
                    ? attachment.sizes.medium.url 
                    : attachment.url;
                    
                $preview.html('<img src="' + imageUrl + '" alt="">');
            }
        },
        
        /**
         * Update gallery order
         */
        updateGalleryOrder: function() {
            $('#property-gallery .gallery-item').each(function(index) {
                $(this).find('input[name="gallery_order[]"]').val(index);
            });
        },
        
        /**
         * Initialize address autocomplete
         */
        initAddressAutocomplete: function(input) {
            if (typeof google !== 'undefined' && google.maps && google.maps.places) {
                var autocomplete = new google.maps.places.Autocomplete(input, {
                    types: ['address']
                });
                
                autocomplete.addListener('place_changed', function() {
                    var place = autocomplete.getPlace();
                    HappyPlaceAdmin.fillAddressFields(place);
                });
            }
        },
        
        /**
         * Fill address fields
         */
        fillAddressFields: function(place) {
            var components = place.address_components;
            var addressData = {};
            
            for (var i = 0; i < components.length; i++) {
                var component = components[i];
                var types = component.types;
                
                if (types.indexOf('street_number') > -1) {
                    addressData.street_number = component.long_name;
                }
                if (types.indexOf('route') > -1) {
                    addressData.street_name = component.long_name;
                }
                if (types.indexOf('locality') > -1) {
                    addressData.city = component.long_name;
                }
                if (types.indexOf('administrative_area_level_1') > -1) {
                    addressData.state = component.short_name;
                }
                if (types.indexOf('postal_code') > -1) {
                    addressData.zip = component.long_name;
                }
            }
            
            // Fill individual fields if they exist
            if (addressData.street_number && addressData.street_name) {
                $('#property_address').val(addressData.street_number + ' ' + addressData.street_name);
            }
            if (addressData.city) $('#property_city').val(addressData.city);
            if (addressData.state) $('#property_state').val(addressData.state);
            if (addressData.zip) $('#property_zip').val(addressData.zip);
            
            // Set coordinates
            if (place.geometry) {
                $('#property_lat').val(place.geometry.location.lat());
                $('#property_lng').val(place.geometry.location.lng());
            }
        },
        
        /**
         * Initialize admin enhancements
         */
        initAdminEnhancements: function() {
            // Enhanced admin tables
            this.initAdminTables();
            
            // Bulk actions
            this.initBulkActions();
            
            // Admin notifications
            this.initAdminNotifications();
            
            // Dashboard widgets
            this.initDashboardWidgets();
        },
        
        /**
         * Initialize admin tables
         */
        initAdminTables: function() {
            // Enhanced sorting
            $('.wp-list-table .column-sortable').on('click', function() {
                var $table = $(this).closest('table');
                $table.find('.loading-indicator').show();
            });
            
            // Quick edit enhancements
            $('.wp-list-table .editinline').on('click', function() {
                setTimeout(function() {
                    HappyPlaceAdmin.enhanceQuickEdit();
                }, 100);
            });
        },
        
        /**
         * Enhance quick edit
         */
        enhanceQuickEdit: function() {
            var $quickEdit = $('.quick-edit-row');
            
            if ($quickEdit.length) {
                // Add color pickers
                $quickEdit.find('.color-picker').wpColorPicker();
                
                // Add media uploaders
                $quickEdit.find('.media-upload-button').off('click').on('click', function(e) {
                    e.preventDefault();
                    var target = $(this).data('target');
                    HappyPlaceAdmin.openMediaLibrary(target);
                });
            }
        },
        
        /**
         * Initialize bulk actions
         */
        initBulkActions: function() {
            // Custom bulk actions
            $('#doaction, #doaction2').on('click', function(e) {
                var action = $(this).siblings('select').val();
                
                if (action.startsWith('hph_')) {
                    e.preventDefault();
                    HappyPlaceAdmin.handleCustomBulkAction(action);
                }
            });
        },
        
        /**
         * Handle custom bulk action
         */
        handleCustomBulkAction: function(action) {
            var checkedItems = $('input[name="post[]"]:checked').map(function() {
                return this.value;
            }).get();
            
            if (checkedItems.length === 0) {
                alert('Please select items to perform this action.');
                return;
            }
            
            // Confirm action
            if (confirm('Are you sure you want to perform this action on ' + checkedItems.length + ' items?')) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'hph_bulk_action',
                        bulk_action: action,
                        post_ids: checkedItems,
                        nonce: $('#_wpnonce').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Action failed: ' + response.data.message);
                        }
                    }
                });
            }
        },
        
        /**
         * Initialize admin notifications
         */
        initAdminNotifications: function() {
            // Auto-dismiss notifications
            $('.notice.is-dismissible').each(function() {
                var $notice = $(this);
                
                setTimeout(function() {
                    $notice.fadeOut();
                }, 10000);
            });
            
            // Enhanced notice styling
            $('.notice').addClass('hph-notice');
        },
        
        /**
         * Initialize dashboard widgets
         */
        initDashboardWidgets: function() {
            // Property stats widget
            this.initPropertyStatsWidget();
            
            // Recent activity widget
            this.initRecentActivityWidget();
            
            // Performance widget
            this.initPerformanceWidget();
        },
        
        /**
         * Initialize property stats widget
         */
        initPropertyStatsWidget: function() {
            var $widget = $('#property-stats-widget');
            
            if ($widget.length) {
                // Refresh stats button
                $widget.find('.refresh-stats').on('click', function(e) {
                    e.preventDefault();
                    HappyPlaceAdmin.refreshPropertyStats();
                });
                
                // Chart initialization (if Chart.js is available)
                if (typeof Chart !== 'undefined') {
                    this.initPropertyChart();
                }
            }
        },
        
        /**
         * Initialize property chart
         */
        initPropertyChart: function() {
            var ctx = document.getElementById('property-chart');
            
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                        datasets: [{
                            label: 'Properties Listed',
                            data: [12, 19, 3, 5, 2, 3],
                            borderColor: '#2E7D32',
                            backgroundColor: 'rgba(46, 125, 50, 0.1)'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }
        },
        
        /**
         * Refresh property stats
         */
        refreshPropertyStats: function() {
            var $widget = $('#property-stats-widget');
            var $button = $widget.find('.refresh-stats');
            
            $button.prop('disabled', true).text('Refreshing...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_refresh_property_stats',
                    nonce: $('#_wpnonce').val()
                },
                success: function(response) {
                    if (response.success) {
                        $widget.find('.stats-content').html(response.data.html);
                    }
                },
                complete: function() {
                    $button.prop('disabled', false).text('Refresh');
                }
            });
        },
        
        /**
         * Initialize asset manager
         */
        initAssetManager: function() {
            var $assetManager = $('#asset-manager-settings');
            
            if ($assetManager.length) {
                // Toggle asset loading
                $assetManager.find('.toggle-asset').on('change', function() {
                    var assetId = $(this).data('asset-id');
                    var enabled = $(this).is(':checked');
                    
                    HappyPlaceAdmin.toggleAsset(assetId, enabled);
                });
                
                // Asset priority sorting
                $assetManager.find('.asset-list').sortable({
                    items: '.asset-item',
                    handle: '.asset-handle',
                    update: function() {
                        HappyPlaceAdmin.updateAssetPriorities();
                    }
                });
                
                // Clear asset cache
                $assetManager.find('.clear-asset-cache').on('click', function(e) {
                    e.preventDefault();
                    HappyPlaceAdmin.clearAssetCache();
                });
            }
        },
        
        /**
         * Toggle asset
         */
        toggleAsset: function(assetId, enabled) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_toggle_asset',
                    asset_id: assetId,
                    enabled: enabled,
                    nonce: $('#_wpnonce').val()
                }
            });
        },
        
        /**
         * Update asset priorities
         */
        updateAssetPriorities: function() {
            var priorities = {};
            
            $('.asset-item').each(function(index) {
                var assetId = $(this).data('asset-id');
                priorities[assetId] = index;
            });
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_update_asset_priorities',
                    priorities: priorities,
                    nonce: $('#_wpnonce').val()
                }
            });
        },
        
        /**
         * Clear asset cache
         */
        clearAssetCache: function() {
            var $button = $('.clear-asset-cache');
            $button.prop('disabled', true).text('Clearing...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'hph_clear_asset_cache',
                    nonce: $('#_wpnonce').val()
                },
                success: function(response) {
                    if (response.success) {
                        alert('Asset cache cleared successfully!');
                    }
                },
                complete: function() {
                    $button.prop('disabled', false).text('Clear Cache');
                }
            });
        },
        
        /**
         * Format price
         */
        formatPrice: function(value) {
            if (!value) return '';
            
            return '$' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }
    };
    
    // Initialize admin when DOM is ready
    $(document).ready(function() {
        HappyPlaceAdmin.init();
    });
    
})(jQuery);
