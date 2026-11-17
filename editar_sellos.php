<?php
declare(strict_types=1);
$sessionPath='/home3/crist668/php_sessions_patrona';
if (!is_dir($sessionPath)||!is_writable($sessionPath)) {http_response_code(503);exit('Sesiones no disponibles');}
session_save_path($sessionPath);session_start();
if(empty($_SESSION['patrona_admin_id'])){header('Location: admin.php');exit;}
header('Cache-Control: no-store');
function se(string $s):string{return htmlspecialchars($s,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
function sealDb():mysqli{
 $cfg=require __DIR__.'/class/conexion_local.php';
 if(!is_array($cfg)||($cfg['db']??'')!=='crist668_colegio_patrona_laflorida')throw new RuntimeException('Base no autorizada');
 mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
 $db=new mysqli($cfg['host'],$cfg['user'],$cfg['pass'],$cfg['db']);$db->set_charset('utf8mb4');return $db;
}
if(empty($_SESSION['patrona_csrf']))$_SESSION['patrona_csrf']=bin2hex(random_bytes(32));
$error='';$notice=(string)($_SESSION['seals_notice']??'');unset($_SESSION['seals_notice']);
$items=[];$configs=[];$sid=0;
try{
 $db=sealDb();
 $q=$db->prepare("SELECT * FROM seccion WHERE id_institucion=2 AND nombre_interno='sellos_home' LIMIT 1");
 $q->execute();$section=$q->get_result()->fetch_assoc();
 if(!$section)throw new RuntimeException('No se encontró el contenedor sellos_home.');
 $sid=(int)$section['id_seccion'];
 if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!hash_equals((string)$_SESSION['patrona_csrf'],(string)($_POST['csrf']??'')))throw new RuntimeException('Token inválido.');
  if(!in_array((string)$section['editable'],['si','1'],true))throw new RuntimeException('Contenedor no editable.');
  $action=(string)($_POST['accion']??'');
  if($action==='config'){
   $db->begin_transaction();
   try{
    foreach(['titulo_bloque','subtitulo_bloque'] as $key){
     $value=trim((string)($_POST[$key]??''));if(mb_strlen($value)>180)throw new RuntimeException('Texto demasiado largo');
     $q=$db->prepare('DELETE FROM seccion_config WHERE id_seccion=? AND clave=?');$q->bind_param('is',$sid,$key);$q->execute();
     $q=$db->prepare('INSERT INTO seccion_config(id_seccion,clave,valor) VALUES(?,?,?)');$q->bind_param('iss',$sid,$key,$value);$q->execute();
    }
    $db->commit();
   }catch(Throwable $e){$db->rollback();throw $e;}
  }elseif($action==='save'){
   $id=max(0,(int)($_POST['id_item']??0));$titulo=trim((string)($_POST['titulo']??''));$desc=trim((string)($_POST['descripcion']??''));$url=trim((string)($_POST['url']??''));
   if(mb_strlen($titulo)>180||mb_strlen($desc)>600)throw new RuntimeException('Título o descripción demasiado largos.');
   if($url!==''&&!preg_match('~^https?://[^\\s]+$~i',$url))throw new RuntimeException('El enlace debe comenzar por https:// o http://');
   $old=null;
   if($id){$q=$db->prepare('SELECT imagen FROM seccion_item WHERE id_item=? AND id_seccion=?');$q->bind_param('ii',$id,$sid);$q->execute();$old=$q->get_result()->fetch_assoc();if(!$old)throw new RuntimeException('Sello no encontrado');}
   $image=(string)($old['imagen']??'');
   if(isset($_FILES['imagen'])&&($_FILES['imagen']['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_NO_FILE){
    $file=$_FILES['imagen'];if($file['error']!==UPLOAD_ERR_OK)throw new RuntimeException('No fue posible subir la imagen.');
    if($file['size']>5*1024*1024)throw new RuntimeException('Máximo 5 MB por imagen.');
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$mime]??null;
    if(!$ext||!getimagesize($file['tmp_name']))throw new RuntimeException('Solo JPG, PNG o WebP válidos.');
    $folder=__DIR__.'/assets/uploads/secciones/sellos_home';
    if(!is_dir($folder)&&!mkdir($folder,0755,true)&&!is_dir($folder))throw new RuntimeException('No se pudo crear directorio de imágenes.');
    $filename=bin2hex(random_bytes(16)).'.'.$ext;
    if(!move_uploaded_file($file['tmp_name'],$folder.'/'.$filename))throw new RuntimeException('No se pudo guardar la imagen.');
    $image='assets/uploads/secciones/sellos_home/'.$filename;
   }
   if($image==='')throw new RuntimeException('Debes subir una imagen para el sello.');
   $visible=($_POST['visible']??'si')==='no'?'no':'si';$orden=max(1,(int)($_POST['orden']??1));
   if($id){
    $q=$db->prepare("UPDATE seccion_item SET etiqueta='sello',titulo=?,descripcion=?,imagen=?,url=?,visible=?,orden=? WHERE id_item=? AND id_seccion=?");
    $q->bind_param('sssssiii',$titulo,$desc,$image,$url,$visible,$orden,$id,$sid);
   }else{
    $q=$db->prepare("INSERT INTO seccion_item(id_seccion,etiqueta,titulo,descripcion,imagen,url,visible,orden) VALUES(?,'sello',?,?,?,?,?,?)");
    $q->bind_param('isssssi',$sid,$titulo,$desc,$image,$url,$visible,$orden);
   }$q->execute();
  }elseif($action==='delete'){
   $id=max(0,(int)($_POST['id_item']??0));
   $q=$db->prepare('DELETE FROM seccion_item WHERE id_item=? AND id_seccion=?');$q->bind_param('ii',$id,$sid);$q->execute();
  }else throw new RuntimeException('Acción no reconocida');
  $_SESSION['seals_notice']='Cambios guardados correctamente.';
  header('Location: editar_sellos.php');exit;
 }
 $q=$db->prepare('SELECT * FROM seccion_item WHERE id_seccion=? ORDER BY orden,id_item');$q->bind_param('i',$sid);$q->execute();$items=$q->get_result()->fetch_all(MYSQLI_ASSOC);
 $q=$db->prepare('SELECT clave,valor FROM seccion_config WHERE id_seccion=?');$q->bind_param('i',$sid);$q->execute();
 foreach($q->get_result()->fetch_all(MYSQLI_ASSOC) as $r)$configs[$r['clave']]=$r['valor'];
}catch(Throwable $e){error_log('Patrona editor sellos: '.$e->getMessage());$error=$e->getMessage();}
$editing=null;$editId=(int)($_GET['item']??0);foreach($items as $i)if((int)$i['id_item']===$editId)$editing=$i;
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Sellos del colegio | CMS</title><link rel="stylesheet" href="assets/admin/css/patrona-panel.css"><style>
body{background:#f3f6fb;font-family:Arial,sans-serif;color:#1a2e4a}.container{max-width:1100px;margin:30px auto;padding:0 20px}.card{background:#fff;border:1px solid #dfe6f0;border-radius:12px;padding:24px;margin:20px 0}.grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.field{display:flex;flex-direction:column;gap:6px}.field input,.field textarea,.field select{padding:11px;border:1px solid #cbd5e1;border-radius:7px}.btn{background:#003b6f;color:#fff;border:0;border-radius:7px;padding:11px 17px;cursor:pointer;text-decoration:none;display:inline-block}.muted{color:#65758a}.item{display:flex;gap:15px;align-items:center;border-bottom:1px solid #e3e8f0;padding:14px 0}.item img{width:95px;height:95px;object-fit:contain}.actions{margin-left:auto;display:flex;gap:8px}.alert{padding:12px;background:#eaf6ef;border-radius:8px}@media(max-width:650px){.grid{grid-template-columns:1fr}.item{flex-wrap:wrap}.actions{margin-left:0}}
</style></head><body><div class="container"><p><a href="admin.php?panel=contenedores">← Volver a contenedores</a> · <a href="index.php#sellos-home" target="_blank">Ver sitio público</a></p>
<h1>Sellos del colegio</h1><p class="muted">Mismo esquema de Tabancura: seccion, seccion_config y seccion_item. Los sellos sin imagen no se publican.</p>
<?php if($error):?><div class="alert" style="background:#ffeded"><?=se($error)?></div><?php endif;?>
<?php if($notice):?><div class="alert"><?=se($notice)?></div><?php endif;?>
<?php if($sid):?>
<div class="card"><h2>Configuración del bloque</h2><form method="post"><input type="hidden" name="csrf" value="<?=se($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="config"><div class="grid">
<label class="field">Título del bloque<input name="titulo_bloque" maxlength="180" value="<?=se((string)($configs['titulo_bloque']??'Sellos del colegio'))?>"></label>
<label class="field">Texto pequeño sobre el título<input name="subtitulo_bloque" maxlength="180" value="<?=se((string)($configs['subtitulo_bloque']??''))?>"></label>
</div><p><button class="btn">Guardar configuración</button></p></form></div>
<div class="card"><h2><?= $editing?'Editar sello':'Agregar sello' ?></h2><form method="post" enctype="multipart/form-data">
<input type="hidden" name="csrf" value="<?=se($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="save"><input type="hidden" name="id_item" value="<?= (int)($editing['id_item']??0) ?>">
<div class="grid">
<label class="field">Imagen del sello (JPG, PNG o WebP; máximo 5 MB)<input type="file" name="imagen" accept="image/jpeg,image/png,image/webp" <?= $editing?'':'required' ?>></label>
<label class="field">Nombre (opcional, accesibilidad)<input name="titulo" maxlength="180" value="<?=se((string)($editing['titulo']??''))?>"></label>
<label class="field">Enlace externo (opcional)<input type="url" name="url" value="<?=se((string)($editing['url']??''))?>" placeholder="https://..."></label>
<label class="field">Orden<input type="number" name="orden" min="1" value="<?= (int)($editing['orden']??count($items)+1) ?>"></label>
<label class="field">Visibilidad<select name="visible"><option value="si" <?=($editing['visible']??'si')==='si'?'selected':''?>>Visible</option><option value="no" <?=($editing['visible']??'si')==='no'?'selected':''?>>Oculto</option></select></label>
<label class="field" style="grid-column:1/-1">Descripción del reverso (máximo 600 caracteres)<textarea name="descripcion" maxlength="600" rows="4"><?=se((string)($editing['descripcion']??''))?></textarea></label>
</div>
<?php if(!empty($editing['imagen'])):?><p>Imagen actual: <img src="<?=se((string)$editing['imagen'])?>" alt="" style="height:90px;max-width:160px;object-fit:contain"></p><?php endif;?>
<p><button class="btn">Guardar sello</button> <?php if($editing):?><a href="editar_sellos.php">Cancelar edición</a><?php endif;?></p></form></div>
<div class="card"><h2>Sellos registrados (<?=count($items)?>)</h2>
<?php if(!$items):?><p class="muted">No hay sellos. Agrega el primero para mostrar el bloque en la página pública.</p><?php endif;?>
<?php foreach($items as $i):?><div class="item">
<?php if(!empty($i['imagen'])):?><img src="<?=se((string)$i['imagen'])?>" alt=""><?php endif;?>
<div><strong><?=se((string)($i['titulo']?:'Sello sin nombre'))?></strong><br><small><?=se((string)$i['visible'])?> · Orden <?= (int)$i['orden'] ?></small></div>
<div class="actions"><a class="btn" href="editar_sellos.php?item=<?=(int)$i['id_item']?>">Editar</a><form method="post" onsubmit="return confirm('¿Eliminar este sello?')"><input type="hidden" name="csrf" value="<?=se($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="delete"><input type="hidden" name="id_item" value="<?=(int)$i['id_item']?>"><button class="btn" style="background:#a72828">Eliminar</button></form></div></div><?php endforeach;?>
</div><?php endif;?></div></body></html>
