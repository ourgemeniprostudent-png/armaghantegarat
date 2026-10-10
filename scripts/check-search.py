#!/usr/bin/env python3
"""Check search usability and public results in WordPress and an optional exact export."""
import argparse,json,os
from functools import partial
from http.server import SimpleHTTPRequestHandler,ThreadingHTTPServer
from pathlib import Path
from threading import Thread
from urllib.parse import urlencode,urlsplit
from playwright.sync_api import sync_playwright

root=Path(__file__).resolve().parent.parent
parser=argparse.ArgumentParser(description=__doc__);parser.add_argument('--preview',type=Path);args=parser.parse_args()
native=(root/'.runtime/site-url.txt').read_text().strip().rstrip('/')
out=root/'.runtime/search-checks';out.mkdir(exist_ok=True)
class Handler(SimpleHTTPRequestHandler):
 def log_message(self,*args):pass
server=None;origins=[('native',native)]
if args.preview:
 server=ThreadingHTTPServer(('127.0.0.1',0),partial(Handler,directory=str(args.preview.resolve())))
 Thread(target=server.serve_forever,daemon=True).start();origins.append(('preview',f'http://127.0.0.1:{server.server_port}'))
queries=[{'q':'قهوه'},{'q':'قهوه','scope':'products'},{'q':'قهوه','scope':'articles'},{'q':'قهوه','scope':'media'},{'q':'همكاري'},{'q':'قهوه کیفیت'},{'q':'تامین'},{'q':'تامین','result_page':'2'},{'q':'ابجدناموجود۹۸۷۶'},{'q':'   '}]
errors=[];reports=[];signatures={}
try:
 with sync_playwright() as p:
  browser=p.chromium.launch(executable_path=os.environ.get('ARMAGHAN_CHROMIUM_BIN','/usr/bin/chromium'),args=['--no-sandbox'])
  for label,origin in origins:
   for width in [1440,390,320]:
    for mode in ['dark','light','mono']:
     context=browser.new_context(viewport={'width':width,'height':900},reduced_motion='reduce')
     context.add_init_script('localStorage.setItem("armaghan-display-mode",'+json.dumps(mode)+');')
     page=context.new_page();page.on('pageerror',lambda error:errors.append(str(error)))
     page.goto(origin+'/search/');page.evaluate('document.fonts.ready')
     if label=='preview':page.wait_for_function('document.querySelector(".ag-search-examples") && document.querySelector(".ag-search-count").textContent === ""')
     if page.locator('[data-event-choice=no]').is_visible():page.locator('[data-event-choice=no]').click()
     geometry=page.evaluate('''() => {
      const rect=s=>document.querySelector(s).getBoundingClientRect();
      const color=s=>getComputedStyle(document.querySelector(s)).color;
      return {headerBottom:rect('.site-header').bottom,pathTop:rect('.breadcrumb').top,inputTop:rect('#ag-search-input').top,inputBottom:rect('#ag-search-input').bottom,overflow:document.documentElement.scrollWidth>innerWidth,headerInk:color('.brand-name'),paper:getComputedStyle(document.querySelector('.ag-search-route')).backgroundColor,wrap:rect('.ag-search-opening>.wrap').width,headerWrap:rect('.header-inner').width,inputInk:color('#ag-search-input')};
     }''')
     assert not geometry['overflow'],(label,width,mode,geometry)
     assert geometry['pathTop']>geometry['headerBottom'],(label,width,mode,geometry)
     assert geometry['inputBottom']<820,(label,width,mode,geometry)
     assert abs(geometry['wrap']-geometry['headerWrap'])<1,(label,width,mode,geometry)
     def rgb(value):return [float(v) for v in value.removeprefix('rgb(').removesuffix(')').split(',')]
     def luminance(value):
      c=[v/255 for v in rgb(value)];c=[v/12.92 if v<=.04045 else ((v+.055)/1.055)**2.4 for v in c];return .2126*c[0]+.7152*c[1]+.0722*c[2]
     values=sorted([luminance(geometry['headerInk']),luminance(geometry['paper'])]);contrast=(values[1]+.05)/(values[0]+.05)
     assert contrast>=4.5,(label,width,mode,'header contrast',contrast)
     page.screenshot(path=str(out/f'{label}-{mode}-{width}.png'),full_page=True)
     if width<=390:
      page.locator('.menu-toggle').click();assert page.locator('#site-nav').is_visible();assert page.locator('.menu-toggle').get_attribute('aria-expanded')=='true';page.locator('.menu-toggle').click()
     page.evaluate('scrollTo(0,400)');page.wait_for_function('document.querySelector(".site-header").classList.contains("is-glass")')
     page.evaluate('scrollTo(0,0)');page.wait_for_function('!document.querySelector(".site-header").classList.contains("is-glass")')
     reports.append({'origin':label,'width':width,'mode':mode,'header_contrast':round(contrast,2),'geometry':geometry})
     context.close()
   context=browser.new_context(viewport={'width':1440,'height':900},reduced_motion='reduce');page=context.new_page();page.on('pageerror',lambda error:errors.append(str(error)))
   for query in queries:
    page.goto(origin+'/search/?'+urlencode(query));page.evaluate('document.fonts.ready')
    if label=='preview':
     page.wait_for_function('''() => {const q=new URLSearchParams(location.search).get('q').trim();return q?document.querySelector('.ag-search-count').textContent.includes('نتیجه'):document.querySelector('.ag-search-count').textContent==='';}''')
    signature=page.evaluate('''() => ({count:document.querySelector('.ag-search-count').textContent,results:[...document.querySelectorAll('.ag-search-result')].map(e=>({title:e.querySelector('h3 a').childNodes[0].textContent.trim(),kind:e.querySelector('.eyebrow').textContent})),empty:!document.querySelector('[data-search-empty]').hidden})''')
    if query.get('scope')=='products':assert signature['results'] and all(r['kind'] in ['گروه محصول','محصول منتشرشده'] for r in signature['results']),signature
    if query.get('scope')=='articles':assert signature['results'] and all(r['kind']=='یادداشت تجارت' for r in signature['results']),signature
    if query['q']=='ابجدناموجود۹۸۷۶':assert signature['empty'] and not signature['results'],signature
    if query['q']=='همكاري':assert signature['results'],signature
    if query['q']=='تامین' and 'result_page' not in query:assert len(signature['results'])==12 and page.locator('.ag-pagination a').count()>0,signature
    key=json.dumps(query,sort_keys=True,ensure_ascii=False)
    if label=='native':signatures[key]=signature
    else:assert signature==signatures[key],(query,signature,signatures[key])
   page.goto(origin+'/search/');page.locator('#ag-search-input').fill('قهوه');page.locator('#ag-search-scope').select_option('products');page.locator('.ag-search-form .button').click()
   page.wait_for_function('document.querySelector(".ag-search-count").textContent.includes("نتیجه")');assert page.locator('.ag-search-result').count()>0
   page.locator('.ag-search-examples a',has_text='برنج').click();page.wait_for_function('document.querySelector(".ag-search-count").textContent.includes("برنج")')
   page.go_back();page.wait_for_function('document.querySelector(".ag-search-count").textContent.includes("قهوه")')
   context.close()
  # Native GET forms and public results remain usable when JavaScript is disabled.
  context=browser.new_context(java_script_enabled=False);page=context.new_page();page.goto(native+'/search/');page.locator('#ag-search-input').fill('قهوه');page.locator('#ag-search-scope').select_option('products');page.locator('.ag-search-form .button').click();assert page.locator('.ag-search-result').count()>0;context.close()
  if args.preview:
   context=browser.new_context();context.route('**/public-index.json',lambda route:route.abort());page=context.new_page();page.on('pageerror',lambda error:errors.append(str(error)));page.goto(origins[-1][1]+'/search/');page.get_by_text('جستجوی پیش‌نمایش بارگذاری نشد؛ صفحه را دوباره باز کنید.').wait_for(state='visible');context.close()
  browser.close()
finally:
 if server:server.shutdown()
assert not errors,errors
result={'views':reports,'queries':len(queries)*len(origins),'native_static_parity':bool(args.preview),'native_no_js':True,'errors':errors}
(out/'results.json').write_text(json.dumps(result,ensure_ascii=False,indent=2)+'\n')
print(json.dumps({'views':len(reports),'queries':result['queries'],'native_static_parity':bool(args.preview),'native_no_js':True,'errors':errors}))
