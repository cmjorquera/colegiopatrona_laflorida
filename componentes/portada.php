<?php
declare(strict_types=1);

function patrona_e(string $value): string {
 return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function patrona_render(array $contenedor): void {
 $tipo = $contenedor['tipo'] ?? '';
 $d = $contenedor['datos'] ?? [];
 switch ($tipo) {
 case 'hero':
 $slides = $d['slides'] ?? [$d];
 $config = $d['config'] ?? [];
 $arrows = ($config['mostrar_flechas'] ?? 'si') === 'si';
 $dots = ($config['mostrar_indicadores'] ?? 'si') === 'si';
 $overlay = in_array($config['overlay'] ?? 'oscuro', ['oscuro','claro','ninguno'], true) ? ($config['overlay'] ?? 'oscuro') : 'oscuro';
 $align = in_array($config['alineacion_texto'] ?? 'centro', ['izquierda','centro','derecha'], true) ? ($config['alineacion_texto'] ?? 'centro') : 'centro';
 ?>
 <section class="hero hero-video patrona-carousel" id="inicio" aria-label="Bienvenida al Colegio Patrona de Lourdes" data-carousel>
 <?php foreach ($slides as $i=>$slide): ?>
 <div class="patrona-slide" data-slide <?= $i>0?'hidden':'' ?>>
   <div class="hero-media" aria-hidden="true">
   <?php $video = (string)($slide['video'] ?? ''); $poster=(string)($slide['imagen']??''); ?>
   <?php if ($video !== ''): ?>
   <video class="hero-background-video" autoplay muted loop playsinline preload="metadata" <?= $poster!==''?'poster="'.patrona_e($poster).'"':'' ?>><source src="<?= patrona_e($video) ?>" type="video/mp4"></video>
   <?php elseif ($poster !== ''): ?><img src="<?= patrona_e($poster) ?>" alt="" style="width:100%;height:100%;object-fit:cover">
   <?php endif; ?>
   </div>
   <?php if ($overlay !== 'ninguno'): ?><div class="hero-shade" aria-hidden="true" style="<?= $overlay==='claro'?'background:rgba(255,255,255,.28)':'' ?>"></div><?php endif; ?>
   <div class="wrap hero-video-content" style="text-align:<?= $align==='izquierda'?'left':($align==='derecha'?'right':'center') ?>">
    <p class="eyebrow"><?= patrona_e((string)($slide['etiqueta']??'')) ?></p>
    <h1><?= patrona_e((string)($slide['titulo']??'')) ?></h1>
    <p class="lead"><?= patrona_e((string)($slide['texto']??'')) ?></p>
    <?php if (!empty($slide['boton'])): ?><a class="btn" href="<?= patrona_e((string)($slide['enlace']?:'#colegio')) ?>"><?= patrona_e((string)$slide['boton']) ?> <span aria-hidden="true">↗</span></a><?php endif; ?>
   </div>
 </div>
 <?php endforeach; ?>
 <?php if (count($slides)>1): ?>
 <?php if ($arrows): ?><button class="patrona-carousel-arrow prev" type="button" data-prev aria-label="Diapositiva anterior">‹</button><button class="patrona-carousel-arrow next" type="button" data-next aria-label="Siguiente diapositiva">›</button><?php endif; ?>
 <?php if ($dots): ?><div class="patrona-carousel-dots"><?php foreach ($slides as $i=>$slide): ?><button type="button" data-dot="<?= $i ?>" aria-label="Ir a diapositiva <?= $i+1 ?>" aria-current="<?= $i===0?'true':'false' ?>"></button><?php endforeach; ?></div><?php endif; ?>
 <?php endif; ?>
 </section>
 <style>.patrona-carousel{position:relative;display:block!important;width:100%}.patrona-slide{position:relative;width:100%;min-height:min(760px,85svh);display:flex;align-items:center}.patrona-slide .hero-media{width:100%;height:100%}.patrona-slide .hero-video-content{width:100%}.patrona-slide[hidden]{display:none!important}.patrona-carousel-arrow{position:absolute;top:50%;transform:translateY(-50%);z-index:5;background:#002b55bb;color:#fff;border:0;border-radius:50%;width:44px;height:44px;font-size:32px;cursor:pointer}.patrona-carousel-arrow.prev{left:20px}.patrona-carousel-arrow.next{right:20px}.patrona-carousel-dots{position:absolute;bottom:20px;left:0;right:0;display:flex;justify-content:center;gap:10px;z-index:6}.patrona-carousel-dots button{width:11px;height:11px;border-radius:50%;border:1px solid white;background:#ffffff70;cursor:pointer}.patrona-carousel-dots button[aria-current="true"]{background:white}</style>
 <script>document.querySelectorAll('[data-carousel]').forEach(function(root){const slides=[...root.querySelectorAll('[data-slide]')];const dots=[...root.querySelectorAll('[data-dot]')];let current=0;function show(n){current=(n+slides.length)%slides.length;slides.forEach((s,i)=>{s.hidden=i!==current;const v=s.querySelector('video');if(v){if(i===current)v.play().catch(()=>{});else v.pause();}});dots.forEach((d,i)=>d.setAttribute('aria-current',i===current?'true':'false'));}root.querySelector('[data-prev]')?.addEventListener('click',()=>show(current-1));root.querySelector('[data-next]')?.addEventListener('click',()=>show(current+1));dots.forEach((d,i)=>d.addEventListener('click',()=>show(i)));});</script>
 <?php break;
 case 'accesos': ?>
 <section class="quick wrap" aria-label="<?= patrona_e($d['titulo'] ?? 'Accesos') ?>"><div class="quick-grid">
 <?php foreach (($d['items'] ?? []) as $item): ?>
 <a class="quick-item" href="<?= patrona_e($item['url'] ?? '#') ?>"><span class="quick-icon" aria-hidden="true"><?= patrona_e($item['icono'] ?? '↗') ?></span><span><strong><?= patrona_e($item['titulo'] ?? '') ?></strong><small><?= patrona_e($item['texto'] ?? '') ?></small></span><span class="quick-arrow" aria-hidden="true">↗</span></a>
 <?php endforeach; ?></div></section>
 <?php break;
 case 'presentacion': ?>
 <section class="section wrap split" id="colegio"><div><p class="eyebrow">Bienvenidos</p><h2><?= patrona_e($d['titulo'] ?? '') ?></h2><p class="body-text"><?= patrona_e($d['texto'] ?? '') ?></p><p class="muted"><?= patrona_e($d['detalle'] ?? '') ?></p></div><div class="photo-placeholder photo-real"><img loading="lazy" src="<?= patrona_e($d["imagen"] ?? "assets/imagenes/logo-colegio-1024x598.jpg") ?>" alt="Gráfica institucional de inicio de clases"></div></section>
 <?php break;
 case 'valores': ?>
 <section class="section tinted" id="proyecto"><div class="wrap"><p class="eyebrow">Lo que nos inspira</p><h2><?= patrona_e($d['titulo'] ?? '') ?></h2><div class="values">
 <?php foreach (($d['items'] ?? []) as $i=>$item): ?><article class="value"><span class="value-num">0<?= $i+1 ?></span><h3><?= patrona_e($item['titulo'] ?? '') ?></h3><p><?= patrona_e($item['texto'] ?? '') ?></p></article><?php endforeach; ?>
 </div></div></section>
 <?php break;
 case 'noticias': ?>
 <section class="section wrap" id="vida-escolar"><div id="noticias" class="section-heading"><div><p class="eyebrow">Actualidad</p><h2><?= patrona_e($d['titulo'] ?? '') ?></h2></div><span class="muted">Contenido de demostración</span></div><div class="news">
 <?php foreach (($d['items'] ?? []) as $i=>$item): ?><article class="news-card"><div class="news-visual visual-<?= ($i%3)+1 ?>" aria-hidden="true"><span><?= patrona_e($item['categoria'] ?? '') ?></span></div><div class="news-body"><span class="category"><?= patrona_e($item['categoria'] ?? '') ?></span><h3><?= patrona_e($item['titulo'] ?? '') ?></h3><p><?= patrona_e($item['texto'] ?? '') ?></p></div></article><?php endforeach; ?>
 </div></section>
 <?php break;
 case 'contacto': ?>
 <section class="contact" id="contacto"><div class="wrap contact-row"><div><p class="eyebrow">Comunidad Patrona</p><h2><?= patrona_e($d['titulo'] ?? '') ?></h2><p><?= patrona_e($d['texto'] ?? '') ?></p></div><span class="contact-symbol" aria-hidden="true">✳</span></div></section>
 <?php break;
 }
}
