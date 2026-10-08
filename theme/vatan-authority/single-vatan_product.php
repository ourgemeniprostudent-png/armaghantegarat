<?php
get_header();the_post();$id=get_the_ID();$s=ag_detail_section($id,'product');
$terms=get_the_terms($id,'vatan_category');$category=$terms&&!is_wp_error($terms)?$terms[0]:null;
$inquiry=add_query_arg(['category'=>$category?$category->slug:'','product_id'=>$id,'product'=>get_the_title()],vatan_url('inquiry/'));
?>
<div class="ag-interior ag-art-page ag-art-article" data-product-detail>
 <section class="ag-article-hero"><div class="wrap ag-page-path"><?php ag_breadcrumb(); ?></div><div class="wrap ag-article-grid"><div data-ag-reveal>
  <span class="eyebrow"><?php ag_text($s,'eyebrow'); ?></span><h1><?php the_title(); ?></h1>
  <p><?php echo esc_html($s['body']); ?></p>
  <a class="button" href="<?php echo esc_url($inquiry); ?>"><?php ag_text($s,'label'); ?> <span aria-hidden="true">↖</span></a>
  <?php ag_whatsapp(get_the_title(),get_permalink()); ?>
 </div><?php ag_art_hero($s,'detail'); ?></div></section>
 <section class="ag-section"><div class="wrap"><?php ag_product_gallery($id); ?><div class="ag-article-layout">
  <article class="ag-editorial" data-article-body><?php the_content(); ?>
   <?php $specs=get_post_meta($id,'_vatan_specs',true);if($specs): ?><h2><?php ag_text($s,'specs_title'); ?></h2><table class="ag-specs"><tbody>
    <?php foreach(explode("\n",$specs) as $line){$pair=explode('|',$line,2);if(count($pair)===2)echo '<tr><th scope="row">'.esc_html(trim($pair[0])).'</th><td>'.esc_html(trim($pair[1])).'</td></tr>';} ?>
   </tbody></table><?php endif; ?>
   <p class="ag-detail-note"><?php echo esc_html(get_post_meta($id,'_vatan_availability',true)?:$s['availability_note']); ?></p>
  </article>
  <aside class="ag-toc"><h2><?php ag_text($s,'aside_title'); ?></h2><p><?php ag_text($s,'aside_body'); ?></p><a href="<?php echo esc_url(vatan_feature('product_short_form')?'#product-request':$inquiry); ?>"><?php ag_text($s,'aside_label'); ?> <span aria-hidden="true">↖</span></a><?php ag_whatsapp(get_the_title(),get_permalink());if($category)echo '<a href="'.esc_url(get_term_link($category)).'">راهنمای '.esc_html($category->name).'</a>'; ?></aside>
 </div>
 <?php if(vatan_feature('product_short_form')): ?><section class="ag-product-short ag-form-card" id="product-request"><h2><?php echo esc_html($s['form_title']??'درخواست درباره همین محصول'); ?></h2><p><?php echo esc_html($s['form_body']??'محصول و گروه از همین صفحه وارد فرم شده‌اند. مقدار، نیاز و اطلاعات تماس را تکمیل کنید.'); ?></p><?php echo vatan_inquiry_form('product',$id); ?></section><?php endif; ?>
 </div></section>
</div>
<?php ag_render_page('product');ag_related($id);get_footer(); ?>
