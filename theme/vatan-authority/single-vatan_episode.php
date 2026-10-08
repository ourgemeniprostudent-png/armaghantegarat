<?php get_header();the_post();$id=get_the_ID();$s=ag_detail_section($id,'episode'); ?>
<div class="ag-interior ag-art-page ag-art-article">
 <section class="ag-article-hero"><div class="wrap ag-page-path"><?php ag_breadcrumb(); ?></div><div class="wrap ag-article-grid"><div data-ag-reveal>
  <span class="eyebrow"><?php ag_text($s,'eyebrow'); ?></span><h1><?php the_title(); ?></h1><p><?php echo esc_html($s['body']); ?></p>
  <div class="ag-article-meta"><time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time><span><?php echo esc_html(get_post_meta($id,'_vatan_duration',true)); ?></span></div>
 </div><?php ag_art_hero($s,'detail'); ?></div></section>
 <section class="ag-section"><div class="wrap ag-editorial" data-article-body>
  <?php ag_episode_player($id); ?>
  <?php $rows=vatan_timestamps(get_post_meta($id,'_vatan_timestamps',true));if($rows): ?><nav class="ag-timestamps" aria-label="بخش‌های گفتگو"><h2><?php echo esc_html($s['timestamps_title']??'بخش‌های گفتگو'); ?></h2><ol><?php foreach($rows as $row)echo '<li><button type="button" data-timestamp="'.esc_attr($row['seconds']).'"><span dir="ltr">'.esc_html($row['time']).'</span><span>'.esc_html($row['title']).'</span></button></li>'; ?></ol></nav><?php endif; ?>
  <?php the_content();$transcript=get_post_meta($id,'_vatan_transcript',true);if($transcript)echo '<section class="ag-transcript"><h2>'.esc_html($s['transcript_title']??'متن گفتگو').'</h2>'.wpautop(esc_html($transcript)).'</section>'; ?>
  <div class="actions"><a class="home-route-link" href="<?php echo esc_url(vatan_url('media/')); ?>"><?php ag_text($s,'label'); ?> <span aria-hidden="true">↖</span></a></div>
 </div></section>
</div>
<?php ag_render_page('episode');ag_related($id);get_footer(); ?>
