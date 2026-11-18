<?php
declare(strict_types=1);
$portada=require __DIR__.'/config/portada.php';
require_once __DIR__.'/componentes/portada.php';
require_once __DIR__.'/componentes/patrona_cms.php';
require_once __DIR__.'/includes/patrona_cms_public.php';
require_once __DIR__.'/componentes/modal_institucional.php';
$site=patrona_cms_site();
$sections=$site['sections'];
$sectionsByName=[];
foreach($sections as $section){$sectionsByName[(string)$section['nombre_interno']]=$section;}
$showTopbar=isset($sectionsByName['topbar']);
$showHeader=isset($sectionsByName['header_principal']);
$showHero=isset($sectionsByName['hero_principal']);
$showQuick=isset($sectionsByName['tab_destacados_home']);
$topbarConfig=$sectionsByName['topbar']['config']??[];
$headerConfig=$sectionsByName['header_principal']['config']??[];
$footerConfig=$sectionsByName['footer_principal']['config']??[];
$showFooter=isset($sectionsByName['footer_principal']);
$quickItems=$sectionsByName['tab_destacados_home']['items']??[];
if($quickItems){
 foreach($portada['contenedores'] as &$block){
  if(($block['clave']??'')==='accesos'){
   $block['datos']['items']=array_map(static fn(array $i):array=>[
    'titulo'=>(string)($i['titulo']??''),
    'texto'=>(string)($i['descripcion']??''),
    'url'=>patrona_cms_url($i['url']??''),
    'icono'=>(string)($i['etiqueta']??'↗')
   ],$quickItems);
  }
 }
 unset($block);
}
$headerItems=$sectionsByName['header_principal']['items']??[];
if($headerItems){
 $portada['menu']=array_map(static fn(array $i):array=>[
 'texto'=>(string)($i['titulo']??''),
 'url'=>patrona_cms_url($i['url']??'')
 ],$headerItems);
}
// El bloque superior es la portada original aprobada visualmente.
// El CMS empieza DESPUÉS de los cuatro accesos rápidos.
$top=array_values(array_filter($portada['contenedores']??[],fn(array $c):bool=>(($c['clave']??'')==='hero'&&$showHero)||(($c['clave']??'')==='accesos'&&$showQuick)));
usort($top,static fn(array $a,array $b):int=>($a['orden']??0)<=>($b['orden']??0));
?>
<!doctype html><html lang="es"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="Colegio Patrona de Lourdes, La Florida.">
<title>Colegio Patrona de Lourdes | La Florida</title>
<link rel="stylesheet" href="assets/css/patrona.css?v=5">
<link rel="stylesheet" href="assets/css/patrona-cms-v6.css?v=2">
<style>
.brand-name{font-size:13px;font-weight:800;color:#153c6b;letter-spacing:.03em}.patrona-cms-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:24px}
.patrona-cms-card{border:1px solid #e3e7ed;border-radius:12px;padding:20px;overflow:hidden}
.patrona-cms-card img{width:100%;height:210px;object-fit:cover;border-radius:8px}
</style></head><body>
<?php if($showTopbar): ?><div class="topline"><div class="wrap"><span>COLEGIO PATRONA DE LOURDES · LA FLORIDA</span><span><?=patrona_e((string)($topbarConfig['texto']??'Sitio en desarrollo'))?></span></div></div><?php endif; ?>
<?php if($showHeader): ?><header class="site-header"><div class="wrap nav"><a class="brand" href="#inicio" aria-label="Inicio Colegio Patrona de Lourdes"><span class="brand-name"><?=patrona_e((string)($headerConfig['titulo_bloque']??'COLEGIO PATRONA DE LOURDES'))?></span><span class="brand-sede">LA FLORIDA</span></a>
<nav aria-label="Navegación principal"><ul><?php foreach (($portada['menu']??[]) as $m): ?><li><a href="<?= patrona_e((string)$m['url']) ?>"><?= patrona_e((string)$m['texto']) ?></a></li><?php endforeach; ?></ul></nav>
<a class="nav-cta" href="<?=patrona_e(patrona_cms_url($headerConfig['enlace']??'#contacto'))?>"><?=patrona_e((string)($headerConfig['boton_texto']??'Contáctanos'))?> ↗</a></div></header><?php endif; ?>
<main>
<?php foreach($top as $c) patrona_render($c); ?>
<?php
$excluded=['topbar','header_principal','hero_principal','tab_destacados_home','footer_principal','presentacion_home','vida_escolar_home','modal_institucional'];
foreach($sections as $section) {
 if(in_array((string)($section['nombre_interno']??''),$excluded,true)) continue;
 patrona_cms_render($section);
}
?>
</main>
<?php
// Accesos flotantes: URLs configurables desde seccion_config de header_principal.
$schoolListUrl=patrona_cms_url($headerConfig['url_utiles_escolares']??'');
$contactUrl=patrona_cms_url($headerConfig['url_contacto_flotante']??'#contacto');
?>
<div class="patrona-actions" aria-label="Accesos rápidos">
  <button type="button" data-patrona-chat aria-expanded="false" aria-controls="patrona-actions-chat" title="Información y consultas" aria-label="Información y consultas">☏</button>
  <?php if($schoolListUrl!=='#'):?><a href="<?=patrona_e($schoolListUrl)?>" target="_blank" rel="noopener noreferrer" download=""><?= '📄' ?></a><?php else:?><button type="button" disabled title="Lista de útiles aún no publicada" aria-label="Lista de útiles aún no publicada">📄</button><?php endif;?>
  <a href="<?=patrona_e($contactUrl)?>" title="Contacto" aria-label="Contacto">✉</a>
</div>
<div class="patrona-actions-panel" id="patrona-actions-chat" data-patrona-panel hidden>
  <button type="button" class="patrona-actions-panel-close" data-patrona-panel-close aria-label="Cerrar">×</button>
  <h3>¿Necesitas ayuda?</h3>
  <p>Este espacio está preparado para conectar el chat institucional. Por ahora puedes utilizar el acceso a Contacto.</p>
  <a href="#contacto">Ir a contacto →</a>
</div>
<?php patrona_render_modal_institucional($sections); ?>
<?php if($showFooter): ?><footer id="contacto"><div class="wrap footer-row"><strong>COLEGIO PATRONA DE LOURDES <small>LA FLORIDA</small></strong><span><?=patrona_e((string)($footerConfig['texto']??'Portada en desarrollo · Contenido institucional administrado desde el CMS'))?></span></div></footer><?php endif; ?>
<script defer src="assets/js/patrona-cms-v6.js?v=2"></script>
</body></html>
