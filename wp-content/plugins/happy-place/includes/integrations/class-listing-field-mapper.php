<?php
/**
 * Listing Field Mapper
 * Maps fields between WordPress listings and Airtable records
 *
 * @package HappyPlace
 */

namespace HappyPlace\Integrations;

use Exception;

if (!defined('ABSPATH')) {
    exit;
}

class Listing_Field_Mapper {

    private $field_mapping;
    private $media_mapping;

    public function __construct() {
        $this->init_field_mapping();
        $this->init_media_mapping();
    }

    private function init_field_mapping() {
        $this->field_mapping = [
            // Basic post fields
            'post_title' => [
                'field' => 'Address',
                'type' => 'text',
                'direction' => 'both'
            ],
            'post_content' => [
                'field' => 'Description',
                'type' => 'text',
                'direction' => 'both'
            ],
            'post_status' => [
                'field' => 'Status',
                'type' => 'select',
                'direction' => 'both',
                'options' => [
                    'publish' => 'Published',
                    'draft' => 'Draft',
                    'private' => 'Private'
                ]
            ],

            // ACF Fields - Basic Information
            'price' => [
                'field' => 'Price',
                'type' => 'number',
                'direction' => 'both'
            ],
            'listing_status' => [
                'field' => 'Listing Status',
                'type' => 'select',
                'direction' => 'both',
                'options' => [
                    'active' => 'Active',
                    'pending' => 'Pending',
                    'sold' => 'Sold',
                    'coming_soon' => 'Coming Soon',
                    'off_market' => 'Off Market'
                ]
            ],
            'mls_number' => [
                'field' => 'MLS Number',
                'type' => 'text',
                'direction' => 'both'
            ],
            'year_built' => [
                'field' => 'Year Built',
                'type' => 'number',
                'direction' => 'both'
            ],

            // Property Details
            'bedrooms' => [
                'field' => 'Bedrooms',
                'type' => 'number',
                'direction' => 'both'
            ],
            'bathrooms' => [
                'field' => 'Bathrooms',
                'type' => 'number',
                'direction' => 'both'
            ],
            'square_feet' => [
                'field' => 'Square Feet',
                'type' => 'number',
                'direction' => 'both'
            ],
            'lot_size' => [
                'field' => 'Lot Size',
                'type' => 'number',
                'direction' => 'both'
            ],
            'garage_spaces' => [
                'field' => 'Garage Spaces',
                'type' => 'number',
                'direction' => 'both'
            ],

            // Address Information
            'street_address' => [
                'field' => 'Street Address',
                'type' => 'text',
                'direction' => 'both'
            ],
            'city' => [
                'field' => 'City',
                'type' => 'text',
                'direction' => 'both'
            ],
            'state' => [
                'field' => 'State',
                'type' => 'text',
                'direction' => 'both'
            ],
            'zip_code' => [
                'field' => 'ZIP Code',
                'type' => 'text',
                'direction' => 'both'
            ],
            'county' => [
                'field' => 'County',
                'type' => 'text',
                'direction' => 'both'
            ],
            'latitude' => [
                'field' => 'Latitude',
                'type' => 'number',
                'direction' => 'both'
            ],
            'longitude' => [
                'field' => 'Longitude',
                'type' => 'number',
                'direction' => 'both'
            ],

            // Financial Information
            'hoa_fees' => [
                'field' => 'HOA Fees',
                'type' => 'number',
                'direction' => 'both'
            ],
            'property_taxes' => [
                'field' => 'Property Taxes',
                'type' => 'number',
                'direction' => 'both'
            ],
            'buyer_agent_commission' => [
                'field' => 'Buyer Agent Commission',
                'type' => 'number',
                'direction' => 'both'
            ],

            // Property Features (Multi-select)
            'property_features' => [
                'field' => 'Property Features',
                'type' => 'multiselect',
                'direction' => 'both'
            ],
            'interior_features' => [
                'field' => 'Interior Features',
                'type' => 'multiselect',
                'direction' => 'both'
            ],
            'exterior_features' => [
                'field' => 'Exterior Features',
                'type' => 'multiselect',
                'direction' => 'both'
            ],

            // Boolean fields
            'has_pool' => [
                'field' => 'Has Pool',
                'type' => 'checkbox',
                'direction' => 'both'
            ],
            'has_spa' => [
                'field' => 'Has Spa',
                'type' => 'checkbox',
                'direction' => 'both'
            ],
            'featured_listing' => [
                'field' => 'Featured',
                'type' => 'checkbox',
                'direction' => 'both'
            ],

            // Relationships
            'listing_agent' => [
                'field' => 'Listing Agent',
                'type' => 'lookup',
                'direction' => 'both',
                'lookup_table' => 'Agents',
                'lookup_field' => 'Name'
            ],
            'co_listing_agent' => [
                'field' => 'Co-Listing Agent',
                'type' => 'lookup',
                'direction' => 'both',
                'lookup_table' => 'Agents',
                'lookup_field' => 'Name'
            ],
            'community' => [
                'field' => 'Community',
                'type' => 'lookup',
                'direction' => 'both',
                'lookup_table' => 'Communities',
                'lookup_field' => 'Name'
            ],

            // URLs and Links
            'virtual_tour_url' => [
                'field' => 'Virtual Tour URL',
                'type' => 'url',
                'direction' => 'both'
            ],
            'video_tour_url' => [
                'field' => 'Video Tour URL',
                'type' => 'url',
                'direction' => 'both'
            ]
        ];
    }

