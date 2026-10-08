<?php
if (!defined('ABSPATH')) exit;
$gallery_context=$args['context']??'home';
$gallery=ag_section($gallery_context==='home'?'products':'gallery',$gallery_context);
$revolver_products=[];
foreach(vatan_categories() as $slug=>$unused){$fields=ag_section('product-'.$slug,$gallery_context);$revolver_products[]=[$slug,$fields['title'],'',$fields['line1'],$fields['line2'],$fields['body'],$fields['caption'],$fields['alt'],$fields['image'],$fields['video']];}

?>
<section class="product-revolver" id="import-categories" data-product-revolver data-active-index="0" aria-labelledby="import-categories-title">
  <h2 class="sr-only" id="import-categories-title"><?php ag_text($gallery,'title'); ?></h2>
  <div class="revolver-sticky">
    <div class="wrap revolver-stage" tabindex="0" role="region" aria-roledescription="اسلایدر" aria-label="حوزه‌های واردات ارمغان تجارت وطن">
      <div class="revolver-copy">
        <span class="eyebrow revolver-eyebrow"><?php ag_text($gallery,'eyebrow'); ?></span>
        <div class="revolver-copy-stack">
          <?php foreach ($revolver_products as $index => $product): ?>
          <div class="revolver-copy-panel <?php echo $index === 0 ? 'is-active' : ''; ?>" data-revolver-copy="<?php echo (int)$index; ?>" aria-hidden="<?php echo $index === 0 ? 'false' : 'true'; ?>" <?php if ($index !== 0) echo 'inert'; ?>>
            <h3><span class="revolver-name"><?php echo esc_html($product[1]); ?>، </span><span class="revolver-tagline"><?php echo esc_html($product[3]); ?><br><?php echo esc_html($product[4]); ?></span></h3>
            <p><?php echo esc_html($product[5]); ?></p><?php ag_video(['video'=>$product[9],'image'=>$product[8],'alt'=>$product[7]]); ?>
            <div class="revolver-actions"><a class="revolver-action" href="<?php echo esc_url(add_query_arg('category', $product[0], vatan_url('inquiry/'))); ?>"><?php ag_text($gallery,'inquiry_label'); ?> <?php echo esc_html($product[1]); ?><span aria-hidden="true">←</span></a><a class="revolver-detail" href="<?php echo esc_url(vatan_url('products/'.$product[0].'/')); ?>"><?php ag_text($gallery,'detail_label'); ?></a></div>

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
        <button class="revolver-product <?php echo $index === 0 ? 'is-active' : ''; ?>" type="button" data-revolver-item="<?php echo (int)$index; ?>" tabindex="-1" aria-label="نمایش <?php echo esc_attr($product[1]); ?>" aria-hidden="<?php echo $index === 0 ? 'false' : 'true'; ?>"><img src="<?php echo esc_url(ag_media_url($product[8])); ?>" alt="<?php echo esc_attr($product[7]); ?>" width="960" height="960" loading="lazy" decoding="async" draggable="false"></button>
        <?php endforeach; ?>
      </div>
      <div class="revolver-dots" role="group" aria-label="انتخاب گروه محصول">
        <?php foreach ($revolver_products as $index => $product): ?>
        <button type="button" data-revolver-select="<?php echo (int)$index; ?>" aria-label="نمایش <?php echo esc_attr($product[1]); ?>، <?php echo (int)($index+1); ?> از <?php echo (int)count($revolver_products); ?>" aria-current="<?php echo $index === 0 ? 'true' : 'false'; ?>"><i aria-hidden="true"></i></button>
        <?php endforeach; ?>
      </div>
      <div class="revolver-bottom"><a href="<?php echo esc_url(vatan_url('products/')); ?>"><?php ag_text($gallery,'label'); ?> <span aria-hidden="true">↖</span></a><span class="revolver-hint"><?php ag_text($gallery,'hint'); ?></span><div class="revolver-arrows"><button type="button" data-revolver-step="-1" aria-label="محصول قبلی">↑</button><button type="button" data-revolver-step="1" aria-label="محصول بعدی">↓</button></div></div>
    </div>
  </div>
  <noscript><div class="wrap revolver-fallback"><?php foreach ($revolver_products as $product) echo '<a href="'.esc_url(vatan_url('products/'.$product[0].'/')).'">'.esc_html($product[1]).'</a>'; ?></div></noscript>
<?php if($gallery['image']||$gallery['video']){echo '<div class="wrap ag-extra-media">';ag_visual($gallery);echo '</div>';} ?>
</section>
