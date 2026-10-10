#!/usr/bin/env python3
"""Publish a verified public export to gh-pages without touching the source index."""
import argparse,hashlib,json,os,subprocess,tempfile
from pathlib import Path
root=Path(__file__).resolve().parent.parent
p=argparse.ArgumentParser(description=__doc__);p.add_argument('--preview',type=Path,required=True);a=p.parse_args();preview=a.preview.resolve()
def git(*args):return subprocess.check_output(['git','-C',str(root),*args],text=True).strip()
manifest=json.loads((preview/'preview-manifest.json').read_text())
assert manifest['source_commit']==git('rev-parse','HEAD'),'Export must match the committed source.'
listed={x['path'] for x in manifest['files']}
actual={f.relative_to(preview).as_posix() for f in preview.rglob('*') if f.is_file() and f.name!='preview-manifest.json'}
assert listed==actual,'Unlisted or missing public files.'
for entry in manifest['files']:
 file=preview/entry['path'];assert file.resolve().is_relative_to(preview)
 assert '.runtime' not in Path(entry['path']).parts
 assert file.suffix.lower() not in ['.php','.db','.sqlite','.sql','.phar']
 assert file.name not in ['access.txt','wp-config.php','salts.json']
 assert file.stat().st_size==entry['bytes']
 with file.open('rb') as stream:assert hashlib.file_digest(stream,'sha256').hexdigest()==entry['sha256'],entry['path']
parent=git('ls-remote','origin','refs/heads/gh-pages').split()[0]
subprocess.run(['git','-C',str(root),'fetch','--no-tags','origin','refs/heads/gh-pages'],check=True)
assert git('rev-parse','FETCH_HEAD')==parent,'Preview branch changed during preparation.'
gitdir=Path(git('rev-parse','--absolute-git-dir'));index=gitdir/'index';before=hashlib.sha256(index.read_bytes()).hexdigest()
with tempfile.TemporaryDirectory(prefix='armaghan-preview-index-') as tmp:
 env={**os.environ,'GIT_INDEX_FILE':str(Path(tmp)/'index')}
 def stage(*args):return subprocess.check_output(['git','--git-dir='+str(gitdir),'--work-tree='+str(preview),*args],env=env,text=True).strip()
 stage('read-tree','--empty');stage('add','--all','--force','.')
 tree=stage('write-tree')
 commit=stage('-c','user.name=Codex','-c','user.email=codex@openai.com','commit-tree',tree,'-p',parent,'-m','Publish Armaghan 1.18.4 with a compact readable search workspace, consistent public results and verified host download')
 assert hashlib.sha256(index.read_bytes()).hexdigest()==before
 subprocess.run(['git','-C',str(root),'push','origin',commit+':refs/heads/gh-pages'],check=True)
 assert git('ls-remote','origin','refs/heads/gh-pages').split()[0]==commit
 (root/'.runtime/preview-commit.txt').write_text(commit+'\n')
print('Published verified preview:',commit)
