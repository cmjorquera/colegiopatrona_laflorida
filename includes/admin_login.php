<?php
// Vista de acceso: la identidad es preparada por admin.php.
$institution = isset($institution) && is_array($institution) ? $institution : [];

$nombreInstitucion = trim((string) ($institution['nombre'] ?? '')) ?: 'Colegio Patrona de Lourdes - La Florida';
$logoInstitucion = trim((string) ($institution['logo_header'] ?? '')) ?: '';
$imagenLogin = trim((string) ($institution['imagen_login'] ?? '')) ?: '';
$esloganLogin = trim((string) ($institution['eslogan'] ?? '')) ?: 'Un colegio para aprender, compartir y crecer.';
foreach (['logoInstitucion', 'imagenLogin'] as $variable) {
    if (!preg_match('~^(?:uploads|assets|imagenes)/[a-zA-Z0-9_./%-]+$~', ${$variable})) {
        ${$variable} = '';
    }
}
$loginColors = [];
foreach (['primario' => '#F0A000', 'secundario' => '#EF6C00', 'terciario' => '#1976D2', 'cuaternario' => '#E53935'] as $key => $fallback) {
    $value = trim((string) ($institution['color_' . $key] ?? ''));
    $loginColors[$key] = preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $value) ? $value : $fallback;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Administración | <?= cms_e($nombreInstitucion) ?></title>
    <link rel="stylesheet" href="assets/admin/css/admin-login.css?v=20261113-fondo-login">
    <link rel="stylesheet" href="assets/admin/css/seduc-colegios.css?v=20261113-escudos">
    <link rel="stylesheet" href="assets/admin/css/patrona-login-premium.css?v=1">
