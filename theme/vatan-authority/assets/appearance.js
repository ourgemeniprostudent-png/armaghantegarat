(() => {
 'use strict';
 const root=document.documentElement,key='armaghan-display-mode',modes=['dark','light','mono'];
 const valid=value=>modes.includes(value)?value:'dark';
 let mode='dark';try{mode=valid(localStorage.getItem(key));}catch(_){}
 const apply=(value,announce=false)=>{
  mode=valid(value);root.dataset.siteTheme=mode;root.style.colorScheme=mode==='dark'?'dark':'light';
  document.querySelectorAll('[data-appearance-toggle]').forEach(button=>{button.setAttribute('aria-checked',String(mode!=='dark'));button.setAttribute('aria-label',button.dataset.lightLabel);button.title=mode==='dark'?button.dataset.lightLabel:button.dataset.darkLabel;});
  document.querySelectorAll('[data-appearance-mono]').forEach(button=>button.setAttribute('aria-pressed',String(mode==='mono')));
  if(announce){const toggle=document.querySelector('[data-appearance-toggle]'),mono=document.querySelector('[data-appearance-mono]');document.querySelectorAll('[data-appearance-status]').forEach(status=>{status.textContent=mode==='mono'?mono?.textContent:(mode==='dark'?toggle?.dataset.darkLabel:toggle?.dataset.lightLabel);});}
 };
 const choose=value=>{apply(value,true);try{localStorage.setItem(key,mode);}catch(_){}};
 apply(mode);
 const bind=()=>{
  document.querySelectorAll('[data-appearance-switch],[data-appearance-mono]').forEach(control=>control.hidden=false);
  document.querySelectorAll('[data-appearance-toggle]').forEach(button=>button.addEventListener('click',()=>choose(mode==='dark'?'light':'dark')));
  document.querySelectorAll('[data-appearance-mono]').forEach(button=>button.addEventListener('click',()=>choose(mode==='mono'?'light':'mono')));
  apply(mode);window.addEventListener('storage',event=>{if(event.key===key)apply(event.newValue,true);});
 };
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',bind,{once:true});else bind();
})();
