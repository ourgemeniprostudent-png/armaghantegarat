#!/usr/bin/env python3
"""Export anonymous public routes to a portable, view-only GitHub preview.
No database, users, credentials, PHP, form tokens or private requests are exported.
"""
import argparse, hashlib, json, os, posixpath, re, shutil, subprocess
from html import escape
from html.parser import HTMLParser
from pathlib import Path
from urllib.error import HTTPError
from urllib.parse import urlsplit, urlunsplit, unquote
from urllib.request import urlopen

ROOT=Path(__file__).resolve().parent.parent
parser=argparse.ArgumentParser(description=__doc__)
parser.add_argument('--output',type=Path,required=True)
parser.add_argument('--php',default=os.environ.get('ARMAGHAN_PHP_BIN','php'))
args=parser.parse_args()
OUT=args.output.resolve();ORIGIN=(ROOT/'.runtime/site-url.txt').read_text().strip().rstrip('/')
if OUT.exists():raise SystemExit('Choose a new output directory; existing output is preserved.')
wp=[args.php,str(ROOT/'vendor/wp-cli-2.12.0.phar'),'--allow-root','--path='+str(ROOT/'.runtime/wordpress'),'--url='+ORIGIN]
code='''$posts=get_posts(['post_type'=>['page','post','vatan_product','vatan_episode'],'post_status'=>'publish','numberposts'=>-1]);$out=[];foreach($posts as $p){if(get_post_meta($p->ID,'_qa_capability',true))throw new RuntimeException('Remove QA fixtures before export');$parts=array_merge([get_the_excerpt($p),wp_strip_all_tags(strip_shortcodes($p->post_content))],ag_search_sections_text(armaghan_context($p),$p->ID));if($p->post_type==='vatan_episode')$parts[]=get_post_meta($p->ID,'_vatan_transcript',true);if($p->post_type==='vatan_product')$parts[]=get_post_meta($p->ID,'_vatan_specs',true);$out[]=['id'=>$p->ID,'url'=>get_permalink($p),'title'=>$p->post_title,'body'=>implode(' ',array_filter($parts)),'excerpt'=>get_the_excerpt($p),'date'=>get_post_time('U',true,$p),'topics'=>wp_get_post_terms($p->ID,'vatan_topic',['fields'=>'slugs']),'kind'=>['page'=>'صفحه','post'=>'یادداشت تجارت','vatan_product'=>'محصول','vatan_episode'=>'رسانه'][$p->post_type],'searchable'=>!in_array($p->post_name,['search','thank-you'],true)];}foreach(get_terms(['taxonomy'=>'vatan_category','hide_empty'=>false]) as $term)$out[]=['url'=>get_term_link($term),'title'=>$term->name,'body'=>implode(' ',ag_search_sections_text(armaghan_context($term),$term->term_id)),'excerpt'=>$term->description,'kind'=>'گروه محصول'];echo wp_json_encode($out);'''
public=json.loads(subprocess.check_output(wp+['eval',code],text=True))
ROUTES=sorted({urlsplit(p['url']).path for p in public})
SHORT={str(p['id']):p['url'] for p in public if 'id' in p}
OUT.mkdir(parents=True)
assets=OUT/'wp-content/themes/vatan-authority';assets.mkdir(parents=True)
shutil.copytree(ROOT/'theme/vatan-authority/assets',assets/'assets')
shutil.copy2(ROOT/'theme/vatan-authority/style.css',assets/'style.css')
# The same original video bytes are served from the preview's own origin with native MIME/Range support.
VIDEO_HASHES={'hero-h264.mp4':'f7ae6d349f977be82939382e521f8e36597d90cceac6393ba7772627596f8f6c','hero-av1.mp4':'b48c6cf207b16ddfd183eac4f7d6263067472f942dc1dfb9004270450c97eec5'}
for name,expected in VIDEO_HASHES.items():
 assert hashlib.file_digest((assets/'assets/media'/name).open('rb'),'sha256').hexdigest()==expected,'Original video changed; verify before publishing'
