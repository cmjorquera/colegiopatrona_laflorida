(()=>{'use strict';
const nav=document.querySelector('.patrona-public-nav');
const menuButton=document.querySelector('.patrona-mobile-toggle');
if(!nav)return;
const parents=[...nav.querySelectorAll('.has-submenu')];
function closeSubmenus(skip){parents.forEach(item=>{if(item===skip)return;item.classList.remove('submenu-open');item.querySelector('.patrona-nav-expand')?.setAttribute('aria-expanded','false');});}
function closeMenu(){nav.classList.remove('is-open');menuButton?.setAttribute('aria-expanded','false');menuButton?.setAttribute('aria-label','Abrir menú de navegación');closeSubmenus();}
menuButton?.addEventListener('click',()=>{const open=nav.classList.toggle('is-open');menuButton.setAttribute('aria-expanded',String(open));menuButton.setAttribute('aria-label',open?'Cerrar menú de navegación':'Abrir menú de navegación');if(!open)closeSubmenus();});
parents.forEach(item=>{const toggle=item.querySelector('.patrona-nav-expand');toggle?.addEventListener('click',()=>{const open=item.classList.contains('submenu-open');closeSubmenus();item.classList.toggle('submenu-open',!open);toggle.setAttribute('aria-expanded',String(!open));});});
document.addEventListener('keydown',e=>{if(e.key==='Escape'){const wasOpen=nav.classList.contains('is-open');closeMenu();if(wasOpen)menuButton?.focus();}});
document.addEventListener('click',e=>{if(!nav.contains(e.target)&&!menuButton?.contains(e.target))closeMenu();});
nav.addEventListener('click',e=>{if(e.target.closest('a')&&window.matchMedia('(max-width:1024px)').matches)closeMenu();});
window.matchMedia('(min-width:1025px)').addEventListener('change',e=>{if(e.matches)closeMenu();});
})();