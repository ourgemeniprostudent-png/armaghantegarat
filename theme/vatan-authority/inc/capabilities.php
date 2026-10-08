<?php
if(!defined('ABSPATH'))exit;
function ag_query_value($key,$max=160){return isset($_GET[$key])&&is_string($_GET[$key])?mb_substr(sanitize_text_field(wp_unslash($_GET[$key])),0,$max):'';}
function ag_topic($key='topic'){$v=ag_query_value($key,60);return term_exists($v,'vatan_topic')?$v:'';}
function ag_public_author($id){return get_post_meta($id,'_vatan_public_author',true)?:'تحریریه ارمغان تجارت وطن';}
function ag_whatsapp($title='', $url=''){
 if(!vatan_feature('whatsapp'))return;$link=vatan_whatsapp_url($title,$url);if($link)echo '<a class="home-route-link" data-whatsapp href="'.esc_url($link).'" target="_blank" rel="noopener">گفتگو در واتس‌اپ <span aria-hidden="true">↗</span></a>';
}
function ag_current_url(){return is_tax('vatan_category')?get_term_link(get_queried_object()):(is_home()?get_permalink((int)get_option('page_for_posts')):(is_singular()?get_permalink():home_url('/')));}
function ag_discovery($layout,$context,$s){
 $catalog=$layout==='catalog';$type=$catalog?'vatan_product':($layout==='articles'?'post':'vatan_episode');$search=ag_query_value('q');$topic=$catalog?'':ag_topic();$sort=ag_query_value('sort');if(!in_array($sort,['newest','oldest','title'],true))$sort='newest';
 $enabled=vatan_feature($catalog?'product_filters':($layout==='articles'?'blog_discovery':'media_discovery'));
 if(!$enabled){$search='';$topic='';$sort='newest';}
 $base=ag_current_url();$page=max(1,absint($_GET['archive_page']??1));$args=['post_type'=>$type,'post_status'=>'publish','posts_per_page'=>9,'paged'=>$page,'s'=>$search,'orderby'=>$sort==='title'?'title':'date','order'=>$sort==='oldest'||$sort==='title'?'ASC':'DESC'];
 if($topic)$args['tax_query']=[['taxonomy'=>'vatan_topic','field'=>'slug','terms'=>$topic]];
 if($catalog)$args['tax_query']=[['taxonomy'=>'vatan_category','field'=>'slug','terms'=>substr($context,9)]];
 // Only expose specification values that actually exist in published products of this group.
 $facets=[];$matching=[];$spec_key=ag_query_value('spec_key',80);$spec_value=ag_query_value('spec_value',100);
 if($catalog&&$enabled){
  $products=get_posts(['post_type'=>$type,'post_status'=>'publish','numberposts'=>-1,'tax_query'=>$args['tax_query']]);
  foreach($products as $p){foreach(explode("\n",get_post_meta($p->ID,'_vatan_specs',true)) as $line){$pair=explode('|',$line,2);if(count($pair)!==2)continue;$k=trim($pair[0]);$v=trim($pair[1]);if($k===''||$v==='')continue;$facets[$k][$v]=true;if($k===$spec_key&&$v===$spec_value)$matching[]=$p->ID;}}
  if($spec_key&&$spec_value&&isset($facets[$spec_key][$spec_value]))$args['post__in']=$matching?:[0];else{$spec_key='';$spec_value='';}
 }
 if($enabled){
  echo '<details class="ag-filter-panel" open><summary>جستجو و انتخاب '.($catalog?'محصول':($layout==='articles'?'یادداشت':'رسانه')).'</summary><form class="ag-discovery-form" data-discovery data-layout="'.esc_attr($layout).'" method="get" action="'.esc_url($base).'#'.esc_attr($s['_id']??'library').'"><div class="ag-discovery-fields"><div class="field"><label for="discovery-q">'.esc_html($s['search_label']??'جستجو در عنوان و متن').'</label><input id="discovery-q" type="search" name="q" value="'.esc_attr($search).'" maxlength="160"></div>';
  if(!$catalog){echo '<div class="field"><label for="discovery-topic">موضوع</label><select id="discovery-topic" name="topic"><option value="">همه موضوعات</option>';foreach(get_terms(['taxonomy'=>'vatan_topic','hide_empty'=>false]) as $t)echo '<option value="'.esc_attr($t->slug).'" '.selected($topic,$t->slug,false).'>'.esc_html($t->name).'</option>';echo '</select></div>';}
  echo '<div class="field"><label for="discovery-sort">ترتیب نمایش</label><select id="discovery-sort" name="sort">';foreach(['newest'=>'تازه‌ترین','oldest'=>'قدیمی‌ترین','title'=>'بر اساس عنوان'] as $k=>$label)echo '<option value="'.$k.'" '.selected($sort,$k,false).'>'.$label.'</option>';echo '</select></div></div>';
  if($facets){echo '<div class="ag-spec-filters"><span>ویژگی‌های منتشرشده</span><a href="'.esc_url(remove_query_arg(['spec_key','spec_value','archive_page'],$base)).'">همه</a>';foreach($facets as $key=>$values)foreach(array_keys($values) as $value)echo '<a '.($key===$spec_key&&$value===$spec_value?'aria-current="true" ':'').'href="'.esc_url(add_query_arg(['q'=>$search,'spec_key'=>$key,'spec_value'=>$value],$base)).'#'.esc_attr($s['_id']??'catalog').'">'.esc_html($key.' / '.$value).'</a>';echo '</div>';}
  echo '<div class="ag-discovery-actions"><button class="button trade-button" type="submit">نمایش نتیجه</button><a href="'.esc_url($base).'#'.esc_attr($s['_id']??'library').'">پاک‌کردن انتخاب‌ها</a></div></form></details>';
 }
 $q=new WP_Query($args);
 echo '<p class="ag-results-count" data-results-count>'.esc_html(vatan_digits_fa($q->found_posts)).' '.($catalog?'محصول':($layout==='articles'?'یادداشت':'رسانه')).' منتشرشده</p>';
 if($q->have_posts()){
  if($layout==='articles')ag_journal_cards($q);
  else{echo '<div class="ag-content-grid">';while($q->have_posts()){$q->the_post();ag_content_card(get_post());}echo '</div>';}
  $clean=['q'=>$search,'topic'=>$topic,'sort'=>$sort,'spec_key'=>$spec_key,'spec_value'=>$spec_value];$clean=array_filter($clean,fn($v)=>$v!=='');
  echo '<nav class="ag-pagination" aria-label="صفحه‌های آرشیو">'.paginate_links(['base'=>str_replace('999999999','%#%',add_query_arg('archive_page',999999999,$base)),'format'=>'','total'=>$q->max_num_pages,'current'=>$page,'add_args'=>$clean,'prev_text'=>'قبلی','next_text'=>'بعدی']).'</nav>';
 }else{
  echo '<div class="ag-empty"><h3>'.esc_html($search||$topic||$spec_key?'نتیجه‌ای برای این انتخاب پیدا نشد.':($s['empty_title']??'هنوز محتوایی منتشر نشده است.')).'</h3><p>'.esc_html($s['empty_body']??'انتخاب‌های دیگری را امتحان کنید یا درخواست خود را برای دفتر بنویسید.').'</p><a class="home-route-link" href="'.esc_url($catalog?add_query_arg('category',substr($context,9),vatan_url('inquiry/')):vatan_url('blog/')).'">'.($catalog?'ثبت درخواست تامین':'خواندن مجله').' <span aria-hidden="true">↗</span></a></div>';
 }
 wp_reset_postdata();
 if($layout==='library'&&vatan_feature('video_library'))ag_video_library($s,$topic,$search);
}
function ag_content_card($p){
 $id=$p->ID;$episode=$p->post_type==='vatan_episode';echo '<article class="ag-content-card" data-ag-reveal>';if(has_post_thumbnail($id))echo get_the_post_thumbnail($id,'large',['loading'=>'lazy']);
 echo '<span class="eyebrow">'.esc_html($episode?'گفتگو و رسانه':($p->post_type==='post'?'مجله تجارت':'شناخت محصول')).'</span><h3><a href="'.esc_url(get_permalink($id)).'">'.esc_html($p->post_title).'</a></h3>';
 if($episode)echo '<p class="ag-card-meta">'.esc_html(get_the_date('', $id).' / '.get_post_meta($id,'_vatan_duration',true)).'</p>';
 echo '<p>'.esc_html(wp_trim_words(get_the_excerpt($p),28)).'</p><a class="home-route-link" href="'.esc_url(get_permalink($id)).'">'.($episode?'شنیدن و خواندن':($p->post_type==='post'?'خواندن یادداشت':'مشخصات و استعلام')).' <span aria-hidden="true">↗</span></a></article>';
}
function ag_video_library($s,$topic='',$search=''){
 $args=['post_type'=>'vatan_episode','post_status'=>'publish','posts_per_page'=>9,'s'=>$search,'meta_query'=>[['key'=>'_vatan_video','value'=>'','compare'=>'!=']]];if($topic)$args['tax_query']=[['taxonomy'=>'vatan_topic','field'=>'slug','terms'=>$topic]];$q=new WP_Query($args);
 echo '<section class="ag-video-library"><h3>'.esc_html($s['video_library_title']??'کتابخانه ویدیو').'</h3>';
 if($q->have_posts()){echo '<div class="ag-content-grid">';while($q->have_posts()){$q->the_post();echo '<article class="ag-content-card"><video class="ag-video" controls playsinline preload="none" poster="'.esc_url(get_the_post_thumbnail_url(null,'large')?:'').'"><source src="'.esc_url(get_post_meta(get_the_ID(),'_vatan_video',true)).'"></video><h4><a href="'.esc_url(get_permalink()).'">'.esc_html(get_the_title()).'</a></h4></article>';}echo '</div>';}else echo '<p>'.esc_html($s['video_empty']??'ویدیوهای منتشرشده همراه توضیح و متن گفتگو اینجا قرار می‌گیرند.').'</p>';
 echo '</section>';wp_reset_postdata();
}
function ag_related($id){
 if(!vatan_feature('related_content'))return;$ids=vatan_public_ids(get_post_meta($id,'_vatan_related',true));
 if(!$ids){$taxonomy=get_post_type($id)==='vatan_product'?'vatan_category':'vatan_topic';$terms=wp_get_post_terms($id,$taxonomy,['fields'=>'ids']);if(!is_wp_error($terms)&&$terms)$ids=get_posts(['post_type'=>get_post_type($id),'post_status'=>'publish','posts_per_page'=>3,'fields'=>'ids','post__not_in'=>[$id],'tax_query'=>[['taxonomy'=>$taxonomy,'terms'=>$terms]]]);}
 if(!$ids)return;echo '<section class="ag-section"><div class="wrap"><h2>برای ادامه شناخت</h2><div class="ag-content-grid">';foreach(array_slice($ids,0,6) as $related)ag_content_card(get_post($related));echo '</div></div></section>';
}
function ag_featured_media($s){
 $q=new WP_Query(['post_type'=>'vatan_episode','post_status'=>'publish','posts_per_page'=>1,'meta_query'=>[['key'=>'_vatan_featured','value'=>'1']]]);if(!$q->have_posts())return;
 echo '<section class="ag-featured-media"><span class="eyebrow">'.esc_html($s['featured_label']??'گفتگوی منتخب').'</span>';
 while($q->have_posts()){$q->the_post();$id=get_the_ID();echo '<h3><a href="'.esc_url(get_permalink()).'">'.esc_html(get_the_title()).'</a></h3><p>'.esc_html(get_the_excerpt()).'</p>';ag_episode_player($id);}
 echo '</section>';wp_reset_postdata();
}
function ag_episode_player($id){
 $video=get_post_meta($id,'_vatan_video',true);$audio=get_post_meta($id,'_vatan_audio',true);$captions=get_post_meta($id,'_vatan_captions',true);
 if($video){echo '<video class="ag-video" data-episode-player controls playsinline preload="none" poster="'.esc_url(get_the_post_thumbnail_url($id,'large')?:'').'" src="'.esc_url($video).'">';if($captions)echo '<track kind="captions" srclang="fa" label="فارسی" src="'.esc_url($captions).'">';echo '</video>';}
 if($audio)echo '<audio class="media-player" data-episode-player controls preload="none" src="'.esc_url($audio).'">مرورگر شما پخش صوت را پشتیبانی نمی‌کند.</audio>';
 if(!$video&&!$audio)echo '<p>فایل پخش برای این گفتگو هنوز منتشر نشده است.</p>';
}
function ag_product_gallery($id){
 if(!vatan_feature('product_gallery'))return;$ids=vatan_gallery_ids(get_post_meta($id,'_vatan_gallery',true));$cover=get_post_thumbnail_id($id);if($cover)$ids=array_values(array_unique(array_merge([$cover],$ids)));if(!$ids)return;
 echo '<div class="ag-product-gallery" aria-label="تصاویر محصول">';foreach($ids as $index=>$image){$alt=get_post_meta($image,'_wp_attachment_image_alt',true)?:get_the_title($id).' / تصویر '.($index+1);echo '<button type="button" data-ag-zoom aria-label="'.esc_attr('نمای بزرگ '.$alt).'">'.wp_get_attachment_image($image,'large',false,['alt'=>$alt,'loading'=>'lazy','sizes'=>'(max-width:767px) 85vw, 460px']).'</button>';}echo '</div>';
}
function ag_office_map($s){
 if(!vatan_feature('office_map'))return;$lat=get_option('vatan_map_latitude','35.7294807434082');$lng=get_option('vatan_map_longitude','51.436744689941406');$coordinates=$lat.','.$lng;
 $embed='https://maps.google.com/maps?q='.rawurlencode($coordinates).'&z=16&output=embed';$route='https://www.google.com/maps/search/?api=1&query='.rawurlencode($coordinates);
 echo '<div class="ag-map"><div class="ag-map-art" aria-hidden="true"><i></i><span>↗</span><b>تهران</b></div><h3>'.esc_html($s['address_label']).'</h3><p>'.esc_html($s['map_note']).'</p><button class="button trade-button" type="button" data-map-load="'.esc_url($embed).'">'.esc_html($s['map_load_label']??'نمایش نقشه دفتر').'</button><a class="home-route-link" rel="noopener" target="_blank" href="'.esc_url($route).'">'.esc_html($s['map_label']).' <span aria-hidden="true">↗</span></a><p class="ag-map-privacy">'.esc_html($s['map_privacy']??'با نمایش نقشه، به سرویس نقشه گوگل متصل می‌شوید.').'</p></div>';
}
