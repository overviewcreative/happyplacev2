<?php
/**
 * Transaction Bridge Functions
 * 
 * Provides a comprehensive interface between the plugin layer and templates
 * for the transaction post type. All data access should go through these functions
 * rather than direct WordPress or ACF calls.
 *
 * @package HappyPlaceTheme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get all transaction data
 * 
 * @param int|WP_Post $transaction Transaction ID or post object
 * @return array Complete transaction data
 */
function hpt_get_transaction($transaction = null) {
    $transaction = get_post($transaction);
    
    if (!$transaction || $transaction->post_type !== 'transaction') {
        return null;
    }
    
    // Get enhanced data from Transaction Service if available
    $enhanced_data = null;
    $commission_data = null;
    
    if (class_exists('HappyPlace\\Services\\TransactionService')) {
        $service = new \HappyPlace\Services\TransactionService();
        $service->init();
        $commission_data = $service->calculate_transaction_commission($transaction->ID);
    }
    
    $base_data = array(
        'id' => $transaction->ID,
        'title' => get_the_title($transaction),
        'slug' => $transaction->post_name,
        'url' => get_permalink($transaction),
        'status' => $transaction->post_status,
        'date_created' => $transaction->post_date,
        'date_modified' => $transaction->post_modified,
        
        // Basic transaction info
        'transaction_type' => hpt_get_transaction_type($transaction->ID),
        'transaction_status' => hpt_get_transaction_status($transaction->ID),
        'mls_number' => hpt_get_transaction_mls_number($transaction->ID),
        'contract_date' => hpt_get_transaction_contract_date($transaction->ID),
        'closing_date' => hpt_get_transaction_closing_date($transaction->ID),
        'actual_closing_date' => hpt_get_transaction_actual_closing_date($transaction->ID),
        
        // Financial details
        'sale_price' => hpt_get_transaction_sale_price($transaction->ID),
        'list_price' => hpt_get_transaction_list_price($transaction->ID),
        'price_variance' => hpt_get_transaction_price_variance($transaction->ID),
        'commission_total' => hpt_get_transaction_commission_total($transaction->ID),
        'commission_split' => hpt_get_transaction_commission_split($transaction->ID),
        'agent_commission' => hpt_get_transaction_agent_commission($transaction->ID),
        
        // Property information
        'property_address' => hpt_get_transaction_property_address($transaction->ID),
        'property_type' => hpt_get_transaction_property_type($transaction->ID),
        'square_feet' => hpt_get_transaction_square_feet($transaction->ID),
        'bedrooms' => hpt_get_transaction_bedrooms($transaction->ID),
        'bathrooms' => hpt_get_transaction_bathrooms($transaction->ID),
        'year_built' => hpt_get_transaction_year_built($transaction->ID),
        
        // Relationships
        'listing' => hpt_get_transaction_listing($transaction->ID),
        'listing_agent' => hpt_get_transaction_listing_agent($transaction->ID),
        'buyer_agent' => hpt_get_transaction_buyer_agent($transaction->ID),
        'client' => hpt_get_transaction_client($transaction->ID),
        
        // Parties involved
        'seller_name' => hpt_get_transaction_seller_name($transaction->ID),
        'buyer_name' => hpt_get_transaction_buyer_name($transaction->ID),
        'title_company' => hpt_get_transaction_title_company($transaction->ID),
        'lender' => hpt_get_transaction_lender($transaction->ID),
        
        // Timeline and metrics
        'days_on_market' => hpt_get_transaction_days_on_market($transaction->ID),
        'days_to_close' => hpt_get_transaction_days_to_close($transaction->ID),
        'contract_to_close_days' => hpt_get_transaction_contract_to_close_days($transaction->ID),
        
        // Documents and notes
        'documents' => hpt_get_transaction_documents($transaction->ID),
        'notes' => hpt_get_transaction_notes($transaction->ID),
        'closing_notes' => hpt_get_transaction_closing_notes($transaction->ID),
        
        // Status checks
        'is_closed' => hpt_is_transaction_closed($transaction->ID),
        'is_pending' => hpt_is_transaction_pending($transaction->ID),
        'is_cancelled' => hpt_is_transaction_cancelled($transaction->ID),
        'is_confidential' => hpt_is_transaction_confidential($transaction->ID),
    );
    
    // Merge with enhanced commission data if available
    if ($commission_data) {
        $base_data['commission_data'] = $commission_data;
        $base_data['total_commission'] = $commission_data['total_commission'] ?? 0;
        $base_data['agent_commission'] = $commission_data['agent_commission'] ?? 0;
        $base_data['brokerage_commission'] = $commission_data['brokerage_commission'] ?? 0;
    }
    
    return $base_data;
}

