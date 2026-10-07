(() => {
  'use strict';
  function revealHash() { const node = document.getElementById(location.hash.slice(1)); if (node?.tagName === 'DETAILS') node.open = true; }
  document.addEventListener('click', event => {
    const pick = event.target.closest('.ag-pick-media');
    if (pick && window.wp?.media) {
      const input = document.getElementById(pick.dataset.target);
      const frame = wp.media({ title: pick.dataset.mediaType === 'video' ? 'انتخاب ویدیو' : 'انتخاب تصویر', button: { text: 'استفاده در این سکشن' }, library: { type: pick.dataset.mediaType }, multiple: false });
      frame.on('select', () => {
        const file = frame.state().get('selection').first().toJSON();
        input.value = `attachment:${file.id}`;
        const preview = document.querySelector(`[data-preview-for="${input.id}"]`);
        preview.replaceChildren();
        const media = document.createElement(pick.dataset.mediaType === 'image' ? 'img' : 'a');
        if (media.tagName === 'IMG') { media.src = file.url; media.alt = ''; }
        else { media.href = file.url; media.textContent = 'دیدن فایل ویدیو'; media.target = '_blank'; media.rel = 'noopener'; }
        preview.append(media);input.dispatchEvent(new Event('change', { bubbles: true }));
      }); frame.open();
    }
    const clear = event.target.closest('.ag-clear-media');
    if (clear) { const input = document.getElementById(clear.dataset.target); input.value = ''; input.dispatchEvent(new Event('change', { bubbles: true })); document.querySelector(`[data-preview-for="${input.id}"]`).replaceChildren(); }
    const link = event.target.closest('.ag-editor-nav a');
    if (link) { const node = document.querySelector(link.getAttribute('href')); if (node) node.open = true; }
  });
  window.addEventListener('hashchange', revealHash); revealHash();
})();
