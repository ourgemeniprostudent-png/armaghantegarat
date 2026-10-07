<?php
if (!defined('ABSPATH')) exit;
function ag_search_normalize($text) {
    return trim(preg_replace('/\s+/u',' ',mb_strtolower(strtr(wp_strip_all_tags($text),['ي'=>'ی','ك'=>'ک','‌'=>' ']))));
}
/** Index only text that these public templates actually render, never private metadata. */
function ag_search_sections_text($context,$id) {
    $text=[];
    foreach(armaghan_section_data($context,$id) as $section)foreach($section['values'] as $key=>$value)
        if(preg_match('/^(?:title|line1|line2|body|lead|caption|success_body|empty_body|item_\d+_(?:title|body|question|answer))$/D',$key)&&$value!=='')$text[]=$value;
    return $text;
}
function ag_public_search($query) {
    $tokens=preg_split('/\s+/u',ag_search_normalize($query),-1,PREG_SPLIT_NO_EMPTY);if(!$tokens)return [];
    $results=[];
    $match=function($title,$parts,$url,$kind)use($tokens,&$results){
        $parts=array_values($parts);$normalized_title=ag_search_normalize($title);$haystack=ag_search_normalize($title.' '.implode(' ',$parts));$score=0;
        foreach($tokens as $token){if(!str_contains($haystack,$token))return;$score+=str_contains($normalized_title,$token)?10:1;}
        $snippet='';foreach($parts as $part)if(str_contains(ag_search_normalize($part),$tokens[0])){$snippet=$part;break;}
        if(!$snippet)$snippet=$parts[0]??'';
        $results[]=['title'=>$title,'url'=>$url,'kind'=>$kind,'excerpt'=>wp_trim_words(wp_strip_all_tags($snippet),35),'score'=>$score];
    };
    $skip=array_filter([get_page_by_path('search')->ID??0,get_page_by_path('thank-you')->ID??0]);
    $posts=get_posts(['post_type'=>['page','post','vatan_product','vatan_episode'],'post_status'=>'publish','numberposts'=>-1,'post__not_in'=>$skip]);
    foreach($posts as $post){
        $parts=[get_the_excerpt($post),wp_strip_all_tags(strip_shortcodes($post->post_content))];
        $parts=array_merge($parts,ag_search_sections_text(armaghan_context($post),$post->ID));
        if($post->post_type==='vatan_product')$parts[]=get_post_meta($post->ID,'_vatan_specs',true);
        if($post->post_type==='vatan_episode')$parts[]=get_post_meta($post->ID,'_vatan_transcript',true);
        $match($post->post_title,array_filter($parts),get_permalink($post),['page'=>'صفحه','post'=>'یادداشت تجارت','vatan_product'=>'محصول منتشرشده','vatan_episode'=>'رسانه منتشرشده'][$post->post_type]);
    }
    $terms=get_terms(['taxonomy'=>'vatan_category','hide_empty'=>false]);
    if(!is_wp_error($terms))foreach($terms as $term)$match($term->name,array_merge([$term->description],ag_search_sections_text(armaghan_context($term),$term->term_id)),get_term_link($term),'گروه محصول');
    usort($results,static fn($a,$b)=>$b['score']<=>$a['score']);return $results;
}
