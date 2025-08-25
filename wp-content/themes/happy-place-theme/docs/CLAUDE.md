# CLAUDE.md - Happy Place Theme

## Project Overview

The Happy Place Theme is a comprehensive WordPress real estate theme built with a component-based architecture. The theme provides a complete solution for real estate websites with listings, agent profiles, open houses, transactions, and advanced dashboard functionality.

## Complete Theme Architecture

### Perfect Framework Alignment

The entire theme structure has been organized with perfect alignment between templates, CSS, and JavaScript:

| Template Directory | CSS Framework Directory | JavaScript Directory | Purpose |
|-------------------|-------------------------|---------------------|---------|
| `template-parts/base/` | `assets/css/framework/01-base/` | `assets/js/base/` | Core reusable components |
| `template-parts/layout/` | `assets/css/framework/02-layout/` | `assets/js/layout/` | Page layouts and structure |
| `template-parts/components/` | `assets/css/framework/03-components/` | `assets/js/components/` | Feature-specific components |
| `template-parts/sections/` | `assets/css/framework/04-sections/` | `assets/js/sections/` | Page section templates |
| `template-parts/pages/` | `assets/css/framework/05-pages/` | `assets/js/pages/` | Page-specific templates |
| `template-parts/utilities/` | `assets/css/framework/06-utilities/` | `assets/js/utilities/` | Helper and utility templates |

### Directory Structure

```
happy-place-theme/
├── 📁 Root WordPress Templates (Required)
│   ├── style.css              # WordPress theme stylesheet
│   ├── index.php              # Fallback template
│   ├── functions.php          # Theme initialization
│   ├── archive-*.php          # Archive templates
│   ├── single-*.php           # Single post templates
│   ├── page-*.php             # Page templates
│   ├── header.php, footer.php # Site structure
│   └── 404.php, search.php    # Special templates
│
├── 📁 assets/                 # All theme assets
│   ├── css/
│   │   └── framework/         # Organized CSS framework
│   │       ├── 01-base/       # Variables, resets, typography
│   │       ├── 02-layout/     # Containers, grid, headers
│   │       ├── 03-components/ # UI components (buttons, forms, etc)
│   │       ├── 04-sections/   # Page sections (hero, content)
│   │       ├── 05-pages/      # Page-specific styles
│   │       └── 06-utilities/  # Helper classes
│   ├── js/                    # JavaScript (mirrors template structure)
│   │   ├── base/              # Core framework JS
│   │   ├── layout/            # Navigation, headers
│   │   ├── components/        # Component-specific JS
│   │   │   ├── listing/       # Property listing JS
│   │   │   ├── forms/         # Form functionality
│   │   │   └── ui/            # UI components
│   │   ├── pages/             # Page-specific JS
│   │   │   └── dashboard/     # Dashboard functionality
│   │   └── utilities/         # Helper functions
│   └── images/                # Theme images and media
│
├── 📁 includes/               # PHP functionality (consolidated)
│   ├── services/              # Core theme services
│   │   ├── class-hph-assets.php      # Asset loading system
│   │   ├── class-hph-dashboard.php   # Dashboard functionality
│   │   ├── class-hph-router.php      # URL routing
│   │   └── class-hph-*.php           # Other services
│   ├── bridge/                # Data bridge functions (see below)
│   ├── admin/                 # Admin panel functionality
│   ├── ajax/                  # AJAX handlers
│   └── integrations/          # Third-party integrations
│
├── 📁 template-parts/         # Component-based templates
│   ├── base/                  # Core reusable components
│   │   ├── card.php           # Universal card component
│   │   ├── card-grid.php      # Grid layout for cards
│   │   ├── card-list.php      # List layout for cards
│   │   ├── card-map.php       # Map layout with cards
│   │   └── data-table.php     # Responsive tables
│   ├── layout/                # Layout templates
│   │   ├── archive-layout.php # Main archive structure
│   │   ├── single-layout.php  # Single post structure
│   │   └── pagination.php     # Consistent pagination
│   ├── components/            # Feature-specific components
│   │   ├── listing/           # Property listing components
│   │   ├── agent/             # Agent-related components
│   │   ├── forms/             # Form components
│   │   └── ui/                # General UI components
│   ├── sections/              # Page sections
│   ├── pages/                 # Page-specific templates
│   │   └── dashboard/         # Dashboard templates
│   └── utilities/             # Helper templates
│
└── 📁 docs/                   # Documentation (organized)
    ├── STRUCTURE.md           # Complete theme structure
    ├── hph-documentation.md   # API documentation
    └── *.md                   # Other documentation files
```

