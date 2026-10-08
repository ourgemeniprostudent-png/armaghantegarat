#!/usr/bin/env python3
"""Attach the verified public host ZIP to Pages without oversized Git blobs."""
import argparse,hashlib,json,shutil,zipfile
from pathlib import Path

p=argparse.ArgumentParser(description=__doc__)
p.add_argument('--archive',type=Path,required=True)
p.add_argument('--preview',type=Path,required=True)
a=p.parse_args();archive=a.archive.resolve();preview=a.preview.resolve()
assert (preview/'preview-manifest.json').is_file(),'Export the preview first.'
assert archive.name=='Armaghan-WordPress-v1.15.0.zip'
with zipfile.ZipFile(archive) as z:
 assert z.testzip() is None,'Archive CRC failed.'
 for name in z.namelist():
  assert '..' not in Path(name).parts and '.runtime' not in Path(name).parts
  assert Path(name).name not in ['wp-config.php','access.txt','salts.json']
  assert Path(name).suffix not in ['.sqlite','.db','.sql']
dest=preview/'downloads/1.15.0';dest.mkdir(parents=True,exist_ok=False)
parts=[];whole=hashlib.sha256()
with archive.open('rb') as source:
 for index in range(1,1000):
  data=source.read(16*1024*1024)
  if not data:break
  name=f'part-{index:02d}.bin';(dest/name).write_bytes(data);whole.update(data)
  parts.append({'path':name,'bytes':len(data),'sha256':hashlib.sha256(data).hexdigest()})
