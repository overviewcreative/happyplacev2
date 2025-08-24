Looking at your architecture outline, I can see you have a robust theme layer with dashboard components already in place. Let me review the plugin structure needed to create a production-level data management system that leverages your existing theme styling framework.

## 🏗️ **Plugin Architecture for Data Layer Services**

### **Core Services & Classes Needed**

```
happy-place/
├── includes/
│   ├── services/                    # Core data services
│   │   ├── class-listing-service.php        # CRUD operations for listings
│   │   ├── class-agent-service.php          # Agent profile management
│   │   ├── class-lead-service.php           # Lead capture & management
│   │   ├── class-import-service.php         # CSV/bulk import handling
│   │   ├── class-sync-service.php           # External sync orchestration
│   │   └── class-form-service.php           # Form processing & validation
│   │
│   ├── integrations/                # External integrations
│   │   ├── class-airtable-sync.php          # Airtable API integration
│   │   ├── class-csv-importer.php           # CSV parsing & mapping
│   │   └── class-lead-connector.php         # External lead sources
│   │
│   ├── forms/                       # Form handlers
│   │   ├── class-listing-form.php           # Listing create/edit forms
│   │   ├── class-agent-form.php             # Agent profile forms
│   │   ├── class-lead-form.php              # Lead capture forms
│   │   └── class-form-validator.php         # Validation utilities
│   │
│   ├── ajax/                        # AJAX handlers
│   │   ├── class-listing-ajax.php           # Listing AJAX operations
│   │   ├── class-upload-ajax.php            # File/image uploads
│   │   ├── class-import-ajax.php            # Import progress handling
│   │   └── class-search-ajax.php            # Autocomplete/search
│   │
│   ├── dashboard/                   # Dashboard controllers
│   │   ├── class-dashboard-router.php       # Route management
│   │   ├── class-dashboard-menu.php         # Menu registration
│   │   ├── class-dashboard-permissions.php  # Access control
│   │   └── class-dashboard-assets.php       # Asset enqueueing
│   │
│   └── utilities/                   # Helper utilities
│       ├── class-field-mapper.php           # ACF field mapping
│       ├── class-data-sanitizer.php         # Input sanitization
│       ├── class-image-processor.php        # Image optimization
│       └── class-notification-service.php   # Email/notifications
```

## 📋 **Service Class Specifications**

### **1. Listing Service (Primary CRUD)**
```php
class HPH_Listing_Service {
    // Core CRUD operations
    public function create_listing($data)
    public function update_listing($listing_id, $data)
    public function delete_listing($listing_id)
    public function duplicate_listing($listing_id)
    
    // Bulk operations
    public function bulk_update($listing_ids, $data)
    public function bulk_delete($listing_ids)
    
    // Status management
    public function update_status($listing_id, $status)
    public function archive_listing($listing_id)
    
    // Media handling
    public function attach_images($listing_id, $images)
    public function set_featured_image($listing_id, $image_id)
    
    // Field mapping
    private function map_form_to_acf($form_data)
    private function validate_required_fields($data)
}
```

### **2. Form Service (Processing & Validation)**
```php
class HPH_Form_Service {
    // Form generation
    public function render_listing_form($listing_id = null)
    public function get_form_fields($form_type)
    
    // Processing
    public function process_submission($form_data, $form_type)
    public function validate_form($data, $rules)
    
    // Field configuration
    public function get_field_config($post_type)
    public function get_validation_rules($form_type)
    
    // Security
    public function verify_nonce($nonce, $action)
    public function check_permissions($user_id, $action)
}
```

### **3. Import Service (CSV & Bulk Operations)**
```php
class HPH_Import_Service {
    // Import operations
    public function import_csv($file_path, $mapping)
    public function validate_csv($file_path)
    public function get_csv_headers($file_path)
    
    // Mapping
    public function auto_map_fields($csv_headers)
    public function save_mapping_template($name, $mapping)
    
    // Progress tracking
    public function init_import_session($total_rows)
    public function update_progress($processed, $total)
    
    // Error handling
    public function log_import_error($row, $error)
    public function get_import_report($session_id)
}
```