/**
 * Get transaction type
 */
function hpt_get_transaction_type($transaction_id) {
    return get_field('transaction_type', $transaction_id) ?: 'sale';
}

/**
 * Get transaction type label
 */
function hpt_get_transaction_type_label($transaction_id) {
    $type = hpt_get_transaction_type($transaction_id);
    
    $labels = array(
        'sale' => __('Sale', 'happy-place-theme'),
        'purchase' => __('Purchase', 'happy-place-theme'),
        'lease' => __('Lease', 'happy-place-theme'),
        'rental' => __('Rental', 'happy-place-theme'),
        'referral' => __('Referral', 'happy-place-theme'),
    );
    
    return $labels[$type] ?? ucfirst($type);
}

/**
 * Get transaction status
 */
function hpt_get_transaction_status($transaction_id) {
    return get_field('transaction_status', $transaction_id) ?: 'pending';
}

/**
 * Get transaction status label
 */
function hpt_get_transaction_status_label($transaction_id) {
    $status = hpt_get_transaction_status($transaction_id);
    
    $labels = array(
        'pending' => __('Pending', 'happy-place-theme'),
        'under_contract' => __('Under Contract', 'happy-place-theme'),
        'inspection' => __('Inspection Period', 'happy-place-theme'),
        'appraisal' => __('Appraisal', 'happy-place-theme'),
        'financing' => __('Financing', 'happy-place-theme'),
        'closing' => __('Ready to Close', 'happy-place-theme'),
        'closed' => __('Closed', 'happy-place-theme'),
        'cancelled' => __('Cancelled', 'happy-place-theme'),
        'expired' => __('Expired', 'happy-place-theme'),
    );
    
    return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
}

/**
 * Get MLS number
 */
function hpt_get_transaction_mls_number($transaction_id) {
    return get_field('mls_number', $transaction_id) ?: '';
}

/**
 * Get contract date
 */
function hpt_get_transaction_contract_date($transaction_id) {
    return get_field('contract_date', $transaction_id) ?: '';
}

/**
 * Get expected closing date
 */
function hpt_get_transaction_closing_date($transaction_id) {
    return get_field('closing_date', $transaction_id) ?: get_field('expected_closing_date', $transaction_id) ?: '';
}

/**
 * Get actual closing date
 */
function hpt_get_transaction_actual_closing_date($transaction_id) {
    return get_field('actual_closing_date', $transaction_id) ?: '';
}

/**
 * Get sale price
 */
function hpt_get_transaction_sale_price($transaction_id) {
    return floatval(get_field('sale_price', $transaction_id) ?: get_field('final_price', $transaction_id));
}

/**
 * Get formatted sale price
 */
function hpt_get_transaction_sale_price_formatted($transaction_id) {
    $price = hpt_get_transaction_sale_price($transaction_id);
    
    if (!$price) {
        return __('N/A', 'happy-place-theme');
    }
    
    return '$' . number_format($price);
}

/**
 * Get original list price
 */
function hpt_get_transaction_list_price($transaction_id) {
    return floatval(get_field('list_price', $transaction_id) ?: get_field('original_price', $transaction_id));
}

/**
 * Get price variance (sale price vs list price)
 */
function hpt_get_transaction_price_variance($transaction_id) {
    $sale_price = hpt_get_transaction_sale_price($transaction_id);
    $list_price = hpt_get_transaction_list_price($transaction_id);
    
    if (!$sale_price || !$list_price) {
        return 0;
    }
    
    return $sale_price - $list_price;
}

/**
 * Get price variance percentage
 */
