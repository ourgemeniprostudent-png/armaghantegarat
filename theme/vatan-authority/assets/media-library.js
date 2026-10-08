(()=>{'use strict';document.querySelectorAll('[data-media-tabs]').forEach(group=>{
 const tabs=[...group.querySelectorAll('[role=tab]')],panels=[...group.querySelectorAll('[data-media-panel]')];
 const choose=index=>{tabs.forEach((tab,i)=>{tab.setAttribute('aria-selected',String(i===index));tab.tabIndex=i===index?0:-1;panels[i].hidden=i!==index;if(i!==index)panels[i].querySelector('audio,video')?.pause();});};
 group.classList.add('am-enhanced');choose(0);tabs.forEach((tab,i)=>{tab.addEventListener('click',()=>choose(i));tab.addEventListener('keydown',e=>{if(!['ArrowLeft','ArrowRight','Home','End'].includes(e.key))return;e.preventDefault();const next=e.key==='Home'?0:e.key==='End'?tabs.length-1:(i+(e.key==='ArrowLeft'?1:-1)+tabs.length)%tabs.length;choose(next);tabs[next].focus();});});
 });document.addEventListener('play',event=>{if(!event.target.matches('[data-episode-player]'))return;document.querySelectorAll('[data-episode-player]').forEach(player=>{if(player!==event.target)player.pause();});},true);
})();
