<?php
/**
 * Happy Place Plugin Activation Helper
 * 
 * Run this script to ensure the plugin is properly activated and post types are registered.
 * Access this file directly in your browser at: /wp-content/plugins/happy-place/activate-helper.php
 */

// Load WordPress
require_once( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php' );

// Check if user is admin
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'You must be logged in as an administrator to run this script.' );
}

echo '<h1>Happy Place Plugin Activation Helper</h1>';
echo '<style>
    body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    code { background: #f4f4f4; padding: 2px 5px; border-radius: 3px; }
</style>';

// 1. Check plugin status
echo '<div class="section">';
echo '<h2>1. Plugin Status</h2>';

$plugin_file = 'happy-place/happy-place.php';
$is_active = is_plugin_active( $plugin_file );

if ( $is_active ) {
    echo '<p class="success">✓ Plugin is ACTIVE</p>';
} else {
    echo '<p class="error">✗ Plugin is NOT ACTIVE</p>';
    
    // Try to activate it
    echo '<p>Attempting to activate plugin...</p>';
    $result = activate_plugin( $plugin_file );
    
    if ( is_wp_error( $result ) ) {
        echo '<p class="error">Failed to activate: ' . $result->get_error_message() . '</p>';
    } else {
        echo '<p class="success">✓ Plugin activated successfully!</p>';
        $is_active = true;
    }
}
echo '</div>';

// 2. Check dependencies
echo '<div class="section">';
echo '<h2>2. Dependencies Check</h2>';

// Check ACF
if ( class_exists( 'ACF' ) ) {
    echo '<p class="success">✓ Advanced Custom Fields is installed</p>';
    if ( function_exists( 'acf_pro' ) ) {
        echo '<p class="success">✓ ACF PRO is active</p>';
    } else {
        echo '<p class="error">✗ ACF PRO is not detected (using free version)</p>';
    }
} else {
    echo '<p class="error">✗ Advanced Custom Fields is NOT installed</p>';
}

// Check WordPress version
if ( version_compare( get_bloginfo( 'version' ), '6.0', '>=' ) ) {
    echo '<p class="success">✓ WordPress version ' . get_bloginfo( 'version' ) . ' meets requirements</p>';
} else {
    echo '<p class="error">✗ WordPress version ' . get_bloginfo( 'version' ) . ' is too old (requires 6.0+)</p>';
}

// Check PHP version
if ( version_compare( PHP_VERSION, '8.0', '>=' ) ) {
    echo '<p class="success">✓ PHP version ' . PHP_VERSION . ' meets requirements</p>';
} else {
    echo '<p class="error">✗ PHP version ' . PHP_VERSION . ' is too old (requires 8.0+)</p>';
}
echo '</div>';

// 3. Check post types registration
echo '<div class="section">';
echo '<h2>3. Custom Post Types Status</h2>';

if ( $is_active ) {
    // Force plugin initialization if needed
    if ( ! did_action( 'hp_init' ) ) {
        echo '<p class="info">Forcing plugin initialization...</p>';
        
        // Load the plugin class directly
        if ( file_exists( dirname( __FILE__ ) . '/includes/class-plugin.php' ) ) {
            require_once dirname( __FILE__ ) . '/includes/class-plugin.php';
            if ( class_exists( 'HappyPlace\Plugin' ) ) {
                $plugin_instance = HappyPlace\Plugin::get_instance();
                $plugin_instance->init();
                echo '<p class="success">✓ Plugin initialized</p>';
            }
        }
    }
    
    $expected_post_types = [
        'listing' => 'Listings',
        'agent' => 'Agents', 
        'community' => 'Communities',
        'city' => 'Cities',
        'open_house' => 'Open Houses',
        'local_place' => 'Local Places',
        'team' => 'Team Members',
        'transaction' => 'Transactions'
    ];
    
    foreach ( $expected_post_types as $post_type => $label ) {
        if ( post_type_exists( $post_type ) ) {
            $count = wp_count_posts( $post_type );
            $total = isset( $count->publish ) ? $count->publish : 0;
            echo '<p class="success">✓ <code>' . $post_type . '</code> (' . $label . ') is registered - ' . $total . ' published</p>';
        } else {
            echo '<p class="error">✗ <code>' . $post_type . '</code> (' . $label . ') is NOT registered</p>';
        }
    }
}
echo '</div>';

// 4. Flush rewrite rules
echo '<div class="section">';
echo '<h2>4. Rewrite Rules</h2>';
echo '<p>Flushing rewrite rules to ensure permalinks work correctly...</p>';
flush_rewrite_rules();
echo '<p class="success">✓ Rewrite rules flushed</p>';
echo '</div>';

// 5. Database check
echo '<div class="section">';
echo '<h2>5. Database Status</h2>';

// Check for plugin options
$version = get_option( 'hp_version' );
if ( $version ) {
    echo '<p class="success">✓ Plugin version in database: ' . $version . '</p>';
} else {
    echo '<p class="info">Plugin version not set in database</p>';
    update_option( 'hp_version', '3.0.0' );
    echo '<p class="success">✓ Set plugin version to 3.0.0</p>';
}

// Set flush flag
update_option( 'hp_flush_rewrite_rules', true );
echo '<p class="success">✓ Set flag to flush rewrite rules on next page load</p>';
echo '</div>';

// 6. Links to admin
echo '<div class="section">';
echo '<h2>6. Quick Links</h2>';
echo '<p><a href="' . admin_url( 'edit.php?post_type=listing' ) . '" target="_blank">→ View Listings</a></p>';
echo '<p><a href="' . admin_url( 'edit.php?post_type=agent' ) . '" target="_blank">→ View Agents</a></p>';
echo '<p><a href="' . admin_url( 'edit.php?post_type=community' ) . '" target="_blank">→ View Communities</a></p>';
echo '<p><a href="' . admin_url( 'plugins.php' ) . '" target="_blank">→ Plugins Page</a></p>';
echo '<p><a href="' . admin_url() . '" target="_blank">→ WP Admin Dashboard</a></p>';
echo '</div>';

// 7. Debug information
echo '<div class="section">';
echo '<h2>7. Debug Information</h2>';
echo '<p><strong>Plugin Directory:</strong> <code>' . dirname( __FILE__ ) . '</code></p>';
echo '<p><strong>WordPress Directory:</strong> <code>' . ABSPATH . '</code></p>';
echo '<p><strong>Active Theme:</strong> ' . get_template() . '</p>';
echo '<p><strong>Site URL:</strong> ' . site_url() . '</p>';

// Check if debug is on
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    echo '<p class="success">✓ WP_DEBUG is enabled</p>';
    
    // Check error log
    $log_file = WP_CONTENT_DIR . '/debug.log';
    if ( file_exists( $log_file ) ) {
        $log_size = filesize( $log_file );
        echo '<p class="info">Debug log exists (' . number_format( $log_size / 1024, 2 ) . ' KB)</p>';
        
        // Show last few lines of log related to HP
        $log_content = file_get_contents( $log_file );
        $lines = explode( "\n", $log_content );
        $hp_lines = array_filter( $lines, function( $line ) {
            return stripos( $line, 'HP' ) !== false || stripos( $line, 'happy' ) !== false;
        });
        
        if ( ! empty( $hp_lines ) ) {
            echo '<h3>Recent Happy Place Log Entries:</h3>';
            echo '<pre style="background: #f4f4f4; padding: 10px; overflow-x: auto; max-height: 300px;">';
            echo htmlspecialchars( implode( "\n", array_slice( $hp_lines, -10 ) ) );
            echo '</pre>';
        }
    }
} else {
    echo '<p class="info">WP_DEBUG is disabled</p>';
}
echo '</div>';

echo '<div class="section">';
echo '<h2>✓ Activation Helper Complete</h2>';
echo '<p>If custom post types are still not showing:</p>';
echo '<ol>';
echo '<li>Make sure you are logged in as an administrator</li>';
echo '<li>Try deactivating and reactivating the plugin</li>';
echo '<li>Check the debug log for errors</li>';
echo '<li>Clear any caching plugins</li>';
echo '</ol>';
echo '</div>';