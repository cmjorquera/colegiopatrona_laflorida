(()=>{'use strict';
const viewport=document.querySelector('.patrona-news-viewport'),track=document.querySelector('[data-news-track]');
if(viewport&&track){
 const cards=[...track.children],dots=document.querySelector('[data-news-dots]'),prev=document.querySelector('[data-news-prev]'),next=document.querySelector('[data-news-next]');
 const reduced=matchMedia('(prefers-reduced-motion: reduce)'),visible=()=>innerWidth<=600?1:innerWidth<=850?2:innerWidth<=1100?3:4;
 const step=()=>cards.length>1?cards[1].offsetLeft-cards[0].offsetLeft:cards[0]?.offsetWidth||1;
 const maxIndex=()=>Math.max(0,cards.length-visible());
 const page=()=>Math.max(0,Math.min(maxIndex(),Math.round(viewport.scrollLeft/Math.max(1,step()))));
 let timer=null,paused=false;
 function render(){
  if(prev)prev.disabled=page()===0;if(next)next.disabled=page()===maxIndex();
  if(!dots)return;dots.replaceChildren();
  for(let i=0;i<=maxIndex();i++){const btn=document.createElement('button');btn.type='button';btn.setAttribute('aria-label','Ver noticias desde '+(i+1));btn.setAttribute('aria-current',String(i===page()));btn.addEventListener('click',()=>{go(i);restart();});dots.append(btn);}
 }
 function go(i){viewport.scrollTo({left:Math.max(0,Math.min(i,maxIndex()))*step(),behavior:reduced.matches?'instant':'smooth'});}
 function tick(){if(paused||document.hidden||reduced.matches||maxIndex()===0)return;go(page()>=maxIndex()?0:page()+1);}
 function restart(){if(timer)clearInterval(timer);if(!reduced.matches&&maxIndex()>0)timer=setInterval(tick,3000);}
 prev?.addEventListener('click',()=>{go(page()-1);restart();});
 next?.addEventListener('click',()=>{go(page()+1);restart();});
 viewport.addEventListener('mouseenter',()=>{paused=true;});viewport.addEventListener('mouseleave',()=>{paused=false;});
 viewport.addEventListener('focusin',()=>{paused=true;});viewport.addEventListener('focusout',e=>{if(!viewport.contains(e.relatedTarget))paused=false;});
 viewport.addEventListener('scroll',()=>requestAnimationFrame(render),{passive:true});
 window.addEventListener('resize',()=>{render();restart();});
 reduced.addEventListener?.('change',restart);
 render();restart();
}
const familyStage=document.querySelector('[data-family-stage]');
if(familyStage){
 const cards=[...familyStage.querySelectorAll('[data-family-card]')],dots=document.querySelector('[data-family-dots]'),detail=document.querySelector('[data-family-selected]');let current=0,expanded=-1;
 const relative=(i)=>{let d=i-current;const n=cards.length;if(d>n/2)d-=n;if(d< -n/2)d+=n;return d;};
 function draw(){
  cards.forEach((card,i)=>{const d=relative(i);card.dataset.position=Math.abs(d)<=2?String(d):'hidden';card.setAttribute('aria-hidden',String(Math.abs(d)>2));card.querySelector('[data-family-toggle]')?.setAttribute('aria-expanded',String(expanded===i));});
  if(dots){dots.innerHTML='';cards.forEach((card,i)=>{const btn=document.createElement('button');btn.type='button';btn.setAttribute('aria-label','Ver tarjeta '+(i+1));btn.setAttribute('aria-current',String(i===current));btn.addEventListener('click',()=>select(i));dots.append(btn);});}
  if(detail){detail.replaceChildren();if(expanded>=0){const card=cards[expanded],copy=document.createElement('div');copy.className='patrona-family-selected-content';const heading=document.createElement('h3');heading.textContent=card.querySelector('h3')?.textContent||'';copy.append(heading);const body=card.querySelector('.patrona-family-detail');if(body){const clone=body.cloneNode(true);clone.removeAttribute('id');clone.removeAttribute('hidden');clone.className='patrona-rich-text';copy.append(...clone.childNodes);}detail.append(copy);}}
 }
 function select(i){current=(i+cards.length)%cards.length;expanded=-1;draw();}
 familyStage.querySelector('[data-family-prev]')?.addEventListener('click',()=>select(current-1));
 familyStage.querySelector('[data-family-next]')?.addEventListener('click',()=>select(current+1));
 cards.forEach((card,i)=>{card.addEventListener('click',e=>{if(i!==current){e.preventDefault();select(i);}});card.querySelector('[data-family-toggle]')?.addEventListener('click',e=>{e.stopPropagation();if(i!==current){select(i);return;}expanded=expanded===i?-1:i;draw();});});
 draw();
}
const lightbox=document.querySelector('[data-gallery-lightbox]'),tiles=[...document.querySelectorAll('[data-gallery-open]')];if(lightbox&&tiles.length){let active=0,previous=null;const img=lightbox.querySelector('[data-gallery-image]'),caption=lightbox.querySelector('[data-gallery-caption]');function show(i){active=(i+tiles.length)%tiles.length;const source=tiles[active].querySelector('img');img.src=source.src;img.alt=source.alt;caption.textContent=tiles[active].querySelector('.patrona-gallery-overlay')?.textContent||'';}function close(){lightbox.hidden=true;document.body.style.overflow='';previous?.focus();}
tiles.forEach((b,i)=>b.addEventListener('click',()=>{previous=document.activeElement;show(i);lightbox.hidden=false;document.body.style.overflow='hidden';lightbox.querySelector('[data-gallery-close]').focus();}));lightbox.querySelector('[data-gallery-close]').addEventListener('click',close);lightbox.querySelector('[data-gallery-prev]').addEventListener('click',()=>show(active-1));lightbox.querySelector('[data-gallery-next]').addEventListener('click',()=>show(active+1));lightbox.addEventListener('click',e=>{if(e.target===lightbox)close();});document.addEventListener('keydown',e=>{if(lightbox.hidden)return;if(e.key==='Escape')close();if(e.key==='ArrowRight')show(active+1);if(e.key==='ArrowLeft')show(active-1);});}
const chat=document.querySelector('[data-patrona-chat]'),panel=document.querySelector('[data-patrona-panel]');chat?.addEventListener('click',()=>{if(!panel)return;panel.hidden=!panel.hidden;chat.setAttribute('aria-expanded',String(!panel.hidden));});document.querySelector('[data-patrona-panel-close]')?.addEventListener('click',()=>{panel.hidden=true;chat?.setAttribute('aria-expanded','false');chat?.focus();});
const stats=[...document.querySelectorAll('[data-stat-count]')];
if(stats.length){
 const reduced=matchMedia('(prefers-reduced-motion: reduce)');
 const animate=el=>{
  if(el.dataset.animated)return;el.dataset.animated='1';
  const raw=el.textContent.trim(),match=raw.match(/^(\\D*)(\\d+(?:[.,]\\d+)?)(.*)$/u);
  if(!match||reduced.matches)return;
  const prefix=match[1],value=Number(match[2].replace(',','.')),suffix=match[3],decimal=match[2].includes(',')?',':match[2].includes('.')?'.':null,places=decimal?match[2].split(decimal)[1].length:0;
  if(!Number.isFinite(value)||value<0||value>1000000)return;
  const start=performance.now(),duration=1250;
  const draw=now=>{const t=Math.min(1,(now-start)/duration),n=value*(1-Math.pow(1-t,3));el.textContent=prefix+n.toFixed(places).replace('.',decimal||'.')+suffix;if(t<1)requestAnimationFrame(draw);else el.textContent=raw;};
  requestAnimationFrame(draw);
 };
 if('IntersectionObserver'in window){const observer=new IntersectionObserver(entries=>{entries.forEach(entry=>{if(entry.isIntersecting){animate(entry.target);observer.unobserve(entry.target);}});},{threshold:.35});stats.forEach(el=>observer.observe(el));}
 else stats.forEach(animate);
}
})();