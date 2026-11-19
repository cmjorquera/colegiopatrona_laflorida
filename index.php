<?php
declare(strict_types=1);
$portada=require __DIR__.'/config/portada.php';
require_once __DIR__.'/componentes/portada.php';
require_once __DIR__.'/componentes/patrona_cms.php';
require_once __DIR__.'/includes/patrona_cms_public.php';
require_once __DIR__.'/componentes/modal_institucional.php';
$site=patrona_cms_site();
$sections=$site['sections'];
$sectionsByName=[];
foreach($sections as $section){$sectionsByName[(string)$section['nombre_interno']]=$section;}
$showTopbar=isset($sectionsByName['topbar']);
$showHeader=isset($sectionsByName['header_principal']);
$publicLogo='';
foreach(['assets/imagenes/LOGO-COLEGIO--Sin-fondo.png','assets/imagenes/logo-colegio-1024x598.jpg','imagenes/logo-colegio-1024x598.jpg','imagenes/LOGO-COLEGIO--Sin-fondo.png'] as $logoCandidate){
 if(is_file(__DIR__.'/'.$logoCandidate)){$publicLogo=$logoCandidate;break;}
}
$showHero=isset($sectionsByName['hero_principal']);
$showQuick=isset($sectionsByName['tab_destacados_home']);
$topbarConfig=$sectionsByName['topbar']['config']??[];
$headerConfig=$sectionsByName['header_principal']['config']??[];
$footerConfig=$sectionsByName['footer_principal']['config']??[];
$showFooter=isset($sectionsByName['footer_principal']);
$quickItems=$sectionsByName['tab_destacados_home']['items']??[];
if($quickItems){
 foreach($portada['contenedores'] as &$block){
  if(($block['clave']??'')==='accesos'){
   $block['datos']['items']=array_map(static fn(array $i):array=>[
    'titulo'=>(string)($i['titulo']??''),
    'texto'=>(string)($i['descripcion']??''),
    'url'=>patrona_cms_url($i['url']??''),
    'icono'=>(string)($i['etiqueta']??'↗')
   ],$quickItems);
  }
 }
 unset($block);
}
$headerItems=$sectionsByName['header_principal']['items']??[];
// La navegación pública utiliza la jerarquía real menus / sub_menus de San Pablo.
$publicMenus=[];
if(!empty($site['menus'])){
    $submenusById=[];
    foreach(($site['subs']??[]) as $sub){
        $parentId=(int)($sub['id_menu']??0);
        $submenusById[$parentId][]=[
            'texto'=>(string)($sub['nombre']??''),
            'url'=>patrona_cms_url($sub['url']??'')
        ];
    }
    foreach($site['menus'] as $menu){
        $id=(int)($menu['id_menu']??0);
        $publicMenus[]=[
            'texto'=>(string)($menu['nombre']??''),
            'url'=>patrona_cms_url($menu['url']??''),
            'children'=>$submenusById[$id]??[]
        ];
    }
} elseif($headerItems){
    // Compatibilidad con los enlaces anteriores si aún no hay menús registrados.
    $publicMenus=array_map(static fn(array $i):array=>[
        'texto'=>(string)($i['titulo']??''),
        'url'=>patrona_cms_url($i['url']??''),
        'children'=>[]
    ],$headerItems);
} else {
    $publicMenus=array_map(static fn(array $item):array=>[
        'texto'=>(string)($item['texto']??''),
        'url'=>(string)($item['url']??'#'),
        'children'=>[]
    ],$portada['menu']??[]);
}
$portada['menu']=$publicMenus;
// El bloque superior es la portada original aprobada visualmente.
// El CMS empieza DESPUÉS de los cuatro accesos rápidos.
$top=array_values(array_filter($portada['contenedores']??[],fn(array $c):bool=>(($c['clave']??'')==='hero'&&$showHero)||(($c['clave']??'')==='accesos'&&$showQuick)));
usort($top,static fn(array $a,array $b):int=>($a['orden']??0)<=>($b['orden']??0));
?>
<!doctype html><html lang="es"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="Colegio Patrona de Lourdes, La Florida.">
<title>Colegio Patrona de Lourdes | La Florida</title>
<link rel="stylesheet" href="assets/css/patrona-tokens.css?v=1">
<link rel="stylesheet" href="assets/css/patrona.css?v=6">
<link rel="stylesheet" href="assets/css/patrona-cms-v6.css?v=8">
<link rel="stylesheet" href="assets/css/patrona-equipo.css?v=9">
<link rel="stylesheet" href="assets/css/patrona-nav.css?v=3">\n<link rel="stylesheet" href="assets/css/patrona-footer.css?v=1">
<link rel="stylesheet" href="assets/css/patrona-identity.css?v=2">
<style>
.brand-name{font-size:13px;font-weight:800;color:var(--pl-navy);letter-spacing:.03em}.patrona-cms-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:var(--pl-space-5)}
.patrona-cms-card{border:1px solid var(--pl-line);border-radius:var(--pl-radius-md);padding:20px;overflow:hidden}
.patrona-cms-card img{width:100%;height:210px;object-fit:cover;border-radius:var(--pl-radius-sm)}
</style></head><body>
<?php if($showTopbar): ?><div class="topline"><div class="wrap"><span>COLEGIO PATRONA DE LOURDES · LA FLORIDA</span><span><?=patrona_e((string)($topbarConfig['texto']??'Sitio en desarrollo'))?></span></div></div><?php endif; ?>
<?php if($showHeader): ?><header class="site-header"><div class="wrap nav"><a class="brand" href="#inicio" aria-label="Inicio Colegio Patrona de Lourdes"><span class="patrona-brand-emblem"><?php if($publicLogo!==''):?><img src="<?=patrona_e($publicLogo)?>" alt="Escudo del Colegio Patrona de Lourdes" onerror="this.hidden=true;this.nextElementSibling.hidden=false"><span class="patrona-brand-monogram" hidden>PL</span><?php else:?><span class="patrona-brand-monogram">PL</span><?php endif;?></span><span class="brand-name"><?=patrona_e((string)($headerConfig['titulo_bloque']??'COLEGIO PATRONA DE LOURDES'))?></span><span class="brand-sede">LA FLORIDA</span></a>
<button class="patrona-mobile-toggle" type="button" aria-label="Abrir menú de navegación" aria-controls="patrona-public-navigation" aria-expanded="false"><span></span><span></span><span></span></button><nav class="patrona-public-nav" id="patrona-public-navigation" aria-label="Navegación principal"><ul><?php foreach (($portada['menu']??[]) as $m): $children=$m['children']??[]; ?><li class="<?= $children?'has-submenu':'' ?>"><a href="<?=patrona_e((string)$m['url'])?>"><?=patrona_e((string)$m['texto'])?></a><?php if($children):?><button type="button" class="patrona-nav-expand" aria-label="Desplegar <?=patrona_e((string)$m['texto'])?>" aria-expanded="false">⌄</button><ul class="patrona-nav-submenu"><?php foreach($children as $child):?><li><a href="<?=patrona_e((string)$child['url'])?>"><?=patrona_e((string)$child['texto'])?></a></li><?php endforeach;?></ul><?php endif;?></li><?php endforeach;?></ul></nav>
<a class="nav-cta" href="<?=patrona_e(patrona_cms_url($headerConfig['enlace']??'#contacto'))?>"><?=patrona_e((string)($headerConfig['boton_texto']??'Contáctanos'))?> ↗</a></div></header><?php endif; ?>
<main>
<?php foreach($top as $c) patrona_render($c); ?>
<?php
$excluded=['topbar','header_principal','hero_principal','tab_destacados_home','footer_principal','presentacion_home','vida_escolar_home','modal_institucional'];
foreach($sections as $section) {
 if(in_array((string)($section['nombre_interno']??''),$excluded,true)) continue;
 patrona_cms_render($section);
}
?>
</main>
<?php
// Accesos flotantes: URLs configurables desde seccion_config de header_principal.
$schoolListUrl=patrona_cms_url($headerConfig['url_utiles_escolares']??'');
$contactUrl=patrona_cms_url($headerConfig['url_contacto_flotante']??'#contacto');
?>
<div class="patrona-actions" aria-label="Accesos rápidos">
  <button type="button" data-patrona-chat aria-expanded="false" aria-controls="patrona-actions-chat" title="Información y consultas" aria-label="Información y consultas">☏</button>
  <?php if($schoolListUrl!=='#'):?><a href="<?=patrona_e($schoolListUrl)?>" target="_blank" rel="noopener noreferrer" download="" title="Lista de útiles escolares" aria-label="Descargar lista de útiles escolares"><span aria-hidden="true">📄</span></a><?php else:?><button type="button" disabled title="Lista de útiles aún no publicada" aria-label="Lista de útiles aún no publicada">📄</button><?php endif;?>
  <a href="<?=patrona_e($contactUrl)?>" title="Contacto" aria-label="Contacto">✉</a>
  <?php $enrollmentUrl=patrona_cms_url($headerConfig['url_matriculas']??'#contacto'); ?>
  <a class="patrona-enrollment-float" href="<?=patrona_e($enrollmentUrl)?>" title="¡Matricúlate aquí!" aria-label="¡Matricúlate aquí!"><span aria-hidden="true">🎓</span><span class="patrona-enrollment-label">¡Matricúlate aquí!</span></a>
