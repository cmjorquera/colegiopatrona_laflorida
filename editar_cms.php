<?php
require_once __DIR__.'/includes/rich_editor.php';
declare(strict_types=1);
$sessionPath='/home3/crist668/php_sessions_patrona';
if(!is_dir($sessionPath)||!is_writable($sessionPath)){http_response_code(503);exit('Sesiones no disponibles');}
session_save_path($sessionPath);session_start();
if(empty($_SESSION['patrona_admin_id'])){header('Location: admin.php');exit;}
if(empty($_SESSION['patrona_csrf']))$_SESSION['patrona_csrf']=bin2hex(random_bytes(32));
function ce($v):string{return htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
$allowed=['topbar','header_principal','tab_destacados_home','spotify_podcast_home','galeria_home','estadisticas_home'];
$requested=(int)($_GET['id']??$_POST['id_seccion']??0);$error='';$notice=(string)($_SESSION['generic_notice']??'');unset($_SESSION['generic_notice']);$items=[];$config=[];$section=null;
try{
 $cfg=require __DIR__.'/class/conexion_local.php';
 if(!is_array($cfg)||($cfg['db']??'')!=='crist668_colegio_patrona_laflorida')throw new RuntimeException('Base no autorizada');
 mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
 $db=new mysqli($cfg['host'],$cfg['user'],$cfg['pass'],$cfg['db']);$db->set_charset('utf8mb4');
 $q=$db->prepare('SELECT * FROM seccion WHERE id_seccion=? AND id_institucion=2 LIMIT 1');$q->bind_param('i',$requested);$q->execute();$section=$q->get_result()->fetch_assoc();
 if(!$section||in_array((string)$section['nombre_interno'],['hero_principal','sellos_home','noticias_home','tab_comunidad_home'],true))throw new RuntimeException('Este contenedor tiene un editor especializado.');
 $sid=(int)$section['id_seccion'];$name=(string)$section['nombre_interno'];
 if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!hash_equals((string)$_SESSION['patrona_csrf'],(string)($_POST['csrf']??'')))throw new RuntimeException('Sesión vencida.');
  if(!in_array((string)$section['editable'],['si','1'],true))throw new RuntimeException('Contenedor no editable.');
  $action=(string)($_POST['accion']??'');$id=max(0,(int)($_POST['id_item']??0));
  if($action==='config'){
   $db->begin_transaction();
   try{
    foreach(['titulo_bloque','subtitulo_bloque','texto','enlace','boton_texto'] as $key){
     $value=trim((string)($_POST[$key]??''));if(mb_strlen($value)>1000)throw new RuntimeException('Configuración demasiado extensa.');
     $q=$db->prepare('DELETE FROM seccion_config WHERE id_seccion=? AND clave=?');$q->bind_param('is',$sid,$key);$q->execute();
     $q=$db->prepare('INSERT INTO seccion_config(id_seccion,clave,valor) VALUES(?,?,?)');$q->bind_param('iss',$sid,$key,$value);$q->execute();
    }$db->commit();
   }catch(Throwable $e){$db->rollback();throw $e;}
  }elseif($action==='save'){
   $title=trim((string)($_POST['titulo']??''));$desc=patrona_rich_clean((string)($_POST['descripcion']??''));$url=trim((string)($_POST['url']??''));$label=trim((string)($_POST['etiqueta']??''));
   if(mb_strlen($title)>220||mb_strlen($desc)>5000||mb_strlen($label)>120)throw new RuntimeException('Texto demasiado largo.');
   if($url!==''&&(!filter_var($url,FILTER_VALIDATE_URL)||!in_array(strtolower((string)parse_url($url,PHP_URL_SCHEME)),['http','https'],true)))throw new RuntimeException('El enlace debe ser HTTP(S).');
   $image='';
   if($id){$q=$db->prepare('SELECT imagen FROM seccion_item WHERE id_item=? AND id_seccion=?');$q->bind_param('ii',$id,$sid);$q->execute();$old=$q->get_result()->fetch_assoc();if(!$old)throw new RuntimeException('Elemento no encontrado');$image=(string)($old['imagen']??'');}
   if(isset($_FILES['imagen'])&&($_FILES['imagen']['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_NO_FILE){
    $f=$_FILES['imagen'];if($f['error']!==UPLOAD_ERR_OK||$f['size']>5*1024*1024)throw new RuntimeException('Imagen inválida o mayor a 5 MB.');
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);$ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$mime]??'';
    if($ext===''||!getimagesize($f['tmp_name']))throw new RuntimeException('Solo JPG, PNG o WebP.');
    $dir=__DIR__.'/assets/uploads/secciones/'.$name;
    if(!is_dir($dir)&&!mkdir($dir,0755,true)&&!is_dir($dir))throw new RuntimeException('No se pudo crear carpeta de imágenes.');
    $filename=bin2hex(random_bytes(16)).'.'.$ext;
    if(!move_uploaded_file($f['tmp_name'],$dir.'/'.$filename))throw new RuntimeException('No se pudo guardar la imagen.');
    $image='assets/uploads/secciones/'.$name.'/'.$filename;
   }
   $visible=($_POST['visible']??'si')==='no'?'no':'si';$orden=max(1,(int)($_POST['orden']??1));
   if($id){
    $q=$db->prepare('UPDATE seccion_item SET etiqueta=?,titulo=?,descripcion=?,imagen=?,url=?,visible=?,orden=? WHERE id_item=? AND id_seccion=?');
    $q->bind_param('ssssssiii',$label,$title,$desc,$image,$url,$visible,$orden,$id,$sid);
   }else{
    $q=$db->prepare('INSERT INTO seccion_item(id_seccion,etiqueta,titulo,descripcion,imagen,url,visible,orden) VALUES(?,?,?,?,?,?,?,?)');
    $q->bind_param('issssssi',$sid,$label,$title,$desc,$image,$url,$visible,$orden);
   }$q->execute();
  }elseif($action==='delete'){
   $q=$db->prepare('DELETE FROM seccion_item WHERE id_item=? AND id_seccion=?');$q->bind_param('ii',$id,$sid);$q->execute();
  }else throw new RuntimeException('Acción inválida');
  $_SESSION['generic_notice']='Cambios guardados correctamente.';header('Location: editar_cms.php?id='.$sid);exit;
 }
 $q=$db->prepare('SELECT * FROM seccion_item WHERE id_seccion=? ORDER BY orden,id_item');$q->bind_param('i',$sid);$q->execute();$items=$q->get_result()->fetch_all(MYSQLI_ASSOC);
 $q=$db->prepare('SELECT clave,valor FROM seccion_config WHERE id_seccion=?');$q->bind_param('i',$sid);$q->execute();foreach($q->get_result()->fetch_all(MYSQLI_ASSOC) as $r)$config[$r['clave']]=$r['valor'];
}catch(Throwable $e){error_log('Patrona CMS editor general: '.$e->getMessage());$error=$e->getMessage();}
$editing=null;foreach($items as $item)if((int)$item['id_item']===(int)($_GET['item']??0))$editing=$item;
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Editor de contenedores | CMS</title><link rel="stylesheet" href="assets/admin/css/patrona-panel.css"><style>
.cms-shell{display:grid;grid-template-columns:240px minmax(0,1fr);min-height:100vh;background:#f3f6fb}.cms-side{background:#102b50;color:#fff;padding:24px 18px}.cms-side a{display:block;padding:12px;color:#e0eafb;text-decoration:none}.cms-main{max-width:1120px;width:100%;margin:auto;padding:32px}.cms-card{background:#fff;border:1px solid #dce5ef;border-radius:12px;padding:24px;margin:22px 0}.cms-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.cms-field{display:flex;flex-direction:column;gap:7px;font-weight:600;font-size:13px}.cms-field input,.cms-field textarea,.cms-field select{border:1px solid #cbd5e1;border-radius:7px;padding:11px;font:inherit;font-weight:400}.cms-btn{background:#1765b8;color:white;border:0;border-radius:7px;padding:10px 16px;cursor:pointer;text-decoration:none}.cms-row{display:flex;align-items:center;gap:16px;padding:12px;border-bottom:1px solid #e4eaf0}.cms-row img{width:110px;height:82px;object-fit:cover;border-radius:9px}.cms-preview{display:none;max-width:320px;max-height:210px;object-fit:contain;border:1px solid #dce5ef;border-radius:10px;padding:8px;margin-top:12px}.cms-preview.is-visible{display:block}.cms-row{background:#fff;border-radius:10px;margin:8px 0}.cms-row strong{color:#123b70}.cms-row a{display:inline-block;margin-top:6px;color:#1765b8;font-weight:600}.cms-field input:focus,.cms-field textarea:focus,.cms-field select:focus{outline:2px solid #91bff2;border-color:#1765b8}.cms-row form{margin-left:auto}@media(max-width:750px){.cms-shell{grid-template-columns:1fr}.cms-grid{grid-template-columns:1fr}.cms-main{padding:15px}}
</style><?php patrona_rich_assets(); ?></head><body><div class="cms-shell"><aside class="cms-side"><h3>Patrona de Lourdes</h3><p>La Florida · Panel CMS</p><a href="admin.php?panel=contenedores">← Contenedores</a><a href="index.php" target="_blank" rel="noopener">Ver sitio público</a></aside><main class="cms-main"><small>GESTIÓN DEL SITIO / CONTENEDORES</small><h1><?=ce($section['titulo_admin']??'Editor de contenedores')?></h1><p>Configuración y elementos del contenedor. Utiliza las mismas tablas CMS de Tabancura.</p>
<?php if($error):?><p style="color:#a92323"><?=ce($error)?></p><?php endif;?><?php if($notice):?><p style="color:#16703b"><?=ce($notice)?></p><?php endif;?>
<?php if($section):?>
<section class="cms-card"><h2>Configuración</h2><form method="post"><input type="hidden" name="id_seccion" value="<?=(int)$sid?>"><input type="hidden" name="csrf" value="<?=ce($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="config"><div class="cms-grid">
<?php foreach(['titulo_bloque'=>'Título del bloque','subtitulo_bloque'=>'Subtítulo','texto'=>'Texto','enlace'=>'Enlace','boton_texto'=>'Texto del botón'] as $key=>$label):?><label class="cms-field"><?=ce($label)?><input name="<?=ce($key)?>" value="<?=ce($config[$key]??'')?>" maxlength="1000"></label><?php endforeach;?>
</div><p><button class="cms-btn">Guardar configuración</button></p></form></section>
<section class="cms-card"><h2><?=$editing?'Editar elemento':'Agregar elemento'?></h2><form method="post" enctype="multipart/form-data"><input type="hidden" name="id_seccion" value="<?=(int)$sid?>"><input type="hidden" name="csrf" value="<?=ce($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="save"><input type="hidden" name="id_item" value="<?=(int)($editing['id_item']??0)?>"><div class="cms-grid">
<label class="cms-field">Título<input name="titulo" maxlength="220" value="<?=ce($editing['titulo']??'')?>"></label>
<label class="cms-field">Etiqueta<input name="etiqueta" maxlength="120" value="<?=ce($editing['etiqueta']??'')?>"></label>
<label class="cms-field">Imagen JPG/PNG/WebP, máximo 5 MB<input type="file" name="imagen" accept="image/jpeg,image/png,image/webp" id="cms-image-input"><img class="cms-preview" id="cms-image-preview" alt="Vista previa de imagen seleccionada"></label>
<label class="cms-field">Enlace HTTP(S)<input type="url" name="url" value="<?=ce($editing['url']??'')?>"></label>
<label class="cms-field">Orden<input type="number" min="1" name="orden" value="<?=(int)($editing['orden']??count($items)+1)?>"></label>
<label class="cms-field">Visibilidad<select name="visible"><option value="si" <?=($editing['visible']??'si')==='si'?'selected':''?>>Visible</option><option value="no" <?=($editing['visible']??'si')==='no'?'selected':''?>>Oculto</option></select></label>
<label class="cms-field" style="grid-column:1/-1">Descripción<textarea name="descripcion" rows="4" maxlength="5000" data-rich-editor><?=ce($editing['descripcion']??'')?></textarea></label>
</div><?php if(!empty($editing['imagen'])):?><p><img src="<?=ce($editing['imagen'])?>" alt="" style="max-width:180px;max-height:120px"></p><?php endif;?><p><button class="cms-btn">Guardar elemento</button> <?php if($editing):?><a href="editar_cms.php?id=<?=(int)$sid?>">Cancelar</a><?php endif;?></p></form></section>
<section class="cms-card"><h2>Elementos registrados (<?=count($items)?>)</h2><?php if(!$items):?><p>No hay elementos registrados.</p><?php endif;?>
<?php foreach($items as $item):?><div class="cms-row"><?php if(!empty($item['imagen'])):?><img src="<?=ce($item['imagen'])?>" alt=""><?php endif;?><div><strong><?=ce($item['titulo']??'')?></strong><p><?=ce($item['visible']??'')?> · Orden <?=(int)$item['orden']?></p><a href="editar_cms.php?id=<?=(int)$sid?>&item=<?=(int)$item['id_item']?>">Editar</a></div><form method="post" onsubmit="return confirm('¿Eliminar este elemento?')"><input type="hidden" name="csrf" value="<?=ce($_SESSION['patrona_csrf'])?>"><input type="hidden" name="id_seccion" value="<?=(int)$sid?>"><input type="hidden" name="accion" value="delete"><input type="hidden" name="id_item" value="<?=(int)$item['id_item']?>"><button type="submit">Eliminar</button></form></div><?php endforeach;?></section><?php endif;?>
</main></div><script>
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
</script></body></html>
