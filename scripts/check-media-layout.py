#!/usr/bin/env python3
"""Read-only channel/episode layout checks, including shared container edges and native submenu."""
import json,os,subprocess
from pathlib import Path
from playwright.sync_api import sync_playwright
root=Path(__file__).resolve().parent.parent;base=(root/'.runtime/site-url.txt').read_text().strip().rstrip('/');out=root/'.runtime/media-channels-checks';out.mkdir(exist_ok=True)
cmd=[os.environ.get('ARMAGHAN_PHP_BIN','php'),str(root/'vendor/wp-cli-2.12.0.phar'),'--allow-root','--path='+str(root/'.runtime/wordpress'),'--url='+base]
routes=json.loads(subprocess.check_output(cmd+['eval','echo wp_json_encode(ag_media_public_routes());'],text=True));routes=[base+'/media/']+routes
records=[]
with sync_playwright() as p:
 b=p.chromium.launch(executable_path=os.environ.get('ARMAGHAN_CHROMIUM_BIN','/usr/bin/chromium'),args=['--no-sandbox']);c=b.new_context(reduced_motion='reduce');q=c.new_page();errors=[];q.on('pageerror',lambda e:errors.append(str(e)))
 for width in [320,390,768,1440,2560]:
  q.set_viewport_size({'width':width,'height':900})
  for url in routes:
   if width not in [390,1440] and '/sample/' in url:continue
   response=q.goto(url,wait_until='domcontentloaded');q.evaluate('document.fonts.ready');q.wait_for_timeout(80)
   if q.locator('[data-event-choice=no]').is_visible():q.locator('[data-event-choice=no]').click()
   assert response.status==200,(url,response.status);assert q.locator('h1').count()==1,url;assert not q.locator('.ms-help,.ms-archive,.ms-videos').count(),url
   assert not q.evaluate('document.documentElement.scrollWidth>innerWidth'),('overflow',width,url)
   assert '<b>Warning</b>' not in q.content() and '<b>Fatal error</b>' not in q.content(),url
   edges=q.evaluate("""()=>{const edge=s=>{const e=document.querySelector(s),r=e.getBoundingClientRect(),c=getComputedStyle(e);return [r.left+parseFloat(c.paddingLeft),r.right-parseFloat(c.paddingRight)]};return {media:edge('.mh-shell'),header:edge('.site-header .wrap'),footer:edge('.site-footer .wrap')}}""")
   assert all(abs(a-b)<1 for a,b in zip(edges['media'],edges['footer'])),('footer alignment',width,url,edges)
   if width>680:assert all(abs(a-b)<1 for a,b in zip(edges['media'],edges['header'])),('header alignment',width,url,edges)
   assert q.locator('.mh-media').evaluate("el=>getComputedStyle(el).backgroundColor")!='rgb(255, 255, 255)'
   if width in [390,1440] and url in [base+'/media/',base+'/media/videos/',base+'/media/podcasts/',base+'/media/videos/sample/video-coffee/',base+'/media/podcasts/sample/coffee/']:
    q.screenshot(path=str(out/(url.removeprefix(base).strip('/').replace('/','-')+f'-{width}.png')),full_page=True)
   records.append({'url':url,'width':width,'edges':edges})
 q.set_viewport_size({'width':1440,'height':900});q.goto(base+'/media/');parent=q.locator('#site-nav a[href="'+base+'/media/"]');parent.focus();parent.press('Tab');assert q.locator('#site-nav a[href="'+base+'/media/videos/"]').evaluate('el=>el===document.activeElement');assert q.locator('#site-nav a[href="'+base+'/media/videos/"]').is_visible()
 q.set_viewport_size({'width':390,'height':844});q.goto(base+'/media/');q.locator('.menu-toggle').click();assert q.locator('#site-nav a[href="'+base+'/media/podcasts/"]').is_visible();q.locator('#site-nav a[href="'+base+'/media/videos/"]').click();q.wait_for_url('**/media/videos/');assert not q.locator('#site-nav').is_visible()
 q.goto(base+'/media/videos/?topic=coffee&q=قهوه');assert q.locator('.mh-video-card').count()==2;q.goto(base+'/media/podcasts/?q=ناموجود');assert q.locator('.ms-episode-row').count()==0
 q.goto(base+'/media/videos/sample/video-coffee/');q.locator('[data-media-mock]').click();assert q.locator('.mh-screen .ms-mock-status').is_visible();assert q.locator('.mh-watch-related a').count()>0
 nojs=b.new_context(java_script_enabled=False,viewport={'width':390,'height':844});n=nojs.new_page();n.goto(base+'/media/podcasts/?topic=coffee');assert n.locator('.ms-episode-row').count()==1;n.goto(base+'/media/videos/?topic=quality');assert n.locator('.mh-video-card').count()==2;nojs.close()
 assert not errors,errors;b.close()
(out/'layouts.json').write_text(json.dumps({'views':records,'errors':errors,'submenu':'desktop keyboard and mobile navigation passed','sample_filtering':'native GET and no-JS passed'},ensure_ascii=False,indent=2))
print(json.dumps({'views':len(records),'errors':errors,'checks':['Shared header/footer/content edges','Separate archives and watch/episode pages','Submenu keyboard and mobile navigation','Native GET and no-JS topic/search filtering','Mock video player disclosure']},ensure_ascii=False))
