<?php
/** Optional display preferences; dark is always the first-visit default. */
if(!defined('ABSPATH'))exit;
add_action('wp_enqueue_scripts',function(){
 if(vatan_feature('appearance_switch'))wp_enqueue_script('armaghan-appearance',vatan_asset('appearance.js'),[],filemtime(__DIR__.'/../assets/appearance.js'),false);
});
add_filter('body_class',function($classes){if(is_front_page()||is_home()||is_page(['about','contact','inquiry','solutions','b2b-supply','blog']))$classes[]='ag-photo-header';return $classes;});
function ag_appearance_mono($mobile=false){
 if(!vatan_feature('appearance_switch'))return;$s=ag_section('identity','global');
 echo '<button class="appearance-mono'.($mobile?' appearance-mono-mobile':'').'" type="button" data-appearance-mono '.($mobile?'hidden ':'').'aria-pressed="false" aria-label="'.esc_attr($s['appearance_mono']).'" title="'.esc_attr($s['appearance_mono']).'"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.5"/><path d="M12 4a8 8 0 0 0 0 16Z" fill="currentColor"/></svg>'.($mobile?'<span>'.esc_html($s['appearance_mono']).'</span>':'').'</button>';
}
function ag_appearance_switch(){
 if(!vatan_feature('appearance_switch'))return;$s=ag_section('identity','global');
 echo '<div class="appearance-switch" data-appearance-switch hidden><button class="appearance-toggle" type="button" role="switch" aria-checked="false" data-appearance-toggle data-dark-label="'.esc_attr($s['appearance_dark']).'" data-light-label="'.esc_attr($s['appearance_light']).'" aria-label="'.esc_attr($s['appearance_light']).'" title="'.esc_attr($s['appearance_label']).'"><span class="appearance-track" aria-hidden="true"><svg class="appearance-sun" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="3.5" stroke="currentColor" stroke-width="1.5"/><path d="M12 2v3m0 14v3M2 12h3m14 0h3M5 5l2 2m10 10 2 2M5 19l2-2M17 7l2-2" stroke="currentColor" stroke-width="1.5"/></svg><svg class="appearance-moon" viewBox="0 0 24 24" fill="none"><path d="M19.5 15.5A8 8 0 0 1 8.5 4.5a8 8 0 1 0 11 11Z" stroke="currentColor" stroke-width="1.5"/></svg><span class="appearance-thumb"></span></span></button>';
 ag_appearance_mono();echo '</div><span class="sr-only" data-appearance-status role="status" aria-live="polite"></span>';
}
