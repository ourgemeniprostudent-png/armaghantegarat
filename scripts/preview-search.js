/* Public, local search for the view-only GitHub preview. */
(() => {
 'use strict';
 const root = new URL('.', document.currentScript.src);
 const norm = value => String(value || '').toLocaleLowerCase('fa').replace(/ي/g,'ی').replace(/ك/g,'ک').replace(/\u200c/g,' ').replace(/\s+/g,' ').trim();
 const digits = value => String(value).replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
 const href = route => new URL(route.replace(/^\//,''),root).href;
 const loadIndex = () => fetch(new URL('public-index.json',root)).then(r => {if (!r.ok) throw Error('index'); return r.json();});
 const matches = (record, query) => norm(query).split(' ').filter(Boolean).every(word => norm(record.title+' '+record.body).includes(word));
 const params = () => new URLSearchParams(location.search);
 const remember = form => {
  const url = new URL(location.href);url.search = new URLSearchParams(new FormData(form)).toString();
  history.pushState(null,'',url);
 };
 document.addEventListener('DOMContentLoaded',async () => {
  const discovery = document.querySelector('[data-discovery]:not([data-media-search-form])');
  const search = document.querySelector('[data-wp-search]');
  if (!discovery && !search) return;
  let records;
  try { records = await loadIndex(); }
  catch (_) {
   const note = document.createElement('p');note.setAttribute('role','status');note.textContent='جستجوی پیش‌نمایش بارگذاری نشد؛ صفحه را دوباره باز کنید.';
   (discovery || search).after(note);return;
  }
  const sync = form => {const p=params();for (const name of ['q','topic','sort','scope']) if (form.elements[name]) form.elements[name].value=p.get(name)|| (name==='sort'?'newest':name==='scope'?'all':'');};
  if (discovery) {
   const cards = [...document.querySelectorAll('[data-public-card]')];
   const count = discovery.closest('.ag-section').querySelector('[data-results-count]');
   const empty = document.createElement('p');empty.className='ag-empty';empty.setAttribute('role','status');empty.textContent='نتیجه‌ای برای این انتخاب پیدا نشد.';empty.hidden=true;
   (count || discovery).after(empty);
   const render = () => {
    sync(discovery);const p=params();let visible=0;
    const ordered = cards.map(card=>({card,record:records.find(r=>String(r.id)===card.dataset.cardId)}));
    ordered.sort((a,b)=>p.get('sort')==='title'?String(a.record?.title).localeCompare(String(b.record?.title),'fa'):p.get('sort')==='oldest'?(a.record?.date||0)-(b.record?.date||0):(b.record?.date||0)-(a.record?.date||0));
    for (const {card,record} of ordered) {
     card.hidden=!record || !matches(record,p.get('q')) || !!(p.get('topic') && !record.topics?.includes(p.get('topic')));
     if (!card.hidden) visible++;
     card.parentElement.append(card);
    }
    if (cards.length) {if(count)count.textContent=digits(visible)+' مورد منتشرشده';empty.hidden=visible>0;}
   };
   discovery.addEventListener('submit',event=>{event.preventDefault();remember(discovery);render();});
   window.addEventListener('popstate',render);render();
  }
  if (search) {
   const output=document.querySelector('.ag-search-output');
   const count=output.querySelector('.ag-search-count');
   const results=output.querySelector('.ag-search-results');
   const empty=output.querySelector('[data-search-empty]');
   const pagination=output.querySelector('.ag-pagination');
   const render=()=>{
    sync(search);const query=[...(params().get('q')||'')].slice(0,150).join('');search.elements.q.value=query;
    const scope=['products','articles','media','pages'].includes(params().get('scope'))?params().get('scope'):'all';search.elements.scope.value=scope;
    const tokens=norm(query).split(' ').filter(Boolean);
    const found=tokens.length?records.filter(r=>r.searchable!==false && (scope==='all'||r.group===scope) && matches(r,query)):[];
    const score=r=>tokens.reduce((sum,word)=>sum+(norm(r.title).includes(word)?10:1),0);
    found.sort((a,b)=>score(b)-score(a));
    count.textContent=tokens.length?digits(found.length)+' نتیجه برای «'+query+'»':'';
    results.replaceChildren();pagination.replaceChildren();empty.hidden=found.length>0;
    const state=tokens.length?'empty':'initial';empty.querySelector('h3').textContent=empty.dataset[state+'Title'];empty.querySelector('p').textContent=empty.dataset[state+'Body'];
    const total=Math.max(1,Math.ceil(found.length/12));const current=Math.min(total,Math.max(1,parseInt(params().get('result_page'),10)||1));
    for(const record of found.slice((current-1)*12,current*12)){
     const article=document.createElement('article');article.className='ag-search-result';
     const kind=document.createElement('span');kind.className='eyebrow';kind.textContent=record.kind;
     const title=document.createElement('h3');const link=document.createElement('a');link.href=href(record.url);link.textContent=record.title;
     const arrow=document.createElement('span');arrow.className='ag-symbol';arrow.setAttribute('aria-hidden','true');
     // Clone the native vector: the preview and WordPress share the same mark.
     arrow.append(search.querySelector('.button svg').cloneNode(true));link.append(arrow);title.append(link);
     const parts=record.parts||[record.excerpt||record.body];const snippet=parts.find(part=>norm(part).includes(tokens[0]))||parts[0]||'';
     const words=snippet.split(/\s+/).filter(Boolean);const text=document.createElement('p');text.textContent=words.slice(0,35).join(' ')+(words.length>35?'…':'');
     article.append(kind,title,text);results.append(article);
    }
    if(total>1){
     const add=(number,label)=>{
      const node=document.createElement(number===current?'span':'a');node.className='page-numbers'+(number===current?' current':'');node.textContent=label;
      if(number===current)node.setAttribute('aria-current','page');
      else{const url=new URL(location.href);url.search=new URLSearchParams({q:query,scope,result_page:String(number)});node.href=url.href;}
      pagination.append(node);
     };
     if(current>1)add(current-1,'قبلی');for(let number=1;number<=total;number++)add(number,digits(number));if(current<total)add(current+1,'بعدی');
    }
   };
   const navigate=event=>{const link=event.target.closest('a');if(!link||event.button!==0||event.metaKey||event.ctrlKey||event.shiftKey||event.altKey)return;event.preventDefault();history.pushState(null,'',link.href);render();};
   search.addEventListener('submit',event=>{event.preventDefault();remember(search);render();});
   pagination.addEventListener('click',navigate);document.querySelector('.ag-search-examples').addEventListener('click',navigate);
   window.addEventListener('popstate',render);render();
  }
 });
})();
