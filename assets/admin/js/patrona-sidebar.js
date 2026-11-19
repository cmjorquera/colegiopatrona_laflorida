(()=>{'use strict';
const shell=document.querySelector('.admin-shell');
if(shell){
 const topbar=shell.querySelector('.admin-content .topbar, .admin-content .nt-top');
 if(topbar&&!topbar.querySelector('.patrona-sidebar-toggle')){
  const btn=document.createElement('button');btn.type='button';btn.className='patrona-sidebar-toggle';btn.setAttribute('aria-label','Contraer menú lateral');btn.setAttribute('title','Contraer o expandir menú');btn.innerHTML='<i class="bi bi-layout-sidebar-inset"></i>';
  topbar.insertBefore(btn,topbar.firstChild);
  btn.addEventListener('click',()=>{const closed=shell.classList.toggle('sidebar-collapsed');btn.setAttribute('aria-label',closed?'Expandir menú lateral':'Contraer menú lateral');btn.setAttribute('title',closed?'Expandir menú lateral':'Contraer menú lateral');try{sessionStorage.setItem('patrona_sidebar_collapsed',closed?'1':'0')}catch(e){}});
  try{if(sessionStorage.getItem('patrona_sidebar_collapsed')==='1'){shell.classList.add('sidebar-collapsed');btn.setAttribute('aria-label','Expandir menú lateral')}}catch(e){}
 }
}
document.querySelectorAll('.sidebar-group-toggle').forEach(button=>{const target=document.getElementById(button.getAttribute('aria-controls'));if(!target)return;button.addEventListener('click',()=>{const expanded=button.getAttribute('aria-expanded')==='true';button.setAttribute('aria-expanded',String(!expanded));target.hidden=expanded;});});
})();