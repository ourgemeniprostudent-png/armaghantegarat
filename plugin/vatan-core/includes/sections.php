<?php
/** Saved WordPress edits take precedence over public bundled defaults. */
if (!defined('ABSPATH')) exit;
function armaghan_section_catalog() {
 static $catalog;
 if ($catalog===null) $catalog=json_decode(file_get_contents(dirname(__DIR__).'/content/sections-fa.json'),true)?:[];
 return $catalog;
}
function armaghan_context($object=null) {
 if($object instanceof WP_Term)return 'category:'.$object->slug;
 $object=$object?:get_post();if(!$object)return 'home';
 if($object->post_type==='page')return $object->post_name;
 if($object->post_type==='post')return 'post:'.$object->post_name;
 return $object->post_type==='vatan_product'?'product':'episode';
}
function armaghan_section_schema($context) {
 $catalog=armaghan_section_catalog();return $catalog[$context]??$catalog[strtok($context,':')]??$catalog['page']??[];
}
function armaghan_section_data($context,$id=0) {
 if(!$id){
  if(in_array($context,['global','home'],true))$id=(int)get_option('page_on_front');
  elseif(str_starts_with($context,'category:')){$term=get_term_by('slug',substr($context,9),'vatan_category');$id=$term?$term->term_id:0;}
  elseif(str_starts_with($context,'post:')){$posts=get_posts(['name'=>substr($context,5),'post_type'=>'post','numberposts'=>1]);$id=$posts?$posts[0]->ID:0;}
  elseif(in_array($context,['product','episode'],true))$id=get_queried_object_id();
  else{$page=get_page_by_path($context==='b2b-supply'?'solutions/b2b-supply':$context);$id=$page?$page->ID:0;}
 }
 $saved=str_starts_with($context,'category:')?get_term_meta($id,'_armaghan_sections',true):get_post_meta($id,$context==='global'?'_armaghan_identity':'_armaghan_sections',true);
 if(!is_array($saved))$saved=[];$sections=[];
 foreach(armaghan_section_schema($context) as $section){$fields=[];
  foreach($section['fields'] as $key=>$field){$value=$saved[$section['id']][$key]??$field['value'];
   if($context==='home'&&$section['id']==='hero'&&!isset($saved['hero'][$key])){$legacy=['line1'=>'vatan_trade_line_one','line2'=>'vatan_trade_line_two','body'=>'vatan_trade_description'];if(isset($legacy[$key]))$value=get_theme_mod($legacy[$key],$value);}
   if(!isset(armaghan_section_catalog()[$context])&&!isset($saved[$section['id']][$key])&&$section['id']==='hero'&&$key==='line1'){if(str_starts_with($context,'category:')){$term=get_term($id,'vatan_category');$value=$term&&!is_wp_error($term)?$term->name:'';}else $value=get_the_title($id);}
   $fields[$key]=is_string($value)?$value:'';
  }
  $sections[$section['id']]=['label'=>$section['label'],'layout'=>$section['layout'],'values'=>$fields];
 }
 return $sections;
}
function armaghan_clean_sections($raw,$context){
 $clean=[];if(!is_array($raw))return $clean;
 foreach(armaghan_section_schema($context) as $section)foreach($section['fields'] as $key=>$field){
  if(!isset($raw[$section['id']][$key])||!is_string($raw[$section['id']][$key]))continue;$value=$raw[$section['id']][$key];
  if(in_array($field['type'],['image','video'],true)){
   if(preg_match('~^asset:[a-zA-Z0-9_./-]+$~D',$value)&&!str_contains($value,'..'))$value=$value;
   elseif(preg_match('/^attachment:[0-9]+$/D',$value))$value=$value;
   else $value=esc_url_raw($value,['http','https']);
  }elseif($field['type']==='url')$value=str_starts_with($value,'#')?'#'.sanitize_title(substr($value,1)):esc_url_raw($value,['http','https','tel','mailto']);
  else $value=mb_substr($field['type']==='textarea'?sanitize_textarea_field($value):sanitize_text_field($value),0,12000);
  $clean[$section['id']][$key]=$value;
 }
 return $clean;
}
function armaghan_section_editor($context,$id,$prefix='armaghan_sections'){
 $data=armaghan_section_data($context,$id);
 echo '<div class="armaghan-editor" dir="rtl"><p class="armaghan-editor-guide">هر بخش مطابق ترتیب نمایش سایت است. متن، تصویر و ویدیو را همین‌جا تغییر دهید و به‌روزرسانی را بزنید. عنوان و متن اصلی نوشته در ویرایشگر وردپرس؛ تلفن و نشانی در تنظیمات وطن؛ منوها در نمایش ← فهرست‌ها مدیریت می‌شوند.</p><nav class="ag-editor-nav" aria-label="انتخاب سکشن">';
 foreach(armaghan_section_schema($context) as $s)echo '<a href="#ag-edit-'.esc_attr($prefix.'-'.$s['id']).'">'.esc_html($s['label']).'</a>';echo '</nav>';
 foreach(armaghan_section_schema($context) as $s){
  echo '<details class="ag-edit-section" id="ag-edit-'.esc_attr($prefix.'-'.$s['id']).'"><summary>'.esc_html($s['label']).'</summary><div class="ag-edit-fields">';
  foreach($s['fields'] as $key=>$field){$value=$data[$s['id']]['values'][$key];$name=$prefix.'['.$s['id'].']['.$key.']';$fid='ag-field-'.$prefix.'-'.$s['id'].'-'.$key;
   echo '<div class="ag-edit-field ag-edit-'.esc_attr($field['type']).'"><label for="'.esc_attr($fid).'">'.esc_html($field['label']).'</label>';
   if($field['type']==='textarea')echo '<textarea id="'.esc_attr($fid).'" name="'.esc_attr($name).'" rows="4">'.esc_textarea($value).'</textarea>';
   else echo '<input type="text" id="'.esc_attr($fid).'" name="'.esc_attr($name).'" value="'.esc_attr($value).'" '.(in_array($field['type'],['image','video','url'],true)?'dir="ltr"':'').'>';
   if(in_array($field['type'],['image','video'],true)){
    echo '<div class="ag-media-preview" data-preview-for="'.esc_attr($fid).'">';$url=str_starts_with($value,'asset:')?get_template_directory_uri().'/assets/'.substr($value,6):(str_starts_with($value,'attachment:')?wp_get_attachment_url((int)substr($value,11)):$value);
    if($url&&$field['type']==='image')echo '<img src="'.esc_url($url).'" alt="">';
    if($url&&$field['type']==='video')echo '<a href="'.esc_url($url).'" target="_blank" rel="noopener">دیدن فایل ویدیو</a>';
    echo '</div><button type="button" class="button ag-pick-media" data-target="'.esc_attr($fid).'" data-media-type="'.esc_attr($field['type']).'">انتخاب یا بارگذاری '.($field['type']==='image'?'تصویر':'ویدیو').'</button> <button type="button" class="button-link ag-clear-media" data-target="'.esc_attr($fid).'">حذف رسانه</button><p class="description">کتابخانه رسانه، نشانی فایل یا دارایی همراه قالب. ویدیو اختیاری با کنترل پخش نمایش داده می‌شود؛ ویدیو اصلی خانه رفتار فعلی را دارد.</p>';
   }
   if($field['type']==='url')echo '<p class="description">نشانی کامل، مسیر داخلی مانند /inquiry/ یا سکشن مانند #questions.</p>';echo '</div>';
  }
  echo '</div></details>';
 }
 echo '</div>';
}
add_action('init',function(){
 foreach(['page','post','vatan_product','vatan_episode'] as $type)register_post_meta($type,'_armaghan_sections',['type'=>'object','single'=>true,'show_in_rest'=>false,'revisions_enabled'=>true,'auth_callback'=>function($allowed,$key,$id){return current_user_can('edit_post',$id);}]);
 register_post_meta('page','_armaghan_identity',['type'=>'object','single'=>true,'show_in_rest'=>false,'revisions_enabled'=>true,'auth_callback'=>function($allowed,$key,$id){return current_user_can('edit_post',$id);}]);
},30);
add_action('add_meta_boxes',function(){foreach(['page','post','vatan_product','vatan_episode'] as $type)add_meta_box('armaghan-sections','سکشن‌های صفحه / متن، تصویر و ویدیو',function($post){wp_nonce_field('armaghan_sections_'.$post->ID,'armaghan_sections_nonce');armaghan_section_editor(armaghan_context($post),$post->ID);if($post->ID===(int)get_option('page_on_front')&&current_user_can('manage_options')){echo '<h3>هویت مشترک تمام صفحات</h3>';armaghan_section_editor('global',$post->ID,'armaghan_identity');}},$type,'normal','high');});
add_action('save_post',function($id,$post){
 if(wp_is_post_revision($id)||wp_is_post_autosave($id)||!isset($_POST['armaghan_sections_nonce'])||!is_string($_POST['armaghan_sections_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['armaghan_sections_nonce'])),'armaghan_sections_'.$id)||!current_user_can('edit_post',$id))return;
 if(isset($_POST['armaghan_sections']))update_post_meta($id,'_armaghan_sections',armaghan_clean_sections(wp_unslash($_POST['armaghan_sections']),armaghan_context($post)));
 if($id===(int)get_option('page_on_front')&&current_user_can('manage_options')&&isset($_POST['armaghan_identity']))update_post_meta($id,'_armaghan_identity',armaghan_clean_sections(wp_unslash($_POST['armaghan_identity']),'global'));
},20,2);
add_action('vatan_category_edit_form_fields',function($term){echo '<tr class="form-field" id="armaghan-sections"><th>سکشن‌های صفحه گروه</th><td>';wp_nonce_field('armaghan_term_'.$term->term_id,'armaghan_term_nonce');armaghan_section_editor(armaghan_context($term),$term->term_id);echo '</td></tr>';});
add_action('edited_vatan_category',function($id){if(!current_user_can('edit_term',$id)||!isset($_POST['armaghan_term_nonce'])||!is_string($_POST['armaghan_term_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['armaghan_term_nonce'])),'armaghan_term_'.$id))return;if(isset($_POST['armaghan_sections']))update_term_meta($id,'_armaghan_sections',armaghan_clean_sections(wp_unslash($_POST['armaghan_sections']),armaghan_context(get_term($id,'vatan_category'))));});
add_action('admin_enqueue_scripts',function(){$screen=get_current_screen();if(!$screen||(!in_array($screen->post_type,['page','post','vatan_product','vatan_episode'],true)&&$screen->taxonomy!=='vatan_category'))return;wp_enqueue_media();wp_enqueue_style('armaghan-editor',plugins_url('../assets/section-editor.css',__FILE__),[],filemtime(dirname(__DIR__).'/assets/section-editor.css'));wp_enqueue_script('armaghan-editor',plugins_url('../assets/section-editor.js',__FILE__),['media-editor'],filemtime(dirname(__DIR__).'/assets/section-editor.js'),true);});
add_action('admin_bar_menu',function($bar){if(is_admin()||!is_user_logged_in())return;$id=is_home()?(int)get_option('page_for_posts'):get_queried_object_id();$url=is_tax('vatan_category')?get_edit_term_link($id,'vatan_category'):get_edit_post_link($id,'raw');if($url)$bar->add_node(['id'=>'armaghan-edit-sections','title'=>'ویرایش سکشن‌های این صفحه','href'=>$url.'#armaghan-sections']);},90);
// These templates use section fields. Keep their native edit screen clear and immediately accessible.
add_filter('use_block_editor_for_post',function($use,$post){return in_array($post->post_type,['page','post','vatan_product','vatan_episode'],true)?false:$use;},10,2);
