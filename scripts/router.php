<?php
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
$root=dirname(__DIR__).'/.runtime/wordpress';
if (str_contains($path,'..')) { http_response_code(400); exit; }
if ($path!=='/' && (is_file($root.$path)||is_dir($root.$path))) return false;
$mapped=preg_replace('#^/en/(wp-admin|wp-content|wp-includes|wp-login\.php|wp-cron\.php)#','/$1',$path);
if($mapped!==$path){
 if(is_dir($root.$mapped)&&is_file($root.$mapped.'/index.php'))$mapped=rtrim($mapped,'/').'/index.php';
 if(is_file($root.$mapped)){
  if(pathinfo($mapped,PATHINFO_EXTENSION)==='php'){$_SERVER['SCRIPT_FILENAME']=$root.$mapped;require $root.$mapped;}
  else { $mime=mime_content_type($root.$mapped);header('Content-Type: '.$mime);readfile($root.$mapped); }
  return true;
 }
}
$_SERVER['SCRIPT_NAME']='/index.php';
require $root.'/index.php';
