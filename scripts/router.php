<?php
$path=rawurldecode(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH));
$root=dirname(__DIR__).'/.runtime/wordpress';
if (str_contains($path,'..')) { http_response_code(400); exit; }
// Match ordinary hosting compression in development; video bytes remain untouched.
if(is_file($root.$path)&&in_array(pathinfo($path,PATHINFO_EXTENSION),['css','js','svg'],true)){
 $type=['css'=>'text/css','js'=>'application/javascript','svg'=>'image/svg+xml'][pathinfo($path,PATHINFO_EXTENSION)];
 header('Content-Type: '.$type);header('Cache-Control: public, max-age=604800');header('Vary: Accept-Encoding');
 $body=file_get_contents($root.$path);if(str_contains($_SERVER['HTTP_ACCEPT_ENCODING']??'','gzip')){header('Content-Encoding: gzip');$body=gzencode($body,6);}header('Content-Length: '.strlen($body));
 if($_SERVER['REQUEST_METHOD']!=='HEAD')echo $body;return true;
}
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
if(str_contains($_SERVER['HTTP_ACCEPT_ENCODING']??'','gzip'))ob_start('ob_gzhandler');
require $root.'/index.php';