function hpt_get_transaction_price_variance_percentage($transaction_id) {
    $sale_price = hpt_get_transaction_sale_price($transaction_id);
    $list_price = hpt_get_transaction_list_price($transaction_id);
    
    if (!$sale_price || !$list_price) {
        return 0;
    }
    
    return round((($sale_price - $list_price) / $list_price) * 100, 2);
}

/**
 * Get total commission
 */
function hpt_get_transaction_commission_total($transaction_id) {
    return floatval(get_field('commission_total', $transaction_id));
}

/**
 * Get commission split
 */
function hpt_get_transaction_commission_split($transaction_id) {
    return floatval(get_field('commission_split', $transaction_id));
}

/**
 * Get agent commission
 */
function hpt_get_transaction_agent_commission($transaction_id) {
    $agent_commission = get_field('agent_commission', $transaction_id);
    
    if ($agent_commission) {
        return floatval($agent_commission);
    }
    
    // Calculate based on total commission and split
    $total = hpt_get_transaction_commission_total($transaction_id);
    $split = hpt_get_transaction_commission_split($transaction_id);
    
    if ($total && $split) {
        return $total * ($split / 100);
    }
    
    return 0;
}

/**
 * Get property address
 */
function hpt_get_transaction_property_address($transaction_id, $format = 'full') {
    $address = array(
        'street' => get_field('property_address', $transaction_id) ?: '',
        'city' => get_field('property_city', $transaction_id) ?: '',
        'state' => get_field('property_state', $transaction_id) ?: '',
        'zip' => get_field('property_zip', $transaction_id) ?: '',
    );
    
    if ($format === 'array') {
        return $address;
    }
    
    if ($format === 'street') {
        return $address['street'];
    }
    
    if ($format === 'city_state') {
        return trim($address['city'] . ', ' . $address['state']);
    }
    
    // Full address
    $parts = array_filter(array(
        $address['street'],
        $address['city'],
        $address['state'] . ' ' . $address['zip']
    ));
    
    return implode(', ', $parts);
}

/**
 * Get property type
 */
function hpt_get_transaction_property_type($transaction_id) {
    return get_field('property_type', $transaction_id) ?: '';
}

/**
 * Get square feet
 */
function hpt_get_transaction_square_feet($transaction_id) {
    return intval(get_field('square_feet', $transaction_id));
}

/**
 * Get bedrooms
 */
function hpt_get_transaction_bedrooms($transaction_id) {
    return intval(get_field('bedrooms', $transaction_id));
}

/**
 * Get bathrooms
 */
function hpt_get_transaction_bathrooms($transaction_id) {
    return floatval(get_field('bathrooms', $transaction_id));
}

/**
 * Get year built
 */
function hpt_get_transaction_year_built($transaction_id) {
    return intval(get_field('year_built', $transaction_id));
}

/**
 * Get related listing
 */
function hpt_get_transaction_listing($transaction_id) {
    $listing_id = get_field('related_listing', $transaction_id);
    
    if (!$listing_id) {
        $listing_id = get_field('listing', $transaction_id);
    }
    
    return $listing_id ? intval($listing_id) : null;
}

/**
 * Get listing agent
 */
function hpt_get_transaction_listing_agent($transaction_id) {
    $agent_id = get_field('listing_agent', $transaction_id);
    
    if (!$agent_id) {
        // Try to get from related listing
        $listing_id = hpt_get_transaction_listing($transaction_id);
        if ($listing_id) {
            $agent_id = hpt_get_listing_agent($listing_id);
        }
    }
    
    return $agent_id ? intval($agent_id) : null;
}

/**
 * Get buyer agent
 */
function hpt_get_transaction_buyer_agent($transaction_id) {
    $agent_id = get_field('buyer_agent', $transaction_id);
    
    if (!$agent_id) {
        $agent_id = get_field('selling_agent', $transaction_id);
    }
    
    return $agent_id ? intval($agent_id) : null;
}

/**
 * Get client
 */
function hpt_get_transaction_client($transaction_id) {
    return get_field('client', $transaction_id) ?: '';
}

/**
 * Get seller name
 */
function hpt_get_transaction_seller_name($transaction_id) {
    return get_field('seller_name', $transaction_id) ?: '';
}

