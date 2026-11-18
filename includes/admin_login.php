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
    <link rel="stylesheet" href="assets/admin/css/patrona-login-premium.css?v=3">
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
                    <div class="patrona-login-social" aria-label="Canales de Webnia">
                      <a class="patrona-social-webnia" href="https://webnia.cl/" target="_blank" rel="noopener noreferrer" title="Sitio web de Webnia" aria-label="Visitar Webnia.cl">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9.3"/><path d="M2.7 12h18.6M12 2.7a15 15 0 0 1 0 18.6M12 2.7a15 15 0 0 0 0 18.6"/></svg>
                      </a>
                      <a class="patrona-social-whatsapp" href="https://wa.me/56988302735?text=Hola%2C%20quiero%20informaci%C3%B3n%20sobre%20un%20sitio%20web%20administrable" target="_blank" rel="noopener noreferrer" aria-label="Contactar a Webnia por WhatsApp" title="WhatsApp de Webnia">
                        <svg viewBox="0 0 32 32" aria-hidden="true" fill="currentColor"><path d="M16 .8A15.1 15.1 0 0 0 3 23.6L.9 31l7.6-2a15.2 15.2 0 1 0 7.5-28.2Zm0 27.6a12.3 12.3 0 0 1-6.3-1.7l-.5-.3-4.5 1.2 1.2-4.4-.3-.5a12.4 12.4 0 1 1 10.4 5.7Zm6.8-9.2c-.4-.2-2.2-1.1-2.5-1.2-.3-.1-.5-.2-.7.2-.2.4-1 1.2-1.4-.1-.4-.2-1.6-.6-3-1.9-1.1-1-1.9-2.2-2.1-2.6-.2-.4 0-.6.2-.8l.6-.7c.2-.2.3-.4.4-.6.1-.2.1-.5 0-.7l-1.1-2.6c-.3-.7-.6-.6-.8-.6h-.7c-.3 0-.7.1-1 .5-.3.4-1.3 1.3-1.3 3.1s1.3 3.5 1.5 3.8c.2.2 2.6 4 6.3 5.6.9.4 1.6.6 2.2.7.9.3 1.7.2 2.4.2.7-.1 2.2-.9 2.5-1.8.3-.9.3-1.7.2-1.8-.1-.2-.3-.3-.7-.5Z"/></svg>
                      </a>
                      <?php
                        // Enlace configurable: sin URL verificada no se atribuye una cuenta ajena a Webnia.
                        $instagramWebnia = 'https://www.instagram.com/webnia_soluciones/';
                        $instagramWebnia = preg_match('~^https://(?:www\\.)?instagram\\.com/[A-Za-z0-9_.]+/?$~', $instagramWebnia) ? $instagramWebnia : '';
                      ?>
                      <?php if($instagramWebnia!==''):?><a class="patrona-social-instagram" href="<?=cms_e($instagramWebnia)?>" target="_blank" rel="noopener noreferrer" title="Instagram de Webnia" aria-label="Instagram de Webnia"><?php else:?><span class="patrona-social-instagram patrona-social-pending" title="Instagram de Webnia: enlace pendiente de configurar" aria-label="Instagram de Webnia pendiente"><?php endif;?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2.5" y="2.5" width="19" height="19" rx="5"/><circle cx="12" cy="12" r="4.3"/><circle cx="17.8" cy="6.3" r="1" fill="currentColor" stroke="none"/></svg>
                      <?php if($instagramWebnia!==''):?></a><?php else:?></span><?php endif;?>
                    </div>
                </div>
                <section class="admin-login-network" aria-label="Administración institucional">
                    <span class="admin-login-network-heading">Una plataforma creada para tu comunidad educativa</span></section>
            </div>
        </section>
    </main>
    <script src="assets/admin/js/admin-login.js" defer></script>
</body>
</html>
