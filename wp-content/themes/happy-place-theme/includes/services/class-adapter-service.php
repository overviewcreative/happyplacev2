<?php
/**
 * Adapter Service
 * 
 * Centralized service for managing all data adapters between
 * bridge functions and base components
 */

namespace HappyPlaceTheme\Services;

class AdapterService {
    
    /**
     * Singleton instance
     */
    private static $instance = null;
    
    /**
     * Registered adapters
     */
    private $adapters = [];
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor - Register all adapters
     */
    private function __construct() {
        $this->register_adapters();
    }
    
    /**
     * Register all available adapters
     */
    private function register_adapters() {
        // Card adapters
        $this->adapters['listing_card'] = [$this, 'adapt_listing_card'];
        $this->adapters['agent_card'] = [$this, 'adapt_agent_card'];
        
        // Grid adapters
        $this->adapters['listing_grid'] = [$this, 'adapt_listing_grid'];
        $this->adapters['agent_grid'] = [$this, 'adapt_agent_grid'];
        
        // Single page section adapters
        $this->adapters['listing_hero'] = [$this, 'adapt_listing_hero'];
        $this->adapters['agent_hero'] = [$this, 'adapt_agent_hero'];
        $this->adapters['listing_details'] = [$this, 'adapt_listing_details'];
        $this->adapters['agent_stats'] = [$this, 'adapt_agent_stats'];
        
        // Form adapters
        $this->adapters['listing_contact_form'] = [$this, 'adapt_listing_contact_form'];
        $this->adapters['agent_contact_form'] = [$this, 'adapt_agent_contact_form'];
        
        // Allow extensions
        $this->adapters = apply_filters('hpt_registered_adapters', $this->adapters);
    }
    
    /**
     * Get adapter function
     */
    public function get_adapter($name) {
        return $this->adapters[$name] ?? null;
    }
    
    /**
     * Transform data using specified adapter
     */
    public function transform($adapter_name, $data, $options = []) {
        $adapter = $this->get_adapter($adapter_name);
        
        if (!$adapter || !is_callable($adapter)) {
            return null;
        }
        
        return call_user_func($adapter, $data, $options);
    }
    
    /**
     * Adapt listing data for card component
     */
    public function adapt_listing_card($listing_id, $options = []) {
        // Get data from bridge functions
        $price = hpt_get_listing_price($listing_id);
        $status = hpt_get_listing_status($listing_id);
        $bedrooms = hpt_get_listing_bedrooms($listing_id);
        $bathrooms = hpt_get_listing_bathrooms($listing_id);
        $sqft = hpt_get_listing_square_feet($listing_id);
        $address = hpt_get_listing_address($listing_id);
        $featured_image = hpt_get_listing_featured_image($listing_id);
        $property_type = hpt_get_listing_property_type($listing_id);
        
        // Build card props
        return [
            'variant' => $options['variant'] ?? 'elevated',
            'layout' => $options['layout'] ?? 'vertical',
            'size' => $options['size'] ?? 'md',
            
            'image' => [
                'src' => $featured_image ?: get_template_directory_uri() . '/assets/images/no-image.jpg',
                'alt' => get_the_title($listing_id),
                'ratio' => 'landscape',
                'position' => 'top'
            ],
            
            'title' => [
                'text' => get_the_title($listing_id),
                'tag' => 'h3',
                'link' => get_permalink($listing_id)
            ],
            
            'subtitle' => $address['city'] ?? '',
            
            'description' => wp_trim_words(hpt_get_listing_description($listing_id), 20),
            
            'badges' => array_filter([
                $status ? [
                    'text' => hpt_get_listing_status_label($listing_id),
                    'variant' => $this->get_status_variant($status)
                ] : null,
                $property_type ? [
                    'text' => $property_type,
                    'variant' => 'default'
                ] : null
            ]),
            
            'meta_items' => array_filter([
                $price ? [
                    'icon' => 'dollar-sign',
                    'text' => hpt_get_listing_price_formatted($listing_id),
                    'prominent' => true
                ] : null,
                $bedrooms ? [
                    'icon' => 'bed',
                    'text' => $bedrooms . ' bed' . ($bedrooms != 1 ? 's' : '')
                ] : null,
                $bathrooms ? [
                    'icon' => 'bath', 
                    'text' => $bathrooms . ' bath' . ($bathrooms != 1 ? 's' : '')
                ] : null,
                $sqft ? [
                    'icon' => 'maximize',
                    'text' => number_format($sqft) . ' sqft'
                ] : null
            ]),
            
            'actions' => [
                [
                    'text' => 'View Details',
                    'href' => get_permalink($listing_id),
                    'variant' => 'primary',
                    'size' => 'sm'
                ],
                [
                    'text' => 'Save',
                    'variant' => 'ghost',
                    'size' => 'sm',
                    'icon' => 'heart',
                    'data' => ['listing-id' => $listing_id],
                    'class' => 'hpt-save-listing'
                ]
            ],
            
            'link_wrapper' => get_permalink($listing_id),
            'hover_effect' => 'lift',
            'class' => 'hpt-listing-card hpt-listing-' . $status,
            
            // Pass through raw data for custom use
            'data' => [
                'listing-id' => $listing_id,
                'listing-status' => $status,
                'listing-price' => $price
            ]
        ];
    }
    
