<?php
/**
 * Asset Loading Testing Helper
 * 
 * Comprehensive testing functions to validate conditional asset loading
 * 
 * @package HappyPlaceTheme
 * @since 3.2.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class for testing asset loading functionality
 */
class HPH_Asset_Testing {
    
    /**
     * Test asset loading on different page types
     */
    public static function run_comprehensive_test() {
        if (!current_user_can('manage_options')) {
            return false;
        }
        
        $results = [];
        
        // Test each page type
        $page_types = [
            'homepage' => home_url('/'),
            'single-listing' => self::get_sample_listing_url(),
            'listing-archive' => get_post_type_archive_link('listing'),
            'agent-archive' => get_post_type_archive_link('agent'),
            'search' => home_url('/?s=test'),
            'dashboard' => home_url('/dashboard/') // Adjust if different
        ];
        
        foreach ($page_types as $type => $url) {
            $results[$type] = self::test_page_assets($url, $type);
        }
        
        return $results;
    }
    
    /**
     * Test assets for a specific page
     */
    private static function test_page_assets($url, $page_type) {
        // Simulate visiting the page
        $test_result = [
            'url' => $url,
            'type' => $page_type,
            'expected_groups' => self::get_expected_groups($page_type),
            'critical_assets' => [],
            'missing_assets' => [],
            'unexpected_assets' => [],
            'performance' => []
        ];
        
        // Check if required asset files exist
        $expected_groups = $test_result['expected_groups'];
        $theme = HPH_Theme::instance();
        $assets_service = $theme->get_service('assets');
        
        if ($assets_service) {
            $registry = self::get_asset_registry();
            
            foreach ($expected_groups as $group) {
                if (isset($registry[$group])) {
                    $group_assets = $registry[$group];
                    
                    // Check CSS files
                    if (isset($group_assets['css'])) {
                        foreach ($group_assets['css'] as $handle => $path) {
                            $full_path = HPH_THEME_DIR . '/assets/css/' . $path;
                            if (!file_exists($full_path)) {
                                $test_result['missing_assets'][] = [
                                    'type' => 'css',
                                    'group' => $group,
                                    'handle' => $handle,
                                    'path' => $path,
                                    'expected' => $full_path
                                ];
                            }
                        }
                    }
                    
                    // Check JS files
                    if (isset($group_assets['js'])) {
                        foreach ($group_assets['js'] as $handle => $path) {
                            if (strpos($path, 'http') === 0) continue; // Skip external
                            
                            $full_path = HPH_THEME_DIR . '/assets/js/' . $path;
                            if (!file_exists($full_path)) {
                                $test_result['missing_assets'][] = [
                                    'type' => 'js',
                                    'group' => $group,
                                    'handle' => $handle,
                                    'path' => $path,
                                    'expected' => $full_path
                                ];
                            }
                        }
                    }
                }
            }
        }
        
        return $test_result;
    }
    
    /**
     * Get expected asset groups for page type
     */
    private static function get_expected_groups($page_type) {
        $always_loaded = ['core', 'sitewide', 'base-ui'];
        
        $page_specific = [
            'homepage' => ['listings'],
            'single-listing' => ['single-listing', 'maps', 'forms'],
            'listing-archive' => ['archive'],
            'agent-archive' => ['agents'],
            'search' => ['archive', 'forms'],
            'dashboard' => ['dashboard', 'charts', 'forms']
        ];
        
        return array_merge($always_loaded, $page_specific[$page_type] ?? []);
    }
    
    /**
     * Get a sample listing URL for testing
     */
    private static function get_sample_listing_url() {
        $listings = get_posts([
            'post_type' => 'listing',
            'posts_per_page' => 1,
            'post_status' => 'publish'
        ]);
        
        if (!empty($listings)) {
            return get_permalink($listings[0]->ID);
        }
        
        return get_post_type_archive_link('listing');
    }
    
    /**
     * Get asset registry (simplified version for testing)
     */
    private static function get_asset_registry() {
        return [
            'core' => [
                'css' => [
                    'variables' => 'framework/core/variables.css',
                    'reset' => 'framework/core/reset.css',
                    'typography' => 'framework/base/typography.css',
                    'layout' => 'framework/layout/grid.css',
                    'containers' => 'framework/layout/containers.css'
                ],
                'js' => [
                    'framework-core' => 'base/framework-core.js',
                    'theme' => 'utilities/theme.js'
                ]
            ],
            'sitewide' => [
                'css' => [
                    'header' => 'framework/layout/header.css',
                    'footer' => 'framework/layout/footer.css',
                    'navigation' => 'framework/components/organisms/navigation.css',
                    'cookie-consent' => 'framework/components/molecules/cookie-consent.css',
                    'search-bar' => 'framework/components/molecules/search-bar.css'
                ],
                'js' => [
                    'navigation' => 'layout/navigation.js',
                    'mobile-menu' => 'components/mobile-menu.js',
                    'cookie-consent' => 'components/cookie-consent.js',
                    'property-search' => 'components/property-search.js'
                ]
            ]
            // Add other groups as needed
        ];
    }
    