    private function init_media_mapping() {
        $this->media_mapping = [
            'featured_image' => 'Featured Image',
            'gallery_images' => 'Gallery',
            'floor_plans' => 'Floor Plans'
        ];
    }

    public function get_field_mapping() {
        return $this->field_mapping;
    }

    public function get_media_mapping() {
        return $this->media_mapping;
    }

    /**
     * Map Airtable record to WordPress post data
     */
    public function map_from_airtable($record) {
        $post_data = [
            'post_type' => 'listing',
            'post_status' => 'publish'
        ];

        $fields = $record['fields'] ?? [];

        foreach ($this->field_mapping as $wp_field => $config) {
            if (!in_array('from_airtable', [$config['direction'], 'both'])) {
                continue;
            }

            $airtable_field = $config['field'];
            if (!isset($fields[$airtable_field])) {
                continue;
            }

            $value = $fields[$airtable_field];
            $mapped_value = $this->convert_from_airtable($value, $config);

            if (in_array($wp_field, ['post_title', 'post_content', 'post_status'])) {
                $post_data[$wp_field] = $mapped_value;
            } else {
                // This will be handled by ACF after post creation
                $post_data['meta_input'][$wp_field] = $mapped_value;
            }
        }

        return $post_data;
    }

    /**
     * Map WordPress post to Airtable record data
     */
    public function map_to_airtable($post) {
        $airtable_data = [];

        foreach ($this->field_mapping as $wp_field => $config) {
            if (!in_array('to_airtable', [$config['direction'], 'both'])) {
                continue;
            }

            $value = null;

            if (in_array($wp_field, ['post_title', 'post_content', 'post_status'])) {
                $value = $post->$wp_field;
            } else {
                $value = get_field($wp_field, $post->ID);
            }

            if ($value !== null && $value !== '') {
                $mapped_value = $this->convert_to_airtable($value, $config);
                $airtable_data[$config['field']] = $mapped_value;
            }
        }

        // Add WordPress ID for reference
        $airtable_data['WordPress ID'] = $post->ID;
        $airtable_data['Last Updated'] = get_post_modified_time('c', false, $post->ID);

        return $airtable_data;
    }

    private function convert_from_airtable($value, $config) {
        switch ($config['type']) {
            case 'checkbox':
                return (bool) $value;

            case 'select':
                // Map Airtable value back to WordPress value
                if (isset($config['options'])) {
                    $flipped = array_flip($config['options']);
                    return $flipped[$value] ?? $value;
                }
                return $value;

            case 'multiselect':
                if (is_array($value)) {
                    return $value;
                }
                return [];

            case 'number':
                return is_numeric($value) ? floatval($value) : 0;

            case 'lookup':
                // For relationships, we need to find the corresponding WordPress post
                return $this->resolve_lookup_from_airtable($value, $config);

            case 'url':
            case 'text':
            default:
                return sanitize_text_field($value);
        }
    }

    private function convert_to_airtable($value, $config) {
        switch ($config['type']) {
            case 'checkbox':
                return (bool) $value;

            case 'select':
                // Map WordPress value to Airtable display value
                if (isset($config['options']) && isset($config['options'][$value])) {
                    return $config['options'][$value];
                }
                return $value;

            case 'multiselect':
                if (is_array($value)) {
                    return $value;
                } elseif (is_string($value)) {
                    return explode(',', $value);
                }
                return [];

            case 'number':
                return is_numeric($value) ? floatval($value) : null;

            case 'lookup':
                // For relationships, convert WordPress post to Airtable reference
                return $this->resolve_lookup_to_airtable($value, $config);

            case 'url':
                return esc_url($value);

            case 'text':
            default:
                return (string) $value;
        }
    }

