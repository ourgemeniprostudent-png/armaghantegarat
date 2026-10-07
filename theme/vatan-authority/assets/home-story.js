(()=>{'use strict';
 const reduce=matchMedia('(prefers-reduced-motion: reduce)'),items=[...document.querySelectorAll('[data-home-reveal]')],manifesto=document.querySelector('[data-home-manifesto]');
 if(!items.length&&!manifesto)return;
 let observer=null,raf=0;
 function reveal(){
  observer?.disconnect();
  if(reduce.matches||!('IntersectionObserver' in window)){items.forEach(el=>{el.classList.remove('home-waiting');el.classList.add('home-visible')});return}
  document.documentElement.classList.add('home-motion');
  observer=new IntersectionObserver(entries=>entries.forEach(entry=>{if(entry.isIntersecting){entry.target.classList.remove('home-waiting');entry.target.classList.add('home-visible');observer.unobserve(entry.target)}}),{threshold:.07,rootMargin:'0px 0px -24px 0px'});
  items.forEach(el=>{if(!el.classList.contains('home-visible'))el.classList.add('home-waiting');observer.observe(el)});
 }
 function update(){raf=0;if(!manifesto)return;if(reduce.matches){manifesto.style.setProperty('--manifesto-drift','0px');return}const r=manifesto.getBoundingClientRect();if(r.bottom<0||r.top>innerHeight)return;const progress=Math.max(-1,Math.min(1,(innerHeight/2-r.top-r.height/2)/(innerHeight/2+r.height/2)));manifesto.style.setProperty('--manifesto-drift',`${(progress*(innerWidth<=680?7:23)).toFixed(2)}px`)}
 const schedule=()=>{if(!raf)raf=requestAnimationFrame(update)};
 window.addEventListener('scroll',schedule,{passive:true});window.addEventListener('resize',schedule,{passive:true});reduce.addEventListener('change',()=>{reveal();schedule()});window.addEventListener('pageshow',()=>{reveal();schedule()});reveal();schedule();
})();
