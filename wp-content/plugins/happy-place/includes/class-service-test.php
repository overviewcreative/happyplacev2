<?php
/**
 * Service Test - Namespace Resolution Verification
 * 
 * This file tests that our new service classes can be properly loaded
 * and instantiated through the autoloader.
 * 
 * @package HappyPlace\Testing
 * @version 4.0.0
 */

namespace HappyPlace;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Service Test Class
 * 
 * Tests service class instantiation and namespace resolution
 */
class ServiceTest {
    
    /**
     * Run all service tests
     * 
     * @return array Test results
     */
    public static function run_tests(): array {
        $results = [
            'passed' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        // Test ListingService
        $listing_test = self::test_listing_service();
        if ($listing_test['success']) {
            $results['passed']++;
        } else {
            $results['failed']++;
            $results['errors']['ListingService'] = $listing_test['error'];
        }
        
        // Test FormService
        $form_test = self::test_form_service();
        if ($form_test['success']) {
            $results['passed']++;
        } else {
            $results['failed']++;
            $results['errors']['FormService'] = $form_test['error'];
        }
        
        // Test ImportService
        $import_test = self::test_import_service();
        if ($import_test['success']) {
            $results['passed']++;
        } else {
            $results['failed']++;
            $results['errors']['ImportService'] = $import_test['error'];
        }
        
        // Test ListingFormHandler
        $form_handler_test = self::test_listing_form_handler();
        if ($form_handler_test['success']) {
            $results['passed']++;
        } else {
            $results['failed']++;
            $results['errors']['ListingFormHandler'] = $form_handler_test['error'];
        }
        
        return $results;
    }
    
    /**
     * Test ListingService instantiation
     */
    private static function test_listing_service(): array {
        try {
            if (!class_exists('HappyPlace\\Services\\ListingService')) {
                return ['success' => false, 'error' => 'Class HappyPlace\\Services\\ListingService not found'];
            }
            
            $service = new \HappyPlace\Services\ListingService();
            
            if (!$service instanceof \HappyPlace\Services\ListingService) {
                return ['success' => false, 'error' => 'ListingService instantiation failed'];
            }
            
            // Check if required methods exist
            $required_methods = ['create_listing', 'update_listing', 'delete_listing', 'bulk_update'];
            foreach ($required_methods as $method) {
                if (!method_exists($service, $method)) {
                    return ['success' => false, 'error' => "Method {$method} not found in ListingService"];
                }
            }
            
            return ['success' => true];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Test FormService instantiation
     */
    private static function test_form_service(): array {
        try {
            if (!class_exists('HappyPlace\\Services\\FormService')) {
                return ['success' => false, 'error' => 'Class HappyPlace\\Services\\FormService not found'];
            }
            
            $service = new \HappyPlace\Services\FormService();
            
            if (!$service instanceof \HappyPlace\Services\FormService) {
                return ['success' => false, 'error' => 'FormService instantiation failed'];
            }
            
            // Check if required methods exist
            $required_methods = ['render_listing_form', 'process_submission', 'validate_form'];
            foreach ($required_methods as $method) {
                if (!method_exists($service, $method)) {
                    return ['success' => false, 'error' => "Method {$method} not found in FormService"];
                }
            }
            
            return ['success' => true];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Test ImportService instantiation
     */
    private static function test_import_service(): array {
        try {
            if (!class_exists('HappyPlace\\Services\\ImportService')) {
                return ['success' => false, 'error' => 'Class HappyPlace\\Services\\ImportService not found'];
            }
            
            $service = new \HappyPlace\Services\ImportService();
            
            if (!$service instanceof \HappyPlace\Services\ImportService) {
                return ['success' => false, 'error' => 'ImportService instantiation failed'];
            }
            
            // Check if required methods exist
            $required_methods = ['import_csv', 'validate_csv', 'auto_map_fields'];
            foreach ($required_methods as $method) {
                if (!method_exists($service, $method)) {
                    return ['success' => false, 'error' => "Method {$method} not found in ImportService"];
                }
            }
            
            return ['success' => true];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Test ListingFormHandler instantiation
     */
    private static function test_listing_form_handler(): array {
        try {
            if (!class_exists('HappyPlace\\Forms\\ListingFormHandler')) {
                return ['success' => false, 'error' => 'Class HappyPlace\\Forms\\ListingFormHandler not found'];
            }
            
            $handler = new \HappyPlace\Forms\ListingFormHandler();
            
            if (!$handler instanceof \HappyPlace\Forms\ListingFormHandler) {
                return ['success' => false, 'error' => 'ListingFormHandler instantiation failed'];
            }
            
            // Check if required methods exist
            $required_methods = ['render_form', 'ajax_save_listing', 'shortcode_handler'];
            foreach ($required_methods as $method) {
                if (!method_exists($handler, $method)) {
                    return ['success' => false, 'error' => "Method {$method} not found in ListingFormHandler"];
                }
            }
            
            return ['success' => true];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get detailed class information
     */
    public static function get_class_info(): array {
        $info = [];
        
        $classes = [
            'HappyPlace\\Services\\ListingService',
            'HappyPlace\\Services\\FormService',
            'HappyPlace\\Services\\ImportService',
            'HappyPlace\\Forms\\ListingFormHandler'
        ];
        
        foreach ($classes as $class) {
            if (class_exists($class)) {
                $reflection = new \ReflectionClass($class);
                $info[$class] = [
                    'file' => $reflection->getFileName(),
                    'methods' => array_map(function($method) {
                        return $method->getName();
                    }, $reflection->getMethods(\ReflectionMethod::IS_PUBLIC)),
                    'namespace' => $reflection->getNamespaceName(),
                    'short_name' => $reflection->getShortName()
                ];
            } else {
                $info[$class] = ['error' => 'Class not found'];
            }
        }
        
        return $info;
    }
}