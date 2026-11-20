<?php
declare(strict_types=1);
require_once __DIR__.'/includes/rich_editor.php';
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
   $id=max(0,(int)($_POST['id_item']??0));$titulo=trim((string)($_POST['titulo']??''));$desc=patrona_rich_clean((string)($_POST['descripcion']??''));$url=trim((string)($_POST['url']??''));
   if(mb_strlen($titulo)>180||mb_strlen(trim(html_entity_decode(strip_tags($desc),ENT_QUOTES|ENT_HTML5,'UTF-8')))>5000)throw new RuntimeException('Título o descripción demasiado largos.');
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
   $visible=($_POST['visible']??'si')==='no'?'no':'si';$orden=max(1,(int)($_POST['orden']??(count($items)+1)));
   if($id){
    $q=$db->prepare("UPDATE seccion_item SET etiqueta='sello',titulo=?,descripcion=?,imagen=?,url=?,visible=?,orden=? WHERE id_item=? AND id_seccion=?");
    $q->bind_param('sssssiii',$titulo,$desc,$image,$url,$visible,$orden,$id,$sid);
   }else{
    $q=$db->prepare("INSERT INTO seccion_item(id_seccion,etiqueta,titulo,descripcion,imagen,url,visible,orden) VALUES(?,'sello',?,?,?,?,?,?)");
    $q->bind_param('isssssi',$sid,$titulo,$desc,$image,$url,$visible,$orden);
   }$q->execute();
  }elseif($action==='reorder'){
   $ids=array_values(array_filter(array_map('intval',explode(',',(string)($_POST['ids']??'')))));
   $q=$db->prepare('SELECT id_item FROM seccion_item WHERE id_seccion=?');$q->bind_param('i',$sid);$q->execute();
   $valid=array_map('intval',array_column($q->get_result()->fetch_all(MYSQLI_ASSOC),'id_item'));
   sort($valid);$check=$ids;sort($check);
   if($check!==$valid)throw new RuntimeException('Orden inválido.');
   $db->begin_transaction();
   try{$q=$db->prepare('UPDATE seccion_item SET orden=? WHERE id_item=? AND id_seccion=?');
    foreach($ids as $pos=>$itemId){$order=$pos+1;$q->bind_param('iii',$order,$itemId,$sid);$q->execute();}
    $db->commit();
   }catch(Throwable $e){$db->rollback();throw $e;}
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
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Sellos institucionales | Panel CMS</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="assets/admin/css/patrona-panel.css?v=4">
<style>
.seal-panel{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:25px;margin:22px 0;box-shadow:0 5px 20px #102d5208}
.seal-panel h2{font-size:19px;margin:0 0 20px;color:#102d52}
.seal-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:17px}
.seal-field{display:flex;flex-direction:column;gap:8px;color:#294261;font-weight:600;font-size:13px}
.seal-field input,.seal-field textarea,.seal-field select{font:inherit;font-weight:400;width:100%;border:1px solid #cbd5e1;border-radius:8px;padding:11px;background:#fff;color:#162b49}
.seal-field input:focus,.seal-field textarea:focus,.seal-field select:focus{outline:2px solid #87baff;border-color:#2879c6}
.seal-btn{display:inline-flex;align-items:center;justify-content:center;background:#286ed0;color:#fff!important;border:0;border-radius:8px;padding:10px 16px;font-weight:600;cursor:pointer;text-decoration:none;font-size:13px}
.seal-btn:hover{background:#1859b4}
.seal-muted{color:#64748b}
.seal-item{display:flex;gap:16px;align-items:center;padding:15px 0;border-bottom:1px solid #e5ebf3}
.seal-item:last-child{border-bottom:0}.seal-item img{width:90px;height:90px;object-fit:contain;border:1px solid #e5ebf3;border-radius:9px;padding:6px}
.seal-actions{margin-left:auto;display:flex;gap:8px}
.seal-panel .alert{padding:13px;border-radius:8px;background:#e7f5ee}
.seal-toolbar{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:16px}
.seal-toolbar a{color:#2879c6;font-weight:600;font-size:13px}
.seal-pill{display:inline-flex;gap:7px;align-items:center;background:#e9f2fc;color:#20599b;padding:7px 12px;border-radius:20px;font-size:12px;font-weight:700}
@media(max-width:850px){.seal-fields{grid-template-columns:1fr}.seal-item{flex-wrap:wrap}.seal-actions{margin-left:0}.admin-sidebar{display:none}.admin-main{padding:20px}}

.seal-modal[hidden]{display:none!important}.seal-modal{position:fixed;inset:0;z-index:1100;background:#0b213a99;display:flex;align-items:center;justify-content:center;padding:20px}.seal-dialog{background:white;border-radius:14px;width:min(850px,100%);max-height:92vh;display:flex;flex-direction:column;box-shadow:0 24px 70px #091e3b55;overflow:hidden}.seal-modal-head,.seal-modal-foot{display:flex;align-items:center;justify-content:space-between;padding:17px 22px;background:#f8fafc;border-bottom:1px solid #dbe4f0}.seal-modal-foot{border-top:1px solid #dbe4f0;border-bottom:0;justify-content:flex-end;gap:10px}.seal-modal-body{overflow:auto;padding:22px}.seal-close{background:transparent;border:0;font-size:25px;cursor:pointer;color:#64748b}.seal-secondary{border:1px solid #cbd5e1;background:#fff;color:#173858;padding:10px 16px;border-radius:8px;cursor:pointer}.seal-list{width:100%;border-collapse:collapse}.seal-list th,.seal-list td{padding:12px;border-bottom:1px solid #e4ebf3;text-align:left}.seal-list th{font-size:11px;letter-spacing:.06em;background:#f4f7fb;color:#56708c}.seal-thumb{width:65px;height:65px;object-fit:contain}.seal-icon-btn{display:inline-flex;align-items:center;justify-content:center;border:1px solid #cfdaea;background:white;border-radius:7px;width:35px;height:35px;cursor:pointer;color:#204b77}.seal-drag{cursor:grab;color:#7890a9}.seal-row.dragging{opacity:.45}.seal-tag{border-radius:99px;padding:5px 10px;font-size:12px;background:#e5f5ed;color:#148052}.seal-tag.off{background:#f1f3f6;color:#718096}.seal-preview{width:100%;height:140px;object-fit:contain;background:#f6f9fd;border:1px dashed #cbd5e1;border-radius:9px;margin-top:8px}.seal-toolbar-top{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}.seal-help{font-size:12px;color:#6b7f99}.seal-panel{margin:12px 0}.seal-modal .seal-field textarea{min-height:115px}.seal-modal .seal-field input[type=checkbox]{width:auto}.seal-item-actions{display:flex;gap:6px}.seal-check{display:flex;align-items:center;gap:10px}.seal-check input{width:18px;height:18px}.seal-hidden-fields{opacity:.55}.seal-hidden-fields input:not([type=checkbox]),.seal-hidden-fields textarea{background:#f3f5f8}
@media(max-width:650px){.seal-modal{padding:8px}.seal-fields{grid-template-columns:1fr}.seal-list{font-size:12px}.seal-list th:nth-child(3),.seal-list td:nth-child(3){display:none}}
</style><?php patrona_rich_assets(); ?></head><body>
<div class="admin-shell">
<?php $panel =  'contenedores'; require __DIR__.'/includes/patrona_sidebar.php'; ?>
<div class="admin-content">
<header class="topbar"><span><i class="bi bi-list"></i> Panel de administración <span class="topbar-muted">/ Contenedores / Sellos institucionales</span></span><a href="index.php#sellos-home" target="_blank" rel="noopener">Ver sitio <i class="bi bi-arrow-up-right"></i></a></header>
<main class="admin-main">
<div class="page-heading"><div><div class="eyebrow">COLEGIO PATRONA DE LOURDES · LA FLORIDA</div><h1>Sellos institucionales</h1><p>Administra las imágenes, descripciones, enlaces y el orden de los sellos del colegio.</p></div><span class="seal-pill"><i class="bi bi-award"></i> Contenedor sellos_home</span></div>
<div class="seal-toolbar"><a href="admin.php?panel=contenedores"><i class="bi bi-arrow-left"></i> Volver a contenedores</a><a href="index.php#sellos-home" target="_blank" rel="noopener"><i class="bi bi-eye"></i> Vista pública</a></div>
<?php if($error):?><div class="alert" style="background:#ffeded"><?=se($error)?></div><?php endif;?>
<?php if($notice):?><div class="alert"><?=se($notice)?></div><?php endif;?>
<?php if($sid):?>
<div class="seal-panel"><h2>Configuración del bloque</h2><form method="post"><input type="hidden" name="csrf" value="<?=se($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="config"><div class="seal-fields">
<label class="seal-field">Título del bloque<input name="titulo_bloque" maxlength="180" value="<?=se((string)($configs['titulo_bloque']??'Sellos del colegio'))?>"></label>
<label class="seal-field">Texto pequeño sobre el título<input name="subtitulo_bloque" maxlength="180" value="<?=se((string)($configs['subtitulo_bloque']??''))?>"></label>
</div><p><button class="seal-btn">Guardar configuración</button></p></form></div>

<div class="seal-panel">
 <div class="seal-toolbar-top"><div><h2 style="margin:0 0 7px">Sellos registrados (<?=count($items)?>)</h2><div class="seal-help"><i class="bi bi-grip-vertical"></i> Arrastra los sellos para cambiar su orden; se guarda automáticamente.</div></div><button type="button" class="seal-btn" onclick="openSeal()"><i class="bi bi-plus-circle"></i>&nbsp; Agregar sello</button></div>
 <?php if(!$items):?><p class="seal-muted">No hay sellos registrados. Agrega el primero para mostrar el bloque.</p><?php else:?>
 <div style="overflow-x:auto;margin-top:18px"><table class="seal-list"><thead><tr><th>ORDEN</th><th>IMAGEN</th><th>NOMBRE</th><th>VISIBLE</th><th>ACCIONES</th></tr></thead><tbody id="sealSortable">
 <?php foreach($items as $idx=>$i):?><tr class="seal-row" draggable="true" data-id="<?=(int)$i['id_item']?>">
 <td><span class="seal-drag" title="Arrastrar para ordenar"><i class="bi bi-grip-vertical"></i></span> <span class="seal-position"><?=$idx+1?></span></td>
 <td><img class="seal-thumb" src="<?=se((string)$i['imagen'])?>" alt=""></td>
 <td><strong><?=se((string)($i['titulo']?:'Sello sin nombre'))?></strong></td>
 <td><span class="seal-tag <?=($i['visible']??'si')==='si'?'':'off'?>"><?=($i['visible']??'si')==='si'?'Visible':'Oculto'?></span></td>
 <td><div class="seal-item-actions">
 <button class="seal-icon-btn" type="button" title="Editar" onclick="openSeal(<?=(int)$i['id_item']?>)"><i class="bi bi-pencil-square"></i></button>
 <button class="seal-icon-btn" type="button" title="Ver imagen" onclick="window.open(<?=json_encode((string)$i['imagen'],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)?>,'_blank','noopener')"><i class="bi bi-eye"></i></button>
 <form method="post" onsubmit="return confirm('¿Eliminar este sello?')"><input type="hidden" name="csrf" value="<?=se($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="delete"><input type="hidden" name="id_item" value="<?=(int)$i['id_item']?>"><button class="seal-icon-btn" title="Eliminar"><i class="bi bi-trash"></i></button></form>
 </div></td></tr><?php endforeach;?></tbody></table></div><?php endif;?>
</div>
<form id="sealOrderForm" method="post" hidden><input type="hidden" name="csrf" value="<?=se($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="reorder"><input type="hidden" name="ids" id="sealOrderIds"></form>
<div class="seal-modal" id="sealModal" hidden role="dialog" aria-modal="true" aria-labelledby="sealModalTitle">
 <div class="seal-dialog">
 <div class="seal-modal-head"><h2 id="sealModalTitle" style="margin:0;font-size:19px">Agregar sello</h2><button type="button" class="seal-close" aria-label="Cerrar" onclick="closeSeal()"><i class="bi bi-x-lg"></i></button></div>
 <form method="post" enctype="multipart/form-data" id="sealForm" style="overflow:auto;display:flex;flex-direction:column">
 <input type="hidden" name="csrf" value="<?=se($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="save"><input type="hidden" name="id_item" id="sealId" value="0">
 <div class="seal-modal-body"><div class="seal-fields">
 <label class="seal-field">Imagen del sello (JPG, PNG o WebP; máximo 5 MB)<input type="file" name="imagen" id="sealImage" accept="image/jpeg,image/png,image/webp"><img id="sealImagePreview" class="seal-preview" alt="Vista previa" hidden></label>
 <label class="seal-field">Nombre del sello<input name="titulo" id="sealTitle" maxlength="180" placeholder="Nombre institucional"></label>
 <label class="seal-field">Enlace externo (opcional)<input type="url" name="url" id="sealUrl" placeholder="https://..."></label>
 <label class="seal-field">Visibilidad<div class="seal-check"><input type="checkbox" id="sealVisible" checked><span id="sealVisibleText">Mostrar en la página</span></div><input type="hidden" name="visible" id="sealVisibleValue" value="si"></label>
 <label class="seal-field" style="grid-column:1/-1">Descripción del reverso (máximo 5.000 caracteres)<textarea name="descripcion" id="sealDescription" maxlength="5000" rows="5" data-rich-editor></textarea></label>
 </div></div>
 <div class="seal-modal-foot"><button type="button" class="seal-secondary" onclick="closeSeal()">Cancelar</button><button class="seal-btn" type="submit"><i class="bi bi-check-circle"></i>&nbsp; Guardar sello</button></div>
 </form></div>
</div>
<?php endif;?>
<script>
const sealData=<?=json_encode(array_map(static fn($i)=>['id'=>(int)$i['id_item'],'titulo'=>(string)($i['titulo']??''),'descripcion'=>(string)($i['descripcion']??''),'url'=>(string)($i['url']??''),'imagen'=>(string)($i['imagen']??''),'visible'=>(string)($i['visible']??'si')],$items),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)?>;
const sealModal=document.getElementById('sealModal'),sealForm=document.getElementById('sealForm'),sealVisible=document.getElementById('sealVisible');
function openSeal(id=0){const i=sealData.find(x=>x.id===id);sealForm.reset();document.getElementById('sealId').value=i?.id||0;document.getElementById('sealModalTitle').textContent=i?'Editar sello':'Agregar sello';document.getElementById('sealTitle').value=i?.titulo||'';document.getElementById('sealUrl').value=i?.url||'';const descField=document.getElementById('sealDescription');descField.value=i?.descripcion||'';descField.dispatchEvent(new Event('rich-editor:load'));sealVisible.checked=!i||i.visible==='si';sealVisibility();const img=document.getElementById('sealImagePreview');img.hidden=!i?.imagen;if(i?.imagen)img.src=i.imagen;document.getElementById('sealImage').required=!i;sealModal.hidden=false;document.body.style.overflow='hidden';}
function closeSeal(){sealModal.hidden=true;document.body.style.overflow='';}
function sealVisibility(){document.getElementById('sealVisibleValue').value=sealVisible.checked?'si':'no';document.getElementById('sealVisibleText').textContent=sealVisible.checked?'Mostrar en la página':'Ocultar de la página';}
sealVisible.addEventListener('change',sealVisibility);
document.getElementById('sealImage').addEventListener('change',e=>{const f=e.target.files[0],img=document.getElementById('sealImagePreview');if(f){img.src=URL.createObjectURL(f);img.hidden=false;}});

const sortable=document.getElementById('sealSortable');if(sortable){let dragged=null;sortable.addEventListener('dragstart',e=>{dragged=e.target.closest('tr');if(dragged){dragged.classList.add('dragging');e.dataTransfer.effectAllowed='move';}});sortable.addEventListener('dragover',e=>{e.preventDefault();const row=e.target.closest('tr');if(row&&dragged&&row!==dragged){const rect=row.getBoundingClientRect();sortable.insertBefore(dragged,e.clientY<rect.top+rect.height/2?row:row.nextSibling);}});sortable.addEventListener('dragend',()=>{if(!dragged)return;dragged.classList.remove('dragging');dragged=null;const rows=[...sortable.querySelectorAll('tr')];rows.forEach((r,i)=>r.querySelector('.seal-position').textContent=i+1);document.getElementById('sealOrderIds').value=rows.map(r=>r.dataset.id).join(',');document.getElementById('sealOrderForm').submit();});}
</script>
</main></div></div><script defer src="assets/admin/js/patrona-sidebar.js?v=2"></script></body></html>