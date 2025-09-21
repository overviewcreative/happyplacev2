<?php
/**
 * Marketing Service
 * 
 * Handles PDF generation, social media templates, and email marketing
 * 
 * @package HappyPlace
 * @since 4.0.0
 */

namespace HappyPlace\Services;

use HappyPlace\Core\Service;

if (!defined('ABSPATH')) {
    exit;
}

class MarketingService extends Service {
    
    /**
     * Service name
     */
    protected string $name = 'marketing_service';
    
    /**
     * PDF generation library
     */
    private $pdf_generator = null;
    
    /**
     * Initialize service
     */
    public function init(): void {
        add_action('wp_ajax_hph_generate_pdf_flyer', [$this, 'generate_pdf_flyer']);
        add_action('wp_ajax_hph_generate_social_template', [$this, 'generate_social_template']);
        add_action('wp_ajax_hph_send_marketing_email', [$this, 'send_marketing_email']);
        add_action('wp_ajax_hph_get_marketing_templates', [$this, 'get_marketing_templates']);
        
        // Register PDF generation capability
        if (!current_user_can('generate_marketing_materials')) {
            add_filter('user_has_cap', [$this, 'add_marketing_capability'], 10, 4);
        }
    }
    
    /**
     * Add marketing capability to agents and admins
     */
    public function add_marketing_capability($allcaps, $caps, $args, $user) {
        if (in_array('agent', $user->roles) || in_array('administrator', $user->roles)) {
            $allcaps['generate_marketing_materials'] = true;
        }
        return $allcaps;
    }
    
    /**
     * Generate PDF flyer for listing
     */
    public function generate_pdf_flyer() {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'], 'hph_marketing_nonce') || 
            !current_user_can('generate_marketing_materials')) {
            wp_die(__('Security check failed', 'happy-place'));
        }
        
        $listing_id = intval($_POST['listing_id']);
        $template_style = sanitize_text_field($_POST['template_style'] ?? 'modern');
        
        if (!$listing_id) {
            wp_send_json_error(__('Invalid listing ID', 'happy-place'));
        }
        
