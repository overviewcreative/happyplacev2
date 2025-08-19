/**
 * Marketing Suite JavaScript for Happy Place Plugin
 */

import '../scss/marketing-suite.scss';

class HappyPlaceMarketingSuite {
    constructor() {
        this.canvas = null;
        this.fabricCanvas = null;
        this.currentTemplate = null;
        this.currentListing = null;
        
        this.init();
    }
    
    async init() {
        // Dynamic import fabric.js only when needed
        if (document.querySelector('#marketing-canvas')) {
            try {
                const fabric = await import('fabric');
                this.fabric = fabric.fabric;
                this.setupCanvas();
            } catch (error) {
                console.error('Failed to load Fabric.js:', error);
                return;
            }
        }
        
        this.setupEventListeners();
        this.loadTemplates();
        
        console.log('Marketing Suite initialized');
    }
    
    setupEventListeners() {
        document.addEventListener('click', (e) => {
            // Template selection
            if (e.target.matches('.template-card')) {
                e.preventDefault();
                this.selectTemplate(e.target.dataset.template);
            }
            
            // Format selection
            if (e.target.matches('.format-option')) {
                e.preventDefault();
                this.selectFormat(e.target.dataset.format);
            }
            
            // Generate flyer
            if (e.target.matches('.btn-generate-flyer')) {
                e.preventDefault();
                this.generateFlyer();
            }
            
            // Download flyer
            if (e.target.matches('.btn-download-flyer')) {
                e.preventDefault();
                this.downloadFlyer(e.target.dataset.format);
            }
            
            // Bulk generate
            if (e.target.matches('.btn-bulk-generate')) {
                e.preventDefault();
                this.bulkGenerate();
            }
        });
        
        // Listing selection
        document.addEventListener('change', (e) => {
            if (e.target.matches('#listing-select')) {
                this.selectListing(e.target.value);
            }
        });
    }
    
    setupCanvas() {
        const canvasElement = document.getElementById('marketing-canvas');
        if (!canvasElement) return;
        
        this.fabricCanvas = new this.fabric.Canvas('marketing-canvas', {
            width: 1080,
            height: 1080,
            backgroundColor: '#ffffff'
        });
        
        // Enable canvas controls
        this.fabricCanvas.on('selection:created', () => {
            this.showObjectControls();
        });
        
        this.fabricCanvas.on('selection:cleared', () => {
            this.hideObjectControls();
        });
    }
    
    async loadTemplates() {
        try {
            const response = await this.apiRequest('hp_get_marketing_templates');
            
            if (response.success && response.data.templates) {
                this.renderTemplates(response.data.templates);
            }
        } catch (error) {
            console.error('Error loading templates:', error);
        }
    }
    
    renderTemplates(templates) {
        const container = document.querySelector('.templates-grid');
        if (!container) return;
        
        container.innerHTML = templates.map(template => `
            <div class="template-card" data-template="${template.id}">
                <img src="${template.thumbnail}" alt="${template.name}" class="template-thumbnail">
                <h3 class="template-name">${template.name}</h3>
                <p class="template-category">${template.category}</p>
            </div>
        `).join('');
    }
    
    async selectTemplate(templateId) {
        try {
            this.showLoading();
            
            const response = await this.apiRequest('hp_get_marketing_template', {
                template_id: templateId
            });
            
            if (response.success && response.data.template) {
                this.currentTemplate = response.data.template;
                this.loadTemplateToCanvas();
                this.showFormatOptions();
            }
            
        } catch (error) {
            console.error('Error loading template:', error);
            this.showNotification('Error loading template', 'error');
        } finally {
            this.hideLoading();
        }
    }
    
    async loadTemplateToCanvas() {
        if (!this.fabricCanvas || !this.currentTemplate) return;
        
        this.fabricCanvas.clear();
        
        // Load template elements
        for (const element of this.currentTemplate.elements) {
            await this.addElementToCanvas(element);
        }
        
        this.fabricCanvas.renderAll();
    }
    
    async addElementToCanvas(element) {
        switch (element.type) {
            case 'image':
                await this.addImageElement(element);
                break;
            case 'text':
                this.addTextElement(element);
                break;
            case 'shape':
                this.addShapeElement(element);
                break;
        }
    }
    
    async addImageElement(element) {
        return new Promise((resolve) => {
            this.fabric.Image.fromURL(element.src, (img) => {
                img.set({
                    left: element.position.x,
                    top: element.position.y,
                    scaleX: element.scale?.x || 1,
                    scaleY: element.scale?.y || 1,
                    selectable: element.editable !== false
                });
                
                this.fabricCanvas.add(img);
                resolve();
            });
        });
    }
    
