#!/usr/bin/env python3
"""Compare exported SVG rendering with native WordPress, without submitting forms."""
import argparse,json,os
from functools import partial
from http.server import SimpleHTTPRequestHandler,ThreadingHTTPServer
from pathlib import Path
from threading import Thread
from PIL import Image,ImageChops
from playwright.sync_api import sync_playwright

root=Path(__file__).resolve().parent.parent
parser=argparse.ArgumentParser(description=__doc__)
parser.add_argument('--preview',type=Path,required=True)
args=parser.parse_args();preview=args.preview.resolve()
routes=[entry['route'] for entry in json.loads((preview/'preview-manifest.json').read_text())['routes']]
native=(root/'.runtime/site-url.txt').read_text().strip().rstrip('/')
out=root/'.runtime/preview-vector-checks';out.mkdir(exist_ok=True)
class Handler(SimpleHTTPRequestHandler):
 def log_message(self,*args):pass
server=ThreadingHTTPServer(('127.0.0.1',0),partial(Handler,directory=str(preview)))
Thread(target=server.serve_forever,daemon=True).start()
exported=f'http://127.0.0.1:{server.server_port}'
signature=r'''() => {
 const attributes=['d','points','transform','viewBox','cx','cy','r','rx','ry','x','y','x1','y1','x2','y2','width','height','gradientUnits','gradientTransform','offset','stop-color','stop-opacity'];
 const normalize=value=>value.replace(/url\(["']?.*?#([^"'()]+)["']?\)/g,'url(#$1)');
 const visit=e=>{
  const style=getComputedStyle(e);
  return {tag:e.localName,namespace:e.namespaceURI,geometry:Object.fromEntries(attributes.filter(a=>e.hasAttribute(a)).map(a=>[a,e.getAttribute(a)])),paint:Object.fromEntries(['fill','stroke','stroke-width','stroke-linecap','stroke-linejoin','opacity','stop-color','stop-opacity'].map(a=>[a,normalize(style.getPropertyValue(a))])),children:[...e.children].map(visit)};
 };
 return [...document.querySelectorAll('svg')].filter(e=>!e.parentElement.closest('svg')).map(visit);
}'''
records=[];errors=[]
try:
 with sync_playwright() as p:
  browser=p.chromium.launch(executable_path=os.environ.get('ARMAGHAN_CHROMIUM_BIN','/usr/bin/chromium'),args=['--no-sandbox'])
  context=browser.new_context(viewport={'width':1440,'height':900},reduced_motion='reduce')
  pages=[context.new_page(),context.new_page()]
  for page in pages:page.on('pageerror',lambda error:errors.append(str(error)))
  for route in routes:
   results=[]
   for page,origin in zip(pages,[native,exported]):
    response=page.goto(origin+route,wait_until='domcontentloaded');assert response.status==200,(origin,route)
    results.append(page.evaluate(signature))
   if results[0]!=results[1]:
    (out/'mismatch.json').write_text(json.dumps({'route':route,'native':results[0],'preview':results[1]},ensure_ascii=False,indent=2))
    raise AssertionError('Exported SVG hierarchy or paint differs from WordPress: '+route)
   records.append({'route':route,'vectors':len(results[0])})
  for width in [1440,390]:
   for mode in ['dark','light']:
    images=[]
    for page,origin,label in zip(pages,[native,exported],['native','preview']):
     page.set_viewport_size({'width':width,'height':900})
     page.goto(origin+'/media/videos/');page.evaluate('(mode)=>localStorage.setItem("armaghan-display-mode",mode)',mode);page.reload();page.evaluate('document.fonts.ready')
     if page.locator('[data-event-choice=no]').is_visible():page.locator('[data-event-choice=no]').click()
     image=out/f'switch-{label}-{mode}-{width}.png'
     page.locator('[data-appearance-switch]').screenshot(path=str(image));images.append(Image.open(image).convert('RGB'))
    assert images[0].size==images[1].size
    assert ImageChops.difference(*images).getbbox() is None,('Exported switch pixels differ',mode,width)
  assert not errors,errors
  browser.close()
finally:server.shutdown()
(out/'results.json').write_text(json.dumps({'source_commit':json.loads((preview/'preview-manifest.json').read_text())['source_commit'],'routes':records,'switch_pixel_comparisons':4,'errors':errors},ensure_ascii=False,indent=2))
print(json.dumps({'routes':len(records),'vectors':sum(r['vectors'] for r in records),'switch_pixel_comparisons':4,'errors':errors}))
