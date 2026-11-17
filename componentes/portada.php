<?php
declare(strict_types=1);

function patrona_e(string $value): string {
 return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function patrona_render(array $contenedor): void {
 $tipo = $contenedor['tipo'] ?? '';
 $d = $contenedor['datos'] ?? [];
 switch ($tipo) {
 case 'hero': ?>
 <section class="hero hero-video" id="inicio" aria-label="Bienvenida al Colegio Patrona de Lourdes">
   <div class="hero-media" aria-hidden="true">
     <video class="hero-background-video" autoplay muted loop playsinline preload="metadata"
       poster="<?= patrona_e($d['imagen'] ?? 'assets/imagenes/hero-patrona.png') ?>">
       <source src="<?= patrona_e($d['video'] ?? 'assets/videos/portada-patrona.mp4') ?>" type="video/mp4">
     </video>
   </div>
   <div class="hero-shade" aria-hidden="true"></div>
   <div class="wrap hero-video-content">
     <p class="eyebrow"><?= patrona_e($d['etiqueta'] ?? '') ?></p>
     <h1><?= patrona_e($d['titulo'] ?? '') ?></h1>
     <p class="lead"><?= patrona_e($d['texto'] ?? '') ?></p>
     <a class="btn" href="<?= patrona_e($d['enlace'] ?? '#colegio') ?>"><?= patrona_e($d['boton'] ?? 'Conocer más') ?> <span aria-hidden="true">↗</span></a>
   </div>
 </section>
 <?php break;
 case 'accesos': ?>
 <section class="quick wrap" aria-label="<?= patrona_e($d['titulo'] ?? 'Accesos') ?>"><div class="quick-grid">
 <?php foreach (($d['items'] ?? []) as $item): ?>
 <a class="quick-item" href="<?= patrona_e($item['url'] ?? '#') ?>"><span class="quick-icon" aria-hidden="true"><?= patrona_e($item['icono'] ?? '↗') ?></span><span><strong><?= patrona_e($item['titulo'] ?? '') ?></strong><small><?= patrona_e($item['texto'] ?? '') ?></small></span><span class="quick-arrow" aria-hidden="true">↗</span></a>
 <?php endforeach; ?></div></section>
 <?php break;
 case 'presentacion': ?>
 <section class="section wrap split" id="colegio"><div><p class="eyebrow">Bienvenidos</p><h2><?= patrona_e($d['titulo'] ?? '') ?></h2><p class="body-text"><?= patrona_e($d['texto'] ?? '') ?></p><p class="muted"><?= patrona_e($d['detalle'] ?? '') ?></p></div><div class="photo-placeholder photo-real"><img loading="lazy" src="assets/imagenes/vida-escolar.png" alt="Gráfica institucional de inicio de clases"></div></section>
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