    addTextElement(element) {
        const text = new this.fabric.Text(element.text, {
            left: element.position.x,
            top: element.position.y,
            fontFamily: element.style.fontFamily || 'Arial',
            fontSize: element.style.fontSize || 24,
            fill: element.style.color || '#000000',
            fontWeight: element.style.fontWeight || 'normal',
            selectable: element.editable !== false
        });
        
        this.fabricCanvas.add(text);
    }
    
    addShapeElement(element) {
        let shape;
        
        switch (element.shape) {
            case 'rectangle':
                shape = new this.fabric.Rect({
                    left: element.position.x,
                    top: element.position.y,
                    width: element.properties.width,
                    height: element.properties.height,
                    fill: element.properties.fill || '#000000'
                });
                break;
            case 'circle':
                shape = new this.fabric.Circle({
                    left: element.position.x,
                    top: element.position.y,
                    radius: element.properties.radius,
                    fill: element.properties.fill || '#000000'
                });
                break;
        }
        
        if (shape) {
            shape.set({ selectable: element.editable !== false });
            this.fabricCanvas.add(shape);
        }
    }
    
    selectListing(listingId) {
        if (!listingId) return;
        
        this.currentListing = listingId;
        this.updateTemplateWithListingData();
    }
    
    async updateTemplateWithListingData() {
        if (!this.currentListing || !this.fabricCanvas) return;
        
        try {
            const response = await this.apiRequest('hp_get_listing_data', {
                listing_id: this.currentListing
            });
            
            if (response.success && response.data.listing) {
                this.populateTemplateFields(response.data.listing);
            }
            
        } catch (error) {
            console.error('Error loading listing data:', error);
        }
    }
    
    populateTemplateFields(listing) {
        // Update text elements with listing data
        const objects = this.fabricCanvas.getObjects('text');
        
        objects.forEach(textObj => {
            let text = textObj.text;
            
            // Replace placeholders
            text = text.replace('{{price}}', this.formatPrice(listing.price));
            text = text.replace('{{address}}', listing.address);
            text = text.replace('{{beds}}', listing.bedrooms);
            text = text.replace('{{baths}}', listing.bathrooms);
            text = text.replace('{{sqft}}', this.formatNumber(listing.square_feet));
            text = text.replace('{{agent_name}}', listing.agent_name);
            text = text.replace('{{agent_phone}}', listing.agent_phone);
            
            textObj.set('text', text);
        });
        
        // Update images if listing has photos
        if (listing.featured_image) {
            const imageObjects = this.fabricCanvas.getObjects('image');
            const heroImage = imageObjects.find(img => img.id === 'hero');
            
            if (heroImage) {
                this.fabric.Image.fromURL(listing.featured_image, (img) => {
                    img.set({
                        left: heroImage.left,
                        top: heroImage.top,
                        scaleX: heroImage.scaleX,
                        scaleY: heroImage.scaleY
                    });
                    
                    this.fabricCanvas.remove(heroImage);
                    this.fabricCanvas.add(img);
                    this.fabricCanvas.renderAll();
                });
            }
        }
        
        this.fabricCanvas.renderAll();
    }
    
    selectFormat(format) {
        const formatConfigs = {
            'instagram_post': { width: 1080, height: 1080 },
            'instagram_story': { width: 1080, height: 1920 },
            'facebook_post': { width: 1200, height: 630 },
            'twitter_post': { width: 1024, height: 512 },
            'full_flyer': { width: 2550, height: 3300 }
        };
        
        const config = formatConfigs[format];
        if (config && this.fabricCanvas) {
            this.fabricCanvas.setDimensions({
                width: config.width,
                height: config.height
            });
            
            this.fabricCanvas.renderAll();
        }
    }
    
    async generateFlyer() {
        if (!this.fabricCanvas || !this.currentListing) {
            this.showNotification('Please select a listing and template', 'error');
            return;
        }
        
        try {
            this.showLoading();
            
            // Get canvas data
            const canvasData = this.fabricCanvas.toDataURL({
                format: 'png',
                quality: 1.0
            });
            
            const response = await this.apiRequest('hp_save_generated_flyer', {
                listing_id: this.currentListing,
                template_id: this.currentTemplate.id,
                canvas_data: canvasData
            });
            
            if (response.success) {
                this.showNotification('Flyer generated successfully', 'success');
                this.showDownloadOptions(response.data.flyer_id);
            }
            
        } catch (error) {
            console.error('Error generating flyer:', error);
            this.showNotification('Error generating flyer', 'error');
        } finally {
            this.hideLoading();
        }
    }
    