        try {
            $pdf_path = $this->create_pdf_flyer($listing_id, $template_style);
            
            if ($pdf_path) {
                // Log marketing activity
                $this->log_marketing_activity($listing_id, 'pdf_flyer', [
                    'template' => $template_style,
                    'file_path' => $pdf_path
                ]);
                
                wp_send_json_success([
                    'message' => __('PDF flyer generated successfully', 'happy-place'),
                    'download_url' => $this->get_pdf_download_url($pdf_path),
                    'file_path' => $pdf_path
                ]);
            } else {
                wp_send_json_error(__('Failed to generate PDF flyer', 'happy-place'));
            }
        } catch (Exception $e) {
            hp_log('Marketing Service: PDF generation failed - ' . $e->getMessage(), 'error');
            wp_send_json_error(__('PDF generation failed', 'happy-place'));
        }
    }
    
    /**
     * Create PDF flyer for listing
     */
    private function create_pdf_flyer($listing_id, $template_style = 'modern') {
        // Get listing data
        $listing_data = $this->get_listing_marketing_data($listing_id);
        
        if (!$listing_data) {
            return false;
        }
        
        // Check if TCPDF is available (for production, this would be installed via Composer)
        $tcpdf_path = ABSPATH . 'wp-content/plugins/happy-place/vendor/tecnickcom/tcpdf/tcpdf.php';
        
        if (!file_exists($tcpdf_path)) {
            // For now, create a simple HTML to PDF solution or use WordPress built-in functions
            return $this->create_simple_pdf_flyer($listing_data, $template_style);
        }
        
        require_once($tcpdf_path);
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set PDF metadata
        $pdf->SetCreator('Happy Place Real Estate');
        $pdf->SetAuthor($listing_data['agent_name']);
        $pdf->SetTitle($listing_data['title'] . ' - Property Flyer');
        $pdf->SetSubject('Real Estate Property Flyer');
        
        // Remove header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Add page
        $pdf->AddPage();
        
        // Generate HTML content based on template
        $html_content = $this->get_pdf_template_html($listing_data, $template_style);
        
        // Write HTML content
        $pdf->writeHTML($html_content, true, false, true, false, '');
        
        // Save PDF
        $upload_dir = wp_upload_dir();
        $pdf_dir = $upload_dir['basedir'] . '/marketing/pdf/';
        
        if (!file_exists($pdf_dir)) {
            wp_mkdir_p($pdf_dir);
        }
        
        $pdf_filename = 'flyer-' . $listing_id . '-' . date('Y-m-d-H-i-s') . '.pdf';
        $pdf_path = $pdf_dir . $pdf_filename;
        
        $pdf->Output($pdf_path, 'F');
        
        return $pdf_path;
    }
    
    /**
     * Get listing data formatted for marketing materials
     */
    private function get_listing_marketing_data($listing_id) {
        $post = get_post($listing_id);
        
        if (!$post || $post->post_type !== 'listing') {
            return false;
        }
        
        // Get listing fields
        $price = get_field('price', $listing_id);
        $bedrooms = get_field('bedrooms', $listing_id);
        $bathrooms_full = get_field('bathrooms_full', $listing_id) ?: 0;
        $bathrooms_half = get_field('bathrooms_half', $listing_id) ?: 0;
        $square_feet = get_field('square_feet', $listing_id);
        $address = get_field('address', $listing_id);
        $property_description = get_field('property_description', $listing_id);
        
        // Get agent info
        $agent_id = get_field('agent', $listing_id);
        $agent_data = [];
        
        if ($agent_id) {
            $agent_data = [
                'name' => get_the_title($agent_id),
                'phone' => get_field('phone', $agent_id),
                'email' => get_field('email', $agent_id),
                'photo' => get_field('photo', $agent_id),
                'license' => get_field('license_number', $agent_id)
            ];
        }
        
        // Get listing images
        $images = [];
        $image_gallery = get_field('gallery', $listing_id);
        
        if ($image_gallery) {
            foreach ($image_gallery as $image) {
                $images[] = [
                    'url' => $image['url'],
                    'alt' => $image['alt']
                ];
            }
        }
        
        return [
            'id' => $listing_id,
            'title' => $post->post_title,
            'price' => $price ? '$' . number_format($price) : '',
            'bedrooms' => $bedrooms,
            'bathrooms' => $bathrooms_full + ($bathrooms_half * 0.5),
            'square_feet' => $square_feet ? number_format($square_feet) . ' sq ft' : '',
            'address' => $address,
            'description' => $property_description ?: $post->post_content,
            'images' => $images,
            'agent_name' => $agent_data['name'] ?? '',
            'agent_phone' => $agent_data['phone'] ?? '',
            'agent_email' => $agent_data['email'] ?? '',
            'agent_photo' => $agent_data['photo'] ?? '',
            'agent_license' => $agent_data['license'] ?? '',
            'listing_url' => get_permalink($listing_id),
            'qr_code' => $this->generate_qr_code(get_permalink($listing_id))
        ];
    }
    
    /**
     * Get PDF template HTML
     */
    private function get_pdf_template_html($data, $style = 'modern') {
        $template_file = HP_PLUGIN_DIR . "/templates/marketing/pdf/{$style}-flyer.php";
        
        if (!file_exists($template_file)) {
            $template_file = HP_PLUGIN_DIR . "/templates/marketing/pdf/default-flyer.php";
        }
        
        if (!file_exists($template_file)) {
            // Return basic HTML template
            return $this->get_default_pdf_html($data);
        }
        
        ob_start();
        include $template_file;
        return ob_get_clean();
    }
    
    /**
     * Get default PDF HTML template
     */
    private function get_default_pdf_html($data) {
        $html = '
        <style>
            .header { background: #2563eb; color: white; padding: 20px; text-align: center; }
            .property-image { width: 100%; max-height: 300px; object-fit: cover; margin: 10px 0; }
            .property-details { margin: 20px 0; }
            .price { font-size: 24px; font-weight: bold; color: #059669; }
            .features { display: flex; gap: 20px; margin: 15px 0; }
            .agent-info { background: #f8fafc; padding: 15px; margin-top: 30px; }
        </style>
        
        <div class="header">
            <h1>' . esc_html($data['title']) . '</h1>
            <div class="price">' . esc_html($data['price']) . '</div>
        </div>';
        
        if (!empty($data['images'])) {
            $html .= '<img src="' . esc_url($data['images'][0]['url']) . '" class="property-image">';
        }
        
        $html .= '
        <div class="property-details">
            <div class="features">
                <strong>🏠 ' . esc_html($data['bedrooms']) . ' Bedrooms</strong>
                <strong>🛁 ' . esc_html($data['bathrooms']) . ' Bathrooms</strong>
                <strong>📐 ' . esc_html($data['square_feet']) . '</strong>
            </div>
            
            <p><strong>📍 Address:</strong> ' . esc_html($data['address']) . '</p>
            
            <div class="description">
                <h3>Property Description</h3>
                <p>' . wp_kses_post($data['description']) . '</p>
            </div>
        </div>
        
        <div class="agent-info">
            <h3>Contact Agent</h3>
            <p><strong>' . esc_html($data['agent_name']) . '</strong></p>
            <p>📞 ' . esc_html($data['agent_phone']) . '</p>
            <p>📧 ' . esc_html($data['agent_email']) . '</p>
            ' . (!empty($data['agent_license']) ? '<p>License: ' . esc_html($data['agent_license']) . '</p>' : '') . '
        </div>';
        
        return $html;
    }
    
    /**
     * Create simple PDF flyer fallback (when TCPDF not available)
     */
    private function create_simple_pdf_flyer($listing_data, $template_style = 'modern') {
        // For now, create an HTML file that can be converted to PDF later
        // In production, you would use a service like Puppeteer, wkhtmltopdf, or similar
        
        $upload_dir = wp_upload_dir();
        $pdf_dir = $upload_dir['basedir'] . '/marketing/html/';
        
        if (!file_exists($pdf_dir)) {
            wp_mkdir_p($pdf_dir);
        }
        
        $html_filename = 'flyer-' . $listing_data['id'] . '-' . date('Y-m-d-H-i-s') . '.html';
        $html_path = $pdf_dir . $html_filename;
        
        // Generate printable HTML content
        $html_content = $this->get_printable_html($listing_data, $template_style);
        
        // Save HTML file
        file_put_contents($html_path, $html_content);
        
        // Return path for download (in production, this would be converted to PDF)
        return $html_path;
    }
    
    /**
     * Get printable HTML for PDF conversion
     */
    private function get_printable_html($data, $style = 'modern') {
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>' . esc_html($data['title']) . ' - Property Flyer</title>
            <style>
                @media print { @page { margin: 0; } }
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: white; }
                .header { background: #2563eb; color: white; padding: 30px; text-align: center; margin: -20px -20px 20px -20px; }
                .property-title { font-size: 28px; font-weight: bold; margin: 0; }
                .price { font-size: 32px; font-weight: bold; color: #059669; margin: 10px 0; }
                .property-image { width: 100%; max-height: 400px; object-fit: cover; border-radius: 8px; margin: 20px 0; }
                .features { display: flex; gap: 30px; margin: 20px 0; justify-content: center; }
                .feature { text-align: center; padding: 15px; background: #f8fafc; border-radius: 8px; flex: 1; }
                .feature-number { font-size: 24px; font-weight: bold; color: #2563eb; }
                .feature-label { font-size: 14px; color: #6b7280; }
                .description { margin: 30px 0; line-height: 1.6; }
                .agent-info { background: #f8fafc; padding: 25px; border-radius: 8px; margin-top: 40px; }
                .agent-header { font-size: 20px; font-weight: bold; margin-bottom: 15px; }
                .contact-info { margin: 10px 0; font-size: 16px; }
                .qr-code { text-align: center; margin-top: 30px; }
                .footer { text-align: center; margin-top: 40px; color: #6b7280; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1 class="property-title">' . esc_html($data['title']) . '</h1>
                <div class="price">' . esc_html($data['price']) . '</div>
            </div>';
        
        if (!empty($data['images'])) {
            $html .= '<img src="' . esc_url($data['images'][0]['url']) . '" class="property-image" alt="Property Photo">';
        }
        
        $html .= '
            <div class="features">
                <div class="feature">
                    <div class="feature-number">' . esc_html($data['bedrooms']) . '</div>
                    <div class="feature-label">Bedrooms</div>
                </div>
                <div class="feature">
                    <div class="feature-number">' . esc_html($data['bathrooms']) . '</div>
                    <div class="feature-label">Bathrooms</div>
                </div>
                <div class="feature">
                    <div class="feature-number">' . esc_html($data['square_feet']) . '</div>
                    <div class="feature-label">Square Feet</div>
                </div>
            </div>
            
            <p><strong>📍 Location:</strong> ' . esc_html($data['address']) . '</p>
            
            <div class="description">
                <h3>Property Description</h3>
                <p>' . wp_kses_post($data['description']) . '</p>
            </div>
            
            <div class="agent-info">
                <div class="agent-header">Contact Your Agent</div>
                <div class="contact-info"><strong>' . esc_html($data['agent_name']) . '</strong></div>
                <div class="contact-info">📞 ' . esc_html($data['agent_phone']) . '</div>
                <div class="contact-info">📧 ' . esc_html($data['agent_email']) . '</div>
                ' . (!empty($data['agent_license']) ? '<div class="contact-info">License: ' . esc_html($data['agent_license']) . '</div>' : '') . '
            </div>
            
            <div class="qr-code">
                <p><strong>Scan for More Information:</strong></p>
                <img src="' . esc_url($data['qr_code']) . '" alt="QR Code" style="width: 150px; height: 150px;">
            </div>
            
            <div class="footer">
                <p>Generated by Happy Place Real Estate Platform</p>
                <p>Property ID: ' . esc_html($data['id']) . ' | Generated: ' . date('F j, Y g:i A') . '</p>
            </div>
        </body>
        </html>';
        
        return $html;
    }
    
    /**
     * Generate social media template
     */
    public function generate_social_template() {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'], 'hph_marketing_nonce') || 
            !current_user_can('generate_marketing_materials')) {
            wp_die(__('Security check failed', 'happy-place'));
        }
        
        $listing_id = intval($_POST['listing_id']);
        $platform = sanitize_text_field($_POST['platform']); // facebook, instagram, twitter
        $template = sanitize_text_field($_POST['template'] ?? 'default');
        
        try {
            $social_content = $this->create_social_template($listing_id, $platform, $template);
            
            if ($social_content) {
                $this->log_marketing_activity($listing_id, 'social_template', [
                    'platform' => $platform,
                    'template' => $template
                ]);
                
                wp_send_json_success([
                    'message' => __('Social media template generated', 'happy-place'),
                    'content' => $social_content
                ]);
            } else {
                wp_send_json_error(__('Failed to generate social template', 'happy-place'));
            }
        } catch (Exception $e) {
            hp_log('Marketing Service: Social template generation failed - ' . $e->getMessage(), 'error');
            wp_send_json_error(__('Template generation failed', 'happy-place'));
        }
    }
    
    /**
     * Create social media template content
     */
    private function create_social_template($listing_id, $platform, $template) {
        $data = $this->get_listing_marketing_data($listing_id);
        
        if (!$data) {
            return false;
        }
        
        $templates = [
            'facebook' => [
                'default' => "🏠 NEW LISTING ALERT! 🏠\n\n{title}\n💰 {price}\n🏠 {bedrooms} bed, {bathrooms} bath\n📐 {square_feet}\n📍 {address}\n\n{description}\n\nContact {agent_name} for details:\n📞 {agent_phone}\n📧 {agent_email}\n\n{listing_url}\n\n#RealEstate #NewListing #PropertyForSale",
                'luxury' => "✨ LUXURY PROPERTY SHOWCASE ✨\n\n{title}\n\n🌟 {price}\n🏠 {bedrooms} Bedrooms | {bathrooms} Bathrooms\n📏 {square_feet} of Pure Elegance\n🌍 {address}\n\n{description}\n\nExclusive showing with {agent_name}\n📞 {agent_phone}\n\n{listing_url}\n\n#LuxuryRealEstate #DreamHome #ExclusiveListing"
            ],
            'instagram' => [
                'default' => "🏠 Just Listed!\n\n{title}\n💰 {price}\n📍 {address}\n\n✨ {bedrooms}BR | {bathrooms}BA | {square_feet}\n\n{description}\n\nDM or call {agent_name}\n📞 {agent_phone}\n\n{listing_url}\n\n#JustListed #RealEstate #NewHome #PropertyGoals #YourNextHome",
                'story' => "🏠 NEW LISTING\n{title}\n💰 {price}\n📞 {agent_phone}"
            ],
            'twitter' => [
                'default' => "🏠 NEW LISTING: {title}\n💰 {price} | {bedrooms}BR/{bathrooms}BA | {square_feet}\n📍 {address}\n\nContact {agent_name}: {agent_phone}\n{listing_url}\n\n#RealEstate #NewListing #PropertyForSale",
                'concise' => "🏠 {title}\n💰 {price}\n📍 {address}\n📞 {agent_phone}\n{listing_url}\n#JustListed"
            ]
        ];
        
        $template_text = $templates[$platform][$template] ?? $templates[$platform]['default'] ?? '';
        
        if (!$template_text) {
            return false;
        }
        
        // Replace placeholders
        $replacements = [
            '{title}' => $data['title'],
            '{price}' => $data['price'],
            '{bedrooms}' => $data['bedrooms'],
            '{bathrooms}' => $data['bathrooms'],
            '{square_feet}' => $data['square_feet'],
            '{address}' => $data['address'],
            '{description}' => wp_trim_words($data['description'], 30),
            '{agent_name}' => $data['agent_name'],
            '{agent_phone}' => $data['agent_phone'],
            '{agent_email}' => $data['agent_email'],
            '{listing_url}' => $data['listing_url']
        ];
        
        $content = str_replace(array_keys($replacements), array_values($replacements), $template_text);
        
        return [
            'platform' => $platform,
            'template' => $template,
            'content' => $content,
            'hashtags' => $this->get_platform_hashtags($platform),
            'character_count' => strlen($content),
            'platform_limits' => $this->get_platform_limits($platform)
        ];
    }
    
    /**
     * Send marketing email
     */
    public function send_marketing_email() {
        // Implementation for email marketing
        wp_send_json_success(['message' => 'Email marketing feature coming soon']);
    }
    
    /**
     * Get available marketing templates
     */
    public function get_marketing_templates() {
        if (!current_user_can('generate_marketing_materials')) {
            wp_send_json_error(__('Permission denied', 'happy-place'));
        }
        
        $templates = [
            'pdf' => [
                'modern' => __('Modern Layout', 'happy-place'),
                'classic' => __('Classic Style', 'happy-place'),
                'luxury' => __('Luxury Theme', 'happy-place')
            ],
            'social' => [
                'facebook' => [
                    'default' => __('Standard Post', 'happy-place'),
                    'luxury' => __('Luxury Showcase', 'happy-place')
                ],
                'instagram' => [
                    'default' => __('Feed Post', 'happy-place'),
                    'story' => __('Story Template', 'happy-place')
                ],
                'twitter' => [
                    'default' => __('Standard Tweet', 'happy-place'),
                    'concise' => __('Concise Version', 'happy-place')
                ]
            ],
            'email' => [
                'listing_announcement' => __('New Listing Announcement', 'happy-place'),
                'open_house_invite' => __('Open House Invitation', 'happy-place'),
                'price_reduction' => __('Price Reduction Alert', 'happy-place')
            ]
        ];
        
        wp_send_json_success($templates);
    }
    
    /**
     * Log marketing activity
     */
    private function log_marketing_activity($listing_id, $activity_type, $data = []) {
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'hp_marketing_activity',
            [
                'listing_id' => $listing_id,
                'user_id' => get_current_user_id(),
                'activity_type' => $activity_type,
                'activity_data' => json_encode($data),
                'created_at' => current_time('mysql')
            ],
            ['%d', '%d', '%s', '%s', '%s']
        );
    }
    
    /**
     * Generate QR code for listing
     */
    private function generate_qr_code($url) {
        // Simple implementation - in production, use proper QR library
        return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($url);
    }
    
    /**
     * Get PDF download URL
     */
    private function get_pdf_download_url($file_path) {
        $upload_dir = wp_upload_dir();
        return str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $file_path);
    }
    
    /**
     * Get platform hashtags
     */
    private function get_platform_hashtags($platform) {
        $hashtags = [
            'facebook' => ['#RealEstate', '#NewListing', '#PropertyForSale', '#DreamHome'],
            'instagram' => ['#JustListed', '#RealEstate', '#NewHome', '#PropertyGoals', '#YourNextHome', '#DreamHome'],
            'twitter' => ['#RealEstate', '#NewListing', '#PropertyForSale', '#JustListed']
        ];
        
        return $hashtags[$platform] ?? [];
    }
    
    /**
     * Get platform character limits
     */
    private function get_platform_limits($platform) {
        return [
            'facebook' => ['characters' => 63206, 'recommended' => 400],
            'instagram' => ['characters' => 2200, 'recommended' => 125],
            'twitter' => ['characters' => 280, 'recommended' => 280]
        ][$platform] ?? ['characters' => 1000, 'recommended' => 200];
    }
    
    /**
     * Get service stats
     */
    public function get_stats(): array {
        global $wpdb;
        
        $stats = [];
        
        // Get activity counts
        $activity_counts = $wpdb->get_results(
            "SELECT activity_type, COUNT(*) as count 
             FROM {$wpdb->prefix}hp_marketing_activity 
             WHERE user_id = " . get_current_user_id() . "
             GROUP BY activity_type"
        );
        
        foreach ($activity_counts as $activity) {
            $stats[$activity->activity_type] = intval($activity->count);
        }
        
        return $stats;
    }
}