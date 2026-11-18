(()=>{'use strict';
const root=document.querySelector('#organizacion-colegio'),intro=root?.querySelector('[data-team-intro]'),backWrap=root?.querySelector('[data-team-back-wrap]'),back=root?.querySelector('[data-team-back]'),buttons=[...(root?.querySelectorAll('[data-team-tab]')||[])],panels=[...(root?.querySelectorAll('.patrona-team-panel')||[])];
if(!root||!intro||!backWrap||!buttons.length)return;
function show(key){
 const chosen=buttons.find(b=>b.dataset.teamTab===key);if(!chosen)return;
 root.classList.add('patrona-team-active');intro.hidden=false;backWrap.hidden=false;
 buttons.forEach(b=>{const active=b===chosen;b.hidden=!active;b.setAttribute('aria-expanded',String(active));b.setAttribute('aria-pressed',String(active));});
 panels.forEach(p=>{p.hidden=p.id!=='team-panel-'+key;});
 chosen.focus({preventScroll:true});
}
function reset(){
 root.classList.remove('patrona-team-active');panels.forEach(p=>p.hidden=true);
 buttons.forEach(b=>{b.hidden=false;b.setAttribute('aria-expanded','false');b.setAttribute('aria-pressed','false');});
 backWrap.hidden=true;intro.hidden=false;buttons[0].focus({preventScroll:true});
}
buttons.forEach(b=>b.addEventListener('click',()=>{if(root.classList.contains('patrona-team-active')&&b.getAttribute('aria-pressed')==='true')reset();else show(b.dataset.teamTab);}));
back?.addEventListener('click',reset);
panels.forEach(panel=>{
 const viewport=panel.querySelector('[data-team-viewport]'),cards=[...(panel.querySelectorAll('.patrona-team-person'))],prev=panel.querySelector('[data-team-prev]'),next=panel.querySelector('[data-team-next]'),dots=panel.querySelector('[data-team-dots]');
 if(!viewport||!cards.length)return;
 const step=()=>cards.length>1?cards[1].offsetLeft-cards[0].offsetLeft:cards[0].getBoundingClientRect().width;
 const visible=()=>Math.max(1,Math.round(viewport.clientWidth/Math.max(step(),1)));
 const index=()=>Math.max(0,Math.round(viewport.scrollLeft/Math.max(step(),1)));
 function go(i){viewport.scrollTo({left:Math.max(0,Math.min(i,cards.length-visible()))*step(),behavior:matchMedia('(prefers-reduced-motion: reduce)').matches?'instant':'smooth'});}
 function render(){const total=Math.max(1,cards.length-visible()+1),current=Math.min(index(),total-1);if(prev)prev.disabled=current<=0;if(next)next.disabled=current>=total-1;if(dots){dots.replaceChildren();for(let i=0;i<total;i++){const b=document.createElement('button');b.type='button';b.setAttribute('aria-label','Ver docentes desde '+(i+1));b.setAttribute('aria-current',String(i===current));b.addEventListener('click',()=>go(i));dots.append(b);}}}
 prev?.addEventListener('click',()=>go(index()-1));next?.addEventListener('click',()=>go(index()+1));viewport.addEventListener('scroll',()=>requestAnimationFrame(render),{passive:true});window.addEventListener('resize',render);render();
});
})();