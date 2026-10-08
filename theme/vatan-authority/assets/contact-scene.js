(() => {
  'use strict';
  const scene=document.querySelector('.ag-contact-stage');
  if(!scene || !scene.querySelector('.ag-contact-sea'))return;
  const reduce=matchMedia('(prefers-reduced-motion: reduce)');
  const staticMotion=document.body.classList.contains('ag-static-motion');
  let visible=false;
  const update=()=>scene.toggleAttribute('data-contact-scene-active',visible&&!document.hidden&&!reduce.matches&&!staticMotion);
  if('IntersectionObserver' in window){
    const observer=new IntersectionObserver(entries=>{visible=entries[0].isIntersecting;update();},{threshold:.08});
    observer.observe(scene);
  }
  document.addEventListener('visibilitychange',update);
  window.addEventListener('pageshow',update);
  reduce.addEventListener('change',update);
})();
