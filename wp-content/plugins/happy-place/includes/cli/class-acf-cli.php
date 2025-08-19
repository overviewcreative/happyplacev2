<?php
/**
 * WP-CLI commands for ACF field group management
 *
 * @package HappyPlace
 */

namespace HappyPlace\CLI;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Manage ACF field groups for Happy Place plugin
 */
class ACF_CLI {

    /**
     * Sync all field groups from JSON files to database
     *
     * ## EXAMPLES
     *
     *     wp happy-place acf sync
     *
     * @when after_wp_load
     */
    public function sync($args, $assoc_args) {
        if (!class_exists('ACF')) {
            \WP_CLI::error('ACF is not installed or activated.');
        }

        $acf_manager = \HappyPlace\Core\ACF_Manager::get_instance();
        
        \WP_CLI::line('Starting field group sync...');
        
        $json_groups = $acf_manager->get_field_groups();
        $synced = 0;
        $errors = [];

        foreach ($json_groups as $key => $group_data) {
            try {
                if (function_exists('acf_import_field_group')) {
                    acf_import_field_group($group_data);
                    \WP_CLI::line("Synced: {$group_data['title']}");
                    $synced++;
                } else {
                    \WP_CLI::warning("ACF import function not available for: {$group_data['title']}");
                }
            } catch (Exception $e) {
                $errors[] = "Failed to sync {$group_data['title']}: " . $e->getMessage();
                \WP_CLI::warning("Failed to sync: {$group_data['title']} - " . $e->getMessage());
            }
        }

        if (empty($errors)) {
            \WP_CLI::success("Successfully synced {$synced} field groups.");
        } else {
            \WP_CLI::warning("Synced {$synced} field groups with " . count($errors) . " errors.");
            foreach ($errors as $error) {
                \WP_CLI::line("Error: {$error}");
            }
        }
    }

    /**
     * Clean up orphaned field groups (in database but no JSON file)
     *
     * ## OPTIONS
     *
     * [--dry-run]
     * : Show what would be removed without actually removing anything
     *
     * ## EXAMPLES
     *
     *     wp happy-place acf cleanup
     *     wp happy-place acf cleanup --dry-run
     *
     * @when after_wp_load
     */
    public function cleanup($args, $assoc_args) {
        if (!class_exists('ACF')) {
            \WP_CLI::error('ACF is not installed or activated.');
        }

        $dry_run = isset($assoc_args['dry-run']);
        $acf_manager = \HappyPlace\Core\ACF_Manager::get_instance();
        
        \WP_CLI::line($dry_run ? 'Dry run - showing what would be removed:' : 'Starting cleanup of orphaned field groups...');
        
        $results = $acf_manager->cleanup_orphaned_field_groups($dry_run);

        if ($results['found'] === 0) {
            \WP_CLI::success('No orphaned field groups found.');
            return;
        }

        foreach ($results['groups'] as $group) {
            $action = $dry_run ? 'Would remove' : 'Removed';
            \WP_CLI::line("{$action}: {$group['title']} ({$group['key']})");
        }

        if (!empty($results['errors'])) {
            foreach ($results['errors'] as $error) {
                \WP_CLI::warning("Error: {$error}");
            }
        }

        $message = $dry_run 
            ? "Found {$results['found']} orphaned field groups that would be removed."
            : "Successfully removed {$results['removed']} orphaned field groups.";
            
        \WP_CLI::success($message);
    }

    /**
     * Refresh all field groups from JSON (removes all, then imports from JSON)
     *
     * ## EXAMPLES
     *
     *     wp happy-place acf refresh
     *
     * @when after_wp_load
     */
    public function refresh($args, $assoc_args) {
        if (!class_exists('ACF')) {
            \WP_CLI::error('ACF is not installed or activated.');
        }

        $acf_manager = \HappyPlace\Core\ACF_Manager::get_instance();
        
        \WP_CLI::line('Starting field group refresh from JSON...');
        
        $results = $acf_manager->refresh_field_groups_from_json();

        \WP_CLI::line("Removed {$results['removed_from_db']} field groups from database");
        \WP_CLI::line("Imported {$results['imported_from_json']} field groups from JSON files");

        if (!empty($results['errors'])) {
            foreach ($results['errors'] as $error) {
                \WP_CLI::warning("Error: {$error}");
            }
        }

        \WP_CLI::success("Successfully refreshed {$results['imported_from_json']} field groups from JSON.");
    }

    /**
     * Export current database field groups to JSON files
     *
     * ## EXAMPLES
     *
     *     wp happy-place acf export
     *
     * @when after_wp_load
     */
    public function export($args, $assoc_args) {
        if (!class_exists('ACF')) {
            \WP_CLI::error('ACF is not installed or activated.');
        }

        $acf_manager = \HappyPlace\Core\ACF_Manager::get_instance();
        
        \WP_CLI::line('Exporting field groups to JSON files...');
        
        $results = $acf_manager->export_field_groups_to_json();

        if (!$results['success']) {
            \WP_CLI::error($results['message']);
        }

        \WP_CLI::line("Exported {$results['exported']} field groups to JSON files");

        if (!empty($results['errors'])) {
            foreach ($results['errors'] as $error) {
                \WP_CLI::warning("Error: {$error}");
            }
        }

        \WP_CLI::success("Successfully exported {$results['exported']} field groups to JSON.");
    }

    /**
     * Show field group synchronization status
     *
     * ## EXAMPLES
     *
     *     wp happy-place acf status
     *
     * @when after_wp_load
     */
    public function status($args, $assoc_args) {
        if (!class_exists('ACF')) {
            \WP_CLI::error('ACF is not installed or activated.');
        }

        $acf_manager = \HappyPlace\Core\ACF_Manager::get_instance();
        $status = $acf_manager->get_sync_status();

        \WP_CLI::line('Field Group Synchronization Status:');
        \WP_CLI::line('=====================================');
        \WP_CLI::line("JSON Files: {$status['json_groups']}");
        \WP_CLI::line("Database Groups: {$status['db_groups']}");
        \WP_CLI::line("Orphaned Groups: {$status['orphaned_groups']}");
        \WP_CLI::line("Out of Sync: {$status['out_of_sync']}");
        \WP_CLI::line("In Sync: {$status['in_sync']}");

        if (!empty($status['orphaned_details'])) {
            \WP_CLI::line("\nOrphaned Groups (in database but no JSON file):");
            foreach ($status['orphaned_details'] as $key => $group) {
                \WP_CLI::line("  - {$group['title']} ({$key})");
            }
        }

        if (!empty($status['out_of_sync_details'])) {
            \WP_CLI::line("\nOut of Sync Groups:");
            foreach ($status['out_of_sync_details'] as $key => $group) {
                \WP_CLI::line("  - {$group['title']} ({$key})");
            }
        }

        if ($status['orphaned_groups'] > 0 || $status['out_of_sync'] > 0) {
            \WP_CLI::line("\nRecommended actions:");
            if ($status['orphaned_groups'] > 0) {
                \WP_CLI::line("  Run 'wp happy-place acf cleanup' to remove orphaned groups");
            }
            if ($status['out_of_sync'] > 0) {
                \WP_CLI::line("  Run 'wp happy-place acf sync' to sync out-of-sync groups");
            }
        } else {
            \WP_CLI::success("All field groups are in sync!");
        }
    }
}