## CSS & Template Standards - CRITICAL RULES

### 🚨 Utility-First Development Standards

**NEVER write custom CSS for spacing, layout, or common styling.** Use our extensive utility class system instead.

#### Template Development Rules

1. **Use Semantic Grid Classes** - NO 12-column Bootstrap grids
   ```php
   // ❌ BAD - Legacy 12-column grid
   <div class="hph-grid hph-grid-cols-12">
       <div class="hph-col-span-4">Photo</div>
       <div class="hph-col-span-8">Content</div>
   </div>
   
   // ✅ GOOD - Semantic grid
   <div class="hph-grid hph-grid-cols-1 md:hph-grid-cols-2 hph-gap-lg">
       <div>Photo</div>
       <div>Content</div>
   </div>
   ```

2. **Use Utility Classes for ALL Styling**
   ```php
   // ❌ BAD - Custom CSS in separate files
   .hph-agent-hero {
       padding: var(--hph-spacing-16) 0;
       background: var(--hph-color-gray-50);
   }
   
   // ✅ GOOD - Utilities in template
   <section class="hph-py-2xl hph-bg-gray-50">
   ```

3. **Standard Component Structure** (Follow content.php and hero.php patterns)
   - Always use `wp_parse_args()` for default arguments
   - **CRITICAL**: Build classes as arrays and `implode()` them - NEVER use string concatenation
   - Use semantic HTML5 elements
   - Progressive enhancement with responsive utilities
   - Extract configuration variables (follow hero.php pattern)
   - Use proper array merging for conditional classes

#### Available Utility Categories

- **Spacing**: `hph-p-*`, `hph-m-*`, `hph-gap-*` (xs, sm, md, lg, xl, 2xl, 3xl)
- **Layout**: `hph-flex`, `hph-grid`, `hph-grid-cols-*`, `hph-items-*`, `hph-justify-*`
- **Typography**: `hph-text-*`, `hph-font-*`, `hph-leading-*`, `hph-uppercase`, `hph-tracking-*`
- **Colors**: `hph-text-*`, `hph-bg-*` (primary-50/100/200/600/700, gray-*, success, danger, warning, info)
- **Dimensions**: `hph-w-*`, `hph-h-*`, `hph-max-w-*`, `hph-min-w-*` (0, px, xs, sm, md, lg, xl, 2xl-7xl, full, screen, fractions)
- **Display**: `d-flex`, `d-grid`, `d-none`, `hph-flex`, `hph-inline-flex`, visibility utilities
- **Animations**: `hph-animate-*` (fade-in, scale-in, slide-*, spin, pulse, bounce), `hph-transition-*`, `hph-hover:*`
- **Transforms**: `hph-scale-*`, `hph-rotate-*`, `hph-translate-*`, hover variants
- **Containers**: Various responsive container classes, content width controls, sticky positioning
- **Badges**: `hph-badge-*` variants (primary, success, danger, warning, info, status, notification, ribbon)
- **Interactive**: `hph-hover:*`, `hph-transition-*`, `hph-duration-*`, `hph-ease-*`, `hph-delay-*`
- **Position**: `hph-relative`, `hph-absolute`, `hph-fixed`, `hph-sticky`, `hph-inset-*`, `hph-z-*`
- **Borders**: `hph-rounded-*`, `hph-border-*`, `hph-ring-*`
- **Shadows**: `hph-shadow-*` (sm, md, lg, xl, none)
- **Opacity**: `hph-opacity-*`, `opacity-*`
- **Overflow**: `hph-overflow-*`, `hph-truncate`

#### CSS Files Should Be MINIMAL

Page-specific CSS files should contain:
- ONLY component-specific interactions (hover effects, animations)
- ONLY styles that cannot be achieved with utilities
- NO spacing, layout, typography, or color declarations

#### Gold Standards for Components

