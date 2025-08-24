<?php
/**
 * Lead Capture System
 * 
 * Handles lead generation forms, validation, storage, and notifications
 * Provides shortcodes and widgets for property inquiry forms
 *
 * @package HappyPlace\Forms
 */

namespace HappyPlace\Forms;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class LeadCapture {
    
    /**
     * Single instance
     */
    private static $instance = null;
    
    /**
     * Form configurations
     */
    private array $form_types = [
        'property_inquiry' => [
            'title' => 'Property Inquiry',
            'fields' => ['name', 'email', 'phone', 'message', 'listing_id'],
            'required' => ['name', 'email', 'message'],
        ],
        'contact_agent' => [
            'title' => 'Contact Agent',
            'fields' => ['name', 'email', 'phone', 'message', 'agent_id', 'preferred_contact'],
            'required' => ['name', 'email'],
        ],
        'schedule_showing' => [
            'title' => 'Schedule a Showing',
            'fields' => ['name', 'email', 'phone', 'preferred_date', 'preferred_time', 'listing_id', 'message'],
            'required' => ['name', 'email', 'phone', 'preferred_date'],
        ],
        'newsletter_signup' => [
            'title' => 'Newsletter Signup',
            'fields' => ['name', 'email', 'interests'],
            'required' => ['email'],
        ],
        'home_valuation' => [
            'title' => 'Free Home Valuation',
            'fields' => ['name', 'email', 'phone', 'address', 'property_type', 'bedrooms', 'bathrooms', 'square_feet', 'message'],
            'required' => ['name', 'email', 'address'],
        ],
        'general_contact' => [
            'title' => 'Contact Us',
            'fields' => ['name', 'email', 'phone', 'subject', 'message'],
            'required' => ['name', 'email', 'message'],
        ],
    ];
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Initialize the lead capture system
     */
    public function init() {
        // Register shortcodes
        add_shortcode('hp_lead_form', [$this, 'render_lead_form_shortcode']);
        add_shortcode('hp_property_inquiry', [$this, 'render_property_inquiry_shortcode']);
        add_shortcode('hp_contact_agent', [$this, 'render_contact_agent_shortcode']);
        add_shortcode('hp_schedule_showing', [$this, 'render_schedule_showing_shortcode']);
        
        // Register AJAX handlers
        add_action('wp_ajax_hp_submit_lead', [$this, 'handle_ajax_submission']);
        add_action('wp_ajax_nopriv_hp_submit_lead', [$this, 'handle_ajax_submission']);
        
        // Register lead post type
        add_action('init', [$this, 'register_lead_post_type']);
        
        // Add form assets
        add_action('wp_enqueue_scripts', [$this, 'enqueue_form_assets']);
        
        // Email hooks
        add_action('hp_new_lead_submitted', [$this, 'send_lead_notifications'], 10, 2);
        
        hp_log('Lead Capture system initialized', 'info', 'LEAD_CAPTURE');
    }
    
