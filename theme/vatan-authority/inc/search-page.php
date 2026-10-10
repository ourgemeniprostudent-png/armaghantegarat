<?php
/** A compact, editable search workspace rather than a narrative page opening. */
if (!defined('ABSPATH')) exit;
function ag_search_arrow() {
    echo '<svg class="ag-arrow-icon" viewBox="0 0 24 24" fill="none" width="1em" height="1em" aria-hidden="true"><path d="M20 20 4 4M4 20V4H20" stroke="currentColor" stroke-width="1.3"/></svg>';
}
function ag_search_section($s){
    $term=isset($_GET['q'])&&!is_array($_GET['q'])?mb_substr(sanitize_text_field(wp_unslash($_GET['q'])),0,150):'';
    $scope=ag_search_scope(isset($_GET['scope'])&&!is_array($_GET['scope'])?sanitize_key($_GET['scope']):'all');
    $active=ag_search_normalize($term)!=='';
    $results=$active?ag_public_search($term,$scope):[];$total=count($results);
    $pages=max(1,(int)ceil($total/12));
    $page=isset($_GET['result_page'])&&!is_array($_GET['result_page'])?max(1,min($pages,absint($_GET['result_page']))):1;
    echo '<div class="ag-search-head"><h2 id="ag-search-form-title">'.esc_html($s['title']).'</h2>';ag_body($s['body']);
    echo '<form class="ag-search-form" data-wp-search role="search" aria-labelledby="ag-search-form-title" method="get" action="'.esc_url(vatan_url('search/')).'">';
    echo '<label for="ag-search-input">'.esc_html($s['input_label']).'</label><div class="ag-search-entry"><svg class="ag-search-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="10" cy="10" r="6.5" stroke="currentColor" stroke-width="1.6"/><path d="m15 15 5 5" stroke="currentColor" stroke-width="1.6"/></svg><input id="ag-search-input" name="q" type="search" maxlength="150" value="'.esc_attr($term).'" placeholder="'.esc_attr($s['placeholder']).'" required><button class="button" type="submit">'.esc_html($s['label']).' ';ag_search_arrow();echo '</button></div>';
    echo '<div class="ag-search-tools"><label for="ag-search-scope">'.esc_html($s['scope_label']).'</label><select id="ag-search-scope" name="scope">';
    foreach(['all','products','articles','media','pages'] as $value)echo '<option value="'.esc_attr($value).'" '.selected($value,$scope,false).'>'.esc_html($s['scope_'.$value]).'</option>';
    echo '</select></div></form><div class="ag-search-examples"><span>'.esc_html($s['examples_label']).'</span>';
    foreach(preg_split('/\R/u',$s['examples']) as $example){$example=trim($example);if($example!=='')echo '<a href="'.esc_url(add_query_arg('q',$example,vatan_url('search/'))).'">'.esc_html($example).'</a>';}
    echo '</div></div>';
    echo '<div class="ag-search-output" aria-label="'.esc_attr($s['results_label']).'"><p class="ag-search-count" role="status">'.($active?esc_html(vatan_digits_fa($total).' نتیجه برای «'.$term.'»'):'').'</p><div class="ag-search-results">';
    foreach(array_slice($results,($page-1)*12,12) as $result){
        echo '<article class="ag-search-result"><span class="eyebrow">'.esc_html($result['kind']).'</span><h3><a href="'.esc_url($result['url']).'">'.esc_html($result['title']).' <span class="ag-symbol" aria-hidden="true">';ag_search_arrow();echo '</span></a></h3><p>'.esc_html($result['excerpt']).'</p></article>';
    }
    echo '</div><div class="ag-search-empty" data-search-empty data-initial-title="'.esc_attr($s['initial_title']).'" data-initial-body="'.esc_attr($s['initial_body']).'" data-empty-title="'.esc_attr($s['empty_title']).'" data-empty-body="'.esc_attr($s['empty_body']).'" '.($total?'hidden':'').'><h3>'.esc_html($s[$active?'empty_title':'initial_title']).'</h3><p>'.esc_html($s[$active?'empty_body':'initial_body']).'</p></div>';
    echo '<nav class="ag-pagination" aria-label="'.esc_attr($s['pagination_label']).'">';
    if($pages>1)echo paginate_links(['base'=>str_replace('999999999','%#%',add_query_arg('result_page',999999999,vatan_url('search/'))),'format'=>'','total'=>$pages,'current'=>$page,'add_args'=>['q'=>$term,'scope'=>$scope],'prev_text'=>'قبلی','next_text'=>'بعدی']);
    echo '</nav></div>';ag_visual($s);
}
function ag_render_search_page(){
    $hero=ag_section('hero','search');$s=ag_section('results','search');$paths=ag_section('suggestions','search');$next=ag_section('next','search');
    echo '<div class="ag-interior ag-search-route" data-ag-context="search"><section class="ag-search-opening" id="hero"><div class="wrap"><div class="ag-page-path">';ag_breadcrumb();echo '</div><div class="ag-search-intro">';echo '<h1>'.esc_html($hero['line1']).' <span class="gold">'.esc_html($hero['line2']).'</span></h1>';ag_body($hero['body']);echo '</div><div id="results">';ag_search_section($s);echo '</div>';ag_visual($hero);echo '</div></section>';
    echo '<section class="ag-search-paths" id="suggestions"><div class="wrap"><div class="ag-search-path-heading">';ag_heading($paths);ag_body($paths['body']);echo '</div><div class="ag-search-path-grid">';
    for($i=1;isset($paths['item_'.$i.'_title']);$i++){
        echo '<article><h3><a href="'.esc_url(ag_link($paths['item_'.$i.'_link'])).'">'.esc_html($paths['item_'.$i.'_title']).' ';ag_search_arrow();echo '</a></h3><p>'.esc_html($paths['item_'.$i.'_body']).'</p>';ag_visual(['image'=>$paths['item_'.$i.'_image']??'','video'=>$paths['item_'.$i.'_video']??'','alt'=>$paths['item_'.$i.'_title']]);echo '</article>';
    }
    echo '</div>';ag_visual($paths);echo '</div></section><section class="ag-search-next" id="next"><div class="wrap"><div>';ag_heading($next);ag_body($next['body']);echo '</div>';ag_action($next,'button');echo '</div>';ag_visual($next);echo '</section>';
    $content=get_post_field('post_content',get_queried_object_id());if(trim(wp_strip_all_tags($content))!=='')echo '<section class="ag-search-editorial"><div class="wrap ag-editorial">'.apply_filters('the_content',$content).'</div></section>';
    echo '</div>';
}
