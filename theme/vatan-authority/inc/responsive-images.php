<?php
if(!defined('ABSPATH'))exit;
function ag_image_attrs($url,$sizes='(max-width:767px) 90vw, 520px',$preferred=768){
    static $manifest;if($manifest===null){$file=get_template_directory().'/assets/responsive/manifest.json';$manifest=is_file($file)?json_decode(file_get_contents($file),true):[];}
    $prefix=vatan_asset('');$path=str_starts_with($url,$prefix)?substr($url,strlen($prefix)):'';$entry=$manifest[$path]??null;
    if(!$entry)return 'src="'.esc_url($url).'"';
    $variants=$entry['variants'];$source=$url;$srcset=[];
    foreach($variants as $item){$srcset[]=vatan_asset($item['path']).' '.$item['width'].'w';if($item['width']<=$preferred)$source=vatan_asset($item['path']);}
    $srcset[]=$url.' '.$entry['width'].'w';
    return 'src="'.esc_url($source).'" srcset="'.esc_attr(implode(', ',$srcset)).'" sizes="'.esc_attr($sizes).'" width="'.esc_attr($entry['width']).'" height="'.esc_attr($entry['height']).'"';
}
function ag_article_html($content){
    $index=0;return preg_replace_callback('/<h([23])([^>]*)>(.*?)<\/h\1>/is',function($m)use(&$index){$index++;$attrs=$m[2];if(!preg_match('/\bid\s*=/', $attrs))$attrs.=' id="article-part-'.$index.'"';return '<h'.$m[1].$attrs.'>'.$m[3].'</h'.$m[1].'>';},$content);
}
function ag_article_toc($html){
    preg_match_all('/<h[23][^>]*\bid=["\']([^"\']+)["\'][^>]*>(.*?)<\/h[23]>/is',$html,$matches,PREG_SET_ORDER);
    foreach($matches as $m)echo '<a href="#'.esc_attr($m[1]).'">'.esc_html(wp_strip_all_tags($m[2])).'</a>';
}
