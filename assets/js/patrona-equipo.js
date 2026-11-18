(()=>{'use strict';
const root=document.querySelector('#organizacion-colegio');
if(!root)return;
const buttons=[...root.querySelectorAll('[data-team-tab]')],panels=[...root.querySelectorAll('.patrona-team-panel')];
function selectCycle(key,focus=false){
 const selected=buttons.find(b=>b.dataset.teamTab===key);
 if(!selected)return;
 buttons.forEach(b=>{const active=b===selected;b.setAttribute('aria-selected',String(active));b.tabIndex=active?0:-1;});
 panels.forEach(p=>{p.hidden=p.id!=='team-panel-'+key;});
 if(focus)selected.focus({preventScroll:true});
 root.dispatchEvent(new Event('patrona-team-change'));
}
buttons.forEach((b,i)=>{
 b.addEventListener('click',()=>selectCycle(b.dataset.teamTab));
 b.addEventListener('keydown',event=>{
  const keys=['ArrowRight','ArrowLeft','Home','End'];
  if(!keys.includes(event.key))return;
  event.preventDefault();
  const index=event.key==='Home'?0:event.key==='End'?buttons.length-1:(i+(event.key==='ArrowRight'?1:-1)+buttons.length)%buttons.length;
  selectCycle(buttons[index].dataset.teamTab,true);
 });
});
panels.forEach(panel=>{
 const viewport=panel.querySelector('[data-team-viewport]'),cards=[...(panel.querySelectorAll('.patrona-team-person'))],prev=panel.querySelector('[data-team-prev]'),next=panel.querySelector('[data-team-next]'),dots=panel.querySelector('[data-team-dots]');
 if(!viewport||!cards.length)return;
 const step=()=>cards.length>1?cards[1].offsetLeft-cards[0].offsetLeft:cards[0].getBoundingClientRect().width;
 const visible=()=>Math.max(1,Math.round(viewport.clientWidth/Math.max(step(),1)));
 const index=()=>Math.max(0,Math.round(viewport.scrollLeft/Math.max(step(),1)));
 function go(i){viewport.scrollTo({left:Math.max(0,Math.min(i,cards.length-visible()))*step(),behavior:matchMedia('(prefers-reduced-motion: reduce)').matches?'instant':'smooth'});}
 function render(){const total=Math.max(1,cards.length-visible()+1),current=Math.min(index(),total-1);if(prev)prev.disabled=current<=0;if(next)next.disabled=current>=total-1;if(dots){dots.replaceChildren();for(let i=0;i<total;i++){const b=document.createElement('button');b.type='button';b.setAttribute('aria-label','Ver docentes desde '+(i+1));b.setAttribute('aria-current',String(i===current));b.addEventListener('click',()=>go(i));dots.append(b);}}}
 prev?.addEventListener('click',()=>go(index()-1));next?.addEventListener('click',()=>go(index()+1));viewport.addEventListener('scroll',()=>requestAnimationFrame(render),{passive:true});window.addEventListener('resize',render);root.addEventListener('patrona-team-change',()=>{viewport.scrollLeft=0;requestAnimationFrame(render);});render();
});
})();