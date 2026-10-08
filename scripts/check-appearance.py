#!/usr/bin/env python3
"""Exercise display switching, persistence and all public page layouts without changing WordPress data."""
import argparse,json,os
from pathlib import Path
from functools import partial
from http.server import SimpleHTTPRequestHandler,ThreadingHTTPServer
from threading import Thread
from playwright.sync_api import sync_playwright
root=Path(__file__).resolve().parent.parent;p=argparse.ArgumentParser(description=__doc__);p.add_argument('--preview',type=Path);a=p.parse_args();server=None
out=root/'.runtime/appearance-checks';out.mkdir(exist_ok=True)
if a.preview:
 class H(SimpleHTTPRequestHandler):
  def log_message(self,*args):pass
 preview=a.preview.resolve();manifest=json.loads((preview/'preview-manifest.json').read_text());routes=[v['route'] for v in manifest['routes']]
 server=ThreadingHTTPServer(('127.0.0.1',0),partial(H,directory=str(preview)));Thread(target=server.serve_forever,daemon=True).start();base=f'http://127.0.0.1:{server.server_port}'
else:
 import subprocess
 base=(root/'.runtime/site-url.txt').read_text().strip().rstrip('/')
 cmd=[os.environ.get('ARMAGHAN_PHP_BIN','php'),str(root/'vendor/wp-cli-2.12.0.phar'),'--allow-root','--path='+str(root/'.runtime/wordpress'),'--url='+base]
 code='echo wp_json_encode(array_values(array_unique(array_merge([home_url("/")],array_map("get_permalink",get_posts(["post_type"=>["page","post","vatan_product","vatan_episode"],"post_status"=>"publish","numberposts"=>-1])),array_map(fn($t)=>get_term_link($t),get_terms(["taxonomy"=>"vatan_category","hide_empty"=>false])),ag_media_public_routes()))));'
 from urllib.parse import urlsplit
 routes=[urlsplit(u).path for u in json.loads(subprocess.check_output(cmd+['eval',code],text=True))]
records=[];errors=[]
def dismiss(q):
 if q.locator('[data-event-choice=no]').is_visible():q.locator('[data-event-choice=no]').click()
def select(q,mode):
 current=q.locator('html').get_attribute('data-site-theme')
 if current==mode:return
 if mode=='dark' or (mode=='light' and current=='dark'):q.locator('[data-appearance-toggle]').click()
 else:
  mono=q.locator('[data-appearance-mono]:visible')
  mobile=mono.count()==0
  if mobile:q.locator('.menu-toggle').click()
  q.locator('[data-appearance-mono]:visible').click()
  if mobile:q.locator('.menu-toggle').click()
 assert q.locator('html').get_attribute('data-site-theme')==mode
