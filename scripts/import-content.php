<?php
if(!defined('ABSPATH'))exit;
$seed=json_decode(file_get_contents(dirname(__DIR__).'/content/public-site.json'),true,512,JSON_THROW_ON_ERROR);
vatan_editorial_setup();$map=[];$url=untrailingslashit(home_url());
foreach($seed['posts'] as $p){
 $old=$p['ID'];unset($p['ID']);$p['post_parent']=0;$p['post_status']='publish';$p['post_author']=1;
 $p['post_content']=str_replace('__ARMAGHAN_SITE_URL__',$url,$p['post_content']);
 $terms=$p['terms'];$topics=$p['topics']??[];$public_meta=$p['meta']??[];unset($p['terms'],$p['topics'],$p['meta']);
 $found=get_posts(['post_type'=>$p['post_type'],'post_status'=>'any','name'=>$p['post_name'],'numberposts'=>1]);
 if($found)$p['ID']=$found[0]->ID;
 $id=wp_insert_post(wp_slash($p),true);if(is_wp_error($id))WP_CLI::error($id->get_error_message());$map[$old]=$id;
 if($terms)wp_set_object_terms($id,$terms,'vatan_category');if($topics)wp_set_object_terms($id,$topics,'vatan_topic');foreach($public_meta as $key=>$value)if(in_array($key,['_vatan_public_author','_vatan_featured','_vatan_seo_title','_vatan_seo_description'],true))update_post_meta($id,$key,$value);
}
foreach($seed['posts'] as $p)if($p['post_parent']&&isset($map[$p['post_parent']]))wp_update_post(['ID'=>$map[$p['ID']],'post_parent'=>$map[$p['post_parent']]]);
foreach($seed['site'] as $key=>$value)if($value!==false)update_option($key,$value);
foreach($seed['theme_mods'] as $key=>$value)set_theme_mod($key,$value);
foreach($seed['menus'] as $menu){$id=wp_create_nav_menu($menu['name']);if(is_wp_error($id)){$existing=wp_get_nav_menu_object($menu['name']);$id=$existing?$existing->term_id:0;}if(!$id)continue;foreach($menu['items'] as $item){$args=['menu-item-title'=>$item['title'],'menu-item-position'=>$item['order'],'menu-item-status'=>'publish','menu-item-type'=>$item['type'],'menu-item-object'=>$item['object']];if($item['type']==='post_type')$args['menu-item-object-id']=$map[$item['object_id']]??0;else $args['menu-item-url']=str_replace('__ARMAGHAN_SITE_URL__',$url,$item['url']);wp_update_nav_menu_item($id,0,$args);}$locations=get_theme_mod('nav_menu_locations',[]);$locations[$menu['location']]=$id;set_theme_mod('nav_menu_locations',$locations);}
foreach(['page_on_front'=>'home','page_for_posts'=>'blog'] as $key=>$slug){$page=get_page_by_path($slug);if($page)update_option($key,$page->ID);}update_option('show_on_front','page');update_option('vatan_notify_email','');
foreach(['hello-world','sample-page'] as $slug){$posts=get_posts(['post_type'=>['post','page'],'post_status'=>'publish','name'=>$slug]);foreach($posts as $p)wp_delete_post($p->ID,true);}
vatan_navigation_defaults();flush_rewrite_rules();WP_CLI::success('Imported public pages, articles and display settings. No users or inquiries imported.');
