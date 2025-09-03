<?php
/**
 * Run FollowUp Boss Integration Migration
 * 
 * This script executes the FollowUp Boss migration to set up
 * the database tables and migrate existing settings.
 */

// Load WordPress
require_once dirname(__FILE__) . '/wp-load.php';

// Ensure we're in admin context
if (!is_admin()) {
    define('WP_ADMIN', true);
}

// Load the migration class
require_once WP_CONTENT_DIR . '/plugins/happy-place/includes/migrations/class-followup-boss-migration.php';

use HappyPlace\Migrations\FollowUp_Boss_Migration;

echo "Starting FollowUp Boss Integration Migration...\n";
echo "==========================================\n\n";

try {
    $migration = new FollowUp_Boss_Migration();
    
    echo "Migration: " . $migration->get_name() . "\n";
    echo "Version: " . $migration->get_version() . "\n\n";
    
    // Run the migration
    $migration->up();
    
    echo "\n✅ Migration completed successfully!\n";
    echo "==========================================\n";
    echo "FollowUp Boss integration is now ready to use.\n";
    echo "Please configure your API key in the admin settings.\n\n";
    
} catch (Exception $e) {
    echo "\n❌ Migration failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "==========================================\n";
    exit(1);
}