    showDownloadOptions(flyerId) {
        const container = document.querySelector('.download-options');
        if (!container) return;
        
        const formats = ['PNG', 'JPG', 'PDF'];
        
        container.innerHTML = `
            <h3>Download Options</h3>
            <div class="format-buttons">
                ${formats.map(format => `
                    <button class="btn btn-download-flyer" data-format="${format.toLowerCase()}" data-flyer="${flyerId}">
                        Download ${format}
                    </button>
                `).join('')}
            </div>
        `;
        
        container.style.display = 'block';
    }
    
    async downloadFlyer(format) {
        if (!this.fabricCanvas) return;
        
        try {
            let dataURL;
            
            switch (format) {
                case 'png':
                    dataURL = this.fabricCanvas.toDataURL({
                        format: 'png',
                        quality: 1.0
                    });
                    break;
                case 'jpg':
                    dataURL = this.fabricCanvas.toDataURL({
                        format: 'jpeg',
                        quality: 0.9
                    });
                    break;
                case 'pdf':
                    // For PDF, we'd need to send to server for processing
                    await this.downloadPDF();
                    return;
            }
            
            // Create download link
            const link = document.createElement('a');
            link.download = `flyer-${Date.now()}.${format}`;
            link.href = dataURL;
            link.click();
            
        } catch (error) {
            console.error('Download error:', error);
            this.showNotification('Error downloading flyer', 'error');
        }
    }
    
    async downloadPDF() {
        try {
            const canvasData = this.fabricCanvas.toDataURL({
                format: 'png',
                quality: 1.0
            });
            
            const response = await this.apiRequest('hp_generate_pdf_flyer', {
                canvas_data: canvasData
            });
            
            if (response.success && response.data.pdf_url) {
                const link = document.createElement('a');
                link.href = response.data.pdf_url;
                link.download = `flyer-${Date.now()}.pdf`;
                link.click();
            }
            
        } catch (error) {
            console.error('PDF generation error:', error);
            this.showNotification('Error generating PDF', 'error');
        }
    }
    
    async bulkGenerate() {
        const selectedListings = this.getSelectedListings();
        const selectedFormats = this.getSelectedFormats();
        
        if (selectedListings.length === 0 || selectedFormats.length === 0) {
            this.showNotification('Please select listings and formats', 'error');
            return;
        }
        
        try {
            this.showLoading();
            
            const response = await this.apiRequest('hp_bulk_generate_flyers', {
                listings: selectedListings,
                formats: selectedFormats,
                template_id: this.currentTemplate.id
            });
            
            if (response.success && response.data.zip_url) {
                // Automatically download the ZIP file
                const link = document.createElement('a');
                link.href = response.data.zip_url;
                link.download = `marketing-materials-${Date.now()}.zip`;
                link.click();
                
                this.showNotification('Bulk generation completed', 'success');
            }
            
        } catch (error) {
            console.error('Bulk generation error:', error);
            this.showNotification('Error generating materials', 'error');
        } finally {
            this.hideLoading();
        }
    }
    
    getSelectedListings() {
        const checkboxes = document.querySelectorAll('.listing-checkbox:checked');
        return Array.from(checkboxes).map(cb => cb.value);
    }
    
    getSelectedFormats() {
        const checkboxes = document.querySelectorAll('.format-checkbox:checked');
        return Array.from(checkboxes).map(cb => cb.value);
    }
    
    showFormatOptions() {
        const container = document.querySelector('.format-options');
        if (container) {
            container.style.display = 'block';
        }
    }
    
    showObjectControls() {
        const controls = document.querySelector('.object-controls');
        if (controls) {
            controls.style.display = 'block';
        }
    }
    
    hideObjectControls() {
        const controls = document.querySelector('.object-controls');
        if (controls) {
            controls.style.display = 'none';
        }
    }
    
    formatPrice(price) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 0
        }).format(price);
    }
    
    formatNumber(number) {
        return new Intl.NumberFormat('en-US').format(number);
    }
    
    async apiRequest(action, data = {}) {
        const formData = new FormData();
        
        Object.keys(data).forEach(key => {
            formData.append(key, data[key]);
        });
        
        formData.append('action', action);
        formData.append('nonce', window.hp_dashboard?.nonce || '');
        
        const response = await fetch(window.hp_dashboard?.ajax_url || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: formData
        });
        
        return await response.json();
    }
    
    showLoading() {
        const loader = document.querySelector('.marketing-loader');
        if (loader) {
            loader.style.display = 'block';
        }
    }
    
    hideLoading() {
        const loader = document.querySelector('.marketing-loader');
        if (loader) {
            loader.style.display = 'none';
        }
    }
    
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `marketing-notification marketing-notification--${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new HappyPlaceMarketingSuite();
    });
} else {
    new HappyPlaceMarketingSuite();
}