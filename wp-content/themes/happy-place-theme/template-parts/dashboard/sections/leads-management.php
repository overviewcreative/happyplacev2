<?php
/**
 * Leads Management Section - Database Connected
 * Agent dashboard section for managing customer leads from hp_leads table
 * 
 * @package HappyPlaceTheme
 */

// Security check
if (!is_user_logged_in()) {
    return;
}

$current_user = wp_get_current_user();
$user_roles = $current_user->roles;
$is_agent = in_array('agent', $user_roles) || in_array('administrator', $user_roles);

if (!$is_agent) {
    return;
}

global $wpdb;

// Get leads filter
$status_filter = isset($_GET['lead_status']) ? sanitize_text_field($_GET['lead_status']) : 'all';
$search_query = isset($_GET['leads_search']) ? sanitize_text_field($_GET['leads_search']) : '';
$source_filter = isset($_GET['lead_source']) ? sanitize_text_field($_GET['lead_source']) : '';

// Build query for hp_leads table
$leads_table = $wpdb->prefix . 'hp_leads';
$where_conditions = ['1=1'];
$query_params = [];

// Filter by agent (assigned_to or agent_id) - Show all leads for administrators
$is_admin = in_array('administrator', $user_roles);
if (!$is_admin || $status_filter === 'my-leads') {
    $where_conditions[] = '(assigned_to = %d OR agent_id = %d)';
    $query_params[] = $current_user->ID;
    $query_params[] = $current_user->ID;
}

// Filter by status if specified (exclude my-leads which is a view filter, not status)
if ($status_filter !== 'all' && $status_filter !== 'my-leads') {
    $where_conditions[] = 'status = %s';
    $query_params[] = $status_filter;
}

// Filter by source if specified  
if ($source_filter) {
    $where_conditions[] = 'source = %s';
    $query_params[] = $source_filter;
}

// Search functionality
if ($search_query) {
    $where_conditions[] = '(first_name LIKE %s OR last_name LIKE %s OR email LIKE %s OR message LIKE %s)';
    $search_param = '%' . $wpdb->esc_like($search_query) . '%';
    $query_params[] = $search_param;
    $query_params[] = $search_param;
    $query_params[] = $search_param;
    $query_params[] = $search_param;
}

$where_clause = implode(' AND ', $where_conditions);

