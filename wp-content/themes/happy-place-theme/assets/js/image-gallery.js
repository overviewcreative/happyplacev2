/**
 * Image Gallery JavaScript
 * 
 * Handles property image galleries and lightbox functionality
 * Photo browsing, fullscreen viewing, and carousel controls
 *
 * @package HappyPlaceTheme
 */

(function($) {
    'use strict';
    
    // Image Gallery namespace
    HPH.ImageGallery = {
        
        /**
         * Initialize image galleries
         */
        init: function() {
            // Only initialize if there are galleries on the page
            if ($('.property-gallery, .gallery-grid, [data-lightbox]').length > 0) {
                this.initPropertyGalleries();
                this.initLightbox();
                this.initThumbnailCarousel();
                this.initVirtualTour();
            }
        },
        
        /**
         * Initialize property galleries
         */
        initPropertyGalleries: function() {
            $('.property-gallery').each(function() {
                var $gallery = $(this);
                HPH.ImageGallery.setupGallery($gallery);
            });
            
            $('.gallery-grid').each(function() {
                var $grid = $(this);
                HPH.ImageGallery.setupGridGallery($grid);
            });
        },
        
        /**
         * Setup individual gallery
         */
        setupGallery: function($gallery) {
            var $images = $gallery.find('img');
            var $thumbnails = $gallery.find('.gallery-thumbnails');
            
            // Add click handlers for gallery images
            $images.on('click', function(e) {
                e.preventDefault();
                var imageIndex = $images.index(this);
                HPH.ImageGallery.openLightbox($gallery, imageIndex);
            });
            
            // Thumbnail navigation
            if ($thumbnails.length) {
                $thumbnails.find('.thumbnail').on('click', function(e) {
                    e.preventDefault();
                    var index = $(this).data('index');
                    HPH.ImageGallery.showImage($gallery, index);
                });
            }
            
            // Keyboard navigation
            $gallery.on('keydown', function(e) {
                if (e.target === this || $(e.target).closest('.gallery-main').length) {
                    switch (e.key) {
                        case 'ArrowLeft':
                            e.preventDefault();
                            HPH.ImageGallery.previousImage($gallery);
                            break;
                        case 'ArrowRight':
                            e.preventDefault();
                            HPH.ImageGallery.nextImage($gallery);
                            break;
                    }
                }
            });
        },
        
        /**
         * Setup grid gallery
         */
        setupGridGallery: function($grid) {
            var $images = $grid.find('a[data-lightbox]');
            
            $images.on('click', function(e) {
                e.preventDefault();
                var imageIndex = $images.index(this);
                var images = [];
                
                $images.each(function() {
                    images.push({
                        src: $(this).attr('href'),
                        title: $(this).find('img').attr('alt') || ''
                    });
                });
                
                HPH.ImageGallery.openLightboxWithImages(images, imageIndex);
            });
        },
        
        /**
         * Initialize lightbox
         */
        initLightbox: function() {
            // Don't create lightbox immediately, it will be created on demand
            // Just set a flag that it's ready to be created
            this.lightboxReady = true;
        },
        
        /**
         * Create lightbox DOM element when needed
         */
        createLightbox: function() {
            // Create lightbox HTML if it doesn't exist
            if (!$('#hph-lightbox').length) {
                var lightboxHtml = `
                    <div id="hph-lightbox" class="hph-lightbox">
                        <div class="hph-lightbox-overlay"></div>
                        <div class="hph-lightbox-container">
                            <div class="hph-lightbox-content">
                                <div class="hph-lightbox-header">
                                    <div class="hph-lightbox-counter">
                                        <span class="current">1</span> of <span class="total">1</span>
                                    </div>
                                    <button type="button" class="hph-lightbox-close">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="hph-lightbox-body">
                                    <div class="hph-lightbox-image-container">
                                        <img class="hph-lightbox-image" src="" alt="">
                                        <div class="hph-lightbox-loading">
                                            <i class="fas fa-spinner fa-spin"></i>
                                        </div>
                                    </div>
                                    <button type="button" class="hph-lightbox-nav hph-lightbox-prev">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <button type="button" class="hph-lightbox-nav hph-lightbox-next">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                                <div class="hph-lightbox-footer">
                                    <div class="hph-lightbox-title"></div>
                                    <div class="hph-lightbox-actions">
                                        <button type="button" class="hph-btn hph-btn-sm hph-btn-outline download-image">
                                            <i class="fas fa-download"></i> Download
                                        </button>
                                        <button type="button" class="hph-btn hph-btn-sm hph-btn-outline share-image">
                                            <i class="fas fa-share"></i> Share
                                        </button>
                                        <button type="button" class="hph-btn hph-btn-sm hph-btn-outline fullscreen-toggle">
                                            <i class="fas fa-expand"></i> Fullscreen
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                $('body').append(lightboxHtml);
                
                // Bind lightbox events
                HPH.ImageGallery.bindLightboxEvents();
            }
        },
        
        /**
         * Bind lightbox events
         */
        bindLightboxEvents: function() {
            var $lightbox = $('#hph-lightbox');
            
            // Close lightbox
            $lightbox.find('.hph-lightbox-close, .hph-lightbox-overlay').on('click', function() {
                HPH.ImageGallery.closeLightbox();
            });
            
            // Navigation
            $lightbox.find('.hph-lightbox-prev').on('click', function() {
                HPH.ImageGallery.lightboxPrevious();
            });
            
            $lightbox.find('.hph-lightbox-next').on('click', function() {
                HPH.ImageGallery.lightboxNext();
            });
            
            // Keyboard navigation
            $(document).on('keydown', function(e) {
                if ($lightbox.hasClass('hph-lightbox-open')) {
                    switch (e.key) {
                        case 'Escape':
                            HPH.ImageGallery.closeLightbox();
                            break;
                        case 'ArrowLeft':
                            HPH.ImageGallery.lightboxPrevious();
                            break;
                        case 'ArrowRight':
                            HPH.ImageGallery.lightboxNext();
                            break;
                    }
                }
            });
            
            // Action buttons
            $lightbox.find('.download-image').on('click', function() {
                HPH.ImageGallery.downloadCurrentImage();
            });
            
            $lightbox.find('.share-image').on('click', function() {
                HPH.ImageGallery.shareCurrentImage();
            });
            
            $lightbox.find('.fullscreen-toggle').on('click', function() {
                HPH.ImageGallery.toggleFullscreen();
            });
            
            // Image load events
            $lightbox.find('.hph-lightbox-image').on('load', function() {
                $lightbox.find('.hph-lightbox-loading').hide();
                $(this).fadeIn();
            }).on('error', function() {
                $lightbox.find('.hph-lightbox-loading').hide();
                $(this).attr('src', 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0yMCAyOEMyNCA0IDI4IDggMjggMTJDMjggMTYgMjQgMjAgMjAgMjBDMTYgMjAgMTIgMTYgMTIgMTJDMTIgOCAxNiA0IDIwIDRaIiBmaWxsPSIjOUI5QjlCIi8+Cjwvc3ZnPgo=');
            });
        },
        
        /**
         * Open lightbox with gallery
         */
        openLightbox: function($gallery, startIndex = 0) {
            var images = [];
            
            $gallery.find('img').each(function() {
                var $img = $(this);
                var $link = $img.closest('a');
                var src = $link.length ? $link.attr('href') : $img.attr('src');
                
                images.push({
                    src: src,
                    title: $img.attr('alt') || ''
                });
            });
            
            HPH.ImageGallery.openLightboxWithImages(images, startIndex);
        },
        
        /**
         * Open lightbox with image array
         */
        openLightboxWithImages: function(images, startIndex = 0) {
            if (!images || images.length === 0) return;
            
            // Create lightbox if it doesn't exist
            this.createLightbox();
            
            var $lightbox = $('#hph-lightbox');
            
            // Store images data
            $lightbox.data('images', images);
            $lightbox.data('currentIndex', startIndex);
            
            // Update counter
            $lightbox.find('.total').text(images.length);
            
            // Show lightbox
            $lightbox.addClass('hph-lightbox-open');
            $('body').addClass('hph-lightbox-active');
            
            // Load current image
            HPH.ImageGallery.loadLightboxImage(startIndex);
            
            // Update navigation visibility
            HPH.ImageGallery.updateLightboxNavigation();
        },
        
        /**
         * Load lightbox image
         */
        loadLightboxImage: function(index) {
            var $lightbox = $('#hph-lightbox');
            var images = $lightbox.data('images');
            
            if (!images || index < 0 || index >= images.length) return;
            
            var image = images[index];
            var $img = $lightbox.find('.hph-lightbox-image');
            var $loading = $lightbox.find('.hph-lightbox-loading');
            var $title = $lightbox.find('.hph-lightbox-title');
            var $current = $lightbox.find('.current');
            
            // Show loading
            $loading.show();
            $img.hide();
            
            // Update counter
            $current.text(index + 1);
            
            // Update title
            $title.text(image.title);
            
            // Load image
            $img.attr('src', image.src);
            
            // Update current index
            $lightbox.data('currentIndex', index);
        },
        
        /**
         * Update lightbox navigation
         */
        updateLightboxNavigation: function() {
            var $lightbox = $('#hph-lightbox');
            var images = $lightbox.data('images');
            var currentIndex = $lightbox.data('currentIndex');
            
            var $prev = $lightbox.find('.hph-lightbox-prev');
            var $next = $lightbox.find('.hph-lightbox-next');
            
            // Hide navigation if only one image
            if (images.length <= 1) {
                $prev.hide();
                $next.hide();
                return;
            }
            
            // Show/hide based on position
            $prev.toggle(currentIndex > 0);
            $next.toggle(currentIndex < images.length - 1);
        },
        
        /**
         * Navigate to previous image
         */
        lightboxPrevious: function() {
            var $lightbox = $('#hph-lightbox');
            var currentIndex = $lightbox.data('currentIndex');
            
            if (currentIndex > 0) {
                HPH.ImageGallery.loadLightboxImage(currentIndex - 1);
                HPH.ImageGallery.updateLightboxNavigation();
            }
        },
        
        /**
         * Navigate to next image
         */
        lightboxNext: function() {
            var $lightbox = $('#hph-lightbox');
            var images = $lightbox.data('images');
            var currentIndex = $lightbox.data('currentIndex');
            
            if (currentIndex < images.length - 1) {
                HPH.ImageGallery.loadLightboxImage(currentIndex + 1);
                HPH.ImageGallery.updateLightboxNavigation();
            }
        },
        
        /**
         * Close lightbox
         */
        closeLightbox: function() {
            var $lightbox = $('#hph-lightbox');
            $lightbox.removeClass('hph-lightbox-open');
            $('body').removeClass('hph-lightbox-active');
        },
        
        /**
         * Download current image
         */
        downloadCurrentImage: function() {
            var $lightbox = $('#hph-lightbox');
            var images = $lightbox.data('images');
            var currentIndex = $lightbox.data('currentIndex');
            
            if (images && images[currentIndex]) {
                var image = images[currentIndex];
                var link = document.createElement('a');
                link.href = image.src;
                link.download = image.title || 'property-image.jpg';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        },
        
        /**
         * Share current image
         */
        shareCurrentImage: function() {
            var $lightbox = $('#hph-lightbox');
            var images = $lightbox.data('images');
            var currentIndex = $lightbox.data('currentIndex');
            
            if (images && images[currentIndex]) {
                var image = images[currentIndex];
                
                if (navigator.share) {
                    navigator.share({
                        title: image.title,
                        url: image.src
                    });
                } else {
                    HPH.copyToClipboard(image.src);
                    HPH.showAlert('Image URL copied to clipboard!', 'success');
                }
            }
        },
        
        /**
         * Toggle fullscreen
         */
        toggleFullscreen: function() {
            var $lightbox = $('#hph-lightbox');
            
            if (!document.fullscreenElement) {
                $lightbox[0].requestFullscreen().then(function() {
                    $lightbox.addClass('hph-lightbox-fullscreen');
                    $lightbox.find('.fullscreen-toggle i').removeClass('fa-expand').addClass('fa-compress');
                });
            } else {
                document.exitFullscreen().then(function() {
                    $lightbox.removeClass('hph-lightbox-fullscreen');
                    $lightbox.find('.fullscreen-toggle i').removeClass('fa-compress').addClass('fa-expand');
                });
            }
        },
        
        /**
         * Initialize thumbnail carousel
         */
        initThumbnailCarousel: function() {
            $('.gallery-thumbnails').each(function() {
                var $carousel = $(this);
                var $container = $carousel.find('.thumbnails-container');
                var $thumbnails = $container.find('.thumbnail');
                var $prevBtn = $carousel.find('.carousel-prev');
                var $nextBtn = $carousel.find('.carousel-next');
                
                if ($thumbnails.length <= 1) return;
                
                var thumbnailWidth = $thumbnails.first().outerWidth(true);
                var visibleCount = Math.floor($carousel.width() / thumbnailWidth);
                var currentPosition = 0;
                var maxPosition = Math.max(0, $thumbnails.length - visibleCount);
                
                function updateCarousel() {
                    var translateX = -currentPosition * thumbnailWidth;
                    $container.css('transform', 'translateX(' + translateX + 'px)');
                    
                    $prevBtn.prop('disabled', currentPosition === 0);
                    $nextBtn.prop('disabled', currentPosition >= maxPosition);
                }
                
                $prevBtn.on('click', function() {
                    if (currentPosition > 0) {
                        currentPosition--;
                        updateCarousel();
                    }
                });
                
                $nextBtn.on('click', function() {
                    if (currentPosition < maxPosition) {
                        currentPosition++;
                        updateCarousel();
                    }
                });
                
                // Initialize
                updateCarousel();
                
                // Update on window resize
                $(window).on('resize', HPH.debounce(function() {
                    visibleCount = Math.floor($carousel.width() / thumbnailWidth);
                    maxPosition = Math.max(0, $thumbnails.length - visibleCount);
                    currentPosition = Math.min(currentPosition, maxPosition);
                    updateCarousel();
                }, 250));
            });
        },
        
        /**
         * Initialize virtual tour
         */
        initVirtualTour: function() {
            $('.virtual-tour-btn').on('click', function(e) {
                e.preventDefault();
                var tourUrl = $(this).data('tour-url');
                
                if (tourUrl) {
                    HPH.ImageGallery.openVirtualTour(tourUrl);
                } else {
                    HPH.showAlert('Virtual tour not available', 'info');
                }
            });
        },
        
        /**
         * Open virtual tour
         */
        openVirtualTour: function(tourUrl) {
            var modalHtml = `
                <div id="virtual-tour-modal" class="hph-modal">
                    <div class="hph-modal-content hph-modal-fullscreen">
                        <div class="hph-modal-header">
                            <h3>Virtual Tour</h3>
                            <button type="button" class="hph-modal-close">&times;</button>
                        </div>
                        <div class="hph-modal-body">
                            <iframe src="${tourUrl}" frameborder="0" allowfullscreen></iframe>
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(modalHtml);
            HPH.openModal('virtual-tour-modal');
            
            // Clean up when modal is closed
            $(document).on('hph-modal-closed', function() {
                $('#virtual-tour-modal').remove();
            });
        }
    };
    
    // Initialize image gallery when DOM is ready
    $(document).ready(function() {
        HPH.ImageGallery.init();
    });
    
})(jQuery);
