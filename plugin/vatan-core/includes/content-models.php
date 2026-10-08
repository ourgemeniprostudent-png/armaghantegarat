<?php
/** Native, revisioned fields for published product and editorial data. */
if (!defined('ABSPATH')) exit;
function vatan_public_ids($value, $types=['post','vatan_product','vatan_episode']) {
    $ids=is_array($value)?$value:preg_split('/[\s,،]+/u',(string)$value);
    return array_values(array_filter(array_unique(array_map('absint',$ids)),function($id)use($types){$p=get_post($id);return $p&&in_array($p->post_type,$types,true)&&$p->post_status==='publish';}));
}
function vatan_gallery_ids($value) {
    $ids=is_array($value)?$value:preg_split('/[\s,،]+/u',(string)$value);
    return array_values(array_filter(array_unique(array_map('absint',$ids)),function($id){return wp_attachment_is_image($id);}));
}
function vatan_timestamps($value) {
    $out=[];
    foreach(explode("\n",(string)$value) as $line){
        $pair=explode('|',$line,2);if(count($pair)!==2)continue;
        $time=trim(vatan_digits($pair[0]));if(!preg_match('/^(?:\d{1,3}:)?[0-5]?\d:[0-5]\d$/D',$time))continue;
        $parts=array_map('intval',explode(':',$time));$seconds=0;foreach($parts as $n)$seconds=$seconds*60+$n;
        $title=mb_substr(sanitize_text_field(trim($pair[1])),0,160);if($title!=='')$out[]=['seconds'=>$seconds,'time'=>$time,'title'=>$title];
    }
    usort($out,fn($a,$b)=>$a['seconds']<=>$b['seconds']);return array_slice($out,0,100);
}
function vatan_topics() {return vatan_core_categories()+['supply'=>'تامین و همکاری','quality'=>'شناخت و نگهداری'];}
add_action('init',function(){
    register_taxonomy('vatan_topic',['post','vatan_episode'],['labels'=>['name'=>'موضوعات مجله و رسانه','singular_name'=>'موضوع'],'public'=>true,'show_in_rest'=>true,'hierarchical'=>true,'rewrite'=>false,'show_admin_column'=>true]);
    foreach(['post','vatan_product','vatan_episode'] as $type){
        foreach(['_vatan_gallery'=>'array','_vatan_related'=>'array','_vatan_timestamps'=>'string','_vatan_public_author'=>'string','_vatan_seo_title'=>'string','_vatan_seo_description'=>'string','_vatan_featured'=>'boolean','_vatan_specs'=>'string','_vatan_availability'=>'string','_vatan_audio'=>'string','_vatan_video'=>'string','_vatan_duration'=>'string','_vatan_transcript'=>'string','_vatan_captions'=>'string'] as $key=>$format)
            register_post_meta($type,$key,['type'=>$format,'single'=>true,'show_in_rest'=>false,'revisions_enabled'=>true,'auth_callback'=>fn($allowed,$key,$id)=>current_user_can('edit_post',$id)]);
    }
},25);
function vatan_editorial_setup() {
    foreach(vatan_topics() as $slug=>$name)if(!term_exists($slug,'vatan_topic'))wp_insert_term($name,'vatan_topic',['slug'=>$slug]);
}
add_action('admin_init','vatan_editorial_setup');
add_action('add_meta_boxes',function(){
    foreach(['post','vatan_product','vatan_episode'] as $type)add_meta_box('vatan-publishing','رسانه، ارتباط محتوا و تنظیمات انتشار','vatan_publishing_fields',$type,'normal','high');
});
function vatan_publishing_fields($post) {
    wp_nonce_field('vatan_publishing_'.$post->ID,'vatan_publishing_nonce');
    if($post->post_type==='vatan_product'){
        $ids=vatan_gallery_ids(get_post_meta($post->ID,'_vatan_gallery',true));
        echo '<p>گالری محصول: تصاویر واقعی را انتخاب کنید. ترتیب انتخاب، ترتیب گالری است؛ ترتیب شناسه‌ها نیز قابل ویرایش است. تصویر شاخص جداگانه حفظ می‌شود.</p><label for="vatan-gallery">شناسه تصاویر به ترتیب</label><input id="vatan-gallery" class="widefat" name="vatan_gallery" value="'.esc_attr(implode(',',$ids)).'"><button type="button" class="button" data-vatan-gallery="vatan-gallery">انتخاب تصاویر گالری</button><div data-gallery-preview style="display:flex;gap:10px;flex-wrap:wrap">';
        foreach($ids as $id)echo wp_get_attachment_image($id,'thumbnail',false,['style'=>'width:80px;height:80px;object-fit:cover']);echo '</div>';
    }
    if($post->post_type==='vatan_episode'){
        echo '<p><label for="vatan-timestamps">زمان‌بندی گفتگو؛ هر خط به صورت 02:15 | عنوان بخش</label><textarea id="vatan-timestamps" name="vatan_timestamps" rows="6" class="widefat">'.esc_textarea(get_post_meta($post->ID,'_vatan_timestamps',true)).'</textarea></p><p><label for="vatan-captions">نشانی زیرنویس فارسی با قالب WebVTT</label><input id="vatan-captions" name="vatan_captions" class="widefat" type="url" value="'.esc_attr(get_post_meta($post->ID,'_vatan_captions',true)).'"></p>';
    }
    if($post->post_type==='post')echo '<p><label for="vatan-author">نام عمومی نویسنده یا گروه تحریریه</label><input id="vatan-author" class="widefat" name="vatan_public_author" value="'.esc_attr(get_post_meta($post->ID,'_vatan_public_author',true)?:'تحریریه ارمغان تجارت وطن').'"></p>';
    echo '<p><label><input type="checkbox" name="vatan_featured" value="1" '.checked(get_post_meta($post->ID,'_vatan_featured',true),true,false).'> نمایش به عنوان محتوای منتخب</label></p><p><label for="vatan-related">مطالب و محصولات مرتبط؛ انتخاب چندتایی</label><select id="vatan-related" name="vatan_related[]" multiple size="8" class="widefat">';
    $selected=vatan_public_ids(get_post_meta($post->ID,'_vatan_related',true));
    foreach(get_posts(['post_type'=>['post','vatan_product','vatan_episode'],'post_status'=>'publish','numberposts'=>-1,'post__not_in'=>[$post->ID],'orderby'=>'title','order'=>'ASC']) as $p)
        echo '<option value="'.esc_attr($p->ID).'" '.(in_array($p->ID,$selected,true)?'selected':'').'>'.esc_html(get_post_type_object($p->post_type)->labels->singular_name.' / '.$p->post_title).'</option>';
    echo '</select><input type="hidden" name="vatan_related_present" value="1"></p>';
    foreach(['seo_title'=>'عنوان برای موتور جستجو (خالی = عنوان نوشته)','seo_description'=>'توضیح برای موتور جستجو (خالی = چکیده)'] as $key=>$label)echo '<p><label for="vatan-'.$key.'">'.esc_html($label).'</label><input id="vatan-'.$key.'" class="widefat" name="vatan_'.$key.'" maxlength="'.($key==='seo_title'?120:300).'" value="'.esc_attr(get_post_meta($post->ID,'_vatan_'.$key,true)).'"></p>';
}
add_action('save_post',function($id,$post){
    if(wp_is_post_revision($id)||wp_is_post_autosave($id)||!current_user_can('edit_post',$id)||!isset($_POST['vatan_publishing_nonce'])||!is_string($_POST['vatan_publishing_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['vatan_publishing_nonce'])),'vatan_publishing_'.$id))return;
    if(isset($_POST['vatan_gallery'])&&is_string($_POST['vatan_gallery']))update_post_meta($id,'_vatan_gallery',vatan_gallery_ids(wp_unslash($_POST['vatan_gallery'])));
    if(isset($_POST['vatan_related_present']))update_post_meta($id,'_vatan_related',vatan_public_ids($_POST['vatan_related']??[]));
    if(isset($_POST['vatan_timestamps'])&&is_string($_POST['vatan_timestamps'])){
        $rows=vatan_timestamps(wp_unslash($_POST['vatan_timestamps']));update_post_meta($id,'_vatan_timestamps',implode("\n",array_map(fn($row)=>$row['time'].' | '.$row['title'],$rows)));
    }
    foreach(['public_author'=>100,'seo_title'=>120,'seo_description'=>300] as $key=>$max)if(isset($_POST['vatan_'.$key])&&is_string($_POST['vatan_'.$key]))update_post_meta($id,'_vatan_'.$key,mb_substr(sanitize_text_field(wp_unslash($_POST['vatan_'.$key])),0,$max));
    if(isset($_POST['vatan_captions'])&&is_string($_POST['vatan_captions']))update_post_meta($id,'_vatan_captions',esc_url_raw(wp_unslash($_POST['vatan_captions'])));
    update_post_meta($id,'_vatan_featured',isset($_POST['vatan_featured']));
},25,2);
add_action('admin_enqueue_scripts',function(){
    $s=get_current_screen();if($s&&in_array($s->post_type,['post','vatan_product','vatan_episode'],true))wp_enqueue_script('vatan-gallery-editor',plugins_url('../assets/gallery-editor.js',__FILE__),['media-editor'],VATAN_CORE_VERSION,true);
});
function vatan_whatsapp_url($title='', $url='') {
    $number=preg_replace('/\D/','',get_option('vatan_whatsapp',''));if(!preg_match('/^[1-9][0-9]{9,14}$/D',$number))return '';
    $title=$title?:wp_get_document_title();$url=$url?:home_url('/');
    return 'https://wa.me/'.$number.'?text='.rawurlencode('سلام، درباره '.$title.' در سایت ارمغان تجارت وطن درخواست مشاوره / استعلام دارم. لینک: '.$url);
}
function vatan_iran_cities() {
    return explode('|','تهران|کرج|مشهد|اصفهان|شیراز|تبریز|قم|اهواز|کرمانشاه|ارومیه|رشت|زاهدان|همدان|کرمان|یزد|اردبیل|بندرعباس|اراک|اسلامشهر|زنجان|سنندج|قزوین|خرم‌آباد|گرگان|ساری|بجنورد|بوشهر|بیرجند|ایلام|شهرکرد|یاسوج|آبادان|کاشان|نجف‌آباد|بابل|آمل|نیشابور|سبزوار|سیرجان|رفسنجان|بروجرد|ملایر|مراغه|خوی|مهاباد|سقز|شاهین‌شهر|خمینی‌شهر|دزفول|اندیمشک|بهبهان|ماهشهر|شوشتر|شوش|گنبد کاووس|قائم‌شهر|بابلسر|چالوس|نوشهر|تنکابن|لاهیجان|انزلی|رودسر|آستارا|لنگرود|تالش|بوکان|میاندوآب|سلماس|بناب|شبستر|اهر|سراب|مرند|میانه|ابهر|خرمدره|تاکستان|الوند|ساوه|خمین|محلات|دلیجان|گلپایگان|خوانسار|نطنز|شهرضا|مبارکه|زرین‌شهر|فولادشهر|اردکان|میبد|بافق|ابرکوه|تفت|طبس|قائن|فردوس|تربت حیدریه|تربت جام|قوچان|کاشمر|گناباد|تایباد|چناران|شیروان|اسفراین|دماوند|فیروزکوه|ورامین|پاکدشت|قرچک|پیشوا|شهریار|قدس|ملارد|رباط‌کریم|بهارستان|پردیس|اندیشه|نظرآباد|هشتگرد|فردیس|اشتهارد|سمنان|شاهرود|دامغان|گرمسار|مرودشت|کازرون|جهرم|لار|فسا|داراب|آباده|اقلید|برازجان|گناوه|دیر|کنگان|عسلویه|جم|بندر لنگه|میناب|قشم|کیش|رودان|بندر خمیر|چابهار|زابل|ایرانشهر|خاش|سراوان|جیرفت|بم|زرند|بافت|کهنوج|شهربابک|دورود|الیگودرز|کوهدشت|ازنا|نورآباد|پلدختر|نهاوند|تویسرکان|اسدآباد|بهار|کبودرآهنگ|سنقر|کنگاور|سرپل ذهاب|اسلام‌آباد غرب|پاوه|جوانرود|قروه|بانه|مریوان|بیجار|دیواندره|دهلران|آبدانان|مهران|دره‌شهر|دهدشت|گچساران|دوگنبدان|بروجن|لردگان|فارسان|بن|سامان|خلخال|مشگین‌شهر|پارس‌آباد|گرمی|نمین|علی‌آباد کتول|آق‌قلا|کردکوی|بندر ترکمن|آزادشهر|رامیان|نور|محمودآباد|رامسر|فریدون‌کنار|بهشهر|نکا|جویبار');
}
