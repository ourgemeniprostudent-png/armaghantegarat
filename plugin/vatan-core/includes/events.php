<?php
/** Opt-in, first-party aggregate events. No form values, IPs, URLs or visitor IDs retained. */
if(!defined('ABSPATH'))exit;
function vatan_event_names(){return ['whatsapp_click','lead_intent','form_start','form_submit','lead_success','phone_click','map_click','podcast_play','video_play','article_read_75','product_view'];}
add_action('rest_api_init',function(){
    register_rest_route('vatan/v1','/events',['methods'=>'POST','permission_callback'=>'__return_true','callback'=>function(WP_REST_Request $request){
        if(!get_option('vatan_analytics_enabled',false))return new WP_REST_Response(['accepted'=>0],200);
        $body=$request->get_json_params();if(!is_array($body)||($body['consent']??false)!==true||!in_array($body['event']??'',vatan_event_names(),true))return new WP_Error('invalid_event','رویداد معتبر نیست.',['status'=>400]);
        $origin=$request->get_header('origin');if($origin&&wp_parse_url($origin,PHP_URL_HOST)!==wp_parse_url(home_url(),PHP_URL_HOST))return new WP_Error('invalid_origin','مبدا معتبر نیست.',['status'=>403]);
        $rate='vatan_evt_'.hash_hmac('sha256',$_SERVER['REMOTE_ADDR']??'',wp_salt('nonce'));$count=(int)get_transient($rate);if($count>=120)return new WP_Error('event_limit','کمی بعد تلاش کنید.',['status'=>429]);set_transient($rate,$count+1,HOUR_IN_SECONDS);
        global $wpdb;$key='vatan_event_'.gmdate('Ymd').'_'.$body['event'];
        // Atomic counter avoids a read/modify/write race and keeps the collector bounded.
        add_option($key,0,'',false);$wpdb->query($wpdb->prepare("UPDATE $wpdb->options SET option_value = CAST(option_value AS UNSIGNED) + 1 WHERE option_name = %s",$key));wp_cache_delete($key,'options');
        return new WP_REST_Response(['accepted'=>1],200);
    }]);
});
add_action('admin_init',function(){
    register_setting('vatan_settings','vatan_analytics_enabled',['type'=>'boolean','sanitize_callback'=>fn($v)=>(bool)$v]);
    register_setting('vatan_settings','vatan_public_email',['sanitize_callback'=>'sanitize_email']);
    register_setting('vatan_settings','vatan_hours',['sanitize_callback'=>'sanitize_text_field']);
});
add_action('admin_menu',function(){add_submenu_page('tools.php','آمار رویدادها','آمار رویدادهای سایت','manage_options','vatan-events',function(){
    if(!current_user_can('manage_options'))return;echo '<div class="wrap"><h1>رویدادهای سایت در ۳۰ روز گذشته</h1><p>فقط تعداد رویدادهای کاربرانی که رضایت داده‌اند ذخیره می‌شود؛ بدون اطلاعات فرم، شناسه بازدیدکننده یا نشانی صفحات.</p><table class="widefat striped"><thead><tr><th>رویداد</th><th>تعداد</th></tr></thead><tbody>';
    foreach(vatan_event_names() as $event){$total=0;for($day=0;$day<30;$day++)$total+=(int)get_option('vatan_event_'.gmdate('Ymd',time()-$day*DAY_IN_SECONDS).'_'.$event,0);echo '<tr><td>'.esc_html($event).'</td><td>'.esc_html(number_format_i18n($total)).'</td></tr>';}
    echo '</tbody></table></div>';
});});
add_action('init',function(){if(get_option('vatan_analytics_enabled',false)&&!wp_next_scheduled('vatan_cleanup_events'))wp_schedule_event(time()+DAY_IN_SECONDS,'daily','vatan_cleanup_events');});
add_action('vatan_cleanup_events',function(){
    global $wpdb;$names=$wpdb->get_col($wpdb->prepare("SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s",$wpdb->esc_like('vatan_event_').'%'));$cutoff=gmdate('Ymd',time()-30*DAY_IN_SECONDS);
    foreach($names as $name)if(preg_match('/^vatan_event_(\d{8})_/', $name,$m)&&$m[1]<$cutoff)delete_option($name);
});
