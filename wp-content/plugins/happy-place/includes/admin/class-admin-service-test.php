<?php
/**
 * Admin Service Test Page
 * 
 * Provides an admin interface to test service class loading and functionality
 * 
 * @package HappyPlace\Admin
 * @version 4.0.0
 */

namespace HappyPlace\Admin;

use HappyPlace\ServiceTest;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Service Test Class
 */
class AdminServiceTest {
    
    /**
     * Initialize
     */
    public function init(): void {
        add_action('admin_menu', [$this, 'add_menu_page']);
        add_action('wp_ajax_hp_run_service_tests', [$this, 'ajax_run_tests']);
    }
    
    /**
     * Add menu page
     */
    public function add_menu_page(): void {
        if (!HP_DEBUG) {
            return; // Only show in debug mode
        }
        
        add_submenu_page(
            'happy-place',
            'Service Tests',
            'Service Tests',
            'manage_options',
            'hp-service-tests',
            [$this, 'render_page']
        );
    }
    
    /**
     * Render test page
     */
    public function render_page(): void {
        ?>
        <div class="wrap">
            <h1>Happy Place Service Tests</h1>
            <p>Test namespace resolution and service class functionality.</p>
            
            <div id="test-results" style="margin: 20px 0;"></div>
            
            <button type="button" id="run-tests" class="button button-primary">Run Tests</button>
            <button type="button" id="show-class-info" class="button">Show Class Info</button>
            
            <div id="class-info" style="margin-top: 20px; display: none;">
                <h2>Class Information</h2>
                <pre id="class-info-content" style="background: #f0f0f0; padding: 15px; overflow-x: auto;"></pre>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#run-tests').on('click', function() {
                var button = $(this);
                button.prop('disabled', true).text('Running Tests...');
                
                $.post(ajaxurl, {
                    action: 'hp_run_service_tests',
                    test_type: 'run',
                    nonce: '<?php echo wp_create_nonce('hp_service_test'); ?>'
                })
                .done(function(response) {
                    if (response.success) {
                        var results = response.data;
                        var html = '<div class="notice notice-' + (results.failed > 0 ? 'error' : 'success') + '">';
                        html += '<p><strong>Test Results:</strong> ' + results.passed + ' passed, ' + results.failed + ' failed</p>';
                        
                        if (results.failed > 0) {
                            html += '<ul>';
                            for (var className in results.errors) {
                                html += '<li><strong>' + className + ':</strong> ' + results.errors[className] + '</li>';
                            }
                            html += '</ul>';
                        }
                        
                        html += '</div>';
                        $('#test-results').html(html);
                    } else {
                        $('#test-results').html('<div class="notice notice-error"><p>Test failed: ' + response.data + '</p></div>');
                    }
                })
                .fail(function() {
                    $('#test-results').html('<div class="notice notice-error"><p>AJAX request failed</p></div>');
                })
                .always(function() {
                    button.prop('disabled', false).text('Run Tests');
                });
            });
            
            $('#show-class-info').on('click', function() {
                var button = $(this);
                button.prop('disabled', true).text('Loading...');
                
                $.post(ajaxurl, {
                    action: 'hp_run_service_tests',
                    test_type: 'info',
                    nonce: '<?php echo wp_create_nonce('hp_service_test'); ?>'
                })
                .done(function(response) {
                    if (response.success) {
                        $('#class-info-content').text(JSON.stringify(response.data, null, 2));
                        $('#class-info').show();
                    }
                })
                .always(function() {
                    button.prop('disabled', false).text('Show Class Info');
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * AJAX handler for running tests
     */
    public function ajax_run_tests(): void {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'hp_service_test')) {
            wp_send_json_error('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $test_type = $_POST['test_type'] ?? 'run';
        
        try {
            if ($test_type === 'info') {
                $info = ServiceTest::get_class_info();
                wp_send_json_success($info);
            } else {
                $results = ServiceTest::run_tests();
                wp_send_json_success($results);
            }
        } catch (\Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }
}