Reference these files for proper patterns:
- `/template-parts/sections/content.php` - Master content component
- `/template-parts/sections/hero.php` - Advanced component configuration
- `/template-parts/components/listing/listing-card.php` - Card component example
- `/template-parts/components/ui/stats-counter.php` - Grid component example

**Key Patterns**:
- Proper argument parsing and defaults with `wp_parse_args()`
- Class array building technique with `implode()`
- Responsive utility usage with breakpoint prefixes
- Component flexibility through configuration arrays
- Semantic HTML structure with ARIA attributes

## Plugin Data Bridge System

The theme integrates with the Happy Place Tools (HPT) plugin through a comprehensive bridge system that provides seamless data access:

### Bridge Architecture

```php
// Located in: includes/bridge/
├── agent-bridge.php           # Agent data bridging
├── listing-bridge.php         # Property listing data
├── community-bridge.php       # Community/neighborhood data
├── open-house-bridge.php      # Open house functionality
├── transaction-bridge.php     # Transaction management
├── dashboard-bridge.php       # Dashboard data aggregation
└── gallery-bridge.php         # Image gallery handling
```

### Key Bridge Functions

#### Listing Bridge Functions
```php
// Get comprehensive listing data
hpt_get_listing($post_id)          // Complete listing object
hpt_get_listing_price($post_id)    // Formatted price
hpt_get_listing_status($post_id)   // Status (active, sold, pending)
hpt_get_listing_features($post_id) // Property features array
hpt_get_listing_gallery($post_id)  // Image gallery
hpt_get_listing_map($post_id)      // Map coordinates and data
```

#### Agent Bridge Functions
```php
// Get agent data and relationships
hpt_get_agent($post_id)           // Complete agent object
hpt_get_agent_listings($agent_id) // Agent's property listings
hpt_get_agent_stats($agent_id)    // Performance statistics
hpt_get_agent_contact($agent_id)  // Contact information
```

#### Dashboard Bridge Functions
```php
// Dashboard data aggregation
hph_get_dashboard_stats($user_id)     // User dashboard statistics
hph_get_user_listings($user_id)       // User's listings
hph_get_user_leads($user_id)          // Lead management
hph_get_user_transactions($user_id)   # Transaction history
```

### Component Usage Examples

#### Universal Card Component
```php
// template-parts/base/card.php - Adapts to any post type
$args = [
    'post_id' => 123,
    'style' => 'featured',
    'show_agent' => true,
    'show_price' => true
];
hph_component('card', $args);
```

#### Listing Grid Component
```php
// Displays listings in responsive grid
$args = [
    'posts' => $listing_query->posts,
    'columns' => 3,
    'show_map_toggle' => true,
    'show_filters' => true
];
hph_component('listings-grid', $args);
```

#### Dashboard Components
```php
// Dashboard statistics widget
$args = [
    'user_id' => get_current_user_id(),
    'stats_type' => 'listings',
    'period' => 'month'
];
hph_component('dashboard-widget', $args);
```

## Base Component Assets

### Complete Asset Coverage ✅
All base components now have corresponding CSS and JavaScript files with full functionality:

#### Base CSS Files (framework/01-base/)
- **`hph-cards.css`** - Universal card styling for all post types
  - Grid, list, and map layouts
  - Hover effects and animations
  - Featured card variants
  - Loading states and responsive design
- **`hph-stat-cards.css`** - Statistics display cards with animations
  - Trend indicators (up/down arrows)
  - Color variants (primary, success, warning, error)
  - Chart integration support
  - Compact and loading states
- **`hph-data-tables.css`** - Enhanced responsive tables
  - Search and filter controls
  - Sortable headers with indicators
  - Pagination styling
  - Action buttons and bulk operations
- **`hph-variables.css`** - CSS custom properties
- **`hph-reset.css`** - Browser normalization
- **`hph-typography.css`** - Typography system
- **`hph-wp-overrides.css`** - WordPress-specific adjustments

#### Base JavaScript Files (assets/js/base/)
- **`framework-core.js`** - Core HPH framework initialization
- **`card.js`** - Universal card functionality
  - Favorites system with localStorage
  - Social sharing (native + modal fallback)
  - Property comparison (up to 3 items)
  - Contact form integration
  - Filtering and sorting
