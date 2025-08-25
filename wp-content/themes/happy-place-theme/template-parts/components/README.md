# Components Directory

This directory contains feature-specific reusable components, organized by functionality to match our CSS framework structure.

## Overview

Components are organized to mirror the CSS framework (`assets/css/framework/03-components/`) making it easy to find corresponding styles and maintain consistency.

## Directory Structure

```
components/
├── agent/         # Agent-related components
├── analytics/     # Analytics and reporting components  
├── forms/         # Form components
├── listing/       # Property listing components
├── open-house/    # Open house components
├── transaction/   # Transaction components
└── ui/            # General UI components (accordions, carousels, etc.)
```

## Component Loading

### Method 1: Helper Function (Recommended)
```php
// Load a component with arguments
hph_component('stat-card', [
    'title' => 'Total Sales',
    'value' => 150,
    'color' => 'primary',
    'format' => 'number'
]);

// Get component output without echoing
$output = hph_component('listing-card', ['listing_id' => 123], false);
```

### Method 2: Component Loader Class
```php
// Load component using static method
HPH_Component_Loader::load_component('dashboard-chart', [
    'type' => 'line',
    'data' => [10, 25, 15, 40]
]);

// Magic method (converts underscores to hyphens)
HPH_Component_Loader::stat_card(['title' => 'Revenue', 'value' => '$25,000']);
```

### Method 3: Traditional get_template_part
```php
// Still works with new directory structure
get_template_part('template-parts/components/base/stat-card', '', [
    'title' => 'Active Listings',
    'value' => 42
]);
```

### Method 4: Shortcodes (Auto-registered)
```php
// All components are automatically available as shortcodes
[hph_stat_card title="Sales" value="150" color="success"]
[hph_listing_card listing_id="123"]
[hph_dashboard_chart type="pie" data="10,20,30"]
```

## Available Components

### Base Components
- **stat-card**: Statistics display with icons and formatting
- **data-table**: Responsive tables with search/sort
- **dashboard-form**: AJAX forms with validation
- **dashboard-layout**: Flexible layout containers
- **dashboard-widget**: Widget containers with controls
- **dashboard-chart**: Chart.js visualization wrapper
- **dashboard-map**: Mapbox mapping component

### Listing Components
- **listing-card**: Property card displays (various layouts)
- **listing-hero**: Property hero sections
- **listing-details**: Property specifications
- **listing-map**: Location mapping
- **listing-contact-form**: Lead generation forms

### Agent Components
- **agent-card**: Agent profile cards with statistics

### Open House Components
- **open-house-card**: Event cards with RSVP
- **open-house-widget**: Upcoming events widget

### Transaction Components
- **transaction-dashboard**: Deal pipeline overview
- **transaction-status**: Progress tracking

### Analytics Components
- **real-estate-charts**: Pre-configured real estate charts

### Form Components
- **advanced-search-form**: Property search forms

## Component Information

```php
// Get component info
$info = hph_component_info('stat-card');
// Returns: array with name, description, category, args

// Get all components
$all_components = hph_components();

// Get components by category
$base_components = hph_components('base');
$listing_components = hph_components('listing');

// Get components organized by category
$categorized = HPH_Component_Loader::get_components_by_category();
```

## Creating New Components

1. **Choose the appropriate directory** based on component purpose
2. **Create the PHP file** following naming conventions
3. **Update the component registry** in `class-hph-component-loader.php`
4. **Use consistent argument patterns** and documentation

### Example Component Structure
```php
<?php
/**
 * Component Name - Description
 *
 * @package HappyPlaceTheme
 */

// Default attributes
$component_args = wp_parse_args($args ?? [], [
    'title' => '',
    'description' => '',
    'class' => '',
    // ... other defaults
]);

// Component logic here
?>

<div class="hph-component-name <?php echo esc_attr($component_args['class']); ?>">
    <!-- Component HTML -->
</div>

<?php
/**
 * Usage Examples:
 * 
 * hph_component('component-name', [
 *     'title' => 'Example Title',
 *     'description' => 'Example description'
 * ]);
 */
?>
```

## Benefits

1. **Organization**: Components grouped by logical purpose
2. **Discoverability**: Easy to find relevant components
3. **Consistency**: Standardized loading patterns
4. **Flexibility**: Multiple loading methods available
5. **Documentation**: Built-in component registry with metadata
6. **Shortcodes**: Automatic shortcode generation
7. **IDE Support**: Better autocompletion and organization

## Migration Guide

### Old Way
```php
get_template_part('template-parts/components/stat-card', '', $args);
```

### New Way
```php
hph_component('stat-card', $args);
```

The old method still works, but paths have changed:
```php
// Update paths to include subdirectories
get_template_part('template-parts/components/base/stat-card', '', $args);
get_template_part('template-parts/components/listing/listing-card', '', $args);
```