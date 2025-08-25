<?php
/**
 * Template part for displaying a message that posts cannot be found
 *
 * @package HappyPlaceTheme
 * @since 3.0.0
 */
?>

<section class="hph-py-2xl hph-text-center hph-bg-gray-50 hph-animate-fade-in-up">
    <div class="hph-container hph-max-w-2xl hph-mx-auto">
        <header class="hph-mb-lg">
            <h1 class="hph-text-3xl hph-font-bold hph-text-gray-900 hph-mb-md"><?php esc_html_e('Nothing here', 'happy-place-theme'); ?></h1>
        </header>

        <div class="hph-space-y-lg">
            <?php if (is_home() && current_user_can('publish_posts')) : ?>
                <p class="hph-text-lg hph-text-gray-600 hph-leading-relaxed"><?php
                printf(
                    wp_kses(
                        /* translators: 1: link to WP admin new post page. */
                        __('Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'happy-place-theme'),
                        array(
                            'a' => array(
                                'href' => array(),
                            ),
                        )
                    ),
                    esc_url(admin_url('post-new.php'))
                );
                ?></p>
            <?php elseif (is_search()) : ?>
                <p class="hph-text-lg hph-text-gray-600 hph-leading-relaxed"><?php esc_html_e('Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'happy-place-theme'); ?></p>
                <div class="hph-mt-lg">
                    <?php get_search_form(); ?>
                </div>
            <?php else : ?>
                <p class="hph-text-lg hph-text-gray-600 hph-leading-relaxed"><?php esc_html_e('It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'happy-place-theme'); ?></p>
                <div class="hph-mt-lg">
                    <?php get_search_form(); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
