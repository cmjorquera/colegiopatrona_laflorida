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
if ($logged) {
    try {
        $db = db();
        $sql = "SELECT id_seccion,nombre_interno,titulo_admin,tipo_seccion,visible,estado,editable,orden
                FROM seccion WHERE id_institucion=2 ORDER BY orden,id_seccion";
        $sections = $db->query($sql)->fetch_all(MYSQLI_ASSOC);
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
    $institution['logo_header'] = 'assets/imagenes/LOGO-COLEGIO--Sin-fondo.png';
    $institution['imagen_login'] = '';
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
if (!in_array($panel, ['dashboard','contenedores'], true)) { $panel = 'contenedores'; }
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>CMS | Patrona de Lourdes · La Florida</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="assets/admin/css/patrona-panel.css?v=2">
</head>
<body>
<div class="admin-shell">
<aside class="admin-sidebar">
  <div class="sidebar-brand"><span class="sidebar-brand-icon"><img src="assets/imagenes/LOGO-COLEGIO--Sin-fondo.png" alt="" onerror="this.style.display='none'"></span><span><strong>Patrona de Lourdes</strong><small>La Florida · Panel CMS</small></span></div>
  <nav class="sidebar-nav">
    <span class="nav-group-label">PRINCIPAL</span>
    <a class="nav-item <?= $panel==='dashboard'?'active':'' ?>" href="admin.php?panel=dashboard"><i class="bi bi-grid-1x2"></i> Inicio</a>
    <span class="nav-group-label">GESTIÓN DEL SITIO</span>
    <a class="nav-item <?= $panel==='contenedores'?'active':'' ?>" href="admin.php?panel=contenedores"><i class="bi bi-layers"></i> Contenedores</a>
    <a class="nav-item" href="index.php" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Ver sitio público</a>
  </nav>
  <div class="sidebar-bottom"><div class="admin-user"><span class="avatar"><i class="bi bi-person"></i></span><span><strong><?=h((string)($_SESSION['patrona_admin_nombre'] ?? 'Administrador'))?></strong><small>Administrador institucional</small></span></div>
  <form method="post"><input type="hidden" name="csrf" value="<?=h($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="salir"><button class="logout" type="submit"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</button></form></div>
</aside>
<div class="admin-content">
<header class="topbar"><span><i class="bi bi-list"></i> Panel de administración <span class="topbar-muted">/ <?= $panel==='dashboard'?'Inicio':'Contenedores del sitio' ?></span></span><a href="index.php" target="_blank" rel="noopener">Ver sitio <i class="bi bi-arrow-up-right"></i></a></header>
<main class="admin-main">
<div class="page-heading"><div><div class="eyebrow">COLEGIO PATRONA DE LOURDES · LA FLORIDA</div><h1><?= $panel==='dashboard'?'Dashboard':'Contenedores del sitio' ?></h1><p><?= $panel==='dashboard'?'Resumen de la administración del sitio web.':'Gestiona la visibilidad y el estado de las secciones de la página.' ?></p></div><span class="section-pill"><i class="bi bi-database-check"></i> MySQL conectado</span></div>
<?php if ($error): ?><div class="alert error"><?=h($error)?></div><?php endif; ?>
<?php if ($notice): ?><div class="alert"><?=h($notice)?></div><?php endif; ?>
<section class="kpi-grid">
<div class="kpi-card"><span class="kpi-icon blue"><i class="bi bi-layers"></i></span><div><small>Total contenedores</small><strong><?= $total ?></strong><span>Secciones registradas</span></div></div>
<div class="kpi-card"><span class="kpi-icon green"><i class="bi bi-eye"></i></span><div><small>Visibles</small><strong><?= $visibleCount ?></strong><span>Mostrados en el CMS</span></div></div>
<div class="kpi-card"><span class="kpi-icon amber"><i class="bi bi-check-circle"></i></span><div><small>Activos</small><strong><?= $activeCount ?></strong><span>Secciones habilitadas</span></div></div>
<div class="kpi-card"><span class="kpi-icon violet"><i class="bi bi-eye-slash"></i></span><div><small>Ocultos</small><strong><?= $total-$visibleCount ?></strong><span>No visibles</span></div></div>
</section>
<section class="content-card"><div class="card-header"><div><h2><?= $panel==='dashboard'?'Resumen de contenedores':'Listado de contenedores' ?></h2><p>Configuración de las secciones de la institución.</p></div><span class="count"><?= $total ?> registros</span></div>
<div class="table-wrap"><table><thead><tr><th>Orden</th><th>Contenedor</th><th>Tipo</th><th>Visible</th><th>Estado</th><th>Editable</th><th>Acciones</th></tr></thead><tbody>
<?php foreach ($sections as $section): ?>
<tr>
<td><span class="order"><?= (int)$section['orden'] ?></span></td>
<td><div class="section-name"><?=h((string)$section['titulo_admin'])?></div><small><?=h((string)$section['nombre_interno'])?></small></td>
<td><span class="type-label"><?=h((string)$section['tipo_seccion'])?></span></td>
<td><form method="post" class="inline-form"><input type="hidden" name="csrf" value="<?=h($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="toggle_contenedor"><input type="hidden" name="campo" value="visible"><input type="hidden" name="id_seccion" value="<?=(int)$section['id_seccion']?>"><button type="submit" class="switch <?=($section['visible']==='si')?'on':''?>" aria-label="Cambiar visibilidad de <?=h((string)$section['titulo_admin'])?>" title="Cambiar visibilidad" aria-pressed="<?=($section['visible']==='si')?'true':'false'?>"><span></span></button></form><small><?=($section['visible']==='si')?'Visible':'Oculto'?></small></td>
<td><form method="post" class="inline-form"><input type="hidden" name="csrf" value="<?=h($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="toggle_contenedor"><input type="hidden" name="campo" value="estado"><input type="hidden" name="id_seccion" value="<?=(int)$section['id_seccion']?>"><button type="submit" class="status <?=($section['estado']==='activo')?'enabled':'disabled'?>"><?=($section['estado']==='activo')?'Activo':'Inactivo'?></button></form></td>
<td><?= ((string)$section['editable']==='si'||(string)$section['editable']==='1')?'<span class="tag">Editable</span>':'<span class="tag muted"><i class="bi bi-lock"></i> Fijo</span>' ?></td>
<td><a class="icon-action" href="index.php" target="_blank" rel="noopener" title="Ver sitio público"><i class="bi bi-eye"></i></a><?php if (($section['nombre_interno']??'')==='hero_principal'): ?><a class="icon-action" href="editar_contenedor.php?id=<?=(int)$section['id_seccion']?>" title="Editar portada"><i class="bi bi-pencil-square"></i></a><?php else: ?><span class="icon-action disabled" title="Editor pendiente"><i class="bi bi-pencil-square"></i></span><?php endif; ?></td>
</tr>
<?php endforeach; ?>
<?php if (!$sections): ?><tr><td colspan="7">No hay contenedores registrados.</td></tr><?php endif; ?>
</tbody></table></div>
<div class="card-footer">Los cambios de visibilidad y estado se guardan en MySQL. La edición de contenido y el ordenamiento se implementarán en la siguiente etapa.</div>
</section>
</main></div></div></body></html>
