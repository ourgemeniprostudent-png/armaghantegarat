(() => {
 'use strict';
 document.querySelectorAll('[data-media-tabs]').forEach(group => {
  const tabs=[...group.querySelectorAll('[role=tab]')],panels=[...group.querySelectorAll('[data-media-panel]')];
  const choose=index=>{tabs.forEach((tab,i)=>{tab.setAttribute('aria-selected',String(i===index));tab.tabIndex=i===index?0:-1;panels[i].hidden=i!==index;if(i!==index)panels[i].querySelector('audio,video')?.pause();});};
  group.classList.add('am-enhanced');choose(0);
  tabs.forEach((tab,i)=>{tab.addEventListener('click',()=>choose(i));tab.addEventListener('keydown',e=>{if(!['ArrowLeft','ArrowRight','Home','End'].includes(e.key))return;e.preventDefault();const next=e.key==='Home'?0:e.key==='End'?tabs.length-1:(i+(e.key==='ArrowLeft'?1:-1)+tabs.length)%tabs.length;choose(next);tabs[next].focus();});});
 });
 document.addEventListener('play',event=>{if(!event.target.matches('[data-episode-player]'))return;document.querySelectorAll('[data-episode-player]').forEach(player=>{if(player!==event.target)player.pause();});},true);
 const time=seconds=>Number.isFinite(seconds)?Math.floor(seconds/60)+':'+String(Math.floor(seconds%60)).padStart(2,'0'):'—:—';
 document.querySelectorAll('[data-audio-console]').forEach(console=>{
  const audio=console.querySelector('audio'),controls=console.querySelector('.ms-audio-controls'),play=console.querySelector('[data-audio-play]'),seek=console.querySelector('[data-audio-seek]'),status=console.querySelector('[data-audio-status]'),mute=console.querySelector('[data-audio-mute]');
  if(!audio||!controls)return;
  controls.hidden=false;console.classList.add('is-enhanced');
  const update=()=>{
   console.classList.toggle('is-playing',!audio.paused);
   play.setAttribute('aria-label',audio.paused?console.dataset.playLabel:console.dataset.pauseLabel);
   play.innerHTML=audio.paused?'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 5 11 7-11 7Z" fill="currentColor"/></svg>':'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14M16 5v14" stroke="currentColor" stroke-width="3"/></svg>';
   console.querySelector('[data-audio-time]').textContent=time(audio.currentTime);console.querySelector('[data-audio-duration]').textContent=time(audio.duration);
   seek.disabled=!Number.isFinite(audio.duration)||audio.duration<=0;seek.value=seek.disabled?0:audio.currentTime/audio.duration*100;
   seek.setAttribute('aria-valuetext',time(audio.currentTime)+' / '+time(audio.duration));
  };
  const fail=()=>{status.textContent=console.dataset.errorLabel;update();};
  play.addEventListener('click',()=>{if(!audio.paused){audio.pause();return;}status.textContent=console.dataset.loadingLabel;audio.play().then(()=>{status.textContent='';}).catch(fail);});
  seek.addEventListener('input',()=>{if(Number.isFinite(audio.duration))audio.currentTime=Number(seek.value)/100*audio.duration;update();});
  console.querySelector('[data-audio-back]').addEventListener('click',()=>{audio.currentTime=Math.max(0,audio.currentTime-15);update();});
  console.querySelector('[data-audio-forward]').addEventListener('click',()=>{if(Number.isFinite(audio.duration))audio.currentTime=Math.min(audio.duration,audio.currentTime+15);update();});
  console.querySelector('[data-audio-rate]').addEventListener('change',event=>{audio.playbackRate=Number(event.target.value);});
  mute.addEventListener('click',()=>{audio.muted=!audio.muted;mute.setAttribute('aria-pressed',String(audio.muted));});
  ['loadedmetadata','durationchange','timeupdate','play','pause','ended'].forEach(event=>audio.addEventListener(event,update));
  audio.addEventListener('waiting',()=>{status.textContent=console.dataset.loadingLabel;});audio.addEventListener('playing',()=>{status.textContent='';});audio.addEventListener('error',fail);update();
 });
 document.querySelectorAll('[data-row-audio]').forEach(trigger=>{
  const panel=document.getElementById(trigger.dataset.rowAudio),audio=panel?.querySelector('audio');if(!panel||!audio)return;
  trigger.addEventListener('click',event=>{event.preventDefault();if(!panel.hidden){audio.pause();panel.hidden=true;trigger.setAttribute('aria-expanded','false');return;}
   document.querySelectorAll('.ms-inline-player').forEach(other=>{if(other!==panel){other.hidden=true;other.querySelector('audio')?.pause();document.querySelector('[data-row-audio="'+other.id+'"]')?.setAttribute('aria-expanded','false');}});
   panel.hidden=false;trigger.setAttribute('aria-expanded','true');audio.play().catch(()=>{});
  });
 });
 document.querySelectorAll('[data-media-mock]').forEach(button=>button.addEventListener('click',event=>{
  event.preventDefault();const box=button.closest('.ms-mock-player,.ms-video-card'),status=box?.querySelector('.ms-mock-status');if(!status)return;
  status.hidden=false;box.classList.add('is-demo-active');status.tabIndex=-1;status.focus({preventScroll:true});
 }));
 document.querySelectorAll('[data-media-video]').forEach(trigger=>{
  const dialog=document.getElementById(trigger.dataset.mediaVideo),video=dialog?.querySelector('video');if(!dialog||!video||typeof dialog.showModal!=='function')return;
  trigger.addEventListener('click',event=>{event.preventDefault();dialog.showModal();dialog.querySelector('button').focus();video.play().catch(()=>{});});
  dialog.querySelector('[data-media-close]').addEventListener('click',()=>dialog.close());
  dialog.addEventListener('click',event=>{if(event.target===dialog){const r=dialog.getBoundingClientRect();if(event.clientX<r.left||event.clientX>r.right||event.clientY<r.top||event.clientY>r.bottom)dialog.close();}});
  dialog.addEventListener('cancel',()=>video.pause());
  dialog.addEventListener('close',()=>{video.pause();trigger.focus({preventScroll:true});});
 });
 const form=document.querySelector('[data-media-search-form]');
 if(form&&(document.documentElement.hasAttribute('data-static-preview')||document.querySelector('.ms-demo-notice'))){
  const input=form.querySelector('input'),select=form.querySelector('select'),items=[...document.querySelectorAll('[data-media-item]')],notice=document.querySelector('[data-media-no-results]');
  const normalize=text=>text.replace(/ي/g,'ی').replace(/ك/g,'ک').replace(/\u200c/g,' ').toLocaleLowerCase('fa').trim();
  const filter=()=>{const query=normalize(input.value),topic=select.selectedIndex?select.value:'';let count=0;items.forEach(item=>{const matches=normalize(item.dataset.mediaSearch).includes(query)&&(!topic||item.dataset.mediaTopic.split(' ').includes(topic));item.hidden=!matches;if(matches)count++;});notice.hidden=count>0;};
  form.addEventListener('submit',event=>{event.preventDefault();event.stopImmediatePropagation();filter();const url=new URL(location.href);input.value?url.searchParams.set('q',input.value):url.searchParams.delete('q');select.value?url.searchParams.set('topic',select.value):url.searchParams.delete('topic');history.replaceState(null,'',url);});
  input.addEventListener('input',filter);select.addEventListener('change',filter);
  const params=new URLSearchParams(location.search);input.value=params.get('q')||'';select.value=params.get('topic')||'';filter();
 }
})();