/**
 * Get buyer name
 */
function hpt_get_transaction_buyer_name($transaction_id) {
    return get_field('buyer_name', $transaction_id) ?: '';
}

/**
 * Get title company
 */
function hpt_get_transaction_title_company($transaction_id) {
    return get_field('title_company', $transaction_id) ?: '';
}

/**
 * Get lender
 */
function hpt_get_transaction_lender($transaction_id) {
    return get_field('lender', $transaction_id) ?: '';
}

/**
 * Get days on market
 */
function hpt_get_transaction_days_on_market($transaction_id) {
    $dom = get_field('days_on_market', $transaction_id);
    
    if ($dom) {
        return intval($dom);
    }
    
    // Try to calculate from listing date and contract date
    $listing_id = hpt_get_transaction_listing($transaction_id);
    $contract_date = hpt_get_transaction_contract_date($transaction_id);
    
    if ($listing_id && $contract_date) {
        $listing_date = hpt_get_listing_date($listing_id);
        if ($listing_date) {
            $dom = floor((strtotime($contract_date) - strtotime($listing_date)) / (60 * 60 * 24));
            return max(0, $dom);
        }
    }
    
    return 0;
}

/**
 * Get days to close
 */
function hpt_get_transaction_days_to_close($transaction_id) {
    $closing_date = hpt_get_transaction_closing_date($transaction_id);
    
    if (!$closing_date) {
        return 0;
    }
    
    $today = time();
    $close_timestamp = strtotime($closing_date);
    
    return max(0, floor(($close_timestamp - $today) / (60 * 60 * 24)));
}

/**
 * Get days from contract to close
 */
function hpt_get_transaction_contract_to_close_days($transaction_id) {
    $contract_date = hpt_get_transaction_contract_date($transaction_id);
    $closing_date = hpt_get_transaction_actual_closing_date($transaction_id);
    
    if (!$closing_date) {
        $closing_date = hpt_get_transaction_closing_date($transaction_id);
    }
    
    if (!$contract_date || !$closing_date) {
        return 0;
    }
    
    return floor((strtotime($closing_date) - strtotime($contract_date)) / (60 * 60 * 24));
}

/**
 * Get transaction documents
 */
function hpt_get_transaction_documents($transaction_id) {
    $documents = get_field('documents', $transaction_id);
    
    if (!is_array($documents)) {
        $documents = array();
    }
    
    return $documents;
}

/**
 * Get transaction notes
 */
function hpt_get_transaction_notes($transaction_id) {
    return get_field('notes', $transaction_id) ?: '';
}

/**
 * Get closing notes
 */
function hpt_get_transaction_closing_notes($transaction_id) {
    return get_field('closing_notes', $transaction_id) ?: '';
}

/**
 * Check if transaction is closed
 */
function hpt_is_transaction_closed($transaction_id) {
    $status = hpt_get_transaction_status($transaction_id);
    return $status === 'closed';
}

/**
 * Check if transaction is pending
 */
function hpt_is_transaction_pending($transaction_id) {
    $status = hpt_get_transaction_status($transaction_id);
    return in_array($status, array('pending', 'under_contract', 'inspection', 'appraisal', 'financing', 'closing'));
}

/**
 * Check if transaction is cancelled
 */
function hpt_is_transaction_cancelled($transaction_id) {
    $status = hpt_get_transaction_status($transaction_id);
    return in_array($status, array('cancelled', 'expired'));
}

/**
 * Check if transaction is confidential
 */
function hpt_is_transaction_confidential($transaction_id) {
    return get_field('confidential', $transaction_id) == true;
}

/**
 * Query transactions
 */
function hpt_query_transactions($args = array()) {
    $defaults = array(
        'post_type' => 'transaction',
        'post_status' => 'publish',
        'posts_per_page' => 20,
        'orderby' => 'date',
        'order' => 'DESC',
    );
    
    $args = wp_parse_args($args, $defaults);
    
    return new WP_Query($args);
}

/**
 * Get recent transactions
 */
