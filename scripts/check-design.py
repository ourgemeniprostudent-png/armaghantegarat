from playwright.sync_api import sync_playwright
from pathlib import Path
import json
root=Path(__file__).resolve().parent.parent
base=(root/'.runtime/site-url.txt').read_text().strip();out=root/'.runtime/design-checks';out.mkdir(exist_ok=True)
routes=['/','/products/','/products/coffee/','/products/rice/','/products/dried-fruits/','/products/spices/','/products/legumes/','/solutions/','/solutions/b2b-supply/','/about/','/contact/','/inquiry/','/media/','/blog/','/faq/','/privacy/','/search/','/thank-you/','/blog/2026/10/06/coffee-inquiry-guide/','/blog/2026/10/06/business-inquiry-checklist/','/blog/2026/10/06/follow-up-your-inquiry/']
report=[]
with sync_playwright() as p:
 browser=p.chromium.launch(executable_path='/usr/bin/chromium',headless=True,args=['--no-sandbox'])
 for width,height in [(1440,900),(390,844)]:
  context=browser.new_context(viewport={'width':width,'height':height},reduced_motion='reduce')
  page=context.new_page();errors=[];http=[]
  page.on('pageerror',lambda e:errors.append(str(e)))
  page.on('response',lambda r:http.append([r.status,r.url]) if r.status>=400 else None)
  for route in routes:
   response=page.goto(base+route,wait_until='networkidle');page.evaluate('document.fonts.ready');assert response.status==200,(route,response.status)
   assert page.locator('h1').count()==1,(route,'heading')
   assert page.locator('html').get_attribute('dir')=='rtl'
   overflow=page.evaluate('document.documentElement.scrollWidth > innerWidth+2')
   if overflow: print('OVERFLOW',width,route,page.evaluate('document.documentElement.scrollWidth'))
   missing=page.locator('img').evaluate_all('(imgs)=>imgs.filter(i=>i.complete && i.naturalWidth===0 && i.getAttribute("src")).map(i=>i.src)')
   assert not missing,(route,missing)
   report.append({'route':route,'width':width,'overflow':overflow})
   if route in ['/about/','/products/coffee/','/inquiry/','/solutions/','/blog/']:
    page.locator('img[loading=lazy]').evaluate_all('(imgs)=>imgs.forEach(i=>i.loading="eager")');page.wait_for_timeout(300);page.screenshot(path=str(out/(str(width)+'-'+route.strip('/').replace('/','-')+'.png')),full_page=True)
  assert not errors,errors;assert not http,http
  page.goto(base+'/inquiry/?category=coffee&product=آزمایش',wait_until='networkidle')
  assert page.input_value('#vatan-category')=='coffee';assert page.input_value('#vatan-product')=='آزمایش'
  page.fill('#vatan-quantity','۲۰ کیلوگرم');page.fill('#vatan-notes','درخواست آزمایشی؛ ارسال واقعی در آزمون جدا بررسی می‌شود.');page.locator('[data-form-next]').click()
  assert page.locator('[data-form-step="1"]').is_visible()
  page.fill('#vatan-name','بررسی توسعه');page.fill('#vatan-mobile','۰۹۱۲۱۲۳۴۵۶۷');page.fill('#vatan-city','تهران');page.select_option('#vatan-customer_type','wholesale');page.locator('[data-form-next]').click()
  assert page.locator('[data-form-review]').is_visible();assert '۲۰ کیلوگرم' in page.locator('[data-form-review]').inner_text()
  page.locator('[data-form-back]').click();assert page.input_value('#vatan-mobile')=='۰۹۱۲۱۲۳۴۵۶۷';page.locator('[data-form-next]').click()
  page.screenshot(path=str(out/(str(width)+'-form-review.png')),full_page=False)
  page.goto(base+'/faq/',wait_until='networkidle');page.fill('[data-faq-filter]','پیگیری');assert page.locator('.ag-faq:visible').count()>0
  page.fill('[data-faq-filter]','zzzzzzzz');assert page.locator('.ag-filter-empty').is_visible()
  page.goto(base+'/search/?q=خشکبار',wait_until='networkidle');assert page.locator('.ag-search-result a[href$="/products/dried-fruits/"]').count()==1
  page.goto(base+'/about/',wait_until='networkidle');page.locator('[data-ag-zoom]').first.click();assert page.locator('.ag-zoom-dialog').evaluate('(d)=>d.open');page.keyboard.press('Escape');assert not page.locator('.ag-zoom-dialog').evaluate('(d)=>d.open')
  if width<980:
   page.locator('.menu-toggle').click();assert page.locator('#site-nav').get_attribute('aria-hidden')=='false';page.keyboard.press('Escape');assert page.locator('.menu-toggle').get_attribute('aria-expanded')=='false'
  context.close()
 browser.close()
out.joinpath('routes.json').write_text(json.dumps(report,ensure_ascii=False,indent=2))
print('Verified',len(report),'desktop/mobile route views, form navigation/review, FAQ filtering, public section/category search, image dialog and mobile menu')
