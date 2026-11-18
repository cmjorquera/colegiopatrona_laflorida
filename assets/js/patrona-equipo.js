(()=>{'use strict';
const root=document.querySelector('#organizacion-colegio'),intro=root?.querySelector('[data-team-intro]'),backWrap=root?.querySelector('[data-team-back-wrap]'),back=root?.querySelector('[data-team-back]'),buttons=[...(root?.querySelectorAll('[data-team-tab]')||[])],panels=[...(root?.querySelectorAll('.patrona-team-panel')||[])];
if(!root||!intro||!backWrap||!buttons.length)return;
function show(key){intro.hidden=true;backWrap.hidden=false;buttons.forEach(b=>b.setAttribute('aria-expanded',String(b.dataset.teamTab===key)));panels.forEach(p=>{p.hidden=p.id!=='team-panel-'+key;});back.focus({preventScroll:true});}
function reset(){panels.forEach(p=>p.hidden=true);buttons.forEach(b=>b.setAttribute('aria-expanded','false'));backWrap.hidden=true;intro.hidden=false;buttons[0].focus({preventScroll:true});}
buttons.forEach(b=>b.addEventListener('click',()=>show(b.dataset.teamTab)));
back?.addEventListener('click',reset);
})();