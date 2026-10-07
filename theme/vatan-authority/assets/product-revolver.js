(()=>{'use strict';
const root=document.querySelector('[data-product-revolver]');if(!root)return;
const stage=root.querySelector('.revolver-stage'),lane=root.querySelector('[data-revolver-lane]');
const items=[...root.querySelectorAll('[data-revolver-item]')],copy=[...root.querySelectorAll('[data-revolver-copy]')],dots=[...root.querySelectorAll('[data-revolver-select]')],meta=[...root.querySelectorAll('[data-revolver-meta]')];
const count=items.length,reduce=matchMedia('(prefers-reduced-motion: reduce)'),mobile=matchMedia('(max-width:760px)');
const halo=['199,160,101','219,207,176','192,148,80','183,49,44','50,80,107'];
const clamp=value=>Math.max(0,Math.min(count-1,value)),now=()=>performance.now();
let index=0,stride=400,headerHeight=86,start=0,end=0,measured=false,busyUntil=0,scrollRaf=0;
let wheelAt=-Infinity,wheelStepAt=-Infinity,wheelDirection=0,wheelConsumed=false,wheelTotal=0,wheelPeak=0;
let drag=null,touch=null,lastDragAt=-Infinity;
const pinned=()=>scrollY>=start-3&&scrollY<=end+3;
const blocked=()=>document.documentElement.classList.contains('menu-open')||document.documentElement.classList.contains('video-intro-js');
function draw(){
 const gap=mobile.matches?Math.max(160,lane.clientHeight*.74):Math.max(300,lane.clientHeight*.51);
 items.forEach((item,i)=>{
  const d=i-index,depth=Math.abs(d),active=d===0;
  // Products keep their volume: a gentle orbit, never an edge-on card flip.
  const y=d*gap,x=active?0:Math.sign(d)*(mobile.matches?18:40),z=active?20:-120*depth;
  item.style.transform=`translate3d(calc(-50% + ${x}px),calc(-50% + ${y}px),${z}px) rotateX(${active?0:-Math.sign(d)*7}deg) rotateY(${active?-3:Math.sign(d)*9}deg) rotateZ(${active?-2:Math.sign(d)*8}deg) scale(${active?1:Math.max(.5,.76-depth*.08)})`;
  item.style.opacity=active?'1':depth===1?'.30':'0';item.style.zIndex=String(count-depth);item.style.pointerEvents=depth===1?'auto':'none';
  item.dataset.distance=String(d);item.classList.toggle('is-active',active);item.setAttribute('aria-hidden',String(!active));
 });
 copy.forEach((panel,i)=>{const active=i===index;panel.classList.toggle('is-active',active);panel.inert=!active;panel.setAttribute('aria-hidden',String(!active))});
 meta.forEach((panel,i)=>{panel.classList.toggle('is-active',i===index);panel.setAttribute('aria-hidden',String(i!==index))});
 dots.forEach((button,i)=>button.setAttribute('aria-current',String(i===index)));
 root.querySelectorAll('[data-revolver-step]').forEach(button=>{button.disabled=Number(button.dataset.revolverStep)<0?index===0:index===count-1});
 root.style.setProperty('--product-halo',halo[index]);root.dataset.activeIndex=String(index);
}
function go(target,source='select',align=true){
 const next=clamp(target),changed=next!==index;
 if(changed){index=next;busyUntil=now()+320;root.dataset.changeSource=source;draw()}
 // All inputs share the same document position; selection cannot desync the exit.
 if(align)window.scrollTo({top:start+index*stride,behavior:'instant'});
 return changed;
}
function measure(){
 const preserve=measured&&pinned();
 const bar=document.getElementById('wpadminbar'),admin=bar?bar.getBoundingClientRect().height:0;
 headerHeight=(innerWidth<=680?72:innerWidth<=980?78:86)+admin;
 const bottomBar=document.querySelector('.mobilebar');
 const bottomHeight=bottomBar&&getComputedStyle(bottomBar).display!=='none'?bottomBar.getBoundingClientRect().height:0;
 const height=Math.max(mobile.matches?470:600,innerHeight-headerHeight-bottomHeight);
 stride=Math.round(innerHeight*.45);
 root.style.setProperty('--revolver-header',`${headerHeight}px`);root.style.setProperty('--revolver-height',`${height}px`);root.style.setProperty('--revolver-stride',`${stride}px`);
 start=root.getBoundingClientRect().top+scrollY-headerHeight;end=start+stride*(count-1);
 if(preserve)window.scrollTo({top:start+index*stride,behavior:'instant'});
 else index=clamp(Math.round((scrollY-start)/stride));
 measured=true;draw();
}
function wheel(event){
 if(blocked()||!pinned()||event.ctrlKey||Math.abs(event.deltaX)>Math.abs(event.deltaY)||!event.deltaY)return;
 const time=now(),direction=Math.sign(event.deltaY),delta=event.deltaY*(event.deltaMode===1?16:event.deltaMode===2?innerHeight:1),magnitude=Math.abs(delta);
 // A short new gesture is enough. Only its trailing inertia is swallowed.
 const quiet=time-wheelAt>100,turned=direction!==wheelDirection;
 const renewed=time-wheelStepAt>450&&magnitude>=Math.max(6,wheelPeak*.65);
 if(quiet||turned||renewed){wheelConsumed=false;wheelTotal=0;wheelPeak=magnitude}
 wheelAt=time;wheelDirection=direction;wheelPeak=Math.max(wheelPeak,magnitude);
 if(wheelConsumed||time<busyUntil){event.preventDefault();return}
 if((index===0&&direction<0)||(index===count-1&&direction>0))return;
 event.preventDefault();wheelTotal+=delta;
 if(Math.abs(wheelTotal)>=6){wheelConsumed=true;wheelStepAt=time;go(index+direction,'wheel')}
}
window.addEventListener('wheel',wheel,{passive:false});
window.addEventListener('scroll',()=>{
 if(scrollRaf)return;scrollRaf=requestAnimationFrame(()=>{
  scrollRaf=0;if(blocked())return;
  // Native scrollbar / page keys also follow the finite five-product rail.
  const next=clamp(Math.round((scrollY-start)/stride));
  if(next!==index)go(next,'scroll',false);
 });
},{passive:true});
dots.forEach(button=>button.addEventListener('click',()=>go(Number(button.dataset.revolverSelect),'select')));
root.querySelectorAll('[data-revolver-step]').forEach(button=>button.addEventListener('click',()=>go(index+Number(button.dataset.revolverStep),'select')));
items.forEach((item,i)=>item.addEventListener('click',()=>{if(now()-lastDragAt>400)go(i,'select')}));
stage.addEventListener('keydown',event=>{
 if(event.repeat||event.metaKey||event.ctrlKey||event.altKey||event.shiftKey||event.target.closest('a,input,textarea,select'))return;
 if(event.key==='ArrowDown'||event.key==='ArrowUp'){
  event.preventDefault();const direction=event.key==='ArrowDown'?1:-1;
  if((index===0&&direction<0)||(index===count-1&&direction>0)){window.scrollBy({top:direction*innerHeight*.6,behavior:reduce.matches?'instant':'smooth'});return}
  go(index+direction,'keyboard');if(event.target.closest('.revolver-dots'))dots[index].focus({preventScroll:true});
 }
});
lane.addEventListener('pointerdown',event=>{
 if(event.pointerType!=='mouse'||event.button!==0)return;
 drag={id:event.pointerId,x:event.clientX,y:event.clientY,item:event.target.closest('[data-revolver-item]')?.dataset.revolverItem,handled:false};lane.setPointerCapture(event.pointerId);lane.classList.add('is-dragging');
});
lane.addEventListener('pointermove',event=>{
 if(!drag||drag.handled||event.pointerId!==drag.id||Math.abs(event.clientY-drag.y)<35)return;
 drag.handled=true;lastDragAt=now();go(index+(event.clientY<drag.y?1:-1),'drag');
});
const endDrag=event=>{
 if(drag&&!drag.handled&&event.type==='pointerup'&&drag.item!==undefined&&Math.hypot(event.clientX-drag.x,event.clientY-drag.y)<15){lastDragAt=now();go(Number(drag.item),'select')}
 drag=null;lane.classList.remove('is-dragging');
};
lane.addEventListener('pointerup',endDrag);lane.addEventListener('pointercancel',endDrag);lane.addEventListener('lostpointercapture',endDrag);
stage.addEventListener('touchstart',event=>{
 if(event.touches.length!==1||event.target.closest('a,button:not(.revolver-product)'))return;
 const p=event.touches[0];touch={x:p.clientX,y:p.clientY,handled:false,capture:pinned()};
},{passive:true});
window.addEventListener('touchmove',event=>{
 if(!touch||event.touches.length!==1||!touch.capture||blocked())return;
 const p=event.touches[0],dy=p.clientY-touch.y,dx=p.clientX-touch.x;
 if(Math.abs(dy)<10||Math.abs(dx)>Math.abs(dy))return;
 const direction=dy<0?1:-1;
 if(touch.handled){event.preventDefault();return}
 if((index===0&&direction<0)||(index===count-1&&direction>0))return;
 event.preventDefault();if(Math.abs(dy)>=28){touch.handled=true;lastDragAt=now();go(index+direction,'touch')}
},{passive:false});
window.addEventListener('touchend',()=>{touch=null},{passive:true});window.addEventListener('touchcancel',()=>{touch=null},{passive:true});
reduce.addEventListener('change',draw);
window.addEventListener('resize',measure,{passive:true});new ResizeObserver(measure).observe(document.querySelector('.category-strip'));
window.addEventListener('pageshow',measure);
document.querySelectorAll('a[href="#import-categories"]').forEach(link=>link.addEventListener('click',event=>{
 event.preventDefault();measure();go(0,'entry');wheelConsumed=false;wheelAt=-Infinity;
 history.replaceState(null,'','#import-categories');stage.focus({preventScroll:true});
}));
root.classList.add('revolver-enhanced');measure();
})();
