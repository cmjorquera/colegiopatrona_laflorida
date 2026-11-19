<?php
declare(strict_types=1);
$sessionPath='/home3/crist668/php_sessions_patrona';
if (!is_dir($sessionPath) || !is_writable($sessionPath)) { http_response_code(503); exit('Sesiones no disponibles.'); }
session_save_path($sessionPath);
session_start();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
if (empty($_SESSION['patrona_admin_id'])) { header('Location: admin.php'); exit; }
function he(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function conexionHero(): mysqli {
 $cfg=require __DIR__.'/class/conexion_local.php';
 if (!is_array($cfg) || ($cfg['db']??'')!=='crist668_colegio_patrona_laflorida') throw new RuntimeException('Configuración de base de datos incorrecta.');
 mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
 $db=new mysqli($cfg['host'],$cfg['user'],$cfg['pass'],$cfg['db']); $db->set_charset('utf8mb4'); return $db;
}
// Sellos usa un editor especializado y mantiene el mismo esquema CMS de Tabancura.
if ((int)($_GET['id']??0)>0) {
 try {
  $sealDb=conexionHero();$requested=(int)$_GET['id'];
  $sealQuery=$sealDb->prepare('SELECT nombre_interno FROM seccion WHERE id_institucion=2 AND id_seccion=? LIMIT 1');
  $sealQuery->bind_param('i',$requested);$sealQuery->execute();
  $sealRow=$sealQuery->get_result()->fetch_assoc();
  $sealDb->close();
  if (($sealRow['nombre_interno']??'')==='sellos_home') {header('Location: editar_sellos.php');exit;}
 }catch(Throwable $e){error_log('Patrona redireccion sellos: '.$e->getMessage());}
}
if (empty($_SESSION['patrona_csrf'])) $_SESSION['patrona_csrf']=bin2hex(random_bytes(32));
$error=''; $notice=(string)($_SESSION['hero_notice']??'');unset($_SESSION['hero_notice']);
$section=[];$items=[];$configs=[];
try {
 $db=conexionHero();
 $requestedId=(int)($_GET['id']??$_POST['id_seccion']??0);
 if ($requestedId<=0) throw new RuntimeException('Selecciona un contenedor desde el administrador.');
 $q=$db->prepare('SELECT * FROM seccion WHERE id_institucion=2 AND id_seccion=? LIMIT 1');
 $q->bind_param('i',$requestedId);$q->execute();$section=$q->get_result()->fetch_assoc()?:[];
 if (!$section) throw new RuntimeException('El contenedor solicitado no existe en La Florida.');
 $sid=(int)$section['id_seccion'];
 if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (!hash_equals((string)$_SESSION['patrona_csrf'],(string)($_POST['csrf']??''))) throw new RuntimeException('Token de seguridad inválido.');
  if (!in_array((string)($section['editable']??''),['si','1'],true)) throw new RuntimeException('El contenedor no es editable.');
  $action=(string)($_POST['accion']??'');
  if ($action==='reorder') {
   $ids=array_map('intval',explode(',',(string)($_POST['ordered_ids']??'')));
   $q=$db->prepare('SELECT id_item FROM seccion_item WHERE id_seccion=?');$q->bind_param('i',$sid);$q->execute();
   $existing=array_map('intval',array_column($q->get_result()->fetch_all(MYSQLI_ASSOC),'id_item'));
   $check=$ids;sort($check);sort($existing);
   if($check!==$existing||count($ids)!==count(array_unique($ids)))throw new RuntimeException('El listado cambió. Recarga para ordenar.');
   $db->begin_transaction();
   try{$q=$db->prepare('UPDATE seccion_item SET orden=? WHERE id_item=? AND id_seccion=?');foreach($ids as $i=>$itemId){$pos=$i+1;$q->bind_param('iii',$pos,$itemId,$sid);$q->execute();}$db->commit();}catch(Throwable $e){$db->rollback();throw $e;}
  } elseif($action==='delete'||$action==='toggle') {
   $id=(int)($_POST['id_item']??0);if($id<1)throw new RuntimeException('Diapositiva inválida.');
   $q=$db->prepare('SELECT visible FROM seccion_item WHERE id_item=? AND id_seccion=?');$q->bind_param('ii',$id,$sid);$q->execute();$old=$q->get_result()->fetch_assoc();if(!$old)throw new RuntimeException('Diapositiva inexistente.');
   if($action==='delete'){$q=$db->prepare('DELETE FROM seccion_item WHERE id_item=? AND id_seccion=?');$q->bind_param('ii',$id,$sid);}
   else{$visible=$old['visible']==='si'?'no':'si';$q=$db->prepare('UPDATE seccion_item SET visible=? WHERE id_item=? AND id_seccion=?');$q->bind_param('sii',$visible,$id,$sid);}
   $q->execute();
  } elseif ($action==='guardar_item') {
   $id=max(0,(int)($_POST['id_item']??0));$old=null;
   if($id){$q=$db->prepare('SELECT * FROM seccion_item WHERE id_item=? AND id_seccion=?');$q->bind_param('ii',$id,$sid);$q->execute();$old=$q->get_result()->fetch_assoc();if(!$old)throw new RuntimeException('La diapositiva no pertenece al contenedor.');}
   $data=[];
   foreach(['etiqueta','titulo','titulo_linea_1','titulo_linea_2','titulo_linea_3','subtitulo','descripcion','imagen','imagen_mobile','video_youtube','url','boton_1_texto','boton_1_url','boton_2_texto','boton_2_url'] as $key){
    $data[$key]=trim((string)($_POST[$key]??($old[$key]??'')));
    if(mb_strlen($data[$key])>1900)throw new RuntimeException('Campo demasiado largo: '.$key);
   }
   $kind=(string)($_POST['media_type']??'imagen');
   if(!in_array($kind,['imagen','youtube','mp4'],true))throw new RuntimeException('Tipo multimedia inválido.');
   $mediaUpload=['imagen'=>'imagen','imagen_mobile'=>'imagen_mobile','video_mp4'=>'url'];
   foreach($mediaUpload as $input=>$column){
    if(empty($_FILES[$input]['name']))continue;
    $file=$_FILES[$input];if(($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK)throw new RuntimeException('Error al cargar '.$input);
    $isVideo=$input==='video_mp4';$max=$isVideo?52428800:8388608;
    if(($file['size']??0)>$max||($file['size']??0)<1)throw new RuntimeException('Archivo demasiado grande o vacío.');
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $types=$isVideo?['video/mp4'=>'mp4']:['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    if(!isset($types[$mime]))throw new RuntimeException('Formato de archivo no permitido: '.$input);
    $folder=$isVideo?'assets/videos/':'assets/uploads/hero/';
    if(!is_dir(__DIR__.'/'.$folder)&&!mkdir(__DIR__.'/'.$folder,0755,true)&&!is_dir(__DIR__.'/'.$folder))throw new RuntimeException('No se pudo crear la carpeta de carga.');
    $dest=$folder.bin2hex(random_bytes(12)).'.'.$types[$mime];
    if(!move_uploaded_file($file['tmp_name'],__DIR__.'/'.$dest))throw new RuntimeException('No se pudo guardar el archivo.');
    $data[$column]=$dest;
   }
   foreach(['imagen','imagen_mobile'] as $key)if($data[$key]!==''&&(!preg_match('~^assets/[a-zA-Z0-9_./%-]+$~',$data[$key])||str_contains($data[$key],'..')))throw new RuntimeException('Ruta de imagen inválida.');
   foreach(['boton_1_url','boton_2_url','url','video_youtube'] as $key){$v=$data[$key];if($v!==''&&!preg_match('~^(https?://[^\s]+|#[a-zA-Z0-9_-]+|/[a-zA-Z0-9_./?&=%#-]+|assets/videos/[a-zA-Z0-9_./%-]+)$~',$v))throw new RuntimeException('URL inválida: '.$key);}
   if($kind==='youtube'){if(!preg_match('~^https?://(www\.)?(youtube\.com|youtu\.be)/~i',$data['video_youtube']))throw new RuntimeException('Ingresa un enlace válido de YouTube.');$data['imagen']='';$data['url']='';}
   elseif($kind==='mp4'){if($data['url']==='')throw new RuntimeException('Carga un video MP4.');$data['video_youtube']='';}
   else{$data['video_youtube']='';$data['url']='';}
   $visible=isset($_POST['visible'])?'si':'no';
   if($id){$orden=(int)$old['orden'];$stmt=$db->prepare('UPDATE seccion_item SET etiqueta=?,titulo=?,titulo_linea_1=?,titulo_linea_2=?,titulo_linea_3=?,subtitulo=?,descripcion=?,imagen=?,imagen_mobile=?,video_youtube=?,url=?,boton_1_texto=?,boton_1_url=?,boton_2_texto=?,boton_2_url=?,visible=? WHERE id_item=? AND id_seccion=?');$stmt->bind_param('ssssssssssssssssii',$data['etiqueta'],$data['titulo'],$data['titulo_linea_1'],$data['titulo_linea_2'],$data['titulo_linea_3'],$data['subtitulo'],$data['descripcion'],$data['imagen'],$data['imagen_mobile'],$data['video_youtube'],$data['url'],$data['boton_1_texto'],$data['boton_1_url'],$data['boton_2_texto'],$data['boton_2_url'],$visible,$id,$sid);}
   else{$q=$db->prepare('SELECT COALESCE(MAX(orden),0)+1 AS n FROM seccion_item WHERE id_seccion=?');$q->bind_param('i',$sid);$q->execute();$orden=(int)$q->get_result()->fetch_assoc()['n'];$stmt=$db->prepare('INSERT INTO seccion_item(id_seccion,etiqueta,titulo,titulo_linea_1,titulo_linea_2,titulo_linea_3,subtitulo,descripcion,imagen,imagen_mobile,video_youtube,url,boton_1_texto,boton_1_url,boton_2_texto,boton_2_url,visible,orden) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');$stmt->bind_param('issssssssssssssssi',$sid,$data['etiqueta'],$data['titulo'],$data['titulo_linea_1'],$data['titulo_linea_2'],$data['titulo_linea_3'],$data['subtitulo'],$data['descripcion'],$data['imagen'],$data['imagen_mobile'],$data['video_youtube'],$data['url'],$data['boton_1_texto'],$data['boton_1_url'],$data['boton_2_texto'],$data['boton_2_url'],$visible,$orden);}
   $stmt->execute();
  } elseif ($action==='guardar_config') {
   if (($section['nombre_interno']??'')!=='hero_principal') throw new RuntimeException('La configuración especializada de este contenedor todavía no está disponible.');
   $keys=['alineacion_texto','overlay','mostrar_flechas','mostrar_indicadores'];
   $db->begin_transaction();
   try {
    foreach ($keys as $key) {
     $value=trim((string)($_POST[$key]??''));
     if (strlen($value)>100) throw new RuntimeException('Configuración inválida.');
     $del=$db->prepare('DELETE FROM seccion_config WHERE id_seccion=? AND clave=?');$del->bind_param('is',$sid,$key);$del->execute();
     $ins=$db->prepare('INSERT INTO seccion_config (id_seccion,clave,valor) VALUES (?,?,?)');$ins->bind_param('iss',$sid,$key,$value);$ins->execute();
    }
    $db->commit();
   } catch(Throwable $e) {$db->rollback();throw $e;}
  } else throw new RuntimeException('Acción desconocida.');
  $_SESSION['hero_notice']='Cambios guardados en MySQL.';
  header('Location: editar_contenedor.php?id='.$sid);exit;
 }
 $q=$db->prepare('SELECT * FROM seccion_item WHERE id_seccion=? ORDER BY orden,id_item');$q->bind_param('i',$sid);$q->execute();$items=$q->get_result()->fetch_all(MYSQLI_ASSOC);
 $q=$db->prepare('SELECT clave,valor FROM seccion_config WHERE id_seccion=?');$q->bind_param('i',$sid);$q->execute();foreach($q->get_result()->fetch_all(MYSQLI_ASSOC) as $r) $configs[$r['clave']]=$r['valor'];
} catch(Throwable $e) {error_log('Patrona hero editor: '.$e->getMessage());$error=$e->getMessage();}
$selected=max(0,(int)($_GET['item']??0));$editing=null;foreach($items as $it)if((int)$it['id_item']===$selected)$editing=$it;
$fields=['etiqueta'=>'Etiqueta','titulo_linea_1'=>'Título línea 1','titulo_linea_2'=>'Título línea 2','titulo_linea_3'=>'Título línea 3','descripcion'=>'Descripción','boton_1_texto'=>'Botón 1 texto','boton_1_url'=>'Botón 1 URL','boton_2_texto'=>'Botón 2 texto','boton_2_url'=>'Botón 2 URL'];
function heroYoutubeId(string $url):string{if(preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([a-zA-Z0-9_-]{11})~',$url,$m))return $m[1];return '';}
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Portada principal | CMS</title>
<link rel="stylesheet" href="assets/admin/css/patrona-panel.css?v=4"><link rel="stylesheet" href="assets/admin/css/patrona-admin-menu.css?v=2"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
[hidden]{display:none!important}body{margin:0;background:#f3f6fb;color:#172d4b;font-family:Arial,sans-serif}.hero-main{padding:18px 24px;max-width:1800px;margin:auto}.hero-head{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:12px}.hero-head h1{font-size:25px;margin:5px 0}.muted{color:#667a96;font-size:13px}.hero-panel{background:white;border:1px solid #dce5f1;border-radius:12px;padding:20px;margin:12px 0}.hero-panel h2{font-size:18px;margin:0 0 5px}.hero-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;border:1px solid #d4dfed;background:#fff;color:#1b3d68;border-radius:8px;padding:10px 14px;text-decoration:none;cursor:pointer;font-weight:700;font-size:13px}.hero-btn.primary{background:#203854;color:white;border-color:#203854}.hero-btn:focus-visible,.hero-handle:focus-visible{outline:3px solid #f4ae37;outline-offset:2px}.hero-toolbar{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}.hero-table{width:100%;border-collapse:collapse;margin-top:14px}.hero-table th,.hero-table td{padding:12px;text-align:left;border-bottom:1px solid #e4eaf3}.hero-table th{background:#f5f8fc;font-size:11px;color:#657792;text-transform:uppercase}.hero-table img{width:78px;height:52px;object-fit:cover;border-radius:8px}.hero-cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(245px,1fr));gap:14px;margin-top:16px}.hero-card{border:1px solid #dbe4f1;border-radius:12px;overflow:hidden;background:white}.hero-card .media{height:160px;background:#eef3f9;display:grid;place-items:center;position:relative}.hero-card .media img{width:100%;height:100%;object-fit:cover}.hero-card .details{padding:14px}.hero-card .details strong{display:block;margin:10px 0}.hero-card .details .actions{display:flex;gap:5px;flex-wrap:wrap}.hero-card[draggable=true],.hero-table tr[draggable=true]{cursor:grab}.hero-card.dragging,.hero-table tr.dragging{opacity:.35}.hero-handle{border:1px solid #d7e1ef;border-radius:7px;background:#f7f9fc;padding:7px 10px;cursor:grab}.hero-badge{display:inline-block;border-radius:30px;background:#e7f2ff;color:#1365b0;padding:4px 9px;font-size:12px}.hero-badge.off{background:#f1f3f6;color:#7d8798}.hero-actions{display:flex;gap:5px;align-items:center}.hero-actions form{display:inline}.hero-alert{background:#e4f6ec;padding:12px;border-radius:9px}.hero-alert.error{background:#ffe9e9}.hero-dialog{border:0;border-radius:14px;padding:0;width:min(1150px,calc(100vw - 24px));max-height:90vh;overflow:auto;box-shadow:0 25px 70px #12213d80}.hero-dialog::backdrop{background:#0c19369c}.hero-dialog header,.hero-dialog footer{padding:16px 20px;display:flex;justify-content:space-between;align-items:center;background:#f8fafc;border-bottom:1px solid #e0e8f1}.hero-dialog footer{border-top:1px solid #e0e8f1;border-bottom:0;justify-content:flex-end;gap:9px}.hero-dialog .body{padding:18px 20px}.hero-form-grid{display:grid;grid-template-columns:2fr 1fr;gap:18px}.hero-fields{display:grid;grid-template-columns:1fr 1fr;gap:12px}.hero-field{display:flex;flex-direction:column;gap:7px;font-size:13px;font-weight:700}.hero-field.wide{grid-column:1/-1}.hero-field input,.hero-field textarea,.hero-field select{box-sizing:border-box;width:100%;padding:10px;border:1px solid #cdd9e9;border-radius:8px;font:inherit;font-weight:400}.hero-media-panel{border:1px solid #dce5f1;border-radius:10px;padding:14px}.hero-preview{height:180px;background:#f1f5fa;border:1px dashed #cbd9eb;border-radius:9px;display:grid;place-items:center;overflow:hidden;margin:12px 0}.hero-preview img,.hero-preview video{width:100%;height:100%;object-fit:contain}.hero-media-options{display:flex;gap:6px;margin:10px 0}.hero-media-options label{padding:9px;border:1px solid #cbd9e9;border-radius:8px;cursor:pointer}.hero-media-options label:has(input:checked){background:#203854;color:white}.hero-config{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}.hero-config label{display:grid;gap:6px;font-size:12px;font-weight:bold}.hero-config select{padding:9px;border:1px solid #cbd9e9;border-radius:8px}.hero-sidebar-toggle{width:38px;height:38px;border:1px solid #d6e0ec;border-radius:8px;background:#fff;cursor:pointer;margin-right:9px}.hero-topbar-title{display:flex;align-items:center}.hero-layout .topbar{display:flex;justify-content:space-between;align-items:center}.hero-layout .topbar a{color:#2562a1}.hero-layout .admin-content{min-width:0}.hero-layout .sidebar-nav .nav-item{font-size:13px}@media(min-width:701px){.hero-layout.collapsed .admin-sidebar{width:74px;overflow:hidden}.hero-layout.collapsed .sidebar-brand>span:last-child,.hero-layout.collapsed .nav-group-label,.hero-layout.collapsed .sidebar-subitems,.hero-layout.collapsed .sidebar-bottom,.hero-layout.collapsed .sidebar-watermark{display:none}.hero-layout.collapsed .sidebar-nav .nav-item{font-size:0;justify-content:center}.hero-layout.collapsed .sidebar-nav .nav-item i{font-size:21px}}@media(max-width:850px){.hero-form-grid{grid-template-columns:1fr}.hero-config{grid-template-columns:1fr 1fr}}@media(max-width:600px){.hero-fields,.hero-config{grid-template-columns:1fr}.hero-main{padding:12px}.hero-panel{padding:12px}.hero-table{min-width:750px}.hero-table-wrap{overflow-x:auto}}
</style></head><body><div class="admin-shell hero-layout" id="hero-shell">
<?php $panel =  'contenedores'; require __DIR__.'/includes/patrona_sidebar.php'; ?>
<div class="admin-content"><header class="topbar"><div class="hero-topbar-title">Panel de administración / Contenedores / Portada principal</div><a href="index.php" target="_blank" rel="noopener">Ver sitio <i class="bi bi-arrow-up-right"></i></a></header><main class="hero-main">
<div class="hero-head"><div><small class="muted">CMS / CONTENEDORES</small><h1>Portada principal</h1><p class="muted">Administra las diapositivas, imágenes, videos y botones del carrusel.</p></div><div><a class="hero-btn" href="admin.php?panel=contenedores"><i class="bi bi-arrow-left"></i> Volver</a> <a class="hero-btn primary" href="index.php" target="_blank" rel="noopener"><i class="bi bi-eye"></i> Visualizar</a></div></div>
<?php if($error):?><div class="hero-alert error"><?=he($error)?></div><?php endif;?><?php if($notice):?><div class="hero-alert"><?=he($notice)?></div><?php endif;?>
<?php if($section):?>
<section class="hero-panel"><div class="hero-toolbar"><div><h2>Ítems del contenedor</h2><p class="muted">Arrastra para ordenar; el cambio se guarda automáticamente.</p></div><div><button class="hero-btn primary" type="button" id="hero-add"><i class="bi bi-plus-circle"></i> Agregar ítem</button></div></div>
<div class="hero-toolbar" style="justify-content:flex-end"><button class="hero-btn" type="button" id="hero-list"><i class="bi bi-list-ul"></i> Listado</button><button class="hero-btn" type="button" id="hero-tiles"><i class="bi bi-grid-3x3-gap"></i> Tarjetas</button></div>
<div id="hero-tiles-view" class="hero-cards">
<?php foreach($items as $it):$img=(string)($it['imagen']??'');$youtube=heroYoutubeId((string)($it['video_youtube']??''));if($img===''&&$youtube!=='')$img='https://img.youtube.com/vi/'.$youtube.'/hqdefault.jpg';?>
<article class="hero-card" draggable="true" data-item-id="<?=(int)$it['id_item']?>"><div class="media"><?php if($img!==''):?><img src="<?=he($img)?>" alt="Miniatura de diapositiva"><?php elseif(!empty($it['url'])):?><i class="bi bi-play-btn" style="font-size:42px"></i><?php else:?><i class="bi bi-image" style="font-size:42px"></i><?php endif;?></div><div class="details"><span class="hero-badge <?=($it['visible']??'si')==='si'?'':'off'?>"><?=($it['visible']??'si')==='si'?'● Activo':'Oculto'?></span><strong><?=he((string)(($it['titulo_linea_1']??'')?:($it['titulo']?:'Slide sin título')))?></strong><div class="actions"><button class="hero-handle" type="button" aria-label="Arrastrar diapositiva" title="Arrastrar o usar flechas del teclado"><i class="bi bi-grip-vertical"></i></button><a class="hero-btn" href="?id=<?=$sid?>&item=<?=(int)$it['id_item']?>" aria-label="Editar diapositiva"><i class="bi bi-pencil-square"></i></a><form method="post"><input type="hidden" name="csrf" value="<?=he($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="toggle"><input type="hidden" name="id_item" value="<?=(int)$it['id_item']?>"><button class="hero-btn" aria-label="Cambiar visibilidad"><i class="bi bi-eye<?=($it['visible']??'si')==='si'?'':'-slash'?>"></i></button></form><form method="post" onsubmit="return confirm('¿Eliminar esta diapositiva?')"><input type="hidden" name="csrf" value="<?=he($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="delete"><input type="hidden" name="id_item" value="<?=(int)$it['id_item']?>"><button class="hero-btn" aria-label="Eliminar"><i class="bi bi-trash"></i></button></form></div></div></article>
<?php endforeach;?></div>
<div id="hero-list-view" class="hero-table-wrap" hidden><table class="hero-table"><thead><tr><th>Orden</th><th>Imagen</th><th>Título</th><th>Etiqueta</th><th>Botón 1</th><th>Visible</th><th>Acciones</th></tr></thead><tbody id="hero-list-body"><?php foreach($items as $it):$img=(string)($it['imagen']??'');$youtube=heroYoutubeId((string)($it['video_youtube']??''));if($img===''&&$youtube!=='')$img='https://img.youtube.com/vi/'.$youtube.'/hqdefault.jpg';?><tr draggable="true" data-item-id="<?=(int)$it['id_item']?>"><td><button class="hero-handle" type="button" aria-label="Arrastrar diapositiva"><i class="bi bi-grip-vertical"></i></button><span class="position"><?=(int)$it['orden']?></span></td><td><?php if($img!==''):?><img src="<?=he($img)?>" alt=""><?php else:?><i class="bi bi-camera-video"></i><?php endif;?></td><td><strong><?=he((string)(($it['titulo_linea_1']??'')?:($it['titulo']?:'Sin título')))?></strong></td><td><?=he((string)$it['etiqueta'])?></td><td><?=he((string)$it['boton_1_texto'])?></td><td><span class="hero-badge <?=($it['visible']??'si')==='si'?'':'off'?>"><?=($it['visible']??'si')==='si'?'Activo':'Oculto'?></span></td><td><div class="hero-actions"><a class="hero-btn" href="?id=<?=$sid?>&item=<?=(int)$it['id_item']?>" aria-label="Editar"><i class="bi bi-pencil-square"></i></a><form method="post"><input type="hidden" name="csrf" value="<?=he($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="toggle"><input type="hidden" name="id_item" value="<?=(int)$it['id_item']?>"><button class="hero-btn" aria-label="Cambiar visibilidad"><i class="bi bi-eye"></i></button></form><form method="post" onsubmit="return confirm('¿Eliminar diapositiva?')"><input type="hidden" name="csrf" value="<?=he($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="delete"><input type="hidden" name="id_item" value="<?=(int)$it['id_item']?>"><button class="hero-btn" aria-label="Eliminar"><i class="bi bi-trash"></i></button></form></div></td></tr><?php endforeach;?></tbody></table></div>
<form method="post" id="hero-reorder" hidden><input type="hidden" name="csrf" value="<?=he($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="reorder"><input type="hidden" name="ordered_ids" id="hero-ordered-ids"></form>
</section>
<section class="hero-panel"><h2>Configuración del carrusel</h2><form method="post"><input type="hidden" name="csrf" value="<?=he($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="guardar_config"><div class="hero-config"><?php foreach(['alineacion_texto'=>['Alineación',['izquierda'=>'Izquierda','centro'=>'Centro','derecha'=>'Derecha']],'overlay'=>['Oscurecimiento',['oscuro'=>'Oscuro','claro'=>'Claro','ninguno'=>'Ninguno']],'mostrar_flechas'=>['Flechas',['si'=>'Mostrar','no'=>'Ocultar']],'mostrar_indicadores'=>['Indicadores',['si'=>'Mostrar','no'=>'Ocultar']]] as $key=>$meta):?><label><?=he($meta[0])?><select name="<?=$key?>"><?php foreach($meta[1] as $value=>$label):?><option value="<?=$value?>" <?=($configs[$key]??'')===$value?'selected':''?>><?=he($label)?></option><?php endforeach;?></select></label><?php endforeach;?></div><p><button class="hero-btn primary"><i class="bi bi-check2"></i> Guardar configuración</button></p></form></section>
<dialog id="hero-dialog" class="hero-dialog" aria-labelledby="hero-dialog-title"><form method="post" enctype="multipart/form-data" id="hero-edit-form"><header><h2 id="hero-dialog-title"><?=$editing?'Editar diapositiva':'Agregar diapositiva'?></h2><button type="button" class="hero-btn" id="hero-close" aria-label="Cerrar"><i class="bi bi-x-lg"></i></button></header><div class="body"><input type="hidden" name="csrf" value="<?=he($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="guardar_item"><input type="hidden" name="id_item" value="<?=(int)($editing['id_item']??0)?>"><div class="hero-form-grid"><div class="hero-fields">
<?php foreach($fields as $key=>$label):?><label class="hero-field <?=$key==='descripcion'?'wide':''?>"><?=he($label)?><?php if($key==='descripcion'):?><textarea name="<?=$key?>" rows="4"><?=he((string)($editing[$key]??''))?></textarea><?php else:?><input name="<?=$key?>" value="<?=he((string)($editing[$key]??''))?>" maxlength="1900"><?php endif;?></label><?php endforeach;?>
<label class="hero-field wide"><span><input type="checkbox" name="visible" value="1" <?=($editing['visible']??'si')==='si'?'checked':''?> style="width:auto"> Visible en el sitio</span></label>
<input type="hidden" name="titulo" value="<?=he((string)($editing['titulo']??''))?>"><input type="hidden" name="subtitulo" value="<?=he((string)($editing['subtitulo']??''))?>">
</div><div class="hero-media-panel"><strong>Tipo de diapositiva</strong><div class="hero-media-options"><?php $mediaKind=!empty($editing['video_youtube'])?'youtube':(!empty($editing['url'])?'mp4':'imagen');foreach(['imagen'=>'Imagen','youtube'=>'YouTube','mp4'=>'Video MP4'] as $kind=>$label):?><label><input type="radio" name="media_type" value="<?=$kind?>" <?=$mediaKind===$kind?'checked':''?>> <?=he($label)?></label><?php endforeach;?></div>
<div class="hero-field" data-media="imagen">Imagen principal<input type="file" name="imagen" accept="image/jpeg,image/png,image/webp"><small class="muted">JPG, PNG o WebP. Máximo 8 MB.</small></div>
<div class="hero-field" data-media="imagen">Imagen móvil (opcional)<input type="file" name="imagen_mobile" accept="image/jpeg,image/png,image/webp"></div>
<div class="hero-field" data-media="youtube">Enlace de YouTube<input name="video_youtube" value="<?=he((string)($editing['video_youtube']??''))?>" placeholder="https://www.youtube.com/watch?v=..."></div>
<div class="hero-field" data-media="mp4">Video MP4<input type="file" name="video_mp4" accept="video/mp4"><small class="muted">Máximo 50 MB.</small></div>
<input type="hidden" name="imagen" value="<?=he((string)($editing['imagen']??''))?>"><input type="hidden" name="imagen_mobile" value="<?=he((string)($editing['imagen_mobile']??''))?>"><input type="hidden" name="url" value="<?=he((string)($editing['url']??''))?>">
<div class="hero-preview" id="hero-media-preview"><?php if(!empty($editing['imagen'])):?><img src="<?=he((string)$editing['imagen'])?>" alt="Imagen actual"><?php else:?><span class="muted"><i class="bi bi-image"></i> Vista previa del archivo</span><?php endif;?></div><p class="muted">Elige imagen, YouTube o MP4. No se publicarán dos videos a la vez.</p></div></div></div><footer><button type="button" class="hero-btn" id="hero-cancel">Cerrar</button><button type="submit" class="hero-btn primary"><i class="bi bi-check2-circle"></i> Guardar</button></footer></form></dialog>
<?php endif;?></main></div></div><script defer src="assets/admin/js/patrona-sidebar.js?v=1"></script><script>
(()=>{'use strict';
const dialog=document.getElementById('hero-dialog'),form=document.getElementById('hero-edit-form');if(!dialog||!form)return;
const close=()=>{dialog.close();if(new URLSearchParams(location.search).has('item'))location.replace('editar_contenedor.php?id=<?=isset($sid)?$sid:0?>');};document.getElementById('hero-add')?.addEventListener('click',()=>{form.reset();form.elements.namedItem('id_item').value='0';document.getElementById('hero-dialog-title').textContent='Agregar diapositiva';document.getElementById('hero-media-preview').innerHTML='<span class="muted">Vista previa del archivo</span>';updateMedia();dialog.showModal();});document.getElementById('hero-close')?.addEventListener('click',close);document.getElementById('hero-cancel')?.addEventListener('click',close);dialog.addEventListener('click',e=>{if(e.target===dialog)close();});if(new URLSearchParams(location.search).has('item'))dialog.showModal();
function updateMedia(){const kind=form.querySelector('input[name=media_type]:checked')?.value||'imagen';form.querySelectorAll('[data-media]').forEach(el=>{el.hidden=el.dataset.media!==kind;});}form.querySelectorAll('input[name=media_type]').forEach(el=>el.addEventListener('change',updateMedia));updateMedia();
form.querySelector('input[type=file][name=imagen]')?.addEventListener('change',e=>{const file=e.target.files?.[0];if(file){const url=URL.createObjectURL(file);const img=document.createElement('img');img.src=url;img.alt='Vista previa';img.onload=()=>URL.revokeObjectURL(url);document.getElementById('hero-media-preview').replaceChildren(img);}});
const tiles=document.getElementById('hero-tiles-view'),list=document.getElementById('hero-list-view'),listButton=document.getElementById('hero-list'),tilesButton=document.getElementById('hero-tiles');function view(mode){const isList=mode==='list';tiles.hidden=isList;list.hidden=!isList;listButton.classList.toggle('primary',isList);tilesButton.classList.toggle('primary',!isList);}listButton?.addEventListener('click',()=>view('list'));tilesButton?.addEventListener('click',()=>view('tiles'));view('tiles');
let dragging=null;const orderForm=document.getElementById('hero-reorder');function sync(container){const ids=[...container.querySelectorAll('[data-item-id]')].map(el=>el.dataset.itemId);const initial=<?=json_encode(array_map('strval',array_column($items,'id_item')))?>;if(ids.join(',')!==initial.join(',')){document.getElementById('hero-ordered-ids').value=ids.join(',');orderForm.submit();}}
[tiles,list.querySelector('tbody')].forEach(container=>{container.addEventListener('dragstart',e=>{if(!e.target.closest('.hero-handle')){e.preventDefault();return;}dragging=e.target.closest('[data-item-id]');dragging.classList.add('dragging');e.dataTransfer.effectAllowed='move';e.dataTransfer.setData('text/plain',dragging.dataset.itemId);});container.addEventListener('dragover',e=>{if(!dragging)return;const target=e.target.closest('[data-item-id]');if(!target||target===dragging)return;e.preventDefault();const box=target.getBoundingClientRect();container.insertBefore(dragging,(container===tiles?e.clientX<box.left+box.width/2:e.clientY<box.top+box.height/2)?target:target.nextSibling);});container.addEventListener('drop',e=>{if(!dragging)return;e.preventDefault();dragging.classList.remove('dragging');dragging=null;sync(container);});container.addEventListener('dragend',()=>{if(dragging){dragging.classList.remove('dragging');dragging=null;sync(container);}});container.addEventListener('keydown',e=>{if(!e.target.closest('.hero-handle')||!['ArrowUp','ArrowDown','ArrowLeft','ArrowRight'].includes(e.key))return;e.preventDefault();const row=e.target.closest('[data-item-id]'),prev=['ArrowUp','ArrowLeft'].includes(e.key),other=prev?row.previousElementSibling:row.nextElementSibling;if(!other)return;if(prev)container.insertBefore(row,other);else container.insertBefore(other,row);sync(container);});});
})();
</script><script defer src="assets/admin/js/patrona-sidebar.js?v=2"></script></body></html>
