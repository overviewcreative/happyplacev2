<?php
/**
 * Open House Service - Event Management & Scheduling
 * 
 * Manages open house events, RSVPs, and attendee tracking
 * 
 * @package HappyPlace\Services
 * @version 4.0.0
 */

namespace HappyPlace\Services;

use HappyPlace\Core\Service;

if (!defined('ABSPATH')) {
    exit;
}

class OpenHouseService extends Service {
    
    protected string $name = 'open_house_service';
    protected string $version = '4.0.0';
    
    /**
     * RSVP table name
     */
    private string $rsvp_table_name;
    
    /**
     * Initialize service
     */
    public function init(): void {
        if ($this->initialized) {
            return;
        }
        
        global $wpdb;
        $this->rsvp_table_name = $wpdb->prefix . 'hp_open_house_rsvps';
        
        // Create RSVP table
        $this->create_rsvp_table();
        
        // Hook into open house post saves
        add_action('save_post_open_house', [$this, 'process_open_house_save'], 10, 2);
        
        // Register AJAX handlers
        $this->register_ajax_handlers();
        
        // Register shortcodes
        add_shortcode('hp_open_house_schedule', [$this, 'render_schedule_shortcode']);
        add_shortcode('hp_open_house_rsvp', [$this, 'render_rsvp_shortcode']);
        add_shortcode('hp_upcoming_open_houses', [$this, 'render_upcoming_shortcode']);
        
        // Add admin columns
        add_filter('manage_open_house_posts_columns', [$this, 'add_admin_columns']);
        add_action('manage_open_house_posts_custom_column', [$this, 'display_admin_columns'], 10, 2);
        
        // Register frontend assets
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        
        // Schedule reminder emails
        add_action('hp_send_open_house_reminders', [$this, 'send_reminder_emails']);
        if (!wp_next_scheduled('hp_send_open_house_reminders')) {
            wp_schedule_event(time(), 'hourly', 'hp_send_open_house_reminders');
        }
        
        $this->initialized = true;
        $this->log('Open House Service initialized successfully');
    }
    