### **4. Sync Service (External Integrations)**
```php
class HPH_Sync_Service {
    // Sync orchestration
    public function sync_with_airtable($direction = 'both')
    public function sync_leads($source)
    
    // Scheduling
    public function schedule_sync($type, $frequency)
    public function run_scheduled_sync($type)
    
    // Conflict resolution
    public function resolve_conflicts($local, $remote)
    public function get_sync_status($sync_type)
    
    // Logging
    public function log_sync_activity($type, $result)
}
```

## 🎯 **Implementation Priority & Phases**

### **Phase 1: Core Forms & CRUD (Week 1)**
```php
// 1. Basic listing form handler
happy-place-homes/includes/forms/class-listing-form-handler.php

class HPH_Listing_Form_Handler {
    
    public function __construct() {
        add_action('wp_ajax_hph_save_listing', array($this, 'ajax_save_listing'));
        add_action('wp_ajax_nopriv_hph_save_listing', array($this, 'ajax_save_listing'));
    }
    
    public function render_form($listing_id = null) {
        $listing_data = $listing_id ? $this->get_listing_data($listing_id) : array();
        
        // Use theme's form template with data
        set_query_var('listing_data', $listing_data);
        get_template_part('template-parts/dashboard/listing-form');
    }
    
    public function ajax_save_listing() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hph_listing_form')) {
            wp_die('Security check failed');
        }
        
        // Check capabilities
        if (!current_user_can('edit_listings')) {
            wp_die('Insufficient permissions');
        }
        
        // Process form data
        $listing_id = $this->process_listing_data($_POST);
        
        wp_send_json_success(array(
            'listing_id' => $listing_id,
            'message' => 'Listing saved successfully',
            'redirect' => get_permalink($listing_id)
        ));
    }
    
    private function process_listing_data($data) {
        // Sanitize input
        $clean_data = $this->sanitize_listing_data($data);
        
        // Create/update post
        $post_data = array(
            'post_title' => $clean_data['title'],
            'post_type' => 'listing',
            'post_status' => $clean_data['status'] ?? 'draft'
        );
        
        if (!empty($clean_data['listing_id'])) {
            $post_data['ID'] = $clean_data['listing_id'];
            $listing_id = wp_update_post($post_data);
        } else {
            $listing_id = wp_insert_post($post_data);
        }
        
        // Update ACF fields
        $this->update_listing_fields($listing_id, $clean_data);
        
        return $listing_id;
    }
    
    private function update_listing_fields($listing_id, $data) {
        // Map form fields to ACF fields
        $field_mapping = array(
            'price' => 'field_price',
            'bedrooms' => 'field_bedrooms',
            'bathrooms' => 'field_bathrooms',
            'square_feet' => 'field_square_feet',
            'address' => 'field_address',
            // ... more field mappings
        );
        
        foreach ($field_mapping as $form_field => $acf_field) {
            if (isset($data[$form_field])) {
                update_field($acf_field, $data[$form_field], $listing_id);
            }
        }
    }
}
```

### **Phase 2: Dashboard Router & Menu (Week 1)**
```php
// Dashboard endpoint management
happy-place-homes/includes/dashboard/class-dashboard-router.php

class HPH_Dashboard_Router {
    
    public function __construct() {
        add_action('init', array($this, 'add_rewrite_rules'));
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('template_redirect', array($this, 'handle_dashboard_routes'));
    }
    
    public function add_rewrite_rules() {
        add_rewrite_rule(
            '^dashboard/([^/]+)/?([^/]+)?/?',
            'index.php?hph_dashboard=1&hph_section=$matches[1]&hph_action=$matches[2]',
            'top'
        );
    }
    
    public function handle_dashboard_routes() {
        if (get_query_var('hph_dashboard')) {
            // Check user is logged in
            if (!is_user_logged_in()) {
                wp_redirect(wp_login_url(home_url('/dashboard/')));
                exit;
            }
            
            // Load dashboard template from theme
            $section = get_query_var('hph_section', 'overview');
            $action = get_query_var('hph_action', 'index');
            
            // Set up dashboard data
            $this->setup_dashboard_data($section, $action);
            
            // Use theme's dashboard template
            get_template_part('template-parts/dashboard/dashboard', $section);
            exit;
        }
    }
}
```

