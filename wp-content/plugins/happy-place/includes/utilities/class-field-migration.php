<?php
/**
 * Field Migration Helper
 * Migrate old field structure to new streamlined structure
 *
 * @package HappyPlace\Utilities
 */

namespace HappyPlace\Utilities;

if (!defined('ABSPATH')) {
    exit;
}

class Field_Migration {
    
    /**
     * Map old fields to new fields
     */
    private static $field_map = [
        'bathrooms' => ['bathrooms_full', 'bathrooms_half'],
        'property_tax' => 'property_taxes', 
        'buyer_agent_commission' => 'buyer_commission',
        'featured_image' => 'primary_photo',
        'gallery_images' => 'photo_gallery',
        'virtual_tour_embed' => 'virtual_tour_url',
        'video_embed' => 'video_url'
    ];
    
    /**
     * Migration log for tracking changes
     */
    private static $migration_log = [];
    
    /**
     * Migrate a single listing
     *
     * @param int $post_id The post ID to migrate
     * @return bool Success status
     */
    public static function migrate_listing($post_id) {
        // Check if already migrated
        if (!self::needs_migration($post_id)) {
            self::$migration_log[] = "Listing {$post_id}: Already migrated, skipping";
            return true;
        }
        
        $migrated_fields = 0;
        
        foreach (self::$field_map as $old_field => $new_field) {
            $old_value = get_field($old_field, $post_id);
            
            if ($old_value !== false && !empty($old_value)) {
                if (is_array($new_field)) {
                    // Special handling for fields that split into multiple
                    if ($old_field === 'bathrooms') {
                        self::migrate_bathrooms($post_id, $old_value);
                        $migrated_fields++;
                    }
                } else {
                    // Direct field mapping
                    $success = update_field($new_field, $old_value, $post_id);
                    if ($success) {
                        // Clear old field
                        delete_field($old_field, $post_id);
                        $migrated_fields++;
                        self::$migration_log[] = "Listing {$post_id}: Migrated '{$old_field}' to '{$new_field}'";
                    } else {
                        self::$migration_log[] = "Listing {$post_id}: Failed to migrate '{$old_field}' to '{$new_field}'";
                    }
                }
            }
        }
        
        if ($migrated_fields > 0) {
            // Mark as migrated
            update_post_meta($post_id, '_hp_migrated_v3', 'yes');
            update_post_meta($post_id, '_hp_migration_date', current_time('mysql'));
            self::$migration_log[] = "Listing {$post_id}: Migration completed, {$migrated_fields} fields migrated";
        }
        
        return true;
    }
    
    /**
     * Check if listing needs migration
     *
     * @param int $post_id The post ID to check
     * @return bool True if migration is needed
     */
    public static function needs_migration($post_id) {
        return get_post_meta($post_id, '_hp_migrated_v3', true) !== 'yes';
    }
    
    /**
     * Migrate bathrooms field (split into full and half)
     *
     * @param int $post_id The post ID
     * @param mixed $old_value The old bathrooms value
     */
    private static function migrate_bathrooms($post_id, $old_value) {
        // If it's a decimal like 2.5, split into full=2, half=1
        if (is_numeric($old_value)) {
            $full_baths = floor($old_value);
            $half_baths = ($old_value - $full_baths) >= 0.5 ? 1 : 0;
            
            update_field('bathrooms_full', $full_baths, $post_id);
            update_field('bathrooms_half', $half_baths, $post_id);
            
            // Clear old field
            delete_field('bathrooms', $post_id);
            
            self::$migration_log[] = "Listing {$post_id}: Migrated bathrooms {$old_value} to full={$full_baths}, half={$half_baths}";
        }
    }
    
