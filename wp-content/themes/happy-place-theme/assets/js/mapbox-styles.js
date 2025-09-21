/**
 * Custom Mapbox Styles Configuration
 * 
 * Provides custom map styles and themes for the Happy Place real estate platform
 * Optimized for real estate listings and property showcase
 */

const HPH_MapboxStyles = {
    
    /**
     * Happy Place Custom Styles
     * These require a Mapbox account with style creation capabilities
     */
    custom: {
        // Light theme - clean and professional for day viewing
        light: 'mapbox://styles/mapbox/light-v11',
        
        // Dark theme - elegant for evening/luxury properties
        dark: 'mapbox://styles/mapbox/dark-v11',
        
        // Streets - enhanced streets view
        streets: 'mapbox://styles/mapbox/streets-v12',
        
        // Satellite - aerial view for property boundaries
        satellite: 'mapbox://styles/mapbox/satellite-streets-v12',
        
        // Navigation - optimized for directions and routing
        navigation: 'mapbox://styles/mapbox/navigation-day-v1'
    },
    
    /**
     * Real Estate Optimized Styles
     */
    realEstate: {
        // Professional light theme for listings
        professional: {
            style: 'mapbox://styles/mapbox/light-v11',
            config: {
                // Remove unnecessary POI clutter
                'poi-label': { visibility: 'none' },
                // Enhance road visibility
                'road-primary': { 'line-width': ['interpolate', ['linear'], ['zoom'], 5, 1, 18, 32] },
                // Subtle water color
                'water': { 'fill-color': '#a3d5f7' },
                // Clean building styling
                'building': { 'fill-color': '#f0f0f0', 'fill-opacity': 0.8 }
            }
        },
        
        // Luxury dark theme for high-end properties
        luxury: {
            style: 'mapbox://styles/mapbox/dark-v11',
            config: {
                // Gold accents for luxury feel
                'water': { 'fill-color': '#1a202c' },
                'building': { 'fill-color': '#2d3748', 'fill-opacity': 0.9 },
                'road-primary': { 'line-color': '#4a5568' }
            }
        },
        
        // Satellite hybrid for property boundaries
        aerial: {
            style: 'mapbox://styles/mapbox/satellite-streets-v12',
            config: {
                // Enhance text visibility on satellite
                'place-label': { 'text-color': '#ffffff', 'text-halo-color': '#000000', 'text-halo-width': 2 },
                'road-label': { 'text-color': '#ffffff', 'text-halo-color': '#000000', 'text-halo-width': 1 }
            }
        }
    },
    
    /**
     * Get style configuration
     */
    getStyle(styleName, theme = 'default') {
        if (this.custom[styleName]) {
            return this.custom[styleName];
        }
        
        if (this.realEstate[styleName]) {
            return this.realEstate[styleName];
        }
        
        // Fallback to streets
        return this.custom.streets;
    },
    
    /**
     * Get style for specific use case
     */
    getStyleForUseCase(useCase) {
        const styles = {
            'listing-detail': this.custom.light,
            'listing-archive': this.custom.streets,
            'luxury-property': this.realEstate.luxury.style,
            'aerial-view': this.realEstate.aerial.style,
            'neighborhood': this.custom.light,
            'dashboard': this.custom.streets,
            'mobile': this.custom.light // Simpler for mobile
        };
        
        return styles[useCase] || this.custom.streets;
    }
};

/**
 * Enhanced Map Themes with Custom Colors
 */
