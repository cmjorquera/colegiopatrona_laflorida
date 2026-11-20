<?php
declare(strict_types=1);
/**
 * Colegio Patrona de Lourdes — primera etapa del administrador de contenedores.
 * Requiere configuración privada class/conexion_local.php y una cuenta en usuario.
 * No modifica la portada ni realiza escrituras en la base de datos.
 */
// Guardar sesiones fuera del directorio público del subdominio.
if (session_status() === PHP_SESSION_NONE) {
    $sessionPath = '/home3/crist668/php_sessions_patrona';
    if (!is_dir($sessionPath) || !is_writable($sessionPath)) {
        error_log('Patrona CMS: directorio de sesiones no disponible: ' . $sessionPath);
        http_response_code(503);
        exit('El administrador no está disponible temporalmente.');
    }
    session_save_path($sessionPath);
    if (!session_start()) {
        error_log('Patrona CMS: no fue posible iniciar la sesión.');
        http_response_code(503);
        exit('El administrador no está disponible temporalmente.');
    }
}
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function db(): mysqli {
    $file = __DIR__ . '/class/conexion_local.php';
    if (!is_file($file)) { throw new RuntimeException('Falta class/conexion_local.php (configuración privada no versionada).'); }
    $cfg = require $file;
    if (!is_array($cfg)) { throw new RuntimeException('Configuración de base de datos inválida.'); }
    foreach (['host','db','user','pass'] as $key) {
        if (!array_key_exists($key, $cfg)) { throw new RuntimeException('Falta la clave de configuración: ' . $key); }
    }
    if ($cfg['db'] !== 'crist668_colegio_patrona_laflorida') {
        throw new RuntimeException('La base configurada no corresponde a Patrona de Lourdes.');
    }
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $db = new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db']);
    $db->set_charset('utf8mb4');
    return $db;
}
if (empty($_SESSION['patrona_csrf'])) { $_SESSION['patrona_csrf'] = bin2hex(random_bytes(32)); }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['patrona_csrf'], (string)($_POST['csrf'] ?? ''))) {
        http_response_code(403); $error = 'Solicitud no válida.';
    } elseif (($_POST['accion'] ?? '') === 'salir') {
        $_SESSION = []; session_regenerate_id(true);
        header('Location: admin.php'); exit;
    } elseif (($_POST['accion'] ?? '') === 'toggle_contenedor' && !empty($_SESSION['patrona_admin_id'])) {
        try {
            $sectionId = filter_var($_POST['id_seccion'] ?? null, FILTER_VALIDATE_INT);
            $field = (string)($_POST['campo'] ?? '');
            if (!$sectionId || !in_array($field, ['visible','estado'], true)) {
                throw new RuntimeException('Solicitud de contenedor inválida.');
            }
            $db = db();
            $stmt = $db->prepare("SELECT visible,estado,editable FROM seccion WHERE id_seccion=? AND id_institucion=2 LIMIT 1");
            $stmt->bind_param('i', $sectionId);
            $stmt->execute();
            $current = $stmt->get_result()->fetch_assoc();
            if (!$current) { throw new RuntimeException('Contenedor no encontrado.'); }
            $next = $field === 'visible'
                ? (($current['visible'] === 'si') ? 'no' : 'si')
                : (($current['estado'] === 'activo') ? 'inactivo' : 'activo');
            $update = $db->prepare("UPDATE seccion SET $field=? WHERE id_seccion=? AND id_institucion=2");
            $update->bind_param('si', $next, $sectionId);
            $update->execute();
            $_SESSION['patrona_notice'] = 'Estado del contenedor actualizado.';
        } catch (Throwable $e) {
            error_log('Patrona CMS toggle: ' . $e->getMessage());
            $_SESSION['patrona_notice'] = 'No fue posible actualizar el contenedor.';
        }
        header('Location: admin.php?panel=contenedores');
        exit;
    } elseif (($_POST['accion'] ?? '') === 'guardar_menu_publico' && !empty($_SESSION['patrona_admin_id'])) {
        try {
            $db=db();$id=(int)($_POST['id_menu']??0);$nombre=trim((string)($_POST['nombre']??''));$url=trim((string)($_POST['url']??''));$estado=isset($_POST['estado'])?1:0;
            if($nombre===''||mb_strlen($nombre)>180||mb_strlen($url)>255)throw new RuntimeException('Datos inválidos.');
            if($url!=='' && $url!=='#' && !preg_match('~^(https?://|/|#|[a-zA-Z0-9_])~',$url))throw new RuntimeException('URL no válida.');
            if($id>0){$q=$db->prepare('UPDATE menus SET nombre=?,url=?,estado=? WHERE id_menu=?');$q->bind_param('ssii',$nombre,$url,$estado,$id);}
            else{$orden=(int)$db->query('SELECT COALESCE(MAX(orden),0)+1 AS orden FROM menus')->fetch_assoc()['orden'];$q=$db->prepare('INSERT INTO menus(nombre,url,icono,orden,estado,fecha_creacion,hora_creacion,ip_creacion) VALUES (?,?,"",?,?,CURDATE(),CURTIME(),"127.0.0.1")');$q->bind_param('ssii',$nombre,$url,$orden,$estado);}
            $q->execute();$_SESSION['patrona_notice']='Menú público guardado.';
        }catch(Throwable $e){error_log('Patrona guardar menu: '.$e->getMessage());$_SESSION['patrona_notice']='No fue posible guardar el menú. Comprueba el esquema de menus.';}
        header('Location: admin.php?panel=menus');exit;
    } elseif (($_POST['accion'] ?? '') === 'guardar_submenu_publico' && !empty($_SESSION['patrona_admin_id'])) {
        try{
            $db=db();$id=(int)($_POST['id_sub_menu']??0);$padre=(int)($_POST['id_menu']??0);$nombre=trim((string)($_POST['nombre']??''));$url=trim((string)($_POST['url']??''));$estado=isset($_POST['estado'])?1:0;
            if($padre<1||$nombre===''||mb_strlen($nombre)>180||mb_strlen($url)>255)throw new RuntimeException('Datos inválidos.');
            if($url!=='' && $url!=='#' && !preg_match('~^(https?://|/|#|[a-zA-Z0-9_])~',$url))throw new RuntimeException('URL no válida.');
            $q=$db->prepare('SELECT id_menu FROM menus WHERE id_menu=?');$q->bind_param('i',$padre);$q->execute();if(!$q->get_result()->fetch_assoc())throw new RuntimeException('Menú padre no existe.');
            if($id>0){$q=$db->prepare('UPDATE sub_menus SET id_menu=?,nombre=?,url=?,estado=? WHERE id_sub_menu=?');$q->bind_param('issii',$padre,$nombre,$url,$estado,$id);}
            else{$o=$db->prepare('SELECT COALESCE(MAX(orden),0)+1 AS orden FROM sub_menus WHERE id_menu=?');$o->bind_param('i',$padre);$o->execute();$orden=(int)$o->get_result()->fetch_assoc()['orden'];$q=$db->prepare('INSERT INTO sub_menus(id_menu,nombre,url,icono,orden,estado,fecha_creacion,hora_creacion,ip_creacion) VALUES (?,?,?,"",?,?,CURDATE(),CURTIME(),"127.0.0.1")');$q->bind_param('issii',$padre,$nombre,$url,$orden,$estado);}
            $q->execute();$_SESSION['patrona_notice']='Submenú guardado.';
        }catch(Throwable $e){error_log('Patrona guardar submenu: '.$e->getMessage());$_SESSION['patrona_notice']='No fue posible guardar el submenú.';}
        header('Location: admin.php?panel=menus');exit;
    } elseif (($_POST['accion'] ?? '') === 'ingresar') {
        try {
            $db = db();
            $email = trim((string)($_POST['usuario'] ?? $_POST['email'] ?? ''));
            $clave = (string)($_POST['clave'] ?? '');
            $stmt = $db->prepare("SELECT id_usuario,id_institucion,nombre,clave,rol,estado FROM usuario WHERE (email=? OR usuario=?) LIMIT 1");
            $stmt->bind_param('ss', $email, $email); $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            if ($user && $user['estado'] === 'activo' && (int)$user['id_institucion'] === 2
                && password_verify($clave, (string)$user['clave'])
                && in_array($user['rol'], ['super_admin','admin_institucion'], true)) {
                session_regenerate_id(true);
                $_SESSION['patrona_admin_id'] = (int)$user['id_usuario'];
                $_SESSION['patrona_admin_nombre'] = (string)$user['nombre'];
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') { header('Content-Type: application/json; charset=utf-8'); echo json_encode(['ok'=>true,'redirect'=>'admin.php?panel=contenedores']); exit; }
                header('Location: admin.php?panel=contenedores'); exit;
            }
            $error = 'Credenciales incorrectas o acceso no autorizado.';
        } catch (Throwable $e) {
            error_log('Patrona CMS login: ' . $e->getMessage());
            $error = 'No fue posible iniciar sesión. Revisa la configuración del servidor.';
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'ingresar' && ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(401); echo json_encode(['ok'=>false,'msg'=>$error]); exit;
}
$logged = !empty($_SESSION['patrona_admin_id']);
$sections = [];
$publicMenus = [];
$publicSubs = [];
if ($logged) {
    try {
        $db = db();
        $sql = "SELECT id_seccion,nombre_interno,titulo_admin,tipo_seccion,visible,estado,editable,orden
                FROM seccion WHERE id_institucion=2 ORDER BY orden,id_seccion";
        $sections = $db->query($sql)->fetch_all(MYSQLI_ASSOC);
        try { $publicMenus=$db->query('SELECT id_menu,nombre,url,icono,orden,estado FROM menus ORDER BY orden,id_menu')->fetch_all(MYSQLI_ASSOC); $publicSubs=$db->query('SELECT id_sub_menu,id_menu,nombre,url,orden,estado FROM sub_menus ORDER BY orden,id_sub_menu')->fetch_all(MYSQLI_ASSOC); } catch(Throwable $err){ error_log('Patrona menu: '.$err->getMessage()); }
    } catch (Throwable $e) {
        error_log('Patrona CMS listado: ' . $e->getMessage());
        $error = 'No se pudo cargar el listado. Revisa la conexión MySQL.';
    }
}
?>
<?php
if (!$logged):
    $institution = [];
    try {
        $connection = db();
        $query = $connection->prepare('SELECT * FROM institucion WHERE id_institucion = ? LIMIT 1');
        $institutionId = 2;
        $query->bind_param('i', $institutionId);
        $query->execute();
        $institution = $query->get_result()->fetch_assoc() ?: [];
        $query->close();
    } catch (Throwable $e) {
        error_log('Patrona CMS identidad login: ' . $e->getMessage());
    }
    $institution['nombre'] = $institution['nombre'] ?? 'Colegio Patrona de Lourdes - La Florida';
    // Priorizar imágenes institucionales realmente presentes en el hosting.
    $assetCandidates = [
        trim((string)($institution['logo_header'] ?? '')),
        'assets/imagenes/LOGO-COLEGIO--Sin-fondo.png',
        'assets/imagenes/logo-colegio-1024x598.jpg',
        'imagenes/logo-colegio-1024x598.jpg',
        'imagenes/LOGO-COLEGIO--Sin-fondo.png'
    ];
    $institution['logo_header'] = '';
    foreach ($assetCandidates as $candidate) {
        if ($candidate !== '' && preg_match('~^(?:assets|imagenes|uploads)/[a-zA-Z0-9_./%-]+$~', $candidate) && is_file(__DIR__ . '/' . $candidate)) {
            $institution['logo_header'] = $candidate;
            break;
        }
    }
    // Usar una fotografía publicada por el CMS si existe; de lo contrario, poster institucional.
    $institution['imagen_login'] = '';
    try {
        $photo = $connection->query("SELECT si.imagen FROM seccion_item si INNER JOIN seccion s ON s.id_seccion=si.id_seccion WHERE s.id_institucion=2 AND s.nombre_interno='hero_principal' AND si.visible='si' AND si.imagen IS NOT NULL AND si.imagen<>'' ORDER BY si.orden,si.id_item LIMIT 1")->fetch_assoc();
        $candidate = trim((string)($photo['imagen'] ?? ''));
        if ($candidate !== '' && preg_match('~^(?:assets|imagenes|uploads)/[a-zA-Z0-9_./%-]+$~', $candidate) && is_file(__DIR__ . '/' . $candidate)) {
            $institution['imagen_login'] = $candidate;
        }
    } catch (Throwable $photoError) { error_log('Patrona login foto: '.$photoError->getMessage()); }
    if ($institution['imagen_login'] === '') {
        foreach (['imagenes/hero-patrona.png','assets/imagenes/hero-patrona.png','assets/imagenes/logo-colegio-1024x598.jpg'] as $candidate) {
            if (is_file(__DIR__ . '/' . $candidate)) { $institution['imagen_login'] = $candidate; break; }
        }
    }
    function cms_e(?string $value): string { return h((string)$value); }
    require __DIR__ . '/includes/admin_login.php';
    exit;
endif;
?>

<?php
$total = count($sections);
$visibleCount = count(array_filter($sections, static fn($s) => ($s['visible'] ?? '') === 'si'));
$activeCount = count(array_filter($sections, static fn($s) => ($s['estado'] ?? '') === 'activo'));
$notice = (string)($_SESSION['patrona_notice'] ?? '');
unset($_SESSION['patrona_notice']);
$panel = (string)($_GET['panel'] ?? 'contenedores');
if (!in_array($panel, ['dashboard','contenedores','menus'], true)) { $panel = 'contenedores'; }
$subsByMenu=[]; foreach($publicSubs as $sub){$subsByMenu[(int)$sub['id_menu']][]=$sub;}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>CMS | Patrona de Lourdes · La Florida</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="assets/admin/css/patrona-panel.css?v=4">
<link rel="stylesheet" href="assets/admin/css/patrona-admin-menu.css?v=3">
</head>
<body>
<div class="admin-shell">
<?php $panel = $panel ?? 'dashboard'; require __DIR__.'/includes/patrona_sidebar.php'; ?>
<div class="admin-content">
<header class="topbar"><span><i class="bi bi-list"></i> Panel de administración <span class="topbar-muted">/ <?= $panel==='dashboard'?'Inicio':($panel==='menus'?'Menú público':'Contenedores del sitio') ?></span></span><a href="index.php" target="_blank" rel="noopener">Ver sitio <i class="bi bi-arrow-up-right"></i></a></header>
<main class="admin-main <?= $panel==='contenedores'?'cms-containers-page':'' ?>">
<div class="page-heading"><div><div class="eyebrow">COLEGIO PATRONA DE LOURDES · LA FLORIDA</div><h1><?= $panel==='dashboard'?'Dashboard':($panel==='menus'?'Menú público':'Contenedores del sitio') ?></h1><p><?= $panel==='dashboard'?'Resumen de la administración del sitio web.':($panel==='menus'?'Administra menús y submenús del encabezado.':'Gestiona la visibilidad y el estado de las secciones de la página.') ?></p></div><span class="section-pill"><i class="bi bi-database-check"></i> MySQL conectado</span></div>
<?php if ($error): ?><div class="alert error"><?=h($error)?></div><?php endif; ?>
<?php if ($notice): ?><div class="alert"><?=h($notice)?></div><?php endif; ?>
<?php if($panel!=='menus'): ?>
<section class="kpi-grid">
<div class="kpi-card"><span class="kpi-icon blue"><i class="bi bi-layers"></i></span><div><small>Total contenedores</small><strong><?= $total ?></strong><span>Secciones registradas</span></div></div>
<div class="kpi-card"><span class="kpi-icon green"><i class="bi bi-eye"></i></span><div><small>Visibles</small><strong><?= $visibleCount ?></strong><span>Mostrados en el CMS</span></div></div>
<div class="kpi-card"><span class="kpi-icon amber"><i class="bi bi-check-circle"></i></span><div><small>Activos</small><strong><?= $activeCount ?></strong><span>Secciones habilitadas</span></div></div>
<div class="kpi-card"><span class="kpi-icon violet"><i class="bi bi-eye-slash"></i></span><div><small>Ocultos</small><strong><?= $total-$visibleCount ?></strong><span>No visibles</span></div></div>
</section>
<section class="content-card cms-containers-card"><div class="card-header"><div><h2><?= $panel==='dashboard'?'Resumen de contenedores':'Contenedores del sitio' ?></h2><p><?= $panel==='dashboard'?'Configuración de las secciones de la institución.':'Panel general de bloques de index.php. Los contenedores fijos permanecen bloqueados.' ?></p></div><span class="count"><?= $total ?> registros</span></div><?php if($panel==='contenedores'):?><div class="cms-container-toolbar"><label for="cms-container-search">Buscar:</label><input id="cms-container-search" type="search" placeholder="Buscar contenedor" autocomplete="off"></div><?php endif;?>
<div class="table-wrap"><table id="cms-container-table"><thead><tr><th>Orden</th><th>Contenedor</th><?php if($panel==='contenedores'):?><th>Qué controla</th><?php else:?><th>Tipo</th><?php endif;?><th>Estado</th><th>Visible</th><?php if($panel!=='contenedores'):?><th>Editable</th><?php endif;?><th>Acciones</th></tr></thead><tbody>
<?php foreach ($sections as $section): ?>
<tr>
<td><span class="order"><?= (int)$section['orden'] ?></span></td>
<td><div class="section-name"><?=h((string)$section['titulo_admin'])?></div><small><?=h((string)$section['nombre_interno'])?></small></td>
<?php if($panel==='contenedores'):?><td class="cms-container-description"><?=h((string)($section['descripcion']??$section['titulo_admin']))?></td><?php else:?><td><span class="type-label"><?=h((string)$section['tipo_seccion'])?></span></td><?php endif;?>

<td><form method="post" class="inline-form"><input type="hidden" name="csrf" value="<?=h($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="toggle_contenedor"><input type="hidden" name="campo" value="estado"><input type="hidden" name="id_seccion" value="<?=(int)$section['id_seccion']?>"><button type="submit" class="status <?=($section['estado']==='activo')?'enabled':'disabled'?>"><?=($section['estado']==='activo')?'Activo':'Inactivo'?></button></form></td><td><form method="post" class="inline-form"><input type="hidden" name="csrf" value="<?=h($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="toggle_contenedor"><input type="hidden" name="campo" value="visible"><input type="hidden" name="id_seccion" value="<?=(int)$section['id_seccion']?>"><button type="submit" class="switch <?=($section['visible']==='si')?'on':''?>" aria-label="Cambiar visibilidad de <?=h((string)$section['titulo_admin'])?>" title="Cambiar visibilidad" aria-pressed="<?=($section['visible']==='si')?'true':'false'?>"><span></span></button></form><small><?=($section['visible']==='si')?'Visible':'Oculto'?></small></td><?php if($panel!=='contenedores'):?>
<td><?= ((string)$section['editable']==='si'||(string)$section['editable']==='1')?'<span class="tag">Editable</span>':'<span class="tag muted"><i class="bi bi-lock"></i> Fijo</span>' ?></td>
<?php endif;?><td><a class="icon-action" href="index.php" target="_blank" rel="noopener" title="Ver sitio público"><i class="bi bi-eye"></i></a><?php if (($section['nombre_interno']??'')==='hero_principal'): ?><a class="icon-action" href="editar_contenedor.php?id=<?=(int)$section['id_seccion']?>" title="Editar portada"><i class="bi bi-pencil-square"></i></a><?php elseif (($section['nombre_interno']??'')==='sellos_home' && in_array((string)($section['editable']??''),['si','1'],true)): ?><a class="icon-action" href="editar_sellos.php" title="Editar sellos institucionales"><i class="bi bi-pencil-square"></i></a><?php elseif (($section['nombre_interno']??'')==='tab_comunidad_home' && in_array((string)($section['editable']??''),['si','1'],true)): ?><a class="icon-action" href="editar_comunidad.php" title="Editar Comunidad escolar · Familias"><i class="bi bi-pencil-square"></i></a><?php elseif (($section['nombre_interno']??'')==='noticias_home' && in_array((string)($section['editable']??''),['si','1'],true)): ?><a class="icon-action" href="editar_noticias.php" title="Editar noticias"><i class="bi bi-pencil-square"></i></a><?php elseif (($section['nombre_interno']??'')==='topbar' && in_array((string)($section['editable']??''),['si','1'],true)): ?><a class="icon-action" href="editar_topbar.php" title="Editar barra superior"><i class="bi bi-pencil-square"></i></a><?php elseif (($section['nombre_interno']??'')==='tab_destacados_home' && in_array((string)($section['editable']??''),['si','1'],true)): ?><a class="icon-action" href="editar_destacados.php" title="Editar cuatro tarjetas destacadas"><i class="bi bi-pencil-square"></i></a><?php elseif (in_array((string)($section['editable']??''),['si','1'],true)): ?><a class="icon-action" href="editar_cms.php?id=<?=(int)$section['id_seccion']?>" title="Editar contenedor"><i class="bi bi-pencil-square"></i></a><?php else: ?><span class="icon-action disabled" title="Contenedor no editable"><i class="bi bi-pencil-square"></i></span><?php endif; ?></td>
</tr>
<?php endforeach; ?>
<?php if (!$sections): ?><tr><td colspan="7">No hay contenedores registrados.</td></tr><?php endif; ?>
</tbody></table></div><?php if($panel==='contenedores'):?><div class="cms-container-pagination"><span id="cms-container-count"></span><div><button type="button" id="cms-container-prev">Anterior</button><span id="cms-container-pages"></span><button type="button" id="cms-container-next">Siguiente</button></div></div><?php endif;?>
<div class="card-footer">Los cambios de visibilidad y estado se guardan en MySQL. La edición de la portada y los sellos ya está disponible; los demás editores siguen en desarrollo.</div>
</section>
<?php else: ?>
<section class="content-card patrona-menu-admin">
  <div class="card-header"><div><h2>Menú principal</h2><p>Expande cada menú para administrar sus submenús.</p></div><span class="count"><?=count($publicMenus)?> menús · <?=count($publicSubs)?> submenús</span></div>
  <div class="patrona-menu-list">
  <?php foreach($publicMenus as $menu):$children=$subsByMenu[(int)$menu['id_menu']]??[];?>
    <details class="patrona-menu-entry">
      <summary><span class="menu-order"><?=(int)$menu['orden']?></span><strong><?=h((string)$menu['nombre'])?></strong><span class="menu-count"><?=count($children)?> submenús</span><span class="menu-status"><?=((int)$menu['estado']===1)?'Activo':'Inactivo'?></span></summary>
      <form class="menu-editor-form" method="post">
        <input type="hidden" name="csrf" value="<?=h($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="guardar_menu_publico"><input type="hidden" name="id_menu" value="<?=(int)$menu['id_menu']?>">
        <label>Nombre<input name="nombre" required maxlength="180" value="<?=h((string)$menu['nombre'])?>"></label>
        <label>Enlace<input name="url" maxlength="255" value="<?=h((string)$menu['url'])?>"></label>
        <label class="menu-check"><input type="checkbox" name="estado" value="1" <?=((int)$menu['estado']===1)?'checked':''?>> Activo</label>
        <button type="submit">Guardar</button>
      </form>
      <div class="patrona-menu-children"><h3>Submenús</h3>
        <?php foreach($children as $sub):?>
          <form class="menu-editor-form" method="post">
            <input type="hidden" name="csrf" value="<?=h($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="guardar_submenu_publico"><input type="hidden" name="id_menu" value="<?=(int)$menu['id_menu']?>"><input type="hidden" name="id_sub_menu" value="<?=(int)$sub['id_sub_menu']?>">
            <label>Nombre<input name="nombre" required maxlength="180" value="<?=h((string)$sub['nombre'])?>"></label>
            <label>Enlace<input name="url" maxlength="255" value="<?=h((string)$sub['url'])?>"></label>
            <label class="menu-check"><input type="checkbox" name="estado" value="1" <?=((int)$sub['estado']===1)?'checked':''?>> Activo</label><button type="submit">Guardar</button>
          </form>
        <?php endforeach;?>
        <form class="menu-editor-form add-sub" method="post">
          <input type="hidden" name="csrf" value="<?=h($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="guardar_submenu_publico"><input type="hidden" name="id_menu" value="<?=(int)$menu['id_menu']?>">
          <label>Nuevo submenú<input name="nombre" required maxlength="180" placeholder="Nombre"></label>
          <label>Enlace<input name="url" maxlength="255" placeholder="#seccion o URL"></label>
          <label class="menu-check"><input type="checkbox" name="estado" value="1" checked> Activo</label><button type="submit">Agregar</button>
        </form>
      </div>
    </details>
  <?php endforeach;?>
  </div>
  <form class="menu-editor-form add-menu" method="post">
    <input type="hidden" name="csrf" value="<?=h($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="guardar_menu_publico">
    <label>Nuevo menú<input name="nombre" required maxlength="180" placeholder="Nombre"></label>
    <label>Enlace<input name="url" maxlength="255" placeholder="#seccion o URL"></label>
    <label class="menu-check"><input type="checkbox" name="estado" value="1" checked> Activo</label><button type="submit">Agregar menú</button>
  </form>
</section>
<?php endif; ?>
</main></div></div><script>if(document.getElementById("cms-container-table")){(()=>{const rows=[...document.querySelectorAll("#cms-container-table tbody tr")],search=document.getElementById("cms-container-search"),count=document.getElementById("cms-container-count"),pages=document.getElementById("cms-container-pages"),prev=document.getElementById("cms-container-prev"),next=document.getElementById("cms-container-next");if(!search)return;let page=1;const draw=()=>{const found=rows.filter(r=>r.textContent.toLocaleLowerCase().includes(search.value.trim().toLocaleLowerCase())),max=Math.max(1,Math.ceil(found.length/10));page=Math.min(page,max);rows.forEach(r=>r.hidden=true);found.slice((page-1)*10,page*10).forEach(r=>r.hidden=false);count.textContent=`Mostrando ${found.length?((page-1)*10+1):0} a ${Math.min(page*10,found.length)} de ${found.length} contenedores`;pages.textContent=` ${page} / ${max} `;prev.disabled=page===1;next.disabled=page===max;};search.addEventListener("input",()=>{page=1;draw()});prev.addEventListener("click",()=>{page--;draw()});next.addEventListener("click",()=>{page++;draw()});draw()})()}</script><script defer src="assets/admin/js/patrona-sidebar.js?v=4"></script></body></html>
