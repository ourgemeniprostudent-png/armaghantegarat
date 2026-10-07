<?php
if (!defined('ABSPATH')) exit;
$revolver_products = [
    ['coffee','قهوه','COFFEE','در مقیاس ','تجارت.','از نوع دانه و مبدا تا حجم سفارش؛ انتخاب قهوه را با نیاز کسب‌وکار و مسیر تامین هماهنگ کنید.','دانه قهوه برای تامین عمده','دانه‌های قهوه با برگ گیاه، بدون پس‌زمینه'],
    ['rice','برنج','RICE','برای تامین ','عمده.','نوع برنج، مشخصات محصول و مقدار موردنیاز را مشخص کنید تا شرایط واردات و تامین با تیم تجارت بررسی شود.','انتخاب محصول، متناسب با بازار','دانه‌های برنج و خوشه، بدون پس‌زمینه'],
    ['dried-fruits','خشکبار','NUTS & DRIED FRUITS','با انتخابی ','دقیق.','گروه محصول، ویژگی موردنظر و حجم سفارش؛ گفتگو درباره تامین خشکبار از همین اطلاعات شروع می‌شود.','آجیل و خشکبار در سبد تجارت','پسته، بادام، گردو و فندق، بدون پس‌زمینه'],
    ['spices','ادویه','SPICES','از عطر، ','تا تجارت.','برای استعلام ادویه، نوع محصول و نیاز بازار خود را با ما در میان بگذارید؛ جزییات سفارش در گفتگوی مستقیم بررسی می‌شود.','تنوع طعم و عطر، در تامین عمده','دارچین، بادیان، هل و ادویه‌های رنگی، بدون پس‌زمینه'],
    ['legumes','حبوبات','LEGUMES','برای بازار ','ایران.','نوع حبوبات، مقدار تقریبی و مقصد را مشخص کنید؛ این اطلاعات، آغاز بررسی شرایط تامین و همکاری تجاری است.','محصولات کشاورزی برای سفارش عمده','نخود، عدس و لوبیا، بدون پس‌زمینه'],
];
?>
<section class="product-revolver" id="import-categories" data-product-revolver data-active-index="0" aria-labelledby="import-categories-title">
  <h2 class="sr-only" id="import-categories-title">از قهوه تا محصولات کشاورزی.</h2>
  <div class="revolver-sticky">
    <div class="wrap revolver-stage" tabindex="0" role="region" aria-roledescription="اسلایدر" aria-label="حوزه‌های واردات ارمغان تجارت وطن">
      <div class="revolver-copy">
        <span class="eyebrow revolver-eyebrow">۰۱ / حوزه‌های واردات</span>
        <div class="revolver-copy-stack">
          <?php foreach ($revolver_products as $index => $product): ?>
          <div class="revolver-copy-panel <?php echo $index === 0 ? 'is-active' : ''; ?>" data-revolver-copy="<?php echo (int)$index; ?>" aria-hidden="<?php echo $index === 0 ? 'false' : 'true'; ?>" <?php if ($index !== 0) echo 'inert'; ?>>
            <h3><span class="revolver-name"><?php echo esc_html($product[1]); ?>، </span><span class="revolver-tagline"><?php echo esc_html($product[3]); ?><br><?php echo esc_html($product[4]); ?></span></h3>
            <p><?php echo esc_html($product[5]); ?></p>
            <div class="revolver-actions"><a class="revolver-action" href="<?php echo esc_url(add_query_arg('category', $product[0], vatan_url('inquiry/'))); ?>">استعلام عمده <?php echo esc_html($product[1]); ?><span aria-hidden="true">←</span></a><a class="revolver-detail" href="<?php echo esc_url(vatan_url('products/'.$product[0].'/')); ?>">شناخت محصول</a></div>

          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="revolver-meta-stack">
        <?php foreach ($revolver_products as $index => $product): ?>
        <div class="revolver-finish <?php echo $index === 0 ? 'is-active' : ''; ?>" data-revolver-meta="<?php echo (int)$index; ?>" aria-hidden="<?php echo $index === 0 ? 'false' : 'true'; ?>"><span class="revolver-count" dir="rtl"><?php echo esc_html(str_replace(range(0,9), ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'], ($index + 1).' از '.count($revolver_products))); ?></span><span class="revolver-caption"><?php echo esc_html($product[6]); ?></span></div>
        <?php endforeach; ?>
      </div>
      <div class="revolver-lane" aria-label="تصاویر محصولات" data-revolver-lane>
        <?php foreach ($revolver_products as $index => $product): ?>
        <button class="revolver-product <?php echo $index === 0 ? 'is-active' : ''; ?>" type="button" data-revolver-item="<?php echo (int)$index; ?>" tabindex="-1" aria-label="نمایش <?php echo esc_attr($product[1]); ?>" aria-hidden="<?php echo $index === 0 ? 'false' : 'true'; ?>"><img src="<?php echo esc_url(add_query_arg('ver', filemtime(get_template_directory().'/assets/products/'.$product[0].'.webp'), vatan_asset('products/'.$product[0].'.webp'))); ?>" alt="<?php echo esc_attr($product[7]); ?>" width="960" height="960" loading="lazy" decoding="async" draggable="false"></button>
        <?php endforeach; ?>
      </div>
      <div class="revolver-dots" role="group" aria-label="انتخاب گروه محصول">
        <?php foreach ($revolver_products as $index => $product): ?>
        <button type="button" data-revolver-select="<?php echo (int)$index; ?>" aria-label="نمایش <?php echo esc_attr($product[1]); ?>، <?php echo (int)($index+1); ?> از <?php echo (int)count($revolver_products); ?>" aria-current="<?php echo $index === 0 ? 'true' : 'false'; ?>"><i aria-hidden="true"></i></button>
        <?php endforeach; ?>
      </div>
      <div class="revolver-bottom"><a href="<?php echo esc_url(vatan_url('products/')); ?>">همه حوزه‌های واردات <span aria-hidden="true">↗</span></a><span class="revolver-hint">اسکرول · کشیدن · انتخاب محصول</span><div class="revolver-arrows"><button type="button" data-revolver-step="-1" aria-label="محصول قبلی">↑</button><button type="button" data-revolver-step="1" aria-label="محصول بعدی">↓</button></div></div>
    </div>
  </div>
  <noscript><div class="wrap revolver-fallback"><?php foreach ($revolver_products as $product) echo '<a href="'.esc_url(vatan_url('products/'.$product[0].'/')).'">'.esc_html($product[1]).'</a>'; ?></div></noscript>
</section>
