#!/usr/bin/env python3
"""Build a clean WordPress host bundle from pinned public sources, never from the runtime DB."""
from pathlib import Path
import argparse,hashlib,json,shutil,subprocess,tarfile,zipfile
root=Path(__file__).resolve().parent.parent
p=argparse.ArgumentParser(description=__doc__);p.add_argument('--output',type=Path,required=True);a=p.parse_args();out=a.output.resolve()
if out.exists():raise SystemExit('Output exists; choose a new directory.')
subprocess.run(['python3',str(root/'scripts/assemble.py')],check=True,cwd=root)
subprocess.run(['python3',str(root/'scripts/build-assets.py')],check=True,cwd=root)
for name,expected in json.loads((root/'vendor/checksums.json').read_text()).items():
 with (root/'vendor'/name).open('rb') as f:assert hashlib.file_digest(f,'sha256').hexdigest()==expected,name
out.mkdir(parents=True);public=out/'public_html';public.mkdir()
with tarfile.open(root/'vendor/wordpress-7.1.3.tar.gz') as tar:
 for item in tar.getmembers():
  name=Path(item.name);assert name.parts[0]=='wordpress' and '..' not in name.parts
  if len(name.parts)==1:continue
  item.name=Path(*name.parts[1:]).as_posix();tar.extract(item,public,filter='data')
content=public/'wp-content';shutil.copytree(root/'theme/vatan-authority',content/'themes/vatan-authority');shutil.copytree(root/'plugin/vatan-core',content/'plugins/vatan-core')
shutil.copytree(root/'vendor/sqlite-database-integration',content/'plugins/sqlite-database-integration');shutil.copy2(root/'vendor/sqlite-db.php',content/'armaghan-sqlite-dropin.php')
(content/'languages').mkdir(exist_ok=True)
with tarfile.open(root/'vendor/languages-fa_IR.tar.gz') as tar:tar.extractall(content/'languages',filter='data')
publicdata=content/'armaghan-public';shutil.copytree(root/'content',publicdata)
shutil.copy2(root/'scripts/host-install.php',public/'armaghan-install.php')
(public/'armaghan-install.php').chmod(0o644)
# Distribution contains code and public seed material only, never an installation's config/users/leads.
(public/'wp-config-sample.php').unlink(missing_ok=True)
(public/'INSTALL-fa.txt').write_text((root/'docs/HOST-INSTALL-fa.md').read_text())
(publicdata/'.htaccess').write_text('Require all denied\n')
# Public code/assets need ordinary hosting permissions; runtime secrets are never copied.
public.chmod(0o755)
for file in public.rglob('*'):
 if file.is_dir():file.chmod(0o755)
 elif file.is_file():file.chmod(0o644)
for file in public.rglob('*'):
 if file.is_file():
  assert file.name not in ['wp-config.php','access.txt','salts.json']
  assert file.suffix not in ['.sqlite','.db','.sql']
manifest=[]
for file in sorted(public.rglob('*')):
 if file.is_file():
  with file.open('rb') as f:digest=hashlib.file_digest(f,'sha256').hexdigest()
  manifest.append({'path':file.relative_to(public).as_posix(),'sha256':digest,'bytes':file.stat().st_size})
source_commit=subprocess.check_output(['git','rev-parse','HEAD'],cwd=root,text=True).strip()
(out/'manifest.json').write_text(json.dumps({'version':'1.18.4','package_revision':'subfolder-1','source_commit':source_commit,'files':manifest},indent=2)+'\n')
archive=out/'Armaghan-WordPress-v1.18.4-subfolder.zip'
with zipfile.ZipFile(archive,'w',compression=zipfile.ZIP_DEFLATED,compresslevel=6) as z:
 for file in sorted(public.rglob('*')):
  if file.is_file():z.write(file,file.relative_to(public),compress_type=zipfile.ZIP_STORED if file.suffix in ['.mp4','.webp','.woff2','.jpg','.png'] else zipfile.ZIP_DEFLATED)
 z.write(out/'manifest.json','armaghan-package-manifest.json')
with archive.open('rb') as f:digest=hashlib.file_digest(f,'sha256').hexdigest()
(out/'SHA256SUMS.txt').write_text(digest+'  '+archive.name+'\n')
print(json.dumps({'archive':str(archive),'bytes':archive.stat().st_size,'sha256':digest,'public_files':len(manifest)}))
