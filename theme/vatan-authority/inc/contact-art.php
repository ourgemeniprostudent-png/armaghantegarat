<?php
/** Contact art direction; public copy and media remain native section fields. */
if (!defined('ABSPATH')) exit;
add_filter('body_class',function($classes){
    if(vatan_feature('warm_pages'))$classes[]='ag-warm-interiors';
    if(is_page('contact')&&vatan_feature('contact_cinematic'))$classes[]='ag-contact-edition';
    return $classes;
});
add_action('wp_enqueue_scripts',function(){
    if(!vatan_feature('warm_pages'))return;
    $color=sanitize_hex_color(get_option('vatan_warm_background','#d8c8a6'))?:'#d8c8a6';
    wp_add_inline_style('armaghan-site','body.ag-warm-interiors{--warm-bg:'.$color.';}');
},20);
function ag_contact_maritime_scene(){
    if(!vatan_feature('maritime_scene'))return;
    // An illustrative cargo vessel and corridor, never a geographic or ownership claim.
    echo '<svg class="ag-contact-sea" viewBox="0 0 900 520" fill="none" aria-hidden="true" focusable="false"><path class="ag-sea-course" pathLength="1" d="M72 435C220 422 284 368 431 383S660 320 820 335"/><g class="ag-sea-coordinates"><path d="M68 36v54M42 63h54M803 440v54M777 467h54M68 280h20M820 172h20"/><circle cx="431" cy="383" r="6"/><circle cx="820" cy="335" r="6"/></g><g class="ag-sea-vessel"><path d="M101 369h232l-24 37H129zM130 369v-36h139v36M139 333v-27h37v27M180 333v-27h37v27M221 333v-27h38v27M275 369v-62h37v62M281 307v-20h22v20M138 343h26v17h-26zM174 343h26v17h-26zM210 343h26v17h-26zM283 318h21v12h-21zM286 287v-19M151 418h123M173 429h123"/></g></svg>';
}
function ag_contact_hero($s,$id,$context){
    echo '<section class="ag-hero ag-contact-hero" id="'.esc_attr($id).'"><div class="wrap ag-page-path">';ag_breadcrumb();echo '</div><div class="wrap ag-contact-hero-grid">';
    echo '<div class="ag-hero-copy ag-contact-hero-copy" data-ag-reveal>';ag_heading($s,'h1');ag_body($s['body']??'');
    $phone=vatan_option('phone','02191028166');
    echo '<a class="ag-contact-direct" href="tel:'.esc_attr(preg_replace('/[^0-9+]/','',$phone)).'"><span>'.esc_html($s['call_label']).'</span><strong dir="ltr">'.esc_html($phone).'</strong><span class="ag-symbol" aria-hidden="true">↖</span></a><div class="ag-contact-actions">';ag_action($s,'button trade-button');
    if(vatan_feature('contact_form'))echo '<a class="home-route-link" href="#short-form">'.esc_html($s['message_label']).' <span aria-hidden="true">↖</span></a>';
    echo '</div></div><div class="ag-hero-stage ag-contact-stage" data-ag-parallax><div class="ag-contact-harbor">';ag_visual($s,true);echo '</div>';
    ag_contact_maritime_scene();
    $focus=ag_media_url($s['focus_image']??'');
    if($focus)echo '<img class="ag-contact-focus" '.ag_image_attrs($focus,'(max-width:900px) 190px, 320px',480).' alt="'.esc_attr($s['focus_alt']??'').'" decoding="async">';
    echo '<span class="ag-contact-stage-word" aria-hidden="true">'.esc_html($s['art_word']??'').'</span><div class="ag-stage-caption"><span class="ag-stage-dot" aria-hidden="true"></span>'.esc_html($s['art_label']??'').'</div></div></div>';
    echo '<div class="wrap ag-contact-access"><span>'.esc_html($s['foot_label']).'</span><a href="#office">'.esc_html($s['office_label']).' <span aria-hidden="true">↓</span></a><a href="#contact-guide">'.esc_html($s['scroll_label']).' <span aria-hidden="true">↓</span></a></div><div class="ag-hero-line" aria-hidden="true"></div></section>';
    ag_inner_nav($context);
}
function ag_contact_message($s){
    echo '<div class="ag-contact-short ag-form-card ag-contact-message"><div class="ag-contact-message-intro"><span class="eyebrow">'.esc_html($s['eyebrow']).'</span><h2>'.esc_html($s['title']).'</h2>';ag_body($s['body']);
    echo '<span class="ag-contact-form-word" aria-hidden="true">'.esc_html($s['form_word']).'</span></div><div class="ag-contact-message-fields">'.vatan_contact_form().'</div></div>';ag_visual($s);
}
