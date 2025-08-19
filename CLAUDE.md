# CLAUDE.md - Happy Place WordPress Project

> **Project Reference Guide for AI Development Sessions**  
> Version: 3.1.0 | Last Updated: August 15, 2025 - Admin Interface Complete

## 🎯 Quick Start Checklist

Before starting any development session:
```bash
# 1. Check current git status
git status

# 2. Review error logs
tail -n 50 wp-content/debug.log

# 3. Verify plugin is active
wp plugin list --status=active

# 4. Check current environment
echo "Working Directory: $(pwd)"
echo "PHP Version: $(php -v | head -n 1)"
echo "WordPress Version: $(wp core version)"
```

## 📁 Project Structure Overview

```
happy-place/
├── wp-content/
│   ├── plugins/
│   │   └── happy-place/              # Main plugin (v3.0.0)
│   │       ├── happy-place.php       # Plugin entry point
│   │       ├── includes/
│   │       │   ├── core/             # Core systems
│   │       │   ├── dashboard/        # Agent dashboard
│   │       │   ├── api/              # REST & AJAX
│   │       │   ├── forms/            # Lead capture
│   │       │   ├── search/           # Property search
│   │       │   ├── integrations/     # External APIs
│   │       │   └── marketing/        # Marketing suite
│   │       ├── assets/               # CSS/JS resources
│   │       └── templates/            # PHP templates
│   └── themes/
│       └── happy-place-theme/        # Theme (v2.1.0)
│           ├── functions.php          # Theme setup
│           ├── inc/
│           │   ├── bridge/           # Bridge functions
│           │   └── assets/           # Asset management
│           └── template-parts/       # Reusable components
```

## 🔑 Critical Constants & Paths

```php
// Plugin Constants (defined in happy-place.php)
HP_VERSION           = '3.0.0'
HP_PLUGIN_DIR        = '/wp-content/plugins/happy-place/'
HP_PLUGIN_URL        = 'http://happy-place.local/wp-content/plugins/happy-place/'
HP_INCLUDES_DIR      = HP_PLUGIN_DIR . 'includes/'
HP_ASSETS_URL        = HP_PLUGIN_URL . 'assets/'
HP_DIST_URL          = HP_PLUGIN_URL . 'dist/'
HP_DEBUG             = WP_DEBUG

// Theme Constants (defined in functions.php)
HPT_VERSION          = '2.1.0'
HPT_THEME_URL        = get_template_directory_uri()
HPT_THEME_PATH       = get_template_directory()

// Database Tables
HP_TABLE_PREFIX      = 'wp_hp_'  // Custom tables prefix
```

## 🏗️ Core Architecture Components

### 1. Custom Post Types (8 Total)
```php
// All registered in includes/core/class-post-types.php
'listing'      // Property listings
'agent'        // Agent profiles
'community'    // Neighborhoods
'city'         // City pages
'open_house'   // Open house events
'local_place'  // Points of interest
'team'         // Team members
'transaction'  // Transactions
'lead'         // Lead management (NEW)
```

### 2. ACF Field Groups (JSON-based)
```
Location: /includes/fields/acf-json/
- group_listing_basic.json      // Core listing fields
- group_listing_address.json    // Address fields
- group_listing_features.json   // Property features
- group_listing_media.json      // Images & videos
- group_listing_financial.json  // Pricing & fees
- group_listing_relationships.json // Agent/office links
- group_agent_profile.json      // Agent information
- group_community.json          // Community details
- group_open_house.json         // Open house events
- group_lead.json               // Lead capture data
```

### 3. Bridge Functions Pattern

**CRITICAL RULE**: Always use bridge functions, never direct WordPress functions!

```php
// ✅ CORRECT - Using bridge functions
$listing = hpt_get_listing($listing_id);
$price = hpt_get_listing_price_formatted($listing_id);
$agent = hpt_get_listing_agent($listing_id);

// ❌ WRONG - Direct WordPress/ACF calls
$post = get_post($listing_id);           // Never use
$price = get_field('price', $listing_id); // Never use
```

