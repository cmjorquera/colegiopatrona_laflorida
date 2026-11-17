<?php
declare(strict_types=1);
$portada=require __DIR__.'/config/portada.php';
require_once __DIR__.'/componentes/portada.php';
require_once __DIR__.'/componentes/patrona_cms.php';
require_once __DIR__.'/includes/patrona_cms_public.php';
$site=patrona_cms_site();
$sections=$site['sections'];
// El bloque superior es la portada original aprobada visualmente.
// El CMS empieza DESPUÉS de los cuatro accesos rápidos.
$top=array_values(array_filter($portada['contenedores']??[],static fn(array $c):bool=>in_array($c['clave']??'',['hero','accesos'],true)));
usort($top,static fn(array $a,array $b):int=>($a['orden']??0)<=>($b['orden']??0));
?>
<!doctype html><html lang="es"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="Colegio Patrona de Lourdes, La Florida.">
<title>Colegio Patrona de Lourdes | La Florida</title>
<link rel="stylesheet" href="assets/css/patrona.css?v=5">
<style>
.patrona-cms-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:24px}
.patrona-cms-card{border:1px solid #e3e7ed;border-radius:12px;padding:20px;overflow:hidden}
.patrona-cms-card img{width:100%;height:210px;object-fit:cover;border-radius:8px}
</style></head><body>
<div class="topline"><div class="wrap"><span>COLEGIO PATRONA DE LOURDES · LA FLORIDA</span><span>Sitio en desarrollo</span></div></div>
<header class="site-header"><div class="wrap nav"><a class="brand" href="#inicio" aria-label="Inicio Colegio Patrona de Lourdes"><img class="brand-logo" src="assets/imagenes/LOGO-COLEGIO--Sin-fondo.png" alt="Colegio Patrona de Lourdes"><span class="brand-sede">LA FLORIDA</span></a>
<nav aria-label="Navegación principal"><ul><?php foreach (($portada['menu']??[]) as $m): ?><li><a href="<?= patrona_e((string)$m['url']) ?>"><?= patrona_e((string)$m['texto']) ?></a></li><?php endforeach; ?></ul></nav>
<a class="nav-cta" href="#contacto">Contáctanos ↗</a></div></header>
<main>
<?php foreach($top as $c) patrona_render($c); ?>
<?php
$excluded=['topbar','header_principal','hero_principal','tab_destacados_home','footer_principal','presentacion_home','vida_escolar_home'];
foreach($sections as $section) {
 if(in_array((string)($section['nombre_interno']??''),$excluded,true)) continue;
 patrona_cms_render($section);
}
?>
</main>
<footer id="contacto"><div class="wrap footer-row"><strong>COLEGIO PATRONA DE LOURDES <small>LA FLORIDA</small></strong><span>Portada en desarrollo · Contenido institucional administrado desde el CMS</span></div></footer>
</body></html>
