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
function ag_render_media(){
    $hero=ag_section('hero','media');$library=ag_section('library','media');
    echo '<div class="at-media">';ag_media_hero($hero,($hero['line1']??'').' '.($hero['line2']??''),$hero['body']??'');
    echo '<section class="am-section" id="library"><div class="wrap"><span class="eyebrow">'.esc_html($library['eyebrow']).'</span><h2>'.esc_html($library['title']).'</h2><p class="am-intro">'.esc_html($library['body']).'</p>';
    if(ag_media_show_samples()){
        $guide=ag_section('sample-guide','media');echo '<p class="am-sample-note">'.esc_html($guide['body']).'</p><div class="am-samples">';
        foreach(ag_media_samples() as $key=>$s){echo '<article><a class="am-cover" href="'.esc_url(ag_media_sample_url($key)).'"><img '.ag_image_attrs(ag_media_url($s['image']),'(max-width:760px) 90vw, 30vw',768).' alt="'.esc_attr($s['alt']).'" loading="lazy"><span>'.esc_html($s['sample_label']).'</span></a><span class="eyebrow">'.esc_html($s['topic']).'</span><h3><a href="'.esc_url(ag_media_sample_url($key)).'">'.esc_html($s['title']).'</a></h3><p>'.esc_html($s['body']).'</p><a class="am-link" href="'.esc_url(ag_media_sample_url($key)).'">'.esc_html($guide['label']).' <svg class="am-arrow" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 20 4 4M4 20V4H20" stroke="currentColor" stroke-width="1.4"/></svg></a></article>';}
        echo '</div>';if(vatan_feature('video_library'))echo '<section class="ag-video-library am-placeholder"><h3>'.esc_html($library['video_library_title']).'</h3><p>'.esc_html($library['video_empty']).'</p></section>';
    }else{echo '<div class="ag-interior">';ag_featured_media($library);ag_discovery('library','media',$library);echo '</div>';}
    ag_video($library);echo '</div></section>';
    foreach(ag_sections('media') as $id=>$section){if(in_array($section['layout'],['media-sample','media-guide','hero','library'],true))continue;$s=$section['values'];echo '<section class="am-section am-secondary" id="'.esc_attr($id).'"><div class="wrap">';ag_heading($s);ag_body($s['body']??'');
        for($i=1;$i<=3;$i++){if(empty($s['item_'.$i.'_title']))continue;echo '<div class="am-chapter"><h3>'.esc_html($s['item_'.$i.'_title']).'</h3>';ag_body($s['item_'.$i.'_body']??'');ag_visual(['image'=>$s['item_'.$i.'_image']??'','video'=>$s['item_'.$i.'_video']??'','alt'=>$s['item_'.$i.'_title']]);if(!empty($s['item_'.$i.'_link']))ag_action(['link'=>$s['item_'.$i.'_link'],'label'=>$s['item_'.$i.'_action']??'بیشتر بخوانید'],'am-link');echo '</div>';}
        ag_visual($s);ag_action($s,'am-link');echo '</div></section>';}
    ag_video($hero);echo '</div>';
}
function ag_render_media_sample($key){
    $s=ag_media_samples()[$key];$guide=ag_section('sample-guide','media');echo '<div class="at-media am-sample-detail">';ag_media_hero($s,$s['title'],$s['body'],true);
    echo '<section class="am-section" id="listen"><div class="wrap am-reading"><p class="am-sample-note">'.esc_html($guide['body']).'</p><h2>'.esc_html($guide['player_title']).'</h2><div class="am-placeholder"><span class="am-wave" aria-hidden="true">';for($i=0;$i<32;$i++)echo '<i style="--wave:'.(16+($i*17)%49).'px"></i>';echo '</span><p>'.esc_html($guide['player_note']).'</p></div><h2>'.esc_html($guide['transcript_title']).'</h2>';ag_body($s['transcript']);ag_video($s);echo '<a class="am-link" href="'.esc_url(home_url('/media/')).'">'.esc_html($guide['back_label']).' <svg class="am-arrow" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 20 4 4M4 20V4H20" stroke="currentColor" stroke-width="1.4"/></svg></a></div></section></div>';
}
function ag_home_media(){
    if(!vatan_feature('home_media'))return;$s=ag_section('media-highlight','home');$posts=get_posts(['post_type'=>'vatan_episode','post_status'=>'publish','numberposts'=>1,'meta_key'=>'_vatan_featured','meta_value'=>'1']);
    if(!$posts)$posts=get_posts(['post_type'=>'vatan_episode','post_status'=>'publish','numberposts'=>1]);
    $post=$posts[0]??null;if(!$post&&!ag_media_show_samples())return;
    $sample=array_values(ag_media_samples())[0]??[];$title=$post?$post->post_title:($sample['title']??$s['title']);$image=$post?(get_the_post_thumbnail_url($post,'large')?:ag_media_url($s['image'])):ag_media_url($s['image']);$link=$post?get_permalink($post):home_url('/media/');
    echo '<section class="at-media am-home" id="home-media" aria-labelledby="home-media-title"><img class="am-home-photo" '.ag_image_attrs($image,'100vw',1280).' alt="'.esc_attr($s['alt']).'" loading="lazy"><div class="wrap am-home-copy"><span class="eyebrow">'.esc_html($s['eyebrow']).'</span><h2 id="home-media-title">'.esc_html($title).'</h2><p>'.esc_html($post?get_the_excerpt($post):$s['body']).'</p>';
    if($post){$duration=get_post_meta($post->ID,'_vatan_duration',true);if($duration)echo '<p>'.esc_html($duration).'</p>';ag_episode_player($post->ID);}else echo '<small>'.esc_html($s['sample_note']).'</small>';
    echo '<a class="am-link" href="'.esc_url($link).'">'.esc_html($s['label']).' <svg class="am-arrow" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 20 4 4M4 20V4H20" stroke="currentColor" stroke-width="1.4"/></svg></a></div>';ag_video($s);echo '</section>';
}
