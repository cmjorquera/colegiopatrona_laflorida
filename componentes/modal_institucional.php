<?php
declare(strict_types=1);
/** Modal institucional automático. Solo contenido publicado en el CMS. */
function patrona_render_modal_institucional(array $sections): void {
    $section=null;
    foreach($sections as $candidate){
        if(($candidate['nombre_interno']??'')==='modal_institucional'){$section=$candidate;break;}
    }
    if(!$section)return;
    $items=$section['items']??[];
    if(!$items)return;
    $item=$items[0];
    $title=trim((string)($item['titulo']??''));
    $description=trim((string)($item['descripcion']??''));
    $image=trim((string)($item['imagen']??''));
    $url=patrona_cms_url($item['url']??'');
    if($title===''&&$description===''&&$image==='')return;
    $description=trim(strip_tags(html_entity_decode($description,ENT_QUOTES|ENT_HTML5,'UTF-8')));
    ?>
    <div id="patrona-modal-institucional" class="pm-backdrop" hidden>
      <section class="pm-dialog" role="dialog" aria-modal="true" aria-labelledby="pm-modal-title" tabindex="-1">
        <button class="pm-close" type="button" aria-label="Cerrar comunicado" data-pm-close>&times;</button>
        <?php if($image!=='' && preg_match('~^(?:assets/|/assets/)~',$image)):?>
          <img class="pm-image" src="<?=patrona_e($image)?>" alt="">
        <?php endif;?>
        <div class="pm-body">
          <?php if($title!==''):?><h2 id="pm-modal-title"><?=patrona_e($title)?></h2><?php else:?><h2 id="pm-modal-title">Comunicado institucional</h2><?php endif;?>
          <?php if($description!==''):?><p><?=nl2br(patrona_e($description))?></p><?php endif;?>
          <?php if($url!=='#'):?><a class="pm-action" href="<?=patrona_e($url)?>" <?=preg_match('~^https?://~i',$url)?'target="_blank" rel="noopener noreferrer"':''?>>Más información</a><?php endif;?>
        </div>
      </section>
    </div>
    <style>
      .pm-backdrop[hidden]{display:none!important}.pm-backdrop{position:fixed;inset:0;z-index:10000;background:rgba(5,23,49,.72);display:flex;align-items:center;justify-content:center;padding:20px}
      .pm-dialog{position:relative;background:#fff;color:#13365e;width:min(100%,620px);max-height:min(90vh,900px);overflow-y:auto;border-radius:16px;box-shadow:0 25px 90px #0006;outline:none}
      .pm-close{position:absolute;right:12px;top:12px;z-index:1;width:40px;height:40px;border:0;border-radius:50%;background:#fff;color:#123b70;font-size:29px;line-height:1;cursor:pointer;box-shadow:0 2px 12px #0002}
      .pm-image{display:block;width:100%;max-height:330px;object-fit:cover}.pm-body{padding:30px}.pm-body h2{margin:0 0 14px;font-size:clamp(23px,4vw,34px);line-height:1.2}.pm-body p{line-height:1.65;color:#4b617c;white-space:normal}.pm-action{display:inline-block;margin-top:15px;padding:12px 20px;border-radius:8px;background:#1675be;color:white!important;text-decoration:none;font-weight:700}
      @media(max-width:600px){.pm-body{padding:22px}.pm-image{max-height:240px}}
    </style>
    <script>
    (function(){
      const root=document.getElementById('patrona-modal-institucional');
      if(!root)return;
      const dialog=root.querySelector('.pm-dialog'),close=root.querySelector('[data-pm-close]');
      let previous=null;
      function hide(){root.hidden=true;document.body.style.overflow='';document.removeEventListener('keydown',keyHandler);if(previous&&previous.focus)previous.focus();}
      function keyHandler(e){
        if(e.key==='Escape'){hide();return;}
        if(e.key!=='Tab')return;
        const focusables=[...dialog.querySelectorAll('button:not([disabled]),a[href],input:not([disabled]),[tabindex]:not([tabindex="-1"])')];
        if(!focusables.length){e.preventDefault();dialog.focus();return;}
        const first=focusables[0],last=focusables[focusables.length-1];
        if(e.shiftKey&&document.activeElement===first){e.preventDefault();last.focus();}
        else if(!e.shiftKey&&document.activeElement===last){e.preventDefault();first.focus();}
      }
      function show(){previous=document.activeElement;root.hidden=false;document.body.style.overflow='hidden';close.focus();document.addEventListener('keydown',keyHandler);}
      close.addEventListener('click',hide);
      root.addEventListener('click',e=>{if(e.target===root)hide();});
      if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',show,{once:true});else show();
    })();
    </script>
    <?php
}
