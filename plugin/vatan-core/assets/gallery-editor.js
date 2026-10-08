(()=>{'use strict';document.addEventListener('click',event=>{
 const button=event.target.closest('[data-vatan-gallery]');if(!button||!window.wp?.media)return;
 const input=document.getElementById(button.dataset.vatanGallery),frame=wp.media({title:'تصاویر واقعی محصول',library:{type:'image'},multiple:true,button:{text:'ذخیره ترتیب گالری'}});
 frame.on('open',()=>{const selection=frame.state().get('selection');input.value.split(',').map(Number).filter(Boolean).forEach(id=>selection.add(wp.media.attachment(id)))});
 frame.on('select',()=>{const files=frame.state().get('selection').toJSON();input.value=files.map(f=>f.id).join(',');const preview=document.querySelector('[data-gallery-preview]');preview.replaceChildren();files.forEach(f=>{const image=document.createElement('img');image.src=f.sizes?.thumbnail?.url||f.url;image.alt=f.alt||'';image.width=80;image.height=80;preview.append(image)})});frame.open();
})})();