    /**
     * Register lead post type
     */
    public function register_lead_post_type() {
        $args = [
            'label' => 'Leads',
            'labels' => [
                'name' => 'Leads',
                'singular_name' => 'Lead',
                'add_new' => 'Add New Lead',
                'add_new_item' => 'Add New Lead',
                'edit_item' => 'Edit Lead',
                'new_item' => 'New Lead',
                'view_item' => 'View Lead',
                'search_items' => 'Search Leads',
                'not_found' => 'No leads found',
                'not_found_in_trash' => 'No leads found in trash',
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'happy-place',
            'capability_type' => 'post',
            'capabilities' => [
                'create_posts' => 'do_not_allow',
            ],
            'map_meta_cap' => true,
            'supports' => ['title', 'custom-fields'],
            'has_archive' => false,
            'rewrite' => false,
            'show_in_rest' => false,
        ];
        
        register_post_type('lead', $args);
    }
    
    /**
     * Enqueue form assets
     */
    public function enqueue_form_assets() {
        if (!$this->should_load_assets()) {
            return;
        }
        
        // Enqueue styles
        wp_enqueue_style(
            'hp-lead-forms',
            HP_PLUGIN_URL . 'assets/css/lead-forms.css',
            [],
            HP_VERSION
        );
        
        // Enqueue scripts
        wp_enqueue_script(
            'hp-lead-forms',
            HP_PLUGIN_URL . 'assets/js/lead-forms.js',
            ['jquery'],
            HP_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('hp-lead-forms', 'hp_lead_forms', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hp_lead_form_nonce'),
            'messages' => [
                'success' => __('Thank you! Your message has been sent successfully.', 'happy-place'),
                'error' => __('There was an error sending your message. Please try again.', 'happy-place'),
                'validation' => __('Please fill in all required fields.', 'happy-place'),
                'email_invalid' => __('Please enter a valid email address.', 'happy-place'),
                'phone_invalid' => __('Please enter a valid phone number.', 'happy-place'),
            ],
        ]);
    }
    
    /**
     * Check if assets should be loaded
     */
    private function should_load_assets() {
        // Load on single listing pages
        if (is_singular('listing')) {
            return true;
        }
        
        // Load on agent pages
        if (is_singular('agent') || is_post_type_archive('agent')) {
            return true;
        }
        
        // Check for shortcodes in content
        global $post;
        if ($post && has_shortcode($post->post_content, 'hp_lead_form')) {
            return true;
        }
        if ($post && has_shortcode($post->post_content, 'hp_property_inquiry')) {
            return true;
        }
        if ($post && has_shortcode($post->post_content, 'hp_contact_agent')) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Render lead form shortcode
     */
    public function render_lead_form_shortcode($atts) {
        $atts = shortcode_atts([
            'type' => 'general_contact',
            'listing_id' => 0,
            'agent_id' => 0,
            'title' => '',
            'button_text' => 'Send Message',
            'success_message' => '',
            'class' => '',
        ], $atts);
        
        return $this->render_form($atts['type'], $atts);
    }
    
    /**
     * Render property inquiry shortcode
     */
    public function render_property_inquiry_shortcode($atts) {
        $atts = shortcode_atts([
            'listing_id' => get_the_ID(),
            'title' => 'Inquire About This Property',
            'button_text' => 'Send Inquiry',
        ], $atts);
        
        $atts['type'] = 'property_inquiry';
        return $this->render_form('property_inquiry', $atts);
    }
    
    /**
     * Render contact agent shortcode
     */
    public function render_contact_agent_shortcode($atts) {
        $atts = shortcode_atts([
            'agent_id' => 0,
            'title' => 'Contact Agent',
            'button_text' => 'Send Message',
        ], $atts);
        
        // Auto-detect agent ID if on agent page
        if (!$atts['agent_id'] && is_singular('agent')) {
            $atts['agent_id'] = get_the_ID();
        }
        
        $atts['type'] = 'contact_agent';
        return $this->render_form('contact_agent', $atts);
    }
    
    /**
     * Render schedule showing shortcode
     */
    public function render_schedule_showing_shortcode($atts) {
        $atts = shortcode_atts([
            'listing_id' => get_the_ID(),
            'title' => 'Schedule a Showing',
            'button_text' => 'Request Showing',
        ], $atts);
        
        $atts['type'] = 'schedule_showing';
        return $this->render_form('schedule_showing', $atts);
    }
    
    /**
     * Render form HTML
     */
    private function render_form($type, $atts = []) {
        if (!isset($this->form_types[$type])) {
            return '';
        }
        
        $form_config = $this->form_types[$type];
        $form_id = 'hp-lead-form-' . wp_generate_uuid4();
        
        ob_start();
        ?>
        <div class="hp-lead-form-wrapper <?php echo esc_attr($atts['class'] ?? ''); ?>">
            <form id="<?php echo esc_attr($form_id); ?>" class="hp-lead-form" data-form-type="<?php echo esc_attr($type); ?>">
                <?php if (!empty($atts['title'])): ?>
                    <h3 class="hp-form-title"><?php echo esc_html($atts['title']); ?></h3>
                <?php endif; ?>
                
                <div class="hp-form-messages"></div>
                
                <?php foreach ($form_config['fields'] as $field): ?>
                    <?php $this->render_field($field, $form_config['required'], $atts); ?>
                <?php endforeach; ?>
                
                <!-- Hidden fields -->
                <input type="hidden" name="form_type" value="<?php echo esc_attr($type); ?>">
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('hp_lead_form_nonce'); ?>">
                
                <?php if (!empty($atts['listing_id'])): ?>
                    <input type="hidden" name="listing_id" value="<?php echo esc_attr($atts['listing_id']); ?>">
                <?php endif; ?>
                
                <?php if (!empty($atts['agent_id'])): ?>
                    <input type="hidden" name="agent_id" value="<?php echo esc_attr($atts['agent_id']); ?>">
                <?php endif; ?>
                
                <div class="hp-form-submit">
                    <button type="submit" class="hp-btn hp-btn-primary">
                        <span class="hp-btn-text"><?php echo esc_html($atts['button_text'] ?? 'Submit'); ?></span>
                        <span class="hp-btn-loading" style="display: none;">
                            <span class="hp-spinner"></span> Sending...
                        </span>
                    </button>
                </div>
                
                <div class="hp-form-privacy">
                    <small><?php _e('Your information is secure and will never be shared with third parties.', 'happy-place'); ?></small>
                </div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render individual form field
     */
    private function render_field($field, $required_fields, $atts) {
        $is_required = in_array($field, $required_fields);
        $field_id = 'hp-field-' . $field . '-' . wp_rand();
        
        ?>
        <div class="hp-form-group hp-field-<?php echo esc_attr($field); ?>">
            <?php
            switch ($field) {
                case 'name':
                    ?>
                    <label for="<?php echo esc_attr($field_id); ?>">
                        <?php _e('Your Name', 'happy-place'); ?>
                        <?php if ($is_required): ?><span class="required">*</span><?php endif; ?>
                    </label>
                    <input type="text" id="<?php echo esc_attr($field_id); ?>" name="name" 
                           class="hp-form-control" <?php echo $is_required ? 'required' : ''; ?>>
                    <?php
                    break;
                    
                case 'email':
                    ?>
                    <label for="<?php echo esc_attr($field_id); ?>">
                        <?php _e('Email Address', 'happy-place'); ?>
                        <?php if ($is_required): ?><span class="required">*</span><?php endif; ?>
                    </label>
                    <input type="email" id="<?php echo esc_attr($field_id); ?>" name="email" 
                           class="hp-form-control" <?php echo $is_required ? 'required' : ''; ?>>
                    <?php
                    break;
                    
                case 'phone':
                    ?>
                    <label for="<?php echo esc_attr($field_id); ?>">
                        <?php _e('Phone Number', 'happy-place'); ?>
                        <?php if ($is_required): ?><span class="required">*</span><?php endif; ?>
                    </label>
                    <input type="tel" id="<?php echo esc_attr($field_id); ?>" name="phone" 
                           class="hp-form-control" placeholder="(555) 123-4567"
                           <?php echo $is_required ? 'required' : ''; ?>>
                    <?php
                    break;
                    
                case 'message':
                    ?>
                    <label for="<?php echo esc_attr($field_id); ?>">
                        <?php _e('Message', 'happy-place'); ?>
                        <?php if ($is_required): ?><span class="required">*</span><?php endif; ?>
                    </label>
                    <textarea id="<?php echo esc_attr($field_id); ?>" name="message" 
                              class="hp-form-control" rows="4"
                              <?php echo $is_required ? 'required' : ''; ?>></textarea>
                    <?php
                    break;
                    
                case 'preferred_date':
                    ?>
                    <label for="<?php echo esc_attr($field_id); ?>">
                        <?php _e('Preferred Date', 'happy-place'); ?>
                        <?php if ($is_required): ?><span class="required">*</span><?php endif; ?>
                    </label>
                    <input type="date" id="<?php echo esc_attr($field_id); ?>" name="preferred_date" 
                           class="hp-form-control" min="<?php echo date('Y-m-d'); ?>"
                           <?php echo $is_required ? 'required' : ''; ?>>
                    <?php
                    break;
                    
                case 'preferred_time':
                    ?>
                    <label for="<?php echo esc_attr($field_id); ?>">
                        <?php _e('Preferred Time', 'happy-place'); ?>
                    </label>
                    <select id="<?php echo esc_attr($field_id); ?>" name="preferred_time" class="hp-form-control">
                        <option value="">Select a time</option>
                        <option value="morning">Morning (9am - 12pm)</option>
                        <option value="afternoon">Afternoon (12pm - 5pm)</option>
                        <option value="evening">Evening (5pm - 8pm)</option>
                    </select>
                    <?php
                    break;
                    
                case 'preferred_contact':
                    ?>
                    <label for="<?php echo esc_attr($field_id); ?>">
                        <?php _e('Preferred Contact Method', 'happy-place'); ?>
                    </label>
                    <select id="<?php echo esc_attr($field_id); ?>" name="preferred_contact" class="hp-form-control">
                        <option value="email">Email</option>
                        <option value="phone">Phone</option>
                        <option value="text">Text Message</option>
                    </select>
                    <?php
                    break;
                    
                case 'subject':
                    ?>
                    <label for="<?php echo esc_attr($field_id); ?>">
                        <?php _e('Subject', 'happy-place'); ?>
                    </label>
                    <input type="text" id="<?php echo esc_attr($field_id); ?>" name="subject" 
                           class="hp-form-control">
                    <?php
                    break;
                    
                case 'address':
                    ?>
                    <label for="<?php echo esc_attr($field_id); ?>">
                        <?php _e('Property Address', 'happy-place'); ?>
                        <?php if ($is_required): ?><span class="required">*</span><?php endif; ?>
                    </label>
                    <input type="text" id="<?php echo esc_attr($field_id); ?>" name="address" 
                           class="hp-form-control" <?php echo $is_required ? 'required' : ''; ?>>
                    <?php
                    break;
                    
                case 'property_type':
                    ?>
                    <label for="<?php echo esc_attr($field_id); ?>">
                        <?php _e('Property Type', 'happy-place'); ?>
                    </label>
                    <select id="<?php echo esc_attr($field_id); ?>" name="property_type" class="hp-form-control">
                        <option value="">Select type</option>
                        <option value="single_family">Single Family Home</option>
                        <option value="condo">Condo</option>
                        <option value="townhouse">Townhouse</option>
                        <option value="multi_family">Multi-Family</option>
                        <option value="land">Land</option>
                        <option value="commercial">Commercial</option>
                    </select>
                    <?php
                    break;
                    
                case 'bedrooms':
                    ?>
                    <label for="<?php echo esc_attr($field_id); ?>">
                        <?php _e('Bedrooms', 'happy-place'); ?>
                    </label>
                    <select id="<?php echo esc_attr($field_id); ?>" name="bedrooms" class="hp-form-control">
                        <option value="">Select</option>
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?>+</option>
                        <?php endfor; ?>
                    </select>
                    <?php
                    break;
                    
                case 'bathrooms':
                    ?>
                    <label for="<?php echo esc_attr($field_id); ?>">
                        <?php _e('Bathrooms', 'happy-place'); ?>
                    </label>
                    <select id="<?php echo esc_attr($field_id); ?>" name="bathrooms" class="hp-form-control">
                        <option value="">Select</option>
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?>+</option>
                        <?php endfor; ?>
                    </select>
                    <?php
                    break;
                    
                case 'square_feet':
                    ?>
                    <label for="<?php echo esc_attr($field_id); ?>">
                        <?php _e('Approximate Square Feet', 'happy-place'); ?>
                    </label>
                    <input type="number" id="<?php echo esc_attr($field_id); ?>" name="square_feet" 
                           class="hp-form-control" min="0">
                    <?php
                    break;
                    
                case 'interests':
                    ?>
                    <label><?php _e('I\'m interested in:', 'happy-place'); ?></label>
                    <div class="hp-checkbox-group">
                        <label class="hp-checkbox">
                            <input type="checkbox" name="interests[]" value="buying">
                            <span><?php _e('Buying', 'happy-place'); ?></span>
                        </label>
                        <label class="hp-checkbox">
                            <input type="checkbox" name="interests[]" value="selling">
                            <span><?php _e('Selling', 'happy-place'); ?></span>
                        </label>
                        <label class="hp-checkbox">
                            <input type="checkbox" name="interests[]" value="renting">
                            <span><?php _e('Renting', 'happy-place'); ?></span>
                        </label>
                        <label class="hp-checkbox">
                            <input type="checkbox" name="interests[]" value="investing">
                            <span><?php _e('Investing', 'happy-place'); ?></span>
                        </label>
                    </div>
                    <?php
                    break;
            }
            ?>
        </div>
        <?php
    }
    
    /**
     * Handle AJAX form submission
     */
    public function handle_ajax_submission() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hp_lead_form_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        // Get form type
        $form_type = sanitize_text_field($_POST['form_type'] ?? 'general_contact');
        
        // Validate form data
        $validation = $this->validate_form_data($_POST, $form_type);
        if (!$validation['valid']) {
            wp_send_json_error(['message' => $validation['message'], 'errors' => $validation['errors']]);
            return;
        }
        
        // Sanitize data
        $lead_data = $this->sanitize_form_data($_POST);
        
        // Create lead post
        $lead_id = $this->create_lead($lead_data, $form_type);
        
        if (!$lead_id) {
            wp_send_json_error(['message' => 'Failed to save lead. Please try again.']);
            return;
        }
        
        // Trigger notifications
        do_action('hp_new_lead_submitted', $lead_id, $lead_data);
        
        // Return success
        wp_send_json_success([
            'message' => 'Thank you for your inquiry! We will contact you shortly.',
            'lead_id' => $lead_id,
        ]);
    }
    
    /**
     * Validate form data
     */
    private function validate_form_data($data, $form_type) {
        $errors = [];
        $form_config = $this->form_types[$form_type] ?? null;
        
        if (!$form_config) {
            return ['valid' => false, 'message' => 'Invalid form type'];
        }
        
        // Check required fields
        foreach ($form_config['required'] as $field) {
            if (empty($data[$field])) {
                $errors[$field] = ucfirst($field) . ' is required';
            }
        }
        
        // Validate email
        if (!empty($data['email']) && !is_email($data['email'])) {
            $errors['email'] = 'Please enter a valid email address';
        }
        
        // Validate phone
        if (!empty($data['phone'])) {
            $phone = preg_replace('/[^0-9]/', '', $data['phone']);
            if (strlen($phone) < 10) {
                $errors['phone'] = 'Please enter a valid phone number';
            }
        }
        
        if (!empty($errors)) {
            return [
                'valid' => false,
                'message' => 'Please correct the errors below',
                'errors' => $errors,
            ];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Sanitize form data
     */
    private function sanitize_form_data($data) {
        $sanitized = [];
        
        // Text fields
        $text_fields = ['name', 'subject', 'address', 'preferred_contact', 'property_type', 'preferred_time'];
        foreach ($text_fields as $field) {
            if (isset($data[$field])) {
                $sanitized[$field] = sanitize_text_field($data[$field]);
            }
        }
        
        // Email
        if (isset($data['email'])) {
            $sanitized['email'] = sanitize_email($data['email']);
        }
        
        // Phone
        if (isset($data['phone'])) {
            $sanitized['phone'] = sanitize_text_field($data['phone']);
        }
        
        // Message
        if (isset($data['message'])) {
            $sanitized['message'] = sanitize_textarea_field($data['message']);
        }
        
        // Numbers
        $number_fields = ['listing_id', 'agent_id', 'bedrooms', 'bathrooms', 'square_feet'];
        foreach ($number_fields as $field) {
            if (isset($data[$field])) {
                $sanitized[$field] = intval($data[$field]);
            }
        }
        
        // Date
        if (isset($data['preferred_date'])) {
            $sanitized['preferred_date'] = sanitize_text_field($data['preferred_date']);
        }
        
        // Arrays
        if (isset($data['interests']) && is_array($data['interests'])) {
            $sanitized['interests'] = array_map('sanitize_text_field', $data['interests']);
        }
        
        return $sanitized;
    }
    
    /**
     * Create lead post
     */
    private function create_lead($data, $form_type) {
        // Create post title
        $title = sprintf(
            'Lead: %s - %s',
            $data['name'] ?? 'Unknown',
            date('Y-m-d H:i:s')
        );
        
        // Create post
        $post_data = [
            'post_title' => $title,
            'post_type' => 'lead',
            'post_status' => 'private',
            'post_author' => 1,
        ];
        
        $lead_id = wp_insert_post($post_data);
        
        if (!$lead_id || is_wp_error($lead_id)) {
            return false;
        }
        
        // Save meta data
        update_post_meta($lead_id, 'lead_form_type', $form_type);
        update_post_meta($lead_id, 'lead_date', current_time('mysql'));
        update_post_meta($lead_id, 'lead_source', 'website');
        update_post_meta($lead_id, 'lead_status', 'new');
        
        // Save form data
        foreach ($data as $key => $value) {
            update_post_meta($lead_id, 'lead_' . $key, $value);
        }
        
        // Link to listing if provided
        if (!empty($data['listing_id'])) {
            update_post_meta($lead_id, 'lead_listing_id', $data['listing_id']);
            
            // Get agent from listing
            $agent_id = get_field('listing_agent', $data['listing_id']);
            if ($agent_id) {
                update_post_meta($lead_id, 'lead_agent_id', $agent_id);
            }
        }
        
        // Link to agent if provided
        if (!empty($data['agent_id'])) {
            update_post_meta($lead_id, 'lead_agent_id', $data['agent_id']);
        }
        
        hp_log("Lead created: ID {$lead_id}, Type: {$form_type}", 'info', 'LEAD_CAPTURE');
        
        return $lead_id;
    }
    
    /**
     * Send lead notifications
     */
    public function send_lead_notifications($lead_id, $data) {
        // Get notification recipients
        $recipients = $this->get_notification_recipients($lead_id);
        
        if (empty($recipients)) {
            hp_log("No recipients found for lead {$lead_id}", 'warning', 'LEAD_CAPTURE');
            return;
        }
        
        // Prepare email content
        $subject = $this->get_email_subject($lead_id);
        $message = $this->get_email_message($lead_id, $data);
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>',
        ];
        
        // Add reply-to if we have lead email
        if (!empty($data['email'])) {
            $headers[] = 'Reply-To: ' . $data['name'] . ' <' . $data['email'] . '>';
        }
        
        // Send emails
        foreach ($recipients as $recipient) {
            $sent = wp_mail($recipient, $subject, $message, $headers);
            
            if ($sent) {
                hp_log("Lead notification sent to {$recipient} for lead {$lead_id}", 'info', 'LEAD_CAPTURE');
            } else {
                hp_log("Failed to send notification to {$recipient} for lead {$lead_id}", 'error', 'LEAD_CAPTURE');
            }
        }
        
        // Send confirmation to lead
        if (!empty($data['email'])) {
            $this->send_lead_confirmation($data['email'], $data['name'], $lead_id);
        }
    }
    
    /**
     * Get notification recipients
     */
    private function get_notification_recipients($lead_id) {
        $recipients = [];
        
        // Get agent email if assigned
        $agent_id = get_post_meta($lead_id, 'lead_agent_id', true);
        if ($agent_id) {
            $agent_email = get_field('email', $agent_id);
            if ($agent_email) {
                $recipients[] = $agent_email;
            }
        }
        
        // Add admin email as fallback
        if (empty($recipients)) {
            $recipients[] = get_option('admin_email');
        }
        
        // Allow filtering of recipients
        $recipients = apply_filters('hp_lead_notification_recipients', $recipients, $lead_id);
        
        return array_unique($recipients);
    }
    
    /**
     * Get email subject
     */
    private function get_email_subject($lead_id) {
        $form_type = get_post_meta($lead_id, 'lead_form_type', true);
        $site_name = get_bloginfo('name');
        
        $subjects = [
            'property_inquiry' => 'New Property Inquiry',
            'contact_agent' => 'New Agent Contact Request',
            'schedule_showing' => 'New Showing Request',
            'newsletter_signup' => 'New Newsletter Signup',
            'home_valuation' => 'New Home Valuation Request',
            'general_contact' => 'New Contact Form Submission',
        ];
        
        $subject = $subjects[$form_type] ?? 'New Lead';
        
        return sprintf('[%s] %s', $site_name, $subject);
    }
    
    /**
     * Get email message
     */
    private function get_email_message($lead_id, $data) {
        $form_type = get_post_meta($lead_id, 'lead_form_type', true);
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2c3e50; color: white; padding: 20px; text-align: center; }
                .content { background: #f9f9f9; padding: 20px; margin-top: 20px; }
                .field { margin-bottom: 15px; }
                .label { font-weight: bold; color: #555; }
                .value { margin-top: 5px; }
                .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #777; }
                .button { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>New Lead Received</h2>
                </div>
                
                <div class="content">
                    <h3>Lead Details</h3>
                    
                    <?php if (!empty($data['name'])): ?>
                    <div class="field">
                        <div class="label">Name:</div>
                        <div class="value"><?php echo esc_html($data['name']); ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($data['email'])): ?>
                    <div class="field">
                        <div class="label">Email:</div>
                        <div class="value">
                            <a href="mailto:<?php echo esc_attr($data['email']); ?>">
                                <?php echo esc_html($data['email']); ?>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($data['phone'])): ?>
                    <div class="field">
                        <div class="label">Phone:</div>
                        <div class="value">
                            <a href="tel:<?php echo esc_attr($data['phone']); ?>">
                                <?php echo esc_html($data['phone']); ?>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($data['message'])): ?>
                    <div class="field">
                        <div class="label">Message:</div>
                        <div class="value"><?php echo nl2br(esc_html($data['message'])); ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($data['preferred_date'])): ?>
                    <div class="field">
                        <div class="label">Preferred Date:</div>
                        <div class="value"><?php echo esc_html($data['preferred_date']); ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($data['preferred_time'])): ?>
                    <div class="field">
                        <div class="label">Preferred Time:</div>
                        <div class="value"><?php echo esc_html($data['preferred_time']); ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($data['listing_id'])): ?>
                    <div class="field">
                        <div class="label">Property:</div>
                        <div class="value">
                            <a href="<?php echo get_permalink($data['listing_id']); ?>">
                                <?php echo get_the_title($data['listing_id']); ?>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="field">
                        <div class="label">Form Type:</div>
                        <div class="value"><?php echo esc_html(ucwords(str_replace('_', ' ', $form_type))); ?></div>
                    </div>
                    
