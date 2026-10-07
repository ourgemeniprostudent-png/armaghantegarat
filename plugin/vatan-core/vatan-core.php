<?php
/**
 * Plugin Name: VATAN Core
 * Description: Product and media management, persistent inquiries and Multisite setup for Armaghan Tejarat Vatan.
 * Version: 1.2.0
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Author: VATAN
 * License: GPL-2.0-or-later
 */
if (!defined('ABSPATH')) exit;
define('VATAN_CORE_VERSION','1.2.0');
require_once __DIR__.'/includes/sections.php';
function vatan_core_categories(){return ['coffee'=>'قهوه','rice'=>'برنج','dried-fruits'=>'خشکبار','spices'=>'ادویه','legumes'=>'حبوبات'];}
function vatan_customer_types(){return ['cafe'=>'کافه','restaurant'=>'رستوران','hotel'=>'هتل','organization'=>'سازمان','store'=>'فروشگاه','wholesale'=>'عمده‌فروش / بنکدار','personal'=>'شخصی','other'=>'سایر'];}
function vatan_core_register(){
 register_post_type('vatan_product',['labels'=>['name'=>'محصولات وطن','singular_name'=>'محصول','add_new_item'=>'افزودن محصول','edit_item'=>'ویرایش محصول'],'public'=>true,'show_in_rest'=>true,'menu_icon'=>'dashicons-products','has_archive'=>false,'rewrite'=>['slug'=>'products/%vatan_category%','with_front'=>false],'supports'=>['title','editor','excerpt','thumbnail','revisions']]);
 register_taxonomy('vatan_category',['vatan_product'],['labels'=>['name'=>'گروه‌های محصول','singular_name'=>'گروه محصول'],'public'=>true,'hierarchical'=>true,'show_in_rest'=>true,'rewrite'=>['slug'=>'products','with_front'=>false]]);
 register_post_type('vatan_episode',['labels'=>['name'=>'رسانه و پادکست','singular_name'=>'اپیزود','add_new_item'=>'افزودن اپیزود'],'public'=>true,'show_in_rest'=>true,'menu_icon'=>'dashicons-microphone','rewrite'=>['slug'=>'media/podcast','with_front'=>false],'supports'=>['title','editor','excerpt','thumbnail','revisions']]);
 $caps=[];foreach(['edit_post','read_post','delete_post','edit_posts','edit_others_posts','publish_posts','read_private_posts','delete_posts','delete_private_posts','delete_published_posts','delete_others_posts','edit_private_posts','edit_published_posts'] as $cap){$caps[$cap]='manage_options';}$caps['create_posts']='do_not_allow';
 register_post_type('vatan_lead',['labels'=>['name'=>'درخواست‌های همکاری','singular_name'=>'درخواست','edit_item'=>'پیگیری درخواست'],'public'=>false,'publicly_queryable'=>false,'exclude_from_search'=>true,'show_ui'=>true,'show_in_rest'=>false,'menu_icon'=>'dashicons-clipboard','supports'=>['title'],'capabilities'=>$caps,'map_meta_cap'=>false]);
}
add_action('init','vatan_core_register');
add_filter('post_type_link',function($url,$post){if($post->post_type==='vatan_product'){$t=get_the_terms($post,'vatan_category');$slug=$t&&!is_wp_error($t)?$t[0]->slug:'coffee';return str_replace('%vatan_category%',$slug,$url);}return $url;},10,2);
function vatan_core_setup(){
 vatan_core_register();
 foreach(vatan_core_categories() as $s=>$n) if(!term_exists($s,'vatan_category'))wp_insert_term($n,'vatan_category',['slug'=>$s]);
 $pages=['home'=>'خانه','products'=>'محصولات','solutions'=>'همکاری تجاری','media'=>'رسانه وطن','blog'=>'مجله وطن','about'=>'درباره وطن','contact'=>'تماس با ما','inquiry'=>'درخواست استعلام','thank-you'=>'درخواست ثبت شد','search'=>'جستجو','faq'=>'پرسش‌های متداول','privacy'=>'حریم خصوصی'];
 foreach($pages as $slug=>$title){if(!get_page_by_path($slug))wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_title'=>$title,'post_name'=>$slug]);}
 $parent=get_page_by_path('solutions');if($parent&&!get_page_by_path('solutions/b2b-supply'))wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_title'=>'تامین سازمانی و عمده','post_name'=>'b2b-supply','post_parent'=>$parent->ID]);
 $home=get_page_by_path('home');$blog=get_page_by_path('blog');if($home){update_option('show_on_front','page');update_option('page_on_front',$home->ID);}if($blog)update_option('page_for_posts',$blog->ID);
 if(!get_option('permalink_structure')) update_option('permalink_structure','/blog/%postname%/');
 add_option('vatan_phone','02191028166');add_option('vatan_address','تهران، سهروردی شمالی، کوچه زمانی، پلاک ۱۱، ساختمان ایلیا، طبقه ۳، واحد ۹');add_option('vatan_whatsapp','');add_option('vatan_notify_email','');add_option('vatan_retention_days',365);
 flush_rewrite_rules();
}
register_activation_hook(__FILE__,function($network){if(is_multisite()&&$network){foreach(get_sites(['number'=>0]) as $site){switch_to_blog($site->blog_id);vatan_core_setup();restore_current_blog();}}else vatan_core_setup();});
add_action('wp_initialize_site',function($site){switch_to_blog($site->blog_id);vatan_core_setup();restore_current_blog();},20);
add_action('template_redirect',function(){if(is_page(['inquiry','thank-you'])){nocache_headers();if(!defined('DONOTCACHEPAGE'))define('DONOTCACHEPAGE',true);}if(is_multisite()&&get_current_blog_id()!==get_main_site_id()&&get_option('vatan_language_pending',false)&&!current_user_can('manage_options')){global $wp_query;$wp_query->set_404();status_header(404);nocache_headers();}});
function vatan_digits($value){return strtr($value,['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9','٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9']);}
function vatan_lead_redirect_error($errors,$data){$token=wp_generate_password(40,false,false);set_transient('vatan_error_'.$token,['errors'=>$errors,'data'=>$data],10*MINUTE_IN_SECONDS);wp_safe_redirect(add_query_arg('form_error',$token,home_url('/inquiry/')),303);exit;}
function vatan_submit_lead(){
 if($_SERVER['REQUEST_METHOD']!=='POST'){status_header(405);exit;}
 $keys=['name','mobile','company','city','customer_type','category','product','quantity','notes','preferred_time','source_url','consent','request_key'];$data=[];
 foreach($keys as $k){$raw=isset($_POST[$k])&&!is_array($_POST[$k])?wp_unslash($_POST[$k]):'';$data[$k]=$k==='notes'?sanitize_textarea_field($raw):sanitize_text_field($raw);}
 $errors=[];
 if(!isset($_POST['vatan_nonce'])||is_array($_POST['vatan_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['vatan_nonce'])),'vatan_inquiry'))$errors['general']='اعتبار فرم پایان یافته است. اطلاعات شما حفظ شده؛ دوباره ارسال کنید.';
 if(!empty($_POST['website']))$errors['general']='ثبت درخواست انجام نشد. با شماره شرکت تماس بگیرید.';
 if(mb_strlen($data['name'])<2||mb_strlen($data['name'])>80)$errors['name']='نام را بین ۲ تا ۸۰ کاراکتر وارد کنید.';
 $mobile=preg_replace('/[\s\-()]/u','',vatan_digits($data['mobile']));if(str_starts_with($mobile,'+98'))$mobile='0'.substr($mobile,3);elseif(str_starts_with($mobile,'0098'))$mobile='0'.substr($mobile,4);if(!preg_match('/^09[0-9]{9}$/D',$mobile))$errors['mobile']='شماره موبایل معتبر ایران را وارد کنید؛ مانند ۰۹۱۲۱۲۳۴۵۶۷.';$data['mobile']=$mobile;
 if(mb_strlen($data['city'])<2||mb_strlen($data['city'])>80)$errors['city']='نام شهر را وارد کنید.';
 if(!isset(vatan_customer_types()[$data['customer_type']]))$errors['customer_type']='نوع کسب‌وکار یا مشتری را انتخاب کنید.';
 if(!isset(vatan_core_categories()[$data['category']]))$errors['category']='گروه محصول را انتخاب کنید.';
 if(mb_strlen($data['notes'])>1000)$errors['notes']='توضیحات باید حداکثر ۱۰۰۰ کاراکتر باشد.';
 foreach(['company','product','quantity'] as $k)if(mb_strlen($data[$k])>150)$errors[$k]='این فیلد باید حداکثر ۱۵۰ کاراکتر باشد.';
 if(!in_array($data['preferred_time'],['','morning','noon','afternoon'],true))$errors['preferred_time']='زمان تماس را از گزینه‌های فرم انتخاب کنید.';
 if($data['consent']!=='1')$errors['consent']='برای ثبت، رضایت به استفاده از اطلاعات تماس لازم است.';
 $data['source_url']=esc_url_raw($data['source_url']);if(strlen($data['source_url'])>1000)$data['source_url']='';
 if(!preg_match('/^[a-zA-Z0-9]{32,64}$/D',$data['request_key']))$errors['general']='شناسه فرم معتبر نیست. دوباره ارسال کنید.';
 if($errors)vatan_lead_redirect_error($errors,$data);
 $key='vatan_sent_'.hash('sha256',$data['request_key']);$existing=get_transient($key);if($existing){wp_safe_redirect(add_query_arg('reference',$existing,home_url('/thank-you/')),303);exit;}
 $rate_key='vatan_rate_'.hash_hmac('sha256',$_SERVER['REMOTE_ADDR']??'unknown',wp_salt('nonce'));$rate=(int)get_transient($rate_key);if($rate>=10)vatan_lead_redirect_error(['general'=>'تعداد درخواست‌ها زیاد است. کمی بعد دوباره تلاش کنید یا با شرکت تماس بگیرید.'],$data);
 $lock='vatan_lock_'.hash('sha256',$data['request_key']);if(!add_option($lock,time(),'',false)){if((int)get_option($lock)<time()-120){delete_option($lock);vatan_lead_redirect_error(['general'=>'ارسال قبلی کامل نشد؛ اکنون دوباره ارسال کنید.'],$data);}vatan_lead_redirect_error(['general'=>'ارسال این درخواست در حال بررسی است؛ کمی بعد دوباره تلاش کنید.'],$data);}
 $reference='VAT-'.strtoupper(wp_generate_password(10,false,false));
 $meta=['_vatan_reference'=>$reference,'_vatan_status'=>'new','_vatan_consent_at'=>gmdate('c'),'_vatan_consent_version'=>'1.0','_vatan_source_url'=>$data['source_url']];foreach($data as $k=>$v){if(!in_array($k,['request_key','consent','source_url'],true))$meta['_vatan_'.$k]=$v;}
 $id=wp_insert_post(['post_type'=>'vatan_lead','post_status'=>'private','post_title'=>$reference.' — '.$data['name'],'meta_input'=>$meta],true);
 if(is_wp_error($id)||!$id){delete_option($lock);vatan_lead_redirect_error(['general'=>'درخواست ذخیره نشد. اطلاعات شما حفظ شده؛ دوباره تلاش کنید یا تلفنی تماس بگیرید.'],$data);}
 set_transient($key,$reference,DAY_IN_SECONDS);delete_option($lock);set_transient($rate_key,$rate+1,HOUR_IN_SECONDS);
 $notify=get_option('vatan_notify_email','');if(is_email($notify)){wp_mail($notify,'درخواست جدید وطن: '.$reference,'یک درخواست جدید ثبت شده است. برای مشاهده و پیگیری وارد پیشخوان شوید: '.admin_url('post.php?post='.$id.'&action=edit'));}
 wp_safe_redirect(add_query_arg('reference',$reference,home_url('/thank-you/')),303);exit;
}
add_action('admin_post_nopriv_vatan_inquiry','vatan_submit_lead');add_action('admin_post_vatan_inquiry','vatan_submit_lead');
require_once __DIR__.'/includes/inquiry-form.php';
add_shortcode('vatan_inquiry','vatan_inquiry_form');
add_action('add_meta_boxes',function(){add_meta_box('vatan-product-specs','مشخصات محصول','vatan_product_fields','vatan_product','normal','high');add_meta_box('vatan-media','فایل رسانه و متن پیاده‌شده','vatan_media_fields','vatan_episode','normal','high');add_meta_box('vatan-lead','اطلاعات و پیگیری درخواست','vatan_lead_fields','vatan_lead','normal','high');});
function vatan_product_fields($post){wp_nonce_field('vatan_meta','vatan_meta_nonce');$value=get_post_meta($post->ID,'_vatan_specs',true);echo '<p>هر مشخصه در یک خط با جداکننده |؛ فقط اطلاعات تاییدشده.</p><textarea name="vatan_specs" rows="8" style="width:100%" placeholder="خاستگاه | برزیل">'.esc_textarea($value).'</textarea>';echo '<p><label>وضعیت استعلام <input name="vatan_availability" value="'.esc_attr(get_post_meta($post->ID,'_vatan_availability',true)).'" placeholder="برای شرایط تامین استعلام بگیرید"></label></p>';}
function vatan_media_fields($post){wp_nonce_field('vatan_meta','vatan_meta_nonce');foreach(['audio'=>'نشانی فایل صوتی','video'=>'نشانی فایل ویدیو','duration'=>'مدت اپیزود','transcript'=>'متن پیاده‌شده (متن واقعی)'] as $k=>$label){echo '<p><label>'.esc_html($label).'<br>';if($k==='transcript')echo '<textarea name="vatan_'.$k.'" rows="10" style="width:100%">'.esc_textarea(get_post_meta($post->ID,'_vatan_'.$k,true)).'</textarea>';else echo '<input style="width:100%" name="vatan_'.$k.'" value="'.esc_attr(get_post_meta($post->ID,'_vatan_'.$k,true)).'">';echo '</label></p>';}}
function vatan_lead_fields($post){if(!current_user_can('manage_options'))return;wp_nonce_field('vatan_meta','vatan_meta_nonce');echo '<table class="widefat striped">';$labels=['reference'=>'کد پیگیری','name'=>'نام','mobile'=>'موبایل','company'=>'مجموعه','city'=>'شهر','customer_type'=>'نوع مشتری','category'=>'گروه محصول','product'=>'محصول','quantity'=>'مقدار','notes'=>'توضیحات','preferred_time'=>'زمان تماس','source_url'=>'صفحه مبدا','consent_at'=>'زمان رضایت'];foreach($labels as $k=>$l){$v=get_post_meta($post->ID,'_vatan_'.$k,true);if($k==='category')$v=vatan_core_categories()[$v]??$v;if($k==='customer_type')$v=vatan_customer_types()[$v]??$v;echo '<tr><th>'.esc_html($l).'</th><td style="white-space:pre-wrap">'.esc_html($v).'</td></tr>';}echo '</table><p><label>وضعیت پیگیری <select name="vatan_status">';foreach(['new'=>'جدید','contacted'=>'تماس گرفته شد','proposal'=>'پیشنهاد ارایه شد','closed'=>'بسته شد'] as $k=>$l)echo '<option value="'.esc_attr($k).'" '.selected(get_post_meta($post->ID,'_vatan_status',true),$k,false).'>'.esc_html($l).'</option>';echo '</select></label></p><p><label>یادداشت داخلی<br><textarea name="vatan_internal_notes" rows="5" style="width:100%">'.esc_textarea(get_post_meta($post->ID,'_vatan_internal_notes',true)).'</textarea></label></p>';}
add_action('save_post',function($id,$post){if(defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE)return;if(wp_is_post_revision($id)||!isset($_POST['vatan_meta_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['vatan_meta_nonce'])),'vatan_meta')||!current_user_can('edit_post',$id))return;
 if($post->post_type==='vatan_product'){foreach(['specs','availability'] as $k)if(isset($_POST['vatan_'.$k])&&!is_array($_POST['vatan_'.$k]))update_post_meta($id,'_vatan_'.$k,sanitize_textarea_field(wp_unslash($_POST['vatan_'.$k])));}
 if($post->post_type==='vatan_episode'){foreach(['audio','video','duration','transcript'] as $k)if(isset($_POST['vatan_'.$k])&&!is_array($_POST['vatan_'.$k])){ $v=wp_unslash($_POST['vatan_'.$k]);update_post_meta($id,'_vatan_'.$k,in_array($k,['audio','video'],true)?esc_url_raw($v):sanitize_textarea_field($v));}}
 if($post->post_type==='vatan_lead'&&current_user_can('manage_options')){if(isset($_POST['vatan_status'])&&in_array($_POST['vatan_status'],['new','contacted','proposal','closed'],true))update_post_meta($id,'_vatan_status',sanitize_key($_POST['vatan_status']));if(isset($_POST['vatan_internal_notes']))update_post_meta($id,'_vatan_internal_notes',sanitize_textarea_field(wp_unslash($_POST['vatan_internal_notes'])));}
},10,2);
add_filter('manage_vatan_lead_posts_columns',function($c){return ['cb'=>$c['cb'],'title'=>'درخواست','vatan_mobile'=>'موبایل','vatan_category'=>'گروه محصول','vatan_status'=>'پیگیری','date'=>'تاریخ'];});
add_action('manage_vatan_lead_posts_custom_column',function($c,$id){if($c==='vatan_mobile')echo esc_html(get_post_meta($id,'_vatan_mobile',true));if($c==='vatan_category')echo esc_html(vatan_core_categories()[get_post_meta($id,'_vatan_category',true)]??'');if($c==='vatan_status')echo esc_html(['new'=>'جدید','contacted'=>'تماس گرفته شد','proposal'=>'پیشنهاد ارایه شد','closed'=>'بسته شد'][get_post_meta($id,'_vatan_status',true)]??'جدید');},10,2);
add_action('admin_menu',function(){add_options_page('تنظیمات وطن','تنظیمات وطن','manage_options','vatan-settings','vatan_settings_page');});
add_action('admin_init',function(){foreach(['phone','address','whatsapp','notify_email'] as $k)register_setting('vatan_settings','vatan_'.$k,['sanitize_callback'=>$k==='notify_email'?'sanitize_email':'sanitize_text_field']);});
function vatan_settings_page(){if(!current_user_can('manage_options'))return;?><div class="wrap"><h1>تنظیمات وطن</h1><form method="post" action="options.php"><?php settings_fields('vatan_settings'); ?><table class="form-table"><?php foreach(['phone'=>'تلفن عمومی','address'=>'نشانی دفتر','whatsapp'=>'واتس‌اپ تجاری (با کد کشور، فقط عدد)','notify_email'=>'ایمیل اعلان درخواست (خالی = بدون اعلان ایمیل)'] as $k=>$l){echo '<tr><th><label for="vatan-'.$k.'">'.esc_html($l).'</label></th><td><input class="regular-text" id="vatan-'.$k.'" name="vatan_'.$k.'" value="'.esc_attr(get_option('vatan_'.$k,'')).'"></td></tr>';} ?></table><p>درخواست‌ها همیشه در پیشخوان ذخیره می‌شوند. اعلان ایمیل فقط پس از تنظیم آدرس و آماده‌بودن سرویس ارسال ایمیل قابل اتکاست.</p><?php submit_button(); ?></form><h2>خروجی درخواست‌ها</h2><p><a class="button button-secondary" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=vatan_export'),'vatan_export')); ?>">دریافت CSV درخواست‌ها</a></p><h2>نگهداری داده</h2><p>حذف درخواست‌های بسته‌شده از بخش درخواست‌های همکاری انجام می‌شود. هیچ حذف خودکاری بدون تصمیم مدیر اجرا نمی‌شود.</p></div><?php }
add_action('admin_post_vatan_export',function(){if(!current_user_can('manage_options'))wp_die('دسترسی ندارید.',403);check_admin_referer('vatan_export');nocache_headers();header('Content-Type: text/csv; charset=utf-8');header('Content-Disposition: attachment; filename="vatan-inquiries.csv"');$out=fopen('php://output','w');fwrite($out,"\xEF\xBB\xBF");$keys=['reference','name','mobile','company','city','customer_type','category','product','quantity','notes','preferred_time','status','consent_at'];fputcsv($out,$keys,',','"','');$page=1;do{$posts=get_posts(['post_type'=>'vatan_lead','post_status'=>'private','posts_per_page'=>200,'paged'=>$page,'orderby'=>'ID','order'=>'ASC']);foreach($posts as $post){$row=[];foreach($keys as $k){$v=(string)get_post_meta($post->ID,'_vatan_'.$k,true);if(preg_match('/^[\s]*[=+@\-]/u',$v))$v="'".$v;$row[]=$v;}fputcsv($out,$row,',','"','');}$page++;}while(count($posts)===200);fclose($out);exit;});
