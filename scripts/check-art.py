from pathlib import Path
from playwright.sync_api import sync_playwright
import json,re
root=Path(__file__).resolve().parent.parent;out=root/'.runtime/design-checks/art-final';out.mkdir(parents=True,exist_ok=True)
base=(root/'.runtime/site-url.txt').read_text().strip().rstrip('/')
report=[]
with sync_playwright() as p:
 browser=p.chromium.launch(executable_path='/usr/bin/chromium',headless=True,args=['--no-sandbox'])
 for width,height in [(1440,1000),(375,812)]:
  ctx=browser.new_context(viewport={'width':width,'height':height});page=ctx.new_page();errors=[];bad=[]
  page.on('pageerror',lambda e:errors.append(str(e)));page.on('response',lambda r:bad.append([r.status,r.url]) if r.status>=400 else None)
  for path in ['/about/','/contact/','/products/','/products/coffee/','/products/rice/','/products/spices/','/solutions/','/solutions/b2b-supply/','/inquiry/','/media/','/blog/','/faq/','/privacy/','/search/','/thank-you/']:
   page.goto(base+path,wait_until='networkidle');page.evaluate('document.fonts.ready');page.wait_for_timeout(1550)
   assert page.locator('h1').is_visible(),path
   assert page.evaluate('document.documentElement.scrollWidth <= innerWidth'),(path,width)
   page.locator('img[src]').evaluate_all('(imgs)=>imgs.forEach(i=>i.loading="eager")')
   page.wait_for_function('Array.from(document.images).filter(i=>i.getAttribute("src")).every(i=>i.complete&&i.naturalWidth>0)',timeout=10000)
   stage=page.locator('[data-ag-parallax]').first
   if stage.count(): assert 0<=float(stage.evaluate('(el)=>getComputedStyle(el).getPropertyValue("--art-progress")'))<=1
   if path=='/about/':
    page.screenshot(path=str(out/f'{width}-about-hero.png'))
    page.locator('#manifesto').scroll_into_view_if_needed();page.wait_for_timeout(1100)
    assert page.locator('.ag-manifesto-type').evaluate('(e)=>parseFloat(getComputedStyle(e).fontSize)') >= (90 if width>780 else 45)
    assert page.locator('.ag-manifesto-mark svg').is_visible()
    assert page.locator('.ag-ink-word').count()>=8
    assert page.locator('.ag-section-nav [aria-current]').count()==1
    assert page.locator('.ag-ink-word:not(.ag-unlit)').count()>0
    page.screenshot(path=str(out/f'{width}-about-manifesto.png'))
    page.locator('#trade-route').scroll_into_view_if_needed();page.wait_for_timeout(1100);page.screenshot(path=str(out/f'{width}-about-route.png'))
   if path=='/inquiry/':
    page.select_option('#vatan-category','coffee');page.fill('#vatan-quantity','۲۰ کیلوگرم')
    assert 'قهوه' in page.locator('[data-ag-form-summary]').inner_text()
    assert '۲۰ کیلوگرم' in page.locator('[data-ag-form-summary]').inner_text()
    page.locator('#form').scroll_into_view_if_needed();page.wait_for_timeout(1100);page.screenshot(path=str(out/f'{width}-inquiry-form.png'))
   if path in ['/blog/','/products/coffee/','/contact/']:
    page.screenshot(path=str(out/(f'{width}-'+path.strip('/').replace('/','-')+'-hero.png')))
   if path=='/blog/':
    page.locator('#articles').scroll_into_view_if_needed();page.wait_for_timeout(1100)
    assert page.locator('#articles').evaluate('(e)=>getComputedStyle(e).backgroundColor')=='rgb(242, 238, 230)'
    page.screenshot(path=str(out/f'{width}-blog-articles.png'))
   report.append({'path':path,'width':width,'motion':'enabled','overflow':False,'loaded_images':'passed'})
  for slug in ['coffee-inquiry-guide','business-inquiry-checklist','follow-up-your-inquiry']:
   page.goto(base+'/blog/2026/10/06/'+slug+'/',wait_until='networkidle');body=page.locator('[data-article-body]')
   words=len(body.inner_text().split());assert words>=900,(slug,words)
   assert page.locator('.ag-toc a').count()>=8
   assert body.evaluate('(e)=>getComputedStyle(e).color')=='rgb(32, 40, 32)'
   assert page.locator('.ag-art-article>.ag-section').evaluate('(e)=>getComputedStyle(e).backgroundColor')=='rgb(242, 238, 230)'
   assert page.evaluate('document.documentElement.scrollWidth <= innerWidth')
   if slug=='coffee-inquiry-guide':
    page.screenshot(path=str(out/f'{width}-article-hero.png'));body.scroll_into_view_if_needed();page.screenshot(path=str(out/f'{width}-article-body.png'))
   report.append({'path':slug,'width':width,'words':words,'toc':'passed','paper':'passed'})
  assert not errors,errors;assert not bad,bad
  ctx.close()
 # Reduced motion remains immediately readable, including when the preference changes live.
 page=browser.new_page(viewport={'width':390,'height':844},reduced_motion='reduce');page.goto(base+'/about/',wait_until='networkidle')
 assert not page.evaluate('document.documentElement.classList.contains("ag-art-motion")')
 assert not page.locator('.ag-ink-word.ag-unlit').count()
 assert page.locator('.ag-word-mask').first.evaluate('(e)=>getComputedStyle(e).display')=='inline-block'
 page.emulate_media(reduced_motion='no-preference');page.wait_for_timeout(100);assert page.evaluate('document.documentElement.classList.contains("ag-art-motion")')
 page.emulate_media(reduced_motion='reduce');assert not page.locator('.ag-ink-word.ag-unlit').count();page.close()
 # Without JS, all editorial sections and all form stages remain available.
 ctx=browser.new_context(java_script_enabled=False,viewport={'width':390,'height':844});page=ctx.new_page();page.goto(base+'/about/');assert page.locator('#manifesto h2').is_visible();assert not page.locator('.ag-waiting').count()
 page.goto(base+'/inquiry/');assert page.locator('[data-form-step]:visible').count()==3
 page.goto(base+'/blog/2026/10/06/coffee-inquiry-guide/');assert len(page.locator('[data-article-body]').inner_text().split())>=900
 ctx.close();browser.close()
(out/'report.json').write_text(json.dumps(report,ensure_ascii=False,indent=2))
print('Art edition passed: 36 desktop/375px views, real scroll motion, image loading, chapter navigation, live form brief, 3 long paper articles, TOC, reduced-motion changes and no-JS fallback')