manifest={'version':'1.15.0','filename':archive.name,'bytes':archive.stat().st_size,'sha256':whole.hexdigest(),'parts':parts}
(dest/'manifest.json').write_text(json.dumps(manifest,indent=2)+'\n')
(dest/'SHA256SUMS.txt').write_text(whole.hexdigest()+'  '+archive.name+'\n')
page=preview/'download';page.mkdir(exist_ok=False)
(page/'index.html').write_text('''<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>دریافت وردپرس ارمغان تجارت وطن</title>
<style>@font-face{font-family:Peyda;src:url('../wp-content/themes/vatan-authority/assets/fonts/PeydaWebFaNum-Regular.woff2') format('woff2');font-display:swap}*{box-sizing:border-box}body{margin:0;background:#080909;color:#eee;font:17px/2 Peyda,Tahoma,sans-serif}main{max-width:720px;margin:6vh auto;padding:28px}h1{font-size:clamp(30px,6vw,46px);font-weight:400;line-height:1.5}p,li{color:#c1c0b9}a{color:#e0ba7c;text-underline-offset:5px}button{font:inherit;font-weight:400;background:#d8b27b;color:#080909;border:0;padding:14px 26px;border-radius:5px;cursor:pointer}button:disabled{opacity:.65;cursor:wait}:focus-visible{outline:3px solid #ead0a6;outline-offset:5px}progress{width:100%;height:8px;accent-color:#d8b27b}small{color:#c9a46d}section{border-top:1px solid #c7a06530;margin-top:35px;padding-top:25px}.status{min-height:60px;margin-top:15px}noscript{display:block;color:#e6ba7d}</style><script src="download.js" defer></script></head><body><main><small>ارمغان تجارت وطن · نسخه ۱.۱۵.۰</small><h1>بسته کامل وردپرس</h1><p>قالب اختصاصی، افزونه، تصاویر، فونت‌ها، ویدیوهای اصلی و محتوای عمومی؛ آماده نصب تازه در ریشه هاست.</p><button id="download" type="button">دریافت فایل ZIP</button><p class="status" id="status" role="status">حجم فایل حدود ۱۸۸ مگابایت است. این صفحه را تا پایان دریافت باز نگه دارید.</p><progress id="progress" value="0" max="100" aria-label="پیشرفت دریافت"></progress><noscript>برای دریافت فایل، جاوااسکریپت مرورگر را فعال کنید.</noscript><section><h2>پس از دریافت</h2><ol><li>فایل را در یک پوشه خالی public_html استخراج کنید؛ index.php باید مستقیم در ریشه باشد.</li><li>دیتابیس خالی و کاربر آن را در پنل هاست بسازید و HTTPS دامنه را فعال کنید.</li><li>نشانی armaghan-install.php را روی دامنه خود باز کنید و حساب مدیر تازه بسازید.</li></ol><p>نسخه فارسی با ۱۳ صفحه و ۸ مقاله نصب می‌شود. /en پنهان می‌ماند. رمز و دیتابیس توسعه یا درخواست‌های خصوصی داخل فایل نیست.</p><p><a href="../downloads/1.15.0/INSTALL-fa.txt">راهنمای کامل نصب</a> · <a href="../index.html">دیدن سایت</a></p></section></main></body></html>''')
(page/'download.js').write_text('''(() => {'use strict';const button=document.getElementById('download'),status=document.getElementById('status'),progress=document.getElementById('progress');const manifestURL=new URL('../downloads/1.15.0/manifest.json',location.href);const hex=buffer=>[...new Uint8Array(buffer)].map(n=>n.toString(16).padStart(2,'0')).join('');const digest=bytes=>crypto.subtle.digest('SHA-256',bytes).then(hex);const fa=n=>new Intl.NumberFormat('fa-IR').format(n);button.addEventListener('click',async()=>{button.disabled=true;progress.value=0;try{const response=await fetch(manifestURL);if(!response.ok)throw Error('دریافت اطلاعات فایل کامل نشد.');const manifest=await response.json(),parts=[];let received=0;for(const part of manifest.parts){const r=await fetch(new URL(part.path,manifestURL));if(!r.ok)throw Error('اتصال هنگام دریافت قطع شد.');const data=await r.arrayBuffer();if(data.byteLength!==part.bytes||await digest(data)!==part.sha256)throw Error('صحت بخشی از فایل تایید نشد.');parts.push(new Blob([data]));received+=data.byteLength;progress.value=received/manifest.bytes*95;status.textContent='دریافت '+fa(Math.round(received/manifest.bytes*100))+' درصد؛ لطفا صفحه را باز نگه دارید.';}const blob=new Blob(parts,{type:'application/zip'});parts.length=0;status.textContent='فایل دریافت شد؛ در حال بررسی صحت نسخه کامل…';if(blob.size!==manifest.bytes||await digest(await blob.arrayBuffer())!==manifest.sha256)throw Error('صحت فایل کامل تایید نشد.');progress.value=100;const url=URL.createObjectURL(blob),a=document.createElement('a');a.href=url;a.download=manifest.filename;a.click();setTimeout(()=>URL.revokeObjectURL(url),60000);status.textContent='صحت فایل تایید شد و دریافت ZIP آغاز شد. راهنمای نصب در پایین همین صفحه است.';}catch(error){status.textContent=error.message+' دوباره روی دریافت فایل کلیک کنید.';}finally{button.disabled=false;}});})();''')
shutil.copy2(Path(__file__).resolve().parent.parent/'docs/HOST-INSTALL-fa.md',dest/'INSTALL-fa.txt')
preview_manifest=json.loads((preview/'preview-manifest.json').read_text())
files=[]
for file in sorted(preview.rglob('*')):
 if not file.is_file() or file.name=='preview-manifest.json':continue
 with file.open('rb') as stream:sha=hashlib.file_digest(stream,'sha256').hexdigest()
 files.append({'path':file.relative_to(preview).as_posix(),'bytes':file.stat().st_size,'sha256':sha})
preview_manifest['files']=files;preview_manifest['host_package']={k:v for k,v in manifest.items() if k!='parts'}
(preview/'preview-manifest.json').write_text(json.dumps(preview_manifest,ensure_ascii=False,indent=2)+'\n')
print(json.dumps({'download_page':'download/','parts':len(parts),'archive_sha256':whole.hexdigest(),'bytes':archive.stat().st_size}))
