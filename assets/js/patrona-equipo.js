(()=>{'use strict';
const root=document.querySelector('#organizacion-colegio');if(!root)return;
const tabs=[...root.querySelectorAll('[data-team-tab]')],panels=[...root.querySelectorAll('.patrona-team-panel')];
function selectCycle(key,focus=false){
 const chosen=tabs.find(b=>b.dataset.teamTab===key);if(!chosen)return;
 tabs.forEach(b=>{const active=b===chosen;b.setAttribute('aria-selected',String(active));b.tabIndex=active?0:-1;});
 panels.forEach(p=>{p.hidden=p.id!=='team-panel-'+key;});
 if(focus)chosen.focus({preventScroll:true});
}
tabs.forEach((b,i)=>{
 b.addEventListener('click',()=>selectCycle(b.dataset.teamTab));
 b.addEventListener('keydown',e=>{if(!['ArrowLeft','ArrowRight','Home','End'].includes(e.key))return;e.preventDefault();const n=e.key==='Home'?0:e.key==='End'?tabs.length-1:(i+(e.key==='ArrowRight'?1:-1)+tabs.length)%tabs.length;selectCycle(tabs[n].dataset.teamTab,true);});
});
panels.forEach(panel=>{
 const courseButtons=[...panel.querySelectorAll('[data-course]')],results=panel.querySelector('[data-course-results]'),title=panel.querySelector('[data-course-title]'),empty=panel.querySelector('[data-course-empty]'),viewport=panel.querySelector('[data-team-viewport]'),cards=[...panel.querySelectorAll('.patrona-team-person')],prev=panel.querySelector('[data-team-prev]'),next=panel.querySelector('[data-team-next]'),dots=panel.querySelector('[data-team-dots]'),heading=panel.querySelector('.patrona-team-carousel-heading');
 const visibleCards=()=>cards.filter(c=>!c.hidden);
 const step=()=>{const active=visibleCards();return active.length>1?active[1].offsetLeft-active[0].offsetLeft:active[0]?.getBoundingClientRect().width||1;};
 const visible=()=>Math.max(1,Math.round((viewport?.clientWidth||1)/Math.max(step(),1)));
 const index=()=>viewport?Math.max(0,Math.round(viewport.scrollLeft/Math.max(step(),1))):0;
 function go(i){if(!viewport)return;viewport.scrollTo({left:Math.max(0,Math.min(i,visibleCards().length-visible()))*step(),behavior:window.matchMedia('(prefers-reduced-motion: reduce)').matches?'instant':'smooth'});}
 function render(){
  const total=Math.max(1,visibleCards().length-visible()+1),current=Math.min(index(),total-1);
  if(prev)prev.disabled=current<=0;if(next)next.disabled=current>=total-1;
  if(dots){dots.replaceChildren();if(total>1)for(let i=0;i<total;i++){const b=document.createElement('button');b.type='button';b.setAttribute('aria-label','Ver docentes desde '+(i+1));b.setAttribute('aria-current',String(i===current));b.addEventListener('click',()=>go(i));dots.append(b);}}
 }
 courseButtons.forEach(b=>b.addEventListener('click',()=>{
  const course=b.dataset.course;courseButtons.forEach(x=>x.setAttribute('aria-pressed',String(x===b)));
  if(results)results.hidden=false;if(title)title.textContent='Profesores de '+b.querySelector('strong').textContent;
  let count=0;
  cards.forEach(card=>{
   const assigned=(card.dataset.personCourse||'').toUpperCase().replace(/[^A-Z0-9]/g,'');
   const role=(card.dataset.personRole||'').toUpperCase().replace(/[^A-Z0-9]/g,'');
   const target=course.toUpperCase().replace(/[^A-Z0-9]/g,'');
   const matches=assigned===target||(!assigned&&role.includes(target));
   card.hidden=!matches;if(matches)count++;
  });
  if(empty)empty.hidden=count>0;if(viewport)viewport.hidden=count===0;if(heading)heading.hidden=count===0;
  if(dots)dots.hidden=count===0;
  if(viewport)viewport.scrollLeft=0;
  requestAnimationFrame(render);
 }));
 prev?.addEventListener('click',()=>go(index()-1));next?.addEventListener('click',()=>go(index()+1));
 viewport?.addEventListener('scroll',()=>requestAnimationFrame(render),{passive:true});
 window.addEventListener('resize',render);render();
});
})();