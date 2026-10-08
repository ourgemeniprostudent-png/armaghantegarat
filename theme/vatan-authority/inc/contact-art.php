<?php
/** Contact art direction; public copy and media remain native section fields. */
if (!defined('ABSPATH')) exit;
add_filter('body_class',function($classes){
    if(vatan_feature('warm_pages'))$classes[]='ag-warm-interiors';
    if(is_page('contact')&&vatan_feature('contact_cinematic'))$classes[]='ag-contact-edition';
    return $classes;
});
function ag_contact_message($s){
    echo '<div class="ag-contact-short ag-form-card ag-contact-message"><div class="ag-contact-message-intro"><span class="eyebrow">'.esc_html($s['eyebrow']).'</span><h2>'.esc_html($s['title']).'</h2>';ag_body($s['body']);
    echo '<span class="ag-contact-form-word" aria-hidden="true">'.esc_html($s['form_word']).'</span></div><div class="ag-contact-message-fields">'.vatan_contact_form().'</div></div>';ag_visual($s);
}
