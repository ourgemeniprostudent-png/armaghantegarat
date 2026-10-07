<?php
if (!defined('ABSPATH')) exit;
$flow_steps = [
 ['needs','شناخت نیاز','گروه محصول، ویژگی موردنظر، حجم تقریبی و مقصد را مشخص کنید؛ مسیر همکاری از شناخت نیاز کسب‌وکار شما شروع می‌شود.','نمونه‌های قهوه، برنج، پسته و ادویه برای بررسی نیاز سفارش'],
 ['inquiry','ثبت استعلام','اطلاعات سفارش و راه تماس را در فرم ثبت کنید. درخواست شما در سامانه نگهداری می‌شود و کد پیگیری در اختیارتان قرار می‌گیرد.','مدارک سفارش و نمونه‌های محصول روی میز یک دفتر تجاری، بدون حضور افراد'],
 ['review','گفتگو و بررسی','درباره مشخصات محصول و شرایط موردنیاز گفتگو می‌کنیم تا جزییات درخواست برای بررسی تامین روشن باشد.','ترازو، ذره‌بین و نمونه‌های محصول برای بررسی مشخصات'],
 ['shipping','هماهنگی همکاری','جزییات پیشنهاد، سفارش و هماهنگی‌های بعدی با توافق دو طرف تعیین می‌شود؛ قدم‌به‌قدم تا شکل گرفتن همکاری تجاری.','کشتی کانتینری، جرثقیل بندر و محموله‌های کشاورزی، بدون حضور افراد'],
];
$fa_number = static fn($value) => str_replace(range(0,9), ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'], (string)$value);
?>
<section class="cooperation-flow" id="business-cooperation" data-cooperation-flow aria-labelledby="cooperation-title">
 <div class="flow-sticky"><div class="wrap flow-stage" tabindex="0" role="region" aria-roledescription="گالری" aria-label="چهار مرحله همکاری تجاری">
  <div class="flow-intro"><span class="eyebrow">۰۲ / همکاری تجاری</span><h2 id="cooperation-title">از نیاز شما،<br><span class="gold">تا یک همکاری ماندگار.</span></h2><a class="flow-about" href="<?php echo esc_url(vatan_url('solutions/')); ?>">مسیر همکاری با ارمغان تجارت وطن <span aria-hidden="true">↗</span></a></div>
  <div class="flow-lane" data-flow-lane aria-label="تصاویر مسیر همکاری">
   <?php foreach($flow_steps as $i=>$step): ?>
   <button type="button" class="flow-card <?php echo $i===0?'is-active':''; ?>" data-flow-card="<?php echo $i; ?>" data-title="<?php echo esc_attr($step[1]); ?>" data-description="<?php echo esc_attr($step[2]); ?>" aria-label="نمایش تصویر <?php echo esc_attr($step[1]); ?>" tabindex="<?php echo $i===0?'0':'-1'; ?>" aria-hidden="<?php echo $i===0?'false':'true'; ?>"><img src="<?php echo esc_url(add_query_arg('ver',filemtime(get_template_directory().'/assets/cooperation/'.$step[0].'.webp'),vatan_asset('cooperation/'.$step[0].'.webp'))); ?>" alt="<?php echo esc_attr($step[3]); ?>" width="900" height="1200" loading="lazy" decoding="async" draggable="false"><span class="flow-expand" aria-hidden="true">⤢</span></button>
   <?php endforeach; ?>
  </div>
  <div class="flow-copy-stack" aria-live="polite" aria-atomic="true"><?php foreach($flow_steps as $i=>$step): ?><div class="flow-copy <?php echo $i===0?'is-active':''; ?>" data-flow-copy="<?php echo $i; ?>" aria-hidden="<?php echo $i===0?'false':'true'; ?>"><span class="flow-step-label">مرحله <?php echo esc_html($fa_number($i+1)); ?> از <?php echo esc_html($fa_number(count($flow_steps))); ?></span><h3><?php echo esc_html($step[1]); ?></h3><p><?php echo esc_html($step[2]); ?></p></div><?php endforeach; ?></div>
  <div class="flow-controls"><div class="flow-dots" role="group" aria-label="انتخاب مرحله همکاری"><?php foreach($flow_steps as $i=>$step): ?><button type="button" data-flow-select="<?php echo $i; ?>" aria-label="مرحله <?php echo esc_attr($step[1]); ?>" aria-current="<?php echo $i===0?'true':'false'; ?>"><i aria-hidden="true"></i></button><?php endforeach; ?></div><div class="flow-arrows"><button type="button" data-flow-step="-1" aria-label="مرحله قبلی">←</button><button type="button" data-flow-step="1" aria-label="مرحله بعدی">→</button></div><a class="flow-inquiry" href="<?php echo esc_url(vatan_url('inquiry/')); ?>">ثبت درخواست همکاری <span aria-hidden="true">↗</span></a></div>
  <span class="flow-hint">اسکرول کنید یا تصاویر را بکشید</span>
 </div></div>
 <noscript><ol class="wrap flow-fallback"><?php foreach($flow_steps as $step): ?><li><strong><?php echo esc_html($step[1]); ?></strong><p><?php echo esc_html($step[2]); ?></p></li><?php endforeach; ?></ol></noscript>
 <dialog class="flow-dialog" aria-labelledby="flow-dialog-title"><img class="flow-dialog-ambient" data-flow-dialog-ambient alt="" aria-hidden="true"><img class="flow-dialog-image" data-flow-dialog-image alt=""><button type="button" class="flow-dialog-close" data-flow-close aria-label="بستن تصویر">×</button><div class="flow-dialog-copy"><h3 id="flow-dialog-title"></h3><p data-flow-dialog-description></p><a href="<?php echo esc_url(vatan_url('inquiry/')); ?>">ثبت درخواست همکاری <span aria-hidden="true">↗</span></a></div></dialog>
</section>
