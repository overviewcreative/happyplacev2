# HPH Framework File Structure Guide

## Current Framework Files Created

Below is the complete file structure showing where each CSS file should be placed within your WordPress theme directory:

```
/wp-content/themes/happy-place/
├── assets/
│   ├── css/
│   │   ├── hph-framework.css (main import file - CREATED)
│   │   ├── framework/
│   │   │   ├── base/
│   │   │   │   ├── variables.css (CREATED)
│   │   │   │   ├── reset.css (needs creation)
│   │   │   │   └── typography.css (needs creation)
│   │   │   ├── utilities/
│   │   │   │   ├── layout.css (exists in main file - needs extraction)
│   │   │   │   ├── spacing.css (exists in main file - needs extraction)
│   │   │   │   ├── colors.css (exists in main file - needs extraction)
│   │   │   │   ├── effects.css (exists in main file - needs extraction)
│   │   │   │   └── animations.css (needs creation)
│   │   │   ├── components/
│   │   │   │   ├── buttons.css (exists in main file - needs extraction)
│   │   │   │   ├── cards.css (exists in main file - needs extraction)
│   │   │   │   ├── forms.css (exists in main file - needs extraction)
│   │   │   │   ├── modals.css (exists in main file - needs extraction)
│   │   │   │   ├── badges.css (exists in main file - needs extraction)
│   │   │   │   ├── alerts.css (exists in main file - needs extraction)
│   │   │   │   ├── navigation.css (exists in main file - needs extraction)
│   │   │   │   ├── tables.css (exists in main file - needs extraction)
│   │   │   │   └── media.css (exists in main file - needs extraction)
│   │   │   ├── real-estate/
│   │   │   │   ├── listing-card.css (CREATED)
│   │   │   │   ├── agent-card.css (CREATED)
│   │   │   │   ├── property-gallery.css (CREATED)
│   │   │   │   ├── map-components.css (needs creation)
│   │   │   │   ├── price-displays.css (needs creation)
│   │   │   │   ├── property-features.css (needs creation)
│   │   │   │   ├── virtual-tour.css (needs creation)
│   │   │   │   ├── open-house.css (needs creation)
│   │   │   │   └── mortgage-calculator.css (needs creation)
│   │   │   ├── layouts/
│   │   │   │   ├── hero-sections.css (CREATED)
│   │   │   │   ├── grid-layouts.css (needs creation)
│   │   │   │   ├── page-sections.css (needs creation)
│   │   │   │   ├── sidebars.css (needs creation)
│   │   │   │   └── footers.css (needs creation)
│   │   │   ├── interactive/
│   │   │   │   ├── carousels.css (needs creation)
│   │   │   │   ├── filters.css (needs creation)
│   │   │   │   ├── calendars.css (needs creation)
│   │   │   │   ├── search.css (needs creation)
│   │   │   │   └── tooltips.css (needs creation)
│   │   │   ├── states/
│   │   │   │   ├── loading.css (needs creation)
│   │   │   │   ├── empty.css (needs creation)
│   │   │   │   └── error.css (needs creation)
│   │   │   ├── wordpress/
│   │   │   │   ├── blocks.css (needs creation)
│   │   │   │   └── templates.css (needs creation)
│   │   │   ├── responsive/
│   │   │   │   ├── tablet.css (needs creation)
│   │   │   │   └── mobile.css (needs creation)
│   │   │   └── print/
│   │   │       └── print.css (needs creation)
│   │   └── hph-framework.min.css (minified version - to be generated)
│   └── js/
│       └── components/
│           ├── gallery.js (needs creation)
│           ├── carousel.js (needs creation)
│           ├── modal.js (needs creation)
│           └── search.js (needs creation)
└── functions.php (needs to enqueue the CSS file)
```

## Files Created So Far

### ✅ Completed Files:
1. **hph-framework-todo.md** - Development checklist
2. **hph-framework.css** - Main import file that brings everything together
3. **variables.css** - Enhanced CSS variables with real estate specific values
4. **listing-card.css** - Complete listing card component with variants
5. **agent-card.css** - Complete agent/team card component with variants
6. **property-gallery.css** - Gallery component with lightbox and multiple layouts
7. **hero-sections.css** - Multiple hero section variants

### 📋 Next Priority Files to Create:
Based on our todo list, the next files to create are:

1. **map-components.css** - Interactive map wrappers and styles
2. **price-displays.css** - Price formatting and display components
3. **property-features.css** - Feature grids and amenity displays
4. **virtual-tour.css** - Virtual tour embed containers
5. **open-house.css** - Open house event cards and calendars

## How to Implement

### Step 1: Create Directory Structure
```bash
cd /wp-content/themes/happy-place/assets/css/
mkdir -p framework/{base,utilities,components,real-estate,layouts,interactive,states,wordpress,responsive,print}
```

### Step 2: Place Created Files
Place each created CSS file in its corresponding directory as shown in the structure above.

### Step 3: Extract Existing Components
The original `hph-framework.css` file contains many components that need to be extracted into separate files. For example:
- Extract button styles to `components/buttons.css`
- Extract card styles to `components/cards.css`
- Extract form styles to `components/forms.css`
- etc.

### Step 4: Enqueue in WordPress
Add to your theme's `functions.php`:

```php
function hph_enqueue_framework_styles() {
    wp_enqueue_style(
        'hph-framework',
        get_template_directory_uri() . '/assets/css/hph-framework.css',
        array(),
        '2.0.0',
        'all'
    );
}
add_action('wp_enqueue_scripts', 'hph_enqueue_framework_styles');
```

### Step 5: Create Supporting JavaScript
Some components (gallery, modal, carousel) will need JavaScript for interactivity. These should be created in `/assets/js/components/`.

## Development Workflow

1. **Current Phase**: Creating real estate-specific components
2. **Next Phase**: Extract and reorganize existing utility classes
3. **Following Phase**: Create interactive components with JavaScript
4. **Final Phase**: Documentation and component playground

## Notes

- All files use pure CSS (no SCSS compilation needed for testing)
- Files follow a consistent naming convention using kebab-case
- Each component file is self-contained but relies on CSS variables from `variables.css`
- Responsive styles are included within each component file but can be extracted later
- The framework is designed to be modular - you can include only what you need

## File Locations Reference

Save this file as: `/wp-content/themes/happy-place/docs/framework-structure.md`