    /**
     * Adapt agent data for card component
     */
    public function adapt_agent_card($agent_id, $options = []) {
        // Get data from bridge
        $name = get_the_title($agent_id);
        $phone = hpt_get_agent_phone($agent_id);
        $email = hpt_get_agent_email($agent_id);
        $photo = hpt_get_agent_photo($agent_id);
        $title = hpt_get_agent_title($agent_id);
        $bio = hpt_get_agent_bio($agent_id);
        $active = hpt_get_agent_active_listings_count($agent_id);
        $sold = hpt_get_agent_sold_listings_count($agent_id);
        $featured = hpt_is_agent_featured($agent_id);
        $rating = hpt_get_agent_rating($agent_id);
        
        // Handle photo - could be URL string or array from ACF
        $photo_url = '';
        if (is_array($photo) && isset($photo['url'])) {
            $photo_url = $photo['url'];
        } elseif (is_string($photo)) {
            $photo_url = $photo;
        } elseif (is_numeric($photo)) {
            $photo_url = wp_get_attachment_image_url($photo, 'medium');
        }
        
        // Fallback to featured image if no photo
        if (empty($photo_url)) {
            $photo_url = get_the_post_thumbnail_url($agent_id, 'large');
        }
        
        // Final fallback to default
        if (empty($photo_url)) {
            $photo_url = get_template_directory_uri() . '/assets/images/default-avatar.jpg';
        }
        
        return [
            'variant' => $options['variant'] ?? 'bordered',
            'layout' => $options['layout'] ?? 'vertical',
            
            'image' => [
                'src' => $photo_url ?: get_template_directory_uri() . '/assets/images/default-avatar.jpg',
                'alt' => $name,
                'ratio' => 'square'
            ],
            
            'title' => [
                'text' => $name,
                'tag' => 'h3',
                'link' => get_permalink($agent_id)
            ],
            
            'subtitle' => $title,
            
            'description' => wp_trim_words($bio, 15),
            
            'badges' => array_filter([
                $featured ? [
                    'text' => 'Featured',
                    'variant' => 'success',
                    'icon' => 'star'
                ] : null,
                $rating >= 4.5 ? [
                    'text' => 'Top Rated',
                    'variant' => 'primary'
                ] : null
            ]),
            
            'meta_items' => array_filter([
                $active !== null ? [
                    'icon' => 'home',
                    'text' => $active . ' active'
                ] : null,
                $sold !== null ? [
                    'icon' => 'check-circle',
                    'text' => $sold . ' sold'
                ] : null,
                $rating ? [
                    'icon' => 'star',
                    'text' => number_format($rating, 1) . ' rating'
                ] : null,
                $phone ? [
                    'icon' => 'phone',
                    'text' => $phone
                ] : null
            ]),
            
            'actions' => array_filter([
                [
                    'text' => 'View Profile',
                    'href' => get_permalink($agent_id),
                    'variant' => 'primary',
                    'size' => 'sm'
                ],
                $phone ? [
                    'text' => 'Call',
                    'href' => 'tel:' . $phone,
                    'variant' => 'outline',
                    'size' => 'sm',
                    'icon' => 'phone'
                ] : ($email ? [
                    'text' => 'Email',
                    'href' => 'mailto:' . $email,
                    'variant' => 'outline',
                    'size' => 'sm',
                    'icon' => 'envelope'
                ] : null)
            ]),
            
            'hover_effect' => 'lift',
            'class' => 'hpt-agent-card'
        ];
    }
    
