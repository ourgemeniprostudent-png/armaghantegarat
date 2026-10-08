<?php
/** Distinct video/watch and podcast/episode experiences within one editable media channel. */
if(!defined('ABSPATH'))exit;
function ag_mh_sample($key,$s,$kind){return array_merge($s,['id'=>0,'key'=>$key,'kind'=>$kind,'topic_slugs'=>$s['topic_slug']??'','url'=>ag_media_sample_url($key,$kind),'sample'=>true,'audio'=>'','video'=>'','duration'=>'','date'=>'']);}
function ag_mh_item($post,$kind){$item=ag_ms_item($post);$item['kind']=$kind;$item['url']=vatan_media_url($kind,$post);$item['sample']=false;return $item;}
function ag_mh_items($kind,$query=null){
 $items=[];if(ag_media_show_kind_samples($kind))foreach($kind==='audio'?ag_media_samples():ag_media_video_samples() as $key=>$s)$items[]=ag_mh_sample($key,$s,$kind);
 else foreach(($query?:ag_ms_query($kind))->posts as $post)$items[]=ag_mh_item($post,$kind);
 if($query&&ag_media_show_kind_samples($kind)&&vatan_feature('media_discovery')){
  $search=ag_query_value('q');$topic=ag_topic();$items=array_values(array_filter($items,function($item)use($search,$topic){return (!$search||mb_stripos($item['title'].' '.$item['body'],$search)!==false)&&(!$topic||in_array($topic,explode(' ',$item['topic_slugs']),true));}));
  $sort=ag_query_value('sort');if($sort==='title')usort($items,fn($a,$b)=>strcmp($a['title'],$b['title']));elseif($sort==='oldest')$items=array_reverse($items);
 }
 return $items;
}
function ag_mh_nav($kind=''){
 echo '<nav class="mh-tabs" aria-label="بخش‌های رسانه">';foreach([''=>'all_media_label','video'=>'videos_nav','audio'=>'podcasts_nav'] as $value=>$label)if($value!=='video'||vatan_feature('video_library'))echo '<a '.($kind===$value?'aria-current="page" ':'').'href="'.esc_url(vatan_media_url($value)).'">'.esc_html(ag_ms_text($label)).'</a>';echo '</nav>';
}
function ag_mh_begin($kind='',$detail=false){echo '<div class="at-media mh-media mh-'.esc_attr($kind?:'hub').($detail?' mh-detail':'').'"><div class="wrap mh-shell">';ag_breadcrumb();ag_mh_nav($kind);}
function ag_mh_end(){echo '</div></div>';}
function ag_mh_badge($item){echo '<span class="mh-status">'.esc_html($item['sample']?ag_ms_text('sample_label'):($item['duration']?:$item['date'])).'</span>';}
function ag_mh_video_card($item,$small=false){
 echo '<article class="mh-video-card '.($small?'mh-video-small':'').'" data-media-item data-media-kind="video" data-media-topic="'.esc_attr($item['topic_slugs']).'" data-media-search="'.esc_attr($item['title'].' '.$item['body'].' '.$item['topic']).'"><a class="mh-thumbnail" href="'.esc_url($item['url']).'" aria-label="'.esc_attr($item['title']).'">';ag_ms_cover($item,'video');echo '<span class="mh-thumb-play">';ag_ms_icon();echo '</span>';ag_mh_badge($item);echo '</a><div class="mh-video-copy"><h3><a href="'.esc_url($item['url']).'">'.esc_html($item['title']).'</a></h3><span>'.esc_html(ag_ms_text('cover_brand')).'</span><p>'.esc_html($item['topic']).'</p></div></article>';
}
function ag_mh_podcast_row($item,$index){
 echo '<div class="mh-podcast-row">';echo '<span class="mh-row-number" aria-hidden="true">'.esc_html(vatan_digits_fa($index+1)).'</span>';ag_ms_episode_row($item,$index);echo '</div>';
}
function ag_mh_search($kind){
 if(!vatan_feature('media_discovery'))return;$base=vatan_media_url($kind);$topic=ag_topic();
 echo '<div class="mh-discovery"><form class="ms-search mh-search" data-media-search-form data-discovery method="get" action="'.esc_url($base).'#library"><label class="sr-only" for="ms-search">'.esc_html(ag_ms_text('search_label')).'</label><div><input id="ms-search" type="search" name="q" value="'.esc_attr(ag_query_value('q')).'" maxlength="160" placeholder="'.esc_attr(ag_ms_text('search_label')).'"><button type="submit">جستجو</button></div><label class="sr-only" for="ms-topic">موضوع</label><select id="ms-topic" name="topic"><option value="">'.esc_html(ag_ms_text('all_topics')).'</option>';foreach(get_terms(['taxonomy'=>'vatan_topic','hide_empty'=>false]) as $term)echo '<option value="'.esc_attr($term->slug).'" '.selected($topic,$term->slug,false).'>'.esc_html($term->name).'</option>';echo '</select><label class="sr-only" for="mh-sort">ترتیب نمایش</label><select id="mh-sort" name="sort">';foreach(['newest'=>'تازه‌ترین','oldest'=>'قدیمی‌ترین','title'=>'بر اساس عنوان'] as $value=>$label)echo '<option value="'.$value.'" '.selected(ag_query_value('sort')?:'newest',$value,false).'>'.$label.'</option>';echo '</select></form>';
 echo '<nav class="mh-topics" aria-label="موضوعات رسانه"><a href="'.esc_url($base).'#library" data-media-topic-filter="" '.(!$topic?'aria-current="true"':'').'>'.esc_html(ag_ms_text('all_topics')).'</a>';
 foreach(get_terms(['taxonomy'=>'vatan_topic','hide_empty'=>false]) as $term)echo '<a href="'.esc_url(add_query_arg('topic',$term->slug,$base)).'#library" data-media-topic-filter="'.esc_attr($term->slug).'" '.($topic===$term->slug?'aria-current="true"':'').'>'.esc_html($term->name).'</a>';echo '</nav></div>';
}
function ag_mh_demo_note($kind){if(ag_media_show_kind_samples($kind))echo '<p class="mh-demo-note ms-demo-notice">'.esc_html(ag_ms_text('sample_label')).' — '.esc_html(ag_ms_text('unavailable')).'</p>';}
function ag_mh_archive($kind){
 ag_mh_begin($kind);
 if(!$kind){
  echo '<header class="mh-channel-heading"><span class="eyebrow">'.esc_html(ag_ms_text('cover_brand')).'</span><h1>'.esc_html(ag_ms_text('channel_title')).'</h1><p>'.esc_html(ag_ms_text('channel_body')).'</p></header><div class="mh-gateways">';
  foreach(['video','audio'] as $type){if($type==='video'&&!vatan_feature('video_library'))continue;$items=ag_mh_items($type);$sample=$items[0]??['image'=>'','title'=>ag_ms_text($type==='video'?'videos_nav':'podcasts_nav'),'cover_title'=>ag_ms_text($type==='video'?'video_archive_title':'podcast_archive_title'),'sample'=>false];
   echo '<section class="mh-gateway mh-gateway-'.$type.'"><div class="mh-gateway-top"><span>';ag_ms_icon($type);echo esc_html(ag_ms_text($type==='video'?'video_format':'audio_format')).'</span><a class="mh-route-link" href="'.esc_url(vatan_media_url($type)).'">'.esc_html(ag_ms_text($type==='video'?'watch_label':'listen_label'));ag_ms_icon('arrow');echo '</a></div><a class="mh-gateway-cover" href="'.esc_url(vatan_media_url($type)).'">';ag_ms_cover($sample,$type);echo '</a><h2><a href="'.esc_url(vatan_media_url($type)).'">'.esc_html(ag_ms_text($type==='video'?'videos_nav':'podcasts_nav')).'</a></h2><p>'.esc_html(ag_ms_text($type==='video'?'video_archive_body':'podcast_archive_body')).'</p><div class="mh-gateway-recent">';
   foreach(array_slice($items,0,2) as $item){echo '<a href="'.esc_url($item['url']).'">';ag_ms_icon($type);echo '<span>'.esc_html($item['title']).'</span>';ag_mh_badge($item);echo '</a>';}echo '</div></section>';
  }echo '</div>';ag_mh_end();return;
 }
 $query=ag_ms_query($kind);$items=ag_mh_items($kind,$query);$sample=ag_media_show_kind_samples($kind);
 if($kind==='video'){
  echo '<header class="mh-channel-heading mh-video-heading"><div class="mh-channel-avatar">';ag_ms_icon('video');echo '</div><div><span class="eyebrow">'.esc_html(ag_ms_text('cover_brand')).'</span><h1>'.esc_html(ag_ms_text('video_archive_title')).'</h1><p>'.esc_html(ag_ms_text('video_archive_body')).'</p></div></header>';
 }else{
  $cover=['image'=>ag_ms_text('podcast_cover'),'alt'=>ag_ms_text('podcast_archive_title'),'title'=>ag_ms_text('podcast_archive_title'),'cover_title'=>ag_ms_text('podcast_archive_title')];
  echo '<header class="mh-album-heading">';ag_ms_cover($cover);echo '<div><span class="eyebrow">'.esc_html(ag_ms_text('audio_format')).'</span><h1>'.esc_html(ag_ms_text('podcast_archive_title')).'</h1><p>'.esc_html(ag_ms_text('podcast_archive_body')).'</p><span class="mh-album-meta">'.esc_html(ag_ms_text('cover_brand')).' · '.esc_html(vatan_digits_fa($sample?count($items):$query->found_posts)).' '.esc_html(ag_ms_text($sample?'sample_count':'published_count')).'</span>';
  if($items){$first=$items[0];echo '<div class="mh-selected"><a href="'.esc_url($first['url']).'">'.esc_html($first['title']).'</a>';if($sample)ag_ms_mock_player($first);else ag_media_audio($first['audio']);echo '</div>';}
  echo '</div></header>';
 }
 ag_mh_search($kind);ag_mh_demo_note($kind);echo '<section class="mh-library" id="library">';
 if($kind==='video'){echo '<div class="mh-video-grid">';foreach($items as $item)ag_mh_video_card($item);echo '</div>';}
 else{echo '<div class="mh-list-heading"><span>'.esc_html(ag_ms_text('episode_title_label')).'</span><span>'.esc_html(ag_ms_text('duration_label')).'</span></div><div class="ms-episodes mh-episodes">';foreach($items as $index=>$item)ag_mh_podcast_row($item,$index);echo '</div>';}
 if(!$items)echo '<p class="mh-empty">'.esc_html(ag_ms_text($kind==='video'?'video_no_results':'podcast_empty')).'</p>';
 echo '<p class="ms-no-results" data-media-no-results hidden role="status">'.esc_html(ag_ms_text('no_results')).'</p>';if(!$sample)ag_ms_pagination($query,$kind);echo '</section>';ag_mh_end();
}
function ag_mh_related($item,$kind){
 if(!$item['sample']){
  $ids=vatan_public_ids(get_post_meta($item['id'],'_vatan_related',true),['vatan_episode']);$posts=[];
  foreach($ids as $id)if(get_post_meta($id,'_vatan_'.$kind,true))$posts[]=get_post($id);
  if(!$posts)$posts=get_posts(['post_type'=>'vatan_episode','post_status'=>'publish','numberposts'=>6,'post__not_in'=>[$item['id']],'meta_query'=>[['key'=>'_vatan_'.$kind,'value'=>'','compare'=>'!=']]]);
  return array_map(fn($p)=>ag_mh_item($p,$kind),array_slice($posts,0,6));
 }
 return array_values(array_filter(ag_mh_items($kind),fn($other)=>$other['key']!==$item['key']));
}
function ag_mh_timestamps($item){if(!$item['id'])return;$rows=vatan_timestamps(get_post_meta($item['id'],'_vatan_timestamps',true));if(!$rows)return;echo '<nav class="mh-chapters" aria-label="بخش‌های گفتگو"><h2>بخش‌های گفتگو</h2><ol>';foreach($rows as $row)echo '<li><button type="button" data-timestamp="'.esc_attr($row['seconds']).'"><span dir="ltr">'.esc_html($row['time']).'</span><span>'.esc_html($row['title']).'</span></button></li>';echo '</ol></nav>';}
function ag_mh_description($item,$kind){
 echo '<section class="mh-description" data-article-body><h2>'.esc_html(ag_ms_text($kind==='video'?'video_detail_about':'podcast_detail_about')).'</h2>';ag_body($item['body']);
 if($item['sample']){echo '<p class="mh-detail-notice">'.esc_html(ag_ms_text('unavailable')).'</p>';if(!empty($item['transcript'])){echo '<h3>'.esc_html(ag_section('sample-guide','media')['transcript_title']).'</h3>';ag_body($item['transcript']);}}
 else{echo apply_filters('the_content',get_post_field('post_content',$item['id']));$transcript=get_post_meta($item['id'],'_vatan_transcript',true);if($transcript){echo '<details class="mh-transcript"><summary>'.esc_html(ag_ms_text('transcript_label')).'</summary>';ag_body($transcript);echo '</details>';}}
 ag_mh_timestamps($item);echo '</section>';
}
function ag_mh_detail($item,$kind){
 ag_mh_begin($kind,true);$related=vatan_feature('related_content')?ag_mh_related($item,$kind):[];
 if($kind==='video'){
  echo '<div class="mh-watch-layout"><div class="mh-watch-main"><div class="mh-screen" id="library">';
  if($item['sample']){ag_ms_cover($item,'video');echo '<button class="mh-screen-play" type="button" data-media-mock aria-label="'.esc_attr(ag_ms_text('video_play_label')).'">';ag_ms_icon();echo '</button><p class="ms-mock-status" role="status" hidden>'.esc_html(ag_ms_text('unavailable')).'</p>';}else{
   echo '<video data-episode-player controls playsinline preload="none" poster="'.esc_url(ag_media_url($item['image'])).'" src="'.esc_url($item['video']).'" aria-label="'.esc_attr($item['title']).'">';$captions=get_post_meta($item['id'],'_vatan_captions',true);if($captions)echo '<track kind="captions" srclang="fa" label="فارسی" src="'.esc_url($captions).'">';echo '</video>';
  }echo '</div><h1>'.esc_html($item['title']).'</h1><div class="mh-watch-meta"><span>'.esc_html(ag_ms_text('cover_brand')).'</span>';ag_mh_badge($item);echo '</div>';
  if(!$item['sample']&&!empty($item['audio']))echo '<a class="mh-route-link mh-alternate" href="'.esc_url(vatan_media_url('audio',get_post($item['id']))).'">'.esc_html(ag_ms_text('audio_version')).'</a>';
  ag_mh_description($item,$kind);echo '</div>';if($related){echo '<aside class="mh-watch-related" aria-label="'.esc_attr(ag_ms_text('related_videos')).'"><h2>'.esc_html(ag_ms_text('related_videos')).'</h2>';foreach($related as $other)ag_mh_video_card($other,true);echo '</aside>';}echo '</div>';
 }else{
  echo '<header class="mh-album-heading mh-episode-heading">';ag_ms_cover($item);echo '<div><span class="eyebrow">'.esc_html(ag_ms_text('podcast_label')).'</span><h1>'.esc_html($item['title']).'</h1><div class="mh-album-meta"><span>'.esc_html(ag_ms_text('cover_brand')).'</span>';ag_mh_badge($item);echo '</div><div id="library">';if($item['sample'])ag_ms_mock_player($item);else ag_media_audio($item['audio']);echo '</div>';
  if(!$item['sample']&&!empty($item['video']))echo '<a class="mh-route-link mh-alternate" href="'.esc_url(vatan_media_url('video',get_post($item['id']))).'">'.esc_html(ag_ms_text('video_version')).'</a>';echo '</div></header><div class="mh-episode-layout"><div>';ag_mh_description($item,$kind);echo '</div>';
  if($related){echo '<aside class="mh-podcast-related"><h2>'.esc_html(ag_ms_text('other_episodes')).'</h2>';foreach($related as $other){echo '<a href="'.esc_url($other['url']).'" class="mh-next-episode">';ag_ms_cover($other);echo '<div><h3>'.esc_html($other['title']).'</h3>';ag_mh_badge($other);echo '</div></a>';}echo '</aside>';}echo '</div>';
 }ag_mh_end();
}
