<?php
/**
 * Lead Forms Test Page
 * 
 * This page demonstrates the available lead capture forms
 */

// Load WordPress
$wp_load_path = '../../../wp-load.php';
if (file_exists($wp_load_path)) {
    require_once $wp_load_path;
} else {
    die('WordPress not found. Please check the path.');
}

// Security check
if (!defined('WP_DEBUG') || !WP_DEBUG) {
    die('Lead forms test is only available in debug mode.');
}

get_header();
?>

<div class="wrap" style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    <h1>Happy Place Lead Forms - Testing Interface</h1>
    
    <p>This page demonstrates the available lead capture forms and shortcodes provided by the Happy Place plugin.</p>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 30px; margin-top: 30px;">
        
        <!-- Property Inquiry Form -->
        <div style="border: 1px solid #ddd; padding: 20px; border-radius: 8px;">
            <h2>Property Inquiry Form</h2>
            <p><strong>Shortcode:</strong> <code>[hp_property_inquiry]</code></p>
            <p>Use this form for general property inquiries. Can be linked to specific listings.</p>
            
            <div style="background: #f9f9f9; padding: 15px; border-radius: 4px; margin: 15px 0;">
                <?php echo do_shortcode('[hp_property_inquiry]'); ?>
            </div>
        </div>
        
        <!-- Contact Agent Form -->
        <div style="border: 1px solid #ddd; padding: 20px; border-radius: 8px;">
            <h2>Contact Agent Form</h2>
            <p><strong>Shortcode:</strong> <code>[hp_contact_agent]</code></p>
            <p>Direct contact form for reaching specific agents.</p>
            
            <div style="background: #f9f9f9; padding: 15px; border-radius: 4px; margin: 15px 0;">
                <?php echo do_shortcode('[hp_contact_agent]'); ?>
            </div>
        </div>
        
        <!-- Schedule Showing Form -->
        <div style="border: 1px solid #ddd; padding: 20px; border-radius: 8px;">
            <h2>Schedule Showing Form</h2>
            <p><strong>Shortcode:</strong> <code>[hp_schedule_showing]</code></p>
            <p>Form to schedule property showings with date/time selection.</p>
            
            <div style="background: #f9f9f9; padding: 15px; border-radius: 4px; margin: 15px 0;">
                <?php echo do_shortcode('[hp_schedule_showing]'); ?>
            </div>
        </div>
        
        <!-- General Lead Form -->
        <div style="border: 1px solid #ddd; padding: 20px; border-radius: 8px;">
            <h2>General Lead Form</h2>
            <p><strong>Shortcode:</strong> <code>[hp_lead_form type="general_contact"]</code></p>
            <p>Customizable lead form for various purposes.</p>
            
            <div style="background: #f9f9f9; padding: 15px; border-radius: 4px; margin: 15px 0;">
                <?php echo do_shortcode('[hp_lead_form type="general_contact"]'); ?>
            </div>
        </div>
        
        <!-- Newsletter Signup -->
        <div style="border: 1px solid #ddd; padding: 20px; border-radius: 8px;">
            <h2>Newsletter Signup</h2>
            <p><strong>Shortcode:</strong> <code>[hp_lead_form type="newsletter_signup"]</code></p>
            <p>Simple email collection for newsletters.</p>
            
            <div style="background: #f9f9f9; padding: 15px; border-radius: 4px; margin: 15px 0;">
                <?php echo do_shortcode('[hp_lead_form type="newsletter_signup"]'); ?>
            </div>
        </div>
        
        <!-- Home Valuation -->
        <div style="border: 1px solid #ddd; padding: 20px; border-radius: 8px;">
            <h2>Home Valuation Request</h2>
            <p><strong>Shortcode:</strong> <code>[hp_lead_form type="home_valuation"]</code></p>
            <p>Form for requesting free home valuations.</p>
            
            <div style="background: #f9f9f9; padding: 15px; border-radius: 4px; margin: 15px 0;">
                <?php echo do_shortcode('[hp_lead_form type="home_valuation"]'); ?>
            </div>
        </div>
    </div>
    
    <div style="margin-top: 40px; padding: 20px; background: #f0f8ff; border-radius: 8px;">
        <h2>Usage Instructions</h2>
        
        <h3>Available Shortcodes:</h3>
        <ul>
            <li><code>[hp_property_inquiry]</code> - Property inquiry form</li>
            <li><code>[hp_contact_agent]</code> - Contact agent form</li>
            <li><code>[hp_schedule_showing]</code> - Schedule showing form</li>
            <li><code>[hp_lead_form type="general_contact"]</code> - General contact form</li>
            <li><code>[hp_lead_form type="newsletter_signup"]</code> - Newsletter signup</li>
            <li><code>[hp_lead_form type="home_valuation"]</code> - Home valuation request</li>
        </ul>
        
        <h3>Shortcode Parameters:</h3>
        <ul>
            <li><code>listing_id="123"</code> - Pre-fill with specific listing</li>
            <li><code>agent_id="456"</code> - Pre-assign to specific agent</li>
            <li><code>title="Custom Form Title"</code> - Custom form title</li>
            <li><code>button_text="Submit Inquiry"</code> - Custom submit button text</li>
        </ul>
        
        <h3>Features:</h3>
        <ul>
            <li>✅ AJAX form submission</li>
            <li>✅ Lead storage in WordPress admin</li>
            <li>✅ Email notifications to agents</li>
            <li>✅ Anti-spam protection</li>
            <li>✅ Mobile-responsive design</li>
            <li>✅ Form validation and error handling</li>
        </ul>
    </div>
    
    <div style="margin-top: 20px; text-align: center;">
        <p><a href="<?php echo admin_url('edit.php?post_type=lead'); ?>">View All Leads in Admin</a> | 
        <a href="<?php echo admin_url('admin.php?page=happy-place'); ?>">Happy Place Dashboard</a></p>
    </div>
</div>

<style>
    .hp-lead-form {
        background: white;
        border: 1px solid #e1e1e1;
        border-radius: 6px;
        padding: 20px;
        margin: 10px 0;
    }
    
    .hp-form-field {
        margin-bottom: 15px;
    }
    
    .hp-form-field label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        color: #333;
    }
    
    .hp-form-field input,
    .hp-form-field textarea,
    .hp-form-field select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .hp-form-field textarea {
        height: 100px;
        resize: vertical;
    }
    
    .hp-form-submit {
        text-align: center;
        margin-top: 20px;
    }
    
    .hp-submit-button {
        background: #0073aa;
        color: white;
        padding: 12px 24px;
        border: none;
        border-radius: 4px;
        font-size: 16px;
        cursor: pointer;
        transition: background 0.3s;
    }
    
    .hp-submit-button:hover {
        background: #005a87;
    }
    
    .hp-form-message {
        padding: 10px 15px;
        border-radius: 4px;
        margin: 15px 0;
    }
    
    .hp-form-success {
        background: #d1e7dd;
        color: #0a3622;
        border: 1px solid #a3cfbb;
    }
    
    .hp-form-error {
        background: #f8d7da;
        color: #58151c;
        border: 1px solid #f0a3a8;
    }
</style>

<?php get_footer(); ?>