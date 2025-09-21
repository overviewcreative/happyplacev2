<?php
$start = get_post_meta(get_the_ID(), 'start_datetime', true);
$price = get_post_meta(get_the_ID(), 'price', true);
$city  = get_post_meta(get_the_ID(), 'hpl_city_slug', true);
$start_fmt = $start ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($start)) : '';
?>
<article class="hpl-card">
  <a href="<?php the_permalink(); ?>" class="hpl-card__media">
    <?php if (has_post_thumbnail()) { the_post_thumbnail('medium_large'); } ?>
  </a>
  <div class="hpl-card__body">
    <div class="hpl-card__eyebrow"><?php echo esc_html(ucfirst($city)); ?></div>
    <h3 class="hpl-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
    <div class="hpl-card__meta">
      <?php if ($start_fmt) echo esc_html($start_fmt); ?>
      <?php if ($price) echo ' · ' . esc_html($price); ?>
    </div>
  </div>
</article>
