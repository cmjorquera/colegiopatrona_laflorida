<?php
declare(strict_types=1);
$sessionDir='/home3/crist668/php_sessions_patrona';
if(!is_dir($sessionDir)||!is_writable($sessionDir)){http_response_code(503);exit('Sesiones no disponibles');}
session_save_path($sessionDir);session_start();
if(empty($_SESSION['patrona_admin_id'])){header('Location: admin.php');exit;}
if(empty($_SESSION['patrona_csrf']))$_SESSION['patrona_csrf']=bin2hex(random_bytes(32));
function news_e($s):string{return htmlspecialchars((string)$s,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
$error='';$notice=(string)($_SESSION['news_notice']??'');unset($_SESSION['news_notice']);$items=[];$sid=0;
try{
 $cfg=require __DIR__.'/class/conexion_local.php';
 if(!is_array($cfg)||($cfg['db']??'')!=='crist668_colegio_patrona_laflorida')throw new RuntimeException('Base no autorizada');
 mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
 $db=new mysqli($cfg['host'],$cfg['user'],$cfg['pass'],$cfg['db']);$db->set_charset('utf8mb4');
 $q=$db->prepare("SELECT * FROM seccion WHERE id_institucion=2 AND nombre_interno='noticias_home' LIMIT 1");$q->execute();$section=$q->get_result()->fetch_assoc();
 if(!$section)throw new RuntimeException('No se encontró noticias_home');$sid=(int)$section['id_seccion'];
 if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!hash_equals((string)$_SESSION['patrona_csrf'],(string)($_POST['csrf']??'')))throw new RuntimeException('Token de sesión inválido.');
  if(!in_array((string)$section['editable'],['si','1'],true))throw new RuntimeException('Contenedor no editable.');
  $action=(string)($_POST['accion']??'');$id=max(0,(int)($_POST['id_item']??0));
  if($action==='save'){
   $title=trim((string)($_POST['titulo']??''));$desc=trim((string)($_POST['descripcion']??''));$url=trim((string)($_POST['url']??''));
   $date=trim((string)($_POST['fecha_publicacion']??''));$visible=($_POST['visible']??'si')==='no'?'no':'si';$orden=max(1,(int)($_POST['orden']??1));
   if($title===''||mb_strlen($title)>220||mb_strlen($desc)>5000)throw new RuntimeException('Revisa título y descripción.');
   if($url!==''&&(!filter_var($url,FILTER_VALIDATE_URL)||!in_array(strtolower((string)parse_url($url,PHP_URL_SCHEME)),['http','https'],true)))throw new RuntimeException('Enlace inválido: usa http o https.');
   if($date!==''&&(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)||!strtotime($date)))throw new RuntimeException('Fecha inválida.');
   $date=$date===''?null:$date;
   $image='';
   if($id){$q=$db->prepare('SELECT imagen FROM seccion_item WHERE id_item=? AND id_seccion=?');$q->bind_param('ii',$id,$sid);$q->execute();$old=$q->get_result()->fetch_assoc();if(!$old)throw new RuntimeException('Noticia no encontrada');$image=(string)($old['imagen']??'');}
   if(isset($_FILES['imagen'])&&($_FILES['imagen']['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_NO_FILE){
    $f=$_FILES['imagen'];if($f['error']!==UPLOAD_ERR_OK||$f['size']>5*1024*1024)throw new RuntimeException('La imagen debe pesar menos de 5 MB.');
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);$ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$mime]??'';
    if($ext===''||!getimagesize($f['tmp_name']))throw new RuntimeException('Utiliza una imagen JPG, PNG o WebP.');
    $dir=__DIR__.'/assets/uploads/secciones/noticias_home';
    if(!is_dir($dir)&&!mkdir($dir,0755,true)&&!is_dir($dir))throw new RuntimeException('No se pudo crear directorio de imágenes.');
    $filename=bin2hex(random_bytes(16)).'.'.$ext;
    if(!move_uploaded_file($f['tmp_name'],$dir.'/'.$filename))throw new RuntimeException('No se pudo subir la imagen.');
    $image='assets/uploads/secciones/noticias_home/'.$filename;
   }
   if($id){
    $q=$db->prepare('UPDATE seccion_item SET titulo=?,descripcion=?,imagen=?,url=?,fecha_publicacion=?,visible=?,orden=? WHERE id_item=? AND id_seccion=?');
    $q->bind_param('ssssssiii',$title,$desc,$image,$url,$date,$visible,$orden,$id,$sid);
   }else{
    $q=$db->prepare('INSERT INTO seccion_item(id_seccion,titulo,descripcion,imagen,url,fecha_publicacion,visible,orden) VALUES(?,?,?,?,?,?,?,?)');
    $q->bind_param('issssssi',$sid,$title,$desc,$image,$url,$date,$visible,$orden);
   }$q->execute();
  }elseif($action==='delete'){
   $q=$db->prepare('DELETE FROM seccion_item WHERE id_item=? AND id_seccion=?');$q->bind_param('ii',$id,$sid);$q->execute();
  }else throw new RuntimeException('Acción desconocida');
  $_SESSION['news_notice']='Noticia guardada correctamente.';header('Location: editar_noticias.php');exit;
 }
 $q=$db->prepare('SELECT * FROM seccion_item WHERE id_seccion=? ORDER BY orden,id_item');$q->bind_param('i',$sid);$q->execute();$items=$q->get_result()->fetch_all(MYSQLI_ASSOC);
}catch(Throwable $e){error_log('Editor Noticias: '.$e->getMessage());$error=$e->getMessage();}
$editing=null;foreach($items as $i)if((int)$i['id_item']===(int)($_GET['item']??0))$editing=$i;
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Noticias | Panel CMS</title><link rel="stylesheet" href="assets/admin/css/patrona-panel.css"><style>
.news-layout{display:grid;grid-template-columns:240px minmax(0,1fr);min-height:100vh;background:#f4f7fb}.news-side{background:#102b50;color:white;padding:24px 18px}.news-side a{display:block;color:#d8e8ff;padding:12px;text-decoration:none;border-radius:7px}.news-side a:hover{background:#214a7b}.news-main{max-width:1150px;width:100%;margin:auto;padding:32px}.news-card{background:#fff;border:1px solid #dfe6ef;border-radius:12px;padding:24px;margin:22px 0;box-shadow:0 5px 18px #102d520a}.news-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}.news-field{display:flex;flex-direction:column;gap:7px;font-weight:600;font-size:13px}.news-field input,.news-field select,.news-field textarea{padding:11px;border:1px solid #cbd5e1;border-radius:8px;font:inherit;font-weight:400}.news-btn{background:#1765b8;color:white;border:0;border-radius:7px;padding:10px 16px;cursor:pointer;text-decoration:none}.news-row{display:flex;gap:15px;align-items:center;padding:14px 0;border-bottom:1px solid #e4eaf2}.news-row img{width:100px;height:80px;object-fit:cover}.news-row form{margin-left:auto}@media(max-width:700px){.news-layout{grid-template-columns:1fr}.news-main{padding:16px}.news-grid{grid-template-columns:1fr}.news-row{flex-wrap:wrap}}
</style></head><body><div class="news-layout"><aside class="news-side"><h3>Patrona de Lourdes</h3><p>La Florida · Panel CMS</p><a href="admin.php?panel=contenedores">← Contenedores</a><a href="index.php#noticias-home" target="_blank" rel="noopener">Ver sitio público</a><hr><strong>Gestión de noticias</strong></aside><main class="news-main"><small>GESTIÓN DEL SITIO / CONTENEDORES</small><h1>Noticias</h1><p>Administra las noticias que se publican en la portada. Conservamos el diseño de tres tarjetas de Tabancura.</p>
<?php if($error):?><p style="color:#b42318"><?=news_e($error)?></p><?php endif;?><?php if($notice):?><p style="color:#187344"><?=news_e($notice)?></p><?php endif;?>
<?php if($sid):?><section class="news-card"><h2><?=$editing?'Editar noticia':'Agregar noticia'?></h2><form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?=news_e($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="save"><input type="hidden" name="id_item" value="<?=(int)($editing['id_item']??0)?>"><div class="news-grid">
<label class="news-field">Título<input required maxlength="220" name="titulo" value="<?=news_e($editing['titulo']??'')?>"></label>
<label class="news-field">Fotografía JPG, PNG o WebP (máx. 5 MB)<input type="file" name="imagen" accept="image/jpeg,image/png,image/webp"></label>
<label class="news-field">Fecha<input type="date" name="fecha_publicacion" value="<?=news_e(substr((string)($editing['fecha_publicacion']??''),0,10))?>"></label>
<label class="news-field">Enlace Leer más (opcional)<input type="url" name="url" placeholder="https://..." value="<?=news_e($editing['url']??'')?>"></label>
<label class="news-field">Orden<input type="number" name="orden" min="1" value="<?=(int)($editing['orden']??count($items)+1)?>"></label>
<label class="news-field">Visibilidad<select name="visible"><option value="si" <?=($editing['visible']??'si')==='si'?'selected':''?>>Visible</option><option value="no" <?=($editing['visible']??'si')==='no'?'selected':''?>>Oculta</option></select></label>
<label class="news-field" style="grid-column:1/-1">Resumen / descripción<textarea rows="5" maxlength="5000" name="descripcion"><?=news_e($editing['descripcion']??'')?></textarea></label></div>
<?php if(!empty($editing['imagen'])):?><p>Imagen actual: <img src="<?=news_e($editing['imagen'])?>" alt="" style="max-width:200px;max-height:120px"></p><?php endif;?>
<p><button class="news-btn">Guardar noticia</button> <?php if($editing):?><a href="editar_noticias.php">Cancelar edición</a><?php endif;?></p></form></section>
<section class="news-card"><h2>Noticias registradas (<?=count($items)?>)</h2><?php if(!$items):?><p>No hay noticias registradas.</p><?php endif;?>
<?php foreach($items as $i):?><div class="news-row"><?php if(!empty($i['imagen'])):?><img src="<?=news_e($i['imagen'])?>" alt=""><?php endif;?><div><strong><?=news_e($i['titulo'])?></strong><p><?=news_e($i['visible'])?> · Orden <?=(int)$i['orden']?></p><a href="editar_noticias.php?item=<?=(int)$i['id_item']?>">Editar</a></div><form method="post" onsubmit="return confirm('¿Eliminar esta noticia?')"><input type="hidden" name="csrf" value="<?=news_e($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="delete"><input type="hidden" name="id_item" value="<?=(int)$i['id_item']?>"><button type="submit">Eliminar</button></form></div><?php endforeach;?></section><?php endif;?></main></div></body></html>
