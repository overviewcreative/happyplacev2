<?php
/**
 * Simple Agent Archive Template
 */

get_header();

// Get filter parameters
$search = sanitize_text_field($_GET['search'] ?? '');
$specialty = sanitize_text_field($_GET['specialty'] ?? '');
$experience = sanitize_text_field($_GET['experience'] ?? '');
$sort = sanitize_text_field($_GET['sort'] ?? 'name_asc');

// Build query
$paged = get_query_var('paged') ? get_query_var('paged') : 1;
$args = [
    'post_type' => 'agent',
    'post_status' => 'publish', 
    'posts_per_page' => -1, // Show all agents on one page
    'meta_query' => []
];

// Add search
if (!empty($search)) {
    $args['s'] = $search;
}

// Add specialty filter
if (!empty($specialty)) {
    $args['meta_query'][] = [
        'key' => 'specialties',
        'value' => $specialty,
        'compare' => 'LIKE'
    ];
}

// Add experience filter
if (!empty($experience)) {
    switch ($experience) {
        case '0-5':
            $args['meta_query'][] = [
                'key' => 'years_experience',
                'value' => [0, 5],
                'type' => 'NUMERIC',
                'compare' => 'BETWEEN'
            ];
            break;
        case '5-10':
            $args['meta_query'][] = [
                'key' => 'years_experience',
                'value' => [5, 10],
                'type' => 'NUMERIC',
                'compare' => 'BETWEEN'
            ];
            break;
        case '10+':
            $args['meta_query'][] = [
                'key' => 'years_experience',
                'value' => 10,
                'type' => 'NUMERIC',
                'compare' => '>='
            ];
            break;
    }
}

// Add sorting
switch ($sort) {
    case 'name_desc':
        $args['orderby'] = 'title';
        $args['order'] = 'DESC';
        break;
    case 'experience_desc':
        $args['meta_key'] = 'years_experience';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'DESC';
        break;
    case 'experience_asc':
        $args['meta_key'] = 'years_experience';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'ASC';
        break;
    case 'recent':
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
        break;
    default: // name_asc
        $args['orderby'] = 'title';
        $args['order'] = 'ASC';
        break;
}

$agents = new WP_Query($args);
?>

<!-- Hero Section with Search/Filter -->
<section class="hph-hero hph-relative hph-min-h-96 hph-flex hph-items-center hph-bg-cover hph-bg-center" 
         style="background-image: url('<?php echo get_template_directory_uri(); ?>/assets/images/agents-hero.jpg');">
    
    <!-- Gradient Overlay -->
    <div class="hph-absolute hph-inset-0 hph-bg-gradient-to-r hph-from-black hph-via-black hph-to-transparent hph-opacity-70"></div>
    
    <!-- Content Container -->
    <div class="hph-container hph-relative hph-z-10 hph-py-xl">
        <div class="hph-max-w-4xl">
            
            <!-- Hero Title -->
            <div class="hph-mb-lg">
                <h1 class="hph-text-white hph-text-4xl md:hph-text-5xl hph-font-bold hph-mb-md">Our Expert Agents</h1>
                <p class="hph-text-white hph-text-xl hph-opacity-90">Find the perfect agent to help you buy or sell your home</p>
            </div>
            
            <!-- Search/Filter Form -->
            <div class="hph-bg-white hph-rounded-lg hph-p-6 hph-shadow-xl">
                <form method="get" class="hph-space-y-md">
                    
                    <!-- Search Bar - Full Width Centered -->
                    <div class="hph-w-full hph-flex hph-flex-col hph-items-center">
                        <label for="search" class="hph-block hph-text-sm hph-font-medium hph-mb-xs hph-text-gray-700 hph-text-center">Search Agents</label>
                        <input type="text" id="search" name="search" value="<?php echo esc_attr($search); ?>" 
                               placeholder="Agent name..." class="hph-input hph-w-full hph-text-lg hph-py-4 hph-px-6 hph-text-center">
                    </div>
                    
                    <!-- Filter Row -->
                    <div class="hph-grid hph-grid-cols-1 md:hph-grid-cols-4 hph-gap-md hph-items-end">
                        
                        <!-- Specialty -->
                        <div>
                            <label for="specialty" class="hph-block hph-text-sm hph-font-medium hph-mb-xs hph-text-gray-700">Specialty</label>
                            <select id="specialty" name="specialty" class="hph-select hph-w-full">
                                <option value="">All Specialties</option>
                                <option value="Residential" <?php selected($specialty, 'Residential'); ?>>Residential</option>
                                <option value="Commercial" <?php selected($specialty, 'Commercial'); ?>>Commercial</option>
                                <option value="First-time Buyers" <?php selected($specialty, 'First-time Buyers'); ?>>First-time Buyers</option>
                                <option value="Luxury" <?php selected($specialty, 'Luxury'); ?>>Luxury Homes</option>
                                <option value="Investment" <?php selected($specialty, 'Investment'); ?>>Investment Properties</option>
                            </select>
                        </div>
                        
                        <!-- Experience -->
                        <div>
                            <label for="experience" class="hph-block hph-text-sm hph-font-medium hph-mb-xs hph-text-gray-700">Experience</label>
                            <select id="experience" name="experience" class="hph-select hph-w-full">
                                <option value="">Any Experience</option>
                                <option value="0-5" <?php selected($experience, '0-5'); ?>>0-5 Years</option>
                                <option value="5-10" <?php selected($experience, '5-10'); ?>>5-10 Years</option>
                                <option value="10+" <?php selected($experience, '10+'); ?>>10+ Years</option>
                            </select>
                        </div>
                        
                        <!-- Sort -->
                        <div>
                            <label for="sort" class="hph-block hph-text-sm hph-font-medium hph-mb-xs hph-text-gray-700">Sort By</label>
                            <select id="sort" name="sort" class="hph-select hph-w-full">
                                <option value="name_asc" <?php selected($sort, 'name_asc'); ?>>Name A-Z</option>
                                <option value="name_desc" <?php selected($sort, 'name_desc'); ?>>Name Z-A</option>
                                <option value="experience_desc" <?php selected($sort, 'experience_desc'); ?>>Most Experienced</option>
                                <option value="experience_asc" <?php selected($sort, 'experience_asc'); ?>>Least Experienced</option>
                                <option value="recent" <?php selected($sort, 'recent'); ?>>Recently Added</option>
                            </select>
                        </div>
                        
                        <!-- Submit -->
                        <div class="hph-flex hph-space-x-sm">
                            <button type="submit" class="hph-btn hph-btn-primary hph-px-6 hph-py-3">Search</button>
                            <a href="<?php echo get_post_type_archive_link('agent'); ?>" class="hph-btn hph-btn-outline hph-px-6 hph-py-3">Clear</a>
                        </div>
                    </div>
                </form>
            </div>
            
        </div>
    </div>
