<?php
get_header();the_post();$context=armaghan_context(get_post());$s=ag_section('article',$context);
if(has_post_thumbnail())$s['image']='attachment:'.get_post_thumbnail_id();
// Count Persian words as well as Latin words.
$minutes=max(1,(int)ceil(count(preg_split('/\s+/u',wp_strip_all_tags(get_the_content()),-1,PREG_SPLIT_NO_EMPTY))/180));
?>
<div class="ag-interior ag-art-page ag-art-article"><div class="ag-reading-progress" aria-hidden="true"></div>
<section class="ag-article-hero"><div class="wrap ag-article-grid"><div data-ag-reveal><div class="breadcrumb"><a href="<?php echo esc_url(vatan_url('blog/')); ?>">مجله تجارت</a> / یادداشت</div><span class="eyebrow"><?php ag_text($s,'eyebrow'); ?></span><h1><?php the_title(); ?></h1><p><?php echo esc_html($s['body']?:get_the_excerpt()); ?></p><div class="ag-article-meta"><time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time><span><?php echo esc_html(vatan_digits_fa($minutes)); ?> <?php ag_text($s,'reading_label'); ?></span></div></div><?php ag_art_hero($s,$context); ?></div></section>
<section class="ag-section"><div class="wrap ag-article-layout"><article class="ag-editorial" data-article-body><?php the_content(); ?><?php wp_link_pages(); ?></article><aside class="ag-toc" data-article-toc><h2><?php ag_text($s,'toc_title'); ?></h2></aside></div></section>
</div>
<?php ag_render_page($context); ?>
<section class="ag-section"><div class="wrap"><div class="ag-section-head"><h2><?php ag_text($s,'related_title'); ?></h2><a class="home-route-link" href="<?php echo esc_url(vatan_url('blog/')); ?>"><?php ag_text($s,'label'); ?> <span class="ag-symbol" aria-hidden="true">↗</span></a></div><?php $related=new WP_Query(['post_type'=>'post','post_status'=>'publish','post__not_in'=>[get_the_ID()],'posts_per_page'=>3]);ag_journal_cards($related);wp_reset_postdata(); ?></div></section>
<?php get_footer(); ?>
