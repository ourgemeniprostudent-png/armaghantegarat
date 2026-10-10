<?php
/** Fresh-install wizard. Never replaces a config, database or existing table set. */
header('Content-Type: text/html; charset=UTF-8');header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');header('Referrer-Policy: no-referrer');
// Isolate the wizard's cookie from another application on the parent domain.
$install_path=rtrim(str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME']??'/armaghan-install.php')),'/').'/';
ini_set('display_errors','0');session_name('armaghan_installer');session_start(['save_path'=>sys_get_temp_dir(),'use_strict_mode'=>true,'cookie_path'=>$install_path,'cookie_httponly'=>true,'cookie_samesite'=>'Strict','cookie_secure'=>!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off']);
$root=__DIR__;$config=$root.'/wp-config.php';$statefile=$root.'/.armaghan-install-state.php';
// SQLite must remain outside the web server's document root, including subfolder installs.
$document_root=realpath($_SERVER['DOCUMENT_ROOT']??'');
$private_parent=$document_root?dirname($document_root):false;
function agi_escape($s){return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');}
function agi_page($body){echo '<!doctype html><html lang="fa" dir="rtl"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>نصب ارمغان تجارت وطن</title><style>body{background:#080909;color:#eee;font-family:Tahoma,sans-serif;line-height:2;margin:0}main{max-width:750px;padding:35px;margin:30px auto}h1{color:#d7b477}label{display:block;margin:15px 0}input,select{box-sizing:border-box;width:100%;padding:12px;background:#17202b;color:#fff;border:1px solid #8d7049;font:inherit}button,a{display:inline-block;color:#e1bb7d;margin:10px 0}button{background:#17202b;padding:12px 24px;font:inherit;border:1px solid #8d7049;cursor:pointer}.error{color:#ffb9ab}</style><main><h1>نصب ارمغان تجارت وطن</h1>'.$body.'</main></html>';}
$token=$_SESSION['armaghan_install_token']??'';
$authorized=is_file($statefile)&&$token!==''&&hash_equals((require $statefile)['token'],hash('sha256',$token));
if(is_file($config)&&!$authorized){agi_page('<p>وردپرس یا تنظیمات موجود پیدا شد. نصب دوباره انجام نمی‌شود و داده‌ها تغییر نمی‌کنند.</p><p><a href="./wp-admin/">ورود به پیشخوان</a></p>');exit;}
if(empty($_SESSION['armaghan_install_csrf']))$_SESSION['armaghan_install_csrf']=bin2hex(random_bytes(32));
$csrf=$_SESSION['armaghan_install_csrf'];$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
 if(!isset($_POST['csrf'])||!is_string($_POST['csrf'])||!hash_equals($csrf,$_POST['csrf'])){http_response_code(403);agi_page('<p>اعتبار صفحه پایان یافته است؛ صفحه را دوباره باز کنید.</p>');exit;}
 if($authorized){
  define('WP_INSTALLING',true);require $root.'/wp-load.php';require_once ABSPATH.'wp-admin/includes/plugin.php';require_once ABSPATH.'wp-admin/includes/upgrade.php';
  $state=require $statefile;wp_set_current_user($state['admin']);add_filter('pre_wp_mail','__return_false');
  $result=activate_plugin('vatan-core/vatan-core.php','',true,true);if(is_wp_error($result)){agi_page('<p class="error">فعال‌سازی افزونه کامل نشد. فایل‌های بسته را بررسی کنید.</p>');exit;}
  vatan_core_register();vatan_editorial_setup();vatan_core_setup();
  update_site_option('allowedthemes',['vatan-authority'=>true]);switch_theme('vatan-authority');
  $seed=json_decode(file_get_contents(WP_CONTENT_DIR.'/armaghan-public/public-site.json'),true,512,JSON_THROW_ON_ERROR);$map=[];$url=untrailingslashit(home_url());
  foreach($seed['posts'] as $p){$old=$p['ID'];unset($p['ID']);$p['post_parent']=0;$p['post_status']='publish';$p['post_author']=$state['admin'];$p['post_content']=str_replace('__ARMAGHAN_SITE_URL__',$url,$p['post_content']);$terms=$p['terms']??[];$topics=$p['topics']??[];$meta=$p['meta']??[];unset($p['terms'],$p['topics'],$p['meta']);$found=get_posts(['post_type'=>$p['post_type'],'post_status'=>'any','name'=>$p['post_name'],'numberposts'=>1]);if($found)$p['ID']=$found[0]->ID;$id=wp_insert_post(wp_slash($p),true);if(is_wp_error($id)){agi_page('<p class="error">ورود محتوای عمومی کامل نشد.</p>');exit;}$map[$old]=$id;if($terms)wp_set_object_terms($id,$terms,'vatan_category');if($topics)wp_set_object_terms($id,$topics,'vatan_topic');foreach($meta as $key=>$value)if(in_array($key,['_vatan_public_author','_vatan_featured','_vatan_seo_title','_vatan_seo_description'],true))update_post_meta($id,$key,$value);}
  foreach($seed['posts'] as $p)if($p['post_parent']&&isset($map[$p['post_parent']]))wp_update_post(['ID'=>$map[$p['ID']],'post_parent'=>$map[$p['post_parent']]]);
  foreach($seed['site'] as $key=>$value)if($value!==false)update_option($key,$value);foreach($seed['theme_mods'] as $key=>$value)set_theme_mod($key,$value);
  vatan_navigation_defaults();
  foreach(['page_on_front'=>'home','page_for_posts'=>'blog'] as $key=>$slug)update_option($key,get_page_by_path($slug)->ID);update_option('show_on_front','page');update_option('vatan_notify_email','');update_option('vatan_analytics_enabled',true);update_option('blog_public',1);
  foreach(['hello-world','sample-page'] as $slug)foreach(get_posts(['post_type'=>['post','page'],'post_status'=>'publish','name'=>$slug]) as $p)wp_delete_post($p->ID,true);
  $domain=wp_parse_url(home_url(),PHP_URL_HOST);$port=wp_parse_url(home_url(),PHP_URL_PORT);if($port)$domain.=':'.$port;
  $base=PATH_CURRENT_SITE;$enpath=$base.'en/';
  $en=get_sites(['network_id'=>1,'domain'=>$domain,'path'=>$enpath,'number'=>1]);if(!$en){$enid=wpmu_create_blog($domain,$enpath,'Armaghan Tejarat Vatan',$state['admin'],['public'=>0],1);if(is_wp_error($enid)){agi_page('<p class="error">ساخت زیرسایت پنهان کامل نشد.</p>');exit;}}else $enid=$en[0]->blog_id;
  switch_to_blog($enid);update_option('vatan_language_pending',true);update_option('WPLANG','en_US');restore_current_blog();
  flush_rewrite_rules(false);update_site_option('fileupload_maxk',102400);update_site_option('blog_upload_space',1024);
  $rewrite="# BEGIN WordPress\nRewriteEngine On\nRewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]\nRewriteBase {$base}\nRewriteRule ^index\\.php$ - [L]\nRewriteRule ^([_0-9a-zA-Z-]+/)?wp-admin$ \$1wp-admin/ [R=301,L]\nRewriteCond %{REQUEST_FILENAME} -f [OR]\nRewriteCond %{REQUEST_FILENAME} -d\nRewriteRule ^ - [L]\nRewriteRule ^([_0-9a-zA-Z-]+/)?(wp-(content|admin|includes).*) \$2 [L]\nRewriteRule ^([_0-9a-zA-Z-]+/)?(.*\\.php)$ \$2 [L]\nRewriteRule . index.php [L]\n# END WordPress\n";
  if(!is_file($root.'/.htaccess')){file_put_contents($root.'/.htaccess',$rewrite."
<IfModule mod_deflate.c>
AddOutputFilterByType DEFLATE text/html text/css application/javascript application/json image/svg+xml
</IfModule>
<IfModule mod_expires.c>
ExpiresActive On
ExpiresByType text/css A604800
ExpiresByType application/javascript A604800
ExpiresByType image/webp A604800
ExpiresByType font/woff2 A2592000
</IfModule>
");chmod($root.'/.htaccess',0644);}
  unlink($statefile);unset($_SESSION['armaghan_install_token']);session_regenerate_id(true);
  agi_page('<p>نصب کامل شد. سایت فارسی، محتوای عمومی و نسخه پنهان انگلیسی آماده‌اند.</p><p><a href="./">دیدن سایت</a> · <a href="./wp-admin/">ورود با حسابی که ساختید</a></p><p>فایل نصب را از ریشه هاست حذف کنید. اطلاعات واتس‌اپ، محصول و رسانه را از تنظیمات قالب و پیشخوان تکمیل کنید.</p>');exit;
 }
 $value=function($key){return isset($_POST[$key])&&is_string($_POST[$key])?trim($_POST[$key]):'';};
 $url=rtrim($value('site_url'),'/');$parts=parse_url($url);$engine=$value('engine');$prefix=$value('prefix');$user=$value('admin_user');$password=$value('admin_password');$email=$value('admin_email');
 if(PHP_VERSION_ID<80100||!extension_loaded('mbstring')||!extension_loaded('gd'))$errors[]='PHP 8.1 یا بالاتر همراه mbstring و GD لازم است.';
 $base=rtrim($parts['path']??'','/').'/';
 if(!$parts||!in_array($parts['scheme']??'',['http','https'],true)||empty($parts['host'])||isset($parts['user'])||isset($parts['pass'])||isset($parts['query'])||isset($parts['fragment'])||!preg_match('#^/(?:[a-zA-Z0-9_-]+/)*$#D',$base))$errors[]='نشانی کامل سایت را وارد کنید؛ مانند https://example.com/vatan/ . نام پوشه فقط حرف لاتین، عدد، خط تیره یا زیرخط باشد.';
 elseif($base!==$install_path)$errors[]='مسیر آدرس با محل استخراج بسته یکسان نیست. آدرس همین پوشه را وارد کنید.';
 if(!preg_match('/^[a-zA-Z][a-zA-Z0-9_]{0,15}$/D',$prefix))$errors[]='پیشوند جدول باید با حرف شروع شود و فقط حرف، عدد و زیرخط داشته باشد.';
 if(!preg_match('/^[a-zA-Z0-9_.-]{3,40}$/D',$user)||strlen($password)<12||!filter_var($email,FILTER_VALIDATE_EMAIL))$errors[]='نام کاربری، رمز حداقل ۱۲ کاراکتری و ایمیل معتبر لازم‌اند.';
 if(!in_array($engine,['mysql','sqlite'],true))$errors[]='نوع دیتابیس معتبر نیست.';
 $dbdir='';
 if($engine==='mysql'){
  if(!extension_loaded('mysqli'))$errors[]='افزونه mysqli در PHP هاست لازم است.';
  elseif(!$errors){try{mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);$host=$value('db_host')?:'localhost';$port=3306;$socket=null;if(preg_match('/^(.+):(\d+)$/D',$host,$match)){$host=$match[1];$port=(int)$match[2];}elseif(str_contains($host,':/')){[$host,$socket]=explode(':',$host,2);}$db=new mysqli($host,$value('db_user'),$value('db_password'),$value('db_name'),$port,$socket);$pattern=$db->real_escape_string(str_replace(['_','%'],['\\_','\\%'],$prefix).'%');$tables=$db->query("SHOW TABLES LIKE '".$pattern."'");if($tables->num_rows)$errors[]='جدول‌هایی با این پیشوند موجودند؛ داده موجود تغییر نمی‌کند. دیتابیس خالی یا پیشوند تازه انتخاب کنید.';$db->close();}catch(Throwable $error){$errors[]='اتصال دیتابیس برقرار نشد. نام، کاربر، رمز، میزبان و دسترسی‌های هاست را بررسی کنید.';}}
 }elseif($engine==='sqlite'){
  if(!extension_loaded('pdo_sqlite'))$errors[]='PDO SQLite در PHP هاست لازم است.';
  if(!$private_parent||!is_writable($private_parent))$errors[]='برای SQLite، پوشه خصوصی بیرون از ریشه عمومی هاست باید قابل ساخت باشد؛ در غیر این صورت MySQL را انتخاب کنید.';
 }
 if(!is_writable($root))$errors[]='ریشه سایت برای ایجاد تنظیمات قابل نوشتن نیست.';
 if(!$errors){
  $token=bin2hex(random_bytes(32));$_SESSION['armaghan_install_token']=$token;
  $constants=['DB_NAME'=>$engine==='mysql'?$value('db_name'):'armaghan','DB_USER'=>$engine==='mysql'?$value('db_user'):'','DB_PASSWORD'=>$engine==='mysql'?$value('db_password'):'','DB_HOST'=>$value('db_host')?:'localhost','DB_CHARSET'=>'utf8mb4','DB_COLLATE'=>'','WP_DEBUG'=>false,'WP_DEBUG_DISPLAY'=>false,'WP_ALLOW_MULTISITE'=>true,'DISALLOW_FILE_EDIT'=>true];
  if($engine==='sqlite'){$dbdir=$private_parent.'/armaghan-private-'.bin2hex(random_bytes(8));if(!mkdir($dbdir,0700)){$errors[]='پوشه خصوصی دیتابیس ساخته نشد.';}else{$constants['DB_ENGINE']='sqlite';$constants['DB_DIR']=$dbdir.'/';$constants['DB_FILE']='site.sqlite';copy($root.'/wp-content/armaghan-sqlite-dropin.php',$root.'/wp-content/db.php');}}
  if(!$errors){
   foreach(['AUTH_KEY','SECURE_AUTH_KEY','LOGGED_IN_KEY','NONCE_KEY','AUTH_SALT','SECURE_AUTH_SALT','LOGGED_IN_SALT','NONCE_SALT'] as $key)$constants[$key]=bin2hex(random_bytes(32));
   $make_config=function($constants)use($prefix){$text="<?php\n// Private host configuration generated at installation.\n";foreach($constants as $key=>$v)$text.='define('.var_export($key,true).','.var_export($v,true).");\n";$text.='$table_prefix = '.var_export($prefix,true).";\nif(!defined('ABSPATH'))define('ABSPATH',__DIR__.'/');\nrequire_once ABSPATH.'wp-settings.php';\n";return $text;};
   $handle=fopen($config,'x');if(!$handle){agi_page('<p>تنظیمات موجود تغییر نمی‌کند.</p>');exit;}fwrite($handle,$make_config($constants));fclose($handle);chmod($config,0600);
   define('WP_INSTALLING',true);require $root.'/wp-load.php';require_once ABSPATH.'wp-admin/includes/upgrade.php';add_filter('pre_wp_mail','__return_false');
   $installed=wp_install('ارمغان تجارت وطن',$user,$email,true,'',$password,'fa_IR');wp_set_current_user($installed['user_id']);update_option('home',$url);update_option('siteurl',$url);
   global $wpdb;foreach($wpdb->tables('ms_global') as $table=>$name)$wpdb->$table=$name;install_network();$domain=$parts['host'].(isset($parts['port'])?':'.$parts['port']:'');$network=populate_network(1,$domain,$email,'ارمغان تجارت وطن',$base,false);if(is_wp_error($network)&&$network->get_error_code()!=='no_wildcard_dns'){agi_page('<p class="error">آماده‌سازی شبکه کامل نشد. نصب دوباره روی داده موجود انجام نمی‌شود.</p>');exit;}
   $constants+=['MULTISITE'=>true,'SUBDOMAIN_INSTALL'=>false,'DOMAIN_CURRENT_SITE'=>$domain,'PATH_CURRENT_SITE'=>$base,'SITE_ID_CURRENT_SITE'=>1,'BLOG_ID_CURRENT_SITE'=>1];file_put_contents($config,$make_config($constants));chmod($config,0600);
   file_put_contents($statefile,'<?php return '.var_export(['token'=>hash('sha256',$token),'admin'=>$installed['user_id']],true).';');chmod($statefile,0600);header('Location: armaghan-install.php?step=content',true,303);exit;
  }
 }
}
if($authorized){agi_page('<p>وردپرس و شبکه ساخته شدند. اکنون قالب و محتوای عمومی را آماده کنید.</p><form method="post"><input type="hidden" name="csrf" value="'.agi_escape($csrf).'"><button type="submit">تکمیل قالب و محتوای سایت</button></form>');exit;}
$scheme=!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off'?'https':'http';$default=$scheme.'://'.($_SERVER['HTTP_HOST']??'').rtrim($install_path,'/');$body='<p>نصب تازه در پوشه خالی سایت؛ در ریشه دامنه یا ساب‌فولدر. هیچ رمز یا درخواست قبلی وارد نمی‌شود.</p>';
foreach($errors as $error)$body.='<p class="error">'.agi_escape($error).'</p>';
$body.='<form method="post" autocomplete="off"><input type="hidden" name="csrf" value="'.agi_escape($csrf).'"><label>نشانی کامل سایت، شامل ساب‌فولدر<input name="site_url" type="url" required value="'.agi_escape($default).'" dir="ltr"></label><label>نوع دیتابیس<select name="engine"><option value="mysql">MySQL / MariaDB — پیشنهاد هاست</option><option value="sqlite">SQLite — نیازمند PDO و پوشه خصوصی</option></select></label>';
foreach(['db_host'=>'میزبان دیتابیس','db_name'=>'نام دیتابیس خالی','db_user'=>'کاربر دیتابیس','db_password'=>'رمز دیتابیس','prefix'=>'پیشوند تازه جدول‌ها','admin_user'=>'نام کاربری مدیر تازه','admin_password'=>'رمز مدیر؛ حداقل ۱۲ کاراکتر','admin_email'=>'ایمیل مدیر'] as $key=>$label)$body.='<label>'.agi_escape($label).'<input name="'.$key.'" type="'.(str_contains($key,'password')?'password':($key==='admin_email'?'email':'text')).'" dir="ltr" value="'.($key==='db_host'?'localhost':($key==='prefix'?'ag_':'')).'" '.(str_starts_with($key,'admin_')?'required':'').'></label>';
agi_page($body.'<button type="submit">ساخت وردپرس و شبکه</button></form>');