### **Phase 3: Import/Export Handler (Week 2)**
```php
// CSV import functionality
happy-place-homes/includes/integrations/class-csv-importer.php

class HPH_CSV_Importer {
    
    public function import_listings($csv_file) {
        // Parse CSV
        $data = $this->parse_csv($csv_file);
        
        // Map fields
        $mapped_data = $this->map_csv_fields($data);
        
        // Process each row
        $results = array(
            'success' => 0,
            'failed' => 0,
            'errors' => array()
        );
        
        foreach ($mapped_data as $row) {
            try {
                $this->create_listing_from_csv($row);
                $results['success']++;
            } catch (Exception $e) {
                $results['failed']++;
                $results['errors'][] = $e->getMessage();
            }
        }
        
        return $results;
    }
    
    private function map_csv_fields($data) {
        // Default field mapping
        $mapping = array(
            'MLS Number' => 'mls_number',
            'Price' => 'price',
            'Bedrooms' => 'bedrooms',
            'Bathrooms' => 'bathrooms',
            'Square Feet' => 'square_feet',
            'Address' => 'street_address',
            'City' => 'city',
            'State' => 'state',
            'ZIP' => 'zip_code'
        );
        
        return $this->apply_mapping($data, $mapping);
    }
}
```

## 🔗 **Connection Points to Theme Dashboard**

### **1. Template Integration Points**
```php
// Theme template: template-parts/dashboard/dashboard-listings.php
<?php
// Get listing service from plugin
$listing_service = HPH_Plugin::get_service('listing');
$listings = $listing_service->get_user_listings(get_current_user_id());

// Use theme's existing styling
?>
<div class="hph-dashboard-listings">
    <div class="hph-dashboard-header">
        <h2>My Listings</h2>
        <button class="hph-btn hph-btn-primary" data-action="create-listing">
            Add New Listing
        </button>
    </div>
    
    <div class="hph-listing-grid">
        <?php foreach ($listings as $listing) : ?>
            <?php 
            // Use existing listing card component
            get_template_part('template-parts/components/listing-card', null, array(
                'listing_id' => $listing->ID,
                'show_actions' => true
            )); 
            ?>
        <?php endforeach; ?>
    </div>
</div>
```

### **2. AJAX Integration**
```javascript
// Theme JS: assets/js/dashboard/dashboard-listings.js
(function($) {
    'use strict';
    
    const DashboardListings = {
        init() {
            this.bindEvents();
        },
        
        bindEvents() {
            $(document).on('click', '[data-action="save-listing"]', this.saveListing);
            $(document).on('click', '[data-action="delete-listing"]', this.deleteListing);
        },
        
        saveListing(e) {
            e.preventDefault();
            
            const formData = new FormData($('#listing-form')[0]);
            formData.append('action', 'hph_save_listing');
            formData.append('nonce', HPH.nonce);
            
            $.ajax({
                url: HPH.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    if (response.success) {
                        HPH.showNotification('Listing saved successfully', 'success');
                        if (response.data.redirect) {
                            window.location.href = response.data.redirect;
                        }
                    }
                }
            });
        }
    };
    
    $(document).ready(() => DashboardListings.init());
})(jQuery);
```

## 📊 **Database Considerations**

Since you mentioned existing tables, ensure the plugin services interact with:
- `wp_posts` (listings, agents as CPTs)
- `wp_postmeta` (ACF field storage)
- Custom tables if any (leads, sync logs, import history)

## 🚀 **Next Steps**

1. **Start with the Form Service** - Get basic create/edit working
2. **Add Dashboard Router** - Set up front-end routing
3. **Implement AJAX handlers** - Enable smooth interactions
4. **Add Import Service** - CSV functionality
5. **Integrate Airtable Sync** - External data synchronization

This architecture keeps data operations in the plugin while leveraging your robust theme styling framework. The services are modular and can be developed incrementally, starting with simple forms and expanding to more complex features.