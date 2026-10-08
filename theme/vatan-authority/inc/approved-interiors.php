<?php
/** Owner-reviewed photographic interiors, rendered through native editable sections. */
if (!defined('ABSPATH')) exit;
add_filter('body_class',function($classes){if(is_home()||is_singular('post'))$classes[]='at-journal-page';return $classes;});
function ag_approved_form_state($context){
    $data=[];$errors=[];$token=isset($_GET['form_error'])&&!is_array($_GET['form_error'])?sanitize_text_field(wp_unslash($_GET['form_error'])):'';
    if(preg_match('/^[a-zA-Z0-9]{40}$/D',$token)){$stored=get_transient('vatan_error_'.$token);if(is_array($stored)&&($stored['data']['form_kind']??'')===($context==='contact'?'contact':'inquiry')){$data=$stored['data'];$errors=$stored['errors'];}}
    if($context==='inquiry'){
        $category=isset($_GET['category'])&&!is_array($_GET['category'])?sanitize_key($_GET['category']):'';
        if(isset(vatan_core_categories()[$category]))$data['category']=$data['category']??$category;
        if(isset($_GET['product'])&&!is_array($_GET['product']))$data['product']=$data['product']??mb_substr(sanitize_text_field(wp_unslash($_GET['product'])),0,150);
        $id=isset($_GET['product_id'])&&!is_array($_GET['product_id'])?absint($_GET['product_id']):0;$p=get_post($id);
        if($p&&$p->post_type==='vatan_product'&&$p->post_status==='publish'){$terms=wp_get_post_terms($id,'vatan_category',['fields'=>'slugs']);if(!is_wp_error($terms)&&$terms){$data['category']=$data['category']??$terms[0];$data['product']=$data['product']??$p->post_title;$data['product_id']=$data['product_id']??$id;}}
    }
    return [$data,$errors];
}
function ag_approved_hidden($context,$data){
    $key=$data['request_key']??'';if(!preg_match('/^[a-zA-Z0-9]{32,64}$/D',$key))$key=wp_generate_password(40,false,false);
    $values=['action'=>'vatan_inquiry','form_kind'=>$context==='contact'?'contact':'inquiry','form_edition'=>'approved','request_key'=>$key,'customer_type'=>'other','product_id'=>$data['product_id']??0,'source_url'=>$data['source_url']??home_url('/'.$context.'/')];
    foreach(['utm_source','utm_medium','utm_campaign','utm_term','utm_content'] as $k)$values[$k]=$data[$k]??'';
    $out=wp_nonce_field('vatan_inquiry','vatan_nonce',true,false);
    foreach($values as $k=>$v)$out.='<input type="hidden" name="'.esc_attr($k).'" value="'.esc_attr($v).'">';
    return $out.'<div class="hp" aria-hidden="true"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div>';
}
function ag_approved_errors($errors){
    if(!$errors)return '';
    $out='<div class="status" role="alert" tabindex="-1" data-server-errors><strong>هنوز ثبت نشده است؛ اطلاعات شما حفظ شده است.</strong><ul>';
    foreach($errors as $key=>$message)$out.='<li><a href="#approved-'.esc_attr($key==='category'?'category-coffee':$key).'">'.esc_html($message).'</a></li>';
    return $out.'</ul></div>';
}
function ag_approved_restore($html,$data,$errors){
    $html=preg_replace_callback('/<input\b[^>]*data-restore-radio="([a-z_]+)"[^>]*>/u',function($m)use($data){preg_match('/\bvalue="([^"]*)"/u',$m[0],$v);return ($data[$m[1]]??'')===($v[1]??'')?substr($m[0],0,-1).' checked>':$m[0];},$html);
    $html=preg_replace_callback('/<input\b[^>]*data-restore-check="([a-z_]+)"[^>]*>/u',function($m)use($data){return ($data[$m[1]]??'')==='1'?substr($m[0],0,-1).' checked>':$m[0];},$html);
    $html=preg_replace_callback('/<select\b[^>]*data-restore-select="([a-z_]+)"[^>]*>.*?<\/select>/su',function($m)use($data){return preg_replace_callback('/<option\b[^>]*value="([^"]*)"[^>]*>/u',function($option)use($data,$m){return ($data[$m[1]]??'')===html_entity_decode($option[1],ENT_QUOTES,'UTF-8')?substr($option[0],0,-1).' selected>':$option[0];},$m[0]);},$html);
    foreach($errors as $key=>$message){$html=preg_replace('/(<(?:input|textarea|select)\b[^>]*\bname="'.preg_quote($key,'/').'"[^>]*)(>)/u','$1 aria-invalid="true"$2',$html);}
    return $html;
}
function ag_render_approved($context){
    [$data,$errors]=ag_approved_form_state($context);$phone=vatan_option('phone');$address=vatan_option('address');
    echo '<div class="at-approved at-approved-'.esc_attr($context==='blog'?'journal':$context).'">';
    echo '<svg width="0" height="0" aria-hidden="true" style="position:absolute"><symbol id="approved-arrow" viewBox="0 0 24 24"><path d="M20 20 4 4M4 20V4H20" fill="none" stroke="currentColor" stroke-width="1.4"/></symbol></svg>';
    foreach(ag_sections($context) as $id=>$section){
        if($section['layout']!=='approved')continue;
        $file=__DIR__.'/approved/'.$context.'-'.$id.'.html';if(!is_file($file))continue;$s=$section['values'];
        $markup=str_replace('href="#arrow"','href="#approved-arrow"',file_get_contents($file));
        $markup=preg_replace_callback('/\{\{([a-z]+):([a-z0-9_-]+)\}\}/',function($m)use($s,$context,$data,$errors,$phone,$address){
            [$all,$kind,$key]=$m;
            if($kind==='text')return esc_html($s[$key]??'');
            if($kind==='media')return esc_url(ag_media_url(($key==='image'&&!empty($s['background_image']))?$s['background_image']:($s[$key]??'')));
            if($kind==='link')return esc_url(ag_link($s[$key]??''));
            if($kind==='value')return esc_attr($data[$key]??'');
            if($kind==='global'){return match($key){'phone'=>esc_html(vatan_digits_fa($phone)),'tel'=>esc_url('tel:'.preg_replace('/[^+0-9]/','',$phone)),'address'=>esc_html($address),'map'=>esc_url('https://www.google.com/maps/search/?api=1&query='.rawurlencode($address)),default=>''};}
            if($kind==='native'){return match($key){'action'=>esc_url(admin_url('admin-post.php')),'privacy'=>esc_url(home_url('/privacy/')),'fields'=>ag_approved_hidden($context,$data).ag_approved_errors($errors),'journal'=>ag_approved_journal($s),default=>''};}
            return '';
        },$markup);
        echo ag_approved_restore($markup,$data,$errors);
        if(!empty($s['video']))ag_video($s,'optional-video');
        // Optional media on typographic sections remains editable without adding stock imagery.
        if(!str_contains($markup,'class="scene"')&&!str_contains($markup,'class="feature-photo"')&&!empty($s['image']))echo '<img class="optional-image" '.ag_image_attrs(ag_media_url($s['image']),'90vw',1280).' alt="'.esc_attr($s['alt']??'').'" loading="lazy">';
    }
    $p=get_queried_object();if($p instanceof WP_Post&&trim($p->post_content)&&!str_contains($p->post_content,'[vatan_inquiry'))echo '<section class="section"><div class="wrap">'.apply_filters('the_content',$p->post_content).'</div></section>';
    echo '</div>';
}
function ag_approved_article_image($post){
    if(has_post_thumbnail($post))return wp_get_attachment_url(get_post_thumbnail_id($post));
    return ag_media_url(ag_section('article','post:'.$post->post_name)['image']??'');
}
function ag_approved_journal($s){
    $topic=isset($_GET['topic'])&&!is_array($_GET['topic'])?sanitize_key($_GET['topic']):'';$term=isset($_GET['q'])&&!is_array($_GET['q'])?sanitize_text_field(wp_unslash($_GET['q'])):'';
    $q=new WP_Query(['post_type'=>'post','post_status'=>'publish','posts_per_page'=>-1]);$posts=$q->posts;$featured=null;
    foreach($posts as $p)if($p->post_name==='coffee-roast-grind-and-consistency'){$featured=$p;break;}
    $featured=$featured??($posts[0]??null);ob_start();
    if($featured){$url=get_permalink($featured);$image=ag_approved_article_image($featured);echo '<article class="feature-article"><div><span class="eyebrow">'.esc_html($s['featured_label']??'پرونده قهوه').'</span><h3 class="feature-title"><a href="'.esc_url($url).'">'.esc_html($s['featured_title']??$featured->post_title).'</a></h3><a class="text-link" href="'.esc_url($url).'">'.esc_html($s['read_label']??'خواندن مقاله').' <svg class="arrow" aria-hidden="true"><use href="#approved-arrow"/></svg></a></div><div class="feature-body"><figure><a href="'.esc_url($url).'"><img class="feature-photo" '.ag_image_attrs($image,'(max-width:760px) 90vw, 45vw',768).' alt="'.esc_attr($featured->post_title).'" loading="lazy"></a><figcaption>'.esc_html($s['photo_note']??'تصویر مفهومی اختصاصی مقاله').'</figcaption></figure><p>'.esc_html(get_the_excerpt($featured)).'</p></div></article>';}
    if(vatan_feature('blog_discovery')){
        $topic=isset($_GET['topic'])&&!is_array($_GET['topic'])?sanitize_key($_GET['topic']):'';$term=isset($_GET['q'])&&!is_array($_GET['q'])?sanitize_text_field(wp_unslash($_GET['q'])):'';
        echo '<form class="editorial-controls journal-search" data-discovery-form data-journal-controls method="get" action="'.esc_url(home_url('/blog/')).'"><div class="filters">';
        echo '<label class="search-field">'.esc_html($s['topic_label']??'موضوع').'<select name="topic" aria-label="'.esc_attr($s['topic_label']??'موضوع مقاله').'"><option value="">'.esc_html($s['all_label']??'همه مقاله‌ها').'</option>';
        foreach(get_terms(['taxonomy'=>'vatan_topic','hide_empty'=>true]) as $t)echo '<option value="'.esc_attr($t->slug).'" '.selected($topic,$t->slug,false).'>'.esc_html($t->name).'</option>';echo '</select></label></div><label class="search-field">'.esc_html($s['search_label']??'جستجو').'<input name="q" value="'.esc_attr($term).'" placeholder="'.esc_attr($s['search_hint']??'عنوان یا موضوع مقاله').'" type="search"></label><button type="submit">'.esc_html($s['search_button']??'جستجو').'</button></form>';
    }
    echo '<p class="small-note" data-journal-count role="status" aria-live="polite"></p><div class="article-list" data-journal-list>';
    foreach($posts as $p){$terms=wp_get_post_terms($p->ID,'vatan_topic');$slugs=wp_list_pluck($terms,'slug');$names=wp_list_pluck($terms,'name');$url=get_permalink($p);$image=ag_approved_article_image($p);
        echo '<article '.((vatan_feature('blog_discovery')&&(($topic&&!in_array($topic,$slugs,true))||($term&&mb_stripos($p->post_title.' '.get_the_excerpt($p),$term)===false)))?'hidden ':'').'class="article-row" data-journal-row data-topics="'.esc_attr(implode(' ',$slugs)).'" data-search="'.esc_attr($p->post_title.' '.get_the_excerpt($p)).'"><a class="article-cover" href="'.esc_url($url).'" aria-label="'.esc_attr($p->post_title).'"><img class="article-thumbnail" '.ag_image_attrs($image,'(max-width:760px) 105px,190px',240).' alt="'.esc_attr($p->post_title).'" loading="lazy"></a><div><h3 class="article-title"><a href="'.esc_url($url).'">'.esc_html($p->post_title).'</a></h3><p>'.esc_html(get_the_excerpt($p)).'</p></div><div class="meta"><span>'.esc_html(implode(' / ',$names)).'</span><br><span>'.esc_html(vatan_digits_fa(max(1,(int)ceil(count(preg_split('/\s+/u',wp_strip_all_tags($p->post_content)))/180)))).' '.esc_html($s['minutes_label']??'دقیقه مطالعه').'</span></div><a class="read-icon" href="'.esc_url($url).'" aria-label="'.esc_attr(($s['read_label']??'خواندن مقاله').' '.$p->post_title).'"><svg class="arrow" aria-hidden="true"><use href="#approved-arrow"/></svg></a></article>';
    }
    echo '</div><p class="status" data-journal-empty hidden>'.esc_html($s['empty_label']??'مقاله‌ای با این جستجو پیدا نشد.').'</p>';return ob_get_clean();
}
