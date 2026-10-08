<?php
if (!defined('ABSPATH')) exit;
function ag_sections($context=null) {
    $context=$context ?: armaghan_context(get_queried_object());
    static $cache; if(!isset($cache[$context]))$cache[$context]=armaghan_section_data($context);
    return $cache[$context];
}
function ag_section($id,$context=null) {return ag_sections($context)[$id]['values']??[];}
function ag_text($section,$key,$fallback='') {echo esc_html($section[$key]??$fallback);}
function ag_media_url($value) {
    if(!$value)return '';
    if(str_starts_with($value,'asset:'))return vatan_asset(substr($value,6));
    if(str_starts_with($value,'attachment:'))return wp_get_attachment_url((int)substr($value,11))?:'';
    return esc_url($value);
}
function ag_link($value) {
    if(str_starts_with($value,'#'))return $value;
    if(str_starts_with($value,'/')&&!str_starts_with($value,'//'))return home_url($value);
    return esc_url($value);
}
function ag_body($text) {foreach(preg_split('/\n\s*\n/u',$text) as $paragraph)if(trim($paragraph)!=='')echo '<p>'.nl2br(esc_html($paragraph)).'</p>';}
function ag_video($s,$class='ag-video') {
    $url=ag_media_url($s['video']??'');if(!$url)return;
    echo '<video class="'.esc_attr($class).'" controls playsinline preload="none" poster="'.esc_url(ag_media_url($s['image']??'')).'" aria-label="'.esc_attr($s['alt']??$s['title']??'ویدیو').'" src="'.esc_url($url).'"></video>';
}
function ag_visual($s,$hero=false) {
    $image=ag_media_url($s['image']??'');$video=ag_media_url($s['video']??'');if(!$image&&!$video)return;
    $contain=str_contains($s['image']??'','products/');
    echo '<figure class="ag-visual '.($contain?'ag-visual-product':'').'" data-ag-reveal>';
    if($video)ag_video($s);
    elseif($image){echo '<button type="button" class="ag-image-expand" data-ag-zoom aria-label="بزرگ‌نمایی تصویر"><img '.ag_image_attrs($image).' alt="'.esc_attr($s['alt']??'').'" '.($hero?'fetchpriority="high"':'loading="lazy"').' decoding="async"><span class="ag-expand-mark" aria-hidden="true">⤢</span></button>';}
    if(!empty($s['caption']))echo '<figcaption>'.esc_html($s['caption']).'</figcaption>';
    echo '</figure>';
}
function ag_heading($s,$tag='h2') {
    if(!empty($s['eyebrow']))echo '<span class="eyebrow">'.esc_html($s['eyebrow']).'</span>';
    echo '<'.$tag.'>';
    if(isset($s['line1']))echo esc_html($s['line1']).'<span class="gold">'.esc_html($s['line2']??'').'</span>';
    else echo esc_html($s['title']??'');
    echo '</'.$tag.'>';
}
function ag_action($s,$class='home-route-link') {if(!empty($s['link'])&&!empty($s['label']))echo '<a class="'.esc_attr($class).'" href="'.esc_url(ag_link($s['link'])).'">'.esc_html($s['label']).' <span aria-hidden="true">↗</span></a>';}
function ag_inner_nav($context) {
    $sections=ag_sections($context);echo '<nav class="ag-section-nav wrap" aria-label="بخش‌های صفحه"><a href="'.esc_url(home_url('/')).'">خانه</a><span aria-hidden="true">/</span>';
    foreach($sections as $id=>$section)if(!in_array($section['layout'],['hero','home-product','home-flow','home-flow-item'],true))echo '<a href="#'.esc_attr($id==='gallery'?($section['layout']==='product-gallery'?'import-categories':'business-cooperation'):$id).'">'.esc_html($section['label']).'</a>';
    echo '</nav>';
}
function ag_render_page($context) {
    echo '<div class="ag-interior ag-art-page ag-context-'.esc_attr(str_replace(':','-',$context)).'" data-ag-context="'.esc_attr($context).'">';
    foreach(ag_sections($context) as $id=>$section){
        $s=$section['values'];$layout=$section['layout'];if(str_starts_with($layout,'home-')||$layout==='article')continue;
        if($layout==='hero'){
            echo '<section class="ag-hero" id="'.esc_attr($id).'"><div class="wrap ag-hero-grid"><div class="ag-hero-copy" data-ag-reveal><div class="breadcrumb"><a href="'.esc_url(home_url('/')).'">خانه</a> / '.esc_html(is_tax('vatan_category')?get_queried_object()->name:(is_home()?get_the_title((int)get_option('page_for_posts')):get_the_title(get_queried_object_id()))).'</div>';
            ag_heading($s,'h1');ag_body($s['body']??'');ag_action($s,'button trade-button');echo '</div>';ag_art_hero($s,$context);echo '</div><div class="ag-hero-foot wrap"><span>'.esc_html($s['foot_label']??$s['eyebrow']).'</span><a href="#'.esc_attr(array_keys(ag_sections($context))[1]??$id).'">'.esc_html($s['scroll_label']??'ادامه روایت').' <span class="ag-symbol" aria-hidden="true">↓</span></a></div><div class="ag-hero-line" aria-hidden="true"></div></section>';ag_inner_nav($context);continue;
        }
        if($layout==='product-gallery'||$layout==='flow-gallery'){
            echo '<div class="wrap ag-gallery-intro" id="'.esc_attr($id).'" data-ag-reveal>';ag_heading($s);ag_body($s['body']??'');echo '</div>';
            get_template_part('template-parts/'.($layout==='product-gallery'?'product-revolver':'cooperation-gallery'),null,['context'=>$context]);continue;
        }
        echo '<section class="ag-section ag-layout-'.esc_attr($layout).'" id="'.esc_attr($id).'"><div class="wrap">';
        if(in_array($layout,['manifesto','route','dossier','fieldnotes'],true))ag_art_section($s,$layout);
        elseif(in_array($layout,['split','legal'],true)){
            echo '<div class="ag-split"><div class="ag-copy" data-ag-reveal>';ag_heading($s);
            if(!empty($s['lead']))echo '<p class="ag-lead">'.esc_html($s['lead']).'</p>';ag_body($s['body']??'');ag_action($s);echo '</div>';ag_visual($s);echo '</div>';
        }elseif(in_array($layout,['cards','steps'],true)){
            echo '<div class="ag-section-head" data-ag-reveal><div>';ag_heading($s);echo '</div><div>';ag_body($s['body']??'');echo '</div></div>';
            $items=[];foreach($s as $key=>$value)if(preg_match('/^item_(\d+)_title$/',$key,$m))$items[$m[1]]=['title'=>$value,'body'=>$s['item_'.$m[1].'_body']??''];
            echo '<div class="ag-cards ag-cards-'.count($items).'">';foreach($items as $index=>$item){echo '<article class="ag-card" data-ag-reveal><span class="ag-card-number" aria-hidden="true">'.esc_html(vatan_digits_fa(str_pad($index,2,'0',STR_PAD_LEFT))).'</span>';ag_visual(['image'=>$s['item_'.$index.'_image']??'','video'=>$s['item_'.$index.'_video']??'','alt'=>$item['title']]);echo '<h3>'.esc_html($item['title']).'</h3><p>'.esc_html($item['body']).'</p></article>'; }echo '</div>';ag_visual($s);
        }elseif($layout==='faq'){
            echo '<div class="ag-faq-grid"><div class="ag-copy" data-ag-reveal>';ag_heading($s);ag_body($s['body']??'');if($context==='faq')echo '<label class="ag-faq-filter">'.esc_html($s['filter_label']).'<input type="search" data-faq-filter placeholder="'.esc_attr($s['filter_placeholder']).'" aria-controls="ag-faq-list"></label><p data-faq-count aria-live="polite"></p>';ag_visual($s);echo '</div><div class="ag-faq-list" id="ag-faq-list">';
            foreach($s as $key=>$value)if(preg_match('/^item_(\d+)_question$/',$key,$m))echo '<details class="ag-faq" data-ag-reveal><summary>'.esc_html($value).'<span aria-hidden="true">+</span></summary><div>'.wpautop(esc_html($s['item_'.$m[1].'_answer']??'')).'</div></details>';
            echo '<p class="ag-filter-empty" hidden>'.esc_html($s['no_results']??'پرسشی پیدا نشد.').'</p></div></div>';
        }elseif($layout==='closing'){
            echo '<div class="ag-closing" data-ag-reveal><div>';ag_heading($s);ag_body($s['body']??'');echo '</div><div>';ag_action($s,'button trade-button');echo '</div></div>';ag_visual($s);
        }elseif($layout==='form'){
            echo '<div class="ag-form-layout"><div class="ag-form-card"><span class="eyebrow">فرم درخواست</span><h2>'.esc_html($s['title']).'</h2><p>'.esc_html($s['body']).'</p>'.vatan_inquiry_form().'</div><aside class="ag-form-aside"><span class="ag-side-mark" aria-hidden="true">↗</span><h3>'.esc_html($s['sidebar_title']).'</h3>';ag_body($s['sidebar_body']);echo '<div class="ag-form-live"><small>'.esc_html($s['summary_label']??'شرح کوتاه نیاز شما').'</small><p data-ag-form-summary data-empty="'.esc_attr($s['summary_empty']??'محصول را انتخاب کنید').'"></p></div><a class="home-route-link" href="'.esc_url(vatan_url('privacy/')).'">'.esc_html($s['privacy_label']).' <span class="ag-symbol" aria-hidden="true">↗</span></a><div class="ag-aside-phone"><span>گفتگو با دفتر</span><a dir="ltr" href="tel:'.esc_attr(preg_replace('/[^0-9+]/','',vatan_option('phone','02191028166'))).'">'.esc_html(vatan_option('phone','02191028166')).'</a></div></aside></div>';
        }elseif($layout==='contact'){
            echo '<div class="ag-contact-grid"><div class="ag-copy" data-ag-reveal>';ag_heading($s);ag_body($s['body']);echo '<div class="ag-phone"><span>'.esc_html($s['phone_label']).'</span><a dir="ltr" href="tel:'.esc_attr(preg_replace('/[^0-9+]/','',vatan_option('phone','02191028166'))).'">'.esc_html(vatan_option('phone','02191028166')).' <span class="ag-symbol" aria-hidden="true">↗</span></a><p>'.esc_html($s['phone_note']).'</p></div><div class="ag-address"><span>'.esc_html($s['address_label']).'</span><p>'.esc_html(vatan_option('address','تهران، سهروردی شمالی، کوچه زمانی، پلاک ۱۱، ساختمان ایلیا، طبقه ۳، واحد ۹')).'</p></div>';
            ag_whatsapp(get_the_title(),get_permalink());$email=vatan_option('public_email');if(is_email($email))echo '<p><a href="'.esc_url('mailto:'.$email).'">'.esc_html($email).'</a></p>';if(vatan_option('hours'))echo '<p>'.esc_html(vatan_option('hours')).'</p>';echo '</div>';ag_office_map($s);echo '</div>';ag_visual($s);

        }elseif($layout==='contact-short-form'){
            if(vatan_feature('contact_form')){echo '<div class="ag-contact-short ag-form-card"><h2>'.esc_html($s['title']).'</h2><p>'.esc_html($s['body']).'</p>'.vatan_contact_form().'</div>';ag_visual($s);}
        }elseif($layout==='receipt'){
            $reference=isset($_GET['reference'])&&!is_array($_GET['reference'])?sanitize_text_field(wp_unslash($_GET['reference'])):'';
            $valid=preg_match('/^VAT-[A-Z0-9]{10}$/D',$reference)&&get_posts(['post_type'=>'vatan_lead','post_status'=>'private','numberposts'=>1,'meta_key'=>'_vatan_reference','meta_value'=>$reference,'fields'=>'ids']);
            echo '<div class="ag-receipt" '.($valid?'data-lead-success ':'').'data-ag-reveal><span class="ag-receipt-symbol" aria-hidden="true">'.($valid?'✓':'↗').'</span><h2>'.esc_html($s[$valid?'success_title':'empty_title']).'</h2><p>'.esc_html($s[$valid?'success_body':'empty_body']).'</p>';
            if($valid)echo '<code dir="ltr" data-reference>'.esc_html($reference).'</code><button type="button" class="button" data-copy-reference>'.esc_html($s['copy_label']).'</button><p data-copy-status role="status"></p>';
            else ag_action($s,'button');echo '</div>';ag_visual($s);
        }elseif($layout==='search')ag_search_section($s);
        elseif(in_array($layout,['library','catalog','articles'],true))ag_collection($s,$layout,$context);
        echo '</div></section>';
    }
    if(is_page()&&!is_home()){$content=get_post_field('post_content',get_queried_object_id());if(trim(wp_strip_all_tags($content))!==''&&!str_contains($content,'[vatan_inquiry'))echo '<section class="ag-section"><div class="wrap ag-editorial">'.apply_filters('the_content',$content).'</div></section>';}
    echo '</div>';
}
function vatan_digits_fa($v){return strtr((string)$v,array_combine(range(0,9),['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹']));}
function ag_collection($s,$layout,$context){
    echo '<div class="ag-section-head" data-ag-reveal><div>';ag_heading($s);echo '</div><div>';ag_body($s['body']??'');echo '</div></div>';
    $s['_id']=$layout==='catalog'?'catalog':($layout==='articles'?'articles':'library');
    if($layout==='library')ag_featured_media($s);
    ag_discovery($layout,$context,$s);ag_visual($s);
}
function ag_journal_cards($q){
    $tones=['navy','ivory','black'];$index=0;echo '<div class="home-journal-shelf ag-journal-shelf">';
    while($q->have_posts()){$q->the_post();$featured=get_post_meta(get_the_ID(),'_vatan_featured',true);$a=ag_section('article','post:'.get_post_field('post_name',get_the_ID()));$image=has_post_thumbnail()?get_the_post_thumbnail_url(null,'large'):ag_media_url($a['image']??'asset:products/coffee.webp');$n=vatan_digits_fa(str_pad($index+1,2,'0',STR_PAD_LEFT));
        echo '<article data-public-card data-card-id="'.esc_attr(get_the_ID()).'" data-card-title="'.esc_attr(get_the_title()).'" data-card-topics="'.esc_attr(implode(',',wp_get_post_terms(get_the_ID(),'vatan_topic',['fields'=>'slugs']))).'" class="home-issue home-issue-'.$tones[$index%3].'" data-ag-reveal><a class="home-issue-link" href="'.esc_url(get_permalink()).'"><div class="home-issue-top"><span>'.esc_html($featured?'یادداشت منتخب':($a['eyebrow']??'یادداشت تجارت')).'</span><span>'.esc_html($n).'</span></div><div class="home-issue-art" aria-hidden="true"><span class="home-issue-number">'.esc_html($n).'</span><img '.ag_image_attrs($image,'210px',480).' alt="" loading="lazy"></div><div class="home-issue-copy"><h3>'.esc_html(get_the_title()).'</h3><p>'.esc_html(wp_trim_words(get_the_excerpt(),30)).'</p><span class="home-issue-read">خواندن یادداشت <span class="ag-symbol" aria-hidden="true">↗</span></span></div></a></article>';$index++;
    }echo '</div>';
}
function ag_search_section($s){
    $term=isset($_GET['q'])&&!is_array($_GET['q'])?mb_substr(sanitize_text_field(wp_unslash($_GET['q'])),0,150):'';
    echo '<div class="ag-search-head" data-ag-reveal>';ag_heading($s);ag_body($s['body']);echo '<form class="ag-search-form" data-wp-search method="get" action="'.esc_url(vatan_url('search/')).'"><label for="ag-search-input">'.esc_html($s['input_label']).'</label><div><input id="ag-search-input" name="q" type="search" maxlength="150" value="'.esc_attr($term).'" required><button class="button" type="submit">'.esc_html($s['label']).' <span class="ag-symbol" aria-hidden="true">↗</span></button></div></form></div>';
    if($term!==''){
        $results=ag_public_search($term);$total=count($results);$page=isset($_GET['result_page'])&&!is_array($_GET['result_page'])?max(1,absint($_GET['result_page'])):1;$visible=array_slice($results,($page-1)*12,12);
        echo '<p class="ag-search-count">'.esc_html(vatan_digits_fa($total)).' نتیجه برای «'.esc_html($term).'»</p>';
        if($visible){echo '<div class="ag-search-results">';foreach($visible as $result)echo '<article class="ag-search-result" data-ag-reveal><span class="eyebrow">'.esc_html($result['kind']).'</span><h3><a href="'.esc_url($result['url']).'">'.esc_html($result['title']).' <span class="ag-symbol" aria-hidden="true">↗</span></a></h3><p>'.esc_html($result['excerpt']).'</p></article>';echo '</div><nav class="ag-pagination" aria-label="نتایج بیشتر">'.paginate_links(['base'=>str_replace('999999999','%#%',add_query_arg('result_page',999999999,vatan_url('search/'))),'format'=>'','total'=>ceil($total/12),'current'=>$page,'add_args'=>['q'=>$term],'prev_text'=>'قبلی','next_text'=>'بعدی']).'</nav>';}
        else echo '<div class="ag-empty"><h3>'.esc_html($s['empty_title']).'</h3><p>'.esc_html($s['empty_body']).'</p></div>';
    }else echo '<div class="ag-empty"><h3>'.esc_html($s['initial_title']).'</h3><p>'.esc_html($s['initial_body']).'</p></div>';
    echo '<div class="ag-topic-links"><a href="'.esc_url(vatan_url('products/')).'">محصولات <span class="ag-symbol" aria-hidden="true">↗</span></a><a href="'.esc_url(vatan_url('solutions/')).'">همکاری <span class="ag-symbol" aria-hidden="true">↗</span></a><a href="'.esc_url(vatan_url('blog/')).'">مجله تجارت <span class="ag-symbol" aria-hidden="true">↗</span></a></div>';ag_visual($s);
}
