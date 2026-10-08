(() => {
  'use strict';
  const reduce = matchMedia('(prefers-reduced-motion: reduce)');
  const staticMotion = document.body.classList.contains('ag-static-motion');
  const pages = [...document.querySelectorAll('.ag-art-page')];
  if (!pages.length) return;
  // Draw symbols instead of relying on a font's missing arrow glyph.
  document.querySelectorAll('span[aria-hidden="true"],.ag-map-art>span,.ag-side-mark,.ag-receipt-symbol').forEach(span => {
    const direction = span.textContent.trim();
    const paths = {'↖':'M20 20L4 4M4 20V4H20','↓':'M12 3V21M4 13L12 21L20 13','⤢':'M4 10V4H10M4 4L10 10M14 14L20 20M14 20H20V14'};
    if (!paths[direction]) return;
    span.textContent = ''; span.classList.add('ag-drawn-symbol');
    const svg = document.createElementNS('http://www.w3.org/2000/svg','svg');
    svg.setAttribute('viewBox','0 0 24 24'); svg.setAttribute('fill','none'); svg.setAttribute('aria-hidden','true');
    const path = document.createElementNS('http://www.w3.org/2000/svg','path');
    path.setAttribute('d',paths[direction]); path.setAttribute('stroke','currentColor'); path.setAttribute('stroke-width','1.2');
    svg.append(path); span.append(svg);
  });
  const scenes = [...document.querySelectorAll('[data-ag-progress],.ag-hero-stage')];
  const active = new Set();
  const ink = new Map();
  const stages = [...document.querySelectorAll('[data-ag-parallax]')];
  const nav = document.querySelector('.ag-section-nav');
  const links = nav ? [...nav.querySelectorAll('a[href^="#"]')] : [];
  const sections = links.map(link => ({link, section: document.getElementById(decodeURIComponent(link.hash.slice(1)))})).filter(pair => pair.section);
  let frame = 0;
  function update() {
    frame = 0;
    if (!reduce.matches && !staticMotion) {
      for (const scene of active) {
        const r = scene.getBoundingClientRect();
        const progress = Math.max(0, Math.min(1, (innerHeight * .95 - r.top) / (Math.min(r.height, innerHeight) * .8)));
        scene.style.setProperty('--art-progress', progress.toFixed(3));
        const words = ink.get(scene);
        if (words) words.forEach((word, i) => word.classList.toggle('ag-unlit', i / words.length > progress));
      }
      stages.forEach(stage => {
        const r = stage.getBoundingClientRect();
        if (r.bottom > 0 && r.top < innerHeight) stage.style.setProperty('--drift', `${Math.max(-28, Math.min(28, (innerHeight / 2 - r.top - r.height / 2) * .055))}px`);
      });
    }
    let current;
    sections.forEach(pair => { if (pair.section.getBoundingClientRect().top <= 215) current = pair; });
    sections.forEach(pair => { if (pair === current) pair.link.setAttribute('aria-current', 'location'); else pair.link.removeAttribute('aria-current'); });
  }
  const request = () => { if (!frame) frame = requestAnimationFrame(update); };
  function enable() {
    document.documentElement.classList.toggle('ag-art-motion', !reduce.matches && !staticMotion);
    if (reduce.matches || staticMotion) {
      scenes.forEach(scene => scene.style.setProperty('--art-progress', '1'));
      stages.forEach(stage => stage.style.setProperty('--drift', '0px'));
      ink.forEach(words => words.forEach(word => word.classList.remove('ag-unlit')));
    }
    request();
  }
  document.querySelectorAll('[data-ag-ink]').forEach(title => {
    const words = title.textContent.trim().split(/\s+/u);
    title.replaceChildren();
    const spans = words.map((word, index) => {
      const span = document.createElement('span'); span.className = 'ag-ink-word'; span.textContent = word;
      title.append(span); if (index < words.length - 1) title.append(document.createTextNode(' '));
      return span;
    });
    ink.set(title.closest('[data-ag-progress]'), spans);
  });
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => { if (entry.isIntersecting) active.add(entry.target); else active.delete(entry.target); }); request();
    }, {rootMargin:'100px'});
    scenes.forEach(scene => observer.observe(scene));
  } else scenes.forEach(scene => active.add(scene));
  // Keep the real heading text in the DOM; no duplicate accessible title or typewriter delay.
  document.querySelectorAll('.ag-hero-copy h1').forEach(title => {
    let index = 0;
    const wrap = node => {
      const words = node.textContent.split(/(\s+)/u); const fragment = document.createDocumentFragment();
      words.forEach(word => {
        if (!word.trim()) { fragment.append(document.createTextNode(word)); return; }
        const mask = document.createElement('span'); mask.className = 'ag-word-mask';
        const inner = document.createElement('span'); inner.textContent = word; inner.style.setProperty('--word-delay', `${Math.min(index++ * .07, .35)}s`);
        mask.append(inner); fragment.append(mask);
      });
      node.replaceWith(fragment);
    };
    [...title.childNodes].forEach(node => { if (node.nodeType === Node.TEXT_NODE) wrap(node); else [...node.childNodes].forEach(child => { if (child.nodeType === Node.TEXT_NODE) wrap(child); }); });
  });
  window.addEventListener('scroll', request, {passive:true});
  window.addEventListener('resize', request, {passive:true});
  reduce.addEventListener('change', enable); enable();
  // A live, local summary supports the form without submitting anything early.
  const form = document.querySelector('[data-inquiry-form]');
  const target = document.querySelector('[data-ag-form-summary]');
  if (form && target) {
    const summary = () => {
      const category = form.elements.category;
      const name = category.value ? category.options[category.selectedIndex].text : target.dataset.empty;
      target.textContent = [name, form.elements.quantity.value.trim(), form.elements.city.value.trim()].filter(Boolean).join(' / ');
    };
    form.addEventListener('input', summary); form.addEventListener('change', summary);
    document.addEventListener('DOMContentLoaded', summary, {once:true}); summary();
  }
})();
