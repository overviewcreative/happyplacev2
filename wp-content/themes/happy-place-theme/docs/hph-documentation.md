# Happy Place Theme Documentation

## Table of Contents
1. [Architecture Overview](#architecture-overview)
2. [System Components](#system-components)
3. [Getting Started](#getting-started)
4. [Core Concepts](#core-concepts)
5. [Services](#services)
6. [Template System](#template-system)
7. [Component Development](#component-development)
8. [Bridge Functions](#bridge-functions)
9. [Best Practices](#best-practices)
10. [Development Workflow](#development-workflow)
11. [Performance Guidelines](#performance-guidelines)
12. [Troubleshooting](#troubleshooting)

---

## Architecture Overview

The Happy Place Theme uses a **service-oriented architecture** that separates concerns into manageable, single-responsibility classes. This modern approach provides a clean, scalable, and maintainable codebase.

### Core Principles

1. **Minimal Bootstrap**: `functions.php` is kept minimal (~30 lines)
2. **Service Container**: All functionality is organized into services
3. **Component-Based**: UI elements are self-contained components
4. **Bridge Pattern**: Data access is abstracted through bridge functions
5. **Smart Loading**: Assets and components load only when needed

### Architecture Diagram

```
┌─────────────────────────────────────────────────────────┐
│                     functions.php                        │
│                     (Minimal Bootstrap)                  │
└────────────────────────┬────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────┐
│                    HPH_Theme Class                       │
│                  (Service Container)                     │
└────────────────────────┬────────────────────────────────┘
                         │
        ┌────────────────┼────────────────┐
        ▼                ▼                ▼
┌──────────────┐ ┌──────────────┐ ┌──────────────┐
│   Services   │ │Template Loader│ │Bridge Functions│
│              │ │              │ │              │
│ - Config     │ │ - Components │ │ - Listings   │
│ - Assets     │ │ - Shortcodes │ │ - Agents     │
│ - Router     │ │ - Blocks     │ │ - Communities│
│ - Query      │ │ - Assets     │ │ - Etc...     │
└──────────────┘ └──────────────┘ └──────────────┘
```

---

## System Components

### 1. Bootstrap Layer
- **functions.php**: Defines constants and loads theme class
- **class-hph-theme.php**: Main orchestrator and service container

### 2. Service Layer
Individual services handle specific functionality:
- Configuration management
- Asset loading
- Query modifications
- Routing
- AJAX handling
- Widget management
- Performance optimizations

### 3. Template Layer
- **Template Loader**: Manages component registration and loading
- **Components**: Self-contained UI elements
- **Templates**: Page templates that use components

### 4. Data Layer
- **Bridge Functions**: Abstract data access from ACF/Plugin
- **Extended Bridge Functions**: Component-specific data helpers

---

## Getting Started

### Initial Setup

1. **Install the theme**
   ```bash
   cd wp-content/themes/
   git clone [repository] happy-place
   ```

2. **Activate the theme**
   - Go to WordPress Admin → Appearance → Themes
   - Activate "Happy Place"

3. **Verify requirements**
   - PHP 7.4 or higher
   - WordPress 5.8 or higher
   - Happy Place Plugin activated
   - ACF Pro installed

### File Structure

```
happy-place/
├── functions.php                 # Minimal bootstrap (30 lines)
├── includes/
│   ├── class-hph-theme.php     # Main theme class
│   ├── class-hph-template-loader.php
│   ├── services/                # Service classes
│   ├── bridge/                  # Bridge functions
│   ├── widgets/                 # Custom widgets
│   └── interfaces/              # Service interfaces
├── template-parts/
│   └── components/              # Component templates
├── templates/                   # Page templates
├── assets/
│   ├── css/
│   │   ├── components/         # Component styles
│   │   └── global.css          # Global styles
│   └── js/
│       ├── components/         # Component scripts
│       └── global.js           # Global scripts
└── config/
    └── theme-config.php        # Optional config file
```

---

## Core Concepts

### Service Container

The theme uses a service container pattern where all functionality is organized into services that are registered and initialized by the main theme class.

```php
// Accessing services
$config = HPH_Theme::instance()->config;
$router = HPH_Theme::instance()->router;
$assets = HPH_Theme::instance()->assets;
```

### Component System

Components are self-contained UI elements with their own:
- Template file
- CSS file
- JavaScript file (optional)
- Shortcode registration
- Bridge function dependencies

### Smart Asset Loading

Assets are registered but only enqueued when needed:
- Components load their assets automatically
- Conditional loading based on page type
- Shortcode detection for automatic loading

---

## Services

### Creating a New Service

1. **Create the service class** in `includes/services/`:

```php
<?php
/**
 * My Custom Service
 * 
 * @package HappyPlaceTheme
 */

class HPH_My_Service {
    
    /**
     * Initialize service
     */
    public function init() {
        // Setup hooks
        add_action('init', array($this, 'setup'));
    }
    
    /**
     * Setup functionality
     */
    public function setup() {
        // Service logic here
    }
    
    /**
     * Public method example
     */
    public function do_something($param) {
        // Method implementation
        return $result;
    }
}
```

2. **Register the service** in `class-hph-theme.php`:

```php
private function register_services() {
    $this->services = array(
        // ... existing services
        'my_service' => 'HPH_My_Service',
    );
}
```

3. **Use the service** anywhere in the theme:

```php
// Access the service
$my_service = HPH_Theme::instance()->my_service;
$result = $my_service->do_something('parameter');
```

### Service Best Practices

- **Single Responsibility**: Each service should have one clear purpose
- **Dependency Injection**: Pass dependencies through constructor or methods
- **Hooks in init()**: Register all hooks in the init() method
- **Public API**: Keep public methods minimal and well-documented

### Core Services Reference

| Service | Purpose | Key Methods |
|---------|---------|-------------|
| `HPH_Config` | Configuration management | `get()`, `set()`, `all()` |
| `HPH_Assets` | CSS/JS management | `enqueue()`, `register()`, `localize()` |
| `HPH_Router` | Custom routing | `add_route()`, `is_route()`, `get_current_route()` |
| `HPH_Query_Manager` | Query modifications | `modify_queries()`, `build_search_query()` |
| `HPH_Widgets` | Widget areas | `register_widget_areas()`, `get_sidebar()` |
| `HPH_Ajax_Handler` | AJAX endpoints | `register_endpoint()`, `handle_request()` |
| `HPH_Customizer` | Theme options | `register_settings()`, `get_option()` |
| `HPH_Performance` | Optimizations | `optimize()`, `enable_caching()` |

---

## Template System

### Component Registration

Components are registered in the Template Loader with configuration:

```php
$this->components = array(
    'listing-card' => array(
        'name'        => 'Listing Card',
        'description' => 'Property listing card',
        'template'    => 'listing-card',
        'styles'      => array('listing-card'),
        'scripts'     => array('listing-card'),
        'shortcode'   => 'hph_listing_card',
        'supports'    => array('customizer', 'block-editor'),
        'version'     => '1.0.0'
    ),
);
```

### Creating a New Component

#### 1. Create Template File
`template-parts/components/my-component.php`:

```php
<?php
/**
 * My Component Template
 * 
 * @package HappyPlaceTheme
 */

// Get component arguments
$args = wp_parse_args($args, array(
    'title' => '',
    'content' => '',
    'style' => 'default',
));

// Get data via bridge functions
$data = hpt_get_some_data($args['id']);
?>

<div class="hph-my-component hph-my-component--<?php echo esc_attr($args['style']); ?>">
    <h3 class="hph-my-component__title">
        <?php echo esc_html($args['title']); ?>
    </h3>
    <div class="hph-my-component__content">
        <?php echo wp_kses_post($args['content']); ?>
    </div>
</div>
```

#### 2. Create Styles
`assets/css/components/my-component.css`:

```css
.hph-my-component {
    padding: var(--hph-spacing-md);
    background: var(--hph-white);
    border-radius: var(--hph-radius-md);
    box-shadow: var(--hph-shadow-sm);
}

.hph-my-component__title {
    font-size: var(--hph-text-lg);
    font-weight: 600;
    margin-bottom: var(--hph-spacing-sm);
}

.hph-my-component--featured {
    border: 2px solid var(--hph-primary);
}
```

#### 3. Create JavaScript (if needed)
`assets/js/components/my-component.js`:

```javascript
(function($) {
    'use strict';
    
    class MyComponent {
        constructor(element) {
            this.$element = $(element);
            this.init();
        }
        
        init() {
            // Component initialization
            this.bindEvents();
        }
        
        bindEvents() {
            this.$element.on('click', '.action-btn', (e) => {
                this.handleAction(e);
            });
        }
        
        handleAction(e) {
            e.preventDefault();
            // Handle action
        }
    }
    
    // Initialize all instances
    $(document).ready(() => {
        $('.hph-my-component').each(function() {
            new MyComponent(this);
        });
    });
    
})(jQuery);
```

#### 4. Register Component

Add to Template Loader's `register_components()` method:

```php
'my-component' => array(
    'name'        => 'My Component',
    'template'    => 'my-component',
    'styles'      => array('my-component'),
    'scripts'     => array('my-component'),
    'shortcode'   => 'hph_my_component',
    'version'     => '1.0.0'
),
```

### Using Components

#### In Templates

```php
// Method 1: Template part
get_template_part('template-parts/components/listing-card', null, array(
    'listing_id' => get_the_ID(),
    'style' => 'featured'
));

// Method 2: Template Loader
$loader = HPH_Template_Loader::get_instance();
echo $loader->load_component('listing-card', array(
    'listing_id' => 123
));

// Method 3: Shortcode
echo do_shortcode('[hph_listing_card id="123" style="featured"]');
```

#### In Content

```
[hph_listing_card id="123" style="featured"]
```

#### Via AJAX

```javascript
jQuery.ajax({
    url: hphData.ajaxUrl,
    type: 'POST',
    data: {
        action: 'hph_load_component',
        component: 'listing-card',
        args: {
            listing_id: 123,
            style: 'featured'
        },
        nonce: hphData.nonce
    },
    success: function(response) {
        if (response.success) {
            jQuery('#container').html(response.data.html);
        }
    }
});
```

---

## Bridge Functions

Bridge functions provide a consistent API for accessing data, abstracting the underlying implementation (ACF, custom fields, etc.).

### Naming Convention

```
hpt_get_{post_type}_{field}()
hpt_is_{post_type}_{state}()
hpt_has_{post_type}_{feature}()
```

### Creating Bridge Functions

```php
/**
 * Get listing price
 * 
 * @param int $listing_id Listing post ID
 * @return string Formatted price
 */
function hpt_get_listing_price($listing_id) {
    $price = get_field('listing_price', $listing_id);
    
    if (!$price) {
        return '';
    }
    
    return '$' . number_format($price);
}

/**
 * Check if listing is featured
 * 
 * @param int $listing_id Listing post ID
 * @return bool
 */
function hpt_is_listing_featured($listing_id) {
    return get_field('featured_listing', $listing_id) === true;
}
```

### Bridge Function Categories

1. **Data Retrieval**: `hpt_get_*`
2. **State Checking**: `hpt_is_*`
3. **Feature Detection**: `hpt_has_*`
4. **Calculations**: `hpt_calculate_*`
5. **Formatting**: `hpt_format_*`

---

## Best Practices

### Code Organization

1. **Follow PSR Standards**
   - Use PSR-4 autoloading structure
   - Follow PSR-12 coding standards
   - Consistent naming conventions

2. **File Naming**
   - Services: `class-hph-{service-name}.php`
   - Widgets: `class-widget-{widget-name}.php`
   - Templates: `{template-name}.php`
   - Components: `{component-name}.php`

3. **Class Naming**
   - Services: `HPH_{Service_Name}`
   - Widgets: `HPH_Widget_{Widget_Name}`
   - Utilities: `HPH_{Utility_Name}`

### Component Development

1. **Self-Contained Components**
   - All component code in one directory
   - No external dependencies
   - Clear input/output interface

2. **BEM Methodology for CSS**
   ```css
   .hph-component {}              /* Block */
   .hph-component__element {}     /* Element */
   .hph-component--modifier {}     /* Modifier */
   ```

3. **Data Validation**
   ```php
   // Always validate and sanitize inputs
   $args = wp_parse_args($args, array(
       'id' => 0,
       'style' => 'default',
   ));
   
   $id = absint($args['id']);
   $style = sanitize_text_field($args['style']);
   ```

### Performance

1. **Lazy Loading**
   - Load assets only when needed
   - Use conditional enqueuing
   - Implement lazy loading for images

2. **Caching Strategy**
   ```php
   // Cache expensive operations
   $cache_key = 'hph_expensive_data_' . $id;
   $data = wp_cache_get($cache_key);
   
   if (false === $data) {
       $data = expensive_operation();
       wp_cache_set($cache_key, $data, '', 3600);
   }
   ```

3. **Database Queries**
   - Use WP_Query properly
   - Limit query results
   - Cache query results

### Security

1. **Data Sanitization**
   ```php
   // Input sanitization
   $input = sanitize_text_field($_POST['input']);
   $number = absint($_POST['number']);
   $html = wp_kses_post($_POST['html']);
   ```

2. **Data Escaping**
   ```php
   // Output escaping
   echo esc_html($text);
   echo esc_attr($attribute);
   echo esc_url($url);
   echo wp_kses_post($html);
   ```

3. **Nonce Verification**
   ```php
   // AJAX security
   if (!wp_verify_nonce($_POST['nonce'], 'hph_nonce')) {
       wp_die('Security check failed');
   }
   ```

---

## Development Workflow

### Setting Up Development Environment

1. **Enable Debug Mode**
   ```php
   // wp-config.php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   define('SCRIPT_DEBUG', true);
   ```

2. **Use Version Control**
   ```bash
   git init
   git add .
   git commit -m "Initial theme setup"
   ```

3. **Install Development Tools**
   - Node.js for build tools
   - Composer for PHP dependencies
   - Browser DevTools
   - Query Monitor plugin

### Adding New Features

1. **Plan the Feature**
   - Define requirements
   - Identify affected components
   - Plan data structure

2. **Create Service/Component**
   - Follow naming conventions
   - Write clean, documented code
   - Include error handling

3. **Test Thoroughly**
   - Unit testing for services
   - Integration testing for components
   - Cross-browser testing
   - Mobile responsiveness

4. **Document Changes**
   - Update inline documentation
   - Add to changelog
   - Update this documentation

### Debugging

1. **Enable Debug Output**
   ```php
   if (HPH_DEV_MODE) {
       error_log('Debug: ' . print_r($data, true));
   }
   ```

2. **Use Browser Console**
   ```javascript
   console.log('Component initialized:', this);
   console.table(data);
   ```

3. **Query Monitor Plugin**
   - Monitor database queries
   - Check hook execution
   - Analyze performance

---

## Agent Dashboard

The Agent Dashboard is a comprehensive, private area where agents can manage listings, leads, transactions, and their profile. It's built using the component system and custom routing.

### Dashboard Architecture

```
Dashboard Structure
├── Router (handles /agent-dashboard/ URL)
├── Main Dashboard Template
├── Dashboard Sections (via AJAX or page reload)
│   ├── Overview (default)
│   ├── Listings Management
│   ├── Leads Management
│   ├── Transactions
│   ├── Open Houses
│   ├── Marketing Tools
│   ├── Analytics
│   └── Profile Settings
└── Dashboard Components
    ├── Stats Cards
    ├── Activity Feed
    ├── Quick Actions
    └── Data Tables
```

### Setting Up the Dashboard

#### 1. Dashboard Service

Create `includes/services/class-hph-dashboard.php`:

```php
<?php
/**
 * Dashboard Service
 * 
 * Manages agent dashboard functionality
 * 
 * @package HappyPlaceTheme
 */

class HPH_Dashboard {
    
    /**
     * Dashboard sections
     */
    private $sections = array();
    
    /**
     * Current user capabilities
     */
    private $user_caps = array();
    
    /**
     * Initialize dashboard
     */
    public function init() {
        $this->register_sections();
        $this->setup_hooks();
        $this->check_capabilities();
    }
    
    /**
     * Register dashboard sections
     */
    private function register_sections() {
        $this->sections = array(
            'overview' => array(
                'title' => __('Overview', 'happy-place-theme'),
                'icon' => 'fas fa-tachometer-alt',
                'capability' => 'read',
                'template' => 'dashboard-overview',
                'priority' => 10,
            ),
            'listings' => array(
                'title' => __('My Listings', 'happy-place-theme'),
                'icon' => 'fas fa-home',
                'capability' => 'edit_posts',
                'template' => 'dashboard-listings',
                'priority' => 20,
            ),
            'leads' => array(
                'title' => __('Leads', 'happy-place-theme'),
                'icon' => 'fas fa-users',
                'capability' => 'edit_posts',
                'template' => 'dashboard-leads',
                'priority' => 30,
            ),
            'transactions' => array(
                'title' => __('Transactions', 'happy-place-theme'),
                'icon' => 'fas fa-handshake',
                'capability' => 'edit_posts',
                'template' => 'dashboard-transactions',
                'priority' => 40,
            ),
            'open-houses' => array(
                'title' => __('Open Houses', 'happy-place-theme'),
                'icon' => 'fas fa-calendar',
                'capability' => 'edit_posts',
                'template' => 'dashboard-open-houses',
                'priority' => 50,
            ),
            'analytics' => array(
                'title' => __('Analytics', 'happy-place-theme'),
                'icon' => 'fas fa-chart-line',
                'capability' => 'read',
                'template' => 'dashboard-analytics',
                'priority' => 60,
            ),
            'marketing' => array(
                'title' => __('Marketing', 'happy-place-theme'),
                'icon' => 'fas fa-bullhorn',
                'capability' => 'edit_posts',
                'template' => 'dashboard-marketing',
                'priority' => 70,
            ),
            'profile' => array(
                'title' => __('Profile', 'happy-place-theme'),
                'icon' => 'fas fa-user',
                'capability' => 'read',
                'template' => 'dashboard-profile',
                'priority' => 100,
            ),
        );
        
        // Allow filtering
        $this->sections = apply_filters('hph_dashboard_sections', $this->sections);
        
        // Sort by priority
        uasort($this->sections, function($a, $b) {
            return $a['priority'] - $b['priority'];
        });
    }
    
    /**
     * Get dashboard sections for current user
     */
    public function get_user_sections() {
        $user_sections = array();
        
        foreach ($this->sections as $key => $section) {
            if (current_user_can($section['capability'])) {
                $user_sections[$key] = $section;
            }
        }
        
        return $user_sections;
    }
    
    /**
     * Get current section
     */
    public function get_current_section() {
        $section = get_query_var('dashboard_page', 'overview');
        
        // Validate section exists and user has access
        if (!isset($this->sections[$section])) {
            return 'overview';
        }
        
        if (!current_user_can($this->sections[$section]['capability'])) {
            return 'overview';
        }
        
        return $section;
    }
    
    /**
     * Get dashboard stats for user
     */
    public function get_user_stats($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $stats = array(
            'active_listings' => $this->count_user_listings($user_id, 'active'),
            'pending_listings' => $this->count_user_listings($user_id, 'pending'),
            'sold_listings' => $this->count_user_listings($user_id, 'sold'),
            'total_leads' => $this->count_user_leads($user_id),
            'new_leads' => $this->count_user_leads($user_id, 'new'),
            'active_transactions' => $this->count_user_transactions($user_id, 'active'),
            'closed_transactions' => $this->count_user_transactions($user_id, 'closed'),
            'upcoming_open_houses' => $this->count_upcoming_open_houses($user_id),
        );
        
        return apply_filters('hph_dashboard_stats', $stats, $user_id);
    }
    
    // ... Additional methods for counting and data retrieval
}
```

#### 2. Dashboard Routing

The dashboard uses custom routing to handle the `/agent-dashboard/` URL. This is managed by the Router service:

```php
// Routes registered in HPH_Router
'agent-dashboard' => array(
    'regex'     => '^agent-dashboard/?',
    'query_var' => 'agent_dashboard',
    'template'  => 'dashboard/dashboard-main.php',
    'auth'      => true,
    'capability' => 'edit_posts',
),
```

#### 3. Main Dashboard Template

Create `templates/dashboard/dashboard-main.php`:

```php
<?php
/**
 * Dashboard Main Template
 * 
 * @package HappyPlaceTheme
 */

// Check authentication (handled by router, but double-check)
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

// Get dashboard service
$dashboard = HPH_Theme::instance()->dashboard;
$current_section = $dashboard->get_current_section();
$sections = $dashboard->get_user_sections();
$stats = $dashboard->get_user_stats();

get_header('dashboard');
?>

<div class="hph-dashboard">
    <!-- Dashboard Sidebar -->
    <aside class="hph-dashboard__sidebar">
        <div class="hph-dashboard__brand">
            <img src="<?php echo HPH_IMAGES_URI; ?>/logo.png" alt="Happy Place">
        </div>
        
        <nav class="hph-dashboard__nav">
            <ul class="hph-dashboard__menu">
                <?php foreach ($sections as $key => $section): ?>
                    <li class="hph-dashboard__menu-item <?php echo $current_section === $key ? 'active' : ''; ?>">
                        <a href="<?php echo home_url('/agent-dashboard/?dashboard_page=' . $key); ?>">
                            <i class="<?php echo esc_attr($section['icon']); ?>"></i>
                            <span><?php echo esc_html($section['title']); ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
        
        <div class="hph-dashboard__user">
            <?php 
            $current_user = wp_get_current_user();
            echo get_avatar($current_user->ID, 40);
            ?>
            <div class="hph-dashboard__user-info">
                <strong><?php echo esc_html($current_user->display_name); ?></strong>
                <a href="<?php echo wp_logout_url(); ?>"><?php _e('Logout', 'happy-place-theme'); ?></a>
            </div>
        </div>
    </aside>
    
    <!-- Dashboard Main Content -->
    <main class="hph-dashboard__main">
        <!-- Top Bar -->
        <header class="hph-dashboard__header">
            <h1><?php echo esc_html($sections[$current_section]['title']); ?></h1>
            
            <div class="hph-dashboard__actions">
                <button class="btn btn-primary" data-action="quick-add">
                    <i class="fas fa-plus"></i>
                    <?php _e('Quick Add', 'happy-place-theme'); ?>
                </button>
            </div>
        </header>
        
        <!-- Dashboard Content -->
        <div class="hph-dashboard__content">
            <?php
            // Load section template
            get_template_part('templates/dashboard/sections/' . $sections[$current_section]['template']);
            ?>
        </div>
    </main>
</div>

<?php get_footer('dashboard'); ?>
```

#### 4. Dashboard Section Templates

Create section templates in `templates/dashboard/sections/`:

**dashboard-overview.php:**
```php
<?php
/**
 * Dashboard Overview Section
 */

$dashboard = HPH_Theme::instance()->dashboard;
$stats = $dashboard->get_user_stats();
$recent_activity = $dashboard->get_recent_activity();
?>

<div class="hph-dashboard-overview">
    <!-- Stats Grid -->
    <div class="hph-stats-grid">
        <div class="hph-stat-card">
            <div class="hph-stat-card__icon">
                <i class="fas fa-home"></i>
            </div>
            <div class="hph-stat-card__content">
                <div class="hph-stat-card__value"><?php echo $stats['active_listings']; ?></div>
                <div class="hph-stat-card__label"><?php _e('Active Listings', 'happy-place-theme'); ?></div>
            </div>
        </div>
        
        <div class="hph-stat-card">
            <div class="hph-stat-card__icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="hph-stat-card__content">
                <div class="hph-stat-card__value"><?php echo $stats['new_leads']; ?></div>
                <div class="hph-stat-card__label"><?php _e('New Leads', 'happy-place-theme'); ?></div>
            </div>
        </div>
        
        <div class="hph-stat-card">
            <div class="hph-stat-card__icon">
                <i class="fas fa-handshake"></i>
            </div>
            <div class="hph-stat-card__content">
                <div class="hph-stat-card__value"><?php echo $stats['active_transactions']; ?></div>
                <div class="hph-stat-card__label"><?php _e('Active Deals', 'happy-place-theme'); ?></div>
            </div>
        </div>
        
        <div class="hph-stat-card">
            <div class="hph-stat-card__icon">
                <i class="fas fa-calendar"></i>
            </div>
            <div class="hph-stat-card__content">
                <div class="hph-stat-card__value"><?php echo $stats['upcoming_open_houses']; ?></div>
                <div class="hph-stat-card__label"><?php _e('Open Houses', 'happy-place-theme'); ?></div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="hph-dashboard-section">
        <h2><?php _e('Recent Activity', 'happy-place-theme'); ?></h2>
        <div class="hph-activity-feed">
            <?php foreach ($recent_activity as $activity): ?>
                <div class="hph-activity-item">
                    <div class="hph-activity-item__icon">
                        <i class="<?php echo esc_attr($activity['icon']); ?>"></i>
                    </div>
                    <div class="hph-activity-item__content">
                        <div class="hph-activity-item__message">
                            <?php echo wp_kses_post($activity['message']); ?>
                        </div>
                        <div class="hph-activity-item__time">
                            <?php echo human_time_diff($activity['timestamp'], current_time('timestamp')); ?> ago
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
```

**dashboard-listings.php:**
```php
<?php
/**
 * Dashboard Listings Management Section
 */

// Get user's listings
$args = array(
    'post_type' => 'listing',
    'author' => get_current_user_id(),
    'posts_per_page' => 20,
    'post_status' => array('publish', 'pending', 'draft'),
);

$listings = new WP_Query($args);
?>

<div class="hph-dashboard-listings">
    <!-- Filters -->
    <div class="hph-dashboard-filters">
        <select id="listing-status-filter">
            <option value=""><?php _e('All Status', 'happy-place-theme'); ?></option>
            <option value="active"><?php _e('Active', 'happy-place-theme'); ?></option>
            <option value="pending"><?php _e('Pending', 'happy-place-theme'); ?></option>
            <option value="sold"><?php _e('Sold', 'happy-place-theme'); ?></option>
        </select>
        
        <input type="text" id="listing-search" placeholder="<?php _e('Search listings...', 'happy-place-theme'); ?>">
        
        <button class="btn btn-primary" data-action="add-listing">
            <i class="fas fa-plus"></i>
            <?php _e('Add New Listing', 'happy-place-theme'); ?>
        </button>
    </div>
    
    <!-- Listings Table -->
    <div class="hph-data-table">
        <table>
            <thead>
                <tr>
                    <th><?php _e('Photo', 'happy-place-theme'); ?></th>
                    <th><?php _e('Address', 'happy-place-theme'); ?></th>
                    <th><?php _e('Price', 'happy-place-theme'); ?></th>
                    <th><?php _e('Status', 'happy-place-theme'); ?></th>
                    <th><?php _e('Views', 'happy-place-theme'); ?></th>
                    <th><?php _e('Leads', 'happy-place-theme'); ?></th>
                    <th><?php _e('Actions', 'happy-place-theme'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($listings->have_posts()): ?>
                    <?php while ($listings->have_posts()): $listings->the_post(); ?>
                        <tr data-listing-id="<?php the_ID(); ?>">
                            <td>
                                <?php if (has_post_thumbnail()): ?>
                                    <?php the_post_thumbnail('thumbnail'); ?>
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-home"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo hpt_get_listing_address(get_the_ID()); ?></strong><br>
                                <small><?php echo hpt_get_listing_city(get_the_ID()); ?>, <?php echo hpt_get_listing_state(get_the_ID()); ?></small>
                            </td>
                            <td><?php echo hpt_get_listing_price(get_the_ID()); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo hpt_get_listing_status(get_the_ID()); ?>">
                                    <?php echo hpt_get_listing_status_label(get_the_ID()); ?>
                                </span>
                            </td>
                            <td><?php echo hpt_get_listing_views(get_the_ID(), 'week'); ?></td>
                            <td><?php echo hpt_get_listing_lead_count(get_the_ID()); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="<?php the_permalink(); ?>" class="btn-icon" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button class="btn-icon" data-action="edit-listing" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon" data-action="listing-stats" title="Statistics">
                                        <i class="fas fa-chart-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="no-results">
                            <?php _e('No listings found. Add your first listing to get started!', 'happy-place-theme'); ?>
                        </td>
                    </tr>
                <?php endif; ?>
                <?php wp_reset_postdata(); ?>
            </tbody>
        </table>
    </div>
</div>
```

### Dashboard AJAX Handlers

Create AJAX handlers for dashboard actions in `includes/ajax/dashboard-ajax.php`:

```php
<?php
/**
 * Dashboard AJAX Handlers
 */

class HPH_Dashboard_Ajax {
    
    public function __construct() {
        // Listing actions
        add_action('wp_ajax_hph_quick_add_listing', array($this, 'quick_add_listing'));
        add_action('wp_ajax_hph_update_listing_status', array($this, 'update_listing_status'));
        add_action('wp_ajax_hph_get_listing_stats', array($this, 'get_listing_stats'));
        
        // Lead actions
        add_action('wp_ajax_hph_get_leads', array($this, 'get_leads'));
        add_action('wp_ajax_hph_update_lead_status', array($this, 'update_lead_status'));
        add_action('wp_ajax_hph_add_lead_note', array($this, 'add_lead_note'));
        
        // Dashboard data
        add_action('wp_ajax_hph_get_dashboard_stats', array($this, 'get_dashboard_stats'));
        add_action('wp_ajax_hph_get_activity_feed', array($this, 'get_activity_feed'));
    }
    
    /**
     * Quick add listing
     */
    public function quick_add_listing() {
        check_ajax_referer('hph_dashboard_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $listing_data = array(
            'post_title' => sanitize_text_field($_POST['title']),
            'post_type' => 'listing',
            'post_status' => 'draft',
            'post_author' => get_current_user_id(),
        );
        
        $listing_id = wp_insert_post($listing_data);
        
        if ($listing_id) {
            // Update ACF fields
            update_field('listing_price', intval($_POST['price']), $listing_id);
            update_field('bedrooms', intval($_POST['bedrooms']), $listing_id);
            update_field('bathrooms', intval($_POST['bathrooms']), $listing_id);
            
            wp_send_json_success(array(
                'listing_id' => $listing_id,
                'edit_url' => get_edit_post_link($listing_id, 'raw'),
            ));
        } else {
            wp_send_json_error('Failed to create listing');
        }
    }
    
    /**
     * Get dashboard statistics
     */
    public function get_dashboard_stats() {
        check_ajax_referer('hph_dashboard_nonce', 'nonce');
        
        $dashboard = HPH_Theme::instance()->dashboard;
        $stats = $dashboard->get_user_stats();
        
        wp_send_json_success($stats);
    }
}

// Initialize AJAX handlers
new HPH_Dashboard_Ajax();
```

### Dashboard Assets

Create dashboard-specific styles in `assets/css/dashboard.css`:

```css
/* Dashboard Layout */
.hph-dashboard {
    display: grid;
    grid-template-columns: 260px 1fr;
    min-height: 100vh;
    background: var(--hph-gray-50);
}

/* Sidebar */
.hph-dashboard__sidebar {
    background: var(--hph-gray-900);
    color: var(--hph-white);
    display: flex;
    flex-direction: column;
}

.hph-dashboard__brand {
    padding: 1.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.hph-dashboard__menu {
    list-style: none;
    padding: 1rem 0;
}

.hph-dashboard__menu-item a {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1.5rem;
    color: var(--hph-gray-300);
    text-decoration: none;
    transition: all 0.2s;
}

.hph-dashboard__menu-item.active a,
.hph-dashboard__menu-item a:hover {
    background: rgba(255, 255, 255, 0.1);
    color: var(--hph-white);
}

/* Main Content */
.hph-dashboard__main {
    display: flex;
    flex-direction: column;
}

.hph-dashboard__header {
    background: var(--hph-white);
    padding: 1.5rem 2rem;
    border-bottom: 1px solid var(--hph-gray-200);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.hph-dashboard__content {
    padding: 2rem;
    flex: 1;
}

/* Stats Grid */
.hph-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.hph-stat-card {
    background: var(--hph-white);
    padding: 1.5rem;
    border-radius: var(--hph-radius-lg);
    box-shadow: var(--hph-shadow-sm);
    display: flex;
    gap: 1rem;
}

.hph-stat-card__icon {
    width: 48px;
    height: 48px;
    background: var(--hph-accent);
    border-radius: var(--hph-radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--hph-primary);
}

.hph-stat-card__value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--hph-gray-900);
}

.hph-stat-card__label {
    font-size: 0.875rem;
    color: var(--hph-gray-600);
}

/* Data Tables */
.hph-data-table {
    background: var(--hph-white);
    border-radius: var(--hph-radius-lg);
    overflow: hidden;
    box-shadow: var(--hph-shadow-sm);
}

.hph-data-table table {
    width: 100%;
    border-collapse: collapse;
}

.hph-data-table th {
    background: var(--hph-gray-50);
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: var(--hph-gray-700);
    border-bottom: 2px solid var(--hph-gray-200);
}

.hph-data-table td {
    padding: 1rem;
    border-bottom: 1px solid var(--hph-gray-100);
}

/* Responsive */
@media (max-width: 1024px) {
    .hph-dashboard {
        grid-template-columns: 1fr;
    }
    
    .hph-dashboard__sidebar {
        position: fixed;
        left: -260px;
        top: 0;
        bottom: 0;
        width: 260px;
        z-index: 1000;
        transition: left 0.3s;
    }
    
    .hph-dashboard__sidebar.active {
        left: 0;
    }
}
```

### Dashboard JavaScript

Create dashboard functionality in `assets/js/dashboard.js`:

```javascript
(function($) {
    'use strict';
    
    class Dashboard {
        constructor() {
            this.init();
        }
        
        init() {
            this.bindEvents();
            this.initDataTables();
            this.loadStats();
        }
        
        bindEvents() {
            // Quick add
            $(document).on('click', '[data-action="quick-add"]', (e) => {
                this.openQuickAdd();
            });
            
            // Edit listing
            $(document).on('click', '[data-action="edit-listing"]', (e) => {
                const listingId = $(e.currentTarget).closest('tr').data('listing-id');
                this.editListing(listingId);
            });
            
            // Mobile menu toggle
            $(document).on('click', '.hph-dashboard__menu-toggle', (e) => {
                $('.hph-dashboard__sidebar').toggleClass('active');
            });
            
            // Filter listings
            $('#listing-status-filter').on('change', (e) => {
                this.filterListings($(e.target).val());
            });
            
            // Search listings
            $('#listing-search').on('keyup debounce', (e) => {
                this.searchListings($(e.target).val());
            });
        }
        
        initDataTables() {
            // Initialize any data table plugins
            if ($.fn.DataTable) {
                $('.hph-data-table table').DataTable({
                    responsive: true,
                    pageLength: 25,
                });
            }
        }
        
        loadStats() {
            $.ajax({
                url: hphData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hph_get_dashboard_stats',
                    nonce: hphData.dashboardNonce,
                },
                success: (response) => {
                    if (response.success) {
                        this.updateStats(response.data);
                    }
                }
            });
        }
        
        updateStats(stats) {
            // Update stat cards with animation
            $('.hph-stat-card').each(function() {
                const $value = $(this).find('.hph-stat-card__value');
                const key = $(this).data('stat-key');
                
                if (stats[key] !== undefined) {
                    $value.text(stats[key]);
                }
            });
        }
        
        openQuickAdd() {
            // Open quick add modal
            const modal = new QuickAddModal();
            modal.open();
        }
        
        editListing(listingId) {
            // Open edit modal or redirect
            window.location.href = `/agent-dashboard/?dashboard_page=listings&edit=${listingId}`;
        }
        
        filterListings(status) {
            // Filter table rows
            if (!status) {
                $('.hph-data-table tbody tr').show();
            } else {
                $('.hph-data-table tbody tr').each(function() {
                    const rowStatus = $(this).find('.status-badge').text().toLowerCase();
                    $(this).toggle(rowStatus.includes(status));
                });
            }
        }
        
        searchListings(query) {
            // Search implementation
            const searchTerm = query.toLowerCase();
            
            $('.hph-data-table tbody tr').each(function() {
                const text = $(this).text().toLowerCase();
                $(this).toggle(text.includes(searchTerm));
            });
        }
    }
    
    // Initialize dashboard
    $(document).ready(() => {
        if ($('.hph-dashboard').length) {
            new Dashboard();
        }
    });
    
})(jQuery);
```

### Dashboard Security & Permissions

The dashboard implements multiple security layers:

1. **Route-Level Security** (in Router service):
   - Authentication check
   - Capability verification
   - Automatic login redirect

2. **Template-Level Security**:
   ```php
   // In dashboard templates
   if (!current_user_can('edit_posts')) {
       wp_die('Insufficient permissions');
   }
   ```

3. **AJAX Security**:
   ```php
   // In AJAX handlers
   check_ajax_referer('hph_dashboard_nonce', 'nonce');
   
   if (!current_user_can('edit_posts')) {
       wp_send_json_error('Insufficient permissions');
   }
   ```

4. **Data Security**:
   ```php
   // Only show user's own data
   $args = array(
       'post_type' => 'listing',
       'author' => get_current_user_id(),
   );
   ```

### Dashboard URL Structure

The dashboard uses clean URLs with query parameters for sections:

- Main Dashboard: `/agent-dashboard/`
- Listings: `/agent-dashboard/?dashboard_page=listings`
- Leads: `/agent-dashboard/?dashboard_page=leads`
- Profile: `/agent-dashboard/?dashboard_page=profile`

### Extending the Dashboard

#### Adding New Sections

```php
// In your plugin or child theme
add_filter('hph_dashboard_sections', function($sections) {
    $sections['custom'] = array(
        'title' => __('Custom Section', 'textdomain'),
        'icon' => 'fas fa-cog',
        'capability' => 'edit_posts',
        'template' => 'dashboard-custom',
        'priority' => 80,
    );
    return $sections;
});
```

#### Adding Dashboard Widgets

```php
// Add to dashboard overview
add_action('hph_dashboard_overview_widgets', function() {
    ?>
    <div class="hph-dashboard-widget">
        <h3>Custom Widget</h3>
        <p>Widget content here</p>
    </div>
    <?php
});
```

#### Custom Dashboard Capabilities

```php
// Define custom capabilities
add_filter('hph_dashboard_capabilities', function($caps) {
    $caps['view_analytics'] = 'manage_options';
    $caps['export_data'] = 'edit_others_posts';
    return $caps;
});
```

### Dashboard Performance Optimization

1. **Lazy Loading**: Load sections via AJAX when clicked
2. **Caching**: Cache dashboard stats for 5 minutes
3. **Pagination**: Limit initial data display
4. **Async Loading**: Load charts and heavy content asynchronously

### Mobile Dashboard

The dashboard is fully responsive with:
- Collapsible sidebar on mobile
- Touch-friendly interface
- Swipe gestures for navigation
- Optimized data tables for small screens

---

## Performance Guidelines

### Asset Optimization

1. **Minification**
   ```bash
   # Minify CSS
   npm run build:css
   
   # Minify JS
   npm run build:js
   ```

2. **Concatenation**
   - Combine component CSS
   - Bundle JavaScript modules
   - Use WordPress concatenation

3. **Lazy Loading**
   ```php
   // Conditional loading
   if (is_singular('listing')) {
       wp_enqueue_script('hph-single-listing');
   }
   ```

### Database Optimization

1. **Efficient Queries**
   ```php
   // Good: Specific fields
   $query = new WP_Query(array(
       'post_type' => 'listing',
       'posts_per_page' => 10,
       'fields' => 'ids',
       'no_found_rows' => true,
   ));
   ```

2. **Transient Caching**
   ```php
   $data = get_transient('hph_cached_data');
   if (false === $data) {
       $data = expensive_operation();
       set_transient('hph_cached_data', $data, 12 * HOUR_IN_SECONDS);
   }
   ```

### Image Optimization

1. **Responsive Images**
   ```php
   // Register appropriate sizes
   add_image_size('listing-mobile', 400, 300, true);
   add_image_size('listing-tablet', 800, 600, true);
   add_image_size('listing-desktop', 1200, 900, true);
   ```

2. **Lazy Loading**
   ```html
   <img loading="lazy" src="..." alt="...">
   ```

---

## Troubleshooting

### Common Issues

#### Components Not Loading

1. **Check registration**
   ```php
   // Verify component is registered
   $loader = HPH_Template_Loader::get_instance();
   $components = $loader->get_components();
   var_dump($components);
   ```

2. **Check file paths**
   - Verify template file exists
   - Check asset file paths
   - Confirm proper naming

#### Assets Not Loading

1. **Check registration**
   ```php
   // Check if style is registered
   global $wp_styles;
   var_dump($wp_styles->registered['hph-component']);
   ```

2. **Check dependencies**
   - Verify jQuery is loaded
   - Check for JavaScript errors
   - Confirm CSS specificity

#### Bridge Functions Not Working

1. **Check plugin activation**
   ```php
   if (!HPH_Theme::is_plugin_active()) {
       error_log('Happy Place plugin not active');
   }
   ```

2. **Verify ACF fields**
   - Check field names
   - Verify field groups are synced
   - Confirm post type support

### Debug Checklist

- [ ] WP_DEBUG enabled
- [ ] Check error logs
- [ ] Browser console clear
- [ ] Query Monitor active
- [ ] Cache cleared
- [ ] Permalinks flushed
- [ ] File permissions correct
- [ ] PHP version compatible

### Getting Help

1. **Check Documentation**
   - This file
   - Inline code comments
   - WordPress Codex

2. **Debug Tools**
   - Query Monitor
   - Debug Bar
   - Browser DevTools

3. **Support Channels**
   - GitHub Issues
   - Support Forum
   - Developer Slack

---

## Appendix

### Glossary

| Term | Definition |
|------|------------|
| **Service** | A class that provides specific functionality |
| **Component** | A self-contained UI element |
| **Bridge Function** | Data access abstraction function |
| **Template Part** | Reusable template file |
| **Shortcode** | WordPress content macro |
| **Hook** | WordPress action or filter |
| **Transient** | Temporary cached data |
| **Asset** | CSS, JavaScript, or image file |

### File Template Headers

```php
<?php
/**
 * [Component/Service/Template] Name
 * 
 * Brief description of what this file does.
 * 
 * @package HappyPlaceTheme
 * @subpackage [Category]
 * @since [Version]
 * 
 * @param array $args {
 *     Optional. Arguments for the component.
 * 
 *     @type int    $id      The ID parameter.
 *     @type string $style   The style variant.
 * }
 */
```

### Coding Standards Quick Reference

```php
// Class names: PascalCase with HPH prefix
class HPH_My_Service {}

// Method names: snake_case
public function get_listing_data() {}

// Variables: snake_case
$listing_price = 500000;

// Constants: UPPERCASE
define('HPH_VERSION', '1.0.0');

// Hooks: snake_case with hph prefix
do_action('hph_before_listing');

// Functions: snake_case with hpt prefix
function hpt_get_listing_price() {}
```

### Version History

| Version | Date | Changes |
|---------|------|---------|
| 2.2.0 | 2024 | Service-oriented architecture |
| 2.1.0 | 2024 | Component system added |
| 2.0.0 | 2024 | Major refactor |
| 1.0.0 | 2023 | Initial release |

---

*This documentation is a living document and should be updated as the theme evolves.*