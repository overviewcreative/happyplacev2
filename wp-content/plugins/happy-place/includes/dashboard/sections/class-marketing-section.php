<?php
/**
 * Dashboard Marketing Section
 * Marketing suite tools and campaign management
 *
 * @package HappyPlace
 */

namespace HappyPlace\Dashboard;

if (!defined('ABSPATH')) {
    exit;
}

class Marketing_Section {

    private Dashboard_Manager $dashboard_manager;

    public function __construct(Dashboard_Manager $dashboard_manager) {
        $this->dashboard_manager = $dashboard_manager;
    }

    public function render(): void {
        $action = $this->dashboard_manager->get_current_action();
        
        echo '<div class="hpt-marketing-section">';
        
        // Add debug info
        echo '<!-- Marketing Section Debug -->';
        echo '<!-- Action: ' . esc_html($action) . ' -->';
        
        // Always render the flyer generator for now
        $this->render_flyer_generator_direct();
        
        echo '</div>';
    }

    private function render_marketing_overview(): void {
        echo '<div class="hpt-marketing-overview">';
        
        // Header
        echo '<div class="hpt-section-header">';
        echo '<div class="hpt-section-header__left">';
        echo '<h2>Marketing Suite</h2>';
        echo '<p>Create professional marketing materials for your listings with our flyer generator.</p>';
        echo '</div>';
        echo '</div>';

        // Main Flyer Generator Tool
        echo '<div class="hpt-marketing-primary-tool">';
        echo '<div class="hpt-flyer-generator-card hpt-card">';
        echo '<div class="hpt-card__body">';
        
        echo '<div class="hpt-tool-card__header">';
        echo '<div class="hpt-tool-card__icon hpt-tool-card__icon--primary">';
        echo '<span class="dashicons dashicons-media-document"></span>';
        echo '</div>';
        echo '<h3 class="hpt-tool-card__title">Flyer Generator</h3>';
        echo '</div>';
        
        echo '<p class="hpt-tool-card__description">Create professional property flyers and marketing materials with our easy-to-use generator. Perfect for listings, open houses, and property marketing campaigns.</p>';
        
        echo '<ul class="hpt-tool-card__features">';
        echo '<li><span class="dashicons dashicons-yes-alt"></span> Professional Templates</li>';
        echo '<li><span class="dashicons dashicons-yes-alt"></span> Custom Branding</li>';
        echo '<li><span class="dashicons dashicons-yes-alt"></span> Multiple Formats (PDF, PNG, Social Media)</li>';
        echo '<li><span class="dashicons dashicons-yes-alt"></span> Print & Digital Ready</li>';
        echo '</ul>';
        
        echo '<div class="hpt-tool-card__actions">';
        echo '<button id="launch-flyer-generator" class="hpt-button hpt-button--primary hpt-button--large">Start Creating</button>';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
        echo '</div>';

        // Additional Tools Coming Soon
        echo '<div class="hpt-marketing-coming-soon">';
        echo '<h3>Additional Tools Coming Soon</h3>';
        echo '<div class="hpt-coming-soon-grid">';
        
        $coming_soon_tools = [
            ['title' => 'Social Media Posts', 'icon' => 'dashicons-share', 'description' => 'Instagram, Facebook, Twitter graphics'],
            ['title' => 'Email Headers', 'icon' => 'dashicons-email-alt', 'description' => 'Professional email campaign headers'],
            ['title' => 'Business Cards', 'icon' => 'dashicons-id', 'description' => 'Property-specific business cards'],
        ];
        
        foreach ($coming_soon_tools as $tool) {
            echo '<div class="hpt-coming-soon-card">';
            echo '<div class="hpt-coming-soon-icon">';
            echo '<span class="dashicons ' . esc_attr($tool['icon']) . '"></span>';
            echo '</div>';
            echo '<h4>' . esc_html($tool['title']) . '</h4>';
            echo '<p>' . esc_html($tool['description']) . '</p>';
            echo '<span class="hpt-coming-soon-badge">Coming Soon</span>';
            echo '</div>';
        }
        
        echo '</div>';
        echo '</div>';

        // Recent Marketing Activity
        echo '<div class="hpt-marketing-recent hpt-card">';
        echo '<div class="hpt-card__header">';
        echo '<h3>Recent Marketing Activity</h3>';
        echo '</div>';
        echo '<div class="hpt-card__body">';
        $this->render_recent_activity();
        echo '</div>';
        echo '</div>';

        // Hidden Flyer Generator Interface
        echo '<div id="flyer-generator-interface" class="hpt-flyer-generator-interface" style="display:none; width:100%; min-height:800px; background:#fff; padding:20px; margin-top:20px; border:1px solid #ddd;">';
        
        // Generator Header
        echo '<div class="hpt-section-header">';
        echo '<div class="hpt-section-header__left">';
        echo '<h2>Flyer Generator</h2>';
        echo '<p>Create professional property flyers in just a few steps.</p>';
        echo '</div>';
        echo '<div class="hpt-section-header__right">';
        echo '<button id="back-to-marketing-overview" class="hpt-button hpt-button--outline">';
        echo '<span class="dashicons dashicons-arrow-left-alt2"></span> Back to Marketing';
        echo '</button>';
        echo '</div>';
        echo '</div>';

        // Generator Interface
        echo '<div class="hpt-flyer-generator-workspace" style="display:flex; gap:30px; margin-top:30px;">';
        
        // Left Panel - Controls
        echo '<div class="hpt-generator-controls" style="flex:1; max-width:400px; background:#f9f9f9; padding:20px; border-radius:8px;">';
        
        // Step 1: Select Listing
        echo '<div class="hpt-control-section">';
        echo '<h3><span class="hpt-step-number">1</span> Select Property</h3>';
        echo '<div class="hpt-listing-dropdown">';
        echo '<select id="flyer-listing-select" class="hpt-select">';
        echo '<option value="">-- Choose a listing --</option>';
        $this->render_listing_options();
        echo '</select>';
        echo '<button id="refresh-listings" class="hpt-button hpt-button--outline hpt-button--sm">';
        echo '<span class="dashicons dashicons-update"></span>';
        echo '</button>';
        echo '</div>';
        echo '</div>';

        // Step 2: Template & Campaign Type
        echo '<div class="hpt-control-section" id="template-section" style="display:none;">';
        echo '<h3><span class="hpt-step-number">2</span> Choose Template & Type</h3>';
        
        echo '<div class="hpt-template-selector">';
        echo '<h4>Template Style</h4>';
        echo '<div class="hpt-template-grid">';
        $templates = [
            ['id' => 'parker-group', 'name' => 'Parker Group', 'description' => 'Professional branded design'],
            ['id' => 'modern', 'name' => 'Modern', 'description' => 'Clean, contemporary style'],
            ['id' => 'luxury', 'name' => 'Luxury', 'description' => 'Elegant premium design'],
            ['id' => 'minimal', 'name' => 'Minimal', 'description' => 'Simple, focused layout']
        ];
        
        foreach ($templates as $template) {
            echo '<div class="hpt-template-option' . ($template['id'] === 'parker-group' ? ' selected' : '') . '" data-template="' . esc_attr($template['id']) . '">';
            echo '<div class="hpt-template-preview">📄</div>';
            echo '<h5>' . esc_html($template['name']) . '</h5>';
            echo '<p>' . esc_html($template['description']) . '</p>';
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';

        echo '<div class="hpt-campaign-selector">';
        echo '<h4>Campaign Type</h4>';
        echo '<div class="hpt-campaign-types">';
        $campaigns = [
            ['id' => 'for_sale', 'name' => 'For Sale', 'active' => true],
            ['id' => 'just_listed', 'name' => 'Just Listed', 'active' => false],
            ['id' => 'open_house', 'name' => 'Open House', 'active' => false],
            ['id' => 'price_change', 'name' => 'Price Reduced', 'active' => false],
            ['id' => 'sold', 'name' => 'Sold', 'active' => false],
            ['id' => 'coming_soon', 'name' => 'Coming Soon', 'active' => false]
        ];
        
        foreach ($campaigns as $campaign) {
            echo '<button class="hpt-campaign-type' . ($campaign['active'] ? ' selected' : '') . '" data-type="' . esc_attr($campaign['id']) . '">';
            echo esc_html($campaign['name']);
            echo '</button>';
        }
        echo '</div>';
        echo '</div>';
        echo '</div>';

        // Step 3: Options
        echo '<div class="hpt-control-section" id="options-section" style="display:none;">';
        echo '<h3><span class="hpt-step-number">3</span> Options</h3>';
        
        echo '<div class="hpt-options-grid">';
        echo '<label class="hpt-option-toggle">';
        echo '<input type="checkbox" id="include-qr-code" checked>';
        echo '<span class="hpt-toggle-slider"></span>';
        echo 'Include QR Code';
        echo '</label>';
        
        echo '<label class="hpt-option-toggle">';
        echo '<input type="checkbox" id="include-agent-photo" checked>';
        echo '<span class="hpt-toggle-slider"></span>';
        echo 'Include Agent Photo';
        echo '</label>';
        
        echo '<label class="hpt-option-toggle">';
        echo '<input type="checkbox" id="include-company-logo" checked>';
        echo '<span class="hpt-toggle-slider"></span>';
        echo 'Include Company Logo';
        echo '</label>';
        echo '</div>';
        echo '</div>';

        // Generate Button
        echo '<div class="hpt-generator-actions">';
        echo '<button id="generate-flyer" class="hpt-button hpt-button--primary hpt-button--large" disabled>';
        echo '<span class="dashicons dashicons-admin-media"></span> Generate Flyer';
        echo '</button>';
        echo '</div>';
        
        echo '</div>';

        // Right Panel - Preview
        echo '<div class="hpt-generator-preview">';
        echo '<h3>Preview</h3>';
        echo '<div class="hpt-canvas-container">';
        echo '<canvas id="flyer-canvas" width="850" height="1100"></canvas>';
        echo '<div class="hpt-canvas-overlay">';
        echo '<div class="hpt-canvas-placeholder">';
        echo '<span class="dashicons dashicons-media-document"></span>';
        echo '<p>Select a listing to see preview</p>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="hpt-download-section" style="display:none;">';
        echo '<h4>Download Options</h4>';
        echo '<div class="hpt-download-buttons">';
        echo '<button id="download-png" class="hpt-button hpt-button--outline">';
        echo '<span class="dashicons dashicons-download"></span> PNG';
        echo '</button>';
        echo '<button id="download-pdf" class="hpt-button hpt-button--outline">';
        echo '<span class="dashicons dashicons-pdf"></span> PDF';
        echo '</button>';
        echo '<button id="download-print" class="hpt-button hpt-button--outline">';
        echo '<span class="dashicons dashicons-printer"></span> Print Quality';
        echo '</button>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';

        // Add JavaScript for the interface
        $this->render_marketing_overview_script();
        
        echo '</div>';
    }

    private function render_flyer_generator_direct(): void {
        echo '<div class="hpt-marketing-overview">';
        
        // Header
        echo '<div class="hpt-section-header">';
        echo '<div class="hpt-section-header__left">';
        echo '<h2>Flyer Generator</h2>';
        echo '<p>Create professional property flyers and marketing materials with our easy-to-use generator.</p>';
        echo '</div>';
        echo '</div>';

        // Flyer Generator Interface (Always Visible)
        echo '<div class="hpt-flyer-generator-workspace" style="display:flex; gap:30px; margin-top:30px;">';
        
        // Left Panel - Controls
        echo '<div class="hpt-generator-controls" style="flex:1; max-width:400px; background:#f9f9f9; padding:20px; border-radius:8px;">';
        
        // Step 1: Select Listing
        echo '<div class="hpt-control-section">';
        echo '<h3><span class="hpt-step-number">1</span> Select Property</h3>';
        echo '<div class="hpt-listing-dropdown">';
        echo '<select id="flyer-listing-select" class="hpt-select">';
        echo '<option value="">-- Choose a listing --</option>';
        $this->render_listing_options();
        echo '</select>';
        echo '<button id="refresh-listings" class="hpt-button hpt-button--outline hpt-button--sm">';
        echo '<span class="dashicons dashicons-update"></span>';
        echo '</button>';
        echo '</div>';
        echo '</div>';

        // Step 2: Template & Campaign Type
        echo '<div class="hpt-control-section" id="template-section" style="display:none;">';
        echo '<h3><span class="hpt-step-number">2</span> Choose Template & Type</h3>';
        
        echo '<div class="hpt-template-selector">';
        echo '<h4>Template Style</h4>';
        echo '<div class="hpt-template-grid">';
        $templates = [
            ['id' => 'parker-group', 'name' => 'Parker Group', 'description' => 'Professional branded design'],
            ['id' => 'modern', 'name' => 'Modern', 'description' => 'Clean, contemporary style'],
            ['id' => 'luxury', 'name' => 'Luxury', 'description' => 'Elegant premium design'],
            ['id' => 'minimal', 'name' => 'Minimal', 'description' => 'Simple, focused layout']
        ];
        
        foreach ($templates as $template) {
            echo '<div class="hpt-template-option' . ($template['id'] === 'parker-group' ? ' selected' : '') . '" data-template="' . esc_attr($template['id']) . '">';
            echo '<div class="hpt-template-preview">📄</div>';
            echo '<h5>' . esc_html($template['name']) . '</h5>';
            echo '<p>' . esc_html($template['description']) . '</p>';
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';

        echo '<div class="hpt-campaign-selector">';
        echo '<h4>Campaign Type</h4>';
        echo '<div class="hpt-campaign-types">';
        $campaigns = [
            ['id' => 'for_sale', 'name' => 'For Sale', 'active' => true],
            ['id' => 'just_listed', 'name' => 'Just Listed', 'active' => false],
            ['id' => 'open_house', 'name' => 'Open House', 'active' => false],
            ['id' => 'price_change', 'name' => 'Price Reduced', 'active' => false],
            ['id' => 'sold', 'name' => 'Sold', 'active' => false],
            ['id' => 'coming_soon', 'name' => 'Coming Soon', 'active' => false]
        ];
        
        foreach ($campaigns as $campaign) {
            echo '<button class="hpt-campaign-type' . ($campaign['active'] ? ' selected' : '') . '" data-type="' . esc_attr($campaign['id']) . '">';
            echo esc_html($campaign['name']);
            echo '</button>';
        }
        echo '</div>';
        echo '</div>';
        echo '</div>';

        // Step 3: Options
        echo '<div class="hpt-control-section" id="options-section" style="display:none;">';
        echo '<h3><span class="hpt-step-number">3</span> Options</h3>';
        
        echo '<div class="hpt-options-grid">';
        echo '<label class="hpt-option-toggle">';
        echo '<input type="checkbox" id="include-qr-code" checked>';
        echo '<span class="hpt-toggle-slider"></span>';
        echo 'Include QR Code';
        echo '</label>';
        
        echo '<label class="hpt-option-toggle">';
        echo '<input type="checkbox" id="include-agent-photo" checked>';
        echo '<span class="hpt-toggle-slider"></span>';
        echo 'Include Agent Photo';
        echo '</label>';
        
        echo '<label class="hpt-option-toggle">';
        echo '<input type="checkbox" id="include-company-logo" checked>';
        echo '<span class="hpt-toggle-slider"></span>';
        echo 'Include Company Logo';
        echo '</label>';
        echo '</div>';
        echo '</div>';

        // Generate Button
        echo '<div class="hpt-generator-actions">';
        echo '<button id="generate-flyer" class="hpt-button hpt-button--primary hpt-button--large" disabled>';
        echo '<span class="dashicons dashicons-admin-media"></span> Generate Flyer';
        echo '</button>';
        echo '</div>';
        
        echo '</div>';

        // Right Panel - Preview
        echo '<div class="hpt-generator-preview" style="flex:1; background:#fff; padding:20px; border-radius:8px; border:1px solid #ddd;">';
        echo '<h3>Preview</h3>';
        echo '<div class="hpt-canvas-container">';
        echo '<canvas id="flyer-canvas" width="850" height="1100"></canvas>';
        echo '<div class="hpt-canvas-overlay">';
        echo '<div class="hpt-canvas-placeholder">';
        echo '<span class="dashicons dashicons-media-document"></span>';
        echo '<p>Select a listing to see preview</p>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="hpt-download-section" style="display:none;">';
        echo '<h4>Download Options</h4>';
        echo '<div class="hpt-download-buttons">';
        echo '<button id="download-png" class="hpt-button hpt-button--outline">';
        echo '<span class="dashicons dashicons-download"></span> PNG';
        echo '</button>';
        echo '<button id="download-pdf" class="hpt-button hpt-button--outline">';
        echo '<span class="dashicons dashicons-pdf"></span> PDF';
        echo '</button>';
        echo '<button id="download-print" class="hpt-button hpt-button--outline">';
        echo '<span class="dashicons dashicons-printer"></span> Print Quality';
        echo '</button>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '</div>';

        // Additional Tools Coming Soon (Simplified)
        echo '<div class="hpt-marketing-coming-soon" style="margin-top:40px;">';
        echo '<h3>Additional Tools Coming Soon</h3>';
        echo '<div class="hpt-coming-soon-grid" style="display:flex; gap:20px; justify-content:center;">';
        
        $coming_soon_tools = [
            ['title' => 'Social Media Posts', 'icon' => 'dashicons-share', 'description' => 'Instagram, Facebook, Twitter graphics'],
            ['title' => 'Email Headers', 'icon' => 'dashicons-email-alt', 'description' => 'Professional email campaign headers'],
            ['title' => 'Business Cards', 'icon' => 'dashicons-id', 'description' => 'Property-specific business cards'],
        ];
        
        foreach ($coming_soon_tools as $tool) {
            echo '<div class="hpt-coming-soon-card" style="padding:15px; background:#f9f9f9; border-radius:8px; text-align:center; flex:1;">';
            echo '<div class="hpt-coming-soon-icon" style="font-size:24px; margin-bottom:10px;">';
            echo '<span class="dashicons ' . esc_attr($tool['icon']) . '"></span>';
            echo '</div>';
            echo '<h4>' . esc_html($tool['title']) . '</h4>';
            echo '<p>' . esc_html($tool['description']) . '</p>';
            echo '<span class="hpt-coming-soon-badge" style="background:#51bae0; color:white; padding:4px 8px; border-radius:12px; font-size:12px;">Coming Soon</span>';
            echo '</div>';
        }
        
        echo '</div>';
        echo '</div>';

        // Add JavaScript for the interface
        $this->render_flyer_generator_simple_script();
        
        echo '</div>';
    }

    private function render_marketing_generator(): void {
        echo '<div class="hpt-marketing-generator">';
        
        // Header
        echo '<div class="hpt-section-header">';
        echo '<div class="hpt-section-header__left">';
        echo '<h2>Flyer Generator</h2>';
        echo '<p>Create professional property flyers in just a few steps.</p>';
        echo '</div>';
        echo '<div class="hpt-section-header__right">';
        echo '<a href="' . esc_url(home_url('/agent-dashboard/marketing/')) . '" class="hpt-button hpt-button--outline">';
        echo '<span class="dashicons dashicons-arrow-left-alt2"></span> Back to Marketing';
        echo '</a>';
        echo '</div>';
        echo '</div>';

        // Generator Interface
        echo '<div class="hpt-flyer-generator-interface">';
        
        // Left Panel - Controls
        echo '<div class="hpt-generator-controls">';
        
        // Step 1: Select Listing
        echo '<div class="hpt-control-section">';
        echo '<h3><span class="hpt-step-number">1</span> Select Property</h3>';
        echo '<div class="hpt-listing-dropdown">';
        echo '<select id="flyer-listing-select" class="hpt-select">';
        echo '<option value="">-- Choose a listing --</option>';
        $this->render_listing_options();
        echo '</select>';
        echo '<button id="refresh-listings" class="hpt-button hpt-button--outline hpt-button--sm">';
        echo '<span class="dashicons dashicons-update"></span>';
        echo '</button>';
        echo '</div>';
        echo '</div>';

        // Step 2: Template & Campaign Type
        echo '<div class="hpt-control-section" id="template-section" style="display:none;">';
        echo '<h3><span class="hpt-step-number">2</span> Choose Template & Type</h3>';
        
        echo '<div class="hpt-template-selector">';
        echo '<h4>Template Style</h4>';
        echo '<div class="hpt-template-grid">';
        $templates = [
            ['id' => 'parker-group', 'name' => 'Parker Group', 'description' => 'Professional branded design'],
            ['id' => 'modern', 'name' => 'Modern', 'description' => 'Clean, contemporary style'],
            ['id' => 'luxury', 'name' => 'Luxury', 'description' => 'Elegant premium design'],
            ['id' => 'minimal', 'name' => 'Minimal', 'description' => 'Simple, focused layout']
        ];
        
        foreach ($templates as $template) {
            echo '<div class="hpt-template-option' . ($template['id'] === 'parker-group' ? ' selected' : '') . '" data-template="' . esc_attr($template['id']) . '">';
            echo '<div class="hpt-template-preview">📄</div>';
            echo '<h5>' . esc_html($template['name']) . '</h5>';
            echo '<p>' . esc_html($template['description']) . '</p>';
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';

        echo '<div class="hpt-campaign-selector">';
        echo '<h4>Campaign Type</h4>';
        echo '<div class="hpt-campaign-types">';
        $campaigns = [
            ['id' => 'for_sale', 'name' => 'For Sale', 'active' => true],
            ['id' => 'just_listed', 'name' => 'Just Listed', 'active' => false],
            ['id' => 'open_house', 'name' => 'Open House', 'active' => false],
            ['id' => 'price_change', 'name' => 'Price Reduced', 'active' => false],
            ['id' => 'sold', 'name' => 'Sold', 'active' => false],
            ['id' => 'coming_soon', 'name' => 'Coming Soon', 'active' => false]
        ];
        
        foreach ($campaigns as $campaign) {
            echo '<button class="hpt-campaign-type' . ($campaign['active'] ? ' selected' : '') . '" data-type="' . esc_attr($campaign['id']) . '">';
            echo esc_html($campaign['name']);
            echo '</button>';
        }
        echo '</div>';
        echo '</div>';
        echo '</div>';

        // Step 3: Options
        echo '<div class="hpt-control-section" id="options-section" style="display:none;">';
        echo '<h3><span class="hpt-step-number">3</span> Options</h3>';
        
        echo '<div class="hpt-options-grid">';
        echo '<label class="hpt-option-toggle">';
        echo '<input type="checkbox" id="include-qr-code" checked>';
        echo '<span class="hpt-toggle-slider"></span>';
        echo 'Include QR Code';
        echo '</label>';
        
        echo '<label class="hpt-option-toggle">';
        echo '<input type="checkbox" id="include-agent-photo" checked>';
        echo '<span class="hpt-toggle-slider"></span>';
        echo 'Include Agent Photo';
        echo '</label>';
        
        echo '<label class="hpt-option-toggle">';
        echo '<input type="checkbox" id="include-company-logo" checked>';
        echo '<span class="hpt-toggle-slider"></span>';
        echo 'Include Company Logo';
        echo '</label>';
        echo '</div>';
        echo '</div>';

        // Generate Button
        echo '<div class="hpt-generator-actions">';
        echo '<button id="generate-flyer" class="hpt-button hpt-button--primary hpt-button--large" disabled>';
        echo '<span class="dashicons dashicons-admin-media"></span> Generate Flyer';
        echo '</button>';
        echo '</div>';
        
        echo '</div>';

        // Right Panel - Preview
        echo '<div class="hpt-generator-preview">';
        echo '<h3>Preview</h3>';
        echo '<div class="hpt-canvas-container">';
        echo '<canvas id="flyer-canvas" width="850" height="1100"></canvas>';
        echo '<div class="hpt-canvas-overlay">';
        echo '<div class="hpt-canvas-placeholder">';
        echo '<span class="dashicons dashicons-media-document"></span>';
        echo '<p>Select a listing to see preview</p>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="hpt-download-section" style="display:none;">';
        echo '<h4>Download Options</h4>';
        echo '<div class="hpt-download-buttons">';
        echo '<button id="download-png" class="hpt-button hpt-button--outline">';
        echo '<span class="dashicons dashicons-download"></span> PNG';
        echo '</button>';
        echo '<button id="download-pdf" class="hpt-button hpt-button--outline">';
        echo '<span class="dashicons dashicons-pdf"></span> PDF';
        echo '</button>';
        echo '<button id="download-print" class="hpt-button hpt-button--outline">';
        echo '<span class="dashicons dashicons-printer"></span> Print Quality';
        echo '</button>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '</div>';

        $this->render_generator_script();
        echo '</div>';
    }

    private function render_marketing_overview_script(): void {
        echo '<script>';
        echo 'jQuery(document).ready(function($) {';
        echo 'console.log("Marketing overview script initialized");';
        echo 'console.log("hptDashboard:", window.hptDashboard);';
        
        // Initialize global variables for debugging
        echo 'window.currentListing = null;';
        echo 'window.currentTemplate = "parker-group";';
        echo 'window.currentCampaignType = "for_sale";';
        echo 'window.fabricCanvas = null;';
        echo 'window.currentListingData = null;';
        
        // Launch flyer generator
        echo '$("#launch-flyer-generator").on("click", function() {';
        echo 'console.log("Launch flyer generator clicked");';
        echo 'console.log("Overview element:", $(".hpt-marketing-overview").length);';
        echo 'console.log("Generator interface element:", $("#flyer-generator-interface").length);';
        echo '$(".hpt-marketing-overview").hide();';
        echo '$("#flyer-generator-interface").addClass("show").css({';
        echo '"display": "block",';
        echo '"visibility": "visible",';
        echo '"opacity": "1",';
        echo '"position": "relative"';
        echo '});';
        echo 'console.log("Generator interface should now be visible, class added:", $("#flyer-generator-interface").hasClass("show"));';
        echo 'console.log("Interface height:", $("#flyer-generator-interface").height());';
        echo 'console.log("Interface width:", $("#flyer-generator-interface").width());';
        echo 'console.log("Interface content length:", $("#flyer-generator-interface").html().length);';
        echo 'console.log("Interface computed display:", window.getComputedStyle(document.getElementById("flyer-generator-interface")).display);';
        echo 'initializeFabricCanvas();';
        echo '});';
        
        // Back to marketing overview
        echo '$("#back-to-marketing-overview").on("click", function() {';
        echo '$("#flyer-generator-interface").hide();';
        echo '$(".hpt-marketing-overview").show();';
        echo '});';
        
        // Initialize Fabric.js canvas
        echo 'function initializeFabricCanvas() {';
        echo 'if (typeof fabric !== "undefined" && !window.fabricCanvas) {';
        echo 'window.fabricCanvas = new fabric.Canvas("flyer-canvas", {';
        echo 'backgroundColor: "#ffffff",';
        echo 'width: 850,';
        echo 'height: 1100';
        echo '});';
        echo '}';
        echo '}';
        
        // Load Fabric.js if not already loaded
        echo 'if (typeof fabric === "undefined") {';
        echo 'var script = document.createElement("script");';
        echo 'script.src = "https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js";';
        echo 'script.onload = function() { console.log("Fabric.js loaded"); };';
        echo 'document.head.appendChild(script);';
        echo '}';
        
        // Listing selection
        echo '$("#flyer-listing-select").on("change", function() {';
        echo 'var listingId = $(this).val();';
        echo 'console.log("Listing selected:", listingId);';
        echo 'window.currentListing = listingId;';
        echo '// currentListing = window.currentListing; // Already using window.currentListing';
        echo 'if (listingId) {';
        echo '$("#template-section").show();';
        echo '$("#options-section").show();';
        echo '$("#generate-flyer").prop("disabled", false);';
        echo '$(".hpt-canvas-placeholder").hide();';
        echo 'loadListingData(listingId);';
        echo '} else {';
        echo '$("#template-section").hide();';
        echo '$("#options-section").hide();';
        echo '$("#generate-flyer").prop("disabled", true);';
        echo '$(".hpt-canvas-placeholder").show();';
        echo '}';
        echo '});';

        // Template selection
        echo '$(document).on("click", ".hpt-template-option", function() {';
        echo '$(".hpt-template-option").removeClass("selected");';
        echo '$(this).addClass("selected");';
        echo 'window.currentTemplate = $(this).data("template");';
        echo '// currentTemplate = window.currentTemplate; // Already using window.currentTemplate';
        echo 'console.log("Template selected:", window.currentTemplate);';
        echo '});';

        // Campaign type selection
        echo '$(document).on("click", ".hpt-campaign-type", function() {';
        echo '$(".hpt-campaign-type").removeClass("selected");';
        echo '$(this).addClass("selected");';
        echo 'window.currentCampaignType = $(this).data("type");';
        echo '// currentCampaignType = window.currentCampaignType; // Already using window.currentCampaignType';
        echo 'console.log("Campaign type selected:", window.currentCampaignType);';
        echo '});';

        // Generate flyer
        echo '$("#generate-flyer").on("click", function() {';
        echo 'console.log("=== GENERATE FLYER CLICKED ===");';
        echo 'console.log("currentListing:", window.currentListing);';
        echo 'console.log("currentListingData:", window.currentListingData);';
        echo 'console.log("fabricCanvas:", window.fabricCanvas);';
        echo 'console.log("currentTemplate:", window.currentTemplate);';
        echo 'console.log("currentCampaignType:", window.currentCampaignType);';
        echo 'if (!window.currentListing) {';
        echo 'alert("Please select a listing first");';
        echo 'return;';
        echo '}';
        echo 'if (!window.currentListingData) {';
        echo 'alert("Listing data not loaded. Please select a different listing.");';
        echo 'console.error("No listing data available");';
        echo 'return;';
        echo '}';
        echo 'if (!window.fabricCanvas) {';
        echo 'alert("Canvas not ready. Please wait and try again.");';
        echo 'console.error("Fabric canvas not initialized");';
        echo 'return;';
        echo '}';
        echo 'console.log("All checks passed, generating flyer...");';
        echo 'generateFlyer();';
        echo '});';
        
        // Download buttons
        echo '$("#download-png").on("click", function() { downloadFlyer("png"); });';
        echo '$("#download-pdf").on("click", function() { downloadFlyer("pdf"); });';
        echo '$("#download-print").on("click", function() { downloadFlyer("print"); });';

        // Functions
        echo 'function loadListingData(listingId) {';
        echo 'if (!window.hptDashboard) {';
        echo 'console.error("hptDashboard object not available");';
        echo 'return;';
        echo '}';
        echo 'console.log("Loading listing data for ID:", listingId);';
        echo '$.post(hptDashboard.ajaxUrl, {';
        echo 'action: "hpt_dashboard_action",';
        echo 'dashboard_action: "get_listing_data",';
        echo 'listing_id: listingId,';
        echo 'source: "marketing",';
        echo 'nonce: hptDashboard.nonce';
        echo '}, function(response) {';
        echo 'console.log("=== AJAX RESPONSE RECEIVED ===");';
        echo 'console.log("Response:", response);';
        echo 'if (response.success) {';
        echo 'window.currentListingData = response.data;';
        echo '// currentListingData = window.currentListingData; // Already using window.currentListingData';
        echo 'console.log("✅ Listing data loaded successfully:", window.currentListingData);';
        echo 'console.log("Agent data:", window.currentListingData.agent);';
        echo 'console.log("Price:", window.currentListingData.price);';
        echo 'console.log("Address:", window.currentListingData.full_address);';
        echo '} else {';
        echo 'console.error("❌ Failed to load listing data:", response);';
        echo 'if (response.data && response.data.message) {';
        echo 'console.error("Error message:", response.data.message);';
        echo '}';
        echo '}';
        echo '}).fail(function(xhr, status, error) {';
        echo 'console.error("❌ AJAX request failed:", status, error);';
        echo 'console.error("XHR:", xhr);';
        echo '});';
        echo '}';

        echo 'function generateFlyer() {';
        echo 'console.log("=== GENERATE FLYER FUNCTION CALLED ===");';
        echo 'console.log("Fabric canvas exists:", !!window.fabricCanvas);';
        echo 'console.log("Canvas size:", window.fabricCanvas ? window.fabricCanvas.width + "x" + window.fabricCanvas.height : "N/A");';
        echo 'console.log("Current listing data:", window.currentListingData);';
        echo 'if (!window.fabricCanvas) {';
        echo 'alert("Canvas not initialized. Please wait for Fabric.js to load.");';
        echo 'console.error("Fabric canvas is null or undefined");';
        echo 'return;';
        echo '}';
        echo 'console.log("Starting flyer generation...");';
        echo 'showLoading(true);';
        echo 'window.fabricCanvas.clear();';
        echo 'console.log("Canvas cleared, calling createParkerGroupFlyer()");';
        echo 'createParkerGroupFlyer();';
        echo '$(".hpt-download-section").show();';
        echo 'setTimeout(function() { showLoading(false); }, 1000);';
        echo 'console.log("Flyer generation process completed");';
        echo '}';

        echo 'function createParkerGroupFlyer() {';
        echo 'if (!window.currentListingData) return;';
        echo 'var data = window.currentListingData;';
        echo 'console.log("Creating flyer with data:", data);';
        
        // Background
        echo 'var background = new fabric.Rect({';
        echo 'left: 0, top: 0, width: 850, height: 1100,';
        echo 'fill: "#51bae0", selectable: false';
        echo '});';
        echo 'window.fabricCanvas.add(background);';
        
        // Header text based on campaign type
        echo 'var campaignTexts = {';
        echo '"for_sale": "FOR SALE",';
        echo '"just_listed": "JUST LISTED",';
        echo '"open_house": "OPEN HOUSE",';
        echo '"price_change": "PRICE REDUCED",';
        echo '"sold": "SOLD",';
        echo '"coming_soon": "COMING SOON"';
        echo '};';
        echo 'var headerText = new fabric.Text(campaignTexts[window.currentCampaignType] || "FOR SALE", {';
        echo 'left: 55, top: 50, fontSize: 80,';
        echo 'fontFamily: "Arial, sans-serif", fontWeight: "bold",';
        echo 'fill: "#ffffff", selectable: false';
        echo '});';
        echo 'window.fabricCanvas.add(headerText);';
        
        // Property stats section
        echo 'var statsSection = new fabric.Rect({';
        echo 'left: 0, top: 655, width: 850, height: 75,';
        echo 'fill: "#082f49", selectable: false';
        echo '});';
        echo 'window.fabricCanvas.add(statsSection);';
        
        // Property stats text
        echo 'var beds = data.bedrooms || "N/A";';
        echo 'var baths = data.bathrooms || "N/A";';
        echo 'var sqft = data.square_feet ? data.square_feet.toLocaleString() : "N/A";';
        
        echo 'var statsText = new fabric.Text(beds + " Bed • " + baths + " Bath • " + sqft + " Sq Ft", {';
        echo 'left: 75, top: 680, fontSize: 16,';
        echo 'fontFamily: "Arial, sans-serif", fontWeight: "500",';
        echo 'fill: "#ffffff", selectable: false';
        echo '});';
        echo 'window.fabricCanvas.add(statsText);';
        
        // Price
        echo 'var priceText = data.price ? "$" + parseInt(data.price).toLocaleString() : "Price Upon Request";';
        echo 'var price = new fabric.Text(priceText, {';
        echo 'left: 750, top: 670, fontSize: 32,';
        echo 'fontFamily: "Arial, sans-serif", fontWeight: "700",';
        echo 'fill: "#ffffff", originX: "right", selectable: false';
        echo '});';
        echo 'window.fabricCanvas.add(price);';
        
        // White section for details
        echo 'var whiteSection = new fabric.Rect({';
        echo 'left: 0, top: 730, width: 850, height: 370,';
        echo 'fill: "#f5f5f4", selectable: false';
        echo '});';
        echo 'window.fabricCanvas.add(whiteSection);';
        
        // Address
        echo 'var address = (data.street_address || "") + (data.city ? ", " + data.city : "");';
        echo 'if (!address.trim()) address = "Address Not Available";';
        echo 'var addressText = new fabric.Text(address, {';
        echo 'left: 55, top: 760, fontSize: 32,';
        echo 'fontFamily: "Arial, sans-serif", fontWeight: "600",';
        echo 'fill: "#51bae0", selectable: false';
        echo '});';
        echo 'window.fabricCanvas.add(addressText);';
        
        // Description
        echo 'var description = data.short_description || data.description || "Beautiful property available for viewing.";';
        echo 'if (description.length > 200) description = description.substring(0, 197) + "...";';
        echo 'var descText = new fabric.Text(description, {';
        echo 'left: 55, top: 820, fontSize: 14,';
        echo 'fontFamily: "Arial, sans-serif",';
        echo 'fill: "#333333", selectable: false,';
        echo 'width: 400';
        echo '});';
        echo 'window.fabricCanvas.add(descText);';
        
        // Agent info
        echo 'if (data.agent && data.agent.name) {';
        echo 'var agentText = new fabric.Text("Contact: " + data.agent.name, {';
        echo 'left: 55, top: 950, fontSize: 16,';
        echo 'fontFamily: "Arial, sans-serif", fontWeight: "600",';
        echo 'fill: "#51bae0", selectable: false';
        echo '});';
        echo 'window.fabricCanvas.add(agentText);';
        
        echo 'if (data.agent.phone) {';
        echo 'var phoneText = new fabric.Text("Phone: " + data.agent.phone, {';
        echo 'left: 55, top: 980, fontSize: 14,';
        echo 'fontFamily: "Arial, sans-serif",';
        echo 'fill: "#333333", selectable: false';
        echo '});';
        echo 'window.fabricCanvas.add(phoneText);';
        echo '}';
        
        echo 'if (data.agent.email) {';
        echo 'var emailText = new fabric.Text("Email: " + data.agent.email, {';
        echo 'left: 55, top: 1000, fontSize: 14,';
        echo 'fontFamily: "Arial, sans-serif",';
        echo 'fill: "#333333", selectable: false';
        echo '});';
        echo 'window.fabricCanvas.add(emailText);';
        echo '}';
        echo '}';
        
        // Company branding
        echo 'var companyText = new fabric.Text("THE PARKER GROUP", {';
        echo 'left: 680, top: 980, fontSize: 18,';
        echo 'fontFamily: "Arial, sans-serif", fontWeight: "700",';
        echo 'fill: "#51bae0", textAlign: "center",';
        echo 'originX: "center", selectable: false';
        echo '});';
        echo 'window.fabricCanvas.add(companyText);';
        
        echo 'var taglineText = new fabric.Text("find your happy place", {';
        echo 'left: 680, top: 1010, fontSize: 12,';
        echo 'fontFamily: "Arial, sans-serif",';
        echo 'fill: "#51bae0", textAlign: "center",';
        echo 'originX: "center", selectable: false';
        echo '});';
        echo 'window.fabricCanvas.add(taglineText);';
        
        echo 'window.fabricCanvas.renderAll();';
        echo '}';

        echo 'function downloadFlyer(format) {';
        echo 'if (!window.fabricCanvas) return;';
        echo 'var dataURL;';
        echo 'var multiplier = format === "print" ? 4 : 2;';
        echo 'dataURL = window.fabricCanvas.toDataURL({';
        echo 'format: format === "pdf" ? "png" : format,';
        echo 'quality: 1.0,';
        echo 'multiplier: multiplier';
        echo '});';
        
        echo 'if (format === "pdf") {';
        echo '$.post(hptDashboard.ajaxUrl, {';
        echo 'action: "hpt_dashboard_action",';
        echo 'dashboard_action: "generate_pdf",';
        echo 'canvas_data: dataURL,';
        echo 'nonce: hptDashboard.nonce';
        echo '}, function(response) {';
        echo 'if (response.success && response.data.pdf_url) {';
        echo 'var link = document.createElement("a");';
        echo 'link.href = response.data.pdf_url;';
        echo 'link.download = "flyer-" + Date.now() + ".pdf";';
        echo 'link.click();';
        echo '} else {';
        echo 'alert("PDF generation failed. Downloading as PNG instead.");';
        echo 'var link = document.createElement("a");';
        echo 'link.download = "flyer-" + Date.now() + ".png";';
        echo 'link.href = dataURL;';
        echo 'link.click();';
        echo '}';
        echo '});';
        echo '} else {';
        echo 'var link = document.createElement("a");';
        echo 'link.download = "flyer-" + Date.now() + "." + (format === "print" ? "png" : format);';
        echo 'link.href = dataURL;';
        echo 'link.click();';
        echo '}';
        echo '}';

        echo 'function showLoading(show) {';
        echo 'if (show) {';
        echo '$("#generate-flyer").prop("disabled", true).html("<span class=\'dashicons dashicons-update\'></span> Generating...");';
        echo '} else {';
        echo '$("#generate-flyer").prop("disabled", false).html("<span class=\'dashicons dashicons-admin-media\'></span> Generate Flyer");';
        echo '}';
        echo '}';

        // Global showListingForm function for compatibility with other sections
        echo 'window.showListingForm = function(listingId) {';
        echo 'console.log("showListingForm called with ID:", listingId);';
        echo 'if (typeof listingId !== "undefined") {';
        echo 'window.location.href = "/agent-dashboard/listings/edit/" + listingId;';
        echo '} else {';
        echo 'window.location.href = "/agent-dashboard/listings/add/";';
        echo '}';
        echo '};';
        
        echo '});';
        echo '</script>';
        
        // Add debug script to check function availability
        echo '<script>';
        echo 'console.log("Debug Modal Script Loaded");';
        echo 'setTimeout(function() {';
        echo 'if (typeof window.showListingForm === "function") {';
        echo 'console.log("✅ showListingForm function IS available");';
        echo '} else {';
        echo 'console.log("❌ showListingForm function is NOT available");';
        echo 'console.log("Available window functions:", Object.keys(window).filter(key => typeof window[key] === "function"));';
        echo '}';
        echo 'var addButton = document.querySelector("[id*=\\"add\\"]");';
        echo 'if (addButton) {';
        echo 'console.log("✅ Add New Listing button found:", addButton.id);';
        echo '} else {';
        echo 'console.log("❌ Add New Listing button NOT found");';
        echo 'console.log("Available buttons with \\"add\\" in ID:", Array.from(document.querySelectorAll("[id*=\\"add\\"]")));';
        echo '}';
        echo '}, 100);';
        echo '</script>';
        
        // Add critical CSS for the interface
        echo '<style>';
        echo '#flyer-generator-interface { display: none !important; }';
        echo '#flyer-generator-interface.show { display: block !important; visibility: visible !important; opacity: 1 !important; background: #ffeb3b !important; border: 3px solid red !important; min-height: 500px !important; }';
        echo '.hpt-flyer-generator-workspace { display: flex; gap: 30px; margin-top: 30px; }';
        echo '.hpt-generator-controls { flex: 1; max-width: 400px; background: #f9f9f9; padding: 20px; border-radius: 8px; }';
        echo '.hpt-generator-preview { flex: 1; background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #ddd; }';
        echo '.hpt-control-section { margin-bottom: 30px; }';
        echo '.hpt-step-number { background: #51bae0; color: white; width: 24px; height: 24px; border-radius: 50%; display: inline-block; text-align: center; line-height: 24px; font-size: 14px; margin-right: 10px; }';
        echo '.hpt-template-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px; }';
        echo '.hpt-template-option { padding: 15px; border: 2px solid #ddd; border-radius: 8px; cursor: pointer; text-align: center; }';
        echo '.hpt-template-option.selected { border-color: #51bae0; background: #f0f9ff; }';
        echo '.hpt-campaign-types { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 15px; }';
        echo '.hpt-campaign-type { padding: 8px 16px; border: 1px solid #ddd; background: white; border-radius: 20px; cursor: pointer; }';
        echo '.hpt-campaign-type.selected { background: #51bae0; color: white; border-color: #51bae0; }';
        echo '.hpt-canvas-container { position: relative; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; }';
        echo '.hpt-canvas-placeholder { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; color: #666; }';
        echo '</style>';
    }

    private function render_listing_options(): void {
        $agent_id = $this->dashboard_manager->get_current_agent_id();
        
        // Debug: Add some console output
        echo '<!-- Debug: Agent ID = ' . esc_html($agent_id) . ' -->';
        
        // First, let's get all listings to see what we have
        $all_listings = get_posts([
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        
        echo '<!-- Debug: Total listings found = ' . count($all_listings) . ' -->';
        
        // If no agent ID, show all listings (for testing)
        if (!$agent_id) {
            $listings = $all_listings;
            echo '<!-- Debug: No agent ID, showing all listings -->';
        } else {
            // For ACF relationship fields, we need to query differently
            $listings = get_posts([
                'post_type' => 'listing',
                'posts_per_page' => -1,
                'meta_query' => [
                    [
                        'key' => 'listing_agent',
                        'value' => $agent_id,
                        'compare' => 'LIKE'
                    ]
                ],
                'orderby' => 'date',
                'order' => 'DESC'
            ]);
            
            echo '<!-- Debug: Agent listings found = ' . count($listings) . ' -->';
            
            // If no agent-specific listings, show all for now (for testing)
            if (empty($listings)) {
                $listings = $all_listings;
                echo '<!-- Debug: No agent listings, falling back to all listings -->';
            }
        }

        if (empty($listings)) {
            echo '<option value="">No listings found - Please create a listing first</option>';
            echo '<!-- Debug: No listings to display -->';
        } else {
            foreach ($listings as $listing) {
                $address = get_field('street_address', $listing->ID) ?: $listing->post_title;
                $city = get_field('city', $listing->ID);
                $state = get_field('state', $listing->ID);
                $price = get_field('price', $listing->ID);
                
                $label = $address;
                if ($city && $state) {
                    $label .= ', ' . $city . ', ' . $state;
                }
                if ($price) {
                    $label .= ' - $' . number_format($price);
                }
                
                echo '<option value="' . esc_attr($listing->ID) . '">' . esc_html($label) . '</option>';
                echo '<!-- Debug: Listed property ID=' . $listing->ID . ' Title=' . esc_html($listing->post_title) . ' -->';
            }
        }
    }

    private function render_listing_selector(): void {
        $agent_id = $this->dashboard_manager->get_current_agent_id();
        
        $listings = get_posts([
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => 'listing_agent',
                    'value' => '"' . $agent_id . '"',
                    'compare' => 'LIKE'
                ]
            ],
            'orderby' => 'date',
            'order' => 'DESC'
        ]);

        if (empty($listings)) {
            echo '<div class="hpt-empty-state">';
            echo '<p>No listings found.</p>';
            echo '<a href="' . esc_url(admin_url('post-new.php?post_type=listing')) . '" class="hpt-button hpt-button--sm">Create First Listing</a>';
            echo '</div>';
            return;
        }

        echo '<div class="hpt-listing-selector-grid">';
        foreach ($listings as $listing) {
            $address = get_field('street_address', $listing->ID);
            $city = get_field('city', $listing->ID);
            $state = get_field('state', $listing->ID);
            $price = get_field('price', $listing->ID);
            $featured_image = get_field('featured_image', $listing->ID);
            
            echo '<div class="hpt-listing-selector-card" data-listing-id="' . $listing->ID . '">';
            
            if ($featured_image) {
                echo '<div class="hpt-listing-card__image">';
                echo '<img src="' . esc_url($featured_image['sizes']['medium']) . '" alt="' . esc_attr($listing->post_title) . '">';
                echo '</div>';
            }
            
            echo '<div class="hpt-listing-card__content">';
            echo '<div class="hpt-listing-card__title">' . esc_html($address ?: $listing->post_title) . '</div>';
            if ($city && $state) {
                echo '<div class="hpt-listing-card__location">' . esc_html($city . ', ' . $state) . '</div>';
            }
            if ($price) {
                echo '<div class="hpt-listing-card__price">$' . number_format($price) . '</div>';
            }
            echo '</div>';
            
            echo '</div>';
        }
        echo '</div>';
    }

    private function render_format_selector(): void {
        $formats = [
            'full_flyer' => ['name' => 'Full Flyer', 'size' => '8.5×11"', 'icon' => '📄'],
            'instagram_post' => ['name' => 'Instagram Post', 'size' => '1080×1080', 'icon' => '📱'],
            'instagram_story' => ['name' => 'Instagram Story', 'size' => '1080×1920', 'icon' => '📱'],
            'facebook_post' => ['name' => 'Facebook Post', 'size' => '1200×630', 'icon' => '🌐'],
            'twitter_post' => ['name' => 'Twitter Post', 'size' => '1024×512', 'icon' => '🐦'],
            'email_header' => ['name' => 'Email Header', 'size' => '600×200', 'icon' => '✉️']
        ];

        foreach ($formats as $key => $format) {
            echo '<div class="hpt-format-card" data-format="' . esc_attr($key) . '">';
            echo '<div class="hpt-format-card__icon">' . $format['icon'] . '</div>';
            echo '<div class="hpt-format-card__name">' . esc_html($format['name']) . '</div>';
            echo '<div class="hpt-format-card__size">' . esc_html($format['size']) . '</div>';
            echo '</div>';
        }
    }

    private function render_recent_activity(): void {
        // Mock data - in real implementation, this would come from database
        $activities = [
            ['text' => 'Generated Instagram post for 123 Oak Street', 'time' => '2 hours ago'],
            ['text' => 'Created flyer for Sunset Villa listing', 'time' => '1 day ago'],
            ['text' => 'Generated Facebook post for Mountain View property', 'time' => '2 days ago'],
            ['text' => 'Created email header for Highland Park listing', 'time' => '3 days ago'],
            ['text' => 'Generated Twitter post for Downtown condo', 'time' => '1 week ago']
        ];

        if (empty($activities)) {
            echo '<div class="hpt-empty-state">';
            echo '<p>No marketing activity yet.</p>';
            echo '</div>';
            return;
        }

        echo '<div class="hpt-activity-feed">';
        foreach ($activities as $activity) {
            echo '<div class="hpt-activity-item">';
            echo '<div class="hpt-activity-item__icon">';
            echo '<span class="dashicons dashicons-megaphone"></span>';
            echo '</div>';
            echo '<div class="hpt-activity-item__content">';
            echo '<div class="hpt-activity-item__text">' . esc_html($activity['text']) . '</div>';
            echo '<div class="hpt-activity-item__time">' . esc_html($activity['time']) . '</div>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    }

    private function render_campaigns_manager(): void {
        echo '<div class="hpt-campaigns-manager">';
        echo '<div class="hpt-section-header">';
        echo '<div class="hpt-section-header__left">';
        echo '<h2>Marketing Campaigns</h2>';
        echo '<p>Manage your marketing campaigns and track performance.</p>';
        echo '</div>';
        echo '<div class="hpt-section-header__right">';
        echo '<button class="hpt-button hpt-button--primary">Create Campaign</button>';
        echo '</div>';
        echo '</div>';
        
        // Campaigns would be displayed here
        echo '<div class="hpt-empty-state">';
        echo '<p>Campaign management coming soon!</p>';
        echo '</div>';
        
        echo '</div>';
    }

    private function render_templates_manager(): void {
        echo '<div class="hpt-templates-manager">';
        echo '<div class="hpt-section-header">';
        echo '<div class="hpt-section-header__left">';
        echo '<h2>Marketing Templates</h2>';
        echo '<p>Manage your custom marketing templates and branding.</p>';
        echo '</div>';
        echo '<div class="hpt-section-header__right">';
        echo '<button class="hpt-button hpt-button--primary">Upload Template</button>';
        echo '</div>';
        echo '</div>';
        
        // Templates would be displayed here
        echo '<div class="hpt-empty-state">';
        echo '<p>Template management coming soon!</p>';
        echo '</div>';
        
        echo '</div>';
    }

    private function render_generator_script(): void {
        echo '<script>';
        echo 'jQuery(document).ready(function($) {';
        
        // Initialize global variables for debugging
        echo 'console.log("=== MARKETING SCRIPT LOADING ===");';
        echo 'window.currentListing = null;';
        echo 'window.currentTemplate = "parker-group";';
        echo 'window.currentCampaignType = "for_sale";';
        echo 'window.fabricCanvas = null;';
        echo 'window.currentListingData = null;';
        
        // Local references for convenience
        echo 'var currentListing = window.currentListing;';
        echo 'var currentTemplate = window.currentTemplate;';
        echo 'var currentCampaignType = window.currentCampaignType;';
        echo 'var fabricCanvas = window.fabricCanvas;';
        echo 'var currentListingData = window.currentListingData;';
        
        // Load Fabric.js if not already loaded
        echo 'if (typeof fabric === "undefined") {';
        echo 'console.log("Loading Fabric.js...");';
        echo 'var script = document.createElement("script");';
        echo 'script.src = "https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js";';
        echo 'script.onload = function() { ';
        echo 'console.log("✅ Fabric.js loaded successfully");';
        echo 'initializeFabricCanvas();';
        echo '};';
        echo 'script.onerror = function() {';
        echo 'console.error("❌ Failed to load Fabric.js");';
        echo '};';
        echo 'document.head.appendChild(script);';
        echo '} else {';
        echo 'console.log("Fabric.js already loaded");';
        echo 'initializeFabricCanvas();';
        echo '}';
        
        // Initialize Fabric.js canvas with proper loading
        echo 'function initializeFabricCanvas() {';
        echo 'console.log("Initializing Fabric canvas...");';
        echo 'if (typeof fabric !== "undefined") {';
        echo 'console.log("Fabric.js is loaded, creating canvas");';
        echo 'try {';
        echo 'window.fabricCanvas = new fabric.Canvas("flyer-canvas", {';
        echo 'backgroundColor: "#ffffff",';
        echo 'width: 850,';
        echo 'height: 1100,';
        echo 'selection: false';
        echo '});';
        echo 'fabricCanvas = window.fabricCanvas;';
        echo 'console.log("✅ Fabric canvas created successfully:", window.fabricCanvas);';
        echo '} catch(e) {';
        echo 'console.error("❌ Error creating Fabric canvas:", e);';
        echo '}';
        echo '} else {';
        echo 'console.log("Fabric.js not loaded yet, waiting...");';
        echo 'setTimeout(initializeFabricCanvas, 100);';
        echo '}';
        echo '}';
        echo 'initializeFabricCanvas();';
        
        // Listing selection
        echo '$("#flyer-listing-select").on("change", function() {';
        echo 'var listingId = $(this).val();';
        echo 'console.log("Listing selected:", listingId);';
        echo 'window.currentListing = listingId;';
        echo '// currentListing = window.currentListing; // Already using window.currentListing';
        echo 'if (listingId) {';
        echo '$("#template-section").show();';
        echo '$("#options-section").show();';
        echo '$("#generate-flyer").prop("disabled", false);';
        echo '$(".hpt-canvas-placeholder").hide();';
        echo 'loadListingData(listingId);';
        echo '} else {';
        echo '$("#template-section").hide();';
        echo '$("#options-section").hide();';
        echo '$("#generate-flyer").prop("disabled", true);';
        echo '$(".hpt-canvas-placeholder").show();';
        echo '}';
        echo '});';

        // Template selection
        echo '$(document).on("click", ".hpt-template-option", function() {';
        echo '$(".hpt-template-option").removeClass("selected");';
        echo '$(this).addClass("selected");';
        echo 'window.currentTemplate = $(this).data("template");';
        echo '// currentTemplate = window.currentTemplate; // Already using window.currentTemplate';
        echo 'console.log("Template selected:", window.currentTemplate);';
        echo '});';

        // Campaign type selection
        echo '$(document).on("click", ".hpt-campaign-type", function() {';
        echo '$(".hpt-campaign-type").removeClass("selected");';
        echo '$(this).addClass("selected");';
        echo 'window.currentCampaignType = $(this).data("type");';
        echo '// currentCampaignType = window.currentCampaignType; // Already using window.currentCampaignType';
        echo 'console.log("Campaign type selected:", window.currentCampaignType);';
        echo '});';

        // Generate flyer
        echo '$("#generate-flyer").on("click", function() {';
        echo 'console.log("=== GENERATE FLYER CLICKED ===");';
        echo 'console.log("currentListing:", window.currentListing);';
        echo 'console.log("currentListingData:", window.currentListingData);';
        echo 'console.log("fabricCanvas:", window.fabricCanvas);';
        echo 'console.log("currentTemplate:", window.currentTemplate);';
        echo 'console.log("currentCampaignType:", window.currentCampaignType);';
        echo 'if (!window.currentListing) {';
        echo 'alert("Please select a listing first");';
        echo 'return;';
        echo '}';
        echo 'if (!window.currentListingData) {';
        echo 'alert("Listing data not loaded. Please select a different listing.");';
        echo 'console.error("No listing data available");';
        echo 'return;';
        echo '}';
        echo 'if (!window.fabricCanvas) {';
        echo 'alert("Canvas not ready. Please wait and try again.");';
        echo 'console.error("Fabric canvas not initialized");';
        echo 'return;';
        echo '}';
        echo 'console.log("All checks passed, generating flyer...");';
        echo 'generateFlyer();';
        echo '});';
        
        // Download buttons
        echo '$("#download-png").on("click", function() { downloadFlyer("png"); });';
        echo '$("#download-pdf").on("click", function() { downloadFlyer("pdf"); });';
        echo '$("#download-print").on("click", function() { downloadFlyer("print"); });';

        // Functions
        echo 'function loadListingData(listingId) {';
        echo '$.post(hptDashboard.ajaxUrl, {';
        echo 'action: "hpt_dashboard_action",';
        echo 'dashboard_action: "get_listing_data",';
        echo 'listing_id: listingId,';
        echo 'nonce: hptDashboard.nonce';
        echo '}, function(response) {';
        echo 'if (response.success) {';
        echo 'window.currentListingData = response.data;';
        echo '// currentListingData = window.currentListingData; // Already using window.currentListingData';
        echo '} else {';
        echo 'console.error("Failed to load listing data");';
        echo '}';
        echo '});';
        echo '}';

        echo 'function generateFlyer() {';
        echo 'if (!window.fabricCanvas) {';
        echo 'alert("Canvas not initialized");';
        echo 'return;';
        echo '}';
        echo 'showLoading(true);';
        echo 'window.fabricCanvas.clear();';
        echo 'createParkerGroupFlyer();';
        echo '$(".hpt-download-section").show();';
        echo 'showLoading(false);';
        echo '}';

        echo 'function createParkerGroupFlyer() {';
        echo 'if (!window.currentListingData) return;';
        echo 'var data = window.currentListingData;';
        
        // Background
        echo 'var background = new fabric.Rect({';
        echo 'left: 0, top: 0, width: 850, height: 1100,';
        echo 'fill: "#51bae0", selectable: false';
        echo '});';
        echo 'window.fabricCanvas.add(background);';
        
        // Header text based on campaign type
        echo 'var campaignTexts = {';
        echo '"for_sale": "FOR SALE",';
        echo '"just_listed": "JUST LISTED",';
        echo '"open_house": "OPEN HOUSE",';
        echo '"price_change": "PRICE REDUCED",';
        echo '"sold": "SOLD",';
        echo '"coming_soon": "COMING SOON"';
        echo '};';
        echo 'var headerText = new fabric.Text(campaignTexts[currentCampaignType], {';
        echo 'left: 55, top: 50, fontSize: 80,';
        echo 'fontFamily: "Poppins, sans-serif", fontWeight: "bold",';
        echo 'fill: "#ffffff", selectable: false';
        echo '});';
        echo 'window.fabricCanvas.add(headerText);';
        
        // Property stats section
        echo 'var statsSection = new fabric.Rect({';
        echo 'left: 0, top: 655, width: 850, height: 75,';
        echo 'fill: "#082f49", selectable: false';
        echo '});';
        echo 'window.fabricCanvas.add(statsSection);';
        
        // Price
        echo 'var priceText = data.price ? "$" + parseInt(data.price).toLocaleString() : "Price Upon Request";';
        echo 'var price = new fabric.Text(priceText, {';
        echo 'left: 750, top: 670, fontSize: 32,';
        echo 'fontFamily: "Poppins, sans-serif", fontWeight: "700",';
        echo 'fill: "#ffffff", originX: "right", selectable: false';
        echo '});';
        echo 'window.fabricCanvas.add(price);';
        
        // White section for details
        echo 'var whiteSection = new fabric.Rect({';
        echo 'left: 0, top: 730, width: 850, height: 370,';
        echo 'fill: "#f5f5f4", selectable: false';
        echo '});';
        echo 'window.fabricCanvas.add(whiteSection);';
        
        // Address
        echo 'var address = (data.street_address || "") + (data.city ? ", " + data.city : "");';
        echo 'var addressText = new fabric.Text(address, {';
        echo 'left: 55, top: 760, fontSize: 32,';
        echo 'fontFamily: "Poppins, sans-serif", fontWeight: "600",';
        echo 'fill: "#51bae0", selectable: false';
        echo '});';
        echo 'window.fabricCanvas.add(addressText);';
        
        echo 'window.fabricCanvas.renderAll();';
        echo '}';

        echo 'function downloadFlyer(format) {';
        echo 'if (!window.fabricCanvas) return;';
        echo 'var dataURL;';
        echo 'var multiplier = format === "print" ? 4 : 2;';
        echo 'dataURL = window.fabricCanvas.toDataURL({';
        echo 'format: format === "pdf" ? "png" : format,';
        echo 'quality: 1.0,';
        echo 'multiplier: multiplier';
        echo '});';
        echo 'if (format === "pdf") {';
        echo '// For PDF, send to server for processing';
        echo '$.post(hptDashboard.ajaxUrl, {';
        echo 'action: "hpt_dashboard_action",';
        echo 'dashboard_action: "generate_pdf",';
        echo 'canvas_data: dataURL,';
        echo 'nonce: hptDashboard.nonce';
        echo '}, function(response) {';
        echo 'if (response.success && response.data.pdf_url) {';
        echo 'var link = document.createElement("a");';
        echo 'link.href = response.data.pdf_url;';
        echo 'link.download = "flyer-" + Date.now() + ".pdf";';
        echo 'link.click();';
        echo '}';
        echo '});';
        echo '} else {';
        echo 'var link = document.createElement("a");';
        echo 'link.download = "flyer-" + Date.now() + "." + (format === "print" ? "png" : format);';
        echo 'link.href = dataURL;';
        echo 'link.click();';
        echo '}';
        echo '}';

        echo 'function showLoading(show) {';
        echo 'if (show) {';
        echo '$("#generate-flyer").prop("disabled", true).html("<span class=\'dashicons dashicons-update\'></span> Generating...");';
        echo '} else {';
        echo '$("#generate-flyer").prop("disabled", false).html("<span class=\'dashicons dashicons-admin-media\'></span> Generate Flyer");';
        echo '}';
        echo '}';

        echo '});';
        echo '</script>';
    }

    public function handle_ajax_generate_marketing($data): array {
        $listing_id = (int) ($data['listing_id'] ?? 0);
        $format = sanitize_text_field($data['format'] ?? '');
        
        if (!$listing_id || !$format) {
            return ['success' => false, 'message' => 'Missing required parameters'];
        }

        // Verify ownership
        $agent_id = $this->dashboard_manager->get_current_agent_id();
        $listing_agent = get_field('listing_agent', $listing_id);
        if (!$listing_agent || !in_array($agent_id, wp_list_pluck($listing_agent, 'ID'))) {
            return ['success' => false, 'message' => 'You do not have permission to create marketing for this listing'];
        }

        // Mock response - in real implementation, this would generate actual marketing materials
        return [
            'success' => true,
            'data' => [
                'download_url' => home_url('/downloads/marketing-' . $listing_id . '-' . $format . '.zip'),
                'message' => 'Marketing materials generated successfully'
            ]
        ];
    }

    public function handle_ajax_get_listing_data($data): array {
        $listing_id = (int) ($data['listing_id'] ?? 0);
        
        if (!$listing_id) {
            return ['success' => false, 'message' => 'Missing listing ID'];
        }

        // Get comprehensive listing data
        $listing = get_post($listing_id);
        if (!$listing || $listing->post_type !== 'listing') {
            return ['success' => false, 'message' => 'Listing not found - ID: ' . $listing_id];
        }
        
        // Debug: Log the listing found
        error_log('Flyer Generator: Loading listing data for ID ' . $listing_id . ' Title: ' . $listing->post_title);

        // Get all ACF fields
        $fields = get_fields($listing_id);
        
        // Get featured image
        $featured_image = null;
        $featured_image_id = get_post_thumbnail_id($listing_id);
        if ($featured_image_id) {
            $featured_image = [
                'id' => $featured_image_id,
                'url' => wp_get_attachment_url($featured_image_id),
                'sizes' => [
                    'full' => wp_get_attachment_image_src($featured_image_id, 'full'),
                    'large' => wp_get_attachment_image_src($featured_image_id, 'large'),
                    'medium' => wp_get_attachment_image_src($featured_image_id, 'medium'),
                ]
            ];
        }

        // Get agent data
        $agent_data = null;
        if (!empty($fields['listing_agent'])) {
            // Handle both object and ID formats
            if (is_array($fields['listing_agent']) && !empty($fields['listing_agent'])) {
                $agent_id = is_object($fields['listing_agent'][0]) ? $fields['listing_agent'][0]->ID : $fields['listing_agent'][0];
            } else {
                $agent_id = is_object($fields['listing_agent']) ? $fields['listing_agent']->ID : $fields['listing_agent'];
            }
            
            if ($agent_id) {
                $agent_post = get_post($agent_id);
                $agent_fields = get_fields($agent_id);
                
                if ($agent_post) {
                    $agent_data = [
                        'id' => $agent_id,
                        'first_name' => $agent_fields['first_name'] ?? '',
                        'last_name' => $agent_fields['last_name'] ?? '',
                        'display_name' => trim(($agent_fields['first_name'] ?? '') . ' ' . ($agent_fields['last_name'] ?? '')) ?: $agent_post->post_title,
                        'email' => $agent_fields['email'] ?? '',
                        'phone' => $agent_fields['phone'] ?? $agent_fields['mobile_phone'] ?? '',
                        'mobile_phone' => $agent_fields['mobile_phone'] ?? '',
                        'profile_photo' => $agent_fields['profile_photo'] ?? null,
                        'title' => $agent_fields['title'] ?? 'REALTOR®',
                    ];
                    
                    error_log('Flyer Generator: Agent data loaded - ' . $agent_data['display_name']);
                }
            }
        }
        
        // Fallback: Get current agent if no listing agent found
        if (!$agent_data) {
            $current_agent_id = $this->dashboard_manager->get_current_agent_id();
            if ($current_agent_id) {
                $agent_post = get_post($current_agent_id);
                $agent_fields = get_fields($current_agent_id);
                
                if ($agent_post) {
                    $agent_data = [
                        'id' => $current_agent_id,
                        'first_name' => $agent_fields['first_name'] ?? '',
                        'last_name' => $agent_fields['last_name'] ?? '',
                        'display_name' => trim(($agent_fields['first_name'] ?? '') . ' ' . ($agent_fields['last_name'] ?? '')) ?: $agent_post->post_title,
                        'email' => $agent_fields['email'] ?? '',
                        'phone' => $agent_fields['phone'] ?? $agent_fields['mobile_phone'] ?? '',
                        'mobile_phone' => $agent_fields['mobile_phone'] ?? '',
                        'profile_photo' => $agent_fields['profile_photo'] ?? null,
                        'title' => $agent_fields['title'] ?? 'REALTOR®',
                    ];
                    
                    error_log('Flyer Generator: Using current agent as fallback - ' . $agent_data['display_name']);
                }
            }
        }

        // Compile listing data
        $listing_data = [
            'id' => $listing_id,
            'title' => $listing->post_title,
            'permalink' => get_permalink($listing_id),
            'price' => $fields['price'] ?? null,
            'bedrooms' => $fields['bedrooms'] ?? null,
            'bathrooms' => $fields['bathrooms'] ?? null,
            'square_feet' => $fields['square_feet'] ?? null,
            'lot_size' => $fields['lot_size'] ?? null,
            'year_built' => $fields['year_built'] ?? null,
            'street_address' => $fields['street_address'] ?? null,
            'city' => $fields['city'] ?? null,
            'state' => $fields['state'] ?? null,
            'zip_code' => $fields['zip_code'] ?? null,
            'description' => $fields['description'] ?? $listing->post_content,
            'short_description' => $fields['short_description'] ?? wp_trim_words($listing->post_content, 50),
            'featured_image' => $featured_image,
            'agent' => $agent_data,
        ];

        return ['success' => true, 'data' => $listing_data];
    }

    public function handle_ajax_generate_pdf($data): array {
        $canvas_data = $data['canvas_data'] ?? '';
        
        if (!$canvas_data) {
            return ['success' => false, 'message' => 'Missing canvas data'];
        }

        // In a real implementation, this would:
        // 1. Decode the base64 image data
        // 2. Use a library like TCPDF, Dompdf, or similar to create PDF
        // 3. Save the PDF to uploads directory
        // 4. Return the PDF URL

        // For now, return mock PDF URL
        $upload_dir = wp_upload_dir();
        $pdf_filename = 'flyer-' . time() . '.pdf';
        $pdf_url = $upload_dir['baseurl'] . '/marketing/' . $pdf_filename;
        
        return [
            'success' => true,
            'data' => [
                'pdf_url' => $pdf_url,
                'message' => 'PDF generated successfully'
            ]
        ];
    }

    private function render_flyer_generator_simple_script(): void {
        echo '<script>';
        echo 'jQuery(document).ready(function($) {';
        echo 'console.log("Flyer generator direct script initialized");';
        echo 'console.log("hptDashboard:", window.hptDashboard);';
        
        // Initialize global variables for debugging
        echo 'window.currentListing = null;';
        echo 'window.currentTemplate = "parker-group";';
        echo 'window.currentCampaignType = "for_sale";';
        echo 'window.fabricCanvas = null;';
        echo 'window.currentListingData = null;';
        
        // Initialize Fabric.js canvas immediately
        echo 'if (typeof fabric !== "undefined") {';
        echo 'initializeFabricCanvas();';
        echo '} else {';
        echo 'var script = document.createElement("script");';
        echo 'script.src = "https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js";';
        echo 'script.onload = function() { console.log("Fabric.js loaded"); initializeFabricCanvas(); };';
        echo 'document.head.appendChild(script);';
        echo '}';
        
        // Initialize Fabric.js canvas
        echo 'function initializeFabricCanvas() {';
        echo 'if (typeof fabric !== "undefined" && !window.fabricCanvas) {';
        echo 'window.fabricCanvas = new fabric.Canvas("flyer-canvas", {';
        echo 'backgroundColor: "#ffffff",';
        echo 'width: 850,';
        echo 'height: 1100';
        echo '});';
        echo 'console.log("Fabric canvas initialized");';
        echo '}';
        echo '}';
        
        // Refresh listings button
        echo '$("#refresh-listings").on("click", function() {';
        echo 'var button = $(this);';
        echo 'var originalHtml = button.html();';
        echo 'button.html("<span class=\"dashicons dashicons-update\"></span>").prop("disabled", true);';
        echo 'location.reload();';
        echo '});';
        
        // Listing selection
        echo '$("#flyer-listing-select").on("change", function() {';
        echo 'var listingId = $(this).val();';
        echo 'console.log("Selected listing ID:", listingId);';
        echo 'currentListing = listingId;';
        echo 'if (listingId) {';
        echo '$("#template-section").show();';
        echo '$("#options-section").show();';
        echo '$("#generate-flyer").prop("disabled", false);';
        echo '$(".hpt-canvas-placeholder").hide();';
        echo 'loadListingData(listingId);';
        echo '} else {';
        echo '$("#template-section").hide();';
        echo '$("#options-section").hide();';
        echo '$("#generate-flyer").prop("disabled", true);';
        echo '$(".hpt-canvas-placeholder").show();';
        echo '}';
        echo '});';

        // Template selection
        echo '$(document).on("click", ".hpt-template-option", function() {';
        echo '$(".hpt-template-option").removeClass("selected");';
        echo '$(this).addClass("selected");';
        echo 'window.currentTemplate = $(this).data("template");';
        echo '// currentTemplate = window.currentTemplate; // Already using window.currentTemplate';
        echo 'console.log("Template selected:", window.currentTemplate);';
        echo '});';

        // Campaign type selection
        echo '$(document).on("click", ".hpt-campaign-type", function() {';
        echo '$(".hpt-campaign-type").removeClass("selected");';
        echo '$(this).addClass("selected");';
        echo 'window.currentCampaignType = $(this).data("type");';
        echo '// currentCampaignType = window.currentCampaignType; // Already using window.currentCampaignType';
        echo 'console.log("Campaign type selected:", window.currentCampaignType);';
        echo '});';

        // Generate flyer
        echo '$("#generate-flyer").on("click", function() {';
        echo 'console.log("=== GENERATE FLYER CLICKED ===");';
        echo 'console.log("currentListing:", window.currentListing);';
        echo 'console.log("currentListingData:", window.currentListingData);';
        echo 'console.log("fabricCanvas:", window.fabricCanvas);';
        echo 'console.log("currentTemplate:", window.currentTemplate);';
        echo 'console.log("currentCampaignType:", window.currentCampaignType);';
        echo 'if (!window.currentListing) {';
        echo 'alert("Please select a listing first");';
        echo 'return;';
        echo '}';
        echo 'if (!window.currentListingData) {';
        echo 'alert("Listing data not loaded. Please select a different listing.");';
        echo 'console.error("No listing data available");';
        echo 'return;';
        echo '}';
        echo 'if (!window.fabricCanvas) {';
        echo 'alert("Canvas not ready. Please wait and try again.");';
        echo 'console.error("Fabric canvas not initialized");';
        echo 'return;';
        echo '}';
        echo 'console.log("All checks passed, generating flyer...");';
        echo 'generateFlyer();';
        echo '});';
        
        // Download buttons
        echo '$("#download-png").on("click", function() { downloadFlyer("png"); });';
        echo '$("#download-pdf").on("click", function() { downloadFlyer("pdf"); });';
        echo '$("#download-print").on("click", function() { downloadFlyer("print"); });';

        // Functions
        echo 'function loadListingData(listingId) {';
        echo 'if (!window.hptDashboard) {';
        echo 'console.error("hptDashboard object not available");';
        echo 'return;';
        echo '}';
        echo 'console.log("Loading listing data for ID:", listingId);';
        echo '$.post(hptDashboard.ajaxUrl, {';
        echo 'action: "hpt_dashboard_action",';
        echo 'dashboard_action: "get_listing_data",';
        echo 'listing_id: listingId,';
        echo 'source: "marketing",';
        echo 'nonce: hptDashboard.nonce';
        echo '}, function(response) {';
        echo 'console.log("=== AJAX RESPONSE RECEIVED ===");';
        echo 'console.log("Response:", response);';
        echo 'if (response.success) {';
        echo 'window.currentListingData = response.data;';
        echo '// currentListingData = window.currentListingData; // Already using window.currentListingData';
        echo 'console.log("✅ Listing data loaded successfully:", window.currentListingData);';
        echo 'console.log("Agent data:", window.currentListingData.agent);';
        echo 'console.log("Price:", window.currentListingData.price);';
        echo 'console.log("Address:", window.currentListingData.full_address);';
        echo '} else {';
        echo 'console.error("❌ Failed to load listing data:", response);';
        echo 'if (response.data && response.data.message) {';
        echo 'console.error("Error message:", response.data.message);';
        echo '}';
        echo '}';
        echo '}).fail(function(xhr, status, error) {';
        echo 'console.error("❌ AJAX request failed:", status, error);';
        echo 'console.error("XHR:", xhr);';
        echo '});';
        echo '}';

        echo 'function generateFlyer() {';
        echo 'console.log("=== GENERATE FLYER FUNCTION CALLED ===");';
        echo 'console.log("Fabric canvas exists:", !!window.fabricCanvas);';
        echo 'console.log("Canvas size:", window.fabricCanvas ? window.fabricCanvas.width + "x" + window.fabricCanvas.height : "N/A");';
        echo 'console.log("Current listing data:", window.currentListingData);';
        echo 'if (!window.fabricCanvas) {';
        echo 'alert("Canvas not initialized. Please wait for Fabric.js to load.");';
        echo 'console.error("Fabric canvas is null or undefined");';
        echo 'return;';
        echo '}';
        echo 'console.log("Starting flyer generation...");';
        echo 'showLoading(true);';
        echo 'window.fabricCanvas.clear();';
        echo 'console.log("Canvas cleared, calling createParkerGroupFlyer()");';
        echo 'createParkerGroupFlyer();';
        echo '$(".hpt-download-section").show();';
        echo 'setTimeout(function() { showLoading(false); }, 1000);';
        echo 'console.log("Flyer generation process completed");';
        echo '}';

        echo 'function createParkerGroupFlyer() {';
        echo 'if (!window.currentListingData) return;';
        echo 'var data = window.currentListingData;';
        echo 'console.log("Creating flyer with data:", data);';
        
        // Background
        echo 'var background = new fabric.Rect({';
        echo 'left: 0, top: 0, width: 850, height: 1100,';
        echo 'fill: "#51bae0", selectable: false';
        echo '});';
        echo 'window.fabricCanvas.add(background);';
        
        // Header text based on campaign type
        echo 'var campaignTexts = {';
        echo '"for_sale": "FOR SALE",';
        echo '"just_listed": "JUST LISTED",';
        echo '"open_house": "OPEN HOUSE",';
        echo '"price_change": "PRICE REDUCED",';
        echo '"sold": "SOLD",';
        echo '"coming_soon": "COMING SOON"';
        echo '};';
        echo 'var headerText = new fabric.Text(campaignTexts[window.currentCampaignType] || "FOR SALE", {';
        echo 'left: 55, top: 50, fontSize: 80,';
        echo 'fontFamily: "Arial, sans-serif", fontWeight: "bold",';
        echo 'fill: "#ffffff", selectable: false';
        echo '});';
        echo 'window.fabricCanvas.add(headerText);';
        
        // Property stats section
        echo 'var statsSection = new fabric.Rect({';
        echo 'left: 0, top: 655, width: 850, height: 75,';
        echo 'fill: "#082f49", selectable: false';
        echo '});';
        echo 'window.fabricCanvas.add(statsSection);';
        
        // Property stats text
        echo 'var beds = data.bedrooms || "N/A";';
        echo 'var baths = data.bathrooms || "N/A";';
        echo 'var sqft = data.square_feet ? data.square_feet.toLocaleString() : "N/A";';
        
        echo 'var statsText = new fabric.Text(beds + " Bed • " + baths + " Bath • " + sqft + " Sq Ft", {';
        echo 'left: 75, top: 680, fontSize: 16,';
        echo 'fontFamily: "Arial, sans-serif", fontWeight: "500",';
        echo 'fill: "#ffffff", selectable: false';
        echo '});';
        echo 'window.fabricCanvas.add(statsText);';
        
        // Price
        echo 'var priceText = data.price ? "$" + parseInt(data.price).toLocaleString() : "Price Upon Request";';
        echo 'var price = new fabric.Text(priceText, {';
        echo 'left: 750, top: 670, fontSize: 32,';
        echo 'fontFamily: "Arial, sans-serif", fontWeight: "700",';
        echo 'fill: "#ffffff", originX: "right", selectable: false';
        echo '});';
        echo 'window.fabricCanvas.add(price);';
        
        // White section for details
        echo 'var whiteSection = new fabric.Rect({';
        echo 'left: 0, top: 730, width: 850, height: 370,';
        echo 'fill: "#f5f5f4", selectable: false';
        echo '});';
        echo 'window.fabricCanvas.add(whiteSection);';
        
        // Address
        echo 'var address = (data.street_address || "") + (data.city ? ", " + data.city : "");';
        echo 'if (!address.trim()) address = "Address Not Available";';
        echo 'var addressText = new fabric.Text(address, {';
        echo 'left: 55, top: 760, fontSize: 32,';
        echo 'fontFamily: "Arial, sans-serif", fontWeight: "600",';
        echo 'fill: "#51bae0", selectable: false';
        echo '});';
        echo 'window.fabricCanvas.add(addressText);';
        
        // Description
        echo 'var description = data.short_description || data.description || "Beautiful property available for viewing.";';
        echo 'if (description.length > 200) description = description.substring(0, 197) + "...";';
        echo 'var descText = new fabric.Text(description, {';
        echo 'left: 55, top: 820, fontSize: 14,';
        echo 'fontFamily: "Arial, sans-serif",';
        echo 'fill: "#333333", selectable: false,';
        echo 'width: 400';
        echo '});';
        echo 'window.fabricCanvas.add(descText);';
        
        // Agent info
        echo 'if (data.agent && data.agent.name) {';
        echo 'var agentText = new fabric.Text("Contact: " + data.agent.name, {';
        echo 'left: 55, top: 950, fontSize: 16,';
        echo 'fontFamily: "Arial, sans-serif", fontWeight: "600",';
        echo 'fill: "#51bae0", selectable: false';
        echo '});';
        echo 'window.fabricCanvas.add(agentText);';
        
        echo 'if (data.agent.phone) {';
        echo 'var phoneText = new fabric.Text("Phone: " + data.agent.phone, {';
        echo 'left: 55, top: 980, fontSize: 14,';
        echo 'fontFamily: "Arial, sans-serif",';
        echo 'fill: "#333333", selectable: false';
        echo '});';
        echo 'window.fabricCanvas.add(phoneText);';
        echo '}';
        
        echo 'if (data.agent.email) {';
        echo 'var emailText = new fabric.Text("Email: " + data.agent.email, {';
        echo 'left: 55, top: 1000, fontSize: 14,';
        echo 'fontFamily: "Arial, sans-serif",';
        echo 'fill: "#333333", selectable: false';
        echo '});';
        echo 'window.fabricCanvas.add(emailText);';
        echo '}';
        echo '}';
        
        // Company branding
        echo 'var companyText = new fabric.Text("THE PARKER GROUP", {';
        echo 'left: 680, top: 980, fontSize: 18,';
        echo 'fontFamily: "Arial, sans-serif", fontWeight: "700",';
        echo 'fill: "#51bae0", textAlign: "center",';
        echo 'originX: "center", selectable: false';
        echo '});';
        echo 'window.fabricCanvas.add(companyText);';
        
        echo 'var taglineText = new fabric.Text("find your happy place", {';
        echo 'left: 680, top: 1010, fontSize: 12,';
        echo 'fontFamily: "Arial, sans-serif",';
        echo 'fill: "#51bae0", textAlign: "center",';
        echo 'originX: "center", selectable: false';
        echo '});';
        echo 'window.fabricCanvas.add(taglineText);';
        
        echo 'window.fabricCanvas.renderAll();';
        echo '}';

        echo 'function downloadFlyer(format) {';
        echo 'if (!window.fabricCanvas) return;';
        echo 'var dataURL;';
        echo 'var multiplier = format === "print" ? 4 : 2;';
        echo 'dataURL = window.fabricCanvas.toDataURL({';
        echo 'format: format === "pdf" ? "png" : format,';
        echo 'quality: 1.0,';
        echo 'multiplier: multiplier';
        echo '});';
        
        echo 'if (format === "pdf") {';
        echo '$.post(hptDashboard.ajaxUrl, {';
        echo 'action: "hpt_dashboard_action",';
        echo 'dashboard_action: "generate_pdf",';
        echo 'canvas_data: dataURL,';
        echo 'nonce: hptDashboard.nonce';
        echo '}, function(response) {';
        echo 'if (response.success && response.data.pdf_url) {';
        echo 'var link = document.createElement("a");';
        echo 'link.href = response.data.pdf_url;';
        echo 'link.download = "flyer-" + Date.now() + ".pdf";';
        echo 'link.click();';
        echo '} else {';
        echo 'alert("PDF generation failed. Downloading as PNG instead.");';
        echo 'var link = document.createElement("a");';
        echo 'link.download = "flyer-" + Date.now() + ".png";';
        echo 'link.href = dataURL;';
        echo 'link.click();';
        echo '}';
        echo '});';
        echo '} else {';
        echo 'var link = document.createElement("a");';
        echo 'link.download = "flyer-" + Date.now() + "." + (format === "print" ? "png" : format);';
        echo 'link.href = dataURL;';
        echo 'link.click();';
        echo '}';
        echo '}';

        echo 'function showLoading(show) {';
        echo 'if (show) {';
        echo '$("#generate-flyer").prop("disabled", true).html("<span class=\'dashicons dashicons-update\'></span> Generating...");';
        echo '} else {';
        echo '$("#generate-flyer").prop("disabled", false).html("<span class=\'dashicons dashicons-admin-media\'></span> Generate Flyer");';
        echo '}';
        echo '}';

        echo '});';
        echo '</script>';
        
        // Add debug functions and test button
        echo '<script>';
        echo 'setTimeout(function() {';
        echo 'window.debugFlyer = function() {';
        echo 'console.log("=== FLYER DEBUG ===");';
        echo 'console.log("Fabric loaded:", typeof fabric !== "undefined");';
        echo 'console.log("Canvas initialized:", window.fabricCanvas !== null);';
        echo 'console.log("Current listing:", window.currentListing || "none");';
        echo 'console.log("Listing data:", window.currentListingData || "none");';
        echo 'if (window.fabricCanvas) {';
        echo 'console.log("Canvas size:", window.fabricCanvas.width, "x", window.fabricCanvas.height);';
        echo 'console.log("Canvas objects:", window.fabricCanvas.getObjects().length);';
        echo '}';
        echo '};';
        echo 'window.testBasicCanvas = function() {';
        echo 'console.log("Testing basic canvas rendering...");';
        echo 'if (!window.fabricCanvas) {';
        echo 'console.error("No canvas available");';
        echo 'return;';
        echo '}';
        echo 'window.fabricCanvas.clear();';
        echo 'var testRect = new fabric.Rect({';
        echo 'left: 50, top: 50, width: 200, height: 100,';
        echo 'fill: "red", selectable: false';
        echo '});';
        echo 'window.fabricCanvas.add(testRect);';
        echo 'var testText = new fabric.Text("TEST CANVAS", {';
        echo 'left: 100, top: 200, fontSize: 24,';
        echo 'fill: "blue", selectable: false';
        echo '});';
        echo 'window.fabricCanvas.add(testText);';
        echo 'window.fabricCanvas.renderAll();';
        echo 'console.log("Basic canvas test completed. You should see a red rectangle and blue text.");';
        echo '};';
        echo 'window.testCanvas = function() {';
        echo 'console.log("Testing canvas...");';
        echo 'if (!window.fabricCanvas) {';
        echo 'console.error("Canvas not available");';
        echo 'return;';
        echo '}';
        echo 'window.fabricCanvas.clear();';
        echo 'var rect = new fabric.Rect({';
        echo 'left: 50, top: 50, width: 100, height: 100,';
        echo 'fill: "red"';
        echo '});';
        echo 'window.fabricCanvas.add(rect);';
        echo 'window.fabricCanvas.renderAll();';
        echo 'console.log("Test rectangle should be visible");';
        echo '};';
        echo 'console.log("Debug functions ready: debugFlyer() and testCanvas()");';
        echo '}, 1000);';
        echo '</script>';
        
        // Add essential CSS
        echo '<style>';
        echo '.hpt-flyer-generator-workspace { display: flex; gap: 30px; margin-top: 30px; }';
        echo '.hpt-generator-controls { flex: 1; max-width: 400px; background: #f9f9f9; padding: 20px; border-radius: 8px; }';
        echo '.hpt-generator-preview { flex: 1; background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #ddd; }';
        echo '.hpt-control-section { margin-bottom: 30px; }';
        echo '.hpt-step-number { background: #51bae0; color: white; width: 24px; height: 24px; border-radius: 50%; display: inline-block; text-align: center; line-height: 24px; font-size: 14px; margin-right: 10px; }';
        echo '.hpt-template-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px; }';
        echo '.hpt-template-option { padding: 15px; border: 2px solid #ddd; border-radius: 8px; cursor: pointer; text-align: center; }';
        echo '.hpt-template-option.selected { border-color: #51bae0; background: #f0f9ff; }';
        echo '.hpt-campaign-types { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 15px; }';
        echo '.hpt-campaign-type { padding: 8px 16px; border: 1px solid #ddd; background: white; border-radius: 20px; cursor: pointer; }';
        echo '.hpt-campaign-type.selected { background: #51bae0; color: white; border-color: #51bae0; }';
        echo '.hpt-canvas-container { position: relative; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; }';
        echo '.hpt-canvas-placeholder { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; color: #666; }';
        echo '</style>';
    }
}