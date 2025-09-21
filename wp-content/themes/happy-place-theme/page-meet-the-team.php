<?php
/**
 * Simple Agent Archive Template
 */

get_header();

// Register template for asset loading
if (function_exists('hph_register_template_part')) {
    hph_register_template_part('meet-the-team');
}

// Get filter parameters
$sort = sanitize_text_field($_GET['sort'] ?? 'name_asc');

// Build combined query for both staff and agents
$args = [
    'post_type' => ['agent', 'staff'],
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'meta_query' => [
        'relation' => 'OR',
        [
            'key' => 'employment_status',
            'value' => 'active',
            'compare' => '='
        ],
        [
            'key' => 'employment_status',
            'compare' => 'NOT EXISTS'
        ]
    ]
];


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
    default: // name_asc - but show staff first, then agents
        $args['orderby'] = [
            'post_type' => 'ASC', // staff comes before agent alphabetically
            'title' => 'ASC'
        ];
        break;
}

$team_members = new WP_Query($args);
?>

<!-- Hero Section -->
<?php
get_template_part('template-parts/sections/hero', null, [
    'style' => 'image',
    'theme' => 'dark',
    'height' => 'md',
    'background_image' => get_template_directory_uri() . '/assets/images/hero-default.jpg',
    'overlay' => 'dark-subtle',
    'alignment' => 'center',
    'content_width' => 'normal',
    'headline' => 'Meet Our Team',
    'subheadline' => 'Expert agents and dedicated staff ready to help you with your real estate needs',
    'content' => 'Our experienced professionals are committed to providing exceptional service and expertise to guide you through every step of your real estate journey.',
    'fade_in' => true,
    'section_id' => 'team-hero'
]);
?>

<!-- Team Results Section -->
<div class="hph-container hph-py-xl">

    <?php if ($team_members->have_posts()) : ?>
        
        <div class="hph-grid hph-grid-cols-1 md:hph-grid-cols-2 lg:hph-grid-cols-3 xl:hph-grid-cols-4 hph-gap-lg hph-mb-xl">
            <?php while ($team_members->have_posts()) : $team_members->the_post(); ?>
                <?php 
                $email = get_field('email');
                $phone = get_field('phone');
                $title = get_field('title');
                $position = get_field('position'); // For staff members
                $post_type = get_post_type();
                
                // Use title for agents, position for staff
                $role_title = ($post_type === 'agent') ? $title : $position;
                ?>
                
                <div class="hph-card hph-rounded-lg hph-p-6 hph-bg-white hph-shadow-md hover:hph-shadow-lg hph-transition-shadow">
                    <!-- Clickable area for profile -->
                    <a href="<?php the_permalink(); ?>" class="hph-block hph-text-decoration-none">
                        <!-- Profile photo -->
                        <div class="hph-flex hph-justify-center hph-mb-4">
                            <?php 
                            // Get profile photo with fallbacks
                            $profile_photo = get_field('profile_photo');
                            if ($profile_photo && is_array($profile_photo)) {
                                $photo_url = $profile_photo['sizes']['thumbnail'] ?? $profile_photo['url'];
                            } elseif ($profile_photo && is_numeric($profile_photo)) {
                                $photo_url = wp_get_attachment_image_url($profile_photo, 'thumbnail');
                            } else {
                                // Fallback to featured image
                                $photo_url = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail');
                            }
                            
                            if ($photo_url) : ?>
                                <img src="<?php echo esc_url($photo_url); ?>" 
                                     alt="<?php the_title(); ?>"
                                     class="hph-w-20 hph-h-20 hph-rounded-full hph-object-cover hph-border-3 hph-border-gray-200"
                                     style="object-position: center center;">
                            <?php else : ?>
                                <div class="hph-w-20 hph-h-20 hph-rounded-full hph-bg-gray-200 hph-flex hph-items-center hph-justify-center">
                                    <i class="fas fa-user hph-text-gray-400 hph-text-xl"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Name and title -->
                        <div class="hph-text-center hph-mb-4">
                            <h3 class="hph-text-lg hph-font-semibold hph-text-gray-900 hph-mb-1"><?php the_title(); ?></h3>
                            <?php if ($role_title) : ?>
                                <p class="hph-text-sm hph-text-gray-600"><?php echo esc_html($role_title); ?></p>
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
            <h3 class="hph-text-2xl hph-font-semibold hph-mb-md">No Team Members Found</h3>
            <p class="hph-text-gray-600 hph-mb-lg">There are currently no team members to display.</p>
        </div>
    <?php endif; ?>

</div>

<?php
wp_reset_postdata();
get_footer();
?>
