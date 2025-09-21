/**
 * Listings JavaScript Bundle - UNIFIED VERSION
 *
 * ELIMINATES REDUNDANCIES:
 * - 12 gallery initialization patterns → 1 unified gallery system
 * - 8 map initialization patterns → 1 unified map system
 * - 5 contact form systems → 1 unified form handler
 * - Multiple filter implementations → 1 comprehensive filter system
 *
 * Target Bundle Size: 180KB (galleries, maps, virtual tours)
 */

// Import unified listings system (replaces all redundant components)
import '../../assets/js/listings/unified-listings.js';

// Note: Hero gallery is currently inline due to SystemJS module resolution issues
// TODO: Re-enable when SystemJS base path is fixed
// import '../../assets/js/components/listing/hero-gallery.js';

// Essential calculators and utilities
import '../../assets/js/components/forms/mortgage-calculator.js';

if (window.hphDebug) {
    console.log('HPH Unified Listings Bundle Loaded - Gallery/Map/Filter Redundancies Eliminated');
}

