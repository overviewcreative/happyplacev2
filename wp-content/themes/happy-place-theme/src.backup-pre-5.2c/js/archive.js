/**
 * Archive JavaScript Bundle - UNIFIED VERSION
 *
 * ELIMINATES REDUNDANCIES:
 * - 7 filter implementations → 1 unified filter system
 * - 5 pagination patterns → 1 unified pagination system
 * - 4 view switching systems → 1 unified view manager
 * - Multiple AJAX search implementations → 1 unified search system
 *
 * Target Bundle Size: 100KB (filtering, pagination, view switching)
 */

// Import unified archive system (replaces all redundant components)
import '../../assets/js/archive/unified-archive.js';

// Import universal components for archive pages
import '../../assets/js/components/universal-loop.js';
import '../../assets/js/components/universal-carousel.js';

// Import HPH Map component for map view functionality
import '../../assets/js/components/hph-map.js';

if (window.hphDebug) {
    console.log('HPH Unified Archive Bundle Loaded - Filter/Pagination/Search Redundancies Eliminated');
}

