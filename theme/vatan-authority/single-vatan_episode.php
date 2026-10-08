<?php get_header();the_post();$id=get_the_ID();$s=ag_detail_section($id,'episode'); ?>
<div class="at-media am-studio">
 <section class="ms-opening ms-detail-opening"><div class="wrap">
 <?php ag_breadcrumb();$item=ag_ms_item(get_post());$item['body']=$s['body']?:$item['body'];if(!empty($s['image']))$item['image']=ag_media_url($s['image']);echo '<div id="library">';ag_ms_feature($item,'h1');echo '</div>'; ?>
 </div></section>
 <section class="am-section ms-episode-body"><div class="wrap ag-editorial" data-article-body>
  <?php $rows=vatan_timestamps(get_post_meta($id,'_vatan_timestamps',true));if($rows): ?><nav class="ag-timestamps" aria-label="بخش‌های گفتگو"><h2><?php echo esc_html($s['timestamps_title']??'بخش‌های گفتگو'); ?></h2><ol><?php foreach($rows as $row)echo '<li><button type="button" data-timestamp="'.esc_attr($row['seconds']).'"><span dir="ltr">'.esc_html($row['time']).'</span><span>'.esc_html($row['title']).'</span></button></li>'; ?></ol></nav><?php endif; ?>
  <?php the_content();$transcript=get_post_meta($id,'_vatan_transcript',true);if($transcript)echo '<section class="ag-transcript"><h2>'.esc_html($s['transcript_title']??'متن گفتگو').'</h2>'.wpautop(esc_html($transcript)).'</section>'; ?>
  <div class="actions"><a class="home-route-link" href="<?php echo esc_url(vatan_url('media/')); ?>"><?php ag_text($s,'label'); ?> <span aria-hidden="true"><svg class="ag-arrow-icon" viewBox="0 0 24 24" fill="none" width="1em" height="1em" aria-hidden="true"><path d="M20 20 4 4M4 20V4H20" stroke="currentColor" stroke-width="1.3"/></svg></span></a></div>
 </div></section>
</div>
<?php ag_render_page('episode');ag_related($id);get_footer(); ?>
