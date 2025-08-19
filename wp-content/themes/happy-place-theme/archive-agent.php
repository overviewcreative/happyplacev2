<?php
/**
 * Archive Template for Agents
 * 
 * Displays all agents with filtering capabilities
 *
 * @package HappyPlaceTheme
 */

get_header(); ?>

<div class="archive-header bg-gray-50 py-12">
    <div class="container">
        <div class="archive-header-content text-center">
            <h1 class="archive-title text-3xl font-bold mb-4">
                <?php 
                if (is_search()) {
                    printf(esc_html__('Search Results for "%s"', 'happy-place-theme'), get_search_query());
                } else {
                    post_type_archive_title();
                }
                ?>
            </h1>
            
            <?php if (have_posts()) : ?>
                <p class="archive-count text-gray-600 mb-4">
                    <?php
                    global $wp_query;
                    $total = $wp_query->found_posts;
                    printf(
                        _n('%d agent found', '%d agents found', $total, 'happy-place-theme'),
                        $total
                    );
                    ?>
                </p>
            <?php endif; ?>
            
            <p class="archive-description text-gray-600 max-w-2xl mx-auto">
                <?php esc_html_e('Meet our experienced real estate professionals dedicated to helping you find your perfect home.', 'happy-place-theme'); ?>
            </p>
        </div>
    </div>
</div>

<div class="agent-filters bg-white border-b border-gray-200 py-6">
    <div class="container">
        <form class="filter-form" method="GET" action="<?php echo esc_url(get_post_type_archive_link('agent')); ?>">
            
            <div class="filter-row grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="filter-group">
                    <label for="agent-search" class="block text-sm font-medium text-gray-700 mb-2"><?php esc_html_e('Search Agents', 'happy-place-theme'); ?></label>
                    <input type="text" id="agent-search" name="search" value="<?php echo esc_attr(get_query_var('search')); ?>" placeholder="<?php esc_attr_e('Enter agent name or specialty', 'happy-place-theme'); ?>" class="form-input w-full">
                </div>
                
                <!-- Specialty -->
                <div class="filter-group">
                    <label for="specialty" class="block text-sm font-medium text-gray-700 mb-2"><?php esc_html_e('Specialty', 'happy-place-theme'); ?></label>
                    <select id="specialty" name="specialty" class="form-select w-full">
                        <option value=""><?php esc_html_e('All Specialties', 'happy-place-theme'); ?></option>
                        <?php
                        $specialties = array(
                            'buyer_agent' => 'Buyer\'s Agent',
                            'listing_agent' => 'Listing Agent',
                            'luxury_homes' => 'Luxury Homes',
                            'first_time_buyers' => 'First Time Buyers',
                            'investment' => 'Investment Properties',
                            'commercial' => 'Commercial Real Estate',
                            'relocation' => 'Relocation Specialist'
                        );
                        $current_specialty = get_query_var('specialty');
                        foreach ($specialties as $key => $specialty) :
                        ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($current_specialty, $key); ?>>
                                <?php echo esc_html($specialty); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Languages -->
                <div class="filter-group">
                    <label for="languages" class="block text-sm font-medium text-gray-700 mb-2"><?php esc_html_e('Languages', 'happy-place-theme'); ?></label>
                    <select id="languages" name="languages" class="form-select w-full">
                        <option value=""><?php esc_html_e('All Languages', 'happy-place-theme'); ?></option>
                        <?php
                        $languages = array(
                            'english' => 'English',
                            'spanish' => 'Spanish',
                            'french' => 'French',
                            'chinese' => 'Chinese',
                            'german' => 'German',
                            'italian' => 'Italian'
                        );
                        $current_language = get_query_var('languages');
                        foreach ($languages as $key => $language) :
                        ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($current_language, $key); ?>>
                                <?php echo esc_html($language); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="hp-filter-group">
                    <button type="submit" class="hp-btn hp-btn-primary">
                        <i class="fas fa-search"></i>
                        <?php esc_html_e('Search Agents', 'happy-place-theme'); ?>
                    </button>
                </div>
            </div>
            
        </form>
    </div>
</div>

<main id="primary" class="hp-main site-main">
    <div class="hp-container">
        
        <?php if (have_posts()) : ?>
            
            <div class="hp-agents-grid">
                <?php while (have_posts()) : the_post(); ?>
                    <?php get_template_part('template-parts/agent-card'); ?>
                <?php endwhile; ?>
            </div>
            
            <?php
            // Pagination
            the_posts_pagination(array(
                'mid_size'  => 2,
                'prev_text' => '<i class="fas fa-chevron-left"></i> ' . esc_html__('Previous', 'happy-place-theme'),
                'next_text' => esc_html__('Next', 'happy-place-theme') . ' <i class="fas fa-chevron-right"></i>',
                'class'     => 'hp-pagination',
            ));
            ?>
            
        <?php else : ?>
            
            <div class="hp-no-agents">
                <div class="hp-no-agents-content">
                    <i class="fas fa-user-tie hp-no-agents-icon"></i>
                    <h2><?php esc_html_e('No Agents Found', 'happy-place-theme'); ?></h2>
                    <p><?php esc_html_e('Sorry, no agents match your search criteria. Try adjusting your filters or search terms.', 'happy-place-theme'); ?></p>
                    
                    <div class="hp-no-agents-actions">
                        <a href="<?php echo esc_url(get_post_type_archive_link('agent')); ?>" class="hp-btn hp-btn-primary">
                            <?php esc_html_e('View All Agents', 'happy-place-theme'); ?>
                        </a>
                        
                        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="hp-btn hp-btn-secondary">
                            <?php esc_html_e('Contact Us', 'happy-place-theme'); ?>
                        </a>
                    </div>
                </div>
            </div>
            
        <?php endif; ?>
        
    </div>
</main>

<style>
.hp-agent-filters {
    background: #f8fafc;
    padding: 2rem 0;
    margin-bottom: 2rem;
    border: 1px solid #e2e8f0;
}

.hp-agents-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.hp-no-agents {
    text-align: center;
    padding: 4rem 2rem;
}

.hp-no-agents-icon {
    font-size: 4rem;
    color: var(--hp-primary);
    margin-bottom: 1rem;
}

.hp-no-agents h2 {
    font-size: 1.875rem;
    margin-bottom: 1rem;
    color: var(--hp-text);
}

.hp-no-agents p {
    font-size: 1.125rem;
    color: #6b7280;
    margin-bottom: 2rem;
}

.hp-no-agents-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .hp-agents-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php get_footer(); ?>
