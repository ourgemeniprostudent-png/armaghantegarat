<?php
if (!defined('ABSPATH')) exit;
/** Create editable native menus only where an owner has never chosen a menu. */
function vatan_navigation_defaults() {
 $locations = get_theme_mod('nav_menu_locations', []);
 $sets = [
  'primary'=>['منوی اصلی ارمغان', ['products'=>'محصولات','solutions'=>'همکاری تجاری','media'=>'رسانه','blog'=>'مجله تجارت','about'=>'درباره ما','contact'=>'تماس']],
  'footer'=>['دسترسی‌های فوتر ارمغان', ['solutions'=>'همکاری تجاری','media'=>'رسانه','blog'=>'مجله تجارت','faq'=>'پرسش‌های متداول','privacy'=>'حریم خصوصی']],
  'footer_products'=>['محصولات فوتر ارمغان', []]
 ];
 foreach ($sets as $location=>$set) {
  if (array_key_exists($location,$locations) || get_option('vatan_menu_initialized_'.$location)) continue;
  $menu=wp_get_nav_menu_object($set[0]);
  $id=$menu?$menu->term_id:wp_create_nav_menu($set[0]);
  if (is_wp_error($id)) continue;
  if (!wp_get_nav_menu_items($id)) {
   foreach ($set[1] as $slug=>$label) {
    $page=get_page_by_path($slug);
    if ($page) wp_update_nav_menu_item($id,0,['menu-item-title'=>$label,'menu-item-status'=>'publish','menu-item-type'=>'post_type','menu-item-object'=>'page','menu-item-object-id'=>$page->ID]);
   }
   if ($location==='footer_products') foreach (vatan_core_categories() as $slug=>$label) {
    $term=get_term_by('slug',$slug,'vatan_category');
    if ($term) wp_update_nav_menu_item($id,0,['menu-item-title'=>$label,'menu-item-status'=>'publish','menu-item-type'=>'taxonomy','menu-item-object'=>'vatan_category','menu-item-object-id'=>$term->term_id]);
   }
  }
  $locations[$location]=(int)$id;
  add_option('vatan_menu_initialized_'.$location,true,'','no');
 }
 set_theme_mod('nav_menu_locations',$locations);
 vatan_media_navigation_defaults();
}

/** Add the requested editable media children once, without replacing an owner's menu. */
function vatan_media_navigation_defaults(){
 if(get_option('vatan_media_submenu_initialized'))return;
 $locations=get_theme_mod('nav_menu_locations',[]);$menu=$locations['primary']??0;if(!$menu)return;
 $items=wp_get_nav_menu_items($menu);$page=get_page_by_path('media');$parent=null;
 foreach((array)$items as $item)if(($item->object==='page'&&$page&&(int)$item->object_id===$page->ID)||untrailingslashit($item->url)===untrailingslashit(vatan_media_url())){$parent=$item;break;}
 if(!$parent)return;
 foreach(['video'=>'ویدیوها','audio'=>'پادکست‌ها'] as $kind=>$label){
  $url=vatan_media_url($kind);$exists=false;
  foreach((array)$items as $item)if(untrailingslashit($item->url)===untrailingslashit($url)){$exists=true;break;}
  if(!$exists){$id=wp_update_nav_menu_item($menu,0,['menu-item-title'=>$label,'menu-item-url'=>$url,'menu-item-type'=>'custom','menu-item-status'=>'publish','menu-item-parent-id'=>$parent->ID]);if(is_wp_error($id))return;}
 }
 add_option('vatan_media_submenu_initialized',true,'','no');
}
add_action('admin_init','vatan_media_navigation_defaults');