</section>

<!-- Results Section -->
<div class="hph-container hph-py-xl">

    <?php if ($agents->have_posts()) : ?>
        <!-- Results Count -->
        <div class="hph-mb-md">
            <p class="hph-text-gray-600">Found <?php echo $agents->found_posts; ?> agent<?php echo $agents->found_posts != 1 ? 's' : ''; ?></p>
        </div>
        
        <div class="hph-grid hph-grid-cols-1 md:hph-grid-cols-2 lg:hph-grid-cols-3 xl:hph-grid-cols-4 hph-gap-lg hph-mb-xl">
            <?php while ($agents->have_posts()) : $agents->the_post(); ?>
                <?php 
                $email = get_field('email');
                $phone = get_field('phone');
                $title = get_field('title');
                ?>
                
                <div class="hph-card hph-rounded-lg hph-p-6 hph-bg-white hph-shadow-md hover:hph-shadow-lg hph-transition-shadow">
                    <!-- Clickable area for profile -->
                    <a href="<?php the_permalink(); ?>" class="hph-block hph-text-decoration-none">
                        <!-- Profile photo -->
                        <div class="hph-flex hph-justify-center hph-mb-4">
                            <?php 
                            $profile_photo = get_field('profile_photo');
                            if ($profile_photo) : ?>
                                <img src="<?php echo esc_url($profile_photo['sizes']['thumbnail'] ?? $profile_photo['url']); ?>" 
                                     alt="<?php the_title(); ?>"
                                     class="hph-w-20 hph-h-20 hph-rounded-full hph-object-cover hph-border-3 hph-border-gray-200"
                                     style="object-position: center center;">
                            <?php else : ?>
                                <div class="hph-w-20 hph-h-20 hph-rounded-full hph-bg-gray-200 hph-flex hph-items-center hph-justify-center">
                                    <i class="fas fa-user hph-text-gray-400 hph-text-xl"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Agent name and title -->
                        <div class="hph-text-center hph-mb-4">
                            <h3 class="hph-text-lg hph-font-semibold hph-text-gray-900 hph-mb-4"><?php the_title(); ?></h3>
                            <?php if ($title) : ?>
                                <p class="hph-text-sm hph-text-gray-600"><?php echo esc_html($title); ?></p>
                            <?php endif; ?>
                        </div>
                    </a>
                    
                    <!-- Contact buttons - NOT clickable to profile -->
                    <div class="hph-space-y-2">
                        <?php if ($phone) : ?>
                            <a href="tel:<?php echo esc_attr($phone); ?>" 
                               class="hph-btn hph-btn-primary hph-btn-sm hph-w-full hph-text-center hph-py-2 hph-mb-2"
                               onclick="event.stopPropagation();">
                                <i class="fas fa-phone hph-mr-2"></i>
                                <?php echo esc_html($phone); ?>
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($email) : ?>
                            <a href="mailto:<?php echo esc_attr($email); ?>" 
                               class="hph-btn hph-btn-outline hph-btn-sm hph-w-full hph-text-center hph-py-2 hph-mb-2"
                               onclick="event.stopPropagation();">
                                <i class="fas fa-envelope hph-mr-2"></i>
                                Email
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>


    <?php else : ?>
        <div class="hph-text-center hph-py-5xl">
            <div class="hph-mb-lg">
                <i class="fas fa-users hph-text-gray-300 hph-text-6xl"></i>
            </div>
            <h3 class="hph-text-2xl hph-font-semibold hph-mb-md">No Agents Found</h3>
            <p class="hph-text-gray-600 hph-mb-lg">There are currently no agents to display.</p>
        </div>
    <?php endif; ?>

</div>

<?php
wp_reset_postdata();
get_footer();
?>