#### Key Bridge Function Files:
- `theme/inc/bridge/listing-bridge.php` - Property data
- `theme/inc/bridge/agent-bridge.php` - Agent data
- `theme/inc/bridge/community-bridge.php` - Community data
- `theme/inc/bridge/open-house-bridge.php` - Events
- `theme/inc/bridge/city-bridge.php` - City pages
- `theme/inc/bridge/transaction-bridge.php` - Transactions

## 📡 API Endpoints & AJAX

### REST API Endpoints (Namespace: `happy-place/v1`)
```
GET  /wp-json/happy-place/v1/listings       - Get listings
GET  /wp-json/happy-place/v1/listings/{id}  - Get single listing
POST /wp-json/happy-place/v1/listings       - Create listing (auth required)
PUT  /wp-json/happy-place/v1/listings/{id}  - Update listing (auth required)
GET  /wp-json/happy-place/v1/agents         - Get agents
GET  /wp-json/happy-place/v1/search         - Search properties
POST /wp-json/happy-place/v1/leads          - Submit lead
```

### AJAX Actions (Dashboard)
```php
// Dashboard AJAX endpoints
'hp_dashboard_get_overview'     // Dashboard overview data
'hp_dashboard_get_listings'     // Agent's listings
'hp_dashboard_save_listing'     // Save/update listing
'hp_dashboard_get_analytics'    // Analytics data
'hp_dashboard_generate_flyer'   // Generate marketing flyer
'hp_load_marketing_suite'       // Load marketing interface
```

### AJAX Security Pattern
```php
// Always verify nonce and capabilities
public function ajax_handler() {
    // 1. Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'hp_dashboard')) {
        wp_send_json_error('Security check failed');
    }
    
    // 2. Check capabilities
    if (!current_user_can('edit_listings')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    // 3. Sanitize input
    $listing_id = intval($_POST['listing_id']);
    
    // 4. Process and return
    wp_send_json_success($data);
}
```

## 🎨 Component Template System

### Template Part Usage
```php
// Loading reusable components
get_template_part('template-parts/listing-card', null, [
    'listing_id' => $listing_id,
    'display_mode' => 'grid'
]);

// Available template parts:
'template-parts/listing-card'      // Property card display
'template-parts/agent-card'        // Agent profile card
'template-parts/advanced-search-form' // Search interface
'template-parts/newsletter-signup'  // Newsletter form
'template-parts/testimonials-carousel' // Reviews slider
```

## 🔧 Development Patterns & Rules

### 1. Data Access Rules
```php
// ALWAYS use bridge functions for data access
$listing = hpt_get_listing($id);          // ✅ Correct
$agent = hpt_get_agent_by_user($user_id); // ✅ Correct

// NEVER use direct WordPress functions
get_post($id);                            // ❌ Wrong
get_field('price', $id);                  // ❌ Wrong
get_user_meta($user_id, 'agent_id');      // ❌ Wrong
```

### 2. User Capability Checks
```php
// Custom capabilities for agents
if (current_user_can('edit_own_listings')) {
    // Agent can edit their listings
}

if (current_user_can('manage_open_houses')) {
    // Can manage open house events
}

// Available capabilities:
'edit_own_listings'
'create_listings'
'manage_open_houses'
'generate_marketing'
'view_analytics'
'manage_leads'
```

### 3. Error Handling Pattern
```php
// Use hp_log for debugging
if (HP_DEBUG) {
    hp_log('Processing listing: ' . $listing_id, 'info', 'listing-save');
}

// Standard error response
try {
    // Process request
} catch (Exception $e) {
    hp_log($e->getMessage(), 'error', 'api');
    wp_send_json_error([
        'message' => 'An error occurred',
        'code' => 'processing_failed'
    ]);
}
```

### 4. Nonce Pattern
```php
// Creating nonces
wp_nonce_field('hp_dashboard_action', 'hp_dashboard_nonce');

// Verifying nonces
if (!wp_verify_nonce($_POST['hp_dashboard_nonce'], 'hp_dashboard_action')) {
    wp_die('Security check failed');
}
```

## 💾 Database Schema

