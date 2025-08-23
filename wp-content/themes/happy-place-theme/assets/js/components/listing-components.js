/**
 * Happy Place Theme - Listing Components JavaScript
 * Interactive functionality for listing page components
 * 
 * File Location: /wp-content/themes/happy-place/assets/js/components/listing-components.js
 */

(function() {
    'use strict';

    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initListingComponents);
    } else {
        initListingComponents();
    }

    function initListingComponents() {
        // Initialize all listing components
        initPropertyGallery();
        initVirtualTour();
        initInteractiveMap();
        initMortgageCalculator();
        initContactForm();
        initShareButtons();
        initFavoriteButton();
        initPrintButton();
        initScheduleShowing();
        initPropertyTabs();
        initFloorPlanViewer();
        initNeighborhoodExplorer();
    }

    // ================================================
    // PROPERTY GALLERY
    // ================================================
    function initPropertyGallery() {
        const galleries = document.querySelectorAll('.property-gallery');
        
        galleries.forEach(gallery => {
            const mainImage = gallery.querySelector('.gallery-main-image img');
            const thumbnails = gallery.querySelectorAll('.gallery-thumbnail');
            const lightboxBtn = gallery.querySelector('.gallery-lightbox-btn');
            const galleryNav = gallery.querySelector('.gallery-nav');
            const prevBtn = gallery.querySelector('.gallery-nav-prev');
            const nextBtn = gallery.querySelector('.gallery-nav-next');
            const counter = gallery.querySelector('.gallery-counter');
            
            let currentIndex = 0;
            const images = Array.from(thumbnails).map(thumb => ({
                src: thumb.dataset.fullSrc || thumb.querySelector('img').src,
                alt: thumb.querySelector('img').alt,
                caption: thumb.dataset.caption || ''
            }));
            
            // Thumbnail click handler
            thumbnails.forEach((thumb, index) => {
                thumb.addEventListener('click', () => {
                    setActiveImage(index);
                });
            });
            
            // Navigation handlers
            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    currentIndex = currentIndex > 0 ? currentIndex - 1 : images.length - 1;
                    setActiveImage(currentIndex);
                });
            }
            
            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    currentIndex = currentIndex < images.length - 1 ? currentIndex + 1 : 0;
                    setActiveImage(currentIndex);
                });
            }
            
            // Keyboard navigation
            gallery.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowLeft' && prevBtn) prevBtn.click();
                if (e.key === 'ArrowRight' && nextBtn) nextBtn.click();
            });
            
            // Lightbox handler
            if (lightboxBtn) {
                lightboxBtn.addEventListener('click', () => {
                    openLightbox(images, currentIndex);
                });
            }
            
            // Touch/swipe support for mobile
            let touchStartX = 0;
            let touchEndX = 0;
            
            if (mainImage) {
                mainImage.addEventListener('touchstart', (e) => {
                    touchStartX = e.changedTouches[0].screenX;
                });
                
                mainImage.addEventListener('touchend', (e) => {
                    touchEndX = e.changedTouches[0].screenX;
                    handleSwipe();
                });
            }
            
            function handleSwipe() {
                if (touchEndX < touchStartX - 50) nextBtn?.click();
                if (touchEndX > touchStartX + 50) prevBtn?.click();
            }
            
            function setActiveImage(index) {
                currentIndex = index;
                
                // Update main image
                if (mainImage && images[index]) {
                    mainImage.src = images[index].src;
                    mainImage.alt = images[index].alt;
                }
                
                // Update active thumbnail
                thumbnails.forEach((thumb, i) => {
                    thumb.classList.toggle('active', i === index);
                });
                
                // Update counter
                if (counter) {
                    counter.textContent = `${index + 1} / ${images.length}`;
                }
            }
        });
    }

    // ================================================
    // LIGHTBOX
    // ================================================
    function openLightbox(images, startIndex = 0) {
        // Create lightbox container
        const lightbox = document.createElement('div');
        lightbox.className = 'hph-lightbox';
        lightbox.innerHTML = `
            <div class="lightbox-overlay"></div>
            <div class="lightbox-container">
                <button class="lightbox-close" aria-label="Close">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
                <div class="lightbox-content">
                    <button class="lightbox-nav lightbox-prev" aria-label="Previous">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </button>
                    <div class="lightbox-image-container">
                        <img class="lightbox-image" src="" alt="">
                        <div class="lightbox-caption"></div>
                    </div>
                    <button class="lightbox-nav lightbox-next" aria-label="Next">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </button>
                </div>
                <div class="lightbox-thumbnails"></div>
                <div class="lightbox-counter"></div>
            </div>
        `;
        
        document.body.appendChild(lightbox);
        document.body.style.overflow = 'hidden';
        
        const closeBtn = lightbox.querySelector('.lightbox-close');
        const overlay = lightbox.querySelector('.lightbox-overlay');
        const prevBtn = lightbox.querySelector('.lightbox-prev');
        const nextBtn = lightbox.querySelector('.lightbox-next');
        const image = lightbox.querySelector('.lightbox-image');
        const caption = lightbox.querySelector('.lightbox-caption');
        const counter = lightbox.querySelector('.lightbox-counter');
        const thumbContainer = lightbox.querySelector('.lightbox-thumbnails');
        
        let currentIndex = startIndex;
        
        // Create thumbnails
        images.forEach((img, index) => {
            const thumb = document.createElement('button');
            thumb.className = 'lightbox-thumb';
            thumb.innerHTML = `<img src="${img.src}" alt="${img.alt}">`;
            thumb.addEventListener('click', () => showImage(index));
            thumbContainer.appendChild(thumb);
        });
        
        const thumbs = thumbContainer.querySelectorAll('.lightbox-thumb');
        
        // Show initial image
        showImage(currentIndex);
        
        // Navigation
        prevBtn.addEventListener('click', () => {
            currentIndex = currentIndex > 0 ? currentIndex - 1 : images.length - 1;
            showImage(currentIndex);
        });
        
        nextBtn.addEventListener('click', () => {
            currentIndex = currentIndex < images.length - 1 ? currentIndex + 1 : 0;
            showImage(currentIndex);
        });
        
        // Close handlers
        closeBtn.addEventListener('click', closeLightbox);
        overlay.addEventListener('click', closeLightbox);
        
        // Keyboard navigation
        document.addEventListener('keydown', handleKeyboard);
        
        function handleKeyboard(e) {
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowLeft') prevBtn.click();
            if (e.key === 'ArrowRight') nextBtn.click();
        }
        
        function showImage(index) {
            currentIndex = index;
            image.src = images[index].src;
            image.alt = images[index].alt;
            caption.textContent = images[index].caption || '';
            counter.textContent = `${index + 1} / ${images.length}`;
            
            thumbs.forEach((thumb, i) => {
                thumb.classList.toggle('active', i === index);
            });
        }
        
        function closeLightbox() {
            lightbox.classList.add('closing');
            setTimeout(() => {
                lightbox.remove();
                document.body.style.overflow = '';
                document.removeEventListener('keydown', handleKeyboard);
            }, 300);
        }
    }

    // ================================================
    // VIRTUAL TOUR
    // ================================================
    function initVirtualTour() {
        const tourButtons = document.querySelectorAll('.virtual-tour-btn');
        
        tourButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const tourUrl = btn.dataset.tourUrl;
                const tourType = btn.dataset.tourType || 'iframe';
                
                if (tourType === 'iframe') {
                    openVirtualTourModal(tourUrl);
                } else if (tourType === 'matterport') {
                    openMatterportTour(tourUrl);
                } else if (tourType === 'external') {
                    window.open(tourUrl, '_blank');
                }
            });
        });
    }
    
    function openVirtualTourModal(url) {
        const modal = document.createElement('div');
        modal.className = 'hph-modal virtual-tour-modal';
        modal.innerHTML = `
            <div class="modal-overlay"></div>
            <div class="modal-container">
                <div class="modal-header">
                    <h3 class="modal-title">Virtual Tour</h3>
                    <button class="modal-close" aria-label="Close">×</button>
                </div>
                <div class="modal-body">
                    <div class="tour-container">
                        <iframe src="${url}" frameborder="0" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        document.body.style.overflow = 'hidden';
        
        // Add active class for animation
        setTimeout(() => modal.classList.add('active'), 10);
        
        // Close handlers
        const closeBtn = modal.querySelector('.modal-close');
        const overlay = modal.querySelector('.modal-overlay');
        
        [closeBtn, overlay].forEach(el => {
            el.addEventListener('click', () => {
                modal.classList.remove('active');
                setTimeout(() => {
                    modal.remove();
                    document.body.style.overflow = '';
                }, 300);
            });
        });
    }

    // ================================================
    // INTERACTIVE MAP
    // ================================================
    function initInteractiveMap() {
        const mapContainers = document.querySelectorAll('.property-map');
        
        mapContainers.forEach(container => {
            const lat = parseFloat(container.dataset.lat);
            const lng = parseFloat(container.dataset.lng);
            const address = container.dataset.address;
            
            // Check if Google Maps or Mapbox is loaded
            if (typeof google !== 'undefined' && google.maps) {
                initGoogleMap(container, lat, lng, address);
            } else if (typeof mapboxgl !== 'undefined') {
                initMapbox(container, lat, lng, address);
            } else {
                // Fallback to static map or placeholder
                showStaticMap(container, lat, lng);
            }
        });
    }
    
    function initGoogleMap(container, lat, lng, address) {
        const map = new google.maps.Map(container, {
            center: { lat, lng },
            zoom: 15,
            styles: getMapStyles(),
            disableDefaultUI: false,
            zoomControl: true,
            mapTypeControl: false,
            scaleControl: false,
            streetViewControl: true,
            rotateControl: false,
            fullscreenControl: true
        });
        
        const marker = new google.maps.Marker({
            position: { lat, lng },
            map: map,
            title: address,
            animation: google.maps.Animation.DROP
        });
        
        // Add nearby places
        const nearbyButtons = container.parentElement.querySelectorAll('.map-nearby-btn');
        nearbyButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const type = btn.dataset.placeType;
                searchNearbyPlaces(map, lat, lng, type);
            });
        });
    }
    
    function getMapStyles() {
        // Custom map styles matching Happy Place brand
        return [
            {
                "featureType": "water",
                "elementType": "geometry",
                "stylers": [{"color": "#e9f4fa"}]
            },
            {
                "featureType": "landscape",
                "elementType": "geometry",
                "stylers": [{"color": "#f5f5f5"}]
            },
            {
                "featureType": "road",
                "elementType": "geometry",
                "stylers": [{"color": "#ffffff"}]
            },
            {
                "featureType": "poi",
                "elementType": "geometry",
                "stylers": [{"color": "#eeeeee"}]
            },
            {
                "featureType": "transit",
                "elementType": "geometry",
                "stylers": [{"color": "#e5e5e5"}]
            },
            {
                "elementType": "labels.text.stroke",
                "stylers": [{"color": "#ffffff"}]
            },
            {
                "elementType": "labels.text.fill",
                "stylers": [{"color": "#616161"}]
            }
        ];
    }

    // ================================================
    // MORTGAGE CALCULATOR
    // ================================================
    function initMortgageCalculator() {
        const calculators = document.querySelectorAll('.mortgage-calculator');
        
        calculators.forEach(calc => {
            const priceInput = calc.querySelector('#calc-price');
            const downPaymentInput = calc.querySelector('#calc-down-payment');
            const downPaymentPercent = calc.querySelector('#calc-down-percent');
            const interestRateInput = calc.querySelector('#calc-interest');
            const loanTermInput = calc.querySelector('#calc-term');
            const propertyTaxInput = calc.querySelector('#calc-property-tax');
            const hoaInput = calc.querySelector('#calc-hoa');
            const insuranceInput = calc.querySelector('#calc-insurance');
            
            const calculateBtn = calc.querySelector('.calc-calculate-btn');
            const resetBtn = calc.querySelector('.calc-reset-btn');
            
            const monthlyPaymentEl = calc.querySelector('.monthly-payment-amount');
            const principalEl = calc.querySelector('[data-payment="principal"]');
            const interestEl = calc.querySelector('[data-payment="interest"]');
            const taxEl = calc.querySelector('[data-payment="tax"]');
            const hoaEl = calc.querySelector('[data-payment="hoa"]');
            const insuranceEl = calc.querySelector('[data-payment="insurance"]');
            
            // Sync down payment percentage
            downPaymentInput?.addEventListener('input', () => {
                const price = parseFloat(priceInput.value) || 0;
                const down = parseFloat(downPaymentInput.value) || 0;
                if (price > 0) {
                    downPaymentPercent.value = ((down / price) * 100).toFixed(1);
                }
            });
            
            downPaymentPercent?.addEventListener('input', () => {
                const price = parseFloat(priceInput.value) || 0;
                const percent = parseFloat(downPaymentPercent.value) || 0;
                downPaymentInput.value = (price * percent / 100).toFixed(0);
            });
            
            // Calculate mortgage
            calculateBtn?.addEventListener('click', calculateMortgage);
            
            // Reset form
            resetBtn?.addEventListener('click', () => {
                calc.querySelector('form').reset();
                monthlyPaymentEl.textContent = '$0';
                principalEl.textContent = '$0';
                interestEl.textContent = '$0';
                taxEl.textContent = '$0';
                hoaEl.textContent = '$0';
                insuranceEl.textContent = '$0';
            });
            
            function calculateMortgage() {
                const price = parseFloat(priceInput.value) || 0;
                const downPayment = parseFloat(downPaymentInput.value) || 0;
                const principal = price - downPayment;
                const monthlyRate = (parseFloat(interestRateInput.value) || 0) / 100 / 12;
                const numPayments = (parseFloat(loanTermInput.value) || 30) * 12;
                const monthlyTax = (parseFloat(propertyTaxInput.value) || 0) / 12;
                const monthlyHOA = parseFloat(hoaInput.value) || 0;
                const monthlyInsurance = (parseFloat(insuranceInput.value) || 0) / 12;
                
                // Calculate monthly payment using mortgage formula
                let monthlyPrincipalInterest = 0;
                if (monthlyRate > 0) {
                    monthlyPrincipalInterest = principal * 
                        (monthlyRate * Math.pow(1 + monthlyRate, numPayments)) /
                        (Math.pow(1 + monthlyRate, numPayments) - 1);
                } else {
                    monthlyPrincipalInterest = principal / numPayments;
                }
                
                const monthlyInterest = principal * monthlyRate;
                const monthlyPrincipal = monthlyPrincipalInterest - monthlyInterest;
                
                const totalMonthly = monthlyPrincipalInterest + monthlyTax + monthlyHOA + monthlyInsurance;
                
                // Update display
                monthlyPaymentEl.textContent = '$' + totalMonthly.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                principalEl.textContent = '$' + monthlyPrincipal.toFixed(0);
                interestEl.textContent = '$' + monthlyInterest.toFixed(0);
                taxEl.textContent = '$' + monthlyTax.toFixed(0);
                hoaEl.textContent = '$' + monthlyHOA.toFixed(0);
                insuranceEl.textContent = '$' + monthlyInsurance.toFixed(0);
                
                // Animate result
                monthlyPaymentEl.parentElement.classList.add('highlight');
                setTimeout(() => {
                    monthlyPaymentEl.parentElement.classList.remove('highlight');
                }, 1000);
            }
        });
    }

    // ================================================
    // CONTACT FORM
    // ================================================
    function initContactForm() {
        const forms = document.querySelectorAll('.listing-contact-form');
        
        forms.forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                
                // Show loading state
                submitBtn.disabled = true;
                submitBtn.textContent = 'Sending...';
                
                // Collect form data
                const formData = new FormData(form);
                formData.append('action', 'hph_listing_inquiry');
                formData.append('nonce', hph_ajax.nonce);
                
                try {
                    const response = await fetch(hph_ajax.ajax_url, {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        showFormMessage(form, 'success', 'Thank you! Your message has been sent.');
                        form.reset();
                    } else {
                        showFormMessage(form, 'error', result.data || 'An error occurred. Please try again.');
                    }
                } catch (error) {
                    showFormMessage(form, 'error', 'Network error. Please try again.');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            });
        });
    }
    
    function showFormMessage(form, type, message) {
        const existingMessage = form.querySelector('.form-message');
        if (existingMessage) existingMessage.remove();
        
        const messageEl = document.createElement('div');
        messageEl.className = `form-message form-message-${type}`;
        messageEl.textContent = message;
        
        form.appendChild(messageEl);
        
        setTimeout(() => {
            messageEl.classList.add('fade-out');
            setTimeout(() => messageEl.remove(), 300);
        }, 5000);
    }

    // ================================================
    // SHARE BUTTONS
    // ================================================
    function initShareButtons() {
        const shareButtons = document.querySelectorAll('.share-btn');
        
        shareButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const platform = btn.dataset.platform;
                const url = window.location.href;
                const title = document.title;
                
                let shareUrl = '';
                
                switch(platform) {
                    case 'facebook':
                        shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
                        break;
                    case 'twitter':
                        shareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`;
                        break;
                    case 'linkedin':
                        shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`;
                        break;
                    case 'pinterest':
                        const image = document.querySelector('.gallery-main-image img')?.src || '';
                        shareUrl = `https://pinterest.com/pin/create/button/?url=${encodeURIComponent(url)}&media=${encodeURIComponent(image)}&description=${encodeURIComponent(title)}`;
                        break;
                    case 'email':
                        shareUrl = `mailto:?subject=${encodeURIComponent(title)}&body=${encodeURIComponent(url)}`;
                        break;
                    case 'copy':
                        copyToClipboard(url);
                        showToast('Link copied to clipboard!');
                        return;
                }
                
                if (shareUrl) {
                    window.open(shareUrl, 'share', 'width=550,height=450');
                }
            });
        });
    }

    // ================================================
    // FAVORITE BUTTON
    // ================================================
    function initFavoriteButton() {
        const favoriteButtons = document.querySelectorAll('.favorite-btn');
        
        favoriteButtons.forEach(btn => {
            btn.addEventListener('click', async () => {
                const listingId = btn.dataset.listingId;
                const isFavorited = btn.classList.contains('favorited');
                
                // Optimistic UI update
                btn.classList.toggle('favorited');
                
                try {
                    const response = await fetch(hph_ajax.ajax_url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            action: 'hph_toggle_favorite',
                            listing_id: listingId,
                            favorited: !isFavorited,
                            nonce: hph_ajax.nonce
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (!result.success) {
                        // Revert on error
                        btn.classList.toggle('favorited');
                        showToast('Error updating favorites');
                    } else {
                        showToast(isFavorited ? 'Removed from favorites' : 'Added to favorites');
                    }
                } catch (error) {
                    // Revert on error
                    btn.classList.toggle('favorited');
                    showToast('Network error');
                }
            });
        });
    }

    // ================================================
    // PRINT BUTTON
    // ================================================
    function initPrintButton() {
        const printButtons = document.querySelectorAll('.print-btn');
        
        printButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                window.print();
            });
        });
    }

    // ================================================
    // SCHEDULE SHOWING
    // ================================================
    function initScheduleShowing() {
        const scheduleButtons = document.querySelectorAll('.schedule-showing-btn');
        
        scheduleButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const modal = createScheduleModal(btn.dataset.listingId);
                document.body.appendChild(modal);
                modal.classList.add('active');
                
                initScheduleForm(modal);
            });
        });
    }
    
    function createScheduleModal(listingId) {
        const modal = document.createElement('div');
        modal.className = 'hph-modal schedule-modal';
        modal.innerHTML = `
            <div class="modal-overlay"></div>
            <div class="modal-container">
                <div class="modal-header">
                    <h3 class="modal-title">Schedule a Showing</h3>
                    <button class="modal-close" aria-label="Close">×</button>
                </div>
                <div class="modal-body">
                    <form class="schedule-form">
                        <input type="hidden" name="listing_id" value="${listingId}">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="schedule-date">Preferred Date</label>
                                <input type="date" id="schedule-date" name="date" required>
                            </div>
                            <div class="form-group">
                                <label for="schedule-time">Preferred Time</label>
                                <select id="schedule-time" name="time" required>
                                    <option value="">Select time</option>
                                    <option value="09:00">9:00 AM</option>
                                    <option value="10:00">10:00 AM</option>
                                    <option value="11:00">11:00 AM</option>
                                    <option value="12:00">12:00 PM</option>
                                    <option value="13:00">1:00 PM</option>
                                    <option value="14:00">2:00 PM</option>
                                    <option value="15:00">3:00 PM</option>
                                    <option value="16:00">4:00 PM</option>
                                    <option value="17:00">5:00 PM</option>
                                    <option value="18:00">6:00 PM</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="schedule-name">Your Name</label>
                            <input type="text" id="schedule-name" name="name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="schedule-email">Email</label>
                            <input type="email" id="schedule-email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="schedule-phone">Phone</label>
                            <input type="tel" id="schedule-phone" name="phone" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="schedule-message">Additional Message (Optional)</label>
                            <textarea id="schedule-message" name="message" rows="3"></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary modal-cancel">Cancel</button>
                            <button type="submit" class="btn btn-primary">Schedule Showing</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        
        // Close handlers
        const closeBtn = modal.querySelector('.modal-close');
        const cancelBtn = modal.querySelector('.modal-cancel');
        const overlay = modal.querySelector('.modal-overlay');
        
        [closeBtn, cancelBtn, overlay].forEach(el => {
            el.addEventListener('click', () => {
                modal.classList.remove('active');
                setTimeout(() => modal.remove(), 300);
            });
        });
        
        return modal;
    }

    // ================================================
    // PROPERTY TABS
    // ================================================
    function initPropertyTabs() {
        const tabContainers = document.querySelectorAll('.property-tabs');
        
        tabContainers.forEach(container => {
            const tabs = container.querySelectorAll('.tab-btn');
            const panels = container.querySelectorAll('.tab-panel');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    const targetPanel = tab.dataset.tab;
                    
                    // Update active states
                    tabs.forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');
                    
                    panels.forEach(panel => {
                        panel.classList.toggle('active', panel.id === targetPanel);
                    });
                    
                    // Save to URL hash
                    window.location.hash = targetPanel;
                });
            });
            
            // Check for hash on load
            if (window.location.hash) {
                const hashTab = container.querySelector(`[data-tab="${window.location.hash.slice(1)}"]`);
                if (hashTab) hashTab.click();
            }
        });
    }

    // ================================================
    // FLOOR PLAN VIEWER
    // ================================================
    function initFloorPlanViewer() {
        const viewers = document.querySelectorAll('.floor-plan-viewer');
        
        viewers.forEach(viewer => {
            const images = viewer.querySelectorAll('.floor-plan-image');
            const selector = viewer.querySelector('.floor-selector');
            
            if (selector) {
                selector.addEventListener('change', () => {
                    const selectedFloor = selector.value;
                    
                    images.forEach(img => {
                        img.classList.toggle('active', img.dataset.floor === selectedFloor);
                    });
                });
            }
            
            // Add zoom functionality
            images.forEach(img => {
                img.addEventListener('click', () => {
                    openLightbox([{
                        src: img.src,
                        alt: img.alt,
                        caption: img.dataset.floorName || ''
                    }], 0);
                });
            });
        });
    }

    // ================================================
    // NEIGHBORHOOD EXPLORER
    // ================================================
    function initNeighborhoodExplorer() {
        const explorers = document.querySelectorAll('.neighborhood-explorer');
        
        explorers.forEach(explorer => {
            const categoryBtns = explorer.querySelectorAll('.neighborhood-category-btn');
            const placesList = explorer.querySelector('.neighborhood-places-list');
            const lat = parseFloat(explorer.dataset.lat);
            const lng = parseFloat(explorer.dataset.lng);
            
            categoryBtns.forEach(btn => {
                btn.addEventListener('click', async () => {
                    const category = btn.dataset.category;
                    
                    // Update active state
                    categoryBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    
                    // Show loading state
                    placesList.innerHTML = '<div class="loading">Loading places...</div>';
                    
                    try {
                        const places = await fetchNearbyPlaces(lat, lng, category);
                        displayPlaces(placesList, places);
                    } catch (error) {
                        placesList.innerHTML = '<div class="error">Error loading places</div>';
                    }
                });
            });
        });
    }
    
    async function fetchNearbyPlaces(lat, lng, category) {
        const response = await fetch(hph_ajax.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'hph_get_nearby_places',
                lat: lat,
                lng: lng,
                category: category,
                nonce: hph_ajax.nonce
            })
        });
        
        const result = await response.json();
        return result.data || [];
    }
    
    function displayPlaces(container, places) {
        if (places.length === 0) {
            container.innerHTML = '<div class="no-results">No places found</div>';
            return;
        }
        
        container.innerHTML = places.map(place => `
            <div class="neighborhood-place">
                <div class="place-icon">
                    <img src="${place.icon}" alt="${place.type}">
                </div>
                <div class="place-info">
                    <h4 class="place-name">${place.name}</h4>
                    <p class="place-address">${place.address}</p>
                    <div class="place-meta">
                        <span class="place-distance">${place.distance}</span>
                        ${place.rating ? `<span class="place-rating">★ ${place.rating}</span>` : ''}
                    </div>
                </div>
            </div>
        `).join('');
    }

    // ================================================
    // UTILITY FUNCTIONS
    // ================================================
    function copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text);
        } else {
            // Fallback for older browsers
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
        }
    }
    
    function showToast(message, duration = 3000) {
        const toast = document.createElement('div');
        toast.className = 'hph-toast';
        toast.textContent = message;
        document.body.appendChild(toast);
        
        // Trigger animation
        setTimeout(() => toast.classList.add('show'), 10);
        
        // Remove after duration
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }
    
    // Public API
    window.HPHListingComponents = {
        openLightbox,
        showToast,
        initPropertyGallery,
        initVirtualTour,
        initInteractiveMap
    };
})();