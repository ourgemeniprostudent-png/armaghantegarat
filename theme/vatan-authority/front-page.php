<?php get_header(); ?>
<section class="trade-hero" aria-labelledby="trade-title" data-trade-hero>
<img class="trade-poster" src="<?php echo esc_url(armaghan_video_asset('hero-poster.jpg')); ?>" alt="کشتی و کانتینرها در بندر؛ مسیر واردات محصولات کشاورزی" width="1920" height="1080" fetchpriority="high">
<video class="trade-video" data-hero-video muted loop playsinline preload="none" aria-hidden="true" tabindex="-1" poster="<?php echo esc_url(armaghan_video_asset('hero-poster.jpg')); ?>" data-av1="<?php echo esc_url(armaghan_video_asset('hero-av1.mp4')); ?>" data-h264="<?php echo esc_url(armaghan_video_asset('hero-h264.mp4')); ?>"></video>
<div class="trade-shade" aria-hidden="true"></div><div class="wrap trade-content"><span class="eyebrow trade-eyebrow">ارمغان تجارت وطن</span><h1 id="trade-title"><?php echo esc_html(get_theme_mod('vatan_trade_line_one','از مبدا،')); ?><br><?php echo esc_html(get_theme_mod('vatan_trade_line_two','تا بازار ایران.')); ?></h1><p><?php echo esc_html(get_theme_mod('vatan_trade_description','واردات مستقیم و تامین عمده قهوه، برنج، خشکبار، ادویه و حبوبات؛ برای بازار و کسب‌وکار ایران.')); ?></p><div class="actions"><a class="button trade-button" href="<?php echo esc_url(vatan_url('inquiry/')); ?>">ثبت درخواست تامین <span aria-hidden="true">←</span></a><a class="button ghost trade-button" href="#import-categories">حوزه‌های واردات</a></div></div>
<div class="wrap trade-bottom"><div class="trade-capabilities"><span>واردات مستقیم</span><i aria-hidden="true"></i><span>تامین عمده</span><i aria-hidden="true"></i><span>حمل دریایی</span></div></div><a class="trade-scroll" href="#import-categories" aria-label="مشاهده حوزه‌های واردات"><span aria-hidden="true">↓</span></a>
</section>
<div class="wrap category-strip"><strong>حوزه‌های تجارت ارمغان</strong><?php foreach(vatan_categories() as $s=>$c) echo '<a href="'.esc_url(vatan_url('products/'.$s.'/')).'">'.esc_html($c[0]).'</a>'; ?></div>
<?php get_template_part('template-parts/product-revolver'); ?>
<?php get_template_part('template-parts/cooperation-gallery'); ?>
<?php get_template_part('template-parts/home-story'); ?>
<?php get_footer(); ?>
