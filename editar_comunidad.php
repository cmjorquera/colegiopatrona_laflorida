<?php
require_once __DIR__.'/includes/rich_editor.php';
declare(strict_types=1);
$sessionDir='/home3/crist668/php_sessions_patrona';
if(!is_dir($sessionDir)||!is_writable($sessionDir)){http_response_code(503);exit('Sesiones no disponibles');}
session_save_path($sessionDir);session_start();
if(empty($_SESSION['patrona_admin_id'])){header('Location: admin.php');exit;}
if(empty($_SESSION['patrona_csrf']))$_SESSION['patrona_csrf']=bin2hex(random_bytes(32));
function fam_e($v):string{return htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
$error='';$notice=$_SESSION['familias_notice']??'';unset($_SESSION['familias_notice']);
$items=[];$config=[];$sid=0;
try{
 $cfg=require __DIR__.'/class/conexion_local.php';
 if(!is_array($cfg)||($cfg['db']??'')!=='crist668_colegio_patrona_laflorida')throw new RuntimeException('Base no autorizada');
 mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
 $db=new mysqli($cfg['host'],$cfg['user'],$cfg['pass'],$cfg['db']);$db->set_charset('utf8mb4');
 $q=$db->prepare("SELECT * FROM seccion WHERE id_institucion=2 AND nombre_interno='tab_comunidad_home' LIMIT 1");$q->execute();$section=$q->get_result()->fetch_assoc();
 if(!$section)throw new RuntimeException('No se encontró tab_comunidad_home');
 $sid=(int)$section['id_seccion'];
 if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!hash_equals((string)$_SESSION['patrona_csrf'],(string)($_POST['csrf']??'')))throw new RuntimeException('Sesión vencida; recarga la página.');
  if(!in_array((string)$section['editable'],['si','1'],true))throw new RuntimeException('Contenedor no editable.');
  $action=(string)($_POST['accion']??'');
  if($action==='save'){
   $id=max(0,(int)($_POST['id_item']??0));
   $title=trim((string)($_POST['titulo']??''));$desc=patrona_rich_clean((string)($_POST['descripcion']??''));$url=trim((string)($_POST['url']??''));
   if($title===''||mb_strlen($title)>180||mb_strlen($desc)>1200)throw new RuntimeException('Revisa el título y la descripción.');
   if($url!==''&&!filter_var($url,FILTER_VALIDATE_URL))throw new RuntimeException('Enlace inválido.');
   if($url!==''&&!in_array(strtolower((string)parse_url($url,PHP_URL_SCHEME)),['https','http'],true))throw new RuntimeException('Solo enlaces HTTP o HTTPS.');
   $img='';
   if($id){$q=$db->prepare("SELECT imagen FROM seccion_item WHERE id_item=? AND id_seccion=? AND etiqueta='familias'");$q->bind_param('ii',$id,$sid);$q->execute();$old=$q->get_result()->fetch_assoc();if(!$old)throw new RuntimeException('Elemento no encontrado');$img=(string)$old['imagen'];}
   if(isset($_FILES['imagen'])&&($_FILES['imagen']['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_NO_FILE){
    $f=$_FILES['imagen'];if($f['error']!==UPLOAD_ERR_OK||$f['size']>5*1024*1024)throw new RuntimeException('Imagen inválida o superior a 5 MB.');
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);$ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$mime]??'';
    if($ext===''||!getimagesize($f['tmp_name']))throw new RuntimeException('Utiliza JPG, PNG o WebP.');
    $dir=__DIR__.'/assets/uploads/secciones/comunidad';if(!is_dir($dir)&&!mkdir($dir,0755,true)&&!is_dir($dir))throw new RuntimeException('No se pudo crear carpeta de imágenes.');
    $filename=bin2hex(random_bytes(16)).'.'.$ext;
    if(!move_uploaded_file($f['tmp_name'],$dir.'/'.$filename))throw new RuntimeException('Error al guardar imagen.');
    $img='assets/uploads/secciones/comunidad/'.$filename;
   }
   $visible=($_POST['visible']??'si')==='no'?'no':'si';$orden=max(1,(int)($_POST['orden']??1));
   if($id){$q=$db->prepare("UPDATE seccion_item SET titulo=?,descripcion=?,imagen=?,url=?,visible=?,orden=? WHERE id_item=? AND id_seccion=? AND etiqueta='familias'");$q->bind_param('sssssiii',$title,$desc,$img,$url,$visible,$orden,$id,$sid);}
   else{$q=$db->prepare("INSERT INTO seccion_item(id_seccion,etiqueta,titulo,descripcion,imagen,url,visible,orden) VALUES(?,'familias',?,?,?,?,?,?)");$q->bind_param('isssssi',$sid,$title,$desc,$img,$url,$visible,$orden);}
   $q->execute();
  }elseif($action==='delete'){
   $id=(int)($_POST['id_item']??0);$q=$db->prepare("DELETE FROM seccion_item WHERE id_item=? AND id_seccion=? AND etiqueta='familias'");$q->bind_param('ii',$id,$sid);$q->execute();
  }else throw new RuntimeException('Acción no válida');
  $_SESSION['familias_notice']='Cambios guardados correctamente.';header('Location: editar_comunidad.php');exit;
 }
 $q=$db->prepare("SELECT * FROM seccion_item WHERE id_seccion=? AND etiqueta='familias' ORDER BY orden,id_item");$q->bind_param('i',$sid);$q->execute();$items=$q->get_result()->fetch_all(MYSQLI_ASSOC);
}catch(Throwable $e){error_log('Editor familias: '.$e->getMessage());$error=$e->getMessage();}
$editId=(int)($_GET['item']??0);$editing=null;foreach($items as $i)if((int)$i['id_item']===$editId)$editing=$i;
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Comunidad escolar · Familias</title><link rel="stylesheet" href="assets/admin/css/patrona-panel.css"><style>
.family-shell{display:grid;grid-template-columns:235px 1fr;min-height:100vh;background:#f3f6fb}.family-side{background:#102b50;color:white;padding:25px 18px}.family-side a{display:block;color:#d8e8ff;text-decoration:none;padding:12px;border-radius:8px}.family-side a:hover{background:#214a7b}.family-main{padding:32px;max-width:1100px;width:100%;margin:auto}.family-card{background:white;border:1px solid #dce4ee;border-radius:12px;padding:24px;margin:22px 0;box-shadow:0 5px 20px #102d5208}.family-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}.family-field{display:flex;flex-direction:column;gap:7px;font-size:13px;font-weight:600}.family-field input,.family-field select,.family-field textarea{padding:11px;border:1px solid #cbd5e1;border-radius:8px;font:inherit;font-weight:400}.family-btn{background:#1765b8;color:white;border:0;border-radius:7px;padding:11px 17px;cursor:pointer;text-decoration:none;display:inline-block}.family-row{display:flex;align-items:center;gap:16px;border-bottom:1px solid #e5eaf0;padding:14px 0}.family-row img{width:100px;height:75px;object-fit:cover}.family-row form{margin-left:auto}@media(max-width:700px){.family-shell{grid-template-columns:1fr}.family-side{padding:12px}.family-main{padding:16px}.family-grid{grid-template-columns:1fr}.family-row{flex-wrap:wrap}}
</style><?php patrona_rich_assets(); ?></head><body><div class="family-shell"><aside class="family-side"><h3>Patrona de Lourdes</h3><p>La Florida · Panel CMS</p><a href="admin.php?panel=contenedores">← Contenedores</a><a href="index.php#comunidad-escolar" target="_blank" rel="noopener">Ver sitio público</a><hr><strong>Comunidad escolar</strong><a href="editar_comunidad.php" aria-current="page">Familias</a><p style="font-size:12px;color:#b5c9e5">Estudiantes y Actividades se incorporarán después.</p></aside><main class="family-main"><small>GESTIÓN DEL SITIO / COMUNIDAD ESCOLAR</small><h1>Familias</h1><p>Administra las tarjetas de la pestaña Familias. No se publicará hasta que agregues contenido visible.</p>
<?php if($error):?><p style="color:#b42318"><?=fam_e($error)?></p><?php endif;?><?php if($notice):?><p style="color:#187344"><?=fam_e($notice)?></p><?php endif;?>
<?php if($sid):?><section class="family-card"><h2><?= $editing?'Editar contenido':'Agregar contenido para Familias' ?></h2><form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?=fam_e($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="save"><input type="hidden" name="id_item" value="<?=(int)($editing['id_item']??0)?>"><div class="family-grid">
<label class="family-field">Título<input name="titulo" required maxlength="180" value="<?=fam_e($editing['titulo']??'')?>"></label>
<label class="family-field">Imagen JPG, PNG o WebP (máx. 5 MB)<input type="file" name="imagen" accept="image/jpeg,image/png,image/webp"></label>
<label class="family-field">Enlace opcional<input name="url" type="url" placeholder="https://..." value="<?=fam_e($editing['url']??'')?>"></label>
<label class="family-field">Orden<input type="number" name="orden" min="1" value="<?=(int)($editing['orden']??count($items)+1)?>"></label>
<label class="family-field">Visibilidad<select name="visible"><option value="si" <?=($editing['visible']??'si')==='si'?'selected':''?>>Visible</option><option value="no" <?=($editing['visible']??'si')==='no'?'selected':''?>>Oculto</option></select></label>
<label class="family-field" style="grid-column:1/-1">Descripción<textarea name="descripcion" maxlength="1200" rows="4" data-rich-editor><?=fam_e($editing['descripcion']??'')?></textarea></label></div>
<?php if(!empty($editing['imagen'])):?><p>Imagen actual: <img src="<?=fam_e($editing['imagen'])?>" alt="" style="max-width:170px;max-height:110px"></p><?php endif;?>
<p><button class="family-btn" type="submit">Guardar contenido</button> <?php if($editing):?><a href="editar_comunidad.php">Cancelar</a><?php endif;?></p></form></section>
<section class="family-card"><h2>Contenidos de Familias (<?=count($items)?>)</h2><?php if(!$items):?><p>Aún no hay contenido registrado.</p><?php endif;?>
<?php foreach($items as $i):?><div class="family-row"><?php if($i['imagen']):?><img src="<?=fam_e($i['imagen'])?>" alt=""><?php endif;?><div><strong><?=fam_e($i['titulo'])?></strong><p><?=fam_e($i['visible'])?> · Orden <?=(int)$i['orden']?></p><a href="editar_comunidad.php?item=<?=(int)$i['id_item']?>">Editar</a></div><form method="post" onsubmit="return confirm('¿Eliminar este contenido?')"><input type="hidden" name="csrf" value="<?=fam_e($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="delete"><input type="hidden" name="id_item" value="<?=(int)$i['id_item']?>"><button type="submit">Eliminar</button></form></div><?php endforeach;?></section><?php endif;?></main></div></body></html>
