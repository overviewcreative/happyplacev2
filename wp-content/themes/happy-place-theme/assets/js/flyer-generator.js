/**
 * Property Flyer Generator Service
 * Handles data population and PDF generation using Fabric.js
 */

class PropertyFlyerGenerator {
    constructor() {
        this.canvas = null;
        this.template = null;
        this.listingData = {};
        this.images = {};
        
        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.init());
        } else {
            this.init();
        }
    }

    /**
     * Initialize the flyer generator
     */
    init() {
        this.loadFabricJS(() => {
            this.setupCanvas();
            this.bindEvents();
        });
    }

    /**
     * Load Fabric.js library dynamically
     */
    loadFabricJS(callback) {
        if (typeof fabric !== 'undefined') {
            callback();
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js';
        script.onload = callback;
        script.onerror = () => {
        };
        document.head.appendChild(script);
    }

    /**
     * Setup Fabric.js canvas
     */
    setupCanvas() {
        // Create canvas element if it doesn't exist
        let canvasElement = document.getElementById('flyer-canvas');
        if (!canvasElement) {
            canvasElement = document.createElement('canvas');
            canvasElement.id = 'flyer-canvas';
            canvasElement.width = 816; // 8.5" at 96 DPI
            canvasElement.height = 1056; // 11" at 96 DPI
            canvasElement.style.display = 'none'; // Hidden during generation
            document.body.appendChild(canvasElement);
        }

        this.canvas = new fabric.Canvas('flyer-canvas', {
            width: 816,
            height: 1056,
            backgroundColor: '#ffffff'
        });

    }

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Listen for flyer generation requests
        document.addEventListener('generate-flyer', (event) => {
            this.generateFlyer(event.detail);
        });

        // Listen for preview requests
        document.addEventListener('preview-flyer', (event) => {
            this.previewFlyer(event.detail);
        });
    }

    /**
     * Extract listing data from the current page
     */
    extractListingData() {
        const data = {};

        // Try to get data from various sources
        // 1. From global JavaScript variables (if available from WordPress)
        if (typeof window.listingData !== 'undefined') {
            Object.assign(data, window.listingData);
        }
        
        // WordPress listing data (if available)
        if (typeof window.wpListingData !== 'undefined') {
            Object.assign(data, window.wpListingData);
        }

        // 2. From ACF fields exposed via WordPress (priority source)
        this.extractACFData(data);

        // 3. From meta tags (secondary source)
        this.extractMetaData(data);

        // 4. From DOM elements with data attributes (fallback)
        this.extractDOMData(data);

        // 5. Try to extract from common WordPress/theme patterns
        this.extractWordPressData(data);

        this.listingData = data;
        return data;
    }
    
    /**
     * Extract data from ACF fields (primary data source)
     */
    extractACFData(data) {
        // Try to get listing ID from WordPress
        const listingId = this.getListingId();
        
        // Check if ACF data is available globally (set by WordPress)
        if (typeof window.acf_data !== 'undefined') {
            const acfData = window.acf_data;
            
            // Map ACF field names to our standard field names
            const acfMapping = {
                // Address fields
                'street_number': 'street_number',
                'street_dir_prefix': 'street_dir_prefix', 
                'street_name': 'street_name',
                'street_type': 'street_type',
                'street_dir_suffix': 'street_dir_suffix',
                'unit_number': 'unit_number',
                'city': 'city',
                'state': 'state',
                'zip_code': 'zip_code',
                
                // Property details
                'listing_price': 'price',
                'bedrooms': 'beds',
                'bathrooms': 'baths', 
                'square_feet': 'sqft',
                'lot_size': 'lot_size',
                'property_description': 'description',
                
                // Media fields
                'primary_photo': 'primary_photo',
                'photo_gallery': 'photo_gallery',
                
                // Agent fields
                'agent_name': 'agent_name',
                'agent_phone': 'agent_phone',
                'agent_email': 'agent_email',
                'office_phone': 'office_phone',
                'office_email': 'office_email',
                'office_address': 'office_address'
            };
            
            Object.keys(acfMapping).forEach(acfField => {
                if (acfData[acfField] !== undefined) {
                    data[acfMapping[acfField]] = acfData[acfField];
                }
            });
        }
    }
    
    /**
     * Extract data from meta tags
     */
    extractMetaData(data) {
        const metaFields = [
            'property-address',
            'property-city',
            'property-state', 
            'property-zip',
            'property-price',
            'property-beds',
            'property-baths',
            'property-sqft',
            'property-lot-size',
            'agent-name',
            'agent-phone',
            'agent-email',
            'office-phone',
            'office-email',
            'office-address'
        ];

        metaFields.forEach(field => {
            const meta = document.querySelector(`meta[name="${field}"]`);
            if (meta && !data[field.replace(/-/g, '_')]) {
                data[field.replace(/-/g, '_')] = meta.getAttribute('content');
            }
        });
    }
    
    /**
     * Extract data from DOM elements
     */
    extractDOMData(data) {
        const elements = document.querySelectorAll('[data-field]');
        elements.forEach(element => {
            const fieldName = element.getAttribute('data-field');
            const value = element.textContent.trim();
            if (value && value !== 'N/A' && !data[fieldName]) {
                data[fieldName] = value;
            }
        });
    }
    
    /**
     * Get the current listing ID from WordPress
     */
    getListingId() {
        // Try various methods to get the listing ID
        if (typeof window.post_id !== 'undefined') {
            return window.post_id;
        }
        
        // Try to extract from body class
        const bodyClasses = document.body.className;
        const postIdMatch = bodyClasses.match(/postid-(\d+)/);
        if (postIdMatch) {
            return parseInt(postIdMatch[1]);
        }
        
        // Try to extract from URL
        const urlMatch = window.location.pathname.match(/\/listings\/[^\/]+\/[^\/]+\/[^\/]+\/[^\/]+\/(\d+)/);
        if (urlMatch) {
            return parseInt(urlMatch[1]);
        }
        
        return null;
    }

    /**
     * Extract data from WordPress/theme specific patterns
     */
    extractWordPressData(data) {
        // Common selectors for property data
        const selectors = {
            address: '.property-address, .listing-address, .property-title h1',
            price: '.property-price, .listing-price, .price',
            beds: '.beds, .bedrooms, [class*="bed"]',
            baths: '.baths, .bathrooms, [class*="bath"]',
            sqft: '.sqft, .square-feet, [class*="sqft"]',
            description: '.property-description, .listing-description, .property-content'
        };

        Object.keys(selectors).forEach(key => {
            if (!data[key]) {
                const element = document.querySelector(selectors[key]);
                if (element) {
                    data[key] = element.textContent.trim();
                }
            }
        });

        // Extract images from gallery or listing images
        const imageElements = document.querySelectorAll('.property-gallery img, .listing-images img, .property-photos img');
        const images = [];
        imageElements.forEach((img, index) => {
            if (index < 4) { // Limit to 4 images for our template
                images.push(img.src);
            }
        });
        if (images.length > 0) {
            data.images = images;
        }
    }

    /**
     * Populate the HTML template with listing data
     */
    populateTemplate(data = null) {
        if (!data) {
            data = this.extractListingData();
        }

        // Find template elements and populate with data
        const templateElements = document.querySelectorAll('[data-field]');
        
        templateElements.forEach(element => {
            const fieldName = element.getAttribute('data-field');
            let value = data[fieldName];

            if (value) {
                // Format specific fields
                switch (fieldName) {
                    case 'address':
                        value = this.formatStreetAddress(data);
                        break;
                    case 'price':
                        value = this.formatPrice(value);
                        break;
                    case 'city_state_zip':
                        value = this.formatLocation(data);
                        break;
                    case 'sqft':
                        value = this.formatNumber(value); // Just the number, no "sq ft"
                        break;
                    case 'lot_size':
                        value = this.formatLotSizeNumber(value); // Just the number for acres
                        break;
                }

                element.textContent = value;
            }
        });

        // Handle images with proper ACF field mapping
        this.populatePrimaryPhoto(data);
        this.populateGalleryPhotos(data);

        // Legacy fallback for simple images array
        if (data.images && data.images.length > 0 && !data.primary_photo && !data.photo_gallery) {
            this.populateImages(data.images);
        }

    }

    /**
     * Populate images in the template
     */
    populateImages(images) {
        const imageContainers = [
            '.main-photo',
            '.small-photo-1', 
            '.small-photo-2',
            '.small-photo-3'
        ];

        images.forEach((imageUrl, index) => {
            if (index < imageContainers.length) {
                const container = document.querySelector(imageContainers[index]);
                if (container) {
                    container.style.backgroundImage = `url(${imageUrl})`;
                    container.style.backgroundSize = 'cover';
                    container.style.backgroundPosition = 'center';
                    container.innerHTML = ''; // Remove placeholder text
                }
            }
        });
    }

    /**
     * Populate primary photo from ACF field
     */
    populatePrimaryPhoto(data) {
        const mainPhotoContainer = document.querySelector('.main-photo');
        if (!mainPhotoContainer) return;

        let primaryPhotoUrl = null;

        // Try to get primary photo from ACF field
        if (data.primary_photo) {
            if (typeof data.primary_photo === 'object' && data.primary_photo.url) {
                // ACF image array format
                primaryPhotoUrl = data.primary_photo.url;
            } else if (typeof data.primary_photo === 'string') {
                // Direct URL string
                primaryPhotoUrl = data.primary_photo;
            }
        }

        // Fallback to first gallery image
        if (!primaryPhotoUrl && data.photo_gallery && data.photo_gallery.length > 0) {
            const firstGalleryImage = data.photo_gallery[0];
            if (typeof firstGalleryImage === 'object' && firstGalleryImage.url) {
                primaryPhotoUrl = firstGalleryImage.url;
            } else if (typeof firstGalleryImage === 'string') {
                primaryPhotoUrl = firstGalleryImage;
            }
        }

        // Final fallback to images array
        if (!primaryPhotoUrl && data.images && data.images.length > 0) {
            primaryPhotoUrl = data.images[0];
        }

        if (primaryPhotoUrl) {
            mainPhotoContainer.style.backgroundImage = `url(${primaryPhotoUrl})`;
            mainPhotoContainer.style.backgroundSize = 'cover';
            mainPhotoContainer.style.backgroundPosition = 'center';
            mainPhotoContainer.innerHTML = ''; // Remove placeholder text
        }
    }

    /**
     * Populate gallery photos for small photo slots
     */
    populateGalleryPhotos(data) {
        const smallPhotoContainers = [
            '.small-photo-1', 
            '.small-photo-2',
            '.small-photo-3'
        ];

        let galleryImages = [];

        // Get gallery images from ACF field
        if (data.photo_gallery && Array.isArray(data.photo_gallery)) {
            galleryImages = data.photo_gallery.slice(1, 4); // Skip first (used as primary), take next 3
        } else if (data.images && Array.isArray(data.images)) {
            galleryImages = data.images.slice(1, 4); // Skip first, take next 3
        }

        smallPhotoContainers.forEach((selector, index) => {
            const container = document.querySelector(selector);
            if (!container) return;

            if (galleryImages[index]) {
                let imageUrl = galleryImages[index];
                
                // Handle ACF image array format
                if (typeof imageUrl === 'object' && imageUrl.url) {
                    imageUrl = imageUrl.url;
                }

                container.style.backgroundImage = `url(${imageUrl})`;
                container.style.backgroundSize = 'cover';
                container.style.backgroundPosition = 'center';
                container.innerHTML = ''; // Remove placeholder text
            }
        });
    }

    /**
     * Format price value
     */
    formatPrice(price) {
        if (!price) return 'Price Available';
        
        // Remove any non-numeric characters except decimal point
        const numericPrice = price.toString().replace(/[^0-9.]/g, '');
        const priceNumber = parseFloat(numericPrice);
        
        if (isNaN(priceNumber)) return price;
        
        return '$' + priceNumber.toLocaleString();
    }

    /**
     * Format location (city, state, zip)
     */
    formatLocation(data) {
        const parts = [];
        if (data.city) parts.push(data.city);
        if (data.state) parts.push(data.state);
        if (data.zip_code || data.zip) parts.push(data.zip_code || data.zip);
        
        return parts.join(', ') || data.city_state_zip || '';
    }

    /**
     * Format complete street address from ACF address components
     */
    formatStreetAddress(data) {
        const parts = [];
        
        // Street Number (required)
        if (data.street_number) parts.push(data.street_number);
        
        // Prefix (optional) - N, S, E, W, etc.
        if (data.street_dir_prefix) parts.push(data.street_dir_prefix);
        
        // Street Name (required)
        if (data.street_name) parts.push(data.street_name);
        
        // Street Type (St, Ave, Blvd, etc.)
        if (data.street_type) parts.push(data.street_type);
        
        // Suffix (optional) - N, S, E, W, etc.
        if (data.street_dir_suffix) parts.push(data.street_dir_suffix);
        
        // Unit Number (optional) - #2A, Apt 5, etc.
        if (data.unit_number) {
            // Add unit with proper formatting
            const unit = data.unit_number.startsWith('#') ? data.unit_number : `#${data.unit_number}`;
            parts.push(unit);
        }
        
        return parts.join(' ') || data.address || '';
    }

    /**
     * Format number with commas
     */
    formatNumber(num) {
        if (!num) return '';
        const number = parseFloat(num.toString().replace(/[^0-9.]/g, ''));
        return isNaN(number) ? num : number.toLocaleString();
    }

    /**
     * Format lot size
     */
    formatLotSize(size) {
        if (!size) return '';
        return size.toString().includes('sq ft') ? size : size + ' sq ft';
    }

    /**
     * Format lot size number only (for acres display)
     */
    formatLotSizeNumber(size) {
        if (!size) return '';
        // Extract just the number from "0.50 acres" or "21780 sq ft"
        const number = parseFloat(size.toString().replace(/[^0-9.]/g, ''));
        return isNaN(number) ? size : number.toString();
    }

    /**
     * Generate flyer as PDF
     */
    async generateFlyer(options = {}) {
        try {
            
            // Extract and populate data
            const data = this.extractListingData();
            this.populateTemplate(data);
            
            // Create Fabric.js representation of the template
            await this.createCanvasFromTemplate();
            
            // Generate PDF
            const pdfBlob = await this.generatePDF(options);
            
            // Handle the generated PDF
            this.handleGeneratedPDF(pdfBlob, options);
            
        } catch (error) {
            this.showError('Failed to generate flyer. Please try again.');
        }
    }

    /**
     * Create Fabric.js canvas from HTML template
     */
    async createCanvasFromTemplate() {
        // This will be implemented in the next phase
        // For now, we'll use the HTML template approach
    }

    /**
     * Generate PDF from canvas
     */
    async generatePDF(options = {}) {
        // For now, we'll use html2pdf or similar library
        // This will be replaced with Fabric.js canvas export
        
        if (typeof html2pdf === 'undefined') {
            await this.loadHTML2PDF();
        }
        
        const element = document.querySelector('.flyer-container');
        if (!element) {
            throw new Error('Flyer template not found');
        }
        
        const opt = {
            margin: 0,
            filename: `property-flyer-${Date.now()}.pdf`,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
        };
        
        return html2pdf().from(element).set(opt).outputPdf('blob');
    }

    /**
     * Load html2pdf library
     */
    loadHTML2PDF() {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    /**
     * Handle generated PDF
     */
    handleGeneratedPDF(pdfBlob, options = {}) {
        const url = URL.createObjectURL(pdfBlob);
        
        if (options.action === 'download') {
            const a = document.createElement('a');
            a.href = url;
            a.download = options.filename || `property-flyer-${Date.now()}.pdf`;
            a.click();
        } else if (options.action === 'print') {
            const printWindow = window.open(url);
            printWindow.addEventListener('load', () => {
                printWindow.print();
            });
        } else {
            // Default: open in new tab
            window.open(url);
        }
        
        // Clean up
        setTimeout(() => URL.revokeObjectURL(url), 10000);
    }

    /**
     * Preview flyer in modal or new window
     */
    previewFlyer(options = {}) {
        this.populateTemplate();
        
        // Open preview in new window
        const previewWindow = window.open('', '_blank', 'width=900,height=1200');
        const flyerHtml = document.querySelector('.flyer-container').outerHTML;
        
        previewWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Property Flyer Preview</title>
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                <style>
                    body { margin: 0; padding: 20px; background: #f5f5f5; font-family: 'Poppins', sans-serif; }
                    ${this.getTemplateCSS()}
                </style>
            </head>
            <body>
                ${flyerHtml}
            </body>
            </html>
        `);
    }

    /**
     * Get template CSS for preview
     */
    getTemplateCSS() {
        // Extract CSS from current page or return embedded styles
        const styleSheets = Array.from(document.styleSheets);
        let css = '';
        
        // This is a simplified version - in practice you'd extract the specific styles
        return `
            /* Flyer template styles would be included here */
            .flyer-container { width: 816px; height: 1056px; margin: 0 auto; background: white; }
            /* ... other styles ... */
        `;
    }

    /**
     * Show error message
     */
    showError(message) {
        
        // Create simple error notification
        const errorDiv = document.createElement('div');
        errorDiv.style.cssText = `
            position: fixed; top: 20px; right: 20px; z-index: 10000;
            background: #ff4444; color: white; padding: 15px; border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        `;
        errorDiv.textContent = message;
        document.body.appendChild(errorDiv);
        
        setTimeout(() => errorDiv.remove(), 5000);
    }
}

// Initialize the flyer generator
window.PropertyFlyerGenerator = PropertyFlyerGenerator;
new PropertyFlyerGenerator();

// Helper function to trigger flyer generation
window.generatePropertyFlyer = function(options = {}) {
    document.dispatchEvent(new CustomEvent('generate-flyer', { detail: options }));
};

// Helper function to preview flyer
window.previewPropertyFlyer = function(options = {}) {
    document.dispatchEvent(new CustomEvent('preview-flyer', { detail: options }));
};