<?php
/**
 * Local Events Archive
 */
get_header();
?>
<main class="hpl-archive">
  <div class="wrap">
    <h1>Local Events</h1>
    <?php
    echo do_shortcode('[hpl_this_weekend limit="12"]');
    ?>
  </div>
</main>
<?php get_footer(); ?>
