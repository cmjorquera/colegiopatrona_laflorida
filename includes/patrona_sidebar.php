<?php
/** Menú administrativo único. Compatible con dashboard, contenedores y editores. */
$panel=$panel??'contenedores';
$sidebarLogo='';
foreach([
 'assets/imagenes/cropped-logo-cuadrado-sin-fondo-2-180x180.png',
 'assets/imagenes/LOGO-COLEGIO--Sin-fondo.png',
 'imagenes/LOGO-COLEGIO--Sin-fondo.png',
 'assets/imagenes/logo-colegio-1024x598.jpg'
] as $candidate){
 if(is_file(dirname(__DIR__).'/'.$candidate)){$sidebarLogo=$candidate;break;}
}
$sidebarH=static fn($v):string=>htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');
?>
<aside class="admin-sidebar patrona-sidebar-v2" id="patrona-sidebar" aria-label="Menú lateral de administración">
  <div class="sidebar-brand">
    <a class="patrona-sidebar-brand-link" href="admin.php?panel=dashboard" title="Panel principal">
      <span class="sidebar-brand-icon"><?php if($sidebarLogo!==''):?><img src="<?=$sidebarH($sidebarLogo)?>" alt="" loading="eager"><?php else:?><span class="patrona-logo-fallback">PL</span><?php endif;?></span>
      <span class="patrona-sidebar-brand-text"><strong>Patrona de Lourdes</strong><small>Panel de administración</small></span>
    </a>
  </div>
  <nav class="sidebar-nav" aria-label="Navegación administrativa">
    <span class="nav-group-label">PRINCIPAL</span>
    <a class="nav-item <?=$panel==='dashboard'?'active':''?>" href="admin.php?panel=dashboard" <?=$panel==='dashboard'?'aria-current="page"':''?> title="Inicio"><i class="bi bi-house-door-fill" aria-hidden="true"></i><span class="patrona-nav-label">INICIO</span></a>

    <span class="nav-group-label">ADMINISTRACIÓN</span>
    <button class="nav-item sidebar-group-toggle <?=in_array($panel,['contenedores','menus'],true)?'is-current-group':''?>" type="button" aria-expanded="true" aria-controls="sidebar-contenidos" title="Gestión del sitio">
      <i class="bi bi-gear-fill" aria-hidden="true"></i><span class="patrona-nav-label">Gestión del sitio</span><i class="bi bi-chevron-down nav-chevron" aria-hidden="true"></i>
    </button>
    <div class="sidebar-subitems" id="sidebar-contenidos">
      <a class="nav-item <?=$panel==='contenedores'?'active':''?>" href="admin.php?panel=contenedores" <?=$panel==='contenedores'?'aria-current="page"':''?> title="Contenedores"><i class="bi bi-grid-fill" aria-hidden="true"></i><span class="patrona-nav-label">Contenedores</span></a>
      <a class="nav-item <?=$panel==='menus'?'active':''?>" href="admin.php?panel=menus" <?=$panel==='menus'?'aria-current="page"':''?> title="Menú público"><i class="bi bi-diagram-3-fill" aria-hidden="true"></i><span class="patrona-nav-label">Menú público</span></a>
    </div>

    <span class="nav-group-label">SITIO WEB</span>
    <a class="nav-item" href="index.php" target="_blank" rel="noopener noreferrer" title="Ver sitio público"><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i><span class="patrona-nav-label">Ver sitio público</span></a>
  </nav>
  <div class="sidebar-watermark<?=$sidebarLogo!==''?' has-logo':''?>" aria-hidden="true" <?php if($sidebarLogo!==''):?>style="--sidebar-logo:url('<?=$sidebarH($sidebarLogo)?>')"<?php endif;?>></div>
  <div class="sidebar-bottom">
    <form method="post" action="admin.php" class="patrona-sidebar-logout">
      <input type="hidden" name="csrf" value="<?=$sidebarH($_SESSION['patrona_csrf']??'')?>">
      <input type="hidden" name="accion" value="salir">
      <button class="logout" type="submit" title="Cerrar sesión"><i class="bi bi-box-arrow-right" aria-hidden="true"></i><span class="patrona-nav-label">Cerrar sesión</span></button>
    </form>
    <button type="button" class="patrona-sidebar-bottom-toggle" data-sidebar-collapse title="Contraer menú"><i class="bi bi-chevron-left" aria-hidden="true"></i><span class="patrona-nav-label">Contraer</span></button>
  </div>
</aside>
