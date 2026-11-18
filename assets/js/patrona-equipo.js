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
})();