GUARD="""(() => {'use strict';document.addEventListener('submit',event=>{if(event.target.matches('[data-discovery],[data-wp-search],[data-journal-controls]' ))return;event.preventDefault();event.stopImmediatePropagation();alert('این نسخه فقط پیش‌نمایش است؛ ثبت درخواست در پیش‌نمایش انجام نمی‌شود. برای ثبت واقعی، نسخه وردپرس باید روی هاست اجرا شود.');},true);document.addEventListener('DOMContentLoaded',()=>{const f=document.querySelector('[data-inquiry-form]');if(f){const q=new URLSearchParams(location.search);for(const key of ['category','product'])if(q.has(key)&&f.elements[key])f.elements[key].value=q.get(key).slice(0,150);}});})();"""
(OUT/'preview-guard.js').write_text(GUARD)
shutil.copy2(ROOT/'scripts/preview-search.js',OUT/'preview-search.js')
index=[{**p,'url':urlsplit(p['url']).path} for p in public]
(OUT/'public-index.json').write_text(json.dumps(index,ensure_ascii=False)+'\n')
MEDIA_EXT={'.png','.jpg','.jpeg','.webp','.avif','.gif','.svg','.mp4','.webm','.mov','.mp3','.m4a','.ogg','.wav','.woff','.woff2','.pdf'}
class Portable(HTMLParser):
 def __init__(self,route):super().__init__(convert_charrefs=False);self.route=route;self.result=[];self.skip=0
 def relative(self,path):
  target=path.lstrip('/')
  if path in ROUTES or path.endswith('/'):target=target.rstrip('/')+'/index.html' if target else 'index.html'
  start=self.route.lstrip('/').rstrip('/') or '.'
  return posixpath.relpath(target,start)
 def url(self,value):
  parts=urlsplit(value)
  if parts.scheme and not value.startswith(ORIGIN+'/'):return value
  if value.startswith('#') or not value.startswith((ORIGIN+'/', '/')):return value
  path=unquote(parts.path);query=parts.query
  if path=='/' and re.fullmatch(r'p=\d+',query):parts=urlsplit(SHORT.get(query[2:],ORIGIN+'/'));path=parts.path;query=''
  if path.startswith('/wp-content/uploads/'):
   file=ROOT/'.runtime/wordpress'/path.lstrip('/');dest=OUT/path.lstrip('/')
   if file.suffix.lower() not in MEDIA_EXT:raise ValueError('Unsupported public upload type')
   assert file.resolve().is_relative_to((ROOT/'.runtime/wordpress/wp-content/uploads').resolve())
   dest.parent.mkdir(parents=True,exist_ok=True);shutil.copy2(file,dest)
  if path.startswith(('/wp-admin/','/wp-login.php','/wp-json/','/xmlrpc.php')):return '#'
  return urlunsplit(('', '',self.relative(path),query,parts.fragment))
 def handle_starttag(self,tag,attrs):
  a=dict(attrs)
  if tag=='html':attrs=list(attrs)+[('data-static-preview','1')]
  if tag=='link' and any(x in a.get('href','') for x in ['wp-json','xmlrpc.php','/feed/']):return
  if tag=='meta' and a.get('name')=='robots':return
  if tag=='input' and a.get('name') in ['vatan_nonce','_wp_http_referer','request_key','source_url','action']:return
  if tag=='script' and a.get('type')=='application/ld+json':self.skip=1;return
  if self.skip:return
  new=[]
  for key,value in attrs:
   if value is None:new.append(key);continue
   if tag=='form' and key=='method' and not ('data-discovery' in a or 'data-wp-search' in a or 'data-journal-controls' in a):value='dialog'
   elif tag=='form' and key=='action':value='#'
   elif key in ['href','src','poster','data-av1','data-h264'] or (tag=='meta' and key=='content' and value.startswith(ORIGIN+'/')):value=self.url(value)
   elif key=='srcset':value=', '.join(' '.join([self.url(entry.strip().split()[0])]+entry.strip().split()[1:]) for entry in value.split(','))
   new.append(key+'="'+escape(value,quote=True)+'"')
  self.result.append('<'+tag+(' '+' '.join(new) if new else '')+'>')
 def handle_startendtag(self,tag,attrs):self.handle_starttag(tag,attrs)
 def handle_endtag(self,tag):
  if self.skip:
   if tag=='script':self.skip=0
   return
  if tag=='head':self.result.append('<meta name="robots" content="noindex,nofollow"><style>[data-public-card][hidden]{display:none!important}</style><script src="'+self.relative('/preview-guard.js')+'" defer></script><script src="'+self.relative('/preview-search.js')+'" defer></script>')
  self.result.append('</'+tag+'>')
 def handle_data(self,data):
  if not self.skip:self.result.append(data)
 def handle_entityref(self,name):
  if not self.skip:self.result.append('&'+name+';')
 def handle_charref(self,name):
  if not self.skip:self.result.append('&#'+name+';')
 def handle_decl(self,decl):self.result.append('<!'+decl+'>')
 def handle_comment(self,data):
  if not self.skip:self.result.append('<!--'+data+'-->')
