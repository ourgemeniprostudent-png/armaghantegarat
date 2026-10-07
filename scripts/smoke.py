#!/usr/bin/env python3
"""Read-only release checks for the transferred development site."""
import json,re,sys
from pathlib import Path
from urllib.request import urlopen,Request
root=Path(__file__).resolve().parent.parent
url=(root/'.runtime/site-url.txt').read_text().strip()
routes=['/','/products/','/products/coffee/','/products/rice/','/products/dried-fruits/','/products/spices/','/products/legumes/','/solutions/','/solutions/b2b-supply/','/thank-you/','/about/','/contact/','/inquiry/','/media/','/blog/','/faq/','/privacy/','/search/','/blog/2026/10/06/follow-up-your-inquiry/','/blog/2026/10/06/business-inquiry-checklist/','/blog/2026/10/06/coffee-inquiry-guide/']
results=[]
for route in routes:
 with urlopen(url+route,timeout=20) as r:
  page=r.read().decode();assert r.status==200,(route,r.status)
  if route=='/':
   assert all(s in page for s in ['home-about','home-manifesto','home-journal','home-contact','data-cooperation-flow','data-product-revolver']), 'Missing approved home sections'
   assert 'class="quote-section"' not in page and 'class="contact-band"' not in page,'Legacy lower home returned'
   assert '/Users/' not in page,'Machine-specific source URL leaked into output'
   lower=page[page.index('id="home-about"'):page.index('</main>')]
   assert not re.search(r'href="[^"]*/inquiry/',lower), 'Duplicate lower-home inquiry CTA'
  results.append({'route':route,'status':r.status})
for route in ['/wp-content/themes/vatan-authority/assets/interior.css','/wp-content/themes/vatan-authority/assets/interior.js','/wp-content/themes/vatan-authority/assets/home-story.css','/wp-content/themes/vatan-authority/assets/home-story.js','/wp-content/themes/vatan-authority/assets/cooperation/shipping.webp','/wp-content/themes/vatan-authority/assets/fonts/PeydaWebFaNum-Regular.woff2']:
 with urlopen(url+route,timeout=20) as r:assert r.status==200 and len(r.read())>0,route
for filename in ['hero-h264.mp4','hero-av1.mp4']:
 req=Request(url+'/wp-content/themes/vatan-authority/assets/media/'+filename,method='HEAD')
 with urlopen(req,timeout=20) as r:assert r.status==200 and int(r.headers['Content-Length'])>1000000,filename
try:
 with urlopen(url+'/en/',timeout=20) as r:assert r.status==404,'English pending site should stay hidden'
except Exception as exc:
 if getattr(exc,'code',None)!=404:raise
report={'site':url,'routes':results,'assets':'passed','pending_english':'404 as expected','home_design':'approved 1.7 layout, section editable','theme_version':'1.8.0'}
(root/'.runtime/smoke-report.json').write_text(json.dumps(report,ensure_ascii=False,indent=2))
print(f'Passed: {len(results)} public routes, CSS/JS/font/images/videos, homepage continuity, pending /en.')
