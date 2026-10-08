(()=>{'use strict';
 const root=document.querySelector('.at-approved');if(!root)return;
 const reduced=matchMedia('(prefers-reduced-motion:reduce)');
 if(!reduced.matches&&!document.body.classList.contains('ag-static-motion')&&'IntersectionObserver'in window){root.classList.add('motion');const observer=new IntersectionObserver(entries=>entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('visible');observer.unobserve(e.target)}}),{threshold:.05});root.querySelectorAll('.reveal').forEach(el=>{el.classList.add('waiting');observer.observe(el)});reduced.addEventListener('change',()=>{root.classList.remove('motion');observer.disconnect()});}
 const controls=root.querySelector('[data-journal-controls]');if(controls){const rows=[...root.querySelectorAll('[data-journal-row]')],count=root.querySelector('[data-journal-count]'),empty=root.querySelector('[data-journal-empty]'),normalize=s=>s.replace(/ي/g,'ی').replace(/ك/g,'ک').replace(/\s+/g,' ').trim().toLowerCase();const filter=()=>{const topic=controls.elements.topic.value,q=normalize(controls.elements.q.value);let total=0;rows.forEach(row=>{row.hidden=!((!topic||row.dataset.topics.split(' ').includes(topic))&&(!q||normalize(row.dataset.search).includes(q)));if(!row.hidden)total++});count.textContent=total.toLocaleString('fa-IR')+' مقاله';empty.hidden=total>0;};controls.addEventListener('submit',e=>{e.preventDefault();filter();const url=new URL(location);for(const k of ['topic','q']){const v=controls.elements[k].value;if(v)url.searchParams.set(k,v);else url.searchParams.delete(k)}history.replaceState(null,'',url)});controls.addEventListener('input',filter);controls.addEventListener('change',filter);filter();}
 const form=root.querySelector('[data-approved-form]');if(!form)return;form.noValidate=true;
 if('IntersectionObserver'in window){const observer=new IntersectionObserver(entries=>{document.body.classList.toggle('at-form-active',entries[0].isIntersecting)}, {threshold:.05});observer.observe(form);}
 const panels=[...form.querySelectorAll('.wizard-panel')],tabs=[...form.querySelectorAll('.step-tab')];let current=0,furthest=0;
 const units={kg:'کیلوگرم',ton:'تن',package:'بسته'},categories={coffee:'قهوه',rice:'برنج','dried-fruits':'خشکبار',spices:'ادویه',legumes:'حبوبات'};
 const submit=form.querySelector('[type=submit]');const savedTitle=submit?.innerHTML;
 function populate(){const data=new FormData(form),target=form.querySelector('#request-review');if(!target)return;target.replaceChildren();const labels={category:'گروه محصول',product:'نام محصول',quantity:'مقدار',city:'مقصد',name:'نام',mobile:'شماره همراه',company:'کسب‌وکار',delivery_time:'زمان تحویل',packaging:'بسته‌بندی',notes:'توضیحات'};for(const [key,label]of Object.entries(labels)){let value=String(data.get(key)||'').trim();if(!value)continue;if(key==='quantity')value+=' '+(units[data.get('quantity_unit')]||'');if(key==='category')value=categories[value]||value;const line=document.createElement('div'),dt=document.createElement('dt'),dd=document.createElement('dd');line.className='review-line';dt.textContent=label;dd.textContent=value;if(key==='mobile')dd.dir='ltr';line.append(dt,dd);target.append(line)}}
 function go(index,focus=true){current=index;furthest=Math.max(furthest,index);panels.forEach((panel,i)=>panel.hidden=i!==index);tabs.forEach((tab,i)=>{tab.disabled=i>furthest;tab.setAttribute('aria-current',i===index?'step':'false')});if(index===2)populate();if(focus){panels[index].querySelector('h3').focus({preventScroll:true});form.scrollIntoView({block:'start',behavior:reduced.matches?'instant':'smooth'})}}
 function valid(index){const ok=window.ATForms.validate(panels[index],false);if(!ok){if(current!==index)go(index);window.ATForms.validate(panels[index],true)}return ok}
 if(panels.length){
  // All stages are exposed in the server HTML; only enhanced clients hide later stages.
  form.classList.add('ready');go(0,false);
  form.querySelectorAll('[data-next]').forEach(b=>b.addEventListener('click',()=>{if(valid(current))go(Math.min(2,current+1))}));form.querySelectorAll('[data-back]').forEach(b=>b.addEventListener('click',()=>go(Math.max(0,current-1))));form.querySelector('[data-edit-product]')?.addEventListener('click',()=>go(0));
  form.querySelector('[data-edit-contact]')?.addEventListener('click',()=>go(1));
  tabs.forEach((tab,i)=>tab.addEventListener('click',()=>{if(i<=current)go(i);else{for(let j=0;j<i;j++)if(!valid(j))return;go(i)}}));
  form.elements.product?.addEventListener('input',()=>{form.elements.product_id.value='0';});
  form.querySelectorAll('[name=category]').forEach(radio=>radio.addEventListener('change',()=>{form.elements.product_id.value='0';const info=[radio.dataset.productLabel,radio.dataset.productHint];if(info){form.querySelector('#product-name-label').textContent=info[0];form.elements.product.placeholder=info[1]}}));
  form.addEventListener('keydown',e=>{if(e.key==='Enter'&&e.target.tagName==='INPUT'&&!['checkbox','radio'].includes(e.target.type)){e.preventDefault();if(current<2&&valid(current))go(current+1)}});
 }
 form.addEventListener('submit',event=>{
  if(panels.length){for(let i=0;i<panels.length;i++)if(!valid(i)){event.preventDefault();return;}}
  else if(!window.ATForms.validate(form)){event.preventDefault();return;}
  if(document.documentElement.hasAttribute('data-static-preview'))return;
  if(submit){submit.disabled=true;submit.textContent='در حال ثبت…';}
 });
 // Server errors preserve entered data and reopen their actual step.
 const invalid=form.querySelector('[aria-invalid=true]');if(invalid&&panels.length){const index=panels.findIndex(p=>p.contains(invalid));if(index>=0){invalid.closest('details')?.setAttribute('open','');go(index,false);}}
 addEventListener('pageshow',()=>{if(submit){submit.disabled=false;submit.innerHTML=savedTitle;}});
})();
