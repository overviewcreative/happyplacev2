<?php
/**
 * Quick lead verification
 */

// Load WordPress
require_once('wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    wp_die('Access denied. Admin access required.');
}

global $wpdb;

$leads_table = $wpdb->prefix . 'hp_leads';

// Get recent leads
$leads = $wpdb->get_results("
    SELECT id, first_name, last_name, email, phone, source, listing_id, agent_id, status, created_at 
    FROM $leads_table 
    ORDER BY created_at DESC 
    LIMIT 10
");

echo "<h1>Recent Leads Check</h1>";

if (empty($leads)) {
    echo "<p>❌ No leads found in database</p>";
} else {
    echo "<p>✅ Found " . count($leads) . " recent leads:</p>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Source</th><th>Listing</th><th>Agent</th><th>Status</th><th>Created</th></tr>";
    
    foreach ($leads as $lead) {
        echo "<tr>";
        echo "<td>{$lead->id}</td>";
        echo "<td>{$lead->first_name} {$lead->last_name}</td>";
        echo "<td>{$lead->email}</td>";
        echo "<td>{$lead->phone}</td>";
        echo "<td>{$lead->source}</td>";
        echo "<td>{$lead->listing_id}</td>";
        echo "<td>{$lead->agent_id}</td>";
        echo "<td>{$lead->status}</td>";
        echo "<td>{$lead->created_at}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check for incomplete data
    $incomplete_leads = $wpdb->get_results("
        SELECT id, first_name, last_name, email, phone, message, source 
        FROM $leads_table 
        WHERE (first_name IS NULL OR first_name = '') 
           OR (email IS NULL OR email = '' OR email NOT LIKE '%@%')
           OR (message IS NULL OR message = '')
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    
    if (!empty($incomplete_leads)) {
        echo "<h2>❌ Incomplete Leads (Missing Required Data)</h2>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>First Name</th><th>Email</th><th>Message</th><th>Source</th></tr>";
        
        foreach ($incomplete_leads as $lead) {
            echo "<tr>";
            echo "<td>{$lead->id}</td>";
            echo "<td>" . ($lead->first_name ?: '<em>MISSING</em>') . "</td>";
            echo "<td>" . ($lead->email && strpos($lead->email, '@') ? $lead->email : '<em>INVALID</em>') . "</td>";
            echo "<td>" . ($lead->message ? substr($lead->message, 0, 50) . '...' : '<em>MISSING</em>') . "</td>";
            echo "<td>{$lead->source}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

// Check plugin admin page link
echo "<hr>";
echo "<h2>Plugin Admin Access</h2>";
echo "<p><a href='/wp-admin/admin.php?page=happy-place-leads' target='_blank'>Open Leads Admin Page</a></p>";
echo "<p><a href='/wp-admin/admin.php?page=happy-place' target='_blank'>Open Happy Place Plugin Main Page</a></p>";
echo "<p><a href='/wp-admin/' target='_blank'>WordPress Admin Dashboard</a></p>";

// Test if the LeadService is available
echo "<h2>Lead Service Test</h2>";
if (class_exists('\HappyPlace\Services\LeadService')) {
    echo "<p>✅ LeadService class is available</p>";
    try {
        $lead_service = new \HappyPlace\Services\LeadService();
        echo "<p>✅ LeadService can be instantiated</p>";
    } catch (Exception $e) {
        echo "<p>❌ LeadService instantiation failed: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>❌ LeadService class not found</p>";
}
?>