                    <div class="field">
                        <div class="label">Submitted:</div>
                        <div class="value"><?php echo date('F j, Y g:i a'); ?></div>
                    </div>
                    
                    <div style="margin-top: 30px; text-align: center;">
                        <a href="<?php echo admin_url('post.php?post=' . $lead_id . '&action=edit'); ?>" class="button">
                            View Lead in Dashboard
                        </a>
                    </div>
                </div>
                
                <div class="footer">
                    <p>This lead was submitted through your website.</p>
                    <p>&copy; <?php echo date('Y'); ?> <?php echo get_bloginfo('name'); ?></p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Send confirmation email to lead
     */
    private function send_lead_confirmation($email, $name, $lead_id) {
        $subject = 'Thank you for contacting ' . get_bloginfo('name');
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2c3e50; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #777; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Thank You for Contacting Us</h2>
                </div>
                
                <div class="content">
                    <p>Dear <?php echo esc_html($name); ?>,</p>
                    
                    <p>Thank you for your inquiry. We have received your message and one of our team members will contact you shortly.</p>
                    
                    <p>If you have any urgent questions, please don't hesitate to call us directly.</p>
                    
                    <p>Best regards,<br>
                    The <?php echo get_bloginfo('name'); ?> Team</p>
                </div>
                
                <div class="footer">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo get_bloginfo('name'); ?>. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        <?php
        $message = ob_get_clean();
        
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>',
        ];
        