function hpt_get_recent_transactions($limit = 10) {
    return get_posts(array(
        'post_type' => 'transaction',
        'posts_per_page' => $limit,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'transaction_status',
                'value' => 'closed',
                'compare' => '='
            )
        ),
        'meta_key' => 'actual_closing_date',
        'orderby' => 'meta_value',
        'order' => 'DESC'
    ));
}

/**
 * Get agent transactions
 */
function hpt_get_agent_transactions($agent_id, $status = null) {
    $meta_query = array(
        'relation' => 'OR',
        array(
            'key' => 'listing_agent',
            'value' => $agent_id,
            'compare' => '='
        ),
        array(
            'key' => 'buyer_agent',
            'value' => $agent_id,
            'compare' => '='
        )
    );
    
    if ($status) {
        $meta_query = array(
            'relation' => 'AND',
            $meta_query,
            array(
                'key' => 'transaction_status',
                'value' => $status,
                'compare' => '='
            )
        );
    }
    
    return get_posts(array(
        'post_type' => 'transaction',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => $meta_query,
        'orderby' => 'date',
        'order' => 'DESC'
    ));
}

/**
 * Get pending transactions
 */
function hpt_get_pending_transactions($agent_id = null) {
    $meta_query = array(
        array(
            'key' => 'transaction_status',
            'value' => array('pending', 'under_contract', 'inspection', 'appraisal', 'financing', 'closing'),
            'compare' => 'IN'
        )
    );
    
    if ($agent_id) {
        $meta_query[] = array(
            'relation' => 'OR',
            array(
                'key' => 'listing_agent',
                'value' => $agent_id,
                'compare' => '='
            ),
            array(
                'key' => 'buyer_agent',
                'value' => $agent_id,
                'compare' => '='
            )
        );
    }
    
    return get_posts(array(
        'post_type' => 'transaction',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => $meta_query,
        'meta_key' => 'closing_date',
        'orderby' => 'meta_value',
        'order' => 'ASC'
    ));
}

/**
 * Get transaction pipeline data with service integration
 * 
 * @param int $agent_id Agent user ID
 * @return array Pipeline data organized by status
 */
function hpt_get_transaction_pipeline_with_service($agent_id = null) {
    if (class_exists('HappyPlace\\Services\\TransactionService')) {
        $service = new \HappyPlace\Services\TransactionService();
        $service->init();
        return $service->get_transaction_pipeline($agent_id);
    }
    
    // Fallback to basic pipeline
    $pipeline = [];
    $statuses = ['draft', 'offer_submitted', 'under_contract', 'inspection', 'financing', 'closing'];
    
    foreach ($statuses as $status) {
        $transactions = hpt_get_transactions_by_status($status, $agent_id);
        $pipeline[$status] = [
            'transactions' => array_map(function($post) {
                return hpt_get_transaction($post->ID);
            }, $transactions),
            'count' => count($transactions),
            'total_value' => array_sum(array_map(function($post) {
                return floatval(get_field('sale_price', $post->ID) ?: 0);
            }, $transactions))
        ];
    }
    
    return $pipeline;
}

/**
 * Get transaction statistics with service integration
 * 
 * @param int $agent_id Agent user ID
 * @param string $period Time period (ytd, mtd, etc.)
 * @return array Statistics data
 */
function hpt_get_transaction_stats_with_service($agent_id = null, $period = 'ytd') {
    if (class_exists('HappyPlace\\Services\\TransactionService')) {
        $service = new \HappyPlace\Services\TransactionService();
        $service->init();
        return $service->get_transaction_stats($agent_id, $period);
    }
    
    // Fallback basic stats
    $closed_transactions = hpt_get_closed_transactions($agent_id);
    $active_transactions = hpt_get_pending_transactions($agent_id);
    
    $total_volume = 0;
    $total_commission = 0;
    
    foreach ($closed_transactions as $transaction) {
        $sale_price = floatval(get_field('sale_price', $transaction->ID) ?: 0);
        $total_volume += $sale_price;
        
        // Basic commission calculation (assuming 3% agent commission)
        $commission = $sale_price * 0.03;
        $total_commission += $commission;
    }
    
    return [
        'active_transactions' => count($active_transactions),
        'closed_transactions' => count($closed_transactions),
        'total_volume' => $total_volume,
        'total_commission' => $total_commission
    ];
}