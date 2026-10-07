(()=>{'use strict';
const root=document.querySelector('[data-cooperation-flow]');if(!root)return;
const stage=root.querySelector('.flow-stage'),lane=root.querySelector('[data-flow-lane]'),cards=[...root.querySelectorAll('[data-flow-card]')],copy=[...root.querySelectorAll('[data-flow-copy]')],dots=[...root.querySelectorAll('[data-flow-select]')],dialog=root.querySelector('.flow-dialog');
const reduce=matchMedia('(prefers-reduced-motion: reduce)'),mobile=matchMedia('(max-width:760px)'),count=cards.length,now=()=>performance.now(),clamp=n=>Math.max(0,Math.min(count-1,n));
let index=0,start=0,end=0,stride=400,measured=false,busyUntil=0,scrollRaf=0,pointerRaf=0,drag=null,touch=null,lastPointerAt=-Infinity;
let wheelAt=-Infinity,wheelStepAt=-Infinity,wheelDirection=0,wheelConsumed=false,wheelTotal=0,wheelPeak=0,returnFocus=null;
const pinned=()=>scrollY>=start-3&&scrollY<=end+3;
const blocked=()=>dialog.open||document.documentElement.classList.contains('menu-open')||document.documentElement.classList.contains('video-intro-js');
const distance=(item,active)=>{let d=(item-active+count)%count;if(d>count/2)d-=count;return d};
function draw(initial=false){
 const width=lane.clientWidth,height=lane.clientHeight;
 const sideScale=mobile.matches?.49:Math.min(365,Math.max(285,width*.25))*.55/cards[0].offsetWidth;
 cards.forEach((card,i)=>{
  const d=distance(i,index),old=Number(card.dataset.distance||0),active=d===0,far=Math.abs(d)>1;
  const recycle=!initial&&Math.abs(old-d)>count/2;if(recycle)card.classList.add('flow-recycle');
  const x=d*(mobile.matches?width*.39:width*.285),y=d*(mobile.matches?height*.15:height*.245);
  card.style.transform=`translate3d(calc(-50% + ${x}px),calc(-50% + ${y}px),${active?30:-55*Math.abs(d)}px) rotateY(${active?-2:Math.sign(d)*9}deg) rotateZ(${active?-1:Math.sign(d)*10}deg) scale(${active?1:far?.38:sideScale})`;
  card.style.opacity=active?'1':far?'0':'.74';card.style.zIndex=String(4-Math.abs(d));card.style.pointerEvents=far?'none':'auto';card.dataset.distance=String(d);card.classList.toggle('is-active',active);card.tabIndex=active?0:-1;card.setAttribute('aria-hidden',String(!active));
  if(recycle)requestAnimationFrame(()=>requestAnimationFrame(()=>card.classList.remove('flow-recycle')));
 });
 copy.forEach((p,i)=>{p.classList.toggle('is-active',i===index);p.setAttribute('aria-hidden',String(i!==index))});
 dots.forEach((b,i)=>b.setAttribute('aria-current',String(i===index)));
 root.querySelectorAll('[data-flow-step]').forEach(b=>{b.disabled=Number(b.dataset.flowStep)<0?index===0:index===count-1});
 root.dataset.activeIndex=String(index);
}
function go(target,source='select',align=true){
 const next=clamp(target);if(next!==index){index=next;root.dataset.changeSource=source;busyUntil=now()+320;draw();lane.style.setProperty('--flow-bend',`${source==='wheel'?.55:0}deg`)}
 if(align)window.scrollTo({top:start+index*stride,behavior:'instant'});
}
function measure(){
 const preserve=measured&&pinned(),bar=document.getElementById('wpadminbar'),admin=bar?bar.getBoundingClientRect().height:0,header=(innerWidth<=680?72:innerWidth<=980?78:86)+admin;
 const bottom=document.querySelector('.mobilebar'),bottomHeight=bottom&&getComputedStyle(bottom).display!=='none'?bottom.getBoundingClientRect().height:0;
 const height=Math.max(mobile.matches?470:590,innerHeight-header-bottomHeight);stride=Math.round(innerHeight*.45);
 root.style.setProperty('--flow-header',`${header}px`);root.style.setProperty('--flow-height',`${height}px`);root.style.setProperty('--flow-stride',`${stride}px`);
 start=root.getBoundingClientRect().top+scrollY-header;end=start+stride*(count-1);
 if(preserve)window.scrollTo({top:start+index*stride,behavior:'instant'});else index=clamp(Math.round((scrollY-start)/stride));
 measured=true;draw(true);
}
function wheel(e){
 if(blocked()||!pinned()||e.ctrlKey||Math.abs(e.deltaX)>Math.abs(e.deltaY)||!e.deltaY)return;
 const time=now(),direction=Math.sign(e.deltaY),delta=e.deltaY*(e.deltaMode===1?16:e.deltaMode===2?innerHeight:1),magnitude=Math.abs(delta);
 const fresh=time-wheelAt>100||direction!==wheelDirection||(time-wheelStepAt>450&&magnitude>=Math.max(6,wheelPeak*.65));
 if(fresh){wheelConsumed=false;wheelTotal=0;wheelPeak=magnitude}wheelAt=time;wheelDirection=direction;wheelPeak=Math.max(wheelPeak,magnitude);
 if(wheelConsumed||time<busyUntil){e.preventDefault();return}
 if((index===0&&direction<0)||(index===count-1&&direction>0))return;
 e.preventDefault();wheelTotal+=delta;if(Math.abs(wheelTotal)>=6){wheelConsumed=true;wheelStepAt=time;go(index+direction,'wheel')}
}
window.addEventListener('wheel',wheel,{passive:false});
window.addEventListener('scroll',()=>{if(scrollRaf)return;scrollRaf=requestAnimationFrame(()=>{scrollRaf=0;if(blocked())return;const next=clamp(Math.round((scrollY-start)/stride));if(next!==index)go(next,'scroll',false)})},{passive:true});
dots.forEach(b=>b.addEventListener('click',()=>go(Number(b.dataset.flowSelect))));
root.querySelectorAll('[data-flow-step]').forEach(b=>b.addEventListener('click',()=>go(index+Number(b.dataset.flowStep))));
function openImage(i){
 if(i!==index){go(i);return}
 const card=cards[i],image=card.querySelector('img');returnFocus=card;
 const main=dialog.querySelector('[data-flow-dialog-image]');main.src=image.currentSrc||image.src;main.alt=image.alt;
 dialog.querySelector('[data-flow-dialog-ambient]').src=main.src;dialog.querySelector('#flow-dialog-title').textContent=card.dataset.title;dialog.querySelector('[data-flow-dialog-description]').textContent=card.dataset.description;
 document.documentElement.classList.add('flow-dialog-open');dialog.showModal();dialog.querySelector('[data-flow-close]').focus({preventScroll:true});
}
cards.forEach((card,i)=>card.addEventListener('click',e=>{if(e.detail===0||now()-lastPointerAt>400)openImage(i)}));
dialog.querySelector('[data-flow-close]').addEventListener('click',()=>dialog.close());
dialog.addEventListener('close',()=>{document.documentElement.classList.remove('flow-dialog-open');returnFocus?.focus({preventScroll:true})});
stage.addEventListener('keydown',e=>{
 if(e.repeat||e.metaKey||e.ctrlKey||e.altKey||e.shiftKey||e.target.closest('a,input,textarea,select'))return;
 const direction=['ArrowRight','ArrowDown'].includes(e.key)?1:['ArrowLeft','ArrowUp'].includes(e.key)?-1:0;
 if(!direction)return;e.preventDefault();
 if((index===0&&direction<0)||(index===count-1&&direction>0)){window.scrollBy({top:direction*innerHeight*.6,behavior:reduce.matches?'instant':'smooth'});return}
 go(index+direction,'keyboard');if(e.target.closest('.flow-dots'))dots[index].focus({preventScroll:true});
});
lane.addEventListener('pointerdown',e=>{if(e.pointerType!=='mouse'||e.button!==0)return;drag={id:e.pointerId,x:e.clientX,y:e.clientY,card:e.target.closest('[data-flow-card]')?.dataset.flowCard,handled:false};lane.setPointerCapture(e.pointerId);lane.classList.add('is-dragging')});
lane.addEventListener('pointermove',e=>{
 if(drag&&e.pointerId===drag.id){const dx=e.clientX-drag.x,dy=e.clientY-drag.y;if(!drag.handled&&Math.max(Math.abs(dx),Math.abs(dy))>=35){drag.handled=true;lastPointerAt=now();go(index+((Math.abs(dx)>Math.abs(dy)?dx:dy)<0?1:-1),'drag')}return}
 if(reduce.matches||e.pointerType!=='mouse'||pointerRaf)return;
 const bounds=lane.getBoundingClientRect(),lean=(e.clientX-bounds.left-bounds.width/2)/bounds.width;
 pointerRaf=requestAnimationFrame(()=>{pointerRaf=0;lane.style.setProperty('--flow-bend',`${Math.max(-.7,Math.min(.7,lean*1.4))}deg`)});
});
const endDrag=e=>{if(drag&&!drag.handled&&e.type==='pointerup'&&drag.card!==undefined&&Math.hypot(e.clientX-drag.x,e.clientY-drag.y)<15){lastPointerAt=now();openImage(Number(drag.card))}drag=null;lane.classList.remove('is-dragging')};
lane.addEventListener('pointerup',endDrag);lane.addEventListener('pointercancel',endDrag);lane.addEventListener('lostpointercapture',endDrag);lane.addEventListener('pointerleave',()=>{lane.style.setProperty('--flow-bend','0deg')});
stage.addEventListener('touchstart',e=>{if(e.touches.length!==1||e.target.closest('a,button:not(.flow-card)'))return;const p=e.touches[0];touch={x:p.clientX,y:p.clientY,handled:false,capture:pinned()}},{passive:true});
window.addEventListener('touchmove',e=>{
 if(!touch||e.touches.length!==1||!touch.capture||blocked())return;const p=e.touches[0],dx=p.clientX-touch.x,dy=p.clientY-touch.y;
 if(Math.max(Math.abs(dx),Math.abs(dy))<10)return;const horizontal=Math.abs(dx)>Math.abs(dy),direction=(horizontal?dx:dy)<0?1:-1;
 if(touch.handled){e.preventDefault();return}if(!horizontal&&((index===0&&direction<0)||(index===count-1&&direction>0)))return;
 e.preventDefault();if(Math.max(Math.abs(dx),Math.abs(dy))>=28){touch.handled=true;lastPointerAt=now();go(index+direction,'touch')}
},{passive:false});
window.addEventListener('touchend',()=>{touch=null},{passive:true});window.addEventListener('touchcancel',()=>{touch=null},{passive:true});
window.addEventListener('resize',measure,{passive:true});new ResizeObserver(measure).observe(document.querySelector('.category-strip'));window.addEventListener('pageshow',measure);reduce.addEventListener('change',()=>{lane.style.setProperty('--flow-bend','0deg');draw(true)});
root.classList.add('flow-enhanced');measure();
})();
