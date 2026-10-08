(() => {
  'use strict';
  const reduce = matchMedia('(prefers-reduced-motion: reduce)');
  const staticMotion = document.body.classList.contains('ag-static-motion');
  const nodes = [...document.querySelectorAll('[data-ag-reveal]')];
  if (!reduce.matches && !staticMotion && 'IntersectionObserver' in window) {
    const observer = new IntersectionObserver(entries => entries.forEach(entry => {
      if (entry.isIntersecting) { entry.target.classList.add('ag-visible'); observer.unobserve(entry.target); }
    }), { threshold: .06 });
    document.documentElement.classList.add('ag-motion-ready');
    nodes.forEach(node => { node.classList.add('ag-waiting'); observer.observe(node); });
    reduce.addEventListener('change', () => { if (reduce.matches || staticMotion) { document.documentElement.classList.remove('ag-motion-ready'); observer.disconnect(); } });
  }
  const zoom = document.querySelector('.ag-zoom-dialog'); let returnFocus;
  if (zoom && typeof zoom.showModal === 'function') {
    document.querySelectorAll('[data-ag-zoom]').forEach(button => button.addEventListener('click', () => {
      const source = button.querySelector('img'); const image = zoom.querySelector('img');
      image.src = source.currentSrc || source.src; image.alt = source.alt;
      zoom.querySelector('p').textContent = source.alt; returnFocus = button; zoom.showModal();
    }));
    zoom.querySelector('button').addEventListener('click', () => zoom.close());
    zoom.addEventListener('click', event => { if (event.target === zoom) { const r = zoom.getBoundingClientRect(); if (event.clientX < r.left || event.clientX > r.right || event.clientY < r.top || event.clientY > r.bottom) zoom.close(); } });
    zoom.addEventListener('close', () => returnFocus?.focus({ preventScroll: true }));
  }
  const filter = document.querySelector('[data-faq-filter]');
  if (filter) filter.addEventListener('input', () => {
    const normalize = text => text.replace(/ي/g, 'ی').replace(/ك/g, 'ک').replace(/\u200c/g, ' ').trim();
    const value = normalize(filter.value); let visible = 0;
    document.querySelectorAll('.ag-faq').forEach(row => { row.hidden = !normalize(row.textContent).includes(value); if (!row.hidden) visible++; });
    document.querySelector('[data-faq-count]').textContent = `${visible.toLocaleString('fa-IR')} پرسش`;
    document.querySelector('.ag-filter-empty').hidden = visible > 0;
  });
  const copy = document.querySelector('[data-copy-reference]');
  if (copy) copy.addEventListener('click', async () => {
    const text = document.querySelector('[data-reference]').textContent;
    const status = document.querySelector('[data-copy-status]');
    try { await navigator.clipboard.writeText(text); status.textContent = 'کد پیگیری کپی شد.'; }
    catch { const selection = window.getSelection(); const range = document.createRange(); range.selectNodeContents(document.querySelector('[data-reference]')); selection.removeAllRanges(); selection.addRange(range); status.textContent = 'کد انتخاب شد؛ آن را کپی یا یادداشت کنید.'; }
  });
  const article = document.querySelector('[data-article-body]'); const toc = document.querySelector('[data-article-toc]');
  if (article && toc) {
    const headings = [...article.querySelectorAll('h2,h3')];
    headings.forEach((heading, index) => { if (!heading.id) heading.id = `article-part-${index + 1}`; const a = document.createElement('a'); a.href = `#${heading.id}`; a.textContent = heading.textContent; if(![...toc.querySelectorAll('a')].some(link=>link.hash===a.hash))toc.append(a); });
    if (!headings.length) toc.hidden = true;
    const update = () => {
      const r = article.getBoundingClientRect(); const distance = Math.max(1, article.offsetHeight - innerHeight + 150); const progress = Math.max(0, Math.min(1, (150 - r.top) / distance));
      document.documentElement.style.setProperty('--reading', `${progress * 100}%`);
      let current = headings[0]; headings.forEach(h => { if (h.getBoundingClientRect().top < 190) current = h; });
      toc.querySelectorAll('a').forEach(a => { if (current && a.hash === `#${current.id}`) a.setAttribute('aria-current', 'location'); else a.removeAttribute('aria-current'); });
    };
    window.addEventListener('scroll', update, { passive: true }); update();
  }
  const form = document.querySelector('[data-inquiry-form]');
  if (!form) return;
  const steps = [...form.querySelectorAll('[data-form-step]')]; if (steps.length !== 3) return;
  const buttons = [...form.querySelectorAll('[data-form-go]')]; const next = form.querySelector('[data-form-next]'); const back = form.querySelector('[data-form-back]'); const submit = form.querySelector('[type=submit]');
  let active = 0, completed = 0;
  const invalid = form.querySelector('[aria-invalid=true]'); if (invalid) active = Math.max(0, steps.findIndex(step => step.contains(invalid)));
  completed = active;
  const convertDigits = value => value.replace(/[۰-۹٠-٩]/g, char => String(char.charCodeAt(0) >= 0x6f0 ? char.charCodeAt(0) - 0x6f0 : char.charCodeAt(0) - 0x660));
  const mobile = form.elements.mobile;
  mobile.addEventListener('input', () => mobile.setCustomValidity(''));
  const validate = (index, report = true) => {
    if (steps[index].contains(mobile)) {
      let value = convertDigits(mobile.value).replace(/[\s\-()]/g, '').replace(/^\+98/, '0').replace(/^0098/, '0');
      mobile.setCustomValidity(/^09[0-9]{9}$/.test(value) ? '' : 'شماره موبایل معتبر ایران را وارد کنید.');
    }
    for (const field of steps[index].querySelectorAll('input,select,textarea')) if (!field.checkValidity()) { if (report) { field.reportValidity(); field.focus(); } return false; }
    return true;
  };
  const review = () => {
    const list = form.querySelector('[data-form-review]'); list.replaceChildren();
    ['category','product','quantity','notes','name','mobile','company','city','customer_type','preferred_time'].forEach(key => {
      const field = form.elements[key]; if (!field.value) return;
      const row = document.createElement('div'); const dt = document.createElement('dt'); const dd = document.createElement('dd');
      dt.textContent = form.querySelector(`label[for="${field.id}"]`).textContent.replace('*', '').trim();
      dd.textContent = field.tagName === 'SELECT' ? field.options[field.selectedIndex].text : field.value;
      row.append(dt, dd); list.append(row);
    });
  };
  const draw = (focus = true) => {
    steps.forEach((step, index) => { step.hidden = index !== active; });
    buttons.forEach((button, index) => { button.disabled = index > completed; if (index === active) button.setAttribute('aria-current','step'); else button.removeAttribute('aria-current'); });
    back.hidden = active === 0; next.hidden = active === 2; submit.hidden = active !== 2;
    if (active === 2) review();
    if (focus) { steps[active].querySelector('legend').focus({ preventScroll: true }); form.scrollIntoView({ behavior: reduce.matches ? 'instant' : 'smooth', block: 'start' }); }
  };
  document.querySelectorAll('.form-status a').forEach(link => link.addEventListener('click', () => { const target = document.getElementById(link.hash.slice(1)); const index = steps.findIndex(step => step.contains(target)); if(index >= 0) { active = index; completed = Math.max(completed, active); draw(false); } }));
  form.classList.add('ag-form-enhanced'); form.noValidate = true; draw(false);
  next.addEventListener('click', () => { if (validate(active)) { active++; completed = Math.max(completed, active); draw(); } });
  back.addEventListener('click', () => { active = Math.max(0, active - 1); draw(); });
  buttons.forEach((button,index) => button.addEventListener('click', () => { if(index <= completed) { active = index; draw(); } }));
  form.addEventListener('keydown', event => { if (event.key === 'Enter' && active < 2 && event.target.tagName === 'INPUT') { event.preventDefault(); next.click(); } });
  // Capture precedes the legacy submit button guard: invalid steps must remain editable.
  form.addEventListener('submit', event => {
    for (let i = 0; i < 3; i++) if (!validate(i, false)) { event.preventDefault(); event.stopImmediatePropagation(); active = i; draw(false); const invalid = steps[i].querySelector(':invalid'); invalid?.focus(); invalid?.reportValidity(); return; }
    submit.disabled = true; submit.textContent = 'در حال ثبت درخواست…';
  }, true);
})();
