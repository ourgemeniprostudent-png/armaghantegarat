<?php
/** Approved About composition; every section uses the native WordPress editor. */
if (!defined('ABSPATH')) exit;

add_filter('body_class', function ($classes) {
    if (is_page('about')) {
        $classes[]='ag-about-page';
        if(vatan_feature('mobile_cta'))$classes[]='ag-about-mobile-cta';
    }
    return $classes;
});
function ag_about_arrow() {
    echo '<svg class="arrow" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 20 4 4M4 20V4H20" stroke="currentColor" stroke-width="1.3"/></svg>';
}
function ag_about_heading($s,$id,$wine=false) {
    $title=esc_html($s['title']??'');$emphasis=esc_html($s['emphasis']??'');
    if ($emphasis!=='') $title=str_replace($emphasis,'<strong>'.($wine?'<em>':'').$emphasis.($wine?'</em>':'').'</strong>',$title);
    echo '<h2 id="'.esc_attr($id).'">'.nl2br($title).'</h2>';
}
function ag_about_label($s) {
    echo '<div class="section-label"><b>'.esc_html($s['section_number']??'').'</b><span>'.esc_html($s['section_label']??'').'</span><i aria-hidden="true"></i></div>';
}
function ag_about_link($s,$class='text-link',$secondary=false) {
    $key=$secondary?'secondary_':'';$label=$s[$key.'label']??'';$link=$s[$key.'link']??'';
    if ($label===''||$link==='') return;
    echo '<a class="'.esc_attr($class).'" href="'.esc_url(ag_link($link)).'">'.esc_html($label);ag_about_arrow();echo '</a>';
}
function ag_about_media($s,$hero=false) {
    $image=ag_media_url(!empty($s['background_image'])?$s['background_image']:($s['image']??''));$video=ag_media_url($s['video']??'');
    if ($video) echo '<video class="scene-photo" controls playsinline preload="'.($hero?'metadata':'none').'"'.($image?' poster="'.esc_url($image).'"':'').' src="'.esc_url($video).'" aria-label="'.esc_attr($s['alt']??'').'">'.esc_html($s['alt']??'').'</video>';
    elseif ($image) echo '<img class="scene-photo" '.ag_image_attrs($image,'(max-width:760px) 1400px, 100vw',1920).' alt="'.esc_attr($s['alt']??'').'" '.($hero?'fetchpriority="high"':'loading="lazy"').' decoding="async">';
}
function ag_about_optional_media($s) {
    if (empty($s['image'])&&empty($s['video'])) return;
    echo '<div class="about-optional-media">';ag_about_media($s);echo '</div>';
}
function ag_render_about() {
    echo '<div class="at-about" data-ag-context="about">';
    foreach (ag_sections('about') as $id=>$section) {
        $s=$section['values'];$layout=$section['layout'];$title_id='about-'.$id.'-title';
        if ($layout==='about-hero') {
            echo '<section class="hero" id="'.esc_attr($id).'" aria-labelledby="'.esc_attr($title_id).'">';ag_about_media($s,true);
            echo '<div class="hero-body"><div class="wrap"><div class="path">';ag_breadcrumb();echo '</div><div data-ag-reveal><span class="eyebrow">'.esc_html($s['eyebrow']).'</span><h1 id="'.esc_attr($title_id).'">'.esc_html($s['line1']).'<span>'.esc_html($s['line2']).'</span></h1><div class="hero-intro">';ag_body($s['body']);
            echo '</div><div class="hero-actions">';ag_about_link($s,'button');ag_about_link($s,'text-link',true);
            echo '</div></div></div></div><div class="hero-bottom"><div class="hero-index wrap"><span><b>'.esc_html($s['foot_label']).'</b>'.esc_html($s['art_label']).'</span><small>'.esc_html($s['product_list']).'</small><a href="#story">'.esc_html($s['scroll_label']).' <span aria-hidden="true">↓</span></a></div></div></section>';
        } elseif ($layout==='about-story') {
            echo '<section class="section company" id="'.esc_attr($id).'" aria-labelledby="'.esc_attr($title_id).'"><div class="wrap">';ag_about_label($s);
            echo '<div class="company-grid"><div data-ag-reveal>';ag_about_heading($s,$title_id,true);
            echo '<p class="intro-kicker">'.nl2br(esc_html($s['kicker'])).'</p><span class="outlined" aria-hidden="true">'.esc_html($s['art_word']).'</span></div><div class="copy" data-ag-reveal>';ag_body($s['body']);
            echo '<div class="company-foot">';for($i=1;$i<=3;$i++)if(!empty($s['tag_'.$i]))echo '<span><i aria-hidden="true"></i>'.esc_html($s['tag_'.$i]).'</span>';
            echo '</div></div></div>';ag_about_optional_media($s);echo '</div></section>';
        } elseif ($layout==='about-scene') {
            echo '<section class="'.($id==='coffee'?'coffee-scene':'partnership').'" id="'.esc_attr($id).'" aria-labelledby="'.esc_attr($title_id).'">';ag_about_media($s);
            echo '<div class="wrap scene-copy" data-ag-reveal><span class="eyebrow">'.esc_html($s['eyebrow']).'</span>';ag_about_heading($s,$title_id);ag_body($s['body']);ag_about_link($s);
            echo '<small class="scene-caption">'.esc_html($s['caption']).'</small></div></section>';
        } elseif ($layout==='about-products') {
            echo '<section class="section section-products" id="'.esc_attr($id).'" aria-labelledby="'.esc_attr($title_id).'"><div class="wrap">';ag_about_label($s);
            echo '<div class="products-head" data-ag-reveal>';ag_about_heading($s,$title_id);echo '<div>';ag_body($s['body']);echo '</div></div><div class="products-list">';
            for($i=1;$i<=5;$i++){
                echo '<details'.($i===1?' open':'').'><summary><span class="product-num">'.esc_html(vatan_digits_fa(str_pad($i,2,'0',STR_PAD_LEFT))).'</span><span class="product-name">'.esc_html($s['item_'.$i.'_title']).'</span><span class="plus" aria-hidden="true">+</span></summary><div class="product-body"><p>'.nl2br(esc_html($s['item_'.$i.'_body'])).'</p><p class="note">'.nl2br(esc_html($s['item_'.$i.'_note'])).'</p></div></details>';
            }
            echo '</div>';ag_about_optional_media($s);echo '</div></section>';
        } elseif ($layout==='about-values') {
            echo '<section class="section section-values" id="'.esc_attr($id).'" aria-label="'.esc_attr($s['section_label']).'"><div class="wrap"><div class="values">';
            for($i=1;$i<=3;$i++)echo '<article class="value" data-ag-reveal><span class="value-num">'.esc_html($s['item_'.$i.'_label']).'</span><h3>'.esc_html($s['item_'.$i.'_title']).'</h3><p>'.nl2br(esc_html($s['item_'.$i.'_body'])).'</p></article>';
            echo '</div>';ag_about_optional_media($s);echo '</div></section>';
        } elseif ($layout==='about-process') {
            echo '<section class="section process" id="'.esc_attr($id).'" aria-labelledby="'.esc_attr($title_id).'"><div class="wrap">';ag_about_label($s);
            echo '<div class="process-head" data-ag-reveal>';ag_about_heading($s,$title_id);echo '<div>';ag_body($s['body']);echo '</div></div><ol class="steps">';
            for($i=1;$i<=3;$i++)echo '<li class="step" data-ag-reveal><span class="step-number" aria-hidden="true">'.esc_html(vatan_digits_fa(str_pad($i,2,'0',STR_PAD_LEFT))).'</span><h3>'.esc_html($s['item_'.$i.'_title']).'</h3><p>'.nl2br(esc_html($s['item_'.$i.'_body'])).'</p><small class="step-meta">'.esc_html($s['item_'.$i.'_meta']).'</small></li>';
            echo '</ol>';ag_about_optional_media($s);echo '</div></section>';
        } elseif ($layout==='about-contact') {
            echo '<section class="section contact" id="'.esc_attr($id).'" aria-labelledby="'.esc_attr($title_id).'"><div class="wrap">';ag_about_label($s);
            echo '<div class="contact-grid"><div data-ag-reveal>';ag_about_heading($s,$title_id);ag_body($s['body']);ag_about_link($s);
            $phone=vatan_option('phone','02191028166');echo '</div><div data-ag-reveal><a class="contact-phone" href="tel:'.esc_attr(preg_replace('/[^0-9+]/','',$phone)).'"><div><small>'.esc_html($s['phone_label']).'</small><span dir="ltr">'.esc_html(vatan_digits_fa($phone)).'</span></div>';ag_about_arrow();
            echo '</a><p class="office">'.esc_html(vatan_option('address','تهران، سهروردی شمالی، کوچه زمانی، پلاک ۱۱، ساختمان ایلیا، طبقه ۳، واحد ۹')).'</p></div></div>';ag_about_optional_media($s);
            if(!empty($s['image_notice']))echo '<p class="image-notice">'.esc_html($s['image_notice']).'</p>';
            echo '</div></section>';
        }
    }
    $content=get_post_field('post_content',get_queried_object_id());
    if(trim(wp_strip_all_tags($content))!=='')echo '<section class="section"><div class="wrap ag-editorial">'.apply_filters('the_content',$content).'</div></section>';
    echo '</div>';
}
