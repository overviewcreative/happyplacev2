<?php
/**
 * Enhanced Agent Card with Contact Integration and Statistics
 * File: template-parts/agent-card-enhanced.php
 * 
 * @package HappyPlaceTheme
 */

$agent_id = $args['agent_id'] ?? get_the_ID();
$view_type = $args['view_type'] ?? 'grid';
$show_contact = $args['show_contact'] ?? false;
$show_favorites = $args['show_favorites'] ?? false;
$show_stats = $args['show_stats'] ?? false;

// Get agent data
$first_name = get_field('first_name', $agent_id);
$last_name = get_field('last_name', $agent_id);
$title = get_field('title', $agent_id);
$email = get_field('email', $agent_id);
$phone = get_field('phone', $agent_id);
$bio = get_field('bio', $agent_id);
$years_experience = get_field('years_experience', $agent_id);
$specialties = get_field('specialties', $agent_id);
$languages = get_field('languages', $agent_id);
$license_number = get_field('license_number', $agent_id);
$featured = get_field('featured', $agent_id);

// Performance stats
$total_sales_volume = get_field('total_sales_volume', $agent_id);
$total_listings_sold = get_field('total_listings_sold', $agent_id);
$average_days_on_market = get_field('average_days_on_market', $agent_id);
$client_satisfaction = get_field('client_satisfaction_rating', $agent_id);
$total_reviews = get_field('total_reviews', $agent_id);

// Office and contact info
$office_id = get_field('office', $agent_id);
$office_name = '';
$office_address = '';
if ($office_id) {
    $office_name = get_the_title($office_id);
    $office_address = get_field('address', $office_id);
}

// Social media and website
$website = get_field('website', $agent_id);
$facebook = get_field('facebook', $agent_id);
$instagram = get_field('instagram', $agent_id);
$linkedin = get_field('linkedin', $agent_id);
$twitter = get_field('twitter', $agent_id);

// Build full name
$full_name = trim($first_name . ' ' . $last_name);
if (empty($full_name)) {
    $full_name = get_the_title($agent_id);
}

// Get agent photo
$agent_photo = get_field('profile_photo', $agent_id);
if ($agent_photo && is_array($agent_photo)) {
    $agent_photo = $agent_photo['sizes']['medium'] ?? $agent_photo['url'];
} elseif ($agent_photo && is_numeric($agent_photo)) {
    $agent_photo = wp_get_attachment_image_url($agent_photo, 'medium');
} elseif (!$agent_photo) {
    $agent_photo = get_the_post_thumbnail_url($agent_id, 'medium');
}

if (!$agent_photo) {
    $agent_photo = get_template_directory_uri() . '/assets/images/placeholder-agent.jpg';
}

// Process specialties and languages
$specialty_list = [];
if ($specialties) {
    if (is_array($specialties)) {
        $specialty_list = $specialties;
    } else {
        $specialty_list = explode(',', $specialties);
    }
    $specialty_list = array_map('trim', $specialty_list);
}

$language_list = [];
if ($languages) {
    if (is_array($languages)) {
        $language_list = $languages;
    } else {
        $language_list = explode(',', $languages);
    }
    $language_list = array_map('trim', $language_list);
}

// Check if user has favorited this agent
$is_favorited = false;
if (is_user_logged_in()) {
    $current_user_id = get_current_user_id();
    $favorite_agents = get_user_meta($current_user_id, '_favorite_agents', true);
    $is_favorited = is_array($favorite_agents) && in_array($agent_id, $favorite_agents);
}

// Card classes
$card_classes = ['agent-card-enhanced', 'hph-card', 'hph-card-elevated', 'hph-h-full', 'hph-flex', 'hph-transition-all', 'hover:hph-shadow-xl'];
if ($view_type === 'list') {
    $card_classes[] = 'hph-flex-row';
    $card_classes[] = 'hph-max-h-80';
} else {
    $card_classes[] = 'hph-flex-col';
}

if ($featured) {
    $card_classes[] = 'featured-agent';
}

// Generate match score if we have session data about user preferences
$match_score = null;
if (isset($_SESSION['agent_preferences'])) {
    $match_score = calculate_agent_match_score($agent_id, $_SESSION['agent_preferences']);
}
?>

