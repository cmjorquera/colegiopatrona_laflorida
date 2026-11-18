<?php
declare(strict_types=1);
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
              <?php if($desc!==''):?><div class="patrona-seal-back"><p><?=nl2br(patrona_e($desc))?></p><?php if($url!=='#'):?><a href="<?=patrona_e($url)?>" target="_blank" rel="noopener noreferrer">Visitar sitio</a><?php endif;?></div><?php endif;?>
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
    if ($name==='noticias_home') {
        if (!$items) return;
        $clean=static function($v):string {
            $v=html_entity_decode((string)$v,ENT_QUOTES|ENT_HTML5,'UTF-8');
            return trim(preg_replace('/\\s+/u',' ',strip_tags($v))??'');
        };
        ?>
        <section class="section patrona-news" id="noticias-home"><div class="wrap">
        <div class="patrona-news-heading"><h2>Noticias</h2></div>
        <div class="patrona-news-grid">
        <?php foreach(array_slice($items,0,3) as $item):
          $url=patrona_cms_url($item['url']??$item['boton_1_url']??'');
          $title=$clean($item['titulo']??'');
          $desc=$clean($item['descripcion']??$item['subtitulo']??'');
        ?>
        <article class="patrona-news-card">
        <?php if(!empty($item['imagen'])):?><img loading="lazy" src="<?=patrona_e((string)$item['imagen'])?>" alt="<?=patrona_e($title)?>"><?php endif;?>
        <div class="patrona-news-body">
        <h3><?=patrona_e($title)?></h3>
        <?php if($desc!==''):?><p><?=patrona_e(mb_strimwidth($desc,0,200,'…','UTF-8'))?></p><?php endif;?>
        <?php if($url!=='#'):?><a href="<?=patrona_e($url)?>" <?=preg_match('~^https?://~i',$url)?'target="_blank" rel="noopener noreferrer"':''?>>Leer más →</a><?php endif;?>
        </div></article><?php endforeach;?>
        </div></div></section>
        <style>
        .patrona-news{padding:70px 0;background:#f5f8fc}.patrona-news-heading{display:flex;align-items:center;justify-content:space-between;margin-bottom:30px}.patrona-news-heading h2{margin:0;color:#003b6f}
        .patrona-news-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:25px}
        .patrona-news-card{background:white;border-radius:12px;overflow:hidden;box-shadow:0 8px 25px #102d5212}
        .patrona-news-card img{display:block;width:100%;height:230px;object-fit:cover}
        .patrona-news-body{padding:24px}.patrona-news-body h3{font-size:21px;line-height:1.35;color:#003b6f;margin:0 0 10px}.patrona-news-body p{font-size:15px;line-height:1.6;color:#526784}.patrona-news-body a{color:#0058a7;font-weight:700;text-decoration:none}
        @media(max-width:850px){.patrona-news-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:580px){.patrona-news-grid{grid-template-columns:1fr}}
        </style>
        <?php return;
    }
    if ($name==='tab_comunidad_home') {
        $families=array_values(array_filter($items,static fn(array $i):bool=>($i['etiqueta']??'')==='familias'));
        if (!$families) return;
        ?>
        <section class="section patrona-family" id="comunidad-escolar"><div class="wrap">
          <h2>Comunidad escolar</h2>
          <div class="patrona-family-tabs" role="tablist" aria-label="Comunidad escolar">
            <button role="tab" aria-selected="true" aria-controls="patrona-panel-familias" id="patrona-tab-familias" type="button">Familias</button>
          </div>
          <div role="tabpanel" id="patrona-panel-familias" aria-labelledby="patrona-tab-familias">
            <div class="patrona-family-grid">
            <?php foreach($families as $item):
                $url=patrona_cms_url($item['url']??'');
            ?>
              <article class="patrona-family-card">
                <?php if(!empty($item['imagen'])):?><img loading="lazy" src="<?=patrona_e((string)$item['imagen'])?>" alt="<?=patrona_e((string)$item['titulo'])?>"><?php endif;?>
                <div class="patrona-family-body"><h3><?=patrona_e((string)$item['titulo'])?></h3>
                <?php if(!empty($item['descripcion'])):?><p><?=nl2br(patrona_e((string)$item['descripcion']))?></p><?php endif;?>
                <?php if($url!=='#'):?><a href="<?=patrona_e($url)?>" <?=preg_match('~^https?://~i',$url)?'target="_blank" rel="noopener noreferrer"':''?>>Conocer más →</a><?php endif;?></div>
              </article>
            <?php endforeach;?></div>
          </div>
        </div></section>
        <style>
        .patrona-family{padding-block:64px}.patrona-family-tabs{display:flex;gap:10px;border-bottom:1px solid #d9e4f1;margin:22px 0 28px}.patrona-family-tabs button{background:#123b70;color:white;border:0;border-radius:9px 9px 0 0;padding:13px 25px;font-weight:700}
        .patrona-family-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:24px}.patrona-family-card{background:white;border:1px solid #dfe7f0;border-radius:14px;overflow:hidden;box-shadow:0 10px 28px #09294e10}.patrona-family-card img{width:100%;height:225px;object-fit:cover}.patrona-family-body{padding:22px}.patrona-family-body h3{margin-top:0}.patrona-family-body p{line-height:1.6}.patrona-family-body a{color:#126cb3;font-weight:700}
        </style>
        <?php
        return;
    }
    if (str_contains($name,'galeria')) {
        $photos=array_values(array_filter($items,static fn(array $i):bool=>trim((string)($i['imagen']??''))!==''));
        if(!$photos)return;
        ?><section class="section wrap patrona-gallery" id="galeria-home"><h2><?=patrona_e((string)($config['titulo_bloque']??'Galería'))?></h2><div class="patrona-gallery-grid">
        <?php foreach($photos as $photo):?><figure><img loading="lazy" src="<?=patrona_e((string)$photo['imagen'])?>" alt="<?=patrona_e((string)($photo['titulo']??''))?>"><figcaption><?=patrona_e((string)($photo['titulo']??''))?></figcaption></figure><?php endforeach;?></div></section>
        <style>.patrona-gallery-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:18px}.patrona-gallery-grid figure{margin:0}.patrona-gallery-grid img{width:100%;height:230px;object-fit:cover;border-radius:12px}.patrona-gallery-grid figcaption{padding:8px;color:#003b6f}</style><?php return;
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
        ?><section class="section wrap patrona-stats" id="estadisticas-home"><h2><?=patrona_e((string)($config['titulo_bloque']??'Nuestro colegio en números'))?></h2><div class="patrona-cms-grid">
        <?php foreach($items as $stat):?><article class="patrona-cms-card"><strong style="font-size:32px;color:#003b6f"><?=patrona_e((string)($stat['etiqueta']??''))?></strong><h3><?=patrona_e((string)($stat['titulo']??''))?></h3><p><?=patrona_e((string)($stat['descripcion']??''))?></p></article><?php endforeach;?></div></section><?php return;
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
