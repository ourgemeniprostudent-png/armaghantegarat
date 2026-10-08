<?php
if (!defined('ABSPATH')) exit;
$gallery_context=$args['context']??'home';
$gallery=ag_section($gallery_context==='home'?'cooperation':'gallery',$gallery_context);
$flow_steps=[];foreach(['needs','inquiry','review','shipping'] as $slug){$f=ag_section('flow-'.$slug,$gallery_context);$flow_steps[]=[$slug,$f['title'],$f['body'],$f['alt'],$f['image'],$f['video']];}

$fa_number = static fn($value) => str_replace(range(0,9), ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'], (string)$value);
?>
<section class="cooperation-flow" id="business-cooperation" data-cooperation-flow aria-labelledby="cooperation-title">
 <div class="flow-sticky"><div class="wrap flow-stage" tabindex="0" role="region" aria-roledescription="گالری" aria-label="چهار مرحله همکاری تجاری">
  <div class="flow-intro"><span class="eyebrow"><?php ag_text($gallery,'eyebrow'); ?></span><h2 id="cooperation-title"><?php ag_text($gallery,'line1'); ?><br><span class="gold"><?php ag_text($gallery,'line2'); ?></span></h2><a class="flow-about" href="<?php echo esc_url(vatan_url('solutions/')); ?>"><?php ag_text($gallery,'label'); ?> <span aria-hidden="true"><svg class="ag-arrow-icon" viewBox="0 0 24 24" fill="none" width="1em" height="1em" aria-hidden="true"><path d="M20 20 4 4M4 20V4H20" stroke="currentColor" stroke-width="1.3"/></svg></span></a></div>
  <div class="flow-lane" data-flow-lane aria-label="تصاویر مسیر همکاری">
   <?php foreach($flow_steps as $i=>$step): ?>
   <button type="button" class="flow-card <?php echo $i===0?'is-active':''; ?>" data-flow-card="<?php echo $i; ?>" data-title="<?php echo esc_attr($step[1]); ?>" data-description="<?php echo esc_attr($step[2]); ?>" aria-label="نمایش تصویر <?php echo esc_attr($step[1]); ?>" tabindex="<?php echo $i===0?'0':'-1'; ?>" aria-hidden="<?php echo $i===0?'false':'true'; ?>"><img src="<?php echo esc_url(ag_media_url($step[4])); ?>" alt="<?php echo esc_attr($step[3]); ?>" width="900" height="1200" loading="lazy" decoding="async" draggable="false"><span class="flow-expand" aria-hidden="true">⤢</span></button>
   <?php endforeach; ?>
  </div>
  <div class="flow-copy-stack" aria-live="polite" aria-atomic="true"><?php foreach($flow_steps as $i=>$step): ?><div class="flow-copy <?php echo $i===0?'is-active':''; ?>" data-flow-copy="<?php echo $i; ?>" aria-hidden="<?php echo $i===0?'false':'true'; ?>"><span class="flow-step-label">مرحله <?php echo esc_html($fa_number($i+1)); ?> از <?php echo esc_html($fa_number(count($flow_steps))); ?></span><h3><?php echo esc_html($step[1]); ?></h3><p><?php echo esc_html($step[2]); ?></p><?php ag_video(['video'=>$step[5],'image'=>$step[4],'alt'=>$step[3]]); ?></div><?php endforeach; ?></div>
  <div class="flow-controls"><div class="flow-dots" role="group" aria-label="انتخاب مرحله همکاری"><?php foreach($flow_steps as $i=>$step): ?><button type="button" data-flow-select="<?php echo $i; ?>" aria-label="مرحله <?php echo esc_attr($step[1]); ?>" aria-current="<?php echo $i===0?'true':'false'; ?>"><i aria-hidden="true"></i></button><?php endforeach; ?></div><div class="flow-arrows"><button type="button" data-flow-step="-1" aria-label="مرحله قبلی">←</button><button type="button" data-flow-step="1" aria-label="مرحله بعدی">→</button></div><a class="flow-inquiry" href="<?php echo esc_url(vatan_url('inquiry/')); ?>"><?php ag_text($gallery,'inquiry_label'); ?> <span aria-hidden="true"><svg class="ag-arrow-icon" viewBox="0 0 24 24" fill="none" width="1em" height="1em" aria-hidden="true"><path d="M20 20 4 4M4 20V4H20" stroke="currentColor" stroke-width="1.3"/></svg></span></a></div>
  <span class="flow-hint"><?php ag_text($gallery,'hint'); ?></span>
 </div></div>
 <noscript><ol class="wrap flow-fallback"><?php foreach($flow_steps as $step): ?><li><strong><?php echo esc_html($step[1]); ?></strong><p><?php echo esc_html($step[2]); ?></p></li><?php endforeach; ?></ol></noscript>
 <dialog class="flow-dialog" aria-labelledby="flow-dialog-title"><img class="flow-dialog-ambient" data-flow-dialog-ambient alt="" aria-hidden="true"><img class="flow-dialog-image" data-flow-dialog-image alt=""><button type="button" class="flow-dialog-close" data-flow-close aria-label="بستن تصویر">×</button><div class="flow-dialog-copy"><h3 id="flow-dialog-title"></h3><p data-flow-dialog-description></p><a href="<?php echo esc_url(vatan_url('inquiry/')); ?>"><?php ag_text($gallery,'inquiry_label'); ?> <span aria-hidden="true"><svg class="ag-arrow-icon" viewBox="0 0 24 24" fill="none" width="1em" height="1em" aria-hidden="true"><path d="M20 20 4 4M4 20V4H20" stroke="currentColor" stroke-width="1.3"/></svg></span></a></div></dialog>
<?php if($gallery['image']||$gallery['video']){echo '<div class="wrap ag-extra-media">';ag_visual($gallery);echo '</div>';} ?>
</section>