<article class="<?php echo implode(' ', $card_classes); ?>" 
         data-agent-id="<?php echo $agent_id; ?>"
         data-specialties="<?php echo esc_attr(implode(',', $specialty_list)); ?>"
         data-languages="<?php echo esc_attr(implode(',', $language_list)); ?>"
         data-experience="<?php echo $years_experience; ?>"
         data-sales-volume="<?php echo $total_sales_volume; ?>"
         data-office="<?php echo $office_id; ?>">
    
    <!-- Match Score Badge -->
    <?php if ($match_score !== null): ?>
    <div class="agent-match-score <?php echo $match_score >= 80 ? 'match-score-high' : ($match_score >= 60 ? 'match-score-medium' : 'match-score-low'); ?>">
        <?php echo $match_score; ?>% Match
    </div>
    <?php endif; ?>
    
    <a href="<?php echo get_permalink($agent_id); ?>" class="hph-block hph-h-full hph-flex <?php echo $view_type === 'list' ? 'hph-flex-row' : 'hph-flex-col'; ?>">
        
        <!-- Photo Container -->
        <div class="hph-relative <?php echo $view_type === 'list' ? 'hph-w-64 hph-flex-shrink-0' : 'hph-aspect-ratio-1-1'; ?> hph-overflow-hidden hph-bg-gray-200">
            <img src="<?php echo esc_url($agent_photo); ?>" 
                 alt="<?php echo esc_attr($full_name); ?>"
                 class="hph-w-full hph-h-full hph-object-cover"
                 loading="lazy">
            
            <!-- Featured Badge -->
            <?php if ($featured): ?>
            <div class="hph-absolute hph-top-md hph-left-md">
                <span class="hph-px-sm hph-py-xs hph-rounded-md hph-text-xs hph-font-semibold hph-bg-warning hph-text-white">
                    <i class="fas fa-star hph-mr-xs"></i>Featured
                </span>
            </div>
            <?php endif; ?>
            
            <!-- Experience Badge -->
            <?php if ($years_experience): ?>
            <div class="hph-absolute hph-top-md hph-right-md">
                <span class="hph-bg-primary hph-text-white hph-px-sm hph-py-xs hph-rounded hph-text-xs hph-font-semibold">
                    <?php echo $years_experience; ?>+ Years
                </span>
            </div>
            <?php endif; ?>
            
            <!-- Online Status Indicator -->
            <div class="hph-absolute hph-bottom-md hph-left-md">
                <div class="hph-flex hph-items-center hph-gap-xs hph-bg-black hph-bg-opacity-75 hph-text-white hph-px-sm hph-py-xs hph-rounded hph-text-xs">
                    <div class="hph-w-2 hph-h-2 hph-bg-green-400 hph-rounded-full hph-animate-pulse"></div>
                    Available
                </div>
            </div>
            
            <!-- Contact Overlay -->
            <?php if ($show_contact): ?>
            <div class="contact-overlay">
                <div class="contact-options">
                    <?php if ($phone): ?>
                    <a href="tel:<?php echo esc_attr($phone); ?>" class="contact-btn" onclick="event.stopPropagation();">
                        <i class="fas fa-phone"></i> Call Now
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($email): ?>
                    <a href="mailto:<?php echo esc_attr($email); ?>" class="contact-btn" onclick="event.stopPropagation();">
                        <i class="fas fa-envelope"></i> Email
                    </a>
                    <?php endif; ?>
                    
                    <button class="contact-btn" onclick="event.stopPropagation(); showContactForm(<?php echo $agent_id; ?>);">
                        <i class="fas fa-comments"></i> Message
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Content -->
        <div class="hph-p-md hph-flex-grow hph-flex hph-flex-col">
            
            <!-- Name, Title & Office -->
            <div class="hph-mb-sm">
                <h3 class="hph-text-lg hph-font-semibold hph-text-gray-900 hph-mb-xs hph-line-clamp-1">
                    <?php echo esc_html($full_name); ?>
                </h3>
                
                <?php if ($title): ?>
                <p class="hph-text-sm hph-text-primary hph-font-medium hph-mb-xs">
                    <?php echo esc_html($title); ?>
                </p>
                <?php endif; ?>
                
                <?php if ($office_name): ?>
                <p class="hph-text-xs hph-text-gray-500">
                    <i class="fas fa-building hph-mr-xs"></i>
                    <?php echo esc_html($office_name); ?>
                </p>
                <?php endif; ?>
                
                <?php if ($license_number): ?>
                <p class="hph-text-xs hph-text-gray-400 hph-mt-xs">
                    License: <?php echo esc_html($license_number); ?>
                </p>
                <?php endif; ?>
            </div>
            
            <!-- Bio -->
            <?php if ($bio): ?>
            <p class="hph-text-sm hph-text-gray-600 hph-mb-md hph-line-clamp-2">
                <?php echo wp_trim_words($bio, 20); ?>
            </p>
            <?php endif; ?>
            
            <!-- Specialties -->
            <?php if (!empty($specialty_list)): ?>
            <div class="hph-mb-md">
                <div class="hph-flex hph-flex-wrap hph-gap-xs">
                    <?php 
                    $displayed_specialties = array_slice($specialty_list, 0, 3);
                    foreach ($displayed_specialties as $specialty): 
                        $clean_specialty = str_replace('-', ' ', $specialty);
                    ?>
                    <span class="specialty-tag <?php echo $featured ? 'featured' : ''; ?>">
                        <?php echo esc_html(ucwords($clean_specialty)); ?>
                    </span>
                    <?php endforeach; ?>
                    
                    <?php if (count($specialty_list) > 3): ?>
                    <span class="hph-text-xs hph-text-gray-500">
                        +<?php echo count($specialty_list) - 3; ?> more
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Languages -->
            <?php if (!empty($language_list) && count($language_list) > 0): ?>
            <div class="hph-mb-md hph-text-sm hph-text-gray-600">
                <i class="fas fa-globe hph-mr-xs hph-text-primary"></i>
                Speaks: <?php echo esc_html(implode(', ', array_slice($language_list, 0, 3))); ?>
                <?php if (count($language_list) > 3): ?>
                    <span class="hph-text-gray-400">+<?php echo count($language_list) - 3; ?> more</span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Performance Stats -->
            <?php if ($show_stats && ($total_sales_volume || $total_listings_sold || $client_satisfaction || $average_days_on_market)): ?>
            <div class="agent-stats hph-mt-auto">
                <?php if ($total_sales_volume): ?>
                <div class="stat-item">
                    <span class="stat-number">$<?php echo number_format($total_sales_volume / 1000000, 1); ?>M</span>
                    <span class="stat-label">Sales Volume</span>
                </div>
                <?php endif; ?>
                
                <?php if ($total_listings_sold): ?>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $total_listings_sold; ?></span>
                    <span class="stat-label">Homes Sold</span>
                </div>
                <?php endif; ?>
                
                <?php if ($client_satisfaction): ?>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $client_satisfaction; ?>%</span>
                    <span class="stat-label">Satisfaction</span>
                </div>
                <?php endif; ?>
                
                <?php if ($average_days_on_market): ?>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $average_days_on_market; ?></span>
                    <span class="stat-label">Avg DOM</span>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Contact Info (for list view) -->
            <?php if ($view_type === 'list'): ?>
            <div class="hph-mt-auto hph-pt-md hph-border-t hph-text-sm hph-text-gray-700">
                <div class="hph-flex hph-justify-between hph-items-center">
                    <div>
                        <?php if ($phone): ?>
                        <div class="hph-flex hph-items-center hph-gap-xs hph-mb-xs">
                            <i class="fas fa-phone hph-text-primary"></i>
                            <span><?php echo esc_html($phone); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($email): ?>
                        <div class="hph-flex hph-items-center hph-gap-xs">
                            <i class="fas fa-envelope hph-text-primary"></i>
                            <span class="hph-truncate"><?php echo esc_html($email); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Social Media Links -->
                    <div class="hph-flex hph-gap-xs">
                        <?php if ($linkedin): ?>
                        <a href="<?php echo esc_url($linkedin); ?>" target="_blank" class="hph-text-gray-400 hover:hph-text-primary" onclick="event.stopPropagation();">
                            <i class="fab fa-linkedin"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($facebook): ?>
                        <a href="<?php echo esc_url($facebook); ?>" target="_blank" class="hph-text-gray-400 hover:hph-text-primary" onclick="event.stopPropagation();">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($instagram): ?>
                        <a href="<?php echo esc_url($instagram); ?>" target="_blank" class="hph-text-gray-400 hover:hph-text-primary" onclick="event.stopPropagation();">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($website): ?>
                        <a href="<?php echo esc_url($website); ?>" target="_blank" class="hph-text-gray-400 hover:hph-text-primary" onclick="event.stopPropagation();">
                            <i class="fas fa-globe"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </a>
    
    <!-- Action Buttons -->
    <?php if ($show_favorites || $show_contact): ?>
    <div class="agent-actions">
        <?php if ($show_favorites): ?>
        <button class="action-btn favorite-btn <?php echo $is_favorited ? 'favorited' : ''; ?>" 
                data-agent-id="<?php echo $agent_id; ?>"
                data-action="favorite"
                title="<?php echo $is_favorited ? 'Remove from Favorites' : 'Add to Favorites'; ?>">
            <i class="<?php echo $is_favorited ? 'fas' : 'far'; ?> fa-heart"></i>
        </button>
        <?php endif; ?>
        
        <!-- Share Button -->
        <button class="action-btn share-btn" 
                data-agent-id="<?php echo $agent_id; ?>"
                data-action="share"
                data-url="<?php echo get_permalink($agent_id); ?>"
                data-title="<?php echo esc_attr($full_name); ?>"
                title="Share this agent">
            <i class="fas fa-share-alt"></i>
        </button>
    </div>
    
    <!-- Quick Contact Button -->
    <?php if ($show_contact && $view_type === 'grid'): ?>
    <button class="quick-contact-btn" 
            data-agent-id="<?php echo $agent_id; ?>"
            onclick="event.stopPropagation(); showQuickContact(<?php echo $agent_id; ?>);"
            title="Quick Contact">
        <i class="fas fa-comments"></i>
    </button>
    <?php endif; ?>
    <?php endif; ?>
    
    <!-- Review Stars (if reviews exist) -->
    <?php if ($total_reviews && $client_satisfaction): ?>
    <div class="hph-absolute hph-bottom-md hph-right-md hph-bg-white hph-bg-opacity-90 hph-backdrop-blur hph-px-sm hph-py-xs hph-rounded hph-text-xs hph-flex hph-items-center hph-gap-xs">
        <div class="hph-flex hph-text-yellow-400">
            <?php 
            $rating = ($client_satisfaction / 100) * 5;
            for ($i = 1; $i <= 5; $i++): ?>
                <i class="<?php echo $i <= $rating ? 'fas' : 'far'; ?> fa-star"></i>
            <?php endfor; ?>
        </div>
        <span class="hph-text-gray-600">(<?php echo $total_reviews; ?>)</span>
    </div>
    <?php endif; ?>
