<?php
get_header();the_post();$post=get_post();$kind=ag_media_view()?:vatan_media_post_kind($post);$item=ag_mh_item($post,$kind);$s=ag_detail_section($post->ID,'episode');
$item['body']=$s['body']?:$item['body'];if(!empty($s['image']))$item['image']=ag_media_url($s['image']);ag_mh_detail($item,$kind);get_footer();