const HPH_MapThemes = {

    // Happy Place brand theme - Updated with correct brand colors
    happyPlace: {
        primary: '#50bae1',      // HPH Primary Blue
        primaryDark: '#02333A',  // HPH Primary Dark
        primaryLight: '#e0f2fe', // HPH Primary Light
        secondary: '#e8a87c',    // HPH Secondary Orange/Peach
        accent: '#8bc34a',       // HPH Accent Green
        success: '#8bc34a',      // Use accent green for success
        warning: '#e8a87c',      // Use secondary for warnings
        danger: '#ef4444',       // Red for errors
        dark: '#02333A',         // Use primary dark
        light: '#eef8fc',        // HPH Primary 25 (lightest)
        white: '#f8fcff',        // HPH White
        gray: {
            50: '#e5f4fa',
            100: '#d1ebf5',
            200: '#b8e0ef',
            300: '#9ed2e7',
            400: '#7bc0dc',
            500: '#5faed1',
            600: '#4790b3',
            700: '#326d89',
            800: '#1e4355',
            900: '#0d2834'
        }
    },
    
    // Luxury real estate theme - HPH variant
    luxury: {
        primary: '#02333A',      // HPH Primary Dark (elegant)
        secondary: '#e8a87c',    // HPH Secondary (warm accent)
        accent: '#d4af37',       // Gold accent for luxury touches
        success: '#8bc34a',      // HPH Accent Green
        warning: '#e8a87c',      // HPH Secondary
        danger: '#ef4444',       // Red
        dark: '#0d2834',         // HPH Gray 900
        light: '#f8fcff',        // HPH White
        overlay: 'rgba(2, 51, 58, 0.9)' // Dark overlay for luxury feel
    },

    // Professional clean theme
    professional: {
        primary: '#4790b3',      // HPH Gray 600 (muted blue)
        secondary: '#326d89',    // HPH Gray 700
        accent: '#50bae1',       // HPH Primary (highlights only)
        success: '#8bc34a',      // HPH Accent Green
        warning: '#e8a87c',      // HPH Secondary
        danger: '#ef4444',       // Red
        dark: '#1e4355',         // HPH Gray 800
        light: '#eef8fc',        // HPH Light
        neutral: '#9ed2e7'       // HPH Gray 300
    },

    // Modern minimalist theme - HPH variant
    minimal: {
        primary: '#0d2834',      // HPH Gray 900 (near black)
        secondary: '#326d89',    // HPH Gray 700
        accent: '#50bae1',       // HPH Primary (minimal accents)
        success: '#8bc34a',      // HPH Accent Green
        warning: '#e8a87c',      // HPH Secondary
        danger: '#ef4444',       // Red
        dark: '#02333A',         // HPH Primary Dark
        light: '#f8fcff'         // HPH White
    }
};

/**
 * Custom Map Configuration Builder
 */
class HPH_MapStyleBuilder {
    constructor(baseStyle = 'streets', theme = 'happyPlace') {
        this.baseStyle = HPH_MapboxStyles.getStyle(baseStyle);
        this.theme = HPH_MapThemes[theme] || HPH_MapThemes.happyPlace;
        this.customLayers = [];
    }
    
    /**
     * Add custom layer for property boundaries
     */
    addPropertyBoundaryLayer() {
        this.customLayers.push({
            id: 'property-boundaries',
            type: 'line',
            source: 'property-data',
            paint: {
                'line-color': this.theme.primary,
                'line-width': 2,
                'line-opacity': 0.8
            },
            filter: ['==', 'type', 'property']
        });
        return this;
    }
    
    /**
     * Add neighborhood highlight layer
     */
    addNeighborhoodLayer() {
        this.customLayers.push({
            id: 'neighborhood-highlight',
            type: 'fill',
            source: 'neighborhood-data',
            paint: {
                'fill-color': this.theme.primary,
                'fill-opacity': 0.1,
                'fill-outline-color': this.theme.primary
            }
        });
        return this;
    }
    
    /**
     * Build the final style configuration
     */
    build() {
        return {
            style: this.baseStyle,
            layers: this.customLayers,
            theme: this.theme
        };
    }
}

/**
 * Export for global use
 */
if (typeof window !== 'undefined') {
    window.HPH_MapboxStyles = HPH_MapboxStyles;
    window.HPH_MapThemes = HPH_MapThemes;
    window.HPH_MapStyleBuilder = HPH_MapStyleBuilder;
}

export { HPH_MapboxStyles, HPH_MapThemes, HPH_MapStyleBuilder };
