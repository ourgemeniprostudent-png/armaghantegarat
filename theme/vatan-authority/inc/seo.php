<?php
if(!defined('ABSPATH'))exit;
function ag_seo_description(){
 $id=get_queried_object_id();$custom=is_singular()?get_post_meta($id,'_vatan_seo_description',true):'';if($custom)return $custom;
 if(is_front_page())return 'ارمغان تجارت وطن؛ واردات مستقیم و تامین عمده قهوه، برنج، خشکبار، ادویه و حبوبات. شناخت محصول و ثبت درخواست همکاری.';
 if(is_tax('vatan_category')){$s=ag_section('hero','category:'.get_queried_object()->slug);return wp_strip_all_tags($s['body']);}
 if(is_home()){$s=ag_section('hero','blog');return wp_strip_all_tags($s['body']);}
 $excerpt=get_the_excerpt($id);if($excerpt)return wp_strip_all_tags($excerpt);
 if(is_page()){$s=ag_section('hero',get_post_field('post_name',$id));return wp_strip_all_tags($s['body']??'');}return '';
}
function ag_breadcrumb_data(){
 $items=[['@type'=>'ListItem','position'=>1,'name'=>'خانه','item'=>home_url('/')]];
 if(is_front_page())return [];
 if(is_singular('post'))$items[]=['@type'=>'ListItem','position'=>2,'name'=>'مجله تجارت','item'=>vatan_url('blog/')];
 if(is_singular('vatan_episode'))$items[]=['@type'=>'ListItem','position'=>2,'name'=>'رسانه','item'=>vatan_url('media/')];
 if(is_tax('vatan_category')||is_singular('vatan_product'))$items[]=['@type'=>'ListItem','position'=>2,'name'=>'محصولات','item'=>vatan_url('products/')];
 if(is_singular('vatan_product')){$terms=get_the_terms(get_queried_object_id(),'vatan_category');if($terms&&!is_wp_error($terms))$items[]=['@type'=>'ListItem','position'=>count($items)+1,'name'=>$terms[0]->name,'item'=>get_term_link($terms[0])];}
 $items[]=['@type'=>'ListItem','position'=>count($items)+1,'name'=>is_tax()?get_queried_object()->name:(is_home()?'مجله تجارت':get_the_title(get_queried_object_id())),'item'=>ag_current_url()];
 return ['@type'=>'BreadcrumbList','itemListElement'=>$items];
}
add_filter('document_title_parts',function($parts){if(vatan_feature('seo')&&is_singular()){$v=get_post_meta(get_queried_object_id(),'_vatan_seo_title',true);if($v)$parts['title']=$v;}return $parts;},20);
add_action('wp_head',function(){
 if(!vatan_feature('seo')||is_404()||is_search())return;
 $desc=wp_trim_words(ag_seo_description(),45);$url=ag_current_url();$id=get_queried_object_id();$image=get_the_post_thumbnail_url($id,'large');
 if(!$image&&is_front_page())$image=vatan_asset('media/hero-poster.jpg');
 if(!$image&&is_singular('post'))$image=ag_media_url(ag_section('article',armaghan_context(get_post($id)))['image']??'');
 echo '<meta name="description" content="'.esc_attr($desc).'"><meta property="og:title" content="'.esc_attr(wp_get_document_title()).'"><meta property="og:description" content="'.esc_attr($desc).'"><meta property="og:type" content="'.(is_singular('post')?'article':'website').'"><meta property="og:url" content="'.esc_url($url).'"><meta name="twitter:card" content="summary_large_image">';
 if($image)echo '<meta property="og:image" content="'.esc_url($image).'">';if(!is_singular())echo '<link rel="canonical" href="'.esc_url($url).'">';
 $org=['@type'=>'Organization','@id'=>home_url('/#organization'),'name'=>'ارمغان تجارت وطن','legalName'=>'شرکت ارمغان تجارت وطن','url'=>home_url('/'),'telephone'=>vatan_option('phone','02191028166'),'logo'=>ag_media_url(ag_section('identity','global')['logo']??'asset:brand-original.png'),'address'=>['@type'=>'PostalAddress','streetAddress'=>vatan_option('address'),'addressLocality'=>'تهران','addressCountry'=>'IR']];if(is_email(vatan_option('public_email')))$org['email']=vatan_option('public_email');
 $graph=[$org,['@type'=>'WebSite','@id'=>home_url('/#website'),'url'=>home_url('/'),'name'=>'ارمغان تجارت وطن','inLanguage'=>'fa-IR','publisher'=>['@id'=>home_url('/#organization')]]];
 if($breadcrumb=ag_breadcrumb_data())$graph[]=$breadcrumb;
 if(is_singular('post')){$article=['@type'=>'BlogPosting','headline'=>get_the_title($id),'description'=>$desc,'datePublished'=>get_post_time('c',true,$id),'dateModified'=>get_post_modified_time('c',true,$id),'author'=>['@type'=>'Organization','name'=>ag_public_author($id)],'publisher'=>['@id'=>home_url('/#organization')],'mainEntityOfPage'=>$url,'inLanguage'=>'fa-IR'];if($image)$article['image']=$image;$graph[]=$article;}
 if(is_singular('vatan_product')){$product=['@type'=>'Product','name'=>get_the_title($id),'description'=>$desc,'url'=>$url];if($image)$product['image']=$image;$specs=[];foreach(explode("\n",get_post_meta($id,'_vatan_specs',true)) as $line){$pair=explode('|',$line,2);if(count($pair)===2)$specs[]=['@type'=>'PropertyValue','name'=>trim($pair[0]),'value'=>trim($pair[1])];}if($specs)$product['additionalProperty']=$specs;$graph[]=$product;}
 if(is_singular('vatan_episode')){
  $episode=['@type'=>'PodcastEpisode','name'=>get_the_title($id),'description'=>$desc,'url'=>$url,'datePublished'=>get_post_time('c',true,$id),'inLanguage'=>'fa-IR','partOfSeries'=>['@type'=>'PodcastSeries','name'=>'رسانه ارمغان تجارت وطن','url'=>vatan_url('media/')]];
  $transcript=get_post_meta($id,'_vatan_transcript',true);if($transcript)$episode['transcript']=$transcript;$graph[]=$episode;
  foreach(['audio'=>'AudioObject','video'=>'VideoObject'] as $field=>$type){$file=get_post_meta($id,'_vatan_'.$field,true);if(!$file)continue;$object=['@type'=>$type,'name'=>get_the_title($id),'description'=>$desc,'contentUrl'=>$file,'uploadDate'=>get_post_time('c',true,$id),'inLanguage'=>'fa-IR'];if($image)$object['thumbnailUrl']=$image;if($transcript)$object['transcript']=$transcript;$graph[]=$object;}
 }
 if(is_page('contact'))$graph[]=['@type'=>'ContactPage','name'=>get_the_title($id),'url'=>$url,'about'=>['@id'=>home_url('/#organization')],'mainEntity'=>['@type'=>'Place','name'=>'دفتر ارمغان تجارت وطن','geo'=>['@type'=>'GeoCoordinates','latitude'=>(float)vatan_option('map_latitude','35.7294807434082'),'longitude'=>(float)vatan_option('map_longitude','51.436744689941406')]]];
 if(is_home()||is_tax('vatan_category')||is_page(['products','media']))$graph[]=['@type'=>'CollectionPage','name'=>wp_get_document_title(),'url'=>$url,'description'=>$desc,'inLanguage'=>'fa-IR'];
 if(is_page('faq')||is_tax('vatan_category')){$context=is_tax()?'category:'.get_queried_object()->slug:'faq';$faqs=[];foreach(ag_sections($context) as $section)if($section['layout']==='faq')foreach($section['values'] as $key=>$value)if(preg_match('/^item_(\d+)_question$/',$key,$m)&&$value!==''&&!empty($section['values']['item_'.$m[1].'_answer']))$faqs[]=['@type'=>'Question','name'=>$value,'acceptedAnswer'=>['@type'=>'Answer','text'=>$section['values']['item_'.$m[1].'_answer']]];if($faqs)$graph[]=['@type'=>'FAQPage','mainEntity'=>$faqs];}
 echo '<script type="application/ld+json">'.wp_json_encode(['@context'=>'https://schema.org','@graph'=>$graph],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT).'</script>';
},15);
add_filter('wp_robots',function($robots){if(ag_query_value('q')||ag_query_value('topic')||ag_query_value('spec_key')||ag_query_value('sort')){$robots['noindex']=true;unset($robots['index']);}return $robots;});
add_filter('wp_sitemaps_posts_query_args',function($args,$type){if($type==='page'){$ids=[];foreach(['inquiry','thank-you','search'] as $slug){$p=get_page_by_path($slug);if($p)$ids[]=$p->ID;}$args['post__not_in']=$ids;}return $args;},10,2);
