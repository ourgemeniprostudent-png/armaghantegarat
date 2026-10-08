<?php
/** Add new editorial material without overwriting administrator edits or existing posts. */
if(!defined('ABSPATH'))exit;
vatan_editorial_setup();
vatan_navigation_defaults();
add_option('vatan_analytics_enabled',true,'','no');
$path=dirname(__DIR__).'/content/editorial/guides-fa.json';
if(!is_file($path))$path=WP_CONTENT_DIR.'/armaghan-public/editorial/guides-fa.json';
$guides=json_decode(file_get_contents($path),true,512,JSON_THROW_ON_ERROR);$added=0;
foreach($guides as $guide){
 $existing=get_posts(['post_type'=>'post','name'=>$guide['slug'],'post_status'=>'any','numberposts'=>1]);
 if($existing)$id=$existing[0]->ID;
 else{$id=wp_insert_post(wp_slash(['post_type'=>'post','post_status'=>'publish','post_name'=>$guide['slug'],'post_title'=>$guide['title'],'post_excerpt'=>$guide['excerpt'],'post_content'=>str_replace('__ARMAGHAN_SITE_URL__',untrailingslashit(home_url()),$guide['body']),'post_date'=>'2026-10-08 07:00:00']),true);if(is_wp_error($id))throw new RuntimeException($id->get_error_message());$added++;}
 if(!wp_get_post_terms($id,'vatan_topic',['fields'=>'ids']))wp_set_object_terms($id,[$guide['topic']],'vatan_topic');
 if(!metadata_exists('post',$id,'_vatan_public_author'))update_post_meta($id,'_vatan_public_author',$guide['author']);
}
foreach(['coffee-inquiry-guide'=>'coffee','business-inquiry-checklist'=>'supply','follow-up-your-inquiry'=>'supply'] as $slug=>$topic){$p=get_page_by_path($slug,OBJECT,'post');if($p&&!wp_get_post_terms($p->ID,'vatan_topic',['fields'=>'ids']))wp_set_object_terms($p->ID,[$topic],'vatan_topic');}
$p=get_page_by_path('coffee-inquiry-guide',OBJECT,'post');if($p&&!metadata_exists('post',$p->ID,'_vatan_featured'))update_post_meta($p->ID,'_vatan_featured',true);
if(defined('WP_CLI')&&WP_CLI)WP_CLI::success('Editorial upgrade: '.$added.' new guides; existing content and edits preserved.');
