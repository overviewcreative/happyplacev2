<?php
/**
 * Agent Field Mapper
 * Maps fields between WordPress agents and Airtable records
 *
 * @package HappyPlace
 */

namespace HappyPlace\Integrations;

use Exception;

if (!defined('ABSPATH')) {
    exit;
}

class Agent_Field_Mapper {

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
                'field' => 'Full Name',
                'type' => 'text',
                'direction' => 'both'
            ],
            'post_content' => [
                'field' => 'Bio',
                'type' => 'text',
                'direction' => 'both'
            ],
            'post_status' => [
                'field' => 'Status',
                'type' => 'select',
                'direction' => 'both',
                'options' => [
                    'publish' => 'Active',
                    'draft' => 'Inactive',
                    'private' => 'Private'
                ]
            ],

            // Personal Information
            'first_name' => [
                'field' => 'First Name',
                'type' => 'text',
                'direction' => 'both'
            ],
            'last_name' => [
                'field' => 'Last Name',
                'type' => 'text',
                'direction' => 'both'
            ],
            'title' => [
                'field' => 'Title',
                'type' => 'text',
                'direction' => 'both'
            ],
            'license_number' => [
                'field' => 'License Number',
                'type' => 'text',
                'direction' => 'both'
            ],
            'years_experience' => [
                'field' => 'Years Experience',
                'type' => 'number',
                'direction' => 'both'
            ],

            // Contact Information
            'phone' => [
                'field' => 'Phone',
                'type' => 'phone',
                'direction' => 'both'
            ],
            'mobile_phone' => [
                'field' => 'Mobile Phone',
                'type' => 'phone',
                'direction' => 'both'
            ],
            'email' => [
                'field' => 'Email',
                'type' => 'email',
                'direction' => 'both'
            ],
            'website' => [
                'field' => 'Website',
                'type' => 'url',
                'direction' => 'both'
            ],

            // Social Media
            'facebook_url' => [
                'field' => 'Facebook URL',
                'type' => 'url',
                'direction' => 'both'
            ],
            'instagram_url' => [
                'field' => 'Instagram URL',
                'type' => 'url',
                'direction' => 'both'
            ],
            'linkedin_url' => [
                'field' => 'LinkedIn URL',
                'type' => 'url',
                'direction' => 'both'
            ],
            'twitter_url' => [
                'field' => 'Twitter URL',
                'type' => 'url',
                'direction' => 'both'
            ],
            'youtube_url' => [
                'field' => 'YouTube URL',
                'type' => 'url',
                'direction' => 'both'
            ],

            // Professional Information
            'specialties' => [
                'field' => 'Specialties',
                'type' => 'multiselect',
                'direction' => 'both'
            ],
            'languages' => [
                'field' => 'Languages',
                'type' => 'multiselect',
                'direction' => 'both'
            ],
            'certifications' => [
                'field' => 'Certifications',
                'type' => 'multiselect',
                'direction' => 'both'
            ],

            // Office Information
            'office_name' => [
                'field' => 'Office Name',
                'type' => 'text',
                'direction' => 'both'
            ],
            'office_phone' => [
                'field' => 'Office Phone',
                'type' => 'phone',
                'direction' => 'both'
            ],
            'office_address' => [
                'field' => 'Office Address',
                'type' => 'text',
                'direction' => 'both'
            ],

            // Performance Metrics (mostly from Airtable to WordPress)
            'total_sales_volume' => [
                'field' => 'Total Sales Volume',
                'type' => 'number',
                'direction' => 'from_airtable'
            ],
            'active_listings_count' => [
                'field' => 'Active Listings',
                'type' => 'number',
                'direction' => 'from_airtable'
            ],
            'sold_listings_count' => [
                'field' => 'Sold Listings',
                'type' => 'number',
                'direction' => 'from_airtable'
            ],
            'average_dom' => [
                'field' => 'Average Days on Market',
                'type' => 'number',
                'direction' => 'from_airtable'
            ],
            'agent_rating' => [
                'field' => 'Agent Rating',
                'type' => 'number',
                'direction' => 'from_airtable'
            ],

            // WordPress User Integration
            'wordpress_user' => [
                'field' => 'WordPress User ID',
                'type' => 'number',
                'direction' => 'both'
            ],
            'agent_status' => [
                'field' => 'Agent Status',
                'type' => 'select',
                'direction' => 'both',
                'options' => [
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                    'pending' => 'Pending',
                    'suspended' => 'Suspended'
                ]
            ]
        ];
    }

    private function init_media_mapping() {
        $this->media_mapping = [
            'profile_photo' => 'Profile Photo',
            'cover_photo' => 'Cover Photo',
            'team_photo' => 'Team Photo',
            'agent_headshot' => 'Headshot'
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
            'post_type' => 'agent',
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

        // Generate post_title from first and last name if not provided
        if (empty($post_data['post_title']) && !empty($post_data['meta_input']['first_name']) && !empty($post_data['meta_input']['last_name'])) {
            $post_data['post_title'] = $post_data['meta_input']['first_name'] . ' ' . $post_data['meta_input']['last_name'];
        }

        return $post_data;
    }

    /**
     * Map WordPress post to Airtable record data
     */
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
        $airtable_data['Full Name'] = $post->post_title;
        
        // Add listing counts (computed from relationships)
        $active_listings = $this->get_agent_active_listings($post->ID);
        $sold_listings = $this->get_agent_sold_listings($post->ID);
        
        $airtable_data['Active Listings Count'] = count($active_listings);
        $airtable_data['Sold Listings Count'] = count($sold_listings);

        return $airtable_data;
    }

    private function convert_from_airtable($value, $config) {
        switch ($config['type']) {
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

            case 'phone':
                return $this->format_phone_number($value);

            case 'email':
                return sanitize_email($value);

            case 'url':
                return esc_url($value);

            case 'text':
            default:
                return sanitize_text_field($value);
        }
    }

    private function convert_to_airtable($value, $config) {
        switch ($config['type']) {
            case 'select':
                if (isset($config['options']) && isset($config['options'][$value])) {
                    return $config['options'][$value];
                }
                return $value;

            case 'multiselect':
                if (is_array($value)) {
                    return $value;
                } elseif (is_string($value)) {
                    // Handle serialized arrays or comma-separated values
                    $unserialized = @unserialize($value);
                    if ($unserialized !== false) {
                        return is_array($unserialized) ? $unserialized : [$unserialized];
                    }
                    return explode(',', $value);
                }
                return [];

            case 'number':
                return is_numeric($value) ? floatval($value) : null;

            case 'phone':
                return $this->format_phone_number($value);

            case 'email':
                return is_email($value) ? $value : null;

            case 'url':
                return filter_var($value, FILTER_VALIDATE_URL) ? $value : null;

            case 'text':
            default:
                return (string) $value;
        }
    }

    private function format_phone_number($phone) {
        if (empty($phone)) {
            return '';
        }

        // Remove all non-digit characters
        $phone = preg_replace('/\D/', '', $phone);
        
        // Format US phone numbers
        if (strlen($phone) === 10) {
            return sprintf('(%s) %s-%s', 
                substr($phone, 0, 3),
                substr($phone, 3, 3),
                substr($phone, 6, 4)
            );
        } elseif (strlen($phone) === 11 && $phone[0] === '1') {
            return sprintf('+1 (%s) %s-%s', 
                substr($phone, 1, 3),
                substr($phone, 4, 3),
                substr($phone, 7, 4)
            );
        }
        
        return $phone;
    }

    private function get_agent_active_listings($agent_id) {
        return get_posts([
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'listing_agent',
                    'value' => '"' . $agent_id . '"',
                    'compare' => 'LIKE'
                ],
                [
                    'key' => 'listing_status',
                    'value' => 'active',
                    'compare' => '='
                ]
            ]
        ]);
    }

    private function get_agent_sold_listings($agent_id) {
        return get_posts([
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'listing_agent',
                    'value' => '"' . $agent_id . '"',
                    'compare' => 'LIKE'
                ],
                [
                    'key' => 'listing_status',
                    'value' => 'sold',
                    'compare' => '='
                ]
            ]
        ]);
    }

    /**
     * Get validation rules for fields
     */
    public function get_validation_rules() {
        return [
            'first_name' => [
                'required' => true,
                'type' => 'string',
                'max_length' => 50
            ],
            'last_name' => [
                'required' => true,
                'type' => 'string',
                'max_length' => 50
            ],
            'email' => [
                'required' => true,
                'type' => 'email'
            ],
            'phone' => [
                'type' => 'phone',
                'pattern' => '/^[\d\s\-\(\)\+\.]+$/'
            ],
            'mobile_phone' => [
                'type' => 'phone',
                'pattern' => '/^[\d\s\-\(\)\+\.]+$/'
            ],
            'license_number' => [
                'type' => 'string',
                'max_length' => 50
            ],
            'years_experience' => [
                'type' => 'integer',
                'min' => 0,
                'max' => 70
            ],
            'website' => [
                'type' => 'url'
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

        if (empty($value)) {
            return true; // Skip other validations for empty optional fields
        }

        // Type checks
        if (isset($rule['type'])) {
            switch ($rule['type']) {
                case 'email':
                    if (!is_email($value)) {
                        return new \WP_Error('invalid_email', sprintf(__('%s must be a valid email address', 'happy-place'), $field_name));
                    }
                    break;

                case 'url':
                    if (!filter_var($value, FILTER_VALIDATE_URL)) {
                        return new \WP_Error('invalid_url', sprintf(__('%s must be a valid URL', 'happy-place'), $field_name));
                    }
                    break;

                case 'phone':
                    if (isset($rule['pattern']) && !preg_match($rule['pattern'], $value)) {
                        return new \WP_Error('invalid_phone', sprintf(__('%s must be a valid phone number', 'happy-place'), $field_name));
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

        // Length check
        if (isset($rule['max_length']) && strlen($value) > $rule['max_length']) {
            return new \WP_Error('max_length', sprintf(__('%s must be no more than %s characters', 'happy-place'), $field_name, $rule['max_length']));
        }

        return true;
    }
}