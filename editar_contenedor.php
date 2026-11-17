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
if (empty($_SESSION['patrona_csrf'])) $_SESSION['patrona_csrf']=bin2hex(random_bytes(32));
$error=''; $notice=(string)($_SESSION['hero_notice']??'');unset($_SESSION['hero_notice']);
$section=[];$items=[];$configs=[];
try {
 $db=conexionHero();
 $q=$db->prepare("SELECT * FROM seccion WHERE id_institucion=2 AND nombre_interno='hero_principal' LIMIT 1");
 $q->execute();$section=$q->get_result()->fetch_assoc()?:[];
 if (!$section) throw new RuntimeException('No existe hero_principal en seccion. Debe registrarse con el esquema original.');
 $sid=(int)$section['id_seccion'];
 if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (!hash_equals((string)$_SESSION['patrona_csrf'],(string)($_POST['csrf']??''))) throw new RuntimeException('Token de seguridad inválido.');
  if (!in_array((string)($section['editable']??''),['si','1'],true)) throw new RuntimeException('El contenedor no es editable.');
  $action=(string)($_POST['accion']??'');
  if ($action==='guardar_item') {
   $id=max(0,(int)($_POST['id_item']??0));
   $data=[];
   foreach (['etiqueta','titulo','subtitulo','descripcion','imagen','imagen_mobile','video_youtube','url','boton_1_texto','boton_1_url','boton_2_texto','boton_2_url'] as $key) {
    $value=trim((string)($_POST[$key]??''));
    if (strlen($value)>2000) throw new RuntimeException('Campo demasiado largo: '.$key);
    $data[$key]=$value;
   }
   foreach (['imagen','imagen_mobile'] as $key) {
    if ($data[$key]!=='' && (!preg_match('~^assets/[a-zA-Z0-9_./%-]+$~',$data[$key]) || str_contains($data[$key],'..'))) throw new RuntimeException('Ruta de imagen inválida.');
   }
   foreach (['boton_1_url','boton_2_url','url','video_youtube'] as $key) {
    $v=$data[$key];
    if ($v!=='' && !preg_match('~^(https?://[^\\s]+|#[a-zA-Z0-9_-]+|/[a-zA-Z0-9_./?&=%#-]+|assets/videos/[a-zA-Z0-9_./%-]+)$~',$v)) throw new RuntimeException('URL inválida: '.$key);
   }
   $visible=($_POST['visible']??'si')==='si'?'si':'no';$orden=max(1,(int)($_POST['orden']??1));
   if ($id) {
    $check=$db->prepare('SELECT id_item FROM seccion_item WHERE id_item=? AND id_seccion=?');
    $check->bind_param('ii',$id,$sid);$check->execute();
    if (!$check->get_result()->fetch_assoc()) throw new RuntimeException('Elemento no pertenece al hero.');
    $sql='UPDATE seccion_item SET etiqueta=?,titulo=?,subtitulo=?,descripcion=?,imagen=?,imagen_mobile=?,video_youtube=?,url=?,boton_1_texto=?,boton_1_url=?,boton_2_texto=?,boton_2_url=?,visible=?,orden=? WHERE id_item=? AND id_seccion=?';
    $stmt=$db->prepare($sql);
    $stmt->bind_param('sssssssssssssiii',$data['etiqueta'],$data['titulo'],$data['subtitulo'],$data['descripcion'],$data['imagen'],$data['imagen_mobile'],$data['video_youtube'],$data['url'],$data['boton_1_texto'],$data['boton_1_url'],$data['boton_2_texto'],$data['boton_2_url'],$visible,$orden,$id,$sid);
   } else {
    $stmt=$db->prepare('INSERT INTO seccion_item (id_seccion,etiqueta,titulo,subtitulo,descripcion,imagen,imagen_mobile,video_youtube,url,boton_1_texto,boton_1_url,boton_2_texto,boton_2_url,visible,orden) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
    $stmt->bind_param('isssssssssssssi',$sid,$data['etiqueta'],$data['titulo'],$data['subtitulo'],$data['descripcion'],$data['imagen'],$data['imagen_mobile'],$data['url'],$data['boton_1_texto'],$data['boton_1_url'],$data['boton_2_texto'],$data['boton_2_url'],$visible,$orden);
   }
   $stmt->execute();
  } elseif ($action==='guardar_config') {
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
$selected=max(0,(int)($_GET['item']??0));$editing=null;foreach($items as $it){if((int)$it['id_item']===$selected)$editing=$it;}
$fields=['etiqueta'=>'Etiqueta','titulo'=>'Título','subtitulo'=>'Subtítulo','descripcion'=>'Descripción','imagen'=>'Ruta de imagen','imagen_mobile'=>'Imagen móvil','url'=>'Ruta de video MP4','video_youtube'=>'Enlace de YouTube','boton_1_texto'=>'Botón 1: texto','boton_1_url'=>'Botón 1: URL','boton_2_texto'=>'Botón 2: texto','boton_2_url'=>'Botón 2: URL'];
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Editar portada | Patrona de Lourdes</title><link rel="stylesheet" href="assets/admin/css/patrona-panel.css?v=2"><style>
body{background:#f3f6fb;color:#24334a;font-family:Arial,sans-serif}.hero-layout{max-width:1100px;margin:35px auto;padding:0 18px}.hero-card{background:#fff;border:1px solid #e1e7f0;border-radius:14px;padding:25px;margin:20px 0;box-shadow:0 4px 16px #16345a0a}.hero-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.hero-field{display:flex;flex-direction:column;gap:7px;font-size:14px}.hero-field input,.hero-field textarea,.hero-field select{padding:11px;border:1px solid #cbd5e1;border-radius:8px;width:100%;box-sizing:border-box}.hero-field.wide{grid-column:1/-1}.hero-actions{display:flex;gap:12px;align-items:center;flex-wrap:wrap}.hero-button{background:#003b6f;color:#fff;border:0;border-radius:8px;padding:12px 18px;cursor:pointer}.hero-item{display:inline-block;margin:6px;padding:12px;border:1px solid #d7e0ea;border-radius:8px;text-decoration:none;color:#003b6f}.hero-alert{padding:12px;border-radius:8px;background:#e8f4ee}.hero-error{background:#fff0f0}@media(max-width:680px){.hero-grid{grid-template-columns:1fr}}
</style></head><body><div class="hero-layout"><div class="hero-actions"><a href="admin.php?panel=contenedores">← Volver a contenedores</a><a href="index.php" target="_blank" rel="noopener">Ver sitio público</a></div><h1>Portada principal</h1><p>Editor del contenedor <code>hero_principal</code> con las tablas originales de Tabancura.</p>
<?php if($error):?><div class="hero-alert hero-error"><?=he($error)?></div><?php endif;?>
<?php if($notice):?><div class="hero-alert"><?=he($notice)?></div><?php endif;?>
<?php if($section):?>
<div class="hero-card"><h2>Diapositivas</h2><div><?php foreach($items as $it):?><a class="hero-item" href="?id=<?=$sid?>&item=<?=(int)$it['id_item']?>"><?=he((string)($it['titulo']?:'Sin título'))?> · <?=he((string)$it['visible'])?></a><?php endforeach;?><a class="hero-item" href="?id=<?=$sid?>">+ Nueva diapositiva</a></div></div>
<div class="hero-card"><h2><?=$editing?'Editar diapositiva':'Nueva diapositiva'?></h2><form method="post"><input type="hidden" name="csrf" value="<?=he($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="guardar_item"><input type="hidden" name="id_item" value="<?=(int)($editing['id_item']??0)?>"><div class="hero-grid">
<?php foreach($fields as $key=>$label):?><label class="hero-field <?=$key==='descripcion'?'wide':''?>"><strong><?=he($label)?></strong><?php if($key==='descripcion'):?><textarea name="<?=$key?>" rows="3"><?=he((string)($editing[$key]??''))?></textarea><?php else:?><input name="<?=$key?>" value="<?=he((string)($editing[$key]??''))?>"><?php endif;?></label><?php endforeach;?>
<label class="hero-field"><strong>Visible</strong><select name="visible"><option value="si" <?=($editing['visible']??'si')==='si'?'selected':''?>>Sí</option><option value="no" <?=($editing['visible']??'si')==='no'?'selected':''?>>No</option></select></label><label class="hero-field"><strong>Orden</strong><input type="number" name="orden" min="1" value="<?=(int)($editing['orden']??(count($items)+1))?>"></label></div><p>Utiliza rutas de archivos ya presentes en el servidor. La subida de archivos se incorporará reutilizando el cargador de Tabancura.</p><button class="hero-button" type="submit">Guardar diapositiva</button></form></div>
<div class="hero-card"><h2>Configuración del carrusel</h2><form method="post"><input type="hidden" name="csrf" value="<?=he($_SESSION['patrona_csrf'])?>"><input type="hidden" name="accion" value="guardar_config"><div class="hero-grid">
<?php foreach(['alineacion_texto'=>'Alineación del texto','overlay'=>'Overlay','mostrar_flechas'=>'Mostrar flechas','mostrar_indicadores'=>'Mostrar indicadores'] as $key=>$label):?><label class="hero-field"><strong><?=he($label)?></strong><input name="<?=$key?>" value="<?=he((string)($configs[$key]??''))?>"></label><?php endforeach;?></div><p></p><button class="hero-button" type="submit">Guardar configuración</button></form></div>
<?php endif;?></div></body></html>
