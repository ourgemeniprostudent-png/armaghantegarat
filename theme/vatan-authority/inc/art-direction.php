<?php
/** The interior art direction. All visible copy and media come from section fields. */
if (!defined('ABSPATH')) exit;

function ag_art_motif($class='') {
    // An abstract trade diagram, not a geographic map or a claim about origins.
    echo '<svg class="ag-art-motif '.esc_attr($class).'" viewBox="0 0 640 640" fill="none" aria-hidden="true"><circle class="ag-orbit" cx="320" cy="320" r="282"/><circle cx="320" cy="320" r="222" stroke-dasharray="1 12"/><ellipse cx="320" cy="320" rx="130" ry="282"/><ellipse cx="320" cy="320" rx="282" ry="95"/><path d="M38 320h564M320 38v564" class="ag-axis"/><path class="ag-route-line" pathLength="1" d="M106 420C130 192 390 160 528 248S306 506 208 474"/><circle cx="106" cy="420" r="6"/><circle cx="528" cy="248" r="6"/><circle cx="208" cy="474" r="6"/></svg>';
}

function ag_art_hero($s,$context) {
    echo '<div class="ag-hero-stage" data-ag-parallax><div class="ag-stage-word" aria-hidden="true">'.esc_html($s['art_word']??'تجارت').'</div>';
    ag_art_motif();
    echo '<div class="ag-stage-orbit" aria-hidden="true"></div><div class="ag-stage-frame">';ag_visual($s,true);echo '</div>';
    echo '<div class="ag-stage-caption"><span class="ag-stage-dot" aria-hidden="true"></span>'.esc_html($s['art_label']??$s['eyebrow']??'').'</div></div>';
}

function ag_art_section($s,$layout) {
    if($layout==='manifesto') {
        echo '<div class="ag-manifesto" data-ag-progress><span class="eyebrow">'.esc_html($s['eyebrow']).'</span><h2 class="ag-manifesto-type" data-ag-ink>'.esc_html($s['title']).'</h2><div class="ag-manifesto-bottom"><span class="ag-manifesto-mark" aria-hidden="true"><svg viewBox="0 0 100 100" fill="none"><path d="M18 82L82 18M18 18H82V82" stroke="currentColor" stroke-width="2"/></svg></span><div>';ag_body($s['body']);ag_action($s);echo '</div></div>';ag_visual($s);echo '</div>';
    } elseif($layout==='route') {
        echo '<div class="ag-route-story" data-ag-progress><div class="ag-section-head" data-ag-reveal><div>';ag_heading($s);echo '</div><div>';ag_body($s['body']);echo '</div></div><div class="ag-route-diagram">';ag_art_motif();echo '<ol class="ag-route-stops">';
        for($i=1;$i<=3;$i++){echo '<li data-ag-reveal><span class="ag-route-index">'.esc_html(vatan_digits_fa('0'.$i)).'</span><h3>'.esc_html($s['item_'.$i.'_title']).'</h3><p>'.esc_html($s['item_'.$i.'_body']).'</p>';ag_visual(['image'=>$s['item_'.$i.'_image']??'','video'=>$s['item_'.$i.'_video']??'','alt'=>$s['item_'.$i.'_title']]);echo '</li>';}
        echo '</ol></div>';ag_visual($s);echo '</div>';
    } elseif($layout==='dossier') {
        echo '<div class="ag-section-head" data-ag-reveal><div>';ag_heading($s);echo '</div><div>';ag_body($s['body']);echo '</div></div><div class="ag-dossier">';
        for($i=1;isset($s['item_'.$i.'_title']);$i++) {
            echo '<article class="ag-dossier-chapter" data-ag-reveal><div class="ag-chapter-index" aria-hidden="true">'.esc_html(vatan_digits_fa('0'.$i)).'</div><div class="ag-chapter-copy"><span class="eyebrow">'.esc_html($s['item_'.$i.'_label']??'').'</span><h3>'.esc_html($s['item_'.$i.'_title']).'</h3>';ag_body($s['item_'.$i.'_body']);
            if(!empty($s['item_'.$i.'_link']))ag_action(['label'=>$s['item_'.$i.'_action'],'link'=>$s['item_'.$i.'_link']]);echo '</div>';ag_visual(['image'=>$s['item_'.$i.'_image']??'','video'=>$s['item_'.$i.'_video']??'','alt'=>$s['item_'.$i.'_title']]);echo '</article>';
        }echo '</div>';ag_visual($s);
    } elseif($layout==='fieldnotes') {
        echo '<div class="ag-fieldnotes"><div class="ag-fieldnote-intro" data-ag-reveal>';ag_heading($s);ag_body($s['body']);ag_visual($s);echo '</div><div class="ag-fieldnote-list">';
        for($i=1;isset($s['item_'.$i.'_title']);$i++){echo '<article data-ag-reveal><span class="ag-fieldnote-number">'.esc_html(vatan_digits_fa('0'.$i)).'</span><div><h3>'.esc_html($s['item_'.$i.'_title']).'</h3><p>'.esc_html($s['item_'.$i.'_body']).'</p>';ag_visual(['image'=>$s['item_'.$i.'_image']??'','video'=>$s['item_'.$i.'_video']??'','alt'=>$s['item_'.$i.'_title']]);echo '</div></article>';}
        echo '</div></div>';
    }
}
