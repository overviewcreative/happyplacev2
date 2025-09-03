/**
 * Single Listing JavaScript
 * Handles interactivity for single listing pages
 */

(function($) {
    'use strict';
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        
        // Initialize gallery lightbox
        initGalleryLightbox();
        
        // Initialize map if coordinates exist
        initPropertyMap();
        
        // Handle RSVP form submissions
        handleRSVPForm();
        
        // Handle contact form submissions
        handleContactForm();
        
        // Initialize smooth scrolling
        initSmoothScroll();
        
        // Initialize print functionality
        initPrintFunction();
        
        // Initialize share functionality
        initShareFunction();
        
    });
    
    /**
     * Initialize Gallery Lightbox
     */
    function initGalleryLightbox() {
        $('.hph-gallery-thumb, .hph-view-all-photos').on('click', function() {
            // Open gallery lightbox
            if (typeof openLightbox === 'function') {
                const index = $(this).data('index') || 0;
                openLightbox(index);
            }
        });
    }
    
    /**
     * Initialize Property Map
     */
    function initPropertyMap() {
        const mapElement = document.getElementById('property-map-' + hph_listing.listing_id);
        
        if (mapElement && hph_listing.mapbox_key) {
            const lat = parseFloat(mapElement.dataset.lat);
            const lng = parseFloat(mapElement.dataset.lng);
            
            mapboxgl.accessToken = hph_listing.mapbox_key;
            
            const map = new mapboxgl.Map({
                container: mapElement.id,
                style: 'mapbox://styles/mapbox/streets-v12',
                center: [lng, lat],
                zoom: 15
            });
            
            // Add marker
            new mapboxgl.Marker({ color: '#3b82f6' })
                .setLngLat([lng, lat])
                .addTo(map);
            
            // Add navigation controls
            map.addControl(new mapboxgl.NavigationControl(), 'top-right');
        }
    }
    
    /**
     * Handle RSVP Form Submissions
     */
    function handleRSVPForm() {
        $('#rsvp-form').on('submit', function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const formData = $form.serialize() + '&action=hph_submit_rsvp&nonce=' + hph_listing.nonce;
            
            // Disable submit button
            $form.find('[type="submit"]').prop('disabled', true);
            
            $.ajax({
                url: hph_listing.ajax_url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        $form[0].reset();
                        closeRSVPModal();
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                },
                complete: function() {
                    $form.find('[type="submit"]').prop('disabled', false);
                }
            });
        });
    }
    
    /**
     * Handle Contact Form Submissions
     */
    function handleContactForm() {
        $('#agent-contact-form').on('submit', function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const formData = $form.serialize() + '&action=hph_submit_contact&nonce=' + hph_listing.nonce;
            
            // Disable submit button
            $form.find('[type="submit"]').prop('disabled', true);
            
            $.ajax({
                url: hph_listing.ajax_url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        $form.after('<div class="hph-form-message hph-form-message--success">' + response.data.message + '</div>');
                        $form[0].reset();
                        
                        // Remove message after 5 seconds
                        setTimeout(function() {
                            $('.hph-form-message').fadeOut();
                        }, 5000);
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                },
                complete: function() {
                    $form.find('[type="submit"]').prop('disabled', false);
                }
            });
        });
    }
    
    /**
     * Initialize Smooth Scrolling
     */
    function initSmoothScroll() {
        $('a[href*="#"]:not([href="#"])').on('click', function() {
            if (location.pathname.replace(/^\//, '') === this.pathname.replace(/^\//, '') && location.hostname === this.hostname) {
                const target = $(this.hash);
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 100
                    }, 800);
                    return false;
                }
            }
        });
    }
    
    /**
     * Initialize Print Function
     */
    function initPrintFunction() {
        $('#print-listing, #print-calculation').on('click', function() {
            window.print();
        });
    }
    
    /**
     * Initialize Share Function
     */
    function initShareFunction() {
        $('#share-listing, #share-calculation').on('click', function() {
            if (navigator.share) {
                navigator.share({
                    title: document.title,
                    text: 'Check out this property',
                    url: window.location.href
                });
            } else {
                // Fallback: Copy to clipboard
                const dummy = document.createElement('input');
                document.body.appendChild(dummy);
                dummy.value = window.location.href;
                dummy.select();
                document.execCommand('copy');
                document.body.removeChild(dummy);
                
                alert('Link copied to clipboard!');
            }
        });
    }
    
})(jQuery);

// Global functions for modal handling
function openRSVPModal(index) {
    document.getElementById('rsvp-modal').classList.remove('hph-hidden');
    document.body.style.overflow = 'hidden';
}

function closeRSVPModal() {
    document.getElementById('rsvp-modal').classList.add('hph-hidden');
    document.body.style.overflow = '';
}

function openGalleryLightbox() {
    // Implement gallery lightbox opening
    console.log('Opening gallery lightbox');
}