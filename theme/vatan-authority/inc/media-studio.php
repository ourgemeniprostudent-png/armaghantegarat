<?php
/** Media-specific presentation. Published files and covers always come from native editing. */
if(!defined('ABSPATH'))exit;
function ag_ms_text($key){$s=ag_section('studio','media');return $s[$key]??'';}
function ag_ms_icon($name='play'){
 $paths=['play'=>'<path d="m9 5 11 7-11 7Z" fill="currentColor"/>','pause'=>'<path d="M8 5v14M16 5v14" stroke="currentColor" stroke-width="3"/>','audio'=>'<path d="M4 14v-3a8 8 0 0 1 16 0v3M4 12h3v8H4zM17 12h3v8h-3z" stroke="currentColor" stroke-width="1.5"/>','video'=>'<rect x="3" y="5" width="18" height="14" rx="1" stroke="currentColor" stroke-width="1.5"/><path d="m10 9 6 3-6 3Z" fill="currentColor"/>','arrow'=>'<path d="M20 20 4 4M4 20V4H20" stroke="currentColor" stroke-width="1.4"/>'];
 echo '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true">'.($paths[$name]??$paths['play']).'</svg>';
}
function ag_ms_item($post){
 $id=$post->ID;$topics=wp_get_post_terms($id,'vatan_topic',['fields'=>'names']);
 return ['id'=>$id,'title'=>$post->post_title,'body'=>get_the_excerpt($post),'topic'=>is_wp_error($topics)?'':implode(' / ',$topics),'topic_slugs'=>implode(' ',wp_get_post_terms($id,'vatan_topic',['fields'=>'slugs'])),'image'=>get_the_post_thumbnail_url($id,'large')?:'','alt'=>$post->post_title,'url'=>get_permalink($post),'audio'=>get_post_meta($id,'_vatan_audio',true),'video'=>get_post_meta($id,'_vatan_video',true),'duration'=>get_post_meta($id,'_vatan_duration',true),'date'=>get_the_date('', $id),'sample'=>false];
}
function ag_ms_sample($key,$s){return array_merge($s,['id'=>0,'key'=>$key,'topic_slugs'=>['coffee'=>'coffee','trade'=>'supply','storage'=>'quality'][$key]??'','url'=>ag_media_sample_url($key),'sample'=>true,'audio'=>'','video'=>'','duration'=>'']);}
function ag_ms_cover($item,$kind='audio'){
 $image=ag_media_url($item['image']??'');
 echo '<div class="ms-cover ms-cover-'.esc_attr($kind).'">';
 if($image)echo '<img '.ag_image_attrs($image,$kind==='video'?'(max-width:760px) 90vw, 45vw':'(max-width:760px) 70vw, 360px',768).' alt="'.esc_attr($item['alt']??$item['title']).'" loading="lazy">';
 else{echo '<span class="ms-cover-brand">'.esc_html(ag_ms_text('cover_brand')).'</span><span class="ms-cover-art" aria-hidden="true">';ag_ms_icon($kind);echo '</span><strong>'.esc_html($item['cover_title']??$item['title']).'</strong><span class="ms-cover-series">'.esc_html(ag_ms_text($kind==='video'?'cover_video_series':'cover_series')).'</span>';}
 echo '</div>';
}
function ag_ms_wave(){echo '<div class="ms-wave" aria-hidden="true">';for($i=0;$i<56;$i++)echo '<i style="--bar:'.(14+($i*23)%67).'%"></i>';echo '</div>';}
function ag_media_audio($url){
 echo '<div class="ms-audio" '.(vatan_feature('media_audio_controls')?'data-audio-console':'').' data-play-label="'.esc_attr(ag_ms_text('play_label')).'" data-pause-label="'.esc_attr(ag_ms_text('pause_label')).'" data-loading-label="'.esc_attr(ag_ms_text('loading_label')).'" data-error-label="'.esc_attr(ag_ms_text('error_label')).'"><audio class="media-player" data-episode-player controls preload="none" aria-label="'.esc_attr(ag_ms_text('play_label')).'" src="'.esc_url($url).'">مرورگر شما پخش صوت را پشتیبانی نمی‌کند.</audio><div class="ms-audio-controls" hidden>';
 ag_ms_wave();echo '<div class="ms-progress"><time data-audio-time dir="ltr">0:00</time><input data-audio-seek type="range" min="0" max="100" value="0" step="0.1" disabled aria-label="موقعیت پخش صوت"><time data-audio-duration dir="ltr">—:—</time></div><div class="ms-transport"><button type="button" data-audio-back aria-label="۱۵ ثانیه به عقب">۱۵−</button><button type="button" class="ms-play" data-audio-play aria-label="'.esc_attr(ag_ms_text('play_label')).'">';ag_ms_icon();echo '</button><button type="button" data-audio-forward aria-label="۱۵ ثانیه به جلو">۱۵+</button><label class="ms-speed">سرعت <select data-audio-rate aria-label="سرعت پخش"><option value="1">۱×</option><option value="1.25">۱٫۲۵×</option><option value="1.5">۱٫۵×</option><option value="2">۲×</option></select></label><button type="button" data-audio-mute aria-pressed="false">بی‌صدا</button></div><p class="ms-player-status" data-audio-status role="status" aria-live="polite"></p></div></div>';
}
function ag_ms_mock_player($item){
 echo '<div class="ms-mock-player">';ag_ms_wave();echo '<div class="ms-progress"><span dir="ltr">—:—</span><span class="ms-track"></span><span>'.esc_html(ag_ms_text('sample_label')).'</span></div><div class="ms-transport"><button type="button" class="ms-play" data-media-mock aria-label="'.esc_attr(ag_ms_text('play_label').'؛ '.ag_ms_text('sample_label')).'">';ag_ms_icon();echo '</button><p>'.esc_html(ag_ms_text('sample_waiting')).'</p></div><p class="ms-mock-status" role="status" hidden>'.esc_html(ag_ms_text('unavailable')).'</p></div>';
}
function ag_ms_feature($item,$heading='h2'){
 echo '<div class="ms-feature">';ag_ms_cover($item,!empty($item['audio'])||$item['sample']||empty($item['video'])?'audio':'video');echo '<div class="ms-feature-copy"><div class="ms-meta"><span>';ag_ms_icon(!empty($item['audio'])||$item['sample']||empty($item['video'])?'audio':'video');echo esc_html(ag_ms_text(!empty($item['audio'])||$item['sample']||empty($item['video'])?'podcast_label':'video_label')).'</span><span>'.esc_html($item['sample']?ag_ms_text('sample_label'):($item['duration']?:$item['date'])).'</span></div><'.$heading.'>'.esc_html($item['title']).'</'.$heading.'><p class="ms-summary">'.esc_html($item['body']).'</p>';
 if($item['sample'])ag_ms_mock_player($item);else ag_episode_player($item['id']);
 echo '<a class="ms-detail-link" href="'.esc_url($item['url']).'">'.esc_html(ag_ms_text('details_label'));ag_ms_icon('arrow');echo '</a></div></div>';
}
function ag_ms_path($sample=false){if(!vatan_feature('breadcrumbs'))return;echo '<nav class="ms-path" aria-label="مسیر صفحه"><a href="'.esc_url(home_url('/')).'">خانه</a><span aria-hidden="true">/</span>'.($sample?'<a href="'.esc_url(home_url('/media/')).'">رسانه</a><span aria-hidden="true">/</span><span aria-current="page">'.esc_html(ag_ms_text('sample_label')).'</span>':'<span aria-current="page">رسانه</span>').'</nav>';}
function ag_ms_query($kind){
 $enabled=vatan_feature('media_discovery');$search=$enabled?ag_query_value('q'):'';$topic=$enabled?ag_topic():'';$page=max(1,absint($_GET[$kind.'_page']??1));$sort=$enabled?ag_query_value('sort'):'';
 $args=['post_type'=>'vatan_episode','post_status'=>'publish','posts_per_page'=>9,'paged'=>$page,'s'=>$search,'meta_query'=>[['key'=>'_vatan_'.$kind,'value'=>'','compare'=>'!=']],'orderby'=>$sort==='title'?'title':'date','order'=>$sort==='oldest'||$sort==='title'?'ASC':'DESC'];
 if($topic)$args['tax_query']=[['taxonomy'=>'vatan_topic','field'=>'slug','terms'=>$topic]];
 return new WP_Query($args);
}
function ag_ms_pagination($query,$kind){if($query->max_num_pages<2)return;echo '<nav class="ms-pagination" aria-label="صفحه‌های '.($kind==='audio'?'پادکست':'ویدیو').'">'.paginate_links(['base'=>str_replace('999999999','%#%',add_query_arg($kind.'_page',999999999,ag_current_url())),'format'=>'','current'=>max(1,absint($_GET[$kind.'_page']??1)),'total'=>$query->max_num_pages,'add_args'=>array_filter(['q'=>ag_query_value('q'),'topic'=>ag_topic(),'sort'=>ag_query_value('sort')]),'prev_text'=>'قبلی','next_text'=>'بعدی']).'</nav>';}
function ag_ms_episode_row($item,$index){
 $uid=wp_unique_id('ms-inline-');
 echo '<article class="ms-episode-row" data-media-item data-media-kind="audio" data-media-topic="'.esc_attr($item['topic_slugs']??'').'" data-media-search="'.esc_attr($item['title'].' '.$item['body'].' '.$item['topic']).'"><a class="ms-row-cover" href="'.esc_url($item['url']).'" aria-label="'.esc_attr($item['title']).'">';ag_ms_cover($item);echo '</a><div class="ms-row-copy"><span class="ms-meta">'.esc_html($item['sample']?ag_ms_text('sample_label'):$item['topic']).'</span><h3><a href="'.esc_url($item['url']).'">'.esc_html($item['title']).'</a></h3><p>'.esc_html($item['body']).'</p></div><div class="ms-row-end"><span>'.esc_html($item['duration']?:'—:—').'</span><a class="ms-row-play" '.(!$item['sample']&&!empty($item['audio'])?'data-row-audio="'.esc_attr($uid).'" aria-expanded="false" aria-controls="'.esc_attr($uid).'"':'').' href="'.esc_url($item['url']).'#library" aria-label="'.esc_attr(ag_ms_text('play_label').'؛ '.$item['title']).'">';ag_ms_icon();echo '</a></div>';if(!$item['sample']&&!empty($item['audio'])){echo '<div class="ms-inline-player" id="'.esc_attr($uid).'" hidden>';ag_media_audio($item['audio']);echo '</div>';}echo '</article>';
}
function ag_ms_video_card($item){ag_mh_video_card($item);}
function ag_media_studio(){ag_mh_archive(ag_media_view());}
function ag_media_studio_sample($key){$kind=ag_media_view()?:'audio';ag_mh_detail(ag_mh_sample($key,ag_media_sample_catalog()[$key],$kind),$kind);}
