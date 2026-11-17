<?php
declare(strict_types=1);
require_once __DIR__.'/componentes/portada.php';
require_once __DIR__.'/componentes/patrona_cms.php';
require_once __DIR__.'/includes/patrona_cms_public.php';
$site=patrona_cms_site();
$sections=$site['sections'];
$menus=$site['menus'];
$subs=$site['subs'];
?>
<!doctype html>
<html lang="es"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="Colegio Patrona de Lourdes, La Florida.">
<title>Colegio Patrona de Lourdes | La Florida</title>
<link rel="stylesheet" href="assets/css/patrona.css?v=4">
<style>
.patrona-public-nav>ul{display:flex;align-items:center;gap:1rem;list-style:none;margin:0;padding:0}
.patrona-public-nav li{position:relative;list-style:none}
.patrona-public-nav a{display:block;text-decoration:none}
.patrona-public-nav .patrona-submenu{display:none;position:absolute;left:0;top:100%;z-index:100;min-width:250px;background:#10264b;padding:10px;box-shadow:0 8px 24px #0003}
.patrona-public-nav .patrona-submenu a{color:white;padding:9px 12px}
.patrona-public-nav .patrona-submenu a:hover,.patrona-public-nav .patrona-submenu a:focus{background:#244679}
.patrona-public-nav li:hover>.patrona-submenu,.patrona-public-nav li:focus-within>.patrona-submenu{display:block}
.patrona-cms-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:24px}
.patrona-cms-card{border:1px solid #e3e7ed;border-radius:12px;padding:20px;overflow:hidden}
.patrona-cms-card img{width:100%;height:210px;object-fit:cover;border-radius:8px}
@media(max-width:850px){.patrona-public-nav>ul{flex-wrap:wrap;gap:10px}.patrona-public-nav .patrona-submenu{position:static;min-width:0}.patrona-public-nav li:focus-within>.patrona-submenu{display:block}}
</style>
</head><body>
<?php foreach ($sections as $section): ?>
<?php
$name=(string)($section['nombre_interno']??'');
if ($name==='topbar') {
  $c=$section['config']??[];
  $text=(string)($c['texto']??$c['texto_topbar']??'');
  if ($text!=='') echo '<div class="topline"><div class="wrap">'.patrona_e($text).'</div></div>';
} elseif ($name==='header_principal') {
  patrona_cms_header($menus,$subs);
} elseif ($name==='footer_principal') {
  $c=$section['config']??[];
  $text=(string)($c['texto']??$c['copyright']??'');
  echo '<footer><div class="wrap footer-row"><strong>COLEGIO PATRONA DE LOURDES <small>LA FLORIDA</small></strong><span>'.patrona_e($text).'</span></div></footer>';
} else {
  patrona_cms_render($section);
}
?>
<?php endforeach; ?>
</body></html>
