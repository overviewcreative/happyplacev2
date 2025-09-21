<?php
/**
 * Business Service Provider
 * 
 * Registers business logic services for the plugin
 *
 * @package HappyPlace\Providers
 * @version 4.0.0
 */

namespace HappyPlace\Providers;

use HappyPlace\Core\ServiceProvider;
use HappyPlace\Services\LeadService;
use HappyPlace\Services\ListingService;
use HappyPlace\Services\AgentService;
use HappyPlace\Services\GeocodingService;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Business Service Provider Class
 * 
 * Handles registration of all business logic services
 * 
 * @since 4.0.0
 */
class BusinessServiceProvider extends ServiceProvider {
    
    /**
     * Register services
     * 
     * @return void
     */
    public function register(): void {
        // Lead Service
        $this->singleton('lead_service', function() {
            return new LeadService();
        });
        
        // Listing Service
        $this->singleton('listing_service', function() {
            return new ListingService();
        });
        
        // Agent Service  
        $this->singleton('agent_service', function() {
            return new AgentService();
        });
        
        // Geocoding Service
        $this->singleton('geocoding_service', function() {
            return new GeocodingService();
        });
    }
    
    /**
     * Boot services
     * 
     * @return void
     */
    public function boot(): void {
        // Initialize all business services
        $services = [
            'lead_service',
            'listing_service', 
            'agent_service',
            'geocoding_service'
        ];
        
        foreach ($services as $service_name) {
            try {
                $service = $this->container->get($service_name);
                if (method_exists($service, 'init')) {
                    $service->init();
                }
            } catch (\Exception $e) {
                hp_log("Failed to initialize service {$service_name}: " . $e->getMessage(), 'error', 'BUSINESS_PROVIDER');
            }
        }
    }
}