        wp_mail($email, $subject, $message, $headers);
    }
    
    /**
     * Get lead statistics for dashboard
     */
    public function get_lead_statistics($agent_id = null) {
        $args = [
            'post_type' => 'lead',
            'post_status' => 'private',
            'posts_per_page' => -1,
        ];
        
        if ($agent_id) {
            $args['meta_query'] = [
                [
                    'key' => 'lead_agent_id',
                    'value' => $agent_id,
                    'compare' => '=',
                ],
            ];
        }
        
        $query = new \WP_Query($args);
        
        $stats = [
            'total' => $query->found_posts,
            'new' => 0,
            'contacted' => 0,
            'converted' => 0,
            'by_type' => [],
            'by_month' => [],
        ];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $lead_id = get_the_ID();
                
                // Count by status
                $status = get_post_meta($lead_id, 'lead_status', true);
                if ($status === 'new') $stats['new']++;
                if ($status === 'contacted') $stats['contacted']++;
                if ($status === 'converted') $stats['converted']++;
                
                // Count by type
                $type = get_post_meta($lead_id, 'lead_form_type', true);
                if (!isset($stats['by_type'][$type])) {
                    $stats['by_type'][$type] = 0;
                }
                $stats['by_type'][$type]++;
                
                // Count by month
                $date = get_the_date('Y-m');
                if (!isset($stats['by_month'][$date])) {
                    $stats['by_month'][$date] = 0;
                }
                $stats['by_month'][$date]++;
            }
            wp_reset_postdata();
        }
        
        return $stats;
    }
}
