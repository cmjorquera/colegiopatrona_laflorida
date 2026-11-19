<?php
declare(strict_types=1);
require_once __DIR__.'/includes/rich_editor.php';
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
   $title=trim((string)($_POST['titulo']??''));$desc=patrona_rich_clean((string)($_POST['descripcion']??''));$url=trim((string)($_POST['url']??''));
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
  }elseif($action==='toggle'){
   $q=$db->prepare("UPDATE seccion_item SET visible=IF(visible='si','no','si') WHERE id_item=? AND id_seccion=?");
   $q->bind_param('ii',$id,$sid);$q->execute();
  }elseif($action==='reorder'){
   $ids=array_map('intval',explode(',',(string)($_POST['orden_ids']??'')));
   $ids=array_values(array_unique(array_filter($ids,static fn(int $v):bool=>$v>0)));
   $q=$db->prepare('SELECT id_item FROM seccion_item WHERE id_seccion=?');$q->bind_param('i',$sid);$q->execute();
   $actual=array_map('intval',array_column($q->get_result()->fetch_all(MYSQLI_ASSOC),'id_item'));
   $sorted=$ids;$expected=$actual;sort($sorted);sort($expected);
   if($sorted!==$expected)throw new RuntimeException('Orden inválido; recarga el listado.');
   $db->begin_transaction();
   try{$q=$db->prepare('UPDATE seccion_item SET orden=? WHERE id_item=? AND id_seccion=?');
    foreach($ids as $pos=>$itemId){$order=$pos+1;$q->bind_param('iii',$order,$itemId,$sid);$q->execute();}
    $db->commit();
   }catch(Throwable $e){$db->rollback();throw $e;}
  }elseif($action==='delete'){
   $q=$db->prepare('DELETE FROM seccion_item WHERE id_item=? AND id_seccion=?');$q->bind_param('ii',$id,$sid);$q->execute();
  }else throw new RuntimeException('Acción desconocida');
  $_SESSION['news_notice']='Noticia guardada correctamente.';header('Location: editar_noticias.php');exit;
 }
 $q=$db->prepare('SELECT * FROM seccion_item WHERE id_seccion=? ORDER BY orden,id_item');$q->bind_param('i',$sid);$q->execute();$items=$q->get_result()->fetch_all(MYSQLI_ASSOC);
}catch(Throwable $e){error_log('Editor Noticias: '.$e->getMessage());$error=$e->getMessage();}
$editing=null;foreach($items as $i)if((int)$i['id_item']===(int)($_GET['item']??0))$editing=$i;
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Noticias | CMS Patrona</title><link rel="stylesheet" href="assets/admin/css/patrona-panel.css?v=4"><style>
*{box-sizing:border-box}body{margin:0;background:#f3f5f8;color:#19304f;font:14px Arial,sans-serif}.nt-top{height:58px;background:white;border-bottom:1px solid #e0e5ed;display:flex;align-items:center;justify-content:space-between;padding:0 22px}.nt-top a{color:#0b568b;text-decoration:none}.nt-wrap{max-width:1180px;margin:28px auto;padding:0 12px}.nt-card{background:#fff;border:1px solid #dce3ec;border-radius:10px;padding:22px;margin-bottom:18px;box-shadow:0 2px 5px #13294d0a}.nt-title{display:flex;align-items:center;justify-content:space-between}.nt-title h1{font-size:20px;margin:0}.nt-tabs{display:flex;gap:12px;flex-wrap:wrap}.nt-tab{border:0;background:#f6f8fa;border-radius:7px;padding:12px 25px;color:#19304f;cursor:pointer}.nt-tab[aria-selected=true]{background:#005c96;color:white;font-weight:bold}.nt-panel[hidden]{display:none!important}.nt-btn{display:inline-block;background:#00629c;color:#fff;border:0;border-radius:7px;padding:11px 16px;text-decoration:none;cursor:pointer;font-weight:600}.nt-btn.secondary{background:#fff;color:#1b4d78;border:1px solid #cbd7e4}.nt-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:20px}.nt-head h2{margin:0;font-size:17px}.nt-muted{color:#637b96;font-size:12px}.nt-view{display:flex;gap:6px;background:#f3f5f8;border:1px solid #e0e5ed;padding:5px;border-radius:9px}.nt-view button{border:0;border-radius:6px;padding:8px 12px;background:transparent;cursor:pointer}.nt-view button.active{background:#102341;color:white}.nt-table{width:100%;border-collapse:collapse}.nt-table th{text-align:left;background:#fafafa;color:#536d88;text-transform:uppercase;font-size:11px;letter-spacing:.04em}.nt-table th,.nt-table td{padding:13px;border-bottom:1px solid #e5eaf0}.nt-thumb{width:78px;height:54px;object-fit:cover;border-radius:8px}.nt-icon{border:1px solid #d6e1ef;background:#fff;color:#1765a3;padding:8px 10px;border-radius:7px;cursor:pointer;text-decoration:none}.nt-actions{display:flex;gap:6px;align-items:center}.nt-actions form{margin:0}.nt-toggle{border:0;background:#d5dce5;width:34px;height:18px;border-radius:20px;cursor:pointer;position:relative}.nt-toggle.on{background:#00649b}.nt-toggle:after{content:'';position:absolute;left:3px;top:3px;width:12px;height:12px;background:white;border-radius:50%;transition:.15s}.nt-toggle.on:after{left:19px}.nt-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:20px}.nt-item{border:1px solid #e0e6ed;border-radius:10px;overflow:hidden}.nt-item img{width:100%;height:170px;object-fit:cover}.nt-item-body{padding:15px}.nt-item-body h3{font-size:16px}.nt-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}.nt-field{display:flex;flex-direction:column;gap:7px;font-weight:600;font-size:13px}.nt-field input,.nt-field select,.nt-field textarea{border:1px solid #cbd5e1;border-radius:7px;padding:10px;font:inherit;font-weight:400}.nt-preview{max-width:240px;max-height:150px;object-fit:contain;border-radius:8px}.nt-grip{cursor:grab;color:#60748c;font-size:20px;user-select:none}.nt-row.dragging{opacity:.4}.nt-row.dragover{outline:2px dashed #1875bb}.nt-note{padding:12px;background:#eef6fc;border-radius:8px;color:#2d587a}.nt-article{padding:18px;border:1px solid #e1e6ef;border-radius:10px;max-width:600px}.nt-article img{max-width:100%;border-radius:8px}.nt-article h3{font-size:22px}@media(max-width:700px){.nt-grid,.nt-form-grid{grid-template-columns:1fr}.nt-table{font-size:12px}.nt-table th,.nt-table td{padding:7px}.nt-top{padding:10px}.nt-actions{flex-wrap:wrap}}
</style><?php patrona_rich_assets(); ?></head><body>
<header class="nt-top"><div>☰ &nbsp; CMS &nbsp;›&nbsp; <strong>Noticias · Patrona de Lourdes</strong></div><div><a href="admin.php?panel=contenedores">← Volver</a> &nbsp; <a class="nt-btn" href="index.php#noticias-home" target="_blank" rel="noopener">◉ Visualizar</a></div></header>
<main class="nt-wrap"><div class="nt-card nt-title"><div><h1>Noticias de Patrona de Lourdes</h1><p class="nt-muted">Administración del contenedor noticias_home</p></div><a class="nt-btn secondary" href="index.php#noticias-home" target="_blank" rel="noopener">Ver en sitio</a></div>
<?php if($error):?><div class="nt-card" style="color:#a92323"><?=news_e($error)?></div><?php endif;?><?php if($notice):?><div class="nt-card" style="color:#16703b"><?=news_e($notice)?></div><?php endif;?>
<?php if($sid):?>
<div class="nt-card"><div class="nt-tabs" role="tablist" aria-label="Opciones del editor">
<button class="nt-tab" role="tab" data-tab="general" aria-selected="false">☷ General</button>
<button class="nt-tab" role="tab" data-tab="items" aria-selected="true">▱ Ítems</button>
<button class="nt-tab" role="tab" data-tab="diseno" aria-selected="false">◉ Diseño</button>
<button class="nt-tab" role="tab" data-tab="preview" aria-selected="false">◎ Vista previa</button>
</div></div>
<section class="nt-panel nt-card" data-panel="general" hidden><h2>Configuración general</h2><p class="nt-note">La visibilidad y el estado del contenedor se administran desde el listado principal. Los datos de las noticias se editan en la pestaña Ítems.</p><p>Contenedor: <strong><?=news_e($section['nombre_interno'])?></strong> · Tipo: <?=news_e($section['tipo_seccion'])?></p><a href="admin.php?panel=contenedores" class="nt-btn secondary">Administrar visibilidad del contenedor</a></section>
<section class="nt-panel nt-card" data-panel="items">
<div class="nt-head"><div><h2>Ítems del contenedor</h2><p class="nt-muted">Administración específica del bloque.</p></div><button class="nt-btn" type="button" id="nt-add">⊕ Agregar noticia</button></div>
<div class="nt-head"><p class="nt-muted">⠿ Arrastra las filas para cambiar el orden. El cambio se guarda al soltar.</p><div class="nt-view"><button class="active" data-view="list" type="button">☷ Listado</button><button data-view="cards" type="button">▦ Tarjetas</button></div></div>
<div id="nt-list"><table class="nt-table"><thead><tr><th></th><th>Orden</th><th>Imagen</th><th>Título</th><th>Visible</th><th>Acciones</th></tr></thead><tbody id="nt-sortable">
<?php foreach($items as $i):?><tr class="nt-row" draggable="true" data-id="<?=(int)$i['id_item']?>"><td class="nt-grip" title="Arrastrar para ordenar">⠿</td><td class="nt-order"><?=(int)$i['orden']?></td><td><?php if(!empty($i['imagen'])):?><img class="nt-thumb" src="<?=news_e($i['imagen'])?>" alt=""><?php endif;?></td><td><?=news_e($i['titulo'])?></td><td><form method="post"><input type="hidden" name="csrf" value="<?=news_e($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="toggle"><input type="hidden" name="id_item" value="<?=(int)$i['id_item']?>"><button type="submit" class="nt-toggle <?=($i['visible']??'no')==='si'?'on':''?>" aria-label="Cambiar visibilidad" title="Cambiar visibilidad"></button></form></td><td><div class="nt-actions"><button class="nt-icon" type="button" data-preview="<?=(int)$i['id_item']?>" title="Vista previa">◎</button><a class="nt-icon" href="editar_noticias.php?item=<?=(int)$i['id_item']?>#editor" title="Editar">✎</a><form method="post" onsubmit="return confirm('¿Eliminar esta noticia?')"><input type="hidden" name="csrf" value="<?=news_e($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="delete"><input type="hidden" name="id_item" value="<?=(int)$i['id_item']?>"><button class="nt-icon" title="Eliminar" type="submit">♜</button></form></div></td></tr><?php endforeach;?></tbody></table></div>
<div id="nt-cards" class="nt-grid" hidden><?php foreach($items as $i):?><article class="nt-item"><?php if(!empty($i['imagen'])):?><img src="<?=news_e($i['imagen'])?>" alt=""><?php endif;?><div class="nt-item-body"><h3><?=news_e($i['titulo'])?></h3><p class="nt-muted">Orden <?=(int)$i['orden']?> · <?=($i['visible']??'no')==='si'?'Visible':'Oculta'?></p><a href="editar_noticias.php?item=<?=(int)$i['id_item']?>#editor">Editar noticia</a></div></article><?php endforeach;?></div>
<form method="post" id="nt-order-form" hidden><input type="hidden" name="csrf" value="<?=news_e($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="reorder"><input type="hidden" name="orden_ids" id="nt-order-ids"></form>
</section>
<section class="nt-panel nt-card" data-panel="diseno" hidden><h2>Diseño del bloque</h2><p class="nt-note">La portada conserva el diseño aprobado: tres tarjetas por fila en escritorio, imagen superior, título y resumen. La configuración avanzada del diseño de Tabancura todavía requiere adaptación.</p><div class="nt-grid"><?php foreach(array_slice($items,0,3) as $i):?><article class="nt-item"><?php if(!empty($i['imagen'])):?><img src="<?=news_e($i['imagen'])?>" alt=""><?php endif;?><div class="nt-item-body"><h3><?=news_e($i['titulo'])?></h3></div></article><?php endforeach;?></div></section>
<section class="nt-panel nt-card" data-panel="preview" hidden><h2>Vista previa de noticias</h2><p class="nt-muted">Vista de los contenidos registrados; la presentación definitiva está en el sitio público.</p><div class="nt-grid"><?php foreach(array_slice(array_filter($items,static fn(array $i):bool=>($i['visible']??'')==='si'),0,3) as $i):?><article class="nt-item" data-preview-id="<?=(int)$i['id_item']?>"><?php if(!empty($i['imagen'])):?><img src="<?=news_e($i['imagen'])?>" alt=""><?php endif;?><div class="nt-item-body"><h3><?=news_e($i['titulo'])?></h3><p><?=news_e(mb_strimwidth(trim(strip_tags(html_entity_decode((string)($i['descripcion']??''),ENT_QUOTES|ENT_HTML5,'UTF-8'))),0,200,'…','UTF-8'))?></p></div></article><?php endforeach;?></div></section>
<section class="nt-card" id="editor" <?= $editing?'':'hidden'?>><div class="nt-head"><h2><?=$editing?'Editar noticia':'Agregar noticia'?></h2><button class="nt-btn secondary" type="button" id="nt-close">Cerrar editor</button></div>
<form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?=news_e($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="save"><input type="hidden" name="id_item" value="<?=(int)($editing['id_item']??0)?>"><div class="nt-form-grid">
<label class="nt-field">Título<input required maxlength="220" name="titulo" value="<?=news_e($editing['titulo']??'')?>"></label>
<label class="nt-field">Fotografía JPG, PNG o WebP (máx. 5 MB)<input type="file" name="imagen" id="nt-image" accept="image/jpeg,image/png,image/webp"></label>
<label class="nt-field">Fecha<input type="date" name="fecha_publicacion" value="<?=news_e(substr((string)($editing['fecha_publicacion']??''),0,10))?>"></label>
<label class="nt-field">Enlace Leer más<input type="url" name="url" placeholder="https://..." value="<?=news_e($editing['url']??'')?>"></label>
<label class="nt-field">Orden<input type="number" name="orden" min="1" value="<?=(int)($editing['orden']??count($items)+1)?>"></label>
<label class="nt-field">Visibilidad<select name="visible"><option value="si" <?=($editing['visible']??'si')==='si'?'selected':''?>>Visible</option><option value="no" <?=($editing['visible']??'si')==='no'?'selected':''?>>Oculta</option></select></label>
<label class="nt-field" style="grid-column:1/-1">Resumen / descripción<textarea rows="5" maxlength="5000" name="descripcion" data-rich-editor><?=news_e($editing['descripcion']??'')?></textarea></label></div>
<p><?php if(!empty($editing['imagen'])):?><img class="nt-preview" id="nt-image-preview" src="<?=news_e($editing['imagen'])?>" alt="Imagen actual"><?php else:?><img class="nt-preview" id="nt-image-preview" hidden alt="Vista previa"><?php endif;?></p>
<p><button class="nt-btn">Guardar noticia</button> <?php if($editing):?><a href="editar_noticias.php">Cancelar edición</a><?php endif;?></p></form></section>
<?php endif;?></main>
<script>
(function(){
 const tabs=[...document.querySelectorAll('[data-tab]')],panels=[...document.querySelectorAll('[data-panel]')];
 function select(name){tabs.forEach(t=>t.setAttribute('aria-selected',String(t.dataset.tab===name)));panels.forEach(p=>p.hidden=p.dataset.panel!==name);}
 tabs.forEach(t=>t.addEventListener('click',()=>select(t.dataset.tab)));
 const editor=document.getElementById('editor');
 document.getElementById('nt-add')?.addEventListener('click',()=>{editor.hidden=false;editor.scrollIntoView({behavior:'smooth'});});
 document.getElementById('nt-close')?.addEventListener('click',()=>editor.hidden=true);
 if(location.hash==='#editor'&&editor){editor.hidden=false;editor.scrollIntoView();}
 document.querySelectorAll('[data-view]').forEach(btn=>btn.addEventListener('click',()=>{const cards=btn.dataset.view==='cards';document.getElementById('nt-list').hidden=cards;document.getElementById('nt-cards').hidden=!cards;document.querySelectorAll('[data-view]').forEach(b=>b.classList.toggle('active',b===btn));}));
 document.querySelectorAll('[data-preview]').forEach(btn=>btn.addEventListener('click',()=>{select('preview');document.querySelector('[data-panel=preview]')?.scrollIntoView({behavior:'smooth'});}));
 const input=document.getElementById('nt-image'),preview=document.getElementById('nt-image-preview');let objectUrl=null;
 input?.addEventListener('change',()=>{if(objectUrl)URL.revokeObjectURL(objectUrl);const f=input.files?.[0];if(!f)return;if(!['image/jpeg','image/png','image/webp'].includes(f.type)||f.size>5242880){alert('Imagen inválida o mayor a 5 MB');input.value='';return;}objectUrl=URL.createObjectURL(f);preview.src=objectUrl;preview.hidden=false;});
 const body=document.getElementById('nt-sortable');let dragging=null;
 if(body){body.querySelectorAll('.nt-row').forEach(row=>{row.addEventListener('dragstart',e=>{dragging=row;row.classList.add('dragging');e.dataTransfer.effectAllowed='move';});row.addEventListener('dragover',e=>{e.preventDefault();if(!dragging||row===dragging)return;row.classList.add('dragover');const middle=row.getBoundingClientRect().top+row.offsetHeight/2;body.insertBefore(dragging,e.clientY<middle?row:row.nextSibling);});row.addEventListener('dragleave',()=>row.classList.remove('dragover'));row.addEventListener('dragend',()=>{row.classList.remove('dragging');body.querySelectorAll('.nt-row').forEach(r=>r.classList.remove('dragover'));const ids=[...body.querySelectorAll('.nt-row')].map(r=>r.dataset.id);document.getElementById('nt-order-ids').value=ids.join(',');document.getElementById('nt-order-form').submit();});});}
})();
</script></body></html>