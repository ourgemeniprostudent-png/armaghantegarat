<?php
/** One route hierarchy for visible navigation and structured data. */
if (!defined('ABSPATH')) exit;
function ag_breadcrumb_items($title='') {
    if (is_front_page() || is_404()) return [];
    if((is_page('media')||is_singular('vatan_episode'))&&(ag_media_view()||ag_media_sample_key()||is_singular('vatan_episode'))){
        $kind=ag_media_view()?:(is_singular('vatan_episode')?vatan_media_post_kind(get_queried_object()):'audio');$items=[['name'=>'خانه','url'=>home_url('/')],['name'=>ag_ms_text('all_media_label'),'url'=>vatan_media_url()]];
        $items[]=['name'=>ag_ms_text($kind==='video'?'videos_nav':'podcasts_nav'),'url'=>vatan_media_url($kind)];
        if($key=ag_media_sample_key())$items[]=['name'=>ag_media_sample_catalog()[$key]['title']??ag_ms_text('sample_label'),'url'=>ag_current_url()];
        elseif(is_singular('vatan_episode'))$items[]=['name'=>get_the_title(get_queried_object_id()),'url'=>ag_current_url()];
        return $items;
    }

    $identity=ag_section('identity','global');
    $items=[['name'=>$identity['breadcrumb_home']??'خانه','url'=>home_url('/')]];
    if (is_singular('post')) $items[]=['name'=>get_the_title((int)get_option('page_for_posts'))?:'مجله تجارت','url'=>vatan_url('blog/')];
    if (is_singular('vatan_episode')) {$page=get_page_by_path('media');$items[]=['name'=>$page?get_the_title($page):'رسانه','url'=>vatan_url('media/')];}
    if (is_tax('vatan_category') || is_singular('vatan_product')) {
        $page=get_page_by_path('products');
        $items[]=['name'=>$page?get_the_title($page):'محصولات','url'=>vatan_url('products/')];
        $term=is_tax('vatan_category')?get_queried_object():null;
        if (!$term) {
            $terms=get_the_terms(get_queried_object_id(),'vatan_category');
            if ($terms && !is_wp_error($terms)) $term=$terms[0];
        }
        if ($term) {
            foreach (array_reverse(get_ancestors($term->term_id,'vatan_category','taxonomy')) as $parent_id) {
                $parent=get_term($parent_id,'vatan_category');
                if (!$parent || is_wp_error($parent)) continue;
                $url=get_term_link($parent);
                if (!is_wp_error($url)) $items[]=['name'=>$parent->name,'url'=>$url];
            }
            if (is_singular('vatan_product')) {
                $url=get_term_link($term);
                if (!is_wp_error($url)) $items[]=['name'=>$term->name,'url'=>$url];
            }
        }
    } elseif (is_page()) {
        foreach (array_reverse(get_post_ancestors(get_queried_object_id())) as $parent_id) {
            if ((int)$parent_id!==(int)get_option('page_on_front')) $items[]=['name'=>get_the_title($parent_id),'url'=>get_permalink($parent_id)];
        }
    }
    if ($title==='') $title=is_tax()?get_queried_object()->name:(is_home()?get_the_title((int)get_option('page_for_posts')):get_the_title(get_queried_object_id()));
    $items[]=['name'=>$title,'url'=>ag_current_url()];
    return $items;
}
function ag_breadcrumb($title='') {
    if (!vatan_feature('breadcrumbs') || !($items=ag_breadcrumb_items($title))) return;
    $identity=ag_section('identity','global');
    echo '<nav class="breadcrumb" aria-label="'.esc_attr($identity['breadcrumb_label']??'مسیر صفحه').'"><ol>';
    $last=count($items)-1;
    foreach ($items as $index=>$item) {
        echo '<li>';
        if ($index>0) echo '<span class="ag-path-separator" aria-hidden="true"></span>';
        if ($index===$last) echo '<span aria-current="page">'.esc_html($item['name']).'</span>';
        else echo '<a href="'.esc_url($item['url']).'">'.esc_html($item['name']).'</a>';
        echo '</li>';
    }
    echo '</ol></nav>';
}