</article>

<?php
/**
 * Helper function to calculate agent match score based on user preferences
 */
function calculate_agent_match_score($agent_id, $preferences) {
    $score = 0;
    $total_factors = 0;
    
    // Goal matching
    if (!empty($preferences['goal'])) {
        $agent_specialties = get_field('specialties', $agent_id);
        $specialty_array = is_array($agent_specialties) ? $agent_specialties : explode(',', $agent_specialties);
        
        $goal_specialty_map = [
            'buy' => 'buyers-agent',
            'sell' => 'listing-agent',
            'luxury' => 'luxury-homes',
            'invest' => 'investment-properties',
            'commercial' => 'commercial'
        ];
        
        if (isset($goal_specialty_map[$preferences['goal']]) && 
            in_array($goal_specialty_map[$preferences['goal']], $specialty_array)) {
            $score += 30;
        }
        $total_factors += 30;
    }
    
    // Language matching
    if (!empty($preferences['language'])) {
        $agent_languages = get_field('languages', $agent_id);
        $language_array = is_array($agent_languages) ? $agent_languages : explode(',', $agent_languages);
        
        if (in_array($preferences['language'], $language_array)) {
            $score += 20;
        }
        $total_factors += 20;
    }
    
    // Experience factor
    $experience = get_field('years_experience', $agent_id);
    if ($experience) {
        if ($experience >= 10) $score += 15;
        elseif ($experience >= 5) $score += 10;
        else $score += 5;
        $total_factors += 15;
    }
    
    // Performance factors
    $sales_volume = get_field('total_sales_volume', $agent_id);
    $satisfaction = get_field('client_satisfaction_rating', $agent_id);
    
    if ($sales_volume && $sales_volume > 5000000) $score += 10;
    if ($satisfaction && $satisfaction > 90) $score += 10;
    $total_factors += 20;
    
    // First-time buyer specialty
    if (!empty($preferences['first_time']) && $preferences['first_time'] === 'yes') {
        if (in_array('first-time-buyers', $specialty_array ?? [])) {
            $score += 15;
        }
        $total_factors += 15;
    }
    
    return $total_factors > 0 ? min(100, round(($score / $total_factors) * 100)) : 0;
}
?>
