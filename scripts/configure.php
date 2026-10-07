<?php
$root=dirname(__DIR__);$wp=$root.'/.runtime/wordpress';$url=rtrim(getenv('ARMAGHAN_SITE_URL')?:'http://127.0.0.1:8788','/');
$host=parse_url($url,PHP_URL_HOST);$port=parse_url($url,PHP_URL_PORT);$path=parse_url($url,PHP_URL_PATH);
if(!$host||($path&&$path!=='/')||!in_array(parse_url($url,PHP_URL_SCHEME),['http','https'],true)){fwrite(STDERR,"ARMAGHAN_SITE_URL must be an http(s) root URL.\n");exit(1);}
$network=!empty($argv[1])&&$argv[1]==='network';
$constants=['DB_NAME'=>'armaghan_development','DB_USER'=>'','DB_PASSWORD'=>'','DB_HOST'=>'localhost','DB_CHARSET'=>'utf8mb4','DB_COLLATE'=>'','DB_ENGINE'=>'sqlite','WP_DEBUG'=>false,'WP_DEBUG_DISPLAY'=>false,'WP_ALLOW_MULTISITE'=>true,'DISALLOW_FILE_EDIT'=>true];
$saltsFile=$root.'/.runtime/salts.json';if(is_file($saltsFile))$salts=json_decode(file_get_contents($saltsFile),true);else{$salts=[];foreach(['AUTH_KEY','SECURE_AUTH_KEY','LOGGED_IN_KEY','NONCE_KEY','AUTH_SALT','SECURE_AUTH_SALT','LOGGED_IN_SALT','NONCE_SALT'] as $key)$salts[$key]=bin2hex(random_bytes(32));file_put_contents($saltsFile,json_encode($salts));chmod($saltsFile,0600);}
$constants=array_merge($constants,$salts);
if($network)$constants=array_merge($constants,['MULTISITE'=>true,'SUBDOMAIN_INSTALL'=>false,'DOMAIN_CURRENT_SITE'=>$host.($port?':'.$port:''),'PATH_CURRENT_SITE'=>'/','SITE_ID_CURRENT_SITE'=>1,'BLOG_ID_CURRENT_SITE'=>1]);
$config="<?php\n// Generated for development only. Never commit this file.\n";foreach($constants as $key=>$value)$config.='define('.var_export($key,true).', '.var_export($value,true).");\n";
$config.="/* That's all, stop editing! Happy publishing. */\n\$table_prefix = 'wp_';\nif (!defined('ABSPATH')) define('ABSPATH', __DIR__ . '/');\nrequire_once ABSPATH . 'wp-settings.php';\n";
file_put_contents($wp.'/wp-config.php',$config);chmod($wp.'/wp-config.php',0600);
