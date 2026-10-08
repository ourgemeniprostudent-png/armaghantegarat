<?php
if(!defined('ABSPATH'))exit;
function vatan_feature_labels(){return [
 'animations'=>'موشن‌های معرفی و صفحات داخلی','home_video'=>'ویدیو خودکار معرفی خانه','home_products'=>'گالری حوزه‌های واردات در خانه','home_cooperation'=>'گالری همکاری در خانه','mobile_cta'=>'دکمه‌های ثابت تماس و استعلام موبایل','article_toc'=>'فهرست مطالعه مقاله',
 'contact_form'=>'فرم کوتاه تماس با دفتر','office_map'=>'نقشه دقیق دفتر با بارگذاری اختیاری',
 'blog_discovery'=>'جستجو و موضوعات مجله','product_filters'=>'جستجو و فیلتر محصولات',
 'media_discovery'=>'جستجو و موضوعات رسانه','video_library'=>'کتابخانه ویدیو',
 'related_content'=>'محصولات و مطالب مرتبط','product_gallery'=>'گالری چندعکسی محصول',
 'product_short_form'=>'فرم داخل صفحه محصول','whatsapp'=>'گفتگو در واتس‌اپ',
 'mobile_footer'=>'فوتر بازشونده موبایل','seo'=>'متادیتا و داده ساختاریافته'
];}
function vatan_feature($key){$saved=get_option('vatan_features',[]);return !array_key_exists($key,(array)$saved)||$saved[$key]==='1';}
function vatan_brand_accent_sanitize($value){
 $value=is_array($value)?$value:[];
 return ['enabled'=>isset($value['enabled'])&&$value['enabled']==='1'?'1':'0',
  'color'=>sanitize_hex_color(is_scalar($value['color']??null)?(string)$value['color']:'')?:'#76273b',
  'strength'=>in_array($value['strength']??'', ['soft','balanced','defined'],true)?$value['strength']:'balanced'];
}
function vatan_brand_accent(){
 $saved=get_option('vatan_brand_accent',null);
 return $saved===null?['enabled'=>'1','color'=>'#76273b','strength'=>'balanced']:vatan_brand_accent_sanitize($saved);
}
add_action('admin_init',function(){
 register_setting('vatan_settings','vatan_brand_accent',['type'=>'array','sanitize_callback'=>'vatan_brand_accent_sanitize']);
 register_setting('vatan_settings','vatan_features',['type'=>'array','sanitize_callback'=>function($value){$clean=[];foreach(vatan_feature_labels() as $key=>$label)$clean[$key]=isset($value[$key])&&$value[$key]==='1'?'1':'0';return $clean;}]);
 foreach(['map_latitude','map_longitude'] as $key)register_setting('vatan_settings','vatan_'.$key,['sanitize_callback'=>function($value)use($key){$text=is_scalar($value)?trim((string)$value):'';$v=filter_var($text,FILTER_VALIDATE_FLOAT);$max=$key==='map_latitude'?90:180;return preg_match('/^[+-]?\d{1,3}(?:\.\d{1,15})?$/D',$text)&&$v!==false&&abs($v)<=$max?$text:($key==='map_latitude'?'35.7294807434082':'51.436744689941406');}]);
});
function vatan_settings_page(){
 if(!current_user_can('manage_options'))return;$home=(int)get_option('page_on_front');
 echo '<div class="wrap" dir="rtl"><h1>تنظیمات قالب ارمغان تجارت وطن</h1><p>اطلاعات مشترک و نمایش قابلیت‌ها را اینجا تنظیم کنید. متن، عکس و ویدیو هر صفحه در بخش‌های مرتب همان صفحه قابل تغییر است.</p><h2>دسترسی سریع به ویرایش</h2><p>';
 foreach(['edit.php?post_type=page'=>'صفحات و سکشن‌ها','edit.php'=>'مقاله‌ها','edit.php?post_type=vatan_product'=>'محصولات','edit.php?post_type=vatan_episode'=>'پادکست و ویدیو','edit-tags.php?taxonomy=vatan_category&post_type=vatan_product'=>'گروه‌های محصول','edit-tags.php?taxonomy=vatan_topic'=>'موضوعات مجله و رسانه','nav-menus.php'=>'منوهای هدر و فوتر','post.php?post='.$home.'&action=edit#armaghan-sections'=>'هویت مشترک، نشان و نوشته‌های فوتر','tools.php?page=vatan-events'=>'آمار رویدادها'] as $path=>$label)echo '<a class="button" style="margin:5px" href="'.esc_url(admin_url($path)).'">'.esc_html($label).'</a>';
 echo '</p><form method="post" action="options.php">';settings_fields('vatan_settings');
 echo '<h2>اطلاعات دفتر و کانال‌های ارتباط</h2><table class="form-table">';
 foreach(['phone'=>'تلفن عمومی','address'=>'نشانی کامل دفتر','whatsapp'=>'شماره تجاری واتس‌اپ با کد کشور؛ فقط عدد','public_email'=>'ایمیل عمومی دفتر','hours'=>'ساعات کاری تاییدشده','map_latitude'=>'عرض جغرافیایی دفتر','map_longitude'=>'طول جغرافیایی دفتر','notify_email'=>'ایمیل خصوصی اعلان درخواست؛ خالی = بدون ارسال'] as $key=>$label){$default=['map_latitude'=>'35.7294807434082','map_longitude'=>'51.436744689941406'][$key]??'';echo '<tr><th><label for="setting-'.$key.'">'.esc_html($label).'</label></th><td><input id="setting-'.$key.'" name="vatan_'.$key.'" class="regular-text" value="'.esc_attr(get_option('vatan_'.$key,$default)).'" '.(in_array($key,['phone','whatsapp','public_email','notify_email','map_latitude','map_longitude'],true)?'dir="ltr"':'').'></td></tr>';}
 $accent=vatan_brand_accent();
 echo '</table><h2>رنگ سوم برند؛ زرشکی سلطنتی</h2><p>نسخه آزمایشی با زرشکی عمیق در خط‌ها و نشانه‌های کوچک؛ مشکی و طلایی رنگ‌های اصلی می‌مانند. شدت، وضوح این جزئیات را تغییر می‌دهد و درصد مساحت صفحه نیست. رنگ پیش‌فرض: <code dir="ltr">#76273b</code>.</p><table class="form-table"><tr><th>نمایش رنگ سوم</th><td><input type="hidden" name="vatan_brand_accent[enabled]" value="0"><label><input type="checkbox" name="vatan_brand_accent[enabled]" value="1" '.checked($accent['enabled'],'1',false).'> فعال؛ خاموش‌کردن، ظاهر قبلی را برمی‌گرداند</label></td></tr><tr><th><label for="brand-accent-color">رنگ زرشکی</label></th><td><input type="color" id="brand-accent-color" name="vatan_brand_accent[color]" value="'.esc_attr($accent['color']).'"><p class="description">با انتخاب‌گر رنگ مرورگر، رنگ یا کد دلخواه را وارد کنید.</p></td></tr><tr><th><label for="brand-accent-strength">شدت حضور در جزئیات</label></th><td><select id="brand-accent-strength" name="vatan_brand_accent[strength]">';
 foreach(['soft'=>'ملایم','balanced'=>'متعادل؛ پیشنهاد فعلی','defined'=>'نمایان‌تر'] as $key=>$label)echo '<option value="'.esc_attr($key).'" '.selected($accent['strength'],$key,false).'>'.esc_html($label).'</option>';
 echo '</select></td></tr></table><h2>نمایش قابلیت‌ها</h2><p>خاموش‌کردن نمایش یک قابلیت، اطلاعات ذخیره‌شده آن را حذف نمی‌کند.</p><input type="hidden" name="vatan_features[_present]" value="1"><table class="form-table">';
 foreach(vatan_feature_labels() as $key=>$label)echo '<tr><th>'.esc_html($label).'</th><td><label><input type="checkbox" name="vatan_features['.esc_attr($key).']" value="1" '.checked(vatan_feature($key),true,false).'> فعال</label></td></tr>';
 echo '</table><h2>رویدادها و حریم خصوصی</h2><input type="hidden" name="vatan_analytics_enabled" value="0"><label><input type="checkbox" name="vatan_analytics_enabled" value="1" '.checked(get_option('vatan_analytics_enabled',false),true,false).'> شمارش محلی رویدادها پس از رضایت اختیاری بازدیدکننده</label><p>هیچ سرویس خارجی یا داده فرم به آمار متصل نیست. داده‌ها فقط تعداد روزانه رویدادها هستند و پس از ۳۰ روز پاک می‌شوند. این انتخاب در ارسال فرم اجباری نیست.</p>';
 submit_button('ذخیره تنظیمات قالب');echo '</form><h2>ویرایش سریع هر صفحه</h2><table class="widefat striped"><tbody>';
 foreach(get_posts(['post_type'=>'page','post_status'=>'publish','numberposts'=>-1,'orderby'=>'menu_order title','order'=>'ASC']) as $p)echo '<tr><td>'.esc_html($p->post_title).'</td><td><a href="'.esc_url(get_edit_post_link($p->ID,'raw').'#armaghan-sections').'">ویرایش سکشن‌ها</a></td><td><a href="'.esc_url(get_permalink($p)).'">دیدن صفحه</a></td></tr>';
 echo '</tbody></table><h2>درخواست‌ها و خروجی</h2><p><a class="button" href="'.esc_url(admin_url('edit.php?post_type=vatan_lead')).'">پیگیری درخواست‌ها</a> <a class="button" href="'.esc_url(wp_nonce_url(admin_url('admin-post.php?action=vatan_export'),'vatan_export')).'">CSV درخواست‌ها</a></p><p>درخواست‌ها در وردپرس خصوصی ذخیره می‌شوند. ایمیل اعلان به تنظیم ارسال ایمیل هاست وابسته است. هیچ حذف خودکار درخواست مشتری اجرا نمی‌شود.</p></div>';
}
