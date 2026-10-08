(()=>{'use strict';
const toggle=document.querySelector('.menu-toggle'), nav=document.querySelector('#site-nav');
if(toggle&&nav){
 const mobile=matchMedia('(max-width:980px)');
 const backgrounds=[document.getElementById('main'),document.querySelector('.site-footer'),document.querySelector('.mobilebar'),document.querySelector('.site-header .brand'),document.querySelector('.header-inquiry')].filter(Boolean);
 const savedInert=new Map();
 let opened=false;
 const syncHidden=()=>{nav.inert=mobile.matches&&!opened;if(mobile.matches)nav.setAttribute('aria-hidden',String(!opened));else nav.removeAttribute('aria-hidden')};
 const close=(restoreFocus=true)=>{
  if(!opened)return;
  opened=false;toggle.setAttribute('aria-expanded','false');toggle.setAttribute('aria-label','باز کردن منو');
  nav.classList.remove('is-open');nav.removeAttribute('role');nav.removeAttribute('aria-modal');
  document.documentElement.classList.remove('menu-open');document.body.classList.remove('menu-open');
  backgrounds.forEach(el=>{el.inert=savedInert.get(el)||false});savedInert.clear();syncHidden();
  if(restoreFocus&&mobile.matches)toggle.focus({preventScroll:true});
 };
 const open=()=>{
  if(!mobile.matches)return;
  opened=true;toggle.setAttribute('aria-expanded','true');toggle.setAttribute('aria-label','بستن منو');
  nav.classList.add('is-open');nav.setAttribute('role','dialog');nav.setAttribute('aria-modal','true');
  document.documentElement.classList.add('menu-open');document.body.classList.add('menu-open');
  backgrounds.forEach(el=>{savedInert.set(el,el.inert);el.inert=true});syncHidden();
  requestAnimationFrame(()=>{if(opened)nav.querySelector('a[href]')?.focus({preventScroll:true})});
 };
 toggle.addEventListener('click',()=>opened?close():open());
 nav.addEventListener('click',e=>{if(e.target.closest('a[href]'))close(false)});
 document.addEventListener('keydown',e=>{
  if(!opened)return;
  if(e.key==='Escape'){e.preventDefault();close();return}
  if(e.key!=='Tab')return;
  const items=[toggle,...nav.querySelectorAll('a[href],button:not([disabled])')].filter(el=>el.getClientRects().length&&!el.inert);
  const index=items.indexOf(document.activeElement);
  if(e.shiftKey&&(index<=0)){e.preventDefault();items.at(-1)?.focus()}
  else if(!e.shiftKey&&(index===items.length-1||index<0)){e.preventDefault();items[0]?.focus()}
 });
 mobile.addEventListener('change',()=>{if(!mobile.matches)close(false);syncHidden()});
 window.addEventListener('pagehide',()=>close(false));syncHidden();
}
const form=document.querySelector('[data-inquiry-form]');if(form&&!form.hasAttribute('data-approved-form')){form.addEventListener('submit',()=>{if(form.checkValidity()){const button=form.querySelector('[type=submit]');button.disabled=true;button.textContent='در حال ثبت درخواست…'}})}document.querySelectorAll('[data-map-load]').forEach(button=>button.addEventListener('click',()=>{const frame=document.createElement('iframe');frame.className='map-frame';frame.title='موقعیت دفتر ارمغان تجارت وطن';frame.src=button.dataset.mapLoad;frame.loading='lazy';frame.referrerPolicy='no-referrer';button.replaceWith(frame)}));})();
