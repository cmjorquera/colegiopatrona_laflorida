(()=>{'use strict';
const shell=document.querySelector('.admin-shell');if(!shell)return;
const topbar=shell.querySelector('.admin-content .topbar, .admin-content .nt-top');
const desktop=()=>window.matchMedia('(min-width:701px)').matches;
const sync=()=>{
 const collapsed=shell.classList.contains('sidebar-collapsed');
 document.querySelectorAll('.patrona-sidebar-toggle').forEach(btn=>{btn.setAttribute('aria-label',collapsed?'Expandir menú lateral':'Contraer menú lateral');btn.title=collapsed?'Expandir menú lateral':'Contraer menú lateral';});
 const bottom=shell.querySelector('[data-sidebar-collapse]');if(bottom)bottom.title=collapsed?'Expandir menú':'Contraer menú';
};
const toggle=()=>{if(!desktop())return;const collapsed=shell.classList.toggle('sidebar-collapsed');try{sessionStorage.setItem('patrona_sidebar_collapsed',collapsed?'1':'0')}catch(e){}sync();};
if(topbar&&!topbar.querySelector('.patrona-sidebar-toggle')){
 const btn=document.createElement('button');btn.type='button';btn.className='patrona-sidebar-toggle';btn.innerHTML='<i class="bi bi-layout-sidebar-inset" aria-hidden="true"></i>';topbar.insertBefore(btn,topbar.firstChild);btn.addEventListener('click',toggle);
}
const bottom=shell.querySelector('[data-sidebar-collapse]');if(bottom)bottom.addEventListener('click',toggle);
try{if(desktop()&&sessionStorage.getItem('patrona_sidebar_collapsed')==='1')shell.classList.add('sidebar-collapsed')}catch(e){}
sync();
document.querySelectorAll('.sidebar-group-toggle').forEach(button=>{
 const target=document.getElementById(button.getAttribute('aria-controls'));if(!target)return;
 button.addEventListener('click',()=>{
  const expanded=button.getAttribute('aria-expanded')==='true';
  button.setAttribute('aria-expanded',String(!expanded));target.hidden=expanded;
 });
});
})();