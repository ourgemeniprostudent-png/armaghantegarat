<?php if (!defined('ABSPATH')) exit; $about=ag_section('about','home');$manifesto=ag_section('manifesto','home');$journal=ag_section('journal','home');$contact=ag_section('contact','home'); ?>
<section class="home-about" id="home-about" aria-labelledby="home-about-title">
 <div class="wrap home-about-grid">
  <div class="home-about-copy" data-home-reveal>
   <span class="eyebrow"><?php ag_text($about,'eyebrow'); ?></span>
   <h2 id="home-about-title"><?php ag_text($about,'title'); ?><span><?php ag_text($about,'line1'); ?><br><?php ag_text($about,'line2'); ?></span></h2>
   <p class="home-about-lead"><?php ag_text($about,'lead'); ?></p>
   <p><?php ag_text($about,'body'); ?></p>
   <div class="home-about-signature"><span><?php ag_text($about,'signature_1'); ?></span><i aria-hidden="true"></i><span><?php ag_text($about,'signature_2'); ?></span></div>
   <a class="home-route-link" href="<?php echo esc_url(vatan_url('about/')); ?>"><?php ag_text($about,'label'); ?> <span aria-hidden="true">↗</span></a>
  </div>
  <figure class="home-about-visual" data-home-reveal>
   <div class="home-about-image"><img src="<?php echo esc_url(ag_media_url($about['image'])); ?>" alt="<?php echo esc_attr($about['alt']); ?>" width="900" height="1200" loading="lazy"></div>
   <figcaption><span><?php ag_text($about,'caption'); ?></span><span><?php ag_text($about,'note'); ?></span></figcaption>
   <div class="home-route-rail" aria-hidden="true"><span><?php ag_text($about,'rail_1'); ?></span><i></i><span><?php ag_text($about,'rail_2'); ?></span><i></i><span><?php ag_text($about,'rail_3'); ?></span></div>
   <span class="home-about-corner" aria-hidden="true">↗</span>
  </figure>
 </div>
<?php ag_video($about,'ag-video ag-extra-media'); ?>
</section>
<section class="home-manifesto" id="home-manifesto" aria-labelledby="home-manifesto-title" data-home-manifesto>
 <div class="wrap">
  <div class="home-manifesto-head"><span class="eyebrow"><?php ag_text($manifesto,'eyebrow'); ?></span><span class="home-manifesto-note"><?php ag_text($manifesto,'note'); ?></span></div>
  <h2 id="home-manifesto-title"><span class="manifesto-line manifesto-line-one"><span class="manifesto-word"><?php ag_text($manifesto,'word_1'); ?></span><span class="manifesto-small"><?php ag_text($manifesto,'before_1'); ?> <strong><?php ag_text($manifesto,'strong'); ?></strong><br><?php ag_text($manifesto,'after_1'); ?></span></span><span class="manifesto-line manifesto-line-two"><span class="manifesto-small"><?php ag_text($manifesto,'before_2'); ?></span><span class="manifesto-word manifesto-gold"><?php ag_text($manifesto,'word_2'); ?></span><span class="manifesto-small"><?php ag_text($manifesto,'after_2'); ?><br><?php ag_text($manifesto,'after_3'); ?></span></span></h2>
  <div class="manifesto-path" aria-hidden="true"><i></i><span><?php ag_text($manifesto,'path_1'); ?></span><b></b><span><?php ag_text($manifesto,'path_2'); ?></span><b></b><span><?php ag_text($manifesto,'path_3'); ?></span><i></i></div>
 </div>
