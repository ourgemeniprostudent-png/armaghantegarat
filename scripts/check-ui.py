#!/usr/bin/env python3
"""Read-only responsive audit of every published page and sample at five widths."""
from pathlib import Path
import json,subprocess,uuid
from playwright.sync_api import sync_playwright
r=Path(__file__).resolve().parent.parent;out=r/'.runtime/ui-checks';out.mkdir(exist_ok=True)
base=(r/'.runtime/site-url.txt').read_text().strip()
cmd=[__import__('os').environ.get('ARMAGHAN_PHP_BIN','php'),str(r/'vendor/wp-cli-2.12.0.phar'),'--allow-root','--path='+str(r/'.runtime/wordpress'),'--url='+base]
def wp(code):return subprocess.check_output(cmd+['eval',code],text=True)
records=json.loads(wp("$out=[];foreach(get_posts(['post_type'=>['page','post'],'post_status'=>'publish','numberposts'=>-1]) as $p)$out[]=get_permalink($p);foreach(get_terms(['taxonomy'=>'vatan_category','hide_empty'=>false]) as $t)$out[]=get_term_link($t);if(ag_media_show_samples())foreach(array_keys(ag_media_samples()) as $k)$out[]=ag_media_sample_url($k);echo wp_json_encode($out);"))
reports=[];failures=[]
with sync_playwright() as p:
 b=p.chromium.launch(executable_path='/usr/bin/chromium',args=['--no-sandbox'])
 for width in [360,390,768,1024,1440]:
  c=b.new_context(viewport={'width':width,'height':900},reduced_motion='reduce');page=c.new_page();errors=[];page.on('pageerror',lambda e:errors.append(str(e)))
  for url in records+[base+'/not-a-real-page/']:
   route=url.replace(base,'');errors.clear();response=page.goto(url,wait_until='networkidle')
   page.locator('[data-event-choice=no]').first.click() if page.locator('[data-event-choice=no]').count() and page.locator('[data-event-choice=no]').first.is_visible() else None
   page.locator('img').evaluate_all('els=>els.forEach(el=>el.loading="eager")');page.wait_for_function('Array.from(document.images).every(i=>!i.getAttribute("src")||i.complete)',timeout=15000)
   issues=page.evaluate('''()=>({overflow:document.documentElement.scrollWidth>innerWidth,h1:document.querySelectorAll('h1').length,broken:[...document.images].filter(i=>i.getAttribute('src')&&!i.closest('[hidden]')&&i.complete&&!i.naturalWidth).map(i=>i.src),unresolved:document.body.innerText.includes('{{'),wide:[...document.querySelectorAll('main *')].filter(e=>{const r=e.getBoundingClientRect();return r.width>0&&(r.right>innerWidth+2||r.left< -2)&&getComputedStyle(e).position!=='absolute'}).slice(0,8).map(e=>e.tagName+'.'+e.className)})''')
   expected=404 if 'not-a-real' in route else 200
   if response.status!=expected or issues['overflow'] or issues['h1']!=1 or issues['broken'] or issues['unresolved'] or errors:failures.append(dict(route=route,width=width,status=response.status,issues=issues,errors=list(errors)))
   reports.append(dict(route=route,width=width,status=response.status))
   if width in [390,1440]:page.screenshot(path=str(out/(str(width)+'-'+route.strip('/').replace('/','_')+'.png')),full_page=True)
  c.close()
 b.close()
(out/'audit.json').write_text(json.dumps(dict(views=reports,failures=failures),ensure_ascii=False,indent=2));print(json.dumps(dict(views=len(reports),failures=failures),ensure_ascii=False))

if failures:raise SystemExit(f"UI audit failed: {len(failures)} views.")
