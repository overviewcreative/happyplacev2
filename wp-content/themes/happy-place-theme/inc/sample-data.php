<?php
/**
 * Sample Data Generator for Happy Place Dashboard Testing
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create sample listings for testing
 */
function hpt_create_sample_listings($count = 10) {
    if (!current_user_can('manage_options')) {
        return false;
    }

    $sample_listings = array(
        array(
            'title' => 'Beautiful Modern Home with Pool',
            'price' => 750000,
            'bedrooms' => 4,
            'bathrooms' => 3,
            'address' => '123 Oak Street, Austin, TX 78701'
        ),
        array(
            'title' => 'Cozy Downtown Condo',
            'price' => 425000,
            'bedrooms' => 2,
            'bathrooms' => 2,
            'address' => '456 Main Avenue, Austin, TX 78702'
        ),
        array(
            'title' => 'Spacious Family Home',
            'price' => 650000,
            'bedrooms' => 5,
            'bathrooms' => 4,
            'address' => '789 Pine Drive, Austin, TX 78703'
        ),
        array(
            'title' => 'Luxury Executive Estate',
            'price' => 1200000,
            'bedrooms' => 6,
            'bathrooms' => 5,
            'address' => '321 Highland Boulevard, Austin, TX 78704'
        ),
        array(
            'title' => 'Starter Home Perfect for First-Time Buyers',
            'price' => 385000,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'address' => '654 Elm Street, Austin, TX 78705'
        )
    );

    $created = 0;
    for ($i = 0; $i < min($count, count($sample_listings)); $i++) {
        $listing = $sample_listings[$i];
        
        $post_id = wp_insert_post(array(
            'post_type' => 'listing',
            'post_title' => $listing['title'],
            'post_content' => 'This is a sample listing created for dashboard testing purposes.',
            'post_status' => 'publish',
            'post_author' => get_current_user_id()
        ));

        if (!is_wp_error($post_id)) {
            // Add basic meta data
            update_post_meta($post_id, 'price', $listing['price']);
            update_post_meta($post_id, 'bedrooms', $listing['bedrooms']);
            update_post_meta($post_id, 'bathrooms', $listing['bathrooms']);
            update_post_meta($post_id, 'address', $listing['address']);
            update_post_meta($post_id, 'listing_status', 'active');
            
            $created++;
        }
    }

    return $created;
}

/**
 * Admin interface for creating sample listings
 */
function hpt_add_sample_data_admin_page() {
    add_management_page(
        'Create Sample Listings',
        'Sample Listings', 
        'manage_options',
        'hpt-sample-data',
        'hpt_sample_data_admin_page'
    );
}
add_action('admin_menu', 'hpt_add_sample_data_admin_page');

/**
 * Admin page content
 */
function hpt_sample_data_admin_page() {
    if (isset($_POST['create_samples'])) {
        $count = intval($_POST['count']);
        $created = hpt_create_sample_listings($count);
        echo '<div class="notice notice-success"><p>Created ' . $created . ' sample listings!</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>Create Sample Listings</h1>
        <p>This will create sample listings for testing the dashboard functionality.</p>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th><label for="count">Number of listings to create:</label></th>
                    <td>
                        <select name="count" id="count">
                            <option value="5">5 listings</option>
                            <option value="3">3 listings</option>
                            <option value="1">1 listing</option>
                        </select>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="create_samples" class="button-primary" value="Create Sample Listings">
            </p>
        </form>
    </div>
    <?php
}

/**
 * WP-CLI command to create sample listings
 */
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('hpt create-samples', function($args) {
        $count = isset($args[0]) ? intval($args[0]) : 5;
        $created = hpt_create_sample_listings($count);
        WP_CLI::success("Created {$created} sample listings");
    });
}