### Custom Tables
```sql
-- Lead tracking table
{$wpdb->prefix}hp_leads
- id (INT)
- listing_id (INT)
- agent_id (INT)
- name (VARCHAR)
- email (VARCHAR)
- phone (VARCHAR)
- message (TEXT)
- source (VARCHAR)
- created_at (DATETIME)

-- Property views tracking
{$wpdb->prefix}hp_property_views
- id (INT)
- listing_id (INT)
- user_id (INT NULL)
- ip_address (VARCHAR)
- viewed_at (DATETIME)
```

## 🚀 Common Development Tasks

### Creating a New Listing
```php
// Use the bridge function
$listing_id = hpt_create_listing([
    'title' => 'Beautiful Home',
    'price' => 500000,
    'bedrooms' => 3,
    'bathrooms' => 2,
    'address' => '123 Main St',
    'agent_id' => $agent_id
]);
```

### Searching Properties
```php
// Use the search bridge
$results = hpt_search_properties([
    'min_price' => 300000,
    'max_price' => 500000,
    'bedrooms' => 3,
    'city' => 'Austin',
    'property_type' => 'single-family'
]);
```

### Generating Marketing Materials
```php
// Trigger flyer generation
$flyer_url = hpt_generate_property_flyer($listing_id, [
    'template' => 'modern',
    'format' => 'pdf',
    'branding' => true
]);
```

### Handling Lead Submission
```php
// Process lead form
$lead_id = hpt_create_lead([
    'listing_id' => $listing_id,
    'name' => sanitize_text_field($_POST['name']),
    'email' => sanitize_email($_POST['email']),
    'phone' => sanitize_text_field($_POST['phone']),
    'message' => sanitize_textarea_field($_POST['message']),
    'source' => 'property_inquiry'
]);
```

## 🐛 Debugging & Testing

### Debug Locations
```bash
# Main debug log
tail -f wp-content/debug.log

# Plugin-specific log
tail -f wp-content/plugins/happy-place/debug.log

# Check PHP errors
tail -f /var/log/apache2/error.log  # Linux
tail -f /Applications/MAMP/logs/php_error.log  # MAMP
```

### Testing Endpoints
```bash
# Test REST API
curl http://happy-place.local/wp-json/happy-place/v1/listings

# Test with authentication
curl -H "X-WP-Nonce: YOUR_NONCE" \
     http://happy-place.local/wp-json/happy-place/v1/listings/123

# Test AJAX endpoint
curl -X POST http://happy-place.local/wp-admin/admin-ajax.php \
     -d "action=hp_dashboard_get_overview&nonce=YOUR_NONCE"
```

### WP-CLI Commands
```bash
# Flush rewrite rules
wp rewrite flush

# Check post type registration
wp post-type list

# Create test data
wp eval 'hpt_create_sample_listings(10);'

# Check user capabilities
wp cap list agent
```

## 📊 Performance Optimization

### Caching Strategy
```php
// Transient caching pattern
$cache_key = 'hp_listings_featured';
$data = get_transient($cache_key);

if (false === $data) {
    $data = expensive_query();
    set_transient($cache_key, $data, HOUR_IN_SECONDS);
}
```

### Query Optimization
```php
// Use WP_Query efficiently
$args = [
    'post_type' => 'listing',
    'posts_per_page' => 12,
    'meta_key' => 'price',
    'orderby' => 'meta_value_num',
    'order' => 'DESC',
    'meta_query' => [
        'relation' => 'AND',
        [
            'key' => 'listing_status',
            'value' => 'active',
            'compare' => '='
        ],
        [
            'key' => 'price',
            'value' => [300000, 500000],
            'compare' => 'BETWEEN',
            'type' => 'NUMERIC'
        ]
    ],
    'no_found_rows' => true,  // Skip pagination if not needed
    'update_post_meta_cache' => false,  // Skip if not using meta
    'update_post_term_cache' => false   // Skip if not using terms
];
```

## 🔒 Security Guidelines

### Input Sanitization
```php
// Always sanitize user input
$text = sanitize_text_field($_POST['text']);
$email = sanitize_email($_POST['email']);
$html = wp_kses_post($_POST['content']);
$url = esc_url_raw($_POST['website']);
$number = intval($_POST['number']);
```