    /**
     * Generate test report
     */
    public static function generate_test_report() {
        if (!current_user_can('manage_options')) {
            return 'Unauthorized';
        }
        
        $results = self::run_comprehensive_test();
        
        ob_start();
        ?>
        <div class="hph-test-report" style="background: white; padding: 20px; margin: 20px; border: 1px solid #ccc; font-family: monospace;">
            <h2>HPH Asset Loading Test Report</h2>
            <p>Generated: <?php echo date('Y-m-d H:i:s'); ?></p>
            
            <?php foreach ($results as $page_type => $result): ?>
                <div class="test-result" style="margin-bottom: 30px; padding: 15px; border-left: 4px solid #007cba;">
                    <h3><?php echo ucfirst($page_type); ?> Page</h3>
                    <p><strong>URL:</strong> <?php echo esc_html($result['url']); ?></p>
                    <p><strong>Expected Groups:</strong> <?php echo implode(', ', $result['expected_groups']); ?></p>
                    
                    <?php if (!empty($result['missing_assets'])): ?>
                        <div style="background: #ffebee; padding: 10px; margin: 10px 0;">
                            <h4 style="color: #c62828;">Missing Assets (<?php echo count($result['missing_assets']); ?>)</h4>
                            <ul>
                                <?php foreach ($result['missing_assets'] as $missing): ?>
                                    <li style="color: #d32f2f;">
                                        <strong><?php echo $missing['group']; ?></strong> - 
                                        <?php echo $missing['type']; ?>: <?php echo $missing['handle']; ?> 
                                        (<?php echo $missing['path']; ?>)
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <div style="background: #e8f5e8; padding: 10px; margin: 10px 0;">
                            <p style="color: #2e7d32;">✅ All expected assets found</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            
            <div class="summary" style="margin-top: 30px; padding: 15px; background: #f5f5f5;">
                <h3>Summary</h3>
                <?php
                $total_missing = 0;
                foreach ($results as $result) {
                    $total_missing += count($result['missing_assets']);
                }
                ?>
                <p><strong>Total Missing Assets:</strong> <?php echo $total_missing; ?></p>
                <p><strong>Test Status:</strong> 
                    <span style="color: <?php echo $total_missing > 0 ? '#d32f2f' : '#2e7d32'; ?>;">
                        <?php echo $total_missing > 0 ? 'ISSUES FOUND' : 'ALL TESTS PASSED'; ?>
                    </span>
                </p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

/**
 * AJAX handler for running tests
 */
add_action('wp_ajax_hph_test_assets', function() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    echo HPH_Asset_Testing::generate_test_report();
    wp_die();
});

/**
 * Add admin menu item for testing
 */
add_action('admin_menu', function() {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        add_theme_page(
            'Asset Testing',
            'Asset Testing',
            'manage_options',
            'hph-asset-testing',
            function() {
                ?>
                <div class="wrap">
                    <h1>HPH Asset Loading Test</h1>
                    <p>This tool tests whether all required asset files exist for each page type.</p>
                    
                    <button id="run-asset-test" class="button button-primary">Run Asset Test</button>
                    
                    <div id="test-results"></div>
                    
                    <script>
                    document.getElementById('run-asset-test').addEventListener('click', function() {
                        this.disabled = true;
                        this.textContent = 'Running Test...';
                        
                        fetch(ajaxurl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'action=hph_test_assets'
                        })
                        .then(response => response.text())
                        .then(html => {
                            document.getElementById('test-results').innerHTML = html;
                            this.disabled = false;
                            this.textContent = 'Run Asset Test';
                        })
                        .catch(error => {
                            console.error('Test failed:', error);
                            this.disabled = false;
                            this.textContent = 'Run Asset Test';
                        });
                    });
                    </script>
                </div>
                <?php
            }
        );
    }
});

/**
 * Frontend testing shortcode (for logged-in admins)
 */
add_shortcode('hph_asset_test', function($atts) {
    if (!current_user_can('manage_options')) {
        return 'Unauthorized';
    }
    
    return HPH_Asset_Testing::generate_test_report();
});