    /**
     * Create RSVP table
     */
    private function create_rsvp_table(): void {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$this->rsvp_table_name} (
            id int(11) NOT NULL AUTO_INCREMENT,
            open_house_id int(11) NOT NULL,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            email varchar(255) NOT NULL,
            phone varchar(20),
            party_size int(11) DEFAULT 1,
            message text,
            status varchar(20) DEFAULT 'confirmed',
            attended boolean DEFAULT FALSE,
            rsvp_date datetime DEFAULT CURRENT_TIMESTAMP,
            reminder_sent datetime,
            ip_address varchar(45),
            user_agent varchar(255),
            PRIMARY KEY (id),
            KEY idx_open_house (open_house_id),
            KEY idx_email (email),
            KEY idx_status (status),
            KEY idx_rsvp_date (rsvp_date)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Process open house save
     */
    public function process_open_house_save(int $post_id, \WP_Post $post): void {
        // Update event status based on dates
        $this->update_event_status($post_id);
        
        // Generate calendar data
        $this->generate_calendar_data($post_id);
        
        do_action('hp_open_house_saved', $post_id, $post);
    }
    
    /**
     * Update event status based on dates
     */
    public function update_event_status(int $post_id): void {
        $start_date = get_post_meta($post_id, 'start_date', true);
        $end_date = get_post_meta($post_id, 'end_date', true);
        
        if (!$start_date) {
            return;
        }
        
        $now = current_time('mysql');
        $start_datetime = $start_date . ' ' . (get_post_meta($post_id, 'start_time', true) ?: '10:00:00');
        $end_datetime = $end_date ? $end_date . ' ' . (get_post_meta($post_id, 'end_time', true) ?: '16:00:00') : $start_datetime;
        
        $status = 'scheduled';
        if ($now > $end_datetime) {
            $status = 'completed';
        } elseif ($now >= $start_datetime && $now <= $end_datetime) {
            $status = 'active';
        }
        
        update_post_meta($post_id, 'event_status', $status);
    }
    
    /**
     * Generate calendar data
     */
    public function generate_calendar_data(int $post_id): void {
        $start_date = get_post_meta($post_id, 'start_date', true);
        $start_time = get_post_meta($post_id, 'start_time', true) ?: '10:00';
        $end_time = get_post_meta($post_id, 'end_time', true) ?: '16:00';
        
        if (!$start_date) {
            return;
        }
        
        // Generate ICS calendar data
        $listing_id = get_post_meta($post_id, 'listing_id', true);
        $listing_title = $listing_id ? get_the_title($listing_id) : 'Open House';
        $address = $listing_id ? get_post_meta($listing_id, 'street_address', true) : '';
        
        $calendar_data = [
            'title' => "Open House: {$listing_title}",
            'start_datetime' => $start_date . 'T' . str_replace(':', '', $start_time) . '00',
            'end_datetime' => $start_date . 'T' . str_replace(':', '', $end_time) . '00',
            'location' => $address,
            'description' => get_the_content(null, false, $post_id)
        ];
        
        update_post_meta($post_id, 'calendar_data', $calendar_data);
    }
    
    /**
     * Create RSVP
     */
    public function create_rsvp(array $data): int {
        global $wpdb;
        
        // Validate required fields
        if (empty($data['open_house_id']) || empty($data['email'])) {
            return 0;
        }
        
        // Check for duplicate RSVP
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->rsvp_table_name} 
             WHERE open_house_id = %d AND email = %s",
            $data['open_house_id'],
            $data['email']
        ));
        
        if ($existing) {
            return $existing; // Return existing RSVP ID
        }
        
        $rsvp_data = [
            'open_house_id' => intval($data['open_house_id']),
            'first_name' => sanitize_text_field($data['first_name'] ?? ''),
            'last_name' => sanitize_text_field($data['last_name'] ?? ''),
            'email' => sanitize_email($data['email']),
            'phone' => sanitize_text_field($data['phone'] ?? ''),
            'party_size' => max(1, intval($data['party_size'] ?? 1)),
            'message' => sanitize_textarea_field($data['message'] ?? ''),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ];
        
        $result = $wpdb->insert(
            $this->rsvp_table_name,
            $rsvp_data,
            ['%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s']
        );
        
        if ($result === false) {
            $this->log('Failed to create RSVP: ' . $wpdb->last_error, 'error');
            return 0;
        }
        
        $rsvp_id = $wpdb->insert_id;
        
        // Send confirmation email
        $this->send_rsvp_confirmation($rsvp_id);
        
        // Notify agent
        $this->notify_agent_of_rsvp($rsvp_id);
        
        do_action('hp_open_house_rsvp_created', $rsvp_id, $data);
        
        return $rsvp_id;
    }
    
    /**
     * Get open house RSVPs
     */
    public function get_rsvps(int $open_house_id): array {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->rsvp_table_name} 
             WHERE open_house_id = %d 
             ORDER BY rsvp_date DESC",
            $open_house_id
        ), ARRAY_A);
    }
    
    /**
     * Get upcoming open houses
     */
    public function get_upcoming_open_houses(int $limit = 10): array {
        $args = [
            'post_type' => 'open_house',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'meta_query' => [
                [
                    'key' => 'start_date',
                    'value' => current_time('Y-m-d'),
                    'compare' => '>='
                ]
            ],
            'meta_key' => 'start_date',
            'orderby' => 'meta_value',
            'order' => 'ASC'
        ];
        
        $posts = get_posts($args);
        $open_houses = [];
        
        foreach ($posts as $post) {
            $listing_id = get_post_meta($post->ID, 'listing_id', true);
            $rsvp_count = $this->get_rsvp_count($post->ID);
            
            $open_houses[] = [
                'id' => $post->ID,
                'title' => $post->post_title,
                'listing_id' => $listing_id,
                'listing_title' => $listing_id ? get_the_title($listing_id) : '',
                'start_date' => get_post_meta($post->ID, 'start_date', true),
                'start_time' => get_post_meta($post->ID, 'start_time', true),
                'end_time' => get_post_meta($post->ID, 'end_time', true),
                'address' => $listing_id ? get_post_meta($listing_id, 'street_address', true) : '',
                'agent_id' => get_post_meta($post->ID, 'agent_id', true),
                'rsvp_count' => $rsvp_count,
                'status' => get_post_meta($post->ID, 'event_status', true),
                'permalink' => get_permalink($post->ID)
            ];
        }
        
        return $open_houses;
    }
    
    /**
     * Get RSVP count
     */
    public function get_rsvp_count(int $open_house_id): int {
        global $wpdb;
        
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->rsvp_table_name} 
             WHERE open_house_id = %d AND status = 'confirmed'",
            $open_house_id
        ));
    }
    
    /**
     * Send RSVP confirmation email
     */
    private function send_rsvp_confirmation(int $rsvp_id): void {
        global $wpdb;
        
        $rsvp = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->rsvp_table_name} WHERE id = %d",
            $rsvp_id
        ), ARRAY_A);
        
        if (!$rsvp) {
            return;
        }
        
        $open_house = get_post($rsvp['open_house_id']);
        if (!$open_house) {
            return;
        }
        
        $listing_id = get_post_meta($open_house->ID, 'listing_id', true);
        $listing_title = $listing_id ? get_the_title($listing_id) : 'Open House';
        $address = $listing_id ? get_post_meta($listing_id, 'street_address', true) : '';
        
        $start_date = get_post_meta($open_house->ID, 'start_date', true);
        $start_time = get_post_meta($open_house->ID, 'start_time', true);
        $end_time = get_post_meta($open_house->ID, 'end_time', true);
        
        $subject = "RSVP Confirmation: Open House - {$listing_title}";
        
        $message = "Dear {$rsvp['first_name']},\n\n";
        $message .= "Thank you for your RSVP! We're excited to see you at our open house.\n\n";
        $message .= "Event Details:\n";
        $message .= "Property: {$listing_title}\n";
        $message .= "Address: {$address}\n";
        $message .= "Date: " . date('F j, Y', strtotime($start_date)) . "\n";
        $message .= "Time: {$start_time} - {$end_time}\n";
        $message .= "Party Size: {$rsvp['party_size']} " . ($rsvp['party_size'] == 1 ? 'person' : 'people') . "\n\n";
        
        if ($listing_id) {
            $message .= "View Property: " . get_permalink($listing_id) . "\n\n";
        }
        
        $message .= "If you need to cancel or modify your RSVP, please contact us.\n\n";
        $message .= "We look forward to meeting you!\n\n";
        $message .= "Best regards,\n" . get_bloginfo('name');
        
        wp_mail($rsvp['email'], $subject, $message);
    }
    
    /**
     * Notify agent of new RSVP
     */
    private function notify_agent_of_rsvp(int $rsvp_id): void {
        global $wpdb;
        
        $rsvp = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->rsvp_table_name} WHERE id = %d",
            $rsvp_id
        ), ARRAY_A);
        
        if (!$rsvp) {
            return;
        }
        
        $open_house = get_post($rsvp['open_house_id']);
        $agent_id = get_post_meta($open_house->ID, 'agent_id', true);
        
        if (!$agent_id) {
            return;
        }
        
        $agent = get_user_by('id', $agent_id);
        if (!$agent) {
            return;
        }
        
        $listing_id = get_post_meta($open_house->ID, 'listing_id', true);
        $listing_title = $listing_id ? get_the_title($listing_id) : 'Open House';
        
        $subject = "New RSVP: {$rsvp['first_name']} {$rsvp['last_name']} - {$listing_title}";
        
        $message = "You have a new RSVP for your open house:\n\n";
        $message .= "Attendee: {$rsvp['first_name']} {$rsvp['last_name']}\n";
        $message .= "Email: {$rsvp['email']}\n";
        $message .= "Phone: {$rsvp['phone']}\n";
        $message .= "Party Size: {$rsvp['party_size']}\n";
        
        if ($rsvp['message']) {
            $message .= "Message: {$rsvp['message']}\n";
        }
        
        $message .= "\nProperty: {$listing_title}\n";
        $message .= "Open House: {$open_house->post_title}\n";
        $message .= "Date: " . get_post_meta($open_house->ID, 'start_date', true) . "\n\n";
        
        $admin_url = admin_url('post.php?post=' . $open_house->ID . '&action=edit');
        $message .= "Manage Open House: {$admin_url}";
        
        wp_mail($agent->user_email, $subject, $message);
    }
    
    /**
     * Send reminder emails
     */
    public function send_reminder_emails(): void {
        global $wpdb;
        
        // Find open houses happening in the next 24 hours with RSVPs that haven't received reminders
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        
        $rsvps = $wpdb->get_results($wpdb->prepare("
            SELECT r.*, p.post_title, pm.meta_value as start_date
            FROM {$this->rsvp_table_name} r
            INNER JOIN {$wpdb->posts} p ON r.open_house_id = p.ID
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'start_date'
            WHERE pm.meta_value = %s
            AND r.status = 'confirmed'
            AND r.reminder_sent IS NULL
        ", $tomorrow), ARRAY_A);
        
        foreach ($rsvps as $rsvp) {
            $this->send_individual_reminder($rsvp);
            
            // Mark as reminder sent
            $wpdb->update(
                $this->rsvp_table_name,
                ['reminder_sent' => current_time('mysql')],
                ['id' => $rsvp['id']],
                ['%s'],
                ['%d']
            );
        }
    }
    
    /**
     * Send individual reminder
     */
    private function send_individual_reminder(array $rsvp): void {
        $open_house = get_post($rsvp['open_house_id']);
        $listing_id = get_post_meta($open_house->ID, 'listing_id', true);
        $listing_title = $listing_id ? get_the_title($listing_id) : 'Open House';
        
        $start_time = get_post_meta($open_house->ID, 'start_time', true);
        $end_time = get_post_meta($open_house->ID, 'end_time', true);
        $address = $listing_id ? get_post_meta($listing_id, 'street_address', true) : '';
        
        $subject = "Reminder: Open House Tomorrow - {$listing_title}";
        
        $message = "Dear {$rsvp['first_name']},\n\n";
        $message .= "This is a friendly reminder about the open house you're registered for tomorrow.\n\n";
        $message .= "Event Details:\n";
        $message .= "Property: {$listing_title}\n";
        $message .= "Address: {$address}\n";
        $message .= "Date: Tomorrow (" . date('F j, Y', strtotime($rsvp['start_date'])) . ")\n";
        $message .= "Time: {$start_time} - {$end_time}\n\n";
        
        $message .= "We're looking forward to seeing you there!\n\n";
        $message .= "Best regards,\n" . get_bloginfo('name');
        
        wp_mail($rsvp['email'], $subject, $message);
    }
    
    /**
     * Register AJAX handlers
     */
    private function register_ajax_handlers(): void {
        add_action('wp_ajax_nopriv_hp_open_house_rsvp', [$this, 'ajax_create_rsvp']);
        add_action('wp_ajax_hp_open_house_rsvp', [$this, 'ajax_create_rsvp']);
        add_action('wp_ajax_hp_mark_attendee', [$this, 'ajax_mark_attendee']);
        add_action('wp_ajax_hp_get_open_house_stats', [$this, 'ajax_get_stats']);
    }
    
    /**
     * AJAX: Create RSVP
     */
    public function ajax_create_rsvp(): void {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'hp_open_house_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $rsvp_data = [
            'open_house_id' => intval($_POST['open_house_id'] ?? 0),
            'first_name' => sanitize_text_field($_POST['first_name'] ?? ''),
            'last_name' => sanitize_text_field($_POST['last_name'] ?? ''),
            'email' => sanitize_email($_POST['email'] ?? ''),
            'phone' => sanitize_text_field($_POST['phone'] ?? ''),
            'party_size' => intval($_POST['party_size'] ?? 1),
            'message' => sanitize_textarea_field($_POST['message'] ?? '')
        ];
        
        // Validate
        if (!$rsvp_data['open_house_id'] || !$rsvp_data['first_name'] || !$rsvp_data['email']) {
            wp_send_json_error(['message' => 'Please fill in all required fields']);
            return;
        }
        
        if (!is_email($rsvp_data['email'])) {
            wp_send_json_error(['message' => 'Please enter a valid email address']);
            return;
        }
        
        $rsvp_id = $this->create_rsvp($rsvp_data);
        
        if (!$rsvp_id) {
            wp_send_json_error(['message' => 'Failed to create RSVP. Please try again.']);
            return;
        }
        
        wp_send_json_success([
            'message' => 'Thank you for your RSVP! You should receive a confirmation email shortly.',
            'rsvp_id' => $rsvp_id
        ]);
    }
    
    /**
     * Render RSVP shortcode
     */
    public function render_rsvp_shortcode($atts): string {
        $atts = shortcode_atts([
            'open_house_id' => get_the_ID(),
            'title' => 'RSVP for Open House',
            'button_text' => 'Reserve Your Spot'
        ], $atts);
        
        if (!$atts['open_house_id']) {
            return '<p>Error: No open house specified.</p>';
        }
        
        ob_start();
        ?>
        <div class="hp-open-house-rsvp-form">
            <h3><?php echo esc_html($atts['title']); ?></h3>
            
            <form class="hp-rsvp-form" data-open-house-id="<?php echo esc_attr($atts['open_house_id']); ?>">
                <div class="hp-form-messages" style="display: none;"></div>
                
                <div class="hp-form-row">
                    <div class="hp-form-group hp-form-half">
                        <label for="rsvp-first-name">First Name <span class="required">*</span></label>
                        <input type="text" id="rsvp-first-name" name="first_name" required>
                    </div>
                    
                    <div class="hp-form-group hp-form-half">
                        <label for="rsvp-last-name">Last Name <span class="required">*</span></label>
                        <input type="text" id="rsvp-last-name" name="last_name" required>
                    </div>
                </div>
                
                <div class="hp-form-group">
                    <label for="rsvp-email">Email Address <span class="required">*</span></label>
                    <input type="email" id="rsvp-email" name="email" required>
                </div>
                
                <div class="hp-form-row">
                    <div class="hp-form-group hp-form-half">
                        <label for="rsvp-phone">Phone Number</label>
                        <input type="tel" id="rsvp-phone" name="phone">
                    </div>
                    
                    <div class="hp-form-group hp-form-half">
                        <label for="rsvp-party-size">Party Size</label>
                        <select id="rsvp-party-size" name="party_size">
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?> <?php echo $i == 1 ? 'person' : 'people'; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                
                <div class="hp-form-group">
                    <label for="rsvp-message">Message (Optional)</label>
                    <textarea id="rsvp-message" name="message" rows="3" placeholder="Any questions or special requests?"></textarea>
                </div>
                
                <button type="submit" class="hp-btn hp-btn-primary">
                    <span class="hp-btn-text"><?php echo esc_html($atts['button_text']); ?></span>
                    <span class="hp-btn-loading" style="display: none;">
                        <span class="hp-spinner"></span> Processing...
                    </span>
                </button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render upcoming open houses shortcode
     */
    public function render_upcoming_shortcode($atts): string {
        $atts = shortcode_atts([
            'limit' => 5,
            'show_rsvp_count' => true,
            'show_agent' => true
        ], $atts);
        
        $open_houses = $this->get_upcoming_open_houses($atts['limit']);
        
        if (empty($open_houses)) {
            return '<p>No upcoming open houses scheduled.</p>';
        }
        
        ob_start();
        ?>
        <div class="hp-upcoming-open-houses">
            <?php foreach ($open_houses as $oh): ?>
                <div class="hp-open-house-card">
                    <div class="hp-open-house-date">
                        <div class="hp-date"><?php echo date('M j', strtotime($oh['start_date'])); ?></div>
                        <div class="hp-time"><?php echo date('g:i A', strtotime($oh['start_time'])); ?></div>
                    </div>
                    
                    <div class="hp-open-house-details">
                        <h4><a href="<?php echo esc_url($oh['permalink']); ?>"><?php echo esc_html($oh['listing_title'] ?: $oh['title']); ?></a></h4>
                        
                        <?php if ($oh['address']): ?>
                            <p class="hp-address"><?php echo esc_html($oh['address']); ?></p>
                        <?php endif; ?>
                        
                        <div class="hp-open-house-meta">
                            <span class="hp-duration"><?php echo date('g:i A', strtotime($oh['start_time'])); ?> - <?php echo date('g:i A', strtotime($oh['end_time'])); ?></span>
                            
                            <?php if ($atts['show_rsvp_count']): ?>
                                <span class="hp-rsvp-count"><?php echo $oh['rsvp_count']; ?> RSVPs</span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($oh['listing_id']): ?>
                            <a href="<?php echo get_permalink($oh['listing_id']); ?>" class="hp-view-property">View Property</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Add admin columns
     */
    public function add_admin_columns($columns): array {
        $new_columns = [];
        
        foreach ($columns as $key => $title) {
            $new_columns[$key] = $title;
            
            if ($key === 'title') {
                $new_columns['event_date'] = 'Date & Time';
                $new_columns['rsvp_count'] = 'RSVPs';
                $new_columns['status'] = 'Status';
            }
        }
        
        return $new_columns;
    }
    
    /**
     * Display admin columns
     */
    public function display_admin_columns($column, $post_id): void {
        switch ($column) {
            case 'event_date':
                $start_date = get_post_meta($post_id, 'start_date', true);
                $start_time = get_post_meta($post_id, 'start_time', true);
                $end_time = get_post_meta($post_id, 'end_time', true);
                
                if ($start_date) {
                    echo date('M j, Y', strtotime($start_date)) . '<br>';
                    echo $start_time . ($end_time ? ' - ' . $end_time : '');
                }
                break;
                
            case 'rsvp_count':
                echo $this->get_rsvp_count($post_id);
                break;
                
            case 'status':
                $status = get_post_meta($post_id, 'event_status', true) ?: 'scheduled';
                echo '<span class="hp-status hp-status-' . esc_attr($status) . '">' . ucfirst($status) . '</span>';
                break;
        }
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets(): void {
        if (is_singular('open_house') || has_shortcode(get_post_field('post_content'), 'hp_open_house_rsvp')) {
            wp_enqueue_script(
                'hp-open-house',
                HP_ASSETS_URL . 'js/open-house.js',
                ['jquery'],
                HP_VERSION,
                true
            );
            
            wp_localize_script('hp-open-house', 'hp_open_house', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('hp_open_house_nonce'),
                'messages' => [
                    'success' => 'Thank you for your RSVP!',
                    'error' => 'Something went wrong. Please try again.',
                    'validation' => 'Please fill in all required fields.'
                ]
            ]);
            
            wp_enqueue_style(
                'hp-open-house',
                HP_ASSETS_URL . 'css/open-house.css',
                [],
                HP_VERSION
            );
        }
    }
}