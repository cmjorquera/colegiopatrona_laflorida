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
            $email = trim((string)($_POST['email'] ?? ''));
            $clave = (string)($_POST['clave'] ?? '');
            $stmt = $db->prepare("SELECT id_usuario,id_institucion,nombre,clave,rol,estado FROM usuario WHERE email=? LIMIT 1");
            $stmt->bind_param('s', $email); $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            if ($user && $user['estado'] === 'activo' && (int)$user['id_institucion'] === 2
                && password_verify($clave, (string)$user['clave'])
                && in_array($user['rol'], ['super_admin','admin_institucion'], true)) {
                session_regenerate_id(true);
                $_SESSION['patrona_admin_id'] = (int)$user['id_usuario'];
                $_SESSION['patrona_admin_nombre'] = (string)$user['nombre'];
                header('Location: admin.php?panel=contenedores'); exit;
            }
            $error = 'Credenciales incorrectas o acceso no autorizado.';
        } catch (Throwable $e) {
            error_log('Patrona CMS login: ' . $e->getMessage());
            $error = 'No fue posible iniciar sesión. Revisa la configuración del servidor.';
        }
    }
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
<!doctype html><html lang="es"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Administrador | Patrona de Lourdes</title>
<style>
:root{font-family:system-ui,Arial,sans-serif;color:#142b49;background:#f3f6fa}
*{box-sizing:border-box}body{margin:0}.layout{display:flex;min-height:100vh}
aside{background:#092d54;color:white;width:235px;padding:28px 20px}
aside img{width:95px;max-height:90px;object-fit:contain;background:white;border-radius:8px;padding:7px}
aside a{color:white;text-decoration:none;display:block;padding:13px 10px;border-radius:8px;margin-top:12px;background:#19446d}
main{flex:1;padding:35px;min-width:0}.panel{background:white;border:1px solid #dce4ee;border-radius:12px;padding:26px;box-shadow:0 3px 16px #102c4d0c}
.login{max-width:440px;margin:10vh auto}label{display:block;margin-top:16px;font-weight:600}
input{width:100%;padding:12px;border:1px solid #b9c9d9;border-radius:7px;margin-top:6px}
button{background:#1369a6;color:white;border:0;padding:12px 18px;border-radius:7px;cursor:pointer;margin-top:18px}
.error{background:#fff0ef;color:#8d2222;padding:12px;border-radius:7px;margin-bottom:15px}
table{border-collapse:collapse;width:100%;margin-top:20px}th,td{padding:12px;border-bottom:1px solid #e1e7ef;text-align:left}
th{background:#eef3f9}small{color:#52657b}.muted{color:#61758c}
@media(max-width:700px){.layout{display:block}aside{width:auto}main{padding:15px}table{display:block;overflow:auto}}
</style></head><body>
<?php if (!$logged): ?>
<div class="panel login">
<img src="assets/imagenes/logo-patrona.png" alt="Patrona de Lourdes" style="width:100px">
<h1>Administración</h1><p>Patrona de Lourdes · La Florida</p>
<?php if ($error): ?><div class="error"><?=h($error)?></div><?php endif; ?>
<form method="post" autocomplete="on">
<input type="hidden" name="csrf" value="<?=h($_SESSION['patrona_csrf'])?>">
<input type="hidden" name="accion" value="ingresar">
<label for="email">Correo electrónico</label><input id="email" name="email" type="email" required autocomplete="username">
<label for="clave">Contraseña</label><input id="clave" name="clave" type="password" required autocomplete="current-password">
<button type="submit">Ingresar</button>
</form><p><small>El acceso requiere un usuario activo autorizado en la base de datos.</small></p>
</div>
<?php else: ?>
<div class="layout"><aside>
<img src="assets/imagenes/logo-patrona.png" alt="Logo institucional">
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
<?php endif; ?></body></html>