def render(html,route):
 html=re.sub(r'<aside\b[^>]*data-analytics-consent[^>]*>.*?</aside>','',html,flags=re.S)
 html=re.sub(r'<div\b[^>]*data-event-endpoint[^>]*></div>','',html)
 html=re.sub(r'<button\b[^>]*data-event-settings[^>]*>.*?</button>','',html,flags=re.S)
 html=re.sub(r'<script\b[^>]*>.*?</script>',lambda m:'' if 'wp-emoji' in m.group(0) else m.group(0),html,flags=re.S|re.I)
 p=Portable(route);p.feed(html);result=''.join(p.result)
 assert ORIGIN not in result and '/Users/' not in result
 assert all(token not in result for token in ['name="vatan_nonce"','name="request_key"','name="source_url"','wp-admin/admin-post.php','wp-admin/post.php'])
 return result
checks=[]
for route in ROUTES:
 with urlopen(ORIGIN+route,timeout=30) as response:
  assert response.status==200
  html=render(response.read().decode(),route)
 target=OUT/route.lstrip('/')/'index.html';target.parent.mkdir(parents=True,exist_ok=True);target.write_text(html)
 checks.append({'route':route,'status':200})
try:urlopen(ORIGIN+'/not-a-real-preview-page/',timeout=20)
except HTTPError as e:
 assert e.code==404;(OUT/'404.html').write_text(render(e.read().decode(),'/'))
(OUT/'.nojekyll').write_text('')
commit=subprocess.check_output(['git','-C',str(ROOT),'rev-parse','HEAD'],text=True).strip()
(OUT/'README.md').write_text('# ارمغان تجارت وطن — پیش‌نمایش عمومی\n\nخروجی فقط برای دیدن طراحی است. جستجوی محتوای عمومی در پیش‌نمایش فعال است. ثبت درخواست و پیشخوان در وردپرس اجرا می‌شوند. هیچ رمز، کاربر، دیتابیس یا درخواست خصوصی در این شاخه نیست.\n\nSource commit: '+commit+'\n')
files=[]
for file in sorted(OUT.rglob('*')):
 if not file.is_file():continue
 assert file.suffix.lower() not in ['.php','.sqlite','.db','.phar','.part','.sql']
 assert '.runtime' not in file.relative_to(OUT).parts and file.name!='access.txt'
 with file.open('rb') as f:digest=hashlib.file_digest(f,'sha256').hexdigest()
 files.append({'path':file.relative_to(OUT).as_posix(),'bytes':file.stat().st_size,'sha256':digest})
(OUT/'preview-manifest.json').write_text(json.dumps({'source_commit':commit,'routes':checks,'files':files},ensure_ascii=False,indent=2)+'\n')
print(f'Exported {len(ROUTES)} public routes and {len(files)} files to {OUT}')
