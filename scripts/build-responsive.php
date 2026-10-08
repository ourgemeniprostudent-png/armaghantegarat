<?php
/** Generate technical display sizes through WordPress' image pipeline; originals are retained. */
if(!defined('ABSPATH'))exit;
$assets=get_template_directory().'/assets';$output=$assets.'/responsive';if(!is_dir($output))mkdir($output,0755,true);
$files=array_merge(glob($assets.'/products/*.webp'),glob($assets.'/cooperation/*.webp'),glob($assets.'/*.webp'),[$assets.'/brand-original.png']);$manifest=[];
foreach($files as $source){$relative=substr($source,strlen($assets)+1);$name=str_replace(['/','.'],['-','-'],$relative);$original=getimagesize($source);$variants=[];
 foreach([240,480,768] as $width){if($width>$original[0])continue;$target=$output.'/'.$name.'-'.$width.'.webp';if(!is_file($target)||filemtime($target)<filemtime($source)){$editor=wp_get_image_editor($source);if(is_wp_error($editor))throw new RuntimeException($editor->get_error_message());$editor->set_quality(90);$editor->resize($width,null,false);$result=$editor->save($target,'image/webp');if(is_wp_error($result))throw new RuntimeException($result->get_error_message());}$size=getimagesize($target);$variants[]=['path'=>'responsive/'.basename($target),'width'=>$size[0],'height'=>$size[1]];}
 $manifest[$relative]=['width'=>$original[0],'height'=>$original[1],'variants'=>$variants];
}
file_put_contents($output.'/manifest.json',wp_json_encode($manifest,JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT)."\n");if(defined('WP_CLI')&&WP_CLI)WP_CLI::success('Responsive image sizes generated without changing originals.');
