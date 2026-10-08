<?php
/** Development-only native byte-range delivery. Original media files are never changed. */
$file=$root.$path;$size=filesize($file);$modified=filemtime($file);
$etag='"'.dechex($size).'-'.dechex($modified).'"';
header('Content-Type: '.(strtolower(pathinfo($file,PATHINFO_EXTENSION))==='webm'?'video/webm':'video/mp4'));
header('Accept-Ranges: bytes');header('Cache-Control: public, max-age=604800');
header('ETag: '.$etag);header('Last-Modified: '.gmdate('D, d M Y H:i:s',$modified).' GMT');
header('X-Content-Type-Options: nosniff');
if(!in_array($_SERVER['REQUEST_METHOD'],['GET','HEAD'],true)){http_response_code(405);header('Allow: GET, HEAD');return;}
if(($_SERVER['HTTP_IF_NONE_MATCH']??'')===$etag){http_response_code(304);return;}
$start=0;$end=$size-1;$range=$_SERVER['HTTP_RANGE']??'';$ifRange=$_SERVER['HTTP_IF_RANGE']??'';
if($ifRange!==''&&$ifRange!==$etag&&strtotime($ifRange)!==$modified)$range='';
// Ignore multipart/unknown range units and return a normal full representation.
if($_SERVER['REQUEST_METHOD']==='GET'&&preg_match('/^bytes=(\d*)-(\d*)$/D',$range,$match)){
 if($match[1]===''&&$match[2]!==''){$suffix=(int)$match[2];$start=max(0,$size-$suffix);}
 elseif($match[1]!==''){$start=(int)$match[1];if($match[2]!=='')$end=min($end,(int)$match[2]);}
 if(($match[1]===''&&($match[2]===''||(int)$match[2]===0))||$start>=$size||$start>$end){
  http_response_code(416);header('Content-Range: bytes */'.$size);header('Content-Length: 0');return;
 }
 http_response_code(206);header('Content-Range: bytes '.$start.'-'.$end.'/'.$size);
}
$remaining=$end-$start+1;header('Content-Length: '.$remaining);
if($_SERVER['REQUEST_METHOD']==='HEAD')return;
$stream=fopen($file,'rb');fseek($stream,$start);
while($remaining>0&&!feof($stream)&&!connection_aborted()){
 $data=fread($stream,min(262144,$remaining));if($data===false||$data==='')break;
 echo $data;$remaining-=strlen($data);flush();
}
fclose($stream);
