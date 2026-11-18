<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/rich_editor.php';
function patrona_cms_url(?string $url): string {
    $url=trim((string)$url);
    if ($url==='' || preg_match('~^(?:javascript|data):~i',$url)) return '#';
    if (preg_match('~^(?:https?://|/|#|[a-zA-Z0-9_-]+(?:/[a-zA-Z0-9_./%-]+)*)~',$url)) return $url;
    return '#';
}
function patrona_cms_render(array $s): void {
    $name=(string)($s['nombre_interno']??'');
    $config=$s['config']??[];
    $items=$s['items']??[];
    if ($name==='topbar' || $name==='header_principal' || $name==='footer_principal') return;
    if ($name==='hero_principal') {
        if (!$items) return;
        $slides=[];
        foreach ($items as $i) $slides[]=[
            'etiqueta'=>(string)($i['etiqueta']??''),'titulo'=>(string)($i['titulo']??''),
            'texto'=>(string)($i['descripcion']?:($i['subtitulo']??'')),
            'imagen'=>(string)($i['imagen']??''),'video'=>(string)($i['url']??''),
            'video_youtube'=>(string)($i['video_youtube']??''),
            'boton'=>(string)($i['boton_1_texto']??''),'enlace'=>patrona_cms_url($i['boton_1_url']??'')
        ];
        patrona_render(['tipo'=>'hero','datos'=>['slides'=>$slides,'config'=>$config]]);
        return;
    }
    if ($name==='sellos_home') {
        $seals=array_values(array_filter($items,static fn(array $i):bool=>trim((string)($i['imagen']??''))!==''));
        if (!$seals) return;
        $title=(string)($config['titulo_bloque']??'Sellos del colegio');
        $subtitle=(string)($config['subtitulo_bloque']??'');
        ?>
        <section class="section patrona-seals" id="sellos-home"><div class="wrap">
          <?php if($subtitle!==''):?><p class="eyebrow"><?=patrona_e($subtitle)?></p><?php endif;?>
          <?php if($title!==''):?><h2><?=patrona_e($title)?></h2><?php endif;?>
          <div class="patrona-seals-grid">
          <?php foreach($seals as $seal):
            $label=(string)($seal['titulo']??'Sello institucional');
            $desc=trim((string)($seal['descripcion']??''));
            $url=patrona_cms_url($seal['url']??'');
          ?>
          <article class="patrona-seal-card <?= $desc!==''?'has-back':'' ?>" tabindex="<?= $desc!==''?'0':'-1' ?>">
            <div class="patrona-seal-inner">
              <div class="patrona-seal-front"><img loading="lazy" src="<?=patrona_e((string)$seal['imagen'])?>" alt="<?=patrona_e($label)?>"></div>
              <?php if($desc!==''):?><div class="patrona-seal-back"><p><?=patrona_rich_clean($desc)?></p><?php if($url!=='#'):?><a href="<?=patrona_e($url)?>" target="_blank" rel="noopener noreferrer">Visitar sitio</a><?php endif;?></div><?php endif;?>
            </div>
            <?php if($desc===''&&$url!=='#'):?><a class="patrona-seal-link" href="<?=patrona_e($url)?>" target="_blank" rel="noopener noreferrer">Visitar sitio</a><?php endif;?>
          </article>
          <?php endforeach;?>
          </div>
        </div></section>
        <style>
        .patrona-seals-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:22px;margin-top:30px}
        .patrona-seal-card{position:relative;perspective:1100px;min-height:255px}
        .patrona-seal-inner{position:relative;width:100%;height:255px;transition:transform .55s;transform-style:preserve-3d}
        .patrona-seal-card.has-back:hover .patrona-seal-inner,.patrona-seal-card.has-back:focus-within .patrona-seal-inner,.patrona-seal-card.has-back:focus .patrona-seal-inner{transform:rotateY(180deg)}
        .patrona-seal-front,.patrona-seal-back{position:absolute;inset:0;backface-visibility:hidden;border:1px solid #dce6f1;border-radius:16px;background:white;display:flex;align-items:center;justify-content:center;padding:22px;box-shadow:0 10px 25px #08295312}
        .patrona-seal-front img{max-width:100%;max-height:100%;width:100%;height:100%;object-fit:contain}
        .patrona-seal-back{transform:rotateY(180deg);flex-direction:column;gap:10px;overflow:auto;text-align:center;background:#153c6b;color:white}
        .patrona-seal-back p{font-size:14px;line-height:1.5}.patrona-seal-back a{color:#fff;text-decoration:underline}
        .patrona-seal-link{display:block;text-align:center;margin-top:10px;color:#1675bb}
        @media(prefers-reduced-motion:reduce){.patrona-seal-inner{transition:none}}
        </style>
        <?php
        return;
    }
    if ($name==='equipo_docente_home') {
        if(!$items)return;
        $cycles=[
            'jardin'=>['Jardín','Educación inicial'],
            'basica_1'=>['Básica','1.er ciclo'],
            'basica_2'=>['Básica','2.º ciclo'],
            'media'=>['Enseñanza','Media']
        ];
        $grouped=[];
        foreach($items as $item){
            $cycle=trim((string)($item['etiqueta']??''));
            if(!isset($cycles[$cycle]))continue;
            $grouped[$cycle][]=$item;
        }
        ?>
        <section class="section patrona-team" id="organizacion-colegio">
          <div class="wrap">
            <div class="patrona-centered-heading"><span class="patrona-kicker">NUESTRO EQUIPO</span><h2><?=patrona_e((string)($config['titulo_bloque']??'Organización del Colegio'))?></h2><p><?=patrona_e((string)($config['subtitulo_bloque']??'Conoce a quienes acompañan el aprendizaje en cada etapa.'))?></p></div>
            <div class="patrona-team-intro" data-team-intro>
              <div class="patrona-cycle-nav" aria-label="Selecciona un ciclo">
              <?php foreach($cycles as $key=>$names):?><button type="button" data-team-tab="<?=patrona_e($key)?>" aria-controls="team-panel-<?=patrona_e($key)?>" aria-expanded="false"><strong><?=patrona_e($names[0])?></strong><span><?=patrona_e($names[1])?></span></button><?php endforeach;?>
              </div>
              <p class="patrona-team-hint">Selecciona un ciclo para conocer a sus profesores y equipo de coordinación.</p>
            </div>
            <div class="patrona-team-back-wrap" data-team-back-wrap hidden>
              <button type="button" class="patrona-team-back" data-team-back>← Volver a los ciclos</button>
            </div>
            <?php foreach($cycles as $key=>$names):?>
              <div class="patrona-team-panel" role="tabpanel" id="team-panel-<?=patrona_e($key)?>" aria-labelledby="team-tab-<?=patrona_e($key)?>" hidden>
                <?php $people=$grouped[$key]??[];if(!$people):?><p class="patrona-team-empty">Próximamente conocerás al equipo de este ciclo.</p><?php else:?>
                <?php
                  $coordinators=[];$teachers=[];
                  foreach($people as $person){
                    if(stripos((string)($person['subtitulo']??''),'coordin')!==false||stripos((string)($person['subtitulo']??''),'director')!==false)$coordinators[]=$person;
                    else $teachers[]=$person;
                  }
                ?>
                <?php foreach($coordinators as $person):?>
                  <article class="patrona-team-lead">
                    <?php if(!empty($person['imagen'])):?><img loading="lazy" src="<?=patrona_e((string)$person['imagen'])?>" alt="<?=patrona_e((string)$person['titulo'])?>"><?php endif;?>
                    <div><span class="patrona-kicker">COORDINACIÓN DEL CICLO</span><h3><?=patrona_e((string)$person['titulo'])?></h3><strong><?=patrona_e((string)($person['subtitulo']??'Coordinación'))?></strong>
                    <?php $email=(string)($person['url']??'');if(filter_var($email,FILTER_VALIDATE_EMAIL)):?><a href="mailto:<?=patrona_e($email)?>"><?=patrona_e($email)?></a><?php endif;?>
                    <?php if(!empty($person['descripcion'])):?><div class="patrona-rich-text"><?=patrona_rich_clean((string)$person['descripcion'])?></div><?php endif;?></div>
                  </article>
                <?php endforeach;?>
                <?php if($teachers):?><h3 class="patrona-team-subheading">Profesores jefes y equipo docente</h3><div class="patrona-team-grid">
                <?php foreach($teachers as $person):$email=(string)($person['url']??'');?><article class="patrona-team-person">
                  <?php if(!empty($person['imagen'])):?><img loading="lazy" src="<?=patrona_e((string)$person['imagen'])?>" alt="<?=patrona_e((string)$person['titulo'])?>"><?php else:?><div class="patrona-team-placeholder" aria-hidden="true">👤</div><?php endif;?>
                  <h4><?=patrona_e((string)$person['titulo'])?></h4><span class="patrona-team-role"><?=patrona_e((string)($person['subtitulo']??'Docente'))?></span>
                  <?php if(filter_var($email,FILTER_VALIDATE_EMAIL)):?><a href="mailto:<?=patrona_e($email)?>"><?=patrona_e($email)?></a><?php endif;?>
                  <?php if(!empty($person['descripcion'])):?><details><summary>Ver información</summary><div class="patrona-rich-text"><?=patrona_rich_clean((string)$person['descripcion'])?></div></details><?php endif;?>
                </article><?php endforeach;?></div><?php endif;?>
                <?php endif;?>
              </div>
            <?php endforeach;?>
          </div>
        </section>
        <?php return;
    }
    if ($name==='noticias_home') {
        if (!$items) return;
        $clean=static function($v):string {return trim(preg_replace('/\\s+/u',' ',strip_tags(html_entity_decode((string)$v,ENT_QUOTES|ENT_HTML5,'UTF-8')))??'');};
        ?>
        <section class="section patrona-news" id="noticias-home"><div class="wrap">
          <div class="patrona-news-heading"><div><span class="patrona-kicker">ACTUALIDAD ESCOLAR</span><h2>Noticias</h2></div>
          <?php if(count($items)>3):?><div class="patrona-news-controls"><button type="button" data-news-prev aria-label="Noticias anteriores">‹</button><button type="button" data-news-next aria-label="Noticias siguientes">›</button></div><?php endif;?></div>
          <div class="patrona-news-viewport" tabindex="0" aria-label="Carrusel de noticias">
            <div class="patrona-news-grid" data-news-track>
            <?php foreach($items as $item):
              $url=patrona_cms_url($item['url']??$item['boton_1_url']??'');
              $title=$clean($item['titulo']??'');$desc=$clean($item['descripcion']??$item['subtitulo']??'');
            ?><article class="patrona-news-card">
              <?php if(!empty($item['imagen'])):?><img loading="lazy" src="<?=patrona_e((string)$item['imagen'])?>" alt="<?=patrona_e($title)?>"><?php endif;?>
              <div class="patrona-news-body"><h3><?=patrona_e($title)?></h3>
              <?php if($desc!==''):?><p><?=patrona_e(mb_strimwidth($desc,0,180,'…','UTF-8'))?></p><?php endif;?>
              <?php if($url!=='#'):?><a href="<?=patrona_e($url)?>" <?=preg_match('~^https?://~i',$url)?'target="_blank" rel="noopener noreferrer"':''?>>Leer más →</a><?php endif;?></div>
            </article><?php endforeach;?></div>
          </div><div class="patrona-news-dots" data-news-dots aria-label="Páginas de noticias"></div>
        </div></section>
        <?php return;
    }
    if ($name==='tab_comunidad_home') {
        $families=array_values(array_filter($items,static fn(array $i):bool=>($i['etiqueta']??'')==='familias'));
        if (!$families) return;
        ?>
        <section class="section patrona-family patrona-family-3d" id="comunidad-escolar"><div class="wrap">
          <div class="patrona-centered-heading"><span class="patrona-kicker">NUESTRA COMUNIDAD</span><h2>Comunidad escolar</h2><p>Conoce los espacios de encuentro y participación de nuestras familias.</p></div>
          <div class="patrona-family-tabs"><span>Familias</span></div>
          <div class="patrona-family-stage" data-family-stage aria-label="Carrusel de comunidad escolar">
            <button class="patrona-family-arrow" type="button" data-family-prev aria-label="Elemento anterior">‹</button>
            <div class="patrona-family-deck">
            <?php foreach($families as $item):$url=patrona_cms_url($item['url']??'');$title=(string)($item['titulo']??'');$id='familia-'.(int)($item['id_item']??0);?>
              <article class="patrona-family-card" data-family-card>
                <?php if(!empty($item['imagen'])):?><img loading="lazy" src="<?=patrona_e((string)$item['imagen'])?>" alt="<?=patrona_e($title)?>"><?php endif;?>
                <div class="patrona-family-body"><h3><?=patrona_e($title)?></h3><button class="patrona-family-expand" type="button" aria-expanded="false" aria-controls="<?=patrona_e($id)?>" data-family-toggle>Conocer más <span aria-hidden="true">+</span></button></div>
                <div class="patrona-family-detail" id="<?=patrona_e($id)?>" hidden>
                  <div class="patrona-rich-text"><?=patrona_rich_clean((string)($item['descripcion']??''))?></div>
                  <?php if($url!=='#'):?><a href="<?=patrona_e($url)?>" <?=preg_match('~^https?://~i',$url)?'target="_blank" rel="noopener noreferrer"':''?>>Más información ↗</a><?php endif;?>
                </div>
              </article>
            <?php endforeach;?></div>
            <button class="patrona-family-arrow" type="button" data-family-next aria-label="Elemento siguiente">›</button>
          </div>
          <div class="patrona-family-dots" data-family-dots aria-label="Seleccionar elemento"></div>
          <div class="patrona-family-selected" data-family-selected aria-live="polite"></div>
        </div></section>
        <?php return;
    }
    if (str_contains($name,'galeria')) {
        $photos=array_values(array_filter($items,static fn(array $i):bool=>trim((string)($i['imagen']??''))!==''));
        if(!$photos)return;
        ?><section class="section patrona-gallery" id="galeria-home"><div class="wrap">
          <div class="patrona-centered-heading"><span class="patrona-kicker">VIDA ESCOLAR</span><h2><?=patrona_e((string)($config['titulo_bloque']??'Nuestra galería'))?></h2><p>Momentos de nuestra comunidad educativa.</p></div>
          <div class="patrona-gallery-grid">
          <?php foreach($photos as $i=>$photo):?><figure class="patrona-gallery-tile">
            <button type="button" data-gallery-open data-gallery-index="<?=$i?>" aria-label="Ampliar <?=patrona_e((string)($photo['titulo']??'fotografía'))?>">
              <img loading="lazy" src="<?=patrona_e((string)$photo['imagen'])?>" alt="<?=patrona_e((string)($photo['titulo']??''))?>">
              <span class="patrona-gallery-overlay"><?=patrona_e((string)($photo['titulo']??''))?></span>
            </button>
          </figure><?php endforeach;?></div>
        </div>
        <div class="patrona-lightbox" data-gallery-lightbox hidden role="dialog" aria-modal="true" aria-label="Fotografía ampliada">
          <button type="button" class="patrona-lightbox-close" data-gallery-close aria-label="Cerrar galería">×</button>
          <button type="button" class="patrona-lightbox-prev" data-gallery-prev aria-label="Fotografía anterior">‹</button>
          <figure><img data-gallery-image alt=""><figcaption data-gallery-caption></figcaption></figure>
          <button type="button" class="patrona-lightbox-next" data-gallery-next aria-label="Fotografía siguiente">›</button>
        </div></section>
        <?php return;
    }
    if(str_contains($name,'podcast')){
        $episodes=array_values(array_filter($items,static fn(array $i):bool=>trim((string)($i['url']??''))!==''));
        if(!$episodes)return;
        ?><section class="section wrap patrona-podcast" id="podcast-home"><h2><?=patrona_e((string)($config['titulo_bloque']??'Podcast'))?></h2><div class="patrona-cms-grid">
        <?php foreach($episodes as $episode):$url=patrona_cms_url($episode['url']??'');if($url==='#')continue;?>
        <article class="patrona-cms-card"><?php if(!empty($episode['imagen'])):?><img loading="lazy" src="<?=patrona_e((string)$episode['imagen'])?>" alt=""><?php endif;?><h3><?=patrona_e((string)($episode['titulo']??''))?></h3><p><?=patrona_e(strip_tags(html_entity_decode((string)($episode['descripcion']??''),ENT_QUOTES|ENT_HTML5,'UTF-8')))?></p><a href="<?=patrona_e($url)?>" target="_blank" rel="noopener noreferrer">Escuchar episodio →</a></article>
        <?php endforeach;?></div></section><?php return;
    }
    if(str_contains($name,'estadisticas')||str_contains($name,'numeros')){
        if(!$items)return;
        ?><section class="section patrona-stats" id="estadisticas-home"><div class="wrap">
        <div class="patrona-centered-heading"><span class="patrona-kicker">NUESTRO COLEGIO</span><h2><?=patrona_e((string)($config['titulo_bloque']??'Nuestro colegio en números'))?></h2><p>Indicadores de nuestra comunidad educativa.</p></div>
        <div class="patrona-stats-grid">
        <?php foreach($items as $i=>$stat):?><article class="patrona-stat-card">
          <?php if(!empty($stat['imagen'])):?><img loading="lazy" src="<?=patrona_e((string)$stat['imagen'])?>" alt=""><?php endif;?>
          <div class="patrona-stat-info"><strong><?=patrona_e((string)($stat['etiqueta']??''))?></strong><h3><?=patrona_e((string)($stat['titulo']??''))?></h3><p><?=patrona_e((string)($stat['descripcion']??''))?></p></div>
        </article><?php endforeach;?></div></div></section><?php return;
    }
    if (!$items) return;
    $id='seccion-'.(int)$s['id_seccion'];
    $title=(string)($config['titulo_bloque']??$config['titulo']??'');
    $type=(string)($s['tipo_seccion']??'content');
    ?>
    <section class="section wrap patrona-cms-section patrona-cms-<?= patrona_e($type) ?>" id="<?= patrona_e($id) ?>">
      <?php if ($title!==''): ?><h2><?= patrona_e($title) ?></h2><?php endif; ?>
      <?php if (!empty($config['descripcion_bloque'])): ?><p><?= patrona_e((string)$config['descripcion_bloque']) ?></p><?php endif; ?>
      <?php if ($items): ?><div class="patrona-cms-grid">
      <?php foreach ($items as $item):
        $heading=(string)($item['titulo']??'');
        $desc=(string)($item['descripcion']?:($item['subtitulo']??''));
        $img=(string)($item['imagen']??'');
        $url=patrona_cms_url($item['url']??$item['boton_1_url']??'');
        ?>
        <article class="patrona-cms-card">
          <?php if ($img!==''): ?><img loading="lazy" src="<?= patrona_e($img) ?>" alt="<?= patrona_e($heading) ?>"><?php endif; ?>
          <?php if (!empty($item['etiqueta'])): ?><p class="eyebrow"><?= patrona_e((string)$item['etiqueta']) ?></p><?php endif; ?>
          <?php if ($heading!==''): ?><h3><?= patrona_e($heading) ?></h3><?php endif; ?>
          <?php if ($desc!==''): ?><p><?= patrona_e($desc) ?></p><?php endif; ?>
          <?php if (!empty($item['video_youtube'])): ?><a href="<?= patrona_e(patrona_cms_url($item['video_youtube'])) ?>" target="_blank" rel="noopener noreferrer">Ver video</a><?php endif; ?>
          <?php if ($url!=='#' && !empty($item['boton_1_texto'])): ?><a href="<?= patrona_e($url) ?>"><?= patrona_e((string)$item['boton_1_texto']) ?></a><?php endif; ?>
        </article>
      <?php endforeach; ?></div><?php endif; ?>
    </section>
    <?php
}
function patrona_cms_header(array $menus,array $subs): void {
    $children=[];
    foreach ($subs as $sub) $children[(int)$sub['id_menu']][]=$sub;
    ?>
    <header class="site-header"><div class="wrap nav">
      <a class="brand" href="index.php" aria-label="Inicio Colegio Patrona de Lourdes"><img class="brand-logo" src="assets/imagenes/LOGO-COLEGIO--Sin-fondo.png" alt="Colegio Patrona de Lourdes"><span class="brand-sede">LA FLORIDA</span></a>
      <nav class="patrona-public-nav" aria-label="Navegación principal"><ul>
      <?php foreach ($menus as $menu):
        $id=(int)$menu['id_menu'];$hasChildren=!empty($children[$id]);
        $url=patrona_cms_url($menu['url']??'');
      ?>
        <li class="<?= $hasChildren?'has-submenu':'' ?>">
          <a href="<?= patrona_e($url) ?>"><?= patrona_e((string)$menu['nombre']) ?></a>
          <?php if ($hasChildren): ?><ul class="patrona-submenu">
          <?php foreach ($children[$id] as $sub): ?><li><a href="<?= patrona_e(patrona_cms_url($sub['url']??'')) ?>"><?= patrona_e((string)$sub['nombre']) ?></a></li><?php endforeach; ?>
          </ul><?php endif; ?>
        </li>
      <?php endforeach; ?>
      </ul></nav>
    </div></header>
    <?php
}