<?php if($manifesto['image']||$manifesto['video']){echo '<div class="wrap ag-extra-media">';ag_visual($manifesto);echo '</div>';} ?>
</section>
<?php
$latest = new WP_Query(['post_type'=>'post','post_status'=>'publish','posts_per_page'=>3]);
if ($latest->have_posts()):
$issue_art = ['legumes.webp','rice.webp','coffee.webp'];
$issue_tones = ['navy','ivory','black'];
$issue_numbers = ['۰۱','۰۲','۰۳'];
$issue_index = 0;
?>
<section class="home-journal" id="home-journal" aria-labelledby="home-journal-title">
 <div class="wrap">
  <div class="home-journal-head" data-home-reveal><div><span class="eyebrow"><?php ag_text($journal,'eyebrow'); ?></span><h2 id="home-journal-title"><?php ag_text($journal,'line1'); ?><br><span><?php ag_text($journal,'line2'); ?></span></h2></div><div class="home-journal-intro"><p><?php ag_text($journal,'body'); ?></p><a class="home-route-link" href="<?php echo esc_url(vatan_url('blog/')); ?>"><?php ag_text($journal,'label'); ?> <span aria-hidden="true">↗</span></a></div></div>
  <div class="home-journal-shelf">
   <?php while ($latest->have_posts()): $latest->the_post(); ?>
   <article class="home-issue home-issue-<?php echo esc_attr($issue_tones[$issue_index]); ?>" data-home-reveal>
    <a class="home-issue-link" href="<?php the_permalink(); ?>" aria-labelledby="home-issue-title-<?php echo (int)$issue_index; ?>">
     <div class="home-issue-top"><span><?php ag_text($journal,'cover_label'); ?></span><span><?php echo esc_html($issue_numbers[$issue_index]); ?></span></div>
     <div class="home-issue-art" aria-hidden="true"><span class="home-issue-number"><?php echo esc_html($issue_numbers[$issue_index]); ?></span><img src="<?php echo esc_url(has_post_thumbnail()?get_the_post_thumbnail_url(null,'large'):ag_media_url(ag_section('article','post:'.get_post_field('post_name',get_the_ID()))['image'])); ?>" alt="" width="960" height="960" loading="lazy"></div>
     <div class="home-issue-copy"><h3 id="home-issue-title-<?php echo (int)$issue_index; ?>"><?php the_title(); ?></h3><p><?php echo esc_html(wp_trim_words(get_the_excerpt(),24)); ?></p><span class="home-issue-read"><?php ag_text($journal,'read_label'); ?> <span aria-hidden="true">↗</span></span></div>
    </a>
   </article>
   <?php $issue_index++; endwhile; ?>
  </div>
 </div>
<?php if($journal['image']||$journal['video']){echo '<div class="wrap ag-extra-media">';ag_visual($journal);echo '</div>';} ?>
</section>
<?php wp_reset_postdata(); endif; ?>
<section class="home-contact" id="home-contact" aria-labelledby="home-contact-title">
 <div class="wrap home-contact-grid">
  <div class="home-contact-heading" data-home-reveal><span class="eyebrow"><?php ag_text($contact,'eyebrow'); ?></span><h2 id="home-contact-title"><?php ag_text($contact,'line1'); ?><br><span><?php ag_text($contact,'line2'); ?></span></h2><p><?php ag_text($contact,'body'); ?></p><a class="home-route-link" href="<?php echo esc_url(vatan_url('contact/')); ?>"><?php ag_text($contact,'label'); ?> <span aria-hidden="true">↗</span></a></div>
  <div class="home-contact-details" data-home-reveal>
   <div class="home-contact-phone"><span><?php ag_text($contact,'phone_label'); ?></span><a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/','',vatan_option('phone','02191028166'))); ?>" dir="ltr"><?php echo esc_html(vatan_option('phone','02191028166')); ?><span aria-hidden="true">↗</span></a></div>
   <div class="home-contact-address"><span><?php ag_text($contact,'address_label'); ?></span><p><?php echo esc_html(vatan_option('address','تهران، سهروردی شمالی، کوچه زمانی، پلاک ۱۱، ساختمان ایلیا، طبقه ۳، واحد ۹')); ?></p></div>
   <div class="home-contact-foot"><span class="home-contact-location"><i aria-hidden="true"></i><?php ag_text($contact,'office_label'); ?></span><a href="<?php echo esc_url(vatan_url('contact/')); ?>"><?php ag_text($contact,'address_link_label'); ?> <span aria-hidden="true">←</span></a></div>
  </div>
 </div>
<?php if($contact['image']||$contact['video']){echo '<div class="wrap ag-extra-media">';ag_visual($contact);echo '</div>';} ?>
</section>
