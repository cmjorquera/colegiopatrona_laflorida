<?php
declare(strict_types=1);
$portada = require __DIR__ . '/config/portada.php';
require_once __DIR__ . '/componentes/portada.php';

// Fuente CMS: seccion / seccion_item / seccion_config, igual que Tabancura.
// Si la base no está disponible, se conserva la portada provisional sin interrumpir el sitio.
try {
 $cfgFile=__DIR__.'/class/conexion_local.php';
 if (is_file($cfgFile)) {
  $cfg=require $cfgFile;
  if (is_array($cfg) && ($cfg['db']??'')==='crist668_colegio_patrona_laflorida') {
   $db=new mysqli($cfg['host'],$cfg['user'],$cfg['pass'],$cfg['db']);
   $db->set_charset('utf8mb4');
   $stmt=$db->prepare("SELECT id_seccion,visible,estado,orden FROM seccion WHERE id_institucion=2 AND nombre_interno='hero_principal' LIMIT 1");
   $stmt->execute();$heroSection=$stmt->get_result()->fetch_assoc();
   if ($heroSection) {
    foreach ($portada['contenedores'] as &$c) {
     if (($c['clave']??'')!=='hero') continue;
     $c['visible']=($heroSection['visible']==='si' && $heroSection['estado']==='activo');
     $c['orden']=(int)$heroSection['orden'];
     $id=(int)$heroSection['id_seccion'];
     $stmtItems=$db->prepare("SELECT * FROM seccion_item WHERE id_seccion=? AND visible='si' ORDER BY orden,id_item");
     $stmtItems->bind_param('i',$id);$stmtItems->execute();
     $slides=$stmtItems->get_result()->fetch_all(MYSQLI_ASSOC);
     if ($slides) {
      $c['datos']['slides']=array_map(static function(array $slide):array {
       return [
        'etiqueta'=>(string)($slide['etiqueta']??''),
        'titulo'=>(string)($slide['titulo']??''),
        'texto'=>(string)($slide['descripcion']?:($slide['subtitulo']??'')),
        'imagen'=>(string)($slide['imagen']??''),
        'video'=>(string)($slide['url']??''),
        'video_youtube'=>(string)($slide['video_youtube']??''),
        'boton'=>(string)($slide['boton_1_texto']??''),
        'enlace'=>(string)($slide['boton_1_url']??'')
       ];
      },$slides);
      $c['datos']=array_merge($c['datos'],$c['datos']['slides'][0]);
      $conf=$db->prepare('SELECT clave,valor FROM seccion_config WHERE id_seccion=?');
      $conf->bind_param('i',$id);$conf->execute();
      $c['datos']['config']=array_column($conf->get_result()->fetch_all(MYSQLI_ASSOC),'valor','clave');
     }
    }
    unset($c);
   }
   $db->close();
  }
 }
} catch (Throwable $e) { error_log('Patrona CMS portada: '.$e->getMessage()); }

$contenedores = array_values(array_filter($portada['contenedores'] ?? [], static fn(array $c): bool => ($c['visible'] ?? false) === true));
usort($contenedores, static fn(array $a,array $b): int => ($a['orden'] ?? 0) <=> ($b['orden'] ?? 0));
?>
<!doctype html>
<html lang="es"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="Colegio Patrona de Lourdes, La Florida. Nueva página institucional en preparación.">
<title><?= patrona_e($portada['colegio'] ?? 'Colegio') ?> | <?= patrona_e($portada['sede'] ?? '') ?></title>
<link rel="stylesheet" href="assets/css/patrona.css?v=3">
</head><body>
<div class="topline"><div class="wrap"><span>COLEGIO PATRONA DE LOURDES · LA FLORIDA</span><span>Sitio en desarrollo</span></div></div>
<header class="site-header"><div class="wrap nav"><a class="brand" href="#inicio" aria-label="Inicio Colegio Patrona de Lourdes"><img class="brand-logo" src="assets/imagenes/LOGO-COLEGIO--Sin-fondo.png" alt="Colegio Patrona de Lourdes"><span class="brand-sede">LA FLORIDA</span></a>
<nav aria-label="Navegación principal"><ul><?php foreach (($portada['menu'] ?? []) as $m): ?><li><a href="<?= patrona_e($m['url']) ?>"><?= patrona_e($m['texto']) ?></a></li><?php endforeach; ?></ul></nav>
<a class="nav-cta" href="#contacto">Contáctanos ↗</a></div></header>
<main><?php foreach ($contenedores as $contenedor) patrona_render($contenedor); ?></main>
<footer><div class="wrap footer-row"><strong>COLEGIO PATRONA DE LOURDES <small>LA FLORIDA</small></strong><span>Portada demostrativa · Datos y recursos institucionales pendientes de validación</span></div></footer>
</body></html>
