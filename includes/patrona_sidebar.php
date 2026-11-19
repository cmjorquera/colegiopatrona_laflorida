<?php
/** Navegación única del CMS. $panel indica la sección activa. */
$panel = $panel ?? 'contenedores';
?>
<aside class="admin-sidebar" id="patrona-sidebar">
  <?php
  // Marca de agua: solo un escudo PNG transparente que exista en el hosting (evita solicitudes 404).
  $sidebarWatermark='';
  foreach(['assets/imagenes/LOGO-COLEGIO--Sin-fondo.png','imagenes/LOGO-COLEGIO--Sin-fondo.png'] as $candidate){if(is_file(__DIR__.'/'.$candidate)){$sidebarWatermark=$candidate;break;}}
  ?>
  <div class="sidebar-watermark<?= $sidebarWatermark!==''?' has-logo':'' ?>" aria-hidden="true"<?php if($sidebarWatermark!==''):?> style="--sidebar-logo:url('<?=htmlspecialchars($sidebarWatermark, ENT_QUOTES, 'UTF-8')?>')"<?php endif;?>></div>
  <div class="sidebar-brand"><span class="sidebar-brand-icon"><?php $sidebarLogo='';foreach(['assets/imagenes/LOGO-COLEGIO--Sin-fondo.png','assets/imagenes/logo-colegio-1024x598.jpg','imagenes/logo-colegio-1024x598.jpg'] as $candidate){if(is_file(__DIR__.'/'.$candidate)){$sidebarLogo=$candidate;break;}} ?><?php if($sidebarLogo!==''):?><img src="<?=htmlspecialchars($sidebarLogo, ENT_QUOTES, 'UTF-8')?>" alt="Escudo del colegio" onerror="this.hidden=true;this.nextElementSibling.hidden=false"><span hidden aria-hidden="true" class="patrona-logo-fallback">PL</span><?php else:?><span aria-hidden="true" class="patrona-logo-fallback">PL</span><?php endif;?></span><span><strong>Patrona de Lourdes</strong><small>La Florida · Panel CMS</small></span></div>
  <nav class="sidebar-nav" aria-label="Navegación administrativa">
    <span class="nav-group-label">PRINCIPAL</span>
    <a class="nav-item <?= $panel==='dashboard'?'active':'' ?>" href="admin.php?panel=dashboard"><i class="bi bi-grid-1x2"></i> Dashboard</a>
    <span class="nav-group-label">GESTIÓN DEL SITIO</span>
    <button class="nav-item sidebar-group-toggle" type="button" aria-expanded="true" aria-controls="sidebar-contenidos"><i class="bi bi-layers"></i> Contenidos del sitio <i class="bi bi-chevron-down nav-chevron"></i></button>
    <div class="sidebar-subitems" id="sidebar-contenidos"><a class="nav-item <?= $panel==='contenedores'?'active':'' ?>" href="admin.php?panel=contenedores"><i class="bi bi-grid-3x3-gap"></i> Contenedores</a></div>
    <button class="nav-item sidebar-group-toggle <?= $panel==='menus'?'active':'' ?>" type="button" aria-expanded="true" aria-controls="sidebar-navegacion"><i class="bi bi-signpost-split"></i> Navegación <i class="bi bi-chevron-down nav-chevron"></i></button>
    <div class="sidebar-subitems" id="sidebar-navegacion"><a class="nav-item <?= $panel==='menus'?'active':'' ?>" href="admin.php?panel=menus"><i class="bi bi-diagram-3"></i> Menú público</a></div>
    <span class="nav-group-label">SISTEMA</span>
    <a class="nav-item" href="index.php" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Ver sitio público</a>
  </nav>
  <div class="sidebar-bottom"><div class="admin-user"><span class="avatar"><i class="bi bi-person"></i></span><span><strong><?=htmlspecialchars((string)($_SESSION['patrona_admin_nombre'] ?? 'Administrador'), ENT_QUOTES, 'UTF-8')?></strong><small>Administrador institucional</small></span></div>
  <form method="post" action="admin.php"><input type="hidden" name="csrf" value="<?=htmlspecialchars((string)($_SESSION['patrona_csrf']??''), ENT_QUOTES, 'UTF-8')?>"><input type="hidden" name="accion" value="salir"><button class="logout" type="submit"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</button></form></div>
</aside>