    /**
     * Migrate all listings in batches
     *
     * @param int $batch_size Number of listings to process per batch
     * @param int $offset Starting offset
     * @return array Migration results
     */
    public static function migrate_all_listings($batch_size = 20, $offset = 0) {
        $results = [
            'total_processed' => 0,
            'migrated' => 0,
            'skipped' => 0,
            'errors' => 0,
            'log' => []
        ];
        
        // Get listings that need migration
        $listings = get_posts([
            'post_type' => 'listing',
            'posts_per_page' => $batch_size,
            'offset' => $offset,
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => '_hp_migrated_v3',
                    'compare' => 'NOT EXISTS'
                ],
                [
                    'key' => '_hp_migrated_v3',
                    'value' => 'yes',
                    'compare' => '!='
                ]
            ],
            'fields' => 'ids'
        ]);
        
        foreach ($listings as $listing_id) {
            $results['total_processed']++;
            
            try {
                if (self::needs_migration($listing_id)) {
                    $success = self::migrate_listing($listing_id);
                    if ($success) {
                        $results['migrated']++;
                    } else {
                        $results['errors']++;
                    }
                } else {
                    $results['skipped']++;
                }
            } catch (Exception $e) {
                $results['errors']++;
                self::$migration_log[] = "Listing {$listing_id}: Error - " . $e->getMessage();
            }
        }
        
        $results['log'] = self::$migration_log;
        self::$migration_log = []; // Reset log
        
        return $results;
    }
    
    /**
     * Get migration statistics
     *
     * @return array Migration statistics
     */
    public static function get_migration_stats() {
        $total_listings = wp_count_posts('listing')->publish;
        
        $migrated_listings = get_posts([
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => '_hp_migrated_v3',
                    'value' => 'yes',
                    'compare' => '='
                ]
            ],
            'fields' => 'ids'
        ]);
        
        $needs_migration = get_posts([
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => '_hp_migrated_v3',
                    'compare' => 'NOT EXISTS'
                ],
                [
                    'key' => '_hp_migrated_v3',
                    'value' => 'yes',
                    'compare' => '!='
                ]
            ],
            'fields' => 'ids'
        ]);
        
        return [
            'total_listings' => $total_listings,
            'migrated' => count($migrated_listings),
            'needs_migration' => count($needs_migration),
            'migration_complete' => count($needs_migration) === 0
        ];
    }
    
    /**
     * Create a migration admin page
     */
    public static function render_migration_admin_page() {
        $stats = self::get_migration_stats();
        
        if (isset($_POST['migrate_batch'])) {
            $batch_size = intval($_POST['batch_size']) ?: 20;
            $results = self::migrate_all_listings($batch_size);
        }
        
        ?>
        <div class="wrap">
            <h1>Happy Place - Field Migration</h1>
            
            <div class="notice notice-info">
                <p><strong>Field Structure Migration:</strong> This tool migrates old field names to the new streamlined structure.</p>
            </div>
            
            <div class="card">
                <h2>Migration Statistics</h2>
                <table class="widefat">
                    <tr>
                        <td><strong>Total Listings:</strong></td>
                        <td><?php echo $stats['total_listings']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Already Migrated:</strong></td>
                        <td><?php echo $stats['migrated']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Needs Migration:</strong></td>
                        <td><?php echo $stats['needs_migration']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>
                            <?php if ($stats['migration_complete']): ?>
                                <span class="notice notice-success inline">✅ Migration Complete</span>
                            <?php else: ?>
                                <span class="notice notice-warning inline">⚠️ Migration Needed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
            
            <?php if ($stats['needs_migration'] > 0): ?>
            <div class="card">
                <h2>Run Migration</h2>
                <form method="post">
                    <table class="form-table">
                        <tr>
                            <th scope="row">Batch Size</th>
                            <td>
                                <input type="number" name="batch_size" value="20" min="1" max="100" />
                                <p class="description">Number of listings to process at once (recommended: 20)</p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" name="migrate_batch" class="button-primary" 
                               value="Migrate Batch" onclick="return confirm('Are you sure you want to run the migration?')" />
                    </p>
                </form>
            </div>
            <?php endif; ?>
            
            <?php if (isset($results)): ?>
            <div class="card">
                <h2>Migration Results</h2>
                <div class="notice notice-success">
                    <p>
                        <strong>Batch Completed:</strong> 
                        Processed <?php echo $results['total_processed']; ?> listings, 
                        Migrated <?php echo $results['migrated']; ?>, 
                        Skipped <?php echo $results['skipped']; ?>, 
                        Errors <?php echo $results['errors']; ?>
                    </p>
                </div>
                
                <?php if (!empty($results['log'])): ?>
                <h3>Migration Log</h3>
                <div style="background: #f9f9f9; padding: 10px; max-height: 300px; overflow-y: scroll; font-family: monospace; font-size: 12px;">
                    <?php foreach ($results['log'] as $log_entry): ?>
                        <div><?php echo esc_html($log_entry); ?></div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <div class="card">
                <h2>Field Mapping</h2>
                <p>The following field mappings will be applied:</p>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>Old Field Name</th>
                            <th>New Field Name(s)</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>bathrooms</code></td>
                            <td><code>bathrooms_full</code> + <code>bathrooms_half</code></td>
                            <td>Decimal values split (e.g., 2.5 → full=2, half=1)</td>
                        </tr>
                        <tr>
                            <td><code>property_tax</code></td>
                            <td><code>property_taxes</code></td>
                            <td>Direct rename</td>
                        </tr>
                        <tr>
                            <td><code>buyer_agent_commission</code></td>
                            <td><code>buyer_commission</code></td>
                            <td>Direct rename</td>
                        </tr>
                        <tr>
                            <td><code>featured_image</code></td>
                            <td><code>primary_photo</code></td>
                            <td>Direct rename</td>
                        </tr>
                        <tr>
                            <td><code>gallery_images</code></td>
                            <td><code>photo_gallery</code></td>
                            <td>Direct rename</td>
                        </tr>
                        <tr>
                            <td><code>virtual_tour_embed</code></td>
                            <td><code>virtual_tour_url</code></td>
                            <td>Direct rename</td>
                        </tr>
                        <tr>
                            <td><code>video_embed</code></td>
                            <td><code>video_url</code></td>
                            <td>Direct rename</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
}