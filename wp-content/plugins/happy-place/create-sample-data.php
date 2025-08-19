<?php
/**
 * Sample Data Creation Test Script
 * 
 * This script can be used to generate sample data for testing.
 * Access this file directly in a browser to create test data.
 */

// Load WordPress
$wp_load_path = '../../../wp-load.php';
if (file_exists($wp_load_path)) {
    require_once $wp_load_path;
} else {
    die('WordPress not found. Please check the path.');
}

// Security check - only allow in development
if (!defined('WP_DEBUG') || !WP_DEBUG) {
    die('Sample data creation is only allowed in debug mode.');
}

// Only allow admin users
if (!current_user_can('manage_options')) {
    wp_die('You do not have permission to access this page.');
}

// Load the sample data generator
$sample_data_file = __DIR__ . '/includes/utilities/class-sample-data-generator.php';
if (!file_exists($sample_data_file)) {
    die('Sample data generator not found.');
}

require_once $sample_data_file;

$generator = \HappyPlace\Utilities\Sample_Data_Generator::get_instance();
$action = $_GET['action'] ?? 'show_form';
$results = null;

// Handle actions
if ($action === 'generate') {
    $force = isset($_GET['force']) && $_GET['force'] === '1';
    $results = $generator->generate_all_sample_data($force);
} elseif ($action === 'cleanup') {
    $deleted_count = $generator->cleanup_sample_data();
    $results = ['cleanup' => true, 'deleted' => $deleted_count];
} elseif ($action === 'stats') {
    $results = $generator->get_sample_data_stats();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Happy Place - Sample Data Generator</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: #f1f1f1;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #0073aa;
            padding-bottom: 10px;
        }
        h2 {
            color: #555;
            margin-top: 30px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background: #0073aa;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 5px;
        }
        .button:hover {
            background: #005a87;
        }
        .button.secondary {
            background: #666;
        }
        .button.danger {
            background: #dc3232;
        }
        .results {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .success {
            background: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .stats-table th,
        .stats-table td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .stats-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Happy Place Plugin - Sample Data Generator</h1>
        
        <p>This tool allows you to create sample data for testing the Happy Place plugin functionality.</p>
        
        <?php if ($results): ?>
            <div class="results <?php echo isset($results['errors']) && !empty($results['errors']) ? 'error' : 'success'; ?>">
                <?php if ($action === 'generate'): ?>
                    <h3>Sample Data Generation Results</h3>
                    <?php if (isset($results['message'])): ?>
                        <p><?php echo esc_html($results['message']); ?></p>
                    <?php else: ?>
                        <ul>
                            <li>Agents created: <?php echo (int) $results['agents']; ?></li>
                            <li>Listings created: <?php echo (int) $results['listings']; ?></li>
                            <li>Communities created: <?php echo (int) $results['communities']; ?></li>
                            <li>Leads created: <?php echo (int) $results['leads']; ?></li>
                        </ul>
                    <?php endif; ?>
                    
                    <?php if (!empty($results['errors'])): ?>
                        <h4>Errors:</h4>
                        <ul>
                            <?php foreach ($results['errors'] as $error): ?>
                                <li><?php echo esc_html($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    
                <?php elseif ($action === 'cleanup'): ?>
                    <h3>Cleanup Results</h3>
                    <p>Deleted <?php echo (int) $results['deleted']; ?> sample data posts.</p>
                    
                <?php elseif ($action === 'stats'): ?>
                    <h3>Current Sample Data Statistics</h3>
                    <table class="stats-table">
                        <thead>
                            <tr>
                                <th>Post Type</th>
                                <th>Sample Data Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $post_type => $count): ?>
                                <tr>
                                    <td><?php echo esc_html(ucfirst($post_type)); ?></td>
                                    <td><?php echo (int) $count; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <h2>Actions</h2>
        
        <div>
            <a href="?action=generate" class="button">Generate Sample Data</a>
            <a href="?action=generate&force=1" class="button secondary">Force Regenerate</a>
            <a href="?action=stats" class="button">Show Statistics</a>
            <a href="?action=cleanup" class="button danger" onclick="return confirm('Are you sure you want to delete all sample data?')">Cleanup Sample Data</a>
        </div>
        
        <h2>What Gets Created</h2>
        
        <h3>Sample Agents (3)</h3>
        <ul>
            <li>Sarah Johnson - Senior Real Estate Agent</li>
            <li>Michael Chen - Listing Specialist</li>
            <li>Jessica Williams - Buyer's Agent</li>
        </ul>
        
        <h3>Sample Listings (3)</h3>
        <ul>
            <li>Modern Downtown Condo with City Views ($450,000)</li>
            <li>Luxury Family Home in Westlake ($1,250,000)</li>
            <li>Charming Bungalow in East Austin ($650,000)</li>
        </ul>
        
        <h3>Sample Communities (2)</h3>
        <ul>
            <li>Westlake Hills - Master planned community</li>
            <li>The Domain - Urban district</li>
        </ul>
        
        <h3>Sample Leads (2)</h3>
        <ul>
            <li>John Smith - Looking for 3-bedroom home</li>
            <li>Emily Davis - First-time luxury condo buyer</li>
        </ul>
        
        <h2>Notes</h2>
        <ul>
            <li>All sample data is marked with <code>_sample_data = 1</code> meta field for easy identification</li>
            <li>Listings are randomly assigned to agents</li>
            <li>Leads are randomly assigned to agents and may be linked to properties</li>
            <li>Use "Force Regenerate" to replace existing sample data</li>
            <li>Sample data can be safely deleted without affecting real data</li>
        </ul>
        
        <p><a href="<?php echo admin_url(); ?>">← Back to WordPress Admin</a></p>
    </div>
</body>
</html>