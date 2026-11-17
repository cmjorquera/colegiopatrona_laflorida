<?php
declare(strict_types=1);
/**
 * Colegio Patrona de Lourdes — primera etapa del administrador de contenedores.
 * Requiere configuración privada class/conexion_local.php y una cuenta en usuario.
 * No modifica la portada ni realiza escrituras en la base de datos.
 */
session_start();
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

<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Contenedores | Patrona de Lourdes</title><link rel="stylesheet" href="assets/admin/css/patrona-panel.css">
</head><body><div class="layout"><aside>
<img src="assets/imagenes/LOGO-COLEGIO--Sin-fondo.png" alt="Logo institucional">
<h3>Patrona de Lourdes</h3><small style="color:#d5e7fa">La Florida</small>
<a href="admin.php?panel=contenedores">Contenedores</a>
<a href="index.php">Ver sitio público</a>
<form method="post"><input type="hidden" name="csrf" value="<?=h($_SESSION['patrona_csrf'])?>">
<input type="hidden" name="accion" value="salir"><button type="submit">Cerrar sesión</button></form>
</aside><main><div class="panel"><h1>Contenedores</h1>
<p class="muted">Listado de secciones registradas en MySQL. Edición y ordenamiento pendientes de la siguiente implementación.</p>
<?php if ($error): ?><div class="error"><?=h($error)?></div><?php endif; ?>
<table><thead><tr><th>Orden</th><th>Contenedor</th><th>Tipo</th><th>Visible</th><th>Estado</th></tr></thead><tbody>
<?php foreach ($sections as $section): ?><tr>
<td><?= (int)$section['orden'] ?></td><td><?=h((string)$section['titulo_admin'])?><br><small><?=h((string)$section['nombre_interno'])?></small></td>
<td><?=h((string)$section['tipo_seccion'])?></td><td><?=h((string)$section['visible'])?></td><td><?=h((string)$section['estado'])?></td>
</tr><?php endforeach; ?>
<?php if (!$sections): ?><tr><td colspan="5">Todavía no hay contenedores registrados.</td></tr><?php endif; ?>
</tbody></table></div></main></div>
</body></html>