- **`data-table.js`** - Interactive table features
  - Live search with debouncing
  - Multi-column sorting
  - Filter system with active states
  - AJAX pagination
  - Bulk operations
  - Custom actions via events
- **`stat-card.js`** - Animated statistics cards
  - Scroll-triggered animations
  - Value formatting (currency, percentage, decimal)
  - Chart.js integration
  - Auto-refresh functionality
  - Easing animations
- **`chart.js`** - Dashboard charts and data visualization
  - Chart.js wrapper with defaults
  - Period selection (day, week, month, year)
  - Chart type switching
  - Download as image
  - Responsive behavior
  - AJAX data refresh

### Asset Integration
- **CSS Framework**: All base CSS files are automatically imported in `hph-framework.css`
- **JavaScript Loading**: Base JS files are loaded by the `HPH_Assets` service in proper dependency order
- **External Libraries**: Integrated with Chart.js, Mapbox GL, and Font Awesome

## Component Loading System

### HPH Component Loader
All components are registered and loaded through the HPH Component Loader:

```php
// Load any component using the unified system
hph_component('component-name', $args);

// Examples:
hph_component('card-grid', $args);           // base/card-grid.php
hph_component('archive-layout', $args);      // layout/archive-layout.php
hph_component('listing-hero', $args);        // components/listing/listing-hero.php
hph_component('dashboard-main', $args);      // pages/dashboard/dashboard-main.php
```

## Asset Loading System

### Automatic Asset Loading
The `HPH_Assets` service automatically loads CSS and JavaScript files based on the organized structure:

1. **Framework Core**: `assets/js/base/framework-core.js` loads first
2. **Directory-based Loading**: Automatically loads files from structured directories
3. **Dependency Management**: Proper loading order and dependencies
4. **Context-aware**: Dashboard assets load only on dashboard pages

### External Integrations
- **Mapbox GL JS**: Advanced mapping functionality
- **Chart.js**: Data visualization for dashboards
- **Google Maps API**: Fallback mapping solution
- **Font Awesome**: Icon system

## Development Workflow

### Creating New Components

1. **Determine Category**: Decide which directory based on functionality
   - `base/` - Reusable across post types
   - `layout/` - Page structure components
   - `components/` - Feature-specific functionality
   - `sections/` - Page sections (hero, content)
   - `pages/` - Page-specific templates

2. **Create Files in Matching Directories**:
   ```
   template-parts/components/example/example-widget.php
   assets/css/framework/03-components/hph-example-widget.css
   assets/js/components/example/example-widget.js
   ```

3. **Register Component**: Add to `HPH_Component_Loader` class
   ```php
   'example-widget' => array(
       'path' => 'components/example/example-widget',
       'name' => 'Example Widget',
       'category' => 'components'
   ),
   ```

4. **Use Component**: Load via unified system
   ```php
   hph_component('example-widget', $args);
   ```

## Key Features

### Dashboard System
- Complete agent/user dashboard with routing
- Lead management and tracking
- Listing management with inline editing
- Transaction history and status tracking
- Performance analytics and charts

### Property Management
- Advanced property search and filtering
- Map-based property browsing
- Property comparison functionality
- Virtual tours and photo galleries
- Mortgage calculator integration

### User Experience
- Responsive design across all devices
- AJAX-powered interactions
- Real-time search and filtering
- Favorite properties management
- Social sharing functionality

## Testing and Maintenance

### Asset Management
- Run `npm run lint` for CSS/JS validation
- Use `npm run build` for production builds
- Monitor asset loading performance

### Component Testing
- Test components across different post types
- Verify responsive behavior
- Check AJAX functionality
- Validate bridge function integration

### Bridge System Testing
- Verify plugin integration
- Test data consistency
- Check error handling
- Validate security measures

## Performance Considerations

- **Lazy Loading**: Images and maps load on demand
- **Conditional Loading**: Dashboard assets only load when needed
- **Caching**: Component output caching where appropriate
- **Optimized Queries**: Efficient database queries through bridge functions
- **Asset Minification**: Production assets are minified and compressed

This architecture provides a maintainable, scalable foundation for real estate websites with perfect alignment between templates, styles, and functionality.