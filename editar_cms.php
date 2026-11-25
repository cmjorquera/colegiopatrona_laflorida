<?php
declare(strict_types=1);
require_once __DIR__.'/includes/rich_editor.php';
require_once __DIR__.'/componentes/modal_institucional.php';
$sessionPath='/home3/crist668/php_sessions_patrona';
if(!is_dir($sessionPath)||!is_writable($sessionPath)){http_response_code(503);exit('Sesiones no disponibles');}
session_save_path($sessionPath);session_start();
if(empty($_SESSION['patrona_admin_id'])){header('Location: admin.php');exit;}
if(empty($_SESSION['patrona_csrf']))$_SESSION['patrona_csrf']=bin2hex(random_bytes(32));
function ce($v):string{return htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
$allowed=['topbar','header_principal','tab_destacados_home','spotify_podcast_home','galeria_home','estadisticas_home'];
$modalTabs = ['general' => 'General', 'items' => 'Ítems', 'diseno' => 'Diseño', 'preview' => 'Vista previa'];
$tab = (string)($_GET['tab'] ?? 'items');
if (!isset($modalTabs[$tab])) $tab = 'items';
$isModal = false;
$requested=(int)($_GET['id']??$_POST['id_seccion']??0);$error='';$notice=(string)($_SESSION['generic_notice']??'');unset($_SESSION['generic_notice']);$items=[];$config=[];$section=null;
try{
 $cfg=require __DIR__.'/class/conexion_local.php';
 if(!is_array($cfg)||($cfg['db']??'')!=='crist668_colegio_patrona_laflorida')throw new RuntimeException('Base no autorizada');
 mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
 $db=new mysqli($cfg['host'],$cfg['user'],$cfg['pass'],$cfg['db']);$db->set_charset('utf8mb4');
 $q=$db->prepare('SELECT * FROM seccion WHERE id_seccion=? AND id_institucion=2 LIMIT 1');$q->bind_param('i',$requested);$q->execute();$section=$q->get_result()->fetch_assoc();
 if(!$section||in_array((string)$section['nombre_interno'],['hero_principal','sellos_home','noticias_home','tab_comunidad_home'],true))throw new RuntimeException('Este contenedor tiene un editor especializado.');
 $sid=(int)$section['id_seccion'];$name=(string)$section['nombre_interno'];
 $isModal = $name === 'modal_institucional';
 if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!hash_equals((string)$_SESSION['patrona_csrf'],(string)($_POST['csrf']??'')))throw new RuntimeException('Sesión vencida.');
  if(!in_array((string)$section['editable'],['si','1'],true))throw new RuntimeException('Contenedor no editable.');
  $action=(string)($_POST['accion']??'');$id=max(0,(int)($_POST['id_item']??0));
  if($action==='footer_config' && $name==='footer_principal'){
   $keys=['texto','direccion','telefono','correo','horario','mapa_url','instagram_url','facebook_url','youtube_url'];
   $db->begin_transaction();
   try{
    foreach($keys as $key){
     $value=trim((string)($_POST[$key]??''));
     if(mb_strlen($value)>1500)throw new RuntimeException('Texto demasiado extenso.');
     if($key==='correo'&&$value!==''&&!filter_var($value,FILTER_VALIDATE_EMAIL))throw new RuntimeException('Correo no válido.');
     if(str_ends_with($key,'_url')&&$value!==''&&!filter_var($value,FILTER_VALIDATE_URL))throw new RuntimeException('Enlace no válido: '.$key);
     $q=$db->prepare('DELETE FROM seccion_config WHERE id_seccion=? AND clave=?');$q->bind_param('is',$sid,$key);$q->execute();
     $q=$db->prepare('INSERT INTO seccion_config(id_seccion,clave,valor) VALUES(?,?,?)');$q->bind_param('iss',$sid,$key,$value);$q->execute();
    }
    $db->commit();
   }catch(Throwable $e){$db->rollback();throw $e;}
  }elseif($action==='config' || ($action==='modal_design' && $isModal)){
   $db->begin_transaction();
   try{
    $keys = $action === 'modal_design' ? ['modal_encuadre', 'modal_color'] : ['titulo_bloque','subtitulo_bloque','texto','enlace','boton_texto'];
    foreach($keys as $key){
     $value=trim((string)($_POST[$key]??''));if(mb_strlen($value)>1000)throw new RuntimeException('Configuración demasiado extensa.');
     if ($key === 'modal_encuadre' && !in_array($value, ['contain','cover'], true)) throw new RuntimeException('Encuadre inválido.');
     if ($key === 'modal_color' && !in_array($value, ['azul','marino'], true)) throw new RuntimeException('Color inválido.');
     if ($isModal && $key === 'enlace' && $value !== '' && patrona_modal_link($value) === '#') throw new RuntimeException('Enlace inválido.');
     $q=$db->prepare('DELETE FROM seccion_config WHERE id_seccion=? AND clave=?');$q->bind_param('is',$sid,$key);$q->execute();
     $q=$db->prepare('INSERT INTO seccion_config(id_seccion,clave,valor) VALUES(?,?,?)');$q->bind_param('iss',$sid,$key,$value);$q->execute();
    }$db->commit();
   }catch(Throwable $e){$db->rollback();throw $e;}
  }elseif($action==='save'){
   $title=trim((string)($_POST['titulo']??''));$desc=patrona_rich_clean((string)($_POST['descripcion']??''));$url=trim((string)($_POST['url']??''));$label=trim((string)($_POST['etiqueta']??''));$subtitle=trim((string)($_POST['subtitulo']??''));
   if(mb_strlen($title)>220||mb_strlen($desc)>5000||mb_strlen($label)>120||mb_strlen($subtitle)>220)throw new RuntimeException('Texto demasiado largo.');
   if ($isModal && $url !== '' && $url !== '#' && patrona_modal_link($url) === '#') throw new RuntimeException('Ingresa un enlace HTTP(S) o una ruta del sitio, como index.php#noticias-home.');
   if(!$isModal && $url!==''&&!(($name==='equipo_docente_home'&&filter_var($url,FILTER_VALIDATE_EMAIL))||(filter_var($url,FILTER_VALIDATE_URL)&&in_array(strtolower((string)parse_url($url,PHP_URL_SCHEME)),['http','https'],true))))throw new RuntimeException('El enlace debe ser HTTP(S).');
   $imageOnly = $isModal && ($_POST['tipo_modal'] ?? '') === 'solo_imagen';
   $youtube = $isModal ? trim((string)($_POST['video_youtube'] ?? '')) : '';
   if ($youtube !== '' && patrona_modal_youtube_id($youtube) === '') {
    throw new RuntimeException('Ingresa un enlace válido de YouTube.');
   }
   $image='';
   if($id){$q=$db->prepare('SELECT imagen FROM seccion_item WHERE id_item=? AND id_seccion=?');$q->bind_param('ii',$id,$sid);$q->execute();$old=$q->get_result()->fetch_assoc();if(!$old)throw new RuntimeException('Elemento no encontrado');$image=(string)($old['imagen']??'');}
   $hasUpload = isset($_FILES['imagen']) && ($_FILES['imagen']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
   if ($isModal && $youtube !== '' && $hasUpload) throw new RuntimeException('Selecciona un archivo o un enlace de YouTube por ítem.');
   if ($isModal && ($youtube !== '' || isset($_POST['quitar_archivo']))) $image = '';
   if ($hasUpload) {
    $f=$_FILES['imagen'];
    if ($f['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('No se pudo subir el archivo. Comprueba el límite de carga del servidor.');
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $extensions = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    if ($isModal && !$imageOnly) $extensions['video/mp4'] = 'mp4';
    $ext = $extensions[$mime] ?? '';
    if ($ext === '' || ($ext !== 'mp4' && !getimagesize($f['tmp_name']))) throw new RuntimeException($isModal ? 'Solo JPG, PNG, WebP o MP4.' : 'Solo JPG, PNG o WebP.');
    if ($f['size'] > ($ext === 'mp4' ? 50 : 5) * 1024 * 1024) throw new RuntimeException('El archivo excede el límite de carga.');
    $dir=__DIR__.'/assets/uploads/secciones/'.$name;
    if(!is_dir($dir)&&!mkdir($dir,0755,true)&&!is_dir($dir))throw new RuntimeException('No se pudo crear carpeta de imágenes.');
    $filename=bin2hex(random_bytes(16)).'.'.$ext;
    if(!move_uploaded_file($f['tmp_name'],$dir.'/'.$filename))throw new RuntimeException('No se pudo guardar la imagen.');
    $image='assets/uploads/secciones/'.$name.'/'.$filename;
   }
   if ($isModal) {
    $label = $imageOnly ? 'solo_imagen' : 'informativo';
    $buttonText = trim((string)($_POST['boton_1_texto'] ?? ''));
    if (mb_strlen($buttonText) > 120) throw new RuntimeException('Texto del botón demasiado largo.');
    if ($label === 'solo_imagen' && (patrona_modal_local_media($image) === '' || patrona_modal_is_video($image) || $youtube !== '')) {
     throw new RuntimeException('Selecciona una imagen para el tipo Solo imagen.');
    }
   }
   $visible=($_POST['visible']??'si')==='no'?'no':'si';$orden=max(1,(int)($_POST['orden']??1));
   if ($isModal && $id) {
    $q = $db->prepare('UPDATE seccion_item SET etiqueta=?,titulo=?,subtitulo=?,descripcion=?,imagen=?,video_youtube=?,url=?,boton_1_texto=?,visible=?,orden=? WHERE id_item=? AND id_seccion=?');
    $q->bind_param('sssssssssiii', $label, $title, $subtitle, $desc, $image, $youtube, $url, $buttonText, $visible, $orden, $id, $sid);
   } elseif ($isModal) {
    $q = $db->prepare('INSERT INTO seccion_item(id_seccion,etiqueta,titulo,subtitulo,descripcion,imagen,video_youtube,url,boton_1_texto,visible,orden) VALUES(?,?,?,?,?,?,?,?,?,?,?)');
    $q->bind_param('isssssssssi', $sid, $label, $title, $subtitle, $desc, $image, $youtube, $url, $buttonText, $visible, $orden);
   } elseif($id){
    $q=$db->prepare('UPDATE seccion_item SET etiqueta=?,titulo=?,subtitulo=?,descripcion=?,imagen=?,url=?,visible=?,orden=? WHERE id_item=? AND id_seccion=?');
    $q->bind_param('sssssssiii',$label,$title,$subtitle,$desc,$image,$url,$visible,$orden,$id,$sid);
   }else{
    $q=$db->prepare('INSERT INTO seccion_item(id_seccion,etiqueta,titulo,subtitulo,descripcion,imagen,url,visible,orden) VALUES(?,?,?,?,?,?,?,?,?)');
    $q->bind_param('isssssssi',$sid,$label,$title,$subtitle,$desc,$image,$url,$visible,$orden);
   }$q->execute();
  }elseif($action==='toggle'){
   $q=$db->prepare("UPDATE seccion_item SET visible=IF(visible='si','no','si') WHERE id_item=? AND id_seccion=?");$q->bind_param('ii',$id,$sid);$q->execute();
  }elseif($action==='reorder'){
   $ids=array_values(array_unique(array_filter(array_map('intval',explode(',',(string)($_POST['orden_ids']??''))),static fn(int $v):bool=>$v>0)));
   $q=$db->prepare('SELECT id_item FROM seccion_item WHERE id_seccion=?');$q->bind_param('i',$sid);$q->execute();$actual=array_map('intval',array_column($q->get_result()->fetch_all(MYSQLI_ASSOC),'id_item'));
   $a=$ids;$b=$actual;sort($a);sort($b);if($a!==$b)throw new RuntimeException('Orden inválido. Recarga la página.');
   $db->begin_transaction();try{$q=$db->prepare('UPDATE seccion_item SET orden=? WHERE id_item=? AND id_seccion=?');foreach($ids as $pos=>$itemId){$order=$pos+1;$q->bind_param('iii',$order,$itemId,$sid);$q->execute();}$db->commit();}catch(Throwable $e){$db->rollback();throw $e;}
  }elseif($action==='delete'){
   $q=$db->prepare('DELETE FROM seccion_item WHERE id_item=? AND id_seccion=?');$q->bind_param('ii',$id,$sid);$q->execute();
  }else throw new RuntimeException('Acción inválida');
  $_SESSION['generic_notice']='Cambios guardados correctamente.';header('Location: editar_cms.php?id='.$sid.($isModal ? '&tab='.$tab : ''));exit;
 }
 $q=$db->prepare('SELECT * FROM seccion_item WHERE id_seccion=? ORDER BY orden,id_item');$q->bind_param('i',$sid);$q->execute();$items=$q->get_result()->fetch_all(MYSQLI_ASSOC);
 $q=$db->prepare('SELECT clave,valor FROM seccion_config WHERE id_seccion=?');$q->bind_param('i',$sid);$q->execute();foreach($q->get_result()->fetch_all(MYSQLI_ASSOC) as $r)$config[$r['clave']]=$r['valor'];
}catch(Throwable $e){error_log('Patrona CMS editor general: '.$e->getMessage());$error=$e->getMessage();}
$editing=null;foreach($items as $item)if((int)$item['id_item']===(int)($_GET['item']??0))$editing=$item;
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Editor de contenedores | CMS</title><?php require __DIR__.'/includes/patrona_admin_assets.php'; ?>
<style>
.admin-shell{min-height:100vh;background:#f3f6fb}.cms-main{max-width:none!important}.cms-side{background:#102b50;color:#fff;padding:24px 18px}.cms-side a{display:block;padding:12px;color:#e0eafb;text-decoration:none}.cms-main{max-width:1120px;width:100%;margin:auto;padding:32px}.cms-card{background:#fff;border:1px solid #dce5ef;border-radius:12px;padding:24px;margin:22px 0}.cms-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.cms-field{display:flex;flex-direction:column;gap:7px;font-weight:600;font-size:13px}.cms-field input,.cms-field textarea,.cms-field select{border:1px solid #cbd5e1;border-radius:7px;padding:11px;font:inherit;font-weight:400}.cms-btn{background:#1765b8;color:white;border:0;border-radius:7px;padding:10px 16px;cursor:pointer;text-decoration:none}.cms-row{display:flex;align-items:center;gap:16px;padding:12px;border-bottom:1px solid #e4eaf0}.cms-row img{width:110px;height:82px;object-fit:cover;border-radius:9px}.cms-preview{display:none;max-width:320px;max-height:210px;object-fit:contain;border:1px solid #dce5ef;border-radius:10px;padding:8px;margin-top:12px}.cms-preview.is-visible{display:block}.cms-row{background:#fff;border-radius:10px;margin:8px 0}.cms-row strong{color:#123b70}.cms-row a{display:inline-block;margin-top:6px;color:#1765b8;font-weight:600}.cms-field input:focus,.cms-field textarea:focus,.cms-field select:focus{outline:2px solid #91bff2;border-color:#1765b8}.cms-row form{margin-left:auto}@media(max-width:750px){.cms-shell{grid-template-columns:1fr}.cms-grid{grid-template-columns:1fr}.cms-main{padding:15px}}
 .gallery-admin{margin-top:16px}.gallery-heading{display:flex;align-items:center;justify-content:space-between;gap:12px}.gallery-heading h2{margin:0}.gallery-heading p{font-size:12px;color:#657b95}.gallery-toolbar{display:flex;justify-content:flex-end;margin:14px 0}.gallery-views{background:#f3f6fa;padding:5px;border-radius:8px;display:flex;gap:4px}.gallery-views button{border:0;border-radius:6px;background:transparent;padding:9px 14px;cursor:pointer}.gallery-views button.active{background:#102c50;color:#fff}.gallery-list table{width:100%;border-collapse:collapse}.gallery-list th{text-align:left;text-transform:uppercase;font-size:11px;color:#5b7190;background:#f8fafc}.gallery-list th,.gallery-list td{padding:12px;border-bottom:1px solid #e1e9f1}.gallery-thumb{width:80px;height:55px;object-fit:cover;border-radius:7px}.gallery-grip{cursor:grab;color:#53769d}.gallery-list tr.dragging{opacity:.4}.gallery-list tr.dragover{outline:2px dashed #2677c1}.gallery-switch{width:36px;height:20px;border:0;border-radius:20px;background:#bdc8d6;padding:3px;cursor:pointer}.gallery-switch.on{background:#07669e}.gallery-switch span{display:block;width:14px;height:14px;background:white;border-radius:50%;transition:transform .15s}.gallery-switch.on span{transform:translateX(16px)}.gallery-actions{white-space:nowrap}.gallery-actions form{display:inline}.gallery-icon{background:#fff;border:1px solid #d6e3f1;color:#1a65a6;border-radius:7px;padding:9px;cursor:pointer;margin-right:5px}.gallery-cards{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.gallery-cards[hidden],.gallery-modal[hidden]{display:none!important}.gallery-tile{border:1px solid #dce5ef;border-radius:10px;overflow:hidden}.gallery-tile img{width:100%;height:185px;object-fit:cover}.gallery-tile>div{padding:12px}.gallery-tile p{font-size:12px;color:#667b91}.gallery-tile button{background:none;border:0;color:#1262a4;cursor:pointer}.gallery-modal{position:fixed;inset:0;z-index:10000;background:#0d284a80;display:flex;justify-content:center;align-items:center;padding:20px}.gallery-dialog{background:white;border-radius:12px;width:min(860px,100%);max-height:calc(100vh - 40px);overflow:auto;box-shadow:0 20px 60px #09213e55}.gallery-modal-header,.gallery-modal-footer{display:flex;justify-content:space-between;align-items:center;padding:18px 22px;border-bottom:1px solid #dce5ef}.gallery-modal-header h2{margin:0}.gallery-modal-body{padding:22px}.gallery-modal-footer{justify-content:flex-end;gap:12px;border-top:1px solid #dce5ef;border-bottom:0;background:#f8fafc}.gallery-close{font-size:28px;background:none;border:0;cursor:pointer;color:#56708d}.gallery-check{flex-direction:row;align-items:center}.gallery-image-preview{max-width:240px;max-height:160px;object-fit:contain;margin-top:8px;border-radius:8px}.gallery-image-preview[hidden]{display:none!important}@media(max-width:750px){.gallery-cards{grid-template-columns:1fr}.gallery-list{overflow:auto}.gallery-heading{align-items:flex-start;flex-direction:column}}
.modal-editor-tabs{display:flex;flex-wrap:wrap;gap:10px;padding:12px;background:#f6f8fc}
.modal-editor-tabs a{padding:9px 16px;border:1px solid #dbe5ef;border-radius:8px;background:white;color:#193553;font-weight:600}
.modal-editor-tabs a[aria-current=page]{background:#102d52;border-color:#102d52;color:white}
.modal-item-list{margin-top:16px;border:1px solid #dbe5ef;border-radius:10px;overflow:hidden}
.modal-item-list:empty{display:none}
.modal-item{display:grid;grid-template-columns:40px 110px minmax(0,1fr) auto;gap:12px;align-items:center;padding:12px;border-bottom:1px solid #dbe5ef}
.modal-item:last-child{border-bottom:0}
.modal-item-actions{display:flex;flex-wrap:wrap;align-items:center;gap:6px}
.modal-item-actions form{margin:0}
.modal-item-list.is-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(230px,100%),1fr));gap:12px;border:0}
.modal-item-list.is-cards .modal-item{grid-template-columns:1fr;gap:8px;border:1px solid #dbe5ef;border-radius:10px}
.modal-item-list.is-cards .gallery-thumb{width:100%;height:135px;object-fit:contain;background:#f3f6fa}
@media(max-width:700px){.modal-item{grid-template-columns:28px 74px minmax(0,1fr)}.modal-item-actions{grid-column:3}}
.modal-type-picker{grid-column:1/-1;display:grid;grid-template-columns:repeat(auto-fit,minmax(min(240px,100%),1fr));gap:10px;margin:0;padding:12px;border:1px solid #dbe5ef;border-radius:10px}
.modal-type-picker legend{padding:0 6px;font-weight:700}.modal-type-picker label{display:flex;align-items:center;gap:10px;padding:12px;border:1px solid #dbe5ef;border-radius:8px;cursor:pointer}.modal-type-picker label:has(:checked){border-color:#193553;background:#f0f5fa}.modal-type-picker small{display:block;color:#526985}.cms-field[data-modal-informativo][hidden]{display:none}
#generic-modal.institutional-item-editor{grid-template-columns:minmax(0,1fr)}
#generic-modal.institutional-item-editor .patrona-modal-panel{width:min(1120px,calc(100vw - 32px))}
@media(max-width:700px){#generic-modal.institutional-item-editor .patrona-modal-panel{width:calc(100vw - 20px)}}
#generic-modal .institutional-wide{grid-column:1/-1}
#generic-modal.institutional-item-editor .cms-field[hidden]{display:none}
.institutional-item-editor .cms-field,.institutional-item-editor details{border:1px solid #dbe5ef;border-radius:9px;padding:10px;min-width:0}
.institutional-item-editor .rich-editor .rich-content{min-height:110px;padding:10px 12px}
.institutional-item-editor #modal-item-options[open]>.cms-grid{margin-top:10px}
</style><?php patrona_rich_assets(); ?></head><body><div class="admin-shell"><?php $panel = 'contenedores'; require __DIR__.'/includes/patrona_sidebar.php'; ?><div class="admin-content"><header class="topbar" data-sidebar-toolbar><span>Panel de administración / Contenedores / Editor</span><a href="admin.php?panel=contenedores">← Volver</a></header><main class="cms-main"><small>GESTIÓN DEL SITIO / CONTENEDORES</small><h1><?=ce($section['titulo_admin']??'Editor de contenedores')?></h1><p>Configuración y elementos del contenedor. Utiliza las mismas tablas CMS de Tabancura.</p>
<?php if($error):?><p style="color:#a92323"><?=ce($error)?></p><?php endif;?><?php if($notice):?><p style="color:#16703b"><?=ce($notice)?></p><?php endif;?>
<?php if($section):?>
<?php if ($isModal): ?>
<nav class="cms-card modal-editor-tabs" aria-label="Configuración del modal">
 <?php foreach ($modalTabs as $key => $label): ?>
 <a href="?id=<?=$sid?>&amp;tab=<?=ce($key)?>" <?=$key === $tab ? 'aria-current="page"' : ''?>><?=ce($label)?></a>
 <?php endforeach; ?>
</nav>
<?php endif; ?>
<?php if (!$isModal || $tab === 'general'): ?>
<?php if(($section['nombre_interno']??'')==='footer_principal'):?>
<section class="cms-card"><h2>Identidad y contacto del colegio</h2><p>Estos datos se muestran en el pie de página. Completa únicamente información verificada.</p>
<form method="post"><input type="hidden" name="id_seccion" value="<?=$sid?>"><input type="hidden" name="csrf" value="<?=ce($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="footer_config"><div class="cms-grid">
<?php foreach(['texto'=>'Descripción institucional','direccion'=>'Dirección','telefono'=>'Teléfono','correo'=>'Correo electrónico','horario'=>'Horario de atención','mapa_url'=>'Enlace de Google Maps','instagram_url'=>'Instagram (URL)','facebook_url'=>'Facebook (URL)','youtube_url'=>'YouTube (URL)'] as $key=>$label):?>
<label class="cms-field"><?=ce($label)?><?php if($key==='texto'):?><textarea name="<?=ce($key)?>" rows="4" maxlength="1500"><?=ce($config[$key]??'')?></textarea><?php else:?><input name="<?=ce($key)?>" value="<?=ce($config[$key]??'')?>" maxlength="1500" <?=$key==='correo'?'type="email"':''?>><?php endif;?></label>
<?php endforeach;?></div><p><button class="cms-btn">Guardar pie de página</button></p></form></section>
<section class="cms-card"><h2>Enlaces institucionales</h2><p>Se obtienen del menú público administrable; no es necesario duplicarlos en el pie de página.</p><a href="admin.php?panel=menu">Administrar menú público</a></section>
<?php else:?>
<section class="cms-card"><h2>Configuración</h2><?php if ($isModal): ?><p>Estos valores se usan cuando un ítem no tiene su propio título, texto o enlace.</p><?php endif; ?><form method="post"><input type="hidden" name="id_seccion" value="<?=(int)$sid?>"><input type="hidden" name="csrf" value="<?=ce($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="config"><div class="cms-grid">
<?php foreach(['titulo_bloque'=>'Título del bloque','subtitulo_bloque'=>'Subtítulo','texto'=>'Texto','enlace'=>'Enlace','boton_texto'=>'Texto del botón'] as $key=>$label):?><label class="cms-field"><?=ce($label)?><input name="<?=ce($key)?>" value="<?=ce($config[$key]??'')?>" maxlength="1000"></label><?php endforeach;?>
</div><p><button class="cms-btn">Guardar configuración</button></p></form></section>
<?php endif;?>
<?php endif; ?>
<?php if ($isModal && $tab === 'diseno'): ?>
<section class="cms-card">
 <h2>Diseño del modal</h2>
 <form method="post">
  <input type="hidden" name="csrf" value="<?=ce($_SESSION['patrona_csrf'])?>">
  <input type="hidden" name="accion" value="modal_design">
  <div class="cms-grid">
   <label class="cms-field">Encuadre de imagen
    <select name="modal_encuadre">
     <option value="contain">Imagen completa</option>
     <option value="cover" <?=($config['modal_encuadre'] ?? '') === 'cover' ? 'selected' : ''?>>Llenar el espacio</option>
    </select>
   </label>
   <label class="cms-field">Color del botón
    <select name="modal_color">
     <option value="azul">Azul institucional</option>
     <option value="marino" <?=($config['modal_color'] ?? '') === 'marino' ? 'selected' : ''?>>Azul marino</option>
    </select>
   </label>
  </div>
  <p><button class="cms-btn">Guardar diseño</button></p>
 </form>
</section>
<?php elseif ($isModal && $tab === 'preview'): ?>
<section class="cms-card" id="modal-preview"><h2>Vista previa</h2>
 <?php if ($section['estado'] !== 'activo' || $section['visible'] !== 'si'): ?>
 <p>El contenedor está inactivo u oculto. Esta vista solo es visible desde el panel.</p>
 <?php endif; ?>
 <?php
 $previewItem = (int)($_GET['preview_item'] ?? 0);
 $previewItems = $previewItem ? array_filter($items, static fn(array $item): bool => (int)$item['id_item'] === $previewItem) : $items;
 patrona_render_modal_institucional([array_merge($section, ['items' => $previewItems, 'config' => $config])], true);
 ?>
</section>
<?php elseif (!$isModal || $tab === 'items'): ?>
<?php if(($section['nombre_interno']??'')==='footer_principal'):?>
<section class="cms-card"><h2>Vista previa</h2><p>El pie de página se actualiza en el sitio público después de guardar los cambios.</p><a class="cms-btn" href="index.php#contacto" target="_blank" rel="noopener">Ver pie de página ↗</a></section>
<?php elseif(($section['nombre_interno']??'')==='galeria_home'):?>
<section class="cms-card gallery-admin">
 <div class="gallery-heading"><div><h2>Ítems del contenedor</h2><p>Arrastra las imágenes para cambiar su orden. Los cambios se guardan al soltar.</p></div><button type="button" class="cms-btn" id="gallery-add"><i class="bi bi-plus-circle"></i> Agregar imagen</button></div>
 <div class="gallery-toolbar"><div class="gallery-views"><button type="button" class="active" data-gallery-view="list"><i class="bi bi-list-ul"></i> Listado</button><button type="button" data-gallery-view="cards"><i class="bi bi-grid-3x3-gap"></i> Tarjetas</button></div></div>
 <div class="gallery-list" id="gallery-list"><table><thead><tr><th></th><th>Orden</th><th>Imagen</th><th>Título</th><th>Visible</th><th>Acciones</th></tr></thead><tbody id="gallery-sortable">
 <?php foreach($items as $item):?><tr draggable="true" data-id="<?=(int)$item['id_item']?>"><td class="gallery-grip" title="Arrastrar para ordenar"><i class="bi bi-grip-vertical"></i></td><td class="gallery-order"><?=(int)$item['orden']?></td><td><?php if($item['imagen']):?><img class="gallery-thumb" src="<?=ce($item['imagen'])?>" alt="<?=ce($item['titulo']??'')?>"><?php endif;?></td><td><?=ce($item['titulo']?:'Imagen sin título')?></td><td><form method="post"><input type="hidden" name="csrf" value="<?=ce($_SESSION['patrona_csrf'])?>"><input type="hidden" name="id_seccion" value="<?=$sid?>"><input type="hidden" name="accion" value="toggle"><input type="hidden" name="id_item" value="<?=(int)$item['id_item']?>"><button class="gallery-switch <?=($item['visible']??'')==='si'?'on':''?>" type="submit" title="Cambiar visibilidad" aria-label="Cambiar visibilidad"><span></span></button></form></td><td class="gallery-actions"><button type="button" class="gallery-icon gallery-edit" data-id="<?=(int)$item['id_item']?>" title="Editar imagen"><i class="bi bi-pencil-square"></i></button><form method="post" onsubmit="return confirm('¿Eliminar esta imagen de la galería?')"><input type="hidden" name="csrf" value="<?=ce($_SESSION['patrona_csrf'])?>"><input type="hidden" name="id_seccion" value="<?=$sid?>"><input type="hidden" name="accion" value="delete"><input type="hidden" name="id_item" value="<?=(int)$item['id_item']?>"><button class="gallery-icon" title="Eliminar imagen"><i class="bi bi-trash"></i></button></form></td></tr><?php endforeach;?>
 </tbody></table><?php if(!$items):?><p>No hay imágenes registradas. Pulsa Agregar imagen para comenzar.</p><?php endif;?></div>
 <div class="gallery-cards" id="gallery-cards" hidden><?php foreach($items as $item):?><article class="gallery-tile"><?php if($item['imagen']):?><img src="<?=ce($item['imagen'])?>" alt="<?=ce($item['titulo']??'')?>"><?php endif;?><div><strong><?=ce($item['titulo']?:'Imagen sin título')?></strong><p>Orden <?=(int)$item['orden']?> · <?=($item['visible']??'')==='si'?'Visible':'Oculta'?></p><button type="button" class="gallery-edit" data-id="<?=(int)$item['id_item']?>">Editar imagen</button></div></article><?php endforeach;?></div>
 <form method="post" id="gallery-reorder-form" hidden><input type="hidden" name="csrf" value="<?=ce($_SESSION['patrona_csrf'])?>"><input type="hidden" name="id_seccion" value="<?=$sid?>"><input type="hidden" name="accion" value="reorder"><input type="hidden" name="orden_ids" id="gallery-order-ids"></form>
</section>
<div class="gallery-modal patrona-modal-overlay" id="gallery-modal" hidden role="dialog" aria-modal="true" aria-labelledby="gallery-modal-title"><div class="gallery-dialog patrona-modal-panel"><div class="gallery-modal-header patrona-modal-header"><h2 id="gallery-modal-title">Agregar imagen</h2><button type="button" class="gallery-close patrona-modal-close" aria-label="Cerrar"><span aria-hidden="true">×</span></button></div><form method="post" enctype="multipart/form-data" id="gallery-form"><input type="hidden" name="id_seccion" value="<?=$sid?>"><input type="hidden" name="csrf" value="<?=ce($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="save"><input type="hidden" name="id_item" id="gallery-item-id" value="0"><div class="gallery-modal-body patrona-modal-body"><div class="cms-grid"><label class="cms-field">Imagen (JPG, PNG o WebP; máximo 5 MB)<input type="file" name="imagen" id="gallery-image-input" accept="image/jpeg,image/png,image/webp"><img id="gallery-image-preview" class="gallery-image-preview" alt="Vista previa de la imagen" hidden></label><label class="cms-field">Título o texto alternativo<input name="titulo" id="gallery-title" maxlength="220" placeholder="Descripción breve de la fotografía"></label><label class="cms-field">Enlace opcional<input type="url" name="url" id="gallery-url" placeholder="https://..."></label><label class="cms-field">Orden<input type="number" name="orden" id="gallery-order" min="1" value="<?=count($items)+1?>"></label><label class="cms-field gallery-check"><input type="checkbox" id="gallery-visible" checked> Mostrar en la página</label><input type="hidden" name="visible" id="gallery-visible-value" value="si"></div></div><div class="gallery-modal-footer patrona-modal-footer"><button class="cms-btn" type="submit"><i class="bi bi-check-circle"></i> Guardar imagen</button></div></form></div></div>
<script type="application/json" id="gallery-data"><?=json_encode(array_map(static fn(array $i):array=>['id'=>(int)$i['id_item'],'titulo'=>(string)($i['titulo']??''),'url'=>(string)($i['url']??''),'orden'=>(int)$i['orden'],'visible'=>(string)$i['visible'],'imagen'=>(string)($i['imagen']??'')],$items),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)?></script>
<?php elseif(($section['nombre_interno']??'')==='estadisticas_home'):?>
<section class="cms-card gallery-admin">
<div class="gallery-heading"><div><h2>Indicadores del colegio</h2><p>Administra el número, título, descripción e imagen de cada indicador. Arrastra las filas para ordenar.</p></div><button type="button" class="cms-btn" id="stat-add"><i class="bi bi-plus-circle"></i> Agregar indicador</button></div>
<div class="gallery-toolbar"><div class="gallery-views"><button type="button" class="active" data-stat-view="list">☷ Listado</button><button type="button" data-stat-view="cards">▦ Tarjetas</button></div></div>
<div class="gallery-list" id="stat-list"><table><thead><tr><th></th><th>Orden</th><th>Imagen</th><th>Número</th><th>Título</th><th>Visible</th><th>Acciones</th></tr></thead><tbody id="stat-sortable"><?php foreach($items as $item):?><tr draggable="true" data-id="<?=(int)$item['id_item']?>"><td class="gallery-grip">⠿</td><td class="gallery-order"><?=(int)$item['orden']?></td><td><?php if($item['imagen']):?><img class="gallery-thumb" src="<?=ce($item['imagen'])?>" alt=""><?php endif;?></td><td><strong><?=ce($item['etiqueta']??'')?></strong></td><td><?=ce($item['titulo']??'')?></td><td><form method="post"><input type="hidden" name="csrf" value="<?=ce($_SESSION['patrona_csrf'])?>"><input type="hidden" name="id_seccion" value="<?=$sid?>"><input type="hidden" name="accion" value="toggle"><input type="hidden" name="id_item" value="<?=(int)$item['id_item']?>"><button class="gallery-switch <?=($item['visible']??'')==='si'?'on':''?>" aria-label="Cambiar visibilidad"><span></span></button></form></td><td class="gallery-actions"><button type="button" class="gallery-icon stat-edit" data-id="<?=(int)$item['id_item']?>" title="Editar"><i class="bi bi-pencil-square"></i></button><form method="post" onsubmit="return confirm('¿Eliminar este indicador?')"><input type="hidden" name="csrf" value="<?=ce($_SESSION['patrona_csrf'])?>"><input type="hidden" name="id_seccion" value="<?=$sid?>"><input type="hidden" name="accion" value="delete"><input type="hidden" name="id_item" value="<?=(int)$item['id_item']?>"><button class="gallery-icon" title="Eliminar"><i class="bi bi-trash"></i></button></form></td></tr><?php endforeach;?></tbody></table><?php if(!$items):?><p>No hay indicadores. Agrega el primero.</p><?php endif;?></div>
<div class="gallery-cards" id="stat-cards" hidden><?php foreach($items as $item):?><article class="gallery-tile"><?php if($item['imagen']):?><img src="<?=ce($item['imagen'])?>" alt=""><?php endif;?><div><strong><?=ce($item['etiqueta']??'')?> · <?=ce($item['titulo']??'')?></strong><p><?=ce(strip_tags((string)($item['descripcion']??'')))?></p><button type="button" class="stat-edit" data-id="<?=(int)$item['id_item']?>">Editar indicador</button></div></article><?php endforeach;?></div>
<form method="post" id="stat-reorder-form" hidden><input type="hidden" name="csrf" value="<?=ce($_SESSION['patrona_csrf'])?>"><input type="hidden" name="id_seccion" value="<?=$sid?>"><input type="hidden" name="accion" value="reorder"><input type="hidden" name="orden_ids" id="stat-order-ids"></form>
</section>
<div class="gallery-modal patrona-modal-overlay" id="stat-modal" hidden role="dialog" aria-modal="true" aria-labelledby="stat-modal-title"><div class="gallery-dialog patrona-modal-panel"><div class="gallery-modal-header patrona-modal-header"><h2 id="stat-modal-title">Agregar indicador</h2><button type="button" class="gallery-close patrona-modal-close" aria-label="Cerrar"><span aria-hidden="true">×</span></button></div><form method="post" enctype="multipart/form-data" id="stat-form"><input type="hidden" name="id_seccion" value="<?=$sid?>"><input type="hidden" name="csrf" value="<?=ce($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="save"><input type="hidden" name="id_item" id="stat-id" value="0"><div class="gallery-modal-body patrona-modal-body"><div class="cms-grid">
<label class="cms-field">Número o porcentaje<input name="etiqueta" id="stat-number" maxlength="120" placeholder="Ej.: 100%, 450, +20"></label><label class="cms-field">Título<input name="titulo" id="stat-title" maxlength="220" placeholder="Ej.: Estudiantes"></label><label class="cms-field">Imagen JPG, PNG o WebP (máx. 5 MB)<input type="file" name="imagen" id="stat-image" accept="image/jpeg,image/png,image/webp"><img class="gallery-image-preview" id="stat-preview" alt="Vista previa" hidden></label><label class="cms-field">Orden<input type="number" min="1" name="orden" id="stat-order" value="<?=count($items)+1?>"></label><label class="cms-field" style="grid-column:1/-1">Descripción breve<textarea name="descripcion" id="stat-description" rows="3" maxlength="5000"></textarea></label><label class="cms-field gallery-check"><input type="checkbox" id="stat-visible" checked> Mostrar en el sitio</label><input type="hidden" name="visible" id="stat-visible-value" value="si">
</div></div><div class="gallery-modal-footer patrona-modal-footer"><button class="cms-btn" type="submit">Guardar indicador</button></div></form></div></div>
<script type="application/json" id="stat-data"><?=json_encode(array_map(static fn(array $i):array=>['id'=>(int)$i['id_item'],'etiqueta'=>(string)($i['etiqueta']??''),'titulo'=>(string)($i['titulo']??''),'descripcion'=>(string)($i['descripcion']??''),'orden'=>(int)$i['orden'],'visible'=>(string)$i['visible'],'imagen'=>(string)($i['imagen']??'')],$items),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)?></script>
<?php else:?>
<section class="cms-card"><div class="gallery-heading"><div><h2>Ítems del contenedor</h2><p>Administra los elementos del bloque desde una ventana de edición.</p></div><button type="button" class="cms-btn" id="generic-add"><i class="bi bi-plus-circle"></i> Agregar elemento</button></div></section>
<div class="gallery-modal patrona-modal-overlay<?=$isModal ? ' institutional-item-editor' : ''?>" id="generic-modal" hidden role="dialog" aria-modal="true" aria-labelledby="generic-modal-title"><div class="gallery-dialog patrona-modal-panel"><div class="gallery-modal-header patrona-modal-header"><h2 id="generic-modal-title">Agregar elemento</h2><button type="button" class="gallery-close patrona-modal-close" aria-label="Cerrar"><span aria-hidden="true">×</span></button></div><form method="post" enctype="multipart/form-data" id="generic-form"><input type="hidden" name="id_seccion" value="<?=(int)$sid?>"><input type="hidden" name="csrf" value="<?=ce($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="save"><input type="hidden" name="id_item" value="<?=(int)($editing['id_item']??0)?>"><div class="patrona-modal-body"><div class="cms-grid">
<?php if ($isModal): ?>
<fieldset class="modal-type-picker">
 <legend>Tipo de modal</legend>
 <label><input type="radio" name="tipo_modal" value="informativo" checked><span><strong>Informativo</strong><small>Imagen o video, título, mensaje y botón</small></span></label>
 <label><input type="radio" name="tipo_modal" value="solo_imagen"><span><strong>Solo imagen</strong><small>Afiche con enlace opcional</small></span></label>
</fieldset>
<label class="cms-field institutional-wide"><span id="modal-file-label">Imagen lateral o video MP4</span>
 <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp,video/mp4">
 <small id="modal-current-file"></small>
 <span id="modal-remove-file" hidden><input type="checkbox" name="quitar_archivo" value="1"> Quitar archivo actual</span>
</label>
<label class="cms-field institutional-wide" id="modal-item-title">Título<input name="titulo" maxlength="220" placeholder="Bienvenidos al nuevo año escolar"></label>
<label class="cms-field institutional-wide" data-modal-informativo>Mensaje<textarea name="descripcion" rows="4" maxlength="5000" data-rich-editor></textarea></label>
<label class="cms-field" data-modal-informativo>Texto del botón<input name="boton_1_texto" maxlength="120" placeholder="Comenzar a navegar"><small>Vacío = sin botón.</small></label>
<label class="cms-field" id="modal-link-field"><span id="modal-link-label">Enlace del botón</span>
 <input type="text" name="url" placeholder="# para solo cerrar o https://…">
 <small id="modal-link-help">Con #, el botón cierra el modal y deja al visitante en la portada.</small>
</label>
<details id="modal-item-options" class="institutional-wide">
 <summary>Más opciones</summary>
 <div class="cms-grid">
  <label class="cms-field" data-modal-informativo>Enlace de YouTube<input type="url" name="video_youtube" placeholder="https://www.youtube.com/watch?v=…"></label>
  <label class="cms-field">Orden en el carrusel<input type="number" min="1" name="orden"></label>
  <label class="cms-field">Visibilidad<select name="visible"><option value="si">Visible</option><option value="no">Oculto</option></select></label>
 </div>
</details>
<input type="hidden" name="subtitulo">
<?php else: ?>
<label class="cms-field">Título<input name="titulo" maxlength="220" value="<?=ce($editing['titulo']??'')?>"></label>
<?php if(($section['nombre_interno']??'')==='equipo_docente_home'):?>
<label class="cms-field">Ciclo<select name="etiqueta"><?php foreach(['jardin'=>'Jardín','basica_1'=>'Básica 1.er ciclo','basica_2'=>'Básica 2.º ciclo','media'=>'Enseñanza Media'] as $key=>$label):?><option value="<?=ce($key)?>" <?=($editing['etiqueta']??'')===$key?'selected':''?>><?=ce($label)?></option><?php endforeach;?></select></label>
<?php else:?><label class="cms-field">Etiqueta<input name="etiqueta" maxlength="120" value="<?=ce($editing['etiqueta']??'')?>"></label><?php endif;?>
<label class="cms-field"><?=($section['nombre_interno']??'')==='equipo_docente_home'?'Cargo / curso (Coordinación, Profesor jefe, etc.)':'Subtítulo'?><input name="subtitulo" maxlength="220" value="<?=ce($editing['subtitulo']??'')?>"></label>
<label class="cms-field">Imagen JPG/PNG/WebP, máximo 5 MB<input type="file" name="imagen" accept="image/jpeg,image/png,image/webp" id="cms-image-input"><img class="cms-preview" id="cms-image-preview" alt="Vista previa de imagen seleccionada"></label>
<label class="cms-field">Enlace<input type="<?=($name==='equipo_docente_home')?'email':'url'?>" name="url" value="<?=ce($editing['url']??'')?>"></label>
<label class="cms-field">Orden<input type="number" min="1" name="orden"></label>
<label class="cms-field">Visibilidad<select name="visible"><option value="si">Visible</option><option value="no">Oculto</option></select></label>
<label class="cms-field" style="grid-column:1/-1">Descripción<textarea name="descripcion" rows="4" maxlength="5000" data-rich-editor><?=ce($editing['descripcion']??'')?></textarea></label>
<?php endif; ?>
</div><?php if(!$isModal && !empty($editing['imagen'])):?><p><img src="<?=ce($editing['imagen'])?>" alt="" style="max-width:180px;max-height:120px"></p><?php endif;?></div><div class="gallery-modal-footer patrona-modal-footer"><button class="cms-btn" type="submit">Guardar elemento</button></div></form></div></div>
<?php if ($isModal): ?>
<section class="cms-card gallery-admin">
 <div class="gallery-heading"><h2>Elementos registrados (<?=count($items)?>)</h2>
  <?php if ($items): ?>
  <div class="gallery-views" aria-label="Formato de visualización">
   <button type="button" class="active" data-modal-layout="list" aria-pressed="true">Listado</button>
   <button type="button" data-modal-layout="cards" aria-pressed="false">Tarjetas</button>
  </div>
  <?php endif; ?>
 </div>
 <?php if (!$items): ?><p>Agrega un ítem para comenzar.</p><?php endif; ?>
 <div id="modal-item-list" class="modal-item-list">
  <?php foreach ($items as $item):
   $itemId = (int)$item['id_item'];
   $media = patrona_modal_local_media((string)($item['imagen'] ?? ''));
   $youtubeId = patrona_modal_youtube_id((string)($item['video_youtube'] ?? ''));
   $video = patrona_modal_is_video($media);
   $visible = $item['visible'] === 'si';
  ?>
  <article class="modal-item">
   <span class="modal-item-order"><?=(int)$item['orden']?></span>
   <div class="modal-item-media">
    <?php if ($youtubeId): ?><span><i class="bi bi-youtube"></i> YouTube</span>
    <?php elseif ($video): ?><span><i class="bi bi-film"></i> MP4</span>
    <?php elseif ($media !== ''): ?><img class="gallery-thumb" src="<?=ce($media)?>" alt="">
    <?php else: ?><span><i class="bi bi-card-text"></i> Texto</span><?php endif; ?>
   </div>
   <strong><?=ce($item['titulo'] ?: 'Sin título')?></strong>
   <div class="modal-item-actions">
    <form method="post">
     <input type="hidden" name="csrf" value="<?=ce($_SESSION['patrona_csrf'])?>">
     <input type="hidden" name="accion" value="toggle">
     <input type="hidden" name="id_item" value="<?=$itemId?>">
     <button class="gallery-switch <?=$visible ? 'on' : ''?>" type="submit" aria-label="Cambiar visibilidad" aria-pressed="<?=$visible ? 'true' : 'false'?>" title="<?=$visible ? 'Visible' : 'Oculto'?>"><span></span></button>
    </form>
    <a class="gallery-icon" href="?id=<?=$sid?>&amp;tab=preview&amp;preview_item=<?=$itemId?>#modal-preview" aria-label="Vista previa"><i class="bi bi-eye" aria-hidden="true"></i></a>
    <button class="gallery-icon generic-edit" type="button" data-id="<?=$itemId?>" aria-label="Editar ítem"><i class="bi bi-pencil-square" aria-hidden="true"></i></button>
    <form method="post" onsubmit="return confirm('¿Eliminar este elemento?')">
     <input type="hidden" name="csrf" value="<?=ce($_SESSION['patrona_csrf'])?>">
     <input type="hidden" name="accion" value="delete">
     <input type="hidden" name="id_item" value="<?=$itemId?>">
     <button class="gallery-icon" type="submit" aria-label="Eliminar ítem"><i class="bi bi-trash" aria-hidden="true"></i></button>
    </form>
   </div>
  </article>
  <?php endforeach; ?>
 </div>
</section>
<script>
document.querySelectorAll('[data-modal-layout]').forEach(button => {
    button.addEventListener('click', () => {
        document.getElementById('modal-item-list').classList.toggle('is-cards', button.dataset.modalLayout === 'cards');
        document.querySelectorAll('[data-modal-layout]').forEach(option => {
            option.classList.toggle('active', option === button);
            option.setAttribute('aria-pressed', String(option === button));
        });
    });
});
</script>
<?php else: ?>
<section class="cms-card"><h2>Elementos registrados (<?=count($items)?>)</h2><?php if(!$items):?><p>No hay elementos registrados.</p><?php endif;?>
<?php foreach($items as $item):?><div class="cms-row"><?php if(!empty($item['imagen']) && (!$isModal || !patrona_modal_is_video($item['imagen']))):?><img src="<?=ce($item['imagen'])?>" alt=""><?php endif;?><div><strong><?=ce($item['titulo']??'')?></strong><p><?=ce($item['visible']??'')?> · Orden <?=(int)$item['orden']?></p><button type="button" class="gallery-icon generic-edit" data-id="<?=(int)$item['id_item']?>" title="Editar elemento"><i class="bi bi-pencil-square"></i> Editar</button></div><form method="post" onsubmit="return confirm('¿Eliminar este elemento?')"><input type="hidden" name="csrf" value="<?=ce($_SESSION['patrona_csrf'])?>"><input type="hidden" name="id_seccion" value="<?=(int)$sid?>"><input type="hidden" name="accion" value="delete"><input type="hidden" name="id_item" value="<?=(int)$item['id_item']?>"><button type="submit">Eliminar</button></form></div><?php endforeach;?></section>
<?php endif; ?>
<script type="application/json" id="generic-items"><?=json_encode(array_map(static fn(array $i):array=>['id'=>(int)$i['id_item'],'titulo'=>(string)($i['titulo']??''),'etiqueta'=>(string)($i['etiqueta']??''),'subtitulo'=>(string)($i['subtitulo']??''),'descripcion'=>(string)($i['descripcion']??''),'video_youtube'=>(string)($i['video_youtube']??''),'boton_1_texto'=>(string)($i['boton_1_texto']??''),'url'=>(string)($i['url']??''),'orden'=>(int)$i['orden'],'visible'=>(string)$i['visible'],'imagen'=>(string)($i['imagen']??'')],$items),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)?></script>
<script>
(()=>{const modal=document.getElementById('generic-modal');if(!modal)return;const form=document.getElementById('generic-form'),data=JSON.parse(document.getElementById('generic-items').textContent),input=form.querySelector('input[type=file]'),preview=document.getElementById('cms-image-preview');let blob=null;const field=n=>form.elements.namedItem(n);
const types=form.querySelectorAll('[name=tipo_modal]');
function syncType(){
 if(!types.length)return;
 const imageOnly=form.elements.tipo_modal.value==='solo_imagen';
 for(const el of form.querySelectorAll('[data-modal-informativo]')){
  el.hidden=imageOnly;
  for(const control of el.querySelectorAll('input, textarea'))control.disabled=imageOnly;
 }
 input.accept='image/jpeg,image/png,image/webp'+(imageOnly?'':',video/mp4');
 document.getElementById('modal-file-label').textContent=imageOnly?'Imagen del afiche (máx. 5 MB)':'Imagen lateral (5 MB) o video MP4 (50 MB)';
 document.getElementById('modal-item-title').hidden=imageOnly;
 const link=form.elements.url;
 link.placeholder=imageOnly?'https://…':'# para solo cerrar o https://…';
 document.getElementById('modal-link-field').classList.toggle('institutional-wide',imageOnly);
 document.getElementById('modal-link-label').textContent=imageOnly?'Enlace del afiche (opcional)':'Enlace del botón';
 document.getElementById('modal-link-help').textContent=imageOnly?'Vacío = imagen sin enlace.':'Con #, el botón cierra el modal y deja al visitante en la portada.';
}
types.forEach(type=>type.addEventListener('change',syncType));
function open(item){form.reset();
if(types.length){form.elements.tipo_modal.value=item?.etiqueta==='solo_imagen'?'solo_imagen':'informativo';syncType()}
field('id_item').value=item?.id||0;for(const n of ['titulo','etiqueta','subtitulo','descripcion','url','video_youtube','boton_1_texto']){const el=field(n);if(el)el.value=item?.[n]||''}const currentFile=document.getElementById('modal-current-file');
if(currentFile){
 currentFile.textContent=item?.imagen ? 'Archivo actual: '+item.imagen.split('/').pop() : '';
 document.getElementById('modal-remove-file').hidden=!item?.imagen;
 document.getElementById('modal-item-options').open=false;
}
field('orden').value=item?.orden||Math.max(0,...data.map(entry=>entry.orden))+1;field('visible').value=item?.visible||'si';if(preview){preview.src=item?.imagen||'';preview.classList.toggle('is-visible',!!item?.imagen)}document.getElementById('generic-modal-title').textContent=item?'Editar elemento':'Agregar elemento';modal.hidden=false;document.body.style.overflow='hidden';form.querySelector('input[name=titulo]').focus()}
function close(){modal.hidden=true;document.body.style.overflow='';if(blob){URL.revokeObjectURL(blob);blob=null}}
document.getElementById('generic-add').addEventListener('click',()=>open(null));document.querySelectorAll('.generic-edit').forEach(b=>b.addEventListener('click',()=>open(data.find(i=>i.id===Number(b.dataset.id)))));modal.querySelectorAll('.gallery-close').forEach(b=>b.addEventListener('click',close));if(input&&preview)input.addEventListener('change',()=>{if(blob)URL.revokeObjectURL(blob);blob=null;const f=input.files[0];if(!f)return;if(!['image/jpeg','image/png','image/webp'].includes(f.type)||f.size>5*1024*1024){alert('Solo JPG, PNG o WebP de máximo 5 MB');input.value='';return}blob=URL.createObjectURL(f);preview.src=blob;preview.classList.add('is-visible')});const selected=new URLSearchParams(location.search).get('item');if(selected)open(data.find(i=>i.id===Number(selected))||null);
})();
</script><?php endif;?><?php endif; ?><?php endif; ?></main></div></div><script>
(function(){
 const input=document.getElementById('cms-image-input'),preview=document.getElementById('cms-image-preview');
 if(!input||!preview)return;
 let url=null;
 input.addEventListener('change',function(){
  if(url){URL.revokeObjectURL(url);url=null;}
  const file=input.files&&input.files[0];
  if(!file){preview.removeAttribute('src');preview.classList.remove('is-visible');return;}
  if(!['image/jpeg','image/png','image/webp'].includes(file.type)||file.size>5*1024*1024){alert('Selecciona una imagen JPG, PNG o WebP de máximo 5 MB.');input.value='';preview.classList.remove('is-visible');return;}
  url=URL.createObjectURL(file);preview.src=url;preview.classList.add('is-visible');
 });
})();
</script><script>
(()=>{const modal=document.getElementById('gallery-modal');if(!modal)return;const items=JSON.parse(document.getElementById('gallery-data').textContent),form=document.getElementById('gallery-form'),file=document.getElementById('gallery-image-input'),preview=document.getElementById('gallery-image-preview'),visible=document.getElementById('gallery-visible');let blob=null;function open(item){form.reset();document.getElementById('gallery-item-id').value=item?.id||0;document.getElementById('gallery-title').value=item?.titulo||'';document.getElementById('gallery-url').value=item?.url||'';document.getElementById('gallery-order').value=item?.orden||items.length+1;visible.checked=!item||item.visible==='si';document.getElementById('gallery-visible-value').value=visible.checked?'si':'no';document.getElementById('gallery-modal-title').textContent=item?'Editar imagen':'Agregar imagen';preview.src=item?.imagen||'';preview.hidden=!item?.imagen;modal.hidden=false;document.body.style.overflow='hidden';document.getElementById('gallery-title').focus()}function close(){modal.hidden=true;document.body.style.overflow='';if(blob){URL.revokeObjectURL(blob);blob=null}}document.getElementById('gallery-add').onclick=()=>open(null);document.querySelectorAll('.gallery-edit').forEach(b=>b.onclick=()=>open(items.find(i=>i.id===Number(b.dataset.id))));document.querySelectorAll('.gallery-close').forEach(b=>b.onclick=close);visible.onchange=()=>document.getElementById('gallery-visible-value').value=visible.checked?'si':'no';file.onchange=()=>{if(blob)URL.revokeObjectURL(blob);blob=null;const f=file.files[0];if(!f)return;if(!['image/jpeg','image/png','image/webp'].includes(f.type)||f.size>5*1024*1024){alert('La imagen debe ser JPG, PNG o WebP y pesar como máximo 5 MB.');file.value='';return}blob=URL.createObjectURL(f);preview.src=blob;preview.hidden=false};form.onsubmit=e=>{if(!document.getElementById('gallery-item-id').value||document.getElementById('gallery-item-id').value==='0'){if(!file.files.length){e.preventDefault();alert('Selecciona una imagen.')}}};document.querySelectorAll('[data-gallery-view]').forEach(b=>b.onclick=()=>{document.querySelectorAll('[data-gallery-view]').forEach(x=>x.classList.toggle('active',x===b));const cards=b.dataset.galleryView==='cards';document.getElementById('gallery-list').hidden=cards;document.getElementById('gallery-cards').hidden=!cards});const tbody=document.getElementById('gallery-sortable');let dragging=null;tbody.querySelectorAll('tr').forEach(tr=>{tr.addEventListener('dragstart',e=>{dragging=tr;tr.classList.add('dragging');e.dataTransfer.effectAllowed='move'});tr.addEventListener('dragover',e=>{e.preventDefault();if(dragging&&dragging!==tr){tr.classList.add('dragover');const box=tr.getBoundingClientRect();tbody.insertBefore(dragging,e.clientY<box.top+box.height/2?tr:tr.nextSibling)}});tr.addEventListener('dragleave',()=>tr.classList.remove('dragover'));tr.addEventListener('dragend',()=>{tr.classList.remove('dragging');tbody.querySelectorAll('tr').forEach(x=>x.classList.remove('dragover'));dragging=null});tr.addEventListener('drop',e=>{e.preventDefault();tbody.querySelectorAll('tr').forEach((x,i)=>x.querySelector('.gallery-order').textContent=i+1);document.getElementById('gallery-order-ids').value=[...tbody.querySelectorAll('tr')].map(x=>x.dataset.id).join(',');document.getElementById('gallery-reorder-form').submit()})})})();
</script><script>
(()=>{const modal=document.getElementById('stat-modal');if(!modal)return;const data=JSON.parse(document.getElementById('stat-data').textContent),form=document.getElementById('stat-form'),file=document.getElementById('stat-image'),preview=document.getElementById('stat-preview'),visible=document.getElementById('stat-visible');let blob=null;function open(item){form.reset();document.getElementById('stat-id').value=item?.id||0;document.getElementById('stat-number').value=item?.etiqueta||'';document.getElementById('stat-title').value=item?.titulo||'';document.getElementById('stat-description').value=item?.descripcion||'';document.getElementById('stat-order').value=item?.orden||data.length+1;visible.checked=!item||item.visible==='si';document.getElementById('stat-visible-value').value=visible.checked?'si':'no';document.getElementById('stat-modal-title').textContent=item?'Editar indicador':'Agregar indicador';preview.src=item?.imagen||'';preview.hidden=!item?.imagen;modal.hidden=false;document.body.style.overflow='hidden';document.getElementById('stat-number').focus()}function close(){modal.hidden=true;document.body.style.overflow='';if(blob){URL.revokeObjectURL(blob);blob=null}}document.getElementById('stat-add').onclick=()=>open(null);document.querySelectorAll('.stat-edit').forEach(b=>b.onclick=()=>open(data.find(x=>x.id===Number(b.dataset.id))));modal.querySelectorAll('.gallery-close').forEach(b=>b.onclick=close);visible.onchange=()=>document.getElementById('stat-visible-value').value=visible.checked?'si':'no';file.onchange=()=>{if(blob)URL.revokeObjectURL(blob);blob=null;const f=file.files[0];if(!f)return;if(!['image/jpeg','image/png','image/webp'].includes(f.type)||f.size>5*1024*1024){alert('Selecciona una imagen JPG, PNG o WebP de hasta 5 MB');file.value='';return}blob=URL.createObjectURL(f);preview.src=blob;preview.hidden=false};form.onsubmit=e=>{if(!document.getElementById('stat-number').value.trim()||!document.getElementById('stat-title').value.trim()){e.preventDefault();alert('Indica un número y un título.');return}if(document.getElementById('stat-id').value==='0'&&!file.files.length){e.preventDefault();alert('Selecciona una imagen.')}};document.querySelectorAll('[data-stat-view]').forEach(b=>b.onclick=()=>{document.querySelectorAll('[data-stat-view]').forEach(x=>x.classList.toggle('active',x===b));const cards=b.dataset.statView==='cards';document.getElementById('stat-list').hidden=cards;document.getElementById('stat-cards').hidden=!cards});const tbody=document.getElementById('stat-sortable');let dragging=null;tbody.querySelectorAll('tr').forEach(tr=>{tr.addEventListener('dragstart',e=>{dragging=tr;tr.classList.add('dragging');e.dataTransfer.effectAllowed='move'});tr.addEventListener('dragover',e=>{e.preventDefault();if(dragging&&dragging!==tr){tr.classList.add('dragover');const b=tr.getBoundingClientRect();tbody.insertBefore(dragging,e.clientY<b.top+b.height/2?tr:tr.nextSibling)}});tr.addEventListener('dragend',()=>{tr.classList.remove('dragging');dragging=null});tr.addEventListener('drop',e=>{e.preventDefault();tbody.querySelectorAll('tr').forEach((x,i)=>x.querySelector('.gallery-order').textContent=i+1);document.getElementById('stat-order-ids').value=[...tbody.querySelectorAll('tr')].map(x=>x.dataset.id).join(',');document.getElementById('stat-reorder-form').submit()})})})();
</script></body></html>