    /**
     * Helper: Get status variant
     */
    private function get_status_variant($status) {
        $variants = [
            'active' => 'success',
            'pending' => 'warning',
            'sold' => 'error',
            'under_contract' => 'info'
        ];
        
        return $variants[$status] ?? 'default';
    }
    
    /**
     * Adapt listing data for hero component
     */
    public function adapt_listing_hero($listing_id, $options = []) {
        // Validate input
        if (!$listing_id || !is_numeric($listing_id)) {
            return null;
        }
        
        // Get comprehensive data from bridge functions with safe fallbacks
        $title = function_exists('hpt_get_listing_title') ? hpt_get_listing_title($listing_id) : get_the_title($listing_id);
        $price = function_exists('hpt_get_listing_price_formatted') ? hpt_get_listing_price_formatted($listing_id) : '';
        $price_raw = function_exists('hpt_get_listing_price_raw') ? hpt_get_listing_price_raw($listing_id) : 0;
        $status = function_exists('hpt_get_listing_status') ? hpt_get_listing_status($listing_id) : '';
        $status_label = function_exists('hpt_get_listing_status_label') ? hpt_get_listing_status_label($listing_id) : '';
        
        // Address components for hero display with safe fallbacks
        $street_address = function_exists('hpt_get_listing_street_address') ? hpt_get_listing_street_address($listing_id) : '';
        $city = function_exists('hpt_get_listing_city') ? hpt_get_listing_city($listing_id) : '';
        $state = function_exists('hpt_get_listing_state') ? hpt_get_listing_state($listing_id) : '';
        $zip = function_exists('hpt_get_listing_zip_code') ? hpt_get_listing_zip_code($listing_id) : '';
        
        // Key property stats with safe fallbacks
        $bedrooms = function_exists('hpt_get_listing_bedrooms') ? hpt_get_listing_bedrooms($listing_id) : 0;
        $bathrooms = function_exists('hpt_get_listing_bathrooms_formatted') ? hpt_get_listing_bathrooms_formatted($listing_id) : '';
        $square_feet = function_exists('hpt_get_listing_square_feet_formatted') ? hpt_get_listing_square_feet_formatted($listing_id) : '';
        $lot_size = function_exists('hpt_get_listing_lot_size_formatted') ? hpt_get_listing_lot_size_formatted($listing_id) : '';
        $property_type = function_exists('hpt_get_listing_property_type_label') ? hpt_get_listing_property_type_label($listing_id) : '';
        
        // Gallery for carousel background with safe fallbacks
        $featured_image = function_exists('hpt_get_listing_featured_image') ? hpt_get_listing_featured_image($listing_id, 'large') : get_the_post_thumbnail_url($listing_id, 'large');
        $gallery = function_exists('hpt_get_listing_gallery') ? hpt_get_listing_gallery($listing_id) : [];
        
        // Calculate "Updated X Days Ago" with error handling
        $listing_date = function_exists('hpt_get_listing_date') ? hpt_get_listing_date($listing_id) : get_the_date('Y-m-d H:i:s', $listing_id);
        $days_ago = '';
        if ($listing_date) {
            try {
                $date_obj = new \DateTime($listing_date);
                $now = new \DateTime();
                $diff = $now->diff($date_obj);
                
                if ($diff->days == 0) {
                    $days_ago = 'Today';
                } elseif ($diff->days == 1) {
                    $days_ago = '1 day ago';
                } else {
                    $days_ago = $diff->days . ' days ago';
                }
            } catch (Exception $e) {
                // Fallback if date parsing fails - use post date
                $post_date = get_the_date('Y-m-d', $listing_id);
                $days_ago = $post_date ? 'Listed ' . date('M j', strtotime($post_date)) : '';
            }
        }
        
        // Build carousel images
        $carousel_images = [];
        if ($featured_image) {
            $carousel_images[] = [
                'url' => $featured_image,
                'alt' => $title ?: 'Property Image',
                'caption' => ''
            ];
        }
        
        if (!empty($gallery)) {
            foreach (array_slice($gallery, 0, 8) as $image) { // Limit to 8 images total
                if (is_array($image) && isset($image['url']) && $image['url'] !== $featured_image) {
                    $carousel_images[] = [
                        'url' => $image['url'],
                        'alt' => $image['alt'] ?: $title ?: 'Property Image',
                        'caption' => $image['caption'] ?? ''
                    ];
                }
            }
        }
        
        // Build address display
        $address_parts = array_filter([$street_address, $city, $state, $zip]);
        $full_address = implode(', ', $address_parts);
        
        // Build key stats for display
        $key_stats = array_filter([
            $bedrooms ? $bedrooms . ' bed' . ($bedrooms > 1 ? 's' : '') : null,
            $bathrooms ?: null,
            $square_feet ? $square_feet : null,
            $lot_size ? $lot_size . ' lot' : null
        ]);
        
        return [
            // Content
            'title' => $title ?: 'Property Listing',
            'address' => $full_address,
            'price' => $price ?: 'Contact for Price',
            'price_raw' => $price_raw,
            
            // Property details
            'bedrooms' => $bedrooms,
            'bathrooms' => $bathrooms,
            'square_feet' => $square_feet,
            'lot_size' => $lot_size,
            'property_type' => $property_type,
            'key_stats' => $key_stats,
            
            // Status and badges
            'status' => $status,
            'status_label' => $status_label,
            'status_badge' => [
                'text' => $status_label ?: $status,
                'variant' => $this->get_status_variant($status),
                'position' => 'top-left'
            ],
            'updated_badge' => $days_ago ? [
                'text' => 'Updated ' . $days_ago,
                'variant' => 'info',
                'position' => 'top-right'
            ] : null,
            
            // Visual content
            'carousel_images' => $carousel_images,
            'featured_image' => $featured_image,
            'has_gallery' => !empty($gallery),
            
            // Layout options
            'layout' => $options['layout'] ?? 'full-width',
            'variant' => $options['variant'] ?? 'overlay',
            'height' => $options['height'] ?? 'large', // small, medium, large, full
            
            // Interaction
            'show_gallery_nav' => count($carousel_images) > 1,
            'show_share_button' => $options['show_share'] ?? true,
            'show_save_button' => $options['show_save'] ?? true,
            
            // HTML attributes
            'class' => 'hph-listing-hero',
            'data' => [
                'listing-id' => $listing_id,
                'listing-status' => $status,
                'listing-price' => $price_raw ?: 0,
                'gallery-count' => count($carousel_images)
            ]
        ];
    }
    
    /**
     * Batch transform multiple items
     */
    public function transform_batch($adapter_name, $items, $options = []) {
        $results = [];
        
        foreach ($items as $item) {
            $id = is_object($item) ? $item->ID : $item;
            $results[] = $this->transform($adapter_name, $id, $options);
        }
        
        return $results;
    }
}