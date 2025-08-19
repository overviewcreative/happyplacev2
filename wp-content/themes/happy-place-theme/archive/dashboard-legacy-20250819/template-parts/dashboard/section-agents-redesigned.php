<?php
/**
 * Agents Management Section - Redesigned
 * 
 * Modern agent management interface with add/edit forms
 * Allows brokers to manage all agents in their organization
 */

// Get current action and agent ID
$action = get_query_var('dashboard_action', 'list');
$agent_id = get_query_var('dashboard_id', 0);

// Get all agents for the current user's permissions
$agents_query = new WP_Query([
    'post_type' => 'agent',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'meta_query' => [
        [
            'key' => 'office_name',
            'value' => 'Happy Place Realty', // Could be dynamic based on user's office
            'compare' => 'LIKE'
        ]
    ]
]);

$agents = $agents_query->posts;
$total_agents = count($agents);
$active_agents = count(array_filter($agents, function($agent) {
    return get_field('agent_status', $agent->ID) === 'active';
}));

?>

<div class="agents-management-redesigned">
    <?php if ($action === 'list'): ?>
        <!-- Agents Hero Section -->
        <div class="agents-hero">
            <div class="agents-hero-bg"></div>
            <div class="agents-hero-content">
                <div class="agents-hero-info">
                    <h1 class="agents-title">Agent Management</h1>
                    <p class="agents-subtitle">Manage your team of real estate professionals</p>
                    
                    <div class="agents-stats">
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $total_agents; ?></span>
                            <span class="stat-label">Total Agents</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $active_agents; ?></span>
                            <span class="stat-label">Active</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $total_agents - $active_agents; ?></span>
                            <span class="stat-label">Inactive</span>
                        </div>
                    </div>
                </div>
                
                <div class="agents-hero-actions">
                    <a href="<?php echo esc_url(add_query_arg(['dashboard_action' => 'add'], get_permalink())); ?>" 
                       class="add-agent-btn">
                        <span class="hph-icon-user-plus"></span>
                        Add New Agent
                    </a>
                </div>
            </div>
        </div>

        <!-- Search & Filter Controls -->
        <div class="agents-controls">
            <form class="agents-search-form" method="GET">
                <input type="hidden" name="dashboard_section" value="agents">
                
                <div class="search-controls">
                    <div class="search-input-group">
                        <span class="search-icon hph-icon-search"></span>
                        <input type="text" 
                               name="search" 
                               class="search-input" 
                               placeholder="Search agents by name, email, or license number..."
                               value="<?php echo esc_attr($_GET['search'] ?? ''); ?>">
                        <button type="submit" class="search-btn">Search</button>
                    </div>
                </div>
                
                <div class="filter-controls">
                    <div class="filter-group">
                        <label for="agent_status">Status</label>
                        <select name="agent_status" id="agent_status">
                            <option value="">All Statuses</option>
                            <option value="active" <?php selected($_GET['agent_status'] ?? '', 'active'); ?>>Active</option>
                            <option value="inactive" <?php selected($_GET['agent_status'] ?? '', 'inactive'); ?>>Inactive</option>
                            <option value="pending" <?php selected($_GET['agent_status'] ?? '', 'pending'); ?>>Pending</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="specialties">Specialty</label>
                        <select name="specialties" id="specialties">
                            <option value="">All Specialties</option>
                            <option value="buyer_agent">Buyer Agent</option>
                            <option value="listing_agent">Listing Agent</option>
                            <option value="luxury_homes">Luxury Homes</option>
                            <option value="first_time_buyers">First-Time Buyers</option>
                            <option value="commercial">Commercial</option>
                            <option value="investment">Investment</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="sort">Sort By</label>
                        <select name="sort" id="sort">
                            <option value="name" <?php selected($_GET['sort'] ?? '', 'name'); ?>>Name A-Z</option>
                            <option value="name_desc" <?php selected($_GET['sort'] ?? '', 'name_desc'); ?>>Name Z-A</option>
                            <option value="sales_volume" <?php selected($_GET['sort'] ?? '', 'sales_volume'); ?>>Sales Volume</option>
                            <option value="date_started" <?php selected($_GET['sort'] ?? '', 'date_started'); ?>>Start Date</option>
                            <option value="listings_count" <?php selected($_GET['sort'] ?? '', 'listings_count'); ?>>Active Listings</option>
                        </select>
                    </div>
                </div>
                
                <div class="view-controls">
                    <div class="bulk-actions">
                        <select name="bulk_action" id="bulk_action">
                            <option value="">Bulk Actions</option>
                            <option value="activate">Activate Selected</option>
                            <option value="deactivate">Deactivate Selected</option>
                            <option value="export">Export Selected</option>
                            <option value="delete">Delete Selected</option>
                        </select>
                        <button type="button" class="btn btn-secondary" id="apply-bulk-action">Apply</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Agents Grid -->
        <div class="agents-grid">
            <?php if (!empty($agents)): ?>
                <?php foreach ($agents as $agent): 
                    $agent_data = [
                        'first_name' => get_field('first_name', $agent->ID),
                        'last_name' => get_field('last_name', $agent->ID),
                        'title' => get_field('title', $agent->ID),
                        'email' => get_field('email', $agent->ID),
                        'phone' => get_field('phone', $agent->ID),
                        'agent_status' => get_field('agent_status', $agent->ID) ?: 'active',
                        'total_sales_volume' => get_field('total_sales_volume', $agent->ID),
                        'active_listings_count' => get_field('active_listings_count', $agent->ID),
                        'years_experience' => get_field('years_experience', $agent->ID),
                        'specialties' => get_field('specialties', $agent->ID) ?: [],
                        'profile_photo' => get_field('profile_photo', $agent->ID),
                        'license_number' => get_field('license_number', $agent->ID)
                    ];
                ?>
                    <div class="agent-card" data-agent-id="<?php echo esc_attr($agent->ID); ?>">
                        <div class="agent-card-header">
                            <div class="agent-avatar">
                                <?php if ($agent_data['profile_photo']): ?>
                                    <img src="<?php echo esc_url($agent_data['profile_photo']['sizes']['medium'] ?? $agent_data['profile_photo']['url']); ?>" 
                                         alt="<?php echo esc_attr($agent_data['first_name'] . ' ' . $agent_data['last_name']); ?>">
                                <?php else: ?>
                                    <div class="avatar-placeholder">
                                        <span class="hph-icon-user"></span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="status-indicator status-<?php echo esc_attr($agent_data['agent_status']); ?>"></div>
                            </div>
                            
                            <div class="selection-checkbox">
                                <input type="checkbox" name="selected_agents[]" value="<?php echo esc_attr($agent->ID); ?>">
                            </div>
                        </div>
                        
                        <div class="agent-card-content">
                            <div class="agent-header">
                                <h3 class="agent-name">
                                    <a href="<?php echo esc_url(add_query_arg(['dashboard_action' => 'edit', 'dashboard_id' => $agent->ID], get_permalink())); ?>">
                                        <?php echo esc_html($agent_data['first_name'] . ' ' . $agent_data['last_name']); ?>
                                    </a>
                                </h3>
                                <span class="agent-title"><?php echo esc_html($agent_data['title']); ?></span>
                            </div>
                            
                            <div class="agent-contact">
                                <div class="contact-item">
                                    <span class="hph-icon-envelope"></span>
                                    <span><?php echo esc_html($agent_data['email']); ?></span>
                                </div>
                                <div class="contact-item">
                                    <span class="hph-icon-phone"></span>
                                    <span><?php echo esc_html($agent_data['phone']); ?></span>
                                </div>
                                <?php if ($agent_data['license_number']): ?>
                                    <div class="contact-item">
                                        <span class="hph-icon-id-card"></span>
                                        <span><?php echo esc_html($agent_data['license_number']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="agent-performance">
                                <div class="performance-item">
                                    <span class="performance-value"><?php echo number_format($agent_data['total_sales_volume'] ?: 0); ?></span>
                                    <span class="performance-label">Sales Volume</span>
                                </div>
                                <div class="performance-item">
                                    <span class="performance-value"><?php echo intval($agent_data['active_listings_count'] ?: 0); ?></span>
                                    <span class="performance-label">Active Listings</span>
                                </div>
                                <div class="performance-item">
                                    <span class="performance-value"><?php echo intval($agent_data['years_experience'] ?: 0); ?></span>
                                    <span class="performance-label">Years Exp.</span>
                                </div>
                            </div>
                            
                            <?php if (!empty($agent_data['specialties'])): ?>
                                <div class="agent-specialties">
                                    <?php foreach (array_slice($agent_data['specialties'], 0, 3) as $specialty): ?>
                                        <span class="specialty-tag"><?php echo esc_html(str_replace('_', ' ', $specialty)); ?></span>
                                    <?php endforeach; ?>
                                    <?php if (count($agent_data['specialties']) > 3): ?>
                                        <span class="specialty-tag">+<?php echo count($agent_data['specialties']) - 3; ?> more</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="agent-card-footer">
                            <div class="agent-actions">
                                <a href="<?php echo esc_url(add_query_arg(['dashboard_action' => 'edit', 'dashboard_id' => $agent->ID], get_permalink())); ?>" 
                                   class="action-btn edit-btn" title="Edit Agent">
                                    <span class="hph-icon-edit"></span>
                                </a>
                                <a href="<?php echo esc_url(get_permalink($agent->ID)); ?>" 
                                   class="action-btn view-btn" title="View Profile">
                                    <span class="hph-icon-eye"></span>
                                </a>
                                <button type="button" 
                                        class="action-btn duplicate-btn" 
                                        title="Duplicate Agent"
                                        data-agent-id="<?php echo esc_attr($agent->ID); ?>">
                                    <span class="hph-icon-copy"></span>
                                </button>
                                <button type="button" 
                                        class="action-btn delete-btn" 
                                        title="Delete Agent"
                                        data-agent-id="<?php echo esc_attr($agent->ID); ?>">
                                    <span class="hph-icon-trash"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="agents-empty-state">
                    <div class="empty-state-icon">
                        <span class="hph-icon-users"></span>
                    </div>
                    <h3>No Agents Found</h3>
                    <p>Start building your team by adding your first agent to the system.</p>
                    <div class="empty-state-actions">
                        <a href="<?php echo esc_url(add_query_arg(['dashboard_action' => 'add'], get_permalink())); ?>" 
                           class="btn btn-primary btn-lg">
                            <span class="hph-icon-user-plus"></span>
                            Add First Agent
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    <?php elseif ($action === 'add' || $action === 'edit'): ?>
        <!-- Agent Add/Edit Form -->
        <?php 
        $editing = ($action === 'edit' && $agent_id);
        $agent_data = [];
        
        if ($editing) {
            $agent_post = get_post($agent_id);
            if (!$agent_post || $agent_post->post_type !== 'agent') {
                wp_redirect(remove_query_arg(['dashboard_action', 'dashboard_id'], get_permalink()));
                exit;
            }
            
            // Get all agent fields
            $agent_data = [
                'post_title' => $agent_post->post_title,
                'post_content' => $agent_post->post_content,
                'first_name' => get_field('first_name', $agent_id),
                'last_name' => get_field('last_name', $agent_id),
                'display_name' => get_field('display_name', $agent_id),
                'title' => get_field('title', $agent_id),
                'short_bio' => get_field('short_bio', $agent_id),
                'email' => get_field('email', $agent_id),
                'phone' => get_field('phone', $agent_id),
                'mobile_phone' => get_field('mobile_phone', $agent_id),
                'office_phone' => get_field('office_phone', $agent_id),
                'website_url' => get_field('website_url', $agent_id),
                'license_number' => get_field('license_number', $agent_id),
                'license_state' => get_field('license_state', $agent_id),
                'license_expiration' => get_field('license_expiration', $agent_id),
                'date_started' => get_field('date_started', $agent_id),
                'years_experience' => get_field('years_experience', $agent_id),
                'office_name' => get_field('office_name', $agent_id),
                'specialties' => get_field('specialties', $agent_id) ?: [],
                'languages' => get_field('languages', $agent_id) ?: [],
                'facebook_url' => get_field('facebook_url', $agent_id),
                'instagram_url' => get_field('instagram_url', $agent_id),
                'linkedin_url' => get_field('linkedin_url', $agent_id),
                'twitter_url' => get_field('twitter_url', $agent_id),
                'profile_photo' => get_field('profile_photo', $agent_id),
                'cover_photo' => get_field('cover_photo', $agent_id),
                'wordpress_user_id' => get_field('wordpress_user_id', $agent_id),
                'agent_status' => get_field('agent_status', $agent_id) ?: 'active'
            ];
        }
        ?>
        
        <div class="agent-form-container">
            <div class="form-header">
                <div class="form-header-content">
                    <h1 class="form-title">
                        <?php echo $editing ? 'Edit Agent' : 'Add New Agent'; ?>
                    </h1>
                    <p class="form-subtitle">
                        <?php echo $editing ? 'Update agent information and settings' : 'Create a new agent profile for your team'; ?>
                    </p>
                </div>
                <div class="form-header-actions">
                    <a href="<?php echo esc_url(remove_query_arg(['dashboard_action', 'dashboard_id'], get_permalink())); ?>" 
                       class="btn btn-secondary">
                        <span class="hph-icon-arrow-left"></span>
                        Back to Agents
                    </a>
                </div>
            </div>
            
            <form class="agent-form" id="agent-form" method="POST" enctype="multipart/form-data">
                <?php wp_nonce_field('hph_agent_form', 'agent_nonce'); ?>
                <input type="hidden" name="action" value="<?php echo $editing ? 'edit' : 'add'; ?>">
                <?php if ($editing): ?>
                    <input type="hidden" name="agent_id" value="<?php echo esc_attr($agent_id); ?>">
                <?php endif; ?>
                
                <!-- Basic Information Section -->
                <div class="form-section">
                    <div class="section-header">
                        <h2 class="section-title">Basic Information</h2>
                        <p class="section-description">Essential agent details and contact information</p>
                    </div>
                    
                    <div class="section-content">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input type="text" 
                                       id="first_name" 
                                       name="first_name" 
                                       value="<?php echo esc_attr($agent_data['first_name'] ?? ''); ?>" 
                                       required>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" 
                                       id="last_name" 
                                       name="last_name" 
                                       value="<?php echo esc_attr($agent_data['last_name'] ?? ''); ?>" 
                                       required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="display_name">Display Name</label>
                                <input type="text" 
                                       id="display_name" 
                                       name="display_name" 
                                       value="<?php echo esc_attr($agent_data['display_name'] ?? ''); ?>"
                                       placeholder="How the name appears publicly">
                            </div>
                            <div class="form-group">
                                <label for="title">Professional Title</label>
                                <input type="text" 
                                       id="title" 
                                       name="title" 
                                       value="<?php echo esc_attr($agent_data['title'] ?? ''); ?>"
                                       placeholder="e.g., Senior Real Estate Agent">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="short_bio">Short Bio</label>
                            <textarea id="short_bio" 
                                      name="short_bio" 
                                      rows="3"
                                      placeholder="Brief description for agent cards and listings..."><?php echo esc_textarea($agent_data['short_bio'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="bio">Full Bio</label>
                            <textarea id="bio" 
                                      name="bio" 
                                      rows="6"
                                      placeholder="Detailed biography for agent profile page..."><?php echo esc_textarea($agent_data['post_content'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Information Section -->
                <div class="form-section">
                    <div class="section-header">
                        <h2 class="section-title">Contact Information</h2>
                        <p class="section-description">How clients can reach this agent</p>
                    </div>
                    
                    <div class="section-content">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo esc_attr($agent_data['email'] ?? ''); ?>" 
                                       required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Primary Phone</label>
                                <input type="tel" 
                                       id="phone" 
                                       name="phone" 
                                       value="<?php echo esc_attr($agent_data['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="mobile_phone">Mobile Phone</label>
                                <input type="tel" 
                                       id="mobile_phone" 
                                       name="mobile_phone" 
                                       value="<?php echo esc_attr($agent_data['mobile_phone'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="office_phone">Office Phone</label>
                                <input type="tel" 
                                       id="office_phone" 
                                       name="office_phone" 
                                       value="<?php echo esc_attr($agent_data['office_phone'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="website_url">Personal Website</label>
                            <input type="url" 
                                   id="website_url" 
                                   name="website_url" 
                                   value="<?php echo esc_attr($agent_data['website_url'] ?? ''); ?>"
                                   placeholder="https://agent-website.com">
                        </div>
                    </div>
                </div>
                
                <!-- Professional Details Section -->
                <div class="form-section">
                    <div class="section-header">
                        <h2 class="section-title">Professional Details</h2>
                        <p class="section-description">Licensing and career information</p>
                    </div>
                    
                    <div class="section-content">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="license_number">License Number</label>
                                <input type="text" 
                                       id="license_number" 
                                       name="license_number" 
                                       value="<?php echo esc_attr($agent_data['license_number'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="license_state">License State</label>
                                <select id="license_state" name="license_state">
                                    <option value="">Select State</option>
                                    <option value="TX" <?php selected($agent_data['license_state'] ?? '', 'TX'); ?>>Texas</option>
                                    <option value="CA" <?php selected($agent_data['license_state'] ?? '', 'CA'); ?>>California</option>
                                    <option value="FL" <?php selected($agent_data['license_state'] ?? '', 'FL'); ?>>Florida</option>
                                    <!-- Add more states as needed -->
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="license_expiration">License Expiration</label>
                                <input type="date" 
                                       id="license_expiration" 
                                       name="license_expiration" 
                                       value="<?php echo esc_attr($agent_data['license_expiration'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="date_started">Start Date</label>
                                <input type="date" 
                                       id="date_started" 
                                       name="date_started" 
                                       value="<?php echo esc_attr($agent_data['date_started'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="years_experience">Years of Experience</label>
                                <input type="number" 
                                       id="years_experience" 
                                       name="years_experience" 
                                       value="<?php echo esc_attr($agent_data['years_experience'] ?? ''); ?>"
                                       min="0"
                                       max="50">
                            </div>
                            <div class="form-group">
                                <label for="office_name">Office Name</label>
                                <input type="text" 
                                       id="office_name" 
                                       name="office_name" 
                                       value="<?php echo esc_attr($agent_data['office_name'] ?? 'Happy Place Realty'); ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="agent_status">Status</label>
                                <select id="agent_status" name="agent_status">
                                    <option value="active" <?php selected($agent_data['agent_status'] ?? 'active', 'active'); ?>>Active</option>
                                    <option value="inactive" <?php selected($agent_data['agent_status'] ?? '', 'inactive'); ?>>Inactive</option>
                                    <option value="pending" <?php selected($agent_data['agent_status'] ?? '', 'pending'); ?>>Pending</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Specialties and Languages Section -->
                <div class="form-section">
                    <div class="section-header">
                        <h2 class="section-title">Specialties & Languages</h2>
                        <p class="section-description">Areas of expertise and languages spoken</p>
                    </div>
                    
                    <div class="section-content">
                        <div class="form-group">
                            <label>Specialties</label>
                            <div class="checkbox-group">
                                <?php 
                                $specialties_options = [
                                    'buyer_agent' => 'Buyer Agent',
                                    'listing_agent' => 'Listing Agent', 
                                    'first_time_buyers' => 'First-Time Buyers',
                                    'luxury_homes' => 'Luxury Homes',
                                    'commercial' => 'Commercial',
                                    'investment' => 'Investment Properties',
                                    'condos' => 'Condominiums',
                                    'new_construction' => 'New Construction',
                                    'relocation' => 'Relocation',
                                    'foreclosure' => 'Foreclosure/REO'
                                ];
                                
                                foreach ($specialties_options as $value => $label):
                                    $checked = in_array($value, $agent_data['specialties'] ?? []);
                                ?>
                                    <label class="checkbox-label">
                                        <input type="checkbox" 
                                               name="specialties[]" 
                                               value="<?php echo esc_attr($value); ?>"
                                               <?php checked($checked); ?>>
                                        <span class="checkmark"></span>
                                        <?php echo esc_html($label); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Languages</label>
                            <div class="checkbox-group">
                                <?php 
                                $languages_options = [
                                    'english' => 'English',
                                    'spanish' => 'Spanish',
                                    'french' => 'French',
                                    'german' => 'German',
                                    'chinese' => 'Chinese',
                                    'japanese' => 'Japanese',
                                    'korean' => 'Korean',
                                    'portuguese' => 'Portuguese',
                                    'italian' => 'Italian',
                                    'russian' => 'Russian'
                                ];
                                
                                foreach ($languages_options as $value => $label):
                                    $checked = in_array($value, $agent_data['languages'] ?? []);
                                ?>
                                    <label class="checkbox-label">
                                        <input type="checkbox" 
                                               name="languages[]" 
                                               value="<?php echo esc_attr($value); ?>"
                                               <?php checked($checked); ?>>
                                        <span class="checkmark"></span>
                                        <?php echo esc_html($label); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Social Media Section -->
                <div class="form-section">
                    <div class="section-header">
                        <h2 class="section-title">Social Media</h2>
                        <p class="section-description">Social media profiles and online presence</p>
                    </div>
                    
                    <div class="section-content">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="facebook_url">Facebook URL</label>
                                <input type="url" 
                                       id="facebook_url" 
                                       name="facebook_url" 
                                       value="<?php echo esc_attr($agent_data['facebook_url'] ?? ''); ?>"
                                       placeholder="https://facebook.com/username">
                            </div>
                            <div class="form-group">
                                <label for="instagram_url">Instagram URL</label>
                                <input type="url" 
                                       id="instagram_url" 
                                       name="instagram_url" 
                                       value="<?php echo esc_attr($agent_data['instagram_url'] ?? ''); ?>"
                                       placeholder="https://instagram.com/username">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="linkedin_url">LinkedIn URL</label>
                                <input type="url" 
                                       id="linkedin_url" 
                                       name="linkedin_url" 
                                       value="<?php echo esc_attr($agent_data['linkedin_url'] ?? ''); ?>"
                                       placeholder="https://linkedin.com/in/username">
                            </div>
                            <div class="form-group">
                                <label for="twitter_url">Twitter URL</label>
                                <input type="url" 
                                       id="twitter_url" 
                                       name="twitter_url" 
                                       value="<?php echo esc_attr($agent_data['twitter_url'] ?? ''); ?>"
                                       placeholder="https://twitter.com/username">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- WordPress User Integration Section -->
                <div class="form-section">
                    <div class="section-header">
                        <h2 class="section-title">WordPress User Integration</h2>
                        <p class="section-description">Link this agent to a WordPress user account for dashboard access</p>
                    </div>
                    
                    <div class="section-content">
                        <div class="form-group">
                            <label for="wordpress_user_id">WordPress User</label>
                            <select id="wordpress_user_id" name="wordpress_user_id">
                                <option value="">No WordPress User</option>
                                <?php 
                                $users = get_users(['role__in' => ['agent', 'broker', 'administrator']]);
                                foreach ($users as $user):
                                    $selected = ($agent_data['wordpress_user_id'] ?? '') == $user->ID;
                                ?>
                                    <option value="<?php echo esc_attr($user->ID); ?>" <?php selected($selected); ?>>
                                        <?php echo esc_html($user->display_name . ' (' . $user->user_email . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-help">Select a WordPress user to give this agent dashboard access</small>
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="form-actions">
                    <div class="form-actions-primary">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <span class="hph-icon-save"></span>
                            <?php echo $editing ? 'Update Agent' : 'Create Agent'; ?>
                        </button>
                        <button type="button" class="btn btn-secondary btn-lg" id="save-draft">
                            <span class="hph-icon-clock"></span>
                            Save as Draft
                        </button>
                    </div>
                    
                    <div class="form-actions-secondary">
                        <a href="<?php echo esc_url(remove_query_arg(['dashboard_action', 'dashboard_id'], get_permalink())); ?>" 
                           class="btn btn-ghost">
                            Cancel
                        </a>
                        <?php if ($editing): ?>
                            <button type="button" class="btn btn-danger" id="delete-agent">
                                <span class="hph-icon-trash"></span>
                                Delete Agent
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    // Auto-update display name based on first/last name
    $('#first_name, #last_name').on('input', function() {
        const firstName = $('#first_name').val();
        const lastName = $('#last_name').val();
        if (firstName && lastName && !$('#display_name').val()) {
            $('#display_name').val(firstName + ' ' + lastName);
        }
    });
    
    // Form validation and submission
    $('#agent-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'hph_save_agent');
        formData.append('nonce', hph_dashboard.nonce);
        
        const $submitBtn = $(this).find('button[type="submit"]');
        const originalText = $submitBtn.html();
        
        $submitBtn.prop('disabled', true).html('<span class="spinner"></span> Saving...');
        
        $.ajax({
            url: hph_dashboard.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Show success message
                    showNotification('success', response.data.message);
                    
                    // Redirect to agents list after a delay
                    setTimeout(function() {
                        window.location.href = '<?php echo esc_url(remove_query_arg(['dashboard_action', 'dashboard_id'], get_permalink())); ?>';
                    }, 1500);
                } else {
                    showNotification('error', response.data.message || 'An error occurred');
                    $submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function() {
                showNotification('error', 'Connection error occurred');
                $submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Bulk actions
    $('#apply-bulk-action').on('click', function() {
        const action = $('#bulk_action').val();
        const selectedAgents = $('input[name="selected_agents[]"]:checked').map(function() {
            return this.value;
        }).get();
        
        if (!action) {
            showNotification('warning', 'Please select a bulk action');
            return;
        }
        
        if (selectedAgents.length === 0) {
            showNotification('warning', 'Please select at least one agent');
            return;
        }
        
        if (action === 'delete' && !confirm('Are you sure you want to delete the selected agents?')) {
            return;
        }
        
        $.ajax({
            url: hph_dashboard.ajax_url,
            type: 'POST',
            data: {
                action: 'hph_bulk_agent_action',
                bulk_action: action,
                agent_ids: selectedAgents,
                nonce: hph_dashboard.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.data.message);
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showNotification('error', response.data.message);
                }
            }
        });
    });
    
    // Delete agent
    $('#delete-agent').on('click', function() {
        if (!confirm('Are you sure you want to delete this agent? This action cannot be undone.')) {
            return;
        }
        
        const agentId = $('input[name="agent_id"]').val();
        
        $.ajax({
            url: hph_dashboard.ajax_url,
            type: 'POST',
            data: {
                action: 'hph_delete_agent',
                agent_id: agentId,
                nonce: hph_dashboard.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.data.message);
                    setTimeout(function() {
                        window.location.href = '<?php echo esc_url(remove_query_arg(['dashboard_action', 'dashboard_id'], get_permalink())); ?>';
                    }, 1500);
                } else {
                    showNotification('error', response.data.message);
                }
            }
        });
    });
    
    // Utility function for notifications
    function showNotification(type, message) {
        // Implementation depends on your notification system
        console.log(type + ': ' + message);
    }
});
</script>