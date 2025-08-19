<?php
/**
 * Marketing Suite Generator
 * Generates marketing materials for listings
 *
 * @package HappyPlace
 */

namespace HappyPlace\Marketing;

if (!defined('ABSPATH')) {
    exit;
}

class Marketing_Generator {

    private array $format_configs;
    private array $template_configs;
    private string $uploads_dir;
    private string $marketing_dir;

    public function __construct() {
        $this->init_format_configs();
        $this->init_template_configs();
        $this->setup_directories();
    }

    private function init_format_configs(): void {
        $this->format_configs = [
            'full_flyer' => [
                'width' => 2550,
                'height' => 3300,
                'dpi' => 300,
                'name' => 'Full Flyer (8.5x11")',
                'format' => 'pdf'
            ],
            'instagram_post' => [
                'width' => 1080,
                'height' => 1080,
                'dpi' => 72,
                'name' => 'Instagram Post',
                'format' => 'jpg'
            ],
            'instagram_story' => [
                'width' => 1080,
                'height' => 1920,
                'dpi' => 72,
                'name' => 'Instagram Story',
                'format' => 'jpg'
            ],
            'facebook_post' => [
                'width' => 1200,
                'height' => 630,
                'dpi' => 72,
                'name' => 'Facebook Post',
                'format' => 'jpg'
            ],
            'twitter_post' => [
                'width' => 1024,
                'height' => 512,
                'dpi' => 72,
                'name' => 'Twitter Post',
                'format' => 'jpg'
            ],
            'email_header' => [
                'width' => 600,
                'height' => 200,
                'dpi' => 72,
                'name' => 'Email Header',
                'format' => 'jpg'
            ]
        ];
    }

    private function init_template_configs(): void {
        $this->template_configs = [
            'modern' => [
                'name' => 'Modern',
                'colors' => ['#51bae0', '#ffffff', '#2c3e50'],
                'fonts' => ['Poppins', 'Open Sans']
            ],
            'classic' => [
                'name' => 'Classic',
                'colors' => ['#2c3e50', '#ecf0f1', '#3498db'],
                'fonts' => ['Georgia', 'Arial']
            ],
            'luxury' => [
                'name' => 'Luxury',
                'colors' => ['#d4af37', '#000000', '#ffffff'],
                'fonts' => ['Playfair Display', 'Lato']
            ],
            'minimal' => [
                'name' => 'Minimal',
                'colors' => ['#333333', '#ffffff', '#f8f9fa'],
                'fonts' => ['Helvetica', 'Arial']
            ]
        ];
    }

    private function setup_directories(): void {
        $upload_dir = wp_upload_dir();
        $this->uploads_dir = $upload_dir['basedir'];
        $this->marketing_dir = $this->uploads_dir . '/marketing';
        
        // Create marketing directory if it doesn't exist
        if (!file_exists($this->marketing_dir)) {
            wp_mkdir_p($this->marketing_dir);
        }
    }

