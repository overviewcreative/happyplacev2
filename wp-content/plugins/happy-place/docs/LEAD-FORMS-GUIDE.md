# 📋 Happy Place Lead Forms Configuration Guide

## 🎯 Overview
The Happy Place lead forms system provides multiple ways to capture leads across your real estate website with automatic scoring, notifications, and management.

## 📊 Admin Menu Structure (Cleaned Up)
After cleanup, your admin menu now has:
- **Leads** (top-level menu) - Complete lead management
- **Happy Place Dashboard** - Main plugin dashboard
- **Analytics, Import/Export, Tools, etc.** - Under Happy Place submenu

## 🔧 Lead Form Configuration

### 1. Basic Settings
Access lead settings at: **wp-admin → Leads → Settings**

### 2. Notification Setup
Configure who receives lead notifications:
```php
// In wp-config.php or functions.php
update_option('hp_lead_notification_emails', 'leads@youragency.com,manager@youragency.com');
```

### 3. Lead Scoring Weights
Customize lead scoring factors:
```php
add_filter('hp_lead_score_factors', function($factors) {
    return [
        'has_phone' => 25,      // Has phone number
        'detailed_message' => 20, // Message over 100 chars
        'property_inquiry' => 30,  // Specific property interest
        'utm_tracking' => 10,      // From marketing campaign
        'business_hours' => 15     // Submitted during business hours
    ];
});
```

## 📝 Form Shortcode Usage

### Basic Lead Form
```php
[hp_lead_form title="Get In Touch" button_text="Send Message"]
```

### Contact Page Form
```php
[hp_contact_form title="Contact Our Team"]
```

### Property Inquiry Form
```php
[hp_property_inquiry title="Inquire About This Property" listing_id="123"]
```

### Advanced Configuration
```php
[hp_lead_form 
    title="Schedule a Viewing"
    button_text="Request Showing"
    show_phone="true"
    show_message="true"
    source="property_page"
    class="custom-lead-form"
    agent_id="5"
]
```

## 🏠 Frontend Template Integration

### Method 1: In Template Files
Add to your theme's template files (e.g., `single-listing.php`, `front-page.php`):

```php
<?php
// Basic lead form
echo do_shortcode('[hp_lead_form title="Interested in this property?"]');

// Property-specific inquiry
if (is_singular('listing')) {
    global $post;
    echo do_shortcode('[hp_property_inquiry listing_id="' . $post->ID . '"]');
}

// Agent-specific form
$listing_agent = get_post_meta(get_the_ID(), 'listing_agent', true);
if ($listing_agent) {
    echo do_shortcode('[hp_lead_form agent_id="' . $listing_agent . '" title="Contact Listing Agent"]');
}
?>
```

### Method 2: Using the Theme Bridge System
In your theme templates, use the existing bridge functions:

```php
<?php
// In your theme template file (e.g., single-listing.php)

// Get listing details using bridge functions
$listing_id = get_the_ID();
$listing_agent = hpt_get_listing_agent($listing_id);

// Display property inquiry form
?>
<div class="property-inquiry-section">
    <h3>Interested in this property?</h3>
    <?php echo do_shortcode('[hp_property_inquiry listing_id="' . $listing_id . '" agent_id="' . $listing_agent['id'] . '"]'); ?>
</div>
```

### Method 3: Adding to Specific Theme Locations

#### A. In the Front Page Template
Edit `wp-content/themes/happy-place-theme/front-page.php`:

```php
// Add somewhere in the template
<section class="lead-capture-section">
    <div class="container">
        <h2>Ready to Find Your Dream Home?</h2>
        <?php echo do_shortcode('[hp_lead_form title="Start Your Search" source="homepage"]'); ?>
    </div>
</section>
```

#### B. In Property Listing Pages
Edit `wp-content/themes/happy-place-theme/single-listing.php`:

```php
// Add near the end of the listing content
<div class="listing-inquiry-form">
    <?php 
    echo do_shortcode('[hp_property_inquiry]'); // Auto-detects current listing
    ?>
</div>
```

#### C. In the Footer
Edit `wp-content/themes/happy-place-theme/footer.php`:

```php
// Before closing body tag
<div class="footer-lead-form">
    <?php echo do_shortcode('[hp_lead_form title="Quick Question?" show_message="false" class="footer-form"]'); ?>
</div>
```

