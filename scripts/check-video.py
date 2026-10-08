#!/usr/bin/env python3
"""Read-only browser checks: immediate page access and progressive original-video playback."""
from pathlib import Path
from playwright.sync_api import sync_playwright
from urllib.request import Request,urlopen
import hashlib,json,os

root=Path(__file__).resolve().parent.parent
base=(root/'.runtime/site-url.txt').read_text().strip()
out=root/'.runtime/video-checks';out.mkdir(exist_ok=True)
expected={'hero-h264.mp4':'f7ae6d349f977be82939382e521f8e36597d90cceac6393ba7772627596f8f6c',
          'hero-av1.mp4':'b48c6cf207b16ddfd183eac4f7d6263067472f942dc1dfb9004270450c97eec5'}
for name,digest in expected.items():
 with (root/'theme/vatan-authority/assets/media'/name).open('rb') as stream:
  assert hashlib.file_digest(stream,'sha256').hexdigest()==digest,name
video_url=base+'/wp-content/themes/vatan-authority/assets/media/hero-h264.mp4'
with urlopen(Request(video_url,headers={'Range':'bytes=0-1023'}),timeout=20) as response:
 assert response.status==206 and len(response.read())==1024

report=[];errors=[]
with sync_playwright() as p:
 browser=p.chromium.launch(executable_path=os.environ.get('ARMAGHAN_CHROMIUM_BIN','/usr/bin/chromium'),args=['--no-sandbox'])
 for width,height in [(1440,900),(390,844)]:
  context=browser.new_context(viewport={'width':width,'height':height},reduced_motion='no-preference')
  page=context.new_page();page.on('pageerror',lambda error:errors.append(str(error)))
  cdp=context.new_cdp_session(page);cdp.send('Network.enable');cdp.send('Network.setCacheDisabled',{'cacheDisabled':True})
  received=[0];ids=set()
  cdp.on('Network.responseReceived',lambda event:ids.add(event['requestId']) if '/assets/media/hero-' in event['response']['url'] and '.mp4' in event['response']['url'] else None)
  cdp.on('Network.dataReceived',lambda event:received.__setitem__(0,received[0]+event['dataLength']) if event['requestId'] in ids else None)
  cdp.send('Network.emulateNetworkConditions',{'offline':False,'latency':80,'downloadThroughput':512000,'uploadThroughput':128000})
  page.goto(base+'/',wait_until='domcontentloaded')
  assert page.locator('[data-video-loader]').count()==0
  assert page.locator('main').is_visible() and page.locator('.site-header').is_visible()
  assert not page.locator('main').evaluate('element=>element.inert')
  assert not page.evaluate('document.documentElement.scrollWidth>innerWidth')
  page.wait_for_function('!!document.querySelector("[data-trade-hero]").dataset.firstPlayTime',timeout=30000)
  state=page.locator('[data-trade-hero]').evaluate('element=>({...element.dataset})')
  media=page.locator('[data-hero-video]').evaluate('v=>({src:v.currentSrc,duration:v.duration,currentTime:v.currentTime,muted:v.muted})')
  size=(root/'theme/vatan-authority/assets/media'/('hero-'+state['codec']+'.mp4')).stat().st_size
  assert 0<received[0]<size,(received[0],size)
  assert 0<float(state['firstPlayBuffered'])<media['duration']-10,state
  assert not media['src'].startswith('blob:') and media['muted']
  report.append({'width':width,'first_play_ms':int(state['firstPlayElapsed']),
                 'buffered_seconds_at_first_play':float(state['firstPlayBuffered']),
                 'received_bytes_at_check':received[0],'video_bytes':size,'codec':state['codec']})
  cdp.send('Network.emulateNetworkConditions',{'offline':False,'latency':0,'downloadThroughput':-1,'uploadThroughput':-1})
  if page.locator('[data-event-choice=no]').is_visible():page.locator('[data-event-choice=no]').click()
  page.evaluate('document.fonts.ready');page.screenshot(path=str(out/f'{width}-home.png'))
  page.locator('#home-about').scroll_into_view_if_needed();page.wait_for_function('document.querySelector("[data-hero-video]").paused')
  page.evaluate('scrollTo(0,0)');page.wait_for_function('!document.querySelector("[data-hero-video]").paused')
  if width<980:
   page.locator('.menu-toggle').click();assert page.locator('#site-nav').get_attribute('aria-hidden')=='false'
   page.keyboard.press('Escape')
  page.emulate_media(reduced_motion='reduce');page.wait_for_function('!document.querySelector("[data-hero-video]").getAttribute("src")')
  assert page.locator('main').is_visible()
  context.close()
 # Reduced motion, data saving and no-JS never block navigation or download hero video.
 for mode in ['reduce','save-data','no-js']:
  context=browser.new_context(reduced_motion='reduce' if mode=='reduce' else 'no-preference',java_script_enabled=mode!='no-js')
  if mode=='save-data':context.add_init_script('Object.defineProperty(navigator,"connection",{value:{saveData:true,addEventListener(){}}})')
  page=context.new_page();requested=[];page.on('request',lambda r:requested.append(r.url) if '/assets/media/hero-' in r.url and '.mp4' in r.url else None)
  page.goto(base+'/',wait_until='networkidle');assert page.locator('main').is_visible()
  assert page.locator('[data-video-loader]').count()==0 and not requested,(mode,requested)
  context.close()
 # A failed preferred codec tries the original H.264 source, then returns to the poster on total failure.
 context=browser.new_context(reduced_motion='no-preference')
 context.add_init_script('navigator.mediaCapabilities.decodingInfo=async()=>({supported:true,smooth:true,powerEfficient:true})')
 page=context.new_page();page.route('**/hero-av1.mp4*',lambda route:route.abort())
 page.goto(base+'/',wait_until='domcontentloaded');page.wait_for_function('!!document.querySelector("[data-trade-hero]").dataset.firstPlayTime',timeout=20000)
 assert page.locator('[data-trade-hero]').get_attribute('data-codec')=='h264'
 context.close()
 context=browser.new_context(reduced_motion='no-preference');page=context.new_page()
 page.route('**/assets/media/hero-*.mp4*',lambda route:route.abort())
 page.goto(base+'/',wait_until='networkidle');page.wait_for_function('document.querySelector("[data-trade-hero]").dataset.loadError==="unavailable"')
 assert page.locator('main').is_visible() and not page.locator('[data-hero-video]').get_attribute('src')
 context.close();assert not errors,errors;browser.close()
(out/'results.json').write_text(json.dumps({'throttled_playback':report,'original_hashes':expected,
 'reduced_motion':True,'save_data':True,'no_js':True,'codec_fallback':True,'network_failure_poster':True,'scroll_pause_resume':True,'errors':errors},indent=2))
print(json.dumps({'progressive_playback':report,'fallbacks':'passed','original_video_hashes':'unchanged'},ensure_ascii=False))
