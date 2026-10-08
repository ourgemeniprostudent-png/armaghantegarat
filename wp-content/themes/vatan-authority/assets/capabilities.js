(()=>{'use strict';
 const small=matchMedia('(max-width:767px)');
 document.querySelectorAll('[data-footer-accordion]').forEach(details=>{const sync=()=>{details.open=!small.matches};sync();small.addEventListener('change',sync);details.addEventListener('toggle',()=>{if(!small.matches&&!details.open)details.open=true})});
 document.querySelectorAll('[data-timestamp]').forEach(button=>button.addEventListener('click',()=>{const player=[...document.querySelectorAll('[data-episode-player]')].find(el=>!el.closest('[hidden]'));if(!player)return;const seek=()=>{player.currentTime=Number(button.dataset.timestamp);player.play().catch(()=>{})};if(player.readyState)seek();else{player.addEventListener('loadedmetadata',seek,{once:true});player.load()}}));
 const forms=[...document.querySelectorAll('[data-inquiry-form]')];
 const numeric=value=>value.replace(/[۰-۹٠-٩]/g,c=>String(c.charCodeAt(0)>0x6ef?c.charCodeAt(0)-0x6f0:c.charCodeAt(0)-0x660));
 forms.forEach(form=>{
  const product=form.elements.product,category=form.elements.category,id=form.elements.product_id,list=form.querySelector('#vatan-products-list');
  if(product&&id&&list){const sync=()=>{const match=[...list.options].find(o=>o.value===product.value&&o.dataset.category.split(',').includes(category.value));id.value=match?.dataset.id||'0'};product.addEventListener('input',sync);category.addEventListener('change',sync)}
  if(form.hasAttribute('data-contact-form')){const mobile=form.elements.mobile;mobile.addEventListener('input',()=>mobile.setCustomValidity(''));form.addEventListener('submit',event=>{const value=numeric(mobile.value).replace(/[\s\-()]/g,'').replace(/^\+98/,'0').replace(/^0098/,'0');mobile.setCustomValidity(/^09[0-9]{9}$/.test(value)?'':'شماره موبایل معتبر ایران را وارد کنید.');if(!form.checkValidity()){event.preventDefault();event.stopImmediatePropagation();form.reportValidity()}},true)}
 });
 // Campaign tags are retained only in the current tab, for at most one day, and sent with a request.
 const tags=['utm_source','utm_medium','utm_campaign','utm_term','utm_content'],params=new URLSearchParams(location.search);let attribution={};
 try{const saved=JSON.parse(sessionStorage.getItem('armaghan-attribution')||'null');if(saved&&Date.now()-saved.at<86400000)attribution=saved.values||{};const incoming={};tags.forEach(key=>{if(params.has(key))incoming[key]=params.get(key).slice(0,120)});if(Object.keys(incoming).length){attribution=incoming;sessionStorage.setItem('armaghan-attribution',JSON.stringify({at:Date.now(),values:incoming}))}}catch{}
 forms.forEach(form=>tags.forEach(key=>{if(form.elements[key]&&!form.elements[key].value)form.elements[key].value=attribution[key]||''}));
 const config=document.querySelector('[data-event-endpoint]');if(!config||document.documentElement.hasAttribute('data-static-preview'))return;
 const bar=document.querySelector('[data-analytics-consent]');let consent=false;
 try{const choice=JSON.parse(localStorage.getItem('armaghan-event-consent')||'null');if(choice&&Date.now()-choice.at<180*86400000){consent=choice.accepted;if(bar)bar.hidden=true}}catch{}
 if(bar){bar.querySelectorAll('[data-event-choice]').forEach(button=>button.addEventListener('click',()=>{consent=button.dataset.eventChoice==='yes';try{localStorage.setItem('armaghan-event-consent',JSON.stringify({accepted:consent,at:Date.now()}))}catch{}bar.hidden=true}));document.querySelectorAll('[data-event-settings]').forEach(button=>button.addEventListener('click',()=>{bar.hidden=false;bar.querySelector('button')?.focus()}))}
 const track=event=>{if(!consent)return;window.dataLayer=window.dataLayer||[];window.dataLayer.push({event});fetch(config.dataset.eventEndpoint,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({consent:true,event}),credentials:'omit',keepalive:true}).catch(()=>{})};
 document.addEventListener('click',event=>{const a=event.target.closest('a[href]');if(!a)return;if(a.href.startsWith('tel:'))track('phone_click');else if(a.hasAttribute('data-whatsapp'))track('whatsapp_click');else if(a.href.includes('/inquiry/'))track('lead_intent');else if(a.href.includes('maps'))track('map_click')});
 document.querySelectorAll('[data-map-load]').forEach(button=>button.addEventListener('click',()=>track('map_click')));
 forms.forEach(form=>{let started=false;form.addEventListener('input',()=>{if(!started){started=true;track('form_start')}},{passive:true});form.addEventListener('submit',()=>{if(form.checkValidity())track('form_submit')})});
 document.querySelectorAll('audio,video').forEach(player=>{let played=false;player.addEventListener('play',()=>{if(!played){played=true;track(player.tagName==='AUDIO'?'podcast_play':'video_play')}})});
 if(document.querySelector('[data-lead-success]'))track('lead_success');
 if(document.querySelector('[data-product-detail]'))track('product_view');
 const article=document.querySelector('[data-article-body]');let read=false;if(article&&document.body.classList.contains('single-post'))window.addEventListener('scroll',()=>{const r=article.getBoundingClientRect(),distance=Math.max(1,article.offsetHeight-innerHeight+150);if(!read&&(150-r.top)/distance>=.75){read=true;track('article_read_75')}},{passive:true});
})();
