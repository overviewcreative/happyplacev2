# Mapbox API Integration Analysis

## 🗺️ How Mapbox API Constants Are Called in Happy Place Project

### Overview
The Mapbox integration uses a layered approach to access the API token through multiple fallback methods, ensuring flexibility and backward compatibility.

## 📍 Primary Integration File
**Location:** `wp-content/plugins/happy-place/includes/integrations/mapbox-integration.php`

### Constants Defined
```php
// Main Mapbox access token
HP_MAPBOX_ACCESS_TOKEN

// Map configuration constants
HP_MAP_CENTER_LAT
HP_MAP_CENTER_LNG
HP_MAP_DEFAULT_ZOOM
HP_MAPBOX_DEFAULT_PROVIDER
```

## 🔧 Configuration Management

### 1. **Configuration Manager Setup**
**File:** `wp-content/plugins/happy-place/includes/core/class-configuration-manager.php`

**Configuration Key:** `mapbox_access_token`
```php
'mapbox_access_token' => [
    'unified_key' => 'hp_mapbox_access_token',
    'legacy_keys' => ['hph_mapbox_api_key', 'mapbox_api_key', 'HPH_MAPBOX_TOKEN'],
    'env_key' => 'MAPBOX_ACCESS_TOKEN',
    'description' => 'Mapbox public access token for advanced map functionality',
    'type' => 'api_key',
    'required' => false,
    'category' => 'maps'
]
```

### 2. **Token Retrieval Hierarchy**
The system checks for the token in this order:

1. **Direct Function Call:** `hp_get_mapbox_token()`
2. **Plugin Function:** `hp_mapbox_available()` + `HP_MAPBOX_ACCESS_TOKEN` constant
3. **Theme Customizer:** `get_theme_mod('mapbox_api_key', '')`
4. **Environment Variable:** `MAPBOX_ACCESS_TOKEN`
5. **Legacy Keys:** Various old configuration keys for backward compatibility

## 🎯 Usage Examples Throughout Project

### 1. **Theme Template Usage**
**File:** `archive-listing-complex.php`
```php
// Get Mapbox configuration from plugin or theme
$mapbox_key = '';
if (function_exists('hp_get_mapbox_token')) {
    $mapbox_key = hp_get_mapbox_token();
} elseif (function_exists('hp_mapbox_available') && hp_mapbox_available()) {
    $mapbox_key = defined('HP_MAPBOX_ACCESS_TOKEN') ? HP_MAPBOX_ACCESS_TOKEN : '';
}

// Fallback to theme customizer
if (empty($mapbox_key)) {
    $mapbox_key = get_theme_mod('mapbox_api_key', '');
}

// Enqueue scripts if token is available
if (!empty($mapbox_key)) {
    wp_enqueue_script('mapbox-gl', 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js', [], '2.15.0', true);
    wp_enqueue_style('mapbox-gl-css', 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css', [], '2.15.0');
    
    wp_localize_script('archive-mapbox', 'hphMapboxConfig', [
        'mapbox_token' => $mapbox_key,
        'center' => $map_center,
        'zoom' => $map_zoom
    ]);
}
```

### 2. **JavaScript Usage**
**File:** `assets/js/components/listing/listing-components.js`
```javascript
// Check if Mapbox is available
} else if (typeof mapboxgl !== 'undefined') {
    // Use Mapbox GL JS for map functionality
    // Token is passed via wp_localize_script as hph_mapbox_config.access_token
}
```

### 3. **Plugin Integration Class**
**File:** `wp-content/plugins/happy-place/includes/integrations/mapbox-integration.php`

**Automatic Enqueuing:**
```php
public function maybe_enqueue_mapbox(): void {
    $access_token = $this->get_access_token();
    if (empty($access_token)) {
        return;
    }

    // Enqueue Mapbox GL JS
    wp_enqueue_script('mapbox-gl-js', 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js', [], '2.15.0', true);
    wp_enqueue_style('mapbox-gl-css', 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css', [], '2.15.0');

    // Localize Mapbox configuration
    wp_localize_script('mapbox-gl-js', 'hph_mapbox_config', [
        'access_token' => $access_token,
        'center' => $this->get_center_coordinates(),
        'default_zoom' => $this->get_default_zoom(),
        'is_default_provider' => $this->is_default_provider(),
    ]);
}
```

## 🛠️ Helper Functions Available

### Global Helper Functions (defined in mapbox-integration.php):

1. **`hp_get_mapbox_token()`** - Get the Mapbox access token
2. **`hp_get_map_center()`** - Get map center coordinates
3. **`hp_get_map_zoom()`** - Get default map zoom level
4. **`hp_is_mapbox_default()`** - Check if Mapbox is default provider
5. **`hp_mapbox_available()`** - Check if Mapbox is configured and available

### Class Methods (MapboxIntegration class):

1. **`get_access_token()`** - Get token from configuration
2. **`get_center_coordinates()`** - Get map center
3. **`get_default_zoom()`** - Get zoom level
4. **`is_default_provider()`** - Check if default provider
5. **`is_available()`** - Check if configured
6. **`test_connection()`** - Test API connectivity

## 📱 Pages Where Mapbox Auto-Loads

The integration automatically enqueues Mapbox on:

- **Listing Archives:** `is_post_type_archive('listing')`
- **Property Taxonomy Pages:** `is_tax('property_type')`, `is_tax('property_status')`, `is_tax('property_feature')`
- **Single Listings:** `is_singular('listing')` (if default provider)
- **Search Pages:** `is_search()`
- **Custom Filter:** `apply_filters('hph_should_enqueue_mapbox', false)`

## 🎛️ Admin Configuration

**File:** `wp-content/plugins/happy-place/includes/admin/class-admin-menu.php`

The admin interface provides fields for:
- **`hp_mapbox_access_token`** - Text input for API token
- **`hp_mapbox_default_map_provider`** - Checkbox for default provider setting

## 🔍 Current Implementation Status

### ✅ **What's Working:**
- Configuration management system with multiple fallbacks
- Automatic script enqueuing on relevant pages
- Helper functions for theme integration
- Admin interface for configuration
- Connection testing functionality

### ⚠️ **Configuration Required:**
- Mapbox API token needs to be set in WordPress admin
- Map center coordinates may need adjustment
- Default provider setting should be configured

### 📝 **Usage Recommendations:**

1. **For Theme Development:** Use `hp_get_mapbox_token()` function
2. **For JavaScript:** Access via `hph_mapbox_config.access_token`
3. **For Conditional Loading:** Check `hp_mapbox_available()`
4. **For Testing:** Use `MapboxIntegration::test_connection()`

## 🔧 Configuration Steps

1. Go to WordPress Admin → Happy Place → Settings
2. Find "Maps Integration" section
3. Enter your Mapbox public access token
4. Set map center coordinates if needed
5. Choose if Mapbox should be the default map provider
6. Save settings

The system will automatically handle script loading and make the token available to your JavaScript code via the `hph_mapbox_config` global object.
