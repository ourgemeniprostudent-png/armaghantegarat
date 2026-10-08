(() => {
  'use strict';
  // A small software 3D renderer: all faces have world-space depth, lighting and
  // perspective. No library, model download, video, WebGL dependency or tracking.
  const root=document.querySelector('[data-vessel]');
  if(!root)return;
  const canvas=root.querySelector('canvas'), ctx=canvas.getContext('2d');
  if(!ctx)return;
  const reduce=matchMedia('(prefers-reduced-motion: reduce)');
  const fixed=document.body.classList.contains('ag-static-motion');
  const gold=getComputedStyle(root).getPropertyValue('--at-gold').trim()||'#c39a5b';
  let hex=gold.replace('#','');if(hex.length===3)hex=[...hex].map(c=>c+c).join('');
  const rgb=hex.match(/.{2}/g).map(v=>parseInt(v,16));
  const faces=[];
  const face=(p,tone=1,edge=true)=>faces.push({p,tone,edge});
  function box(x,y,z,w,h,d,tone=1,ribs=false){
    const p=[[x,y,z],[x+w,y,z],[x+w,y+h,z],[x,y+h,z],[x,y,z+d],[x+w,y,z+d],[x+w,y+h,z+d],[x,y+h,z+d]];
    [[0,1,2,3],[4,7,6,5],[0,3,7,4],[1,5,6,2],[3,2,6,7]].forEach((ids,i)=>face(ids.map(n=>p[n]),tone*[.65,.8,.72,1,1.24][i]));
    if(ribs)for(let n=1;n<7;n++){
      const zz=z+d*n/7;
      face([[x-.003,y+.035,zz],[x-.003,y+h-.02,zz],[x-.009,y+h-.02,zz+.012],[x-.009,y+.035,zz+.012]],tone*.42,false);
      face([[x+w+.003,y+.035,zz],[x+w+.003,y+h-.02,zz],[x+w+.009,y+h-.02,zz+.012],[x+w+.009,y+.035,zz+.012]],tone*.42,false);
    }
    if(ribs)for(let n=1;n<5;n++){
      const xx=x+w*n/5;
      face([[xx,y+.025,z-.004],[xx+.014,y+.025,z-.004],[xx+.014,y+h-.02,z-.004],[xx,y+h-.02,z-.004]],tone*.49,false);
    }
  }
  const stations=[[-3.8,.025],[-3.1,.74],[-2.45,1.03],[2.7,1.03],[3.25,.83]];
  for(let i=0;i<stations.length-1;i++){
    const [z,w]=stations[i],[zz,ww]=stations[i+1];
    face([[-w,.08,z],[w,.08,z],[ww,.08,zz],[-ww,.08,zz]],1.1);
    for(const side of [-1,1]){
      face([[side*w,.08,z],[side*ww,.08,zz],[side*ww*.8,-.7,zz],[side*w*.8,-.7,z]],side===-1?.6:.94);
      face([[side*w*.8,-.7,z],[side*ww*.8,-.7,zz],[side*ww*.65,-.88,zz],[side*w*.65,-.88,z]],.33);
      face([[side*w,.08,z],[side*ww,.08,zz],[side*ww,.14,zz],[side*w,.14,z]],1.48);
    }
  }
  face([[-.83,.08,3.25],[.83,.08,3.25],[.664,-.7,3.25],[-.664,-.7,3.25]],.64);
  for(let row=0;row<6;row++)for(let col=0;col<4;col++)for(let level=0;level<(row===0?1:2);level++)
    box(-.92+col*.465,.14+level*.41,-2.5+row*.68,.44,.385,.64,((col+row)%5===0?.68:1),true);
  box(-.91,.14,1.72,1.82,.65,.88,.88);
  box(-.77,.79,1.82,1.54,.4,.66,1.17);
  box(-1.04,1.19,1.76,2.08,.29,.77,1.05);
  box(-.66,1.48,1.86,1.32,.085,.62,1.32);
  for(let i=0;i<9;i++)box(-.95+i*.22,1.26,1.748,.14,.11,.015,.2,false);
  box(-.05,1.57,2.02,.035,.64,.035,.85);
  box(-.4,1.94,2.02,.8,.02,.02,1.4);
  box(.39,1.57,2.17,.16,.32,.22,.42);
  box(-.4,.14,-3.07,.8,.075,.38,1.12);
  // Rotation and light are fixed; approaching changes camera distance, so depth
  // ordering can be prepared once instead of allocating/sorting on every frame.
  const yaw=-.35,pitch=.40,cy=Math.cos(yaw),sy=Math.sin(yaw),cp=Math.cos(pitch),sp=Math.sin(pitch);
  const geometry=faces.map(f=>{
    const points=f.p.map(p=>{const x=p[0]*cy+p[2]*sy,z=p[2]*cy-p[0]*sy;return [x,p[1]*cp+z*sp,z*cp-p[1]*sp];});
    const mix=v=>Math.round(Math.min(255,v*f.tone+(f.tone>1?30*(f.tone-1):0)));
    return {points,edge:f.edge,depth:points.reduce((n,p)=>n+p[2],0)/points.length,color:`rgb(${rgb.map(mix).join(',')})`};
  }).sort((a,b)=>b.depth-a.depth);
  let width=0,height=0,elapsed=0,last=0,painted=0,raf=0,visible=true,ready=false;
  const duration=4400, distance={soft:2.5,balanced:5,strong:7}[root.dataset.intensity]||5;
  function draw(progress){
    if(!width||!height)return;
    const ease=1-Math.pow(1-progress,4),camera=9+distance*(1-ease),focal=Math.min(width*1.67,height*2.9);
    ctx.clearRect(0,0,width,height);
    const shadow=ctx.createRadialGradient(width*.5,height*.8,0,width*.5,height*.8,width*.35);
    shadow.addColorStop(0,'rgba(11,11,11,.3)');shadow.addColorStop(1,'rgba(11,11,11,0)');
    ctx.fillStyle=shadow;ctx.fillRect(0,height*.62,width,height*.3);
    geometry.forEach(f=>{
      ctx.beginPath();f.points.forEach((p,i)=>{const k=focal/(camera+p[2]),x=width*.5+p[0]*k,y=height*.54-p[1]*k;i?ctx.lineTo(x,y):ctx.moveTo(x,y);});ctx.closePath();
      ctx.fillStyle=f.color;ctx.fill();
      if(f.edge){ctx.strokeStyle='rgba(250,230,190,.24)';ctx.lineWidth=.55;ctx.stroke();}
    });
    root.classList.add('is-rendered');ready=true;
    root.dataset.sceneProgress=progress.toFixed(2);
  }
  function tick(now){
    raf=0;
    if(!visible||document.hidden)return;
    if(last)elapsed+=Math.min(60,now-last);last=now;
    // Mobile caps at 30 fps; elapsed time stays independent of frame count.
    if(width>700||now-painted>=32||elapsed>=duration){draw(Math.min(1,elapsed/duration));painted=now;}
    if(elapsed<duration)raf=requestAnimationFrame(tick);
    else root.dataset.sceneState='settled';
  }
  function sync(){
    cancelAnimationFrame(raf);raf=0;last=0;
    if(reduce.matches||fixed){draw(1);root.dataset.sceneState='static';return;}
    if(!visible||document.hidden){root.dataset.sceneState='paused';return;}
    root.dataset.sceneState=elapsed>=duration?'settled':'approaching';
    if(elapsed<duration)raf=requestAnimationFrame(tick);else draw(1);
  }
  function resize(){
    const rect=root.getBoundingClientRect();width=rect.width;height=rect.height;
    const ratio=Math.min(devicePixelRatio||1,1.6);canvas.width=Math.round(width*ratio);canvas.height=Math.round(height*ratio);ctx.setTransform(ratio,0,0,ratio,0,0);
    draw(reduce.matches||fixed?1:Math.min(1,elapsed/duration));
  }
  if('ResizeObserver' in window)new ResizeObserver(resize).observe(root);else window.addEventListener('resize',resize);
  if('IntersectionObserver' in window)new IntersectionObserver(entries=>{visible=entries[0].isIntersecting;sync();},{threshold:.05}).observe(root);
  document.addEventListener('visibilitychange',sync);reduce.addEventListener('change',sync);
  window.addEventListener('pagehide',()=>cancelAnimationFrame(raf));
  window.addEventListener('pageshow',()=>{if(ready)sync();});
  resize();sync();
})();
