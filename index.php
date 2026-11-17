<?php
declare(strict_types=1);
$portada = require __DIR__ . '/config/portada.php';
require_once __DIR__ . '/componentes/portada.php';
$contenedores = array_values(array_filter($portada['contenedores'] ?? [], static fn(array $c): bool => ($c['visible'] ?? false) === true));
usort($contenedores, static fn(array $a,array $b): int => ($a['orden'] ?? 0) <=> ($b['orden'] ?? 0));
?>
<!doctype html>
<html lang="es"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="Colegio Patrona de Lourdes, La Florida. Nueva página institucional en preparación.">
<title><?= patrona_e($portada['colegio'] ?? 'Colegio') ?> | <?= patrona_e($portada['sede'] ?? '') ?></title>
<link rel="stylesheet" href="assets/css/patrona.css">
</head><body>
<div class="topline"><div class="wrap"><span>COLEGIO PATRONA DE LOURDES · LA FLORIDA</span><span>Sitio en desarrollo</span></div></div>
<header class="site-header"><div class="wrap nav"><a class="brand" href="#inicio" aria-label="Inicio Colegio Patrona de Lourdes"><span class="brand-symbol" aria-hidden="true">PL</span><span>COLEGIO <strong>PATRONA DE LOURDES</strong><small>LA FLORIDA</small></span></a>
<nav aria-label="Navegación principal"><ul><?php foreach (($portada['menu'] ?? []) as $m): ?><li><a href="<?= patrona_e($m['url']) ?>"><?= patrona_e($m['texto']) ?></a></li><?php endforeach; ?></ul></nav>
<a class="nav-cta" href="#contacto">Contáctanos ↗</a></div></header>
<main><?php foreach ($contenedores as $contenedor) patrona_render($contenedor); ?></main>
<footer><div class="wrap footer-row"><strong>COLEGIO PATRONA DE LOURDES <small>LA FLORIDA</small></strong><span>Portada demostrativa · Datos y recursos institucionales pendientes de validación</span></div></footer>
</body></html>
