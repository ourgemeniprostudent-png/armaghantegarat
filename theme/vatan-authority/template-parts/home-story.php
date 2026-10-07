<?php if (!defined('ABSPATH')) exit; ?>
<section class="home-about" id="home-about" aria-labelledby="home-about-title">
 <div class="wrap home-about-grid">
  <div class="home-about-copy" data-home-reveal>
   <span class="eyebrow">۰۳ / درباره ما</span>
   <h2 id="home-about-title">ارمغان تجارت وطن<span>از جهان،<br>برای بازار ایران.</span></h2>
   <p class="home-about-lead">ما واردکننده مستقیم و تامین‌کننده عمده محصولات کشاورزی و غذایی هستیم.</p>
   <p>از قهوه تا برنج، خشکبار، ادویه و حبوبات؛ کار ما پیوند دادن مبدا محصول با نیاز بازار ایران است. واردات در مقیاس عمده، محموله‌های کانتینری و حمل دریایی، بخشی از این مسیر تجاری هستند.</p>
   <div class="home-about-signature"><span>شناخت محصول</span><i aria-hidden="true"></i><span>تجارت در مقیاس عمده</span></div>
   <a class="home-route-link" href="<?php echo esc_url(vatan_url('about/')); ?>">داستان ارمغان تجارت وطن <span aria-hidden="true">↗</span></a>
  </div>
  <figure class="home-about-visual" data-home-reveal>
   <div class="home-about-image"><img src="<?php echo esc_url(vatan_asset('cooperation/shipping.webp')); ?>" alt="نمایی تصویری از حمل دریایی و محموله‌های کانتینری" width="900" height="1200" loading="lazy"></div>
   <figcaption><span>تجارت فراتر از مرزها</span><span>واردات مستقیم / حمل دریایی</span></figcaption>
   <div class="home-route-rail" aria-hidden="true"><span>مبدا</span><i></i><span>مسیر دریایی</span><i></i><span>ایران</span></div>
   <span class="home-about-corner" aria-hidden="true">↗</span>
  </figure>
 </div>
</section>
<section class="home-manifesto" id="home-manifesto" aria-labelledby="home-manifesto-title" data-home-manifesto>
 <div class="wrap">
  <div class="home-manifesto-head"><span class="eyebrow">نگاه ما به تجارت</span><span class="home-manifesto-note">از اولین انتخاب، تا یک رابطه ماندگار.</span></div>
  <h2 id="home-manifesto-title"><span class="manifesto-line manifesto-line-one"><span class="manifesto-word">تجارت،</span><span class="manifesto-small">با یک <strong>انتخاب</strong><br>شروع می‌شود.</span></span><span class="manifesto-line manifesto-line-two"><span class="manifesto-small">با یک</span><span class="manifesto-word manifesto-gold">رابطه</span><span class="manifesto-small">ادامه<br>پیدا می‌کند.</span></span></h2>
  <div class="manifesto-path" aria-hidden="true"><i></i><span>انتخاب</span><b></b><span>اعتماد</span><b></b><span>تداوم</span><i></i></div>
 </div>
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
  <div class="home-journal-head" data-home-reveal><div><span class="eyebrow">۰۴ / مجله تجارت</span><h2 id="home-journal-title">نگاهی نزدیک‌تر.<br><span>انتخابی دقیق‌تر.</span></h2></div><div class="home-journal-intro"><p>برای شروع گفتگویی دقیق‌تر؛ یادداشت‌هایی درباره شناخت محصول، تامین عمده و مسیر تجارت.</p><a class="home-route-link" href="<?php echo esc_url(vatan_url('blog/')); ?>">ورق زدن مجله <span aria-hidden="true">↗</span></a></div></div>
  <div class="home-journal-shelf">
   <?php while ($latest->have_posts()): $latest->the_post(); ?>
   <article class="home-issue home-issue-<?php echo esc_attr($issue_tones[$issue_index]); ?>" data-home-reveal>
    <a class="home-issue-link" href="<?php the_permalink(); ?>" aria-labelledby="home-issue-title-<?php echo (int)$issue_index; ?>">
     <div class="home-issue-top"><span>یادداشت تجارت</span><span><?php echo esc_html($issue_numbers[$issue_index]); ?></span></div>
     <div class="home-issue-art" aria-hidden="true"><span class="home-issue-number"><?php echo esc_html($issue_numbers[$issue_index]); ?></span><img src="<?php echo esc_url(vatan_asset('products/'.$issue_art[$issue_index])); ?>" alt="" width="960" height="960" loading="lazy"></div>
     <div class="home-issue-copy"><h3 id="home-issue-title-<?php echo (int)$issue_index; ?>"><?php the_title(); ?></h3><p><?php echo esc_html(wp_trim_words(get_the_excerpt(),24)); ?></p><span class="home-issue-read">خواندن یادداشت <span aria-hidden="true">↗</span></span></div>
    </a>
   </article>
   <?php $issue_index++; endwhile; ?>
  </div>
 </div>
</section>
<?php wp_reset_postdata(); endif; ?>
<section class="home-contact" id="home-contact" aria-labelledby="home-contact-title">
 <div class="wrap home-contact-grid">
  <div class="home-contact-heading" data-home-reveal><span class="eyebrow">۰۵ / تماس با ما</span><h2 id="home-contact-title">راه ارتباط،<br><span>همیشه روشن.</span></h2><p>برای پرسش درباره مجموعه و محصولات، با دفتر ارمغان تجارت وطن تماس بگیرید.</p><a class="home-route-link" href="<?php echo esc_url(vatan_url('contact/')); ?>">اطلاعات تماس و مسیر دسترسی <span aria-hidden="true">↗</span></a></div>
  <div class="home-contact-details" data-home-reveal>
   <div class="home-contact-phone"><span>شماره دفتر</span><a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/','',vatan_option('phone','02191028166'))); ?>" dir="ltr"><?php echo esc_html(vatan_option('phone','02191028166')); ?><span aria-hidden="true">↗</span></a></div>
   <div class="home-contact-address"><span>نشانی دفتر / تهران</span><p><?php echo esc_html(vatan_option('address','تهران، سهروردی شمالی، کوچه زمانی، پلاک ۱۱، ساختمان ایلیا، طبقه ۳، واحد ۹')); ?></p></div>
   <div class="home-contact-foot"><span class="home-contact-location"><i aria-hidden="true"></i>دفتر ارمغان تجارت وطن</span><a href="<?php echo esc_url(vatan_url('contact/')); ?>">دیدن نشانی <span aria-hidden="true">←</span></a></div>
  </div>
 </div>
</section>