### Method 4: Widget Areas
Add lead forms to any widget area via **Appearance → Widgets**:

1. Add a **Text Widget** 
2. Insert shortcode: `[hp_lead_form title="Contact Us"]`

### Method 5: Floating Action Form
Add a persistent floating form to all pages:

```php
// Add to your theme's functions.php or footer.php
add_action('wp_footer', function() {
    if (!is_admin()) {
        ?>
        <div class="hp-floating-lead-form">
            <div class="hp-floating-trigger" onclick="toggleFloatingForm()">
                💬
            </div>
            <div class="hp-floating-form-content" id="floating-form">
                <?php echo do_shortcode('[hp_lead_form title="Quick Question?" show_phone="false" class="floating-form"]'); ?>
            </div>
        </div>
        
        <script>
        function toggleFloatingForm() {
            document.getElementById('floating-form').classList.toggle('active');
        }
        </script>
        <?php
    }
});
```

## 🎨 Custom Styling

### Method 1: Theme Customizer
Add CSS via **Appearance → Customize → Additional CSS**:

```css
/* Custom lead form styling */
.hp-lead-form-wrapper {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 40px;
    border-radius: 15px;
}

.hp-lead-form-title {
    color: white;
    text-align: center;
}

.hp-lead-form label {
    color: rgba(255, 255, 255, 0.9);
}

.hp-btn-primary {
    background: #ff6b6b;
    padding: 15px 40px;
    font-size: 18px;
}

.hp-btn-primary:hover {
    background: #ff5252;
    transform: translateY(-2px);
}
```

### Method 2: Theme Styles
Add to your theme's `style.css`:

```css
/* Property inquiry specific styling */
.hp-property-inquiry-form .hp-lead-form-wrapper {
    border: 3px solid #0073aa;
    background: #f8f9fa;
}

/* Footer form styling */
.footer-form .hp-lead-form-wrapper {
    padding: 20px;
    background: #2c3e50;
}

.footer-form .hp-lead-form-title {
    color: #ecf0f1;
}
```

## 📱 Responsive Customization

```css
/* Mobile-specific adjustments */
@media (max-width: 768px) {
    .hp-floating-lead-form {
        bottom: 10px;
        right: 10px;
    }
    
    .hp-floating-form-content {
        width: calc(100vw - 40px);
        right: -10px;
    }
    
    .hp-lead-form-wrapper {
        padding: 20px;
    }
}
```

## 🔧 Advanced Customization

### Custom Field Addition
```php
add_filter('hp_form_fields_lead', function($fields) {
    // Add budget field
    $fields['budget'] = [
        'label' => 'Budget Range',
        'type' => 'select',
        'choices' => [
            'under_300k' => 'Under $300,000',
            '300k_500k' => '$300,000 - $500,000',
            '500k_800k' => '$500,000 - $800,000',
            'over_800k' => 'Over $800,000'
        ]
    ];
    
    return $fields;
});
```

### Custom Validation Rules
```php
add_filter('hp_validation_rules_lead', function($rules) {
    $rules['phone'] = [
        'required' => true,
        'pattern' => '/^\(\d{3}\) \d{3}-\d{4}$/'
    ];
    
    return $rules;
});
```

### Integration Hooks
```php
// When a lead is created
add_action('hp_lead_created', function($lead_id, $lead_data) {
    // Send to CRM
    // Add to mailing list  
    // Track in analytics
    // Custom notifications
});

// When lead status changes
add_action('hp_lead_status_updated', function($lead_id, $status) {
    // Update CRM
    // Send status email
});
```

## 📊 Lead Management

### Accessing Leads
- **Admin URL**: `wp-admin/admin.php?page=hp-leads`
- **Lead Statuses**: New, Contacted, Qualified, Proposal, Negotiation, Closed Won, Closed Lost
- **Lead Scoring**: Automatic 0-100 scoring based on lead quality factors
- **Export**: CSV export available
- **Notes System**: Add timestamped notes to each lead

### Lead Data Structure
Each lead captures:
- Contact info (name, email, phone)
- Source tracking (UTM parameters, referrer, page)
- Property interest (specific listings)
- Agent assignment
- Lead scoring and priority
- IP address and user agent
- Timestamps and notes

Now your lead forms are cleaned up and ready to capture high-quality leads across your real estate website!