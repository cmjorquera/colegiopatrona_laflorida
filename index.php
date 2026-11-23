<?php
declare(strict_types=1);
$isReligiousPage=defined('PATRONA_INTERIOR_PAGE') && PATRONA_INTERIOR_PAGE==='formacion-religiosa';
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
foreach(['assets/imagenes/cropped-logo-cuadrado-sin-fondo-2-180x180.png','assets/imagenes/LOGO-COLEGIO--Sin-fondo.png','assets/imagenes/logo-colegio-1024x598.jpg','imagenes/logo-colegio-1024x598.jpg','imagenes/LOGO-COLEGIO--Sin-fondo.png'] as $logoCandidate){
 if(is_file(__DIR__.'/'.$logoCandidate)){$publicLogo=$logoCandidate;break;}
}
$showHero=isset($sectionsByName['hero_principal']);
$showQuick=isset($sectionsByName['tab_destacados_home']);
$topbarConfig=$sectionsByName['topbar']['config']??[];
$headerConfig=$sectionsByName['header_principal']['config']??[];
$footerConfig=$sectionsByName['footer_principal']['config']??[];
$showFooter=isset($sectionsByName['footer_principal']);
$quickItems=array_slice($sectionsByName['tab_destacados_home']['items']??[],0,4);
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
// Las diapositivas administradas reemplazan el contenido de ejemplo cuando existen ítems visibles.
$heroItems=$sectionsByName['hero_principal']['items']??[];
if($heroItems){
 $heroSlides=[];
 foreach($heroItems as $item){
  $lines=array_filter([(string)($item['titulo_linea_1']??''),(string)($item['titulo_linea_2']??''),(string)($item['titulo_linea_3']??'')],static fn($v)=>trim($v)!=='');
  $heroSlides[]=[
   'etiqueta'=>(string)($item['etiqueta']??''),
   'titulo'=>$lines?implode("\n",$lines):(string)($item['titulo']??''),
   'texto'=>(string)(($item['descripcion']??'')?:($item['subtitulo']??'')),
   'imagen'=>(string)($item['imagen']??''),
   'imagen_mobile'=>(string)($item['imagen_mobile']??''),
   'video'=>(string)($item['url']??''),
   'youtube'=>(string)($item['video_youtube']??''),
   'boton'=>(string)($item['boton_1_texto']??''),
   'enlace'=>patrona_cms_url($item['boton_1_url']??''),
   'boton_2'=>(string)($item['boton_2_texto']??''),
   'enlace_2'=>patrona_cms_url($item['boton_2_url']??'')
  ];
 }
 foreach($portada['contenedores'] as &$block){
  if(($block['clave']??'')==='hero'){$block['datos']['slides']=$heroSlides;$block['datos']['config']=$sectionsByName['hero_principal']['config']??[];}
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
// Enlace a página interior pública, sin modificar menús en MySQL.
foreach($publicMenus as &$publicMenu){
 if(preg_match('/formaci[oó]n religiosa/iu',(string)($publicMenu['texto']??'')))$publicMenu['url']='formacion_religiosa.php';
}
unset($publicMenu);
$portada['menu']=$publicMenus;
// El bloque superior es la portada original aprobada visualmente.
// El CMS empieza DESPUÉS de los cuatro accesos rápidos.
$top=array_values(array_filter($portada['contenedores']??[],fn(array $c):bool=>(($c['clave']??'')==='hero'&&$showHero)||(($c['clave']??'')==='accesos'&&$showQuick)));
usort($top,static fn(array $a,array $b):int=>($a['orden']??0)<=>($b['orden']??0));
?>
<!doctype html><html lang="es"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="<?= $isReligiousPage ? 'Formación Religiosa del Colegio Patrona de Lourdes, La Florida: Club Buscadores del Grial y propuestas por nivel escolar.' : 'Colegio Patrona de Lourdes, La Florida.' ?>">
<title><?= $isReligiousPage ? 'Formación Religiosa | Colegio Patrona de Lourdes' : 'Colegio Patrona de Lourdes | La Florida' ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="assets/css/patrona-tokens.css?v=1">
<link rel="stylesheet" href="assets/css/patrona.css?v=6">
<link rel="stylesheet" href="assets/css/patrona-cms-v6.css?v=8">
<link rel="stylesheet" href="assets/css/patrona-equipo.css?v=9">
<link rel="stylesheet" href="assets/css/patrona-nav.css?v=3">
<link rel="stylesheet" href="assets/css/patrona-footer.css?v=1">
<link rel="stylesheet" href="assets/css/patrona-mobile.css?v=1">
<link rel="stylesheet" href="assets/css/patrona-actions-icons.css?v=1">
<link rel="stylesheet" href="assets/css/patrona-identity.css?v=2">
<link rel="stylesheet" href="assets/css/patrona-topbar.css?v=1">
<?php if($isReligiousPage):?><link rel="stylesheet" href="assets/css/patrona-formacion.css?v=1"><?php endif;?>
<style>
.brand-name{font-size:13px;font-weight:800;color:var(--pl-navy);letter-spacing:.03em}.patrona-cms-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:var(--pl-space-5)}
.patrona-cms-card{border:1px solid var(--pl-line);border-radius:var(--pl-radius-md);padding:20px;overflow:hidden}
.patrona-cms-card img{width:100%;height:210px;object-fit:cover;border-radius:var(--pl-radius-sm)}
</style></head><body>
<?php if($showTopbar): ?>
<?php
$topbarItems=$sectionsByName['topbar']['items']??[];
$topbarIcons=['direccion'=>'bi-geo-alt-fill','telefono'=>'bi-telephone-fill','correo'=>'bi-envelope-fill','enlace'=>'bi-box-arrow-up-right','instagram'=>'bi-instagram','facebook'=>'bi-facebook','youtube'=>'bi-youtube','linkedin'=>'bi-linkedin','x'=>'bi-twitter-x'];
$topbarContacts=[];$topbarSocial=[];
foreach($topbarItems as $topbarItem){
 $kind=(string)($topbarItem['etiqueta']??'');$value=trim((string)($topbarItem['url']??''));$label=trim((string)($topbarItem['titulo']??''));
 if($label===''||!isset($topbarIcons[$kind]))continue;
 $href='';
 if($kind==='telefono' && preg_match('/^[+0-9 ()-]{6,30}$/',$value))$href='tel:'.preg_replace('/[^+0-9]/','',$value);
 elseif($kind==='correo' && filter_var($value,FILTER_VALIDATE_EMAIL))$href='mailto:'.$value;
 elseif($kind==='direccion')$href='';
 elseif(filter_var($value,FILTER_VALIDATE_URL)&&in_array(strtolower((string)parse_url($value,PHP_URL_SCHEME)),['http','https'],true))$href=$value;
 if($href===''&&$kind!=='direccion')continue;
 $entry=['label'=>$label,'href'=>$href,'icon'=>$topbarIcons[$kind],'kind'=>$kind];
 if(in_array($kind,['instagram','facebook','youtube','linkedin','x'],true))$topbarSocial[]=$entry;
 else $topbarContacts[]=$entry;
}
?>
<div class="topline patrona-topbar"><div class="wrap patrona-topbar-inner">
 <span class="patrona-topbar-brand"><?=patrona_e((string)(($topbarConfig['titulo_bloque']??'')?:'COLEGIO PATRONA DE LOURDES · LA FLORIDA'))?></span>
 <?php if($topbarContacts||$topbarSocial):?>
 <div class="patrona-topbar-items">
 <?php foreach($topbarContacts as $entry):?>
 <?php if($entry['href']!==''):?><a href="<?=patrona_e($entry['href'])?>" <?=str_starts_with($entry['href'],'http')?'target="_blank" rel="noopener noreferrer"':''?>><i class="bi <?=patrona_e($entry['icon'])?>" aria-hidden="true"></i><span><?=patrona_e($entry['label'])?></span></a><?php else:?><span class="patrona-topbar-address"><i class="bi <?=patrona_e($entry['icon'])?>" aria-hidden="true"></i><?=patrona_e($entry['label'])?></span><?php endif;?>
 <?php endforeach;?>
 </div><div class="patrona-topbar-social" aria-label="Redes sociales"><?php foreach($topbarSocial as $entry):?><a href="<?=patrona_e($entry['href'])?>" target="_blank" rel="noopener noreferrer" aria-label="<?=patrona_e($entry['label'])?>" title="<?=patrona_e($entry['label'])?>"><i class="bi <?=patrona_e($entry['icon'])?>" aria-hidden="true"></i></a><?php endforeach;?></div>
 <?php else:?><span class="patrona-topbar-message"><?=patrona_e((string)($topbarConfig['texto']??'Sitio en desarrollo'))?></span><?php endif;?>
</div></div><?php endif; ?>
<style>
/* Menú público: logo y navegación equilibrados sin botón de contacto. */
/* Escudo protagonista: sobresale hacia la franja institucional y se compacta al desplazarse. */
.site-header{z-index:30;overflow:visible;transition:box-shadow .3s ease}
.site-header .wrap.nav{position:relative;transition:min-height .35s ease}
.site-header .brand{position:relative;z-index:2;min-width:0}
.site-header .patrona-brand-emblem{width:112px;height:112px;min-width:112px;display:grid;place-items:center;align-self:center;margin-top:-34px;margin-bottom:-12px;border:0;background:transparent;box-shadow:none;overflow:visible;transition:width .35s ease,height .35s ease,min-width .35s ease,margin .35s ease,transform .35s ease}
.site-header .patrona-brand-emblem img{display:block;width:100%;height:100%;max-width:none;max-height:none;object-fit:contain;filter:drop-shadow(0 5px 8px rgba(12,45,78,.16));transition:filter .3s ease}
.site-header .brand:hover .patrona-brand-emblem{transform:translateY(-3px)}
.site-header.is-scrolled{box-shadow:0 8px 24px rgba(8,37,70,.15)}
.site-header.is-scrolled .wrap.nav{min-height:70px}
.site-header.is-scrolled .patrona-brand-emblem{width:67px;height:67px;min-width:67px;margin-top:0;margin-bottom:0}
@media(min-width:1024px){.site-header .wrap.nav{min-height:94px}.site-header .brand{gap:14px}}
@media(max-width:1023px){.site-header .patrona-brand-emblem{width:76px;height:76px;min-width:76px;margin-top:-14px;margin-bottom:0}.site-header.is-scrolled .patrona-brand-emblem{width:55px;height:55px;min-width:55px}.site-header .wrap.nav{min-height:76px}.site-header.is-scrolled .wrap.nav{min-height:65px}}
@media(max-width:560px){.site-header .patrona-brand-emblem{width:64px;height:64px;min-width:64px;margin-top:-8px}.site-header .brand-name{font-size:11px}.site-header .brand-sede{font-size:10px}}
@media(prefers-reduced-motion:reduce){.site-header,.site-header .wrap.nav,.site-header .patrona-brand-emblem,.site-header .patrona-brand-emblem img{transition:none}}

@media(min-width:1024px){
.site-header .wrap.nav{max-width:1540px;width:calc(100% - 64px);margin-inline:auto;gap:20px}
.site-header .brand{flex-shrink:0}
.site-header .patrona-public-nav{margin-left:auto}
.site-header .patrona-brand-emblem{flex-shrink:0}
}
@media(min-width:1600px){.site-header .wrap.nav{width:calc(100% - 120px)}}
</style>
<?php if($showHeader): ?><header class="site-header"><div class="wrap nav"><a class="brand" href="index.php#inicio" aria-label="Inicio Colegio Patrona de Lourdes"><span class="patrona-brand-emblem"><?php if($publicLogo!==''):?><img src="<?=patrona_e($publicLogo)?>" alt="Escudo del Colegio Patrona de Lourdes" onerror="this.hidden=true;this.nextElementSibling.hidden=false"><span class="patrona-brand-monogram" hidden>PL</span><?php else:?><span class="patrona-brand-monogram">PL</span><?php endif;?></span><span class="brand-name"><?=patrona_e((string)($headerConfig['titulo_bloque']??'COLEGIO PATRONA DE LOURDES'))?></span><span class="brand-sede">LA FLORIDA</span></a>
<button class="patrona-mobile-toggle" type="button" aria-label="Abrir menú de navegación" aria-controls="patrona-public-navigation" aria-expanded="false"><span></span><span></span><span></span></button><nav class="patrona-public-nav" id="patrona-public-navigation" aria-label="Navegación principal"><ul><?php foreach (($portada['menu']??[]) as $m): $children=$m['children']??[]; ?><li class="<?= $children?'has-submenu':'' ?>"><a href="<?=patrona_e((string)$m['url'])?>"><?=patrona_e((string)$m['texto'])?></a><?php if($children):?><button type="button" class="patrona-nav-expand" aria-label="Desplegar <?=patrona_e((string)$m['texto'])?>" aria-expanded="false">⌄</button><ul class="patrona-nav-submenu"><?php foreach($children as $child):?><li><a href="<?=patrona_e((string)$child['url'])?>"><?=patrona_e((string)$child['texto'])?></a></li><?php endforeach;?></ul><?php endif;?></li><?php endforeach;?></ul></nav>
</div></header><?php endif; ?>
<main id="inicio">
<?php if($isReligiousPage):?>
<?php require __DIR__.'/componentes/formacion_religiosa.php'; ?>
<?php else:?>
<?php foreach($top as $c) patrona_render($c); ?>
<?php
$excluded=['topbar','header_principal','hero_principal','tab_destacados_home','footer_principal','presentacion_home','vida_escolar_home','modal_institucional'];
foreach($sections as $section) {
 if(in_array((string)($section['nombre_interno']??''),$excluded,true)) continue;
 patrona_cms_render($section);
}
?>
<?php endif;?>
</main>
<?php
// Accesos flotantes: URLs configurables desde seccion_config de header_principal.
$schoolListUrl=patrona_cms_url($headerConfig['url_utiles_escolares']??'');
$contactUrl=patrona_cms_url($headerConfig['url_contacto_flotante']??'#contacto');
?>
<div class="patrona-actions" aria-label="Accesos rápidos">
  <button type="button" data-patrona-chat aria-expanded="false" aria-controls="patrona-actions-chat" title="Información y consultas" aria-label="Información y consultas"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2A19.8 19.8 0 0 1 3 5.2 2 2 0 0 1 5 3h3a2 2 0 0 1 2 1.7l.5 3a2 2 0 0 1-.6 1.7L8.1 11a16 16 0 0 0 4.9 4.9l1.6-1.8a2 2 0 0 1 1.7-.6l3 .5A2 2 0 0 1 22 16.9z"/></svg></button>
  <?php if($schoolListUrl!=='#'):?><a href="<?=patrona_e($schoolListUrl)?>" target="_blank" rel="noopener noreferrer" download="" title="Lista de útiles escolares" aria-label="Descargar lista de útiles escolares"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h8"/></svg></a><?php else:?><button type="button" disabled title="Lista de útiles aún no publicada" aria-label="Lista de útiles aún no publicada"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h8"/></svg></button><?php endif;?>
  <a href="<?=patrona_e($contactUrl)?>" title="Contacto" aria-label="Contacto"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 6 10 7 10-7"/></svg></a>
  <?php $enrollmentUrl=patrona_cms_url($headerConfig['url_matriculas']??'#contacto'); ?>
  <a class="patrona-enrollment-float" href="<?=patrona_e($enrollmentUrl)?>" title="¡Matricúlate aquí!" aria-label="¡Matricúlate aquí!"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="m2 10 10-5 10 5-10 5-10-5ZM6 12v5c4 3 8 3 12 0v-5M22 10v7"/></svg><span class="patrona-enrollment-label">¡Matricúlate aquí!</span></a>
</div>
<div class="patrona-actions-panel" id="patrona-actions-chat" data-patrona-panel hidden>
  <button type="button" class="patrona-actions-panel-close" data-patrona-panel-close aria-label="Cerrar">×</button>
  <h3>¿Necesitas ayuda?</h3>
  <p>Este espacio está preparado para conectar el chat institucional. Por ahora puedes utilizar el acceso a Contacto.</p>
  <a href="#contacto">Ir a contacto →</a>
</div>
<?php if(!$isReligiousPage) patrona_render_modal_institucional($sections); ?>
<?php if($showFooter): ?>
<style>
.patrona-footer{position:relative;isolation:isolate;overflow:hidden;border-top:4px solid #efa52b}
.patrona-footer::before{content:"";position:absolute;inset:0;pointer-events:none;z-index:0;background:radial-gradient(ellipse at 88% 50%,rgba(255,255,255,.055),transparent 43%)}
.patrona-footer .patrona-footer-watermark{position:absolute;right:4%;top:50%;transform:translateY(-50%);width:min(32vw,400px);height:min(32vw,400px);object-fit:contain;opacity:.055;filter:grayscale(1) brightness(0) invert(1);pointer-events:none;z-index:0}
.patrona-footer .patrona-footer-grid,.patrona-footer .patrona-footer-bottom{position:relative;z-index:1}
.patrona-footer .patrona-footer-logo{display:block;width:auto;max-width:130px;max-height:115px;object-fit:contain;object-position:left center;margin:0 0 18px}
.patrona-footer .patrona-social-links{display:flex;gap:12px;flex-wrap:wrap;margin-top:20px}
.patrona-footer .patrona-social-link{display:inline-flex;align-items:center;justify-content:center;width:46px;height:46px;min-width:46px;border:1px solid rgba(255,255,255,.28);border-radius:50%;color:#fff;text-decoration:none;background:rgba(255,255,255,.075);transition:transform .2s ease,background .2s ease,border-color .2s ease}
.patrona-footer .patrona-social-link:hover,.patrona-footer .patrona-social-link:focus-visible{transform:translateY(-3px);background:#e9a23a;border-color:#e9a23a;color:#102f53}
.patrona-footer .patrona-social-link svg{width:21px;height:21px;fill:none;stroke:currentColor;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round}
@media(max-width:640px){.patrona-footer .patrona-footer-logo{max-width:110px;max-height:100px}.patrona-footer .patrona-footer-watermark{width:230px;height:230px;right:-50px;opacity:.035}}
@media(prefers-reduced-motion:reduce){.patrona-footer .patrona-social-link{transition:none}}
</style>
<footer id="contacto" class="patrona-footer">
  <?php if($publicLogo!==''):?><img src="<?=patrona_e($publicLogo)?>" alt="" aria-hidden="true" class="patrona-footer-watermark" loading="lazy"><?php endif;?>
  <div class="wrap patrona-footer-grid">
    <div class="patrona-footer-intro">
      <?php if($publicLogo!==''):?><img src="<?=patrona_e($publicLogo)?>" alt="Escudo del Colegio Patrona de Lourdes" class="patrona-footer-logo" loading="lazy" decoding="async"><?php endif;?>
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
      <div class="patrona-social-links" aria-label="Redes sociales del colegio">
      <?php foreach(['instagram_url'=>'Instagram','facebook_url'=>'Facebook','youtube_url'=>'YouTube'] as $key=>$label):$url=patrona_cms_url($footerConfig[$key]??'');if($url==='#')continue;?>
      <a class="patrona-social-link" href="<?=patrona_e($url)?>" target="_blank" rel="noopener noreferrer" aria-label="<?=patrona_e($label)?>" title="<?=patrona_e($label)?>">
      <?php if($key==='instagram_url'):?><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="2.5" y="2.5" width="19" height="19" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="18" cy="6" r=".7" fill="currentColor" stroke="none"/></svg>
      <?php elseif($key==='facebook_url'):?><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15.5 3.5h-2.2c-2.4 0-3.6 1.5-3.6 3.8v2H7.4v3.5h2.3v7.7h3.6v-7.7h2.8l.5-3.5h-3.3V7.7c0-.7.3-1 1-1h1.2z"/></svg>
      <?php else:?><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="2" y="5" width="20" height="14" rx="4"/><path d="m10 9 5 3-5 3z" fill="currentColor" stroke="none"/></svg><?php endif;?>
      </a><?php endforeach;?>
      </div>
    </div>
  </div>
  <div class="wrap patrona-footer-bottom"><span>© <?=date('Y')?> Colegio Patrona de Lourdes · La Florida</span></div>
</footer>
<?php endif; ?>
<script>
(function(){
  const header=document.querySelector('.site-header');
  if(!header)return;
  let ticking=false;
  const update=()=>{header.classList.toggle('is-scrolled',window.scrollY>65);ticking=false;};
  window.addEventListener('scroll',()=>{if(!ticking){ticking=true;window.requestAnimationFrame(update)}},{passive:true});
  update();
})();
</script>
<script defer src="assets/js/patrona-cms-v6.js?v=3"></script>
<script defer src="assets/js/patrona-equipo.js?v=8"></script>
<script defer src="assets/js/patrona-nav.js?v=2"></script>
<!-- Chat de consultas (Webnia Bot, empresa "patrona-laflorida").
     Por ahora apunta al Webnia Bot que corre en el PC de Cristian (Docker + localhost:5173):
     en otro equipo la burbuja no carga y no aparece nada. Cuando Webnia Bot esté en su
     servidor, solo se cambia la dirección por https://chatbot.webnia.cl/widget.js. -->
<script src="http://localhost:5173/widget.js"
        data-tenant="patrona-laflorida" data-lang="es" data-color="#1266ac"
        data-position="bottom-left" async></script>
</body></html>
