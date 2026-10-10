#!/usr/bin/env python3
"""Exercise the ZIP's two-step wizard on a disposable, empty local Apache/MySQL installation.

Never point this test at a customer host: it creates a fresh admin and synthetic leads.
The database must already exist and be empty. Credentials are read from environment.
"""
import argparse,hashlib,json,os,re,secrets,subprocess
from html.parser import HTMLParser
from http.cookiejar import CookieJar
from pathlib import Path
from urllib.error import HTTPError
from urllib.parse import parse_qs,urlencode,urlparse
from urllib.request import HTTPCookieProcessor,Request,build_opener

p=argparse.ArgumentParser(description=__doc__)
p.add_argument('--url',required=True);p.add_argument('--path',type=Path,required=True)
p.add_argument('--php',required=True);p.add_argument('--wp-cli',type=Path,required=True)
p.add_argument('--db-name',required=True);p.add_argument('--report',type=Path,required=True)
p.add_argument('--engine',choices=['mysql','sqlite'],default='mysql')
a=p.parse_args();base=a.url.rstrip('/');host=urlparse(base).hostname
assert host in ['127.0.0.1','localhost'],'Disposable local tests only.'
assert not (a.path/'wp-config.php').exists(),'Existing installations are never reset.'
wizard=base+'/armaghan-install.php';jar=CookieJar();client=build_opener(HTTPCookieProcessor(jar))
checks=[]
def get(url):
 with client.open(url,timeout=60) as r:return r.read().decode(),r.url
def post(url,data):
 with client.open(Request(url,data=urlencode(data).encode()),timeout=90) as r:return r.read().decode(),r.url
class Inputs(HTMLParser):
 def __init__(self):super().__init__();self.fields={};self.actions=[]
 def handle_starttag(self,tag,attrs):
  d=dict(attrs)
  if tag=='input' and d.get('name'):self.fields[d['name']]=d.get('value','')
  if tag=='form':self.actions.append(d.get('action',''))
def inputs(html):
 out=Inputs();out.feed(html);return out
def wp(code):
 return subprocess.check_output([a.php,str(a.wp_cli),'--allow-root','--path='+str(a.path),'--url='+base+'/','eval',code],text=True).strip()
html,_=get(wizard);fields=inputs(html).fields
assert fields['site_url']==base,'The wizard must detect its actual subfolder.'
assert all(c.path==urlparse(base).path+'/' for c in jar),'Installer cookie must be scoped to its folder.'
password=secrets.token_urlsafe(25)
data={**fields,'engine':a.engine,'db_host':os.environ.get('ARMAGHAN_TEST_DB_HOST','127.0.0.1:13316'),'db_name':a.db_name,'db_user':os.environ.get('ARMAGHAN_TEST_DB_USER','root'),'db_password':os.environ.get('ARMAGHAN_TEST_DB_PASSWORD',''),'prefix':'ag_','admin_user':'host_check_admin','admin_password':password,'admin_email':'host-check@example.invalid'}
try:post(wizard,{**data,'csrf':'invalid'})
except HTTPError as e:assert e.code==403
else:raise AssertionError('Invalid wizard CSRF was accepted.')
assert not (a.path/'wp-config.php').exists()
wrong,_=post(wizard,{**data,'site_url':base+'/other'})
assert 'مسیر آدرس با محل استخراج' in wrong and not (a.path/'wp-config.php').exists()
checks+=['automatic subfolder URL and cookie scope','CSRF and mismatched-path rejection']
html,_=post(wizard,data)
assert 'تکمیل قالب و محتوای سایت' in html,'Network initialization failed.'
html,_=post(wizard,{'csrf':inputs(html).fields['csrf']})
assert 'نصب کامل شد' in html,'Content installation failed.'
assert not (a.path/'.armaghan-install-state.php').exists()
info=json.loads(wp('echo wp_json_encode(["home"=>home_url(),"network_path"=>PATH_CURRENT_SITE,"paths"=>array_map(function($s){return $s->path;},get_sites()),"pages"=>(int)wp_count_posts("page")->publish,"posts"=>(int)wp_count_posts("post")->publish,"notify"=>get_option("vatan_notify_email"),"leads"=>(int)wp_count_posts("vatan_lead")->private]);'))
assert info['home'].rstrip('/')==base and info['network_path']==urlparse(base).path+'/'
assert sorted(info['paths'])==sorted([info['network_path'],info['network_path']+'en/'])
assert info['pages']==13 and info['posts']==8 and info['leads']==0 and info['notify']==''
assert 'RewriteBase '+info['network_path'] in (a.path/'.htaccess').read_text()
checks+=['two-stage '+a.engine+' multisite installation','13 pages, 8 articles, hidden English inside the subfolder, no imported users/leads']
if a.engine=='sqlite':
 dbdir=Path(wp('echo DB_DIR;')).resolve()
 document_root=a.path.resolve()
 for _ in urlparse(base).path.strip('/').split('/'):
  document_root=document_root.parent
 assert not dbdir.is_relative_to(document_root)
 checks+=['SQLite database is outside the public document root']
