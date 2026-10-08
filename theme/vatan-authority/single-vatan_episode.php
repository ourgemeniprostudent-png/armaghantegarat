<?php get_header();the_post();$id=get_the_ID();$s=ag_detail_section($id,'episode'); ?>
<div class="at-media">
 <?php $cover=get_the_post_thumbnail_url($id,'full')?:ag_media_url($s['image']??'')?:vatan_asset('podcasts/studio.webp');ag_media_hero(['image'=>$cover,'alt'=>get_the_title(),'eyebrow'=>$s['eyebrow']??'گفتگو','scroll_label'=>'شنیدن و خواندن'],get_the_title(),$s['body']?:get_the_excerpt()); ?>
 <div class="wrap am-episode-meta"><time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time><span><?php echo esc_html(get_post_meta($id,'_vatan_duration',true)); ?></span></div>
 <section class="am-section" id="library"><div class="wrap ag-editorial" data-article-body>
  <?php ag_episode_player($id); ?>
  <?php $rows=vatan_timestamps(get_post_meta($id,'_vatan_timestamps',true));if($rows): ?><nav class="ag-timestamps" aria-label="بخش‌های گفتگو"><h2><?php echo esc_html($s['timestamps_title']??'بخش‌های گفتگو'); ?></h2><ol><?php foreach($rows as $row)echo '<li><button type="button" data-timestamp="'.esc_attr($row['seconds']).'"><span dir="ltr">'.esc_html($row['time']).'</span><span>'.esc_html($row['title']).'</span></button></li>'; ?></ol></nav><?php endif; ?>
  <?php the_content();$transcript=get_post_meta($id,'_vatan_transcript',true);if($transcript)echo '<section class="ag-transcript"><h2>'.esc_html($s['transcript_title']??'متن گفتگو').'</h2>'.wpautop(esc_html($transcript)).'</section>'; ?>
  <div class="actions"><a class="home-route-link" href="<?php echo esc_url(vatan_url('media/')); ?>"><?php ag_text($s,'label'); ?> <span aria-hidden="true">↖</span></a></div>
 </div></section>
</div>
<?php ag_render_page('episode');ag_related($id);get_footer(); ?>
