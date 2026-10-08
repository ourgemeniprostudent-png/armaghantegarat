(() => {
 'use strict';
 const header=document.querySelector('.site-header'),nav=document.getElementById('site-nav');
 const glass=()=>header?.classList.toggle('is-glass',!header.classList.contains('video-header')||window.scrollY>36||!!nav?.classList.contains('is-open'));
 glass();window.addEventListener('scroll',glass,{passive:true});
 if(nav)new MutationObserver(glass).observe(nav,{attributes:true,attributeFilter:['class']});
 const hero=document.querySelector('[data-trade-hero]'),video=hero?.querySelector('[data-hero-video]');
 if(!video)return;
 const reduce=matchMedia('(prefers-reduced-motion: reduce)'),connection=navigator.connection;
 let inView=true,active=true,starting=false,disposed=false,fallbackTried=false,failed=false;
 const allowed=()=>!reduce.matches&&!connection?.saveData&&!disposed;
 const play=async()=>{
  if(!allowed()||!active||!inView||document.hidden||failed||!video.getAttribute('src'))return;
  try{await video.play();}
  catch(error){if(error.name!=='AbortError')hero.dataset.playback='awaiting-user';}
 };
 const source=(codec)=>{
  hero.dataset.codec=codec;hero.dataset.playback='loading';failed=false;
  video.muted=true;video.defaultMuted=true;video.preload='metadata';
  // Native MP4 delivery starts decoding the initial bytes; never collect the whole file in a Blob.
  video.src=video.dataset[codec];video.load();play();
 };
 const chooseCodec=async()=>{
  if(!video.dataset.av1)return 'h264';
  if(!video.dataset.h264)return 'av1';
  if(!video.canPlayType('video/mp4; codecs="av01.0.08M.08"'))return 'h264';
  try{
   const result=await Promise.race([
    navigator.mediaCapabilities.decodingInfo({type:'file',video:{contentType:'video/mp4; codecs="av01.0.08M.08"',width:1920,height:1080,bitrate:7250000,framerate:30}}),
    new Promise(resolve=>setTimeout(()=>resolve(null),350))
   ]);
   if(result?.supported&&result.smooth&&result.powerEfficient)return 'av1';
  }catch{}
  return 'h264';
 };
 const start=async()=>{
  if(!allowed()||starting||failed)return;
  if(video.getAttribute('src')){play();return;}
  starting=true;
  try{const codec=await chooseCodec();if(allowed()&&video.dataset[codec])source(codec);else hero.dataset.playback='poster';}
  finally{starting=false;}
 };
 video.addEventListener('playing',()=>{
  hero.classList.add('video-ready');hero.dataset.playback='playing';
  if(!hero.dataset.firstPlayTime){
   hero.dataset.firstPlayTime=String(video.currentTime);
   hero.dataset.firstPlayBuffered=String(video.buffered.length?video.buffered.end(0):0);
   hero.dataset.firstPlayElapsed=String(Math.round(performance.now()));
  }
 });
 video.addEventListener('waiting',()=>{if(video.getAttribute('src')&&!failed)hero.dataset.playback='buffering';});
 video.addEventListener('pause',()=>{if(video.getAttribute('src')&&!failed)hero.dataset.playback='paused';});
 video.addEventListener('error',()=>{
  if(!video.getAttribute('src')||disposed)return;
  if(hero.dataset.codec==='av1'&&!fallbackTried&&video.dataset.h264){fallbackTried=true;source('h264');return;}
  failed=true;hero.dataset.playback='poster';hero.dataset.loadError='unavailable';
  hero.classList.remove('video-ready');video.removeAttribute('src');video.load();
 });
 if('IntersectionObserver' in window)new IntersectionObserver(entries=>{
  inView=entries[0].isIntersecting;if(!inView)video.pause();else start();
 },{threshold:.08}).observe(hero);
 document.addEventListener('visibilitychange',()=>{if(document.hidden)video.pause();else start();});
 const preference=()=>{
  if(allowed()){start();return;}
  video.pause();video.removeAttribute('src');video.load();hero.classList.remove('video-ready');hero.dataset.playback='poster';
 };
 reduce.addEventListener('change',preference);connection?.addEventListener('change',preference);
 // Some mobile browsers require a gesture even for muted video. Navigation remains immediately usable.
 document.addEventListener('pointerdown',play,{passive:true});document.addEventListener('keydown',play);
 window.addEventListener('pagehide',event=>{active=false;video.pause();if(!event.persisted)disposed=true;});
 window.addEventListener('pageshow',()=>{active=true;glass();start();});
 if(allowed())start();else hero.dataset.playback='poster';
})();