</div>
<div class="patrona-actions-panel" id="patrona-actions-chat" data-patrona-panel hidden>
  <button type="button" class="patrona-actions-panel-close" data-patrona-panel-close aria-label="Cerrar">×</button>
  <h3>¿Necesitas ayuda?</h3>
  <p>Este espacio está preparado para conectar el chat institucional. Por ahora puedes utilizar el acceso a Contacto.</p>
  <a href="#contacto">Ir a contacto →</a>
</div>
<?php patrona_render_modal_institucional($sections); ?>
<?php if($showFooter): ?>
<footer id="contacto" class="patrona-footer">
  <div class="wrap patrona-footer-grid">
    <div class="patrona-footer-intro">
      <strong>COLEGIO PATRONA DE LOURDES <small>LA FLORIDA</small></strong>
      <?php if(trim((string)($footerConfig['texto']??''))!==''):?><p><?=patrona_e((string)$footerConfig['texto'])?></p><?php endif;?>
    </div>
    <div class="patrona-footer-column">
      <h2>Contacto</h2>
      <?php foreach(['direccion'=>'Dirección','telefono'=>'Teléfono','correo'=>'Correo','horario'=>'Horario'] as $key=>$label):$value=trim((string)($footerConfig[$key]??''));if($value==='')continue; ?>
      <p><strong><?=patrona_e($label)?>:</strong> <?php if($key==='correo'&&filter_var($value,FILTER_VALIDATE_EMAIL)):?><a href="mailto:<?=patrona_e($value)?>"><?=patrona_e($value)?></a><?php elseif($key==='telefono'):?><a href="tel:<?=patrona_e(preg_replace('/[^+0-9]/','',$value))?>"><?=patrona_e($value)?></a><?php else:?><?=patrona_e($value)?><?php endif;?></p>
      <?php endforeach;?>
      <?php $mapUrl=patrona_cms_url($footerConfig['mapa_url']??'');if($mapUrl!=='#'):?><a class="patrona-footer-link" href="<?=patrona_e($mapUrl)?>" target="_blank" rel="noopener noreferrer">Ver ubicación en el mapa ↗</a><?php endif;?>
    </div>
    <div class="patrona-footer-column">
      <h2>Enlaces institucionales</h2>
      <a href="#inicio">Inicio</a>
      <?php foreach(($site['menus']??[]) as $menu):$url=patrona_cms_url($menu['url']??'');if($url==='#')continue;?><a href="<?=patrona_e($url)?>"><?=patrona_e((string)($menu['nombre']??''))?></a><?php endforeach;?>
    </div>
    <div class="patrona-footer-column">
      <h2>Redes sociales</h2>
      <?php foreach(['instagram_url'=>'Instagram','facebook_url'=>'Facebook','youtube_url'=>'YouTube'] as $key=>$label):$url=patrona_cms_url($footerConfig[$key]??'');if($url==='#')continue;?><a href="<?=patrona_e($url)?>" target="_blank" rel="noopener noreferrer"><?=patrona_e($label)?> ↗</a><?php endforeach;?>
    </div>
  </div>
  <div class="wrap patrona-footer-bottom"><span>© <?=date('Y')?> Colegio Patrona de Lourdes · La Florida</span></div>
</footer>
<?php endif; ?>
<script defer src="assets/js/patrona-cms-v6.js?v=3"></script>
<script defer src="assets/js/patrona-equipo.js?v=8"></script>
<script defer src="assets/js/patrona-nav.js?v=2"></script>
</body></html>
