<?php
/**
 * Leads Management Section
 * Agent dashboard section for managing customer leads
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

// Get leads filter
$status_filter = isset($_GET['lead_status']) ? sanitize_text_field($_GET['lead_status']) : 'all';

// Get agent's leads
$leads_args = [
    'post_type' => 'lead',
    'post_status' => 'publish',
    'author' => $current_user->ID,
    'posts_per_page' => 20,
    'meta_key' => '_edit_last',
    'orderby' => 'meta_value_num date',
    'order' => 'DESC'
];

// Filter by status if specified
if ($status_filter !== 'all') {
    $leads_args['meta_query'] = [
        [
            'key' => 'lead_status',
            'value' => $status_filter
        ]
    ];
}

$leads_query = new WP_Query($leads_args);

// Get status counts
$status_counts = [];
$statuses = ['hot', 'warm', 'cold', 'converted'];
foreach ($statuses as $status) {
    $count_query = new WP_Query([
        'post_type' => 'lead',
        'post_status' => 'publish',
        'author' => $current_user->ID,
        'meta_query' => [
            [
                'key' => 'lead_status',
                'value' => $status
            ]
        ],
        'posts_per_page' => -1
    ]);
    $status_counts[$status] = $count_query->found_posts;
}
?>

<div class="hph-leads-management">
    
    <!-- Section Header -->
    <div class="hph-section-header">
        <div class="hph-header-content">
            <h2 class="hph-section-title"><?php _e('Lead Management', 'happy-place-theme'); ?></h2>
            <p class="hph-section-description">
                <?php _e('Manage your customer leads, track interactions, and convert prospects into clients.', 'happy-place-theme'); ?>
            </p>
        </div>
        
        <div class="hph-header-actions">
            <button class="hph-btn hph-btn-primary" id="addNewLeadBtn">
                <span class="hph-btn-icon hph-icon-user-plus"></span>
                <span class="hph-btn-text"><?php _e('Add New Lead', 'happy-place-theme'); ?></span>
            </button>
            <button class="hph-btn hph-btn-outline" id="importLeadsBtn">
                <span class="hph-btn-icon hph-icon-upload"></span>
                <span class="hph-btn-text"><?php _e('Import Leads', 'happy-place-theme'); ?></span>
            </button>
        </div>
    </div>

    <!-- Status Filter Tabs -->
    <div class="hph-filter-tabs">
        <div class="hph-tabs-container">
            <a href="?section=leads&lead_status=all" class="hph-tab <?php echo $status_filter === 'all' ? 'active' : ''; ?>">
                <span class="hph-tab-text"><?php _e('All Leads', 'happy-place-theme'); ?></span>
                <span class="hph-tab-count"><?php echo $leads_query->found_posts; ?></span>
            </a>
            
            <a href="?section=leads&lead_status=hot" class="hph-tab <?php echo $status_filter === 'hot' ? 'active' : ''; ?>">
                <span class="hph-tab-text"><?php _e('Hot', 'happy-place-theme'); ?></span>
                <span class="hph-tab-count hph-count-hot"><?php echo $status_counts['hot']; ?></span>
            </a>
            
            <a href="?section=leads&lead_status=warm" class="hph-tab <?php echo $status_filter === 'warm' ? 'active' : ''; ?>">
                <span class="hph-tab-text"><?php _e('Warm', 'happy-place-theme'); ?></span>
                <span class="hph-tab-count hph-count-warm"><?php echo $status_counts['warm']; ?></span>
            </a>
            
            <a href="?section=leads&lead_status=cold" class="hph-tab <?php echo $status_filter === 'cold' ? 'active' : ''; ?>">
                <span class="hph-tab-text"><?php _e('Cold', 'happy-place-theme'); ?></span>
                <span class="hph-tab-count hph-count-cold"><?php echo $status_counts['cold']; ?></span>
            </a>
            
            <a href="?section=leads&lead_status=converted" class="hph-tab <?php echo $status_filter === 'converted' ? 'active' : ''; ?>">
                <span class="hph-tab-text"><?php _e('Converted', 'happy-place-theme'); ?></span>
                <span class="hph-tab-count hph-count-success"><?php echo $status_counts['converted']; ?></span>
            </a>
        </div>
        
        <!-- Additional Controls -->
        <div class="hph-filter-controls">
            <div class="hph-search-box">
                <input type="search" class="hph-search-input" placeholder="<?php _e('Search leads...', 'happy-place-theme'); ?>" id="leadsSearch">
                <span class="hph-search-icon hph-icon-search"></span>
            </div>
            
            <div class="hph-filter-dropdown">
                <select class="hph-select" id="leadSourceFilter">
                    <option value=""><?php _e('All Sources', 'happy-place-theme'); ?></option>
                    <option value="website"><?php _e('Website', 'happy-place-theme'); ?></option>
                    <option value="referral"><?php _e('Referral', 'happy-place-theme'); ?></option>
                    <option value="social"><?php _e('Social Media', 'happy-place-theme'); ?></option>
                    <option value="advertisement"><?php _e('Advertisement', 'happy-place-theme'); ?></option>
                    <option value="open_house"><?php _e('Open House', 'happy-place-theme'); ?></option>
                </select>
            </div>
        </div>
    </div>

    <!-- Leads List -->
    <div class="hph-leads-container" id="leadsContainer">
        <?php if ($leads_query->have_posts()): ?>
            <div class="hph-leads-list" id="leadsList">
                <?php while ($leads_query->have_posts()): $leads_query->the_post(); ?>
                    <?php
                    $lead_id = get_the_ID();
                    $lead_status = get_field('lead_status', $lead_id) ?: 'cold';
                    $lead_email = get_field('lead_email', $lead_id);
                    $lead_phone = get_field('lead_phone', $lead_id);
                    $lead_source = get_field('lead_source', $lead_id);
                    $interested_listing = get_field('interested_listing', $lead_id);
                    $budget_min = get_field('budget_min', $lead_id);
                    $budget_max = get_field('budget_max', $lead_id);
                    $last_contact = get_field('last_contact_date', $lead_id);
                    $next_followup = get_field('next_followup_date', $lead_id);
                    $notes = get_field('lead_notes', $lead_id);
                    
                    // Get interested listing details if available
                    $listing_title = '';
                    if ($interested_listing) {
                        $listing_title = get_the_title($interested_listing);
                    }
                    ?>
                    
                    <div class="hph-lead-card" data-lead-id="<?php echo $lead_id; ?>" data-status="<?php echo $lead_status; ?>">
                        
                        <!-- Lead Header -->
                        <div class="hph-lead-header">
                            <div class="hph-lead-info">
                                <div class="hph-lead-avatar">
                                    <?php echo get_avatar($lead_email ?: 'unknown@email.com', 48, '', get_the_title($lead_id), ['class' => 'hph-avatar-image']); ?>
                                </div>
                                
                                <div class="hph-lead-details">
                                    <h3 class="hph-lead-name"><?php echo esc_html(get_the_title($lead_id)); ?></h3>
                                    <div class="hph-lead-contact">
                                        <?php if ($lead_email): ?>
                                            <a href="mailto:<?php echo esc_attr($lead_email); ?>" class="hph-contact-link">
                                                <span class="hph-icon-mail"></span>
                                                <?php echo esc_html($lead_email); ?>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($lead_phone): ?>
                                            <a href="tel:<?php echo esc_attr($lead_phone); ?>" class="hph-contact-link">
                                                <span class="hph-icon-phone"></span>
                                                <?php echo esc_html($lead_phone); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="hph-lead-status-actions">
                                <div class="hph-lead-status hph-status-<?php echo esc_attr($lead_status); ?>">
                                    <span class="hph-status-dot"></span>
                                    <span class="hph-status-text"><?php echo ucfirst($lead_status); ?></span>
                                </div>
                                
                                <div class="hph-lead-actions">
                                    <button class="hph-btn hph-btn-sm hph-btn-primary" data-action="contact" data-lead-id="<?php echo $lead_id; ?>">
                                        <span class="hph-btn-icon hph-icon-mail"></span>
                                        <span class="hph-btn-text"><?php _e('Contact', 'happy-place-theme'); ?></span>
                                    </button>
                                    
                                    <div class="hph-dropdown">
                                        <button class="hph-dropdown-toggle hph-btn hph-btn-sm hph-btn-ghost" aria-label="<?php _e('More Actions', 'happy-place-theme'); ?>">
                                            <span class="hph-icon-more-vertical"></span>
                                        </button>
                                        <div class="hph-dropdown-menu">
                                            <a href="#" class="hph-dropdown-item" data-action="edit" data-lead-id="<?php echo $lead_id; ?>">
                                                <span class="hph-icon-edit"></span>
                                                <?php _e('Edit Lead', 'happy-place-theme'); ?>
                                            </a>
                                            <a href="#" class="hph-dropdown-item" data-action="add-note" data-lead-id="<?php echo $lead_id; ?>">
                                                <span class="hph-icon-file-text"></span>
                                                <?php _e('Add Note', 'happy-place-theme'); ?>
                                            </a>
                                            <a href="#" class="hph-dropdown-item" data-action="schedule" data-lead-id="<?php echo $lead_id; ?>">
                                                <span class="hph-icon-calendar"></span>
                                                <?php _e('Schedule Follow-up', 'happy-place-theme'); ?>
                                            </a>
                                            <div class="hph-dropdown-divider"></div>
                                            <a href="#" class="hph-dropdown-item" data-action="convert" data-lead-id="<?php echo $lead_id; ?>">
                                                <span class="hph-icon-check-circle"></span>
                                                <?php _e('Mark as Converted', 'happy-place-theme'); ?>
                                            </a>
                                            <a href="#" class="hph-dropdown-item hph-danger" data-action="archive" data-lead-id="<?php echo $lead_id; ?>">
                                                <span class="hph-icon-archive"></span>
                                                <?php _e('Archive', 'happy-place-theme'); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Lead Content -->
                        <div class="hph-lead-content">
                            
                            <!-- Lead Metadata -->
                            <div class="hph-lead-meta">
                                <div class="hph-meta-grid">
                                    
                                    <?php if ($lead_source): ?>
                                        <div class="hph-meta-item">
                                            <span class="hph-meta-icon hph-icon-globe"></span>
                                            <div class="hph-meta-content">
                                                <span class="hph-meta-label"><?php _e('Source', 'happy-place-theme'); ?></span>
                                                <span class="hph-meta-value"><?php echo ucfirst($lead_source); ?></span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($budget_min || $budget_max): ?>
                                        <div class="hph-meta-item">
                                            <span class="hph-meta-icon hph-icon-dollar-sign"></span>
                                            <div class="hph-meta-content">
                                                <span class="hph-meta-label"><?php _e('Budget', 'happy-place-theme'); ?></span>
                                                <span class="hph-meta-value">
                                                    <?php if ($budget_min && $budget_max): ?>
                                                        $<?php echo number_format($budget_min); ?> - $<?php echo number_format($budget_max); ?>
                                                    <?php elseif ($budget_min): ?>
                                                        $<?php echo number_format($budget_min); ?>+
                                                    <?php elseif ($budget_max): ?>
                                                        Up to $<?php echo number_format($budget_max); ?>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($interested_listing): ?>
                                        <div class="hph-meta-item">
                                            <span class="hph-meta-icon hph-icon-home"></span>
                                            <div class="hph-meta-content">
                                                <span class="hph-meta-label"><?php _e('Interested Property', 'happy-place-theme'); ?></span>
                                                <a href="<?php echo get_permalink($interested_listing); ?>" class="hph-meta-value hph-meta-link" target="_blank">
                                                    <?php echo esc_html($listing_title); ?>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="hph-meta-item">
                                        <span class="hph-meta-icon hph-icon-calendar"></span>
                                        <div class="hph-meta-content">
                                            <span class="hph-meta-label"><?php _e('Created', 'happy-place-theme'); ?></span>
                                            <span class="hph-meta-value"><?php echo get_the_date('M j, Y', $lead_id); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Last Contact & Next Follow-up -->
                            <div class="hph-lead-timeline">
                                <?php if ($last_contact): ?>
                                    <div class="hph-timeline-item">
                                        <span class="hph-timeline-icon hph-icon-message-circle"></span>
                                        <div class="hph-timeline-content">
                                            <span class="hph-timeline-label"><?php _e('Last Contact', 'happy-place-theme'); ?></span>
                                            <span class="hph-timeline-date">
                                                <?php echo human_time_diff(strtotime($last_contact), current_time('timestamp')) . ' ago'; ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($next_followup): ?>
                                    <div class="hph-timeline-item">
                                        <span class="hph-timeline-icon hph-icon-clock"></span>
                                        <div class="hph-timeline-content">
                                            <span class="hph-timeline-label"><?php _e('Next Follow-up', 'happy-place-theme'); ?></span>
                                            <span class="hph-timeline-date <?php echo strtotime($next_followup) < current_time('timestamp') ? 'hph-overdue' : ''; ?>">
                                                <?php 
                                                $time_diff = human_time_diff(current_time('timestamp'), strtotime($next_followup));
                                                if (strtotime($next_followup) < current_time('timestamp')) {
                                                    printf(__('%s overdue', 'happy-place-theme'), $time_diff);
                                                } else {
                                                    printf(__('in %s', 'happy-place-theme'), $time_diff);
                                                }
                                                ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Lead Notes Preview -->
                            <?php if ($notes): ?>
                                <div class="hph-lead-notes">
                                    <div class="hph-notes-header">
                                        <span class="hph-notes-icon hph-icon-file-text"></span>
                                        <span class="hph-notes-label"><?php _e('Latest Note', 'happy-place-theme'); ?></span>
                                    </div>
                                    <div class="hph-notes-content">
                                        <?php echo wp_trim_words($notes, 20, '...'); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Lead Footer -->
                        <div class="hph-lead-footer">
                            <div class="hph-lead-priority">
                                <?php if ($lead_status === 'hot'): ?>
                                    <span class="hph-priority-indicator hph-priority-high">
                                        <span class="hph-icon-fire"></span>
                                        <?php _e('High Priority', 'happy-place-theme'); ?>
                                    </span>
                                <?php elseif ($next_followup && strtotime($next_followup) < current_time('timestamp')): ?>
                                    <span class="hph-priority-indicator hph-priority-urgent">
                                        <span class="hph-icon-alert-circle"></span>
                                        <?php _e('Follow-up Overdue', 'happy-place-theme'); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="hph-lead-quick-actions">
                                <button class="hph-quick-btn" data-action="call" data-phone="<?php echo esc_attr($lead_phone); ?>" title="<?php _e('Call', 'happy-place-theme'); ?>">
                                    <span class="hph-icon-phone"></span>
                                </button>
                                <button class="hph-quick-btn" data-action="email" data-email="<?php echo esc_attr($lead_email); ?>" title="<?php _e('Email', 'happy-place-theme'); ?>">
                                    <span class="hph-icon-mail"></span>
                                </button>
                                <button class="hph-quick-btn" data-action="note" data-lead-id="<?php echo $lead_id; ?>" title="<?php _e('Add Note', 'happy-place-theme'); ?>">
                                    <span class="hph-icon-edit"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($leads_query->max_num_pages > 1): ?>
                <div class="hph-pagination">
                    <?php
                    echo paginate_links([
                        'total' => $leads_query->max_num_pages,
                        'current' => max(1, get_query_var('paged')),
                        'format' => '?paged=%#%',
                        'prev_text' => '<span class="hph-icon-chevron-left"></span> ' . __('Previous', 'happy-place-theme'),
                        'next_text' => __('Next', 'happy-place-theme') . ' <span class="hph-icon-chevron-right"></span>',
                        'type' => 'list',
                        'end_size' => 2,
                        'mid_size' => 1
                    ]);
                    ?>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- Empty State -->
            <div class="hph-empty-state">
                <div class="hph-empty-icon">
                    <span class="hph-icon-users"></span>
                </div>
                <h3 class="hph-empty-title">
                    <?php if ($status_filter === 'all'): ?>
                        <?php _e('No Leads Yet', 'happy-place-theme'); ?>
                    <?php else: ?>
                        <?php printf(__('No %s Leads', 'happy-place-theme'), ucfirst($status_filter)); ?>
                    <?php endif; ?>
                </h3>
                <p class="hph-empty-description">
                    <?php if ($status_filter === 'all'): ?>
                        <?php _e('Start building your client base by adding leads from inquiries and referrals.', 'happy-place-theme'); ?>
                    <?php else: ?>
                        <?php printf(__('You don\'t have any %s leads at the moment.', 'happy-place-theme'), $status_filter); ?>
                    <?php endif; ?>
                </p>
                <div class="hph-empty-actions">
                    <?php if ($status_filter === 'all'): ?>
                        <button class="hph-btn hph-btn-primary" id="addFirstLeadBtn">
                            <span class="hph-btn-icon hph-icon-user-plus"></span>
                            <span class="hph-btn-text"><?php _e('Add Your First Lead', 'happy-place-theme'); ?></span>
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