before=hashlib.sha256((a.path/'wp-config.php').read_bytes()).hexdigest()
locked,_=post(wizard,data)
assert 'نصب دوباره انجام نمی‌شود' in locked
assert hashlib.sha256((a.path/'wp-config.php').read_bytes()).hexdigest()==before
checks+=['repeat installation leaves configuration and data intact']
form,_=get(base+'/contact/');parsed=inputs(form)
assert base+'/wp-admin/admin-post.php' in parsed.actions
lead={**parsed.fields,'action':'vatan_inquiry','form_kind':'contact','form_edition':'approved','name':'آزمایش نصب ساب‌فولدر','mobile':'۰۹۱۲۱۲۳۴۵۶۷','customer_type':'other','topic':'general','notes':'آزمایش خصوصی بسته نصب؛ بدون ایمیل','consent':'1','website':''}
html,receipt=post(base+'/wp-admin/admin-post.php',lead)
assert receipt.startswith(base+'/thank-you/?reference=VAT-'),receipt
reference=parse_qs(urlparse(receipt).query)['reference'][0]
assert reference in html and lead['name'] not in html
_,repeat=post(base+'/wp-admin/admin-post.php',lead);assert repeat==receipt
lead_info=json.loads(wp('$q=get_posts(["post_type"=>"vatan_lead","post_status"=>"private","meta_key"=>"_vatan_reference","meta_value"=>"'+reference+'"]);echo wp_json_encode(["count"=>count($q),"id"=>$q[0]->ID,"mobile"=>get_post_meta($q[0]->ID,"_vatan_mobile",true)]);'))
assert lead_info['count']==1 and lead_info['mobile']=='09121234567'
for route in ['/search/?q='+reference,'/wp-json/wp/v2/search?search='+reference]:
 body,_=get(base+route);assert lead['name'] not in body and lead['notes'] not in body
wp('wp_delete_post('+str(lead_info['id'])+',true);')
checks+=['real contact persistence, Persian digits, subfolder receipt, duplicate prevention and private-data isolation']
get(base+'/wp-login.php')
admin,admin_url=post(base+'/wp-login.php',{'log':'host_check_admin','pwd':password,'wp-submit':'Log In','redirect_to':base+'/wp-admin/','testcookie':'1'})
assert admin_url.startswith(base+'/wp-admin/') and 'id="wpadminbar"' in admin
settings,_=get(base+'/wp-admin/options-general.php?page=vatan-settings');assert 'vatan_phone' in settings
checks+=['admin login cookie and native theme settings inside subfolder']
report={'url':base,'database':a.engine,'checks':checks,'content':info,'status':'passed'}
a.report.write_text(json.dumps(report,ensure_ascii=False,indent=2)+'\n')
print('Passed: '+str(len(checks))+' host installation and persistence checks.')
