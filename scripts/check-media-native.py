#!/usr/bin/env python3
"""Exercise real product/media templates and native editing; remove only owned fixtures."""
import json,os,re,subprocess,uuid,wave
from pathlib import Path
from playwright.sync_api import sync_playwright
root=Path(__file__).resolve().parent.parent;base=(root/'.runtime/site-url.txt').read_text().strip();out=root/'.runtime/media-checks';out.mkdir(exist_ok=True)
wp_cmd=[os.environ.get('ARMAGHAN_PHP_BIN','php'),str(root/'vendor/wp-cli-2.12.0.phar'),'--allow-root','--path='+str(root/'.runtime/wordpress'),'--url='+base]
def wp(code):return subprocess.check_output(wp_cmd+['eval',code],text=True)
assert not wp('echo get_site_option("vatan_notify_email","");').strip(),'Testing requires email notifications off.'
marker='ui-'+uuid.uuid4().hex[:12];audio=root/'.runtime/wordpress/wp-content/uploads'/f'{marker}.wav'
with wave.open(str(audio),'wb') as f:f.setnchannels(1);f.setsampwidth(2);f.setframerate(8000);f.writeframes(b'\x00\x00'*40000)
state=None;report=[]
try:
 state=json.loads(wp('''$marker='''+json.dumps(marker)+''';$owned=[];$products=[];
 $imgfile=wp_upload_dir()['basedir'].'/'.$marker.'.webp';copy(get_template_directory().'/assets/podcasts/trade.webp',$imgfile);
 $img=wp_insert_attachment(['post_title'=>$marker,'post_mime_type'=>'image/webp','post_status'=>'inherit','meta_input'=>['_qa_capability'=>'1']],$imgfile);$owned[]=$img;require_once ABSPATH.'wp-admin/includes/image.php';wp_update_attachment_metadata($img,wp_generate_attachment_metadata($img,$imgfile));
 $audio=wp_insert_attachment(['post_title'=>$marker.' audio','post_mime_type'=>'audio/wav','post_status'=>'inherit','meta_input'=>['_qa_capability'=>'1']],wp_upload_dir()['basedir'].'/'.$marker.'.wav');$owned[]=$audio;
 foreach(vatan_core_categories() as $slug=>$name){$id=wp_insert_post(['post_type'=>'vatan_product','post_status'=>'publish','post_name'=>$marker.'-'.$slug,'post_title'=>'آزمون موقت محصول '.$name,'post_excerpt'=>'رکورد موقت بررسی قالب؛ حذف خواهد شد.','post_content'=>'<h2>شرح نمونه آزمایشی</h2><p>آزمون مشخصات و نمایش محصول.</p>','meta_input'=>['_qa_capability'=>'1','_vatan_specs'=>"اندازه | متوسط\\nشکل | دانه",'_vatan_gallery'=>[$img]]]);$owned[]=$id;wp_set_object_terms($id,[$slug],'vatan_category');set_post_thumbnail($id,$img);$products[]=get_permalink($id);}
 $ep=wp_insert_post(['post_type'=>'vatan_episode','post_status'=>'publish','post_name'=>$marker,'post_title'=>'آزمون موقت گفتگو','post_excerpt'=>'بررسی صوت، ویدیو و متن گفتگو','post_content'=>'<h2>معرفی گفتگو</h2><p>محتوای موقت برای بررسی فنی.</p>','meta_input'=>['_qa_capability'=>'1','_vatan_audio'=>wp_get_attachment_url($audio),'_vatan_video'=>get_template_directory_uri().'/assets/media/hero-h264.mp4','_vatan_transcript'=>'متن نمونه آزمون فنی؛ پس از بررسی حذف می‌شود.','_vatan_timestamps'=>'00:01 | بخش دوم','_vatan_duration'=>'آزمون','_vatan_featured'=>true]]);$owned[]=$ep;wp_set_object_terms($ep,['coffee'],'vatan_topic');set_post_thumbnail($ep,$img);
 for($i=0;$i<3;$i++){$id=wp_insert_post(['post_type'=>'post','post_status'=>'publish','post_name'=>$marker.'-'.$i,'post_title'=>'یادداشت آزمایشی '.$i,'post_excerpt'=>'نمونه موقت صفحه‌بندی','meta_input'=>['_qa_capability'=>'1']]);$owned[]=$id;set_post_thumbnail($id,$img);wp_set_object_terms($id,['coffee'],'vatan_topic');}
 echo wp_json_encode(['owned'=>$owned,'episode'=>$ep,'url'=>get_permalink($ep),'products'=>$products,'audio_url'=>wp_get_attachment_url($audio),'features'=>get_option('vatan_features',null)]);'''))
 (out/'owned-fixtures.json').write_text(json.dumps(state))
 with sync_playwright() as p:
  b=p.chromium.launch(executable_path=os.environ.get('ARMAGHAN_CHROMIUM_BIN','/usr/bin/chromium'),args=['--no-sandbox']);c=b.new_context(viewport={'width':1440,'height':900},reduced_motion='reduce');q=c.new_page();errors=[];q.on('pageerror',lambda e:errors.append(str(e)))
  for width in ([] if os.environ.get('QA_FAST') else [360,390,768,1440]):
   q.set_viewport_size({'width':width,'height':900})
   for url in state['products']+[state['url'],base+'/media/',base+'/']:
    response=q.goto(url,wait_until='networkidle');assert response.status==200,(url,response.status);assert q.locator('h1').count()==1,url;assert not q.evaluate('document.documentElement.scrollWidth>innerWidth'),('overflow',width,url);report.append(dict(width=width,url=url))
   if width in [390,1440]:
    q.goto(state['url'],wait_until='networkidle');q.locator('.am-players').scroll_into_view_if_needed();q.screenshot(path=str(out/f'episode-{width}.png'))
    q.goto(state['products'][0],wait_until='networkidle');q.screenshot(path=str(out/f'product-{width}.png'))
  q.set_viewport_size({'width':1440,'height':900});q.goto(state['url']);assert q.locator('[role=tab]').count()==2;assert q.locator('.ms-audio-controls').is_visible() and not q.locator('video').is_visible()
  q.locator('[data-timestamp]').click();q.wait_for_timeout(2500);assert q.locator('audio').evaluate('el=>el.currentTime')>=1,q.locator('audio').evaluate('el=>({time:el.currentTime,duration:el.duration,error:el.error?.message,src:el.src,ready:el.readyState})')
  q.locator('[role=tab]').nth(1).click();assert q.locator('video').is_visible() and not q.locator('audio').is_visible();assert q.locator('audio').evaluate('el=>el.paused')
  q.locator('[data-timestamp]').click();q.wait_for_timeout(1000);assert q.locator('video').evaluate('el=>el.currentTime')>=1
  q.locator('[role=tab]').nth(1).press('Home');assert q.locator('[role=tab]').first.get_attribute('aria-selected')=='true';report.append('Audio/video tabs, keyboard controls, active-player seeking and pause passed')
  q.goto(base+'/media/');assert q.locator('.am-samples').count()==0;assert q.locator('.ms-episode-row').count()>0
  q.locator('.ms-feature [data-audio-play]').click();q.wait_for_timeout(600);assert not q.locator('.ms-feature audio').evaluate('el=>el.paused')
  q.locator('.ms-feature [data-audio-rate]').select_option('1.5');assert q.locator('.ms-feature audio').evaluate('el=>el.playbackRate')==1.5
  q.locator('.ms-feature [data-audio-mute]').click();assert q.locator('.ms-feature audio').evaluate('el=>el.muted')
  q.locator('[data-media-video]').click();assert q.locator('dialog[open]').count()==1;q.wait_for_timeout(600);assert q.locator('.ms-feature audio').evaluate('el=>el.paused');assert not q.locator('dialog[open] video').evaluate('el=>el.paused')
  q.locator('[data-media-close]').press('Escape');assert q.locator('dialog[open]').count()==0;q.wait_for_function("document.querySelector('dialog video').paused && document.querySelector('[data-media-video]')===document.activeElement")
  q.locator('[data-row-audio]').click();q.wait_for_timeout(400);assert q.locator('.ms-inline-player').is_visible();assert not q.locator('.ms-inline-player audio').evaluate('el=>el.paused');assert q.locator('.ms-feature audio').evaluate('el=>el.paused')
  q.locator('.ms-inline-player [data-audio-seek]').fill('70');q.wait_for_function("document.querySelector('.ms-inline-player audio').currentTime>=3")
  q.locator('[data-row-audio]').click();assert not q.locator('.ms-inline-player').is_visible();assert q.locator('.ms-inline-player audio').evaluate('el=>el.paused')
  report.append('Inline episode play, seeking, collapse and pause passed')
  report.append('Hub audio play, playback speed, mute, video dialog, mutual pause and Escape focus restoration passed')
  q.route(state['audio_url'],lambda route:route.fulfill(status=404,body=''))
  q.goto(state['url']);q.locator('[data-audio-play]').click();q.wait_for_function("document.querySelector('[data-audio-status]').textContent.includes('در دسترس نیست')");q.unroute(state['audio_url']);report.append('Unavailable audio reports a readable error without a JavaScript crash')
  q.goto(base+'/blog/');assert q.locator('[data-journal-row]:visible').count()==9;q.locator('[data-journal-pages] a').last.click();assert q.locator('[data-journal-row]:visible').count()==2;q.reload();assert q.locator('[data-journal-row]:visible').count()==2;report.append('Journal pagination and reload passed')
  q.goto(base+'/contact/');assert q.locator('iframe').count()==0;q.route('**/maps.google.com/**',lambda route:route.fulfill(status=200,content_type='text/html',body='<p>Map transport fixture</p>'));q.locator('[data-map-load]').click();assert '35.7294807434082' in q.locator('iframe').get_attribute('src');assert '51.436744689941406' in q.locator('iframe').get_attribute('src');report.append('Map loads only on request and uses configured coordinates')
  access=dict(re.findall(r'^(Username|Password): (.*)$',(root/'.runtime/access.txt').read_text(),re.M));q.goto(base+'/wp-login.php');q.fill('#user_login',access['Username']);q.fill('#user_pass',access['Password']);q.click('#wp-submit');q.wait_for_url('**/wp-admin/**')
  q.goto(base+f'/wp-admin/post.php?post={state["episode"]}&action=edit');q.locator('[data-vatan-file-clear="vatan-media-audio"]').click();assert q.locator('#vatan-media-audio').input_value()=='';q.locator('[data-vatan-file="vatan-media-audio"]').click();dialog=q.locator('.media-modal');dialog.locator('#menu-item-browse').click();dialog.screenshot(path=str(out/'picker.png'))
  dialog.locator('.attachment[data-id="'+str(state['owned'][1])+'"]').click();dialog.locator('.media-button-select').click();assert q.locator('#vatan-media-audio').input_value()==state['audio_url'];q.locator('#publish').click();q.wait_for_load_state('networkidle');assert wp(f'echo get_post_meta({state["episode"]},"_vatan_audio",true);')==state['audio_url'];report.append('Native media picker, clear, selection and save persisted')
  for feature,route,selector in [('contact_form','contact','[data-approved-form]'),('office_map','contact','[data-map-load]'),('breadcrumbs','inquiry','.path'),('home_media','','#home-media'),('media_player_tabs','media/podcast/'+marker,'[role=tab]'),('media_audio_controls','media/podcast/'+marker,'[data-audio-console]')]:
   wp('$v=get_option("vatan_features",[]);$v['+json.dumps(feature)+']="0";update_option("vatan_features",$v);');q.goto(base+'/'+route+'/');assert q.locator(selector).count()==0,(feature,selector)
  assert q.locator('audio').is_visible();report.append('Contact form, map, breadcrumbs, Home media, player tabs and native audio fallback settings affect real rendering')
  value=state['features'];wp('delete_option("vatan_features");' if value is None else 'update_option("vatan_features",json_decode('+json.dumps(json.dumps(value))+',true));')
  nojs=b.new_context(java_script_enabled=False,viewport={'width':390,'height':844});n=nojs.new_page();n.goto(state['url']);assert n.locator('audio').is_visible() and n.locator('video').is_visible();n.goto(base+'/blog/?archive_page=2');assert n.locator('[data-journal-row]:visible').count()==2;nojs.close();assert not errors,errors;b.close()
finally:
 if state:
  wp('foreach('+json.dumps(state['owned'])+' as $id){if(get_post_meta($id,"_qa_capability",true)!=="1")throw new RuntimeException("Unexpected fixture owner");if(get_post_type($id)==="attachment")wp_delete_attachment($id,true);else wp_delete_post($id,true);}')
  value=state['features'];wp('delete_option("vatan_features");' if value is None else 'update_option("vatan_features",json_decode('+json.dumps(json.dumps(value))+',true));')
 audio.unlink(missing_ok=True)
 (out/'results.json').write_text(json.dumps(report,ensure_ascii=False,indent=2))
print(json.dumps({'views':len([x for x in report if isinstance(x,dict)]),'checks':[x for x in report if isinstance(x,str)]},ensure_ascii=False))
