<?php
/** Native public media and explicitly marked, editable presentation samples. */
if(!defined('ABSPATH'))exit;
function ag_media_sample_key(){$key=get_query_var('ag_media_sample');return is_string($key)?sanitize_key($key):'';}
function ag_media_samples(){
    $items=[];foreach(ag_sections('media') as $id=>$section)if($section['layout']==='media-sample')$items[$id]=$section['values'];return $items;
}
function ag_media_has_episodes(){return (int)wp_count_posts('vatan_episode')->publish>0;}
function ag_media_show_samples(){return vatan_feature('media_samples')&&!ag_media_has_episodes();}

function ag_media_view(){$kind=get_query_var('ag_media_view');return in_array($kind,['audio','video'],true)?$kind:'';}
function ag_media_video_samples(){$items=[];foreach(ag_sections('media') as $id=>$section)if($section['layout']==='media-video-sample')$items[$id]=$section['values'];return $items;}
function ag_media_show_kind_samples($kind){
 if(!vatan_feature('media_samples'))return false;
 return !get_posts(['post_type'=>'vatan_episode','post_status'=>'publish','numberposts'=>1,'fields'=>'ids','meta_query'=>[['key'=>'_vatan_'.$kind,'value'=>'','compare'=>'!=']]]);
}
function ag_media_sample_catalog(){return ag_media_view()==='video'?ag_media_video_samples():ag_media_samples();}
function ag_media_sample_url($id,$kind='audio'){return vatan_media_url($kind).'sample/'.sanitize_title($id).'/';}
function ag_media_public_routes(){
 $routes=[vatan_media_url('audio')];if(vatan_feature('video_library'))$routes[]=vatan_media_url('video');
 foreach(['audio','video'] as $kind){if($kind==='video'&&!vatan_feature('video_library'))continue;if(!ag_media_show_kind_samples($kind))continue;
  foreach(array_keys($kind==='audio'?ag_media_samples():ag_media_video_samples()) as $key){$routes[]=ag_media_sample_url($key,$kind);if($kind==='audio')$routes[]=home_url('/media/sample/'.$key.'/');}
 }
 foreach(get_posts(['post_type'=>'vatan_episode','post_status'=>'publish','numberposts'=>-1]) as $post)foreach(['audio','video'] as $kind)if(get_post_meta($post->ID,'_vatan_'.$kind,true))$routes[]=vatan_media_url($kind,$post);
 return array_values(array_unique($routes));
}
add_filter('query_vars',function($vars){$vars[]='ag_media_sample';return $vars;});
add_filter('template_include',function($template){
 $kind=ag_media_view();$key=ag_media_sample_key();$invalid=false;
 if($key)$invalid=!isset(ag_media_sample_catalog()[$key])||!ag_media_show_kind_samples($kind?:'audio');
 if($kind==='video'&&is_page('media')&&!vatan_feature('video_library'))$invalid=true;
 if(is_singular('vatan_episode')&&$kind&&!get_post_meta(get_queried_object_id(),'_vatan_'.$kind,true))$invalid=true;
 if($invalid){global $wp_query;$wp_query->set_404();status_header(404);return get_404_template();}
 if($key){status_header(200);return get_template_directory().'/media-sample.php';}
 return $template;
});
add_filter('redirect_canonical',function($redirect){return ag_media_view()||ag_media_sample_key()?false:$redirect;});
add_filter('get_canonical_url',function($url){return ag_media_view()||ag_media_sample_key()?ag_current_url():$url;});
add_filter('wp_robots',function($robots){if(ag_media_sample_key()){$robots['noindex']=true;unset($robots['index']);}return $robots;});
add_filter('pre_get_document_title',function($title){
 if($key=ag_media_sample_key()){$s=ag_media_sample_catalog()[$key]??null;return $s?$s['title'].' | نمونه نمایشی | ارمغان تجارت وطن':$title;}
 if(is_page('media')&&ag_media_view())return (ag_media_view()==='video'?ag_ms_text('videos_nav'):ag_ms_text('podcasts_nav')).' | ارمغان تجارت وطن';
 return $title;
});
function ag_render_media(){ag_media_studio();}
function ag_render_media_sample($key){ag_media_studio_sample($key);}
function ag_home_media(){
 if(!vatan_feature('home_media'))return;$s=ag_section('media-highlight','home');$posts=get_posts(['post_type'=>'vatan_episode','post_status'=>'publish','numberposts'=>1,'meta_key'=>'_vatan_featured','meta_value'=>'1']);
 if(!$posts)$posts=get_posts(['post_type'=>'vatan_episode','post_status'=>'publish','numberposts'=>1]);
 $post=$posts[0]??null;if(!$post&&!ag_media_show_samples())return;
 $samples=ag_media_samples();$key=array_key_first($samples);$item=$post?ag_ms_item($post):ag_ms_sample($key,$samples[$key]);if(!empty($s['image'])){$item['image']=$s['image'];$item['alt']=$s['alt']??$item['title'];}
 echo '<section class="at-media am-studio ms-home" id="home-media" aria-labelledby="home-media-title"><div class="wrap"><span class="eyebrow">'.esc_html($s['eyebrow']).'</span><h2 id="home-media-title">'.esc_html($s['title']).'</h2><p>'.esc_html($s['body']).'</p>';ag_ms_feature($item,'h3');echo '<a class="ms-detail-link" href="'.esc_url(home_url('/media/')).'">'.esc_html($s['label']);ag_ms_icon('arrow');echo '</a></div>';ag_video($s);echo '</section>';
}
