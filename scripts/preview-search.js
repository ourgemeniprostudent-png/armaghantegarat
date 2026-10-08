/* Public, local search for the view-only GitHub preview. */
(() => {
 'use strict';
 const root = new URL('.', document.currentScript.src);
 const norm = value => String(value || '').toLocaleLowerCase('fa').replace(/ي/g,'ی').replace(/ك/g,'ک').replace(/\u200c/g,' ').replace(/\s+/g,' ').trim();
 const digits = value => String(value).replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
 const href = route => new URL(route.replace(/^\//,''),root).href;
 const index = fetch(new URL('public-index.json',root)).then(r => {if (!r.ok) throw Error('index'); return r.json();});
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
  try { records = await index; }
  catch (_) {
   const note = document.createElement('p');note.setAttribute('role','status');note.textContent='جستجوی پیش‌نمایش بارگذاری نشد؛ صفحه را دوباره باز کنید.';
   (discovery || search).after(note);return;
  }
  const sync = form => {const p=params();for (const name of ['q','topic','sort']) if (form.elements[name]) form.elements[name].value=p.get(name)|| (name==='sort'?'newest':'');};
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
   const head=search.closest('.ag-search-head');
   if(head.nextElementSibling?.classList.contains('ag-empty'))head.nextElementSibling.remove();
   const count=document.createElement('p');count.className='ag-search-count';count.setAttribute('role','status');
   const results=document.createElement('div');results.className='ag-search-results';head.after(count,results);
   const render=()=>{
    sync(search);const query=params().get('q')||'';results.replaceChildren();
    if(!norm(query)){count.textContent='عبارت موردنظر را برای جستجو در محتوای عمومی بنویسید.';return;}
    const found=records.filter(r=>r.searchable!==false && matches(r,query));count.textContent=digits(found.length)+' نتیجه برای «'+query+'»';
    for(const record of found){
     const article=document.createElement('article');article.className='ag-search-result';
     const kind=document.createElement('span');kind.className='eyebrow';kind.textContent=record.kind;
     const title=document.createElement('h3');const link=document.createElement('a');link.href=href(record.url);link.textContent=record.title;title.append(link);
     const text=document.createElement('p');text.textContent=record.excerpt||record.body.split(/\s+/).slice(0,35).join(' ');article.append(kind,title,text);results.append(article);
    }
   };
   search.addEventListener('submit',event=>{event.preventDefault();remember(search);render();});window.addEventListener('popstate',render);render();
  }
 });
})();
