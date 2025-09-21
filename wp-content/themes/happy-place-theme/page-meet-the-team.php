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
        // Custom ordering: staff first, then agents, then by title
        $args['orderby'] = [
            'post_type' => 'DESC', // staff comes after agent alphabetically, so DESC puts staff first
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
    'height' => 'lg',
    'is_top_of_page' => true,
    'background_image' => hph_add_fastly_optimization(get_template_directory_uri() . '/assets/images/hero-bg6.jpg', 'full'),
    'ken_burns' => true,
    'ken_burns_direction' => 'zoom-pan',
    'ken_burns_duration' => 40,
    'overlay' => 'dark',
    'alignment' => 'left',
    'content_width' => 'narrow',
    'headline' => 'Meet Our Team',
    'subheadline' => 'Expert agents and dedicated staff ready to help you with your real estate needs',
    'content' => 'Our experienced professionals are committed to providing exceptional service and expertise to guide you through every step of your real estate journey.',
    'buttons' => [
        [
            'text' => 'Contact Our Team',
            'url' => '/contact/',
            'style' => 'white',
            'size' => 'xl',
            'icon' => 'fas fa-users'
        ],
        [
            'text' => 'Schedule Meeting',
            'url' => '#',
            'style' => 'outline-white',
            'size' => 'xl',
            'icon' => 'fas fa-calendar',
            'data_attributes' => 'data-modal-form="general-contact" data-modal-id="hph-consultation-modal" data-modal-title="Schedule Meeting" data-modal-subtitle="Let\'s schedule a time to meet and discuss your real estate needs."'
        ]
    ],
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
                
                <div class="hph-card hph-rounded-lg hph-p-6 hph-bg-white hph-shadow-md hover:hph-shadow-lg hph-transition-shadow <?php echo ($post_type === 'staff') ? 'staff-card' : 'agent-card'; ?>">
                    <!-- Clickable area for profile -->
                    <a href="<?php the_permalink(); ?>" class="hph-block hph-text-decoration-none">
                        <!-- Profile photo -->
                        <div class="hph-flex hph-justify-center hph-mb-4">
                            <?php
                            // Use bridge functions for consistent photo handling
                            $photo_data = null;
                            if ($post_type === 'agent' && function_exists('hpt_get_agent_photo')) {
                                $photo_data = hpt_get_agent_photo(get_the_ID(), 'thumbnail');
                            } elseif ($post_type === 'staff' && function_exists('hpt_get_team_member_photo')) {
                                $photo_data = hpt_get_team_member_photo(get_the_ID(), 'thumbnail');
                            }

                            if ($photo_data && !empty($photo_data['url'])) : ?>
                                <img src="<?php echo esc_url($photo_data['url']); ?>"
                                     alt="<?php echo esc_attr($photo_data['alt'] ?: get_the_title()); ?>"
                                     class="hph-w-xs hph-h-xs hph-rounded-full hph-object-cover hph-border hph-border-gray-200"
                                     style="object-position: center center;">
                            <?php else : ?>
                                <div class="hph-w-xs hph-h-xs hph-rounded-full hph-bg-gray-200 hph-flex hph-items-center hph-justify-center">
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
                               data-hph-stop-propagation="true">
                                <i class="fas fa-phone hph-mr-2"></i>
                                <?php echo esc_html($phone); ?>
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($email) : ?>
                            <a href="mailto:<?php echo esc_attr($email); ?>"
                               class="hph-btn hph-btn-outline hph-btn-sm hph-w-full hph-text-center hph-py-2 hph-mb-2"
                               data-hph-stop-propagation="true">
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

<style>
/* Subtle full border styling using variables */
.staff-card {
    border: 1px solid var(--hph-primary);
}

.staff-card:hover {
    border-color: var(--hph-primary-100);
    box-shadow: 0 10px 25px rgba(16, 185, 129, 0.1);
}

.agent-card {
    border: 1px solid var(--hph-primary-50);
}

.agent-card:hover {
    border-color: var(--hph-primary);
    box-shadow: 0 10px 25px rgba(59, 130, 246, 0.1);
}
</style>

<?php
wp_reset_postdata();
get_footer();
?>
