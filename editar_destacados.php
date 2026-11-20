<?php
declare(strict_types=1);
$sessionPath='/home3/crist668/php_sessions_patrona';
if(!is_dir($sessionPath)||!is_writable($sessionPath)){http_response_code(503);exit('Sesiones no disponibles');}
session_save_path($sessionPath);session_start();
if(empty($_SESSION['patrona_admin_id'])){header('Location: admin.php');exit;}
if(empty($_SESSION['patrona_csrf']))$_SESSION['patrona_csrf']=bin2hex(random_bytes(32));
function qe($v):string{return htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
$error='';$notice=(string)($_SESSION['quick_notice']??'');unset($_SESSION['quick_notice']);
$section=null;$items=[];$sid=0;
$defaults=[
 ['icon'=>'✦','title'=>'[DEMO] Admisión','description'=>'Conoce los pasos y fechas de postulación.','url'=>'#'],
 ['icon'=>'▦','title'=>'[DEMO] Calendario','description'=>'Próximas actividades y encuentros escolares.','url'=>'#'],
 ['icon'=>'◇','title'=>'[DEMO] Comunidad','description'=>'Actividades para estudiantes y familias.','url'=>'#'],
 ['icon'=>'↗','title'=>'[DEMO] Contacto','description'=>'Canales de información institucional.','url'=>'#']
];
try{
 $cfg=require __DIR__.'/class/conexion_local.php';
 if(!is_array($cfg)||($cfg['db']??'')!=='crist668_colegio_patrona_laflorida')throw new RuntimeException('Base no autorizada.');
 mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
 $db=new mysqli($cfg['host'],$cfg['user'],$cfg['pass'],$cfg['db']);$db->set_charset('utf8mb4');
 $q=$db->prepare("SELECT * FROM seccion WHERE id_institucion=2 AND nombre_interno='tab_destacados_home' LIMIT 1");$q->execute();$section=$q->get_result()->fetch_assoc();
 if(!$section)throw new RuntimeException('No existe el contenedor de destacados.');
 $sid=(int)$section['id_seccion'];
 if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!hash_equals((string)$_SESSION['patrona_csrf'],(string)($_POST['csrf']??'')))throw new RuntimeException('Sesión vencida. Recarga la página.');
  if(!in_array((string)($section['editable']??''),['si','1'],true))throw new RuntimeException('Este contenedor no es editable.');
  $slot=(int)($_POST['slot']??0);if($slot<0||$slot>3)throw new RuntimeException('Tarjeta inválida.');
  $title=trim((string)($_POST['titulo']??''));$desc=trim((string)($_POST['descripcion']??''));$icon=trim((string)($_POST['etiqueta']??''));$url=trim((string)($_POST['url']??''));
  if($title===''||$desc===''||$icon==='')throw new RuntimeException('Completa el ícono, título y descripción.');
  if(mb_strlen($title)>120||mb_strlen($desc)>220||mb_strlen($icon)>12||mb_strlen($url)>500)throw new RuntimeException('Alguno de los textos excede el máximo permitido.');
  if($url!==''&&$url!=='#'&&!preg_match('~^(https?://[^\s]+|#[a-zA-Z0-9_-]+|/[a-zA-Z0-9_./?&=%#-]+)$~',$url))throw new RuntimeException('Utiliza un enlace HTTP(S), una ruta interna o un ancla #seccion.');
  $q=$db->prepare('SELECT id_item FROM seccion_item WHERE id_seccion=? ORDER BY orden,id_item LIMIT 4');$q->bind_param('i',$sid);$q->execute();$ids=array_map('intval',array_column($q->get_result()->fetch_all(MYSQLI_ASSOC),'id_item'));
  $db->begin_transaction();
  try{
   // Crear los cuatro registros de una vez evita perder las otras tarjetas de demostración.
   if(count($ids)<4){
    $q=$db->prepare('INSERT INTO seccion_item(id_seccion,etiqueta,titulo,descripcion,url,visible,orden) VALUES(?,?,?,?,?,?,?)');
    for($j=count($ids);$j<4;$j++){$d=$defaults[$j];$pos=$j+1;$visible='si';$q->bind_param('isssssi',$sid,$d['icon'],$d['title'],$d['description'],$d['url'],$visible,$pos);$q->execute();$ids[]=$db->insert_id;}
   }
   $id=$ids[$slot];$q=$db->prepare("UPDATE seccion_item SET etiqueta=?,titulo=?,descripcion=?,url=?,visible='si' WHERE id_item=? AND id_seccion=?");
   $q->bind_param('ssssii',$icon,$title,$desc,$url,$id,$sid);$q->execute();
   // Limitar el contenedor a cuatro elementos visibles sin eliminar datos anteriores.
   $q=$db->prepare("UPDATE seccion_item SET visible='no' WHERE id_seccion=? AND id_item NOT IN (?,?,?,?)");
   $q->bind_param('iiiii',$sid,$ids[0],$ids[1],$ids[2],$ids[3]);$q->execute();
   $db->commit();
  }catch(Throwable $e){$db->rollback();throw $e;}
  $_SESSION['quick_notice']='Tarjeta actualizada correctamente.';header('Location: editar_destacados.php?item='.$slot);exit;
 }
 $q=$db->prepare('SELECT * FROM seccion_item WHERE id_seccion=? ORDER BY orden,id_item LIMIT 4');$q->bind_param('i',$sid);$q->execute();$items=$q->get_result()->fetch_all(MYSQLI_ASSOC);
}catch(Throwable $e){error_log('Patrona destacados: '.$e->getMessage());$error=$e->getMessage();}
$cards=[];for($i=0;$i<4;$i++){$item=$items[$i]??[];$cards[]=['icon'=>(string)($item['etiqueta']??$defaults[$i]['icon']),'title'=>(string)($item['titulo']??$defaults[$i]['title']),'description'=>(string)($item['descripcion']??$defaults[$i]['description']),'url'=>(string)($item['url']??$defaults[$i]['url'])];}
$selected=(int)($_GET['item']??0);if($selected<0||$selected>3)$selected=0;
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Destacados | Patrona CMS</title>
<?php require __DIR__.'/includes/patrona_admin_assets.php'; ?>
<style>
[hidden]{display:none!important}body{margin:0;background:#f3f6fb;color:#172c48;font-family:Arial,sans-serif}.quick-editor{padding:20px clamp(16px,2vw,32px);max-width:1650px;margin:auto}.quick-heading{display:flex;justify-content:space-between;align-items:center;gap:15px;flex-wrap:wrap}.quick-heading h1{font-size:25px;margin:5px 0}.quick-muted{font-size:13px;color:#718097}.quick-panel{background:#fff;border:1px solid #dce5ef;border-radius:12px;padding:20px;margin-top:16px}.quick-panel h2{font-size:18px;margin:0 0 6px}.quick-preview{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));border-radius:12px;overflow:hidden;box-shadow:0 12px 35px #142d4814;margin-top:16px}.quick-preview-card{display:flex;align-items:center;gap:13px;min-width:0;border:1px solid #e1e8f1;border-right:0;padding:17px 13px;background:white;text-align:left;cursor:pointer;color:#153d71}.quick-preview-card:last-child{border-right:1px solid #e1e8f1}.quick-preview-card.active{background:#eff6ff;box-shadow:inset 0 -3px #1765b8}.quick-preview-card .quick-icon{font-size:25px;color:#1d6bb7;flex:0 0 28px}.quick-preview-card strong{display:block;font-size:14px}.quick-preview-card small{display:block;font-size:12px;line-height:1.45;color:#61748e;margin-top:4px}.quick-preview-card .quick-edit-icon{margin-left:auto;color:#1b69b2}.quick-grid{display:grid;grid-template-columns:1fr 1fr;gap:15px}.quick-field{display:flex;flex-direction:column;gap:7px;font-weight:700;font-size:13px}.quick-field input,.quick-field textarea{border:1px solid #cdd9e7;border-radius:8px;padding:11px;font:inherit;font-weight:400}.quick-field textarea{resize:vertical}.quick-field.wide{grid-column:1/-1}.quick-actions{display:flex;gap:10px;margin-top:16px}.quick-btn{display:inline-flex;align-items:center;gap:8px;border-radius:8px;padding:10px 14px;background:#fff;border:1px solid #d5e1ed;color:#1c4878;text-decoration:none;font-size:13px;font-weight:700;cursor:pointer}.quick-btn.primary{background:#173f6f;border-color:#173f6f;color:white}.quick-notice{padding:12px;background:#e8f6ef;border-radius:8px;margin-top:12px}.quick-notice.error{background:#ffe8e8}.quick-topbar-title{display:flex;align-items:center;gap:10px}@media(max-width:950px){.quick-preview{grid-template-columns:repeat(2,1fr)}}@media(max-width:600px){.quick-preview,.quick-grid{grid-template-columns:1fr}.quick-panel{padding:14px}}
</style></head><body><div class="admin-shell" id="quick-shell">
<?php $panel = 'contenedores'; require __DIR__.'/includes/patrona_sidebar.php'; ?>
<div class="admin-content"><header class="topbar" data-sidebar-toolbar><div class="quick-topbar-title">Panel de administración / Contenedores / Destacados</div><a href="index.php" target="_blank" rel="noopener">Ver sitio <i class="bi bi-arrow-up-right"></i></a></header>
<main class="quick-editor"><div class="quick-heading"><div><small class="quick-muted">CMS / CONTENEDORES</small><h1>Carrusel de destacados</h1><p class="quick-muted">Cuatro tarjetas fijas. Selecciona una para modificar su ícono, título, descripción y destino.</p></div><a class="quick-btn" href="admin.php?panel=contenedores"><i class="bi bi-arrow-left"></i> Volver</a></div>
<?php if($error):?><div class="quick-notice error"><?=qe($error)?></div><?php endif;?><?php if($notice):?><div class="quick-notice"><?=qe($notice)?></div><?php endif;?>
<?php if($section):?><section class="quick-panel"><h2>Vista previa de las cuatro tarjetas</h2><p class="quick-muted">Haz clic en la tarjeta que quieras editar. La distribución pública se mantiene en cuatro columnas en escritorio.</p>
<div class="quick-preview" id="quick-preview"><?php foreach($cards as $i=>$card):?><button type="button" class="quick-preview-card <?=$selected===$i?'active':''?>" data-slot="<?=$i?>" aria-pressed="<?=$selected===$i?'true':'false'?>"><span class="quick-icon"><?=qe($card['icon'])?></span><span><strong><?=qe($card['title'])?></strong><small><?=qe($card['description'])?></small></span><i class="bi bi-pencil-square quick-edit-icon"></i></button><?php endforeach;?></div></section>
<section class="quick-panel"><h2 id="quick-edit-title">Editar tarjeta <?=$selected+1?></h2><p class="quick-muted">Solo se editan los datos de la tarjeta seleccionada; no se agregan ni eliminan tarjetas.</p><form method="post" id="quick-form"><input type="hidden" name="csrf" value="<?=qe($_SESSION['patrona_csrf'])?>"><input type="hidden" name="slot" id="quick-slot" value="<?=$selected?>"><div class="quick-grid"><label class="quick-field">Ícono o símbolo<input name="etiqueta" id="quick-icon-input" maxlength="12" required value="<?=qe($cards[$selected]['icon'])?>"><small class="quick-muted">Ejemplos: ✦, ▦, ◇, ↗</small></label><label class="quick-field">Título<input name="titulo" id="quick-title-input" maxlength="120" required value="<?=qe($cards[$selected]['title'])?>"></label><label class="quick-field wide">Descripción<textarea name="descripcion" id="quick-description-input" rows="3" maxlength="220" required><?=qe($cards[$selected]['description'])?></textarea></label><label class="quick-field wide">Enlace de destino<input name="url" id="quick-url-input" maxlength="500" value="<?=qe($cards[$selected]['url'])?>" placeholder="https://... o #contacto"></label></div><div class="quick-actions"><button type="submit" class="quick-btn primary"><i class="bi bi-check2-circle"></i> Guardar tarjeta</button><a class="quick-btn" href="index.php" target="_blank" rel="noopener"><i class="bi bi-eye"></i> Ver sitio</a></div></form></section><?php endif;?></main></div></div>
<script>(()=>{'use strict';const cards=<?=json_encode($cards,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)?>;const shell=document.getElementById('quick-shell');const form=document.getElementById('quick-form');if(!form)return;const fields={icon:document.getElementById('quick-icon-input'),title:document.getElementById('quick-title-input'),description:document.getElementById('quick-description-input'),url:document.getElementById('quick-url-input')};let selected=Number(document.getElementById('quick-slot').value);function saveDraft(){cards[selected]={icon:fields.icon.value,title:fields.title.value,description:fields.description.value,url:fields.url.value};}function render(){document.querySelectorAll('[data-slot]').forEach(el=>{const i=Number(el.dataset.slot),c=cards[i];el.classList.toggle('active',i===selected);el.setAttribute('aria-pressed',String(i===selected));el.querySelector('.quick-icon').textContent=c.icon;el.querySelector('strong').textContent=c.title;el.querySelector('small').textContent=c.description;});}document.querySelectorAll('[data-slot]').forEach(el=>el.addEventListener('click',()=>{saveDraft();selected=Number(el.dataset.slot);document.getElementById('quick-slot').value=selected;document.getElementById('quick-edit-title').textContent='Editar tarjeta '+(selected+1);for(const key of Object.keys(fields))fields[key].value=cards[selected][key];render();}));Object.values(fields).forEach(el=>el.addEventListener('input',()=>{saveDraft();render();}));render();})();</script></body></html>
