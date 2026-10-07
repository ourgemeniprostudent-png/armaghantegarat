#!/usr/bin/env python3
"""Rebuild large bundled files without transcoding or changing any bytes."""
from pathlib import Path
import hashlib,json,os
root=Path(__file__).resolve().parent.parent
manifest=json.loads((root/'payload/manifest.json').read_text())
def digest(path):
 h=hashlib.sha256()
 with path.open('rb') as f:
  for block in iter(lambda:f.read(1024*1024),b''):h.update(block)
 return h.hexdigest()
for asset in manifest['assets']:
 target=root/asset['path']
 if target.is_file() and target.stat().st_size==asset['bytes'] and digest(target)==asset['sha256']:continue
 target.parent.mkdir(parents=True,exist_ok=True);temp=target.with_name(target.name+'.assembly.tmp')
 try:
  h=hashlib.sha256();size=0
  with temp.open('wb') as out:
   for part in asset['parts']:
    source=root/part['path']
    if not source.is_file() or digest(source)!=part['sha256']:raise RuntimeError('Missing/corrupt payload part: '+part['path'])
    with source.open('rb') as f:
     for block in iter(lambda:f.read(1024*1024),b''):
      out.write(block);h.update(block);size+=len(block)
  if h.hexdigest()!=asset['sha256'] or size!=asset['bytes']:raise RuntimeError('Reassembled asset failed integrity check: '+asset['path'])
  os.replace(temp,target)
  print('Rebuilt and verified:',asset['path'])
 finally:
  if temp.exists():temp.unlink()
