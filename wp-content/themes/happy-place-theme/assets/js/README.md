# JavaScript Assets Structure

This directory structure mirrors the template and CSS framework organization for consistent development workflow.

## Directory Structure

```
assets/js/
├── base/              # Core JS framework (mirrors 01-base CSS & template/base)
│   └── framework-core.js  # Main HPH framework initialization
├── layout/            # Layout-related JS (mirrors 02-layout CSS & template/layout)  
│   ├── hph-header.js      # Header functionality
│   └── navigation.js      # Navigation components
├── components/        # Component-specific JS (mirrors 03-components CSS & template/components)
│   ├── agent/             # Agent component JS
│   ├── forms/             # Form component JS
│   │   ├── agent-lead-form.js     # Agent lead generation
│   │   ├── mortgage-calculator.js  # Mortgage calculations
│   │   └── search-filters.js       # Search and filter forms
│   ├── listing/           # Listing component JS
│   │   ├── listing-agent.js        # Listing agent components
│   │   ├── listing-card.js         # Interactive listing cards
│   │   ├── listing-components.js   # General listing functionality
│   │   ├── listing-details.js      # Property details
│   │   ├── listing-features.js     # Property features
│   │   ├── listing-gallery.js      # Photo galleries
│   │   ├── listing-map.js          # Property maps
│   │   └── property-map.js         # Advanced property mapping
│   └── ui/                # UI component JS
│       └── image-gallery.js       # General image gallery functionality
├── sections/          # Section-specific JS (mirrors 04-sections CSS & template/sections)
│   └── hero-listing.js    # Hero section functionality
├── pages/             # Page-specific JS (mirrors 05-pages CSS & template/pages)
│   ├── dashboard/         # Dashboard JS
│   │   ├── dashboard-leads.js      # Lead management
│   │   ├── dashboard-listings.js   # Listing management
│   │   ├── dashboard-main.js       # Main dashboard controller
│   │   ├── dashboard-open-houses.js # Open house management
│   │   ├── dashboard-router.js     # Dashboard routing
│   │   └── listing-form.js         # Listing form functionality
│   ├── archive-listing.js # Archive page functionality
│   ├── homepage.js        # Homepage-specific JS
│   └── single-listing.js  # Single listing page JS
└── utilities/         # Helper JS (mirrors 06-utilities CSS & template/utilities)
    └── admin/             # Admin utility JS
        └── admin-settings.js   # Admin panel functionality
```

## Framework Alignment

This structure directly corresponds to:

| JS Directory | Template Directory | CSS Directory | Purpose |
|-------------|-------------------|---------------|---------|
| `base/` | `template-parts/base/` | `01-base/` | Core framework components |
| `layout/` | `template-parts/layout/` | `02-layout/` | Layout and navigation JS |
| `components/` | `template-parts/components/` | `03-components/` | Component-specific functionality |
| `sections/` | `template-parts/sections/` | `04-sections/` | Section JS (hero, content) |
| `pages/` | `template-parts/pages/` | `05-pages/` | Page-specific JS |
| `utilities/` | `template-parts/utilities/` | `06-utilities/` | Helper utilities |

## Asset Loading

JavaScript files are automatically loaded by the HPH Assets service based on the new structure:

### Automatic Loading Order:
1. **Base JS**: `framework-core.js` (loaded first as dependency)
2. **Layout JS**: Navigation, headers, pagination
3. **Component JS**: Feature-specific functionality
4. **Section JS**: Hero sections, content sections
5. **Page JS**: Page-specific functionality
6. **Utilities**: Admin and helper functions

### Manual Loading:
```php
// Load specific JS files when needed
wp_enqueue_script('hph-listing-card', HPH_JS_URI . '/components/listing/listing-card.js', ['hph-framework-core'], HPH_VERSION, true);
```

## Component Development

### File Organization:
- **Component JS**: Place in appropriate component subdirectory
- **Page JS**: Place in `pages/` directory
- **Shared utilities**: Place in `utilities/`

### Naming Convention:
- Use kebab-case: `listing-card.js`
- Prefix handles with `hph-`: `hph-listing-card`
- Match template names: `listing-card.js` ↔ `listing-card.php`

### Code Structure:
```javascript
/**
 * Component Name JavaScript
 * Description of functionality
 * 
 * @package HappyPlaceTheme
 */

(function($) {
    'use strict';
    
    // Component initialization
    class ComponentName {
        constructor(element) {
            this.$element = $(element);
            this.init();
        }
        
        init() {
            this.bindEvents();
            // Component setup
        }
        
        bindEvents() {
            // Event handlers
        }
    }
    
    // Auto-initialize components
    $(document).ready(function() {
        $('.hph-component-selector').each(function() {
            new ComponentName(this);
        });
    });

})(jQuery);
```

## Benefits

1. **Intuitive Organization**: JS mirrors template and CSS structure
2. **Easy Maintenance**: Find related files quickly across all three systems
3. **Consistent Naming**: Same conventions across templates, CSS, and JS
4. **Automatic Loading**: Asset service handles loading based on structure
5. **Scalable**: Clear structure for adding new components

## Development Workflow

When creating new functionality:

1. **Create template** in appropriate `template-parts/` directory
2. **Create CSS** in matching `assets/css/framework/` directory
3. **Create JS** in matching `assets/js/` directory
4. **Use consistent naming** across all three files

Example:
- Template: `template-parts/components/ui/modal.php`
- CSS: `assets/css/framework/03-components/hph-modals.css`
- JS: `assets/js/components/ui/modal.js`

This ensures perfect alignment and makes the codebase much more maintainable!