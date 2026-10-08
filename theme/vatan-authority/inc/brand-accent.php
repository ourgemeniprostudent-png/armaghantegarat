<?php
/** A restrained, reversible third brand color. Gold remains the text/control color. */
if (!defined('ABSPATH')) exit;
function ag_brand_accent_settings() {
    return function_exists('vatan_brand_accent') ? vatan_brand_accent() : ['enabled'=>'1','color'=>'#76273b','strength'=>'balanced'];
}
add_filter('body_class', function ($classes) {
    if (ag_brand_accent_settings()['enabled']==='1') $classes[]='ag-brand-accent';
    return $classes;
});
add_action('wp_enqueue_scripts', function () {
    $accent=ag_brand_accent_settings();
    if ($accent['enabled']!=='1') return;
    $color=sanitize_hex_color($accent['color'])?:'#76273b';
    $opacity=['soft'=>'.62','balanced'=>'.82','defined'=>'1'][$accent['strength']]??'.82';
    $share=['soft'=>'62%','balanced'=>'76%','defined'=>'88%'][$accent['strength']]??'76%';
    wp_add_inline_style('armaghan-site','body.ag-brand-accent{--brand-wine:'.$color.';--brand-wine-opacity:'.$opacity.';--brand-wine-share:'.$share.';}');
}, 20);