</head>
<body class="admin-login" style="<?php foreach ($loginColors as $key => $color): ?>--login-<?= $key ?>:<?= cms_e($color) ?>;<?php endforeach; ?><?php if ($imagenLogin !== ''): ?>--login-photo:url(&quot;<?= cms_e($imagenLogin) ?>&quot;);<?php endif; ?>">
    <main class="admin-login-shell">
        <section class="admin-login-brand" aria-labelledby="institution-title" style="background-color:#102d4d;background-image:linear-gradient(90deg,rgba(5,31,58,.94) 0%,rgba(8,39,72,.81) 47%,rgba(7,34,64,.42) 100%),<?= $imagenLogin!=='' ? "url('".cms_e($imagenLogin)."')" : "none" ?>;background-position:center,center;background-size:cover,cover;background-repeat:no-repeat,no-repeat;">
            <div class="admin-login-brand-top">
                <div class="patrona-login-logo-frame"><?php if($logoInstitucion!==''):?><img class="admin-login-logo" src="<?= cms_e($logoInstitucion) ?>" alt="<?= cms_e($nombreInstitucion) ?>" id="institution-logo" onerror="this.hidden=true;this.nextElementSibling.hidden=false"><span class="patrona-crest-fallback" hidden aria-label="Patrona de Lourdes">PL</span><?php else:?><span class="patrona-crest-fallback" aria-label="Patrona de Lourdes">PL</span><?php endif;?></div>
                <span class="admin-login-brand-kicker">Gestión de sitio web</span>
            </div>
            <div class="admin-login-intro">
                <p class="admin-login-eyebrow"><span class="admin-login-status-dot"></span> Plataforma de gestión de páginas web</p>
                <h1 id="institution-title">Tu colegio.<br><span>Tu sitio web.</span><br>Todo bajo control.</h1>
                <p class="patrona-login-school-name"><?=cms_e($nombreInstitucion)?></p>
                <p>Un espacio para gestionar el sitio institucional: actualiza contenidos, noticias, imágenes y secciones desde un solo lugar.</p>
                <div class="admin-login-feature-grid" aria-label="Funciones del gestor web">
                    <div class="admin-login-feature"><span class="admin-login-feature-icon" aria-hidden="true">▤</span><span><strong>Páginas web</strong><small>Crea y organiza contenidos.</small></span></div>
                    <div class="admin-login-feature"><span class="admin-login-feature-icon" aria-hidden="true">▣</span><span><strong>Noticias</strong><small>Publica novedades y comunicados.</small></span></div>
                    <div class="admin-login-feature"><span class="admin-login-feature-icon" aria-hidden="true">▧</span><span><strong>Multimedia</strong><small>Gestiona imágenes y videos.</small></span></div>
                    <div class="admin-login-feature"><span class="admin-login-feature-icon" aria-hidden="true">♙</span><span><strong>Usuarios</strong><small>Control de accesos y permisos.</small></span></div>
                </div>
            </div>
            <div class="admin-login-brand-bottom">
                <div class="admin-login-colorband" aria-hidden="true"><span></span><span></span><span></span><span></span></div>
                <p>Plataforma web administrable desarrollada por <strong>webnia.cl</strong> · <?=cms_e($esloganLogin)?></p>
            </div>
        </section>
        <section class="admin-login-content" aria-labelledby="login-title">
            <div class="admin-login-content-inner">
                <div class="admin-login-platform admin-login-platform-institution" aria-label="Identidad del Colegio Patrona de Lourdes - La Florida">
                    <div class="admin-login-institution-logo-wrap">
                        <?php if($logoInstitucion!==''):?><img src="<?= cms_e($logoInstitucion) ?>" alt="Logo de <?= cms_e($nombreInstitucion) ?>" class="admin-login-institution-logo" onerror="this.hidden=true;this.nextElementSibling.hidden=false"><span class="patrona-crest-fallback small" hidden aria-label="Patrona de Lourdes">PL</span><?php else:?><span class="patrona-crest-fallback small" aria-label="Patrona de Lourdes">PL</span><?php endif;?>
                    </div>
                    <div class="admin-login-platform-copy">
                        <span class="admin-login-platform-eyebrow">Panel de administración web</span>
                        <span class="admin-login-institution-name"><?= cms_e($nombreInstitucion) ?></span>
                        <p>Acceso exclusivo para administrar el sitio institucional.</p>
                    </div>
                </div>
                <div class="admin-login-card">
                    <p class="admin-login-eyebrow">Acceso institucional</p>
                    <h2 id="login-title">Iniciar sesión</h2>
                    <p class="admin-login-subtitle">Ingresa tus credenciales para administrar el sitio web de Colegio Patrona de Lourdes - La Florida.</p>
                    <form id="admin-access-form" action="login_check.php" method="post">
                        <input type="hidden" name="csrf" value="<?= cms_e($_SESSION['patrona_csrf'] ?? '') ?>">
                        <div id="admin-access-alert" class="admin-login-alert" role="alert" hidden></div>
                        <div class="admin-login-field">
                            <label for="admin-usuario">Usuario o correo electrónico</label>
                            <input id="admin-usuario" name="usuario" type="text" autocomplete="username" autocapitalize="none" spellcheck="false" placeholder="Tu usuario o correo" required>
                        </div>
                        <div class="admin-login-field">
                            <label for="admin-clave">Contraseña</label>
                            <div class="admin-login-password">
                                <input id="admin-clave" name="clave" type="password" autocomplete="current-password" placeholder="Ingresa tu contraseña" required>
                                <button id="admin-password-toggle" type="button" aria-label="Mostrar contraseña" aria-pressed="false" aria-controls="admin-clave">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7-3.5 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/><path class="admin-eye-slash" d="m3 3 18 18" hidden/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="admin-login-password-actions"><span title="La recuperación automática por correo está pendiente de configurar">¿Olvidaste tu contraseña? Contacta al administrador.</span></div>
                        <button id="admin-access-submit" class="admin-login-submit" type="submit">Ingresar al panel <span aria-hidden="true">→</span></button>
                    </form>
                    <div class="admin-login-card-footer"><span aria-hidden="true">●</span> Acceso exclusivo para personal autorizado</div>
                    <noscript><p>Activa JavaScript en tu navegador para iniciar sesión desde este formulario.</p></noscript>
                </div>
                <div class="admin-login-developer" aria-label="Créditos de desarrollo">
                    <span class="admin-login-developer-caption">Desarrollado por</span>
                    <a href="https://webnia.cl/" target="_blank" rel="noopener noreferrer" class="admin-login-developer-link">webnia<span>.cl</span></a>
                    <span class="admin-login-developer-description">Diseño, desarrollo y administración de sitios web institucionales</span>
                    <a class="patrona-webnia-cta" href="https://webnia.cl/" target="_blank" rel="noopener noreferrer">Conoce Webnia.cl <span aria-hidden="true">↗</span></a>
                    <a class="patrona-webnia-whatsapp" href="https://wa.me/56988302735?text=Hola%2C%20quiero%20informaci%C3%B3n%20sobre%20un%20sitio%20web%20administrable" target="_blank" rel="noopener noreferrer" aria-label="Consultar a Webnia por WhatsApp">Consultar por WhatsApp <span aria-hidden="true">↗</span></a>
                </div>
                <section class="admin-login-network" aria-label="Administración institucional">
                    <span class="admin-login-network-heading">Una plataforma creada para tu comunidad educativa</span></section>
            </div>
        </section>
    </main>
    <script src="assets/admin/js/admin-login.js" defer></script>
</body>
</html>
