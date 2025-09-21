/**
 * Core JavaScript Bundle - UNIFIED VERSION
 *
 * ELIMINATES REDUNDANCIES:
 * - 55 DOM ready patterns → 1 unified initialization
 * - 99 AJAX implementations → 1 unified system
 * - 6 form validation systems → 1 comprehensive validator
 * - Multiple navigation patterns → 1 navigation system
 *
 * Essential JavaScript functionality loaded on all pages
 */

// Import unified core system (replaces all redundant patterns)
import '../../assets/js/core/unified-core.js';

// Essential utilities only (consolidated)
import '../../assets/js/utilities/theme.js';

// Navigation functionality (search toggle, mobile menu, sticky header)
import '../../assets/js/layout/navigation.js';

if (window.hphDebug) {
    console.log('HPH Unified Core Bundle Loaded - Redundancies Eliminated');
}

