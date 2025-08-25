# Template Parts Structure

This directory structure mirrors our CSS framework organization for easy maintenance and development.

## Directory Structure

```
template-parts/
├── base/              # Core base components (cards, tables, forms, widgets)
├── layout/            # Layout templates (archive, single, pagination, navigation)
├── components/        # Feature-specific reusable components
│   ├── agent/         # Agent-related components
│   ├── analytics/     # Analytics and reporting components
│   ├── forms/         # Form components
│   ├── listing/       # Property listing components
│   ├── open-house/    # Open house components
│   ├── transaction/   # Transaction components
│   └── ui/            # General UI components (accordions, carousels, etc.)
├── sections/          # Page section templates (hero, content sections)
├── pages/             # Full page templates and page-specific components
│   └── dashboard/     # Dashboard-specific templates
└── utilities/         # Helper templates and utility components
```

## Naming Conventions

- All template files use kebab-case: `listing-card.php`
- CSS classes follow hph- prefix: `.hph-listing-card`
- Component directories match CSS file organization
- Each component should have corresponding CSS in framework structure

## CSS Framework Alignment

This structure directly mirrors:
- `assets/css/framework/01-base/` → `template-parts/base/`
- `assets/css/framework/02-layout/` → `template-parts/layout/`  
- `assets/css/framework/03-components/` → `template-parts/components/`
- `assets/css/framework/04-sections/` → `template-parts/sections/`
- `assets/css/framework/05-pages/` → `template-parts/pages/`
- `assets/css/framework/06-utilities/` → `template-parts/utilities/`

## Usage

Load components using the HPH Component Loader:
```php
// Load a base component
hph_component('card', $args);

// Load a specific feature component  
hph_component('listing-hero', $args);

// Load a layout template
hph_component('single-layout', $args);
```