try:
 with sync_playwright() as p:
  b=p.chromium.launch(executable_path=os.environ.get('ARMAGHAN_CHROMIUM_BIN','/usr/bin/chromium'),args=['--no-sandbox'])
  c=b.new_context(viewport={'width':1440,'height':900},color_scheme='light',reduced_motion='reduce');q=c.new_page();q.on('pageerror',lambda e:errors.append(str(e)))
  q.goto(base+'/');dismiss(q);assert q.locator('html').get_attribute('data-site-theme')=='dark','A first visit must stay dark even with a light OS preference.'
  for mode in ['light','mono','dark']:
   select(q,mode);assert q.evaluate('localStorage.getItem("armaghan-display-mode")')==mode
   q.reload();assert q.locator('html').get_attribute('data-site-theme')==mode
   q.goto(base+'/media/videos/');assert q.locator('html').get_attribute('data-site-theme')==mode;assert q.locator('[data-appearance-toggle]').get_attribute('aria-checked')==str(mode!='dark').lower()
   assert all(v=='true' for v in q.locator('[data-appearance-mono]').evaluate_all('(els)=>els.map(e=>e.getAttribute("aria-pressed"))')) if mode=='mono' else True
  q.locator('[data-appearance-toggle]').focus();q.keyboard.press('Space');assert q.locator('html').get_attribute('data-site-theme')=='light';q.keyboard.press('Space');assert q.locator('html').get_attribute('data-site-theme')=='dark'
  assert q.locator('.appearance-options,[data-appearance-switch] summary').count()==0,'The switch must never open a selection menu.'
  for mode in ['dark','light','mono']:
   q.evaluate('(mode)=>localStorage.setItem("armaghan-display-mode",mode)',mode)
   for width in [1440,390]:
    q.set_viewport_size({'width':width,'height':900})
    for route in routes:
     res=q.goto(base+route,wait_until='domcontentloaded');q.evaluate('document.fonts.ready');dismiss(q)
     assert res.status==200,(route,res.status);assert q.locator('h1').count()==1,route;assert not q.evaluate('document.documentElement.scrollWidth>innerWidth'),(mode,width,route)
     assert q.locator('html').get_attribute('data-site-theme')==mode;assert q.locator('[data-appearance-toggle]').is_visible()
     assert q.locator('.header-inquiry').get_attribute('aria-label'),'Compact mobile inquiry must retain its accessible name.'
     if mode!='dark':assert q.locator('body').evaluate('e=>getComputedStyle(e).backgroundColor')=='rgb(255, 255, 255)'
     assert q.locator('.header-inquiry').evaluate('e=>getComputedStyle(e).borderRadius')=='8px'
     records.append({'mode':mode,'width':width,'route':route})
    q.goto(base+'/media/videos/');dismiss(q);q.screenshot(path=str(out/f'{"preview-" if a.preview else ""}switch-{mode}-{width}.png'))
    if width==390:
     select(q,'light');select(q,'mono');select(q,'dark');select(q,mode)
     q.locator('.menu-toggle').click();assert q.locator('#site-nav').is_visible();assert q.locator('#site-nav .sub-menu').is_visible();q.locator('#site-nav .sub-menu a').last.click();q.wait_for_load_state('domcontentloaded');assert q.locator('html').get_attribute('data-site-theme')==mode
  for width in [320,768,1024,1150,2560]:
   q.set_viewport_size({'width':width,'height':900});q.goto(base+'/');dismiss(q);assert not q.evaluate('document.documentElement.scrollWidth>innerWidth'),width
   assert q.locator('[data-appearance-switch]').evaluate('e=>{const r=e.getBoundingClientRect();return r.left>=0&&r.right<=innerWidth}'),width
   select(q,'light');select(q,'dark')
  q.goto(base+'/contact/');dismiss(q);q.locator('[name=name]').fill('بررسی ظاهر');q.locator('[name=mobile]').fill('09120000000');q.locator('[name=notes]').fill('بررسی حفظ متن هنگام تغییر ظاهر');q.locator('[name=consent]').check()
  for mode in ['light','mono','dark']:
   select(q,mode);assert q.locator('[name=name]').input_value()=='بررسی ظاهر';assert q.locator('[name=notes]').input_value()=='بررسی حفظ متن هنگام تغییر ظاهر';assert q.locator('[name=consent]').is_checked()
  q.evaluate('localStorage.setItem("armaghan-display-mode","invalid")');q.reload();assert q.locator('html').get_attribute('data-site-theme')=='dark'
  q.evaluate('window.dispatchEvent(new StorageEvent("storage",{key:"armaghan-display-mode",newValue:"light"}))');assert q.locator('html').get_attribute('data-site-theme')=='light'
  c.close()
  blocked=b.new_context();blocked.add_init_script("Object.defineProperty(window,'localStorage',{get(){throw new DOMException('Storage blocked','SecurityError');}})");n=blocked.new_page();n.on('pageerror',lambda e:errors.append(str(e)));n.goto(base+'/media/videos/');dismiss(n);select(n,'light');assert n.locator('html').get_attribute('data-site-theme')=='light';blocked.close()
  nojs=b.new_context(java_script_enabled=False);n=nojs.new_page();n.goto(base+'/media/videos/');assert not n.locator('[data-appearance-switch]').is_visible();assert n.locator('h1').count()==1;nojs.close()
  assert not errors,errors;b.close()
finally:
 if server:server.shutdown()
(out/('preview-layouts.json' if a.preview else 'native-layouts.json')).write_text(json.dumps({'views':records,'errors':errors},ensure_ascii=False,indent=2))
print(json.dumps({'views':len(records),'errors':errors,'checks':['Dark first visit despite OS preference','Direct sliding toggle and independent monochrome button','Reload and cross-page persistence; keyboard Space and cross-tab preference','Responsive switch and native media submenu','White page surfaces and eight-pixel controls','Form contents retained during appearance changes','Blocked storage, invalid preference and no-JS fallback']}))
