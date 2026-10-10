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
/** One public allowlist supplies native results and the portable preview index. */
function ag_search_public_records($export=false) {
    $records=[];
    $posts=get_posts(['post_type'=>['page','post','vatan_product','vatan_episode'],'post_status'=>'publish','numberposts'=>-1]);
    foreach($posts as $post){
        if($export&&get_post_meta($post->ID,'_qa_capability',true))throw new RuntimeException('Remove QA fixtures before indexing');
        $parts=[get_the_excerpt($post),wp_strip_all_tags(strip_shortcodes($post->post_content))];
        $parts=array_merge($parts,ag_search_sections_text(armaghan_context($post),$post->ID));
        if($post->post_type==='vatan_product')$parts[]=get_post_meta($post->ID,'_vatan_specs',true);
        if($post->post_type==='vatan_episode')$parts[]=get_post_meta($post->ID,'_vatan_transcript',true);
        $parts=array_values(array_filter($parts));
        $records[]=['id'=>$post->ID,'title'=>$post->post_title,'url'=>get_permalink($post),'parts'=>$parts,'body'=>implode(' ',$parts),'excerpt'=>get_the_excerpt($post),'date'=>get_post_time('U',true,$post),'topics'=>wp_get_post_terms($post->ID,'vatan_topic',['fields'=>'slugs']),
            'group'=>['page'=>'pages','post'=>'articles','vatan_product'=>'products','vatan_episode'=>'media'][$post->post_type],
            'kind'=>['page'=>'صفحه','post'=>'یادداشت تجارت','vatan_product'=>'محصول منتشرشده','vatan_episode'=>'رسانه منتشرشده'][$post->post_type],
            'searchable'=>!in_array($post->post_name,['search','thank-you'],true)];
    }
    $terms=get_terms(['taxonomy'=>'vatan_category','hide_empty'=>false]);
    if(!is_wp_error($terms))foreach($terms as $term){
        $parts=array_values(array_filter(array_merge([$term->description],ag_search_sections_text(armaghan_context($term),$term->term_id))));
        $records[]=['title'=>$term->name,'url'=>get_term_link($term),'parts'=>$parts,'body'=>implode(' ',$parts),'excerpt'=>$term->description,'group'=>'products','kind'=>'گروه محصول','searchable'=>true];
    }
    return $records;
}
function ag_search_scope($scope) {return in_array($scope,['all','products','articles','media','pages'],true)?$scope:'all';}
function ag_public_search($query,$scope='all') {
    $tokens=preg_split('/\s+/u',ag_search_normalize($query),-1,PREG_SPLIT_NO_EMPTY);if(!$tokens)return [];
    $scope=ag_search_scope($scope);$results=[];
    foreach(ag_search_public_records() as $record){
        if(!$record['searchable']||($scope!=='all'&&$record['group']!==$scope))continue;
        $title=ag_search_normalize($record['title']);$haystack=ag_search_normalize($record['title'].' '.$record['body']);$score=0;
        foreach($tokens as $token){if(!str_contains($haystack,$token))continue 2;$score+=str_contains($title,$token)?10:1;}
        $snippet='';foreach($record['parts'] as $part)if(str_contains(ag_search_normalize($part),$tokens[0])){$snippet=$part;break;}
        if(!$snippet)$snippet=$record['parts'][0]??'';
        $results[]=['title'=>$record['title'],'url'=>$record['url'],'kind'=>$record['kind'],'excerpt'=>wp_trim_words(wp_strip_all_tags($snippet),35),'score'=>$score];
    }
    usort($results,static fn($a,$b)=>$b['score']<=>$a['score']);return $results;
}
