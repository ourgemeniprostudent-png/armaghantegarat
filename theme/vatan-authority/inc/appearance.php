<?php
/** Optional display preferences; dark is always the first-visit default. */
if(!defined('ABSPATH'))exit;
add_action('wp_enqueue_scripts',function(){
 if(vatan_feature('appearance_switch'))wp_enqueue_script('armaghan-appearance',vatan_asset('appearance.js'),[],filemtime(__DIR__.'/../assets/appearance.js'),false);
});
add_filter('body_class',function($classes){if(is_front_page()||is_home()||is_page(['about','contact','inquiry','solutions','b2b-supply','blog']))$classes[]='ag-photo-header';return $classes;});
function ag_appearance_mono(){
 if(!vatan_feature('appearance_switch'))return;$s=ag_section('identity','global');
 echo '<button class="appearance-mono" type="button" data-appearance-mono hidden aria-pressed="false">'.esc_html($s['appearance_mono']).'</button>';
}
function ag_appearance_switch(){
 if(!vatan_feature('appearance_switch'))return;$s=ag_section('identity','global');
 echo '<div class="appearance-switch" data-appearance-switch hidden><button class="appearance-toggle" type="button" role="switch" aria-checked="false" data-appearance-toggle data-dark-label="'.esc_attr($s['appearance_dark']).'" data-light-label="'.esc_attr($s['appearance_light']).'" aria-label="'.esc_attr($s['appearance_light']).'" title="'.esc_attr($s['appearance_label']).'"><span class="appearance-track" aria-hidden="true"><svg class="appearance-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="4" fill="currentColor" stroke="none"/><line x1="12" y1="1.5" x2="12" y2="4"/><line x1="12" y1="20" x2="12" y2="22.5"/><line x1="1.5" y1="12" x2="4" y2="12"/><line x1="20" y1="12" x2="22.5" y2="12"/><line x1="4.5" y1="4.5" x2="6.3" y2="6.3"/><line x1="17.7" y1="17.7" x2="19.5" y2="19.5"/><line x1="4.5" y1="19.5" x2="6.3" y2="17.7"/><line x1="17.7" y1="6.3" x2="19.5" y2="4.5"/></svg><svg class="appearance-moon" viewBox="0 0 24 24" fill="none"><path d="M19.5 15.5A8 8 0 0 1 8.5 4.5a8 8 0 1 0 11 11Z" stroke="currentColor" stroke-width="1.5"/></svg><span class="appearance-thumb"></span></span></button>';
 echo '</div><span class="sr-only" data-appearance-status role="status" aria-live="polite"></span>';
}
