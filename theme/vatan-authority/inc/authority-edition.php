<?php
/** Shared art direction. Content and media continue to use native section fields. */
if (!defined('ABSPATH')) exit;

function ag_authority_palette() {
    $defaults=['navy'=>'#0d1b2a','black'=>'#0b0b0b','gold'=>'#c39a5b'];
    $saved=get_option('vatan_authority_palette',[]);$saved=is_array($saved)?$saved:[];
    foreach($defaults as $key=>$value) $defaults[$key]=sanitize_hex_color($saved[$key]??'')?:$value;
    return $defaults;
}
add_action('wp_enqueue_scripts',function(){
    $p=ag_authority_palette();
    wp_add_inline_style('armaghan-site',':root{--at-navy:'.$p['navy'].';--at-black:'.$p['black'].';--at-gold:'.$p['gold'].';--gold:'.$p['gold'].';--black:'.$p['black'].';}');
    if(!is_front_page()) wp_enqueue_script('armaghan-authority',vatan_asset('authority-edition.js'),['armaghan-art'],filemtime(__DIR__.'/../assets/authority-edition.js'),true);
},20);
add_filter('body_class',function($classes){if(!is_front_page())$classes[]='ag-authority';return $classes;});

function ag_authority_background($s) {
    $image=ag_media_url(!empty($s['background_image'])?$s['background_image']:($s['image']??''));
    if(!$image)return;
    echo '<div class="at-backdrop" aria-hidden="true"><img '.ag_image_attrs($image,'100vw',1920).' alt="" fetchpriority="high" decoding="async"></div>';
}
function ag_authority_hero($s,$id,$context) {
    $mode=in_array($context,['about','solutions','b2b-supply','media'],true)?'panorama':(in_array($context,['inquiry','thank-you','search','faq','privacy'],true)?'utility':($context==='blog'?'journal':'product'));
    echo '<section class="ag-hero at-opening at-opening-'.esc_attr($mode).'" id="'.esc_attr($id).'"><div class="wrap ag-page-path">';ag_breadcrumb();echo '</div>';
    if($mode==='panorama')ag_authority_background($s);
    echo '<div class="wrap at-opening-grid"><div class="ag-hero-copy at-opening-copy">';ag_heading($s,'h1');ag_body($s['body']??'');ag_action($s,'button trade-button');echo '</div>';
    if($mode!=='panorama')echo '<div class="at-opening-art">';
    if($mode!=='panorama'){ag_visual($s,true);echo '<span class="at-art-word" aria-hidden="true">'.esc_html($s['art_word']??'').'</span></div>';}
    elseif(!empty($s['video'])){echo '<div class="at-opening-video">';ag_video($s);echo '</div>';}
    echo '</div><div class="wrap at-opening-foot"><span>'.esc_html($s['art_label']??$s['foot_label']??'').'</span><a href="#'.esc_attr(array_keys(ag_sections($context))[1]??$id).'">'.esc_html($s['scroll_label']??'ادامه روایت').' <span aria-hidden="true">↓</span></a></div></section>';ag_inner_nav($context);
}

function ag_authority_contact($s,$id,$context) {
    echo '<section class="ag-hero at-contact" id="'.esc_attr($id).'">';ag_authority_background($s);
    echo '<div class="wrap ag-page-path">';ag_breadcrumb();echo '</div><div class="wrap at-contact-grid"><div class="ag-hero-copy at-contact-copy">';ag_heading($s,'h1');
    echo '<div class="at-contact-description">';ag_body($s['body']??'');echo '</div>';
    $phone=vatan_option('phone','02191028166');
    echo '<a class="ag-contact-direct" href="tel:'.esc_attr(preg_replace('/[^0-9+]/','',$phone)).'"><span>'.esc_html($s['call_label']).'</span><strong dir="ltr">'.esc_html($phone).'</strong><span class="ag-symbol" aria-hidden="true">↖</span></a><div class="ag-contact-actions">';ag_action($s,'button trade-button');
    if(vatan_feature('contact_form'))echo '<a class="home-route-link" href="#short-form">'.esc_html($s['message_label']).' <span aria-hidden="true">↖</span></a>';
    echo '</div></div><div class="at-contact-art"><span class="at-art-word" aria-hidden="true">'.esc_html($s['art_word']).'</span>';
    if(vatan_feature('maritime_scene')){
        $intensity=get_option('vatan_scene_intensity','balanced');
        echo '<div class="at-vessel" data-vessel data-intensity="'.esc_attr(in_array($intensity,['soft','balanced','strong'],true)?$intensity:'balanced').'" aria-hidden="true"><canvas></canvas><svg class="at-vessel-fallback" viewBox="0 0 600 420" fill="none"><path d="M300 370L105 225l37-58 154-80 179 115 18 66z" fill="#c39a5b"/><path d="M300 370V259L493 268M300 259L142 167M300 259L475 202" stroke="#0d1b2a" stroke-width="3"/><path d="M192 185V136l44-23 83 55v51M242 218V167l45-24 83 54v52M294 250V198l47-24 82 53v42" stroke="#f4e7d1" stroke-width="4"/></svg></div>';
    }
    $focus=ag_media_url($s['focus_image']??'');if($focus)echo '<img class="at-coffee" '.ag_image_attrs($focus,'(max-width:700px) 130px, 210px',480).' alt="'.esc_attr($s['focus_alt']??'').'" decoding="async">';
    echo '<p class="at-scene-caption">'.esc_html($s['art_label']).'</p></div></div><div class="wrap ag-contact-access"><span>'.esc_html($s['foot_label']).'</span><a href="#office">'.esc_html($s['office_label']).' <span aria-hidden="true">↓</span></a><a href="#short-form">'.esc_html($s['message_label']).' <span aria-hidden="true">↓</span></a></div></section>';ag_inner_nav($context);
    if(!empty($s['video'])){echo '<div class="wrap at-contact-video">';ag_video($s);echo '</div>';}
}
