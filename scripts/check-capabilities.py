#!/usr/bin/env python3
"""Development-only integration checks; create and clean only marked QA records."""
from pathlib import Path
from playwright.sync_api import sync_playwright
import json,subprocess,re,wave,struct,os
root=Path(__file__).resolve().parent.parent;out=root/'.runtime/capability-checks';out.mkdir(exist_ok=True);base=(root/'.runtime/site-url.txt').read_text().strip();php=os.environ.get('ARMAGHAN_PHP_BIN','php')
if not base.startswith(('http://127.0.0.1:','http://localhost:')) or not (root/'.runtime/access.txt').exists():raise SystemExit('Run only on the private development installation.')
cli=[php,str(root/'vendor/wp-cli-2.12.0.phar'),'--allow-root','--path='+str(root/'.runtime/wordpress'),'--url='+base]
def wp(code):return subprocess.check_output(cli+['eval',code],text=True).strip()
if wp('echo get_option("vatan_notify_email","");'):raise SystemExit('Development email notifications must be off for this test.')
# Only records with this test's explicit marker are deleted at the end.
fixture_code=r'''
$ids=[];$uploads=wp_upload_dir();$assets=get_template_directory().'/assets/products/';
foreach(['coffee','rice'] as $name){$file=$uploads['path'].'/qa-capability-'.$name.'.webp';copy($assets.$name.'.webp',$file);$id=wp_insert_attachment(['post_title'=>'QA capability image','post_mime_type'=>'image/webp','post_status'=>'inherit','meta_input'=>['_qa_capability'=>'1']],$file);require_once ABSPATH.'wp-admin/includes/image.php';wp_update_attachment_metadata($id,wp_generate_attachment_metadata($id,$file));$ids[]=$id;}
$products=[];foreach(vatan_core_categories() as $slug=>$name){$id=wp_insert_post(['post_type'=>'vatan_product','post_status'=>'publish','post_name'=>'qa-capability-'.$slug,'post_title'=>'QA محصول '.$name,'post_content'=>'<h2>مشخصات نمونه آزمایشی</h2><p>این رکورد فقط برای آزمون است و منتشر نخواهد ماند.</p>','post_excerpt'=>'نمونه آزمایشی','meta_input'=>['_qa_capability'=>'1','_vatan_specs'=>"اندازه | متوسط\nشکل | دانه",'_vatan_gallery'=>$ids]]);wp_set_object_terms($id,[$slug],'vatan_category');set_post_thumbnail($id,$ids[0]);$products[$slug]=['id'=>$id,'url'=>get_permalink($id)];}
$ep=wp_insert_post(['post_type'=>'vatan_episode','post_status'=>'publish','post_name'=>'qa-capability-episode','post_title'=>'QA گفتگو','post_excerpt'=>'آزمون رسانه','post_content'=>'<h2>موضوع آزمون</h2><p>شرح آزمون</p>','meta_input'=>['_qa_capability'=>'1','_vatan_audio'=>home_url('/wp-content/uploads/qa-capability.wav'),'_vatan_video'=>get_template_directory_uri().'/assets/media/hero-h264.mp4','_vatan_transcript'=>'متن واقعی نمونه برای آزمون فنی پخش و جستجو.','_vatan_timestamps'=>"00:01 | بخش دوم",'_vatan_featured'=>true,'_vatan_duration'=>'۲ دقیقه','_vatan_related'=>[$products['coffee']['id']]]]);wp_set_object_terms($ep,['coffee'],'vatan_topic');set_post_thumbnail($ep,$ids[0]);
$options=[];foreach(['vatan_whatsapp','vatan_analytics_enabled','vatan_features'] as $key)$options[$key]=get_option($key,null);update_option('vatan_whatsapp','989120000000');update_option('vatan_analytics_enabled',true);
$day=gmdate('Ymd');$counts=[];foreach(vatan_event_names() as $event){$key='vatan_event_'.$day.'_'.$event;$counts[$key]=get_option($key,null);}
echo wp_json_encode(['products'=>$products,'episode'=>['id'=>$ep,'url'=>get_permalink($ep)],'images'=>$ids,'options'=>$options,'counts'=>$counts,'lead_before'=>wp_count_posts('vatan_lead')->private]);
'''
state=json.loads(wp(fixture_code));(out/'fixture-state.json').write_text(json.dumps(state));reports=[]
audio=root/'.runtime/wordpress/wp-content/uploads/qa-capability.wav'
with wave.open(str(audio),'wb') as f:f.setnchannels(1);f.setsampwidth(2);f.setframerate(8000);f.writeframes(struct.pack('<h',0)*24000)
try:
 with sync_playwright() as p:
  b=p.chromium.launch(executable_path=os.environ.get('ARMAGHAN_CHROMIUM_BIN','/usr/bin/chromium'),args=['--no-sandbox']);ctx=b.new_context(viewport={'width':1440,'height':950},reduced_motion='reduce');page=ctx.new_page();errors=[];page.on('pageerror',lambda e:errors.append(str(e)))
  for width in ([] if os.environ.get('QA_FAST') else [360,390,768,1024,1440]):
   page.set_viewport_size({'width':width,'height':900})
   for route in ['/','/contact/','/blog/','/media/','/inquiry/',*['/products/'+c+'/' for c in state['products']],*['/blog/2026/10/08/'+g['slug']+'/' for g in json.loads((root/'content/editorial/guides-fa.json').read_text())],*['/products/'+c+'/qa-capability-'+c+'/' for c in state['products']],'/media/podcast/qa-capability-episode/']:
    response=page.goto(base+route,wait_until='networkidle');assert response.status==200,(route,response.status)
    assert not page.evaluate('document.documentElement.scrollWidth>innerWidth'),('overflow',width,route)
    assert page.locator('h1').count()==1,('h1',route)
    reports.append({'width':width,'route':route})
   for route in ['/contact/','/blog/','/inquiry/']:
    if width in [390,1024,1440]:page.goto(base+route,wait_until='networkidle');page.screenshot(path=str(out/f'{width}-{route.strip("/")}.png'),full_page=True)
  page.set_viewport_size({'width':1440,'height':950})
  page.goto(base+'/contact/');assert page.locator('iframe[src*="maps"]').count()==0
  page.route('**/maps.google.com/**',lambda route:route.fulfill(status=200,body='<p>Map fixture</p>',content_type='text/html'))
  page.locator('[data-map-load]').click();assert '35.7294807434082' in page.locator('iframe').get_attribute('src');assert '51.436744689941406' in page.locator('iframe').get_attribute('src')
  href=page.locator('[data-whatsapp]').first.get_attribute('href');assert 'text=' in href and '%D8' in href
  page.goto(base+'/blog/');assert page.locator('[data-journal-row]:visible').count()==8
  page.fill('[data-journal-controls] [name=q]','اندازه');page.select_option('[data-journal-controls] [name=topic]','legumes');page.locator('[data-journal-controls] button[type=submit]').click();page.wait_for_load_state('networkidle');assert page.locator('[data-journal-row]:visible').count()==1
  page.goto(base+'/products/coffee/');page.locator('.ag-spec-filters a').last.click();page.wait_for_load_state('networkidle');assert page.locator('.ag-content-card').count()==1
  page.goto(state['products']['coffee']['url']);assert page.locator('.ag-product-gallery button').count()==2
  page.locator('.ag-product-gallery button').last.click();assert page.locator('.ag-zoom-dialog').evaluate('el=>el.open');page.keyboard.press('Escape');assert not page.locator('.ag-zoom-dialog').evaluate('el=>el.open')
  assert page.locator('[name=product_id]').input_value()==str(state['products']['coffee']['id'])
  page.goto(state['episode']['url']);assert page.locator('.ag-transcript').count()==1 and page.locator('[data-timestamp]').count()==1
  # Audio is loaded by click and the timestamp is respected.
  page.locator('video').evaluate('el=>el.remove()');page.locator('[data-timestamp]').click();page.wait_for_timeout(2500);assert page.locator('audio').evaluate('el=>el.currentTime')>=1
  page.goto(base+'/contact/?utm_source=qa-source&utm_campaign=qa-campaign');page.fill('#approved-name','آزمون قابلیت جدید');page.fill('#approved-mobile','۰۹۱۲۰۰۰۰۰۰۰');page.fill('#approved-notes','پیام آزمایشی فرم کوتاه');page.select_option('#approved-topic','products');page.check('#approved-consent');page.locator('#contact-form [type=submit]').click();page.wait_for_url('**/thank-you/**');assert page.locator('[data-lead-success]').count()==1
  lead_id=int(wp('$p=get_posts(["post_type"=>"vatan_lead","post_status"=>"private","numberposts"=>1,"meta_key"=>"_vatan_name","meta_value"=>"آزمون قابلیت جدید"]);echo $p[0]->ID;'))
  assert wp(f'echo get_post_meta({lead_id},"_vatan_utm_source",true);')=='qa-source'
  assert wp(f'echo get_post_meta({lead_id},"_vatan_form_kind",true);')=='contact'
  assert wp(f'echo get_post_meta({lead_id},"_vatan_category",true);')==''
  wp(f'update_post_meta({lead_id},"_qa_capability","1");')
  # Without analytics consent there are no collector requests; after consent counts are accepted.
  fresh=b.new_context(viewport={'width':1440,'height':950},reduced_motion='reduce');fresh.add_init_script("document.addEventListener('click',e=>{if(e.target.closest('a[href^=\"tel:\"]'))e.preventDefault()})");q=fresh.new_page();events=[];q.on('request',lambda request:events.append(request) if '/vatan/v1/events' in request.url else None)
  q.goto(base+'/contact/');q.locator('a[href^="tel:"]').first.click();q.wait_for_timeout(200);assert len(events)==0
  q.locator('[data-event-choice=yes]').click();q.locator('a[href^="tel:"]').first.click();q.wait_for_timeout(500);assert len(events)==1
  result=q.request.post(base+'/wp-json/vatan/v1/events',data={'consent':True,'event':'article_read_75'});assert result.ok and result.json()['accepted']==1,result.text()
  assert q.request.post(base+'/wp-json/vatan/v1/events',data={'consent':False,'event':'phone_click'}).status==400
  q.locator('[data-event-settings]').click();q.locator('[data-event-choice=no]').click();q.locator('a[href^="tel:"]').first.click();q.wait_for_timeout(300);assert len(events)==1
  fresh.close()
  # Server-side errors must retain safe entered values and describe invalid controls.
  page.goto(base+'/contact/')
  invalid=page.locator('#contact-form').evaluate("f=>Object.fromEntries(new FormData(f))")
  invalid.update({'name':'آ','mobile':'123','notes':'','consent':''})
  response=page.request.post(base+'/wp-admin/admin-post.php',form=invalid)
  assert response.ok and '/contact/' in response.url and 'form_error=' in response.url
  page.goto(response.url);assert page.locator('#approved-name').input_value()=='آ'
  assert page.locator('#approved-mobile').get_attribute('aria-invalid')=='true'
  assert page.locator('[data-server-errors] a[href="#approved-consent"]').count()==1
  assert page.locator('[data-server-errors]').count()==1
  # Verify native editing and gallery field persistence using only test posts.
  access=dict(re.findall(r'^(Username|Password): (.*)$',(root/'.runtime/access.txt').read_text(),re.M))
  page.goto(base+'/wp-login.php');page.fill('#user_login',access['Username']);page.fill('#user_pass',access['Password']);page.click('#wp-submit');page.wait_for_url('**/wp-admin/**')
  page.goto(base+'/wp-admin/options-general.php?page=vatan-settings');assert 'تنظیمات قالب ارمغان تجارت وطن' in page.locator('h1').inner_text();assert page.locator('[name^="vatan_features["]').count()>=12
  # Save feature controls through the actual Settings API, then verify rendered behavior.
  for feature in ['animations','home_video','video_library','product_filters']:
   page.locator('[name="vatan_features['+feature+']"]').uncheck()
  page.locator('#submit').click();page.wait_for_load_state('networkidle')
  page.goto(base+'/');assert page.locator('body.ag-static-motion').count()==1
  assert page.locator('[data-video-loader]').count()==0
  page.goto(base+'/media/');assert page.locator('.ag-video-library').count()==0
  page.goto(base+'/products/coffee/');assert page.locator('[data-discovery]').count()==0
  value=state['options']['vatan_features'];wp('delete_option("vatan_features");' if value is None else f'update_option("vatan_features",json_decode({json.dumps(json.dumps(value))},true));')
  page.goto(base+f'/wp-admin/post.php?post={state["products"]["coffee"]["id"]}&action=edit');page.fill('#vatan-gallery',str(state['images'][1]));page.locator('.ag-editor-nav a[href="#ag-edit-armaghan_sections-detail"]').click();page.fill('[name="armaghan_sections[detail][body]"]','مقدمه قابل ویرایش نمونه آزمون');page.fill('[name="armaghan_sections[detail][image]"]','asset:products/rice.webp');page.locator('#publish').click();page.wait_for_load_state('networkidle');saved_gallery=json.loads(wp(f'echo wp_json_encode(get_post_meta({state["products"]["coffee"]["id"]},"_vatan_gallery",true));'));assert saved_gallery==[state['images'][1]]
  page.goto(state['products']['coffee']['url']);assert 'مقدمه قابل ویرایش نمونه آزمون' in page.locator('.ag-article-hero').inner_text();assert 'rice' in page.locator('.ag-article-hero img').get_attribute('src')
  page.goto(base+'/wp-admin/nav-menus.php');assert page.locator('body').count()==1
  assert errors==[],errors
  b.close()
 print(json.dumps({'views':len(reports),'form':'private contact stored with UTM','gallery':'native edit/save/zoom','media':'timestamps/play/transcript','discovery':'search/topic/spec filters','analytics':'opt-in collector and revocation','js_errors':errors},ensure_ascii=False))
finally:
 owned=state['images']+[p['id'] for p in state['products'].values()]+[state['episode']['id']]+([lead_id] if 'lead_id' in locals() else [])
 wp('foreach('+json.dumps(owned)+' as $id){if(get_post_meta($id,"_qa_capability",true)!=="1")continue;if(get_post_type($id)==="attachment")wp_delete_attachment($id,true);else wp_delete_post($id,true);}')
 for key,value in state['options'].items():
  wp((f'delete_option({json.dumps(key)});' if value is None else f'update_option({json.dumps(key)},json_decode({json.dumps(json.dumps(value))},true));'))
 for key,value in state['counts'].items():wp(f'delete_option({json.dumps(key)});' if value is None else f'update_option({json.dumps(key)},{int(value)});')
 audio.unlink(missing_ok=True)
 (out/('results.json' if reports else 'functional-results.json')).write_text(json.dumps({'views':reports},ensure_ascii=False,indent=2))
