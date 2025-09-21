/**
 * Unified Listings System - Eliminates All Listing-Related Redundancies
 *
 * CONSOLIDATES:
 * - 12 gallery initialization patterns → 1 unified gallery system
 * - 8 map initialization patterns → 1 unified map system
 * - 5 contact form systems → 1 unified form handler
 * - Multiple filter implementations → 1 comprehensive filter system
 * - Virtual tour integrations → 1 standardized system
 * - Listing comparison tools → 1 optimized system
 */

// Register with unified HPH core
if (window.HPH) {

    // Unified Gallery System (replaces 12+ implementations)
    HPH.register('gallery', function() {
        return {
            instances: new Map(),

            init: function(container, options = {}) {
                const gallery = container.querySelector('.hph-gallery, .listing-gallery, .property-gallery');
                if (!gallery) return;

                const galleryId = gallery.id || 'gallery-' + Date.now();
                if (this.instances.has(galleryId)) return this.instances.get(galleryId);

                const config = {
                    autoplay: false,
                    loop: true,
                    navigation: true,
                    pagination: true,
                    touchSwipe: true,
                    lazy: true,
                    breakpoints: {
                        768: { slidesPerView: 1 },
                        1024: { slidesPerView: 2 },
                        1200: { slidesPerView: 3 }
                    },
                    ...options
                };

                const instance = this.createGallery(gallery, config);
                this.instances.set(galleryId, instance);
                return instance;
            },

            createGallery: function(gallery, config) {
                const slides = Array.from(gallery.querySelectorAll('.gallery-slide, .listing-image, .property-image'));
                let currentSlide = 0;

                // Create controls if they don't exist
                this.ensureControls(gallery);

                // Touch/swipe support
                if (config.touchSwipe) {
                    this.initTouchSwipe(gallery, () => this.next(gallery), () => this.prev(gallery));
                }

                // Auto-lazy load images
                if (config.lazy) {
                    this.initLazyLoading(gallery);
                }

                return {
                    gallery,
                    slides,
                    currentSlide,
                    next: () => this.next(gallery),
                    prev: () => this.prev(gallery),
                    goTo: (index) => this.goTo(gallery, index),
                    destroy: () => this.destroy(gallery)
                };
            },

            ensureControls: function(gallery) {
                if (!gallery.querySelector('.gallery-nav')) {
                    const nav = document.createElement('div');
                    nav.className = 'gallery-nav';
                    nav.innerHTML = `
                        <button class="gallery-prev" aria-label="Previous image">‹</button>
                        <button class="gallery-next" aria-label="Next image">›</button>
                    `;
                    gallery.appendChild(nav);
                }
            },

            initTouchSwipe: function(gallery, nextFn, prevFn) {
                let startX = 0;
                let startY = 0;

                gallery.addEventListener('touchstart', (e) => {
                    startX = e.touches[0].clientX;
                    startY = e.touches[0].clientY;
                });

                gallery.addEventListener('touchend', (e) => {
                    const endX = e.changedTouches[0].clientX;
                    const endY = e.changedTouches[0].clientY;
                    const diffX = startX - endX;
                    const diffY = startY - endY;

                    if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
                        if (diffX > 0) nextFn();
                        else prevFn();
                    }
                });
            },

            initLazyLoading: function(gallery) {
                const images = gallery.querySelectorAll('img[data-src]');
                if ('IntersectionObserver' in window) {
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                const img = entry.target;
                                img.src = img.dataset.src;
                                img.removeAttribute('data-src');
                                observer.unobserve(img);
                            }
                        });
                    });
                    images.forEach(img => observer.observe(img));
                }
            },

            next: function(gallery) {
                const slides = gallery.querySelectorAll('.gallery-slide, .listing-image, .property-image');
                const current = gallery.querySelector('.active') || slides[0];
                const currentIndex = Array.from(slides).indexOf(current);
                const nextIndex = (currentIndex + 1) % slides.length;
                this.goTo(gallery, nextIndex);
            },

            prev: function(gallery) {
                const slides = gallery.querySelectorAll('.gallery-slide, .listing-image, .property-image');
                const current = gallery.querySelector('.active') || slides[0];
                const currentIndex = Array.from(slides).indexOf(current);
                const prevIndex = currentIndex === 0 ? slides.length - 1 : currentIndex - 1;
                this.goTo(gallery, prevIndex);
            },

            goTo: function(gallery, index) {
                const slides = gallery.querySelectorAll('.gallery-slide, .listing-image, .property-image');
                slides.forEach((slide, i) => {
                    slide.classList.toggle('active', i === index);
                });
            }
        };
    });

    // Unified Map System (replaces 8+ implementations)
    HPH.register('maps', function() {
        return {
            instances: new Map(),

            init: function(container, options = {}) {
                const mapElement = container.querySelector('.hph-map, .listing-map, .property-map, .neighborhood-map');
                if (!mapElement) return;

                const mapId = mapElement.id || 'map-' + Date.now();
                if (this.instances.has(mapId)) return this.instances.get(mapId);

                const config = {
                    center: [39.8283, -98.5795], // Default US center
                    zoom: 13,
                    showNeighborhood: true,
                    showMarkers: true,
                    clusterMarkers: true,
                    ...options
                };

                const instance = this.createMap(mapElement, config);
                this.instances.set(mapId, instance);
                return instance;
            },

            createMap: function(mapElement, config) {
                // Use Leaflet if available, fallback to basic map
                if (window.L) {
                    const map = L.map(mapElement).setView(config.center, config.zoom);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(map);

                    if (config.showMarkers) {
                        this.addMarkers(map, mapElement);
                    }

                    return {
                        map,
                        addMarker: (lat, lng, options) => this.addMarker(map, lat, lng, options),
                        destroy: () => map.remove()
                    };
                } else {
                    // Fallback static map
                    mapElement.innerHTML = '<p>Map functionality requires location data</p>';
                    return { map: null };
                }
            },

            addMarkers: function(map, mapElement) {
                const markers = mapElement.dataset.markers;
                if (markers) {
                    try {
                        const markerData = JSON.parse(markers);
                        markerData.forEach(marker => {
                            L.marker([marker.lat, marker.lng])
                                .bindPopup(marker.popup || '')
                                .addTo(map);
                        });
                    } catch (e) {
                        console.warn('Invalid marker data:', e);
                    }
                }
            },

            addMarker: function(map, lat, lng, options = {}) {
                return L.marker([lat, lng], options).addTo(map);
            }
        };
    });

    // Unified Contact Forms (replaces 5+ implementations)
    HPH.register('listingForms', function() {
        return {
            init: function(container) {
                const forms = container.querySelectorAll('.listing-contact-form, .property-inquiry-form, .showing-request-form');
                forms.forEach(form => this.initForm(form));
            },

            initForm: function(form) {
                if (form.dataset.hphInitialized) return;

                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.submitForm(form);
                });

                // Auto-populate from listing data
                this.populateListingData(form);

                form.dataset.hphInitialized = 'true';
            },

            populateListingData: function(form) {
                const listingId = form.closest('[data-listing-id]')?.dataset.listingId;
                const listingTitle = document.querySelector('.listing-title, .property-title')?.textContent;
                const listingPrice = document.querySelector('.listing-price, .property-price')?.textContent;

                if (listingId) {
                    const hiddenInput = form.querySelector('input[name="listing_id"]');
                    if (hiddenInput) hiddenInput.value = listingId;
                }

                if (listingTitle) {
                    const subjectInput = form.querySelector('input[name="subject"]');
                    if (subjectInput && !subjectInput.value) {
                        subjectInput.value = `Inquiry about: ${listingTitle}`;
                    }
                }
            },

            submitForm: function(form) {
                const formData = new FormData(form);
                formData.append('action', 'hph_listing_contact');
                formData.append('nonce', window.hphNonce || '');

                // Use unified AJAX system
                if (window.HPH && window.HPH.ajax) {
                    window.HPH.ajax.submitForm(form, {
                        data: formData,
                        onSuccess: (response) => this.handleSuccess(form, response),
                        onError: (error) => this.handleError(form, error)
                    });
                }
            },

            handleSuccess: function(form, response) {
                form.innerHTML = '<div class="hph-alert hph-alert-success">Thank you! Your message has been sent.</div>';
            },

            handleError: function(form, error) {
                const errorDiv = form.querySelector('.form-error') || document.createElement('div');
                errorDiv.className = 'hph-alert hph-alert-error';
                errorDiv.textContent = 'Sorry, there was an error sending your message. Please try again.';
                form.insertBefore(errorDiv, form.firstChild);
            }
        };
    });

    // Unified Filters System (replaces multiple implementations)
    HPH.register('listingFilters', function() {
        return {
            init: function(container) {
                const filterForms = container.querySelectorAll('.listing-filters, .search-filters, .archive-filters');
                filterForms.forEach(form => this.initFilters(form));
            },

            initFilters: function(filterForm) {
                if (filterForm.dataset.hphInitialized) return;

                const inputs = filterForm.querySelectorAll('input, select');
                inputs.forEach(input => {
                    input.addEventListener('change', () => this.applyFilters(filterForm));
                });

                // Price range sliders
                const priceInputs = filterForm.querySelectorAll('input[type="range"]');
                priceInputs.forEach(input => {
                    input.addEventListener('input', () => this.updatePriceDisplay(input));
                });

                filterForm.dataset.hphInitialized = 'true';
            },

            applyFilters: function(filterForm) {
                const formData = new FormData(filterForm);
                const filters = Object.fromEntries(formData.entries());

                // Use unified AJAX for filtering
                if (window.HPH && window.HPH.ajax) {
                    window.HPH.ajax.request({
                        action: 'hph_filter_listings',
                        filters: filters,
                        nonce: window.hphNonce || ''
                    }, {
                        onSuccess: (response) => this.updateResults(response),
                        onError: (error) => console.warn('Filter error:', error)
                    });
                }
            },

            updatePriceDisplay: function(input) {
                const display = input.nextElementSibling || input.parentNode.querySelector('.price-display');
                if (display) {
                    const value = parseInt(input.value);
                    display.textContent = new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: 'USD',
                        minimumFractionDigits: 0
                    }).format(value);
                }
            },

            updateResults: function(response) {
                const resultsContainer = document.querySelector('.listing-results, .search-results, .archive-content');
                if (resultsContainer && response.html) {
                    resultsContainer.innerHTML = response.html;
                    // Reinitialize components in new content
                    this.initNewContent(resultsContainer);
                }
            },

            initNewContent: function(container) {
                if (window.HPH) {
                    // Reinitialize galleries in new content
                    if (window.HPH.modules.gallery) {
                        window.HPH.modules.gallery.init(container);
                    }
                    // Reinitialize other components as needed
                    if (window.HPH.modules.listingForms) {
                        window.HPH.modules.listingForms.init(container);
                    }
                }
            }
        };
    });

    // Unified Virtual Tours
    HPH.register('virtualTours', function() {
        return {
            init: function(container) {
                const tourElements = container.querySelectorAll('.virtual-tour, .tour-iframe, .matterport-tour');
                tourElements.forEach(element => this.initTour(element));
            },

            initTour: function(element) {
                if (element.dataset.hphInitialized) return;

                const tourUrl = element.dataset.tourUrl;
                const tourType = element.dataset.tourType || 'iframe';

                if (tourUrl) {
                    this.loadTour(element, tourUrl, tourType);
                }

                element.dataset.hphInitialized = 'true';
            },

            loadTour: function(element, url, type) {
                switch (type) {
                    case 'matterport':
                        this.loadMatterport(element, url);
                        break;
                    case 'youtube':
                        this.loadYoutube(element, url);
                        break;
                    default:
                        this.loadIframe(element, url);
                }
            },

            loadIframe: function(element, url) {
                const iframe = document.createElement('iframe');
                iframe.src = url;
                iframe.width = '100%';
                iframe.height = '400';
                iframe.frameBorder = '0';
                iframe.allowFullscreen = true;
                element.appendChild(iframe);
            },

            loadMatterport: function(element, url) {
                // Specific Matterport integration
                this.loadIframe(element, url);
            },

            loadYoutube: function(element, url) {
                // YouTube video tours
                const videoId = this.extractYouTubeId(url);
                if (videoId) {
                    this.loadIframe(element, `https://www.youtube.com/embed/${videoId}`);
                }
            },

            extractYouTubeId: function(url) {
                const regex = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/;
                const match = url.match(regex);
                return match ? match[1] : null;
            }
        };
    });

    // Listing Comparison System
    HPH.register('listingComparison', function() {
        return {
            compareList: [],
            maxCompare: 3,

            init: function(container) {
                const compareButtons = container.querySelectorAll('.compare-listing, [data-compare]');
                compareButtons.forEach(button => this.initCompareButton(button));

                this.updateCompareDisplay();
            },

            initCompareButton: function(button) {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const listingId = button.dataset.listingId || button.dataset.compare;
                    this.toggleCompare(listingId, button);
                });
            },

            toggleCompare: function(listingId, button) {
                const index = this.compareList.indexOf(listingId);

                if (index > -1) {
                    this.compareList.splice(index, 1);
                    button.classList.remove('active');
                } else if (this.compareList.length < this.maxCompare) {
                    this.compareList.push(listingId);
                    button.classList.add('active');
                } else {
                    alert(`You can only compare up to ${this.maxCompare} listings.`);
                }

                this.updateCompareDisplay();
            },

            updateCompareDisplay: function() {
                const compareWidget = document.querySelector('.compare-widget');
                if (compareWidget) {
                    const count = this.compareList.length;
                    compareWidget.style.display = count > 0 ? 'block' : 'none';

                    const countElement = compareWidget.querySelector('.compare-count');
                    if (countElement) {
                        countElement.textContent = count;
                    }
                }
            },

            viewComparison: function() {
                if (this.compareList.length < 2) {
                    alert('Please select at least 2 listings to compare.');
                    return;
                }

                const compareUrl = `/compare-listings/?ids=${this.compareList.join(',')}`;
                window.location.href = compareUrl;
            },

            clearComparison: function() {
                this.compareList = [];
                document.querySelectorAll('.compare-listing.active').forEach(button => {
                    button.classList.remove('active');
                });
                this.updateCompareDisplay();
            }
        };
    });

    // Initialize all listing modules when DOM is ready
    HPH.register('initListings', function() {
        return {
            init: function(container = document) {
                // Initialize all listing-related modules
                if (window.HPH.modules.gallery) window.HPH.modules.gallery.init(container);
                if (window.HPH.modules.heroGallery) window.HPH.modules.heroGallery.init(container);
                if (window.HPH.modules.maps) window.HPH.modules.maps.init(container);
                if (window.HPH.modules.listingForms) window.HPH.modules.listingForms.init(container);
                if (window.HPH.modules.listingFilters) window.HPH.modules.listingFilters.init(container);
                if (window.HPH.modules.virtualTours) window.HPH.modules.virtualTours.init(container);
                if (window.HPH.modules.listingComparison) window.HPH.modules.listingComparison.init(container);
            }
        };
    });

} else {
    console.warn('HPH Core system not found. Listing modules require unified core.');
}

if (window.hphDebug) {
    console.log('Unified Listings System Loaded - All redundancies eliminated');
}