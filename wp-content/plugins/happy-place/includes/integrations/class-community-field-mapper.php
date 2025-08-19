<?php
/**
 * Community Field Mapper
 * Maps fields between WordPress communities and Airtable records
 *
 * @package HappyPlace
 */

namespace HappyPlace\Integrations;

use Exception;

if (!defined('ABSPATH')) {
    exit;
}

class Community_Field_Mapper {

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
                'field' => 'Name',
                'type' => 'text',
                'direction' => 'both'
            ],
            'post_content' => [
                'field' => 'Description',
                'type' => 'text',
                'direction' => 'both'
            ],

            // Location Information
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

            // Community Details
            'community_type' => [
                'field' => 'Community Type',
                'type' => 'select',
                'direction' => 'both',
                'options' => [
                    'subdivision' => 'Subdivision',
                    'gated_community' => 'Gated Community',
                    'master_planned' => 'Master Planned',
                    'apartment_complex' => 'Apartment Complex',
                    'townhome_community' => 'Townhome Community',
                    'condominium' => 'Condominium'
                ]
            ],
            'year_built' => [
                'field' => 'Year Built',
                'type' => 'number',
                'direction' => 'both'
            ],
            'total_homes' => [
                'field' => 'Total Homes',
                'type' => 'number',
                'direction' => 'both'
            ],
            'lot_size_range' => [
                'field' => 'Lot Size Range',
                'type' => 'text',
                'direction' => 'both'
            ],
            'price_range' => [
                'field' => 'Price Range',
                'type' => 'text',
                'direction' => 'both'
            ],

            // HOA Information
            'hoa_fees' => [
                'field' => 'HOA Fees',
                'type' => 'number',
                'direction' => 'both'
            ],
            'hoa_frequency' => [
                'field' => 'HOA Fee Frequency',
                'type' => 'select',
                'direction' => 'both',
                'options' => [
                    'monthly' => 'Monthly',
                    'quarterly' => 'Quarterly',
                    'annually' => 'Annually'
                ]
            ],
            'hoa_amenities' => [
                'field' => 'HOA Amenities',
                'type' => 'multiselect',
                'direction' => 'both'
            ],

            // Amenities
            'amenities' => [
                'field' => 'Amenities',
                'type' => 'multiselect',
                'direction' => 'both'
            ],
            'recreational_facilities' => [
                'field' => 'Recreational Facilities',
                'type' => 'multiselect',
                'direction' => 'both'
            ],

            // Schools
            'elementary_school' => [
                'field' => 'Elementary School',
                'type' => 'text',
                'direction' => 'both'
            ],
            'middle_school' => [
                'field' => 'Middle School',
                'type' => 'text',
                'direction' => 'both'
            ],
            'high_school' => [
                'field' => 'High School',
                'type' => 'text',
                'direction' => 'both'
            ],
            'school_district' => [
                'field' => 'School District',
                'type' => 'text',
                'direction' => 'both'
            ],

            // Features
            'gated_community' => [
                'field' => 'Gated',
                'type' => 'checkbox',
                'direction' => 'both'
            ],
            'golf_course' => [
                'field' => 'Golf Course',
                'type' => 'checkbox',
                'direction' => 'both'
            ],
            'waterfront' => [
                'field' => 'Waterfront',
                'type' => 'checkbox',
                'direction' => 'both'
            ],
            'new_construction' => [
                'field' => 'New Construction',
                'type' => 'checkbox',
                'direction' => 'both'
            ],

            // Builder Information
            'builder_name' => [
                'field' => 'Builder Name',
                'type' => 'text',
                'direction' => 'both'
            ],
            'developer_name' => [
                'field' => 'Developer Name',
                'type' => 'text',
                'direction' => 'both'
            ],

            // Website/Links
            'website' => [
                'field' => 'Website',
                'type' => 'url',
                'direction' => 'both'
            ],
            'virtual_tour_url' => [
                'field' => 'Virtual Tour',
                'type' => 'url',
                'direction' => 'both'
            ]
        ];
    }

    private function init_media_mapping() {
        $this->media_mapping = [
            'featured_image' => 'Featured Image',
            'community_photos' => 'Photos',
            'amenity_photos' => 'Amenity Photos',
            'map_image' => 'Community Map'
        ];
    }

    public function get_field_mapping() {
        return $this->field_mapping;
    }

    public function get_media_mapping() {
        return $this->media_mapping;
    }

    public function map_from_airtable($record) {
        $post_data = [
            'post_type' => 'community',
            'post_status' => 'publish'
        ];

        $fields = $record['fields'] ?? [];

        foreach ($this->field_mapping as $wp_field => $config) {
            if (!in_array($config['direction'], ['from_airtable', 'both'])) {
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
                $post_data['meta_input'][$wp_field] = $mapped_value;
            }
        }

        return $post_data;
    }

    public function map_to_airtable($post) {
        $airtable_data = [];

        foreach ($this->field_mapping as $wp_field => $config) {
            if (!in_array($config['direction'], ['to_airtable', 'both'])) {
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
                if ($mapped_value !== null) {
                    $airtable_data[$config['field']] = $mapped_value;
                }
            }
        }

        // Add WordPress ID for reference
        $airtable_data['WordPress ID'] = $post->ID;
        $airtable_data['Last Updated'] = get_post_modified_time('c', false, $post->ID);

        // Add computed fields
        $listing_count = $this->get_community_listing_count($post->ID);
        $airtable_data['Active Listings'] = $listing_count;

        return $airtable_data;
    }

    private function convert_from_airtable($value, $config) {
        switch ($config['type']) {
            case 'checkbox':
                return (bool) $value;

            case 'select':
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

            case 'url':
                return esc_url($value);

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

            case 'url':
                return filter_var($value, FILTER_VALIDATE_URL) ? $value : null;

            case 'text':
            default:
                return (string) $value;
        }
    }

    private function get_community_listing_count($community_id) {
        $listings = get_posts([
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => 'community',
                    'value' => '"' . $community_id . '"',
                    'compare' => 'LIKE'
                ]
            ]
        ]);

        return count($listings);
    }

    public function get_validation_rules() {
        return [
            'post_title' => [
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
                'type' => 'string',
                'pattern' => '/^\d{5}(-\d{4})?$/'
            ],
            'total_homes' => [
                'type' => 'integer',
                'min' => 0
            ],
            'hoa_fees' => [
                'type' => 'numeric',
                'min' => 0
            ],
            'website' => [
                'type' => 'url'
            ]
        ];
    }

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

        if (empty($value)) {
            return true;
        }

        // Type checks
        if (isset($rule['type'])) {
            switch ($rule['type']) {
                case 'url':
                    if (!filter_var($value, FILTER_VALIDATE_URL)) {
                        return new \WP_Error('invalid_url', sprintf(__('%s must be a valid URL', 'happy-place'), $field_name));
                    }
                    break;

                case 'integer':
                    if (!is_int((int) $value)) {
                        return new \WP_Error('invalid_type', sprintf(__('%s must be an integer', 'happy-place'), $field_name));
                    }
                    break;

                case 'numeric':
                    if (!is_numeric($value)) {
                        return new \WP_Error('invalid_type', sprintf(__('%s must be a number', 'happy-place'), $field_name));
                    }
                    break;
            }
        }

        // Range checks
        if (isset($rule['min']) && is_numeric($value) && $value < $rule['min']) {
            return new \WP_Error('min_value', sprintf(__('%s must be at least %s', 'happy-place'), $field_name, $rule['min']));
        }

        // Pattern check
        if (isset($rule['pattern']) && !preg_match($rule['pattern'], $value)) {
            return new \WP_Error('invalid_format', sprintf(__('%s has an invalid format', 'happy-place'), $field_name));
        }

        // Length check
        if (isset($rule['max_length']) && strlen($value) > $rule['max_length']) {
            return new \WP_Error('max_length', sprintf(__('%s must be no more than %s characters', 'happy-place'), $field_name, $rule['max_length']));
        }

        return true;
    }
}