### Output Escaping
```php
// Always escape output
echo esc_html($title);
echo esc_attr($attribute);
echo esc_url($url);
echo wp_kses_post($content);  // For HTML content
```

### SQL Queries
```php
// Use prepared statements
global $wpdb;
$results = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}hp_leads 
         WHERE agent_id = %d AND status = %s",
        $agent_id,
        'active'
    )
);
```

## 📝 Important Notes & Reminders

### Critical Rules
1. **NEVER** use direct WordPress functions - always use bridge functions
2. **NEVER** create files unless absolutely necessary
3. **ALWAYS** prefer editing existing files over creating new ones
4. **ALWAYS** check user capabilities before sensitive operations
5. **ALWAYS** verify nonces for form submissions and AJAX requests
6. **NEVER** output data without proper escaping

### Common Pitfalls to Avoid
- Don't forget to flush rewrite rules after adding new endpoints
- Don't use `get_field()` directly - use bridge functions
- Don't create admin pages without capability checks
- Don't store sensitive data in transients
- Don't forget to register scripts before enqueueing

### Build Commands
```bash
# Development build with watch
npm run dev

# Production build
npm run build

# Linting
npm run lint

# Clean build directory
npm run clean
```

### Useful Shortcuts
```php
// Get current user's agent ID
$agent_id = hpt_get_current_user_agent_id();

// Check if user is an agent
$is_agent = hpt_user_is_agent();

// Get listing by MLS number
$listing = hpt_get_listing_by_mls($mls_number);

// Format price for display
$price = hpt_format_price(500000); // Returns "$500,000"

// Get agent's active listings
$listings = hpt_get_agent_listings($agent_id, 'active');
```

## 🔄 Git Workflow

```bash
# Start new feature
git checkout -b feature/new-feature

# Commit with conventional commits
git add .
git commit -m "feat: add new search filters"
git commit -m "fix: resolve AJAX nonce issue"
git commit -m "docs: update API documentation"

# Push and create PR
git push origin feature/new-feature
```

## 📚 Additional Resources

### File References
- Main Plugin: `/wp-content/plugins/happy-place/happy-place.php`
- Dashboard Manager: `/includes/dashboard/class-dashboard-manager.php`
- REST API: `/includes/api/class-rest-api.php`
- Lead Capture: `/includes/forms/class-lead-capture.php`
- Property Search: `/includes/search/class-property-search.php`
- Marketing Generator: `/includes/marketing/class-marketing-generator.php`

### Key Classes & Namespaces
```php
HappyPlace\Core\Post_Types           // CPT registration
HappyPlace\Core\ACF_Manager          // ACF field management
HappyPlace\Dashboard\Dashboard_Manager // Agent dashboard
HappyPlace\API\REST_API              // REST endpoints
HappyPlace\API\Ajax\Dashboard_Ajax   // AJAX handlers
HappyPlace\Forms\Lead_Capture        // Lead forms
HappyPlace\Search\Property_Search    // Search system
HappyPlace\Marketing\Marketing_Generator // Marketing tools
```

### Testing URLs
- Frontend: `http://happy-place.local/`
- Dashboard: `http://happy-place.local/agent-dashboard/`
- API: `http://happy-place.local/wp-json/happy-place/v1/`
- Admin: `http://happy-place.local/wp-admin/`

---

## 🎯 Current Development Status

### ✅ Fully Implemented
- 8 custom post types with full CRUD
- ACF field structure (JSON-based)
- Agent dashboard system
- Bridge function architecture
- REST API with 12+ endpoints
- Lead capture system with 6 form types
- Advanced property search
- Marketing suite (flyer generation)
- AJAX-powered dashboard

### ⚠️ Needs Attention
- Email notification templates
- Performance optimization for large datasets
- Mobile responsive improvements
- SEO schema markup
- Analytics tracking enhancement

### 🚧 In Progress
- CRM integration connectors
- Advanced analytics dashboard
- MLS compliance validation
- Bulk import tools

---

*Last Updated: August 15, 2025 | Version 3.0.0*  
*Use this guide for all development sessions to maintain consistency*