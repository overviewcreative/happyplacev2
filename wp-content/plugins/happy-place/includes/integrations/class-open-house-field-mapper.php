<?php
/**
 * Open House Field Mapper
 * Maps fields between WordPress open houses and Airtable records
 *
 * @package HappyPlace
 */

namespace HappyPlace\Integrations;

use Exception;

if (!defined('ABSPATH')) {
    exit;
}

class Open_House_Field_Mapper {

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
                'field' => 'Title',
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
                    'publish' => 'Scheduled',
                    'draft' => 'Draft',
                    'private' => 'Private'
                ]
            ],

            // Event Details
            'start_date' => [
                'field' => 'Date',
                'type' => 'date',
                'direction' => 'both'
            ],
            'start_time' => [
                'field' => 'Start Time',
                'type' => 'time',
                'direction' => 'both'
            ],
            'end_time' => [
                'field' => 'End Time',
                'type' => 'time',
                'direction' => 'both'
            ],
            'duration' => [
                'field' => 'Duration (hours)',
                'type' => 'number',
                'direction' => 'both'
            ],

            // Listing Relationship
            'listing' => [
                'field' => 'Listing',
                'type' => 'lookup',
                'direction' => 'both',
                'lookup_table' => 'Listings',
                'lookup_field' => 'Address'
            ],

            // Contact Information
            'contact_name' => [
                'field' => 'Contact Name',
                'type' => 'text',
                'direction' => 'both'
            ],
            'contact_phone' => [
                'field' => 'Contact Phone',
                'type' => 'phone',
                'direction' => 'both'
            ],
            'contact_email' => [
                'field' => 'Contact Email',
                'type' => 'email',
                'direction' => 'both'
            ],

            // Open House Details
            'special_instructions' => [
                'field' => 'Special Instructions',
                'type' => 'text',
                'direction' => 'both'
            ],
            'parking_instructions' => [
                'field' => 'Parking Instructions',
                'type' => 'text',
                'direction' => 'both'
            ],
            'refreshments' => [
                'field' => 'Refreshments',
                'type' => 'checkbox',
                'direction' => 'both'
            ],
            'sign_in_required' => [
                'field' => 'Sign-in Required',
                'type' => 'checkbox',
                'direction' => 'both'
            ],
            'shoes_off' => [
                'field' => 'Shoes Off',
                'type' => 'checkbox',
                'direction' => 'both'
            ],

            // Marketing
            'advertised_online' => [
                'field' => 'Advertised Online',
                'type' => 'checkbox',
                'direction' => 'both'
            ],
            'mls_listing' => [
                'field' => 'MLS Listing',
                'type' => 'checkbox',
                'direction' => 'both'
            ],
            'yard_sign' => [
                'field' => 'Yard Sign',
                'type' => 'checkbox',
                'direction' => 'both'
            ],

            // Attendance Tracking
            'expected_attendance' => [
                'field' => 'Expected Attendance',
                'type' => 'number',
                'direction' => 'both'
            ],
            'actual_attendance' => [
                'field' => 'Actual Attendance',
                'type' => 'number',
                'direction' => 'from_airtable'
            ],
            'qualified_leads' => [
                'field' => 'Qualified Leads',
                'type' => 'number',
                'direction' => 'from_airtable'
            ],

            // Weather and Conditions
            'weather_backup_plan' => [
                'field' => 'Weather Backup Plan',
                'type' => 'text',
                'direction' => 'both'
            ],
            'cancelled' => [
                'field' => 'Cancelled',
                'type' => 'checkbox',
                'direction' => 'both'
            ],
            'cancellation_reason' => [
                'field' => 'Cancellation Reason',
                'type' => 'text',
                'direction' => 'both'
            ],

            // Follow-up
            'follow_up_completed' => [
                'field' => 'Follow-up Completed',
                'type' => 'checkbox',
                'direction' => 'from_airtable'
            ],
            'follow_up_notes' => [
                'field' => 'Follow-up Notes',
                'type' => 'text',
                'direction' => 'from_airtable'
            ]
        ];
    }

    private function init_media_mapping() {
        $this->media_mapping = [
            'event_photos' => 'Event Photos',
            'promotional_materials' => 'Promotional Materials'
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
            'post_type' => 'open_house',
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

        // Generate post title if not provided
        if (empty($post_data['post_title']) && !empty($post_data['meta_input']['listing'])) {
            $listing_id = $post_data['meta_input']['listing'];
            $listing_title = get_the_title($listing_id);
            $date = $post_data['meta_input']['start_date'] ?? '';
            $post_data['post_title'] = sprintf('Open House - %s - %s', $listing_title, $date);
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
        $listing_id = get_field('listing', $post->ID);
        if ($listing_id) {
            $listing = get_post($listing_id);
            if ($listing) {
                $airtable_data['Listing Address'] = get_field('street_address', $listing_id) ?: $listing->post_title;
                $airtable_data['Listing Price'] = get_field('listing_price', $listing_id);
            }
        }

        // Format date/time for display
        $start_date = get_field('start_date', $post->ID);
        $start_time = get_field('start_time', $post->ID);
        $end_time = get_field('end_time', $post->ID);

        if ($start_date && $start_time && $end_time) {
            $airtable_data['Event Time'] = sprintf('%s from %s to %s', 
                date('M j, Y', strtotime($start_date)), 
                $start_time, 
                $end_time
            );
        }

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

            case 'number':
                return is_numeric($value) ? floatval($value) : 0;

            case 'date':
                return $value ? date('Y-m-d', strtotime($value)) : '';

            case 'time':
                return $value ? date('H:i:s', strtotime($value)) : '';

            case 'phone':
                return $this->format_phone_number($value);

            case 'email':
                return sanitize_email($value);

            case 'lookup':
                return $this->resolve_lookup_from_airtable($value, $config);

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

            case 'number':
                return is_numeric($value) ? floatval($value) : null;

            case 'date':
                return $value ? date('Y-m-d', strtotime($value)) : null;

            case 'time':
                return $value;

            case 'phone':
                return $this->format_phone_number($value);

            case 'email':
                return is_email($value) ? $value : null;

            case 'lookup':
                return $this->resolve_lookup_to_airtable($value, $config);

            case 'text':
            default:
                return (string) $value;
        }
    }

    private function format_phone_number($phone) {
        if (empty($phone)) {
            return '';
        }

        $phone = preg_replace('/\D/', '', $phone);
        
        if (strlen($phone) === 10) {
            return sprintf('(%s) %s-%s', 
                substr($phone, 0, 3),
                substr($phone, 3, 3),
                substr($phone, 6, 4)
            );
        }
        
        return $phone;
    }

    private function resolve_lookup_from_airtable($value, $config) {
        if (!is_array($value) || empty($value)) {
            return null;
        }

        // For now, store the Airtable record ID
        // In a full implementation, you'd map this back to WordPress posts
        return $value[0];
    }

    private function resolve_lookup_to_airtable($value, $config) {
        if (!$value) {
            return null;
        }

        if (is_object($value) && isset($value->ID)) {
            $airtable_record_id = get_post_meta($value->ID, '_airtable_record_id', true);
            return $airtable_record_id ? [$airtable_record_id] : null;
        } elseif (is_numeric($value)) {
            $airtable_record_id = get_post_meta($value, '_airtable_record_id', true);
            return $airtable_record_id ? [$airtable_record_id] : null;
        }

        return null;
    }

    public function get_validation_rules() {
        return [
            'start_date' => [
                'required' => true,
                'type' => 'date'
            ],
            'start_time' => [
                'required' => true,
                'type' => 'time'
            ],
            'end_time' => [
                'required' => true,
                'type' => 'time'
            ],
            'listing' => [
                'required' => true,
                'type' => 'integer'
            ],
            'contact_email' => [
                'type' => 'email'
            ],
            'contact_phone' => [
                'type' => 'phone',
                'pattern' => '/^[\d\s\-\(\)\+\.]+$/'
            ],
            'expected_attendance' => [
                'type' => 'integer',
                'min' => 0
            ],
            'actual_attendance' => [
                'type' => 'integer',
                'min' => 0
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
                case 'email':
                    if (!is_email($value)) {
                        return new \WP_Error('invalid_email', sprintf(__('%s must be a valid email address', 'happy-place'), $field_name));
                    }
                    break;

                case 'phone':
                    if (isset($rule['pattern']) && !preg_match($rule['pattern'], $value)) {
                        return new \WP_Error('invalid_phone', sprintf(__('%s must be a valid phone number', 'happy-place'), $field_name));
                    }
                    break;

                case 'date':
                    if (!strtotime($value)) {
                        return new \WP_Error('invalid_date', sprintf(__('%s must be a valid date', 'happy-place'), $field_name));
                    }
                    break;

                case 'time':
                    if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $value)) {
                        return new \WP_Error('invalid_time', sprintf(__('%s must be a valid time format', 'happy-place'), $field_name));
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

        return true;
    }
}