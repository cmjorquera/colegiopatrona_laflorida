(function(){
'use strict';
function init(textarea){
 if(textarea.dataset.richReady)return;
 textarea.dataset.richReady='1';
 const wrapper=document.createElement('div');wrapper.className='rich-editor';
 const toolbar=document.createElement('div');toolbar.className='rich-toolbar';toolbar.setAttribute('role','toolbar');toolbar.setAttribute('aria-label','Formato de descripción');
 const commands=[['bold','B'],['italic','I'],['underline','U'],['strikeThrough','S'],['insertUnorderedList','• Lista'],['insertOrderedList','1. Lista'],['formatBlock','❞ Cita','blockquote'],['createLink','🔗 Enlace'],['unlink','Quitar enlace'],['undo','↶'],['redo','↷']];
 const editor=document.createElement('div');editor.className='rich-content';editor.contentEditable='true';editor.setAttribute('role','textbox');editor.setAttribute('aria-multiline','true');editor.setAttribute('aria-label','Descripción enriquecida');editor.innerHTML=textarea.value;
 function sync(){textarea.value=editor.innerHTML;}
 // Mantiene sincronizada la vista enriquecida cuando un modal carga datos existentes.
 function loadSource(){editor.innerHTML=textarea.value;}
 textarea.addEventListener('rich-editor:load',loadSource);
 commands.forEach(([cmd,label,arg])=>{
  const b=document.createElement('button');b.type='button';b.textContent=label;b.title=cmd;b.setAttribute('aria-label',cmd);b.addEventListener('mousedown',e=>e.preventDefault());
  b.addEventListener('click',()=>{
   editor.focus();
   if(cmd==='createLink'){const href=prompt('URL del enlace (https://...)');if(!href||!/^https?:\/\//i.test(href))return;document.execCommand(cmd,false,href);}
   else document.execCommand(cmd,false,arg||null);
   sync();
  });toolbar.appendChild(b);
 });
 const color=document.createElement('input');color.type='color';color.title='Color de texto';color.setAttribute('aria-label','Color de texto');color.addEventListener('input',()=>{editor.focus();document.execCommand('foreColor',false,color.value);sync();});toolbar.appendChild(color);
 const lockLabel=document.createElement('label');lockLabel.className='rich-lock';const lock=document.createElement('input');lock.type='checkbox';lockLabel.append(lock,document.createTextNode(' Bloquear'));lock.addEventListener('change',()=>{editor.contentEditable=lock.checked?'false':'true';toolbar.querySelectorAll('button,input[type=color]').forEach(el=>el.disabled=lock.checked);});toolbar.appendChild(lockLabel);
 textarea.parentNode.insertBefore(wrapper,textarea);wrapper.append(toolbar,editor);textarea.classList.add('rich-source-hidden');
 editor.addEventListener('input',sync);textarea.form?.addEventListener('submit',sync);
}
function boot(){document.querySelectorAll('textarea[data-rich-editor]').forEach(init);}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',boot);else boot();
})();
