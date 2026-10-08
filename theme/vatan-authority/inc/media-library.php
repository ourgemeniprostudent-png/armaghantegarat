<?php
/** Native public media and explicitly marked, editable presentation samples. */
if(!defined('ABSPATH'))exit;
function ag_media_sample_key(){$key=get_query_var('ag_media_sample');return is_string($key)?sanitize_key($key):'';}
function ag_media_samples(){
    $items=[];foreach(ag_sections('media') as $id=>$section)if($section['layout']==='media-sample')$items[$id]=$section['values'];return $items;
}
function ag_media_has_episodes(){return (int)wp_count_posts('vatan_episode')->publish>0;}
function ag_media_show_samples(){return vatan_feature('media_samples')&&!ag_media_has_episodes();}
function ag_media_sample_url($id){return home_url('/media/sample/'.sanitize_title($id).'/');}
add_filter('query_vars',function($vars){$vars[]='ag_media_sample';return $vars;});
add_filter('template_include',function($template){
    $key=ag_media_sample_key();if(!$key)return $template;
    if(!isset(ag_media_samples()[$key])||!ag_media_show_samples()){global $wp_query;$wp_query->set_404();status_header(404);return get_404_template();}
    status_header(200);return get_template_directory().'/media-sample.php';
});
add_filter('wp_robots',function($robots){if(ag_media_sample_key()){$robots['noindex']=true;unset($robots['index']);}return $robots;});
add_filter('pre_get_document_title',function($title){$key=ag_media_sample_key();$s=ag_media_samples()[$key]??null;return $s?$s['title'].' | نمونه نمایشی | ارمغان تجارت وطن':$title;});
function ag_media_hero($s,$title,$body,$sample=false){
    $image=ag_media_url($s['background_image']??'')?:ag_media_url($s['image']??'');
    echo '<section class="am-hero"><img class="am-hero-photo" '.ag_image_attrs($image,'100vw',1280).' alt="'.esc_attr($s['alt']??'').'" fetchpriority="high"><div class="wrap am-hero-copy">';
    if(is_singular('vatan_episode'))ag_breadcrumb();
    elseif(vatan_feature('breadcrumbs'))echo '<nav class="am-path" aria-label="مسیر صفحه"><a href="'.esc_url(home_url('/')).'">خانه</a><span aria-hidden="true">/</span>'.($sample?'<a href="'.esc_url(home_url('/media/')).'">رسانه</a><span aria-hidden="true">/</span>':'').'<span aria-current="page">'.esc_html($sample?'نمونه نمایشی':'رسانه').'</span></nav>';
    echo '<span class="eyebrow">'.esc_html($sample?($s['sample_label']??'نمونه نمایشی'):($s['eyebrow']??'رسانه')).'</span><h1>'.esc_html($title).'</h1><p>'.esc_html($body).'</p><a class="am-link" href="#'.($sample?'listen':'library').'">'.esc_html($s['scroll_label']??'ادامه روایت').' <svg class="am-arrow" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 20 4 4M4 20V4H20" stroke="currentColor" stroke-width="1.4"/></svg></a></div></section>';
}
function ag_render_media(){ag_media_studio();}
function ag_render_media_sample($key){ag_media_studio_sample($key);}
function ag_home_media(){
 if(!vatan_feature('home_media'))return;$s=ag_section('media-highlight','home');$posts=get_posts(['post_type'=>'vatan_episode','post_status'=>'publish','numberposts'=>1,'meta_key'=>'_vatan_featured','meta_value'=>'1']);
 if(!$posts)$posts=get_posts(['post_type'=>'vatan_episode','post_status'=>'publish','numberposts'=>1]);
 $post=$posts[0]??null;if(!$post&&!ag_media_show_samples())return;
 $samples=ag_media_samples();$key=array_key_first($samples);$item=$post?ag_ms_item($post):ag_ms_sample($key,$samples[$key]);if(!empty($s['image'])){$item['image']=$s['image'];$item['alt']=$s['alt']??$item['title'];}
 echo '<section class="at-media am-studio ms-home" id="home-media" aria-labelledby="home-media-title"><div class="wrap"><span class="eyebrow">'.esc_html($s['eyebrow']).'</span><h2 id="home-media-title">'.esc_html($s['title']).'</h2><p>'.esc_html($s['body']).'</p>';ag_ms_feature($item,'h3');echo '<a class="ms-detail-link" href="'.esc_url(home_url('/media/')).'">'.esc_html($s['label']);ag_ms_icon('arrow');echo '</a></div>';ag_video($s);echo '</section>';
}