    private function resolve_lookup_from_airtable($value, $config) {
        if (!is_array($value) || empty($value)) {
            return null;
        }

        // For now, we'll store the Airtable record ID
        // In a full implementation, you'd need to map this back to WordPress posts
        return $value[0]; // Assuming single selection for now
    }

    private function resolve_lookup_to_airtable($value, $config) {
        if (!$value) {
            return null;
        }

        if (is_object($value) && isset($value->ID)) {
            // WordPress post object
            $airtable_record_id = get_post_meta($value->ID, '_airtable_record_id', true);
            return $airtable_record_id ? [$airtable_record_id] : null;
        } elseif (is_numeric($value)) {
            // Post ID
            $airtable_record_id = get_post_meta($value, '_airtable_record_id', true);
            return $airtable_record_id ? [$airtable_record_id] : null;
        } elseif (is_array($value)) {
            // Array of posts
            $record_ids = [];
            foreach ($value as $item) {
                if (is_object($item) && isset($item->ID)) {
                    $record_id = get_post_meta($item->ID, '_airtable_record_id', true);
                    if ($record_id) {
                        $record_ids[] = $record_id;
                    }
                } elseif (is_numeric($item)) {
                    $record_id = get_post_meta($item, '_airtable_record_id', true);
                    if ($record_id) {
                        $record_ids[] = $record_id;
                    }
                }
            }
            return !empty($record_ids) ? $record_ids : null;
        }

        return null;
    }

    /**
     * Get validation rules for fields
     */
    public function get_validation_rules() {
        return [
            'price' => [
                'required' => true,
                'type' => 'numeric',
                'min' => 0
            ],
            'bedrooms' => [
                'type' => 'integer',
                'min' => 0,
                'max' => 20
            ],
            'bathrooms' => [
                'type' => 'numeric',
                'min' => 0,
                'max' => 20
            ],
            'square_feet' => [
                'type' => 'integer',
                'min' => 0
            ],
            'street_address' => [
                'required' => true,
                'type' => 'string',
                'max_length' => 255
            ],
            'city' => [
                'required' => true,
                'type' => 'string',
                'max_length' => 100
            ],
            'state' => [
                'required' => true,
                'type' => 'string',
                'max_length' => 2
            ],
            'zip_code' => [
                'required' => true,
                'type' => 'string',
                'pattern' => '/^\d{5}(-\d{4})?$/'
            ]
        ];
    }

    /**
     * Validate field data
     */
    public function validate_field($field_name, $value) {
        $rules = $this->get_validation_rules();
        
        if (!isset($rules[$field_name])) {
            return true;
        }

        $rule = $rules[$field_name];

        // Required check
        if (isset($rule['required']) && $rule['required'] && empty($value)) {
            return new \WP_Error('required_field', sprintf(__('%s is required', 'happy-place'), $field_name));
        }

        // Type checks
        if (isset($rule['type']) && !empty($value)) {
            switch ($rule['type']) {
                case 'numeric':
                    if (!is_numeric($value)) {
                        return new \WP_Error('invalid_type', sprintf(__('%s must be a number', 'happy-place'), $field_name));
                    }
                    break;

                case 'integer':
                    if (!is_int((int) $value)) {
                        return new \WP_Error('invalid_type', sprintf(__('%s must be an integer', 'happy-place'), $field_name));
                    }
                    break;
            }
        }

        // Range checks
        if (isset($rule['min']) && is_numeric($value) && $value < $rule['min']) {
            return new \WP_Error('min_value', sprintf(__('%s must be at least %s', 'happy-place'), $field_name, $rule['min']));
        }

        if (isset($rule['max']) && is_numeric($value) && $value > $rule['max']) {
            return new \WP_Error('max_value', sprintf(__('%s must be no more than %s', 'happy-place'), $field_name, $rule['max']));
        }

        // Pattern check
        if (isset($rule['pattern']) && !empty($value) && !preg_match($rule['pattern'], $value)) {
            return new \WP_Error('invalid_format', sprintf(__('%s has an invalid format', 'happy-place'), $field_name));
        }

        return true;
    }
}