// Get leads with pagination
$page = max(1, intval($_GET['paged'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

$leads_query = "SELECT * FROM {$leads_table} WHERE {$where_clause} ORDER BY created_at DESC LIMIT %d OFFSET %d";
$query_params[] = $per_page;
$query_params[] = $offset;

$leads = $wpdb->get_results($wpdb->prepare($leads_query, $query_params));

// Get total count for pagination
$count_query = "SELECT COUNT(*) FROM {$leads_table} WHERE {$where_clause}";
$count_params = array_slice($query_params, 0, -2); // Remove limit and offset
$total_leads = $wpdb->get_var($wpdb->prepare($count_query, $count_params));

// Get status counts
$status_counts = [];
$all_statuses = ['new', 'contacted', 'qualified', 'hot', 'warm', 'cold', 'converted', 'lost'];

foreach ($all_statuses as $status) {
    if ($is_admin) {
        $count_query = "SELECT COUNT(*) FROM {$leads_table} WHERE status = %s";
        $status_counts[$status] = $wpdb->get_var($wpdb->prepare($count_query, $status));
    } else {
        $count_query = "SELECT COUNT(*) FROM {$leads_table} WHERE (assigned_to = %d OR agent_id = %d) AND status = %s";
        $status_counts[$status] = $wpdb->get_var($wpdb->prepare($count_query, $current_user->ID, $current_user->ID, $status));
    }
}

// Get source counts for filters
if ($is_admin) {
    $source_query = "SELECT source, COUNT(*) as count FROM {$leads_table} GROUP BY source";
    $sources = $wpdb->get_results($source_query);
} else {
    $source_query = "SELECT source, COUNT(*) as count FROM {$leads_table} WHERE (assigned_to = %d OR agent_id = %d) GROUP BY source";
    $sources = $wpdb->get_results($wpdb->prepare($source_query, $current_user->ID, $current_user->ID));
}
?>

<div class="hph-leads-management">
    
    <!-- Section Header -->
    <div class="hph-section-header hph-flex hph-flex-row hph-justify-between hph-items-start hph-mb-lg">
        <div class="hph-header-content">
            <h2 class="hph-text-2xl hph-font-bold hph-text-gray-800 hph-mb-xs">
                <?php _e('Lead Management', 'happy-place-theme'); ?>
            </h2>
            <p class="hph-text-sm hph-text-gray-600">
                <?php _e('Manage your customer leads, track interactions, and convert prospects into clients.', 'happy-place-theme'); ?>
            </p>
        </div>
        
        <div class="hph-header-actions hph-flex hph-flex-row hph-gap-sm">
            <button class="hph-btn hph-btn-outline hph-btn-sm" id="importLeadsBtn">
                <i class="fas fa-upload hph-mr-xs"></i>
                <?php _e('Import Leads', 'happy-place-theme'); ?>
            </button>
            <button class="hph-btn hph-btn-primary hph-btn-sm" id="addNewLeadBtn">
                <i class="fas fa-user-plus hph-mr-xs"></i>
                <?php _e('Add New Lead', 'happy-place-theme'); ?>
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="hph-bg-white hph-border hph-border-gray-200 hph-rounded-lg hph-p-lg hph-mb-lg">
        <div class="hph-filters-row hph-flex hph-flex-wrap hph-gap-md hph-items-end">
            
            <!-- Search Box -->
            <div class="hph-flex-grow hph-max-w-md">
                <label class="hph-form-label"><?php _e('Search Leads', 'happy-place-theme'); ?></label>
                <div class="hph-relative">
                    <input type="text" 
                           class="hph-form-input hph-pl-10" 
                           placeholder="<?php _e('Search by name, email, or message...', 'happy-place-theme'); ?>"
                           id="leadsSearch" 
                           value="<?php echo esc_attr($search_query); ?>">
                    <i class="fas fa-search hph-absolute hph-left-3 hph-top-1/2 hph-transform -hph-translate-y-1/2 hph-text-gray-400"></i>
                </div>
            </div>

            <!-- Source Filter -->
            <div class="hph-min-w-0 hph-w-48">
                <label class="hph-form-label"><?php _e('Source', 'happy-place-theme'); ?></label>
                <select class="hph-form-select" id="leadSourceFilter">
                    <option value=""><?php _e('All Sources', 'happy-place-theme'); ?></option>
                    <?php foreach ($sources as $source): ?>
                        <option value="<?php echo esc_attr($source->source); ?>" <?php selected($source_filter, $source->source); ?>>
                            <?php echo esc_html(ucfirst($source->source)); ?> (<?php echo $source->count; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Actions -->
            <div class="hph-flex hph-flex-row hph-gap-sm">
                <button type="button" class="hph-btn hph-btn-secondary hph-btn-sm" id="applyLeadFilters">
                    <i class="fas fa-filter hph-mr-xs"></i>
                    <?php _e('Apply', 'happy-place-theme'); ?>
                </button>
                
                <button type="button" class="hph-btn hph-btn-outline hph-btn-sm" id="clearLeadFilters">
                    <?php _e('Clear', 'happy-place-theme'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Status Filter Tabs -->
    <div class="hph-bg-white hph-border hph-border-gray-200 hph-rounded-lg hph-overflow-hidden hph-gap-4 hph-mb-lg">
        <div class="hph-status-tabs hph-gap-2 hph-flex hph-flex-wrap">
            
            <a href="?section=leads&lead_status=all" 
               class="hph-status-tab <?php echo $status_filter === 'all' ? 'hph-active' : ''; ?>">
                <span class="hph-tab-text"><?php _e('All Leads', 'happy-place-theme'); ?></span>
                <span class="hph-tab-count"><?php echo intval($total_leads); ?></span>
            </a>
            
            <?php if (!$is_admin): ?>
            <a href="?section=leads&lead_status=my-leads" 
               class="hph-status-tab <?php echo $status_filter === 'my-leads' ? 'hph-active' : ''; ?>">
                <span class="hph-tab-text"><?php _e('My Leads', 'happy-place-theme'); ?></span>
                <span class="hph-tab-count"><?php 
                    $my_leads_query = "SELECT COUNT(*) FROM {$leads_table} WHERE (assigned_to = %d OR agent_id = %d)";
                    $my_leads_count = $wpdb->get_var($wpdb->prepare($my_leads_query, $current_user->ID, $current_user->ID));
                    echo intval($my_leads_count); 
                ?></span>
            </a>
            <?php endif; ?>
            
            <a href="?section=leads&lead_status=new" 
               class="hph-status-tab <?php echo $status_filter === 'new' ? 'hph-active' : ''; ?>">
                <span class="hph-tab-text"><?php _e('New', 'happy-place-theme'); ?></span>
                <span class="hph-tab-count hph-count-new"><?php echo intval($status_counts['new']); ?></span>
            </a>
            
            <a href="?section=leads&lead_status=hot" 
               class="hph-status-tab <?php echo $status_filter === 'hot' ? 'hph-active' : ''; ?>">
                <span class="hph-tab-text"><?php _e('Hot', 'happy-place-theme'); ?></span>
                <span class="hph-tab-count hph-count-hot"><?php echo intval($status_counts['hot']); ?></span>
            </a>
            
            <a href="?section=leads&lead_status=warm" 
               class="hph-status-tab <?php echo $status_filter === 'warm' ? 'hph-active' : ''; ?>">
                <span class="hph-tab-text"><?php _e('Warm', 'happy-place-theme'); ?></span>
                <span class="hph-tab-count hph-count-warm"><?php echo intval($status_counts['warm']); ?></span>
            </a>
            
            <a href="?section=leads&lead_status=cold" 
               class="hph-status-tab <?php echo $status_filter === 'cold' ? 'hph-active' : ''; ?>">
                <span class="hph-tab-text"><?php _e('Cold', 'happy-place-theme'); ?></span>
                <span class="hph-tab-count hph-count-cold"><?php echo intval($status_counts['cold']); ?></span>
            </a>
            
            <a href="?section=leads&lead_status=converted" 
               class="hph-status-tab <?php echo $status_filter === 'converted' ? 'hph-active' : ''; ?>">
                <span class="hph-tab-text"><?php _e('Converted', 'happy-place-theme'); ?></span>
                <span class="hph-tab-count hph-count-success"><?php echo intval($status_counts['converted']); ?></span>
            </a>
        </div>
    </div>

    <!-- Leads List -->
    <div class="hph-leads-container">
        <?php if (!empty($leads)): ?>
            <div class="hph-leads-list hph-space-y-md" id="leadsList">
                <?php foreach ($leads as $lead): ?>
                    <?php
                    $lead_name = trim($lead->first_name . ' ' . $lead->last_name);
                    $lead_email = $lead->email;
                    $lead_phone = $lead->phone;
                    $lead_status = $lead->status ?: 'new';
                    $lead_source = $lead->source ?: 'website';
                    $lead_score = $lead->lead_score ?: 0;
                    $created_at = $lead->created_at;
                    $last_contacted = $lead->last_contacted;
                    $listing_id = $lead->listing_id;
                    
                    // Get listing details if available
                    $listing_title = '';
                    if ($listing_id) {
                        $listing_post = get_post($listing_id);
                        if ($listing_post) {
                            $listing_title = $listing_post->post_title;
                        }
                    }
                    
                    // Get FollowUp Boss data if available
                    $followup_boss_data = null;
                    $followup_boss_url = '';
                    if (isset($lead->followup_boss_id) && !empty($lead->followup_boss_id)) {
                        // We have a FollowUp Boss ID from previous sync
                        $followup_boss_url = "https://app.followupboss.com/2/people/{$lead->followup_boss_id}";
                        
                        // Try to get fresh data from FollowUp Boss
                        if (class_exists('\\HappyPlace\\Integrations\\FollowUp_Boss_Integration')) {
                            $fub_integration = \HappyPlace\Integrations\FollowUp_Boss_Integration::get_instance();
                            $followup_boss_data = $fub_integration->get_person($lead->followup_boss_id);
                        }
                    } else {
                        // FollowUp Boss integration disabled - method search_person_by_email() not available
                        // TODO: Implement proper FollowUp Boss search functionality
                    }
                    
                    // Status color mapping
                    $status_colors = [
                        'new' => 'blue',
                        'contacted' => 'purple', 
                        'qualified' => 'green',
                        'hot' => 'red',
                        'warm' => 'orange',
                        'cold' => 'gray',
                        'converted' => 'green',
                        'lost' => 'gray'
                    ];
                    $status_color = $status_colors[$lead_status] ?? 'gray';
                    ?>
                    
                    <div class="hph-lead-card hph-bg-white hph-border hph-border-gray-200 hph-rounded-lg hph-p-lg hph-hover-shadow-md" 
                         data-lead-id="<?php echo esc_attr($lead->id); ?>" 
                         data-status="<?php echo esc_attr($lead_status); ?>">
                        
                        <!-- Lead Header -->
                        <div class="hph-flex hph-flex-row hph-justify-between hph-items-start hph-mb-md">
                            <div class="hph-flex hph-flex-row hph-items-center hph-gap-md">
                                <div class="hph-lead-avatar">
                                    <?php echo get_avatar($lead_email, 48, '', $lead_name, ['class' => 'hph-w-12 hph-h-12 hph-rounded-full']); ?>
                                </div>
                                
                                <div class="hph-lead-info">
                                    <h3 class="hph-text-lg hph-font-semibold hph-text-gray-900 hph-mb-xs">
                                        <?php echo esc_html($lead_name ?: 'Unknown Lead'); ?>
                                    </h3>
                                    
                                    <div class="hph-flex hph-flex-wrap hph-gap-md hph-text-sm hph-text-gray-600">
                                        <?php if ($lead_email): ?>
                                            <a href="mailto:<?php echo esc_attr($lead_email); ?>" 
                                               class="hph-flex hph-items-center hph-gap-xs hph-text-blue-600 hph-hover-text-blue-800">
                                                <i class="fas fa-envelope"></i>
                                                <?php echo esc_html($lead_email); ?>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($lead_phone): ?>
                                            <a href="tel:<?php echo esc_attr($lead_phone); ?>" 
                                               class="hph-flex hph-items-center hph-gap-xs hph-text-blue-600 hph-hover-text-blue-800">
                                                <i class="fas fa-phone"></i>
                                                <?php echo esc_html($lead_phone); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="hph-flex hph-flex-row hph-items-center hph-gap-sm">
                                <div class="hph-status-badge hph-status-<?php echo esc_attr($status_color); ?>">
                                    <span class="hph-status-dot"></span>
                                    <span class="hph-status-text"><?php echo esc_html(ucfirst($lead_status)); ?></span>
                                </div>
                                
                                <div class="hph-dropdown">
                                    <button class="hph-btn hph-btn-ghost hph-btn-sm hph-dropdown-toggle" 
                                            data-lead-id="<?php echo esc_attr($lead->id); ?>">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="hph-dropdown-menu hph-hidden">
                                        <a href="#" class="hph-dropdown-item" data-action="edit" data-lead-id="<?php echo esc_attr($lead->id); ?>">
                                            <i class="fas fa-edit hph-mr-xs"></i>
                                            <?php _e('Edit Lead', 'happy-place-theme'); ?>
                                        </a>
                                        <a href="#" class="hph-dropdown-item" data-action="add-note" data-lead-id="<?php echo esc_attr($lead->id); ?>">
                                            <i class="fas fa-sticky-note hph-mr-xs"></i>
                                            <?php _e('Add Note', 'happy-place-theme'); ?>
                                        </a>
                                        <a href="#" class="hph-dropdown-item" data-action="change-status" data-lead-id="<?php echo esc_attr($lead->id); ?>">
                                            <i class="fas fa-exchange-alt hph-mr-xs"></i>
                                            <?php _e('Change Status', 'happy-place-theme'); ?>
                                        </a>
                                        <a href="#" class="hph-dropdown-item" data-action="assign-agent" data-lead-id="<?php echo esc_attr($lead->id); ?>">
                                            <i class="fas fa-user-tag hph-mr-xs"></i>
                                            <?php _e('Assign to Agent', 'happy-place-theme'); ?>
                                        </a>
                                        <?php if ($is_admin): ?>
                                        <div class="hph-dropdown-divider"></div>
                                        <a href="#" class="hph-dropdown-item hph-text-red-600" data-action="delete" data-lead-id="<?php echo esc_attr($lead->id); ?>">
                                            <i class="fas fa-trash hph-mr-xs"></i>
                                            <?php _e('Delete Lead', 'happy-place-theme'); ?>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Lead Content -->
                        <div class="hph-lead-content">
                            
                            <!-- Lead Metadata -->
                            <div class="hph-grid hph-grid-cols-1 md:hph-grid-cols-2 lg:hph-grid-cols-4 hph-gap-md hph-mb-md">
                                
                                <div class="hph-meta-item">
                                    <div class="hph-flex hph-items-center hph-gap-xs hph-text-sm hph-text-gray-600">
                                        <i class="fas fa-globe hph-text-gray-400"></i>
                                        <span class="hph-font-medium"><?php _e('Source:', 'happy-place-theme'); ?></span>
                                        <span><?php echo esc_html(ucfirst($lead_source)); ?></span>
                                    </div>
                                </div>
                                
                                <?php if ($lead_score > 0): ?>
                                <div class="hph-meta-item">
                                    <div class="hph-flex hph-items-center hph-gap-xs hph-text-sm hph-text-gray-600">
                                        <i class="fas fa-star hph-text-gray-400"></i>
                                        <span class="hph-font-medium"><?php _e('Score:', 'happy-place-theme'); ?></span>
                                        <span class="hph-font-semibold hph-text-blue-600"><?php echo intval($lead_score); ?></span>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="hph-meta-item">
                                    <div class="hph-flex hph-items-center hph-gap-xs hph-text-sm hph-text-gray-600">
                                        <i class="fas fa-calendar hph-text-gray-400"></i>
                                        <span class="hph-font-medium"><?php _e('Created:', 'happy-place-theme'); ?></span>
                                        <span><?php echo human_time_diff(strtotime($created_at), current_time('timestamp')) . ' ago'; ?></span>
                                    </div>
                                </div>
                                
                                <?php if ($last_contacted): ?>
                                <div class="hph-meta-item">
                                    <div class="hph-flex hph-items-center hph-gap-xs hph-text-sm hph-text-gray-600">
                                        <i class="fas fa-comment hph-text-gray-400"></i>
                                        <span class="hph-font-medium"><?php _e('Last Contact:', 'happy-place-theme'); ?></span>
                                        <span><?php echo human_time_diff(strtotime($last_contacted), current_time('timestamp')) . ' ago'; ?></span>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- FollowUp Boss Information -->
                            <?php if ($followup_boss_data): ?>
                                <div class="hph-meta-item">
                                    <div class="hph-flex hph-items-center hph-gap-xs hph-text-sm hph-text-blue-600">
                                        <i class="fas fa-external-link-alt hph-text-blue-500"></i>
                                        <span class="hph-font-medium"><?php _e('FollowUp Boss:', 'happy-place-theme'); ?></span>
                                        <span class="hph-fub-status">
                                            <?php 
                                            $fub_status = $followup_boss_data['stage'] ?? $followup_boss_data['status'] ?? 'Synced';
                                            echo esc_html($fub_status);
                                            ?>
                                        </span>
                                        <?php if (!empty($followup_boss_data['lastActivityAt'])): ?>
                                            <span class="hph-text-gray-500">
                                                (<?php echo human_time_diff(strtotime($followup_boss_data['lastActivityAt']), current_time('timestamp')) . ' ago'; ?>)
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Interested Property -->
                            <?php if ($listing_title): ?>
                                <div class="hph-bg-blue-50 hph-border hph-border-blue-200 hph-rounded-md hph-p-sm hph-mb-md">
                                    <div class="hph-flex hph-items-center hph-gap-xs hph-text-sm">
                                        <i class="fas fa-home hph-text-blue-600"></i>
                                        <span class="hph-font-medium hph-text-blue-800"><?php _e('Interested in:', 'happy-place-theme'); ?></span>
                                        <a href="<?php echo get_permalink($listing_id); ?>" 
                                           target="_blank" 
                                           class="hph-text-blue-700 hph-hover-text-blue-900 hph-underline">
                                            <?php echo esc_html($listing_title); ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Lead Message -->
                            <?php if ($lead->message): ?>
                                <div class="hph-lead-message hph-bg-gray-50 hph-border hph-border-gray-200 hph-rounded-md hph-p-sm">
                                    <div class="hph-text-sm hph-text-gray-700">
                                        <span class="hph-font-medium"><?php _e('Message:', 'happy-place-theme'); ?></span>
                                        <span class="hph-italic">"<?php echo esc_html(wp_trim_words($lead->message, 20)); ?>"</span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Lead Actions Footer -->
                        <div class="hph-flex hph-flex-row hph-justify-between hph-items-center hph-mt-md hph-pt-md hph-border-t hph-border-gray-200">
                            <div class="hph-lead-priority">
                                <?php if ($lead_status === 'hot'): ?>
                                    <span class="hph-inline-flex hph-items-center hph-gap-xs hph-px-2 hph-py-1 hph-bg-red-100 hph-text-red-800 hph-text-xs hph-font-medium hph-rounded-full">
                                        <i class="fas fa-fire"></i>
                                        <?php _e('High Priority', 'happy-place-theme'); ?>
                                    </span>
                                <?php elseif ($lead_status === 'new'): ?>
                                    <span class="hph-inline-flex hph-items-center hph-gap-xs hph-px-2 hph-py-1 hph-bg-blue-100 hph-text-blue-800 hph-text-xs hph-font-medium hph-rounded-full">
                                        <i class="fas fa-star"></i>
                                        <?php _e('New Lead', 'happy-place-theme'); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="hph-flex hph-gap-xs">
                                <?php if ($lead_phone): ?>
                                    <button class="hph-btn hph-btn-sm hph-btn-outline" 
                                            data-action="call" 
                                            data-phone="<?php echo esc_attr($lead_phone); ?>"
                                            title="<?php _e('Call Lead', 'happy-place-theme'); ?>">
                                        <i class="fas fa-phone"></i>
                                        <?php _e('Call', 'happy-place-theme'); ?>
                                    </button>
                                <?php endif; ?>
                                
                                <button class="hph-btn hph-btn-sm hph-btn-outline" 
                                        data-action="email" 
                                        data-email="<?php echo esc_attr($lead_email); ?>"
                                        title="<?php _e('Email Lead', 'happy-place-theme'); ?>">
                                    <i class="fas fa-envelope"></i>
                                    <?php _e('Email', 'happy-place-theme'); ?>
                                </button>
                                
                                <button class="hph-btn hph-btn-sm hph-btn-primary" 
                                        data-action="contact" 
                                        data-lead-id="<?php echo esc_attr($lead->id); ?>"
                                        title="<?php _e('Contact Lead', 'happy-place-theme'); ?>">
                                    <i class="fas fa-comment"></i>
                                    <?php _e('Contact', 'happy-place-theme'); ?>
                                </button>
                                
                                <?php if (!empty($followup_boss_url)): ?>
                                <a href="<?php echo esc_url($followup_boss_url); ?>" 
                                   target="_blank" 
                                   class="hph-btn hph-btn-sm hph-btn-outline"
                                   title="<?php _e('Open in FollowUp Boss', 'happy-place-theme'); ?>">
                                    <i class="fas fa-external-link-alt"></i>
                                    <?php _e('Open in FUB', 'happy-place-theme'); ?>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php 
            $total_pages = ceil($total_leads / $per_page);
            if ($total_pages > 1): 
            ?>
                <div class="hph-pagination hph-mt-lg">
                    <div class="hph-flex hph-justify-between hph-items-center hph-bg-white hph-border hph-border-gray-200 hph-rounded-lg hph-p-md">
                        <div class="hph-text-sm hph-text-gray-600">
                            <?php 
                            $showing_from = (($page - 1) * $per_page) + 1;
                            $showing_to = min($page * $per_page, $total_leads);
                            printf(
                                __('Showing %d to %d of %d leads', 'happy-place-theme'),
                                $showing_from,
                                $showing_to,
                                $total_leads
                            );
                            ?>
                        </div>
                        
                        <div class="hph-flex hph-gap-xs">
                            <?php if ($page > 1): ?>
                                <a href="?section=leads&paged=<?php echo ($page - 1); ?><?php echo $status_filter !== 'all' ? '&lead_status=' . $status_filter : ''; ?><?php echo $search_query ? '&leads_search=' . urlencode($search_query) : ''; ?><?php echo $source_filter ? '&lead_source=' . urlencode($source_filter) : ''; ?>" 
                                   class="hph-btn hph-btn-outline hph-btn-sm">
                                    <i class="fas fa-chevron-left hph-mr-xs"></i>
                                    <?php _e('Previous', 'happy-place-theme'); ?>
                                </a>
                            <?php endif; ?>
                            
                            <span class="hph-px-md hph-py-2 hph-text-sm hph-text-gray-600">
                                <?php printf(__('Page %d of %d', 'happy-place-theme'), $page, $total_pages); ?>
                            </span>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?section=leads&paged=<?php echo ($page + 1); ?><?php echo $status_filter !== 'all' ? '&lead_status=' . $status_filter : ''; ?><?php echo $search_query ? '&leads_search=' . urlencode($search_query) : ''; ?><?php echo $source_filter ? '&lead_source=' . urlencode($source_filter) : ''; ?>" 
                                   class="hph-btn hph-btn-outline hph-btn-sm">
                                    <?php _e('Next', 'happy-place-theme'); ?>
                                    <i class="fas fa-chevron-right hph-ml-xs"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- Empty State -->
            <div class="hph-empty-state hph-bg-white hph-border hph-border-gray-200 hph-rounded-lg hph-p-xl hph-text-center">
                <div class="hph-empty-icon hph-mb-md">
                    <i class="fas fa-users hph-text-4xl hph-text-gray-400"></i>
                </div>
                <h3 class="hph-text-lg hph-font-semibold hph-text-gray-900 hph-mb-sm">
                    <?php if ($status_filter === 'all'): ?>
                        <?php _e('No Leads Found', 'happy-place-theme'); ?>
                    <?php else: ?>
                        <?php printf(__('No %s Leads', 'happy-place-theme'), ucfirst($status_filter)); ?>
                    <?php endif; ?>
                </h3>
                <p class="hph-text-gray-600 hph-mb-lg">
                    <?php if ($status_filter === 'all'): ?>
                        <?php _e('Start building your client base by adding leads from inquiries and referrals.', 'happy-place-theme'); ?>
                    <?php else: ?>
                        <?php printf(__('You don\'t have any %s leads at the moment.', 'happy-place-theme'), $status_filter); ?>
                    <?php endif; ?>
                </p>
                <div class="hph-empty-actions">
                    <?php if ($status_filter === 'all'): ?>
                        <button class="hph-btn hph-btn-primary" id="addFirstLeadBtn">
                            <i class="fas fa-user-plus hph-mr-xs"></i>
                            <?php _e('Add Your First Lead', 'happy-place-theme'); ?>
                        </button>
                    <?php else: ?>
                        <a href="?section=leads" class="hph-btn hph-btn-outline">
                            <?php _e('View All Leads', 'happy-place-theme'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>


<div id="leadsPage" class="leads-page">
    <!-- This ID is required for the dashboard-leads.js controller -->
</div>

<script>
// Leads management functionality
// Ensure jQuery is available
if (typeof jQuery === 'undefined') {
    console.error('jQuery is not loaded! Lead management functionality may not work.');
    return;
}

jQuery(document).ready(function($) {
    console.log('Leads management: Starting initialization');
    console.log('Available variables:', {
        hphDashboard: typeof hphDashboard !== 'undefined' ? hphDashboard : 'not defined',
        ajaxurl: typeof ajaxurl !== 'undefined' ? ajaxurl : 'not defined'
    });
    
    // Initialize lead management functionality
    console.log('Leads management: Initialization complete');
    
    // Filter handling
    $('#applyLeadFilters').on('click', function() {
        const search = $('#leadsSearch').val();
        const source = $('#leadSourceFilter').val();
        
        let url = '?section=leads';
        if (search) url += '&leads_search=' + encodeURIComponent(search);
        if (source) url += '&lead_source=' + encodeURIComponent(source);
        
        window.location.href = url;
    });
    
    $('#clearLeadFilters').on('click', function() {
        window.location.href = '?section=leads';
    });
    
    // Search on Enter key
    $('#leadsSearch').on('keypress', function(e) {
        if (e.which === 13) {
            $('#applyLeadFilters').click();
        }
    });
    
    // Dropdown toggles - Fix for click-based dropdowns
    $('.hph-dropdown-toggle').on('click', function(e) {
        e.stopPropagation();
        const dropdown = $(this).next('.hph-dropdown-menu');
        
        // Close all other dropdowns first
        $('.hph-dropdown-menu').not(dropdown).removeClass('hph-dropdown-active').hide();
        
        // Toggle this dropdown
        if (dropdown.hasClass('hph-dropdown-active')) {
            dropdown.removeClass('hph-dropdown-active').hide();
        } else {
            dropdown.addClass('hph-dropdown-active').show();
        }
    });
    
    // Close dropdowns when clicking outside
    $(document).on('click', function() {
        $('.hph-dropdown-menu').removeClass('hph-dropdown-active').hide();
    });
    
    // Prevent dropdown from closing when clicking inside
    $('.hph-dropdown-menu').on('click', function(e) {
        e.stopPropagation();
    });
    
    // Action handlers
    $('.hph-dropdown-item').on('click', function(e) {
        e.preventDefault();
        const action = $(this).data('action');
        const leadId = $(this).data('lead-id');
        
        // Handle different actions
        switch(action) {
            case 'edit':
                // TODO: Open edit lead modal
                console.log('Edit lead:', leadId);
                break;
            case 'add-note':
                // TODO: Open add note modal
                console.log('Add note to lead:', leadId);
                break;
            case 'change-status':
                // TODO: Open status change modal
                console.log('Change status for lead:', leadId);
                break;
            case 'assign-agent':
                showAssignAgentModal(leadId);
                break;
            case 'delete':
                if (confirm('<?php _e('Are you sure you want to delete this lead?', 'happy-place-theme'); ?>')) {
                    deleteLead(leadId);
                }
                break;
        }
    });
    
    // Quick action handlers
    $('[data-action="call"]').on('click', function() {
        const phone = $(this).data('phone');
        if (phone) {
            window.location.href = 'tel:' + phone;
        }
    });
    
    $('[data-action="email"]').on('click', function() {
        const email = $(this).data('email');
        if (email) {
            window.location.href = 'mailto:' + email;
        }
    });
    
    // Delete lead function
    function deleteLead(leadId) {
        if (!leadId) {
            alert('Invalid lead ID');
            return;
        }
        
        // Show loading state
        const leadCard = $('[data-lead-id="' + leadId + '"]');
        leadCard.addClass('hph-loading');
        
        $.ajax({
            url: hphDashboard.ajaxurl || ajaxurl,
            type: 'POST',
            data: {
                action: 'delete_lead',
                lead_id: leadId,
                nonce: hphDashboard.nonce || '<?php echo wp_create_nonce('hph_dashboard_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    // Remove the lead card with animation
                    leadCard.fadeOut(300, function() {
                        $(this).remove();
                        // Update counts if needed
                        updateLeadCounts();
                    });
                    
                    // Show success message
                    showNotification('Lead deleted successfully', 'success');
                } else {
                    leadCard.removeClass('hph-loading');
                    showNotification(response.data || 'Failed to delete lead', 'error');
                }
            },
            error: function() {
                leadCard.removeClass('hph-loading');
                showNotification('Error deleting lead', 'error');
            }
        });
    }
    
    // Update lead counts after deletion
    function updateLeadCounts() {
        const remainingLeads = $('.hph-lead-card').length;
        if (remainingLeads === 0) {
            // Show empty state
            $('.hph-leads-list').html(
                '<div class="hph-empty-state hph-bg-white hph-border hph-border-gray-200 hph-rounded-lg hph-p-xl hph-text-center">' +
                '<div class="hph-empty-icon hph-mb-md"><i class="fas fa-users hph-text-4xl hph-text-gray-400"></i></div>' +
                '<h3 class="hph-text-lg hph-font-semibold hph-text-gray-900 hph-mb-sm">No Leads Found</h3>' +
                '<p class="hph-text-gray-600 hph-mb-lg">Start building your client base by adding leads from inquiries and referrals.</p>' +
                '</div>'
            );
        }
    }
    
    // Show notification function
    function showNotification(message, type) {
        const notification = $('<div class="hph-notification hph-notification-' + type + '">' + message + '</div>');
        $('body').append(notification);
        
        setTimeout(function() {
            notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }
    
    // Show assign agent modal
    function showAssignAgentModal(leadId) {
        const modal = $(`
            <div class="hph-modal-overlay" id="assignAgentModal">
                <div class="hph-modal-content">
                    <div class="hph-modal-header">
                        <h3><?php _e('Assign Lead to Agent', 'happy-place-theme'); ?></h3>
                        <button class="hph-modal-close">&times;</button>
                    </div>
                    <div class="hph-modal-body">
                        <div class="hph-form-group">
                            <label for="assignAgent"><?php _e('Select Agent:', 'happy-place-theme'); ?></label>
                            <select id="assignAgent" class="hph-form-select">
                                <option value=""><?php _e('-- Select Agent --', 'happy-place-theme'); ?></option>
                                <?php 
                                $agents = get_users(['role__in' => ['agent', 'administrator']]);
                                foreach ($agents as $agent): 
                                ?>
                                <option value="<?php echo $agent->ID; ?>"><?php echo esc_html($agent->display_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="hph-modal-footer">
                        <button class="hph-btn hph-btn-outline" id="cancelAssign"><?php _e('Cancel', 'happy-place-theme'); ?></button>
                        <button class="hph-btn hph-btn-primary" id="confirmAssign"><?php _e('Assign Lead', 'happy-place-theme'); ?></button>
                    </div>
                </div>
            </div>
        `);
        
        $('body').append(modal);
        
        // Handle modal interactions
        $('#cancelAssign, .hph-modal-close, .hph-modal-overlay').on('click', function(e) {
            if (e.target === this) {
                modal.remove();
            }
        });
        
        $('#confirmAssign').on('click', function() {
            const agentId = $('#assignAgent').val();
            if (!agentId) {
                showNotification('Please select an agent', 'error');
                return;
            }
            
            assignLeadToAgent(leadId, agentId);
            modal.remove();
        });
    }
    
    // Assign lead to agent
    function assignLeadToAgent(leadId, agentId) {
        $.ajax({
            url: hphDashboard.ajaxurl || ajaxurl,
            type: 'POST',
            data: {
                action: 'assign_lead_to_agent',
                lead_id: leadId,
                agent_id: agentId,
                nonce: hphDashboard.nonce || '<?php echo wp_create_nonce('hph_dashboard_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    showNotification('Lead assigned successfully', 'success');
                    // Refresh the page to update the display
                    location.reload();
                } else {
                    showNotification(response.data || 'Failed to assign lead', 'error');
                }
            },
            error: function() {
                showNotification('Error assigning lead', 'error');
            }
        });
    }
    
    console.log('Leads management initialized');
});
</script>