    public function generate_materials($listing_id, $format, $template = 'modern'): array {
        try {
            // Validate inputs
            if (!$this->is_valid_format($format)) {
                return ['success' => false, 'message' => 'Invalid format specified'];
            }

            if (!$this->is_valid_template($template)) {
                return ['success' => false, 'message' => 'Invalid template specified'];
            }

            // Get listing data
            $listing_data = $this->get_listing_data($listing_id);
            if (!$listing_data) {
                return ['success' => false, 'message' => 'Listing not found'];
            }

            // Generate material
            $result = $this->create_marketing_material($listing_data, $format, $template);
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'download_url' => $result['download_url'],
                    'preview_url' => $result['preview_url'],
                    'file_path' => $result['file_path']
                ];
            } else {
                return ['success' => false, 'message' => $result['message']];
            }

        } catch (\Exception $e) {
            error_log('Marketing Generator Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to generate marketing material'];
        }
    }

    private function create_marketing_material($listing_data, $format, $template): array {
        $config = $this->format_configs[$format];
        $template_config = $this->template_configs[$template];
        
        // Create filename
        $filename = $this->generate_filename($listing_data, $format, $template);
        $file_path = $this->marketing_dir . '/' . $filename;
        
        // Generate material based on format
        switch ($format) {
            case 'full_flyer':
                $result = $this->generate_flyer($listing_data, $config, $template_config, $file_path);
                break;
                
            case 'instagram_post':
            case 'instagram_story':
            case 'facebook_post':
            case 'twitter_post':
                $result = $this->generate_social_media_post($listing_data, $config, $template_config, $file_path);
                break;
                
            case 'email_header':
                $result = $this->generate_email_header($listing_data, $config, $template_config, $file_path);
                break;
                
            default:
                return ['success' => false, 'message' => 'Unsupported format'];
        }

        if ($result) {
            $upload_dir = wp_upload_dir();
            $relative_path = str_replace($upload_dir['basedir'], '', $file_path);
            
            return [
                'success' => true,
                'file_path' => $file_path,
                'download_url' => $upload_dir['baseurl'] . $relative_path,
                'preview_url' => $upload_dir['baseurl'] . $relative_path
            ];
        } else {
            return ['success' => false, 'message' => 'Failed to generate material'];
        }
    }

    private function generate_flyer($listing_data, $config, $template_config, $file_path): bool {
        // For now, create a simple HTML-based flyer and convert to PDF
        $html = $this->generate_flyer_html($listing_data, $config, $template_config);
        
        // In a real implementation, you would use a library like TCPDF or Dompdf
        // For now, we'll create a mock PDF file
        $pdf_content = $this->create_mock_pdf($html);
        
        return file_put_contents($file_path, $pdf_content) !== false;
    }

    private function generate_social_media_post($listing_data, $config, $template_config, $file_path): bool {
        // Create canvas-based image
        $canvas_data = $this->create_social_media_canvas($listing_data, $config, $template_config);
        
        // In a real implementation, you would use GD or ImageMagick
        // For now, create a mock image
        $image_content = $this->create_mock_image($canvas_data, $config);
        
        return file_put_contents($file_path, $image_content) !== false;
    }

    private function generate_email_header($listing_data, $config, $template_config, $file_path): bool {
        // Create email header image
        $canvas_data = $this->create_email_header_canvas($listing_data, $config, $template_config);
        
        // In a real implementation, you would use image manipulation libraries
        $image_content = $this->create_mock_image($canvas_data, $config);
        
        return file_put_contents($file_path, $image_content) !== false;
    }

    private function generate_flyer_html($listing_data, $config, $template_config): string {
        $primary_color = $template_config['colors'][0];
        $secondary_color = $template_config['colors'][1];
        $accent_color = $template_config['colors'][2];
        $primary_font = $template_config['fonts'][0];
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <style>
        body { 
            font-family: "' . $primary_font . '", sans-serif; 
            margin: 0; 
            padding: 40px; 
            background: ' . $secondary_color . ';
            color: ' . $accent_color . ';
        }
        .flyer-container { 
            width: 100%; 
            max-width: 8.5in; 
            background: white; 
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header { 
            background: ' . $primary_color . '; 
            color: white; 
            padding: 30px; 
            text-align: center; 
        }
        .price { 
            font-size: 2.5em; 
            font-weight: bold; 
            margin: 0; 
        }
        .address { 
            font-size: 1.2em; 
            margin: 10px 0; 
        }
        .details { 
            padding: 30px; 
        }
        .features { 
            display: flex; 
            justify-content: space-around; 
            margin: 20px 0; 
        }
        .feature { 
            text-align: center; 
        }
        .feature-value { 
            font-size: 2em; 
            font-weight: bold; 
            color: ' . $primary_color . '; 
        }
        .description { 
            margin: 20px 0; 
            line-height: 1.6; 
        }
        .contact { 
            background: ' . $accent_color . '; 
            color: white; 
            padding: 20px; 
            text-align: center; 
        }
    </style>
</head>
<body>
    <div class="flyer-container">
        <div class="header">
            <h1 class="price">$' . number_format($listing_data['price']) . '</h1>
            <div class="address">' . htmlspecialchars($listing_data['address']) . '</div>
            <div class="address">' . htmlspecialchars($listing_data['city'] . ', ' . $listing_data['state']) . '</div>
        </div>
        
        <div class="details">
            <div class="features">
                <div class="feature">
                    <div class="feature-value">' . $listing_data['bedrooms'] . '</div>
                    <div>Bedrooms</div>
                </div>
                <div class="feature">
                    <div class="feature-value">' . $listing_data['bathrooms'] . '</div>
                    <div>Bathrooms</div>
                </div>
                <div class="feature">
                    <div class="feature-value">' . number_format($listing_data['square_feet']) . '</div>
                    <div>Sq Ft</div>
                </div>
            </div>
            
            <div class="description">
                ' . wpautop($listing_data['description']) . '
            </div>
        </div>
        
        <div class="contact">
            <h3>Contact ' . htmlspecialchars($listing_data['agent_name']) . '</h3>
            <p>Phone: ' . htmlspecialchars($listing_data['agent_phone']) . '</p>
            <p>Email: ' . htmlspecialchars($listing_data['agent_email']) . '</p>
        </div>
    </div>
</body>
</html>';

        return $html;
    }

    private function create_social_media_canvas($listing_data, $config, $template_config): array {
        return [
            'width' => $config['width'],
            'height' => $config['height'],
            'elements' => [
                [
                    'type' => 'background',
                    'color' => $template_config['colors'][1]
                ],
                [
                    'type' => 'text',
                    'content' => '$' . number_format($listing_data['price']),
                    'font' => $template_config['fonts'][0],
                    'size' => 48,
                    'color' => $template_config['colors'][0],
                    'x' => 50,
                    'y' => 100
                ],
                [
                    'type' => 'text',
                    'content' => $listing_data['address'],
                    'font' => $template_config['fonts'][1],
                    'size' => 24,
                    'color' => $template_config['colors'][2],
                    'x' => 50,
                    'y' => 160
                ],
                [
                    'type' => 'features',
                    'content' => $listing_data['bedrooms'] . ' bed • ' . $listing_data['bathrooms'] . ' bath • ' . number_format($listing_data['square_feet']) . ' sq ft',
                    'font' => $template_config['fonts'][1],
                    'size' => 20,
                    'color' => $template_config['colors'][2],
                    'x' => 50,
                    'y' => 200
                ]
            ]
        ];
    }

    private function create_email_header_canvas($listing_data, $config, $template_config): array {
        return [
            'width' => $config['width'],
            'height' => $config['height'],
            'elements' => [
                [
                    'type' => 'background',
                    'color' => $template_config['colors'][0]
                ],
                [
                    'type' => 'text',
                    'content' => 'New Listing: ' . $listing_data['address'],
                    'font' => $template_config['fonts'][0],
                    'size' => 24,
                    'color' => $template_config['colors'][1],
                    'x' => 20,
                    'y' => 100
                ],
                [
                    'type' => 'text',
                    'content' => '$' . number_format($listing_data['price']),
                    'font' => $template_config['fonts'][0],
                    'size' => 32,
                    'color' => $template_config['colors'][1],
                    'x' => 20,
                    'y' => 140
                ]
            ]
        ];
    }

    private function create_mock_pdf($html): string {
        // In a real implementation, this would use TCPDF, Dompdf, or similar
        // For now, return mock PDF content
        return "%PDF-1.4\n" . base64_encode($html) . "\n%%EOF";
    }

    private function create_mock_image($canvas_data, $config): string {
        // In a real implementation, this would use GD, ImageMagick, or similar
        // For now, create a simple mock image file
        $width = $config['width'];
        $height = $config['height'];
        
        // Create mock PNG data
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
    }

    private function get_listing_data($listing_id): ?array {
        $listing = get_post($listing_id);
        if (!$listing) {
            return null;
        }

        // Get ACF fields
        $price = get_field('price', $listing_id) ?: 0;
        $bedrooms = get_field('bedrooms', $listing_id) ?: 0;
        $bathrooms = get_field('bathrooms', $listing_id) ?: 0;
        $square_feet = get_field('square_feet', $listing_id) ?: 0;
        $address = get_field('street_address', $listing_id) ?: $listing->post_title;
        $city = get_field('city', $listing_id) ?: '';
        $state = get_field('state', $listing_id) ?: '';
        
        // Get agent data
        $listing_agent = get_field('listing_agent', $listing_id);
        $agent_name = '';
        $agent_phone = '';
        $agent_email = '';
        
        if ($listing_agent) {
            $agent = is_array($listing_agent) ? $listing_agent[0] : $listing_agent;
            if ($agent) {
                $agent_name = get_field('first_name', $agent->ID) . ' ' . get_field('last_name', $agent->ID);
                $agent_phone = get_field('phone', $agent->ID) ?: '';
                $agent_email = get_field('email', $agent->ID) ?: '';
            }
        }

        return [
            'id' => $listing_id,
            'title' => $listing->post_title,
            'description' => $listing->post_content,
            'price' => $price,
            'bedrooms' => $bedrooms,
            'bathrooms' => $bathrooms,
            'square_feet' => $square_feet,
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'agent_name' => $agent_name,
            'agent_phone' => $agent_phone,
            'agent_email' => $agent_email,
            'featured_image' => get_field('featured_image', $listing_id)
        ];
    }

    private function generate_filename($listing_data, $format, $template): string {
        $address_slug = sanitize_title($listing_data['address']);
        $timestamp = date('Y-m-d-H-i-s');
        $extension = $this->format_configs[$format]['format'];
        
        return "{$address_slug}-{$format}-{$template}-{$timestamp}.{$extension}";
    }

    private function is_valid_format($format): bool {
        return array_key_exists($format, $this->format_configs);
    }

    private function is_valid_template($template): bool {
        return array_key_exists($template, $this->template_configs);
    }

    public function get_available_formats(): array {
        return $this->format_configs;
    }

    public function get_available_templates(): array {
        return $this->template_configs;
    }

    public function bulk_generate($listing_id, $formats = [], $template = 'modern'): array {
        if (empty($formats)) {
            $formats = array_keys($this->format_configs);
        }

        $results = [];
        $success_count = 0;
        $files = [];

        foreach ($formats as $format) {
            $result = $this->generate_materials($listing_id, $format, $template);
            $results[$format] = $result;
            
            if ($result['success']) {
                $success_count++;
                $files[] = $result['file_path'];
            }
        }

        // Create ZIP file if multiple formats generated
        if ($success_count > 1) {
            $zip_path = $this->create_zip_archive($files, $listing_id);
            if ($zip_path) {
                $upload_dir = wp_upload_dir();
                $relative_path = str_replace($upload_dir['basedir'], '', $zip_path);
                
                return [
                    'success' => true,
                    'generated_count' => $success_count,
                    'total_count' => count($formats),
                    'zip_download_url' => $upload_dir['baseurl'] . $relative_path,
                    'individual_results' => $results
                ];
            }
        }

        return [
            'success' => $success_count > 0,
            'generated_count' => $success_count,
            'total_count' => count($formats),
            'results' => $results
        ];
    }

    private function create_zip_archive($files, $listing_id): ?string {
        if (!class_exists('ZipArchive')) {
            return null;
        }

        $zip = new \ZipArchive();
        $zip_filename = "marketing-materials-{$listing_id}-" . date('Y-m-d-H-i-s') . '.zip';
        $zip_path = $this->marketing_dir . '/' . $zip_filename;

        if ($zip->open($zip_path, \ZipArchive::CREATE) === TRUE) {
            foreach ($files as $file) {
                if (file_exists($file)) {
                    $zip->addFile($file, basename($file));
                }
            }
            $zip->close();
            return $zip_path;
        }

        return null;
    }
}