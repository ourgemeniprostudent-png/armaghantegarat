<?php
get_header();the_post();$context=armaghan_context(get_post());$s=ag_section('article',$context);
if(has_post_thumbnail())$s['image']='attachment:'.get_post_thumbnail_id();
// Count Persian words as well as Latin words.
$article_html=ag_article_html(apply_filters('the_content',get_the_content()));
$minutes=max(1,(int)ceil(count(preg_split('/\s+/u',wp_strip_all_tags(get_the_content()),-1,PREG_SPLIT_NO_EMPTY))/180));
?>
<div class="ag-interior ag-art-page ag-art-article"><div class="ag-reading-progress" aria-hidden="true"></div>
<section class="ag-article-hero"><div class="wrap ag-page-path"><?php ag_breadcrumb(); ?></div><div class="wrap ag-article-grid"><div data-ag-reveal><span class="eyebrow"><?php ag_text($s,'eyebrow'); ?></span><h1><?php the_title(); ?></h1><p><?php echo esc_html($s['body']?:get_the_excerpt()); ?></p><div class="ag-article-meta"><span class="ag-author"><?php echo esc_html(ag_public_author(get_the_ID())); ?></span><time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time><span><?php echo esc_html(vatan_digits_fa($minutes)); ?> <?php ag_text($s,'reading_label'); ?></span></div><div class="ag-topic-tags"><?php foreach(wp_get_post_terms(get_the_ID(),'vatan_topic') as $topic)echo '<a href="'.esc_url(add_query_arg('topic',$topic->slug,vatan_url('blog/'))).'">'.esc_html($topic->name).'</a>'; ?></div></div><?php ag_art_hero($s,$context); ?></div></section>
<section class="ag-section"><div class="wrap ag-article-layout"><article class="ag-editorial" data-article-body><?php echo $article_html; ?><?php wp_link_pages(); ?></article><?php if(vatan_feature('article_toc')): ?><aside class="ag-toc" data-article-toc><h2><?php ag_text($s,'toc_title'); ?></h2><?php ag_article_toc($article_html); ?></aside><?php endif; ?></div></section>
</div>
<?php ag_render_page($context); ?>
<?php if(vatan_feature('related_content')): ?><section class="ag-section"><div class="wrap"><div class="ag-section-head"><h2><?php ag_text($s,'related_title'); ?></h2><a class="home-route-link" href="<?php echo esc_url(vatan_url('blog/')); ?>"><?php ag_text($s,'label'); ?> <span class="ag-symbol" aria-hidden="true">↗</span></a></div><?php $ids=vatan_public_ids(get_post_meta(get_the_ID(),'_vatan_related',true),['post']);$terms=wp_get_post_terms(get_the_ID(),'vatan_topic',['fields'=>'ids']);$args=['post_type'=>'post','post_status'=>'publish','post__not_in'=>[get_the_ID()],'posts_per_page'=>3];if($ids)$args['post__in']=$ids;elseif(!is_wp_error($terms)&&$terms)$args['tax_query']=[['taxonomy'=>'vatan_topic','terms'=>$terms]];$related=new WP_Query($args);if(vatan_feature('related_content'))ag_journal_cards($related);wp_reset_postdata(); ?></div></section><?php endif; ?>
<?php get_footer(); ?>
